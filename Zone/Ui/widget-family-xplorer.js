
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Ext.fdl.FamilyNavigator
 * @namespace Ext.fdl.Collection
 * @author Clement Laballe
 * <p>This class represent an instance of family navigator which is a replacement for the classic onefam application.</p>
 */

Ext.fdl.FamilyXplorer = Ext.extend(Ext.Panel, {

    context: null,
    
    /**
     * @cfg {Object} familyTree Object representing families and searches displayed by the widget.
     */
    familyTree: null,
    
    application: null,
    
    /**
     * @cfg {String} applicationLabel Displayed name
     */
    applicationLabel: null,
    
    onAdminFamily: function(){
    	// Behaviour when admin family button is clicked
    },
    
    onUserFamily: function(){
    	// Behaviour when user family button is clicked
    },
    
    constructor: function(config){
        
        Ext.apply(this, config);
        
        var me = this;
        
        if(!me.displayConfig){
        	me.displayConfig = {};
        }
        
        var canExecuteGetMasterPref = this.canEditMasterFamilies;
        var canExecuteGetPref = this.canEditUserFamilies;
        
        var ftp = new Ext.fdl.FamilyTreePanel({
            context: this.context,
            
            dataConfig: me.familyTreeDataConfig ? me.familyTreeDataConfig : Ext.fdl.FamilyTreePanel.prototype.dataConfig,
            
            border: false,
                        
            familyData: this.familyTree,
            
            displayCollectionGrid: function(config,node,reload){
                                
                console.log('Config',config,node);
                
                var container = Ext.getCmp('collection-container');
                
                if(node && node.attributes){
                    var interfaceId = node.attributes.interfaceId;
                } else {
                    var interfaceId = 'temporary-result';
                }
                
                if(!this.collectionArray){
                    this.collectionArray = [];  
                }
                
                var collectionPanelString = '';
                
                if(config.search && config.search.family){
                    if(me.displayConfig.familyWidget && me.displayConfig.familyWidget[config.search.family]){
                        collectionPanelString = me.displayConfig.familyWidget[config.search.family];
                    }
                }
                
                if(collectionPanelString == ''){
                    collectionPanelString = 'Ext.fdl.GridCollection';
                }
                
                var collectionPanel =  new (eval("("+collectionPanelString+")"))({
                    search: config.search,
                    collection: config.collection,
                    header: false,
                    displaySearchFilter: function(id){
                        var panel = ftp.getEditSearchPanel(id);
                        //console.log(ftp.collectionArray[interfaceId],interfaceId);
                        var multidocument = Ext.getCmp('document-container');                        
                        multidocument.add(panel);
                        multidocument.setActiveTab(panel);
                    }
                });
                
                if(!this.collectionArray[interfaceId]){
                    
                    this.collectionArray[interfaceId] = new Ext.fdl.CollectionContainer({
                        //collectionMenu: "EXTUI:default-collection-menu.xml",
                        collectionMenuConfig: {
                            app: me.application,
                            action: 'ONEFAM_EXT_MENU',
                            fldid: config.collection ? config.collection.id : '',
                            famid: config.search ? config.search.family : ''
                        },
                        header: false,
                        collectionPanel: collectionPanel,
                        collectionPanelConfig: {                        	
                        	
                        },
                        title: config.collection ? config.collection.getTitle() : (config.search ? config.search.family.id : ''),
                        tbar: [],
                        context: me.context,
                        border: false,
                        displayDocument: function(id, mode){
                            var multidocument = Ext.getCmp('document-container');                        
                            multidocument.addDocumentId(id, mode);
                        },
                        getToolbar: function(dataMenu,extToolbar){
                                                            
                            extToolbar = Ext.fdl.CollectionContainer.prototype.getToolbar.call(this,dataMenu,extToolbar);
                            
                            extToolbar.add({
                                xtype: 'tbfill'
                            });
                            
                            extToolbar.add({
                                text: me.context._("eui::Search"),
                                menu: {
                                    items: [{
                                        text: me.context._("eui::Create Search"),
                                        handler: function(item){
                                            var panel = ftp.getCreateSearchPanel(ftp.displayed);
                                            console.log(ftp.collectionArray[interfaceId],interfaceId);
                                            var multidocument = Ext.getCmp('document-container');                        
                                            multidocument.add(panel);
                                            multidocument.setActiveTab(panel);
                                        }
                                    },{
                                        text: me.context._("eui::Search by word"),
                                        checked: true,
                                        group: 'search',
                                        checkHandler: function(item,checked){
                                            if(ftp.collectionArray[interfaceId].collectionPanel.search){
                                                ftp.collectionArray[interfaceId].collectionPanel.search.mode = 'word';                                          
                                            } else {
                                                ftp.collectionArray[interfaceId].collectionPanel.contentConfig.mode = 'word';
                                            }
                                            searchField.emptyText = me.context._("eui::Search by word");
                                            searchField.focus();
                                        }
                                    },{
                                        text: me.context._("eui::Search by exp"),
                                        checked: false,
                                        group: 'search',
                                        checkHandler: function(item,checked){
                                            if(ftp.collectionArray[interfaceId].collectionPanel.search){
                                                ftp.collectionArray[interfaceId].collectionPanel.search.mode = 'regexp';
                                            } else {
                                                ftp.collectionArray[interfaceId].collectionPanel.contentConfig.mode = 'regexp';
                                            }
                                            searchField.emptyText = me.context._("eui::Search by exp");
                                            searchField.focus();
                                        }
                                    }]  
                                }
                            });
                            
                            var searchField = new Ext.form.TextField({
                                xtype: 'textfield',
                                emptyText: me.context._("eui::Search by word"),
                                value: ftp.collectionArray[interfaceId] ? ftp.collectionArray[interfaceId].keyValue : null,
                                enableKeyEvents: true,
                                listeners: {
                                    keyup: function(field,event){
                                        if(event.getCharCode() == event.ENTER){
                                        
                                            //console.log('SEARCH',g.collectionPanel.search);
                                            if(ftp.collectionArray[interfaceId].collectionPanel.search){
                                                ftp.collectionArray[interfaceId].collectionPanel.search.key = field.getValue();
                                            } else {
                                                ftp.collectionArray[interfaceId].collectionPanel.contentConfig.key = field.getValue();
                                            }
                                            ftp.collectionArray[interfaceId].keyValue = field.getValue();
                                            ftp.collectionArray[interfaceId].collectionPanel.reload();                                      
                                            
                                        }
                                    }
                                }
                            });
                                                    
                            extToolbar.add(searchField);
                                                    
                            return extToolbar;
                            
                        }
                    });
                    
                    if(config.search){
                        this.collectionArray[interfaceId].onWidgetCollectionChange = function(collectionPanelClass){
                         
                            if(!me.displayConfig.familyWidget){
                                me.displayConfig.familyWidget = {};
                            }
                            
                            me.displayConfig.familyWidget[config.search.family] = collectionPanelClass ;
                            
                            me.onDisplayConfigChange(me.displayConfig);
                            
                        }
                    }
                                        
                    container.add(this.collectionArray[interfaceId]);
                    
                }
                else {
                	if(reload){
	                    console.log('Interface',this.collectionArray[interfaceId]);
	                    this.collectionArray[interfaceId].collectionPanel.reload();
                	}
                }
                
                ftp.currentFamily = config.search ? config.search.family : (config.collection ? config.collection.getValue('se_famid') : '') ;
                ftp.currentContainer = this.collectionArray[interfaceId] ;
//              console.log('Current Family',ftp.currentFamily, config.search, config.collection);
                
//              container.removeAll(false);
//              container.doLayout();
//              console.log('REMOVE ALL');
//              
//              container.add(this.collectionArray[interfaceId]);
                
//              container.items.each(function(item){
//                  item.hide();
//              });             
                
                console.log('ID',this.collectionArray[interfaceId]);
                container.layout.setActiveItem(this.collectionArray[interfaceId].id);
                
                //container.removeAll();
                
                if(node){
                	console.log('NODE',node);
                    container.ownerCt.setTitle('<span style="margin-left:5px;"><img src='+node.attributes.icon+' style="float:left;height:15;width:15;" />' + node.text + "</span>");
                } else {
                    if(config.collection){
                    	console.log('COLLECTION',config.collection);
                        container.ownerCt.setTitle('<span style="margin-left:5px;"><img src='+config.collection.getIcon({width:15})+' style="float:left;height:15;width:15;" />'+config.collection.getTitle()+ "</span>");
                    }
                }
                
                container.doLayout();
                
            },
            
            displayDocument: function(id, mode, node, reload){
            
                this.displayed = {
                    document : id
                };
                
                var document = ftp.context.getDocument({
                    id: id
                });
                
                this.displayed.family = document.getValue('se_famid');
                
                this.displayCollectionGrid({
                    collection: me.context.getDocument({
                        id: id
                    })
                },node,reload);
                
            },
            displaySearch: function(filter, node){
                                
                this.displayed = {
                    family : filter.family,
                    filter : filter
                };
                
                var search = me.context.getSearchDocument({
                    filter: filter,
                    family: filter.family
                });
                
                search.family = filter.family ;
                
                this.displayCollectionGrid({
                    search: search
                },node);
                
            },
            
            getEditSearchPanel: function(documentId){
              
                
                var document = ftp.context.getDocument({
                    id: documentId
                });
                
                var familyDocument = ftp.context.getDocument({
                    id : document.getValue('se_famid'),
                    useCache: true
                });
                   
                var temporarySearch = document.cloneDocument({
                    temporary: true
                });
                                                
                var requester = new Ext.fdl.Requester({
                    tbar: [{
                        text: me.context._("eui::Evaluate"),
                        handler: function(){
                        
                            requester.save();
                            console.log('REQUESTER-PREVIEW-DOCUMENT',requester.document);
                            ftp.displayDocument(requester.document.id,null,null,true);
                            
                        }
                    },{
                        text: me.context._("eui::Save"),
                        handler: function(){
                        
                            requester.document = document ;
                              
                            //requester.document.setValue('se_famid',displayed.family);
                            requester.save();
                            
                            document.setValue('se_famid',familyDocument.id);
                            document.save();
                            ftp.displayDocument(document.id);
                            ftp.reload();
                             
                        }
                    }],
                    document: temporarySearch,
                    familyId: familyDocument.id,
                    allowSubfamily: true,
                    closable: true,
                    title: me.context._("eui::Edit search : ") + document.getTitle()
                });
                
                return requester ;
                
            },
            
            getCreateSearchPanel: function(displayed){
                
                if(displayed.document) {
                    
                    var document = ftp.context.getDocument({
                        id: displayed.document
                    });
                    
                    var familyDocument = ftp.context.getDocument({
                        id : document.getValue('se_famid'),
                        useCache: true
                    });
                    
                    var temporarySearch = document.cloneDocument({
                        temporary: true
                    });
                    
                } else {
                
                    var familyDocument = ftp.context.getDocument({
                        id: displayed.family,
                        useCache: true
                    });
                    
                    var temporarySearch = ftp.context.createDocument({
                        familyId: 'REPORT',
                        temporary: true
                    });
                    
                    //temporarySearch.save();
                
                }
                                
                var requester = new Ext.fdl.Requester({
                    tbar: [{
                        text: 'Evaluer',
                        handler: function(){
                        
                            requester.document.setValue('se_famid',displayed.family);
                            requester.save();
                            console.log('REQUESTER-PREVIEW-DOCUMENT',requester.document);
                            ftp.displayDocument(requester.document.id, null, null, true);
                            
                        }
                    },{
                        text: 'Sauvegarder',
                        handler: function(){
                        
                            Ext.Msg.prompt('freedom',me.context._("eui::Enter search name"),function(btn,text){
                               
                                if(btn == 'ok'){
                                    
                                    //requester.document.setValue('se_famid',displayed.family);
                                    requester.save();
                                    var doc = requester.document.cloneDocument({
                                        temporary: false,
                                        title: text
                                    });
                                    console.log('CLONED DOCUMENT',doc,displayed.family);
                                    doc.setValue('se_famid',displayed.family);
                                    doc.save();
                                    ftp.displayDocument(doc.id, null, null, true);
                                    ftp.reload();
                                    
                                }
                            
                            });
                        
                        }
                    }],
                    document: temporarySearch,
                    familyId: displayed.family,
                    allowSubfamily: true,
                    closable: true,
                    title: me.context._("eui::Create search :") + familyDocument.getTitle()
                });
                
                return requester ;
                
            }
                
                
        });
        
        ftp.subscribe('modifydocument',function(fdldoc){
            console.log('FTP',ftp,fdldoc.getProperty('fromid'));
            console.log('MODIFY DOCUMENT EVENT RECEIVED');
            if(fdldoc.getProperty('fromid') == ftp.currentFamily){
                ftp.currentContainer.collectionPanel.reload();
            }
            
        });
        
        this.familyTreePanel = ftp ;
        
        this.leftContainer = new Ext.Panel({
            
            layout: 'fit',
            
            region: 'west',
            split: true,
            collapsible: true,
            width: '200px',
            
            title: me.applicationLabel?me.applicationLabel:me.application,
                    
            items: [ftp],
            
            tbar: [
                   {
                   text: me.context._("eui::Families"),
                   menu: {
                       items:[{
                           text: me.context._("eui::Administrator"),
                           disabled: !canExecuteGetMasterPref,
                           handler: function(){                            
                               me.onAdminFamily();                            
                           }
                       },{
                           text: me.context._("eui::User"),
                           disabled: !canExecuteGetPref,
                           handler: function(){                            
                               me.onUserFamily();                            
                           }
                       }]  
                   }
               },{
                   text: me.context._("eui::Display"),
                   menu: {
                       items: [{
                           text: me.context._("eui::Horizontal"),
                           id: 'display-north',
                           icon: 'lib/ui/icon/application_split.png',
                           disabled: me.displayConfig.display && me.displayConfig.display == 'north',
                           handler: function(){
                           
                               var center = Ext.getCmp('center-panel');
                               
                               var collectionContainer = Ext.getCmp('collection-container');
                               var westCollectionContainer = Ext.getCmp('west-collection-container');
                               var northCollectionContainer = Ext.getCmp('north-collection-container');
                               
                               northCollectionContainer.setTitle(westCollectionContainer.title);
                               
                               westCollectionContainer.setWidth(0);
                               westCollectionContainer.remove(collectionContainer,false);                  
                               westCollectionContainer.hide();
                               westCollectionContainer.doLayout();
                               
                               northCollectionContainer.setHeight(400);
                               northCollectionContainer.add(collectionContainer);
                               northCollectionContainer.show();
                               northCollectionContainer.doLayout();
                               
                               center.doLayout();
                               
                               Ext.getCmp('display-north').disable();
                               Ext.getCmp('display-west').enable();
                               
                               // Must be remembered in a parameter.
                               me.displayConfig.display = 'north';
                               me.modifyDisplayConfig(me.displayConfig);
                               
                           }
                       },{
                           text: me.context._("eui::Vertical"),
                           id: 'display-west',
                           icon: 'lib/ui/icon/application_tile_horizontal.png',
                           disabled: me.displayConfig.display && me.displayConfig.display == 'west',
                           handler: function(){
                           
                               var center = Ext.getCmp('center-panel');
                               
                               var collectionContainer = Ext.getCmp('collection-container');
                               var westCollectionContainer = Ext.getCmp('west-collection-container');
                               var northCollectionContainer = Ext.getCmp('north-collection-container');
                               
                               westCollectionContainer.setTitle(northCollectionContainer.title);
                               
                               northCollectionContainer.setHeight(0);
                               northCollectionContainer.remove(collectionContainer,false);                  
                               northCollectionContainer.hide();
                               northCollectionContainer.doLayout();
                               
                               westCollectionContainer.setWidth(400);
                               westCollectionContainer.add(collectionContainer);
                               westCollectionContainer.show();
                               westCollectionContainer.doLayout();
                               
                               center.doLayout();
                               
                               Ext.getCmp('display-west').disable();
                               Ext.getCmp('display-north').enable();
                               
                               // Must be remembered in a parameter.
                               me.displayConfig.display = 'west';
                               me.modifyDisplayConfig(me.displayConfig);
                           
                           }
                       }]
                   
                   }
               },{
                   xtype: 'tbfill'
               },{
                   handler: function(){
                       ftp.reload();
                   },
                   iconCls: 'x-tbar-loading',
                   scope: this,
                   tooltip: '',
                   overflowText: ''
               }]
        
        });
        
        Ext.apply(this, {
            layout: 'border',
            items: [this.leftContainer, new Ext.Panel({
                layout: 'border',
                region: 'center',
                id: 'center-panel',
                border: false,
                items: [{
                    id: 'west-collection-container',
                    region: 'west',
                    layout: 'fit',
                    split: true,
                    collapsible: true,
                    width: 0,
                    hidden: true//,
                    //html: '<i>Ici une vue collection</i>'
                }, new Ext.fdl.MultiDocumentPanel({
                    id: 'document-container',
                    region: 'center',
                    context: this.context,
                    forceClassic: true
                }), {
                    id: 'north-collection-container',
                    region: 'north',
                    layout: 'fit',
                    split: true,
                    collapsible: true,
                    height: 0,
                    hidden: true
                }]
            })]
        });
        
        if(me.displayConfig.display && me.displayConfig.display == 'north'){
        	var container = Ext.getCmp('north-collection-container');
        	container.show();
        	var height = me.displayConfig.size || 400 ;
        	container.setHeight(height);
        } else {
        	var container = Ext.getCmp('west-collection-container');
        	container.show();
        	var width = me.displayConfig.size || 400 ;
        	container.setWidth(width);
        }
        
        container.add({
            id: 'collection-container',
            layout: 'card',
            border: false
        });
        
        Ext.fdl.FamilyXplorer.superclass.constructor.call(this,config);
                
	    Ext.getCmp('north-collection-container').on('resize', function(panel, width, height){
	     	me.displayConfig.size = height;
	        me.modifyDisplayConfig(me.displayConfig);
	    });
	      
	    Ext.getCmp('west-collection-container').on('resize', function(panel, width, height){
	     	me.displayConfig.size = width;
	        me.modifyDisplayConfig(me.displayConfig);
	    });
        
    },
    
    openDocument: function(id,mode,config){
        
         var multidocument = Ext.getCmp('document-container');                        
         multidocument.addDocumentId(id,mode,config); 
        
    },
    
    modifyDisplayConfig: function(config){
    	
    	this.displayConfig = config ;
    	
    	this.onDisplayConfigChange(this.displayConfig);
    	
    },
    
    onDisplayConfigChange: function(config){
    	
    },
        
    toString: function(){
        return 'Ext.fdl.FamilyXplorer';
    }   
    
});