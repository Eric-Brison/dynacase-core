
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Ext.fdl.FamilyTreePanel
 * @extends Ext.tree.TreePanel
 * @author Clément Laballe
 * <p>This class represents the tree collection interface.</p>
 */
Ext.fdl.FamilyTreePanel = function(config){
    Ext.fdl.FamilyTreePanel.superclass.constructor.call(this, config);
};

Ext.extend(Ext.fdl.FamilyTreePanel, Ext.tree.TreePanel, {

    context: null,
    
    height: 400,
    width: 400,
    
    rootVisible: false,
    lines: false,
    autoScroll: true,
    enableDD: false,
    
    dataConfig: {
		app: 'ONEFAM',
		action: 'ONEFAM_GETTREEFAMILY',
		appid: 'ONEFAM'
	},
	
	getData: function(){
	    
	    console.log('DATACONFIG',this.dataConfig);
		
		var famsearches = this.context.retrieveData(this.dataConfig);
        console.log('FAMSEARCHES',famsearches);
        
        var afamilies = this.getOnefamSearches(famsearches.admin);
        var ufamilies = this.getOnefamSearches(famsearches.user);
        
        var families = afamilies.concat(ufamilies);
        
        return families;
        
	},
	
	reload: function(){
		
		var families = this.getData();
		
		this.root.removeAll();
		this.root.appendChild(families);
		
		this.autoExpand(this.expandedFamilyList);
		
	},
	
	expandedFamilyList: null,
	
	autoExpand: function(familyList){
	    
	    this.root.eachChild(function(node){
	       console.log('NODE',node, node.attributes);
	       if(familyList[node.attributes.documentId]){
	           node.expand();
	       }
	    });
	    
	},
        
    initComponent: function(){
    
    	if(this.familyData){    		
    		var afamilies = this.getOnefamSearches(this.familyData.admin);
        	var ufamilies = this.getOnefamSearches(this.familyData.user);        
        	var families = afamilies.concat(ufamilies);    		
    	} else {
        	var families = this.getData();
    	}
    	
    	console.log('FAMILY DATA',this.familyData);
        
        this.loader = new Ext.tree.TreeLoader();
        
        this.root = new Ext.tree.AsyncTreeNode({
            expanded: true,
            leaf: false,
            text: 'Tree Root',
            children: families
        });
        
        Ext.fdl.FamilyTreePanel.superclass.initComponent.call(this);
        
        this.on({
            render: {
                fn: function(){
                }
            },
            
            afterrender: {
                fn: function(){
                }
            },
            
            show: {
                fn: function(){
                    //console.log('from show');
                }
            }
        
        });
        
        this.expandedFamilyList = {};
        
    },
    
    displayDocument: function(id, mode, title){
        this.publish('opendocument', this, id, mode);
    },
    
    displaySearch: function(filter, title){
        this.publish('opensearch', this, filter);
    },
    
    getOnefamSearches: function(searches){
    
        var me = this;
        
        var families = [];
        var sf = [];
        for (var i = 0; i < searches.length; i++) {
            sf = [];
            if(searches[i]){
            	
                if(searches[i].userSearches){
    	            for (var j = 0 ; j < searches[i].userSearches.length ; j++) {
    	                sf.push({
    	                    text: searches[i].userSearches[j].title,
    	                    icon: this.context.resizeImage(searches[i].userSearches[j].icon, 16),
    	                    documentId: searches[i].userSearches[j].id,
    	                    interfaceId: searches[i].userSearches[j].id,
    	                    leaf: true,
    	                    listeners: {
    	                        click: function(n, e){
    	                            me.displayDocument(n.attributes.documentId,'view',n);
    	                        }
    	                    }
    	                });
    	            }
                }
	            
                if(searches[i].adminSearches){
    	            for (var j = 0 ; j < searches[i].adminSearches.length ; j++) {
    	                sf.push({
    	                    text: searches[i].adminSearches[j].title,
    	                    icon: this.context.resizeImage(searches[i].adminSearches[j].icon, 16),
    	                    documentId: searches[i].adminSearches[j].id,
    	                    interfaceId: searches[i].adminSearches[j].id,
    	                    leaf: true,
    	                    listeners: {
    	                        click: function(n, e){
    	                            me.displayDocument(n.attributes.documentId,'view',n);
    	                        }
    	                    }
    	                });
    	            }
                }
	            
	            for (var j in searches[i].workflow) {
	                sf.push({
	                    text: (searches[i].workflow[j].activity) ? searches[i].workflow[j].activity : searches[i].workflow[j].label,
	                    icon: this.context.resizeImage('Images/workflow.png', 20),
	                    documentState: searches[i].workflow[j].state,
	                    documentId: searches[i].info.id,
	                    interfaceId: searches[i].info.id + '-' + searches[i].workflow[j].state,
	                    documentTitle: searches[i].info.title,
	                    leaf: true,
	                    listeners: {
	                        click: function(n, e){
	                        
	                            me.displaySearch({
	                                family: n.attributes.documentId,
	                                criteria: [{
	                                    operator: '=',
	                                    left: 'state',
	                                    right: n.attributes.documentState
	                                }]
	                            },n);
	                            
	                            //                            Fdl.ApplicationManager.displaySearch('', {
	                            //                                family: n.attributes.documentId,
	                            //                                criteria: [{
	                            //                                    operator: '=',
	                            //                                    left: 'state',
	                            //                                    right: n.attributes.documentState
	                            //                                }]
	                            //                            }, {
	                            //                                windowName: 'worflow' + n.attributes.documentId + n.attributes.documentState,
	                            //                                windowTitle: n.attributes.documentTitle + ' ' + n.text
	                            //                            });
	                        
	                        }
	                    }
	                });
	            }
            
            families.push({
                text: Ext.util.Format.capitalize(searches[i].info.title),
                icon: this.context.resizeImage(searches[i].info.icon, 15),
                documentId: searches[i].info.id,
                interfaceId: searches[i].info.id,
                documentTitle: searches[i].info.title,
                leaf: (sf.length == 0),
                children: sf,
                listeners: {
                    click: function(n, e){
                    
                        me.displaySearch({
                            family: n.attributes.documentId
                        },n);
                        
                        //                        Fdl.ApplicationManager.displaySearch('', {
                        //                            family: n.attributes.documentId
                        //                        }, {
                        //                            windowName: 'family' + n.attributes.documentId,
                        //                            windowTitle: n.attributes.documentTitle
                        //                        });
                    },
                    expand: function(n){
                     
                        me.expandedFamilyList[n.attributes.interfaceId] = true;
                        console.log('EXPANDED FAMILY',me.expandedFamilyList);
                        
                    },
                    collapse: function(n){
                        
                        me.expandedFamilyList[n.attributes.interfaceId] = false;
                        console.log('EXPANDED FAMILY',me.expandedFamilyList);
                        
                    }
                    
                }
            });
            
            }
            
        }
        return families;
    },
    
    toString: function(){
        return 'Ext.fdl.FamilyTreePanel';
    },
    
    displayDocument: function(id, mode, source){
        this.publish('opendocument', this, id, mode);
    }
    
});
