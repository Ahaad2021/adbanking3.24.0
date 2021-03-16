CREATE OR REPLACE FUNCTION patch_357() RETURNS void AS 
$$
DECLARE
tableliste_ident INTEGER = 0;

BEGIN

RAISE NOTICE 'DEMARRAGE mise a jour base de données pour le ticket #357';

-- Create the column num_cpte_comptable in ad_cpt if it does not exist
IF (SELECT count(*) from information_schema.columns WHERE table_name = 'ad_cpt' AND column_name='num_cpte_comptable') = 0 THEN
	ALTER TABLE ad_cpt ADD COLUMN num_cpte_comptable character varying(50);
	CREATE INDEX i_ad_cpt_num_cpte_comptable on ad_cpt(num_cpte_comptable);
	RAISE NOTICE 'Created column num_cpte_comptable in table ad_cpt';
END IF;

-- Create Table pour historisation des écarts inventaire / compte comptable if not exists
IF (SELECT count(*) from information_schema.tables WHERE table_name = 'ad_ecart_compta') > 0 THEN
	DROP TABLE ad_ecart_compta;
END IF;

CREATE TABLE "ad_ecart_compta"
(  
  "id" SERIAL NOT NULL,
  "date_ecart" timestamp NOT NULL,	
  "num_cpte_comptable" text NOT NULL,
  "libel_cpte_comptable" text NOT NULL,
  "devise" character(3),
  "solde_cpte_int" numeric(30,6) DEFAULT 0,
  "solde_cpte_comptable" numeric(30,6) DEFAULT 0,
  "ecart" numeric(30,6) DEFAULT 0,
  "id_ag" integer NOT NULL,
  "login" text NULL,	
  "id_his" integer NULL,	
  PRIMARY KEY("id") 
)
WITH (
  OIDS=FALSE
);
COMMENT ON TABLE ad_ecart_compta IS 'Log des écarts comptabilité/inventaire';
COMMENT ON COLUMN "ad_ecart_compta"."date_ecart" IS 'Date à laquelle l''écart a été constaté';
COMMENT ON COLUMN "ad_ecart_compta"."solde_cpte_int" IS 'Le sum solde des comptes internes ad_cpt associés à ce compte comptable à la date_ecart';
COMMENT ON COLUMN "ad_ecart_compta"."solde_cpte_comptable" IS 'Le solde du compte comptable dans ad_cpt_comptable à la date_ecart';
COMMENT ON COLUMN "ad_ecart_compta"."ecart" IS 'L''écart entre les deux soldes';
COMMENT ON COLUMN "ad_ecart_compta"."login" IS 'Si renseigné, le login qui a peut etre causé l''écart';
COMMENT ON COLUMN "ad_ecart_compta"."id_his" IS 'Si renseigné, l''operation comptable loggé dans id_his qui a peut etre causé l''écart';

CREATE INDEX i_ad_ecart_compta_num_cpte_comptable on ad_ecart_compta(num_cpte_comptable);
RAISE NOTICE 'Table ad_ecart_compta ajouté';


-- Insertion dans tableliste
IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ad_ecart_compta') THEN
	INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'ad_ecart_compta', makeTraductionLangSyst('Historisation des écarts inventaire / compte comptable'), true);
	RAISE NOTICE 'Données table ad_ecart_compta rajoutés dans table tableliste';
END IF;

-- Renseigne le identifiant pour insertion dans d_tableliste
tableliste_ident := (select ident from tableliste where nomc like 'ad_ecart_compta' order by ident desc limit 1);

-- Insertion dans d_tableliste
IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_ecart' and tablen = tableliste_ident) THEN
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_ecart',makeTraductionLangSyst('Date à laquelle l''écart a été constaté'), true, NULL, 'dte', true, true, false);
END IF;

IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'num_cpte_comptable' and tablen = tableliste_ident) THEN
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'num_cpte_comptable',makeTraductionLangSyst('Le compte comptable en question'), true, NULL, 'txt', true, true, false);
END IF;

IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'libel_cpte_comptable' and tablen = tableliste_ident) THEN	
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'libel_cpte_comptable',makeTraductionLangSyst('Le libellé du compte comptable'), true, NULL, 'txt', true, false, false);
END IF;

IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'devise' and tablen = tableliste_ident) THEN
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'devise',makeTraductionLangSyst('La devise du compte comptable'), false, NULL, 'txt', true, false, false);
END IF;

IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'solde_cpte_int' and tablen = tableliste_ident) THEN
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'solde_cpte_int',makeTraductionLangSyst('Le sum solde des comptes internes ad_cpt associés à ce compte comptable à la date_ecart'), true, NULL, 'mnt', true, false, false);
END IF;

IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'solde_cpte_comptable' and tablen = tableliste_ident) THEN
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'solde_cpte_comptable',makeTraductionLangSyst('Le solde du compte comptable dans ad_cpt_comptable à la date_ecart'), true, NULL, 'mnt', true, false, false);
END IF;

IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'ecart' and tablen = tableliste_ident) THEN
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'ecart',makeTraductionLangSyst('L''écart entre les deux soldes'), true, NULL, 'mnt', true, false, false);
END IF;

IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id_ag' and tablen = tableliste_ident) THEN
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_ag',makeTraductionLangSyst('L''id agence'), true, NULL, 'int', true, false, false);
END IF;

IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'login' and tablen = tableliste_ident) THEN
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'login',makeTraductionLangSyst('Si renseigné, le login qui a peut etre causé l''écart'), false, NULL, 'txt', true, false, false);
END IF;

IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id_his' and tablen = tableliste_ident) THEN
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_his',makeTraductionLangSyst('Si renseigné, l''operation comptable loggé dans id_his qui a peut etre causé l''écart'), false, NULL, 'int', true, false, false);
END IF;

RAISE NOTICE 'Données champs table ad_ecart_compta rajoutés dans table d_tableliste';
RAISE NOTICE 'FIN mise a jour base de données pour le ticket #357';

END;

$$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_357() OWNER TO adbanking;

-- ----------------------------------------------------------------------------
-- Execution
-- ----------------------------------------------------------------------------
SELECT patch_357();
DROP FUNCTION patch_357();

-- ---------------------------------------------------------------------------
-- Alimentation des comptes internes dans ad_cpt (utilisé par le batch) :
-- ---------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION recup_num_cpte_comptable_cpte_interne() RETURNS integer AS $BODY$
DECLARE

cur_cptes_interne CURSOR FOR SELECT * FROM ad_cpt WHERE etat_cpte != 2 ORDER BY id_cpte;
ligne_cpte RECORD;
id_cpte INTEGER; 
num_cpte_compta TEXT;
toInsert BOOLEAN;
counter INTEGER DEFAULT 0;

BEGIN

-- Fonction qui permet d'alimenter le champ num_cpte_comptable de la table ad_cpt pour les comptes existants
-- Voir ticket #357 sur trac

RAISE NOTICE 'Starting update procedure for column num_cpte_comptable in table ad_cpt';

OPEN cur_cptes_interne; -- Open cursor cur_cptes_interne
FETCH cur_cptes_interne INTO ligne_cpte;

WHILE FOUND LOOP

-- Init
toInsert := false;

-- Depot à vue ou dépot / compte à terme :
IF(ligne_cpte.id_prod = 1 OR ligne_cpte.id_prod > 5) THEN  

	-- compte à l'état dormant qui sont déclassés sur un autre compte comptable, operation 170
	IF(ligne_cpte.etat_cpte = 4) THEN 	
		SELECT INTO num_cpte_compta num_cpte 
		FROM ad_cpt_ope_cptes 
		WHERE type_operation = 170 
		AND sens = 'c' 
		AND id_ag = ligne_cpte.id_ag;
		
	toInsert := true;
	--RAISE NOTICE 'Inserting ad_cpt with etat_cpte = 4 AND id_prod 1 or > 5';
			
	ELSE	-- Depot à vue ou dépot / compte à terme OUVERTS		
		SELECT INTO num_cpte_compta cpte_cpta_prod_ep FROM adsys_produit_epargne 
		WHERE id = ligne_cpte.id_prod 
		AND id_ag = ligne_cpte.id_ag
		AND devise = ligne_cpte.devise;

		toInsert := true;			
		--RAISE NOTICE 'Inserting ad_cpt with etat_cpte != 4 AND != 2 AND id_prod 1 or > 5';					
	END IF;

ELSE IF(ligne_cpte.id_prod = 3) THEN -- comptes de crédit
	SELECT INTO num_cpte_compta etat_cpte.num_cpte_comptable
	FROM adsys_etat_credit_cptes etat_cpte, ad_dcr doss 
	WHERE doss.cre_id_cpte = ligne_cpte.id_cpte
	AND etat_cpte.id_prod_cre = doss.id_prod 
	AND etat_cpte.id_etat_credit = doss.cre_etat
	AND etat_cpte.id_ag = ligne_cpte.id_ag
	AND doss.id_ag = ligne_cpte.id_ag
	AND doss.cre_etat IS NOT NULL; -- exclure les credits non-approvés

	toInsert := true;
	--RAISE NOTICE 'Inserting ad_cpt with id_prod = 3';
	
	ELSE IF(ligne_cpte.id_prod = 4) THEN -- comptes de garantie
		SELECT INTO num_cpte_compta prod.cpte_cpta_prod_cr_gar 
		FROM adsys_produit_credit prod, ad_dcr doss, ad_gar gar
		WHERE gar.gar_num_id_cpte_nantie = ligne_cpte.id_cpte
		AND gar.type_gar = 1
		AND gar.id_doss = doss.id_doss
		AND doss.id_prod = prod.id
		AND prod.id_ag = ligne_cpte.id_ag
		AND doss.id_ag = ligne_cpte.id_ag
		AND gar.id_ag = ligne_cpte.id_ag
		AND doss.cre_etat IS NOT NULL; -- exclure les credits non-approvés

		toInsert := true;
		--RAISE NOTICE 'Inserting ad_cpt with id_prod = 4';
	END IF;
			
END IF;	
END IF;	


IF (toInsert = true) THEN
	--RAISE NOTICE 'id_cpte=% , id_prod=% , etat_cpte=% , num_cpte_comptable=%', ligne_cpte.id_cpte ,ligne_cpte.id_prod, ligne_cpte.etat_cpte, num_cpte_comptable;			
	UPDATE ad_cpt SET num_cpte_comptable = num_cpte_compta WHERE ad_cpt.id_cpte = ligne_cpte.id_cpte;
	counter := counter + 1;
END IF;	
	

FETCH cur_cptes_interne INTO ligne_cpte; -- GET next element 
END LOOP;

CLOSE cur_cptes_interne; -- Close cursor cur_cptes_interne

RAISE NOTICE 'Ending update procedure for column num_cpte_comptable in table ad_cpt';

RETURN counter;

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION recup_num_cpte_comptable_cpte_interne() OWNER TO adbanking;

-- ---------------------------------------------------------------------------------------------------------
-- Verification de l'equilibre comptable par compte comptable
-- ---------------------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION verification_equilibre_comptable(integer, text, text, integer, integer) RETURNS boolean AS $BODY$

DECLARE

compte_interne alias FOR $1;
compte_comptable alias FOR $2;
utilisateur  alias FOR $3;
id_hist alias FOR $4;
id_agence alias FOR $5;

libel_compte TEXT;
devise_compte TEXT;
compte_comptable_recup TEXT;
solde_cpte_interne numeric(30,6) DEFAULT 0;
solde_cpte_compta numeric(30,6) DEFAULT 0;
ecart_calcule numeric(30,6) DEFAULT 0;
ecart_prev numeric(30,6) DEFAULT 0;
has_ecart boolean default false;	-- flag to know the state of the ecart. This value is returned

BEGIN

compte_comptable_recup := compte_comptable;

IF(compte_interne IS NOT NULL) THEN
	SELECT INTO compte_comptable_recup num_cpte_comptable FROM ad_cpt WHERE id_cpte = compte_interne;
END IF;

RAISE NOTICE 'compte_comptable_recup = %', compte_comptable_recup;

IF(compte_comptable_recup IS NULL) THEN
	RAISE EXCEPTION 'Pas de compte comptable fourni !';
END IF;

SELECT INTO solde_cpte_interne SUM(solde) FROM ad_cpt WHERE num_cpte_comptable = compte_comptable_recup AND id_ag = numagc(); 
SELECT INTO solde_cpte_compta solde FROM ad_cpt_comptable WHERE num_cpte_comptable = compte_comptable_recup AND id_ag = numagc();

ecart_calcule := solde_cpte_interne - solde_cpte_compta;

IF(ecart_calcule != 0) THEN
	
	-- Get libelle for the compte compta
	SELECT INTO libel_compte libel_cpte_comptable FROM ad_cpt_comptable WHERE num_cpte_comptable = compte_comptable_recup;
	--RAISE NOTICE 'libel_compte = %', libel_compte;

	-- Get devise for the compte compta
	SELECT INTO devise_compte devise FROM ad_cpt_comptable WHERE num_cpte_comptable = compte_comptable_recup;
	--RAISE NOTICE 'devise_compte = %', libel_compte;
	
	-- Check if we dnt have the same ecart for the same num_cpte_comptable
	SELECT INTO ecart_prev ecart FROM ad_ecart_compta WHERE num_cpte_comptable = compte_comptable_recup ORDER BY id DESC LIMIT 1;	
	--RAISE NOTICE 'ecart_prev = %', ecart_prev;


	-- We have an entry for the num_cpte_comptable and its different from the just calculated value
	IF ( ecart_prev IS NULL OR (ecart_prev != 0 AND ecart_prev <> ecart_calcule)) THEN			
		INSERT INTO ad_ecart_compta (
			date_ecart,
			num_cpte_comptable,
			libel_cpte_comptable,
			devise,
			solde_cpte_int,
			solde_cpte_comptable,
			ecart,
			id_ag,
			login,
			id_his
		) 
		VALUES (
			now(),
			compte_comptable_recup,
			libel_compte,
			devise_compte,
			solde_cpte_interne,
			solde_cpte_compta,
			ecart_calcule,
			numagc(),
			utilisateur,
			id_hist				
		);

		-- set the ecart flag to true
		has_ecart := true; 
	END IF;
END IF;

--RAISE NOTICE 'has_ecart = %', has_ecart;
RETURN has_ecart;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION verification_equilibre_comptable(integer, text, text, integer, integer) OWNER TO adbanking;
COMMENT ON FUNCTION verification_equilibre_comptable(integer, text, text, integer, integer) IS 'Fonction qui verifie l''equilibre entre le solde d''un compte comptable et le solde correspondant des comptes internes assignes a ce compte comptable';

-- ---------------------------------------------------------------------------------------------------------
-- Verification de l'equilibre comptable par le batch
-- ---------------------------------------------------------------------------------------------------------

-- Suppression fonction precedente
DROP function IF EXISTS verification_equilibre_comptable_lot(text);

-- Creation nouvelle fonction
CREATE OR REPLACE FUNCTION verification_equilibre_comptable_lot(text, integer) RETURNS integer AS $BODY$
DECLARE

utilisateur alias FOR $1;
id_histo alias FOR $2; 
cur_cptes_interne CURSOR FOR SELECT DISTINCT num_cpte_comptable FROM ad_cpt WHERE etat_cpte != 2 AND num_cpte_comptable IS NOT NULL ORDER BY num_cpte_comptable;
ligne_cpte RECORD;
counter INTEGER DEFAULT 0;
result BOOLEAN DEFAULT FALSE;

BEGIN

-- Fonction qui verifie l'équilibre inventaire - comptabilité en masse pour tous les num_cpte_comptable renseignés dans ad_cpt
-- Voir ticket #357 sur trac

OPEN cur_cptes_interne; -- Open cursor cur_cptes_interne
FETCH cur_cptes_interne INTO ligne_cpte;

WHILE FOUND LOOP	
	SELECT INTO result verification_equilibre_comptable(NULL, ligne_cpte.num_cpte_comptable, utilisateur, id_histo, numagc());	

FETCH cur_cptes_interne INTO ligne_cpte; -- GET next element 
END LOOP;

CLOSE cur_cptes_interne; -- Close cursor cur_cptes_interne

-- Recupere et retourne le nombre des comptes comptables qui ont des ecarts
SELECT INTO counter count(distinct num_cpte_comptable) FROM ad_ecart_compta;

RETURN counter;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION verification_equilibre_comptable_lot(text, integer) OWNER TO adbanking;
COMMENT ON FUNCTION verification_equilibre_comptable_lot(text, integer) IS 'Fonction qui vérifie l''équilibre inventaire - comptabilité en masse pour tous les num_cpte_comptable renseignés dans ad_cpt';

-- ----------------------------------------------------------------------------
-- Execution
-- ----------------------------------------------------------------------------
SELECT recup_num_cpte_comptable_cpte_interne();
