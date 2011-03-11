
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */


// Utility function to add an event listener
function addEvent(o,e,f){
	if (o.addEventListener){ o.addEventListener(e,f,true); return true; }
	else if (o.attachEvent){ return o.attachEvent("on"+e,f); }
	else { return false; }
}

// Utility function to send an event
// o the object
// en the event name : mouseup, mouseodwn
function sendEvent(o,en) {  
  if (o) {
    if( document.createEvent ) {    
      //      var ne=document.createEvent("HTMLEvents");
      var ne;
      if ((en.indexOf('mouse') > -1)||(en.indexOf('click') > -1)) ne=document.createEvent("MouseEvents");
      else ne=document.createEvent("HTMLEvents");
      ne.initEvent(en,true,true);
      o.dispatchEvent(ne);
    }
    else {	
      try {
	o.fireEvent( "on"+en );
      }
      catch (ex) {
	;
      }

    } 
  }
}


Ext.fdl.Document = Ext.extend(Ext.Panel, {

	document: null,
	
	context: null,
	
	mode: 'view',
	
    forceExt: false,
    
    forceClassic: false,
    
    displayToolbar: true,
    
    displaynotes: true,
    notes: [],
	
	layout: 'fit',
	
	toString: function(){
        return 'Ext.fdl.Document';
    },
    
    initComponent: function(){
    	
    	if(!this.document){
    		console.log('Ext.fdl.Document is not provided a document.');
    	}
    	
    	this.context = this.document.context;
    	
    	if(this.displayToolbar){
	    	this.tbar = new Ext.Toolbar({
	        	enableOverflow: true,
	        	hidden: true,
	        	height: 28
	        });
    	}
    	
    	Ext.fdl.Document.superclass.initComponent.call(this);
    	
    	this.on({            
            afterrender: {
                fn: function(){
					                
                    this.switchMode(this.mode);
                    
                    var o = this;
                    
                    (function(){
                        o.viewNotes();
                    }).defer(1000);
					
					fdldoc = this.document;
					
					(function(){
                        fdldoc.addUserTag({
                            tag: 'VIEWED'
                        });
                    }).defer(1000);                    
                    
                },
				scope: this
            }
        });
    	
    },

    setDocument: function(document){    	
    	this.document = document ;    	
    },
    
    reload: function(){
    	this.switchMode(this.mode);
    },
    
    switchMode: function(mode){
    
    	var me = this ;
    	
        if (this.showMask) {
            this.showMask();
        }
        
        this.removeAll();
        
        switch (mode) {
            case 'view':
                if ((this.document.getProperty('generateVersion') == 3 && !this.forceClassic)|| this.forceExt) {
                
                    var dcv = this.document.getDefaultConsultationView();
                    
                    var subPanel = new Ext.fdl.SubDocument({
                    	document: this.document,
                    	config: this.config
                    });
                    
                    subPanel.on('close',function(){
                    	this.fireEvent('close',this);
                    },this);
                    
                    subPanel.switchMode = function(mode){
                    	me.switchMode(mode);
                    };
                    
                    if (dcv) {
                    
                        if (!eval('window.' + dcv.extwidget)) {
                            // Dynamically add Javascript file
                            this.includeJS(dcv.extsrc);
                        }
                        
                        // Override document with extended behaviours
                        Ext.apply(subPanel, eval('window.' + dcv.extwidget));
                        
                    }
                    else {
                        Ext.apply(subPanel, Ext.fdl.DocumentDefaultView);
                    }
                    
                    this.add(subPanel);
                                        
                    subPanel.on('afterrender',function(){
                    	subPanel.display();
                    });
                                                            
                }
                else {
                    this.renderViewClassic();
                }
                break;
            case 'edit':
            case 'create':
                
                //var dev = this.document.getDefaultEditionView();
            
                if ((this.document.getProperty('generateVersion') == 3 && !this.forceClassic) || this.forceExt) {
                
                    var dev = this.document.getDefaultEditionView();
                    
                    var subPanel = new Ext.fdl.SubDocument({
                    	document: this.document,
                    	mode: mode,
                    	config: this.config
                    });
                    
                    subPanel.on('close',function(){
                    	this.fireEvent('close',this);
                    },this);
                    
                    subPanel.switchMode = function(mode){
                    	me.switchMode(mode);
                    };
                    
                    if (dev) {
                    
                        if (!eval('window.' + dev.extwidget)) {
                            // Dynamically add Javascript file
                            this.includeJS(dev.extsrc);
                        }
                        
                        // Override document with extended behaviours
                        Ext.apply(subPanel, eval('window.' + dev.extwidget));
                        
                    }
                    else {
                        Ext.apply(subPanel, Ext.fdl.DocumentDefaultEdit);
                    }
                    
                    this.add(subPanel);
                                        
                    subPanel.on('afterrender',function(){
                    	subPanel.display();
                    });
                                       
                }
                else {
                    this.renderEditClassic();
                }
                break;
                
            default:
                break;
        };
        
        this.mode = mode;
        
        // Do layout will not work on render. It must not be called first time.
        if (this.firstLayout) {
            this.doLayout();
        }
        
        this.firstLayout = true;
        
        if (this.hideMask) {
            this.hideMask();
        }
        
    },
        
    generateMenu: function(panel,menu,mediaObject){
            	
    	var me = this ;
            			
		//console.log('GENERATE MENU',panel,menu,mediaObject.dom);
		
		//panel.getTopToolbar().removeAll();
		menu.removeAll();

		var documentMenu = mediaObject.dom.contentWindow.documentMenu;
		//console.log('DOCUMENT MENU',documentMenu);
		
		var documentId = (mediaObject.dom.contentWindow.document.getElementsByName('document-id').length > 0) ? mediaObject.dom.contentWindow.document.getElementsByName('document-id')[0].content : '' ;
		//console.log('DOCUMENT ID', documentId);
		
		var document = this.context.getDocument({
			id: documentId,
			useCache:true,
			latest: false
		});
				
		if(document && document.id){
			this.setDocument(document);
			//console.log('DOCUMENT',documentId,me.document.getTitle());
			//me.publish('modifydocument',me.document);
		}
		
		for(var name in documentMenu){
			
			var menuObject = documentMenu[name];			
			var menuItem = Ext.fdl.MenuManager.getMenuItem(menuObject,{
				widgetDocument:me,
				documentId:documentId,
				panel: panel,
				mediaObject: mediaObject,
				context: this.context,
				menu: menu
			});
			console.log('MENU ITEM',menuItem);
			menu.add(menuItem);
		}
		
		menu.add(new Ext.Toolbar.Fill());

        var toolbarStatus = me.documentToolbarStatus();
                
        for (var i = 0; i < toolbarStatus.length; i++) {
            if (toolbarStatus[i]) {
                menu.add(toolbarStatus[i]);
            }
        }
		
		menu.doLayout();
		menu.show();
		
		mediaObject.dom.contentWindow.displaySaveForce = function(){
		
		    if (mediaObject.dom.contentWindow.documentMenu.saveforce) {
		    	mediaObject.dom.contentWindow.documentMenu.saveforce.visibility = 1 ;
		    	me.generateMenu(panel,menu,mediaObject);
		    }
		};
	
	},	
	
	// If this function returns a string, confirm when closing this document displaying this string.
	// This method is used in Ext.fdl.Window to check if document can be closed with ou without confirm.
    closeConfirm: function(){
    	    	    	
		if(this.mediaObject){
			try {
				if(this.mediaObject.dom.contentWindow.beforeUnload){
					var beforeUnload = this.mediaObject.dom.contentWindow.beforeUnload();
					return beforeUnload ;
				}
			}  catch(exception) {

			}
		}
    	
    	return false;
    	
    },
	    
    renderViewClassic: function(){
    	
    	//console.log('RENDER VIEW CLASSIC DOCUMENT ID',this.document.id);
    	
    	var me = this ;
    	
    	if(!this.config){
    		this.config = {};
    	}
    	
    	if(!this.config.targetRelation){
    	 	//this.config.targetRelation = 'Ext.fdl.Document.prototype.publish("opendocument",null,%V%,"view")';
    	}
    	
    	console.log('CONFIG',this.config);
    	delete this.config.opener ;

        var sconf='';
        if (this.config && this.document.context) sconf=JSON.stringify(this.config);
        //console.log(this.config);
        //console.log(JSON.stringify(this.config));
        
    	if(this.config.url){
    		var url = this.config.url ;
    		url = url.replace(new RegExp("(action=FDL_CARD)","i"),"action=VIEWEXTDOC");
    		//url = this.document.context.url + url ;
    		console.log('Calculated URL',url);
    	} else {
			url = this.document.context.url + '?app=FDL&action=VIEWEXTDOC&id=' + this.document.getProperty('id') + '&extconfig='+encodeURI(sconf);
    	}
        
        var mediaPanel = new Ext.ux.MediaPanel({
        	style: 'height:100%;',
        	bodyStyle: 'height:100%;',
        	border: false,
        	autoScroll: false,
            mediaCfg: {
                mediaType: 'HTM',
                url: url
            },
            listeners  : { 
            	mediaload : function(panel,mediaObject){
            		
            		me.mediaObject = mediaObject;
            		
            		var menu = me.getTopToolbar();            		
            		console.log('MEDIA OBJECT',mediaObject);
            		me.generateMenu(panel,menu,mediaObject);
            		
            		addEvent(mediaObject.dom,'load',function(){
            			var menu = me.getTopToolbar();            		
            			console.log('MEDIA OBJECT',mediaObject);
            			me.generateMenu(panel,menu,mediaObject);
            		});
            		            		            		
            	}               
           }
        });
        
        this.add(mediaPanel);                
        this.doLayout();
        
    },    
    
    renderEditClassic: function(){
    	
    	var me = this ;
    	
    	if(!this.config){
    		this.config = {};
    	}
    	
    	if(this.config.url){
    		var url = this.config.url ;
    		url = url.replace(new RegExp("(app=GENERIC)","i"),"app=FDL");
    		url = url.replace(new RegExp("(action=GENERIC_EDIT)","i"),"action=EDITEXTDOC");
    		//url = this.document.context.url + url ;
    		console.log('Calculated URL',url);
    	} else {
			var url = this.document.context.url + '?app=FDL&action=EDITEXTDOC&classid=' + this.document.getProperty('fromid') + '&id=' + this.document.getProperty('id');
    	}
    	
        var mediaPanel = new Ext.ux.MediaPanel({
        	style: 'height:100%;',
        	bodyStyle: 'height:100%;',
        	border: false,
        	autoScroll: false,
            mediaCfg: {
                mediaType: 'HTM',
                url: url
            },
            listeners  : { 
            	mediaload : function(panel,mediaObject){
            		
            		me.mediaObject = mediaObject;
            		
            		var menu = me.getTopToolbar();            		
            		console.log('MEDIA OBJECT(1)',mediaObject);
            		me.generateMenu(panel,menu,mediaObject);
            		
            		addEvent(mediaObject.dom,'load',function(){
            			var menu = me.getTopToolbar();            		
            			console.log('MEDIA OBJECT(2)',mediaObject);
            			me.generateMenu(panel,menu,mediaObject);
            		});
            		            		
            	}               
           }
        });
        
        this.add(mediaPanel);                
        this.doLayout();
    },
    
    displayUrl: function(url,target,config){    	
    	this.publish('openurl',url,target,config);    	
    },
    
    addNote: function(){
    
        var note = this.document.context.createDocument({        
            familyId: 'SIMPLENOTE',
            mode: 'view'        
        });
        
        if (note) {
            note.setValue('note_pasteid', this.document.getProperty('initid'));
            note.setValue('note_width', 200);
            note.setValue('note_height', 200);
            note.setValue('note_top', 50);
            note.setValue('note_left', 50);
            note.save();
          
            this.document.reload();
            var nid=note.getProperty('initid');
            this.viewNotes();
            var wnid = this.notes[nid];
            if (wnid) {
                var p = wnid.items.itemAt(0);
                //console.log("note",p);
                setTimeout(function(){
                    p.items.itemAt(0).items.itemAt(0).setVisible(false);
                    p.items.itemAt(0).items.itemAt(1).setVisible(true);
                    p.items.itemAt(0).items.itemAt(2).setVisible(true);
                    p.items.itemAt(0).items.itemAt(3).setVisible(true);
                    p.items.itemAt(0).items.itemAt(1).focus();
                }, 1000);
            
        }
        }
        
    },
    
    viewNotes: function(config){
        var noteids = this.document.getProperty('postitid');
        
        if (noteids.length > 0) {
            for (var i = 0; i < noteids.length; i++) {
                if (noteids[i] > 0) {
                    var note;
                    if (!this.notes[noteids[i]]) {
                        note = this.document.context.getDocument({
                            id: noteids[i]
                        });
                        var wd = new Ext.fdl.Document({
                            style: 'padding:0px;margin:0px;',
                            bodyStyle: 'padding:0px;margin:0px;',
                            document: note,
                            anchor: '100% 100%',
                            displayToolbar: false,
                            listeners: {close: function (panel) {
                        	   panel.ownerCt.close();
                            }}
                        });
                        this.notes[noteids[i]] = wd;
                    }
                    else {
                        note = this.notes[noteids[i]].document;
                    }
                    if (note.isAlive()) {
                        var color = 'yellow';
                        var nocolor = note.getValue('note_color');
                        if (nocolor) 
                            color = nocolor;
                        var x = parseInt(note.getValue('note_left'));
                        var y = parseInt(note.getValue('note_top'));
                        
                        
                        
                        if ((!this.notes[noteids[i]].window) || (this.notes[noteids[i]].window.getWidth() == 0)) {
                            var notewin = new Ext.Window({
                                layout: 'fit',
                                cls: 'x-fdl-note',
                                style: 'padding:0px;margin:0px;background-color:' + color,
                                title: note.getTitle(),
                                closeAction: 'hide',
                                width: parseInt(note.getValue('note_width')),
                                height: parseInt(note.getValue('note_height')),
                                resizable: true,
                                note: note,
                                tools: [{
                                    id: 'close',
                                    qtip: 'Cacher la note',
                                    // hidden:true,
                                    handler: function(event, toolEl, panel){
                                        panel.setVisible(false);
                                    }
                                }],
                                plain: true,
                                renderTo: this.body,
                                constrain: true,
                                items: [this.notes[note.id]],
                                x: x,
                                y: y,
                                listeners: {
                                    move: function(o){
                                        if (this.note && (this.note.getProperty('owner') == this.note.context.getUser().id)) {
                                            var xy = o.getPosition(true);
                                            if ((o.getWidth() > 0) && (xy[0] > 0)) {
                                            
                                                this.note.setValue('note_width', o.getWidth());
                                                this.note.setValue('note_height', o.getHeight());
                                                this.note.setValue('note_left', xy[0]);
                                                this.note.setValue('note_top', xy[1]);
                                                this.note.save();
                                            }
                                        }
                                    },
                                    close: function(o){
                                    
                                    }
                                }
                            });
                            
                            this.notes[noteids[i]].window = notewin;
                            notewin.show();
                            
                        }
                        else {
                            if (config && config.undisplay) 
                                this.notes[noteids[i]].window.setVisible(false);
                            else 
                                this.notes[noteids[i]].window.setVisible(true);
                        }
                    }
                }
                
                
            }
            
        }
    },
    
    includeJS: function(url){
    
        console.log('Include JS', url);
        
        if (window.XMLHttpRequest) {
            var XHR = new XMLHttpRequest();
        }
        else {
            var XHR = new ActiveXObject("Microsoft.XMLHTTP");
        }
        if (XHR) {
            XHR.open("GET", (this.document.context.url + url), false);
            XHR.send(null);
            eval(XHR.responseText);
        }
        else {
            return false;
        }
        
    },
    
    detailSearch: function(){
    
        return new Ext.fdl.DSearch({
            document: this.document,
            hidden: true,
            border: false
        });
        
    },
    
    documentToolbarStatus: function(){
    	
    	// If document is in creation mode, no toolbar status to display
    	if(!this.document.id){
    		return false ;
    	}
    
        var u = this.document.context.getUser();
        var info = u.getInfo();
        
        
        var statestatus = null;
        var statestatustext = '';
        
        if(this.document.getProperty('version')){
        	console.log('VERSION',this.document.getProperty('version'));
        	statestatustext = 'version ' + this.document.getProperty('version')+' ';
        }
        
        if (this.document.hasWorkflow()){
            if(this.document.isFixed()) {
                statestatustext += '<i>' + '<span style="padding-left:10px;margin-right:3px;background-color:' + this.document.getColorState() + '">&nbsp;</span>' + this.document.getLocalisedState() + '</i>';
            } else {
                if (this.document.getActivityState()) {
                    statestatustext += '<i>' + this.document.getActivityState() + '</i>';
                } else {
                    statestatustext += '<i>' + this.document.getLocalisedState() + '</i>';
                }
            }
        }
        
        if(statestatustext){
        	statestatus = new Ext.Toolbar.TextItem(statestatustext);
        }

                
        var lockstatus = null;
        if (this.document.getProperty('locked') > 0) {
            if (this.document.getProperty('locked') == info['id']) {
                lockstatus = new Ext.Toolbar.TextItem('<img ext:qtip="Verrouillé par ' + this.document.getProperty('locker') + '" src="' + this.document.context.url + 'FDL/Images/greenlock.png" style="height:16px" />');
            }
            else {
                lockstatus = new Ext.Toolbar.TextItem('<img ext:qtip="Vérrouillé par ' + this.document.getProperty('locker') + '" src="' + this.document.context.url + 'FDL/Images/redlock.png" style="height:16px" />');
            }
        }
        
        var readstatus = null;
        if (this.document.getProperty('locked') == -1) {
            readstatus = new Ext.Toolbar.TextItem('<img ext:qtip="Figé" src="' + this.document.context.url + 'FDL/Images/readfixed.png" style="height:16px" />');
        }
        else 
            if (!this.document.canEdit()) {
                readstatus = new Ext.Toolbar.TextItem('<img ext:qtip="Lecture seule" src="' + this.document.context.url + 'FDL/Images/readonly.png" style="height:16px" />');
            }
        
        var postitstatus = null;
        if (this.document.getProperty('postitid').length > 0) {
            //console.log(this.document.getProperty('postitid'));
            postitstatus = new Ext.Button({
                tooltip: 'Afficher/Cacher les notes',
                text: 'Notes',
                icon: this.document.context.url + 'Images/simplenote16.png',
                scope: this,
                handler: function(b, e){
                    this.displaynotes = (!this.displaynotes);
                    this.viewNotes({
                        undisplay: (!this.displaynotes)
                    });
                }
            });
            
        }
        
        // TODO Correct the icon path
        var allocatedstatus = null;
        if (this.document.getProperty('allocated')) {
            var an = this.document.getProperty('allocatedname');
            var aimg = this.document.context.url + "Images/allocatedred16.png";
            if (this.document.getProperty('allocated') == this.document.context.getUser().id) {
                aimg = this.document.context.url + "Images/allocatedgreen16.png";
            }
            allocatedstatus = new Ext.Toolbar.TextItem('<img ext:qtip="Affecté à ' + an + '" src="' + aimg + '" style="height:16px" />');
        }
                
        return [statestatus,lockstatus, readstatus, postitstatus, allocatedstatus];
        
    }
    
});

Ext.reg('fdl-document', Ext.fdl.Document);

Ext.fdl.SubDocument = Ext.extend(Ext.form.FormPanel, {

    document: null,
    
    context: null,
    
    mode: 'view',
    
    // Force Ext default rendering, ignoring generateVersion property
    forceExt: false,
    
    displayToolbar: true,
    
    border: false,
    bodyStyle: 'overflow-y:auto; padding: 0px;',
    
    //frame: true,
    autoHeight: false,
    
    layout: 'form',
    
    method: 'POST',
    enctype: 'multipart/form-data',
    fileUpload: true,
    
    initComponent: function(){
    	
    	if(!this.document){
    		console.log('Ext.fdl.Document is not provided a document.');
    	}
    	
    	this.context = this.document.context;
    	
        Ext.fdl.SubDocument.superclass.initComponent.call(this);
    },
    
    close: function(){
    	console.log('DOCUMENT CLOSE');
		this.fireEvent('close',this);
    },
    
    getHtmlValue: function(attrid, tag){
    
        var as = this.document.getAttributes();
        var ht = '';
        for (var aid in as) {
            oa = as[aid];
            if (oa.parentId == attrid && oa.getValue) {
                if (oa.getValue() != '') 
                    ht += '<' + tag + '>' + oa.getLabel() + ' : ' + oa.getValue() + '</' + tag + '>';
            }
        }
        return ht;
    },
    
    applyLink: function(attrid){
    
        var me = this;
        
        if (attrid) {
        
            var text = me.document.getValue(attrid) || '';
            
            var reg = new RegExp("", "ig");
            reg.compile("\\[ADOC ([0-9]*)\\]", "ig");
            
            var getLink = function(str, id){
            
                var value = me.document.getValue(id);
                var display = me.document.getDisplayValue(id);
                
                return "<a class='docid' oncontextmenu='window.Fdl.ApplicationManager.displayDocument(" + value + ");return false;' href='javascript:window.Fdl.ApplicationManager.displayDocument(" + value + ");'> " +
                //				me.context.getDocument({
                //					id: id
                //				}).getTitle() +
                display +
                "</a>";
            };
            
            text = text.replace(reg, getLink);
            
        }
        
        return text;
    },
    
    orderAttribute: function(){
    
        if (!this.ordered) {
        
            function sortAttribute(attr1, attr2){
                return attr1.rank - attr2.rank;
            };
            
            function giveRank(type){
                for (var i = 0; i < ordered.length; i++) {
                    if (ordered[i].type == type) {
                        for (var j = 0; j < ordered.length; j++) {
                            if ((ordered[i].id == ordered[j].parentId) && (ordered[i].rank > ordered[j].rank || ordered[i].rank == 0)) {
                                ordered[i].rank = ordered[j].rank;
                            }
                        }
                    }
                }
            };
            
            var ordered = new Array();
            
            var as = this.document.getAttributes();
            for (var aid in as) {
                ordered.push(as[aid]);
            }
            
            //ordered = ordered.slice(); // Makes an independant copy of attribute array
            // Each structuring attribute is given its children lowest rank
            giveRank('array');
            giveRank('frame');
            giveRank('tab');
            
            ordered.sort(sortAttribute);
            
            this.ordered = ordered;
            
        }
        
        return this.ordered;
    },
    
    documentToolbarButton: function(){
    
        var button = new Ext.Button({
        
            text: 'Document',
            menu: [{
                xtype: 'menuitem',
                text: this.context._("eui::ToEdit"),
                scope: this,
                handler: function(){
                    this.switchMode('edit');
                },
                disabled: !this.document.canEdit()
            }, {
                xtype: 'menuitem',
                text: 'Verrouiller',
                scope: this,
                handler: function(){
                    this.document.lock();
                    Ext.Info.msg(this.document.getTitle(), "Verrouillage");
                    this.switchMode(this.mode);
                },
                disabled: !this.document.canEdit(),
                hidden: (this.document.getProperty('locked') > 0 || this.document.getProperty('locked') == -1)
            }, {
                xtype: 'menuitem',
                text: 'Déverrouiller',
                scope: this,
                handler: function(){
                    this.document.unlock();
                    Ext.Info.msg(this.document.getTitle(), "Déverrouillage");
                    this.switchMode(this.mode);
                },
                disabled: !this.document.canEdit(),
                hidden: (this.document.getProperty('locked') == 0 || this.document.getProperty('locked') == -1)
            }, {
                xtype: 'menuitem',
                text: 'Actualiser',
                scope: this,
                handler: function(){
                    this.document.reload();
                    this.switchMode(this.mode);
                }
            }, {
                xtype: 'menuitem',
                text: 'Supprimer',
                scope: this,
                handler: function(){
                    this.document.remove();
                    updateDesktop();
                    this.ownerCt.destroy();
                }
            }, {
                xtype: 'menuitem',
                text: 'Historique',
                scope: this,
                handler: function(){
                    var histowin = Ext.fdl.viewDocumentHistory(this.document);
                    histowin.show();
                }
            }, {
                xtype: 'menuitem',
                text: 'Ajouter une note',
                scope: this,
                handler: function(){
                    var nid = this.addNote();
                    this.viewNotes();
                    var wnid = this.notes[nid];
                    if (wnid) {
                        var p = wnid.items.itemAt(0);
                        setTimeout(function(){
                            p.items.itemAt(0).setVisible(false);
                            p.items.itemAt(1).setVisible(true);
                            p.items.itemAt(2).setVisible(true);
                            p.items.itemAt(3).setVisible(true);
                            p.items.itemAt(1).focus();
                        }, 1000);
                        
                        
                    }
                },
                
                hidden: (this.document.getProperty('locked') == -1)
            }, {
                xtype: 'menuitem',
                text: 'Affecter un utilisateur',
                scope: this,
                handler: function(){
                    var o = this;
                    //console.log(o);
                    var oa = new Fdl.RelationAttribute({
                        relationFamilyId: 'IUSER'
                    });
                    console.log('THISDOCUMENT', this.document, oa);
                    oa._family = this.document;
                    
                    //console.log(oa);
                    var wu = new Ext.fdl.DocId({
                        attribute: oa
                    });
                    
                    var toolbar = new Ext.Toolbar({
                        scope: this,
                        items: [{
                            xtype: 'button',
                            text: 'Affecter',
                            scope: this,
                            handler: function(){
                                var uid = wu.getValue();
                                if (uid) {
                                    if (this.document.allocate({
                                        userId: uid
                                    })) {
                                        this.switchMode(this.mode);
                                    }
                                    else {
                                        Ext.Msg.alert(Fdl.getLastErrorMessage());
                                    }
                                    w.close();
                                    
                                }
                            }
                        }, {
                            xtype: 'button',
                            scope: this,
                            text: 'Enlever l\'affectation',
                            handler: function(){
                                if (this.document.unallocate()) {
                                    this.switchMode(this.mode);
                                }
                                else {
                                    Ext.Msg.alert(Fdl.getLastErrorMessage());
                                }
                                w.close();
                                
                            }
                        }, {
                            xtype: 'button',
                            text: 'Annuler',
                            handler: function(){
                                w.close();
                            }
                        }]
                    });
                    
                    var w = new Ext.Window({
                        constrain: true,
                        renderTo: o.ownerCt.body,
                        title: 'Affecter un utilisateur',
                        bbar: toolbar,
                        items: [wu]
                    });
                    
                    //                    var f = new Ext.FormPanel({
                    //                        items: [wu, toolbar]
                    //                    });
                    //                    w.add(f);
                    w.show();
                    (function(){
                        wu.focus();
                    }).defer(1000);
                },
                disabled: !this.document.canEdit()
            }, {
            
                xtype: 'menuitem',
                text: 'Enregistrer une version',
                scope: this,
                handler: function(){
                    Ext.Msg.prompt('Version', 'Entrer le nom de la version', function(btn, text){
                        if (btn == 'ok') {
                            // Fdl.ApplicationManager.closeDocument(this.document.id);
                            
                            var previousId = this.document.id;
                            // Add Revision change document id ...
                            this.document.addRevision({
                                version: text,
                                volatileVersion: true
                            });
                            // ... so we need to notify a document
                            //Fdl.ApplicationManager.notifyDocument(this.document, previousId);
                        
                            //                            this.switchMode(this.mode);
                            //                            this.doLayout();
                        }
                    }, this);
                },
                disabled: !this.document.canEdit(),
                hidden:(this.document.getProperty('doctype')!='F')
            }]
        
        });
        
        return button;
    },
    
    cycleToolbarButton: function(){
    
        if (this.document.hasWorkflow()) {
        
            var menu = Array();
            
            var fs = this.document.getFollowingStates();
            
            for (var i = 0; i < fs.length; i++) {
            
                menu.push({
                    xtype: 'menuitem',
                    text: fs[i].transition ? Ext.util.Format.capitalize(fs[i].transitionLabel) : "Passer à l'état : " + fs[i].label,
                    style: 'background-color:' + fs[i].color + ';',
                    scope: this,
                    to_state: fs[i].state,
                    handler: function(b, e){
                    
                        var previousId = this.document.id;
                        
                        if (this.document.changeState({
                            state: b.to_state
                        })) {
                            Fdl.ApplicationManager.notifyDocument(this.document, previousId);
                            Ext.Info.msg(this.document.getTitle(), "Changement d'état");
                        }
                        else {
                            // Error during state change
                        }
                        
                        //                        this.switchMode(this.mode);
                        //                        this.doLayout();
                    }
                });
                
            }
            
            menu.push({
                xtype: 'menuitem',
                text: 'Voir le graphe',
                scope: this,
                handler: function(){
                    var wid = this.document.getProperty('wid');
                    new Ext.Window({
                        title: 'Cycle pour ' + this.document.getTitle(),
                        resizable: true,
                        width: 800,
                        height: 400,
                        border: false,
                        items: [new Ext.ux.MediaPanel({
                            mediaCfg: {
                                mediaType: 'HTM',
                                url: '/?app=FDL&action=WORKFLOW_GRAPH&id=' + wid,
                                width: '100%',
                                height: '100%'
                            }
                        })]
                    }).show();
                }
            });
            
            var button = new Ext.Button({
                text: 'Cycle',
                menu: menu
            });
            
            return button;
            
        }
        
        return null;
        
    },
    
    documentToolbarStatus: function(){
    
        var u = this.document.context.getUser();
        var info = u.getInfo();
        
        var lockstatus = null;
        if (this.document.getProperty('locked') > 0) {
            if (this.document.getProperty('locked') == info['id']) {
                lockstatus = new Ext.Toolbar.TextItem('<img ext:qtip="Verrouillé par ' + this.document.getProperty('locker') + '" src="FDL/Images/greenlock.png" style="height:16px" />');
            }
            else {
                lockstatus = new Ext.Toolbar.TextItem('<img ext:qtip="Vérrouillé par ' + this.document.getProperty('locker') + '" src="FDL/Images/redlock.png" style="height:16px" />');
            }
        }
        
        var readstatus = null;
        if (this.document.getProperty('locked') == -1) {
            readstatus = new Ext.Toolbar.TextItem('<img ext:qtip="Figé" src="FDL/Images/readfixed.png" style="height:16px" />');
        }
        else 
            if (!this.document.canEdit()) {
                readstatus = new Ext.Toolbar.TextItem('<img ext:qtip="Lecture seule" src="FDL/Images/readonly.png" style="height:16px" />');
            }
        
        var postitstatus = null;
        if (this.document.getProperty('postitid').length > 0) {
            //console.log(this.document.getProperty('postitid'));
            postitstatus = new Ext.Button({
                tooltip: 'Afficher/Cacher les notes',
                text: 'Notes',
                icon: 'Images/simplenote16.png',
                scope: this,
                handler: function(b, e){
                    this.displaynotes = (!this.displaynotes);
                    this.viewNotes({
                        undisplay: (!this.displaynotes)
                    });
                }
            });
            
        }
        
        // TODO Correct the icon path
        var allocatedstatus = null;
        if (this.document.getProperty('allocated')) {
            var an = this.document.getProperty('allocatedname');
            var aimg = "Images/allocatedred16.png";
            if (this.document.getProperty('allocated') == this.document.context.getUser().id) {
                aimg = "Images/allocatedgreen16.png";
            }
            allocatedstatus = new Ext.Toolbar.TextItem('<img ext:qtip="Affecté à ' + an + '" src="' + aimg + '" style="height:16px" />');
        }
        
        return [lockstatus, readstatus, postitstatus, allocatedstatus];
        
    },
    
    adminToolbarButton: function(){
    
        var button = new Ext.Button({
            text: 'Administration',
            menu: [{
                xtype: 'menuitem',
                text: 'Famille',
                scope: this,
                handler: function(b, e){
                    Fdl.ApplicationManager.displayFamily(this.document.getProperty('fromid'));
                }
            }, {
                xtype: 'menuitem',
                text: 'Modifier le Cycle',
                scope: this,
                handler: function(b, e){
                    Fdl.ApplicationManager.displayCycleEditor(this.document.getProperty('wid'));
                }
            }, {
                xtype: 'menuitem',
                text: 'Administrer le Cycle',
                scope: this,
                handler: function(b, e){
                    Fdl.ApplicationManager.displayDocument(this.document.getProperty('wid'), 'edit');
                }
            }, {
                xtype: 'menuitem',
                text: 'Propriétés',
                handler: function(b, e){
                    Ext.Msg.alert('Propriétés', 'Pas encore disponible.');
                }
            }]
        });
        
        return button;
        
    },
    
    renderToolbar: function(){
    	    	
        if (!this.displayToolbar) {
            return false;
        }
        
        var toolbar = new Ext.Toolbar({
            style: 'margin-bottom:10px;'
        });
        
        toolbar.add(this.documentToolbarButton());
        
        // Add cycle toolbar button if applicable
        var cycleToolbarButton = this.cycleToolbarButton();
        if (cycleToolbarButton) {
            toolbar.add(cycleToolbarButton);
        }
        
       // toolbar.add(this.adminToolbarButton());
        
        toolbar.add(new Ext.Toolbar.Fill());
        
        var toolbarStatus = this.documentToolbarStatus();
        
        for (var i = 0; i < toolbarStatus.length; i++) {
            if (toolbarStatus[i]) {
                toolbar.add(toolbarStatus[i]);
            }
        }
        
        return toolbar;
        
    },
    
    /**
     *
     * @param {Object} attrid
     * @param {Object} config (display : if true returns widget even if value is empty)
     */
    getExtValue: function(attrid, config){
    
        if ((config && config.display) || this.alwaysDisplay) {
            var display = true;
        }
        
        if ((config && config.hideHeader)) {
            var hideHeader = true;
        }
        
        var attr = this.document.getAttribute(attrid);
        
        if (!attr) {
            return null;
        }
        
        var ordered = this.orderAttribute();
        
        // Handle Visibility
        switch (attr.getVisibility()) {
            case 'W':
            case 'R':
            case 'S':
                break;
            case 'O':
            case 'H':
            case 'I':
                return null;
                break;
            case 'U':
                //For array, prevents add and delete of rows
                break;
        }
        
        switch (attr.type) {
        
            case 'menu':
            case 'action':
                return new Ext.Button({
                    text: attr.getLabel(),
                    handler: this.handleAction
                });
                break;
                
            case 'text':
            case 'longtext':
            case 'date':
            case 'integer':
            case 'int':
            case 'double':
            case 'money':
                if (this.document.getValue(attr.id) || display) {
                    return new Ext.fdl.DisplayField({
                        fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                        value: this.document.getValue(attr.id)
                    });
                }
                else {
                    return null;
                }
                break;
                
            case 'htmltext':
                if (this.document.getValue(attr.id) || display) {
                    return new Ext.fdl.DisplayField({
                        fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                        value: this.applyLink(attr.id)
                    });
                }
                else {
                    return null;
                }
                break;
                
            case 'time':
                if (this.document.getValue(attr.id) || display) {
                    return new Ext.form.TimeField({
                        fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                        value: this.document.getValue(attr.id),
                        disabled: true
                    });
                }
                else {
                    return null;
                }
                break;
                
            case 'timestamp':
                if (this.document.getValue(attr.id) || display) {
                    return new Ext.ux.form.DateTime({
                        fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                        value: this.document.getValue(attr.id),
                        disabled: true
                    });
                }
                else {
                    return null;
                }
                break;
                
            case 'password':
                if (this.document.getValue(attr.id) || display) {
                    return new Ext.form.TextField({
                        fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                        inputType: 'password',
                        value: this.document.getValue(attr.id),
                        disabled: true
                    });
                }
                else {
                    return null;
                }
                break;
                
            case 'image':
                //                return new Ext.form.FileUploadField({
                //                    fieldLabel: attr.getLabel(),
                //                    buttonCfg: {
                //                        text: '',
                //                        iconCls: 'upload-icon'
                //                    },
                //                    disabled: true
                //                });
                return null;
                break;
                
            case 'color':
                //                return new Ext.form.ColorField({
                //                    fieldLabel: attr.getLabel()
                //                })
                ////console.log('Attribute not represented : ' + attr.type);
                return null;
                break;
                
            case 'frame':
                
                var frame = new Ext.form.FieldSet({
                    title: Ext.util.Format.capitalize(attr.getLabel()),
                    autoHeight: true,
                    collapsible: true,
                    width: 'auto',
                    labelWidth: 150,
                    style: 'margin:10px;',
                    bodyStyle: 'padding-left:40px;width:auto;'
                });
                
                var empty = true;
                
                for (var i = 0; i < ordered.length; i++) {
                    var curAttr = ordered[i];
                    if (curAttr.parentId == attr.id) {
                        var extValue = this.getExtValue(curAttr.id);
                        if (extValue != null) {
                            frame.add(extValue);
                            empty = false;
                        }
                    }
                }
                
                if (!empty || display) {
                    return frame;
                }
                else {
                    return null;
                }
                break;
                
            case 'tab':
                
                var tab = new Ext.Panel({
                    title: Ext.util.Format.capitalize(attr.getLabel()),
                    autoHeight: true,
                    autoWidth: true,
                    width: 'auto',
                    layout: 'form',
                    border: false,
                    defaults: {
                        //width: 'auto',
                        // as we use deferredRender:false we mustn't
                        // render tabs into display:none containers
                        hideMode: 'offsets'
                    }
                });
                
                var empty = true;
                
                for (var i = 0; i < ordered.length; i++) {
                    var curAttr = ordered[i];
                    if (curAttr.parentId == attr.id) {
                        var extValue = this.getExtValue(curAttr.id);
                        if (extValue != null) {
                            tab.add(extValue);
                            empty = false;
                        }
                    }
                }
                
                if (!empty || display) {
                    return tab;
                }
                else {
                    return null;
                }
                break;
                
            case 'array':
                
                var elements = attr.getElements();
                
                var fields = new Array();
                
                for (var i = 0; i < elements.length; i++) {
                    fields.push(elements[i].id);
                }
                
                //console.log('VALUES',this.document.getValues(attr.id));
                
                //var values = attr.getArrayValues()
                
                var values = this.document.getValue(attr.id) || [];
                
                if (values.length == 0 && !display) {
                    return null;
                }
                
                for (var i = 0; i < elements.length; i++) {
                
                    // Transform docid into links
                    if (elements[i].type == 'docid') {
                        for (var j = 0; j < values.length; j++) {
                        
                            if (values[j][elements[i].id]) {
                                values[j][elements[i].id] = "<a class='docid' " +
                                'href="javascript:window.Fdl.ApplicationManager.displayDocument(' +
                                values[j][elements[i].id] +
                                ');"' +
                                'oncontextmenu="window.Fdl.ApplicationManager.displayDocument(' +
                                values[j][elements[i].id] +
                                ');return false;">' +
                                elements[i].getTitle()[j] +
                                "</a>";
                            }
                            else {
                                values[j][elements[i].id] = '';
                            }
                        }
                        
                    }
                    
                    // Transform enum with correct label
                    if (elements[i].type == 'enum') {
                    
                        for (var j = 0; j < values.length; j++) {
                        
                            values[j][elements[i].id] = elements[i].getEnumLabel({
                                key: values[j][elements[i].id]
                            });
                            
                        }
                    }
                }
                
                var store = new Ext.data.JsonStore({
                    fields: fields,
                    data: values
                });
                
                var columns = [];
                
                for (var i = 0; i < ordered.length; i++) {
                    var curAttr = ordered[i];
                    if (curAttr.parentId == attr.id) {
                    
                        // Handle Visibility
                        switch (curAttr.getVisibility()) {
                            case 'W':
                            case 'R':
                            case 'S':
                                columns.push({
                                    header: Ext.util.Format.capitalize(curAttr.getLabel()),
                                    dataIndex: curAttr.id
                                });
                                break;
                            case 'O':
                            case 'H':
                            case 'I':
                                break;
                            case 'U':
                                //For array, prevents add and delete of rows
                                break;
                        }
                        
                        
                    }
                }
                
                var array = new Ext.grid.GridPanel({
                
                    title: Ext.util.Format.capitalize(attr.getLabel()),
                    autoHeight: true,
                    collapsible: true,
                    titleCollapse: true,
                    viewConfig: {
                        forceFit: true,
                        autoFill: true
                    },
                    animCollapse: false,
                    disableSelection: true,
                    store: store,
                    columns: columns,
                    style: 'margin-bottom:10px;',
                    header: !hideHeader,
                    border: false
                });
                
                return array;
                break;
                
            case 'enum':
                //console.log('Attribute not represented : ' + attr.type);
                return null;
                break;
                
            case 'docid':
                
                if (this.document.getValue(attr.id) || display) {
                
                    if (attr.getOption('multiple') != 'yes') {
                    
                        return new Ext.fdl.DisplayField({
                            fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                            value: "<a class='docid' " +
                            'href="javascript:window.Fdl.ApplicationManager.displayDocument(' +
                            this.document.getValue(attr.id) +
                            ');"' +
                            'oncontextmenu="window.Fdl.ApplicationManager.displayDocument(' +
                            this.document.getValue(attr.id) +
                            ');return false;">' +
							(this.document.getDisplayValue(attr.id) || '') +
                            "</a>"
                        });
                        
                    }
                    else {
                    
                        var values = this.document.getValue(attr.id);
                        var displays = this.document.getDisplayValue(attr.id);
                        
                        console.log('Attribute', values, displays);
                        
                        var fieldValue = '';
                        
                        for (var i = 0; i < values.length; i++) {
                            fieldValue += "<a class='docid' " +
                            'href="javascript:window.Fdl.ApplicationManager.displayDocument(' +
                            values[i] +
                            ');"' +
                            'oncontextmenu="window.Fdl.ApplicationManager.displayDocument(' +
                            values[i] +
                            ');return false;">' +
                            displays[i] +
                            "</a><br/>";
                        }
                        
                        return new Ext.fdl.DisplayField({
                            fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                            value: fieldValue
                        });
                        
                        
                    }
                }
                else {
                    return null;
                }
                break;
                
            case 'file':
                
                if (this.document.getValue(attr.id) || display) {
                    return new Ext.fdl.DisplayField({
                        fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                        value: '<a class="docid" ' +
                        'href="' +
                        this.document.getDisplayValue(attr.id, {
                            url: true,
                            inline: false
                        }) +
                        '" >' +
                        this.document.getDisplayValue(attr.id) +
                        "</a>"
                    });
                }
                else {
                    return null;
                }
                break;
                
            default:
                //console.log('Attribute not represented : ' + attr.type);
                return null;
                break;
                
        }
    },
    
    getHeader: function(){
    
        return new Ext.Panel({
            html: ""
            /*
             html: '<img src=' +
             this.document.getIcon({
             width: 48
             }) +
             ' style="float:left;padding:0px 5px 5px 0px;" />' +
             "<p style='font-size:12;'>" +
             this.document.getProperty('fromtitle') +
             '</p>' +
             '<h1 style="display:inline;font-size:14;text-transform: uppercase;">' +
             (this.document.getProperty('id') ? this.document.getTitle() : ('Création ' + this.document.getProperty('fromtitle'))) +
             '</h1>' +
             '<span style="padding-left:10px">' +
             (this.document.getProperty('version') ? (' Version ' + this.document.getProperty('version')) : '') +
             '</span>' +
             (this.document.hasWorkflow() ?
             "<p><i style='border-style:none none solid none;border-width:2px;border-color:" +
             this.document.getColorState() +
             "'>" +
             (this.document.isFixed() ? ('(' + this.document.getLocalisedState() + ') ') : '') )+
             "<b>" +
             (this.document.getActivityState() ? Ext.util.Format.capitalize(this.document.getActivityState()) : '') +
             "</b>" +
             '</i></p>'*/
        });
        
    },
    
    /**
     * Get Ext default input component for a given id.
     * @param {Object} attrid
     * @param {Object} inArray
     * @param {Object} rank
     * @param {Object} defaultValue
     * @param {Object} empty
     */
    getExtInput: function(attrid, inArray, rank, defaultValue, empty){
    
        var attr = this.document.getAttribute(attrid);
        
        if (!attr) {
            return null;
        }
        
        //var name = inArray ? attr.id + '[]' : attr.id;
        var name = attr.id;
        
        var ordered = this.orderAttribute();
        
        // Handle Visibility
        switch (attr.getVisibility()) {
            case 'W':
            case 'O':
                break;
            case 'R':
            case 'H':
            case 'I':
                return null;
                break;
            case 'S':
                //Viewable in edit mode but not editable
                var disabled = true;
                break;
            case 'U':
                //For array, prevents add and delete of rows
                break;
        }
        
        switch (attr.type) {
        
            case 'menu':
                //                return new Ext.Button({
                //                    text: Ext.util.Format.capitalize(attr.getLabel()),
                //                    handler: this.handleAction
                //                });
                break;
                
            case 'text':
                return new Ext.fdl.Text({
                    fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                    value: !empty ? (rank != undefined ? this.document.getValue(attr.id)[rank] : this.document.getValue(attr.id)) : null,
                    name: name,
                    allowBlank: !attr.needed,
                    disabled: disabled
                });
                break;
                
            case 'longtext':
                return new Ext.fdl.LongText({
                    fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                    value: !empty ? (rank != undefined ? this.document.getValue(attr.id)[rank] : this.document.getValue(attr.id)) : null,
                    name: name,
                    allowBlank: !attr.needed,
                    disabled: disabled
                });
                break;
                
            case 'htmltext':
                return new Ext.fdl.HtmlText({
                    fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                    value: this.document.getValue(attr.id),
                    name: name,
                    disabled: disabled
                });
                break;
                
            case 'int':
            case 'integer':
                return new Ext.fdl.Integer({
                    fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                    value: this.document.getValue(attr.id),
                    name: name,
                    allowBlank: !attr.needed,
                    disabled: disabled
                });
                break;
                
            case 'double':
                return new Ext.fdl.Double({
                    fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                    value: this.document.getValue(attr.id),
                    name: name,
                    allowBlank: !attr.needed,
                    disabled: disabled
                });
                break;
                
            case 'money':
                return new Ext.fdl.Money({
                    fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                    value: this.document.getValue(attr.id),
                    name: name,
                    allowBlank: !attr.needed,
                    disabled: disabled
                });
                break;
                
            case 'date':
                return new Ext.fdl.Date({
                    fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                    altFormats: 'd-j-Y|d-m-Y',
                    format: 'd/m/Y',
                    value: !empty ? (rank != undefined ? this.document.getValue(attr.id)[rank] : this.document.getValue(attr.id)) : null,
                    name: name,
                    disabled: disabled
                });
                break;
                
            case 'time':
                return new Ext.form.TimeField({
                    fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                    value: this.document.getValue(attr.id),
                    name: name,
                    disabled: disabled
                });
                break;
                
            case 'timestamp':
                return new Ext.ux.form.DateTime({
                    fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                    name: name,
                    disabled: disabled
                });
                break;
                
            case 'password':
                return new Ext.form.TextField({
                    fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                    inputType: 'password',
                    value: this.document.getValue(attr.id),
                    name: name,
                    disabled: disabled
                });
                break;
                
            case 'image':
                return new Ext.fdl.Image({
                    fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                    name: name,
                    value: attr.getFileName()
                });
                break;
                
            case 'file':
                return new Ext.fdl.File({
                    fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                    name: name,
                    value: attr.getFileName()
                });
                break;
                
            case 'color':
                return new Ext.fdl.Color({
                    fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                    name: name
                });
                break;
                
            case 'frame':
                
                var frame = new Ext.form.FieldSet({
                    title: Ext.util.Format.capitalize(attr.getLabel()),
                    autoHeight: true,
                    autoWidth: true,
                    collapsible: true,
                    labelWidth: 150,
                    //width: 'auto',
                    style: 'margin:10px;',
                    //bodyStyle: 'padding-left:40px;width:auto;'
                    bodyStyle: 'padding-left:20px;',
                    anchor: '100%'
                });
                
                for (var i = 0; i < ordered.length; i++) {
                    var curAttr = ordered[i];
                    if (curAttr.parentId == attr.id) {
                        var ext_input = this.getExtInput(curAttr.id);
                        if (ext_input != null) {
                            frame.add(ext_input);
                        }
                    }
                }
                
                return frame;
                break;
                
            case 'tab':
                
                var tab = new Ext.Panel({
                    title: Ext.util.Format.capitalize(attr.getLabel()),
                    autoHeight: true,
                    autoWidth: true,
                    //frame: true,
                    layout: 'form',
                    border: false,
                    defaults: { // as we use deferredRender:false we mustn't
                    // render tabs into display:none containers
                    //hideMode: 'offsets'
                    }
                });
                
                var empty = true;
                
                for (var i = 0; i < ordered.length; i++) {
                    var curAttr = ordered[i];
                    if (curAttr.parentId == attr.id) {
                        tab.add(this.getExtInput(curAttr.id));
                        empty = false;
                    }
                }
                
                if (!empty) {
                    return tab;
                }
                else {
                    return null;
                }
                break;
                
            // Improvement for using RowEditor with array
            /*			case 'array':
             var elements = attr.getElements();
             
             var values = attr.getArrayValues();
             
             var docWidget = this;
             
             var columns = [new Ext.grid.RowNumberer()];
             
             var fields = [];
             
             for (var i = 0; i < elements.length; i++) {
             var attr = this.document.getAttribute(elements[i].id);
             var col = Ext.apply({},{
             editor: this.getExtInput(elements[i].id, true, null, null, true),
             header: attr.getLabel(),
             dataIndex: elements[i].id,
             hideable: false,
             viewCfg: {
             autoFill: true,
             forceFit: true
             }
             });
             //should be a method
             switch(attr.type){
             case 'docid':
             var renderer = attr.getTitle.createDelegate(attr);
             //this is not the good renderer
             //the good one would only be fdl.getTitle
             //that does not exists currently
             break;
             default:
             var renderer = null;
             break;
             }
             if(renderer){
             Ext.apply(col, {
             renderer: renderer
             });
             }
             //EO method
             var field = Ext.apply({},{
             name: elements[i].id
             })
             columns.push(col);
             fields.push(field);
             }
             var store = new Ext.data.Store({
             reader: new Ext.data.JsonReader({
             fields: fields
             }),
             data: values
             })
             
             // not forget to include ext/examples/ux/RowEditor.js
             //maybe work on roweditor about last row...
             var editor = new Ext.ux.RowEditor({
             saveText: 'Update'
             });
             //var arrayPanel = new Ext.grid.EditorGridPanel({ //only gridpanel if using roweditor!
             var arrayPanel = new Ext.grid.GridPanel({
             title: Ext.util.Format.capitalize(attr.getLabel()),
             frame: true,
             plugins:[editor],
             collapsible: true,
             titleCollapse: true,
             animCollapse: false,
             style: 'margin-bottom:10px;',
             columns: columns,
             store: store,
             autoHeight: true,
             autoScroll: true,
             bbar: [{
             ref: '../addBtn',
             iconCls: 'icon-row-add',
             text: 'Add row',
             handler: function(){
             var e = new store.recordType();
             editor.stopEditing();
             store.insert(0, e);
             arrayPanel.getView().refresh();
             arrayPanel.getSelectionModel().selectRow(0);
             editor.startEditing(0);
             }
             },{
             ref: '../removeBtn',
             iconCls: 'icon-row-delete',
             text: 'Remove row',
             disabled: true,
             handler: function(){
             editor.stopEditing();
             var s = arrayPanel.getSelectionModel().getSelections();
             for(var i = 0, r; r = s[i]; i++){
             store.remove(r);
             }
             }
             }]
             });
             
             arrayPanel.getSelectionModel().on('selectionchange', function(sm){
             arrayPanel.removeBtn.setDisabled(sm.getCount() < 1);
             });
             return arrayPanel;
             break;
             */
            case 'array':
                
                var elements = attr.getElements();
                
                var fields = new Array();
                
                for (var i = 0; i < elements.length; i++) {
                    fields.push(elements[i].id);
                }
                
                //var values = attr.getArrayValues();
                
                var values = this.document.getValue(attr.id) || [];
                
                var docWidget = this;
                
                var columns = 0;
                for (var i = 0; i < elements.length; i++) {
                
                    // Handle Visibility
                    switch (elements[i].getVisibility()) {
                        case 'W':
                        case 'O':
                            columns++;
                            break;
                    }
                }
                
                var arrayPanel = new Ext.Panel({
                    title: Ext.util.Format.capitalize(attr.getLabel()),
                    frame: true,
                    collapsible: true,
                    titleCollapse: true,
                    animCollapse: false,
                    style: 'margin-bottom:10px;',
                    layout: 'table',
                    layoutConfig: {
                        columns: columns
                    },
                    bbar: [{
                        text: 'Ajouter',
                        tooltip: 'Ajouter',
                        scope: docWidget,
                        handler: function(){
                        
                            // For each columnPanel child
                            // Add one row of editor widget
                            
                            var elements = attr.getElements();
                            
                            for (var i = 0; i < elements.length; i++) {
                            
                                switch (elements[i].getVisibility()) {
                                    case 'W':
                                    case 'O':
                                        
                                        var editorWidget = this.getExtInput(elements[i].id, true, null, null, true);
                                        
                                        arrayPanel.add(editorWidget);
                                        
                                        break;
                                        
                                }
                                
                            }
                            
                            arrayPanel.doLayout();
                            
                        }
                    }]
                
                });
                
                for (var i = 0; i < elements.length; i++) {
                
                    // Handle Visibility
                    switch (elements[i].getVisibility()) {
                        case 'W':
                        case 'O':
                            
                            switch (elements[i].type) {
                            
                                case 'enum':
                                    if (attr.getOption('eformat') == 'bool') {
                                        var width = 60;
                                    }
                                    else {
                                        var width = 120;
                                    }
                                    break;
                                    
                                case 'docid':
                                    var width = 200;
                                    break;
                                    
                                case 'date':
                                    var width = 110;
                                    break;
                                    
                                default:
                                    var width = 100;
                                    break;
                                    
                            }
                            
                            var columnPanel = new Ext.Panel({
                                title: Ext.util.Format.capitalize(elements[i].getLabel()),
                                width: width,
                                style: 'overflow:auto;',
                                bodyStyle: 'text-align:center;margin-top:3px;',
                                frame: false,
                                layout: 'form',
                                hideLabels: true
                            });
                            
                            arrayPanel.add(columnPanel);
                            
                            break;
                            
                    }
                    
                }
                
                for (var j = 0; j < values.length; j++) {
                
                    for (var i = 0; i < elements.length; i++) {
                    
                        switch (elements[i].getVisibility()) {
                            case 'W':
                            case 'O':
                                
                                var editorWidget = this.getExtInput(elements[i].id, true, j, values[j][elements[i].id]);
                                
                                arrayPanel.add(editorWidget);
                                
                        }
                        
                    }
                    
                }
                
                return arrayPanel;
                
                break;
                
            case 'enum':
                
//                var label = attr.getEnumLabel({
//                    key: this.document.getValue(attr.id)[rank]
//                });
//                
//                if (attr.getOption('eformat') == 'bool') {
//                
//                    return new Ext.ux.form.XCheckbox({
//                        fieldLabel: inArray ? ' ' : Ext.util.Format.capitalize(attr.getLabel()),
//                        //boxLabel: inArray ? '' : Ext.util.Format.capitalize(attr.getLabel()) ,
//                        checked: this.document.getValue(attr.id)[rank] == 'yes' ? true : false,
//                        submitOnValue: 'yes',
//                        submitOffValue: 'no',
//                        name: name,
//                        style: inArray ? 'margin:auto;' : ''
//                    });
//                    
//                }
//                else {
//                
//                    var items = attr.getEnumItems();
//                    
//                    return new Ext.form.ComboBox({
//                    
//                        fieldLabel: inArray ? ' ' : Ext.util.Format.capitalize(attr.getLabel()),
//                        
//                        valueField: 'key',
//                        displayField: 'label',
//                        
//                        width: 150,
//                        
//                        // Required to give simple select behaviour
//                        //emptyText: '--Famille--',
//                        editable: false,
//                        forceSelection: true,
//                        disableKeyFilter: true,
//                        triggerAction: 'all',
//                        mode: 'local',
//                        
//                        value: items[0].key,
//                        
//                        store: new Ext.data.JsonStore({
//                            data: items,
//                            fields: ['key', 'label']
//                        })
//                    
//                    });
//                    
//                }
                break;
                
            case 'docid':
                
                if (attr.getOption('multiple') == 'yes') {
                
                    return new Ext.fdl.MultiDocId({
                    
                        attribute: attr,
                        
                        fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                        
                        docIdList: this.document.getValue(attr.id),
                        
                        docTitleList: this.document.getValue(attr.id + '_title')
                    
                    });
                    
                }
                else {
                
                    return new Ext.fdl.DocId({
                    
                        attribute: attr,
                        
                        fieldLabel: Ext.util.Format.capitalize(attr.getLabel()),
                        //value: this.document.getValue(attr.id),
                        value: !empty ? (rank != null ? this.document.getDisplayValue(attr.id)[rank] : this.document.getDisplayValue(attr.id)) : null,
                        //name: attr.id,
                        hiddenName: name,
                        hiddenValue: !empty ? (defaultValue != null ? defaultValue : this.document.getValue(attr.id)) : null,
                        allowBlank: !attr.needed,
                        
                        disabled: disabled
                    
                    });
                }
                break;
                
            default:
                console.log('Attribute not represented : ' + attr.type);
                return null;
                break;
                
        }
        
    }
    
});
