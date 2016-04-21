CREATE OR REPLACE FUNCTION upval[docid]() RETURNS trigger AS $$
declare	
begin

[BLOCK ATTRFIELD]
if not NEW.[attrid] isnull then
  NEW.values := NEW.values || '£' || NEW.[attrid];
  NEW.attrids := NEW.attrids || '£' || '[attrid]';
end if;
[ENDBLOCK ATTRFIELD]

[IF hasattr]
NEW.values := NEW.values || '£';
NEW.attrids := NEW.attrids || '£';
[ENDIF hasattr]

return NEW;
end;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION searchvalues[docid]() RETURNS trigger AS $$
		declare
		  pos int;
  		good bool;
  		reallyUpdated bool;
		begin

		if NEW.doctype != 'T' then
				reallyUpdated := (TG_OP = 'INSERT') OR (NEW.values != OLD.values) OR (COALESCE(NEW.svalues,'') != COALESCE(OLD.svalues,'')) OR (NEW.fulltext is null);

			RAISE NOTICE 'reallyUpdated %',reallyUpdated;

			if reallyUpdated then
					-- Plain text Part
				NEW.svalues:=COALESCE(NEW.svalues, '');
				if (TG_OP = 'UPDATE' AND NEW.svalues = OLD.svalues) then
					-- reset to display value part
					pos := position('ΞΞ' in NEW.svalues);
					NEW.svalues:=substring(OLD.svalues from 0 for pos);
				  NEW.svalues:=COALESCE(NEW.svalues, '');
				end if;
				if (TG_OP = 'INSERT' OR (NEW.values != OLD.values) OR (NEW.svalues != COALESCE(OLD.svalues,''))) then

						-- Fulltext Part

						begin
								[BLOCK FILEATTR]
								if NEW.[vecid] is null or (NEW.[vecid]='' and NEW.[attrid]!='') then
									NEW.[vecid] := setweight2(NEW.[attrid]);
								end if; [ENDBLOCK FILEATTR]

								NEW.fulltext:=setweight2(NEW.title, 'A') || setweight2(NEW.svalues, 'C') ||

								[BLOCK ABSATTR]
									setweight2(NEW.[attrid]::text, 'B') ||[ENDBLOCK ABSATTR]
								[BLOCK FILEATTR2]
									NEW.[vecid] ||[ENDBLOCK FILEATTR2]
								[BLOCK FULLTEXT_C]
									setweight2(NEW.[attrid]::text, 'C') ||[ENDBLOCK FULLTEXT_C]
									setweight2('', 'C');

									EXCEPTION
										WHEN OTHERS THEN
										RAISE NOTICE 'fulltext not set %',NEW.id;
						end;


						-- Plain text Part

			RAISE NOTICE 'savlues %',NEW.svalues;
						NEW.svalues := NEW.svalues || ' ΞΞ ' ||
						[BLOCK SEARCHFIELD] COALESCE(NEW.[attrid] || '£', '') ||
						[ENDBLOCK SEARCHFIELD]
						'£';

				end if;
			end if;
		end if;
		return NEW;
		end;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION fullvectorize[docid]() RETURNS trigger AS $$
declare 
  good bool;
begin
if NEW.doctype != 'T' then
  good := true;
  if (TG_OP = 'UPDATE') then 
    if (NEW.fulltext is not null) then
      good:=(NEW.values != OLD.values);
    end if;
  end if;

  if (good) then
  begin
[BLOCK FILEATTR]
    if NEW.[vecid] is null or (NEW.[vecid]='' and NEW.[attrid]!='') then
      NEW.[vecid] := setweight2(NEW.[attrid]);
    end if;
[ENDBLOCK FILEATTR]

  NEW.fulltext := setweight2(NEW.title, 'A') ||
[BLOCK ABSATTR]
  setweight2(NEW.[attrid]::text, 'B') ||[ENDBLOCK ABSATTR]
[BLOCK FILEATTR2]
  NEW.[vecid] ||[ENDBLOCK FILEATTR2]
[BLOCK FULLTEXT_C]
  setweight2(NEW.[attrid]::text, 'C') ||[ENDBLOCK FULLTEXT_C]
  setweight2('', 'C');

  EXCEPTION
    WHEN OTHERS THEN
	  RAISE NOTICE 'fulltext not set %',NEW.id;
    end;
  end if;
end if;

return NEW;
end;
$$ LANGUAGE 'plpgsql'; 
