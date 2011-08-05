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
    
    private $init = false;
    public $length = 0;
    
    public function __construct(SearchDoc &$s = null)
    {
        $this->search = $s;
        $this->initSearch();
    }
    
    /**
     * get number of returned documents
     * can be upper of real length due to callback map
     * @return int
     */
    public function count() {
        $this->initSearch();
        return $this->length;
    }
    private function initSearch()
    {
        if ($this->search) {
            if (!$this->init) {
                if (!$this->search->isExecuted()) $this->search->search();
                if ($this->search->getError()) {
                    throw new Exception($this->search->getError());
                }
                $this->length = $this->search->count();
                $this->init=true;
            }
        }
    }
    
    private function getCurrentDoc() {
        $this->currentDoc = $this->search->nextDoc();
        $good = ($this->callHook() !== false);
        if (!$good) {
            while ( $this->currentDoc = $this->search->nextDoc() ) {
                $good = ($this->callHook() !== false);
                if ($good) break;
            }
        }
    }
    
    public function rewind()
    {
        $this->initSearch();
        $this->getCurrentDoc();
    
    
    }
    public function next()
    {
        $this->getCurrentDoc();
    }
    
    private function callHook()
    {
        if ($this->currentDoc && $this->hookFunction) {
            // call_user_func($function, $this->currentDoc);
            $h = $this->hookFunction;
            return $h($this->currentDoc);
        
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
            if ((!$v) || (!is_numeric($v))) unset($ids[$k]);
        }
        $ids = array_unique($ids);
        $sid = $useInitid ? "initid" : "id";
        if (count($ids) == 0) {
            $this->search->addFilter("false");
        } else {
            $this->search->addFilter($this->search->sqlCond($ids, $sid, true));
        }
    }
    /**
     * apply a callback on each document
     * if callback return false, the document is skipped from list
     * @return void
     */
    public function listMap($hookFunction)
    {
        $this->hookFunction = $hookFunction;
    }
}
?>