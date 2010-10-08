
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Ext.fdl.FamilyPanel
 * @namespace Ext.fdl
 */
Ext.fdl.FamilyPanel = Ext.extend(Ext.Panel, {

    document: null,
    
    familyId: null,
    
    // Icon Paths
    //iconAdd: 'lib/ui/icon/add.png',
    iconDelete: 'lib/ui/icon/delete.png',
    
    // Display close tool action
    closeTool: true,
    
    layout: 'form',
    bodyStyle: 'padding:5px;',
    
    filter: null,
    
    title: null,
    
    criteriaArray: null,
    
    // Default value when a new criteria is created
    defaultLeft: 'svalues',
    defaultOperator: '~*',
    
    getOperand: function(left, operator){
    
        var attribute;
        
        if (this.family) {
            attribute = this.family.getAttribute(left);
        }
        
        if (attribute) {
            var type = attribute.type;
        }
        else {
            var info = this.document.getPropertyInformation(left);
            var type = info.type;
        }
        
        var tCriteria = this.document.getSearchCriteria()[type];
        
        for (var i = 0; i < tCriteria.length; i++) {
            if (operator && tCriteria[i].operator == operator) {
                return tCriteria[i].operand;
            }
        }
        
        return [];
        
    },
    
    cleanCriteria: function(criteria){
    
        if (criteria.left && criteria.operator) {
            var operand = this.getOperand(criteria.left, criteria.operator);
            for (var j = 1; j < operand.length; j++) {
                delete criteria[operand[j]];
            }
        }
        
        return criteria;
        
    },
    
    getCriteria: function(){
    
        var validCriteriaArray = [];
        
        var foundLP = false; //To remember that a left parenthesis was found and still no right parenthesis, used to check parenthesis constintency
        for (var i = 0; i < this.criteriaArray.length; i++) {
        
            // Get operators info in case of attribute and property to check that all operand are filled to make a valid criteria
            
            var attribute;
            if (this.family) {
                attribute = this.family.getAttribute(this.criteriaArray[i].left);
            }
            
            var operand = [];
            
            if (this.criteriaArray[i].left) {
                if (attribute) {
                    var operatorsInfo = this.family.getSearchCriteria()[attribute.type];
                }
                else {
                    var info = this.document.getPropertyInformation(this.criteriaArray[i].left);
                    var operatorsInfo = this.document.getSearchCriteria()[info.type];
                }
                
                var operatorInfo;
                for (var j = 0; j < operatorsInfo.length; j++) {
                    if (operatorsInfo[j].operator == this.criteriaArray[i].operator) {
                        operatorInfo = operatorsInfo[j];
                        operand = operatorInfo.operand;
                    }
                }
                
            }
            
            if (this.criteriaArray[i].lp) {
                if (foundLP == true) {
                    console.log('Opening parenthesis without closing parenthesis has been found');
                    return false;
                }
                foundLP = true;
            }
            
            if (this.criteriaArray[i].rp) {
                if (foundLP == false) {
                    console.log('Closing parenthesis without opening parenthesis has been found');
                    return false;
                }
                foundLP = false;
            }
            
            var valid = true;
            if (!this.criteriaArray[i].operator) {
                valid = false;
            }
            else {
                for (var j = 0; j < operand.length; j++) {
                    if (!this.criteriaArray[i][operand[j]]) {
                        valid = false;
                    }
                }
            }
            if (valid) {
                validCriteriaArray.push(this.criteriaArray[i]);
            }
            
        }
        
        if (foundLP == true) {
            console.log('Opening parenthesis without closing parenthesis has been found');
            return false;
        }
        
        return validCriteriaArray;
        
    },
    
    // useDefault defaults to false and if true, only used if no criteria is provided
    addCriteria: function(criteria, useDefault){
    
        if (!criteria) {
        
            var criteria = {
                lp: false,
                rp: false,
                ol: 'and'
            };
            
            if (useDefault) {
                criteria.left = this.defaultLeft;
                criteria.operator = this.defaultOperator;
            }
            
        }
        
        if (criteria) {
            var attrid = criteria.left;
            var func = criteria.operator;
            //var key = criteria.right;
            var leftP = criteria.lp;
            var rightP = criteria.rp;
            var ol = criteria.ol;
            
            var key = {};
            
            if (criteria.left) {
            
                var operand = this.getOperand(criteria.left, criteria.operator);
                
                // Start at 1 skips 'left'
                for (var j = 1; j < operand.length; j++) {
                    key[operand[j]] = criteria[operand[j]];
                }
                
            }
            
        }
        
        this.criteriaArray.push(criteria);
        
        var efd = this;
        
        // Only display union button if row is not the first one
        var uButton = null;
        
        if (this.arrayPanel.items.length > 0) {
            uButton = new Ext.Button({
                width: 25,
                text: ol == 'and' ? 'ET' : 'OU',
                margins: {
                    top: 0,
                    right: 0,
                    bottom: 2,
                    left: 2
                },
                listeners: {
                    click: function(button){
                        if (button.getText() == 'ET') {
                            button.setText('OU');
                            criteria.ol = 'or';
                        }
                        else {
                            button.setText('ET');
                            criteria.ol = 'and';
                        }
                        console.log(criteria);
                    }
                }
            });
        }
        else {
            uButton = {
                width: 25,
                border: false,
                margins: {
                    top: 0,
                    right: 0,
                    bottom: 2,
                    left: 2
                }
            };
        }
        
        var acb = new Ext.fdl.AttributeComboBox({
            context: efd.document.context,
            flex: 1,
            margins: {
                top: 0,
                right: 2,
                bottom: 4,
                left: 2
            },
            attributeSelect: function(attributeObj, family){
                criteria.left = attributeObj.id;
                console.log('SelectAttribute', attributeObj.id);
                acb.ocb.setAttributeOperatorList(attributeObj, family);
                acb.ocb.setDefaultValue();
                acb.ocb.operatorSelect(acb.ocb.getValue());
                
                console.log(criteria);
            },
            propertySelect: function(propertyId, family){
                criteria.left = propertyId;
                console.log('SelectProperty', propertyId);
                acb.ocb.setPropertyOperatorList(propertyId, efd.document);
                acb.ocb.setDefaultValue();
                acb.ocb.operatorSelect(acb.ocb.getValue());
                
                console.log(criteria);
            }
        });
        
		if (efd.family) {
			acb.setFamily(efd.family);
		} else {
			acb.setFamily(false);
		}
        this.acbArray.push(acb);
        
        acb.ocb = new Ext.fdl.OperatorComboBox({
            flex: 1,
            margins: {
                top: 0,
                right: 2,
                bottom: 4,
                left: 2
            },
            operatorSelect: function(op){
            
                efd.cleanCriteria(criteria);
                
                criteria.operator = op;
                console.log(criteria);
                
                if (acb.ocb.attribute) {
                    acb.ocb.key.setAttributeField(acb.ocb.attribute, op);
                }
                
                if (acb.ocb.property) {
                    acb.ocb.key.setPropertyField(acb.ocb.property, op, efd.family);
                }
                
            },
            family: efd.family
        });
        
        if (attrid) {
            acb.setValue(attrid);
            
			
            var attribute ;
			if(efd.family){
				attribute = efd.family.getAttribute(attrid);
            }
			
            if (attribute) {
                acb.ocb.setAttributeOperatorList(attribute, efd.family);
            }
            else {
                acb.ocb.setPropertyOperatorList(attrid, efd.document);
            }
            
            if (func) {
                acb.ocb.setValue(func);
            }
            else {
                acb.ocb.setDefaultValue();
            }
            
        }
        
        acb.ocb.key = new Ext.fdl.ValuePanel({
            flex: 1,
            height: 22,
            margins: {
                top: 0,
                right: 2,
                bottom: 4,
                left: 2
            },
            border: false,
            change: function(identifier, newValue){
                criteria[identifier] = newValue;
                console.log('Change', identifier, newValue, criteria);
            }
        });

		// Equip ValuePanel with context to handle case when 'All family' is selected (and thus no family would be provided to get states so widget has to process a generic document)
		acb.ocb.key.context = this.document.context;
		
        // Case attribute
        if (attrid) {
            if (attribute) {
                acb.ocb.key.setAttributeField(attribute, func);
            }
            // Case property
            else {
                acb.ocb.key.setPropertyField(attrid, func, efd.family);
            }
        }
        
        if (key.length != 0) {
            for (var identifier in key) {
                // Test if key is a function
                if (key[identifier] && key[identifier].substring(0, 2) == '::') {
                    acb.ocb.key.setFunctionMode(identifier);
                }
                else {
                    acb.ocb.key.setNormalMode(identifier);
                }
                acb.ocb.key.setValue(identifier, key[identifier]);
            }
        }
        
        var newRow = new Ext.Panel({
            layout: 'hbox',
            layoutConfig: {
                pack: 'start'
            },
            autoHeight: true,
            border: false,
            items: [new Ext.Button({
                icon: this.document.context.url + efd.iconDelete,
                width: 30,
                margins: {
                    top: 0,
                    right: 2,
                    bottom: 4,
                    left: 0
                },
                handler: function(b, e){
                    efd.arrayPanel.remove(newRow);
                    efd.criteriaArray.remove(criteria);
                    console.log(efd.getCriteria());
                    if (efd.arrayPanel.items.length > 0) {
                        efd.arrayPanel.items.itemAt(0).removeUButton();
                        efd.criteriaArray[0].ol = false;
                        console.log(efd.criteriaArray);
                    }
                }
            }), uButton, new Ext.Button({
                width: 20,
                text: '(',
                enableToggle: true,
                pressed: leftP,
                margins: {
                    top: 0,
                    right: 2,
                    bottom: 2,
                    left: 0
                },
                handler: function(b, e){
                    if (b.pressed) {
                        criteria.lp = true;
                    }
                    else {
                        criteria.lp = false;
                    }
                    console.log(criteria);
                }
            }), acb, acb.ocb, acb.ocb.key, new Ext.Button({
                width: 20,
                text: ')',
                enableToggle: true,
                pressed: rightP,
                margins: {
                    top: 0,
                    right: 0,
                    bottom: 2,
                    left: 2
                },
                handler: function(b, e){
                    if (b.pressed) {
                        criteria.rp = true;
                    }
                    else {
                        criteria.rp = false;
                    }
                    console.log(criteria);
                }
            })]
        });
        
        newRow.uButton = uButton;
        newRow.removeUButton = function(){
            if (this.uButton && (this.uButton instanceof Ext.Button)) {
            
                this.remove(this.uButton);
                this.uButton = this.insert(1, new Ext.Panel({
                    width: 25,
                    border: false,
                    margins: {
                        top: 0,
                        right: 0,
                        bottom: 2,
                        left: 2
                    }
                }));
                
            }
        };
        
        this.arrayPanel.add(newRow);
        
        this.arrayPanel.doLayout();
        
    },
    
    initComponent: function(){
    
        var efd = this;
        
        this.criteriaArray = [];
        
        if (this.familyId) {
            this.family = this.document.context.getDocument({
                id: this.familyId
            });
			
			console.log('FAMILY ATTRIBUTES', this.family.getFilterableAttributes());
            
            var icon = this.family.getIcon({
                width: 15
            });
            
            var title = this.family.getTitle();
            
        }
        else {
            var title = 'Toutes les familles';
        }
        
        var tools = [{
            id: 'plus',
            handler: function(event, toolEl, panel, config){
                efd.addCriteria();
            }
        }, {
            id: 'gear',
            handler: function(event, toolEl, panel, config){
            }
        }];  
        
        if(this.closeTool){
            tools.push({
                id: 'close',
                handler: function(event, toolEl, panel, config){
                    panel.ownerCt.familyPanelArray.remove(panel);
                    console.log('Family Panel Array', panel.ownerCt.familyPanelArray);
                    panel.ownerCt.remove(panel);
                }
            });
        }
        
        Ext.apply(this, {
            title: (icon ? ('<img src=' + icon + ' style="float:left;margin-right:3px; />') : '') + title,        
            tools: tools
        });
        
        Ext.fdl.FamilyPanel.superclass.initComponent.call(this);
        
        //        var fcb = new Ext.fdl.FamilyComboBox({
        //            fieldLabel: 'Famille',
        //            filter: this.filter,
        //            context: this.document.context,
        //            hiddenName: 'se_famid',
        //            familySelect: function(id){
        //            
        //                var family = efd.document.context.getDocument({
        //                    id: id
        //                });
        //                
        //                for (var i = 0; i < acbArray.length; i++) {
        //                    acbArray[i].setFamily(family);
        //                }
        //            }
        //        });
        
        this.acbArray = [];
        
        this.arrayPanel = new Ext.Panel({
            border: false,
            items: []
            //            items: [{
            //                layout: 'hbox',
            //                layoutConfig: {
            //                    pack: 'start'
            //                },
            //                border: false,
            //                height: 30,
            //                items: [new Ext.Button({
            //                    icon: this.iconAdd,
            //                    width: 30,
            //                    margins: {
            //                        top: 0,
            //                        right: 2,
            //                        bottom: 2,
            //                        left: 0
            //                    },
            //                    handler: function(b, e){
            //                        efd.addCriteria();
            //                    }
            //                }), {
            //                    width: 25,
            //                    border: false,
            //                    margins: {
            //                        top: 0,
            //                        right: 0,
            //                        bottom: 2,
            //                        left: 2
            //                    }
            //                }, {
            //                    width: 20,
            //                    border: false,
            //                    margins: {
            //                        top: 0,
            //                        right: 2,
            //                        bottom: 2,
            //                        left: 0
            //                    }
            //                }, {
            //                    title: 'Attribut/Propriété',
            //                    margins: {
            //                        top: 0,
            //                        right: 2,
            //                        bottom: 2,
            //                        left: 2
            //                    },
            //                    flex: 1,
            //                    bodyStyle: 'border:0px;'
            //                }, {
            //                    title: 'Opérateur',
            //                    margins: {
            //                        top: 0,
            //                        right: 2,
            //                        bottom: 2,
            //                        left: 2
            //                    },
            //                    flex: 1,
            //                    bodyStyle: 'border:0px;'
            //                }, {
            //                    title: 'Valeur(s)',
            //                    margins: {
            //                        top: 0,
            //                        right: 2,
            //                        bottom: 2,
            //                        left: 2
            //                    },
            //                    flex: 1,
            //                    bodyStyle: 'border:0px;'
            //                }, {
            //                    width: 20,
            //                    border: false,
            //                    margins: {
            //                        top: 0,
            //                        right: 0,
            //                        bottom: 2,
            //                        left: 2
            //                    }
            //                }]
            //            }]
        });
        
        //        if (this.document.getValue('se_famid')) {
        //            fcb.setValue(this.document.getValue('se_famid'));
        //            fcb.familySelect(this.document.getValue('se_famid'));
        //            var family = this.document.context.getDocument({
        //                id: this.document.getValue('se_famid')
        //            });
        //            
        //        }
        
        //        if (this.document.getValue('se_attrids')) {
        //        
        //            var a = this.document.getValue('se_attrids');
        //            var l = a.length;
        //            
        //            for (var i = 0; i < l; i++) {
        //            
        //                var acb = new Ext.fdl.AttributeComboBox({
        //                    flex: 2,
        //                    margins: {
        //                        top: 0,
        //                        right: 2,
        //                        bottom: 4,
        //                        left: 2
        //                    },
        //                    attributeSelect: function(id, type){
        //                        acb.ocb.setOperatorList(type);
        //                    },
        //                    hiddenName: 'se_attrids[]'
        //                });
        //                
        //                acb.setFamily(family);
        //                acb.setValue(this.document.getValue('se_attrids')[i]);
        //                
        //                acbArray.push(acb);
        //                
        //                acb.ocb = new Ext.fdl.OperatorComboBox({
        //                    flex: 1,
        //                    margins: {
        //                        top: 0,
        //                        right: 2,
        //                        bottom: 4,
        //                        left: 2
        //                    },
        //                    hiddenName: 'se_funcs[]',
        //                    attribute: family.getAttribute(this.document.getValue('se_attrids')[i])
        //                });
        //                
        //                if (this.document.getValue('se_funcs')[i]) {
        //                    acb.ocb.setValue(this.document.getValue('se_funcs')[i]);
        //                }
        //                
        //                acb.ocb.key = new Ext.fdl.Text({
        //                    flex: 1,
        //                    margins: {
        //                        top: 0,
        //                        right: 0,
        //                        bottom: 4,
        //                        left: 2
        //                    },
        //                    name: 'se_keys[]'
        //                });
        //                
        //                if (this.document.getValue('se_keys')[i]) {
        //                    acb.ocb.key.setValue(this.document.getValue('se_keys')[i]);
        //                }
        //                
        //                var newRow = new Ext.Panel({
        //                    layout: 'hbox',
        //                    layoutConfig: {
        //                        pack: 'start'
        //                    },
        //                    autoHeight: true,
        //                    border: false,
        //                    items: [new Ext.Button({
        //                        icon: efd.iconDelete,
        //                        width: 30,
        //                        margins: {
        //                            top: 0,
        //                            right: 2,
        //                            bottom: 4,
        //                            left: 0
        //                        },
        //                        handler: function(b, e){
        //                            newRow.ownerCt.remove(newRow);
        //                        }
        //                    }), new Ext.Button({
        //                        width: 30,
        //                        border: false,
        //                        text: '('
        //                    }), acb, acb.ocb, acb.ocb.key]
        //                });
        //                
        //                arrayPanel.add(newRow);
        //                
        //            }
        //            
        //            arrayPanel.doLayout();
        //            
        //        }
        
        //        this.add(fcb);
        this.add(this.arrayPanel);
        
    }
    
});

/**
 * @class Ext.fdl.ValuePanel
 * @namespace Ext.fdl
 */
Ext.fdl.ValuePanel = Ext.extend(Ext.Panel, {
	
	context: null,

    layout: 'hbox',
    layoutConfig: {
        align: 'stretch',
        pack: 'start'
    },
    
    // This is hard-coded match of functions available for each type of attribute
    // Later the server should be able to send it
    functionByType: {
        'date': [{
            "aujourd'hui": "::getDate(0)",
            "hier": "::getDate(-1)",
            "demain": "::getDate(1)"
        }],
		'timestamp': [{
            "aujourd'hui": "::getDate(0)",
            "hier": "::getDate(-1)",
            "demain": "::getDate(1)"
        }],
        'uid': [{
            "mon identifiant utilisateur": "::getSystemUserId()"
        }],
        'docid': [{
            "mon identifiant 'document'": "::getUserId()"
        }]
    },
    
    setAttributeField: function(attribute, operator){
    
        console.log('Set Attribute Field', attribute, operator);
        
        var operatorsInfo = this.context.getSearchCriteria()[attribute.type];
        var operatorInfo = null;
        for (var i = 0; i < operatorsInfo.length; i++) {
            if (operatorsInfo[i].operator == operator) {
                operatorInfo = operatorsInfo[i];
            }
        }
        
        var me = this;
        
        this.removeAll();
        
        if (operatorInfo) {
            // Skip 'left' in operand
            for (var i = 1; i < operatorInfo.operand.length; i++) {
                var input = this.getInput(attribute.type, attribute, operatorInfo.operand[i]);
                this.add(input);
            }
        }
        
        this.doLayout();
        
    },
    
    setPropertyField: function(property, operator, family){
    
        var me = this;
		
		if (family) {
			var info = this.context.getPropertyInformation(property);
			var operatorsInfo = this.context.getSearchCriteria()[info.type];
		} else {
			var genericDocument = this.context.getDocument({
				id: 5,
				useCache: true
			});
			var info = genericDocument.getPropertyInformation(property);
			var operatorsInfo = genericDocument.getSearchCriteria()[info.type];
		}
        

        var operatorInfo = null;
        for (var i = 0; i < operatorsInfo.length; i++) {
            if (operatorsInfo[i].operator == operator) {
                operatorInfo = operatorsInfo[i];
            }
        }
        
        this.removeAll();
        
        if (operatorInfo) {
            // Skip 'left' in operand
            for (var i = 1; i < operatorInfo.operand.length; i++) {
                var input = this.getInput(info.type, property, operatorInfo.operand[i], family);
                this.add(input);
            }
        }
        
        this.doLayout();
        
    },
    
    setNormalMode: function(identifier){
    
        var me = this;
		
        if (me.functionMode[identifier]) {
            me.panel[identifier].remove(this.input[identifier]);
            var input = me.getSubInput(me._type, me._attribute, identifier, me._family);
            me.input[identifier] = me.panel[identifier].insert(0, input);
            me.panel[identifier].doLayout();
            me.functionMode[identifier] = false;
        }
        me.normalButton[identifier].disable();
        me.functionButton[identifier].enable();
        me.change(identifier, '');
        
    },
    
    setFunctionMode: function(identifier){
    
        var me = this;
        
        if (!(this.input[identifier] instanceof Ext.fdl.Text)) {
        
            me.panel[identifier].remove(me.input[identifier]);
            me.input[identifier] = me.panel[identifier].insert(0, new Ext.fdl.Text({
                listeners: {
                    keyup: function(field, event){
                        me.change(identifier, field.getValue());
                    }
                },
                flex: 1
            }));
            
            me.panel[identifier].doLayout();
        }
        
        me.input[identifier].setValue('::');
        me.change(identifier, '::');
        me.functionMode[identifier] = true;
        me.functionButton[identifier].disable();
        me.normalButton[identifier].enable();
    },
    
    getSubInput: function(type, attribute, identifier, family){
    
        var me = this;
        
        var input;
        
        // In case we are treating the state property
        if (attribute == 'state') {
        
            var input = new Ext.fdl.StateComboBox({
                displayField: 'activity',
                //OR displayField: 'label', // Depending on the options for the family panel (to define with Eric)
                family: family,
                listeners: {
                    select: function(field, record){
                        me.change(identifier, record.json.key);
                    }
                },
                flex: 1
            });
            
            return input;
            
        }
        
        switch (type) {
			
            case 'enum':
                
                input = new Ext.fdl.Enum({
                    attribute: attribute,
                    listeners: {
                        select: function(field){
                            me.change(identifier, field.getValue());
                        }
                    },
                    flex: 1
                });
                
                break;
                
            case 'date':
            case 'timestamp':
                
                input = new Ext.fdl.Date({
                    listeners: {
                        select: function(field, date){
                            me.change(identifier, date.format('d/m/Y'));
                        },
                        valid: function(field){
                            if (field.getValue() instanceof Date) {
                                me.change(identifier, field.getValue().format('d/m/Y'));
                            }
                        },
                        invalid: function(field){
                            me.change(identifier, '');
                        }
                    },
                    flex: 1
                });
                
                break;
                
            default:
                
                input = new Ext.fdl.Text({
                    listeners: {
                        keyup: function(field, event){
                            me.change(identifier, field.getValue());
                        }
                    },
                    flex: 1
                });
                
                break;
        }
        
        return input;
        
    },
    
    getFunctionMenu: function(type, attribute, identifier, family){
    
        var me = this;
        
        var menuItems = [];
        
        this.normalButton[identifier] = new Ext.menu.Item({
            text: '<b>mode normal</b>',
            handler: function(b, e){
                me.setNormalMode(identifier);
            },
            disabled: true
        });
        
        menuItems.push(this.normalButton[identifier]);
        
        this.functionButton[identifier] = new Ext.menu.Item({
            text: '<b>mode fonction</b>',
            handler: function(b, e){
                me.setFunctionMode(identifier);
            }
        });
        
        menuItems.push(this.functionButton[identifier]);
        
        if (this.functionByType[type]) {
        
            for (var i = 0; i < this.functionByType[type].length; i++) {
            
                for (var j in this.functionByType[type][i]) {
                    menuItems.push({
                        text: j,
                        fct: this.functionByType[type][i][j],
                        handler: function(b, e){
                        
                            if (!(me.input[identifier] instanceof Ext.fdl.Text)) {
                            
                                me.panel[identifier].remove(me.input[identifier]);
                                me.input[identifier] = me.panel[identifier].insert(0, new Ext.fdl.Text({
                                    listeners: {
                                        keyup: function(field, event){
                                            me.change(identifier, field.getValue());
                                        }
                                    },
                                    flex: 1
                                }));
                                me.panel[identifier].doLayout();
                            }
                            
                            me.input[identifier].setValue(b.fct);
                            me.change(identifier, b.fct);
                            
                            me.functionMode[identifier] = true;
                            me.functionButton[identifier].disable();
                            me.normalButton[identifier].enable();
                            
                        }
                    });
                }
            }
            
        }
        
        return new Ext.Button({
            text: 'Σ',
            menu: menuItems
        });
        
    },
    
    getInput: function(type, attribute, identifier, family){
    
        var me = this;
        
        this._type = type;
        this._attribute = attribute;
        this._identifier = identifier;
        this._family = family;
        
        this.panel[identifier] = new Ext.Panel({
            border: false,
            layout: 'hbox',
            layoutConfig: {
                align: 'stretch',
                pack: 'start'
            },
            flex: 1
        });
        
        this.input[identifier] = this.panel[identifier].add(this.getSubInput(type, attribute, identifier, family));
        
        this.panel[identifier].add(this.getFunctionMenu(type, attribute, identifier, family));
        
        return this.panel[identifier];
        
    },
    
    initComponent: function(){
		
        Ext.fdl.ValuePanel.superclass.initComponent.call(this);
        
        this.input = {};
        this.panel = {};
        
        this.functionButton = {};
        this.normalButton = {};
        
        this.functionMode = {};
        
    },
    
    setValue: function(identifier, value){
        this.input[identifier].setValue(value);
        this.change(identifier, value);
    },
    
    // Automatically called when a change in criteria is triggered by this component
    change: function(identifier, value){
    
    }
    
});

/**
 * @class Ext.fdl.Requester
 * @namespace Ext.fdl
 */
Ext.fdl.Requester = Ext.extend(Ext.Panel, {

    bodyStyle: 'padding:5px;overflow:auto;',
    
    layout: 'form',
    
    labelWidth: 150,
    
    familyPanelArray: [],
    
    familyId: null,
    
    allowSubfamily: true,
    
    selectFamilyId: function(id){
    
        var famId;
        if (id == null || id == '_allfam') {
            famId = null;
        }
        else {
            famId = id;
        }
        
        var me = this;
        
        var familyPanel = new Ext.fdl.FamilyPanel({
            document: this.document,
            familyId: famId,
            style: 'padding:5px;'
        });
        
        // TODO remove when data is correct        
        this.removeAll();
        if(!this.familyId){
            this.add(new Ext.fdl.FamilyComboBox({
                context: me.document.context,
                fieldLabel: 'Inclure une famille dans la recherche',
                familySelect: function(id){
                    var familyPanel = me.selectFamilyId(id);
                    familyPanel.addCriteria(null, true);
                }
            }));
            //---
        } else {
            
            var family = this.document.context.getDocument({
                id: famId,
                useCache: true
            });
            
            this.add(new Ext.form.DisplayField({
                fieldLabel: 'Recherche sur la famille',
                value: '<div style="background-image:url(\'' + family.getIcon({width:15}) + '\');padding-left:20px;background-repeat:no-repeat;" class="x-combo-list-item" >' + Ext.util.Format.capitalize(family.getTitle()) + '</div>'
            }));
        }
        this.add(familyPanel);
        this.doLayout();
        
        //TODO remove when data is correct
        this.familyPanelArray = [];
        //---
        
        this.familyPanelArray.push(familyPanel);
        
        return familyPanel;
        
    },
    
    initComponent: function(){
    
        var me = this;
        
        if(!this.familyId){
            Ext.apply(this, {
            
                items: [new Ext.fdl.FamilyComboBox({
                    context: me.document.context,
                    fieldLabel: 'Inclure une famille dans la recherche',
                    familySelect: function(id){
                        var familyPanel = me.selectFamilyId(id);
                        familyPanel.addCriteria(null, true);
                    }
                })]
            
            });
        } else {
                        
            var family = me.document.context.getDocument({
                id: this.familyId,
                useCache: true
            });
            
            var familyPanel = new Ext.fdl.FamilyPanel({
                document: this.document,
                familyId: this.familyId,
                style: 'padding:5px;',
                closeTool: false
            });
            
            familyPanel.addCriteria(null, true);
            
            //TODO remove when data is correct
            this.familyPanelArray = [];
            //---
            
            this.familyPanelArray.push(familyPanel);
            
            console.log('FAMILY-PANEL-ARRAY-INIT',this.familyPanelArray);
                        
            Ext.apply(this, {
                
                items: [new Ext.form.DisplayField({
                    fieldLabel: 'Recherche sur la famille',
                    value: '<div style="background-image:url(\'' + family.getIcon({width:15}) + '\');padding-left:20px;background-repeat:no-repeat;" class="x-combo-list-item" >' + Ext.util.Format.capitalize(family.getTitle()) + '</div>'
                }), familyPanel]
                
            });
            
        }
        
        Ext.fdl.Requester.superclass.initComponent.call(this);
        
        var filters = this.document.getFilters();

        console.log('the filters',this.document,filters);
        if (!filters) {
            filters = [];
        }
        
        if(!this.familyPanelArray){
            this.familyPanelArray = [];
        }        
        
        for (var f = 0; f < filters.length; f++) {
            if (filters[f].family) {
            
                var filter = filters[f];
                
                var familyPanel = this.selectFamilyId(filter.family);
                console.log('negin',filter);
           //    console.log('Linearize', filter.criteria, filter.linearize(), filter.unLinearize(filter.linearize()));
                
                var linearize = filter.linearize();

                for (var i = 0; i < linearize.length; i++) {
                    familyPanel.addCriteria(linearize[i]);
                }
                
                if (linearize.length == 0) {
                    familyPanel.addCriteria(null, true);
                }
                
                //console.log('Linearize', filter.criteria, filter.linearize(), filter.unLinearize(filter.linearize()));
                
            }
        }
        
        //        var saveButton = new Ext.Button({
        //            text: 'Sauver (provisoire)',
        //            scope: this,
        //            handler: function(b, e){
        //                this.save();
        //            }
        //        });
        //        
        //        this.add(saveButton);
    
    },
    
    save: function(){
    
        this.document.resetFilter();
        
        console.log('FAMILY-PANEL-ARRAY',this.familyPanelArray);
        
        // Process Filters here
        for (var i = 0; i < this.familyPanelArray.length; i++) {
        
            var family = this.familyPanelArray[i].familyId;
            var criteria = this.familyPanelArray[i].getCriteria();
            
            var filter = new Fdl.DocumentFilter({
                family: family + '' // error if family is a number
            });
            
            filter.criteria = filter.unLinearize(criteria);
            
            console.log('addFilter', filter);
            
            this.document.addFilter(filter);
            
        }
        
        this.document.save();
        
    },
    
    setDocument: function(doc){
        this.document = doc;
    }
    
});


Ext.fdl.TabRequester = Ext.extend(Ext.TabPanel, {

    document: null,
    
    deferredRender: false,
    activeTab: 0,
    
    height: 400,
    
    initComponent: function(){
    
        var me = this;
        
        Ext.apply(this, {
        
            items: [new Ext.form.FormPanel({
                title: 'Critère',
                bodyStyle: 'padding:5px;overflow:auto;',
                items: [new Ext.fdl.Requester({
                    document: me.document
                })]
            }), new Ext.form.FormPanel({
                title: 'Options',
                layou: 'form',
                bodyStyle: 'padding:5px;'
                //                items: [new Ext.form.ComboBox({
                //                    name: 'countries',
                //                    editable: false,
                //                    disableKeyFilter: true,
                //                    forceSelection: true,
                //                    triggerAction: 'all',
                //                    mode: 'local',
                //                    store: new Ext.data.SimpleStore({
                //                        id: 0,
                //                        fields: ['value', 'text'],
                //                        data: [['1', 'Courante'], ['2', 'Toutes les révisions']]
                //                    }),
                //                    valueField: 'value',
                //                    displayField: 'text',
                //                    hiddenName: 'countries',
                //                    fieldLabel: 'Révision'
                //                }), {
                //                    xtype: 'textfield',
                //                    fieldLabel: 'A partir du dossier'
                //                }, new Ext.form.ComboBox({
                //                    name: 'countries',
                //                    editable: false,
                //                    disableKeyFilter: true,
                //                    forceSelection: true,
                //                    triggerAction: 'all',
                //                    mode: 'local',
                //                    store: new Ext.data.SimpleStore({
                //                        id: 0,
                //                        fields: ['value', 'text'],
                //                        data: [['1', 'Avec les sous familles'], ['2', 'Sans les sous familles']]
                //                    }),
                //                    valueField: 'value',
                //                    displayField: 'text',
                //                    hiddenName: 'countries',
                //                    fieldLabel: 'Sans sous famille'
                //                }), {
                //                    xtype: 'textfield',
                //                    fieldLabel: 'Document'
                //                }, new Ext.form.ComboBox({
                //                    name: 'countries',
                //                    editable: false,
                //                    disableKeyFilter: true,
                //                    forceSelection: true,
                //                    triggerAction: 'all',
                //                    mode: 'local',
                //                    store: new Ext.data.SimpleStore({
                //                        id: 0,
                //                        fields: ['value', 'text'],
                //                        data: [['1', 'Non'], ['2', 'Seulement']]
                //                    }),
                //                    valueField: 'value',
                //                    displayField: 'text',
                //                    hiddenName: 'countries',
                //                    fieldLabel: 'Dans la poubelle'
                //                })]
            }), {
                title: 'Présentation'
                //                items: [new Ext.form.ComboBox({
                //                    name: 'countries',
                //                    editable: false,
                //                    disableKeyFilter: true,
                //                    forceSelection: true,
                //                    triggerAction: 'all',
                //                    mode: 'local',
                //                    store: new Ext.data.SimpleStore({
                //                        id: 0,
                //                        fields: ['value', 'text'],
                //                        data: [['1', 'Grille'], ['2', 'Icone']]
                //                    }),
                //                    valueField: 'value',
                //                    displayField: 'text',
                //                    hiddenName: 'countries',
                //                    fieldLabel: 'Interface'
                //                })]
            }]
        
        });
        
        Ext.fdl.TabRequester.superclass.initComponent.call(this);
        
    }
    
});
