/**
 * Fdl.InterfaceAction.ViewDocument
 */

Fdl.InterfaceAction.ViewDocument = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.ViewDocument.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.ViewDocument.prototype.toString= function() {
	return 'Fdl.InterfaceAction.ViewDocument';
};

Fdl.InterfaceAction.ViewDocument.prototype.preCondition = function () {
		
	var selection = this.getSelection();
	var document = this.getDocument();

	if (document) {
		return document.control('view');
	}
	return false;
};

Fdl.InterfaceAction.ViewDocument.prototype.process = function () {
		
	var selection=this.getSelection();
	var document=this.getDocument();
	var wDocument=this.getWidgetDocument();
	var collection= this.getCollection();
	var wCollection=this.getWidgetCollection();
	
	if (document) {
		if (wDocument) wDocument.displayDocument(document.id,'view');
		if (wCollection) wCollection.displayDocument(document.id,'view');
	}
};

/**
 * EO Fdl.InterfaceAction.ViewDocument
 */

/**
 * Fdl.InterfaceAction.EditDocument
 */

Fdl.InterfaceAction.EditDocument = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.EditDocument.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.EditDocument.prototype.toString= function() {
	return 'Fdl.InterfaceAction.EditDocument';
};
Fdl.InterfaceAction.EditDocument.prototype.canEditDocument = function (d) {
	if (d) {
		if (d.control('edit')) return true;
		return false;
	}
	return null;
};
Fdl.InterfaceAction.EditDocument.prototype.preCondition = function () {
		
	var selection = this.getSelection();
	var document = this.getDocument();
	var parameters = this.getParameters();
	var collection = this.getCollection();

	if (selection) {
		var lid=selection.getDocumentIdList();
        for (var i=0;i<lid.length;i++) {
        	var ds=this.context.getDocument({
        		id:lid[i],
        		usecache:true        		
        	});
        	if (! this.canEditDocument(ds)) return false;
        }
        return true;
	} else if (document) {
		return this.canEditDocument(document);
	} else if (collection) {
		return this.canEditDocument(collection);
	}
};


Fdl.InterfaceAction.EditDocument.prototype.process = function () {
		
	var selection=this.getSelection();
	var document=this.getDocument();
	var wDocument=this.getWidgetDocument();
	var collection= this.getCollection();
	var wCollection=this.getWidgetCollection();
	var parameters=this.getParameters();
	
	if(parameters && parameters.id){
		if (wDocument) wDocument.displayDocument(parameters.id,'edit');
		if (wCollection) wCollection.displayDocument(parameters.id,'edit');
	} else if (document) {
		if (wDocument) wDocument.displayDocument(document.id,'edit');
		if (wCollection) wCollection.displayDocument(document.id,'edit');
	} else if (collection) {
		if (wDocument) wDocument.displayDocument(collection.id,'edit');
		if (wCollection) wCollection.displayDocument(collection.id,'edit');
	}
	
};

/**
 * EO Fdl.InterfaceAction.ViewDocument
 */



/**
 * Fdl.InterfaceAction.Lock
 */

Fdl.InterfaceAction.Lock = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.Lock.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.Lock.prototype.toString= function() {
	return 'Fdl.InterfaceAction.Lock';
};
Fdl.InterfaceAction.Lock.prototype.canLock = function (d) {
	if (d) {
		if (!d.control('edit')) return false;
		var locked=d.getProperty('locked');
		if (locked == 0) return true;
		var user=this.context.getUser();
		if (user.id==Math.abs(locked)) return true;
		console.log('DOCUMENT',d.id,'cannot be locked');
		return false;
	}
	return null;
};
Fdl.InterfaceAction.Lock.prototype.preCondition = function () {
		
	var selection = this.getSelection();
	var document = this.getDocument();
	
	console.log('Precondition',selection,document);

	if (selection) {
		var lid=selection.getDocumentIdList();
        for (var i=0;i<lid.length;i++) {
        	var ds=this.context.getDocument({
        		id:lid[i],
        		usecache:true        		
        	});
        	if (! this.canLock(ds)) return false;
        }
        return true;
	} else if (document) {
		return this.canLock(document);
	}
};


Fdl.InterfaceAction.Lock.prototype.process = function () {
		
	var selection=this.getSelection();
	var document=this.getDocument();
	var wDocument=this.getWidgetDocument();
	var collection=this.getCollection();
	var wCollection=this.getWidgetCollection();

	if (selection) {
		
		var g=selection.context.createGroupRequest();
		g.addRequest({s:g.getSelection(selection)});
		g.addRequest({locks:g.foreach('s').callMethod('lock')}); 
		var result = g.submit();
		 
		var iter = result.get('locks');
		 
		for (var ic=0;ic<iter.length;ic++) {
			if (iter[ic].error) {
				this.warningMessage(iter[ic].document.getTitle()+':'+iter[ic].error);
			} else {
				//this.informationMessage(iter[ic].document.getTitle()+':'+'lock succeed');
			}
		}
		 		 
		if(wCollection){
			var modifiedDocObj = {};
			if(selection.collectionId){				 
				modifiedDocObj[selection.collectionId] = true ;
			}
			wCollection.reload(true,modifiedDocObj);
		}
		return true;
		 
	} else if (document) {
		var isLocked = document.lock();
		if (! isLocked) this.warningMessage(this.context.getLastErrorMessage());
		else if (wDocument) wDocument.reload();
		else if (wCollection) wCollection.reload(true,(collection?collection.id:null));
	}
};

/**
 * EO Fdl.InterfaceAction.Lock
 */

/**
 * Fdl.InterfaceAction.Unlock
 */

Fdl.InterfaceAction.Unlock = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.Unlock.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.Unlock.prototype.toString= function() {
	return 'Fdl.InterfaceAction.Unlock';
};
Fdl.InterfaceAction.Unlock.prototype.canUnlock = function (d) {
	if (d) {
		if (!d.control('edit')) return false;
		var locked=d.getProperty('locked');
		if (locked != 0) return true;
		var user=this.context.getUser();
		if (user.id==Math.abs(locked)) return true;
		return false;
	}
	return null;
};
Fdl.InterfaceAction.Unlock.prototype.preCondition = function () {
		
	var selection = this.getSelection();
	var document = this.getDocument();

	if (selection) {
		var lid=selection.getDocumentIdList();
        for (var i=0;i<lid.length;i++) {
        	var ds=this.context.getDocument({
        		id:lid[i],
        		usecache:true        		
        	});
        	if (! this.canUnlock(ds)) return false;
        }
        return true;
	} else if (document) {
		return this.canUnlock(document);
	}
};


Fdl.InterfaceAction.Unlock.prototype.process = function () {
		
	var selection=this.getSelection();
	var document=this.getDocument();
	var wDocument=this.getWidgetDocument();
	var collection=this.getCollection();
	var wCollection=this.getWidgetCollection();

	if (selection) {
		 var g=selection.context.createGroupRequest();
		 g.addRequest({s:g.getSelection(selection)});
		 g.addRequest({unlocks:g.foreach('s').callMethod('unlock')}); 
		 var result = g.submit();
		 
		 var iter = result.get('unlocks');
		  
		 for (var ic=0;ic<iter.length;ic++) {
			 if (iter[ic].error) {
				 this.warningMessage(iter[ic].document.getTitle()+':'+iter[ic].error);
			 } else {
				 //this.informationMessage(iter[ic].document.getTitle()+' : '+ 'unlock succeed');
			 }
		 }
		 		 
		 if(wCollection){
			 wCollection.reload(true,selection.collectionId);
		 }
		 return true;
		 
	} else if (document) {
		var isUnlocked = document.unlock();
		if (! isUnlocked) this.warningMessage(this.context.getLastErrorMessage());
		else if (wDocument) wDocument.reload();
		else if (wCollection) wCollection.reload(true,collection.id);
	}
};

/**
 * EO Fdl.InterfaceAction.Unlock
 */

/**
 * Fdl.InterfaceAction.Delete
 */

Fdl.InterfaceAction.Delete = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.Delete.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.Delete.prototype.toString= function() {
	return 'Fdl.InterfaceAction.Delete';
};
Fdl.InterfaceAction.Delete.prototype.canDelete = function (d) {
	if (d) {
		if (d.control('delete')) return true;
		return false;
	}
	return null;
};
Fdl.InterfaceAction.Delete.prototype.preCondition = function () {
		
	var selection = this.getSelection();
	var document = this.getDocument();

	if (selection) {
		var lid=selection.getDocumentIdList();
        for (var i=0;i<lid.length;i++) {
        	var ds=this.context.getDocument({
        		id:lid[i],
        		usecache:true        		
        	});
        	if (! this.canDelete(ds)) return false;
        }
        return true;
	} else if (document) {
		return this.canDelete(document);
	}
};


Fdl.InterfaceAction.Delete.prototype.process = function () {
		
	var selection=this.getSelection();
	var document = this.getDocument();
	var wDocument=this.getWidgetDocument();
	var collection = this.getCollection();
	var wCollection=this.getWidgetCollection();

	if (selection) {
		 var g=selection.context.createGroupRequest();
		 g.addRequest({s:g.getSelection(selection)});
		 g.addRequest({deleted:g.foreach('s').callMethod('delete')}); 
		 var result = g.submit();
		 
		 var iter = result.get('deleted');
		  
		 for (var ic=0;ic<iter.length;ic++) {
			 if (iter[ic].error) {
				 this.warningMessage(iter[ic].document.getTitle()+':'+iter[ic].error);
			 } else {
				 this.informationMessage(iter[ic].document.getTitle()+':'+'delete succeed');
			 }
		 }
		 		 
		 if(wCollection){
			 var modifiedDocObj = {};
			 if(selection.collectionId){				 
				 modifiedDocObj[selection.collectionId] = true ;
			 }
			 wCollection.reload(true,modifiedDocObj);
		 }
		 return true;
		 
	} else if (document) {
		var isDeleted= document.remove();
		if (! isDeleted) this.warningMessage(this.context.getLastErrorMessage());
		else if (wCollection) wCollection.reload(true,collection.id);
	}
};

/**
 * EO Fdl.InterfaceAction.Delete
 */

/**
 * Fdl.InterfaceAction.Duplicate
 * TODO Does not work yet : duplicate is probably not automaticaly moved to parent folder
 */

Fdl.InterfaceAction.Duplicate = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.Duplicate.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.Duplicate.prototype.toString= function() {
	return 'Fdl.InterfaceAction.Duplicate';
};
Fdl.InterfaceAction.Duplicate.prototype.canDuplicate = function (d) {
	if (d) {
		if (d.control('edit')) return true;
		return false;
	}
	return null;
};
Fdl.InterfaceAction.Duplicate.prototype.preCondition = function () {
		
	var selection = this.getSelection();
	var document = this.getDocument();

	if (selection) {
		var lid=selection.getDocumentIdList();
        for (var i=0;i<lid.length;i++) {
        	var ds=this.context.getDocument({
        		id:lid[i],
        		usecache:true        		
        	});
        	if (! this.canDuplicate(ds)) return false;
        }
        return true;
	} else if (document) {
		return this.canDuplicate(document);
	}
};


Fdl.InterfaceAction.Duplicate.prototype.process = function () {

	var selection=this.getSelection();
	var document=this.getDocument();
	var wDocument=this.getWidgetDocument();
	var collection=this.getCollection();
	var wCollection=this.getWidgetCollection();
	
	if (selection) {
		// Duplicate is not implemented yet for selection.
//		 var g=selection.context.createGroupRequest();
//		 g.addRequest({s:g.getSelection(selection)});
//		 g.addRequest({cloned:g.foreach('s').callMethod('cloneDocument', {linkFolder:true})});
//		 //g.addRequest({inserted:g.foreach('cloned').callMethod('moveto', {folderId: selection.collectionId})});
//		 var result = g.submit();
//		 
//		 var iter = result.get('cloned');
//		  
//		 for (var ic=0;ic<iter.length;ic++) {
//			 if (iter[ic].error) {
//				 if(wCollection) wCollection.warningMessage(iter[ic].document.getTitle()+':'+iter[ic].error);
//			 } else {
//				 if(wCollection) wCollection.informationMessage(iter[ic].document.getTitle()+':'+'duplicate succeed');
//			 }
//		 }
//		 		 
//		 if(wCollection){
//			 var modifiedDocObj = {};
//			 if(selection.collectionId){				 
//				 modifiedDocObj[selection.collectionId] = true ;
//			 }
//			 wCollection.reload(true,modifiedDocObj);
//		 }
//		 return true;
		 
	} else if (document) {
		Ext.Msg.prompt('Information', 'Titre du document répliqué : ', function(btn, text){
		    if (btn == 'ok'){
		    	var cloneDoc= document.cloneDocument({
					linkFolder: true,
					title: text
				});
				if(collection && cloneDoc) {
					cloneDoc.moveTo({
						folderId:collection.id
					});
				}
				console.log('clone:',cloneDoc,this.context.getLastErrorMessage());
				if (! cloneDoc) this.warningMessage(this.context.getLastErrorMessage());
				else if (wDocument) wDocument.reload();
				else if (wCollection) wCollection.reload(true,collection.id);
		    }
		},this,false,'Copie de ' + document.getTitle());
		
	}
};
/**
 * EO Fdl.InterfaceAction.Duplicate
 */


/**
 * Fdl.InterfaceAction.RemoveFromFolder
 */
Fdl.InterfaceAction.RemoveFromFolder = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.RemoveFromFolder.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.RemoveFromFolder.prototype.toString= function() {
	return 'Fdl.InterfaceAction.RemoveFromFolder';
};
Fdl.InterfaceAction.RemoveFromFolder.prototype.canRemoveFromFolder = function (d) {
	if (d) {
		if (d.control('edit')) return true;
		return false;
	}
	return null;
};
Fdl.InterfaceAction.RemoveFromFolder.prototype.preCondition = function () {
		
	var selection = this.getSelection();
	var document = this.getDocument();

	if (selection) {
		var lid=selection.getDocumentIdList();
        for (var i=0;i<lid.length;i++) {
        	var ds=this.context.getDocument({
        		id:lid[i],
        		usecache:true        		
        	});
        	if (! this.canRemoveFromFolder(ds)) return false;
        }
        return true;
	} else if (document) {
		return this.canRemoveFromFolder(document);
	}
};

Fdl.InterfaceAction.RemoveFromFolder.prototype.process = function () {
		
	var selection=this.getSelection();
	var document=this.getDocument();
	var wDocument=this.getWidgetDocument();
	var collection= this.getCollection();
	var wCollection=this.getWidgetCollection();

	if (selection) {
		
		if(collection){
			var resultObject = collection.unlinkDocuments({
				selection:selection
			});
			if (!resultObject) console.log(this.context.getLastErrorMessage());
			else if (wCollection) wCollection.reload(true,selection.collectionId);
		}
		 		 
		 
	} else if (document) {
		
		if(collection){
			var isUnlinked = collection.unlinkDocument({
				id: document.id
			});
			if (! isUnlinked) console.log(this.context.getLastErrorMessage());
			else if (wDocument) wDocument.reload();
			else if (wCollection) wCollection.reload(true,collection.id);
		}
		
	}
};
/**
 * EO Fdl.InterfaceAction.RemoveFromFolder
 */


/**
 * Fdl.InterfaceAction.Historic
 */
Fdl.InterfaceAction.Historic = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.Historic.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.Historic.prototype.toString= function() {
	return 'Fdl.InterfaceAction.Historic';
};
Fdl.InterfaceAction.Historic.prototype.canHistoric = function (d) {
	if (d) {
		if (d.control('view')) return true;
		return false;
	}
	return null;
};
Fdl.InterfaceAction.Historic.prototype.preCondition = function () {

	var document = this.getDocument();
	if (document) {
		return this.canHistoric(document);
	}
};

Fdl.InterfaceAction.Historic.prototype.process = function () {
		
	var selection=this.getSelection();
	var document=this.getDocument();
	var wDocument=this.getWidgetDocument();
	var collection= this.getCollection();
	var wCollection=this.getWidgetCollection();

	if (document) {		
		var histowin = Ext.fdl.viewDocumentHistory(document);
        histowin.show();		
	}
};
/**
 * EO Fdl.InterfaceAction.Historic
 */





/**
 * Fdl.InterfaceAction.SimpleNote
 */

Fdl.InterfaceAction.SimpleNote = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.SimpleNote.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.SimpleNote.prototype.toString= function() {
	return 'Fdl.InterfaceAction.Lock';
};


Fdl.InterfaceAction.SimpleNote.prototype.process = function () {
		
	var selection=this.getSelection();
	var document=this.getDocument();
	var wDocument=this.getWidgetDocument();
	var collection=this.getCollection();
	var wCollection=this.getWidgetCollection();

	if (selection) {
		return false;
		 
	} else if (document) {
            if (wDocument) wDocument.addNote();
            
            return true;
	}
};

/**
 * Fdl.InterfaceAction.Reload
 */

Fdl.InterfaceAction.Reload = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.Reload.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.Reload.prototype.toString= function() {
	return 'Fdl.InterfaceAction.Reload';
};


Fdl.InterfaceAction.Reload.prototype.process = function () {
		
	var selection=this.getSelection();
	var document=this.getDocument();
	var wDocument=this.getWidgetDocument();
	var collection=this.getCollection();
	var wCollection=this.getWidgetCollection();

	if (selection) {
		 return false;
	} else if (document) {
		if (wDocument) {
		    wDocument.mode = 'view';
		    wDocument.reload();
		}
		return true;
	}
};

/**
 * EO Fdl.InterfaceAction.Reload
 */


/**
 * Fdl.InterfaceAction.CreateDocument
 */

Fdl.InterfaceAction.CreateDocument = function (config) {
	Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.CreateDocument.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.CreateDocument.prototype.toString= function() {
	return 'Fdl.InterfaceAction.ViewDocument';
};

Fdl.InterfaceAction.CreateDocument.prototype.preCondition = function () {
		
	var context = this.getContext();
	var parameters = this.getParameters();
	
	var family = context.getDocument({
		id: parameters.family
	});

	if (family) {
		return family.control('icreate');
	}
	return false;
};

Fdl.InterfaceAction.CreateDocument.prototype.process = function () {
	
	var context = this.getContext();
	var parameters = this.getParameters();
	var wDocument=this.getWidgetDocument();
	var wCollection=this.getWidgetCollection();
	
	if (document) {
		if (wDocument) wDocument.displayDocument(parameters.family,'create');
		if (wCollection) wCollection.displayDocument(parameters.family,'create');
	}
};

/**
 * EO Fdl.InterfaceAction.CreateDocument
 */

/**
 * Fdl.InterfaceAction.EditSearchFilter
 */

Fdl.InterfaceAction.EditSearchFilter = function (config) {
    Fdl.InterfaceAction.call(this,config);
};
Fdl.InterfaceAction.EditSearchFilter.prototype = new Fdl.InterfaceAction();
Fdl.InterfaceAction.EditSearchFilter.prototype.toString= function() {
    return 'Fdl.InterfaceAction.EditSearchFilter';
};
Fdl.InterfaceAction.EditSearchFilter.prototype.canEditDocument = function (d) {
    if (d) {
        if (d.control('edit')) return true;
        return false;
    }
    return null;
};
Fdl.InterfaceAction.EditSearchFilter.prototype.preCondition = function () {
        
    var selection = this.getSelection();
    var document = this.getDocument();
    var parameters = this.getParameters();
    var collection = this.getCollection();

    if (selection) {
        var lid=selection.getDocumentIdList();
        for (var i=0;i<lid.length;i++) {
            var ds=this.context.getDocument({
                id:lid[i],
                usecache:true               
            });
            if (! this.canEditDocument(ds)) return false;
        }
        return true;
    } else if (document) {
        return this.canEditDocument(document);
    } else if (collection) {
        return this.canEditDocument(collection);
    }
};


Fdl.InterfaceAction.EditSearchFilter.prototype.process = function () {
        
    var selection=this.getSelection();
    var document=this.getDocument();
    var wDocument=this.getWidgetDocument();
    var collection= this.getCollection();
    var wCollection=this.getWidgetCollection();
    var parameters=this.getParameters();
    
    if(parameters && parameters.id){
        if (wDocument) wDocument.displaySearchFilter(parameters.id);
        if (wCollection) wCollection.displaySearchFilter(parameters.id);
    } else if (document) {
        if (wDocument) wDocument.displaySearchFilter(document.id);
        if (wCollection) wCollection.displaySearchFilter(document.id);
    } else if (collection) {
        if (wDocument) wDocument.displaySearchFilter(collection.id);
        if (wCollection) wCollection.displaySearchFilter(collection.id);
    }
    
};

/**
 * EO Fdl.InterfaceAction.EditSearchFilter
 */


