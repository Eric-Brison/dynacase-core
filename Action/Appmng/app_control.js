function Valid_Send()
{
  var reLog = /^[\w-]+(\.[\w-]+)?$/
  var ok=true;
  if ( (document.edit.name.value == "") ||
       (document.edit.short_name.value == "") ||
       (document.edit.description.value == "") ||
       (document.edit.access_free.options[document.edit.access_free.selectedIndex].value == "") ||
       (document.edit.available.options[document.edit.available.selectedIndex].value == "") ||
       (document.edit.displayable.options[document.edit.displayable.selectedIndex].value == "")  )

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
