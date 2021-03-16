<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Enregistrement des variables globales dans la session
 * Les session_register démarrent une session
 * Il faut donc _toujours_ faire le require_once "VariablesSession.php" après un session_name("ADbanking") !
 * @package Systeme
 */

/* La session doit être nommée avant tout enregistrement de variable dedans ! */
/* Un session_register démarre une session, si elle n'est pas nommée, elle aura pour nom PHPSESSID */
/* Attention aussi à la déclaration de $SESSION_VARS[] en dehors de ce fichier */
session_name("ADbanking");

session_register("global_institution"); //Nom de l'institution
session_register("global_agence"); //Nom de l'agence
session_register("global_id_agence"); //Identificateur de l'agence
session_register("global_niveau_max"); //Niveau maximum des comptes comptables
session_register("global_statut_agence"); //Statut de l'agence
session_register("global_monnaie"); //Abrev. de l'unité monétaire (ex. FCFA)
session_register("global_monnaie_prec"); //Précision de la devise de référence (chiffres après virgule)
session_register("global_monnaie_courante"); // Devise actuellement utilisée
session_register("global_monnaie_courante_prec"); // Précision de la devise actuellement utilisée
session_register("global_multidevise"); // Travaille-t-on en mode multidevise ?
session_register("global_guichet"); //Nom du guichet
session_register("global_id_guichet"); //Identificateur du guichet
session_register("global_id_utilisateur"); //Identificateur de l'utilisateur
session_register("global_nom_utilisateur"); //Nom complet de l'utilisateur (prénom nom)
session_register("global_nom_login"); //Login utilisé
session_register("global_id_profil"); //Identificateur du profil du login utilisé
session_register("global_profil_axs"); //Array contenant toutes les fonctions auquel à droit le profil loggé
session_register("global_timeout"); //Timeout associé au profil du login
session_register("global_client"); //Prénom & nom du client
session_register("global_id_client"); //Identificateur du client
session_register("global_id_client_formate"); //Identificateur du client formaté (sur 6 caractères)
session_register("global_photo_client"); // Nom du fichier contenant la photo du client
session_register("global_signature_client"); // Nom du fichier contenant la specimen de signature du client
session_register("global_etat_client"); // Contient l'état du client (2 = actif)
session_register("global_cpt_base_ouvert"); // Le compte de base est-il ouvert ?
session_register("global_depot_bloque"); // Les depots sont-ils bloqués? ?
session_register("global_retrait_bloque"); // Les retraits sont-ils bloqués ?
session_register("global_nom_ecran"); // Nom de l'écran actuel
session_register("global_nom_ecran_prec"); //nom de l'écran précédent
session_register("global_billet_req"); // Indique si le billettage est requis ou non
session_register("global_billets"); // Billets définits; contient un sous-tableau indexé sur l'id des billets et contenants 2 champs : 'libel' et 'valeur'
session_register("global_last_axs"); //Heure du dernier accès stocké en secondes depuis epoch
session_register("global_menus_struct"); //Structure du menu affiché dans le frame gauche
session_register("global_ecrans_struct"); //Structure des écrans affiché dans le frame gauche
session_register("global_client_debiteur"); //indique si le compte de base du client est débiteur (booléen)
session_register("global_caissier_central"); //indique si l'utilisateur gère la caisse centrale (booléen)
session_register("global_have_left_frame"); //Doit-on afficher le frame gauche pour ce login ?
session_register("global_modif_pwd_login");//indique si le mot de passe doit etre modifier lors de la connexion
//doit-on afficher une alerte pour un client dont un DAT arrive à échéance ?
session_register("global_alerte_DAT");

// Niveau de retard d'un crédit ?
session_register("global_credit_niveau_retard");

// Le client doit-t-il payer l'épargne obligatoire ?
session_register("global_cli_epar_obli");

// Le client possède-t-il un crédit pour lesquelles le décompte des pénalités est suspendu ?
session_register("global_suspension_pen");

session_register("global_type_structure"); // Type de structure (ad_agc)

session_register("global_id_exo"); //exercice comptable en cours

session_register("global_langue_utilisateur"); // Langue dans laquelle doit d'afficher l'interface utilisateur
session_register("global_langue_rapport"); // Langue dans laquelle le rapport ou reçu doit s'imprimer
session_register("global_langue_systeme_dft"); // Langue système par défaut

session_register("global_mode_agence"); // Mode de l'agence par défaut
session_register("global_acces_allowed"); // Global access to ADBanking

?>
