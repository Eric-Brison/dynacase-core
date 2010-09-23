/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero
 *          General Public License
 */

Ext.fdl.AdminPanel = Ext.extend(Ext.Panel, {

	layout : 'border',

	userName : '',

	initComponent : function() {

		var me = this;

		// me.onLogout = me.initialConfig.onLogout ;

		this.tbar = new Ext.Toolbar({
					items : [{
								// xtype: 'tbtext',
								cls : 'x-btn-text-icon',
								text : '<b>dynacase webstudio | context : freedom</b>',
								icon : 'Images/dynacase.ico',
								handler : function() {
									// this.centerPanel.getLayout().setActiveItem('welcome');
									// this.centerPanel.doLayout();
									me.serviceSelect({
												text : 'Welcome Screen',
												name : 'welcome',
												url : '../webstudio/welcome.php'
											});
								}
							}, '->', {
								text : this.userName,
								icon : '../lib/ui/icon/user.png',
								handler : function() {
									me.onUserName();
								}
							}, '-', {
								text : 'logout',
								icon : '../lib/ui/icon/door_out.png',
								handler : function() {
									me.onLogout();
								}
							}]
				});

		this.centerStatusBar = new Ext.Toolbar({
			style : "background-image:none;background-color:#FFEE99;border-color:#FFCC00;",
			items : [{
						id : 'event-text',
						xtype : 'tbtext',
						text : 'Event...'
					}, '->', {
						id : 'event-list',
						xtype : 'button',
						icon : '../lib/ui/icon/text_columns.png',
						handler : function() {
							me.displayStudioEvents();
						}
					}, {
						id : 'event-prev',
						xtype : 'button',
						// text: 'prev',
						icon : '../lib/ui/icon/resultset_previous.png',
						handler : function() {
							me.setStudioEvent(me.currentEventIndex - 1);
						}
					}, {
						id : 'event-next',
						xtype : 'button',
						// text: 'next',
						icon : '../lib/ui/icon/resultset_next.png',
						handler : function() {
							me.setStudioEvent(me.currentEventIndex + 1);
						}
					}]
		});

		this.studioEvents = [];

		this.currentEventIndex = 0;

		this.addStudioEvent('Webstudio session start');

		// this.setStudioEvent(this.currentEventIndex);

		this.centerPanel = new Ext.Panel({
			// region: 'center',
			layout : 'card',
			activeItem : 'welcome',
			border : false,
			items : [{
				id : 'welcome',
				html : '<iframe style="width:100%;height:100%" frameborder="0" marginheight="0" marginwidth="0"   src="'
						+ '../webstudio/welcome.php' + '")></iframe>'
			}]
		});

		this.centerContainer = new Ext.Panel({
					tbar : this.centerStatusBar,
					region : 'center',
					layout : 'fit',
					items : [this.centerPanel]
				});

		this.westTree = new Ext.tree.TreePanel({
					region : 'north',
					split : true,
					rootVisible : false,
					root : new Ext.tree.TreeNode('My Services'),
					lines : false,
					height : 300,
					title : 'Services'
				});

		this.westDetails = new Ext.Panel({
					title : 'Details',
					region : 'center'
				});

		this.westPanel = new Ext.Panel({
					region : 'west',
					layout : 'border',
					split : true,
					collapsible : true,
					collapseMode : 'mini',
					header : false,
					width : 200,
					border : false,
					items : [me.westTree, me.westDetails]
				});

		this.items = [this.centerContainer, this.westPanel];

		this.webstudioNode = new Ext.tree.TreeNode({
					text : '<b>Webstudio</b>',
					icon : '../lib/ui/icon/cog.png',
					expanded : true
				});

		this.toolboxNode = new Ext.tree.TreeNode({
					text : '<b>Platform</b>',
					icon : '../lib/ui/icon/application.png',
					expanded : true
				});

		this.applicationNode = new Ext.tree.TreeNode({
					text : '<b>Applications</b>',
					icon : '../lib/ui/icon/application_cascade.png',
					expanded : true
				});

		this.westTree.root.appendChild(this.webstudioNode);
		this.westTree.root.appendChild(this.toolboxNode);
		this.westTree.root.appendChild(this.applicationNode);

		var addServiceNode = function(service, node) {

			var label = service.label;
			var url = service.url;
			var component = service.component;
			var icon = service.icon;
			var name = service.name;
			var application = service.application;
			var applicationLabel = service.applicationLabel;
			var description = service.description;
			var help = service.help;
			var action = service.root;

			var snode = new Ext.tree.TreeNode({
						text : label,
						// leaf: true,
						component : component,
						url : url,
						icon : icon,
						name : name,
						application : application,
						applicationLabel : applicationLabel,
						description : description,
						help : help,
						action : action
					});

			snode.on('dblclick', function(node, event) {
						me.serviceSelect(node.attributes, true);
					});

			// this.serviceNode.appendChild(node);
			if (node) {
				node.appendChild(snode);
			} else if (application == 'WEBSTUDIO') {
				me.webstudioNode.appendChild(snode);
			} else if (application == 'TOOLBOX') {
				me.toolboxNode.appendChild(snode);
			} else {
				me.applicationNode.appendChild(snode);
			}

			if (service.services) {
				for (var j = 0; j < service.services.length; j++) {
					addServiceNode(service.services[j], snode);
				}
			}

		};

		for (var i = 0; i < this.services.length; i++) {
			addServiceNode(this.services[i]);
		}

		Ext.fdl.AdminPanel.superclass.initComponent.call(this);

		this.westTree.getSelectionModel().on({

					'selectionchange' : function(sm, node) {
						if (node) {
							this.serviceSelect(node.attributes);
						}
					},
					scope : this

				});

	},

	onLogout : function() {
		// To implement on instanciation.
	},

	displayStudioEvents : function() {

		var me = this;

		if (!this.studioEventPanel) {

			var eventPanel = new Ext.grid.GridPanel({
						store : new Ext.data.JsonStore({
									autodestroy : true,
									data : me.studioEvents,
									fields : ['text', 'date']
								}),
						colModel : new Ext.grid.ColumnModel({
									columns : [{
												id : 'date',
												header : 'Date',
												dataIndex : 'date',
												width : 200
											}, {
												id : 'event',
												header : 'Event',
												dataIndex : 'text'
											}]
								}),
						sm : new Ext.grid.RowSelectionModel({
									singleSelect : true
								}),
						autoExpandColumn : 'event',
						title : 'Event List',
						border : 'false'
					});

			this.studioEventPanel = eventPanel;
			this.studioEventPanel.store.sort('date', 'DESC');
			this.centerPanel.add(eventPanel);
			this.centerPanel.getLayout().setActiveItem(eventPanel);
			this.centerPanel.doLayout();

		} else {
			this.studioEventPanel = null;

			if (this.activeService) {
				this.centerPanel.getLayout().setActiveItem(this.activeService);
				this.centerPanel.doLayout();
			} else {
				this.centerPanel.getLayout().setActiveItem('welcome');
				this.centerPanel.doLayout();
			}
		}

	},

	addStudioEvent : function(event) {

		this.studioEvents.push({
					'text' : event,
					'date' : new Date().format('Y-m-d H:i:s')
				});
		this.currentEventIndex = this.studioEvents.length - 1;
		this.setStudioEvent(this.currentEventIndex);
	},

	setStudioEvent : function(index) {
		if (this.studioEvents[index]) {
			Ext.getCmp('event-text').setText(this.studioEvents[index].date
					+ ' : ' + '<b>' + this.studioEvents[index].text + '</b>');
			this.currentEventIndex = index;

			if (!this.studioEvents[index - 1]) {
				Ext.getCmp('event-prev').disable();
			} else {
				Ext.getCmp('event-prev').enable();
			}

			if (!this.studioEvents[index + 1]) {
				Ext.getCmp('event-next').disable();
			} else {
				Ext.getCmp('event-next').enable();
			}

		} else {
			console.log('No event at this index', index);
		}
	},

	formatDetails : function(attributes) {
		var string = "<div style='margin:5px;' >";
		string += "<p><b>Label : </b>"
				+ (attributes.text ? attributes.text : '') + "<br/>";
		string += "<b>Description : </b>"
				+ (attributes.description ? attributes.description : '')
				+ "<br/>";
		string += "<b>Application : </b>"
				+ (attributes.applicationLabel
						? attributes.applicationLabel
						: '') + "<br/>";
		string += "</p>";
		string += '</div>';
		return string;
	},

	displayDetails : function(string) {
		this.westDetails.body.update(string);
	},

	serviceSelect : function(attributes, forceReload) {

		// console.log('CENTER PANEL', attributes);

		// this.centerPanel.removeAll();
		var me = this;
		if (attributes.name && attributes.name != 'welcome') {
			me.addStudioEvent('Use service : ' + attributes.text);
			me.displayDetails(me.formatDetails(attributes));
		}

		if (!me.centerPanel.findById(attributes.name) || forceReload) {

			// console.log('search the script to execute');
			if (attributes.name) {
				if (attributes.action != "") {
					var construct_url = 'index_webstudio.php?'
							+ attributes.action.link;
					var component = new Ext.Panel({
						border : false,
						id : attributes.name,
						html : '<iframe style="width:100%;height:100%" frameborder="0" marginheight="0" marginwidth="0"   src="'
								+ construct_url + '")></iframe>'
					});
					if (me.accountPanel) {
						me.accountPanel = null;
						Ext.getCmp('webstudioFormPanelAccountChange')
								.removeAll(false);
					}
					me.centerPanel.add(component);
					me.activeServiceBefore = me.activeService;
					me.activeService = attributes.name;
					me.centerPanel.getLayout().setActiveItem(attributes.name);
					me.centerPanel.doLayout();
				} else if (attributes.component) {
					// console.log("success setting component");

					var component = new (eval("(" + attributes.component + ")"))(
							{
								border : false,
								id : attributes.name
							});
					if (me.accountPanel) {
						me.accountPanel = null;
						Ext.getCmp('webstudioFormPanelAccountChange')
								.removeAll(false);
					}
					me.centerPanel.add(component);
					me.activeServiceBefore = me.activeService;
					me.activeService = attributes.name;
					me.centerPanel.getLayout().setActiveItem(attributes.name);
					me.centerPanel.doLayout();
				} else if (attributes.url) {
					var construct_url = 'index_webstudio.php?action=getComponent&service='
							+ attributes.name;
					var component = new Ext.Panel({
						border : false,
						id : attributes.name,
						html : '<iframe style="width:100%;height:100%" frameborder="0" marginheight="0" marginwidth="0"   src="'
								+ construct_url + '")></iframe>'
					});
					if (me.accountPanel) {
						me.accountPanel = null;
						Ext.getCmp('webstudioFormPanelAccountChange')
								.removeAll(false);
					}
					me.centerPanel.add(component);
					me.activeServiceBefore = me.activeService;
					me.activeService = attributes.name;
					me.centerPanel.getLayout().setActiveItem(attributes.name);
					me.centerPanel.doLayout();

				} else {
					// console.log("No services' action set");
					Ext.Msg.alert('Critical error',
							_('No action set for this service'));
					if (me.accountPanel) {
						me.accountPanel = null;
						Ext.getCmp('webstudioFormPanelAccountChange')
								.removeAll(false);
					}
					me.activeServiceBefore = me.activeService;
					me.activeService = 'welcome';
					me.centerPanel.getLayout().setActiveItem('welcome');
					me.centerPanel.doLayout();
					return;
				}
			}
		}
		if (me.accountPanel) {
			me.accountPanel = null;
			Ext.getCmp('webstudioFormPanelAccountChange').removeAll(false);
		}
		me.activeServiceBefore = me.activeService;
		me.activeService = attributes.name;
		me.centerPanel.getLayout().setActiveItem(attributes.name);
		me.centerPanel.doLayout();
	}
});

// /////////
// See :
// http://www.sencha.com/forum/showthread.php?51162-Latest-code-for-ColumnTree&highlight=editable+column+tree

Ext.ns("Ext.ux.tree");

Ext.ux.tree.TreeReader = Ext.extend(Ext.data.DataReader, {

			tree : null,

			constructor : function(meta, recordType) {
				Ext.ux.tree.TreeReader.superclass.constructor.call(this, meta,
						recordType || meta.fields);
			},

			load : function(node) {
				if (node.attributes.children) {
					var cs = node.attributes.children;
					for (var i = 0, len = cs.length; i < len; i++) {
						var cn = node.appendChild(this.createNode(cs[i]));
						this.load(cn);
					}
				}
			},

			createNode : function(attr) {
				var node = new Ext.data.Node(attr);
				node.expanded = (attr.expanded === true);
				return node;
			},

			/**
			 * Create a data block containing Ext.data.Records from a tree.
			 */
			readRecords : function(o) {

				var root = this.createNode({
							text : 'Root',
							id : 'root',
							children : o
						});
				this.tree = new Ext.data.Tree(root);
				this.load(root);

				var f = this.recordType.prototype.fields;
				var records = [];
				root.cascade(function(node) {
							if (node !== root) {
								var record = new this.recordType(this
												.extractValues(node, f.items),
										node.id);
								record.node = node;
								record.depth = node.getDepth();
								records.push(record);
							}
						}, this);

				return {
					success : true,
					records : records,
					totalRecords : records.length
				};
			},

			/**
			 * type-casts a single node
			 */
			extractValues : function(node, fields) {
				var f, values = {};
				for (var j = 0; j < fields.length; j++) {
					f = fields[j];
					var v = node.attributes[f.mapping];
					values[f.name] = f.convert((v !== undefined)
									? v
									: f.defaultValue, node);
				}
				return values;
			}
		});

Ext.tree.ColumnTree = Ext.extend(Ext.tree.TreePanel, {
			lines : false,
			borderWidth : Ext.isBorderBox ? 0 : 2, // the combined left/right
			// border
			// for each cell
			cls : 'x-column-tree',
			collapsible : false,
			onRender : function() {
				Ext.tree.ColumnTree.superclass.onRender.apply(this, arguments);
				this.headers = this.body.createChild({
							cls : 'x-tree-headers'
						}, this.innerCt.dom);

				var cols = this.columns, c;
				var totalWidth = 0;

				for (var i = 0, len = cols.length; i < len; i++) {
					c = cols[i];
					totalWidth += c.width;
					this.headers.createChild({
								cls : 'x-tree-hd '
										+ (c.cls ? c.cls + '-hd' : ''),
								cn : {
									cls : 'x-tree-hd-text',
									html : c.header
								},
								style : 'width:' + (c.width - this.borderWidth)
										+ 'px;'
							});
				}

				this.headers.createChild({
							cls : 'x-clear'
						});

				// prevent floats from wrapping when clipped
				this.headers.setWidth(totalWidth);
				this.innerCt.setWidth(totalWidth);
			},
			initComponent : function() {
				Ext.tree.ColumnTree.superclass.initComponent.apply(this,
						arguments);
				this.addEvents('beforecelledit');
				for (var i = 0; i < this.columns.length; i++) {
					var col = this.columns[i];

					if (col.editorField) {
						var editor = new Ext.tree.ColumnTreeEditor(this,
								col.dataIndex, col.editorField);
						this.editor = editor;
					}
				}
			}
		});

Ext.tree.ColumnNodeUI = Ext.extend(Ext.tree.TreeNodeUI, {
	focus : Ext.emptyFn, // prevent odd scrolling behavior
	renderElements : function(n, a, targetNode, bulkRender) {
		this.indentMarkup = n.parentNode
				? n.parentNode.ui.getChildIndent()
				: '';

		var t = n.getOwnerTree();
		var cols = t.columns;
		var bw = t.borderWidth;
		var c = cols[0];

		n.cols = new Array();
		n.renderers = new Array();

		var text = n.text
				|| (c.renderer
						? c.renderer(a[c.dataIndex], n, a)
						: a[c.dataIndex]);
		n.cols[cols[0].dataIndex] = text;

		var buf = [
				'<li class="x-tree-node" unselectable="on"><div ext:tree-node-id="',
				n.id, '" class="x-tree-node-el x-tree-node-leaf ', a.cls,
				'" unselectable="on">',
				'<div class="x-tree-col" style="width:', c.width - bw,
				'px;" unselectable="on">',
				'<span class="x-tree-node-indent" unselectable="on">',
				this.indentMarkup, "</span>", '<img src="', this.emptyIcon,
				'" class="x-tree-ec-icon x-tree-elbow" unselectable="on">',
				'<img src="', a.icon || this.emptyIcon,
				'" class="x-tree-node-icon',
				(a.icon ? ' x-tree-node-inline-icon' : ''),
				(a.iconCls ? ' ' + a.iconCls : ''), '" unselectable="on">',
				'<a hidefocus="on" class="x-tree-node-anchor" href="',
				a.href ? a.href : '#', '" tabIndex="1" ',
				a.hrefTarget ? ' target="' + a.hrefTarget + '"' : '',
				' unselectable="on">', '<span unselectable="on">', text,
				'</span></a>', '</div>'];

		for (var i = 1, len = cols.length; i < len; i++) {
			c = cols[i];
			var text = (c.renderer
					? c.renderer(a[c.dataIndex], n, a)
					: a[c.dataIndex]);
			n.cols[c.dataIndex] = text;
			n.renderers[c.dataIndex] = {
				renderer : (c.renderer ? c.renderer : false)
			};
			buf.push('<div class="x-tree-col ', (c.cls ? c.cls : ''),
					'" style="width:', c.width - bw, 'px;" unselectable="on">',
					'<div class="x-tree-col-text x-tree-col-', c.dataIndex,
					'" unselectable="on">', text, '</div>', '</div>');
		}
		buf
				.push(
						'<div class="x-clear" unselectable="on"></div></div>',
						'<ul class="x-tree-node-ct" style="display:none;" unselectable="on"></ul>',
						'</li>');

		if (bulkRender !== true && n.nextSibling && n.nextSibling.ui.getEl()) {
			this.wrap = Ext.DomHelper.insertHtml('beforeBegin',
					n.nextSibling.ui.getEl(), buf.join(''));
		} else {
			this.wrap = Ext.DomHelper.insertHtml('beforeEnd', targetNode, buf
							.join(''));
		}

		this.elNode = this.wrap.childNodes[0];
		this.ctNode = this.wrap.childNodes[1];
		var cs = this.elNode.firstChild.childNodes;
		this.indentNode = cs[0];
		this.ecNode = cs[1];
		this.iconNode = cs[2];
		this.anchor = cs[3];
		this.textNode = cs[3].firstChild;
	}
});

Ext.tree.ColumnTreeEditor = function(tree, colIndex, editorConfig) {
	var field;

	if (editorConfig.xtype) {
		field = new Ext.ComponentMgr.create(editorConfig);
		Ext.tree.ColumnTreeEditor.superclass.constructor.call(this, field);
	} else {
		field = {};
	}

	this.tree = tree;
	this.columnIndex = colIndex;

	if (!this.tree.rendered) {
		this.tree.on('render', this.initEditor, this);
	} else {
		this.initEditor(this.tree);
	}
};

Ext.extend(Ext.tree.ColumnTreeEditor, Ext.Editor, {
	onTargetBeforeClick : function(node, event) {
		var sinceLast = (this.lastClick ? this.lastClick.getElapsed() : 0);
		this.lastClick = new Date();

		if (sinceLast <= this.editDelay
				|| !this.tree.getSelectionModel().isSelected(node)) {
			this.completeEdit();
		}
		var obj = event.target;

		if (Ext.select('.x-tree-node-anchor', false, obj).getCount() == 1) {
			obj = Ext.select('.x-tree-node-anchor', false, obj).elements[0].firstChild;
		}

		if (obj.nodeName != 'SPAN' && obj.nodeName != 'DIV') {
			return true;
		}
		var colIndex = 0;

		if (this.tree.fireEvent('beforecelledit', this.tree, node,
				this.columnIndex) === false) {
			return true;
		}

		var elt = Ext.Element.fly(obj);
		if (elt.hasClass('x-tree-col-' + this.columnIndex)) {
			this.triggerEdit(node, event, this.columnIndex);
			event.stopEvent();
			return false;
		} else {
			return true;
		}
	},
	alignment : 'l-l',
	autoSize : false,
	hideEl : false,
	cls : 'x-small-editor x-tree-editor',
	shim : false,
	shadow : 'frame',
	maxWidth : 250,
	editDelay : 0,
	initEditor : function(tree) {
		this.tree.on('beforeclick', this.onTargetBeforeClick, this);
		this.on('complete', this.updateNode, this);
		// this.on('beforestartedit', this.fitToTree, this);
		this.on('startedit', this.bindScroll, this, {
					delay : 10
				});

		this.on('specialkey', this.onSpecialKey, this);
	},
	fitToTree : function(ed, el) {
		var td = this.tree.getTreeEl().dom, nd = el.dom;

		if (td.scrollLeft > nd.offsetLeft) {
			td.scrollLeft = nd.offsetLeft;
		}
		var w = Math.min(this.maxWidth, (td.clientWidth > 20
						? td.clientWidth
						: td.offsetWidth)
						- Math.max(0, nd.offsetLeft - td.scrollLeft) - 5);
		this.setSize(w, '');
	},
	triggerEdit : function(node, e, colIndex) {
		var obj = e.target;

		if (Ext.select('.x-tree-node-anchor', false, obj).getCount() == 1) {
			obj = Ext.select('.x-tree-node-anchor', false, obj).elements[0].firstChild;
		} else if (obj.nodeName == 'SPAN' || obj.nodeName == 'DIV') {
			obj = e.target;
		} else {
			return false;
		}

		this.completeEdit();
		this.editNode = node;
		this.editCol = obj;
		this.editColIndex = colIndex;
		this.startEdit(obj);

		if (obj.nodeName == 'DIV') {
			var width = obj.offsetWidth;
			this.setSize(width);
		}
	},
	bindScroll : function() {
		this.tree.getTreeEl().on('scroll', this.cancelEdit, this);
	},
	beforeNodeClick : function(node, e) {
		var sinceLast = (this.lastClick ? this.lastClick.getElapsed() : 0);
		this.lastClick = new Date();

		if (sinceLast > this.editDelay
				&& this.tree.getSelectionModel().isSelected(node)) {
			e.stopEvent();
			this.triggerEdit(node, e);
			return false;
		} else {
			this.completeEdit();
		}
	},
	updateNode : function(ed, value) {
		if (value != '' && value != this.editCol.innerHTML) {
			value = (this.editNode.renderers[this.editColIndex].renderer
					? this.editNode.renderers[this.editColIndex]
							.renderer(value)
					: value);
			this.tree.getTreeEl().un('scroll', this.cancelEdit, this);
			this.editNode.cols[this.editColIndex] = value; // for
			// internal
			// use
			// only
			this.editNode.attributes[this.editColIndex] = value; // duplicate
			// into
			// array
			// of
			// node
			// attributes
			this.editCol.innerHTML = value;
		}
	},

	onHide : function() {
		Ext.tree.ColumnTreeEditor.superclass.onHide.call(this);
		if (this.editNode) {
			this.editNode.ui.focus();
		}
	},
	onSpecialKey : function(field, e) {
		var k = e.getKey();

		if (k == e.ESC) {
			e.stopEvent();
			this.cancelEdit();
		} else if (k == e.ENTER && !e.hasModifier()) {
			e.stopEvent();
			this.completeEdit();
		}
	}
});

Ext.fdl.AppMngPanel = Ext.extend(Ext.Panel, {

	layout : 'fit',

	initComponent : function() {

		var store = new Ext.data.SimpleStore({
					fields : ['enum'],
					data : [['Enum 1'], ['Enum 2']]
				});

		var combo_config = {
			id : 'enum_combo_list',
			store : store,
			displayField : 'enum',
			xtype : 'combo',
			mode : 'local',
			triggerAction : 'all',
			width : 100,
			emptyText : 'Please select report type...',
			selectOnFocus : true
		};

		this.treePanel = new Ext.tree.ColumnTree({
			border : false,
			columnLines : true,
			rootVisible : false,
			// columnModel: new Ext.grid.ColumnModel({
			// columns: [
			// {
			// header: 'Label',
			// width: 200,
			// dataIndex: 'param_label'//,
			// // editorField:
			// // {
			// // xtype: 'textfield',
			// // editable: true
			// // }
			// },
			// // {
			// // header: 'Name',
			// // width: 150,
			// // dataIndex: 'param_name'
			// // },
			// {
			// header: 'Type',
			// width: 100,
			// dataIndex: 'param_type'
			// },
			// {
			// header: 'Value',
			// width: 120,
			// dataIndex: 'approval'//,
			// //editorField: combo_config
			// }
			// ],
			// editors: {
			// 'text': new Ext.grid.GridEditor(new Ext.form.TextField({})),
			// 'number': new Ext.grid.GridEditor(new Ext.form.NumberField({})),
			// 'date': new Ext.grid.GridEditor(new Ext.form.DateField({}))
			// },
			// getCellEditor: function(colIndex, rowIndex) {
			// var field = this.getDataIndex(colIndex);
			// if (field == 'approval') {
			// var rec = store.getAt(rowIndex);
			// return this.editors[rec.get('param_type')];
			// }
			// return Ext.grid.ColumnModel.prototype.getCellEditor.call(this,
			// colIndex, rowIndex);
			// }
			// }),

			columns : [{
				header : 'Label',
				width : 200,
				dataIndex : 'param_label'// ,
					// editorField:
					// {
					// xtype: 'textfield',
					// editable: true
					// }
				},
					// {
					// header: 'Name',
					// width: 150,
					// dataIndex: 'param_name'
					// },
					{
						header : 'Type',
						width : 100,
						dataIndex : 'param_type'
					}, {
						header : 'Value',
						width : 120,
						dataIndex : 'param_value',
						editorField : combo_config
					}],
			loader : new Ext.tree.TreeLoader({
						preloadChildren : true,
						uiProviders : {
							'col' : Ext.tree.ColumnNodeUI
						}
					}),
			root : new Ext.tree.AsyncTreeNode({
				allowChildren : true,
				children : [{
							param_label : 'Application 1',
							param_type : 'application',
							param_name : 'blah blah',
							uiProvider : 'col',
							param_value : '',
							children : [{
										param_label : 'Parameter 1',
										param_type : 'number',
										param_name : 'blah blah',
										param_value : 'text',
										uiProvider : 'col',
										leaf : true
									}, {
										param_label : 'Parameter 2',
										param_type : 'number',
										param_name : 'blah blah',
										param_value : 'text',
										uiProvider : 'col',
										leaf : true
									}, {
										param_label : 'Parameter 3',
										param_type : 'text',
										param_name : 'blah blah',
										param_value : 'text',
										uiProvider : 'col',
										leaf : true
									}]
						}, {
							param_label : 'Application 2',
							param_type : 'application',
							param_name : 'blah blah',
							param_value : '',
							uiProvider : 'col',
							children : [{
										param_label : 'Parameter 1',
										param_type : 'text',
										param_name : 'blah blah',
										param_value : 'text',
										uiProvider : 'col',
										leaf : true
									}, {
										param_label : 'Parameter 2',
										param_type : 'text',
										param_name : 'blah blah',
										param_value : 'text',
										uiProvider : 'col',
										leaf : true
									}, {
										param_label : 'Parameter 3',
										param_type : 'text',
										param_name : 'blah blah',
										param_value : 'text',
										uiProvider : 'col',
										leaf : true
									}, {
										param_label : 'Parameter 4',
										param_type : 'text',
										param_name : 'blah blah',
										param_value : '',
										uiProvider : 'col',
										leaf : true
									}, {
										param_label : 'Parameter 5',
										param_type : 'text',
										param_name : 'blah blah',
										param_value : 'text',
										uiProvider : 'col',
										leaf : true
									}, {
										param_label : 'Parameter 6',
										param_type : 'text',
										param_name : 'blah blah',
										param_value : '',
										uiProvider : 'col',
										leaf : true
									}]
						}, {
							param_label : 'Application 3',
							param_type : 'application',
							param_name : 'blah blah',
							param_value : '',
							uiProvider : 'col',
							children : [{
										param_label : 'Parameter 1 (Group)',
										param_type : 'text',
										param_name : 'blah blah',
										param_value : '',
										uiProvider : 'col',
										children : [{
													param_label : 'SubParameter 1',
													param_type : 'text',
													param_name : 'blah blah',
													param_value : '',
													uiProvider : 'col',
													leaf : true
												}, {
													param_label : 'SubParameter 2',
													param_type : 'text',
													param_name : 'blah blah',
													param_value : '',
													uiProvider : 'col',
													leaf : true
												}]
									}, {
										param_label : 'Parameter 2',
										param_type : 'text',
										param_name : 'blah blah',
										param_value : '',
										uiProvider : 'col',
										leaf : true
									}, {
										param_label : 'Parameter 3',
										param_type : 'text',
										param_name : 'blah blah',
										param_value : '',
										uiProvider : 'col',
										leaf : true
									}, {
										param_label : 'Parameter 4',
										param_type : 'text',
										menu_url : 'blah blah',
										param_value : '',
										uiProvider : 'col',
										leaf : true
									}, {
										param_label : 'Parameter 5',
										param_type : 'text',
										menu_url : 'blah blah',
										uiProvider : 'col',
										param_value : '',
										leaf : true
									}, {
										param_label : 'Parameter 6',
										param_type : 'text',
										menu_url : 'blah blah',
										uiProvider : 'col',
										param_value : '',
										leaf : true
									}, {
										param_label : 'Parameter 7',
										param_type : 'text',
										menu_url : 'blah blah',
										param_value : '',
										uiProvider : 'col',
										leaf : true
									}, {
										param_label : 'Parameter 8',
										param_type : 'text',
										menu_url : 'blah blah',
										param_value : '',
										uiProvider : 'col',
										leaf : true
									}, {
										param_label : 'Parameter 9',
										param_type : 'text',
										menu_url : 'blah blah',
										uiProvider : 'col',
										param_value : '',
										leaf : true
									}]
						}]
			})
		});

		this.treePanel.on('beforecelledit', function(tree, node, columnIndex) {
					// Return false to make cell un-editable
					console.log('BEFORECELLEDIT', tree, node, columnIndex);

					if (node.attributes.param_type == 'application') {
						return false;
					}
					return true;
				});

		// rootVisible: false,
		// root: new Ext.tree.TreeNode('My Parameters'),
		// lines: false
		// });

		this.items = [this.treePanel];

		this.tbar = {
			items : [{
						text : 'Action 1'
					}, {
						text : 'Action 2'
					}]
		};

		// this.applications = [{
		// title: 'Application 1',
		// parameters: [{
		// label: 'Parameter 1'
		// },{
		// label: 'Parameter 2'
		// },{
		// label: 'Parameter Group 1',
		// parameters: [{
		// label: 'Parameter 3'
		// },{
		// label: 'Parameter 4'
		// }]
		// }]
		// },{
		// title: 'Application 2'
		// }];
		//        
		// for(var i = 0 ; i < this.applications.length ; i++){
		//        	
		// var application = this.applications[i];
		//                        
		// var title = application.title ;
		//            
		// var node = new Ext.tree.TreeNode({
		// text: title
		// });
		//            
		// if(application.parameters){
		//            	
		// this.recursiveParameterNode(application.parameters,node);
		//            	
		// for (var j = 0 ; j < application.parameters.length ; j++ ){
		// var subnode = new Ext.tree.TreeNode({
		// text: application.parameters[j].label,
		// leaf: true
		// });
		// node.appendChild(subnode);
		// }
		//            
		// }
		//            
		// this.treePanel.root.appendChild(node);
		//            
		// }

		Ext.fdl.AppMngPanel.superclass.initComponent.call(this);

	}// ,

		// recursiveParameterNode: function(parameter,node){
		//		
		// if(parameter.parameters){
		// for (var j = 0 ; j < parameter.parameters.length ; j++ ){
		// var subnode = new Ext.tree.TreeNode({
		// text: parameter.parameters[j].label,
		// leaf: true
		// });
		// node.appendChild(subnode);
		// this.recursiveParameterNode(parameter.parameters[j],subnode);
		// }
		// }
		//	    	
		// }

});
