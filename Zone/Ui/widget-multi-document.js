
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */


/**
 * Override to allow using icons in TabPanel
 * http://www.extjs.com/forum/showthread.php?t=59154
 * Ext JS team argues that icons should be handled in css.
 * In our case it is not true. We get documents which have their own icons. So we cannot have a predefined css which matches every document in the freedom system.
 */
Ext.override(Ext.TabPanel, {
  changeTabIcon: function(item, icon){
    var el = this.getTabEl(item);
    if(el){
        Ext.fly(el).addClass('x-tab-with-icon').child('span.x-tab-strip-text').setStyle({backgroundImage:'url('+icon+')'});
    }
  }
});

/**
 * SlidingTabPanel is not compatible with drag a drop z-index correction
 */

//Ext.ns("Ext.ux");
//
//Ext.ux.SlidingTabPanel = Ext.extend(Ext.TabPanel, {
//    
//    initTab: function(item, index){
//        Ext.ux.SlidingTabPanel.superclass.initTab.call(this, item, index);
//        var p = this.getTemplateArgs(item);
//        if(!this.slidingTabsID) this.slidingTabsID = Ext.id(); // Create a unique ID for this tabpanel
//        new Ext.ux.DDSlidingTab(p, this.slidingTabsID, {
//            tabpanel:this // Pass a reference to the tabpanel for each dragObject
//        });
//    }
//    
//});
//
//Ext.ux.DDSlidingTab = Ext.extend(Ext.dd.DDProxy, {
//    
//    // Constructor
//    constructor: function() {
//        Ext.ux.DDSlidingTab.superclass.constructor.apply(this, arguments);
//        this.setYConstraint(0,0,0); // Lock the proxy to its initial Y coordinate
//        
//        // Create a convenient reference to the tab's tabpanel
//        this.tabpanel = this.config.tabpanel;
//        
//        // Set the slide duration
//        this.slideDuration = this.tabpanel.slideDuration;
//        if(!this.slideDuration) this.slideDuration = .1;
//    }
//    
//    // Pseudo Private Methods
//    ,handleMouseDown: function(e, oDD){
//        if(this.primaryButtonOnly && e.button != 0) return;
//        if(this.isLocked()) return;
//        this.DDM.refreshCache(this.groups);
//        var pt = new Ext.lib.Point(Ext.lib.Event.getPageX(e), Ext.lib.Event.getPageY(e));
//        if (!this.hasOuterHandles && !this.DDM.isOverTarget(pt, this) )  {
//        } else {
//            if (this.clickValidator(e)) {
//                this.setStartPosition(); // Set the initial element position
//                this.b4MouseDown(e);
//                this.onMouseDown(e);
//                this.DDM.handleMouseDown(e, this);
//                // this.DDM.stopEvent(e); // Must remove this event swallower for the tabpanel to work
//            }
//        }
//    }
//    ,startDrag: function(x, y) {
//        Ext.dd.DDM.useCache = false; // Disable caching of element location
//        Ext.dd.DDM.mode = 1; // Point mode
//        
//        this.proxyWrapper = Ext.get(this.getDragEl()); // Grab a reference to the proxy element we are creating
//        this.proxyWrapper.update(); // Clear out the proxy's nodes
//        this.proxyWrapper.applyStyles('z-index:1001;border:0 none;');
//        this.proxyWrapper.addClass('tab-proxy');
//            
//            // Use 2 nested divs to mimic the default tab styling
//            // You may need to customize the proxy to get it to look like your custom tabpanel if you use a bunch of custom css classes and styles
//        this.stripWrap = this.proxyWrapper.insertHtml('afterBegin', '<div class="x-tab-strip x-tab-strip-top"></div>', true);
//        this.dragEl = this.stripWrap.insertHtml('afterBegin','<div></div>', true);
//        
//        this.tab = Ext.get(this.getEl()); // Grab a reference to the tab being dragged
//        this.tab.applyStyles('visibility:hidden;'); // Hide the tab being dragged
//        
//        // Insert the html and css classes for the dragged tab into the proxy
//        this.dragEl.insertHtml('afterBegin', this.tab.dom.innerHTML, false);
//        this.dragEl.dom.className = this.tab.dom.className; 
//        
//        // Constrain the proxy drag in the X coordinate to the tabpanel
//        var panelWidth = this.tabpanel.el.getWidth();
//        var panelX = this.tabpanel.el.getX();
//        var tabX = this.tab.getX();
//        var tabWidth = this.tab.getWidth();
//        var left = tabX - panelX;
//        var right = panelX + panelWidth - tabX - tabWidth;
//        this.resetConstraints();
//        this.setXConstraint(left, right);
//    }
//    ,onDragOver: function(e, targetArr) {
//        
//        console.log('ON DRAG OVER');
//        
//        e.stopEvent();
//        
//        // Grab the tab you have dragged the proxy over
//        var target = Ext.get(targetArr[0].id);
//        var targetWidth = target.getWidth();
//        var targetX = target.getX();
//        var targetMiddle = targetX + (targetWidth / 2);
//        var elX = this.tab.getX();
//        var dragX = this.proxyWrapper.getX();
//        var dragW = this.proxyWrapper.getWidth();
//        if(dragX < targetX && ((dragX + dragW) > targetMiddle) ) {
//            if(target.next() != this.tab) {
//                target.applyStyles('visibility:hidden;');
//                this.tab.insertAfter(target);
//                this.targetProxy = this.createSliderProxy(targetX, target);
//                if(!this.targetProxy.hasActiveFx()) this.animateSliderProxy(target, this.targetProxy, elX);
//            }
//        }
//        if(dragX > targetX && (dragX < targetMiddle)  ) {
//            if(this.tab.next() != target) {
//                target.applyStyles('visibility:hidden;');
//                this.tab.insertBefore(target);
//                this.targetProxy = this.createSliderProxy(targetX, target);
//                if(!this.targetProxy.hasActiveFx()) this.animateSliderProxy(target, this.targetProxy, elX);
//            }
//        }
//    }
//    ,animateSliderProxy: function(target, targetProxy, elX){
//        targetProxy.shift({
//            x: elX
//            ,easing: 'easeOut'
//            ,duration: this.slideDuration
//            ,callback: function() {
//                targetProxy.remove();
//                target.applyStyles('visibility:visible;');
//            }
//            ,scope:this
//        }); 
//    }
//    ,createSliderProxy: function(targetX, target) {
//        var sliderWrapperEl = Ext.getBody().insertHtml('afterBegin', '<div class="tab-proxy" style="position:absolute;visibility:visible;z-index:999;left:' + targetX + 'px;"></div>', true);
//        sliderWrapperEl.stripWrapper = sliderWrapperEl.insertHtml('afterBegin', '<div class="x-tab-strip x-tab-strip-top"></div>', true);
//        sliderWrapperEl.dragEl = sliderWrapperEl.stripWrapper.insertHtml('afterBegin', '<div></div>', true);
//        sliderWrapperEl.dragEl.update(target.dom.innerHTML);
//        sliderWrapperEl.dragEl.dom.className = target.dom.className;
//        var h = parseInt(target.getTop(false));
//        sliderWrapperEl.setTop(h);
//        return sliderWrapperEl;
//    }
//    ,onDragDrop: function(e, targetId) {
//        e.stopEvent();
//    }
//    ,endDrag: function(e){
//        var elX         = this.tab.getX();
//        this.proxyWrapper.applyStyles('visibility:visible;');
//        
//        // Animate the dragProxy to the proper position
//        this.proxyWrapper.shift({
//            x: elX
//            ,easing: 'easeOut'
//            ,duration: this.slideDuration
//            ,callback: function() {
//                this.proxyWrapper.applyStyles('visibility:hidden;');
//                this.tab.applyStyles('visibility:visible;');
//                
//                // Cleanup
//                this.stripWrap.remove();
//                this.dragEl.remove();
//                if(!this.targetProxy) return;
//                this.targetProxy.stripWrapper.remove();
//                this.targetProxy.dragEl.remove();
//            }
//            ,scope:this
//        });
//        
//        Ext.dd.DDM.useCache = true;
//    }
//});

/**
 * MultiDocumentPanel is containing a TabPanel representing documents (itself containing DocumentMultiView)
 * @class Ext.fdl.MultiDocumentPanel
 * @namespace Ext.fdl.Document
 */
Ext.fdl.MultiDocumentPanel = Ext.extend(Ext.TabPanel, {
	
	/**
	 * Targeted Context
	 * @link {Fdl.Context context}
	 * @type Fdl.Context
	 */
	context: null,
	
	forceExt: false,
	forceClassic: false,
		
	toString: function(){
		return 'Ext.fdl.MultiDocumentPanel';
	},
	
	constructor: function(config){
		
		Ext.apply(this,config);
				
		this.documentArray = {};
		
		Ext.apply(this,{			
			enableTabScroll: true		
		});
				
		Ext.fdl.MultiDocumentPanel.superclass.constructor.call(this,config);
		
	},
	
//	initComponent: function(){
//    
//        Ext.fdl.MultiDocumentPanel.superclass.initComponent.call(this);
//		
//	},
	
	/**
	 * Add a document tab by providing a document panel.
	 * @method addDocumentPanel
	 * @param {Ext.fdl.MultiDocument||Ext.fdl.Document} Document panel.
	 */
	addDocumentPanel: function(documentPanel){
		
		var documentId = documentPanel.document.id ;
		
		documentPanel.on('close',function(panel){
			this.documentArray[documentId] = null;
		},this);
		
		if(!this.documentArray[documentId]){
			
			this.add(documentPanel);
			this.doLayout();
			
			this.documentArray[documentId] = documentPanel ;
			
		}
		
		this.setActiveTab(this.documentArray[documentId]);	
		
	},
	
	/**
	 * Add a document tab by providing a document id.
	 * @method addDocumentId
	 * @param {Integer||String} documentId Document reference.
	 */
	addDocumentId: function(documentId, mode, config){
		
		console.log('Add document Id',documentId,mode,config);
		
		var me = this;
		
		if(!this.documentArray[documentId]){
			
			if(mode == 'create'){
				var document = this.context.createDocument({
					familyId: documentId
				});
			} else {
				var document = this.context.getDocument({
					id: documentId
				});
			}
			
			if(!document){
				Ext.Msg.alert(me.context._("eui::missing right"),me.context._("eui::You have no right to access this document"));	
				return;
			}
			
			console.log('Document',document);
			
			if(document.isCollection() && !(mode=='create')){
		
				var documentPanel = new Ext.fdl.DocumentMultiView({
					document: document,
					title: Fdl.encodeHtmlTags(document.getTitle()) || me.context._("eui::Creation :")+' ' + Fdl.encodeHtmlTags(me.context.getDocument({id:document._mvalues.family}).getTitle()),
					config: config,
					forceExt: me.forceExt,
					forceClassic: me.forceClassic,
					mode: mode || 'view',
					closable: true,
					listeners: {
						close: function(panel){
							me.documentArray[panel.document.id] = null;
						}
					}
				});
			
			} else {
				
				var documentPanel = new Ext.fdl.Document({
					document: document,
					title: Fdl.encodeHtmlTags(document.getTitle())|| me.context._("eui::Creation :")+' ' + Fdl.encodeHtmlTags(me.context.getDocument({id:document._mvalues.family}).getTitle()),
					config: config,
					forceExt: me.forceExt,
                    forceClassic: me.forceClassic,
					mode: mode || 'view',
					closable: true,
					listeners: {
						close: function(panel){
							me.documentArray[panel.document.id] = null;
						}
					}
				
				});
				
			}
			
			documentPanel.subscribe('modifydocument',function(fdldoc){
				if(documentPanel.document && documentPanel.document.id == fdldoc.id){
					documentPanel.document = fdldoc;
					documentPanel.setTitle(Fdl.encodeHtmlTags(documentPanel.document.getTitle()) || me.context._("eui::Creation :")+' ' + Fdl.encodeHtmlTags(me.context.getDocument({id:documentPanel.document._mvalues.family}).getTitle()));
					me.documentArray[fdldoc.id] = documentPanel ;
				}
			});
			
			documentPanel.on('beforeclose',function(panel){
	            if(panel.closeConfirm){
	                
	                var closeConfirm = panel.closeConfirm();
	                if(closeConfirm && !panel.closeConfirmed){
	                    
	                    Ext.Msg.show({
	                        buttons:{
	                            ok:'Oui',
	                            cancel:'Non'
	                        },
	                        fn: function(id){
	                            if(id=='ok'){
	                                panel.closeConfirmed = true;
	                                me.remove(panel,true);
	                                me.documentArray[panel.document.id] = null;
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
				
			this.add(documentPanel);
			this.doLayout();
			
			this.changeTabIcon(documentPanel,document.getIcon({width: 15}));
		
			if(mode != 'create'){
				this.documentArray[documentId] = documentPanel ;
			} else {
				this.setActiveTab(documentPanel);
				return ;
			}
			
		}
		
		this.setActiveTab(this.documentArray[documentId]);
		
	},
	
	/**
	 * Remove a document by providing a document id.
	 * @method removeDocumentId
	 * @param {Integer||String} documentId Document reference.
	 */
	removeDocumentId: function(documentId){
		
		if(this.documentArray[documentId]){		
			this.remove(this.documentArray[documentId],true);
			this.documentArray[documentId] = null ;			
		}
		
	}

});