
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

// This override corrects a problem appeared when migrating to 3.1.1
// Exact reasons of this should be investigated
Ext.override(Ext.layout.ContainerLayout,{
	configureItem: function(c, position){
		if(this.extraCls){
			var t = c.getPositionEl ? c.getPositionEl() : c;
			if (t) { // clement
				t.addClass(this.extraCls);
			} // clement
		}
		// If we are forcing a layout, do so *before* we hide so elements have height/width
		if(c.doLayout && this.forceLayout){
			c.doLayout();
		}
		if (this.renderHidden && c != this.activeItem) {
			c.hide();
		}
	}
});

///**
// * For optimization matter on firefox.
// * See : http://www.extjs.com/forum/showthread.php?t=88479
// * 
// */

// Override to get compatibility between Grid Drag & Drop and CheckboxSelectionModel
// See https://www.extjs.com/forum/showthread.php?82004-Drag-amp-Drop-%E3%81%A8-CheckboxSelectionModel-%E3%81%AE%E4%B8%A1%E7%AB%8B
Ext.override(Ext.grid.CheckboxSelectionModel, {
  handleMouseDown : function(g, rowIndex, e){
    if((g.enableDragDrop || g.enableDrag) && e.getTarget().className == 'x-grid3-row-checker'){
      return;
    }else{
      Ext.grid.CheckboxSelectionModel.superclass.handleMouseDown.apply(this, arguments);
    }
  }
});


/**
 * @class Ext.fdl.GridCollection
 * @extends Ext.grid.GridPanel
 * @namespace Ext.fdl.Collection
 * @author Clément Laballe
 * <p>This class represents the grid collection interface.</p>
 */

Ext.fdl.GridCollection = Ext.extend(Ext.grid.GridPanel, {

	// private
	produceStore: function(properties, attributes){
		
		var me = this;
		
		var store = new Ext.ux.data.SimplePagingStore({
            data: [],
            fields: properties.concat(attributes)
        });
        
        // Override to custom loading data from the freedom-api.
        store.load = function(options) {
        	
        	var options = options || {};
        	
			this.fireEvent('beforeload', this, options);
                    
			// Start appropriate search on server.
			if (options.params.server != false) {
                    
            	var sConfig = {
               		start: options.params.start,
               		slice: options.params.limit,
               		filter: me.volatilefilter
            	};
                        
            	if (me.orderByName && me.orderByDirection) {
               		sConfig.orderBy = me.orderByName + ' ' + Ext.util.Format.lowercase(me.orderByDirection);
            	}
                        
            	me.getData(sConfig);
                        
         	}
         	                    
            // Empty store
         	//Suspending events prevent the store from actualizing the gridview with potential long list (causing crashes and rendering artifacts)
         	this.suspendEvents();
            this.removeAll();
                    
            var records = [];
                    
            if (me.content) {
            	
				// Fill store with appropriate data and empty data to emulate length
				for (var j = 0, l = Math.min(options.params.limit,me.content.length); j < l; j++) {
					
					var dataItem = {};                            
                                                        
                    var doc = me.content[j];
                                
                    if (doc) {
                                
                    	for (var pi = 0, pl = properties.length; pi < pl; pi++) {
                        	dataItem[properties[pi]] = doc.getProperty(properties[pi]);
                        }
                                    
                        for (var ai = 0, al = attributes.length; ai < al; ai++) {
                            dataItem[attributes[ai]] = doc.getValue(attributes[ai]);
                        }
                                    
                        dataItem['_fdldoc'] = doc;
                                    
                    }                                
                            
                    records.push(new this.recordType(dataItem));
                           
                }
			};
                    
            this.add(records);
            this.resumeEvents();
                   
            this.totalLength = me.getCount();
                   
            this.fireEvent('load', this, records, options);
            this.fireEvent('datachanged', this);
                        
        };
        
        // Override to custom sorting data according to freedom.
        store.sortData = function(f, direction){
        	
        	if(me.family){
        		
        		var attribute = me.family.getAttribute(f);
        		if( attribute && attribute.getSortKey){
        			f = attribute.getSortKey();
        		}
        		
        	}
        	
        	// Code to measure execution time
            me.orderByName = f;
            me.orderByDirection = direction;
            var start = new Date();
            me.reload(true);
            var end = new Date();
            console.log('Sort Data execution time : ' + (end - start) + ' ms.');
        };
        
        return store;
        
	},
	
	// private
	produceView: function(){
		
		var view = new Ext.grid.GridView({
        	forceFit: true
        });
        
        // Handle highlight if applicable
        if(this.search && this.search.key && this.search.withHighlight){
        	view.enableRowBody = true;
        	view.getRowClass = function(record, rowIndex, p, store){
        	    p.body = '<p style="margin:3px;margin-left:20px;font-style:italic;">' + record.get('_fdldoc').getHighlight() + '</p>';
                return 'x-grid3-row-expanded';
            };
        }
        	
        // special override
        view.fitColumns = function(preventRefresh, onlyExpand, omitColumn){
	        var cm = this.cm, i;
	        var tw = cm.getTotalWidth(false);
	        var aw = this.grid.getGridEl().getWidth(true)-this.getScrollOffset();
	
	        if(aw < 20){ // not initialized, so don't screw up the default widths
	            return;
	        }
	        var extra = aw - tw;
	
	        if(extra === 0){
	            return false;
	        }
	
	        var vc = cm.getColumnCount(true);
	        var ac = vc-(Ext.isNumber(omitColumn) ? 1 : 0);
	        if(ac === 0){
	            ac = 1;
	            omitColumn = undefined;
	        }
	        var colCount = cm.getColumnCount();
	        var cols = [];
	        var extraCol = 0;
	        var width = 0;
	        var w;
	        for (i = 0; i < colCount; i++){
	            if(!cm.isHidden(i) && !cm.isFixed(i) && i !== omitColumn /* clement */ && !cm.getColumnAt(i).forcedWidth){
	                w = cm.getColumnWidth(i);
	                cols.push(i);
	                extraCol = i;
	                cols.push(w);
	                width += w;
	            }
	        }
	        var frac = (aw - cm.getTotalWidth())/width;
	        while (cols.length){
	            w = cols.pop();
	            i = cols.pop();
	            cm.setColumnWidth(i, Math.max(this.grid.minColumnWidth, Math.floor(w + w*frac)), true);
	        }
	
	        if((tw = cm.getTotalWidth(false)) > aw){
	            var adjustCol = ac != vc ? omitColumn : extraCol;
	             cm.setColumnWidth(adjustCol, Math.max(1,
	                     cm.getColumnWidth(adjustCol)- (tw-aw)), true);
	        }
	
	        if(preventRefresh !== true){
	            this.updateAllColumnWidths();
	        }
	
	
	        return true;
	    };
		
	    return view;
	    
	},
	
    constructor: function(config){
    	
    	Ext.apply(this, config);
    	
    	// Equip GridCollection instance with Collection widget generic interface.
        Ext.applyIf(this, Ext.fdl.Collection);
        this.initCollectionWidget();
        
        // Setup GridCollection instance.
            	
    	var me = this;
    	    
        var properties = this.getProperties();                
        var attributes = this.getAttributes();
        
        var store = this.produceStore(properties,attributes);     
        
        this.selButton = new Ext.Button({
            icon: me.context.url + 'lib/ui/icon/page_white_stack.png',
            cls: 'x-btn-text-icon',
            text: '  ',
            menu: new Ext.menu.Menu({
                items: [{
                    text: 'Tout',
                    handler: function(b, e){
                        me.selectAll();
                        me.selButton.applySelection();
                    }
                }, {
                    text: 'Rien',
                    handler: function(b, e){
                        me.unselectAll();
                        me.selButton.applySelection();
                    }
                }, {
                    text: 'Inverse',
                    handler: function(b, e){
                        me.reverseSelection();
                        me.selButton.applySelection();
                    }
                }]
            }),
            listeners: {
                render: function(button){
                    button.applySelection();
                }
            }
        });
        
        this.selButton.applySelection = function(){
            this.setText(me.selection.count() + ''); // Concatening an empty string converts number to string.
            this.selTooltip = new Ext.ToolTip({
                target: this.getEl(),
                html: me.selection.count() + ' document(s) sélectionné(s)',
                hideDelay: 0,
                showDelay: 0
            });
        };
        
        this.pagingBar = new Ext.PagingToolbar({
            pageSize: this.pageSize || 20,
            store: store,
            displayInfo: true,
            displayMsg: 'Documents {0} - {1} de {2}',
            afterPageText: 'de {0}',
            emptyMsg: "Aucun document",
            items: ['-', this.selButton],
            // Special override of private
            // Allow dynamic loading of search results
            doLoad: function(start, server){           	
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
        
        if(this.collectionLoad){
		    // Disable navigator context menu
	        this.pagingBar.on('afterrender',function() {				
				me.reload(true);
	        },this);
	    }
	    
	    var selModelType = 'Ext.grid.RowSelectionModel';
	    if(this.checkboxSelection){
	    	selModelType = 'Ext.grid.CheckboxSelectionModel';
	    }
	    
	    this.selModel = new (eval(selModelType))({
            listeners: {
                beforerowselect: function(t, i, ke, record){
                
                    if (ke == false) {
                        me.selection.mainSelector = 'none';
                        me.selection.clearSelection();
                    }
                                           
                    return true;
                    
                },
                rowselect: function(t, i, record){
                
                    var doc = record.get('_fdldoc');
                    if (me.selection.mainSelector == 'all') {
                        me.selection.removeFromList(doc);
                    }
                    else {
                        me.selection.insertToList(doc);
                    }
                    
                    me.selButton.applySelection();
                    
/**
 * @event documentselect fires when a document is selected.
 * @param {Ext.fdl.DocumentSelection} selection
 * @param {Integer} document reference
 */
                    me.fireEvent('documentselect',me.selection,doc.id);
                    me.onDocumentSelect(me.selection,doc.id);
                
                },
                rowdeselect: function(t, i, record){
                
                    var doc = record.get('_fdldoc');
                    if (me.selection.mainSelector == 'all') {
                        me.selection.insertToList(doc);
                    }
                    else {
                        me.selection.removeFromList(doc);
                    }
                    
                    me.selButton.applySelection();                    
                   
/**
 * @event documentdeselect fires when a document is deselected.
 * @param {Ext.fdl.DocumentSelection} selection
 * @param {Integer} document reference
 */
                    me.fireEvent('documentdeselect',me.selection,doc.id);
                    me.onDocumentDeselect(me.selection,doc.id);
                
                },
                selectionchange: {
                
                	fn: function(t){
/**
 * @event documentselectionchange fires when the document selection changes.
 * @param {Ext.fdl.DocumentSelection} selection
 */
                		me.fireEvent('documentselectionchange',me.selection);
                		me.onSelectionChange(me.selection);
                	},
                	buffer: 50 // Used to avoid triggering twice the selectionchange.
                	
                }
                	
            }
        });
	    
        
        var columns = this.getColumns(properties, attributes);
        
        var plugins = [];
        
        if (this.filterColumns == true) {
            plugins.push(new Ext.ux.grid.FilterRow());
        }
        
        var view = this.produceView();

        Ext.applyIf(config,{
        	                    
            store: store,
            enableDragDrop: true,
            ddGroup: 'docDD',
            
            loadMask: {
                msg: 'Chargement...'
            },
            stripeRows: true,
            columnLines: true,
            hideHeaders: this.hideHeaders,
            columns: columns,
            
            plugins: plugins,
            
            view: view,
                                    
            // paging bar on the bottom
            bbar: this.pagingBar
            
        });
           	
        this.setupDragDrop();        
        
        Ext.fdl.GridCollection.superclass.constructor.call(this,config);
        
        this.on('rowcontextmenu',function(grid,rowIndex,e){
	       	if(!grid.getSelectionModel().isSelected(rowIndex)){
           		grid.getSelectionModel().selectRow(rowIndex,e.ctrlKey);
           	}
           	var dDoc = grid.getStore().getAt(rowIndex).get('_fdldoc');
           	me.displayContextMenu(dDoc.getProperty('id'),e);
		});
		
		this.on('rowdblclick', function(grid, rowIndex, e){
        	var dDoc = grid.getStore().getAt(rowIndex).get('_fdldoc');
            me.displayDocument(dDoc.getProperty('id'), 'view', e);
            me.fireEvent('dblclick', me, dDoc);
        });
                
        this.on('columnmove', function(oldIndex,newIndex){
           	me.setColumnOrderUserTag(me.getColumnModel().config);
        });
        
        this.on('columnresize', function(columnIndex,newSize){
           	me.getColumnModel().getColumnAt(columnIndex).forcedWidth = me.getColumnModel().getColumnAt(columnIndex).width;
          	// New size is the user defined size
           	// Here we take the real size that the column gets (given that forceFit may not allow column to take newSize)
           	me.setColumnUserTag(me.getColumnModel().getColumnId(columnIndex),'width',me.getColumnModel().getColumnAt(columnIndex).width);
        });
        
        this.setupFilter();
        
        this.getColumnModel().on('hiddenchange',function(columnModel,columnIndex,hidden){
        	me.setColumnUserTag(columnModel.getColumnId(columnIndex),'hide',hidden);
        });
        
        // Disable navigator context menu
        this.on('render',function(gridPanel) {
        	gridPanel.getEl().on("contextmenu", Ext.emptyFn, null, {preventDefault: true});
	    });
        
        // Display selections which have been kept on various pages.
        this.getStore().on('load', function(store, record, options){
        	if(this.loaded){
            	me.applySelection();
            }
            this.loaded = true;
        });
        
    },
    
    display: function(){
    	console.log('Ext.fdl.Collection.display() is deprecated. They are panels and can be used directly as such.');
    	return this;
    },
    
    toString: function(){
        return 'Ext.fdl.GridCollection';
    },
    
    /**
     * @cfg {Boolean} rememberUserColumns True to automatically store and retrieve column setup as user tag.
     */
    rememberUserColumns: true,
//    
//    /**
//     * @cfg {Boolean} movable True to allow column moving. Default to true.
//     */
//    movable: false,
//    
//    /**
//     * @cfg {Array|Boolean} sortableColumns Array of column name strings to allow column sorting, true to enable sorting for all, false to disable sorting. Default to false.
//     */
//    sortableColumns: false,
//    
//    /**
//     * @cfg {Array} hiddenColumns Array of column name strings to hide columns, or false for no hidden column. Default to false.
//     */
//    hiddenColumns: false,
//    
//    /**
//     * @cfg {Function} formatColumns Function to format column display. Default to null.
//     */
//    formatColumns: null,
//    
    /**
     * @cfg {Array|Boolean} filterColumns Array of column name strings to allow column filtering, true to enable filtering for all, false to disable filtering. Default to false.
     */
    filterColumns: true,
    
    setupFilter: function(){

        var me = this ;
        
        if (this.filterColumns === true) {
            var criteria = this.context.getSearchCriteria();
            var fProperties = this.context.getFilterableProperties();
            
            for (var i = 0; i < fProperties.length; i++) {
            
                var info = this.context.getPropertyInformation(fProperties[i]);
                
                if (Ext.getCmp(this.id + '-filter-editor-' + fProperties[i])) {
                    Ext.getCmp(this.id + '-filter-editor-' + fProperties[i]).destroy();
                }
                    
                new Ext.fdl.FilterFieldPanel({
                    criteria: criteria[info.type],
                    property: fProperties[i],
                    propertyInfo: info,
                    id: this.id + '-filter-editor-' + fProperties[i],
                    listeners: {
                        filter: function(panel, filter){
                            
                            if (!me.volatilefilter) {
                                me.volatilefilter = new Fdl.DocumentFilter();
                                me.volatilefilter.criteria = [];
                            }
                            
                            var criteria = me.volatilefilter.criteria;
                            //console.log('Criteria', criteria, 'filter', filter);
                            
                            var found = false;
                            for (var i = 0; i < criteria.length; i++) {
                                if (criteria[i].left == filter.left) {
                                    if (filter.operator) {
                                        criteria[i] = filter;
                                    }
                                    else {
                                        criteria.remove(criteria[i]);
                                    }
                                    found = true;
                                }
                            }
                            
                            if (!found) {
                                me.volatilefilter.criteria.push(filter);
                            }
                            
                            me.pagingBar.doLoad(0);
                        }
                    }
                });
                
            }
            
            if(this.family){
                var fAttributes = this.family.getFilterableAttributes();
                                
                for (var j = 0, l = fAttributes.length; j < l; j++) {
                
                    var attribute = fAttributes[j];
                    
                    if (Ext.getCmp(this.id + '-filter-editor-' + attribute.id)) {
                        Ext.getCmp(this.id + '-filter-editor-' + attribute.id).destroy();
                    }
                    
                    
                    new Ext.fdl.FilterFieldPanel({
                        criteria: criteria[attribute.type],
                        attribute: attribute.id,
                        attributeInfo: attribute,
                        id: this.id + '-filter-editor-' + attribute.id,
                        listeners: {
                            filter: function(panel, filter){
                            
                                if (!me.volatilefilter) {
                                    me.volatilefilter = new Fdl.DocumentFilter();
                                    me.volatilefilter.criteria = [];
                                }
                                
                                var criteria = me.volatilefilter.criteria;
                                //console.log('Criteria', criteria, 'filter', filter);
                                
                                var found = false;
                                for (var i = 0; i < criteria.length; i++) {
                                    if (criteria[i].left == filter.left) {
                                        if (filter.operator) {
                                            criteria[i] = filter;
                                        }
                                        else {
                                            criteria.remove(criteria[i]);
                                        }
                                        found = true;
                                    }
                                }
                                
                                if (!found) {
                                    me.volatilefilter.criteria.push(filter);
                                }
                                
                                me.pagingBar.doLoad(0);
                            }
                        }
                    }); 
                    
                }
                
            }
            
        }
        
    },
    
//    
//    /**
//     * @cfg {Array|Boolean} editingColumns Array of column name strings to allow column editing (only applies to attribute columns), true to enable editing for all, false to disable editing. Default to false.
//     */
//    editingColumns: false,
//    
//    /**
//     * @cfg {Array|Boolean} lockingColumns Array of column name strings to allow left column locking, false to disable locking.
//     */
//    lockingColumns: false,
    
    /**
     * @cfg {Boolean} checkboxSelection True to display a column of checkboxes for document selection.
     */
    checkboxSelection: false,
    
    /**
     * Method to apply current DocumentSelection to collection graphic representation.
     */
    applySelection: function(){
    	    
	        var selModel = this.getSelectionModel();
	        	        
	        // Equip selModel with deselectRecords method (that Ext does not provide even though it does provide selectRecords...)
	        selModel.deselectRecords = function(records){
	            var ds = this.grid.store;
	            for (var i = 0, len = records.length; i < len; i++) {
	                this.deselectRow(ds.indexOf(records[i]));
	            }
	        };
	        
	        // Events are suspended because there is no true user selection processed.
	        selModel.suspendEvents(false);
	        
	        if (this.selection.mainSelector == 'all') {
	            selModel.selectAll();
	        }
	        else {
	            selModel.clearSelections();
	        }
	        
	        var sDoc = this.selection.selectionItems;
	        if (sDoc) {
	        
	            var sRec = [];
	            
	            this.getStore().each(function(record){
	            
	                for (var i = 0; i < sDoc.length; i++) {
	                    if ((sDoc[i] == record.get('id'))) {
	                        sRec.push(record);
	                    }
	                }
	                
	            });
	            
	            if (this.selection.mainSelector == 'all') {
	                selModel.deselectRecords(sRec, true);
	            }
	            else {
	                selModel.selectRecords(sRec, true);
	            }
	            
	        }
	        
	        selModel.resumeEvents();
        
    },
    
    
    /**
     * @method reload
     */
    reload: function(server,refreshColumns){    	    	
        
        var rank = this.getBottomToolbar().cursor || 0 ;
        
        this.getBottomToolbar().doLoad(rank, server);
        if(refreshColumns === true){
        	this.refreshColumns();
        }
    },
    
    /**
     * Apply loadmask
     * @method mask
     */
    mask: function(){
        if (this.loadMask) {
            this.loadMask.show();
        }
    },
    
    /**
     * Remove loadmask
     * @method unmask
     */
    unmask: function(){
        if (this.loadMask) {
            this.loadMask.hide();
        }
    },
    
    /**
     * @cfg {Object} userColumnConfig This config object parameter describes the columns that must be generated in the grid.
     * By default displayable attributes and properties are displayed. Syntax of this object is the following :
     * {
     * 	myPropertyOrAttributeId-1: {
     * 		hide: boolean, //Is the column shown or hidden at display. Default true.
     * 		width: integer, //In pixel. If not specified, will size according to available width.
     * 		order: integer //Starting position for the column (beginning at 0).
     * 	},
     *  myPropertyOrAttributeId-2: {
     *  
     *  } 
     * }
     * See UI Example 2 for a simple use case.
     */
    userColumnConfig: false,
    
//    // private
//    getColumns: function(properties, attributes){
//    	
//    	var me = this;
//    	
//    	var userColumnConfig = this.userColumnConfig || this.getColumnUserTag();
//    	    
//        var columns = [];
//        
//        for (var j = 0; j < properties.length; j++) {
//        
//            var info = this.context.getPropertyInformation(properties[j]);
//            
//            var width = 80;
//            var minWidth = 100;
//            var maxWidth = 300;
//            if (properties[j] == 'id') {
//                width = 40;
//            }
//            if (properties[j] == 'title') {
//                width = 120;
//            }
//            if (properties[j] == 'icon') {
//                width = 25;
//                minWidth = 25;
//                maxWidth = 25;
//            }
//            if (properties[j] == 'revision') {
//                width = 25;
//            }
//            if (properties[j] == 'cdate') {
//                maxWidth = 100;
//            }            
//            
//            var hidden = true;
//            if (properties[j] == 'title' ||
//            properties[j] == 'icon' ||
//            properties[j] == 'cdate') {
//                hidden = false;
//            }
//            
//            var header = info['label'];
//            if(properties[j] == "locked"){
//            	header = me.context._("eui::lock_as_name");
//            }
//            
//            if(userColumnConfig && userColumnConfig[properties[j]] && userColumnConfig[properties[j]].hide != undefined ){
//            	hidden = userColumnConfig[properties[j]].hide ;
//            }
//            
//            var forcedWidth = false ;
//            if(userColumnConfig && userColumnConfig[properties[j]] && userColumnConfig[properties[j]].width != undefined ){
//            	var width = userColumnConfig[properties[j]].width;
//            	var forcedWidth = width ;
//            }
//            
//            if(userColumnConfig && userColumnConfig[properties[j]] && userColumnConfig[properties[j]].order != undefined ){
//            	var order = userColumnConfig[properties[j]].order ;
//            }
//            
//            var columnConfig = {
//                id: properties[j],
//                header: header,
//                dataIndex: properties[j],
//                sortable: info['sortable'],
//                width: width,
//                forcedWidth: forcedWidth,
//                minWidth: minWidth,
//                maxWidth: maxWidth,
//                hidden: hidden,
//                renderer: function(value, metaData, record, rowIndex, colIndex, store){
//                
//                    if (record.get('_fdldoc')) { // There is a problem on sorting when this is not tested, and it is strange.
//                        //                        if (this.dataIndex == 'owner') {
//                        //                            return "<a class='docid' " +
//                        //                            'onclick="event.stopPropagation();window.Fdl.ApplicationManager.displayDocument(' +
//                        //                            value +
//                        //                            ');return false;">' +
//                        //                            record.get('_fdldoc').getProperty('ownername') +
//                        //                            "</a>";
//                        //                        }
//                        
//                        if (this.dataIndex == 'owner') {
//                            return record.get('_fdldoc').getProperty('ownername');
//                        }
//                        
//                        if (this.dataIndex == 'locked'){
//                        	if(value == 0){
//                        		return '' ;
//                        	} else if (value == -1){
//                        		return String.format('<img src="{0}" style="height:15px;width:15px;" />', me.context.url + 'FDL/Images/readfixed.png');
//                        	} else if (value == me.context.getUser().id){
//                            	return String.format('<img src="{0}" style="height:15px;width:15px;" />', me.context.url + 'FDL/Images/greenlock.png');
//                        	} else if (value != me.context.getUser().id){
//                        		return String.format('<img src="{0}" style="height:15px;width:15px;" />', me.context.url + 'FDL/Images/redlock.png');
//                        	}
//                        }
//                        
//                        if (this.dataIndex == 'revdate') {
//                            return record.get('_fdldoc').getProperty('mdate');
//                        }
//                        
//                        if (this.dataIndex == 'fromid') {
//                            return record.get('_fdldoc').getProperty('fromtitle');
//                        }
//                        if (this.dataIndex == 'cdate') {
//                            // not view hours
//                            return String.format('<span ext:qtip="{0}">{1}</span>', value, value.substr(0, 10));
//                        }
//                        
//                        if (this.dataIndex == 'icon') {
//                            return String.format('<img src="{0}" style="height:15px;width:15px;" />', record.get('_fdldoc').getIcon({
//                                width: 15
//                            }));
//                        }
//                    }
//                    
//                    return value;
//                }
//            };
//            
//            if(!userColumnConfig || order == undefined){
//            	columns.push(columnConfig);
//            } else {
//            	if(userColumnConfig[properties[j]]){
//            		columns[order] = columnConfig;
//            	}
//            }
//        }
//        
//        for (var j = 0; j < attributes.length; j++) {
//        
//            var attribute = this.family.getAttribute(attributes[j]);
//            
//            //			console.log('ATTRIBUTE IN COLUMN',attribute);
//            
//            if(userColumnConfig && userColumnConfig[attributes[j]] && userColumnConfig[attributes[j]].hide != undefined ){
//            	hidden = userColumnConfig[attributes[j]].hide ;
//            }
//            
//            var forcedWidth = false;
//            if(userColumnConfig && userColumnConfig[attributes[j]] && userColumnConfig[attributes[j]].width != undefined ){
//            	var width = width;
//            	var forcedWidth = width ;
//            }
//            
//            if(userColumnConfig && userColumnConfig[attributes[j]] && userColumnConfig[attributes[j]].order != undefined ){
//            	var order = userColumnConfig[attributes[j]].order ;
//            }
//            
//            var columnConfig = {
//                id: attributes[j],
//                header: attribute.getLabel(),
//                width: width,
//                sortable: attribute.isSortable(),
//                forcedWidth: forcedWidth,
//                dataIndex: attributes[j],
//                renderer: function(value, metaData, record, rowIndex, colIndex, store){
//                	if(record.get('_fdldoc')){
//                    	return record.get('_fdldoc').getDisplayValue(this.dataIndex);
//                	} else {
//                		return value;
//                	}
//                },
//                hidden: !attribute.inAbstract
//            };
//            
//            if(!userColumnConfig || order == undefined){
//            	columns.push(columnConfig);
//            } else {
//            	if(userColumnConfig[attributes[j]]){
//            		columns[order] = columnConfig;
//            	}
//            }
//        }
//        
//        
//        if(this.checkboxSelection){
//        	columns.splice(0,0,this.selModel);
//        }
//        
//        return columns;
//        
//    },
    
    refreshColumns: function(){
        //this method is implemented afterrender
    },
        
    /**
     * Set an attribute specific to user in user tag for a given column
     * @method setColumnUserTag
     * @param {} columnId
     * @param {} attribute
     * @param {} value
     */
    setColumnUserTag: function(columnId,attribute,value){

	    if(this.rememberUserColumns){
	    	(function(){
	    		
	        	var columns = this.getColumnUserTag();
	        	
	        	if(columns){
	        		        	
		        	if(!columns[columnId]){
		        		columns[columnId] = {};	
		        	}
		        	
		        	columns[columnId][attribute] = value ;
		        	
		        	if(this.collection){
		        	
			        	this.collection.addUserTag({
			        		tag: 'columns',
			        		comment: JSON.stringify(columns)
			        	});
			        	
		        	} else if (this.familyDocument) {
		        		
	        			this.familyDocument.addUserTag({
			        		tag: 'columns',
			        		comment: JSON.stringify(columns)
			        	});
	        			
	        		}
	        	
	        	}		        	
		    	
	    	}).defer(10,this);
	    }
    	
    },
    
    setColumnOrderUserTag: function(columnConfig){

	    if(this.rememberUserColumns){
	    	(function(){
	    		
	    			//console.log('COLUMN MOVE',oldIndex,newIndex,this.gridPanel.getColumnModel().getColumnId(newIndex));		        		
		        		        	
		        	var columns = this.getColumnUserTag();
		        	
		        	if(columns){
		        	
		        		for(var i = 0, l = columnConfig.length; i < l ; i++){
		        			
		        			var columnId = columnConfig[i].id ;
		        			
		        			if(!columns[columnId]){
				        		columns[columnId] = {};	
				        	}
				        	
				        	columns[columnId]['order'] = i ;
				        	
		        		}
		        		
		        		if(this.collection){
		        					        	
				        	this.collection.addUserTag({
				        		tag: 'columns',
				        		comment: JSON.stringify(columns)
				        	});
			        	
		        		} else if (this.familyDocument) {
		        		
		        			this.familyDocument.addUserTag({
				        		tag: 'columns',
				        		comment: JSON.stringify(columns)
				        	});
		        			
		        		}
		        	
		        	}
		        	
		    	
	    	}).defer(10,this);
	    }
    	
    },
    
    /**
     * Get column config stored in user tag for this collection
     * @method getColumnUserTag
     * @return {Boolean|Object} Column tag object if applicable, else false. Example : { id_column_1 : { width : 30, hide : true, index : 2 }}
     */
    getColumnUserTag: function(){
    	
    	if(this.rememberUserColumns){
    		
    		//console.log('COLUMN TAG COLLECTION');
    		
	    	// Only applicable when representing a document (not a volatile search)
	    	if(this.collection){
	    		
	    		//console.log('COLLECTION TATA');
	    		
	    		var tags = this.collection.getUserTags();
	    		if(tags.length != 0){
	    			
	    			//console.log('TAGS',tags);
	    			
			        if(!tags.columns){
			        	var columns = {};
			        } else {
			        	var columns = eval("("+tags.columns+")");
			        }
			        return columns ;
	    		} else {
	    			if(this.collection && this.collection.getValue("se_famid")){
	    				
	    				//console.log('COLLECTION TOTO',this.collection,this.collection.getValue("se_famid"));
	    				
	    				this.familyDocument = this.context.getDocument({
		    				id: this.collection.getValue("se_famid"),
		    				useCache: true
		    			});
	    				
	    				if (this.familyDocument) {
		    			var tags = this.familyDocument.getUserTags();
			    		if(tags){
					        if(!tags.columns){
					        	var columns = {};
					        } else {
					        	var columns = eval("("+tags.columns+")");
					        }
			    		}
	    				} else {
	                        Ext.Msg.alert('Error', this.context.getLastErrorMessage());
	    				}
			    		return columns ;
		    			
	    			}
	    		}
		        
	    	} else if (this.search) {
	    			    		
	    		if(this.search.family){
	    			
	    			this.familyDocument = this.context.getDocument({
	    				id: this.search.family,
	    				useCache: true
	    			});
	    			if (this.familyDocument) {
	    			var tags = this.familyDocument.getUserTags();
		    		if(tags){
				        if(!tags.columns){
				        	var columns = {};
				        } else {
				        	var columns = eval("("+tags.columns+")");
				        }
		    		}
	    			} else {
	    				Ext.Msg.alert('Error', this.context.getLastErrorMessage());
	    			}
	    			
	    		}
	    		
	    		//console.log('SEARCH USER TAG', this.search);
	    		
	    		return columns ;
	    		
	    	}
	    	
    	}
    	
    	return false;
    	
    },
    
    // private
    setupDragDrop: function(){
    	
    	var me = this ;
    	
    	// Handle Drag
        this.on('afterrender', function(panel){
        
            me.refreshColumns = function(){
                if (this.rendered) {
                    // Recalculate columns
                    var properties = me.getProperties();
                    var attributes = me.getAttributes();
                    var columns = me.getColumns(properties, attributes);
                    var colModel = new Ext.grid.ColumnModel(columns);
                    
                    var start2 = new Date();                    
                    me.reconfigure(me.getStore(), colModel);                    
                    var end2 = new Date();
                    console.log('Execution time reconfigure : ' + (end2 - start2) + ' ms.');
                    
                    me.getView().fitColumns(false, false);
                
                }
            };
            
            var dragZone = me.getView().dragZone;
            
            dragZone.onBeforeDrag = function(data, e){
                me.notifyDocumentDrag(me.selection);
            };
            
            dragZone.onInitDrag = function(x, y){
                me.displayProxy(me, true);
                this.onStartDrag(x, y);
                return true;
            };
            
            dragZone.getDragData = function(e){
                var data = Ext.grid.GridDragZone.prototype.getDragData.call(this, e);
                console.log('DRAG DATA',data);
                data.component = me;
                data.selection = me.selection;
                return data;
            };
                        
            // This function returns the drag proxy
            me.getProxy = function(){
                return me.getView().dragZone.getProxy();
            };
            
            me.isDragging = function(){
            	return me.getView().dragZone.dragging;
            };
            
            me.getDropZone = function(){
            	return me.dropTarget;
            };
            
            /* Handling of keyboard input to update drag proxy */
            // Define listener function
            var onKeyChange = function(){
            	me.displayProxy(me.hoveredWid, true);
            };
            
            // Setup listener on keyboard input object
            Ext.fdl.KeyBoard.on('keychange',onKeyChange);
			
            // Setup removal of listener when panel is destroyed 
			me.on('destroy',function(){
				Ext.fdl.KeyBoard.un('keychange',onKeyChange);
			});
			/* EO Handling keyboard input */
            
            // Handle drop
            if (me.collection && me.collection.isFolder()) {
            
                var dropTarget = new Ext.dd.DropTarget(me.getEl(), {
                
                    ddGroup: 'docDD',
                    
                    notifyDrop: function(source, e, data){
                    
                        if (data.component.notifyDocumentDragOver(data.component, data.component.collection, me.collection, data.selection, data.component.overDoc)) {
                        
                        	var behaviour = data.component.dragBehaviour(data.component.collection, me.collection, data.selection, data.component.overDoc);
                        	
                        	if(behaviour == 'move'){
                        		data.component.mask();
                        	}
                        	
                            // Suspend event because row select is fired elsewhere.
                            // Which is a bug from Ext.
                            me.mask();                            
                            
                            me.selModel.suspendEvents(false);
                            if (data.component && data.component.selModel) {
                                data.component.selModel.suspendEvents(false);
                            }
                            
                            //console.log('DATA', data);
                            
                            // Get Fdl.Document on which Fdl.Selection is dropped.
                            var t = Ext.lib.Event.getTarget(e);
                            var rowIndex = me.getView().findRowIndex(t);
                            
                            //console.log('ROW INDEX', rowIndex);
                            
                            var dropDoc = null;
                            if (typeof rowIndex != "undefined" && rowIndex !== false) {
                                dropDoc = me.getStore().getAt(rowIndex).get('_fdldoc');
                            }
                            
                            me.selModel.resumeEvents();
                            if (data.component && data.component.selModel) {
                                data.component.selModel.resumeEvents();
                            }
                            
                            (function(){
                                // Invoke notifyDocumentDrop and get boolean result.
                                var ret = me.notifyDocumentDrop(data.component, data.component.collection, me.collection, data.selection, dropDoc);
                                
                                me.unmask();
                                data.component.unmask();
                                
                                if (!ret) {
                                    Ext.Msg.alert('Warning', 'Problem during drag and drop');
                                }
                                
                            }).defer(5);
                            
                        }
                        
                        return true;
                        
                    },
                    
                    notifyEnter: function(source, e, data){
                        data.component.displayProxy(me, true);
                    },
                    
                    notifyOver: function(source, e, data){
                    
                        // Get Fdl.Document on which Fdl.Selection is over.
                        var t = Ext.lib.Event.getTarget(e);
                        var rowIndex = me.getView().findRowIndex(t);
                        
                        if (typeof rowIndex != "undefined" && rowIndex !== false) {
                            var newOverDoc = me.getStore().getAt(rowIndex).get('_fdldoc');
                            if (!data.component.overDoc || (data.component.overDoc.getProperty('id') != newOverDoc.getProperty('id'))) {
                                data.component.overDoc = newOverDoc;
                                data.component.displayProxy(me);
                            }
                        }
                        else {
                            if (data.component.overDoc) {
                                data.component.overDoc = null;
                                data.component.displayProxy(me);
                            }
                        }
                        
                        //console.log('OVER', source, source.getDragData(e), rowIndex, data, data.component.overDoc);
                        var ret = data.component.notifyDocumentDragOver(data.component, data.component.collection, me.collection, data.selection, data.component.overDoc);
                        
                        if (ret) {
                            return this.dropAllowed;
                        }
                        else {
                            return this.dropNotAllowed;
                        }
                        
                    },
                    
                    notifyOut: function(source, e, data){
                        data.component.overDoc = null;
                        data.component.displayProxy();
                    }
                    
                });
            } else {
				
				var dropTarget = new Ext.dd.DropTarget(me.getEl(), {
                
                    ddGroup: 'docDD',
                    
                    notifyDrop: function(source, e, data){
                    
                        if (data.component.notifyDocumentDragOver(data.component, data.component.collection, me.collection, data.selection, data.component.overDoc)) {
                        
                            // Suspend event because row select is fired elsewhere.
                            // Which is a bug from Ext.
                            
                            me.selModel.suspendEvents(false);
                            if (data.component) {
                                data.component.selModel.suspendEvents(false);
                            }
                            
                            //console.log('DATA', data);
                            
                            // Get Fdl.Document on which Fdl.Selection is dropped.
                            var t = Ext.lib.Event.getTarget(e);
                            var rowIndex = me.getView().findRowIndex(t);
                            
                            //console.log('ROW INDEX', rowIndex);
                            
                            var dropDoc = null;
                            if (typeof rowIndex != "undefined" && rowIndex !== false) {
                                dropDoc = me.getStore().getAt(rowIndex).get('_fdldoc');
                            }
                            
                            me.selModel.resumeEvents();
                            if (data.component) {
                                data.component.selModel.resumeEvents();
                            }
                            
                            (function(){
                                // Invoke notifyDocumentDrop and get boolean result.
                                var ret = me.notifyDocumentDrop(data.component, data.component.collection, me.collection, data.selection, dropDoc);
                                  
                                if (!ret) {
                                    Ext.Msg.alert('Warning', 'Problem during drag and drop');
                                }
                                
                            }).defer(5);
                            
                        }
                        
                        return true;
                        
                    },
                    
                    notifyEnter: function(source, e, data){
                        data.component.displayProxy(me);
                    },
                    
                    notifyOver: function(source, e, data){
                    
                        // Get Fdl.Document on which Fdl.Selection is over.
                        var t = Ext.lib.Event.getTarget(e);
                        var rowIndex = me.getView().findRowIndex(t);
                        
                        if (typeof rowIndex != "undefined" && rowIndex !== false) {
                            var newOverDoc = me.getStore().getAt(rowIndex).get('_fdldoc');
                            if (!data.component.overDoc || (data.component.overDoc.getProperty('id') != newOverDoc.getProperty('id'))) {
                                data.component.overDoc = newOverDoc;
                                data.component.displayProxy(me);
                            }
                        }
                        else {
                            if (data.component.overDoc) {
                                data.component.overDoc = null;
                                data.component.displayProxy(me);
                            }
                        }
                        
                        //console.log('OVER', source, source.getDragData(e), rowIndex, data, data.component.overDoc);
                        var ret = data.component.notifyDocumentDragOver(data.component, data.component.collection, me.collection, data.selection, data.component.overDoc);
                        
                        if (ret) {
                            return this.dropAllowed;
                        }
                        else {
                            return this.dropNotAllowed;
                        }
                        
                    },
                    
                    notifyOut: function(source, e, data){
                        data.component.overDoc = null;
                        data.component.displayProxy();
                    }
				
				});
				
			}
			
			me.dropTarget = dropTarget ;
            
        });
        
    },
    
    setCollection: function(collection){
                                
        var me = this ;
        
        this.collection = collection;
        this.reload(true,true);
                
        this.setupFilter();
        
        this.getColumnModel().on('hiddenchange',function(columnModel,columnIndex,hidden){
            me.setColumnUserTag(columnModel.getColumnId(columnIndex),'hide',hidden);
            if(me.plugins[0] && me.plugins[0].onColumnHiddenChange){
                me.plugins[0].onColumnHiddenChange(me.getColumnModel(),columnIndex,hidden);
            } // Should target specifically the filter plugin;
        });
        
        this.getView().refresh();
        
        this.selection = new Fdl.DocumentSelection({
            context: this.context,
            collectionId: this.collection ? this.collection.getProperty('id') : null,
            mainSelector: 'none'
        });
        
        this.applySelection();
        this.selButton.applySelection();
        
    },
    
    setVolatileFilter: function(filter){
        
        if (!this.volatilefilter) {
            this.volatilefilter = new Fdl.DocumentFilter();
            this.volatilefilter.criteria = [];
        }
                                
        var criteria = this.volatilefilter.criteria;
        //console.log('Criteria', criteria, 'filter', filter);
        
        var found = false;
        for (var i = 0; i < criteria.length; i++) {
            if (criteria[i].left == filter.left) {
                if (filter.operator) {
                    criteria[i] = filter;
                }
                else {
                    criteria.remove(criteria[i]);
                }
                found = true;
            }
        }
        
        if (!found) {
            this.volatilefilter.criteria.push(filter);
        }
        
        //me.pagingBar.doLoad(0);
        
    },
    
    unsetVolatileFilter: function(left){
                                
        if (this.volatilefilter) {
            var criteria = this.volatilefilter.criteria;
            
            if (criteria) {
                for (var i = 0; i < criteria.length; i++) {
                    if (criteria[i].left == left) {
                        criteria.remove(criteria[i]);
                    }
                }
            }
        }
        
    },
    
    restrictColumns: null,
    
    excludeColumns: null,
    
    getColumns: function(properties, attributes){
        
        var me = this;

        var userColumnConfig = this.userColumnConfig || this.getColumnUserTag();
            
        var columns = [];
        
        for (var j = 0; j < properties.length; j++) {
                                                
            if((!this.restrictColumns || this.restrictColumns.indexOf(properties[j]) != -1) &&
            (!this.excludeColumns || this.excludeColumns.indexOf(properties[j]) == -1)){
                                                
                var info = this.context.getPropertyInformation(properties[j]);
                
                var width = 80;
                var minWidth = 100;
                var maxWidth = 300;
                if (properties[j] == 'id') {
                    width = 40;
                }
                if (properties[j] == 'title') {
                    width = 120;
                }
                if (properties[j] == 'icon') {
                    width = 25;
                    minWidth = 25;
                    maxWidth = 25;
                }
                if (properties[j] == 'revision') {
                    width = 25;
                }
                if (properties[j] == 'cdate') {
                    maxWidth = 100;
                }            
                
                var hidden = true;
                if (properties[j] == 'title' ||
                properties[j] == 'icon' ||
                properties[j] == 'cdate') {
                    hidden = false;
                }
                
                var header = info['label'];
                if(properties[j] == "locked"){
                    header = me.context._("eui::lock_as_name");
                }
                
                if(userColumnConfig && userColumnConfig[properties[j]] && userColumnConfig[properties[j]].hide != undefined ){
                    hidden = userColumnConfig[properties[j]].hide ;
                }
                
                var forcedWidth = false ;
                if(userColumnConfig && userColumnConfig[properties[j]] && userColumnConfig[properties[j]].width != undefined ){
                    var width = userColumnConfig[properties[j]].width;
                    var forcedWidth = width ;
                }
                
                if(userColumnConfig && userColumnConfig[properties[j]] && userColumnConfig[properties[j]].order != undefined ){
                    var order = userColumnConfig[properties[j]].order ;
                }
                
                var columnConfig = {
                    id: properties[j],
                    header: header,
                    dataIndex: properties[j],
                    sortable: info['sortable'],
                    width: width,
                    forcedWidth: forcedWidth,
                    minWidth: minWidth,
                    maxWidth: maxWidth,
                    hidden: hidden,
                    renderer: function(value, metaData, record, rowIndex, colIndex, store){
                    
                        if (record.get('_fdldoc')) { // There is a problem on sorting when this is not tested, and it is strange.
                            //                        if (this.dataIndex == 'owner') {
                            //                            return "<a class='docid' " +
                            //                            'onclick="event.stopPropagation();window.Ext.aeres.UI.displayDocument(' +
                            //                            value +
                            //                            ');return false;">' +
                            //                            record.get('_fdldoc').getProperty('ownername') +
                            //                            "</a>";
                            //                        }
                            if (this.dataIndex == 'owner') {
                                return Fdl.encodeHtmlTags(record.get('_fdldoc').getProperty('ownername'));
                            }
                            
                            if (this.dataIndex == 'locked'){
                                if(value == 0){
                                    return '' ;
                                } else if (value == -1){
                                    return String.format('<img src="{0}" style="height:15px;width:15px;" />', me.context.url + 'FDL/Images/readfixed.png');
                                } else if (value == me.context.getUser().id){
                                    return String.format('<img src="{0}" style="height:15px;width:15px;" />', me.context.url + 'FDL/Images/greenlock.png');
                                } else if (value != me.context.getUser().id){
                                    return String.format('<img src="{0}" style="height:15px;width:15px;" />', me.context.url + 'FDL/Images/redlock.png');
                                }
                            }
                            
                            if (this.dataIndex == 'revdate') {
                                return record.get('_fdldoc').getProperty('mdate');
                            }
                            
                            if (this.dataIndex == 'fromid') {
                                return Fdl.encodeHtmlTags(record.get('_fdldoc').getProperty('fromtitle'));
                            }
                            if (this.dataIndex == 'cdate') {
                                // not view hours
                                return String.format('<span ext:qtip="{0}">{1}</span>', value, value.substr(0, 10));
                            }
                            
                            if (this.dataIndex == 'icon') {
                                return String.format('<img src="{0}" style="height:15px;width:15px;" />', record.get('_fdldoc').getIcon({
                                    width: 15
                                }));
                            }
                            
                            if (this.dataIndex == 'state') {
                                return record.get('_fdldoc').getLocalisedState();
                            }
                            
                        }
                        
                        return Fdl.encodeHtmlTags(value);
                    }
                };
                
                if(!userColumnConfig || !userColumnConfig[properties[j]] || order == undefined){
                    columns.push(columnConfig);
                } else {
                    if(userColumnConfig[properties[j]]){
                        //columns[order] = columnConfig;
                        columns.splice(order,0,columnConfig);
                    }
                }
            
            }
        }
        
        for (var j = 0; j < attributes.length; j++) {
        
            var attribute = this.family.getAttribute(attributes[j]);
            
            
            if ((!this.restrictColumns || this.restrictColumns.indexOf(attributes[j]) != -1)&&
            (!this.excludeColumns || this.excludeColumns.indexOf(attributes[j]) == -1)) {
            
                //            console.log('ATTRIBUTE IN COLUMN',attribute);
                
                var hidden = !attribute.inAbstract ;
                if (userColumnConfig && userColumnConfig[attributes[j]] && userColumnConfig[attributes[j]].hide != undefined) {
                    hidden = userColumnConfig[attributes[j]].hide;
                }
                
                var forcedWidth = false;
                if (userColumnConfig && userColumnConfig[attributes[j]] && userColumnConfig[attributes[j]].width != undefined) {
                    var width = userColumnConfig[attributes[j]].width;
                    var forcedWidth = width;
                }
                
                if (userColumnConfig && userColumnConfig[attributes[j]] && userColumnConfig[attributes[j]].order != undefined) {
                    var order = userColumnConfig[attributes[j]].order;
                }
                
                var columnConfig = {
                    id: attributes[j],
                    header: attribute.getLabel(),
                    width: width,
                    sortable: attribute.isSortable(),
                    forcedWidth: forcedWidth,
                    dataIndex: attributes[j],
                    renderer: function(value, metaData, record, rowIndex, colIndex, store){
                        if (record.get('_fdldoc')) {
                            return Fdl.encodeHtmlTags(record.get('_fdldoc').getDisplayValue(this.dataIndex));
                        }
                        else {
                            return Fdl.encodeHtmlTags(value);
                        }
                    },
                    hidden: hidden
                };
                
                if (!userColumnConfig || !userColumnConfig[attributes[j]] || order == undefined) {
                    columns.push(columnConfig);
                }
                else {
                    if (userColumnConfig[attributes[j]]) {
                        columns.splice(order,0,columnConfig);
                    }
                }
            }
        
        }
        
        var rcolumns = [];
        
        // Clean empty indexes.
        for(var i = 0 ; i < columns.length ; i ++ ){
            if(columns[i] != undefined){
                rcolumns.push(columns[i]);
            }
        }
        
        
        if(this.checkboxSelection){
            rcolumns.splice(0,0,this.selModel);
        }
                
        return rcolumns;
        
    }
    
});

Ext.reg('collectiongridpanel',Ext.fdl.GridCollection);
