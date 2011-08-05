
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

// Remove emptyText from fields
// http://extjs.com/forum/showthread.php?t=66409
//Ext.override(Ext.form.Field, {
//    initValue: function(){
//        if (this.value !== undefined) {
//            this.setValue(this.value);
//        }
//        else 
//            if (!Ext.isEmpty(this.el.dom.value) /*&& this.el.dom.value != this.emptyText*/) {
//                this.setValue(this.el.dom.value);
//            }
//        this.originalValue = this.getValue();
//    },
//    getRawValue: function(){
//        var v = this.rendered ? this.el.getValue() : Ext.value(this.value, '');
//        /*if(v === this.emptyText){
//         v = '';
//         }*/
//        return v;
//    },
//    getValue: function(){
//        if (!this.rendered) {
//            return this.value;
//        }
//        var v = this.el.getValue();
//        if (/*v === this.emptyText ||*/v === undefined) {
//            v = '';
//        }
//        return v;
//    }
//});
//Ext.override(Ext.form.TextField, {
//    applyEmptyText: function(){
//        if (this.rendered && this.emptyText && this.getRawValue().length < 1 && !this.hasFocus) {
//            /*this.setRawValue(this.emptyText);*/
//            this.el.addClass(this.emptyClass);
//            var el = this.emptyTextEl;
//            if (!el) {
//                el = this.emptyTextEl = this.el.insertSibling({
//                    cls: 'x-field-empty-text',
//                    unselectable: 'on',
//                    cn: this.emptyText
//                }, 'after');
//                el.setVisibilityMode(Ext.Element.VISIBILITY);
//                el.on('click', this.emptyTextClick, this);
//            }
//            el.setSize(this.el.getSize());
//            el.alignTo(this.el, 'tl');
//            el.show();
//        }
//    },
//    preFocus: function(){
//        if (this.emptyText) {
//            /*if(this.el.dom.value == this.emptyText){
//             this.setRawValue('');
//             }*/
//            this.el.removeClass(this.emptyClass);
//            if (this.emptyTextEl) {
//                this.emptyTextEl.hide();
//            }
//        }
//        if (this.selectOnFocus) {
//            this.el.dom.select();
//        }
//    },
//    setValue: function(v){
//        if (this.emptyText && this.el && v !== undefined && v !== null && v !== '') {
//            this.el.removeClass(this.emptyClass);
//            if (this.emptyTextEl) {
//                this.emptyTextEl.hide();
//            }
//        }
//        Ext.form.TextField.superclass.setValue.apply(this, arguments);
//        this.applyEmptyText();
//        this.autoSize();
//        return this;
//    },
//    onDestroy: function(){
//        if (this.validationTask) {
//            this.validationTask.cancel();
//            this.validationTask = null;
//        }
//        Ext.destroy(this.emptyTextEl);
//        Ext.form.TextField.superclass.onDestroy.call(this);
//    },
//    emptyTextClick: function(e){
//        this.focus(false, 1);
//    }
//});
//Ext.override(Ext.form.TriggerField, {
//    emptyTextClick: function(e){
//        Ext.form.TriggerField.superclass.emptyTextClick.apply(this, arguments);
//        if (!this.editable && this.onTriggerClick) {
//            this.onTriggerClick();
//        }
//    }
//});

// Override to fit listWidth of combobox
// http://extjs.net/forum/showthread.php?p=421739
Ext.override(Ext.form.ComboBox, {
    initList: function(){
        if (!this.list) {
            var cls = 'x-combo-list';
            
            this.list = new Ext.Layer({
                parentEl: this.getListParent(),
                shadow: this.shadow,
                cls: [cls, this.listClass].join(' '),
                constrain: false
            });
            
            
            
            this.innerList = this.list.createChild({
                cls: cls + '-inner'
            });
            this.mon(this.innerList, 'mouseover', this.onViewOver, this);
            this.mon(this.innerList, 'mousemove', this.onViewMove, this);
            
            if (this.pageSize) {
                this.footer = this.list.createChild({
                    cls: cls + '-ft'
                });
                this.pageTb = new Ext.PagingToolbar({
                    store: this.store,
                    pageSize: this.pageSize,
                    renderTo: this.footer
                });
                this.assetHeight += this.footer.getHeight();
            }
            
            if (!this.tpl) {
                this.tpl = '<tpl for="."><div class="' + cls + '-item">{' + this.displayField + '}</div></tpl>';
            }
            this.view = new Ext.DataView({
                applyTo: this.innerList,
                tpl: this.tpl,
                singleSelect: true,
                selectedClass: this.selectedClass,
                itemSelector: this.itemSelector || '.' + cls + '-item',
                emptyText: this.listEmptyText
            });
            
            this.mon(this.view, 'click', this.onViewClick, this);
            
            //Only after the store is loaded will we know the real width of items in the drop down list
            this.bindStore(this.store, true);
            
            var lw;
            if (Ext.isDefined(this.resizable) || this.listWidth === 'auto') {
                var rect = 0;
                if (Ext.isIE) {
                    //list-auto-width-IE class contains one line --> display:inline;
                    //This will allow us to get the actual width of innerList div
                    this.innerList.addClass('list-auto-width-IE');
                    rect = this.list.getBox();
                    //Remove this class once we got the width
                    this.innerList.removeClass('list-auto-width-IE');
                }
                else {
                    rect = this.list.getBox();
                }
                lw = Math.max(this.wrap.getWidth(), rect.width);
                
            }
            else 
                if (this.listWidth) {
                    lw = this.listWidth || Math.max(this.wrap.getWidth(), this.minListWidth);
                    
                }
            
            // Clement
            // To handle scrollbar width
//            lw = lw + 20;
//            
//            this.list.setSize(lw, 0);
            // EO Clement
            
            this.list.swallowEvent('mousewheel');
            this.assetHeight = 0;
            if (this.syncFont !== false) {
                this.list.setStyle('font-size', this.el.getStyle('font-size'));
            }
            if (this.title) {
                this.header = this.list.createChild({
                    cls: cls + '-hd',
                    html: this.title
                });
                this.assetHeight += this.header.getHeight();
            }
            this.innerList.setWidth(lw - this.list.getFrameWidth('lr'));
            
            if (this.resizable) {
                this.resizer = new Ext.Resizable(this.list, {
                    pinned: true,
                    handles: 'se'
                });
                this.mon(this.resizer, 'resize', function(r, w, h){
                    this.maxHeight = h - this.handleHeight - this.list.getFrameWidth('tb') - this.assetHeight;
                    this.listWidth = w;
                    this.innerList.setWidth(w - this.list.getFrameWidth('lr'));
                    this.restrictHeight();
                }, this);
                
                this[this.pageSize ? 'footer' : 'innerList'].setStyle('margin-bottom', this.handleHeight + 'px');
            }
        }
    }
});




/**
 * @class Ext.fdl.FamilyComboBox
 * @extends Ext.form.ComboBox
 * <p>This component is a combobox for freedom family selection. It is designed to search for available freedom families with a Fdl.SearchDocument object.</p>
 */
Ext.fdl.FamilyComboBox = Ext.extend(Ext.form.ComboBox, {

    valueField: 'id',
    displayField: 'title',
    
    context: null,
    
    /**
     * If this is set to false, no option to select all family will appear in the combobox (defaults to true).
     */
    allOption: true,
    
    /**
     * Specify a privilege current user must have for displayed families in the combobox
     */
    control: false,
    
    //width: 180,
    
    // Required to give simple select behaviour
    emptyText: '--Choisir Famille--',
    //editable: false,
    forceSelection: true,
    disableKeyFilter: true,
    triggerAction: 'all',
    mode: 'local',
    
    /**
     * @cfg {String} filter Filtering expression to restrict family search on server. Defaults to null.
     */
    filter: null,
    
    tpl: '<tpl for="."><div style="background-image:url(\'{icon}\');padding-left:20px;background-repeat:no-repeat;" class="x-combo-list-item" >{title}</div></tpl>',
    
    toString: function(){
        return 'Ext.fdl.FamilyComboBox';
    },
    
    initComponent: function(){
    
        Ext.fdl.FamilyComboBox.superclass.initComponent.call(this);
        
        if (!this.store) {
        
            //			////////////
            //			
            //			console.log('Restricted Family ?', this, this.restrictedFamily);
            //        
            //			if (this.restrictedFamily && this.restrictedFamily.getProperty('dfldid')) {
            //				var folderId = this.restrictedFamily.getProperty('dfldid');
            //
            //				var s = new Fdl.Collection({
            //					id: folderId
            //				});
            //				var sr = s.getAuthorizedFamilies();
            //				
            //				console.log('Folder Id', folderId, sr);
            //				
            //			////////////	
            //				
            //			}
            //			else {
            
            // No more available without context
            //			var s = new Fdl.SearchDocument();
            //			if (this.filter) {
            //				var sr = s.getFamilies({
            //					filter: this.filter
            //				});
            //			} else {
            //				var sr = s.getFamilies();
            //			}
            //
            
        	var s = new Fdl.SearchDocument({
        		context: this.context
        	});
        	
        	var sr = s.getFamilies({
        		filter: new Fdl.DocumentFilter({
        			criteria : [ {
        		  		or : [{
        		  			operator : '=',
        		  			left : 'usefor',
        		  			right : 'N'
        		  		}, {
        		  			operator : '=',
        		  			left : 'usefor',
        		  			right : 'F'
        		  		}]
        		  	}]
        		})
        	}).getDocuments();
        	        	
//            var s = this.context.getDocument({
//                id: 11,
//                useCache: true
//            });
//            var dl=s.getContent({slice:'ALL'});
//            var sr = dl.getDocuments();
            
            //			}
            
            var data = [];
            
            if (this.allOption) {
                data.push({
                    id: '_allfam',
                    title: 'Toutes les familles'
                });
            }
            
            for (var i = 0; i < sr.length; i++) {
            	var family = sr[i];
	            if(!this.control || (this.control && family.control(this.control))){
		            data.push({
		                id: family.getProperty('id'),
		                title: Ext.util.Format.capitalize(family.getTitle()),
		                icon: family.getIcon({
		                    width: 18
		                }),
		                _fdldoc: family
		            });
	            }
            }
            
            this.store = new Ext.data.JsonStore({
                data: data,
                fields: ['id', 'title', 'icon', '_fdldoc']
            });
            
        }
        
        this.on({
            render: {
                scope: this,
                fn: function(){
                
                }
            },
            select: {
                fn: function(combo, record, index){
                    this.familySelect(record.id);
                }
            }
        
        });
        
    },
    
    /**
     * @method familySelect
     * This method is called when a family is selected. No default implementation is provided.
     * @param {Object} id
     */
    // Override this method to give behavior on family selection
    familySelect: function(id){
        //console.log('Family Id ', id);
    },
    
    setIcon: function(){
        var fcb = this;
        var rec = this.store.queryBy(function(rec, id){
            return (rec.data[this.valueField] == this.getValue()); // || rec.data[this.displayField] == this.getValue()
        }, fcb).itemAt(0);
        if (rec) {
            if (this.getEl()) {
                this.getEl().applyStyles({
                    'padding-left': '20px', // FIXME This seems to break sizing.
                    'background-image': 'url(' + rec.data.icon + ')',
                    'background-repeat': 'no-repeat'
                });
            }
        }
        else {
            if (this.getEl()) {
                this.getEl().applyStyles({
                    'padding-left': '0px',
                    'background-image': ''
                });
            }
        }
    },
    
    /**
     * setValue
     * Set selected family by id.
     * @param {String} id
     */
    setValue: function(value){
        Ext.fdl.FamilyComboBox.superclass.setValue.call(this, value);
        this.setIcon();
    }
    
});

Ext.reg('familycombobox',Ext.fdl.FamilyComboBox);

/**
 * @class Ext.fdl.MultiFamilyComboBox
 * ExtJS Component for Multiple Family Selector
 */
Ext.fdl.MultiFamilyComboBox = Ext.extend(Ext.fdl.FamilyComboBox, {

    emptyText: '--Ajouter Famille--',
    
    familyList: null,
    
    toString: function(){
        return 'Ext.fdl.MultiFamilyComboBox';
    },
    
    initComponent: function(){
    
        Ext.fdl.MultiFamilyComboBox.superclass.initComponent.call(this);
        
        this.on({
            render: {
                fn: function(){
                
                    for (var i = 0; i < this.familyList.length; i++) {
                        if (this.familyList[i] != '') {
                        
                            var index = this.ownerCt.items.indexOf(this);
                            
                            var clearFamilyComboBox = this.getFamilyComboBox();
                            
                            this.ownerCt.insert(index + 1, clearFamilyComboBox);
                            
                            //this.ownerCt.doLayout();
                            
                            //                            this.setValue(null);
                            
                            clearFamilyComboBox.setValue(this.familyList[i]);
                            
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
    
    getFamilyComboBox: function(){
    
        var mfcb = this;
        
        var clearFamilyComboBox = new Ext.fdl.FamilyComboBox({
            //value: id,
            triggerClass: 'x-form-clear-trigger',
            labelWidth: 200,
            store: this.store,
            // Special override since method is supposed private by ExtJS
            onTriggerClick: function(){
                this.ownerCt.setHeight(this.ownerCt.getHeight() - 26);
                this.ownerCt.remove(this);
                mfcb.familyClear(this.getValue());
            },
            hiddenName: 'se_famid[]'
        });
        
        return clearFamilyComboBox;
        
    },
    
    familySelect: function(id){
    
        var index = this.ownerCt.items.indexOf(this);
        
        var clearFamilyComboBox = this.getFamilyComboBox();
        
        this.ownerCt.insert(index + 1, clearFamilyComboBox);
        
        // TODO Check if doLayout and setValue are necessary.
        
        this.ownerCt.doLayout();
        
        this.setValue(null);
        
        clearFamilyComboBox.setValue(id);
        
        this.ownerCt.setHeight(this.ownerCt.getHeight() + 26);
        
    },
    
    // To be overriden.
    familyClear: function(id){
    
    }
    
});

/**
 * @class Ext.fdl.AttributeComboBox
 * ExtJS Component for Family Attributes Selection
 */
Ext.fdl.AttributeComboBox = Ext.extend(Ext.form.ComboBox, {

    context: null,
    
    valueField: 'id',
    displayField: 'label',
    
    //    width: 180,
    //    listWidth: 196,
    
    // Required to give simple select behaviour
    //emptyText: '--Attribut--',
    //editable: false,
    forceSelection: true,
    disableKeyFilter: true,
    triggerAction: 'all',
    mode: 'local',
    
    familyId: null,
    
    tpl: '<tpl for="."><tpl if="property == true"><div class="x-combo-list-item" style="font-style: italic;" >{label}</div></tpl><tpl if="attribute == true"><div class="x-combo-list-item" >{label}</div></tpl></tpl>',
    
    toString: function(){
        return 'Ext.fdl.AttributeComboBox';
    },
    
    initComponent: function(){
    
        Ext.fdl.AttributeComboBox.superclass.initComponent.call(this);
        
        if (this.familyId) {
            this.setFamilyId(this.familyId);
        }
        else {
            this.disable();
        }
        
        this.on({
            render: {
                scope: this,
                fn: function(){
                }
            },
            select: {
                scope: this,
                fn: function(combobox, record, index){
                    if (record.json.attribute) {
                        this.attributeSelect(record.json.object, record.json.family);
                    }
                    if (record.json.property) {
                        this.propertySelect(record.json.id, record.json.family);
                    }
                }
            }
        
        });
        
    },
    

    setFamily: function(family){
		
		console.log('SET FAMILY',family);
            
        var data = [];
        
        var fp = this.context.getFilterableProperties();
        
        for (var i = 0; i < fp.length; i++) {
            data.push({
                id: fp[i],
                label: this.context.getPropertyInformation(fp[i]).label,
                property: true,
                family: family
            });
        }
        
        if (family) {
            
            var fa = family.getFilterableAttributes();
			
			console.log('FILTERABLE ATTRIBUTES',fa);
            
            for (var i = 0; i < fa.length; i++) {
                data.push({
                    id: fa[i].id,
                    label: fa[i].getLabel(),
                    attribute: true,
                    object: fa[i],
                    family: family
                });
            }
        }
        
        if (!this.store) {
            this.store = new Ext.data.JsonStore({
                data: data,
                fields: ['id', 'label', 'type', 'property', 'attribute']
            });
        }
        else {
            this.getStore().loadData(data);
        }
        
        this.enable();
        
    },
    
    setFamilyId: function(id){
    
        this.familyId = id;
        
        var family = this.context.getDocument({
            id: this.familyId,
            useCache: true
        });
        
        this.setFamily(family);
        
    },
    
    // To override with appropriate behaviour
    attributeSelect: function(attributeObj, family){
    
    },
    
    // To override with appropriate behaviour
    propertySelect: function(propertyId, family){
    
    }
    
});


/**
 * @class Ext.fdl.OperatorComboBox
 * ExtJS Component for Operators
 */
Ext.fdl.OperatorComboBox = Ext.extend(Ext.form.ComboBox, {

    valueField: 'value',
    displayField: 'display',
    
    //editable: false,
    forceSelection: true,
    disableKeyFilter: true,
    triggerAction: 'all',
    mode: 'local',
    
    initComponent: function(){
    
        Ext.fdl.OperatorComboBox.superclass.initComponent.call(this);
        
        if (this.attribute) {
            this.setAttributeOperatorList(this.attribute, this.family);
        }
        else {
            if (this.property) {
                this.setPropertyOperatorList(this.property, this.family);
            }
            else {
                this.disable();
            }
        }
        
        this.on({
            select: {
                scope: this,
                fn: function(combobox, record, index){
                    this.operatorSelect(record.data.value);
                }
            }
        
        });
        
    },
    
    setAttributeOperatorList: function(attribute, family){
    
        var type = attribute.type;
        var searchCriteria = family.getSearchCriteria()[type];
        
        //console.log('SearchCriteria', type, searchCriteria, family.getSearchCriteria());
        
        if (!this.store) {
            this.store = new Ext.data.ArrayStore({
                data: searchCriteria,
                fields: [{
                    name: 'display',
                    mapping: 'label'
                }, {
                    name: 'value',
                    mapping: 'operator'
                }]
            });
        }
        else {
            this.getStore().loadData(searchCriteria);
        }
        
        this.enable();
        this.setValue();
        
        this.attribute = attribute;
        this.property = null;
        
    },
    
    setPropertyOperatorList: function(property, family){
    
        var propertyInfo = family.getPropertyInformation(property);
        var type = propertyInfo.type;
        
        var searchCriteria = family.getSearchCriteria()[type];
        
        //console.log('SETPROPERTYOPERATOR',property,family,type,searchCriteria);
        
        if (!this.store) {
            this.store = new Ext.data.ArrayStore({
                data: searchCriteria,
                fields: [{
                    name: 'display',
                    mapping: 'label',
                    convert: function(v, record){
                        // Perhaps the server should send these values already decoded ?
                        return Ext.util.Format.htmlDecode(record.label);
                    }
                }, {
                    name: 'value',
                    mapping: 'operator'
                }]
            });
        }
        else {
            this.getStore().loadData(searchCriteria);
        }
        
        this.enable();
        this.setValue();
        
        this.attribute = null;
        this.property = property;
        
    },
    
    setDefaultValue: function(){
        console.log('THIS', this);
        var firstRecord = this.store.getAt(0);
        if (firstRecord) {
            this.setValue(firstRecord.json.operator);
        }
    },
    
    // To override with appropriate behavior
    operatorSelect: function(operator){
        //console.log('Operator selected',operator);
    }
    
});

/**
 * @class Ext.fdl.StateComboBox
 * ExtJS Component for States
 */
Ext.fdl.StateComboBox = Ext.extend(Ext.form.ComboBox, {

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
