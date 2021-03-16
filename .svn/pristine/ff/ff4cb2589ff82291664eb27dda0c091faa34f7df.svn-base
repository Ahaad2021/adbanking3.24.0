-- Script de mise à jour de la base de données de la version 3.8.x à la version 3.10.x

-- #350
CREATE OR REPLACE FUNCTION alter_350()  RETURNS INT AS
$$
DECLARE
	output_result INTEGER = 1;

BEGIN

	-- Check if field pp_is_vip exist in table ad_cli
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_cli' AND column_name = 'pp_is_vip') THEN
		ALTER TABLE ad_cli ADD COLUMN pp_is_vip boolean DEFAULT false;
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'pp_is_vip') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_cli' order by ident desc limit 1), 'pp_is_vip', maketraductionlangsyst('Client VIP?'), false, NULL, 'bol', false, false, false);
		output_result := 2;
	END IF;

	-- Check if field access_solde_vip exist in table adsys_profils
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_profils' AND column_name = 'access_solde_vip') THEN
		ALTER TABLE adsys_profils ADD COLUMN access_solde_vip boolean DEFAULT false;
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'access_solde_vip') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'adsys_profils' order by ident desc limit 1), 'access_solde_vip', maketraductionlangsyst('Masquer le solde des VIP ?'), false, NULL, 'bol', false, false, false);
		output_result := 2;
	END IF;

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;
  
SELECT alter_350();
DROP FUNCTION alter_350();

CREATE OR REPLACE FUNCTION create_table_adsys_fonction()  RETURNS INT AS
$$
DECLARE
	output_result INTEGER = 1;

BEGIN

	-- Create Table pour les fonctions systèmes
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_fonction') THEN

		CREATE TABLE "adsys_fonction" (
			"id" SERIAL NOT NULL,
			"code_fonction" integer NOT NULL,
			"libelle" character varying(300),
			"id_ag" integer NULL,
			PRIMARY KEY ("code_fonction")
		)
		WITH (
		  OIDS=FALSE
		);
		COMMENT ON TABLE "adsys_fonction" IS 'Liste des fonctions systèmes';

		-- Insert data
		INSERT INTO adsys_fonction VALUES (344, 3, 'Sélection d''un client', numagc());
		INSERT INTO adsys_fonction VALUES (345, 5, 'Visualisation menu gestion client', numagc());
		INSERT INTO adsys_fonction VALUES (346, 10, 'Modification client', numagc());
		INSERT INTO adsys_fonction VALUES (347, 11, 'Gestion des relations', numagc());
		INSERT INTO adsys_fonction VALUES (348, 15, 'Défection client', numagc());
		INSERT INTO adsys_fonction VALUES (349, 16, 'Finalisation défection client décédé', numagc());
		INSERT INTO adsys_fonction VALUES (350, 17, 'Simulation défection client', numagc());
		INSERT INTO adsys_fonction VALUES (351, 19, 'Faire jouer l''assurance', numagc());
		INSERT INTO adsys_fonction VALUES (352, 20, 'Souscription parts sociales', numagc());
		INSERT INTO adsys_fonction VALUES (353, 25, 'Consultation client', numagc());
		INSERT INTO adsys_fonction VALUES (354, 30, 'Ajout client', numagc());
		INSERT INTO adsys_fonction VALUES (355, 31, 'Perception frais adhésion', numagc());
		INSERT INTO adsys_fonction VALUES (356, 40, 'Visualisation menu documents', numagc());
		INSERT INTO adsys_fonction VALUES (357, 41, 'Commande chèquier', numagc());
		INSERT INTO adsys_fonction VALUES (358, 42, 'Retrait chèquier', numagc());
		INSERT INTO adsys_fonction VALUES (359, 43, 'Extraits de compte', numagc());
		INSERT INTO adsys_fonction VALUES (360, 44, 'Situation globale client', numagc());
		INSERT INTO adsys_fonction VALUES (361, 45, 'Mis En opposition chèque ', numagc());
		INSERT INTO adsys_fonction VALUES (362, 51, 'Visualisation menu épargne', numagc());
		INSERT INTO adsys_fonction VALUES (363, 53, 'Ouverture compte', numagc());
		INSERT INTO adsys_fonction VALUES (364, 54, 'Clôture compte', numagc());
		INSERT INTO adsys_fonction VALUES (365, 55, 'Simulation arrêté compte', numagc());
		INSERT INTO adsys_fonction VALUES (366, 70, 'Retrait', numagc());
		INSERT INTO adsys_fonction VALUES (367, 75, 'Dépôt', numagc());
		INSERT INTO adsys_fonction VALUES (368, 76, 'Transfert compte', numagc());
		INSERT INTO adsys_fonction VALUES (369, 78, 'Prolongation DAT', numagc());
		INSERT INTO adsys_fonction VALUES (370, 79, 'Ordres Permanents', numagc());
		INSERT INTO adsys_fonction VALUES (371, 80, 'Consultation des comptes', numagc());
		INSERT INTO adsys_fonction VALUES (372, 81, 'Recharge Carte Ferlo par Compte epargne', numagc());
		INSERT INTO adsys_fonction VALUES (373, 85, 'Retrait express', numagc());
		INSERT INTO adsys_fonction VALUES (374, 86, 'Dépôt express', numagc());
		INSERT INTO adsys_fonction VALUES (375, 87, 'Frais en attente', numagc());
		INSERT INTO adsys_fonction VALUES (376, 88, 'Modification du compte', numagc());
		INSERT INTO adsys_fonction VALUES (377, 89, 'Bloquer / débloquer un compte', numagc());
		INSERT INTO adsys_fonction VALUES (378, 90, 'Gestion des mandats', numagc());
		INSERT INTO adsys_fonction VALUES (379, 91, 'Activez les comptes dormants', numagc());
		INSERT INTO adsys_fonction VALUES (380, 92, 'Retrait en déplacé', numagc());
		INSERT INTO adsys_fonction VALUES (381, 93, 'Dépôt en déplacé', numagc());
		INSERT INTO adsys_fonction VALUES (382, 95, 'Ajout d''un mandat', numagc());
		INSERT INTO adsys_fonction VALUES (383, 96, 'Modification d''un mandat', numagc());
		INSERT INTO adsys_fonction VALUES (384, 101, 'Visualisation menu crédit', numagc());
		INSERT INTO adsys_fonction VALUES (385, 105, 'Création dossier de crédit', numagc());
		INSERT INTO adsys_fonction VALUES (386, 110, 'Approbation dossier de crédit', numagc());
		INSERT INTO adsys_fonction VALUES (387, 115, 'Rejet dossier de crédit', numagc());
		INSERT INTO adsys_fonction VALUES (388, 120, 'Annulation dossier de crédit', numagc());
		INSERT INTO adsys_fonction VALUES (389, 125, 'Déboursement des fonds', numagc());
		INSERT INTO adsys_fonction VALUES (390, 126, 'Annulation déboursement progressif', numagc());
		INSERT INTO adsys_fonction VALUES (391, 129, 'Correction dossier de crédit', numagc());
		INSERT INTO adsys_fonction VALUES (392, 130, 'Modification dossier de crédit', numagc());
		INSERT INTO adsys_fonction VALUES (393, 131, 'Suspension / ajustement des pénalités', numagc());
		INSERT INTO adsys_fonction VALUES (394, 132, 'Abattement des intérêts et pénalités', numagc());
		INSERT INTO adsys_fonction VALUES (395, 135, 'Simulation échéancier', numagc());
		INSERT INTO adsys_fonction VALUES (396, 140, 'Consultation dossier de crédit', numagc());
		INSERT INTO adsys_fonction VALUES (397, 145, 'Demande Rééchelonement/moratoire', numagc());
		INSERT INTO adsys_fonction VALUES (398, 146, 'Rééchelonement/moratoire', numagc());
		INSERT INTO adsys_fonction VALUES (399, 147, 'Remboursement crédit', numagc());
		INSERT INTO adsys_fonction VALUES (400, 148, 'Réalisation garanties', numagc());
		INSERT INTO adsys_fonction VALUES (401, 149, 'Modification date remboursement', numagc());
		INSERT INTO adsys_fonction VALUES (402, 136, 'Modification de l''échéancier de crédit', numagc());
		INSERT INTO adsys_fonction VALUES (403, 141, 'Demande modification de la date de remboursement', numagc());
		INSERT INTO adsys_fonction VALUES (404, 142, 'Approbation modification de la date de remboursement', numagc());
		INSERT INTO adsys_fonction VALUES (405, 143, 'Demande raccourcissement de la durée du crédit', numagc());
		INSERT INTO adsys_fonction VALUES (406, 144, 'Approbation raccourcissement de la durée du crédit', numagc());
		INSERT INTO adsys_fonction VALUES (407, 151, 'Visualisation menu guichet', numagc());
		INSERT INTO adsys_fonction VALUES (408, 155, 'Approvisionnement', numagc());
		INSERT INTO adsys_fonction VALUES (409, 156, 'Délestage', numagc());
		INSERT INTO adsys_fonction VALUES (410, 158, 'Dépôt par lot // Redondant avec 501', numagc());
		INSERT INTO adsys_fonction VALUES (411, 159, 'Dépôt par lot via fichier', numagc());
		INSERT INTO adsys_fonction VALUES (412, 160, 'Recharge Carte Ferlo par Versement espèce', numagc());
		INSERT INTO adsys_fonction VALUES (413, 170, 'Ajustement encaisse', numagc());
		INSERT INTO adsys_fonction VALUES (414, 180, 'Visualisation des transactions', numagc());
		INSERT INTO adsys_fonction VALUES (415, 181, 'Visualisation des transactions tous guichets', numagc());
		INSERT INTO adsys_fonction VALUES (416, 186, 'Change Cash', numagc());
		INSERT INTO adsys_fonction VALUES (417, 187, 'Gestion des paiements Net Bank', numagc());
		INSERT INTO adsys_fonction VALUES (418, 188, 'Traitement des attentes', numagc());
		INSERT INTO adsys_fonction VALUES (419, 189, 'Passage opérations diverses de caisse/compte', numagc());
		INSERT INTO adsys_fonction VALUES (420, 190, 'Souscription des parts sociales par lot via fichier', numagc());
		INSERT INTO adsys_fonction VALUES (421, 191, 'Chéquier à Imprimer', numagc());
		INSERT INTO adsys_fonction VALUES (422, 192, 'Confirmation des chéquiers Imprimés', numagc());
		INSERT INTO adsys_fonction VALUES (423, 193, 'Opération en déplacé', numagc());
		INSERT INTO adsys_fonction VALUES (424, 194, 'Visualisation des opérations en déplacé', numagc());
		INSERT INTO adsys_fonction VALUES (425, 201, 'Visualisation menu système', numagc());
		INSERT INTO adsys_fonction VALUES (426, 205, 'Ouverture agence', numagc());
		INSERT INTO adsys_fonction VALUES (427, 206, 'Fermeture agence', numagc());
		INSERT INTO adsys_fonction VALUES (428, 210, 'Sauvegarde des données', numagc());
		INSERT INTO adsys_fonction VALUES (429, 211, 'Consolidation de données', numagc());
		INSERT INTO adsys_fonction VALUES (430, 212, 'Batch', numagc());
		INSERT INTO adsys_fonction VALUES (431, 213, 'Traitements de nuit Multi-Agence', numagc());
		INSERT INTO adsys_fonction VALUES (432, 215, 'Modification autre mot de passe', numagc());
		INSERT INTO adsys_fonction VALUES (433, 230, 'Déconnexion autre code utilisateur', numagc());
		INSERT INTO adsys_fonction VALUES (434, 235, 'Ajustement du solde d''un compte', numagc());
		INSERT INTO adsys_fonction VALUES (435, 240, 'Gestion de la licence', numagc());
		INSERT INTO adsys_fonction VALUES (436, 251, 'Visualisation menu paramétrage', numagc());
		INSERT INTO adsys_fonction VALUES (437, 255, 'Visualisation gestion des profils', numagc());
		INSERT INTO adsys_fonction VALUES (438, 256, 'Ajout d''un profil', numagc());
		INSERT INTO adsys_fonction VALUES (439, 257, 'Consultation d''un profil', numagc());
		INSERT INTO adsys_fonction VALUES (440, 258, 'Modification d''un profil', numagc());
		INSERT INTO adsys_fonction VALUES (441, 259, 'Suppression d''un profil', numagc());
		INSERT INTO adsys_fonction VALUES (442, 260, 'Modification mot de passe', numagc());
		INSERT INTO adsys_fonction VALUES (443, 265, 'Visualisation menu gestion des utilisateurs', numagc());
		INSERT INTO adsys_fonction VALUES (444, 270, 'Ajout utilisateur', numagc());
		INSERT INTO adsys_fonction VALUES (445, 271, 'Consultation utilisateur', numagc());
		INSERT INTO adsys_fonction VALUES (446, 272, 'Modification utilisateur', numagc());
		INSERT INTO adsys_fonction VALUES (447, 273, 'Suppression utilisateur', numagc());
		INSERT INTO adsys_fonction VALUES (448, 274, 'Visualisation des devises', numagc());
		INSERT INTO adsys_fonction VALUES (449, 275, 'Ajout d''une devise', numagc());
		INSERT INTO adsys_fonction VALUES (450, 276, 'Modification d''une devise', numagc());
		INSERT INTO adsys_fonction VALUES (451, 277, 'Visualisation des positions de change', numagc());
		INSERT INTO adsys_fonction VALUES (452, 281, 'Visualisation gestion des champs extras', numagc());
		INSERT INTO adsys_fonction VALUES (453, 287, 'Visualisation menu gestion des codes utilisateurs', numagc());
		INSERT INTO adsys_fonction VALUES (454, 288, 'Ajout login', numagc());
		INSERT INTO adsys_fonction VALUES (455, 289, 'Consultation login', numagc());
		INSERT INTO adsys_fonction VALUES (456, 290, 'Modification login', numagc());
		INSERT INTO adsys_fonction VALUES (457, 291, 'Suppression login', numagc());
		INSERT INTO adsys_fonction VALUES (458, 292, 'Visualisation gestion des tables de paramétrage', numagc());
		INSERT INTO adsys_fonction VALUES (459, 293, 'Consultation des tables de paramétrage', numagc());
		INSERT INTO adsys_fonction VALUES (460, 294, 'Modification des tables de paramétrage', numagc());
		INSERT INTO adsys_fonction VALUES (461, 295, 'Ajout dans tables de paramétrage', numagc());
		INSERT INTO adsys_fonction VALUES (462, 296, 'Visualisation jours ouvrables', numagc());
		INSERT INTO adsys_fonction VALUES (463, 297, 'Modification des profils associés aux codes utilisateurs', numagc());
		INSERT INTO adsys_fonction VALUES (464, 298, 'Suppression dans table de paramétrage', numagc());
		INSERT INTO adsys_fonction VALUES (465, 299, 'Modification de frais et commisions', numagc());
		INSERT INTO adsys_fonction VALUES (466, 300, 'Gestion de Jasper report ', numagc());
		INSERT INTO adsys_fonction VALUES (467, 301, 'Visualisation du menu rapport', numagc());
		INSERT INTO adsys_fonction VALUES (468, 310, 'Rapports client', numagc());
		INSERT INTO adsys_fonction VALUES (469, 330, 'Rapports épargne', numagc());
		INSERT INTO adsys_fonction VALUES (470, 350, 'Rapports crédit', numagc());
		INSERT INTO adsys_fonction VALUES (471, 370, 'Rapports agence', numagc());
		INSERT INTO adsys_fonction VALUES (472, 380, 'Rapports externe ', numagc());
		INSERT INTO adsys_fonction VALUES (473, 390, 'Simulation échéancier', numagc());
		INSERT INTO adsys_fonction VALUES (474, 399, 'Visualisation dernier rapport', numagc());
		INSERT INTO adsys_fonction VALUES (475, 401, 'Visualisation du menu comptabilité', numagc());
		INSERT INTO adsys_fonction VALUES (476, 410, 'Gestion du plan comptable', numagc());
		INSERT INTO adsys_fonction VALUES (477, 420, 'Gestion des opérations comptables', numagc());
		INSERT INTO adsys_fonction VALUES (478, 430, 'Rapports', numagc());
		INSERT INTO adsys_fonction VALUES (479, 431, 'Traitement des transactions FERLO', numagc());
		INSERT INTO adsys_fonction VALUES (480, 432, 'Dotation aux provisions crédits ', numagc());
		INSERT INTO adsys_fonction VALUES (481, 433, 'Modification provisions crédits ', numagc());
		INSERT INTO adsys_fonction VALUES (482, 440, 'Gestion des exercices', numagc());
		INSERT INTO adsys_fonction VALUES (483, 441, 'Consultation des exercices', numagc());
		INSERT INTO adsys_fonction VALUES (484, 442, 'Cloture d''un exercice', numagc());
		INSERT INTO adsys_fonction VALUES (485, 443, 'Modification d''un exercice', numagc());
		INSERT INTO adsys_fonction VALUES (486, 444, 'Clôture périodique', numagc());
		INSERT INTO adsys_fonction VALUES (487, 450, 'Gestion des journaux comptables', numagc());
		INSERT INTO adsys_fonction VALUES (488, 451, 'Consultation des journaux comptables', numagc());
		INSERT INTO adsys_fonction VALUES (489, 452, 'Modification des journaux comptables', numagc());
		INSERT INTO adsys_fonction VALUES (490, 453, 'Suppression des journaux comptables', numagc());
		INSERT INTO adsys_fonction VALUES (491, 454, 'Creation d''un journal comptable', numagc());
		INSERT INTO adsys_fonction VALUES (492, 455, 'Saisie des Opérations', numagc());
		INSERT INTO adsys_fonction VALUES (493, 456, 'Ajout compte de contrepartie', numagc());
		INSERT INTO adsys_fonction VALUES (494, 470, 'Saisie écritures utilisateurs', numagc());
		INSERT INTO adsys_fonction VALUES (495, 471, 'Validation écritures utilisateurs', numagc());
		INSERT INTO adsys_fonction VALUES (496, 472, 'Gestion des opérations diverses de caisse/compte', numagc());
		INSERT INTO adsys_fonction VALUES (497, 473, 'Passage des opérations siège/agence', numagc());
		INSERT INTO adsys_fonction VALUES (498, 474, 'Annulation des opérations réciproques', numagc());
		INSERT INTO adsys_fonction VALUES (499, 475, 'Radiation crédit', numagc());
		INSERT INTO adsys_fonction VALUES (500, 476, 'Déclarations de tva', numagc());
		INSERT INTO adsys_fonction VALUES (501, 477, 'Suppression de compte comptable', numagc());
		INSERT INTO adsys_fonction VALUES (502, 478, 'Gestion des écritures libres', numagc());
		INSERT INTO adsys_fonction VALUES (503, 479, 'Mouvementer un compte interne à une date antérieure ', numagc());
		INSERT INTO adsys_fonction VALUES (504, 500, 'Reprise des données comptes de base', numagc());
		INSERT INTO adsys_fonction VALUES (505, 501, 'Reprise de comptes épargne existants', numagc());
		INSERT INTO adsys_fonction VALUES (506, 502, 'Reprise de comptes PS existants', numagc());
		INSERT INTO adsys_fonction VALUES (507, 503, 'Reprise d''un crédit existant', numagc());
		INSERT INTO adsys_fonction VALUES (508, 504, 'Reprise du bilan d''ouverture', numagc());
		INSERT INTO adsys_fonction VALUES (509, 510, 'Régularisation suite erreur logicielle', numagc());
		INSERT INTO adsys_fonction VALUES (510, 511, 'Régularisation suite erreur utilisateur', numagc());
	END IF;

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;
  
SELECT create_table_adsys_fonction();
DROP FUNCTION create_table_adsys_fonction();

-- Re-instate update 3.4.3 : #440
UPDATE d_tableliste set type = 'txt' where nchmpc ='libel_jou';
