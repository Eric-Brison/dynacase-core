
--
-- This script uses functions from `core_database_utils.sql'
--


create schema if not exists search;
create schema if not exists filecontent;
drop function if exists fulltext();
drop function if exists updatevector(int);

CREATE EXTENSION IF NOT EXISTS pg_trgm;
CREATE EXTENSION IF NOT EXISTS unaccent;

SELECT pg_temp.createNewTextSearchConfigurationCopyIfNotExists('search', 'french', 'pg_catalog', 'french');
ALTER TEXT SEARCH CONFIGURATION search.french
  ALTER MAPPING FOR hword, hword_part, word
  WITH unaccent, french_stem;
