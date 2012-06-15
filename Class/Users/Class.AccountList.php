<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Account list class
 *
 * @author Anakeen
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */
class AccountList implements Iterator, Countable
{
    /**
     * @var null|SearchAccount
     */
    private $accountsData = null;
    /**
     * @var null|Account
     */
    private $currentAccount = null;
    private $currentIndex = 0;
    
    private $init = false;
    public $length = 0;
    
    public function __construct(Array $data)
    {
        $this->accountsData = $data;
        $this->length = count($this->accountsData);
        $this->initSearch();
    }
    /**
     * get number of returned documents
     * can be upper of real length due to callback map
     * @return int
     */
    public function count()
    {
        return $this->length;
    }
    private function initSearch()
    {
        
        $this->currentIndex = 0;
        $this->currentAccount = new Account();
    }
    
    private function getCurrentAccount()
    {
        if (empty($this->accountsData[$this->currentIndex])) return null;
        $this->currentAccount->affect($this->accountsData[$this->currentIndex]);
        return $this->currentAccount;
    }
    
    public function rewind()
    {
        $this->initSearch();
    }
    /**
     * @return void
     */
    public function next()
    {
        $this->currentIndex++;
    }
    
    public function key()
    {
        return $this->currentAccount->id;
    }
    /**
     * @return \Account|null
     */
    public function current()
    {
        return $this->getCurrentAccount();
    }
    /**
     * @return bool
     */
    public function valid()
    {
        return (!empty($this->accountsData[$this->currentIndex]));
    }
}
?>