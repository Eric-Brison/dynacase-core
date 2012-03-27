<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Persons & LDAP methods
 *
 * @author Anakeen 2000
 * @version $Id: Method.DocUser.php,v 1.37 2006/06/23 15:30:55 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage USERCARD
 */
/**
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _USER extends Doc
{
    /*
     * @end-method-ignore
    */
    
    var $defaultabstract = "USERCARD:VIEWABSTRACTCARD";
    var $cviews = array(
        "USERCARD:VIEWABSTRACTCARD"
    );
    /**
     * @templateController
     * @param string $target
     * @param bool $ulink
     * @param string $abstract
     */
    function viewabstractcard($target = "finfo", $ulink = true, $abstract = "Y")
    {
        // -----------------------------------
        //     doc::viewabstractcard($target,$ulink,$abstract);
        $this->viewprop($target, $ulink, $abstract);
        $this->viewattr($target, $ulink, $abstract);
    }
    
    function PostModify()
    {
        $err = $this->RefreshLdapCard();
        
        $this->SetPrivacity(); // set doc properties in concordance with its privacity
        return ($err);
    }
    
    function SpecRefresh()
    {
        // " gettitle(D,US_IDSOCIETY):US_SOCIETY,US_IDSOCIETY";
        $this->refreshDocTitle("US_IDSOCIETY", "US_SOCIETY");
        
        $this->AddParamRefresh("US_IDSOCIETY,US_SOCADDR", "US_WORKADDR,US_WORKTOWN,US_WORKPOSTALCODE,US_WORKWEB,US_WORKCEDEX,US_COUNTRY,US_SPHONE,US_SFAX");
        $this->AddParamRefresh("US_IDSOCIETY", "US_SCATG,US_JOB");
        
        $doc = new_Doc($this->dbaccess, $this->getValue("US_IDSOCIETY"));
        if ($doc->isAlive()) {
            if ($this->getValue("US_SOCADDR") != "") {
                $this->setValue("US_WORKADDR", $doc->getValue("SI_ADDR", " "));
                $this->setValue("US_WORKTOWN", $doc->getValue("SI_TOWN", " "));
                $this->setValue("US_WORKPOSTALCODE", $doc->getValue("SI_POSTCODE", " "));
                $this->setValue("US_WORKWEB", $doc->getValue("SI_WEB", " "));
                $this->setValue("US_WORKCEDEX", $doc->getValue("SI_CEDEX", " "));
                $this->setValue("US_COUNTRY", $doc->getValue("SI_COUNTRY", " "));
            }
            $this->setValue("US_SCATG", $doc->getValue("SI_CATG"));
            $this->setValue("US_JOB", $doc->getValue("SI_JOB"));
            
            if ($this->getValue("US_PPHONE") != "") $this->setValue("US_PHONE", $this->getValue("US_PPHONE") . " (" . _("direct") . ")");
            else $this->setValue("US_PHONE", $doc->getValue("SI_PHONE", " "));
            if ($this->getValue("US_PFAX") != "") $this->setValue("US_FAX", $this->getValue("US_PFAX") . " (" . _("direct") . ")");
            else $this->setValue("US_FAX", $doc->getValue("SI_FAX", " "));
        } else {
            $this->setValue("US_PHONE", $this->getValue("US_PPHONE", " "));
            $this->setValue("US_FAX", $this->getValue("US_PFAX", " "));
        }
    }
    /**
     * refresh LDAP
     */
    function PostDelete()
    {
        $this->SetLdapParam();
        $this->DeleteLdapCard();
    }
    /**
     * test if the document can be set in LDAP
     */
    function canUpdateLdapCard()
    {
        // $priv=$this->GetValue("US_PRIVCARD");
        $priv = '';
        if ($priv == "S") return false;
        return true;
    }
    /**
     * return different DN if is a private or not private card
     * @return string
     */
    function getUserLDAPDN($rdn, $path = "")
    {
        $priv = $this->GetValue("US_PRIVCARD");
        if ($priv == "P") {
            $u = new Account("", $this->owner);
            if ($u->isAffected()) {
                $this->infoldap[$this->cindex]["ou"] = $u->login;
                return sprintf("%s=%s,ou=%s,%s,%s", $rdn, $this->infoldap[$this->cindex][$rdn], $u->login, $path, $this->racine);
            }
        } elseif ($priv == "G") {
            $tidg = $this->getTValue("us_idprivgroup");
            
            $tdn = array(); // array od DN
            foreach ($tidg as $k => $idg) {
                $t = getTDoc($this->dbaccess, $idg);
                $login = getv($t, "us_login");
                $this->infoldap[$this->cindex]["ou"] = $login;
                $tdn[] = sprintf("%s=%s,ou=%s,%s,%s", $rdn, $this->infoldap[$this->cindex][$rdn], $login, $path, $this->racine);
            }
            if (count($tdn) == 0) return "";
            elseif (count($tdn) == 1) return $tdn[0];
            return $tdn;
        } else {
            return $this->getLDAPDN($rdn, $path);
        }
        return '';
    }
    /**
     * recompute profil with privacy attribute value
     * 5 possibilities :
     *  W : public in read/write
     *  R : public in read mode
     *  P : private
     *  G : group restriction
     *  S : specific profil : do nothing
     */
    function SetPrivacity()
    {
        $priv = $this->GetValue("US_PRIVCARD");
        $err = "";
        
        switch ($priv) {
            case "P":
                if ($this->profid == "0") {
                    $err = $this->setControl();
                } else {
                    $this->RemoveControl();
                    $err = $this->setControl();
                }
                $err = $this->lock();
                
                break;

            case "R":
                if ($this->profid != "0") {
                    $err = $this->unsetControl();
                }
                $this->lock();
                break;

            case "W":
                if ($this->profid != "0") {
                    $err = $this->unsetControl();
                }
                $this->unlock();
                break;

            case "G":
                if ($this->profid == "0") {
                    $err = $this->setControl();
                } elseif ($this->profid == $this->id) {
                    //already profil :reset
                    $this->RemoveControl();
                    $err = $this->setControl();
                }
                if ($this->profid == $this->id) {
                    
                    $tidg = $this->getTValue("us_idprivgroup");
                    foreach ($tidg as $k => $idg) {
                        $t = getTDoc($this->dbaccess, $idg);
                        $gid = getv($t, "us_whatid");
                        
                        $this->AddControl($gid, "view");
                    }
                }
                
                $err = $this->lock();
                
                break;
        }
        if ($err != "") AddLogMsg($this->title . ":" . $err);
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