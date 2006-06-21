// ===================================================================
// Author: Matt Kruse <matt@mattkruse.com>
// WWW: http://www.mattkruse.com/
//
// NOTICE: You may use this code for any purpose, commercial or
// private, without any further permission from the author. You may
// remove this notice from your final code if you wish, however it is
// appreciated by the author if at least my web site address is kept.
//
// You may *NOT* re-distribute this code in any way except through its
// use. That means, you can include it in your product, or your web
// site, or any other form where the code is actually being used. You
// may not put the plain javascript up on your site for download or
// include it in your javascript libraries for download. 
// If you wish to share this code with others, please just point them
// to the URL instead.
// Please DO NOT link directly to my .js files from your site. Copy
// the files to your server and use them there. Thank you.
// ===================================================================

/* 
Last modified: 02/24/2003

DESCRIPTION: This widget is used to select a color, in hexadecimal #RRGGBB 
form. It uses a color "swatch" to display the standard 216-color web-safe 
palette. The user can then click on a color to select it.

COMPATABILITY: See notes in AnchorPosition.js and PopupWindow.js.
Only the latest DHTML-capable browsers will show the color and hex values
at the bottom as your mouse goes over them.

USAGE:
// Create a new ColorPicker object using DHTML popup
var cp = new ColorPicker();

// Create a new ColorPicker object using Window Popup
var cp = new ColorPicker('window');

// Add a link in your page to trigger the popup. For example:
<A HREF="#" onClick="cp.show('pick');return false;" NAME="pick" ID="pick">Pick</A>

// Or use the built-in "select" function to do the dirty work for you:
<A HREF="#" onClick="cp.select(document.forms[0].color,'pick');return false;" NAME="pick" ID="pick">Pick</A>

// If using DHTML popup, write out the required DIV tag near the bottom
// of your page.
<SCRIPT LANGUAGE="JavaScript">cp.writeDiv()</SCRIPT>

// Write the 'pickColor' function that will be called when the user clicks
// a color and do something with the value. This is only required if you
// want to do something other than simply populate a form field, which is 
// what the 'select' function will give you.
function pickColor(color) {
	field.value = color;
	}

NOTES:
1) Requires the functions in AnchorPosition.js and PopupWindow.js

2) Your anchor tag MUST contain both NAME and ID attributes which are the 
   same. For example:
   <A NAME="test" ID="test"> </A>

3) There must be at least a space between <A> </A> for IE5.5 to see the 
   anchor tag correctly. Do not do <A></A> with no space.

4) When a ColorPicker object is created, a handler for 'onmouseup' is
   attached to any event handler you may have already defined. Do NOT define
   an event handler for 'onmouseup' after you define a ColorPicker object or
   the color picker will not hide itself correctly.
*/ 
ColorPicker_targetInput = null;
function ColorPicker_writeDiv() {
	document.writeln("<DIV ID=\"colorPickerDiv\" STYLE=\"position:absolute;visibility:hidden;\"> </DIV>");
	}

function ColorPicker_show(anchorname) {
	this.showPopup(anchorname);
	}

function ColorPicker_pickColor(color,obj) {
	obj.hidePopup();
	pickColor(color);
	}

// A Default "pickColor" function to accept the color passed back from popup.
// User can over-ride this with their own function.
function pickColor(color) {
	if (ColorPicker_targetInput==null) {
		alert("Target Input is null, which means you either didn't use the 'select' function or you have no defined your own 'pickColor' function to handle the picked color!");
		return;
		}
	ColorPicker_targetInput.value = color;
	ColorPicker_targetInput.style.backgroundColor = color;
	}

// This function is the easiest way to popup the window, select a color, and
// have the value populate a form field, which is what most people want to do.
function ColorPicker_select(inputobj,linkname) {
	if (inputobj.type!="text" && inputobj.type!="hidden" && inputobj.type!="textarea") { 
		alert("colorpicker.select: Input object passed is not a valid form input object"); 
		window.ColorPicker_targetInput=null;
		return;
		}
	window.ColorPicker_targetInput = inputobj;
	this.show(linkname);
	}
	
// This function runs when you move your mouse over a color block, if you have a newer browser
function ColorPicker_highlightColor(c) {
	var thedoc = (arguments.length>1)?arguments[1]:window.document;
	var d = thedoc.getElementById("colorPickerSelectedColor");
	d.style.backgroundColor = c;
	d = thedoc.getElementById("colorPickerSelectedColorValue");
	d.innerHTML = c;
	}

function ColorPicker() {
	var windowMode = false;
	// Create a new PopupWindow object
	if (arguments.length==0) {
		var divname = "colorPickerDiv";
		}
	else if (arguments[0] == "window") {
		var divname = '';
		windowMode = true;
		}
	else {
		var divname = arguments[0];
		}
	
	if (divname != "") {
		var cp = new PopupWindow(divname);
		}
	else {
		var cp = new PopupWindow();
		cp.setSize(250,225);
		}

	// Object variables
	cp.currentValue = "#FFFFFF";
	
	// Method Mappings
	cp.writeDiv = ColorPicker_writeDiv;
	cp.highlightColor = ColorPicker_highlightColor;
	cp.show = ColorPicker_show;
	cp.select = ColorPicker_select;

	// Code to populate color picker window
	var colors = new Array("#4D1A1A","#4D221A","#4D2B1A","#4D331A","#4D3C1A","#4D441A","#4D4D1A","#444D1A","#3C4D1A","#334D1A","#2B4D1A","#224D1A","#1A4D1A","#1A4D22","#1A4D2B","#1A4D33","#1A4D3C","#1A4D44","#1A4D4D","#1A444D","#1A3C4D","#1A334D","#1A2B4D","#1A224D","#1A1A4D","#221A4D","#2B1A4D","#331A4D","#3C1A4D","#441A4D","#4D1A4D","#4D1A44","#4D1A3C","#4D1A33","#4D1A2B","#4D1A22","#732626","#733326","#734026","#734D26","#735926","#736626","#737326","#667326","#597326","#4D7326","#407326","#337326","#267326","#267333","#267340","#26734D","#267359","#267366","#267373","#266673","#265973","#264D73","#264073","#263373","#262673","#332673","#402673","#4D2673","#592673","#662673","#732673","#732666","#732659","#73264D","#732640","#732633","#993333","#994433","#995533","#996633","#997733","#998833","#999933","#889933","#779933","#669933","#559933","#449933","#339933","#339944","#339955","#339966","#339977","#339988","#339999","#338899","#337799","#336699","#335599","#334499","#333399","#443399","#553399","#663399","#773399","#883399","#993399","#993388","#993377","#993366","#993355","#993344","#BF4040","#BF5540","#BF6A40","#BF8040","#BF9540","#BFAA40","#BFBF40","#AABF40","#95BF40","#80BF40","#6ABF40","#55BF40","#40BF40","#40BF55","#40BF6A","#40BF80","#40BF95","#40BFAA","#40BFBF","#40AABF","#4095BF","#4080BF","#406ABF","#4055BF","#4040BF","#5540BF","#6A40BF","#8040BF","#9540BF","#AA40BF","#BF40BF","#BF40AA","#BF4095","#BF4080","#BF406A","#BF4055","#CC6666","#CC7766","#CC8866","#CC9966","#CCAA66","#CCBB66","#CCCC66","#BBCC66","#AACC66","#99CC66","#88CC66","#77CC66","#66CC66","#66CC77","#66CC88","#66CC99","#66CCAA","#66CCBB","#66CCCC","#66BBCC","#66AACC","#6699CC","#6688CC","#6677CC","#6666CC","#7766CC","#8866CC","#9966CC","#AA66CC","#BB66CC","#CC66CC","#CC66BB","#CC66AA","#CC6699","#CC6688","#CC6677","#D98C8C","#D9998C","#D9A68C","#D9B38C","#D9BF8C","#D9CC8C","#D9D98C","#CCD98C","#BFD98C","#B3D98C","#A6D98C","#99D98C","#8CD98C","#8CD999","#8CD9A6","#8CD9B3","#8CD9BF","#8CD9CC","#8CD9D9","#8CCCD9","#8CBFD9","#8CB3D9","#8CA6D9","#8C99D9","#8C8CD9","#998CD9","#A68CD9","#B38CD9","#BF8CD9","#CC8CD9","#D98CD9","#D98CCC","#D98CBF","#D98CB3","#D98CA6","#D98C99","#E6B3B3","#E6BBB3","#E6C4B3","#E6CCB3","#E6D5B3","#E6DDB3","#E6E6B3","#DDE6B3","#D5E6B3","#CCE6B3","#C4E6B3","#BBE6B3","#B3E6B3","#B3E6BB","#B3E6C4","#B3E6CC","#B3E6D5","#B3E6DD","#B3E6E6","#B3DDE6","#B3D5E6","#B3CCE6","#B3C4E6","#B3BBE6","#B3B3E6","#BBB3E6","#C4B3E6","#CCB3E6","#D5B3E6","#DDB3E6","#E6B3E6","#E6B3DD","#E6B3D5","#E6B3CC","#E6B3C4","#E6B3BB","#F2D9D9","#F2DDD9","#F2E1D9","#F2E6D9","#F2EAD9","#F2EED9","#F2F2D9","#EEF2D9","#EAF2D9","#E6F2D9","#E1F2D9","#DDF2D9","#D9F2D9","#D9F2DD","#D9F2E1","#D9F2E6","#D9F2EA","#D9F2EE","#D9F2F2","#D9EEF2","#D9EAF2","#D9E6F2","#D9E1F2","#D9DDF2","#D9D9F2","#DDD9F2","#E1D9F2","#E6D9F2","#EAD9F2","#EED9F2","#F2D9F2","#F2D9EE","#F2D9EA","#F2D9E6","#F2D9E1","#F2D9DD","#000000", "#070707", "#0E0E0E", "#151515", "#1C1C1C", "#232323", "#2B2B2B", "#323232", "#393939", "#404040", "#474747", "#4E4E4E", "#555555", "#5C5C5C", "#636363", "#6A6A6A", "#717171", "#787878", "#808080", "#878787", "#8E8E8E", "#959595", "#9C9C9C", "#A3A3A3", "#AAAAAA", "#B1B1B1", "#B8B8B8", "#BFBFBF", "#C6C6C6", "#CDCDCD", "#D5D5D5", "#DCDCDC", "#E3E3E3", "#EAEAEA", "#F1F1F1", "#F8F8F8", "#FFFFFF");
	var total = colors.length;
	var width = 36;
	var cp_contents = "";
	var windowRef = (windowMode)?"window.opener.":"";
	if (windowMode) {
		cp_contents += "<HTML><HEAD><TITLE>Select Color</TITLE></HEAD>";
		cp_contents += "<BODY MARGINWIDTH=0 MARGINHEIGHT=0 LEFMARGIN=0 TOPMARGIN=0><CENTER>";
		}
	cp_contents += "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0  cols=18>";
	var use_highlight = (document.getElementById || document.all)?true:false;
	for (var i=0; i<total; i++) {
		if ((i % width) == 0) { cp_contents += "<TR>"; }
		if (use_highlight) { var mo = 'onMouseOver="'+windowRef+'ColorPicker_highlightColor(\''+colors[i]+'\',window.document)"'; }
		else { mo = ""; }
		cp_contents += '<TD '+mo+' style="width:6px;height:5px;background-color:'+colors[i]+'" onClick="'+windowRef+'ColorPicker_pickColor(\''+colors[i]+'\','+windowRef+'window.popupWindowObjects['+cp.index+']);return false;" </TD>';
		if ( ((i+1)>=total) || (((i+1) % width) == 0)) { 
			cp_contents += "</TR>";
			}
		}
	// If the browser supports dynamically changing TD cells, add the fancy stuff
	if (document.getElementById) {
		var width1 = Math.floor(width/2);
		var width2 = width = width1;
		cp_contents += "<TR><TD COLSPAN='"+width1+"' BGCOLOR='#ffffff' ID='colorPickerSelectedColor'>&nbsp;</TD><TD COLSPAN='"+width2+"' ALIGN='CENTER' style='font-family:monospace' ID='colorPickerSelectedColorValue'>#FFFFFF</TD></TR>";
		}
	cp_contents += "</TABLE>";
	if (windowMode) {
		cp_contents += "</CENTER></BODY></HTML>";
		}
	// end populate code

	// Write the contents to the popup object
	cp.populate(cp_contents+"\n");
	// Move the table down a bit so you can see it
	cp.offsetY = 25;
	cp.autoHide();
	cp.writeDiv();
	return cp;
	}
