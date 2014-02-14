
--
-- This script uses functions from `core_database_utils.sql'
--


create schema if not exists search;
drop function if exists fulltext();
drop function if exists updatevector(int);