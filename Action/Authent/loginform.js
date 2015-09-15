var isNN = (navigator.appName.indexOf("Netscape") != -1);

document.onkeypress = trackEnter;

function trackEnter(evt) {
    var intKeyCode;

    if (isNN) {
        intKeyCode = evt.which;
    }
    else {
        intKeyCode = window.event.keyCode;
    }

    if (intKeyCode == 13) { // enter key
        if (document.loginform) login(document.loginform, "[TEXT:warning_name]", '[TEXT:warning_pass]');
        else if (document.reqform) document.reqform.submit();
        else if (document.chgpwd) document.chgpwd.submit();
        return false;
    } else
        return true;
}

function login(aform, usermsg, passmsg) {
    var user = aform.auth_user.value;
    var pass = aform.auth_pass.value;
    if (user == "") {
        document.getElementById('msgerr').innerHTML = usermsg;
        document.forms['loginform'].elements['auth_user'].focus();
        return;
    }
    if (pass == "") {
        document.getElementById('msgerr').innerHTML = passmsg;
        document.forms['loginform'].elements['auth_pass'].focus();
        return;
    }
    aform.submit();
}

function aumilieu(eid) {
    var winH = getFrameHeight();
    var winW = getFrameWidth();
    if (document.getElementById) { // DOM3 = IE5, NS6
        var divlogo = document.getElementById(eid);
        if (divlogo) {
            divlogo.style.position = 'absolute';
            if ((winH > 0) && (winW > 0)) {
                divlogo.style.top = (winH / 2 - (divlogo.offsetHeight / 2) + document.body.scrollTop) + 'px';
                divlogo.style.left = (winW / 2 - (divlogo.offsetWidth / 2)) + 'px';
            }
        }
    }
    return true;
}

function display_help() {
    aumilieu('zonehelp', 2);
    document.getElementById('zonehelp').style.visibility = 'visible';
    document.getElementById('zonehelp').style.zIndex = 100;
}
function close_help() {
    document.getElementById('zonehelp').style.visibility = 'hidden';
}
function centerZone() {
    aumilieu('main');
    aumilieu('zonehelp');
}

function appendRedirectHashFragment() {
    if (window.location.hash == '') {
        return;
    }
    var elmts = document.getElementsByName('redirect_uri');
    for (var i = 0; i < elmts.length; i++) {
        elmts[i].value = elmts[i].value + window.location.hash;
    }
}

function initZone() {
    appendRedirectHashFragment();

    centerZone();
    
    if (document.getElementById('zonehelp')) document.getElementById('zonehelp').style.visibility = 'hidden';
    document.getElementById('main').style.visibility = 'visible';
    var focus = document.getElementById("passfocus");
    if (focus) {
     focus = document.getElementById("passfocus").value ? 'auth_pass' : 'auth_user';
        var ifocus = document.getElementById(focus);
        if (ifocus) {
            ifocus.focus();
        }
    }

}
addEvent(window, 'load', initZone);
addEvent(window, 'resize', centerZone);
