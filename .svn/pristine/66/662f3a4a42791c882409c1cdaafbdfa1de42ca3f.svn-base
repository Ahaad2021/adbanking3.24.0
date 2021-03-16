CREATE OR REPLACE FUNCTION patch_ticket_361() RETURNS integer AS
$BODY$
DECLARE
var_libel_menu INTEGER;
output_result INTEGER;

BEGIN
RAISE INFO 'CREATION ECRANS/TABLE/OPERATIONS_FINANCIERE -->TRANSFERT PS' ;
----------------------------------CREATION ECRANS
var_libel_menu = (select maketraductionlangsyst('Transfert parts sociales'));
-- Ecrans 
IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Mps-1') THEN
INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,is_cliquable)
VALUES ('Mps-1', var_libel_menu, 'Gen-9',5,11,'t','f');
END IF;


-- ecran  menu
IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Mps-1') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Mps-1','modules/clients/menu_transfert_part.php','Mps-1',21);
END IF;


--ecrans  demande
IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Dps-1') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Dps-1','modules/clients/demande_transfert_part.php','Mps-1',22);
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Dps-2') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Dps-2','modules/clients/demande_transfert_part.php','Mps-1',22);
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Dps-3') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Dps-3','modules/clients/demande_transfert_part.php','Mps-1',22);
END IF;

--ecrans approb

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Aps-1') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Aps-1','modules/clients/approb_transfert_part.php','Mps-1',23);
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Aps-2') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Aps-2','modules/clients/approb_transfert_part.php','Mps-1',23);
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Aps-3') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Aps-3','modules/clients/approb_transfert_part.php','Mps-1',23);
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Aps-4') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Aps-4','modules/clients/approb_transfert_part.php','Mps-1',23);
END IF;




----------------------------CREATION TABLE ad_part_sociale_his-----------------------


IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_part_sociale_his') THEN

	CREATE TABLE ad_part_sociale_his
	(
	  id serial NOT NULL ,
	  date_his timestamp without time zone ,
	  id_client integer,
	  qualite integer,
	  type_fonc integer,
	  id_his integer,
	  nbre_ps_souscrite integer ,
	  nbre_ps_lib integer ,
	  solde_ps_lib numeric(30,6),
	  solde_ps_restant numeric(30,6),
	  nom_login text,
	  id_ag integer,
	  
	  CONSTRAINT ad_part_sociale_his_pkey PRIMARY KEY (id, id_ag),
	  CONSTRAINT ad_part_sociale_his_id_client_fkey FOREIGN KEY (id_client, id_ag)
		  REFERENCES ad_cli (id_client, id_ag) MATCH SIMPLE
		  ON UPDATE NO ACTION ON DELETE NO ACTION,


	  CONSTRAINT ad_part_sociale_his_id_his_fkey FOREIGN KEY (id_his, id_ag)
		  REFERENCES ad_his (id_his , id_ag) MATCH SIMPLE
		  ON UPDATE NO ACTION ON DELETE CASCADE

	)
	WITH (
	  OIDS=FALSE
	);
	ALTER TABLE ad_part_sociale_his
	  OWNER TO postgres;
	COMMENT ON TABLE ad_part_sociale_his
	  IS 'Table historique des operations concernant les parts sociales.'; 

END IF;	


----------------------------CREATION TABLE ad_transfert_ps_his ---------------------

IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_transfert_ps_his') THEN
	
	CREATE TABLE ad_transfert_ps_his
	(
	  id serial NOT NULL ,--numéro dhistorique
	  date_demande timestamp without time zone DEFAULT now(),--date de la demande transfert basant sur etat_transfert
	  libel_operation integer, --champs traduit
	  id_client_src integer,--id_client source
	  id_client_dest integer,--id client destinataire
	  id_cpt_src integer,--id compte client source
	  id_cpt_dest integer,--id compte client destinataire
	  num_cpte_src character varying(50) NOT NULL,-- numero compte du client source
	  num_cpte_dest character varying(50) NOT NULL,-- numero compte du client destinataire
	  type_transfert integer NOT NULL,--le type de transfert choisit
	  init_nbre_ps_sscrt_src integer, -- nombre de part sociale souscrit du client source
	  init_nbre_ps_lib_src integer, -- nombre de part sociale liberer du client source
	  init_solde_part_src numeric(30,6),-- solde initiale de part sociale du client source
	  nbre_part_a_trans integer ,
	  init_nbre_ps_sscrt_dest integer ,-- nombre intiale de part sociale souscrite du client destinataire
	  init_nbre_ps_lib_dest integer ,-- nombre intiale de part sociale liberer du client destinataire
	  
	  init_solde_compte_dest numeric(30,6),-- solde initiale compte du client destinataire
	  id_ag integer, --identifiant  agence

	 id_his integer,  -- fonction utilisé
	 id_operation integer, -- operation passé
	 etat_transfert integer,--etat de transfert
	 date_approb timestamp without time zone,--date approbation de la demande basant sur etat_transfert
	 date_rejet timestamp without time zone,--date de rejet basant sur etat_transfert
	 nouv_nbre_ps_sscrt_src integer,-- nouveau nombre  part du client source apres transfert effectué
	 nouv_nbre_ps_sscrt_dest integer,-- nouveau nombre  part du client destinataire apres transfert effectué
	 nouv_nbre_ps_lib_src integer,-- nouveau nombre  part liberer du client source apres transfert effectué
	 nouv_nbre_ps_lib_dest integer,-- nouveau nombre  part liberer du client destinataire apres transfert effectué
	 nouv_solde_part_src numeric(30,6),-- nouveau solde  part du client source apres transfert effectué
	 nouv_solde_compte_dest numeric(30,6),-- nouveau solde compte du client destinataire apres transfert effectué
	
	 
	  
		CONSTRAINT ad_transfert_ps_his_pkey PRIMARY KEY (id, id_ag),
		CONSTRAINT ad_transfert_ps_his_id_client_src_fkey FOREIGN KEY (id_client_src, id_ag)
		  REFERENCES ad_cli (id_client, id_ag) MATCH SIMPLE
		  ON UPDATE NO ACTION ON DELETE NO ACTION,

		CONSTRAINT ad_transfert_ps_his_id_client_dest_fkey FOREIGN KEY (id_client_dest, id_ag)
		  REFERENCES ad_cli (id_client, id_ag) MATCH SIMPLE
		  ON UPDATE NO ACTION ON DELETE NO ACTION,

		CONSTRAINT ad_transfert_ps_his_libel_operation_trad_fkey FOREIGN KEY (libel_operation)
		  REFERENCES ad_str (id_str) MATCH SIMPLE
		  ON UPDATE NO ACTION ON DELETE CASCADE,

		CONSTRAINT ad_transfert_ps_his_id_his_fkey FOREIGN KEY (id_his, id_ag)
		  REFERENCES ad_his (id_his , id_ag) MATCH SIMPLE
		  ON UPDATE NO ACTION ON DELETE CASCADE

	)
	WITH (
	  OIDS=FALSE
	);
	ALTER TABLE ad_transfert_ps_his
	  OWNER TO postgres;
	COMMENT ON TABLE ad_transfert_ps_his
	  IS 'Table historique de transfert de part sociale.'; 

END IF;	


------------------------------CREATION OPERATION FINANCIERE-----------------------------------------------
-------------------OPE_TRANSFERT VERS COMPTE PS
IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=82 AND categorie_ope = 1 AND id_ag = numagc()) THEN
  -- Transfert vers un autre compte de part sociale
  INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) 
  VALUES (82, 1, numagc(), maketraductionlangsyst('Transfert PS vers un autre compte de part sociale'));
 
  RAISE NOTICE 'Insertion type_operation 82 dans la table ad_cpt_ope effectuée';
  output_result := 2;
 END IF;

 IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=82 AND sens = 'd' AND categorie_cpte = 9 AND id_ag = numagc()) THEN
  INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (82, NULL, 'd', 9, numagc());
 
  RAISE NOTICE 'Insertion type_operation 82 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
  output_result := 2;
 END IF;

 IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=82 AND sens = 'c' AND categorie_cpte = 9 AND id_ag = numagc()) THEN
  INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (82, NULL, 'c', 9, numagc());
 
  RAISE NOTICE 'Insertion type_operation 82 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
  output_result := 2;
 END IF;
 
-------------------OPE_TRANSFERT VERS COMPTE COURANT
 IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=83 AND categorie_ope = 1 AND id_ag = numagc()) THEN
  -- Transfert PS vers compte courant
  INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) 
  VALUES (83, 1, numagc(), maketraductionlangsyst('Transfert PS vers compte courant'));
 
  RAISE NOTICE 'Insertion type_operation 83 dans la table ad_cpt_ope effectuée';
  output_result := 2;
 END IF;

 IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=83 AND sens = 'd' AND categorie_cpte = 9 AND id_ag = numagc()) THEN
  INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (83, NULL, 'd', 9, numagc());
 
  RAISE NOTICE 'Insertion type_operation 83 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
  output_result := 2;
 END IF;

 IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=83 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
  INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (83, NULL, 'c', 1, numagc());
 
  RAISE NOTICE 'Insertion type_operation 83 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
  output_result := 2;
 END IF;
 
 
-------------------MAJ OPE_SOUSCRIPTION -> Liberation
 IF EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=80 AND categorie_ope = 1 AND id_ag = numagc()) THEN
  -- Ex souscription ps
  update ad_cpt_ope set libel_ope = maketraductionlangsyst('Libération parts sociales') WHERE type_operation=80 AND categorie_ope = 1 AND id_ag = numagc();
  RAISE NOTICE 'update libellé type_operation 80 dans la table ad_cpt_ope effectuée';
  output_result := 2;
 END IF;


return 1;

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_ticket_361()
OWNER TO adbanking;

SELECT patch_ticket_361();
DROP FUNCTION patch_ticket_361();
---------------------------------------------------------------------------------------------------------------------------------------------------------------
-------------------------------------------------FONCTION :CREATION ECRANS CONSULTATION PS
---------------------------------------------------------------------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION patch_ecrans_consultation() RETURNS integer AS
$BODY$
DECLARE
var_libel_menu INTEGER;


BEGIN
RAISE INFO 'CREATION ECRANS CONSULTATION PS' ;
----------------------------------CREATION ECRANS
var_libel_menu = (select maketraductionlangsyst('Consultation compte de parts sociales'));
-- Ecrans 
IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Cps-1') THEN
INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,is_cliquable)
VALUES ('Cps-1', var_libel_menu, 'Gen-9',5,12,'t','f');
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Cps-1') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Cps-1','modules/clients/consult_compte_ps.php','Cps-1',26);
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Cps-2') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Cps-2','modules/clients/consult_compte_ps.php','Cps-1',26);
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Cps-3') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Cps-3','modules/clients/consult_compte_ps.php','Cps-1',26);
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Cps-4') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Cps-4','modules/clients/consult_compte_ps.php','Cps-1',26);
END IF;

 return 1;

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_ecrans_consultation()
OWNER TO adbanking;

SELECT patch_ecrans_consultation();
DROP FUNCTION patch_ecrans_consultation();
---------------------------------------------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------FONCTION :MAJ NOUVEAU CHAMPS PS LIBERER / SOUSCRIT
---------------------------------------------------------------------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION nouveau_champs_361()  RETURNS INT AS
$$
DECLARE
	output_result INTEGER = 1;

BEGIN
	RAISE INFO 'MAJ NOUVEAU CHAMPS PS-LIBERER/SOUSCRIT ' ;
	-- Check if field "nbre_parts_lib" exist in table "ad_cli"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_cli' AND column_name = 'nbre_parts_lib') THEN
		ALTER TABLE ad_cli ADD COLUMN nbre_parts_lib integer DEFAULT 0;
		output_result := 2;
	END IF;
	
	
	--insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'nbre_parts_lib') THEN
	
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_cli' order by ident desc limit 1), 'nbre_parts_lib', maketraductionlangsyst('Nombre parts libérées'), false, NULL, 'int', false, false, false);
		output_result := 2;
	END IF;



        -- Check if field "nbre_part_sociale_lib" exist in table "ad_cli"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'nbre_part_sociale_lib') THEN
		ALTER TABLE ad_agc ADD COLUMN nbre_part_sociale_lib integer DEFAULT 0;
		output_result := 2;
	END IF;
	
	
	--insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'nbre_part_sociale_lib') THEN
	
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1), 'nbre_part_sociale_lib', maketraductionlangsyst('Nombre parts sociales libérées'), false, NULL, 'int', false, false, false);
		output_result := 2;
	END IF;

	        -- Check if field "capital_sociale_autorise" exist in table "ad_agc"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'capital_sociale_autorise') THEN
		ALTER TABLE ad_agc ADD COLUMN capital_sociale_autorise numeric(30,6) DEFAULT 0;
		output_result := 2;
	END IF;
	
	
	--insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'capital_sociale_autorise') THEN
	
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1), 'capital_sociale_autorise', maketraductionlangsyst('Capital sociale autorisée (0 pour souscription illimitée)'), false, NULL, 'mnt', false, false, false);
		output_result := 2;
	END IF;



	        -- Check if field "capital_sociale_souscrites" exist in table "ad_agc"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'capital_sociale_souscrites') THEN
		ALTER TABLE ad_agc ADD COLUMN capital_sociale_souscrites numeric(30,6) DEFAULT 0;
		output_result := 2;
	END IF;
	
	
	--insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'capital_sociale_souscrites') THEN
	
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1), 'capital_sociale_souscrites', maketraductionlangsyst('Capital sociale souscrites'), false, NULL, 'mnt', false, false, false);
		output_result := 2;
	END IF;


	       -- Check if field "capital_sociale_lib" exist in table "ad_agc"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'capital_sociale_lib') THEN
		ALTER TABLE ad_agc ADD COLUMN capital_sociale_lib numeric(30,6) DEFAULT 0;
		output_result := 2;
	END IF;
	
	
	--insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'capital_sociale_lib') THEN
	
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1), 'capital_sociale_lib', maketraductionlangsyst('Capital sociale libérées'), false, NULL, 'mnt', false, false, false);
		output_result := 2;
	END IF;



	RETURN output_result;

END;
$$
LANGUAGE plpgsql;
  
SELECT nouveau_champs_361();
DROP FUNCTION nouveau_champs_361();
---------------------------------------------------------------------------------------------------------------------------------------------------------------
-----------------------------------------------------FONCTION :SCRIPT REPRISE PS EXISTANTS
---------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Function: maj_souscription()
-- DROP FUNCTION maj_souscription()

CREATE OR REPLACE FUNCTION maj_souscription()
  RETURNS VOID AS $BODY$
  
DECLARE
   val_nominale  numeric(30,6);
   nbre_part_max_cli integer ;
   id_prod_ps integer;
   ligne_source_cli_cpt RECORD; 

----cursor de donnees
  info_source_cli CURSOR FOR  
 SELECT a.id_client AS id_cli,a.nbre_parts AS nbre_parts, a.nbre_parts_lib AS nbre_parts_lib,b.solde AS soldePS, b.solde_part_soc_restant AS soldeRestant,b.mnt_bloq from ad_cli a, ad_cpt b where a.id_client = b.id_titulaire  and b.id_prod =(SELECT id_prod_cpte_parts_sociales from ad_agc);
----champs calculé     
new_nbre_ps_lib integer;
new_nbre_ps_souscrit integer;
new_soldePS numeric(30,6);
new_soldePSRestant numeric(30,6);
----champs ad_agc
total_nbre_ps_souscrite integer;
total_nbre_ps_lib integer;
cap_sociale_souscrite numeric(30,6);
cap_sociale_lib numeric(30,6);

BEGIN
RAISE INFO 'REPRISE PARTS SOCIALES EXISTANTS' ;
--get valeur from ad_agc
SELECT INTO  val_nominale val_nominale_part_sociale
  FROM ad_agc;
SELECT INTO nbre_part_max_cli nbre_part_social_max_cli
  FROM ad_agc;
SELECT INTO id_prod_ps id_prod_cpte_parts_sociales
  FROM ad_agc;

new_nbre_ps_lib := 0;
new_nbre_ps_souscrit := 0;
new_soldePS :=0;
new_soldePSRestant :=0;

OPEN info_source_cli;
	FETCH info_source_cli INTO ligne_source_cli_cpt;
		WHILE FOUND LOOP
  
         --RAISE INFO 'id_client  = %,  nombre_ps = %, nbre_parts_lib = %, soldePS = %,solde_part_soc_restant = %' , ligne_source_cli_cpt.id_cli, ligne_source_cli_cpt.nbre_parts, ligne_source_cli_cpt.nbre_parts_lib, ligne_source_cli_cpt.soldePS,ligne_source_cli_cpt.soldeRestant ; 

        -------------CALCUL & MAJ NOMBRE PARTS SOCIALE LIBEREES----------------------------------------------------------------------------------------------
         new_nbre_ps_lib  = floor (ligne_source_cli_cpt.soldePS / val_nominale);
          
         --NOTE :control nest pas fait sur le nombre part max client
       IF (new_nbre_ps_lib > 0) THEN
       --RAISE INFO 'new_nbre_ps = % ' ,new_nbre_ps_lib  ;
            --update champ ad_cli.nbre_parts_lib 
            UPDATE ad_cli
            SET nbre_parts_lib = new_nbre_ps_lib 
            WHERE  id_client = ligne_source_cli_cpt.id_cli ;
        END IF; 
        --------------CALCUL & MAJ  NOMBRE PARTS SOCIALE SOUSCRITES-------------------------------------------------------------------------------------------
        new_nbre_ps_souscrit  = ceiling((ligne_source_cli_cpt.soldePS + ligne_source_cli_cpt.soldeRestant)/ val_nominale);   
           
       IF(new_nbre_ps_souscrit >0) THEN
           --update champ ad_cli.nbre_parts
            UPDATE ad_cli
            SET nbre_parts = new_nbre_ps_souscrit 
            WHERE  id_client = ligne_source_cli_cpt.id_cli ;

       END IF;
       --------------CALCUL & MAJ SOLDE PART SOCIALE RESTANT---------------------------------------------------------------------------------------------------
        IF(new_nbre_ps_lib = new_nbre_ps_souscrit) THEN 
                      new_soldePSRestant = 0;
            IF(ligne_source_cli_cpt.soldeRestant != new_soldePSRestant ) THEN
            UPDATE ad_cpt
            SET solde_part_soc_restant = new_soldePSRestant --update spsrestant
            WHERE  id_titulaire = ligne_source_cli_cpt.id_cli AND id_prod = id_prod_ps ;
            END IF;

        ELSE
            --new_soldePSRestant = ( ((new_nbre_ps_souscrit * val_nominale )- ligne_source_cli_cpt.soldePS) -  (new_nbre_ps_lib * val_nominale));
            new_soldePSRestant = ((new_nbre_ps_souscrit * val_nominale )- ligne_source_cli_cpt.soldePS) ;
         IF(ligne_source_cli_cpt.soldeRestant != new_soldePSRestant ) THEN
            UPDATE ad_cpt
            SET solde_part_soc_restant = new_soldePSRestant --update spsrestant
            WHERE  id_titulaire = ligne_source_cli_cpt.id_cli AND id_prod = id_prod_ps ;
            END IF;
           
       END IF;     
        ----------------CALCUL & MAJ MONTANT BLOQ------------------------------------------------------------------------------------------------------------
       IF(ligne_source_cli_cpt.soldePS >0) THEN
           --update champ ad_cli.nbre_parts
           IF(ligne_source_cli_cpt.mnt_bloq != ligne_source_cli_cpt.soldePS ) THEN
            UPDATE ad_cpt
            SET mnt_bloq = ligne_source_cli_cpt.soldePS
            WHERE  id_titulaire = ligne_source_cli_cpt.id_cli and id_prod = id_prod_ps ;
            END IF;
       END IF;
		
		------------------------------------------------------------------------------------------------------------------------------------------------------
		FETCH info_source_cli INTO ligne_source_cli_cpt;
	END LOOP;
CLOSE info_source_cli;

----------------LES MAJ DANS LA TABLE AGENCE ------------------------------------------------------------------------------------
--sum nombre part souscrites
SELECT into total_nbre_ps_souscrite SUM(COALESCE(nbre_parts,0))FROM ad_cli ;
        IF(total_nbre_ps_souscrite > 0) THEN 
                   
            UPDATE ad_agc
            SET nbre_part_sociale = total_nbre_ps_souscrite 
            WHERE  id_ag = (SELECT numagc());
        END IF;

        
--sum nombre part liberees
SELECT into total_nbre_ps_lib SUM(COALESCE(nbre_parts_lib,0))FROM ad_cli ;
        IF(total_nbre_ps_lib > 0) THEN 
                   
            UPDATE ad_agc
            SET nbre_part_sociale_lib = total_nbre_ps_lib 
            WHERE  id_ag = (SELECT numagc());
        END IF;

--SUM capital souscrite
SELECT into cap_sociale_souscrite SUM(COALESCE(nbre_parts * val_nominale,0))FROM ad_cli;
       IF(cap_sociale_souscrite > 0) THEN 
                   
            UPDATE ad_agc
            SET capital_sociale_souscrites = cap_sociale_souscrite
            WHERE  id_ag = (SELECT numagc());
        END IF;

--SUM capital liberées
--SELECT into cap_sociale_lib SUM(COALESCE(nbre_parts_lib * val_nominale,0))FROM ad_cli;
SELECT into cap_sociale_lib SUM(solde)FROM ad_cpt where id_prod = id_prod_ps;
       IF(cap_sociale_lib > 0) THEN 
                   
            UPDATE ad_agc
            SET capital_sociale_lib = cap_sociale_lib
            WHERE  id_ag = (SELECT numagc());
        END IF;
  
---------------------------------------------------------------------------------------------------------------------------
END;
$BODY$
  LANGUAGE plpgsql VOLATILE;
  
ALTER FUNCTION maj_souscription()
  OWNER TO adbanking;
SELECT maj_souscription();
DROP FUNCTION maj_souscription();

---------------------------------------------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------FONCTION :MAJ MENU SOUSCRIPTION / LIBERATION
---------------------------------------------------------------------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION menu_lib_361() RETURNS integer AS
$BODY$
DECLARE
var_libel_menu INTEGER;
output_result INTEGER;

BEGIN
RAISE INFO 'CREATION ECRANS/MAJ MENU SOUSCRIPTION/LIBERATION' ;
----------------------------------CREATION ECRANS
var_libel_menu = (select maketraductionlangsyst('Gestion des parts sociales'));
-- menu gestion des ps
IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Mgp-1') THEN
INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,is_cliquable)
VALUES ('Mgp-1', var_libel_menu, 'Gen-9',5,5,'t','f');
END IF;


--update as sous menu: souscription  as sous menu of Mgp-1
IF EXISTS (SELECT * FROM menus WHERE nom_menu = 'Sps') THEN
update menus set  nom_pere ='Mgp-1', pos_hierarch = 6, ordre = 1, is_cliquable ='f' WHERE nom_menu ='Sps';
END IF;

--IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Sps') THEN
--INSERT INTO menus VALUES ('Sps', maketraductionlangsyst('Souscription parts sociales'), 'Mgp-1', 6, 1, true, 20, true);
--END IF;

-- ecran  menu
IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Mgp-1') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Mgp-1','modules/clients/menu_gestion_part_sociale.php','Mgp-1',27);
END IF;

--create new sous menu liberation PS
IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Lps') THEN
INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,is_cliquable)
VALUES ('Lps', maketraductionlangsyst('Libération parts sociales') , 'Mgp-1',6,2,'t','f');
END IF;

--create new sous menu liberation PS
IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Lps-1') THEN
INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,is_cliquable)
VALUES ('Lps-1', maketraductionlangsyst('Saisie') , 'Lps',7,1,'f','false');
END IF;
--create new sous menu liberation PS
IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Lps-2') THEN
INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,is_cliquable)
VALUES ('Lps-2', maketraductionlangsyst('Demande Confirmation') , 'Lps',7,2,'f','false');
END IF;

--create new sous menu liberation PS
IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Lps-3') THEN
INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,is_cliquable)
VALUES ('Lps-3', maketraductionlangsyst('Confirmation') , 'Lps',7,3,'f','false');
END IF;



--ecrans  liberation
IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Lps-1') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Lps-1','modules/clients/liberation_parts.php','Lps-1',28);
END IF;

--ecrans  liberation
IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Lps-2') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Lps-2','modules/clients/liberation_parts.php','Lps-2',28);
END IF;

--ecrans  liberation
IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Lps-3') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Lps-3','modules/clients/liberation_parts.php','Lps-3',28);
END IF;

--update if exists
IF  EXISTS (SELECT * FROM menus WHERE nom_menu = 'Sps-1') THEN
Update menus set  nom_pere ='Sps' ,pos_hierarch =7 WHERE nom_menu ='Sps-1';
END IF;

IF EXISTS (SELECT * FROM menus WHERE nom_menu = 'Sps-2') THEN
Update menus set  nom_pere ='Sps' ,pos_hierarch =7 WHERE nom_menu ='Sps-2';
END IF;

IF EXISTS (SELECT * FROM menus WHERE nom_menu = 'Sps-3') THEN
Update menus set  nom_pere ='Sps' ,pos_hierarch =7 WHERE nom_menu ='Sps-3';
END IF;

return 1;

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION menu_lib_361()
OWNER TO adbanking;

SELECT menu_lib_361();
DROP FUNCTION menu_lib_361();
