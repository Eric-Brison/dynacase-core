-- PgSQL
--
-- Host: localhost    Database: webdav
---------------------------------------------------------
-- Server version	4.0.3-beta

--
-- Table structure for table 'locks'
--

CREATE TABLE locks (
  token varchar(255) NOT NULL default '',
  path text NOT NULL default '',
  expires int NOT NULL default '0',
  owner varchar(200) default NULL,
  recursive int default '0',
  writelock int default '0',
  exclusivelock int NOT NULL default 0,
  PRIMARY KEY  (token)
);
create index lockspath on locks (path);
create index lockspath2 on  locks(path,token);
create index lockexp on  locks(expires);


--
-- Table structure for table 'properties'
--

CREATE TABLE properties (
  path text NOT NULL default '',
  name varchar(120) NOT NULL default '',
  ns varchar(120) NOT NULL default 'DAV:',
  value text,
  PRIMARY KEY  (path,name,ns)
);

create index properties_path on  properties(path);


--
-- Table structure for table 'session'
--

CREATE TABLE sessions (
  session varchar(200) NOT NULL ,
  owner varchar(200) NOT NULL ,
  vid int NOT NULL ,
  fid int NOT NULL ,
  expires int NOT NULL default '0',
  PRIMARY KEY  (session)
) ;


