<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * For TEST widget menu Calendar
 *
 * @author Anakeen 2005
 * @version $Id: menu.php,v 1.2 2005/11/24 13:47:51 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
?>
<html>

<head>
<script type="text/javascript" src="/test/mcallib.js"></script>
<script type="text/javascript" src="/test/mcalmenu.js"></script>
</head>
<body>
<script type="text/javascript">

function testMenu() {
  alert('Dans la sonction');
}
    // label  : ...
    // status : 0: hidden, 1:Inactif, 2:Actif
    // type   : 0 : title 1 : menu item 2 : separator
    // icon   : icon relative path 

    // onmouse : 0 : none 1 onclick 2 on shiftclick 3 : on ctrl-click

    // amode   : 0:display event action 1=http 2=javascript
    // atarget : target for http (like target for <a>)
    // ascript : url (http) or fonction (javascript)
    // aevent  : 0 : none, 1 :reload event; 2 : delete event, 3 reload calendar

   var m1 = [ 
     { label:"titre du menu", status:1, type:0 },
     { label:"item 1", desc:'Avec un description plus longue',  status:1, type:1, eaction:0, icon:"defico.png", amode:0, atarget:"_blanck", ascript:"http://127.0.0.1", aevent:0 },
     { label:"item 2", desc:'Avec un description plus longue',  status:2, type : 1, eaction : 0, icon : "defico.png", amode:0, atarget:"_blanck", ascript:"http://127.0.0.1/", aevent:0 },
     { type : 2 },
     { label : "item 3", desc:'Avec un description plus longue', status : 2, type : 1, eaction : 0, amode:0, atarget:"_blanck", ascript:"http://127.0.0.1/?", aevent:0 },
     { label : "item 4", desc:'Avec un description plus longue', onmouse:1, status : 2, type : 1, eaction : 0, icon : "defico.png", amode:1, atarget:"_blanck", ascript:testMenu, aevent:0 },
     ];
var m2 = [ 
  { label:"Menu2", status:1, type:0 },
  { label : "item 1", desc:'Avec un description plus longue',  status : 2, type : 1, eaction : 0, amode:0, atarget:"_blanck", ascript:"http://127.0.0.1/?", aevent:0 },
  { label : "item 2", desc:'Avec un description plus longue',  status : 2, type : 1, eaction : 0, amode:0, atarget:"_blanck", ascript:"http://127.0.0.1/?", aevent:0 },
  { label : "item 3", desc:'Avec un description plus longue',  status : 2, type : 1, eaction : 0, icon : "defico.png", amode:1, atarget:"_blanck", ascript:testMenu, aevent:0 },
  { label : "item 4", desc:'Avec un description plus longue',  status : 2, type : 1, eaction : 0, icon : "defico.png", amode:1, atarget:"_blanck", ascript:testMenu, aevent:0 },
  { type : 2 },
  { label : "item 5", desc:'Avec un description plus longue',  status : 2, type : 1, eaction : 0, amode:0, atarget:"_blanck", ascript:"http://127.0.0.1/?", aevent:0 },
  { label : "item 6", desc:'Avec un description plus longue',  status : 2, type : 1, eaction : 0, icon : "defico.png", amode:1, atarget:"_blanck", ascript:testMenu, aevent:0 },
  { label : "item 7", desc:'Avec un description plus longue',  status : 1, type : 1, eaction : 0, icon : "defico.png", amode:0, atarget:"_blanck", ascript:"http://127.0.0.1", aevent:0 },
  { label : "item 8", desc:'Avec un description plus longue',  status : 2, type : 1, eaction : 0, icon : "defico.png", amode:0, atarget:"_blanck", ascript:"http://127.0.0.1/", aevent:0 },
  ];

var mt1 = new MCalMenu('ev1', m1); mt1.create();
//mt1.attachToElt('mev1');
var mt2 = new MCalMenu('ev2', m2);
mt2.setColor('#000081', '#E9E3FF', '', '#C2C5F9', 'white', '#000081');
mt2.create();
mt2.attachToElt('mev2'); 
</script>
    <div id="mev1" style="position:absolute; z-index:100; left:100; top:100; width:100; height:100; background:blue" oncontextmenu="MCalMenu.showMenu(event, mt1.menuId)">Click</div>
    <div id="mev2" style="position:absolute; z-index:100; left:300; top:100; width:100; height:100; background:blue">Click</div>

<script type="text/javascript">
mt2.attachToElt('mev2'); 
</script>
</body>
</html>
