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

	public function viewhelppage($target="_self",$ulink=true,$abstract=false) {
		global $action;

		$all_lang_keys = $this->_val2array($this->getParamValue('help_p_lang_key'));
		$all_lang_texts = $this->_val2array($this->getParamValue('help_p_lang_name'));

		$user_lang = $action->getParam('CORE_LANG');

		$rubriques_values = $this->getAValues('help_t_rubriques');

		// construct rubriques
		$rubriques = array();
		foreach($rubriques_values as $rubriques_value) {
			$key = str_pad($rubriques_value['help_rub_ordre'], 5, '0', STR_PAD_LEFT).$rubriques_value['help_rub_key'];
			$rubriques[$key][$rubriques_value['help_rub_lang']] = $rubriques_value;
		}
		ksort($rubriques);

		// construct rubriques on the left
		$leftrub = array();
		foreach($rubriques as $key=>$rubrique) {
			foreach($rubrique as $lang => $rub) {
				$leftrub[] = array(
					'RUBKEY' => $rub['help_rub_key'],
					'RUBNAME' => $rub['help_rub_name'],
				);
				break;
			}
		}

		$this->lay->setBlockData('LEFTRUB', $leftrub);
		
		// contsruct rubriques on the right
		$contentrub = array();
		$i = 0;
		foreach($rubriques as $key=>$rubrique) {
			// get first lang
			$first_lang = $this->getFirstRubLang($rubrique, $user_lang);

			foreach($all_lang_keys as $lang_key) {
				// construct rubrique
				if(array_key_exists($lang_key, $rubrique)) {
					$rub = $rubrique[$lang_key];
					$contentrub[] = array(
						'RUBKEY' => $rub['help_rub_key'],
						'RUBNAME' => $rub['help_rub_name'],
						'RUBLANG' => $rub['help_rub_lang'],
						'RUBTEXT' => $rub['help_rub_text'],
						'RUBDISPLAY' => $lang_key == $first_lang ? 'block':'none',
						'RUBLANGS' => 'rublangs'.$i,
					);
					$this->lay->setBlockData('rublangs'.$i, $this->getRubriqueLangs($all_lang_keys, $all_lang_texts, $lang_key, $rubrique));
					$i++;
				}
			}
		}

		$this->lay->setBlockData('CONTENTRUB', $contentrub);


	}
	/**
	 *
	 * @param Array $all_lang_keys
	 * @param Array $all_lang_texts
	 * @param string $current_lang
	 * @param string $rubrique
	 * @return array
	 */
	public function getRubriqueLangs($all_lang_keys, $all_lang_texts, $current_lang, $rubrique) {

		$langs = array();
		foreach($all_lang_keys as $i => $lang_key) {
			if($lang_key == $current_lang) {
				$langclass = 'current';
			}
			elseif(array_key_exists($lang_key, $rubrique)) {
				$langclass = 'active';
			}
			else {
				$langclass = 'inactive';
			}
			$langs[] = array(
				'LANGKEY' => $lang_key,
				'LANGNAME' => $all_lang_texts[$i],
				'LANGCLASS' => $langclass,
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