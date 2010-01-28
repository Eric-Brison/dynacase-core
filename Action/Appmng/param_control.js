
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

function Valid_Send()
{
  var reLog = /^[\w-]+(\.[\w-]+)?$/
  var ok=true;
  if ( (document.edit.name.value == "") ||
       (document.edit.val.value == "") )

  {
    alert("Tous les champs sont obligatoires !!!");
    return false;
  }

  sendform();
  if (document.edit.id.value == "") {
    return true;
  } else {
    self.close();
  }
}
