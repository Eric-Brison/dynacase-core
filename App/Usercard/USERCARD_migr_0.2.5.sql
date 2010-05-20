alter table doc120 add column us_role text;
alter table doc120 add column us_scatg text;

update doc120 set us_role=us_type;
update doc120 set us_scatg=us_catg;
update docattr set phpfunc=(select phpfunc from docattr where docid=120 and id='us_catg') where docid=124 and id='si_catg';

delete from docattr where id='us_catg';
--alter table doc120 drop column us_catg;
