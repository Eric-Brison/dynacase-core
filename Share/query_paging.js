
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */



function set_form_par(form,key,val,sub) {
   self.document[form].key.value=key;
   if (key == 'all') {
     self.document[form].submit();
     return;
   }
   self.document[form][key].value=val;
   if (sub == 1) {
     self.document[form].submit();
   }
}
