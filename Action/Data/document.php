<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Document Api access
 *
 * @author Anakeen 2009
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("DATA/Class.Collection.php");
/**
 * Retrieve and set documents
 * @param Action &$action current action
 * @global id Http var : document identificator
 */
function document(Action & $action)
{
    $mb1 = microtime(true);
    $id = getHttpVars("id");
    $method = strtolower(getHttpVars("method"));
    if ($err) {
        $returntype = "json";
        $out->error = $err;
    } else {
        $start = microtime(true);
        documentApplyMethod($action, $id, $method, $returntype, $out);
    }
    if ($returntype != "json") $action->lay->template = $out;
    else {
        if ($action->debug) {
            if (is_array($out)) $out["debuginfo"] = getPerfoInfo(sprintf("%.03fs", microtime(true) - $mb1));
            else if (is_object($out)) $out->debuginfo = getPerfoInfo(sprintf("%.03fs", microtime(true) - $mb1));
        }
        addLogInData($action, $out);
        $action->lay->template = json_encode($out);
    }
    $action->lay->noparse = true; // no need to parse after - increase performances
    
}
include_once ("WHAT/wdebug.php");

function getPerfoInfo($time = '')
{
    global $SQLDELAY, $SQLDEBUG;
    global $TSQLDELAY, $DEBUGINFO;
    
    $trace["all"] = sprintf("%.03fs", microtime(true) - $DEBUGINFO["mbinit"]);
    $trace["queries"] = sprintf("%.03fs #%d", $SQLDELAY, count($TSQLDELAY));
    if ($time) $trace["action"] = $time;
    $trace["initauthent"] = sprintf("%.03fs", $DEBUGINFO["mbaction"] - $DEBUGINFO["mbinit"]);
    $trace["memory"] = sprintf("%dkb", round(memory_get_usage() / 1024));
    return $trace;
}
/**
 * Display info before download
 * @global id Http var : document for file to edit (SIMPLEFILE family)
 */
function documentApplyMethod(Action & $action, $id, $method, &$returntype, &$out, &$doc = null)
{
    $returntype = "json";
    $doc = null;
    $out = false;
    switch ($method) {
        case 'getselection':
            $config = getHttpVars("config");
            if (is_object($config)) {
                include_once ("DATA/Class.DocumentSelection.php");
                $sel = new Fdl_DocumentSelection($config);
                $idssel = $sel->getIdentificators();
                $out->content = array();
                foreach ($idssel as $did) { // set mimimum info to use it with foreach request
                    $out->content[]["properties"]["id"] = $did;
                }
            } else {
                $out->error = sprintf(_("getSelection: no configuration set"));
            }
            break;

        case 'getcontent':
            if (!$id) {
                $err = sprintf(_("empty reference"));
                break;
            }
            $doc = new Fdl_Collection($id);
            if (!$doc->error) {
                $doc->setContentCompleteProperties($action->getArgument("completeProperties", "false") == "true");
                $doc->setContentOnlyValue($action->getArgument("onlyValues", "true") == "true");
                $doc->setContentOrderBy($action->getArgument("orderBy"));
                $doc->setContentSlice($action->getArgument("slice", 100));
                $doc->setContentStart($action->getArgument("start", 0));
                $doc->setContentKey($action->getArgument("key"));
                $doc->setContentKeyMode($action->getArgument("mode", "word"));
                $doc->setContentRecursiveLevel($action->getArgument("recursiveLevel", 0));
                $doc->setContentSearchProperty($action->getArgument("searchProperty", "svalues"));
                $filter = $action->getArgument("filter");
                if ($filter) $filter = json_decode($filter);
                if ($filter == "undefined") $filter = "";
                $doc->setContentFilter($filter);
                $doc->setContentVerifyHasChild($action->getArgument("verifyhaschild") == "true");
                $out = $doc->getContent();
            } else $out->error = $doc->error;
            break;

        case 'getauthorizedfamilies':
            $doc = new Fdl_Collection($id);
            if (!$doc->error) {
                $out->authorizedFamilies = $doc->getAuthorizedFamilies();
            } else $out->error = $doc->error;
            break;

        case 'getsubfamilies':
            $famid = getHttpVars("famid");
            if (!$famid) $out->error = _("data:getsubfamilies no family parameter");
            else {
                $doc = new Fdl_Collection();
                if (!$doc->error) {
                    $cc = (getHttpVars("controlCreation", "false") == "true");
                    $out = $doc->getSubFamilies($famid, $cc);
                } else $out->error = $doc->error;
            }
            break;

        case 'search':
            $onlyValues = getHttpVars("onlyValues", "true") == "true";
            $doc = new Fdl_Collection();
            if (!$doc->error) {
                $key = getHttpVars("key");
                $famid = getHttpVars("family", getHttpVars("famid", 0));
                $start = getHttpVars("start", 0);
                $slice = getHttpVars("slice", 100);
                $mode = getHttpVars("mode", "word");
                $orderby = getHttpVars("orderBy");
                $whl = (getHttpVars("withHighlight", "false") == "true");
                $filter = getHttpVars("filter");
                $verifyhaschild = getHttpVars("verifyhaschild");
                if ($filter) $filter = json_decode($filter);
                if ($filter == "undefined") $filter = "";
                $searchproperty = getHttpVars("searchProperty", "svalues");
                $out = $doc->simpleSearch($key, $mode, $famid, $filter, $start, $slice, $orderby, $onlyValues, $searchproperty, $whl, $verifyhaschild);
            } else $out->error = $doc->error;
            break;

        case 'retrieveproposal':
            $key = getHttpVars("key");
            $aid = getHttpVars("attributeId");
            $famid = getHttpVars("relationFamilyId");
            if (((!$id) || ($id == "null") || (!$aid) || ($aid == "null")) && ($famid)) {
                include_once ("DATA/Lib.Document.php");
                $out->proposal = searchByTitle($famid, $key);
            } else {
                
                $doc = new Fdl_Document($id);
                if (!$doc->error) {
                    $oa = $doc->getAttribute($aid);
                    if (!$oa) {
                        $out->error = sprintf(_("attribute unknow %s") , $aid);
                    } else {
                        $out->error = null;
                        $out->proposal = $doc->getProposalDocuments($oa->id, $key);
                    }
                } else $out->error = $doc->error;
            }
            break;

        case 'insertdocument':
            $doc = new Fdl_Collection($id);
            if (!$doc->error) {
                $idtoadd = getHttpVars("idtoadd");
                if (!$idtoadd) $out->error = _("nothing to add");
                else $out = $doc->insertDocument($idtoadd);
            } else $out->error = $doc->error;
            break;

        case 'unlinkdocument':
            $doc = new Fdl_Collection($id);
            if (!$doc->error) {
                $idtoadd = getHttpVars("idtounlink");
                if (!$idtoadd) $out->error = _("nothing to unlink");
                else $out = $doc->unlinkDocument($idtoadd);
            } else $out->error = $doc->error;
            break;

        case 'moveto':
            $doc = new Fdl_Document($id);
            if (!$doc->error) {
                $moveto = getHttpVars("folderId");
                $movefrom = getHttpVars("fromFolderId");
                $doc->moveto($moveto);
                $out = $doc->getDocument($onlyValues);
            } else $out->error = $doc->error;
            break;

        case 'unlinkdocuments':
            $doc = new Fdl_Collection($id);
            if (!$doc->error) {
                $selection = getHttpVars("selection");
                if ($selection) {
                    $selection = json_decode($selection);
                    $out = $doc->unlinkDocuments($selection);
                } else $out->error = _("nothing to unlink");
            } else $out->error = $doc->error;
            break;

        case 'unlinkalldocuments':
            $doc = new Fdl_Collection($id);
            if (!$doc->error) {
                $out = $doc->unlinkAllDocuments();
            } else $out->error = $doc->error;
            break;

        case 'movedocuments':
            $doc = new Fdl_Collection($id);
            if (!$doc->error) {
                $selection = getHttpVars("selection");
                $targetId = getHttpVars("targetIdentificator");
                if ($selection) {
                    $selection = json_decode($selection);
                    $out = $doc->moveDocuments($selection, $targetId);
                } else $out->error = _("nothing to unlink");
            } else $out->error = $doc->error;
            break;

        case 'insertdocuments':
            $doc = new Fdl_Collection($id);
            if (!$doc->error) {
                $selection = getHttpVars("selection");
                if ($selection) {
                    $selection = json_decode($selection);
                    $out = $doc->insertDocuments($selection);
                } else $out->error = _("nothing to insert");
            } else $out->error = $doc->error;
            break;

        case 'history':
            $doc = new Fdl_Document($id);
            if (!$doc->error) {
                $out->items = $doc->getHistory();
                $out->revisions = $doc->getRevisions();
            } else $out->error = $doc->error;
            break;

        case 'create':
            $doc = new Fdl_Document();
            $temporary = (getHttpVars("temporary", "false") == "true");
            $doc->createDocument($id, $temporary); // not set in database
            $out = $doc->getDocument($onlyValues);
            break;

        case 'clonedocument':
        case 'clone':
            $temporary = (getHttpVars("temporary", "false") == "true");
            $linkfld = (getHttpVars("linkFolder", "true") == "true");
            $copyfiles = (getHttpVars("cloneFiles", "false") == "true");
            $title = getHttpVars("title");
            if (!$id) $out->error = _("no identificator set");
            else {
                $doc = new Fdl_Document($id);
                if (!$doc->error) {
                    $clone = $doc->cloneDocument($temporary, $linkfld, $copyfiles, $title);
                    if ($clone) $out = $clone->getDocument($onlyValues);
                    else $out->error = $doc->error;
                    $doc = $clone;
                } else $out->error = $doc->error;
            }
            break;

        case 'send':
        case 'senddocument':
            if (!$id) {
                $out->error = _("no identificator set");
            } else {
                $to = getHttpVars("to");
                $cc = getHttpVars("cc");
                $bcc = getHttpVars("bcc");
                $subject = getHttpVars("subject");
                $comment = getHttpVars("comment");
                $savecopy = getHttpVars("sendercopy");
                $doc = new Fdl_Document($id);
                $doc->send($to, $cc, $bcc, $subject, $comment, $savecopy);
            }
            break;

        case 'save':
            $autounlock = (getHttpVars("autounlock") == "true");
            if ($id) {
                $doc = new Fdl_Document($id);
                $doc->setValueFromHttpVars();
                $doc->setLogicalIdentificator();
                $doc->save();
            } else {
                $doc = new Fdl_Document();
                $famid = getHttpVars("family", getHttpVars("familyid"));
                $temporary = (getHttpVars("temporary", "false") == "true");
                $doc->createDocument($famid, $temporary);
                $doc->setValueFromHttpVars();
                $doc->setLogicalIdentificator();
                $doc->create(); // really set in database
                
            }
            if (!$doc->error) {
                if ($autounlock) $doc->unlock(true);
                $out = $doc->getDocument($onlyValues);
            } else $out->error = $doc->error;
            break;

        case 'saveform':
            $autounlock = (getHttpVars("autounlock") == "true");
            $doc = new Fdl_Document($id);
            if (!$doc->error) {
                $doc->setValueFromHttpVars();
                $doc->setFileValueFromHttpVars();
                $doc->setLogicalIdentificator();
                $callback = getHttpVars("callid");
                $doc->save();
                if ((!$doc->error) && $autounlock) $doc->unlock(true);
                $data = $doc->getDocument($onlyValues);
                $out = sprintf("<html><script>var data=%s;</script><body onload=\"window.parent.Fdl._completeSave('%s',data)\"></body></html>", json_encode($data) , $callback);
                
                $returntype = "html";
            } else $out->error = $doc->error;
            break;

        case 'changestate':
            $newstate = getHttpVars("state");
            $doc = new Fdl_Document($id);
            if (!$doc->error) $doc->changeState($newstate);
            $out = $doc->getDocument($onlyValues);
            break;

        case 'addusertag':
            $tag = getHttpVars("tag");
            $comment = getHttpVars("comment");
            $doc = new Fdl_Document($id);
            if (!$doc->error) $doc->addUserTag($tag, $comment);
            $out = $doc->getDocument($onlyValues, true, false, true);
            break;

        case 'deleteusertag':
            $tag = getHttpVars("tag");
            $doc = new Fdl_Document($id);
            if (!$doc->error) $doc->deleteUserTag($tag);
            $out = $doc->getDocument($onlyValues, true, false, true);
            break;

        case 'lock':
            $auto = (getHttpVars("autolock") == "true");
            $doc = new Fdl_Document($id);
            if (!$doc->error) $doc->lock($auto);
            $out = $doc->getDocument($onlyValues);
            break;

        case 'unlock':
            $auto = (getHttpVars("autolock") == "true");
            $doc = new Fdl_Document($id);
            if (!$doc->error) $doc->unlock($auto);
            $out = $doc->getDocument($onlyValues);
            break;

        case 'allocate':
            $uid = getHttpVars("userSystemId");
            $duid = getHttpVars("userId");
            if ($duid) {
                $du = new Fdl_Document($duid);
                $uid = $du->getValue("us_whatid");
            }
            $comment = getHttpVars("comment");
            $revision = (getHttpVars("revision") == "true");
            $autolock = (getHttpVars("lock") == "true");
            $doc = new Fdl_Document($id);
            if (!$doc->error) $doc->allocate($uid, $comment, $revision, $autolock);
            $out = $doc->getDocument($onlyValues);
            break;

        case 'unallocate':
            $comment = getHttpVars("comment");
            $revision = (getHttpVars("revision") == "true");
            $doc = new Fdl_Document($id);
            if (!$doc->error) $doc->unallocate($comment, $revision);
            $out = $doc->getDocument($onlyValues);
            break;

        case 'addrevision':
            $comment = (getHttpVars("comment"));
            $version = (getHttpVars("version"));
            $volatile = (getHttpVars("volatileVersion", "false") == "true");
            $doc = new Fdl_Document($id);
            if (!$doc->error) {
                $doc->addRevision($comment, $version);
                if ((!$doc->error) && $volatile && $version) {
                    $doc->setVersion(' ', false);
                    $doc->save();
                }
            }
            $out = $doc->getDocument($onlyValues);
            break;

        case 'delete':
            $really = (getHttpVars("really") == "true");
            $doc = new Fdl_Document($id);
            if (!$doc->error) $doc->delete($really);
            $out = $doc->getDocument($onlyValues);
            break;

        case 'restore':
            $doc = new Fdl_Document($id);
            if (!$doc->error) $doc->restore();
            $out = $doc->getDocument($onlyValues);
            break;

        case 'davurl':
            include_once ("DATA/Lib.Document.php");
            $vid = getHttpVars("vid");
            $doc = new Fdl_Document($id);
            $out->url = getDavUrl($id, $vid);
            break;

        case 'haswaitingfiles':
            include_once ("DATA/Lib.Document.php");
            $doc = new Fdl_Document($id);
            if (!$doc->error) {
                $out->haswaitingfiles = $doc->hasWaitingFiles();
                $out->error = null;
            } else $out->error = $doc->error;
            break;

        case 'getfollowingstates':
            include_once ("DATA/Lib.Document.php");
            $doc = new Fdl_Document($id);
            if (!$doc->error) {
                $out->followingStates = $doc->getFollowingStates();
                $out->error = null;
            } else $out->error = $doc->error;
            break;

        case 'getusertags':
            include_once ("DATA/Lib.Document.php");
            $doc = new Fdl_Document($id);
            if (!$doc->error) {
                $out->userTags = $doc->getUserTags();
                $out->error = null;
            } else $out->error = $doc->error;
            break;

        case 'getattachedtimers':
            include_once ("DATA/Lib.Document.php");
            $doc = new Fdl_Document($id);
            if (!$doc->error) {
                $out->attachedTimers = $doc->getAttachedTimers();
                $out->error = null;
            } else $out->error = $doc->error;
            break;

        case 'addattribute':
        case 'modifyattribute':
        case 'removeattribute':
            include_once ("DATA/Class.Family.php");
            $doc = new Fdl_Family($id);
            if (!$doc->error) {
                $aid = getHttpVars("attributeId");
                $type = getHttpVars("type", null);
                $parent = getHttpVars("parent", null);
                $visibility = getHttpVars("visibility", null);
                $label = getHttpVars("label", null);
                $order = getHttpVars("order", null);
                $inTitle = (getHttpVars("inTitle") == "true");
                $inAbstract = (getHttpVars("inAbstract") == "true");
                $needed = (getHttpVars("needed") == "true");
                $link = getHttpVars("link", null);
                $elink = getHttpVars("elink", null);
                $constraint = getHttpVars("constraint", null);
                $options = getHttpVars("options", null);
                if ($options) $options = json_decode($options);
                
                if ($method == "addattribute") {
                    $doc->addAttribute($aid, $label, $order, $type, $parent, $visibility, $inTitle, $inAbstract, $needed, $link, $elink, $phpfile, $phpfunc, $constraint, $options);
                } elseif ($method == "removeattribute") {
                    $doc->removeAttribute($aid);
                } else {
                    $doc->modifyAttribute($aid, $label, $order, $type, $parent, $visibility, $inTitle, $inAbstract, $needed, $link, $elink, $phpfile, $phpfunc, $constraint, $options);
                }
                $out = $doc->getDocument($onlyValues);
            } else $out->error = $doc->error;
            break;

        case 'addtransition':
        case 'removetransition':
            include_once ("DATA/Class.Workflow.php");
            $doc = new Fdl_Workflow($id);
            if (!$doc->error) {
                $start = getHttpVars("start");
                $finish = getHttpVars("finish");
                $transitiontype = getHttpVars("transitionType");
                if ($method == "addtransition") $doc->addTransition($start, $finish, $transitiontype);
                else $doc->removeTransition($start, $finish);
                $out = $doc->getDocument($onlyValues);
            } else $out->error = $doc->error;
            break;

        case 'addstate':
        case 'modifystate':
        case 'removestate':
            include_once ("DATA/Class.Workflow.php");
            $doc = new Fdl_Workflow($id);
            if (!$doc->error) {
                $key = getHttpVars("key");
                $label = getHttpVars("label");
                $activity = getHttpVars("activity");
                
                if ($method == "addstate") $doc->addState($key, $label, $activity);
                elseif ($method == "removestate") $doc->removeState($key);
                else $doc->modifyState($key, $label, $activity);
                $out = $doc->getDocument($onlyValues);
            } else $out->error = $doc->error;
            break;

        case 'addtransitiontype':
        case 'removetransitiontype':
        case 'modifytransitiontype':
            include_once ("DATA/Class.Workflow.php");
            $doc = new Fdl_Workflow($id);
            if (!$doc->error) {
                $key = getHttpVars("key");
                $label = getHttpVars("label", null);
                $preMethod = getHttpVars("preMathod", null);
                $postMethod = getHttpVars("postMathod", null);
                $noComment = getHttpVars("noComment", null);
                $ask = getHttpVars("ask", null);
                if ($ask) $ask = json_decode($ask);
                if ($method == "addtransitiontype") $doc->addTransitiontype($key, $label, $ask, $preMethod, $postmethod, $noComment);
                elseif ($method == "modifytransitiontype") $doc->modifyTransitiontype($key, $label, $ask, $preMethod, $postmethod, $noComment);
                else $doc->removeTransitiontype($key);
                $out = $doc->getDocument($onlyValues);
            } else $out->error = $doc->error;
            break;

        case 'getsearchcriteria':
            include_once ("FDL/Class.DocSearch.php");
            $s = new DocSearch();
            $operators = $s->top;
            $out->error = '';
            $out->operators = array();
            $alltype = array(
                'integer',
                'text',
                'longtext',
                'htmltext',
                'docid',
                'double',
                "ifile",
                "array",
                "file",
                "image",
                "enum",
                "color",
                "date",
                "time",
                "timestamp",
                "money"
            );
            foreach ($operators as $k => $v) {
                if (!is_array($v["type"])) $v["type"] = $alltype;
                foreach ($v["type"] as $type) {
                    $label = $v["label"] ? _($v["label"]) : '';
                    $dynlabel = $v["dynlabel"] ? _($v["dynlabel"]) : '';
                    if ($v["slabel"]) {
                        foreach ($v["slabel"] as $kl => $vl) {
                            if ($kl == $type) $label = _($vl);
                        }
                    }
                    if ($v["sdynlabel"]) {
                        foreach ($v["sdynlabel"] as $kl => $vl) {
                            if ($kl == $type) $dynlabel = _($vl);
                        }
                    }
                    $out->operators[$type][] = array(
                        "operator" => $k,
                        "operand" => $v["operand"],
                        "label" => $label,
                        "tplLabel" => $dynlabel
                    );
                }
            }
            
            break;

        case 'reload':
        case '':
            if (!$id) $out->error = _("no identificator");
            else {
                $config->onlyValues = getHttpVars("onlyValues", "true") == "true";
                $config->latest = (getHttpVars("latest", "true") == "true");
                $winfo = (getHttpVars("needWorkflow", "false") == "true");
                $config->propertiesInformation = (getHttpVars("propertiesInformation", "false") == "true");
                $config->completeProperties = getHttpVars("completeProperties", "true") == "true";
                $withContent = getHttpVars("contentStore", "false") == "true";
                $config->getUserTags = getHttpVars("getUserTags", "false") == "true";
                if ($withContent) $doc = new Fdl_Collection($id, $config);
                elseif ($winfo) {
                    include_once ("DATA/Class.Workflow.php");
                    $doc = new Fdl_Workflow($id, $config);
                } else $doc = new Fdl_Document($id, $config);
                $out = $doc->getDocument($config->onlyValues, $config->completeProperties, $config->propertiesInformation, $config->getUserTags);
                if ($withContent) {
                    if ($doc->isCollection()) {
                        $configContent = getHttpVars("contentConfig");
                        if ($configContent) {
                            $configContent = json_decode($configContent);
                            
                            $doc->setContentCompleteProperties($configContent->completeProperties === true);
                            $doc->setContentOnlyValue($configContent->onlyValues !== false);
                            $doc->setContentOrderBy($configContent->orderBy);
                            $doc->setContentSlice(($configContent->slice) ? $configContent->slice : 100);
                            $doc->setContentStart($configContent->start);
                            $doc->setContentKey($configContent->key);
                            $doc->setContentKeyMode($configContent->mode);
                            $doc->setContentSearchProperty($configContent->searchProperty);
                            $doc->setContentRecursiveLevel($configContent->recursiveLevel);
                            
                            $doc->setContentFilter($configContent->filter);
                            $doc->setContentVerifyHasChild($configContent->verifyhaschild === true);
                        }
                        $out["storedContent"] = $doc->getContent();
                    } else {
                        $out["storedContent"] = null;
                    }
                }
            }
            
            break;

        default:
            $out->error = sprintf(_("method %s not possible") , $method);
        }
    }
    function addLogInData(&$action, &$out)
    {
        $l = $action->parent->GetLogMsg();
        if (is_array($l) && (count($l) > 0)) {
            foreach ($l as $k => $v) {
                if ($d = json_decode($v)) $l[$k] = $d;
            }
            if (is_object($out)) {
                $out->log = $l;
                $action->parent->ClearLogMsg();
            }
            if (is_array($out)) {
                $out["log"] = $l;
                $action->parent->ClearLogMsg();
            }
        }
    }
?>