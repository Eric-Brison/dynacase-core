begin;
delete from dav.sessions where to_timestamp(expires) < now();
delete from dav.locks where to_timestamp(expires) < now();
end;