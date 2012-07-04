/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

// to adjust height of body in edit card in fixed positionning
function fixedPosition() {
    var fspan = document.getElementById('fixspanhead');
    var ftable = document.getElementById('fixtablehead');
    var xy;
    var h;

    if (isIE && ((document.body.scrollHeight) <= document.body.clientHeight)) {
        if (fspan && ftable) {
            ftable.style.position = 'static';
            fspan.style.display = 'none';
        }
        fspan = document.getElementById('fixspanfoot');
        ftable = document.getElementById('fixtablefoot');
        if (fspan && ftable) {
            ftable.style.position = 'static';
            fspan.style.display = 'none';
        }
    } else {
        if (fspan && ftable) {
            xy = getAnchorPosition(ftable.id);
            h = parseInt(getObjectHeight(ftable)) - xy.y;
            if (h > 0) {
                fspan.style.height = parseInt(getObjectHeight(ftable)) + 'px';
                fspan.style.top = xy.y + 'px';
            }
        }
        fspan = document.getElementById('fixspanfoot');
        ftable = document.getElementById('fixtablefoot');

        if (fspan && ftable) {
            fspan.style.height = parseInt(getObjectHeight(ftable)) + 'px';
        }
    }

}

if (isNetscape) addEvent(window, "load", fixedPosition);
