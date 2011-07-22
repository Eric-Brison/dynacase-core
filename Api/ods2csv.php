<?php
/**
 * Convert OpenDocument Spreadsheet to csv (semicolon)
 *
 * @author Anakeen 2000 
 * @version $Id: ods2csv.php,v 1.8 2008/12/02 15:22:32 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */
define ("SEPCHAR", ';');
define ("ALTSEPCHAR", ' --- ');

$inrow=false;
$incell=false;
$nrow=0;
$ncol=0;
$rows=array();
$colrepeat=0;
function startElement($parser, $name, $attrs) {
  global $rows,$nrow,$inrow,$incell,$ncol,$colrepeat,$celldata;
  if ($name ==  "TABLE:TABLE-ROW") {
    $inrow=true;
    if (isset($rows[$nrow])) {
      // fill empty cells
      $idx=0;
      foreach ($rows[$nrow] as $k=>$v) {
	if (! isset($rows[$nrow][$idx])) $rows[$nrow][$idx]='';
	$idx++;
      }
      ksort($rows[$nrow],SORT_NUMERIC);
    }
    $nrow++;
    $ncol=0;
    $rows[$nrow] = array();
  }
  
  if ($name ==  "TABLE:TABLE-CELL") {
    $incell=true;
    $celldata = "";
    if ($attrs["TABLE:NUMBER-COLUMNS-REPEATED"]) {
      $colrepeat=intval($attrs["TABLE:NUMBER-COLUMNS-REPEATED"]);      
     }
  }
  if ($name ==  "TEXT:P") {
    if (strlen($rows[$nrow][$ncol]) > 0) $rows[$nrow][$ncol].='\n';
  }
}

function endElement($parser, $name) {
  global $rows,$nrow,$inrow,$incell,$ncol,$colrepeat,$celldata;
  if ($name ==  "TABLE:TABLE-ROW") {
    // Remove trailing empty cells
    $i = $ncol - 1;
    while( $i >= 0 ) {
      if( strlen($rows[$nrow][$i]) > 0 ) {
	break;
      }
      $i--;
    }
    array_splice($rows[$nrow], $i+1);
    $inrow=false;
  }

  if ($name ==  "TABLE:TABLE-CELL") {
    $incell=false;

    $rows[$nrow][$ncol] = $celldata;

    if ( $colrepeat > 1 ) {
      $rval=$rows[$nrow][$ncol];
      for ($i=1;$i<$colrepeat;$i++) {
	$ncol++;
	$rows[$nrow][$ncol]=$rval;
      }
      }
      //$ncol+=intval($attrs["TABLE:NUMBER-COLUMNS-REPEATED"]);
    
    $ncol++;
    $colrepeat=0;
  }

  
}

function characterData($parser, $data)
{
  global $rows,$nrow,$inrow,$incell,$ncol,$celldata;
  if ($inrow && $incell) {
    $celldata.=preg_replace('/\s/u', ' ', preg_replace(
      '/^\s*[\r\n]\s*$/ms', '', str_replace(SEPCHAR,ALTSEPCHAR,$data)
    ));
  }
  //  print $data. "- ";
}


function xmlcontent2csv($xmlcontent,&$fcsv) {
  global $rows;
  $xml_parser = xml_parser_create();
  // Utilisons la gestion de casse, de maniere a etre surs de trouver la balise dans $map_array
  xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
  xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 0);
  xml_set_element_handler($xml_parser, "startElement", "endElement");
  xml_set_character_data_handler($xml_parser, "characterData");
 

 
    if (!xml_parse($xml_parser,$xmlcontent )) {
      return (sprintf("error XML : %s line %d",
		  xml_error_string(xml_get_error_code($xml_parser)),
		  xml_get_current_line_number($xml_parser)));
    }
  
  xml_parser_free($xml_parser);
  //print_r($rows);
  foreach ($rows as $k=>$row) {
    $fcsv.= implode(SEPCHAR,$row)."\n";
  }
}

function ods2content($odsfile,&$content) {
  if (! file_exists($odsfile)) return "file $odsfile not found";
  $cibledir=uniqid(getTmpDir()."/ods");
  
  $cmd = sprintf("unzip -j %s content.xml -d %s >/dev/null", escapeshellarg($odsfile), escapeshellarg($cibledir) );
  system($cmd);
  
  $contentxml=$cibledir."/content.xml";
  if (file_exists($contentxml)) {
    $content=file_get_contents($contentxml);
    unlink($contentxml);
  }
  
  rmdir($cibledir);
}

$odsfile = GetHttpVars("odsfile"); // file ods (input)
$csvfile = GetHttpVars("csvfile"); // file xml (output)

if ($odsfile=="") {
  print "odsfile needed :usage  --odsfile=<ods file> [--csvfile=<csv file output>]\n";
  return;
 }

$err=ods2content($odsfile,$content);
if ($err == "") {
  $err=xmlcontent2csv($content,$csv);
  if ($err=="") {
    if ($csvfile) {
      $n=file_put_contents($csvfile,$csv);
      if ($n > 0) print sprintf(_("csv file <%s> wroted")."\n",$csvfile);
      else $err=sprintf(_("cannot write  %s"),$csvfile);
    } else print $csv;
  }
 }
if ($err != "") print "ERROR:$err\n";


?> 