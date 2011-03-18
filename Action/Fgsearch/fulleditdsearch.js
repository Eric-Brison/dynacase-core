
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

var SEARCHWIDTH=false;

function maskfilter() {  
  maskifyfilter('none');
}
function unmaskfilter() {
  maskifyfilter('');
}
function maskifyfilter(vis) {
  var co=document.getElementById('conditions');
  co.style.display=vis;
  co=document.getElementById('famselection');
  co.style.display=vis;
  co=document.getElementById('amask');
  co.style.display='none';
  co=document.getElementById('aunmask');
  co.style.display='none';
  if (vis=='') {
    co=document.getElementById('dsearch');
    co.style.borderStyle='';
    //co.style.width='auto';
    co.style.width=SEARCHWIDTH;
    co=document.getElementById('amask');
    co.style.display='';
  } else {
    co=document.getElementById('dsearch');
    SEARCHWIDTH=co.style.width;
    co.style.width='40px';        
    co.style.borderStyle='none';
    co=document.getElementById('aunmask');
    co.style.display='';
  }
}

function addfilter(newtr,cible) {  
  var  co=document.getElementById('conditions');
  var  se=document.getElementById('dsearch');
  var w1=getObjectWidth(co);
  var r1=co.style.right;
  addrow(newtr,cible);

  var w2=getObjectWidth(co);
  if ((w2-w1)>0) {
    se.style.width=(w2+20)+'px';
  }
}

function filterfuncz(o) {
  var  co=document.getElementById('conditions');
  var  se=document.getElementById('dsearch');
  var w1=getObjectWidth(co);
  var r1=co.style.right;
  filterfunc(o);
  var w2=getObjectWidth(co);
  if ((w2-w1)>0) {
    se.style.width=(w2+20)+'px';
  }
}

function resizeiframedetail(event) {
	  if (document.getElementById("searchresult")) {
	    var fh=getFrameHeight();
	    var xy=getAnchorPosition("searchresult");
	    var nh=fh-xy.y-5;

	    if (nh> 100) document.getElementById("searchresult").style.height=nh+'px';
	     //  alert('EDIT:'+xy.y+' - '+fh+' - '+nh);
	  }
	}
 
