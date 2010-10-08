
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @author Clément Laballe
 */
Ext.fdl.Window = Ext.extend(Ext.Window, {

    context: null,
    
    mode: 'view',
    
    document: null,
    
    border: false,
    //bodyBorder: false,
    width: 800,
    height: 600,
    //autoScroll: true,
    //autoHeight: true,
    resizable: true,
    minWidth: 400,
    bodyStyle: '',
    //closeAction: 'hide',
    maximizable: true,
    minimizable: true,
    collapsible: true,
    plain: true,
    constrain: true,
    layout: 'fit',
    shadow: false,
    
    initComponent: function(){
    
        Ext.fdl.Window.superclass.initComponent.call(this);
		
		if (!this.mode) {
			this.mode = 'view';
		}
        		
		var me = this ;
		
		this.subscribe('modifydocument',function(fdldoc){
			if(me.document && me.document.id == fdldoc.id){
				me.document = fdldoc;
				me.updateTitle();
			}
		});
		
		// Remove listener when window is closed
		this.on('close',function(window){
			window.removeSubscriptionsFor('modifydocument');
		});
		
		this.on('beforeclose',function(window){
			if(window.documentPanel && window.documentPanel.closeConfirm){
				
				var closeConfirm = window.documentPanel.closeConfirm();
				if(closeConfirm && !window.closeConfirmed){
					
					Ext.Msg.show({
	            		buttons:{
	            			ok:'Oui',
	            			cancel:'Non'
	            		},
	            		fn: function(id){
	            			if(id=='ok'){
	            				window.closeConfirmed = true;
	            				window.close();
	            			}
	            		},
	            		title: 'freedom',
	            		msg: closeConfirm
	            	});
	            	
	            	return false;
				} else {
					return true ;
				}
			}
		});
		
		console.log('WINDOW',this);
		    
    },
        
//    handleResize: function(box){
//    	
//    	console.log('handle resize');
//    	
//    	var me = this ;
//    	
//    	this.items.each(function(item){
//    		item.hide();
//    	});
//    	
//    	(function(){
//    		Ext.fdl.Window.superclass.handleResize.call(me,box);
//    		
//    		me.items.each(function(item){
//    			item.show();
//    		});
//    		
//    	}).defer(10);
//    	
//    },
    
    showMask: function(){
        if (!this.mask) {
            this.mask = new Ext.LoadMask(this.body, {
                msg: "En cours de chargement..."
            });
        }
        this.mask.show();
    },
    
    hideMask: function(){
        if (this.mask) {
            this.mask.hide();
        }
    },
    
    onDocumentModified: function(newDoc,prevId){
    	// Nothing by default, to be implemented by developper
    },
    
    updateDocument: function(doc){
    	
    	console.log('UPDATE DOCUMENT');
    
    	var me = this ;
    	
        this.document = doc;
        
        this.context = doc.context ;
        
        this.showMask();
        
        // If this document is a Folder in view mode
        if (doc.isCollection() && this.mode != 'create') {
        	
        	var panel = new Ext.fdl.DocumentMultiView({
				document: doc,
				view: this.view,
				mode: this.mode
			});
			
			panel.setDocument = function (doc){
            	Ext.fdl.DocumentMultiView.prototype.setDocument.call(this,doc);
            	var prevId = me.document.id ;
            	me.document = doc ;
            	me.onDocumentModified(doc,prevId);
            };
            
            panel.on('close',function(){
            	this.close();
            },this);
            
        }
        else {
            if (doc instanceof Fdl.SearchDocument) {
                        	
                var colWid = new Ext.fdl.GridCollection({
                    search: doc,
                    height: this.getHeight(),
                    tBar: true,
                    filterColumns: true
                });
                
                var panel = new Ext.fdl.CollectionContainer({
                	collectionPanel: colWid
                });
                
                //var panel = cWid.display();
                
            }
            // Document is not a collection
            else {
            
                var dWid = new Ext.fdl.Document({
                    document: doc,
                    mode: this.mode,
                    config: this.config
                });
                
                dWid.setDocument = function (doc){
                	Ext.fdl.Document.prototype.setDocument.call(this,doc);
                	var prevId = me.document.id || me.document.getProperty('fromid') ;
                	me.document = doc ;
                	me.onDocumentModified(doc,prevId);
                };
                
                var panel = dWid;
                
                panel.on('close',function(){
            		this.close();
            	},this);
                
            }
            
            var dropTarget = new Ext.dd.DropTarget(this.getEl(), {
                ddGroup: 'docDD',
                notifyEnter: function(source, e, data){
                    if (this.overClass) {
                        this.el.addClass(this.overClass);
                    }
                    return this.dropNotAllowed;
                },
                notifyOver: function(source, e, data){
                    return this.dropNotAllowed;
                },
                notifyDrop: function(source, e, data){
                
                }
            });            
            
        }
        
        this.documentPanel = panel ;
        
        this.removeAll();
        this.add(panel);
        
        this.updateTitle();
        
        this.hideMask();
        
        
    },
    
    updateDocumentId: function(id){
    	
    	//console.log('UPDATE DOCUMENT ID', id);
    
        this.showMask();
        
        if (this.mode != 'create') {
            this.document = this.context.getDocument({
                id: id,
				contentStore: true,
				latest: false,
				getUserTags: true
            });
            var doc = this.document;
            
        }
        else {
            this.document = this.context.createDocument({
                familyId: id
            });
        }
        
        if(!this.document){
            Ext.Msg.alert(this.context._("eui::missing right"),this.context._("eui::You have no right to access this document"));
            this.close();
            return;
        }
        
        this.updateDocument(this.document);
        
    },
    
    addIcon: function(){
    	
    	if(this.document.id || this.mode == 'create'){
	        this.header.addClass('x-btn-text-icon');
	        this.header.setStyle('background-image', 'url(' +
	        this.document.getIcon({
	            width: 18
	        }) +
	        ')');
	        this.header.setStyle('background-position', '0 6px');
	        this.header.setStyle('background-repeat', 'no-repeat');
	        this.header.setStyle('padding', '7px 0 7px 20px');
    	}
    },
    
    updateTitle: function(){
    	
    	if (!this.document.id && this.mode == 'create'){ // Create mode    		
    		var title = this.context._("eui::Creation :")+' ' + this.document.getProperty('fromtitle');
    		this.setTitle(title);    		
    	}
    	
        if (this.document.getProperty && this.document.getProperty('id') > 0) {
        
            var title2 = '<span style="float:left;">' + this.document.getProperty('fromtitle') + " " +
            Fdl.encodeHtmlTags(this.document.getTitle()) +
//            '</span><span style="float:right">' +
//            (this.document.getProperty('version') ? 'Version ' + this.document.getProperty('version') + ' ' : '') +
//            (this.document.hasWorkflow() ? (this.document.isFixed() ? '<span style="padding-left:10px;margin-right:3px;background-color:' + this.document.getColorState() + '">&nbsp;</span>' + this.document.getLocalisedState() : (this.document.getActivityState() ? this.document.getActivityState() : '<i>' + this.document.getLocalisedState() + '</i>')) : '') +
            '</span>';
            this.setTitle(title2);
                        
        }
        
        this.addIcon();
        
        this.doLayout();
        
    }

});
