-- Fonction renvoyant le niveau d'un compte comptable
CREATE OR REPLACE FUNCTION getNiveau(TEXT,INTEGER) RETURNS integer AS '
 DECLARE
   num_cpte TEXT;
   cpte TEXT;
	 trouve BOOLEAN;
	 niveau INTEGER;
	BEGIN
	num_cpte := $1;
  trouve := false;
  niveau := 1;
  WHILE (trouve = false) LOOP
   SELECT INTO cpte cpte_centralise FROM ad_cpt_comptable WHERE num_cpte_comptable = num_cpte AND id_ag=$2 ;
   IF cpte IS NOT NULL THEN
     niveau := niveau + 1;
     num_cpte := cpte;
   ELSE
      trouve := true;
   END IF;
  END LOOP;
  RETURN niveau;
  END;
' LANGUAGE plpgsql;

-- Fonction renvoyant le numéro d'une agence
CREATE OR REPLACE FUNCTION NumAgc() RETURNS integer AS '
  SELECT MIN(id_ag) from ad_agc;
' LANGUAGE SQL;

-- Fonction renvoyant l'identifiant de l'exercice courant
CREATE OR REPLACE FUNCTION exoCourant() RETURNS integer AS '
  SELECT MAX(id_exo_compta) from ad_exercices_compta;
' LANGUAGE SQL;

CREATE OR REPLACE FUNCTION MaxIdCpte(bigint) RETURNS integer AS '
  SELECT max(num_cpte)+1 FROM ad_cpt WHERE id_ag=NumAgc() AND id_titulaire = $1;
' LANGUAGE SQL;

CREATE OR REPLACE FUNCTION MontantMin(bigint) RETURNS numeric(30,6) AS '
  SELECT mnt_min FROM adsys_produit_epargne WHERE id = $1;
' LANGUAGE SQL;

-- Fonction générant le numéro complet de compte de base d'un client à partir du numéro de client
CREATE OR REPLACE FUNCTION makeNumCompletCpte (bigint, bigint, bigint) RETURNS text AS '
  BEGIN
    IF $1 = 1 THEN
      RETURN lpad(NumAgc()::text,3,''0''::text)||''-''||lpad($2::text, 6, ''0''::text)||''-''||lpad($3::text, 2, ''0''::text)||''-''||to_char((to_number(lpad(NumAgc()::text,3,''0''::text)||lpad($2::text, 6, ''0''::text)||lpad($3::text, 2, ''0''::text),''99999999999'')%97), ''FM00'');
    ELSIF $1 = 2 THEN
      RETURN lpad((SELECT code_banque FROM ad_agc WHERE id_ag=NumAgc())::text, 2, ''0''::text)||lpad((SELECT code_ville FROM ad_agc WHERE id_ag=NumAgc())::text, 2, ''0''::text)||''-''||lpad($2::text, 5, ''0''::text)||lpad($3::text, 2, ''0''::text)||''-''||to_char((to_number(lpad((SELECT code_banque FROM ad_agc WHERE id_ag=NumAgc())::text, 2, ''0''::text)||lpad((SELECT code_ville FROM ad_agc WHERE id_ag=NumAgc())::text, 2, ''0''::text)||lpad($2::text, 5, ''0''::text)||lpad($3::text, 2, ''0''::text),''99999999999'')%97), ''FM00'');
    ELSIF $1 = 3 THEN
      RETURN lpad((SELECT code_banque FROM ad_agc WHERE id_ag=NumAgc())::text, 3, ''0''::text)||''-''||lpad($2::text, 7, ''0''::text)||''-''||lpad($3::text, 2, ''0''::text);
    ELSIF $1 = 4 THEN
      RETURN lpad((SELECT code_antenne FROM ad_agc WHERE id_ag=NumAgc())::text,NumAgc(),''0''::text)||''-''||lpad($2::text, 6, ''0''::text)||''-''||lpad($3::text, 2, ''0''::text)||''-''||to_char((to_number(lpad(NumAgc()::text,3,''0''::text)||lpad($2::text, 6, ''0''::text)||lpad($3::text, 2, ''0''::text),''99999999999'')%97), ''FM00'');
    END IF;

  END;
' LANGUAGE plpgsql;

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
	SELECT INTO num_ecr last_ref_ecriture FROM ad_journaux WHERE id_ag=NumAgc() AND id_jou = id_journal FOR UPDATE;
	num_ecr := num_ecr + 1;
	SELECT INTO code_journal code_jou FROM ad_journaux WHERE id_ag=NumAgc() AND id_jou = id_journal;

	UPDATE ad_journaux SET last_ref_ecriture = num_ecr WHERE id_ag=NumAgc() and id_jou = id_journal;
	ref_final = code_journal || ''-'' || lpad(num_ecr::text, 8, ''0''::text) || ''-'' || lpad(id_exo::text, 2, ''0''::text);
	RETURN ref_final;
END;
' LANGUAGE plpgsql;


-- Cette fonction crée une nouvelle entrée dans les tables de traduction. Elle prend en paramètre le string exprimé dans la langue système par défaut. Elle renvoie l'id_str crée.
CREATE OR REPLACE FUNCTION makeTraductionLangSyst (text) RETURNS integer AS '
    DECLARE
	   agence       RECORD;
	   str          RECORD;
       verification RECORD;
    BEGIN
        SELECT INTO agence langue_systeme_dft from ad_agc WHERE id_ag=NumAgc();
        SELECT INTO verification count(*) FROM ad_traductions WHERE langue = agence.langue_systeme_dft AND traduction = $1;
        IF verification.count = 1 THEN
            -- Si chaine déjà présente, on retourne son id_str
            SELECT INTO str id_str FROM ad_traductions WHERE langue = agence.langue_systeme_dft AND traduction = $1;
            RETURN str.id_str;
        ELSIF verification.count > 1 THEN
            RAISE EXCEPTION ''makeTraductionLangSyst: Valeur du paramètre 1 incorrecte (chaine % déjà présente en multiples exemplaires)'', $1;
        END IF;
        
        -- On insère effectivement la nouvelle traduction
    	INSERT INTO ad_str DEFAULT VALUES;
	    SELECT INTO str max(id_str) FROM ad_str;
	    INSERT INTO ad_traductions(id_str, langue, traduction) VALUES (str.max, agence.langue_systeme_dft, $1);

    	RETURN str.max;
    END;
' LANGUAGE plpgsql;

-- Cette fonction ajoute une traduction pour une chaîne déjà existante dans la BD
-- Elle prend en paramètre la chaîne originale, le code de la langue pour laquelle on traduit, la traduction
CREATE OR REPLACE FUNCTION addTraduction (text, text, text) RETURNS integer AS '
    DECLARE
        original ALIAS FOR $1;
        newlang  ALIAS FOR $2;
        newtrad  ALIAS FOR $3;
        agence       RECORD;
        str          RECORD;
        verification RECORD;
    BEGIN
        SELECT INTO agence langue_systeme_dft FROM ad_agc WHERE id_ag=NumAgc();
        -- Vérification de la présence de l''original
        SELECT INTO verification count(*) FROM ad_traductions WHERE langue = agence.langue_systeme_dft AND traduction = original;
        IF verification.count > 1 THEN
            RAISE EXCEPTION ''addTraduction: Valeur du paramètre 1 incorrecte : chaine originale % présente en multiples exemplaires'', original;
        ELSIF verification.count < 1 THEN
            -- L''original n''est pas présent, on ignore donc l''insert
            RETURN 0;
        END IF;
        -- Vérification de la présence de la traduction
        SELECT INTO verification count(*) FROM ad_traductions WHERE langue = newlang AND traduction = newtrad;
        IF verification.count = 1 THEN
            -- Si chaine déjà présente, on retourne son id_str
            SELECT INTO str id_str FROM ad_traductions WHERE langue = newlang AND traduction = newtrad;
            RETURN str.id_str;
        ELSIF verification.count > 1 THEN
            RAISE EXCEPTION ''addTraduction: Valeur du paramètre 3 incorrecte (traduction % déjà présente en multiples exemplaires)'', $1;
        END IF;

        -- On recherche le id_str original
        SELECT INTO str id_str FROM ad_traductions WHERE langue = agence.langue_systeme_dft AND traduction = original;
        INSERT INTO ad_traductions(id_str, langue, traduction) VALUES (str.id_str, newlang, newtrad);

        RETURN str.id_str;
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
              SELECT INTO agence langue_systeme_dft FROM ad_agc WHERE id_ag=NumAgc();
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
          SELECT INTO cpte_comptable cpte_princ_jou,cpte_centralise FROM ad_cpt_comptable WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte;

          -- Si le compte est un principal d''un journal
          IF cpte_comptable.cpte_princ_jou = ''t'' THEN

            -- le compte est-il directement associé au journal
            SELECT INTO journal id_jou FROM ad_journaux WHERE id_ag=NumAgc() AND num_cpte_princ = num_cpte;
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


/*
Fonction qui vérifie si une date donnée (jj/mm/aaaa) coïncide avec une fin de mois
*/
CREATE OR REPLACE FUNCTION isFinMois(date) RETURNS boolean AS '
DECLARE
	date_donnee ALIAS FOR $1;

	nb_jours INTEGER;
	rang_jour INTEGER;
	tmp1_date DATE;
	tmp2_date DATE;
	tmp3_date DATE;

BEGIN

	-- RECUPERATION DU JOUR DANS date_donnee
	SELECT INTO rang_jour date_part(''day'', date_donnee);

	-- LE PREMIER DU MOIS
	SELECT INTO tmp1_date date_donnee - rang_jour + 1;

	-- LE PREMIER DU MOIS SUIVANT
	SELECT INTO tmp2_date tmp1_date + interval ''1 month'';

	-- LA FIN DU MOIS
	SELECT INTO tmp3_date tmp2_date - 1;

	-- NOMBRE DE JOUR ENTRE LA DATE ET LA FIN DE MOIS
	SELECT INTO nb_jours tmp3_date - date_donnee;
	IF nb_jours = 0 THEN
	 	RETURN true;
	ELSIF nb_jours > 0 THEN
		RETURN false;
	END IF;
END;
' LANGUAGE plpgsql;


/*
Fonction qui vérifie si une date donnée(jj/mm/aaaa) coïncide avec une fin de trimestre par rapport au début de l'exercice
*/
CREATE OR REPLACE FUNCTION isFinTrimestre(date) RETURNS boolean AS '
DECLARE
	date_donnee ALIAS FOR $1;

	fin_trim BOOLEAN;
	nb_jours INTEGER;
	debut_exo DATE;
	tmp_date DATE;

BEGIN

	fin_trim := false;

	SELECT INTO debut_exo date_deb_exo FROM ad_exercices_compta WHERE id_ag=NumAgc() AND date_deb_exo <= date_donnee
	AND date_donnee <= date_fin_exo;

	-- FIN PREMIER TRIMESTRE
	SELECT INTO tmp_date debut_exo + interval ''3 month'';
	SELECT INTO tmp_date tmp_date - 1;

	-- NOMBRE DE JOUR ENTRE LA DATE ET LA FIN DU PREMIER TRIMESTRE
	SELECT INTO nb_jours tmp_date - date_donnee;
	IF nb_jours = 0 THEN
		fin_trim := true;
	END IF;

	-- FIN DEUXIEME TRIMESTRE
	SELECT INTO tmp_date debut_exo + interval ''6 month'';
	SELECT INTO tmp_date tmp_date - 1;

	-- NOMBRE DE JOUR ENTRE LA DATE ET LA FIN DU DEUXIEME TRIMESTRE
	SELECT INTO nb_jours tmp_date - date_donnee;
	IF nb_jours = 0 THEN
		fin_trim := true;
	END IF;

	-- FIN TROISIEME TRIMESTRE
	SELECT INTO tmp_date debut_exo + interval ''9 month'';
	SELECT INTO tmp_date tmp_date - 1;

	-- NOMBRE DE JOUR ENTRE LA DATE ET LA FIN DU TROISIEME TRIMESTRE
	SELECT INTO nb_jours tmp_date - date_donnee;
	IF nb_jours = 0 THEN
		fin_trim := true;
	END IF;

	-- FIN QUATRIEME TRIMESTRE
	SELECT INTO tmp_date debut_exo + interval ''12 month'';
	SELECT INTO tmp_date tmp_date - 1;

	-- NOMBRE DE JOUR ENTRE LA DATE ET LA FIN DU QUATRIEME TRIMESTRE
	SELECT INTO nb_jours tmp_date - date_donnee;
	IF nb_jours = 0 THEN
		fin_trim := true;
	END IF;

	RETURN fin_trim;

END;
' LANGUAGE plpgsql;


/*
Fonction qui vérifie si une date donnée(jj/mm/aaaa) coïncide avec une fin de semestre par rapport au début de l'exercice
*/
CREATE OR REPLACE FUNCTION isFinSemestre(date) RETURNS boolean AS '
DECLARE
	date_donnee ALIAS FOR $1;

	fin_sem BOOLEAN;
	nb_jours INTEGER;
	debut_exo DATE;
	tmp_date DATE;
BEGIN
	fin_sem := false;

	SELECT INTO debut_exo date_deb_exo FROM ad_exercices_compta WHERE id_ag=NumAgc() and date_deb_exo <= date_donnee
	AND date_donnee <= date_fin_exo;

	-- FIN PREMIER SEMESTRE
	SELECT INTO tmp_date debut_exo + interval ''6 month'';
	SELECT INTO tmp_date tmp_date - 1;

	-- NOMBRE DE JOUR ENTRE LA DATE ET LA FIN DU PREMIER SEMESTRE
	SELECT INTO nb_jours tmp_date - date_donnee;
	IF nb_jours = 0 THEN
		fin_sem := true;
	END IF;

	-- FIN DEUXIEME SEMESTRE
	SELECT INTO tmp_date debut_exo + interval ''12 month'';
	SELECT INTO tmp_date tmp_date - 1;

	-- NOMBRE DE JOUR ENTRE LA DATE ET LA FIN DU DEUXIEME SEMESTRE
	SELECT INTO nb_jours tmp_date - date_donnee;
	IF nb_jours = 0 THEN
		fin_sem := true;
	END IF;

	RETURN fin_sem;
END;
' LANGUAGE plpgsql;

/*
Fonction qui vérifie si une date donnée(jj/mm/aaaa) coïncide avec une fin de l'année par rapport au début de l'exercice
*/
CREATE OR REPLACE FUNCTION isFinAnnee(date) RETURNS boolean AS '
DECLARE
	date_donnee ALIAS FOR $1;

	fin_ann BOOLEAN;
	nb_jours INTEGER;
	debut_exo DATE;
	tmp_date DATE;
BEGIN
	fin_ann := false;

	SELECT INTO debut_exo date_deb_exo FROM ad_exercices_compta WHERE id_ag=NumAgc() and date_deb_exo <= date_donnee
	AND date_donnee <= date_fin_exo;

	-- FIN ANNEE
	SELECT INTO tmp_date debut_exo + interval ''12 month'';
	SELECT INTO tmp_date tmp_date - 1;

	-- NOMBRE DE JOUR ENTRE LA DATE ET LA FIN ANNEE
	SELECT INTO nb_jours tmp_date - date_donnee;
	IF nb_jours = 0 THEN
		fin_ann := true;
	END IF;

	RETURN fin_ann;
END;
' LANGUAGE plpgsql;


/*
Fonction qui renvoie la fin du mois contenant la date donnée
Exemple : 31/12/2004 pour les dates 05/12/2004, 27/12/2004 ...
*/
CREATE OR REPLACE FUNCTION getFinMois(date) RETURNS date AS '
DECLARE
	date_donnee ALIAS FOR $1;

	rang_jour INTEGER;
	tmp1_date DATE;
	tmp2_date DATE;
	tmp3_date DATE;

BEGIN

	-- RECUPERATION DU JOUR DANS date_donnee
	SELECT INTO rang_jour date_part(''day'', date_donnee);

	-- LE PREMIER DU MOIS
	SELECT INTO tmp1_date date_donnee - rang_jour + 1;

	-- LE PREMIER DU MOIS SUIVANT
	SELECT INTO tmp2_date tmp1_date + interval ''1 month'';

	-- LA FIN DU MOIS
	SELECT INTO tmp3_date tmp2_date - 1;

	RETURN date(tmp3_date);
END;
' LANGUAGE plpgsql;


/*
Fonction qui renvoie la date de fin du trimestre dans lequel se trouve la date donnée
*/
CREATE OR REPLACE FUNCTION getFinTrimestre(date) RETURNS date AS '
DECLARE
	date_donnee ALIAS FOR $1;

	tmp1_date DATE;
	tmp2_date DATE;
	fin_trim DATE;
	debut_exo DATE;	-- date début de l''exercice contenant date_donnee

BEGIN

	SELECT INTO debut_exo date_deb_exo FROM ad_exercices_compta WHERE id_ag=NumAgc() and date_deb_exo <= date_donnee
	AND date_donnee <= date_fin_exo;

	-- VERIFIE SI ON EST DANS LE PREMIER TRIMESTRE DE L''EXERCICE
	SELECT INTO tmp1_date debut_exo + interval ''3 month'';
 	IF date_donnee < tmp1_date THEN
		SELECT INTO fin_trim date(tmp1_date);
	END IF;

	-- VERIFIE SI ON EST DANS LE SECOND TRIMESTRE DE L''EXERCICE
	SELECT INTO tmp1_date debut_exo + interval ''3 month'';
	SELECT INTO tmp2_date debut_exo + interval ''6 month'';

 	IF date_donnee >= tmp1_date AND date_donnee < tmp2_date THEN
		SELECT INTO fin_trim date(tmp2_date);
	END IF;

	-- VERIFIE SI ON EST DANS LE TROISIEME TRIMESTRE DE L''EXERCICE
	SELECT INTO tmp1_date debut_exo + interval ''6 month'';
	SELECT INTO tmp2_date debut_exo + interval ''9 month'';

 	IF date_donnee >= tmp1_date AND date_donnee < tmp2_date THEN
		SELECT INTO fin_trim date(tmp2_date);
	END IF;

	-- VERIFIE SI ON EST DANS LE QUATRIEME TRIMESTRE DE L''EXERCICE
	SELECT INTO tmp1_date debut_exo + interval ''9 month'';
	SELECT INTO tmp2_date debut_exo + interval ''12 month'';

 	IF date_donnee >= tmp1_date AND date_donnee < tmp2_date THEN
		SELECT INTO fin_trim date(tmp2_date);
	END IF;

	RETURN fin_trim;
END;
' LANGUAGE plpgsql;


/*
Fonction qui renvoie la date de fin du semestre dans lequel se trouve la date donnée
*/
CREATE OR REPLACE FUNCTION getFinSemestre(date) RETURNS date AS '
DECLARE
	date_donnee ALIAS FOR $1;

	tmp1_date DATE;
	tmp2_date DATE;
	fin_sem DATE;
	debut_exo DATE;	-- date début de l''exercice contenant date_donnee

BEGIN

	SELECT INTO debut_exo date_deb_exo FROM ad_exercices_compta WHERE id_ag=NumAgc() and date_deb_exo <= date_donnee
	AND date_donnee <= date_fin_exo;

	-- VERIFIE SI ON EST DANS LE PREMIER SEMESTRE DE L''EXERCICE
	SELECT INTO tmp1_date debut_exo + interval ''6 month'';
 	IF date_donnee < tmp1_date THEN
		SELECT INTO fin_sem date(tmp1_date);
	END IF;

	-- VERIFIE SI ON EST DANS LE SECOND SEMESTRE DE L''EXERCICE
	SELECT INTO tmp1_date debut_exo + interval ''6 month'';
	SELECT INTO tmp2_date debut_exo + interval ''12 month'';

 	IF date_donnee >= tmp1_date AND date_donnee < tmp2_date THEN
		SELECT INTO fin_sem date(tmp2_date);
	END IF;

	RETURN fin_sem;
END;
' LANGUAGE plpgsql;


/*
Fonction qui renvoie la fin de l'année contenant la date donnéé par rapport au début de l'exercice
*/
CREATE OR REPLACE FUNCTION getFinAnnee(date) RETURNS date AS '
DECLARE
	date_donnee ALIAS FOR $1;

	tmp1_date DATE;
	fin_an DATE;
	debut_exo DATE;	-- date début de l''exercice contenant date_donnee

BEGIN

	-- VERIFIE SI ON EST DANS UN EXERCICE
	SELECT INTO debut_exo date_deb_exo FROM ad_exercices_compta WHERE id_ag=NumAgc() and date_deb_exo <= date_donnee
	AND date_donnee <= date_fin_exo;

	SELECT INTO tmp1_date debut_exo + interval ''12 month'';

	SELECT INTO fin_an date(tmp1_date);

	RETURN fin_an;
END;
' LANGUAGE plpgsql;

/*
Fonction qui renvoie la prochaine date de capitalisation d'un CAT de mode de paiement 'Paiement date ouverture'
IN :
  - date ouverture du compte
  - date fin du compte
  - date dernière capitalisation
  - mode de paiement
  - fréquence de capitalisation
OUT :
  - la date de la prochiane capitalisation
ALGO
  Récupérer la date dont le nombre de mois qui la sépare de la date d'ouverture est un multiple de la fréquence et qui est supérieure à
  la dernière capitalisation
*/
CREATE OR REPLACE FUNCTION getProchaineCapitalisation(date, date, date, integer, integer) RETURNS date AS '
DECLARE
	date_ouv ALIAS FOR $1;
	date_fin ALIAS FOR $2;
	date_last_cap ALIAS FOR $3;
	mode_paie ALIAS FOR $4;
	freq_cap ALIAS FOR $5;

	tmp_date DATE;
	derniere_cap DATE; -- Dernière capitalisation ou date ouverture
	next_cap DATE;
	trouve BOOLEAN;
	n INTEGER;
	nb_mois INTEGER;

BEGIN
	next_cap := NULL;

	IF mode_paie = 2 THEN -- PAIEMENT FIN DE MOIS
		trouve := false;
		n := 1;

		IF date_last_cap IS NOT NULL THEN
		derniere_cap := date_last_cap;
		ELSIF date_ouv IS NOT NULL THEN
		derniere_cap := date_ouv;
		END IF;

		-- PREMIERE DATE DE REMUNERATION DU COMPTE
		IF freq_cap = 1 THEN -- FREQUENCE MENSUELLE
			SELECT INTO tmp_date date_ouv + interval ''1 month '' ;
		ELSIF freq_cap = 2 THEN -- FREQUENCE TRIMESTRIELLE
			SELECT INTO tmp_date date_ouv + interval ''3 month '' ;
		ELSIF freq_cap = 3 THEN -- FREQUENCE SEMESTRIELLE
			SELECT INTO tmp_date date_ouv + interval ''6 month '' ;
		ELSIF freq_cap = 4 THEN -- FREQUENCE ANNUELLE
			SELECT INTO tmp_date date_ouv + interval ''12 month '' ;
		END IF;

		WHILE (trouve=false) AND (tmp_date <= date_fin)  LOOP
			IF isMultipleFrequence(tmp_date, date_ouv, freq_cap) AND (tmp_date > derniere_cap) THEN
				trouve := true;
				next_cap := tmp_date;
			ELSE
				-- PROCHAINE DATE DE REMUNERATION DU COMPTE
				n := n + 1;
				IF freq_cap = 1 THEN -- FREQUENCE MENSUELLE
					nb_mois := n * 1;
				ELSIF freq_cap = 2 THEN -- FREQUENCE TRIMESTRIELLE
					nb_mois := n * 3;
				ELSIF freq_cap = 3 THEN -- FREQUENCE SEMESTRIELLE
					nb_mois := n * 6;
				ELSIF freq_cap = 4 THEN -- FREQUENCE ANNUELLE
					nb_mois := n * 12;
				END IF;

				SELECT INTO tmp_date date_ouv + (nb_mois::text || '' month'')::interval;
			END IF;
		END LOOP;
	END IF;

	RETURN next_cap;
END;
' LANGUAGE plpgsql;


/*
Fonction qui calcul le nombre de jours de la période de capitalisation
ulile pour la mise à jour des soldes de calcul intérêts de type : solde moyen mensuel, solde moyen trimestriel, etc..

IN
 1 la fréquence de capitalisation
 2 le mode de paiement
 3 la date à laquelle on met à jour le solde de calcul des intérêts
 4 la date de la dernière rémunération
 5 la date d'ouverture du compte
 6 la date de fin du compte si compte à terme

OUT
  le noombre de jours de la période de capitalisation

ALGO
	1: Si mode de paiement est 'Paiement fin de mois'
		1:1 Si la fréquence mensuelle
			1:1:1 Renvoie le nombre de jours du mois dans lequel se trouve la date de mise à jour
                1:2 Sinon Si la fréquence trimestrielle
			1:2:1 Renvoie le nombre de jours du trimestre dans lequel se trouve la date de mise à jour
		1:3 Sinon Si la fréquence semestrielle
			1:3:1 Renvoie le nombre de jours du semestre dans lequel se trouve la date de mise à jour
		1:4 Sinon Si la fréquence annuelle
			1:4:1 Renvoie le nombre de jours de l'année dans laquelle se trouve la date de mise à jour
	2: Si mode de paiement est 'Paiement date ouverture'
		2:1 Si le compte n'a jamais été rémunéré
 			2:1:1 Renvoie le nombre de jours entre la date d'ouverture et la prochaine capitalisation
		2:2 Sinon Si le compte a une fois été rémunéré
			2:2:1 Renvoie le nombre de jours entre la dernière rémunération et la prochaine rémunération
*/

CREATE OR REPLACE FUNCTION getPeriode (integer, integer, date, date, date,date) RETURNS integer AS '
DECLARE
	freq ALIAS FOR $1;	-- fréquence de capitalisation
	mode_paie ALIAS FOR $2;	-- le mode de paiement
	date_maj ALIAS FOR $3;	-- la date de mise à jour des soldes de calcul intérêt : date du batch
	date_last_cap ALIAS FOR $4;	-- dernière capitalisation du compte
	date_ouv_cpte ALIAS FOR $5;	-- date ouverture du compte
	date_fin_cpte ALIAS FOR $6;	-- date fin si compte à terme

	nb_jours INTEGER;	-- la période de capitaglisation en jour
	last_cap DATE;	-- dernière capitalisation du compte ou ouverture du compte
	next_cap DATE;	-- prochaine capitalisation du compte

BEGIN
	nb_jours := 0;

	-- DERNIERE CAPITALISATION OU OUVETURE DU COMPTE
	IF date_last_cap IS NOT NULL THEN
		last_cap := date_last_cap;
	ELSE
		last_cap := date_ouv_cpte;
	END IF;

	-- PROCHAINE CAPITALISATION
	IF mode_paie = 1 THEN	-- PAIEMENT FIN DE MOIS
		IF freq = 1  THEN 	-- FREQUENCE MENSUELLE
			SELECT INTO next_cap getFinMois(date_maj);
		ELSIF freq = 2 THEN	-- FREQUENCE TRIMESTRIELLE
			SELECT INTO next_cap getFinTrimestre(date_maj);
		ELSIF freq = 3 THEN	-- FREQUENCE SEMESTRIELLE
			SELECT INTO next_cap getFinSemestre(date_maj);
		ELSIF freq = 4 THEN	-- FREQUENCE ANNUELLE
			SELECT INTO next_cap getFinAnnee(date_maj);
		END IF;		-- FIN SI FREQ = 1
	ELSIF mode_paie = 2 THEN
		SELECT INTO next_cap getProchaineCapitalisation(date_ouv_cpte, date_fin_cpte, date_last_cap, mode_paie, freq);
	END IF;

	-- PERIODE DE CAPITALISATION EN JOURS
	SELECT INTO nb_jours next_cap - last_cap;

	RETURN nb_jours;
END;
' LANGUAGE plpgsql;


/*
Fonction qui récupère la période effective à rémunérer en jours
IN :
	1: date de capitalisation
	2: date d'ouverture du compte
	3: date dernière capitalisation
OUT :
	- la période à rémunérer en jours
ALGO :
	1: Si date dernière capitalisation est non null
		1-1: Retourner le nombre de jours entre date capitalisation et date dernière capitalisation
		1-2: Sinon // Première rémunération
			1-2-1: Retourner le nombre de jours entre date capitalisation et date ouverture du compte
*/
CREATE OR REPLACE FUNCTION getPeriodeCapitalisation(date, date,date) RETURNS integer AS '
DECLARE
	date_cap ALIAS FOR $1;
	date_ouv ALIAS FOR $2;
	date_last_cap ALIAS FOR $3;

	nb_jours INTEGER;
	tmp_date DATE;

BEGIN
	nb_jours := 0;

	IF date_last_cap IS NOT NULL THEN
		tmp_date := date_last_cap;
	ELSIF date_ouv IS NOT NULL THEN
		tmp_date := date_ouv;
	END IF;

	IF (date_last_cap IS NOT NULL) OR (date_ouv IS NOT NULL) THEN
		SELECT INTO nb_jours date_cap - tmp_date;
	END IF;

	RETURN nb_jours;
END;
' LANGUAGE plpgsql;


/*
Fonction qui vérifie si le compte a été ouvert avant la marge de tolérance fixée à n jours avant la date de rémunération.
*/
CREATE OR REPLACE FUNCTION isRemunerable(date, timestamp, integer) RETURNS boolean AS '
DECLARE
	date_rem ALIAS FOR $1;	-- date de capitalisation
	date_ouv ALIAS FOR $2;	-- date ouverture compte
	marge_tol ALIAS FOR $3; -- marge de tolérance

	is_rem BOOLEAN;
	nb_jours INTEGER;
	tmp_date DATE;
BEGIN
	is_rem := false;

	-- CONVERTIR LE TYPTE timestamp EN TYPE date
	SELECT INTO tmp_date date_ouv;

	-- NOMBRE DE JOUR ENTRE DATE REMUNERATION ET DATE OUVERTURE COMPTE

	SELECT INTO nb_jours date_rem - tmp_date;

	IF marge_tol IS NULL THEN
		IF nb_jours > 0 THEN
			is_rem := true;
		END IF;
	ELSIF nb_jours > marge_tol THEN
		is_rem := true;
	END IF;

	RETURN is_rem;
END;
' LANGUAGE plpgsql;


/*
Fonction qui vérifie si le nombre de mois entre une date et la date d'ouverture d'un compte est un multiple de la fréquence.
Elle détermine les dates de capitalisation et de mise à jour des soldes des comptes de mode de paiement 'Paiement date ouverture'
Exemple : pour un DAT annuel ouvert le 23/01/2004 de fréqeunce trimestrielle, les dates de capitalisation sont :
   - 23/04/2004
   - 23/07/2004
   - 23/10/2004
   - 23/01/2005
*/
CREATE OR REPLACE FUNCTION isMultipleFrequence(date, timestamp, integer) RETURNS boolean AS '
DECLARE
	date_donnee ALIAS FOR $1;	-- date à vérifier
	date_ouv ALIAS FOR $2;	-- date ouverture compte
	freq ALIAS FOR $3; -- la fréquence de capitalisation ou de mise à jour des soldes

	is_multiple BOOLEAN;
	nb_jours INTEGER;
	n INTEGER;
	nb_mois INTEGER;
	tmp_date timestamp;
BEGIN
	is_multiple := false;
	n := 1;

	-- PREMIERE DATE DE REMUNERATION DU COMPTE
	IF freq = 1 THEN -- FREQUENCE MENSUELLE
		SELECT INTO tmp_date date_ouv + interval ''1 month '' ;
	ELSIF freq = 2 THEN -- FREQUENCE TRIMESTRIELLE
		SELECT INTO tmp_date date_ouv + interval ''3 month '' ;
	ELSIF freq = 3 THEN -- FREQUENCE SEMESTRIELLE
		SELECT INTO tmp_date date_ouv + interval ''6 month '' ;
	ELSIF freq = 4 THEN -- FREQUENCE ANNUELLE
		SELECT INTO tmp_date date_ouv + interval ''12 month '' ;
	END IF;

	SELECT INTO nb_jours date_donnee - date(tmp_date);

	WHILE nb_jours >= 0 AND is_multiple = false LOOP

		IF nb_jours = 0 THEN
			is_multiple := true;
		ELSE
			-- PROCHAINE DATE DE REMUNERATION DU COMPTE
			n := n + 1;
			IF freq = 1 THEN -- FREQUENCE MENSUELLE
				nb_mois := n * 1;
			ELSIF freq = 2 THEN -- FREQUENCE TRIMESTRIELLE
				nb_mois := n * 3;
			ELSIF freq = 3 THEN -- FREQUENCE SEMESTRIELLE
				nb_mois := n * 6;
			ELSIF freq = 4 THEN -- FREQUENCE ANNUELLE
				nb_mois := n * 12;
			END IF;

			SELECT INTO tmp_date date_ouv + (nb_mois::text || '' month'')::interval;
			SELECT INTO nb_jours date_donnee - date(tmp_date);
		END IF;

	END LOOP;

	RETURN is_multiple;
END;
' LANGUAGE plpgsql;


/*
recupeCptesAremunerer(date,refcursor) : fonction qui récupère les comptes à rémunerer à une date donnée
IN :
	- la date du batch
OUT :
	- un curseur qui contient les comptes rémunérables à la date donnée
ALGO
	1 : Récupérer les comptes à terme ouverts ayant un taux d'intérêt et dont l'échéance est égale à la date donnée
	2 : Si date donnée coïncide avec une fin de mois par rapport au début de l'exercice
		2-1: Récupérer les comptes ouverts, ayant un taux d'intérêt, de mode de paiement 'Paiement fin de mois', de fréquence mensuelle et respectant la marge de tolérance
	3 : Si date donnée coïncide avec une fin de trimestre par rapport au début de l'exercice
		3-1: Récupérer les comptes ouverts, ayant un taux d'intérêt, de mode de paiement 'Paiement fin de mois', de fréquence trimestrielle et respectant la marge de tolérance
	4 : Si date donnée coïncide avec une fin de semestre par rapport au début de l'exercice
		4-1: Récupérer les comptes ouverts, ayant un taux d'intérêt, de mode de paiement 'Paiement fin de mois', de fréquence semestrielle et respectant la marge de tolérance
	5 : Si date donnée coïncide avec une fin d'année par rapport au début de l'exercice
		5-1: Récupérer les comptes ouverts, ayant un taux d'intérêt, de mode de paiement 'Paiement fin de mois', de fréquence annuelle et respectant la marge de tolérance
	6 : Récupérer les comptes ouverts, ayant un taux d'intérêt, de mode de paiement 'Paiement date ouverture' dont le nombre de mois entre la date d'ouverture et la date donnée est un multiple de la fréquence
	7 : Renvoyer tous les comptes récupérés
*/
CREATE OR REPLACE FUNCTION recupeCptesAremunerer(date,refcursor) RETURNS refcursor AS '
DECLARE
	date_donnee ALIAS FOR $1;
	CUR ALIAS FOR $2;
BEGIN
	OPEN CUR FOR SELECT a.* FROM ad_cpt a, adsys_produit_epargne b WHERE a.id_ag=b.id_ag and a.id_ag=NumAgc() and a.id_prod = b.id AND (
	(etat_cpte=1 AND terme_cpte > 0 AND tx_interet_cpte > 0 AND date(dat_date_fin) = date_donnee)
	OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 1 AND isFinMois(date_donnee)
	AND isRemunerable(date_donnee, date_ouvert, marge_tolerance))
	OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 2 AND isFinTrimestre(date_donnee)
	AND isRemunerable(date_donnee, date_ouvert, marge_tolerance))
	OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 3 AND isFinSemestre(date_donnee)
	AND isRemunerable(date_donnee, date_ouvert, marge_tolerance))
	OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 4 AND isFinAnnee(date_donnee)
	AND isRemunerable(date_donnee, date_ouvert, marge_tolerance))
	OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 2 AND isMultipleFrequence(date_donnee,date_ouvert,freq_calcul_int_cpte))
	);

	RETURN CUR;

	CLOSE CUR;

END;
' LANGUAGE plpgsql;

/*
miseAjourSoldeInteret : fonction appelée chaque soir pour mettre à jour les soldes utilisés dans le calcul des intérêts.
Elle doit en principe être appelée avant la rémunération.
IN :
	- date du batch
OUT :
	- NEANT
ALGO
	1:Pour les comptes de mode de calcul 'Solde journalier le plus bas' dont solde fin journée < solde calcul intérêts
		1-1:solde de calcul des intérêts devient alors solde de fin de journée
	2: Pour tous les comptes de 'Paiement fin de mois' et 'Solde mensuel le plus bas' et solde fin journée < solde calcul intérêts
		2-1: Si date_mise_ajour coïncide avec une fin de mois
		 	2-1-1:solde de calcul des intérêts devient alors solde de fin de journée
	3: Pour tout compte de 'Paiement date ouverture' et 'Solde mensuel le plus bas' dont solde fin journée < solde calcul intérêts
		3-1 Si date donnee est un mois de plus depuis la date d'ouverture du compte
			3-1-1 solde de calcul des intérêts devient alors solde de fin de journée
	4: Pour les comptes 'Paiement fin de mois' et 'Solde trimestriel le plus bas' dont solde fin journée < solde calcul intérêts
		4-1: Si date_mise_ajour coïncide avce la fin d'un trimestre
			4-1-1: solde de calcul des intérêts devient alors solde de fin de journée
	5: Pour tout compte de 'Paiement date ouverture' et 'Solde trimestriel le plus bas' dont solde fin journée <solde calcul intérêts
		5-1: Si le nombre de mois entre date_mise_ajour et date d'ouverture est un multiple de trois
			5-1-1: solde de calcul des intérêts devient alors solde de fin de journée
	6: Pour les comptes 'Paiement fin de mois' et 'Solde semestriel le plus bas' dont solde fin journée < solde calcul intérêts
		6-1: Si date_mise_ajour coïncide avec la fin d'un semestre
			6-1-1: Solde de calcul des intérêts devient alors solde de fin de journée
	7: Pour tout compte de 'Paiement date ouverture' et 'Solde semestriel le plus bas' dont solde fin journée < solde calcul intérêts
		7-1: Si le nombre de mois entre date_mise_ajour et date d'ouverture est un multiple de 6
			7-1-1: Solde de calcul des intérêts devient alors solde de fin de journée
	8: Pour tous les comptes de mode de calcul 'Sur solde courant du compte'
		8-1: Solde calcul intérêts = solde courant
	9: Pour tout compte de 'Solde moyen mensuel' ou 'Solde moyen trimestriel' ou 'Solde moyen semestriel' ou 'Solde moyen annuel'
		9-1: Solde calcul intérêts = solde calcul intérêts + ( solde fin journée / nombre de jour de la période)
	10: Pour tout compte de 'Solde pour épargne à la source'
		10-1: Solde calcul intérêts = solde calcul intérêts + ( montant de dépôt du mois / 12)

*/
CREATE OR REPLACE FUNCTION miseAjourSoldeInteret(date) RETURNS INTEGER  AS '
DECLARE
	date_donnee ALIAS FOR $1;
BEGIN
	-- SUR SOLDE JOURNALIER LE PLUS BAS
	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee
	WHERE id_ag=NumAgc() and etat_cpte = 1 AND mode_calcul_int_cpte=2 AND solde < solde_calcul_interets;

	-- SUR SOLDE MENSUEL LE PLUS BAS
	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee
	WHERE id_ag=NumAgc() and etat_cpte=1 AND mode_paiement_cpte=1 AND mode_calcul_int_cpte=4 AND solde<solde_calcul_interets AND isFinMois(date_donnee);

	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee
	WHERE id_ag=NumAgc() and etat_cpte = 1 AND mode_paiement_cpte =2 AND mode_calcul_int_cpte=4 AND solde < solde_calcul_interets
	AND isMultipleFrequence(date_donnee, date_ouvert, 1);

	-- SUR SOLDE TRIMESTRIEL LE PLUS BAS
	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee
	WHERE id_ag=NumAgc() and etat_cpte = 1 AND mode_paiement_cpte = 1 AND mode_calcul_int_cpte=5 AND solde < solde_calcul_interets AND isFinTrimestre(date_donnee);

	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee
	WHERE id_ag=NumAgc() and etat_cpte = 1 AND mode_paiement_cpte =2 AND mode_calcul_int_cpte=5 AND solde < solde_calcul_interets
	AND isMultipleFrequence(date_donnee, date_ouvert, 2);

	-- SUR SOLDE SEMESTRIEL LE PLUS BAS
	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee
	WHERE id_ag=NumAgc() and etat_cpte = 1 AND mode_paiement_cpte = 1 AND mode_calcul_int_cpte=6 AND solde < solde_calcul_interets AND isFinSemestre(date_donnee);

	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee
	WHERE id_ag=NumAgc() and etat_cpte = 1 AND mode_paiement_cpte =2 AND mode_calcul_int_cpte=6 AND solde < solde_calcul_interets
	AND isMultipleFrequence(date_donnee, date_ouvert, 3);

	-- SUR SOLDE COURANT DU COMPTE
	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee
	WHERE id_ag=NumAgc() and etat_cpte = 1 AND mode_calcul_int_cpte = 7;

	-- SOLDE MOYEN
	UPDATE ad_cpt SET solde_calcul_interets = solde_calcul_interets + solde/getPeriode(freq_calcul_int_cpte,mode_paiement_cpte,date_donnee,date(date_calcul_interets), date(date_ouvert), date(dat_date_fin)) , date_solde_calcul_interets = date_donnee
	WHERE id_ag=NumAgc() and etat_cpte = 1 AND mode_calcul_int_cpte IN (8,9,10,11) AND date(date_ouvert) != date_donnee ;
	
	-- SOLDE POUR EPARGNE A LA SOURCE
	--UPDATE ad_cpt SET solde_calcul_interets = solde_calcul_interets + calculsoldecpte(id_cpte, date(date(date_donnee)-interval ''1 month''+interval ''1 day''), date_donnee) , date_solde_calcul_interets = date_donnee
	--WHERE id_ag = NumAgc() and etat_cpte = 1 AND mode_calcul_int_cpte = 12 AND date(date_ouvert) != date_donnee AND isFinMois(date_donnee);
	UPDATE ad_cpt SET interet_a_capitaliser = interet_a_capitaliser + solde * (tx_interet_cpte) / 12, interet_annuel = interet_a_capitaliser + solde * (tx_interet_cpte) / 12, solde_calcul_interets = solde , date_solde_calcul_interets = date_donnee
	WHERE id_ag = NumAgc() and etat_cpte = 1 AND mode_calcul_int_cpte = 12 AND date(date_ouvert) != date_donnee AND isFinMois(date_donnee);
	RETURN 0;

END;
' LANGUAGE plpgsql;


/*
miseAjourSoldeInteretEpargneSource : fonction appelée chaque soir pour mettre à jour les soldes utilisés dans le calcul des intérêts pour l'épargne à la source.
Elle doit en principe être appelée après la rémunération.
IN :
	- date du batch
OUT :
	- NEANT
ALGO
	1:Pour les comptes de mode de calcul 'Solde épargne à la source'
		1-1:solde de calcul des intérêts devient alors solde courant
*/

CREATE OR REPLACE FUNCTION miseAjourSoldeInteretEpargneSource(date) RETURNS INTEGER  AS '
DECLARE
	date_donnee ALIAS FOR $1;
BEGIN

	-- SUR SOLDE EPARGNE A LA SOURCE
	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee
	WHERE id_ag=NumAgc() and etat_cpte = 1 AND mode_paiement_cpte = 3 AND mode_calcul_int_cpte = 12 AND isFinMois(date_donnee);

	RETURN 0;


END;
' LANGUAGE plpgsql;


/* Pour obtenir la prochaine date d'exécution d'un ordre permanent
   On ajoute à la date l'intervalle défini par la périodicité et par l'intervalle
*/
CREATE OR REPLACE FUNCTION ordreperm_proch_exe(date, int, int) RETURNS date AS '
DECLARE
  date_last_exe ALIAS FOR $1;
  interv ALIAS FOR $2;
  periodicite ALIAS FOR $3;
  tempdate DATE;
  cur1 REFCURSOR;
  qstr TEXT;
BEGIN
  qstr = ''SELECT date(''''''||to_char(date_last_exe,''DD/MM/YYYY'')||'''''') + interval '''''' || interv ;
  IF periodicite = 1 THEN
    qstr = qstr ||'' days'''''';
  ELSIF periodicite = 2 THEN
    qstr = qstr ||'' weeks'''''';
  ELSIF periodicite = 3 THEN
    qstr = qstr ||'' months'''''';
  ELSIF periodicite = 4 THEN
    qstr = qstr ||'' years'''''';
  END IF;
  OPEN cur1 FOR EXECUTE qstr;
  FETCH cur1 INTO tempdate;
  CLOSE cur1;
  RETURN tempdate;
END;
' LANGUAGE plpgsql;


/*
 Fonction qui fusion les données d'une agence dans la db siège
 IN : liste_id_ag : tableau contenant le ou les identifiants des agences
 OUT : NEANT
 ALGO :
    1 : créer une table temporaire pour conserver les mouvements déjà consolidés
    2 : conserver les mouvements déjà consolidés dans la table temporaire
    3 : supprimer toutes les anciennes données de l'agence dans la db siège
*/
CREATE OR REPLACE FUNCTION consolidation_db(integer) RETURNS integer AS '
DECLARE
  id_agence ALIAS FOR $1;  -- id agence à consolider
  ad_table RECORD;         -- nom table dans la db
  SQL  TEXT;               -- text permettant de construire la requête sql
  liste_tables REFCURSOR;  -- liste des tables de la db
  is_id_ag INTEGER;        -- vérifie si le champ id_ag est présente dans la table

BEGIN
	-- Sauvegarde des mouvements déjà consolidés pour cette agence
	DELETE FROM ad_mouvement_consolide WHERE id_ag = id_agence;
	INSERT INTO ad_mouvement_consolide SELECT id_ag,id_mouvement FROM ad_mouvement m WHERE m.id_ag=id_agence AND m.consolide=''t'';

	-- récupération des noms des tables de la db siège
	OPEN liste_tables FOR SELECT * from adsys_table_conso;

	-- parcours des tables
	FETCH liste_tables INTO ad_table;
	WHILE FOUND LOOP
			SQL = ''DELETE FROM ONLY ''||ad_table.nom_table||'' WHERE id_ag = ''||id_agence||'';'';
			EXECUTE SQL;
		FETCH liste_tables INTO ad_table;
	END LOOP;

	CLOSE liste_tables;

  RETURN 0;
END;
' LANGUAGE plpgsql;


-- Fonction utilitaire qui ajoute un champ dans une table s'il n'est pas encore présent
CREATE OR REPLACE FUNCTION add_column_to_table(text, text, text)
RETURNS boolean AS '
DECLARE
  table_name ALIAS FOR $1;
  column_name ALIAS FOR $2;
  column_type ALIAS FOR $3;
  column_exists boolean;
  SQL text;

BEGIN
  SELECT INTO column_exists EXISTS (SELECT attname FROM pg_attribute WHERE attname = column_name AND attrelid = (SELECT relfilenode FROM pg_class WHERE relname = table_name));
  IF NOT column_exists THEN
    SQL = ''ALTER TABLE ''||table_name||'' ADD COLUMN ''||column_name||'' ''||column_type||'';'';
    EXECUTE SQL;
    RETURN true;
  ELSE
    RETURN false;
  END IF;
END;
' LANGUAGE plpgsql;

-- Fonction utilitaire qui supprime un champ dans une table s'il est bien présent
CREATE OR REPLACE FUNCTION delete_column_from_table(text, text)
RETURNS boolean AS '
DECLARE
  table_name ALIAS FOR $1;
  column_name ALIAS FOR $2;
  column_exists boolean;
  SQL text;

BEGIN
  SELECT INTO column_exists EXISTS (SELECT attname FROM pg_attribute WHERE attname = column_name AND attrelid = (SELECT relfilenode FROM pg_class WHERE relname = table_name));
  IF column_exists THEN
    SQL = ''ALTER TABLE ''||table_name||'' DROP COLUMN ''||column_name||'';'';
    EXECUTE SQL;
    RETURN true;
  ELSE
    RETURN false;
  END IF;
END;
' LANGUAGE plpgsql;

--
-- Fonction retournant le Decouvert_Max autorisé sur un produit d''epargne
--
CREATE OR REPLACE FUNCTION DecouvertMax(bigint) RETURNS numeric(30,6) AS '
  SELECT decouvert_max FROM adsys_produit_epargne WHERE id = $1;
' LANGUAGE SQL;

----------------------------------------------------------------------------------------------------------
-- Cette fonction renvoie la date valeur  suivant la date date_compta
--(IL EXISTE UNE VERSIOn PHP de cette fonction)
-- OUT : La date demandée
-----------------------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION getDateValeur(INTEGER,TEXT,DATE) RETURNS Date AS $$
DECLARE
 id_compte ALIAS FOR $1 ;-- identifiant du compte d epargne
 sens ALIAS FOR $2;-- sens de l opération
 date_compta ALIAS FOR  $3; -- La date de comptabilisation de l opération, au format jj/mm/aaaa
 nbre_jours_report INTEGER;
BEGIN
 -- recuperation de nbre de jours de report
 IF sens='c' THEN  --nbre de jours report au credit
	 SELECT INTO nbre_jours_report nbre_jours_report_credit FROM adsys_produit_epargne p, ad_cpt c
	  WHERE c.id_ag =numAgc()  AND c.id_ag = p.id_ag AND c.id_prod = p.id AND c.id_cpte = id_compte ;

  ELSEIF sens='d' THEN -- nbre de jours report au debit

	  SELECT INTO nbre_jours_report nbre_jours_report_debit FROM adsys_produit_epargne p, ad_cpt c
	  WHERE c.id_ag =numAgc()  AND c.id_ag = p.id_ag AND c.id_prod = p.id AND c.id_cpte = id_compte ;

	  nbre_jours_report:=nbre_jours_report*(-1);
  END IF;
  return jour_ouvrable( date_compta,nbre_jours_report);
END;
$$ LANGUAGE plpgsql;


----------------------------------------------------------------------------------------------------------
-- Cette fonction renvoie vrai si le jour  de la date date_param est ferié
--(IL EXISTE UNE VERSIOn PHP de cette fonction)
-- IN  : date_param: La date de départ
--
-- OUT : BOOLEAN
-----------------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION is_ferie(DATE) RETURNS BOOLEAN AS
$$
 DECLARE
 	 date_param ALIAS FOR $1;
 	 rep char(4);
 	 jr_semaine INTEGER;
 BEGIN
  -- recupere le Jour de la semaine au format numérique
   SELECT into jr_semaine date_part('DOW',date_param);

  -- Maintenant on passe au format de la table ad_fer : 1 = lundi, 7 = dimanche
   if jr_semaine=0 then
   jr_semaine=7;
   end if;


	SELECT into rep count(*) FROM ad_fer WHERE id_ag=numAgc() AND
	((jour_semaine = jr_semaine) OR (jour_semaine = NULL) OR (jour_semaine = 0)) AND
	((date_jour = date_part('day',date(date_param))) OR (date_jour = NULL) OR (date_jour = 0)) AND
	((date_mois = date_part('month',date_param)) OR (date_mois = NULL) OR (date_mois = 0)) AND
	((date_annee =date_part('year',date_param)) OR (date_annee = NULL) OR (date_annee = 0));
	 return rep > 0 ;
 END;
$$ LANGUAGE 'plpgsql';


----------------------------------------------------------------------------------------------------------
-- Cette fonction renvoie la date du n ème jour ouvrable suivant la date date_param
--(IL EXISTE UNE VERSIOn PHP de cette fonction)
-- Si nbre_jour est négatif, on remonte dans le temps
-- IN  : date_param: La date de départ
--       nbre_jour : Le nombre de jours à avancer / reculer
-- OUT : La date demandée
-----------------------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION jour_ouvrable(DATE,INTEGER) RETURNS DATE AS
$$
 DECLARE
  date_param ALIAS FOR $1;
  nbre_jour ALIAS FOR $2;
  date_temp DATE;
 sens INTEGER;
 compteur INTEGER;
 BEGIN
	IF nbre_jour>=0 THEN
		sens=1;
	ELSE
		sens=-1;
	END IF;
	compteur=0;
	date_temp:=date_param;
	WHILE (compteur<(nbre_jour*sens) ) LOOP
		date_temp:=date_temp+sens;
    		IF ( NOT  CAST ( is_ferie(date_temp) as boolean)) THEN
    			compteur:=compteur+1;
    		END IF;


	END LOOP;


	 return date_temp;
 END;
$$ LANGUAGE plpgsql;


-- FUNCTION: calculsoldecpte(integer, date, date)

-- DROP FUNCTION calculsoldecpte(integer, date, date);

CREATE OR REPLACE FUNCTION calculsoldecpte(integer, date, date)
  RETURNS numeric AS
$BODY$
DECLARE
	param_id_cpte ALIAS FOR $1;
	date_min ALIAS FOR $2;
	date_sup ALIAS FOR $3;
	mnt_dep numeric;
	mnt_debit numeric;
	mnt_credit numeric;
	date_inf DATE;
BEGIN
	date_inf := date_min;
	IF (date_min IS NULL) THEN
	 SELECT INTO date_inf date_ouvert FROM ad_cpt c WHERE c.id_cpte =  param_id_cpte;
	END IF;
	SELECT INTO mnt_debit sum(montant) from ad_mouvement where cpte_interne_cli = param_id_cpte and sens = 'd' and date_valeur >= date_inf and date_valeur <= date_sup;
	SELECT INTO mnt_credit sum(montant) from ad_mouvement where cpte_interne_cli = param_id_cpte and sens = 'c' and date_valeur >= date_inf and date_valeur <= date_sup;
	IF mnt_credit IS NULL THEN
		mnt_credit = 0;
	END IF;
	IF mnt_debit IS NULL THEN
		mnt_debit = 0;
	END IF;	
	mnt_dep = mnt_credit - mnt_debit;
	RETURN mnt_dep;
END
$BODY$
  LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION calculsoldecpte(integer, date, date) OWNER TO adbanking;


-- Function: calculnombrejoursretardech(integer, integer, date, integer)

-- DROP FUNCTION calculnombrejoursretardech(integer, integer, date, integer);

CREATE OR REPLACE FUNCTION calculnombrejoursretardech(integer, integer, date, integer)
  RETURNS double precision AS
$BODY$
DECLARE
        iddoss ALIAS FOR $1;
	idech ALIAS FOR $2;
	date_arrete ALIAS FOR $3;
        id_agence ALIAS FOR $4;
	max_date DATE;
	dateech DATE;
	isremb boolean;
	nbr_jours_retard DOUBLE PRECISION;
BEGIN
       SELECT INTO dateech,isremb date_ech, remb FROM ad_etr WHERE id_ag = id_agence AND id_doss = iddoss AND id_ech = idech;
       nbr_jours_retard := date_part('day', date_arrete::timestamp - dateech::timestamp);
       -- Pour les échéances remboursées avant la date d'arrete: nbr_jours_retard = 0
       SELECT INTO max_date MAX(date_remb) FROM ad_sre WHERE id_ag = id_agence AND id_doss = iddoss AND id_ech = idech;
       IF ((max_date <= date_arrete) AND (isremb = 't')) THEN
		nbr_jours_retard := 0;
       END IF;
       RETURN nbr_jours_retard;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION calculnombrejoursretardech(integer, integer, date, integer) OWNER TO adbanking;


-- Function: calculnombrejoursretardoss(integer, date, integer)

-- DROP FUNCTION calculnombrejoursretardoss(integer, date, integer);

CREATE OR REPLACE FUNCTION calculnombrejoursretardoss(integer, date, integer)
  RETURNS double precision AS
$BODY$
DECLARE
        iddoss ALIAS FOR $1;
	date_arrete ALIAS FOR $2;
        id_agence ALIAS FOR $3;
	max_jours_retard DOUBLE PRECISION;
BEGIN
       SELECT INTO max_jours_retard MAX(calculnombrejoursretardech(iddoss, id_ech, date_arrete, id_agence)) FROM ad_etr WHERE id_ag = id_agence AND id_doss = iddoss;
       IF (max_jours_retard <= 0) THEN
   			RETURN 0;
 	   ELSE
       		RETURN max_jours_retard;
        END IF;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION calculnombrejoursretardoss(integer, date, integer) OWNER TO adbanking;


-- Function: calculetatcredit(integer, date, integer)

-- DROP FUNCTION calculetatcredit(integer, date, integer);


CREATE OR REPLACE FUNCTION calculetatcredit(integer, date, integer)
  RETURNS integer AS
$BODY$

DECLARE
 id_dossier ALIAS FOR $1;
 date_ref ALIAS FOR $2;
 id_agence ALIAS FOR $3;
 nbr_jours_retard DOUBLE PRECISION;
 interv_min INTEGER;
 interv_max INTEGER;
 etat INTEGER;
 etat_credit INTEGER;
 date_etat_credit DATE;
 nbr_jours_retard_max INTEGER;
 etats_credits CURSOR FOR SELECT * FROM adsys_etat_credits WHERE id_ag = id_agence ORDER BY id;
 ligne RECORD;

BEGIN
 nbr_jours_retard := calculnombrejoursretardoss(id_dossier, date_ref, id_agence);
 ---- L'état du crédit est soit en perte, soit à radier si le nombre max de jours est atteint
 SELECT INTO nbr_jours_retard_max sum(nbre_jours)  FROM adsys_etat_credits WHERE nbre_jours > 0 AND id_ag = id_agence;
   IF (nbr_jours_retard >= nbr_jours_retard_max) THEN
     SELECT INTO etat_credit, date_etat_credit cre_etat, cre_date_etat FROM ad_dcr WHERE id_doss = id_dossier;
     SELECT INTO etat id FROM adsys_etat_credits WHERE nbre_jours = -1 AND id_ag = id_agence;
     IF ((etat_credit = etat) AND (date_etat_credit <= date_ref)) THEN -- état à perte
      RETURN etat;
     ELSE -- état à radier
      SELECT INTO etat id FROM adsys_etat_credits WHERE nbre_jours = -2 AND id_ag = id_agence;
      RETURN etat;
     END IF;
 ElSEIF (nbr_jours_retard <= 0) THEN --- Crédits sains
   RETURN 1;
 ELSE --- Autres états
 OPEN etats_credits;
 FETCH etats_credits INTO ligne;
  interv_max := -1;
  WHILE FOUND LOOP
    interv_min := interv_max+1;
    interv_max = interv_min + ligne.nbre_jours - 1;
    IF (nbr_jours_retard >= interv_min AND nbr_jours_retard <= interv_max) THEN
     etat := ligne.id;
     exit;
    END IF;
  FETCH etats_credits INTO ligne;
  END LOOP;
  CLOSE etats_credits;
  RETURN etat;
 END IF;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION calculetatcredit(integer, date, integer) OWNER TO adbanking;

CREATE OR REPLACE FUNCTION CalculMntPenEch(INTEGER, INTEGER, DATE, INTEGER)
  RETURNS DOUBLE PRECISION AS
$BODY$
DECLARE
 id_dossier ALIAS FOR $1;
 id_echeance ALIAS FOR $2;
 date_ref ALIAS FOR $3;
 id_agence ALIAS FOR $4;
 ligne RECORD;
 solde_penalite DOUBLE PRECISION;
 mnt_rembourse_pen DOUBLE PRECISION;
BEGIN
 SELECT INTO solde_penalite COALESCE(solde_pen,0)  FROM ad_etr WHERE id_ech =id_echeance AND id_doss = id_dossier AND id_ag = id_agence;
 SELECT INTO mnt_rembourse_pen COALESCE(sum(mnt_remb_pen),0) FROM ad_sre WHERE id_ech =id_echeance AND id_doss = id_dossier AND id_ag = id_agence;
 
 RETURN solde_penalite + mnt_rembourse_pen;
END;
$BODY$
LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION CalculMntPenEch(INTEGER, INTEGER, DATE, INTEGER) OWNER TO adbanking;
/*
CREATE OR REPLACE FUNCTION CalculMntPenEch(INTEGER, INTEGER, DATE, INTEGER)
  RETURNS DOUBLE PRECISION AS
$BODY$
DECLARE
 id_dossier ALIAS FOR $1;
 id_echeance ALIAS FOR $2;
 date_ref ALIAS FOR $3;
 id_agence ALIAS FOR $4;
 nbr_jours_retard DOUBLE PRECISION;
 ligne RECORD;
 solde_capital DOUBLE PRECISION;
 solde_interet DOUBLE PRECISION;
 mnt_rembourse_cap DOUBLE PRECISION;
 mnt_rembourse_int DOUBLE PRECISION;
 mnt_capital_doss DOUBLE PRECISION;
 mnt_rembourse_cap_doss DOUBLE PRECISION;
 mnt_pen DOUBLE PRECISION;
 mnt_calc_pen DOUBLE PRECISION;
 nbre_jours INTEGER;
 nbre_jours_an INTEGER;
BEGIN
 nbr_jours_retard := calculnombrejoursretardech(id_dossier, id_echeance, date_ref, id_agence);
 IF (nbr_jours_retard <= 0) THEN
   RETURN 0;
 END IF;
 SELECT INTO ligne tx_interet, mode_calc_int, typ_pen_pourc_dcr, type_duree_credit, mnt_penalite_jour, prc_penalite_retard, max_jours_compt_penalite, suspension_pen, delai_grac FROM adsys_produit_credit p, ad_dcr d WHERE id_doss = id_dossier AND d.id_prod = p.id AND d.id_ag = p.id_ag AND p.id_ag = id_agence;
 nbr_jours_retard := nbr_jours_retard - COALESCE(ligne.delai_grac, 0);
 IF (ligne.suspension_pen = 't') THEN
   RETURN 0;
 END IF;
 SELECT INTO mnt_rembourse_cap, mnt_rembourse_int sum(mnt_remb_cap), sum(mnt_remb_int) FROM ad_sre WHERE id_ech =id_echeance AND id_doss = id_dossier AND date_remb <= date_ref AND id_ag = id_agence;
 SELECT INTO solde_capital, solde_interet (mnt_cap - mnt_rembourse_cap), (mnt_int - mnt_rembourse_int) FROM ad_etr WHERE id_ech =id_echeance AND id_doss = id_dossier AND id_ag = id_agence;
 IF (ligne.typ_pen_pourc_dcr = 1) THEN
   SELECT INTO mnt_rembourse_cap_doss sum(mnt_remb_cap) FROM ad_sre WHERE id_doss = id_dossier AND date_remb <= date_ref AND id_ag = id_agence;
   SELECT INTO mnt_capital_doss sum(mnt_cap) FROM ad_etr WHERE id_doss = id_dossier AND id_ag = id_agence;
   mnt_calc_pen := mnt_capital_doss - mnt_rembourse_cap_doss;
 ELSEIF (ligne.typ_pen_pourc_dcr = 2) THEN
   mnt_calc_pen := solde_capital+solde_interet;
 ELSE
   mnt_calc_pen := 0;
 END IF;
 IF (ligne.type_duree_credit = 2) THEN -- durée hebdomadaire
    nbre_jours := 7;
 --ELSEIF (ligne.mode_calc_int = 3) THEN -- intérêts dégressif
 -- nbre_jours := 360;
 ELSE
    nbre_jours := 30;
 END IF;
 mnt_pen := COALESCE((ligne.prc_penalite_retard / nbre_jours * mnt_calc_pen),0) + COALESCE(ligne.mnt_penalite_jour,0) + COALESCE((ligne.tx_interet / nbre_jours * solde_capital),0);
 nbre_jours_an := 360;
 IF (ligne.mode_calc_int = 3) THEN -- Mode intérêts degressifs
   mnt_pen := COALESCE((ligne.prc_penalite_retard / nbre_jours_an * mnt_calc_pen),0) + COALESCE(ligne.mnt_penalite_jour,0) + COALESCE((ligne.tx_interet / nbre_jours_an * solde_capital),0);
 ELSE
    mnt_pen := COALESCE((ligne.prc_penalite_retard / nbre_jours * mnt_calc_pen),0) + COALESCE(ligne.mnt_penalite_jour,0);
 END IF;
 RETURN mnt_pen*nbr_jours_retard;
END;
$BODY$
LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION CalculMntPenEch(INTEGER, INTEGER, DATE, INTEGER) OWNER TO adbanking;
*/
-- Function: calculesoldecpteinterne(integer, date, integer)

-- DROP FUNCTION calculesoldecpteinterne(integer, date, integer);

CREATE OR REPLACE FUNCTION calculesoldecpteinterne(integer, date, integer)
  RETURNS numeric AS
$BODY$
 DECLARE
        id_cpte_interne ALIAS FOR $1;   			-- Numéro du compte
	date_param  ALIAS FOR $2;		-- Date du solde
	idAgc ALIAS FOR $3;			-- id de l'agence
	solde_debit NUMERIC(30,6):=0;		--solde au débit du compte
	solde_credit NUMERIC(30,6):=0;		--solde au crédit du compte 
	
	solde_courant NUMERIC(30,6):=0;		--solde courant du compte interne( solde actuel ds ad_cpt)

	BEGIN
	 select INTO solde_courant solde from ad_cpt where id_ag =idAgc AND id_cpte = id_cpte_interne  ;
         
	SELECT INTO solde_debit sum(m.montant) FROM ad_ecriture e, ad_mouvement m, ad_his h
         WHERE e.id_ag = idAgc AND e.id_ag = m.id_ag AND e.id_ag = h.id_ag
         AND e.id_ecriture = m.id_ecriture AND e.id_his = h.id_his
         AND date(h.date) BETWEEN date(date_param) AND date(now())
         AND sens = 'd' AND cpte_interne_cli = id_cpte_interne;


 
	SELECT INTO solde_credit sum(m.montant) FROM ad_ecriture e, ad_mouvement m, ad_his h
         WHERE e.id_ag = idAgc AND e.id_ag = m.id_ag AND e.id_ag = h.id_ag
         AND e.id_ecriture = m.id_ecriture AND e.id_his = h.id_his
         AND date(h.date) BETWEEN date(date_param) AND date(now())
         AND sens = 'c' AND cpte_interne_cli = id_cpte_interne;
       RETURN COALESCE(solde_courant,0) +COALESCE(solde_debit,0) -COALESCE(solde_credit,0);
 END;
$BODY$
  LANGUAGE plpgsql VOLATILE;

-- Traductions pour de nouveaux champs
-- Fonction utilitaire qui ajoute le champ pour la traduction s'il n'est pas déjà présent
CREATE OR REPLACE FUNCTION add_traduction_to_table(text, text)
RETURNS boolean AS $$
DECLARE
  table_name ALIAS FOR $1;
  column_name ALIAS FOR $2;
  column_exists boolean;
  SQL text;

BEGIN
 --SELECT INTO column_exists EXISTS (SELECT attname FROM pg_attribute WHERE attname = column_name AND atttypid != 23 AND attrelid = (SELECT relfilenode FROM pg_class WHERE relname = table_name));
  SELECT INTO column_exists EXISTS (SELECT pg_catalog.quote_ident(attname)   FROM pg_catalog.pg_attribute a, pg_catalog.pg_class c  WHERE c.oid = a.attrelid    AND a.attnum > 0    AND NOT a.attisdropped    AND attname=pg_catalog.quote_ident(column_name)   AND (pg_catalog.quote_ident(relname)=pg_catalog.quote_ident(table_name) )    AND pg_catalog.pg_table_is_visible(c.oid) and pg_catalog.format_type(a.atttypid,a.atttypmod) <> 'integer');
  
  IF column_exists THEN
    SQL = 'ALTER TABLE '||table_name||' ADD COLUMN "trad" int REFERENCES ad_str(id_str) ON DELETE CASCADE;';
    EXECUTE SQL;
    SQL = 'UPDATE '||table_name||' SET trad = makeTraductionLangSyst('||column_name||');';
    EXECUTE SQL;
    SQL = 'ALTER TABLE '||table_name||' DROP COLUMN '||column_name||';';
    EXECUTE SQL;
    SQL = 'ALTER TABLE '||table_name||' RENAME trad TO '||column_name||';';
    EXECUTE SQL;
    RETURN true;
  ELSE
    RETURN false;
  END IF;
END;
$$ LANGUAGE plpgsql;