<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Document Object Definition
 *
 * @author Anakeen
 * @version $Id: Class.Doc.php,v 1.562 2009/01/14 09:18:05 eric Exp $
 * @package FDL
 */
/**
 */
include_once ("FDL/Class.Doc.php");
/**
 * Document Class
 *
 */
class Fdl_Document
{
    /**
     * @var Doc|DocFam
     */
    protected $doc;
    protected $_properties;
    public $error = '';
    public $dbaccess;
    private $onlyAttributes = null;
    /**
     * @param int $id
     * @param stdClass $config
     * @param Doc $doc
     */
    function __construct($id = 0, $config = null, Doc & $doc = null)
    {
        $this->dbaccess = isset($doc) ? $doc->dbaccess : getDbAccess();
        if (isset($config->latest)) $latest = $config->latest;
        else $latest = true;
        
        if ($config === null) {
            $config = new stdClass();
            $config->onlyValues = null;
        }
        if ($id || is_object($doc)) {
            if ($doc) $this->doc = $doc;
            else $this->doc = new_doc($this->dbaccess, $id, $latest);
            if (!$this->doc->isAffected()) $this->error = sprintf(_("document %s not exist") , $id);
            if (!$this->error) {
                // no control families if structure is required
                if ($this->doc->doctype != 'C' || (!empty($config->onlyValues))) $this->error = $this->doc->control('view');
                if ($this->error) $this->error = sprintf(_("no privilege view for %d") , $id);
                elseif ($this->doc->isConfidential()) $this->error = sprintf(_("confidential document"));
            }
            if ($this->error) $this->doc = null;
            else {
                $this->doc->refresh();
                $mask = $this->doc->getDefaultView(false, "mask");
                if ($mask) $this->doc->applyMask($mask);
            }
        }
    }
    /**
     * create document : not set in dataabse until it was saved
     * @param string $familyId the family identifier
     * @param boolean $temporary set to true if need only a temporary document
     * @return void
     */
    function createDocument($familyId, $temporary = false)
    {
        if ($temporary) $cdoc = createTmpDoc($this->dbaccess, $familyId);
        else $cdoc = createDoc($this->dbaccess, $familyId);
        if ($cdoc) {
            $this->doc = $cdoc;
        } else {
            $this->setError(sprintf(_("cannot create document for family [%s]") , $familyId));
        }
    }
    
    function docisset()
    {
        return is_object($this->doc);
    }
    function setError($err)
    {
        if ($err) $this->error = $err;
    }
    function isAlive()
    {
        if ($this->doc) return $this->doc->isAlive();
        return null;
    }
    /**
     * @param Doc $doc
     */
    function affect(&$doc)
    {
        $this->doc = $doc;
        $this->_properties = null;
        $this->error = $this->doc->control('view');
        if ($this->error) unset($this->doc);
    }
    function getAttribute($aid)
    {
        if ($this->doc) {
            return $this->doc->getAttribute($aid);
        }
        return null;
    }
    
    public function usePartialDocument(array $attrids)
    {
        $this->onlyAttributes = $attrids;
    }
    /**
     * @return Doc|null
     */
    function getInternalDocument()
    {
        return $this->doc;
    }
    /**
     * return all attribute values
     * @return array|null
     */
    function getValues()
    {
        $lvalues = null;
        if ($this->doc) {
            $lvalues = array();
            if ($this->doc->doctype == 'C') {
                if (isset($this->doc->addfields)) {
                    foreach ($this->doc->addfields as $aid) {
                        $lvalues[$aid] = $this->doc->$aid;
                    }
                }
            } else {
                $nattr = $this->doc->getNormalAttributes();
                if ($this->onlyAttributes === null) $this->doc->applyMask();
                $isoDate = (getParam("DATA_LCDATE") == 'iso');
                foreach ($nattr as $k => $v) {
                    if ($this->onlyAttributes !== null && (!in_array($v->id, $this->onlyAttributes))) continue;
                    
                    if ($v->mvisibility != "I" && (!empty($this->doc->$k)) && $v->getOption("autotitle") != "yes") {
                        if ($v->inArray() || ($v->getOption("multiple") == "yes")) $lvalues[$v->id] = $this->doc->getMultipleRawValues($v->id);
                        else $lvalues[$v->id] = $this->doc->getRawValue($v->id);
                        
                        if (($v->type == "docid" || $v->type == "account") && ($v->visibility != 'H')) {
                            $isLatest = $v->getOption("docrev", "latest") == "latest";
                            if ($v->isMultiple()) {
                                $lv = $lvalues[$v->id];
                                $ltitle = array();
                                foreach ($lv as $kv => $aDocid) {
                                    if (strpos($aDocid, '<BR>') !== false) {
                                        
                                        $tt = explode('<BR>', $aDocid);
                                        $lvalues[$v->id][$kv] = $tt;
                                        $trtitle = array();
                                        foreach ($tt as $vv) {
                                            $rtitle = DocTitle::getRelationTitle($vv, $isLatest, $this->doc);
                                            if ($rtitle === false) $rtitle = $v->getOption("noaccesstext", _("information access deny"));
                                            $trtitle[] = $rtitle;
                                        }
                                        $ltitle[] = $trtitle; //implode('<BR>',$trtitle);
                                        
                                    } else {
                                        $rtitle = DocTitle::getRelationTitle($aDocid, $isLatest, $this->doc);
                                        if ($rtitle === false) $rtitle = $v->getOption("noaccesstext", _("information access deny"));
                                        $ltitle[] = $rtitle;
                                    }
                                }
                                $lvalues[$v->id . "_title"] = $ltitle;
                            } else {
                                $lvalues[$v->id . "_title"] = DocTitle::getRelationTitle($this->doc->getRawValue($v->id) , $isLatest, $this->doc);
                                if ($lvalues[$v->id . "_title"] === false) $lvalues[$v->id . "_title"] = $v->getOption("noaccesstext", _("information access deny"));
                            }
                            //if ($v->inArray() || ($v->getOption("multiple") == "yes")) $lvalues[$v->id . "_title"] = $this->doc->rawValueToArray($lvalues[$v->id . "_title"]);
                            
                        } elseif (($v->type == "thesaurus")) {
                            $lvalues[$v->id . "_title"] = $this->doc->getTitle($this->doc->getRawValue($v->id));
                            if ($v->inArray() || ($v->getOption("multiple") == "yes")) $lvalues[$v->id . "_title"] = $this->doc->rawValueToArray($lvalues[$v->id . "_title"]);
                        } elseif ($isoDate && ($v->type == 'date' || $v->type == 'timestamp')) {
                            if (is_array($lvalues[$v->id])) {
                                foreach ($lvalues[$v->id] as $kd => $vd) {
                                    $lvalues[$v->id][$kd] = StringDateToIso($vd, false);
                                }
                            } else $lvalues[$v->id] = StringDateToIso($lvalues[$v->id], false);
                        }
                    }
                }
            }
        }
        return $lvalues;
    }
    /**
     * return an attribute values
     * @return array
     */
    function getValue($aid)
    {
        if ($this->doc) {
            $oa = $this->doc->getAttribute($aid);
            if ($oa && ($oa->mvisibility != "I")) {
                if ($oa->inArray()) return $this->doc->getMultipleRawValues($oa->id);
                else return $this->doc->getRawValue($oa->id);
            }
        }
        return null;
    }
    /**
     * return document list for relation attribute
     * @return array
     */
    function getProposalDocuments($aid, $key)
    {
        if ($this->doc) {
            /**
             * @var NormalAttribute $oa
             */
            $oa = $this->doc->getAttribute($aid);
            $oa->phpfile = "fdl.php";
            $famid = $oa->format;
            $oa->phpfunc = "lfamily(D,'$famid,'$key):theid,thetitle";
            include_once ("FDL/enum_choice.php");
            $res = getResPhpFunc($this->doc, $oa, $rarg, $outselect, $outval);
            $out = array();
            foreach ($res as $v) {
                $out[] = array(
                    "display" => $v[0],
                    "id" => $v[1],
                    "title" => $v[2]
                );
            }
            return $out;
        }
        return null;
    }
    /**
     * return an attribute values
     * @return array
     */
    function setValue($aid, $nv)
    {
        if ($this->doc) {
            $err = '';
            $oa = $this->doc->getAttribute($aid);
            if ($oa && ($oa->mvisibility != "I")) $err = $this->doc->setValue($oa->id, $nv);
            if ($err == "") return true;
            else $this->setError($err);
        }
        return null;
    }
    
    function setVolatileProperty($key, $value)
    {
        if ($this->doc) {
            $this->doc->fields[] = $key;
            $this->doc->$key = $value;
            if ($this->_properties) $this->_properties[$key] = $value;
        }
    }
    
    protected function getDocPropertiesFields()
    {
        static $d = null;
        if ($d === null) $d = new Doc();
        return $d->fields;
    }
    /**
     * return all properties object like id, initid, revision, ...
     * @param bool $complete if false not set fromname, fromtitle, lastmodifiername (it is set to false in collection to increase speed)
     * @param bool $infoprop if true add informations about properties like labels
     * @return array
     */
    function getProperties($complete = true, $infoprop = false)
    {
        if ($this->_properties) return $this->_properties;
        $props = null;
        if ($this->doc) {
            $props = array();
            $dFields = $this->getDocPropertiesFields();
            foreach ($dFields as $k => $v) {
                $props[$v] = $this->doc->$v;
            }
            if (isset($this->doc->addfields)) {
                foreach ($this->doc->addfields as $k => $v) {
                    $props[$v] = $this->doc->$v;
                }
            }
            $props["icon"] = $this->doc->getIcon();
            $props["title"] = $this->doc->getTitle();
            $props["labelstate"] = $this->doc->state ? _($this->doc->state) : '';
            $props["defdoctype"] = $this->doc->defDoctype;
            
            if ($props['id'] > 0) {
                $props["mdate"] = strftime("%Y-%m-%d %H:%M:%S", $this->doc->revdate);
                $props["readonly"] = ($this->doc->canEdit() != "");
                
                $props["lockdomainid"] = intval($this->doc->lockdomainid);
                // numeric values
                if ($props["postitid"]) $props["postitid"] = $this->doc->rawValueToArray($props["postitid"]);
                else $props["postitid"] = array();
                $props["id"] = intval($props["id"]);
                $props["initid"] = intval($props["initid"]);
                $props["locked"] = intval($props["locked"]);
                $props["revision"] = intval($props["revision"]);
                $props["wid"] = intval($props["wid"]);
                $props["cvid"] = intval($props["cvid"]);
                $props["prelid"] = intval($props["prelid"]);
                $props["profid"] = intval($props["profid"]);
                $props["dprofid"] = intval($props["dprofid"]);
                $props["fromid"] = intval($props["fromid"]);
                $props["allocated"] = intval($props["allocated"]);
                $props["owner"] = intval($props["owner"]);
                if ($props["domainid"]) $props["domainid"] = $this->doc->rawValueToArray($props["domainid"]);
                else $props["domainid"] = array();
                
                if ($props["allocated"] > 0) $props["allocatedname"] = USER::getDisplayName(abs($props["allocated"]));
                $props["ownername"] = USER::getDisplayName(abs($props["owner"]));
                if ($complete) {
                    if (($this->doc->locked > 0) || ($this->doc->locked < - 1)) $props["locker"] = USER::getDisplayName(abs($props["locked"]));
                    $props["lastmodifiername"] = $this->getLastModifier();
                }
                
                $props["fromname"] = $this->doc->fromname;
                $props["fromtitle"] = $this->doc->fromtitle;
            }
            if ($this->doc->doctype == 'C') {
                $props["generateVersion"] = doubleval($this->doc->genversion);
                if ($complete) $props["configuration"] = $this->doc->getConfiguration();
            } else {
                if ($complete) {
                    $fdoc = $this->doc->getFamilyDocument();
                    if ($fdoc->isAffected()) {
                        $props["fromname"] = $fdoc->name;
                        $props["fromtitle"] = $fdoc->getTitle();
                        $props["generateVersion"] = doubleval($fdoc->genversion);
                        $props["configuration"] = $fdoc->getConfiguration();
                    }
                }
            }
            if ($this->doc && $this->doc->wid) {
                /**
                 * @var WDoc $wd
                 */
                $wd = new_doc($this->dbaccess, $this->doc->wid);
                if ($wd->isAlive()) {
                    $props["colorstate"] = $wd->getColor($this->doc->state);
                    if ($this->doc->locked != - 1) $props["activitystate"] = $wd->getActivity($this->doc->state);
                }
            }
            if ($infoprop) {
                foreach (Doc::$infofields as $k => $v) Doc::$infofields[$k]["label"] = _($v["label"]);
                $props["informations"] = Doc::$infofields; // only when search folder family
                
            }
            $this->_properties = $props;
        }
        return $props;
    }
    /**
     * return all configuration item from xml config file
     * @return array
     */
    function getConfiguration()
    {
        $conf = null;
        if ($this->doc) {
            $fdoc = $this->doc->getFamilyDocument();
            if ($this->doc->doctype == 'C') {
                $conf = $this->doc->getConfiguration();
            } else {
                $conf = $fdoc->getConfiguration();
            }
        }
        return $conf;
    }
    /**
     * return all configuration item from xml config file
     * @return array
     */
    function getSecurity()
    {
        $conf = null;
        if ($this->doc) {
            $acls = $this->doc->acls;
            foreach ($acls as $acl) {
                if (isset($this->doc->dacls[$acl])) {
                    $conf[$acl] = array(
                        "acl" => $acl,
                        "description" => $this->doc->dacls[$acl]["description"],
                        "ldescription" => ($this->doc->dacls[$acl]["description"]) ? _($this->doc->dacls[$acl]["description"]) : '',
                        "control" => $this->doc->control($acl) == ""
                    );
                }
            }
        }
        return $conf;
    }
    function getLastModifier()
    {
        if ($this->doc) {
            $thm = $this->doc->getHisto(false, "MODIFY");
            if (count($thm) > 0) return $thm[0]["uname"];
            else return USER::getDisplayName(abs($this->doc->owner));
        }
        return null;
    }
    
    function getProperty($idprop)
    {
        $idprop = strtolower($idprop);
        if (!$this->_properties) $this->getProperties();
        if (isset($this->_properties[$idprop])) return $this->_properties[$idprop];
        return null;
    }
    /**
     * return all attribute definition
     * return parameters also  only if it is a family
     * @return array
     */
    function getAttributes()
    {
        $attrs = null;
        if ($this->doc) {
            
            $props = array();
            $listattr = $this->doc->getAttributes();
            
            foreach ($listattr as $k => $oa) {
                if ($oa && ($oa->id != \Adoc::HIDDENFIELD) && ($oa->usefor != "Q")) {
                    foreach ($oa as $aid => $v) {
                        if ($aid == "options") {
                            $oa->getOption('');
                            $topt = array();
                            foreach ($oa->_topt as $ko => $vo) {
                                if ($vo) $topt[$ko] = $oa->getOption($ko);
                            }
                            $attrs[$oa->id]["options"] = $topt;
                            unset($oa->_topt);
                        } elseif (!is_object($v)) $attrs[$oa->id][$aid] = $v;
                        else if ($aid == "fieldSet") if ($v->id != \Adoc::HIDDENFIELD) $attrs[$oa->id]["parentId"] = $v->id;
                        else $attrs[$oa->id]["parentId"] = null;
                    }
                    $attrs[$oa->id]['labelText'] = $oa->getLabel();
                }
                if ($oa->type == "enum") {
                    /**
                     * @var NormalAttribute $oa
                     */
                    $attrs[$oa->id]["enumerate"] = $oa->getEnum();
                }
            }
        }
        return $attrs;
    }
    public function isCollection()
    {
        if ($this->doc) {
            return ($this->doc->defDoctype == 'D' || $this->doc->defDoctype == 'S');
        }
        return null;
    }
    /**
     * return all attribute definition
     * @return
     */
    function hasAttribute($aid)
    {
        if ($this->doc) {
            return ($this->doc->getAttribute($aid) != false);
        }
        return null;
    }
    /**
     * return properties, values and attributes definition
     */
    function getDocument($onlyvalues = false, $completeprop = true, $infoprop = false, $usertags = false)
    {
        $out = array(
            "error" => $this->error,
            "properties" => $this->getProperties($completeprop, $infoprop) ,
            "configuration" => $this->getConfiguration($completeprop) ,
            "security" => $this->getSecurity() ,
            "requestDate" => date('Y-m-d H:i:s') ,
            "values" => $this->getValues()
        );
        if ($this->onlyAttributes !== null) {
            $out["partialDocument"] = true;
        }
        
        if ($completeprop) {
            $out["followingStates"] = $this->getFollowingStates();
        }
        if ($usertags) {
            $out["userTags"] = $this->getUserTags();
        }
        
        if (!$onlyvalues) {
            $out["attributes"] = $this->getAttributes();
        }
        return $out;
    }
    /**
     * return properties, values and attributes definition
     */
    function getRevisions($onlyvalues = true, $completeprop = false)
    {
        $out = null;
        if ($this->doc) {
            $out = array();
            $rev = $this->doc->getRevisions("TABLE");
            $tmpdoc = new Fdl_Document();
            foreach ($rev as $k => $zdoc) {
                $rdoc = getDocObject($this->dbaccess, $zdoc);
                $tmpdoc->Affect($rdoc);
                $out[] = $tmpdoc->getDocument($onlyvalues, $completeprop);
            }
        }
        return $out;
    }
    /**
     * clone document
     */
    function cloneDocument($temporary = false, $linkfld = true, $copyfile = false, $title = "")
    {
        $out = null;
        if ($this->doc) {
            $clone = $this->doc->duplicate($temporary, true, $linkfld, $copyfile);
            
            if (is_object($clone)) {
                if ($title) {
                    $clone->setTitle($title);
                    $clone->disableEditControl();
                    $clone->modify();
                    if ($clone->title != $title) {
                        $clone->title = $title;
                        $clone->modify(true, array(
                            "title"
                        ) , true);
                    }
                    $clone->enableEditControl();
                }
                $dc = new Fdl_Document();
                $dc->doc = $clone;
                return $dc;
            } else {
                $this->setError($clone);
                return false;
            }
        }
        return null;
    }
    /**
     * return properties, values and attributes definition
     */
    function getHistory($allrevision = true, $code = "")
    {
        if ($this->doc) {
            $out = $this->doc->getHisto($allrevision, $code);
            foreach ($out as $k => $v) {
                $out[$k]["userName"] = $v["uname"];
                $out[$k]["userId"] = $v["uid"];
                unset($out[$k]["uname"]);
                unset($out[$k]["uid"]);
            }
            return $out;
        }
        return null;
    }
    function save()
    {
        $err = $this->doc->canEdit();
        if ($err) {
            $this->setError($err);
        } else {
            $olds = $this->doc->getOldRawValues();
            $needpostmodif = (is_array($olds));
            
            $this->doc->refresh();
            if ($needpostmodif) $this->doc->postStore();
            $err = $this->doc->modify();
            $this->setError($err);
            if ($err == "") {
                $olds = $this->doc->getOldRawValues();
                $keys = array();
                if (is_array($olds)) {
                    foreach ($olds as $ka => $va) {
                        $oa = $this->doc->getAttribute($ka);
                        $keys[] = $oa->getLabel();
                    }
                    $skeys = implode(",", $keys);
                    $this->doc->addHistoryEntry(sprintf(_("change %s") , $skeys) , HISTO_INFO, "MODIFY");
                }
            }
        }
    }
    
    function send($to = "", $cc = "", $bcc = "", $subject = "", $comment = "", $savecopy = false)
    {
        include_once ("FDL/mailcard.php");
        $err = sendCard($action, $this->doc->id, $to, $cc, $subject, "", true, $comment, "", $bcc, "html", true, array() , true, $savecopy);
        if ($err != "") {
            $this->setError($err);
        }
        return $err == "";
    }
    
    function create()
    {
        $err = $this->doc->store($info, true);
        $this->setError($err);
    }
    
    function lock($auto = false)
    {
        if ($this->doc) {
            $err = $this->doc->lock($auto);
            if ($err) $this->setError($err);
            return ($err == "");
        }
        return null;
    }
    function unlock($auto = false)
    {
        if ($this->doc) {
            $err = $this->doc->unlock($auto);
            if ($err) $this->setError($err);
            return ($err == "");
        }
        return null;
    }
    function addUserTag($tag, $comment)
    {
        if ($this->doc) {
            $err = $this->doc->addUtag($this->doc->userid, $tag, $comment);
            if ($err) $this->setError($err);
            return ($err == "");
        }
        return null;
    }
    function deleteUserTag($tag)
    {
        if ($this->doc) {
            $err = $this->doc->delUtag($this->doc->userid, $tag);
            if ($err) $this->setError($err);
            return ($err == "");
        }
        return null;
    }
    function getUserTags()
    {
        if ($this->doc) {
            $dbutags = $this->doc->searchUtags();
            $utags = array();
            foreach ($dbutags as $k => $v) {
                $utags[$v["tag"]] = $v["comment"];
            }
            return $utags;
        }
        return null;
    }
    /**
     * allocate document
     *
     * affect a document to a user
     * @param int $userid the system identifier of the user to affect
     * @param bool $revision if false no revision are made
     * @param bool $autolock if false no lock are made
     *
     * @return string error message, if no error empty string, if message
     */
    function allocate($userid, $comment = "", $revision = false, $autolock = true)
    {
        if ($this->doc) {
            $err = $this->doc->allocate($userid, $comment, $revision, $autolock);
            if ($err) $this->setError($err);
            return ($err == "");
        }
        return null;
    }
    /**
     * allocate document
     *
     * affect a document to a user
     * @param int $userid the system identifier of the user to affect
     * @param bool $revision if false no revision are made
     * @param bool $autolock if false no lock are made
     *
     * @return string error message, if no error empty string, if message
     */
    function unallocate($comment = "", $revision = false)
    {
        if ($this->doc) {
            $err = $this->doc->unallocate($comment, $revision);
            if ($err) $this->setError($err);
            return ($err == "");
        }
        return null;
    }
    function changeState($state)
    {
        if ($this->doc) {
            $err = $this->doc->setState($state);
            if ($err) $this->setError($err);
            return ($err == "");
        }
        return null;
    }
    
    function delete($really = false)
    {
        if ($this->doc) {
            $err = $this->doc->delete($really);
            if ($err) $this->setError($err);
            return ($err == "");
        }
        return null;
    }
    function restore()
    {
        if ($this->doc) {
            $err = $this->doc->control("edit");
            if ($err == "") {
                $err = $this->doc->undelete();
            }
            if ($err) $this->setError($err);
            return ($err == "");
        }
        return null;
    }
    function addRevision($comment, $version = "", $volatile = false)
    {
        if ($this->doc) {
            if ($version) $this->setVersion($version);
            if (!$comment) $comment = _("revision of document");
            $err = $this->doc->revise($comment);
            if ($err) $this->setError($err);
            return ($err == "");
        }
        return null;
    }
    function moveTo($movetoid, $fromtoid = null)
    {
        if ($this->doc) {
            $err = '';
            if (!$fromtoid) $fromtoid = $this->doc->prelid;
            // if ($fromtoid == $movetoid) return; // same destination
            $da = new_doc($this->dbaccess, $movetoid);
            if ($da->isAlive()) {
                if (method_exists($da, "addFile")) {
                    /**
                     * @var Dir $da
                     */
                    $err = $da->insertDocument($this->doc->initid);
                    if ($err == "") {
                        if (($fromtoid) && ($fromtoid != $movetoid)) {
                            $d = new_doc($this->dbaccess, $fromtoid);
                            if ($d->isAlive()) {
                                if (method_exists($d, "delFile")) {
                                    /**
                                     * @var Dir $d
                                     */
                                    $err = $d->removeDocument($this->doc->initid);
                                    if ($err == "") {
                                        $this->doc->prelid = $da->initid;
                                        $this->doc->modify(true, array(
                                            "prelid"
                                        ) , true);
                                    }
                                } else $err = sprintf(_("document %s is not a folder") , $d->getTitle());
                            }
                        } else {
                            if ($err == "") {
                                $this->doc->prelid = $da->initid;
                                $this->doc->modify(true, array(
                                    "prelid"
                                ) , true);
                            }
                        }
                    }
                } else $err = sprintf(_("document %s is not a folder") , $da->getTitle());
            }
            
            if ($err) $this->setError($err);
            return ($err == "");
        }
        return null;
    }
    function setFileValueFromHttpVars()
    {
        include_once ("FDL/modcard.php");
        foreach ($_FILES as $k => $v) {
            if ($this->hasAttribute($k)) {
                $oldname = $this->doc->vault_filename($k);
                $filename = insert_file($this->doc, $k, true);
                if ($filename != "") {
                    $this->setValue($k, $filename);
                    if ($this->doc->vault_filename($k) == "Unknown") $this->doc->renameFile($k, $oldname);
                }
            }
        }
    }
    
    function setValueFromHttpVars()
    {
        if (!$this->doc) return false;
        $as = $this->getAttributes();
        foreach ($as as $aid => $oa) {
            $nv = getHttpVars($aid, null);
            if ($nv !== null) {
                if ($nv === '') $nv = ' ';
                if ($nv[0] == '[') {
                    $oa = $this->doc->getAttribute($aid);
                    if ($oa->isMultiple()) $nv = json_decode($nv);
                }
                $this->setValue($aid, $nv);
            }
        }
        return true;
    }
    function setLogicalName()
    {
        if ($this->doc && $this->doc->name == '') {
            $name = getHttpVars("name");
            if ($name) {
                $err = $this->doc->setLogicalName($name);
                if ($err) {
                    $this->setError($err);
                    return false;
                }
                return true;
            }
        }
        return null;
    }
    
    function hasWaitingFiles()
    {
        if ($this->doc) {
            return $this->doc->hasWaitingFiles();
        }
        return null;
    }
    function setVersion($version, $usecomment = true)
    {
        $la = $this->doc->attributes->getNormalAttributes();
        $hasversion = false;
        $err = "";
        foreach ($la as $at) {
            if ($at->getOption("version") == "yes") {
                $err = $this->doc->setValue($at->id, $version);
                $hasversion = true;
                if ((!$err) && $usecomment) $this->doc->addHistoryEntry(sprintf(_("change version to %s") , $version));
                break;
            }
        }
        if (($err == "") && (!$hasversion)) {
            $this->doc->version = trim($version);
            if ($usecomment) {
                if ($version == "") $this->doc->addHistoryEntry(sprintf(_("reset version")));
                else $this->doc->addHistoryEntry(sprintf(_("change version to %s") , $version));
            }
        }
        
        $this->setError($err);
    }
    
    function getFollowingStates()
    {
        if ($this->doc && $this->doc->wid) {
            /**
             * @var WDoc $wd
             */
            $wd = new_doc($this->dbaccess, $this->doc->wid);
            if (!$wd->isAlive()) return null;
            $wd->set($this->doc);
            $ns = $wd->getFollowingStates();
            addLogMsg($ns);
            $ts = array();
            foreach ($ns as $state) {
                if ($state) {
                    $tlabel = "";
                    foreach ($wd->cycle as $k => $t) {
                        if (($t["e1"] == $this->doc->state) && ($t["e2"] == $state)) {
                            $tlabel = $t["t"];
                            break;
                        }
                    }
                    $ts[] = array(
                        "state" => $state,
                        "transition" => $tlabel,
                        "transitionLabel" => $tlabel ? _($tlabel) : '',
                        "label" => _($state) ,
                        "color" => $wd->getColor($state)
                    );
                }
            }
            return $ts;
        } else return null;
    }
    
    function getAttachedTimers()
    {
        $prev = array();
        $timers = $this->doc->getAttachedTimers();
        foreach ($timers as $k => $v) {
            /**
             * @var \Dcp\Family\TIMER $timer
             */
            $timer = new_doc($this->dbaccess, $v["timerid"]);
            if ($timer->isAlive()) {
                $iprev = $timer->getPrevisions($v["attachdate"], $v["tododate"], $v["level"]);
                foreach ($iprev as $pk => $pv) {
                    $iprev[$pk]["timerid"] = $timer->id;
                    $iprev[$pk]["timertitle"] = $timer->getTitle();
                }
                $prev = array_merge($prev, $iprev);
            }
        }
        foreach ($prev as $k => $v) {
            $prev[$k]["local"]["lstate"] = "";
            $prev[$k]["local"]["lmethod"] = "";
            $prev[$k]["local"]["tmailtitle"] = "";
            if ($v["actions"]["state"]) $prev[$k]["local"]["lstate"] = _($v["actions"]["state"]);
            else $prev[$k]["local"]["lstate"] = false;
            if ($v["actions"]["tmail"]) {
                $prev[$k]["local"]["tmailtitle"] = $this->doc->getTitle($v["actions"]["tmail"]);
            }
            if ($v["actions"]["method"]) $prev[$k]["local"]["lmethod"] = _($v["actions"]["method"]);
            else $prev[$k]["local"]["lmethod"] = false;
            $prev[$k]["local"]["hdelay"] = $this->humandelay($v["execdelay"]);
        }
        usort($prev, array(
            get_class($this) ,
            "sortprevision"
        ));
        
        return ($prev);
    }
    static function sortprevision($a, $b)
    {
        if ($a["execdelay"] > $b["execdelay"]) return 1;
        elseif ($a["execdelay"] < $b["execdelay"]) return -1;
        return 0;
    }
    
    static function humandelay($dd)
    {
        $s = "";
        if ($dd > 1) {
            $j = intval($dd);
            if ($j > 1) $s.= sprintf(_("%d days") , $j);
            else $s.= sprintf(_("%d day") , $j);
            $s.= " ";
            $dd = $dd - $j;
        }
        $dd = $dd * 24;
        
        if ($dd > 1) {
            $j = intval($dd);
            if ($j > 1) $s.= sprintf(_("%d hours") , $j);
            else $s.= sprintf(_("%d hour") , $j);
            $s.= " ";
            $dd = $dd - $j;
        }
        $dd = $dd * 60;
        if ($dd > 1) {
            $j = intval($dd);
            if ($j > 1) $s.= sprintf(_("%d minutes") , $j);
            else $s.= sprintf(_("%d minute") , $j);
            $s.= " ";
            $dd = $dd - $j;
        }
        return $s;
    }
}
