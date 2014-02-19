CREATE OR REPLACE FUNCTION [docname]_copysearch() RETURNS trigger AS $$
declare
        searchesValues text;
        fullValues tsvector;
        rowExists bool;
        previousid int;
begin

if NEW.doctype != 'T' then
        searchesValues :=
[BLOCK SEARCHFIELD]
   '£' || coalesce(NEW.[attrid]::text,'') ||[ENDBLOCK SEARCHFIELD] '£';

    begin
        fullValues := setweight2(NEW.title, 'A')
[BLOCK ABSATTR]
     || setweight2(NEW.[attrid]::text, 'B')    [ENDBLOCK ABSATTR]
[BLOCK FULLTEXT_C]
     || setweight2(NEW.[attrid]::text, 'C')    [ENDBLOCK FULLTEXT_C];

    EXCEPTION
    WHEN OTHERS THEN
	  RAISE NOTICE 'fulltext not set %',NEW.id;
    end;
  select true into rowExists from search.[docname] where id=NEW.id;

  [IFNOT FILESEARCH]
  if (rowExists) then
        update search.[docname] set svalues=searchesValues,  fulltext=fullValues where id=NEW.id;
  else
        insert into search.[docname] (id, svalues, fulltext) values (NEW.id, searchesValues,fullValues);
  end if;
  [ENDIF FILESEARCH]
  [IF FILESEARCH]
    if (rowExists is null ) then
      insert into search.[docname] (id) values (NEW.id);
    end if;
	  select true into rowExists from filecontent.[docname] where id=NEW.id;
	  if (rowExists is null and new.revision > 0) then
	     select id into previousid  from family.[docname] where initid=NEW.initid and revision=(NEW.revision -1);
        if (previousid > 0) then
            -- duplicate filecontent
            insert into filecontent.[docname] (id [BLOCK FILEATTR],[txtattrid][ENDBLOCK FILEATTR])
             (select NEW.id[BLOCK FILEATTR],[txtattrid][ENDBLOCK FILEATTR] from filecontent.[docname] where id=previousid);
        end if;
	  end if;

	  if (rowExists) then
          update search.[docname] set svalues=searchesValues[BLOCK FILEATTR]
             ||  '£' || coalesce([txtattrid]::text,'')   [ENDBLOCK FILEATTR]
        ,  fulltext=fullValues [BLOCK FILEATTR]
             ||  setweight2([txtattrid]::text,'D') [ENDBLOCK FILEATTR]
        from filecontent.[docname] where search.[docname].id=NEW.id and filecontent.[docname].id = search.[docname].id;
    else
          update search.[docname] set svalues=searchesValues, fulltext=fullValues where search.[docname].id=NEW.id;
    end if;
    [ENDIF FILESEARCH]
end if;
return NEW;
end;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION [docname]_fullvectorize() RETURNS trigger AS $$
        declare
          atxt text;
          r record;
        begin

                [BLOCK FILEATTR]
        [IF ismultiple]
        NEW.[vecattrid]= '{}';
        FOR r IN select unnest(NEW.[txtattrid]) as atxt LOOP
            NEW.[vecattrid]= NEW.[vecattrid] || setweight2(r.atxt,'D');
        end loop;

        [ENDIF ismultiple]
        [IFNOT ismultiple]

                    NEW.[vecattrid]= setweight2(NEW.[txtattrid],'D');

        [ENDIF ismultiple]
                [ENDBLOCK FILEATTR]

        return NEW;
        end;
        $$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION [docname]_avalues() RETURNS trigger AS $$
declare
  av text;
begin

av:='{';
[BLOCK ATTRFIELD]
if not NEW.[attrid] isnull then
  av:= av || '"[attrid]":' || to_json(NEW.[attrid]::text) || ',';
end if;[ENDBLOCK ATTRFIELD]
if (char_length(av) > 1) then
  av:= substring(av for char_length(av) - 1) || '}';
else
  av:=  '{}';
end if;
--RAISE NOTICE 'avalues %',av;
NEW.avalues := av;

return NEW;
end;
$$ LANGUAGE 'plpgsql'; 


