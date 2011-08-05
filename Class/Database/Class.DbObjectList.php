<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
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
class DbObjectList implements Iterator
{
    private $object = null;
    private $res = null;
    private $index = 0;
    public $length = 0;
    
    public function __construct($dbaccess, $res, $classname)
    {
        if ($res) {
            $this->res = $res;
            $this->object = new $classname($dbaccess);
            $this->length = pg_num_rows($this->res);
        }
    }
    
    public function rewind()
    {
        $this->index = 0;
    }
    public function next()
    {
        $this->index++;
    }
    public function key()
    {
        return $this->index;
    }
    public function current()
    {
        /*print_r2(array(
            "type" => __METHOD__,
            "index" => $this->index,
            "length" => $this->length
        ));*/
        
        $this->object->affect(pg_fetch_array($this->res, $this->index, PGSQL_ASSOC));
        
        return $this->object;
    }
    public function valid()
    {
        return ($this->index < $this->length);
    }
}
?>