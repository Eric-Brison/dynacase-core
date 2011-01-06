
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */
/**
 * @class Ext.fdl.TreeCollection
 * @extends Ext.tree.TreePanel
 * @namespace Ext.fdl.Collection
 * @author Clément Laballe
 * <p>This class represents the tree collection interface.</p>
 */
Ext.fdl.TreeCollection = Ext.extend(Ext.tree.TreePanel, {

    rootVisible: false,
    
    singleDisplayClick: true,
    
    onlyCollection: true,
    
    /**
     * @cfg {Boolean} enableFreedomDragDrop True to enable freedom document drag&drop. Default to true.
     */
    enableFreedomDragDrop: true,
    
    contentConfig: {},
    
    propagatedContentConfig: {},
    
    constructor: function(config){
    	
    	Ext.apply(this, config);
    	
    	// Equip TreeCollection instance with Collection widget generic interface.
        Ext.applyIf(this, Ext.fdl.Collection);
        this.initCollectionWidget();
        
        // Setup TreeCollection.
        var me = this;
                
        var node=null;
        if (this.collection) {
             node = this.getTreeNode(this.collection);
        }
        
        if (this.search) {
             node = this.getSearchTreeNode(this.search);
        }
        
        Ext.applyIf(config,{
        
            title: me.title,
            
            loader: new Ext.tree.TreeLoader(),
                        
            root: node,
            
            selModel: new Ext.fdl.TreeMultiSelectionModel({
                //selModel: new Ext.tree.DefaultSelectionModel({
                listeners: {
                    selectionchange: function(model, nodes){
                        //console.log('SELECTION CHANGE HANDLER', nodes);
                        me.selection.clearSelection();
                        for (var i = 0; i < nodes.length; i++) {
                            if (nodes[i] != me.getRootNode()) {
                                me.selection.insertToList({
                                    id: nodes[i].attributes.collectionId
                                });
                            }
                        }
                    }
                }
            }),
            
            lines: false,
            autoScroll: true,            
            
            rootVisible: me.rootVisible,
                        
            listeners: {
                contextmenu: function(node,event){
                    me.displayContextMenu(node.attributes._fdldoc.getProperty('id'),event);
                }            
            }
        
        });
      
        Ext.apply(config, {
            enableDD: this.enableFreedomDragDrop,
            ddGroup: this.enableFreedomDragDrop ? 'docDD' : null
        });
        
        // Disable navigator context menu
        this.on('render',function(treePanel) {
        	treePanel.getEl().on("contextmenu", Ext.emptyFn, null, {preventDefault: true});
	    });
        
        if(this.enableFreedomDragDrop){
            this.setupDragDrop();
        }
        
        this.on('afterrender', function(){
            me.getRootNode().reload();
        	me.getRootNode().expand();
              
        });
        
        Ext.fdl.TreeCollection.superclass.constructor.call(this,config);
        
        
        
    },
    
    setupDragDrop: function(){
    	
    	var me = this;
        
        this.on('afterrender', function(){
                          
              var dragZone = me.dragZone;
              
              dragZone.onBeforeDrag = function(data, e){
                  me.notifyDocumentDrag(me.selection);
              };
              
              dragZone.onInitDrag = function(x, y){
                  me.displayProxy(me, true);
                  this.onStartDrag(x, y);
                  return true;
              };
              
              dragZone.getDragData = function(e){
                              
                  var data = Ext.tree.TreeDragZone.prototype.getDragData.call(this, e);
                  
                  if (data) {
                  
                      if (e.ctrlKey && data.node.isSelected()) {
                          me.getSelectionModel().unselect(data.node);
                          return;
                      }
                      
                      // Use this line to handle multiple drag - drop for tree
                      // Commented for now because support for selection with multiple collectionId should be implemented on DocumentSelection for this to work                   
                      //                    if (!data.node.isSelected()) {
                      //                        me.getSelectionModel().select(data.node, e, e.ctrlKey);
                      //                    }
                      
                      if (!data.node.isSelected()) {
                          me.getSelectionModel().select(data.node, e, false);
                      }
                      
                      
                      data.component = me;
                      data.selection = me.selection;
                      
                      //Problem here because we can have a selection with multiple parent collection and Fdl.DocumentSelection does not support that
                      // So for now it is likely that drag and drop will only work for single D&D
                      data.selection.collectionId = data.node.parentNode ? data.node.parentNode.attributes._fdldoc.id : null;
                      
                      // Disable drag for nodes who have no collection they belong to (this concerns the root node which should not be dragged)
                      if (!data.selection.collectionId && !me.search) {
                          return false;
                      }
                      
                  }
                  
                  return data;
              };
              
              // Fix for invalid drop
              dragZone.beforeInvalidDrop = function(e, id){
                  // this scrolls the original position back into view
                  //                var sm = this.tree.getSelectionModel();
                  //                sm.clearSelections();
                  //                sm.select(this.dragData.nodes, e, true);
              
              };
              
              
              // This function returns the drag proxy
              me.getProxy = function(){
                  return me.dragZone.getProxy();
              };
              
              me.isDragging = function(){
                  return me.dragZone.dragging;
              };
              
              me.getDropZone = function(){
                  return me.dropZone;
              };
              
              Ext.fdl.KeyBoard.on('keychange',function(){
                  me.displayProxy(me.hoveredWid, true);
              });
              
              me.dropZone.onNodeOver = function(nodedata, source, e, data){
              
                  var fromCol = me.context.getDocument({
                      id: source.dragData.selection.collectionId,
                      useCache: true
                  });
                  
                  var overCol = nodedata.node.parentNode ? nodedata.node.parentNode.attributes._fdldoc : null;
                  var overDoc = nodedata.node.attributes._fdldoc;
                  
                  if (!(overDoc.isCollection() && overDoc.isFolder())) {
                      return this.dropNotAllowed;
                  }
                  
                  var ret = me.notifyDocumentDragOver(me, fromCol, null, source.dragData.selection, overDoc);
                  
                  if (ret) {
                      return this.dropAllowed;
                  }
                  else {
                      return this.dropNotAllowed;
                  }
              };
              
              me.dropZone.onNodeEnter = function(nodedata, source, e, data){
                  data.component.overDoc = nodedata.node.attributes._fdldoc;
                  data.component.displayProxy(me);
              };
              
              me.dropZone.onNodeOut = function(nodedata, source, e, data){
                  data.component.overDoc = null;
                  data.component.displayProxy(me);
              };
              
              me.dropZone.onNodeDrop = function(nodedata, source, e, data){
              
                  var dropDoc = nodedata.node.attributes._fdldoc;
                  
                  var dropCol = nodedata.node.parentNode ? nodedata.node.parentNode.attributes._fdldoc : null;
                  
                  var canDrop = me.notifyDocumentDragOver(
                      me, data.component.collection,
                      null, data.selection,
                      dropDoc
                  );
                  
                  if(canDrop) {

                      nodedata.node.mask();
                      
                      var dragCol = me.context.getDocument({
                          id: source.dragData.selection.collectionId,
                          useCache: true
                      });
                      
                      //var dropCol = nodedata.node.parentNode ? nodedata.node.parentNode.attributes._fdldoc : null;
                      var dropCol = null;
                      
                      (function(){
                          var ret = me.notifyDocumentDrop(source.dragData.component, dragCol, dropCol, source.dragData.selection, dropDoc);
                          
                          //nodedata.node.unmask();
                          
                          if (!ret) {
                              Ext.Msg.alert('Warning', 'Problem during drag and drop');
                          }
                      }).defer(5);
                                  
                  }
                  
                  return true;
                  
              };
              
          });
      },
    
    toString: function(){
        return 'Ext.fdl.TreeCollection';
    },
    
    /**
     * @method applySelection
     * Method to apply current DocumentSelection to collection graphic representation.
     */
    applySelection: function(){
    
    },
    
    /**
     * @method reload
     * modifiedDocObject can be an object with document id as indexes or can be a document id
     */
    reload: function(server, modifiedDocObj){
		
		console.log('RELOAD',server,modifiedDocObj);
    
        this.recursiveNodeReload = function(node){
        
            if (modifiedDocObj == node.attributes._fdldoc.getProperty('id') || modifiedDocObj[node.attributes._fdldoc.getProperty('id')]) {            
                node.reload();
                return false;
            }
            
            node.eachChild(function(node){
                this.recursiveNodeReload(node);
            }, this);
            
        };
        
        if (!modifiedDocObj || modifiedDocObj == this.collection.getProperty('id') || modifiedDocObj[this.collection.getProperty('id')]) {
            // Reload root node
            this.root.reload(true);
        }
        else {
            // Go search into subnodes
            this.recursiveNodeReload(this.root);
            
        }
        
        
    },
    
    mask: function(){
        //        if (this.gridPanel && this.gridPanel.loadMask) {
        //            this.gridPanel.loadMask.show();
        //        }
    },
    
    unmask: function(){
        //        if (this.gridPanel && this.gridPanel.loadMask) {
        //            this.gridPanel.loadMask.hide();
        //        }
    },
        
    getTreeNode: function(collection){
    
        var me = this;

        var node = new Ext.tree.TreeNode({
            collectionId: collection.id,
            contentConfig: me.contentConfig,
            propagatedContentConfig: me.propagatedContentConfig,
            _fdldoc: collection,
            text: collection.getTitle(),
            expandable: (me.collection != collection && collection.isCollection() && collection.getProperty('haschildfolder')), // undefined is to be removed once data is able to return haschildfolder for documents
            expanded: false,
            icon: collection.getIcon({
                width: 16
            }),
            draggable: me.collection != collection,
            listeners: {
                beforeexpand: function(n){                
                    n.reload();                    
                },
                click: function(n, e){
                	console.log('CLICK',me);
                	if(me.singleDisplayClick){
                    	me.displayDocument(n.attributes._fdldoc.id, 'view', e);
                	}
                },
                dblclick: function(n, e){
                	console.log('DBLCLICK');
                	if(!me.singleDisplayClick){
                    	me.displayDocument(n.attributes._fdldoc.id, 'view', e);
                	}
                }
            }
        });
        
        node.reload = function(){
        
            this.mask();
            
            while (this.firstChild) {
                this.removeChild(this.firstChild);
            }
            
            var defContentConfig = {
                verifyhaschild: true,
                slice: 'ALL'
            };
            
            if (this.getOwnerTree().onlyCollection) {
                defContentConfig.filter = "doctype = 'D' or doctype = 'S'";
            }
            
            if (! this.contentConfig){
                if(this.getDepth()==0){
                    this.contentConfig = this.getOwnerTree().contentConfig||{};
                } else {
                    this.contentConfig = this.getOwnerTree().propagatedContentConfig||{};
                }
            }
            
            Ext.applyIf(this.contentConfig,defContentConfig);
            
            //if (!n.hasChildNodes()) {
            var c = this.getOwnerTree().context.getDocument({
                id: this.attributes.collectionId,
                //useCache: true,
                contentStore: true,
                contentConfig: this.contentConfig
            });
            if (c.isAlive()) {
            
                if (c.getStoredContent()) {
                    var sf = c.getStoredContent().getDocuments();
                }
                else {
                    var sf = [];
                }
                
                for (var i = 0; i < sf.length; i++) {
                    var doc = sf[i];
                    this.appendChild(this.getOwnerTree().getTreeNode(doc));
                }
            }
            else {
                var t = 'ERROR:' + c.context.getLastErrorMessage();
            }
            //}
            
            this.unmask();
            
        };
        
        node.mask = function(){
            if (this.getUI() && this.getUI().getIconEl()) {
                this.getUI().getIconEl().src = this.getOwnerTree().context.url + 'lib/ext/resources/images/default/tree/loading.gif';
            }
        };
        
        node.unmask = function(){
            if (this.getUI() && this.getUI().getIconEl()) {
                this.getUI().getIconEl().src = this.attributes.icon;
            }
        };
        
        return node;
        
    },
    
    getSearchTreeNode: function(search){
    
        var me = this;
        
        var node = new Ext.tree.TreeNode({
            _fdldoc: search,
            text: 'Recherche',
            expandable: true,
            expanded: false,
            draggable: false,
            listeners: {
                beforeexpand: function(n){
                    n.reload();
                },
                click: function(n, e){
                	console.log('CLICK');
                	if(me.singleDisplayClick){
                    	me.displayDocument(n.attributes._fdldoc.id, 'view', e);
                	}
                },
                dblclick: function(n, e){
                	console.log('DBLCLICK');
                	if(!me.singleDisplayClick){
                    	me.displayDocument(n.attributes._fdldoc.id, 'view', e);
                	}
                }
            }
        
        });
        
        node.reload = function(expand){
			
			//console.log('RELOAD SEARCH NODE');
			
            this.mask();
            
            while (this.firstChild) {
                this.removeChild(this.firstChild);
            }
            
            var search = this.attributes._fdldoc;
            
            var defContentConfig = {
                verifyhaschild: true,
                slice: 'ALL',
                key:(search.key ? search.key : '')
            };
            
            if (! this.contentConfig){
                if(this.getDepth()==0){
                    this.contentConfig = this.getOwnerTree().contentConfig||{};
                } else {
                    this.contentConfig = this.getOwnerTree().propagatedContentConfig||{};
                }
            }
            
            Ext.applyIf(this.contentConfig,defContentConfig);
                       
            if (search.filter) this.contentConfig.filter=null;
            var dl=search.search(this.contentConfig);
            if (dl) {
            	var sf = dl.getDocuments();
            	for (var i = 0; i < sf.length; i++) {
            		var doc = sf[i];
            		this.appendChild(this.getOwnerTree().getTreeNode(doc));
            	}
            } else Ext.Msg.alert('Error search criteria');
            if(expand){
            	this.expand();
            }
            
            this.unmask();
        };
        
        node.mask = function(){
            if (this.getUI()) {
                this.getUI().beforeLoad();
            }
        };
        node.unmask = function(){
            if (this.getUI()) {
                this.getUI().afterLoad();
            }
        };
        
        
        return node;
        
    },
    
    defaultDropCollection: function(){
    	return null ;
    },
    
    /**
     * Method to display tree.
     * @method display
     * @param {Object} target
     */
    display: function(target){   
        console.log('Ext.fdl.TreeCollection.display() is deprecated.');//FIXME: it would be better to warn instaed of log
        return this;        
    }
    
});

// If drag zone is installed it is necessary to remove node click because it doubles up drag selection
// See post http://www.extjs.com/forum/showthread.php?t=28115 where such tricks have been used to implement multiselect drag & drop on tree
// I agree it is hacky still but current ExtJS implementation (as for version 3.0 public)  does not offer other ways 
Ext.fdl.TreeMultiSelectionModel = Ext.extend(Ext.tree.MultiSelectionModel, {
    onNodeClick: function(node, e){
        // Nothing to do
    }
});


// TODO Check why extending treenode does not work for events
//
//Ext.fdl.TreeCollectionNode = function(config){
//	
//	Ext.fdl.TreeCollectonNode.superclass.constructor.apply(this);
//	
//};
//Ext.fdl.TreeCollectionNode = Ext.extend(Ext.tree.TreeNode, {
//
//    //    collection: null,
//    
//    //    constructor: function(config){
//    
//    //		Ext.apply(this,config);
//    //		
//    //		console.log('COLLECTION',this.collection);
//    //    
//    //        if (!this.collection) {
//    //            console.log('Error : Ext.fdl.TreeCollectionNode is provided no collection');
//    //        }
//    //        
//    //        if (!this.collection.isCollection()) {
//    //            console.log('Error : Ext.fdl.TreeCollectionNode is provided an object which is not a Fdl.Collection.');
//    //        }
//    //        
//    //        Ext.apply(this, {
//    //            collectionId: this.collection.id,
//    //            text: this.collection.getTitle(),
//    //            expandable: (this.collection.getProperty('haschildfolder') == undefined || this.collection.getProperty('haschildfolder') == true), // undefined is to be removed once data is able to return haschildfolder for documents
//    //            expanded: false,
//    //            icon: this.collection.getIcon({
//    //                width: 16
//    //            }),
//    //            draggable: true,
//    //			listeners: {
//    //				dblclick: function(n, e){
//    //					console.log('dblclick');
//    //				}
//    //			}
//    //        });
//    
//    //        Ext.fdl.TreeCollectionNode.superclass.constructor.call(config);
//    
//    //        this.on('beforeexpand', function(n){
//    //        
//    //            n.getUI().getIconEl().src = window.location.pathname + 'lib/ext/resources/images/default/tree/loading.gif';
//    //            
//    //            while (n.firstChild) {
//    //                n.removeChild(n.firstChild);
//    //            }
//    //            
//    //            //if (!n.hasChildNodes()) {
//    //            var c = me.collection.context.getDocument({
//    //                id: n.attributes.collectionId,
//    //                useCache: true
//    //            });
//    //            
//    //            var sf = c.getSubCollections();
//    //            for (var i = 0; i < sf.length; i++) {
//    //                var doc = sf[i];
//    //                n.appendChild(new Ext.fdl.TreeCollectionNode({
//    //					collection: doc
//    //				}));
//    //            }
//    //            //}
//    //            
//    //            n.getUI().getIconEl().src = n.attributes.icon;
//    //            
//    //        });
//    
//    //        this.on('dblclick', function(n, e){
//    //            console.log('dblclick', n.attributes.collectionId);
//    //        });
//    
//    
//    
//    //    },
//        
//    toString: function(){
//        return 'Ext.fdl.TreeCollectionNode';
//    }
//    
//});
