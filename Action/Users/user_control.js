  function setFrameHeight(h) {
     if ( parseInt(navigator.appVersion) >3 ) {
          window.resizeTo(getFrameWidth(),h);
      }
  }

  function getFrameWidth() {
      winW = window.innerWidth;
      if (! winW)	   
            winW = document.body.offsetWidth;

      return (winW);
  }
  
  function getFrameHeight() {
      winH= window.innerHeight;
      if (! winH)	
           winH = document.body.offsetHeight;
      return (winH);
  }

// select the group domain in group list
  function selectGroupDomain (domain) {

      var odomain=0; //option group domain
      var oall =0;   //option group all

	var grsel;

	
	var grview = document.getElementById('groupview');
	if (! grview) return;

	for (gr=0; gr < 2; gr++) {

	  if (gr == 0) {
	    grsel = document.getElementById('othergroup');
	  } else {
	    grsel = document.getElementById('domaingroup');
	  }
	  if (grsel && grview) {
	    for (i=0; i< grsel.length; i++) {
	      if (grsel.options[i].text.substring(0,4) == "all@") {

		  if (grsel.options[i].text.substring(4) == "local") oall = grsel.options[i]
		if (grsel.options[i].text.substring(4) == domain) {
		  // find default domain group
		  odomain = grsel.options[i];
		}

	      }
	    }
	    // unselect all domain group
            for (j=0; j< grsel.length; j++) 
              if (grsel.options[j].text.substring(0,4) == "all@") 
		grsel.options[j].selected = false;

	  }
	  
	}
	
	  if (odomain) odomain.selected =true;
	  else if (oall) oall.selected = true;

	  refreshGroupView();

  }

  function refreshGroupView() {
      var v=0;
	var grsel1 = document.getElementById('othergroup');
	var grsel2 = document.getElementById('domaingroup');
	var grview = document.getElementById('groupview');
        if (grsel1 && grsel2 && grview) {
          
	  for (i=0; i< grsel1.length; i++) {
	     if (grsel1.options[i].selected) {
	             grview.options[v] = new Option;
		     grview.options[v].disabled  = true;
		     grview.options[v].text  = grsel1.options[i].text;
		     grview.options[v].value  = grsel1.options[i].value;
		     v++;
                   }         		
	    }
	  for (i=0; i< grsel2.length; i++) {
	     if (grsel2.options[i].selected) {
	             grview.options[v] = new Option;
		     grview.options[v].disabled  = true;
		     grview.options[v].text  = grsel2.options[i].text;
		     grview.options[v].value  = grsel2.options[i].value;
		     v++;
                   }         		
	    }


          grview.length = v; 
      }
  }

var groupdisplayed=false;

function groupVisibility() { // hidden or visible
  if (document.getElementById) {  // DOM3 = IE5, NS6
    if (! groupdisplayed) {
       if (getFrameHeight() < 480) {
         setFrameHeight(480);
       }
    }
    if (! groupdisplayed) {
     //document.getElementById('changegroup').style.visibility = 'visible';    
     document.getElementById('changegroup').style.display = 'inline';
    } else {
     //document.getElementById('changegroup').style.visibility = 'hidden';
     document.getElementById('changegroup').style.display = 'none';
    }
    groupdisplayed = ! groupdisplayed;
   } 
   
   
}


function Valid_Send()
{
  var reLog = /^[\w-]+(\.[\w-]+)?$/
  var ok=true;
  if (document.edit.login.value == "") {
    alert("Le login est obligatoire");
    return false;
  }

  if (document.edit.id.value == "" &&
      (document.edit.login.value == "postmaster"
    || document.edit.login.value == "webmaster"
    || document.edit.login.value == "hostmaster"
    || document.edit.login.value == "admin")) {
    alert('Ce nom de login n\'est pas autorisé !');
    return false;
  }

  if (!reLog.test(document.edit.login.value)) {
    alert('Le login saisi est invalide !');
    return false;
  }

  if (document.edit.group.value == "no") {
    // group have not passwd
    if (document.edit.id.value == "" &&
	document.edit.passwd.value == "" |
	document.edit.passwdchk.value == "") {
      document.edit.passwd.value = "";
      document.edit.passwdchk.value = "";
      alert("Le mot de passe est obligatoire");
      return false;
    }
    if (document.edit.passwd.value != document.edit.passwdchk.value) {
      alert("Les mots de passe saisis sont différents !\nRecommencez.");
      document.edit.passwd.value = "";
      document.edit.passwdchk.value = "";
      return false;
    }
  }
  
  // select element to send values to PHP
  var grview = document.getElementById('groupview');
  for (i=0; i< grview.length; i++) {
	      grview.options[i].selected=true;
	      grview.options[i].disabled=false;
  }
	
  document.edit.submit();
  if (document.edit.id.value == "") {
    document.edit.reset();
    selectGroupDomain(0);
    return true;
  } else {
    // close after submited is done
    setTimeout('self.close()',10);
  }
}
