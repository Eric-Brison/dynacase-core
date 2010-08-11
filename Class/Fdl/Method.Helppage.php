<?php

/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocText.php,v 1.2 2003/08/18 15:47:04 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Method.DocText.php,v 1.2 2003/08/18 15:47:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Method.DocText.php,v $
// ---------------------------------------------------------------

/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
Class _HELPPAGE extends Doc {
	/*
	 * @end-method-ignore
	 */

	public $defaultview = 'FDL:VIEWHELPPAGE:T';
	//public $defaultedit = 'FDL:EDITHELPPAGE';

	public function  preEdition() {
		$oa = $this->getAttribute('help_rub_text');
		$oa->type = 'longtext';
	}
	/**
	 *
	 * @return array
	 */
	public function getRubriquesByLang() {
		$rows = $this->getAValues('help_t_rubriques');

		$rubriques = array();
		foreach($rows as $row) {
			$key = str_pad($row['help_rub_ordre'], 8, '0', STR_PAD_LEFT).$row['help_rub_key'];
			$rubriques[$key][$row['help_rub_lang']] = $row;
		}
		ksort($rubriques);

		return $rubriques;
	}
	/**
	 *
	 * @return array
	 */
	public function getFamilyLangs() {
		$all_lang_keys = $this->_val2array($this->getParamValue('help_p_lang_key'));
		$all_lang_texts = $this->_val2array($this->getParamValue('help_p_lang_name'));
		$all_langs = array();
		foreach($all_lang_keys as $i => $key) {
			$all_langs[$key] = $all_lang_texts[$i];
		}
		return $all_langs;
	}
	/**
	 *
	 * @global  $action
	 * @return string
	 */
	public function getUserLang() {
		global $action;
		$user_lang = $action->getParam('CORE_LANG');
		return $user_lang;
	}
	/**
	 *
	 * @return array
	 */
	public function getHelpByLang() {
		$rows = $this->getAValues('help_t_aide');

		$helps = array();
		foreach($rows as $row) {
			$helps[$row['help_lang']] = $row;
		}

		return $helps;
	}
	/**
	 * 
	 */
	public function edithelppage() {

		$langs = $this->getFamilyLangs();
		$user_lang = $this->getUserLang();
		$rubriques = $this->getRubriquesByLang();
		
		$this->editattr();

		$help_values = $this->getHelpByLang();
foreach(explode("\n", print_r($help_values, true)) as $tmp) {error_log($tmp);}
foreach(explode("\n", print_r($langs, true)) as $tmp) {error_log($tmp);}
foreach(explode("\n", print_r($user_lang, true)) as $tmp) {error_log($tmp);}

		// set help values
		$helpname = '';
		$helplangiso = '';
		$lang_key = '';
		// search user lang
		foreach($help_values as $lang => $help) {
			if($lang == $user_lang) {
				$lang_key = $lang;
				$helpname = $help['help_name'];
				$helpdescription = $help['help_description'];
				break;
			}
		}
		if(empty($lang_key)) {
			// search first lang
			foreach($help_values as $lang => $help) {
				$lang_key = $lang;
				$helpname = $help['help_name'];
				$helpdescription = $help['help_description'];
			}
		}
		$this->lay->set('HELPID', $this->id);
		$this->lay->set('HELPNAME', $helpname);
		$this->lay->set('HELPDESCRIPTION', $helpdescription);

foreach(explode("\n", print_r($lang_key, true)) as $tmp) {error_log($tmp);}
foreach(explode("\n", print_r($this->getLangsFromItem($langs, $lang_key, $help_values), true)) as $tmp) {error_log($tmp);}

		$this->lay->SetBlockData('HELPLANGS', $this->getLangsFromItem($langs, $lang_key, $help_values));

	}
	/**
	 *
	 * @global <type> $action
	 * @param <type> $target
	 * @param <type> $ulink
	 * @param <type> $abstract
	 */
	public function viewhelppage($target="_self",$ulink=true,$abstract=false) {
		global $action;

		include_once("FDL/Class.SearchDoc.php");

		$this->lay->set('HELPTITLE', $this->getTitle());

		if($this->CanEdit() == '') {
			$this->lay->set('HELPEDITABLE', '1');
			if($action->getArgument('target') == 'ext') {
				$this->lay->set('HELPEDITURI', '?app=FDL&action=EDITEXTDOC&id='.$this->id);
			}
			else {
				$this->lay->set('HELPEDITURI', '?app=GENERIC&action=GENERIC_EDIT&id='.$this->id);
			}
		}
		else {
			$this->lay->set('HELPEDITABLE', '0');
			$this->lay->set('HELPEDITURI', '');
		}

		$langs = $this->getFamilyLangs();
		$user_lang = $this->getUserLang();
		$rubriques = $this->getRubriquesByLang();
		
		// contsruct rubriques on the right
		$leftrub = array();
		$contentrub = array();
		$i = 0;
		foreach($rubriques as $rubrique) {
			// get first lang
			$first_lang = $this->getFirstRubLang($rubrique, $user_lang);
			$ifirst = -1;
			$ilast = -1;
			foreach($langs as $lang_key => $lang_name) {
				// construct rubrique
				if(array_key_exists($lang_key, $rubrique)) {
					$rub = $rubrique[$lang_key];
					if($lang_key == $first_lang) {
						$leftrub[] = array(
							'RUBKEY' => $rub['help_rub_key'],
							'RUBNAME' => $rub['help_rub_name'],
							'RUBLANG' => $rub['help_rub_lang'],
						);
					}
					if($ifirst < 0) {
						$ifirst = $i;
					}
					$contentrub[] = array(
						'RUBKEY' => $rub['help_rub_key'],
						'RUBNAME' => $rub['help_rub_name'],
						'RUBLANG' => $rub['help_rub_lang'],
						'RUBTEXT' => $rub['help_rub_text'],
						'RUBDISPLAY' => $lang_key == $first_lang ? 'block':'none',
						'RUBLANGS' => 'rublangs'.$i,
						'RUBHEADER' => '0',
						'RUBFOOTER' => '0',
					);
					$ilast = $i;
					$this->lay->setBlockData('rublangs'.$i, $this->getLangsFromItem($langs, $lang_key, $rubrique));
					$i++;
				}
			}
			if($ifirst >= 0 && $ilast >= 0) {
				$contentrub[$ifirst]['RUBHEADER'] = '1';
				$contentrub[$ilast]['RUBFOOTER'] = '1';
			}
		}

		$this->lay->setBlockData('LEFTRUB', $leftrub);

		$this->lay->setBlockData('CONTENTRUB', $contentrub);
		$this->lay->setBlockData('JSRUBRIQUES', $contentrub);

		$all_langs = array();
		foreach($langs as $lang_key => $lang_name) {
			$all_langs[] = array(
				'LANGKEY' => $lang_key,
				'LANGNAME' => $lang_name,
				'LANGISO' => strtolower(substr($lang_key, -2)),
			);
		}
		$this->lay->setBlockData('ALLLANGS', $all_langs);

		// construct aides
		$aides = array();
		$s = new SearchDoc($this->dbaccess, 'HELPPAGE');
		$s->setObjectReturn();
		$s->orderby = 'title';
		$s->search();
		while($doc = $s->nextDoc()) {
			$aides[] = array(
				'AIDE' => $doc->getDocAnchor($doc->id, '_self', true, false, false),
			);
		}
		$this->lay->setBlockData('LEFTAIDES', $aides);

	}
	/**
	 *
	 * @param Array $all_lang_keys
	 * @param Array $all_lang_texts
	 * @param string $current_lang
	 * @param string $item
	 * @return array
	 */
	public function getLangsFromItem($all_langs, $current_lang, $item) {

		$langs = array();
		foreach($all_langs as $lang_key => $lang_name) {
			if($lang_key == $current_lang) {
				$langclass = 'current';
			}
			elseif(array_key_exists($lang_key, $item)) {
				$langclass = 'active';
			}
			else {
				$langclass = 'inactive';
			}
			$langs[] = array(
				'LANGKEY' => $lang_key,
				'LANGNAME' => $lang_name,
				'LANGCLASS' => $langclass,
				'LANGISO' => strtolower(substr($lang_key, -2)),
			);
		}
		return $langs;
	}
	/**
	 *
	 * @param string $rubrique
	 * @param string $user_lang
	 * @return string
	 */
	public function getFirstRubLang($rubrique, $user_lang) {
		// return lang if found
		foreach($rubrique as $lang => $rub) {
			if($lang == $user_lang) {
				return $lang;
			}
		}
		// return first lang
		foreach($rubrique as $lang => $rub) {
			return $lang;
		}
	}

	/**
	 * @begin-method-ignore
	 * this part will be deleted when construct document class until end-method-ignore
	 */
}

/*
 * @end-method-ignore
 */
?>