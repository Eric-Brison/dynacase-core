/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Fdl.Attribute
 * @namespace Fdl.Attribute
 * @param {Object}
 *            config
 */

Fdl.Attribute = function(config) {
	if (config) {
		this._data = new Object();
		for ( var name in config) {
			switch (name) {
			case 'id':
				this.id = config[name];
				break;
			case 'docid':
				this.famId = config[name];
				break;
			case 'labelText':
				this.label = config[name];
				break;
			case 'type':
			case 'options':
			case 'usefor':
			case 'mvisibility':
			case 'visibility':
				this[name] = config[name];
				break;
			default:
				this._data[name] = config[name];
				break;
			}
		}

		this.rank = config.rank || 0;
		this.parentId = config.parentId || 0;
	}
};

Fdl.Attribute.prototype = {
	/**
	 * identificator of the attribute
	 * 
	 * @type Number
	 */
	id : null,
	/**
	 * type of attribute : int, docid, date,...
	 * 
	 * @type String
	 */
	type : null,
	/**
	 * family identificator
	 * 
	 * @type Number
	 */
	famId : null,
	/**
	 * the node attribute where the attribute is inserted (could be null if top
	 * attribute)
	 * 
	 * @type String
	 */
	parentId : null,
	/**
	 * label of attribute use ::getLabel() to get it
	 * 
	 * @hide
	 * @type String
	 */
	label : null,
	usefor : null,
	options : null,
	visibility : null,
	mvisibility : null,
	options : new Object(),
	_isNode : null,
	_child : null,

	toString : function() {
		return 'Fdl.Attribute';
	},
	getLabel : function() {
		return this.label;
	},
	getVisibility : function() {
		if (this.mvisibility) {
			return this.mvisibility;
		}
		return this.visibility;
	},
	getOption : function(optkey) {
		if (this.options && this.options[optkey]) {
			return this.options[optkey];
		}
		return null;
	},
	getParent : function() {
		if (!this.parentId)
			return null;
		var oa = this._family.getAttribute(this.parentId);
		return oa;
	},
	getChildAttributes : function() {
		if (this._childs)
			return this._childs;
		this._chids = [];
		var als = this._family.getAttributes();
		for ( var aid in als) {
			if (als[aid].parentId == this.id)
				this._chids.push(this._family.getAttribute(aid));
		}
		return this._chids;
	},
	/**
	 * Verify if attribute is a leaf
	 * 
	 * @return {Boolean} return true if it is a leaf
	 */
	isLeaf : function() {
		return (this._isNode !== null && (!this._isNode));
	},
	/**
	 * Verify if attribute is a node
	 * 
	 * @return {Boolean} return true if it is a node
	 */
	isNode : function() {
		return (this._isNode !== null && this._isNode);
	}

};
/**
 * get compatible filter criteria
 * 
 * @return {Array} of filter description object
 */
Fdl.Attribute.prototype.getFilterCriteria = function() {
	if (this._family) {
		var top = this._family.getSearchCriteria();
		if (top) {
			if (top[this.type]) {
				if (this.type == "docid") {
					if (this.relationFamilyId) {
						return top[this.type];
					} else {
						var ftop = [];
						for ( var i in top[this.type]) {
							if (top[this.type][i].operator != '=~*')
								ftop.push(top[this.type][i]);
						}
						return ftop;
					}
				} else
					return top[this.type];
			} else
				return [];
		}
	}
	return false;
};

/**
 * Test if attribute is sortable.
 * 
 * @return {Boolean} true if attribute is sortable
 */
Fdl.Attribute.prototype.isSortable = function() {
	if (this.isLeaf() && (!this.inArray()) && (this.type != 'longtext')
			&& (this.type != 'htmltext') && (this.type != 'color')
			&& (this.type != 'image') && (this.type != 'file')) {
		return true;
	} else {
		return false;
	}
};

/**
 * @class Fdl.LeafAttribute
 * @extends Fdl.Attribute
 * @namespace Fdl.Attribute
 * @param {Object}
 *            config
 */
Fdl.LeafAttribute = function(config) {
	Fdl.Attribute.call(this, config);
	if (config) {
		for ( var name in config) {
			switch (name) {
			case 'phpconstraint':
				this.constrain = config[name];
				break;
			case 'ordered':
				this.rank = config[name];
				break;
			case 'isInTitle':
				this.inTitle = config[name];
				break;
			case 'isInAbstract':
				this.inAbstract = config[name];
				break;
			case 'link':
				this[name] = config[name];
				break;
			case 'needed':
				this[name] = config[name];
				break;
			default:
				this._data[name] = config[name];
				break;
			}
		}
		if (!this.rank)
			this.rank = 0;
	}
};
Fdl.LeafAttribute.prototype = new Fdl.Attribute();
Fdl.LeafAttribute.prototype._isNode = false;
Fdl.LeafAttribute.prototype.rank = 0;
Fdl.LeafAttribute.prototype.needed = null;
Fdl.LeafAttribute.prototype.inAbstract = null;
Fdl.LeafAttribute.prototype.inTitle = null;
/**
 * verify if attribute has a contraint defined
 * 
 * @return {Boolean} true if constrint detected
 */
Fdl.LeafAttribute.prototype.hasConstrain = function() {
	return this.constrain != '';
};
Fdl.LeafAttribute.prototype.toString = function() {
	return 'Fdl.LeafAttribute';
};

/**
 * Get associated value
 * 
 * @return {Any} return value of current document fot this attribute
 */
/*
 * Fdl.LeafAttribute.prototype.getValue= function() { if (this._family) { return
 * this._family.getValue(this.id); } return null; };
 */
Fdl.LeafAttribute.prototype.hasInputHelp = function() {
	return (this._data.phpfunc && this._data.phpfile && this.type != 'enum');
};

/**
 * Verify if attribute is defined in a array
 * 
 * @return {Boolean} return true if is a part of array
 */
Fdl.LeafAttribute.prototype.inArray = function() {
	if (this.isLeaf() && this.parentId && this.getParent().type == 'array')
		return true;
	return false;
};
/**
 * Get sort key In main case it is the attribute id but in some case like
 * relations attribute it is the title of relation
 * 
 * @return {String} return the sort key
 */
Fdl.LeafAttribute.prototype.getSortKey = function() {
	return this.id;
};

/**
 * @class Fdl.TextAttribute
 * @extends Fdl.LeafAttribute
 * @namespace Fdl.Attribute
 * @param {Object}
 *            config
 */
Fdl.TextAttribute = function(config) {
	Fdl.LeafAttribute.call(this, config);
};
Fdl.TextAttribute.prototype = new Fdl.LeafAttribute();
Fdl.TextAttribute.prototype.toString = function() {
	return 'Fdl.TextAttribute';
};

/**
 * @class Fdl.EnumAttribute Enum Attribute class
 * @extends Fdl.LeafAttribute
 * @namespace Fdl.Attribute
 * @param {Object}
 *            config
 */
Fdl.EnumAttribute = function(config) {
	Fdl.LeafAttribute.call(this, config);
};
Fdl.EnumAttribute.prototype = new Fdl.LeafAttribute();
Fdl.EnumAttribute.prototype.toString = function() {
	return 'Fdl.EnumAttribute';
};
/**
 * return list of items for a enum attribute
 * 
 * @return {array} of object {key:'thefirst',label: 'my first key'}
 */
Fdl.EnumAttribute.prototype.getEnumItems = function() {
	if (this._data.enumerate) {
		var t = new Array();
		for ( var i in this._data.enumerate) {
			if (typeof this._data.enumerate[i] != 'function')
				t.push({
					key : i,
					label : this._data.enumerate[i]
				});
		}
		return t;
	}
	return null;
};
/**
 * return label for a key
 * 
 * @param {Object}
 *            config
 *            <ul>
 *            <li><b>key : </b>The key item</li>
 *            </ul>
 * @return {String} the label, null if key not exists
 */
Fdl.EnumAttribute.prototype.getEnumLabel = function(config) {
	if (config && config.key) {
		if (this._data.enumerate) {
			for ( var i in this._data.enumerate) {
				if (i == config.key)
					return this._data.enumerate[i];
			}
		}
	}
	return null;
};
/**
 * verify is enum accept several values
 * 
 * @return {Boolean} return true if accept several values, false if single value
 *         accepted
 */
Fdl.EnumAttribute.prototype.isMultiple = function() {
	return this.getOption('multiple') == 'yes';
};

/**
 * @class Fdl.MenuAttribute Menu Attribute class
 * @extends Fdl.Attribute
 * @namespace Fdl.Attribute
 * @param {Object}
 *            config
 */
Fdl.MenuAttribute = function(config) {
	Fdl.Attribute.call(this, config);
};
Fdl.MenuAttribute.prototype = new Fdl.Attribute();
Fdl.MenuAttribute.prototype.toString = function() {
	return 'Fdl.MenuAttribute';
};

/**
 * @class Fdl.ColorAttribute Color Attribute class
 * @extends Fdl.LeafAttribute
 * @namespace Fdl.Attribute
 * @param {Object}
 *            config
 */
Fdl.ColorAttribute = function(config) {
	Fdl.LeafAttribute.call(this, config);
};
Fdl.ColorAttribute.prototype = new Fdl.LeafAttribute();
Fdl.ColorAttribute.prototype.toString = function() {
	return 'Fdl.ColorAttribute';
};
/**
 * @class Fdl.ThesaurusAttribute Thesaurus Attribute class
 * @extends Fdl.LeafAttribute
 * @namespace Fdl.Attribute
 * @param {Object}
 *            config
 */
Fdl.ThesaurusAttribute = function(config) {
	Fdl.LeafAttribute.call(this, config);
};
Fdl.ThesaurusAttribute.prototype = new Fdl.LeafAttribute();
Fdl.ThesaurusAttribute.prototype.toString = function() {
	return 'Fdl.ThesaurusAttribute';
};

/**
 * @class Fdl.RelationAttribute Relation Attribute class
 * @extends Fdl.LeafAttribute
 * @namespace Fdl.Attribute
 * @param {Object}
 *            config
 */
Fdl.RelationAttribute = function(config) {
	Fdl.LeafAttribute.call(this, config);
	if (this._data)
		this.relationFamilyId = this._data.format;
	if (config && config.relationFamilyId)
		this.relationFamilyId = config.relationFamilyId;
};
Fdl.RelationAttribute.prototype = new Fdl.LeafAttribute();
Fdl.RelationAttribute.prototype.toString = function() {
	return 'Fdl.RelationAttribute';
};
/**
 * return title of document relation
 * 
 * @deprecated
 * @return string the title of the document linked
 */
Fdl.RelationAttribute.prototype.getTitle = function() {
	alert('do not use relationAttribut::getTitle');
	return this._family.getValue(this.id + '_title');
};
/**
 * Get sort key the title of relation
 * 
 * @return {String} return the sort key (null is it unsortable)
 */
Fdl.RelationAttribute.prototype.getSortKey = function() {
	var atitle = this.getOption('doctitle');
	if (atitle) {
		if (atitle == 'auto')
			return this.id + '_title';
		else
			return atitle;
	}
	return null;
};
/**
 * return possible document where could ne linked
 * 
 * @param string
 *            [TODO]...
 * @return array
 */
Fdl.RelationAttribute.prototype.retrieveProposal = function(config) {
	var data = this._family.context.retrieveData({
		app : 'DATA',
		action : 'DOCUMENT',
		method : 'retrieveproposal',
		attributeId : this.id,
		relationFamilyId : this.relationFamilyId,
		id : (this._family) ? (this._family.id ? this._family.id : this._family
				.getProperty('fromid')) : null
	}, config);
	if (data) {
		if (!data.error) {
			return data.proposal;
		} else {
			this._family.context.setErrorMessage(data.error);
		}
	} else {
		this._family.context.setErrorMessage('retrieveProposal: no data');
	}
	return null;
};

/**
 * @class Fdl.DateAttribute Date Attribute class
 * @extends Fdl.LeafAttribute
 * @namespace Fdl.Attribute
 * @param {Object}
 *            config
 */
Fdl.DateAttribute = function(config) {
	Fdl.LeafAttribute.call(this, config);
};
Fdl.DateAttribute.prototype = new Fdl.LeafAttribute();
Fdl.DateAttribute.prototype.toString = function() {
	return 'Fdl.DateAttribute';
};

/**
 * @class Fdl.FileAttribute File Attribute class
 * @extends Fdl.LeafAttribute
 * @namespace Fdl.Attribute
 * @param {Object}
 *            config
 */
Fdl.FileAttribute = function(config) {
	Fdl.LeafAttribute.call(this, config);
};
Fdl.FileAttribute.prototype = new Fdl.LeafAttribute();
Fdl.FileAttribute.prototype.toString = function() {
	return 'Fdl.FileAttribute';
};
Fdl.FileAttribute.prototype.getUrl = function(v, documentId, config) {
	if (v) {
		var sinline = 'yes';
		var swidth = false;
		var stype = false;
		var spage = false;
		var index = -1;
		if (config) {
			if (config.inline === false)
				sinline = 'no';
			if (config.type)
				stype = config.type;
			if (config.width)
				swidth = parseInt(config.width);
			if (config.page !== null)
				spage = parseInt(config.page);
			if (typeof (config.index) == 'number') {
				index = parseInt(config.index);
				v = v[index];
			}
		}

		var p1 = v.indexOf('|', 0);
		var p2 = v.indexOf('|', p1 + 1);
		if (p2 == -1)
			p2 = v.length;

		if ((p1 > 0) && (p2 > p1)) {
			var vid = v.substring(p1 + 1, p2);
			var url = this._family.context.url
					+ '?app=FDL&action=EXPORTFILE&inline=' + sinline
					+ '&cache=no&vid=' + vid + '&docid=' + documentId
					+ '&attrid=' + this.id + '&index=' + index;
			if (swidth)
				url += '&width=' + swidth;
			if (stype)
				url += '&type=' + stype;
			if (spage !== false)
				url += '&page=' + spage;
			return url;
		}
	}
	return null;
};

Fdl.FileAttribute.prototype.getDavUrl = function(v, documentId) {
	data = this._family.context.retrieveData({
		app : 'DATA',
		action : 'DOCUMENT',
		method : 'davurl',
		id : documentId,
		vid : this.getVaultId(v)
	});
	if (data) {
		if (!data.error) {
			var url = data.url;
			return url;
		} else {
			this._family.context.setErrorMessage(data.error);
		}
	}
	return null;
};
/*
 * @deprecated Fdl.FileAttribute.prototype.hasPDF= function(config) { var
 *             apdf=this.getOption('pdffile'); if (apdf) {
 * 
 * var vpdf=this._family.getValue(apdf); if (vpdf) { if (vpdf.indexOf('/pdf')>0)
 * return true; } } return false; }
 */
Fdl.FileAttribute.prototype.getFileName = function(v, config) {
	if (v) {
		if (config && typeof (config.index) == 'number') {
			index = parseInt(config.index);
			v = v[index];
		}
		var p1 = v.indexOf('|', 0);
		var p2 = v.indexOf('|', p1 + 1);
		if ((p2 > 0) && (p2 < v.length)) {
			return v.substring(p2 + 1, v.length);
		}
	}
	return null;
};
Fdl.FileAttribute.prototype.getVaultId = function(v) {
	if (v) {
		var p1 = v.indexOf('|', 0);
		var p2 = v.indexOf('|', p1 + 1);

		if ((p1 > 0) && (p2 > p1)) {
			var vid = v.substring(p1 + 1, p2);
			return vid;
		}
	}
	return null;
};

/**
 * @class Fdl.NumericAttribute Numeric Attribute class
 * @extends Fdl.LeafAttribute
 * @namespace Fdl.Attribute
 * @param {Object}
 *            config
 */
Fdl.NumericAttribute = function(config) {
	Fdl.LeafAttribute.call(this, config);
};
Fdl.NumericAttribute.prototype = new Fdl.LeafAttribute();
Fdl.NumericAttribute.prototype.toString = function() {
	return 'Fdl.NumericAttribute';
};

/**
 * @class Fdl.NodeAttribute Node Attribute class
 * @extends Fdl.Attribute
 * @namespace Fdl.Attribute
 * @param {Object}
 *            config
 */
Fdl.NodeAttribute = function(config) {
	Fdl.Attribute.call(this, config);
};
Fdl.NodeAttribute.prototype = new Fdl.Attribute();
Fdl.NodeAttribute.prototype._isNode = true;
Fdl.NodeAttribute.prototype.toString = function() {
	return 'Fdl.NodeAttribute';
};

/**
 * @class Fdl.ArrayAttribute Array Attribute class
 * @extends Fdl.NodeAttribute
 * @namespace Fdl.Attribute
 * @param {Object}
 *            config
 */
Fdl.ArrayAttribute = function(config) {
	Fdl.NodeAttribute.call(this, config);

};
Fdl.ArrayAttribute.prototype = new Fdl.NodeAttribute();
Fdl.ArrayAttribute.prototype.toString = function() {
	return 'Fdl.ArrayAttribute';
};

/**
 * return all attributes which compose the array
 * 
 * @return {Array} of Fdl.Attribute
 */
Fdl.ArrayAttribute.prototype.getElements = function() {
	return this.getChildAttributes();
};

Fdl.ArrayAttribute.prototype.getArrayValues = function(config) {
	var oas = this.getElements();
	var rv = [];
	var vc;

	if (oas.length > 0 && oas[0].getValue().length > 0) {
		// first find max rows
		var i = 0;
		for (i = 0; i < oas[0].getValue().length; i++) {
			rv[i] = new Object();
		}

		for (i = 0; i < oas.length; i++) {
			vc = oas[i].getValue();
			for ( var ic = 0; ic < vc.length; ic++) {
				rv[ic][oas[i].id] = vc[ic];
			}
		}
	}
	return rv;
};

/**
 * @class Fdl.TabAttribute Tab Attribute class
 * @extends Fdl.NodeAttribute
 * @namespace Fdl.Attribute
 * @param {Object}
 *            config
 */
Fdl.TabAttribute = function(config) {
	Fdl.NodeAttribute.call(this, config);
};
Fdl.TabAttribute.prototype = new Fdl.NodeAttribute();
Fdl.TabAttribute.prototype.toString = function() {
	return 'Fdl.TabAttribute';
};

/**
 * @class Fdl.FrameAttribute Frame Attribute class
 * @extends Fdl.NodeAttribute
 * @namespace Fdl.Attribute
 * @param {Object}
 *            config
 */
Fdl.FrameAttribute = function(config) {
	Fdl.NodeAttribute.call(this, config);
};
Fdl.FrameAttribute.prototype = new Fdl.NodeAttribute();
Fdl.FrameAttribute.prototype.toString = function() {
	return 'Fdl.FrameAttribute';
};
