
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

if (!window.console) {
	console = {};
	console.log = function () {
	};
}

// use when submit to avoid first unused item
function deletenew() {
	if (canmodify(true)) {
		resetInputs('newcond');
		var na=document.getElementById('newcond');
		if (na) na.parentNode.removeChild(na);
		na=document.getElementById('newstate');
		if (na) na.parentNode.removeChild(na);
	}
  
}
  

function sendsearch(faction,artarget) {
	var fedit = document.fedit;
	resetInputs('newcond');
  
	with (document.modifydoc) {
		var editAction=action;
		var editTarget=target;

		enableall();
		var na=document.getElementById('newcond');
		
		if (na) {
			disabledInput(na,true);
		    nt=document.getElementById('newstate');
			if (nt)   disabledInput(nt,true);
		}
		if ((!artarget) &&  (window.parent.fvfolder)) artarget='fvfolder';
		else if ((!artarget) &&  (window.parent.flist)) {
			artarget='flist';
			faction=faction + '&ingeneric=yes';
		} else  if (!artarget) artarget='_blank';
		target=artarget;
		action=faction;
		submit();
		target=editTarget;
		action=editAction;

    
		if (na) {
			disabledInput(na,false);
			if (nt) disabledInput(nt,false);
		}
    
		}
}

/**
 * Extend jQuery to add a stash/unstash function
 * to move/restore childs from an element into a
 * hidden <div/>
 */
(function($){
	/**
	 * Move childs nodes into an undisplayed <div class="stash"/>
	 * and disable form elements from submission.
	 */
	$.fn.stash = function() {
		return this.each(function() {
			var stash = $(this).children('.stash').get(0);
			if (!stash) {
				stash = $('<div></div>').addClass('stash').css('display', 'none');
				$(this).prepend(stash);
			}
			$(this).children().not('.stash').each(function(index, elmt) {
				if (elmt.tagName == 'INPUT' || elmt.tagName == 'SELECT' || elmt.tagName == 'BUTTON' || elmt.tagName == 'TEXTAREA') {
					var name = elmt.getAttribute('name');
					if (name != null) {
						elmt.setAttribute('data-stash-original-name', name);
						elmt.removeAttribute('name');
					}
				}
				$(elmt).remove();
				$(stash).append(elmt);
			});
		});
	}

	/**
	 * Move back stashed elements as childs of an element
	 */
	$.fn.unstash = function() {
		return this.each(function() {
			var self = this;
			var stash = $(this).children('.stash').get(0);
			if (!stash) {
				return;
			}
			$(this).children().not('.stash').remove();
			$(stash).children().each(function(index, elmt) {
				if (elmt.tagName == 'INPUT' || elmt.tagName == 'SELECT' || elmt.tagName == 'BUTTON' || elmt.tagName == 'TEXTAREA') {
					var original_name = elmt.getAttribute('data-stash-original-name');
					if (original_name != null) {
						elmt.removeAttribute('data-stash-original-name');
						elmt.setAttribute('name', original_name);
					}
				}
				$(elmt).remove();
				$(self).append(elmt);
			});
		});
	}
})(jQuery);

function callFunction(event,th) {
	var famid = $('#famid').get(0).value;
	var attrid = $(th).parent().parent().find('select[name="_se_attrids[]"] option:selected').get(0).value;
	var targetCell = getPrevElement($(th).parent().get(0));

	if ($(targetCell).data('issearchmethod') == 'yes') {
		// Get current input value
		var input = $(targetCell).children('input').get(0);
		var inputValue = $(input).val();
		// Unstash
		$(targetCell).unstash();
		// Set back input value on unstashed input
		input = $(targetCell).children('input').get(0);
		if (input) {
			$(input).val(inputValue);
		}
		// Unmark cell as being a search method
		$(targetCell).data('issearchmethod', 'no');
		return;
	}

	if (famid == '') {
		alert('Empty famid value');
		return;
	}
	if (attrid == '') {
		alert('Empty attrid value');
		return;
	}

	globalcursor('wait');
	getSearchMethods({
		famid: famid,
		attrid: attrid,
		cbdata: {
			'td': targetCell
		},
		success: function (data, args) {
			var targetCell = args.td;
			if (!targetCell) {
				unglobalcursor();
				return;
			}
			// Get current input value
			var input = $(targetCell).children('input').get(0);
			var inputValue = $(input).val();
			// Stash elements
			$(targetCell).stash();
			// Create input + select
			var methodSelectElmt = $('#method-selector').get(0).cloneNode(true);
			methodSelectElmt.removeAttribute('id');
			var input = document.createElement('input');
			input.setAttribute('type', 'text');
			input.setAttribute('size', '20');
			input.setAttribute('name', '_se_keys[]');
			// Append input + select
			$(targetCell).append(input);
			$(targetCell).append(methodSelectElmt);
			// Add options from the reponse data
			for (var i = 0; i < data.length; i++) {
				var option = document.createElement('option');
				option.setAttribute('value', data[i].method);
				if (inputValue != '' && data[i].method == inputValue) {
					option.setAttribute("selected", "");
				}
				option.appendChild(document.createTextNode(data[i].label));
				methodSelectElmt.appendChild(option);
			}
			// Set method select element in target cell
			$(targetCell).append(methodSelectElmt);
			$(methodSelectElmt).show();
			// Set back current input value
			$(input).val(inputValue);
			// Mark cell as being a search method
			$(targetCell).data('issearchmethod', 'yes');
			unglobalcursor();
		},
		error: function (errmsg, args) {
			alert(errmsg);
			unglobalcursor();
		}
	});
}
function getSearchMethods(opts) {
	$.getJSON(
		'?',
		{
			app: 'FREEDOM',
			action: 'GETSEARCHMETHODS',
			famid: opts.famid,
			attrid: opts.attrid
		},
		function (data) {
			if (!data) {
				return (typeof(opts.error) == 'function') ? (opts.error)("undefined data in response", opts.cbdata) : undefined;
			}
			if (data.error) {
				return (typeof(opts.error) == 'function') ? (opts.error)(data.error, opts.cbdata) : undefined;
			}
			if (!data.data) {
				return (typeof(opts.error) == 'function') ? (opts.error)("undefined data.data", opts.cbdata) : undefined;
				return;
			}
			return (typeof(opts.success) == 'function') ? (opts.success)(data.data, opts.cbdata) : undefined;
		}
	).error(function () {
		return (typeof(opts.error) == 'function') ? (opts.error)("error retrieving data", opts.cbdata) : undefined;
	});
}
function setKey(event,th) {
	var pnode;

	pnode=th.previousSibling;
	while (pnode!=null && ((pnode.nodeType != 1) || (pnode.name != '_se_keys[]'))) pnode = pnode.previousSibling;

	pnode.value = th.options[th.selectedIndex].value;

  
}

function getNextElement(th) {
	var pnode;
	pnode=th.nextSibling;
	while (pnode && (pnode.nodeType != 1)) pnode = pnode.nextSibling;
	return pnode;
  
}

function getPrevElement(th) {
	var pnode;
	pnode=th.previousSibling;
	while (pnode && (pnode.nodeType != 1)) pnode = pnode.previousSibling;
	return pnode;
  
}

function filterfunc2(th) {
	var so=null, i;
	var pnode = th.parentNode.previousSibling;
	while (pnode && ((pnode.nodeType != 1) || (pnode.tagName != 'TD'))) pnode = pnode.previousSibling;
	for (i=0;i<pnode.childNodes.length;i++) {
		if (pnode.childNodes[i].tagName=='SELECT') {
			so=pnode.childNodes[i];
		}
	}
	if(so) {
		filterfunc(so);
	}
}

function filterfunc(th) {
	var p=th.parentNode;
	var opt=th.options[th.selectedIndex];
	var atype=opt.getAttribute('atype');
	var ismultiple=(opt.getAttribute('ismultiple')=='yes')?true:false;
	var i;
	var pnode,so=false;
	var aid=opt.value;
	var sec,se;
	var needresetselect=false,ifirst=0;
	var ex=document.getElementById('method-selector');
	var lc=document.getElementById('lastcell');

	// move to tfoot to not be removed
	if (ex)  {
		ex.style.display='none';
		lc.appendChild(ex);
		for (i=0;i<ex.options.length;i++) {
		    ex.options[i].selected=false;
		}
	}

	// search brother select input
	pnode=p.nextSibling;
	while (pnode!=null && ((pnode.nodeType != 1) || (pnode.tagName != 'TD'))) pnode = pnode.nextSibling;

 
	for (i=0;i<pnode.childNodes.length;i++) {
		if (pnode.childNodes[i].tagName=='SELECT') {
			so=pnode.childNodes[i];
		}
	}


	// display only matches
	ifirst=-1;
	for (i=0;i<so.options.length;i++) {
		opt=so.options[i];
		ctype=opt.getAttribute('ctype');
		if ( (ismultiple && (ctype=='' || ctype.indexOf('array') >= 0)) || (!ismultiple && ((ctype=='') || (ctype.indexOf(atype)>=0))) ) {
			if (ifirst == -1) ifirst=i;
			opt.style.display='';
			opt.disabled=false;
		} else {
			opt.style.display='none';
			if (opt.selected) needresetselect=true;
			opt.selected=false;
			opt.disabled=true;
		}
	}
	if (needresetselect) {
		so.options[ifirst].selected=true;
	}
	var egaloperator = false;
	if(so.value == '=' || so.value == '!=') {
		egaloperator = true;
	}


	// find key cell
	pnode=pnode.nextSibling;
	while (pnode!=null && ((pnode.nodeType != 1) || (pnode.tagName != 'TD'))) pnode = pnode.nextSibling;
	// now enum
	if ((atype=='enum') || (atype=='enumlist')) {
		se=document.getElementById('selenum'+aid);
		if (se!=null && pnode!=null) {
			pnode.innerHTML='';
			sec=se.cloneNode(true);
			sec.name='_se_keys[]';
			sec.id='';
			pnode.appendChild(sec);
		}
	} else if(atype == 'docid') {
		se=document.getElementById('thekey');
		if (se!=null && pnode!=null) {
			if(!egaloperator) {
				sec=se.cloneNode(true);
				sec.name='_se_keys[]';
				sec.id='';
				pnode.innerHTML='';
				pnode.appendChild(sec);
			}
			else {
				var famid=null;
				if(document.getElementById('famid')) {
					famid = document.getElementById('famid').value;
				}
				if(famid) {
					var html = '<input type="hidden"  name="_se_keys[]" id="'+aid+'" value="">';
					html += '<input autocomplete="off" autoinput="1" onfocus="activeAuto(event,'+famid+',this,\'\',\''+aid+'\',\'\')"   onchange="addmdocs(\'_'+aid+'\')" type="text" name="_ilink_'+aid+'" id="ilink_'+aid+'" value="">';
					pnode.innerHTML= html;
				}
				else {
					sec=se.cloneNode(true);
					sec.name='_se_keys[]';
					sec.id='';
					pnode.innerHTML='';
					pnode.appendChild(sec);
				}
			}
		}
	} else {
		se=document.getElementById('thekey');
		if (se!=null && pnode!=null) {
			sec=se.cloneNode(true);
			sec.name='_se_keys[]';
			sec.id='';
			pnode.innerHTML='';
			pnode.appendChild(sec);
		}
	}
  
}

function showModePersoIfSelected() {
    /**
     * Show parenthesis if global mode is 'perso'
     */
    if ($('select#se_ol').val() == 'perso') {
        toggleModePerso(true);
        return;
    }

    /**
     * Lookup condlist lines and show parenthesis if
     * parenthesis select is 'yes' or operator is 'and' or 'or'
     */
    var selectList = $('#condlist select.modeperso');
    var visible = false;
    for (var i = 0; i < selectList.length; i++) {
        if (selectList[i].value == 'yes' || selectList[i].value == 'and' || selectList[i] == 'or') {
            visible = true;
            break;
        }
    }

    if (visible) {
        $('select#se_ol').val('perso');
    }
    toggleModePerso(visible);
}

function toggleModePerso(visible) {
    if (typeof visible != "boolean") {
        return;
    }

    /**
     * Show/hide parenthesis controls and
     */
    $('span.modeperso-header').toggle(visible);
    $('select.modeperso').toggle(visible);

    if (visible) {
        /**
         * Remove the "global" operator in perso mode and
         * set to default "and"
         */
        removeGlobalOperator();
    } else {
        /**
         * Add the "global" operator in perso mode and
         * set to default ""
         */
        addGlobalOperator();

        /**
         * Set parenthesis to default "no"
         */
        $.merge($('select[name="_se_leftp[]"]'), $('select[name="_se_rightp[]"]')).each(
            function (index, elmt) {
                $(elmt).val("no");
            }
        );
    }

    refreshCondList();
}

function removeGlobalOperator() {
    $('select[name="_se_ols[]"] > option[value=""]').each(
        function (index, elmt) {
            if ($(this).parent().val() == '') {
                $(this).parent().val("and");
            }
            $(this).remove();
        }
    );
}

function addGlobalOperator() {
    $('select[name="_se_ols[]"]').each(
        function (index, elmt) {
            var option = document.createElement('option');
            option.appendChild(document.createTextNode("global"));
            option.value = "";
            $(this).prepend(option);
            $(this).val("");
        }
    )
}

function refreshCondList() {
    $('#condlist select[name="_se_ols[]"]:eq(0)').toggle(false);
}

function initializeMethodSelectors() {
	$('td[data-issearchmethod="yes"]').each(function(index, elmt) {
		$(elmt).data('issearchmethod', 'no');
		var sigmaCell = getNextElement(elmt);
		if (sigmaCell) {
			var input = $(sigmaCell).children('input').get(0);
			if (input) {
				callFunction(null, input);
			}
		}
	});
}

function initializeSysFamSelector(select, input) {
	if (!select) {
		return;
	}
	if (select.peer) {
		return;
	}
	if (!input) {
		return;
	}

	/* Create the hidden select that will hold all the families */
	select.peer = document.createElement('select');
	select.peer.style.display = 'none';
	select.peer.peer = select;
	select.parentNode.insertBefore(select.peer, select);

	/* Copy all options to the hidden select
	 * and only keep non-system families in
	 * the main select
	 */
	var options = $(select).children('option');
	for (var i = 0; i < options.length; i++) {
		var sysfam = $(options[i]).data('sysfam');
		select.peer.appendChild(options[i].cloneNode(true));
		if (!input.checked && sysfam == 'yes') {
			select.removeChild(options[i]);
		}
	}

	select.se_sysfam_input = input;

	$(input).bind('click', function() {
		setSysFamSelector(select);
	});

	setSysFamSelector(select);
}

function setSysFamSelector(select) {
	if (!select || !select.peer || !select.se_sysfam_input) {
		return;
	}
	var selectedText = select.options[select.selectedIndex].text;
	if (select.se_sysfam_input.checked) {
		/*
		 * Show system families
		 */
		/* Empty main <select/> */
		$(select).children('option').remove();
		/* Copy back options from peer <select/> into main <select/> */
		var options = $(select.peer).children('option');
		for (var i = 0; i < options.length; i++) {
			option = options[i].cloneNode(true);
			if (option.text == selectedText) {
				option.selected = true;
			} else {
				option.selected = false;
			}
			select.appendChild(option);
		}
	} else {
		/*
		 * Hide systems families
		 */
		var options = $(select).children('option');
		/* Remove system families from the main <select/> */
		for (var i = 0; i < options.length; i++) {
			if ($(options[i]).data('sysfam') == 'yes') {
				select.removeChild(options[i]);
			}
		}
	}
}

$(document).ready(function () {
	initializeMethodSelectors();
});