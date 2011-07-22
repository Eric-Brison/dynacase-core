<?php
/**
 * Class use to import and  export Ldif format (like LDAP) with FREEDOM USER Family
 *
 * @author Anakeen 2001
 * @version \$Id: Class.UsercardLdif.php,v 1.9 2005/02/01 16:23:25 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
/**
 */




/**
 * Class use to import and  export Ldif format (like LDAP) with FREEDOM USER Family
 *
 * @deprecated use Method.User.php instead
 */
class UsercardLdif 
{
  var $import = array(
		      // Person Class
		      "sn"                              => "US_LNAME",
		      "cn"                              => "",
		      "userPassword"                    => "US_PASSWD",
		      "telephonenumber"                 => "US_PHONE",
		      
		      // organizationalPerson
		      "title"           		=> "", 
		      "x121Address"     		=> "", 
		      "registeredAddress"               => "", 
		      "destinationIndicator"            => "", 
		      "preferredDeliveryMethod"         => "", 
		      "telexNumber"                     => "", 
		      "telexTerminalIdentifier"         => "", 
		      "internationaliSDNNumner"         => "", 
		      "facsimiletelephonenumber"        => "US_FAX",
		      "street"                          => "",
		      "postOfficeBox"                   => "",
		      "postalcode"                      => "US_WORKPOSTALCODE",
		      "postalAddress"                   => "US_WORKADDR",
		      "physicalDeliveryOfficeName"      => "",
		      "ou"                              => "",
		      "st"                              => "",
		      "l"                               => "US_WORKTOWN",
		      
		      // InetOrgPerson
		      "audio"                           => "",
		      "businessCategory"                => "",
		      "carLicense"                      => "",
		      "departmentNumber"                => "US_SERV",
		      "displayName"                     => "",
		      "employeeNumber"                  => "US_MATRICULE",
		      "employeeType"                    => "US_TYPE",
		      "givenName"                       => "US_FNAME",
		      "homePhone"                       => "US_HOMEPHONE",
		      "homePostalAddress"               => "",
		      "initials"                        => "US_INITIALS",
		      "jpegPhoto"                       => "US_PHOTO",
		      "labeledURI"                      => "US_WORKWEB",
		      "mail"                            => "US_MAIL",
		      "manager"                         => "",
		      "mobile"                          => "US_MOBILE",
		      "o"                               => "US_SOCIETY",
		      "pager"                           => "",
		      "photo"                           => "",
		      "roomNumber"                      => "US_LOCALISATION",
		      "secretary"                       => "",
		      "uid"                             => "US_WHATID",
		      "userCertificate"                 => "",
		      "x500uniqueIdentifier"            => "",
		      "preferredLanguage"               => "",
		      "userSMIMECertificate"            => "",
		      "userPKCS12"                      => "");
  
  function g_ReadCard(&$tattr) 
    {
      // Read a structure of import file : return array ('name', 'value')
	
	$tattr=array();
      $endCardFound = false;
      $beginCardFound = false;
      $line="";
      
      // search begin of a card : dn line
	while ( (! feof ($this->fd)) &&
	       (! $beginCardFound) )
	  {
	    $line = fgets($this->fd, 4096);
	    $beginCardFound = preg_match("/dn:(.*)/", $line);
	  }
      
      
      // search element of a card until : objectclass
	while ( (! feof ($this->fd)) &&
	       (! $endCardFound) )
	  {
	    if (! $endCardFound)
	      {
		//line like cellphone:05.61.15.54.54
		  if (preg_match("/([a-z;]*):(.*)/", $line, $reg))
		    $tattr[$reg[1]]=$reg[2];
	      }
	    
	    $line = fgets($this->fd, 4096);
	    $endCardFound = preg_match("/objectclass:(.*)/", $line);
	  }
      
      return ( ! feof ($this->fd));
    }
}

?>
