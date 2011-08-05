
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

Ext.fdl.DSearch = Ext.extend(Ext.Panel, {

    document: null,
    
    // Icon Paths
    iconAdd: 'ECM/Images/add.png',
    iconDelete: 'ECM/Images/delete.png',
    
    layout: 'form',
    bodyStyle: 'padding:5px;',
    
    filter: null,
    
    initComponent: function(){
	
        efd = this;
        
        Ext.Panel.superclass.initComponent.call(this);
        
        var fcb = new Ext.fdl.FamilyComboBox({
            fieldLabel: 'Famille',
            filter: this.filter,
            hiddenName: 'se_famid',
            familySelect: function(id){
				
				console.log('FAMILY SELECT', efd, efd.document);
            
                var family = efd.document.context.getDocument({
                    id: id
                });
                
                for (var i = 0; i < acbArray.length; i++) {
                    acbArray[i].setFamily(family);
                }
            }
        });
        
        var acbArray = [];
        
        var arrayPanel = new Ext.Panel({
            border: false,
            items: [{
                layout: 'hbox',
                layoutConfig: {
                    pack: 'start'
                },
                border: false,
                height: 30,
                items: [new Ext.Button({
                    icon: this.iconAdd,
                    width: 30,
                    margins: {
                        top: 0,
                        right: 2,
                        bottom: 2,
                        left: 0
                    },
                    handler: function(b, e){
                    
                        var acb = new Ext.fdl.AttributeComboBox({
                            flex: 2,
                            margins: {
                                top: 0,
                                right: 2,
                                bottom: 4,
                                left: 2
                            },
                            attributeSelect: function(id, type){
                                acb.ocb.setOperatorList(type);
                            },
                            hiddenName: 'se_attrids[]'
                        });
                        
                        if (fcb.value) {
                            acb.setFamilyId(fcb.value);
                        }
                        
                        acbArray.push(acb);
                        
                        acb.ocb = new Ext.fdl.OperatorComboBox({
                            flex: 1,
                            margins: {
                                top: 0,
                                right: 2,
                                bottom: 4,
                                left: 2
                            },
                            hiddenName: 'se_funcs[]'
                        });
                        
                        var newRow = new Ext.Panel({
                            layout: 'hbox',
                            layoutConfig: {
                                pack: 'start'
                            },
                            autoHeight: true,
                            border: false,
                            items: [new Ext.Button({
                                icon: efd.iconDelete,
                                width: 30,
                                margins: {
                                    top: 0,
                                    right: 2,
                                    bottom: 4,
                                    left: 0
                                },
                                handler: function(b, e){
                                    newRow.ownerCt.remove(newRow);
                                }
                            }), acb, acb.ocb, new Ext.fdl.Text({
                                flex: 1,
                                margins: {
                                    top: 0,
                                    right: 0,
                                    bottom: 4,
                                    left: 2
                                },
                                name: 'se_keys[]'
                            })]
                        });
                        
                        arrayPanel.add(newRow);
                        
                        arrayPanel.doLayout();
                    }
                    
                }), {
                    title: 'Attributs',
                    margins: {
                        top: 0,
                        right: 2,
                        bottom: 2,
                        left: 2
                    },
                    flex: 2,
                    bodyStyle: 'border:0px;'
                }, {
                    title: 'Fonctions',
                    margins: {
                        top: 0,
                        right: 2,
                        bottom: 2,
                        left: 2
                    },
                    flex: 1,
                    bodyStyle: 'border:0px;'
                }, {
                    title: 'Mot-clefs',
                    margins: {
                        top: 0,
                        right: 0,
                        bottom: 2,
                        left: 2
                    },
                    flex: 1,
                    bodyStyle: 'border:0px;'
                }]
            }]
        });
        
		console.log('DOCUMENT',this.document);
		
        if (this.document.getValue('se_famid')) {
            fcb.setValue(this.document.getValue('se_famid'));
            fcb.familySelect(this.document.getValue('se_famid'));
            var family = this.document.context.getDocument({
                id: this.document.getValue('se_famid')
            });
            
        }
        
        if (this.document.getValue('se_attrids')) {
        
            var a = this.document.getValue('se_attrids');
            var l = a.length;
            
            for (var i = 0; i < l; i++) {
            
                var acb = new Ext.fdl.AttributeComboBox({
                    flex: 2,
                    margins: {
                        top: 0,
                        right: 2,
                        bottom: 4,
                        left: 2
                    },
                    attributeSelect: function(id, type){
                        acb.ocb.setOperatorList(type);
                    },
                    hiddenName: 'se_attrids[]'
                });
                
                acb.setFamily(family);
                acb.setValue(this.document.getValue('se_attrids')[i]);
                
                acbArray.push(acb);
                
                acb.ocb = new Ext.fdl.OperatorComboBox({
                    flex: 1,
                    margins: {
                        top: 0,
                        right: 2,
                        bottom: 4,
                        left: 2
                    },
                    hiddenName: 'se_funcs[]',
                    attribute: family.getAttribute(this.document.getValue('se_attrids')[i])
                });
                
                if (this.document.getValue('se_funcs')[i]) {
                    acb.ocb.setValue(this.document.getValue('se_funcs')[i]);
                }
                
                acb.ocb.key = new Ext.fdl.Text({
                    flex: 1,
                    margins: {
                        top: 0,
                        right: 0,
                        bottom: 4,
                        left: 2
                    },
                    name: 'se_keys[]'
                });
                
                if (this.document.getValue('se_keys')[i]) {
                    acb.ocb.key.setValue(this.document.getValue('se_keys')[i]);
                }
                
                var newRow = new Ext.Panel({
                    layout: 'hbox',
                    layoutConfig: {
                        pack: 'start'
                    },
                    autoHeight: true,
                    border: false,
                    items: [new Ext.Button({
                        icon: efd.iconDelete,
                        width: 30,
                        margins: {
                            top: 0,
                            right: 2,
                            bottom: 4,
                            left: 0
                        },
                        handler: function(b, e){
                            newRow.ownerCt.remove(newRow);
                        }
                    }), acb, acb.ocb, acb.ocb.key]
                });
                
                arrayPanel.add(newRow);
                
            }
            
            arrayPanel.doLayout();
            
        }
        
        this.add(fcb);
        this.add(arrayPanel);
        
    }
    
});
