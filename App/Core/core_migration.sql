
--
-- This script uses functions from `core_database_utils.sql'
--

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
delete from paramv P1 where type='A' and name in (select name from paramv P2 where P2.val=P1.val and type='G');
update paramv set type='G' from paramdef where paramv.type='A' and paramdef.name=paramv.name and paramdef.isglob='Y';

--
-- Update `acl' table data types
--
SELECT pg_temp.changeColumnType('', 'acl', 'name', 'text', '');
SELECT pg_temp.changeColumnType('', 'acl', 'description', 'text', '');
SELECT pg_temp.changeColumnType('', 'acl', 'group_default', 'character', '');

--
-- Update `action' data types
--
SELECT pg_temp.changeColumnType('', 'action', 'name', 'text', '');
SELECT pg_temp.changeColumnType('', 'action', 'available', 'character', '');
SELECT pg_temp.changeColumnType('', 'action', 'acl', 'text', '');
SELECT pg_temp.changeColumnType('', 'action', 'icon', 'text', '');

--
-- Update `application' data types
--
SELECT pg_temp.changeColumnType('', 'application', 'name', 'text', '');
SELECT pg_temp.changeColumnType('', 'application', 'access_free', 'character', '');
SELECT pg_temp.changeColumnType('', 'application', 'childof', 'text', '');

--
-- Update `users' data types
--
select pg_temp.dropIndexIfExists('', 'users', 'uni_users');
select pg_temp.dropIndexIfExists('', 'users', 'users_idx3');
SELECT pg_temp.dropColumnIfExists('', 'users', 'iddomain');
SELECT pg_temp.dropColumnIfExists('', 'users', 'ntpasswordhash');
SELECT pg_temp.dropColumnIfExists('', 'users', 'lmpasswordhash');
select pg_temp.addIndexIfNotExists('', 'users', 'users_login', true, '(login)');