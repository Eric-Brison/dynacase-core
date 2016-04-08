<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Ask documents
 *
 */
namespace Dcp\Core;
class Wask extends \Dcp\Family\Document
{
    /**
     * @var \Dcp\Family\Wask
     */
    private $prdoc = null;
    function postConstructor()
    {
        $this->dacls["answer"] = array(
            "pos" => 31,
            "description" => _("need answer")
        );
        $this->acls[] = "answer"; # _("answer")
        
    }
    /**
     * return sql query to search wanted document
     */
    function getAskLabels($keys)
    {
        $tk = array();
        foreach ($keys as $k) {
            $tk[$k] = $this->getAskLabel($k);
        }
        return $tk;
    }
    
    function getAskLabel($keys)
    {
        $i = array_search($keys, $this->getMultipleRawValues("was_keys"));
        if ($i !== false) {
            return $this->getMultipleRawValues("was_labels", "", $i);
        }
        return "";
    }
    
    function DocControl($aclname)
    {
        return \Doc::Control($aclname);
    }
    /**
     * Special control in case of dynamic controlled profil
     */
    function Control($aclname, $strict = false)
    {
        
        $err = $this->DocControl($aclname);
        if ($err == "") return $err; // normal case
        if ($this->getRawValue("DPDOC_FAMID") > 0) {
            if ($this->doc) {
                // special control for dynamic users
                if (!isset($this->prdoc)) {
                    $pdoc = createTmpDoc($this->dbaccess, $this->fromid);
                    $err = $pdoc->Add();
                    if ($err != "") return "Wask::Control:" . $err; // can't create profil
                    $pdoc->setProfil($this->profid, $this->doc);
                    $this->prdoc = & $pdoc;
                }
                $err = $this->prdoc->DocControl($aclname);
            }
        }
        return $err;
    }
    
    function Set(&$doc)
    {
        if (!isset($this->doc)) {
            $this->doc = & $doc;
        }
    }
}