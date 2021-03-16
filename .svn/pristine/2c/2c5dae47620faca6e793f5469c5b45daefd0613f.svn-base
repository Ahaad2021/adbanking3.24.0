CREATE OR REPLACE FUNCTION deploy_multi_agence() RETURNS VARCHAR(4000) AS
$$
DECLARE
C1 refcursor;
output_result VARCHAR(4000) = '';
var_sql_string VARCHAR(1000) = '';
var_sql_string2 VARCHAR(100) = '';
var_sql varchar(5000) = '';
var_libel_menu INTEGER;

var_nom_pere varchar(1000);
var_pos_hierarch smallint;
var_ordre smallint;
var_is_menu boolean; 
var_fonction smallint;
var_is_cliquable boolean;

BEGIN

RAISE NOTICE 'DEBUT - Deploy Multi-Agence';

IF EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_batch_multi_agence') THEN
DROP TABLE adsys_batch_multi_agence;
RAISE NOTICE 'Suppression table adsys_batch_multi_agence effectuée';
END IF;


CREATE TABLE adsys_batch_multi_agence
(
  id_batch serial NOT NULL,
  date_crea timestamp without time zone,
  date_maj timestamp without time zone,
  id_ag integer NOT NULL,
  nom_login character(80) NOT NULL,
  db_backup_path character(255),
  batch_rapport_pdf_path character(255),
  error_message text,
  sql_log text,
  success_flag boolean DEFAULT false,
  CONSTRAINT adsys_batch_multi_agence_pkey PRIMARY KEY (id_batch)
)
WITH (
  OIDS=FALSE
);

output_result:= output_result || E'DROP and CREATE adsys_batch_multi_agence - OK \r\n';

IF EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_audit_multi_agence') THEN
DROP TABLE adsys_audit_multi_agence;
RAISE NOTICE 'Suppression table adsys_audit_multi_agence effectuée';
END IF;

CREATE TABLE adsys_audit_multi_agence
(
  id_audit serial NOT NULL,
  date_crea timestamp without time zone,
  date_maj timestamp without time zone,
  id_ag_local integer NOT NULL,
  id_ag_distant integer NOT NULL,
  nom_login character(80) NOT NULL,
  id_client_distant integer NOT NULL,
  id_compte_distant integer NOT NULL,
  type_transaction character(20) NOT NULL,
  type_choix integer NOT NULL,
  type_choix_libel text,
  montant numeric(30,6) DEFAULT 0,
  post_message text,
  id_his_local integer,
  id_ecriture_local integer,
  id_his_distant integer,
  id_ecriture_distant integer,
  error_message text,
  sql_log text,
  success_flag boolean DEFAULT false,
  CONSTRAINT adsys_audit_multi_agence_pkey PRIMARY KEY (id_audit)
)
WITH (
  OIDS=FALSE
);

output_result:= output_result || E'DROP and CREATE adsys_audit_multi_agence - OK \r\n';

IF EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_multi_agence') THEN
DROP TABLE adsys_multi_agence;
RAISE NOTICE 'Suppression table adsys_multi_agence effectuée';
END IF;

CREATE TABLE adsys_multi_agence
(
  id_mag serial NOT NULL,
  id_agc integer NOT NULL,
  compte_liaison text,
  compte_avoir text,
  is_agence_siege boolean DEFAULT false,
  app_db_description text,
  app_db_host character varying(50),
  app_db_port character varying(10),
  app_db_name character varying(50),
  app_db_username character varying(50),
  app_db_password character varying(50),
  id_ag integer NOT NULL,
  CONSTRAINT adsys_multi_agence_pkey PRIMARY KEY (id_mag)
)
WITH (
  OIDS=FALSE
);

output_result:= output_result || E'DROP and CREATE adsys_multi_agence - OK \r\n';

RAISE NOTICE 'FIN - CREATION TABLES';

RAISE NOTICE 'DEBUT - INSERT TRADUCTION TEXTS';

output_result:= output_result || E'---------------\r';
output_result:= output_result || E'INSERT TRADUCTION\r';
output_result:= output_result || E'---------------\r\n';

var_libel_menu = (select maketraductionlangsyst('Retrait en déplacé'));
var_libel_menu = (select maketraductionlangsyst('Dépôt en déplacé'));
var_libel_menu = (select maketraductionlangsyst('Critères de recherche'));
var_libel_menu = (select maketraductionlangsyst('Visualisation'));
var_libel_menu = (select maketraductionlangsyst('Rapport transactions'));
var_libel_menu = (select maketraductionlangsyst('N° transaction local'));
var_libel_menu = (select maketraductionlangsyst('N° transaction distant'));
var_libel_menu = (select maketraductionlangsyst('Transactions réussis'));
var_libel_menu = (select maketraductionlangsyst('Oui'));
var_libel_menu = (select maketraductionlangsyst('Non'));

var_libel_menu = 0;

output_result:= output_result || E'FIN - INSERT TRADUCTION\r';
output_result:= output_result || E'---------------\r\n';


RAISE NOTICE 'CREATION TABLE TEMPORAIRE POUR LES TRADUCTIONS';

IF EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'tmp_add_menus') THEN
DROP TABLE tmp_add_menus;
RAISE NOTICE 'Suppression table tmp_add_menus';
END IF;

CREATE TABLE tmp_add_menus (id_menu serial NOT NULL,code_menu varchar(100),menu varchar(1000),
nom_pere varchar(1000),pos_hierarch smallint,ordre smallint,is_menu boolean, fonction smallint,is_cliquable boolean DEFAULT true,
CONSTRAINT tmp_add_menus_pkey PRIMARY KEY (id_menu))WITH (OIDS=FALSE);

output_result:= output_result || E'DROP and CREATE table temporaire tmp_add_menus - OK \r\n';

output_result:= output_result || E'\r\n';
output_result:= output_result || E'---------------\r\n';

insert into tmp_add_menus (code_menu,menu,nom_pere,pos_hierarch,ordre,is_menu,fonction,is_cliquable)
	            values('Ope-11','Opération en déplacé','Gen-6',3,8,true,193,true);

insert into tmp_add_menus (code_menu,menu,nom_pere,pos_hierarch,ordre,is_menu,fonction,is_cliquable)
		    values('Tnm-1','Traitements de nuit Multi-Agence','Gen-7', 3, 10, true, 213, true);

insert into tmp_add_menus (code_menu,menu,nom_pere,pos_hierarch,ordre,is_menu,fonction,is_cliquable)
		    values('Ama-1','Visualisation des opérations en déplacé','Gen-6', 3, 9, true, 194, true);
 
/* Alimentation table - menus -  dans adbanking */

var_sql:='select code_menu, menu, nom_pere,pos_hierarch,ordre,is_menu,fonction,is_cliquable from tmp_add_menus order by id_menu asc;';

open C1 for execute(var_sql);
loop
fetch C1 into var_sql_string,var_sql_string2,var_nom_pere,var_pos_hierarch,var_ordre,var_is_menu,var_fonction,var_is_cliquable;

if not found then
exit;
end if;

	var_libel_menu:= (select maketraductionlangsyst(var_sql_string2));

	output_result:= output_result || E'id_str menu - '||var_sql_string||' --> '||cast(var_libel_menu as varchar(100)) || E'\r\n';

	IF EXISTS(SELECT nom_menu FROM menus WHERE nom_menu=TRIM(var_sql_string)) THEN
	output_result:= output_result || E'UPDATE menu - '||var_sql_string||E' \r\n';

		UPDATE menus SET libel_menu= var_libel_menu WHERE nom_menu=TRIM(var_sql_string);

		output_result:= output_result || E'---------------\r\n';

	ELSE
	output_result:= output_result || E'INSERT menu - '||var_sql_string||E' \r\n';

		INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) 
		VALUES (var_sql_string, var_libel_menu, var_nom_pere,var_pos_hierarch,var_ordre,var_is_menu,var_fonction,var_is_cliquable);

		output_result:= output_result || E'---------------\r\n';

	END IF;

end loop;
close C1;

RAISE NOTICE 'FIN - TRADUCTION TEXTS';


RAISE NOTICE 'REMOTE LOGIN';

	IF NOT EXISTS(SELECT login FROM ad_log WHERE login='distant') THEN

	INSERT INTO ad_log(login, pwd, profil, guichet, id_utilisateur, have_left_frame, billet_req, langue, id_ag) VALUES('distant', md5('distant'),1,NULL,1,'t','f','fr_BE',(SELECT numagc()));

	output_result:= output_result || E'\r\n';
	output_result:= output_result || E'Creation login: distant - OK \r\n';
	output_result:= output_result || E'\r\n';
	
	END IF;

RAISE NOTICE 'FIN - REMOTE LOGIN';

RAISE NOTICE 'ECRANS';

	output_result:= output_result || E'---------------\r';
	output_result:= output_result || E'Creation ecrans \r';
	output_result:= output_result || E'---------------\r';
	output_result:= output_result || E'\r';

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ope-11') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ope-11', 'ad_ma/app/views/epargne/operation_deplace.php', 'Ope-11', 193);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ope-12') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ope-12', 'ad_ma/app/views/epargne/operation_deplace.php', 'Ope-11', 193);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ope-13') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ope-13', 'ad_ma/app/views/epargne/operation_deplace.php', 'Ope-11', 193);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rcp-11') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Rcp-11', 'ad_ma/app/views/epargne/retrait_compte.php', 'Ope-11', 92);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rcp-21') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Rcp-21', 'ad_ma/app/views/epargne/retrait_compte.php', 'Ope-11', 92);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rcp-31') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Rcp-31', 'ad_ma/app/views/epargne/retrait_compte.php', 'Ope-11', 92);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rcp-41') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Rcp-41', 'ad_ma/app/views/epargne/retrait_compte.php', 'Ope-11', 92);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Dcp-11') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Dcp-11', 'ad_ma/app/views/epargne/depot_compte.php', 'Ope-11', 93);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Dcp-21') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Dcp-21', 'ad_ma/app/views/epargne/depot_compte.php', 'Ope-11', 93);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Dcp-31') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Dcp-31', 'ad_ma/app/views/epargne/depot_compte.php', 'Ope-11', 93);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Dcp-41') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Dcp-41', 'ad_ma/app/views/epargne/depot_compte.php', 'Ope-11', 93);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ama-1') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ama-1', 'ad_ma/app/views/audit/audit_ma.php', 'Ama-1', 194);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ama-2') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ama-2', 'ad_ma/app/views/audit/audit_ma.php', 'Ama-1', 194);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ama-3') THEN									        
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ama-3', 'ad_ma/app/views/audit/audit_ma.php', 'Ama-1', 194);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tnm-1') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tnm-1', 'ad_ma/batch/batch_ma.php', 'Tnm-1', 213);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tnm-2') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tnm-2', 'ad_ma/batch/batch_ma.php', 'Tnm-1', 213);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tnm-3') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tnm-3', 'ad_ma/batch/batch_ma.php', 'Tnm-1', 213);
END IF;


	output_result:= output_result || E'---------------\r';
	output_result:= output_result || E'Creation ecrans - OK \r';
	output_result:= output_result || E'---------------\r';
	output_result:= output_result || E'\r';


RAISE NOTICE 'Give profile access to menu "Traitements de nuit Multi-Agence"';

	output_result:= output_result || E'---------------\r';
	output_result:= output_result || E'Accès profile au menu "Traitements de nuit Multi-Agence" \r';
	output_result:= output_result || E'---------------\r';

IF NOT EXISTS (SELECT fonction FROM adsys_profils_axs WHERE profil=1 AND fonction=213) THEN
	INSERT INTO adsys_profils_axs (profil, fonction) VALUES (1, 213);
END IF;

	output_result:= output_result || E'---------------\r';
	output_result:= output_result || E'Accès profile au menu "Traitements de nuit Multi-Agence" - OK \r';
	output_result:= output_result || E'---------------\r';



RAISE NOTICE 'FIN - Deploy Multi-Agence';

	output_result:= output_result || E'---------------\r';
	output_result:= output_result || E'FIN - Deploy Multi-Agence \r';
	output_result:= output_result || E'---------------\r';


/*CLEAN UP*/
IF EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'tmp_add_menus') THEN
DROP TABLE tmp_add_menus;
RAISE NOTICE 'Suppression table tmp_add_menus';
END IF;

output_result:= output_result || E'Suppression table temporaire tmp_add_menus \r\n';

RETURN output_result;

END;
$$
LANGUAGE plpgsql;


select deploy_multi_agence();

drop function deploy_multi_agence()
