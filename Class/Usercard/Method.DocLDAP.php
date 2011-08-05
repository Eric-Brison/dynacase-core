<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 *  LDAP methods
 *
 * @author Anakeen 2000
 * @version $Id: Method.DocLDAP.php,v 1.9 2008/03/10 11:18:29 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
/**
 */
// LDAP parameters
var $serveur;
var $port;
var $racine;
var $rootdn;
var $rootpw;
/**
 * init society organization of the tree
 * @return bool true if organization has been created or its already created
 */
function OrgInit()
{
    if (!$this->useldap) false;
    // ------------------------------
    // include LDAP organisation first
    $orgldap["objectClass"] = "organization";
    if (preg_match("/.*o=(.*),.*/", $this->racine, $reg)) $orgldap["o"] = $reg[1]; // get organisation from LDAP_ROOT
    else $orgldap["o"] = "unknown";
    
    $dn = $this->racine;
    $ds = ldap_connect($this->serveur, $this->port);
    
    if ($ds) {
        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (@ldap_bind($ds, $this->rootdn, $this->rootpw)) {
            
            if (@ldap_search($ds, "dc=users," . $dn, "", array())) return true;
            else {
                if (!@ldap_search($ds, "dc=users," . $dn, "", array())) {
                    ldap_add($ds, $dn, $orgldap);
                }
                $err = $this->createLDAPDc($ds, "users");
                if ($err == "") $err = $this->createLDAPDc($ds, "people");
                if ($err) AddWarningMsg($err);
                return true;
            }
        }
    }
    
    return false;
}
/**
 * initialialize LDAP coordonates
 */
function SetLdapParam()
{
    global $action;
    $this->serveur = $action->GetParam("LDAP_SERVEUR");
    $this->port = $action->GetParam("LDAP_PORT");
    $this->racine = $action->GetParam("LDAP_ROOT");
    $this->rootdn = $action->GetParam("LDAP_ROOTDN");
    $this->rootpw = $action->GetParam("LDAP_ROOTPW");
    $this->useldap = ($action->GetParam("LDAP_ENABLED", "no") == "yes");
    
    $this->action = $action;
}
/**
 * get DNs created in LDAP database from this document
 * @return array of Dns indexed by card index which comes from definition of mapping
 */
function getDNs()
{
    if ($this->ldapdn == "") return array();
    return $this->_val2array($this->ldapdn);
}
/**
 * set new DNs created in LDAP database from this document
 * suppress old DNs card from LDAP if exists
 * @param resource $ds LDAP connection ressouce
 * @param array $tdn array of DN new DN
 * @return void
 */
function setDNs($ds, $tdn)
{
    $toldn = $this->getDNs();
    foreach ($toldn as $k => $dn) {
        if (!in_array($dn, $tdn)) {
            ldap_delete($ds, $dn);
        }
    }
    $this->ldapdn = $this->_array2val($tdn);
    $this->modify(true, array(
        "ldapdn"
    ) , true);
}
/**
 * update or delete LDAP card
 */
function RefreshLdapCard()
{
    $this->SetLdapParam();
    if (!$this->useldap) return false;
    
    if ($this->canUpdateLdapCard()) {
        $tinfoldap = $this->ConvertToLdap();
        $err = $this->ModifyLdapCard($tinfoldap);
    } else {
        $err = $this->DeleteLdapCard();
    }
    return $err;
}
/**
 * delete LDAP cards of document
 */
function DeleteLdapCard()
{
    if (!$this->useldap) return;
    
    if (($this->serveur != "") && ($this->id > 0)) {
        $ds = ldap_connect($this->serveur, $this->port);
        
        if ($ds) {
            
            ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
            if (@ldap_bind($ds, $this->rootdn, $this->rootpw)) {
                $this->setDNs($ds, array());
            }
            
            ldap_close($ds);
        }
    }
}
/**
 * get DN of document
 */
function getLDAPDN($rdn, $path = "")
{
    if ($path == "") $dn = "$rdn=" . $this->infoldap[$this->cindex][$rdn] . "," . $this->racine;
    else $dn = "$rdn=" . $this->infoldap[$this->cindex][$rdn] . ",$path," . $this->racine;
    return $dn;
}
/**
 * get Attribute mapping FREEDOM -> LDAP
 * @return array
 */
function getMapAttributes()
{
    include_once ("FDL/Class.DocAttrLDAP.php");
    $fids = $this->GetFromDoc();
    
    include_once ("Class.QueryDb.php");
    $q = new QueryDb($this->dbaccess, "DocAttrLDAP");
    $q->AddQuery(getSqlCond($fids, "famid"));
    $q->order_by = "famid,ldapclass";
    $l = $q->Query(0, 0, "TABLE");
    $this->ldapmap = array();
    foreach ($l as $v) {
        $this->ldapmap[$v["ldapname"] . $v["index"]] = $v;
    }
    return $this->ldapmap;
}
/**
 * return array(card) of array of ldap values LDAP card from user document
 */
function ConvertToLdap()
{
    
    $this->infoldap = array();
    
    $tmap = $this->getMapAttributes();
    
    foreach ($tmap as $ki => $v) {
        $k = $v["ldapname"];
        $map = $v["ldapmap"];
        $index = $v["index"];
        if ($map) {
            if (substr($map, 0, 2) == "::") {
                // call method
                $this->cindex = $index; // current index
                $value = $this->ApplyMethod($map);
                if ($value) {
                    $this->infoldap[$index][$k] = $value;
                    if ((!isset($this->infoldap[$index]["objectClass"])) || (!in_array($v["ldapclass"], $this->infoldap[$index]["objectClass"]))) $this->infoldap[$index]["objectClass"][] = $v["ldapclass"];
                }
            } else {
                switch ($map) {
                    case "I":
                        $this->infoldap[$index][$k] = $this->initid;
                        break;

                    case "T":
                        $this->infoldap[$index][$k] = $this->title;
                        break;

                    default:
                        $oa = $this->getAttribute($map);
                        $value = $this->getValue($map);
                        
                        if ($value) {
                            if ((!isset($this->infoldap[$index]["objectClass"])) || (!in_array($v["ldapclass"], $this->infoldap[$index]["objectClass"]))) $this->infoldap[$index]["objectClass"][] = $v["ldapclass"];
                            
                            switch ($oa->type) {
                                case "image":
                                    if (preg_match(PREGEXPFILE, $value, $reg)) {
                                        $vf = newFreeVaultFile($this->dbaccess);
                                        if ($vf->Retrieve($reg[2], $info) == "") {
                                            $fd = fopen($info->path, "r");
                                            if ($fd) {
                                                $contents = @fread($fd, filesize($info->path));
                                                $this->infoldap[$index][$k] = ($contents);
                                                fclose($fd);
                                            }
                                        }
                                    }
                                    break;

                                case "password":
                                    $this->infoldap[$index][$k] = "{CRYPT}" . ($value);
                                    break;

                                default:
                                    $this->infoldap[$index][$k] = $value;
                            }
                        }
                    }
            }
        }
    }
    
    return $this->infoldap;
}
/**
 * get ldap value
 * @param string $idattr ldap attribute name
 * @return string the value
 */
function getLDAPValue($idattr, $index = "")
{
    if (!isset($this->infoldap)) {
        $this->SetLdapParam();
        $this->ConvertToLdap();
    }
    if ($index == "") $tldap = current($this->infoldap);
    else $tldap = $this->infoldap[$index];
    
    return $tldap[$idattr];
}
/**
 * modify in LDAP database information
 */
function ModifyLdapCard($tinfoldap)
{
    if (!$this->useldap) return;
    $retour = "";
    if ($this->serveur != "") {
        if ($this->OrgInit()) {
            // ------------------------------
            // update LDAP values
            if (!isset($ds)) {
                $ds = ldap_connect($this->serveur, $this->port);
            }
            
            if ($ds) {
                ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
                
                if (@ldap_bind($ds, $this->rootdn, $this->rootpw)) {
                    $tnewdn = array();
                    foreach ($tinfoldap as $k => $infoldap) {
                        $tdn = $infoldap["dn"];
                        unset($infoldap["dn"]);
                        if (!is_array($tdn)) $tdn = array(
                            $tdn
                        );
                        foreach ($tdn as $dn) {
                            
                            $sr = @ldap_read($ds, $dn, "objectClass=*");
                            
                            if ($sr) {
                                $attrs = ldap_get_attributes($ds, ldap_first_entry($ds, $sr));
                                // need to reset all values in case of deleted values
                                $delldap = array();
                                for ($i = 0; $i < $attrs["count"]; $i++) {
                                    if (!isset($infoldap[$attrs[$i]])) $delldap[$attrs[$i]] = array();
                                }
                                if (count($delldap) > 0) {
                                    ldap_mod_del($ds, $dn, $delldap);
                                }
                                ldap_mod_replace($ds, $dn, $infoldap);
                                $tnewdn[] = $dn;
                            } else {
                                if (!@ldap_add($ds, $dn, $infoldap)) {
                                    $retour.= sprintf(_("errldapadd:%s\n%s\n%d\n") , $dn, ldap_error($ds) , ldap_errno($ds));
                                } else {
                                    // add OK
                                    $tnewdn[] = $dn;
                                }
                            }
                        }
                    }
                    $this->setDNs($ds, $tnewdn); // suppress old DN if needed
                    
                }
                ldap_close($ds);
            } else {
                $retour = _("errldapconnect");
            }
        } else {
            $retour = _("errldaporginit");
        }
    }
    return $retour;
}
/**
 * created an LDAP DC object in root directory
 */
function createLDAPDc($ds, $n)
{
    
    if ($ds) {
        if (!@ldap_add($ds, "dc=$n," . $this->racine, array(
            "objectClass" => array(
                "dcObject",
                "organizationalUnit"
            ) ,
            "dc" => "$n",
            "ou" => "$n"
        ))) return ldap_error($ds);
    }
}
?>