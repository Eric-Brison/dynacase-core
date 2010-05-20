
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

[BLOCK ADDMENUS]
nbmitem['[name]'] += [nbmitem]; 
tdivid['[name]']=tdivid['[name]'].concat([menuitems]);
tdivsmenu['[name]']=tdivsmenu['[name]'].concat([menulabel]);
[ENDBLOCK ADDMENUS]

[BLOCK ADDMENUACCESS]
tdiv['[name]'][[divid]]=tdiv['[name]'][[divid]].concat([vmenuitems]);
[ENDBLOCK ADDMENUACCESS]

