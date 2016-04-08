<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * PortFolio Methods
 *
 */
namespace Dcp\Core;
class PortFolio extends \Dcp\Family\Dir
{
    /**
     * Call to create default tabs
     */
    function PostCreated()
    {
        if ($this->revision > 0) return '';
        if (!method_exists($this, "addfile")) return '';
        // copy all guide-card from default values
        $this->CreateDefaultTabsFromParameter();
        return $this->CreateDefaultTabs();
    }
    
    function ReCreateDefaultTabs()
    {
        include_once ("FDL/Lib.Dir.php");
        $err='';
        $child = getChildDir($this->dbaccess, 1, $this->initid, false, "TABLE");
        if (count($child) == 0) {
            $err = $this->CreateDefaultTabs();
        }
        return $err;
    }
    /**
     * Create default tabs based on tabs of PFL_IDDEF document
     * @return string message error (empty if no error)
     */
    function CreateDefaultTabs()
    {
        
        $err = "";
        include_once ("FDL/Lib.Dir.php");
        
        $ddocid = $this->getRawValue("PFL_IDDEF");
        
        if ($ddocid != "") {
            $ddoc = new_Doc($this->dbaccess, $ddocid);
            if ($ddoc->isAffected()) {
                $child = getChildDir($this->dbaccess, $this->userid, $ddoc->initid, false, "TABLE");
                
                foreach ($child as $k => $tdoc) {
                    $doc = getDocObject($this->dbaccess, $tdoc);
                    $copy = $doc->duplicate();
                    if (!is_object($copy)) $err.= $copy;
                    else $err.= $this->insertDocument($copy->id, "latest", true, true);
                }
            } else {
                $err = sprintf(_("Error in portfolio : folder %s not exists") , $ddocid);
            }
        }
        return $err;
    }
    /**
     * Create default tabs based on tabs of PFL_IDCOPYTAB parameter
     * @return string message error (empty if no error)
     */
    function CreateDefaultTabsFromParameter()
    {
        
        $err = "";
        include_once ("FDL/Lib.Dir.php");
        
        $copytab = $this->getFamilyParameterValue("pfl_idcopytab");
        if ($copytab) {
            $copytab = $this->rawValueToArray($copytab);
            foreach ($copytab as $k => $id) {
                $tdoc = getTDoc($this->dbaccess, $id);
                
                $doc = getDocObject($this->dbaccess, $tdoc);
                $copy = $doc->duplicate();
                if (!is_object($copy)) $err.= $copy;
                else $err.= $this->insertDocument($copy->id, "latest", true, true);
            }
        }
        
        return $err;
    }
    function postInsertDocument($docid, $multiple = false)
    {
        $doc = new_Doc($this->dbaccess, $docid);
        if ($doc->doctype == "S") {
            $doc->setValue("SE_IDCFLD", $this->initid);
            $doc->refresh();
            $doc->modify();
        }
    }
    /**
     * return document includes in portfolio an in each of its guide or searched inside portfolio
     * @param bool $controlview if false all document are returned else only visible for current user  document are return
     * @param array $filter to add list sql filter for selected document
     * @param int $famid family identifier to restrict search
     * @param bool $insertguide if true merge each content of guide else same as a normal folder
     * @return array array of document array
     */
    function getContent($controlview = true, array $filter = array() , $famid = "", $insertguide = false, $unused = "")
    {
        $tdoc = \Dir::getContent($controlview, $filter, $famid);
        if ($insertguide) {
            $todoc = array();
            foreach ($tdoc as $k => $v) {
                if (($v["doctype"] == "D") || ($v["doctype"] == "S")) {
                    /**
                     * @var \DocCollection $dir
                     */
                    $dir = new_Doc($this->dbaccess, $v["id"]);
                    $todoc = array_merge($todoc, $dir->getContent($controlview, $filter));
                    unset($tdoc[$k]);
                }
            }
            if (count($todoc)) {
                // array unique
                $todoc = array_merge($tdoc, $todoc);
                $tdoc = array();
                foreach ($todoc as $k => $v) {
                    $tdoc[$v["id"]] = $v;
                }
            }
        }
        return $tdoc;
    }
}