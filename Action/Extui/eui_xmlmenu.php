<?php
/**
 * Send javascript context menu
 *
 * @author Anakeen 2010
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once("FDL/Class.Doc.php");
/**
 * Display info before download
 * @param Action &$action current action
 * @global id Http var : id document for context menu
 * @global collectionId Http var : id collection where is actually the document (the context)
 * @global menuxml Http var : the xml menu file APP:file.xml
 */
function eui_xmlmenu(&$action) {
    $docid=getHttpVars("id");
    $menuxml=getHttpVars("menuxml","EXTUI:default-context-menu.xml");
    $fldid=getHttpVars("collectionId");
    $out=eui_getxmlmenu($docid,$menuxml,$fldid);
    $action->lay->noparse=true; // no need to parse after - increase performances
    $action->lay->template=json_encode($out);
}
function eui_getxmlmenu($docid,$menuxml,$folderid=0) {
    $dbaccess=getParam("FREEDOM_DB");
    $err="";
    $out=array();
    list($menuapp,$menulay)=explode(":",$menuxml);
    $lay=new Layout(getLayoutFile($menuapp,$menulay));
    if (! $lay->file) {
        $out["error"]=$lay->template;
    } else  {
        $doc=new_doc($dbaccess,$docid);
        if (! $doc->isAffected()) $doc=null;
        if ($doc) $err=$doc->control('view');
        if ($err != "")  $out["error"]=$err;
        else {
            if ($folderid) {
                $folder=new_doc($dbaccess,$folderid,true);
                if (! $folder->isAlive()) $folder=null;
            } else $folder=null;
             
            if ($doc) {
                $lay->set("TITLE",str_replace('&','&amp;',$doc->getTitle()));
                $lay->set("ICON",str_replace('&','&amp;',$doc->getIcon()));
            }
            $gen=$lay->gen();
            $gen=str_replace(array("<ext ","</ext>"),array("<script ","</script>"),$gen); // to delete - need for compliant with old spec
            $sepk=0;
            $om=@simplexml_load_string($gen);
            if (! $om) {
                $out["error"]=sprintf(_("xml parse error %s"),$lay->file);
                $out["parseError"]= libxml_get_last_error();
            } else {
                $ilm=$om->children();
                if ($ilm->itemmenu) {
                    foreach ($ilm->itemmenu as $v) {
                        $type=@current($v->attributes()->type);
                        if ($type=="separator") {
                            $out["menu"]['sep'.($sepk++)]=array("type"=>$type,
                                 "backgroundColor"=>current($v->backgroundColor));
                        } else {
                            $idm=current($v->attributes()->id);
                            $out["menu"][$idm]=parseItemMenu($v,$doc,$folder);
                        }
                    }
                }
                if ($ilm->itemlistmenu) {
                    foreach ($ilm->itemlistmenu as $im) {
                        $imenu=array();
                        $items=$im->itemmenu;
                        $idlm=@current($im->attributes()->id);
                        if (!$idlm)  $out["error"]=_("eui:no id in itemlistmenu");
                        if (is_object($items)) {
                            foreach ($items as $k=>$v) {
                                $type=@current($v->attributes()->type);
                                if ($type=="separator") {
                                    $imenu['sep'.($sepk++)]=array("type"=>$type,
                                 "backgroundColor"=>current($v->backgroundColor));
                                } else {
                                     
                                    $idm=current($v->attributes()->id);
                                    $imenu[$idm]=parseItemMenu($v,$doc,$folder);
                                }
                            }
                            $out["menu"][$idlm]=array("type"=>"menu",
                                              "icon"=>$im->icon?@current($im->icon->attributes()->src):false,
                                              "backgroundColor"=>current($im->backgroundColor),
                                              "label"=>current($im->label),
                                              "items"=>$imenu);
                             
                        } else $out["error"]=_("eui:no itemmenu tags found");
                    }
                } else $out["error"]=_("eui:itemlistmenu tag not found");
            }
        }
         
    }
    return $out;
}

function parseItemMenu($im,&$doc=null,&$folder=null) {
    $control=$im->control;
    $visibility=@current($im->attributes()->visibility);
    if ($visibility=="hidden") $visibility=POPUP_INVISIBLE;
    else $visibility=POPUP_ACTIVE;
    if (is_object($control)) {
        foreach ($control as $actrl) {
            $avis=@current($actrl->attributes()->visibility);
            if ($avis) {
                $avis=($avis=="hidden")?POPUP_INVISIBLE:POPUP_ACTIVE;
                $privilege=@current($actrl->attributes()->privilege);
                $object=@current($actrl->attributes()->object);
                if ($privilege) {

                    if ($object=="folder" && $folder) {
                        if (in_array($privilege,$folder->acls)) {
                            $err=$folder->control($privilege);
                            if ($err=="") $visibility=$avis;
                        }
                    } else {
                        if (! $doc) $visibility=POPUP_INACTIVE;
                        else {
                            if (in_array($privilege,$doc->acls)) {
                                $err=$doc->control($privilege);
                                if ($err=="") $visibility=$avis;
                            }
                        }
                    }
                } else {
                    $method=@current($actrl->attributes()->method);
                    if ($method) {
                        if ($method[0]=='!') {
                            $not=true;
                            $method=substr($method,1);
                        } else $not=false;
                        $isok=null;
                        if ($object=="folder" && $folder) {
                            if ($folder) $isok=$folder->applyMethod('::'.$method,null);
                            else $visibility=POPUP_INACTIVE;
                        } else {
                            if ($doc) $isok=$doc->applyMethod('::'.$method,null);
                            else $visibility=POPUP_INACTIVE;
                        }

                        if (($isok !== null) && ($not xor $isok)) $visibility=$avis;
                    }
                }
            }
        }
    }
    
    $url = $im->url?(($doc)?$doc->urlwhatEncode(@current($im->url->attributes()->href)):@current($im->url->attributes()->href)):false;
    if(!$url && $doc){
    	$url = $doc->applyMethod(@current($im->url->attributes()->method),null);
    }
    $type=@current($im->attributes()->type);
    return array("type"=>($type && $type!="menu")?$type:"item",
                 "url"=>$url,
                 "javascript"=>current($im->javascript),
                 "visibility"=>$visibility,
                 "label"=>current($im->label),
                 "target"=>@current($im->attributes()->target),
                 "useSelection"=>(@current($im->attributes()->useselection)=="yes")?true:false,
                 "description"=>current($im->description),
                 "backgroundColor"=>current($im->backgroundColor),
                 "icon"=>$im->icon?@current($im->icon->attributes()->src):false,
                 "confirm"=>(current($im->confirm))?array("label"=>current($im->confirm->label),
                                                          "continue"=>current($im->confirm->continue),
                                                          "cancel"=>current($im->confirm->cancel)):false,
                 "script"=>current($im->script)?array("file"=>current($im->script->attributes()->file),
                                                "class"=>current($im->script->attributes()->class),
                                                "parameters"=>@current($im->script->attributes()->parameters)):false
    );
}


?>