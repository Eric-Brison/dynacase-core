

create or replace function getprivilege(int, int, int, bool) 
returns int[] as '
declare 
  arg_user alias for $1;
  arg_obj alias for $2;
  arg_class alias for $3;
  grouponly alias for $4;
  useracls  operm.ids_acl%TYPE;
  groupacls  operm.ids_acl%TYPE;
  group RECORD;
  dim_useracls int;
  i int;
  acls int[];
begin
acls := ''{}'';
for group in select idgroup from groups where iduser=arg_user loop
   groupacls := getprivilege(group.idgroup, arg_obj, arg_class,false);

   dim_useracls := array_count(groupacls);

   if (dim_useracls > 0) then
     for i in 1..dim_useracls loop
        if (groupacls[i] < 0) then
           acls := array_del(acls,-groupacls[i]);
        else
           acls := array_add(acls,groupacls[i]);
        end if;
     end loop;
   end if;
end loop;

if (grouponly) then
  return acls;
end if;

select into useracls ids_acl from operm where (id_user=arg_user) and (id_obj=arg_obj) and (id_class=arg_class);

dim_useracls := array_count(useracls);

if (dim_useracls > 0) then
  for i in 1..dim_useracls loop
    if (useracls[i] < 0) then
         acls := array_del(acls,-useracls[i]);
    else
      acls := array_add(acls,useracls[i]);
    end if;
  end loop;
end if;
return acls;
end;
' language 'plpgsql';



create or replace function hasprivilege(int, int, int, int) 
returns bool as '
declare 
  arg_user alias for $1;
  arg_obj alias for $2;
  arg_class alias for $3;
  arg_acl alias for $4;
  control int;
begin
   if (arg_user = 1) then 
     return true; -- it is admin user
   end if;
   select into control count(*) from octrl where  (id_obj=arg_obj) and (id_class=arg_class);
   if (control = 0) then 
	return true; -- no controlled object
   end if;
   return hasprivilege_(arg_user, arg_obj, arg_class ,arg_acl ) ;
end;
' language 'plpgsql';


create or replace function hasprivilege_(int, int, int, int) 
returns bool as '
declare 
  arg_user alias for $1;
  arg_obj alias for $2;
  arg_class alias for $3;
  arg_acl alias for $4;
  useracls  operm.ids_acl%TYPE;
  groupacls  operm.ids_acl%TYPE;
  group RECORD;
  dim_useracls int;
  i int;
begin


select into useracls ids_acl from operm where (id_user=arg_user) and (id_obj=arg_obj) and (id_class=arg_class);

dim_useracls := array_count(useracls);

if (dim_useracls > 0) then
  for i in 1..dim_useracls loop
    if (useracls[i] < 0) then
      if (-useracls[i] = arg_acl) then
       return false;
      end if;
    else
      if (useracls[i] = arg_acl) then
	return true;
      end if;
    end if;
  end loop;
end if;

-- try now in group permission

for group in select idgroup from groups where iduser=arg_user loop
   if ( hasprivilege_(group.idgroup, arg_obj, arg_class, arg_acl)) then
      return true;
   end if;

end loop;


return false; -- by default


end;
' language 'plpgsql';



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
