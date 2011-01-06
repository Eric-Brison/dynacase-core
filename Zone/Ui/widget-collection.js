
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Ext.fdl.Collection
 * @namespace Ext.fdl.Collection
 * @brick brick-icon
 * @author Clement Laballe
 * <p>This class represents the generic collection interface and is supposed to be extended in specific collection interfaces.</p>
 */

Ext.fdl.Collection = {

    initCollectionWidget: function(){
                
        var context = null;
    
        if (this.collection) {
            if (!this.collection.isCollection()) {
                console.log('Error : Ext.fdl.Collection is provided an object which is not a Fdl.Collection.');
            }
            context = this.collection.context;       
        }
        
        if (this.search) {
            if (!this.search instanceof Fdl.SearchDocument) {
                console.log('Error : Ext.fdl.Collection is provided an object which is not a Fdl.SearchDocument.');
            }
            context = this.search.context;
        }
        
        if (!this.collection && !this.search) {
            console.log('Error : Ext.fdl.Collection is not provided a collection or a search.');
        }    
        
        this.context = context;    
        
        if (!this.selection) {
            this.selection = new Fdl.DocumentSelection({
                context: context,
                collectionId: this.collection ? this.collection.getProperty('id') : null,
                mainSelector: 'none'
            });
        }
        
        if(!this.contentConfig){
            this.contentConfig = {};
        }
        
        this.firstload = true;
        
        this.addEvents({
            dblclick: true,
            multidblclick: true,
            select: true,
            unselect: true,
            hover: true
        });
        
    },
    
    collectionLoad: true,
    
    /**
     * @cfg {Boolean} selectable True to allow document selection
     */
    selectable: false,
    
    /**
     * @cfg {Boolean} singleSelect If selectable is true, false to allow multiple document selection.
     */
    singleSelect: false,
    
    /**
     * @cfg {Ext.Toolbar|Boolean} tBar Top toolbar component.
     */
    tBar: null,
    
    /**
     * @cfg {Ext.Toolbar|Boolean} bBar Bottom toolbar component.
     */
    bBar: null,
    
    /**
     * @cfg {Boolean} enableContextMenu If true, context menu will be displayed on right click.
     */
    enableContextMenu: true,
    
    /**
     * @cfg {Fdl.Collection} collection Collection object covered by this interface. Either this or 'search' config is mandatory.
     */
    collection: null,
    
    /**
     * @cfg {Fdl.SearchDocument} search document SearchDocument object covered by this interface. Either this or 'collection' config is mandatory.
     */
    search: null,
    
    /**
     * Targeted Context
     * @link {Fdl.Context context}
     * @type Fdl.Context
     */
    context: null,
    
    /**
     * Current DocumentSelection
     * @link {Fdl.DocumentSelection selection}
     * @type Fdl.DocumentSelection
     */
    selection: null,
    
    /**
     * Current DocumentFilter
     * @link {Fdl.DocumentFilter filter}
     * @type Fdl.DocumentFilter
     */ 
    filter: null,
    
    /**
     * @cfg {Object|String} target Target for document link opening.
     * Default value is '_blank' and means to display document in a new Ext.fdl.Window
     */
    target: '_blank',
    
    /**
     * @cfg {Boolean} enableDrop True to enable document drop. Default to false.
     */
    enableDrop: false,
    
    /**
     * @cfg {Boolean} singleDrop If enableDrop is true, false to allow multiple document drop.
     */
    singleDrop: false,
    /**
     * latest result of getContent
     * @link {Fdl.DocumentList document list}
     * @type Fdl.DocumentList 
     */
    documentList: null,
    
    contentConfig: null,
    
    /**
     * Get data from underlying FdlData object.
     * This method should be called internally in Ext.fdl.Collection implementations to get the document lists and counts to use.
     * @method getData
     * @param {Object} config 
     */
    getData: function(config){
        
        Ext.applyIf(config,this.contentConfig);
    
        if (this.collection) {
        
            if (this.firstload) {
                this.documentList = this.collection.getStoredContent();
                if (!this.documentList) {
                    this.documentList = this.collection.getContent(config);
                }
                this.firstload = false;
            }
            else {
                this.documentList = this.collection.getContent(config);
                
            }
            if (!this.documentList) {
                Ext.Msg.alert('Error', this.context.getLastErrorMessage());
            }
            else {
                this.content = this.documentList.getDocuments();
            }
        }
        else {
            if (this.search) {
                // we clone this.search.filter because we do not want to modify it (additional filter are volatile in grid collection for example)
                var sConfig = {};
                sConfig = Ext.apply(sConfig, this.search.filter);
                
                if (config.filter) {
                    if (sConfig.criteria) {
                        sConfig.criteria = sConfig.criteria.concat(config.filter.criteria);
                    }
                    else {
                        sConfig.criteria = config.filter.criteria;
                    }
                }
                config.filter = sConfig;
                console.log('Search',this.search,this.search.key,this.search.withHighlight);
                if(this.search.key){
                    config.key = this.search.key; // FIXME This should not be necessary, Fdl.SearchDocument must be corrected
                    config.withHighlight = this.search.withHighlight;
                }
                if(this.search.family){
                    config.family = this.search.family; // FIXME This should not be necessary, Fdl.SearchDocument must be corrected
                }
                this.documentList = this.search.search(config);
                if (this.documentList) {
                    this.content = this.documentList.getDocuments();
                    if(this.documentList.date){
                        this.reloadDate = this.documentList.date;
                    }
                    if(this.documentList.document){
                        this.selection.collectionId = this.documentList.document.properties.id;
                        this.searchId = this.documentList.document.properties.id;
                        this.fireEvent('search',this.documentList.document.properties.id);
                    }
                }               
            }
        }
        
    },
    
    /**
     * Get count of current document list
     * @method getCount
     * @return {Number} The count of current document list.
     */
    getCount: function(){
    
        if (this.documentList) {
            this.selection.totalCount = this.documentList.count();
            return this.documentList.count();
        }
        
    },
    
    /**
     * Display interface in a given element. Implementation is provided by each heritor classes.
     * @method display
     * @param {String|Ext.Element} target Element to display to.
     * @return {Boolean} True if method succeeded.
     */
    display: function(target){
    
    },
    
    /**
     * Change collection covered by this interface.
     * @method setCollection
     * @param {Fdl.Collection} collection The new collection.
     * @return {Boolean} True if method succeeded.
     */
    setCollection: function(collection){
        this.collection = collection;
    },
    
    /**
     * Set object configuration with config parameters similar to those used at instanciation.
     * @method setConfig
     * @param {Object} config The configuration object.
     * @return {Boolean} True if method succeeded.
     */
    setConfig: function(config){
    
    },
    
    getContainerPanel: function(){
        return this.containerPanel;
    },
    
    /**
     * Get an array of current selected document ids.
     * @method getSelectedDocumentIds
     * @return {Array} Selected document ids.
     */
    getSelectedDocumentIds: function(){
    
    },
    
    /**
     * Select all documents.
     * @method selectAll
     * @return {Array} Selected document ids.
     */
    selectAll: function(){
        this.selection.mainSelector = 'all';
        this.selection.clearSelection();
        this.applySelection();
    },
    
    /**
     * Unselect all documents.
     * @method unselectAll
     * @return {Boolean} True if method succeeded.
     */
    unselectAll: function(){
        this.selection.mainSelector = 'none';
        this.selection.clearSelection();
        this.applySelection();
    },
    
    /**
     * Reverse document selection.
     * @method reverseSelection
     */
    reverseSelection: function(){
        this.selection.invertSelection();
        this.applySelection();
    },
    
    /**
     * Select one or more document by id.
     * @method selectDocument
     * @param {Number|Array} docId The id of the document(s) to select.
     * @return {Boolean} True if method succeeded.
     */
    selectDocument: function(docId){
    
    },
    
    /**
     * Unselect one or more document by id.
     * @method unselectDocument
     * @param {Number|Array} docId The id of the document(s) to unselect.
     * @return {Boolean} True if method succeeded.
     */
    unselectDocument: function(docId){
    
    },
    
    /** 
     * This function is called once to notify this collection that a document or an array of documents has been dropped on it.
     * This function has a default implementation to add the dropped document(s) to the collection if possible, or to the targeted collection folder if applicable, and return true, but another implementation that does something to process the drop and that returns true if successful can be provided.
     * If this function returns false then the defined drag repair action runs.
     * @method notifyDocumentDrop
     * @param {Fdl.Collection} dropCol Fdl.Collection in which the drag selection is dropped.
     * @param {Fdl.DocumentSelection} dragSel Fdl.DocumentSelection describing the document(s) that was (were) dragged over this collection.
     * @param {Fdl.Document} dropDoc Fdl.Document that the dragged document(s) was dropped on, or null.
     * @return {Boolean} True if the drop was valid, else false.
     */
    notifyDocumentDrop: function(dragWid, dragCol, dropCol, dragSel, dropDoc){
        
        //console.log('Before Document Drop Allowed and Processed', dragWid, dragCol, dropCol, dragSel, dropDoc );
        
        if(!this.notifyDocumentDragOver(dragWid, dragCol, dropCol, dragSel, dropDoc)){
            return true;
        }
        
        //console.log('Document Drop Allowed and Processed', dragWid, dragCol, dropCol, dragSel, dropDoc );
                
        var targetCol;
        if (dropDoc && dropDoc.isCollection() && dropDoc.isFolder()) {
            targetCol = dropDoc;
        }
        else {
            targetCol = dropCol;
        }
        
        var context = targetCol.context;
        
        var g = context.createGroupRequest();
        
        g.addRequest({
            drop: g.getDocument({
                id: targetCol.id
            })
        });
        
        if (dragSel.collectionId) {
            g.addRequest({
                drag: g.getDocument({
                    id: dragSel.collectionId
                })
            });
        }
        
        var dragColModified = false ;
        
        var behaviour = this.dragBehaviour(dragCol, dropCol, dragSel, dropDoc);
        
        switch (behaviour){
            case 'duplicate':
                
                if(dragSel.mainSelector == 'all' || dragSel.selectionItems.length != 1){
                    Ext.Msg.alert('Warning','Duplicate for multiple selection must be implemented.');
                    return true;
                } else {
                    g.addRequest({
                        doc: g.getDocument({
                            id: dragSel.selectionItems[0]
                        })
                    });
                    g.addRequest({
                        clone: g.get('doc').callMethod('cloneDocument', {
                            linkFolder: false
                        })
                    });
                    g.addRequest({
                        insert: g.get('clone').callMethod('moveto', {
                            folderId: targetCol.id
                        })
                    });
                    if(targetCol.id == dragCol.id){
                        dragColModified = true;
                    }
                }
            
            
            break;
            case 'link':
                
                g.addRequest({
                    a: g.get('drop').callMethod('insertDocuments', {
                        selection: dragSel
                    })
                });
            
            
            break;
            case 'move':
                
                g.addRequest({
                    a: g.get('drag').callMethod('moveDocuments', {
                        selection: dragSel,
                        targetIdentificator: targetCol.id
                    })
                });
                dragColModified = true;
            
            break;
        }
            
        
        g.addRequest({
            c: g.get('drop').callMethod('getContent')
        });
        if (dragColModified) {
            g.addRequest({
                d: g.get('drag').callMethod('getContent')
            });
        }
        
        var r = g.submit();
        
        console.log('R',r);
        
        var modifiedDocObj = {};
        if (dragCol) {
            modifiedDocObj[dragCol.id] = dragCol;
        }
        
        if (targetCol) {
            modifiedDocObj[targetCol.id] = targetCol;
        }
        
        if (dragWid.reload && dragColModified) {
            if (r.get('drag').getProperty('id') == dragWid.collection.getProperty('id')) {
                dragWid.documentList = r.get('d');
                dragWid.content = dragWid.documentList.getDocuments();
                dragWid.collection = r.get('drag');
                //console.log('Problem : collection should have a count here', r.get('drag'), r.get('drag').count());
                dragWid.reload(false, modifiedDocObj);
            } else {
                dragWid.reload(true, modifiedDocObj);
            }
        }
        
        //console.log('DROP WIDGET',this,this.collection, this.collection.isSearch());
        
        // Test if this drop widget is not the same than drag widget
        if (dragWid != this && !this.collection.isSearch()){
            // If this drop widget is representing the collection in which drop was done
            if (r.get('drop').getProperty('id') == this.collection.getProperty('id')) {
                this.documentList = r.get('c');
                this.content=this.documentList.getDocuments();
                this.collection = r.get('drop');
                this.reload(false,modifiedDocObj);
            } else {
                this.reload(true,modifiedDocObj);
            }
            
        }
        
        return true;
        
    },
    
    /**
     * This method is called to determine if a drop would be valid.
     * All test to determine of a drop would be valid should call this method.
     * It is automatically called when a document is dragged over the drop zone to check if it is allowed to drop.
     * It is automatically called when a drop occurs to check if it is allowed to drop.
     * @method notifyDocumentDragOver
     * @param {Ext.fdl.Collection} dragWid Ext.fdl.Collection from which the document selection is dragged.
     * @param {Fdl.Collection} fromCol Fdl.Collection from which the drag selection is dragged, or null.
     * @param {Fdl.Collection} overCol Fdl.Collection over which the drag selection is dragged.
     * @param {Fdl.DocumentSelection} dragSel Fdl.DocumentSelection describing the document(s) that is (are) dragged over this collection.
     * @param {Fdl.Document} overDoc Fdl.Document that the document selection is dragged over, or null.
     */
    notifyDocumentDragOver: function(dragWid, fromCol, overCol, dragSel, overDoc){
        
        //console.log('dragWid',dragWid,'fromCol',fromCol,'overCol',overCol,'dragSel',dragSel,'overDoc',overDoc);
        if ((overCol && overCol.isCollection() && overCol.isFolder() && ((overCol != fromCol) || (Ext.fdl.KeyBoard.keys[17] && (!Ext.fdl.KeyBoard.keys[16])) )) // overCol is a folder and (is different from source folder or action is cloning)
            || (overDoc && overDoc.isCollection() && overDoc.isFolder())) { // OR overDoc is a folder
            return true;
        }
        
        return false;
        
    },
    
    /**
     * This function is called to notify this collection that a document or an array of documents has been dragged from it.
     * This function has no default implementation.
     * @method notifyDocumentDrag
     * @param {Number|Array} dragId The id of the document(s) that is (are) dragged from this collection.
     * @return {Boolean} True if the drag was valid, else false.
     */
    notifyDocumentDrag: function(dragId){
    
    },
    
    /**
     * This function reloads the collection interface to display a change in the content.
     * @param {Object} server
     * @param {Object} col For multiple collection interfaces like the tree collection, this parameters indicates a specific subcollection to reload.
     */
    reload: function(server, col){
    
    },
    
    /**
     * This function is automatically called when a document in the collection is dblclicked. By default, it publishes an opendocument event, but it can be overriden by another implementation.
     * @param {Object} id
     * @param {Object} mode
     * @param {Object} source
     */
    displayDocument: function(id, mode, source){
        this.publish('opendocument', this, id, mode);
    },
    
    defaultDragBehaviour: 'move',
    
    /**
     * 
     * @param {} fromCol
     * @param {} overCol
     * @param {} dSel
     * @param {} overDoc
     * @return {String} string code for the expected behaviour
     */
    dragBehaviour: function(fromCol, overCol, dSel, overDoc){
        
        if (this.notifyDocumentDragOver(null,fromCol, overCol, dSel, overDoc)) {
            
            if(!fromCol || (fromCol.isCollection &&fromCol.isSearch())){
                // Behaviour when we are dragging from nothing or from a search
                if (Ext.fdl.KeyBoard.keys[17] && !Ext.fdl.KeyBoard.keys[16]) {
                    return 'duplicate';
                } else {
                    return 'link';
                }
            } else {
                // Behaviour when we are dragging from a folder
                if (Ext.fdl.KeyBoard.keys[16] && Ext.fdl.KeyBoard.keys[17]){
                    return 'link';
                } else if (Ext.fdl.KeyBoard.keys[17]) {
                    return 'duplicate';
                } else if (Ext.fdl.KeyBoard.keys[16]) {
                    return 'move';
                } else {
                    return this.defaultDragBehaviour ;
                }
            }
            
        }
        
        return false ;
        
    },
    
    /**
     * This function is called to construct the drag proxy (html content used when dragging document(s)). It is called each time keys are pressed.
     * @param {Fdl.DocumentSelection} dSel Describe the documents which are currently dragged.
     * @param {Fdl.Document} overDoc Document over which selection is currently dragged.
     * @return {String} Html code to display.
     */
    dragTemplate: function(fromCol, overCol, dSel, overDoc){
        
        //console.log('DRAGTEMPLATE',fromCol,overCol,dSel, overDoc, Ext.fdl.KeyBoard.keys);
    
        var l = dSel.count();
        var fDoc = dSel.context.getDocument({
            id: dSel.selectionItems[0],
            useCache: true
        });
        
        var object = '';
        
        var target = '';
        
        if (overDoc && overDoc.isCollection() && overDoc.isFolder()) {
            target = this.context._("eui::infolder") +' <b>' + overDoc.getTitle() + '</b>';
        }
        
        if (l > 1) {
            object = '<div>' + l + ' documents' + '</div>';
        }
        else {
            object = '<img src=' +
            fDoc.getIcon({
                width: 16
            }) +
            ' style="width:16px;margin-right:2px;float:left;" />' +
            '<div style="margin-left:18px;">' +
            fDoc.getTitle() +
            '</div>';
        }
        
        var behaviour = this.dragBehaviour(fromCol, overCol, dSel, overDoc);
        
        var action = '';
        
        switch (behaviour){
            case 'duplicate':
                action = this.context._("eui::Duplicate");
            break;
            case 'link':
                action = this.context._("eui::Link");
            break;
            case 'move':
                action = this.context._("eui::Move");
            break;
        }
                
        return this.dragTemplateFormat(action,object,target);
        
    },
    
    dragTemplateFormat: function(action,object,target){
        
        return '<div style="min-height:15px;">' +
        '<i><div style="margin-left:18px;">' +
        action +
        '</div></i>' +
        '<b>' +
        object +
        '</b><div style="margin-left:18px;">' +
        target + '</div>' +
        '</div>';
        
    },
    
    defaultDropCollection: function(){
        return this.collection ;
    },
    
    // This function is used to display the proxy of the dragged data as defined by the drop widget which is hovered (hWid)
    displayProxy: function(hWid, forceDisplay){
                        
        var me = this ;
                    
        if (me.isDragging()) {
        
            me.hoveredWid = hWid;
            
            var proxy = me.getProxy();
            
            var tplWid = hWid || me;
                        
            var overCol = hWid && hWid.defaultDropCollection
                    ? hWid.defaultDropCollection()
                    : null;
                        
            // Display cache is used because the non official correction for z-index in drag & drop causes multiple calls of notifyEnter
            // So we need to vall dragTemplate only if parameters have changed else drag becomes laggy
                        
            if (proxy) {
                if (forceDisplay || !me.displayCache || me.displayCache.dragCol != me.collection || me.displayCache.overCol != overCol || me.displayCache.selection != me.selection || me.displayCache.overDoc != me.overDoc) {
                                    
                    if (!me.displayCache) {
                        me.displayCache = {};
                    }
                    me.displayCache.dragCol = me.collection;
                    me.displayCache.overCol = overCol;
                    me.displayCache.selection = me.selection;
                    me.displayCache.overDoc = me.overDoc;
                    
                    proxy.update(tplWid.dragTemplate(me.collection, overCol, me.selection, me.overDoc));
                                        
                    var ret = me.notifyDocumentDragOver(
                            me, me.collection,
                            overCol, me.selection,
                            me.overDoc);

                            if (ret) {
                                proxy.setStatus(me.getDropZone().dropAllowed);
                            } else {
                                proxy.setStatus(me.getDropZone().dropNotAllowed);
                            }
                    
                    proxy.sync();
                }
            }
            
        }
    },

//    /**
//     * @property collection
//     * Collection object which is displayed by this interface.
//     * @type Fdl.Collection
//     */
//    /**
//     * @event dblclick Fires when a document is double clicked.
//     * @param {Ext.fdl.Collection} this
//     * @param {Number} docid The id of the document that was double clicked.
//     */
//    /**
//     * @event multidblclick Fires when a multiple document selection is double clicked.
//     * @param {Ext.fdl.Collection} this
//     * @param {Array} docids Array of Number. The ids of the selected document that were double clicked.
//     */
//    /**
//     * @event select Fires when a document is selected.
//     * @param {Ext.fdl.Collection} this
//     * @param {Number} docid The id of the document that was selected.
//     */
//    /**
//     * @event unselect Fires when a document is unselected.
//     * @param {Ext.fdl.Collection} this
//     * @param {Number} docid The id of the document that was unselected.
//     */
//    /**
//     * @event hover fires when a document is hovered by mouse.
//     * @param {Ext.fdl.Collection} this
//     * @param {Number} docid The id of the document that was hovered.
//     */
    
    /**
     * Automatically called when current selection change. No default implementation.
     * @method onSelectionChange
     * @param {Fdl.DocumentSelection} selection Current selection.
     */
    onSelectionChange: function(selection){
        console.log('Test Selection Change');
    },
    
    /**
     * Automatically called when a document is selected. No default implementation.
     * @method onDocumentSelect
     * @param {Fdl.DocumentSelection} selection Current selection.
     * @param {Integer} id Selected document reference.
     */
    onDocumentSelect: function(selection,id){
        console.log('Test Document Select');
    },
    
    /**
     * Automatically called when a document is deselected. No default implementation.
     * @method onDocumentDeselect
     * @param {Fdl.DocumentSelection} selection Current selection.
     * @param {Integer} id Deselected document reference.
     */
    onDocumentDeselect: function(selection,id){
        console.log('Test Document Deselect');
    },
    
    getProperties: function(){
        return this.context.getDisplayableProperties();        
    },
    
    getAttributes: function(){
    
        var attributes = [];
        
        if ((this.collection && this.collection.isSearch()) || (this.search)) {
            
            var doc = this.collection || this.search ;          
            var filters=null;
            if (this.collection) {          
                 filters = doc.getFilters();                
            }
            
            if (this.search) {              
                filters = [] ;
                filters.push( this.search.filter );             
            }
                        
            var family = false;
            var monofam = true;
            
            if (filters) {
                for (var f = 0; f < filters.length; f++) {
                
                    if (filters[f].family != 'null') { // FIXME It's strange that we have to test for null as a string here. Check why.
                                            
                        if (!family) {
                            // If family is found and is the first, we memorize it                          
                            family = filters[f].family;                         
                        }
                        else {
                            // If a family has already been found we compare if it is the same family
                            if (!(family == filters[f].family)) {
                                monofam = false;
                            }
                        }
                        
                    }
                    else {
                        monofam = false;
                    }
                    
                }
            } else {
                if(this.search){
                    family = this.search.getValue('se_famid');
                    monofam = true;
                } else if(this.collection){
                    family = this.collection.getValue('se_famid');
                    monofam = true;
                } else {
                    monofam = false;
                }
            }
                        
            //if (filters && filters.length == 1 && filters[0].family) {
            if (monofam == true && family){   
                // Mono-family search
                this.family = doc.context.getDocument({
                    id: family,
                    useCache: true,
                    onlyValues: false
                });
                
                var a = this.family.getAttributes();
                
                for (var i in a) {
                    if (i != 'undefined') { // Why do some families have an undefined attribute ??
                        if (a[i].getVisibility() != 'I' && a[i].getVisibility() != 'H' && a[i].isLeaf()) {
                            attributes.push(a[i].id);
                        }
                    }
                    
                }
            }
            
        }
        
        return attributes;
        
    },
    
    displayDocumentContextMenu: function(docId,e){      
        var dataMenu = this.retrieveDocumentContextMenu(docId);
        if(dataMenu){
            var extMenu = this.getMenu(dataMenu.docctx.items,docId,e);
            var xy = e.getXY();
            extMenu.showAt(xy);
        }
    },
    
    displaySelectionContextMenu: function(e){       
        var dataMenu = this.retrieveSelectionContextMenu();
        if(dataMenu){
            var extMenu = this.getMenu(dataMenu.docctx.items,null,e);
            var xy = e.getXY();
            extMenu.showAt(xy);
        }
    },
    
    displayContextMenu: function(docId,e){
        
        if(this.enableContextMenu){
            
            if(this.selection.mainSelector == "none" && this.selection.selectionItems.length == 1){
                // If selection is only about one document, display document context menu
                this.displayDocumentContextMenu(docId,e);           
            } else {
                // If selection is about multiple documents, display selection context menu
                this.displaySelectionContextMenu(e);
            }
            
        }
        
    },
    
    //selectionContextMenu: "EXTUI:default-selection-context-menu.xml",
    
    retrieveSelectionContextMenu: function(){
        
        if(this.selectionContextMenu){
        
            if(this.collection){
                var id = this.collection.id;    
            }
            
            if(this.searchId){
                var id = this.searchId; 
            }
            
            var data = this.context.retrieveData({
                app:'EXTUI',
                action: 'EUI_XMLMENU',
                collectionId: id,
                menuxml: this.selectionContextMenu
            });
                    
            if (data) {
                if (!data.error) {
                    return data.menu;
                } else {    
                    this.context.setErrorMessage(data.error);
                }
            } else {      
                this.context.setErrorMessage('eui_contextmenu : no data');
            }
        
        } else {
            return false ;
        }
        
    },
    
    //documentContextMenu: "EXTUI:default-context-menu.xml",
    
    retrieveDocumentContextMenu: function(docId){
        
        if(this.documentContextMenu){
        
            if(this.collection){
                var colId = this.collection.id; 
            }
            
            if(this.searchId){
                var colId = this.searchId;  
            }
            
            var data = this.context.retrieveData({
                app:'EXTUI',
                action: 'EUI_XMLMENU',
                id: docId,
                collectionId: colId,
                menuxml: this.documentContextMenu
            });
            
            if (data) {
                if (!data.error) {
                    console.log('DOCUMENT MENU',data.menu);
                    return data.menu;
                } else {    
                    this.context.setErrorMessage(data.error);
                }
            } else {      
                this.context.setErrorMessage('eui_contextmenu : no data');
            }
                
        } else {
            return false ;
        }
        
    },
    
    displayUrl: function(url,target,config){        
        this.publish('openurl',url,target,config);      
    },
    
    getMenu: function(dataMenu,docId,e){
        
        var me = this ;
                        
        //console.log('GENERATE MENU',dataMenu);
        
        //panel.getTopToolbar().removeAll();
        //menu.removeAll();
        
        var extMenu = new Ext.menu.Menu({});
        
        for(var name in dataMenu){
            
            var menuObject = dataMenu[name];
            
            var menuItem = Ext.fdl.MenuManager.getMenuItem(menuObject,{
                context: me.context,
                collection: me.collection,
                widgetCollection: me,
                selection: me.selection,
                documentId: docId
            });
            
            //console.log('MENUITEM',menuItem);
            
            if(menuItem){
                extMenu.add(menuItem);
            }
            
        }
        
        return extMenu ;
            
    }
    
};

/**
 * @class Ext.fdl.OperatorComboBox
 * @namespace Ext.fdl.InputField
 */
Ext.fdl.Operator = Ext.extend(Ext.form.ComboBox, {

    criteria: null,
    
    triggerConfig: {
        tag: "img",
        src: Ext.BLANK_IMAGE_URL,
        cls: "x-form-trigger " + this.triggerClass,
        style: "border-left:1px solid #B5B8C8;left:2px;"
    },
    
    valueField: 'value',
    displayField: 'display',
    
    editable: false,
    forceSelection: true,
    disableKeyFilter: true,
    triggerAction: 'all',
    mode: 'local',
    
    initComponent: function(){
    
        Ext.fdl.Operator.superclass.initComponent.call(this);
        
        if (this.criteria) {
            this.setOperatorList(this.criteria);
        }
        else {
            this.disable();
        }
        
        this.on({
            select: {
                fn: function(combobox, record, index){
                    this.onOperatorSelect(combobox, record, index);
                }
            }
        });
        
    },
    
    // Override of initList() to adjust dropdow list size to its largest element.
    initList: function(){
        Ext.fdl.Operator.superclass.initList.apply(this, arguments);
        this.list.setWidth('auto');
        this.innerList.setWidth('auto');
    },
    
    setOperatorList: function(criteria){
    
        var opData = [];
        
        opData.push(['aucun', '', []]);
        
        for (var i = 0; i < criteria.length; i++) {
            opData.push([criteria[i].label, criteria[i].operator, criteria[i]]);
        }
        
        if (!this.store) {
            this.store = new Ext.data.ArrayStore({
                data: opData,
                fields: ['display', 'value', 'criteria']
            });
        }
        else {
            this.getStore().loadData(opData);
        }
        
        this.enable();
        
    },
    
    onOperatorSelect: function(combobox, record, index){
    
    }
    
});

Ext.reg("fdloperator", Ext.fdl.Operator);


/**
 * @class Ext.fdl.StateComboBox
 * @namespace Ext.fdl.InputField
 */
Ext.fdl.EnumComboBox = Ext.extend(Ext.form.ComboBox, {

    family: null,
    
    valueField: 'key',
    displayField: 'label',
    
    //editable: false,
    forceSelection: true,
    disableKeyFilter: true,
    triggerAction: 'all',
    mode: 'local',
    
    initComponent: function(){
        
        if (!this.family) {
            console.log('Warning : Ext.fdl.StateComboBox is not provided a family.');
        }
        
        var stateArray = [];
        
        if (this.family instanceof Fdl.Family) {
            if (!this.family.hasWorkflow()) {
                console.log('Warning : Ext.fdl.StateComboBox is provided a family without workflow.');
            }
            else {
            
                var wid = this.family.getProperty('wid');
                
                var workflow = this.family.context.getDocument({
                    id: wid,
                    needWorkflow: true
                });
                
                var states = workflow.getStates();
                
                // convert states into an array
                for (var i in states) {
                    stateArray.push(states[i]);
                }
                
            }
        }
        
        this.store = new Ext.data.JsonStore({
            data: stateArray,
            fields: [{
                name: 'key'
            }, {
                name: 'activity',
                // If there is no activity, label will be the state
                convert: function(v, record){
                    // Perhaps the server should send these values already decoded ?
                    if (!record.activity) {
                        return record.label;
                    }
                    else {
                        return record.activity;
                    }
                }
            }, {
                name: 'label'
            }]
        });
        
        Ext.fdl.StateComboBox.superclass.initComponent.call(this);
        
    }
    
});

/**
 * @class Ext.fdl.FilterFieldPanel
 * @namespace Ext.fdl.InputField
 */
Ext.fdl.FilterFieldPanel = Ext.extend(Ext.ux.form.FieldPanel, {

    criteria: null,
    selectedCriteria: null,
    property: null,
    propertyInfo: null,
    
    validateOnBlur: false,
    
    setRawValue: function(v){
        //Ext.fdl.FilterFieldPanel.superclass.setRawValue.call(this, v);
    },
    
    // check if all operand are properly filled
    // default to false
    checkOperand: false,
    
    border: false,
    baseCls: null,
    
    layout: 'hbox',
    layoutConfig: {
        align: 'top',
        pack: 'start'
    },
    
    width: 'auto',
    
    initComponent: function(){
    
        if (this.criteria == null) {
            console.log('Error : Ext.fdl.FilterFieldPanel has no criteria provided.');
        }
        
        Ext.fdl.FilterFieldPanel.superclass.initComponent.call(this);
        
        this.addEvents('filter');
        
        this.on({
            render: function(panel){
                if(panel.criteria && panel.criteria[0]){
                    panel.display(panel.criteria[0]);
                    panel.render(); // This is strange (should not be required to render here)
                }
            },
            change: function(panel, value, startValue){
                panel.checkFilter();
            }
        });
        
    },
    
    checkFilter: function(){
        var filter = this.getFilter();
        if (filter != false) {
            this.fireEvent('filter', this, filter);
        }
    },
    
    getFdlType: function(){
        if (this.propertyInfo){
            return this.propertyInfo.type;
        }
        
        if (this.attributeInfo){
            return this.attributeInfo.type;
        }
        
        return false;
    },
    
    display: function(criteria){
    
        this.selectedCriteria = criteria;
        
        var me = this;
        
        this.add({
            xtype: 'fdloperator',
            style: 'padding:1px 0px;visibility:hidden;',
            width: 18,
            criteria: this.criteria,
            onOperatorSelect: function(combobox, record, index){
                me.removeAll();
                me.display(record.get('criteria'));
                me.doLayout();
                me.checkFilter();
            },
            value: criteria.operator
        });
        
        // First operand does not produce an input field.
        if (criteria.operand) {
            for (var i = 1; i < criteria.operand.length; i++) {
                if (i != 1) {
                    this.add({
                        html: '',
                        baseCls: null,
                        border: false,
                        width: 2
                    });
                }
                
                var config = {
                    xtype: 'textfield',
                    flex: 1,
                    style: 'padding:1px 0px;',
                    
                    format: 'd/m/Y',
                    
                    listeners: {
                        change: me.onChange,
                        select: function(field, date){
                            // If this editor respond to select, then it is a combobox or a picker and so we have to disable the change event which is not relevant.
                            me.onChange();
                            me.un('change', me.onChange);
                        },
                        specialkey: function(field, ev){
                            if (ev.getKey() == ev.ENTER) {
                                ev.preventDefault();
                                me.onChange();
                            }
                        }
                    }
                };
                                
                if (me.getFdlType() == 'date' || me.getFdlType() == 'timestamp') {
                    
                    config.xtype = 'datefield';
                    config.preventMark = true;
                    config.getSubmitValue = function(){
                        var v = this.getRawValue();
                        if (v !== '') {
                            return v;
//                            var date = new Date(v);
//                            return date.format("d/m/Y");
                        }
                    };
                }
                
                if(me.getFdlType() == 'enum') {
                    
                    var field = new Ext.fdl.Enum({
                        attribute: me.attributeInfo,
                        flex: 1,
                        listeners: {
                            change: me.onChange,
                            select: function(field, date){
                                // If this editor respond to select, then it is a combobox or a picker and so we have to disable the change event which is not relevant.
                                me.onChange();
                                me.un('change', me.onChange);
                            },
                            specialkey: function(field, ev){
                                if (ev.getKey() == ev.ENTER) {
                                    ev.preventDefault();
                                    me.onChange();
                                }
                            }
                        }
                    });
                    
                    this.add(field);
                    continue;
                    
                }
                
                this.add(config);
                
            }
        }
        
        this.setToolTip();
        
    },
    
    setToolTip: function(){
    
        if (this.tooltip != null) {
            this.tooltip.destroy();
            this.tooltip = null;
        }
        
        var label = this.selectedCriteria.tplLabel;
        
        var sValue = this.getRawValue().split(this.token);
        
        if (this.selectedCriteria.operand) {
            for (var i = 0; i < this.selectedCriteria.operand.length; i++) {
                if (i == 0) {
                    label = label.replace('{' + this.selectedCriteria.operand[i] + '}', '<b>' + (this.propertyInfo ? this.propertyInfo.label : this.attributeInfo.label) + '</b>');
                }
                if (sValue[i] != 'undefined') {
                    label = label.replace('{' + this.selectedCriteria.operand[i] + '}', '<b>' + sValue[i] + '</b>');
                }
                else {
                    label = label.replace('{' + this.selectedCriteria.operand[i] + '}', '<b>' + '?' + '</b>');
                }
            }
        }
        else {
            label = 'aucun filtre';
        }
        
        this.tooltip = new Ext.ToolTip({
            target: this.body,
            html: label,
            hideDelay: 0,
            showDelay: 0
        });
        
    },
    
    getFilter: function(){
    
        var sValue = this.getRawValue().split(this.token);
        
        // No filter
        if (sValue[0] == "" && sValue.length == 1) {
            return {
                left: this.property || this.attribute
            };
        }
        
        this.setToolTip();
        
        // Check if all operands have been filled.
        if(this.checkOperand){
            for (var i = 0; i < sValue.length; i++) {
                if (sValue[i] == "" || sValue[i] == 'undefined') {
                    return false;
                }
            }
        }
        
        var criteria = {};
        
        criteria.operator = this.selectedCriteria.operator;
        
        criteria[this.selectedCriteria.operand[0]] = this.property || this.attribute;
        
        for (var i = 1; i < this.selectedCriteria.operand.length; i++) {
            if(sValue[i] != 'undefined'){
                criteria[this.selectedCriteria.operand[i]] = sValue[i];
            }
        }
        return criteria;
        
    }
    
});

/**
 * @class Ext.fdl.CollectionContainer
 * @namespace Ext.fdl.Collection
 * @extends Ext.Panel
 */
Ext.fdl.CollectionContainer = Ext.extend(Ext.Panel,{
    
    layout: 'fit',
    
    tBar: true,
    
    collectionPanel: null,
    
    initComponent: function(){
                
        if(this.initialConfig.collectionPanel){
            this.collectionPanel = this.initialConfig.collectionPanel;
                        
            this.collectionPanel.border = false;
            
            if(this.collectionPanel.collection) {
                this.collection = this.collectionPanel.collection;
                this.context = this.collection.context;
            }
            
            if(this.collectionPanel.search) {
                this.search = this.collectionPanel.search;
                this.context = this.search.context;
            }
        }
        
        this.tbar = this.getTBar();
        
        Ext.fdl.CollectionContainer.superclass.initComponent.call(this);
        
        this.on('afterrender',function(panel){
            panel.add(panel.collectionPanel);
            panel.doLayout();
        });
        
        this.collectionPanel.on('search',function(searchId){
            var dataMenu = this.retrieveCollectionMenu(searchId);
            if(dataMenu){
                this.getToolbar(dataMenu,this.getTopToolbar());
                this.getTopToolbar().doLayout();
                this.doLayout();
            }
        },this);
        
        if(this.displayDocument){
            this.collectionPanel.displayDocument = this.displayDocument ;
        }

    },
        
    getTBar: function(){
            
        var toolbar = null;
        
        if (this.tBar == true) {
            var dataMenu = this.retrieveCollectionMenu();
            if(dataMenu){
                toolbar = this.getToolbar(dataMenu);
            }
        }
        
        return toolbar;
        
    },
    
    getToolbar: function(dataMenu,extToolbar){
                
        var me = this ;
        
        if(!extToolbar){
            var extToolbar = new Ext.Toolbar({});
        } else {
            extToolbar.removeAll();
        }
        
        for(var name in dataMenu){
            
            var menuObject = dataMenu[name];                            
            
            var menuItem = Ext.fdl.MenuManager.getMenuItem(menuObject,{
                context: me.context,
                collection: me.collection,
                search: me.search,
                widgetCollection: me.collectionPanel,
                widgetCollectionContainer: me
            });         
            if(menuItem){
                extToolbar.add(menuItem);
            }
            
        }
        
        return extToolbar ;
            
    },
    
    setWidgetCollection: function(collectionPanelClass){
        
        this.removeAll();
                
        var newcollectionPanel = new (eval("("+collectionPanelClass+")"))({
            collection: this.collection,
            search: this.search,
            border: false
        });
        
        if(this.displayDocument){
            newcollectionPanel.displayDocument = this.displayDocument ;
        }
        
        this.add(newcollectionPanel);
        this.doLayout();
        
        this.collectionPanel = newcollectionPanel;
        this.collection = this.collectionPanel.collection;
        this.search = this.collectionPanel.search;
        this.collection ? this.context = this.collection.context : null;
        this.search ? this.context = this.search.context : null;
        
        this.onWidgetCollectionChange(collectionPanelClass);
        
    },
    
    onWidgetCollectionChange: function(collectionPanelClass){
        // Empty function, automatically called when collection widget is called
    },
    
    /**
     * @cfg {Boolean|String} collectionMenu Specify xml menu description. Example : "EXTUI:default-collection-menu.xml"
     */
    collectionMenu: false,
    
    collectionMenuConfig: null,
    
    retrieveCollectionMenu: function(id){
        
        if(this.collectionMenu || this.collectionMenuConfig){
                
            if(!id){
                if(this.collection){
                    var id = this.collection.id;    
                }
            }
            
            if(this.collectionMenuConfig){
                
                if(id){
                    this.collectionMenuConfig.fldid = id ;
                }
                
//              if(!this.collectionMenuConfig.fldid){
//                  return false;
//              }
                
                var data = this.context.retrieveData(this.collectionMenuConfig);
                
            } else {
                
                var data = this.context.retrieveData({
                    app:'EXTUI',
                    action: 'EUI_XMLMENU',
                    menuxml: this.collectionMenu,
                    id: id
                });
                
            }
            
            //console.log('DATA MENU',data);
            
            if (data) {
                if (!data.error) {
                    console.log('COLLECTION MENU',data.menu);
                    return data.menu;
                } else {    
                    this.context.setErrorMessage(data.error);
                }
            } else {      
                this.context.setErrorMessage('eui_contextmenu : no data');
            }
        
        } else {
            return false ;
        }
        
    }
    
});
