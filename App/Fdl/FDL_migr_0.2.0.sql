

UPDATE docattr set needed='N';
UPDATE docattr set needed='Y' where visibility='N';
UPDATE docattr set visibility='W' where visibility='N';
UPDATE docattr set visibility='F' where type='frame';
UPDATE doc set classname='' where classname='DocUser';


create table docfamtemp (id int, cprofid int, dfldid int) ;
insert into docfamtemp (id, cprofid, dfldid) select id, cprofid, dfldid from only doc where  doctype='C';

