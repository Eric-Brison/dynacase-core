
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */


document.menuclosed=true; // use to avoid autoclose when inputs in menu

function closeAllMenu() {
  if (document.menuclosed) {
[BLOCK CMENUS]  closeMenu('[name]');
[ENDBLOCK CMENUS]
  }

  document.menuclosed=true;
  unSelectMenu();
}
[BLOCK MENUS]
nbmitem["[name]"] =[nbmitem]; 
tdiv["[name]"]= new Array([nbdiv]);
tdivid["[name]"]=[menuitems];
tdivsmenu["[name]"]=[menulabel];
[ENDBLOCK MENUS]

[BLOCK MENUACCESS]
tdiv["[name]"][[divid]]=[vmenuitems];
[ENDBLOCK MENUACCESS]


[BLOCK ADDMENUS]
nbmitem["[name]"] += [nbmitem]; 
tdivid["[name]"]=tdivid["[name]"].concat([menuitems]);
[ENDBLOCK ADDMENUS]

[BLOCK ADDMENUACCESS]
tdiv["[name]"][[divid]]=tdiv["[name]"][[divid]].concat([vmenuitems]);
alert(tdivid["[name]"].toString());
[ENDBLOCK ADDMENUACCESS]

var fdl_hd2size=parseInt('[FDL_HD2SIZE]');
var fdl_vd2size=parseInt('[FDL_VD2SIZE]');


if (fdl_hd2size == 0) fdl_hd2size=400;
if (fdl_vd2size == 0) fdl_vd2size=300;

addEvent(document,"keydown",trackMenuKey);
