function Valid_Send()
{
  var reLog = /^[\w-]+(\.[\w-]+)?$/
  var ok=true;
  if ( (document.edit.name.value == "") ||
       (document.edit.val.value == "") )

  {
    alert("Tous les champs sont obligatoires !!!");
    return false;
  }

  sendform();
  if (document.edit.id.value == "") {
    return true;
  } else {
    self.close();
  }
}
