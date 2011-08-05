
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */


// This source IS NOT DISTRIBUTED UNDER FREE LICENSE (like GPL or Artistic...)
// For any usage -commercial, private or other- you have to pay Marc Claverie.


// --------------------------------------------------------------------------------------
// Initialize menu 'mid', menu contains item description
//
// mid : alphnum string
// menu : array of object { label, status, type, icon, onmouse, amode, atarget, ascript, aevent }
//
// label    : item displayed in menu
// desc     : description displayed wher mouse is over item (like html title attribute)
// status   : 0: Hidden, 1:Inactif, 2:Actif
// type     : 0 : title 1 : menu item 2 : separator
// icon     : icon relative path 
// onmouse  : 0 : none 1 onclick 2 on shiftclick 3 : on ctrl-click
//            used for attach menu to an element
// amode    : 0=http 1=javascript
// atarget  : target for http (like target for <a>)
// ascript  : url (http) or fonction (javascript)
// aevent   : 0 : none, 1 :reload event; 2 : delete event, 3 reload calendar

function MCalMenu( mid, menu, style ) {

  if (!document.__mcalmenus) document.__mcalmenus = new Array;

  if (document.__mcalmenus[mid]) {

    this.zIndex = document.__mcalmenus[mid].zIndex;
    this.menuReactivity = document.__mcalmenus[mid].menuReactivity;
    this.setIcons = document.__mcalmenus[mid].setIcons;
    this.menuWidth = document.__mcalmenus[mid].menuWidth;
    this.bgTitleColor = document.__mcalmenus[mid].bgTitleColor;
    this.bgColor = document.__mcalmenus[mid].bgColor;
    this.bgAltColor = document.__mcalmenus[mid].bgAltColor;
    this.color = document.__mcalmenus[mid].color;
    this.titleColor = document.__mcalmenus[mid].titleColor;
    this.altColor = document.__mcalmenus[mid].altColor;
    this.itemFont = document.__mcalmenus[mid].itemFont;
    this.itemFontSize = document.__mcalmenus[mid].itemFontSize;
    this.menuHeight = document.__mcalmenus[mid].menuHeight;
    this.menuItem = document.__mcalmenus[mid].menuItem;
    this.xBorder = document.__mcalmenus[mid].xBorder;
    this.yBorder = document.__mcalmenus[mid].yBorder;
    this.border = document.__mcalmenus[mid].border;
    this.param = document.__mcalmenus[mid].param;
    this.menuId = mid;

  } else {

    // param
    this.zIndex = 5000;
    this.menuReactivity = 500;
    this.setIcons = true;
    this.menuWidth = (this.setIcons?90:70);
    
    this.bgTitleColor = "#000081";
    this.bgColor =      "#E9E3FF";
    this.bgAltColor =   "#C2C5F9";
    this.color =        "#000081";
    this.titleColor =   "white";
    this.altColor =     "white";
    this.itemFont = 'Tahoma,Arial,Helvetica,sans-serif';
    this.itemFontSize = 9;
    if (style) {
      if (style.tbg && style.tbg!='') this.bgTitleColor =  style.tbg;
      if (style.bg && style.bg!='') this.bgColor =  style.bg;
      if (style.abg && style.abg!='') this.bgAltColor =  style.abg;
      if (style.tfg && style.tfg!='') this.titleColor =  style.tfg;
      if (style.fg && style.fg!='') this.color =  style.fg;
      if (style.afg && style.afg!='') this.altColor =  style.afg;
      if (style.font && style.font!='') this.itemFont = style.font ;
      if (style.sz && style.sz!='') this.itemFontSize = style.sz;
    }    
    this.menuHeight = 16 + (this.itemFontSize - 9);
    
    // Computed...
    this.menuItem = (!menu ? [] : menu);
    
    this.xBorder = this.yBorder = this.border = 1;
    this.menuId = mid;
    
    this.param = '';

    document.__mcalmenus[mid] = this;
    
  }
  return;
}


// --------------------------------------------------------------------------------------
// Set menu color 
// fg : foreground, bg : background
// afg : altern foreground, abg : alter background (color set on pointer over item)
// tfg : title foreground, tbg : title background (item title)

MCalMenu.prototype.setColor = function(fg, bg, afg, abg, tfg, tbg ) {

  this.bgColor = (bg!=''?bg:this.bgColor);
  this.color = (fg!=''?fg:this.color);

  this.bgAltColor = (abg!=''?abg:this.bgAltColor);
  this.altColor = (afg!=''?afg:this.altColor);

  this.bgTitleColor = (tbg!=''?tbg:this.bgTitleColor);
  this.titleColor = (tfg!=''?tfg:this.titleColor);

}

// --------------------------------------------------------------------------------------
// Set Item size 
MCalMenu.prototype.setItemSize = function(h, w) {
  this.menuWidth = w;
  this.menuHeight = h;
  this.itemFontSize = 9 + (this.menuHeight - 16);
}

// --------------------------------------------------------------------------------------
// Show / Hide Icons
MCalMenu.prototype.showIcons = function() { this.setIcons = true; }
MCalMenu.prototype.hideIcons = function() { this.setIcons = false; }
 
// --------------------------------------------------------------------------------------
// Set the reactivity (milli second delay) to close menu on ouse out
MCalMenu.prototype.setSensitivity = function(x) {
  if (parseInt(x)) this.menuReactivity = parseInt(x);
}
 

// --------------------------------------------------------------------------------------
// Values parameter send whith request (http get or js fonction argument)
MCalMenu.prototype.setParam = function(p) {
  this.param = p;
}

  
// --------------------------------------------------------------------------------------
// Hide menu
MCalMenu.hideMenu = function() {
  var cm = new MCalMenu(MCalMenu.MenuId);
  if (!cm) return false;
  cm.__undisplayMenu();
  return true;
}

// --------------------------------------------------------------------------------------
// Display menu mid at pointer position 
MCalMenu.MenuId = '';
MCalMenu.HandlerCtx = '';
MCalMenu.HandlerFunc = '';
MCalMenu.HandlerArgs = new Array;
MCalMenu.showMenu = function(e, xpos, ypos, mid, items, handlercontext, hfunction, hargs) {
  var cm = new MCalMenu(mid);
  if (!cm) return false;
  MCalMenu.MenuId = mid;
  MCalMenu.HandlerCtx = handlercontext;
  MCalMenu.HandlerFunc = hfunction;
  MCalMenu.HandlerArgs = hargs;
  MCalMenu.stopTempo(mid);
  if (xpos==0 && ypos==0) {
    var evcoord = mcalEventXY(e);
    cm.__displayMenu(evcoord.x, evcoord.y, items);
  } else {
    cm.__displayMenu(xpos, ypos, items);
  }
  mcalCancelEvent(e);
}


// --------------------------------------------------------------------------------------
// Activate an item
MCalMenu.activateItem = function(event, mid, iid) {
  
  var cm = new MCalMenu(mid);
  if (!cm) return false;
  if (!cm.menuItem[iid]) alert('Pas d\'item '+iid+' dans le menu '+mid);
  else {
    eval(MCalMenu.HandlerFunc)(event, 
			       parseInt(cm.menuItem[iid].amode),
			       parseInt(cm.menuItem[iid].aevent),
			       cm.menuItem[iid].ascript,
			       cm.menuItem[iid].atarget,
			       MCalMenu.HandlerCtx, 
			       MCalMenu.HandlerArgs );
  }
  MCalMenu.HandlerCtx = '';
  MCalMenu.HandlerFunc = '';
  MCalMenu.HandlerArgs = [];
  MCalMenu.hideMenu();
  return;
}
  
   
// --------------------------------------------------------------------------------------
// Attach menu to element
// when x and are set, menu is opened at this position elsewhere event position is used
  MCalMenu.prototype.attachToElt = function(elt, x, y, useitem, handmode, handlerFunction, handlerArgs) {

  var thismenu = this.menuId;
  var items = useitem;
  var targs = new Array;
  var xpos = x;
  var ypos = y;

  if (document.getElementById(elt)) {
    var elti = document.getElementById(elt);
    switch (handmode) {
      
    case 'click' : 
       mcalAddEvent( elti, 
		    'click', 
		    function cev(e) { 
		      var lmenu = thismenu; 
		      var hmode = handmode; 
		      var hfunction = handlerFunction;
		      var hargs = handlerArgs;
		      MCalMenu.showMenu(e, xpos, ypos, lmenu, items, hmode, hfunction, hargs); }, 
		    true);
     break;
      
    default: //case 'contextmenu' :
      mcalAddEvent( elti, 
		    'contextmenu', 
		    function cev(e) { 
		      var lmenu = thismenu; 
		      var hmode = handmode; 
		      var hfunction = handlerFunction;
		      var hargs = handlerArgs;
		      MCalMenu.showMenu(e, xpos, ypos, lmenu, items, hmode, hfunction, hargs); }, 
		    true);
    }

  } else {
    mcalShowError('MCalMenu.attachToElt:: no such element '+elt);
  }
  
  return;
}

// --------------------------------------------------------------------------------------
MCalMenu.prototype.__undisplayMenu = function() {
  for (var num=0; num<this.menuItem.length; num++ ) {
    if (document.getElementById(this.menuId+'_item'+num)) {
      var e = document.getElementById(this.menuId+'_item'+num);
      e.parentNode.removeChild(e);
    }
  }
}
    
MCalMenu.prototype.__displayMenu = function(xinit, yinit, items) {

  this.__undisplayMenu();

  var x = xinit;
  var y = yinit;
  var w = this.menuWidth - (2*this.yBorder);
  var h = [ this.menuHeight , this.menuHeight, 1 ];
  
  var normalTopEffect; // Computed according to item position
  var normalBottomEffect; // Computed according to item position
  var normalLeftEffect = this.border+'px outset '+this.bgColor;
  var normalRightEffect = this.border+'px outset '+this.bgColor;
  var overEffect = this.border+'px outset '+this.bgAltColor;
  
  // Compute menu real size
  var rh = 0;
  for (var num=0; num<this.menuItem.length; num++ ) {    
    if (items[num]!=-1) this.menuItem[num].typerh += h[this.menuItem[num].type] + 2;
  }
  // correct position the menu according to available space
  var ww = getFrameWidth();
  var wh = getFrameHeight();

  if ((rh+y)>=wh) y = wh - rh - 10; 
  
  if (x-20<0) x=xinit;
  if ((w+x)>=ww) x = ww - w; 
  
  for (var num=0; num<this.menuItem.length; num++ ) {
    
    if (items[num]==-1) {
      
      // hidden...

    } else {
 
      var m = this.menuItem[num];
      
      var itext = '';
      if (m.type==2) itext = '';
      else {
	itext = '<span style="vertical-align:middle">'+m.label+'</span>';
	var itico = '';
	if (this.setIcons) {
	  if (m.icon && m.icon!='') itico = '<img src="' + m.icon + '" style="vertical-align:middle; border:0; width:12; height:12">&nbsp;';
	  else itico = '<span style="padding-left:12">&nbsp;</span>';
	}
	itext = itico + itext;
      }
      
      normalTopEffect = this.border+'px '+(num==0?'outset':'solid')+' '+this.bgColor;
      normalBottomEffect = this.border+'px '+(num==(this.menuItem.length-1)?'outset':'solid')+' '+this.bgColor;
      
      var mistyle = [ 
	{ id:'overflow', val:'hidden' },
	{ id:'z-index', val:this.zIndex },
	{ id:'cursor', val:'pointer' },
	{ id:'border-top', val:normalTopEffect },
	{ id:'border-bottom', val:normalBottomEffect },
	{ id:'border-left', val:normalLeftEffect },
	{ id:'border-right', val:normalRightEffect },
	{ id:'padding-top', val:'2' },
	{ id:'padding-left', val:'6' },
	{ id:'display', val:'' },
	];
      
      if (m.type==0) {
	mistyle[mistyle.length] = { id:'text-align', val:'center' };
	mistyle[mistyle.length] = { id:'font-weight', val:'bold' };
	mistyle[mistyle.length] = { id:'background-color', val:this.bgTitleColor };
	mistyle[mistyle.length] = { id:'color', val:this.titleColor };
	mistyle[mistyle.length] = { id:'border-bottom', val:this.border+'px ridge '+this.titleColor };
      } else if (m.type==2) {
	mistyle[mistyle.length] = { id:'background-color', val:this.bgColor };
	mistyle[mistyle.length] = { id:'border-bottom', val:this.border+'px dotted '+this.color };
      } else {
	mistyle[mistyle.length] = { id:'background-color', val:this.bgColor };
	mistyle[mistyle.length] = { id:'color', val:this.color };
	mistyle[mistyle.length] = { id:'font-style', val:(items[num]==2 ? 'italic' : '' )} ;
      }
      
      
      var mclick = mover = mout = '';
      if (m.type==1 && ((items[num] && items[num]==1) || !items[num])) {
	mover = "this.style.color='"+this.altColor+"'; this.style.background = '"+this.bgAltColor+"'; this.style.border='"+ overEffect +"'";
	mout = "this.style.color='"+this.color+"'; this.style.background = '"+this.bgColor+"'; this.style.borderTop='" + normalTopEffect +"'; this.style.borderBottom='" + normalBottomEffect +"'; this.style.borderLeft='" + normalLeftEffect +"'; this.style.borderRight='" + normalRightEffect +"';"; 
	mclick = "MCalMenu.activateItem(event, '"+this.menuId+"', "+num+");";
      }
      var miattr = [ 
	{ id:'title', val:(m.desc?m.desc:m.label) },
	{ id:'onmouseover', val:"MCalMenu.stopTempo('"+this.menuId+"'); "+mover },
	{ id:'onmouseout', val:mout+"; MCalMenu.startTempo('"+this.menuId+"')"   },
	{ id:'onclick', val:mclick } ];
      
      // Draw menu in this.menuName element father...
      mcalDrawRectAbsolute(this.menuId+'_item'+num, '', x, y, w, h[m.type], this.zIndex, '', true, itext, miattr, mistyle); 
      y += h[m.type] + (2*this.yBorder) + 2;
      
    }
  }
  return;
} 

var CMt = 0;
var CMi = 0;
MCalMenu.startTempo = function(idm) {
  CMi = idm;
  CMt = setTimeout('MCalMenu.hideMenu()', 500);
}
MCalMenu.stopTempo = function(idm) {
  if (idm==CMi) clearTimeout(CMt);
}

  
