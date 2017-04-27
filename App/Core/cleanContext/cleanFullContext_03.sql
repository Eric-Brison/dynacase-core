BEGIN;
delete from docperm where not exists (select 1 from docread where docid=id );
delete from docpermext where not exists (select 1 from docread where docid=id);
COMMIT;