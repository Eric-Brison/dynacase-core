<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: Method.DocText.php,v 1.2 2003/08/18 15:47:04 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
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
class _HELPPAGE extends Doc
{
    /*
     * @end-method-ignore
    */
    
    public $defaultview = 'FDL:VIEWHELPPAGE:T';
    public $defaultedit = 'FDL:EDITHELPPAGE';
    /**
     *
     * @return string
     */
    public function getSpecTitle()
    {
        $titles = $this->getHelpByLang();
        $user_lang = $this->getUserLang();
        if (count($titles) == 0) {
            return $this->title;
        }
        if (array_key_exists($user_lang, $titles)) {
            if ($titles[$user_lang]['help_name']) return $titles[$user_lang]['help_name'];
        } else {
            $item = array_shift($titles);
            if ($item['help_name']) return $item['help_name'];
        }
        return $this->title;
    }
    /**
     *
     */
    public function preEdition()
    {
        $oa = $this->getAttribute('help_sec_text');
        $oa->type = 'longtext';
        $err = '';
        if (!$this->id) {
            
            $oa = $this->getAttribute('help_family');
            $oa->setVisibility('S');
        }
        if ($this->id == 0) {
            $doc = createDoc($this->dbaccess, $this->fromid);
            if ($doc) {
                $err = $doc->add();
                if ($err == "") {
                    $this->Affect(getTdoc($this->dbaccess, $doc->id));
                    
                    $this->setValue("help_family", getHttpVars("help_family"));
                    if ($this->getValue("help_family")) $this->title = sprintf(_("help for %s") , $this->getTitle($this->getValue("help_family")));
                    
                    $this->modify();
                    global $action;
                    redirect($action, getHttpVars("app") , getHttpVars("action") . '&id=' . $this->id);
                }
            }
        }
        return $err;
    }
    /**
     * Check 'view' control acl against the help_family family profile
     * @param $aclname
     * @return string non-empty string with error message when forbidden or empty string if allowed
     */
    public function Control($aclname)
    {
        $control = parent::Control($aclname);
        
        if ($control != '') {
            return $control;
        }
        
        if ($aclname == 'view') {
            $famId = $this->getValue('HELP_FAMILY');
            $fam = new_Doc($this->dbaccess, $famId);
            if ($fam->isAlive()) {
                return $fam->Control('view');
            }
        }
        return '';
    }
    /**
     *
     * @return array
     */
    public function getSectionsByLang()
    {
        $rows = $this->getAValues('help_t_sections');
        
        $sections = array();
        foreach ($rows as $row) {
            $key = str_pad($row['help_sec_order'], 8, '0', STR_PAD_LEFT) . $row['help_sec_key'];
            $sections[$key][$row['help_sec_lang']] = $row;
        }
        ksort($sections);
        
        return $sections;
    }
    /**
     *
     * @return array
     */
    public function getFamilyLangs()
    {
        $all_lang_keys = $this->_val2array($this->getParamValue('help_p_lang_key'));
        $all_lang_texts = $this->_val2array($this->getParamValue('help_p_lang_name'));
        $all_langs = array();
        foreach ($all_lang_keys as $i => $key) {
            $all_langs[$key] = $all_lang_texts[$i];
        }
        return $all_langs;
    }
    /**
     *
     * @global  $action
     * @return string
     */
    public function getUserLang()
    {
        global $action;
        $user_lang = $action->getParam('CORE_LANG');
        return $user_lang;
    }
    /**
     *
     * @return array
     */
    public function getHelpByLang()
    {
        $rows = $this->getAValues('help_t_help');
        
        $helps = array();
        foreach ($rows as $row) {
            $helps[$row['help_lang']] = $row;
        }
        
        return $helps;
    }
    /**
     * @templateController special edit help page
     */
    public function edithelppage()
    {
        $this->editattr();
        $langs = $this->getFamilyLangs();
        $user_lang = $this->getUserLang();
        $sections = $this->getSectionsByLang();
        
        $this->editattr();
        
        $help_values = $this->getHelpByLang();
        // set help values
        $helpname = '';
        $helplangiso = '';
        $lang_key = '';
        // search user lang
        foreach ($help_values as $lang => $help) {
            if ($lang == $user_lang) {
                $lang_key = $lang;
                $helpname = $help['help_name'];
                $helpdescription = $help['help_description'];
                break;
            }
        }
        if (empty($lang_key)) {
            // search first lang
            foreach ($help_values as $lang => $help) {
                $lang_key = $lang;
                $helpname = $help['help_name'];
                $helpdescription = $help['help_description'];
            }
        }
        $this->lay->set('HELPID', $this->id);
        $this->lay->set('HELPNAME', $helpname);
        $this->lay->set('HELPDESCRIPTION', $helpdescription);
        // help add section
        $famid = $this->getValue('help_family');
        if (empty($famid)) {
            $this->lay->set('HELPATTRIBUTESLIST', false);
        } else {
            $this->lay->set('HELPATTRIBUTESLIST', true);
            $docfam = createDoc($this->dbaccess, $famid, false);
            $docattributes = $docfam->GetNormalAttributes();
            array_merge($docattributes, $docfam->getFieldAttributes());
            $attributes = array();
            foreach ($docattributes as & $attribute) {
                $attributes[] = array(
                    'HELPATTRVALUE' => $attribute->id,
                    'HELPATTRNAME' => sprintf("%s (%s)", $attribute->getLabel() , $attribute->type)
                );
            }
            unset($attribute);
            $this->lay->SetBlockData('HELPATTRIBUTES', $attributes);
        }
        
        $this->lay->SetBlockData('HELPLANGS', $this->getLangsFromItem($langs, $lang_key, $help_values));
        $this->lay->set('JSONLANGS', json_encode($langs));
        // construct sections
        $n = count($langs) - 1;
        $contentsection = array();
        foreach ($sections as $section) {
            $sec_key = $this->getSectionKey($section);
            $first_lang = $this->getFirstSectionLang($section, $user_lang);
            $secitems = array();
            $i = 0;
            foreach ($langs as $lang_key => $lang_name) {
                if (array_key_exists($lang_key, $section)) {
                    $sec = $section[$lang_key];
                } else {
                    $sec = array(
                        'help_sec_key' => $sec_key,
                        'help_sec_name' => '',
                        'help_sec_lang' => $lang_key,
                        'help_sec_text' => '',
                    );
                }
                $secitems[] = array(
                    'SECNAME' => $sec['help_sec_name'],
                    'SECLANG' => $sec['help_sec_lang'],
                    'SECTEXT' => $sec['help_sec_text'],
                    'SECDISPLAY' => $lang_key == $first_lang ? 'block' : 'none',
                );
                $i++;
            }
            $contentsection[] = array(
                'SECKEY' => $sec_key,
                'SECTIONLANGS' => 'seclangs' . $sec_key,
                'SECTIONITEMS' => 'secitems' . $sec_key,
            );
            $this->lay->setBlockData('seclangs' . $sec_key, $this->getLangsFromItem($langs, $first_lang, $section));
            $this->lay->setBlockData('secitems' . $sec_key, $secitems);
        }
        $this->lay->setBlockData('CONTENTSECTIONS', $contentsection);
        
        $langitems = array();
        $first = true;
        foreach ($langs as $lang_key => $lang_name) {
            $langitems[] = array(
                'LANGKEY' => $lang_key,
                'LANGISO' => strtolower(substr($lang_key, -2)) ,
                'LANGNAME' => $lang_name,
                'LANGCLASS' => $first ? 'current' : 'inactive',
                'LANGDISPLAY' => $first ? 'block' : 'none',
            );
            $first = false;
        }
        $this->lay->setBlockData('TEMPLATELANGS1', $langitems);
        $this->lay->setBlockData('TEMPLATELANGS2', $langitems);
    }
    /**
     *
     * @templateController for paper
     * @global <type> $action
     * @param <type> $target
     * @param <type> $ulink
     * @param <type> $abstract
     */
    public function printhelppage($target = "_self", $ulink = true, $abstract = false)
    {
        $this->viewhelppage($target, $ulink, $abstract);
    }
    /**
     *
     * @templateController special view help page
     * @global <type> $action
     * @param <type> $target
     * @param <type> $ulink
     * @param <type> $abstract
     */
    public function viewhelppage($target = "_self", $ulink = true, $abstract = false)
    {
        global $action;
        
        include_once ("FDL/Class.SearchDoc.php");
        
        $this->lay->set('HELPTITLE', $this->getTitle());
        $this->lay->set('DOCID', $this->id);
        
        if ($this->CanEdit() == '') {
            $this->lay->set('HELPEDITABLE', '1');
            if ($action->getArgument('target') == 'ext') {
                $this->lay->set('HELPEDITURI', '?app=FDL&action=EDITEXTDOC&viewext=yes&id=' . $this->id);
            } else {
                $this->lay->set('HELPEDITURI', '?app=GENERIC&action=GENERIC_EDIT&id=' . $this->id);
            }
        } else {
            $this->lay->set('HELPEDITABLE', '0');
            $this->lay->set('HELPEDITURI', '');
        }
        
        $langs = $this->getFamilyLangs();
        $user_lang = $this->getUserLang();
        $sections = $this->getSectionsByLang();
        // construct sections on the right
        $leftsection = array();
        $contentsection = array();
        $i = 0;
        foreach ($sections as $section) {
            // get first lang
            $first_lang = $this->getFirstSectionLang($section, $user_lang);
            $ifirst = - 1;
            $ilast = - 1;
            foreach ($langs as $lang_key => $lang_name) {
                // construct section
                if (array_key_exists($lang_key, $section)) {
                    $sec = $section[$lang_key];
                    if ($lang_key == $first_lang) {
                        $leftsection[] = array(
                            'SECKEY' => $sec['help_sec_key'],
                            'SECNAME' => $sec['help_sec_name'],
                            'SECLANG' => $sec['help_sec_lang'],
                        );
                    }
                    if ($ifirst < 0) {
                        $ifirst = $i;
                    }
                    $contentsection[] = array(
                        'SECKEY' => $sec['help_sec_key'],
                        'SECNAME' => $sec['help_sec_name'],
                        'SECLANG' => $sec['help_sec_lang'],
                        'SECTEXT' => $this->getHtmlValue($this->getAttribute('help_sec_text') , $sec['help_sec_text']) ,
                        'SECDISPLAY' => $lang_key == $first_lang ? 'block' : 'none',
                        'SECLANGS' => 'seclangs' . $i,
                        'SECHEADER' => '0',
                        'SECFOOTER' => '0',
                    );
                    $ilast = $i;
                    $this->lay->setBlockData('seclangs' . $i, $this->getLangsFromItem($langs, $lang_key, $section));
                    $i++;
                }
            }
            if ($ifirst >= 0 && $ilast >= 0) {
                $contentsection[$ifirst]['SECHEADER'] = '1';
                $contentsection[$ilast]['SECFOOTER'] = '1';
            }
        }
        
        $this->lay->setBlockData('LEFTSECTIONS', $leftsection);
        
        $this->lay->setBlockData('CONTENTSECTIONS', $contentsection);
        $this->lay->setBlockData('JSSECTIONS', $contentsection);
        
        $all_langs = array();
        foreach ($langs as $lang_key => $lang_name) {
            $all_langs[] = array(
                'LANGKEY' => $lang_key,
                'LANGNAME' => $lang_name,
                'LANGISO' => strtolower(substr($lang_key, -2)) ,
            );
        }
        $this->lay->setBlockData('ALLLANGS', $all_langs);
        
        $descriptions = $this->getAvalues("help_t_help");
        $first = true;
        foreach ($descriptions as & $v) {
            $v["firstdesc"] = $first;
            if ($v["help_description"]) {
                $first = false;
            }
        }
        $this->lay->setBlockData('DESCR', $descriptions);
        
        $first = true;
        foreach ($descriptions as & $v) {
            $v["firsttitle"] = $first;
            if ($v["help_name"]) {
                $first = false;
            }
        }
        $this->lay->setBlockData('TITLES', $descriptions);
        // construct aides
        $aides = array();
        $s = new SearchDoc($this->dbaccess, 'HELPPAGE');
        $s->setObjectReturn();
        $s->orderby = 'title';
        $s->search();
        while ($doc = $s->nextDoc()) {
            $aides[] = array(
                'AIDE' => $doc->getDocAnchor($doc->id, $target, true, false, false) ,
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
    public function getLangsFromItem($all_langs, $current_lang, $item)
    {
        
        $langs = array();
        foreach ($all_langs as $lang_key => $lang_name) {
            if ($lang_key == $current_lang) {
                $langclass = 'current';
            } elseif (array_key_exists($lang_key, $item)) {
                $langclass = 'active';
            } else {
                $langclass = 'inactive';
            }
            $langs[] = array(
                'LANGKEY' => $lang_key,
                'LANGNAME' => $lang_name,
                'LANGCLASS' => $langclass,
                'LANGISO' => strtolower(substr($lang_key, -2)) ,
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
    public function getFirstSectionLang($section, $user_lang)
    {
        // return lang if found
        foreach ($section as $lang => $sec) {
            if ($lang == $user_lang) {
                return $lang;
            }
        }
        // return first lang
        foreach ($section as $lang => $sec) {
            return $lang;
        }
    }
    /**
     *
     * @param string $section
     * @return string
     */
    public function getSectionKey($section)
    {
        foreach ($section as $lang => $sec) {
            return $sec['help_sec_key'];
        }
        return false;
    }
    /**
     * Get the list of attributes ids which have an help message
     * @return array the list of attributes ids which have an help message
     */
    public function getHelpAttributes()
    {
        $attrList = $this->getTValue("help_sec_key");
        if (!is_array($attrList)) {
            return array();
        }
        return $attrList;
    }
    /**
     * Get the Url to the section of the help message of a specific attribute
     * @param string $attrId get the Url to the message for the given attribute
     * @return bool|string boolean false if no help is available for the given attribute id, or the url to the help
     */
    public function getAttributeHelpUrl($attrId = "")
    {
        $attrId = strtolower($attrId);
        if ($attrId == "") {
            return sprintf("?app=FDL&action=DOCHELP&id=%s", $this->id);
        }
        
        $helpAttrList = $this->getHelpAttributes();
        if (!in_array($attrId, $helpAttrList)) {
            return false;
        }
        
        return sprintf("?app=FDL&action=DOCHELP&id=%s&anchor=%s", $this->id, $attrId);
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