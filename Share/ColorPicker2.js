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
function ColorPicker_viewpallette(n) {
  var t=document.getElementById('pickercolortable');
  var tbodies=t.getElementsByTagName('tbody');

  for (var i=0;i<tbodies.length;i++) {
   tbodies[i].style.display='none';
    if (tbodies[i].id=='palette'+n)  tbodies[i].style.display='';
  }

  
}

var ColorPicker_PALETTE=1;
function ColorPicker_changepalette() {
  ColorPicker_PALETTE=(ColorPicker_PALETTE+1)%4;
  ColorPicker_viewpallette(ColorPicker_PALETTE);
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
	var colors2 = new Array("#4D1A1A","#4D221A","#4D2B1A","#4D331A","#4D3C1A","#4D441A","#4D4D1A","#444D1A","#3C4D1A","#334D1A","#2B4D1A","#224D1A","#1A4D1A","#1A4D22","#1A4D2B","#1A4D33","#1A4D3C","#1A4D44","#1A4D4D","#1A444D","#1A3C4D","#1A334D","#1A2B4D","#1A224D","#1A1A4D","#221A4D","#2B1A4D","#331A4D","#3C1A4D","#441A4D","#4D1A4D","#4D1A44","#4D1A3C","#4D1A33","#4D1A2B","#4D1A22","#602020","#602B20","#603520","#604020","#604A20","#605520","#606020","#556020","#4A6020","#406020","#356020","#2B6020","#206020","#20602B","#206035","#206040","#20604A","#206055","#206060","#205560","#204A60","#204060","#203560","#202B60","#202060","#2B2060","#352060","#402060","#4A2060","#552060","#602060","#602055","#60204A","#602040","#602035","#60202B","#732626","#733326","#734026","#734D26","#735926","#736626","#737326","#667326","#597326","#4D7326","#407326","#337326","#267326","#267333","#267340","#26734D","#267359","#267366","#267373","#266673","#265973","#264D73","#264073","#263373","#262673","#332673","#402673","#4D2673","#592673","#662673","#732673","#732666","#732659","#73264D","#732640","#732633","#862D2D","#863C2D","#864A2D","#86592D","#86682D","#86772D","#86862D","#77862D","#68862D","#59862D","#4A862D","#3C862D","#2D862D","#2D863C","#2D864A","#2D8659","#2D8668","#2D8677","#2D8686","#2D7786","#2D6886","#2D5986","#2D4A86","#2D3C86","#2D2D86","#3C2D86","#4A2D86","#592D86","#682D86","#772D86","#862D86","#862D77","#862D68","#862D59","#862D4A","#862D3C","#993333","#994433","#995533","#996633","#997733","#998833","#999933","#889933","#779933","#669933","#559933","#449933","#339933","#339944","#339955","#339966","#339977","#339988","#339999","#338899","#337799","#336699","#335599","#334499","#333399","#443399","#553399","#663399","#773399","#883399","#993399","#993388","#993377","#993366","#993355","#993344","#AC3939","#AC4D39","#AC6039","#AC7339","#AC8639","#AC9939","#ACAC39","#99AC39","#86AC39","#73AC39","#60AC39","#4DAC39","#39AC39","#39AC4D","#39AC60","#39AC73","#39AC86","#39AC99","#39ACAC","#3999AC","#3986AC","#3973AC","#3960AC","#394DAC","#3939AC","#4D39AC","#6039AC","#7339AC","#8639AC","#9939AC","#AC39AC","#AC3999","#AC3986","#AC3973","#AC3960","#AC394D","#BF4040","#BF5540","#BF6A40","#BF8040","#BF9540","#BFAA40","#BFBF40","#AABF40","#95BF40","#80BF40","#6ABF40","#55BF40","#40BF40","#40BF55","#40BF6A","#40BF80","#40BF95","#40BFAA","#40BFBF","#40AABF","#4095BF","#4080BF","#406ABF","#4055BF","#4040BF","#5540BF","#6A40BF","#8040BF","#9540BF","#AA40BF","#BF40BF","#BF40AA","#BF4095","#BF4080","#BF406A","#BF4055","#C65353","#C66653","#C67953","#C68C53","#C69F53","#C6B353","#C6C653","#B3C653","#9FC653","#8CC653","#79C653","#66C653","#53C653","#53C666","#53C679","#53C68C","#53C69F","#53C6B3","#53C6C6","#53B3C6","#539FC6","#538CC6","#5379C6","#5366C6","#5353C6","#6653C6","#7953C6","#8C53C6","#9F53C6","#B353C6","#C653C6","#C653B3","#C6539F","#C6538C","#C65379","#C65366","#CC6666","#CC7766","#CC8866","#CC9966","#CCAA66","#CCBB66","#CCCC66","#BBCC66","#AACC66","#99CC66","#88CC66","#77CC66","#66CC66","#66CC77","#66CC88","#66CC99","#66CCAA","#66CCBB","#66CCCC","#66BBCC","#66AACC","#6699CC","#6688CC","#6677CC","#6666CC","#7766CC","#8866CC","#9966CC","#AA66CC","#BB66CC","#CC66CC","#CC66BB","#CC66AA","#CC6699","#CC6688","#CC6677","#D27979","#D28879","#D29779","#D2A679","#D2B579","#D2C479","#D2D279","#C4D279","#B5D279","#A6D279","#97D279","#88D279","#79D279","#79D288","#79D297","#79D2A6","#79D2B5","#79D2C4","#79D2D2","#79C4D2","#79B5D2","#79A6D2","#7997D2","#7988D2","#7979D2","#8879D2","#9779D2","#A679D2","#B579D2","#C479D2","#D279D2","#D279C4","#D279B5","#D279A6","#D27997","#D27988","#D98C8C","#D9998C","#D9A68C","#D9B38C","#D9BF8C","#D9CC8C","#D9D98C","#CCD98C","#BFD98C","#B3D98C","#A6D98C","#99D98C","#8CD98C","#8CD999","#8CD9A6","#8CD9B3","#8CD9BF","#8CD9CC","#8CD9D9","#8CCCD9","#8CBFD9","#8CB3D9","#8CA6D9","#8C99D9","#8C8CD9","#998CD9","#A68CD9","#B38CD9","#BF8CD9","#CC8CD9","#D98CD9","#D98CCC","#D98CBF","#D98CB3","#D98CA6","#D98C99","#DF9F9F","#DFAA9F","#DFB59F","#DFBF9F","#DFCA9F","#DFD59F","#DFDF9F","#D5DF9F","#CADF9F","#BFDF9F","#B5DF9F","#AADF9F","#9FDF9F","#9FDFAA","#9FDFB5","#9FDFBF","#9FDFCA","#9FDFD5","#9FDFDF","#9FD5DF","#9FCADF","#9FBFDF","#9FB5DF","#9FAADF","#9F9FDF","#AA9FDF","#B59FDF","#BF9FDF","#CA9FDF","#D59FDF","#DF9FDF","#DF9FD5","#DF9FCA","#DF9FBF","#DF9FB5","#DF9FAA","#E6B3B3","#E6BBB3","#E6C4B3","#E6CCB3","#E6D5B3","#E6DDB3","#E6E6B3","#DDE6B3","#D5E6B3","#CCE6B3","#C4E6B3","#BBE6B3","#B3E6B3","#B3E6BB","#B3E6C4","#B3E6CC","#B3E6D5","#B3E6DD","#B3E6E6","#B3DDE6","#B3D5E6","#B3CCE6","#B3C4E6","#B3BBE6","#B3B3E6","#BBB3E6","#C4B3E6","#CCB3E6","#D5B3E6","#DDB3E6","#E6B3E6","#E6B3DD","#E6B3D5","#E6B3CC","#E6B3C4","#E6B3BB","#ECC6C6","#ECCCC6","#ECD2C6","#ECD9C6","#ECDFC6","#ECE6C6","#ECECC6","#E6ECC6","#DFECC6","#D9ECC6","#D2ECC6","#CCECC6","#C6ECC6","#C6ECCC","#C6ECD2","#C6ECD9","#C6ECDF","#C6ECE6","#C6ECEC","#C6E6EC","#C6DFEC","#C6D9EC","#C6D2EC","#C6CCEC","#C6C6EC","#CCC6EC","#D2C6EC","#D9C6EC","#DFC6EC","#E6C6EC","#ECC6EC","#ECC6E6","#ECC6DF","#ECC6D9","#ECC6D2","#ECC6CC",
			       "#000000",  "#0E0E0E", "#151515", "#1C1C1C", "#232323", "#2B2B2B", "#323232", "#393939", "#404040", "#474747", "#4E4E4E", "#555555", "#5C5C5C", "#636363", "#6A6A6A", "#717171", "#787878", "#808080", "#878787", "#8E8E8E", "#959595", "#9C9C9C", "#A3A3A3", "#AAAAAA", "#B1B1B1", "#B8B8B8", "#BFBFBF", "#C6C6C6", "#CDCDCD", "#D5D5D5", "#DCDCDC", "#E3E3E3", "#EAEAEA", "#F1F1F1", "#F8F8F8", "#FFFFFF");
	var colors1= new Array(	"#402626","#402B26","#402F26","#403326","#403726","#403C26","#404026","#3C4026","#374026","#334026","#2F4026","#2B4026","#264026","#26402B","#26402F","#264033","#264037","#26403C","#264040","#263C40","#263740","#263340","#262F40","#262B40","#262640","#2B2640","#2F2640","#332640","#372640","#3C2640","#402640","#40263C","#402637","#402633","#40262F","#40262B","#503030","#503530","#503A30","#504030","#504530","#504A30","#505030","#4A5030","#455030","#405030","#3A5030","#355030","#305030","#305035","#30503A","#305040","#305045","#30504A","#305050","#304A50","#304550","#304050","#303A50","#303550","#303050","#353050","#3A3050","#403050","#453050","#4A3050","#503050","#50304A","#503045","#503040","#50303A","#503035","#603939","#604039","#604639","#604D39","#605339","#605939","#606039","#596039","#536039","#4D6039","#466039","#406039","#396039","#396040","#396046","#39604D","#396053","#396059","#396060","#395960","#395360","#394D60","#394660","#394060","#393960","#403960","#463960","#4D3960","#533960","#593960","#603960","#603959","#603953","#60394D","#603946","#603940","#704343","#704A43","#705243","#705943","#706143","#706843","#707043","#687043","#617043","#597043","#527043","#4A7043","#437043","#43704A","#437052","#437059","#437061","#437068","#437070","#436870","#436170","#435970","#435270","#434A70","#434370","#4A4370","#524370","#594370","#614370","#684370","#704370","#704368","#704361","#704359","#704352","#70434A","#804D4D","#80554D","#805E4D","#80664D","#806F4D","#80774D","#80804D","#77804D","#6F804D","#66804D","#5E804D","#55804D","#4D804D","#4D8055","#4D805E","#4D8066","#4D806F","#4D8077","#4D8080","#4D7780","#4D6F80","#4D6680","#4D5E80","#4D5580","#4D4D80","#554D80","#5E4D80","#664D80","#6F4D80","#774D80","#804D80","#804D77","#804D6F","#804D66","#804D5E","#804D55","#8F5656","#8F6056","#8F6956","#8F7356","#8F7C56","#8F8656","#8F8F56","#868F56","#7C8F56","#738F56","#698F56","#608F56","#568F56","#568F60","#568F69","#568F73","#568F7C","#568F86","#568F8F","#56868F","#567C8F","#56738F","#56698F","#56608F","#56568F","#60568F","#69568F","#73568F","#7C568F","#86568F","#8F568F","#8F5686","#8F567C","#8F5673","#8F5669","#8F5660","#9F6060","#9F6A60","#9F7560","#9F8060","#9F8A60","#9F9560","#9F9F60","#959F60","#8A9F60","#809F60","#759F60","#6A9F60","#609F60","#609F6A","#609F75","#609F80","#609F8A","#609F95","#609F9F","#60959F","#608A9F","#60809F","#60759F","#606A9F","#60609F","#6A609F","#75609F","#80609F","#8A609F","#95609F","#9F609F","#9F6095","#9F608A","#9F6080","#9F6075","#9F606A","#A97070","#A97970","#A98370","#A98C70","#A99670","#A99F70","#A9A970","#9FA970","#96A970","#8CA970","#83A970","#79A970","#70A970","#70A979","#70A983","#70A98C","#70A996","#70A99F","#70A9A9","#709FA9","#7096A9","#708CA9","#7083A9","#7079A9","#7070A9","#7970A9","#8370A9","#8C70A9","#9670A9","#9F70A9","#A970A9","#A9709F","#A97096","#A9708C","#A97083","#A97079","#B38080","#B38880","#B39180","#B39980","#B3A280","#B3AA80","#B3B380","#AAB380","#A2B380","#99B380","#91B380","#88B380","#80B380","#80B388","#80B391","#80B399","#80B3A2","#80B3AA","#80B3B3","#80AAB3","#80A2B3","#8099B3","#8091B3","#8088B3","#8080B3","#8880B3","#9180B3","#9980B3","#A280B3","#AA80B3","#B380B3","#B380AA","#B380A2","#B38099","#B38091","#B38088","#BC8F8F","#BC978F","#BC9E8F","#BCA68F","#BCAD8F","#BCB58F","#BCBC8F","#B5BC8F","#ADBC8F","#A6BC8F","#9EBC8F","#97BC8F","#8FBC8F","#8FBC97","#8FBC9E","#8FBCA6","#8FBCAD","#8FBCB5","#8FBCBC","#8FB5BC","#8FADBC","#8FA6BC","#8F9EBC","#8F97BC","#8F8FBC","#978FBC","#9E8FBC","#A68FBC","#AD8FBC","#B58FBC","#BC8FBC","#BC8FB5","#BC8FAD","#BC8FA6","#BC8F9E","#BC8F97","#C69F9F","#C6A69F","#C6AC9F","#C6B39F","#C6B99F","#C6BF9F","#C6C69F","#BFC69F","#B9C69F","#B3C69F","#ACC69F","#A6C69F","#9FC69F","#9FC6A6","#9FC6AC","#9FC6B3","#9FC6B9","#9FC6BF","#9FC6C6","#9FBFC6","#9FB9C6","#9FB3C6","#9FACC6","#9FA6C6","#9F9FC6","#A69FC6","#AC9FC6","#B39FC6","#B99FC6","#BF9FC6","#C69FC6","#C69FBF","#C69FB9","#C69FB3","#C69FAC","#C69FA6","#CFAFAF","#CFB5AF","#CFBAAF","#CFBFAF","#CFC5AF","#CFCAAF","#CFCFAF","#CACFAF","#C5CFAF","#BFCFAF","#BACFAF","#B5CFAF","#AFCFAF","#AFCFB5","#AFCFBA","#AFCFBF","#AFCFC5","#AFCFCA","#AFCFCF","#AFCACF","#AFC5CF","#AFBFCF","#AFBACF","#AFB5CF","#AFAFCF","#B5AFCF","#BAAFCF","#BFAFCF","#C5AFCF","#CAAFCF","#CFAFCF","#CFAFCA","#CFAFC5","#CFAFBF","#CFAFBA","#CFAFB5","#D9BFBF","#D9C4BF","#D9C8BF","#D9CCBF","#D9D0BF","#D9D5BF","#D9D9BF","#D5D9BF","#D0D9BF","#CCD9BF","#C8D9BF","#C4D9BF","#BFD9BF","#BFD9C4","#BFD9C8","#BFD9CC","#BFD9D0","#BFD9D5","#BFD9D9","#BFD5D9","#BFD0D9","#BFCCD9","#BFC8D9","#BFC4D9","#BFBFD9","#C4BFD9","#C8BFD9","#CCBFD9","#D0BFD9","#D5BFD9","#D9BFD9","#D9BFD5","#D9BFD0","#D9BFCC","#D9BFC8","#D9BFC4","#E2CFCF","#E2D2CF","#E2D6CF","#E2D9CF","#E2DCCF","#E2DFCF","#E2E2CF","#DFE2CF","#DCE2CF","#D9E2CF","#D6E2CF","#D2E2CF","#CFE2CF","#CFE2D2","#CFE2D6","#CFE2D9","#CFE2DC","#CFE2DF","#CFE2E2","#CFDFE2","#CFDCE2","#CFD9E2","#CFD6E2","#CFD2E2","#CFCFE2","#D2CFE2","#D6CFE2","#D9CFE2","#DCCFE2","#DFCFE2","#E2CFE2","#E2CFDF","#E2CFDC","#E2CFD9","#E2CFD6","#E2CFD2",
			       "#000000",  "#0E0E0E", "#151515", "#1C1C1C", "#232323", "#2B2B2B", "#323232", "#393939", "#404040", "#474747", "#4E4E4E", "#555555", "#5C5C5C", "#636363", "#6A6A6A", "#717171", "#787878", "#808080", "#878787", "#8E8E8E", "#959595", "#9C9C9C", "#A3A3A3", "#AAAAAA", "#B1B1B1", "#B8B8B8", "#BFBFBF", "#C6C6C6", "#CDCDCD", "#D5D5D5", "#DCDCDC", "#E3E3E3", "#EAEAEA", "#F1F1F1", "#F8F8F8", "#FFFFFF");

	var colors3=new Array("#590D0D","#591A0D","#59260D","#59330D","#59400D","#594D0D","#59590D","#4D590D","#40590D","#33590D","#26590D","#1A590D","#0D590D","#0D591A","#0D5926","#0D5933","#0D5940","#0D594D","#0D5959","#0D4D59","#0D4059","#0D3359","#0D2659","#0D1A59","#0D0D59","#1A0D59","#260D59","#330D59","#400D59","#4D0D59","#590D59","#590D4D","#590D40","#590D33","#590D26","#590D1A","#701010","#702010","#703010","#704010","#705010","#706010","#707010","#607010","#507010","#407010","#307010","#207010","#107010","#107020","#107030","#107040","#107050","#107060","#107070","#106070","#105070","#104070","#103070","#102070","#101070","#201070","#301070","#401070","#501070","#601070","#701070","#701060","#701050","#701040","#701030","#701020","#861313","#862613","#863913","#864D13","#866013","#867313","#868613","#738613","#608613","#4D8613","#398613","#268613","#138613","#138626","#138639","#13864D","#138660","#138673","#138686","#137386","#136086","#134D86","#133986","#132686","#131386","#261386","#391386","#4D1386","#601386","#731386","#861386","#861373","#861360","#86134D","#861339","#861326","#9C1616","#9C2D16","#9C4316","#9C5916","#9C7016","#9C8616","#9C9C16","#869C16","#709C16","#599C16","#439C16","#2D9C16","#169C16","#169C2D","#169C43","#169C59","#169C70","#169C86","#169C9C","#16869C","#16709C","#16599C","#16439C","#162D9C","#16169C","#2D169C","#43169C","#59169C","#70169C","#86169C","#9C169C","#9C1686","#9C1670","#9C1659","#9C1643","#9C162D","#B31A1A","#B3331A","#B34D1A","#B3661A","#B3801A","#B3991A","#B3B31A","#99B31A","#80B31A","#66B31A","#4DB31A","#33B31A","#1AB31A","#1AB333","#1AB34D","#1AB366","#1AB380","#1AB399","#1AB3B3","#1A99B3","#1A80B3","#1A66B3","#1A4DB3","#1A33B3","#1A1AB3","#331AB3","#4D1AB3","#661AB3","#801AB3","#991AB3","#B31AB3","#B31A99","#B31A80","#B31A66","#B31A4D","#B31A33","#C91D1D","#C9391D","#C9561D","#C9731D","#C98F1D","#C9AC1D","#C9C91D","#ACC91D","#8FC91D","#73C91D","#56C91D","#39C91D","#1DC91D","#1DC939","#1DC956","#1DC973","#1DC98F","#1DC9AC","#1DC9C9","#1DACC9","#1D8FC9","#1D73C9","#1D56C9","#1D39C9","#1D1DC9","#391DC9","#561DC9","#731DC9","#8F1DC9","#AC1DC9","#C91DC9","#C91DAC","#C91D8F","#C91D73","#C91D56","#C91D39","#DF2020","#DF4020","#DF6020","#DF8020","#DF9F20","#DFBF20","#DFDF20","#BFDF20","#9FDF20","#80DF20","#60DF20","#40DF20","#20DF20","#20DF40","#20DF60","#20DF80","#20DF9F","#20DFBF","#20DFDF","#20BFDF","#209FDF","#2080DF","#2060DF","#2040DF","#2020DF","#4020DF","#6020DF","#8020DF","#9F20DF","#BF20DF","#DF20DF","#DF20BF","#DF209F","#DF2080","#DF2060","#DF2040","#E23636","#E25336","#E27036","#E28C36","#E2A936","#E2C636","#E2E236","#C6E236","#A9E236","#8CE236","#70E236","#53E236","#36E236","#36E253","#36E270","#36E28C","#36E2A9","#36E2C6","#36E2E2","#36C6E2","#36A9E2","#368CE2","#3670E2","#3653E2","#3636E2","#5336E2","#7036E2","#8C36E2","#A936E2","#C636E2","#E236E2","#E236C6","#E236A9","#E2368C","#E23670","#E23653","#E64D4D","#E6664D","#E6804D","#E6994D","#E6B34D","#E6CC4D","#E6E64D","#CCE64D","#B3E64D","#99E64D","#80E64D","#66E64D","#4DE64D","#4DE666","#4DE680","#4DE699","#4DE6B3","#4DE6CC","#4DE6E6","#4DCCE6","#4DB3E6","#4D99E6","#4D80E6","#4D66E6","#4D4DE6","#664DE6","#804DE6","#994DE6","#B34DE6","#CC4DE6","#E64DE6","#E64DCC","#E64DB3","#E64D99","#E64D80","#E64D66","#E96363","#E97963","#E98F63","#E9A663","#E9BC63","#E9D263","#E9E963","#D2E963","#BCE963","#A6E963","#8FE963","#79E963","#63E963","#63E979","#63E98F","#63E9A6","#63E9BC","#63E9D2","#63E9E9","#63D2E9","#63BCE9","#63A6E9","#638FE9","#6379E9","#6363E9","#7963E9","#8F63E9","#A663E9","#BC63E9","#D263E9","#E963E9","#E963D2","#E963BC","#E963A6","#E9638F","#E96379","#EC7979","#EC8C79","#EC9F79","#ECB379","#ECC679","#ECD979","#ECEC79","#D9EC79","#C6EC79","#B3EC79","#9FEC79","#8CEC79","#79EC79","#79EC8C","#79EC9F","#79ECB3","#79ECC6","#79ECD9","#79ECEC","#79D9EC","#79C6EC","#79B3EC","#799FEC","#798CEC","#7979EC","#8C79EC","#9F79EC","#B379EC","#C679EC","#D979EC","#EC79EC","#EC79D9","#EC79C6","#EC79B3","#EC799F","#EC798C","#EF8F8F","#EF9F8F","#EFAF8F","#EFBF8F","#EFCF8F","#EFDF8F","#EFEF8F","#DFEF8F","#CFEF8F","#BFEF8F","#AFEF8F","#9FEF8F","#8FEF8F","#8FEF9F","#8FEFAF","#8FEFBF","#8FEFCF","#8FEFDF","#8FEFEF","#8FDFEF","#8FCFEF","#8FBFEF","#8FAFEF","#8F9FEF","#8F8FEF","#9F8FEF","#AF8FEF","#BF8FEF","#CF8FEF","#DF8FEF","#EF8FEF","#EF8FDF","#EF8FCF","#EF8FBF","#EF8FAF","#EF8F9F","#F2A6A6","#F2B3A6","#F2BFA6","#F2CCA6","#F2D9A6","#F2E6A6","#F2F2A6","#E6F2A6","#D9F2A6","#CCF2A6","#BFF2A6","#B3F2A6","#A6F2A6","#A6F2B3","#A6F2BF","#A6F2CC","#A6F2D9","#A6F2E6","#A6F2F2","#A6E6F2","#A6D9F2","#A6CCF2","#A6BFF2","#A6B3F2","#A6A6F2","#B3A6F2","#BFA6F2","#CCA6F2","#D9A6F2","#E6A6F2","#F2A6F2","#F2A6E6","#F2A6D9","#F2A6CC","#F2A6BF","#F2A6B3","#F5BCBC","#F5C6BC","#F5CFBC","#F5D9BC","#F5E2BC","#F5ECBC","#F5F5BC","#ECF5BC","#E2F5BC","#D9F5BC","#CFF5BC","#C6F5BC","#BCF5BC","#BCF5C6","#BCF5CF","#BCF5D9","#BCF5E2","#BCF5EC","#BCF5F5","#BCECF5","#BCE2F5","#BCD9F5","#BCCFF5","#BCC6F5","#BCBCF5","#C6BCF5","#CFBCF5","#D9BCF5","#E2BCF5","#ECBCF5","#F5BCF5","#F5BCEC","#F5BCE2","#F5BCD9","#F5BCCF","#F5BCC6",
			       "#000000",  "#0E0E0E", "#151515", "#1C1C1C", "#232323", "#2B2B2B", "#323232", "#393939", "#404040", "#474747", "#4E4E4E", "#555555", "#5C5C5C", "#636363", "#6A6A6A", "#717171", "#787878", "#808080", "#878787", "#8E8E8E", "#959595", "#9C9C9C", "#A3A3A3", "#AAAAAA", "#B1B1B1", "#B8B8B8", "#BFBFBF", "#C6C6C6", "#CDCDCD", "#D5D5D5", "#DCDCDC", "#E3E3E3", "#EAEAEA", "#F1F1F1", "#F8F8F8", "#FFFFFF");

	var colors4=new Array("#660000","#661100","#662200","#663300","#664400","#665500","#666600","#556600","#446600","#336600","#226600","#116600","#006600","#006611","#006622","#006633","#006644","#006655","#006666","#005566","#004466","#003366","#002266","#001166","#000066","#110066","#220066","#330066","#440066","#550066","#660066","#660055","#660044","#660033","#660022","#660011","#800000","#801500","#802B00","#804000","#805500","#806A00","#808000","#6A8000","#558000","#408000","#2B8000","#158000","#008000","#008015","#00802B","#008040","#008055","#00806A","#008080","#006A80","#005580","#004080","#002B80","#001580","#000080","#150080","#2B0080","#400080","#550080","#6A0080","#800080","#80006A","#800055","#800040","#80002B","#800015","#990000","#991A00","#993300","#994D00","#996600","#998000","#999900","#809900","#669900","#4D9900","#339900","#1A9900","#009900","#00991A","#009933","#00994D","#009966","#009980","#009999","#008099","#006699","#004D99","#003399","#001A99","#000099","#1A0099","#330099","#4D0099","#660099","#800099","#990099","#990080","#990066","#99004D","#990033","#99001A","#B30000","#B31E00","#B33C00","#B35900","#B37700","#B39500","#B3B300","#95B300","#77B300","#59B300","#3CB300","#1EB300","#00B300","#00B31E","#00B33C","#00B359","#00B377","#00B395","#00B3B3","#0095B3","#0077B3","#0059B3","#003CB3","#001EB3","#0000B3","#1E00B3","#3C00B3","#5900B3","#7700B3","#9500B3","#B300B3","#B30095","#B30077","#B30059","#B3003C","#B3001E","#CC0000","#CC2200","#CC4400","#CC6600","#CC8800","#CCAA00","#CCCC00","#AACC00","#88CC00","#66CC00","#44CC00","#22CC00","#00CC00","#00CC22","#00CC44","#00CC66","#00CC88","#00CCAA","#00CCCC","#00AACC","#0088CC","#0066CC","#0044CC","#0022CC","#0000CC","#2200CC","#4400CC","#6600CC","#8800CC","#AA00CC","#CC00CC","#CC00AA","#CC0088","#CC0066","#CC0044","#CC0022","#E60000","#E62600","#E64D00","#E67300","#E69900","#E6BF00","#E6E600","#BFE600","#99E600","#73E600","#4DE600","#26E600","#00E600","#00E626","#00E64D","#00E673","#00E699","#00E6BF","#00E6E6","#00BFE6","#0099E6","#0073E6","#004DE6","#0026E6","#0000E6","#2600E6","#4D00E6","#7300E6","#9900E6","#BF00E6","#E600E6","#E600BF","#E60099","#E60073","#E6004D","#E60026","#FF0000","#FF2B00","#FF5500","#FF8000","#FFAA00","#FFD500","#FFFF00","#D5FF00","#AAFF00","#80FF00","#55FF00","#2BFF00","#00FF00","#00FF2B","#00FF55","#00FF80","#00FFAA","#00FFD5","#00FFFF","#00D5FF","#00AAFF","#0080FF","#0055FF","#002BFF","#0000FF","#2B00FF","#5500FF","#8000FF","#AA00FF","#D500FF","#FF00FF","#FF00D5","#FF00AA","#FF0080","#FF0055","#FF002B","#FF1A1A","#FF401A","#FF661A","#FF8C1A","#FFB31A","#FFD91A","#FFFF1A","#D9FF1A","#B3FF1A","#8CFF1A","#66FF1A","#40FF1A","#1AFF1A","#1AFF40","#1AFF66","#1AFF8C","#1AFFB3","#1AFFD9","#1AFFFF","#1AD9FF","#1AB3FF","#1A8CFF","#1A66FF","#1A40FF","#1A1AFF","#401AFF","#661AFF","#8C1AFF","#B31AFF","#D91AFF","#FF1AFF","#FF1AD9","#FF1AB3","#FF1A8C","#FF1A66","#FF1A40","#FF3333","#FF5533","#FF7733","#FF9933","#FFBB33","#FFDD33","#FFFF33","#DDFF33","#BBFF33","#99FF33","#77FF33","#55FF33","#33FF33","#33FF55","#33FF77","#33FF99","#33FFBB","#33FFDD","#33FFFF","#33DDFF","#33BBFF","#3399FF","#3377FF","#3355FF","#3333FF","#5533FF","#7733FF","#9933FF","#BB33FF","#DD33FF","#FF33FF","#FF33DD","#FF33BB","#FF3399","#FF3377","#FF3355","#FF4D4D","#FF6A4D","#FF884D","#FFA64D","#FFC44D","#FFE14D","#FFFF4D","#E1FF4D","#C4FF4D","#A6FF4D","#88FF4D","#6AFF4D","#4DFF4D","#4DFF6A","#4DFF88","#4DFFA6","#4DFFC4","#4DFFE1","#4DFFFF","#4DE1FF","#4DC4FF","#4DA6FF","#4D88FF","#4D6AFF","#4D4DFF","#6A4DFF","#884DFF","#A64DFF","#C44DFF","#E14DFF","#FF4DFF","#FF4DE1","#FF4DC4","#FF4DA6","#FF4D88","#FF4D6A","#FF6666","#FF8066","#FF9966","#FFB366","#FFCC66","#FFE666","#FFFF66","#E6FF66","#CCFF66","#B3FF66","#99FF66","#80FF66","#66FF66","#66FF80","#66FF99","#66FFB3","#66FFCC","#66FFE6","#66FFFF","#66E6FF","#66CCFF","#66B3FF","#6699FF","#6680FF","#6666FF","#8066FF","#9966FF","#B366FF","#CC66FF","#E666FF","#FF66FF","#FF66E6","#FF66CC","#FF66B3","#FF6699","#FF6680","#FF8080","#FF9580","#FFAA80","#FFBF80","#FFD580","#FFEA80","#FFFF80","#EAFF80","#D5FF80","#BFFF80","#AAFF80","#95FF80","#80FF80","#80FF95","#80FFAA","#80FFBF","#80FFD5","#80FFEA","#80FFFF","#80EAFF","#80D5FF","#80BFFF","#80AAFF","#8095FF","#8080FF","#9580FF","#AA80FF","#BF80FF","#D580FF","#EA80FF","#FF80FF","#FF80EA","#FF80D5","#FF80BF","#FF80AA","#FF8095","#FF9999","#FFAA99","#FFBB99","#FFCC99","#FFDD99","#FFEE99","#FFFF99","#EEFF99","#DDFF99","#CCFF99","#BBFF99","#AAFF99","#99FF99","#99FFAA","#99FFBB","#99FFCC","#99FFDD","#99FFEE","#99FFFF","#99EEFF","#99DDFF","#99CCFF","#99BBFF","#99AAFF","#9999FF","#AA99FF","#BB99FF","#CC99FF","#DD99FF","#EE99FF","#FF99FF","#FF99EE","#FF99DD","#FF99CC","#FF99BB","#FF99AA","#FFB3B3","#FFBFB3","#FFCCB3","#FFD9B3","#FFE6B3","#FFF2B3","#FFFFB3","#F2FFB3","#E6FFB3","#D9FFB3","#CCFFB3","#BFFFB3","#B3FFB3","#B3FFBF","#B3FFCC","#B3FFD9","#B3FFE6","#B3FFF2","#B3FFFF","#B3F2FF","#B3E6FF","#B3D9FF","#B3CCFF","#B3BFFF","#B3B3FF","#BFB3FF","#CCB3FF","#D9B3FF","#E6B3FF","#F2B3FF","#FFB3FF","#FFB3F2","#FFB3E6","#FFB3D9","#FFB3CC","#FFB3BF",
			       "#000000",  "#0E0E0E", "#151515", "#1C1C1C", "#232323", "#2B2B2B", "#323232", "#393939", "#404040", "#474747", "#4E4E4E", "#555555", "#5C5C5C", "#636363", "#6A6A6A", "#717171", "#787878", "#808080", "#878787", "#8E8E8E", "#959595", "#9C9C9C", "#A3A3A3", "#AAAAAA", "#B1B1B1", "#B8B8B8", "#BFBFBF", "#C6C6C6", "#CDCDCD", "#D5D5D5", "#DCDCDC", "#E3E3E3", "#EAEAEA", "#F1F1F1", "#F8F8F8", "#FFFFFF");
	var colors=new Array(colors1,colors2,colors3,colors4);
	var total = colors1.length;
	var width = 36;
	var cp_contents = "";
	var windowRef = (windowMode)?"window.opener.":"";
	if (windowMode) {
		cp_contents += "<HTML><HEAD><TITLE>Select Color</TITLE></HEAD>";
		cp_contents += "<BODY MARGINWIDTH=0 MARGINHEIGHT=0 LEFMARGIN=0 TOPMARGIN=0><CENTER>";
		}
	cp_contents += "<TABLE id='pickercolortable' BORDER=0 CELLSPACING=0 CELLPADDING=0  cols=18>";
	var use_highlight = (document.getElementById || document.all)?true:false;
	for (var j=0;j<colors.length;j++) {
	  if (j==1) 	cp_contents += "<tbody id='palette"+j+"'>";
	  else 	cp_contents += "<tbody  id='palette"+j+"' style='display:none'>";
	  for (var i=0; i<total; i++) {
		if ((i % width) == 0) { cp_contents += "<TR>"; }
		if (use_highlight) { var mo = 'onMouseOver="'+windowRef+'ColorPicker_highlightColor(\''+colors[j][i]+'\',window.document)"'; }
		else { mo = ""; }
		cp_contents += '<TD '+mo+' style="width:6px;height:5px;background-color:'+colors[j][i]+'" onClick="'+windowRef+'ColorPicker_pickColor(\''+colors[j][i]+'\','+windowRef+'window.popupWindowObjects['+cp.index+']);return false;" </TD>';
		if ( ((i+1)>=total) || (((i+1) % width) == 0)) { 
			cp_contents += "</TR>";
			}
		}
	cp_contents += "</tbody>";
	}
	// If the browser supports dynamically changing TD cells, add the fancy stuff
	if (document.getElementById) {
		var width1 = Math.floor(width/2);
		var width2 = width = width1;
		width2--;
		cp_contents += "<tfoot><TR><TD COLSPAN='"+width1+"' BGCOLOR='#ffffff' ID='colorPickerSelectedColor'>&nbsp;</TD><TD COLSPAN='"+width2+"' ALIGN='CENTER' style='font-family:monospace' ID='colorPickerSelectedColorValue'>#FFFFFF</TD><td title='saturation' style='cursor:pointer' onclick='ColorPicker_changepalette()'>o</td></TR></tfoot>";
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
