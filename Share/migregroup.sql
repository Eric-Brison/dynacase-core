--use to add constrint on unique
create table groups2 ( iduser, idgroup) as (select distinct on ( iduser, idgroup) iduser,idgroup from groups);
insert into groups(iduser, idgroup) select (iduser, idgroup) from groups2;
create unique index groups_idx2 on groups(iduser,idgroup);
DROP TABLE groups2;
