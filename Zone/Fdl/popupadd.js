
/**
 * @author Anakeen
 */

[BLOCK ADDMENUS]
nbmitem['[name]'] += [nbmitem]; 
tdivid['[name]']=tdivid['[name]'].concat([menuitems]);
tdivsmenu['[name]']=tdivsmenu['[name]'].concat([menulabel]);
[ENDBLOCK ADDMENUS]

[BLOCK ADDMENUACCESS]
tdiv['[name]'][[divid]]=tdiv['[name]'][[divid]].concat([vmenuitems]);
[ENDBLOCK ADDMENUACCESS]

