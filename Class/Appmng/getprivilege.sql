
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
