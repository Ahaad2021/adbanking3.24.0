-- Script de mise à jour de la base de données de la version 3.12.1 à la version 3.12.2

------------- Ticket #413 creation index_ ad_mouvement ---------------------------------------

CREATE OR REPLACE FUNCTION create_index(table_name text, index_name text, column_name text) RETURNS void AS $$ 
declare 
   l_count integer;
begin
  select count(*)
     into l_count
  from pg_indexes
  where schemaname = 'public'
    and tablename = lower(table_name)
    and indexname = lower(index_name);

  if l_count = 0 then 
     execute 'create index ' || index_name || ' on ' || table_name || ' USING btree (' || column_name || ')';
  end if;
end;
$$ LANGUAGE plpgsql;

-- Execution Create index
SELECT create_index('ad_mouvement' ,'idx_cpte_interne_cli' ,'cpte_interne_cli');
DROP FUNCTION create_index(text,text,text);


------------- Ticket #511 ---------------------------------------
CREATE OR REPLACE FUNCTION reset_sequence(tablename text, seq_name text, columnname text)
  RETURNS bigint AS
$BODY$

DECLARE 
	seqname CHARACTER VARYING;
	c INTEGER;
BEGIN
	IF seq_name IS NULL THEN
		SELECT tablename || '_' || columnname || '_seq' INTO seqname;
	ELSE
		seqname := seq_name;
	END IF;	
	    	
	EXECUTE 'SELECT max("' || columnname || '") FROM "' || tablename || '"' INTO c;
	
	IF c IS NULL THEN c = 0; 
	END IF;

	--because of substitution of setval with "alter sequence" :	
	c = c+1;
	   
	EXECUTE 'alter sequence ' || seqname ||' restart with ' || cast(c as character varying);

	RETURN c;    
  END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION reset_sequence(text, text, text)
  OWNER TO adbanking;
