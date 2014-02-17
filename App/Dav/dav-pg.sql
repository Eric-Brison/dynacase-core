-- PgSQL
--
-- Host: localhost    Database: webdav
---------------------------------------------------------
-- Server version	4.0.3-beta

--
-- Table structure for table 'locks'
--
create schema dav;

CREATE TABLE dav.locks (
  token text NOT NULL default '',
  path text NOT NULL default '',
  expires int NOT NULL default '0',
  owner text default NULL,
  recursive int default '0',
  writelock int default '0',
  exclusivelock int NOT NULL default 0,
  PRIMARY KEY  (token)
);
create index lockspath on dav.locks (path);
create index lockspath2 on  dav.locks(path,token);
create index lockexp on  dav.locks(expires);


--
-- Table structure for table 'properties'
--

CREATE TABLE dav.properties (
  path text NOT NULL default '',
  name text NOT NULL default '',
  ns text NOT NULL default 'DAV:',
  value text,
  PRIMARY KEY  (path,name,ns)
);

create index properties_path on  dav.properties(path);


--
-- Table structure for table 'session'
--

CREATE TABLE dav.sessions (
  session text NOT NULL ,
  owner text NOT NULL ,
  vid int NOT NULL ,
  fid int NOT NULL ,
  expires int NOT NULL default '0',
  PRIMARY KEY  (session)
) ;


