
--
-- tableExists function
--
CREATE OR REPLACE FUNCTION pg_temp.tableExists(arg_schema text, arg_table text)
RETURNS BOOLEAN AS
$$
DECLARE
  t_schema text;
  res text;
BEGIN

  t_schema := arg_schema;
  IF t_schema = '' THEN
    SELECT current_schema() INTO t_schema;
  END IF;

  SELECT * INTO res FROM information_schema.tables WHERE
    table_schema = t_schema
    AND table_name = arg_table
  ;

  IF FOUND THEN
    RETURN TRUE;
  END IF;

  RETURN FALSE;
END;
$$ LANGUAGE plpgsql;

--
-- addColumnIfNotExists function
--
CREATE OR REPLACE FUNCTION pg_temp.addColumnIfNotExists(arg_schema text, arg_table text, arg_column text, arg_column_spec text)
RETURNS BOOLEAN AS
$$
DECLARE
  t_schema text;
  res text;
  query text;
BEGIN

  t_schema := arg_schema;
  IF t_schema = '' THEN
    SELECT current_schema() INTO t_schema;
  END IF;

  IF NOT pg_temp.tableExists(t_schema, arg_table) THEN
    RETURN FALSE;
  END IF;

  SELECT * INTO res FROM information_schema.columns WHERE
    table_schema = t_schema
    AND table_name = arg_table
    AND column_name = arg_column
  ;

  IF NOT FOUND THEN
    query := 'ALTER TABLE "' || t_schema || '"."' || arg_table || '" ADD COLUMN "' || arg_column || '" ' || arg_column_spec || ';';
    RAISE NOTICE 'Executing: %', query;
    EXECUTE query;
    RETURN TRUE;
  END IF;

  RETURN FALSE;
END;
$$ LANGUAGE plpgsql;

--
-- dropColumnIfExists function
--
CREATE OR REPLACE FUNCTION pg_temp.dropColumnIfExists(arg_schema text, arg_table text, arg_column text)
RETURNS BOOLEAN AS
$$
DECLARE
  t_schema text;
  res text;
  query text;
BEGIN

  t_schema := arg_schema;
  IF t_schema = '' THEN
    SELECT current_schema() INTO t_schema;
  END IF;

  IF NOT pg_temp.tableExists(t_schema, arg_table) THEN
    RETURN FALSE;
  END IF;

  SELECT * INTO res FROM information_schema.columns WHERE
    table_schema = t_schema
    AND table_name = arg_table
    AND column_name = arg_column
  ;

  IF FOUND THEN
    query := 'ALTER TABLE "' || t_schema || '"."' || arg_table || '" DROP COLUMN "' || arg_column || '"' || ';';
    RAISE NOTICE 'Executing: %s', query;
    EXECUTE query;
    RETURN TRUE;
  END IF;

  RETURN FALSE;
END;
$$ LANGUAGE plpgsql;

--
-- addIndexIfNotExists function
--
CREATE OR REPLACE FUNCTION pg_temp.addIndexIfNotExists(arg_schema text, arg_table text, arg_index text, arg_unique boolean, arg_index_spec text)
RETURNS BOOLEAN AS
$$
DECLARE
  t_schema text;
  t_unique text;
  res text;
  query text;
BEGIN

  t_schema := arg_schema;
  IF t_schema = '' THEN
    SELECT current_schema() INTO t_schema;
  END IF;

  IF NOT pg_temp.tableExists(t_schema, arg_table) THEN
    RETURN FALSE;
  END IF;

  t_unique := '';
  IF arg_unique THEN
    t_unique := 'UNIQUE';
  END IF;

  SELECT relname INTO res FROM pg_index, pg_class WHERE
    indrelid IN (
      SELECT oid FROM pg_class WHERE
        relnamespace IN (
          SELECT oid FROM pg_namespace WHERE nspname = t_schema
        )
    )
    AND pg_index.indexrelid = pg_class.oid
    AND pg_class.relname = arg_index
  ;

  IF NOT FOUND THEN
    query := 'CREATE ' || t_unique || ' INDEX "' || arg_index || '" ON "' || t_schema || '"."' || arg_table || '" ' || arg_index_spec || ';';
    RAISE NOTICE 'Executing: %', query;
    EXECUTE query;
    RETURN TRUE;
  END IF;

  RETURN FALSE;
END;
$$ LANGUAGE plpgsql;

--
-- dropIndexIfExists function
--
CREATE OR REPLACE FUNCTION pg_temp.dropIndexIfExists(arg_schema text, arg_table text, arg_index text)
RETURNS BOOLEAN AS
$$
DECLARE
  t_schema text;
  res text;
  query text;
BEGIN

  t_schema := arg_schema;
  IF t_schema = '' THEN
    SELECT current_schema() INTO t_schema;
  END IF;

  IF NOT pg_temp.tableExists(t_schema, arg_table) THEN
    RETURN FALSE;
  END IF;

  SELECT relname INTO res FROM pg_index, pg_class WHERE
    indrelid IN (
      SELECT oid FROM pg_class WHERE
        relnamespace IN (
          SELECT oid FROM pg_namespace WHERE nspname = t_schema
        )
        AND relname = arg_table
    )
    AND pg_index.indexrelid = pg_class.oid
    AND pg_class.relname = arg_index
  ;

  IF FOUND THEN
    query := 'DROP INDEX "' || t_schema || '"."' || arg_index || '"' || ';';
    RAISE NOTICE 'Executing: %', query;
    EXECUTE query;
    RETURN TRUE;
  END IF;

  RETURN FALSE;
END;
$$ LANGUAGE plpgsql;

--
-- Add `tag' column to `application' table
--
SELECT pg_temp.addColumnIfNotExists('', 'application', 'tag', 'text');

--
-- Add `parsable' column to `style' table
--
SELECT pg_temp.addColumnIfNotExists('', 'style', 'parsable', 'char DEFAULT ''N''');

--
-- Add `tag' column to `application' table
--
SELECT pg_temp.addColumnIfNotExists('', 'docutag', 'fixed', 'boolean default false');

--
-- Add `tag' column to `application' table
--
SELECT pg_temp.addColumnIfNotExists('', 'doc', 'lockdomainid', 'int');
SELECT pg_temp.addColumnIfNotExists('', 'docread', 'lockdomainid', 'int');
SELECT pg_temp.addColumnIfNotExists('', 'doc', 'domainid', 'text');
SELECT pg_temp.addColumnIfNotExists('', 'docread', 'domainid', 'text');
SELECT pg_temp.addColumnIfNotExists('', 'docwait', 'extradata', 'text');
--
-- Update global type for parameters
--
update paramv set type='G' from paramdef where paramv.type='A' and paramdef.name=paramv.name and paramdef.isglob='Y';


