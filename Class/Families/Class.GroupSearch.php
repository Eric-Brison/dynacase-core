<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Grouped searches
 */
namespace Dcp\Core;
class GroupedSearch extends \Dcp\Family\Search
{
    var $defaultedit = "FDL:EDITBODYCARD";
    var $defaultview = "FDL:VIEWBODYCARD";
    
    function ComputeQuery($keyword = "", $famid = - 1, $latest = "yes", $sensitive = false, $dirid = - 1, $subfolder = true, $full = false)
    {
        $tidsearch = $this->getMultipleRawValues("SEG_IDCOND");
        
        $query = array();
        foreach ($tidsearch as $k => $v) {
            /**
             * @var \Dcp\Family\Search $doc
             */
            $doc = new_Doc($this->dbaccess, $v);
            $err = $doc->control("execute");
            
            if ($err == "" && method_exists($doc, "getQuery")) {
                
                $doc->setValue("SE_IDCFLD", $this->getRawValue("SE_IDCFLD"));
                $q = $doc->getQuery();
                
                $query[] = $q[0];
            }
        }
        
        return $query;
    }
    /**
     * return false : is never staticSql
     * @return bool
     */
    function isStaticSql()
    {
        return false;
    }
}
