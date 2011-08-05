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
// include it in your javascript libraries for download. Instead,
// please just point to my URL to ensure the most up-to-date versions
// of the files. Thanks.
// ===================================================================


/* 
ColorPicker.js
Author: Matt Kruse
Last modified: 6/19/01

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

// If using DHTML popup, write out the required DIV tag near the bottom
// of your page.
<SCRIPT LANGUAGE="JavaScript">cp.writeDiv()</SCRIPT>

// Write the 'pickColor' function that will be called when the user clicks
// a color and do something with the value
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

function OptionPicker_writeDiv() {
	document.writeln("<DIV ID=\"optionPickerDiv\" STYLE=\"border:solid 1px;position:absolute;visibility:hidden;\"> </DIV>");
	}

function OptionPicker_show(anchorname) {
	this.showPopup(anchorname);
	}
function OptionPicker_set(toption) {
    document.getElementById('selectOptionPicker').lenght=toption.length+1;
    for (var i=0; i < toption.length; i++) {
	document.getElementById('selectOptionPicker').options[i]=new Option(toption[i],toption[i],false,false);
    }
    document.getElementById('selectOptionPicker').options[i]=new Option('','',true,true);

}

function OptionPicker_pickOption(color,obj) {
	obj.hidePopup();
	if (window.pickOption) {
		pickOption(color);
		}
	else {
		alert("You must define a function named 'pickOption' to receive the value clicked!");
		}
	}
	
// This function runs when you move your mouse over a color block, if you have a newer browser
function OptionPicker_highlightColor(c) {
	var thedoc = (arguments.length>1)?arguments[1]:window.document;
	var d = thedoc.getElementById("colorPickerSelectedColor");
	d.style.backgroundColor = c;
	d = thedoc.getElementById("colorPickerSelectedColorValue");
	d.innerHTML = c;
	}

function OptionPicker() {
	var windowMode = false;
	// Create a new PopupWindow object
	if (arguments.length==0) {
		var divname = "optionPickerDiv";
		}
	else if (arguments[0] == "window") {
		var divname = '';
		windowMode = true;
		}
	else {
		var divname = arguments[0];
		}
	
	if (divname != "") {
		var op = new PopupWindow(divname);
		}
	else {
		var op = new PopupWindow();
		op.setSize(250,225);
		}

	// Object variables
	op.currentValue = "#FFFFFF";
	
	// Method Mappings
	op.writeDiv = OptionPicker_writeDiv;
	op.highlightColor = OptionPicker_highlightColor;
	op.show = OptionPicker_show;
	op.setOptions = OptionPicker_set;
	

	var op_contents = "";
	var windowRef = (windowMode)?"window.opener.":"";
	if (windowMode) {
		op_contents += "<HTML><HEAD><TITLE>Select Color</TITLE></HEAD>";
		op_contents += "<BODY MARGINWIDTH=0 MARGINHEIGHT=0 LEFMARGIN=0 TOPMARGIN=0><CENTER>";
		}
	op_contents += '<SELECT id="selectOptionPicker" onchange="'+windowRef+'OptionPicker_pickOption(this.options[this.selectedIndex].value'+','+windowRef+'window.popupWindowObjects['+op.index+']);return false;">';
	
	op_contents += "</SELECT>";
	if (windowMode) {
		op_contents += "</CENTER></BODY></HTML>";
		}
	// end populate code

	// Write the contents to the popup object
	op.populate(op_contents);
	// Move the table down a bit so you can see it
	op.offsetY = 25;
	op.autoHide();
	op.writeDiv();
	return op;
	}
