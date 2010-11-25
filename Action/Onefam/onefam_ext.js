ONEFAM = {};

ONEFAM.start = function(){
	
	Ext.QuickTips.init();
	
	ONEFAM.context = new Fdl.Context({
	    url: window.location.pathname
	});
	
	console.log('ONEFAM object', ONEFAM);
	
	ONEFAM.ui = new Ext.fdl.Interface({
		context: ONEFAM.context,
		onOpenDocument: function(widget, id, mode, config){
			console.log('OPEN DOCUMENT', widget, id, mode, config);
			ONEFAM.navigator.openDocument(id, mode, config);
		}
	});
	
	ONEFAM.navigator = new Ext.fdl.FamilyXplorer({
		context: ONEFAM.context,
		application: ONEFAM.applicationName,
		applicationLabel: ONEFAM.applicationLabel,
		border: false,
		familyTree: ONEFAM.familyTree,
		familyTreeDataConfig: {
	        app: ONEFAM.applicationName,
	        action: 'ONEFAM_GETTREEFAMILY',
	        appid: ONEFAM.applicationName,
	    },
		canEditMasterFamilies: ONEFAM.canEditMasterFamilies,
		canEditUserFamilies: ONEFAM.canEditUserFamilies,
		displayConfig: ONEFAM.displayConfig
	});
	
	ONEFAM.navigator.canDisplayFamilyProvider = true;
	
	ONEFAM.navigator.onAdminFamily = function(){
	    
	    if(ONEFAM.navigator.canDisplayFamilyProvider){
            
            ONEFAM.navigator.canDisplayFamilyProvider = false ;
    	    
    	    var me = ONEFAM.navigator;
    	    
    	    var sfam = me.context.retrieveData({
                app: me.application,
                action: 'ONEFAM_EXT_GETMASTERPREF'
            });
                                
    	    var provider = new Ext.fdl.FamilyProvider({
                context: me.context,
                application: me.application,
                documentList: sfam.ids,            
                onChange: function(selection){
                            
                    if(!me.context.retrieveData({
                        app: me.application,
                        action: 'ONEFAM_EXT_MODMASTERPREF',
                        idsfam: JSON.stringify(selection.selectionItems)
                    })){
                        Ext.Msg.alert('freedom','Error on family parameter save');
                    } else {
                        ONEFAM.navigator.familyTreePanel.reload();
                    }
                        
                }
            });
            
            var panel = new Ext.Panel({
                layout: 'fit',
                width: 200,
                height: 300,
                border: false,
                tools: [{
                    id: 'close',
                    handler: function(event,toolEl,panel){
                        ONEFAM.navigator.familyTreePanel.show();
                        ONEFAM.navigator.leftContainer.remove(panel);
                        ONEFAM.navigator.leftContainer.doLayout();
                        
                        ONEFAM.navigator.canDisplayFamilyProvider = true;
                        
                    }
                }],
                title: 'Editer les familles utilisateur',
                items: [provider]
            });
                    
            ONEFAM.navigator.familyTreePanel.hide();
            ONEFAM.navigator.leftContainer.add(panel);
            ONEFAM.navigator.leftContainer.doLayout();
                            
//        var window = new Ext.Window({
//            layout: 'fit',
//            width: 200,
//            height: 300,
//            border: false,
//            title: 'Editer les familles administrateur',
//            items: [ provider]
//        });
//        
//        window.show();
            
	    }
	}
	
	ONEFAM.navigator.onUserFamily = function(){
	    
	    if(ONEFAM.navigator.canDisplayFamilyProvider){
            
            ONEFAM.navigator.canDisplayFamilyProvider = false ;
    	 
    	    var me = ONEFAM.navigator;
    	    
    	    var sfam = me.context.retrieveData({
                app: me.application,
                action: 'ONEFAM_EXT_GETPREF'
            });
                                
    	    var provider =  new Ext.fdl.FamilyProvider({
                context: me.context,
                application: me.application,
                documentList: sfam.ids,            
                onChange: function(selection){
                            
                    if(!me.context.retrieveData({
                        app: me.application,
                        action: 'ONEFAM_EXT_MODPREF',
                        idsfam: JSON.stringify(selection.selectionItems)
                    })){
                        Ext.Msg.alert('freedom','Error on family parameter save');
                    } else {
                        ONEFAM.navigator.familyTreePanel.reload();   
                    }
                        
                }
            });
            
            var panel = new Ext.Panel({
                layout: 'fit',
                width: 200,
                height: 300,
                border: false,
                tools: [{
                    id: 'close',
                    handler: function(event,toolEl,panel){
                        ONEFAM.navigator.familyTreePanel.show();
                        ONEFAM.navigator.leftContainer.remove(panel);
                        ONEFAM.navigator.leftContainer.doLayout();
                        
                        ONEFAM.navigator.canDisplayFamilyProvider = true ;
                        
                    }
                }],
                title: 'Editer les familles utilisateur',
                items: [provider]
            });
            
            ONEFAM.navigator.familyTreePanel.hide();
            ONEFAM.navigator.leftContainer.add(panel);
            ONEFAM.navigator.leftContainer.doLayout();
                        
//    var window = new Ext.Window({
//        layout: 'fit',
//        width: 200,
//        height: 300,
//        border: false,
//        title: 'Editer les familles utilisateur',
//        items: [provider]
//    });
//    
//    window.show();
    
        }
	    
	}
	
	ONEFAM.navigator.onDisplayConfigChange = function(config){
	    
	    console.log('On Display Config Change', config);
	    
	    (function(){
	        ONEFAM.context.retrieveData({
    	        app: ONEFAM.applicationName,
                action: 'ONEFAM_EXT_SETDISPLAYCONFIG',
                config: JSON.stringify(config)
    	    });
	    }).defer(10);
	    
	};
		
	ONEFAM.viewport = new Ext.Viewport({
        layout: 'fit',
        renderTo: Ext.getBody(),
        items: [ONEFAM.navigator]
    });
	
	Ext.get('loading').fadeOut({
    	remove:true
    });
	
};