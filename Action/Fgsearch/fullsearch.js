/**
 * Full Text Search document
 * 
 * @author Anakeen
 * @version $Id: $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero
 *          General Public License
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
		// form.submit();
	}
}
function viewUrlFromSearch(event, url) {
	console.log(buttonNumber(event));
	var bn=buttonNumber(event);
	if ((bn == 2 )||(bn == 4 ))  {
		window.open(url,'_blank');
	} else {

		var idiv=document.getElementById('divdoc');
		var ifr=document.getElementById('detaildoc');
		var dr=document.getElementById('dresult');
		var ds=window.parent.document.getElementById('dsearch');
		var fe=document.getElementById('fedit');

		ifr.src=url;
		idiv.style.display='';
		// dr.style.display='none';
		dr.style.opacity='0.5';
		fe.style.opacity='0.5';
		fe.className='dark';
		if (ds) ds.style.display='none';
	}
}
function hideUrlFromSearch() {

	var idiv=document.getElementById('divdoc');
	var ifr=document.getElementById('detaildoc');
	var dr=document.getElementById('dresult');

	var ds=window.parent.document.getElementById('dsearch');
	var fe=document.getElementById('fedit');

	ifr.src='';
	idiv.style.display='none';
	// dr.style.display='none';
	dr.style.opacity='';
	fe.style.opacity='';
	fe.className='';
	if (ds) ds.style.display='';
	
}

function buttonNumber(event) {
	// if (event) return event.button +1;
	if (! event) return event = window.event;
	if( typeof( event.which ) == 'number' ) {
		//Netscape compatible
		return   event.which;
	} else if( typeof( event.button ) == 'number' ) {
		//DOM
		return   event.button;
	} else {
		//total failure, we have no way of obtaining the button
	}

	return 0;
}

function resizeiframe(event) {
  if (document.getElementById("dresult")) {
    var fh=getFrameHeight();
    var xy=getAnchorPosition("dresult");
    var nh=fh-xy.y-5;
    var ds=window.parent.document.getElementById('dsearch');
    var xydetail=getAnchorPosition("dresult");
    var nhdetail=fh-xydetail.y;
    if (ds) nhdetail-=50;
    if (nhdetail> 150) document.getElementById("detaildoc").style.height=(nhdetail)+'px';
    if (nh> 100) document.getElementById("dresult").style.height=nh+'px';
    // alert(xy.y+' - '+fh+' - '+nh);
  }
}
