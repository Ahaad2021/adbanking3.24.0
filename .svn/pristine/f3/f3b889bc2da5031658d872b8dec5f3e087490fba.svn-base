
-- *** Fichier contenant les données de démarrage pour ****


--exercices
-- création premier exercice

INSERT INTO ad_exercices_compta (date_deb_exo,id_ag,date_fin_exo,etat_exo)
VALUES
(date(:debutexo),:agence,date(:finexo),'1');


--Agence
-- attention code institution et au code agence en cas de multiagence --
-- La langue système par défaut est toujours le fr_BE car ça la langue dans laquelle les développeurs ajoutent les chaînes originales
--\set agence 2;
INSERT INTO ad_agc(id_ag, statut, id_prod_cpte_base, id_prod_cpte_parts_sociales,id_prod_cpte_credit,id_prod_cpte_epargne_nantie,
delai_max_eav, alerte_dat_jours,duree_min_avant_octr_credit, octroi_credit_non_soc,
last_date, last_batch, last_prelev_frais_tenue, exercice,langue_systeme_dft,code_antenne)
VALUES(:agence, 2, 1, 2, 3, 4,20, 30,0,'f',now(), now() - interval '1 day', now() - interval '1 day', 1,'fr_BE',:codeAntenne);

-- Journaux
INSERT INTO ad_journaux
(libel_jou,id_ag, num_cpte_princ, etat_jou, code_jou)
VALUES(maketraductionlangsyst('Journal principal'), numagc(), NULL,1, 'ADB');

INSERT INTO ad_journaux
(libel_jou,id_ag, num_cpte_princ, etat_jou, code_jou)
VALUES (maketraductionlangsyst('Journal des opérations diverses'), numagc(), NULL,1, 'ODD');

-- Produits d'épargne
-- Epargne à vue
INSERT INTO  adsys_produit_epargne
(id,id_ag, libel, sens, service_financier, nbre_occurrences, frais_tenue_prorata,
retrait_unique, depot_unique, certif,dat_prolongeable, classe_comptable,mode_paiement)

VALUES (
1,:agence,'Epargne libre','c', true, 1, false,
false, false, false, false, 1,1);

-- Parts sociales
INSERT INTO adsys_produit_epargne
(id, id_ag,libel, sens, service_financier, nbre_occurrences, frais_tenue_prorata,
retrait_unique, depot_unique, certif,dat_prolongeable, classe_comptable)

VALUES (
2, :agence,'Parts sociales', 'c', 'f', 1, 'f',
'f', 'f', 'f', 'f', 4);

-- Compte de crédit
INSERT INTO adsys_produit_epargne
(id,id_ag, libel, sens, service_financier, nbre_occurrences, frais_tenue_prorata,
retrait_unique, depot_unique, certif,dat_prolongeable, classe_comptable)

VALUES (
3,:agence,'Crédit','d', false, 1,
false, false, false, false, false, 0);

-- Epargne nantie
INSERT INTO adsys_produit_epargne
(id, id_ag,libel, sens, service_financier, nbre_occurrences, frais_tenue_prorata,
retrait_unique, depot_unique, certif,dat_prolongeable, classe_comptable)

VALUES (
4,:agence, 'Epargne nantie','c', false, 1, false,
false,false, false,  false, 3);


-- Compte d'attente créditeur
INSERT INTO adsys_produit_epargne
(id, id_ag,libel, sens, service_financier, nbre_occurrences, frais_tenue_prorata,
retrait_unique, depot_unique, certif,dat_prolongeable, classe_comptable)

VALUES (
5,:agence, 'Compte d''attente créditeur', 'c', false, 1, false,
false,false, false,  false, 7);

--Setval pour les prochains produits d"épargne
SELECT setval('adsys_produit_epargne_id_seq', 6, true);

--Création du premier état de crédit : Crédits sains
INSERT INTO adsys_etat_credits(id, libel, nbre_jours, id_etat_prec, id_ag) VALUES (DEFAULT, 'Sain', 1, NULL,:agence);

--Insertion de la version de la base de données
INSERT INTO adsys_version_schema(version,date_version) VALUES('1.1.0','2010-02-24');

-- Création des pièces comptables
-- Ces données sont insérés dans adsys_type_piece_payement pour garder la cohérence avec l'array $adsys["adsys_type_piece_payement"] de tableSys.php : Voir #782
-- la colonne id doit respecter l'ordre établi dans tableSys.php
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Espèce'),NumAgc());            /* id=1 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Chèque extérieur'),NumAgc());  /* id=2 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Ordre de paiement'),NumAgc()); /* id=3 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Autorisation de retrait sans livret/chèque'),NumAgc());    /* id=4 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Travelers cheque'),NumAgc());  /* id=5 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Mise à disposition'),NumAgc());/* id=6 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Envoi argent'),NumAgc());      /* id=7 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Reçu ADbanking'),NumAgc());    /* id=8 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Facture'),NumAgc());           /* id=9 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Extrait de compte'),NumAgc()); /* id=10 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Reçu externe'),NumAgc());      /* id=11 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Contrat'),NumAgc());           /* id=12 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Bordereau'),NumAgc());         /* id=13 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Opération Diverse'),NumAgc()); /* id=14 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Chèque guichet'),NumAgc());    /* id=15 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Ordre permanent'),NumAgc());   /* id=16 */

--Insertion des schemas comptables internes
INSERT INTO ad_cpt_ope VALUES (10,maketraductionlangsyst('Remboursement capital sur crédits'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (11,maketraductionlangsyst('Annulation remboursement capital sur crédits'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (20,maketraductionlangsyst('Remboursement intérêts sur crédits'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (21,maketraductionlangsyst('Annulation remboursement intérêts sur crédits'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (30,maketraductionlangsyst('Remboursement pénalités'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (31,maketraductionlangsyst('Annulation remboursement pénalités'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (40,maketraductionlangsyst('Versement des intérêts sur compte epargne'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (50,maketraductionlangsyst('Retrait des frais de tenue de compte'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (60,maketraductionlangsyst('Perception des frais de fermeture'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (61,maketraductionlangsyst('Fermeture de compte au guichet'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (62,maketraductionlangsyst('Fermeture de compte par transfert'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (70,maketraductionlangsyst('Compensation assurance'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (80,maketraductionlangsyst('Souscription parts sociales'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (81,maketraductionlangsyst('Remboursement parts sociales'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (90,maketraductionlangsyst('Perception frais adhésion'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (100,maketraductionlangsyst('Perception des frais d''ouverture de compte'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (110,maketraductionlangsyst('Pénalité rupture anticipée'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (120,maketraductionlangsyst('Transfert entre comptes d''epargne'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (123,maketraductionlangsyst('Restitution de la garantie numéraire'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (124,maketraductionlangsyst('Recupération de la garantie numéraire'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (131,maketraductionlangsyst('Perception des frais de retrait'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (140,maketraductionlangsyst('Retrait en espèces'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (141, maketraductionlangsyst('Recharge Carte Ferlo par compte Epargne'),1, :agence );
INSERT INTO ad_cpt_ope VALUES (142, maketraductionlangsyst('Retrait espèce par Carte Ferlo'),1, :agence );
INSERT INTO ad_cpt_ope VALUES (143, maketraductionlangsyst('Recharge Carte Ferlo par versement espèce'),1, :agence );
INSERT INTO ad_cpt_ope VALUES (150,maketraductionlangsyst('Perception frais de dépôt'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (151,maketraductionlangsyst('Frais de virement'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (152,maketraductionlangsyst('Frais de transfert'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (160,maketraductionlangsyst('Dépôt espèces'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (162, maketraductionlangsyst('Dépôt/Payement par Carte Ferlo'), 1, :agence );
INSERT INTO ad_cpt_ope VALUES (170, maketraductionlangsyst('Déclasser les Comptes dormants'), 1, :agence );
INSERT INTO ad_cpt_ope VALUES (200,maketraductionlangsyst('Frais de dossier de crédit'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (201,maketraductionlangsyst('Annulation transfert des frais'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (210,maketraductionlangsyst('Déboursement crédit '),1,:agence);
INSERT INTO ad_cpt_ope VALUES (211,maketraductionlangsyst('Annulation déboursement crédit'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (212,maketraductionlangsyst('Mise en attente de déboursement progressif'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (213,maketraductionlangsyst('Annulation déboursement progressif'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (220,maketraductionlangsyst('Transfert des garanties numéraires'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (221,maketraductionlangsyst('Annulation transfert des garanties numéraires'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (230,maketraductionlangsyst('Transfert des assurances'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (231,maketraductionlangsyst('Annulation transfert des assurances'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (260,maketraductionlangsyst('Ajustement encaisse à la baisse'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (261,maketraductionlangsyst('Ajustement encaisse à la hausse'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (270,maketraductionlangsyst('Déclassement/reclassement crédit'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (271,maketraductionlangsyst('Provisions crédits'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (272,maketraductionlangsyst('Reprise sur provisions crédits'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (275,maketraductionlangsyst('Transfert du solde lié à un état de crédit d''un produit de crédit'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (280,maketraductionlangsyst('Passage crédit en perte'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (290,maketraductionlangsyst('Approvisionnement guichet'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (300,maketraductionlangsyst('Délestage guichet'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (320,maketraductionlangsyst('Gains sur arrondis versés au guichet'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (321,maketraductionlangsyst('Gains sur arrondis prélevés sur Compte Epargne'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (330,maketraductionlangsyst('Recouvrement de frais en attente'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (340,maketraductionlangsyst('Passage en perte de frais en attente'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (350,maketraductionlangsyst('Intégration exceptionnelle solde du compte d''epargne dans fonds propres'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (352,maketraductionlangsyst('Intégration exceptionnelle solde du compte Garanties dans fonds propres'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (353,maketraductionlangsyst('Intégration exceptionnelle parts sociales dans fonds propres'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (354,maketraductionlangsyst('Passage en perte solde compte épargne débiteur'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (360,maketraductionlangsyst('Perception commissions de déboursement'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (361,maketraductionlangsyst('Annulation perception commissions de déboursement'),1, :agence);
INSERT INTO ad_cpt_ope VALUES (370,maketraductionlangsyst('Virement banque pour le compte d''un client'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (390,maketraductionlangsyst('Augmentation capital suite à rééch/moratoire sur crédit '),1,:agence);
INSERT INTO ad_cpt_ope VALUES (400,maketraductionlangsyst('Régularisation suite à apurement crédit rééchelonné'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (401,maketraductionlangsyst('Contre régularisation suite à apurement crédit rééchelonné'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (410,maketraductionlangsyst('Recouvrement sur crédit en perte'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (411,maketraductionlangsyst('Annulation recouvrement sur crédit en perte'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (420,maketraductionlangsyst('Retrait par chèque sur compte epargne'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (421,maketraductionlangsyst('Virement client sur la banque'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (440,maketraductionlangsyst('Ajustement du solde d''un compte d''epargne en faveur du client'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (442,maketraductionlangsyst('Ajustement du solde d''un compte d''epargne en faveur de l''IMF'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (450,maketraductionlangsyst('Perception de commission de change'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (451,maketraductionlangsyst('Perception de taxe de change'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (452,maketraductionlangsyst('Perception de bénéfice sur le taux de change'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (453,maketraductionlangsyst('Variation négative du taux de change'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (454,maketraductionlangsyst('Variation positive du taux de change'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (455,maketraductionlangsyst('Versement au guichet du reste change'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (456,maketraductionlangsyst('Versement sur compte de base du reste change'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (457,maketraductionlangsyst('Intégration aux produits du reste change'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (458,maketraductionlangsyst('Vente à perte de devise'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (460,maketraductionlangsyst('Change cash'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (470,maketraductionlangsyst('Perception des frais de dossier découvert'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (471,maketraductionlangsyst('Perception des intérets débiteurs'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (472,maketraductionlangsyst('Perception des frais de chèquier'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (473,maketraductionlangsyst('Paiement de tva déductible'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (474,maketraductionlangsyst('Perception de tva collectée'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (475,maketraductionlangsyst('Déclaration de tva'),1,:agence);

INSERT INTO ad_cpt_ope VALUES (500,maketraductionlangsyst('Mise en attente chèque'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (501,maketraductionlangsyst('Acceptation chèque externe'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (502,maketraductionlangsyst('Envoi chèque'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (503,maketraductionlangsyst('Réception chèque externe'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (504,maketraductionlangsyst('Refus chèque'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (505,maketraductionlangsyst('Frais de refus chèque (client)'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (506,maketraductionlangsyst('Frais de refus chèque (correspondant)'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (507,maketraductionlangsyst('Dépôt travelers cheque'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (508,maketraductionlangsyst('Virement national'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (510,maketraductionlangsyst('Perception frais de crédit direct sauf bonne fin'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (511,maketraductionlangsyst('Retrait travelers cheque'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (512,maketraductionlangsyst('Retrait cash par chèque'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (513,maketraductionlangsyst('OP vers un compte extérieur'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (514,maketraductionlangsyst('OP (hors client) vers l''extérieur'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (515,maketraductionlangsyst('Frais OP (hors client) vers l''extérieur'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (516,maketraductionlangsyst('Envoi OP (hors client) vers l''extérieur'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (517,maketraductionlangsyst('Refus OP (hors client) vers l''extérieur'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (518,maketraductionlangsyst('Frais de refus OP (à payer au correspondant)'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (519,maketraductionlangsyst('Remboursement d''un OP refusé'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (520,maketraductionlangsyst('Frais de refus OP (à facturer au donneur d''ordre)'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (521,maketraductionlangsyst('Mise à disposition'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (522,maketraductionlangsyst('Frais de mise à disposition (à payer au correspondant)'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (523,maketraductionlangsyst('Paiement d''une mise à disposition'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (524,maketraductionlangsyst('Frais d''une mise à disposition'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (525,maketraductionlangsyst('Refus d''une mise à disposition'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (526,maketraductionlangsyst('Envoi d''un OP vers un correspondant'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (527,maketraductionlangsyst('Rejet d''un OP vers l''extérieur'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (528,maketraductionlangsyst('Rejet d''une mise à disposition'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (529,maketraductionlangsyst('Réception d''un chèque client'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (600,maketraductionlangsyst('Dépôt au siège'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (601,maketraductionlangsyst('Dépôt agence'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (602,maketraductionlangsyst('Emprunt auprès du siège'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (603,maketraductionlangsyst('Prêt à une agence'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (604,maketraductionlangsyst('Titres de participation'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (605,maketraductionlangsyst('Parts sociales agence'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (606,maketraductionlangsyst('Participation aux charges du réseau'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (607,maketraductionlangsyst('Refacturation aux agences'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (608, maketraductionlangsyst('Retrait au siège'),1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES (609, maketraductionlangsyst('Retrait des agences'),1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES (610, maketraductionlangsyst('Remboursement crédit du siège'),1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES (611, maketraductionlangsyst('Remboursement crédit aux agences'),1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES (612, maketraductionlangsyst('Récupération parts sociales'),1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES (613, maketraductionlangsyst('Remboursement parts sociales'),1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES (1000,maketraductionlangsyst('Contre-passation'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (1001,maketraductionlangsyst('Virement du compte de résultat'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (1003,maketraductionlangsyst('Virement Solde d''un compte principal'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (1002,maketraductionlangsyst('Mise en attente de frais'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (1004,maketraductionlangsyst('Migration de fonds suite modification paramétrage des produits d''épargne'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (1005,maketraductionlangsyst('transfert solde  d'' un compte supprimé'),1, NumAgc());

INSERT INTO ad_cpt_ope VALUES (2000,maketraductionlangsyst('Opération de régularisation'),1,:agence);
INSERT INTO ad_cpt_ope VALUES (2001,maketraductionlangsyst('Reprise des crédits'),1,:agence);

INSERT INTO ad_cpt_ope VALUES (9999,maketraductionlangsyst('Non disponible'),1,:agence);


--Schemas

-- METTEZ LES OPERATIONS DANS L'ORDRE LORSQUE VOUS EN RAJOUTEZ !!!
INSERT INTO ad_cpt_ope_cptes VALUES (10,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (10,NULL, 'c', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (11,NULL, 'd', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (11,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (20,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (20,NULL, 'c', 6,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (21,NULL, 'd', 6,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (21,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (30,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (30,NULL, 'c', 7,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (31,NULL, 'd', 7,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (31,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (40,NULL, 'd', 10,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (40,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (50,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (50,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (60,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (60,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (61,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (61,NULL, 'c', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (62,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (62,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (70,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (70,NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (80,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (80,NULL, 'c', 9,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (81,NULL, 'd', 9,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (81,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (90,NULL, 'd', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (90,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (100,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (100,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (110,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (110,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (120,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (120,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (123,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (123,NULL, 'd', 8,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (124,NULL, 'c', 8,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (124,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (131,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (131,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (140,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (140,NULL, 'c', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (141,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (141,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (142,NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (142,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (143,NULL, 'd', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (143,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (150,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (150,NULL, 'd', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (151,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (151,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (152,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (152,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (160,NULL, 'd', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (160,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (162,NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (162,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (170,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (170,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (200,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (200,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (201,NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (201,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (210,NULL, 'd', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (210,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (211,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (211,NULL, 'c', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (212,NULL, 'd', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (212,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (213,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (213,NULL, 'c', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (220,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (220,NULL, 'c', 8,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (221,NULL, 'd', 8,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (221,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (230,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (230,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (231,NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (231,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (260,NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (260,NULL, 'c', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (261,NULL, 'd', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (261,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (270,NULL, 'd', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (270,NULL, 'c', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (271,NULL, 'd', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (272,NULL, 'c', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (272,NULL, 'd', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (271,NULL, 'c', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (275,NULL, 'd', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (275,NULL, 'c', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (280,NULL, 'd', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (280,NULL, 'c', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (290,NULL, 'c', 3,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (290,NULL, 'd', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (300,NULL, 'd', 3,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (300,NULL, 'c', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (320,NULL, 'd', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (320,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (321,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (321,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (330,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (330,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (340,NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (340,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (350,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (350,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (352,NULL, 'd', 8,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (352,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (353,NULL, 'd', 9,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (353,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (354,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (354,NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (360,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (360,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (361,NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (361,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (370,NULL, 'd', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (370,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (390,NULL, 'd', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (390,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (400,NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (400,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (401,NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (401,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (410,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (410,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (411,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (411,(SELECT num_cpte FROM ad_cpt_ope_cptes WHERE type_operation = 410 AND sens = 'c' AND id_ag = :agence LIMIT 1), 'd', 7,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (420,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (420,NULL, 'c', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (421,NULL, 'c', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (421,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (440,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (440,NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (442,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (442,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (450,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (450,NULL, 'c', 16,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (451,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (451,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (452,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (452,NULL, 'c', 17,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (453,NULL, 'd', 13,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (453,NULL, 'c', 14,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (454,NULL, 'd', 15,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (454,NULL, 'c', 13,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (455,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (455,NULL, 'c', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (456,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (456,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (457,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (457,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (458,NULL, 'd', 21,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (458,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (460,NULL, 'd', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (460,NULL, 'c', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (470,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (470,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (471,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (471,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (472,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (472,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (473,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (473,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (474,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (474,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (475,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (475,NULL, 'd', 1,:agence);

INSERT INTO ad_cpt_ope_cptes VALUES (500,NULL, 'd', 19,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (500,NULL, 'c', 20,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (501,NULL, 'd', 20,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (501,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (502,NULL, 'd', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (502,NULL, 'c', 19,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (503,NULL, 'd', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (503,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (504,NULL, 'd', 20,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (504,NULL, 'c', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (505,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (505,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (506,NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (506,NULL, 'c', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (507,NULL, 'd', 18,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (507,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (508,NULL, 'd', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (508,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (510,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (510,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (511,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (511,NULL, 'c', 18,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (512,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (512,NULL, 'c', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (513,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (513,NULL, 'c', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (514,NULL, 'd', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (514,NULL, 'c', 20,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (515,NULL, 'd', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (515,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (516,NULL, 'd', 20,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (516,NULL, 'c', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (517,NULL, 'd', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (517,NULL, 'c', 20,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (518,NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (518,NULL, 'c', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (519,NULL, 'd', 20,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (519,NULL, 'c', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (520,NULL, 'd', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (520,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (521,NULL, 'd', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (521,NULL, 'c', 2,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (522,NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (522,NULL, 'c', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (523,NULL, 'd', 20,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (523,NULL, 'c', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (524,NULL, 'd', 4,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (524,NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (525,NULL, 'd', 20,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (525,NULL, 'c', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (526,NULL, 'd', 20,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (526,NULL, 'c', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (527,NULL, 'd', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (527,NULL, 'c', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (528,NULL, 'd', 20,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (528,NULL, 'c', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (529,NULL, 'd', 1,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (529,NULL, 'c', 5,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (600, NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (600, NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (601, NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (601, NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (602, NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (602, NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (603, NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (603, NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (604, NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (604, NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (605, NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (605, NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (606, NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (606, NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (607, NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (607, NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (608, NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (608, NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (609, NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (609, NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (610, NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (610, NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (611, NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (611, NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (612, NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (612, NULL, 'c', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (613, NULL, 'd', 0,:agence);
INSERT INTO ad_cpt_ope_cptes VALUES (613, NULL, 'c', 0,:agence);

INSERT INTO adsys_param_epargne (id_ag) VALUES (:agence);


INSERT INTO adsys_fonction VALUES (344, 3, 'Sélection d''un client', :agence);
INSERT INTO adsys_fonction VALUES (345, 5, 'Visualisation menu gestion client', :agence);
INSERT INTO adsys_fonction VALUES (346, 10, 'Modification client', :agence);
INSERT INTO adsys_fonction VALUES (347, 11, 'Gestion des relations', :agence);
INSERT INTO adsys_fonction VALUES (348, 15, 'Défection client', :agence);
INSERT INTO adsys_fonction VALUES (349, 16, 'Finalisation défection client décédé', :agence);
INSERT INTO adsys_fonction VALUES (350, 17, 'Simulation défection client', :agence);
INSERT INTO adsys_fonction VALUES (351, 19, 'Faire jouer l''assurance', :agence);
INSERT INTO adsys_fonction VALUES (352, 20, 'Souscription parts sociales', :agence);
INSERT INTO adsys_fonction VALUES (353, 25, 'Consultation client', :agence);
INSERT INTO adsys_fonction VALUES (354, 30, 'Ajout client', :agence);
INSERT INTO adsys_fonction VALUES (355, 31, 'Perception frais adhésion', :agence);
INSERT INTO adsys_fonction VALUES (356, 40, 'Visualisation menu documents', :agence);
INSERT INTO adsys_fonction VALUES (357, 41, 'Commande chèquier', :agence);
INSERT INTO adsys_fonction VALUES (358, 42, 'Retrait chèquier', :agence);
INSERT INTO adsys_fonction VALUES (359, 43, 'Extraits de compte', :agence);
INSERT INTO adsys_fonction VALUES (360, 44, 'Situation globale client', :agence);
INSERT INTO adsys_fonction VALUES (361, 45, 'Mis En opposition chèque ', :agence);
INSERT INTO adsys_fonction VALUES (362, 51, 'Visualisation menu épargne', :agence);
INSERT INTO adsys_fonction VALUES (363, 53, 'Ouverture compte', :agence);
INSERT INTO adsys_fonction VALUES (364, 54, 'Clôture compte', :agence);
INSERT INTO adsys_fonction VALUES (365, 55, 'Simulation arrêté compte', :agence);
INSERT INTO adsys_fonction VALUES (366, 70, 'Retrait', :agence);
INSERT INTO adsys_fonction VALUES (367, 75, 'Dépôt', :agence);
INSERT INTO adsys_fonction VALUES (368, 76, 'Transfert compte', :agence);
INSERT INTO adsys_fonction VALUES (369, 78, 'Prolongation DAT', :agence);
INSERT INTO adsys_fonction VALUES (370, 79, 'Ordres Permanents', :agence);
INSERT INTO adsys_fonction VALUES (371, 80, 'Consultation des comptes', :agence);
INSERT INTO adsys_fonction VALUES (372, 81, 'Recharge Carte Ferlo par Compte epargne', :agence);
INSERT INTO adsys_fonction VALUES (373, 85, 'Retrait express', :agence);
INSERT INTO adsys_fonction VALUES (374, 86, 'Dépôt express', :agence);
INSERT INTO adsys_fonction VALUES (375, 87, 'Frais en attente', :agence);
INSERT INTO adsys_fonction VALUES (376, 88, 'Modification du compte', :agence);
INSERT INTO adsys_fonction VALUES (377, 89, 'Bloquer / débloquer un compte', :agence);
INSERT INTO adsys_fonction VALUES (378, 90, 'Gestion des mandats', :agence);
INSERT INTO adsys_fonction VALUES (379, 91, 'Activez les comptes dormants', :agence);
INSERT INTO adsys_fonction VALUES (380, 92, 'Retrait en déplacé', :agence);
INSERT INTO adsys_fonction VALUES (381, 93, 'Dépôt en déplacé', :agence);
INSERT INTO adsys_fonction VALUES (382, 95, 'Ajout d''un mandat', :agence);
INSERT INTO adsys_fonction VALUES (383, 96, 'Modification d''un mandat', :agence);
INSERT INTO adsys_fonction VALUES (384, 101, 'Visualisation menu crédit', :agence);
INSERT INTO adsys_fonction VALUES (385, 105, 'Création dossier de crédit', :agence);
INSERT INTO adsys_fonction VALUES (386, 110, 'Approbation dossier de crédit', :agence);
INSERT INTO adsys_fonction VALUES (387, 115, 'Rejet dossier de crédit', :agence);
INSERT INTO adsys_fonction VALUES (388, 120, 'Annulation dossier de crédit', :agence);
INSERT INTO adsys_fonction VALUES (389, 125, 'Déboursement des fonds', :agence);
INSERT INTO adsys_fonction VALUES (390, 126, 'Annulation déboursement progressif', :agence);
INSERT INTO adsys_fonction VALUES (391, 129, 'Correction dossier de crédit', :agence);
INSERT INTO adsys_fonction VALUES (392, 130, 'Modification dossier de crédit', :agence);
INSERT INTO adsys_fonction VALUES (393, 131, 'Suspension / ajustement des pénalités', :agence);
INSERT INTO adsys_fonction VALUES (394, 132, 'Abattement des intérêts et pénalités', :agence);
INSERT INTO adsys_fonction VALUES (395, 135, 'Simulation échéancier', :agence);
INSERT INTO adsys_fonction VALUES (396, 140, 'Consultation dossier de crédit', :agence);
INSERT INTO adsys_fonction VALUES (397, 145, 'Demande Rééchelonement/moratoire', :agence);
INSERT INTO adsys_fonction VALUES (398, 146, 'Rééchelonement/moratoire', :agence);
INSERT INTO adsys_fonction VALUES (399, 147, 'Remboursement crédit', :agence);
INSERT INTO adsys_fonction VALUES (400, 148, 'Réalisation garanties', :agence);
INSERT INTO adsys_fonction VALUES (401, 149, 'Modification date remboursement', :agence);
INSERT INTO adsys_fonction VALUES (402, 136, 'Modification de l''échéancier de crédit', :agence);
INSERT INTO adsys_fonction VALUES (403, 141, 'Demande modification de la date de remboursement', :agence);
INSERT INTO adsys_fonction VALUES (404, 142, 'Approbation modification de la date de remboursement', :agence);
INSERT INTO adsys_fonction VALUES (405, 143, 'Demande raccourcissement de la durée du crédit', :agence);
INSERT INTO adsys_fonction VALUES (406, 144, 'Approbation raccourcissement de la durée du crédit', :agence);
INSERT INTO adsys_fonction VALUES (407, 151, 'Visualisation menu guichet', :agence);
INSERT INTO adsys_fonction VALUES (408, 155, 'Approvisionnement', :agence);
INSERT INTO adsys_fonction VALUES (409, 156, 'Délestage', :agence);
INSERT INTO adsys_fonction VALUES (410, 158, 'Dépôt par lot // Redondant avec 501', :agence);
INSERT INTO adsys_fonction VALUES (411, 159, 'Dépôt par lot via fichier', :agence);
INSERT INTO adsys_fonction VALUES (412, 160, 'Recharge Carte Ferlo par Versement espèce', :agence);
INSERT INTO adsys_fonction VALUES (413, 170, 'Ajustement encaisse', :agence);
INSERT INTO adsys_fonction VALUES (414, 180, 'Visualisation des transactions', :agence);
INSERT INTO adsys_fonction VALUES (415, 181, 'Visualisation des transactions tous guichets', :agence);
INSERT INTO adsys_fonction VALUES (416, 186, 'Change Cash', :agence);
INSERT INTO adsys_fonction VALUES (417, 187, 'Gestion des paiements Net Bank', :agence);
INSERT INTO adsys_fonction VALUES (418, 188, 'Traitement des attentes', :agence);
INSERT INTO adsys_fonction VALUES (419, 189, 'Passage opérations diverses de caisse/compte', :agence);
INSERT INTO adsys_fonction VALUES (420, 190, 'Souscription des parts sociales par lot via fichier', :agence);
INSERT INTO adsys_fonction VALUES (421, 191, 'Chéquier à Imprimer', :agence);
INSERT INTO adsys_fonction VALUES (422, 192, 'Confirmation des chéquiers Imprimés', :agence);
INSERT INTO adsys_fonction VALUES (423, 193, 'Opération en déplacé', :agence);
INSERT INTO adsys_fonction VALUES (424, 194, 'Visualisation des opérations en déplacé', :agence);
INSERT INTO adsys_fonction VALUES (425, 201, 'Visualisation menu système', :agence);
INSERT INTO adsys_fonction VALUES (426, 205, 'Ouverture agence', :agence);
INSERT INTO adsys_fonction VALUES (427, 206, 'Fermeture agence', :agence);
INSERT INTO adsys_fonction VALUES (428, 210, 'Sauvegarde des données', :agence);
INSERT INTO adsys_fonction VALUES (429, 211, 'Consolidation de données', :agence);
INSERT INTO adsys_fonction VALUES (430, 212, 'Batch', :agence);
INSERT INTO adsys_fonction VALUES (431, 213, 'Traitements de nuit Multi-Agence', :agence);
INSERT INTO adsys_fonction VALUES (432, 215, 'Modification autre mot de passe', :agence);
INSERT INTO adsys_fonction VALUES (433, 230, 'Déconnexion autre code utilisateur', :agence);
INSERT INTO adsys_fonction VALUES (434, 235, 'Ajustement du solde d''un compte', :agence);
INSERT INTO adsys_fonction VALUES (435, 240, 'Gestion de la licence', :agence);
INSERT INTO adsys_fonction VALUES (436, 251, 'Visualisation menu paramétrage', :agence);
INSERT INTO adsys_fonction VALUES (437, 255, 'Visualisation gestion des profils', :agence);
INSERT INTO adsys_fonction VALUES (438, 256, 'Ajout d''un profil', :agence);
INSERT INTO adsys_fonction VALUES (439, 257, 'Consultation d''un profil', :agence);
INSERT INTO adsys_fonction VALUES (440, 258, 'Modification d''un profil', :agence);
INSERT INTO adsys_fonction VALUES (441, 259, 'Suppression d''un profil', :agence);
INSERT INTO adsys_fonction VALUES (442, 260, 'Modification mot de passe', :agence);
INSERT INTO adsys_fonction VALUES (443, 265, 'Visualisation menu gestion des utilisateurs', :agence);
INSERT INTO adsys_fonction VALUES (444, 270, 'Ajout utilisateur', :agence);
INSERT INTO adsys_fonction VALUES (445, 271, 'Consultation utilisateur', :agence);
INSERT INTO adsys_fonction VALUES (446, 272, 'Modification utilisateur', :agence);
INSERT INTO adsys_fonction VALUES (447, 273, 'Suppression utilisateur', :agence);
INSERT INTO adsys_fonction VALUES (448, 274, 'Visualisation des devises', :agence);
INSERT INTO adsys_fonction VALUES (449, 275, 'Ajout d''une devise', :agence);
INSERT INTO adsys_fonction VALUES (450, 276, 'Modification d''une devise', :agence);
INSERT INTO adsys_fonction VALUES (451, 277, 'Visualisation des positions de change', :agence);
INSERT INTO adsys_fonction VALUES (452, 281, 'Visualisation gestion des champs extras', :agence);
INSERT INTO adsys_fonction VALUES (453, 287, 'Visualisation menu gestion des codes utilisateurs', :agence);
INSERT INTO adsys_fonction VALUES (454, 288, 'Ajout login', :agence);
INSERT INTO adsys_fonction VALUES (455, 289, 'Consultation login', :agence);
INSERT INTO adsys_fonction VALUES (456, 290, 'Modification login', :agence);
INSERT INTO adsys_fonction VALUES (457, 291, 'Suppression login', :agence);
INSERT INTO adsys_fonction VALUES (458, 292, 'Visualisation gestion des tables de paramétrage', :agence);
INSERT INTO adsys_fonction VALUES (459, 293, 'Consultation des tables de paramétrage', :agence);
INSERT INTO adsys_fonction VALUES (460, 294, 'Modification des tables de paramétrage', :agence);
INSERT INTO adsys_fonction VALUES (461, 295, 'Ajout dans tables de paramétrage', :agence);
INSERT INTO adsys_fonction VALUES (462, 296, 'Visualisation jours ouvrables', :agence);
INSERT INTO adsys_fonction VALUES (463, 297, 'Modification des profils associés aux codes utilisateurs', :agence);
INSERT INTO adsys_fonction VALUES (464, 298, 'Suppression dans table de paramétrage', :agence);
INSERT INTO adsys_fonction VALUES (465, 299, 'Modification de frais et commisions', :agence);
INSERT INTO adsys_fonction VALUES (466, 300, 'Gestion de Jasper report ', :agence);
INSERT INTO adsys_fonction VALUES (467, 301, 'Visualisation du menu rapport', :agence);
INSERT INTO adsys_fonction VALUES (468, 310, 'Rapports client', :agence);
INSERT INTO adsys_fonction VALUES (469, 330, 'Rapports épargne', :agence);
INSERT INTO adsys_fonction VALUES (470, 350, 'Rapports crédit', :agence);
INSERT INTO adsys_fonction VALUES (471, 370, 'Rapports agence', :agence);
INSERT INTO adsys_fonction VALUES (472, 380, 'Rapports externe ', :agence);
INSERT INTO adsys_fonction VALUES (473, 390, 'Simulation échéancier', :agence);
INSERT INTO adsys_fonction VALUES (474, 399, 'Visualisation dernier rapport', :agence);
INSERT INTO adsys_fonction VALUES (475, 401, 'Visualisation du menu comptabilité', :agence);
INSERT INTO adsys_fonction VALUES (476, 410, 'Gestion du plan comptable', :agence);
INSERT INTO adsys_fonction VALUES (477, 420, 'Gestion des opérations comptables', :agence);
INSERT INTO adsys_fonction VALUES (478, 430, 'Rapports', :agence);
INSERT INTO adsys_fonction VALUES (479, 431, 'Traitement des transactions FERLO', :agence);
INSERT INTO adsys_fonction VALUES (480, 432, 'Dotation aux provisions crédits ', :agence);
INSERT INTO adsys_fonction VALUES (481, 433, 'Modification provisions crédits ', :agence);
INSERT INTO adsys_fonction VALUES (482, 440, 'Gestion des exercices', :agence);
INSERT INTO adsys_fonction VALUES (483, 441, 'Consultation des exercices', :agence);
INSERT INTO adsys_fonction VALUES (484, 442, 'Cloture d''un exercice', :agence);
INSERT INTO adsys_fonction VALUES (485, 443, 'Modification d''un exercice', :agence);
INSERT INTO adsys_fonction VALUES (486, 444, 'Clôture périodique', :agence);
INSERT INTO adsys_fonction VALUES (487, 450, 'Gestion des journaux comptables', :agence);
INSERT INTO adsys_fonction VALUES (488, 451, 'Consultation des journaux comptables', :agence);
INSERT INTO adsys_fonction VALUES (489, 452, 'Modification des journaux comptables', :agence);
INSERT INTO adsys_fonction VALUES (490, 453, 'Suppression des journaux comptables', :agence);
INSERT INTO adsys_fonction VALUES (491, 454, 'Creation d''un journal comptable', :agence);
INSERT INTO adsys_fonction VALUES (492, 455, 'Saisie des Opérations', :agence);
INSERT INTO adsys_fonction VALUES (493, 456, 'Ajout compte de contrepartie', :agence);
INSERT INTO adsys_fonction VALUES (494, 470, 'Saisie écritures utilisateurs', :agence);
INSERT INTO adsys_fonction VALUES (495, 471, 'Validation écritures utilisateurs', :agence);
INSERT INTO adsys_fonction VALUES (496, 472, 'Gestion des opérations diverses de caisse/compte', :agence);
INSERT INTO adsys_fonction VALUES (497, 473, 'Passage des opérations siège/agence', :agence);
INSERT INTO adsys_fonction VALUES (498, 474, 'Annulation des opérations réciproques', :agence);
INSERT INTO adsys_fonction VALUES (499, 475, 'Radiation crédit', :agence);
INSERT INTO adsys_fonction VALUES (500, 476, 'Déclarations de tva', :agence);
INSERT INTO adsys_fonction VALUES (501, 477, 'Suppression de compte comptable', :agence);
INSERT INTO adsys_fonction VALUES (502, 478, 'Gestion des écritures libres', :agence);
INSERT INTO adsys_fonction VALUES (503, 479, 'Mouvementer un compte interne à une date antérieure ', :agence);
INSERT INTO adsys_fonction VALUES (504, 500, 'Reprise des données comptes de base', :agence);
INSERT INTO adsys_fonction VALUES (505, 501, 'Reprise de comptes épargne existants', :agence);
INSERT INTO adsys_fonction VALUES (506, 502, 'Reprise de comptes PS existants', :agence);
INSERT INTO adsys_fonction VALUES (507, 503, 'Reprise d''un crédit existant', :agence);
INSERT INTO adsys_fonction VALUES (508, 504, 'Reprise du bilan d''ouverture', :agence);
INSERT INTO adsys_fonction VALUES (509, 510, 'Régularisation suite erreur logicielle', :agence);
INSERT INTO adsys_fonction VALUES (510, 511, 'Régularisation suite erreur utilisateur', :agence);