-- Test d'une fonction qui construit un numéro de compte à partir d'un numéro de client

-- Fonction générant le numéro complet de compte de base d'un client à partir du numéro de client
-- On suppose ici que l'agence est 1

-- Nouvelles fonctions 9/01/04 

CREATE OR REPLACE FUNCTION NumAgc() RETURNS integer AS ' 
  SELECT id_ag from ad_agc;
' LANGUAGE SQL;

CREATE OR REPLACE FUNCTION MaxIdCpte(bigint) RETURNS integer AS '
 SELECT max(num_cpte)+1 FROM ad_cpt WHERE id_titulaire = $1;
' LANGUAGE SQL;

CREATE OR REPLACE FUNCTION  makeNumCompletCpteBase (bigint) RETURNS text AS '
  SELECT lpad(NumAgc(),3,''0'')||''-''||lpad($1, 6, ''0'')||''-00-''||to_char((to_number(lpad(NumAgc(),3,''0'')||lpad($1, 6, ''0'')||''00'',''99999999999'')%97), ''FM00'');
' LANGUAGE SQL;

-- Fonction générant le numéro complet de compte de PS d'un client à partir du numéro de client
-- On suppose ici que l'agence est 1
-- Fonction modifiée le 9/01/04 
CREATE OR REPLACE FUNCTION makeNumCpletCpte (bigint) RETURNS text AS '
  SELECT lpad(NumAgc(),3,''0'')||''-''||lpad($1, 6, ''0'')||''-''||lpad(MaxIdCpte($1),2,''0'')||''-''||to_char((to_number(lpad(NumAgc(),3,''0'')||lpad($1, 6, ''0'')||lpad(MaxIdCpte($1),2,''0''),''99999999999'')%97), ''FM00'');
' LANGUAGE SQL;

-- Fonction générant une référence d'écriture
-- Version PL/pgSQL de la fonction makeNumEcriture de historique.php
-- Fonction qui calcule la C/V de montant devise1 en devise2

CREATE OR REPLACE FUNCTION makeNumEcriture (integer, integer) RETURNS text AS '
DECLARE
	id_journal ALIAS FOR $1;
	id_exo ALIAS FOR $2;
	num_ecr INTEGER;
	code_journal char(3);
	ref_final TEXT;
BEGIN
	SELECT INTO num_ecr last_ref_ecriture FROM ad_journaux WHERE id_jou = id_journal FOR UPDATE;
	num_ecr := num_ecr + 1;
	RAISE NOTICE ''Le num ecr est %'', num_ecr;
	SELECT INTO code_journal code_jou FROM ad_journaux WHERE id_jou = id_journal;

	UPDATE ad_journaux SET last_ref_ecriture = num_ecr WHERE id_jou = id_journal;
	ref_final = code_journal || ''-'' || lpad(num_ecr, 8, ''0'') || ''-'' || lpad(id_exo, 2, ''0'');
	RETURN ref_final; 
END;
' LANGUAGE plpgsql;

-- Cette fonction crée une nouvelle entrée dans les tables de traduction. Elle prend en paramètre le string exprimé dans la langue système par défaut. Elle renvoie l'id_str crée.
CREATE OR REPLACE FUNCTION makeTraductionLangSyst (text) RETURNS integer AS '
    DECLARE
	agence RECORD;
	str    RECORD;
    BEGIN
	INSERT INTO ad_str DEFAULT VALUES;
	SELECT INTO str max(id_str) FROM ad_str;
	SELECT INTO agence langue_systeme_dft from ad_agc;
	INSERT INTO ad_traductions(id_str,langue,traduction) VALUES (str.max,agence.langue_systeme_dft,$1);

	RETURN str.max;
    END;
' LANGUAGE plpgsql;

-- Cette fonction traduit un string dans une langue donnée. Elle prend comme paramètres (id_str, id_lang) et renvoie la traduction du string id_str dans la langue id_lang (si elle existe). Si la traduction recherchée n'existe pas, la fonction renvoie la traduction dans la langue syst par défaut

CREATE OR REPLACE FUNCTION traduction(integer, text) RETURNS text AS '
	DECLARE
	   rech_id_str ALIAS FOR $1;
	   rech_lang ALIAS FOR $2;
	   agence RECORD;
	   tupletraduction RECORD;
	   verification RECORD;
	BEGIN
	   -- Vérif de la validité des paramètres passés à la fonction
	   SELECT INTO verification count(*) FROM ad_str WHERE id_str=rech_id_str;
	   IF verification.count <> 1 THEN
	      RAISE EXCEPTION ''traduire: Valeur du paramètre 1 incorrecte (id str % inenxistant)'', rech_id_str;
	   END IF;
           SELECT INTO verification count(*) FROM adsys_langues_systeme WHERE code=rech_lang;
           IF verification.count <> 1 THEN
              RAISE EXCEPTION ''traduire: Valeur du paramètre 2 incorrecte (code lang % inenxistant)'', rech_lang;
           END IF;
		
	   -- Traitement proprement dit
	   SELECT INTO tupletraduction * FROM ad_traductions WHERE id_str=rech_id_str AND langue=rech_lang;
	   IF NOT FOUND THEN
	      -- String pas encore traduit dans la langue recherchée, on affiche dans la lang syst par dft
              SELECT INTO agence langue_systeme_dft FROM ad_agc;
	      SELECT INTO tupletraduction * FROM ad_traductions WHERE id_str=rech_id_str AND langue=agence.langue_systeme_dft;	
	      IF NOT FOUND THEN
	         -- Erreur, tout string doit être traduit au moins dans la lang syst par dft
	         RAISE EXCEPTION ''traduire: id str % non traduit dans la langue systeme par défaut (%)'', rech_id_str, agence.langue_systeme_dft;
	      END IF;
	   END IF;
	   
	   RETURN tupletraduction.traduction;
	END;
' LANGUAGE plpgsql;

-- Récupère le numéro du journal associé si le compte donné en paramètre est un compte principal
CREATE OR REPLACE FUNCTION recupeJournal(text) RETURNS integer AS ' 
	DECLARE
	 num_cpte ALIAS FOR $1;
         cpte_comptable RECORD;
         journal  RECORD;
	 num_jou INTEGER;

	BEGIN	

          -- Informations sur le compte comptable
          SELECT INTO cpte_comptable cpte_princ_jou,cpte_centralise FROM ad_cpt_comptable WHERE num_cpte_comptable = num_cpte;

          -- Si le compte est un principal d''un journal
          IF cpte_comptable.cpte_princ_jou = ''t'' THEN

            -- le compte est-il directement associé au journal
            SELECT INTO journal id_jou FROM ad_journaux WHERE num_cpte_princ = num_cpte;
            IF journal.id_jou > 0 THEN
               RETURN journal.id_jou;
            ELSE  -- le compte est principal mais n''est pas directement lié au journal
               SELECT INTO num_jou recupeJournal(cpte_comptable.cpte_centralise); -- Appel récursif sur son compte centralisateur
               RETURN num_jou;
            END IF;
          ELSE -- Le compte n''est pas principal
              RETURN NULL; 
          END IF;
	END;
' LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION traduction_pot(text,text,text) RETURNS text 
	AS 'fonctions.so' LANGUAGE 'c' IMMUTABLE STRICT;
