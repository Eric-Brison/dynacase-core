
alter table doc4 rename to doc4temp;
drop index doc_pkey4;
drop sequence seq_doc4;

insert into doc4(id,owner,title,revision,initid,fromid,doctype,locked,icon,lmodify,profid,usefor,revdate,comment,classname,state,wid,values,attrids,ba_title) select id,owner,title,revision,initid,fromid,doctype,locked,icon,lmodify,profid,usefor,revdate,comment,classname,state,wid,values,attrids,ba_title from doc4temp;

drop table doc4temp;
