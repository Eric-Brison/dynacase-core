
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * Freedom Widget Ext Library
 * 12/05/2009
 * @author Clément Laballe
 */
Ext.ns('Ext.fdl');

Ext.fdl.DisplayField = Ext.extend(Ext.form.DisplayField, {

    style: 'line-height:22px;margin-bottom:0px;',
    
    initComponent: function(){
        Ext.fdl.DisplayField.superclass.initComponent.call(this);
    },
    
    anchor: '-15'

});

Ext.fdl.Text = Ext.extend(Ext.form.TextField, {
	
	toString: function(){
		return 'Ext.fdl.Text';
	},

    enableKeyEvents: true,
    
    initComponent: function(){
        Ext.fdl.Text.superclass.initComponent.call(this);
    },
    
    anchor: '-15'

});

Ext.fdl.LongText = Ext.extend(Ext.form.TextArea, {

    initComponent: function(){
        Ext.fdl.LongText.superclass.initComponent.call(this);
    },
    
    anchor: '-15'

});

Ext.fdl.HtmlText = Ext.extend(Ext.form.HtmlEditor, {

    width: 524,
    height: 150,
    
    initComponent: function(){
        Ext.fdl.HtmlText.superclass.initComponent.call(this);
    }
    
});

Ext.fdl.Integer = Ext.extend(Ext.form.NumberField, {

    initComponent: function(){
        Ext.fdl.Integer.superclass.initComponent.call(this);
    },
    
    anchor: '-15'

});

Ext.fdl.Double = Ext.extend(Ext.form.NumberField, {

    initComponent: function(){
        Ext.fdl.Double.superclass.initComponent.call(this);
    },
    
    anchor: '-15'

});

Ext.fdl.Money = Ext.extend(Ext.form.NumberField, {

    initComponent: function(){
        Ext.fdl.Money.superclass.initComponent.call(this);
    },
    
    anchor: '-15'

});

Ext.fdl.Date = Ext.extend(Ext.form.DateField, {

	toString: function(){
		return 'Ext.fdl.Date';
	},

    initComponent: function(){
        Ext.fdl.Date.superclass.initComponent.call(this);
    },
    
    altFormats: 'd-j-Y|d-m-Y',
    format: 'd/m/Y',
    
    anchor: '-15'

});

Ext.fdl.Image = Ext.extend(Ext.form.FileUploadField, {

    width: 200,
    
    buttonText: '',
    buttonCfg: {
        iconCls: 'upload-icon'
    },
    
    initComponent: function(){
        Ext.fdl.Image.superclass.initComponent.call(this);
    }
    
});

Ext.fdl.File = Ext.extend(Ext.form.FileUploadField, {

    width: 200,
    
    buttonText: '',
    buttonCfg: {
        iconCls: 'upload-icon'
    },
    
    initComponent: function(){
        Ext.fdl.File.superclass.initComponent.call(this);
    }
    
});

Ext.fdl.Color = Ext.extend(Ext.form.ColorField, {

    initComponent: function(){
        Ext.fdl.Color.superclass.initComponent.call(this);
    },
    
    anchor: '-15'

});

Ext.fdl.Enum = Ext.extend(Ext.form.ComboBox, {

    attribute: null,
    
    editable: false,
    forceSelection: true,
    disableKeyFilter: true,
    triggerAction: 'all',
    mode: 'local',
    
    initComponent: function(){
    
		var enumItems = this.attribute.getEnumItems();
		
		console.log('ENUM',enumItems);
	
        this.store = new Ext.data.JsonStore({
            data: enumItems,
            fields: ['key', 'label']
        });
        
        Ext.fdl.Enum.superclass.initComponent.call(this);
        
    },
    
    valueField: 'key',
    displayField: 'label'

});

Ext.fdl.DocId = Ext.extend(Ext.form.ComboBox, {

    attribute: null,
    
    lastkey: '',
    
    initComponent: function(){
        Ext.fdl.DocId.superclass.initComponent.call(this);
        
        this.on({
            render: {
                scope: this,
                fn: function(){
                
                }
            },
            select: {
                fn: function(combo, record, index){
                    combo.documentSelect(record.id);
                }
            },
            keypress: {
                fn: function(t){
                
                    // Keypress is fired before actual value in browser is modified
                    // This is not related to ExtJS but to browser behaviour
                    // http://extjs.com/forum/showthread.php?t=50189
                    
                    (function(){
                        if (t.getRawValue() != this.lastkey) {
                        
                            console.log('PROPOSAL', t.attribute, t.getRawValue());
                            console.log('RETRIEVE', t.attribute.retrieveProposal({
                                key: t.getRawValue()
                            }));
                            
                            var proposal = t.attribute.retrieveProposal({
                                key: t.getRawValue()
                            });
                            //console.log('new:' + t.getRawValue() + ',last:' + this.lastkey);
                            this.lastkey = t.getRawValue();
                            
                            t.getStore().removeAll();
                            t.getStore().loadData(proposal);
                        }
                        
                    }).defer(100);
                },
                buffer: 500
            }
        
        });
        
    },
    
    anyMatch: true, // not ComboBox-native, see override
    mode: 'local',
    enableKeyEvents: true,
    
    // FIXME Instanciation here create a singleton (all store for Ext.fdl.DocId will be the same) 
    store: new Ext.data.JsonStore({
        data: new Array(),
        fields: ['id', 'display']
    }),
    
    valueField: 'id',
    displayField: 'display',
    //    width: 180,
    //    minListWidth: 200,
    
    anchor: '-15',
    
    // Called when a document is selected in the drop list.
    // To be overriden for specific behaviors.
    documentSelect: function(id){
        //console.log('Document Selected', id);
    }
    
});

/**
 * ExtJS Component for Multiple Family Selector
 */
Ext.fdl.MultiDocId = Ext.extend(Ext.fdl.DocId, {

    attribute: null,
    
    emptyText: '',
    
    docIdList: null,
    
    docTitleList: null,
    
    toString: function(){
        return 'Ext.fdl.MultiDocId';
    },
    
    //emptyField: null,
    
    initComponent: function(){
    
        Ext.fdl.MultiDocId.superclass.initComponent.call(this);
        
        this.hiddenName = this.attribute.id + '[]';
        
        this.on({
            render: {
                fn: function(t){
                
                    //                    if (!t.emptyField) {
                    //						
                    //						console.log('Hidden empty is inserted');
                    //						
                    //                        // Hidden field with empty value
                    //                        // Used so data can receive when no docid is given
                    //                        t.emptyField = new Ext.form.Field({
                    //                            name: this.attribute.id + '[]',
                    //                            //hidden: true,
                    //                            value: ''
                    //                        });
                    //                        
                    //                        t.ownerCt.insert(0, t.emptyField);
                    //                        
                    //                    }
                    
                    for (var i = 0; i < this.docIdList.length; i++) {
                        if (this.docIdList[i] != '') {
                        
                            var index = this.ownerCt.items.indexOf(this);
                            
                            var clearDocId = this.getDocIdComboBox();
                            
                            this.ownerCt.insert(index + 1, clearDocId);
                            
                            //this.ownerCt.doLayout();
                            
                            //                            this.setValue(null);
                            
                            var data = [{
                                id: this.docIdList[i],
                                display: this.docTitleList[i]
                            }];
                            
                            t.getStore().loadData(data, true); // Complete store with appropriate record to trigger proper recognition of display in combobox
                            clearDocId.setValue(this.docIdList[i]);
                            
                        }
                    }
                    
                }
            },
            select: {
                fn: function(combo, record, index){
                    (function(){
                        combo.getEl().blur();
                    }).defer(100);
                }
            }
        
        });
        
    },
    
    getDocIdComboBox: function(){
    
        var mfcb = this;
        
        var clearDocId = new Ext.fdl.DocId({
            //value: id,
            attribute: this.attribute,
            triggerClass: 'x-form-clear-trigger',
            store: this.store,
            // Special override since method is supposed private by ExtJS
            onTriggerClick: function(){
                this.ownerCt.setHeight(this.ownerCt.getHeight() - 26);
                this.ownerCt.remove(this);
                mfcb.documentClear(this.getValue());
            },
            hiddenName: this.attribute.id + '[]',
            width: this.getWidth() // Should not be necessary with default anchoring but width is not correct if omitted...
        });
        
        return clearDocId;
        
    },
    
    documentSelect: function(id){
    
        var index = this.ownerCt.items.indexOf(this);
        
        var clearDocId = this.getDocIdComboBox();
        
        this.ownerCt.insert(index + 1, clearDocId);
        
        // TODO Check if doLayout and setValue are necessary.
        
        this.ownerCt.doLayout();
        
        this.setValue(null);
        
        clearDocId.setValue(id);
        
        this.ownerCt.setHeight(this.ownerCt.getHeight() + 26);
        
    },
    
    // To be overriden.
    documentClear: function(id){
    
    }
    
    
});
