<?php
// ---------------------------------------------------------------
//
// $Id: libphp.php,v 1.1 2002/01/08 12:41:34 eric Exp $
// (c) anakeen 1999       marc.claverie@anakeen.com
//                    yannick.lebriquer@anakeen.com
//                   marianne.lebriquer@anakeen.com
//
// $Log: libphp.php,v $
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.1  2001/02/10 09:56:49  yannick
// Ajout de vieilles classes
//
// Revision 1.6  2000/07/29 10:30:05  marc
// Syntax error
//
// Revision 1.5  2000/07/05 13:19:59  yannick
// Mise au point
//
// Revision 1.4  1999/12/06 21:31:47  marc
// Define pour inclusion multiple
//
// Revision 1.3  1999/12/02 10:47:02  marc
// Ahout de la fonction libphpshowvar
//
//
// Include libphp and control if they are not already included 
//  Should be included at the top level of your Php app
//  All calls to libphpinclude should be done outside functions or class
//  definition
// ---------------------------------------------------------------

$LIBPHP_PHP = "";
$LEVEL="";

function libphpinclude($module) {

  $defname = strtoupper($module);
  $defname = strtr($defname,".","_");

  global $$defname;
  global $LEVEL;
  if (!isset($$defname)) {
    include($module);
  }
}

function libphpshowvar($name, $var, $f=1)
{
  $out="";
  if ($f) $out = $out . '<font face="sans-serif" size="-1">';
  $out = $out . '<table width="100%" border="'. $f
    .'" align="center" cellpadding="0" border="0" cellspacing="0">';
  $out = $out . '<tr valign="top">';
  $out = $out . '<td width="20%" align="left" bgcolor="#CCFFEE">'.$name.'</th>';
  if (!isset($var)) {
    $out = $out . '<td width="10%" align="left">-- none --</th>';
    $out = $out . '<td width="70%" align="left">-- no value --</th>';
  } else {
    $out = $out . '<td width="10%" align="left">'.gettype($var).'</th>';
    $out = $out . '<td width="70%" align="left">';
    if (is_array($var)) {
      while (list($k, $v) = each($var)) {
	$out = $out . libphpshowvar($k, $v, 1);
      }
      reset($var);
    } else {
      if (strlen(strval($var))>0) 
	$out = $out . strval($var);
      else
	$out = $out . "-- empty --";
    }
    $out = $out . '</td>';
  }
  $out = $out . '</tr>';
  $out = $out . '</table>';
  if ($f) $out = $out . '</font>';
  return($out);
}


?>
