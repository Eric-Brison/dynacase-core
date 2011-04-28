<?php
/**
 * Document list class
 *
 * @author Anakeen  
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */
class DocumentList implements Iterator
{
    private $search = null;
    private $currentDoc = null;
    /** anonymous function */
    private $hookFunction = null;
    public $length = 0;
    
    public function __construct(SearchDoc &$s = null)
    {
        $this->search = $s;
        $this->initSearch();
    }
    
    private function initSearch()
    {
        if ($this->search) {
            if (!$this->search->isExecuted()) $this->search->search();
            if ($this->search->getError()) {
                throw new Exception($this->search->getError());
            }
            $this->length = $this->search->count();
        }
    }
    
    public function rewind()
    {
        
        $this->currentDoc = $this->search->nextDoc();
        $this->callHook();
       
    }
    public function next()
    {
        $this->currentDoc = $this->search->nextDoc();
        $this->callHook();
    }
    
    private function callHook() {
        if ($this->currentDoc && $this->hookFunction) {
       
           // call_user_func($function, $this->currentDoc);
            $h=$this->hookFunction;
            $h($this->currentDoc);
        
        }
    }
    public function key()
    {
        return $this->currentDoc->id;
    }
    public function current()
    {
        return $this->currentDoc;
    }
    public function valid()
    {
        return $this->currentDoc != false;
    }
    public function getSearchDocument()
    {
        return $this->search;
    }
    
    public function addDocumentIdentificators(array $ids, $useInitid = true)
    {
        $this->search = new SearchDoc(getDbAccess());
        $this->search->setObjectReturn();
        $this->search->excludeConfidential();
        foreach ( $ids as $k => $v ) {
            if (!$v) unset($ids[$k]);
        }
        $ids = array_unique($ids);
        $sid = $useInitid ? "initid" : "id";
        $this->search->addFilter($this->search->sqlCond($ids, $sid, true));
        $this->initSearch();
    }
    public function setHook($hookFunction) {
        $this->hookFunction=$hookFunction;
    }
}
?>