/**
 * @author Anakeen
 */

var isNetscape = navigator.appName === "Netscape";
var isIE = navigator.appName === "Microsoft Internet Explorer";
var isIE6 = /msie 6/i.test(navigator.userAgent);
var isIE7 = /msie 7/i.test(navigator.userAgent);
var isIE8 = /msie 8/i.test(navigator.userAgent);
var isIE9 = /msie 9/i.test(navigator.userAgent);
var isIE10 = /\bmsie 10\b/i.test(navigator.userAgent);
var isSafari = navigator.userAgent.indexOf('AppleWebKit/') > -1;
function getMouseButton(event) {
    // 1 is the left, 2 the middle, 3 the right button
    var button;
    if (window.event) {
        button = window.event.button;
    } else {
        button = event.button + 1;
    }
    return button;
}

var windows = {};

function displayPropertyNames(obj) {
    var names = "";
    for (var name in obj) {
        try {
            names += name + " - [" + obj[name] + "]\n ";
        }
        catch (ex) {
            names += name + " - [" + "unreadable" + "]\n";
        }
    }
    alert(names);
}

function getConnexeWindows(w) {
    var cw;

    try {
        cw = getChildFrames(w.top);
        if ((w.top.opener) && (w.top.opener != w.top)) {
            cw = cw + getConnexeWindows(w.top.opener);
        }
    }
    catch (ex) {
    }
    return cw;
}
function getChildFrames(w) {

    var fnames = '';
    var fname = '';

    try {
        fname = w.frames;
    }
    catch (ex) {
        return fnames;
    }
    for (var i = 0; i < w.frames.length; i++) {
        try {
            fname = w.frames[i].name;
        }
        catch (ex) {
            fname = '';
        }
        if (fname && (fname != '')) {
            windows[fname] = w.frames[i];
        }
        fnames = fnames + ' ' + i + fname;
        fnames = fnames + getChildFrames(w.frames[i]);
    }

    return fnames;
}

function getParentWindow(w) {
    var pWindow=null;
    if (!w) {
        w=window;
    }

    try {
        if (w.parent.document.domain) {
            pWindow = w.parent;
        }
    } catch (e) {
        pWindow=null;
    }
    return pWindow;
}

function windowExist(Name, NoOpen) {
    var w;
	var pWindow=getParentWindow();

    getChildFrames(window);

    if (windows[Name]) {
        if (windows[Name] == 'none') {
            return false;
        }
        if (windows[Name].closed) {
            return false;
        }
        try {
            w = windows[Name].document;
        }
        catch (ex) {
            windows[Name] = 'none';
            return false;
        }
        return  windows[Name];
    }

    if (pWindow) {
        getChildFrames(pWindow);
        if (windows[Name]) {
            return  windows[Name];
        }
    }

    getConnexeWindows(window);
    if (windows[Name]) {
        return  windows[Name];
    }

    // ---------------------
    // Try open
    if (NoOpen == '') {
        var dy = self.screen.availHeight;
        var dx = self.screen.availWidth;
        w = window.open('', Name, 'top=' + dy + ',left=' + dx + 'menubar=no,resizable=no,scrollbars=no,width=1,height=1');
        if (w.opener && (w.opener.location.href == self.location.href) && (w.location.href == 'about:blank')) {
            w.close();
            windows[Name] = 'none';
            return false;
        }
        windows[Name] = w;
        getConnexeWindows(w);
    }
    return w;
}
var warnmsg = '';
/**
 * Display a warning message
 *
 * If in an extjs context use extjs
 * Or try to find
 *
 * @param logmsg
 */
function displayWarningMsg(logmsg) {
    var msg = logmsg, generateParentList, parentList = [], i, length, currentParent, isFunction;
	var pWindow=getParentWindow();

    isFunction = function(obj) {
      return typeof obj === 'function';
    };
    if (pWindow && pWindow.Ext) {
        msg = msg.replace(new RegExp("\n", "ig"), '<br/>');
        msg = msg.replace(new RegExp("\n", "ig"), '<br/>');
        pWindow.Ext.Msg.alert('Warning', msg);
        return '';
    } else {
        generateParentList = function generateParentList(parent) {
            parentList.unshift(parent);
            try {
                if (parent !== parent.top && parent.parent) {
                    generateParentList(parent.parent);
                }
            } catch (e) {}
        }(window);
        for (i = 0, length = parentList.length; i < length; i += 1) {
            currentParent = parentList[i];
            if (currentParent && currentParent.dcp && isFunction(currentParent.dcp.displayWarningMessage)) {
                setTimeout(function () {
                    currentParent.dcp.displayWarningMessage(logmsg);
                }, 100);
                return '';
            }else if(currentParent && currentParent.$ && currentParent.$.isFunction(currentParent.$().dialog)) {
                setTimeout(function () {
                    var parent$ = currentParent.$, i, logMsgs, length,
                    $wrapper = parent$('<p></p>');
                    logMsgs = logmsg.split("\n");
                    for (i = 0 , length = logMsgs.length ; i < length; i+=1) {
                        $wrapper.append(parent$("<span></span>").text(logMsgs[i]));
                        if (i < length -1) {
                            $wrapper.append("<br/>");
                        }
                    }

                    $wrapper = parent$('<div class="ui-state-highlight"></div>').append($wrapper);

                    parent$('<div></div>').append($wrapper)
                        .dialog({
                            modal:true,
                            title:'<span class="ui-icon ui-icon-info"></span>'
                        });
                }, 500);
                return '';
            }
        }
    }
    setTimeout(function () {alert(msg);},1000);
    return '';
}
function displayLogMsg(logmsg) {
    var i, logi;
    var wTop;
    if (logmsg.length == 0) return;

    if ("console" in window) {
        for (i = 0; i < logmsg.length; i++) {
            console.log(logmsg[i]);
        }
    }
    try {
        if (window.top.document.domain) {
            wTop = window.top;
        }
    } catch (e) {
        wTop=null;
    }

    var log = false;
    if (wTop && wTop.foot) {
        log = wTop.foot.document.getElementById('slog');
    } else {
        // redirect to foot function
        if (window.name != "foot") {
            var wfoot = windowExist('foot');
            if (wfoot)  {
                wfoot.displayLogMsg(logmsg);
            }
        }
        return;
    }

    if (log) {
        var classn = 'CORETblCell';
        var k = 0;

        if ((log.options) && (log.options.length > 0)) {
            if (log.options[log.options.length - 1].className == "CORETblCell") {
                classn = 'CORETblCellAltern';
            }
            k = log.options.length;
        }
        for (i = 0; i < logmsg.length; i++) {
            log.options[k] = new Option(logmsg[i], '', false, false);
            log.options[k].className = classn;
            k = log.options.length;
        }
        log.selectedIndex = log.options.length - 1;

        logi = wTop.foot.document.getElementById('ilog');
        if ((!log.options) || (log.options.length == 0)) {
            logi.style.display = 'none';
        } else {
            logi.style.display = 'inline';
        }
    }

}

// Utility function to add an event listener
function addEvent(o, e, f) {
    if (o.addEventListener) {
        o.addEventListener(e, f, true);
        return true;
    }
    else if (o.attachEvent) {
        return o.attachEvent("on" + e, f);
    }
    else {
        return false;
    }
}
// Utility function to add an event listener
function delEvent(o, e, f) {
    if (o.removeEventListener) {
        o.removeEventListener(e, f, true);
        return true;
    }
    else if (o.detachEvent) {
        return o.detachEvent("on" + e, f);
    }
    else {
        return false;
    }
}
function correctPNG() {// correctly handle PNG transparency in Win IE 5.5 and IE < 7.0
    if (isIE6) {
        for (var i = 0; i < document.images.length; i++) {
            _correctOnePNG(document.images[i]);
        }
    }
}
function correctOnePNG(img, iknowitisapng) {// correctly handle PNG transparency in Win IE 5.5 and < 7.0
    if (isIE6) {
        _correctOnePNG(img, iknowitisapng);
    }
}

function _correctOnePNG(img, iknowitisapng) {// correctly handle PNG transparency in Win IE 5.5 or higher.
    if (img.className == 'icon') {
        return;
    }
    var imgName = img.src.toUpperCase();
    if ((iknowitisapng == true) || (imgName.substring(imgName.length - 3, imgName.length) == "PNG")) {
        img.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + img.src + "',sizingMethod='scale') ";
        img.style.width = img.width;
        img.style.height = img.height;
        img.src = 'Images/1x1.gif';
    }
}
function sendActionNotification(code, arg) {
    var pWindow=getParentWindow(), ppWindow=null;

    if (pWindow) {
        ppWindow=getParentWindow(pWindow);
    }
    if (window) {
        if (window.receiptActionNotification) {
            window.receiptActionNotification(code, arg);
        }
    }

    if (window.opener) {
        try {
            if (window.opener.receiptActionNotification) {
                window.opener.receiptActionNotification(code, arg);
            }
        } catch (e) {}
    }
    if (pWindow && (window != pWindow)) {
        if (pWindow.receiptActionNotification) {
            pWindow.receiptActionNotification(code, arg);
        }
    }
    if (ppWindow && (window != ppWindow)) {
        if (ppWindow.receiptActionNotification) {
            ppWindow.receiptActionNotification(code, arg);
        }
    }

}

var CGCURSOR = 'auto'; // current global cursor
function globalcursor(c, w) {
    var theSheet;

    if (!w) {
        w = window;
    }
    if ((!w) && (c == CGCURSOR)) {
        return;
    }
    if (!w.document.styleSheets) {
        if (w.document.createStyleSheet) {
            w.document.createStyleSheet("javascript:''");
        }
        else {
            var newSS = w.document.createElement('link');
            newSS.rel = 'stylesheet';
            newSS.href = 'data:text/css';
        }

    }

    unglobalcursor();

    if (w.document.styleSheets.length == 1) {
        theSheet = w.document.styleSheets[0];
    }
    else {
        theSheet = w.document.styleSheets[1];
    }

    if (!theSheet) {
        return;
    }
    if (theSheet.addRule) {
        theSheet.addRule("*", "cursor:" + c + " ! important", 0);
    } else if (theSheet.insertRule) {
        theSheet.insertRule("*{cursor:" + c + " ! important;}", 0);
    }
    CGCURSOR = c;

}
function unglobalcursor(w) {
    if (!w) {
        w = window;
    }
    if (!w.document.styleSheets) {
        return;
    }
    var theRules;
    var theSheet;
    var r0;

    if (w.document.styleSheets.length == 1) theSheet = w.document.styleSheets[0];
    else theSheet = w.document.styleSheets[1];
    if (!theSheet) return;
    if (theSheet.cssRules)
        theRules = theSheet.cssRules;
    else if (theSheet.rules)
        theRules = theSheet.rules;
    else return;
    if (theRules.length > 0) {
        r0 = theRules[0].selectorText;
        if ((r0 == '*') || (r0 == '')) {
            if (theSheet.removeRule) {
                theSheet.removeRule(0);
            } else if (theSheet.deleteRule) {
                theSheet.deleteRule(0);
            }
        }
    }
    CGCURSOR = 'auto';

}
function include_js(script_filename) {
    var jsver = is_already_include_js(script_filename);
    if (jsver >= 0) {
        var html_doc = document.getElementsByTagName('head').item(0);
        var js = document.createElement('script');
        js.setAttribute('language', 'javascript');
        js.setAttribute('type', 'text/javascript');
        if (jsver > 0) {
            script_filename += '?wv=' + jsver;
        }
        js.setAttribute('src', script_filename);
        html_doc.appendChild(js);
    }
    return false;
}
function is_already_include_js(file) {
    var html_doc = document.getElementsByTagName('script');
    var i;
    var jssrc;
    var pat = /wv=([0-9]+)/;
    var rp;
    var jsver = 0;
    for (i = 0; i < html_doc.length; i++) {
        jssrc = html_doc[i].getAttribute('src');
        if (jssrc) {
            if (jssrc == file) {
                return -1;
            }
            if (jssrc.indexOf(file) >= 0) {
                return -1;
            }
            if ((!jsver) && ( rp = pat.exec(jssrc))) {
                jsver = rp[1];
            }
        }
    }
    return jsver;
}
function getAbsPath(file) {
    image1 = new Image(); // not terrible: do a download
    image1.src = file;
    return image1.src;
}
