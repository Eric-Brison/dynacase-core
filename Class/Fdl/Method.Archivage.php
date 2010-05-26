<?php
/**
 * Detailled search
 *
 * @author Anakeen 2000
 * @version $Id: Method.DetailSearch.php,v 1.73 2009/01/08 17:52:54 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
/**
 */


/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
Class _ARCHIVING extends Doc {
	/*
	 * @end-method-ignore
	 */
	
        function arc_close() {
            $err=$this->setValue("arc_status","C");
            if (! $err) $err=$this->modify();
            
            if (! $err) {
               
                $err=simpleQuery($this->dbaccess,sprintf("select childid from fld where dirid=%d and qtype='S'",$this->initid),$ids,true,false);
                print_r2($ids);
            }
            
            return $err;
        }
        function arc_reopen() {
            $err=$this->setValue("arc_status","O");
            if (! $err) $err=$this->modify();
            return $err;
        }
        function arc_restore() {
            $err=$this->setValue("arc_status","O");
            if (! $err) $err=$this->modify();
            return $err;
        }
	/**
	* @begin-method-ignore
	* this part will be deleted when construct document class until end-method-ignore
	*/
}

/*
 * @end-method-ignore
 */

?>