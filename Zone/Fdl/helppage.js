
var helppage = {
	langs: [],
	view: {},
	edithelp: {},
	editsection: {},
	edit: {
		calls: {},
		params: {},
		tools: {},
		section: {},
		popup: {}
	},
	print: function () {
		self.print();
	},
	get: function(id) {
		return document.getElementById(id);
	},
	langToIndex: function(lang) {
		var i = 0;
		for (var k in helppage.langs) {
			if(lang == k) {
				return i;
			}
			i++;
		}
		return -1;
	}
};

/*****************************************************************************
 * EDIT SECTION SUBWINDOW
 */

helppage.editsection.datas = null;
helppage.editsection.seckey = null;
helppage.editsection.curlang = false;
helppage.editsection.secorder = 0;

helppage.editsection.cancel = function() {
	window.close();
};

helppage.editsection.apply = function() {
	helppage.editsection.get(helppage.editsection.curlang);
	var sent_datas = {
		'seckey': helppage.editsection.seckey,
		'secorder': helppage.editsection.secorder,
		'curlang': helppage.editsection.curlang,
		'langs': helppage.editsection.datas
	};
	Ih.callOpener('helpcallSaveSection', sent_datas);
	window.close();
};

helppage.editsection.clearall = function() {
	for(var lang in helppage.langs) {
		helppage.editsection.datas[lang].name = '';
		helppage.editsection.datas[lang].text = '';
	}
	var sent_datas = {
		'seckey': helppage.editsection.seckey,
		'secorder': helppage.editsection.secorder,
		'curlang': helppage.editsection.curlang,
		'langs': helppage.editsection.datas
	};
	Ih.callOpener('helpcallSaveSection', sent_datas);
	window.close();
};

helppage.editsection.init = function() {
	helppage.editsection.datas = Ih.callOpener('helpcallGetSection', helppage.editsection.seckey);
	helppage.editsection.secorder = Ih.callOpener('helpcallGetSectionOrder', helppage.editsection.seckey);
};

helppage.editsection.load = function() {
	helppage.editsection.view(Ih.callOpener('helpcallGetCurrentSectionLang',helppage.editsection.seckey));
};

helppage.editsection.view = function(seclang) {
	if(helppage.editsection.curlang) {
		// save before changing
		helppage.editsection.get(helppage.editsection.curlang);
	}
	helppage.editsection.set(seclang);
	document.getElementById('help-legend').className = seclang;
	helppage.editsection.curlang = seclang;
};

helppage.editsection.set = function(lang) {
	document.getElementById('sec-name').value = helppage.editsection.datas[lang].name;
	document.getElementById('sec-text').value = helppage.editsection.datas[lang].text;
	FCKeditorAPI.GetInstance('sec-text').SetHTML(helppage.editsection.datas[lang].text);
};

helppage.editsection.get = function(lang) {
	helppage.editsection.datas[lang].name = document.getElementById('sec-name').value;
	helppage.editsection.datas[lang].text = FCKeditorAPI.GetInstance('sec-text').GetHTML();
};

helppage.editsection.clear = function() {
	helppage.editsection.datas[helppage.editsection.curlang].name = '';
	helppage.editsection.datas[helppage.editsection.curlang].text = '';
	helppage.editsection.set(helppage.editsection.curlang);
};

/*****************************************************************************
 * EDIT HELP SUBWINDOW
 */

helppage.edithelp.cancel = function() {
	window.close();
};

helppage.edithelp.apply = function() {
	Ih.docClearTableRow('help_t_help');
	for (var k in helppage.langs) {
		var index = helppage.langToIndex(k);
		if(index < 0) {
			continue;
		}
		var name = helppage.get('help_name_'+index).value;
		var description = helppage.get('help_description_'+index).value;
		if(name || description) {
			Ih.docAddTableRow({
				help_lang: k,
				help_name: name,
				help_description: description
			});
		}
	}
	Ih.callOpener('helpcallReloadLangs','');
	window.close();
};

helppage.edithelp.view = function(lang) {
	for (var k in helppage.langs) {
		var o = helppage.get('help-'+k);
		if(o) {
			if(lang == k) {
				o.style.display = 'block';
			}
			else {
				o.style.display = 'none';
			}
		}
	}
};

helppage.edithelp.load = function() {
	var help_langs = Ih.docGetFormValue('help_lang');
	var help_names = Ih.docGetFormValue('help_name');
	var help_descriptions = Ih.docGetFormValue('help_description');
	if(help_langs) {
		for (var k in helppage.langs) {
			helppage.edithelp.setValues('', '', k);
			for(var i=0; i < help_langs.length; i++) {
				if(k == help_langs[i]) {
					helppage.edithelp.setValues(help_names[i], help_descriptions[i], k);
					break;
				}
			}
		}
	}
	else {
		for (var k in helppage.langs) {
			helppage.edithelp.setValues('', '', k);
		}
	}
	var current_lang = Ih.callOpener('helpcallGetCurrentLang','');
	if(current_lang) {
		helppage.edithelp.view(current_lang);
	}
};

helppage.edithelp.clear = function(lang) {
	for (var k in helppage.langs) {
		if(lang == k) {
			helppage.edithelp.setValues('', '', k);
		}
	}
};

helppage.edithelp.setValues = function(name, description, lang) {
	var i = helppage.langToIndex(lang);
	if(i < 0) {
		return;
	}
	var input_name = helppage.get('help_name_'+i);
	var input_description = helppage.get('help_description_'+i);
	if(input_name && input_description) {
		input_name.value = name;
		input_description.value = description;
	}
};

/*****************************************************************************
 * EDIT
 */

helppage.edit.load =  function() {
	helppage.edit.section.checkFirstLast();
};

helppage.edit.change = function(obj, langkey) {
	if(obj.className == 'inactive') {
		return false;
	}
	var help_langs = getFormValue('help_lang');
	var help_names = getFormValue('help_name');
	var help_descriptions = getFormValue('help_description');
	for(var i=0; i < help_langs.length; i++) {
		if(help_langs[i] == langkey) {
			helppage.get('help-name').innerHTML = help_names[i];
			helppage.get('help-description').innerHTML = help_descriptions[i].replace(/\n/g, "<br />");
			var flags = jQuery.find('#help-langs a');
			for(var j=0; j < flags.length; j++) {
				if(flags[j].className == 'current') {
					flags[j].className = 'active';
				}
			}
			break;
		}
	}
	obj.className = 'current';
	return false;
};

helppage.edit.subwindow = function(event,attrname,seckey) {
	var o = helppage.get('tispecial'+attrname);
	if(seckey) {
		helppage.get('edit_section_key').value = seckey;
	}
	if(o) {
		o.onclick.apply(null,[event]);
	}
	else {
		alert('Ooops, something that\'s shouldn\'t occur just happens !');
	}
	return false;
};

/*****************************************************************************
 * EDIT CALLS
 */

// helpcallReloadLangs
helppage.edit.calls.reloadLangs = function() {
	// init
	var help_langs = getFormValue('help_lang');

	var current_lang = '';
	var new_current = '';
	// no lang at all
	if(!help_langs) {
		for(var lang in helppage.langs) {
			var a = helppage.get('help-langs-'+lang);
			if(a) {
				a.className = 'inactive';
			}
		}
		helppage.get('help-name').innerHTML = '';
		helppage.get('help-description').innerHTML = '';
		return true;
	}
	// search current lang
	for(var lang in helppage.langs) {
		var a = helppage.get('help-langs-'+lang);
		if(a && a.className == 'current') {
			current_lang = lang;
			break;
		}
	}
	// redefine class for flag links
	for(var lang in helppage.langs) {
		var a = helppage.get('help-langs-'+lang);
		if(!a) {
			continue;
		}
		var found = -1;
		for(var i=0; i< help_langs.length; i++) {
			if(help_langs[i] == lang) {
				found = i;
				break;
			}
		}
		if(found >= 0) {
			if(lang == current_lang) {
				a.className = 'current';
				new_current = lang;
				helppage.edit.change(a, lang);
			}
			else {
				a.className = 'active';
			}
		}
		else {
			a.className = 'inactive';
		}
	}
	// if no current lang, set current the first active
	if(new_current == '') {
		for(var lang in helppage.langs) {
			var a = helppage.get('help-langs-'+lang);
			if(a && a.className == 'active') {
				a.className == 'current';
				new_current = lang;
				helppage.edit.change(a, lang);
			}
		}
	}
	// if always no current lang, empty fields
	if(new_current == '') {
		helppage.get('help-name').innerHTML = '';
		helppage.get('help-description').innerHTML = '';
	}
	return true;
};

// helpcallGetCurrentLang
helppage.edit.calls.getCurrentLang = function() {
	for(var lang in helppage.langs) {
		var a = helppage.get('help-langs-'+lang);
		if(a && a.className == 'current') {
			return lang;
		}
	}
	return false;
};

// helpcallGetCurrentSectionLang
helppage.edit.calls.getCurrentSectionLang = function(seckey) {
	var items = jQuery.find('#sec-langs-'+seckey+' a');
	var found = -1;

	for(var i=0; i < items.length; i++) {
		if(items[i].className == 'current') {
			found = i;
			break;
		}
	}
	var i = 0;
	for(var lang in helppage.langs) {
		if(i == found) {
			return lang;
		}
		i++;
	}
	for(var lang in helppage.langs) {
		return lang;
	}
	return false;
};

// helpcallGetSection
helppage.edit.calls.getSection = function(seckey) {
	var sec_names = getFormValue('help_sec_name');
	var sec_langs = getFormValue('help_sec_lang');
	var sec_keys = getFormValue('help_sec_key');
	var sec_texts = getFormValue('help_sec_text');

	// init
	var res = [];
	for(var lang in helppage.langs) {
		res[lang] = { 'name': '', 'text': '' };
	}

	if(!sec_keys || sec_keys.length == 0) {
		return res;
	}

	// load
	for(var i=0; i< sec_keys.length; i++) {
		if(sec_keys[i] == seckey) {
			res[sec_langs[i]].name = sec_names[i];
			res[sec_langs[i]].text = sec_texts[i];
		}
	}
	return res;
};

// helpcallGetSectionOrder
helppage.edit.calls.getSectionOrder = function(seckey) {

	var sec_orders = getFormValue('help_sec_order');
	var sec_keys = getFormValue('help_sec_key');

	if(!sec_keys || sec_keys.length == 0) {
		return 0;
	}

	for(var i=0; i< sec_keys.length; i++) {
		if(sec_keys[i] == seckey) {
			return sec_orders[i];
		}
	}
	return 0;
};

// helpcallSaveSection
helppage.edit.calls.saveSection = function(config) {

	// check rows to delete
	var trs = helppage.edit.tools.getTableRows();
	var trToRemove = [];
	for(var i = 0; i < trs.length; i++) {
		var found = false;
		var inputs = jQuery.find('input', trs[i]);
		for(var k=0; k < inputs.length; k++) {
			if(inputs[k].name.substr(0, 13) == '_help_sec_key' && inputs[k].value == config.seckey) {
				found = true;
			}
		}
		if(found) {
			trToRemove.push(trs[i]);
		}
	}
	
	// remove rows
	var tbody = helppage.get('tbodyhelp_t_sections');
	for(var i = 0; i < trToRemove.length; i++) {
		tbody.removeChild(trToRemove[i]);
	}

	// add rows
	var current_found = false;
	var firstlang = false;
	for(var lang in config.langs) {
		if(lang.match(/^[a-z]{2}_[a-z]{2}$/i)) {
			if(config.langs[lang].name || config.langs[lang].text) {
				firstlang = lang;
				addTableRow({
					help_sec_order: config.secorder,
					help_sec_key: config.seckey,
					help_sec_lang: lang,
					help_sec_name: config.langs[lang].name,
					help_sec_text: config.langs[lang].text
				});
				if(config.curlang == lang) {
					config.langs[lang].classname = 'current';
					current_found = true;
				}
				else {
					config.langs[lang].classname = 'active';
				}
			}
			else {
				config.langs[lang].classname = 'inactive';
			}
			helppage.get('sec-name-'+config.seckey+'-'+lang).innerHTML = config.langs[lang].name;
			helppage.get('sec-text-'+config.seckey+'-'+lang).innerHTML = config.langs[lang].text.replace(/\n/, '<br />');
		}
	}
	if(!current_found) {
		if(firstlang) {
			config.langs[firstlang].classname = 'current';
		}
		else {
			var sec = helppage.get('sec-'+config.seckey);
			helppage.get('help-sections').removeChild(sec);
			try {
				helppage.edit.tools.reOrder(config.secorder);
				helppage.edit.section.checkFirstLast();
			} catch(e) {}
			return false;
		}
	}
	// update text
	var items = jQuery.find('#sec-langs-'+config.seckey+' a');
	var item_current;
	var i = 0;
	for(var lang in helppage.langs) {
		items[i].className = config.langs[lang].classname;
		if(config.langs[lang].classname == 'current') {
			helppage.edit.section.changeLang(items[i],config.seckey,lang);
		}
		i++;
	}
};

/*****************************************************************************
 * EDIT TOOLS
 */

helppage.edit.tools.getMinMax = function() {
	var min = 999999;
	var max = -1;
	var sec_orders = getFormValue('help_sec_order');
	for(var i=0; i< sec_orders.length; i++) {
		if(sec_orders[i] > max) {
			max = parseInt(sec_orders[i]);
		}
		if(sec_orders[i] < min) {
			min = parseInt(sec_orders[i]);
		}
	}
	if(min == 999999) {
		return false;
	}
	if(max == -1) {
		return false;
	}
	return {
		'min': min,
		'max': max
	};
};

helppage.edit.tools.seckeyToOrder = function(seckey) {
	var sec_orders = getFormValue('help_sec_order');
	var sec_keys = getFormValue('help_sec_key');

	// load sections
	for(var i=0; i< sec_keys.length; i++) {
		if(seckey == sec_keys[i]) {
			return parseInt(sec_orders[i]);
		}
	}
	return -1;
};

helppage.edit.tools.orderToSeckey = function(order) {
	var sec_orders = getFormValue('help_sec_order');
	var sec_keys = getFormValue('help_sec_key');

	// load sections
	for(var i=0; i< sec_keys.length; i++) {
		if(order == sec_orders[i]) {
			return sec_keys[i];
		}
	}
	return false;
};

helppage.edit.tools.getTableRows = function() {
	var rows = [];
	var trs = jQuery.find('#tbodyhelp_t_sections > tr');
	for(var i=0; i < trs.length; i++) {
		if(trs[i].id && trs[i].id.match(/^lasttr/i)) {
			break;
		}
		rows.push(trs[i]);
	}
	return rows;
};

helppage.edit.tools.incrementOrders = function() {
	var trs = helppage.edit.tools.getTableRows();
	for(var i=0; i < trs.length; i++) {
		var inputs = jQuery.find('input', trs[i]);
		for(var k=0; k < inputs.length; k++) {
			var input = inputs[k];
			if(input.name && input.name.match(/^_help_sec_order/i)) {
				input.value = parseInt(input.value) + 1;
			}
		}
	}
};

helppage.edit.tools.reOrder = function(secorder) {
	var trs = helppage.edit.tools.getTableRows();
	for(var i=0; i < trs.length; i++) {
		var inputs = jQuery.find('input', trs[i]);
		for(var k=0; k < inputs.length; k++) {
			var input = inputs[k];
			if(input.name && input.name.match(/^_help_sec_order/i) && input.value > secorder) {
				input.value = parseInt(input.value) - 1;
			}
		}
	}
};

/*****************************************************************************
 * EDIT SECTION
 */

helppage.edit.section.add = function(seckey) {
	var item = {
		help_sec_order: 1,
		help_sec_key: seckey,
		help_sec_lang: '',
		help_sec_name: helppage.edit.params.msg5+" : "+seckey,
		help_sec_text: ''
	};

	helppage.edit.tools.incrementOrders();

	// get lang
	for(var lang in helppage.langs) {
		item.help_sec_lang = lang;
		break;
	}

	addTableRow(item);

	// add element in DOM
	var div = document.createElement('div');
	div.id = 'sec-'+seckey;
	div.className = 'help-section';
	div.innerHTML = helppage.edit.section.template.replace(/\{SECKEY\}/gi, seckey);

	// try to find first correct child
	var sections = jQuery.find('#help-sections .help-section');
	if(sections && sections.length > 0) {
		helppage.get('help-sections').insertBefore(div, sections[0]);
	}
	else {
		helppage.get('help-sections').appendChild(div);
	}
	helppage.edit.section.checkFirstLast();
};

helppage.edit.section.exists = function(seckey) {
	var obj = helppage.get('sec-'+seckey);
	if(obj) {
		return true;
	}
	else {
		return false;
	}
};

helppage.edit.section.changeLang = function(obj, seckey, langkey) {
	if(obj.className == 'inactive') {
		return false;
	}
	var i=0;
	var childs = jQuery.find('#sec-langs-'+seckey+' a');
	for(var lang in helppage.langs) {
		if(childs[i].className != 'inactive') {
			childs[i].className = 'active';
		}
		if(lang == langkey) {
			helppage.get('sec-'+seckey+'-'+lang).style.display='block';
		}
		else {
			helppage.get('sec-'+seckey+'-'+lang).style.display='none';
		}
		i++;
	}
	obj.className = 'current';
	return false;
};

helppage.edit.section.toggle = function(id) {
	for(var lang in helppage.langs) {
		var oname = helppage.get('sec-name-'+id+'-'+lang);
		var otext = helppage.get('sec-text-'+id+'-'+lang);
		if(oname && otext) {
			if(otext.style.display == 'none') {
				otext.style.display = 'block';
				oname.className = 'help-section-title';
			}
			else {
				otext.style.display = 'none';
				oname.className = 'help-section-title section-closed';
			}
		}
	}
};

helppage.edit.section.checkFirstLast = function() {

	var sec_orders = getFormValue('help_sec_order');
	var sec_keys = getFormValue('help_sec_key');
	var first,last;
	var last_order = -1;

	if(!sec_keys) {
		sec_keys = [];
	}
	if(!sec_orders) {
		sec_orders = [];
	}

	// load sections
	for(var i=0; i< sec_keys.length; i++) {
		if(!helppage.get('sec-'+sec_keys[i])) {
			continue;
		}
		helppage.get('sec-'+sec_keys[i]).className = 'help-section';
		if(sec_orders[i] == 1) {
			first = sec_keys[i];
		}
		if(sec_orders[i] > last_order) {
			last_order = sec_orders[i];
			last = sec_keys[i];
		}
	}

	if(first) {
		helppage.get('sec-'+first).className += ' help-first';
	}
	if(last) {
		helppage.get('sec-'+last).className += ' help-last';
	}
};

helppage.edit.section.down = function(seckey) {
	var minmax = helppage.edit.tools.getMinMax();
	var secorder = helppage.edit.tools.seckeyToOrder(seckey);
	// checks
	if(!minmax || secorder < 0 || secorder == minmax.max) {
		return false;
	}
	var tmp;
	// change values in array HTML
	var trs = helppage.edit.tools.getTableRows();
	for(var i=0; i < trs.length; i++) {
		var inputs = jQuery.find('input', trs[i]);
		for(var k=0; k < inputs.length; k++) {
			var input = inputs[k];
			if(input.name && input.name.match(/^_help_sec_order/i)) {
				var order = parseInt(input.value);
				if(order == secorder) {
					input.value = order + 1;
				}
				else if (order == secorder + 1) {
					input.value = order - 1;
				}
			}
		}
	}
	// move DOM
	var div_parent = helppage.get('help-sections');
	var div = helppage.get('sec-'+seckey);
	var div2 = helppage.get('sec-'+helppage.edit.tools.orderToSeckey(secorder+2));
	if(div2) {
		tmp = div_parent.removeChild(div);
		div_parent.insertBefore(tmp, div2);
	}
	else {
		tmp = div_parent.removeChild(div);
		div_parent.appendChild(tmp);
	}
	helppage.edit.section.checkFirstLast();
	return false;
};

helppage.edit.section.up = function(seckey) {
	var minmax = helppage.edit.tools.getMinMax();
	var secorder = helppage.edit.tools.seckeyToOrder(seckey);
	// checks
	if(!minmax || secorder < 0 || secorder == minmax.min) {
		return false;
	}
	var tmp;
	// change values in array HTML
	var trs = helppage.edit.tools.getTableRows();
	for(var i=0; i < trs.length; i++) {
		var inputs = jQuery.find('input', trs[i]);
		for(var k=0; k < inputs.length; k++) {
			var input = inputs[k];
			if(input.name && input.name.match(/^_help_sec_order/i)) {
				var order = parseInt(input.value);
				if(order == secorder) {
					input.value = order - 1;
				}
				else if (order == secorder - 1) {
					input.value = order + 1;
				}
			}
		}
	}
	// move DOM
	var div_parent = helppage.get('help-sections');
	var div = helppage.get('sec-'+seckey);
	var div2 = helppage.get('sec-'+helppage.edit.tools.orderToSeckey(secorder));
	if(div2) {
		tmp = div_parent.removeChild(div);
		div_parent.insertBefore(tmp, div2);
	}
	else {
		tmp = div_parent.removeChild(div);
		div_parent.appendChild(tmp);
	}
	helppage.edit.section.checkFirstLast();
	return false;
};

/*****************************************************************************
 * EDIT POPUP
 */

helppage.edit.popup.close = function() {
	var div_s = helppage.get('HELPPOPUP_s');
	var div_b = helppage.get('HELPPOPUP_b');
	var body = document.body;
	if(div_s && body) {
		body.removeChild(div_s);
	}
	if(div_b && body) {
		body.removeChild(div_b);
	}
};

helppage.edit.popup.get_objects = function() {
	var datas = {
		check1: null,
		check2: null,
		select: null,
		input: null
	};
	var i;
	var inputs = jQuery.find('#HELPPOPUP_c input');
	for(i in inputs) {
		if(inputs[i].name == 'help_popup_key') {
			datas.input = inputs[i];
		}
		else if(inputs[i].name == 'help_popup_type') {
			if(datas.check1) {
				datas.check2 = inputs[i];
			}
			else {
				datas.check1 = inputs[i];
			}
		}
	}
	var selects = jQuery.find('#HELPPOPUP_c select');
	for(i in selects) {
		if(selects[i].name == 'help_popup_select') {
			datas.select = selects[i];
		}
	}
	return datas;
};

helppage.edit.popup.add = function() {
	var datas = helppage.edit.popup.get_objects();
	var seckey = '';
	if(datas.check1 && datas.check1.checked) {
		if(datas.check2) {
			seckey = datas.select.value;
		}
		else {
			seckey = datas.input.value;
		}
	}
	else if(datas.check2 && datas.check2.checked) {
		seckey = datas.input.value;
	}
	if(!seckey) {
		displayWarningMsg(helppage.edit.params.msg1);
	}
	else if(!seckey.match(/^[a-z0-9_-]+$/i)) {
		displayWarningMsg(helppage.edit.params.msg2);
	}
	else if(helppage.edit.section.exists(seckey)) {
		displayWarningMsg(helppage.edit.params.msg3);
	}
	else {
		helppage.edit.section.add(seckey);
		helppage.edit.popup.close();
	}
};

helppage.edit.popup.input_change = function(value) {
	var datas = helppage.edit.popup.get_objects();
	if(datas.check2) {
		datas.check2.checked = true;
	}
};

helppage.edit.popup.select_change = function(value) {
	var datas = helppage.edit.popup.get_objects();
	if(datas.check1) {
		datas.check1.checked = true;
	}
};

helppage.edit.popup.show = function(event) {
	if (event) {
		event.cancelBubble=true;
	}
	GetXY(event);
	var x=Xpos;
	var y=Ypos;
	var w=380;
	var h=130;
	helppage.edit.popup.close();
	var html = helppage.get('help-section-add-div').innerHTML;
	new popUp(x, y, w, h, 'HELPPOPUP', html,
		helppage.edit.params.color5,
		helppage.edit.params.color6,
		'16pt serif',
		helppage.edit.params.msg4,
		helppage.edit.params.color1,
		helppage.edit.params.color2,
		helppage.edit.params.color3,
		helppage.edit.params.color4,
		'black', true, true, true, true, false, false,true);
	var datas = helppage.edit.popup.get_objects();
	if(datas.check1) {
		datas.check1.checked = true;
		if(!datas.check2) {
			datas.check1.style.display = 'none';
		}
	}
};

/*****************************************************************************
 * VIEW
 */

helppage.view.changeall = function(lang) {
	helppage.view.changeallchecks(lang);
	var i, descsel;
	var sp = jQuery.find('#helppage-description span');
	if (sp && sp.length > 0) {
		for (i = 0;i<sp.length;i++ ) {
			sp[i].style.display = 'none';
		}
		descsel = helppage.get('helppage-desc-'+lang);
		if (descsel) descsel.style.display = '';
		else if (sp.length > 0) {
			sp[0].style.display = '';
		}
	}
	sp = jQuery.find('#helppage-titles span');
	if (sp && sp.length > 0) {
		for (i = 0;i<sp.length;i++ ) {
			sp[i].style.display = 'none';
		}
		descsel = helppage.get('helppage-title-'+lang);
		if (descsel) {
			descsel.style.display = '';
			document.title = descsel.innerHTML;
		} else if (sp.length > 0) {
			sp[0].style.display = '';
			document.title = sp[0].innerHTML;
		}
	}
	helppage.view.resize();
	return false;
};

helppage.view.changeallcheck = function(lang, secid) {
	var o1 = helppage.get(secid);
	var o2 = helppage.get(secid.substr(0, secid.length-5)+lang);
	if(o1 && o2) {
		if(secid.substr(secid.length-5, 5) == lang) {
			o1.style.display = 'block';
			var ldiv = jQuery.find('div', o1);
			for (var i = 0;i<ldiv.length;i++) {
				if (ldiv[i].className == 'help-section-title') {
					if (ldiv[i].innerHTML) {
						var secname = secid.substr(4,secid.length-10);
						var sec = helppage.get('helppage-sec-'+secname);
						if (sec) {
							sec.innerHTML = ldiv[i].innerHTML;
						}
					}
				}
			}
		} else if(o2.className !=  'inactive') {
			o1.style.display = 'none';
		}
	}
};

helppage.view.changeallchecks = function(lang) {
	var divs = jQuery.find('#helppage-content .help-section-item');
	for(var i = 0; i < divs.length; i++) {
		helppage.view.changeallcheck(lang, divs[i].id);
	}
};

helppage.view.goto = function(sec) {
	var o_sec = helppage.get(sec);
	if(o_sec) {
		var content = helppage.get('helppage-content');
		content.scrollTop = o_sec.offsetTop - content.offsetTop;
	}
	return false;
};

helppage.view.checkdirectaccess = function() {
	var loc = document.location+'';
	var m = [];
	if(m = loc.match(/\#(.*)$/)) {
		helppage.view.goto('sec-'+m[1]);
	}
};

helppage.view.change = function(src, dst, sectitle) {
	var o_src = helppage.get(src);
	var o_dst = helppage.get(dst);
	if(o_src && o_dst) {
		o_src.style.display = 'none';
		o_dst.style.display = 'block';
		var ldiv = jQuery.find('div', o_dst);
		for (var i = 0 ; i < ldiv.length ; i++) {
			if (ldiv[i].className == 'help-section-title') {
				if (ldiv[i].innerHTML) {
					var sec = helppage.get(sectitle);
					if (sec) {
						sec.innerHTML = ldiv[i].innerHTML;
					}
				}
			}
		}
	}
	return false;
};

helppage.view.resize = function() {
	var div_main = helppage.get('helppage-main');
	var div_left = helppage.get('helppage-left');
	var div_right = helppage.get('helppage-right');
	var div_sections = helppage.get('helppage-sections');
	var div_aides = helppage.get('helppage-aides');
	var div_content = helppage.get('helppage-content');

	var h = getFrameHeight() - div_main.offsetTop - 12;
	var h_title = getObjectHeight('helppage-title');
	var h_subtitle = getObjectHeight('helppage-content-title');

	if(getObjectHeight('helppage-aides-title') > h_subtitle) {
		h_subtitle = getObjectHeight('helppage-aides-title');
	}
	if(getObjectHeight('helppage-sections-title') > h_subtitle) {
		h_subtitle = getObjectHeight('helppage-sections-title');
	}

	div_main.style.height = h + 'px';
	div_left.style.height = (h - h_title)+'px';
	div_right.style.height = (h - h_title)+'px';

	div_content.style.height = (h - h_title - h_subtitle - 10)+'px';

	if(2*getObjectHeight('helppage-sections') + 2*h_subtitle + h_title > h) {
		div_sections.style.height = (Math.floor((h - h_title - 2*h_subtitle)/2) - 10)+'px';
	}
	div_aides.style.height = (h - 2*h_subtitle - h_title - getObjectHeight('helppage-sections') - 10)+'px';
};
