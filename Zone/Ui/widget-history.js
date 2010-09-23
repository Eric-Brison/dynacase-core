
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

Ext.fdl.viewDocumentHistory = function(doc) {
        var h = doc.getHistory();
        if (h) {
            var li = h.getRevisions();
            var items = [];
            var grid;
            var store;
            var hi;
            var hData;
            var th;
            var version;
	    var moreinfo='';
            hData = [];
                      
            for (var i = 0; i < li.length; i++) {		
		if ((i==0)&&(li.length>1)&&(Fdl._print_r(li[i].getValues())==Fdl._print_r(li[i+1].getValues()))) {
		    continue; // do not display latest revision if no changes
		}
                hi = h.getItems(li[i].id);
		moreinfo='<table>';
                for (var j = 0; j < hi.length; j++) {
		    
		    //                    hData.push([th, li[i].getProperty('revision'), hi[j].id, hi[j].comment, hi[j].userName, hi[j].date]);
		    moreinfo+=String.format('<tr><td style="width:80%">{0}</td><td style="width:10px;white-space:nowrap">{1}</td><td style="width:10px;white-space:nowrap">{2}</td></tr>', hi[j].comment,hi[j].userName, hi[j].date);
                }
		moreinfo+='</table>';
		hData.push([li[i].getProperty('title'), 
			    li[i].getProperty('revision'),
			    (li[i].getProperty('locked')==-1)?('<span style="padding-left:10px;margin-right:3px;background-color:'+li[i].getColorState()+'">&nbsp;</span>'+li[i].getLocalisedState()):li[i].getActivityState(),

			    li[i].getProperty('version'),
			    li[i].getProperty('id') ,
			    'no comment',li[i].getProperty('ownername') , 
			    li[i].getProperty('mdate'),
			    moreinfo]);
            }

	    
            var reader = new Ext.data.ArrayReader({}, [{
                name: 'title'
            }, {
                name: 'revision'
            },{
                name: 'state'
            },{
                name: 'version'
            }, {
                name: 'id'
            }, {
                name: 'comment'
            }, {
                name: 'username'
            }, {
                name: 'date',
                type: 'date',
               dateFormat: 'd/m/Y H:i:s'
            },{
                name: 'desc'
            }]);


	    var xg = Ext.grid;


	    ////////////////////////////////////////////////////////////////////////////////////////
	    // Grid 1
	    ////////////////////////////////////////////////////////////////////////////////////////
	    // row expander
	    var expander = new xg.RowExpander({
		tpl : new Ext.Template(
		    '<p><b>Titre:</b> {title}</p><br>',
		    '<p><b>Détail:</b> {desc}</p>'
		)
	    });

	    var grid1 = new xg.GridPanel({
		store: new Ext.data.Store({
		    reader: reader,
		    data: hData
		}),
		cm: new xg.ColumnModel({
		    defaults: {
			width: 20,
			sortable: true
		    },
		    columns: [expander,{
			id: 'revision',
			hidden: false,
			header: "Révision",
			sortable: true,
			dataIndex: 'revision'
                    },{
			id: 'state',
			hidden: (!doc.hasWorkflow()),
			header: "État",
			sortable: true,
			dataIndex: 'state'
                    }, {
			id: 'version',
			hidden: false,
			header: "Version",
			sortable: true,
			dataIndex: 'version'
                    }, {
			id: 'title',
			hidden: true,
			header: "Title",
			sortable: false,
			dataIndex: 'title'
                    }, {
			id: 'id',
			hidden: true,
			header: "id",
			sortable: true,
			dataIndex: 'id'
                    }, {
			header: "Auteur",
			sortable: true,
			dataIndex: 'username'
                    }, {
			header: "Date",
			sortable: true,
			renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),
			dataIndex: 'date'
                    }]
                    
		}),
		viewConfig: {
		    forceFit:true
		},
		sm: new Ext.grid.RowSelectionModel({
                    singleSelect: true,
                    listeners: {
                        rowselect: {
                            fn: function(sm, index, record){
                                Fdl.ApplicationManager.displayDocument(record.data.id, 'view');
                                // display document here
                            }
                        }
                    }
                }),
		columnLines: true,
		forceFit:true,
		plugins: expander,
		collapsible: false,
		animCollapse: false,
		title: '',
		iconCls: 'icon-grid'
	    });


           
            

            
            var histowin = new Ext.Window({
                layout: 'fit',
                title: 'Historique ' + doc.getTitle(),
                //closeAction: 'hide',
                width: 500 ,
                height: 450 ,
                resizable: true,
                plain: true,
                //renderTo: Fdl.ApplicationManager.desktopPanel.body,
                constrain: true,
                items: [grid1],
		listeners: {			    
		    show: function(o) {
			(function () {expander.expandRow(0);}).defer(100);
		    }}
            });
            
            return histowin;
        }
        
    };