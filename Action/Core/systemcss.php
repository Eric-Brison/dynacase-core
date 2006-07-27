<?php
/**
 * Concatenation of the 2 css file : style and size
 *
 * @author Anakeen 2000 
 * @version $Id: systemcss.php,v 1.1 2006/07/27 16:04:19 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */



function systemcss(&$action) {
  $style = $action->getParam("STYLE","DEFAULT");
  $file=GetParam("CORE_PUBDIR")."/STYLE/$style/Layout/gen.css";
  
  $tstyle=file_get_contents($file);

  
  $size=$action->getParam("FONTSIZE","normal");     
  
  $file=$action->GetParam("CORE_PUBDIR")."/WHAT/Layout/size-$size.css";

  $tsize=file_get_contents($file);

  $action->lay->template=$tstyle."\n".$tsize;

}
?>