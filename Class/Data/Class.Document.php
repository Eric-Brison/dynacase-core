<?php

/**
 * Document Object Definition
 *
 * @author Anakeen 2002
 * @version $Id: Class.Doc.php,v 1.562 2009/01/14 09:18:05 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package API
 */
/**
 */
include_once("FDL/Class.Doc.php");
/**
 * Document Class
 *
 */
Class Fdl_Document  {

    protected $doc;
    function __construct($id=0, $config=null) {
        $this->dbaccess=getParam("FREEDOM_DB");
        if (isset($config->latest)) $latest=$config->latest;
        else $latest=true;
        if ($id) {
            $this->doc=new_doc($this->dbaccess, $id,$latest);
            if (! $this->doc->isAffected()) $this->error=sprintf(_("document %s not exist"),$id);
            if (! $this->error) {
                $this->error=$this->doc->control('view');
                if ($this->error) $this->error=sprintf(_("no privilege view for %d"),$id);
            }
            if ($this->error) unset($this->doc);
            else {
                $this->doc->refresh();
                $mask=$this->doc->getDefaultView(false,"mask");
                if ($mask) $this->doc->applyMask($mask);
            }
        }
    }
    /**
     * create document : not set in dataabse until it was saved
     * @param string $familyId the family identificator
     * @param boolean $temporary set to true if need only a temporary document
     * @return void
     */
    function createDocument($familyId,$temporary=false) {
        if ($temporary) $cdoc=createTmpDoc($this->dbaccess,$familyId);
        else $cdoc=createDoc($this->dbaccess,$familyId);
        if ($cdoc) {
            $this->doc=$cdoc;
        } else {
            $this->setError(sprintf(_("cannot create document for family [%s]"),$familyId));
        }
    }

    function docisset() {
        return is_object($this->doc);
    }
    function setError($err) {
        if ($err) $this->error=$err;
    }
    function isAlive() {
        if ($this->doc) return $this->doc->isAlive();
        return null;
    }
    function affect(&$doc) {
        $this->doc=$doc;
        $this->_properties=null;
        $this->error=$this->doc->control('view');
        if ($this->error) unset($this->doc);
    }
    function getAttribute($aid) {
        if ($this->doc) {
            return $this->doc->getAttribute($aid);
        }
        return null;
    }

    function getInternalDocument() {
        return $this->doc;
    }
    /**
     * return all attribute values
     * @return array
     */
    function getValues() {
        if ($this->doc) {
            $lvalues=array();
            if ($this->doc->doctype=='C') {
                foreach ($this->doc->addfields as $aid) {
                    $lvalues[$aid] = $this->doc->$aid;
                }
            } else {
                $nattr = $this->doc->getNormalAttributes();
                foreach($nattr as $k=>$v) {
                    if ($v->mvisibility!="I" && $this->doc->$k) {
                        if ($v->inArray() || ($v->getOption("multiple")=="yes")) $lvalues[$v->id] = $this->doc->GetTValue($v->id);
                        else $lvalues[$v->id] = $this->doc->GetValue($v->id);
                        
                        if (($v->type=="docid") && ($v->visibility!='H') && ($v->getOption("doctitle")!="auto")) {
                            $lvalues[$v->id."_title"]=$this->doc->getTitle($this->doc->getValue($v->id));
                            if ($v->inArray() || ($v->getOption("multiple")=="yes"))  $lvalues[$v->id."_title"]=$this->doc->_val2array($lvalues[$v->id."_title"]);
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
    function getValue($aid) {
        if ($this->doc) {
            $oa=$this->doc->getAttribute($aid);
            if ($oa &&  ($oa->mvisibility!="I")) {
                if ($oa->inArray()) return $this->doc->GetTValue($oa->id);
                else return $this->doc->GetValue($oa->id);
            }
        }
        return null;
    }
    /**
     * return document list for relation attribute
     * @return array
     */
    function getProposalDocuments($aid,$key) {
        if ($this->doc) {
            $oa=$this->doc->getAttribute($aid);
            $oa->phpfile="fdl.php";
            $famid=$oa->format;
            $oa->phpfunc="lfamily(D,'$famid,'$key):theid,thetitle";
            include_once("FDL/enum_choice.php");
            $res=getResPhpFunc($this->doc,$oa,$rarg,$outselect, $outval);
            $out=array();
            foreach ($res as $v) {
                $out[]=array("display"=>$v[0],
		     "id"=>$v[1],
		     "title"=>$v[2]);
            }
            return $out;
        }
        return null;
    }


    /**
     * return an attribute values
     * @return array
     */
    function setValue($aid,$nv) {
        if ($this->doc) {
            $oa=$this->doc->getAttribute($aid);
            if ($oa &&  ($oa->mvisibility!="I")) $err=$this->doc->setValue($oa->id,$nv);
            if ($err=="") return true;
            else $this->setError($err);
        }
        return null;
    }

    function setVolatileProperty($key,$value) {
        if ($this->doc) {
            $this->doc->fields[]=$key;
            $this->doc->$key=$value;
            if ($this->_properties)  $this->_properties[$key]=$value;
        }
    }
    /**
     * return all properties object like id, initid, revision, ...
     * @param bool $complete if false not set fromname, fromtitle, lastmodifiername (it is set to false in collection to increase speed)
     * @param bool $infoprop if true add informations about properties like labels
     * @return array
     */
    function getProperties($complete=true,$infoprop=false) {
        if ($this->_properties) return $this->_properties;
        $props=null;
        if ($this->doc) {
            $props=array();
            foreach($this->doc->fields as $k=>$v) {
                if (is_numeric($k)) $props[$v] = $this->doc->$v;
            }
            if ($this->doc->addfields) {
                foreach($this->doc->addfields as $k=>$v) {
                    $props[$v] = $this->doc->$v;
                }

            }
            $props["icon"]=$this->doc->getIcon();
            $props["title"]=$this->doc->getTitle();
            $props["labelstate"]=$this->doc->state?_($this->doc->state):'';
            $props["defdoctype"]=$this->doc->defDoctype;
            
            if ($props['id'] > 0) {
                $props["mdate"]=strftime("%d/%m/%Y %H:%M:%S",$this->doc->revdate);
                $props["readonly"]=($this->doc->canEdit()!="");

                // numeric values
                if ($props["postitid"]) $props["postitid"]=$this->doc->_val2array($props["postitid"]);
                else $props["postitid"]=array();
                $props["id"]=intval($props["id"]);
                $props["initid"]=intval($props["initid"]);
                $props["locked"]=intval($props["locked"]);
                $props["revision"]=intval($props["revision"]);
                $props["wid"]=intval($props["wid"]);
                $props["cvid"]=intval($props["cvid"]);
                $props["prelid"]=intval($props["prelid"]);
                $props["forumid"]=intval($props["forumid"]);
                $props["profid"]=intval($props["profid"]);
                $props["dprofid"]=intval($props["dprofid"]);
                $props["fromid"]=intval($props["fromid"]);
                $props["allocated"]=intval($props["allocated"]);
                $props["owner"]=intval($props["owner"]);



                if ($props["allocated"]>0) $props["allocatedname"]=USER::getDisplayName(abs($props["allocated"]));
                $props["ownername"]=USER::getDisplayName(abs($props["owner"]));
                if ($complete) {
                    if (($this->doc->locked > 0) ||($this->doc->locked < -1))  $props["locker"]=USER::getDisplayName(abs($props["locked"]));
                    $props["lastmodifiername"]=$this->getLastModifier();
                }
                
                $props["fromname"]=$this->doc->fromname;
                $props["fromtitle"]=$this->doc->fromtitle;
            }
            if ($this->doc->doctype=='C') {
                $props["generateVersion"]=doubleval($this->doc->genversion);
                if ($complete) $props["configuration"]=$this->doc->getConfiguration();
            } else {
                if ($complete) {
                    $fdoc=$this->doc->getFamDoc();
                    if ($fdoc->isAffected()) {
            $props["fromname"]=$fdoc->name;
            $props["fromtitle"]=$fdoc->getTitle();
                $props["generateVersion"]=doubleval($fdoc->genversion);
                 $props["configuration"]=$fdoc->getConfiguration();
                    }
                }
            }
            if  ($this->doc && $this->doc->wid) {
                $wd=new_doc($this->dbaccess,$this->doc->wid);
                if ($wd->isAlive()) {
                    $props["colorstate"]=$wd->getColor($this->doc->state);
                    if ($this->doc->locked != -1) $props["activitystate"]=$wd->getActivity($this->doc->state);
                }
            }


            foreach ($this->doc->infofields as $k=>$v) $this->doc->infofields[$k]["label"]=_($v["label"]);
            if ($infoprop) $props["informations"]=$this->doc->infofields; // only when search folder family
            $this->_properties=$props;
        }
        return $props;
    }
    /**
     * return all configuration item from xml config file
     * @return array
     */
    function getConfiguration() {
        $conf=null;
        if ($this->doc) {
            $fdoc=$this->doc->getFamDoc();
            if ($this->doc->doctype=='C') {
                $conf=$this->doc->getConfiguration();
            } else {
                $conf=$fdoc->getConfiguration();
            }
        }
        return $conf;
    }
    /**
     * return all configuration item from xml config file
     * @return array
     */
    function getSecurity() {
        $conf=null;
        if ($this->doc) {
            $acls=$this->doc->acls;
            foreach ($acls as $acl) {
                $conf[$acl]=array("acl"=>$acl,
		      "description"=>$this->doc->dacls[$acl]["description"],
		      "ldescription"=>($this->doc->dacls[$acl]["description"])?_($this->doc->dacls[$acl]["description"]):'',
		      "control"=>$this->doc->control($acl)=="");

            }
        }
        return $conf;
    }
    function getLastModifier() {
        if ($this->doc) {
            $thm=$this->doc->getHisto(false,"MODIFY");
            if (count($thm) > 0) return $thm[0]["uname"];
            else return USER::getDisplayName(abs($this->doc->owner));
        }
        return null;
    }

    function getProperty($idprop) {
        $idprop=strtolower($idprop);
        if (! $this->_properties) $this->getProperties();
        if (isset($this->_properties[$idprop])) return $this->_properties[$idprop];
        return null;
    }
    /**
     * return all attribute definition
     * return parameters also  only if it is a family
     * @return array
     */
    function getAttributes() {
        $attrs=null;
        if ($this->doc) {
            $props=array();
            $listattr = $this->doc->getAttributes();

            foreach($listattr as $k=>$oa) {
                if ($oa && ($oa->id != 'FIELD_HIDDENS')&& (($this->doc->doctype=='C')&&($oa->usefor != "Q"))) {
                    foreach ($oa as $aid=>$v) {
                        if ($aid=="options") {
                            $oa->getOption('');
                            $topt=array();
                            foreach ($oa->_topt as $ko=>$vo) {
                                if ($vo)  $topt[$ko]=$oa->getOption($ko);
                            }
                            $attrs[$oa->id]["options"]=$topt;
                            unset($oa->_topt);
                        } elseif (! is_object($v)) $attrs[$oa->id][$aid]=$v;
                        else if ($aid=="fieldSet") if ($v->id != 'FIELD_HIDDENS') $attrs[$oa->id]["parentId"]=$v->id; else $attrs[$oa->id]["parentId"]=null;
                    }
                }
                if ($oa->type=="enum") {
                    $attrs[$oa->id]["enumerate"]=$oa->getEnum();
                }

            }

        }
        return $attrs;
    }
    public function isCollection() {
        if ($this->doc) {
            return ($this->doc->defDoctype=='D' || $this->doc->defDoctype=='S');
        }
        return null;
    }
    /**
    * return all attribute definition
    * @return
    */
    function hasAttribute($aid) {
        if ($this->doc) {
            return ($this->doc->getAttribute($aid)!=false);
        }
        return null;
    }
    /**
     * return properties, values and attributes definition
     */
    function getDocument($onlyvalues=false,$completeprop=true,$infoprop=false,$usertags=false) {
        $out=array("error"=>$this->error,
	       "properties"=>$this->getProperties($completeprop,$infoprop),
	       "configuration"=>$this->getConfiguration($completeprop),
	       "security"=>$this->getSecurity(),
	       "requestDate"=>microtime(true),
	       "values"=>$this->getValues());

        if ($completeprop) {
            $out["followingStates"]=$this->getFollowingStates();
        }
        if ($usertags) {
            $out["userTags"]=$this->getUserTags();
        }
        if (! $onlyvalues) {
            $out["attributes"]=$this->getAttributes();
        }
        return $out;
    }
    /**
     * return properties, values and attributes definition
     */
    function getRevisions($onlyvalues=true,$completeprop=false) {
        $out=null;
        if ($this->doc) {
            $out=array();
            $rev=$this->doc->getRevisions("TABLE");
            $tmpdoc=new Fdl_Document();
            foreach($rev as $k=>$zdoc) {
                $rdoc=getDocObject($this->dbaccess,$zdoc);
                $tmpdoc->Affect($rdoc);
                $out[]=$tmpdoc->getDocument($onlyvalues,$completeprop);
            }
        }
        return $out;
    }

    /**
     * clone document
     */
    function cloneDocument($temporary=false,$linkfld=true,$copyfile=false,$title="") {
        $out=null;
        if ($this->doc) {
            $clone=$this->doc->copy($temporary,true,$linkfld,$copyfile);
           
            if (is_object($clone)) {
                if ($title) {
                    $clone->setTitle($title);
                    $clone->disableEditControl();
                    $clone->modify();
                    if ($clone->title != $title) {
                        $clone->title=$title;
                        $clone->modify(true,array("title"),true);
                    }
                    $clone->enableEditControl();
                }
                $dc=new Fdl_Document();
                $dc->doc=$clone;
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
    function getHistory($allrevision=true,$code="") {
        if ($this->doc) {
            $out=$this->doc->getHisto($allrevision,$code);
            foreach ($out as $k=>$v) {
                $out[$k]["userName"]=$v["uname"];
                $out[$k]["userId"]=$v["uid"];
                unset($out[$k]["uname"]);
                unset($out[$k]["uid"]);
            }
            return $out;
        }
        return null;
    }
    function save() {
        $err=$this->doc->canEdit();
        if ($err) {
            $this->setError($err);
        } else {
            $olds=$this->doc->getOldValues();
            $needpostmodif=(is_array($olds));

            $this->doc->refresh();
            if ($needpostmodif) $this->doc->postModify();
            $err=$this->doc->modify();
            $this->setError($err);
            if ($err=="") {
                $olds=$this->doc->getOldValues();
                $keys=array();
                if (is_array($olds)) {
                    foreach ($olds as $ka=>$va) {
                        $oa=$this->doc->getAttribute($ka);
                        $keys[]=$oa->getLabel();
                    }
                    $skeys=implode(",",$keys);
                    $this->doc->Addcomment(sprintf(_("change %s"),$skeys),HISTO_INFO,"MODIFY");
                }
            }
        }
    }
    
    function send($to="",$cc="",$bcc="",$subject="",$comment="",$savecopy=false) {
    	include_once("FDL/mailcard.php");
    	$err = sendCard($action,$this->doc->id,$to,$cc,$subject,"",true,$comment,"",$bcc,"html",true,array(),true,$savecopy);
    	if($err!=""){
    		$this->setError($err);
    	}
    	return $err=="";
    }

    function create() {
        $this->doc->refresh();
        $this->doc->postModify();
        $err=$this->doc->add();
        $this->setError($err);
    }

    function lock($auto=false) {
        if ($this->doc) {
            $err=$this->doc->lock($auto);
            if ($err) $this->setError($err);
            return ($err=="");
        }
        return null;
    }
    function unlock($auto=false) {
        if ($this->doc) {
            $err=$this->doc->unlock($auto);
            if ($err) $this->setError($err);
            return ($err=="");
        }
        return null;
    }
    function addUserTag($tag, $comment) {
        if ($this->doc) {
            $err=$this->doc->addUtag($this->doc->userid,$tag,$comment);
            if ($err) $this->setError($err);
            return ($err=="");
        }
        return null;
    }
    function deleteUserTag($tag) {
        if ($this->doc) {
            $err=$this->doc->delUtag($this->doc->userid,$tag);
            if ($err) $this->setError($err);
            return ($err=="");
        }
        return null;
    }
    function getUserTags() {
        if ($this->doc) {
            $dbutags=$this->doc->searchUtags();
            $utags=array();
            foreach ($dbutags as $k=>$v) {
                $utags[$v["tag"]]=$v["comment"];
            }
            return $utags;
        }
        return null;
    }
    /**
     * allocate document
     *
     * affect a document to a user
     * @param int $userid the system identificator of the user to affect
     * @param bool $revision if false no revision are made
     * @param bool $autolock if false no lock are made
     *
     * @return string error message, if no error empty string, if message
     */
    function allocate($userid,$comment="",$revision=false,$autolock=true) {
        if ($this->doc) {
            $err=$this->doc->allocate($userid,$comment,$revision,$autolock);
            if ($err) $this->setError($err);
            return ($err=="");
        }
        return null;
    }  /**
    * allocate document
    *
    * affect a document to a user
    * @param int $userid the system identificator of the user to affect
    * @param bool $revision if false no revision are made
    * @param bool $autolock if false no lock are made
    *
    * @return string error message, if no error empty string, if message
    */
    function unallocate($comment="",$revision=false) {
        if ($this->doc) {
            $err=$this->doc->unallocate($comment,$revision);
            if ($err) $this->setError($err);
            return ($err=="");
        }
        return null;
    }
    function changeState($state) {
        if ($this->doc) {
            $err=$this->doc->setState($state);
            if ($err) $this->setError($err);
            return ($err=="");
        }
        return null;
    }

    function delete($really=false) {
        if ($this->doc) {
            $err=$this->doc->delete($really);
            if ($err) $this->setError($err);
            return ($err=="");
        }
        return null;
    }
    function restore() {
        if ($this->doc) {
            $err=$this->doc->control("edit");
            if ($err=="") {
                $err=$this->doc->revive();
            }
            if ($err) $this->setError($err);
            return ($err=="");
        }
        return null;
    }
    function addRevision($comment, $version="", $volatile=false) {
        if ($this->doc) {
            if ($version) $this->setVersion($version);
            if (! $comment) $comment=_("revision of document");
            $err=$this->doc->addRevision($comment);
            if ($err) $this->setError($err);
            return ($err=="");
        }
        return null;
    }
    function moveTo($movetoid,$fromtoid=null) {
        if ($this->doc) {
            if (! $fromtoid) $fromtoid=$this->doc->prelid;
            // if ($fromtoid == $movetoid) return; // same destination
            $da=new_doc($this->dbaccess, $movetoid);
            if ($da->isAlive()) {
                if (method_exists($da,"addFile")) {
                    $err=$da->addFile($this->doc->initid);
                    if ($err=="")  {
                        if (($fromtoid) && ($fromtoid != $movetoid)) {
                            $d=new_doc($this->dbaccess, $fromtoid);
                            if ($d->isAlive()) {
                                if (method_exists($d,"delFile")) {
                                    $err=$d->delFile($this->doc->initid);
                                    if ($err=="") {
                                        $this->doc->prelid=$da->initid;
                                        $this->doc->modify(true,array("prelid"),true);
                                    }
                                } else $err=sprintf(_("document %s is not a folder"),$d->getTitle());
                            }
                        } else {
                            if ($err=="") {
                                $this->doc->prelid=$da->initid;
                                $this->doc->modify(true,array("prelid"),true);
                            }
                        }
                    }
                } else $err=sprintf(_("document %s is not a folder"),$da->getTitle());
            }
             
            if ($err) $this->setError($err);
            return ($err=="");
        }
        return null;
    }
    function setFileValueFromHttpVars() {
        include_once("FDL/modcard.php");
        foreach ($_FILES as $k=>$v)    {
            if ($this->hasAttribute($k)) {
                $oldname=$this->doc->vault_filename($k);
                $filename=insert_file($this->doc,$k,true);
                if ($filename != "") {
                    $this->setValue($k,$filename);
                    if ($this->doc->vault_filename($k)=="Unknown") $this->doc->renameFile($k,$oldname);
                }

            }
        }
    }

    function setValueFromHttpVars() {
        if (! $this->doc) return false;
        $as=$this->getAttributes();
        foreach ($as  as $aid=>$oa) {
            $nv=getHttpVars($aid,null);
            if ($nv !== null) {
                if ($nv === '') $nv=' ';
                if ($nv[0]=='[') {
                    $oa=$this->doc->getAttribute($aid);
                    if ($oa->isMultiple()) $nv=json_decode($nv);
                }
                $this->setValue($aid,$nv);
            }
        }
        return true;
    }
    function setLogicalIdentificator() {
        if ($this->doc && $this->doc->name=='') {
            $name=getHttpVars("name");
            if ($name) {
                $err=$this->doc->setLogicalIdentificator($name);
                if ($err) {
                    $this->setError($err);
                    return false;
                }
                return true;
            }
        }
        return null;
    }

    function hasWaitingFiles() {
        if ($this->doc ) {
            return $this->doc->hasWaitingFiles();
        }
        return null;
    }
    function setVersion($version, $usecomment=true) {
        $la = $this->doc->GetNormalAttributes();
        $hasversion=false;
        $err="";
        foreach ($la as $at) {
            if ($at->getOption("version")=="yes") {
                $err=$this->doc->setValue($at->id,$version);
                $hasversion=true;
                if ((! $err) && $usecomment) $this->doc->addComment(sprintf(_("change version to %s"),$version));
                break;
            }
        }
        if (($err=="")&& (!$hasversion)) {
            $this->doc->version=trim($version);
            if ($usecomment) {
                if ($version=="") $this->doc->addComment(sprintf(_("reset version")));
                else $this->doc->addComment(sprintf(_("change version to %s"),$version));
            }
        }

        $this->setError($err);
    }

    function getFollowingStates() {
        if ($this->doc && $this->doc->wid) {
            $wd=new_doc($this->dbaccess,$this->doc->wid);
            if (! $wd->isAlive()) return null;
            $wd->set($this->doc);
            $ns=$wd->getFollowingStates();
            addLogMsg($ns);
            $ts=array();
            foreach ($ns as $state) {
                if ($state) {
                    $tlabel="";
                    foreach ($wd->cycle as $k=>$t) {
                        if (($t["e1"]==$this->doc->state) && ($t["e2"]==$state)) {
                            $tlabel=$t["t"];
                            break;
                        }
                    }
                    $ts[]=array("state"=>$state,
		      "transition"=>$tlabel,
		      "transitionLabel"=>$tlabel?_($tlabel):'',
		      "label"=>_($state),
		      "color"=>$wd->getColor($state));
                }
            }
            return $ts;

        } else return null;
    }

    function getAttachedTimers() {
        $prev=array();
        $timers=$this->doc->getAttachedTimers();
        foreach ($timers as $k=>$v) {
            $timer=new_doc($this->dbaccess,$v["timerid"]);
            if ($timer->isAlive()) {
                $iprev=$timer->getPrevisions($v["attachdate"],$v["tododate"],$v["level"]);
                foreach ($iprev as $pk=>$pv) {
                    $iprev[$pk]["timerid"]=$timer->id;
                    $iprev[$pk]["timertitle"]=$timer->getTitle();
                }
                $prev=array_merge($prev,$iprev);
            }
        }
        foreach ($prev as $k=>$v) {
            $prev[$k]["local"]["lstate"]="";
            $prev[$k]["local"]["lmethod"]="";
            $prev[$k]["local"]["tmailtitle"]="";
            if ($v["actions"]["state"]) $prev[$k]["local"]["lstate"]=_($v["actions"]["state"]);
            else $prev[$k]["local"]["lstate"]=false;
            if ($v["actions"]["tmail"]) {
                $prev[$k]["local"]["tmailtitle"]=$this->doc->getTitle($v["actions"]["tmail"]);
            }
            if ($v["actions"]["method"]) $prev[$k]["local"]["lmethod"]=_($v["actions"]["method"]);
            else $prev[$k]["local"]["lmethod"]=false;
            $prev[$k]["local"]["hdelay"]=$this->humandelay($v["execdelay"]);
        }
        usort($prev,array (get_class($this), "sortprevision"));

        return($prev);
    }
    static function sortprevision($a,$b) {
        if ($a["execdelay"] > $b["execdelay"]) return 1;
        elseif ($a["execdelay"] < $b["execdelay"]) return -1;
        return 0;
    }

    static function humandelay($dd) {
        $s="";
        if ($dd > 1) {
            $j=intval($dd);
            if ($j>1) $s.=sprintf(_("%d days"),$j);
            else $s.=sprintf(_("%d day"),$j);
            $s.= " ";
            $dd=$dd-$j;
        }
        $dd=$dd*24;

        if ($dd > 1) {
            $j=intval($dd);
            if ($j>1) $s.=sprintf(_("%d hours"),$j);
            else $s.=sprintf(_("%d hour"),$j);
            $s.= " ";
            $dd=$dd-$j;
        }
        $dd=$dd*60;
        if ($dd > 1) {
            $j=intval($dd);
            if ($j>1) $s.=sprintf(_("%d minutes"),$j);
            else $s.=sprintf(_("%d minute"),$j);
            $s.= " ";
            $dd=$dd-$j;
        }
        return $s;
    }
}
?>