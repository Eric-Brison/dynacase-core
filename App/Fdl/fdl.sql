create or replace function in_textlist(text, text) 
returns bool as $$
declare 
  arg_tl alias for $1;
  arg_v alias for $2;
  rvalue bool;
  wt text;
begin
  rvalue := (arg_tl = arg_v) ;
  if (not rvalue) then	
    
     -- search in middle
    wt := E'\n'||arg_v||E'\n';
    rvalue := (position(wt in arg_tl) > 0);

     -- search in begin
     if (not rvalue) then	
       wt := arg_v||E'\n';
       rvalue := (position(wt in arg_tl) = 1);

	
        -- search in end
       if (not rvalue) then	
          wt := E'\n'||arg_v;
          rvalue := (position(wt in arg_tl) = (char_length(arg_tl)-char_length(arg_v))) and (position(wt in arg_tl) > 0);	
        end if;
     end if;
  end if;
  return rvalue;
end;
$$ language 'plpgsql';




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



create or replace function resetvalues() 
returns trigger as $$
declare 
   lname text;
   cfromid int;
begin

NEW.values:='';
NEW.svalues:='';
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

create or replace function initacl() 
returns trigger as '
declare 
begin
if (TG_OP = ''UPDATE'') then
   if (NEW.cacl != 0)  and ((NEW.upacl != OLD.upacl) OR (NEW.unacl != OLD.unacl)) then
     update docperm set cacl=0 where docid=NEW.docid;
   end if;
end if;

if (TG_OP = ''INSERT'') then
   if (NEW.cacl != 0) then 
     update docperm set cacl=0 where docid=NEW.docid;
   end if;
end if;
return NEW;
end;
' language 'plpgsql';

create or replace function to2_ascii(text) 
returns text as $$
declare 
begin
   return translate(lower($1),'éèêëàâùüçôîïÉÈÊËÀÂÙÜÇÔÎÏ.','eeeeaauucoiieeeeaauucoii ');
end;
$$ language 'plpgsql' ;

create or replace function setweight2(text,"char") 
returns tsvector as $$
declare 
  a_text alias for $1;
  a_weight alias for $2;
begin
   if (a_text is null) or (a_text = '') then
     return to_tsvector('simple','');
   else
     return setweight(to_tsvector('french',to2_ascii(a_text)), a_weight);
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

create or replace function fulltext() 
returns trigger as $$
declare 
  good bool;
begin
  good := true;
  if (TG_OP = 'UPDATE') then 
    if (NEW.fulltext is not null) then
      good:=(NEW.values != OLD.values);
    end if;
  end if;

  if (good) then
  begin
   NEW.fulltext := setweight(to_tsvector('french',to2_ascii(NEW.title)), 'A')|| to_tsvector('french',replace(to2_ascii(NEW.values),'£',' '));

     EXCEPTION
	 WHEN OTHERS THEN
	    RAISE NOTICE 'Error fulltext %',NEW.id;
   end;
   end if;
return NEW;
END;
$$ language 'plpgsql';

create or replace function fixeddoc() 
returns trigger as '
declare 
   lid int;
   lname text;
   cfromid int;
begin


if (TG_OP = ''INSERT'') then
     if (NEW.doctype = ''C'') then 
       cfromid=-1; -- for families
     else
       cfromid=NEW.fromid;
       if (NEW.revision > 0) then
         EXECUTE ''update doc'' || cfromid || '' set lmodify=''''N'''' where initid= '' || NEW.initid || '' and lmodify != ''''N'''''';
         EXECUTE ''update doc'' || cfromid || '' set lmodify=''''L'''' where  id=(select distinct on (initid) id from only doc'' || cfromid || '' where initid = '' || NEW.initid || '' and locked = -1 order by initid, revision desc)'';
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
' language 'plpgsql';

create or replace function setread() 
returns trigger as '
declare 
   lid int;
   lname text;
   cfromid int;
begin

if (TG_OP = ''DELETE'') then  
   delete from docread where id=OLD.id;   
end if;

if ((TG_OP = ''UPDATE'') OR (TG_OP = ''INSERT'')) then
  if  NEW.doctype != ''T'' then
     select into lid id from docread where id= NEW.id;
     if (lid = NEW.id) then 
	update docread set id=NEW.id,owner=NEW.owner,title=NEW.title,revision=NEW.revision,initid=NEW.initid,fromid=NEW.fromid,doctype=NEW.doctype,locked=NEW.locked,allocated=NEW.allocated,archiveid=NEW.archiveid,icon=NEW.icon,lmodify=NEW.lmodify,profid=NEW.profid,views=NEW.views,usefor=NEW.usefor,revdate=NEW.revdate,version=NEW.version,cdate=NEW.cdate,adate=NEW.adate,classname=NEW.classname,state=NEW.state,wid=NEW.wid,attrids=NEW.attrids,postitid=NEW.postitid,lockdomainid=NEW.lockdomainid,domainid=NEW.domainid,forumid=NEW.forumid,cvid=NEW.cvid,name=NEW.name,dprofid=NEW.dprofid,prelid=NEW.prelid,atags=NEW.atags,confidential=NEW.confidential,ldapdn=NEW.ldapdn,values=NEW.values,fulltext=NEW.fulltext,svalues=NEW.svalues where id=NEW.id;
     else 
	insert into docread(id,owner,title,revision,initid,fromid,doctype,locked,allocated,archiveid,icon,lmodify,profid,views,usefor,revdate,version,cdate,adate,classname,state,wid,attrids,postitid,lockdomainid,domainid,forumid,cvid,name,dprofid,prelid,atags,confidential,ldapdn,values,fulltext,svalues) values (NEW.id,NEW.owner,NEW.title,NEW.revision,NEW.initid,NEW.fromid,NEW.doctype,NEW.locked,NEW.allocated,NEW.archiveid,NEW.icon,NEW.lmodify,NEW.profid,NEW.views,NEW.usefor,NEW.revdate,NEW.version,NEW.cdate,NEW.adate,NEW.classname,NEW.state,NEW.wid,NEW.attrids,NEW.postitid,NEW.lockdomainid,NEW.domainid,NEW.forumid,NEW.cvid,NEW.name,NEW.dprofid,NEW.prelid,NEW.atags,NEW.confidential,NEW.ldapdn,NEW.values,NEW.fulltext,NEW.svalues);
     end if;
  end if;
--RAISE NOTICE ''coucou %'',replace(NEW.values,''£'','' '');
end if;

	
return NEW;
end;
' language 'plpgsql';

create or replace FUNCTION updatevector(int) RETURNS void LANGUAGE sql AS
  'update docread set fulltext=setweight(to_tsvector(title), ''A'')|| to_tsvector(values) where id=$1;';


create or replace function droptrigger(name) 
returns bool as '
declare 
  tname alias for $1;
  toid oid;
  trigname pg_trigger%ROWTYPE;
begin
   select into toid oid from pg_class where relname=tname;
   --select into trigname tgname from pg_trigger where tgrelid=toid;
   for trigname in select * from pg_trigger where tgrelid=toid  loop
--	 drop trigger quote_ident(trigname.tgname) on tname;
         EXECUTE ''DROP TRIGGER '' || quote_ident(trigname.tgname) || '' on  '' || tname;
   end loop;



   return true;
end;
' language 'plpgsql' ;




create or replace function disabledtrigger(name) 
returns bool as '
declare 
  tname alias for $1;
begin
   EXECUTE ''UPDATE pg_catalog.pg_class SET reltriggers = 0 WHERE oid = '''''' || quote_ident(tname) || ''''''::pg_catalog.regclass'';



   return true;
end;
' language 'plpgsql' ;




create or replace function enabledtrigger(name) 
returns bool as '
declare 
  tname alias for $1;
begin
   EXECUTE ''UPDATE pg_catalog.pg_class SET reltriggers = (SELECT pg_catalog.count(*) FROM pg_catalog.pg_trigger where pg_class.oid = tgrelid) WHERE oid =  '''''' || quote_ident(tname) || ''''''::pg_catalog.regclass;'';



   return true;
end;
' language 'plpgsql' ;




create or replace function getdoc(int) 
returns record as '
declare 
  docid alias for $1;
  r record;
   dfromid int;
begin
    select into dfromid fromid from docfrom where id=docid;
  if (dfromid > 0) then
 FOR r IN EXECUTE ''select * from only doc'' || dfromid || ''  where id= '' || docid LOOP 

  END LOOP;
  end if;
  return r;
end;
' language 'plpgsql' STABLE ;


create or replace function relfld() 
returns trigger as '
declare 
  rs record;
  rc record;
  cfromid int;
  sfromid int;
begin


if (TG_OP = ''INSERT'') or (TG_OP = ''UPDATE'')then

  select into sfromid fromid from docfrom where id=NEW.dirid;
  select into cfromid fromid from docfrom where id=NEW.childid;
  if (cfromid > 0) and (sfromid > 0) then
  FOR rs IN EXECUTE ''select * from only doc'' || sfromid || ''  where id= '' || NEW.dirid || ''and doctype != ''''Z'''''' LOOP   
  END LOOP;
 FOR rc IN EXECUTE ''select * from only doc'' || cfromid || ''  where id= '' || NEW.childid  || ''and doctype != ''''Z'''''' LOOP 
  BEGIN
  INSERT INTO docrel (sinitid,cinitid,stitle,ctitle,sicon,cicon,type,doctype) VALUES (rs.initid,rc.initid,rs.title,rc.title,rs.icon,rc.icon,''folder'',rc.doctype);
	EXCEPTION
	 WHEN UNIQUE_VIOLATION THEN
	    sfromid := cfromid;
	END;
  END LOOP;
  end if;
end if;
 
if (TG_OP = ''DELETE'') then
	delete from docrel where sinitid=OLD.dirid and cinitid=OLD.childid and type=''folder'';
end if;


return NEW;
end;
' language 'plpgsql';

create or replace function fromfld() 
returns trigger as '
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
' language 'plpgsql';

create or replace function reldocfld() 
returns trigger as '
declare 
  rs record;
  rc record;
  cfromid int;
  sfromid int;
  allfld int[];
  i int;
  theqtype char;
  thechildid int;
  thedirid int;
  msg text;
begin


if (TG_OP = ''INSERT'') or (TG_OP = ''UPDATE'')then
  theqtype=NEW.qtype;
  thedirid=NEW.dirid;
  thechildid=NEW.childid;
end if;
if (TG_OP = ''DELETE'') then
  theqtype=OLD.qtype;
  thedirid=OLD.dirid;
  thechildid=OLD.childid;
  
end if;

if (theqtype = ''S'') and (thedirid > 0) and (thechildid > 0) then
  select into sfromid fromid from docfrom where id=thechildid;
  if (sfromid is null)  then
	RAISE NOTICE ''document inconnu %'',thechildid;
  else 
  if (sfromid > 0)  then
--msg=''update doc'' || sfromid ||''  set fldrels=getreldocfld(initid) where initid='' || thechildid || '' and locked != -1'';
-- RAISE NOTICE ''coucou %'',msg;
  EXECUTE ''update doc'' || sfromid ||''  set fldrels=getreldocfld(initid) where initid='' || thechildid || '' and locked != -1'' ;
 
  end if;
  end if;
  end if;
return NEW;
end;
' language 'plpgsql';

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

create or replace function getreldocfld(int)
returns int[] as $$
declare 
  thechildid alias for $1;
  allfld int[];
  i int;
  rc record;
begin
  i=0;
 FOR rc IN EXECUTE 'select * from fld where childid=' || thechildid  LOOP
  BEGIN
     allfld[i]=rc.dirid;
     i=i+1;
  END;
 END LOOP; 
return allfld;
end;
$$ language 'plpgsql';
