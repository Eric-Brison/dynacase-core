begin;
  update doc set name = null where name ~ '^TEMPORARY_';
  delete from docname;
  insert INTO docname (name, id, fromid) select name,id, fromid from doc where name is not null and name != '' and locked != -1;
end;