

function set_form_par(form,key,val,sub) {
   self.document[form].key.value=key;
   if (key == 'all') {
     self.document[form].submit();
     return;
   }
   self.document[form][key].value=val;
   if (sub == 1) {
     self.document[form].submit();
   }
}
