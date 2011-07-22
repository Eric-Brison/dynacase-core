<?php
/**
 * Concatenation of the 2 css file : style and size
 *
 * @author Anakeen 2000 
 * @version $Id: systemcss.php,v 1.1 2006/07/27 16:04:19 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
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