
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

// --------------------------------------------------
// $Id: Lib.FileDir.js,v 1.1 2002/01/08 12:41:34 eric Exp $
// --------------------------------------------------
// $Log: Lib.FileDir.js,v $
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.1  2000/10/27 16:27:12  marc
// Creation
//
// Revision 1.3  2000/10/24 18:25:50  marc
// alias name validation added
//
// Revision 1.2  2000/10/24 10:40:03  marc
// email address list RegExp added
//
// Revision 1.1.1.1  2000/10/23 09:12:33  marc
// Initial released
//
//
// --------------------------------------------------
// Check directory filename
function checkdirname(a) {
   var re = /^[\s]*(\/[\w\.-]+)+[\/]?[\s]*$/
   return re.test(a);
}
