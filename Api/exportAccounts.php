<?php
/*
 * Account export
 * @author Anakeen
 * @package FDL
*/

$usage = new ApiUsage();
$usage->setDefinitionText("Export accounts definition");
/** @noinspection PhpUnusedParameterInspection */
$filename = $usage->addRequiredParameter("file", "the output file (use - for stdout)", function ($values, $argName, ApiUsage $apiusage)
{
    if ($values === ApiUsage::GET_USAGE) {
        return "";
    }
    if ($values !== "-" && is_file($values) && !is_writable($values)) {
        $apiusage->exitError(sprintf("Error: file output \"%s\" not writable.", $values));
    }
    return '';
});
/** @noinspection PhpUnusedParameterInspection */
$schemaDirectory = $usage->addOptionalParameter("schema-directory", "directory where produce xsd for documents", function ($values, $argName, ApiUsage $apiusage)
{
    if ($values === ApiUsage::GET_USAGE) {
        return "";
    }
    if ($values !== "-" && is_dir($values) && !is_writable($values)) {
        $apiusage->exitError(sprintf("Error: directory output \"%s\" not writable.", $values));
    }
    return '';
});
/**
 * @var \Account $memberAccount
 */
$memberAccount = null;
/** @noinspection PhpUnusedParameterInspection */
$memberOf = $usage->addOptionalParameter("memberOf", "Restrict to account which are member of this group or role reference", function ($values, $argName, ApiUsage $apiusage) use (&$memberAccount)
{
    if ($values) {
        if ($values === ApiUsage::GET_USAGE) {
            return "";
        }
        $memberAccount = new Account();
        if (!$memberAccount->setLoginName($values)) {
            $apiusage->exitError(sprintf("Error: member reference \"%s\" unknow.", $values));
        }
    }
    return '';
});

$exportPassword = $usage->addEmptyParameter("crypt-password", "add crypt password");
$exportRole = $usage->addEmptyParameter("roles", "export associated roles");
$exportGroup = $usage->addEmptyParameter("groups", "export parent groups");
$exportDocument = $usage->addEmptyParameter("document", "export specific document information");
/** @noinspection PhpUnusedParameterInspection */
$type = $usage->addOptionalParameter("type", "restricted to account type", function ($values, $argName, ApiUsage $apiusage)
{
    $opt = array(
        "user",
        "role",
        "group"
    );
    if ($values === ApiUsage::GET_USAGE) return sprintf(" [%s] ", implode('|', $opt));
    
    $error = $apiusage->matchValues($values, $opt);
    if ($error) {
        $apiusage->exitError(sprintf("Error: wrong value for argument 'type' : %s", $error));
    }
    return '';
});
$filterLogin = $usage->addOptionalParameter("login-filter", "filter login contains");
$usage->verify();

$export = new \Dcp\Core\ExportAccounts();
$search = new SearchAccount();
if ($filterLogin) {
    $search->addFilter(sprintf("login ~* '%s'", $filterLogin));
}
if ($memberOf && $memberAccount) {
    if ($memberAccount->accounttype === Account::GROUP_TYPE) {
        $search->addGroupFilter($memberAccount->login);
    }
    if ($memberAccount->accounttype === Account::ROLE_TYPE) {
        $search->addRoleFilter($memberAccount->login);
    }
}

if ($type) {
    if (!is_array($type)) {
        $type = array(
            $type
        );
    }
    $accountType = 0;
    foreach ($type as $singleType) {
        switch ($singleType) {
            case "user":
                $accountType|= \SearchAccount::userType;
                break;

            case "group":
                $accountType|= \SearchAccount::groupType;
                break;

            case "role":
                $accountType|= \SearchAccount::roleType;
                break;
        }
    }
    $search->setTypeFilter($accountType);
}
if ($schemaDirectory) {
    $export->setExportSchemaDirectory($schemaDirectory);
}
$export->setExportGroupParent($exportGroup);
$export->setExportRoleParent($exportRole);
$export->setExportCryptedPassword($exportPassword);
$export->setExportDocument($exportDocument);
$export->setSearchAccount($search);

if ($filename === "-") {
    $filename = "php://stdout";
}
file_put_contents($filename, $export->export());
