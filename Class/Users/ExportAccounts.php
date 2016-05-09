<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Core;

class ExportAccounts
{
    /**
     * @var \SearchAccount
     */
    protected $search = null;
    /**
     * @var \DOMElement
     */
    private $rootNode;
    /**
     * @var \DOMElement
     */
    private $roleRootNode;
    /**
     * @var \DOMElement
     */
    private $groupRootNode;
    /**
     * @var \DOMElement
     */
    private $userRootNode;
    /**
     * @var \DOMDocument
     */
    protected $xml;
    /**
     * @var \Account
     */
    private $workAccount;
    
    protected $sessionKey = '';
    protected $exportCryptedPassword = false;
    /**
     * @var \DocFam[]
     */
    private $families = array();
    
    const ABORTORDER = "::ABORT::";
    const XSDDIR = "XSD";
    protected $exportGroupParent = false;
    protected $exportRoleParent = false;
    protected $exportDocument = false;
    protected $exportSchemaDirectory = "";
    protected $aborted = false;
    
    protected $documentInfo = array();
    private $addedIds = array();
    private $schemaWritted = array();
    /**
     * Define accounts to exports
     * @param \SearchAccount $s
     */
    public function setSearchAccount(\SearchAccount $s)
    {
        $this->search = $s;
    }
    /**
     * Return account exportation based on SearchAccount object
     * @return string the XML result
     * @throws Exception
     */
    public function export()
    {
        if (!$this->search) {
            throw new Exception("ACCT0101");
        }
        $this->workAccount = new \Account();
        $this->initXml();
        $this->setSessionMessage(___("Retrieve Account data", "fuserexport"));
        
        try {
            $accounts = $this->search->search();
            
            $count = $accounts->length;
            $k = 0;
            foreach ($accounts as $account) {
                $k++;
                $this->setSessionMessage(sprintf(___("Export accounts (%d/%d)", "fuserexport") , $k, $count));
                switch ($account->accounttype) {
                    case \Account::USER_TYPE:
                        $this->addUserAccount($account);
                        break;

                    case \Account::GROUP_TYPE:
                        $this->addGroupAccount($account);
                        break;

                    case \Account::ROLE_TYPE:
                        $this->addRoleAccount($account);
                        break;

                    default:
                        throw new Exception("ACCT0100", $account->login, $account->id);
                }
            }
            $this->addDocumentNodes();
            $this->clearInfo();
            $this->reorderGroups();
        }
        catch(Exception $e) {
            if ($e->getDcpCode() === "ACCT0205") {
                $this->aborted = true;
                $this->rootNode->setAttribute("aborted", "true");
                $this->setSessionMessage(___("Export Aborted", "fuserexport"));
            } else {
                $this->setSessionMessage("::END::");
                throw $e;
            }
        }
        $this->setSessionMessage("::END::");
        return $this->xml->saveXML();
    }
    /**
     * @param string $exportSchemaDirectory
     * @throws Exception
     */
    public function setExportSchemaDirectory($exportSchemaDirectory)
    {
        if (!is_dir($exportSchemaDirectory)) {
            throw new Exception("ACCT0207", $exportSchemaDirectory);
        }
        if (!is_writable($exportSchemaDirectory)) {
            throw new Exception("ACCT0208", $exportSchemaDirectory);
        }
        $this->exportSchemaDirectory = $exportSchemaDirectory . "/" . self::XSDDIR;
        
        if (!is_dir($this->exportSchemaDirectory)) {
            mkdir($this->exportSchemaDirectory);
        }
    }
    /**
     * @param string $sessionKey
     */
    public function setSessionKey($sessionKey)
    {
        $this->sessionKey = $sessionKey;
    }
    /**
     * Abort current export session
     */
    public function abortSession()
    {
        if ($this->sessionKey) {
            global $action;
            $action->session->register($this->sessionKey . "::ABORT", self::ABORTORDER);
        }
    }
    /**
     * @return boolean
     */
    public function isAborted()
    {
        return $this->aborted;
    }
    protected function setSessionMessage($text)
    {
        if ($this->sessionKey) {
            global $action;
            $action->session->register($this->sessionKey, $text);
            $msg = $action->session->read($this->sessionKey . "::ABORT");
            if ($msg === self::ABORTORDER) {
                $action->session->register($this->sessionKey . "::ABORT", "CATCHED");
                throw new Exception("ACCT0205");
            }
        }
    }
    
    public function getSessionMessage()
    {
        if ($this->sessionKey) {
            global $action;
            return $action->session->read($this->sessionKey);
        }
        return null;
    }
    /**
     * Reorder group bu depth to avoid unreferenced groups
     * @throws \Dcp\Db\Exception
     */
    protected function reorderGroups()
    {
        $xpath = new \DOMXpath($this->xml);
        $groups = $xpath->query('//accounts/groups/group/reference');
        
        $groupLogins = array();
        /**
         * @var \DOMElement $loginNode
         */
        foreach ($groups as $loginNode) {
            $groupLogins[] = pg_escape_string($loginNode->nodeValue);
        }
        if (count($groupLogins) > 0) {
            // Get all members of every exported groups
            $sql = sprintf("select memberof,id from users where login in (%s)", implode(array_map(function ($s)
            {
                return pg_escape_literal($s);
            }
            , $groupLogins) , ", "));
            simpleQuery("", $sql, $members);
            $searchGroups = array();
            foreach ($members as $parents) {
                $memberOf = explode(',', substr($parents["memberof"], 1, -1));
                $memberOf[] = $parents["id"];
                $searchGroups = array_merge($searchGroups, $memberOf);
            }
            $searchGroups = array_unique($searchGroups);
            $searchGroups = array_filter($searchGroups, function ($x)
            {
                return !empty($x);
            });
            if ($searchGroups) {
                // Get tree group information
                $sql = sprintf("select groups.iduser as groupid, groups.idgroup as parentid, users.login as grouplogin from groups, users where groups.iduser in (%s) and groups.iduser=users.id and users.accounttype='G'", implode(array_map(function ($s)
                {
                    return pg_escape_literal($s);
                }
                , $searchGroups) , ", "));
                simpleQuery("", $sql, $groupTree);
                
                if ($groupTree) {
                    foreach ($groupTree as & $groupItem) {
                        $groupItem["groupid"] = intval($groupItem["groupid"]);
                        $groupItem["parentid"] = intval($groupItem["parentid"]);
                    }
                    // Compute level depth for each group
                    $groupOrdered = array();
                    foreach ($groupLogins as $groupRef) {
                        $groupOrdered[] = array(
                            "reference" => $groupRef,
                            "order" => $this->getDepthLevel($groupRef, $groupTree)
                        );
                    }
                    // sort by  order
                    usort($groupOrdered, function ($a, $b)
                    {
                        if ($a["order"] > $b["order"]) return +1;
                        elseif ($a["order"] < $b["order"]) return -1;
                        return 0;
                    });
                    
                    foreach ($groupOrdered as $group) {
                        $reference = $group["reference"];
                        /**
                         * @var \DOMElement $groups
                         */
                        $groups = $xpath->query(sprintf('//accounts/groups/group/reference[text()=%s]/..', self::xpathLiteral($reference)))->item(0);
                        // $groups->setAttribute("level", $group["order"]);
                        $groups->parentNode->appendChild($groups);
                    }
                }
            }
        }
    }
    
    protected function getDepthLevel($groupIdentifier, $tree)
    {
        $parentLevel = 0;
        foreach ($tree as $row) {
            if ($row["grouplogin"] === $groupIdentifier || $row["groupid"] === $groupIdentifier) {
                $parentLevel = max($parentLevel, $this->getDepthLevel($row["parentid"], $tree));
            }
        }
        return $parentLevel + 1;
    }
    /**
     * Remove empty tags
     */
    protected function clearInfo()
    {
        $xpath = new \DOMXpath($this->xml);
        foreach ($xpath->query('//*[not(node())][not(@*)]') as $node) {
            $node->parentNode->removeChild($node);
        }
        foreach ($xpath->query('//*[not(node())][not(@*)]') as $node) {
            $node->parentNode->removeChild($node);
        }
    }
    /**
     * Set to true to export crypted password (default is false)
     * @param bool $exportCryptedPassword
     */
    public function setExportCryptedPassword($exportCryptedPassword)
    {
        $this->exportCryptedPassword = $exportCryptedPassword;
    }
    /**
     * Set to true to export parent group definition for user accounts (default is false)
     * @param bool $exportGroupParent
     */
    public function setExportGroupParent($exportGroupParent)
    {
        $this->exportGroupParent = $exportGroupParent;
    }
    /**
     * Set to true to export document information about account (default is false)
     * @param bool $exportDocument
     */
    public function setExportDocument($exportDocument)
    {
        $this->exportDocument = $exportDocument;
    }
    /**
     * Set to true to export relative roles for group and user accounts (default is false)
     * @param bool $exportRoleParent
     */
    public function setExportRoleParent($exportRoleParent)
    {
        $this->exportRoleParent = $exportRoleParent;
    }
    protected function initXml()
    {
        $this->xml = new \DOMDocument("1.0", "utf-8");
        $this->rootNode = $this->xml->createElement("accounts");
        $this->rootNode->setAttribute("date", date("Y-m-d\\TH:i:s"));
        $this->xml->appendChild($this->rootNode);
        $this->roleRootNode = $this->xml->createElement("roles");
        $this->rootNode->appendChild($this->roleRootNode);
        $this->groupRootNode = $this->xml->createElement("groups");
        $this->rootNode->appendChild($this->groupRootNode);
        $this->userRootNode = $this->xml->createElement("users");
        $this->userRootNode = $this->xml->createElement("users");
        $this->rootNode->appendChild($this->userRootNode);
        
        $this->xml->preserveWhiteSpace = false;
        $this->xml->formatOutput = true;
    }
    /**
     * Record document to add
     * These record are processed in one time at the end of export
     * @see addDocumentNodes
     * @param \Account $user
     */
    protected function memoDocumentInfo(\Account $user)
    {
        $this->documentInfo[$user->id] = $user->fid;
    }
    /**
     * Add document node for each recorded account
     * @throws \Dcp\Exception
     */
    protected function addDocumentNodes()
    {
        $s = new \DocumentList();
        $s->addDocumentIdentifiers($this->documentInfo);
        if (!$this->exportDocument) {
            $search = $s->getSearchDocument();
            $search->returnsOnly(array(
                "id",
                "fromid"
            ));
        }
        
        $export = new \Dcp\ExportXmlDocument();
        $export->setStructureAttributes(true);
        $export->setIncludeSchemaReference(false);
        
        $docXml = new \DOMDocument("1.0", "utf-8");
        $docXml->preserveWhiteSpace = false;
        $xpath = new \DOMXpath($this->xml);
        $count = $s->count();
        $k = 0;
        foreach ($s as $doc) {
            $k++;
            $this->setSessionMessage(sprintf(___("Export relative document (%d/%d)", "fuserexport") , $k, $count));
            
            $documentNode = $this->xml->createElement("document");
            $documentNode->setAttribute("family", $doc->fromname);
            $uid = array_search($doc->id, $this->documentInfo);
            $nodes = $xpath->query(sprintf('//*[@id="%d"]', $uid));
            
            $accountNode = $nodes->item(0);
            $accountNode->appendChild($documentNode);
            if ($this->exportDocument) {
                $export->setDocument($doc);
                
                $export->setAttributeToExport(array(
                    $doc->fromid => $this->filterAttribute($doc)
                ));
                $xml = $export->getXml();
                $docXml->loadXML($xml);
                //$docXml->documentElement->removeAttribute("xsi:noNamespaceSchemaLocation");
                //$docXml->documentElement->removeAttribute("xmlns:xsi");
                $docNode = $this->xml->importNode($docXml->documentElement, true);
                
                $documentNode->appendChild($docNode);
                if ($this->exportSchemaDirectory) {
                    $this->writeFamilySchema($doc->fromname);
                }
            }
        }
        if ($this->exportSchemaDirectory) {
            $this->writeCommonSchema();
        }
    }
    
    protected function writeCommonSchema()
    {
        copy(sprintf("%s/FDL/Layout/fdl.xsd", DEFAULT_PUBDIR) , sprintf("%s/fdl.xsd", $this->exportSchemaDirectory));
        copy(sprintf("%s/FDL/Layout/fdloptions.xsd", DEFAULT_PUBDIR) , sprintf("%s/fdloptions.xsd", $this->exportSchemaDirectory));
        $this->xml->documentElement->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
        $this->xml->documentElement->setAttribute("xsi:noNamespaceSchemaLocation", self::XSDDIR . "/accounts.xsd");
        
        $xsd = new \DOMDocument();
        $xsd->load("USERCARD/Layout/accounts.xsd");
        $xpath = new \DOMXPath($xsd);
        $documentTypeNode = $xpath->query('//xs:complexType[@name="documentType"]/xs:sequence')->item(0);
        
        $this->writeFamilySchema("IUSER");
        $this->writeFamilySchema("IGROUP");
        $this->writeFamilySchema("ROLE");
        
        $family = new_doc("", "IUSER");
        $subFams = $family->getChildFam();
        foreach ($subFams as $subFam) {
            $this->writeFamilySchema($subFam["name"]);
        }
        $family = new_doc("", "IGROUP");
        $subFams = $family->getChildFam();
        foreach ($subFams as $subFam) {
            $this->writeFamilySchema($subFam["name"]);
        }
        $family = new_doc("", "ROLE");
        $subFams = $family->getChildFam();
        foreach ($subFams as $subFam) {
            $this->writeFamilySchema($subFam["name"]);
        }
        
        foreach ($this->schemaWritted as $familyName => $true) {
            $familyName = strtolower($familyName);
            $node = $xsd->createElement("xs:element");
            $node->setAttribute("type", sprintf("family-%s-type", $familyName));
            $node->setAttribute("name", $familyName);
            $node->setAttribute("minOccurs", "0");
            $documentTypeNode->appendChild($node);
            
            $node = $xsd->createElement("xs:include");
            $node->setAttribute("schemaLocation", sprintf("%s.xsd", $familyName));
            
            $firstElement = $xpath->query('//xs:element')->item(0);
            $firstElement->parentNode->insertBefore($node, $firstElement);
        }
        file_put_contents(sprintf("%s/accounts.xsd", $this->exportSchemaDirectory) , $xsd->saveXML());
    }
    
    protected function writeFamilySchema($familyName)
    {
        if (empty($this->schemaWritted[$familyName])) {
            /**
             * @var \DocFam $fam
             */
            $fam = new_doc("", $familyName);
            $output = sprintf("%s/%s.xsd", $this->exportSchemaDirectory, strtolower($fam->name));
            
            file_put_contents($output, $fam->getXmlSchema(true));
            $this->schemaWritted[$familyName] = true;
        }
    }
    /**
     * Return only specific document attribute.
     * System attributes are no exported in document node
     * @param \Doc $doc
     * @return array
     */
    private function filterAttribute(\Doc $doc)
    {
        $filter = array();
        $excludeFilters = array();
        if (is_a($doc, "\\Dcp\\Family\\Iuser")) {
            if (!isset($this->families["IUSER"])) {
                $this->families["IUSER"] = new_doc("", "IUSER");
            }
            $excludeFilters = array(
                "us_lname",
                "us_meid",
                "us_login",
                "us_extmail",
                "us_fname",
                "us_rolegorigin",
                "us_expiresd",
                "us_mail",
                "us_groups",
                "us_t_roles",
                "us_whatid",
                "us_roles",
                "us_rolesorigin",
                "us_group",
                "us_idgroup",
                "us_expires",
                "us_daydelay",
                "us_expirest",
                "us_passdelay",
                "us_ldapdn",
                "us_substitute",
                "us_incumbents",
                "us_passwd1",
                "us_passwd2",
                "us_status",
                "us_loginfailure"
            );
        }
        if (is_a($doc, "\\Dcp\\Family\\Igroup")) {
            if (!isset($this->families["IGROUP"])) {
                $this->families["IGROUP"] = new_doc("", "IGROUP");
            }
            $excludeFilters = array(
                "us_login",
                "us_meid",
                "grp_roles",
                "us_whatid",
                "grp_idpgroup",
                "grp_isrefreshed",
                "grp_name",
                "grp_idgroup",
                "grp_mail",
                "grp_hasmail",
                "ba_title",
                "ba_desc",
                "gui_color",
                "gui_isrss",
                "gui_sysrss",
                "fld_allbut",
                "fld_tfam",
                "fld_fam",
                "fld_famids",
                "fld_subfam",
                "fld_pdoc",
                "fld_pdocid",
                "fld_pdir",
                "fld_pdirid"
            );
        }
        if (is_a($doc, "\\Dcp\\Family\\Role")) {
            if (!isset($this->families["ROLE"])) {
                $this->families["ROLE"] = new_doc("", "ROLE");
            }
            $excludeFilters = array(
                "role_login",
                "role_name",
                "us_whatid"
            );
        }
        
        $attributes = $doc->getAttributes();
        foreach ($attributes as $oattr) {
            if ($oattr->usefor !== "Q" && !in_array($oattr->id, $excludeFilters) && (!$oattr->isNormal || $oattr->type === "array" || $doc->getRawValue($oattr->id) !== "")) {
                $filter[] = $oattr->id;
            }
        }
        return $filter;
    }
    /**
     * Add nodes for group and role related accounts
     * @param \Account $user
     * @param \DOMElement $node
     */
    private function addParentInfo(\Account $user, \DOMElement $node)
    {
        
        $roles = $user->getRoles();
        $groups = $user->getGroupsId();
        
        if (count($roles) > 0) {
            $roleNode = $this->xml->createElement("associatedRoles");
            $roleNode->setAttribute("reset", "false");
            $node->appendChild($roleNode);
            foreach ($roles as $role) {
                $this->workAccount->select($role);
                $roleRef = $this->workAccount->login;
                if ($this->exportRoleParent) {
                    $this->addRoleAccount($this->workAccount);
                }
                $nodeInfo = $this->xml->createElement("associatedRole");
                $nodeInfo->setAttribute("reference", $roleRef);
                $roleNode->appendChild($nodeInfo);
            }
        }
        
        if (count($groups) > 0) {
            $roleNode = $this->xml->createElement("parentGroups");
            $roleNode->setAttribute("reset", "false");
            $node->appendChild($roleNode);
            foreach ($groups as $group) {
                $this->workAccount->select($group);
                $groupRef = $this->workAccount->login;
                if ($this->exportGroupParent) {
                    $this->addGroupAccount($this->workAccount);
                }
                $nodeInfo = $this->xml->createElement("parentGroup");
                $nodeInfo->setAttribute("reference", $groupRef);
                $roleNode->appendChild($nodeInfo);
            }
        }
    }
    /**
     * Add User node info
     * @param \Account $user
     * @throws \Dcp\Db\Exception
     */
    protected function addUserAccount(\Account $user)
    {
        $node = $this->xml->createElement("user");
        
        $infos = array(
            "login",
            "firstname",
            "lastname",
            "mail"
        );
        foreach ($infos as $info) {
            $infoValue = $user->$info;
            if ($infoValue) {
                $nodeInfo = $this->xml->createElement($info, htmlspecialchars($infoValue));
                $node->appendChild($nodeInfo);
            }
        }
        $nodeInfo = $this->xml->createElement("status");
        $nodeInfo->setAttribute("activated", $user->status === "D" ? "false" : "true");
        $node->appendChild($nodeInfo);
        
        if ($user->substitute) {
            simpleQuery("", sprintf("select login from users where id = %d", $user->substitute) , $substituteLogin, true, true);
            if ($substituteLogin) {
                $nodeInfo = $this->xml->createElement("substitute");
                $nodeInfo->setAttribute("reference", $substituteLogin);
                $node->appendChild($nodeInfo);
            }
        }
        if ($this->exportCryptedPassword) {
            $nodeInfo = $this->xml->createElement("password", htmlspecialchars($user->password));
            $nodeInfo->setAttribute("crypted", "true");
            $node->appendChild($nodeInfo);
        }
        
        $node->setAttribute("id", $user->id);
        $this->addParentInfo($user, $node);
        
        $this->memoDocumentInfo($user);
        
        $this->userRootNode->appendChild($node);
    }
    /**
     * Add group node info
     * @param \Account $group
     */
    protected function addGroupAccount(\Account $group)
    {
        if (empty($this->addedIds[$group->id])) {
            $node = $this->xml->createElement("group");
            $nodeInfo = $this->xml->createElement("reference", htmlspecialchars($group->login));
            $node->appendChild($nodeInfo);
            $nodeInfo = $this->xml->createElement("displayName", htmlspecialchars($group->getAccountName()));
            $node->appendChild($nodeInfo);
            
            $node->setAttribute("id", $group->id);
            
            $this->memoDocumentInfo($group);
            $this->groupRootNode->appendChild($node);
            $this->addedIds[$group->id] = true;
            $this->addParentInfo($group, $node);
        }
    }
    /**
     * Add role node info
     * @param \Account $role
     */
    protected function addRoleAccount(\Account $role)
    {
        if (empty($this->addedIds[$role->id])) {
            $node = $this->xml->createElement("role");
            $nodeInfo = $this->xml->createElement("reference", htmlspecialchars($role->login));
            $node->appendChild($nodeInfo);
            $nodeInfo = $this->xml->createElement("displayName", htmlspecialchars($role->getAccountName()));
            $node->appendChild($nodeInfo);
            $node->setAttribute("id", $role->id);
            $this->memoDocumentInfo($role);
            
            $this->roleRootNode->appendChild($node);
            $this->addedIds[$role->id] = true;
        }
    }
    /**
     * Convert a string to an XPath literal
     *
     * If the string contains an apostrophe, then a concat() is used
     * to construct the string literal expression.
     *
     * If no apostrophe is found, then quote the string with apostrophes.
     *
     * @param $str
     * @return string
     */
    protected static function xpathLiteral($str)
    {
        if (strpos($str, "'") === false) {
            return "'" . $str . "'";
        } else {
            return "concat(" . str_replace(array(
                "'',",
                ",''"
            ) , "", "'" . implode("',\"'\",'", explode("'", $str)) . "'") . ")";
        }
    }
}
