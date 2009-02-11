function Valid_Send()
{
  

  sendform();
  return true;
}
function setopenertarget(o) {
  if (o) {
    var f=o.form;
    if (window.opener) {
      var n=window.opener.name;
      if (n) f.target=n;
    }
  }
}
