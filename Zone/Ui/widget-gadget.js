
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

// -----------------------------------
// First gadget : collection
Ext.fdl.gadgetCollection = function(config,context){
    var f = context.getDocument({
        id: config.collectionId
    });
    //console.log('gadget collec',config,f);
    var grid = new Ext.fdl.ServiceView({
        collection: f,
        iconSize: 16,
        pageSize: 50
    });	
	
    console.log('gadget collection',grid,grid.initialConfig);
    
    return grid;
	
};

Ext.fdl.ServiceView = Ext.extend(Ext.fdl.GridCollection, {
    iconSize: 16,
    viewToolbar: false,
    hideHeaders: true,
    getTitle: function(){
        return this.collection.getTitle();
    }
});


// -----------------------------------
// Second gadget : iGoogle
Ext.fdl.gadgetiGoogleView = function(up){
    var jsiggogle = up.url;
    if (jsiggogle.indexOf('<script src="') >= 0) {
        var gi = jsiggogle.indexOf('"'); // delete script tag
        jsiggogle = jsiggogle.substring(gi + 1);
        gi = jsiggogle.lastIndexOf('"');
        jsiggogle = jsiggogle.substring(0, gi);
    }
    
    var tab2 = new Ext.Panel({
        html: '<iframe frameborder="none"  src="lib/ui/widget-services.php?js=' + encodeURIComponent(jsiggogle) + '" style="width:100%;height:100%;border:none;margin:0px;padding:0px"></iframe>',
        
        listeners: {
            render: function(o){
                //		this.ownerCt.setTitle(up.title);
            }
        },
        getTitle: function(){
            return up.title;
        }
    });
    
    return tab2;
};
Ext.fdl.gadgetiGoogleParam = function(up){
    var jsiggogle = up.url;
    
    var wig = new Ext.FormPanel({
        //	style:'border:solid 2px red',
        
        //	layout:'fit',
        //anchor:'100% -100',
        
        items: [{
            xtype: 'textfield',
            fieldLabel: 'Titre',
            value: up.title,
            anchor: '100%',
            name: 'title'
        }, {
            xtype: 'textarea',
            anchor: '100% 100%',
            //grow:true,
            fieldLabel: '<a title="Rechercher un gadget" target="_blank" href="http://www.google.com/ig/directory?synd=open&hl=fr">google script</a>',
            value: jsiggogle,
            name: 'igurl'
        }],
        listeners: {
            render: function(o){
                //console.log(this);
            }
        }
    });
    
    return wig;
};

