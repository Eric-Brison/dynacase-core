
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

// $Id: f-accordeon.js,v 1.2 2008/11/13 17:44:37 marc Exp $

include_js('/freedom/FDC/Layout/inserthtml.js');
   

function getUtilWidth(elt) {
  var rw = 0;
  rw += parseInt($(elt).getWidth()); 
  rw -= getsz($(elt), 'padding-left'); 
  rw -= getsz($(elt), 'padding-right'); 
  rw -= getsz($(elt), 'border-left-width'); 
  rw -= getsz($(elt), 'border-right-width'); 
  return rw;
}
function getUtilHeight(elt) {
  var rw = 0;
  rw += parseInt($(elt).getHeight()); 
  rw -= getsz($(elt), 'padding-top'); 
  rw -= getsz($(elt), 'padding-bottom'); 
  rw -= getsz($(elt), 'border-top-width'); 
  rw -= getsz($(elt), 'border-bottom-width'); 
  return rw;
}
function getsz(bid, dim) {
  var s = $(bid).getStyle(dim);
  return parseInt((s==null?0:s));
}


Faccordeon.prototype.computeUtilHeight = function() {
  var titleh = 0;
  for (var i=0; i<this.items.length; i++) {
    titleh += parseInt($('b:'+this.pnum+':'+i).getHeight()); 
    titleh += getsz($('b:'+this.pnum+':'+i),'padding-top');
    titleh += getsz($('b:'+this.pnum+':'+i),'padding-bottom');
    titleh += getsz($('b:'+this.pnum+':'+i),'border-top-width');
    titleh += getsz($('b:'+this.pnum+':'+i),'border-bottom-width');
  }
  var bx = getUtilHeight($(faccordeons[this.pnum].box.id));
  return parseInt(bx - titleh);
}

function setAllAccordeonBoxSize() {
  var tb;
  var cxy;
  var h = document.viewport.getHeight();
  var delta=15;
  if (isIE) delta=25;
  var ipi;
  
  for (var i=0; i<faccordeons.length; i++) {
    faccordeons[i].content.setBoxSize();
  }
}

Faccordeon.prototype.setBoxSize = function() {
  var a;
  var fac = $(faccordeons[this.pnum].box);
  fac.style.height = getUtilHeight($(fac.parentNode))+'px'; 
  $(fac).style.width = getUtilWidth($(fac.parentNode))+'px'; 
  this.contentHeight = this.computeUtilHeight();
  for (var ii=0; ii<this.items.length; ii++) {
    a = $('c:'+this.pnum+':'+ii);
    if (a) a.setStyle( { 'height' : this.contentHeight+'px' } );
  }
}


function showItem (e) {
    var targ;
    if (!e) var e = window.event;
    if (e.target) targ = e.target;
    else if (e.srcElement) targ = e.srcElement;
    var ctrlKey = e.ctrlKey;
    
    var pid = targ.id.split(":");
    faccordeons[pid[1]].content.closeAllItems();
    
    faccordeons[pid[1]].content.openItem(pid[2], ctrlKey);
}

var faccordeons = new Array();

function Faccordeon() {
    this.pnum = faccordeons.length;
    this.items = new Array();
    this.css = { 'itemTitleOpen'  : 'faccordeon-title-open',
		 'itemTitleClose' : 'faccordeon-title',
		 'itemContent'    : 'faccordeon-content' };
    var pb = document.createElement('div');
    pb.id = 'box:'+this.pnum;
    pb.className = 'faccordeon';
    pb.style.border='0px solid blue';
    pb.style.padding = '0px';
    pb.style.margin = '0px';
    faccordeons[this.pnum] = { 'box' : pb, 'content' : this };
}

Faccordeon.prototype.openItem = function(item, forcereload) {
     var bit = 'b:'+this.pnum+':'+item;
     var cit = 'c:'+this.pnum+':'+item;
     if (this.items[item].data.content && this.items[item].data.content!='') {
	 $(cit).innerHTML = this.items[item].data.content;
     } else if (this.items[item].data.url && this.items[item].data.url!='') {
       if (this.items[item].data.reload || !this.items[item].data.loaded || (this.items[item].data.loaded && forcereload)) {
	 enableSynchro();
	 try {
	   requestUrlSend($(cit), this.items[item].data.url);
	 } catch(exception) {
	   $(cit).innerHTML = exception;
	 }
	 disableSynchro();
	 this.items[item].data.loaded = true;
       }
     } else {    
	 $(cit).innerHTML = '-- empty --';
     }
     $(cit).setStyle({  'display':'block' }); 
     $(cit).className = this.css.itemContent;
     $(bit).className = this.css.itemTitleOpen;
     this.items[item].opened = true;
}
    
Faccordeon.prototype.closeAllItems = function() {
    for (var i=0; i<this.items.length; i++) {
	var idc = 'c:'+this.pnum+':'+i;
	var idt = 'b:'+this.pnum+':'+i;
	if (!document.getElementById(idc)) continue;
	document.getElementById(idc).style.display = 'none';
	document.getElementById(idt).className = this.css.itemTitleClose;
	this.items[i].opened = false;
    }
}
	
Faccordeon.prototype.display = function(father)  {
    if (!document.getElementById(father)) return;
    var fe = document.getElementById(father);
    var nitp = null;
    var ntitle = null;
    var ncontent = null;
    for (var i=0; i<this.items.length; i++) {
      nitp = document.createElement('div');
      nitp.id = 'p:'+this.pnum+':'+i;
      nitp.style.display = 'block';
      nitp.className = (i==0?' start':'')+(i==this.items.length-1?' end':'');
      ntitle = document.createElement('div');
      ntitle.id = 'b:'+this.pnum+':'+i;
      ntitle.className = this.css.itemTitleClose;
      ntitle.innerHTML = this.items[i].title;
      ntitle.style.display = 'block';
      nitp.appendChild(ntitle);
      addEvent(ntitle,'click',showItem);
      ncontent = document.createElement('div');
      ncontent.id = 'c:'+this.pnum+':'+i;
      ncontent.innerHTML = ''; //empty<hr>'+this.items[i].title;
      ncontent.style.display = 'none';
      nitp.appendChild(ncontent);
      faccordeons[this.pnum].box.appendChild(nitp);
    }
    faccordeons[this.pnum].box.style.height =  $(fe).getHeight(); //'100%';
    faccordeons[this.pnum].box.style.width = $(fe).getWidth();
    fe.appendChild(faccordeons[this.pnum].box);
    this.setBoxSize();
    this.openItem(0);
    addEvent(window,'resize',setAllAccordeonBoxSize); 
}

    
Faccordeon.prototype.addItem = function(title, rcallback) {
    this.items[this.items.length] = {
	'title' : title,
	'data'  : rcallback
    };
    return;
}

