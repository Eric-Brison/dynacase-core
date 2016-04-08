<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Search Account : User / Group / Role
 *
 * @author Anakeen
 * @package FDL
 */

include_once ("FDL/Lib.Dir.php");
/**
 * @class SearchAccount
 * @code
 $s = new SearchAccount();
 $s->addRoleFilter($s->getLoginFromDocName('TST_ROLEWRITTER'));
 $s->addGroupFilter("all");
 $s->addFilter("mail ~ '%s'", "test");
 $al = $s->search();
 foreach ($al as $account) {
 printf("%s => %s\n ", $account->login, $account->mail);
 }
 * @endcode
 */
class SearchAccount
{
    /**
     * user type filter
     */
    const userType = 0x01;
    /**
     * group type filter
     */
    const groupType = 0x02;
    /**
     * role type filter
     */
    const roleType = 0x04;
    /**
     * AccountList type return
     */
    const returnAccount = 1;
    /**
     * DocumentList type return
     */
    const returnDocument = 2;
    
    private $returnType = self::returnAccount;
    private $roleFilters = array();
    private $groupFilters = array();
    private $searchResult = array();
    private $dbaccess;
    private $filters = array();
    private $order = 'login';
    private $slice = 'ALL';
    private $start = 0;
    private $familyFilter = null;
    
    private $returnUser = true;
    private $returnGroup = true;
    private $returnRole = true;
    private $viewControl = false;
    
    public function __construct()
    {
        $this->dbaccess = getDbAccess();
    }
    /**
     * add role filter appartenance
     * @api add role filter appartenance
     * @param string $role role reference (login)
     * @throws Dcp\Sacc\Exception
     */
    public function addRoleFilter($role)
    {
        $roles = explode(' ', $role);
        foreach ($roles as $aRole) {
            $aRole = trim($aRole);
            if ($aRole) {
                $sql = sprintf("select id from users where accounttype='R' and login='%s'", pg_escape_string(mb_strtolower($aRole)));
                simpleQuery($this->dbaccess, $sql, $result, true, true);
                if (!$result) {
                    throw new Dcp\Sacc\Exception(ErrorCode::getError("SACC0002", $aRole));
                }
                $this->roleFilters[] = $result;
            }
        }
    }
    /**
     * add group filter appartenance
     * @api add group filter appartenance
     * @param string $group group name (login)
     * @throws Dcp\Sacc\Exception
     */
    public function addGroupFilter($group)
    {
        $groups = explode(' ', $group);
        foreach ($groups as $aGroup) {
            $aGroup = trim($aGroup);
            if ($aGroup) {
                $sql = sprintf("select id from users where accounttype='G' and login='%s'", pg_escape_string(mb_strtolower($aGroup)));
                simpleQuery($this->dbaccess, $sql, $result, true, true);
                if (!$result) {
                    throw new Dcp\Sacc\Exception(ErrorCode::getError("SACC0005", $aGroup));
                }
                $this->groupFilters[] = $result;
            }
        }
    }
    /**
     * set account type filter (only matching accounts will be returned)
     * @api set account type filter (only matching accounts will be returned)
     * @code
     * $s->setTypeFilter($s::userType | $s::groupType);
     * @endcode
     * @param int $type can be bitmask of SearchAccount::userType, SearchAccount::groupType,SearchAccount::roleType
     */
    public function setTypeFilter($type)
    {
        
        $this->returnUser = ($type & self::userType) == self::userType;
        $this->returnGroup = ($type & self::groupType) == self::groupType;
        $this->returnRole = ($type & self::roleType) == self::roleType;
    }
    /**
     * add sql filter about Account properties
     * @api add sql filter about Account properties
     * @code
     * $s->addFilter("mail ~ '%s'", $mailExpr);
     * @endcode
     * @param string $filter sql filter
     * @param string $arg optional arguments
     */
    public function addFilter($filter, $arg = null)
    {
        if ($filter != "") {
            $args = func_get_args();
            if (count($args) > 1) {
                $fs[0] = $args[0];
                for ($i = 1; $i < count($args); $i++) {
                    $fs[] = pg_escape_string($args[$i]);
                }
                $filter = call_user_func_array("sprintf", $fs);
            }
            
            $this->filters[] = $filter;
        }
    }
    /**
     * set order can be login, mail, id, firstname,… each Account properties
     * @api set order can be login, mail, id, firstname,… each Account properties
     * @param string $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }
    /**
     * set slice limit / "all" for no limit
     * @api set slice limit / "all" for no limit
     * @param int|string $slice
     * @throws Dcp\Sacc\Exception
     */
    public function setSlice($slice)
    {
        if (((!is_numeric($slice)) && (strtolower($slice) != 'all')) || ($slice < 0)) {
            throw new Dcp\Sacc\Exception(ErrorCode::getError("SACC0003", $slice));
        }
        if (is_numeric($slice)) $this->slice = intval($slice);
        else $this->slice = $slice;
    }
    /**
     * set start offset
     * @api set start offset
     * @param int $start
     * @throws Dcp\Sacc\Exception
     */
    public function setStart($start)
    {
        if ((!is_numeric($start)) || ($start < 0)) {
            throw new Dcp\Sacc\Exception(ErrorCode::getError("SACC0004", $start));
        }
        $this->start = intval($start);
    }

    /**
     * set if use view control document's privilege to filter account
     *
     * @deprecated use {@link SearchAccount::overrideViewControl} instead. Be carefull: this is the opposite!
     * @see SearchAccount::overrideViewControl
     *
     * @param bool $control
     */
    public function useViewControl($control = true)
    {
        deprecatedFunction();
        $this->overrideViewControl(!$control);
    }

    /**
     * include accounts the user cannot view
     * @api include accounts the user cannot view
     * @param bool $override
     */
    public function overrideViewControl($override = true)
    {
        $this->viewControl = !$override;
    }

    /**
     * set object type return by ::search method
     *
     * @deprecated use {@link SearchAccount::setReturnType} instead
     * @see SearchAccount::setReturnType
     *
     * @param string $type self::returnDocument or self::returnAccount
     * @throws Dcp\Sacc\Exception
     */
    public function setObjectReturn($type)
    {
        if ($type != self::returnAccount && $type != self::returnDocument) {
            throw new Dcp\Sacc\Exception(ErrorCode::getError("SACC0001", $type));
        }
        $this->returnType = $type;
    }
    /**
     * set object type return by ::search method
     *
     * @api set object type return by ::search method
     *
     * @param string $type self::returnDocument or self::returnAccount
     * @throws Dcp\Sacc\Exception
     */
    public function setReturnType($type)
    {
        if ($type != self::returnAccount && $type != self::returnDocument) {
            throw new Dcp\Sacc\Exception(ErrorCode::getError("SACC0001", $type));
        }
        $this->returnType = $type;
    }

    /**
     * convert logical name document to login account
     *
     * @static
     * @deprecated use {@link SearchAccount::getLoginFromDocName} instead
     * @see SearchAccount::getLoginFromDocName
     *
     * @param string $name lolgical name
     * @return string login , null if not found
     */
    public static function docName2login($name)
    {
        deprecatedFunction();
        return self::getLoginFromDocName($name);
    }
    /**
     * get login account from logical name document
     *
     * @static
     * @api get login account from logical name document
     *
     * @param string $name logical name
     * @return string|bool login , false if not found
     */
    public static function getLoginFromDocName($name)
    {
        $sql = sprintf("select login from docname, users where docname.id = users.fid and docname.name='%s'", pg_escape_string($name));
        simpleQuery('', $sql, $login, true, true);
        return $login;
    }

    /**
     * @param string $family
     * @throws Dcp\Sacc\Exception if $family is not a valid family name
     */
    public function filterFamily($family)
    {
        if (!is_numeric($family)) {
            $famId = getFamIdFromName($this->dbaccess, $family);
            if (!$famId) throw new Dcp\Sacc\Exception(ErrorCode::getError("SACC0006", $family));
            $this->familyFilter = $famId;
        } else {
            $this->familyFilter = $family;
        }
    }
    /**
     * send search of account's object
     * @api send search of account's object
     * @return DocumentList|AccountList
     */
    public function search()
    {
        simpleQuery($this->dbaccess, $this->getQuery() , $this->searchResult);
        if ($this->returnType == self::returnAccount) {
            $al = new AccountList($this->searchResult);
            return $al;
        } else {
            $ids = array();
            foreach ($this->searchResult as $account) {
                if ($account["fid"]) $ids[] = $account["fid"];
            }
            $dl = new DocumentList();
            
            $dl->addDocumentIdentifiers($ids);
            return $dl;
        }
    }
    /**
     * get sql par to filter group or role
     * @return string
     */
    private function getgroupRoleFilter()
    {
        $rids = array_merge($this->roleFilters, $this->groupFilters);
        if ($rids) {
            $filter = sprintf("memberof && '{%s}'", implode(',', $rids));
            return $filter;
        } else {
            return "true";
        }
    }
    /**
     * get final query to search accounts
     * @return string
     */
    public function getQuery()
    {
        
        $groupRoleFilter = $this->getgroupRoleFilter();
        
        $u = getCurrentUser();
        if ($this->viewControl && $u->id != 1) {
            $viewVector = SearchDoc::getUserViewVector($u->id);
            if ($this->familyFilter) {
                $table = "doc" . $this->familyFilter;
                $sql = sprintf("select users.* from users, $table where users.fid = $table.id and $table.views && '%s' and %s ", $viewVector, $groupRoleFilter);
            } else {
                $sql = sprintf("select users.* from users, docread where users.fid = docread.id and docread.views && '%s' and %s ", $viewVector, $groupRoleFilter);
            }
        } else {
            if ($this->familyFilter) {
                $table = "doc" . $this->familyFilter;
                $sql = sprintf("select users.* from users, $table where users.fid = $table.id  and %s ", $groupRoleFilter);
            } else {
                $sql = sprintf("select * from users where %s ", $groupRoleFilter);
            }
        }
        foreach ($this->filters as $aFilter) {
            $sql.= sprintf(" and (%s) ", $aFilter);
        }
        
        if ((!$this->returnUser) || (!$this->returnGroup) || (!$this->returnRole)) {
            $fa = array();
            if ($this->returnUser) $fa[] = "accounttype='U'";
            if ($this->returnGroup) $fa[] = "accounttype='G'";
            if ($this->returnRole) $fa[] = "accounttype='R'";
            if ($fa) $sql.= sprintf(" and (%s)", implode(' or ', $fa));
        }
        
        if ($this->order) $sql.= sprintf(" order by %s", pg_escape_string($this->order));
        $sql.= sprintf(" offset %d limit %s", $this->start, pg_escape_string($this->slice));
        
        return $sql;
    }
}
?>