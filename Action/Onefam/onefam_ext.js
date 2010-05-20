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
	
	ONEFAM.navigator = new Ext.fdl.FamilyNavigator({
		context: ONEFAM.context,
		application: ONEFAM.applicationName,
		border: false,
		familyTree: ONEFAM.familyTree,
		canEditMasterFamilies: ONEFAM.canEditMasterFamilies,
		canEditUserFamilies: ONEFAM.canEditUserFamilies
	});
	
	ONEFAM.viewport = new Ext.Viewport({
        layout: 'fit',
        renderTo: Ext.getBody(),
        items: [ONEFAM.navigator]
    });
	
	Ext.get('loading').fadeOut({
    	remove:true
    });
	
};