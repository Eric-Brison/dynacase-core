
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @author Clément Laballe
 */
Ext.fdl.MenuManager = {

	getMenuItem: function(menuObject,config){
										
		if(!config){
			config = {};
		}
		
		var subMenu = null ;
		
		if(menuObject.items){
			
			var subItems = [];
			
			for(var subname in menuObject.items){
				var subMenuObject = menuObject.items[subname];
				var subMenuItem = Ext.fdl.MenuManager.getMenuItem(subMenuObject,config);
				subItems.push(subMenuItem);
			}
			
			if(subItems.length != 0){
				subMenu = new Ext.menu.Menu({
					items: subItems
				});
			}
			
		}
		
		var style = '';
		if(menuObject.backgroundColor){
			style += 'background-color:'+menuObject.backgroundColor+';';
		}
		
		if (menuObject.type == 'separator'){
			var menuItem = '-' ;
		} else if (menuObject.type == 'text'){
			var menuItem = {
				text: Ext.util.Format.capitalize(menuObject.label),
				menuObject: menuObject,
				disabled: true,
				disabledClass: '',
				cls: 'x-hover-disabled',
				hidden: (menuObject.visibility == 2),
				style: style,				
				icon: menuObject.icon ? config.context.resizeImage(menuObject.icon, 16) : ''
			};
		} else if(menuObject.type == 'item' || menuObject.type == 'menu' || true){
			
			var menuItem = {
				text: Ext.util.Format.capitalize(menuObject.label),
				menuObject: menuObject,
				disabled: (menuObject.visibility == 0 || menuObject.visibility == 4),
				hidden: (menuObject.visibility == 2),
				style: style,
				icon: menuObject.icon ? config.context.resizeImage(menuObject.icon, 16) : '',
				menu: subMenu ? subMenu : false,
				handler: subMenu ? null : function(button,event){
									
					var execute = function(){
						
						console.log('MENU',button.menuObject,config);
						
						if(button.menuObject.script){
							
							Ext.ensure({
								js: config.context.url + button.menuObject.script.file,
								callback: function(){
									
									var selection = config.selection ;
									if(!selection && (config.widgetCollection && config.widgetCollection.selection)){
										selection = config.widgetCollection.selection;
									}
									
									if(selection){
										if(selection.mainSelector == 'none' && selection.selectionItems.length == 1){
											var selection = null;
										}
									}
									
									console.log('MENU OBJECT',button.menuObject);
																		
									var action = new (eval('('+button.menuObject.script['class']+')'))({
										context: config.context,
										document: config.context.getDocument({
											id: config.documentId,
											useCache: true
										}),
										widgetDocument: config.widgetDocument,
										search: config.search,										
										selection: selection,
										collection: config.collection,
										widgetCollection: config.widgetCollection,
										widgetCollectionContainer: config.widgetCollectionContainer,
										parameters: button.menuObject.script ? button.menuObject.script.parameters : null
									});
									
									console.log('ACTION',action);
									
									var preCondition = action.preCondition();
									
									console.log('PRECONDITION',preCondition);
									
									if(preCondition){
										action.process();
									};
								}
							});									
							
						} else if(button.menuObject.javascript){
															
							// There is a media object only in document menu
							if(config.mediaObject){
								config.mediaObject.purgeAllListeners();
								
								config.mediaObject.on('load',function(){
									console.log('media-object-load');									
									config.widgetDocument.publish('modifydocument',config.widgetDocument.document);
									config.widgetDocument.generateMenu(config.panel,config.menu,config.mediaObject);
								});
								
								config.mediaObject.dom.contentWindow.eval(button.menuObject.javascript);
							} else {
								eval(button.menuObject.javascript);
							}
							
						} else if(button.menuObject.url){
							
							if(config.documentId){
								var doc = config.context.getDocument({
									id: config.documentId,
									useCache: true
								});
							}
							var url;
							// Detect if url is absolute or relative and concatene context url if appropriate.
							if((button.menuObject.url.indexOf('://') == -1) && (button.menuObject.url.indexOf('javascript:') == -1)){
								 url = config.context.url + button.menuObject.url;
							} else {
								 url = button.menuObject.url;
							}
							
							if(button.menuObject.target == '_self' && config.widgetDocument){
								
								if(config.panel){
								
									config.panel.purgeListeners();
									
									config.panel.on('mediaload',function(panel,mediaObject){
										
										// This is used to register the media object in parent panel (certain methods of Ext.fdl.Document needs it)
										if(panel.ownerCt){
											panel.ownerCt.mediaObject = mediaObject;
										}
										
										config.widgetDocument.generateMenu(panel,config.menu,mediaObject);
										
										addEvent(mediaObject.dom,'load',function(){
					            			var menu = config.widgetDocument.getTopToolbar();            		
					            			config.widgetDocument.generateMenu(panel,menu,mediaObject);
					            		});
										
									});
									
									if(button.menuObject.url){
										config.panel.renderMedia({
											mediaType: 'HTM',
											url: url,
											width: '100%',
											height: '100%'
										});
									}
								
								}
								
							} else if(button.menuObject.target == '_hidden'){
								
								open(url, Fdl.getHiddenTarget());
								
							} else {
								
								if(config.widgetDocument){
									config.widgetDocument.displayUrl(url,button.menuObject.target,{
										title: config.document ? (config.document.getTitle() + ' : ' + button.menuObject.label) : button.menuObject.label
									});
								}
								
								if(config.widgetCollection){
									config.widgetCollection.displayUrl(url,button.menuObject.target,{
										title: config.collection ? (config.collection.getTitle() + ' : ' + button.menuObject.label ) : button.menuObject.label
									});
								}
								
							}
														
						}						
						
					};
				
					if(button.menuObject.confirm){
					
						Ext.Msg.show({
							title: 'freedom',
							msg: button.menuObject.confirm.label,
							buttons: {
								ok: button.menuObject.confirm['continue'],
								cancel: button.menuObject.confirm.cancel
							},
							fn: function(buttonId){
								if(buttonId == 'ok'){
									execute();
								}
							}
						});
						
					} else {
						execute();
					}								
					
				}
			};
		
		}
		
		// See http://www.extjs.com/forum/showthread.php?t=77312
		if(menuObject.description){
//		    console.log('DESCRIPTION TOOLTIP',menuObject.description);
			if(Ext.QuickTips){ // fix when qtip is not already initialized
			    Ext.QuickTips.init();
			}

			menuItem.tooltip = {
				text: menuObject.description
			};
		}
		
		return menuItem ;

	}
	
};
