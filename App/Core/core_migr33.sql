CREATE OR REPLACE FUNCTION pg_temp.deleteAllViews(IN _schema TEXT)
    RETURNS void
    LANGUAGE plpgsql
    AS
    $$
    DECLARE
        row     record;
    BEGIN
        FOR row IN
            SELECT
                table_schema,
                table_name
            FROM
                information_schema.views
            WHERE
                table_schema = _schema
        LOOP
            EXECUTE 'DROP VIEW ' || quote_ident(row.table_schema) || '.' || quote_ident(row.table_name);
            RAISE INFO 'Drop View: %', quote_ident(row.table_schema) || '.' || quote_ident(row.table_name);
        END LOOP;
    END;
$$;
CREATE OR REPLACE FUNCTION pg_temp.renameFamilyTable()
    RETURNS void
    LANGUAGE plpgsql
    AS
    $$
    DECLARE
        row     record;
    BEGIN
        FOR row IN
            select table_schema, table_name, lower(docfam.name) as famname from (
SELECT table_schema, table_name, substring(table_name from 4)::int as famid FROM information_schema.tables WHERE  table_schema = 'public' and table_name ~ '^doc[1-9]') as z, docfam where z.famid=docfam.id

        LOOP
            EXECUTE 'alter table ' || quote_ident(row.table_schema) || '.' || quote_ident(row.table_name) || ' set schema family';
            EXECUTE 'alter table family.' || quote_ident(row.table_name) || ' rename to ' || quote_ident(row.famname);

            RAISE INFO 'Move: %', quote_ident(row.table_schema) || '.' || quote_ident(row.table_name) || ' to family.' || quote_ident(row.famname);
        END LOOP;

        FOR row IN
            select sequence_schema, sequence_name, lower(docfam.name) as famname from (
SELECT sequence_schema, sequence_name, substring(sequence_name from 8)::int as famid FROM information_schema.sequences WHERE  sequence_schema = 'public' and sequence_name ~ '^seq_doc[1-9]') as z, docfam where z.famid=docfam.id

        LOOP


            EXECUTE 'alter sequence ' || quote_ident(row.sequence_schema) || '.' || quote_ident(row.sequence_name) || ' set schema family';
            EXECUTE 'alter table family.' || quote_ident(row.sequence_name) || ' rename to seq_' || (row.famname);

            RAISE INFO 'Move: %', quote_ident(row.sequence_schema) || '.' || quote_ident(row.sequence_name) || ' to family.seq_' || (row.famname);
        END LOOP;
    END;
$$;
CREATE OR REPLACE FUNCTION pg_temp.convertMultiple(IN _family name, IN _column name)
    RETURNS void
    LANGUAGE plpgsql
    AS
    $$
    DECLARE
        row     record;
	currentType text;
	isMultiple bool;
	attrType text;
	arrayType text;
	pgType text;
	famId int;
	myQuery text;
    BEGIN
        select udt_name into currentType from information_schema.columns 
	       where table_schema='family' and table_name = _family and column_name=_column;
	--RAISE NOTICE 'LOG TYPE %', currentType;
	
	IF currentType is null THEN
	   --RAISE NOTICE 'SKIP: UNKNOW %/%', _family, _column;
	   RETURN;
	END IF;

	select id into famId from family.families where lower(name)=_family;

	IF substring(currentType from 1 for 1) = '_' THEN
	   --RAISE NOTICE 'SKIP: ALREADY ARRAY family.%/%', _family, _column;
	   RETURN;
	END IF;
	select true into isMultiple from docattr where docid=famId and id=_column and options ~ E'\\ymultiple=yes\\y';
	IF isMultiple is null THEN	
	   -- try if in array
	    select true into isMultiple from docattr 
	    	   where id = (select frameid from docattr where docid=famId and id=_column)
		   and substring(type from 1 for 5) = 'array';
	END IF;
	IF isMultiple is null THEN
	   --RAISE NOTICE 'SKIP: NO MULTIPLE family.%/%', _family, _column;
	   RETURN;

	END IF;
	PERFORM droptrigger('family',_family);
	select substring(type from '[a-z]+') into attrType from docattr where id=_column and docid=famId;
	CASE attrType
	  WHEN 'int', 'integer' THEN
	       pgType:='int';
	  WHEN 'float', 'double', 'money' THEN
	       pgType:='float';
	  WHEN 'date' THEN
	       pgType:='date';
	  WHEN 'timestamp' THEN
	       pgType:='timestamp without time zone';
	  WHEN 'time' THEN
	       pgType:='time';
	  ELSE
	       pgType:='text';
	END CASE;

	
	myQuery:= 'alter table family.'|| _family || ' add column __' || _column || ' ' || pgType || '[]';
	--RAISE NOTICE '%',myQuery;
	EXECUTE myQuery;
	myQuery:= 'update family.'|| _family || ' set __' || _column || ' =  string_to_array(' || _column || E', E''\\n'','''')::' || pgType ||'[] where '|| _column || E' != E''\t''' ;
	--RAISE NOTICE '%',myQuery;
	EXECUTE myQuery;
	myQuery:= 'update family.'|| _family || ' set __' || _column || ' =  ''{NULL}'' where '|| _column || E' = E''\\t''' ;
	--RAISE NOTICE '%',myQuery;
	EXECUTE myQuery;
	myQuery:=  'alter table family.'|| _family || ' rename column ' || _column || ' to "~' || _column || '"';
	--RAISE NOTICE '%',myQuery;
	EXECUTE myQuery;
	myQuery:=  'alter table family.'|| _family || ' rename column __' || _column || ' to ' || _column || '';
	--RAISE NOTICE '%',myQuery;
	EXECUTE myQuery;	
    END;
$$;


CREATE OR REPLACE FUNCTION pg_temp.dropAllTriggers()
    RETURNS void
    LANGUAGE plpgsql
    AS
    $$
    DECLARE
        r     record;
    BEGIN	
   FOR r IN EXECUTE 'select distinct on (trigger_name) trigger_name, event_object_schema, event_object_table from information_schema.triggers where event_object_schema= ''family''' LOOP
        RAISE NOTICE 'dropping %',r.trigger_name;
         EXECUTE 'DROP TRIGGER if exists '  || quote_ident(r.trigger_name) || ' on  ' || quote_ident(r.event_object_schema) || '.' || quote_ident(r.event_object_table);
   end loop;

    END;
$$;

CREATE OR REPLACE FUNCTION pg_temp.convertAllMultiple()
    RETURNS void
    LANGUAGE plpgsql
    AS
    $$
    DECLARE
        row     record;
    BEGIN	
    	FOR row IN
            select a.id as attrname, lower(f.name) as familyname from docattr a, docattr b, family.families f 
	    where f.id=a.docid and b.id=a.frameid and 
	    	  (a.options ~ E'\\ymultiple=yes\\y' or substring(b.type from 1 for 5) = 'array')

        LOOP
		RAISE NOTICE '==>%','pg_temp.convertMultiple('||row.familyname||', '||row.attrname||')';
		PERFORM pg_temp.convertMultiple(row.familyname, row.attrname);
	END LOOP;

	-- another loop for doctitle pseudo-attributes
    	FOR row IN
	select a.id || '_title' as attrname, a.docid, lower(f.name) as familyname  from docattr a, docattr b, family.families f 
	    where f.id=a.docid and b.id=a.frameid and  a.options ~ E'\\ydoctitle=auto\\y' and
	    	  (a.options ~ E'\\ymultiple=yes\\y' or substring(b.type from 1 for 5) = 'array')
        LOOP
		RAISE NOTICE '==>%','pg_temp.convertMultiple('||row.familyname||', '||row.attrname||')';
		insert into docattr(id, docid, type, usefor, options) values (row.attrname, row.docid, 'text', 'T', 'multiple=yes');
		PERFORM pg_temp.convertMultiple(row.familyname, row.attrname);
		delete from docattr where usefor='T';
	END LOOP;
    END;
$$;