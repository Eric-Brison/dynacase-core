
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Ext.fdl.AbstractCollection
 * @extends Ext.Panel
 * @namespace Ext.fdl.Collection
 * @author Clément Laballe
 * <p>This class represents the abstract collection interface.</p>
 */
Ext.fdl.AbstractCollection = Ext.extend(Ext.Panel, {
	
	iconSize: 20,
	
	abstractWidth: '100%',
	
	usePaging : true,

	bodyStyle : null,

	constructor : function(config) {
		
		Ext.apply(this, config);
    	
    	// Equip AbstractCollection instance with Collection widget generic interface.
        Ext.applyIf(this, Ext.fdl.Collection);
        this.initCollectionWidget();
        
        // Setup AbstractCollection instance.		
		var me = this;

		var properties = me.getProperties();
		
		var attributes = me.getAttributes();

		var store = new Ext.data.JsonStore({
			data : [],
			fields : properties.concat(attributes)
		});

		store.load = function(options) {

			var options = options || {};

			this.fireEvent('beforeload', this, options);

			// Start appropriate search on server.
			if (options.params.server != false) {

				var sConfig = {
					start : options.params.start,
					slice : options.params.limit,
					filter : me.filter,
					onlyValues: false // In abstract we need to get attributes definition for display.
				};

				// if (me.orderByName && me.orderByDirection) {
				// sConfig.orderBy = me.orderByName + ' ' +
				// Ext.util.Format.lowercase(me.orderByDirection);
				// }

				me.getData(sConfig);

			}

			// Empty store
			this.suspendEvents();
			this.removeAll();

			var records = [];

			if (me.content) {

				// Fill store with appropriate data and empty data to emulate
				// length
				for (var j = 0; j < me.content.length; j++) {

					var dataItem = {};

					// if (j >= start && j < start + this.pageSize) {

					// var doc = me.content[j - start];

					var doc = me.content[j];

					if (doc) {

						var properties = me.getProperties();

						for (var i = 0; i < properties.length; i++) {
							dataItem[properties[i]] = doc.getProperty(properties[i]);
						}

						dataItem['_fdldoc'] = doc;
						
						var attributes = doc.getAttributes();
						
						dataItem.abstract = [];
						
						for (var i in attributes) {
							
							//console.log('ATTRIBUTES',attributes[i],attributes[i].inAbstract);
							
							if(attributes[i].inAbstract && doc.getValue(i)){
								dataItem.abstract.push({
									label: attributes[i].getLabel(),
									value: doc.getDisplayValue(i)
								});
							}
							
						}
						
					}

					// }

					records.push(new this.recordType(dataItem));

				}
			}

			// Suspending events prevent the store from actualizing the gridview
			// with potential long list (causing crashes and rendering
			// artifacts)

			this.add(records);
			this.resumeEvents();

			this.totalLength = me.getCount();

			this.fireEvent('load', this, records, options);
			this.fireEvent('datachanged', this);

		};

		// Make the load mask appear on the view.
		store.on('beforeload', function() {
			me.mask();
		});

		store.on('load', function() {
			me.unmask();
		});

		this.selButton = new Ext.Button({
			icon : me.context.url + 'lib/ui/icon/page_white_stack.png',
			cls : 'x-btn-text-icon',
			text : '  ',
			menu : new Ext.menu.Menu({
				items : [{
						text : 'Tout',
						handler : function(b, e) {
							me.selectAll();
							me.selButton.applySelection();
						}
					}, {
						text : 'Rien',
						handler : function(b, e) {
							me.unselectAll();
							me.selButton.applySelection();
						}
					}, {
						text : 'Inverse',
						handler : function(b, e) {
							me.reverseSelection();
							me.selButton.applySelection();
						}
					}]
			}),
			listeners : {
				render : function(button) {
					button.applySelection();
				}
			}
		});

		this.selButton.applySelection = function() {
			this.setText(me.selection.count() + ''); // Concatening an empty
			// string converts
			// number to string.
			this.selTooltip = new Ext.ToolTip({
						target : this.getEl(),
						html : me.selection.count()
								+ ' document(s) sélectionné(s)',
						hideDelay : 0,
						showDelay : 0
					});
		};

		if (me.usePaging) {

			me.pagingBar = new Ext.PagingToolbar({
				pageSize : me.pageSize || 20,
				store : store,
				displayInfo : true,
				displayMsg : 'Documents {0} - {1} de {2}',
				afterPageText : 'de {0}',
				emptyMsg : "Aucun document",
				items : ['-', this.selButton],
				// Special override of private
				// Allow dynamic loading of search results
				doLoad : function(start, server) {
					var o = {}, pn = this.getParams();
					o[pn.start] = start;
					o[pn.limit] = this.pageSize;
					o['server'] = server;
						if (this.fireEvent('beforechange', this, o) !== false) {
						this.store.load({
							params : o
						});
					}
				}
			});
		}
		
		var viewConfig = {};
		
		// Config options on Ext.fdl.IconCollection are passed to data view, as it is done in Ext JS for TreePanel with TreeView.
		// DataView is accessible with the iconView property.
		Ext.apply(viewConfig,config);
		
		delete viewConfig.id ; // If id exists, it provokes errors.

		Ext.applyIf(viewConfig, {
			itemSelector : 'div.freedom-abstract-container',
			style : 'overflow:auto;',
			height : '100%',
			multiSelect : true,
			store : store,
			tpl : new Ext.XTemplate(
					'<tpl for=".">',
					'<div class="freedom-abstract-container" id="{values.id}" style="float:left;width:'+ me.abstractWidth +';">',
					'<div class="freedom-abstract-wrap" style="overflow:auto;border: 1px solid #99BBE8; margin:5px; padding: 5px;">',
					'<div class="freedom-abstract" style="float:left;">',
					'<img src="{[ values._fdldoc.getIcon({ width: '
							+ me.iconSize + ' }) ]}" style="height:'
							+ me.iconSize + 'px;width:' + me.iconSize
							+ 'px;" class="icon-img"></div>',
					'<div style="padding-left:'+(eval(me.iconSize+'+10'))+'px;"><span class="freedom-abstract-text"><b>{[Ext.util.Format.ellipsis(values.title, 20)]}</b></span></div>',
					'<div style="padding-left:'+(eval(me.iconSize+'+15'))+'px;padding-top:5px;">',
					'<tpl for="abstract" >',
					'<p><b>{label}</b>: {value}</p>',
					'</tpl>',
					'</div></div></div>',
					'</tpl>'),
			listeners : {
				dblclick : function(dataview, index, node, event) {
					me.displayDocument(node.id, 'view', node);
				},
				selectionchange : function(dataview, nodes) {

					var records = dataview.getSelectedRecords();
					
					me.selection.clearSelection();
					for (var i = 0; i < records.length; i++) {
						me.selection.insertToList({
									id : records[i].get('id')
								});
					}

					if (me.selButton) {
						me.selButton.applySelection();
					}
					
/**
 * @event documentselectionchange fires when the document selection changes.
 * @param {Ext.fdl.DocumentSelection} selection
 */
					me.fireEvent('documentselectionchange',me.selection);
					me.onSelectionChange(me.selection);
					
				},
				contextmenu: function(dataview, index, node, event) {
                	me.displayContextMenu(node.id,event);
				}
			}
		});
		
		// Setup icon data view.
		this.iconView = new Ext.DataView(viewConfig);

		Ext.applyIf(config,{
			bodyStyle : me.bodyStyle,
			bbar : me.pagingBar,
			layout : 'fit',
			items : [this.iconView]
		});

		this.on('afterrender', function() {			
			this.reload(true);					
		}, this);
		
		// Disable navigator context menu
        this.on('render',function(panel) {
        	panel.getEl().on("contextmenu", Ext.emptyFn, null, {preventDefault: true});
	    });

		this.iconView.on('afterrender', function() {

			me.dragZone = new Ext.dd.DragZone(this.iconView.getEl(), {
						containerScroll : true,
						ddGroup : 'docDD'
					});

			me.dragZone.onBeforeDrag = function(data, e) {
				me.notifyDocumentDrag(me.selection);
			};

			me.dragZone.onInitDrag = function(x, y) {
				me.displayProxy(me, true);
				this.onStartDrag(x, y);
				return true;
			};

			me.dragZone.getDragData = function(e) {
				var target = e.getTarget('.freedom-abstract-container');
				if (target) {
					var view = me.iconView;
					if (!view.isSelected(target)) {
						view.select(target, true);
						view.onClick(e);
					}

					var dragData = {};

					dragData.component = me;
					dragData.selection = me.selection;

					return dragData;
				}
				return false;
			};

			me.dragZone.afterRepair = function() {
				// for (var i = 0, len = this.dragData.nodes.length; i < len;
				// i++) {
				// Ext.fly(this.dragData.nodes[i]).frame('#8db2e3', 1);
				// }
				this.dragging = false;
			};

			me.dragZone.getRepairXY = function(e) {
				// if (!this.dragData.multi) {
				// var xy = Ext.Element.fly(this.dragData.ddel).getXY();
				// xy[0] += 3;
				// xy[1] += 3;
				// return xy;
				// }
				return false;
			};

			// This function returns the drag proxy
			me.getProxy = function() {
				return me.dragZone.getProxy();
			};
			
			me.isDragging = function() {
				return me.dragZone.dragging;
			};
			
			me.getDropZone = function(){
				return me.dropZone;
			};
			
			Ext.fdl.KeyBoard.on('keychange',function(){
				me.displayProxy(me.hoveredWid, true);
			});
			
			me.dropZone = new Ext.dd.DropZone(this.iconView.getEl(), {

				ddGroup : 'docDD',

				// If the mouse is over a document icon node, return that node.
				// This is provided as the nodedata on all onNodeXXX handling
				// functions.
				getTargetFromEvent : function(e) {
					return e.getTarget('.freedom-abstract-container');
				},

				notifyEnter : function(source, e, data) {
					data.component.displayProxy(me);
				},

				notifyOut : function(source, e, data) {
					data.component.overDoc = null;
					data.component.displayProxy();
				},

				onNodeDrop : function(nodedata, source, e, data) {

					var dragCol = me.context.getDocument({
								id : source.dragData.selection.collectionId,
								useCache : true
							});

					var dropCol = me.collection;
					var dropDoc = me.iconView.getRecord(nodedata).get('_fdldoc');
					
					var behaviour = data.component.dragBehaviour(dragCol, dropCol, source.dragData.selection, dropDoc);
                    
					me.mask();
                	if(behaviour == 'move'){
                		data.component.mask();
                	}

			(function() {

						var ret = me.notifyDocumentDrop(
								source.dragData.component, dragCol, dropCol,
								source.dragData.selection, dropDoc);

						me.unmask();
						data.component.unmask();

						if (!ret) {
							Ext.Msg.alert('freedom ecm',
									'Problem during drag and drop');
						}
					}).defer(5);

					return true;

				},

				onNodeEnter : function(nodedata, source, e, data) {

					var record = me.iconView.getRecord(nodedata);
					me.overDoc = record.get('_fdldoc');
					data.component.displayProxy(me);

				},

				onNodeOut : function(nodedata, source, e, data) {

					data.component.overDoc = null;
					data.component.displayProxy();

				},

				onNodeOver : function(nodedata, source, e, data) {

					var record = me.iconView.getRecord(nodedata);

					var overDoc = record.get('_fdldoc');

					data.component.overDoc = overDoc;
					data.component.displayProxy(me);

					var ret = data.component.notifyDocumentDragOver(
							data.component, data.component.collection,
							me.collection, data.selection,
							data.component.overDoc);

					if (ret) {
						return this.dropAllowed;
					} else {
						return this.dropNotAllowed;
					}

				},

				onContainerDrop : function(source, e, data) {

					var canDrop = data.component.notifyDocumentDragOver(
							data.component, data.component.collection,
							me.collection, data.selection,
							data.component.overDoc);

					if (canDrop) {
						
						me.mask();
						var behaviour = data.component.dragBehaviour(data.component.collection,
							me.collection, data.selection,
							data.component.overDoc);
                    
	                	if(behaviour == 'move'){
	                		data.component.mask();
	                	}

						var dragCol = me.context.getDocument({
									id : source.dragData.selection.collectionId,
									useCache : true
								});

						if (dragCol && !dragCol.isCollection()) {
							dragCol = null;
						}

						var dropCol = me.collection;
						var dropDoc = null;

			(function	() {
							var ret = me.notifyDocumentDrop(
									source.dragData.component, dragCol,
									dropCol, source.dragData.selection,
									dropDoc);

							me.unmask();
							data.component.unmask();

							if (!ret) {
								Ext.Msg.alert('freedom ecm',
										'Problem during drag and drop');
							}
						}).defer(5);

					}

					return true;

				},

				onContainerOver : function(source, e, data) {

					data.component.overDoc = null;
					data.component.displayProxy(me);

					var ret = data.component.notifyDocumentDragOver(
							data.component, data.component.collection,
							me.collection, data.selection,
							data.component.overDoc);

					if (ret) {
						return this.dropAllowed;
					} else {
						return this.dropNotAllowed;
					}

				}

			});

		}, this);		
		
		Ext.fdl.IconCollection.superclass.constructor.call(this,config);
	},

	toString : function() {
		return 'Ext.fdl.AbstractCollection';
	},

	/**
	 * @method applySelection Method to apply current DocumentSelection to
	 *         collection graphic representation.
	 */
	applySelection : function() {

	},

	/**
	 * @method reload
	 */
	reload : function(server, config) {

		if (this.pagingBar) {
			this.pagingBar.doLoad(0, server);
		} else {
			this.iconView.store.load({
						params : {
							
							start : 0,
							server : server,
							limit : 50
							// Check here to make infinite ?
						}
					});
		}

	},

	mask : function() {
			
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(this.bwrap, {
				msg : 'Chargement...'
			});
		}
		this.loadMask.show();

	},

	unmask : function() {
		if (this.loadMask) {
			this.loadMask.hide();
		}
	},

	getProperties : function() {

		var properties = this.context.getDisplayableProperties();

		properties.push('ownername');
		properties.push('fromtitle');
		properties.push('mdate');

		return properties;
	},

	display : function(target) {
		console.log('Ext.fdl.AbstractCollection.display() is deprecated.');
		return this;
	}
	
});
