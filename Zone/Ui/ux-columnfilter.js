
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

// Source code taken from https://extjs.net/forum/showthread.php?p=386069
// Modified by clement @ anakeen

Ext.namespace('Ext.ux.grid');

Ext.ux.grid.FilterRow = function(config){
    Ext.apply(this, config);
    
    this.addEvents("change");
    
    Ext.ux.grid.FilterRow.superclass.constructor.call(this);
};

Ext.extend(Ext.ux.grid.FilterRow, Ext.util.Observable, {

    init: function(grid){
        this.grid = grid;
        
        // clement		
        grid.addClass('filter-grid');
        // eo clement
        
        var view = grid.getView();
        
        this.applyTemplate();
        
        var gridHandlers = {
            scope: this,
            afterrender: this.renderFields,
            staterestore: this.onColumnChange
        };
        
        grid.on(gridHandlers);
        
        view.on({
            scope: this,
            'beforerefresh': this.onColumnChange,
            'refresh': this.renderFields
        });
        
        // clement
        // For autoExpand
        view.onColumnWidthUpdated = view.onColumnWidthUpdated.createSequence(function(col, w){
            //this.syncFields(col, w);
        	this.syncAllFields();
        }, this);
        
        view.onAllColumnWidthsUpdated = view.onAllColumnWidthsUpdated.createSequence(function(ws, tw){
        	this.syncAllFields();
        }, this);
        // eo clement
        
        var cm = grid.getColumnModel();
        cm.on({
            scope: this,
            'widthchange': this.onColumnWidthChange,
            'hiddenchange': this.onColumnHiddenChange
        });
        
    },
    
    onColumnHiddenChange: function(cm, colIndex, hidden){
        var gridId = this.grid.id;
        var col = cm.getColumnById(cm.getColumnId(colIndex));
        var editorDiv = Ext.get(gridId + '-filter-' + col.id);
       
        if (editorDiv) {
            editorDiv.parent().dom.style.display = hidden ? 'none' : '';
        }
        
        if(!hidden){
        	var filterDiv = Ext.get(gridId + "-filter-" + col.id);
	        var editor = Ext.getCmp(gridId + '-filter-editor-' + col.id);
	        if (editor) {
	            editor.setWidth(cm.getColumnWidth(colIndex) - 2);
	            if (editor.rendered) {
	                filterDiv.appendChild(editor.el);
	            }
	            else {
	                if (editor.getXType() == 'combo') {
	                    editor.on('select', this.onChange, this);
	                }
	                else {
	                    editor.on('change', this.onChange, this);
	                }
	                editor.render(filterDiv);
	            }
	        }
        }   
    },
    
    applyTemplate: function(){
        var grid = this.grid;
        var view = grid.getView();
        var cols = grid.getColumnModel().config;
        
        var colTpl = "";
        Ext.each(cols, function(col){
            var filterDivId = grid.id + "-filter-" + col.id;
            var style = col.hidden ? " style='display:none'" : "";
            colTpl += '<td' + style + '><div class="x-small-editor" id="' + filterDivId + '"></div></td>';
        });
        
        var headerTpl = new Ext.Template('<table border="0" cellspacing="0" cellpadding="0" style="{tstyle}">', '<thead><tr class="x-grid3-hd-row">{cells}</tr></thead>', '<tbody><tr class="new-filter-row">', colTpl, '</tr></tbody>', "</table>");
        
        var view = grid.getView();
        Ext.applyIf(view, {
            templates: {}
        });
        view.templates.header = headerTpl;
    },
    
    onColumnChange: function(){
        var grid = this.grid;
        var cm = grid.getColumnModel();
        var cols = cm.config;
        var gridId = grid.id;
        Ext.each(cols, function(col){
            var editor = Ext.getCmp(gridId + '-filter-editor-' + col.id);
            if (editor && editor.rendered) {
                var el = editor.el.dom;
                var parentNode = el.parentNode;
                if(parentNode){
                	parentNode.removeChild(el);
                }
            }
        }, this);
        this.applyTemplate();
    },
    
    renderFields: function(){
    	
    	//var renderStart = new Date();
    	
        var grid = this.grid;
        var cm = grid.getColumnModel();
        var cols = cm.config;
        var gridId = grid.id;
        Ext.each(cols, function(col){
        	if(!col.hidden){
        		var filterDiv = Ext.get(gridId + "-filter-" + col.id);
	            var editor = Ext.getCmp(gridId + '-filter-editor-' + col.id);
	            if (editor) {
	                editor.setWidth(col.width - 2);
	                if (editor.rendered) {
	                    filterDiv.appendChild(editor.el);
	                }
	                else {
	                    if (editor.getXType() == 'combo') {
	                        editor.on('select', this.onChange, this);
	                    }
	                    else {
	                        editor.on('change', this.onChange, this);
	                    }
	                    if(filterDiv){
	                    	editor.render(filterDiv);
	                    }
	                }
	            }
        	}
        }, this);
        
//        var renderEnd = new Date();
//        console.log('Execution time (grid filter) : ' + (renderEnd - renderStart) + ' ms.');
        
    },
    
    getData: function(){
        var grid = this.grid;
        var cm = grid.getColumnModel();
        var cols = cm.config;
        var gridId = grid.id;
        var data = {};
        Ext.each(cols, function(col){
            if (!col.hidden) {
                var filterDivId = gridId + "-filter-" + col.id;
                var editor = Ext.getCmp(gridId + '-filter-editor-' + col.id);
                if (editor) {
                    data[col.id] = editor.getValue();
                }
            }
        });
        return data;
    },
    
    onChange: function(){
        this.fireEvent("change", {
            filter: this,
            data: this.getData()
        });
    },
    
    clearFilters: function(){
        this.fireEvent("change", {
            filter: this,
            data: {}
        });
    },
    
    // clement
    onColumnResize: function(colIndex, newWidth){
    	//console.log('Column Resize');
    	//this.syncFields(colIndex, newWidth);
    	this.syncAllFields();
    },
    
    onResize: function(panel, adjWidth, adjHeight, rawWidth, rawHeight){
    	//console.log('Resize');
    	//this.syncFields(colIndex, newWidth);
    	this.syncAllFields();
    },    
    
    onColumnWidthChange: function(colModel, colIndex, newWidth){
    	//console.log('Column Width Change');
        this.syncAllFields();
    },
    // eo clement
    
    syncFields: function(colIndex, newWidth){
        var grid = this.grid;
        var cm = grid.getColumnModel();
        var col = cm.getColumnById(cm.getColumnId(colIndex));
        var editor = Ext.getCmp(grid.id + '-filter-editor-' + col.id);
        newWidth = parseInt(newWidth);
        if (editor) {
            editor.setWidth(newWidth - 2);
        }
    },
    
    // clement
    syncAllFields: function(){
    	//console.log('SYNCALLFIELDS');
    	var grid = this.grid;
    	var cm = grid.getColumnModel();
    	for (var i = 0, l = cm.config.length ; i < l ; i++ ){
    	
    		var col = cm.getColumnById(cm.getColumnId(i));
            var editor = Ext.getCmp(grid.id + '-filter-editor-' + col.id);
            var newWidth = cm.getColumnWidth(i);
            if (editor) {
                editor.setWidth(newWidth - 2);
            }
    		
    	}
    }
    // eo clement
    	
});

//Ext.namespace('Ext.ux.grid');
// 
///**
//* @class Ext.ux.grid.FilterRow
//* @extends Ext.util.Observable
//*
//* Grid plugin that adds filtering row below grid header.
//*
//* <p>To add filtering to column, define "filter" property in column
//* config to be an object with the following properties:
//*
//* <ul>
//* <li>field - an instance of a form field component.
//* <li>events - array of event names to listen from this field.
//* Each time one of the events is heard, FilterRow will fire its "change"
//* event. (Defaults to ["change"], which should be implemented by all
//* Ext.form.Field descendants.)
//* </ul>
//*
//* <pre><code>
//columns: [
//{
//header: 'Company',
//width: 160,
//dataIndex: 'company',
//filter: {
//field: new Ext.form.TextField(),
//events: ["keyup", "specialkey"]
//}
//},
//...
//]
//* </code></pre>
//*
//* Based on: http://www.extjs.net/forum/showthread.php?t=55730
//*/
//Ext.ux.grid.FilterRow = Ext.extend(Ext.util.Observable, {
//  constructor: function(config) {
//    Ext.apply(this, config);
//    
//    this.addEvents(
//      /**
//* @event change
//* Fired when any one of the fields is changed.
//* @param {Object} filterValues object containing values of all
//* filter-fields. When column has "id" defined, then property
//* with that ID will hold filter value. When no "id" defined,
//* then numeric indexes are used, starting from zero.
//*/
//      "change"
//    );
//    
//    Ext.ux.grid.FilterRow.superclass.constructor.call(this);
//  },
//  
//  init: function(grid) {
//    this.grid = grid;
//    var cm = grid.getColumnModel();
//    var view = grid.getView();
//    
//    this.applyTemplate();
//    // add class for attatching plugin specific styles
//    grid.addClass('filter-row-grid');
//    
//    // when grid initially rendered
//    grid.on("render", this.renderFields, this);
//    
//    // when Ext grid state restored (untested)
//    grid.on("staterestore", this.onColumnChange, this);
//    
//    // when the width of the whole grid changed
//    grid.on("resize", this.resizeAllFilterFields, this);
//    // when column width programmatically changed
//    cm.on("widthchange", this.onColumnWidthChange, this);
//    // Monitor changes in column widths
//    // newWidth will contain width like "100px", so we use parseInt to get rid of "px"
//    view.onColumnWidthUpdated = view.onColumnWidthUpdated.createSequence(function(colIndex, newWidth) {
//      this.onColumnWidthChange(this.grid.getColumnModel(), colIndex, parseInt(newWidth, 10));
//    }, this);
//    
//    // before column is moved, remove fields, after the move add them back
//    cm.on("columnmoved", this.onColumnChange, this);
//    view.afterMove = view.afterMove.createSequence(this.renderFields, this);
//    
//    // When column hidden or shown
//    cm.on("hiddenchange", this.onColumnHiddenChange, this);
//  },
//  
//  onColumnHiddenChange: function(cm, colIndex, hidden) {
//    var filterDiv = Ext.get(this.getFilterDivId(cm.getColumnId(colIndex)));
//    if (filterDiv) {
//      filterDiv.parent().dom.style.display = hidden ? 'none' : '';
//    }
//    this.resizeAllFilterFields();
//  },
//  
//  applyTemplate: function() {
//    var colTpl = "";
//    this.eachColumn(function(col) {
//      var filterDivId = this.getFilterDivId(col.id);
//      var style = col.hidden ? " style='display:none'" : "";
//      colTpl += '<td' + style + '><div class="x-small-editor" id="' + filterDivId + '"></div></td>';
//    });
//    
//    var headerTpl = new Ext.Template(
//      '<table border="0" cellspacing="0" cellpadding="0" style="{tstyle}">',
//      '<thead><tr class="x-grid3-hd-row">{cells}</tr></thead>',
//      '<tbody><tr class="filter-row-header">',
//      colTpl,
//      '</tr></tbody>',
//      "</table>"
//    );
//    
//    var view = this.grid.getView();
//    Ext.applyIf(view, { templates: {} });
//    view.templates.header = headerTpl;
//  },
//  
//  onColumnChange: function() {
//    this.eachColumn(function(col) {
//      var editor = this.getFilterField(col);
//      if (editor && editor.rendered) {
//        var el = this.getFilterFieldDom(editor);
//        var parentNode = el.parentNode;
//        parentNode.removeChild(el);
//      }
//    });
//    this.applyTemplate();
//  },
//  
//  renderFields: function() {
//    this.eachColumn(function(col) {
//      var filterDiv = Ext.get(this.getFilterDivId(col.id));
//      var editor = this.getFilterField(col);
//      if (editor) {
//        editor.setWidth(col.width - 2);
//        if (editor.rendered) {
//          filterDiv.appendChild(this.getFilterFieldDom(editor));
//        }
//        else {
//          Ext.each(col.filter.events || ["change"], function(eventName) {
//            editor.on(eventName, this.onFieldChange, this);
//          }, this);
//          
//          editor.render(filterDiv);
//        }
//      }
//    });
//  },
//  
//  onFieldChange: function() {
//    this.fireEvent("change", this.getData());
//  },
//  
//  getData: function() {
//    var data = {};
//    this.eachColumn(function(col) {
//      if (!col.hidden) {
//        var editor = this.getFilterField(col);
//        if (editor) {
//          data[col.id] = editor.getValue();
//        }
//      }
//    });
//    return data;
//  },
//  
//  onColumnWidthChange: function(cm, colIndex, newWidth) {
//    this.resizeFilterField(cm.getColumnById(cm.getColumnId(colIndex)), newWidth);
//  },
//  
//  // When grid has forceFit: true, then all columns will be resized
//  // when grid resized or column added/removed.
//  resizeAllFilterFields: function() {
//    var cm = this.grid.getColumnModel();
//    this.eachColumn(function(col, i) {
//      this.resizeFilterField(col, cm.getColumnWidth(i));
//    });
//  },
//  
//  // Resizes filter field according to the width of column
//  resizeFilterField: function(column, newColumnWidth) {
//    var editor = this.getFilterField(column);
//    if (editor) {
//      editor.setWidth(newColumnWidth - 2);
//    }
//  },
//  
//  // Returns HTML ID of element containing filter div
//  getFilterDivId: function(columnId) {
//    return this.grid.id + '-filter-' + columnId;
//  },
//  
//  // returns filter field of a column
//  getFilterField: function(column) {
//    return column.filter && column.filter.field;
//  },
//  
///**
//* Returns DOM Element that is the root element of form field.
//*
//* For most fields, this will be the "el" property, but TriggerField
//* and it's descendants will wrap "el" inside another div called
//* "wrap".
//* @private
//*/
//  getFilterFieldDom: function(field) {
//    return field.wrap ? field.wrap.dom : field.el.dom;
//  },
//  
//  // Iterates over each column in column config array
//  eachColumn: function(func) {
//    Ext.each(this.grid.getColumnModel().config, func, this);
//  }
//  
//});
