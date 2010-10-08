
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Ext.fdl.FamilyProvider
 * @extends Ext.Panel
 * @namespace Ext.fdl.Collection
 * @author Clément Laballe
 * <p>This class represents the grid collection interface.</p>
 */

Ext.fdl.FamilyProvider = Ext.extend(Ext.Panel, {
	    
	context: null,
	
	selection: null,
	
	documentList: null,
	
    constructor: function(config){
    	
    	Ext.apply(this, config);
    	
    	var me = this;
    	
    	if(!this.selection){
    		this.selection = new Fdl.DocumentSelection({
    			selectionItems: this.documentList || []
    		});
    		
    		var rfam = this.loadFamilyArray(this.selection.selectionItems);
    		
    	}
    	
    	console.log('RFAM',rfam);
    	
    	this.store = new Ext.data.JsonStore({
	    	data: rfam || [],
			fields: ['icon','title','_fdldocument']
		});
		
//		var action = new Ext.ux.grid.RowActions({
//			actions:{		
//				//iconIndex:'remove',
//				//qtipIndex:'qtip1',
//				iconCls:'icon-remove'//,
//				//tooltip:'Remove'
//			}
//		});
    	
    	Ext.apply(this,{
    		
    		layout: 'vbox',
    		layoutConfig: {
			    align : 'stretch',
    			pack  : 'start'
			},
			
			border: false,
    	
    		items: [{
    			border: false,
    			layout: 'anchor',
    			//frame: true,
    			bodyStyle: 'padding:5px;',
    			items: [new Ext.fdl.FamilyComboBox({
	    			context: this.context,
	    			allOption: false,
	    			anchor: '0',
	    			familySelect: function(id){
	    				this.reset();
	    				this.collapse();
	    				me.addFamily(id);
	    			}
	    		})]
    		}, new Ext.grid.GridPanel({
    		    border: false,
    			store: this.store,
    			header: false,
        		hideHeaders: true,
        		stripeRows: true,
        		columnLines: true,
        		autoExpandColumn: 'title',      		
    			flex: 1,
    			bodyStyle: 'border-top:0px;',
    			viewConfig: {
    				deferEmptyText: false,
    				emptyText: "No selected family." // FIXME Not working, check why.
    			},
    			plugins: [new Ext.ux.dd.GridDragDropRowOrder(
			    {
			    	listeners: {
			    		afterrowmove: function(target,oldIndex,newIndex){
			    			me.storeChanged(me.store);
			    		}
			    	}
			        //copy: true, // false by default
			        //scrollable: true, // enable scrolling support (default is false)
			        //targetCfg: { } // any properties to apply to the actual DropTarget
			    })
			    //,action
			    ],
    			columns: [{
	        		dataIndex: 'icon',
	        		width: 30,
	        		renderer: function(value, metaData, record, rowIndex, colIndex, store){	               
			        	if (record.get('_fdldocument')) { // There is a problem on sorting when this is not tested, and it is strange.
			        		return String.format('<img src="{0}" style="height:15px;width:15px;" />', record.get('_fdldocument').getIcon({
			        			width: 15
			        		}));			        		
			        	}			        	
			        }
	        	},{
	        		id: 'title',
	        		dataIndex: 'title',
	        		renderer: function(value, metaData, record, rowIndex, colIndex, store){
	        			return Ext.util.Format.capitalize(value);
	        		}
	        	},{
	        		id: 'remove-action',
	        		dataIndex: '_fdldocument',
	        		width: 30,
	        		renderer: function(value, metaData, record, rowIndex, colIndex, store){
	        			return String.format('<img src="{0}" style="height:15px;width:15px;cursor:pointer;" />', me.context.url + 'lib/ui/icon/cross.png');
	        		}
	        	}],
	        	listeners: {
	        		cellclick: function(grid, rowIndex, columnIndex, e) {
					    var record = grid.getStore().getAt(rowIndex);  // Get the Record
					    var fieldName = grid.getColumnModel().getDataIndex(columnIndex); // Get field name
					    var data = record.get(fieldName);
					    if(grid.getColumnModel().getColumnId(columnIndex) == 'remove-action'){
					    	grid.getStore().remove(record);
					    	me.storeChanged(me.store);
					    }
					}
	        	}
    		})]    		
    	});   
                
        Ext.fdl.GridCollection.superclass.constructor.call(this,config);
        
    },
    
    loadFamilyArray: function(sfam){
    	
    	var rfam = [];
			        
	    if (sfam) {			        	
	      	// Create Group Request.
	       	var g = this.context.createGroupRequest();
	       	
	        for (var i = 0; i < sfam.length; i++) {			            
	       	if(sfam[i]){
	         		
		    	var famId = sfam[i];
		          		
		    	var request = {};
		    	request[famId] = g.getDocument({
		    		id: famId
		    	});
		            		
		    	g.addRequest(request);
	            					                
	    	}			                                
	    }
	            
	    var r = g.submit();
	            
	    // Extract families from group request.
	    for (var i = 0; i < sfam.length; i++) {			            
	      	if(sfam[i]){
	           		
	       		var famId = sfam[i];
		        var fam = r.get(famId);	
		        
		        if(fam){
		        
			        rfam.push({
		               icon: fam.getProperty('icon'),
		               title: fam.getTitle(),
		               _fdldocument: fam
		            });
		            
		        }
	                
	          	}			                                
	        }

	    }
	    
	    return rfam ;
    	
    },
    
    addFamily: function(familyId){
    	if(this.selection){
    		if(this.selection.selectionItems.indexOf(familyId) != -1){
    			return;	
    		}
    		this.selection.insertToList({id:familyId});
    	}
    	var family = this.context.getDocument({
    		id: familyId,
    		useCache: true
    	});
	    // Insert family record into store
	    this.store.insert(0,new this.store.recordType({
	    	icon: family.getProperty('icon'),
	    	title: family.getTitle(),
	    	_fdldocument: family
	    }));
	    
	    this.storeChanged(this.store);
	    
    },
    
    removeFamily: function(familyId){
    	// TODO Implement this.
    },
    
    storeChanged: function(store){
    	
    	var me = this ;
    	
    	(function(){
    	me.selection.clearSelection();
    	
    	me.store.each(function(record){
    		me.selection.insertToList({id:record.get('_fdldocument').id});
    	},me);
    	    	
    	me.onChange(me.selection);
    	}).defer(10);
    	
    },
    
    /**
     * This method is supposed to be implemented to give proper behaviour.
     * @param {} selection
     */
    onChange: function(selection){
    	
//    	if(this.applicationParameter){
//    				
//			if (!this.context.setParameter({
//		        id: this.applicationParameter,
//				value: JSON.stringify(this.selection.selectionItems)
//			})) {
//			    Ext.Msg.alert('Error on set families');
//			}
//        		
//    	}
    	
    },    
    	
    toString: function(){
        return 'Ext.fdl.FamilyProvider';
    }
    
});

Ext.reg('familyprovider',Ext.fdl.FamilyProvider);