begin;
  delete from docfrom;
  insert INTO docfrom (id, fromid) select id, fromid from doc;
  update docfrom set fromid=-1 where id in (select id from docfam);
end;