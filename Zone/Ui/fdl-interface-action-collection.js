Fdl.InterfaceAction.ExportSelection = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.ExportSelection.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.ExportSelection.prototype.toString= function() {
	return 'Fdl.InterfaceAction.ExportSelection';
};

Fdl.InterfaceAction.ExportSelection.prototype.preCondition = function () {
	var selection=this.getSelection();

	if (selection) {
        return true;
	} 
    return false;
};


Fdl.InterfaceAction.ExportSelection.prototype.process = function () {
	var selection=this.getSelection();

	if (selection) {
		this.context.sendForm({
		        app: 'FDL',
		        action: 'EXPORTFLD',
		        wfile:'Y',
		        code:'utf8',
		        selection:selection
		    },'_hidden');
		return true;
	} 
	return false;
};

Fdl.InterfaceAction.CreateSearch = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.CreateSearch.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.CreateSearch.prototype.toString= function() {
	return 'Fdl.InterfaceAction.CreateSearch';
};

Fdl.InterfaceAction.CreateSearch.prototype.preCondition = function () {
	return true;
};


Fdl.InterfaceAction.CreateSearch.prototype.process = function () {
	
	console.log('SEARCH',this.search);
	
	var widgetCollection = this.getWidgetCollection();
	
	if(this.search){
		
		if(widgetCollection){
			widgetCollection.publish('opendocument',this.widgetCollection,'REPORT','create',{
				search: this.search
			});		
		}
	}
	
	return true;
};



Fdl.InterfaceAction.GridDisplay = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.GridDisplay.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.GridDisplay.prototype.toString= function() {
	return 'Fdl.InterfaceAction.GridDisplay';
};

Fdl.InterfaceAction.GridDisplay.prototype.preCondition = function () {
	return true;
};

Fdl.InterfaceAction.GridDisplay.prototype.process = function () {
	this.widgetCollectionContainer.setWidgetCollection('Ext.fdl.GridCollection');
	return true;
};


Fdl.InterfaceAction.IconDisplay = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.IconDisplay.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.IconDisplay.prototype.toString= function() {
	return 'Fdl.InterfaceAction.IconDisplay';
};

Fdl.InterfaceAction.IconDisplay.prototype.preCondition = function () {
	return true;
};

Fdl.InterfaceAction.IconDisplay.prototype.process = function () {
	this.widgetCollectionContainer.setWidgetCollection('Ext.fdl.IconCollection');
	return true;
};


Fdl.InterfaceAction.TreeDisplay = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.TreeDisplay.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.TreeDisplay.prototype.toString= function() {
	return 'Fdl.InterfaceAction.TreeDisplay';
};

Fdl.InterfaceAction.TreeDisplay.prototype.preCondition = function () {
	return true;
};

Fdl.InterfaceAction.TreeDisplay.prototype.process = function () {
	this.widgetCollectionContainer.setWidgetCollection('Ext.fdl.TreeCollection');
	return true;
};


Fdl.InterfaceAction.AbstractDisplay = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.AbstractDisplay.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.AbstractDisplay.prototype.toString= function() {
	return 'Fdl.InterfaceAction.AbstractDisplay';
};

Fdl.InterfaceAction.AbstractDisplay.prototype.preCondition = function () {
	return true;
};

Fdl.InterfaceAction.AbstractDisplay.prototype.process = function () {
	this.widgetCollectionContainer.setWidgetCollection('Ext.fdl.AbstractCollection');
	return true;
};