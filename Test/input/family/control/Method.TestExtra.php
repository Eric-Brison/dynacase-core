<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Extra attribute test
 *
 * @author Anakeen
 * 
 * @package Dcp\Pu
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _TEST_EXTRA extends Doc
{
    /**
     * @end-method-ignore
     */
    
    public function preImport(array $extra)
    {
        $err = parent::preImport($extra);
        if ($err == "") {
            if (empty($extra) || empty($extra["state"]) || ($extra["state"] != "alive" && $extra["num"] == "1") || ($extra["state"] != "sick" && $extra["num"] == "2")) {
                return _("TEST_EXTRA:Extra state not found");
            }
        }
        return $err;
    }
    
    public function postImport(array $extra)
    {
        $err = parent::postImport($extra);
        if ($err == "") {
            $err = $this->SetValue("test_extra", json_encode($extra));
            if ($err == "") {
                $err = $this->store();
            }
        }
        return $err;
    }
    /**
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
     */
}
/**
 * @end-method-ignore
 */
