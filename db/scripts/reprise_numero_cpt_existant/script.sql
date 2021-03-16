
CREATE OR REPLACE FUNCTION script_reprise_num_cpt_existant() RETURNS INT AS
$$
DECLARE

	cur_ad_cpt CURSOR FOR SELECT * FROM ad_cpt WHERE id_ag = numagc() ORDER BY id_cpte ASC;
	ligne_ad_cpt RECORD;

	start_str character varying(250);
	middle_str character varying(250);
	last_str character varying(250);

	new_num_complet_cpte character varying(250);

	dash_pos INTEGER;

	output_result INTEGER = 1;

BEGIN
	RAISE NOTICE 'DEBUT traitement';
	RAISE NOTICE 'DEBUT reprise des comptes clients';

	--BEGIN;
	RAISE NOTICE 'BEGIN';
	
	-- Traitement ad_cpt
	OPEN cur_ad_cpt; -- Open cursor cur_ad_cpt
	FETCH cur_ad_cpt INTO ligne_ad_cpt;

	WHILE FOUND LOOP -- Loop in resultset

		IF (SELECT has_cpte_cmplt_agc FROM ad_agc WHERE id_ag = numagc()) = 't' THEN

			dash_pos := position('-' in ligne_ad_cpt.num_complet_cpte::text);
			start_str := substr(ligne_ad_cpt.num_complet_cpte::text,1,dash_pos);

			middle_str := substr(ligne_ad_cpt.num_complet_cpte::text,7,15);
			dash_pos := position('-' in middle_str);

			last_str := substr(middle_str,dash_pos,5);
			middle_str := lpad(ligne_ad_cpt.id_ag::text,2,'0')||lpad((substr(middle_str,1,dash_pos-1)::integer)::text,8,'0');

			new_num_complet_cpte := start_str||middle_str||last_str;
			
			-- Update num_complet_cpte
			UPDATE ad_cpt SET num_complet_cpte = new_num_complet_cpte WHERE id_cpte = ligne_ad_cpt.id_cpte AND id_ag = numagc();

			--RAISE NOTICE 'id_cpte = % , num_complet_cpte = % ( new = %)', ligne_ad_cpt.id_cpte, ligne_ad_cpt.num_complet_cpte, new_num_complet_cpte;
			--RAISE NOTICE 'start_str = % , middle_str = % , last_str = %', start_str, middle_str, last_str;

			output_result := 2;

		END IF;

		FETCH cur_ad_cpt INTO ligne_ad_cpt; -- GET next element
	END LOOP;

	CLOSE cur_ad_cpt; -- Close cursor cur_ad_cpt
	
	--COMMIT;
	RAISE NOTICE 'COMMIT';
	
	RAISE NOTICE 'FIN reprise des comptes clients';
	RAISE NOTICE 'FIN traitement';

	RETURN output_result;

	EXCEPTION WHEN others THEN 
		--ROLLBACK;
		RAISE NOTICE 'ROLLBACK';
		RAISE NOTICE '% %', SQLERRM, SQLSTATE;	

		RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT script_reprise_num_cpt_existant();
DROP FUNCTION script_reprise_num_cpt_existant();
