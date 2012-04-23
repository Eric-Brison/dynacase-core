<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * View folder containt
 *
 * @author Anakeen 2003
 * @version $Id: viewfolder.php,v 1.92 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.SearchDoc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("FDL/freedom_util.php");
include_once ("FDL/Class.QueryDir.php");
// -----------------------------------
// -----------------------------------
function viewfolder(&$action, $with_abstract = false, $with_popup = true, $column = false, $slice = "-", // view all document (not slice by slice)
$sqlfilters = array() , // more filters to see specials doc
$famid = "") // folder containt special fam id

{
    // -----------------------------------
    // Get all the params
    $dirid = GetHttpVars("dirid"); // directory to see
    $refresh = GetHttpVars("refresh", "no"); // force folder refresh
    $startpage = GetHttpVars("page", "0"); // page number
    $target = GetHttpVars("target", "fdoc"); // target for hyperlinks
    $sqlorder = GetHttpVars("sqlorder"); // order sort attribute
    $viewone = (GetHttpVars("viewone", "N") == "Y"); // direct view if only one
    if ($slice == "-") $slice = $action->GetParam("FDL_FOLDERMAXITEM", 1000);
    // $column = ($with_popup && ($action->getParam("FREEDOM_VIEW")=="column"));
    // Set the globals elements
    $baseurl = $action->GetParam("CORE_BASEURL");
    $standurl = $action->GetParam("CORE_STANDURL");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $dir = new_Doc($dbaccess, $dirid);
    
    $dirid = $dir->id; // use initial id for directories
    $distinct = false;
    
    $action->lay->set("RSS", ($dir->getValue("gui_isrss") == "yes" ? true : false));
    $action->lay->set("rsslink", $dir->getRssLink());
    $action->lay->set("foldername", $dir->getHtmlTitle());
    // control open
    if ($dir->defDoctype == 'S') {
        $aclctrl = "execute";
        if ($sqlorder == "") $sqlorder = $dir->getValue("se_orderby");
    } else $aclctrl = "open";
    if (($err = $dir->Control($aclctrl)) != "") $action->exitError($err);
    
    $action->lay->Set("dirtitle", stripslashes($dir->getHtmlTitle()));
    $action->lay->Set("dirid", $dirid);
    
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/resizeimg.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    
    $action->lay->set("VALTERN", ($action->GetParam("FDL_VIEWALTERN", "yes") == "yes"));
    
    if ($dirid == "") {
        $action->exitError(_("cannot see unknow folder"));
    }
    
    if ($startpage > 0) {
        $pagefolder = $action->Read("pagefolder");
        $start = $pagefolder[$startpage];
    } else $start = 0;
    
    $terr = getChildDocError($dbaccess, $dirid);
    if (count($terr) > 0) {
        if ($dir->defDoctype == 'S') {
            redirect($action, "FDL", "IMPCARD&zone=FREEDOM:PARAMDSEARCH:T&id=$dirid", $action->GetParam("CORE_STANDURL") , true);
        } else {
            $action->addWarningMsg(implode("\n", $terr));
            redirect($action, "FDL", "FDL_CARD&id=$dirid", $action->GetParam("CORE_STANDURL") , true);
        }
    }
    
    $sd = new SearchDoc($dbaccess, $famid);
    $sd->setSlice($slice);
    $sd->setStart($start);
    if ($dirid) $sd->useCollection($dirid);
    $sd->excludeConfidential();
    $sd->distinct = $distinct;
    if ($column) {
        if ($sqlorder != "") {
            $sqlorder = "fromid,$sqlorder";
        } else {
            $sqlorder = "fromid,title";
        }
    }
    $sd->setOrder($sqlorder);
    if ($sqlfilters) foreach ($sqlfilters as $filter) $sd->addFilter($filter);
    $sd->setObjectReturn();
    //$ldoc = getChildDoc($dbaccess, $dirid,$start,$slice,$sqlfilters,$action->user->id,"TABLE",$famid,
    //$distinct, $sqlorder);
    $sd->search();
    
    if ($viewone && ($sd->count() == 1)) {
        
        $doc1 = $sd->nextDoc();
        
        if ($doc1->doctype == "D") redirect($action, "FREEDOM", "OPENFOLIO&id=" . $doc1->initid, $action->GetParam("CORE_STANDURL"));
        else redirect($action, "FDL", "FDL_CARD&latest=Y&id=" . $doc1->id, $action->GetParam("CORE_STANDURL"));
        exit;
    }
    $famid = abs($famid);
    if ($with_popup) {
        // Set Popup
        include_once ("FDL/popup_util.php");
        // ------------------------------------------------------
        // definition of popup menu
        popupInit("popuplist", array(
            'vprop',
            'editdoc',
            'cancel',
            'copy',
            'addbasket',
            'duplicate',
            'ifld',
            'delete'
        ));
    }
    
    $kdiv = 1;
    $tdoc = array();
    
    $nbseedoc = 0;
    if (!$sd->searchError()) {
        // get date format
        if ($action->GetParam("CORE_LANG") == "fr_FR") { // date format depend of locale
            $fdate = "%d/%m/%y";
        } else {
            $fdate = "%x";
        }
        
        $nbdoc = 0;
        $prevFromId = - 2;
        
        $tfamdoc = array();
        
        $k = 0;
        while ($doc = $sd->nextDoc()) {
            
            if ($doc->isConfidential()) continue;
            $nbseedoc++;
            
            $nbdoc++; // one more visible doc
            $docid = $doc->id;
            
            $tdoc[$k]["id"] = $docid;
            $tdoc[$k]["fromid"] = $doc->fromid;
            // search title for freedom item
            $title = $doc->getHtmlTitle();
            $tdoc[$k]["title"] = $title;
            
            if ($doc->doctype == "C") $tdoc[$k]["title"] = "<B>" . $title . "</B>";
            
            if (strlen($title) > 20) $tdoc[$k]["abrvtitle"] = mb_substr($title, 0, 12) . " ... " . mb_substr($title, -5);
            else $tdoc[$k]["abrvtitle"] = $title;
            
            if (isset($doc->_highlight) && $doc->_highlight != "") {
                $tdoc[$k]["highlight"] = $doc->_highlight;
            } else $tdoc[$k]["highlight"] = $title;
            $tdoc[$k]["icontitle"] = $tdoc[$k]["highlight"];
            
            $tdoc[$k]["profid"] = $doc->profid;
            $tdoc[$k]["revdate"] = strftime($fdate, intval($doc->revdate));
            
            $tdoc[$k]["iconsrc"] = $doc->geticon();
            
            $tdoc[$k]["divid"] = $kdiv;
            
            $tdoc[$k]["locked"] = "";
            $tdoc[$k]["emblem"] = $action->GetImageUrl("1x1.png");
            $tdoc[$k]["emblemt"] = "";
            $tdoc[$k]["emblemw"] = "0";
            $tdoc[$k]["canedit"] = 1;
            $tdoc[$k]["postitid"] = ($doc->postitid > 0) ? $doc->postitid : false;
            $tdoc[$k]["forumid"] = ($doc->forumid > 0) ? $doc->forumid : false;
            $tdoc[$k]["inDomain"] = $doc->isInDomain();
            
            $tdoc[$k]["emblem"] = $doc->getEmblem();
            if ($doc->confidential > 0) {
                // $tdoc[$k]["emblem"] = $action->GetImageUrl("confidential.gif");
                $tdoc[$k]["emblemt"] = _("confidential");
                //$tdoc[$k]["emblemw"] ="12";
                $tdoc[$k]["canedit"] = false;
                $tdoc[$k]["locked"] = sprintf("<img src=\"%s\" title=\"%s\" width=\"20px\">", $tdoc[$k]["emblem"], $tdoc[$k]["emblemt"]);
            } else if ($doc->locked == - 1) {
                // $tdoc[$k]["emblem"] = $action->GetImageUrl("revised.gif");
                $tdoc[$k]["emblemt"] = _("fixed");
                // $tdoc[$k]["emblemw"] ="12";
                $tdoc[$k]["canedit"] = false;
                $tdoc[$k]["locked"] = sprintf("<img src=\"%s\" title=\"%s\" width=\"20px\">", $tdoc[$k]["emblem"], $tdoc[$k]["emblemt"]);
            } else if ($doc->lockdomainid > 0) {
                //  $tdoc[$k]["emblem"] = $action->GetImageUrl("clef1.gif");
                $tdoc[$k]["emblemt"] = _("domain locked");
                //  $tdoc[$k]["emblemw"] ="12";
                $tdoc[$k]["locked"] = sprintf("<img src=\"%s\" title=\"%s\" width=\"20px\">", $tdoc[$k]["emblem"], $tdoc[$k]["emblemt"]);
            } else if ((abs($doc->locked) == $action->parent->user->id)) {
                //  $tdoc[$k]["emblem"] = $action->GetImageUrl("clef1.gif");
                $tdoc[$k]["emblemt"] = _("locked");
                //  $tdoc[$k]["emblemw"] ="12";
                $tdoc[$k]["locked"] = sprintf("<img src=\"%s\" title=\"%s\" width=\"20px\">", $tdoc[$k]["emblem"], $tdoc[$k]["emblemt"]);
            } else if ($doc->locked != 0) {
                // $tdoc[$k]["emblem"] = $action->GetImageUrl("clef2.gif");
                $tdoc[$k]["emblemt"] = _("locked");
                //   $tdoc[$k]["emblemw"] ="12";
                $tdoc[$k]["canedit"] = false;
                $tdoc[$k]["locked"] = sprintf("<img src=\"%s\" title=\"%s\" width=\"20px\">", $tdoc[$k]["emblem"], $tdoc[$k]["emblemt"]);
            } else if ($doc->archiveid != 0) {
                // $tdoc[$k]["emblem"] = $action->GetImageUrl("archive.png");
                $tdoc[$k]["emblemt"] = _("archived");
                // $tdoc[$k]["emblemw"] ="12";
                $tdoc[$k]["canedit"] = false;
                $tdoc[$k]["locked"] = sprintf("<img src=\"%s\" title=\"%s\" width=\"20px\">", $tdoc[$k]["emblem"], $tdoc[$k]["emblemt"]);
            } else if ($doc->control("edit") != "") {
                // $tdoc[$k]["emblem"] = $action->GetImageUrl("nowrite.png");
                $tdoc[$k]["emblemt"] = _("read-only");
                //  $tdoc[$k]["emblemw"] ="12";
                $tdoc[$k]["canedit"] = false;
                $tdoc[$k]["locked"] = sprintf("<img src=\"%s\" title=\"%s\" width=\"20px\">", $tdoc[$k]["emblem"], $tdoc[$k]["emblemt"]);
            }
            //else if ($doc->lmodify == "Y") if ($doc->doctype == 'F') $tdoc[$k]["locked"] = $action->GetIcon("changed2.gif",N_("changed"), 20,20);
            $tdoc[$k]["iconsrc"] = $doc->geticon();
            
            if ($with_popup) {
                // ------------------------------
                // define popup accessibility
                popupInvisible("popuplist", $kdiv, 'vprop'); // don't use : idem like simple clic
                popupActive("popuplist", $kdiv, 'cancel');
                popupActive("popuplist", $kdiv, 'copy');
                
                if (!$action->getParam("FREEDOM_IDBASKET")) popupInvisible("popuplist", $kdiv, 'addbasket');
                else popupActive("popuplist", $kdiv, 'addbasket');
                popupActive("popuplist", $kdiv, 'ifld');
                popupActive("popuplist", $kdiv, 'duplicate');
                
                if ($dirid > 0) popupActive("popuplist", $kdiv, 'delete');
                else popupInactive("popuplist", $kdiv, 'delete');
                
                if ($doc->doctype == 'C') {
                    popupInvisible("popuplist", $kdiv, 'editdoc');
                } else {
                    $cud = ($doc->CanLockFile() == "");
                    if ($cud) {
                        popupActive("popuplist", $kdiv, 'editdoc');
                    } else {
                        popupInactive("popuplist", $kdiv, 'editdoc');
                    }
                }
                
                if ($dir->defDoctype != 'D') {
                    // it's a search :: inhibit duplicate and suppress reference
                    popupInvisible("popuplist", $kdiv, 'duplicate');
                    popupInvisible("popuplist", $kdiv, 'delete');
                }
            }
            
            $kdiv++;
            if ($doc->isRevisable()) $tdoc[$k]["revision"] = $doc->revision;
            else $tdoc[$k]["revision"] = "";
            if ($doc->state) {
                $tdoc[$k]["state"] = $action->Text($doc->getState()); //$action->Text($doc->state);
                $tdoc[$k]["statecolor"] = $doc->getStateColor("transparent");
            } else {
                $tdoc[$k]["state"] = "";
                $tdoc[$k]["statecolor"] = "transparent";
            }
            
            if (($doc->doctype == 'D') || ($doc->doctype == 'S')) $tdoc[$k]["isfld"] = "true";
            else $tdoc[$k]["isfld"] = "false";
            // ----------------------------------------------------------
            //                 ABSTRACT MODE
            // ----------------------------------------------------------
            if ($with_abstract) {
                if (!$doc->isConfidential()) {
                    // search abstract attribute for freedom item
                    $doc->ApplyMask(); // apply mask attribute
                    if ($with_abstract === 2) {
                        $tdoc[$k]["ABSTRACTVALUES"] = getAbstractDetail($doc, $target);
                    } else {
                        $tdoc[$k]["ABSTRACTVALUES"] = $doc->viewDoc($doc->defaultabstract, $target, true, $abstract);
                        $tdoc[$k]["LOrR"] = ($k % 2 == 0) ? "left" : "right";
                    }
                } else $tdoc[$k]["ABSTRACTVALUES"] = "";
            }
            // ----------------------------------------------------------
            //                 COLUMN MODE
            // ----------------------------------------------------------
            if ($column) {
                $adoc = $doc->getFamDoc();
                /* Check if the family header has already been generated */
                $famdocAlreadyExists = false;
                foreach ($tfamdoc as $famdoc) {
                    if ($famdoc['fid'] == $doc->fromid) {
                        $famdocAlreadyExists = true;
                        break;
                    }
                }
                if (!$famdocAlreadyExists) {
                    /* Generate the family header */
                    $tfamdoc[] = array(
                        "iconfamsrc" => $tdoc[$k]["iconsrc"],
                        "ftitle" => $adoc->title,
                        "fid" => $doc->fromid,
                        "blockattr" => "BATT" . $doc->fromid,
                        "blockvalue" => "BVAL" . $doc->fromid
                    );
                    // create the TR head
                    $lattr = $adoc->GetAbstractAttributes();
                    $taname = array();
                    $emptytableabstract = array();
                    foreach ($lattr as $ka => $attr) {
                        if (($attr->mvisibility == 'H') || ($attr->mvisibility == 'I')) unset($lattr[$ka]);
                    }

                    foreach ($lattr as $ka => $attr) {
                        $emptytableabstract[$attr->id]["value"] = "-";
                        $taname[$attr->id]["aname"] = $attr->getLabel();
                    }
                    $action->lay->SetBlockData("BATT" . $doc->fromid, $taname);
                }

                /* Stack up the documents values in tdoc */
                $tvalues = array();
                
                if ($doc->isConfidential()) {
                    foreach ($lattr as $ka => $attr) {
                        $tvalues[] = "x";
                    }
                } else {
                    foreach ($lattr as $ka => $attr) {
                        //$tvalues[]=$doc->getValue($attr->id,"-");
                        if ($attr->type == "image") $tvalues[] = '<img src="' . $doc->getHtmlValue($attr, $doc->getValue($attr->id, "-") , $target) . '&height=30"  height="30">';
                        else $tvalues[] = ($doc->getValue($attr->id) ? $doc->getHtmlValue($attr, $doc->getValue($attr->id) , $target) : '-');
                    }
                }
                $tdoc[$k]["values"] = implode('</td><td class="tlist">', $tvalues);
            }

            $k++;
        }

        if ($column == 1) {
            /* Order tdoc by 'fromid', 'title' */
            $collator = new Collator($action->GetParam("CORE_LANG", "fr_FR"));
            usort($tdoc,function($a, $b) use ($collator) {
                $cmp = ($a['fromid'] - $b['fromid']);
                return ($cmp == 0) ? $collator->compare($a['title'], $b['title']) : $cmp;
            });
            /*
             * Sort documents with same fromid into separate lists
             */
            $tdocByFromId = array();
            foreach ($tdoc as $doc) {
                $tdocByFromId[$doc['fromid']] []= $doc;
            }
            /*
             * Set the BVAL<fromid> blocks with the list of
             * documents from the same family
             */
            foreach ($tdocByFromId as $fromid => $documentList) {
                $action->lay->SetBlockData("BVAL" . $fromid, $documentList);
            }
        } else {
            if ((GetHttpVars("sqlorder") == "") && ($slice >= $action->GetParam("FDL_FOLDERMAXITEM", 1000))) uasort($tdoc, "orderbytitle");
        }
    } else {
        //error in search
        addWarningMsg($sd->getError());
        addLogMsg($sd->getSearchInfo());
    }
    // Out
    //------------------------------
    // display popup action
    $tboo[0]["boo"] = "";
    $action->lay->SetBlockData("VIEWPROP", $tboo);
    
    $action->lay->Set("nbdiv", $kdiv - 1);
    if ($column) {
        /* Order tfamdoc by 'ftitle' */
        $collator = new Collator($action->GetParam("CORE_LANG", "fr_FR"));
        usort($tfamdoc, function($a, $b) use ($collator) {
            return $collator->compare($a['ftitle'], $b['ftitle']);
        });
        $action->lay->SetBlockData("TABLEBODY", $tfamdoc);
    } else $action->lay->SetBlockData("TABLEBODY", $tdoc);
    
    if ($with_popup) {
        // display popup js
        popupGen($kdiv - 1);
    }
    
    if ($with_popup || $column) {
        // js : manage icons
        $licon = new Layout($action->Getparam("CORE_PUBDIR") . "/FDL/Layout/manageicon.js", $action);
        $licon->Set("nbdiv", $kdiv - 1);
        $action->parent->AddJsCode($licon->gen());
    }
    // when slicing
    $pagefolder[$startpage + 1] = $nbseedoc + $start;
    $action->Register("pagefolder", $pagefolder);
    $action->lay->Set("next", $startpage + 1);
    $action->lay->Set("prev", $startpage - 1);
    
    $action->lay->Set("nbdoc", $nbdoc);
    $action->lay->Set("wtarget", $target);
    
    return $nbdoc;
}

function orderbyfromidtitle($a, $b)
{
    $cmp = ($a['fromid'] - $b['fromid']);
    return ($cmp == 0) ? strcasecmp($a['title'], $b['title']) : $cmp;
}

function orderbytitle($a, $b)
{
    return strcasecmp($a["title"], $b["title"]);
}

function getAbstractDetail(&$doc, $target)
{
    $tout = array();
    $lattr = $doc->GetAbstractAttributes();
    $emptytableabstract = array();
    foreach ($lattr as $ka => $attr) {
        $val = $doc->GetHtmlAttrValue($ka, $target, 2, -1, true, true);
        
        if ($val) $tout[] = $val;
    }
    return implode(" - ", $tout);
}
?>
