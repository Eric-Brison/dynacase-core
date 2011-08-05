
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Ext.fdl.Interface
 * <p>This class represents the global application interface. It is delivered with methods to handle global behaviour and to react to broadcasted events.
 * It should be instanciated only once in most cases but nothing prevents from creating two or more if it is required to have more than one global behaviour.
 * An implementation must be provided by the developper. It is recommended that attributes and methods used to describe interface behaviour are defined on this object.</p>
 * <p><b>Broadcasted Interface Events.</b>
 * ExtUI components communicate events to report user actions which do not concern them, like the action of opening a freedom document.
 * All Ext.Observable are given this ability by extending ExtJS with the subscribe() and publish() methods.
 * The developper can define new events to fit the interface needs.
 * If possible, it is recommended that these events are subscribed by the Ext.fdl.Interface for the architecture to be more easily readable and consistent.
 * Following events are subscribed and implemented by default. Other ExtUI components publish them by default.
 * <ul>
 * <li>opendocument : published when the user make an action to open a document</li>
 * <li>closedocument : published when the user make an action to close a document</li>
 * <li>opensearch : published when the user make an action to open a search document</li>
 * </ul></p>
 */
Ext.fdl.Interface = Ext.extend(Ext.util.Observable, {
	
	/**
	 * @cfg {Fdl.Context} context Holds reference to a freedom context for convenience.
	 */
	context: null,

    views: {},
    
    constructor: function(config){
    
        Ext.fdl.Interface.superclass.constructor.call(config);
		
		Ext.apply(this,config);
		
		if(this.context == null){
			console.log('Warning : Application is not provided a context');
		}
		        
        this.subscribe('opendocument', function(wid, id, mode, config){
			console.log('Event Received in Ext.fdl.Interface : opendocument', wid, id, mode, config);
            this.onOpenDocument(wid, id, mode, config);
        }, this);
                
        this.subscribe('closedocument', function(id,mode){
            console.log('Event Received in Ext.fdl.Interface : closedocument', id,mode);
            this.onCloseDocument(id,mode);
        }, this);
		
		this.subscribe('opensearch', function(filter,config){
			console.log('Event Received in Ext.fdl.Interface : opensearch', filter, config);
			this.onOpenSearch(filter, config);
		}, this);
		
		this.subscribe('openurl', function(url,target,config){
			console.log('Event Received in Ext.fdl.Interface : openurl', url,target,config);
			this.onOpenUrl(url,target,config);
		},this);
        
    },
    
	/**
	 * Automatically called when an 'opendocument' event is received. Default implementation opens a Ext.fdl.Window. Can be overriden.
	 * @method onOpenDocument
	 * @param {Ext.fdl.Collection} wid The object from which the document was opened.
	 * @param {Number} id The identifier of the document. Can be a family identifier for 'create' mode.
	 * @param {String} mode The desired mode for the document interface. Default possible values are : 'view','edit','create'.
	 * @param {Object} config An arbitrary configuration object which may contain implementation specific informations.
	 */
    onOpenDocument: function(wid, id, mode, config){
    
        if (!mode) {
            var mode = 'view';
        }
        
        // Default
        if (!wid || wid.target == '_blank') {
        
            var win = new Ext.fdl.Window({
                mode: mode,
                context: this.context,
                config: config,
                listeners: {
                    show: function(win){
                    	var latest=true;
                    	if (config && config.latest === false) latest=false;
                        var vdocument = this.context.getDocument({
                            id: id,
            				contentStore: true,
            				latest: latest,
            				getUserTags: true
                        });
                        if (vdocument.id) {
                        	win.updateDocumentId(vdocument.id,config);
                        } else {
                            Ext.Msg.alert(this.context._("eui::missing right"),this.context._("eui::You have no right to access this document"));
                        }
                    },
                    close: function(win){
                        win.publish('closedocument', win);
                    }
                }
            });
            
            win.show();
            
        }
        
    },
	
	/**
	 * Automatically called when a 'closedocument' event is received. No default implementation.
	 * @method onCloseDocument
	 * @param {Object} id
	 */
	onCloseDocument: function(id){
		
	},
	
	/**
	 * Automatically called when a 'opensearch' event is received. No default implementation.
	 * @method onOpenSearch
	 * @param {Fdl.DocumentFilter} filter The filter object defining the search. 
	 * @param {Object} config An arbitrary configuration object which may contain implementation specific informations.
	 */
	onOpenSearch: function(filter, config){
		
	},
	
	onOpenUrl: function(url,target,config){
		
		var me = this ;
		
		if(!config){
			var config = {};
		}
		
		if(url && !config.url){
			config.url = url ;
		}
				
		// If the url is an url for a freedom document, we parse it and redirect to openDocument
		if((new RegExp("action=(FDL_CARD|VIEWEXTDOC)", "i").test(url) && new RegExp("app=FDL", "i").test(url))){
			if (! new RegExp("zone=.*:pdf", "").test(url) ) {
				if (new RegExp("zone=.*\.odt", "").test(url) ) {
					window.open(url,'download_frame');
					return;
				} else {
					var result = url.match(new RegExp("id=([0-9]+)","i"));
					this.onOpenDocument(null,result[1],'view',config);
					return;
				}
			}
		}
		
		if((new RegExp("action=(GENERIC_EDIT|EDITEXTDOC)", "i").test(url) && new RegExp("app=(GENERIC|FDL)", "i").test(url))){									
			var result = url.match(new RegExp("id=([0-9]+)","i"));		
			if (! result) {
				result = url.match(new RegExp("(classid|famid)=([0-9A-Z_-]+)","i"));
				if (result) this.onOpenDocument(null,result[2],'create',config);
			} else this.onOpenDocument(null,result[1],'edit',config);
			return;
		}

		if(!config){
			var config = {};
		}
		
		if(target == '_blank'){
			if(!this.blankWindowCurId){
				this.blankWindowCurId = '0';
			} else {
				this.blankWindowCurId ++ ;
			}
			var win = null ;
		} else {
			var win = Ext.WindowMgr.get(target);
		}
		
		if(!win){
			
			var mediaPanel = new Ext.ux.MediaPanel({
				autoScroll: false,
				border: false,
				style: 'height:100%;width:100%;',
				bodyStyle: 'height:100%;',
				mediaCfg:{
					mediaType: 'HTM',
					name:(target == '_blank')?('blank' + me.blankWindowCurId):target,
					url: config.opener ? 'about:blank' : url
				},
				listeners: {
					mediaload: function(mediaPanel,mediaObject){												
						win.hideMask();
					}
				}
			});
		
			win = new Ext.fdl.Window({
				layout: 'fit',
				id: target == '_blank' ? ('blank' + me.blankWindowCurId) : target,
				height: config.height || 400,
				width: config.width || 400,
				border: true,
				title: config.title,
				listeners: {
					afterrender: function(){
						win.showMask();
					}
				},
				items: [mediaPanel]

			});
			
			win.show();
		
		} else {
			
			win.showMask();
			win.removeAll();
			win.add(new Ext.ux.MediaPanel({
				autoScroll: false,
				border: false,
				style: 'height:100%;width:100%;',
				bodyStyle: 'height:100%;',
				mediaCfg:{
					mediaType: 'HTM',
					name:(target == '_blank')?('blank' + me.blankWindowCurId):target,
					url: config.opener ? 'about:blank' : url
				},
				listeners: {
					mediaload: function(){
						win.hideMask();
					}
				}
			}));
			win.doLayout();
			win.setTitle(config.title);
			
			Ext.WindowMgr.bringToFront(win);
			
		}
					
		var jswindow = mediaPanel.mediaObject.dom.contentWindow;
			
		// This will affect jswindow.opener property.
		if(config.opener){
			// For firefox we simulate opening, which will affect jswindow.opener. (opener is not writable in Firefox)
			jswindow  = config.opener.open(url,(target == '_blank')?('blank' + me.blankWindowCurId):target);
			// For IE we affect opener directly.
			jswindow.opener = config.opener ;
		}
		
		jswindow.extResize = function(dw,dh){
			console.log('extResize',dw,dh);
			win.setWidth(win.getWidth() + dw);
			win.setHeight(win.getHeight() + dh + 32);
		};
		
		try{
			addEvent(mediaPanel.mediaObject.dom,'load',function(){
		    	var jswindow = mediaPanel.mediaObject.dom.contentWindow;
		    	
		    	var jsdocument = mediaPanel.mediaObject.dom.contentWindow.document || mediaPanel.mediaObject.dom.contentDocument ;
		    	
				if(jsdocument.title){
					win.setTitle(jsdocument.title);
				}
				
				jswindow.close = function(){
					win.close();
				};
				
//				jswindow.resizeBy = function(dw,dh){
//					console.log('resizeBy',dw,dh);
//				};
				
				console.log('JSWINDOW',jswindow);
				
		   	});
		} catch(e) {}
		
	},
    
	// Deprecated
    addView: function(view){
		console.log('Ext.fdl.Interface addView() method is deprecated. This call should be avoided.');
        var documents = view.getDocuments();
        var l = documents.length;
        for (var i = 0; i < l; i++) {
            if (!this.views[documents[i].getProperty('id')]) {
                this.views[documents[i].getProperty('id')] = [];
            }
            if (this.views[documents[i].getProperty('id')].indexOf(view) == -1) {
                this.views[documents[i].getProperty('id')].push(view);
            }
        }
    },
    
	// Deprecated
    notifyDocument: function(document, previousId){
    	console.log('Ext.fdl.Interface notifyDocument() method is deprecated. This call should be avoided.');
        // If previousId is set, it means document has changed its id (due to revision or new state for example).
        if (previousId) {
            this.views[document.id] = this.views[previousId];
            this.windows[document.id] = this.windows[previousId];
            this.windows[previousId] = null;
            //docBar[document.id] = docBar[previousId];
            taskBar.removeTaskButton(this.docBar[previousId]);
            this.docBar[previousId] = null;
        }
        
        //if (document.isCollection()) {
        this.windows[document.id].mode = 'view';
        this.windows[document.id].updateDocumentId(document.id);
        //}
        
        //        var views = this.views[document.id];
        //        if (views) {
        //            var l = views.length;
        //            for (var i = 0; i < l; i++) {
        //                var view = views[i];
        //                if (view.setDocument) {
        //                    view.setDocument(this.documents[document.id]);
        //                }
        //                else {
        //                    view.update();
        //                }
        //            }
        //        }
        
        updateDesktop();
        
        // Update taskbar button
        taskBar.removeTaskButton(this.docBar[document.id]);
        this.windows[document.id].taskTitle = document.getTitle();
        this.docBar[document.id] = taskBar.addTaskButton(this.windows[document.id]);
        
    },
    
	// Deprecated
    createDocument: function(name){
		console.log('Ext.fdl.Interface createDocument() method is deprecated. This call should be avoided.');    
        var document = this.context.createDocument({
            familyId: name
        });
        
        return document;
    },
    
	// Deprecated
    addDocument: function(document){
        if (!this.documents[document.getProperty('id')]) {
            this.documents[document.getProperty('id')] = document;
        }
    },
    
	// Deprecated
    refreshDocument: function(document){
        this.documents[document.id].save();
        this.notifyDocument(document);
        return this.documents[document.id];
    },
    
	// Deprecated
    closeDocument: function(id){
        if (this.windows[id]) {
            this.windows[id].close();
        }
        docBar[id] = null;
    },
    
    /**
     * Handle display of document window
     * Deprecated
     */
    displayDocument: function(id, mode, source, config){
		this.publish('opendocument',null,id,mode,config);
    },
    
    displayFamily: function(id){
    
        var family = context.getDocument({
            id: id
        });
        
        var attributes = family.getAttributes();
        
        var node = new Ext.tree.TreeNode({
            attributeId: null,
            structureRoot: true,
            text: family.getTitle(),
            expandable: true,
            expanded: true,
            icon: family.getIcon({
                width: 20
            }),
            draggable: false,
            listeners: {
                expand: function(n){
                //expandFolder(n);
                },
                click: function(n, e){
                
                    addButton.enable();
                    addButton.setHandler(function(b, e){
                        createNode(n);
                    });
                    
                    deleteButton.disable();
                    saveButton.disable();
                    
                    var formpanel = Ext.getCmp('form-attribute-edition');
                    
                    formpanel.removeAll();
                    
                //Fdl.ApplicationManager.displayDocument(n.attributes.collection, 'view', e);
                }
            }
        });
        
        var addButton = new Ext.Button({
            icon: 'ECM/Images/add.png',
            text: 'Ajouter',
            disabled: true
        });
        
        var deleteButton = new Ext.Button({
            icon: 'ECM/Images/delete.png',
            text: 'Supprimer',
            disabled: true
        });
        
        var saveButton = new Ext.Button({
            icon: 'ECM/Images/application_edit.png',
            text: 'Sauver',
            disabled: true
        
        });
        
        var unselect = function(){
        
            Ext.getCmp('treepanel-family').getSelectionModel().clearSelections();
            Ext.getCmp('form-attribute-edition').removeAll();
            
            addButton.disable();
            deleteButton.disable();
            saveButton.disable();
            
        };
        
        var refreshNode = function(n){
            n.setText(n.attributes.attribute.getLabel());
            
            if (n.attributes.attribute.isNode()) {
                n.attributes.leaf = false;
                n.attributes.expandable = true;
                n.attributes.expanded = true;
                n.getUI().updateExpandIcon();
            }
            else {
                n.attributes.leaf = true;
                n.attributes.expandable = false;
                n.attributes.expanded = true;
                n.getUI().updateExpandIcon();
            }
        };
        
        // a : Fdl.Attribute
        // n : Ext.tree.TreeNode
        var removeAttribute = function(a, n){
        
            if (family.removeAttribute({
                attributeId: a.id
            })) {
            
                n.remove();
                
                unselect();
            }
            else {
                Ext.Msg.alert('Erreur', Fdl.getLastErrorMessage());
            }
            
        };
        
        var modifyAttribute = function(a, n){
        
            var values = Ext.getCmp('form-attribute-edition').getForm().getValues();
            
            
            var type = values.type;
            
            if (type == 'docid' && values.familyid) {
                type = type + '("' + values.familyid + '")';
            }
            else 
                if (type == 'docid') {
                    Ext.Msg.alert('Erreur', 'Aucune famille sélectionnée');
                    return;
                }
            
            if (family.modifyAttribute({
                attributeId: a.id,
                //parent: 'us_fr_ident',
                type: type,
                visibility: values.visibility,
                label: values.label
            //				order: 40,
            //				inTitle: false,
            //				inAbstract: false,
            //				needed: false,
            //				link: '',
            //				elink: '',
            //				constraint: '',
            //				options: {
            //					vlabel: 'up'
            //				}
            })) {
                // Required to actualize attribute and node
                family.reload();
                a = family.getAttribute(a.id);
                n.attributes.attribute = a;
                
                refreshNode(n);
                
                unselect();
                
            }
            else {
                Ext.Msg.alert('Erreur', Fdl.getLastErrorMessage());
            }
            
        };
        
        var moveAttribute = function(a){
        
            if (family.modifyAttribute({
                attributeId: a.id,
                parent: (n.parentNode.attributes.attribute && n.parentNode.attributes.attribute.id) ? n.parentNode.attributes.attribute.id : ''
            })) {
            
            }
            else {
                Ext.Msg.alert('Erreur', Fdl.getLastErrorMessage());
            }
            
        };
        
        var createAttribute = function(n){
        
            var values = Ext.getCmp('form-attribute-edition').getForm().getValues();
            
            
            var type = values.type;
            
            if (type == 'docid' && values.familyid) {
                type = type + '("' + values.familyid + '")';
            }
            else 
                if (type == 'docid') {
                    Ext.Msg.alert('Erreur', 'Aucune famille sélectionnée');
                    return;
                }
            
            if (family.addAttribute({
                attributeId: values.attributeId,
                parent: (n.parentNode.attributes.attribute && n.parentNode.attributes.attribute.id) ? n.parentNode.attributes.attribute.id : '',
                type: type,
                visibility: values.visibility,
                label: values.label
            //                order: 30
            })) {
            
                // Required to actualize attribute and node
                family.reload();
                a = family.getAttribute(values.attributeId);
                
                n.attributes.attribute = a;
                
                refreshNode(n);
                
                unselect();
                
            }
            else {
                Ext.Msg.alert('Erreur', Fdl.getLastErrorMessage());
            }
            
        };
        
        Ext.fdl.AttributeNode = Ext.extend(Ext.tree.TreeNode, {
        
            updateForm: function(n){
            
                if (!n.attributes.attribute) {
                    var isNew = true;
                }
                else {
                    var isNew = false;
                }
                
                // Add Button
                if (isNew) {
                    addButton.disable();
                }
                else {
                    if (n.attributes.attribute.isNode()) {
                        addButton.enable();
                        addButton.setHandler(function(b, e){
                            createNode(n);
                        });
                    }
                    else {
                        addButton.disable();
                    }
                }
                
                // Delete Button
                if (isNew) {
                    deleteButton.enable();
                    deleteButton.setHandler(function(b, e){
                        n.remove();
                    });
                }
                else {
                    deleteButton.enable();
                    deleteButton.setHandler(function(b, e){
                        removeAttribute(n.attributes.attribute, n);
                    });
                }
                
                // Save Button
                if (isNew) {
                    saveButton.enable();
                    saveButton.setHandler(function(b, e){
                        createAttribute(n);
                    });
                }
                else {
                    saveButton.enable();
                    saveButton.setHandler(function(b, e){
                        modifyAttribute(n.attributes.attribute, n);
                    });
                }
                
                // Form Panel Display
                var formpanel = Ext.getCmp('form-attribute-edition');
                
                formpanel.removeAll();
                
                formpanel.add([new Ext.form.TextField({
                    name: 'attributeId',
                    fieldLabel: 'Identifiant',
                    value: isNew ? '' : n.attributes.attribute.id,
                    disabled: isNew ? false : true,
                    anchor: '-15'
                }), new Ext.form.TextField({
                    name: 'label',
                    fieldLabel: 'Label',
                    value: isNew ? '' : n.attributes.attribute.getLabel(),
                    anchor: '-15'
                }), new Ext.form.ComboBox({
                    name: 'type',
                    hiddenName: 'type',
                    fieldLabel: 'Type',
                    value: isNew ? 'text' : n.attributes.attribute.type,
                    anchor: '-15',
                    
                    editable: false,
                    forceSelection: true,
                    disableKeyFilter: true,
                    triggerAction: 'all',
                    mode: 'local',
                    
                    store: new Ext.data.ArrayStore({
                        fields: ['type', 'display'],
                        data: [['tab', 'Onglet'], ['frame', 'Cadre'], ['array', 'Tableau'], ['text', 'Texte'], ['longtext', 'Texte Long'], ['htmltext', 'Texte Html'], ['date', 'Date'], ['file', 'Fichier'], ['image', 'Image'], ['docid', 'Relation'], ['enum', 'Enuméré']]
                    }),
                    valueField: 'type',
                    displayField: 'display',
                    
                    listeners: {
                        select: function(combo, record, index){
                            if (record.get('type') == 'docid') {
                                if (!formpanel.docIdfield) {
                                    formpanel.docIdField = formpanel.add(new Ext.fdl.FamilyComboBox({
                                        //anchor: '-15',
                                        width: 160,
                                        fieldLabel: 'Famille',
                                        name: 'familyid',
                                        hiddenName: 'familyid'
                                    }));
                                }
                            }
                            else {
                                if (formpanel.docIdField) {
                                    formpanel.remove(formpanel.docIdField);
                                    formpanel.docIdField = null;
                                }
                            }
                            formpanel.doLayout();
                        //console.log('Selected', record.get('type'));
                        }
                    }
                
                }), new Ext.form.ComboBox({
                    name: 'visibility',
                    hiddenName: 'visibility',
                    fieldLabel: 'Visibilité',
                    value: isNew ? 'W' : n.attributes.attribute.visibility,
                    anchor: '-15',
                    
                    editable: false,
                    forceSelection: true,
                    disableKeyFilter: true,
                    triggerAction: 'all',
                    mode: 'local',
                    
                    store: new Ext.data.ArrayStore({
                        fields: ['visibility', 'display'],
                        data: [['H', 'Caché'], ['R', 'Lecture seule'], ['W', 'Lecture/Écriture'], ['S', 'Visible en écriture'], ['O', 'Écriture seule'], ['I', 'Invisible'], ['U', 'Tableau statique']]
                    }),
                    valueField: 'visibility',
                    displayField: 'display',
                    
                    listeners: {
                        select: function(combo, record, index){
                        //console.log('Selected', record.get('type'));
                        }
                    }
                
                }), new Ext.form.Checkbox({
                    name: 'inTitle',
                    fieldLabel: 'Dans le titre',
                    checked: isNew ? false : false
                }), new Ext.form.Checkbox({
                    name: 'inAbstract',
                    fieldLabel: 'Dans le résumé',
                    checked: isNew ? false : false
                }), new Ext.form.Checkbox({
                    name: 'needed',
                    fieldLabel: 'Obligatoire',
                    checked: isNew ? false : false
                })]);
                
                if (!isNew && n.attributes.attribute.type == 'docid') {
                
                    //console.log('Attribute', n.attributes.attribute);
                    
                    formpanel.docIdField = formpanel.add(new Ext.fdl.FamilyComboBox({
                        //anchor: '-15',
                        width: 160,
                        fieldLabel: 'Famille',
                        hiddenName: 'familyid',
                        name: 'familyid',
                        value: n.attributes.attribute._data.format
                    }));
                }
                
                formpanel.doLayout();
                
                formpanel.show();
                
            }
            
        });
        
        var createNode = function(n){
        
            var child;
            
            n.expand(false, false, function(n){
                child = n.appendChild(new Ext.fdl.AttributeNode({
                    attribute: null,
                    text: '',
                    expandable: false,
                    expanded: false,
                    draggable: true,
                    listeners: {
                        expand: function(n){
                        //expandFolder(n);
                        },
                        click: function(n, e){
                        
                            this.updateForm(n);
                            
                        }
                    },
                    leaf: true
                }));
            });
            
            child.select();
            child.fireEvent('click', child);
        };
        
        var getNode = function(attribute, parentNode){
        
            var child = parentNode.appendChild(new Ext.fdl.AttributeNode({
                attribute: attribute,
                text: attribute.getLabel(),
                expandable: attribute.isNode(),
                expanded: attribute.isNode(),
                draggable: true,
                listeners: {
                    expand: function(n){
                    //expandFolder(n);
                    },
                    click: function(n, e){
                    
                        this.updateForm(n);
                        
                    }
                },
                leaf: attribute.isLeaf()
            }));
            
            return child;
            
        };
        
        var recursive = function(node){
        
            for (var a in attributes) {
            
                var attribute = attributes[a];
                if ((!attribute.parentId && node.attributes.structureRoot) || (node.attributes.attribute && attribute.parentId == node.attributes.attribute.id)) {
                    if (attribute.usefor != 'A') {
                        var child = getNode(attribute, node);
                        
                        recursive(child);
                    }
                    
                }
            }
            
        };
        
        recursive(node);
        
        var win = new Ext.Window({
            title: 'Famille ' + family.getTitle(),
            layout: 'hbox',
            width: 600,
            height: 400,
            constrain: true,
            renderTo: Fdl.ApplicationManager.desktopPanel.body,
            layoutConfig: {
                align: 'stretch',
                pack: 'start'
            },
            
            items: [{
                id: 'treepanel-family',
                xtype: 'treepanel',
                loader: new Ext.tree.TreeLoader(),
                root: node,
                enableDD: true,
                autoScroll: true,
                flex: 1,
                bbar: [addButton, deleteButton],
                listeners: {
                    movenode: function(tree, node, oldParent, newParent, index){
                    
                        if (family.modifyAttribute({
                            attributeId: node.attributes.attribute.id,
                            parent: (newParent.attributes.attribute && newParent.attributes.attribute.id) ? newParent.attributes.attribute.id : ''
                        })) {
                        //console.log('Move node ' + node.attributes.attribute.getLabel() + ' from ' + oldParent.attributes.attribute.getLabel() + ' to ' + newParent.attributes.attribute.getLabel());
                        }
                        else {
                            Ext.Msg.alert('Erreur', Fdl.getLastErrorMessage());
                        }
                        
                        
                        
                    }
                }
            }, {
                id: 'form-attribute-edition',
                xtype: 'form',
                bodyStyle: 'padding:10px;',
                bbar: [saveButton],
                flex: 1
            }]
        });
        
        win.show();
        
    },
    
    displayCycleEditor: function(id){
    
        var workflow = new Fdl.Workflow({
            id: id
        });
        
        var normDPI = 96;
        
        var computeGraphSize = function(){
            if (workflowGraphWiz) {
                var delta = 3;
                var fw = workflowGraphWiz.getWidth() - delta;
                var fh = workflowGraphWiz.getHeight() - delta;
                var wcm = (fw / normDPI) * 2.54;
                var hcm = (fh / normDPI) * 2.54;
                
                return wcm + ',' + hcm;
            }
            return 24;
            
        };
        
        if (workflow.isAlive()) {
        
            var states = workflow.getStates();
            
            var transitions = workflow.getTransitions();
            
            var transitionTypes = workflow.getTransitionTypes();
            
            // Code for States
            
            var stateData = new Array();
            
            for (var state in states) {
                stateData.push(states[state]);
            }
            
            var stateStore = new Ext.data.JsonStore({
            
                data: stateData,
                
                idProperty: 'key',
                
                fields: [{
                    name: 'label'
                }, {
                    name: 'activity'
                }, {
                    name: 'key'
                }]
            });
            
            var newButton = new Ext.Button({
                text: 'Nouvel état',
                icon: 'ECM/Images/add.png',
                handler: function(b, e){
                
                    stateEditor.getForm().reset();
                    
                    createButton.enable();
                    saveButton.disable();
                    deleteButton.disable();
                    newButton.disable();
                    
                    keyField.enable();
                    
                    stateEditor.enable();
                    
                },
                disabled: false
            });
            
            var stateList = new Ext.grid.GridPanel({
            
                store: stateStore,
                
                width: '100%',
                
                forceFit: true,
                autoExpandColumn: 'activity',
                
                columns: [{
                    header: 'Clef',
                    dataIndex: 'key',
                    hidden: true
                }, {
                    header: 'Etat',
                    width: 80,
                    dataIndex: 'label'
                }, {
                    header: 'Activité',
                    id: 'activity',
                    dataIndex: 'activity'
                }],
                
                flex: 1,
                
                sm: new Ext.grid.RowSelectionModel({
                    singleSelect: true,
                    listeners: {
                        rowselect: function(sm, row, rec){
                            stateEditor.getForm().loadRecord(rec);
                            saveButton.currentRecord = rec;
                            saveButton.enable();
                            deleteButton.currentRecord = rec;
                            deleteButton.enable();
                            
                            createButton.disable();
                            newButton.enable();
                            stateEditor.enable();
                            keyField.disable();
                        }
                    }
                }),
                
                bbar: [newButton]
            
            });
            
            
            var createButton = new Ext.Button({
                text: 'Créer',
                icon: 'ECM/Images/add.png',
                handler: function(b, e){
                
                    var values = stateEditor.getForm().getValues();
                    
                    if (workflow.addState({
                        key: keyField.getValue(),
                        activity: values.activity,
                        label: values.label
                    })) {
                    
                        var dataItem = {
                            key: keyField.getValue(),
                            activity: values.activity,
                            label: values.label
                        };
                        
                        stateStore.add(new stateStore.recordType(dataItem, keyField.getValue()));
                        
                        workflowGraphWiz.renderMedia();
                        
                        resetStateForm();
                        
                    }
                    else {
                        Ext.Msg.alert('Erreur', Fdl.getLastErrorMessage());
                    }
                    
                },
                disabled: true
            });
            
            var saveButton = new Ext.Button({
                text: 'Sauver',
                icon: 'ECM/Images/application_edit.png',
                handler: function(b, e){
                
                    var values = stateEditor.getForm().getValues();
                    
                    if (workflow.modifyState({
                        key: keyField.getValue(),
                        activity: values.activity,
                        label: values.label
                    })) {
                    
                        saveButton.currentRecord.set('key', keyField.getValue());
                        saveButton.currentRecord.set('activity', values.activity);
                        saveButton.currentRecord.set('label', values.label);
                        saveButton.currentRecord.commit();
                        saveButton.currentRecord = null;
                        
                        workflowGraphWiz.renderMedia();
                        
                        resetStateForm();
                        
                    }
                    else {
                        Ext.Msg.alert('Erreur', Fdl.getLastErrorMessage());
                    }
                    
                },
                disabled: true
            });
            
            var deleteButton = new Ext.Button({
                text: 'Supprimer',
                icon: 'ECM/Images/delete.png',
                handler: function(b, e){
                
                    //var values = stateEditor.getForm().getValues();
                    
                    if (workflow.removeState({
                        key: keyField.getValue()
                    })) {
                    
                        workflowGraphWiz.renderMedia();
                        
                        stateStore.remove(stateStore.getById(keyField.getValue()));
                        deleteButton.currentRecord = null;
                        
                        resetStateForm();
                        
                    }
                    else {
                        Ext.Msg.alert('Erreur', Fdl.getLastErrorMessage());
                    }
                    
                },
                disabled: true
            });
            
            var resetStateForm = function(){
            
                stateEditor.getForm().reset();
                
                createButton.disable();
                saveButton.disable();
                deleteButton.disable();
                newButton.enable();
                
                keyField.disable();
                
                stateList.getSelectionModel().clearSelections();
                
                stateEditor.disable();
                
            };
            
            var keyField = new Ext.form.TextField({
                fieldLabel: 'Clef',
                name: 'key',
                disabled: true,
                anchor: '-7'
            });
            
            
            var stateEditor = new Ext.FormPanel({
            
                items: [keyField, {
                    xtype: 'textfield',
                    fieldLabel: 'Label',
                    name: 'label',
                    anchor: '-7'
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Activité',
                    name: 'activity',
                    anchor: '-7'
                }],
                
                bbar: [createButton, saveButton, deleteButton],
                
                height: 130,
                style: 'width:100%;',
                bodyStyle: 'padding:10px;',
                
                disabled: true
            
            });
            
            // eo Code for States
            
            // Code for Transitions Types
            
            var transitionTypeData = new Array();
            
            for (var transitionType in transitionTypes) {
                transitionTypeData.push(transitionTypes[transitionType]);
            }
            
            var transitionTypeStore = new Ext.data.JsonStore({
            
                data: transitionTypeData,
                
                idProperty: 'key',
                
                fields: [{
                    name: 'label'
                }, {
                    name: 'key'
                }]
            });
            
            var tNewButton = new Ext.Button({
                text: 'Nouvelle transition',
                icon: 'ECM/Images/add.png',
                handler: function(b, e){
                
                    transitionTypeEditor.getForm().reset();
                    
                    tCreateButton.enable();
                    tSaveButton.disable();
                    tDeleteButton.disable();
                    tNewButton.disable();
                    
                    transitionTypeEditor.enable();
                    tKeyField.enable();
                    
                },
                disabled: false
            });
            
            var transitionTypeList = new Ext.grid.GridPanel({
            
                store: transitionTypeStore,
                
                autoExpandColumn: 'label',
                forceFit: true,
                
                columns: [{
                    header: 'Clef',
                    width: 80,
                    dataIndex: 'key',
                    readOnly: true
                }, {
                    header: 'Label',
                    id: 'label',
                    dataIndex: 'label'
                }],
                
                flex: 1,
                
                sm: new Ext.grid.RowSelectionModel({
                    singleSelect: true,
                    listeners: {
                        rowselect: function(sm, row, rec){
                        
                            transitionTypeEditor.getForm().loadRecord(rec);
                            
                            tSaveButton.currentRecord = rec;
                            tSaveButton.enable();
                            tDeleteButton.currentRecord = rec;
                            tDeleteButton.enable();
                            
                            tCreateButton.disable();
                            tNewButton.enable();
                            
                            transitionTypeEditor.enable();
                            tKeyField.disable();
                            
                        }
                    }
                }),
                
                bbar: [tNewButton]
            
            });
            
            var tCreateButton = new Ext.Button({
                text: 'Créer',
                icon: 'ECM/Images/add.png',
                handler: function(b, e){
                
                    var values = transitionTypeEditor.getForm().getValues();
                    
                    if (workflow.addTransitionType({
                        key: tKeyField.getValue(),
                        label: values.label
                    })) {
                    
                        transitionTypes[values.transitionType] = {
                            key: tKeyField.getValue(),
                            label: values.label
                        };
                        
                        var dataItem = {
                            key: tKeyField.getValue(),
                            label: values.label
                        };
                        
                        transitionTypeStore.add(new transitionTypeStore.recordType(dataItem, values.key));
                        
                        resetTransitionTypeForm();
                        
                        workflowGraphWiz.renderMedia();
                        
                    }
                    else {
                        Ext.Msg.alert('Erreur', Fdl.getLastErrorMessage());
                    }
                    
                },
                disabled: true
            });
            
            var tSaveButton = new Ext.Button({
                text: 'Sauver',
                icon: 'ECM/Images/application_edit.png',
                handler: function(b, e){
                
                    var values = transitionTypeEditor.getForm().getValues();
                    
                    if (workflow.modifyTransitionType({
                        key: tKeyField.getValue(),
                        label: values.label
                    })) {
                    
                        transitionTypes[tKeyField.getValue()].label = values.label;
                        
                        tSaveButton.currentRecord.set('label', values.label);
                        tSaveButton.currentRecord.commit();
                        tSaveButton.currentRecord = null;
                        
                        resetTransitionTypeForm();
                        
                        workflowGraphWiz.renderMedia();
                        
                    }
                    else {
                        Ext.Msg.alert('Erreur', Fdl.getLastErrorMessage());
                    }
                },
                disabled: true
            });
            
            var tDeleteButton = new Ext.Button({
                text: 'Supprimer',
                icon: 'ECM/Images/delete.png',
                handler: function(b, e){
                
                    var values = transitionTypeEditor.getForm().getValues();
                    
                    if (workflow.removeTransitionType({
                        key: tKeyField.getValue()
                    })) {
                    
                        transitionTypeStore.remove(transitionTypeStore.getById(tKeyField.getValue()));
                        tDeleteButton.currentRecord = null;
                        
                        resetTransitionTypeForm();
                        
                        workflowGraphWiz.renderMedia();
                        
                    }
                    else {
                        Ext.Msg.alert('Erreur', Fdl.getLastErrorMessage());
                    }
                    
                },
                disabled: true
            });
            
            var resetTransitionTypeForm = function(){
            
                transitionTypeEditor.getForm().reset();
                
                tCreateButton.disable();
                tSaveButton.disable();
                tDeleteButton.disable();
                tNewButton.enable();
                
                transitionTypeList.getSelectionModel().clearSelections();
                
                tKeyField.disable();
                transitionTypeEditor.disable();
            };
            
            var tKeyField = new Ext.form.TextField({
                fieldLabel: 'Clef',
                name: 'key',
                disabled: true,
                anchor: '-7'
            });
            
            var transitionTypeEditor = new Ext.FormPanel({
            
                items: [tKeyField, {
                    xtype: 'textfield',
                    fieldLabel: 'Label',
                    name: 'label',
                    anchor: '-7'
                }],
                
                bbar: [tCreateButton, tSaveButton, tDeleteButton],
                
                height: 104,
                style: 'width:100%;',
                bodyStyle: 'padding:10px;',
                
                disabled: true
            
            });
            
            // eo Code for Transitions Types
            
            // Code for Graph
            
            var transitionStore = new Ext.data.JsonStore({
            
                data: transitions,
                
                //idProperty: 'transitionType',
                
                fields: [{
                    name: 'start'
                }, {
                    name: 'finish'
                }, {
                    name: 'transitionType'
                }]
            });
            
            var gNewButton = new Ext.Button({
                text: 'Associer une transition',
                icon: 'ECM/Images/add.png',
                handler: function(b, e){
                
                    transitionEditor.getForm().reset();
                    
                    gCreateButton.enable();
                    gDeleteButton.disable();
                    gNewButton.disable();
                    
                    transitionEditor.enable();
                    gStartField.enable();
                    gFinishField.enable();
                    gTypeField.enable();
                    
                },
                disabled: false
            });
            
            var transitionList = new Ext.grid.GridPanel({
            
                store: transitionStore,
                forceFit: false,
                autoExpandColumn: 'transitionType',
                columns: [{
                    header: 'Début',
                    width: 80,
                    dataIndex: 'start',
                    renderer: function(value, metaData, record, rowIndex, colIndex, store){
                        return states[value].label;
                    }
                }, {
                    header: 'Fin',
                    dataIndex: 'finish',
                    width: 80,
                    renderer: function(value, metaData, record, rowIndex, colIndex, store){
                        return states[value].label;
                    }
                }, {
                    header: 'Type de transition',
                    dataIndex: 'transitionType',
                    id: 'transitionType',
                    renderer: function(value, metaData, record, rowIndex, colIndex, store){
                        if (transitionTypeStore.getById(value)) {
                            return transitionTypeStore.getById(value).get('label');
                        }
                        return '';
                    }
                }],
                
                bbar: [gNewButton],
                
                flex: 1,
                
                sm: new Ext.grid.RowSelectionModel({
                    singleSelect: true,
                    listeners: {
                        rowselect: function(sm, row, rec){
                        
                            transitionEditor.getForm().loadRecord(rec);
                            
                            
                            gDeleteButton.currentRecord = rec;
                            gDeleteButton.enable();
                            
                            gCreateButton.disable();
                            gNewButton.enable();
                            
                            transitionEditor.enable();
                            gStartField.disable();
                            gFinishField.disable();
                            gTypeField.disable();
                            
                        }
                    }
                })
            
            });
            
            
            var gCreateButton = new Ext.Button({
                text: 'Créer',
                icon: 'ECM/Images/add.png',
                handler: function(b, e){
                
                    var values = transitionEditor.getForm().getValues();
                    
                    if (workflow.addTransition({
                        start: values.start,
                        finish: values.finish,
                        transitionType: values.transitionType
                    })) {
                    
                        var dataItem = {
                            start: values.start,
                            finish: values.finish,
                            transitionType: values.transitionType
                        };
                        
                        transitionStore.add(new transitionStore.recordType(dataItem));
                        
                        resetTransitionForm();
                        
                        workflowGraphWiz.renderMedia();
                        
                    }
                    else {
                        Ext.Msg.alert('Erreur', Fdl.getLastErrorMessage());
                    }
                    
                },
                disabled: true
            });
            
            var gDeleteButton = new Ext.Button({
                text: 'Supprimer',
                icon: 'ECM/Images/delete.png',
                handler: function(b, e){
                
                    //var values = transitionEditor.getForm().getValues();
                    
                    if (workflow.removeTransition({
                        start: gDeleteButton.currentRecord.get('start'),
                        finish: gDeleteButton.currentRecord.get('finish')
                    })) {
                    
                        transitionStore.remove(gDeleteButton.currentRecord);
                        gDeleteButton.currentRecord = null;
                        
                        resetTransitionForm();
                        
                        workflowGraphWiz.renderMedia();
                    }
                    else {
                        Ext.Msg.alert('Erreur', Fdl.getLastErrorMessage());
                    }
                    
                },
                disabled: true
            });
            
            var resetTransitionForm = function(){
            
                transitionEditor.getForm().reset();
                
                gCreateButton.disable();
                gDeleteButton.disable();
                gNewButton.enable();
                
                transitionList.getSelectionModel().clearSelections();
                
                transitionEditor.disable();
                
            };
            
            Ext.fdl.StateSelector = Ext.extend(Ext.form.ComboBox, {
            
                initComponent: function(){
                    Ext.fdl.StateSelector.superclass.initComponent.call(this);
                },
                
                editable: false,
                forceSelection: true,
                disableKeyFilter: true,
                triggerAction: 'all',
                mode: 'local',
                
                anyMatch: true, // not ComboBox-native, see override
                store: stateStore,
                
                anchor: '-7',
                
                valueField: 'key',
                displayField: 'label'
            
            });
            
            Ext.fdl.TransitionTypeSelector = Ext.extend(Ext.form.ComboBox, {
            
                initComponent: function(){
                    Ext.fdl.TransitionTypeSelector.superclass.initComponent.call(this);
                    
                    this.on({
                        select: {
                            fn: function(combo, record, index){
                            
                            }
                        }
                    });
                    
                },
                
                editable: false,
                forceSelection: true,
                disableKeyFilter: true,
                triggerAction: 'all',
                mode: 'local',
                
                anyMatch: true, // not ComboBox-native, see override
                store: transitionTypeStore,
                
                anchor: '-7',
                
                valueField: 'key',
                displayField: 'label'
            
            });
            
            var gTypeField = new Ext.fdl.TransitionTypeSelector({
                fieldLabel: 'Type',
                hiddenName: 'transitionType',
                name: 'transitionType',
                anchor: '-7'
            });
            
            var gStartField = new Ext.fdl.StateSelector({
                fieldLabel: 'Début',
                name: 'start',
                hiddenName: 'start',
                anchor: '-7'
            });
            
            var gFinishField = new Ext.fdl.StateSelector({
                fieldLabel: 'Fin',
                name: 'finish',
                hiddenName: 'finish',
                anchor: '-7'
            });
            
            var transitionEditor = new Ext.FormPanel({
            
                items: [gTypeField, gStartField, gFinishField],
                
                bbar: [gCreateButton, gDeleteButton],
                
                height: 146,
                width: '100%',
                bodyStyle: 'padding:10px;',
                
                disabled: true
            
            });
            
            // eo Code for Graph
            
            var workflowGraphWiz = new Ext.ux.MediaPanel({
                xtype: 'mediapanel',
                style: 'overflow:auto;',
                //bbar: [],
                flex: 1,
                mediaCfg: {
                    mediaType: 'PNG',
                    //url: Fdl.context.url + '?sole=Y&&app=FDL&action=VIEW_WORKFLOW_GRAPH&type=complet&format=png&size=' + computeGraphSize() + '&ratio=fill&orient=TB&id=' + id,
                    style: { //width: '100%'
}
                },
                listeners: {
                    resize: function(){
                        var url = Fdl.context.url + '?sole=Y&&app=FDL&action=VIEW_WORKFLOW_GRAPH&type=complet&format=png&size=' + computeGraphSize() + '&ratio=fill&orient=TB&id=' + id;
                        this.mediaCfg.url = url;
                        this.renderMedia();
                    }
                }
            });
            
            var win = new Ext.Window({
                title: 'Cycle ' + workflow.getTitle(),
                layout: 'hbox',
                width: 600,
                height: 400,
                maximizable: true,
                constrain: true,
                renderTo: Fdl.ApplicationManager.desktopPanel.body,
                layoutConfig: {
                    align: 'stretch',
                    pack: 'start'
                },
                
                items: [{
                    id: 'workflow-editor',
                    xtype: 'tabpanel',
                    activeTab: 0,
                    frame: true,
                    autoScroll: true,
                    
                    width: 300,
                    //				    style: 'width:100%;',
                    items: [{
                        xtype: 'panel',
                        title: 'Etats',
                        layout: 'vbox',
                        style: 'width:100%;',
                        items: [stateList, stateEditor]
                    }, {
                        xtype: 'panel',
                        title: 'Transitions',
                        layout: 'vbox',
                        style: 'width:100%;',
                        items: [transitionTypeList, transitionTypeEditor]
                    }, {
                        xtype: 'panel',
                        title: 'Graphe',
                        layout: 'vbox',
                        style: 'width:100%;',
                        items: [transitionList, transitionEditor]
                    }]
                }, workflowGraphWiz]
            });
            
            win.show();
            
        }
        else {
            Ext.Msg.alert('Erreur', Fdl.getLastErrorMessage());
        }
        
    }
    
});

Ext.fdl.KeyBoard = new Ext.util.Observable ({
	
});

Ext.fdl.KeyBoard.keys = {};

Ext.EventManager.on(document,'keydown',function(e){	
	 //Don't enable shortcut keys in Input, Textarea fields
//	var element;
//	if(e.target) element=e.target;
//	else if(e.srcElement) element=e.srcElement;
//	if(element.nodeType==3) element=element.parentNode;
//	if(element.tagName == 'INPUT' || element.tagName == 'TEXTAREA'){ return;}
	
	Ext.fdl.KeyBoard.keys[e.getKey()] = true;
	//console.log('KEYCHANGE',Ext.fdl.KeyBoard.keys);
	Ext.fdl.KeyBoard.fireEvent('keychange',Ext.fdl.KeyBoard.keys);
});

Ext.EventManager.on(document,'keyup',function(e){	
	//Don't enable shortcut keys in Input, Textarea fields
//	var element;
//	if(e.target) element=e.target;
//	else if(e.srcElement) element=e.srcElement;
//	if(element.nodeType==3) element=element.parentNode;
//	if(element.tagName == 'INPUT' || element.tagName == 'TEXTAREA'){ return;}
	
	delete Ext.fdl.KeyBoard.keys[e.getKey()];
	//console.log('KEYCHANGE',Ext.fdl.KeyBoard.keys);
	Ext.fdl.KeyBoard.fireEvent('keychange',Ext.fdl.KeyBoard.keys);
});

Ext.EventManager.on(document,'mousedown',function(e){	
	window.focus();
});

window.focus();
