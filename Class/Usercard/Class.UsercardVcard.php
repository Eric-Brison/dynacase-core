<?php
/**
 * Class use to import and export VCARD format with FREEDOM USER Family
 *
 * @author Anakeen 2001
 * @version \$Id: Class.UsercardVcard.php,v 1.16 2005/11/23 14:04:20 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage USERCARD
 */


/**
 * Class use to read, import and export VCARD format with FREEDOM USER Family
 *
 */
class UsercardVcard 
{
  var $import = array(
		      "FN" => "",

		      "N" => "US_LNAME;US_FNAME",
		      "N;GIVEN" => "US_FNAME",
		      "N;FAMILY"=> "US_LNAME",		
		      "N;MIDDLE" => "",
		      "N;PREFIX" => "",
		      "N;SUFFIX" => "",
		      "SOUND" => "",
		      "BDAY" => "",
		      "NOTE" => "",
		      "TZ" => "",
		      "GEO" => "",
		      "URL" => "US_WORKWEB",
		      "URL;WORK" => "US_WORKWEB",
		      "PUBKEY" => "",
		      "ORG" => "US_SOCIETY;US_UNIT",
		      "ORG;NAME" => "US_SOCIETY",
		      "ORG;UNIT" => "",
		      "TITLE" => "US_TYPE",
			
		      "ADR;TYPE;WORK" => "",
		      "ADR;TYPE;HOME" => "",
		      "TEL;PREFER" => "",
		      "EMAIL;INTERNET" => "US_MAIL",
		      "EMAIL;INTERNET;WORK" => "US_MAIL",
		      "EMAIL;PREF;INTERNET" => "US_MAIL",
		      "EMAIL;INTERNET;HOME" => "",
			
		      "ADR;WORK" => "0;0;US_WORKADDR;US_WORKTOWN;0;US_WORKPOSTALCODE;US_COUNTRY",
		      "ADR;WORK;STREET" => "US_WORKADDR",
		      "ADR;WORK;LOCALITY" => "US_WORKTOWN", 
		      "ADR;WORK;REGION" => "", 
		      "ADR;WORK;POSTALCODE" => "US_WORKPOSTALCODE",
		      "ADR;WORK;COUNTRYNAME" => "US_COUNTRY",
		      "EXT" => "",
		      "LABEL" => "",

		      "ADR;HOME" => "0;0;320;325;0;322",
		      "ADR;HOME;STREET" => "320",
		      "ADR;HOME;LOCALITY" => "325",
		      "ADR;HOME;REGION" => "",
		      "ADR;HOME;POSTALCODE" => "322",
		      "ADR;HOME;COUNTRYNAME" => "",
			
		      "TEL;WORK" => "US_PPHONE",
		      "TEL;WORK;VOICE" => "US_PPHONE",
		      "TEL;HOME" => "",
		      "TEL;VOICE" => "",
		      "TEL;FAX" => "US_PFAX",
		      "TEL;WORK;FAX" => "US_PFAX",
		      "TEL;MSG" => "",
		      "TEL;CELL" => "US_MOBILE",
		      "TEL;CELL;VOICE" => "US_MOBILE",
		      "TEL;PAGER" => "",
		      "TEL;BBS" => "",
		      "TEL;MODEM" => "",
		      "TEL;CAR" => "",
		      "TEL;ISDN" => "",
		      "TEL;VIDEO" => "",
		      "EMAIL;WORK" => "",
		      "EMAIL;HOME" => "");
  
  var $mime_type = "text/x-vcard";
  var $ext = "vcf";

  // --------------------------------------------------------------------
  function Open($filename, $mode="r") {
    // Open import/export file : return file descriptor
     $this->fd = fopen($filename,$mode);
     return ($this->fd);
 
  }

  function Close() {
    // Close import file
    if ($this->fd)
      fclose($this->fd);
 
  }
  function ReadCard(&$tattr) 
    {
      // Read a structure of import file : return array ('name', 'value')
    
      $tattr=array();
      $endCardFound = false;
      $beginCardFound = false;
      $line="";

      // search begin of a card : BEGIN:VCARD
      while ( (! feof ($this->fd)) &&
	      (! $beginCardFound) )
	{
	  $line = fgets($this->fd, 4096);
	  $beginCardFound = preg_match("/BEGIN:VCARD(.*)/", $line);
	}
    

      // search element of a card until : END:VCARD
      while ( (! feof ($this->fd)) &&
	      (! $endCardFound) )
	{
	  $line = fgets($this->fd, 4096);
	  $endCardFound = preg_match("/END:VCARD(.*)/", $line);
	  if (! $endCardFound)
	    {
	      if (preg_match("/([A-Z;]*);ENCODING=QUOTED-PRINTABLE:(.*)/", $line, $reg)){
		$tattr[$reg[1]]=utf8_encode(quoted_printable_decode(rtrim($reg[2])));
	      } elseif (preg_match("/([A-Z;]*);CHARSET=UTF-8:(.*)/", $line, $reg)){
		  $tattr[$reg[1]]=rtrim($reg[2]);
	      } elseif (preg_match("/([A-Z;]*):(.*)/", $line, $reg)){
	      //line like TEL;WORK:05.61.15.54.54
		  $tattr[$reg[1]]=str_replace("\\n","\n",rtrim($reg[2]));
	      }
	    }
    
	}

      return ( ! feof ($this->fd));
    }  
  // --------------------------------------------------------------------
  function Read(&$tattr) {
    // Read import file : return attribute object
 

    if ($cr = $this ->ReadCard($tbrut))
      {
	
	$tattr=array();
	while(list($k,$v) = each($tbrut)) 
	  {
		
	    if (isset($this->import[$k]) && ($this->import[$k] != ""))
	      {		
	
		// if is single value (no regexp)
		if (preg_match("/^[0-9A-Z_]*$/",$this->import[$k]))
		  {
		    // suppress http
		    if ($this->import[$k] == "US_WORKWEB") $tattr[$this->import[$k]]=str_replace("http://","",$v);
		    else $tattr[$this->import[$k]]=$v;

		  }
		else
		  {
		    // regexp case
		      // example A;B;C;D;E;F
			$complxreg="/([^;]*)[;]{0,1}([^;]*)[;]{0,1}([^;]*)[;]{0,1}([^;]*)[;]{0,1}([^;]*)[;]{0,1}([^;]*)[;]{0,1}/";
		   
		    if (preg_match($complxreg,
			     $this->import[$k], $reg))
		      { 
			if (preg_match($complxreg, $v , $regv))
			  {
			    for ($ir=1;$ir<7;$ir++) {
			      if ($reg[$ir] == "US_WORKWEB") $tattr[$reg[$ir]]=str_replace("http://","",$regv[$ir]);
			      else $tattr[$reg[$ir]]= $regv[$ir];
			    }
			  }
		      }
		  }
	      }
	  }
      }
    return ($cr);
  }
  function WriteCard($title,$tattr) 
    {
      // Write a structure in export file
	
	fputs($this->fd,"BEGIN:VCARD\n");
      fputs($this->fd,"FN:".chop($title)."\n");
      reset($this->import);
      
      while(list($k,$v) = each($this->import))
	{
	  if ($v != "")
	    {
	      $v=strtolower($v);
	      if (isset($tattr[$v]))
		fputs($this->fd,$k.":".str_replace("\n","\\n",$tattr[$v])."\n");
	      
	      else { // multi fields
		       $lidattr = explode(";", $v);
		     if ((is_array($lidattr)) && (count($lidattr) > 1)){
		       fputs($this->fd,"$k:");
		       while(list($k2,$idattr) = each($lidattr)) {
			 
			 if (isset($tattr[$idattr])) fputs($this->fd,str_replace("\n","\\n",$tattr[$idattr]));
			 if ($k2 < count($lidattr) - 1) fputs($this->fd,";");
		       }
		       fputs($this->fd,"\n");
		     }
		   }
	      
	    }
	}
      fputs($this->fd,"END:VCARD\n\n");
    }
}

?>
