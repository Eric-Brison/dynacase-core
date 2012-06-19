<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Search Account : User / Group / Role
 *
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

include_once ("FDL/Lib.Dir.php");
/**
 * @class SearchAccount
 * @code
 $s = new SearchAccount();
 $s->addRoleFilter($s->docName2login('TST_ROLEWRITTER'));
 $s->addGroupFilter("all");
 $s->addFilter("mail ~ '%s'", "test);
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
     * @param string $role role reference (login)
     * @throws Exception
     */
    public function addRoleFilter($role)
    {
        $roles = explode(' ', $role);
        foreach ($roles as $aRole) {
            $aRole = trim($aRole);
            $sql = sprintf("select id from users where accounttype='R' and login='%s'", pg_escape_string(mb_strtolower($aRole)));
            simpleQuery($this->dbaccess, $sql, $result, true, true);
            if (!$result) {
                throw new Exception(ErrorCode::getError("SACC0002", $aRole));
            }
            $this->roleFilters[] = $result;
        }
    }
    /**
     * add group filter appartenance
     * @param string $group group name (login)
     * @throws Exception
     */
    public function addGroupFilter($group)
    {
        $groups = explode(' ', $group);
        foreach ($groups as $aGroup) {
            $aGroup = trim($aGroup);
            $sql = sprintf("select id from users where accounttype='G' and login='%s'", pg_escape_string(mb_strtolower($aGroup)));
            simpleQuery($this->dbaccess, $sql, $result, true, true);
            if (!$result) {
                throw new Exception(ErrorCode::getError("SACC0005", $aGroup));
            }
            $this->groupFilters[] = $result;
        }
    }
    /**
     * set account type filter
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
     * @code
     * $s->addFilter("mail ~ '%s'", $mailExpr);
     * @endcode
     * @param string $filter sql filter
     * @param string $arg optionnal arguments
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
     * set order can be login, mail, id, firstname,... each Account properties
     * @param string $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }
    /**
     * set slice limit / "all" if no limit
     * @param int|string $slice
     * @throws Exception
     */
    public function setSlice($slice)
    {
        if (((!is_numeric($slice)) && (strtolower($slice) != 'all')) || ($slice < 0)) {
            throw new Exception(ErrorCode::getError("SACC0003", $slice));
        }
        if (is_numeric($slice)) $this->slice = intval($slice);
        else $this->slice = $slice;
    }
    /**
     * set start offset
     * @param int $start
     * @throws Exception
     */
    public function setStart($start)
    {
        if ((!is_numeric($start)) || ($start < 0)) {
            throw new Exception(ErrorCode::getError("SACC0004", $start));
        }
        $this->start = intval($start);
    }
    /**
     * set if use view control document's privilege to filter account
     * @param bool $control
     */
    public function useViewControl($control = true)
    {
        $this->viewControl = $control;
    }
    /**
     * set object type return by ::search method
     * @param string $type self::returnDocument or self::returnAccount
     * @throws Exception
     */
    public function setObjectReturn($type)
    {
        if ($type != self::returnAccount && $type != self::returnDocument) {
            throw new Exception(ErrorCode::getError("SACC0001", $type));
        }
        $this->returnType = $type;
    }
    /**
     * convert logical name document to login account
     * @static
     * @param string $name lolgical name
     * @return string login , null if not found
     */
    public static function docName2login($name)
    {
        $sql = sprintf("select login from docname, users where docname.id = users.fid and docname.name='%s'", pg_escape_string($name));
        simpleQuery('', $sql, $login, true, true);
        return $login;
    }
    /**
     * send search of account's object
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
            
            $dl->addDocumentIdentificators($ids);
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
    private function getQuery()
    {
        
        $groupRoleFilter = $this->getgroupRoleFilter();

        $u = getCurrentUser();
        if ($this->viewControl && $u->id!=1) {
            $viewVector = SearchDoc::getUserViewVector($u->id);
            $sql = sprintf("select users.* from users, docread where users.fid = docread.id and docread.views && '%s' and %s ", $viewVector, $groupRoleFilter);
        } else {
            $sql = sprintf("select * from users where %s ", $groupRoleFilter);
        }
        foreach ($this->filters as $aFilter) {
            $sql.= sprintf(" and (%s) ", $aFilter);
        }
        
        if ((!$this->returnUser) || (!$this->returnGroup) || (!$this->returnGroup)) {
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