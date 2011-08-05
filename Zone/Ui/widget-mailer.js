
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Ext.fdl.Mailer
 * @namespace Ext.fdl
 */
Ext.fdl.Mailer = Ext.extend(Ext.FormPanel, {

    //bodyStyle: 'padding:5px;overflow:auto;',
    
    //layout: 'form',
                
    initComponent: function(){
    
    	var Target = Ext.data.Record.create([{
        	name: 'sendmode',
	        type: 'string'
	    },{
	        name: 'email',
	        type: 'string'
	    },{
	        name: 'notification',
	        type: 'bool'
	    }]);

	    var store = new Ext.data.GroupingStore({
	        reader: new Ext.data.JsonReader({fields: Target}),
	        data: []//,
	        //sortInfo: {field: 'email', direction: 'ASC'}
	    });
	    
	    var editor = new Ext.ux.grid.RowEditor({
		    saveText: 'Valider',
		    cancelText: 'Annuler',
		    monitorValid: true,
		    errorSummary: false
		});
	    
	    var grid = new Ext.grid.GridPanel({
	        store: store,
	        plugins: [editor],
	        view: new Ext.grid.GroupingView({
    	        markDirty: false
	        }),
	        autoExpandColumn: 'email',
	        columnLines: true,
	        stripeRows: true,
	        columns: [
	        {
	            id: 'sendmode',
	            header: 'Mode',
	            dataIndex: 'sendmode',
	            width: 50,
	            editor: {
	                xtype: 'combo',
	                allowBlank: false,
	                mode: 'local',
	                lazyRender: true,
	                triggerAction: 'all',
	                editable: false,
	                store: new Ext.data.ArrayStore({
				        fields: [
				            'sendmodeValue',
				            'sendmodeDisplay'
				        ],
				        data: [['to', 'To'], ['cc', 'Cc'], ['bcc', 'Bcc']]
				    }),
				    valueField: 'sendmodeValue',
				    displayField: 'sendmodeDisplay'
	            }
	        },{	        	
	            id: 'email',
	            header: 'Destinataire',
	            dataIndex: 'email',
	            sortable: true,
	            editor: {
	                xtype: 'textfield',
	                allowBlank: false,
	                vtype: 'email'
	            }
	        },{
	            xtype: 'booleancolumn',
	            id: 'notification',
	            header: 'Notification',
	            dataIndex: 'notification',
	            align: 'center',
	            width: 50,
	            trueText: 'Yes',
	            falseText: 'No',
	            editor: {
	                xtype: 'checkbox'
	            }
	        }]	        
	    });
    	
    	Ext.apply(this,{
    		title: 'Edition de mail',
    		labelWidth: 140,
    		bodyStyle: 'padding:5px;',
    		tbar: [{
	        	text: 'Envoyer le mail',
	        	handler : function(){
	        		
	        		var send = {};
	        		
	        		send.to = '';
	        		send.cc = '';
	        		send.bcc = '';
	        		
	        		store.each(function(record){
	        			switch(record.get('sendmode')){
	        				case 'to':
	        					send.to += record.get('email') + ',';
	        				break;
	        				case 'cc':
	        					send.cc += record.get('email') + ',';
	        				break;
	        				case 'bcc':
	        					send.bcc += record.get('email') + ',';
	        				break;
	        			}
	        		},this);
	        		
	        		if(send.to != ''){
	        			send.to = send.to.substr(0,(send.to.length - 1));
	        		}
	        		if(send.cc != ''){
	        			send.cc = send.cc.substr(0,(send.cc.length - 1));
	        		}
	        		if(send.bcc != ''){
	        			send.bcc = send.bcc.substr(0,(send.bcc.length - 1));
	        		}
		            
	        		// TODO Of course document must not be hardcoded here
	        		var doc = C.getDocument({
	        			id:1705
	        		});
	        		
	        		var subjectField = this.items.find(function(item){
	        			return(item.id == 'subject');	
	        		});
	        		
	        		var commentField = this.items.find(function(item){
	        			return(item.id == 'comment');	
	        		});
	        		
	        		var savecopyBox = this.items.find(function(item){
	        			return(item.id == 'savecopy');
	        		});
	        			        			        		
	        		send.subject = subjectField.getValue();
	        		send.comment = commentField.getValue();
	        		send.sendercopy =  savecopyBox.getValue();
	        		
	        		if(doc.send(send)){
	        			console.log('Send successful');
	        		} else {
	        			console.log('Send error');
	        		}
	        		
	            },
	            scope: this
	        },{
	        	text: 'Ajouter',
	        	handler : function(){
		            // access the Record constructor through the grid's store
		            var target = new (store.recordType)({
		                sendmode: 'to',
		                email: '',
		                notification: false
		            });
		            editor.stopEditing();
	                store.insert(0, target);
	                grid.getView().refresh();
	                grid.getSelectionModel().selectRow(0);
	                editor.startEditing(0);
	            }
	        },{
	        	text: 'Retirer',
	        	handler: function(){
	        		editor.stopEditing();
	                var s = grid.getSelectionModel().getSelections();
	                for(var i = 0, r; r = s[i]; i++){
	                    store.remove(r);
	                }
	        	}
	        },{
	        	text: 'Annuler'
	        }],
	        layout: {
	        	type: 'vbox',
            	align: 'stretch'
	        },
	        items: [{
	        	xtype: 'fieldset',
	        	title: 'Destinataires',
	        	layout: 'fit',
	        	items: [grid],
	        	flex: 1
	        },{
	        	plugins: [Ext.ux.FieldLabeler],
	        	xtype: 'textfield',
	        	fieldLabel: 'Sujet',
	        	style: 'margin-top:5px;margin-bottom:3px;',
	        	anchor: '-15',
	        	id: 'subject'
	        },{
	        	plugins: [Ext.ux.FieldLabeler],
	        	xtype: 'checkbox',
	        	fieldLabel: 'Enregistrer une copie',
	        	id: 'savecopy'
	        },{
	        	xtype: 'textfield',
	        	fieldLabel: 'Commentaire',
	        	hideLabel: true,
	        	anchor: '-15',
	        	flex: 1,
	        	id: 'comment'
	        }]
    	});
    	        
        Ext.fdl.Mailer.superclass.initComponent.apply(this,arguments);
         
    },
    
    onRender: function(){    	
    	Ext.fdl.Mailer.superclass.onRender.apply(this,arguments);    	
    },
    
    sendHandler: function(){
    	
    }
    
});
