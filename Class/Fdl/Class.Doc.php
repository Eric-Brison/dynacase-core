<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Document Object Definition
 *
 * @author Anakeen
 * @version $Id: Class.Doc.php,v 1.562 2009/01/14 09:18:05 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

include_once ("Class.QueryDb.php");
include_once ("Lib.FileMime.php");
include_once ("FDL/Class.DocCtrl.php");
include_once ("FDL/freedom_util.php");
include_once ("FDL/Class.DocVaultIndex.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("FDL/Class.DocHisto.php");
include_once ('FDL/Class.ADoc.php');
// define constant for search attributes in concordance with the file "init.freedom"
/**#@+
 * constant for document family identifier in concordance with the file "FDL/init.freedom"
 *
*/
define("FAM_BASE", 1);
define("FAM_DIR", 2);
define("FAM_ACCESSDOC", 3);
define("FAM_ACCESSDIR", 4);
define("FAM_SEARCH", 5);
define("FAM_ACCESSSEARCH", 6);
define("FAM_ACCESSFAM", 23);
define("MENU_ACTIVE", 1);
define("MENU_INVISIBLE", 2);
define("MENU_INACTIVE", 0);

define('POPUP_INACTIVE', 0);
define('POPUP_ACTIVE', 1);
define('POPUP_CTRLACTIVE', 3);
define('POPUP_CTRLINACTIVE', 4);
define('POPUP_INVISIBLE', 2);

define("DELVALUE", 'DEL??');
/**#@-*/
/**
 * max cache document
 */
define("MAXGDOCS", 20);

define("REGEXPFILE", "([^\|]*)\|([0-9]*)\|?(.*)?");
define("PREGEXPFILE", "/([^\|]*)\|([0-9]*)\|?(.*)?/");
/**
 * Document Class
 *
 */
class Doc extends DocCtrl
{
    const USEMASKCVVIEW = - 1;
    const USEMASKCVEDIT = - 2;
    public $fields = array(
        "id",
        "owner",
        "title",
        "revision",
        "version",
        "initid",
        "fromid",
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
        "domainid",
        "lockdomainid",
        "cvid",
        "name",
        "dprofid",
        "views",
        "atags",
        "prelid",
        "confidential",
        "ldapdn"
    );
    
    public $sup_fields = array(
        "values",
        "attrids"
    ); // not be in fields else trigger error
    public static $infofields = array(
        "id" => array(
            "type" => "integer",
            "displayable" => true,
            "sortable" => true,
            "filterable" => true,
            "label" => "prop_id"
        ) , # N_("prop_id")
        "owner" => array(
            "type" => "uid",
            "displayable" => true,
            "sortable" => true,
            "filterable" => true,
            "label" => "prop_owner"
        ) , # N_("prop_owner"),
        "icon" => array(
            "type" => "image",
            "displayable" => true,
            "sortable" => false,
            "filterable" => false,
            "label" => "prop_icon"
        ) , # N_("prop_icon"),
        "title" => array(
            "type" => "text",
            "displayable" => true,
            "sortable" => true,
            "filterable" => true,
            "label" => "prop_title"
        ) , # N_("prop_title"),
        "revision" => array(
            "type" => "integer",
            "displayable" => true,
            "sortable" => true,
            "filterable" => true,
            "label" => "prop_revision"
        ) , # N_("prop_revision"),
        "version" => array(
            "type" => "text",
            "displayable" => true,
            "sortable" => true,
            "filterable" => true,
            "label" => "prop_version"
        ) , # N_("prop_version"),
        "initid" => array(
            "type" => "docid",
            "displayable" => true,
            "sortable" => true,
            "filterable" => true,
            "label" => "prop_initid"
        ) , # N_("prop_initid"),
        "fromid" => array(
            "type" => "docid",
            "displayable" => true,
            "sortable" => true,
            "filterable" => true,
            "label" => "prop_fromid"
        ) , # N_("prop_fromid"),
        "doctype" => array(
            "type" => "text",
            "displayable" => false,
            "sortable" => true,
            "filterable" => true,
            "label" => "prop_doctype"
        ) , # N_("prop_doctype"),
        "locked" => array(
            "type" => "uid",
            "displayable" => true,
            "sortable" => false,
            "filterable" => false,
            "label" => "prop_locked"
        ) , # N_("prop_locked"),
        "allocated" => array(
            "type" => "uid",
            "displayable" => false,
            "sortable" => true,
            "filterable" => true,
            "label" => "prop_allocated"
        ) , # N_("prop_allocated"),
        "lmodify" => array(
            "type" => "text",
            "displayable" => false,
            "sortable" => false,
            "filterable" => false,
            "label" => "prop_lmodify"
        ) , # N_("prop_lmodify"),
        "profid" => array(
            "type" => "integer",
            "displayable" => false,
            "sortable" => false,
            "filterable" => false,
            "label" => "prop_profid"
        ) , # N_("prop_profid"),
        "usefor" => array(
            "type" => "text",
            "displayable" => false,
            "sortable" => false,
            "filterable" => false,
            "label" => "prop_usefor"
        ) , # N_("prop_usefor")
        "cdate" => array(
            "type" => "timestamp",
            "displayable" => true,
            "sortable" => true,
            "filterable" => true,
            "label" => "prop_cdate"
        ) , # N_("prop_cdate")
        "adate" => array(
            "type" => "timestamp",
            "displayable" => true,
            "sortable" => true,
            "filterable" => true,
            "label" => "prop_adate"
        ) , # N_("prop_adate"),
        "revdate" => array(
            "type" => "timestamp",
            "displayable" => true,
            "sortable" => true,
            "filterable" => true,
            "label" => "prop_revdate"
        ) , # N_("prop_revdate"),
        "comment" => array(
            "type" => "text",
            "displayable" => false,
            "sortable" => false,
            "filterable" => false,
            "label" => "prop_comment"
        ) , # N_("prop_comment"),
        "classname" => array(
            "type" => "text",
            "displayable" => false,
            "sortable" => false,
            "filterable" => false,
            "label" => "prop_classname"
        ) , # N_("prop_classname")
        "state" => array(
            "type" => "text",
            "displayable" => true,
            "sortable" => true,
            "filterable" => true,
            "label" => "prop_state"
        ) , # N_("prop_state"),
        "wid" => array(
            "type" => "docid",
            "displayable" => false,
            "sortable" => false,
            "filterable" => false,
            "label" => "prop_wid"
        ) , # N_("prop_wid")
        "postitid" => array(
            "type" => "text",
            "displayable" => false,
            "sortable" => false,
            "filterable" => false,
            "label" => "prop_postitid"
        ) , # N_("prop_postitid")
        "cvid" => array(
            "type" => "integer",
            "displayable" => false,
            "sortable" => false,
            "filterable" => false,
            "label" => "prop_cvid"
        ) , # N_("prop_cvid")
        "name" => array(
            "type" => "text",
            "displayable" => true,
            "sortable" => true,
            "filterable" => true,
            "label" => "prop_name"
        ) , # N_("prop_name")
        "dprofid" => array(
            "type" => "docid",
            "displayable" => false,
            "sortable" => false,
            "filterable" => false,
            "label" => "prop_dprofid"
        ) , # N_("prop_dprofid")
        "atags" => array(
            "type" => "text",
            "displayable" => false,
            "sortable" => false,
            "filterable" => false,
            "label" => "prop_atags"
        ) , # N_("prop_atags")
        "prelid" => array(
            "type" => "docid",
            "displayable" => false,
            "sortable" => false,
            "filterable" => false,
            "label" => "prop_prelid"
        ) , # N_("prop_prelid")
        "lockdomainid" => array(
            "type" => "docid",
            "displayable" => true,
            "sortable" => true,
            "filterable" => false,
            "label" => "prop_lockdomainid"
        ) , # N_("prop_lockdomainid")
        "domainid" => array(
            "type" => "docid",
            "displayable" => false,
            "sortable" => false,
            "filterable" => false,
            "label" => "prop_domainid"
        ) , # N_("prop_domainid")
        "confidential" => array(
            "type" => "integer",
            "displayable" => false,
            "sortable" => false,
            "filterable" => true,
            "label" => "prop_confidential"
        ) , # N_("prop_confidential")
        "svalues" => array(
            "type" => "fulltext",
            "displayable" => false,
            "sortable" => false,
            "filterable" => true,
            "label" => "prop_svalues"
        ) , # N_("prop_svalues")
        "ldapdn" => array(
            "type" => "text",
            "displayable" => false,
            "sortable" => false,
            "filterable" => false,
            "label" => "prop_ldapdn"
        )
    ); # N_("prop_ldapdn");
    
    /**
     * identifier of the document
     * @var int
     */
    public $id;
    /**
     * user identifier for the creator
     * @var int
     */
    public $owner;
    /**
     * the title of the document
     * @var string
     */
    public $title;
    /**
     * number of the revision. First is zero
     * @var int
     */
    public $revision;
    /**
     * tag for version
     * @var string
     */
    public $version;
    /**
     * identifier of the first revision document
     * @var int
     */
    public $initid;
    /**
     * identifier of the family document
     * @var int
     */
    public $fromid;
    /**
     * domain where document is lock
     * @var int
     */
    public $lockdomainid;
    /**
     * domain where document is attached
     * @var array
     */
    public $domainid;
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
     * @var string single character
     */
    public $doctype;
    /**
     * user identifier for the locker
     * @vart
     */
    public $locked;
    /**
     * filename or vault id for the icon
     * @var string
     */
    public $icon;
    /**
     * set to 'Y' if the document has been modify until last revision
     * @var string single character
     */
    public $lmodify;
    /**
     * identifier of the profil document
     * @var int
     */
    public $profid;
    /**
     * user/group/role which can view document
     * @var string
     */
    public $views;
    /**
     * to precise a special use of the document
     * @var string single character
     */
    public $usefor;
    /**
     * date of the last modification (the revision date for fixed document)
     * @var int
     */
    public $revdate;
    /**
     * date of creation
     * @var string date 'YYYY-MM-DD'
     */
    public $cdate;
    /**
     * date of latest access
     * @var string date 'YYYY-MM-DD'
     */
    public $adate;
    /**
     * @deprecated old history notation
     * @var string
     */
    public $comment;
    /**
     * class name in case of special family (only set in family document)
     * @var string
     */
    public $classname;
    /**
     * state of the document if it is associated with a workflow
     * @var string
     */
    public $state;
    /**
     * identifier of the workflow document
     *
     * if 0 then no workflow
     * @var int
     */
    public $wid;
    /**
     * identifier of the control view document
     *
     * if 0 then no special control view
     * @var int
     */
    public $cvid;
    /**
     * string identifier of the document
     *
     * @var string
     */
    public $name;
    /**
     * identifier of the mask document
     *
     * if 0 then no mask
     * @var int
     */
    public $mid = 0;
    /**
     * identifier of dynamic profil
     *
     * if 0 then no dynamic profil
     * @var int
     */
    public $dprofid = 0;
    /**
     * primary relation id
     *
     * generally towards a folder
     * @var int
     */
    public $prelid = 0;
    /**
     * applications tag
     * use by specifics applications to search documents by these tags
     *
     * @var string
     */
    public $atags;
    /**
     * idengtificator of document's note
     * @var int
     */
    public $postitid;
    /**
     * confidential level
     * if not 0 this document is confidential, only user with the permission 'confidential' can read this
     *
     * @var int
     */
    public $confidential;
    /**
     * Distinguish Name for LDAP use
     *
     * @var string
     */
    public $ldapdn;
    /**
     * Allocate user id
     *
     * @var int
     */
    public $allocated;
    /**
     * Archive document id
     *
     * @var int
     */
    public $archiveid;
    /**
     * @var string logical name family
     */
    public $fromname;
    /**
     * @var string raw family title
     */
    public $fromtitle;
    /**
     * @var string fulltext vector
     */
    public $fulltext;
    /**
     * for system purpose only
     * @var string concatenation of all values
     */
    protected $values;
    /**
     * for system purpose only
     * @var string concatenation of all attribute ids (use it with ::values)
     */
    protected $attrids;
    /**
     * param value cache
     * @var array
     */
    private $_paramValue = array();
    /**
     * @var string last modify error when refresh
     */
    private $lastRefreshError = '';
    /**
     * identification of special views
     *
     * @var array
     */
    public $cviews = array(
        "FDL:VIEWBODYCARD",
        "FDL:VIEWABSTRACTCARD",
        "FDL:VIEWTHUMBCARD"
    );
    public $eviews = array(
        "FDL:EDITBODYCARD"
    );
    /**
     * @var WDoc
     */
    public $wdoc = null;
    /**
     * @var Adoc
     */
    public $attributes = null;
    public static $sqlindex = array(
        "doc_initid" => array(
            "unique" => false,
            "on" => "initid"
        ) ,
        "doc_title" => array(
            "unique" => false,
            "on" => "title"
        ) ,
        "doc_name" => array(
            "unique" => true,
            "on" => "name,revision,doctype"
        ) ,
        "doc_full" => array(
            "unique" => false,
            "using" => "@FDL_FULLIDX",
            "on" => "fulltext"
        ) ,
        "doc_profid" => array(
            "unique" => false,
            "on" => "profid"
        )
    );
    public $id_fields = array(
        "id"
    );
    
    public $dbtable = "doc";
    
    public $order_by = "title, revision desc";
    
    public $fulltextfields = array(
        "title"
    );
    private $mvalues = array();
    /**
     * number of disabledEditControl
     */
    private $withoutControlN = 0;
    private $withoutControl = false;
    private $constraintbroken = false; // true if one constraint is not verified
    private $_oldvalue = array();
    private $fathers = null;
    private $childs = null;
    /**
     * @var DocHtmlFormat
     */
    private $htmlFormater = null;
    /**
     * @var DocOooFormat
     */
    private $oooFormater = null;
    /**
     * used by fulltext indexing
     * @var array
     */
    private $textsend = array();
    /**
     * to not detect changed when it is automatic setValue
     * @var bool
     */
    private $_setValueDetectChange = true;
    /**
     * list of availaible control
     * @var array
     */
    public $acls = array();
    /**
     * document layout
     * @var Layout|OooLayout
     */
    public $lay = null;
    /**
     * default family id for the profil access
     * @var int
     */
    public $defProfFamId = FAM_ACCESSDOC;
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
                   icon text,
                   lmodify char DEFAULT 'N',
                   profid int DEFAULT 0,
                   usefor text DEFAULT 'N',
                   revdate int,
                   version text,
                   cdate timestamp,
                   adate timestamp,
                   comment text,
                   classname text,
                   state text,
                   wid int DEFAULT 0,
                   values text DEFAULT '',
                   attrids text DEFAULT '',
                   fulltext tsvector,
                   postitid text,
                   domainid text,
                   lockdomainid int,
                   cvid int,
                   name text,
                   dprofid int DEFAULT 0,
                   views int[],
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
    public $obj_acl = array(); // set by childs classes
    // --------------------------------------------------------------------
    
    /**
     * default view to view card
     * @var string
     */
    public $defaultview = "FDL:VIEWBODYCARD";
    /**
     * default view to edit card
     * @var string
     */
    public $defaultedit = "FDL:EDITBODYCARD";
    /**
     * default view for abstract card
     * @var string
     */
    public $defaultabstract = "FDL:VIEWABSTRACTCARD";
    /**
     * default view use when edit document for the first time (creation mode)
     * @var string
     */
    public $defaultcreate = "";
    /**
     * for email : the same as $defaultview by default
     * @var string
     */
    public $defaultmview = "";
    /**
     * use when family wants to define a special context menu
     * @var array
     */
    public $specialmenu = array();
    
    public $defDoctype = 'F';
    /**
     * to indicate values modification
     * @var bool
     * @access private
     */
    private $hasChanged = false;
    
    public $paramRefresh = array();
    /**
     * optimize: compute mask in needed only
     * @var bool
     * @access private
     */
    private $_maskApplied = false; // optimize: compute mask if needed only
    
    /**
     * By default, setValue() will call completeArrayRow when setting
     * values of arrays columns.
     * @var bool
     * @access private
     */
    private $_setValueCompleteArrayRow = true;
    /**
     * display document main properties as string
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s "%s" [#%d]', $this->fromname, $this->getTitle() , $this->id);
    }
    /**
     * Increment sequence of family and call to Doc::PostCreated
     * send mail if workflow is attached to it
     * affect profil
     * @see Doc::PostCreated
     *
     * @return void
     */
    final public function PostInsert()
    {
        // controlled will be set explicitly
        //$this->SetControl();
        if (($this->revision == 0) && ($this->doctype != "T")) {
            // increment family sequence
            $this->nextSequence();
            $famDoc = $this->getFamilyDocument();
            $incumbentName = getCurrentUser()->getIncumbentPrivilege($famDoc, 'create');
            $createComment = _("document creation");
            if ($incumbentName) $createComment = sprintf(_("(substitute of %s) : ") , $incumbentName) . $createComment;
            $this->addHistoryEntry($createComment, HISTO_INFO, "CREATE");
            if ($this->wdoc) {
                $this->wdoc->workflowSendMailTemplate($this->state, _("creation"));
                $this->wdoc->workflowAttachTimer($this->state);
                $this->wdoc->changeAllocateUser($this->state);
            }
            $this->addLog("create", array(
                "id" => $this->id,
                "title" => $this->title,
                "fromid" => $this->fromid,
                "fromname" => $this->fromname
            ));
        }
        $this->Select($this->id);
        // set creation date
        $this->cdate = $this->getTimeDate(0, true);
        $this->adate = $this->cdate;
        $date = gettimeofday();
        $this->revdate = $date['sec'];
        $this->modify(true, array(
            "cdate",
            "adate",
            "revdate"
        ) , true); // to force also execute sql trigger
        if ($this->doctype != "T") {
            $err = $this->PostCreated();
            if ($err != "") AddWarningMsg($err);
            $this->sendTextToEngine();
            if ($this->dprofid > 0) {
                $this->setProfil($this->dprofid); // recompute profil if needed
                $this->modify(true, array(
                    "profid"
                ) , true);
            }
            $this->UpdateVaultIndex();
            $this->updateRelations(true);
        }
        $this->hasChanged = false;
        
        global $gdocs; // set to cache
        if (count($gdocs) < MAXGDOCS && ($this->doctype != 'C')) {
            $gdocs[$this->id] = & $this;
        }
    }
    
    function setChanged()
    {
        $this->hasChanged = true;
    }
    /**
     * return true if document has changed after setValue/clearValue calling
     * @api test if document attributes are changed
     * @return bool
     */
    function isChanged()
    {
        return ($this->hasChanged === true);
    }
    /**
     * set default values and creation date
     * the access control is provided by {@see Doc::createDoc()} function.
     * call {@see Doc::PreCreated()} method before execution
     *
     * @return string error message, if no error empty string
     */
    final public function PreInsert()
    {
        
        $err = $this->PreCreated();
        if ($err != "") return $err;
        // compute new id
        if ($this->id == "") {
            if ($this->doctype == 'T') $res = pg_query($this->init_dbid() , "select nextval ('seq_id_tdoc')");
            else $res = pg_query($this->init_dbid() , "select nextval ('seq_id_doc')");
            $arr = pg_fetch_array($res, 0);
            $this->id = $arr[0];
        }
        // set default values
        if ($this->initid == "") $this->initid = $this->id;
        $this->RefreshTitle();
        if (chop($this->title) == "") {
            $fdoc = $this->getFamilyDocument();
            $this->title = sprintf(_("untitle %s %d") , $fdoc->title, $this->initid);
        }
        if ($this->doctype == "") $this->doctype = $this->defDoctype;
        if ($this->revision == "") $this->revision = "0";
        
        if ($this->profid == "") {
            $this->views = "{0}";
            $this->profid = "0";
        }
        if ($this->usefor == "") $this->usefor = "N";
        
        if ($this->lmodify == "") $this->lmodify = "N";
        if ($this->locked == "") $this->locked = "0";
        if ($this->owner == "") $this->owner = $this->userid;
        //      if ($this->state == "") $this->state=$this->firstState;
        $this->version = $this->getVersion();
        
        if ($this->name && $this->revision == 0) {
            $err = $this->setLogicalName($this->name, false, true);
            if ($err) {
                return $err;
            }
        }
        if ($this->wid > 0) {
            /**
             * @var WDoc $wdoc
             */
            $wdoc = new_Doc($this->dbaccess, $this->wid);
            $this->wdoc = $wdoc;
            if ($this->wdoc->isAlive()) {
                if ($this->wdoc->doctype != 'W') $err = sprintf(_("creation : document %s is not a workflow") , $this->wid);
                else $this->wdoc->Set($this); // set first state
                
            } else $err = sprintf(_("creation : workflow %s not exists") , $this->wid);
        } else {
            $this->wdoc = null;
        }
        return $err;
    }
    /**
     * Verify control edit
     *
     * if {@link Doc::disableEditControl()} is call before control permission is desactivated
     * if attribute values are changed the modification date is updated
     * @return string error message, if no error empty string
     */
    function PreUpdate()
    {
        if ($this->id == "") return _("cannot update no initialized document");
        if ($this->doctype == 'I') return _("cannot update inconsistent document"); // provides from waiting document or searchDOc with setReturns
        if (!$this->withoutControl) {
            $err = $this->control("edit");
            if ($err != "") return ($err);
        }
        if ($this->locked == - 1) $this->lmodify = 'N';
        $err = $this->docIsCleanToModify();
        if ($err) return $err;
        if ($this->constraintbroken) return (sprintf(_("constraint broken %s") , $this->constraintbroken));
        $this->RefreshTitle();
        if ($this->hasChanged) {
            if (chop($this->title) == "") $this->title = _("untitle document");
            // set modification date
            $date = gettimeofday();
            $this->revdate = $date['sec'];
            $this->version = $this->getVersion();
            $this->lmodify = 'Y';
        }
        return '';
    }
    
    private function docIsCleanToModify()
    {
        if ($this->initid > 0 && $this->fromid > 0) {
            simpleQuery($this->dbaccess, sprintf("select initid, id, revision, locked from only doc%d where initid=%d", $this->fromid, $this->initid) , $r);
            
            $cAlive = 0;
            $imAlive = false;
            foreach ($r as $docInfo) {
                if (($docInfo["id"] == $this->id) && ($docInfo["locked"] == - 1)) {
                    return ErrorCode::getError('DOC0118', $this->getTitle() , $this->id);
                } elseif ($docInfo["locked"] != - 1) {
                    if ($docInfo["id"] == $this->id) $imAlive = true;
                    $cAlive++;
                }
            }
            if ($this->locked != - 1 && $cAlive == 1 && $imAlive) {
                return ''; // OK
                
            } elseif ($cAlive > 1) {
                // multiple alive already set : need fix it
                fixMultipleAliveDocument($this);
                if ($this->isFixed()) { // if locked now ?
                    return ErrorCode::getError('DOC0119', $this->getTitle() , $this->id);
                }
            }
        }
        return '';
    }
    /**
     * optimize for speed : memorize object for future use
     * @return string
     */
    function PostUpdate()
    {
        fixMultipleAliveDocument($this);
        if ($this->hasChanged) {
            $this->computeDProfil();
            if ($this->doctype != 'C') {
                $this->regenerateTemplates();
                $this->UpdateVaultIndex();
                $this->updateRelations();
                
                if ($this->getATag("DYNTIMER")) $this->resetDynamicTimers();
                $this->addLog("changed", array_keys($this->getOldRawValues()));
            }
        }
        $this->sendTextToEngine();
        $this->hasChanged = false;
        return '';
    }
    /**
     * Regenerate the template referenced by an attribute
     *
     * @param string $aid the name of the attribute holding the template
     * @param string $index the value for $index row (default value -1 means all values)
     * @return string error message, if no error empty string
     */
    function regenerateTemplate($aid, $index = - 1)
    {
        $layout = 'THIS:' . $aid;
        if ($index > - 1) {
            $layout.= '[' . $index . ']';
        }
        $orifile = $this->getZoneFile($layout);
        if (!$orifile) {
            $err = sprintf(_("Dynamic template %s not found ") , $orifile);
            return $err;
        }
        if (!file_exists($orifile)) {
            $err = sprintf(_("Dynamic template %s not found ") , $orifile);
            addWarningMsg($err);
            return $err;
        }
        if (getFileExtension($orifile) != 'odt') {
            $err = sprintf(_("Dynamic template %s not an odt file ") , $orifile);
            addWarningMsg($err);
            return $err;
        }
        $outfile = $this->viewDoc($layout . ':B', 'ooo');
        if (!file_exists($outfile)) {
            $err = sprintf(_("viewDoc did not returned a valid file"));
            addWarningMsg($err);
            return $err;
        }
        $fh = fopen($outfile, 'rb');
        if ($fh === false) {
            $err = sprintf(_("Error opening %s file '%s'", 'outfile', $outfile));
            addWarningMsg($err);
            return $err;
        }
        $err = $this->saveFile($aid, $fh, '', $index);
        if ($err != '') {
            addWarningMsg($err);
            return $err;
        }
        fclose($fh);
        $this->addHistoryEntry(sprintf(_('regeneration of file template %s') , $aid));
        return '';
    }
    /**
     * Regenerate all templates referenced by the document attributes
     *
     * @return string error message, if no error empty string
     */
    final function regenerateTemplates()
    {
        $fa = $this->GetFileAttributes();
        $errorList = array();
        foreach ($fa as $aid => $oattr) {
            $opt = $oattr->getOption("template");
            if ($opt == "dynamic" || $opt == "form") {
                if ($oattr->inArray()) {
                    $ta = $this->getMultipleRawValues($aid);
                    foreach ($ta as $k => $v) {
                        $err = $this->regenerateTemplate($aid, $k);
                        if ($err != '') {
                            array_push($errorList, $err);
                        }
                    }
                } else {
                    $err = $this->regenerateTemplate($aid);
                    if ($err != '') {
                        array_push($errorList, $err);
                    }
                }
            }
        }
        if (count($errorList) > 0) {
            return join("\n", $errorList);
        }
        return '';
    }
    /**
     * Set relation doc id use on docrel table
     */
    function updateRelations($force = false)
    {
        //    return; // for the moment
        include_once ("FDL/Class.DocRel.php");
        $or = new DocRel($this->dbaccess);
        //    $or->resetRelations('',$this->initid); // not necessary now
        $or->initRelations($this, $force);
    }
    /**
     * get current sequence number :: number of doc for this family
     * @return int
     */
    function getCurSequence()
    {
        if ($this->doctype == 'C') return 0;
        if ($this->fromid == "") return 0;
        // cannot use currval if nextval is not use before
        $res = pg_query($this->init_dbid() , "select nextval ('seq_doc" . $this->fromid . "')");
        $arr = pg_fetch_array($res, 0);
        $cur = intval($arr[0]) - 1;
        $res = pg_query($this->init_dbid() , "select setval ('seq_doc" . $this->fromid . "',$cur)");
        
        return $cur;
    }
    /**
     * set next sequence family
     * @param int $fromid
     * @return int
     */
    function nextSequence($fromid = 0)
    {
        if ($fromid == 0) $fromid = $this->fromid;
        if ($this->fromid == 0) return 0;
        if ($this->doctype == 'C') return 0;
        // cannot use currval if nextval is not use before
        $res = pg_query($this->init_dbid() , "select nextval ('seq_doc" . $fromid . "')");
        $arr = pg_fetch_array($res, 0);
        $cur = intval($arr[0]);
        return $cur;
    }
    /**
     * disable edit control for setValue/modify/store
     * the document can be modified without testing edit acl
     * @see Doc::enableEditControl
     * @api disable edit control for setValue/modify/store
     */
    final public function disableEditControl()
    {
        $this->withoutControlN++;
        $this->withoutControl = true;
    }
    /**
     * default edit control enable
     * restore control which are disabled by disableEditControl
     * @see Doc::disableEditControl
     * @api default edit control enable
     */
    final public function enableEditControl()
    {
        $this->withoutControlN--;
        if ($this->withoutControlN <= 0) {
            $this->withoutControlN = 0;
            $this->withoutControl = false;
        }
    }
    /**
     * to know if the document can be revised
     * @return bool true is revisable
     */
    public function isRevisable()
    {
        if (($this->doctype == 'F') && ($this->usefor != 'P')) {
            $fdoc = $this->getFamilyDocument();
            if ($fdoc->schar != "S") return true;
        }
        return false;
    }
    /**
     * copy values from anothers document (must be same family or descendant)
     *
     * @param Doc &$from document source for the transfert
     * @return string error message from setValue, if no error, empty string
     */
    final public function transfertValuesFrom(&$from)
    {
        
        $values = $from->getValues();
        $err = "";
        foreach ($values as $k => $v) {
            $err.= ($err ? '\n' : '') . $this->setValue($k, $v);
        }
        return $err;
    }
    /**
     * convert to another family
     * loose all revisions
     * @param int $fromid family identifier where the document will be converted
     * @param array $prevalues values which will be added before conversion
     * @return doc the document converted (don't reuse $this) if error return string message
     */
    final public function convert($fromid, $prevalues = array())
    {
        
        $cdoc = createDoc($this->dbaccess, $fromid);
        if (!$cdoc) return false;
        if ($this->fromid == $cdoc->fromid) return false; // no convert if not needed
        if ($this->locked == - 1) return false; // not revised document
        if ($cdoc->fromid == 0) return false;
        $f1doc = $this->getFamilyDocument();
        $f1from = $f1doc->title . "[" . $f1doc->id . "]";
        $f2doc = $cdoc->getFamilyDocument();
        $f2from = $f2doc->title . "[" . $f2doc->id . "]";
        
        $cdoc->id = $this->id;
        $cdoc->initid = $this->id;
        $cdoc->revision = 0;
        $cdoc->cdate = $this->cdate;
        $cdoc->revdate = $this->revdate;
        $cdoc->adate = $this->adate;
        $cdoc->locked = $this->locked;
        $cdoc->profid = $this->profid;
        $cdoc->dprofid = $this->dprofid;
        $cdoc->prelid = $this->prelid;
        
        $values = $this->getValues();
        $point = "convert" . $this->id;
        $this->savePoint($point); // begin transaction in case of fail add
        $err = $this->delete(true, false, true); // delete before add to avoid double id (it is not authorized)
        if ($err != "") return $err;
        
        foreach ($prevalues as $k => $v) {
            $cdoc->setValue($k, $v);
        }
        $err = $cdoc->Add(true, true);
        if ($err != "") {
            $this->rollbackPoint($point);
            return $err;
        }
        
        foreach ($values as $k => $v) {
            $cdoc->setValue($k, $v);
        }
        
        $err = $cdoc->Modify();
        if ($err == "") {
            if ($this->revision > 0) {
                $this->exec_query(sprintf("update fld set childid=%d where childid=%d", $cdoc->id, $this->initid));
            }
        }
        $this->exec_query(sprintf("update fld set fromid=%d where childid=%d", $cdoc->fromid, $this->initid));
        
        $cdoc->addHistoryEntry(sprintf(_("convertion from %s to %s family") , $f1from, $f2from));
        
        $this->commitPoint($point);
        global $gdocs; //reset cache if needed
        if (isset($gdocs[$this->id])) {
            $gdocs[$this->id] = & $cdoc;
        }
        
        return $cdoc;
    }
    /**
     * test if the document can be revised now
     * it must be locked by the current user
     * @deprecated use canEdit instead
     * @return string empty means user can update else message of the raison
     */
    final public function canUpdateDoc()
    {
        deprecatedFunction();
        return $this->canEdit();
    }
    /**
     * save document if attribute are change
     * not be use when modify properties
     * only use with use of setValue.
     * @param stdClass $info refresh and postStore messages
     * @param boolean $skipConstraint set to true to not test constraints
     * @deprecated use ::store() instead
     * @return string error message
     */
    public function save(&$info = null, $skipConstraint = false)
    {
        deprecatedFunction();
        $err = '';
        $info = new stdClass();
        $info->constraint = '';
        if (!$skipConstraint) {
            $err = $this->verifyAllConstraints(false, $info->constraint);
        }
        if ($err == '') {
            $info->refresh = $this->refresh();
            $info->postModify = $this->postStore();
            if ($this->hasChanged) {
                //in case of change in postModify
                $err = $this->modify();
            }
            if ($err == "") $this->addHistoryEntry(_("save document") , HISTO_INFO, "MODIFY");
        }
        $info->error = $err;
        return $err;
    }
    /**
     * record new document or update
     * @api record new document or update it in database
     * @param storeInfo $info refresh and postStore messages
     * @param boolean $skipConstraint set to true to not test constraints
     * @return string error message
     */
    public function store(&$info = null, $skipConstraint = false)
    {
        $err = '';
        $constraint = '';
        $info = new storeInfo();
        
        $err = $this->preStore();
        if ($err) {
            $info->preStore = $err;
            $info->error = $err;
            $info->errorCode = storeInfo::PRESTORE_ERROR;
            return $err;
        }
        if (!$skipConstraint) {
            $err = $this->verifyAllConstraints(false, $constraint);
        }
        if ($err == '') {
            $create = false;
            if (!$this->isAffected()) {
                $err = $this->add();
                $create = true;
            }
            if ($err == '') {
                $this->lastRefreshError = '';
                $info->refresh = $this->refresh();
                $err = $this->lastRefreshError;
                if ($err) {
                    $info->errorCode = storeInfo::UPDATE_ERROR;
                } else {
                    $info->postStore = $this->postStore();
                    /** @noinspection PhpDeprecationInspection
                     * compatibility until postModify exists
                     */
                    $info->postModify = $info->postStore;
                    if ($this->hasChanged) {
                        //in case of change in postStore
                        $err = $this->modify();
                        if ($err) $info->errorCode = storeInfo::UPDATE_ERROR;
                    }
                    if ($err == "" && (!$create)) $this->addHistoryEntry(_("save document") , HISTO_INFO, "MODIFY");
                }
            } else {
                $info->errorCode = storeInfo::CREATE_ERROR;
            }
        } else {
            $info->errorCode = storeInfo::CONSTRAINT_ERROR;
        }
        $info->constraint = $constraint;
        $info->error = $err;
        return $err;
    }
    /**
     * test if the document can be modified by the current user
     * the document is not need to be locked
     * @param bool $verifyDomain
     * @return string empty means user can update else message of the raison
     */
    public function canEdit($verifyDomain = true)
    {
        if ($this->locked == - 1) {
            $err = sprintf(_("cannot update file %s (rev %d) : fixed. Get the latest version") , $this->title, $this->revision);
            return ($err);
        }
        if ($this->userid == 1) return ""; // admin can do anything but not modify fixed doc
        $err = "";
        if ($verifyDomain && ($this->lockdomainid > 0)) $err = sprintf(_("document is booked in domain %s") , $this->getTitle($this->lockdomainid));
        else {
            if ($this->withoutControl) return ""; // no more test if disableEditControl activated
            if (($this->locked != 0) && (abs($this->locked) != $this->userid)) {
                $user = new Account("", abs($this->locked));
                if ($this->locked < - 1) $err = sprintf(_("Document %s is in edition by %s.") , $this->getTitle() , $user->firstname . " " . $user->lastname);
                else $err = sprintf(_("you are not allowed to update the file %s (rev %d) is locked by %s.") , $this->getTitle() , $this->revision, $user->firstname . " " . $user->lastname);
            } else {
                $err = $this->Control("edit");
            }
        }
        return ($err);
    }
    /**
     * test if the document can be locked
     * it is not locked before, and the current user can edit document
     *
     * @return string empty means user can update else message of the raison
     */
    final public function CanLockFile()
    {
        $err = $this->canEdit();
        return $err;
    }
    /**
     * @see Doc::canUnLock
     * @return boolean true if current user can lock file
     */
    public function canLock()
    {
        return ($this->CanLockFile() == "");
    }
    /**
     * @see Doc::canLock
     * @return bool true if current user can lock file
     */
    public function canUnLock()
    {
        return ($this->CanUnLockFile() == "");
    }
    /**
     * test if the document can be unlocked
     * @see Doc::CanLockFile()
     * @see Doc::CanUpdateDoc()
     * @return string empty means user can update else message of the raison
     */
    final public function CanUnLockFile()
    {
        if ($this->userid == 1) return ""; // admin can do anything
        $err = "";
        if ($this->locked != 0) { // if is already unlocked
            if ($this->profid > 0) $err = $this->Control("unlock"); // first control unlock privilege
            else $err = _("cannot unlock"); // not control unlock if the document is not controlled
            
        }
        if ($err != "") $err = $this->canEdit();
        else {
            $err = $this->Control("edit");
            if ($err != "") {
                if ($this->profid > 0) {
                    $err = $this->Control("unlock");
                }
            }
        }
        return ($err);
    }
    /**
     * test if the document is locked
     * @see Doc::canLockFile()
     * @param bool $my if true test if it is lock of current user
     *
     * @return bool true if locked. If $my return true if it is locked by another user
     */
    final public function isLocked($my = false)
    {
        if ($my) {
            if ($this->userid == 1) {
                if ($this->locked == 1) return false;
            } elseif (abs($this->locked) == $this->userid) return false;
        }
        return (($this->locked > 0) || ($this->locked < - 1));
    }
    /**
     * test if the document is confidential
     * @return bool true if confidential and current user is not authorized
     */
    final public function isConfidential()
    {
        return (($this->confidential > 0) && ($this->controlId($this->profid, 'confidential') != ""));
    }
    /**
     * return the family document where the document comes from
     * @deprecated use {@link Doc::getFamilyDocument} instead
     * @see Doc::getFamilyDocument
     * @return DocFam
     */
    final public function getFamDoc()
    {
        deprecatedFunction();
        return $this->getFamilyDocument();
    }
    /**
     * return the family document where the document comes from
     * @api return family odcument
     * @return DocFam
     */
    final public function getFamilyDocument()
    {
        static $famdoc = null;
        if (($famdoc === null) || ($famdoc->id != $this->fromid)) $famdoc = new_Doc($this->dbaccess, $this->fromid);
        return $famdoc;
    }
    /**
     * search the first document from its title
     * @param string $title the title to search (must be exactly the same title)
     * @return int document identifier
     */
    function getFreedomFromTitle($title)
    {
        
        $query = new QueryDb($this->dbaccess, "Doc");
        $query->basic_elem->sup_where = array(
            "title='" . $title . "'"
        );
        
        $table1 = $query->Query();
        $id = 0;
        if ($query->nb > 0) {
            $id = $table1[0]->id;
            
            unset($table1);
        }
        return $id;
    }
    /**
     * return family parameter
     *
     * @deprecated use {@link Doc::getFamilyParameterValue} instead
     * @see Doc::getFamilyParameterValue
     * @param string $idp parameter identifier
     * @param string $def default value if parameter not found or if it is null
     * @return string parameter value
     */
    public function getParamValue($idp, $def = "")
    {
        return $this->getFamilyParameterValue($idp, $def);
    }
    /**
     * return family parameter
     *
     * @api return family parameter value
     * @param string $idp parameter identifier
     * @param string $def default value if parameter not found or if it is null
     * @note The value of parameter can come from inherited family if its own value is empty.
     The value of parameter comes from default configuration value if no one value are set in its family or in a parent family.
     the default configuration value comes from inherited family if no default configuration.
     In last case, if no values and no configurated default values, the $def argument is returned
     * @return string parameter value
     */
    public function getFamilyParameterValue($idp, $def = "")
    {
        
        $r = $def;
        if (isset($this->_paramValue[$idp])) return $this->_paramValue[$idp];
        $r = $this->getParameterFamilyRawValue($idp, $def);
        /* @var NormalAttribute $paramAttr */
        $paramAttr = $this->getAttribute($idp);
        if (!$paramAttr) return $def;
        if ($paramAttr->phpfunc != "" && $paramAttr->phpfile == "") {
            $this->_paramValue[$idp] = $r;
            if ($paramAttr->inArray()) {
                $attributes_array = $this->attributes->getArrayElements($paramAttr->fieldSet->id);
                $max = 0;
                foreach ($attributes_array as $attr) {
                    $count = count($this->rawValueToArray($this->getFamilyParameterValue($attr->id)));
                    if ($count > $max) $max = $count;
                }
                $tmpVal = "";
                for ($i = 0; $i < $max; $i++) {
                    $val = $this->applyMethod($paramAttr->phpfunc, "", $i);
                    if ($val != $paramAttr->phpfunc) {
                        if ($tmpVal) $tmpVal.= "\n";
                        $tmpVal.= $val;
                    }
                }
                $r = $tmpVal;
            } else {
                $val = $this->getValueMethod($paramAttr->phpfunc);
                if ($val != $paramAttr->phpfunc) {
                    $r = $val;
                }
            }
        } else if ($r) {
            $this->_paramValue[$idp] = $r;
            $r = $this->getValueMethod($r, $r);
        }
        $this->_paramValue[$idp] = $r;
        return $r;
    }
    
    protected function getParameterFamilyRawValue($idp, $def)
    {
        if (!$this->fromid) return false;
        $fdoc = $this->getFamilyDocument();
        if (!$fdoc->isAlive()) $r = false;
        else $r = $fdoc->getParameterRawValue($idp, $def);
        return $r;
    }
    /**
     * return similar documents
     *
     * @param string $key1 first attribute id to perform search
     * @param string $key2 second attribute id to perform search
     * @return string parameter value
     */
    final public function getDocWithSameTitle($key1 = "title", $key2 = "")
    {
        include_once ("FDL/Lib.Dir.php");
        // --------------------------------------------------------------------
        $filter[] = "doctype!='T'";
        if ($this->initid > 0) $filter[] = sprintf("initid != %d", $this->initid); // not itself
        $filter[] = sprintf("%s=E'%s'", $key1, pg_escape_string($this->getRawValue($key1)));
        if ($key2 != "") $filter[] = sprintf("%s=E'%s'", $key2, pg_escape_string($this->getRawValue($key2)));
        $tpers = internalGetDocCollection($this->dbaccess, 0, 0, "ALL", $filter, 1, "LIST", $this->fromid);
        
        return $tpers;
    }
    /**
     * return the latest revision id with the indicated state
     * For the user the document is in the trash
     * @param string $state wanted state
     * @param bool $fixed set to true if not search in current state
     * @return int document id (0 if no found)
     */
    final public function getRevisionState($state, $fixed = false)
    {
        $ldoc = $this->GetRevisions("TABLE");
        $vdocid = 0;
        
        foreach ($ldoc as $k => $v) {
            if ($v["state"] == $state) {
                if ((($v["locked"] == - 1) && $fixed) || (!$fixed)) {
                    $vdocid = $v["id"];
                    break;
                }
            }
        }
        return $vdocid;
    }
    // --------------------------------------------------------------------
    final public function DeleteTemporary()
    {
        // --------------------------------------------------------------------
        $result = pg_query($this->init_dbid() , "delete from doc where doctype='T'");
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
        if ($this->lockdomainid > 0) return sprintf(_("document is booked in domain %s") , $this->getTitle($this->lockdomainid));
        $err = $this->Control("delete");
        
        return $err;
    }
    /**
     * Really delete document from database
     * @deprecated use {@link Doc::delete} instead
     * @see Doc::delete
     * @param bool $nopost set to true if no need tu call postDelete methods
     * @return string error message, if no error empty string
     */
    final public function ReallyDelete($nopost)
    {
        deprecatedFunction();
        return $this->_destroy($nopost);
    }
    /**
     * Really delete document from database
     * @param bool $nopost set to true if no need tu call postDelete methods
     * @return string error message, if no error empty string
     */
    final private function _destroy($nopost)
    {
        $err = DbObj::delete($nopost);
        if ($err == "") {
            $dvi = new DocVaultIndex($this->dbaccess);
            $err = $dvi->DeleteDoc($this->id);
            if ($this->name != '') {
                $this->exec_query(sprintf("delete from docname where name='%s'", pg_escape_string($this->name)));
            }
            $this->exec_query(sprintf("delete from docfrom where id='%s'", pg_escape_string($this->id)));
        }
        return $err;
    }
    /**
     * Set the document to zombie state
     * For the user the document is in the trash
     * @api Delete document
     * @param bool $really if true  really delete from database
     * @param bool $control if false don't control 'delete' acl
     * @param bool $nopost if true don't call {@link Doc::postDelete} and {@link Doc::preDelete}
     * @return string error message
     */
    final public function delete($really = false, $control = true, $nopost = false)
    {
        $err = '';
        if ($control) {
            // Control if the doc can be deleted
            $err = $this->PreDocDelete();
            if ($err != '') return $err;
        }
        
        if ($really) {
            if ($this->id != "") {
                // delete all revision also
                global $action;
                global $_SERVER;
                $appli = $action->parent;
                $this->addHistoryEntry(sprintf(_("Destroyed by action %s/%s from %s") , $appli->name, $action->name, isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : 'bash mode') , DocHisto::NOTICE);
                $this->addHistoryEntry(_("Document destroyed") , DocHisto::MESSAGE, "DELETE");
                $this->addLog('delete', array(
                    "really" => $really
                ));
                $rev = $this->GetRevisions();
                foreach ($rev as $k => $v) {
                    $v->_destroy($nopost);
                }
            }
        } else {
            // Control if the doc can be deleted
            if ($this->doctype == 'Z') $err = _("already deleted");
            if ($err != '') return $err;
            
            if (!$nopost) {
                $err = $this->preDelete();
                if ($err != '') return $err;
            }
            
            if ($this->doctype != 'Z') {
                
                if ($this->name != "") $this->exec_query(sprintf("delete from doc%d where name='%s' and doctype='Z'", $this->fromid, pg_escape_string($this->name))); // need to not have twice document with same name
                $this->doctype = 'Z'; // Zombie Doc
                $this->locked = - 1;
                $this->lmodify = 'D'; // indicate last delete revision
                $date = gettimeofday();
                $this->revdate = $date['sec']; // Delete date
                global $action;
                global $_SERVER;
                
                $appli = $action->parent;
                $this->addHistoryEntry(sprintf(_("delete by action %s/%s from %s") , $appli->name, $action->name, isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : 'bash mode') , DocHisto::NOTICE);
                $this->addHistoryEntry(_("document deleted") , DocHisto::MESSAGE, "DELETE");
                $this->addLog('delete', array(
                    "really" => $really
                ));
                
                $err = $this->modify(true, array(
                    "doctype",
                    "revdate",
                    "locked",
                    "owner",
                    "lmodify"
                ) , true);
                if ($err == "") {
                    if (!$nopost) {
                        $msg = $this->postDelete();
                        if ($msg != '') {
                            $this->addHistoryEntry($msg, HISTO_MESSAGE);
                        }
                    }
                    // delete all revision also
                    $rev = $this->GetRevisions();
                    foreach ($rev as $k => $v) {
                        if ($v->doctype != 'Z') {
                            $v->doctype = 'Z'; // Zombie Doc
                            if ($v->locked == - 1) $v->modify(true, array(
                                "doctype"
                            ) , true);
                        }
                    }
                }
            }
        }
        return $err;
    }
    /**
     * To restore a document which is in the trash
     * @see Doc::undelete
     * @deprecated use {@link Doc::undelete} instead
     * @return string error message (empty message if no errors);
     */
    final public function revive()
    {
        deprecatedFunction();
        return $this->undelete();
    }
    /**
     * To restore a document which is in the trash
     * @api restore deleted document
     * @see Doc::delete
     * @return string error message (empty message if no errors);
     */
    final public function undelete()
    {
        $err = "";
        if (($this->control('delete') == "") || ($this->userid == 1)) {
            if (!$this->isAlive()) {
                $err = $this->preUndelete();
                if ($err) return $err;
                $err = simpleQuery($this->dbaccess, sprintf("SELECT id from only doc%d where initid = %d order by id desc limit 1", $this->fromid, $this->initid) , $latestId, true, true);
                if ($err == "") {
                    if (!$latestId) $err = sprintf(_("document %s [%d] is strange") , $this->title, $this->id);
                    else {
                        $this->doctype = $this->defDoctype;
                        $this->locked = 0;
                        $this->id = $latestId;
                        $this->lmodify = 'Y'; // indicate last restoration
                        $this->modify(true, array(
                            "doctype",
                            "locked",
                            "lmodify"
                        ) , true);
                        $this->addHistoryEntry(_("revival document") , HISTO_MESSAGE, "REVIVE");
                        $msg = $this->postUndelete();
                        if ($msg) $this->addHistoryEntry($msg, HISTO_MESSAGE);
                        $this->addLog('undelete');
                        $rev = $this->getRevisions();
                        /**
                         * @var Doc $v
                         */
                        foreach ($rev as $k => $v) {
                            if ($v->doctype == 'Z') {
                                $v->doctype = $v->defDoctype;
                                $err.= $v->modify(true, array(
                                    "doctype"
                                ) , true);
                            }
                        }
                        if ($this->name) {
                            // force reset logival name if not set
                            $name = $this->name;
                            $this->name = '';
                            $this->modify(true, array(
                                "name"
                            ) , true);
                            $this->setLogicalName($name);
                        }
                    }
                }
            } else return sprintf(_("document %s [%d] is not in the trash") , $this->getTitle() , $this->id);
        } else return sprintf(_("need privilege delete to restore %s") , $this->getTitle());
        return $err;
    }
    /**
     * Adaptation of affect Method from DbObj because of inheritance table
     * this function is call from QueryDb and all fields can not be instanciate
     * @param array $array the data array
     * @param bool $more add values from values attributes needed only if cast document
     * @return void
     */
    final public function affect($array, $more = false)
    {
        if (is_array($array)) {
            if ($more) $this->ResetMoreValues();
            unset($this->uperm); // force recompute privileges
            foreach ($array as $k => $v) {
                if (!is_integer($k)) {
                    $this->$k = $v;
                }
            }
            $this->Complete();
            if ($more) $this->GetMoreValues();
            
            $this->_maskApplied = false;
            $this->_oldvalue = array();
            $this->_paramValue = array();
            $this->_setValueDetectChange = true;
            $this->childs = null;
            $this->constraintbroken = false;
            $this->fathers = null;
            $this->hasChanged = false;
            $this->htmlFormater = null;
            $this->lastRefreshError = '';
            $this->mvalues = array();
            $this->oooFormater = null;
            $this->textsend = array();
            
            $this->isset = true;
        }
    }
    /**
     * Set to default values before add new doc
     * @return void
     */
    function init()
    {
        $this->isset = false;
        $this->id = "";
        $this->initid = "";
        $nattr = $this->GetNormalAttributes();
        foreach ($nattr as $k => $v) {
            if (isset($this->$k) && ($this->$k != "")) $this->$k = "";
        }
    }
    // --------------------------------------------------------------------
    function description()
    {
        // --------------------------------------------------------------------
        return $this->title . " - " . $this->revision;
    }
    /**
     * use for system purpose
     *  prefer ::getFromDoc instead
     * @return int[]
     */
    final public function getFathersDoc()
    {
        // --------------------------------------------------------------------
        // Return array of father doc id : class document
        if ($this->fathers === null) {
            
            $this->fathers = array();
            if ($this->fromid > 0) {
                $fdoc = $this->getFamilyDocument();
                $this->fathers = $fdoc->GetFathersDoc();
                array_push($this->fathers, $this->fromid);
            }
        }
        return $this->fathers;
    }
    /**
     * Return array of fathers doc id : class document
     * @return int[]
     */
    final public function getFromDoc()
    {
        return $this->attributes->fromids;
    }
    /**
     * Return array of child family raw documents
     * @param int $id if -1 use child for current document else for the family identifier set
     * @param bool $controlcreate set to true to not return documents which cannot be created by current user
     * @return array raw docfam values
     */
    final public function getChildFam($id = - 1, $controlcreate = false)
    {
        if ($id == 0) return array();
        if (($id != - 1) || (!isset($this->childs))) {
            include_once ("FDL/Class.SearchDoc.php");
            if ($id == - 1) $id = $this->id;
            if (!isset($this->childs)) $this->childs = array();
            
            $s = new SearchDoc($this->dbaccess, -1);
            $s->addFilter("fromid = " . $id);
            $s->overrideViewControl();
            $table1 = $s->search();
            if ($table1) {
                foreach ($table1 as $k => $v) {
                    if ((!$controlcreate) || controlTdoc($v, "icreate")) {
                        $this->childs[$v["id"]] = $v;
                    }
                    $this->GetChildFam($v["id"], $controlcreate);
                }
            }
        }
        return $this->childs;
    }
    /**
     * return all revision documents
     * @param string $type LIST|TABLE il LIST return Doc object else if TABLE raw documents
     * @param int $limit limit of revision (by default the 200 latest revisions)
     * @return Doc[]|array
     */
    final public function getRevisions($type = "LIST", $limit = 200)
    {
        // Return the document revision
        $query = new QueryDb($this->dbaccess, strtolower(get_class($this)));
        //$query->AddQuery("revision <= ".$this->revision);
        $query->AddQuery("initid = " . $this->initid);
        $query->order_by = "revision DESC LIMIT $limit";
        
        $rev = $query->Query(0, 0, $type);
        if ($query->nb == 0) return array();
        return $rev;
    }
    /** get Latest Id of document
     *
     * @deprecated use {@link Doc::getLatestId} instead
     * @param bool $fixed if true latest fixed revision
     * @param bool $forcequery if true force recompute of id (use it in case of modification by another program)
     * @see Doc::getLatestId
     * @return int identifier of latest revision
     */
    final public function latestId($fixed = false, $forcequery = false)
    {
        deprecatedFunction();
        return $this->getLatestId($fixed, $forcequery);
    }
    /** get Latest Id of document
     *
     * @api get latest id of document
     * @param bool $fixed if true latest fixed revision
     * @param bool $forcequery if true force recompute of id (use it in case of modification by another program)
     * @return int identifier of latest revision
     */
    final public function getLatestId($fixed = false, $forcequery = false)
    {
        if ($this->id == "") return false;
        if (!$forcequery) {
            if (($this->locked != - 1) && (!$fixed)) return $this->id;
            if ($fixed && ($this->lmodify == "L")) return $this->id;
        }
        if (!$fixed) return getLatestDocId($this->dbaccess, $this->initid);
        $query = new QueryDb($this->dbaccess, strtolower(get_class($this)));
        $query->AddQuery("initid = " . $this->initid);
        if ($fixed) $query->AddQuery("lmodify = 'L'");
        elseif ($this->doctype != 'Z') $query->AddQuery("locked != -1");
        else {
            $query->order_by = "id desc";
        }
        $rev = $query->Query(0, 2, "TABLE");
        
        if ($this->doctype != 'Z') {
            if (count($rev) > 1) addWarningMsg(sprintf("document %d : multiple alive revision", $this->initid));
        }
        return $rev[0]["id"];
    }
    /**
     * get version of document
     * can be redefined by child document classes if needed
     * @return string
     */
    public function getVersion()
    {
        $tversion = array();
        if (isset($this->attributes->attr)) {
            foreach ($this->attributes->attr as $k => $v) {
                if ((get_class($v) == "NormalAttribute") && ($v->getOption("version") == "yes")) {
                    $tversion[] = $this->getRawValue($v->id);
                }
            }
        }
        if (count($tversion) > 0) $version = implode(" ", $tversion);
        else $version = $this->version;
        return $version;
    }
    /**
     * return the string label text for a id (label depends of current user locale)
     * @param string $idAttr attribute identifier
     * @return string
     */
    final public function getLabel($idAttr)
    {
        if (isset($this->attributes->attr[$idAttr])) return $this->attributes->attr[$idAttr]->getLabel();
        return _("unknow attribute");
    }
    /**
     * return the property value like id, initid, revision, ...
     * @deprecated use {@link Doc::getPropertyValue} instead
     * @see Doc::getPropertyValue
     * @param string $prop property identifier
     * @return string false if not an property
     */
    final public function getProperty($prop)
    {
        deprecatedFunction();
        return $this->getPropertyValue($prop);
    }
    /**
     * return the property value like id, initid, revision, ...
     * @api get property value
     * @param string $prop property identifier
     * @return string false if not an property
     */
    final public function getPropertyValue($prop)
    {
        $prop = trim(strtolower($prop));
        if (!in_array($prop, $this->fields)) return false;
        if (isset($this->fields[$prop])) return false; // it's an attribute
        return $this->$prop;
    }
    /**
     * Return the tag object for the document
     *
     * @throws Dcp\Exception
     * @return TagManager &$tag object reference use to modify tags
     */
    final public function &tag()
    {
        static $tag = null;
        
        if (empty($tag) || $tag->docid != $this->initid) {
            if (class_exists("TagManager")) {
                $tag = new TagManager($this, $this->initid);
            } else {
                throw new Dcp\Exception("Need install dynacase-tags module.\n");
            }
        }
        return $tag;
    }
    /**
     * return the attribute object for a id
     * the attribute can be defined in fathers
     * @api get attribute object
     * @param string $idAttr attribute identifier
     * @param BasicAttribute &$oa object reference use this if want to modify attribute
     * @return BasicAttribute|bool
     */
    final public function &getAttribute($idAttr, &$oa = null)
    {
        if (!$this->_maskApplied) $this->ApplyMask();
        $idAttr = strtolower($idAttr);
        $oas = $this->getAttributes();
        
        if (isset($this->attributes->attr[$idAttr])) $oa = $oas[$idAttr];
        else $oa = false;
        
        return $oa;
    }
    /**
     * return all the attributes object
     * the attribute can be defined in fathers
     * @return BasicAttribute[]
     */
    final public function &getAttributes()
    {
        $fromname = ($this->doctype == 'C') ? $this->name : $this->fromname;
        $aFromName = isset($this->attributes->fromname) ? $this->attributes->fromname : '';
        if ($aFromName != $fromname) {
            // reset when use partial cache
            $fromid = ($this->doctype == 'C') ? $this->id : $this->fromid;
            $adocClassName = "ADoc" . $fromid;
            $classname = "Doc" . $fromid;
            $GEN = getGen($this->dbaccess);
            $includePath = "FDL$GEN/Class.$classname.php";
            if (file_exists($includePath)) {
                include_once ($includePath);
                $this->attributes = new $adocClassName();
            }
        }
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
    final public function getDefaultView($edition = false, $extract = "all")
    {
        $vid = 0;
        if ($this->cvid > 0) {
            // special controlled view
            
            /**
             * @var CVDoc $cvdoc
             */
            $cvdoc = new_Doc($this->dbaccess, $this->cvid);
            $cvdoc->set($this);
            
            $view = $cvdoc->getPrimaryView($edition);
            
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
     * @return string error message
     */
    final public function setMask($mid)
    {
        $this->mid = $mid;
        if (isset($this->attributes->attr)) {
            // reinit mask before apply
            foreach ($this->attributes->attr as $k => $v) {
                $this->attributes->attr[$k]->mvisibility = $v->visibility;
            }
        }
        return $this->ApplyMask($mid);
    }
    /**
     * apply visibility mask
     *
     * @param int $mid mask ident, if not set it is found from possible workflow
     * @param bool $force set to true to force reapply mask even it is already applied
     * @return string error message
     */
    final public function applyMask($mid = 0, $force = false)
    {
        // copy default visibilities
        $err = '';
        $this->_maskApplied = true;
        $oas = $this->getAttributes();
        if (is_array($oas)) {
            foreach ($oas as $k => $v) {
                if ($oas[$k]) $oas[$k]->mvisibility = ComputeVisibility($v->visibility, (empty($v->fieldSet)) ? '' : $v->fieldSet->mvisibility, (!empty($v->fieldSet->fieldSet)) ? $v->fieldSet->fieldSet->mvisibility : '');
            }
        }
        $argMid = $mid;
        if ((!$force) && (($this->doctype == 'C') || (($this->doctype == 'T') && ($mid == 0)))) return '';
        // modify visibilities if needed
        if ((!is_numeric($mid)) && ($mid != "")) {
            $imid = getIdFromName($this->dbaccess, $mid);
            if (!$imid) {
                $err = ErrorCode::getError('DOC1004', $argMid, $this->getTitle());
                return $err;
            } else {
                $mid = $imid;
            }
        }
        
        if ($mid == 0) $mid = $this->mid;
        if ($mid == Doc::USEMASKCVVIEW || $mid == Doc::USEMASKCVEDIT) {
            if ($this->cvid) {
                /**
                 * @var \Dcp\Family\CVDoc $cvdoc
                 */
                $cvdoc = new_Doc($this->dbaccess, $this->cvid);
                if ($cvdoc->isAlive()) {
                    $cvdoc->set($this);
                    $vid = $this->getDefaultView(($mid == Doc::USEMASKCVEDIT) , "id");
                    if ($vid != '') {
                        $tview = $cvdoc->getView($vid);
                        $mid = ($tview !== false) ? $tview["CV_MSKID"] : 0;
                    }
                }
            }
            if ($mid == Doc::USEMASKCVVIEW || $mid == Doc::USEMASKCVEDIT) {
                $mid = 0;
            }
        }
        if ($mid == 0) {
            if (($this->wid > 0) && ($this->wid != $this->id)) {
                // search mask from workflow
                
                /**
                 * @var $wdoc WDoc
                 */
                $wdoc = new_Doc($this->dbaccess, $this->wid);
                if ($wdoc->isAlive()) {
                    if ($this->id == 0) {
                        $wdoc->set($this);
                    }
                    $mid = $wdoc->getStateMask($this->state);
                    if ((!is_numeric($mid)) && ($mid != "")) $mid = getIdFromName($this->dbaccess, $mid);
                }
            }
        }
        
        if ($mid) {
            if (!$argMid) $argMid = $mid;
            /**
             * @var \Dcp\Family\MASK $mdoc
             */
            $mdoc = new_Doc($this->dbaccess, $mid);
            if ($mdoc->isAlive()) {
                if (is_a($mdoc, '\Dcp\Family\Mask')) {
                    
                    $maskFam = $mdoc->getRawValue("msk_famid");
                    if (!in_array($maskFam, $this->getFromDoc())) {
                        $err = ErrorCode::getError('DOC1002', $argMid, $this->getTitle() , getNameFromId($this->dbaccess, $maskFam));
                    } else {
                        $tvis = $mdoc->getCVisibilities();
                        foreach ($tvis as $k => $v) {
                            if (isset($oas[$k])) {
                                if ($v != "-") $oas[$k]->mvisibility = $v;
                            }
                        }
                        $tdiff = array_diff(array_keys($oas) , array_keys($tvis));
                        // compute frame before because has no order
                        foreach ($tdiff as $k) {
                            $v = $oas[$k];
                            if ($v->type == "frame") {
                                $oas[$k]->mvisibility = ComputeVisibility($v->visibility, isset($v->fieldSet) ? $v->fieldSet->mvisibility : '', '');
                            }
                        }
                        foreach ($tdiff as $k) {
                            $v = $oas[$k];
                            if ($v->type == "array") {
                                $oas[$k]->mvisibility = ComputeVisibility($v->visibility, isset($v->fieldSet) ? $v->fieldSet->mvisibility : '', isset($v->fieldSet->fieldSet) ? $v->fieldSet->fieldSet->mvisibility : '');
                            }
                        }
                        // recompute loosed attributes
                        foreach ($tdiff as $k) {
                            $v = $oas[$k];
                            if ($v->type != "frame") {
                                $oas[$k]->mvisibility = ComputeVisibility($v->visibility, isset($v->fieldSet) ? $v->fieldSet->mvisibility : '', isset($v->fieldSet->fieldSet) ? $v->fieldSet->fieldSet->mvisibility : '');
                            }
                        }
                        // modify needed attribute also
                        $tneed = $mdoc->getNeedeeds();
                        foreach ($tneed as $k => $v) {
                            if (isset($oas[$k])) {
                                if ($v == "Y") $oas[$k]->needed = true;
                                else if ($v == "N") $oas[$k]->needed = false;
                            }
                        }
                    }
                } else {
                    $err = ErrorCode::getError('DOC1001', $argMid, $mdoc->fromname, $this->getTitle());
                }
            } else {
                $err = ErrorCode::getError('DOC1000', $argMid, $this->getTitle());
            }
        }
        if (empty($this->attributes->isOrdered)) {
            uasort($this->attributes->attr, "tordered");
            $this->attributes->isOrdered = true;
        }
        if ($err) error_log($err);
        return $err;
    }
    /**
     * return all the attributes except frame & menu & action
     * @param boolean $onlyopt get only optional attributes
     *
     * @return NormalAttribute[]
     */
    final public function getNormalAttributes($onlyopt = false)
    {
        if (!$this->_maskApplied) $this->ApplyMask();
        if ((isset($this->attributes)) && (method_exists($this->attributes, "GetNormalAttributes"))) return $this->attributes->GetNormalAttributes($onlyopt);
        else return array();
    }
    /**
     * return  frame attributes
     *
     * @return  FieldSetAttribute[]
     */
    final public function getFieldAttributes()
    {
        if (!$this->_maskApplied) $this->ApplyMask();
        $tsa = array();
        
        foreach ($this->attributes->attr as $k => $v) {
            if (get_class($v) == "FieldSetAttribute") $tsa[$v->id] = $v;
        }
        return $tsa;
    }
    /**
     * return action attributes
     *
     * @return  ActionAttribute[]
     */
    final public function getActionAttributes()
    {
        if (!$this->_maskApplied) $this->ApplyMask();
        $tsa = array();
        $at = $this->attributes->GetActionAttributes();
        foreach ($at as $k => $v) {
            if ($v->mvisibility != 'H') $tsa[$v->id] = $v;
        }
        return $tsa;
    }
    /**
     * return all the attributes object for abstract
     * the attribute can be defined in fathers
     * @return  NormalAttribute[]
     */
    final public function getAbstractAttributes()
    {
        if (!$this->_maskApplied) $this->ApplyMask();
        $tsa = array();
        
        if (isset($this->attributes->attr)) {
            foreach ($this->attributes->attr as $k => $v) {
                /**
                 * @var NormalAttribute $v
                 */
                if ((get_class($v) == "NormalAttribute") && ($v->usefor != 'Q') && ($v->isInAbstract)) $tsa[$v->id] = $v;
            }
        }
        return $tsa;
    }
    /**
     * return all the attributes object for title
     * the attribute can be defined in fathers
     * @return  NormalAttribute[]
     */
    final public function getTitleAttributes()
    {
        if (!$this->_maskApplied) $this->ApplyMask();
        $tsa = array();
        if (isset($this->attributes->attr)) {
            foreach ($this->attributes->attr as $k => $v) {
                /**
                 * @var NormalAttribute $v
                 */
                if ((get_class($v) == "NormalAttribute") && ($v->isInTitle)) $tsa[$v->id] = $v;
            }
        }
        return $tsa;
    }
    /**
     * return all the attributes that can be use in profil
     *
     * @return  BasicAttribute[]
     */
    final public function getProfilAttributes()
    {
        if (!$this->_maskApplied) $this->ApplyMask();
        $tsa = array();
        $tsb = array();
        $wopt = false;
        if (isset($this->attributes->attr)) {
            foreach ($this->attributes->attr as $k => $v) {
                if ($v->type == "docid") {
                    if ($v->getOption("isuser") != "") {
                        if ($v->getOption("isuser") == "yes") $tsb[$v->id] = $v;
                        $wopt = true;
                    }
                } elseif ($v->type == "account") {
                    $wopt = true;
                    if ($v->getOption("isuser") != "no") $tsb[$v->id] = $v;
                }
            }
        }
        if ($wopt) return $tsb;
        return $tsa;
    }
    /**
     * return all the attributes object for to e use in edition
     * the attribute can be defined in fathers
     * @param bool $onlyopt deprecated arguments
     * @return  NormalAttribute[]
     */
    final public function getInputAttributes($onlyopt = false)
    {
        if (!$this->_maskApplied) $this->ApplyMask();
        $tsa = array();
        
        foreach ($this->attributes->attr as $k => $v) {
            if ((get_class($v) == "NormalAttribute") && (!$v->inArray()) && ($v->mvisibility != "I")) { // I means not editable
                if ((($this->usefor == "Q") && ($v->usefor == "Q")) || (($this->usefor != "Q") && ((($v->usefor != "Q") && (!$onlyopt)) || (($v->usefor == "O") && ($onlyopt))))) $tsa[$v->id] = $v; //special parameters
                
            }
        }
        return $tsa;
    }
    /**
     * return all the parameters definition for its family
     * the attribute can be defined in fathers
     * @return  NormalAttribute[]
     */
    final public function getParamAttributes()
    {
        
        if (!$this->_maskApplied) $this->ApplyMask();
        if ((isset($this->attributes)) && (method_exists($this->attributes, "getParamAttributes"))) return $this->attributes->getParamAttributes();
        else return array();
    }
    /**
     * return all the attributes object for abstract
     * the attribute can be defined in fathers
     * @param bool $onlyfile set to true if don't want images
     * @return  NormalAttribute[]
     */
    final public function getFileAttributes($onlyfile = false)
    {
        if (!$this->_maskApplied) $this->ApplyMask();
        $tsa = array();
        
        foreach ($this->attributes->attr as $k => $v) {
            if ((get_class($v) == "NormalAttribute") && ($v->usefor != 'Q') && ((($v->type == "image") && (!$onlyfile)) || ($v->type == "file"))) $tsa[$v->id] = $v;
        }
        return $tsa;
    }
    /**
     * return files properties of file attributes
     *
     * @return array
     */
    final public function getFilesProperties()
    {
        $dvi = new DocVaultIndex($this->dbaccess);
        $tvid = $dvi->getVaultIds($this->id);
        $tinfo = array();
        $vf = newFreeVaultFile($this->dbaccess);
        foreach ($tvid as $vid) {
            $info = null;
            $err = $vf->Retrieve($vid, $info);
            $t = get_object_vars($info);
            $t["vid"] = $vid;
            if ($err == "") $tinfo[] = $t;
        }
        
        return $tinfo;
    }
    /**
     * verify if has some files waiting conversion
     *
     * @return bool
     */
    final public function hasWaitingFiles()
    {
        $dvi = new DocVaultIndex($this->dbaccess);
        $tvid = $dvi->getVaultIds($this->id);
        if (count($tvid) == 0) return false;
        $sql = sprintf("select id_file from vaultdiskstorage where teng_state=%d and %s limit 1", TransformationEngine::status_waiting, getSqlCond($tvid, "id_file", true));
        simpleQuery($this->dbaccess, $sql, $waiting, true, true);
        return ($waiting != false);
    }
    /**
     * reset Conversion of file
     * update $attrid_txt table column
     * @param string $attrid file attribute identifier
     * @param int $index index in case of multiple attribute
     * @apiExpose
     * @return string error message
     */
    public function resetConvertVaultFile($attrid, $index)
    {
        $err = $this->canEdit();
        if ($err) {
            return $err;
        }
        $val = $this->getMultipleRawValues($attrid, false, $index);
        if (($index == - 1) && (count($val) == 1)) {
            $val = $val[0];
        }
        
        if ($val) {
            $info = $this->getFileInfo($val);
            if ($info) {
                $ofout = new VaultDiskStorage($this->dbaccess, $info["id_file"]);
                if ($ofout->isAffected()) {
                    $err = $ofout->delete();
                }
            }
        }
        return $err;
    }
    /**
     * send a request to TE to convert files
     * update $attrid_txt table column
     * waiting end of conversion
     * @param string $va value of file attribute like mime|vid|name
     * @param string $engine the name of transformation
     * @param bool $isimage set true if it is an image (error returns is not same)
     * @return string new file reference
     */
    public function convertVaultFile($va, $engine, $isimage = false)
    {
        include_once ("FDL/Lib.Vault.php");
        $engine = strtolower($engine);
        $value = '';
        if (is_array($va)) return "";
        $err = '';
        if (getParam("TE_ACTIVATE") == "yes") {
            if (preg_match(PREGEXPFILE, $va, $reg)) {
                $vidin = $reg[2];
                $vidout = 0;
                $info = vault_properties($vidin, $engine);
                // in case of server not reach : try again
                if (!is_object($info)) {
                    // not found : create it
                    $info = new VaultFileInfo();
                }
                if ($info->teng_state == TransformationEngine::error_connect) {
                    $info->teng_state = TransformationEngine::status_inprogress;
                }
                if ((!$info->teng_vid) || ($info->teng_state == TransformationEngine::status_inprogress)) {
                    $vf = newFreeVaultFile($this->dbaccess);
                    if (!$info->teng_vid) {
                        // create temporary file
                        $value = sprintf(_("conversion %s in progress") , $engine);
                        if ($isimage) {
                            $filename = getParam("CORE_PUBDIR") . "/Images/workinprogress.png";
                        } else $filename = uniqid(getTmpDir() . "/conv") . ".txt";
                        $nc = file_put_contents($filename, $value);
                        $vidout = 0;
                        $err = $vf->Store($filename, false, $vidout, "", $engine, $vidin);
                        if ($err) {
                            $this->addHistoryEntry(sprintf(_("convert file %s as %s failed : %s") , $info->name, $engine, $err) , HISTO_ERROR);
                            error_log($err);
                            return '';
                        }
                        $info = vault_properties($vidin);
                        if (!$isimage) {
                            unlink($filename);
                            $mime = 'text/plain';
                        } else {
                            $mime = 'image/png';
                        }
                        
                        $value = "$mime|$vidout";
                        if ($err == "") $vf->rename($vidout, sprintf(_("conversion of %s in progress") . ".%s", $info->name, $engine));
                        
                        $this->addHistoryEntry("value $engine : $value");
                    } else {
                        if ($err == "") {
                            $info1 = vault_properties($vidin);
                            $vidout = $info->id_file;
                            $vf->rename($vidout, sprintf(_("update of %s in progress") . ".%s", $info1->name, $engine));
                            $value = $info->mime_s . '|' . $info->id_file;
                        }
                    }
                    
                    $err = vault_generate($this->dbaccess, $engine, $vidin, $vidout, $isimage, $this->initid);
                    if ($err != "") {
                        $this->addHistoryEntry(sprintf(_("convert file %s as %s failed : %s") , $info->name, $engine, $err) , HISTO_ERROR);
                    }
                } else {
                    if ($isimage) {
                        if ($info->teng_state < 0) {
                            if ($info->teng_state == - 1) $value = "convertfail.png";
                            else $value = "convertimpossible.png";
                        } else {
                            if ($info->teng_state == 1) $value = $info->mime_s . '|' . $info->id_file . '|' . $info->name;
                        }
                    } else {
                        $value = $info->mime_s . '|' . $info->id_file . '|' . $info->name;
                    }
                }
            }
        }
        return $value;
    }
    /**
     * Return all the attributes object for popup menu
     * the attribute can be defined in fathers
     * @param bool $viewhidden set to true if need all defined menu (hidden also)
     * @return MenuAttribute[]
     */
    function getMenuAttributes($viewhidden = false)
    {
        if (!$this->_maskApplied) $this->ApplyMask();
        $tsa = array();
        
        reset($this->attributes->attr);
        foreach ($this->attributes->attr as $k => $v) {
            if (((get_class($v) == "MenuAttribute")) && (($v->mvisibility != 'H') || $viewhidden)) $tsa[$v->id] = $v;
        }
        return $tsa;
    }
    /**
     * return all the necessary attributes
     * @param bool $parameters set to true if want parameters instead of attributes
     * @return NormalAttribute[]
     */
    final public function getNeededAttributes($parameters = false)
    {
        $tsa = array();
        
        if ($parameters) {
            foreach ($this->attributes->attr as $k => $v) {
                /**
                 * @var NormalAttribute $v
                 */
                if ((get_class($v) == "NormalAttribute") && ($v->needed) && ($v->usefor == 'Q')) $tsa[$v->id] = $v;
            }
        } else {
            if (!$this->_maskApplied) $this->ApplyMask();
            foreach ($this->attributes->attr as $k => $v) {
                /**
                 * @var NormalAttribute $v
                 */
                if ((get_class($v) == "NormalAttribute") && ($v->needed) && ($v->usefor != 'Q')) $tsa[$v->id] = $v;
            }
        }
        return $tsa;
    }
    /**
     * verify if all needed attributes are set
     *
     * @return string error message if some needed attributes are empty
     */
    final public function isCompleteNeeded()
    {
        $tsa = $this->GetNeededAttributes();
        $err = "";
        foreach ($tsa as $k => $v) {
            if ($this->getRawValue($v->id) == "") $err.= sprintf(_("%s needed\n") , $v->getLabel());
        }
        return $err;
    }
    /**
     * verify if attribute equals $b
     * to be use in constraint
     * @param string $a attribute identifier
     * @param string $b value
     * @return bool
     */
    final public function equal($a, $b)
    {
        return ($this->$a == $b);
    }
    /**
     * return list of attribut which can be exported
     * @param bool $withfile true if export also file attribute
     * @param bool $forcedefault if true preference FREEDOM_EXPORTCOLS are not read
     * @return NormalAttribute[]
     */
    final public function getExportAttributes($withfile = false, $forcedefault = false)
    {
        include_once ("GENERIC/generic_util.php");
        global $action;
        
        if ($this->doctype == 'C') $famid = $this->id;
        else $famid = $this->fromid;
        if (!$this->_maskApplied) $this->ApplyMask();
        $tsa = array();
        if (isset($this->attributes->attr)) {
            $pref = getFamilyParameter($action, $famid, "FREEDOM_EXPORTCOLS");
            if ((!$forcedefault) && ($pref != "")) {
                
                $tpref = explode(";", $pref);
                
                foreach ($this->attributes->attr as $k => $v) {
                    if (in_array($v->id, $tpref)) {
                        $tsa[$v->id] = $v;
                    }
                }
            } else {
                foreach ($this->attributes->attr as $k => $v) {
                    if (get_class($v) == "NormalAttribute" && $v->usefor != 'Q') {
                        
                        if (($v->type != "array") && ($withfile || (($v->type != "image") && ($v->type != "file")))) $tsa[$v->id] = $v;
                    }
                }
            }
        }
        return $tsa;
    }
    /**
     * return all the attributes object for import
     * @return NormalAttribute[]
     */
    final public function getImportAttributes()
    {
        
        if (!$this->_maskApplied) $this->ApplyMask();
        $tsa = array();
        $tattr = $this->attributes->attr;
        foreach ($tattr as $k => $v) {
            /**
             * @var NormalAttribute $v
             */
            
            if ((get_class($v) == "NormalAttribute") && (($v->mvisibility == "W") || ($v->mvisibility == "O") || ($v->type == "docid")) && ($v->type != "array")) {
                
                if (preg_match("/\(([^\)]+)\):(.+)/", $v->phpfunc, $reg)) {
                    
                    $aout = explode(",", $reg[2]);
                    foreach ($aout as $ka => $va) {
                        $ra = $this->GetAttribute($va);
                        if ($ra) $tsa[strtolower($va) ] = $ra;
                    }
                }
                $tsa[$v->id] = $v;
            }
        }
        
        uasort($tsa, "tordered");
        return $tsa;
    }
    /**
     * return all the attributes which can be sorted
     * @return NormalAttribute[]
     */
    public function getSortAttributes()
    {
        $tsa = array();
        $nattr = $this->GetNormalAttributes();
        reset($nattr);
        
        foreach ($nattr as $k => $a) {
            if ($a->repeat || ($a->visibility == "I") || ($a->visibility == "O") || ($a->type == "longtext") || ($a->type == "xml") || ($a->type == "htmltext") || ($a->type == "image") || ($a->type == "file") || ($a->getOption('sortable') != 'asc' && $a->getOption('sortable') != 'desc')) {
                continue;
            }
            $tsa[$a->id] = $a;
        }
        return $tsa;
    }
    /**
     * recompute the title from attribute values
     */
    final public function refreshTitle()
    {
        
        if ($this->doctype == 'C') return; // no refresh for family  document
        $ltitle = $this->GetTitleAttributes();
        
        $title1 = "";
        foreach ($ltitle as $k => $v) {
            if ($this->getRawValue($v->id) != "") {
                if ($v->inArray() && ($v->getOption('multiple') == 'yes')) {
                    $title1.= mb_trim(str_replace("<BR>", " ", $this->getRawValue($v->id))) . " ";
                } else $title1.= $this->getRawValue($v->id) . " ";
            }
        }
        if (mb_trim($title1) != "") $this->title = mb_substr(mb_trim(str_replace("\n", " ", $title1)) , 0, 255); // restric to 256 char
        $this->title = mb_substr(mb_trim(str_replace("\n", " ", $this->getCustomTitle())) , 0, 255);
    }
    /**
     * call after construct
     * @return void
     */
    function postConstructor()
    {
    }
    /**
     * no in postUpdate method :: call this only if real change (values)
     * @deprecated hook use {@link Doc::postStore} instead
     * @see Doc::postStore
     * @return string error message
     */
    function postModify()
    {
        return "";
    }
    /**
     * no in postUpdate method :: call this only if real change (values)
     * @api hook called in ::store()
     * @return string error message
     */
    function postStore()
    {
        // to be defined in child class
        return "";
    }
    /**
     * call in beging store before constraint verification
     * if error message is returned store is aborted and the message is returned by store method
     * @api hook called in Doc::store()
     * @see Doc::store()
     * @return string error message
     */
    function preStore()
    {
        // to be defined in child class
        return "";
    }
    /**
     * called when user edit a document FDL/editcard
     * @api hook called when compose edit document web interface
     */
    function preEdition()
    {
        // to be defined in child class
        return "";
    }
    /**
     * called when user view a document FDL/fdl_card
     * @api hook called when compose view document web interface
     */
    function preConsultation()
    {
        // to be defined in child class
        return "";
    }
    /**
     * call in doc::postInsert method
     * @api hook called when document is created in database
     * @return string error message
     */
    function postCreated()
    {
        // to be defined in child class
        return "";
    }
    /**
     * call in doc::add method
     * if return message, creation is aborted
     * @api hook called before document is created in database
     * @return string error message
     */
    function preCreated()
    {
        // to be defined in child class
        return "";
    }
    /**
     * call when doc is being imported before any modification
     * if return non null string import will ne aborted
     * @api hook called when import document - before import it
     * @param array $extra extra parameters
     * @return string error message, if no error empty string
     */
    function preImport(array $extra = array())
    {
    }
    /**
     * call when doc is imported after databases modification
     * the error message will appeared like message
     * @api hook called when import document - after it is imported
     * @param array $extra extra parameters
     * @return string warning message, if no warning empty string
     */
    function postImport(array $extra = array())
    {
    }
    /**
     * call when doc is being revised before new document is created
     * if return non null string revision will ne aborted
     * @api hook called when revise document - before revise it
     * @see Doc::revise
     * @return string error message, if no error empty string
     */
    function preRevise()
    {
    }
    /**
     * call when doc is revised after new document is created
     * the error message will appeared like message
     * @api hook called when revise document - after it is revided
     * @see Doc::revise
     * @return string message - message is added to history
     */
    function postRevise()
    {
    }
    /**
     * call when doc is being undelete
     * if return non null string undelete will ne aborted
     * @api hook called before undelete document
     * @see Doc::undelete
     * @return string error message, if no error empty string
     */
    function preUndelete()
    {
    }
    /**
     * call when doc is revived after resurrection in database
     * the error message will appeared like message
     * @api hook called after undelete document
     * @return string warning message, if no warning empty string
     */
    function postUndelete()
    {
    }
    /**
     * call when doc is being undelete
     * if return non null string undelete will ne aborted
     * @deprecated hook use {@link Doc:::preUndelete} instead
     * @see Doc::preUndelete
     * @return string error message, if no error empty string
     */
    function preRevive()
    {
    }
    /**
     * call when doc is revived after resurrection in database
     * the error message will appeared like message
     * @deprecated hook use {@link Doc:::postUndelete} instead
     * @see Doc::postUndelete
     * @return string warning message, if no warning empty string
     */
    function postRevive()
    {
    }
    /**
     * set attribute title value
     * the first value of type text use for title will be modify to have the new title
     * @param string $title new title
     */
    final public function setTitle($title)
    {
        $ltitle = $this->getTitleAttributes();
        $otitle = '';
        foreach ($ltitle as $at) {
            if (($at->type == 'text') && (($at->visibility == 'W') || ($at->visibility == 'O')) && (!$at->inArray())) {
                $otitle = $at;
                break;
            }
        }
        if ($otitle) {
            /**
             * @var NormalAttribute $otitle
             */
            $idt = $otitle->id;
            
            $this->title = str_replace("\n", " ", $title);
            $this->setvalue($idt, $title);
        }
    }
    /**
     * return all attribute values
     * @return array all attribute values, index is attribute identifier
     */
    final public function getValues()
    {
        $lvalues = array();
        //    if (isset($this->id) && ($this->id>0)) {
        $nattr = $this->GetNormalAttributes();
        foreach ($nattr as $k => $v) {
            $lvalues[$v->id] = $this->getRawValue($v->id);
        }
        // }
        $lvalues = array_merge($lvalues, $this->mvalues); // add more values possibilities
        reset($lvalues);
        return $lvalues;
    }
    //-------------------------------------------------------------------
    
    
    /**
     * return the raw value (database value) of an attribute document
     * @api get the value of an attribute
     * @param string $idAttr attribute identifier
     * @param string $def default value returned if attribute not found or if is empty
     * @code
     $doc = new_Doc('',7498 );
     if ($doc->isAlive()) {
     $rev = $doc->getPropertyValue('revision');
     $order = $doc->getRawValue("tst_order");
     $level = $doc->getRawValue("tst_level","0");
     }
     * @endcode
     * @see Doc::getAttributeValue
     * @return string the attribute value
     */
    final public function getRawValue($idAttr, $def = "")
    {
        $lidAttr = strtolower($idAttr);
        if (isset($this->$lidAttr) && ($this->$lidAttr != "")) return $this->$lidAttr;
        
        return $def;
    }
    /**
     * get a typed value of an attribute
     *
     * return value of an attribute
     * return null if value is empty
     * return an array for multiple value
     * return date in DateTime format, number in int or double
     * @api get typed value of an attribute
     * @param string $idAttr attribute identifier
     * @throws Dcp\Exception DOC0114 code
     * @see ErrorCodeDoc::DOC0114
     * @return mixed the typed value
     */
    final public function getAttributeValue($idAttr)
    {
        /**
         * @var \NormalAttribute $oa
         */
        $oa = $this->getAttribute($idAttr);
        if (!$oa) {
            throw new Dcp\Exception('DOC0114', $idAttr, $this->title, $this->fromname);
        }
        
        if (empty($oa->isNormal)) {
            throw new Dcp\Exception('DOC0116', $idAttr, $this->title, $this->fromname);
        }
        return Dcp\AttributeValue::getTypedValue($this, $oa);
    }
    /**
     * Set a value to a document's attribute
     * the affectation is only in object. To set modification in database the Doc::store() method must be
     * call after modification
     * @api Set a value to an attribute
     * @param string $idAttr attribute identifier
     * @param mixed $value the new value - value format must be compatible with type
     * @throws Dcp\Exception
     * @see ErrorCodeDoc::DOC0115
     * @see ErrorCodeDoc::DOC0117
     * @return void
     */
    final public function setAttributeValue($idAttr, $value)
    {
        $localRecord = array();
        $oa = $this->getAttribute($idAttr);
        if (!$oa) {
            throw new Dcp\Exception('DOC0115', $idAttr, $this->title, $this->fromname);
        }
        if (empty($oa->isNormal)) {
            throw new Dcp\Exception('DOC0117', $idAttr, $this->title, $this->fromname);
        }
        if ($oa->type === "array") {
            // record current array values
            $ta = $this->attributes->getArrayElements($oa->id);
            foreach ($ta as $k => $v) {
                $localRecord[$k] = $this->getRawValue($v->id);
            }
        }
        Dcp\AttributeValue::setTypedValue($this, $oa, $value);
        if ($oa->type === "array") {
            foreach ($localRecord as $aid => $v) {
                if ($this->$aid !== $v) {
                    $this->_oldvalue[$aid] = $v;
                }
            }
        }
    }
    /**
     * return the value of an attribute document
     * @deprecated use {@link Doc::getRawValue} instead
     * @param string $idAttr attribute identifier
     * @param string $def default value returned if attribute not found or if is empty
     * @see Doc::getRawValue
     * @return string the attribute value
     */
    final public function getValue($idAttr, $def = "")
    {
        static $first = true;
        if ($first) {
            deprecatedFunction();
            $first = false;
        }
        return $this->getRawValue($idAttr, $def);
    }
    /**
     * return all values of a multiple value attribute
     *
     * the attribute must be in an array or declared with multiple option
     * @deprecated use {@link Doc::getMultipleRawValues} instead
     * @param string $idAttr identifier of list attribute
     * @param string $def default value returned if attribute not found or if is empty
     * @param string $index the values for $index row (default value -1 means all values)
     * @see Doc::getMultipleRawValues
     * @return array the list of attribute values
     */
    final public function getTValue($idAttr, $def = "", $index = - 1)
    {
        static $first = true;
        if ($first) {
            deprecatedFunction();
            $first = false;
        }
        return $this->getMultipleRawValues($idAttr, $def, $index);
    }
    /**
     * return all values of a multiple value attribute
     *
     * @api return all values of a multiple value attribute
     * the attribute must be in an array or declared with multiple option
     * @param string $idAttr identifier of list attribute
     * @param string $def default value returned if attribute not found or if is empty
     * @param string $index the values for $index row (default value -1 means all values)
     * @return array the list of attribute values
     */
    final public function getMultipleRawValues($idAttr, $def = "", $index = - 1)
    {
        $v = $this->getRawValue("$idAttr", null);
        if ($v === null) {
            if ($index == - 1) return array();
            else return $def;
        } else if ($v == "\t") {
            if ($index == - 1) return array(
                ""
            );
            else return $def;
        }
        $t = $this->rawValueToArray($v);
        if ($index == - 1) {
            $oa = $this->getAttribute($idAttr);
            if ($oa && $oa->type == "xml") {
                foreach ($t as $k => $v) {
                    $t[$k] = str_replace('<BR>', "\n", $v);
                }
            }
            return $t;
        }
        if (isset($t[$index])) {
            $oa = $this->getAttribute($idAttr);
            if ($oa && $oa->type == "xml") $t[$index] = str_replace('<BR>', "\n", $t[$index]);
            return $t[$index];
        } else return $def;
    }
    /**
     * return the array of values for an array attribute
     *
     * the attribute must  an array type
     * @deprecated use {@link Doc::getArrayRawValues} instead
     * @see Doc::getArrayRawValues
     * @param string $idAttr identifier of array attribute
     * @param string $index the values for $index row (default value -1 means all values)
     * @return array all values of array order by rows (return false if not an array attribute)
     */
    final public function getAValues($idAttr, $index = - 1)
    {
        deprecatedFunction();
        return $this->getArrayRawValues($idAttr, $index);
    }
    /**
     * return the array of values for an array attribute
     *
     * the attribute must  an array type
     * @api get all values for an array attribute
     * @param string $idAttr identifier of array attribute
     * @param string $index the values for $index row (default value -1 means all values)
     * @return array all values of array order by rows (return false if not an array attribute)
     */
    final public function getArrayRawValues($idAttr, $index = - 1)
    {
        $a = $this->getAttribute($idAttr);
        if ($a->type == "array") {
            $ta = $this->attributes->getArrayElements($a->id);
            $ti = $tv = array();
            $ix = 0;
            // transpose
            foreach ($ta as $k => $v) {
                $tv[$k] = $this->getMultipleRawValues($k);
                $ix = max($ix, count($tv[$k]));
            }
            for ($i = 0; $i < $ix; $i++) {
                $ti[$i] = array();
            }
            foreach ($ta as $k => $v) {
                for ($i = 0; $i < $ix; $i++) {
                    $ti[$i]+= array(
                        $k => isset($tv[$k][$i]) ? $tv[$k][$i] : ''
                    );
                }
            }
            if ($index == - 1) return $ti;
            else return $ti[$index];
        }
        return false;
    }
    /**
     * delete a row in an array attribute
     *
     * the attribute must an array type
     * @param string $idAttr identifier of array attribute
     * @api delete a row in an array attribute
     * @param string $index  $index row (first is 0)
     * @return string error message, if no error empty string
     */
    final public function removeArrayRow($idAttr, $index)
    {
        $a = $this->getAttribute($idAttr);
        if ($a->type == "array") {
            $ta = $this->attributes->getArrayElements($a->id);
            $ti = array();
            $err = "";
            // delete in each columns
            foreach ($ta as $k => $v) {
                $tv = $this->getMultipleRawValues($k);
                unset($tv[$index]);
                $tvu = array();
                foreach ($tv as $vv) $tvu[] = $vv; // key reorder
                $err.= $this->setValue($k, $tvu);
            }
            return $err;
        }
        return sprintf(_("%s is not an array attribute") , $idAttr);
    }
    /**
     * in case of array where each column are not the same length
     *
     * the attribute must an array type
     * fill uncomplete column with null values
     * @param string $idAttr identifier of array attribute
     * @param bool $deleteLastEmptyRows by default empty rows which are in the end are deleted
     * @return string error message, if no error empty string
     */
    final public function completeArrayRow($idAttr, $deleteLastEmptyRows = true)
    {
        /* Prevent recursive calls of completeArrayRow() by setValue() */
        static $calls = array();
        if (array_key_exists(strtolower($idAttr) , $calls)) {
            return '';
        } else {
            $calls[strtolower($idAttr) ] = 1;
        }
        
        $err = '';
        $a = $this->getAttribute($idAttr);
        if ($a->type == "array") {
            $ta = $this->attributes->getArrayElements($a->id);
            
            $max = - 1;
            $needRepad = false;
            $tValues = array();
            foreach ($ta as $k => $v) { // delete empty end values
                $tValues[$k] = $this->getMultipleRawValues($k);
                if ($deleteLastEmptyRows) {
                    $c = count($tValues[$k]);
                    for ($i = $c - 1; $i >= 0; $i--) {
                        if ($tValues[$k][$i] === '' || $tValues[$k][$i] === null) {
                            unset($tValues[$k][$i]);
                            $needRepad = true;
                        } else break;
                    }
                }
            }
            foreach ($ta as $k => $v) { // detect uncompleted rows
                $c = count($tValues[$k]);
                if ($max < 0) $max = $c;
                else {
                    if ($c != $max) $needRepad = true;
                    if ($max < $c) $max = $c;
                }
            }
            if ($needRepad) {
                $oldComplete = $this->_setValueDetectChange;
                $this->_setValueDetectChange = false;
                foreach ($ta as $k => $v) { // fill uncompleted rows
                    $c = count($tValues[$k]);
                    if ($c < $max) {
                        $nt = array_pad($tValues[$k], $max, "");
                        $err.= $this->setValue($k, $nt);
                    } else {
                        $err.= $this->setValue($k, $tValues[$k]);
                    }
                }
                $this->_setValueDetectChange = $oldComplete;
            }
            
            unset($calls[strtolower($idAttr) ]);
            return $err;
        }
        
        unset($calls[strtolower($idAttr) ]);
        return sprintf(_("%s is not an array attribute") , $idAttr);
    }
    /**
     * add new row in an array attribute
     *
     * the attribute must be an array type
     * @api add new row in an array attribute
     * @param string $idAttr identifier of array attribute
     * @param array $tv values of each column. Array index must be the attribute identifier
     * @param string $index  $index row (first is 0) -1 at the end; x means before x row
     * @return string error message, if no error empty string
     */
    final public function addArrayRow($idAttr, $tv, $index = - 1)
    {
        if (!is_array($tv)) return sprintf('values "%s" must be an array', $tv);
        $old_setValueCompleteArrayRow = $this->_setValueCompleteArrayRow;
        $this->_setValueCompleteArrayRow = false;
        
        $tv = array_change_key_case($tv, CASE_LOWER);
        $a = $this->getAttribute($idAttr);
        if ((!empty($a)) && $a->type == "array") {
            $err = $this->completeArrayRow($idAttr, false);
            if ($err == "") {
                $ta = $this->attributes->getArrayElements($a->id);
                $attrOut = array_diff(array_keys($tv) , array_keys($ta));
                if ($attrOut) {
                    $this->_setValueCompleteArrayRow = $old_setValueCompleteArrayRow;
                    return sprintf(_('attribute "%s" is not a part of array "%s"') , implode(', ', $attrOut) , $idAttr);
                }
                
                $ti = array();
                $err = "";
                // add in each columns
                foreach ($ta as $k => $v) {
                    $k = strtolower($k);
                    $tnv = $this->getMultipleRawValues($k);
                    $val = isset($tv[$k]) ? $tv[$k] : '';
                    if ($index == 0) {
                        array_unshift($tnv, $val);
                    } elseif ($index > 0 && $index < count($tnv)) {
                        $t1 = array_slice($tnv, 0, $index);
                        $t2 = array_slice($tnv, $index);
                        $tnv = array_merge($t1, array(
                            $val
                        ) , $t2);
                    } else {
                        $tnv[] = $val;
                    }
                    $err.= $this->setValue($k, $tnv);
                }
                if ($err == "") {
                    $err = $this->completeArrayRow($idAttr, false);
                }
            }
            $this->_setValueCompleteArrayRow = $old_setValueCompleteArrayRow;
            return $err;
        }
        $this->_setValueCompleteArrayRow = $old_setValueCompleteArrayRow;
        return sprintf(_("%s is not an array attribute") , $idAttr);
    }
    /**
     * delete all attributes values of an array
     *
     * the attribute must be an array type
     * @api delete all attributes values of an array
     * @param string $idAttr identifier of array attribute
     * @return string error message, if no error empty string
     */
    final public function clearArrayValues($idAttr)
    {
        $old_setValueCompleteArrayRow = $this->_setValueCompleteArrayRow;
        $this->_setValueCompleteArrayRow = false;
        
        $a = $this->getAttribute($idAttr);
        if ($a->type == "array") {
            $ta = $this->attributes->getArrayElements($a->id);
            $err = "";
            // delete each columns
            foreach ($ta as $k => $v) {
                $err.= $this->clearValue($k);
            }
            $this->_setValueCompleteArrayRow = $old_setValueCompleteArrayRow;
            return $err;
        }
        $this->_setValueCompleteArrayRow = $old_setValueCompleteArrayRow;
        return sprintf(_("%s is not an array attribute") , $idAttr);
    }
    /**
     * delete all attributes values of an array
     *
     * the attribute must be an array type
     * @param string $idAttr identifier of array attribute
     * @deprecated use {@link Doc::clearArrayValues} instead
     * @see Doc::clearArrayValues
     * @return string error message, if no error empty string
     */
    final public function deleteArray($idAttr)
    {
        deprecatedFunction();
        return $this->clearArrayValues($idAttr);
    }
    /**
     * affect value for $attrid attribute
     *
     * the affectation is only in object. To set modification in database the store method must be
     * call after modification
     * If value is empty no modification are set. To reset a value use Doc::clearValue method.
     * an array can be use as value for values which are in arrays
     * @api affect value for an attribute
     * @see Doc::setAttributeValue
     * @param string $attrid attribute identifier
     * @param string $value new value for the attribute
     * @param int $index only for array values affect value in a specific row
     * @param int &$kvalue in case of error the index of error (for arrays)
     * @return string error message, if no error empty string
     */
    final public function setValue($attrid, $value, $index = - 1, &$kvalue = null)
    {
        // control edit before set values
        if (!$this->withoutControl) {
            if ($this->id > 0) { // no control yet if no effective doc
                $err = $this->Control("edit");
                if ($err != "") return ($err);
            }
        }
        $attrid = strtolower($attrid);
        /**
         * @var NormalAttribute $oattr
         */
        $oattr = $this->GetAttribute($attrid);
        if ($index > - 1) { // modify one value in a row
            $tval = $this->getMultipleRawValues($attrid);
            if (($index + 1) > count($tval)) {
                $tval = array_pad($tval, $index + 1, "");
            }
            $tval[$index] = $value;
            $value = $tval;
        }
        if (is_array($value)) {
            if ($oattr && $oattr->type == 'htmltext') {
                $value = $this->arrayToRawValue($value, "\r");
                if ($value === '') {
                    $value = DELVALUE;
                }
            } else {
                if (count($value) == 0) $value = DELVALUE;
                elseif ((count($value) == 1) && (first($value) === "" || first($value) === null) && (substr(key($value) , 0, 1) != "s")) $value = "\t"; // special tab for array of one empty cell
                else {
                    if ($oattr && $oattr->repeat && (count($value) == 1) && substr(key($value) , 0, 1) == "s") {
                        $ov = $this->getMultipleRawValues($attrid);
                        $rank = intval(substr(key($value) , 1));
                        if (count($ov) < ($rank - 1)) { // fill array if not set
                            $start = count($ov);
                            for ($i = $start; $i < $rank; $i++) $ov[$i] = "";
                        }
                        foreach ($value as $k => $v) $ov[substr($k, 1, 1) ] = $v;
                        $value = $this->arrayToRawValue($ov);
                    } else {
                        $value = $this->arrayToRawValue($value);
                    }
                }
            }
        }
        if (($value !== "") && ($value !== null)) {
            // change only if different
            if ($oattr === false) {
                if ($this->id > 0) {
                    return sprintf(_("attribute %s unknow in document \"%s\" [%s]") , $attrid, $this->getTitle() , $this->fromname);
                } else {
                    
                    return sprintf(_("attribute %s unknow in family \#%s\"") , $attrid, $this->fromname);
                }
            }
            if ($oattr->mvisibility == "I") return sprintf(_("no permission to modify this attribute %s") , $attrid);
            if ($value === DELVALUE) {
                if ($oattr->type != "password") $value = " ";
                else return '';
            }
            if ($value === " ") {
                $value = ""; // erase value
                if ((!empty($this->$attrid)) || (isset($this->$attrid) && $this->$attrid === "0")) {
                    //print "change by delete $attrid  <BR>\n";
                    if ($this->_setValueDetectChange) {
                        $this->hasChanged = true;
                        $this->_oldvalue[$attrid] = $this->$attrid;
                    }
                    $this->$attrid = "";
                    if ($oattr->type == "file") {
                        // need clear computed column
                        $this->clearFullAttr($oattr->id);
                    }
                }
            } else {
                $value = trim($value, " \x0B\r"); // suppress white spaces end & begin
                if (!isset($this->$attrid)) $this->$attrid = "";
                
                if (strcmp($this->$attrid, $value) != 0 && strcmp($this->$attrid, str_replace("\n ", "\n", $value)) != 0) {
                    // print "change2 $attrid  to <PRE>[{$this->$attrid}] [$value]</PRE><BR>";
                    if ($oattr->repeat) {
                        $tvalues = $this->rawValueToArray($value);
                    } else {
                        $tvalues[] = $value;
                    }
                    
                    foreach ($tvalues as $kvalue => $avalue) {
                        if (($avalue != "") && ($avalue != "\t")) {
                            if ($oattr) {
                                $avalue = trim($avalue);
                                $tvalues[$kvalue] = $avalue;
                                switch ($oattr->type) {
                                    case 'account':
                                    case 'docid':
                                        if (!is_numeric($avalue)) {
                                            if ((!strstr($avalue, "<BR>")) && (!strstr($avalue, "\n"))) {
                                                if ($oattr->getOption("docrev", "latest") == "latest") {
                                                    $tvalues[$kvalue] = getInitidFromName($avalue);
                                                } else {
                                                    $tvalues[$kvalue] = getIdFromName($this->dbaccess, $avalue);
                                                }
                                            } else {
                                                $tnames = explode("\n", $avalue);
                                                
                                                $tids = array();
                                                foreach ($tnames as $lname) {
                                                    $mids = explode("<BR>", $lname);
                                                    $tlids = array();
                                                    foreach ($mids as $llname) {
                                                        if (!is_numeric($llname)) {
                                                            if ($oattr->getOption("docrev", "latest") == "latest") {
                                                                $llid = getInitidFromName($llname);
                                                            } else {
                                                                $llid = getIdFromName($this->dbaccess, $llname);
                                                            }
                                                            $tlids[] = $llid ? $llid : $llname;
                                                        } else {
                                                            $tlids[] = $llname;
                                                        }
                                                    }
                                                    $tids[] = implode('<BR>', $tlids);
                                                }
                                                
                                                $tvalues[$kvalue] = implode("\n", $tids);
                                            }
                                        }
                                        break;

                                    case 'enum':
                                        if ($oattr->getOption("etype") == "open") {
                                            // added new
                                            $tenum = $oattr->getEnum();
                                            $keys = array_keys($tenum);
                                            if (!in_array($avalue, $keys)) {
                                                $oattr->addEnum($this->dbaccess, $avalue, $avalue);
                                            }
                                        }
                                        break;

                                    case 'double':
                                        if ($avalue == '-') {
                                            $avalue = 0;
                                        }
                                        $tvalues[$kvalue] = str_replace(",", ".", $avalue);
                                        $tvalues[$kvalue] = str_replace(" ", "", $tvalues[$kvalue]);
                                        if ($avalue != "\t") {
                                            if (!is_numeric($tvalues[$kvalue])) {
                                                return sprintf(_("value [%s] is not a number") , $tvalues[$kvalue]);
                                            } else {
                                                $tvalues[$kvalue] = (string)((double)$tvalues[$kvalue]); // delete non signifiant zeros
                                                
                                            }
                                        }
                                        
                                        break;

                                    case 'money':
                                        if ($avalue == '-') {
                                            $avalue = 0;
                                        }
                                        $tvalues[$kvalue] = str_replace(",", ".", $avalue);
                                        $tvalues[$kvalue] = str_replace(" ", "", $tvalues[$kvalue]);
                                        if (($avalue != "\t") && (!is_numeric($tvalues[$kvalue]))) {
                                            return sprintf(_("value [%s] is not a number") , $tvalues[$kvalue]);
                                        }
                                        $tvalues[$kvalue] = round(doubleval($tvalues[$kvalue]) , 2);
                                        break;

                                    case 'integer':
                                    case 'int':
                                        if ($avalue == '-') {
                                            $avalue = 0;
                                        }
                                        if (($avalue != "\t") && (!is_numeric($avalue))) {
                                            return sprintf(_("value [%s] is not a number") , $avalue);
                                        }
                                        if (intval($avalue) != floatval($avalue)) {
                                            return sprintf(_("[%s] must be a integer") , $avalue);
                                        }
                                        
                                        $tvalues[$kvalue] = intval($avalue);
                                        break;

                                    case 'time':
                                        if (preg_match('/^(\d\d?):(\d\d?):?(\d\d?)?$/', $avalue, $reg)) {
                                            $hh = intval($reg[1]);
                                            $mm = intval($reg[2]);
                                            $ss = isset($reg[3]) ? intval($reg[3]) : 0; // seconds are optionals
                                            if ($hh < 0 || $hh > 23 || $mm < 0 || $mm > 59 || $ss < 0 || $ss > 59) {
                                                return sprintf(_("value [%s] is out of limit time") , $avalue);
                                            }
                                            if (isset($reg[3])) {
                                                $tvalues[$kvalue] = sprintf("%02d:%02d:%02d", $hh, $mm, $ss);
                                            } else {
                                                $tvalues[$kvalue] = sprintf("%02d:%02d", $hh, $mm);
                                            }
                                        } else {
                                            return sprintf(_("value [%s] is not a valid time") , $avalue);
                                        }
                                        
                                        break;

                                    case 'date':
                                        if (trim($avalue) == "") {
                                            if (!$oattr->repeat) {
                                                $tvalues[$kvalue] = "";
                                            }
                                        } else {
                                            if (!isValidDate($avalue)) {
                                                return sprintf(_("value [%s] is not a valid date") , $avalue);
                                            }
                                            
                                            $localeconfig = getLocaleConfig();
                                            if ($localeconfig !== false) {
                                                $tvalues[$kvalue] = stringDateToIso($avalue, $localeconfig['dateFormat']);
                                                if (getLcdate() != "iso") {
                                                    $tvalues[$kvalue] = preg_replace('#^([0-9]{4})-([0-9]{2})-([0-9]{2})#', '$3/$2/$1', $tvalues[$kvalue]);
                                                }
                                            } else {
                                                return sprintf(_("local config for date not found"));
                                            }
                                        }
                                        break;

                                    case 'timestamp':
                                        if (trim($avalue) == "") {
                                            if (!$oattr->repeat) {
                                                $tvalues[$kvalue] = "";
                                            }
                                        } else {
                                            if (!isValidDate($avalue)) {
                                                return sprintf(_("value [%s] is not a valid timestamp") , $avalue);
                                            }
                                            
                                            $localeconfig = getLocaleConfig();
                                            if ($localeconfig !== false) {
                                                $tvalues[$kvalue] = stringDateToIso($avalue, $localeconfig['dateTimeFormat']);
                                                if (getLcdate() != "iso") {
                                                    $tvalues[$kvalue] = preg_replace('#^([0-9]{4})-([0-9]{2})-([0-9]{2})#', '$3/$2/$1', $tvalues[$kvalue]);
                                                }
                                            } else {
                                                return sprintf(_("local config for timestamp not found"));
                                            }
                                        }
                                        break;

                                    case 'file':
                                        // clear fulltext realtive column
                                        if ((!$oattr->repeat) || ($avalue != $this->getMultipleRawValues($attrid, "", $kvalue))) {
                                            // only if changed
                                            $this->clearFullAttr($oattr->id, ($oattr->repeat) ? $kvalue : -1);
                                        }
                                        $tvalues[$kvalue] = str_replace('\\', '', $tvalues[$kvalue]); // correct possible save error in old versions
                                        break;

                                    case 'image':
                                        $tvalues[$kvalue] = str_replace('\\', '', $tvalues[$kvalue]);
                                        break;

                                    case 'htmltext':
                                        $tvalues[$kvalue] = str_replace('&#39;', "'", $tvalues[$kvalue]);
                                        
                                        $tvalues[$kvalue] = preg_replace("/<!--.*?-->/ms", "", $tvalues[$kvalue]); //delete comments
                                        /* Double encode the entities we want to keep encoded as entities
                                         * after the html_entity_decode() below.
                                        */
                                        $tvalues[$kvalue] = preg_replace('/&(gt|lt|amp|quot|apos);/', '&amp;\1;', $tvalues[$kvalue]);
                                        
                                        $tvalues[$kvalue] = str_replace(array(
                                            '<noscript',
                                            '</noscript>',
                                            '<script',
                                            '</script>'
                                        ) , array(
                                            '<pre',
                                            '</pre>',
                                            '<pre',
                                            '</pre>'
                                        ) , html_entity_decode($tvalues[$kvalue], ENT_NOQUOTES, 'UTF-8'));
                                        $tvalues[$kvalue] = str_replace("[", "&#x5B;", $tvalues[$kvalue]); // need to stop auto instance
                                        $tvalues[$kvalue] = str_replace('--quoteric--', '&amp;quot;', $tvalues[$kvalue]); // reinject original quote entity
                                        $tvalues[$kvalue] = preg_replace("/<\/?meta[^>]*>/s", "", $tvalues[$kvalue]);
                                        if ($oattr->getOption("htmlclean") == "yes") {
                                            $tvalues[$kvalue] = preg_replace("/<\/?span[^>]*>/s", "", $tvalues[$kvalue]);
                                            $tvalues[$kvalue] = preg_replace("/<\/?font[^>]*>/s", "", $tvalues[$kvalue]);
                                            $tvalues[$kvalue] = preg_replace("/<style[^>]*>.*?<\/style>/s", "", $tvalues[$kvalue]);
                                            $tvalues[$kvalue] = preg_replace("/<([^>]*) style=\"[^\"]*\"/s", "<\\1", $tvalues[$kvalue]);
                                            $tvalues[$kvalue] = preg_replace("/<([^>]*) class=\"[^\"]*\"/s", "<\\1", $tvalues[$kvalue]);
                                        }
                                        break;

                                    case 'thesaurus':
                                        // reset cache of doccount
                                        include_once ("FDL/Class.DocCount.php");
                                        $d = new docCount($this->dbaccess);
                                        $d->famid = $this->fromid;
                                        $d->aid = $attrid;
                                        $d->deleteAll();
                                        break;

                                    case 'text':
                                        $tvalues[$kvalue] = str_replace("\r", " ", $tvalues[$kvalue]);
                                        break;
                                }
                            }
                        }
                    }
                    //print "<br/>change $attrid to :".$this->$attrid."->".implode("\n",$tvalues);
                    $rawValue = implode("\n", $tvalues);
                    if ($this->_setValueDetectChange && $this->$attrid != $rawValue) {
                        $this->_oldvalue[$attrid] = $this->$attrid;
                        $this->hasChanged = true;
                    }
                    $this->$attrid = $rawValue;
                }
            }
        }
        if ($this->_setValueCompleteArrayRow && $oattr && $oattr->inArray()) {
            return $this->completeArrayRow($oattr->fieldSet->id);
        }
        return '';
    }
    /**
     * clear $attrid_txt and $attrid_vec
     *
     * @param string $attrid identifier of file attribute
     * @param int $index in case of multiple values
     * @return string error message, if no error empty string
     */
    final private function clearFullAttr($attrid, $index = - 1)
    {
        $attrid = strtolower($attrid);
        $oa = $this->getAttribute($attrid);
        if ($oa && $oa->usefor != 'Q') {
            if ($oa->getOption("search") != "no") {
                $ak = $attrid . '_txt';
                if ($index == - 1) {
                    $this->$ak = '';
                } else {
                    if ($this->AffectColumn(array(
                        $ak
                    ))) {
                        $this->$ak = sep_replace($this->$ak, $index);
                    }
                }
                $this->fields[$ak] = $ak;
                $ak = $attrid . '_vec';
                $this->$ak = '';
                $this->fields[$ak] = $ak;
                $this->fulltext = '';
                $this->fields['fulltext'] = 'fulltext'; // to enable trigger
                $this->textsend[$attrid . $index] = array(
                    "attrid" => $attrid,
                    "index" => $index
                );
            }
        }
    }
    /**
     * send text transformation
     * after ::clearFullAttr is called
     *
     */
    final private function sendTextToEngine()
    {
        $err = '';
        if (!empty($this->textsend)) {
            include_once ("FDL/Lib.Vault.php");
            foreach ($this->textsend as $k => $v) {
                $index = $v["index"];
                if ($index > 0) $fval = $this->getMultipleRawValues($v["attrid"], "", $index);
                else $fval = strtok($this->getRawValue($v["attrid"]) , "\n");
                if (preg_match(PREGEXPFILE, $fval, $reg)) {
                    $vid = $reg[2];
                    $err = sendTextTransformation($this->dbaccess, $this->id, $v["attrid"], $index, $vid);
                    if ($err != "") $this->addHistoryEntry(_("error sending text conversion") . ": $err", DocHisto::NOTICE);
                }
            }
            $this->textsend = array(); //reinit
            
        }
        return $err;
    }
    /**
     * force recompute all file text transformation
     * @param string $aid file attribute identifier. If empty all files attributes will be reseted
     * @return string error message, if no error empty string
     */
    final public function recomputeTextFiles($aid = '')
    {
        if (!$aid) $afiles = $this->GetFileAttributes(true);
        else $afiles[$aid] = $this->getAttribute($aid);
        
        $ttxt = array();
        foreach ($afiles as $k => $v) {
            $kt = $k . '_txt';
            $ttxt[] = $kt;
            if ($v->inArray()) {
                $tv = $this->getMultipleRawValues($k);
                foreach ($tv as $kv => $vv) {
                    $this->clearFullAttr($k, $kv);
                }
            } else {
                $this->clearFullAttr($k);
            }
            $this->$kt = '';
            $kv = $k . '_vec';
            $ttxt[] = $kv;
            $this->$kv = '';
        }
        $this->modify(true, $ttxt, true);
        $err = $this->sendTextToEngine();
        return $err;
    }
    /**
     * affect text value in $attrid file attribute
     *
     * create a new file in Vault to replace old file
     * @param string $attrid identifier of file attribute
     * @param string $value new value for the attribute
     * @param string $ftitle the name of file (if empty the same as before)
     * @return string error message, if no error empty string
     */
    final public function setTextValueInFile($attrid, $value, $ftitle = "")
    {
        $err = '';
        $a = $this->getAttribute($attrid);
        if ($a->type == "file") {
            $err = "file conversion";
            $vf = newFreeVaultFile($this->dbaccess);
            $fvalue = $this->getRawValue($attrid);
            $basename = "";
            if (preg_match(PREGEXPFILE, $fvalue, $reg)) {
                $vaultid = $reg[2];
                $mimetype = $reg[1];
                $info = new stdClass();
                $err = $vf->Retrieve($vaultid, $info);
                
                if ($err == "") {
                    $basename = $info->name;
                }
            }
            $filename = uniqid(getTmpDir() . "/_html") . ".html";
            $nc = file_put_contents($filename, $value);
            /**
             * @var int $vid
             */
            $err = $vf->Store($filename, false, $vid);
            if ($ftitle != "") {
                $vf->Rename($vid, $ftitle);
                $basename = $ftitle;
            } else {
                if ($basename != "") { // keep same file name
                    $vf->Rename($vid, $basename);
                }
            }
            if ($err == "") {
                $mime = trim(shell_exec(sprintf("file -ib %s", escapeshellarg($filename))));
                $value = "$mime|$vid|$basename";
                $err = $this->setValue($attrid, $value);
                //$err="file conversion $mime|$vid";
                if ($err == "xx") {
                    $index = 0;
                    $this->clearFullAttr($attrid); // because internal values not changed
                    
                }
            }
            if ($nc > 0) unlink($filename);
        }
        return $err;
    }
    /**
     * get text value from $attrid file attribute
     *
     * get content of a file (must be an ascii file)
     * @param string $attrid identifier of file attribute
     * @param string &$text the content of the file
     * @return string error message, if no error empty string
     */
    final public function getTextValueFromFile($attrid, &$text)
    {
        $err = '';
        $a = $this->getAttribute($attrid);
        if ($a->type == "file") {
            $vf = newFreeVaultFile($this->dbaccess);
            $fvalue = $this->getRawValue($attrid);
            $basename = "";
            if (preg_match(PREGEXPFILE, $fvalue, $reg)) {
                $vaultid = $reg[2];
                $mimetype = $reg[1];
                $info = new VaultFileInfo();
                /**
                 * VaultFileInfo $info
                 */
                $err = $vf->Retrieve($vaultid, $info);
                
                if (!$err) {
                    $filename = $info->path;
                    $text = file_get_contents($filename);
                }
            }
        }
        return $err;
    }
    /**
     * save stream file in an file attribute
     *
     * replace a new file in Vault to replace old file
     * @param string $attrid identifier of file attribute
     * @param resource $stream file resource from fopen
     * @param string $ftitle to change title of file also (empty to unchange)
     * @param int $index for array of file : modify in specific row
     * @return string error message, if no error empty string
     */
    final public function saveFile($attrid, $stream, $ftitle = "", $index = - 1)
    {
        $err = '';
        if (is_resource($stream) && get_resource_type($stream) == "stream") {
            $mimetype = $ext = $oftitle = $vaultid = '';
            $a = $this->getAttribute($attrid);
            if ($a->type == "file") {
                $err = "file conversion";
                $vf = newFreeVaultFile($this->dbaccess);
                if ($index > - 1) $fvalue = $this->getMultipleRawValues($attrid, '', $index);
                else $fvalue = $this->getRawValue($attrid);
                $basename = "";
                if (preg_match(PREGEXPFILE, $fvalue, $reg)) {
                    $vaultid = $reg[2];
                    $mimetype = $reg[1];
                    $oftitle = $reg[3];
                    $info = new stdClass();
                    $err = $vf->Retrieve($vaultid, $info);
                    
                    if ($err == "") {
                        $basename = $info->name;
                    }
                }
                if ($ftitle) {
                    $ext = getFileExtension($ftitle);
                }
                if ($ext == "") $ext = "nop";
                
                $filename = uniqid(getTmpDir() . "/_fdl") . ".$ext";
                $tmpstream = fopen($filename, "w");
                while (!feof($stream)) {
                    if (false === fwrite($tmpstream, fread($stream, 4096))) {
                        $err = "403 Forbidden";
                        break;
                    }
                }
                fclose($tmpstream);
                // verify if need to create new file in case of revision
                $newfile = ($basename == "");
                
                if ($this->revision > 0) {
                    $trev = $this->GetRevisions("TABLE", 2);
                    $revdoc = $trev[1];
                    $prevfile = getv($revdoc, strtolower($attrid));
                    if ($prevfile == $fvalue) $newfile = true;
                }
                
                if (!$newfile) {
                    $err = $vf->Save($filename, false, $vaultid);
                } else {
                    $err = $vf->Store($filename, false, $vaultid);
                }
                if ($ftitle != "") {
                    $vf->Rename($vaultid, $ftitle);
                } elseif ($basename != "") { // keep same file name
                    $vf->Rename($vaultid, $basename);
                }
                if ($err == "") {
                    if ($mimetype) $mime = $mimetype;
                    else $mime = trim(shell_exec(sprintf("file -ib %s", escapeshellarg($filename))));
                    if ($ftitle) $value = "$mime|$vaultid|$ftitle";
                    else $value = "$mime|$vaultid|$oftitle";
                    $err = $this->setValue($attrid, $value, $index);
                    if ($err == "") {
                        $index = 0;
                        $this->clearFullAttr($attrid); // because internal values not changed
                        
                    }
                    //$err="file conversion $mime|$vid";
                    
                }
                unlink($filename);
                $this->addHistoryEntry(sprintf(_("modify file %s") , $ftitle));
                $this->hasChanged = true;
            }
        }
        return $err;
    }
    /**
     * use for duplicate physicaly the file
     *
     * @param string $idattr identifier of file attribute
     * @param string $newname basename if want change name of file
     * @param int $index in case of array
     * @return string attribut value formated to be inserted into a file attribute
     */
    final function copyFile($idattr, $newname = "", $index = - 1)
    {
        if ($index >= 0) $f = $this->getMultipleRawValues($idattr, "", $index);
        else $f = $this->getRawValue($idattr);
        if ($f) {
            if (preg_match(PREGEXPFILE, $f, $reg)) {
                $vf = newFreeVaultFile($this->dbaccess);
                /**
                 * @var vaultFileInfo $info
                 */
                if ($vf->Show($reg[2], $info) == "") {
                    $cible = $info->path;
                    if (file_exists($cible)) {
                        /**
                         * @var int $vid vault id
                         */
                        $err = $vf->Store($cible, false, $vid);
                        if ($err == "") {
                            if (!$newname) $newname = $info->name;
                            if ($newname) {
                                $vf->Rename($vid, $newname);
                            }
                            return $reg[1] . "|$vid|$newname";
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
     * @param string $idattr identifier of file attribute
     * @param string $newname base name file
     * @param int $index in case of array of files
     * @return string empty if no error
     */
    final function renameFile($idattr, $newname, $index = - 1)
    {
        if ($newname) {
            if ($index == - 1) $f = $this->getRawValue($idattr);
            else $f = $this->getMultipleRawValues($idattr, "", $index);
            if ($f) {
                if (preg_match(PREGEXPFILE, $f, $reg)) {
                    $vf = newFreeVaultFile($this->dbaccess);
                    $vid = $reg[2];
                    /**
                     * @var vaultFileInfo $info
                     */
                    if ($vf->Show($reg[2], $info) == "") {
                        $cible = $info->path;
                        if (file_exists($cible)) {
                            
                            $vf->Rename($vid, $newname);
                            $this->setValue($idattr, $info->mime_s . '|' . $vid . '|' . $newname, $index);
                        }
                    }
                }
            }
        }
        return false;
    }
    /**
     * Register (store) a file in the vault and return the file's vault's informations
     *
     * @param string $filename the file pathname
     * @param string $ftitle override the stored file name or empty string to keep the original file name
     * @param VaultFileInfo $info the vault's informations for the stored file or null if could not get informations
     * @return string trigram of the file in the vault: "mime_s|id_file|name"
     * @throws \Exception on error
     */
    final public function vaultRegisterFile($filename, $ftitle = "", &$info = null)
    {
        include_once ('FDL/Lib.Vault.php');
        
        $vaultid = 0;
        $err = vault_store($filename, $vaultid, $ftitle);
        if ($err != '') {
            throw new \Exception(ErrorCode::getError('FILE0009', $filename, $err));
        }
        $info = vault_properties($vaultid);
        if (!is_object($info) || !is_a($info, 'VaultFileInfo')) {
            throw new \Exception(ErrorCode::getError('FILE0010', $filename));
        }
        
        return sprintf("%s|%s|%s", $info->mime_s, $info->id_file, $info->name);
    }
    /**
     * Store a file in a file attribute
     *
     * @param string $attrid identifier of file attribute
     * @param string $filename file path
     * @param string $ftitle basename of file
     * @param int $index only for array values affect value in a specific row
     * @return string error message, if no error empty string
     */
    final public function setFile($attrid, $filename, $ftitle = "", $index = - 1)
    {
        include_once ("FDL/Lib.Vault.php");
        
        $err = '';
        try {
            $a = $this->getAttribute($attrid);
            if ($a) {
                if (($a->type == "file") || ($a->type == "image")) {
                    $info = null;
                    $vaultid = $this->vaultRegisterFile($filename, $ftitle, $info);
                    $err = $this->setValue($attrid, $vaultid, $index);
                } else {
                    $err = sprintf(_("attribute %s is not a file attribute") , $a->getLabel());
                }
            } else {
                $err = sprintf(_("unknow attribute %s") , $attrid);
            }
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        return $err;
    }
    /**
     * store new file in an file attribute
     *
     * @deprecated use setFile() instead
     *
     * @param string $attrid identifier of file attribute
     * @param string $filename file path
     * @param string $ftitle basename of file
     * @param int $index only for array values affect value in a specific row
     * @return string error message, if no error empty string
     */
    final public function storeFile($attrid, $filename, $ftitle = "", $index = - 1)
    {
        deprecatedFunction();
        
        return $this->setFile($attrid, $filename, $ftitle, $index);
    }
    /**
     * store multiples new files in an file array attribute
     *
     * @deprecated use setFile() instead
     *
     * @param string $attrid identifier of file attribute
     * @param array $filenames file path
     * @param array|string $ftitle basename of file
     * @return string error message, if no error empty string
     */
    final public function storeFiles($attrid, $filenames, $ftitle = "")
    {
        deprecatedFunction();
        $err = '';
        if (!is_array($filenames)) return _("no files");
        
        $a = $this->getAttribute($attrid);
        if (($a->type == "file") || ($a->type == "image")) {
            if ($a->inArray()) {
                $tvid = array();
                foreach ($filenames as $k => $filename) {
                    if (is_file($filename)) {
                        include_once ("FDL/Lib.Vault.php");
                        
                        $err = vault_store($filename, $vaultid, $ftitle[$k]);
                        if ($err == "") {
                            $info = vault_properties($vaultid);
                            $mime = $info->mime_s;
                            if ($ftitle[$k] == "") $ftitle[$k] = $info->name;
                            $tvid[] = "$mime|$vaultid|" . $ftitle[$k];
                        }
                    }
                }
                $this->setValue($attrid, $tvid);
            } else {
                $err = sprintf(_("attribute %s is not int a array") , $a->getLabel());
            }
        } else {
            $err = sprintf(_("attribute %s is not a file attribute") , $a->getLabel());
        }
        
        return $err;
    }
    /**
     * Duplicate physically all files of documents
     *
     */
    function duplicateFiles()
    {
        $err = "";
        $fa = $this->GetFileAttributes();
        foreach ($fa as $aid => $oa) {
            if ($oa->inArray()) {
                $t = $this->getMultipleRawValues($oa->id);
                $tcopy = array();
                foreach ($t as $k => $v) {
                    $tcopy[$k] = $this->copyFile($oa->id, "", $k);
                }
                $this->setValue($oa->id, $tcopy);
            } else {
                $this->setValue($oa->id, $this->copyFile($oa->id));
            }
        }
        return $err;
    }
    /**
     * Return the related value by linked attributes.
     *
     * Can be used to retrieve a value by traversing multiple docid.
     *
     * For example,
     * @code
     * $val = $this->getRValue("id1:id2:id3")
     * @endcode
     * is a shortcut for
     * @code
     * $id1 = $this->getRawValue("id1");
     * $doc1 = new_Doc('', $id1);
     * $id2 = $doc1->getRawValue("id2");
     * $doc2 = new_Doc('', $id2);
     * $val = $doc2->getRawValue('', "id3");
     * @endcode
     *
     * @warning
     * Each of the traversed docid **must** be a docid or an account, and **must not** be multiple.\n
     * Elsewhere, the returned value is $def
     * @endwarning
     *
     * @param string $RidAttr attributes identifier chain (separated by ':')
     * @param string $def $def default return value
     * @param bool $latest always last revision of document
     * @param bool $html return formated value for html
     * @return array|string
     */
    final public function getRValue($RidAttr, $def = "", $latest = true, $html = false)
    {
        $tattrid = explode(":", $RidAttr);
        $lattrid = array_pop($tattrid); // last attribute
        $doc = $this;
        foreach ($tattrid as $k => $v) {
            $docid = $doc->getRawValue($v);
            if ($docid == "") return $def;
            $doc = new_Doc($this->dbaccess, $docid);
            
            if ($latest) {
                if ($doc->locked == - 1) { // it is revised document
                    $ldocid = $doc->getLatestId();
                    if ($ldocid != $doc->id) $doc = new_Doc($this->dbaccess, $ldocid);
                }
            }
            if (!$doc->isAlive()) return $def;
        }
        if ($html) return $doc->getHtmlAttrValue($lattrid, $def);
        else return $doc->getRawValue($lattrid, $def);
    }
    /**
     * return the previous value for a attibute set before Doc::SetValue
     * can be used in Doc::postStore generaly
     * @deprecated use Doc::getOldRawvalue
     * @see Doc::getOldRawValue
     * @param string $attrid attribute identifier
     * @return string the old value (false if not modified before)
     */
    final public function getOldValue($attrid)
    {
        deprecatedFunction();
        return $this->getOldRawValue($attrid);
    }
    /**
     * return the previous value for a attibute set before Doc::SetValue
     * can be used in Doc::postModify generaly
     * @api get previous value of an attribute
     * @param string $attrid attribute identifier
     * @return string the old value (false if not modified before)
     *
     */
    final public function getOldRawValue($attrid)
    {
        $attrid = strtolower($attrid);
        if (isset($this->_oldvalue[$attrid])) return $this->_oldvalue[$attrid];
        return false;
    }
    /**
     * return all modified values from last modify
     * @deprecated use Doc::getOldRawValues instead
     * @see Doc::getOldRawValues
     * @return array indexed by attribute identifier (lowercase)
     */
    final public function getOldValues()
    {
        deprecatedFunction();
        return $this->getOldRawValues();
    }
    /**
     * return all modified values from last modify
     * @api get all modified values from last modify
     * @return array indexed by attribute identifier (lowercase)
     */
    final public function getOldRawValues()
    {
        if (isset($this->_oldvalue)) return $this->_oldvalue;
        return array();
    }
    /**
     * delete a value of an attribute
     * @see Doc::setValue
     * @param string $attrid attribute identifier
     * @api clear value of an attribute
     * @return string error message
     */
    final public function clearValue($attrid)
    {
        $oattr = $this->GetAttribute($attrid);
        if ($oattr->type == 'docid') {
            $doctitle = $oattr->getOption('doctitle');
            if ($doctitle == 'auto') {
                $doctitle = $attrid . '_title';
            }
            if (!empty($doctitle)) {
                $this->SetValue($doctitle, " ");
            }
        }
        return $this->SetValue($attrid, " ");
    }
    /**
     * delete a value of an attribute
     * @see Doc::setValue
     * @param string $attrid attribute identifier
     * @deprecated use {@link Doc::clearValue} instead
     * @see Doc::clearValue
     * @return string error message
     */
    final public function deleteValue($attrid)
    {
        deprecatedFunction();
        return $this->clearValue($attrid);
    }
    /**
     * add values present in values field
     */
    private function getMoreValues()
    {
        if (isset($this->values)) {
            $tvalues = explode("£", $this->values);
            $tattrids = explode("£", $this->attrids);
            
            foreach ($tvalues as $k => $v) {
                $attrid = $tattrids[$k];
                if (($attrid != "") && empty($this->$attrid)) {
                    $this->$attrid = $v;
                    $this->mvalues[$attrid] = $v; // to be use in getValues()
                    
                }
            }
        }
    }
    /**
     * reset values present in values field
     */
    private function resetMoreValues()
    {
        if (isset($this->values) && $this->id) {
            $tattrids = explode("£", $this->attrids);
            
            foreach ($tattrids as $k => $v) {
                $attrid = $tattrids[$k];
                if ($attrid) $this->$attrid = "";
            }
        }
        $this->mvalues = array();
    }
    
    final public function getValueMethod($value, $attrid = '')
    {
        
        $value = $this->ApplyMethod($value, $value);
        
        return $value;
    }
    /**
     * apply a method to a doc
     * specified like ::getFoo(10)
     * @param string $method the method to apply
     * @param string $def default value if no method
     * @param int $index index in case of value in row
     * @param array $bargs first arguments sent before for the method
     * @param array $mapArgs indexed array to add more possibilities to map arguments
     * @param string $err error message
     *
     * @return string the value
     */
    final public function applyMethod($method, $def = "", $index = - 1, array $bargs = array() , array $mapArgs = array() , &$err = '')
    {
        $value = $def;
        $err = '';
        
        if (is_string($method) && preg_match('/([^:]*)::([^\(]+)\(([^\)]*)\)/', $method, $reg)) {
            
            $parseMethod = new parseFamilyMethod();
            $parseMethod->parse($method);
            $err = $parseMethod->getError();
            if ($err) return $err;
            
            $staticClass = $parseMethod->className;
            if (!$staticClass) $staticClass = $this;
            $methodName = $parseMethod->methodName;
            if (method_exists($staticClass, $methodName)) {
                if ((count($parseMethod->inputs) == 0) && (empty($bargs))) {
                    // without argument
                    $value = call_user_func(array(
                        $staticClass,
                        $methodName
                    ));
                } else {
                    // with argument
                    $args = array();
                    
                    $inputs = array();
                    foreach ($bargs as $extraArg) {
                        $inputs[] = new inputArgument($extraArg);
                    }
                    $inputs = array_merge($inputs, $parseMethod->inputs);
                    foreach ($inputs as $ki => $input) {
                        $args[$ki] = null;
                        if ($input->type == "string") {
                            $args[$ki] = $input->name;
                        } else {
                            $mapped = (isset($mapArgs[strtolower($input->name) ])) ? $mapArgs[strtolower($input->name) ] : null;
                            if ($mapped) {
                                if (is_object($mapped)) $args[$ki] = & $mapArgs[strtolower($input->name) ];
                                else $args[$ki] = $mapped;
                            } elseif ($attr = $this->getAttribute($input->name)) {
                                if ($attr->usefor == 'Q') {
                                    if ($attr->inArray()) {
                                        $pas = $this->rawValueToArray($this->getFamilyParameterValue($input->name));
                                        if ($index == - 1) $args[$ki] = $pas;
                                        else $args[$ki] = isset($pas[$index]) ? $pas[$index] : null;
                                    } else {
                                        $args[$ki] = $this->getFamilyParameterValue($input->name);
                                    }
                                } else {
                                    if ($attr->inArray()) $args[$ki] = $this->getMultipleRawValues($input->name, "", $index);
                                    else $args[$ki] = $this->getRawValue($input->name);
                                }
                            } else {
                                if ($input->name == 'THIS') {
                                    $args[$ki] = & $this;
                                } elseif ($input->name == 'K') {
                                    $args[$ki] = $index;
                                } else {
                                    
                                    $args[$ki] = $input->name; // not an attribute just text
                                    
                                }
                            }
                        }
                    }
                    $value = call_user_func_array(array(
                        $staticClass,
                        $methodName,
                    ) , $args);
                }
            } else {
                $err = sprintf(_("Method [%s] not exists") , $method);
                addWarningMsg($err);
                error_log(print_r(getDebugStack() , true));
                return null;
            }
        }
        return $value;
    }
    /**
     * verify attribute constraint
     *
     * @param string $attrid attribute identifier
     * @param int $index index in case of multiple values
     * @return array array of 2 items ("err" + "sug").
     * The err is the string error message (empty means no error)
     * The sug is an array of possibles corrections
     */
    final public function verifyConstraint($attrid, $index = - 1)
    {
        $ok = array(
            "err" => "",
            "sug" => array()
        );
        /**
         * @var NormalAttribute $oattr
         */
        $oattr = $this->getAttribute($attrid);
        if (strlen(trim($oattr->phpconstraint)) > 1) {
            $ko = array(
                "err" => sprintf(_("method %s not found") , $oattr->phpconstraint) ,
                "sug" => array()
            );
            $res = $this->applyMethod($oattr->phpconstraint, $ko, $index);
            
            if ($res !== true) {
                if (!is_array($res)) {
                    if ($res === false) $res = array(
                        "err" => _("constraint error") ,
                        "sug" => array()
                    );
                    elseif (is_string($res)) $res = array(
                        "err" => $res,
                        "sug" => array()
                    );
                } elseif (!empty($res["sug"]) && (!is_array($res["sug"]))) {
                    $res["sug"] = array(
                        $res["sug"]
                    );
                }
                if (is_array($res) && $res["err"] != "") $this->constraintbroken = "[$attrid] " . $res["err"];
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
    final public function verifyAllConstraints($stoptofirst = true, &$info = array())
    {
        $err = "";
        
        $listattr = $this->GetNormalAttributes();
        foreach ($listattr as $v) {
            if (strlen($v->phpconstraint) > 1) {
                if ($v->inArray()) {
                    $tv = $this->getMultipleRawValues($v->id);
                    for ($i = 0; $i < count($tv); $i++) {
                        $res = $this->verifyConstraint($v->id, $i);
                        if ($res["err"] != "") {
                            $info[$v->id . $i] = array(
                                "id" => $v->id,
                                "label" => $v->getLabel() ,
                                "sug" => $res["sug"],
                                "err" => $res["err"],
                                "index" => $i,
                                "pid" => $v->fieldSet->id
                            );
                            if ($stoptofirst) return sprintf("[%s] %s", $v->getLabel() , $res["err"]);
                            $err = $res["err"];
                        }
                    }
                } else {
                    $res = $this->verifyConstraint($v->id);
                    if ($res["err"] != "") {
                        $info[$v->id] = array(
                            "id" => $v->id,
                            "label" => $v->getLabel() ,
                            "pid" => $v->fieldSet->id,
                            "sug" => $res["sug"],
                            "err" => $res["err"]
                        );
                        if ($stoptofirst) return sprintf("[%s] %s", $v->getLabel() , $res["err"]);
                        $err = $res["err"];
                    }
                }
            }
        }
        return $err;
    }
    /**
     * return the first attribute of type 'file' false if no file
     * @return NormalAttribute|bool
     */
    final public function getFirstFileAttributes()
    {
        $t = $this->GetFileAttributes();
        if (count($t) > 0) return current($t);
        return false;
    }
    /**
     * Add a comment line in history document
     * note : modify is call automatically
     * @api Add a comment message in history document
     * @param string $comment the comment to add
     * @param int $level level of comment DocHisto::INFO, DocHisto::ERROR, DocHisto::NOTICE DocHisto::MESSAGE, DocHisto::WARNING
     * @param string $code use when memorize notification
     * @param string $uid user identifier : by default its the current user
     * @return string error message
     */
    final public function addHistoryEntry($comment = '', $level = DocHisto::INFO, $code = '', $uid = '')
    {
        global $action;
        if ($this->id == "") return '';
        
        $h = new DocHisto($this->dbaccess);
        
        $h->id = $this->id;
        $h->initid = $this->initid;
        if (!isUTF8($comment)) $comment = utf8_encode($comment);
        $h->comment = $comment;
        $h->date = date("d-m-Y H:i:s");
        if ($uid > 0) {
            $u = new Account("", $uid);
            $h->uid = $u->id;
            $h->uname = sprintf("%s %s", $u->firstname, $u->lastname);
        } else {
            $h->uname = sprintf("%s %s", $action->user->firstname, $action->user->lastname);
            $h->uid = $action->user->id;
        }
        $h->level = $level;
        $h->code = $code;
        
        $err = $h->Add();
        if ($level == HISTO_ERROR) {
            error_log(sprintf("document %s [%d] : %s", $this->title, $this->id, $comment));
        }
        return $err;
    }
    /**
     * Add a comment line in history document
     * note : modify is call automatically
     * @param string $comment the comment to add
     * @param int $level level of comment DocHisto::INFO, DocHisto::ERROR, DocHisto::NOTICE DocHisto::MESSAGE, DocHisto::WARNING
     * @param string $code use when memorize notification
     * @param string $uid user identifier : by default its the current user
     * @deprecated use {@link Doc::addHistoryEntry} instead
     * @see Doc::addHistoryEntry
     * @return string error message
     */
    final public function addComment($comment = '', $level = DocHisto::INFO, $code = '', $uid = '')
    {
        deprecatedFunction();
        return $this->addHistoryEntry($comment = '', $level, $code, $uid);
    }
    /**
     * Add a log entry line in log document
     *
     * @param string $comment the comment to add
     * @param string $level level of comment
     * @param string $code use when memorize notification
     * @param string $arg serialized object
     * @param string $uid user identifier : by default its the current user
     * @return string error message
     */
    final public function addLog($code = '', $arg = '', $comment = '', $level = '', $uid = '')
    {
        global $action;
        if (($this->id == "") || ($this->doctype == 'T')) return '';
        
        include_once ("FDL/Class.DocLog.php");
        $h = new DocLog($this->dbaccess);
        $h->id = $this->id;
        $h->initid = $this->initid;
        $h->title = $this->title;
        if (!isUTF8($comment)) $comment = utf8_encode($comment);
        $h->comment = $comment;
        
        if ($uid > 0) {
            $u = new Account("", $uid);
            $h->uid = $u->id;
            $h->uname = sprintf("%s %s", $u->firstname, $u->lastname);
        } else {
            $h->uname = sprintf("%s %s", $action->user->firstname, $action->user->lastname);
            $h->uid = $action->user->id;
        }
        $h->level = $level ? $level : DocLog::LOG_NOTIFY;
        $h->code = $code;
        if ($arg) $h->arg = serialize($arg);
        
        $err = $h->Add();
        return $err;
    }
    /**
     * Get history for the document
     * @param bool $allrev set true if want for all revision
     *
     * @param string $code code filter
     * @param int $limit limit of items returned
     * @return array of different comment
     */
    public function getHisto($allrev = false, $code = "", $limit = 0)
    {
        include_once ("Class.QueryDb.php");
        $q = new QueryDb($this->dbaccess, "dochisto");
        if ($allrev) $q->AddQuery("initid=" . $this->initid);
        else $q->AddQuery("id=" . $this->id);
        if ($code) $q->addQuery(sprintf("code='%s'", pg_escape_string($code)));
        $q->order_by = "date desc";
        $l = $q->Query(0, $limit, "TABLE");
        
        if (is_array($l)) return $l;
        return array();
    }
    /**
     * Add a application tag for the document
     * if it is already set no set twice
     * A application tag must not contains "\n" character
     * @param string $tag the tag to add
     * @return string error message
     */
    final public function addATag($tag)
    {
        $err = "";
        if (strpos($tag, "\n") !== false) return ErrorCode::getError('DOC0121', $tag, $this->id);
        if (!$tag) return ErrorCode::getError('DOC0122', $this->id);
        if ($this->atags == "") {
            $this->atags = $tag;
            $err = $this->modify(true, array(
                "atags"
            ) , true);
        } else {
            if (!$this->getATag($tag)) {
                $this->atags.= "\n$tag";
                $err = $this->modify(true, array(
                    "atags"
                ) , true);
            }
        }
        return $err;
    }
    /**
     * Return true if application tag is present
     *
     * @param string $tag the tag to search
     * @return bool
     */
    final public function getATag($tag)
    {
        if ($this->atags == "") return false;
        return (preg_match(sprintf('/^(%s)$/m', preg_quote($tag, '/')) , $this->atags) > 0);
    }
    /**
     * Delete a application tag for the document
     *
     * @param string $tag the tag to delete
     * @return string error message
     */
    final public function delATag($tag)
    {
        $err = "";
        if ($this->atags == "") return "";
        $atags = preg_replace(sprintf('/^%s$/m', preg_quote($tag, '/')) , '', $this->atags);
        $atags = str_replace("\n\n", "\n", $atags);
        $atags = preg_replace("/\n$/m", '', $atags);
        $atags = preg_replace("/^\n/", '', $atags);
        if ($atags != $this->atags) {
            $this->atags = $atags;
            $err = $this->modify(true, array(
                "atags"
            ) , true);
        }
        return $err;
    }
    /**
     * Add a user tag for the document
     * if it is already set no set twice
     * @param int $uid the system user identifier
     * @param string $tag the key tag
     * @param string $datas a comment or a value for the tag
     * @param bool $allrevision set to false if attach a tag to a specific version
     * @return string error message
     */
    final public function addUTag($uid, $tag, $datas = "", $allrevision = true)
    {
        if (!$this->initid) return "";
        if ($tag == "") return _("no user tag specified");
        $this->delUTag($uid, $tag, $allrevision);
        
        global $action;
        $h = new DocUTag($this->dbaccess);
        
        $h->id = $this->id;
        $h->initid = $this->initid;
        $h->fixed = ($allrevision) ? 'false' : 'true';
        $h->date = date("d-m-Y H:i:s");
        if ($uid > 0) {
            $u = new Account("", $uid);
            $h->uid = $u->id;
            $h->uname = sprintf("%s %s", $u->firstname, $u->lastname);
        }
        $h->fromuid = $action->user->id;
        
        $h->tag = $tag;
        $h->comment = $datas;
        
        $err = $h->Add();
        return $err;
    }
    /**
     * Test if current user has the user tag specified
     *
     * @param string $tag the tag to verify
     * @param bool $allrevision set to false to verify a tag to a specific version
     * @return bool
     */
    final public function hasUTag($tag, $allrevision = true)
    {
        if (!$this->initid) return false;
        include_once ("FDL/Class.DocUTag.php");
        $docid = ($allrevision) ? $this->initid : $this->id;
        $utag = new DocUTag($this->dbaccess, array(
            $docid,
            $this->userid,
            $tag
        ));
        return $utag->isAffected();
    }
    /**
     * Get current user tag specified
     *
     * @param string $tag the tag to verify
     * @param bool $allrevision set to false to get a tag to a specific version
     * @return DocUTag|bool
     */
    final public function getUTag($tag, $allrevision = true, $uid = null)
    {
        if (!$this->initid) return "";
        if ($uid === null) {
            $uid = $this->userid;
        }
        
        include_once ("FDL/Class.DocUTag.php");
        $q = new QueryDb($this->dbaccess, "docUTag");
        $q->addQuery("uid=" . intval($uid));
        if ($tag) $q->addQuery("tag = '" . pg_escape_string($tag) . "'");
        if ($allrevision) $q->addQuery("initid = " . $this->initid);
        else $q->addQuery("id = " . $this->id);
        $q->order_by = "id desc";
        $r = $q->Query(0, 1);
        if ($q->nb == 1) return $r[0];
        return false;
    }
    /**
     * Remove a user tag for the document
     * if it is already set no set twice
     * @param int $uid the system user identifier
     * @param string $tag the tag to add
     * @param bool $allrevision set to false to del a tag to a specific version
     * @return string error message
     */
    final public function delUTag($uid, $tag, $allrevision = true)
    {
        if ($tag == "") return _("no user tag specified");
        include_once ("FDL/Class.DocUTag.php");
        $err = "";
        
        $docid = ($allrevision) ? $this->initid : $this->id;
        if ($allrevision) {
            $err = $this->exec_query(sprintf("delete from docutag where initid=%d and tag='%s' and uid=%d", $this->initid, pg_escape_string($tag) , $uid));
        } else {
            $err = $this->exec_query(sprintf("delete from docutag where id=%d and tag='%s' and uid=%d", $this->id, pg_escape_string($tag) , $uid));
        }
        return $err;
    }
    /**
     * Remove all user tag for the document
     *
     * @param int $uid the system user identifier
     * @return string error message
     */
    final public function delUTags($uid = 0)
    {
        if (!$this->initid) return "";
        if (!$uid) $uid = $this->userid;
        $err = $this->exec_query(sprintf("delete from docutag where initid=%d and uid=%d", $this->initid, $uid));
        
        return $err;
    }
    /**
     * Refresh all user tag for the document in case of revision
     * @return string error message
     */
    final public function refreshUTags()
    {
        $err = '';
        if (!$this->initid) return "";
        include_once ("FDL/Class.DocUTag.php");
        $q = new QueryDb($this->dbaccess, "docUTag");
        $q->Query(0, 0, "TABLE", sprintf("update docutag set id=%d where initid=%d and (not fixed)", $this->id, $this->initid));
        
        return $err;
    }
    /**
     * search all user tag for the document
     * @param string $tag tag to search
     * @param boolean $allrevision view tags for all revision
     * @param boolean $allusers view tags of all users
     * @return array user tags key=>value
     */
    final public function searchUTags($tag = "", $allrevision = true, $allusers = false)
    {
        if (!$this->initid) return "";
        include_once ("FDL/Class.DocUTag.php");
        $q = new QueryDb($this->dbaccess, "docUTag");
        if (!$allusers) $q->addQuery("uid=" . intval($this->userid));
        if ($tag) $q->addQuery("tag = '" . pg_escape_string($tag) . "'");
        if ($allrevision) $q->addQuery("initid = " . $this->initid);
        else $q->addQuery("id = " . $this->id);
        $r = $q->Query(0, 0, "TABLE");
        if ($q->nb == 0) $r = array();
        return $r;
    }
    /**
     * get ask for current users
     * @param bool $control if false all associated askes else only askes available for current user
     * @return array
     */
    public function getWasks($control = true)
    {
        $t = array();
        if ($this->wid > 0 && $this->locked == - 1 && $this->doctype != 'Z' && $this->state) {
            /**
             * @var WDoc $wdoc
             */
            $wdoc = new_doc($this->dbaccess, $this->wid);
            if ($wdoc->isAlive()) {
                $wdoc->set($this);
                $waskids = $wdoc->getDocumentWasks($this->state, $control);
                foreach ($waskids as $k => $waskid) {
                    /**
                     * @var \Dcp\Family\WASK $wask
                     */
                    $wask = new_doc($this->dbaccess, $waskid);
                    if ($wask->isAlive()) {
                        $ut = $this->getUTag("ASK_" . $wask->id, false);
                        if ($ut) $answer = $ut->comment;
                        else $answer = "";
                        $t[] = array(
                            "waskid" => $wask->id,
                            "ask" => $wask->getRawValue("was_ask") ,
                            "key" => $answer,
                            "label" => $wask->getAskLabel($answer)
                        );
                    }
                }
            }
        }
        return $t;
    }
    /**
     * set a answer for a document for a ask (for current user)
     * @param int $waskid the identifier of wask
     * @return string error message
     */
    function setWaskAnswer($waskid, $answer)
    {
        $err = _("setWaskAnswer::invalid parameters");
        $waskid = intval($waskid);
        if ($waskid && $answer) {
            if (is_array($answer)) $answer = $this->arrayToRawValue($answer);
            $err = $this->addUTag($this->userid, "ASK_" . intval($waskid) , $answer, false);
            return $err;
        }
        return $err;
    }
    /**
     * all ask are answer ?
     * @return bool true if all ask are answer or when has no askes
     */
    function askIsCompleted()
    {
        $ans = $this->getWasks();
        foreach ($ans as $an) {
            if (!$an["key"]) return false;
        }
        return true;
    }
    /**
     * return the latest document id in history of a document which has ask
     * @return int the identifier
     */
    function getLatestIdWithAsk()
    {
        if (!$this->wid) return false;
        $ldoc = $this->GetRevisions("TABLE");
        /**
         * @var WDoc $wdoc
         */
        $wdoc = new_doc($this->dbaccess, $this->wid);
        if ($wdoc->isAlive()) {
            $wdoc->set($this);
            foreach ($ldoc as $k => $v) {
                $aask = $wdoc->attrPrefix . "_ASKID" . ($v["state"]);
                if ($v["locked"] == - 1 && $wdoc->getRawValue($aask)) {
                    if ($wdoc->getRawValue($aask)) return $v["id"];
                }
            }
        }
        return false;
    }
    /**
     * verify if document is really fixed (verify in database)
     * @return bool
     */
    function isFixed()
    {
        return isFixedDoc($this->dbaccess, $this->id);
    }
    /**
     * Create a new revision of a document
     * the current document is revised (became a fixed document)
     * a new revision is created, a new identifier if set
     * @api Create a new revision of a document
     * @param string $comment the comment of the revision
     * @return string error text (empty if no error)
     */
    final public function revise($comment = '')
    {
        // first control
        if ($this->locked == - 1) return _("document already revised");
        if ($this->isFixed()) {
            $err = _("document already revised");
            $this->addHistoryEntry($err, DocHisto::ERROR, "REVERROR");
            return $err;
        }
        $err = $this->preRevise();
        if ($err) return $err;
        if (!$this->withoutControl) {
            $err = $this->Control("edit");
            if ($err != "") return ($err);
        }
        $fdoc = $this->getFamilyDocument();
        
        if ($fdoc->schar == "S") return sprintf(_("the document of %s family cannot be revised") , $fdoc->title);
        $locked = $this->locked;
        $allocated = $this->allocated;
        $postitid = $this->postitid; // transfert post-it to latest revision
        $this->locked = - 1; // the file is archived
        $this->lmodify = 'N'; // not locally modified
        $this->allocated = 0; // cannot allocated fixed document
        $this->owner = $this->userid; // rev user
        $this->postitid = 0;
        $date = gettimeofday();
        $this->revdate = $date['sec']; // change rev date
        $point = "revision" . $this->id;
        $this->savePoint($point);
        if ($comment != '') $this->addHistoryEntry($comment, DocHisto::MESSAGE, "REVISION");
        $err = $this->modify();
        if ($err != "") {
            $this->rollbackPoint($point);
            //$this->exec_query("rollback;");
            $this->select($this->id); // reset db values
            return $err;
        }
        // double control
        if (!$this->isFixed()) {
            $err = sprintf("track error revision [%s]", pg_last_error($this->dbid));
            $this->addHistoryEntry($err, DocHisto::ERROR, "REVERROR");
            $this->commitPoint($point);
            return $err;
        }
        
        $fa = $this->GetFileAttributes(true); // copy cached values
        $ca = array();
        foreach ($fa as $k => $v) {
            $ca[] = $v->id . "_txt";
        }
        $this->AffectColumn($ca);
        foreach ($ca as $a) {
            if ($this->$a != "") $this->fields[$a] = $a;
        }
        //$listvalue = $this->GetValues(); // save copy of values
        // duplicate values
        $olddocid = $this->id;
        $this->id = "";
        
        if ($locked > 0) $this->locked = $locked; // report the lock
        else $this->locked = 0;
        $this->allocated = $allocated; // report the allocate
        $this->revision = $this->revision + 1;
        $this->postitid = $postitid;
        
        $err = $this->Add();
        if ($err != "") {
            // restore last revision
            // $this->exec_query("rollback;");
            $this->rollbackPoint($point);
            
            $this->select($olddocid); // reset db values
            return $err;
        }
        
        $this->commitPoint($point);
        
        $this->refresh(); // to recompute possible dynamic profil variable
        if ($this->dprofid > 0) $this->setProfil($this->dprofid); // recompute profil if needed
        $err = $this->modify(); // need to applicate SQL triggers
        $this->UpdateVaultIndex();
        $this->refreshUTags();
        if ($err == "") {
            $this->addLog("revision", array(
                "id" => $this->id,
                "initid" => $this->initid,
                "revision" => $this->revision,
                "title" => $this->title,
                "fromid" => $this->fromid,
                "fromname" => $this->fromname
            ));
            // max revision
            $fdoc = $this->getFamilyDocument();
            $maxrev = intval($fdoc->maxrev);
            if ($maxrev > 0) {
                if ($this->revision > $maxrev) {
                    // need delete first revision
                    $revs = $this->getRevisions("TABLE", "ALL");
                    for ($i = $maxrev; $i < count($revs); $i++) {
                        $d = getDocObject($this->dbaccess, $revs[$i]);
                        if ($d) $d->_destroy(true);
                    }
                }
            }
            $msg = $this->postRevise();
            if ($msg) $this->addHistoryEntry($msg, DocHisto::MESSAGE, "POSTREVISE");
        }
        
        return $err;
    }
    /**
     * Create a new revision of a document
     * the current document is revised (became a fixed document)
     * a new revision is created, a new identifier if set
     * @deprecated use {@link Doc::revise} instead
     * @see Doc::revise
     * @param string $comment the comment of the revision
     * @return string error text (empty if no error)
     */
    final public function addRevision($comment = '')
    {
        deprecatedFunction();
        return $this->revise($comment);
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
    final public function changeFreeState($newstateid, $comment = '', $revision = true)
    {
        if ($this->wid > 0) return sprintf(_("cannot set free state in workflow controlled document %s") , $this->title);
        if ($this->wid == - 1) return sprintf(_("cannot set free state for document %s: workflow not allowed") , $this->title);
        if (!$this->isRevisable()) return sprintf(_("cannot set free state for document %s: document cannot be revised") , $this->title);
        if ($newstateid == 0) {
            $this->state = "";
            $err = $this->modify(false, array(
                "state"
            ));
            if ($err == "") {
                $comment = sprintf(_("remove state : %s") , $comment);
                if ($revision) $err = $this->revise($comment);
                else $err = $this->addHistoryEntry($comment);
            }
        } else {
            
            $state = new_doc($this->dbaccess, $newstateid);
            if (!$state->isAlive()) return sprintf(_("invalid freestate document %s") , $newstateid);
            if ($state->fromid != 39) return sprintf(_("not a freestate document %s") , $state->title);
            
            $this->state = $state->id;
            $err = $this->modify(false, array(
                "state"
            ));
            if ($err == "") {
                $comment = sprintf(_("change state to %s : %s") , $state->title, $comment);
                if ($revision) $err = $this->revise($comment);
                else $err = $this->addHistoryEntry($comment);
            }
        }
        return $err;
    }
    /**
     * set state for a document controled by a workflow
     * apply associated transaction
     *
     * @api set state for a document controled by a workflow
     * @param string $newstate the new state
     * @param string $comment optional comment to set in history
     * @param bool $force is true when it is the second passage (without interactivity)
     * @param bool $withcontrol set to false if you want to not verify control permission ot transition
     * @param bool $wm1 set to false if you want to not apply m1 methods
     * @param bool $wm2 set to false if you want to not apply m2 methods
     * @param bool $wneed set to false to not test required attributes
     * @param bool $wm0 set to false if you want to not apply m0 methods
     * @param bool $wm3 set to false if you want to not apply m3 methods
     * @param string $msg return message from m2 or m3
     * @internal param bool $need set to false if you want to not verify needed attribute are set
     * @return string error message empty if no error
     */
    final public function setState($newstate, $comment = '', $force = false, $withcontrol = true, $wm1 = true, $wm2 = true, $wneed = true, $wm0 = true, $wm3 = true, &$msg = '')
    {
        if ($newstate == "") return _("no state specified");
        if (!$this->wid) return _("document is not controlled by a workflow");
        /**
         * @var WDoc $wdoc
         */
        $wdoc = new_doc($this->dbaccess, $this->wid);
        if (!$wdoc->isAlive()) return _("assigned workflow is not alive");
        try {
            $wdoc->Set($this);
            $err = $wdoc->ChangeState($newstate, $comment, $force, $withcontrol, $wm1, $wm2, $wneed, $wm0, $wm3, $msg);
        }
        catch(Exception $e) {
            $err = sprintf(_("workflow associated %s [%d] is corrupted") , $wdoc->title, $wdoc->id);
        }
        return $err;
    }
    /**
     * return the state of a document
     * if document has workflow it is the key
     * if document state is a free state it is the name of the state
     *
     * @api get the state of a document
     * @return string the state - empty if no state
     */
    public function getState()
    {
        if ($this->wid > 0) return $this->state;
        if (is_numeric($this->state) && ($this->state > 0)) {
            $state = $this->getTitle($this->state);
            return $state;
        }
        
        return $this->state;
    }
    /**
     * return the color associated for the state of a document
     * if document has workflow : the color state
     * if document state is a free state the color
     *
     * @param string $def default color if state not found or color is empty
     * @return string the color of the state - empty if no state
     */
    public function getStateColor($def = "")
    {
        if ($this->wid > 0) {
            /**
             * @var WDoc $wdoc
             */
            $wdoc = new_Doc($this->dbaccess, $this->wid);
            if ($wdoc->isAffected()) return $wdoc->getColor($this->state, $def);
        } else {
            if (is_numeric($this->state) && ($this->state > 0)) {
                $state = $this->getDocValue($this->state, "frst_color", $def);
                return $state;
            }
        }
        return $def;
    }
    /**
     * return the action associated for the state of a document
     * if document has workflow : the action label description
     * if document state is a free state : state description
     *
     * @param string $def default activity is activity is empty
     * @return string the color of the state - empty if no state
     */
    final public function getStateActivity($def = "")
    {
        if ($this->wid > 0) {
            /**
             * @var WDoc $wdoc
             */
            $wdoc = new_Doc($this->dbaccess, $this->wid);
            if ($wdoc->isAffected()) return $wdoc->getActivity($this->state, $def);
        } else {
            if (is_numeric($this->state) && ($this->state > 0)) {
                $stateact = $this->getDocValue($this->state, "frst_desc", $def);
                return $stateact;
            }
        }
        return $def;
    }
    /**
     * return state label if ficed document else activity label
     * if not activity return state
     * @return string localized state label
     */
    final public function getStatelabel()
    {
        if ($this->locked == - 1) $stateValue = $this->getState();
        else $stateValue = $this->getStateActivity($this->getState());
        return (empty($stateValue) ? '' : _($stateValue));
    }
    /**
     * return the copy (duplication) of the document
     * the copy is created to the database
     * the profil of the copy is the default profil according to his family
     * the copy is not locked and if it is related to a workflow, his state is the first state
     * @deprecated use {@link Doc::duplicate} instead
     * @see Doc::duplicate
     * @param bool $temporary if true the document create it as temporary document
     * @param bool $control if false don't control acl create (generaly use when temporary is true)
     * @param bool $linkfld if true and document is a folder then document included in folder are also inserted in the copy (are not duplicated) just linked
     * @param bool $copyfile if true duplicate files of the document
     * @return Doc in case of error return a string that indicate the error
     */
    final public function copy($temporary = false, $control = true, $linkfld = false, $copyfile = false)
    {
        deprecatedFunction();
        return $this->duplicate($temporary, $control, $linkfld, $copyfile);
    }
    /**
     * return the copy (duplication) of the document
     * the copy is created to the database
     * the profil of the copy is the default profil according to his family
     * the copy is not locked and if it is related to a workflow, his state is the first state
     * @api duplicate document
     * @param bool $temporary if true the document create it as temporary document
     * @param bool $control if false don't control acl create (generaly use when temporary is true)
     * @param bool $linkfld if true and document is a folder then document included in folder are also inserted in the copy (are not duplicated) just linked
     * @param bool $copyfile if true duplicate files of the document
     * @return Doc|string in case of error return a string that indicate the error
     */
    final public function duplicate($temporary = false, $control = true, $linkfld = false, $copyfile = false)
    {
        
        $copy = createDoc($this->dbaccess, $this->fromid, $control);
        if (!is_object($copy)) return false;
        /**
         * @var Doc $copy
         */
        $err = $copy->transfertValuesFrom($this);
        if ($err != "") return $err;
        
        $copy->id = "";
        $copy->initid = "";
        $copy->revision = "0";
        $copy->locked = "0";
        $copy->allocated = "0";
        $copy->state = "";
        $copy->icon = $this->icon;;
        
        if ($temporary) {
            $copy->doctype = "T";
            $copy->profid = 0;
            $copy->dprofid = 0;
        } else {
            $cdoc = $this->getFamilyDocument();
            $copy->setProfil($cdoc->cprofid);
        }
        
        $err = $copy->preDuplicate($this);
        if ($err != "") return $err;
        
        $err = $copy->Add();
        if ($err != "") return $err;
        $copy->addHistoryEntry(sprintf(_("copy from document #%d -%s-") , $this->id, $this->title));
        
        if ($copyfile) $copy->duplicateFiles();
        
        $msg = $copy->postDuplicate($this);
        if ($msg != "") {
            $copy->addHistoryEntry($msg, HISTO_MESSAGE);
        }
        
        $copy->Modify();
        if ($linkfld && method_exists($copy, "insertFolder")) {
            /**
             * @var Dir $copy
             */
            $copy->insertFolder($this->initid);
        }
        
        return $copy;
    }
    /**
     * call before copy document
     * if return error message duplicate is aborted
     * @api hook called before duplicate document
     * @see Doc::duplicate
     * @param Doc $copyfrom
     * @return string
     */
    function preDuplicate(&$copyfrom)
    {
        // to be defined in child class
        return "";
    }
    /**
     * call before copy document
     * if return error message duplicate is aborted
     * @deprecated hook use {@link Doc::preDuplicate} instead
     * @see Doc::preDuplicate
     * @param Doc $copyfrom
     * @return string
     */
    function preCopy(&$copyfrom)
    {
        // to be defined in child class
        return "";
    }
    /**
     * call after copy document
     * @api hook called after duplicate document
     * @see Doc::duplicate
     * @param Doc $copyfrom
     * @return string
     */
    function postDuplicate(&$copyfrom)
    {
        // to be defined in child class
        return "";
    }
    /**
     * call after copy document
     * @api hook called after duplicate document
     * @deprecated use {@link Doc::postDuplicate} hook instead
     * @see Doc::postDuplicate
     * @param Doc $copyfrom
     * @return string
     */
    function postCopy(&$copyfrom)
    {
        // to be defined in child class
        return "";
    }
    
    final public function translate($docid, $translate)
    {
        $doc = new_Doc($this->dbaccess, $docid);
        if ($doc->isAlive()) {
            foreach ($translate as $afrom => $ato) {
                $this->setValue($ato, $doc->getRawValue($afrom));
            }
        }
    }
    /**
     * Put document in an archive
     * @param \Dcp\Family\ARCHIVING $archive the archive document
     * @return string error message
     */
    final public function archive(&$archive)
    {
        $err = "";
        if ($this->archiveid == 0) {
            if ($this->doctype == "C") $err = sprintf("families cannot be archieved");
            elseif (!$this->withoutControl) $err = $this->control("edit");
            if ($err == "") {
                $this->locked = 0;
                $this->archiveid = $archive->id;
                $this->dprofid = ($this->dprofid > 0) ? (-$this->dprofid) : (-abs($this->profid));
                $archprof = $archive->getRawValue("arc_profil");
                $this->profid = $archprof;
                $err = $this->modify(true, array(
                    "locked",
                    "archiveid",
                    "dprofid",
                    "profid"
                ) , true);
                if (!$err) {
                    $this->addHistoryEntry(sprintf(_("Archiving into %s") , $archive->getTitle()) , HISTO_MESSAGE, "ARCHIVE");
                    $this->addLog('archive', $archive->id, sprintf(_("Archiving into %s") , $archive->getTitle()));
                    $err = $this->exec_query(sprintf("update doc%d set archiveid=%d, dprofid=-abs(profid), profid=%d where initid=%d and locked = -1", $this->fromid, $archive->id, $archprof, $this->initid));
                }
            }
        } else $err = sprintf("document is already archived");
        
        return $err;
    }
    /**
     * Delete document in an archive
     * @param \Dcp\Family\ARCHIVING $archive the archive document
     *
     * @return string error message
     */
    final public function unArchive(&$archive)
    {
        $err = "";
        
        if ($this->archiveid == $archive->id) {
            if (!$this->withoutControl) $err = $this->control("edit");
            if ($err == "") {
                $this->locked = 0;
                $this->archiveid = ""; // set to null
                $restoreprofil = abs($this->dprofid);
                $this->dprofid = 0;
                $err = $this->setProfil($restoreprofil);
                if (!$err) $err = $this->modify(true, array(
                    "locked",
                    "archiveid",
                    "dprofid",
                    "profid"
                ) , true);
                if (!$err) {
                    $this->addHistoryEntry(sprintf(_("Unarchiving from %s") , $archive->getTitle()) , HISTO_MESSAGE, "UNARCHIVE");
                    $this->addLog('unarchive', $archive->id, sprintf(_("Unarchiving from %s") , $archive->getTitle()));
                    $err = $this->exec_query(sprintf("update doc%d set archiveid=null, profid=abs(dprofid), dprofid=null where initid=%d and locked = -1", $this->fromid, $this->initid));
                }
            }
        } else $err = sprintf("document not archived");
        
        return $err;
    }
    /**
     * lock document
     *
     * the auto lock is unlocked when the user discard edition or when he's modify document
     * @param bool $auto if true it is a automatic lock due to an edition (@see Doc::editcard()}
     * @param int $userid if set lock with another userid, the edit control will be disabled
     *
     * @return string error message, if no error empty string, if message
     * @see Doc::CanLockFile()
     * @see Doc::unlock()
     */
    final public function lock($auto = false, $userid = 0)
    {
        
        $err = "";
        if ($userid == 0) {
            $err = $this->CanLockFile();
            if ($err != "") return $err;
            $userid = $this->userid;
        } else {
            $this->disableEditControl();
        }
        // test if is not already locked
        if ($auto) {
            if (($userid != 1) && ($this->locked == 0)) {
                $this->locked = - $userid; // in case of auto lock the locked id is negative
                $err = $this->modify(false, array(
                    "locked"
                ));
                if (!$err) $this->addLog('lock');
            }
        } else {
            if (($this->locked != $userid) || ($this->lockdomainid)) {
                $this->locked = $userid;
                $err = $this->modify(false, array(
                    "locked"
                ));
                if (!$err) $this->addLog('lock');
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
    final public function unLock($auto = false, $force = false)
    {
        $err = '';
        if ($this->locked == 0) return "";
        if (!$force) $err = $this->CanUnLockFile();
        if ($err != "") return $err;
        
        if ($auto) {
            if ($this->locked < - 1) {
                $this->locked = "0";
                $this->modify(false, array(
                    "locked"
                ));
                if (!$err) $this->addLog('unlock');
            }
        } else {
            if ($this->locked != - 1) {
                $this->locked = "0";
                $this->lockdomainid = '';
                $this->modify(false, array(
                    "locked",
                    "lockdomainid"
                ));
                if (!$err) $this->addLog('unlock');
            }
        }
        
        return "";
    }
    /**
     * allocate document
     *
     * affect a document to a user
     * @param int $userid the system identifier of the user to affect
     * @param string $comment message for allocation
     * @param bool $revision if false no revision are made
     * @param bool $autolock if false no lock are made
     *
     * @return string error message, if no error empty string, if message
     */
    final public function allocate($userid, $comment = "", $revision = false, $autolock = true)
    {
        
        $err = $this->canEdit();
        if ($err != "") $err = _("Affectation aborded") . "\n" . $err;
        if ($err == "") {
            $u = new Account("", $userid);
            if ($u->isAffected()) {
                if ($err != "") $err = _("Affectation aborded") . "\n" . $err;
                // no test if allocated can edit document
                //$err=$this->ControlUser($u->id,"edit");
                //if ($err != "") $err=sprintf(_("Affectation aborded\n%s for user %s %s"),$err,$u->firstname,$u->lastname);
                if ($err == "") {
                    $this->addHistoryEntry(sprintf(_("Affected to %s %s") , $u->firstname, $u->lastname));
                    if ($comment) {
                        if ($revision) {
                            $this->revise(sprintf(_("Affected for %s") , $comment));
                        } else {
                            $this->addHistoryEntry(sprintf(_("Affected for %s") , $comment));
                        }
                    }
                    $this->addLog('allocate', array(
                        "allocated" => array(
                            "id" => $u->id,
                            "firstname" => $u->firstname,
                            "lastname" => $u->lastname
                        )
                    ));
                    
                    $this->delUTag($this->userid, "AFFECTED"); // TODO need delete all AFFECTED tag
                    $this->addUTag($userid, "AFFECTED", $comment);
                    if ($autolock) $err = $this->lock(false, $userid);
                }
            } else {
                $err = _("Affectation aborded : user not know");
            }
        }
        if ($err == "") {
            $this->allocated = $userid;
            $this->modify(true, array(
                "allocated"
            ) , true);
        }
        
        return $err;
    }
    /**
     * unallocate document
     *
     * unaffect a document to a user
     * only the allocated user can unallocate and also users which has unlock acl
     * @param string $comment message for unallocation
     * @param bool $revision if false no revision are made
     *
     * @return string error message, if no error empty string, if message
     */
    final public function unallocate($comment = "", $revision = true)
    {
        if ($this->allocated == 0) return "";
        $err = "";
        $err = $this->canEdit();
        if ($err == "") {
            if ((!$this->withoutControl) && ($this->userid != $this->allocated)) $err = $this->control("unlock");
        }
        
        if ($err == "") {
            $u = new Account("", $this->allocated);
            if ($u->isAffected()) {
                $err = $this->unlock();
                if ($err == "") {
                    $this->delUTag($this->userid, "AFFECTED"); // TODO need delete all AFFECTED tag
                    if ($revision) $this->revise(sprintf(_("Unallocated of %s %s : %s") , $u->firstname, $u->lastname, $comment));
                    else $this->addHistoryEntry(sprintf(_("Unallocated of %s %s: %s") , $u->firstname, $u->lastname, $comment));
                }
            } else {
                $err = _("user not know");
            }
        }
        if ($err == "") {
            $this->allocated = 0;
            $this->modify(true, array(
                "allocated"
            ) , true);
            $this->addLog('unallocate');
        }
        
        if ($err != "") $err = _("Unallocate aborded") . "\n" . $err;
        return $err;
    }
    /**
     * return icon url
     * if no icon found return doc.png
     * @param string $idicon
     * @param int $size width size
     * @return string icon url
     */
    final public function getIcon($idicon = "", $size = null)
    {
        /**
         * @var Action $action
         */
        global $action;
        if ($idicon == "") $idicon = $this->icon;
        if ($idicon != "") {
            
            if (preg_match(PREGEXPFILE, $idicon, $reg)) {
                if ($size) {
                    $efile = "resizeimg.php?vid=" . $reg[2] . "&size=" . $size;
                } else {
                    $efile = "FDL/geticon.php?vaultid=" . $reg[2] . "&mimetype=" . $reg[1];
                }
            } else {
                $efile = $action->parent->getImageLink($idicon, true, $size);
            }
            return $efile;
        } else {
            return $action->parent->getImageLink("doc.png", true, $size);
        }
    }
    /**
     * change icon for a class or a simple doc
     * @param string $icon basename icon file
     */
    final public function changeIcon($icon)
    {
        
        if ($this->doctype == "C") { //  a class
            $fromid = $this->initid;
            if ($this->icon != "") {
                // need disabled triggers to increase speed
                $qt[] = "ALTER TABLE doc$fromid DISABLE TRIGGER ALL";
                $qt[] = "update doc$fromid set icon='$icon' where (fromid=" . $fromid . ") AND (doctype != 'C') and ((icon='" . $this->icon . "') or (icon is null))";
                $qt[] = "ALTER TABLE doc$fromid ENABLE TRIGGER ALL";
                $qt[] = "update docread set icon='$icon' where (fromid=" . $fromid . ") AND (doctype != 'C') and ((icon='" . $this->icon . "') or (icon is null))";
                
                $this->exec_query(implode(";", $qt));
            } else {
                $q = "update doc$fromid set icon='$icon' where (fromid=" . $fromid . ") AND (doctype != 'C') and (icon is null)";
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
    final public function addParamRefresh($in, $out)
    {
        // to know which attribut must be disabled in edit mode
        $tin = explode(",", strtolower($in));
        $tout = explode(",", strtolower($out));
        $this->paramRefresh["$in:$out"] = array(
            "in" => $tin,
            "out" => $tout
        );
    }
    /**
     * compute new visibility with depended attributes
     * @return array of visibilities computed with dependance between attributes
     */
    public function getRefreshVisibility()
    {
        $tv = array();
        foreach ($this->attributes->attr as $k => $v) {
            $tv[$v->id] = $v->mvisibility;
        }
        foreach ($this->paramRefresh as $k => $v) {
            reset($v["in"]);
            $val = true;
            while ($val && (list($ka, $va) = each($v["in"]))) {
                $val = $this->getRawValue($va);
            }
            if ($val) {
                foreach ($v["out"] as $oa) {
                    if (($tv[$oa] == "W") || ($tv[$oa] == "O")) $tv[$oa] = "S";
                }
            }
        }
        
        return $tv;
    }
    /**
     * Special Refresh
     * called when refresh document : when view, modify document - generally when access to the document
     * @note during preRefresh edit control is disabled
     * @see Doc::refresh
     * @api hook called in begining of refresh before update computed attributes
     */
    public function preRefresh()
    {
        return '';
    }
    /**
     * Special Refresh
     * called when refresh document : when view, modify document - generally when access to the document
     * @note during specRefresh edit control is disabled
     * @deprecated This hook may be replaced by preRefresh in the the next version.
     * @see Doc::refresh
     */
    public function specRefresh()
    {
        return '';
    }
    /**
     * post Refresh
     * called when refresh document : when view, modify document - generally when access to the document
     * a modify is done after if attributes are chahged
     * @note during postRefresh edit control is disabled
     * @see Doc::refresh
     * @api hook called at the end of refresh after update computed attributes
     */
    public function postRefresh()
    {
        return '';
    }
    /**
     * Special Refresh Generated automatically
     * is defined in generated child classes
     */
    public function specRefreshGen($onlyspec = false)
    {
        return '';
    }
    /**
     * recompute all computed attribut
     * and save the document in database if changes occurred
     * @api refresh document by calling specRefresh and update computed attributes
     * @return string information message
     */
    final public function refresh()
    {
        if ($this->locked == - 1) return ''; // no refresh revised document
        if (($this->doctype == 'C') || ($this->doctype == 'Z')) return ''; // no refresh for family  and zombie document
        if ($this->lockdomainid > 0) return '';
        $changed = $this->hasChanged;
        if (!$changed) $this->disableEditControl(); // disabled control just to refresh
        $msg = $this->preRefresh();
        // if ($this->id == 0) return; // no refresh for no created document
        $msg.= $this->SpecRefreshGen();
        $msg.= $this->postRefresh();
        if ($this->hasChanged && $this->id > 0) {
            $this->lastRefreshError = $this->modify(); // refresh title
            
        }
        if (!$changed) $this->enableEditControl();
        return $msg;
    }
    /**
     * Recompute file name in concordance with rn option
     *
     */
    public function refreshRn()
    {
        $err = "";
        $fa = $this->GetFileAttributes();
        foreach ($fa as $aid => $oa) {
            $rn = $oa->getOption("rn");
            if ($rn) {
                if ($oa->inArray()) {
                    $t = $this->getMultipleRawValues($oa->id);
                    foreach ($t as $k => $v) {
                        $cfname = $this->vault_filename($oa->id, false, $k);
                        if ($cfname) {
                            $fname = $this->applyMethod($rn, "", $k, array(
                                $cfname
                            ));
                            
                            if ($fname != $cfname) {
                                $err.= $this->renameFile($oa->id, $fname, $k);
                            }
                        }
                    }
                } else {
                    $cfname = $this->vault_filename($oa->id);
                    if ($cfname) {
                        $fname = $this->applyMethod($rn, "", -1, array(
                            $cfname
                        ));
                        if ($fname != $cfname) {
                            $err.= $this->renameFile($oa->id, $fname);
                        }
                    }
                }
            }
        }
        return $err;
    }
    /**
     * replace % tag of a link attribute
     * @param string $link url to analyze
     * @param int $k index
     * @return bool|string
     */
    final public function urlWhatEncode($link, $k = - 1)
    {
        /**
         * @var Action $action
         */
        global $action;
        
        $urllink = "";
        $mi = strlen($link);
        for ($i = 0; $i < $mi; $i++) {
            switch ($link[$i]) {
                case '%':
                    $i++;
                    
                    if (isset($link[$i]) && $link[$i] == "%") {
                        $urllink.= "%"; // %% is %
                        
                    } else {
                        $optional = false;
                        if (isset($link[$i]) && $link[$i] == "?") {
                            $i++;
                            $optional = true;
                        }
                        if (isset($link[$i + 1]) && $link[$i + 1] == "%") {
                            // special link
                            switch ($link[$i]) {
                                case "B": // baseurl
                                    $urllink.= $action->GetParam("CORE_BASEURL");
                                    break;

                                case "S": // standurl
                                    $urllink.= $action->GetParam("CORE_STANDURL");
                                    break;

                                case "I": // id
                                    $urllink.= $this->id;
                                    break;

                                case "T": // title
                                    $urllink.= rawurlencode($this->title);
                                    break;

                                default:
                                    
                                    break;
                            }
                            $i++; // skip end '%'
                            
                        } else {
                            
                            $sattrid = "";
                            while (($i < $mi) && ($link[$i] != "%")) {
                                $sattrid.= $link[$i];
                                $i++;
                            }
                            
                            if (preg_match('/^[a-z0-9_]*::/i', $sattrid)) {
                                $urllink.= $this->applyMethod($sattrid, $sattrid, $k);
                            } else {
                                
                                if (!in_array(mb_strtolower($sattrid) , $this->fields)) {
                                    if (preg_match('/[0-9A-F][0-9A-F]/', $sattrid)) {
                                        $urllink.= '%' . $sattrid; // hexa code
                                        if (isset($link[$i]) && $link[$i] == '%') {
                                            $urllink.= '%';
                                        }
                                    } else {
                                        
                                        if (!$optional) {
                                            return false;
                                        }
                                    }
                                } else {
                                    /**
                                     * @var NormalAttribute $oa
                                     */
                                    $oa = $this->GetAttribute($sattrid);
                                    if (($k >= 0) && ($oa && $oa->repeat)) {
                                        $tval = $this->getMultipleRawValues($sattrid);
                                        
                                        $ovalue = isset($tval[$k]) ? chop($tval[$k]) : '';
                                    } else {
                                        // get property also
                                        $ovalue = $this->getRawValue($sattrid);
                                    }
                                    if ($ovalue == "" && (!$optional)) {
                                        return false;
                                    }
                                    
                                    if (strstr($ovalue, "\n")) {
                                        $ovalue = str_replace("\n", '\n', $ovalue);
                                    }
                                    $urllink.= rawurlencode($ovalue); // need encode
                                    
                                }
                            }
                        }
                    }
                    break;

                case '{':
                    $i++;
                    
                    $sattrid = "";
                    while ($link[$i] != '}') {
                        $sattrid.= $link[$i];
                        $i++;
                    }
                    //	  print "attr=$sattrid";
                    $ovalue = $action->getParam($sattrid);
                    $urllink.= rawurlencode($ovalue);
                    
                    break;

                default:
                    $urllink.= $link[$i];
            }
        }
        $urllink = $this->urlWhatEncodeSpec($urllink); // complete in special case families
        return (chop($urllink));
    }
    /**
     * virtual method must be use in child families if needed complete url
     */
    public function urlWhatEncodeSpec($l)
    {
        return $l;
    }
    /**
     * convert flat attribute value to an array for multiple attributes
     *
     * use only for specific purpose. If need typed attributes use Doc::getAttribute()
     * @api convert flat attribute value to an array
     * @see Doc::getAttributeValue
     * @param string $v value
     * @return array
     */
    public static function rawValueToArray($v)
    {
        if ($v === "" || $v === null) return array();
        return explode("\n", str_replace("\r", "", $v));
    }
    /**
     * convert flat attribute value to an array for multiple attributes
     * @deprecated use instead {@Doc::rawValueToArray}
     * @see Doc::rawValueToArray
     * @param string $v value
     * @return array
     */
    public static function _val2array($v)
    {
        deprecatedFunction();
        return self::rawValueToArray($v);
    }
    /**
     * convert array value to flat attribute value
     * @api convert array value to flat attribute value
     * @param array $v
     * @param string $br
     * @return string
     */
    public static function arrayToRawValue($v, $br = '<BR>')
    {
        $v = str_replace("\n", $br, $v);
        if (count($v) == 0) return "";
        return implode("\n", $v);
    }
    /**
     * convert array value to flat attribute value
     * @param array $v
     * @param string $br
     * @deprecated use {@link Doc::arrayToRawValue} instead
     * @see Doc::arrayToRawValue
     * @return string
     */
    public static function _array2val($v, $br = '<BR>')
    {
        deprecatedFunction();
        return self::arrayToRawValue($v, $br);
    }
    /**
     * return an url to access of folder/search RSS in open mode authentication
     * @return string the url anchor
     */
    public function getRssLink()
    {
        /**
         * @var Action $action
         */
        global $action;
        return sprintf("%s?app=FREEDOM&action=FREEDOM_RSS&authtype=open&privateid=%s&id=%s", $action->getParam("CORE_OPENURL", $action->getParam("CORE_EXTERNURL")) , $action->user->getUserToken() , $this->id);
    }
    /**
     * return an url to download for file attribute
     * @param string $attrid attribute identifier
     * @param int $index set to row rank if it is in array else use -1
     * @param bool $cache set to true if file may be persistent in client cache
     * @param bool $inline set to true if file must be displayed in web browser
     * @param string $otherValue use another file value instead of attribute value
     * @return string the url anchor
     */
    public function getFileLink($attrid, $index = - 1, $cache = false, $inline = false, $otherValue = '')
    {
        if ($index === '' || $index === null) {
            $index = - 1;
        }
        if (!$otherValue) {
            if ($index >= 0) $avalue = $this->getMultipleRawValues($attrid, "", $index);
            else $avalue = $this->getRawValue($attrid);
        } else {
            if ($index >= 0) {
                if (is_array($otherValue)) $avalue = $otherValue[$index];
                else $avalue = $otherValue;
            } else $avalue = $otherValue;
        }
        if (preg_match(PREGEXPFILE, $avalue, $reg)) {
            $vid = $reg[2];
            // will be rewrited by apache rules
            //rewrite to  sprintf("%s?app=FDL&action=EXPORTFILE&cache=%s&inline=%s&vid=%s&docid=%s&attrid=%s&index=%d", "", $cache ? "yes" : "no", $inline ? "yes" : "no", $vid, $this->id, $attrid, $index);
            $url = sprintf("file/%s/%d/%s/%s/%s?cache=%s&inline=%s", $this->id, $vid, $attrid, $index, rawurlencode($reg[3]) , $cache ? "yes" : "no", $inline ? "yes" : "no");
            if ($this->cvid > 0) {
                $viewId = getHttpVars("vid");
                if ($viewId) {
                    $url.= '&cvViewid=' . $viewId;
                }
            }
            return $url;
        }
        return '';
    }
    /**
     * return an html anchor to a document
     * @api return an html anchor to a document
     * @param int $id identifier of document
     * @param string $target window target
     * @param bool $htmllink must be true else return nothing
     * @param bool|string $title should we override default title
     * @param bool $js should we add a javascript contextual menu
     * @param string $docrev style of link (default:latest, other values: fixed or state(xxx))
     * @param bool $viewIcon set to true to have icon in html link
     * @return string the html anchor
     */
    final public function getDocAnchor($id, $target = "_self", $htmllink = true, $title = false, $js = true, $docrev = "latest", $viewIcon = false)
    {
        $a = "";
        $latest = ($docrev == "latest" || $docrev == "");
        if ($htmllink) {
            
            if (!$title) $title = $this->getHTMLTitle(strtok($id, '#') , '', $latest);
            else $title = $this->htmlEncode($title);
            if (trim($title) == "") {
                if ($id < 0) {
                    $a = "<a>" . sprintf(_("document not exists yet")) . "</a>";
                } else {
                    $a = "<a>" . sprintf(_("unknown document id %s") , $id) . "</a>";
                }
            } else {
                /* Setup base URL */
                $ul = '?';
                switch ($target) {
                    case "mail":
                        $js = false;
                        $ul = htmlspecialchars(GetParam("CORE_MAILACTIONURL"));
                        $ul.= "&amp;id=$id";
                        break;

                    case "ext":
                        $ul.= "&amp;app=FDL&amp;action=VIEWEXTDOC&amp;id=$id";
                        break;

                    default:
                        $ul.= "&amp;app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=$id";
                }
                /* Add target's specific elements to base URL */
                if ($target == "ext") {
                    //$ec=getSessionValue("ext:targetRelation");
                    $jslatest = ($latest) ? 'true' : 'false';
                    $ec = getHttpVars("ext:targetRelation", 'Ext.fdl.Document.prototype.publish("opendocument",null,%V%,"view",{latest:' . $jslatest . '})');
                    if ($ec) {
                        if (!is_numeric($id)) $id = getIdFromName($this->dbaccess, $id);
                        else if ($latest) {
                            $lid = getLatestDocId($this->dbaccess, $id);
                            if ($lid) $id = $lid;
                        }
                        $ec = str_replace("%V%", $id, $ec);
                        $ecu = str_replace("'", '"', $ec);
                        $ajs = "";
                        if ($viewIcon) {
                            simpleQuery($this->dbaccess, sprintf('select icon from docread where id=%d', $id) , $iconValue, true, true);
                            $ajs.= sprintf('class="relation" style="background-image:url(%s)"', $this->getIcon($iconValue, 14));
                        }
                        $a = "<a $ajs onclick='parent.$ecu'>$title</a>";
                    } else {
                        if ($docrev == "latest" || $docrev == "" || !$docrev) $ul.= "&amp;latest=Y";
                        elseif ($docrev != "fixed") {
                            // validate that docrev looks like state(xxx)
                            if (preg_match("/^state\(([a-zA-Z0-9_:-]+)\)/", $docrev, $matches)) {
                                $ul.= "&amp;state=" . $matches[1];
                            }
                        }
                        $a = "<a href=\"$ul\">$title</a>";
                    }
                } else {
                    if ($docrev == "latest" || $docrev == "" || !$docrev) $ul.= "&amp;latest=Y";
                    elseif ($docrev != "fixed") {
                        // validate that docrev looks like state(xxx)
                        if (preg_match("/^state\(([a-zA-Z0-9_:-]+)\)/", $docrev, $matches)) {
                            $ul.= "&amp;state=" . $matches[1];
                        }
                    }
                    if ($js) $ajs = "oncontextmenu=\"popdoc(event,'$ul');return false;\"";
                    else $ajs = "";
                    
                    $ajs.= sprintf(' documentId="%s" ', $id);
                    if ($viewIcon) {
                        simpleQuery($this->dbaccess, sprintf('select icon from docread where id=%d', $id) , $iconValue, true, true);
                        $ajs.= sprintf('class="relation" style="background-image:url(%s)"', $this->getIcon($iconValue, 14));
                    }
                    $a = "<a $ajs target=\"$target\" href=\"$ul\">$title</a>";
                }
            }
        } else {
            if (!$title) $a = $this->getHTMLTitle($id, '', $latest);
            else $a = $this->htmlEncode($title);
        }
        return $a;
    }
    /**
     * return HTML formated value of an attribute
     * @param NormalAttribute $oattr
     * @param string $value raw value
     * @param string $target html target in case of link
     * @param bool $htmllink
     * @param int $index
     * @param bool $entities
     * @param bool $abstract
     * @return string the formated value
     */
    final public function getHtmlValue($oattr, $value, $target = "_self", $htmllink = true, $index = - 1, $entities = true, $abstract = false)
    {
        static $level = 0;
        static $otherFormatter = array();;
        if (!$this->htmlFormater) {
            $this->htmlFormater = new DocHtmlFormat($this);
        }
        if ($level == 0) {
            $htmlFormater = & $this->htmlFormater;
        } else {
            if (!isset($otherFormatter[$level])) {
                $otherFormatter[$level] = new DocHtmlFormat($this);
            }
            $htmlFormater = $otherFormatter[$level];
        }
        if ($htmlFormater->doc->id != $this->id) {
            $htmlFormater->setDoc($this);
        }
        $level++;
        $r = $htmlFormater->getHtmlValue($oattr, $value, $target, $htmllink, $index, $entities, $abstract);
        $level--;
        return $r;
    }
    /**
     * return an html anchor to a document
     * @see Doc::getHtmlValue
     * @param string $attrid attribute identifier
     * @param string $target html target in case of link
     * @param int $htmllink
     * @param $index
     * @param bool $entities
     * @param bool $abstract
     * @return string
     */
    final public function getHtmlAttrValue($attrid, $target = "_self", $htmllink = 2, $index = - 1, $entities = true, $abstract = false)
    {
        if ($index != - 1) $v = $this->getMultipleRawValues($attrid, "", $index);
        else $v = $this->getRawValue($attrid);
        
        return $this->GetHtmlValue($this->getAttribute($attrid) , $v, $target, $htmllink, $index, $entities, $abstract);
    }
    /**
     * Get a textual representation of the content of an attribute
     *
     * @param string $attrId logical name of the attr
     * @param $index
     * @param array $configuration value config array : dateFormat => 'US' 'ISO', decimalSeparator => '.',
     * multipleSeparator => array(0 => 'arrayLine', 1 => 'multiple') (defaultValue : dateFormat : 'US', decimalSeparator : '.', multiple => array(0 => "\n", 1 => ", "))
     *
     * @return string|bool return false if attribute not found else the textual value
     */
    final public function getTextualAttrValue($attrId, $index = - 1, Array $configuration = array())
    {
        $objectAttr = $this->getAttribute($attrId);
        if ($objectAttr) {
            return $objectAttr->getTextualValue($this, $index, $configuration);
        } else {
            return $objectAttr;
        }
    }
    /**
     * get value for open document text template
     * @param string $attrid attribute identifier
     * @param string $target unused
     * @param bool $htmllink unused
     * @param int $index index rank in case of multiple attribute value
     * @return string XML fragment
     */
    final public function getOooAttrValue($attrid, $target = "_self", $htmllink = false, $index = - 1)
    {
        if ($index != - 1) $v = $this->getMultipleRawValues($attrid, "", $index);
        else $v = $this->getRawValue($attrid);
        if ($v == "") return $v;
        return $this->getOooValue($this->getAttribute($attrid) , $v, '', false, $index);
    }
    /**
     * return open document text format for attribute value
     * @param NormalAttribute $oattr
     * @param string $value
     * @param string $target unused
     * @param bool $htmllink unused
     * @param int $index index rank in case of multiple attribute value
     * @return string XML fragment
     */
    final public function getOooValue($oattr, $value, $target = "_self", $htmllink = false, $index = - 1)
    {
        
        if (!$this->oooFormater) {
            $this->oooFormater = new DocOooFormat($this);
        }
        if ($this->oooFormater->doc->id != $this->id) {
            $this->oooFormater->setDoc($this);
        }
        return $this->oooFormater->getOooValue($oattr, $value, $index);
    }
    /**
     * Control Access privilege for document for current user
     *
     * @param string $aclname identifier of the privilege to test
     * @param bool $strict set tio true to test without notion of account susbstitute
     * @return string empty means access granted else it is an error message (access unavailable)
     */
    public function control($aclname, $strict = false)
    {
        $err = '';
        if (($this->isAffected())) {
            if (($this->profid <= 0) || ($this->userid == 1)) return ""; // no profil or admin
            $err = $this->controlId($this->profid, $aclname, $strict);
            if (($err != "") && ($this->isConfidential())) $err = sprintf(_("no privilege %s for %s") , $aclname, $this->getTitle());
            // Edit rights on profiles must also be controlled by the 'modifyacl' acl
            if (($err == "") && ($aclname == 'edit' || $aclname == 'delete' || $aclname == 'unlock') && $this->isRealProfile()) {
                $err = $this->controlId($this->profid, 'modifyacl', $strict);
            }
        }
        return $err;
    }
    /**
     * Control Access privilege for document for current user
     *
     * @api control document access
     * @param string $aclName identifier of the privilege to test
     * @param bool $strict set tio true to test without notion of account susbstitute
     * @return bool return true if access $aclName is granted, false else
     */
    public function hasPermission($aclName, $strict = false)
    {
        return ($this->control($aclName, $strict) == "");
    }
    /**
     * Control Access privilege for document for other user
     *
     * @param int $uid user identifier
     * @param string $aclname identifier of the privilege to test
     * @return string empty means access granted else it is an error message (access unavailable)
     */
    public function controlUser($uid, $aclname)
    {
        // --------------------------------------------------------------------
        if ($this->IsAffected()) {
            if (($this->profid <= 0) || ($uid == 1)) return ""; // no profil or admin
            if (!$uid) return _("control :: user identifier is null");
            return $this->controlUserId($this->profid, $uid, $aclname);
        }
        return "";
    }
    /**
     * verify that the document exists and is not in trash (not a zombie)
     * @api verify that the document exists and is not in trash
     * @return bool
     */
    final public function isAlive()
    {
        return ((DbObj::isAffected()) && ($this->doctype != 'Z'));
    }
    /**
     * add several triggers to update different tables (such as docread) or attributes (such as values)
     * @param bool $onlydrop set to false for only drop triggers
     * @param bool $code
     * @return string sql commands
     */
    final public function sqlTrigger($onlydrop = false, $code = false)
    {
        
        if (get_class($this) == "DocFam") {
            $cid = "fam";
            $famId = $this->id;
        } else {
            if ($this->doctype == 'C') return '';
            if (intval($this->fromid) == 0) return '';
            
            $cid = $this->fromid;
            $famId = $this->fromid;
        }
        
        $sql = "";
        // delete all relative triggers
        $sql.= "select droptrigger('doc" . $cid . "');";
        if ($onlydrop) return $sql; // only drop
        if ($code) {
            $files = array();
            $lay = new Layout("FDL/Layout/sqltrigger.xml");
            $na = $this->GetNormalAttributes();
            $tvalues = array();
            $tsearch = array();
            $fulltext_c = array();
            foreach ($na as $k => $v) {
                $opt_searchcriteria = $v->getOption("searchcriteria", "");
                if (($v->type != "array") && ($v->type != "frame") && ($v->type != "tab") && ($v->type != "idoc")) {
                    // values += any attribute
                    if ($v->docid == $famId) {
                        $tvalues[] = array(
                            "attrid" => $k
                        );
                    }
                    // svalues += attribute allowed to be indexed
                    if (($v->type != "file") && ($v->type != "image") && ($v->type != "password") && ($opt_searchcriteria != "hidden")) {
                        if ($v->docid == $famId) {
                            $tsearch[] = array(
                                "attrid" => $k
                            );
                        }
                        $fulltext_c[] = array(
                            "attrid" => $k
                        );
                    }
                }
                if ($v->type == "file" && $opt_searchcriteria != "hidden") {
                    // fulltext += file attributes
                    $files[] = array(
                        "attrid" => $k . "_txt",
                        "vecid" => $k . "_vec"
                    );
                    // svalues += file attributes
                    if ($v->docid == $famId) {
                        $tsearch[] = array(
                            "attrid" => $k . "_txt"
                        );
                    }
                }
            }
            // fulltext += abstract attributes
            $tabstract = array();
            $na = $this->GetAbstractAttributes();
            foreach ($na as $k => $v) {
                $opt_searchcriteria = $v->getOption("searchcriteria", "");
                if ($opt_searchcriteria == "hidden") {
                    continue;
                }
                if (($v->type != "array") && ($v->type != "file") && ($v->type != "image") && ($v->type != "password")) {
                    $tabstract[] = array(
                        "attrid" => $k
                    );
                }
            }
            $lay->setBlockData("ATTRFIELD", $tvalues);
            $lay->setBlockData("SEARCHFIELD", $tsearch);
            $lay->setBlockData("ABSATTR", $tabstract);
            $lay->setBlockData("FILEATTR", $files);
            $lay->setBlockData("FILEATTR2", $files);
            $lay->setBlockData("FILEATTR3", $files);
            $lay->setBlockData("FULLTEXT_C", $fulltext_c);
            $lay->set("hasattr", (count($tvalues) > 0));
            $lay->set("hassattr", (count($tsearch) > 0));
            $lay->set("hasabsattr", (count($tabstract) > 0));
            $lay->set("docid", $this->fromid);
            $sql = $lay->gen();
        } else {
            
            if ($this->attributes !== null && isset($this->attributes->fromids) && is_array($this->attributes->fromids)) {
                foreach ($this->attributes->fromids as $k => $v) {
                    
                    $sql.= "create trigger UV{$cid}_$v BEFORE INSERT OR UPDATE ON doc$cid FOR EACH ROW EXECUTE PROCEDURE upval$v();";
                }
            }
            // the reset trigger must begin with 'A' letter to be proceed first (pgsql 7.3.2)
            if ($cid != "fam") {
                $sql.= "create trigger AUVR{$cid} BEFORE UPDATE  ON doc$cid FOR EACH ROW EXECUTE PROCEDURE resetvalues();";
                $sql.= "create trigger VFULL{$cid} BEFORE INSERT OR UPDATE  ON doc$cid FOR EACH ROW EXECUTE PROCEDURE fullvectorize$cid();";
            } else {
                $sql.= "create trigger UVdocfam before insert or update on docfam FOR EACH ROW EXECUTE PROCEDURE upvaldocfam();";
            }
            $sql.= "create trigger zread{$cid} AFTER INSERT OR UPDATE OR DELETE ON doc$cid FOR EACH ROW EXECUTE PROCEDURE setread();";
            $sql.= "create trigger FIXDOC{$cid} AFTER INSERT ON doc$cid FOR EACH ROW EXECUTE PROCEDURE fixeddoc();";
        }
        return $sql;
    }
    /**
     * add specials SQL indexes
     *
     * @return array sqls queries to create indexes
     */
    final public function getSqlIndex()
    {
        $t = array();;
        $id = $this->fromid;
        if (static::$sqlindex) $sqlindex = array_merge(static::$sqlindex, Doc::$sqlindex);
        else $sqlindex = Doc::$sqlindex;
        foreach ($sqlindex as $k => $v) {
            
            if (!empty($v["unique"])) $unique = "unique";
            else $unique = "";
            if (!empty($v["using"])) {
                
                if ($v["using"][0] == "@") {
                    $v["using"] = getParam(substr($v["using"], 1));
                }
                $t[] = sprintf("CREATE $unique INDEX %s$id on  doc$id using %s(%s);\n", $k, $v["using"], $v["on"]);
            } else {
                $t[] = sprintf("CREATE $unique INDEX %s$id on  doc$id(%s);\n", $k, $v["on"]);
            }
        }
        return $t;
    }
    /**
     * return the basename of template file
     * @param string $zone zone to parse
     * @return string|null (return null if template not found)
     */
    public function getZoneFile($zone)
    {
        $index = - 1;
        if ($zone == "") {
            return null;
        }
        
        $reg = $this->parseZone($zone);
        if (is_array($reg)) {
            $aid = $reg['layout'];
            if ($reg['index'] != '') {
                $index = $reg['index'];
            }
            $oa = $this->getAttribute($aid);
            if ($oa) {
                if ($oa->usefor != 'Q') {
                    $template = $this->getRawValue($oa->id);
                } else {
                    $template = $this->getFamilyParameterValue($aid);
                }
                if ($index >= 0) {
                    $tt = $this->rawValueToArray($template);
                    $template = $tt[$index];
                }
                
                if ($template == "") {
                    return null;
                }
                
                return $this->vault_filename_fromvalue($template, true);
            }
            return getLayoutFile($reg['app'], ($aid));
        }
        return null;
    }
    /**
     * return the character in third part of zone
     * @return string single character
     */
    public function getZoneOption($zone = "")
    {
        if ($zone == "") {
            $zone = $this->defaultview;
        }
        
        $zoneElements = $this->parseZone($zone);
        if ($zoneElements === false) {
            return '';
        }
        
        return $zoneElements['modifier'];
    }
    /**
     * return the characters in fourth part of zone
     * @return string
     */
    public function getZoneTransform($zone = "")
    {
        if ($zone == "") {
            $zone = $this->defaultview;
        }
        
        $zoneElements = $this->parseZone($zone);
        if ($zoneElements === false) {
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
    final public function setDefaultValues($tdefval, $method = true, $forcedefault = false)
    {
        if (is_array($tdefval)) {
            foreach ($tdefval as $aid => $dval) {
                /**
                 * @var BasicAttribute $oattr
                 */
                $oattr = $this->getAttribute($aid);
                
                $ok = false;
                if (empty($oattr)) $ok = false;
                elseif ($dval === '' || $dval === null) $ok = false;
                elseif (!is_a($oattr, "BasicAttribute")) $ok = false;
                elseif ($forcedefault) $ok = true;
                elseif (!$oattr->inArray()) $ok = true;
                elseif ($oattr->fieldSet->format != "empty" && $oattr->fieldSet->getOption("empty") != "yes") {
                    if (empty($tdefval[$oattr->fieldSet->id])) $ok = true;
                    else $ok = false;
                }
                if ($ok) {
                    if ($oattr->type == "array") {
                        if ($method) {
                            
                            $values = json_decode($dval, true);
                            if ($values === null) {
                                $values = $this->applyMethod($dval, null);
                                if ($values === null) {
                                    throw new Dcp\Exception("DFLT0007", $aid, $dval, $this->fromname);
                                }
                            }
                            if (!is_array($values)) {
                                throw new Dcp\Exception("DFLT0008", $aid, $dval, $values, $this->fromname);
                            }
                            $terr = '';
                            foreach ($values as $row) {
                                $err = $this->addArrayRow($aid, $row);
                                if ($err) $terr[] = $err;
                            }
                            if ($terr) throw new Dcp\Exception("DFLT0009", $aid, $dval, $values, $this->fromname, implode('; ', $terr));
                        }
                        //print_r($this->getValues());
                        
                    } else {
                        if ($method) {
                            $this->setValue($aid, $this->GetValueMethod($dval));
                        } else {
                            $this->setValue($aid, $dval); // raw data
                            
                        }
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
     * @param boolean $temporary compute a temporary logical name that will be deleted by the cleanContext API
     * @return string error message (empty means OK).
     */
    final public function setNameAuto($temporary = false)
    {
        $err = '';
        if (($this->name == "") && ($this->initid > 0)) {
            $dfam = $this->getFamilyDocument();
            if ($dfam->name == "") return sprintf("no family name %s", $dfam->id);
            if ($temporary) {
                $this->name = sprintf('TEMPORARY_%s_%s_%s', $dfam->name, $this->initid, uniqid());
            } else {
                $this->name = $dfam->name . '_' . $this->initid;
            }
            $err = $this->modify(true, array(
                "name"
            ) , true);
        }
        return $err;
    }
    /**
     * Return the main path relation
     * list of prelid properties (primary relation)
     * the first item is the direct parent, the second:the grand-parent , etc.
     * @return array key=id , value=title of relation
     */
    function getMainPath()
    {
        $tr = array();
        
        if ($this->prelid > 0) {
            
            $d = getTDoc($this->dbaccess, $this->prelid);
            $fini = false;
            while (!$fini) {
                if ($d) {
                    if (controlTDoc($d, "view")) {
                        if (!in_array($d["initid"], array_keys($tr))) {
                            $tr[$d["initid"]] = $d["title"];
                            if ($d["prelid"] > 0) $d = getTDoc($this->dbaccess, $d["prelid"]);
                            else $fini = true;
                        } else $fini = true;
                    } else $fini = true;
                } else {
                    $fini = true;
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
     * @throws Exception
     * @return string genererated template . If target is binary, return file path of temporary generated file
     */
    final public function viewDoc($layout = "FDL:VIEWBODYCARD", $target = "_self", $ulink = true, $abstract = false, $changelayout = false)
    {
        global $action;
        
        $reg = $this->parseZone($layout);
        if ($reg === false) {
            return htmlspecialchars(sprintf(_("error in pzone format %s") , $layout) , ENT_QUOTES);
        }
        
        if (array_key_exists('args', $reg)) {
            // in case of arguments in zone
            global $ZONE_ARGS;
            $layout = $reg['fulllayout'];
            if (array_key_exists('argv', $reg)) {
                foreach ($reg['argv'] as $k => $v) {
                    $ZONE_ARGS[$k] = $v;
                }
            }
        }
        $play = null;
        if (!$changelayout) {
            $play = $this->lay;
        }
        $binary = ($this->getZoneOption($layout) == "B");
        
        $tplfile = $this->getZoneFile($layout);
        
        $ext = getFileExtension($tplfile);
        if (strtolower($ext) == "odt") {
            include_once ('Class.OOoLayout.php');
            $target = "ooo";
            $ulink = false;
            $this->lay = new OOoLayout($tplfile, $action, $this);
        } else {
            $this->lay = new Layout($tplfile, $action, "");
        }
        //if (! file_exists($this->lay->file)) return sprintf(_("template file (layout [%s]) not found"), $layout);
        $this->lay->set("_readonly", ($this->Control('edit') != ""));
        $method = strtok(strtolower($reg['layout']) , '.');
        if (method_exists($this, $method)) {
            try {
                $refMeth = new ReflectionMethod(get_class($this) , $method);
                if (preg_match('/@templateController\b/', $refMeth->getDocComment())) {
                    $this->$method($target, $ulink, $abstract);
                } else {
                    global $action;
                    $syserr = ErrorCode::getError("DOC1101", $refMeth->getDeclaringClass()->getName() , $refMeth->getName() , $this);
                    $action->log->error($syserr);
                    $err = sprintf(_("Layout \"%s\" : Controller not allowed") , $layout);
                    return $err;
                }
            }
            catch(Exception $e) {
                if ((!file_exists($this->lay->file) && (!$this->lay->template))) {
                    return sprintf(_("template file (layout [%s]) not found") , $layout);
                } else throw $e;
            }
        } else {
            $this->viewdefaultcard($target, $ulink, $abstract);
        }
        
        if ((!file_exists($this->lay->file) && (!$this->lay->template))) {
            return sprintf(_("template file (layout [%s]) not found") , $layout);
        }
        
        $laygen = $this->lay->gen();
        
        if (!$changelayout) $this->lay = $play;
        
        if (!$ulink) {
            // suppress href attributes
            return preg_replace(array(
                "/href=\"index\.php[^\"]*\"/i",
                "/onclick=\"[^\"]*\"/i",
                "/ondblclick=\"[^\"]*\"/i"
            ) , array(
                "",
                "",
                ""
            ) , $laygen);
        }
        if ($target == "mail") {
            // suppress session id
            return preg_replace("/\?session=[^&]*&/", "?", $laygen);
        }
        if ($binary && ($target != "ooo")) {
            // set result into file
            $tmpfile = uniqid(getTmpDir() . "/fdllay") . ".html";
            $nc = file_put_contents($tmpfile, $laygen);
            $laygen = $tmpfile;
        }
        
        return $laygen;
    }
    // --------------------------------------------------------------------
    
    /**
     * default construct layout for view card containt
     *
     * @templateController default controller view
     * @param string $target window target name for hyperlink destination
     * @param bool $ulink if false hyperlink are not generated
     * @param bool $abstract if true only abstract attribute are generated
     * @param bool $viewhidden if true view also hidden attributes
     */
    final public function viewdefaultcard($target = "_self", $ulink = true, $abstract = false, $viewhidden = false)
    {
        $this->viewattr($target, $ulink, $abstract, $viewhidden);
        $this->viewprop($target, $ulink, $abstract);
    }
    // --------------------------------------------------------------------
    
    /**
     * construct layout for view card containt
     *
     * @templateController default HTML view controller
     * @param string $target window target name for hyperlink destination
     * @param bool $ulink if false hyperlink are not generated
     * @param bool $abstract if true only abstract attribute are generated
     * @param bool $onlyopt if true only optional attributes are displayed
     */
    function viewbodycard($target = "_self", $ulink = true, $abstract = false, $onlyopt = false)
    {
        /**
         * @var Action $action
         */
        global $action;
        
        $frames = array();
        if ($abstract) {
            // only 3 properties for abstract mode
            $listattr = $this->GetAbstractAttributes();
        } else {
            $listattr = $this->GetNormalAttributes($onlyopt);
        }
        
        $nattr = count($listattr); // attributes list count
        $k = 0; // number of frametext
        $v = 0; // number of value in one frametext
        $nbimg = 0; // number of image in one frametext
        $currentFrameId = "";
        /**
         * @var FieldSetAttribute
         */
        $currentFrame = null;
        $changeframe = false; // is true when need change frame
        $tableframe = array();
        $tableimage = array();
        $ttabs = array();
        $frametpl = '';
        
        $iattr = 0;
        $onlytab = strtolower(getHttpVars("onlytab"));
        $tabonfly = false; // I want tab on fly
        $showonlytab = ($onlytab ? $onlytab : false);
        if ($onlytab) {
            $this->addUTag($this->userid, "lasttab", $onlytab);
        }
        /**
         * @var NormalAttribute $attr
         */
        foreach ($listattr as $i => $attr) {
            if ($onlytab && ($attr->fieldSet->id != $onlytab && $attr->fieldSet->fieldSet->id != $onlytab)) continue;
            
            $iattr++;
            $htmlvalue = '';
            //------------------------------
            // Compute value element
            $value = chop($this->getRawValue($i));
            if (!$attr->fieldSet) {
                addWarningMsg(sprintf(_("unknow set for attribute %s %s") , $attr->id, $attr->getLabel()));
                continue;
            }
            $frametpl = $attr->fieldSet->getOption("viewtemplate");
            if ($attr->fieldSet && ($frametpl && $attr->fieldSet->type != "array")) {
                $goodvalue = false;
                if ($currentFrameId != $attr->fieldSet->id) {
                    if (($attr->fieldSet->mvisibility != "H") && ($attr->fieldSet->mvisibility != "I")) {
                        $changeframe = true;
                    }
                }
            } else {
                $goodvalue = ((($value != "") || ($attr->type == "array") || $attr->getOption("showempty")) && ($attr->mvisibility != "H") && ($attr->mvisibility != "I") && ($attr->mvisibility != "O") && (!$attr->inArray()));
                if (($attr->type == "array") && (!$attr->getOption("showempty"))) {
                    if (count($this->getArrayRawValues($attr->id)) == 0) $goodvalue = false;
                }
                
                if ($goodvalue) {
                    // detect first tab
                    $toptab = $attr->getTab();
                    /**
                     * @var FieldsetAttribute $toptab
                     */
                    if ($toptab) $tabonfly = ($toptab->getOption("viewonfly") == "yes");
                    if ($tabonfly && (!$showonlytab)) {
                        $ut = $this->getUtag("lasttab");
                        if ($ut) $showonlytab = $ut->comment;
                        elseif ($attr->fieldSet->id && $attr->fieldSet->fieldSet) {
                            $showonlytab = $attr->fieldSet->fieldSet->id;
                        }
                    }
                    $attrInNextTab = ($tabonfly && $toptab && ($toptab->id != $showonlytab));
                    if (!$attrInNextTab) {
                        $viewtpl = $attr->getOption("viewtemplate");
                        if ($viewtpl) {
                            if ($viewtpl == "none") {
                                $htmlvalue = '';
                            } else {
                                if ($this->getZoneOption($viewtpl) == 'S') {
                                    $attr->setOption("vlabel", "none");
                                }
                                $htmlvalue = sprintf("[ZONE FDL:VIEWTPL?id=%d&famid=%d&target=%s&zone=%s]", $this->id, $this->fromid, $target, $viewtpl);
                            }
                        } else {
                            $htmlvalue = $this->GetHtmlValue($attr, $value, $target, $ulink);
                        }
                    } else {
                        $htmlvalue = false; // display defer
                        
                    }
                } else $htmlvalue = "";
                
                if (($htmlvalue === false) || ($goodvalue)) { // to define when change frame
                    if ($currentFrameId != $attr->fieldSet->id) {
                        if (($currentFrameId != "") && ($attr->fieldSet->mvisibility != "H")) $changeframe = true;
                    }
                }
            }
            //------------------------------
            // change frame if needed
            if ($changeframe) { // to generate  fieldset
                $oaf = $this->getAttribute($currentFrameId);
                
                $frametpl = $oaf->getOption("viewtemplate");
                $changeframe = false;
                if ((($v + $nbimg) > 0) || $frametpl) { // one value detected
                    $frames[$k]["frametext"] = ($oaf && $oaf->getOption("vlabel") != "none") ? mb_ucfirst($this->GetLabel($oaf->id)) : "";
                    $frames[$k]["frameid"] = $oaf->id;
                    $frames[$k]["bgcolor"] = $oaf ? $oaf->getOption("bgcolor", false) : false;
                    
                    $frames[$k]["tag"] = "";
                    $frames[$k]["TAB"] = false;
                    /**
                     * @var FieldSetAttribute $pSet
                     */
                    $pSet = $oaf->fieldSet;
                    if ($pSet && ($pSet->id != "") && ($pSet->id != "FIELD_HIDDENS")) {
                        $frames[$k]["tag"] = "TAG" . $pSet->id;
                        $frames[$k]["TAB"] = true;
                        $ttabs[$pSet->id] = array(
                            "tabid" => $pSet->id,
                            "tabtitle" => ($pSet->getOption("vlabel") == "none") ? '&nbsp;' : mb_ucfirst($pSet->getLabel())
                        );
                    }
                    $frames[$k]["viewtpl"] = ($frametpl != "");
                    $frames[$k]["zonetpl"] = ($frametpl != "") ? sprintf("[ZONE FDL:VIEWTPL?id=%d&famid=%d&target=%s&zone=%s]", $this->id, $this->fromid, $target, $frametpl) : '';
                    
                    $frames[$k]["rowspan"] = $v + 1; // for images cell
                    $frames[$k]["TABLEVALUE"] = "TABLEVALUE_$k";
                    
                    $this->lay->SetBlockData($frames[$k]["TABLEVALUE"], $tableframe);
                    $frames[$k]["IMAGES"] = "IMAGES_$k";
                    $this->lay->SetBlockData($frames[$k]["IMAGES"], $tableimage);
                    $frames[$k]["notloaded"] = false;
                    if ($oaf && $oaf->type == "frame" && (count($tableframe) + count($tableimage)) == 0) {
                        if (!$frames[$k]["viewtpl"]) {
                            $frames[$k]["viewtpl"] = true;
                            $frames[$k]["zonetpl"] = _("Loading...");
                            $frames[$k]["notloaded"] = true;
                        }
                    }
                    unset($tableframe);
                    unset($tableimage);
                    $tableframe = array();
                    $tableimage = array();
                    $k++;
                }
                $v = 0;
                $nbimg = 0;
                $currentFrameId = $attr->fieldSet->id;
            }
            if ($htmlvalue === false) {
                $goodvalue = false;
                if ($currentFrameId != $attr->fieldSet->id) {
                    if (($attr->fieldSet->mvisibility != "H") && ($attr->fieldSet->mvisibility != "I")) {
                        $changeframe = true;
                        $currentFrameId = $attr->fieldSet->id;
                        $currentFrame = $attr->fieldSet;
                        $v++;
                    }
                }
            }
            //------------------------------
            // Set the table value elements
            if ($goodvalue) {
                switch ($attr->type) {
                    case "image":
                        $tableimage[$nbimg]["imgsrc"] = $htmlvalue;
                        $tableimage[$nbimg]["itarget"] = ($action->Read("navigator", "") == "NETSCAPE") ? "_self" : "_blank";
                        $width = $attr->getOption("iwidth", "80px");
                        $tableimage[$nbimg]["imgwidth"] = $width;
                        if (strstr($htmlvalue, 'EXPORTFILE')) {
                            $tableimage[$nbimg]["imgthumbsrc"] = $htmlvalue . "&width=" . intval($width);
                        } else {
                            $tableimage[$nbimg]["imgthumbsrc"] = $htmlvalue;
                        }
                        break;

                    default:
                        $tableframe[$v]["nonelabel"] = false;
                        $tableframe[$v]["normallabel"] = true;
                        $tableframe[$v]["uplabel"] = false;
                        $tableframe[$v]["value"] = $htmlvalue;
                        break;
                }
                
                if (($attr->fieldSet->mvisibility != "H") && ($htmlvalue !== "" || $goodvalue)) {
                    $currentFrameId = $attr->fieldSet->id;
                    $currentFrame = $attr->fieldSet;
                }
                // print name except image (printed otherthere)
                if ($attr->type != "image") {
                    $tableframe[$v]["wvalue"] = (($attr->type == "array") && ($attr->getOption("vlabel") == "up" || $attr->getOption("vlabel") == "none")) ? "1%" : "30%"; // width
                    $tableframe[$v]["ndisplay"] = "inline";
                    
                    if ($attr->getOption("vlabel") == "none") {
                        $tableframe[$v]["nonelabel"] = true;
                        $tableframe[$v]["normallabel"] = false;
                    } else if ($attr->getOption("vlabel") == "up") {
                        if ($attr->type == "array") { // view like none label
                            $tableframe[$v]["nonelabel"] = true;
                            $tableframe[$v]["normallabel"] = false;
                        } else {
                            $tableframe[$v]["normallabel"] = false;
                            $tableframe[$v]["uplabel"] = true;
                        }
                    }
                    $tableframe[$v]["name"] = $this->GetLabel($attr->id);
                    if (($attr->type == "htmltext") && (count($tableframe) == 1)) {
                        $keys = array_keys($listattr);
                        if (isset($keys[$iattr])) {
                            $na = $listattr[$keys[$iattr]]; // next attribute
                            if ($na->fieldSet->id != $attr->fieldSet->id) { // only when only one attribute in frame
                                $tableframe[$v]["ndisplay"] = "none";
                                $tableframe[$v]["wvalue"] = "1%";
                            }
                        }
                    }
                    
                    $tableframe[$v]["classback"] = ($attr->usefor == "O") ? "FREEDOMOpt" : "FREEDOMBack1";
                    $v++;
                } else {
                    $tableimage[$nbimg]["imgalt"] = $this->GetLabel($attr->id);
                    $nbimg++;
                }
            }
        }
        $oaf = $this->getAttribute($currentFrameId);
        if ($oaf) {
            $frametpl = $oaf->getOption("viewtemplate");
        }
        if ((($v + $nbimg) > 0) || $frametpl) // // last fieldset
        {
            if ($oaf) $frames[$k]["frametext"] = ($oaf->getOption("vlabel") != "none") ? mb_ucfirst($this->GetLabel($currentFrameId)) : "";
            else $frames[$k]["frametext"] = '';
            $frames[$k]["frameid"] = $currentFrameId;
            $frames[$k]["tag"] = "";
            $frames[$k]["TAB"] = false;
            $frames[$k]["viewtpl"] = ($frametpl != "");
            $frames[$k]["zonetpl"] = ($frametpl != "") ? sprintf("[ZONE FDL:VIEWTPL?id=%d&famid=%d&target=%s&zone=%s]", $this->id, $this->fromid, $target, $frametpl) : '';
            
            $frames[$k]["bgcolor"] = $oaf ? $oaf->getOption("bgcolor", false) : false;
            $pSet = $currentFrame->fieldSet;
            if (($pSet->id != "") && ($pSet->id != "FIELD_HIDDENS")) {
                $frames[$k]["tag"] = "TAG" . $pSet->id;
                $frames[$k]["TAB"] = true;
                $ttabs[$pSet->id] = array(
                    "tabid" => $pSet->id,
                    "tabtitle" => ($pSet->getOption("vlabel") == "none") ? '&nbsp;' : mb_ucfirst($pSet->getLabel())
                );
            }
            $frames[$k]["rowspan"] = $v + 1; // for images cell
            $frames[$k]["TABLEVALUE"] = "TABLEVALUE_$k";
            
            $this->lay->SetBlockData($frames[$k]["TABLEVALUE"], $tableframe);
            
            $frames[$k]["IMAGES"] = "IMAGES_$k";
            $this->lay->SetBlockData($frames[$k]["IMAGES"], $tableimage);
            $frames[$k]["notloaded"] = false;
            if ($oaf->type == "frame" && (count($tableframe) + count($tableimage)) == 0) {
                if (!$frames[$k]["viewtpl"]) {
                    $frames[$k]["viewtpl"] = true;
                    $frames[$k]["zonetpl"] = _("Loading...");
                    $frames[$k]["notloaded"] = true;
                }
            }
        }
        // Out
        $this->lay->SetBlockData("TABLEBODY", $frames);
        $this->lay->SetBlockData("TABS", $ttabs);
        $this->lay->Set("ONETAB", count($ttabs) > 0);
        $this->lay->Set("NOTAB", ($target == "mail") || $onlytab);
        $this->lay->Set("docid", $this->id);
        
        if (count($ttabs) > 0) {
            $this->lay->Set("firsttab", false);
            $ut = $this->getUtag("lasttab");
            if ($ut) $firstopen = $ut->comment; // last memo tab
            else $firstopen = false;
            foreach ($ttabs as $k => $v) {
                $oa = $this->getAttribute($k);
                if ($oa->getOption("firstopen") == "yes") $this->lay->set("firsttab", $k);
                if ($firstopen == $oa->id) $this->lay->Set("firsttab", $k);
            }
        }
    }
    /**
     * write layout for thumb view
     * @templateController controller for thumb view (used in mail link)
     */
    function viewthumbcard($target = "finfo", $ulink = true, $abstract = true)
    {
        $this->viewabstractcard($target, $ulink, $abstract);
        $this->viewprop($target, $ulink, $abstract);
        $this->lay->set("iconsrc", $this->getIcon());
        $state = $this->getState();
        if ($state != "") $this->lay->set("state", _($state));
        else $this->lay->set("state", "");
    }
    /**
     *  layout for view answers
     * @templateController controller used if use WASK in workflow
     */
    function viewanswers($target = "finfo", $ulink = true, $abstract = true)
    {
        $err = '';
        if (!$this->isAlive()) $err = (sprintf(_("unknow document reference '%s'") , GetHttpVars("docid")));
        if ($err == "") $err = $this->control("wask");
        if ($err) {
            $this->lay->template = $err;
            return;
        }
        
        $answers = $this->getWasks(false);
        $tw = array();
        foreach ($answers as $ka => $ans) {
            $utags = $this->searchUTags("ASK_" . $ans["waskid"], false, true);
            /**
             * @var \Dcp\Family\WASK $wask
             */
            $wask = new_doc($this->dbaccess, $ans["waskid"]);
            $wask->set($this);
            
            $taguid = array();
            
            $t = array();
            foreach ($utags as $k => $v) {
                $taguid[] = $v["uid"];
                $t[$k] = $v;
                $t[$k]["label"] = $wask->getAskLabel($v["comment"]);
                $t[$k]["ask"] = $wask->getRawValue("was_ask");
            }
            
            uasort($t, array(
                get_class($this) ,
                "_cmpanswers"
            ));
            $prevc = '';
            $odd = 0;
            foreach ($t as $k => $v) {
                if ($v["comment"] != $prevc) {
                    $prevc = $v["comment"];
                    $odd++;
                }
                $t[$k]["class"] = (($odd % 2) == 0) ? "evenanswer" : "oddanswer";
            }
            // find user not answered
            $ru = $wask->getUsersForAcl('answer'); // all users must answered
            $una = array_diff(array_keys($ru) , $taguid);
            
            $tna = array();
            
            $tuna = array();
            foreach ($una as $k => $v) {
                $tuna[$v] = $ru[$v]["login"];
            }
            
            asort($tuna, SORT_STRING);
            foreach ($tuna as $k => $v) {
                $tna[] = array(
                    "login" => $ru[$k]["login"],
                    "fn" => $ru[$k]["firstname"],
                    "ln" => $ru[$k]["lastname"]
                );
            }
            
            $this->lay->setBlockData("ANSWERS" . $wask->id, $t);
            $this->lay->setBlockData("NOTANS" . $wask->id, $tna);
            $title = $wask->getTitle();
            
            $this->lay->set("asktitle", $title);
            $tw[] = array(
                "waskid" => $wask->id,
                "nacount" => sprintf(_("number of waiting answers %d") , count($una)) ,
                "count" => (count($t) > 1) ? sprintf(_("%d answers") , count($t)) : sprintf(_("%d answer") , count($t)) ,
                "ask" => $wask->getRawValue("was_ask")
            );
        }
        $this->lay->setBlockData("WASK", $tw);
        $this->lay->set("docid", $this->id);
    }
    /**
     * to sort answer by response
     */
    static function _cmpanswers($a, $b)
    {
        return strcasecmp($a["comment"] . $a["uname"], $b["comment"] . $b["uname"]);
    }
    /**
     * @templateController controller to view document properties
     * write layout for properties view
     */
    function viewproperties($target = "finfo", $ulink = true, $abstract = true)
    {
        global $action;
        $this->viewprop($target, $ulink, $abstract);
        $this->lay->set("iconsrc", $this->getIcon());
        $fdoc = $this->getFamilyDocument();
        $this->lay->Set("ficonsrc", $fdoc->getIcon());
        $owner = new Account("", abs($this->owner));
        $this->lay->Set("username", $owner->firstname . " " . $owner->lastname);
        $this->lay->Set("userid", $owner->fid);
        $this->lay->Set("lockedby", $this->lay->get("locked"));
        
        $this->lay->Set("lockdomain", '');
        if ($this->locked == - 1) {
            $this->lay->Set("lockedid", false);
        } else {
            $user = new Account("", abs($this->locked));
            // $this->lay->Set("locked", $user->firstname." ".$user->lastname);
            if ($this->lockdomainid) {
                $this->lay->Set("lockdomain", sprintf(_("in domain %s") , $this->getDocAnchor($this->lockdomainid, '_blank', true, '', false, true, true)));
            }
            $this->lay->Set("lockedid", $user->fid);
        }
        $state = $this->getState();
        if ($state != "") {
            if (($this->locked == - 1) || ($this->lmodify != 'Y')) $this->lay->Set("state", _($state));
            else $this->lay->Set("state", sprintf(_("current (<em>%s</em>)") , _($state)));
        } else $this->lay->set("state", _("no state"));
        if (is_numeric($this->state) && ($this->state > 0) && (!$this->wid)) {
            $this->lay->set("freestate", $this->state);
        } else $this->lay->set("freestate", false);
        $this->lay->set("setname", $action->parent->Haspermission("FREEDOM_MASTER", "FREEDOM"));
        $this->lay->set("lname", $this->name);
        $this->lay->set("hasrevision", ($this->revision > 0));
        $this->lay->Set("moddate", strftime("%Y-%m-%d %H:%M:%S", $this->revdate));
        $this->lay->set("moddatelabel", _("last modification date"));
        if ($this->locked == - 1) {
            if ($this->doctype == 'Z') $this->lay->set("moddatelabel", _("suppression date"));
            else $this->lay->set("moddatelabel", _("revision date"));
        }
        if (GetParam("CORE_LANG") == "fr_FR") { // date format depend of locale
            $this->lay->Set("revdate", strftime("%a %d %b %Y %H:%M", $this->revdate));
        } else {
            $this->lay->Set("revdate", strftime("%x %T", $this->revdate));
        }
        $this->lay->Set("version", $this->version);
        
        if ((abs($this->profid) > 0) && ($this->profid != $this->id)) {
            
            $this->lay->Set("profile", $this->getDocAnchor(abs($this->profid) , '_blank', true, '', false, 'latest', true));
        } else {
            if ($this->profid == 0) {
                $this->lay->Set("profile", _("no access control"));
            } else {
                if ($this->dprofid == 0) {
                    
                    $this->lay->Set("profile", $this->getDocAnchor(abs($this->profid) , '_blank', true, _("specific control") , false, 'latest', true));
                } else {
                    $this->lay->Set("profile", $this->getDocAnchor(abs($this->dprofid) , '_blank', true, _("dynamic control") . " (" . $this->getHTMLTitle(abs($this->dprofid)) . ")", false, 'latest', true));
                }
            }
        }
        if ($this->cvid == 0) {
            $this->lay->Set("cview", _("no view control"));
        } else {
            $this->lay->Set("cview", $this->getDocAnchor($this->cvid, '_blank', true, '', false, 'latest', true));
        }
        if ($this->prelid == 0) {
            $this->lay->Set("prel", _("no folder"));
        } else {
            
            $this->lay->Set("prel", $this->getDocAnchor($this->prelid, '_blank', true, '', false, 'latest', true));
            $fldids = $this->getParentFolderIds();
            $tfld = array();
            foreach ($fldids as $fldid) {
                if ($fldid != $this->prelid) {
                    $tfld[] = array(
                        "fld" => $this->getDocAnchor($fldid, '_blank', true, '', false, 'latest', true)
                    );
                }
            }
            $this->lay->setBlockData("FOLDERS", $tfld);
        }
        if ($this->allocated == 0) {
            $this->lay->Set("allocate", _("no allocate"));
            $this->lay->Set("allocateid", false);
        } else {
            $user = new Account("", ($this->allocated));
            $this->lay->Set("allocate", $user->firstname . " " . $user->lastname);
            $this->lay->Set("allocateid", $user->fid);
        }
        
        $tms = $this->getAttachedTimers();
        
        $this->lay->Set("Timers", (count($tms) > 0));
    }
    /**
     * @templateController controller for abstract view
     * write layout for abstract view
     */
    function viewabstractcard($target = "finfo", $ulink = true, $abstract = true)
    {
        $listattr = $this->GetAbstractAttributes();
        
        $tableframe = array();
        
        foreach ($listattr as $i => $attr) {
            //------------------------------
            // Compute value elements
            $value = $this->getRawValue($i);
            
            if (($attr->mvisibility != "H") && ($attr->mvisibility != "I")) {
                $dValue = '';
                switch ($attr->type) {
                    case "image":
                        $iValue = $this->GetHtmlValue($listattr[$i], $value, $target, $ulink, -1, true, true);
                        if ($iValue != "") {
                            if ($value) {
                                $dValue = "<img align=\"absbottom\" height=\"30px\" src=\"" . $iValue . "&height=30\">";
                            } else {
                                $dValue = "<img align=\"absbottom\" height=\"30px\" src=\"" . $iValue . "\">";
                            }
                        }
                        break;

                    default:
                        $dValue = $this->getHtmlValue($listattr[$i], $value, $target, $ulink = 1, -1, true, true);
                        
                        break;
                }
                if ($dValue !== '') {
                    $tableframe[] = array(
                        "name" => $attr->getLabel() ,
                        "aid" => $attr->id,
                        "value" => $dValue
                    );
                }
            }
        }
        $this->lay->SetBlockData("TABLEVALUE", $tableframe);
    }
    /**
     * set V_<attrid> and L_<attrid> keys for current layout
     * the keys are in uppercase letters
     * @param string $target HTML target for links
     * @param bool $ulink set to true to have HTML hyperlink when it is possible
     * @param bool $abstract set to true to restrict to abstract attributes
     * @param bool $viewhidden set to true to return also hidden attribute (visibility H)
     */
    final public function viewattr($target = "_self", $ulink = true, $abstract = false, $viewhidden = false)
    {
        $listattr = $this->GetNormalAttributes();
        // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value
        foreach ($listattr as $k => $v) {
            $value = chop($this->getRawValue($v->id));
            //------------------------------
            // Set the table value elements
            $this->lay->Set("S_" . strtoupper($v->id) , ($value != ""));
            // don't see  non abstract if not
            if ((($v->mvisibility == "H") && (!$viewhidden)) || ($v->mvisibility == "I") || (($abstract) && (!$v->isInAbstract))) {
                $this->lay->Set("V_" . strtoupper($v->id) , "");
                $this->lay->Set("L_" . strtoupper($v->id) , "");
            } else {
                if ($target == "ooo") {
                    if ($v->type == "array") {
                        $tva = $this->getArrayRawValues($v->id);
                        
                        $tmkeys = array();
                        foreach ($tva as $kindex => $kvalues) {
                            foreach ($kvalues as $kaid => $va) {
                                $oa = $this->getAttribute($kaid);
                                if ($oa->getOption("multiple") == "yes") {
                                    // second level
                                    $oa->setOption("multiple", "no"); //  needto have values like first level
                                    $values = explode("<BR>", $va);
                                    $ovalues = array();
                                    foreach ($values as $ka => $vaa) {
                                        $ovalues[] = htmlspecialchars_decode($this->GetOOoValue($oa, $vaa));
                                    }
                                    //print_r(array($oa->id=>$ovalues));
                                    $tmkeys[$kindex]["V_" . strtoupper($kaid) ] = $ovalues;
                                    $oa->setOption("multiple", "yes"); //  needto have values like first level
                                    
                                } else {
                                    $tmkeys[$kindex]["V_" . strtoupper($kaid) ] = $this->GetOOoValue($oa, $va);
                                }
                            }
                        }
                        //print_r($tmkeys);
                        $this->lay->setRepeatable($tmkeys);
                    } else {
                        $ovalue = $this->GetOOoValue($v, $value);
                        if ($v->isMultiple()) $ovalue = str_replace("<text:tab/>", ', ', $ovalue);
                        $this->lay->Set("V_" . strtoupper($v->id) , $ovalue);
                        // print_r(array("V_".strtoupper($v->id)=>$this->GetOOoValue($v, $value),"raw"=>$value));
                        if ((!$v->inArray()) && ($v->getOption("multiple") == "yes")) {
                            $values = $this->getMultipleRawValues($v->id);
                            $ovalues = array();
                            $v->setOption("multiple", "no");
                            foreach ($values as $ka => $va) {
                                $ovalues[] = htmlspecialchars_decode($this->GetOOoValue($v, $va));
                            }
                            $v->setOption("multiple", "yes");
                            //print_r(array("V_".strtoupper($v->id)=>$ovalues,"raw"=>$values));
                            $this->lay->setColumn("V_" . strtoupper($v->id) , $ovalues);
                        } else {
                            //$this->lay->Set("V_" . strtoupper($v->id), $this->GetOOoValue($v, $value));
                            
                        }
                    }
                } else $this->lay->Set("V_" . strtoupper($v->id) , $this->GetHtmlValue($v, $value, $target, $ulink));
                $this->lay->Set("L_" . strtoupper($v->id) , $v->getLabel());
            }
        }
        $listattr = $this->GetFieldAttributes();
        // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value
        foreach ($listattr as $k => $v) {
            $this->lay->Set("L_" . strtoupper($v->id) , $v->getLabel());
        }
    }
    /**
     * set properties keys in current layout
     *  the keys are in uppercase letters
     * produce alse V_TITLE key to have a HTML link to document (for HTML layout)
     * @param string $target
     * @param bool $ulink for the V_TITLE key
     * @param bool $abstract unused
     */
    final public function viewprop($target = "_self", $ulink = true, $abstract = false)
    {
        foreach ($this->fields as $k => $v) {
            if ($target == 'ooo') $this->lay->Set(strtoupper($v) , ($this->$v === null) ? false : str_replace(array(
                "<",
                ">",
                '&'
            ) , array(
                "&lt;",
                "&gt;",
                "&amp;"
            ) , $this->$v));
            else $this->lay->Set(strtoupper($v) , ($this->$v === null) ? false : $this->$v);
        }
        if ($target == 'ooo') $this->lay->Set("V_TITLE", $this->lay->get("TITLE"));
        else $this->lay->Set("V_TITLE", $this->getDocAnchor($this->id, $target, $ulink, false, false));
    }
    /**
     * affect a logical name that can be use as unique reference of a document independant of database
     * @param string $name new logical name
     * @param bool $reset set to true to accept change
     * @deprecated use ::setLogicalName instead
     * @return string error message if cannot be
     */
    function setLogicalIdentificator($name, $reset = false)
    {
        deprecatedFunction();
        return $this->setLogicalName($name, $reset);
    }
    /**
     * affect a logical name that can be use as unique reference of a document independant of database
     * @param string $name new logical name
     * @param bool $reset set to true to accept change
     * @param bool $verifyOnly if true only verify syntax and unicity
     * @return string error message if cannot be
     */
    function setLogicalName($name, $reset = false, $verifyOnly = false)
    {
        if ($name) {
            if (!CheckDoc::isWellformedLogicalName($name)) {
                if (!$this->isAffected()) {
                    $this->name = $name; // affect to be controlled in add and return error also
                    
                }
                return (sprintf(_("name must begin with a letter and the containt only alphanumeric characters or - and _: invalid  [%s]") , $name));
            } elseif (!$verifyOnly && !$this->isAffected()) {
                $this->name = $name;
                return "";
            } elseif (!$verifyOnly && $this->isAffected() && ($this->name != "") && ($this->doctype != 'Z') && !$reset) {
                return (sprintf(_("Logical name %s already set for %s. Use reset parameter to overhide it") , $name, $this->title));
            } else {
                // verify not use yet
                $d = getTDoc($this->dbaccess, $name);
                if ($d && $d["doctype"] != 'Z') {
                    return sprintf(_("Logical name %s already use in document %s") , $name, $d["title"]);
                } elseif (!$verifyOnly) {
                    if ($this->name) {
                        simpleQuery($this->dbaccess, sprintf("UPDATE docname SET name = '%s' WHERE name = '%s'", pg_escape_string($name) , pg_escape_string($this->name)));
                    }
                    $this->name = $name;
                    $err = $this->modify(true, array(
                        "name"
                    ) , true);
                    if ($err != "") {
                        return $err;
                    }
                }
            }
        }
        return "";
    }
    /**
     * view only option values
     * @templateController
     * @deprecated option attributes are not supported
     * @param string $target
     * @param bool $ulink
     * @param bool $abstract
     * @return void
     */
    final public function viewoptcard($target = "_self", $ulink = true, $abstract = false)
    {
        deprecatedFunction();
        $this->viewbodycard($target, $ulink, $abstract, true);
    }
    /**
     * edit only option
     * @templateController
     * @deprecated option attributes are not supported
     * @param string $target
     * @param bool $ulink
     * @param bool $abstract
     * @return void
     */
    final public function editoptcard($target = "_self", $ulink = true, $abstract = false)
    {
        deprecatedFunction();
        $this->editbodycard($target, $ulink, $abstract, true);
    }
    /**
     * value for edit interface
     * @templateController default control for HTML form document edition
     * @param bool $onlyopt if true only optional attributes are displayed
     */
    function editbodycard($target = "_self", $ulink = true, $abstract = false, $onlyopt = false)
    {
        include_once ("FDL/editutil.php");
        include_once ("FDL/Class.SearchDoc.php");
        
        $docid = $this->id; // document to edit
        // ------------------------------------------------------
        //  new or modify ?
        if ($docid == 0) {
            // new document
            if ($this->fromid > 0) {
                $cdoc = $this->getFamilyDocument();
                $this->lay->Set("title", sprintf(_("new %s") , $cdoc->getHtmlTitle()));
            }
        } else {
            // when modification
            
            /**
             * @var Action $action
             */
            global $action;
            if (!$this->isAlive()) $action->ExitError(_("document not referenced"));
            $this->lay->Set("title", $this->getHtmlTitle());
        }
        $this->lay->Set("id", $docid);
        $this->lay->Set("classid", $this->fromid);
        // get inline help
        $help = $this->getHelpPage();
        // ------------------------------------------------------
        // Perform SQL search for doc attributes
        // ------------------------------------------------------
        $frames = array();
        $listattr = $this->GetInputAttributes($onlyopt);
        
        $nattr = count($listattr); // number of attributes
        $k = 0; // number of frametext
        $v = 0; // number of value in one frametext
        $currentFrameId = "";
        /**
         * @var NormalAttribute $currentFrame
         */
        $currentFrame = null;
        $currentFrameText = "";
        $changeframe = false;
        $ih = 0; // index for hidden values
        $thidden = array();
        $tableframe = array();
        $ttabs = array();
        $frametpl = '';
        $iattr = 0;
        foreach ($listattr as $i => $attr) {
            $iattr++;
            // Compute value elements
            if ($docid > 0) $value = $this->getRawValue($attr->id);
            else {
                $value = $this->getRawValue($attr->id);
                //	$value = $this->GetValueMethod($this->GetValue($listattr[$i]->id));
                
            }
            if (!$attr->fieldSet) {
                addWarningMsg(sprintf(_("unknow set for attribute %s %s") , $attr->id, $attr->getLabel()));
                continue;
            }
            
            if ($currentFrameId != $attr->fieldSet->id) {
                if ($currentFrameId != "") $changeframe = true;
            }
            if ($changeframe) { // to generate final frametext
                $changeframe = false;
                /**
                 * @var BasicAttribute $oaf
                 */
                $oaf = $this->getAttribute($currentFrameId);
                if ($v > 0 || $frametpl) { // one value detected
                    if ($oaf->getOption("vlabel") == "none") $currentFrameText = '';
                    else $currentFrameText = mb_ucfirst($oaf->GetLabel());
                    
                    $frames[$k]["frametext"] = $currentFrameText;
                    $frames[$k]["frameid"] = $oaf->id;
                    $frames[$k]["tag"] = "";
                    $frames[$k]["TAB"] = false;
                    $frames[$k]["edittpl"] = ($frametpl != "");
                    $frames[$k]["zonetpl"] = ($frametpl != "") ? sprintf("[ZONE FDL:EDITTPL?id=%d&famid=%d&zone=%s]", $this->id, $this->fromid, $frametpl) : '';
                    $oaf = $this->getAttribute($oaf->id);
                    $frames[$k]["bgcolor"] = $oaf ? $oaf->getOption("bgcolor", false) : false;
                    $frames[$k]["ehelp"] = ($help->isAlive()) ? $help->getAttributeHelpUrl($oaf->id) : false;
                    $frames[$k]["ehelpid"] = ($help->isAlive()) ? $help->id : false;
                    if ($oaf && $oaf->fieldSet && ($oaf->fieldSet->id != "") && ($oaf->fieldSet->id != "FIELD_HIDDENS")) {
                        $frames[$k]["tag"] = "TAG" . $oaf->fieldSet->id;
                        $frames[$k]["TAB"] = true;
                        $ttabs[$oaf->fieldSet->id] = array(
                            "tabid" => $oaf->fieldSet->id,
                            "tabtitle" => ($oaf->fieldSet->getOption("vlabel") == "none") ? '&nbsp;' : mb_ucfirst($oaf->fieldSet->getLabel())
                        );
                    }
                    $frames[$k]["TABLEVALUE"] = "TABLEVALUE_$k";
                    $this->lay->SetBlockData($frames[$k]["TABLEVALUE"], $tableframe);
                    unset($tableframe);
                    $tableframe = array();
                    $k++;
                }
                $v = 0;
            }
            
            $currentFrameId = $listattr[$i]->fieldSet->id;
            $currentFrame = $attr->fieldSet;
            
            if ($currentFrame->mvisibility == 'R' || $currentFrame->mvisibility == 'H' || $currentFrame->mvisibility == 'I') {
                $frametpl = '';
            } else {
                $frametpl = $currentFrame->getOption("edittemplate");
            }
            if (!$frametpl) {
                //------------------------------
                // Set the table value elements
                if ($currentFrame->getOption("vlabel") == "none") $currentFrameText = '';
                else $currentFrameText = mb_ucfirst($currentFrame->GetLabel());
                if (($listattr[$i]->mvisibility == "H") || ($listattr[$i]->mvisibility == "R")) {
                    // special case for hidden values
                    if ($listattr[$i]->type != "array") {
                        $thidden[$ih]["hname"] = "_" . $listattr[$i]->id;
                        $thidden[$ih]["hid"] = $listattr[$i]->id;
                        if (($value == "") && ($this->id == 0)) $thidden[$ih]["hvalue"] = GetHttpVars($listattr[$i]->id);
                        else $thidden[$ih]["hvalue"] = chop(htmlentities($value, ENT_COMPAT, "UTF-8"));
                        
                        $thidden[$ih]["inputtype"] = getHtmlInput($this, $listattr[$i], $value, "", "", true);
                    }
                    $ih++;
                } else {
                    $tableframe[$v]["value"] = chop(htmlentities($value, ENT_COMPAT, "UTF-8"));
                    $label = $listattr[$i]->getLabel();
                    $tableframe[$v]["attrid"] = $listattr[$i]->id;
                    $tableframe[$v]["name"] = mb_ucfirst($label);
                    
                    if ($listattr[$i]->needed) $tableframe[$v]["labelclass"] = "FREEDOMLabelNeeded";
                    else $tableframe[$v]["labelclass"] = "FREEDOMLabel";
                    $elabel = $listattr[$i]->getoption("elabel");
                    $elabel = str_replace("'", "&rsquo;", $elabel);
                    $tableframe[$v]["elabel"] = mb_ucfirst(str_replace('"', "&rquot;", $elabel));
                    $tableframe[$v]["aehelp"] = ($help->isAlive()) ? $help->getAttributeHelpUrl($listattr[$i]->id) : false;
                    $tableframe[$v]["aehelpid"] = ($help->isAlive()) ? $help->id : false;
                    
                    $tableframe[$v]["multiple"] = ($attr->getOption("multiple") == "yes") ? "true" : "false";
                    $tableframe[$v]["atype"] = $attr->type;
                    $tableframe[$v]["name"] = mb_ucfirst($label);
                    $tableframe[$v]["classback"] = ($attr->usefor == "O") ? "FREEDOMOpt" : "FREEDOMBack1";
                    
                    $tableframe[$v]["SINGLEROW"] = true;
                    
                    $vlabel = $listattr[$i]->getOption("vlabel");
                    if ((($listattr[$i]->type == "array") && ($vlabel != 'left')) || (($listattr[$i]->type == "htmltext") && ($vlabel != 'left')) || ($vlabel == 'up') || ($vlabel == 'none')) $tableframe[$v]["SINGLEROW"] = false;
                    
                    $tableframe[$v]["viewlabel"] = (($listattr[$i]->type != "array") && ($vlabel != 'none'));
                    $edittpl = $listattr[$i]->getOption("edittemplate");
                    if ($edittpl) {
                        if ($edittpl == "none") {
                            unset($tableframe[$v]);
                        } else {
                            if ($this->getZoneOption($edittpl) == 'S') {
                                $tableframe[$v]["SINGLEROW"] = false;
                                $tableframe[$v]["viewlabel"] = false;
                            }
                            $tableframe[$v]["inputtype"] = sprintf("[ZONE FDL:EDITTPL?id=%d&famid=%d&zone=%s]", $this->id, $this->fromid, $edittpl);
                        }
                    } else {
                        $tableframe[$v]["inputtype"] = getHtmlInput($this, $listattr[$i], $value);
                    }
                    $v++;
                }
            }
        }
        // Out
        $oaf = $this->getAttribute($currentFrameId);
        if ($oaf->mvisibility == 'R' || $oaf->mvisibility == 'H' || $oaf->mvisibility == 'I') {
            $frametpl = '';
        } else {
            $frametpl = $oaf->getOption("edittemplate");
        }
        if ($v > 0 || $frametpl) { // latest fieldset
            if ($oaf->getOption("vlabel") == "none") $currentFrameText = '';
            else $currentFrameText = mb_ucfirst($oaf->GetLabel());
            $frames[$k]["frametext"] = $currentFrameText;
            $frames[$k]["frameid"] = $oaf->id;
            $frames[$k]["TABLEVALUE"] = "TABLEVALUE_$k";
            $frames[$k]["tag"] = "";
            $frames[$k]["TAB"] = false;
            $frames[$k]["edittpl"] = ($frametpl != "");
            $frames[$k]["zonetpl"] = ($frametpl != "") ? sprintf("[ZONE FDL:EDITTPL?id=%d&famid=%d&zone=%s]", $this->id, $this->fromid, $frametpl) : '';
            $frames[$k]["ehelp"] = ($help->isAlive()) ? $help->getAttributeHelpUrl($oaf->id) : false;
            $frames[$k]["ehelpid"] = ($help->isAlive()) ? $help->id : false;
            
            $oaf = $this->getAttribute($oaf->id);
            $frames[$k]["bgcolor"] = $oaf ? $oaf->getOption("bgcolor", false) : false;
            if (($currentFrame->fieldSet->id != "") && ($currentFrame->fieldSet->id != "FIELD_HIDDENS")) {
                $frames[$k]["tag"] = "TAG" . $currentFrame->fieldSet->id;
                $frames[$k]["TAB"] = true;
                $ttabs[$currentFrame->fieldSet->id] = array(
                    "tabid" => $currentFrame->fieldSet->id,
                    "tabtitle" => ($currentFrame->fieldSet->getOption("vlabel") == "none") ? '&nbsp;' : mb_ucfirst($currentFrame->fieldSet->getLabel())
                );
            }
            $this->lay->SetBlockData($frames[$k]["TABLEVALUE"], $tableframe);
        }
        $this->lay->SetBlockData("HIDDENS", $thidden);
        $this->lay->SetBlockData("TABLEBODY", $frames);
        $this->lay->SetBlockData("TABS", $ttabs);
        $this->lay->Set("ONETAB", count($ttabs) > 0);
        $this->lay->Set("fromid", $this->fromid);
        $this->lay->Set("docid", $this->id);
        if (count($ttabs) > 0) {
            $this->lay->Set("firsttab", false);
            $ut = $this->getUtag("lasttab");
            if ($ut) $firstopen = $ut->comment; // last memo tab
            else $firstopen = false;
            
            foreach ($ttabs as $k => $v) {
                $oa = $this->getAttribute($k);
                if ($oa->getOption("firstopen") == "yes") $this->lay->Set("firsttab", $k);
                if ($firstopen == $oa->id) $this->lay->Set("firsttab", $k);
            }
        }
    }
    /**
     * add V_<<ATTRID> keys for HTML form in current layout
     * add also L_<ATTRID> for attribute labels
     * create input fields for attribute document
     * @param bool $withtd set to false if don't wan't <TD> tag in the middle bet<een fields and button
     */
    final public function editattr($withtd = true)
    {
        
        include_once ("FDL/editutil.php");
        $listattr = $this->GetNormalAttributes();
        // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value
        foreach ($listattr as $k => $v) {
            //------------------------------
            // Set the table value elements
            $value = chop($this->getRawValue($v->id));
            if ($v->mvisibility == "R") $v->mvisibility = "H"; // don't see in edit mode
            $this->lay->Set("V_" . strtoupper($v->id) , getHtmlInput($this, $v, $value, "", "", (!$withtd)));
            if ($v->needed == "Y") $this->lay->Set("L_" . strtoupper($v->id) , "<B>" . $v->getLabel() . "</B>");
            else $this->lay->Set("L_" . strtoupper($v->id) , $v->getLabel());
            $this->lay->Set("W_" . strtoupper($v->id) , ($v->mvisibility != "H"));
        }
        
        $listattr = $this->GetFieldAttributes();
        // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value
        foreach ($listattr as $k => $v) {
            $this->lay->Set("L_" . strtoupper($v->id) , $v->getLabel());
        }
        
        $this->setFamidInLayout();
    }
    /**
     * add IDFAM_<famNAme> keys in current layout
     */
    final public function setFamidInLayout()
    {
        // add IDFAM_ attribute in layout
        global $tFamIdName;
        
        if (!isset($tFamIdName)) getFamIdFromName($this->dbaccess, "-");
        
        reset($tFamIdName);
        foreach ($tFamIdName as $k => $v) {
            $this->lay->set("IDFAM_$k", $v);
        }
    }
    /**
     * get vault file name or server path of filename
     * @param string $attrid identifier of file attribute
     * @param bool $path false return original file name (basename) , true the real path
     * @param int $index in case of array of files
     * @return string the file name of the attribute
     */
    final public function vault_filename($attrid, $path = false, $index = - 1)
    {
        if ($index == - 1) $fileid = $this->getRawValue($attrid);
        else $fileid = $this->getMultipleRawValues($attrid, '', $index);
        return $this->vault_filename_fromvalue($fileid, $path);
    }
    /**
     * get vault file name or server path of filename
     * @param string $fileid value of file attribute
     * @param bool $path false return original file name (basename) , true the real path
     * @return string the file name of the attribute
     */
    final public function vault_filename_fromvalue($fileid, $path = false)
    {
        $fname = "";
        if (preg_match(PREGEXPFILE, $fileid, $reg)) {
            // reg[1] is mime type
            $vf = newFreeVaultFile($this->dbaccess);
            /**
             * @var vaultFileInfo $info
             */
            if ($vf->Show($reg[2], $info) == "") {
                if ($path) $fname = $info->path;
                else $fname = $info->name;
            }
        }
        return $fname;
    }
    /**
     * get vault file name or server path of filename
     * @param NormalAttribute $attr identifier of file attribute
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
    final public function vault_properties(NormalAttribute $attr)
    {
        if ($attr->inArray()) $fileids = $this->getMultipleRawValues($attr->id);
        else $fileids[] = $this->getRawValue($attr->id);
        
        $tinfo = array();
        foreach ($fileids as $k => $fileid) {
            if (preg_match(PREGEXPFILE, $fileid, $reg)) {
                // reg[1] is mime type
                $vf = newFreeVaultFile($this->dbaccess);
                /**
                 * @var vaultFileInfo $info
                 */
                if ($vf->Show($reg[2], $info) == "") {
                    $tinfo[$k] = get_object_vars($info);
                    $tinfo[$k]["vid"] = $reg[2];
                }
            }
        }
        
        return $tinfo;
    }
    /**
     * return a property of vault file value
     * @param string $filesvalue the file value : like application/pdf|12345
     * @param string $key one of property id_file, name, size, public_access, mime_t, mime_s, cdate, mdate, adate, teng_state, teng_lname, teng_vid, teng_comment, path
     * @return string|array value of property or array of all properties if no key
     */
    final public function getFileInfo($filesvalue, $key = "")
    {
        if (!is_string($filesvalue)) return false;
        if (preg_match(PREGEXPFILE, $filesvalue, $reg)) {
            include_once ("FDL/Lib.Vault.php");
            $vid = $reg[2];
            $info = vault_properties($vid);
            if (!$info) return false;
            if ($key != "") {
                if (isset($info->$key)) return $info->$key;
                else return sprintf(_("unknow %s file property") , $key);
            } else {
                return get_object_vars($info);
            }
        }
        return $key ? '' : array();
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
    public function exportXml(&$xml, $withfile = false, $outfile = "", $wident = true, $flat = false, $exportAttributes = array())
    {
        $err = '';
        $lay = new Layout(getLayoutFile("FDL", "exportxml.xml"));
        //$lay=&$this->lay;
        $lay->set("famname", strtolower($this->fromname));
        $lay->set("id", ($wident ? $this->id : ''));
        $lay->set("name", $this->name);
        $lay->set("revision", $this->revision);
        $lay->set("version", $this->getVersion());
        $lay->set("state", $this->getState());
        $lay->set("title", str_replace(array(
            "&",
            '<',
            '>'
        ) , array(
            "&amp;",
            '&lt;',
            '&gt;'
        ) , $this->getTitle()));
        $lay->set("mdate", strftime("%FT%X", $this->revdate));
        $lay->set("flat", $flat);
        $la = $this->GetFieldAttributes();
        $level1 = array();
        
        foreach ($la as $k => $v) {
            if ((!$v) || ($v->getOption("autotitle") == "yes") || ($v->usefor == 'Q')) unset($la[$k]);
        }
        $option = new exportOptionAttribute();
        $option->withFile = $withfile;
        $option->outFile = $outfile;
        $option->withIdentifier = $wident;
        $option->flat = $flat;
        $option->exportAttributes = $exportAttributes;
        
        foreach ($la as $k => & $v) {
            if (($v->id != "FIELD_HIDDENS") && ($v->type == 'frame' || $v->type == "tab") && ((!$v->fieldSet) || $v->fieldSet->id == "FIELD_HIDDENS")) {
                $level1[] = array(
                    "level" => $v->getXmlValue($this, $option)
                );
            } else {
                // if ($v)  $tax[]=array("tax"=>$v->getXmlSchema());
                
            }
        }
        $lay->setBlockData("top", $level1);
        if ($outfile) {
            if ($withfile) {
                $xmlcontent = $lay->gen();
                $fo = fopen($outfile, "w");
                $pos = strpos($xmlcontent, "[FILE64");
                $bpos = 0;
                while ($pos !== false) {
                    if (fwrite($fo, substr($xmlcontent, $bpos, $pos - $bpos))) {
                        $bpos = strpos($xmlcontent, "]", $pos) + 1;
                        
                        $filepath = substr($xmlcontent, $pos + 8, ($bpos - $pos - 9));
                        /* If you want to encode a large file, you should encode it in chunks that
                                            are a multiple of 57 bytes.  This ensures that the base64 lines line up
                                            and that you do not end up with padding in the middle. 57 bytes of data
                                            fills one complete base64 line (76 == 57*4/3):*/
                        $ff = fopen($filepath, "r");
                        $size = 6 * 1024 * 57;
                        while ($buf = fread($ff, $size)) {
                            fwrite($fo, base64_encode($buf));
                        }
                        $pos = strpos($xmlcontent, "[FILE64", $bpos);
                    } else {
                        $err = sprintf(_("exportXml : cannot write file %s") , $outfile);
                        $pos = false;
                    }
                }
                if ($err == "") fwrite($fo, substr($xmlcontent, $bpos));
                fclose($fo);
            } else {
                if (file_put_contents($outfile, $lay->gen()) === false) {
                    $err = sprintf(_("exportXml : cannot write file %s") , $outfile);
                }
            }
        } else {
            $xml = $lay->gen();
            return $err;
        }
        return $err;
    }
    // =====================================================================================
    // ================= Methods use for XML ======================
    
    /**
     * @deprecated use exportXml instead
     * @param bool $withdtd
     * @param string $id_doc
     * @return string
     */
    final public function toxml($withdtd = false, $id_doc = "")
    {
        deprecatedFunction();
        /**
         * @var Action $action
         */
        global $action;
        $doctype = $this->doctype;
        
        $docid = intval($this->id);
        if ($id_doc == "") {
            $id_doc = $docid;
        }
        
        $title = $this->title;
        $fromid = $this->fromid;
        $dbaccess = $action->GetParam("FREEDOM_DB");
        $fam_doc = new_Doc($this->dbaccess, $this->fromid);
        $name = str_replace(" ", "_", $fam_doc->title);
        
        if ($withdtd == true) {
            $dtd = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\" ?>";
            $dtd.= "<!DOCTYPE $name [";
            /** @noinspection PhpDeprecationInspection */
            $dtd.= $this->todtd();
            $dtd.= "]>";
        } else {
            $dtd = "";
        }
        
        $this->lay = new Layout("FDL/Layout/viewxml.xml", $action);
        $this->lay->Set("DTD", $dtd);
        $this->lay->Set("NOM_FAM", $name);
        $this->lay->Set("id_doc", $id_doc);
        $this->lay->Set("TITRE", $title);
        $this->lay->Set("ID_FAM", $fam_doc->name);
        $this->lay->Set("revision", $this->revision);
        $this->lay->Set("revdate", $this->revdate);
        //$this->lay->Set("IDOBJECT",$docid);
        //$this->lay->Set("IDFAM",$fromid);
        //$idfam=$fam_doc->classname;
        //$this->lay->Set("TYPEOBJECT",$doctype);
        ////debut
        $listattr = $this->GetNormalAttributes();
        
        $frames = array();
        
        $nattr = count($listattr); // attributes list count
        $k = 0; // number of frametext
        $v = 0; // number of value in one frametext
        $currentFrameId = "";
        
        $changeframe = false; // is true when need change frame
        $tableframe = array();
        
        $iattr = 0;
        
        foreach ($listattr as $i => $attr) {
            $iattr++;
            
            if ((chop($listattr[$i]->id) != "") && ($listattr[$i]->id != "FIELD_HIDDENS")) {
                //------------------------------
                // Compute value elements
                if ($currentFrameId != $listattr[$i]->fieldSet->id) {
                    if ($currentFrameId != "") $changeframe = true;
                }
                //------------------------------
                // change frame if needed
                if ( // to generate  fiedlset
                $changeframe) {
                    $changeframe = false;
                    if ($v > 0) // one value detected
                    {
                        
                        $frames[$k]["FIELD"] = $currentFrameId;
                        $frames[$k]["ARGUMENT"] = "ARGUMENT_$k";
                        
                        $this->lay->SetBlockData($frames[$k]["ARGUMENT"], $tableframe);
                        $frames[$k]["nom_fieldset"] = $this->GetLabel($currentFrameId);
                        unset($tableframe);
                        $tableframe = array();
                        $k++;
                    }
                    $v = 0;
                }
                // Set the table value elements
                if (($iattr <= $nattr) && ($this->getRawValue($i) != "")) {
                    $attrtype_idoc = false;
                    $attrtype_list = false;
                    
                    if (strstr($listattr[$i]->type, "textlist") != false) {
                        $attrtype_list = true;
                    }
                    if ((strstr($listattr[$i]->type, "idoclist")) != false) {
                        $attrtype_list = true;
                        $attrtype_idoc = true;
                    }
                    if ((strstr($listattr[$i]->type, "idoc")) != false) {
                        $attrtype_idoc = true;
                    }
                    if ($listattr[$i]->inArray()) {
                        $attrtype_list = true;
                    }
                    
                    if ($attrtype_list) {
                        // $value=htmlspecialchars($this->GetValue($i));
                        $value = $this->getRawValue($i);
                        $textlist = $this->rawValueToArray($value);
                        
                        while ($text = each($textlist)) {
                            $currentFrameId = $listattr[$i]->fieldSet->id;
                            $tableframe[$v]["id"] = $listattr[$i]->id;
                            if ($attrtype_idoc) {
                                $tableframe[$v]["value"] = base64_decode($text[1]);
                                $tableframe[$v]["type"] = "idoc";
                            } else {
                                $tableframe[$v]["value"] = $text[1];
                                $tableframe[$v]["type"] = base64_encode($listattr[$i]->type);
                            }
                            $tableframe[$v]["labelText"] = (str_replace(array(
                                "%",
                                "\""
                            ) , array(
                                "",
                                "\\\""
                            ) , $listattr[$i]->getLabel()));
                            //$tableframe[$v]["type"]=$listattr[$i]->type;
                            //$tableframe[$v]["visibility"]=$listattr[$i]->visibility;
                            //$tableframe[$v]["needed"]=$listattr[$i]->needed;
                            $v++;
                        }
                    } else {
                        
                        if ($attrtype_idoc) {
                            $value = base64_decode($this->getRawValue($i));
                            $tableframe[$v]["type"] = "idoc";
                            //printf($value);
                            
                        } else {
                            $value = htmlspecialchars($this->getRawValue($i));
                            $tableframe[$v]["type"] = base64_encode($listattr[$i]->type);
                        }
                        
                        $currentFrameId = $listattr[$i]->fieldSet->id;
                        $tableframe[$v]["id"] = $listattr[$i]->id;
                        $tableframe[$v]["value"] = $value;
                        $tableframe[$v]["labelText"] = addslashes($listattr[$i]->getLabel());
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
            
            $frames[$k]["FIELD"] = $currentFrameId;
            $frames[$k]["ARGUMENT"] = "ARGUMENT_$k";
            
            $this->lay->SetBlockData($frames[$k]["ARGUMENT"], $tableframe);
            $frames[$k]["nom_fieldset"] = $this->GetLabel($currentFrameId);
            unset($tableframe);
            $tableframe = array();
            $tableimage = array();
            $k++;
        }
        
        $this->lay->SetBlockData("FIELDSET", $frames);
        return $this->lay->gen();
    }
    /**
     * @deprecated use exportXml instead
     * @return string
     */
    final public function todtd()
    {
        deprecatedFunction();
        global $action;
        $this->lay = new Layout("FDL/Layout/viewdtd.xml", $action);
        
        $fam_doc = $this->getFamilyDocument();
        $name = str_replace(" ", "_", $fam_doc->title);
        $this->lay->Set("doctype", $this->doctype);
        $this->lay->Set("idfam", $this->fromid);
        $this->lay->Set("nom_fam", $name);
        $this->lay->Set("id_fam", $name);
        
        $listattr = $this->GetNormalAttributes();
        
        $frames = $elements = array();
        
        $nattr = count($listattr); // attributes list count
        $k = 0; // number of frametext
        $v = 0; // number of value in one frametext
        $currentFrameId = "";
        
        $changeframe = false; // is true when need change frame
        $needed = false;
        $tableattrs = array();
        $tablesetting = array();
        $iattr = 0;
        
        foreach ($listattr as $i => $attr) {
            $iattr++;
            //------------------------------
            // Compute value elements
            if ($currentFrameId != $listattr[$i]->fieldSet->id) {
                if ($currentFrameId != "") $changeframe = true;
            }
            //------------------------------
            // change frame if needed
            if ( // to generate  fiedlset
            $changeframe) {
                $changeframe = false;
                
                if ($v > 0) // one value detected
                {
                    
                    $frames[$k]["name"] = $currentFrameId;
                    $elements[$k]["name"] = $currentFrameId;
                    if ($needed) {
                        $elements[$k]["name"].= ", ";
                    } else {
                        $elements[$k]["name"].= "?, ";
                    }
                    $needed = false;
                    
                    $frames[$k]["ATTRIBUT_NAME"] = "ATTRIBUT_NAME_$k";
                    $frames[$k]["ATTRIBUT_SETTING"] = "ATTRIBUT_SETTING_$k";
                    
                    $this->lay->SetBlockData($frames[$k]["ATTRIBUT_NAME"], $tableattrs);
                    
                    $this->lay->SetBlockData($frames[$k]["ATTRIBUT_SETTING"], $tablesetting);
                    unset($tableattrs);
                    unset($tablesetting);
                    $tableattrs = array();
                    $tablesetting = array();
                    
                    $k++;
                }
                $v = 0;
            }
            // Set the table value elements
            if ($iattr <= $nattr) {
                
                $currentFrameId = $listattr[$i]->fieldSet->id;
                $tablesetting[$v]["name_attribut"] = $listattr[$i]->id;
                $tablesetting[$v]["labelText"] = addslashes(str_replace("%", "", $listattr[$i]->getLabel()));
                $tablesetting[$v]["type"] = base64_encode($listattr[$i]->type);
                $tablesetting[$v]["visibility"] = $listattr[$i]->visibility;
                if ($listattr[$i]->needed) {
                    $needed = true;
                }
                
                if ($v == 0) {
                    $insert = $listattr[$i]->id;
                    if ($listattr[$i]->type == "textlist") {
                        if ($listattr[$i]->needed) {
                            $insert.= "+";
                            $tableattrs[$v]["name_attribut"] = $insert;
                        } else {
                            $insert.= "*";
                            $tableattrs[$v]["name_attribut"] = $insert;
                        }
                    } else {
                        if ($listattr[$i]->needed) {
                            $tableattrs[$v]["name_attribut"] = $insert;
                        } else {
                            $tableattrs[$v]["name_attribut"] = ($insert . "?");
                        }
                    }
                } else {
                    $insert = (", " . $listattr[$i]->id);
                    if ($listattr[$i]->type == "textlist") {
                        if ($listattr[$i]->needed) {
                            $insert.= "+";
                        } else {
                            $insert.= "*";
                        }
                        $tableattrs[$v]["name_attribut"] = $insert;
                    } else {
                        if ($listattr[$i]->needed) {
                            $tableattrs[$v]["name_attribut"] = $insert;
                        } else {
                            $tableattrs[$v]["name_attribut"] = ($insert . "?");
                        }
                    }
                }
                $v++;
            }
        }
        
        if ($v > 0) // last fieldset
        {
            $frames[$k]["name"] = $currentFrameId;
            if ($needed) {
                $elements[$k]["name"] = $currentFrameId;
            } else {
                $elements[$k]["name"] = ($currentFrameId . "?");
            }
            $needed = false;
            $frames[$k]["ATTRIBUT_NAME"] = "ATTRIBUT_NAME_$k";
            $frames[$k]["ATTRIBUT_SETTING"] = "ATTRIBUT_SETTING_$k";
            $this->lay->SetBlockData($frames[$k]["ATTRIBUT_NAME"], $tableattrs);
            
            $this->lay->SetBlockData($frames[$k]["ATTRIBUT_SETTING"], $tablesetting);
            unset($tableattrs);
            unset($tablesetting);
            $tableattrs = array();
            $tablesetting = array();
            
            $k++;
        }
        
        $this->lay->SetBlockData("FIELDSET", $frames);
        $this->lay->SetBlockData("ELEMENT", $elements);
        return $this->lay->gen();
    }
    /**
     * define custom title used to set title propert when update or create document
     * @api hook called in refresh title
     * this method can be redefined in child family to compose specific title
     */
    public function getCustomTitle()
    {
        return $this->title;
    }
    /**
     * define custom title used to set title propert when update or create document
     * @deprecated This hook may be replaced by getCustomTitle in the the next version.
     * this method can be redefined in child family to compose specific title
     */
    public function getSpecTitle()
    {
        return $this->title;
    }
    /**
     * @deprecated not needed until 2.0 version
     * @param string $nameId
     * @param $nameTitle
     */
    final public function refreshDocTitle($nameId, $nameTitle)
    {
        deprecatedFunction();
        // gettitle(D,SI_IDSOC):SI_SOCIETY,SI_IDSOC
        $this->AddParamRefresh("$nameId", "$nameTitle");
        $doc = new_Doc($this->dbaccess, $this->getRawValue($nameId));
        if ($doc->isAlive()) $this->setValue($nameTitle, $doc->title);
        else {
            // suppress
            if (!$doc->isAffected()) $this->clearValue($nameId);
        }
    }
    /**
     * get filename image emblem for the doc like lock/nowrite
     * @param int $size image width in pixel
     * @return string the url of the image
     */
    function getEmblem($size = null)
    {
        /**
         * @var Action $action
         */
        global $action;
        if ($this->confidential > 0) return $action->parent->getImageLink("confidential.gif", true, $size);
        else if ($this->locked == - 1) return $action->parent->getImageLink("revised.png", true, $size);
        else if ($this->lockdomainid > 0) {
            if ($this->locked > 0) {
                if ((abs($this->locked) == $this->userid)) return $action->parent->getImageLink("lockorange.png", true, $size);
                else return $action->parent->getImageLink("lockred.png", true, $size);
            } else return $action->parent->getImageLink("lockorange.png", true, $size);
        } else if ($this->allocated == $this->userid) return $action->parent->getImageLink("lockblue.png", true, $size);
        else if ((abs($this->locked) == $this->userid)) return $action->parent->getImageLink("lockgreen.png", true, $size);
        else if ($this->locked != 0) return $action->parent->getImageLink("lockred.png", true, $size);
        else if ($this->archiveid != 0) return $action->parent->getImageLink("archive.png", true, $size);
        else if ($this->control("edit") != "") return $action->parent->getImageLink("nowrite.png", true, $size);
        else return $action->parent->getImageLink("1x1.gif");
    }
    /**
     * use only for paramRefresh in attribute definition of a family
     */
    function nothing($a = "", $b = "", $c = "")
    {
        return "";
    }
    /**
     * return application parameter value
     * @deprecated use instead getParam global function or parameterManager
     * @see Doc::parameterManager
     * @param  string  $param parameter
     * @param  string  $defv default return value
     * @return string  returns parameter value ou default value
     */
    final public function getParam($param, $defv = "")
    {
        deprecatedFunction();
        return getParam($param, $defv);
    }
    //----------------------------------------------------------------------
    //   USUAL METHODS USE FOR CALCULATED ATTRIBUTES OR FUNCTION SEARCHES
    //----------------------------------------------------------------------
    // ALL THESE METHODS NAME MUST BEGIN WITH 'GET'
    
    /**
     * return title of document in latest revision
     * @param string $id identifier of document
     * @param string $def default value if document not found
     * @return string
     */
    final public function getLastTitle($id = "-1", $def = "")
    {
        return $this->getTitle($id, $def, true);
    }
    /**
     * return title of document
     * @api get document's title
     * @param string $id identifier of document (if not set use current document)
     * @param string $def default value if document not found
     * @param boolean $latest search title in latest revision
     * @return string
     * @see Doc::getCustomTitle()
     */
    final public function getTitle($id = "-1", $def = "", $latest = false)
    {
        if (is_array($id)) return $def;
        if ($id == "") return $def;
        if ($id == "-1") {
            if ($this->locked != - 1 || (!$latest)) {
                if ($this->isConfidential()) return _("confidential document");
                return $this->getCustomTitle();
            } else {
                // search latest
                $id = $this->getLatestId();
                $lastId = $id;
            }
        }
        if ((strpos($id, "\n") !== false) || (strpos($id, "<BR>") !== false)) {
            $tid = explode("\n", str_replace("<BR>", "\n", $id));
            $ttitle = array();
            foreach ($tid as $idone) {
                $ttitle[] = $this->getTitle($idone, $def, $latest);
            }
            return implode("\n", $ttitle);
        } else {
            if (!is_numeric($id)) $id = getIdFromName($this->dbaccess, $id);
            if ($id > 0) {
                $title = getDocTitle($id, $latest);
                if (!$title) return " "; // delete title
                return $title;
            }
        }
        return $def;
    }
    /**
     * Same as ::getTitle()
     * the < & > characters as replace by entities
     * @param string $id docuemnt identifier to set else use current document title
     * @param string $def default value if document not found
     * @param bool $latest force use latest revision of document
     * @see Doc::getTitle
     * @return string
     */
    function getHTMLTitle($id = "-1", $def = "", $latest = false)
    {
        $t = $this->getTitle($id, $def, $latest);
        return $this->htmlEncode($t);
    }
    /**
     * the < > & characters as replace by entities
     * @static
     * @param $s
     * @return mixed
     */
    public static function htmlEncode($s)
    {
        $s = str_replace("&", "&amp;", $s);
        return str_replace(array(
            "<",
            ">"
        ) , array(
            "&lt;",
            "&gt;"
        ) , $s);
    }
    /**
     * return the today date with european format DD/MM/YYYY
     *
     * @searchLabel today
     * @searchType date
     * @searchType timestamp
     * @api get date
     * @param int $daydelta to have the current date more or less day (-1 means yesterday, 1 tomorrow)
     * @param int|string $dayhour hours of day
     * @param int|string $daymin minutes of day
     * @param bool $getlocale whether to return locale date or not
     * @return string YYYY-MM-DD or DD/MM/YYYY (depend of CORE_LCDATE parameter) or locale dateDD/MM/YYYY or locale date
     */
    public static function getDate($daydelta = 0, $dayhour = "", $daymin = "", $getlocale = false)
    {
        $delta = abs(intval($daydelta));
        if ($daydelta > 0) {
            $nd = strtotime("+$delta day");
        } else if ($daydelta < 0) {
            $nd = strtotime("-$delta day");
        } else {
            $nd = time();
        }
        $isIsoDate = (getLcdate() == "iso");
        if ($dayhour !== "" || $daymin !== "") {
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
                return stringDateToLocaleDate(date("Y-m-d H:i", $nd));
            } else {
                if ($isIsoDate) return date("Y-m-d H:i", $nd);
                else return date("d/m/Y H:i", $nd);
            }
        } else {
            if ($getlocale) {
                return stringDateToLocaleDate(date("Y-m-d", $nd));
            } else {
                if ($isIsoDate) return date("Y-m-d", $nd);
                else return date("d/m/Y", $nd);
            }
        }
    }
    /**
     * return the today date and time with european format DD/MM/YYYY HH:MM
     * @param int $hourdelta to have the current date more or less hour  (-1 means one hour before, 1 one hour after)
     * @param bool $second if true format DD/MM/YYYY HH:MM
     * @return string DD/MM/YYYY HH:MM or YYYY-MM-DD HH:MM (depend of CORE_LCDATE parameter)
     */
    public static function getTimeDate($hourdelta = 0, $second = false)
    {
        $delta = abs(intval($hourdelta));
        if ((getLcdate() == "iso")) {
            if ($second) $format = "Y-m-d H:i:s";
            else $format = "Y-m-d H:i";
        } else {
            if ($second) $format = "d/m/Y H:i:s";
            else $format = "d/m/Y H:i";
        }
        if ($hourdelta > 0) {
            if (is_float($hourdelta)) {
                $dm = intval((abs($hourdelta) - $delta) * 60);
                return date($format, strtotime("+$delta hour $dm minute"));
            } else return date($format, strtotime("+$delta hour"));
        } else if ($hourdelta < 0) {
            if (is_float($hourdelta)) {
                $dm = intval((abs($hourdelta) - $delta) * 60);
                return date($format, strtotime("-$delta hour $dm minute"));
            } else return date($format, strtotime("-$delta hour"));
        }
        return date($format);
    }
    /**
     * Return the related value by linked attributes starting from referenced document.
     *
     * Can be used to retrieve a value by traversing multiple docid.
     *
     * For example,
     * @code
     * $val = $this->getDocValue("id", "id1:id2:id3")
     * @endcode
     * is a shortcut for
     * @code
     * $doc = new_Doc('', "id");
     * $val = $doc->getRValue("id1:id2:id3");
     * @endcode
     *
     * @warning
     * Each of the traversed docid **must** be a docid or an account, and **must not** be multiple.\n
     * Elsewhere, the returned value is $def
     * @endwarning
     * @see Doc::getRValue
     * @param int $docid document identifier
     * @param string $attrid attributes identifier chain (separated by ':')
     * @param string $def $def default return value
     * @param bool $latest always last revision of document
     * @return array|string
     */
    final public function getDocValue($docid, $attrid, $def = " ", $latest = false)
    {
        if ((!is_numeric($docid)) && ($docid != "")) {
            $docid = getIdFromName($this->dbaccess, $docid);
        }
        if (intval($docid) > 0) {
            if (strpos(':', $attrid) === false) {
                $attrid = strtolower($attrid);
                if ($latest) {
                    $rawDoc = getTDoc($this->dbaccess, $docid, array() , array(
                        "initid",
                        "id",
                        "locked",
                        $attrid
                    ));
                    if ($rawDoc["locked"] == - 1) {
                        $docid = getLatestDocId($this->dbaccess, $rawDoc["initid"]);
                        $rawDoc = getTDoc($this->dbaccess, $docid, array() , array(
                            $attrid
                        ));
                    }
                } else {
                    $rawDoc = getTDoc($this->dbaccess, $docid, array() , array(
                        $attrid
                    ));
                }
                if ($rawDoc) {
                    return $rawDoc[$attrid];
                }
            } else {
                $doc = new_Doc($this->dbaccess, $docid);
                if ($doc->isAlive()) {
                    if ($latest && ($doc->locked == - 1)) {
                        $ldocid = $doc->getLatestId();
                        if ($ldocid != $doc->id) $doc = new_Doc($this->dbaccess, $ldocid);
                    }
                    return $doc->getRValue($attrid, $def, $latest);
                }
            }
        }
        return "";
    }
    /**
     * return value of an property for the document referenced
     * @see Doc::getPropertyValue
     * @param int $docid document identifier
     * @param string $propid property identifier
     * @param bool $latest always last revision of document if true
     * @return string
     */
    final public function getDocProp($docid, $propid, $latest = false)
    {
        if (intval($docid) > 0) {
            if ($latest) $tdoc = getTDoc($this->dbaccess, $docid);
            else $tdoc = getLatestTDoc($this->dbaccess, $docid);
            return $tdoc[strtolower($propid) ];
        }
        return "";
    }
    /**
     * return the current user display name
     * @param bool $withfirst if true compose first below last name
     * @return string
     */
    public static function getUserName($withfirst = false)
    {
        global $action;
        if ($withfirst) return $action->user->firstname . " " . $action->user->lastname;
        return $action->user->lastname;
    }
    /**
     * return the user document identifier associated to the current account
     * @return int
     */
    public static function userDocId()
    {
        global $action;
        
        return $action->user->fid;
    }
    /**
     * alias for Doc::userDocId
     * @searchLabel My user account id
     * @searchType account
     * @searchType docid("IUSER")
     *
     * @return int
     */
    public static function getUserId()
    {
        return Doc::userDocId();
    }
    /**
     * return system user id
     * @deprecated use getSystemUserId instead
     * @return int
     */
    public static function getWhatUserId()
    {
        global $action;
        deprecatedFunction();
        
        return $action->user->id;
    }
    /**
     * return system user id
     * @searchLabel My system user id
     * @searchType uid
     * @return int
     */
    public static function getSystemUserId()
    {
        global $action;
        return $action->user->id;
    }
    /**
     * return a specific attribute of the current user document
     * @searchLabel account attribute
     * @return int
     */
    final public function getMyAttribute($idattr)
    {
        $mydoc = new_Doc($this->dbaccess, $this->getUserId());
        
        return $mydoc->getRawValue($idattr);
    }
    /**
     * concatenate and format string
     * to be use in computed attribute
     * @param string $fmt like sprintf format
     * @internal param string $extra parameters of string composition
     * @return string the composed string
     */
    function formatString($fmt)
    {
        $nargs = func_num_args();
        
        if ($nargs < 1) return "";
        $fmt = func_get_arg(0);
        $sp = array();
        for ($ip = 1; $ip < $nargs; $ip++) {
            $vip = func_get_arg($ip);
            if (gettype($vip) != "array") {
                $sp[] = $vip;
            }
        }
        $r = vsprintf($fmt, $sp);
        return $r;
    }
    /**
     * update internal vault index relation table
     */
    public function updateVaultIndex()
    {
        if (empty($this->id)) return;
        $dvi = new DocVaultIndex($this->dbaccess);
        $err = $dvi->DeleteDoc($this->id);
        $fa = $this->GetFileAttributes();
        
        $tvid = array();
        foreach ($fa as $aid => $oattr) {
            if ($oattr->inArray()) {
                $ta = $this->getMultipleRawValues($aid);
            } else {
                $ta = array(
                    $this->getRawValue($aid)
                );
            }
            foreach ($ta as $k => $v) {
                $vid = "";
                if (preg_match(PREGEXPFILE, $v, $reg)) {
                    $vid = $reg[2];
                    $tvid[$vid] = $vid;
                }
            }
        }
        
        foreach ($tvid as $vid) {
            if ($vid > 0) {
                $dvi->docid = $this->id;
                $dvi->vaultid = $vid;
                $dvi->Add();
            }
        }
    }
    // ===================
    // Timer Part
    
    /**
     * attach timer to a document
     * @param \Dcp\Family\TIMER &$timer the timer document
     * @param Doc &$origin the document which comes from the attachement
     * @param string $execdate date to execute first action YYYY-MM-DD HH:MM:SS
     * @return string error - empty if no error -
     */
    final public function attachTimer(&$timer, &$origin = null, $execdate = null)
    {
        $dyn = false;
        if ($execdate == null) {
            $dyn = trim(strtok($timer->getRawValue("tm_dyndate") , " "));
            if ($dyn) $execdate = $this->getRawValue($dyn);
        }
        if (method_exists($timer, 'attachDocument')) {
            $err = $timer->attachDocument($this, $origin, $execdate);
            if ($err == "") {
                if ($dyn) $this->addATag("DYNTIMER");
                $this->addHistoryEntry(sprintf(_("attach timer %s [%d]") , $timer->title, $timer->id) , DocHisto::NOTICE);
                $this->addLog("attachtimer", array(
                    "timer" => $timer->id
                ));
            }
        } else {
            $err = sprintf(_("attachTimer : the timer parameter is not a document of TIMER family"));
        }
        return $err;
    }
    /**
     * unattach timer to a document
     * @param \Dcp\Family\TIMER &$timer the timer document
     * @return string error - empty if no error -
     */
    final public function unattachTimer(&$timer)
    {
        if (method_exists($timer, 'unattachDocument')) {
            $err = $timer->unattachDocument($this);
            if ($err == "") {
                $this->addHistoryEntry(sprintf(_("unattach timer %s [%d]") , $timer->title, $timer->id) , DocHisto::NOTICE);
                $this->addLog("unattachtimer", array(
                    "timer" => $timer->id
                ));
            }
        } else $err = sprintf(_("unattachTimer : the timer parameter is not a document of TIMER family"));
        return $err;
    }
    
    final public function resetDynamicTimers()
    {
        $tms = $this->getAttachedTimers();
        if (count($tms) == 0) {
            $this->delATag("DYNTIMER");
        } else {
            foreach ($tms as $k => $v) {
                $t = new_doc($this->dbaccess, $v["timerid"]);
                if ($t->isAlive()) {
                    $dynDateAttr = trim(strtok($t->getRawValue("tm_dyndate") , " "));
                    if ($dynDateAttr) {
                        $execdate = $this->getRawValue($dynDateAttr);
                        $previousExecdate = $this->getOldRawValue($dynDateAttr);
                        // detect if need reset timer : when date has changed
                        if ($previousExecdate && ($execdate != $previousExecdate)) {
                            if ($v["originid"]) $ori = new_doc($this->dbaccess, $v["originid"]);
                            else $ori = null;
                            $this->unattachTimer($t);
                            $this->attachTimer($t, $ori);
                        }
                    }
                } else {
                    $this->unattachTimer($t);
                }
            }
        }
    }
    /**
     * unattach several timers to a document
     * @param Doc &$origin if set unattach all timer which comes from this origin
     * @return string error - empty if no error -
     */
    final public function unattachAllTimers(&$origin = null)
    {
        /**
         * @var \Dcp\Family\TIMER $timer
         */
        $timer = createTmpDoc($this->dbaccess, "TIMER");
        $c = 0;
        $err = $timer->unattachAllDocument($this, $origin, $c);
        if ($err == "" && $c > 0) {
            if ($origin) $this->addHistoryEntry(sprintf(_("unattach %d timers associated to %s") , $c, $origin->title) , DocHisto::NOTICE);
            else $this->addHistoryEntry(sprintf(_("unattach all timers [%s]") , $c) , DocHisto::NOTICE);
            $this->addLog("unattachtimer", array(
                "timer" => "all",
                "number" => $c
            ));
        }
        return $err;
    }
    /**
     * return all activated document timer
     * @return array of doctimer values
     */
    final public function getAttachedTimers()
    {
        include_once ("Class.QueryDb.php");
        include_once ("Class.DocTimer.php");
        $q = new QueryDb($this->dbaccess, "doctimer");
        $q->AddQuery("docid=" . $this->initid);
        $q->AddQuery("donedate is null");
        $l = $q->Query(0, 0, "TABLE");
        
        if (is_array($l)) return $l;
        return array();
    }
    /**
     * get all domains where document is attached by current user
     * @param boolean $user is set to false list all domains (independant of current user)
     * @param boolean $folderName is set to true append also folder name
     * @return array id
     */
    public function getDomainIds($user = true, $folderName = false)
    {
        if (file_exists("OFFLINE/Class.DomainManager.php")) {
            include_once ("FDL/Class.SearchDoc.php");
            $s = new searchDoc($this->dbaccess, "OFFLINEFOLDER");
            $s->join("id = fld(dirid)");
            $s->addFilter("fld.childid = %d", $this->initid);
            $uid = $this->getUserId();
            if ($user) $s->addFilter("off_user = '%d' or off_user is null", $uid);
            $s->overrideViewControl();
            $t = $s->search();
            $ids = array();
            foreach ($t as $v) {
                $ids[] = $v['off_domain'];
                if ($folderName && ((!$user) || ($v['off_user'] == $uid))) {
                    $ids[] = $v["name"];
                }
            }
            return array_unique($ids);
        }
        return null;
    }
    /**
     * attach lock to specific domain.
     * @param int $domainId domain identifier
     * @param int $userid system user's id
     * @return string error message
     */
    public function lockToDomain($domainId, $userid = 0)
    {
        $err = '';
        if (!$userid) $userid = $this->userid;
        
        if ($domainId != '') {
            /*
             * Memorize current core lock and lock document
            */
            if ($this->lockdomainid != $domainId) {
                /*
                 * Memorize current core lock if lockdomain changes
                */
                $this->addUTag(1, 'LOCKTODOMAIN_LOCKED', $this->locked);
            }
            $err = $this->lock(false, $userid);
            if ($err != '') {
                return $err;
            }
        } else {
            /*
             * Restore core lock
            */
            $tag = $this->getUTag('LOCKTODOMAIN_LOCKED', true, 1);
            if ($tag !== false) {
                $this->delUTag(1, 'LOCKTODOMAIN_LOCKED');
                $this->locked = $tag->comment;
                $err = $this->modify(true, array(
                    "locked"
                ) , true);
                if ($err != '') {
                    return $err;
                }
            }
        }
        /*
         * Set or remove domain's lock
        */
        $this->lockdomainid = $domainId;
        $err = $this->modify(true, array(
            "lockdomainid"
        ) , true);
        return $err;
    }
    /**
     * return folder where document is set into
     * @return array of folder identifiers
     */
    public function getParentFolderIds()
    {
        $fldids = array();
        $err = simpleQuery($this->dbaccess, sprintf("select dirid from fld where qtype='S' and childid=%d", $this->initid) , $fldids, true, false);
        return $fldids;
    }
    /**
     * update Domain list
     */
    public function updateDomains()
    {
        $domains = $this->getDomainIds(false, true);
        //delete domain lock if is not in the list
        $this->domainid = trim($this->arrayToRawValue($domains));
        if ($this->lockdomainid) {
            if (!in_array($this->lockdomainid, $domains)) $this->lockdomainid = '';
            else {
                if ($this->locked > 0) {
                    $err = simpleQuery($this->dbaccess, sprintf("select id from users where id=%d", $this->locked) , $lockUserId, true, true);
                    
                    if ($lockUserId && (!$this->isInDomain(true, $lockUserId))) {
                        $this->lockdomainid = '';
                    }
                }
            }
        }
        
        $this->modify(true, array(
            "domainid",
            "lockdomainid"
        ) , true);
    }
    /**
     * verify is doc is set in a domain
     * @param boolean $user limit domains where user as set document
     * @param string $userId another user's id else current user
     * @return bool
     */
    public function isInDomain($user = true, $userId = '')
    {
        if ($user) {
            global $action;
            if (!$userId) $userId = $action->user->id;
            if (preg_match('/_' . $userId . '$/m', $this->domainid)) return true;
            return false;
        } else {
            return (!empty($this->domainid));
        }
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
     * @return bool|array false on error or an array containing the components
     */
    static public function parseZone($zone)
    {
        $p = array();
        // Separate layout (left) from args (right)
        $split = preg_split('/\?/', $zone, 2);
        $left = $split[0];
        if (count($split) > 1) $right = $split[1];
        else $right = '';
        // Check that the layout part has al least 2 elements
        $el = preg_split('/:/', $left);
        if (count($el) < 2) {
            return false;
        }
        $p['fulllayout'] = $left;
        $p['index'] = - 1;
        // Parse args into argv (k => v)
        if ($right != "") {
            $p['args'] = $right;
            $argList = preg_split('/&/', $p['args']);
            $p['argv'] = array();
            foreach ($argList as $arg) {
                $split = preg_split('/=/', $arg, 2);
                $left = urldecode($split[0]);
                $right = urldecode($split[1]);
                $p['argv'][$left] = $right;
            }
        }
        // Parse layout
        $parts = array(
            0 => 'app',
            1 => 'layout',
            2 => 'modifier',
            3 => 'transform'
        );
        foreach ($parts as $aPart) $p[$aPart] = null;
        $match = array();
        $i = 0;
        while ($i < count($el)) {
            if (!array_key_exists($i, $parts)) {
                error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Unexpected part '%s' in zone '%s'.", $el[$i], $zone));
                return false;
            }
            // Extract index from 'layout' part if present
            if ($i == 1 && preg_match("/^(?P<name>.*?)\[(?P<index>-?\d)\]$/", $el[$i], $match)) {
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
    /**
     * Get the helppage document associated to the document family.
     * @param string $fromid get the helppage for this family id (default is the family of the current document)
     * @return \Dcp\Family\HELPPAGE the helppage document on success, or a non-alive document if no helppage is associated with the family
     */
    public function getHelpPage($fromid = "")
    {
        if ($fromid === "") {
            $fromid = $this->fromid;
        }
        $s = new SearchDoc($this->dbaccess, "HELPPAGE");
        $s->addFilter("help_family='%d'", $fromid);
        $help = $s->search();
        $helpId = "";
        if ($s->count() > 0) {
            $helpId = $help[0]["id"];
        }
        return new_Doc($this->dbaccess, $helpId);
    }
    /**
     * Get the list of compatible search methods for a given attribute type
     * @param string $attrId attribute name
     * @param string $attrType empty string to returns all methods or attribute type (e.g. 'date', 'docid', 'docid("IUSER")', etc.) to restrict search to methods supporting this type
     * @return array list of array('method' => '::foo()', 'label' => 'Foo Bar Baz')
     */
    public function getSearchMethods($attrId, $attrType = '')
    {
        include_once ('FDL/Lib.Attr.php');
        /**
         * @var Action $action
         */
        global $action;
        // Strip format strings for non-docid types
        $pType = parseType($attrType);
        if ($pType['type'] != 'docid') {
            $attrType = $pType['type'];
        }
        
        $collator = new Collator($action->GetParam('CORE_LANG', 'fr_FR'));
        
        $compatibleMethods = array();
        
        if ($attrType == 'date' || $attrType == 'timestamp') {
            $compatibleMethods = array_merge($compatibleMethods, array(
                array(
                    'label' => _("yesterday") ,
                    'method' => '::getDate(-1)'
                ) ,
                array(
                    'label' => _("tomorrow") ,
                    'method' => '::getDate(1)'
                )
            ));
        }
        
        try {
            $rc = new ReflectionClass(get_class($this));
        }
        catch(Exception $e) {
            return $compatibleMethods;
        }
        
        $methods = array_filter($rc->getMethods() , function ($aMethod)
        {
            /**
             * @var ReflectionMethod $aMethod
             */
            $methodName = $aMethod->getName();
            return ($aMethod->isPublic() && $methodName != '__construct');
        });
        /**
         * @var ReflectionMethod[] $methods
         */
        foreach ($methods as $method) {
            $tags = self::getDocCommentTags($method->getDocComment());
            
            $searchLabel = null;
            $searchTypes = array();
            
            foreach ($tags as $tag) {
                if ($tag['name'] == 'searchLabel') {
                    $searchLabel = $tag['value'];
                } elseif ($tag['name'] == 'searchType') {
                    $searchTypes[] = $tag['value'];
                }
            }
            
            if ($searchLabel === null) {
                continue;
            }
            
            if ($attrType == '' || in_array($attrType, $searchTypes)) {
                $compatibleMethods[] = array(
                    'label' => _($searchLabel) ,
                    'method' => sprintf('::%s()', $method->getName())
                );
            }
        }
        
        usort($compatibleMethods, function ($a, $b) use ($collator)
        {
            /**
             * @var Collator $collator
             */
            return $collator->compare($a['label'], $b['label']);
        });
        
        return $compatibleMethods;
    }
    /**
     * Check if a specific method from a specific class is a valid search method
     *
     * @param string|object $className the class name
     * @param string $methodName the method name
     * @return bool boolean 'true' if valid, boolean 'false' is not valid
     */
    public function isValidSearchMethod($className, $methodName)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }
        try {
            $rc = new ReflectionClass($className);
            $method = $rc->getMethod($methodName);
            $tags = self::getDocCommentTags($method->getDocComment());
            
            foreach ($tags as $tag) {
                if ($tag['name'] == 'searchLabel') {
                    return true;
                }
            }
        }
        catch(Exception $e) {
            return false;
        }
        return false;
    }
    /**
     * Extract tags names/values from methods doc comments text
     * @static
     * @param string $docComment the doc comment text
     * @return array|null list of array('name' => $tagName, 'value' => $tagValue)
     */
    final private static function getDocCommentTags($docComment = '')
    {
        if (!preg_match_all('/^.*?@(?P<name>[a-zA-Z0-9_-]+)\s+(?P<value>.*?)\s*$/m', $docComment, $tags, PREG_SET_ORDER)) {
            return array();
        }
        $tags = array_map(function ($tag)
        {
            return array(
                'name' => $tag['name'],
                'value' => $tag['value']
            );
        }
        , $tags);
        return $tags;
    }
}

