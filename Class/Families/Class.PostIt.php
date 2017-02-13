<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * templates for postit
 */
namespace Dcp\Core;
class PostitView extends \Dcp\Family\Document
{
    
    var $defaultview = "FDL:VIEWPOSTIT:T";
    var $defaultedit = "FDL:EDITPOSTIT:T";
    // -----------------------------------
    
    /**
     * @templateController special view postit
     * @param string $target
     * @param bool $ulink
     * @param bool $abstract
     */
    function viewpostit($target = "_self", $ulink = true, $abstract = false)
    {
        // -----------------------------------
        $tcomment = $this->getMultipleRawValues("PIT_COM");
        $tuser = $this->getMultipleRawValues("PIT_USER");
        $tdate = $this->getMultipleRawValues("PIT_DATE");
        $tcolor = $this->getMultipleRawValues("PIT_COLOR");
        
        $nbcar = strlen($this->getRawValue("PIT_COM"));
        if ($nbcar < 60) $fontsize = 120;
        elseif ($nbcar < 200) $fontsize = 100;
        else $fontsize = 80;
        $tlaycomment = array();
        foreach ($tcomment as $k => $v) {
            $tlaycomment[] = array(
                "comments" => $this->getHtmlValue($this->getAttribute('PIT_COM') , $v, '_blank') ,
                "user" => str_replace(array(
                    '[',
                    ']'
                ) , array(
                    '&#91;',
                    '&#93;'
                ) , htmlspecialchars($tuser[$k], ENT_QUOTES)) ,
                "date" => htmlspecialchars(stringDateToLocaleDate($tdate[$k]) , ENT_QUOTES) ,
                "color" => htmlspecialchars($tcolor[$k], ENT_QUOTES)
            );
        }
        
        $this->lay->rSet("EMPTY", (bool)(count($tcomment) == 0));
        $this->lay->rSet("fontsize", (int)$fontsize);
        // Out
        $this->lay->SetBlockData("TEXT", $tlaycomment);
    }
    /**
     *
     * @templateController special view postit
     */
    function editpostit()
    {
        $this->editattr();
    }
    
    function getpostittitle($s)
    {
        return sprintf(_("postit of %s") , $this->getTitle($s));
    }
    function postStore()
    {
        $docid = $this->getRawValue("PIT_IDADOC");
        if ($docid > 0) {
            $doc = new_Doc($this->dbaccess, $docid);
            if (intval($doc->postitid) == 0) {
                $doc->disableEditControl();
                $doc->postitid = $this->id;
                $doc->modify();
                $doc->enableEditControl();
            }
        }
        
        $ncom = $this->getRawValue("PIT_NCOM");
        if ($ncom != "") {
            
            $tcom = $this->getMultipleRawValues("PIT_COM");
            $tdate = $this->getMultipleRawValues("PIT_DATE");
            $tiduser = $this->getMultipleRawValues("PIT_IDUSER");
            $tcolor = $this->getMultipleRawValues("PIT_COLOR");
            
            foreach ($tcom as $k => $v) {
                if ($v == "") {
                    unset($tcom[$k]);
                    unset($tdate[$k]);
                    unset($tiduser[$k]);
                    unset($tcolor[$k]);
                }
            }
            $nk = count($tcom);
            $tcom[$nk] = $ncom;
            $tdate[$nk] = $this->getDate();
            $tiduser[$nk] = $this->getUserId();
            $tcolor[$nk] = $this->getRawValue("PIT_NCOLOR");
            
            $this->setValue("PIT_COM", $tcom);
            $this->setValue("PIT_DATE", $tdate);
            $this->setValue("PIT_IDUSER", $tiduser);
            $this->setValue("PIT_COLOR", $tcolor);
            $this->clearValue("PIT_NCOLOR");
            $this->clearValue("PIT_NCOM");
        }
    }
    
    function PostDelete()
    {
        $docid = $this->getRawValue("PIT_IDADOC");
        if ($docid > 0) {
            $doc = new_Doc($this->dbaccess, $docid);
            if ($doc->locked == - 1) $doc = new_Doc($this->dbaccess, $doc->getLatestId());
            if (intval($doc->postitid) > 0) {
                $doc->disableEditControl();
                $doc->postitid = 0;
                $doc->modify();
                $doc->enableEditControl();
            }
        }
    }
    
    function preCreated()
    {
        
        $tcomment = $this->getRawValue("PIT_NCOM");
        if ($tcomment == "") return (_("no message : post-it creation aborted"));
        return '';
    }
}
