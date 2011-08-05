
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */


var isNetscape = navigator.appName=="Netscape";

function setiinput(event,idx) {
  correctlevel();

  var sal= document.getElementById('ialabel');
  var sre = document.getElementById('iaref');
  var sle = document.getElementById('ialevel');

  if (sal.multiple) sal.multiple=false;
  if (sre.multiple) sre.multiple=false;
  if (sle.multiple) sle.multiple=false;
  //  var idx=sal.selectedIndex;
  if (idx == -1) return;

  sre.selectedIndex=idx;  
  sle.selectedIndex=idx;  
  sal.selectedIndex=idx;    
  var ila = sal.options[idx].value;
  var ire = sre.options[idx].value;

  document.getElementById('ilabel').value=ila;
  document.getElementById('iref').value=ire;
}

function selectalls(ids) {
  var s= document.getElementById(ids);
  var i;
  if (! s.multiple) s.multiple=true;
  for (i=0;i<s.options.length;i++) {
    s.options[i].selected=true;
  }
}

function selectall() {
  selectalls('ialabel');
  selectalls('iaref');
  selectalls('ialevel');
}


function correctlevel() {
  var sle= document.getElementById('ialevel');
  var sal= document.getElementById('ialabel');
  var sre= document.getElementById('iaref');
  var i,le;

  var ple = 1;
  // first level is always one
  if (sle.options.length==0) return;
  sle.options[0].value=1;
  sle.options[0].text=sle.options[0].value;
  var ref= '';
  for (i=0;i<sle.options.length;i++) {
    le = parseInt(sle.options[i].value);
    if (le > ple+1) {
      sle.options[i].value= ple+1;
      sle.options[i].text = sle.options[i].value;
      le=ple+1;
    }
    label = '';
    for (l=1;l<le;l++) label='.....'+label;	      
    sal.options[i].text=label+sal.options[i].value;
    if (le == 1) ref=''; 
    else if (ple < le) {
      // add level ref index
      ref = ref  + sre.options[i-1].value+ '.';
    } else  if (ple > le) {
      // suppress one or more level ref index
      for (l=0;l<ple-le;l++)  ref=ref.substr(0,ref.lastIndexOf('.')-1);
    }
    sre.options[i].text=ref+sre.options[i].value;

    ple = le;
  }
}
function upas(ids,idx) {
  var s= document.getElementById(ids);
  
  var t1,s1;
  if (idx >=1 ) {
    t1= s.options[idx].text;
    v1= s.options[idx].value;
    s.options[idx].text = s.options[idx-1].text;
    s.options[idx].value = s.options[idx-1].value;
    s.options[idx-1].text=t1;
    s.options[idx-1].value=v1;
    s.selectedIndex=idx-1;
  }
}
function downas(ids,idx) {
  
  var s= document.getElementById(ids);

  var t1,s1;
  if (idx < s.options.length-1 ) {
    t1= s.options[idx+1].text;
    v1= s.options[idx+1].value;
    s.options[idx+1].text = s.options[idx].text;
    s.options[idx+1].value = s.options[idx].value;
    s.options[idx].text=t1;
    s.options[idx].value=v1;
    s.selectedIndex=idx+1;        
  }
}
function upa(event) {
  var sal= document.getElementById('ialabel');
  var idx=sal.selectedIndex;
  upas('ialabel',idx);
  upas('iaref',idx);
  upas('ialevel',idx);

}

function downa(event) {
  var sal= document.getElementById('ialabel');  
  var idxa=sal.selectedIndex;

  downas('ialabel',idxa);
  downas('iaref',  idxa);
  downas('ialevel',idxa);
}

function righta(event) {
  
  var sal= document.getElementById('ialabel');
  var sle = document.getElementById('ialevel');
  
  var t1,s1;
  var idx=sal.selectedIndex;
  if (idx >=0) {
    sle.options[idx].value++;
    sle.options[idx].text = sle.options[idx].value;
    correctlevel();
  }
}
function lefta(event) {
  
  var sal= document.getElementById('ialabel');
  var sle = document.getElementById('ialevel');
  
  var t1,s1;
  var idx=sal.selectedIndex;
  if (idx >=0) {
    if (parseInt(sle.options[idx].value)>1) {
      sle.options[idx].value--;
      sle.options[idx].text = sle.options[idx].value;
      correctlevel();
    }
  }
}
function adda(event) {
  var sal= document.getElementById('ialabel');
  var sle = document.getElementById('ialevel');
  var sre= document.getElementById('iaref');

  var ila= document.getElementById('ilabel');
  var ire= document.getElementById('iref');

  var pos;
  var idx=sal.selectedIndex;
  if (idx == -1) idx=0;

  if (isNetscape) pos=null;
  else pos=idx+1;
  sal.add(new Option(ila.value, ila.value, false, true),pos);
  sre.add(new Option(ire.value, ire.value, false, true),pos);
  sle.add(new Option('1', '1', false, true),pos);
  correctlevel();
  ila.value='';
  ire.value='';
  ila.focus();
}

function moda(event) {
  var sal= document.getElementById('ialabel');
  var sre= document.getElementById('iaref');

  var ila= document.getElementById('ilabel');
  var ire= document.getElementById('iref');

  var idx=sal.selectedIndex;
  if (idx == -1) return;
  sal.options[idx].value=ila.value;
  sre.options[idx].value=ire.value;
  correctlevel();
}
function rema(event) {
  var sal= document.getElementById('ialabel');
  var sle = document.getElementById('ialevel');
  var sre= document.getElementById('iaref');


  var idx=sal.selectedIndex;
  if (idx == -1) return;
  sal.remove(idx);
  sle.remove(idx);
  sre.remove(idx);
  correctlevel();
}
