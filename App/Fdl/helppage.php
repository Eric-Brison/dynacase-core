<?php

/**
 * return dynamic enum from family parameters
 * @return string
 */
function helppageenumlang() {

	$dbaccess = getParam("FREEDOM_DB");
	if (!is_numeric($famid)) {
		$famid = getFamIdFromName($dbaccess, 'HELPPAGE');
	}
	$doc = new_Doc($dbaccess, $famid);
	if ($doc->isAlive()) {
		$all_lang_keys = $doc->_val2array($doc->getParamValue('help_p_lang_key'));
		$all_lang_texts = $doc->_val2array($doc->getParamValue('help_p_lang_name'));
		$langs = array();
		foreach($all_lang_keys as $i => $key) {
			$langs[] = $key.'|'.$all_lang_texts[$i];
		}
		return implode(',', $langs);
	}
	return 'fr_FR|'._('french');
}


function helppage_editrubrique(Action &$action,$dbaccess,$docid) {
	$action->lay->set();

	//GetHttpVars($name);
}

function helppage_edithelp(Action &$action,$dbaccess,$docid) {

	include_once('FDL/editutil.php');
	editmode($action);
	
	$doc = new_Doc($dbaccess, $docid);
	$action->lay->set('DOCID', $docid);
	$action->lay->set('DOCTITLE', $doc->getTitle());

	$helps = $doc->getHelpByLang();
	$langs = $doc->getFamilyLangs();

	$helplangs = array();
	$index = 0;
	$displayed = false;
	foreach($langs as $lang_key => $lang_name) {
		$item = array();
		$item['LANGKEY'] = $lang_key;
		$item['LANGISO'] = strtolower(substr($lang_key, -2));
		$item['LANGNAME'] = $lang_name;
		$item['LANGDISPLAY'] = 'none';
		$oa1 = $doc->getAttribute('help_name');
		$oa2 = $doc->getAttribute('help_description');
		if(array_key_exists($lang_key, $helps)) {
			if(!$displayed) {
				$item['LANGDISPLAY'] = 'block';
				$displayed = true;
			}
			$item['INPUTLANGNAME'] = getHtmlInput($doc, $oa1, $helps[$lang_key]['help_name'], $index, '', true);
			$item['INPUTLANGDESCRIPTION'] = getHtmlInput($doc, $oa2, $helps[$lang_key]['help_description'], $index, '', true);
		}
		else {
			$item['INPUTLANGNAME'] = getHtmlInput($doc, $oa1, '', $index, '', true);
			$item['INPUTLANGDESCRIPTION'] = getHtmlInput($doc, $oa2, '', $index, '', true);
		}
		$index++;
		$helplangs[] = $item;
	}
	if(!empty($helplangs) && !$displayed) {
		$helplangs[0]['LANGDISPLAY'] = 'block';
	}
	$action->lay->SetBlockData('HELPLANGS', $helplangs);
	$action->lay->SetBlockData('MENULANGS1', $helplangs);
	$action->lay->SetBlockData('MENULANGS2', $helplangs);
	
foreach(explode("\n", print_r($helplangs, true)) as $tmp) {error_log($tmp);}

	$action->lay->set('DOCID', $docid);
	//$action->lay->set();
	
	//GetHttpVars($name);
}
?>