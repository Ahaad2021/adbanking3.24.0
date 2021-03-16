<?php
/*
   TABLES SYSTEMES

   Conventions :
   - Les noms commencent avec 'adsys'
   - La valeur 0 est réservée pour "[aucun]"
*/

require_once('lib/misc/divers.php');
require_once('lib/dbProcedures/agence.php');

$adsys = array();

//Types de périodicités de remboursement
$adsys["adsys_type_periodicite"][1] = _("Mensuelle");
$adsys["adsys_type_periodicite"][2] = _("Quinzaine");
$adsys["adsys_type_periodicite"][3] = _("Trimestrielle");
$adsys["adsys_type_periodicite"][4] = _("Semestrielle");
$adsys["adsys_type_periodicite"][5] = _("Annuelle");
$adsys["adsys_type_periodicite"][6] = _("En une fois");
$adsys["adsys_type_periodicite"][7] = _("Tous les 2 mois");
$adsys["adsys_type_periodicite"][8] = _("Hebdomadaire");

//Types de durée d'un crédit
$adsys["adsys_type_duree_credit"][1] = _("Mois");
$adsys["adsys_type_duree_credit"][2] = _("Semaines");

//Période de prélévement frais dossier
$adsys["adsys_prelev_frais_doss"][1] = _("Lors de la mise en place");
$adsys["adsys_prelev_frais_doss"][2] = _("Lors du déboursement");

//type de numérotation de compte
$adsys["adsys_type_numerotation_compte"][1] = _("Standard");
$adsys["adsys_type_numerotation_compte"][2] = _("Rép Dém du Congo");

$adsys["adsys_type_numerotation_compte"][3] = _("Rwanda");
$adsys["adsys_type_numerotation_compte"][4] = _("Avec code antenne et numéro bureau");

//Type de pénalités en pourcentage
$adsys["adsys_type_pen_pourc_dcr"][1] = _("Sur solde capital restant du");
$adsys["adsys_type_pen_pourc_dcr"][2] = _("Sur échéance capital et intérets");

//Durée (en mois) des périodes de remboursement
$adsys["adsys_duree_periodicite"][1] = 1;
$adsys["adsys_duree_periodicite"][2] = 0.5; // Quinzaine
$adsys["adsys_duree_periodicite"][3] = 3;
$adsys["adsys_duree_periodicite"][4] = 6;
$adsys["adsys_duree_periodicite"][5] = 12;
$adsys["adsys_duree_periodicite"][6] = 0; // N/A
$adsys["adsys_duree_periodicite"][7] = 2;
//durée en semaine pour le type de durée hebdomadaire
$adsys["adsys_duree_periodicite"][8] = 1;

//Durée (en jours) des périodes de remboursement
$adsys["adsys_duree_jours_periodicite"][1] = 30;
$adsys["adsys_duree_jours_periodicite"][2] = 15; // Quinzaine
$adsys["adsys_duree_jours_periodicite"][3] = 90;
$adsys["adsys_duree_jours_periodicite"][4] = 180;
$adsys["adsys_duree_jours_periodicite"][5] = getBaseTauxCalculInteret();
$adsys["adsys_duree_jours_periodicite"][6] = 0; // N/A
$adsys["adsys_duree_jours_periodicite"][7] = 60;
//durée en semaine pour le type de durée hebdomadaire
$adsys["adsys_duree_jours_periodicite"][8] = 7;

//Termes
$adsys["adsys_termes_credit"][1]['libel'] = _("Court terme");
$adsys["adsys_termes_credit"][1]['mois_min'] = 0;
$adsys["adsys_termes_credit"][1]['mois_max'] = 12;
$adsys["adsys_termes_credit"][2]['libel'] = _("Moyen terme");
$adsys["adsys_termes_credit"][2]['mois_min'] = 13;
$adsys["adsys_termes_credit"][2]['mois_max'] = 36;
$adsys["adsys_termes_credit"][3]['libel'] = _("Long terme");
$adsys["adsys_termes_credit"][3]['mois_min'] = 37;
$adsys["adsys_termes_credit"][3]['mois_max'] = 0;

//Perception des frais ,commissions et assurance
$adsys["adsys_percep_frais_com_ass"][1] = _("avant déboursement");
$adsys["adsys_percep_frais_com_ass"][2] = _("après déboursement");

//Etats d'un dossier de crédit
$adsys["adsys_etat_dossier_credit"][1] = _("En attente de décision");
$adsys["adsys_etat_dossier_credit"][2] = _("Accepté");
$adsys["adsys_etat_dossier_credit"][3] = _("Rejeté");
$adsys["adsys_etat_dossier_credit"][4] = _("Annulé");
$adsys["adsys_etat_dossier_credit"][5] = _("Fonds déboursés");
$adsys["adsys_etat_dossier_credit"][6] = _("Crédit soldé");
$adsys["adsys_etat_dossier_credit"][7] = _("En attente de rééchel/Moratoire");
$adsys["adsys_etat_dossier_credit"][9] = _("Passé en perte");
$adsys["adsys_etat_dossier_credit"][10] = _("En cours de reprise");
$adsys["adsys_etat_dossier_credit"][11] = _("Transfert client");
$adsys["adsys_etat_dossier_credit"][12] = _("Supprimé");
$adsys["adsys_etat_dossier_credit"][13] = _("En déboursement progressif");
$adsys["adsys_etat_dossier_credit"][14] = _("En attente approbation modification date");
$adsys["adsys_etat_dossier_credit"][15] = _("En attente approbation raccourcissement durée");
$adsys["adsys_etat_dossier_credit"][16] = _("En attente approbation differé échéance");

//Motif de l'etat du dossier de crédit
$adsys["adsys_motif_etat_dossier_credit"][1] = _("Dossier incomplet");
$adsys["adsys_motif_etat_dossier_credit"][2] = _("Garanties insuffisantes");
$adsys["adsys_motif_etat_dossier_credit"][3] = _("Durée minimum entre deux crédits non atteinte");
$adsys["adsys_motif_etat_dossier_credit"][4] = _("Activité non viable");
$adsys["adsys_motif_etat_dossier_credit"][5] = _("Antécédents douteux");
$adsys["adsys_motif_etat_dossier_credit"][6] = _("Fonds de crédit insuffisants");
$adsys["adsys_motif_etat_dossier_credit"][7] = _("Obtention d'un crédit dans une autre institution");
$adsys["adsys_motif_etat_dossier_credit"][8] = _("Désir de ne plus mener l'activité");
$adsys["adsys_motif_etat_dossier_credit"][9] = _("Possibilité de financement sur fonds propres");
$adsys["adsys_motif_etat_dossier_credit"][10] = _("Conditions de crédit non-adaptées");
$adsys["adsys_motif_etat_dossier_credit"][11] = _("Autre");

// Motifs de l'état d'un dossier de crédit en fonction de son état
// Ex: $adsys["lien_motif_etat"][3] = array(1,2) signifie que les motifs 1 et 2 de la table adsys_motif_etat_dossier_credit se rapportent à l'état 3: rejeté.
$adsys["adsys_lien_motif_etat_credit"][1] = array();
$adsys["adsys_lien_motif_etat_credit"][2] = array();
$adsys["adsys_lien_motif_etat_credit"][3] = array(1,2,3,4,5,6,11);
$adsys["adsys_lien_motif_etat_credit"][4] = array(7,8,9,10,11);

//Types de garanties
$adsys["adsys_type_garantie"][1] = _("Numéraire");
$adsys["adsys_type_garantie"][2] = _("Matériel");

// Etat des garanties
$adsys["adsys_etat_gar"][1] = _("En cours de mobilisation");
$adsys["adsys_etat_gar"][2] = _("Prête");
$adsys["adsys_etat_gar"][3] = _("Mobilisée");
$adsys["adsys_etat_gar"][4] = _("Restituée");
$adsys["adsys_etat_gar"][5] = _("Réalisée");


//Type_tranfert part sociale_361
$adsys["adsys_type_transfert_ps"][1] = _("Transfert vers un autre compte de PS");
$adsys["adsys_type_transfert_ps"][2] = _("Transfert vers compte courant");

//Etat de transfert part sociale_361
$adsys["adsys_etat_transfert_parts_sociale "][1] = _("En attente de décision");
$adsys["adsys_etat_transfert_parts_sociale "][2] = _("Transfert approuvé");
$adsys["adsys_etat_transfert_parts_sociale "][3] = _("Transfert rejeté");


//Etat civil
$adsys["adsys_etat_civil"][1] = _("célibataire");
$adsys["adsys_etat_civil"][2] = _("marié(e)");
$adsys["adsys_etat_civil"][3] = _("veuf/veuve");
$adsys["adsys_etat_civil"][4] = _("divorcé(e)");

//Sexe
$adsys["adsys_sexe"][1] = _("Masculin");
$adsys["adsys_sexe"][2] = _("Féminin");

//Qualité client
$adsys["adsys_qualite_client"][1] = _("Auxiliaire");
$adsys["adsys_qualite_client"][2] = _("Ordinaire");
$adsys["adsys_qualite_client"][3] = _("Employé");
$adsys["adsys_qualite_client"][4] = _("Dirigeant");

//Tranche d'âge client
$adsys["adsys_tranche_age_client"][1] = _("00 - 15");
$adsys["adsys_tranche_age_client"][2] = _("16 - 25");
$adsys["adsys_tranche_age_client"][3] = _("26 - 35");
$adsys["adsys_tranche_age_client"][4] = _("36 - 45");
$adsys["adsys_tranche_age_client"][5] = _("46 - 55");
$adsys["adsys_tranche_age_client"][6] = _("56 - 65");
$adsys["adsys_tranche_age_client"][7] = _("66 - 75");
$adsys["adsys_tranche_age_client"][8] = _("76 - 85");
$adsys["adsys_tranche_age_client"][9] = _("86 - 95");
$adsys["adsys_tranche_age_client"][10] = _("96 - 195");

//Etat d'un compte d'épargne
$adsys["adsys_etat_cpt_epargne"][1] = _("Ouvert");
$adsys["adsys_etat_cpt_epargne"][2] = _("Fermé");
$adsys["adsys_etat_cpt_epargne"][3] = _("Bloqué");
$adsys["adsys_etat_cpt_epargne"][4] = _("Dormant");
$adsys["adsys_etat_cpt_epargne"][5] = _("Attente fermeture manuelle");
$adsys["adsys_etat_cpt_epargne"][6] = _("Dépôt bloqué");
$adsys["adsys_etat_cpt_epargne"][7] = _("Retrait bloqué");
$adsys["adsys_etat_cpt_epargne"][8] = _("Inactif");

//Compte de versement des interets : simulation echeancier DAT
$adsys["adsys_type_cpt_vers_int"][1] = _("Compte lui-meme");
$adsys["adsys_type_cpt_vers_int"][2] = _("Autre Compte");

//Mode de calcul des intérêts sur le crédit
$adsys["adsys_mode_calc_int_credit"][1] = _("Constant");
$adsys["adsys_mode_calc_int_credit"][2] = _("Dégressif prédéfini");
$adsys["adsys_mode_calc_int_credit"][3] = _("Dégressif variable");
$adsys["adsys_mode_calc_int_credit"][4] = _("Dégressif capital constant");
$adsys["adsys_mode_calc_int_credit"][5] = _("Ligne de crédit");

//Mode de perception des intérêts
$adsys["adsys_mode_perception_int"][1] = _("Au début");
$adsys["adsys_mode_perception_int"][2] = _("Inclus dans les remboursements");
$adsys["adsys_mode_perception_int"][3] = _("A la fin");

//Statut juridique
$adsys["adsys_stat_jur"][1] = _("Personne physique");
$adsys["adsys_stat_jur"][2] = _("Personne morale");
$adsys["adsys_stat_jur"][3] = _("Groupe informel");
$adsys["adsys_stat_jur"][4] = _("Groupe solidaire");

//Types de relations
$adsys["adsys_type_relation"][1] = _("Frère/Soeur");
$adsys["adsys_type_relation"][2] = _("Père/mère");
$adsys["adsys_type_relation"][3] = _("Ayant droit");
$adsys["adsys_type_relation"][4] = _("Fils/Fille");
$adsys["adsys_type_relation"][5] = _("Tuteur/Tutrice");
$adsys["adsys_type_relation"][7] = _("Conjoint");
$adsys["adsys_type_relation"][8] = _("Contact");

//Etat des journaux
$adsys["adsys_etat_journal"][1] = _("Ouvert");
$adsys["adsys_etat_journal"][2] = _("Fermé");

//Etat des comptes comptables
$adsys["adsys_etat_cpte_compta"][1] = _("Ouvert");
$adsys["adsys_etat_cpte_compta"][2] = _("Fermé");

//Sens naturel d'un compte de compta
$adsys["adsys_cpte_compta_sens"][1] = _("Débiteur");
$adsys["adsys_cpte_compta_sens"][2] = _("Créditeur");
$adsys["adsys_cpte_compta_sens"][3] = _("Mixte");

//Etats d'un exercice comptable
$adsys["adsys_etat_exo_compta"][1] = _("Ouvert");
$adsys["adsys_etat_exo_compta"][2] = _("En cours de cloture");
$adsys["adsys_etat_exo_compta"][3] = _("Cloture");

// Categorie personne morale
$adsys["adsys_categorie_pm"][1] = _("Banque");
$adsys["adsys_categorie_pm"][2] = _("Institution financière");
$adsys["adsys_categorie_pm"][3] = _("Organisme public");
$adsys["adsys_categorie_pm"][4] = _("Entreprise privée");
$adsys["adsys_categorie_pm"][5] = _("Association sans but lucratif");

// Catégorie de groupe solidaire
$adsys["adsys_categorie_gs"][1] = _("Groupe solidaire à dossier unique");
$adsys["adsys_categorie_gs"][2] = _("Groupe solidaire à dossiers multiples");

//Catégorie des comptes
$adsys["adsys_categorie_compte"][0] = _("Libre");
$adsys["adsys_categorie_compte"][1] = _("Compte d'epargne");
$adsys["adsys_categorie_compte"][2] = _("Compte de credit");
$adsys["adsys_categorie_compte"][3] = _("Compte coffre-fort");
$adsys["adsys_categorie_compte"][4] = _("Compte guichet");
$adsys["adsys_categorie_compte"][5] = _("Compte banque");
$adsys["adsys_categorie_compte"][6] = _("Compte d'intérêts sur crédits");
$adsys["adsys_categorie_compte"][7] = _("Compte de penalités sur crédits");
$adsys["adsys_categorie_compte"][8] = _("Compte de garantie");
$adsys["adsys_categorie_compte"][9] = _("Compte de parts sociales");
$adsys["adsys_categorie_compte"][10] = _("Compte d'intérêts sur épargne");
$adsys["adsys_categorie_compte"][11] = "Compte de frais sur crédits";
$adsys["adsys_categorie_compte"][12] = _("Compte de position de change");
$adsys["adsys_categorie_compte"][13] = _("Compte de contrevaleur de la position de change");
$adsys["adsys_categorie_compte"][14] = _("Compte d'attente de variation de taux - crédit");
$adsys["adsys_categorie_compte"][15] = _("Compte d'attente de variation de taux - débit");
$adsys["adsys_categorie_compte"][16] = _("Compte de produit par commissions de change");
$adsys["adsys_categorie_compte"][17] = _("Compte de produit par jeu sur le taux de change");
$adsys["adsys_categorie_compte"][18] = _("Compte travelers cheque");
$adsys["adsys_categorie_compte"][19] = _("Compte d'attente débiteur");
$adsys["adsys_categorie_compte"][20] = _("Compte d'attente créditeur");
$adsys["adsys_categorie_compte"][21] = _("Compte de vente à perte de devise");
$adsys["adsys_categorie_compte"][22] = "Compte de chèque certifié";
$adsys["adsys_categorie_compte"][23] = "Compte de commission chèque certifié";
$adsys["adsys_categorie_compte"][24] = "Compte de frais sur épargne";
$adsys["adsys_categorie_compte"][25] = _("Compte d'impot");
$adsys["adsys_categorie_compte"][26] = _("Compte des intérêts à payer sur comptes d'épargne");
$adsys["adsys_categorie_compte"][27] = _("Compte des intérêts à recevoir sur dossiers de crédit");
$adsys["adsys_categorie_compte"][28] = _("Compte tiers");
$adsys["adsys_categorie_compte"][29] = _("Compte Agency Banking");


// Périodicité des ordres permanents
$adsys["adsys_periodicite_ordre_perm"][1] = _("Journalier");
$adsys["adsys_periodicite_ordre_perm"][2] = _("Hebdomadaire");
$adsys["adsys_periodicite_ordre_perm"][3] = _("Mensuel");
$adsys["adsys_periodicite_ordre_perm"][4] = _("Annuel");

// Type de taxes gérées par adbanking
$adsys["adsys_type_taxe"][1] = _("Taxe sur la valeur ajoutée");
$adsys["adsys_type_taxe"][2] = _("Taxe sur impôt mobilier");

// Type d'opérations siège/agence
$adsys["adsys_op_siege_agence"][600] = _("Dépôt au siège"); // opération dans une agence
$adsys["adsys_op_siege_agence"][601] = _("Dépôt des agences"); // opération effectuée au siège
$adsys["adsys_op_siege_agence"][602] = _("Emprunt auprès du siège");  // opération dans une agence
$adsys["adsys_op_siege_agence"][603] = _("Prêts aux agences"); // opération effectuée au siège
$adsys["adsys_op_siege_agence"][604] = _("Titres de participation");  // opération dans une agence
$adsys["adsys_op_siege_agence"][605] = _("Parts sociales des agences"); // opération effectuée au siège
$adsys["adsys_op_siege_agence"][606] = _("Participation aux charges du réseau"); // opération dans une agence
$adsys["adsys_op_siege_agence"][607] = _("Refacturation charges aux agences"); // opération effectuée au siège
$adsys["adsys_op_siege_agence"][608] = _("Retrait au siège"); // opération dans une agence
$adsys["adsys_op_siege_agence"][609] = _("Retrait des agences"); // opération effectuée au siège
$adsys["adsys_op_siege_agence"][610] = _("Remboursement crédit du siège");  // opération dans une agence
$adsys["adsys_op_siege_agence"][611] = _("Remboursement crédit aux agences");  // opération effectuée au siège
$adsys["adsys_op_siege_agence"][612] = _("Récupération titres de participation");  // opération dans une agence
$adsys["adsys_op_siege_agence"][613] = _("Remboursement titres de participation");  // opération effectuée au siège


//Fonctions systèmes
/* Par convention les numéros sont attribués de la manière suivante :
	0	: réservé, accès pour tout le monde
	1-> 50   : fonctions du module client
	51-> 100 : fonctions du module épargne
	101-> 150: fonctions du module crédit
	151-> 200: fonctions du module guichet
	201-> 250: fonctions du module système
	251-> 300: fonctions du module paramétrage
	301-> 400: fonctions du module rapport
	401-> 499: fonctions du module comptabilité et consolidation
	500-> 599: fonctions internes (qui ne sont pas accessibles à l'utilisateur)
	600-> 699: fonctions du module ligne de crédit
  700-> 750: fonctions du module budget
  751-> 799: fonctions du module Agency Banking
  800-> 899: fonctions du module budget supplement(la limite a été depassée)
  900-> 950 : fonction du module Mobile Lending
*/
//Module client
$adsys["adsys_fonction_systeme"][3] = _("Sélection d'un client");
$adsys["adsys_fonction_systeme"][5] = _("Visualisation menu gestion client");
$adsys["adsys_fonction_systeme"][10] = _("Modification client");
$adsys["adsys_fonction_systeme"][11] = _("Gestion des relations");
$adsys["adsys_fonction_systeme"][12] = _("Gestion des abonnements"); // API
if (isAtmModule()) {
    $adsys["adsys_fonction_systeme"][13] = _("Gestion des abonnements ATM"); // ATM
}
$adsys["adsys_fonction_systeme"][15] = _("Défection client");
$adsys["adsys_fonction_systeme"][16] = _("Finalisation défection client décédé");
$adsys["adsys_fonction_systeme"][17] = _("Simulation défection client");
$adsys["adsys_fonction_systeme"][19] = _("Faire jouer l'assurance");
$adsys["adsys_fonction_systeme"][20] = _("Souscription parts sociales");
$adsys["adsys_fonction_systeme"][21] = _("Visualisation Menu Transfert parts sociales");
$adsys["adsys_fonction_systeme"][22] = _("Demande transfert parts sociales");
$adsys["adsys_fonction_systeme"][23] = _("Approbation transfert parts sociales");
//$adsys["adsys_fonction_systeme"][24] = _("Rejet transfert parts sociales");
$adsys["adsys_fonction_systeme"][25] = _("Consultation client");
$adsys["adsys_fonction_systeme"][26] = _("Consultation compte de parts sociales");
$adsys["adsys_fonction_systeme"][27] = _("Gestion des parts sociales");
$adsys["adsys_fonction_systeme"][28] = _("Libération parts sociales");
$adsys["adsys_fonction_systeme"][30] = _("Ajout client");
$adsys["adsys_fonction_systeme"][31] = _("Perception frais adhésion");
$adsys["adsys_fonction_systeme"][32] = _("Commission agent sur creation nouveau client");
//module documents
$adsys["adsys_fonction_systeme"][40] = _("Visualisation menu documents");
$adsys["adsys_fonction_systeme"][41] = _("Commande chèquier");
$adsys["adsys_fonction_systeme"][42] = _("Retrait chèquier");
$adsys["adsys_fonction_systeme"][43] = _("Extraits de compte");
$adsys["adsys_fonction_systeme"][44] = _("Situation globale client");
$adsys["adsys_fonction_systeme"][45] = _("Mise en opposition chèque / chèquier");
if(isAtmModule()) {
    $adsys["adsys_fonction_systeme"][46] = _("Commande carte ATM"); // Module bswtich ATM
    $adsys["adsys_fonction_systeme"][47] = _("Retrait carte ATM"); // Module bswtich ATM
    $adsys["adsys_fonction_systeme"][48] = _("Gestion des cartes suspendues/désactivée"); // Module bswtich ATM
}
//module épargne
$adsys["adsys_fonction_systeme"][51] = _("Visualisation menu épargne");
$adsys["adsys_fonction_systeme"][52] = _("Débloquer les montants");
$adsys["adsys_fonction_systeme"][53] = _("Ouverture compte");
$adsys["adsys_fonction_systeme"][54] = _("Clôture compte");
$adsys["adsys_fonction_systeme"][55] = _("Simulation arrêté compte");
//Modification d'un ordre permanent_454(1)
$adsys["adsys_fonction_systeme"][56] = _("Ajout d'un ordre permanent");
$adsys["adsys_fonction_systeme"][57] = _("Modification d'un ordre permanent");

// #537 : modifcation parametres epargne
$adsys["adsys_fonction_systeme"][58] = _("Modification des paramètres d’épargne");

// Ouverture compte/ordres permanent meme si un client est à l'etat 1
$adsys["adsys_fonction_systeme"][59] = _("Autoriser ouverture des comptes et ordres permanents sans frais?");

// Annulation Retrait et Dépôt
$adsys["adsys_fonction_systeme"][60] = _("Gestion Annulation Retrait et Dépôt");
$adsys["adsys_fonction_systeme"][61] = _("Demande annulation retrait / dépôt");
$adsys["adsys_fonction_systeme"][62] = _("Approbation demande annulation retrait / dépôt");
$adsys["adsys_fonction_systeme"][63] = _("Effectuer annulation retrait / dépôt");
$adsys["adsys_fonction_systeme"][65] = _("Annulation Retrait");
$adsys["adsys_fonction_systeme"][66] = _("Annulation Dépôt");

//ticket 696 : nouveau fonction epargne -> simulation produit epargne
$adsys["adsys_fonction_systeme"][68] = _("Simulation échéancier"); //ticket 696 : nouveau fonction epargne

$adsys["adsys_fonction_systeme"][70] = _("Retrait");
$adsys["adsys_fonction_systeme"][71] = _("Demande autorisation retrait");
$adsys["adsys_fonction_systeme"][157] = _("Autorisation de retrait");
$adsys["adsys_fonction_systeme"][73] = _("Refus retrait");
$adsys["adsys_fonction_systeme"][74] = _("Paiement retrait autorisé");
$adsys["adsys_fonction_systeme"][94] = _("Refus transfert");
$adsys["adsys_fonction_systeme"][100] = _("Paiement transfert autorisé");
$adsys["adsys_fonction_systeme"][75] = _("Dépôt");
$adsys["adsys_fonction_systeme"][76] = _("Transfert compte");
$adsys["adsys_fonction_systeme"][77] = _("Transfert compte API"); // API
$adsys["adsys_fonction_systeme"][78] = _("Prolongation DAT");
$adsys["adsys_fonction_systeme"][79] = _("Ordres Permanents");
$adsys["adsys_fonction_systeme"][80] = _("Consultation des comptes");
$adsys["adsys_fonction_systeme"][81] = _("Recharge Carte Ferlo par Compte epargne");
$adsys["adsys_fonction_systeme"][85] = _("Retrait express");
$adsys["adsys_fonction_systeme"][86] = _("Dépôt express");
$adsys["adsys_fonction_systeme"][87] = _("Frais en attente");
$adsys["adsys_fonction_systeme"][88] = _("Modification du compte");
$adsys["adsys_fonction_systeme"][89] = _("Bloquer / débloquer un compte");
$adsys["adsys_fonction_systeme"][90] = _("Gestion des mandats");
$adsys["adsys_fonction_systeme"][91] = _("Activez les comptes dormants / inactifs");

// Vérification si Multi-Agence
if(isset($global_id_agence) && isMultiAgence())
{
$adsys["adsys_fonction_systeme"][92] = _("Retrait en déplacé");
$adsys["adsys_fonction_systeme"][93] = _("Dépôt en déplacé");
$adsys["adsys_fonction_systeme"][64] = _("Paiement retrait en déplacé autorisé");
}

$adsys["adsys_fonction_systeme"][95] = _("Ajout d'un mandat");
$adsys["adsys_fonction_systeme"][96] = _("Modification d'un mandat");

$adsys["adsys_fonction_systeme"][97] = _("Transfert vers eWallet"); // eWallet
$adsys["adsys_fonction_systeme"][98] = _("Dépôt eWallet"); // transfert eWallet vers banque
$adsys["adsys_fonction_systeme"][99] = _("Retrait eWallet"); // transfert eWallet vers banque
$adsys["adsys_fonction_systeme"][67] = _("Annulation ecriture brouillard"); // annulation depot ewallet brouillard

//module crédit
$adsys["adsys_fonction_systeme"][101] = _("Visualisation menu crédit");
$adsys["adsys_fonction_systeme"][105] = _("Création dossier de crédit");
$adsys["adsys_fonction_systeme"][106] = _("Flexibilité produit de crédit");
$adsys["adsys_fonction_systeme"][110] = _("Approbation dossier de crédit");
$adsys["adsys_fonction_systeme"][115] = _("Rejet dossier de crédit");
$adsys["adsys_fonction_systeme"][120] = _("Annulation dossier de crédit");
$adsys["adsys_fonction_systeme"][125] = _("Déboursement des fonds");
$adsys["adsys_fonction_systeme"][126] = _("Annulation déboursement progressif");
$adsys["adsys_fonction_systeme"][129] = _("Correction dossier de crédit");
$adsys["adsys_fonction_systeme"][130] = _("Modification dossier de crédit");
$adsys["adsys_fonction_systeme"][131] = _("Suspension / ajustement des pénalités");
$adsys["adsys_fonction_systeme"][132] = _("Abattement des intérêts et pénalités");
$adsys["adsys_fonction_systeme"][133] = _("Traitement pour remboursement anticipé");
$adsys["adsys_fonction_systeme"][135] = _("Simulation échéancier");
$adsys["adsys_fonction_systeme"][140] = _("Consultation dossier de crédit");
$adsys["adsys_fonction_systeme"][145] = _("Demande Rééchelonement/moratoire");
$adsys["adsys_fonction_systeme"][146] = _("Rééchelonnement/moratoire");
$adsys["adsys_fonction_systeme"][147] = _("Remboursement crédit");
$adsys["adsys_fonction_systeme"][148] = _("Réalisation garanties");
$adsys["adsys_fonction_systeme"][149] = _("Modification date remboursement");

$adsys["adsys_fonction_systeme"][136] = _("Modification de l'échéancier de crédit");
$adsys["adsys_fonction_systeme"][141] = _("Demande modification de la date de remboursement");
$adsys["adsys_fonction_systeme"][142] = _("Approbation modification de la date de remboursement");
$adsys["adsys_fonction_systeme"][143] = _("Demande raccourcissement de la durée du crédit");
$adsys["adsys_fonction_systeme"][144] = _("Approbation raccourcissement de la durée du crédit");
$adsys["adsys_fonction_systeme"][150] = _("Annulation raccourcissement de la durée du crédit");
$adsys["adsys_fonction_systeme"][103] = _("Demande de differé en échéances");
$adsys["adsys_fonction_systeme"][104] = _("Approbation differé en échéances");
$adsys["adsys_fonction_systeme"][107] = _("Annulation differé en échéances");
$adsys["adsys_fonction_systeme"][108] = _("Rejet differé en échéances");

$adsys["adsys_fonction_systeme"][137] = _("Demande rééchelonnement crédits 'En une fois'");
$adsys["adsys_fonction_systeme"][138] = _("Approbation rééchelonnement crédits 'En une fois'");
$adsys["adsys_fonction_systeme"][139] = _("Annulation rééchelonnement crédits 'En une fois'");

//module ligne de crédit
$adsys["adsys_fonction_systeme"][102] = _("Gestion ligne de crédit");

$adsys["adsys_fonction_systeme"][600] = _("Mise en place ligne de crédit");
$adsys["adsys_fonction_systeme"][601] = _("Approbation ligne de crédit");
$adsys["adsys_fonction_systeme"][602] = _("Rejet ligne de crédit");
$adsys["adsys_fonction_systeme"][603] = _("Annulation ligne de crédit");
$adsys["adsys_fonction_systeme"][604] = _("Déboursement fonds ligne de crédit");
$adsys["adsys_fonction_systeme"][605] = _("Modification ligne de crédit");
$adsys["adsys_fonction_systeme"][606] = _("Consultation ligne de crédit");
$adsys["adsys_fonction_systeme"][607] = _("Remboursement ligne de crédit");
$adsys["adsys_fonction_systeme"][608] = _("Réalisation garanties ligne de crédit");
$adsys["adsys_fonction_systeme"][609] = _("Correction dossier ligne de crédit");
$adsys["adsys_fonction_systeme"][610] = _("Clôturer la ligne de crédit");

//module budget
$adsys["adsys_fonction_systeme"][700] = _("Gestion du Budget");
$adsys["adsys_fonction_systeme"][701] = _("Gestion des tables de correspondance");
$adsys["adsys_fonction_systeme"][702] = _("Parametrer la table de correspondance");
$adsys["adsys_fonction_systeme"][703] = _("Modifier la table de correspondance");
//$adsys["adsys_fonction_systeme"][704] = _("Annuler la table de correspondance");
$adsys["adsys_fonction_systeme"][705] = _("Mise en Place du Budget Annuel");
$adsys["adsys_fonction_systeme"][706] = _("Raffiner le Budget");
$adsys["adsys_fonction_systeme"][707] = _("Réviser le Budget");
$adsys["adsys_fonction_systeme"][708] = _("Valider le Budget");
$adsys["adsys_fonction_systeme"][709] = _("Valider le Raffinement du Budget");
$adsys["adsys_fonction_systeme"][710] = _("Valider la Révision du Budget");
//$adsys["adsys_fonction_systeme"][711] = _("Annuler le Budget");
$adsys["adsys_fonction_systeme"][712] = _("Visualisation du Budget");
$adsys["adsys_fonction_systeme"][713] = _("Visualisation des Comptes Comptables bloqués");
$adsys["adsys_fonction_systeme"][714] = _("Débloquer les Comptes Comptables");
$adsys["adsys_fonction_systeme"][715] = _("Rapports Budget");
$adsys["adsys_fonction_systeme"][716] = _("Mise en place Nouvelle(s) Ligne(s) Budgetaire(s)");
$adsys["adsys_fonction_systeme"][717] = _("Validation Nouvelle(s) Ligne(s) Budgetaire(s)");

if (isAgencyBanking()) {
// Module Agency Banking
  $adsys["adsys_fonction_systeme"][751] = _("Gestion Agency Banking");
  $adsys["adsys_fonction_systeme"][752] = _("Création des utilisateurs agents");
  $adsys["adsys_fonction_systeme"][753] = _("Création des codes utilisateurs agents");
  $adsys["adsys_fonction_systeme"][754] = _("Paramétrage des commissions");
  $adsys["adsys_fonction_systeme"][755] = _("Paramétrage des commissions pour l'institution");
  $adsys["adsys_fonction_systeme"][762] = _("Création client par agent");
  $adsys["adsys_fonction_systeme"][756] = _("Demande approvisionnement agent");
  $adsys["adsys_fonction_systeme"][757] = _("Vérification des demandes approvisionnements/transferts agents");
  $adsys["adsys_fonction_systeme"][758] = _("Approbation des demandes approvisionnements/transferts agents");
  $adsys["adsys_fonction_systeme"][759] = _("Demande de transfert de compte de flotte vers compte courant");
  $adsys["adsys_fonction_systeme"][760] = _("Paramétrage Agency Banking");
  $adsys["adsys_fonction_systeme"][761] = _("Paramétrage approvisionnement/transfert compte de flotte");
  $adsys["adsys_fonction_systeme"][763] = _("Dépôt via agent");
  $adsys["adsys_fonction_systeme"][764] = _("Retrait via agent");
  $adsys["adsys_fonction_systeme"][765] = _("Gestion annulation dépôts/retraits");
  $adsys["adsys_fonction_systeme"][766] = _("Demande annulation dépôts/retraits");
  $adsys["adsys_fonction_systeme"][767] = _("Approbation demande annulation dépôts/retraits via agent");
  $adsys["adsys_fonction_systeme"][768] = _("Effectuer demande annulation dépôts/retraits via agent");
  $adsys["adsys_fonction_systeme"][769] = _("Annulation dépôt via agent");
  $adsys["adsys_fonction_systeme"][770] = _("Annulation retrait via agent");
  $adsys["adsys_fonction_systeme"][771] = _("Visualisation des demandes d’approvisionnement/transfert");
  $adsys["adsys_fonction_systeme"][773] = _("Opération en déplacé via agent");
  $adsys["adsys_fonction_systeme"][774] = _("Retrait en déplacé via agent");
  $adsys["adsys_fonction_systeme"][775] = _("Dépôt en déplacée via agent");
  $adsys["adsys_fonction_systeme"][776] = _("Gestion profil agents");
  $adsys["adsys_fonction_systeme"][777] = _("Ajout profil agent");
  $adsys["adsys_fonction_systeme"][778] = _("Modification profil agent");
  $adsys["adsys_fonction_systeme"][779] = _("Consultation profil agent");
  $adsys["adsys_fonction_systeme"][780] = _("Supprimer profil agent");
  $adsys["adsys_fonction_systeme"][781] = _("Visualisation des transactions agents");
  $adsys["adsys_fonction_systeme"][782] = _("Approbation création client par agent");
  $adsys["adsys_fonction_systeme"][783] = _("Effectuer création client par agent");
  $adsys["adsys_fonction_systeme"][784] = _("Visualisation création client par agent");
  $adsys["adsys_fonction_systeme"][785] = _("Paramétrage impôt sur commission");
  $adsys["adsys_fonction_systeme"][786] = _("Commissions sur transaction Agency Banking");
  $adsys["adsys_fonction_systeme"][787] = _("Rapports agency banking");
  $adsys["adsys_fonction_systeme"][788] = _("Consultation d'un compte en déplacé via agent");
  $adsys["adsys_fonction_systeme"][789] = _("Consultation d'un compte via agent");
}
if (isMobileLending()) {
  $adsys["adsys_fonction_systeme"][900] = _("Gestion Mobile Lending");
  $adsys["adsys_fonction_systeme"][901] = _("Nouveaux clients Mobile Lending");
  $adsys["adsys_fonction_systeme"][902] = _("Dossiers Mobile Lending en attente");
  $adsys["adsys_fonction_systeme"][903] = _("Lancement du cron manuel");
  $adsys["adsys_fonction_systeme"][904] = _("Approbation dossier de crédit");
  $adsys["adsys_fonction_systeme"][905] = _("Déboursement dossier de crédit");
  $adsys["adsys_fonction_systeme"][906] = _("Rapport Mobile Lending");
}

// Fonction ATM
$adsys["adsys_fonction_systeme"][921] = _("Solde disponible"); // service ATM solde disponible
$adsys["adsys_fonction_systeme"][922] = _("Mini statement"); // service ATM mini statement
$adsys["adsys_fonction_systeme"][923] = _("Retrait ATM"); // service ATM retrait
$adsys["adsys_fonction_systeme"][924] = _("Revirement ATM"); // service ATM revirement


//Ordre de remboursement ligne de crédit
$adsys["adsys_ordre_remb_lcr"][1] = _("Frais -> Garantie -> pénalité -> intérêt -> capital");
$adsys["adsys_ordre_remb_lcr"][2] = _("Frais -> Garantie -> capital -> intérêt -> pénalité");
$adsys["adsys_ordre_remb_lcr"][3] = _("Frais -> Garantie -> intérêt -> capital -> pénalité");
$adsys["adsys_ordre_remb_lcr"][4] = _("Frais -> Garantie -> intérêt -> pénalité -> capital");
$adsys["adsys_ordre_remb_lcr"][5] = _("Frais -> Intérêt -> pénalité -> capital -> garantie");
$adsys["adsys_ordre_remb_lcr"][6] = _("Frais -> Intérêt -> capital -> pénalité -> garantie");
$adsys["adsys_ordre_remb_lcr"][7] = _("Frais -> Pénalité -> intérêt -> capital -> garantie");
$adsys["adsys_ordre_remb_lcr"][8] = _("Frais -> Capital -> intérêt -> pénalité -> garantie");


// Événements dans le cycle de vie d'une ligne de crédit
$adsys["adsys_type_evnt_ligne_credit"][1] = _("Approbation");
$adsys["adsys_type_evnt_ligne_credit"][2] = _("Déboursement");
$adsys["adsys_type_evnt_ligne_credit"][3] = _("Remboursement");
$adsys["adsys_type_evnt_ligne_credit"][4] = _("Prélèvement frais");
$adsys["adsys_type_evnt_ligne_credit"][5] = _("Suspension");
$adsys["adsys_type_evnt_ligne_credit"][6] = _("Annulation suspension");
$adsys["adsys_type_evnt_ligne_credit"][7] = _("Soldé");
$adsys["adsys_type_evnt_ligne_credit"][8] = _("Supprimé");
$adsys["adsys_type_evnt_ligne_credit"][9] = _("Radié");

// Nature des événements
$adsys["adsys_nature_evnt_ligne_credit"][1] = _("Capital");
$adsys["adsys_nature_evnt_ligne_credit"][2] = _("Intérêts");


//module guichet
$adsys["adsys_fonction_systeme"][151] = _("Visualisation menu guichet");
$adsys["adsys_fonction_systeme"][152] = _("Autorisation de transfert"); // ticket #695
$adsys["adsys_fonction_systeme"][198] = _("Autorisation de retrait en déplacé"); // ticket AT-44
$adsys["adsys_fonction_systeme"][155] = _("Approvisionnement");
$adsys["adsys_fonction_systeme"][156] = _("Délestage");
$adsys["adsys_fonction_systeme"][154] = _("Retrait par lot");
$adsys["adsys_fonction_systeme"][158] = _("Dépôt par lot"); // Redondant avec 501
$adsys["adsys_fonction_systeme"][159] = _("Traitement par lot Dépôt  / Quotité via fichier");
$adsys["adsys_fonction_systeme"][153] = _("Perception des frais d’adhesion par lot"); // Jira: Mae-17 -> Perception des frais d’adhesion par lot
$adsys["adsys_fonction_systeme"][160] = _("Recharge Carte Ferlo par Versement espèce");
//Ajout chéquiers imprimés-454
$adsys["adsys_fonction_systeme"][161] = _("Ajout chéquiers imprimés");

// Projet Chèques Internes
$adsys["adsys_fonction_systeme"][162] = _("Gestion des chèques certifiés");
$adsys["adsys_fonction_systeme"][163] = _("Traitement des chèques reçus en compensation");
$adsys["adsys_fonction_systeme"][164] = _("Enregistrement des chèques");
$adsys["adsys_fonction_systeme"][165] = _("Traitement des chèques certifiés");
$adsys["adsys_fonction_systeme"][166] = _("Traitement des chèques ordinaires (non certifiés)");
$adsys["adsys_fonction_systeme"][167] = _("Traitement des chèques ordinaires mis en attente");

$adsys["adsys_fonction_systeme"][170] = _("Ajustement encaisse");

if (isEngraisChimiques()){
  $adsys["adsys_fonction_systeme"][171] = _("PNSEB_FENACOBU");
  $adsys["adsys_fonction_systeme"][172] = _("Ajout commande");
  $adsys["adsys_fonction_systeme"][173] = _("Paiement commande en attente");
  $adsys["adsys_fonction_systeme"][174] = _("Approuver derogation");
  $adsys["adsys_fonction_systeme"][175] = _("Effectuer derogation");
  $adsys["adsys_fonction_systeme"][176] = _("Annulation commande");
  //$adsys["adsys_fonction_systeme"][177] = _("Ajout beneficiaire");
  $adsys["adsys_fonction_systeme"][178] = _("Modification beneficiaire");
  $adsys["adsys_fonction_systeme"][179] = _("Liste des Rapports");
  $adsys["adsys_fonction_systeme"][182] = _("Visualisations des transactions PNSEB_FENACOBU");
  $adsys["adsys_fonction_systeme"][183] = _("Valider commandes en attente");
  $adsys["adsys_fonction_systeme"][184] = _("Enregistrement des bons d'achats");
  $adsys["adsys_fonction_systeme"][185] = _("Consultation des stocks");
  $adsys["adsys_fonction_systeme"][199] = _("Gestion des stocks bon d'achats");
  $adsys["adsys_fonction_systeme"][200] = _("Approvisionnement des bon d'achats");
  $adsys["adsys_fonction_systeme"][168] = _("Delestage des bon d'achats");
  $adsys["adsys_fonction_systeme"][169] = _("Paiement des commandes");
  $adsys["adsys_fonction_systeme"][801] = _("Distribution des bons d'achats");
  $adsys["adsys_fonction_systeme"][802] = _("Consultation des bons achats agent");
  $adsys["adsys_fonction_systeme"][803] = _("Consultation des bons achats de tous les agents");

}
$adsys["adsys_fonction_systeme"][804] = _("Autorisation d'approvisionnement/délestage"); // Ticket AT-39
$adsys["adsys_fonction_systeme"][805] = _("Refuser d'approvisionnement/délestage"); // Ticket AT-39
$adsys["adsys_fonction_systeme"][806] = _("Remboursement de crédit par lot"); // Ticket AT-198
if(isAtmModule()) {
    $adsys["adsys_fonction_systeme"][807] = _("Gestion des cartes ATM"); // Ticket FS-37
    $adsys["adsys_fonction_systeme"][808] = _("Listes des commandes à envoyer pour impression"); // Ticket FS-38
    $adsys["adsys_fonction_systeme"][809] = _("Import des cartes imprimées"); // Ticket FS-39
    $adsys["adsys_fonction_systeme"][810] = _("Historique des commandes envoyées"); // Ticket FS-42
    $adsys["adsys_fonction_systeme"][811] = _("Liste de toutes les cartes"); // Ticket FS-42
    $adsys["adsys_fonction_systeme"][812] = _("Les rapports ATM"); // Ticket FS-46
}
$adsys["adsys_fonction_systeme"][177] = _("Effectuer d'approvisionnement/délestage"); // Ticket AT-39


$adsys["adsys_fonction_systeme"][180] = _("Visualisation des transactions");
$adsys["adsys_fonction_systeme"][181] = _("Visualisation des transactions tous guichets");
$adsys["adsys_fonction_systeme"][186] = _("Change Cash");
$adsys["adsys_fonction_systeme"][187] = _("Gestion des paiements Net Bank");
$adsys["adsys_fonction_systeme"][188] = _("Traitement des attentes");
$adsys["adsys_fonction_systeme"][189] = _("Passage opérations diverses de caisse/compte");
$adsys["adsys_fonction_systeme"][195] = _("Rapport sur les opérations diverses"); //ticket 437

$adsys["adsys_fonction_systeme"][190] = _("Souscription des parts sociales par lot via fichier");
$adsys["adsys_fonction_systeme"][191] = _("Chéquier à Imprimer");
$adsys["adsys_fonction_systeme"][192] = _("Confirmation des chéquiers Imprimés");

// Vérification si Multi-Agence
if(isset($global_id_agence) && isMultiAgence())
{
$adsys["adsys_fonction_systeme"][193] = _("Opération en déplacé");
$adsys["adsys_fonction_systeme"][194] = _("Visualisation des opérations en déplacé");
}
if (isEngraisChimiques()){
	$adsys["adsys_fonction_systeme"][196] = _("Autorisation de commande PNSEB_FENACOBU");
	$adsys["adsys_fonction_systeme"][197] = _("Rejet de commande PNSEB_FENACOBU");
  //$adsys["adsys_fonction_systeme"][198] = _("Enregistrement des livraisons bon d'achats");
}

//module système
$adsys["adsys_fonction_systeme"][201] = _("Visualisation menu système");
$adsys["adsys_fonction_systeme"][205] = _("Ouverture agence");
$adsys["adsys_fonction_systeme"][206] = _("Fermeture agence");
$adsys["adsys_fonction_systeme"][210] = _("Sauvegarde des données");
$adsys["adsys_fonction_systeme"][211] = _("Consolidation de données");
$adsys["adsys_fonction_systeme"][212] = _("Batch");

// Vérification si Multi-Agence
if(isset($global_id_agence) && isMultiAgence())
{
$adsys["adsys_fonction_systeme"][213] = _("Traitements de nuit Multi-Agence");
}

// Vérification si Compensation au siège
if(isset($global_id_agence) && isCompensationSiege())
{
$adsys["adsys_fonction_systeme"][214] = _("Traitement compensation au siège");
}

$adsys["adsys_fonction_systeme"][215] = _("Modification autre mot de passe");
$adsys["adsys_fonction_systeme"][230] = _("Déconnexion autre code utilisateur");
$adsys["adsys_fonction_systeme"][235] = _("Ajustement du solde d'un compte");
$adsys["adsys_fonction_systeme"][240] = _("Gestion de la licence");
$adsys["adsys_fonction_systeme"][241] = _("Informations système");
if(isset($global_id_agence) && isCompensationSiege())
{
$adsys["adsys_fonction_systeme"][242] = _("Rapport Etat de la Compensation des Opérations en déplacé");
}
//module paramétrage

// module parametrage => parametrage Engrais Chimique PNSEB isEngraisChimiques()
$adsys["adsys_fonction_systeme"][252] = _("Gestion des modules spécifiques");
if (isEngraisChimiques()){
  $adsys["adsys_fonction_systeme"][253] = _("PNSEB_FENACOBU");
}
$adsys["adsys_fonction_systeme"][254] = _("Automatisme module spécifique");



$adsys["adsys_fonction_systeme"][251] = _("Visualisation menu paramétrage");
$adsys["adsys_fonction_systeme"][255] = _("Visualisation gestion des profils");
$adsys["adsys_fonction_systeme"][256] = _("Ajout d'un profil");
$adsys["adsys_fonction_systeme"][257] = _("Consultation d'un profil");
$adsys["adsys_fonction_systeme"][258] = _("Modification d'un profil");
$adsys["adsys_fonction_systeme"][259] = _("Suppression d'un profil");
$adsys["adsys_fonction_systeme"][260] = _("Modification mot de passe");
$adsys["adsys_fonction_systeme"][265] = _("Visualisation menu gestion des utilisateurs");
$adsys["adsys_fonction_systeme"][270] = _("Ajout utilisateur");
$adsys["adsys_fonction_systeme"][271] = _("Consultation utilisateur");
$adsys["adsys_fonction_systeme"][272] = _("Modification utilisateur");
$adsys["adsys_fonction_systeme"][273] = _("Suppression utilisateur");
$adsys["adsys_fonction_systeme"][274] = _("Visualisation des devises");
$adsys["adsys_fonction_systeme"][275] = _("Ajout d'une devise");
$adsys["adsys_fonction_systeme"][276] = _("Modification d'une devise");
$adsys["adsys_fonction_systeme"][277] = _("Visualisation des positions de change");
$adsys["adsys_fonction_systeme"][278] = _("Afficher les utilisateurs");
$adsys["adsys_fonction_systeme"][281] = _("Visualisation gestion des champs extras");
$adsys["adsys_fonction_systeme"][287] = _("Visualisation menu gestion des codes utilisateurs");
$adsys["adsys_fonction_systeme"][288] = _("Ajout login");
$adsys["adsys_fonction_systeme"][289] = _("Consultation login");
$adsys["adsys_fonction_systeme"][290] = _("Modification login");
$adsys["adsys_fonction_systeme"][291] = _("Suppression login");
$adsys["adsys_fonction_systeme"][292] = _("Visualisation gestion des tables de paramétrage");
$adsys["adsys_fonction_systeme"][293] = _("Consultation des tables de paramétrage");
$adsys["adsys_fonction_systeme"][294] = _("Modification des tables de paramétrage");
$adsys["adsys_fonction_systeme"][295] = _("Ajout dans tables de paramétrage");
$adsys["adsys_fonction_systeme"][296] = _("Visualisation jours ouvrables");
$adsys["adsys_fonction_systeme"][297] = _("Modification des profils associés aux codes utilisateurs");
$adsys["adsys_fonction_systeme"][298] = _("Suppression dans table de paramétrage");
$adsys["adsys_fonction_systeme"][299] = _("Modification de frais et commisions");
$adsys["adsys_fonction_systeme"][300] = _("Gestion de Jasper report"); 

//Module rapport
$adsys["adsys_fonction_systeme"][301] = _("Visualisation du menu rapport");
$adsys["adsys_fonction_systeme"][310] = _("Rapports client");
$adsys["adsys_fonction_systeme"][320] = _("Rapports multi-agences");
$adsys["adsys_fonction_systeme"][330] = _("Rapports épargne");
$adsys["adsys_fonction_systeme"][340] = _("Rapports chéquiers");
$adsys["adsys_fonction_systeme"][350] = _("Rapports crédit");
$adsys["adsys_fonction_systeme"][370] = _("Rapports agence");
$adsys["adsys_fonction_systeme"][380] = _("Rapports externe"); 
$adsys["adsys_fonction_systeme"][390] = _("Simulation échéancier");
$adsys["adsys_fonction_systeme"][399] = _("Visualisation dernier rapport");
$adsys["adsys_fonction_systeme"][398] = _("Visualisation des rapports imprimé");

//Module comptable
$adsys["adsys_fonction_systeme"][401] = _("Visualisation du menu comptabilité");
$adsys["adsys_fonction_systeme"][410] = _("Gestion du plan comptable");
$adsys["adsys_fonction_systeme"][420] = _("Gestion des opérations comptables");
$adsys["adsys_fonction_systeme"][430] = _("Rapports");
$adsys["adsys_fonction_systeme"][431] = _("Traitement des transactions FERLO");
$adsys["adsys_fonction_systeme"][432] = _("Dotation aux provisions crédits ");
$adsys["adsys_fonction_systeme"][433] = _("Modification provisions crédits ");
$adsys["adsys_fonction_systeme"][440] = _("Gestion des exercices");
$adsys["adsys_fonction_systeme"][441] = _("Consultation des exercices");
$adsys["adsys_fonction_systeme"][442] = _("Cloture d'un exercice");
$adsys["adsys_fonction_systeme"][443] = _("Modification d'un exercice");
$adsys["adsys_fonction_systeme"][444] = _("Clôture périodique");
$adsys["adsys_fonction_systeme"][450] = _("Gestion des journaux comptables");
$adsys["adsys_fonction_systeme"][451] = _("Consultation des journaux comptables");
$adsys["adsys_fonction_systeme"][452] = _("Modification des journaux comptables");
$adsys["adsys_fonction_systeme"][453] = _("Suppression des journaux comptables");
$adsys["adsys_fonction_systeme"][454] = _("Creation d'un journal comptable");
$adsys["adsys_fonction_systeme"][455] = _("Saisie des Opérations");
$adsys["adsys_fonction_systeme"][456] = _("Ajout compte de contrepartie");
$adsys["adsys_fonction_systeme"][470] = _("Saisie écritures utilisateurs");
$adsys["adsys_fonction_systeme"][471] = _("Validation écritures utilisateurs");
$adsys["adsys_fonction_systeme"][472] = _("Gestion des opérations diverses de caisse/compte");
$adsys["adsys_fonction_systeme"][473] = _("Passage des opérations siège/agence");
$adsys["adsys_fonction_systeme"][474] = _("Annulation des opérations réciproques");
$adsys["adsys_fonction_systeme"][475] = _("Radiation crédit");
$adsys["adsys_fonction_systeme"][476] = _("Déclarations de tva");
$adsys["adsys_fonction_systeme"][477] = _("Suppression de compte comptable");
$adsys["adsys_fonction_systeme"][478] = _("Gestion des écritures libres");
$adsys["adsys_fonction_systeme"][479] = _("Mouvementer un compte interne à une date antérieure"); 
// fonctions internes (A partir du numéro 500)
// 500 - 509 : Reprise des données
$adsys["adsys_fonction_systeme"][500] = _("Reprise des données comptes de base");
$adsys["adsys_fonction_systeme"][501] = _("Reprise de comptes épargne existants");
$adsys["adsys_fonction_systeme"][502] = _("Reprise de comptes PS existants");
$adsys["adsys_fonction_systeme"][503] = _("Reprise d'un crédit existant");
$adsys["adsys_fonction_systeme"][504] = _("Reprise du bilan d'ouverture");
// 510 - 519 : Régularisations suite erreur
$adsys["adsys_fonction_systeme"][510] = _("Régularisation suite erreur logicielle");
$adsys["adsys_fonction_systeme"][511] = _("Régularisation suite erreur utilisateur");


//Dépendances entre fonctions systèmes
/* On définit pour une fonction donnée toutes les fonctions qui doivent être accessibles pour
   qu'elle même soit accessible. La fonction spéciale numéro 1000 correspond à la présence ou non d'un guichet
   Ex: [5] = array(3) signifie que 5 a besoin de 3 */
$adsys["adsys_fonction_systeme_dependance"][5] = array(3);
$adsys["adsys_fonction_systeme_dependance"][81] = array(3);
$adsys["adsys_fonction_systeme_dependance"][85] = array(3);
$adsys["adsys_fonction_systeme_dependance"][86] = array(3);
$adsys["adsys_fonction_systeme_dependance"][10] = array(5);
$adsys["adsys_fonction_systeme_dependance"][15] = array(5);
$adsys["adsys_fonction_systeme_dependance"][16] = array(5);
$adsys["adsys_fonction_systeme_dependance"][19] = array(5);
$adsys["adsys_fonction_systeme_dependance"][20] = array(5);
$adsys["adsys_fonction_systeme_dependance"][25] = array(5);
$adsys["adsys_fonction_systeme_dependance"][30] = array(5);
$adsys["adsys_fonction_systeme_dependance"][51] = array(3);
$adsys["adsys_fonction_systeme_dependance"][53] = array(51);
$adsys["adsys_fonction_systeme_dependance"][54] = array(51);
$adsys["adsys_fonction_systeme_dependance"][55] = array(51);
$adsys["adsys_fonction_systeme_dependance"][59] = array(51);
$adsys["adsys_fonction_systeme_dependance"][68] = array(51); //ticket 696
$adsys["adsys_fonction_systeme_dependance"][70] = array(51);
$adsys["adsys_fonction_systeme_dependance"][71] = array(51);
$adsys["adsys_fonction_systeme_dependance"][74] = array(51);
$adsys["adsys_fonction_systeme_dependance"][75] = array(51);
$adsys["adsys_fonction_systeme_dependance"][76] = array(51);
$adsys["adsys_fonction_systeme_dependance"][78] = array(51);
$adsys["adsys_fonction_systeme_dependance"][80] = array(51);
$adsys["adsys_fonction_systeme_dependance"][81] = array(51);
$adsys["adsys_fonction_systeme_dependance"][85] = array(51);
$adsys["adsys_fonction_systeme_dependance"][86] = array(51);

$adsys["adsys_fonction_systeme_dependance"][92] = array(51);
$adsys["adsys_fonction_systeme_dependance"][93] = array(51);
$adsys["adsys_fonction_systeme_dependance"][64] = array(51);

$adsys["adsys_fonction_systeme_dependance"][101] = array(3);
$adsys["adsys_fonction_systeme_dependance"][102] = array(3);
$adsys["adsys_fonction_systeme_dependance"][105] = array(101);
$adsys["adsys_fonction_systeme_dependance"][110] = array(101);
$adsys["adsys_fonction_systeme_dependance"][115] = array(101);
$adsys["adsys_fonction_systeme_dependance"][120] = array(101);
$adsys["adsys_fonction_systeme_dependance"][125] = array(101);
$adsys["adsys_fonction_systeme_dependance"][126] = array(101);
$adsys["adsys_fonction_systeme_dependance"][129] = array(101);
$adsys["adsys_fonction_systeme_dependance"][130] = array(101);
$adsys["adsys_fonction_systeme_dependance"][132] = array(101);
$adsys["adsys_fonction_systeme_dependance"][133] = array(101);
$adsys["adsys_fonction_systeme_dependance"][135] = array(101);
$adsys["adsys_fonction_systeme_dependance"][140] = array(101);
$adsys["adsys_fonction_systeme_dependance"][141] = array(101);
$adsys["adsys_fonction_systeme_dependance"][143] = array(101);
$adsys["adsys_fonction_systeme_dependance"][145] = array(101);
$adsys["adsys_fonction_systeme_dependance"][147] = array(101);
$adsys["adsys_fonction_systeme_dependance"][148] = array(101);
$adsys["adsys_fonction_systeme_dependance"][152] = array(151);
$adsys["adsys_fonction_systeme_dependance"][153] = array(151); // Jira: Mae-17 -> Perception des frais d’adhesion par lot
$adsys["adsys_fonction_systeme_dependance"][154] = array(151);
$adsys["adsys_fonction_systeme_dependance"][155] = array(151);
$adsys["adsys_fonction_systeme_dependance"][156] = array(151);
$adsys["adsys_fonction_systeme_dependance"][157] = array(151);
$adsys["adsys_fonction_systeme_dependance"][158] = array(151);
$adsys["adsys_fonction_systeme_dependance"][160] = array(151);
$adsys["adsys_fonction_systeme_dependance"][162] = array(151);
$adsys["adsys_fonction_systeme_dependance"][163] = array(151);
$adsys["adsys_fonction_systeme_dependance"][170] = array(151);
$adsys["adsys_fonction_systeme_dependance"][103] = array(101);
$adsys["adsys_fonction_systeme_dependance"][104] = array(101);
$adsys["adsys_fonction_systeme_dependance"][107] = array(101);
$adsys["adsys_fonction_systeme_dependance"][108] = array(101);

//$adsys["adsys_fonction_systeme_dependance"][171] = array(151);
if (isEngraisChimiques()) {
  $adsys["adsys_fonction_systeme_dependance"][172] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][173] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][174] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][175] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][176] = array(171);
  //$adsys["adsys_fonction_systeme_dependance"][177] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][178] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][179] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][182] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][183] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][184] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][185] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][199] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][200] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][168] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][169] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][801] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][802] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][803] = array(171);
}
$adsys["adsys_fonction_systeme_dependance"][180] = array(151);
$adsys["adsys_fonction_systeme_dependance"][181] = array(151);
$adsys["adsys_fonction_systeme_dependance"][188] = array(151);
if (isEngraisChimiques()){
  $adsys["adsys_fonction_systeme_dependance"][196] = array(171);
  $adsys["adsys_fonction_systeme_dependance"][197] = array(171);
}

$adsys["adsys_fonction_systeme_dependance"][164] = array(163);
$adsys["adsys_fonction_systeme_dependance"][165] = array(163);
$adsys["adsys_fonction_systeme_dependance"][166] = array(163);
$adsys["adsys_fonction_systeme_dependance"][167] = array(163);

$adsys["adsys_fonction_systeme_dependance"][600] = array(102);
$adsys["adsys_fonction_systeme_dependance"][601] = array(102);
$adsys["adsys_fonction_systeme_dependance"][602] = array(102);
$adsys["adsys_fonction_systeme_dependance"][603] = array(102);
$adsys["adsys_fonction_systeme_dependance"][604] = array(102);
$adsys["adsys_fonction_systeme_dependance"][605] = array(102);
$adsys["adsys_fonction_systeme_dependance"][606] = array(102);
$adsys["adsys_fonction_systeme_dependance"][607] = array(102);
$adsys["adsys_fonction_systeme_dependance"][608] = array(102);
$adsys["adsys_fonction_systeme_dependance"][609] = array(102);
$adsys["adsys_fonction_systeme_dependance"][610] = array(102);

$adsys["adsys_fonction_systeme_dependance"][92] = array(151);
$adsys["adsys_fonction_systeme_dependance"][93] = array(151);
$adsys["adsys_fonction_systeme_dependance"][193] = array(151);
$adsys["adsys_fonction_systeme_dependance"][194] = array(151);

$adsys["adsys_fonction_systeme_dependance"][205] = array(201);
$adsys["adsys_fonction_systeme_dependance"][206] = array(201);
$adsys["adsys_fonction_systeme_dependance"][210] = array(201);
$adsys["adsys_fonction_systeme_dependance"][212] = array(201);
$adsys["adsys_fonction_systeme_dependance"][215] = array(201);
$adsys["adsys_fonction_systeme_dependance"][230] = array(201);
$adsys["adsys_fonction_systeme_dependance"][255] = array(251);
$adsys["adsys_fonction_systeme_dependance"][256] = array(255);
$adsys["adsys_fonction_systeme_dependance"][257] = array(255);
$adsys["adsys_fonction_systeme_dependance"][258] = array(255);
$adsys["adsys_fonction_systeme_dependance"][259] = array(255);
$adsys["adsys_fonction_systeme_dependance"][260] = array(251);
$adsys["adsys_fonction_systeme_dependance"][252] = array(251);
$adsys["adsys_fonction_systeme_dependance"][265] = array(251);
$adsys["adsys_fonction_systeme_dependance"][270] = array(265);
$adsys["adsys_fonction_systeme_dependance"][271] = array(265);
$adsys["adsys_fonction_systeme_dependance"][272] = array(265);
$adsys["adsys_fonction_systeme_dependance"][273] = array(265);
$adsys["adsys_fonction_systeme_dependance"][287] = array(251);
$adsys["adsys_fonction_systeme_dependance"][288] = array(287);
$adsys["adsys_fonction_systeme_dependance"][289] = array(287);
$adsys["adsys_fonction_systeme_dependance"][290] = array(287);
$adsys["adsys_fonction_systeme_dependance"][291] = array(287);
$adsys["adsys_fonction_systeme_dependance"][292] = array(251);
$adsys["adsys_fonction_systeme_dependance"][293] = array(292);
$adsys["adsys_fonction_systeme_dependance"][294] = array(292);
$adsys["adsys_fonction_systeme_dependance"][295] = array(292);
$adsys["adsys_fonction_systeme_dependance"][296] = array(251);
if (isEngraisChimiques()) {
  $adsys["adsys_fonction_systeme_dependance"][253] = array(252);
}
$adsys["adsys_fonction_systeme_dependance"][254] = array(252);
$adsys["adsys_fonction_systeme_dependance"][310] = array(301);
$adsys["adsys_fonction_systeme_dependance"][310] = array(44);
$adsys["adsys_fonction_systeme_dependance"][330] = array(301);
//$adsys["adsys_fonction_systeme_dependance"][330] = array(43);
//$adsys["adsys_fonction_systeme_dependance"][330] = array(80);
//$adsys["adsys_fonction_systeme_dependance"][350] = array(140);
$adsys["adsys_fonction_systeme_dependance"][350] = array(301);
$adsys["adsys_fonction_systeme_dependance"][370] = array(301);
$adsys["adsys_fonction_systeme_dependance"][390] = array(301);
$adsys["adsys_fonction_systeme_dependance"][399] = array(301);
$adsys["adsys_fonction_systeme_dependance"][410] = array(401);
$adsys["adsys_fonction_systeme_dependance"][420] = array(401);
$adsys["adsys_fonction_systeme_dependance"][398] = array(301);

//$adsys["adsys_fonction_systeme_dependance"][700] = array(3);
$adsys["adsys_fonction_systeme_dependance"][701] = array(700);
$adsys["adsys_fonction_systeme_dependance"][702] = array(701);
$adsys["adsys_fonction_systeme_dependance"][703] = array(701);
$adsys["adsys_fonction_systeme_dependance"][704] = array(701);
$adsys["adsys_fonction_systeme_dependance"][705] = array(700);
$adsys["adsys_fonction_systeme_dependance"][706] = array(700);
$adsys["adsys_fonction_systeme_dependance"][707] = array(700);
$adsys["adsys_fonction_systeme_dependance"][708] = array(700);
$adsys["adsys_fonction_systeme_dependance"][709] = array(700);
$adsys["adsys_fonction_systeme_dependance"][710] = array(700);
$adsys["adsys_fonction_systeme_dependance"][711] = array(700);
$adsys["adsys_fonction_systeme_dependance"][712] = array(700);
$adsys["adsys_fonction_systeme_dependance"][713] = array(700);
$adsys["adsys_fonction_systeme_dependance"][714] = array(713);
$adsys["adsys_fonction_systeme_dependance"][715] = array(700);
$adsys["adsys_fonction_systeme_dependance"][716] = array(700);
$adsys["adsys_fonction_systeme_dependance"][717] = array(700);

if (isAgencyBanking()) {
  $adsys["adsys_fonction_systeme_dependance"][752] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][753] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][754] = array(760);
  $adsys["adsys_fonction_systeme_dependance"][755] = array(760);
  $adsys["adsys_fonction_systeme_dependance"][756] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][757] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][758] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][759] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][760] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][761] = array(760);
  $adsys["adsys_fonction_systeme_dependance"][762] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][763] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][764] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][765] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][766] = array(765);
  $adsys["adsys_fonction_systeme_dependance"][767] = array(765);
  $adsys["adsys_fonction_systeme_dependance"][768] = array(765);
  $adsys["adsys_fonction_systeme_dependance"][771] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][773] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][774] = array(753);
  $adsys["adsys_fonction_systeme_dependance"][775] = array(753);
  $adsys["adsys_fonction_systeme_dependance"][776] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][777] = array(776);
  $adsys["adsys_fonction_systeme_dependance"][778] = array(776);
  $adsys["adsys_fonction_systeme_dependance"][779] = array(776);
  $adsys["adsys_fonction_systeme_dependance"][780] = array(776);
  $adsys["adsys_fonction_systeme_dependance"][781] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][782] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][783] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][784] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][785] = array(760);
  $adsys["adsys_fonction_systeme_dependance"][787] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][788] = array(751);
  $adsys["adsys_fonction_systeme_dependance"][789] = array(751);
}

if (isMobileLending()) {
  $adsys["adsys_fonction_systeme_dependance"][901] = array(900);
  $adsys["adsys_fonction_systeme_dependance"][902] = array(900);
  $adsys["adsys_fonction_systeme_dependance"][903] = array(900);
  $adsys["adsys_fonction_systeme_dependance"][904] = array(902);
  $adsys["adsys_fonction_systeme_dependance"][905] = array(902);
  $adsys["adsys_fonction_systeme_dependance"][906] = array(900);
}

//Fonctions systèmes qui nécessitent un guichet; il suffit de signaler les pères; les fils seront automatiquement considérés
$adsys["adsys_fonction_systeme_guichet"] = array(15,16,30,31,32,53,70,71,74,75,81,85,86,608,154,155,156,158,159,160,173,180,183,92,93,64);

//Caisse centrale, liste des fonctions dont il faut posséder l'accès pour obtenir le statut 'caissier central'
$adsys["fonctions_cc"] = array(151);

//Fonctions systèmes qui nécessitent que l'agence soit ouverte
$adsys["adsys_fonction_systeme_ouvert"] = array(10,15,16,17,20,25,30,31,32,53,54,70,71,74,75,76,78,110,115,120,126,129,130,145,148,154,155,156,157,158,160,170,171,81,85,86,252,253,254,601,602,603,605,608,609,751,900);

//Fonctions qui peuvent être contre-passées
$adsys["adsys_fonction_systeme_contrepass"] = array(20, 30, 31,32, 53, 70, 75, 81, 85, 86, 105, 110, 115, 120, 125, 126, 147, 154, 155, 156, 158, 160, 473,474, 600, 601, 602, 603, 604, 607, 610);

// Fonctions qui ne doivent pas apparaître si l'institution est un ICD
$adsys["adsys_fonctions_non_icd"] = array(20,21,22,23,26,27,28,53,54,70,71,74,75,76,78,80,81,85,86,88,89,154,157,158,160,162,163,164,165,166,167,186,187,188,278);

// Fonction qui ne doivent pas apparaitre si l'institution est une banque
$adsys["adsys_fonctions_non_bq"] = array(20,21,22,23,26,27,28,278);

//Fonction qui exclu les fonctionnalite agent
$adsys["adsys_exclu_fonc_agent"] = array(762,756,763,764,759,766,768,773,774,775,783, 788, 789);

//Fonction qui exclu les fonctionnalite gestionnaire
$adsys["adsys_exclu_fonc_gestion"] = array(752,753,754,755,757,758,760,761,767,776,777,778,779,780,785,782);


// Exclusivité entre fonctions
// On définit une exclusivité entre deux fonctions lorsque celles-ce ne peuvent pas être à la fois accordées à un utilisateur
// $adsys["adsys_fonction_systeme_exclusivite"][A] = array(B) signifie que si A est activée B doit être désactivée et vice versa
// $adsys["adsys_fonction_systeme_exclusivite_guichet"] = array(C) signifie que C est exclusive à la présence d'un guichet et vice versa
// $adsys["adsys_fonction_systeme_exclusivite_cc"]=array(D) => D est exclusive à la présence d'une caisse centrale et vice versa
// l'exclusivite étant commutative alors on aura pas besoin d'une entrée $adsys["adsys_fonction_systeme_exclusivite"][B] = array(A)
$adsys["adsys_fonction_systeme_exclusivite_guichet"] = array(205, 206, 212,172,169,801);

// Catégories d'opérations
$adsys["adsys_categorie_ope"][1] = _("Opération automatique");
$adsys["adsys_categorie_ope"][2] = _("Opération diverse de caisse");
$adsys["adsys_categorie_ope"][3] = _("Opération diverse de compte");
$adsys["adsys_categorie_ope"][4] = _("Opération d'écritures libres");

//Etat du client
$adsys["adsys_etat_client"][1] = _("En attente de validation");
$adsys["adsys_etat_client"][2] = _("Actif");
$adsys["adsys_etat_client"][3] = _("Décédé");
$adsys["adsys_etat_client"][4] = _("Transféré");
$adsys["adsys_etat_client"][5] = _("Démissionnaire");
$adsys["adsys_etat_client"][6] = _("Radié");
$adsys["adsys_etat_client"][7] = _("En attente enregistrement décès");
$adsys["adsys_etat_client"][8] = _("Non-inscrit");
$adsys["adsys_etat_client"][9] = _("En attente solde crédit garanti");
$adsys["adsys_etat_client"][10] = _("En attente d'approbation");
$adsys["adsys_etat_client"][11] = _("Approbation creation client");
$adsys["adsys_etat_client"][12] = _("Rejet creation client");

//Statut de l'agence
$adsys["adsys_statut_agence"][1] = _("Ouvert");
$adsys["adsys_statut_agence"][2] = _("Fermé");
$adsys["adsys_statut_agence"][3] = _("Traitements de nuits");

//Statut d'un utilisateur
$adsys["adsys_statut_utilisateur"][1] = _("Actif");
$adsys["adsys_statut_utilisateur"][2] = _("Inactif");
$adsys["adsys_statut_utilisateur"][3] = _("Supprimé");

// Mode de calcul d'intérêt sur compte d'épargne
$adsys["adsys_mode_calcul_int_epargne"][1] = _("Aucun intérêt");
$adsys["adsys_mode_calcul_int_epargne"][2] = _("Sur solde journalier le plus bas");
$adsys["adsys_mode_calcul_int_epargne"][3] = _("Sur solde courant le plus bas");
$adsys["adsys_mode_calcul_int_epargne"][4] = _("Sur solde mensuel le plus bas");
$adsys["adsys_mode_calcul_int_epargne"][5] = _("Sur solde trimestriel le plus bas");
$adsys["adsys_mode_calcul_int_epargne"][6] = _("Sur solde semestriel le plus bas");
$adsys["adsys_mode_calcul_int_epargne"][7] = _("Sur solde courant du compte");
$adsys["adsys_mode_calcul_int_epargne"][8] = _("Sur solde moyen mensuel");
$adsys["adsys_mode_calcul_int_epargne"][9] = _("Sur solde moyen trimestriel");
$adsys["adsys_mode_calcul_int_epargne"][10] = _("Sur solde moyen semestriel");
$adsys["adsys_mode_calcul_int_epargne"][11] = _("Sur solde moyen annuel");
$adsys["adsys_mode_calcul_int_epargne"][12] = _("Sur solde pour épargne à la source");


// Mode de paiement des intérêts sur compte d'épargne
$adsys["adsys_mode_paiement"][1] = _("Paiement en fin de mois");
$adsys["adsys_mode_paiement"][2] = _("Paiement date ouverture");
$adsys["adsys_mode_paiement"][3] = _("Paiement annuel");

//Mode de calcul d'intérêts lors de la rupture

$adsys["adsys_mode_calcul_int_rupt"][1] = _("Sans intérêts");
$adsys["adsys_mode_calcul_int_rupt"][2] = _("Intérêts calculés au prorata");
$adsys["adsys_mode_calcul_int_rupt"][3] = _("Intérêts calculés pour tout le reste du terme");

//Mode de calcul des pénalités à la rupture
$adsys["adsys_mode_calcul_penal_rupt"][1] = _("Pénalités normales");
$adsys["adsys_mode_calcul_penal_rupt"][2] = _("Pénalités proportionelles dégressives");

//Calcule des pénalités sur le solde ou sur les intérêts générés
$adsys["adsys_calcul_pen_int"][1] = _("Le capital");
$adsys["adsys_calcul_pen_int"][2] = _("Les intérêts");

//Ordre de remboursement
$adsys["adsys_ordre_remb"][1] = _("Garantie -> pénalité -> intérêt -> capital");
$adsys["adsys_ordre_remb"][2] = _("Garantie -> capital -> intérêt -> pénalité");
$adsys["adsys_ordre_remb"][3] = _("Garantie -> intérêt -> capital -> pénalité");
$adsys["adsys_ordre_remb"][4] = _("Garantie -> intérêt -> pénalité -> capital");
$adsys["adsys_ordre_remb"][5] = _("Intérêt -> pénalité -> capital -> garantie");
$adsys["adsys_ordre_remb"][6] = _("Intérêt -> capital -> pénalité -> garantie");
$adsys["adsys_ordre_remb"][7] = _("Pénalité -> intérêt -> capital -> garantie");
$adsys["adsys_ordre_remb"][8] = _("Capital -> intérêt -> pénalité -> garantie");

// Base pour le calcul des taux d'intérêts//Statuts des messages Swift
$adsys["adsys_statut_message_swif"][0]= _("En attente");
$adsys["adsys_statut_message_swif"][1]= _("Prêt à être exécuté");
$adsys["adsys_statut_message_swif"][2]= _("Erreur détectée, attente de décision de l'utilisateur");
$adsys["adsys_statut_message_swif"][3]= _("Exécuté");
$adsys["adsys_statut_message_swif"][4]= _("Rejeté");
$adsys["adsys_base_taux"][1] = _("360 jours");
$adsys["adsys_base_taux"][2] = _("365 jours");

// Fréquence de calcul des intérêts d'un compte épargne
// ATTENTION : Les numéros des fréquences doivent respecter l'ordre d'inclusivité
// Ex: Trimestrielle inclut mensuelle, semestrielle inclut trimestrielle et mensuelle
$adsys["adsys_freq"][1] = _("Mensuelle");
$adsys["adsys_freq"][2] = _("Trimestrielle");
$adsys["adsys_freq"][3] = _("Semestrielle");
$adsys["adsys_freq"][4] = _("Annuelle");

//Choix de la frequence pour la duree minimum entre deux retraits
$adsys["adsys_type_duree_min2retrait"][1] = _("Durée en nombre de jours");
$adsys["adsys_type_duree_min2retrait"][2] = _("Durée en nombre de fois dans une mois");

//Type comptable des comptes
$adsys["adsys_type_cpte_comptable"][1] = _("Dépôts à vue");
$adsys["adsys_type_cpte_comptable"][2] = _("Dépôt à terme");
$adsys["adsys_type_cpte_comptable"][3] = _("Autres dépôts: Garanties sur crédits");
$adsys["adsys_type_cpte_comptable"][4] = _("Capital social");
$adsys["adsys_type_cpte_comptable"][5] = _("Compte à terme");
$adsys["adsys_type_cpte_comptable"][6] = _("Epargne à la source");
$adsys["adsys_type_cpte_comptable"][7] = _("Comptes d'attente");
$adsys["adsys_type_cpte_comptable"][8] = _("Comptes chèques certifiés");

//Compartiments comptables
$adsys["adsys_compartiment_comptable"][1] = _("Actif");
$adsys["adsys_compartiment_comptable"][2] = _("Passif");
$adsys["adsys_compartiment_comptable"][3] = _("Charge");
$adsys["adsys_compartiment_comptable"][4] = _("Produit");
$adsys["adsys_compartiment_comptable"][5] = _("Actif-Passif");

//Jours de la semaine//Statuts des messages Swift
$adsys["adsys_statut_message_swif"][0]= _("En attente");
$adsys["adsys_statut_message_swif"][1]= _("Prêt à être exécuté");
$adsys["adsys_statut_message_swif"][2]= _("Erreur détectée, attente de décision de l'utilisateur");
$adsys["adsys_statut_message_swif"][3]= _("Exécuté");
$adsys["adsys_statut_message_swif"][4]= _("Rejeté");
$adsys["adsys_jour_semaine"][1] = _("Lundi");
$adsys["adsys_jour_semaine"][2] = _("Mardi");
$adsys["adsys_jour_semaine"][3] = _("Mercredi");
$adsys["adsys_jour_semaine"][4] = _("Jeudi");
$adsys["adsys_jour_semaine"][5] = _("Vendredi");
$adsys["adsys_jour_semaine"][6] = _("Samedi");
$adsys["adsys_jour_semaine"][7] = _("Dimanche");

$adsys["adsys_jour_semaine_pluriel"][1] = _("Lundis");
$adsys["adsys_jour_semaine_pluriel"][2] = _("Mardis");
$adsys["adsys_jour_semaine_pluriel"][3] = _("Mercredis");
$adsys["adsys_jour_semaine_pluriel"][4] = _("Jeudis");
$adsys["adsys_jour_semaine_pluriel"][5] = _("Vendredis");
$adsys["adsys_jour_semaine_pluriel"][6] = _("Samedis");
$adsys["adsys_jour_semaine_pluriel"][7] = _("Dimanches");

/*Années définies pour les jours feriés : utile car l'interface de paramétrage de la table 'ad_fer' est générique et
  affichera '0' si l'année n'est pas renseignée*/
$adsys["adsys_annee"][1995] = 1995;
$adsys["adsys_annee"][1996] = 1996;
$adsys["adsys_annee"][1997] = 1997;
$adsys["adsys_annee"][1998] = 1998;
$adsys["adsys_annee"][1999] = 1999;
$adsys["adsys_annee"][2000] = 2000;
$adsys["adsys_annee"][2001] = 2001;
$adsys["adsys_annee"][2002] = 2002;
$adsys["adsys_annee"][2003] = 2003;
$adsys["adsys_annee"][2004] = 2004;
$adsys["adsys_annee"][2005] = 2005;
$adsys["adsys_annee"][2006] = 2006;
$adsys["adsys_annee"][2007] = 2007;
$adsys["adsys_annee"][2008] = 2008;
$adsys["adsys_annee"][2009] = 2009;
$adsys["adsys_annee"][2010] = 2010;
$adsys["adsys_annee"][2011] = 2011;
$adsys["adsys_annee"][2012] = 2012;
$adsys["adsys_annee"][2013] = 2013;
$adsys["adsys_annee"][2014] = 2014;
$adsys["adsys_annee"][2015] = 2015;
$adsys["adsys_annee"][2016] = 2016;
$adsys["adsys_annee"][2017] = 2017;
$adsys["adsys_annee"][2018] = 2018;
$adsys["adsys_annee"][2019] = 2019;
$adsys["adsys_annee"][2020] = 2020;
$adsys["adsys_annee"][2021] = 2021;
$adsys["adsys_annee"][2022] = 2022;
$adsys["adsys_annee"][2023] = 2023;
$adsys["adsys_annee"][2024] = 2024;
$adsys["adsys_annee"][2025] = 2025;

//Mois de l'année
$adsys["adsys_mois"][1] = _("Janvier");
$adsys["adsys_mois"][2] = _("Février");
$adsys["adsys_mois"][3] = _("Mars");
$adsys["adsys_mois"][4] = _("Avril");
$adsys["adsys_mois"][5] = _("Mai");
$adsys["adsys_mois"][6] = _("Juin");
$adsys["adsys_mois"][7] = _("Juillet");
$adsys["adsys_mois"][8] = _("Août");
$adsys["adsys_mois"][9] = _("Septembre");
$adsys["adsys_mois"][10] = _("Octobre");
$adsys["adsys_mois"][11] = _("Novembre");
$adsys["adsys_mois"][12] = _("Décembre");

//Jours du mois//Statuts des messages Swift
$adsys["adsys_statut_message_swif"][0]= _("En attente");
$adsys["adsys_statut_message_swif"][1]= _("Prêt à être exécuté");
$adsys["adsys_statut_message_swif"][2]= _("Erreur détectée, attente de décision de l'utilisateur");
$adsys["adsys_statut_message_swif"][3]= _("Exécuté");
$adsys["adsys_statut_message_swif"][4]= _("Rejeté");
$adsys["adsys_jour_mois"][1] = 1;
$adsys["adsys_jour_mois"][2] = 2;
$adsys["adsys_jour_mois"][3] = 3;
$adsys["adsys_jour_mois"][4] = 4;
$adsys["adsys_jour_mois"][5] = 5;
$adsys["adsys_jour_mois"][6] = 6;
$adsys["adsys_jour_mois"][7] = 7;
$adsys["adsys_jour_mois"][8] = 8;
$adsys["adsys_jour_mois"][9] = 9;
$adsys["adsys_jour_mois"][10] = 10;
$adsys["adsys_jour_mois"][11] = 11;
$adsys["adsys_jour_mois"][12] = 12;
$adsys["adsys_jour_mois"][13] = 13;
$adsys["adsys_jour_mois"][14] = 14;
$adsys["adsys_jour_mois"][15] = 15;
$adsys["adsys_jour_mois"][16] = 16;
$adsys["adsys_jour_mois"][17] = 17;
$adsys["adsys_jour_mois"][18] = 18;
$adsys["adsys_jour_mois"][19] = 19;
$adsys["adsys_jour_mois"][20] = 20;
$adsys["adsys_jour_mois"][21] = 21;
$adsys["adsys_jour_mois"][22] = 22;
$adsys["adsys_jour_mois"][23] = 23;
$adsys["adsys_jour_mois"][24] = 24;
$adsys["adsys_jour_mois"][25] = 25;
$adsys["adsys_jour_mois"][26] = 26;
$adsys["adsys_jour_mois"][27] = 27;
$adsys["adsys_jour_mois"][28] = 28;
$adsys["adsys_jour_mois"][29] = 29;
$adsys["adsys_jour_mois"][30] = 30;
$adsys["adsys_jour_mois"][31] = 31;

//Report en cas d'échéance lors d'un jour ferié
$adsys["adsys_report"][1] = _("Aucun report");
$adsys["adsys_report"][2] = _("Prochain jour ouvrable");
$adsys["adsys_report"][3] = _("Jour ouvrable précédent");

// Types de comptes apparaissant dans l'historique
$adsys["adsys_typ_cpte_hist"][1] = _("Compte comptable");
$adsys["adsys_typ_cpte_hist"][2] = _("Compte client");
$adsys["adsys_typ_cpte_hist"][3] = _("Compte guichet");
$adsys["adsys_typ_cpte_hist"][4] = _("Compte agence");
$adsys["adsys_typ_cpte_hist"][5] = _("Compte chèque");
$adsys["adsys_typ_cpte_hist"][6] = _("Compte caisse centrale");
$adsys["adsys_typ_cpte_hist"][7] = _("Compte banque");
$adsys["adsys_typ_cpte_hist"][8] = _("Compte chèque agence");

// Raisons de clôture d'un compte
$adsys["adsys_raison_cloture"][1] = _("Défection du client");
$adsys["adsys_raison_cloture"][2] = _("Sur demande du client");
$adsys["adsys_raison_cloture"][3] = _("Rupture anticipée");
$adsys["adsys_raison_cloture"][4] = _("Terme échu");
$adsys["adsys_raison_cloture"][5] = _("Crédit soldé");
$adsys["adsys_raison_cloture"][6] = _("Passage en perte");
$adsys["adsys_raison_cloture"][7] = _("Réalisation garantie");
$adsys["adsys_raison_cloture"][8] = _("Suppression dossier crédit");

//Types de structures
$adsys["adsys_typ_struct"][1] = _("Mutuelle d'épargne et de crédit");
#$adsys["adsys_typ_struct"][2] = _("Institution de crédit direct");
$adsys["adsys_typ_struct"][3] = _("Banque");

// *** Rapports
// Rapports du menu Rapports Clients (CLI-*)
$adsys["adsys_rapport"]['CLI-GEN'] = _("Rapport généraliste sur les clients");
$adsys["adsys_rapport"]['CLI-SIT'] = _("Situation globale d'un client");
$adsys["adsys_rapport"]['CLI-SOC'] = _("Liste des sociétaires de l'institution");
$adsys["adsys_rapport"]['CLI-PSR'] = _("Liste des parts sociales reprises");
$adsys["adsys_rapport"]['CLI-ETA'] = _("Répartition des clients par état");
$adsys["adsys_rapport"]['CLI-CON'] = _("Concentration sur les clients");
$adsys["adsys_rapport"]['CLI-EXP'] = _("Export clients/comptes au format CSV");
// Rapports du menu Rapports Epargne (EPA-*)
$adsys["adsys_rapport"]['EPA-EXT'] = _("Liste des mouvements sur un compte d'épargne");
$adsys["adsys_rapport"]['EPA-CAT'] = _("Suivi des échéances des comptes à terme");
$adsys["adsys_rapport"]['EPA-DAT'] = _("DAT arrivant à échéance");
$adsys["adsys_rapport"]['EPA-CEC'] = _("Historique comptes d'épargne cloturés");
$adsys["adsys_rapport"]['EPA-CON'] = _("Concentration de l'épargne");
$adsys["adsys_rapport"]['EPA-INA'] = _("Comptes d'épargne inactifs / dormant");
$adsys["adsys_rapport"]['EPA-BAS'] = _("Etat général des comptes");
$adsys["adsys_rapport"]['EPA-BAJ'] = _("Soldes des comptes d'épargne mouvementés dans la journée");
$adsys["adsys_rapport"]['EPA-MAX'] = _("Créditeurs les plus importants");
$adsys["adsys_rapport"]['EPA-EXC'] = _("Extraits de compte");
$adsys["adsys_rapport"]['EPA-EXN'] = _("Extraits de compte pour Netbank");
//$adsys["adsys_rapport"]['EPA-CHQ'] = _("Chèquiers à imprimer");
//$adsys["adsys_rapport"]['EPA-CHI'] = _("Chèquiers imprimés");
$adsys["adsys_rapport"]['EPA-CER'] = _("Comptes d'epargne repris");
$adsys["adsys_rapport"]['EPA-ATT'] = _("Liste des frais en attente");
$adsys["adsys_rapport"]['EPA-LST'] = _("Liste des épargnes");
$adsys["adsys_rapport"]['EPA-LDI'] = _("Liste des dépôts initiaux à l'ouverture de comptes épargne");
$adsys["adsys_rapport"]['EPA-IDP'] = _("Rapport inventaire de dépots");
$adsys["adsys_rapport"]['EPA-CDT'] = _("Rapport des comptes dormants");
// Rapports du menu Rapports Crédits (CRD-*)
$adsys["adsys_rapport"]['CRD-ECH'] = _("Echéancier et suivi de remboursement");
$adsys["adsys_rapport"]['CRD-RET'] = _("Crédits en retard");
$adsys["adsys_rapport"]['CRD-CON'] = _("Concentration du portefeuille de crédit");
$adsys["adsys_rapport"]['CRD-SLD'] = _("Historique crédits soldés Clients");
$adsys["adsys_rapport"]['CRD-DEM'] = _("Historique demande de crédits Clients");
$adsys["adsys_rapport"]['CRD-OCT'] = _("Historique des crédits octroyés");
$adsys["adsys_rapport"]['CRD-REE'] = _("Rapport sur les crédits réechelonnés");
$adsys["adsys_rapport"]['CRD-BAL'] = _("Balance âgée du portefeuille à risque");
$adsys["adsys_rapport"]['CRD-MAX'] = _("Encours de crédit les plus importants");
$adsys["adsys_rapport"]['CRD-EMP'] = _("Crédits accordés aux employés et dirigeants");
$adsys["adsys_rapport"]['CRD-DIR'] = _("Loans Granted to the Directors");
$adsys["adsys_rapport"]['CRD-EMY'] = _("Employees loans");
$adsys["adsys_rapport"]['CRD-PRT'] = _("Crédits passés en perte");
$adsys["adsys_rapport"]['CRD-REG'] = _("Registre des prêts");
$adsys["adsys_rapport"]['CRD-AEC'] = _("Crédits arrivant à échéance");
$adsys["adsys_rapport"]['CRD-CAA'] = _("Crédits actifs par agent de crédit");
$adsys["adsys_rapport"]['CRD-REP'] = _("Crédits repris");
$adsys["adsys_rapport"]['CRD-LCD'] = _("Débiteurs les plus importants");
$adsys["adsys_rapport"]['CRD-SRC'] = _("Situation des risques des crédits");
$adsys["adsys_rapport"]['CRD-PGE'] = _("List of 10 Biggest Borrowers");
$adsys["adsys_rapport"]['CRD-CRA'] = _("Risks Situation by Sector");
$adsys["adsys_rapport"]['CRD-RCR'] = _("Recovery of Doubtful Loans, Litigious and Contingent Loans");
$adsys["adsys_rapport"]['CRD-PCS'] = _("Provisions des crédits en souffrances");
$adsys["adsys_rapport"]['CRD-REC'] = _("Recouvrement sur Crédits");
$adsys["adsys_rapport"]['CRD-SLC'] = _("Rapport Suivi Ligne de crédit");
$adsys["adsys_rapport"]['CRD-ICT'] = _("Rapport inventaire de crédits");

// Rapports du menu Guichet (GUI-*)
$adsys["adsys_rapport"]['GUI-TRA'] = _("Visualisation des transactions");
$adsys["adsys_rapport"]['GUI-TRA-EC'] = _("Visualisation des transactions PNSEB-FENACOBU");
$adsys["adsys_rapport"]['GUI-OPD-EXT'] = _("Visualisation des opérations des clients externes à l'agence");
$adsys["adsys_rapport"]['GUI-OPD-INT'] = _("Visualisation des opérations des clients internes à l'agence");
$adsys["adsys_rapport"]['GUI-OPE-DIV'] = _("Rapports sur les opérations diverses ");



// Rapports du menu Rapports Agence (AGC-*)
$adsys["adsys_rapport"]['AGC-STA'] = _("Statistiques et indicateurs d'agence");
$adsys["adsys_rapport"]['AGC-STR'] = _("Statistiques et indicateurs d'agence pour le réseau");
$adsys["adsys_rapport"]['AGC-JOU'] = _("Rapport journalier");
$adsys["adsys_rapport"]['AGC-LIQ'] = _("Prévision des liquidités");
$adsys["adsys_rapport"]['AGC-BRO'] = _("Brouillard de caisse journalier");
$adsys["adsys_rapport"]['AGC-BAT'] = _("Compte rendu d'exécution du batch");
$adsys["adsys_rapport"]['AGC-ADC'] = _("Ajustements de caisse");
$adsys["adsys_rapport"]['AGC-ACT'] = _("Rapport d'activité");
$adsys["adsys_rapport"]['AGC-LIB'] = _("Ecritures libres");
$adsys["adsys_rapport"]['AGC-APF'] = _("Appel de Fonds");
$adsys["adsys_rapport"]['AGC-TRT'] = _("Tableau de Résultats Trimestriels");
$adsys["adsys_rapport"]['AGC-BNR'] = _("Rapports de la BNR");
$adsys["adsys_rapport"]['AGC-EIC'] = _("Équilibre inventaire / comptabilité");
$adsys["adsys_rapport"]['AGC-BCE'] = _("BIC BCEAO");
$adsys["adsys_rapport"]['AGC-STO'] = _("Statistiques Opérationnelles");
$adsys["adsys_rapport"]['AGC-INT'] = _("Génération Interface"); // JIRA : MAE-20


// Rapports du menu Rapports Multi-agences (RMA-*)
$adsys["adsys_rapport"]['RMA-OPD'] = _("Visualisation des opérations en deplacé");
$adsys["adsys_rapport"]['RMA-SCP'] = _("Situation de compensation");

// Rapports du menu Rapports Chequiers (RCQ-*)
$adsys["adsys_rapport"]['RCQ-CCM'] = _("Liste des commandes de chéquiers");
$adsys["adsys_rapport"]['RCQ-CEI'] = _("Liste des chéquiers envoyés à l'impression");
$adsys["adsys_rapport"]['RCQ-CMO'] = _("Liste des chèques/chéquiers mis en opposition");
$adsys["adsys_rapport"]['RCQ-ECI'] = _("États des chéquiers imprimés");

// Rapports du menu Rapports Comptabilité (CPT-*)
$adsys["adsys_rapport"]['CPT-JOU'] = _("Journal comptable");
$adsys["adsys_rapport"]['CPT-BAL'] = _("Balance comptable");
$adsys["adsys_rapport"]['CPT-EXJ'] = _("Exportation du journal comptable");
$adsys["adsys_rapport"]['CPT-GLI'] = _("Grand Livre");
$adsys["adsys_rapport"]['CPT-RES'] = _("Compte de résultat");
$adsys["adsys_rapport"]['CPT-IST'] = _("Compte de résultat BNR");//Compte de résultat
$adsys["adsys_rapport"]['CPT-BIL'] = _("Le bilan");
$adsys["adsys_rapport"]['CPT-BSH'] = _("Bilan BNR");//bilan
$adsys["adsys_rapport"]['CPT-SIT'] = _("Situation intermédiaire MN/ME");
$adsys["adsys_rapport"]['CPT-JAN'] = _("Journal des mouvements réciproques");
$adsys["adsys_rapport"]['CPT-EXB'] = _("Exportation du bilan");
$adsys["adsys_rapport"]['CPT-EXL'] = _("Exportation de la balance");
$adsys["adsys_rapport"]['CPT-GOP'] = _("Liste des opérations comptables");
$adsys["adsys_rapport"]['CPT-LIR'] = _("Ratipo de liquidité");
$adsys["adsys_rapport"]['CPT-TVA'] = _("Déclaration de tva");
$adsys["adsys_rapport"]['CPT-IMP'] = _("Rapport d’impôt mobilier collecté");
$adsys["adsys_rapport"]['CPT-IAP'] = _("Intérêts à payer sur les comptes d’épargne");
$adsys["adsys_rapport"]['CPT-IAR'] = _("Intérêts à recevoir sur les comptes de credits");
// Rapports divers
$adsys["adsys_rapport"]['DEV-POS'] = _("Position de change");
$adsys["adsys_rapport"]['DET-CLO'] = _("Détail clôture périodique ");
$adsys["adsys_rapport"]['ECH-REL'] = _("Echéancier réel de remboursement");
$adsys["adsys_rapport"]['SIM-ECH'] = _("Simulation d'échéancier de remboursement");
$adsys["adsys_rapport"]['SIM-DAT'] = _("Simulation échéancier théorique de DAT");
$adsys["adsys_rapport"]['FIC-CLI'] = _("Fiche client");
$adsys["adsys_rapport"]['SIT-CLI'] = _("Situation analytique client");
$adsys["adsys_rapport"]['AUT-CLI'] = _("Demande d'autorisation de recharge Carte Ferlo");
$adsys["adsys_rapport"]['GUI-DIV'] = _("Attestation opération diverse de caisse");
$adsys["adsys_rapport"]['PIE-ECR'] = _("Pièce comptable passage ecriture");
$adsys["adsys_rapport"]['ATT-DBC'] = _("Attestation de déboursement de crédit par compte");
$adsys["adsys_rapport"]['ATT-RMC'] = _("Attestation de remboursement par compte");
$adsys["adsys_rapport"]['ECH-CRE'] = _("Echéancier credit");


// Rapports Budget
$adsys["adsys_rapport"]['BGT-RHB'] = _("Historique de révision budgétaire");
$adsys["adsys_rapport"]['BGT-EEB'] = _("Etat d’exécution budgétaire");
$adsys["adsys_rapport"]['BGT-RAB'] = _("Rapport budget");

//Rapports ATM
$adsys["adsys_rapport"]['ATM-CAI'] = _("Rapport des cartes à imprimer");
$adsys["adsys_rapport"]['ATM-CAA'] = _("Rapport des cartes à activer");
$adsys["adsys_rapport"]['ATM-LCA'] = _("Liste des cartes");
$adsys["adsys_rapport"]['ATM-TRA'] = _("Transaction ATM");

// Reçus (REC-*)
// Code = REC-* : Les reçus
$adsys["adsys_rapport"]['REC-DEE'] = _("Reçu dépôt en espèces");
$adsys["adsys_rapport"]['REC-SMS'] = _("Reçu frais transactionnel SMS");
$adsys["adsys_rapport"]['REC-DEC'] = _("Reçu dépôt par chèque");
$adsys["adsys_rapport"]['REC-REE'] = _("Reçu retrait en espèces");
$adsys["adsys_rapport"]['REC-INI'] = _("Reçu de perception du versement initial");
$adsys["adsys_rapport"]['REC-TFA'] = _("Reçu de perception tranche frais adhésion");
$adsys["adsys_rapport"]['REC-OUC'] = _("Reçu ouverture de compte");
$adsys["adsys_rapport"]['REC-DEF'] = _("Reçu suite à la défection");
$adsys["adsys_rapport"]['REC-REC'] = _("Reçu retrait par chèque");
$adsys["adsys_rapport"]['REC-SPS'] = _("Attestation de souscription des parts sociales");
$adsys["adsys_rapport"]['REC-LPS'] = _("Attestation de libération des parts sociales");
$adsys["adsys_rapport"]['REC-DBG'] = _("Reçu de déboursement au guichet");
$adsys["adsys_rapport"]['REC-DBC'] = _("Reçu de déboursement par chèque");
$adsys["adsys_rapport"]['REC-RMC'] = _("Reçu de remboursement au guichet");
$adsys["adsys_rapport"]['REC-CLC'] = _("Reçu de cloture de compte ");
$adsys["adsys_rapport"]['REC-CHG'] = _("Reçu de Change");
$adsys["adsys_rapport"]['REC-DIV'] = _("Reçu opération diverse de caisse");
$adsys["adsys_rapport"]['REC-FER'] = _("Reçu opération de recharge de Carte Ferlo");
$adsys["adsys_rapport"]['REC-TRC'] = _("Reçu Transfert entre compte");
$adsys["adsys_rapport"]['REC-HLC'] = _("Historisation dossier Ligne de crédit");
$adsys["adsys_rapport"]['REC-DAU'] = _("Bordereau de demande d'autorisation de retrait");
$adsys["adsys_rapport"]['REC-DAD'] = _("Bordereau de demande d'autorisation de retrait en déplacé");
$adsys["adsys_rapport"]['REC-DAT'] = _("Bordereau de demande d'autorisation de transfert");
$adsys["adsys_rapport"]['REC-PND'] = _("Bordereau de demande d'autorisation de commande Engrais Chimiques");
$adsys["adsys_rapport"]['REC-PNA'] = _("Bordereau de commande Engrais Chimiques");
$adsys["adsys_rapport"]['REC-PNP'] = _("Bordereau de paiement commande Engrais Chimiques");
$adsys["adsys_rapport"]['REC-BAP'] = _("Reçu approvisionnement caisse");
$adsys["adsys_rapport"]['REC-BDE'] = _("Reçu délestage");
$adsys["adsys_rapport"]['REC-BAA'] = _("Reçu demande approvisionnement caisse");
$adsys["adsys_rapport"]['REC-BDA'] = _("Reçu demande délestage");
if (isAgencyBanking()) {
  //Rapport du menu agency banking
  $adsys["adsys_rapport"]['AGB-TRA'] = _("Visualisation des approvisionnement/transfert compte de flotte");
  $adsys["adsys_rapport"]['AGB-TRS'] = _("Visualisation des transactions agents");
  $adsys["adsys_rapport"]['REC-RDA'] = _("Reçu dépôt en espèces via agent");
  $adsys["adsys_rapport"]['REC-RRA'] = _("Reçu retrait en espèces via agent");
  $adsys["adsys_rapport"]['AGB-RAA'] = _("Rapport sur agents");
  $adsys["adsys_rapport"]['AGB-RDC'] = _("Rapport des commissions");
  $adsys["adsys_rapport"]['AGB-RCA'] = _("Clients crées via agents ");
  $adsys["adsys_rapport"]['AGB-VCH'] = _("Visualisation des commissions historisées");
  $adsys["adsys_rapport"]['AGB-VCC'] = _("Visualisation création client");
//  $adsys["adsys_rapport"]['AGB-BDC'] = _("Brouillard de caisse journalier Agency Banking");
  $adsys["adsys_rapport"]['AGB-BDC'] = _("Brouillard du compte de flotte journalier");
  $adsys["adsys_rapport"]['AGB-RCH'] = _("Historique de modifications des commissions");
}

//transfert PS RECU
$adsys["adsys_rapport"]['REC-TPS'] = _("Attestation de transfert parts sociales");

//transfert PS DEMANDE
$adsys["adsys_rapport"]['DEM-TPS'] = _("Attestation de demande de transfert parts sociales");

//rapport Engrais Chimiques PNSEB
$adsys["adsys_rapport"]['PNS-SIT'] = _("Situation des paiements/commandes selon une période donnée");
$adsys["adsys_rapport"]['PNS-PLA'] = _("Liste des bénéficiaires ayant eu des autorisations de dépassement du plafond");
$adsys["adsys_rapport"]['PNS-LBP'] = _("Liste des bénéficiaires ayant payés dans une période donnée");
$adsys["adsys_rapport"]['PNS-RQZ'] = _("Répartition des quantités selon les zones");
$adsys["adsys_rapport"]['PNS-SPG'] = _("Globalisation Situation des paiements/commandes selon une période donnée");
$adsys["adsys_rapport"]['PNS-LPG'] = _("Globalisation Liste des bénéficiaires ayant payés dans une période donnée");
$adsys["adsys_rapport"]['PNS-RZG'] = _("Globalisation Répartition des quantités selon les zones");

//rapport etat de la compensation des operations en deplace
$adsys["adsys_rapport"]['SYS-REC'] = _("Rapport Etat de la Compensation des Opérations en déplacé");


if (isMobileLending()) {
//rapport mobile lending
  $adsys["adsys_rapport"]['MLE-NCL'] = _("Rapport nouveaux clients Mobile Lending");
  $adsys["adsys_rapport"]['MLR-DEB'] = _("Crédits déboursés en cours");
  $adsys["adsys_rapport"]['MLR-RET'] = _("Crédits en retard");
  $adsys["adsys_rapport"]['MLR-ACP'] = _("Crédits acceptés par agents");
  $adsys["adsys_rapport"]['MLR-REF'] = _("Crédits refusés par agents");
  $adsys["adsys_rapport"]['MLR-PER'] = _("Crédits en perte");
  $adsys["adsys_rapport"]['MLR-SOL'] = _("Crédits soldés");
}


// Etat d'un journal
$adsys["adsys_etat_journal"][1] = _("Ouvert");
$adsys["adsys_etat_journal"][2] = _("fermé");

//Destination du reste lors d'une opération de change
$adsys["adsys_change_dest_reste"][1] = _("Au guichet");
$adsys["adsys_change_dest_reste"][2] = _("Sur compte de base");
$adsys["adsys_change_dest_reste"][3] = _("Dans produits de l'agence");

//Type de pièce justificative
// Cette liste est verrouillée, pour ajouter de nouvelle pièce:
// aller au menu 'Parametrage' -> 'Gestion de la table de paramétrage' -> 'Types de pièces comptables'
$adsys["adsys_type_piece_payement"][1]= _("Espèce");
$adsys["adsys_type_piece_payement"][2]= _("Chèque extérieur");
$adsys["adsys_type_piece_payement"][3]= _("Ordre de paiement");
$adsys["adsys_type_piece_payement"][4]= _("Autorisation de retrait sans livret/chèque");
$adsys["adsys_type_piece_payement"][5]= _("Travelers cheque");
$adsys["adsys_type_piece_payement"][6]= _("Mise à disposition");
$adsys["adsys_type_piece_payement"][7]= _("Envoi argent");
$adsys["adsys_type_piece_payement"][8]= _("Reçu ADbanking");
$adsys["adsys_type_piece_payement"][9]= _("Facture");
$adsys["adsys_type_piece_payement"][10]= _("Extrait de compte");
$adsys["adsys_type_piece_payement"][11]= _("Reçu externe");
$adsys["adsys_type_piece_payement"][12]= _("Contrat");
$adsys["adsys_type_piece_payement"][13]= _("Bordereau");
$adsys["adsys_type_piece_payement"][14]= _("Opération Diverse");
$adsys["adsys_type_piece_payement"][15]= _("Chèque guichet");
$adsys["adsys_type_piece_payement"][16]= _("Ordre permanent");

//Etat de l'attente de crédit
$adsys["adsys_etat_ac"][1]= _("En attente");
$adsys["adsys_etat_ac"][2]= _("Envoyée");
$adsys["adsys_etat_ac"][3]= _("Acceptée");
$adsys["adsys_etat_ac"][4]= _("Refusée");
$adsys["adsys_etat_ac"][5]= _("A rembourser");
$adsys["adsys_etat_ac"][6]= _("Remboursée");

//Statut d'un transfert vers l'étranger
$adsys["adsys_statut_transferts_etranger"][1]= _("En attente");
$adsys["adsys_statut_transferts_etranger"][2]= _("Envoyé");
$adsys["adsys_statut_transferts_etranger"][3]= _("Confirmé");
$adsys["adsys_statut_transferts_etranger"][4]= _("Demande complément d'information");
$adsys["adsys_statut_transferts_etranger"][5]= _("Annulé");
$adsys["adsys_statut_transferts_etranger"][6]= _("Payé");

//Statuts des messages Swift
$adsys["adsys_statut_message_swif"][0]= _("En attente");
$adsys["adsys_statut_message_swif"][1]= _("Prêt à être exécuté");
$adsys["adsys_statut_message_swif"][2]= _("Erreur détectée, attente de décision de l'utilisateur");
$adsys["adsys_statut_message_swif"][3]= _("Exécuté");
$adsys["adsys_statut_message_swif"][4]= _("Rejeté");

//Types des ordres permanents
$adsys["adsys_type_ordre_permanent"][1]= _("Même client");
$adsys["adsys_type_ordre_permanent"][2]= _("Virement interne");
//$adsys["adsys_type_ordre_permanent"][3]= _("Virement externe"); pas encore actif

//Types d'objets internes
DEFINE ("GUI", 1);
DEFINE ("PRODEP", 2);
DEFINE ("PRODCR", 3);
DEFINE ("CPTA", 4);

//Type de pouvoir de signature
$adsys["adsys_type_pouv_sign"][1]= _("Seule");
$adsys["adsys_type_pouv_sign"][2]= _("Conjointe");

//Intitulés pour les extraits de compte
$adsys["adsys_intitule_extrait"][15] = _("DEFECTION DU CLIENT");
$adsys["adsys_intitule_extrait"][19] = _("ASSURANCE");
$adsys["adsys_intitule_extrait"][20] = _("SOUSCRIPTION DES PARTS SOCIALES");
$adsys["adsys_intitule_extrait"][30] = _("AJOUT DU CLIENT");
$adsys["adsys_intitule_extrait"][31] = _("PERCEPTION DES FRAIS D'ADHESION");
$adsys["adsys_intitule_extrait"][41] = _("COMMANDE DE CHEQUIER");
$adsys["adsys_intitule_extrait"][53] = _("OUVERTURE DU COMPTE");
$adsys["adsys_intitule_extrait"][54] = _("CLOTURE DU COMPTE");
$adsys["adsys_intitule_extrait"][70] = _("RETRAIT SUR LE COMPTE");
$adsys["adsys_intitule_extrait"][75] = _("DEPOT SUR LE COMPTE");
$adsys["adsys_intitule_extrait"][76] = _("TRANSFERT ENTRE COMPTES");
$adsys["adsys_intitule_extrait"][81] = _("RECHARGE D'UNE CARTE FERLO");
$adsys["adsys_intitule_extrait"][85] = _("RETRAIT EXPRESS SUR LE COMPTE");
$adsys["adsys_intitule_extrait"][86] = _("DEPOT EXPRESS SUR LE COMPTE");
$adsys["adsys_intitule_extrait"][87] = _("PERCEPTION DES FRAIS EN ATTENTE");
$adsys["adsys_intitule_extrait"][88] = _("MODIFICATION DU COMPTE");
$adsys["adsys_intitule_extrait"][105] = _("MISE EN PLACE D'UN DOSSIER DE CREDIT");
$adsys["adsys_intitule_extrait"][125] = _("DEBOURSEMENT D'UN DOSSIER DE CREDIT");
$adsys["adsys_intitule_extrait"][129] = _("CORRECTION D'UN DOSSIER DE CREDIT");
$adsys["adsys_intitule_extrait"][147] = _("REMBOURSEMENT D'UN DOSSIER DE CREDIT");
$adsys["adsys_intitule_extrait"][154] = _("RETRAIT SUR LE COMPTE");
$adsys["adsys_intitule_extrait"][158] = _("DEPOT SUR LE COMPTE");
$adsys["adsys_intitule_extrait"][159] = _("DEPOT SUR LE COMPTE");
$adsys["adsys_intitule_extrait"][160] = _("RECHARGE D'UNE CARTE FERLO");
$adsys["adsys_intitule_extrait"][188] = _("TRAITEMENT DES ATTENTES");
$adsys["adsys_intitule_extrait"][189] = _("OPERATION DIVERSE DE COMPTE");
$adsys["adsys_intitule_extrait"][190] = _("Souscription des parts sociales par lot via fichier");
$adsys["adsys_intitule_extrait"][212] = _("OPERATION AUTOMATIQUE");
$adsys["adsys_intitule_extrait"][235] = _("AJUSTEMENT DU SOLDE DU COMPTE");
$adsys["adsys_intitule_extrait"][470] = _("ECRITURE LIBRE");
$adsys["adsys_intitule_extrait"][471] = _("ECRITURE LIBRE");
$adsys["adsys_intitule_extrait"][501] = _("REPRISE DU COMPTE");
$adsys["adsys_intitule_extrait"][500] = _("REPRISE CLIENT");
$adsys["adsys_intitule_extrait"][600] = _("MISE EN PLACE D'UN DOSSIER LIGNE DE CREDIT");
$adsys["adsys_intitule_extrait"][604] = _("DEBOURSEMENT D'UN DOSSIER LIGNE DE CREDIT");
$adsys["adsys_intitule_extrait"][607] = _("REMBOURSEMENT D'UN DOSSIER LIGNE DE CREDIT");
//intilulés pour les rapport de la BNR
$adsys["adsys_rapport_BNR"][1]=_("Balance Sheet");
$adsys["adsys_rapport_BNR"][2]=_("Income Statement");
$adsys["adsys_rapport_BNR"][3]=_("Loans Granted to the Directors");
$adsys["adsys_rapport_BNR"][4]=_("Employees loans");
$adsys["adsys_rapport_BNR"][5]=_("Liquidity Ratio ");
$adsys["adsys_rapport_BNR"][6]=_("List of 10 Biggest Borrowers");
$adsys["adsys_rapport_BNR"][7]=_("Risks Situation by Sector");
$adsys["adsys_rapport_BNR"][8]=_("Recovery of Doubtful Loans, Litigious and Contingent Loans");
// type de paramètre jasper 
$adsys["adsys_jasper_type_param"]['txt']=_("Texte"); 
$adsys["adsys_jasper_type_param"]['dtg']=_("Date"); 
$adsys["adsys_jasper_type_param"]['int']=_("Entier"); 
$adsys["adsys_jasper_type_param"]['mnt']=_("Monétaire");
$adsys["adsys_jasper_type_param"]['lsb']=_("Liste déroulante");

//Gestion des types des champs
$adsys["adsys_type_champs"]['txt']=_("Texte");
$adsys["adsys_type_champs"]['dtg']=_("Date");
$adsys["adsys_type_champs"]['int']=_("Entier");
$adsys["adsys_type_champs"]['mnt']=_("Monétaire");
$adsys["adsys_type_champs"]['lsb']=_("Liste déroulante");

$adsys["adsys_jasper_format"]['pdf']=_("PDF");
$adsys["adsys_jasper_format"]['csv']=_("CSV");
//$adsys["adsys_jasper_format"]['DOC']=_("WORD");
$adsys["adsys_jasper_format"]['xls']=_("EXCEL");
//Etat d'un chèquier
$adsys["adsys_etat_chequier"][0] = _("En attente de livraison");
$adsys["adsys_etat_chequier"][1] = _("Prêt");
$adsys["adsys_etat_chequier"][2] = _("Terminé");
$adsys["adsys_etat_chequier"][3] = _("Supprimé");
$adsys["adsys_etat_chequier"][4] = _("Annulé");
$adsys["adsys_etat_chequier"][5] = _("Mise en opposition");
// statut chéquier
$adsys["adsys_statut_chequier"][0] = _("Inactif");
$adsys["adsys_statut_chequier"][1] = _("Actif");

//Etat d'un chèque
$adsys["adsys_etat_cheque"][1] = _("Encaissé");
$adsys["adsys_etat_cheque"][2] = _("Volé");
$adsys["adsys_etat_cheque"][3] = _("Perte");
$adsys["adsys_etat_cheque"][4] = _("Certifié");

// Etat commande chéquier
$adsys["adsys_etat_commande_chequier"][1] = _("A envoyer à l'impréssion");
$adsys["adsys_etat_commande_chequier"][2] = _("En attente d'impréssion");
$adsys["adsys_etat_commande_chequier"][3] = _("Imprimé");
$adsys["adsys_etat_commande_chequier"][4] = _("Annulé");
//$adsys["adsys_etat_commande_chequier"][4] = _("Terminé");

//Types opérations pour l'affichage des numéros des chèques.
$adsys["adsys_operation_cheque_infos"] = array(501,503,512,530,531,532,533,534,535,536,537,538,539);

// États calcul des calculs des intérêts a payer
$adsys["adsys_etat_calc_int"][1] = _("Calculé");
$adsys["adsys_etat_calc_int"][2] = _("Repris");

//Etat des saisons pour les Engrais Chimiques PNSEB
$adsys["adsys_etat_saison"][1]= _("En Cours");
$adsys["adsys_etat_saison"][2]= _("Fermé");

// type des produits de la PNSEB
$adsys["adsys_type_produit"][1]= _("Engrais");
$adsys["adsys_type_produit"][2]= _("Amendement");

// Etat des produits de la PNSEB
$adsys["adsys_etat_produit"][1]= _("Actif");
$adsys["adsys_etat_produit"][2]= _("Inactif");

// Type localisation de la PNSEB
$adsys["adsys_type_localisation"][1]= _("Province");
$adsys["adsys_type_localisation"][2]= _("Commune");
$adsys["adsys_type_localisation"][3]= _("Zone");
$adsys["adsys_type_localisation"][4]= _("Colline");


// Etat anne agricole
$adsys["adsys_etat_annee_agricole"][1]= _("Ouvert");
$adsys["adsys_etat_annee_agricole"][2]= _("Fermé");

// Etat commande PNSEB-FENACOBU
// Type localisation de la PNSEB
$adsys["adsys_etat_commande"][1]= _("Enregistré");
$adsys["adsys_etat_commande"][2]= _("En cours");
$adsys["adsys_etat_commande"][3]= _("Soldé");
$adsys["adsys_etat_commande"][4]= _("Non-soldé");
$adsys["adsys_etat_commande"][5]= _("Annulé");
$adsys["adsys_etat_commande"][6]= _("Attente de derogation");
$adsys["adsys_etat_commande"][7]= _("Commande en attente");
$adsys["adsys_etat_commande"][8]= _("Paiement en attente");

//Etat commande details
$adsys["adsys_etat_commande_detail"][1]= _("Paiement en attente");
$adsys["adsys_etat_commande_detail"][2]= _("Paiement effectué");


// Etat derogation commande
$adsys["adsys_etat_commande_derogation"][1]= _("Attente");
$adsys["adsys_etat_commande_derogation"][2]= _("Validé");
$adsys["adsys_etat_commande_derogation"][3]= _("Rejeté");
$adsys["adsys_etat_commande_derogation"][4]= _("Effectué");

//choix de la periode engrais chimiques
$adsys["adsys_choix_periode"][1]= _("Période des avances");
$adsys["adsys_choix_periode"][2]= _("Période des soldes");

//choix type de flux appro_delestage
$adsys["adsys_type_flux"][1]= _("Flux approvisionnement");
$adsys["adsys_type_flux"][2]= _("Flux delestage");

// Les types de rapport Budget
$adsys["adsys_type_budget"][1] = _("Budget opérationnel");
$adsys["adsys_type_budget"][2] = _("Budget d’investissement");
$adsys["adsys_type_budget"][3] = _("Budget de financement");

// Les types de validation budget
$adsys["adsys_type_validation_budget"][1] = _("Budget raffiné");
$adsys["adsys_type_validation_budget"][2] = _("Budget revisé");

// Les types de periode pour rapport budget
$adsys["adsys_type_periode_budget"][1] = _("Annuel");
$adsys["adsys_type_periode_budget"][2] = _("Trimestriel");

$adsys["type_operation_frais_sms"] = array(10,11,20,21,30,31,40,62,70,80,81,90,100,110,120,123,124,140,160,182,201,210,211,220,221,230,231,330,360,361,370,410,411,420,421,440,442,471,508,510,512);


// Les etats de la compensation au siege automatique
$adsys["adsys_etat_compensation_siege_auto"]["t"] = _("Réussi");
$adsys["adsys_etat_compensation_siege_auto"]["f"] = _("Echoué");

// les différents type identification client dans la table agence
$adsys["adsys_identification_client"][1] = _("Standard");
$adsys["adsys_identification_client"][2] = _("Rwanda");

// les differents type de localisation pour le rwanda
$adsys["type_localisation_rwanda"][1] = _("Province");
$adsys["type_localisation_rwanda"][2] = _("District");
$adsys["type_localisation_rwanda"][3] = _("Secteur");
$adsys["type_localisation_rwanda"][4] = _("Cellule");
$adsys["type_localisation_rwanda"][5] = _("Village");


// Agency Banking : Compte commission agent
$adsys["cpt_comm_agent"][1] = _("Compte de flotte");
$adsys["cpt_comm_agent"][2] = _("Compte de base");

//Agency Banking : Type de commissions
$adsys["type_comm"][1] = _("Commission sur retrait");
$adsys["type_comm"][2] = _("Commission sur dépôt");
$adsys["type_comm"][3] = _("Commission sur nouveau client");
$adsys["type_comm"][4] = _("Compte de commission intermédiaire");

//Agency Banking : prise en charge de commissiion de l'agent
$adsys["comm_agent_par_inst"][1] = _("Commission supportée par l'institution");
$adsys["comm_agent_par_inst"][2] = _("Commission supportée par le client");

//Agency Banking : type d'approvisionnement/transfert agent
$adsys["type_appro_agent"][1] = _("Approvisionnement par solde disponible du compte courant");
$adsys["type_appro_agent"][2] = _("Approvisionnement par montant deposé à la banque");
$adsys["type_appro_agent"][3] = _("Transfert compte de flotte vers compte courant");

//Agency Banking : Etat de demande d'approvisionnement /transfert
$adsys["etat_appro_trans"][1] = _("Enregistré");
$adsys["etat_appro_trans"][2] = _("Validé");
$adsys["etat_appro_trans"][3] = _("Effectué");
$adsys["etat_appro_trans"][4] = _("Rejeté");

//Agency Banking : Depot via agent
$adsys["type_depot_agent"][1] = _("Epargne libre");


// Agency Banking : Etat Demande d'annulation Depot/retait
$adsys["etat_annul_depot_retrait_agent"][1] = _("Enregistré");
$adsys["etat_annul_depot_retrait_agent"][2] = _("Autorisé");
$adsys["etat_annul_depot_retrait_agent"][3] = _("Rejeté");
$adsys["etat_annul_depot_retrait_agent"][4] = _("Effectué");

$adsys["type_transaction"][1] = _("Insertion d'un palier");
$adsys["type_transaction"][2] = _("Modifcation d'un palier");
$adsys["type_transaction"][3] = _("Effacement d'un palier");
$adsys["type_transaction"][4] = _("Affichage d'un palier");

//AT-106 :  les differents prelevement de garanti
$adsys["adsys_prelev_garanti"][1] = _("Lors de la mise en place");
$adsys["adsys_prelev_garanti"][2] = _("Lors du déboursement");

/************************* AT - 186 **********************************/
$adsys["adsys_interet_differe_echeance"][1] = _("Sur intérêt");

/************************* ML-46 **********************************/
$adsys["adsys_localisation_ml"][1] = _("Urbaine");
$adsys["adsys_localisation_ml"][2] = _("Rurale");

/************************* Mobile Lending **********************************/
$adsys["etat_demande_mobile_lending"][1] = _("En attente de décision");
$adsys["etat_demande_mobile_lending"][2] = _("Deboursé Automatiquement");
$adsys["etat_demande_mobile_lending"][3] = _("Remboursement terminé");
$adsys["etat_demande_mobile_lending"][4] = _("En attente de l’agent");
$adsys["etat_demande_mobile_lending"][5] = _("Deboursé par l’agent");
$adsys["etat_demande_mobile_lending"][6] = _("Refusé par l’agent");
$adsys["etat_demande_mobile_lending"][7] = _("Erreur fonctionelle");
$adsys["etat_demande_mobile_lending"][8] = _("Refusé, crédit déjà en cours");
$adsys["etat_demande_mobile_lending"][9] = _("Refusé, crédit déjà en attente");
$adsys["etat_demande_mobile_lending"][10] = _("Déboursé Automatiquement pour client salarié");
$adsys["etat_demande_mobile_lending"][11] = _("Deboursé par l’agent pour client salarié");

/************************* AT-145 **********************************/
$adsys["adsys_type_mode_calcul_frais_inactif"][1]= _("Ne pas calculer ces frais");
$adsys["adsys_type_mode_calcul_frais_inactif"][2]= _("Calculer ces frais  uniquement si le compte a un solde pour les couvrir");
$adsys["adsys_type_mode_calcul_frais_inactif"][3]= _("Calculer ces frais infiniment");

$adsys["adsys_type_mode_calcul_frais_dormant"][1]= _("Ne pas calculer ces frais");
$adsys["adsys_type_mode_calcul_frais_dormant"][2]= _("Calculer ces frais  uniquement si le compte a un solde pour les couvrir");
$adsys["adsys_type_mode_calcul_frais_dormant"][3]= _("Calculer ces frais infiniment");
$adsys["statut_abonnement_atm"][1] = _("En attente d'activation");
$adsys["statut_abonnement_atm"][2] = _("Actif");
$adsys["statut_abonnement_atm"][3] = _("Inactif");


$adsys["motif_demande"][1] = _("Première demande de carte sur le compte");
$adsys["motif_demande"][2] = _("Remplacement carte expiré");
$adsys["motif_demande"][3] = _("Remplacement carte volée / perdu");
$adsys["motif_demande"][4] = _("Autre");

$adsys["etat_carte_atm"][1] = _("A envoyer pour impression");
$adsys["etat_carte_atm"][2] = _("En attente impression");
$adsys["etat_carte_atm"][3] = _("A activer / Imprimée");
$adsys["etat_carte_atm"][4] = _("Annulé");
$adsys["etat_carte_atm"][5] = _("Activé");
$adsys["etat_carte_atm"][6] = _("Désactivé / Suspendu");
$adsys["etat_carte_atm"][7] = _("Perdue / Volée");
$adsys["etat_carte_atm"][8] = _("Expirée");
$adsys["etat_carte_atm"][9] = _("Fin de vie / Supprimé / EOL");

$adsys["type_prestataire"][1] = _("SMS");
$adsys["type_prestataire"][2] = _("ATM");

$adsys["motif_inactif"][1] = _("Perdue");
$adsys["motif_inactif"][2] = _("Volée");
$adsys["motif_inactif"][3] = _("Autre");

// Type Agence
$adsys["type_niveau_agence"][1]= _("Coopec");
$adsys["type_niveau_agence"][2]= _("Agence");
$adsys["type_niveau_agence"][3]= _("Guichet");
?>
