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

	/**
	 *
	 * @return string
	 */
	public function  getSpecTitle() {
		$titles = $this->getHelpByLang();
		$user_lang = $this->getUserLang();
		if(count($titles) == 0) {
			return $this->title;
		}
		if(array_key_exists($user_lang, $titles)) {
			return $titles[$user_lang]['help_name'];
		}
		else {
			$item = array_shift($titles);
			return $item['help_name'];
		}
	}
	/**
	 *
	 */
	public function  preEdition() {
		$oa = $this->getAttribute('help_sec_text');
		$oa->type = 'longtext';
	}
	/**
	 *
	 * @return array
	 */
	public function getSectionsByLang() {
		$rows = $this->getAValues('help_t_sections');

		$sections = array();
		foreach($rows as $row) {
			$key = str_pad($row['help_sec_order'], 8, '0', STR_PAD_LEFT).$row['help_sec_key'];
			$sections[$key][$row['help_sec_lang']] = $row;
		}
		ksort($sections);

		return $sections;
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
		$rows = $this->getAValues('help_t_help');

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
		$sections = $this->getSectionsByLang();
		
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
		$sections = $this->getSectionsByLang();
		
		// contsruct sections on the right
		$leftsection = array();
		$contentsection = array();
		$i = 0;
		foreach($sections as $section) {
			// get first lang
			$first_lang = $this->getFirstSectionLang($section, $user_lang);
			$ifirst = -1;
			$ilast = -1;
			foreach($langs as $lang_key => $lang_name) {
				// construct section
				if(array_key_exists($lang_key, $section)) {
					$sec = $section[$lang_key];
					if($lang_key == $first_lang) {
						$leftsection[] = array(
							'SECKEY' => $sec['help_sec_key'],
							'SECNAME' => $sec['help_sec_name'],
							'SECLANG' => $sec['help_sec_lang'],
						);
					}
					if($ifirst < 0) {
						$ifirst = $i;
					}
					$contentsection[] = array(
						'SECKEY' => $sec['help_sec_key'],
						'SECNAME' => $sec['help_sec_name'],
						'SECLANG' => $sec['help_sec_lang'],
						'SECTEXT' => $sec['help_sec_text'],
						'SECDISPLAY' => $lang_key == $first_lang ? 'block':'none',
						'SECLANGS' => 'seclangs'.$i,
						'SECHEADER' => '0',
						'SECFOOTER' => '0',
					);
					$ilast = $i;
					$this->lay->setBlockData('seclangs'.$i, $this->getLangsFromItem($langs, $lang_key, $section));
					$i++;
				}
			}
			if($ifirst >= 0 && $ilast >= 0) {
				$contentsection[$ifirst]['SECHEADER'] = '1';
				$contentsection[$ilast]['SECFOOTER'] = '1';
			}
		}

		$this->lay->setBlockData('LEFTSECTIONS', $leftsection);

		$this->lay->setBlockData('CONTENTSECTIONS', $contentsection);
		$this->lay->setBlockData('JSSECTIONS', $contentsection);

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
		$this->lay->setBlockData('LEFTHELPS', $aides);

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
	 * @param string $section
	 * @param string $user_lang
	 * @return string
	 */
	public function getFirstSectionLang($section, $user_lang) {
		// return lang if found
		foreach($section as $lang => $sec) {
			if($lang == $user_lang) {
				return $lang;
			}
		}
		// return first lang
		foreach($section as $lang => $sec) {
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