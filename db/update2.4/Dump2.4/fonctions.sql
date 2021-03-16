-- Test d'une fonction qui construit un numéro de compte à partir d'un numéro de client

-- Fonction générant le numéro complet de compte de base d'un client à partir du numéro de client
-- On suppose ici que l'agence est 1

-- Nouvelles fonctions 9/01/04 

-- Fonction renvoyant le numéro d'une agence
CREATE OR REPLACE FUNCTION NumAgc() RETURNS integer AS ' 
  SELECT id_ag from ad_agc;
' LANGUAGE SQL;

CREATE OR REPLACE FUNCTION MaxIdCpte(bigint) RETURNS integer AS '
 SELECT max(num_cpte)+1 FROM ad_cpt WHERE id_titulaire = $1;
' LANGUAGE SQL;

-- Fonction générant un numéro complet de compte
CREATE OR REPLACE FUNCTION makeNumCompletCpte (bigint, bigint, bigint) RETURNS text AS '
  BEGIN
    IF $1 = 1 THEN
      RETURN lpad(NumAgc(),3,''0'')||''-''||lpad($2, 6, ''0'')||''-''||lpad($3, 2, ''0'')||''-''||to_char((to_number(lpad(NumAgc(),3,''0'')||lpad($2, 6, ''0'')||lpad($3, 2, ''0''),''99999999999'')%97), ''FM00'');
    ELSIF $1 = 2 THEN
      RETURN lpad((SELECT code_banque FROM ad_agc), 2, ''0'')||lpad((SELECT code_ville FROM ad_agc), 2, ''0'')||''-''||lpad($2, 5, ''0'')||lpad($3, 2, ''0'')||''-''||to_char((to_number(lpad((SELECT code_banque FROM ad_agc), 2, ''0'')||lpad((SELECT code_ville FROM ad_agc), 2, ''0'')||lpad($2, 5, ''0'')||lpad($3, 2, ''0''),''99999999999'')%97), ''FM00'');
    ELSIF $1 = 3 THEN
      RETURN lpad((SELECT code_banque FROM ad_agc), 3, ''0'')||''-''||lpad($2, 7, ''0'')||''-''||lpad($3, 2, ''0'');
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

	SELECT INTO debut_exo date_deb_exo FROM ad_exercices_compta WHERE date_deb_exo <= date_donnee 
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

	SELECT INTO debut_exo date_deb_exo FROM ad_exercices_compta WHERE date_deb_exo <= date_donnee 
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

	SELECT INTO debut_exo date_deb_exo FROM ad_exercices_compta WHERE date_deb_exo <= date_donnee 
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

	SELECT INTO debut_exo date_deb_exo FROM ad_exercices_compta WHERE date_deb_exo <= date_donnee 
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

	SELECT INTO debut_exo date_deb_exo FROM ad_exercices_compta WHERE date_deb_exo <= date_donnee 
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
	SELECT INTO debut_exo date_deb_exo FROM ad_exercices_compta WHERE date_deb_exo <= date_donnee 
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
	OPEN CUR FOR SELECT a.* FROM ad_cpt a, adsys_produit_epargne b WHERE a.id_prod = b.id AND (
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

--BEGIN;
--SELECT recupeCptesAremunerer(date '15/05/2006','funccursor');
--FETCH ALL IN funccursor;
--COMMIT;

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

*/
CREATE OR REPLACE FUNCTION miseAjourSoldeInteret(date) RETURNS INTEGER  AS '
DECLARE
	date_donnee ALIAS FOR $1;
BEGIN
	-- SUR SOLDE JOURNALIER LE PLUS BAS
	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee 
	WHERE etat_cpte = 1 AND mode_calcul_int_cpte=2 AND solde < solde_calcul_interets;

	-- SUR SOLDE MENSUEL LE PLUS BAS
	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee 
	WHERE etat_cpte=1 AND mode_paiement_cpte=1 AND mode_calcul_int_cpte=3 AND solde<solde_calcul_interets AND isFinMois(date_donnee);

	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee 
	WHERE etat_cpte = 1 AND mode_paiement_cpte =2 AND mode_calcul_int_cpte=3 AND solde < solde_calcul_interets 
	AND isMultipleFrequence(date_donnee, date_ouvert, 1);

	-- SUR SOLDE TRIMESTRIEL LE PLUS BAS
	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee 
	WHERE etat_cpte = 1 AND mode_paiement_cpte = 1 AND mode_calcul_int_cpte=4 AND solde < solde_calcul_interets AND isFinTrimestre(date_donnee);	

	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee 
	WHERE etat_cpte = 1 AND mode_paiement_cpte =2 AND mode_calcul_int_cpte=4 AND solde < solde_calcul_interets 
	AND isMultipleFrequence(date_donnee, date_ouvert, 2);

	-- SUR SOLDE SEMESTRIEL LE PLUS BAS
	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee 
	WHERE etat_cpte = 1 AND mode_paiement_cpte = 1 AND mode_calcul_int_cpte=5 AND solde < solde_calcul_interets AND isFinSemestre(date_donnee);	

	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee 
	WHERE etat_cpte = 1 AND mode_paiement_cpte =2 AND mode_calcul_int_cpte=5 AND solde < solde_calcul_interets 
	AND isMultipleFrequence(date_donnee, date_ouvert, 3);

	-- SUR SOLDE COURANT DU COMPTE
	UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_donnee 
	WHERE etat_cpte = 1 AND mode_calcul_int_cpte = 6;	

	-- SOLDE MOYEN
	UPDATE ad_cpt SET solde_calcul_interets = solde_calcul_interets + solde/getPeriode(freq_calcul_int_cpte,mode_paiement_cpte,date_donnee,date(date_calcul_interets), date(date_ouvert), date(dat_date_fin)) , date_solde_calcul_interets = date_donnee 
	WHERE etat_cpte = 1 AND mode_calcul_int_cpte IN (7,8,9,10) AND date(date_ouvert) != date_donnee ;	

	RETURN 0;


END;
' LANGUAGE plpgsql;
