<?php
/*
 * Clean parasite attributes
 *
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("FDL/Class.DocFam.php");

$usage = new ApiUsage();
$usage->setDefinitionText("Delete attributes parameters and values which are not defined in family");
$docid = $usage->addRequiredParameter("famid", "family identifier - 0 means all families");
$verifyOnly = $usage->addEmptyParameter("verify-only", "only verify, do not changes");
$withoutConfirm = $usage->addEmptyParameter("without-confirm", "ask confirmation before operating");
$usage->verify();

class cleanFamily
{
    /**
     * @var DocFam
     */
    public $doc = null;
    public $messages = array();
    public $dryrun = false;
    
    public function clean(DocFam $fam)
    {
        $this->doc = $fam;
        $this->messages = array();
        $this->cleanDefaultAndParametersValues();
        $this->cleanStructure();
    }
    public function justModify($justModify)
    {
        $this->dryrun = $justModify;
    }
    public function cleanStructure()
    {
        if (!$this->doc) return;
        
        $orpheanAttributes = CheckDb::getOrpheanAttributes($this->doc->id);
        if ($orpheanAttributes) {
            
            $sql = array();
            foreach ($orpheanAttributes as $orpheanAttrId) {
                $sql[] = sprintf("alter table %s drop column %s cascade; ", familyTableName($this->doc->id) , $orpheanAttrId);
                
                $this->messages[].= "Destroy values for \"$orpheanAttrId\".";
            }
            //$sql[] = sprintf("create view family.\"%s\" as select * from doc%d", strtolower($this->doc->name) , $this->doc->id);
            if (!$this->dryrun) {
                foreach ($sql as $aSql) {
                    simpleQuery('', $aSql);
                }
            }
        }
    }
    
    public function cleanDefaultAndParametersValues()
    {
        $defs = $this->doc->getOwnDefValues();
        foreach ($defs as $aid => $v) {
            if (!$this->doc->getAttribute($aid)) {
                $this->doc->setDefValue($aid, '', false);
                $this->messages[].= "Clear default value \"$aid\".";
            }
        }
        $defs = $this->doc->getOwnParams();
        foreach ($defs as $aid => $v) {
            $oa = $this->doc->getAttribute($aid);
            if (!$oa || $oa->usefor != 'Q') {
                $this->doc->setParam($aid, '', false);
                $this->messages[].= "Clear parameter value \"$aid\".";
            }
        }
        
        if (!$this->dryrun) {
            $this->doc->modify();
        }
    }
    
    public function getMessage($sep = "\n")
    {
        return implode($sep, $this->messages);
    }
}
/**
 * @var Action $action
 */
// First Part: Workflow
print "\t === Deleting parasite attributes ===\n";

$s = new SearchDoc($action->dbaccess, "-1");
$s->setObjectReturn(true);
$s->setOrder("initid");
$fam = null;
if ($docid) {
    $fam = new_Doc($action->dbaccess, $docid);
    if (!$fam->isAlive()) {
        $action->exitError(sprintf("%s is not referenced", $docid));
    }
    if ($fam->doctype != 'C') {
        $action->exitError(sprintf("%s is not a family reference", $docid));
    }
    $s->addFilter("id = %d", $fam->id);
}

if ($verifyOnly) {
    print "\nJust Verify...\n";
} elseif (!$withoutConfirm) {
    printf("The suppression of attributes is irreversible.\nConfirm deleting parasites for %s [Y|N] ? ", $docid ? $fam->name : "all families");
    $confirm = strtolower(trim(fgets(STDIN)));
    if ($confirm != "y" && $confirm != "yes") {
        print "\nAborted.\n";
        exit(0);
    }
    print "\nCleaning in progress...\n";
}

$s->search();
$deleting = array();

$c = new cleanFamily();
$c->justModify($verifyOnly);
/**
 * @var DocFam $fam
 */
while ($fam = $s->getNextDoc()) {
    $c->clean($fam);
    $msg = $c->getMessage("\n\t");
    if ($msg) {
        printf("\n\"%s\" %s cleaning\n", $fam->getTitle() , $fam->name);
        print "\t$msg\n";
    } else {
        printf("\"%s\" %s is clean. Nothing to do.\n", $fam->getTitle() , $fam->name);
    }
}
print "\n";
