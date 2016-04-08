
/**
 * @author Anakeen
 */


// use when submit to avoid first unused item
function deletenew() {
  resetInputs('newcond');
  var na=document.getElementById('newcond');
  if (na) na.parentNode.removeChild(na); 
  na=document.getElementById('newstate');
  if (na) na.parentNode.removeChild(na);
  
  
}
  
function selectalls(ids) {
  var s= document.getElementById(ids);
  var i;
  if (! s.multiple) s.multiple=true;
  for (i=0;i<s.options.length;i++) {
    s.options[i].selected=true;
  }
}

function selectallcol() {
  deletenew();
  selectalls('rep_idcols');
  
}
