create or replace function in_textlist(text[], text)
returns bool as $func$
  select $2 = any($1)
$func$ language sql;

CREATE OR REPLACE FUNCTION iregexp_commutator(text, text)
  RETURNS bool AS
$func$
SELECT $2 ~* $1
$func$  LANGUAGE sql IMMUTABLE;

CREATE OR REPLACE FUNCTION regexp_commutator(text, text)
  RETURNS bool AS
$func$
SELECT $2 ~ $1
$func$  LANGUAGE sql IMMUTABLE;

DROP OPERATOR IF EXISTS ~*<(text, text);
DROP OPERATOR IF EXISTS ~<(text, text);
CREATE OPERATOR ~*< ( leftarg = text, rightarg = text, procedure = iregexp_commutator);
CREATE OPERATOR ~< ( leftarg = text, rightarg = text, procedure = regexp_commutator);
COMMENT ON OPERATOR ~*<(text, text) IS 'insensitive regexp commutator';
COMMENT ON OPERATOR ~<(text, text) IS 'regexp commutator';

-- to order family by depth
create or replace function familyLevel(int)
returns int as $$
declare
  famid alias for $1;
  sfromid int;
begin
  select into sfromid fromid from family.families where id=famid;
  if (sfromid > 0)  then
    return 1 + familyLevel(sfromid);
  end if;
  return 0;
end;
$$ language 'plpgsql';


CREATE OR REPLACE FUNCTION attributeIsMultiple(IN _family name, IN _column name)
    RETURNS bool
    LANGUAGE plpgsql
    AS
    $$
    DECLARE
	isMultiple bool;
	famId int;
    BEGIN
      select id into famId from family.families where lower(name)=_family;
      select true into isMultiple from docattr where docid=famId and id=_column and options ~ E'\\ymultiple=yes\\y';
      IF isMultiple is null THEN
         -- try if in array
          select true into isMultiple from docattr
               where id = (select frameid from docattr where docid=famId and id=_column)
           and substring(type for 5) = 'array';
      END IF;
      RETURN isMultiple;
    END;
$$;

-- change type of column
create or replace function alter_table_column(text, text, text)
returns bool as $$
declare 
  t alias for $1;
  col alias for $2;
  ctype alias for $3;
begin
   EXECUTE 'ALTER TABLE ' || quote_ident(t) || ' RENAME COLUMN ' || col || ' TO zou' || col;
   EXECUTE 'ALTER TABLE ' || quote_ident(t) || ' ADD COLUMN '  || col || ' ' || ctype;
   EXECUTE 'UPDATE ' || quote_ident(t) || ' set ' || col || ' = ' || 'zou' || col|| '::' || ctype;
   EXECUTE 'ALTER TABLE ' || quote_ident(t) ||  'DROP COLUMN  zou' || col ;
 
   return true;
end;
$$ language 'plpgsql';

create or replace function flog(int, int) 
returns bool as $$
declare 
  tlog int;
begin

   select into tlog t from log ;
    if (tlog is null) then
      tlog:=0;
      insert into log (t) values (0); 
   end if;
   tlog := tlog+1;
   update log set t=tlog;
return true;


end;
$$ language 'plpgsql' ;



create or replace function getaperm(int[], int)
returns int as $$
declare
  a_accounts alias for $1;
  a_profid alias for $2;
  uperm int;
begin
   if (a_profid <= 0) then
     return -1; -- it is no controlled object
   end if;
   -- can use intset(userid) instead of ('{'||userid||'}') if intarray module installed
   select into uperm bit_or(upacl) from docperm where docid=a_profid and ('{'||userid||'}')::int[] && a_accounts;

   if (uperm is null) then
     return 0;
   end if;

   return uperm;
end;
$$ language 'plpgsql';




create or replace function hasaprivilege(int[], int, int)
returns bool as $$
declare
  a_account alias for $1;
  a_profid alias for $2;
  a_pos alias for $3;
  uperm int;
begin

   uperm := getaperm(a_account, a_profid);


   return ((uperm & a_pos) = a_pos);
end;
$$ language 'plpgsql' ;

-- The TRIGGERS -----------


-- AFTER INSERT OR UPDATE ON family.*
CREATE OR REPLACE FUNCTION upvaldocfam() RETURNS trigger AS $$
declare
begin
NEW.values := '£' || coalesce(NEW.cprofid,0) || '£' || coalesce(NEW.dfldid,0) || '£' || coalesce(NEW.cfldid,0) || '£' || coalesce(NEW.ccvid,0) || '£' || coalesce(NEW.ddocid,0) || '£' || coalesce(NEW.methods,'') || '£' || coalesce(NEW.defval,'') || '£' || coalesce(NEW.param,'') || '£' || coalesce(NEW.maxrev,0) || '£' || coalesce(NEW.tagable,'') || '£' || coalesce(NEW.schar,'') || '£';
NEW.attrids := '£cprofid£dfldid£cfldid£ccvid£ddocid£methods£defval£param£maxrev£tagable£schar£';

return NEW;
end;
$$ language 'plpgsql';


-- AFTER INSERT OR UPDATE ON filecontent.*
CREATE OR REPLACE FUNCTION updatesearchfilecontent() RETURNS trigger AS $$
declare
begin

update family.documents set id = id where id= NEW.id;

return NEW;
end;
$$ language 'plpgsql';

-- AFTER DELETE ON family.*
create or replace function deletedoc()
returns trigger as $$
declare
begin

if (TG_OP = 'DELETE') then
   delete from docread where id=OLD.id;
   delete from search.documents where id=OLD.id;
   delete from filecontent.documents where id=OLD.id;
   --delete from fld where childid=OLD.id;
   delete from docfrom where id=OLD.id;
   delete from docname where id=OLD.id;
   delete from docperm where docid=OLD.id;
   delete from docpermext where docid=OLD.id;
end if;

return NEW;
end;
$$ language 'plpgsql';


-- BEFORE UPDATE ON family.*
create or replace function resetvalues() 
returns trigger as $$
declare 
   lname text;
   cfromid int;
begin

NEW.values:='';
NEW.attrids:='';

if (NEW.doctype = 'Z') and (NEW.name is not null) then
	delete from docname where name=NEW.name;
end if;	
if (NEW.name is not null and OLD.name is null) then
  if (NEW.doctype = 'C') then 
       cfromid=-1; -- for families
     else
       cfromid=NEW.fromid;
     end if;
     select into lname name from docname where name= NEW.name;
     if (lname = NEW.name) then 
 	 update docname set fromid=cfromid,id=NEW.id where name=NEW.name;	
     else 
	 insert into docname (id,fromid,name) values (NEW.id, cfromid, NEW.name);
     end if;
  end if;
return NEW;
end;
$$ language 'plpgsql';


create or replace function setweight2(text,"char") 
returns tsvector as $$
declare 
  a_text alias for $1;
  a_weight alias for $2;
begin
   if (a_text is null) or (a_text = '') then
     return to_tsvector('simple','');
   else
     return setweight(to_tsvector('search.french',a_text), a_weight);
   end if;      
end;
$$ language 'plpgsql' ;

create or replace function setweight2(text) 
returns tsvector as $$
declare   
begin
     return setweight2($1, 'D');
end;
$$ language 'plpgsql' ;


-- AFTER INSERT ON family.*
create or replace function fixeddoc() 
returns trigger as $$
declare 
   lid int;
   lname text;
   cfromid int;
   cfromname text;
begin


if (TG_OP = 'INSERT') then
     if (NEW.doctype = 'C') then
       cfromid=-1; -- for families
     else
       cfromid=NEW.fromid;
       cfromname=lower(NEW.fromname);
       if (NEW.revision > 0) then
         EXECUTE 'update family.' || cfromname || ' set lmodify=''N'' where initid= ' || NEW.initid || ' and lmodify != ''N''';
         EXECUTE 'update family.' || cfromname || ' set lmodify=''L'' where  id=(select distinct on (initid) id from only family.' || cfromname || ' where initid = ' || NEW.initid || ' and locked = -1 order by initid, revision desc)';
       end if;
     end if;
     select into lid id from docfrom where id= NEW.id;
     if (lid = NEW.id) then 
	      update docfrom set fromid=cfromid where id=NEW.id;
     else 
	      insert into docfrom (id,fromid) values (NEW.id, cfromid);
     end if;
     if (NEW.name is not null) then
       select into lname name from docname where name= NEW.name;
       if (lname = NEW.name) then 
        	 update docname set fromid=cfromid,id=NEW.id where name=NEW.name;
       else 
	         insert into docname (id,fromid,name) values (NEW.id, cfromid, NEW.name);
       end if;
     end if;
end if;
 
return NEW;
end;
$$ language 'plpgsql';


-- AFTER INSERT OR UPDATE ON family.*
create or replace function setread() 
returns trigger as '
declare 
   lid int;
   lname text;
   cfromid int;
begin

if ((TG_OP = ''UPDATE'') OR (TG_OP = ''INSERT'')) then
  if  NEW.doctype != ''T'' then
     select into lid id from docread where id= NEW.id;
     if (lid = NEW.id) then 
	update docread set id=NEW.id,owner=NEW.owner,title=NEW.title,revision=NEW.revision,initid=NEW.initid,fromid=NEW.fromid,fromname=NEW.fromname,doctype=NEW.doctype,locked=NEW.locked,allocated=NEW.allocated,archiveid=NEW.archiveid,icon=NEW.icon,lmodify=NEW.lmodify,profid=NEW.profid,views=NEW.views,usefor=NEW.usefor,revdate=NEW.revdate,version=NEW.version,cdate=NEW.cdate,adate=NEW.adate,classname=NEW.classname,state=NEW.state,wid=NEW.wid,attrids=NEW.attrids,postitid=NEW.postitid,lockdomainid=NEW.lockdomainid,domainid=NEW.domainid,cvid=NEW.cvid,name=NEW.name,dprofid=NEW.dprofid,prelid=NEW.prelid,atags=NEW.atags,confidential=NEW.confidential,ldapdn=NEW.ldapdn,values=NEW.values where id=NEW.id;
     else 
	insert into docread(id,owner,title,revision,initid,fromid,fromname,doctype,locked,allocated,archiveid,icon,lmodify,profid,views,usefor,revdate,version,cdate,adate,classname,state,wid,attrids,postitid,lockdomainid,domainid,cvid,name,dprofid,prelid,atags,confidential,ldapdn,values) values (NEW.id,NEW.owner,NEW.title,NEW.revision,NEW.initid,NEW.fromid,NEW.fromname,NEW.doctype,NEW.locked,NEW.allocated,NEW.archiveid,NEW.icon,NEW.lmodify,NEW.profid,NEW.views,NEW.usefor,NEW.revdate,NEW.version,NEW.cdate,NEW.adate,NEW.classname,NEW.state,NEW.wid,NEW.attrids,NEW.postitid,NEW.lockdomainid,NEW.domainid,NEW.cvid,NEW.name,NEW.dprofid,NEW.prelid,NEW.atags,NEW.confidential,NEW.ldapdn,NEW.values);
     end if;
  end if;
--RAISE NOTICE ''coucou %'',replace(NEW.values,''£'','' '');
end if;

	
return NEW;
end;
' language 'plpgsql';




create or replace function droptrigger(name, name)
returns bool as $$
declare 
  schemaName alias for $1;
  tableName alias for $2;
  r record;
begin

   FOR r IN EXECUTE 'select distinct on (trigger_name) trigger_name, event_object_schema, event_object_table from information_schema.triggers where event_object_schema= ''' || (schemaName) || ''' and event_object_table=''' || (tableName) || '''' LOOP
      --RAISE NOTICE 'droping %',r.trigger_name;
         EXECUTE 'DROP TRIGGER if exists '  || quote_ident(r.trigger_name) || ' on  ' || quote_ident(r.event_object_schema) || '.' || quote_ident(r.event_object_table);
   end loop;


   return true;
end;
$$ language 'plpgsql' ;




create or replace function disabledtrigger(name) 
returns bool as $$
declare 
  tname alias for $1;
begin
   EXECUTE 'UPDATE pg_catalog.pg_class SET reltriggers = 0 WHERE oid = ''' || quote_ident(tname) || '''::pg_catalog.regclass';
   return true;
end;
$$ language 'plpgsql' ;




create or replace function enabledtrigger(name) 
returns bool as $$
declare 
  tname alias for $1;
begin
   EXECUTE 'UPDATE pg_catalog.pg_class SET reltriggers = (SELECT pg_catalog.count(*) FROM pg_catalog.pg_trigger where pg_class.oid = tgrelid) WHERE oid =  ''' || quote_ident(tname) || '''::pg_catalog.regclass;';
   return true;
end;
$$ language 'plpgsql' ;


-- BEFORE INSERT on fld
create or replace function fromfld() 
returns trigger as $$
declare 
  rs record;
  a_fromid int;
  a_doctype char;
begin
  select into a_fromid, a_doctype fromid,doctype from docread where initid=NEW.childid and locked != -1 limit 1;
    NEW.fromid=a_fromid;
    NEW.doctype=a_doctype;

  return NEW;
end;
$$ language 'plpgsql';



create or replace function vaultreindex(int, text) 
returns bool as $$
declare 
  a_docid alias for $1;
  sfile alias for $2;
  rvalue bool;
  wt text;
  wti int;
  i int;
  elementsCount int;
  elements text[];
  matches text[];
begin
  -- RAISE NOTICE 'vaultreindex(%, %)', a_docid, sfile;
  -- Expand multiples file ('<BR>' separator), then
  -- split the file list ('\n' separator)
  elements := regexp_split_to_array(replace(sfile, '<BR>', E'\n'), E'\\s*\n\\s*');
  elementsCount := array_upper(elements, 1);
  i := 1;
  LOOP
    IF i > elementsCount THEN
       EXIT; -- exit loop
    END IF;
    wt := elements[i];
    -- RAISE NOTICE 'vaultreindex processing (wt=%)', wt;
    matches := regexp_matches(wt, E'^[^|]*\\|([0-9]+)');
    IF matches IS NULL OR array_upper(matches, 1) < 1 THEN
      i := i + 1;
      CONTINUE;
    END IF;
    wt := matches[1];
    wti := wt::int;
    -- RAISE NOTICE 'vaultreindex inserting (docid=%, vaultid=%)', a_docid, wti;
    BEGIN
      INSERT INTO docvaultindex(docid, vaultid) VALUES (a_docid, wti);
      EXCEPTION
      WHEN OTHERS THEN
        RAISE NOTICE 'Error docvaultindex(docid=%, vaultid=%)', a_docid, wti;
    END;
    i := i+1;
  END LOOP;

  return rvalue;
end;
$$ language 'plpgsql';

create or replace function vaultreindexparam(int, text, text)
returns bool as $$
declare
  docid alias for $1;
  paramValue alias for $2;
  paramName alias for $3;
  rvalue bool;
  matches text[];
begin
  -- RAISE NOTICE 'vaultreindexparam(%, %, %)', docid, paramValue, paramName;
  matches = regexp_matches(paramValue, E'\\[' || paramName || E'\\|([^\\]]*)\\]');
  IF matches IS NULL OR array_upper(matches, 1) < 1 THEN
    return rvalue;
  END IF;
  PERFORM vaultreindex(docid, matches[1]);
  return rvalue;
end;
$$ language 'plpgsql';

create or replace function docrelreindex(int, text,text) 
returns bool as $$
declare 
  a_docid alias for $1;
  sfile alias for $2;
  rvalue bool;
  wt text;
  wti int;
  i int;
  elementsCount int;
  elements text[];
begin
  -- Expand multiples docid ('<BR>' separator), then
  -- split the docid list ('\n' separator)
  elements := regexp_split_to_array(replace(sfile, '<BR>', E'\n'), E'\\s*\n\\s*');
  elementsCount := array_upper(elements, 1);
  i := 1;
  LOOP
    IF i > elementsCount THEN
       EXIT; -- exit loop
    END IF;
    wt := elements[i];
    IF wt !~ E'^\\s*[0-9]+\\s*$' THEN
      -- Skip non-numeric id
	  i := i+1;
	  CONTINUE;
    END IF;
    wti = wt::int;
    -- RAISE NOTICE 'inserting (sinitid=%, cinitid=%, type=%)', a_docid, wti, $3;
    begin
    insert into docrel(sinitid,cinitid,type) values (a_docid,wti,$3);
     EXCEPTION
	 WHEN OTHERS THEN
	    RAISE NOTICE 'Error relindex (sinitid=%, cinitid=%, type=%)', a_docid, wti, $3;
     end;
     i := i+1;
  END LOOP;

  return rvalue;
end;
$$ language 'plpgsql';

