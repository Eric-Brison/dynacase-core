/**
 * @class Fdl.InterfaceAction
 * @namespace Fdl.InterfaceAction
 * @cfg {Fdl.DocumentSelection} (optional) selection document object
 * @cfg {Ext.Fdl.Collection} (optional) Collection widget
 * @cfg {Ext.Fdl.Document} (optional) Document widget
 * 
 */ 
Fdl.InterfaceAction = function(config) {
	if (config) {
		for ( var i in config) this[i] = config[i];
		if (this.widgetDocument) {
			if (this.widgetDocument.document) {
				this.document=this.widgetDocument.document;
				if (! this.context) this.context=this.widgetDocument.document.context;
			}
		}
		if (this.widgetCollection) {
			if (this.widgetCollection.document) {
				this.document=this.widgetCollection.collection;
				if (! this.context) this.context=this.widgetCollection.collection.context;
			}
		}
		this.parameters = config.parameters;
	}
	
};
Fdl.InterfaceAction.prototype = {
	/** selection object where apply action @type Fdl.DocumentSelection */
	selection:null,
	/** selection object where apply action @type Ext.Fdl.Document */
	widgetDocument:null,
	/** selection object where apply action @type Ext.Fdl.Collection */
	widgetCollection:null,
	/** collection document @type Fdl.Collection */
	collection:null,
	/**  document @type Fdl.Document */
	document:null,	
	/** context @type Fdl.Context */
	context: null,
	/** parameters @type Object */
	parameters: null,
	
	preCondition : function() {
		return true;
	},
	process : function() {
		return true;
	},
	getSelection : function() {
		if (this.selection) return this.selection;
		return null;
	},
	getDocument : function() {
		if(this.document) return this.document;
		return null;
	},
	getCollection : function() {
		if(this.collection) return this.collection;
		return null;
	},
	getWidgetDocument : function() {
		if (this.widgetDocument) return this.widgetDocument;
		return null;
	},
	getWidgetCollection : function() {
		if (this.widgetCollection) return this.widgetCollection;
		return null;
	},
	getContext : function() {
		if(this.context) return this.context;
		return null;
	},
	getParameters : function() {
		if(this.parameters) return this.parameters;
	},
	toString : function() {
		return 'Fdl.InterfaceAction';
	},
	informationMessage: function(text){
		Ext.Msg.show({
		   title:'Information',
		   msg: text,
		   buttons: Ext.Msg.OK,
		   icon: Ext.MessageBox.INFO
		});
    },    
    warningMessage: function(text){
    	Ext.Msg.show({
    	   title:'Warning',
    	   msg: text,
    	   buttons: Ext.Msg.OK,
    	   icon: Ext.MessageBox.WARNING
    	});    	
    },    
    errorMessage: function(text){
    	Ext.Msg.show({
     	   title:'Error',
     	   msg: text,
     	   buttons: Ext.Msg.OK,
     	   icon: Ext.MessageBox.ERROR
     	});  	
    }	

};