<?php
/**
 * For test widget maclendar
 *
 * @author Anakeen 2005
 * @version $Id: mcalendar_detail.php,v 1.3 2005/11/24 13:47:51 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
global $_GET;
$id = $_GET["id"];
//  sleep(1);
$t[] = "Ceci est le titre complet de l'évènement $id";
$t[] = "qsdqsd qsdsd qdqsdqsdm lqkqqmskld qùmdlkùdmklqdùmqmqdk qddjlksq qd";
foreach ($t as $k => $v) echo '<div>'.htmlentities($v).'</div>';
?>
