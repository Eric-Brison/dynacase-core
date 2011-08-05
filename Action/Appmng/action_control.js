
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

function Valid_Send()
{
  

  sendform();
  return true;
}
function setopenertarget(o) {
  if (o) {
    var f=o.form;
    if (window.opener) {
      var n=window.opener.name;
      if (n) f.target=n;
    }
  }
}
