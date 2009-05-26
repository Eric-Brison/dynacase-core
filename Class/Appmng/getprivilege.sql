
create or replace function array_count(int[]) 
returns int as '
declare 
  
begin

return  substr(array_dims($1),4,char_length(array_dims($1))-4);
end;
' language 'plpgsql';


create or replace function array_add(int[], int) 
returns int[] as '
declare 
  narr text;  -- new array
  i int;
  dim int;
begin

dim := array_count($1);
narr:=''{'';
if (dim > 0) then
  for i in 1..dim loop
    narr := narr||$1[i]||'','';
  end loop;
end if;
narr:= narr||$2||''}'';

return  narr;
end;
' language 'plpgsql';



create or replace function array_del(int[], int) 
returns int[] as '
declare 
  iarr alias for $1;
  idel alias for $2;
  narr text;  -- new return array
  i int;
  dim int;
begin

dim := array_count(iarr);
narr := ''{'';
if (dim > 0) then
  for i in 1..dim loop
    if (iarr[i] != idel) then 
      narr := narr||iarr[i]||'','';
    end if;
  end loop;
end if;
if (char_length(narr) > 1) then 
   narr:= substr(narr,1,char_length(narr)-1)||''}'';
else
   narr:=''{}'';
end if;

return  narr;
end;
' language 'plpgsql';



-- a_G2 is a sub group of a_G1  ?
create or replace function subgroup(int,int,int) 
returns bool as '
declare 
  a_G1 alias for $1;
  a_G2 alias for $2;
  a_level alias for $3;
  xgid RECORD;
  bing bool;
begin
   if (a_G1 = a_G2) then
      return true;
   end if;

  if (a_level > 20) then
      raise exception ''level reached'';
  end if;
  for xgid in select idgroup from groups where iduser=a_G2 loop
	bing := subgroup(a_G1, xgid.idgroup, a_level + 1);
    	if bing then 
           return true;
	end if;
   end loop;
return false;
end;
' language 'plpgsql';



create or replace function nogrouploop() 
returns trigger as '
declare 
  notgood bool;
begin
   notgood:=subgroup(NEW.iduser,NEW.idgroup,0);

   if notgood then
      raise exception ''group loop'';
   end if;

return NEW;
end;
' language 'plpgsql';

-- str_replace r1 by r2 in s1
create or replace function str_replace(text, text, text) 
returns text as '
declare 
  s1 alias for $1;
  r1 alias for $2;
  r2 alias for $3;
  s2 text;
  sw text;
  p  int;
begin
  p := position(r1 in s1);
  sw := s1;

  while (p > 0) loop
    s2 := substring(sw FROM 0 FOR p);
    s2 := s2 || r2;
    p:= p+length(r1);
    s2 := s2 || substring(sw FROM p);

    -- try again
    p := position(r1 in s2);
    sw := s2;
  end loop;
  return sw;
end;
' language 'plpgsql';

-- change type of column
create or replace function alter_table_column(text, text, text) 
returns bool as '
declare 
  t alias for $1;
  col alias for $2;
  ctype alias for $3;
begin
   EXECUTE ''ALTER TABLE '' || quote_ident(t) || '' RENAME COLUMN   '' || col || '' TO zou'' || col;
   EXECUTE ''ALTER TABLE '' || quote_ident(t) || '' ADD COLUMN   '' || col || '' '' || ctype;	
   EXECUTE ''UPDATE '' || quote_ident(t) || '' set '' || col || ''='' || ''zou'' || col|| ''::'' || ctype;
   EXECUTE ''ALTER TABLE '' || quote_ident(t) || '' DROP COLUMN   zou'' || col ;		
 
   return true;
end;
' language 'plpgsql';

--
-- Compute and returns the resulting permission recursively for a given (user, app, acl)
--
CREATE OR REPLACE FUNCTION computePerm(integer, integer, integer) RETURNS integer AS $function_computePerm$
BEGIN
	RETURN computePermRD($1, $2, abs($3), -abs($3));
END;
$function_computePerm$ LANGUAGE plpgsql;

--
-- Helper function for recursive permission computation
--
--   /!\ Should not be called directly
--
CREATE OR REPLACE FUNCTION computePermRD(integer, integer, integer, integer) RETURNS integer AS $function_computePermRD$
DECLARE
	uid ALIAS FOR $1;
	appid ALIAS FOR $2;
	aclid ALIAS FOR $3;
	aclDefaultValue ALIAS FOR $4;
	curPerm integer;
	perm integer;
	g record;
	p record;
BEGIN
	curPerm = aclDefaultValue;

	-- Check if a computed permission is available
	perm := getComputedPerm(uid, appid, aclid);
	IF perm <> 0 THEN
		RETURN perm;
	END IF;

	-- Check for a hard positioned UP/UN permission
	perm := getNonComputedPerm(uid, appid, aclid);
	IF perm <> 0 THEN
		RETURN perm;
	END IF;

	-- Compute permission recursively with parent groups
	FOR g IN SELECT idgroup FROM groups WHERE iduser = uid LOOP
		perm := computePermRD(g.idgroup, appid, aclid, aclDefaultValue);
		IF curPerm < 0 THEN
			curPerm = perm;
		END IF;
	END LOOP;

	INSERT INTO permission VALUES (uid, appid, curPerm, TRUE);

	RETURN curPerm;
END;
$function_computePermRD$ LANGUAGE plpgsql;

--
-- Returns the first computed permission available for a given (user, app, acl).
-- Returns 0 if no computed permissions are available.
--
CREATE OR REPLACE FUNCTION getComputedPerm(integer, integer, integer) RETURNS integer AS $function_getComputedPerm$
DECLARE
	uid ALIAS FOR $1;
	appid ALIAS FOR $2;
	aclid ALIAS FOR $3;
	p record;
BEGIN
	FOR p IN SELECT id_acl FROM permission WHERE id_user = uid AND id_application = appid AND @id_acl = @aclid AND computed = TRUE LOOP
		RETURN p.id_acl;
	END LOOP;

	RETURN 0;
END;
$function_getComputedPerm$ LANGUAGE plpgsql;

--
-- Returns the first uncomputed permission available for a given (user, app, acl).
-- Returns 0 if no uncomputed permissions are available.
--
CREATE OR REPLACE FUNCTION getNonComputedPerm(integer, integer, integer) RETURNS integer AS $function_getNonComputedPerm$
DECLARE
	uid ALIAS FOR $1;
	appid ALIAS FOR $2;
	aclid ALIAS FOR $3;
	p record;
BEGIN
	FOR p IN SELECT id_acl FROM permission WHERE id_user = uid AND id_application = appid AND @id_acl = @aclid AND (computed = FALSE OR computed IS NULL) LOOP
		RETURN p.id_acl;
	END LOOP;

	RETURN 0;
END;
$function_getNonComputedPerm$ LANGUAGE plpgsql;
