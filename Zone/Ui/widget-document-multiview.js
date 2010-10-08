
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

Ext.fdl.DocumentMultiView = Ext.extend(Ext.Panel, {

	document: null,
	
	context: null,
	
	forceExt: false,
	forceClassic: false,
	
	mode: 'view',
	
	layout: 'fit',
		
	toString: function(){
		return 'Ext.fdl.DocumentMultiView';
	},
	
	initComponent: function(){
		
		this.context = this.document.context;
		    
        Ext.fdl.DocumentMultiView.superclass.initComponent.call(this);
        
		this.on({            
            afterrender: {
                fn: function(){
					                
                    this.display();
                    this.doLayout();
                    
                }
            }
        
        });
		
	},
	
	setDocument: function(doc){
		this.document = doc;
	},
	
	display : function(){
		
		var me = this ;
		
		var tabItems = [];
		console.log('DOCUMENT',this,this.document);
		if (this.document.isCollection()) {
		
			if ( (!this.view) || this.view == 'grid') {
			
				var cWid = new Ext.fdl.GridCollection({
					collection: this.document,
					//height: this.getHeight(),
					tBar: true,
					filterColumns: true
				});
				
				var contentPanel = cWid.display();
				
			}
			
			if( this.view == 'icon') {
				var cWid = new Ext.fdl.IconCollection({
					collection: this.document,
					//height: this.getHeight(),
					tBar: true,
					usePaging: true
				});
				
				var contentPanel = cWid.display();
			}
			
			if (this.view == 'tree') {
				
				var cWid = new Ext.fdl.TreeCollection({
					collection: this.document,
					//height: this.getHeight(),
					tBar: true,
					onlyCollection: false
				});
				
				var contentPanel = cWid.display();
			}
						
			var collectionPanel = new Ext.fdl.CollectionContainer({
				collectionPanel: cWid
			});
			
			collectionPanel.setTitle(this.context._("eui::Content"));
			
			tabItems.push(collectionPanel);
			
		}
		
		var infoPanel = new Ext.fdl.Document({
			document: this.document,
			mode: this.mode,
			config: this.config,
			forceExt: this.forceExt,
			forceClassic: this.forceClassic
		});
		
		infoPanel.setDocument = function (doc){
        	Ext.fdl.Document.prototype.setDocument.call(this,doc);
        	me.setDocument(doc);
        };
		
		infoPanel.setTitle(this.context._("eui::Informations"));
		
		tabItems.push(infoPanel);
		
		var activeTab = 0 ;
		
		this.tabPanel = new Ext.TabPanel({
			activeTab: this.mode == 'edit' ? 1 : 0,
			border: false,
			items: tabItems
		});
		
		this.add(this.tabPanel);
		
	}


});