<?php
/**
 * Document Object Definition
 *
 * @author Anakeen 2002
 * @version $Id: Class.Doc.php,v 1.562 2009/01/14 09:18:05 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */
/**
 */

include_once("Class.QueryDb.php");
include_once("Lib.FileMime.php");
include_once("FDL/Class.DocCtrl.php");
include_once("FDL/freedom_util.php");
include_once("FDL/Class.DocVaultIndex.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/Class.DocHisto.php");
include_once('FDL/Class.ADoc.php');


// define constant for search attributes in concordance with the file "init.freedom"

/**#@+
 * constant for document family identificator in concordance with the file "FDL/init.freedom"
 * 
 */
define ("FAM_BASE", 1);
define ("FAM_DIR", 2);
define ("FAM_ACCESSDOC", 3);
define ("FAM_ACCESSDIR", 4);
define ("FAM_SEARCH", 5);
define ("FAM_ACCESSSEARCH", 6);
define ("FAM_ACCESSFAM", 23);
define ("MENU_ACTIVE", 1);
define ("MENU_INVISIBLE", 2);
define ("MENU_INACTIVE", 0);

define('POPUP_INACTIVE',0);
define('POPUP_ACTIVE',1);
define('POPUP_CTRLACTIVE',3);
define('POPUP_CTRLINACTIVE',4);
define('POPUP_INVISIBLE',2);

define ("DELVALUE", 'DEL??');
/**#@-*/
/**
 * max cache document
 */
define ("MAXGDOCS", 20);

define("REGEXPFILE","([^\|]*)\|([0-9]*)\|?(.*)?");
define("PREGEXPFILE", "/([^\|]*)\|([0-9]*)\|?(.*)?/");
/**
 * Document Class
 *
 */
Class Doc extends DocCtrl {
  public $fields = array ( "id","owner","title",
			   "revision","version",
			   "initid","fromid",
			   "doctype",
			   "locked",
			   "allocated",
			   "archiveid",
			   "icon",
			   "lmodify",
			   "profid",
			   "usefor",
			   "cdate",
			   "adate",
			   "revdate",
			   "comment",
			   "classname",
			   "state",
			   "wid",
			   "postitid",
			   "forumid",
			   "cvid",
			   "name",
			   "dprofid",
			   "atags",
			   "prelid",
			   "confidential",
			   "ldapdn");

  public $sup_fields= array("values",
			    "attrids"); // not be in fields else trigger error

  public $infofields = array ( "id"=>array("type"=>"integer","displayable"=>true,"sortable"=>true,"filterable"=>true,"label"=>"prop_id"), # N_("prop_id")
			       "owner"=>array("type"=>"uid","displayable"=>true,"sortable"=>true,"filterable"=>true,"label"=>"prop_owner"), # N_("prop_owner"),
			       "icon"=>array("type"=>"image","displayable"=>true,"sortable"=>false,"filterable"=>false,"label"=>"prop_icon"), # N_("prop_icon"),
			       "title"=>array("type"=>"text","displayable"=>true,"sortable"=>true,"filterable"=>true,"label"=>"prop_title"), # N_("prop_title"),
			       "revision"=>array("type"=>"integer","displayable"=>true,"sortable"=>true,"filterable"=>true,"label"=>"prop_revision"), # N_("prop_revision"),
			       "version"=>array("type"=>"text","displayable"=>true,"sortable"=>true,"filterable"=>true,"label"=>"prop_version"), # N_("prop_version"),
			       "initid"=>array("type"=>"docid","displayable"=>true,"sortable"=>true,"filterable"=>true,"label"=>"prop_initid"), # N_("prop_initid"),
			       "fromid"=>array("type"=>"docid","displayable"=>true,"sortable"=>true,"filterable"=>true,"label"=>"prop_fromid"), # N_("prop_fromid"),
			       "doctype"=>array("type"=>"text","displayable"=>false,"sortable"=>true,"filterable"=>true,"label"=>"prop_doctype"), # N_("prop_doctype"),
			       "locked"=>array("type"=>"uid","displayable"=>true,"sortable"=>false,"filterable"=>false,"label"=>"prop_locked"), # N_("prop_locked"),
			       "allocated"=>array("type"=>"uid","displayable"=>false,"sortable"=>true,"filterable"=>true,"label"=>"prop_allocated"), # N_("prop_allocated"),
			       "lmodify"=>array("type"=>"text","displayable"=>false,"sortable"=>false,"filterable"=>false,"label"=>"prop_lmodify"), # N_("prop_lmodify"),
			       "profid"=>array("type"=>"integer","displayable"=>false,"sortable"=>false,"filterable"=>false,"label"=>"prop_profid"), # N_("prop_profid"),
			       "usefor"=>array("type"=>"text","displayable"=>false,"sortable"=>false,"filterable"=>false,"label"=>"prop_usefor"), # N_("prop_usefor")
			       "cdate"=>array("type"=>"timestamp","displayable"=>true,"sortable"=>true,"filterable"=>true,"label"=>"prop_cdate"), # N_("prop_cdate")
			       "adate"=>array("type"=>"timestamp","displayable"=>true,"sortable"=>true,"filterable"=>true,"label"=>"prop_adate"), # N_("prop_adate"),
			       "revdate"=>array("type"=>"timestamp","displayable"=>true,"sortable"=>true,"filterable"=>true,"label"=>"prop_revdate"), # N_("prop_revdate"),
			       "comment"=>array("type"=>"text","displayable"=>false,"sortable"=>false,"filterable"=>false,"label"=>"prop_comment"), # N_("prop_comment"),
			       "classname"=>array("type"=>"text","displayable"=>false,"sortable"=>false,"filterable"=>false,"label"=>"prop_classname"), # N_("prop_classname")
			       "state"=>array("type"=>"text","displayable"=>true,"sortable"=>true,"filterable"=>true,"label"=>"prop_state"), # N_("prop_state"),
			       "wid"=>array("type"=>"docid","displayable"=>false,"sortable"=>false,"filterable"=>false,"label"=>"prop_wid"), # N_("prop_wid")
			       "postitid"=>array("type"=>"text","displayable"=>false,"sortable"=>false,"filterable"=>false,"label"=>"prop_postitid"), # N_("prop_postitid")
			       "forumid"=>array("type"=>"docid","displayable"=>false,"sortable"=>false,"filterable"=>false,"label"=>"forum_id"), # N_("forum_id")
			       "cvid"=>array("type"=>"integer","displayable"=>false,"sortable"=>false,"filterable"=>false,"label"=>"prop_cvid"), # N_("prop_cvid")
			       "name"=>array("type"=>"text","displayable"=>true,"sortable"=>true,"filterable"=>true,"label"=>"prop_name"), # N_("prop_name")
			       "dprofid"=>array("type"=>"docid","displayable"=>false,"sortable"=>false,"filterable"=>false,"label"=>"prop_dprofid"), # N_("prop_dprofid")
			       "atags"=>array("type"=>"text","displayable"=>false,"sortable"=>false,"filterable"=>false,"label"=>"prop_atags"), # N_("prop_atags")
			       "prelid"=>array("type"=>"docid","displayable"=>false,"sortable"=>false,"filterable"=>false,"label"=>"prop_prelid"), # N_("prop_prelid")
			       "confidential"=>array("type"=>"integer","displayable"=>false,"sortable"=>true,"filterable"=>true,"label"=>"prop_confidential"), # N_("prop_confidential")
  			       "svalues"=>array("type"=>"fulltext","displayable"=>false,"sortable"=>false,"filterable"=>true,"label"=>"prop_svalues"), # N_("prop_svalues")
			       "ldapdn"=>array("type"=>"text","displayable"=>false,"sortable"=>false,"filterable"=>false,"label"=>"prop_ldapdn")); # N_("prop_ldapdn");
  /**
   * identificator of the document
   * @public int
   */
  public $id;
  /**
   * user identificator for the creator
   * @public int
   */
  public $owner;
  /**
   * the title of the document
   * @public string
   */
  public $title;
  /**
   * number of the revision. First is zero
   * @public int
   */
  public $revision;
  /**
   * tag for version
   * @public string
   */
  public $version;
  /**
   * identificator of the first revision document
   * @public int
   */
  public $initid;
  /**
   * identificator of the family document
   * @public int
   */
  public $fromid;
  /**
   * the type of document
   *
   * F : normal document (default)
   * C : family document
   * D : folder document
   * P : profil document
   * S : search document
   * T : temporary document
   * W : workflow document
   * Z : zombie document
   *
   * @public char
   */
  public $doctype;
  /**
   * user identificator for the locker
   * @public int
   */
  public $locked;
  /**
   * filename or vault id for the icon
   * @public string
   */
  public $icon;
  /**
   * set to 'Y' if the document has been modify until last revision
   * @public char
   */
  public $lmodify;
  /**
   * identificator of the profil document
   * @public int
   */
  public $profid;
  /**
   * to precise a special use of the document
   * @public char
   */
  public $usefor;
  /**
   * date of the last modification (the revision date for fixed document)
   * @public int
   */
  public $revdate;
  /**
   * date of creation
   * @public date
   */
  public $cdate;
  /**
   * date of latest access
   * @public date
   */
  public $adate;
  /**
   * date of the last modification (the revision date for fixed docuemnt)
   * @public int
   */
  public $comment;
  /**
   * class name in case of special family (only set in family document)
   * @public string
   */
  public $classname;
  /**
   * state of the document if it is associated with a workflow
   * @public string
   */
  public $state;
  /**
   * identificator of the workflow document
   * 
   * if 0 then no workflow
   * @public int
   */
  public $wid;
  /**
   * identificator of the control view document
   * 
   * if 0 then no special control view
   * @public int
   */
  public $cvid;
  /**
   * string identificator of the document
   * 
   * @public string
   */
  public $name;
  /**
   * identificator of the mask document
   * 
   * if 0 then no mask
   * @public int
   */
  public $mid=0;
  /**
   * identificator of dynamic profil
   * 
   * if 0 then no dynamic profil
   * @public int
   */
  public $dprofid=0;
  /**
   * primary relation id
   * 
   * generally towards a folder
   * @public int
   */
  public $prelid=0;
 
  /**
   * applications tag 
   * use by specifics applications to search documents by these tags
   * 
   * @public string
   */
  public $atag;
  /**
   * confidential level
   * if not 0 this document is confidential, only user with the permission 'confidential' can read this
   * 
   * @public int
   */
  public $confidential;
  /**
   * Distinguish Name for LDAP use
   * 
   * @public text
   */
  public $ldapdn; 
  /**
   * Allocate user id
   * 
   * @public int
   */
  public $allocated;
  /**
   * Archive document id
   * 
   * @public int
   */
  public $archiveid;

  /**
   * identification of special views
   * 
   * @public array
   */
  public $cviews=array("FDL:VIEWBODYCARD",
		       "FDL:VIEWABSTRACTCARD",
		       "FDL:VIEWTHUMBCARD");
  public $eviews=array("FDL:EDITBODYCARD");


  public static $sqlindex=array("doc_initid"=>array("unique"=>false,
						    "on"=>"initid"),
				"doc_title"=>array("unique"=>false,
						   "on"=>"title"),
				"doc_name"=>array("unique"=>true,
						  "on"=>"name,revision,doctype"),
				"doc_full"=>array("unique"=>false,
						  "using"=>"@FDL_FULLIDX",
						  "on"=>"fulltext"),
				"doc_profid"=>array("unique"=>false,
						    "on"=>"profid"));
  public $id_fields = array ("id");

  public $dbtable = "doc";

  public $order_by="title, revision desc";

  public $fulltextfields = array ("title");
  private $mvalues = array();
  /**
   * number of disabledEditControl
   */
  private $withoutControlN=0; 
  private $withoutControl=false; 
  private $constraintbroken=false; // true if one constraint is not verified
  /**
   * default family id for the profil access
   * @public int
   */
  public $defProfFamId=FAM_ACCESSDOC;
  public $sqlcreate = "
create table doc ( id int not null,
                   primary key (id),
                   owner int,
                   title varchar(256),
                   revision int DEFAULT 0,
                   initid int,
                   fromid int,
                   doctype char DEFAULT 'F',
                   locked int DEFAULT 0,
                   archiveid int DEFAULT 0,
                   allocated int DEFAULT 0,
                   icon varchar(256),
                   lmodify char DEFAULT 'N',
                   profid int DEFAULT 0,
                   usefor char  DEFAULT 'N',
                   revdate int, 
                   version text,
                   cdate timestamp,  
                   adate timestamp,  
                   comment text,
                   classname varchar(64),
                   state varchar(64),
                   wid int DEFAULT 0,  
                   values text DEFAULT '',
                   attrids text DEFAULT '',
                   fulltext tsvector,  
                   postitid text,
                   forumid int,
                   cvid int,
                   name text,
                   dprofid int DEFAULT 0,
                   prelid int DEFAULT 0,
                   atags text,
                   confidential int DEFAULT 0,
                   ldapdn text,
                   svalues text DEFAULT ''
                   );
create table docfrom ( id int not null,
                   primary key (id),
                   fromid int);
create table docname ( name text not null,
                   primary key (name),
                   id int,
                   fromid int);
create sequence seq_id_doc start 1000;
create sequence seq_id_tdoc start 1000000000;
create index i_docname on doc(name);
create unique index i_docir on doc(initid, revision);";

  // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  
  public $obj_acl = array (); // set by childs classes

  // --------------------------------------------------------------------

  /**
   * default view to view card
   * @public string
   */
  public $defaultview= "FDL:VIEWBODYCARD";
  /**
   * default view to edit card
   * @public string
   */
  public $defaultedit = "FDL:EDITBODYCARD";
  /**
   * default view for abstract card
   * @public string 
   */
  public $defaultabstract = "FDL:VIEWABSTRACTCARD";
  /**
   * for email : the same as $defaultview by default
   * @public string 
   */
  public $defaultmview = ""; 
 

  /**
   * use when family wants to define a special context menu
   * @public array
   */
  public $specialmenu=array();

 

  public $defDoctype='F';

  /**
   * to indicate values modification
   * @public bool 
   * @access private
   */
  private $hasChanged=false; 

  public $isCacheble= false;

  public $paramRefresh=array();

  /**
   * optimize: compute mask in needed only
   * @public bool 
   * @access private
   */
  private $_maskApplied=false; // optimize: compute mask if needed only
 
 



  /**
   * Increment sequence of family and call to {@see PostCreated()}
   * 
   * 
   * @return void
   */
  final public function PostInsert()  {
    // controlled will be set explicitly
    //$this->SetControl();
    if (($this->revision == 0) && ($this->doctype != "T")) {
      // increment family sequence
      $this->nextSequence();
      $this->Addcomment(_("document creation"),HISTO_INFO,"CREATE");
      if ($this->wdoc) {
	$this->wdoc->workflowSendMailTemplate($this->state,_("creation"));
	$this->wdoc->workflowAttachTimer($this->state);
	$this->wdoc->changeAllocateUser($this->state);
      }
      $this->addLog("create",array("id"=>$this->id,"title"=>$this->title,"fromid"=>$this->fromid,
				   "fromname"=>$this->fromname));
    }
    $this->Select($this->id);
    // set creation date
    $this->cdate=$this->getTimeDate(0,true);
    $this->adate=$this->cdate;
    $date = gettimeofday();
    $this->revdate = $date['sec'];
    $this->modify(true,array("cdate","adate","revdate"),true); // to force also execute sql trigger
    if ($this->doctype != "T") {
      $err=$this->PostCreated(); 
      if ($err!="") AddWarningMsg($err);
      $this->sendTextToEngine();
      if ($this->dprofid >0) {
	$this->setProfil($this->dprofid);// recompute profil if needed
	$this->modify(true,array("profid"),true); 
      }
      $this->UpdateVaultIndex();
      $this->updateRelations();
    }
    $this->hasChanged=false;
     
      

    global $gdocs; // set to cache
    if (count($gdocs) < MAXGDOCS && ($this->doctype != 'C')) {
        $gdocs[$this->id]=&$this;
    }
  }  

  function setChanged() {
    $this->hasChanged=true;
  } 
  function isChanged() {
    return ($this->hasChanged===true);
  }
  
  
  /**
   * set default values and creation date
   * the access control is provided by {@see createDoc()} function.
   * call {@see Doc::PreCreated()} method before execution
   * 
   * @return string error message, if no error empty string
   */
  final public function PreInsert() {

    $err=$this->PreCreated(); 
    if ($err != "") return $err;
      
    // compute new id
    if ($this->id == "") {
      if ($this->doctype=='T') $res = pg_exec($this->init_dbid(), "select nextval ('seq_id_tdoc')");
      else $res = pg_exec($this->init_dbid(), "select nextval ('seq_id_doc')");
      $arr = pg_fetch_array ($res, 0);
      $this->id = $arr[0];

    }
      

    // set default values

    if ($this->initid == "") $this->initid=$this->id;
    $this->RefreshTitle();
    if (chop($this->title) == "") {
      $fdoc=$this->getFamDoc();
      $this->title =sprintf(_("untitle %s %d"),$fdoc->title,$this->initid);
    }
    if ($this->doctype == "")  $this->doctype = $this->defDoctype;
    if ($this->revision == "") $this->revision = "0";

    if ($this->profid == "") $this->profid = "0";
    if ($this->usefor == "") $this->usefor = "N";

    if ($this->lmodify == "") $this->lmodify = "N";
    if ($this->locked == "") $this->locked = "0";
    if ($this->owner == "") $this->owner = $this->userid;

    //      if ($this->state == "") $this->state=$this->firstState;
    $this->version=$this->getVersion();

    if ($this->wid > 0) {
      $this->wdoc = new_Doc($this->dbaccess,$this->wid);
      if ($this->wdoc->isAlive()) {
	if ($this->wdoc->doctype != 'W') $err=sprintf(_("creation : document %s is not a workflow"),$this->wid);
	else $this->wdoc->Set($this); // set first state
      } else $err=sprintf(_("creation : workflow %s not exists"),$this->wid);
    }
    return $err;
  }   


  /** 
   * Verify control edit
   * 
   * if {@link disableEditControl()} is call before control permission is desactivated
   * if attribute values are changed the modification date is updated
   * @return string error message, if no error empty string
   */
  function PreUpdate() {
    if ($this->id == "") return _("cannot update no initialized document");
    if (! $this->withoutControl) {
      $err = $this->Control("edit");
      if ($err != "") return ($err); 
    }
    if ($this->locked == -1) $this->lmodify='N';
    if ($this->isFixed()) return _("cannot update fixed document");
    if ($this->constraintbroken) return (sprintf(_("constraint broken %s"),$this->constraintbroken));
    $this->RefreshTitle();
    if ($this->hasChanged) {
      if (chop($this->title) == "") $this->title =_("untitle document");
      // set modification date
      $date = gettimeofday();
      $this->revdate = $date['sec'];
      $this->version=$this->getVersion();
      $this->lmodify='Y';
      //	$this->postModify(); // in modcard function
    }
      
  }

  /**
   * optimize for speed : memorize object for future use
   * @global array optimize for speed :: reference is not a pointer !!
   */
  function PostUpdate() {
    global $gdocs;// optimize for speed :: reference is not a pointer !!
    //unset($gdocs[$this->id]); // clear cache
    if (isset($gdocs[$this->id]))  {
      if ($this->nocache) unset($gdocs[$this->id]); // clear cache
      else $gdocs[$this->id]=$this; // update caches
    }

    if ($this->hasChanged) {
      $this->computeDProfil();
      $this->regenerateTemplates();
      $this->UpdateVaultIndex();
      $this->updateRelations();
      if ($this->getATag("DYNTIMER")) $this->resetDynamicTimers();
      $this->addLog("changed",array_keys($this->getOldValues()));
    }
    $this->sendTextToEngine();
    $this->hasChanged=false;
  }
  
  /**
   * Regenerate the template referenced by an attribute
   * 
   * @param string $aid the name of the attribute holding the template
   * @param string $index the value for $index row (default value -1 means all values)
   * @return string error message, if no error empty string
   */
  function regenerateTemplate($aid, $index=-1) {
  	$layout = 'THIS:'.$aid;
  	if($index>-1) {
  		$layout.='['.$index.']';
  	}
  	$orifile=$this->getZoneFile($layout);
  	if( ! $orifile ) {
  		$err = sprintf(_("Dynamic template %s not found "), $orifile);
  		return $err;
  	}
  	if( ! file_exists($orifile) ) {
  		$err = sprintf(_("Dynamic template %s not found "), $orifile);
  		addWarningMsg($err);
  		return $err;
  	}
  	if( getFileExtension($orifile) != 'odt' ) {
  		$err = sprintf(_("Dynamic template %s not an odt file "), $orifile);
  		addWarningMsg($err);
  		return $err;
  	}
  	$outfile = $this->viewDoc($layout.':B', 'ooo');
  	if( ! file_exists($outfile) ) {
  		$err = sprintf(_("viewDoc did not returned a valid file"));
  		addWarningMsg($err);
  		return $err;
  	}
  	$fh = fopen($outfile, 'rb');
  	if( $fh === false ) {
  		$err = sprintf(_("Error opening %s file '%s'", 'outfile', $outfile));
  		addWarningMsg($err);
  		return $err;
  	}
  	$err = $this->saveFile($aid, $fh, '', $index);
  	if( $err != '' ) {
  		addWarningMsg($err);
  		return $err;
  	}
  	fclose($fh);
  	$this->AddComment(sprintf(_('regeneration of file template %s'), $aid));
  	return '';
  }
  
  /**
   * Regenerate all templates referenced by the document attributes
   * 
   * @return string error message, if no error empty string
   */
  final function regenerateTemplates() {
    $fa = $this->GetFileAttributes();
    $errorList = array();
    foreach ($fa as $aid=>$oattr) {
      $opt = $oattr->getOption("template");
      if ($opt == "dynamic" || $opt == "form") {
	if ($oattr->inArray()) {
	  $ta=$this->getTValue($aid);
	  foreach($ta as $k=>$v) {
	    $err = $this->regenerateTemplate($aid,$k);
	    if( $err != '' ) {
			array_push($errorList, $err);
	    }
	  }
	} else {
	  $err = $this->regenerateTemplate($aid);
	  if( $err != '' ) {
		array_push($errorList, $err);
	  }
	}
      }
    }
    if( count($errorList) > 0 ) {
    	return join("\n", $errorList);
    }
    return '';
  }

  /**
   * Set relation doc id use on docrel table
   */
  function updateRelations() {
    //    return; // for the moment
    include_once("FDL/Class.DocRel.php");
    $or=new DocRel($this->dbaccess);
    //    $or->resetRelations('',$this->initid); // not necessary now
    $or->initRelations($this);    
  }

  /**
   * get current sequence number :: number of doc for this family
   * @return int
   */
  function getCurSequence() {
    if ($this->doctype=='C') return 0;
    if ($this->fromid == "") return 0;
    // cannot use currval if nextval is not use before
    $res = pg_exec($this->init_dbid(), "select nextval ('seq_doc".$this->fromid."')");
    $arr = pg_fetch_array ($res, 0);
    $cur = intval($arr[0]) - 1;
    $res = pg_exec($this->init_dbid(), "select setval ('seq_doc".$this->fromid."',$cur)");
    
    return $cur;
  }
  // set next sequence family
  function nextSequence($fromid=0) {
    if ($fromid==0) $fromid=$this->fromid;
    if ($this->fromid==0)    return 0;
    if ($this->doctype=='C') return 0;
    // cannot use currval if nextval is not use before
    $res = pg_exec($this->init_dbid(), "select nextval ('seq_doc".$fromid."')");   
    $arr = pg_fetch_array ($res, 0);
    $cur = intval($arr[0]) ;
    return $cur;
  }

  /**
   * modify without edit control
   */
  final public function disableEditControl() {
    $this->withoutControlN++;
    $this->withoutControl=true;
  }
  /**
   * default edit control enable
   */
  final public function enableEditControl() {
    $this->withoutControlN--;
    if ($this->withoutControlN <= 0) {
      $this->withoutControlN=0;
      $this->withoutControl=false;
    }
  }
  /**
   * to know if the document can be revised
   *
   * @return bool true is revisable
   */
  public function isRevisable() {
    if (($this->doctype == 'F') && ($this->usefor != 'P')) {
      $fdoc = $this->getFamDoc();
      if ($fdoc->schar != "S") return true;
    }
    return false;
  }
 
  /**
   * copy values from anothers document (must be same family or descendant)
   *
   * @param Doc &$from document source for the transfert
   */
  final public function transfertValuesFrom(&$from) {
    
    $values = $from->getValues();
    

    foreach($values as $k=>$v) {
      $this->setValue($k,$v);
    }
  }
  /**
   * convert to another family
   * loose all revisions
   * @param int $fromid family identificator where the document will be converted
   * @param array $prevalues values which will be added before conversion
   * @return doc the document converted (don't reuse $this) if error return string message
   */
  final public function convert($fromid, $prevalues=array()) {
    
    $cdoc = createDoc($this->dbaccess, $fromid);
    if (! $cdoc) return false;
    if ($this->fromid  == $cdoc->fromid) return false; // no convert if not needed
    if ($this->locked == -1) return false; // not revised document
    if ($cdoc->fromid == 0) return false;
    $f1doc=$this->getFamDoc();
    $f1from=$f1doc->title."[".$f1doc->id."]";
    $f2doc=$cdoc->getFamDoc();
    $f2from=$f2doc->title."[".$f2doc->id."]";
    
    $cdoc->id = $this->id;
    $cdoc->initid=$this->id;
    $cdoc->revision=0;
    $cdoc->cdate=$this->cdate;
    $cdoc->revdate=$this->revdate;
    $cdoc->adate=$this->adate;
    $cdoc->locked=$this->locked;
    $cdoc->profid=$this->profid;
    $cdoc->dprofid=$this->dprofid;
    $cdoc->prelid=$this->prelid;
    
    $values = $this->getValues();

    $this->exec_query("begin;"); // begin transaction in case of fail add
    $err=$this->delete(true,false,true); // delete before add to avoid double id (it is not authorized)
    if ($err != "") return $err;

    foreach($prevalues as $k=>$v) {
      $cdoc->setValue($k,$v);
    }
    $err=$cdoc->Add(true,true);
    if ($err != "") {
       $this->exec_query("rollback;");
        return $err;
    }

    foreach($values as $k=>$v) {
      $cdoc->setValue($k,$v);
    }

    $err=$cdoc->Modify();
    if ($err=="") {     
      if ($this->revision > 0) {
	$this->exec_query(sprintf("update fld set childid=%d where childid=%d",$cdoc->id,$this->initid));
      }
    }
    $this->exec_query(sprintf("update fld set fromid=%d where childid=%d",$cdoc->fromid,$this->initid));
    
    $cdoc->AddComment(sprintf(_("convertion from %s to %s family"),$f1from,$f2from));
		
    $this->exec_query("commit;");
    global $gdocs; //reset cache if needed
    if (isset($gdocs[$this->id])) {
        $gdocs[$this->id]=&$cdoc;
    }
    
    return $cdoc;
    
  }

  /**
   * test if the document can be revised now
   * it must be locked by the current user
   * @return string empty means user can update else message of the raison
   */
  final public function CanUpdateDoc() {

    if ($this->locked == -1) {
      $err = sprintf(_("cannot update file %s (rev %d) : fixed. Get the latest version"), $this->title,$this->revision);      
      return $err;
    }

    if ($this->userid == 1) return "";// admin can do anything but not modify fixed doc
    $err="";
  
    if ($this->locked == 0) {     
      $err = sprintf(_("the file %s (rev %d) must be locked before"), $this->title,$this->revision);      
    } else {
      if (abs($this->locked) != $this->userid) {
	  
	$user = new User("", abs($this->locked));
	if ($this->locked < -1) $err = sprintf(_("Document %s is in edition by %s."), $this->getTitle(),$user->firstname." ".$user->lastname); 
	else $err = sprintf(_("you are not allowed to update the file %s (rev %d) is locked by %s."), $this->title,$this->revision,$user->firstname." ".$user->lastname); 
	  
      } else $err = $this-> Control( "edit");
    }
    
    return($err);
  }  

  /**
   * test if the document can be edit by the current user
   * the diffence between ::canUpdateDoc is that document is not need to be locked
   * @return string empty means user can update else message of the raison
   */
  public function CanEdit() {
    if ($this->locked == -1) {
      $err = sprintf(_("cannot update file %s (rev %d) : fixed. Get the latest version"), $this->title,$this->revision);  
      return($err);     
    }
    if ($this->userid == 1) return "";// admin can do anything but not modify fixed doc
    $err="";      
    if ($this->withoutControl) return ""; // no more test if disableEditControl activated
    if  (($this->locked != 0) && (abs($this->locked) != $this->userid)) {	  
      $user = new User("", abs($this->locked));
      if ($this->locked < -1) $err = sprintf(_("Document %s is in edition by %s."), $this->getTitle(),$user->firstname." ".$user->lastname); 
      else $err = sprintf(_("you are not allowed to update the file %s (rev %d) is locked by %s."), $this->getTitle(),$this->revision,$user->firstname." ".$user->lastname); 
	  
    } else {
      $err = $this->Control("edit");
    }        
    return($err);
  }

  /**
   * test if the document can be locked
   * it is not locked before, and the current user can edit document
   * @return string empty means user can update else message of the raison
   */
  final public function CanLockFile() {
    $err="";
    
    if ($this->locked == -1) {
      
      $err = sprintf(_("cannot lock document %s [%d] (rev %d) : fixed. Get the latest version"), 
		     $this->title,$this->id,$this->revision);
    }  else {
      if ($this->userid == 1) return ""; // admin can do anything
      if ($this->locked == 0) $err = $this-> Control( "edit");
      // test if is not already locked
      else {
	if ( abs($this->locked) != $this->userid) {
	  $user = new User("", abs($this->locked));
	  if ($this->locked < -1) $err = sprintf(_("Document %s is in edition by %s."), $this->getTitle(),$user->firstname." ".$user->lastname); 
	  else $err = sprintf(_("cannot lock file %s [%d] : already locked by %s."),
			      $this->title,$this->id,$user->firstname." ".$user->lastname);
	}   else  {      
	  $err = $this-> Control( "edit");
	}
      }
    }
    

    return($err);  
  }
  /**
   * @return boolean true if can lock file
   */
  public function canLock() {
    return ($this->CanLockFile()=="");
  }
  /**
   * @return boolean true if can lock file
   */
  public function canUnLock() {
    return ($this->CanUnLockFile()=="");
  }
  /** 
   * test if the document can be unlocked
   * @see CanLockFile()
   * @see CanUpdateDoc()
   * @return string empty means user can update else message of the raison
   */
  final public function CanUnLockFile() {
    if ($this->userid == 1) return "";// admin can do anything
    $err="";
    if ($this->locked != 0) { // if is already unlocked
      if ($this->profid > 0) 	$err = $this->Control("unlock"); // first control unlock privilege
      else $err=_("cannot unlock"); // not control unlock if the document is not controlled
    }
    if ($err != "") $err=$this->CanUpdateDoc();
    else {      
      $err = $this->Control("edit");
      if ($err != "") {
	if ($this->profid > 0) {
	  $err = $this->Control("unlock");
	}  
      }
    }
    return($err);
  
  }

  /** 
   * test if the document is locked
   * @see CanLockFile()
   * @param bool $my if true test if it is lock of current user
   * 
   * @return bool true if locked. If $my return true if it is locked by another user
   */
  final public function isLocked($my=false) {
    if ($my) {      
      if (($this->user->id == 1) || (abs($this->locked) == $this->userid)) return false;
    }
    return (($this->locked > 0) || ($this->locked < -1));
  }

  /** 
   * test if the document is confidential
   * 
   * @return bool true if confidential and current user is not authorized
   */
  final public function isConfidential() {
    return (($this->confidential > 0) && ($this->controlId($this->profid,'confidential')!=""));
  }
  /** 
   * return the family document where the document comes from
   * 
   * @return DocFam
   */
  final public function getFamDoc() {
    if (! isset($this->famdoc)||($this->famdoc->id != $this->fromid)) $this->famdoc= new_Doc($this->dbaccess, $this->fromid);
    return $this->famdoc;
  }

  /**
   * search the first document from its title
   * @param string $title the title to search (must be exactly the same title)
   * @return int document identificator
   */
  function GetFreedomFromTitle($title) {

    $query = new QueryDb($this->dbaccess,"Doc");
    $query->basic_elem->sup_where=array ("title='".$title."'");


    $table1 = $query->Query();
    $id=0;
    if ($query->nb > 0)
      {
	$id = $table1[0]->id;

	unset ($table1);
      }
    return $id;
  }


  /**
   * return family parameter
   * 
   * @param string $idp parameter identificator
   * @param string $def default value if parameter not found or if it is null
   * @return string parameter value
   */
  public function getParamValue($idp, $def="") {
    if ($this->doctype=='C') return $this->getParamValue($idp,$def);
    if (! $this->fromid) return false;
    $fdoc=$this->getFamDoc();
    if (! $fdoc->isAlive()) return false;
    return $fdoc->getParamValue($idp,$def);
    
  }
  


  /**
   * return similar documents
   * 
   * @param string $key1 first attribute id to perform search 
   * @param string $key2 second attribute id to perform search 
   * @return string parameter value
   */
  final public function GetDocWithSameTitle($key1="title",$key2="") {
    include_once("FDL/Lib.Dir.php");
    // --------------------------------------------------------------------

    $filter[]="doctype!='T'";
    if ($this->initid>0)$filter[]="initid !='".$this->initid."'";  // not itself
    $filter[]="$key1='".addslashes($this->getValue($key1))."'";
    if ($key2 != "") $filter[]="$key2='".addslashes($this->getValue($key2))."'";
    $tpers = getChildDoc($this->dbaccess, 0,0,"ALL", $filter,1,"LIST",$this->fromid);
  
    return $tpers;

    
  }

   
  /** 
   * return the latest revision id with the indicated state 
   * For the user the document is in the trash
   * @param string $state wanted state
   * @param bool $fixed set to true if not search in current state
   * @return int document id (0 if no found)
   */
  final public function getRevisionState($state,$fixed=false) {    
    $ldoc = $this->GetRevisions("TABLE");
    $vdocid=0;

    foreach($ldoc as $k=>$v) {
      if ($v["state"]==$state) {
	if ((($v["locked"]==-1) && $fixed) || (! $fixed)) {
	  $vdocid = $v["id"];
	  break;
	}
      }	  	  
    }
    return $vdocid;
  }    

  // --------------------------------------------------------------------
  final public function DeleteTemporary() {
    // --------------------------------------------------------------------

    $result = pg_exec($this->init_dbid(),"delete from doc where doctype='T'");

    
  }
  
  /** 
   * Control if the doc can be deleted
   * @access private
   * @return string error message, if no error empty string
   * @see Doc::Delete()
   */
  function PreDocDelete()    
  {      
    if ($this->doctype == 'Z') return _("already deleted");
    if ($this->isLocked(true)) return _("locked");
    $err = $this->Control("delete");
                        
    return $err;      
  }

  /** 
   * Really delete document from database
   * @return string error message, if no error empty string
   */
  final public function ReallyDelete($nopost) {
    $err= DbObj::delete($nopost);
    if ($err=="") {      
      $dvi = new DocVaultIndex($this->dbaccess);
      $err = $dvi->DeleteDoc($this->id);
      if ($this->name != '') {
        $this->exec_query(sprintf("delete from docname where name='%s'",pg_escape_string($this->name)));
      }
      $this->exec_query(sprintf("delete from docfrom where id='%s'",pg_escape_string($this->id)));
    }
    return $err;
  }

  /** 
   * Set the document to zombie state
   * For the user the document is in the trash
   * @param bool $really if true call {@link ReallyDelete} really delete from database
   * @param bool $control if false don't control 'delete' acl
   * @param bool $nopost if true don't call {@link PostDelete} and {@link PreDelete}
   * @return void
   */
  final public function Delete($really=false,$control=true,$nopost=false) {

    if ($control) {
      // Control if the doc can be deleted
      $msg = $this->PreDocDelete();
      if ($msg!='') return $msg;
    }

    if (abs(intval($this->forumid))>0) {
      $df = new_Doc($this->dbaccess, abs(intval($this->forumid)));
      $df->delete($really, $control, $nopost);
    }
    
    if ($really) {
      if ($this->id != "") {
	// delete all revision also
	$this->addLog('delete',array("really"=>$really));
	$rev=$this->GetRevisions();
	foreach($rev as $k=>$v) {
	  $v->ReallyDelete($nopost);
	}
      }
    } else {
      // Control if the doc can be deleted
      if ($this->doctype == 'Z') $msg= _("already deleted");       
      if ($msg!='') return $msg;

      if (!$nopost) $msg=$this->PreDelete();
      if ($msg!='') return $msg;

      if ($this->doctype != 'Z') {

	if ($this->name != "") $this->exec_query(sprintf("delete from doc%d where name='%s' and doctype='Z'",
							 $this->fromid,pg_escape_string($this->name))); // need to not have twice document with same name
	$this->doctype='Z'; // Zombie Doc
	$this->locked= -1; 
        $this->lmodify= 'D'; // indicate last delete revision 
	$date = gettimeofday();
	$this->revdate = $date['sec']; // Delete date

	global $action;
	global $_SERVER;
	$this->AddComment(sprintf(_("delete by action %s/%s from %s"),
				  $action->parent->name,$action->name,
				  $_SERVER["REMOTE_ADDR"]),HISTO_NOTICE);
	$this->addComment(_("document deleted"),HISTO_MESSAGE,"DELETE");
	$this->addLog('delete',array("really"=>$really));

	$this->modify(true,array("doctype","revdate","locked","owner","lmodify"),true);
	if (!$nopost) $msg=$this->PostDelete();

	// delete all revision also
	$rev=$this->GetRevisions();
	foreach($rev as $k=>$v) {	
	  if ($v->doctype != 'Z') {
	    $v->doctype='Z'; // Zombie Doc
	    if ($v->locked == -1)  $v->modify(true,array("doctype"),true);
	  }	    
	}
      }
      return $msg;
    }
  }



  /** 
   * To restore a document which is in the trash
   * @return string error message (empty message if no errors);
   */
  final public function revive() {
    $err="";
    if (($this->owner == $this->userid) || ($this->userid==1)) {
      if (! $this->isAlive()) {
	$err=simpleQuery($this->dbaccess,
			 sprintf("SELECT id from only doc%d where initid = %d order by id desc limit 1",$this->fromid,$this->initid),$latestId,true, true);
	if ($err=="") {
	  if (! $latestId) $err=sprintf(_("document %s [%d] is strange"),$this->title,$this->id);
	  else {
	    $this->doctype=$this->defDoctype;
	    $this->locked=0;
	    $this->id=$latestId;
	    $this->lmodify= 'Y'; // indicate last restoration
	    $this->modify(true,array("doctype","locked","lmodify"),true);
	    $this->AddComment(_("revival document"),HISTO_MESSAGE,"REVIVE");

	    $this->addLog('revive');
	    $rev=$this->getRevisions();
	    foreach($rev as $k=>$v) {
	      if ($v->doctype=='Z') {
		$v->doctype=$v->defDoctype;
		$err.=$v->modify(true,array("doctype"),true);
	      }
	    }
	    if ($this->name) {
	      // force reset logival name if not set
	      $name=$this->name;
	      $this->name='';
	      $this->modify(true,array("name"),true);
	      $this->setLogicalIdentificator($name);
	    }
	  }
	}
      } else return sprintf(_("document %s [%d] is not in the trash"),$this->title,$this->id);
    } else return _("Only owner of document can restore it");
    return $err;
  }

  /** 
   * Adaptation of affect Method from DbObj because of inheritance table
   * this function is call from QueryDb and all fields can not be instanciate
   * @param array $array the data array
   * @param bool $more add values from values attributes needed only if cast document
   * @return void
   */
  final public function Affect($array,$more=false) { 
    if (is_array($array)) {
      if ($more)  $this->ResetMoreValues();   
      unset($this->uperm); // force recompute privileges
      foreach($array as $k=>$v) {
	if (!is_integer($k)) {
	  $this->$k = $v;
	}
      }
      $this->Complete();
      if ($more)  $this->GetMoreValues();
      
      $this->isset = true;
    }
  }
  /** 
   * Set to default values before add new doc
   * @return void
   */
  function Init() {         
    $this->isset = false;
    $this->id="";
    $this->initid="";
    $this->comment="";
    $nattr = $this->GetNormalAttributes();
    foreach($nattr as $k=>$v) {
      if (isset($this->$k) && ($this->$k != "")) $this->$k ="";
    }	
    unset($this->lvalues);
    
  }

  // --------------------------------------------------------------------
  function Description() {
    // -------------------------------------------------------------------- 
    
    return $this->title." - ".$this->revision;
  }


 
  // --------------------------------------------------------------------
  final public function GetFathersDoc() {
    // -------------------------------------------------------------------- 
    // Return array of father doc id : class document 
    if (! isset($this->fathers)) {

      $this->fathers=array();
      if ($this->fromid > 0) {
	$fdoc= $this->getFamDoc();
	$this->fathers=$fdoc->GetFathersDoc();
	array_push($this->fathers,$this->fromid);
      }
    }
    return $this->fathers;
  }
  
  /**
   * Return array of fathers doc id : class document 
   * @return array
   */
  final public function GetFromDoc() {   
    return $this->attributes->fromids;
  }

  /**
   * Return array of child doc id : class document 
   * @return array
   */
  final public function GetChildFam($id=-1, $controlcreate=false) {        
    if ($id == 0) return array();
    if (($id!=-1) || (! isset($this->childs))) {
      include_once("FDL/Class.SearchDoc.php");
      if ($id==-1) 	$id= $this->id;	
      if (! isset($this->childs)) $this->childs=array();
   
      $s=new SearchDoc($this->dbaccess,-1);
      $s->addFilter("fromid = ".$id);
      $s->noViewControl();
      $table1=$s->search();
      if ($table1) {
	foreach($table1 as $k=>$v) {	 
	  if ((! $controlcreate) || controlTdoc($v,"icreate")) {
	    $this->childs[$v["id"]]=$v;
	  }
	  $this->GetChildFam($v["id"],$controlcreate);
	  
	}
      }
    }
    return $this->childs;
  }

  /**
   * return all revision documents
   */
  final public function GetRevisions($type="LIST",$limit=200) {
    // Return the document revision 
    $query = new QueryDb($this->dbaccess, strtolower(get_class($this)));

      
    //$query->AddQuery("revision <= ".$this->revision);
    $query->AddQuery("initid = ".$this->initid);
    $query->order_by="revision DESC LIMIT $limit";
      
    $rev= $query->Query(0,0,$type);
    if ($query->nb == 0) return array();
    return $rev;
  }

  /** get Latest Id of document
   * 
   * @param bool $fixed if true latest fixed revision
   * @param bool $forcequery if true force recompute of id (use it in case of modification by another program)
   * @return int identificator of latest revision
   */
  final public function latestId($fixed=false,$forcequery=false) {
    if ($this->id == "") return false;
    if (!$forcequery) {
      if (($this->locked != -1) && (!$fixed)) return $this->id;
      if ($fixed && ($this->lmodify == "L")) return $this->id;
    }
    $query = new QueryDb($this->dbaccess, strtolower(get_class($this)));
    $query->AddQuery("initid = ".$this->initid);
    if ($fixed) $query->AddQuery("lmodify = 'L'");
    elseif ($this->doctype!='Z') $query->AddQuery("locked != -1");
    else {
      $query->order_by="id desc";
    }
    $rev= $query->Query(0,2,"TABLE");
    
    if ($this->doctype!='Z') {
    	if (count($rev)>1) addWarningMsg(sprintf("document %d : multiple alive revision",$this->initid));
    }
    return $rev[0]["id"];
  }

  /**
   * get version of document 
   * must be redefined by child document classes if needed
   * @return string
   */
  final public function getVersion() { 
    $tversion=array();
    if (isset($this->attributes->attr)) {
      foreach($this->attributes->attr as $k=>$v) {
	if ((get_class($v) == "NormalAttribute") && ($v->getOption("version") == "yes")) {
	  $tversion[]=$this->getValue($v->id);
	}
      }
    }
    if (count($tversion) > 0) $version=implode(" ",$tversion);
    else $version=$this->version;
    return $version;
  }


  /**
   * return the string label text for a id
   * @return string
   */
  final public function getLabel($idAttr)  {
    if (isset($this->attributes->attr[$idAttr])) return $this->attributes->attr[$idAttr]->getLabel();
    return _("unknow attribute");
  }
  
  /** 
   * return the property object like id, initid, revision, ...
   * @param string $idAttr attribute identificator
   * @return string false if not an property
   */
  final public function getProperty($prop)   {
    $prop=trim(strtolower($prop));
    if (! in_array($prop,$this->fields)) return false;
    if (isset($this->fields[$prop])) return false; // it's an attribute
    return $this->$prop;	
  }
  

  /**
   * return the attribute object for a id
   * the attribute can be defined in fathers
   * @param string $idAttr attribute identificator
   * @param DocAttribute &$oa object reference use this if want to modify attribute
   * @return DocAttribute
   */
  final public function &getAttribute($idAttr,&$oa=null)   {
    if (!$this->_maskApplied) $this->ApplyMask();
    $idAttr = strtolower($idAttr);
    $oa=$this->attributes->attr[$idAttr];
    if (isset($this->attributes->attr[$idAttr])) return $oa;
		 
    return false;
  }
  /**
   * return all the attributes object 
   * the attribute can be defined in fathers
   * @return array DocAttribute
   */
  final public function GetAttributes()     {     
    if (!$this->_maskApplied) $this->ApplyMask();
    reset($this->attributes->attr);
    return $this->attributes->attr;
  }


  /**
   * retrieve first compatible view from default view control
   * @param bool $edition if true edition view else consultation view
   * @param string $extract [id|mask|all]
   * @return array view definition "cv_idview", "cv_mskid"
   */
  final public function getDefaultView($edition=false,$extract="all") {
    $vid=0;
    if ($this->cvid > 0) {
      // special controlled view
      $cvdoc= new_Doc($this->dbaccess, $this->cvid);
      $cvdoc->set($this);

      $view=$cvdoc->getPrimaryView($edition);
         
      if ($view) {
	switch ($extract) {
	case 'id':
	  return $view["cv_idview"];	
	case 'mask':
	  return $view["cv_mskid"];	
	default:
	  return $view;
	  
	}
      }
    }
    return 0;
  }
  /**
   * set visibility mask
   *
   * @param int $mid mask ident
   */
  final public function setMask($mid) {
    $this->mid=$mid;
    if (isset($this->attributes->attr)) {
      // reinit mask before apply
      foreach($this->attributes->attr as $k=>$v) {
	$this->attributes->attr[$k]->mvisibility=$v->visibility;
      }
    }
    $this->ApplyMask($mid);
  }
  /**
   * apply visibility mask
   *
   * @param int $mid mask ident, if not set it is found from possible workflow
   */
  final public function ApplyMask($mid = 0) {    
    // copy default visibilities
    if (is_object($this->attributes)) {
      foreach($this->attributes->attr as $k=>$v) {
	if ($this->attributes->attr[$k]) $this->attributes->attr[$k]->mvisibility=ComputeVisibility($v->visibility,$v->fieldSet->mvisibility);

      }
    }
    $this->_maskApplied=true;
    if (($this->doctype=='C')||(($this->doctype=='T')&&($mid==0))) return;
    // modify visibilities if needed
    if ((! is_numeric($mid)) && ($mid!="")) $mid=getIdFromName($this->dbaccess,$mid);  
    if ($mid == 0) $mid=$this->mid;
    if ($mid == 0) {
      if (($this->wid > 0) && ($this->wid != $this->id)) {
	// search mask from workflow
	$wdoc=new_Doc($this->dbaccess,$this->wid);
	if ($wdoc->isAlive()) {
	  if ($this->id == 0) {	  
	    $wdoc->set($this);
	  }
	  $mid = $wdoc->getValue($wdoc->attrPrefix."_MSKID".$this->state);
	  if ((! is_numeric($mid)) && ($mid!="")) $mid=getIdFromName($this->dbaccess,$mid);  
	}      
      }	
    }
    if ($mid > 0) { 

      $mdoc = new_Doc($this->dbaccess,$mid );
      if ($mdoc->isAlive()) {
	$tvis = $mdoc->getCVisibilities();
	  
	foreach ($tvis as $k=>$v) {
	  if (isset($this->attributes->attr[$k])) {
	    if ($v != "-") $this->attributes->attr[$k]->mvisibility=$v;	      
	  }
	}
	$tdiff=array_diff(array_keys($this->attributes->attr),array_keys($tvis));
	// recompute loosed attributes
	foreach	($tdiff	as $k)	{
	  $v=$this->attributes->attr[$k];
	  $this->attributes->attr[$k]->mvisibility=ComputeVisibility($v->visibility,$v->fieldSet->mvisibility);
        }

	// modify needed attribute also
	$tneed = $mdoc->getNeedeeds();
	foreach ($tneed as $k=>$v) {
	  if (isset($this->attributes->attr[$k])) {
	    if ($v == "Y") $this->attributes->attr[$k]->needed=true;
	    else if ($v == "N") $this->attributes->attr[$k]->needed=false;
	  }
	}
      }
    }
    uasort($this->attributes->attr,"tordered"); 
  }

  /**
   * return all the attributes except frame & menu & action
   * 
   * @return array DocAttribute
   */
  final public function GetNormalAttributes($onlyopt=false)
  {      
    if (!$this->_maskApplied) $this->ApplyMask();
    if ((isset($this->attributes)) && (method_exists($this->attributes,"GetNormalAttributes")))
      return $this->attributes->GetNormalAttributes($onlyopt);      
    else return array();
  } 


  /**
   * return  frame attributes  
   * 
   * @return array FieldSetAttribute
   */
  final public function GetFieldAttributes() {      
    if (!$this->_maskApplied) $this->ApplyMask();
    $tsa=array();
           
    foreach($this->attributes->attr as $k=>$v) {
      if (get_class($v) == "FieldSetAttribute")  $tsa[$v->id]=$v;
    }
    return $tsa;      
  }



  /**
   * return action attributes  
   * 
   * @return array ActionAttribute
   */
  final public function GetActionAttributes() {      
    if (!$this->_maskApplied) $this->ApplyMask();
    $tsa=array();
    $at= $this->attributes->GetActionAttributes(); 
    foreach($at as $k=>$v) {
      if ($v->mvisibility != 'H') $tsa[$v->id]=$v;	  	
    }
    return $tsa;     
  }
  /**
   * return all the attributes object for abstract
   * the attribute can be defined in fathers
   * @return array DocAttribute
   */
  final public function GetAbstractAttributes()
  {      
    if (!$this->_maskApplied) $this->ApplyMask();
    $tsa=array();

    if (isset($this->attributes->attr)) {
      foreach($this->attributes->attr as $k=>$v) {
	if ((get_class($v) == "NormalAttribute")&&($v->usefor!='Q')&&($v->isInAbstract)) $tsa[$v->id]=$v;
      }
    }
    return $tsa;      
  }

  

  /**
   * return all the attributes object for title
   * the attribute can be defined in fathers
   * @return array DocAttribute
   */
  final public function GetTitleAttributes() { 
    if (!$this->_maskApplied) $this->ApplyMask();
    $tsa=array();
    if (isset($this->attributes->attr)) {
      foreach($this->attributes->attr as $k=>$v) {
	if ((get_class($v) == "NormalAttribute") && ($v->isInTitle)) $tsa[$v->id]=$v;      
      }
    }
    return $tsa;
  }

  /**
   * return all the attributes that can be use in profil
   * 
   * @return array DocAttribute
   */
  final public function GetProfilAttributes() { 
    if (!$this->_maskApplied) $this->ApplyMask();
    $tsa=array();
    $tsb=array();
    $wopt=false;
    if (isset($this->attributes->attr)) {
      foreach($this->attributes->attr as $k=>$v) {
	if ((get_class($v) == "NormalAttribute") && ($v->type=="docid") ) {
	  if ($v->getOption("isuser")!="") {
	    if ($v->getOption("isuser")=="yes") $tsb[$v->id]=$v;
	    $wopt=true;
	  } else  $tsa[$v->id]=$v;
	}
      }
    }
    if ($wopt) return $tsb;
    return $tsa;
  }


  /** 
   * return all the attributes object for to e use in edition
   * the attribute can be defined in fathers
   * @return array DocAttribute
   */
  final public function GetInputAttributes($onlyopt=false)
  { 
    if (!$this->_maskApplied) $this->ApplyMask();
    $tsa=array();


    foreach($this->attributes->attr as $k=>$v) {
      if ((get_class($v) == "NormalAttribute") && (!$v->inArray()) && 
	  ($v->mvisibility != "I" )) {  // I means not editable
	if ((($this->usefor=="Q") && ($v->usefor=="Q")) ||
	    (($this->usefor!="Q") && 
	     ((($v->usefor!="Q")&&(!$onlyopt)) || (($v->usefor=="O")&&($onlyopt))  )))
	  $tsa[$v->id]=$v;    //special parameters
      }
    }
    return $tsa;
  }
  /** 
   * return all the parameters definition for its family
   * the attribute can be defined in fathers
   * @return array DocAttribute
   */
  final public function getParamAttributes()    { 
     
    if (!$this->_maskApplied) $this->ApplyMask();
    if ((isset($this->attributes)) && (method_exists($this->attributes,"getParamAttributes")))
      return $this->attributes->getParamAttributes();      
    else return array();
  }


  /**
   * return all the attributes object for abstract
   * the attribute can be defined in fathers
   * @param bool $onlyfile set to true if don't want images
   * @return array DocAttribute
   */
  final public function GetFileAttributes($onlyfile=false)
  {      
    if (!$this->_maskApplied) $this->ApplyMask();
    $tsa=array();
      
    foreach($this->attributes->attr as $k=>$v) {
      if ((get_class($v) == "NormalAttribute") && ($v->usefor != 'Q') &&
	  ((($v->type == "image") && (! $onlyfile) ) || 
	   ($v->type == "file"))) $tsa[$v->id]=$v;
    }
    return $tsa;      
  }


  /**
   * return files properties of file attributes
   * 
   * @return array 
   */
  final public function GetFilesProperties() {      
    $dvi = new DocVaultIndex($this->dbaccess);
    $tvid=$dvi->getVaultIds($this->id);
    $tinfo=array();
    $vf = newFreeVaultFile($this->dbaccess);
    foreach ($tvid as $vid) {
        $info=null;
      $err=$vf->Retrieve($vid, $info);
      $t=get_object_vars($info);
      $t["vid"]=$vid;
      if ($err=="") $tinfo[]=$t;
    }

    return $tinfo;
  } 
  /**
   * verify if has some files waiting conversion
   * 
   * @return bool 
   */
  final public function hasWaitingFiles() {      
    $dvi = new DocVaultIndex($this->dbaccess);
    $tvid=$dvi->getVaultIds($this->id);
    if (count($tvid) ==0) return false;
    $sql=sprintf("select id_file from vaultdiskstorage where teng_state=%d and %s limit 1",TransformationEngine::status_waiting,getSqlCond($tvid,"id_file",true));
    simpleQuery($this->dbaccess,$sql,$waiting,true,true);
    return ($waiting!=false);
  } 


  
  
  /**
   * reset Conversion of file
   * update $attrid_txt table column
   * @param string $attrid file attribute identificator
   * @return string error message 
   */
  public function resetConvertVaultFile($attrid,$index) {  
        $err='';
        $val=$this->getTValue($attrid, false, $index);
        if (($index==-1) && (count($val) == 1)) {
        	$val=$val[0];
        }
        
        if ($val) {
            $info=$this->getFileInfo($val);
            if ($info) {
                  $ofout=new VaultDiskStorage($this->dbaccess,$info["id_file"]);
                  if ($ofout->isAffected()) {
                    $err=$ofout->delete();
                  }
            }            
        }
        return $err;
  }
  /**
   * send a request to TE to convert fiele
   * update $attrid_txt table column
   * @param string $va value of file attribute like mime|vid
   * @param string $engine the name of transformation
   * @return new file reference
   */
  public function convertVaultFile($va,$engine,$isimage=false,$force=false) {     
    include_once("FDL/Lib.Vault.php");   
    $engine=strtolower($engine);
    $value='';
    if (is_array($va)) return "";
    
    if (getParam("TE_ACTIVATE")=="yes") {
      if (preg_match(PREGEXPFILE, $va, $reg)) {  
	$vidin=$reg[2];
	$info=vault_properties($vidin,$engine);

	// in case of server not reach : try again
      if ($info->teng_state == TransformationEngine::error_connect) $info->teng_state=TransformationEngine::status_inprogress;
	if ((! $info->teng_vid) || ($info->teng_state==TransformationEngine::status_inprogress)) {
	  $vf = newFreeVaultFile($this->dbaccess);
	  if (! $info->teng_vid) {
	    // create temporary file
	    $value=sprintf(_("conversion %s in progress"),$engine);
	    if ($isimage) {
	      $filename=getParam("CORE_PUBDIR")."/Images/workinprogress.png";
	    } else   $filename=uniqid(getTmpDir()."/conv").".txt";
	    $nc=file_put_contents($filename,$value);
	    $vidout=0;
	    $err=$vf->Store($filename, false , $vidout,"",$engine,$vidin);
	    $info=vault_properties($vidin);
	    if (! $isimage) {
	      unlink($filename);
	      $mime='text/plain';
	    } else {
	      $mime='image/png';
	    }
	  
	    $value="$mime|$vidout";
	    if ($err=="") $vf->rename($vidout,sprintf(_("conversion of %s in progress").".%s",$info->name,$engine));

	  
	    $this->AddComment("value $engine : $value");
	  } else {	 
	    if ($err=="") {
	      $info1=vault_properties($vidin);
	      $vidout=$info->id_file;
	      $vf->rename($vidout,sprintf(_("update of %s in progress").".%s",$info1->name,$engine));
	      $value=$info->mime_s.'|'.$info->id_file;
	    }
	  }

	  $err=vault_generate($this->dbaccess,$engine,$vidin,$vidout,$isimage,$this->initid);
	  if ($err!="") {
	       $this->addComment(sprintf(_("convert file %s as %s failed : %s"),$info->name,$engine, $err), HISTO_ERROR);
	  }
	} else {
	  if ($isimage) {
	    if ($info->teng_state<0) {
	      if ($info->teng_state==-1)  $value="convertfail.png";
	      else $value="convertimpossible.png";
	    } else {
	      if ($info->teng_state==1)   $value=$info->mime_s.'|'.$info->id_file.'|'.$info->name;
	    }
	  } else{
	    $value=$info->mime_s.'|'.$info->id_file.'|'.$info->name;
	  }
	}
      }
    }
    return $value;
  }
  /** return all the attributes object for popup menu
   * the attribute can be defined in fathers
   * @param boolean $viewhidden set to true if need all defined menu (hidden also)
   * @return array DocAttribute
   */
  function GetMenuAttributes($viewhidden=false)
  {      
    if (!$this->_maskApplied) $this->ApplyMask();
    $tsa=array();
      
    reset($this->attributes->attr);
    foreach($this->attributes->attr as $k=>$v) {
      if (((get_class($v) == "MenuAttribute"))&&(($v->mvisibility != 'H')||$viewhidden)) $tsa[$v->id]=$v;
	  
	
    }
    return $tsa;
  }

  /**
   * return all the necessary attributes 
   * @param bool $parameters set to true if want parameters instead of attributes
   * @return array DocAttribute
   */
  final public function GetNeededAttributes($parameters=false) {
      $tsa=array();

      if ($parameters) {
          foreach($this->attributes->attr as $k=>$v) {
              if ((get_class($v) == "NormalAttribute") && ($v->needed) && ($v->usefor=='Q')) $tsa[$v->id]=$v;
          }
      } else {
          if (!$this->_maskApplied) $this->ApplyMask();
          foreach($this->attributes->attr as $k=>$v) {
              if ((get_class($v) == "NormalAttribute") && ($v->needed) && ($v->usefor!='Q')) $tsa[$v->id]=$v;
          }
      }
      return $tsa;
  }

  final public function isCompleteNeeded() {
    $tsa=$this->GetNeededAttributes();
    $err="";
    foreach($tsa as $k=>$v) {
      if ($this->getValue($v->id) == "") $err .= sprintf(_("%s needed\n"),$v->getLabel());
    }
    return $err;
  }

  final public function equal($a,$b) {
    return  ($this->$a == $b);
  }
 
  /**
   * return list of attribut which can be exported
   * @param bool $withfile true if export also file attribute
   * @param bool $forcedefault if true preference FREEDOM_EXPORTCOLS are not read
   * @return array DocAttribute
   */
  final public function GetExportAttributes($withfile=false,$forcedefault=false) { 
    include_once("GENERIC/generic_util.php");
    global $action;

    if ($this->doctype=='C') $famid=$this->id;
    else $famid=$this->fromid;
    if (!$this->_maskApplied) $this->ApplyMask();
    $tsa=array();
    if (isset($this->attributes->attr)) {
      $pref=getFamilyParameter($action,$famid,"FREEDOM_EXPORTCOLS");
      if ((!$forcedefault) && ($pref != "")) {
       
	$tpref=explode(";",$pref);
	

	foreach($this->attributes->attr as $k=>$v) {	  
	  if (in_array($v->id,$tpref))  {
	    $tsa[$v->id]=$v;
	  }
	}             
      } else {
	foreach($this->attributes->attr as $k=>$v) {	  
	  if (get_class($v) == "NormalAttribute")  {

	    if (($v->type != "array") && ($withfile || (($v->type != "image") &&($v->type != "file"))))  $tsa[$v->id]=$v;
	  }
	
	}
      }
    }
    return $tsa;      
  } 

  /**
   * return all the attributes object for import
   * @return array DocAttribute
   */
  final public function GetImportAttributes()
  {      

    if (!$this->_maskApplied) $this->ApplyMask();
    $tsa=array();
    $tattr = $this->attributes->attr;

    foreach($tattr as $k=>$v) {

      if ((get_class($v) == "NormalAttribute") && 
	  (($v->mvisibility == "W") || ($v->mvisibility == "O") || ($v->type == "docid")) &&
	  ($v->type != "array")  ) {
	  
	if (preg_match("/\(([^\)]+)\):(.+)/", $v->phpfunc, $reg)) {
	  
	  $aout = explode(",",$reg[2]);
	  foreach($aout as $ka=>$va) {
	    $ra = $this->GetAttribute($va);
	    if ($ra) $tsa[strtolower($va)]=$ra;
	  }
	
      
	}
	$tsa[$v->id]=$v;
      }
    }


    uasort($tsa,"tordered"); 
    return $tsa;      
  }


  /**
   * return all the attributes which can be sorted
   * @return array DocAttribute
   */
  public function GetSortAttributes()  {      
    $tsa = array();
    $nattr = $this->GetNormalAttributes();
    reset($nattr);

    foreach($nattr as $k=>$a) {
        if ($a->getOption('sortable') != 'yes') {
      if ($a->repeat || ($a->visibility == "H")||  ($a->visibility == "I") || ($a->visibility == "O") || ($a->type == "longtext") || ($a->type == "xml") || 
	  (($a->type == "docid") && ($a->getOption("doctitle")==""))||  ($a->type == "htmltext") ||
	  ($a->type == "image") || ($a->type == "file" ) || ($a->fieldSet->visibility == "H") ||
	  ($a->getOption('sortable') == 'no' )) continue;
        }
      $tsa[$a->id]=$a;
        
    }
    return $tsa;      
  } 

  // recompute the title from attribute values
  final public function RefreshTitle() {

    if ($this->doctype == 'C') return; // no refresh for family  document

    $ltitle = $this->GetTitleAttributes();

    $title1 = "";
    foreach($ltitle as $k=>$v) {
      if ($this->GetValue($v->id) != "") {
	if ($v->type=="enum") $title1.= $this->GetHtmlValue($v,$this->GetValue($v->id))." ";
	else $title1.= $this->GetValue($v->id)." ";
      }
    }
    if (chop($title1) != "")  $this->title = mb_substr(chop(str_replace("\n"," ",$title1)),0,255);// restric to 256 char
    $this->title=mb_substr(chop(str_replace("\n"," ",$this->getSpecTitle())),0,255);
  }
 
  /**
   * call after construct
   * @return void
   */
  function postConstructor() {
  }
  /**
   * no in postUpdate method :: call this only if real change (values)
   * @return string error message
   */
  function PostModify() {
    // to be defined in child class
    return "";
  }
  /**
   * called when user edit a document FDL/editcard
   */
  function preEdition() {
    // to be defined in child class
    return "";
  }
  /**
   * called when user view a document FDL/fdl_card
   */
  function preConsultation() {
    // to be defined in child class
    return "";
  }
  /**
   * call in doc::postInsert method
   * @return string error message
   */
  function PostCreated() {
    // to be defined in child class
    return "";
  }
  /**
   * call in doc::add method
   * @return string error message
   */
  function PreCreated() {
    // to be defined in child class
    return "";
  }


  /**
   * call when doc is being imported before any modification
   * if return non null string import will ne aborted
   * @return string error message, if no error empty string
   */
  function preImport() {}
  /**
   * call when doc is imported after databases modification
   * the error message will appeared like message
   * @return string warning message, if no warning empty string
   */
  function postImport() {}

  /**
   * recompute values from title
   * the first value of type text use for title will be modify to have the new title
   * @param string $title new title
   */
  final public function setTitle($title) {
    $ltitle = $this->GetTitleAttributes();
    foreach ($ltitle as $at) {
      if (($at->type == 'text') && (($at->visibility=='W')||($at->visibility=='O')) && (! $at->inArray())) {
	$otitle=$at;
	break;
      }
    }
    if ($otitle) {
      $idt=$otitle->id;
      
      $this->title=str_replace("\n"," ",$title);
      $this->setvalue($idt,$title);
    }
  }

 
  
  /**
   * return all attribute values
   *
   * @return array all attribute values 
   */
  final public function GetValues()  {
    $this->lvalues=array();
    //    if (isset($this->id) && ($this->id>0)) {

    $nattr = $this->GetNormalAttributes();
    foreach($nattr as $k=>$v) {
      $this->lvalues[$v->id] = $this->GetValue($v->id);
    }
    // }
    $this->lvalues=array_merge($this->lvalues,$this->mvalues); // add more values possibilities
    reset($this->lvalues);
    return $this->lvalues;
  }
  //-------------------------------------------------------------------


  /**
   * return the value of an attribute document 
   * @param string $idAttr identificator of attribute
   * @param string $def default value returned if attribute not found or if is empty
   * @return string the attribute value 
   */
  final public function getValue($idAttr, $def="")  {      
    
    $lidAttr=strtolower($idAttr);
    if (isset($this->$lidAttr) && ($this->$lidAttr != "")) return $this->$lidAttr;
         
    return $def;
  }

  /**
   * return the value of an list attribute document
   *
   * the attribute must be in an array or of a type '*list' like enumlist or textlist
   * @param string $idAttr identificator of list attribute 
   * @param string $def default value returned if attribute not found or if is empty
   * @param string $index the values for $index row (default value -1 means all values)
   * @return array the list of attribute values 
   */
  final public function getTValue($idAttr, $def="",$index=-1)  {
    $v=$this->getValue("$idAttr",$def);
    if ($v == "") {
      if ($index == -1) return array();
      else return $def;
    } else if ($v == "\t") {
      if ($index == -1) return array("");
      else return "";
    }
    $t = $this->_val2array($v);
    if ($index == -1) {
      $oa=$this->getAttribute($idAttr);
      if ($oa && $oa->type=="xml") {
	foreach ($t as $k=>$v) {
	  $t[$k]=str_replace('<BR>',"\n",$v);
	}
      }
      return $t;
    }
    if (isset($t[$index])) {
      $oa=$this->getAttribute($idAttr);
      if ($oa && $oa->type=="xml") $t[$index]=str_replace('<BR>',"\n",$t[$index]);
      return $t[$index];
    }
    else return $def;
  }

  /**
   * return the array of values for an array attribute
   *
   * the attribute must  an array type
   * @param string $idAttr identificator of array attribute 
   * @param string $index the values for $index row (default value -1 means all values)
   * @return array all values of array order by rows (return false if not an array attribute)
   */
  final public function getAValues($idAttr, $index=-1)  { 
    $a=$this->getAttribute($idAttr);
    if ($a->type=="array") {
      $ta=$this->attributes->getArrayElements($a->id);
      $ti=array();
      // transpose
      foreach($ta as $k=>$v) {
        $tv[$k]=$this->getTValue($k);
	$ix=max($ix,count($tv[$k]));
      }
      for ($i=0;$i<$ix;$i++) {
        $ti[$i]=array();
      }
      foreach($ta as $k=>$v) {
        for ($i=0;$i<$ix;$i++) {
          $ti[$i]+=array($k=>$tv[$k][$i]);
        }
      }
      if ($index==-1) return $ti;
      else return $ti[$index];
    }
    return false;        
  }


  /**
   * delete a row in an array attribute
   *
   * the attribute must an array type
   * @param string $idAttr identificator of array attribute 
   * @param string $index  $index row (first is 0)
   * @return string error message, if no error empty string
   */
  final public function removeArrayRow($idAttr, $index)  { 
    $a=$this->getAttribute($idAttr);
    if ($a->type=="array") {
      $ta=$this->attributes->getArrayElements($a->id);
      $ti=array();
      $err="";
      // delete in each columns
      foreach($ta as $k=>$v) {
        $tv=$this->getTValue($k);
	unset($tv[$index]);
	$tvu=array();
	foreach ($tv as $vv) $tvu[]=$vv; // key reorder
	$err.=$this->setValue($k,$tvu);
      }
      return $err;
    }
    return sprintf(_("%s is not an array attribute"),$idAttr);        
  }  

 
  /**
   * in case of array where each column are not the same length
   *
   * the attribute must an array type
   * fill uncomplete column with null values
   * @param string $idAttr identificator of array attribute 
   * @return string error message, if no error empty string
   */
  final public function completeArrayRow($idAttr)  { 
    $a=$this->getAttribute($idAttr);
    if ($a->type=="array") {
      $ta = $this->attributes->getArrayElements($a->id);
     
      $max=-1;
      $maxdiff=false;
      foreach($ta as $k=>$v) { // detect uncompleted rows
	$c=count($this->getTValue($k));	
	if ($max < 0) $max=$c;
	else {
	  if ($c != $max) $maxdiff=true;
	  if ($max < $c) $max=$c;
	}
      }
      if ($maxdiff) {
	foreach($ta as $k=>$v) { // fill uncompleted rows
	  $c=count($this->getTValue($k));
	  if ($c < $max) {
	    $t=$this->getTValue($k);
	    $t=array_pad($t,$max,"");	  
	    $err.=$this->setValue($k,$t);
	  }
	}
      }
     
      return $err;
    }
    return sprintf(_("%s is not an array attribute"),$idAttr);        
  }
	/**
	* add new row in an array attribute
	*
	* the attribute must be an array type
	* @param string $idAttr identificator of array attribute
	* @param array $tv values of each column. Array index must be the attribute identificator
	* @param string $index  $index row (first is 0) -1 at the end; x means before x row
	* @return string error message, if no error empty string
	*/
	final public function addArrayRow($idAttr, $tv, $index=-1) {
		$a = $this->getAttribute($idAttr);
		if ($a->type == "array") {
			$err = $this->completeArrayRow($idAttr);
			if ($err == "") {
				$ta = $this->attributes->getArrayElements($a->id);
				$ti = array();
				$err = "";
				// add in each columns
				foreach ($ta as $k => $v) {
					$tnv = $this->getTValue($k);
					$val = $tv[strtolower($k)];
					if ($index == 0) {
						array_unshift($tnv, $val);
					} elseif($index > 0 && $index < count($tnv)) {
						$t1 = array_slice($tnv, 0, $index);
						$t2 = array_slice($tnv, $index);
						$tnv = array_merge($t1, array($val), $t2);
					} else {
						$tnv[] = $val;
					}
					$err.=$this->setValue($k, $tnv);
				}
				if ($err = "") {
					$err = $this->completeArrayRow($idAttr);
				}
			}
			return $err;
		}
		return sprintf(_("%s is not an array attribute"), $idAttr);
	}



  /**
   * affect value for $attrid attribute
   *
   * the affectation is only in object. To set modification in database the modify method must be 
   * call after modification
   * If value is empty no modification are set. To reset a value use Doc::DeleteValue method.
   * an array can be use as value for values which are in arrays
   * @param string $idAttr identificator of attribute 
   * @param string $value new value for the attribute
   * @param int $index only for array values affect value in a specific row
   * @param int &$kvalue in case of error the index of error (for arrays)
   * @return string error message, if no error empty string
   */
  final public function SetValue($attrid, $value,$index=-1,&$kvalue=null) {
    // control edit before set values
  	

    if (! $this->withoutControl) {
      if ($this->id > 0) { // no control yet if no effective doc
	$err = $this-> Control("edit");
	if ($err != "") return ($err);
      }
    }
    $attrid = strtolower($attrid);
    $oattr=$this->GetAttribute($attrid);
    if ($index > -1) { // modify one value in a row
      $tval=$this->getTValue($attrid);
      $tval[$index]=$value;
      $value=$tval;
    }
    if (is_array($value)) {
      if ($oattr->type=='htmltext') {
		  $value= $this->_array2val($value,"\r");
		  if($value === '') {
			  $value = DELVALUE;
		  }
	  }
      else {
	if (count($value)==0) $value = DELVALUE;
	elseif ((count($value) == 1) && (first($value)==="" || first($value)===null) && (substr(key($value),0,1)!="s")) $value= "\t"; // special tab for array of one empty cell 			  
	else {
	  if ($oattr->repeat && (count($value)==1) && substr(key($value),0,1)=="s") {
	    $ov=$this->getTValue($attrid);
	    $rank=intval(substr(key($value),1));
	    if (count($ov) < ($rank-1)) { // fill array if not set
	      $start=count($ov);
	      for ($i=$start;$i<$rank;$i++) $ov[$i]="";
	    }
	    foreach ($value as $k=>$v) $ov[substr($k,1,1)]=$v;
	    $value=$this->_array2val($ov);
	  } else {
	    $value = $this->_array2val($value);
	  }
	}
      }
    }
    if (($value !== "") && ($value !== null))  {
      // change only if different
      if ($oattr === false) return sprintf(_("attribute %s unknow in family %s [%d]"),$attrid, $this->title, $this->id);
      if ($oattr->mvisibility=="I") return sprintf(_("no permission to modify this attribute %s"),$attrid);
      if ($value === DELVALUE) {
	if ( $oattr->type != "password") $value=" ";
	else return;
      }
      if ($value === " ") {
	$value=""; // erase value
	if  ($this->$attrid != "") {
	  $this->hasChanged=true;
	  //print "change by delete $attrid  <BR>";
	  $this->_oldvalue[$attrid]=$this->$attrid;
	  $this->$attrid="";
	  if ($oattr->type=="file") {
	    // need clear computed column
	    $this->clearFullAttr($oattr->id);
	  }
	}
      } else {
	$value=trim($value," \x0B\r");// suppress white spaces end & begin
	if (!isset($this->$attrid)) $this->$attrid="";

	if  (strcmp($this->$attrid, $value)!=0 && strcmp($this->$attrid, str_replace("\n ","\n",$value))!=0)	  {
	  $this->hasChanged=true;
	  // print "change2 $attrid  to <PRE>[{$this->$attrid}] [$value]</PRE><BR>";
	  if ($oattr->repeat) {
	    $tvalues = $this->_val2array($value);
	  } else {
	    $tvalues[]=$value;
	  }

	  foreach($tvalues as $kvalue=>$avalue) {
	    if (($avalue != "") && ($avalue != "\t")) {
	      if ($oattr) {
	          $avalue=trim($avalue);
	          $tvalues[$kvalue]=$avalue;
		switch($oattr->type) {
		case 'docid':
		  if  ((! strstr($avalue,"<BR>")) && (! strstr($avalue,"\n"))&& (!is_numeric($avalue))) {
		    $tvalues[$kvalue]=getIdFromName($this->dbaccess,$avalue);
		  }
		  break;
		case 'enum':
		  if ($oattr->getOption("etype")=="open") {
		    // added new
		    $tenum=$oattr->getEnum();
		    $keys=array_keys($tenum);
		    if (! in_array($avalue,$keys)) {
		      $oattr->addEnum($this->dbaccess,$avalue,$avalue);
		    }
		  }
		  break;
		case 'double':
		  if ($avalue=='-') $avalue=0;
		  $tvalues[$kvalue]=str_replace(",",".",$avalue);
		  $tvalues[$kvalue]=str_replace(" ","",$tvalues[$kvalue]);
		  if (($avalue != "\t") && (! is_numeric($tvalues[$kvalue]))) return sprintf(_("value [%s] is not a number"),$tvalues[$kvalue]);
		  break;
		case 'money':
		  if ($avalue=='-') $avalue=0;
		  $tvalues[$kvalue]=str_replace(",",".",$avalue);
		  $tvalues[$kvalue]=str_replace(" ","",$tvalues[$kvalue]);
		  if (($avalue != "\t") && (! is_numeric($tvalues[$kvalue]))) return sprintf(_("value [%s] is not a number"),$tvalues[$kvalue]);
		  $tvalues[$kvalue]=round(doubleval($tvalues[$kvalue]),2);
		  break;
		case 'integer':
		case 'int':
		  if ($avalue=='-') $avalue=0;
		  if (($avalue != "\t") && (! is_numeric($avalue))) return sprintf(_("value [%s] is not a number"),$avalue);
		  if (intval($avalue) != floatval($avalue)) return sprintf(_("[%s] must be a integer"),$avalue);

		  $tvalues[$kvalue]=intval($avalue);
		  break;
		case 'time':
		  $tt=explode(":",$avalue);
		  if (count($tt)==2) {
		    list($hh,$mm) = $tt;
		    $tvalues[$kvalue]=sprintf("%02d:%02d",intval($hh)%24,intval($mm)%60);
		  } else if (count($tt)==3) {
		    list($hh,$mm,$ss) = $tt;
		    $tvalues[$kvalue]=sprintf("%02d:%02d:%02d",intval($hh)%24,intval($mm)%60,intval($ss)%60);
		  }
		  break;
		case 'date':
		  if (trim($avalue)=="") {
		    if (! $oattr->repeat) $tvalues[$kvalue]="";
		  } else {
  										 
		    $localeconfig = getLocaleConfig();
		    if($localeconfig !== false) {
		      $tvalues[$kvalue]=stringDateToIso($avalue, $localeconfig['dateFormat']);
		      $tvalues[$kvalue]=preg_replace('#^([0-9]{4})-([0-9]{2})-([0-9]{2})#', '$3/$2/$1', $tvalues[$kvalue]);
		    }
		    else {
		      return sprintf(_("value [%s] is not a valid date"),$avalue);
		    }
		  }
		  break;
		case 'timestamp':
		  if (trim($avalue)=="") {
		    if (! $oattr->repeat) $tvalues[$kvalue]="";
		  } else {
  										 
		    $localeconfig = getLocaleConfig();
		    if($localeconfig !== false) {
		      $tvalues[$kvalue]=stringDateToIso($avalue, $localeconfig['dateTimeFormat']);
		      $tvalues[$kvalue]=preg_replace('#^([0-9]{4})-([0-9]{2})-([0-9]{2})#', '$3/$2/$1', $tvalues[$kvalue]);
		    }
		    else {
		      return sprintf(_("value [%s] is not a valid timestamp"),$avalue);
		    }
		  }
		  break;
		case 'file':
		  // clear fulltext realtive column
		  if ((!$oattr->repeat) || ($avalue != $this->getTValue($attrid,"",$kvalue))) {
		    // only if changed
		    $this->clearFullAttr($oattr->id,($oattr->repeat)?$kvalue:-1);
		  }
		  $tvalues[$kvalue]=str_replace('\\','',$tvalues[$kvalue]); // correct possible save error in old versions
		  break;
                case 'image':
                  $tvalues[$kvalue]=str_replace('\\','',$tvalues[$kvalue]);
                  break;
		case 'htmltext':
		  $avalue=str_replace('&quot;','--quoteric--',$avalue);

		  $tvalues[$kvalue] = preg_replace("/<!--.*?-->/ms", "", $tvalues[$kvalue]); //delete comments
		  $tvalues[$kvalue]=str_replace(array('<noscript','</noscript>','<script','</script>'),array('<pre', '</pre>','<pre', '</pre>'),html_entity_decode($tvalues[$kvalue],ENT_NOQUOTES,'UTF-8'));
		  $tvalues[$kvalue]=str_replace("[","&#x5B;",$tvalues[$kvalue]); // need to stop auto instance
		  $tvalues[$kvalue]=str_replace('--quoteric--','&amp;quot;',$tvalues[$kvalue]); // reinject original quote entity
		  $tvalues[$kvalue]=preg_replace("/<\/?meta[^>]*>/s","",$tvalues[$kvalue]);
		  if ($oattr->getOption("htmlclean")=="yes") {
		    $tvalues[$kvalue]=preg_replace("/<\/?span[^>]*>/s","",$tvalues[$kvalue]);
		    $tvalues[$kvalue]=preg_replace("/<\/?font[^>]*>/s","",$tvalues[$kvalue]);
		    $tvalues[$kvalue]=preg_replace("/<style[^>]*>.*?<\/style>/s","",$tvalues[$kvalue]);
		    $tvalues[$kvalue]=preg_replace("/<([^>]*) style=\"[^\"]*\"/s","<\\1",$tvalues[$kvalue]);
		    $tvalues[$kvalue]=preg_replace("/<([^>]*) class=\"[^\"]*\"/s","<\\1",$tvalues[$kvalue]);
		  }
		  break;
		case 'thesaurus':
		  // reset cache of doccount
		  include_once("FDL/Class.DocCount.php");
		  $d=new docCount($this->dbaccess);
		  $d->famid=$this->fromid;
		  $d->aid=$attrid;
		  $d->deleteAll();
		  break;
		case 'text':
		    $tvalues[$kvalue]=str_replace("\r"," ",$tvalues[$kvalue]);
		    break;
		}
	      }
	    }
	  }
	  //print "<br/>change $attrid to :".$this->$attrid."->".implode("\n",$tvalues);
	  $this->_oldvalue[$attrid]=$this->$attrid;
	  $this->$attrid=implode("\n",$tvalues);

	}

      }
  		
    }
  }

  /**
   * clear $attrid_txt and $attrid_vec
   *
   * @param string $idAttr identificator of file attribute 
   * @return string error message, if no error empty string
   */
  final private function clearFullAttr($attrid,$index=-1) {
    $attrid=strtolower($attrid);
    $oa=$this->getAttribute($attrid);
    if ($oa) {
      if ($oa->getOption("search")!="no") {      
	$ak=$attrid.'_txt';
	if ($index == -1) {
	  $this->$ak='';
	} else {
	  if ($this->AffectColumn(array($ak))) {
	    $this->$ak=sep_replace($this->$ak,$index);
	
	  }
	}
	$this->fields[$ak]=$ak;
	$ak=$attrid.'_vec';
	$this->$ak='';
	$this->fields[$ak]=$ak;
	$this->fulltext='';
	$this->fields['fulltext']='fulltext'; // to enable trigger
	$this->textsend[$attrid.$index]=array("attrid"=>$attrid,
					      "index"=>$index);
      }
    }
  }
  /**
   * send text transformation 
   * after ::clearFullAttr is called
   * 
   */
  final private function sendTextToEngine() {
    if (is_array($this->textsend)) { 
      include_once("FDL/Lib.Vault.php");
      foreach($this->textsend as $k=>$v) {
	$index=$v["index"];
	if ($index>0) $fval=$this->getTValue($v["attrid"],"",$index);
	else $fval=strtok($this->getValue($v["attrid"]),"\n");
	if (preg_match(PREGEXPFILE, $fval, $reg)) {
	  $vid= $reg[2];
	  $err=sendTextTransformation($this->dbaccess,$this->id,$v["attrid"],$index,$vid);
	  if ($err!="") $this->AddComment(_("error sending text conversion").": $err",HISTO_NOTICE);
	}
      }
      $this->textsend=array();//reinit
    }
  }
  /**
   * force recompute all file text transformation 
   * @param string $aid file attribute identificator. If false all files attributes will be reseted
   * @return string error message, if no error empty string
   */
  final public function recomputeTextFiles($aid=false) {
    if (! $aid) $afiles=$this->GetFileAttributes(true);
    else $afiles[$aid]=$this->getAttribute($aid);

    $ttxt=array();
    foreach ($afiles as $k=>$v) {
      $kt=$k.'_txt';
      $ttxt[]=$kt;
      if ($v->inArray()) {
	$tv=$this->getTValue($k);
	foreach ($tv as $kv=>$vv) {
	  $this->clearFullAttr($k,$kv);	  
	}
      }  else {
	$this->clearFullAttr($k);
      }
      $this->$kt='';
      $kv=$k.'_vec';
      $ttxt[]=$kv;
      $this->$kv='';
    }
    $this->modify(true,$ttxt,true);
    $err=$this->sendTextToEngine();
    return $err;
  }
  /**
   * affect text value in $attrid file attribute
   *
   * create a new file in Vault to replace old file
   * @param string $idAttr identificator of file attribute 
   * @param string $value new value for the attribute
   * @param string $ftitle the name of file (if empty the same as before)
   * @return string error message, if no error empty string
   */
  final public function SetTextValueInFile($attrid, $value,$ftitle="") {   
    $a=$this->getAttribute($attrid);     
    if ($a->type == "file") {
      $err="file conversion";
      $vf = newFreeVaultFile($this->dbaccess);
      $fvalue=$this->getValue($attrid);
      $basename="";
      if (preg_match(PREGEXPFILE, $fvalue, $reg)) {
	$vaultid= $reg[2];
	$mimetype=$reg[1];
	
	$err=$vf->Retrieve($vaultid, $info);

	if ($err == "") {
	  $basename=$info->name;
	}
      }
      $filename=uniqid(getTmpDir()."/_html").".html";
      $nc=file_put_contents($filename,$value);
      $err=$vf->Store($filename, false , $vid);
      if ($ftitle != "") {
	$vf->Rename($vid,$ftitle);
	$basename=$ftitle;
      } else {
	if ($basename!="") { // keep same file name
	  $vf->Rename($vid,$basename);
	}
      }
      if ($err == "") {
	$mime=trim(shell_exec(sprintf("file -ib %s", escapeshellarg($filename))));
	$value="$mime|$vid|$basename";
	$err=$this->setValue($attrid,$value);
	//$err="file conversion $mime|$vid";	
	if ($err=="xx") {
	  $index=0;
	  $this->clearFullAttr($attrid); // because internal values not changed
	}
      }
      if ($nc>0) unlink($filename);	     
    } 	
    return $err;
  } 
  /**
   * get text value from $attrid file attribute
   *
   * get content of a file (must be an ascii file)
   * @param string $idAttr identificator of file attribute 
   * @param string &$text the content of the file
   * @return string error message, if no error empty string
   */
  final public function getTextValueFromFile($attrid, &$text) {   
    $a=$this->getAttribute($attrid);     
    if ($a->type == "file") {
      $vf = newFreeVaultFile($this->dbaccess);
      $fvalue=$this->getValue($attrid);
      $basename="";
      if (preg_match(PREGEXPFILE, $fvalue, $reg)) {
	$vaultid= $reg[2];
	$mimetype=$reg[1];
	
	$err=$vf->Retrieve($vaultid, $info);

	if ($err == "") {
	  $basename=$info->name;
	}
      }
      $filename=$info->path;
      $text=file_get_contents($filename);  
    } 	
    return $err;
  } 

  /**
   * save stream file in an file attribute
   *
   * replace a new file in Vault to replace old file
   * @param string $idAttr identificator of file attribute 
   * @param stream $stream file resource from fopen
   * @param int $index for array of file : modify in specific row
   * @return string error message, if no error empty string
   */
  final public function saveFile($attrid, $stream,$ftitle="",$index=-1) {   
    if (is_resource($stream) && get_resource_type($stream) == "stream") {


      $a=$this->getAttribute($attrid);
      if ($a->type == "file") {
    	$err="file conversion";
    	$vf = newFreeVaultFile($this->dbaccess);
    	if ($index > -1) $fvalue=$this->getTValue($attrid,'',$index);
    	else $fvalue=$this->getValue($attrid);
    	$basename="";
    	if (preg_match(PREGEXPFILE, $fvalue, $reg)) {
	  $vaultid= $reg[2];
	  $mimetype=$reg[1];
	  $oftitle=$reg[3];
	  $err=$vf->Retrieve($vaultid, $info);

	  if ($err == "") {
	    $basename=$info->name;
	  }
    	}
    	if ($ftitle) {
	  $ext=getFileExtension($ftitle);
    	}
    	if ($ext=="") $ext="nop";

    	$filename=uniqid(getTmpDir()."/_fdl").".$ext";
    	$tmpstream=fopen($filename,"w");
    	while (!feof($stream)) {
	  if (false === fwrite($tmpstream, fread($stream, 4096))) {
	    $err = "403 Forbidden";
	    break;
	  }
    	}
    	fclose($tmpstream);
    	// verify if need to create new file in case of revision
    	$newfile=($basename=="");

    	if ($this->revision > 0) {
	  $trev=$this->GetRevisions("TABLE",2);
	  $revdoc=$trev[1];
	  $prevfile=getv($revdoc,strtolower($attrid));
	  if ($prevfile == $fvalue) $newfile=true;
    	}

    	if (! $newfile) {
	  $err=$vf->Save($filename, false , $vaultid);
    	} else {
	  $err=$vf->Store($filename, false , $vaultid);
    	}
    	if ($ftitle != "") {
	  $vf->Rename($vaultid,$ftitle);
    	} elseif ($basename!="") { // keep same file name
	  $vf->Rename($vaultid,$basename);
    	}
    	if ($err == "") {
	  if ($mimetype) $mime=$mimetype;
	  else $mime=trim(shell_exec(sprintf("file -ib %s", escapeshellarg($filename))));
	  if ($ftitle) $value="$mime|$vaultid|$ftitle";
	  else $value="$mime|$vaultid|$oftitle";
	  $err=$this->setValue($attrid,$value,$index);
	  if ($err=="") {
	    $index=0;
	    $this->clearFullAttr($attrid); // because internal values not changed
	  }
	  //$err="file conversion $mime|$vid";
    	}
    	unlink($filename);
    	$this->AddComment(sprintf(_("modify file %s"),$ftitle));
    	$this->hasChanged = true;
      }
    }
    return $err;
  }

  /**
   * use for duplicate physicaly the file
   *
   * @param string $idattr identificator of file attribute 
   * @param string $newname basename if want change name of file
   * @param int $index in case of array
   * @return string attribut value formated to be inserted into a file attribute
   */
  final function copyFile($idattr,$newname="",$index=-1) { 
    if ($index>=0) $f=$this->getTValue($idattr,"",$index);
    else $f=$this->getValue($idattr);
    if ($f) {
      if (preg_match(PREGEXPFILE, $f, $reg)) {
	$vf = newFreeVaultFile($this->dbaccess);
	if ($vf->Show($reg[2], $info) == "") {
	  $cible=$info->path;
	  if (file_exists($cible)) {
	    $err=$vf->Store($cible, false , $vid);
	    if ($err == "") {
	      if (!$newname) $newname=$info->name;
	      if ($newname) {
		$vf->Rename($vid,$newname);   
	      }
	      return $reg[1]."|$vid|$newname";
	    }
	  }
	}
      }
    }
    return false;
  }



  /**
   * rename physicaly the file
   *
   * @param string $idattr identificator of file attribute 
   * @param string $newname base name file
   * @param int $index in case of array of files
   * @return string empty if no error
   */
  final function renameFile($idattr,$newname,$index=-1) {
    if ($newname) {
      if ($index==-1)  $f=$this->getValue($idattr);
      else $f=$this->getTValue($idattr,"",$index);
      if ($f) {
	if (preg_match(PREGEXPFILE, $f, $reg)) {
	  $vf = newFreeVaultFile($this->dbaccess);
	  $vid=$reg[2];
	  if ($vf->Show($reg[2], $info) == "") {
	    $cible=$info->path;	  
	    if (file_exists($cible)) {
	      if ($err == "") {	 	      
		$vf->Rename($vid,$newname);
		$this->setValue($idattr,$info->mime_s.'|'.$vid.'|'.$newname,$index);
	      }	      
	    }
	  }
	}
      }
    }
    return false;
  }
  /**
   * store new file in an file attribute
   *
   * @param string $idAttr identificator of file attribute 
   * @param string $filename file path
   * @param string $ftitle basename of file   
   * @param int $index only for array values affect value in a specific row
   * @return string error message, if no error empty string
   */
  final public function storeFile($attrid, $filename,$ftitle="",$index=-1) {   
    if (is_file($filename) ) {
      include_once("FDL/Lib.Vault.php");

      $a=$this->getAttribute($attrid);     
      if ($a) {
	if (($a->type == "file")||($a->type == "image")) {
	  $err=vault_store($filename,$vaultid,$ftitle);
	  if ($err=="") {
	    $info=vault_properties($vaultid);
	    $mime=$info->mime_s;
	    if (!$ftitle) $ftitle=$info->name;
	    $this->setValue($attrid,"$mime|$vaultid|$ftitle",$index);	
	  }    
	} else {
	  $err=sprintf(_("attribute %s is not a file attribute"),$a->getLabel());
	}
      } else {
	$err=sprintf(_("unknow attribute %s"),$attrid);
      }
    }
    return $err;
  }  
  /**
   * store multiples new files in an file array attribute
   *
   * @param string $idAttr identificator of file attribute 
   * @param array $filename file path
   * @param array $ftitle basename of file
   * @return string error message, if no error empty string
   */
  final public function storeFiles($attrid, $filenames,$ftitle="") {  
    if (!is_array($filenames)) return _("no files");
 
    $a=$this->getAttribute($attrid);
    if (($a->type == "file")||($a->type == "image")) {
      if ($a->inArray()) {
	$tvid=array();
	foreach ($filenames as $k=>$filename) {
	  if (is_file($filename) ) {
	    include_once("FDL/Lib.Vault.php");

	    $err=vault_store($filename,$vaultid,$ftitle[$k]);
	    if ($err=="") {
	      $info=vault_properties($vaultid);
	      $mime=$info->mime_s;
	      if ($ftitle[$k]=="") $ftitle[$k]=$info->name;
	      $tvid[]="$mime|$vaultid|".$ftitle[$k];
	    }
	  }	
	}
	$this->setValue($attrid,$tvid);
      } else {
	$err=sprintf(_("attribute %s is not int a array"),$a->getLabel());
      }
    } else {
      $err=sprintf(_("attribute %s is not a file attribute"),$a->getLabel());
    }
  
    return $err;
  }
  /**
   * Duplicate physically all files of documents
   * 
   */
  function duplicateFiles() {
    $err="";
    $fa=$this->GetFileAttributes();    
    foreach ($fa as $aid=>$oa) {            
      if ($oa->inArray()) {
	$t=$this->getTvalue($oa->id);
	$tcopy=array();
	foreach ($t as $k=>$v) {
	  $tcopy[$k]=$this->copyFile($oa->id,"",$k);	    	    
	}
	$this->setValue($oa->id,$tcopy);
      } else {
	$this->setValue($oa->id,$this->copyFile($oa->id));
      }      
    }
    return $err;
  }
  /**
   * return the related value by linked attributes
   * @param string $RidAttr attribute identificator
   * @param string def $def default return value
   * @param bool $latest always last revision of document
   */
  final public function GetRValue($RidAttr, $def="",$latest=true,$html=false)  {      
    $tattrid = explode(":",$RidAttr);
    $lattrid=array_pop($tattrid); // last attribute

    $doc=$this;
    foreach($tattrid as $k=>$v) { 
      $docid= $doc->getValue($v);
      if ($docid == "") return $def;
      $doc = new_Doc($this->dbaccess, $docid);

      if ($latest) {
	if ($doc->locked == -1) { // it is revised document
	  $ldocid = $doc->latestId();
	  if ($ldocid != $doc->id) $doc = new_Doc($this->dbaccess, $ldocid);
	}
      }
      if (! $doc->isAlive())  return $def;
    }
    if ($html) return $doc->getHtmlAttrValue($lattrid, $def);
    else return $doc->getValue($lattrid, $def);
  }
  

  /**
   * return the previous value for a attibute set before Doc::SetValue
   * can be use in Doc::postModify generaly
   * @param string $attrid identificator of attribute 
   * @return string the old value (false if not modified before)
   * 
   */
  final public function getOldValue($attrid) {
    $attrid=strtolower($attrid);
    if (isset($this->_oldvalue[$attrid])) return $this->_oldvalue[$attrid];
    return false;
  }
  final public function getOldValues() {
    if (isset($this->_oldvalue)) return $this->_oldvalue;
    return array();
  }


  final public function DeleteValue($attrid) {
	$oattr=$this->GetAttribute($attrid);
	if($oattr->type == 'docid') {
		$doctitle = $oattr->getOption('doctitle');
		if($doctitle == 'auto') {
			$doctitle = $attrid.'_title';
		}
		if(!empty($doctitle)) {
			$this->SetValue($doctitle, " ");
		}
	}
    return $this->SetValue($attrid," ");
  }


  /**
   * add values present in values field
   */
  private function GetMoreValues()  {      
    if (isset($this->values)) {
      $tvalues = explode("£",$this->values);
      $tattrids = explode("£",$this->attrids);
      
      foreach($tvalues as $k=>$v) {
	$attrid = $tattrids[$k];	
	if (($attrid != "") &&  ($this->$attrid == "")) {
	  $this->$attrid=$v;
	  $this->mvalues[$attrid]=$v; // to be use in getValues()
	}
      }      
    }      
  }

  /**
   * reset values present in values field
   */
  private function ResetMoreValues()  {      
    if (isset($this->values) && $this->id) {
      $tattrids = explode("£",$this->attrids);
      
      foreach($tattrids as $k=>$v) {
	$attrid = $tattrids[$k];
	if ($attrid) $this->$attrid="";
      }
    } 
    $this->mvalues=array();
  }

  final public function GetValueMethod($value, $attrid='') {
    
    $value=$this->ApplyMethod($value,$value);
    
    return $value;
  } 

  /**
   * apply a method to a doc 
   * specified like ::getFoo(10)
   * @param string $method the method to apply
   * @param string $def default value if no method
   * @param int $index index in case of value in row
   * @param array $bargs first arguments sent before for the method
   * 
   * @return string the value
   */
  final public function ApplyMethod($method,$def="",$index=-1, $bargs=false) {
    $value=$def;
    if (preg_match("/::([^\(]+)\(([^\)]*)\)/",$method, $reg)) {
      if (method_exists ( $this, $reg[1])) {
	if (($reg[2] == "")&& (!$bargs)) {
	  // without argument
	  $value = call_user_func(array($this, $reg[1]));
	} else {
	  // with argument
	  $args = explode(",",$reg[2]);
	  if ($bargs && is_array($bargs)) $args=array_merge($bargs,$args);
	  if ($attrid != "") {
	    $this->AddParamRefresh($reg[2],$attrid);
	  }
	      
	  foreach($args as $k=>$v) { 
	    if ($v != " ") $v=trim($v);
	    if ($attr=$this->getAttribute($v)) {
	      if ($attr->inArray())   $args[$k]=$this->GetTValue($v,"",$index);
	      else $args[$k]=$this->GetValue($v);
	    } else {
	      if (($v[0]=="'")|| ($v[0] == '"')) {
		$lc=substr($v,-1);
		if (($lc == "'") || ($lc == '"')) $v= substr($v,1,-1);
		else $v=substr($v,1);
	      }
	      $args[$k]=$v; // not an attribute just text
	    }
	    //   $args[$k]=$this->GetTValue($args[$k],$def,$index);
	  }	  	  
	  $value = call_user_func_array(array($this, $reg[1]), $args);
	}
      } else {
	addWarningMsg(sprintf(_("Method [%s] not exists"),$reg[1]));
      }
	
    }
    return $value;
  }
 
  /**
   * verify attribute constraint
   *
   * @param string $attrid attribute identificator
   * @return array array of 2 items ("err" + "sug"). 
   * The err is the string error message (empty means no error)
   * The sug is an array of possibles corrections
   */
  final public function verifyConstraint($attrid, $index=-1) {
    $ok=array("err"=>"",
	      "sug"=>array());
    $oattr = $this->getAttribute($attrid);
    if (strlen(trim($oattr->phpconstraint)) > 1) {
      $ko=array("err"=>sprintf(_("method %s not found"),$oattr->phpconstraint),
		"sug"=>array());
      $res = $this->applyMethod($oattr->phpconstraint,$ko,$index);

      if ($res !== true) {
	if (! is_array($res)) {
	  if ($res === false) $res=array("err"=>_("constraint error"));
	  elseif (is_string($res)) $res=array("err"=>$res);
	} elseif ($res["sug"] && (! is_array($res["sug"]))) $res["sug"]=array($res["sug"]);
	if (is_array($res) && $res["err"]!="")	$this->constraintbroken="[$attrid] ".$res["err"];
	return $res;
      }
    }

    return $ok;
     
  }


  /**
   * verify if constraint ore OK
   * @param boolean $stoptofirst stop in first constraint error
   * @param array &$info set of information about constraint test
   * @return string error message (empty means no error)
   */
  final public function verifyAllConstraints($stoptofirst=true,&$info=array()) {
    $err="";
    
    $listattr = $this->GetNormalAttributes();
    foreach ($listattr as $k => $v) {
      if (strlen($v->phpconstraint)> 1) {
	if ($v->inArray()) {
	  $tv = $this->getTValue($v->id); 
	  for ($i=0;$i<count($tv);$i++) {
	    $res= $this->verifyConstraint($v->id,$i);	    
	    if ($res["err"]!="") {
	      $info[$v->id.$i]=array("id"=>$v->id,
				     "sug"=>$res["sug"],
				     "err"=>$res["err"],
				     "index"=>$i,
				     "pid"=>$v->fieldSet->id);
	      if ($stoptofirst) return sprintf("[%s] %s", $v->getLabel(), $res["err"]);
	      $err=$res["err"];
	    }
	  }
	} else {
	  $res= $this->verifyConstraint($v->id);
	  if ($res["err"]!="") {
	    $info[$v->id]=array("id"=>$v->id,
				"pid"=>$v->fieldSet->id, 
				"sug"=>$res["sug"],
				"err"=>$res["err"]);
	    if ($stoptofirst) return sprintf("[%s] %s", $v->getLabel(), $res["err"]);
	    $err=$res["err"];
	  }
	}
      }
    }
    return $err;
  }
  /** return the first attribute of type 'file'
   * @return Attribute 
   */
  final public function GetFirstFileAttributes()
  {
    $t =  $this->GetFileAttributes();
    if (count($t) > 0) return current($t);
    return false;      
  }


  /**
   * Add a comment line in history document
   * note : modify is call automatically
   * @param string $comment the comment to add
   * @param string $level level of comment
   * @param string $code use when memorize notification
   * @param string $uid user identificator : by default its the current user
   */
  final public function AddComment($comment='',$level=HISTO_INFO,$code='',$uid='') {
    global $action;
    if ($this->id=="") return;

    $h=new DocHisto($this->dbaccess);

    $h->id=$this->id;
    $h->initid=$this->initid;
    if (!isUTF8($comment)) $comment=utf8_encode($comment);
    $h->comment=$comment;
    $h->date=date("d-m-Y H:i:s");
    if ($uid > 0) {
      $u=new User("",$uid);
      $h->uid=$u->id;
      $h->uname=sprintf("%s %s",$u->firstname,$u->lastname);      
    } else {
      $h->uname=sprintf("%s %s",$action->user->firstname,$action->user->lastname);
      $h->uid=$action->user->id;
    }
    $h->level=$level;
    $h->code=$code;

    $err=$h->Add();
    if ($level == HISTO_ERROR) {
        error_log(sprintf("document %s [%d] : %s",$this->title, $this->id, $comment));
    }
    return $err;
  }  

  /**
   * Add a log entry line in log document
   * 
   * @param string $comment the comment to add
   * @param string $level level of comment
   * @param string $code use when memorize notification
   * @param string $arg serialized object
   * @param string $uid user identificator : by default its the current user
   */
  final public function addLog($code='',$arg='',$comment='',$level='',$uid='') {
    global $action;
    if (($this->id=="") || ($this->doctype=='T')) return;

    include_once("FDL/Class.DocLog.php");
    $h=new DocLog($this->dbaccess);
    $h->id=$this->id;
    $h->initid=$this->initid;
    $h->title=$this->title;
    if (!isUTF8($comment)) $comment=utf8_encode($comment);
    $h->comment=$comment;

    if ($uid > 0) {
      $u=new User("",$uid);
      $h->uid=$u->id;
      $h->uname=sprintf("%s %s",$u->firstname,$u->lastname);      
    } else {
      $h->uname=sprintf("%s %s",$action->user->firstname,$action->user->lastname);
      $h->uid=$action->user->id;
    }
    $h->level=$level?$level:LOG_NOTIFY;
    $h->code=$code;
    if ($arg) $h->arg=serialize($arg);

    $err=$h->Add();
    return $err;
  }
  /**
   * Get history for the document
   * @param bool $all set true if want for all revision
   * 
   * @return array of different comment
   */
  public function getHisto($allrev=false,$code="",$limit=0) {    
    include_once("Class.QueryDb.php");
    $q=new QueryDb($this->dbaccess,"dochisto");
    if ($allrev) $q->AddQuery("initid=".$this->initid);
    else $q->AddQuery("id=".$this->id);
    if ($code) $q->addQuery(sprintf("code='%s'",pg_escape_string($code)));
    $q->order_by="date desc";
    $l=$q->Query(0,$limit,"TABLE");

    if (is_array($l))  return $l;
    return array();
  }
  /**
   * Add a application tag for the document
   * if it is already set no set twice
   * @param string $atg the tag to add
   */
  final public function addATag($tag) {
    if ($this->atags == "") {
      $this->atags =  $tag;
      $this->modify(true,array("atags"),true);
    } else {
      if (!$this->getATag($tag)) {
	$this->atags .= "\n$tag";
	$this->modify(true,array("atags"),true);
      }
    }    
  }

  /**
   * Return true if tag is present
   * 
   * @param string $tag the tag to search
   * @return bool
   */
  final public function getATag($tag) {
    if ($this->atags == "") return false;
    return preg_match("/\b$tag\b/", $this->atags);
  } 
  /**
   * Delete a application tag for the document
   * 
   * @param string $tag the tag to delete
   * @return void
   */
  final public function delATag($tag) {
    if ($this->atags == "") return "";
    $atags=preg_replace("/\b$tag\b/", "", $this->atags);
    $atags=str_replace("\n\n","\n",$atags);
    $atags=preg_replace("/\n$/m",'',$atags);
    if ($atags != $this->atags) {      
      $this->atags=$atags;
      $this->modify(true,array("atags"),true);
    }
  }

  /**
   * Add a user tag for the document
   * if it is already set no set twice
   * @param int $uid the system user identificator
   * @param string $tag the key tag 
   * @param string $datas a comment or a value for the tag
   * @param bool $allrevision set to false if attach a tag to a specific version
   */
  final public function addUTag($uid,$tag,$datas="",$allrevision=true) {
    if (! $this->initid) return "";
    if ($tag == "") return _("no user tag specified");
    $this->delUTag($uid,$tag,$allrevision);

    global $action;
    $h=new DocUTag($this->dbaccess);

    $h->id=$this->id;
    $h->initid=$this->initid;
    $h->fixed=($allrevision)?'false':'true';
    $h->date=date("d-m-Y H:i:s");
    if ($uid > 0) {
      $u=new User("",$uid);
      $h->uid=$u->id;
      $h->uname=sprintf("%s %s",$u->firstname,$u->lastname);      
    } 
    $h->fromuid=$action->user->id;
      
    $h->tag=$tag;
    $h->comment=$datas;

    $err=$h->Add();
    return $err;
  } 

  /**
   * Test if current user has the u tag specified
   * 
   * @param string $tag the tag to verify
   * @param bool $allrevision set to false to verify a tag to a specific version
   */
  final public function hasUTag($tag,$allrevision=true) {
    if (! $this->initid) return false;
    include_once("FDL/Class.DocUTag.php");
    $docid= ($allrevision)?$this->initid:$this->id;
    $utag=new DocUTag($this->dbaccess,array($docid,$this->userid,$tag));
    return $utag->isAffected();
  }

  /**
   * Get current user tag specified
   * 
   * @param string $tag the tag to verify
   * @param bool $allrevision set to false to get a tag to a specific version
   * @return DocUTag
   */
  final public function getUTag($tag,$allrevision=true) {
    if (! $this->initid) return "";
    
    
    
    include_once("FDL/Class.DocUTag.php");
    $q=new QueryDb($this->dbaccess,"docUTag");
    $q->addQuery("uid=".intval($this->userid));
    if ($tag) $q->addQuery("tag = '".pg_escape_string($tag)."'");
    if ($allrevision) $q->addQuery("initid = ".$this->initid);
    else $q->addQuery("id = ".$this->id);
    $q->order_by="id desc";
    $r=$q->Query(0,1);
    if ($q->nb==1) return $r[0];
    return false;
  }
  

  /**
   * Remove a user tag for the document
   * if it is already set no set twice
   * @param int $uid the system user identificator
   * @param string $tag the tag to add
   * @param bool $allrevision set to false to del a tag to a specific version
   */
  final public function delUTag($uid,$tag,$allrevision=true) { 
    if ($tag == "") return _("no user tag specified");
    include_once("FDL/Class.DocUTag.php");
    $err="";
    
    $docid= ($allrevision)?$this->initid:$this->id;
    if ($allrevision) {
      $err=$this->exec_query(sprintf("delete from docutag where initid=%d and tag='%s' and uid=%d",$this->initid,pg_escape_string($tag),$uid));
    } else {
      $err=$this->exec_query(sprintf("delete from docutag where id=%d and tag='%s' and uid=%d",$this->id,pg_escape_string($tag),$uid));
    }
    return $err;
  }
  /**
   * Remove all user tag for the document
   * 
   * @param int $uid the system user identificator
   */
  final public function delUTags($uid="") { 
    if ($tag == "") return _("no user tag specified");
    if (! $this->initid) return "";
    if (!$uid) $uid=$this->userid;
    include_once("FDL/Class.DocUTag.php");
    $q=new QueryDb($this->dbaccess,"docUTag");
    $q->Query(0,0,"TABLE",
	      sprintf("delete from docutag where initid=%d and uid=%d",$this->initid,$uid));
    

    return $err;
  }
  /**
   * Refresh all user tag for the document in case of revision
   * 
   */
  final public function refreshUTags() { 
    if (! $this->initid) return "";
    include_once("FDL/Class.DocUTag.php");
    $q=new QueryDb($this->dbaccess,"docUTag");
    $q->Query(0,0,"TABLE",
	      sprintf("update docutag set id=%d where initid=%d and (not fixed)",$this->id,$this->initid));

    return $err;
  } 
  /**
   * search all user tag for the document 
   * @param string $tag tag to search
   * @param boolean $allrevision view tags for all revision
   * @param boolean $allusers view tags of all users
   * @return array user tags key=>value
   */
  final public function searchUTags($tag="",$allrevision=true, $allusers=false) { 
    if (! $this->initid) return "";
    include_once("FDL/Class.DocUTag.php");
    $q=new QueryDb($this->dbaccess,"docUTag");
    if (! $allusers) $q->addQuery("uid=".intval($this->userid));
    if ($tag) $q->addQuery("tag = '".pg_escape_string($tag)."'");
    if ($allrevision) $q->addQuery("initid = ".$this->initid);
    else $q->addQuery("id = ".$this->id);
    $r=$q->Query(0,0,"TABLE");
    if ($q->nb==0) $r=array();
    return $r;
  }

  /**
   * get ask for current users
   * @param bool $control if false all associated askes else only askes available for current user
   * @return array
   */
  public function getWasks($control=true) {
    $t=array();
    if ($this->wid >0 && $this->locked==-1 && $this->doctype !='Z' && $this->state) {
      $wdoc=new_doc($this->dbaccess,$this->wid);
      if ($wdoc->isAlive()) {
	$wdoc->set($this);
	$waskids=$wdoc->getDocumentWasks($this->state,$control);
	foreach ($waskids as $k=>$waskid) {
	  $wask=new_doc($this->dbaccess,$waskid);
	  if ($wask->isAlive()) {
	    $ut=$this->getUTag("ASK_".$wask->id,false);
	    if ($ut) $answer=$ut->comment;
	    else $answer="";
	    $t[]=array("waskid"=>$wask->id,
		       "ask"=>$wask->getValue("was_ask"),
		       "key"=>$answer,
		       "label"=>$wask->getAskLabel($answer));
	  }
	}
      }
    }
    return $t;
  }

  /**
   * set a answer for a document for a ask (for current user)
   * @param int $waskid the identificator of wask
   */
  function setWaskAnswer($waskid,$answer) {
    $err=_("setWaskAnswer::invalid parameters");
    $waskid=intval($waskid);
    if ($waskid && $answer) {
      if (is_array($answer)) $answer=$this->_array2val($answer);
      $err=$this->addUTag($this->userid,"ASK_".intval($waskid),$answer,false);
      return $err;
    }
    return $err;
  }

  /**
   * all ask are answer ?
   * @return bool true if all ask are answer or when has no askes
   */
  function askIsCompleted() {
    $ans=$this->getWasks();
    foreach ($ans as $an) {
      if (! $an["key"]) return false;
    }
    return true;
  }

  /**
   * return the latest document id in history of a document which has ask
   * @return int the identificator
   */
  function getLatestIdWithAsk() {
    if (! $this->wid) return false;
    $ldoc = $this->GetRevisions("TABLE"); 
    $wdoc=new_doc($this->dbaccess,$this->wid);
    if ($wdoc->isAlive()) {
      $wdoc->set($this);
      foreach($ldoc as $k=>$v) {
	$aask=$wdoc->attrPrefix."_ASKID".($v["state"]);
	if ($v["locked"]==-1 && $wdoc->getValue($aask)) {
	  if ($wdoc->getValue($aask)) return $v["id"];
	}
      }
    }
    return false;
  }
  /**
   * verify if document is really fixed
   * @return bool
   */
  function isFixed() {
    return isFixedDoc($this->dbaccess, $this->id);
  }

  /**
   * Create a new revision of a document
   * the current document is revised (became a fixed document)
   * a new revision is created
   * @param string $comment the comment of the revision
   * @return string error text (empty if no error)
   */
  final public function addRevision($comment='') {
    // first control
    if ($this->locked == -1) return _("document already revised");
    if ($this->isFixed() ) {
      $err=_("document already revised");
      $this->Addcomment($err,HISTO_ERROR,"REVERROR");
      return $err;
    } 
    if (! $this->withoutControl) {
      $err = $this->Control("edit");
      if ($err != "") return ($err); 
    }
    $fdoc = $this->getFamDoc();
   
    if ($fdoc->schar == "S") return sprintf(_("the document of %s family cannot be revised"),$fdoc->title);
    $locked=$this->locked;
    $allocated=$this->allocated;
    $postitid = $this->postitid; // transfert post-it to latest revision
    $forumid = $this->forumid; // transfert forum to latest revision

    $this->locked = -1; // the file is archived
    $this->lmodify = 'N'; // not locally modified
    $this->allocated = 0; // cannot allocated fixed document
    $this->owner = $this->userid; // rev user 
    $this->postitid=0;
    $this->forumid=0;
    $date = gettimeofday();
    $this->revdate = $date['sec']; // change rev date
    $this->exec_query("begin;");
    if ($comment != '') $this->Addcomment($comment,HISTO_MESSAGE,"REVISION");
    $err=$this->modify();
    if ($err != "") {
      $this->exec_query("rollback;");
      $this->select($this->id); // reset db values
      return $err;
    }

    // double control
    if (! $this->isFixed()) {
      $err=sprintf("track error revision [%s]", pg_last_error($this->dbid));
      $this->Addcomment($err,HISTO_ERROR,"REVERROR");
      $this->exec_query("commit;");
      return $err;
    }

    $fa=$this->GetFileAttributes(true); // copy cached values
    $ca=array();
    foreach ($fa as $k=>$v) {
      $ca[]=$v->id."_txt";      
    }
    $this->AffectColumn($ca);
    foreach ($ca as $a) {
      if ($this->$a != "") $this->fields[$a]=$a;
    }
    //$listvalue = $this->GetValues(); // save copy of values

    // duplicate values
    $olddocid = $this->id;
    $this->id="";

    if ($locked > 0) $this->locked = $locked; // report the lock
    else $this->locked = 0;
    $this->allocated = $allocated; // report the allocate
    $this->comment = ""; // change comment
    $this->revision = $this->revision+1;
    $this->postitid=$postitid;
    $this->forumid=$forumid;
   
    $err=$this->Add();
    if ($err != "") {
      // restore last revision      
      $this->exec_query("rollback;");
      $this->select($olddocid); // reset db values
      return $err;
    }
    
    $this->exec_query("commit;");
    $this->refresh(); // to recompute possible dynamic profil variable
    if ($this->dprofid > 0) $this->setProfil($this->dprofid); // recompute profil if needed
    $err=$this->modify(); // need to applicate SQL triggers
       
    $this->UpdateVaultIndex();
    $this->refreshUTags();
    if ($err=="") {
      $this->addLog("revision",array("id"=>$this->id,"initid"=>$this->initid,"revision"=>$this->revision,"title"=>$this->title,"fromid"=>$this->fromid,"fromname"=>$this->fromname));
      // max revision
      $fdoc=$this->getFamDoc();
      $maxrev=intval($fdoc->maxrev);
      if ($maxrev > 0) {
	if ($this->revision > $maxrev) {
	  // need delete first revision
	  $revs=$this->getRevisions("TABLE","ALL");
	  for ($i=$maxrev; $i < count($revs); $i++) {
	    $d=getDocObject($this->dbaccess,$revs[$i]);
	    if ($d) $d->ReallyDelete(true);
	  }
	}
      }
    }

    return $err;
    
  }
  /**
   * Set a free state to the document
   * for the document without workflow
   * a new revision is created
   * @param string $newstateid the document id of the state (FREESTATE family)
   * @param string $comment the comment of the state change
   * @param bool $revision if false no revision are made
   * @return string error text (empty if no error)
   */
  final public function changeFreeState($newstateid,$comment='',$revision=true) {
    if ($this->wid > 0) return sprintf(_("cannot set free state in workflow controlled document %s"),$this->title);
    if ($this->wid == -1) return sprintf(_("cannot set free state for document %s: workflow not allowed"),$this->title);
    if (! $this->isRevisable()) return sprintf(_("cannot set free state for document %s: document cannot be revised"),$this->title);
    if ($newstateid==0) {
      $this->state="";
      $err=$this->modify(false,array("state")); 
      if ($err == "") { 
	$comment=sprintf(_("remove state : %s"),$comment);
	if ($revision) $err=$this->addRevision($comment);
	else $err=$this->addComment($comment);
      }
    } else {

      $state=new_doc($this->dbaccess,$newstateid);
      if (! $state->isAlive()) return sprintf(_("invalid freestate document %s"),$newstateid);
      if ($state->fromid != 39) return sprintf(_("not a freestate document %s"),$state->title);

      $this->state=$state->id;
      $err=$this->modify(false,array("state")); 
      if ($err == "") { 
	$comment=sprintf(_("change state to %s : %s"),$state->title,$comment);
	if ($revision) $err=$this->addRevision($comment);
	else $err=$this->addComment($comment);
      }
    }
    return $err;
  } 
  /**
   * set state for a document controled by a workflow
   * 
   * @param string $newstate the new state
   * @param string $comment optionnal comment to set in history
   * @param bool $force is true when it is the second passage (without interactivity)
   * @param bool $withcontrol set to false if you want to not verify control permission ot transition
   * @param bool $wm1 set to false if you want to not apply m1 methods
   * @param bool $wm2 set to false if you want to not apply m2 methods
   * @param bool $need set to false if you want to not verify needed attribute are set
   * @return string the state - empty if no state
   */
  final public function setState($newstate,$comment='',$force=false,$withcontrol=true,$wm1=true,$wm2=true,$wneed=true) {
    if ($newstate=="") return _("no state specified");
    if (! $this->wid) return _("document is not controlled by a workflow");
    $wdoc=new_doc($this->dbaccess,$this->wid);
    if (! $wdoc->isAlive()) return _("assigned workflow is not alive");
    try {
      $wdoc->Set($this); 
      $err=$wdoc->ChangeState($newstate,$comment,$force,$withcontrol,$wm1,$wm2,$wneed);
    } catch (Exception $e) {
      $err=sprintf(_("workflow associated %s [%d] is corrupted"),$wdoc->title,$wdoc->id);
    }
    return $err;
  }
  /**
   * return the state of a document
   * if document has workflow it is the key
   * if document state is a free state it is the name of the state
   * 
   * @return string the state - empty if no state
   */
  public function getState() {
    if ($this->wid > 0) return $this->state;
    if (is_numeric($this->state) && ($this->state>0) ) {
      $state=$this->getTitle($this->state);
      return $state;
    }

    return $this->state;
  }
  /**
   * return the color associated for the state of a document
   * if document has workflow : the color state
   * if document state is a free state the color
   * 
   * @return string the color of the state - empty if no state
   */
  public function getStateColor($def="") {
    if ($this->wid > 0) {      
      $wdoc = new_Doc($this->dbaccess,$this->wid);
      if ($wdoc->isAffected()) return $wdoc->getColor($this->state,$def);
    } else {
      if (is_numeric($this->state) && ($this->state>0) ) {
	$state=$this->getDocValue($this->state,"frst_color",$def);
	return $state;
      }
    }
    return $def;
  }  
  /**
   * return the action associated for the state of a document
   * if document has workflow : the action label description
   * if document state is a free state description
   * 
   * @return string the color of the state - empty if no state
   */
  final public function getStateActivity($def="") {
    if ($this->wid > 0) {      
      $wdoc = new_Doc($this->dbaccess,$this->wid);
      if ($wdoc->isAffected()) return $wdoc->getActivity($this->state,$def);
    } else {
      if (is_numeric($this->state) && ($this->state>0) ) {
	$stateact=$this->getDocValue($this->state,"frst_desc",$def);
	return $stateact;
      }
    }
    return $def;
  }
  /**
   * return the copy of the document
   * the copy is created to the database
   * the profil of the copy is the default profil according to his family
   * the copy is not locked and if it is related to a workflow, his state is the first state
   * @param bool $temporary if true the document create it as temporary document
   * @param bool $control if false don't control acl create (generaly use when temporary is true)
   * @param bool $linkfld if true and document is a folder then document included in folder are also inserted in the copy (are not duplicated) just linked
   * @param bool $copyfile if true duplicate files of the document 
   * @return Doc in case of error return a string that indicate the error
   */
  final public function Copy($temporary=false,$control=true,$linkfld=false,$copyfile=false) {

    $copy=createDoc($this->dbaccess, $this->fromid, $control);
    if (! is_object($copy)) return false;
    
    $copy->transfertValuesFrom($this);
    
    $copy->id = "";
    $copy->initid = "";
    $copy->revision = "0";
    $copy->locked = "0";
    $copy->allocated = "0";
    $copy->state = "";
    $copy->comment = "";
    $copy->icon = $this->icon;;

    if ($temporary) {
      $copy->doctype = "T";
      $copy->profid=0;
      $copy->dprofid=0;
    } else {
      $cdoc= $this->getFamDoc();
      $copy->setProfil($cdoc->cprofid);
    }

    $err = $copy->PreCopy($this);
    if ($err != "") return $err;

    $err = $copy->Add();
    if ($err != "") return $err;
    $copy->addComment(sprintf(_("copy from document #%d -%s-"),$this->id, $this->title));

    if ($copyfile) $copy->duplicateFiles();

    $copy->PostCopy($this);
    if ($err != "") AddWarningMsg($err);

    $copy->Modify();
    if ($linkfld && method_exists($copy,"insertFolder")) {
      $copy->insertFolder($this->initid);
    }
    
    return $copy;
  }

  function PreCopy(&$copyfrom) {
    // to be defined in child class
    return "";
  }

  function PostCopy(&$copyfrom) {
    // to be defined in child class
    return "";
  }


  final public function translate($docid, $translate) {
    $doc = new_Doc($this->dbaccess, $docid);
    if ($doc->isAlive()) {      
      foreach($translate as $afrom=>$ato) {
	$this->setValue($ato, $doc->getValue($afrom));
      }
    }
  }
  
  /**
   * Put document in an archive
   * @param _ARCHIVING $archive the archive document
   */
  final public function archive(&$archive) {
    $err="";
    if ($this->archiveid==0) {
      if ($this->doctype=="C") $err=sprintf("families cannot be archieved");
      elseif (! $this->withoutControl) $err=$this->control("edit");
      if ($err=="") {
	$this->locked = 0;
	$this->archiveid = $archive->id;
	$this->dprofid=($this->dprofid>0)?(- $this->dprofid):(- abs($this->profid));
	$archprof=$archive->getValue("arc_profil");
	$this->profid=$archprof;
	$err=$this->modify(true,array("locked","archiveid", "dprofid", "profid"),true);
	if (! $err) {
	  $this->addComment(sprintf(_("Archiving into %s"),$archive->getTitle()),HISTO_MESSAGE,"ARCHIVE");
	  $this->addLog('archive',$archive->id,sprintf(_("Archiving into %s"),$archive->getTitle()));
	  $err=$this->exec_query(sprintf("update doc%d set archiveid=%d, dprofid=-abs(profid), profid=%d where initid=%d and locked = -1",$this->fromid, $archive->id,$archprof,$this->initid));
	}
      }
    } else $err=sprintf("document is already archived");

    return $err;
  }
  /**
   * Delete document in an archive
   * @param _ARCHIVING $archive the archive document
   */
  final public function unArchive(&$archive) {
    $err="";
      
    if ($this->archiveid==$archive->id) {
      if (! $this->withoutControl) $err=$this->control("edit");
      if ($err=="") {
	$this->locked = 0;
	$this->archiveid = ""; // set to null
	$restoreprofil=abs($this->dprofid);
	$this->dprofid = 0;
	$err=$this->setProfil($restoreprofil);
	if (! $err) $err=$this->modify(true,array("locked","archiveid","dprofid","profid"),true);
	if (! $err) {
	  $this->addComment(sprintf(_("Unarchiving from %s"),$archive->getTitle()),HISTO_MESSAGE,"UNARCHIVE");
	  $this->addLog('unarchive',$archive->id,sprintf(_("Unarchiving from %s"),$archive->getTitle()));
	  $err=$this->exec_query(sprintf("update doc%d set archiveid=null, profid=abs(dprofid), dprofid=null where initid=%d and locked = -1",$this->fromid,$this->initid));
	}
      }
    } else $err=sprintf("document not archived");

    return $err;
  }
  /** 
   * lock document
   * 
   * the auto lock is unlocked when the user discard edition or when he's modify document
   * @param bool $auto if true it is a automatic lock due to an edition (@see editcard()}
   * @param int $userid if set lock with another userid, the edit control will be disabled
   * 
   * @return string error message, if no error empty string, if message
   * @see Doc::CanLockFile()
   * @see Doc::unlock()
   */
  final public function lock($auto=false,$userid="") {

    $err="";
    if ($userid=="") {
      $err=$this->CanLockFile();
      if ($err != "") return $err;
      $userid=$this->userid;
    } else {
      $this->disableEditControl();
    }
    

    // test if is not already locked
    if ($auto) {
      if (($userid != 1) && ($this->locked == 0)) {
	$this->locked = -$userid; // in case of auto lock the locked id is negative
	$err=$this->modify(false,array("locked"));
	if (! $err) $this->addLog('lock');
      }
    } else { 
      if ($this->locked != $userid) {
	$this->locked = $userid;     
	$err=$this->modify(false,array("locked"));
	if (! $err) $this->addLog('lock');
      }
    }
    $this->enableEditControl();
    
    return $err;
  }

  /** 
   * unlock document
   * 
   * the automatic unlock is done only if the lock has been set automatically also
   * the explicit unlock, unlock in all case (if CanUnLockFile)
   * @param bool $auto if true it is a automatic unlock 
   * @param bool $force if true no control oif can unlock
   * 
   * @return string error message, if no error empty string
   * @see Doc::CanUnLockFile()
   * @see Doc::lock()
   */
  final public function unLock($auto=false,$force=false) {    
    if ($this->locked == 0) return "";
    if (!$force) $err=$this->CanUnLockFile();
    if ($err != "") return $err;
      
    if ($auto) {
      if ($this->locked < -1) {
	$this->locked = "0";      
	$this->modify(false,array("locked"));
	if (! $err) $this->addLog('unlock');
      }
    } else {
      if ($this->locked != -1) {
	$this->locked = "0";      
	$this->modify(false,array("locked"));
	if (! $err) $this->addLog('unlock');
      }
    }
    
    return "";
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
  final public function allocate($userid,$comment="",$revision=false,$autolock=true) {
    
    $err="";
    $err=$this->canEdit();
    if ($err != "") $err=_("Affectation aborded")."\n".$err;
    if ($err == "") {
      $u=new User("",$userid);
      if ($u->isAffected()) {
	if ($err != "") $err=_("Affectation aborded")."\n".$err;
	// no test if allocated can edit document
	//$err=$this->ControlUser($u->id,"edit");
	//if ($err != "") $err=sprintf(_("Affectation aborded\n%s for user %s %s"),$err,$u->firstname,$u->lastname);
	if ($err == "") {
	  $this->addComment(sprintf(_("Affected to %s %s"),$u->firstname,$u->lastname));
	  if ($comment) {
	    if ($revision) {
	      $this->addRevision(sprintf(_("Affected for %s"),$comment));
	    } else {
	      $this->addComment(sprintf(_("Affected for %s"),$comment));
	    }
	  }
	  $this->addLog('allocate',array("allocated"=>array("id"=>$u->id,"firstname"=>$u->firstname,"lastname"=>$u->lastname)));

	  $this->delUTag("AFFECTED");
	  $this->addUTag($userid,"AFFECTED",$comment);	  
	  if ($autolock) $err=$this->lock(false,$userid);
	}
      } else {
	$err=_("Affectation aborded : user not know");
      }
    }
    if ($err=="") {
      $this->allocated=$userid;
      $this->modify(true,array("allocated"),true);      
    }

    return $err;
  } 

  /** 
   * unallocate document
   * 
   * unaffect a document to a user
   * only the allocated user can unallocate and also users which has unlock acl
   * @param bool $revision if false no revision are made
   * 
   * @return string error message, if no error empty string, if message
   */
  final public function unallocate($comment="",$revision=true) {
    if ($this->allocated==0) return "";
    $err="";
    $err=$this->canEdit();        
    if ($err == "") {
      if ((! $this->withoutControl)&& ($this->userid != $this->allocated)) $err=$this->control("unlock");
    }

    if ($err == "") {
      $u=new User("",$this->allocated);
      if ($u->isAffected()) {	
	$err=$this->unlock();
	if ($err == "") {
	  $this->delUTag("AFFECTED");
	  if ($revision) $this->addRevision(sprintf(_("Unallocated of %s %s : %s"),$u->firstname,$u->lastname,$comment));
	  else $this->addComment(sprintf(_("Unallocated of %s %s: %s"),$u->firstname,$u->lastname,$comment));
	}
      } else {
	$err=_("user not know");
      }
    }
    if ($err=="") {
      $this->allocated=0;
      $this->modify(true,array("allocated"),true);   
      $this->addLog('unallocate');
    }

    if ($err != "") $err=_("Unallocate aborded")."\n".$err;
    return $err;
  }
  /**
   * return icon url
   * if no icon found return doc.gif
   * @return string icon url
   */
  final public function getIcon($idicon="") {

    global $action;
    if ($idicon=="") $idicon=$this->icon;
    if ($idicon != "") {
    
      if (preg_match(PREGEXPFILE, $idicon, $reg)) {    
	$efile="FDL/geticon.php?vaultid=".$reg[2]."&mimetype=".$reg[1];
      } else {
	$efile=$action->parent->GetImageUrl($idicon,false);
      }
      return $efile;

    } else {
      if ($this->fromid == 0) {

	return  $action->GetImageUrl("doc.gif");
      }
      //$fdoc = new_Doc(newDoc($this->dbaccess, $this->fromid);
    
      return  $action->GetImageUrl("doc.gif");
      // don't recursivity to increase speed
      //    return $fdoc->geticon();
    }

  }


  // change icon for a class or a simple doc
  final public function changeIcon($icon) {

    if ($this->doctype == "C") { //  a class
      $fromid=$this->initid;	
      if ($this->icon!="") {

	// need disabled triggers to increase speed
	$qt[]="begin";
	$qt[]="ALTER TABLE doc$fromid DISABLE TRIGGER ALL";
	$qt[]="update doc$fromid set icon='$icon' where (fromid=".$fromid.") AND (doctype != 'C') and ((icon='".$this->icon."') or (icon is null))";
	$qt[]="ALTER TABLE doc$fromid ENABLE TRIGGER ALL";
	$qt[]="update docread set icon='$icon' where (fromid=".$fromid.") AND (doctype != 'C') and ((icon='".$this->icon."') or (icon is null))";
	$qt[]="commit";

	$this->exec_query(implode(";",$qt));  
      } else {
	$q="update doc$fromid set icon='$icon' where (fromid=".$fromid.") AND (doctype != 'C') and (icon is null)";	
	$this->exec_query($q);
      }
    } 
    //    $this->title = AddSlashes($this->title);
    $this->icon = $icon;
    $this->Modify();
  }
  /**
   * declare a dependance between several attributes
   * @param string $in attributes id use for compute $out attributes separates by commas
   * @param string $out attributes id calculated by $in attributes separates by commas
   */
  final public function AddParamRefresh($in,$out) {
    // to know which attribut must be disabled in edit mode
    $tin=explode(",",strtolower($in));
    $tout=explode(",",strtolower($out));
    $this->paramRefresh["$in:$out"]=array("in"=>$tin,
					  "out"=>$tout);
    
  }

  /** 
   * compute new visibility with depended attributes
   * @return array of visibilities computed with dependance between attributes
   */
  public function getRefreshVisibility() {
    $tv=array();
    foreach ($this->attributes->attr as $k=>$v) {
      $tv[$v->id]=$v->mvisibility;      
    }
    foreach ($this->paramRefresh as $k=>$v) {
      reset($v["in"]);
      $val=true;
      while ($val && (list($ka, $va) = each($v["in"]))) {
	$val=$this->getValue($va);
      }
      if ($val) {
	foreach ($v["out"] as $oa) {
	  if (($tv[$oa]=="W") || ($tv[$oa]=="O"))
	    $tv[$oa]="S";
	}
      }
    }
        
    return $tv;
  }

  /**
   * Special Refresh
   * to define in child classes
   */
  function SpecRefresh() {}  
  /**
   * Special Refresh Generated automatically
   * is defined in generated child classes
   */
  function SpecRefreshGen($onlyspec=false) {}

  /**
   * recompute all calculated attribut
   * and save the document in database if changes occurred
   */
  final public function Refresh() {
    if ($this->locked == -1) return; // no refresh revised document
    if (($this->doctype == 'C') || ($this->doctype == 'Z') ) return; // no refresh for family  and zombie document   
    $changed=$this->hasChanged;
    if (!$changed)  $this->disableEditControl();// disabled control just to refresh    
    $err=$this->SpecRefresh();
    // if ($this->id == 0) return; // no refresh for no created document
    $err.=$this->SpecRefreshGen();
    if ($this->hasChanged)  {
      $err.=$this->modify(); // refresh title
    }
    if (!$changed)  $this->enableEditControl();
    return $err;
	
  }
  /**
   * Recompute file name in concordance with rn option
   * 
   */
  function refreshRn() {
    $err="";
    $fa=$this->GetFileAttributes();    
    foreach ($fa as $aid=>$oa) {
      $rn=$oa->getOption("rn");
      if ($rn) {
	if ($oa->inArray()) {
	  $t=$this->getTvalue($oa->id);
	  foreach ($t as $k=>$v) {
	    $cfname=$this->vault_filename($oa->id,false,$k);
	    if ($cfname) {
	      $fname=$this->applyMethod($rn,"",$k,array($cfname));
	      if ($fname != $cfname) {
		$err.=$this->renameFile($oa->id,$fname,$k);
	      }
	    }
	  }
	} else {
	  $cfname=$this->vault_filename($oa->id);
	  if ($cfname) {
	    $fname=$this->applyMethod($rn,"",-1,array($cfname));
	    if ($fname != $cfname) {
	      $err.=$this->renameFile($oa->id,$fname);
	    }
	  }
	}
      }
    }
    return $err;
  }
  
  final public function urlWhatEncode( $link, $k=-1) {
    global $action;
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $urllink="";
    $mi=strlen($link);
    for ($i=0; $i < $mi; $i++) {
      switch ($link[$i]) {
      
      case '%' :
	$i++;
	if ($link[$i] == "%") { 
	  $urllink.= "%"; // %% is %
	} else {
	  if ($link[$i+1] == "%") { 
	    // special link
	    
	    switch ($link[$i]) {
	    case "B": // baseurl	  
	      $urllink.=$action->GetParam("CORE_BASEURL");	      
	      break;

	    case "S": // standurl	  
	      $urllink.=$action->GetParam("CORE_STANDURL");	      
	      break;

	    case "I": // id	  
	      $urllink.=$this->id;	      
	      break;

	    case "T": // title  
	      $urllink.=$this->title;	      
	      break;	    	    

	    default:
	      
	      break;
	    }
	    $i++; // skip end '%'
	  } else {
	  
	    $sattrid="";
	    while (($i < $mi) && ($link[$i] != "%" )) {
	      $sattrid.= $link[$i];
	      $i++;
	    }
	    $oa=$this->GetAttribute($sattrid);
	    if (($k >= 0)&&($oa && $oa->repeat)) {
	      $tval= $this->GetTValue($sattrid);
	      $ovalue = chop($tval[$k]);
	    } else {
	      $ovalue = $this->GetValue($sattrid);
	    }
	    if ($ovalue == "") return false;
	    //$urllink.=urlencode($ovalue); // encode because url values must be encoded
	    //$urllink.=urlencode($ovalue); // not encode cause url will became invalid
	    if ($ovalue[0]=='[') $urllink.=urlencode($ovalue);
	    else if (strstr($ovalue,"\n")) $urllink.=str_replace("\n",'\n',$ovalue);
	    else $urllink.=($ovalue); // not encode cause url will became invalid	  	  
	  }
	}
	break;

      case '{' :
	$i++;

	  
	$sattrid="";
	while ($link[$i] != '}' ) {
	  $sattrid.= $link[$i];
	  $i++;
	}
	//	  print "attr=$sattrid";
	  
	$ovalue = $action->GetParam($sattrid);
	$urllink.=$ovalue;
	  
	  
	
	break;

      default:
	$urllink.=$link[$i];
      }
    }
    $urllink=$this->urlWhatEncodeSpec($urllink); // complete in special case families
    
    return (chop($urllink));
    
  }
  
  /**
   * virtual method must be use in child families if needed complete url
   */
  public function urlWhatEncodeSpec($l) {return $l;}

  public static function _val2array($v) {
    if ($v==="" || $v===null) return array();
    return explode("\n", str_replace("\r","",$v));
  }
  
  public static function _array2val($v,$br='<BR>') {    
    $v=str_replace("\n",$br,$v);
    if (count($v) == 0) return "";
    return implode("\n", $v);
  }
  /**
   * return an url
   * @return string the url anchor
   */
  public function getRssLink() {
    global $action;
    return sprintf("%s?app=FREEDOM&action=FREEDOM_RSS&authtype=open&privateid=%s&id=%s",
		   $action->getParam("CORE_OPENURL",$action->getParam("CORE_EXTERNURL")),
		   $action->user->getUserToken(),
		   $this->id);
  } 
  /**
   * return an url for file attribute
   * @param string $attrid attribute identificator
   * @param int $index set to row rank if it is in array else use -1
   * @param bool $cache set to true if file may be persistent in client cache
   * @param bool $inline set to true if file must be displayed in web browser
   * @return string the url anchor
   */
  public function getFileLink($attrid,$index=-1,$cache=false,$inline=false) {
    global $action;
    if ($index >=0) $avalue=$this->getTValue($attrid,"",$index);
    else $avalue=$this->getValue($attrid);
    if (preg_match(PREGEXPFILE, $avalue, $reg)) {
      $vid=$reg[2];
      if (true) {
          // will be rewrited by apache rules
           return sprintf("file/%s/%d/%s/%s/%s?cache=%s&inline=%s",
		     $this->id, $vid,
		     $attrid,
		     $index,
		     rawurlencode($reg[3]),
		     $cache?"yes":"no",
		     $inline?"yes":"no");
      }
      else {
      return sprintf("%s?app=FDL&action=EXPORTFILE&cache=%s&inline=%s&vid=%s&docid=%s&attrid=%s&index=%d",
		     "",
		     $cache?"yes":"no",
		     $inline?"yes":"no",
		     $vid,
		     $this->id,
		     $attrid,
		     $index);
      }
    }
  }
  /**
   * return an html anchor to a document
   * @param int $id identificator of document
   * @param string $target window target
   * @param bool $htmllink must be true else return nothing
   * @param string $title should we override default title
   * @param bool $js should we add a javascript contextual menu
   * @param string $docrev style of link (default:latest, other values: fixed or state(xxx))
   * @return string the html anchor
   */
  final public function getDocAnchor($id,$target="_self",$htmllink=true,$title=false,$js=true,$docrev="latest") {
    $a="";
    $latest=($docrev=="latest" || $docrev=="");
    if ($htmllink) {
    	
      if (! $title) $title=$this->getHTMLTitle(strtok($id,'#'),'',$latest);
      if ($title == "") {
	$a="<a>".sprintf(_("unknown document id %s"),$id)."</a>";
      } else {
	$ul=getParam("CORE_STANDURL");
	if ($target=="mail") {
	  $ul=GetParam("CORE_EXTERNURL")."?";
	}
	if ($target=="ext") {
                  
	  //$ec=getSessionValue("ext:targetRelation");
	  $jslatest=($latest)?'true':'false';
	  $ec=getHttpVars("ext:targetRelation",'Ext.fdl.Document.prototype.publish("opendocument",null,%V%,"view",{latest:'.$jslatest.'})');
	  if ($ec)  {
	    if (! is_numeric($id)) $id=getIdFromName($this->dbaccess,$id);
	    else if ($latest) {
	    	$lid=getLatestDocId($this->dbaccess,$id);
	    	if ($lid) $id=$lid;
	    }
	    $ec=str_replace("%V%",$id,$ec);
	    $ecu=str_replace("'",'"',$ec);
        
	    $a="<a  onclick='parent.$ecu'>$title</a>";
	  } else {
	    if ($docrev=="latest" || $docrev=="" || !$docrev)
	      $ul.="&latest=Y";
	    elseif ($docrev != "fixed") {
	      // validate that docrev looks like state(xxx)
	      if (preg_match("/^state\(([a-zA-Z0-9_:-]+)\)/", $docrev, $matches)) {
		$ul.="&state=".$matches[1];
	      }
	    }
            $ul.="&app=FDL&action=VIEWEXTDOC&id=$id";
	    $a="<a href=\"$ul\">$title</a>";
	  }
                   
	} else {
	  if ($docrev=="latest" || $docrev=="" || !$docrev)
	    $ul.="&latest=Y";
	  elseif ($docrev != "fixed") {
	    // validate that docrev looks like state(xxx)
	    if (preg_match("/^state\(([a-zA-Z0-9_:-]+)\)/", $docrev, $matches)) {
	      $ul.="&state=".$matches[1];
	    }
	  }
          $ul.="&app=FDL&action=FDL_CARD&id=$id";
	  if ($js) $ajs="oncontextmenu=\"popdoc(event,'$ul');return false;\"" ;
	  else $ajs="";

	  $a="<a $ajs target=\"$target\" href=\"$ul\">$title</a>";
	}
      }

    } else {
      if (! $title) $a=$this->getHTMLTitle($id,'',$latest);
      else $a=$title;
    }
    return $a;
  }
  final private function rowattrReplace($s,$index) {
    if (substr($s,0,2)=="L_") return "[$s]";    
    if (substr($s,0,2)=="V_") {
      $sl=substr(strtolower($s),2);
      $vis=$this->getAttribute($sl)->mvisibility;

      if (($vis=="H")||($vis=="I")||($vis=="O")) $v="";      
      else $v=$this->GetHtmlAttrValue($sl,"_self",2,$index);
    } else {
      $sl=strtolower($s);
      if (! isset($this->$sl)) return "[$s]";
      $v=$this->getTValue($sl,"",$index);
    }
    return $v;
  }

  final public function getHtmlValue($oattr, $value, $target="_self",$htmllink=true, $index=-1,$entities=true,$abstract=false) {
    global $action;

    $aformat=$oattr->format;
    $atype=$oattr->type;

    if (($oattr->repeat)&&($index <= 0)){
      $tvalues = explode("\n",$value);
    } else {
      $tvalues[$index]=$value;
    }
    $idocfamid=$oattr->format;

    $attrid=$oattr->id;
    foreach($tvalues as $kvalue=>$avalue) {
      $htmlval="";

      switch ($atype) {
      case "idoc":
	$aformat="";
	$value=$avalue;
	if($value!=""){
	  // printf("la ");
	  $temp=base64_decode($value);
	  $entete="<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\" ?>";
	  $xml=$entete;
	  $xml.=$temp;
	  $title=recup_argument_from_xml($xml,"title");//in freedom_util.php
	}
	$attrid=$attrid.$index;
	$htmlval="<form style=\"display:inline\"><INPUT id=\"_" .$attrid."\" TYPE=\"hidden\"  name=\"_".$attrid."\" value=\"".$value." \">";
	$htmlval.="<a onclick=\"subwindow(400,400,'_$attrid','');viewidoc('_$attrid','$idocfamid')\" ";
	$htmlval.="oncontextmenu=\"viewidoc_in_popdoc(event,'$attrid','_$attrid','$idocfamid');return false\">$title</a>";
	$htmlval.="</form>";
	break;

  				 
      case "image":
	if ($target=="mail") {
	  $htmlval="cid:".$oattr->id;
	  if ($index >= 0) $htmlval.="+$index";
	}   if ($target=="te") {
	  $htmlval="file://".$this->vault_filename($oattr->id,true,$kvalue);
	}  else {
	  $vid="";
	  if (preg_match(PREGEXPFILE, $avalue, $reg)) {
	    $vid=$reg[2];

	    if (($oattr->repeat)&&($index <= 0))   $idx=$kvalue;
	    else $idx=$index;
	    $inline=$oattr->getOption("inline");
	    if ($inline=="yes") $opt="&inline=yes";
	    else $opt="";
	    $htmlval=$action->GetParam("CORE_BASEURL").
	      "app=FDL"."&action=EXPORTFILE$opt&cache=no&vid=$vid&docid=".$this->id."&attrid=".$oattr->id."&index=$idx"; // upload name
  						 
	  } else {
	    $htmlval=$action->GetImageUrl($avalue);
	  }
	}
	break;
      case "file":
	$vid="";
        $info=false;
	if (preg_match(PREGEXPFILE, $avalue, $reg)) {
	  // reg[1] is mime type
	  $vid=$reg[2];
	  $mime=$reg[1];
	  include_once("FDL/Lib.Dir.php");
	  $vf = newFreeVaultFile($this->dbaccess);
	  if ($vf->Show ($reg[2], $info) == "") $fname = $info->name;
	  else $htmlval=_("vault file error");
	} else $htmlval=_("no filename");


	if ($target=="mail") {
	  $htmlval="<a target=\"_blank\" href=\"";
	  $htmlval.="cid:".$oattr->id;
	  if ($index >= 0) $htmlval.="+$index";
	  $htmlval.=  "\">".$fname."</a>";
	} else {
	  if ($info) {
	    if ($info->teng_state < 0 || $info->teng_state > 1) {
	        $htmlval="";
	        include_once("WHAT/Class.TEClient.php");
	        switch (intval($info->teng_state)) {
	            case TransformationEngine::error_convert: // convert fail
                        $textval=_("file conversion failed");
	                break;
	            case TransformationEngine::error_noengine: // no compatible engine
                        $textval=_("file conversion not supported");
	                break;
                    case TransformationEngine::error_connect: // no compatible engine
                        $textval=_("cannot contact server");
                        break;
	            case TransformationEngine::status_waiting: // waiting
                        $textval=_("waiting conversion file");
                        break;
                    case TransformationEngine::status_inprogress: // in progress
                        $textval=_("generating file");
                        break;
                    default:
                        $textval=sprintf(_("unknown file state %s"),$info->teng_state);
	        }
	        if ($htmllink) {
	            //$errconvert=trim(file_get_contents($info->path));
	            //$errconvert=sprintf('<p>%s</p>',str_replace(array("'","\r","\n"),array("&rsquo;",""),nl2br(htmlspecialchars($errconvert,ENT_COMPAT,"UTF-8"))));
	            if ($info->teng_state > 1) $waiting="<img class=\"mime\" src=\"Images/loading.gif\">";
	            else $waiting="<img class=\"mime\" needresize=1 src=\"lib/ui/icon/bullet_error.png\">";;
	            $htmlval=sprintf('<a _href_="%s" vid="%d" onclick="popdoc(event,this.getAttribute(\'_href_\')+\'&inline=yes\',\'%s\')">%s %s</a>',
	                             $this->getFileLink($oattr->id,$index),
	                             $info->id_file,str_replace("'","&rsquo;",_("file status")),$waiting,$textval);
	              if ($info->teng_state < 0)  {
	                  $htmlval.=sprintf('<a href="?app=FDL&action=FDL_METHOD&id=%d&method=resetConvertVaultFile(\'%s,%s)"><img class="mime" title="%s" src="%s"></a>',
	                                    $this->id,$oattr->id,$index,_("retry file conversion"),"lib/ui/icon/arrow_refresh.png");
	              }              
	        } else {
	            $htmlval=$textval;
	        }
	    } elseif ($htmllink) {

	        $mimeicon=getIconMimeFile($info->mime_s==""?$mime:$info->mime_s);
	        if (($oattr->repeat)&&($index <= 0))   $idx=$kvalue;
	        else $idx=$index;
	        $standardview=true;
	        $infopdf=false;
	        $viewfiletype=$oattr->getOption("viewfiletype");
	        if ($viewfiletype=="image" || $viewfiletype=="pdf") {
	            global $action;
	            $waiting=false;
	            if (substr($info->mime_s,0,5) == "image") {
	                $imageview=true;
	                $viewfiletype='png';
	                $pages=1;
	            } elseif (substr($info->mime_s,0,4) == "text") {
	                $imageview=true;
	                $viewfiletype='embed';
	                $pages=1;
	            } else {
	                $err=$vf->Show($vid, $infopdf,'pdf');
	                if ($err=="") {
	                    if ($infopdf->teng_state == TransformationEngine::status_done ||
	                        $infopdf->teng_state == TransformationEngine::status_waiting||
                                $infopdf->teng_state == TransformationEngine::status_inprogress) {
	                        $imageview=true;
	                        if ($viewfiletype=='image') $viewfiletype='png';
	                        else if ($viewfiletype=='pdf') $viewfiletype='embed';
	                        
	                        $pages=getPdfNumberOfPages($infopdf->path);
	                        if ($infopdf->teng_state == TransformationEngine::status_waiting ||
	                            $infopdf->teng_state == TransformationEngine::status_inprogress) $waiting=true;
	                    }
	                }
	            }


	            if ($imageview && (!$abstract)) {
	                $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/widgetFile.js");
	                $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/detectPdfPlugin.js");
	                $lay = new Layout("FDL/Layout/viewfileimage.xml", $action);
	                $lay->set("docid",$this->id);
                        $lay->set("waiting",($waiting?'true':'false'));
	                $lay->set("attrid", $oattr->id);
	                $lay->set("index", $idx);
	                $lay->set("viewtype", $viewfiletype);
	                $lay->set("mimeicon", $mimeicon);
                        $lay->set("vid", ($infopdf?$infopdf->id_file:$vid));
	                $lay->set("filetitle", $fname);
	                $lay->set("height", $oattr->getOption('viewfileheight','300px'));	                 
	                $lay->set("filelink", $this->getFileLink($oattr->id,$idx,false,false));
	                
	                $lay->set("pdflink", '');
	                if ($pdfattr=$oattr->getOption('pdffile')) {
	                    //$infopdf=$this->vault_properties($this->getAttribute($pdfattr));
	                    
	                    if (! preg_match('/^(text|image)/',$info->mime_s)) {
	                    //$pdfidx=($idx <0)?0:$idx;
	                    if ( $waiting || preg_match('/(pdf)/',$infopdf->mime_s)) {
	                       $lay->set("pdflink", $this->getFileLink($pdfattr,$idx,false,false));	                       
	                    }
	                    }
	                } 
	                $lay->set("pages", $pages); // todo
	                $htmlval =$lay->gen();
	                $standardview=false;
	            }
	        }
	        if ($standardview) {
	            $size=round($info->size/1024)._("AbbrKbyte");
	            $utarget= ($action->Read("navigator","")=="NETSCAPE")?"_self":"_blank";
	            $opt="";
	            $inline=$oattr->getOption("inline");
	            if ($inline=="yes") $opt="&inline=yes";
	            $htmlval="<a onmousedown=\"document.noselect=true;\" title=\"$size\" target=\"$utarget\" type=\"$mime\" href=\"".
	            $this->getFileLink($oattr->id,$idx, false, ($inline=="yes"))."\">";
	            if ($mimeicon) $htmlval.="<img class=\"mime\" needresize=1  src=\"Images/$mimeicon\">&nbsp;";
	            $htmlval.=$fname."</a>";
	        }

	    } else {
	        $htmlval=$info->name;
	    }

	  }
	
	}

	break;
      case "longtext":
      case "xml":
	if ($entities) $bvalue=nl2br(htmlentities(stripslashes(str_replace("<BR>","\n",$avalue)),ENT_COMPAT,"UTF-8"));
	else $bvalue=stripslashes(str_replace("<BR>","\n",$avalue));
	$shtmllink=$htmllink?"true":"false";
	$bvalue = preg_replace("/(\[|&#x5B;)ADOC ([^\]]*)\]/e",
			       "\$this->getDocAnchor('\\2',\"$target\",$shtmllink)",
			       $bvalue);
	$htmlval=str_replace(array("[","$"),array("&#091;","&#036;"),$bvalue);
	break;
      case "password":
	$htmlval=preg_replace("/./", "*", htmlentities(stripslashes($avalue),ENT_COMPAT,"UTF-8"));

	break;
      case "enum":
	$enumlabel = $oattr->getEnumlabel();
	$colors=$oattr->getOption("boolcolor");
	if ($colors!="") {
	  if (isset($enumlabel[$avalue])) {
	    reset($enumlabel);
	    $tcolor=explode(",",$colors);
	    if (current($enumlabel) == $enumlabel[$avalue]) {
	      $color=$tcolor[0];
	      $htmlval=sprintf('<pre style="background-color:%s;display:inline">&nbsp;-&nbsp;</pre>',$color);
	    } else {
	      $color=$tcolor[1];
	      $htmlval=sprintf('<pre style="background-color:%s;display:inline">&nbsp;&bull;&nbsp;</pre>',$color);
	    }
	  } else $htmlval=$avalue;
	} else {
	  if (isset($enumlabel[$avalue]))  $htmlval=$enumlabel[$avalue];
	  else $htmlval=$avalue;

	}

	break;
      case "array":
	$viewzone=$oattr->getOption("rowviewzone");
	$sort=$oattr->getOption("sorttable");
	if ($sort=="yes") {
	  global $action;
	  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FREEDOM/Layout/sorttable.js");
	}

	$lay = new Layout("FDL/Layout/viewdocarray.xml", $action);
	$lay->set("issort",($sort=="yes"));
	if (! method_exists($this->attributes,"getArrayElements")) {
	  break;
	}
	$height=$oattr->getOption("height",false);
	$lay->set("tableheight",$height);
	$lay->set("caption",$oattr->getLabel());
	$lay->set("aid",$oattr->id);

	if (($viewzone != "") &&  preg_match("/([A-Z_-]+):([^:]+):{0,1}[A-Z]{0,1}/",$viewzone,$reg)) {
	  // detect special row zone
  					 

	  $dxml=new DomDocument();
	  $rowlayfile=getLayoutFile($reg[1],($reg[2]));
	  if (! @$dxml->load($rowlayfile)) {
	    AddwarningMsg(sprintf(_("cannot open %s layout file"),DEFAULT_PUBDIR."/$rowlayfile"));
	    break;
	  }
	  $theads=$dxml->getElementsByTagName('table-head');
	  if ($theads->length > 0) {
	    $thead=$theads->item(0);
	    $theadcells=$thead->getElementsByTagName('cell');
	    $talabel=array();
	    for ($i = 0; $i < $theadcells->length; $i++) {
	      $th= xt_innerXML($theadcells->item($i));
	      $thstyle=$theadcells->item($i)->getAttribute("style");
	      if ($thstyle!="") $thstyle="style=\"$thstyle\"";
	      $talabel[] = array("alabel"=>$th,
				 "astyle"=>$thstyle,
				 "cwidth"=>"auto");
	    }
	    $lay->setBlockData("TATTR",$talabel);
	  }

	  $tbodies=$dxml->getElementsByTagName('table-body');
	  if ($tbodies->length > 0) {
	    $tbody=$tbodies->item(0);
	    $tbodycells=$tbody->getElementsByTagName('cell');
	    for ($i = 0; $i < $tbodycells->length; $i++) {
	      $tr[]= xt_innerXML($tbodycells->item($i));
	      $tcellstyle[]=$tbodycells->item($i)->getAttribute("style");
	    }
	  }
	  $ta = $this->attributes->getArrayElements($oattr->id);
	  $nbitem=0;
	  foreach($ta as $k=>$v) {
	    $tval[$k]=$this->getTValue($k);
	    $nbitem= max($nbitem,count($tval[$k]));
	    if ($emptyarray && ($this->getValue($k)!="")) $emptyarray=false;
	    $lay->set("L_".strtoupper($v->id),ucfirst($v->getLabel()));
	  }
	  // view values
	  $tvattr = array();
	  for ($k=0;$k<$nbitem;$k++) {
	    $tvattr[]=array("bevalue" => "bevalue_$k");
	    reset($ta);
	    $tivalue=array();

	    foreach($tr as $kd=>$vd) {

	      $hval = preg_replace("/\[([^\]]*)\]/e",
				   "\$this->rowattrReplace('\\1',$k)",
				   $vd);
	      $tdstyle=$tcellstyle[$kd];
	      $tivalue[]=array("evalue"=>$hval,
			       "color"=>"inherit",
			       "tdstyle"=>$tdstyle,
			       "bgcolor"=>"inherit",
			       "align"=>"inherit" );
	    }
	    $lay->setBlockData("bevalue_$k",$tivalue);
	  }
	  $lay->setBlockData("EATTR",$tvattr);
	  if ($nbitem > 10) $lay->set("caption",$oattr->getLabel()." ($nbitem)");
  					 
	  $htmlval =$lay->gen();
	} else {
	  $ta = $this->attributes->getArrayElements($oattr->id);
	  $talabel=array();
	  $tvattr = array();

	  $emptyarray=true;
	  $nbitem=0;
	  foreach($ta as $k=>$v) {
	    if (($v->mvisibility=="H")||($v->mvisibility=="I")||($v->mvisibility=="O")) continue;
	    $talabel[] = array("alabel"=>ucfirst($v->getLabel()),
			       "astyle"=>$v->getOption("cellheadstyle"),
			       "cwidth"=>$v->getOption("cwidth","auto"));	
	    $tval[$k]=$this->getTValue($k);
	    $nbitem= max($nbitem,count($tval[$k]));
	    if ($emptyarray && ($this->getValue($k)!="")) $emptyarray=false;
			
	  }
	  if (! $emptyarray) {
	    if ($oattr->getOption("vlabel")=="up") {
	      $caption=$oattr->getLabel();
	      if ($nbitem > 10) $caption.=" ($nbitem)";
	    } else {
	      $caption="";
	      if ($nbitem > 10) {
		if (count($talabel) > 0) {
		  $talabel[0]["alabel"].= " ($nbitem)";
		}
	      }
	    }
			       	
	    $lay->setBlockData("TATTR",$talabel);
	    $lay->set("caption",$caption);
	    $tvattr = array();
	    for ($k=0;$k<$nbitem;$k++) {
	      $tvattr[]=array("bevalue" => "bevalue_$k");
	      $tivalue=array();
	      foreach($ta as $ka=>$va) {
		if (($va->mvisibility=="H")||($va->mvisibility=="I")||($va->mvisibility=="O")) continue;
		$hval = $this->getHtmlValue($va,$tval[$ka][$k],$target,$htmllink,$k);
		if ($va->type=="image" ) {
		  $iwidth=$va->getOption("iwidth","80px");
		  if ($tval[$ka][$k]=="") $hval="";
		  else if ($va->link=="")  {
		    if (strstr($hval,'?')) $optwidth="&width=".intval($iwidth);
		    else $optwidth='';
		    $hval="<a  href=\"$hval\"><img border='0' width=\"$iwidth\" src=\"".$hval.$optwidth."\"></a>";
		  }  else {
		    $hval=preg_replace("/>(.+)</",">&nbsp;<img class=\"button\" width=\"$iwidth\" src=\"\\1\">&nbsp;<" ,$hval);
		  }
		}
		$tivalue[]=array("evalue"=>$hval,
				 "tdstyle"=>$va->getOption("cellbodystyle"),
				 "color"=>$va->getOption("color","inherit"),
				 "bgcolor"=>$va->getOption("bgcolor","inherit"),
				 "align"=>$va->getOption("align","inherit") );
	      }
	      $lay->setBlockData("bevalue_$k",$tivalue);
	    }
	    $lay->setBlockData("EATTR",$tvattr);
			       	 
	    $htmlval =$lay->gen();
	  } else {
	    $htmlval = "";
	  }
	}
	break;

      case "doc":

	$htmlval = "";
	if ($avalue != "") {
	  if ($kvalue>-1)   $idocid=$this->getTValue($aformat,"",$kvalue);
	  else $idocid=$this->getValue($aformat);
  					 
	  if ($idocid>0) {
	    //$lay = new Layout("FDL/Layout/viewadoc.xml", $action);
	    //$lay->set("id",$idocid);
	    $idoc = new_Doc($this->dbaccess,$idocid);
	    $htmlval =$idoc->viewDoc("FDL:VIEWTHUMBCARD:T","finfo");

	    //$htmlval =$lay->gen();
	  }
	}
	break;

      case "docid":
	if ($oattr->format != "") {
  					 
	  $aformat="";
	  $multiple=($oattr->getOption("multiple")=="yes");
	  $dtarget=$target;
	  if ($target != "mail") {
	    $ltarget=$oattr->getOption("ltarget");
	    if ($ltarget != "") $dtarget=$ltarget;
	  }
	  if ($multiple) {
	    $avalue=str_replace("\n","<BR>",$avalue);
	    $tval=explode("<BR>",$avalue);
	    $thval=array();
	    foreach ($tval as $kv=>$vv) {
	      if (trim($vv) =="")  $thval[] = $vv;
	      elseif ($oattr->link != "") {
		$link=preg_replace("/%".$oattr->id."%/i",$vv,$oattr->link);
		$link=$this->urlWhatEncode( $oattr->link, $kvalue);
		if ($link) $thval[]='<a target="'.$dtarget.'" href="'.$link.'">'.$this->getHTMLTitle($vv).'</a>';
		else $thval[]=$this->getHTMLTitle($vv);
	      } else $thval[]=$this->getDocAnchor(trim($vv),$dtarget,$htmllink);
	    }
	    if ($oattr->link) $htmllink=false;
	    $htmlval=implode("<br/>",$thval);
	  } else {
	    if ($avalue=="") $htmlval = $avalue;
	    elseif ($oattr->link != "") $htmlval=$this->getHTMLTitle($avalue);
	    else $htmlval = $this->getDocAnchor(trim($avalue),$dtarget,$htmllink,false,true,$oattr->getOption("docrev"));
	  }
	} else
	  $htmlval=$avalue;

	break;
      case "thesaurus":
	$aformat="";
	$multiple=($oattr->getOption("multiple")=="yes");
	if ($multiple) {
	  $avalue=str_replace("\n","<BR>",$avalue);
	  $tval=explode("<BR>",$avalue);
	  $thval=array();
	  foreach ($tval as $kv=>$vv) {
	    if (trim($vv) =="")  $thval[] = $vv;
	    else {
	      $thc=new_doc($this->dbaccess,trim($vv));
	      if ($thc->isAlive()) $thval[]=$this->getDocAnchor(trim($vv),$target,$htmllink,$thc->getLangTitle());
	      else $thval[]="th error $vv";
	    }
	  }
	  $htmlval=implode("<br/>",$thval);
	} else {
	  if ($avalue=="") $htmlval = $avalue;
	  else {
	    $thc=new_doc($this->dbaccess,$avalue);
	    if ($thc->isAlive()) $htmlval = $this->getDocAnchor(trim($avalue),$target,$htmllink,$thc->getLangTitle());
	    else $htmlval="th error $avalue";
	  }
	}

	break;
      case "option":
	$lay = new Layout("FDL/Layout/viewdocoption.xml", $action);
	$htmlval = "";

	if ($kvalue>-1) $di=$this->getTValue($oattr->format,"",$kvalue);
	else $di=$this->getValue($oattr->format);
	if ($di > 0) {
	  $lay->set("said",$di);
	  $lay->set("uuvalue",urlencode($avalue));

	  $htmlval =$lay->gen();
	}
	break;
      case 'money':


	$htmlval=money_format('%!.2n', doubleval($avalue));
	$htmlval=str_replace(" ","&nbsp;",$htmlval); // need to replace space by non breaking spaces
	break;

      case 'htmltext':
	$shtmllink=$htmllink?"true":"false";
	$avalue = preg_replace("/(\[|&#x5B;)ADOC ([^\]]*)\]/e",
			       "\$this->getDocAnchor('\\2',\"$target\",$shtmllink)",
			       $avalue);
	$htmlval="<DIV>$avalue</DIV>";
	break;
      case 'date':
	if (($aformat!="") && (trim($avalue) != "")) {
	  if ($avalue) $htmlval=strftime($aformat,FrenchDateToUnixTs($avalue));
	  else $htmlval=$avalue;
	} elseif(trim($avalue) == "") {
	  $htmlval="";
	} else {
	  $htmlval=FrenchDateToLocaleDate($avalue);
	}
	$aformat="";
	break;
      case 'time':
	if (($aformat!="") && (trim($avalue) != "")) {
	  if ($avalue) $htmlval=strftime($aformat,strtotime($avalue));
	  else $htmlval=$avalue;
	} else {
	  $htmlval=substr($avalue,0,5); // do not display second
	}
	$aformat="";
	break;
      case 'timestamp':
	if (($aformat!="") && (trim($avalue) != "")) {
	  if ($avalue) $htmlval=strftime($aformat,FrenchDateToUnixTs($avalue));
	  else $htmlval=$avalue;
	} elseif(trim($avalue) == "") {
	  $htmlval="";
	} else {
	  $htmlval=FrenchDateToLocaleDate($avalue);
	}
	$aformat="";
	break;
      case 'ifile':
	$lay = new Layout("FDL/Layout/viewifile.xml", $action);
	$lay->set("aid",$oattr->id);
	$lay->set("id",$this->id);
	$lay->set("iheight",$oattr->getOption("height","200px"));
	$htmlval =$lay->gen();

	break;
  				 
      case 'color':
	$htmlval=sprintf("<span style=\"background-color:%s\">%s</span>",$avalue,$avalue);
	break;

      default :
	if ($entities) $avalue=htmlentities(stripslashes($avalue),ENT_COMPAT,"UTF-8");
	else $avalue=stripslashes($avalue);
	$htmlval=str_replace(array("[","$"),array("&#091;","&#036;"),$avalue);
  				 
	break;

      }

      if (($aformat != "") && ($atype != "doc") && ($atype != "array")&& ($atype != "option") ){
	//printf($htmlval);
	$htmlval=sprintf($aformat,$htmlval);
      }
      // add link if needed
      if ($htmllink && ($oattr->link != "") ) {
	$ititle="";
	$hlink=$oattr->link;
	if ($hlink[0] == "[") {
	  if (preg_match('/\[(.*)\](.*)/', $hlink, $reg)) {
	    $hlink=$reg[2];
	    $ititle=str_replace("\"","'",$reg[1]);
	  }
	}
	if ($ulink = $this->urlWhatEncode( $hlink, $kvalue)) {
	  if ($target=="ext") {
	    if (preg_match("/FDL_CARD.*id=([0-9]+)/",$ulink,$reg)) {
	      $abegin=$this->getDocAnchor($reg[1],$target,true,$htmlval);
	      $htmlval='';
	      $aend="";
	    } else if (true || preg_match("/^http:/",$ulink,$reg)) {
	      $ec=getSessionValue("ext:targetUrl");
  						 
	      if ($ec)  {
		$ec=str_replace("%V%",$ulink,$ec);
		$ec=str_replace("%L%",$oattr->getLabel(),$ec);
		$ecu=str_replace("'","\\'",$this->urlWhatEncode($ec));
		$abegin="<a  onclick='parent.$ecu'>";
	      } else {
		$ltarget=$oattr->getOption("ltarget");
		$abegin="<a target=\"$ltarget\"  href=\"$ulink\">";
	      }
  						 
	      $aend="</a>";
	    }
	  } else if ($target == "mail") {
	    $scheme="";
	    if (preg_match("/^([[:alpha:]]*):(.*)/",$ulink,$reg)) {
	      $scheme=$reg[1];
	    }
	    $abegin="<a target=\"$target\"  href=\"";
	    if ($scheme == "") $abegin.= $action->GetParam("CORE_URLINDEX",($action->GetParam("CORE_ABSURL")."/")).$ulink;
	    else $abegin.= $ulink;
	    $abegin.="\">";
	    $aend="</a>";
	  } else {
	    $ltarget=$oattr->getOption("ltarget");
	    if ($ltarget != "") $target=$ltarget;
	    $ltitle=$oattr->getOption("ltitle");
	    if ($ltitle != "") $ititle=str_replace("\"","'",$ltitle);
	    $abegin="<a target=\"$target\" title=\"$ititle\" onmousedown=\"document.noselect=true;\" href=\"";
	    $abegin.= $ulink."\" ";;
	    if ($htmllink > 1){
	      $scheme="";
	      if (preg_match("/^([[:alpha:]]*):(.*)/",$ulink,$reg)) {
		$scheme=$reg[1];
	      }
	      if (($scheme == "") || ($scheme == "http")) {
		if ($scheme == "") $ulink.="&ulink=1";
		$abegin.=" oncontextmenu=\"popdoc(event,'$ulink');return false;\" ";
	      }
	    }
	    $abegin.=">";
	    $aend="</a>";
	  }


	} else {
	  $abegin="";
	  $aend="";
	}
      } else {
	$abegin="";
	$aend="";
      }

      $thtmlval[$kvalue]=$abegin.$htmlval.$aend;
    }

    return implode("<BR>",$thtmlval);
  }

  /**
   * return raw content
   * @param string $attr html tag attributes
   * @param string $data html content (innerHTML)
   * @return string raw content
   */
  final private static function getHtmlTdContent($attr,$data) {  
    $data=preg_replace('|<(/?[^> ]+)(\s[^>]*?)?>|ms', '', $data); // delete all tags
    return '<td>'.$data.'</td>';
  }


  final public function getHtmlAttrValue($attrid, $target="_self",$htmllink=2, $index=-1,$entities=true,$abstract=false) {
    if ($index != -1) $v=$this->getTValue($attrid,"",$index);
    else $v=$this->getValue($attrid);
    if ($v=="") return $v;
    return $this->GetHtmlValue($this->getAttribute($attrid),
			       $v,$target,$htmllink,$index,$entities,$abstract);
  }
 final public function getOooAttrValue($attrid, $target="_self",$htmllink=false, $index=-1) {
    if ($index != -1) $v=$this->getTValue($attrid,"",$index);
    else $v=$this->getValue($attrid);
    if ($v=="") return $v;
    return $this->getOooValue($this->getAttribute($attrid),
			       $v,$target,$htmllink,$index);
  }
  final public function getOooValue($oattr, $value, $target="_self",$htmllink=false, $index=-1) { 
    global $action;

    $aformat=$oattr->format;
    $atype=$oattr->type;

    if (($oattr->repeat)&&($index <= 0)){
      $tvalues = explode("\n",$value);
    } else {
      $tvalues[$index]=$value;
    }
    $idocfamid=$oattr->format;

    $attrid=$oattr->id;
    foreach($tvalues as $kvalue=>$avalue) {
      $htmlval="";
      switch ($atype)	{
      case "idoc":
	// nothing        
	break;
      case "image":
	$htmlval=$this->vault_filename_fromvalue($avalue, true);
	break;
      case "file":
	// file name
	$htmlval=$this->vault_filename_fromvalue($avalue, false);
	break;
      case "longtext":
      case "xml":
	$htmlval=str_replace("&","&amp;",$avalue);
	$htmlval=str_replace(array("<",">"),array("&lt;","&gt;"),$htmlval);
	$htmlval=str_replace("\n","<text:line-break/>",$htmlval);
	$htmlval=str_replace("&lt;BR&gt;","<text:line-break/>",$htmlval);
	$htmlval=str_replace("\r","",$htmlval);
	break;
      case "password":

	break;
      case "enum":
	$enumlabel = $oattr->getEnumlabel();
	$colors=$oattr->getOption("boolcolor");
	if ($colors!="") {
	  if (isset($enumlabel[$avalue])) {
	    reset($enumlabel);
	    $tcolor=explode(",",$colors);
	    if (current($enumlabel) == $enumlabel[$avalue]) {
	      $color=$tcolor[0];
	      $htmlval=sprintf('<pre style="background-color:%s;display:inline">&nbsp;-&nbsp;</pre>',$color);
	    } else {
	      $color=$tcolor[1];
	      $htmlval=sprintf('<pre style="background-color:%s;display:inline">&nbsp;&bull;&nbsp;</pre>',$color);
	    }
	  } else $htmlval=$avalue;
	} else {
	  if (isset($enumlabel[$avalue]))  $htmlval=$enumlabel[$avalue];
	  else $htmlval=$avalue;
	}

	break;
      case "thesaurus":
	$aformat="";
	$multiple=($oattr->getOption("multiple")=="yes");
	if ($multiple) {
	  $avalue=str_replace("\n","<BR>",$avalue);
	  $tval=explode("<BR>",$avalue);
	  $thval=array();
	  foreach ($tval as $kv=>$vv) {
	    if (trim($vv) =="")  $thval[] = $vv;
	    else {
	      $thc=new_doc($this->dbaccess,trim($vv));
	      if ($thc->isAlive()) $thval[]=$thc->getLangTitle();
	      else $thval[]="th error $vv";
	    }
	  }
	  $htmlval=implode("<text:tab/>",$thval);
	} else {
	  if ($avalue=="") $htmlval = $avalue;
	  else {
	    $thc=new_doc($this->dbaccess,$avalue);
	    if ($thc->isAlive()) $htmlval = $thc->getLangTitle();
	    else $htmlval="th error $avalue";
	  }
	}

	break;
      case "array":
	break;
      case "doc":
	break;
      case "docid":
	if ($oattr->format != "") {
  					 
	  $aformat="";
	  $multiple=($oattr->getOption("multiple")=="yes");
	  $dtarget=$target;
	  if ($target != "mail") {
	    $ltarget=$oattr->getOption("ltarget");
	    if ($ltarget != "") $dtarget=$ltarget;
	  }
	  if ($multiple) {
	    $avalue=str_replace("\n","<BR>",$avalue);
	    $tval=explode("<BR>",$avalue);
	    $thval=array();
	    foreach ($tval as $kv=>$vv) {
	      if (trim($vv) =="")  $thval[] = $vv;
	      else $thval[]=$this->getDocAnchor(trim($vv),$dtarget,false);
	    }
	    $htmlval=implode("<text:tab/>",$thval);
	  } else {
	    if ($avalue=="") $htmlval = $avalue;
	    elseif ($oattr->link != "") $htmlval=$this->getTitle($avalue);
	    else $htmlval = $this->getDocAnchor(trim($avalue),$dtarget,false);
	  }
	} else
	  $htmlval=$avalue;

	break;

      case "option":
	break;
      case "money":
	$htmlval=money_format('%!.2n', doubleval($avalue));
	//$htmlval=str_replace(" ","&nbsp;",$htmlval); // need to replace space by non breaking spaces
	break;

      case "htmltext":
	$html_body=trim($avalue);
	$html_body=str_replace(array('&quot;','&lt;','&gt;'),array('--quoteric--','--lteric--','--gteric--'),$html_body); // prevent pb for quot in quot

	if ($html_body[0] != '<') {
	  // think it is raw text
	  $html_body=str_replace("\n<br/>","\n",$html_body);
	  $html_body=str_replace('<br/>',"\n",$html_body);
	  if (! strpos($html_body,'<br')) $html_body=str_replace(array("<",">",'&'),array("&lt;","&gt;","&amp;"),$html_body);
	  $html_body='<p>'.nl2br($html_body).'</p>';
	}
		  $html_body=str_replace(">\r\n",">",$html_body);
		  $html_body=str_replace("\r","",$html_body);
	
	$html_body = preg_replace("/<!--.*?-->/ms", "", $html_body); //delete comments
	$html_body = preg_replace("/<td(\s[^>]*?)?>(.*?)<\/td>/mse",
				  "\$this->getHtmlTdContent('\\1','\\2')",
				  $html_body); // accept only text in td tag
	$html_body=cleanhtml($html_body);
	$html_body=preg_replace("/(<\/?)([^\s>]+)([^>]*)(>)/e",
				"toxhtmltag('\\1','\\2','\\3','\\4')",
  				$html_body ); // begin tag transform to pseudo xhtml

	$html_body=str_replace(array('\"','&quot;'),'"',$html_body);
	$html_body=str_replace('&','&amp;',html_entity_decode($html_body,ENT_NOQUOTES,'UTF-8'));

	$html_body=str_replace(array('--quoteric--','--lteric--','--gteric--'),array('&quot;','&lt;','&gt;'),$html_body); // prevent pb for quot in quot


	$xmldata='<xhtml:body xmlns:xhtml="http://www.w3.org/1999/xhtml">'.$html_body."</xhtml:body>";

	$xslt = new xsltProcessor;
	$xslt->importStyleSheet(DomDocument::load(DEFAULT_PUBDIR."/CORE/Layout/html2odt.xsl"));
	//	set_error_handler('HandleXmlError');
	try {
	  $dom = @DomDocument::loadXML($xmldata);
	} catch (Exception $e) {
	  addWarningMsg(sprintf(_("possible incorrect conversion HTML to ODT %s"),$this->title));
	  /*
	    print "Exception catched:\n";
	    print "Code: ".$e->getCode()."\n";
	    print "Message: ".$e->getMessage()."\n";
	    print  "Line: ".$e->getLine();
	    // error in XML
	    print "\n<br>ERRORXSLT:".$this->id.$this->title."\n";
	    print "\n=========RAWDATA=================\n";
	    print  $avalue;
	    print "\n=========XMLDATA=================\n";
	    print_r2($xmldata);
	    exit;*/
	}
	//restore_error_handler();
	if ($dom) {
	  $xmlout= $xslt->transformToXML($dom);
	  $dxml=new DomDocument();
	  $dxml->loadXML($xmlout);
	  //office:text
	  $ot=$dxml->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:office:1.0","text");
	  $ot1=$ot->item(0);
	  $officetext= $ot1->ownerDocument->saveXML($ot1);
	  $htmlval=str_replace(array('<office:text>', '</office:text>','<office:text/>'),"",$officetext);
	  // work around : tables are not in paragraph
	  $htmlval=preg_replace("/(<text:p>[\s]*<table:table )/ ",
				"<table:table ",$htmlval);
	  $htmlval=preg_replace("/(<\/table:table>[\s]*<\/text:p>)/ ",
				"</table:table> ",$htmlval);	
	  
	  $pppos=mb_strrpos($htmlval, '</text:p>');
	
	  $htmlval=sprintf('<text:section text:style-name="Sect%s" text:name="Section%s" aid="%s">%s</text:section>',
	   $attrid,$attrid,$attrid,$htmlval);
	} else {
  					 
	  addWarningMsg(sprintf(_("incorrect conversion HTML to ODT %s"),$this->title));

	}
	//$htmlval=preg_replace("/<\/?(\w+[^:]?|\w+\s.*?)>//g", "",$htmlval  );
	break;
      case 'date':
	if (($aformat!="") && (trim($avalue) != "")) {
	  if ($avalue) $htmlval=strftime($aformat,FrenchDateToUnixTs($avalue));
	  else $htmlval=$avalue;
	} elseif(trim($avalue) == "") {
	  $htmlval="";
	} else {
	  $htmlval=FrenchDateToLocaleDate($avalue);
	}
	$aformat="";
	break;
      case 'time':
	if ($aformat!="") {
	  if ($avalue) $htmlval=strftime($aformat,strtotime($avalue));
	  else $htmlval=$avalue;
	  $aformat="";
	} else {
	  $htmlval=substr($avalue,0,5); // do not display second
	}

	break;
      case 'timestamp':
	if (($aformat!="") && (trim($avalue) != "")) {
	  if ($avalue) $htmlval=strftime($aformat,FrenchDateToUnixTs($avalue));
	  else $htmlval=$avalue;
	} elseif(trim($avalue) == "") {
	  $htmlval="";
	} else {
	  $htmlval=FrenchDateToLocaleDate($avalue);
	}
	$aformat="";
	break;
      case 'ifile':
	$lay = new Layout("FDL/Layout/viewifile.xml", $action);
	$lay->set("aid",$oattr->id);
	$lay->set("id",$this->id);
	$lay->set("iheight",$oattr->getOption("height","200px"));
	$htmlval =$lay->gen();

	break;
  				 
      case 'color':
	$htmlval=sprintf("<span style=\"background-color:%s\">%s</span>",$avalue,$avalue);
	break;

      default :
	$htmlval=stripslashes($avalue);
	$htmlval=str_replace(array("<",">",'&'),array("&lt;","&gt;","&amp;"),$htmlval);

	break;
      }

      if (($aformat != "") && ($atype != "doc") && ($atype != "array")&& ($atype != "option") ){
	//printf($htmlval);
	$htmlval=sprintf($aformat,$htmlval);
      }


      $thtmlval[$kvalue]=$htmlval;
    }

    return implode("<text:tab/>",$thtmlval);
  }

  /**
   * Control Access privilege for document for current user
   *
   * @param string $aclname identificator of the privilege to test
   * @return string empty means access granted else it is an error message (access unavailable)
   */
  public function Control($aclname) {
    // -------------------------------------------------------------------- 
    if (($this->IsAffected()) ) {	
      
      if (($this->profid <= 0) || ($this->userid == 1 )) return ""; // no profil or admin

      $err= $this->controlId($this->profid,$aclname);
      if (($err!="") &&  ($this->isConfidential())) $err=sprintf(_("no privilege %s for %s"),$aclname,$this->getTitle());
      
      return $err;
    }
    return "";
    return sprintf(_("cannot control : object not initialized : %s"),$aclname);
  } 

  /**
   * Control Access privilege for document for other user
   *
   * @param int $uid user identificator
   * @param string $aclname identificator of the privilege to test
   * @return string empty means access granted else it is an error message (access unavailable)
   */
  public function ControlUser($uid,$aclname) {
    // -------------------------------------------------------------------- 
    if ($this->IsAffected() ) {	      
      if (($this->profid <= 0) || ($uid == 1 )) return ""; // no profil or admin
      if (! $uid) return _("control :: user identificator is null");
      return $this->controlUserId($this->profid,$uid,$aclname);
    }
    return "";
  }
  
  /**
   * verify that the document exists and is not in trash (not a zombie)
   * @return bool
   */
  final public function isAlive() {
    return ((DbObj::isAffected()) && ($this->doctype != 'Z'));
  }

  /**
   * add several triggers to update different tables (such as docread) or attributes (such as values)
   * @param bool $onlydrop set to false for only drop triggers
   */
  final public function SqlTrigger($onlydrop=false,$code=false) {

    if (get_class($this) == "DocFam") {
      $cid = "fam";
    } else {
      if ($this->doctype == 'C') return;
      if (intval($this->fromid) == 0) return;
      
      $cid = $this->fromid;
    }
          
    $sql = "";
    // delete all relative triggers
    $sql .= "select droptrigger('doc".$cid."');";     
    if ($onlydrop) return $sql; // only drop

    if ($code) { 
      $lay = new Layout("FDL/Layout/sqltrigger.xml");
      $na=$this->GetNormalAttributes();
      $tvalues=array();
      $tsearch=array();
      foreach ($na as $k=>$v) {
	if (($v->type != "array") && ($v->type != "frame") && ($v->type != "tab") && ($v->type != "idoc") ) {
	  $tvalues[]=array("attrid"=>$k);
	  if (($v->type != "file") && ($v->type != "image") && ($v->type != "password"))  $tsearch[]=array("attrid"=>$k);
	}
	if ($v->type == "file") {
	  $files[]=array("attrid"=>$k."_txt",
			 "vecid"=>$k."_vec");
	  $tsearch[]=array("attrid"=>$k."_txt");
	}
      }      
      $na=$this->GetAbstractAttributes();
      foreach ($na as $k=>$v) {
	if (($v->type != "array") && ($v->type != "file") && ($v->type != "image") && ($v->type != "password")) {
	  $tabstract[]=array("attrid"=>$k);
	}
      }    
      $lay->setBlockData("ATTRFIELD",$tvalues);
      $lay->setBlockData("SEARCHFIELD",$tsearch);
      $lay->setBlockData("ABSATTR",$tabstract);
      $lay->setBlockData("FILEATTR",$files);
      $lay->setBlockData("FILEATTR2",$files);
      $lay->setBlockData("FILEATTR3",$files);
      $lay->set("hasattr",(count($tvalues)>0));
      $lay->set("hassattr",(count($tsearch)>0));
      $lay->set("hasabsattr",(count($tabstract)>0));
      $lay->set("docid",$this->fromid);
      $sql=$lay->gen();
    } else {


      if (is_array($this->attributes->fromids)) {
	foreach($this->attributes->fromids as $k=>$v) {

	  $sql .="create trigger UV{$cid}_$v BEFORE INSERT OR UPDATE ON doc$cid FOR EACH ROW EXECUTE PROCEDURE upval$v();";
     
	}
      }
      // the reset trigger must begin with 'A' letter to be proceed first (pgsql 7.3.2)
      if ($cid!="fam") {
	$sql .="create trigger AUVR{$cid} BEFORE UPDATE  ON doc$cid FOR EACH ROW EXECUTE PROCEDURE resetvalues();";
	$sql .="create trigger VFULL{$cid} BEFORE INSERT OR UPDATE  ON doc$cid FOR EACH ROW EXECUTE PROCEDURE fullvectorize$cid();";
      
      }
      $sql .="create trigger zread{$cid} AFTER INSERT OR UPDATE OR DELETE ON doc$cid FOR EACH ROW EXECUTE PROCEDURE setread();";
      $sql .="create trigger FIXDOC{$cid} AFTER INSERT ON doc$cid FOR EACH ROW EXECUTE PROCEDURE fixeddoc();";
    }
    return $sql;
  }

  /**
   * add specials SQL indexes
   */
  final public function GetSqlIndex() {
    $t="";
    $id=$this->fromid;
    if ($this->sqlindex)  $sqlindex=array_merge($this->sqlindex,Doc::$sqlindex);
    else $sqlindex=Doc::$sqlindex;
    foreach ($sqlindex as $k=>$v) {
      
      if ($v["unique"])  $unique="unique";
      else $unique="";
      if ($v["using"]!= "") {
	
	if ($v["using"][0]== "@") {
	  $v["using"]=getParam(substr($v["using"],1));
	}
	$t.=sprintf("CREATE $unique INDEX %s$id on  doc$id using %s(%s);\n",$k,$v["using"],$v["on"]);
      } else {
	$t.=sprintf("CREATE $unique INDEX %s$id on  doc$id(%s);\n",$k,$v["on"]);
      }
    }
    return $t;
  }
  
  /** 
   * return the basename of template file
   * @return string (return null if template not found)
   */
  public function getZoneFile($zone) {
  	$index = -1;
  	if( $zone == "" ) {
  		return null;
  	}

  	$reg = $this->parseZone($zone);
  	if( is_array($reg) ) {
  		$aid = $reg['layout'];
  		if( $reg['index'] != '' ) {
  			$index = $reg['index'];
  		}
  		$oa = $this->getAttribute($aid);
  		if( $oa ) {
  			if( $oa->usefor != 'Q' ) {
  				$template = $this->getValue($oa->id);
  			} else {
  				$template = $this->getParamValue($aid);
  			}
  			if( $index >= 0 ) {
  				$tt = $this->_val2array($template);
  				$template = $tt[$index];
  			}

  			if( $template == "" ) {
  				return null;
  			}

  			return $this->vault_filename_fromvalue($template, true);
  		}
  		return getLayoutFile($reg['app'], ($aid));
  		
  	}
  }
  /** 
   * return the character in third part of zone
   * @return char
   */
  public function getZoneOption($zone="") {
    if ($zone=="") {
      $zone=$this->defaultview;
    }

    $zoneElements = $this->parseZone($zone);
    if( $zoneElements === false ) {
      return '';
    }

    return $zoneElements['modifier'];
  }  
  /** 
   * return the characters in fourth part of zone
   * @return string
   */
  public function getZoneTransform($zone="") {
    if ($zone=="") {
      $zone=$this->defaultview;
    }

    $zoneElements = $this->parseZone($zone);
    if( $zoneElements === false ) {
      return '';
    }

    return $zoneElements['transform'];
  }
  /**
   * set default values define in family document
   * the format of the string which define default values is like
   * [US_ROLE|director][US_SOCIETY|alwaysNet]...
   * @param string $defval the default values
   * @param bool  $method set to false if don't want interpreted values
   * @param bool  $forcedefault force default values
   */
  final public function setDefaultValues($tdefval,$method=true,$forcedefault=false) {
    if (is_array($tdefval)) {
      foreach ($tdefval as $aid=>$dval) {
	$oattr = $this->getAttribute($aid);
  			
	$ok = false;
	if(empty($oattr)) $ok = false;
        elseif(! method_exists($oattr,"inArray")) $ok = false;
	elseif($forcedefault) $ok = true;
	elseif(!$oattr->inArray()) $ok = true;
	elseif($oattr->fieldSet->format != "empty" && $oattr->fieldSet->getOption("empty")!="yes") {
	  $ok = true;
	}
	if ($ok) {
	  if ($method) {
	    $this->setValue($aid, $this->GetValueMethod($dval));
	  } else {
	    $this->$aid= $dval; // raw data
	  }
	} else {
	    // TODO raise exception
	}
      }
    }
  }

  /**
   * set default name reference 
   * if no name a new name will ne computed from its initid and family name
   * the new name is set to name attribute
   * @return string error messahe (empty means OK).
   */
  final public function setNameAuto() {
    if (($this->name=="")&&($this->initid>0)) {
      $dfam=$this->getFamDoc();
      if ($dfam->name=="") return sprintf("no family name %s",$dfam->id);
      $this->name=$dfam->name.'_'.$this->initid;
      $err=$this->modify(true,array("name"),true);
    }
    return $err;
  }

  /**
   * set all attribute in W visibility 
   * 
   * 
   */
  function SetWriteVisibility() {
    // transform hidden to writted attribut for default document
   
    $listattr = $this->GetAttributes();
    foreach($listattr as $i=>$attr) {
      if (($attr->mvisibility == "H") || ($attr->mvisibility == "I") || ($attr->mvisibility == "R") || ($attr->mvisibility == "S")) {
	$this->attributes->attr[$i]->mvisibility="W";
      }
    }
    
  }

  /**
   * Return the main path relation
   * list of prelid properties (primary relation)
   * the first item is the direct parent, the second:the grand-parent , etc.
   * @return array key=id , value=title of relation
   */ 
  function getMainPath() {
    $tr=array();

    if ($this->prelid > 0) {

      $d=getTDoc($this->dbaccess,$this->prelid);
      $fini=false;
      while (! $fini) {
	if ($d) {
	  if (controlTDoc($d,"view")) {
	    if (! in_array($d["initid"],array_keys($tr))) {
	      $tr[$d["initid"]]=$d["title"];
	      if ($d["prelid"] > 0) $d=getTDoc($this->dbaccess,$d["prelid"]);
	      else $fini=true;

	    } else $fini=true;
	  } else $fini=true;
	} else {
	  $fini=true;
	}
	 
      }
    }
    return $tr;
  }

  /**
   * generate HTML code for view doc
   * @param string $layout layout to use to view document
   * @param string $target window target name for hyperlink destination
   * @param bool $ulink if false hyperlink are not generated
   * @param bool $abstract if true only abstract attribute are generated
   * @param bool $changelayout if true the internal layout ($this->lay) will be replace by the new layout
   */
  final public function viewDoc($layout="FDL:VIEWBODYCARD",$target="_self",$ulink=true,$abstract=false,$changelayout=false) {
    global $action;

    $reg = $this->parseZone($layout);
    if( $reg === false ) {
      return sprintf(_("error in pzone format %s"), $layout);
    }

    if( array_key_exists('args', $reg) ) {
      // in case of arguments in zone
      global $ZONE_ARGS;
      $layout = $reg['fulllayout'];
      if( array_key_exists('argv', $reg) ) {
	foreach( $p['argv'] as $k => $v ) {
	  $ZONE_ARGS[$k] = $v;
	}
      }
    }
  	 
    if (!$changelayout) {
      $play=$this->lay;
    }
    $binary=($this->getZoneOption($layout)=="B");

    $tplfile=$this->getZoneFile($layout);
    
    $ext=getFileExtension($tplfile);
    if (strtolower($ext)=="odt") {
      include_once('Class.OOoLayout.php');
      $target="ooo";
      $ulink=false;
      $this->lay = new OOoLayout($tplfile, $action, $this);
    } else {
      $this->lay = new Layout($tplfile, $action);
    }

    if (! file_exists($this->lay->file)) return sprintf(_("template file (layout [%s]) not found"), $layout);
        $this->lay->setZone($reg);
       
    $this->lay->set("_readonly",($this->Control('edit')!=""));
    $method = strtok(strtolower($reg['layout']),'.');

  	 
    if (method_exists ( $this, $method)) {
      $this->$method($target,$ulink,$abstract);
    } else {
      $this->viewdefaultcard($target,$ulink,$abstract);
    }


    $laygen=$this->lay->gen();

    if (!$changelayout)       $this->lay=$play;

    if (! $ulink) {
      // suppress href attributes
      return preg_replace(array("/href=\"index\.php[^\"]*\"/i", "/onclick=\"[^\"]*\"/i","/ondblclick=\"[^\"]*\"/i"),
			  array("","","") ,$laygen );
    }
    if ($target=="mail") {
      // suppress session id
      return preg_replace("/\?session=[^&]*&/", "?" ,$laygen );
    }
    if ($binary && ($target != "ooo")) {
      // set result into file
      $tmpfile=uniqid(getTmpDir()."/fdllay").".html";
      $nc=file_put_contents($tmpfile,$laygen);
      $laygen=$tmpfile;

    }


    return $laygen;
  }
  // --------------------------------------------------------------------

  /**
   * default construct layout for view card containt
   *
   * @param string $target window target name for hyperlink destination
   * @param bool $ulink if false hyperlink are not generated
   * @param bool $abstract if true only abstract attribute are generated
   * @param bool $viewhidden if true view also hidden attributes
   */
  final public function viewdefaultcard($target="_self",$ulink=true,$abstract=false,$viewhidden=false) {
    $this->viewattr($target,$ulink,$abstract,$viewhidden);
    $this->viewprop($target,$ulink,$abstract);
  }
  // --------------------------------------------------------------------

  /**
   * construct layout for view card containt
   *
   * @param string $target window target name for hyperlink destination
   * @param bool $ulink if false hyperlink are not generated
   * @param bool $abstract if true only abstract attribute are generated
   * @param bool $onlyopt if true only optionnal attributes are displayed
   */
  function viewbodycard($target="_self",$ulink=true,$abstract=false,$onlyopt=false) {
    global $action;
  
    $frames= array();     
    if ($abstract){
      // only 3 properties for abstract mode
      $listattr = $this->GetAbstractAttributes();
    } else {
      $listattr = $this->GetNormalAttributes($onlyopt);    
    }
    

    $nattr = count($listattr); // attributes list count


    $k=0; // number of frametext
    $v=0;// number of value in one frametext
    $nbimg=0;// number of image in one frametext
    $currentFrameId="";

    $changeframe=false; // is true when need change frame
    $tableframe=array();
    $tableimage=array();
    $ttabs=array();

    $iattr=0;
    $firsttab=false;
    $onlytab=strtolower(getHttpVars("onlytab"));
    $tabonfly=false; // I want tab on fly
    $showonlytab=($onlytab?$onlytab:false);
    if ($onlytab) {
        $this->addUTag($this->userid,"lasttab",$onlytab);
    }
    foreach($listattr as $i=>$attr) {
        if ($onlytab && ($attr->fieldSet->id != $onlytab && $attr->fieldSet->fieldSet->id != $onlytab)) continue;
        
      $iattr++;

      //------------------------------
      // Compute value element
      $value = chop($this->GetValue($i));
      if (!$attr->fieldSet) {
          addWarningMsg(sprintf(_("unknow set for attribute %s %s"),$attr->id, $attr->getLabel()));
          continue;
      }
      $frametpl=$attr->fieldSet->getOption("viewtemplate");
      if ($attr->fieldSet && ($frametpl && $attr->fieldSet->type != "array")) {
	$goodvalue=false;
	if ( $currentFrameId != $attr->fieldSet->id) {
	  if ( ($attr->fieldSet->mvisibility != "H") && ($attr->fieldSet->mvisibility != "I")) {
	    $changeframe=true;
	    $currentFrameId = $attr->fieldSet->id;
	    $currentFrame = $attr->fieldSet;
	    $v++;
	  }
	}
      } else {
	$goodvalue=((($value != "") || ( $attr->type=="array") || $attr->getOption("showempty") ) &&
		    ($attr->mvisibility != "H") && ($attr->mvisibility != "I") && ($attr->mvisibility != "O") && (! $attr->inArray()));
	if (( $attr->type=="array") && (!$attr->getOption("showempty"))) {
	  if (count($this->getAValues($attr->id))==0) $goodvalue=false;
	}
          
	
	if ($goodvalue) {
	    // detect first tab 
	    
            $toptab=$attr->getTab();
            if ($toptab) $tabonfly=($toptab->getOption("viewonfly")=="yes");
	    if ($tabonfly && (! $showonlytab)) {
	         $ut=$this->getUtag("lasttab");
                 if ($ut) $showonlytab=$ut->comment; 
	        elseif ($attr->fieldSet->id && $attr->fieldSet->fieldSet) {
	            $showonlytab=$attr->fieldSet->fieldSet->id;
	        }
	    }
	    $attrInNextTab=($tabonfly && $toptab && ($toptab->id != $showonlytab));
	    if (! $attrInNextTab) {
	        $viewtpl=$attr->getOption("viewtemplate");
	        if ($viewtpl ) {
	            if ($viewtpl=="none") {
	                $htmlvalue='';
	            } else {
	                if ($this->getZoneOption($viewtpl) == 'S') {
	                    $attr->setOption("vlabel","none");
	                }
	                $htmlvalue=sprintf("[ZONE FDL:VIEWTPL?id=%d&famid=%d&target=%s&zone=%s]",$this->id, $this->fromid,$target,$viewtpl);
	            }
	        } else {
	            if ((($value == "")&&($attr->type!="array")) || (($attr->type=="array")&& (count($this->getAValues($attr->id))==0))) $htmlvalue=$attr->getOption("showempty");
	            else $htmlvalue=$this->GetHtmlValue($attr,$value,$target,$ulink);
	        }
	    } else {
	        $htmlvalue=false; // display defer	    
        }
          
	    
	} else $htmlvalue="";

	if (($htmlvalue === false) || ($goodvalue)) {// to define when change frame
	    if ( $currentFrameId != $attr->fieldSet->id) {
	        if (($currentFrameId != "") && ($attr->fieldSet->mvisibility != "H")) $changeframe=true;
	    }
	}
	
      }
      //------------------------------
      // change frame if needed
        
      if ($changeframe)	{// to generate  fieldset
	$changeframe=false;
	if (($v+$nbimg) > 0) {// one value detected
	  $oaf=$this->getAttribute($currentFrameId);
	  $frames[$k]["frametext"]=($oaf && $oaf->getOption("vlabel")!="none")?ucfirst($this->GetLabel($currentFrameId)):"";
	  $frames[$k]["frameid"]=$currentFrameId;
	  $frames[$k]["bgcolor"]=$oaf?$oaf->getOption("bgcolor",false):false;
	        
	  $frames[$k]["tag"]="";
	  $frames[$k]["TAB"]=false;
	  if (($currentFrame->fieldSet->id!="")&&($currentFrame->fieldSet->id!="FIELD_HIDDENS")) {
	    $frames[$k]["tag"]="TAG".$currentFrame->fieldSet->id;
	    $frames[$k]["TAB"]=true;
	    $ttabs[$currentFrame->fieldSet->id]=array("tabid"=>$currentFrame->fieldSet->id,
						      "tabtitle"=>ucfirst($currentFrame->fieldSet->getLabel()));
	  }
	  $frames[$k]["viewtpl"]=($frametpl!=""); 
	  $frames[$k]["zonetpl"]=($frametpl!="")?sprintf("[ZONE FDL:VIEWTPL?id=%d&famid=%d&target=%s&zone=%s]",$this->id, $this->fromid,$target,$frametpl):'';
                
                
	  $frames[$k]["rowspan"]=$v+1; // for images cell
	  $frames[$k]["TABLEVALUE"]="TABLEVALUE_$k";

	  $this->lay->SetBlockData($frames[$k]["TABLEVALUE"],
				   $tableframe);
	  $frames[$k]["IMAGES"]="IMAGES_$k";
	  $this->lay->SetBlockData($frames[$k]["IMAGES"],
				   $tableimage);
            $frames[$k]["notloaded"]=false;
	if ($oaf->type=="frame" &&(count($tableframe)+count($tableimage))==0) {
	    $frames[$k]["viewtpl"]=true;
	    $frames[$k]["zonetpl"]=_("Loading...");
            $frames[$k]["notloaded"]=true;
	} 
	  unset($tableframe);
	  unset($tableimage);
	  $tableframe=array();
	  $tableimage=array();
	  $k++;
	}
	$v=0;
	$nbimg=0;
      }
      if ($htmlvalue===false) {
          $goodvalue=false;
          if ( $currentFrameId != $attr->fieldSet->id) {
              if ( ($attr->fieldSet->mvisibility != "H") && ($attr->fieldSet->mvisibility != "I")) {
                  $changeframe=true;
                  $currentFrameId = $attr->fieldSet->id;
                  $currentFrame = $attr->fieldSet;
                  $v++;
              }
          }
      }

      //------------------------------
      // Set the table value elements
    
      if ($goodvalue)   {               
	switch ($attr->type)
	  {	      
	  case "image": 		  
	    $tableimage[$nbimg]["imgsrc"]=$htmlvalue;
	    $tableimage[$nbimg]["itarget"]=($action->Read("navigator","")=="NETSCAPE")?"_self":"_blank";
	    $width=$attr->getOption("iwidth","80px");
	    $tableimage[$nbimg]["imgwidth"]=$width;
	    if (strstr($htmlvalue,'EXPORTFILE'))   $tableimage[$nbimg]["imgthumbsrc"]=$htmlvalue."&width=".intval($width);
	    else $tableimage[$nbimg]["imgthumbsrc"]=$htmlvalue;
	    break;
	  default : 
	    $tableframe[$v]["nonelabel"]=false;
	    $tableframe[$v]["normallabel"]=true;
	    $tableframe[$v]["uplabel"]=false;
	    $tableframe[$v]["value"]=$htmlvalue;
	    break;
		
	  }
	  
	if (($attr->fieldSet->mvisibility!="H")&&($htmlvalue!=="" || $goodvalue)) {
	  $currentFrameId = $attr->fieldSet->id;
	  $currentFrame = $attr->fieldSet;
	}


	
	// print name except image (printed otherthere)
	if ($attr->type != "image") {	
	  $tableframe[$v]["wvalue"]=(($attr->type == "array")&&($attr->getOption("vlabel")=="up" || $attr->getOption("vlabel")=="none"))?"1%":"30%"; // width
	  $tableframe[$v]["ndisplay"]="inline";
	
	  if ($attr->getOption("vlabel")=="none") {
	    $tableframe[$v]["nonelabel"]=true;
	    $tableframe[$v]["normallabel"]=false;	    
	  } else if ($attr->getOption("vlabel")=="up") {
	    if ($attr->type == "array") { // view like none label
	      $tableframe[$v]["nonelabel"]=true;
	      $tableframe[$v]["normallabel"]=false;
	    } else {
	      $tableframe[$v]["normallabel"]=false;
	      $tableframe[$v]["uplabel"]=true;
	    }
	  }
	  $tableframe[$v]["name"]=$this->GetLabel($attr->id);
	  if ( ($attr->type == "htmltext") && (count($tableframe)==1)) {
	    $keys=array_keys($listattr);
	    $na=$listattr[$keys[$iattr]]; // next attribute
	    if ($na->fieldSet->id != $attr->fieldSet->id) { // only when only one attribute in frame
	      $tableframe[$v]["ndisplay"]="none";
	      $tableframe[$v]["wvalue"]="1%";
	    }
	  }
	  

	  $tableframe[$v]["classback"]=($attr->usefor=="O")?"FREEDOMOpt":"FREEDOMBack1";
	  $v++;
	} else	{
	  $tableimage[$nbimg]["imgalt"]=$this->GetLabel($attr->id);
	  $nbimg++;
	}	      
      }
    }

    if (($v+$nbimg) > 0) // // last fieldset
      {
	$oaf=$this->getAttribute($currentFrameId);
	if ($oaf)	$frames[$k]["frametext"]=($oaf->getOption("vlabel")!="none")?ucfirst($this->GetLabel($currentFrameId)):"";
	else $frames[$k]["frametext"]='';
	$frames[$k]["frameid"]=$currentFrameId;
	$frames[$k]["tag"]="";
	$frames[$k]["TAB"]=false;
	$frames[$k]["viewtpl"]=($frametpl!="");
	$frames[$k]["zonetpl"]=($frametpl!="")?sprintf("[ZONE FDL:VIEWTPL?id=%d&famid=%d&target=%s&zone=%s]",$this->id, $this->fromid,$target,$frametpl):'';

	$frames[$k]["bgcolor"]=$oaf?$oaf->getOption("bgcolor",false):false;
	if (($currentFrame->fieldSet->id!="")&&($currentFrame->fieldSet->id!="FIELD_HIDDENS")) {
	  $frames[$k]["tag"]="TAG".$currentFrame->fieldSet->id;
	  $frames[$k]["TAB"]=true;
	  $ttabs[$currentFrame->fieldSet->id]=array("tabid"=>$currentFrame->fieldSet->id,
						    "tabtitle"=>ucfirst($currentFrame->fieldSet->getLabel()));
	}
	$frames[$k]["rowspan"]=$v+1; // for images cell
	$frames[$k]["TABLEVALUE"]="TABLEVALUE_$k";

	$this->lay->SetBlockData($frames[$k]["TABLEVALUE"],
				 $tableframe);
	
	$frames[$k]["IMAGES"]="IMAGES_$k";
	$this->lay->SetBlockData($frames[$k]["IMAGES"],
				 $tableimage);
            $frames[$k]["notloaded"]=false;
	if ($oaf->type=="frame" &&(count($tableframe)+count($tableimage))==0) {
            $frames[$k]["viewtpl"]=true;
            $frames[$k]["zonetpl"]=_("Loading...");
            $frames[$k]["notloaded"]=true;
        } 
      }
    // Out
    $this->lay->SetBlockData("TABLEBODY",$frames);
    $this->lay->SetBlockData("TABS",$ttabs);
    $this->lay->Set("ONETAB",count($ttabs)>0);
    $this->lay->Set("NOTAB",($target=="mail")||$onlytab);
    $this->lay->Set("docid",$this->id);

    if (count($ttabs)>0)   {
            $this->lay->Set("firsttab",false); 
            $ut=$this->getUtag("lasttab");
            if ($ut)  $firstopen=$ut->comment; // last memo tab
            else $firstopen=false;
            foreach ($ttabs as $k=>$v) {
                $oa=$this->getAttribute($k);
                if ($oa->getOption("firstopen")=="yes")  $this->lay->set("firsttab",$k);
                if ($firstopen == $oa->id) $this->lay->Set("firsttab",$k);
            }
            
        } 
    
  }
  
  /**
   * write layout for thumb view
   */
  function viewthumbcard($target="finfo",$ulink=true,$abstract=true) {
    $this->viewabstractcard($target,$ulink,$abstract);
    $this->viewprop($target,$ulink,$abstract);
    $this->lay->set("iconsrc",$this->getIcon());
    $state=$this->getState();
    if ($state != "") $this->lay->set("state",_($state));
    else $this->lay->set("state","");
  }  

  /**
   *  layout for view answers
   */
  function viewanswers($target="finfo",$ulink=true,$abstract=true) {    
    if (!$this->isAlive()) $err=(sprintf(_("unknow document reference '%s'"),GetHttpVars("docid")));
    if ($err=="") $err=$this->control("wask");
    if ($err) {
      $this->lay->template=$err;
      return;
    }

    $answers=$this->getWasks(false);
  
    foreach ($answers as $ka=>$ans) {
      $utags=$this->searchUTags("ASK_".$ans["waskid"],false,true);
      $wask=new_doc($this->dbaccess,$ans["waskid"]);
      $wask->set($this);
    
      $taguid=array();

      $t=array();
      foreach ($utags as $k=>$v) {
	$taguid[]=$v["uid"];
	$t[$k]=$v;
	$t[$k]["label"]=$wask->getAskLabel($v["comment"]);
	$t[$k]["ask"]=$wask->getvalue("was_ask");
      }

      uasort($t,array (get_class($this), "_cmpanswers"));
      $prevc='';$odd=0;
      foreach ($t as $k=>$v) {
	if ($v["comment"]!=$prevc) {
	  $prevc=$v["comment"];
	  $odd++;
	}
	$t[$k]["class"]=(($odd%2)==0)?"evenanswer":"oddanswer";
      }

      // find user not answered    
      $ru=$wask->getUsersForAcl('answer'); // all users must answered
      $una=array_diff(array_keys($ru),$taguid);


      $tna=array();

      $tuna=array();
      foreach ($una as $k=>$v) {
	$tuna[$v]=$ru[$v]["login"];      
      }

      asort($tuna,SORT_STRING);
      foreach ($tuna as $k=>$v) {
	$tna[]=array("login"=>$ru[$k]["login"],
		     "fn"=>$ru[$k]["firstname"],
		     "ln"=>$ru[$k]["lastname"]);
      }

      $this->lay->setBlockData("ANSWERS".$wask->id,$t);
      $this->lay->setBlockData("NOTANS".$wask->id,$tna);
      if ($title!="") $title.=', ';
      $title.=$wask->getTitle();
   
      $this->lay->set("asktitle",$title);
      $tw[]=array("waskid"=>$wask->id,
		  "nacount"=>sprintf(_("number of waiting answers %d"),count($una)),
		  "count"=>(count($t)>1)?sprintf(_("%d answers"),count($t)):sprintf(_("%d answer"),count($t)),
		  "ask"=>$wask->getValue("was_ask"));
    }
    $this->lay->setBlockData("WASK",$tw);
    $this->lay->set("docid",$this->id);
  }
  
  /**
   * to sort answer by response
   */
  static function _cmpanswers($a,$b) {  
    return strcasecmp($a["comment"].$a["uname"],$b["comment"].$b["uname"]);
  }   

  /**
   * write layout for properties view
   */
  function viewproperties($target="finfo",$ulink=true,$abstract=true) {
    global $action;
    $this->viewprop($target,$ulink,$abstract);
    $this->lay->set("iconsrc",$this->getIcon());
    $fdoc=$this->getFamDoc();
    $this->lay->Set("ficonsrc", $fdoc->getIcon());
    $owner = new User("", abs($this->owner));
    $this->lay->Set("username", $owner->firstname." ".$owner->lastname);
    $this->lay->Set("userid", $owner->fid);
    if ($this->locked== -1) {
      $this->lay->Set("lockedid",false);
    } else {
      $user = new User("", abs($this->locked));
      // $this->lay->Set("locked", $user->firstname." ".$user->lastname);
      $this->lay->Set("lockedid", $user->fid);
    }
    $state=$this->getState();
    if ($state != "") {
      if (($this->locked == -1)||($this->lmodify != 'Y'))  $this->lay->Set("state", _($state));
      else $this->lay->Set("state", sprintf(_("current (<i>%s</i>)"),_($state)));
    } else $this->lay->set("state",_("no state"));
    if (is_numeric($this->state) && ($this->state>0) && (! $this->wid)) {
      $this->lay->set("freestate",$this->state);
    }
    else $this->lay->set("freestate",false);
    $this->lay->set("setname",($this->name=="")&& $action->parent->Haspermission("FREEDOM_MASTER","FREEDOM"));
    $this->lay->set("hasrevision",($this->revision > 0));
    $this->lay->Set("moddate", strftime ("%d/%m/%Y %H:%M:%S",$this->revdate));
    $this->lay->set("moddatelabel",_("last modification date"));
    if ($this->locked == -1) {
      if ($this->doctype=='Z') $this->lay->set("moddatelabel",_("suppression date"));
      else $this->lay->set("moddatelabel",_("revision date"));
    }
    if (GetParam("CORE_LANG") == "fr_FR") { // date format depend of locale
      $this->lay->Set("revdate", strftime ("%a %d %b %Y %H:%M",$this->revdate));
    } else {
      $this->lay->Set("revdate", strftime ("%x %T",$this->revdate));
    }
    $this->lay->Set("version", $this->version);
    
    $this->lay->Set("profid", abs($this->profid));
    if ((abs($this->profid) > 0) && ($this->profid != $this->id)) {
      $pdoc = new_Doc($this->dbaccess, abs($this->profid));
      $this->lay->Set("profile", $pdoc->title);
    } else {
      if ($this->profid == 0)
	$this->lay->Set("profile", _("no access control"));
      else {
	if ($this->dprofid==0) $this->lay->Set("profile", _("specific control"));
	else {
	
	  $this->lay->Set("profile", _("dynamic control"));
	  $this->lay->Set("profid", abs($this->dprofid));
	}
      }
    }
    if ($this->cvid == 0) {
      $this->lay->Set("cview", _("no view control"));
    } else {  
      $cvdoc= new_Doc($this->dbaccess, $this->cvid);
      $this->lay->Set("cview", $cvdoc->title);
    }
    if ($this->prelid == 0) {
      $this->lay->Set("prel", _("no folder"));
    } else {  
      $cvdoc= new_Doc($this->dbaccess, $this->prelid);
      $this->lay->Set("prel", $cvdoc->title);
    }
    if ($this->allocated == 0) {
      $this->lay->Set("allocate", _("no allocate"));      
      $this->lay->Set("allocateid", false);
    } else {
      $user = new User("", ($this->allocated));
      $this->lay->Set("allocate", $user->firstname." ".$user->lastname);
      $this->lay->Set("allocateid", $user->fid);
    }

	
    if ($this->forumid=="") {
      $this->lay->Set("forum", _("forum disallowed"));
      $this->lay->Set("hforum", false);
    } else if ($this->forumid===0) {
      $this->lay->Set("forum", _("forum allowed"));
      $this->lay->Set("hforum", false);
    } else {
      if ($this->forumid>0) $this->lay->Set("forum", _("forum opened"));
      else $this->lay->Set("forum", _("forum closed"));
      $this->lay->Set("hforum", true);
      $this->lay->Set("forumid", abs($this->forumid));
    }
      
	
    $tms=$this->getAttachedTimers();
  
    $this->lay->Set("Timers", (count($tms)>0));
    
  }
  /**
   * write layout for abstract view
   */
  function viewabstractcard($target="finfo",$ulink=true,$abstract=true) {  
    $listattr = $this->GetAbstractAttributes();
 
    $tableframe=array();
 
    foreach($listattr as $i=>$attr) {
      //------------------------------
      // Compute value elements
	  
      $value = chop($this->GetValue($i));

    


      if (($value != "") && ($attr->mvisibility != "H") && ($attr->mvisibility != "I"))   {
		
	switch ($attr->type)
	  {
	  case "image": 
		  
	    $img = "<IMG align=\"absbottom\" height=\"30px\" SRC=\"".
	      $this->GetHtmlValue($listattr[$i],$value,$target,$ulink).
	      "&height=30\">";
	    $tableframe[]=array("name"=>$attr->getLabel(),
				"aid"=>$attr->id,
				"value"=>$img);
	    break;
	  default : 
	    // print values
	   
	    $tableframe[]=array("name"=>$attr->getLabel(),
				"aid"=>$attr->id,
				"value"=>$this->GetHtmlValue($listattr[$i],$value,$target,$ulink=1,-1,true,true));
	
	    break;
	  }
	      
      
      }
    }



    $this->lay->SetBlockData("TABLEVALUE",$tableframe);
  




  }
    
    // -----------------------------------
    final public function viewattr($target = "_self", $ulink = true, $abstract = false, $viewhidden = false)
    {
        $listattr = $this->GetNormalAttributes();
        
        // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value
        foreach ( $listattr as $k => $v ) {
            $value = chop($this->GetValue($v->id));
            
            //------------------------------
            // Set the table value elements
            
            $this->lay->Set("S_" . strtoupper($v->id), ($value != ""));
            // don't see  non abstract if not
            if ((($v->mvisibility == "H") && (!$viewhidden)) || ($v->mvisibility == "I") || (($abstract) && (!$v->isInAbstract))) {
                $this->lay->Set("V_" . strtoupper($v->id), "");
                $this->lay->Set("L_" . strtoupper($v->id), "");
            } else {
                if ($target == "ooo") {
                    if ($v->type == "array") {
                        $tva = $this->getAValues($v->id);
                        
                        $tmkeys = array();
                        foreach ( $tva as $kindex => $kvalues ) {
                            foreach ( $kvalues as $kaid => $va ) {
                                $oa = $this->getAttribute($kaid);
                                if ($oa->getOption("multiple") == "yes") {
                                    // second level
                                    $oa->setOption("multiple","no"); //  needto have values like first level
                                    $values = explode("<BR>", $va);
                                    $ovalues = array();
                                    foreach ( $values as $ka => $va ) {
                                        $ovalues[] = $this->GetOOoValue($oa, $va);
                                    }
                                    //print_r(array($oa->id=>$ovalues));
                                    $tmkeys[$kindex]["V_" . strtoupper($kaid)] = $ovalues;
                                    $oa->setOption("multiple","yes"); //  needto have values like first level
                                } else {
                                    $tmkeys[$kindex]["V_" . strtoupper($kaid)] = $this->GetOOoValue($oa, $va);
                                }
                            }
                        }
                        //print_r($tmkeys);
                        $this->lay->setRepeatable($tmkeys);
                    } else {
                        $ovalue=$this->GetOOoValue($v, $value);
                        if ($v->isMultiple()) $ovalue=str_replace("<text:tab/>",', ', $ovalue);
                        $this->lay->Set("V_" . strtoupper($v->id),$ovalue);
                       // print_r(array("V_".strtoupper($v->id)=>$this->GetOOoValue($v, $value),"raw"=>$value));
                        if ((!$v->inArray()) && ($v->getOption("multiple") == "yes")) {
                            $values = $this->getTValue($v->id);
                            $ovalues = array();
                            $v->setOption("multiple","no"); 
                            foreach ( $values as $ka => $va ) {
                                $ovalues[] = $this->GetOOoValue($v, $va);
                            }
                            $v->setOption("multiple","yes"); 
                            //print_r(array("V_".strtoupper($v->id)=>$ovalues,"raw"=>$values));
                            $this->lay->setColumn("V_" . strtoupper($v->id), $ovalues);
                        } else {
                            //$this->lay->Set("V_" . strtoupper($v->id), $this->GetOOoValue($v, $value));
                            
                        }
                    }
                } else
                    $this->lay->Set("V_" . strtoupper($v->id), $this->GetHtmlValue($v, $value, $target, $ulink));
                $this->lay->Set("L_" . strtoupper($v->id), $v->getLabel());
            }
        }
        $listattr = $this->GetFieldAttributes();
        
        // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value
        foreach ( $listattr as $k => $v ) {
            $this->lay->Set("L_" . strtoupper($v->id), $v->getLabel());
        }
    
    }


  // view doc properties
  final public function viewprop($target="_self",$ulink=true,$abstract=false) {
    foreach($this->fields as $k=>$v) {
      if($target=='ooo') $this->lay->Set(strtoupper($v),($this->$v===null)?false:str_replace(array("<",">",'&'),array("&lt;","&gt;","&amp;"),$this->$v));
      else $this->lay->Set(strtoupper($v),($this->$v===null)?false:$this->$v);
    }
    if($target=='ooo') $this->lay->Set("V_TITLE",$this->lay->get("TITLE"));
    else $this->lay->Set("V_TITLE",$this->getDocAnchor($this->id,$target,$ulink,false,false));
  }

  /**
	 * affect a logical name that can be use as unique reference of a document independant of database
	 * @param string
	 * @return string error message if cannot be
	 */
	function setLogicalIdentificator($name) {
		if ($name) {
			if (!preg_match("/^[A-Z][0-9A-Z:_-]*$/i", $name)) {
				return(sprintf(_("name must containt only alphanumeric characters: invalid  [%s]"), $name));
			}
			elseif (!$this->isAffected()) {
				return (sprintf(_("Cannot set logical name %s because object is not affected"), $name));
			}
			elseif ($this->isAffected() && ($this->name != "") && ($this->doctype != 'Z')) {
				return (sprintf(_("Logical name %s already set for %s"), $name, $this->title));
			}
			else {
				// verify not use yet
				$d = getTDoc($this->dbaccess, $name);
				if ($d && $d["doctype"] != 'Z') {
					return sprintf(_("Logical name %s already use in document %s"), $name, $d["title"]);
				}
				else {
					$this->name = $name;
					$err = $this->modify(true, array("name"), true);
					if ($err != "") {
						return $err;
					}
				}
			}
		}
	}
  /**
   * view only option values
   * @param int $dirid   directory to place doc if new doc
   * @param bool $onlyopt if true only optionnal attributes are displayed
   */
  final public function viewoptcard($target="_self",$ulink=true,$abstract=false) {
    return $this->viewbodycard($target,$ulink,$abstract,true);
  }

  /**
   * edit only option
   * @param int $dirid   directory to place doc if new doc
   * @param bool $onlyopt if true only optionnal attributes are displayed
   */
  final public function editoptcard($target="_self",$ulink=true,$abstract=false) {
    return $this->editbodycard($target,$ulink,$abstract,true);
  }
  /**
   * value for edit interface
   * @param bool $onlyopt if true only optionnal attributes are displayed
   */
  function editbodycard($target="_self",$ulink=true,$abstract=false,$onlyopt=false) {
    include_once("FDL/editutil.php");
    include_once("FDL/Class.SearchDoc.php");
 
    $docid = $this->id;        // document to edit	        
    // ------------------------------------------------------
    //  new or modify ?
    if ($docid == 0)    {	
      // new document
      if ($this->fromid > 0) {
	$cdoc= $this->getFamDoc();
	$this->lay->Set("title", sprintf(_("new %s"),$cdoc->title));     
      }	
    }  else    {   
      // when modification 
      if (! $this->isAlive()) $action->ExitError(_("document not referenced"));	       
      $this->lay->Set("title", $this->title);	
    } 
    $this->lay->Set("id", $docid);
    $this->lay->Set("classid", $this->fromid);


    // search inline help
    $s=new SearchDoc($this->dbaccess,"HELPPAGE");
    $s->addFilter("help_family='%d'",$this->fromid);
    $help=$s->search();
    $helpid=false;
    $helpattr=array();
    if ($s->count() > 0) {
      $helpid=$help[0]["id"];
      $helpattr=$this->_val2array($help[0]["help_sec_key"]);
    }
    // ------------------------------------------------------
    // Perform SQL search for doc attributes
    // ------------------------------------------------------	        
  
  
 
    $frames=array();
    $listattr = $this->GetInputAttributes($onlyopt);

  
    $nattr = count($listattr); // number of attributes
    
    $k=0; // number of frametext
    $v=0;// number of value in one frametext
    $currentFrameId="";
    $changeframe=false;
    $ih = 0; // index for hidden values
    $thidden =array();
    $tableframe=array();
    $ttabs=array();

    $iattr=0;

    foreach($listattr as $i=>$attr) {
      $iattr++;
    
      // Compute value elements	
      if ($docid > 0) $value = $this->GetValue($attr->id);
      else {
	$value = $this->GetValue($attr->id);
	//	$value = $this->GetValueMethod($this->GetValue($listattr[$i]->id));
      }
      if (!$attr->fieldSet) {
          addWarningMsg(sprintf(_("unknow set for attribute %s %s"),$attr->id, $attr->getLabel()));
          continue;
      }
      $frametpl=$attr->fieldSet->getOption("edittemplate");
      
      if ( $currentFrameId != $attr->fieldSet->id) {
	if ($frametpl) {
	  $changeframe=true;
	  $currentFrameId=$attr->fieldSet->id;
	  $v++;
	} elseif ($currentFrameId != "") $changeframe=true;
      }
      if ( $changeframe){  // to generate final frametext
	$changeframe=false;
	if ($v > 0 ) {// one value detected	  	      
	  $frames[$k]["frametext"]=ucfirst($this->GetLabel($currentFrameId));
	  $frames[$k]["frameid"]=$currentFrameId;
	  $frames[$k]["tag"]="";
	  $frames[$k]["TAB"]=false;
          $frames[$k]["edittpl"]=($frametpl!="");
          $frames[$k]["zonetpl"]=($frametpl!="")?sprintf("[ZONE FDL:EDITTPL?id=%d&famid=%d&zone=%s]",$this->id, $this->fromid,$frametpl):'';
          
	  $oaf=$this->getAttribute($currentFrameId);	      
	  $frames[$k]["bgcolor"]=$oaf?$oaf->getOption("bgcolor",false):false;
	  if (($currentFrame->fieldSet->id!="")&&($currentFrame->fieldSet->id!="FIELD_HIDDENS")) {
	    $frames[$k]["tag"]="TAG".$currentFrame->fieldSet->id;
	    $frames[$k]["TAB"]=true;
	    $ttabs[$currentFrame->fieldSet->id]=array("tabid"=>$currentFrame->fieldSet->id,
						      "tabtitle"=>ucfirst($currentFrame->fieldSet->getLabel()));
	  }
	  $frames[$k]["TABLEVALUE"]="TABLEVALUE_$k";
	  $this->lay->SetBlockData($frames[$k]["TABLEVALUE"],
				   $tableframe);
	  unset($tableframe);
	  $tableframe=array();
	  $k++;
	}
	$v=0;
      }
      if (! $frametpl) {
	//------------------------------
	// Set the table value elements
           
	$currentFrameId = $listattr[$i]->fieldSet->id;
	$currentFrame = $listattr[$i]->fieldSet;
	if ( ($listattr[$i]->mvisibility == "H") || ($listattr[$i]->mvisibility == "R") ) {
	  // special case for hidden values
	  $thidden[$ih]["hname"]= "_".$listattr[$i]->id;
	  $thidden[$ih]["hid"]= $listattr[$i]->id;
	  if (($value == "")&&($this->id==0)) $thidden[$ih]["hvalue"] = GetHttpVars($listattr[$i]->id);
	  else $thidden[$ih]["hvalue"]=chop(htmlentities($value,ENT_COMPAT,"UTF-8"));
               
               
	  $thidden[$ih]["inputtype"]=getHtmlInput($this,
						  $listattr[$i],
						  $value,"","",true);
	  $ih++;

	} else {
	  $tableframe[$v]["value"]=chop(htmlentities($value,ENT_COMPAT,"UTF-8"));
	  $label = $listattr[$i]->getLabel();
	  $tableframe[$v]["attrid"]=$listattr[$i]->id;
	  $tableframe[$v]["name"]=ucfirst($label);

	  if ($listattr[$i]->needed ) $tableframe[$v]["labelclass"]="FREEDOMLabelNeeded";
	  else $tableframe[$v]["labelclass"]="FREEDOMLabel";
	  $elabel=$listattr[$i]->getoption("elabel");
	  $elabel=str_replace("'","&rsquo;",$elabel);
	  $tableframe[$v]["elabel"]=ucfirst(str_replace('"',"&rquot;",$elabel));
	  $tableframe[$v]["helpid"]=$helpid;
	  $tableframe[$v]["ehelp"]=($helpid!=false) && (in_array($listattr[$i]->id,$helpattr));

	  $tableframe[$v]["name"]=ucfirst($label);
	  $tableframe[$v]["classback"]=($attr->usefor=="O")?"FREEDOMOpt":"FREEDOMBack1";
	  //$tableframe[$v]["name"]=$action->text($label);

	  $tableframe[$v]["SINGLEROW"]=true;


	  $vlabel=$listattr[$i]->getOption("vlabel");
	  if ((($listattr[$i]->type=="array")&&($vlabel!='left'))||(($listattr[$i]->type=="htmltext")&&($vlabel!='left'))||($vlabel=='up')||($vlabel=='none')) $tableframe[$v]["SINGLEROW"]=false;

	  $tableframe[$v]["viewlabel"]=(($listattr[$i]->type != "array")&&($vlabel!='none'));
	  $edittpl=$listattr[$i]->getOption("edittemplate");
	  if ($edittpl) {
	    if ($edittpl=="none") {
	      unset ($tableframe[$v]);
	    } else {
	      if ($this->getZoneOption($edittpl) == 'S') {
		$tableframe[$v]["SINGLEROW"]=false;
		$tableframe[$v]["viewlabel"]=false;
	      }
	      $tableframe[$v]["inputtype"]=sprintf("[ZONE FDL:EDITTPL?id=%d&famid=%d&zone=%s]",$this->id, $this->fromid,$edittpl);
	    }
	  } else {
	    $tableframe[$v]["inputtype"]=getHtmlInput($this,
						      $listattr[$i],
						      $value);
	  }
	  $v++;
	}
      }
    }
  
    // Out
    if ($v > 0 ) {// latest fieldset	  	      
      $frames[$k]["frametext"]=ucfirst($this->GetLabel($currentFrameId));
      $frames[$k]["frameid"]=$currentFrameId;
      $frames[$k]["TABLEVALUE"]="TABLEVALUE_$k";
      $frames[$k]["tag"]="";
      $frames[$k]["TAB"]=false;      
      $frames[$k]["edittpl"]=($frametpl!="");
      $frames[$k]["zonetpl"]=($frametpl!="")?sprintf("[ZONE FDL:EDITTPL?id=%d&famid=%d&zone=%s]",$this->id, $this->fromid,$frametpl):'';
          
      $oaf=$this->getAttribute($currentFrameId);	      
      $frames[$k]["bgcolor"]=$oaf?$oaf->getOption("bgcolor",false):false;
      if (($currentFrame->fieldSet->id!="")&&($currentFrame->fieldSet->id!="FIELD_HIDDENS")) {
	$frames[$k]["tag"]="TAG".$currentFrame->fieldSet->id;
	$frames[$k]["TAB"]=true;
	$ttabs[$currentFrame->fieldSet->id]=array("tabid"=>$currentFrame->fieldSet->id,
						  "tabtitle"=>ucfirst($currentFrame->fieldSet->getLabel()));
      }
      $this->lay->SetBlockData($frames[$k]["TABLEVALUE"],
			       $tableframe);
	    
    }
    $this->lay->SetBlockData("HIDDENS",$thidden);
    $this->lay->SetBlockData("TABLEBODY",$frames);    
    $this->lay->SetBlockData("TABS",$ttabs);
    $this->lay->Set("ONETAB",count($ttabs)>0);
    $this->lay->Set("fromid",$this->fromid);
    $this->lay->Set("docid",$this->id);
    if (count($ttabs)>0)   {
        $this->lay->Set("firsttab",false);
        $ut=$this->getUtag("lasttab");
        if ($ut)  $firstopen=$ut->comment; // last memo tab
        else $firstopen=false;
        
        foreach ($ttabs as $k=>$v) {
            $oa=$this->getAttribute($k);
            if ($oa->getOption("firstopen")=="yes")  $this->lay->Set("firsttab",$k);
            if ($firstopen == $oa->id) $this->lay->Set("firsttab",$k);
        }
         
    }
    
  
  }

  /**
   * create input fields for attribute document
   * @param bool $withtd set to false if don't wan't <TD> tag in the middle
   */
  final public function editattr($withtd=true) {
 
    include_once("FDL/editutil.php");
    $listattr = $this->GetNormalAttributes();
        

    // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value

    foreach($listattr as $k=>$v) {
      //------------------------------
      // Set the table value elements
      $value = chop($this->GetValue($v->id));
      if ($v->mvisibility=="R") $v->mvisibility="H"; // don't see in edit mode
      $this->lay->Set("V_".strtoupper($v->id),
		      getHtmlInput($this,
				   $v, 
				   $value,"","",(!$withtd)));
      if ($v->needed == "Y") $this->lay->Set("L_".strtoupper($v->id),"<B>".$v->getLabel()."</B>");
      else $this->lay->Set("L_".strtoupper($v->id),$v->getLabel());
      $this->lay->Set("W_".strtoupper($v->id),($v->mvisibility!="H"));
      
    }
  
    $listattr = $this->GetFieldAttributes();

    // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value

    foreach($listattr as $k=>$v) {
      $this->lay->Set("L_".strtoupper($v->id),$v->getLabel());  
    }


    $this->setFamidInLayout();
  }


  final public function setFamidInLayout() {
    // add IDFAM_ attribute in layout
    global $tFamIdName;

    if (! isset($tFamIdName))  getFamIdFromName($this->dbaccess,"-");
  
    reset($tFamIdName);
    foreach($tFamIdName as $k=>$v) {
      $this->lay->set("IDFAM_$k", $v);
    }
  }
  /**
   * get vault file name or server path of filename
   * @param string $idAttr identificator of file attribute 
   * @param bool $path false return original file name (basename) , true the real path
   * @param int $index in case of array of files
   * @return string the file name of the attribute
   */
  final public function vault_filename($attrid,$path=false,$index=-1) {
    if ($index == -1)  $fileid= $this->getValue($attrid);
    else $fileid= $this->getTValue($attrid,'',$index);
    return $this->vault_filename_fromvalue($fileid,$path);
  }

  /**
   * get vault file name or server path of filename
   * @param string $fileid value of file attribute
   * @param bool $path false return original file name (basename) , true the real path
   * @return string the file name of the attribute
   */
  final public function vault_filename_fromvalue($fileid,$path=false) {   
    $fname="";
    if (preg_match(PREGEXPFILE, $fileid, $reg)) {	 
      // reg[1] is mime type
      $vf = newFreeVaultFile($this->dbaccess);
      if ($vf -> Show ($reg[2], $info) == "") {
	if ($path) $fname = $info->path;
	else $fname = $info->name;
      }    
    } 
    return $fname;
  }



  /**
   * get vault file name or server path of filename
   * @param NormalAttribute $idAttr identificator of file attribute 
   * @param bool false return original file name (basename) , true the real path
   * @return array of properties :
          [0]=>
            [name] => TP_Users.pdf
            [size] => 179435
            [public_access] => 
            [mime_t] => PDF document, version 1.4
            [mime_s] => application/pdf
            [cdate] => 24/12/2010 11:44:36
            [mdate] => 24/12/2010 11:44:41
            [adate] => 25/03/2011 08:13:34
            [teng_state] => 1
            [teng_lname] => pdf
            [teng_vid] => 15
            [teng_comment] => 
            [path] => /var/www/eric/vaultfs/1/16.pdf
            [vid] => 16

   */
  final public function vault_properties(NormalAttribute $attr) {
    if ($attr->inArray()) $fileids= $this->getTValue($attr->id);
    else $fileids[]= $this->getValue($attr->id);
   
    $tinfo=array();
    foreach ($fileids as $k=>$fileid) {
      if (preg_match(PREGEXPFILE, $fileid, $reg)) {	 
	// reg[1] is mime type
	$vf = newFreeVaultFile($this->dbaccess);
	if ($vf->Show ($reg[2], $info) == "") {	
	  $tinfo[$k]= get_object_vars($info);
	  $tinfo[$k]["vid"]=$reg[2];
	}
      }
    } 

    return $tinfo;
  }

  /**
   * return a property of vault file value
   * 
   * @param string $filesvalue the file value : like application/pdf|12345
   * @param string $key one of property id_file, name, size, public_access, mime_t, mime_s, cdate, mdate, adate, teng_state, teng_lname, teng_vid, teng_comment, path
   * @return string value of property or array of all properties if no key
   */
  final public function getFileInfo($filesvalue, $key="") {    
    if (! is_string(  $filesvalue)) return false;  
    if (preg_match(PREGEXPFILE, $filesvalue, $reg)) {
      include_once("FDL/Lib.Vault.php");
      $vid=$reg[2];
      $info=vault_properties($vid);
      if (! $info) return false;
      if ($key != "") {
	if (isset($info->$key)) return $info->$key;
	else return sprintf(_("unknow %s file property"),$key);
      } else {
	return get_object_vars($info);
      }
    }
  }
  /**
   * 
   * @param string &$xml content xml (empty if $outfile is not empty
   * @param boolean $withfile include files in base64 encoded
   * @param string $outfile if not empty means content is put into this file
   * @param boolean $flat set to true if don't want structure
   * @param array $exportAttribute to export only a part of attributes
   * @return string error message (empty if no error)
   */
  public function exportXml(&$xml,$withfile=false,$outfile="",$wident=true, $flat=false,$exportAttributes=array()) {
      
    $lay=new Layout(getLayoutFile("FDL","exportxml.xml"));
    //$lay=&$this->lay;
    $lay->set("famname",strtolower($this->fromname));
    $lay->set("id",($wident?$this->id:''));
    $lay->set("name",$this->name);
    $lay->set("revision",$this->revision);
    $lay->set("version",$this->getVersion());
    $lay->set("state",$this->getState());
    $lay->set("title",str_replace(array("&",'<','>'),array("&amp;",'&lt;','&gt;'),$this->getTitle()));
    $lay->set("mdate",strftime("%FT%X",$this->revdate));
    $lay->set("flat",$flat);
    $la=$this->GetFieldAttributes();
    $level1=array();
            
    foreach ($la as $k=>$v) {
      if  ((!$v) || ($v->getOption("autotitle")=="yes") || ($v->usefor == 'Q')) unset($la[$k]);
    }
    $option->withFile=$withfile;
    $option->outFile=$outfile;
    $option->withIdentificator=$wident;
    $option->flat=$flat;
    $option->exportAttributes=$exportAttributes;
      
    foreach ($la as $k=>&$v) {
      if (($v->id != "FIELD_HIDDENS") && 
	  ($v->type=='frame' || $v->type=="tab") && 
	  ((!$v->fieldSet) || $v->fieldSet->id=="FIELD_HIDDENS")) {
	$level1[]=array("level"=>$v->getXmlValue($this,$option));
      } else {
	// if ($v)  $tax[]=array("tax"=>$v->getXmlSchema());
      }
    }
    $lay->setBlockData("top",$level1);
    if ($outfile) {
      if ($withfile) {
	$xmlcontent=$lay->gen();
	$fo=fopen($outfile,"w");
	$pos = strpos($xmlcontent, "[FILE64");
	$bpos=0;
	while ($pos !== false) {
	  if (fwrite($fo,substr($xmlcontent,$bpos,$pos-$bpos))) {
	    $bpos=strpos($xmlcontent, "]",$pos)+1;

	    $filepath=substr($xmlcontent,$pos+8,($bpos-$pos -9));

	    /* If you want to encode a large file, you should encode it in chunks that
	       are a multiple of 57 bytes.  This ensures that the base64 lines line up
	       and that you do not end up with padding in the middle. 57 bytes of data
	       fills one complete base64 line (76 == 57*4/3):*/
	    $ff=fopen($filepath,"r");
	    $size=6*1024*57;
	    while ($buf=fread($ff, $size)) {
	      fwrite($fo,base64_encode($buf));
	    }
	    $pos = strpos($xmlcontent, "[FILE64", $bpos);
	   
	  } else {
	    $err=sprintf(_("exportXml : cannot write file %s"),$outfile);
	    $pos=false;
	  }
	}
	if ($err=="") fwrite($fo,substr($xmlcontent,$bpos));
	fclose($fo);
      } else {
	if (file_put_contents($outfile,$lay->gen())===false) {
	  $err=sprintf(_("exportXml : cannot write file %s"),$outfile);
	}
      }
    }
    else {
      $xml=$lay->gen();
      return $err;
    }
    return $err;
  }
  
  // =====================================================================================
  // ================= Methods use for XML ======================
  final public function toxml($withdtd=false,$id_doc="")  {

    global $action;
    $doctype=$this->doctype; 
    
    $docid=intval($this->id);
    if ($id_doc==""){
      $id_doc=$docid;
    }

    $title=$this->title;
    $fromid=$this->fromid;
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $fam_doc=new_Doc($this->dbaccess,$this->fromid);
    $name=str_replace(" ","_",$fam_doc->title);


    if ($withdtd==true) {
      $dtd="<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\" ?>";
      $dtd.="<!DOCTYPE $name [";
      $dtd.=$this->todtd();
      $dtd.="]>";
    }
    else{ $dtd="";}

    $this->lay = new Layout("FDL/Layout/viewxml.xml", $action);
    $this->lay->Set("DTD",$dtd);
    $this->lay->Set("NOM_FAM",$name);
    $this->lay->Set("id_doc",$id_doc);
    $this->lay->Set("TITRE",$title);
    $this->lay->Set("ID_FAM",$fam_doc->name);  
    $this->lay->Set("revision",$this->revision);
    $this->lay->Set("revdate",$this->revdate);


    //$this->lay->Set("IDOBJECT",$docid);
    //$this->lay->Set("IDFAM",$fromid);
    //$idfam=$fam_doc->classname;
    //$this->lay->Set("TYPEOBJECT",$doctype);



    ////debut
    $listattr= $this->GetNormalAttributes();

    $frames= array();


    $nattr = count($listattr); // attributes list count


    $k=0; // number of frametext
    $v=0;// number of value in one frametext
    $currentFrameId="";

    $changeframe=false; // is true when need change frame
    $tableframe=array();
     

    $iattr=0;

    foreach($listattr as $i=>$attr) {
      $iattr++;

      if ((chop($listattr[$i]->id)!="") && ($listattr[$i]->id!="FIELD_HIDDENS")){

	//------------------------------
	// Compute value elements
	  
       
	if ( $currentFrameId != $listattr[$i]->fieldSet->id) {
	  if ($currentFrameId != "") $changeframe=true;
	}
	  


	//------------------------------
	// change frame if needed

	if (  // to generate  fiedlset
	    $changeframe)
	  {
	    $changeframe=false;
	    if ($v > 0) // one value detected
	      {
				      
		$frames[$k]["FIELD"]=$currentFrameId;
		$frames[$k]["ARGUMENT"]="ARGUMENT_$k";

		$this->lay->SetBlockData($frames[$k]["ARGUMENT"],
					 $tableframe);
		$frames[$k]["nom_fieldset"]=$this->GetLabel($currentFrameId);
		unset($tableframe);
		$tableframe=array();
		$k++;
	      }
	    $v=0;
      
	  }



	// Set the table value elements
	if (($iattr <= $nattr) && ($this->Getvalue($i)!="") )	{
	  $attrtype_idoc=false;
	  $attrtype_list=false;

	  if (strstr($listattr[$i]->type,"textlist")!=false){
	    $attrtype_list=true;
	  }
	  if ((strstr($listattr[$i]->type,"idoclist"))!=false){
	    $attrtype_list=true;
	    $attrtype_idoc=true;
	  }
	  if ((strstr($listattr[$i]->type,"idoc"))!=false){
	    $attrtype_idoc=true;
	  }
	  if($listattr[$i]->inArray()){
	    $attrtype_list=true;
	  }

	  if ($attrtype_list){
	    // $value=htmlspecialchars($this->GetValue($i));
	    $value=$this->GetValue($i);
	    $textlist=$this->_val2array($value);
	      
	    while ($text = each($textlist)){
	      $currentFrameId = $listattr[$i]->fieldSet->id;
	      $tableframe[$v]["id"]=$listattr[$i]->id;
	      if  ($attrtype_idoc){
		$tableframe[$v]["value"]=base64_decode($text[1]);
		$tableframe[$v]["type"]="idoc";
	      }
	      else{
		$tableframe[$v]["value"]=$text[1];
		$tableframe[$v]["type"]=base64_encode($listattr[$i]->type);
	      }
	      $tableframe[$v]["labelText"]=(str_replace(array("%","\""),
							array("","\\\""), $listattr[$i]->getLabel()));
	      //$tableframe[$v]["type"]=$listattr[$i]->type;
	      //$tableframe[$v]["visibility"]=$listattr[$i]->visibility;
	      //$tableframe[$v]["needed"]=$listattr[$i]->needed;
	      $v++;
	    }

	  }
		
	  else{
	  
	    if ($attrtype_idoc){
	      $value=base64_decode($this->GetValue($i));
	      $tableframe[$v]["type"]="idoc";
	      //printf($value);
	   
	    }
	    else{
	      $value=htmlspecialchars($this->GetValue($i));
	      $tableframe[$v]["type"]=base64_encode($listattr[$i]->type);
	    }

	    $currentFrameId = $listattr[$i]->fieldSet->id;
	    $tableframe[$v]["id"]=$listattr[$i]->id;
	    $tableframe[$v]["value"]=$value;
	    $tableframe[$v]["labelText"]=addslashes($listattr[$i]->getLabel());
	    //$tableframe[$v]["type"]=$listattr[$i]->type;
	    //$tableframe[$v]["visibility"]=$listattr[$i]->visibility;
	    //$tableframe[$v]["needed"]=$listattr[$i]->needed;
	    $v++;

	  }
	
	}


      }
    }
 


    if ($v > 0) // last fieldset
      {
				      
	$frames[$k]["FIELD"]=$currentFrameId;
	$frames[$k]["ARGUMENT"]="ARGUMENT_$k";

	$this->lay->SetBlockData($frames[$k]["ARGUMENT"],
				 $tableframe);
	$frames[$k]["nom_fieldset"]=$this->GetLabel($currentFrameId);
	unset($tableframe);
	$tableframe=array();
	$tableimage=array();
	$k++;
      }
 



 




    $this->lay->SetBlockData("FIELDSET",$frames);
    return $this->lay->gen();
  }
  
  final public function todtd() {


    global $action;
    $this->lay = new Layout("FDL/Layout/viewdtd.xml", $action);

    $fam_doc=$this->getFamDoc();
    $name=str_replace(" ","_",$fam_doc->title);
    $this->lay->Set("doctype",$this->doctype);
    $this->lay->Set("idfam",$this->fromid);
    $this->lay->Set("nom_fam",$name);
    $this->lay->Set("id_fam",$name);

    $listattr= $this->GetNormalAttributes();

    $frames= array();

    $nattr = count($listattr); // attributes list count

    $k=0; // number of frametext
    $v=0;// number of value in one frametext
    $currentFrameId="";

    $changeframe=false; // is true when need change frame
    $needed=false;
    $tableattrs=array();
    $tablesetting=array();
    $iattr=0;

    foreach($listattr as $i=>$attr) {
      $iattr++;
      //------------------------------
      // Compute value elements
	  
      if ( $currentFrameId != $listattr[$i]->fieldSet->id) {
	if ($currentFrameId != "") $changeframe=true;
      }

      //------------------------------
      // change frame if needed

      if (  // to generate  fiedlset
	  $changeframe)
	{
	  $changeframe=false;
	  


	  if ($v > 0) // one value detected
	    {
	      	     
	      $frames[$k]["name"]=$currentFrameId;
	      $elements[$k]["name"]=$currentFrameId;
	      if ($needed){
		$elements[$k]["name"].=", ";
	      }
	      else{
		$elements[$k]["name"].="?, ";
	      }
	      $needed=false;

	      $frames[$k]["ATTRIBUT_NAME"]="ATTRIBUT_NAME_$k";
	      $frames[$k]["ATTRIBUT_SETTING"]="ATTRIBUT_SETTING_$k";

	      $this->lay->SetBlockData($frames[$k]["ATTRIBUT_NAME"],
				       $tableattrs);
              
	      $this->lay->SetBlockData($frames[$k]["ATTRIBUT_SETTING"],
				       $tablesetting);
	      unset($tableattrs);
	      unset($tablesetting);
	      $tableattrs=array();
	      $tablesetting=array();

	      $k++;
	    }
	  $v=0;


	}





      // Set the table value elements
      if ($iattr <= $nattr)	{
   		  
	$currentFrameId = $listattr[$i]->fieldSet->id;
	$tablesetting[$v]["name_attribut"]=$listattr[$i]->id;
	$tablesetting[$v]["labelText"]=addslashes(str_replace("%","",$listattr[$i]->getLabel()));
	$tablesetting[$v]["type"]=base64_encode($listattr[$i]->type);
	$tablesetting[$v]["visibility"]=$listattr[$i]->visibility;
	if ($listattr[$i]->needed){
	  $needed=true;
	}
	 

	if ($v==0){
	  $insert=$listattr[$i]->id;
	  if ($listattr[$i]->type=="textlist"){
	    if ($listattr[$i]->needed){
	      $insert.="+";$tableattrs[$v]["name_attribut"]=$insert;
	    }
	    else{
	      $insert.="*";$tableattrs[$v]["name_attribut"]=$insert;
	    }
	    
	  }
	  else{
	    if ($listattr[$i]->needed){
	      $tableattrs[$v]["name_attribut"]=$insert;
	    }
	    else{
	      $tableattrs[$v]["name_attribut"]=($insert ."?");
	    }
	  }

	}
	else{
	  $insert=(", " .$listattr[$i]->id);          
	  if ($listattr[$i]->type=="textlist"){
	    if ($listattr[$i]->needed){
	      $insert.="+";
	    }
	    else{
	      $insert.="*";
	    }
	    $tableattrs[$v]["name_attribut"]=$insert;
	  }
	  else{
	    if ($listattr[$i]->needed){
	      $tableattrs[$v]["name_attribut"]=$insert;
	    }
	    else{
	      $tableattrs[$v]["name_attribut"]=($insert ."?");
	    }
	  }

        }
	$v++;
    
      }

    }
 
 


    if ($v > 0) // last fieldset
      {
	$frames[$k]["name"]=$currentFrameId;
	if ($needed){
	  $elements[$k]["name"]=$currentFrameId;
	}
	else{
	  $elements[$k]["name"]=($currentFrameId ."?");
	}
	$needed=false;
	$frames[$k]["ATTRIBUT_NAME"]="ATTRIBUT_NAME_$k";
	$frames[$k]["ATTRIBUT_SETTING"]="ATTRIBUT_SETTING_$k";
	$this->lay->SetBlockData($frames[$k]["ATTRIBUT_NAME"],
				 $tableattrs);

	$this->lay->SetBlockData($frames[$k]["ATTRIBUT_SETTING"],
				 $tablesetting);
	unset($tableattrs);
	unset($tablesetting);
	$tableattrs=array();
	$tablesetting=array();

	$k++;

	     	     
      }



    $this->lay->SetBlockData("FIELDSET",$frames);
    $this->lay->SetBlockData("ELEMENT",$elements);
    return $this->lay->gen();
  }

  
  /**
   * return possible dynamic title
   * this method can be redefined in child if the title is variable by other parameters than containt
   */
  function getSpecTitle() {
    return $this->title;
  }

  final public function refreshDocTitle($nameId,$nameTitle) {
  
    // gettitle(D,SI_IDSOC):SI_SOCIETY,SI_IDSOC

    $this->AddParamRefresh("$nameId","$nameTitle");
    $doc=new_Doc($this->dbaccess, $this->getValue($nameId));
    if ($doc->isAlive())  $this->setValue($nameTitle,$doc->title);
    else {
      // suppress
      if (! $doc->isAffected()) $this->deleteValue($nameId);
    }
  }


  /**
   * get image emblem for the doc like lock/nowrite
   * @return string the url of the image
   */
  function getEmblem() {
    global $action;
    if ($this->confidential >0) return  $action->getImageUrl("confidential.gif");
    else if ($this->locked == -1) return  $action->getImageUrl("revised.png");
    else if ($this->allocated == $this->userid) return $action->getImageUrl("clef3.gif");
    else if ((abs($this->locked) == $this->userid)) return $action->getImageUrl("clef1.gif");
    else if ($this->locked != 0) return $action->getImageUrl("clef2.gif");
    else if ($this->archiveid != 0) return $action->getImageUrl("archive.png");
    else if ($this->control("edit") != "") return $action->getImageUrl("nowrite.png");
    else return $action->getImageUrl("1x1.gif");
  }

  /**
   * use only for paramRefresh in attribute definition of a family
   */
  function nothing($a="",$b="",$c="") {
    return "";
  }


  /**
   * return parameter value
   * @param  string  parameter
   * @param  string  default return value
   * @return string  returns parameter value ou default value
   */
  final public function getParam($param, $defv="")  {
    return getParam($param, $defv);
  }


  //----------------------------------------------------------------------
  //   USUAL METHODS USE FOR CALCULATED ATTRIBUTES OR FUNCTION SEARCHES
  //----------------------------------------------------------------------
  // ALL THESE METHODS NAME MUST BEGIN WITH 'GET'
  
  /**
   * return title of document in latest revision
   * @param string $id identificator of document
   * @param string $def default value if document not found
   */
  final public function getLastTitle($id="-1",$def="") {
        return $this->getTitle($id,$def,true);
  }
  /**
   * return title of document
   * @param string $id identificator of document
   * @param string $def default value if document not found
   * @param boolean $latest search title in latest revision
   * @see Doc::getSpecTitle()
   */
  final public function getTitle($id="-1",$def="", $latest=false) {
  	if (is_array($id)) return $def;
  	if ($id=="") return $def;
  	if ($id=="-1") {
  		if ($this->locked != -1 || (!$latest)) {
  			if ($this->isConfidential())  return _("confidential document");
  			return $this->getSpecTitle();
  		} else {
  			// search latest
  			$id=$this->latestId();
  			$lastId=$id;
  		}
  	}
  	if ((strpos($id,"\n")!==false)||(strpos($id,"<BR>")!==false)) {
  		$tid=explode("\n",str_replace("<BR>","\n",$id));
  		$ttitle=array();
  		foreach ($tid as $idone) {
  			$ttitle[]=$this->getTitle($idone,$def,$latest);
  		}
  		return implode("\n",$ttitle);
  	} else {
  		if (! is_numeric($id)) $id=getIdFromName($this->dbaccess,$id);
  		if ($id > 0) {
  			$t = getTDoc($this->dbaccess,$id,array(),array("title","doctype","locked","initid"));
  			if ($latest && ($t["locked"]==-1)) {
  			    if ($lastId!=$id) {
  				$id=getLatestDocId($this->dbaccess, $t["initid"]);
  				$t = getTDoc($this->dbaccess,$id,array(),array("title","doctype","locked"));
  			    }
  			}
  			if ($t)  {
  				if ($t["doctype"]=='C') return getFamTitle($t);
  				return $t["title"];
  			}
  			return " "; // delete title
  		}
  	}
  	return $def;
  }
  /**
   * Same as ::getTitle() 
   * the < > characters as replace by entities
   */
  function getHTMLTitle($id="-1",$def="",$latest=false) {   
    $t=$this->getTitle($id,$def,$latest);
    $t=str_replace("&","&amp;",$t);
    return str_replace(array("<",">"),array("&lt;","&gt;"),$t);
  }
  /**
   * return the today date with european format DD/MM/YYYY
   * @param int $daydelta to have the current date more or less day (-1 means yesterday, 1 tomorrow)
   * @param int $dayhour hours of day
   * @param int $daymin minutes of day
   * @param bool $getlocale whether to return locale date or not
   * @return string DD/MM/YYYY or locale date
   */
  public static function getDate($daydelta=0,$dayhour="",$daymin="",$getlocale=false) {
    $delta = abs(intval($daydelta));
    if ($daydelta > 0) {
      $nd = strtotime("+$delta day");
    } else if ($daydelta < 0) {
      $nd = strtotime("-$delta day");
    } else {
      $nd = time();
    }

    if ($dayhour !== "") {
      $delta = abs(intval($dayhour));
      if ($dayhour > 0) {
	$nd = strtotime("+$delta hour", $nd);
      } else if ($dayhour < 0) {
	$nd = strtotime("-$delta hour", $nd);
      }
      $delta = abs(intval($daymin));
      if ($daymin > 0) {
	$nd = strtotime("+$delta min", $nd);
      } else if ($daymin < 0) {
	$nd = strtotime("-$delta min", $nd);
      }

      if ($getlocale) {
	return FrenchDateToLocaleDate(date("d/m/Y H:i", $nd));
      } else {
	return date("d/m/Y H:i", $nd);
      }
    }
    else {
      if ($getlocale) {
	return FrenchDateToLocaleDate(date("d/m/Y", $nd));
      } else {
	return date("d/m/Y", $nd);
      }
    }
  }

  /**
   * return the today date and time with european format DD/MM/YYYY HH:MM
   * @param int $hourdelta to have the current date more or less hour  (-1 means one hour before, 1 one hour after)
   * @param bool $second if true format DD/MM/YYYY HH:MM
   * @return string DD/MM/YYYY HH:MM
   */
  public static  function getTimeDate($hourdelta=0,$second=false) {
    $delta = abs(intval($hourdelta));
    if ($second) $format="d/m/Y H:i:s";
    else $format="d/m/Y H:i";
    if ($hourdelta > 0) {
      if (is_float($hourdelta)) {
	$dm=intval((abs($hourdelta)-$delta)*60);
	return date($format,strtotime ("+$delta hour $dm minute"));
      } else  return date($format,strtotime ("+$delta hour"));
    } else if ($hourdelta < 0) {      
      if (is_float($hourdelta)) {
	$dm=intval((abs($hourdelta)-$delta)*60);
	return date($format,strtotime ("-$delta hour $dm minute"));
      } else return date($format,strtotime ("-$delta hour"));
    }
    return date($format);
  }

  /**
   * return value of an attribute for the document referenced
   * @param int $docid document identificator
   * @param string $attrid attribute identificator
   * @param string def $def default return value
   * @param bool $latest always last revision of document
   */
  final public function getDocValue($docid, $attrid,$def=" ",$latest=false) {
    if (intval($docid) > 0) {      
      $doc = new_Doc($this->dbaccess, $docid);
      if ($doc->isAlive()) {
	if ($latest && ($doc->locked==-1)) {
	  $ldocid = $doc->latestId();
	  if ($ldocid != $doc->id) $doc = new_Doc($this->dbaccess, $ldocid);	  
	}
	return $doc->getRValue($attrid,$def,$latest);
      }
    }
    return "";
  }

  /**
   * return value of an property for the document referenced
   * @param int document identificator
   * @param string  property identificator
   * @param bool $latest always last revision of document if true
   */
  final public function getDocProp($docid, $propid,$latest=false) {
    if (intval($docid) > 0) {
      if ($latest) $tdoc = getTDoc($this->dbaccess, $docid);
      else $tdoc = getLatestTDoc($this->dbaccess, $docid);
      return $tdoc[strtolower($propid)];
    }
    return "";
  }
  /**
   * return the user last name 
   * @param bool $withfirst if true compose first below last name
   * @return string
   */
  public static function getUserName($withfirst=false) {
    global $action;
    if ($withfirst) return $action->user->firstname." ".$action->user->lastname;
    return $action->user->lastname; 
  }

  

  /**
   * return the personn doc id conform to firstname & lastname of the user
   * @return int
   */
  public static function userDocId() {
    global $action;

    
    return $action->user->fid;
     
  }
  /**
   * alias for @see Doc:userDocId
   * @return int
   */
  public static function getUserId() {
    return Doc::userDocId();
  }

  /**
   * return system user id
   * @deprecated
   * @return int
   */
  public static function getWhatUserId() {
    global $action;    
    return $action->user->id;
  }  
  /**
   * return system user id
   * @return int
   */
  public static function getSystemUserId() {
    global $action;    
    return $action->user->id;
  }


  /**
   * return a specific attribute of the current user document
   * @return int
   */
  final public function getMyAttribute($idattr) {
    $mydoc=new_Doc($this->dbaccess,$this->getUserId());

    return $mydoc->getValue($idattr);
  }

  /**
   * concatenate and format string
   * @param string $fmt like sprintf format
   * @param string parameters of string composition
   * @return string the composed string
   */
  function formatString($fmt) {   
    $nargs = func_num_args();
    for ($ip=0; $ip<$nargs; $ip++) {
      $var = func_get_arg($ip);
    }
    if ($nargs<1) return "";
    $fmt = func_get_arg(0);
    $sp = array();
    for ($ip=1; $ip<$nargs; $ip++) {
      if (gettype($var) != "array") {
	$sp[] = func_get_arg($ip);
      }
    }
    $r = vsprintf($fmt, $sp);
    return $r;
  }

  public function UpdateVaultIndex() {
    $dvi = new DocVaultIndex($this->dbaccess);
    $err = $dvi->DeleteDoc($this->id);
    $fa=$this->GetFileAttributes();
    
    $tvid=array();
    foreach ($fa as $aid=>$oattr) {
      if ($oattr->inArray()) {
	$ta=$this->getTValue($aid);
      } else {
	$ta=array($this->getValue($aid));	  
      }
      foreach ($ta as $k=>$v) {
	$vid="";
	if (preg_match(PREGEXPFILE, $v, $reg)) {
	  $vid=$reg[2];
	  $tvid[$vid]=$vid;
	}
      }      	
    }
    
    foreach ($tvid as $k=>$vid) {
      $dvi->docid = $this->id;
      $dvi->vaultid = $vid;
      $dvi->Add();	
    }
  }

  // ===================
  // Timer Part

  /**
   * attach timer to a document
   * @param _TIMER &$timer the timer document
   * @param Doc &$origin the document which comes from the attachement
   * @return string error - empty if no error -
   */
  final public function attachTimer(&$timer,&$origin=null,$execdate=null) {
    $dyn=false;
    if ($execdate==null) {
      $dyn=trim(strtok($timer->getValue("tm_dyndate")," "));
      if ($dyn)	$execdate=$this->getValue($dyn);      
    }
    if (method_exists($timer,'attachDocument')) {
      $err=$timer->attachDocument($this,$origin,$execdate);
      if ($err=="") {
	if ($dyn) $this->addATag("DYNTIMER");
	$this->addComment(sprintf(_("attach timer %s [%d]"),$timer->title,$timer->id),HISTO_NOTICE);
	$this->addLog("attachtimer",array("timer"=>$timer->id));
      }
    } else {
      $err=sprintf(_("attachTimer : the timer parameter is not a document of TIMER family"));
    }
    return $err;
  }    
  /**
   * unattach timer to a document
   * @param _TIMER &$timer the timer document
   * @param Doc &$origin if set unattach all timer which comes from this origin
   * @return string error - empty if no error -
   */
  final public function unattachTimer(&$timer) {
    if (method_exists($timer,'unattachDocument')) {
      $err=$timer->unattachDocument($this);
      if ($err=="") {
	$this->addComment(sprintf(_("unattach timer %s [%d]"),$timer->title,$timer->id),HISTO_NOTICE);	
	$this->addLog("unattachtimer",array("timer"=>$timer->id));
      }
    } else $err=sprintf(_("unattachTimer : the timer parameter is not a document of TIMER family"));
    return $err;
  }

  final public function resetDynamicTimers() {
    $tms=$this->getAttachedTimers();
    if (count($tms)==0) {
      $this->delATag("DYNTIMER");
    } else {
      foreach ($tms as $k=>$v) {
	$t=new_doc($this->dbaccess,$v["timerid"]);
	$this->unattachTimer($t);
	if ($t->isAlive()) {
	  if ($v["originid"]) $ori=new_doc($this->dbaccess,$v["originid"]);
	  else $ori=null;
	  $this->attachTimer($t,$ori);	 
	}
      }
    }
  }

  /**
   * unattach timer to a document
   * @param _TIMER &$timer the timer document
   * @param Doc &$origin if set unattach all timer which comes from this origin
   * @return string error - empty if no error -
   */
  final public function unattachAllTimers(&$origin=null) {
    $timer=createTmpDoc($this->dbaccess,"TIMER");
    $err=$timer->unattachAllDocument($this,$origin,$c);
    if ($err=="" && $c>0) {
      if ($origin) $this->addComment(sprintf(_("unattach %d timers associated to %s"),$c,$origin->title),HISTO_NOTICE);
      else $this->addComment(sprintf(_("unattach all timers [%s]"),$c),HISTO_NOTICE);	
      $this->addLog("unattachtimer",array("timer"=>"all","number"=>$c));
    }
    return $err;
  }  
  /**
   * return all activated document timer
   * @return array of doctimer values
   */
  final public function getAttachedTimers() {      
    include_once("Class.QueryDb.php");    
    include_once("Class.DocTimer.php");
    $q=new QueryDb($this->dbaccess,"doctimer");
    $q->AddQuery("docid=".$this->initid);
    $q->AddQuery("donedate is null");    
    $l=$q->Query(0,0,"TABLE");

    if (is_array($l))  return $l;    
    return array();
  }

  /**
   * Parse a zone string "FOO:BAR[-1]:B:PDF?k1=v1,k2=v2" into an array:
   *
   * array(
   *     'fulllayout' => 'FOO:BAR[-1]:B:PDF',
   *     'args' => 'k1=v1,k2=v2',
   *     'argv' => array(
   *         'k1' => 'v1',
   *         'k2' => 'v2
   *      ),
   *     'app' => 'FOO',
   *     'layout' => 'BAR',
   *     'index' => '-1',
   *     'modifier' => 'B',
   *     'transform' => 'PDF'
   *  )
   *
   * @param zone string "APP:LAYOUT:etc." $zone
   * @return false on error or an array containing the components
   */
  static public function parseZone($zone) {
    $p = array();

    // Separate layout (left) from args (right)
    $split = preg_split('/\?/', $zone, 2);
    $left = $split[0];
    $right = $split[1];
    
    // Check that the layout part has al least 2 elements
    $el = preg_split('/:/', $left);
    if( count($el) < 2 ) {
      return false;
    }
    $p['fulllayout'] = $left;
    $p['index']=-1;
    // Parse args into argv (k => v)
    if( $right != "" ) {
      $p['args'] = $right;
      $argList = preg_split('/&/', $p['args']);
      foreach( $argList as $arg ) {
	$split = preg_split('/=/', $arg, 2);
	$left = urldecode($split[0]);
	$right = urldecode($split[1]);
	$p['argv'][$left] = $right;
      }
    }
    
    // Parse layout
    $parts = array(0 => 'app', 1 => 'layout', 2 => 'modifier', 3 => 'transform');
    $match = array();
    $i = 0;
    while( $i < count($el) ) {
      if( ! array_key_exists($i, $parts) ) {
	error_log(__CLASS__."::".__FUNCTION__." ".sprintf("Unexpected part '%s' in zone '%s'.", $el[$i], $zone));
	return false;
      }

      // Extract index from 'layout' part if present
      if( $i == 1 && preg_match("/^(?P<name>.*?)\[(?P<index>-?\d)\]$/", $el[$i], $match) ) {
	$p[$parts[$i]] = $match['name'];
	$p['index'] = $match['index'];
	$i++;
	continue;
      }

      // Store part
      $p[$parts[$i]] = $el[$i];
      $i++;
    }
    
    return $p;
  }

}

?>
