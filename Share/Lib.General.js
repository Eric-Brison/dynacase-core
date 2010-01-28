
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

// --------------------------------------------------
// $Id: Lib.General.js,v 1.1 2002/01/08 12:41:34 eric Exp $
// --------------------------------------------------
// $Log: Lib.General.js,v $
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.1  2000/11/06 20:19:39  marc
// Creation de Generals (checkint)
// Ajout des scrollbars dans les fenetres complementaires
//
//
// --------------------------------------------------
// Check date and time format
//
// Check for an unsigned integer.
// ------------------------------
function checkint(val, min, max) {
  var reint = /^[\s]*[\d]+[\s]*$/;
  if (val == "" ) return false;
  if (reint.test(val)) {
    if (min != -1 && val<min) return false;
    if (max != -1 && val>max) return false;
  } else {
    return false;
  }
  return true;
}
