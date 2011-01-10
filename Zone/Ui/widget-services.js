
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */





Ext.fdl.Service = function(config,context){
	
	console.log('Service Context',context);
	
	var callback = function(){
	
	    if (config.gadget) {
	    	
	    	var extview = eval(config.gadget.extview);
	    	if(!extview){
	    		Ext.Msg.alert('Error','Error : Gadget View not available.');
	    	}
	    
	        var gview = eval('new ' + config.gadget.extview + '(config.gadget.userPref,context)');
	        
	        console.log('GVIEW',config.gadget.extview, config.gadget.userPref, gview);
	        
	        var gtools = [];
	        
	        if (config.gadget.hasParameters) 
	            gtools = [{
	                id: 'gear',
	                qtip: 'Paramètres',
	                // hidden:true,
	                handler: function(event, toolEl, panel){
	                    // refresh logic
	                    var wparam = eval('new ' + config.gadget.extparam + '(config.gadget.userPref)');
	                    panel.removeAll();
	                    
	                    var pparam = new Ext.Panel({
	                        //			style:'border:solid 2px blue',
	                        anchor: '100% 100%'
	                    });
	                    
	                    var menu = new Ext.Toolbar({
	                        style: 'margin-bottom:10px;'
	                    });
	                    
	                    menu.add(new Ext.Button({
	                        text: 'Sauver',
	                        handler: function(){
	                            if (wparam) {
	                                var vv = wparam.getForm().getValues();
	                                
	                                config.gadget.userPref.url = vv.igurl;
	                                config.gadget.userPref.title = vv.title;
	                                ecm.updateSessionGadget(config.gadget);
	                                var gview = eval('new ' + config.gadget.extview + '(config.gadget.userPref)');
	                                panel.removeAll();
	                                panel.add(gview);
	                                panel.setTitle(gview.getTitle());
	                                panel.doLayout();
	                            }
	                        }
	                    }));
	                    
	                    menu.add(new Ext.Button({
	                        text: 'Annuler',
	                        scope: this,
	                        handler: function(){
	                            var gview = eval('new ' + config.gadget.extview + '(config.gadget.userPref)');
	                            panel.removeAll();
	                            panel.add(gview);
	                            panel.doLayout();
	                        }
	                    }));
	                    
	                    pparam.add(menu);
	                    pparam.add(wparam);
	                    panel.add(pparam);
	                    panel.doLayout();
	                }
	            }];
	        
	        var serwin = new Ext.Window({
	            layout: 'fit',
	            title: gview.getTitle(),
	            //closeAction: 'hide',
	            width: 270 + 17,
	            height: 150 + 25,
	            resizable: true,
	            plain: true,
	            renderTo: Fdl.ApplicationManager.desktopPanel.body,
	            constrain: true,
	            cls: 'x-fdl-service',
	            items: [gview],
	            shadow: false,
	            gadget: config.gadget,
	            listeners: {
	                move: function(o){
	                    o.toBack();
	                    this.gadget.width = o.getWidth();
	                    this.gadget.height = o.getHeight();
	                    this.gadget.position = o.getPosition(true);
	                    ecm.updateSessionGadget(this.gadget);
	                },
	                close: function(o){
	                    ecm.removeSessionGadget(this.gadget);
	                }
	            },
	            tools: gtools
	        });
	        
	        if (config.gadget.width) 
	            serwin.setWidth(config.gadget.width);
	        if (config.gadget.height) 
	            serwin.setHeight(config.gadget.height);
	        if (config.gadget.position) 
	            serwin.setPosition(config.gadget.position[0], config.gadget.position[1]);
	            
	        serwin.show();    
	            
	        return serwin;
	    }
	    else {
	        Ext.Msg.alert('No gadget');
	        return false;
	    }
	    
	};
		
	// Default javascript source for gadgets.
	if(!config.gadget.javascript){
		config.gadget.javascript = 'lib/ui/widget-gadget.js';
	}
	
	if(config.gadget && config.gadget.javascript){
		console.log('Loading javascript file for gadget', context.url + config.gadget.javascript);
		Ext.ensure({
			js: context.url + config.gadget.javascript,
			callback: function(){
				callback();
			}
		});
	} else {
		return callback();
	}	
    
};

