// --------------------------------------------------
// $Id: Lib.Address.js,v 1.1 2002/01/08 12:41:34 eric Exp $
// --------------------------------------------------
// $Log: Lib.Address.js,v $
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.7  2001/09/13 08:29:03  yannick
// Gestion de la release
//
// Revision 1.6  2001/09/12 17:33:53  eric
// fonction checkipaddress
//
// Revision 1.5  2001/09/12 14:49:09  yannick
// See changelog
//
// Revision 1.4  2000/10/27 07:50:05  marc
// Controle nom de machine
//
// Revision 1.3  2000/10/24 18:25:50  marc
// alias name validation added
//
// Revision 1.2  2000/10/24 10:40:03  marc
// email address list RegExp added
//
// Revision 1.1.1.1  2000/10/23 09:12:33  marc
// Initial released
//
//
// --------------------------------------------------
function isEmpty(s)
{   return ((s == null) || (s.length == 0))
}

// Check e-mail address
function checkemail(a) {
   var re = /^[ \t\r]*[\w\.-]+@[\w\.-]+[\s]*$/
   return re.test(a);
}

// Check e-mail address list (comma separated)
function checkemaillist(a) {
   var re = /^[\s]*[\w\.-]+@[\w\.-]+[\s]*(,[\s]*[\w\.-]+@[\w\.-]+)*[\s]*$/
   return re.test(a);
}

// Check an alias name
function checkalias(a) {
  var re = /^[\s]*[\w\.-]+[\s]*$/
  return re.test(a);
}

function checkhostname(h) {
  var re = /^[\w-]+(\.[\w-]+)*[\s]*$/
  return re.test(h);
}


function isShortInt(i) {// between 0-255
	return ((i >= 0 ) && (i <=255));	
}
function checkipaddress(ip) {

   var re= new RegExp();
   re = /^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)/;


	myArray = re.exec(ip);
	if (myArray) {
	   for (i=1; i<5;i++) {
	      if (! isShortInt(parseInt(myArray[i]))) {		 
		return false;
             }
           }
           return true;
        } 

  return false;
}
