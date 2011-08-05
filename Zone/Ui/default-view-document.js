
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

// Override to modify render of required item labels in form
//Ext.apply(Ext.layout.FormLayout.prototype, {
//    originalRenderItem: Ext.layout.FormLayout.prototype.renderItem,
//    renderItem: function(c, position, target){
//        if (c && !c.rendered && c.isFormField && c.fieldLabel && c.allowBlank === false) {
//            c.fieldLabel = " <b>" + c.fieldLabel + "</b> ";
//        }
//        this.originalRenderItem.apply(this, arguments);
//    }
//});

Ext.fdl.DocumentDefaultView = {
    display: function(){
    
        //this.add(this.getHeader());
        
        var panel = null;
        
        this.add(this.renderToolbar());
        
        // Ordered attribute array
        var ordered = this.orderAttribute();
        //var ordered = this.document.getAttributes();
        
        for (var i = 0; i < ordered.length; i++) {
            var curAttr = ordered[i];
            var widget = this.getExtValue(curAttr.id);
            
            switch (curAttr.type) {
            
                case 'text':
                case 'longtext':
                case 'htmltext':
                case 'integer':
                case 'double':
                case 'money':
                case 'date':
                case 'time':
                case 'timestamp':
                case 'password':
                case 'image':
                case 'array':
                case 'color':
                    break;
                    
                case 'menu':
                    //                    if (widget != null) {
                    //                        menu.add(widget);
                    //                    }
                    break;
                    
                case 'frame':
                    if (widget != null && curAttr.parentId == 0) {
                        this.add(widget);
                    }
                    break;
                    
                case 'tab':
                    if (widget != null) {
                        if (panel == null) {
                            panel = new Ext.TabPanel({
                                activeTab: 0,
                                bodyStyle: 'margin-bottom:10px;',
                                autoHeight: true,
                                border: false,
                                
                                // this line is necessary for anchoring to work at
                                // lower level containers and for full height of tabs
                                //anchor: '100% 100%',
                                
                                // only fields from an active tab are submitted
                                // if the following line is not present
                                deferredRender: false
                            });
                            this.add(panel);
                        }
                        
                        panel.add(widget);
                    }
                    break;
                    
            }
        };
        
            }
    
};

Ext.fdl.DocumentDefaultEdit = {
    display: function(){
    
        var mode = this.document.getProperty('id') ? 'edit' : 'create';
        
        //this.add(this.getHeader());
        
        var menu = new Ext.Toolbar({
            style: 'margin-bottom:10px;'
        });
		
		if (this.displayToolbar) {
			this.add(menu);
		}
        
        if (mode == 'edit') {
        
            menu.add(new Ext.Button({
                text: 'Sauver',
                scope: this,
                handler: function(){
                    var form = this.getForm().getEl().dom;
                    
                    var panel = this;
                    
                    this.document.save({
                        form: form,
                        callback: function(doc){
                            //Fdl.ApplicationManager.notifyDocument(panel.document);
                    		panel.switchMode('view');
                        }
                    });
                }
            }));
            
            menu.add(new Ext.Button({
                text: 'Annuler',
                scope: this,
                handler: function(){
                    //                    if (!this.document.isCollection()) {
                    //                        this.switchMode('view');
                    //                    }
                    //                    else {
//                    Fdl.ApplicationManager.windows[this.document.id].mode = 'view';
//                    Fdl.ApplicationManager.windows[this.document.id].updateDocument(this.document.id);
                    //                    }
					this.switchMode('view');
                }
            }));
            if (this.document.getProperty('postitid').length > 0) {
                menu.add(new Ext.Button({
                    scope: this,
                    tooltip: 'Afficher/Cacher les notes',
                    text: 'Notes',
                    icon: 'Images/simplenote16.png',
                    handler: function(){
                        this.displaynotes = (!this.displaynotes);
                        this.viewNotes({
                            undisplay: (!this.displaynotes)
                        });
                    }
                }));
            }
            
        }
        
        if (mode == 'create') {
        
            menu.add(new Ext.Button({
                text: 'Créer',
                scope: this,
                handler: function(){
                    var form = this.getForm().getEl().dom;
                                        
                    this.document.save();
                    
                    this.document.save({
                        form: form,
                        callback: function(doc){							
                            var c = doc.context.getDesktopFolder();
                            c.insertDocument({
                                id: doc.getProperty('id')
                            });
                            if(window.updateDesktop){
                            	window.updateDesktop();
                            }
                        }
                    });
                    
                    this.close();
                                        
                }
            }));
            
            menu.add(new Ext.Button({
                text: 'Annuler',
                scope: this,
                handler: function(){
            		this.close();
                }
            }));
            
        }
        
        var panel = null;
        
        // Ordered attribute array
        var ordered = this.orderAttribute();
        
        for (var i = 0; i < ordered.length; i++) {
            var curAttr = ordered[i];
            var widget = this.getExtInput(curAttr.id);
            
            switch (curAttr.type) {
            
                case 'text':
                case 'longtext':
                case 'htmltext':
                case 'integer':
                case 'double':
                case 'money':
                case 'date':
                case 'time':
                case 'timestamp':
                case 'password':
                case 'image':
                case 'array':
                case 'color':
                    break;
                    
                case 'menu':
                    if (widget != null) {
                        menu.add(widget);
                    }
                    break;
                    
                case 'frame':
                    if (widget != null && curAttr.parentId == 0) {
                        this.add(widget);
                    }
                    break;
                    
                case 'tab':
                    if (widget != null) {
                        if (panel == null) {
                            panel = new Ext.TabPanel({
                                activeTab: 0,
                                bodyStyle: 'margin-bottom:10px;',
                                autoHeight: true,
                                border: false,
                                
                                // this line is necessary for anchoring to work at
                                // lower level containers and for full height of tabs
                                //anchor: '100% 100%',
                                
                                // only fields from an active tab are submitted
                                // if the following line is not present
                                deferredRender: false
                            });
                            this.add(panel);
                        }
                        panel.add(widget);
                    }
                    break;
            }
        };
        
            }
};
