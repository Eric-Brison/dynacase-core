/**
 * Full Text Search document
 *
 * @author Anakeen
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */

function sendNextSearch() {
	var form=document.searchdoc;
	if (form) {
		form.target='nextresult';
		form.page.value=parseInt(form.page.value)+1;
		form.submit();
	}
}
function sendNewSearch() {
	var form=document.searchdoc;
	if (form) {
		form.target='';
		form.page.value=0;
		//form.submit();
	}
}
