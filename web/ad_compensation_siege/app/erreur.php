<?php
/**
 * Gestion des erreurs "internes"  erreurs "utilisateur".
 * @package Systeme
 */

//require_once 'lib/misc/VariablesGlobales.php';
//require_once 'lib/misc/VariablesSession.php';
//require_once 'lib/dbProcedures/historique.php';

/**
 * Signale une erreur interne à l'utilisateur.
 * Appel typique :<br>
 *   <code>signalErreur(__FILE__,__LINE__,__FUNCTION__, $error[$myErr->errCode].$myErr->param);</code><br>
 * Attention à bien placer le tableau global $error dans le contexte de la fonction courante.
 * @author ADbanking
 * @since 1.0?
 * @param char $fichier Le fichier dans lequel l'erreur s'est produite
 * @param int $ligne La ligne du fichier dans laquelle l'erreur s'est produite
 * @param char $fonction La fonction dans laquelle l'erreur s'est produite (default: '')
 * @param char $opt <em>Paramètre optionel</em> Un message d'erreur à afficher à destination de l'utilisateur.
 * @return die
 */
function signalErreur($fichier, $ligne, $fonction='') {
  global $global_id_agence;
  global $colb_tableau;
  global $colt_error;
  global $ProjectName;
  global $global_statut_agence;
  global $db;
  global $appli;
  global $global_nom_login;
  global $global_id_client;
  global $REMOTE_ADDR;
  global $log_path;

  if ($appli == "batch") { //Si on est dans le batch
    echo "<FONT color=$colt_error>";
    echo getLevelStr();
    printf(_("[%s] Erreur dans la fonction <strong>%s</strong> (ligne %d du fichier %s)"), date("H:i:s  d/m/Y"), $fonction, $ligne, $fichier);
    if (func_num_args()>3) {
      echo " - <em>".func_get_arg(3)."</em>";
    }
    echo "</FONT>";

    //Si possible on termine la db (mais si DB error précédemment alors ce n'est plus possible)
    if (method_exists($db, "query")) $result = $db->query("ROLLBACK");  //Termine la transaction
    if (method_exists($db, "disconnect")) $db->disconnect();  //Déconnecte de la base de données

    //Fin
    die("<hr />"._("Annulation des traitements déjà réalisés (ROLLBACK) et arrêt du batch !")."<br />");
  } else { //Si on est dans l'application
    echo "<h1 align=center>"._("Erreur interne")." $ProjectName</h1><br><br>";
    echo "<TABLE align=center>";
    echo "<TR bgcolor=$colb_tableau>";
    echo "<TD colspan=2 align=center><FONT color=$colt_error>"._("Une erreur interne s'est produite !")."</FONT></TD></TR>";
    echo "<TR bgcolor=$colb_tableau>";
    echo "<TD align=left>"._("Fichier")."</TD><TD>$fichier</TD></TR>";
    echo "<TR bgcolor=$colb_tableau>";
    echo "<TD align=left>"._("Ligne")."</TD><TD>$ligne</TD></TR>";
    echo "<TR bgcolor=$colb_tableau>";
    echo "<TD align=left>"._("Fonction")."</TD><TD>$fonction</TD></TR>";
    if (func_num_args()>3) {
      echo "<TR bgcolor=$colb_tableau>";
      echo "<TD align=left>"._("Erreur")."</TD><TD>".func_get_arg(3)."</TD></TR>";
    }
    echo "</TABLE>";

    // Ajout dans le fichier log
    $fich = fopen("$log_path/error.log", 'a');
    $text = "[".date("r")."] "._("ERREUR INTERNE ADbanking");
    $text .= "\n\t "._("login")." : $global_nom_login";
    $text .= "\n\t "._("Adresse IP")." : $REMOTE_ADDR";
    $text .= "\n\t "._("ID client")." : $global_id_client";
    $text .= "\n\t "._("Fichier")." : $fichier";
    $text .= "\n\t "._("Ligne")." : $ligne";
    $text .= "\n\t "._("Fonction")." : $fonction";
    if (func_num_args()>3) {
      $text .= "\n\t "._("Erreur")." : ".func_get_arg(3);
    }
    $text .= "\n\n";
    fwrite($fich, $text);
    fclose($fich);

    // Ajout des informations supplémentaire si Xdebug installé
    if (function_exists('xdebug_enable')) {
      global $DEBUG;
      if ($DEBUG) {
        $output = "\n<pre>*******************************************************************************************************\n";
        $output .= sprintf(_("Erreur à la ligne %s du fichier %s"),"<b>".xdebug_call_line()."</b>","<b>".xdebug_call_file()."</b>")."\n";
        $output .= "\n-----\n"._("Pile d'appel des fonctions")."\n";
        echo $output."</pre>";
        var_dump(xdebug_get_function_stack()); // Pile d'appel des fonctions
        $tracefile_name = xdebug_get_tracefile_name();
        $profilingfile_name = xdebug_get_profiler_filename();
        $output = "<pre>\n";
        if ($tracefile_name != '') {
          $output .= "\n-----\n"._("La trace complète de la pile se trouve dans le fichier").": <b>".$tracefile_name."</b>\n";
        }
        if ($profilingfile_name != '') {
          $output .= "\n"._("Les diagrammes de profilage (à utiliser avec cachegrind) se trouve dans le fichier").": <b>".$profilingfile_name."</b>\n";
        }
        $output .= "\n*******************************************************************************************************\n</pre>";
        echo $output;
        xdebug_dump_superglobals(); // Affichage des variables super-globale (Voir xdebug.ini)
      }
    }

    die();
  }
}

function sendMsgConfirmation($titre,$msg,$ecran_retour) {
  $MyPage = new HTML_message($titre);
  $MyPage->setMessage($msg);
  $MyPage->addButton(BUTTON_OK, $ecran_retour);

  $MyPage->buildHTML();
  echo $MyPage->HTML_code;
}



//fonction utilaire
function sendMsgErreur ($titre,$errObj,$ecran_retour ) {
  global $error;
  if(is_object($errObj)){
    $param = $errObj->param;debug($errObj->errCode);
    $msg = _("Erreur")." : ".$error[$errObj->errCode];
    if ($param != NULL) {
      if(is_array($param)) {
        foreach($param as $key => $val) {
          $msg .= "<br /> ".$key." : ".$param["$key"]."";
        }
      }else {
        $msg .=  $param;
      }
    }
  } else {
    $msg=$errObj;
  }

  $html_err = new HTML_erreur($titre);
  $html_err->setMessage($msg);
  $html_err->addButton("BUTTON_OK", $ecran_retour);
  $html_err->buildHTML();
  echo $html_err->HTML_code;
  exit(0);
}

/**
 * Erreurs utilisateur, définition des constantes d'erreur.
 */

define("NO_ERR", 0);

define("ERR_CPTE_CRED_NEG", 1);
define("ERR_CPTE_DEB_POS", 2);
define("ERR_CPTE_BLOQUE", 3);
define("ERR_CPTE_SOLDE_NON_NUL", 4);
define("ERR_DUPLICATE_CHEQUE", 5);
define("ERR_CPTE_FERME", 6);
define("ERR_CPTE_ATT_FERM", 7);
define("ERR_CPTE_INEXISTANT", 8);
//define("ERR_CPTE_ORD_PERM", 17);
define("ERR_CPTE_BLOQUE_DEPOT", 9);
define("ERR_CPTE_BLOQUE_RETRAIT", 14);

define("ERR_CPTE_GUI_POS",10);
define("ERR_CPTE_CHQ_POS",11);
define("ERR_CPTE_CPT_POS",12);
define("ERR_CPTE_CLI_POS",13);
define("ERR_CPTE_CLI_BASE_NEG",19);
define("ERR_CPTE_CLI_NEG",20);
define("ERR_CPTE_CPT_NEG",21);

define("ERR_CPTE_OUVERT", 30);
define("ERR_CPTE_DORMANT", 31);
define("ERR_CPTE_PART_SOC", 32);
define("ERR_CPTE_GARANTIE", 33);

define("ERR_DB_SQL", 99);

define("ERR_PFH_DEJA_PAYE", 101);
define("ERR_SPS_ETAT_EAV", 111);
define("ERR_DEPOT_UNIQUE", 121);
define("ERR_RETRAIT_UNIQUE", 122);
define("ERR_MNT_MAX_DEPASSE", 123);
define("ERR_MNT_MIN_DEPASSE", 124);
define("ERR_CHAMP_NON_SAISI", 125);
define("ERR_DUREE_MIN_RETRAIT", 126);
define("ERR_CLIENT_INEXISTANT", 127);
define("ERR_CLOTURE_NON_AUTORISEE", 128);
define("ERR_SOLDE_INSUFFISANT", 129);
define("ERR_MANDAT_INSUFFISANT", 130);
define("ERR_CHEQUE_EN_INSTANCE", 140);

define("ERR_ID_CLIENT_NON_EXIST",141);
define("ERR_MNT_PS",142);
define("ERR_NBRE_MAX_PS",143);
define("ERR_NBRE_MAX_PS_LIBERER",144);
define("ERR_NBRE_MAX_PS_DESTINATAIRE",145);
define("ERR_MAX_PS_SOUSCRIT_AGC",146);


define("ERR_DEF_CPT_EXIST", 150);
define("ERR_DEF_SLD_NON_NUL", 151);
define("ERR_DEF_AUTRE_CLIENT_EPNANTIE", 152);
define("ERR_DEF_CLIENT_EAV", 153);
define("ERR_CLIENT_NON_EAED", 160);
define("ERR_CLIENT_EXIST_AD", 161);

define("ERR_CLIENT_DEBITEUR", 170);
// chèquier
define("ERR_CMD_CHEQUIER_ENCOURS", 180);
define("ERR_NO_CMD_CHEQUIER", 181);
define("ERR_NO_CHEQUIER", 182);
define("ERR_NO_CHEQUIER_IMPR", 182);
define("ERR_NO_CHEQUE", 183);
define("ERR_CHEQUE_USE", 184);
define("ERR_CHEQUIER_INACTIF", 185);
define("ERR_CHEQUIER_SERIE_EXIST", 186);
define("ERR_CHEQUE_OPPOSITION", 187);
define("ERR_CMD_CHEQUIER_MAX_CARNETS", 188);

define("ERR_CARTE_ACTIVE", 190);

define("ERR_CRE_NO_DOSS", 200);
define("ERR_CRE_ASS_DIFF_SOLDE", 201);
define("ERR_GAR_ETAT_INCORRECT", 202);
define("ERR_CRE_MNT_TROP_ELEVE", 211);
define("ERR_CRE_NO_ECH", 212);

define("ERR_CRE_PEN_TROP_ELEVE", 221);

define("ERR_CRE_MNT_DEB_TROP_ELEVE", 231);
define("ERR_CRE_DEST_DEB_INCONNU", 232);

define("ERR_HIS_NO_DEB", 300);
define("ERR_SOURCE_FRAIS_RETRAIT", 311);
define("ERR_FRAIS_DEBIT_NON_PERCU", 312);

define("ERR_LOC_EXIST_CHILD", 321);
define("ERR_LOC_EXIST_CLIENT", 322);

define("ERR_EMAIL_EXIST", 350);
define("ERR_CPT_EXIST", 351);
define("ERR_NO_ASSOCIATION", 352);
define("ERR_CPT_RESULTAT_EXIST", 353);
define("ERR_CPT_ECRITURE_EXIST", 354);
define("EXIST_ECR_ATT", 355);
define("ERR_DATE_NON_VALIDE", 356);
define("ERR_SOLDES_DIFFERENTS", 357);
define("ERR_CPT_CENTRALISE", 358);
define("ERR_SOLDE_MAL_REPARTI", 359);
define("ERR_DEJA_PRINC_JOURNAL", 360);
define("ERR_DEJA_CONTREPARTIE", 361);
define("ERR_JOU_EXISTE", 362);
define("ERR_JOU_NON_SUPPRIMABLE", 363);
define("ERR_ECR_NON_VALIDE", 364);
define("ERR_CPTE_ETAT_CRE_NON_PARAMETRE", 365);
define("ERR_PAS_CPTE_LIAISON", 366);
define("ERR_CPTE_RESULT_NON_DEF", 367);
define("ERR_CPTE_NON_PARAM", 368);
define("ERR_ECR_ATTENTE_VALID", 369);
define("ERR_MODIF_ETAT_CRE", 370);
define("ERR_CPTE_CRE_ATT_DEB_NON_PARAMETRE", 371);

define("ERR_EXIST_REFERENCE", 401);
define("ERR_TROP_RECORDS", 402);
define("ERR_TIMEOUT_INVALID", 403);
define("ERR_NO_RECORDS", 404);

define("ERR_GUICHET_OUVERT", 451);

define("ERR_ANNULATION", 500);

define("ERR_DAT_NON_PROLONGEABLE",501);
define("ERR_DAT_NBRE_PROLONGATIONS_MAX_ATTEINT",502);
define("ERR_NON_CAT",503);
define("ERR_MNT_NON_EQUIV",504);

// Erreurs dépôt par lot via fichier
define("ERR_FICHIER_DONNEES", 511);
define("ERR_NBR_COLONNES", 512);
define("ERR_NUM_COMPLET_CPTE", 513);
define("ERR_ID_CLIENT", 514);
define("ERR_DEVISE_CPTE", 515);
define("ERR_MONTANT", 516);
define("ERR_NUM_COMPLET_CPTE_NOT_EXIST", 517);
define("ERR_NBR", 518);
//erreurs parametrage rapport comptabilité
define("ERR_NON_ID_PERE",530);
define("ERR_NUM_COMPART_NON_VALIDE",531);
// Erreurs multidevise
define("ERR_DEVISE_NOT_EXIST", 601);
define("ERR_DEV_DIFF_CPT_CENTR", 602);
define("ERR_MULTIDEV_ECR_EXIST", 603);
define("ERR_POS_CHANGE_NOT_EXIST", 604);
define("ERR_DOSSIER_NOT_EXIST", 605);
define("ERR_DEVISE_CPT_DIFF", 606);
define("ERR_CPTE_ABSENT", 607);
define("ERR_SWIFT_NON_VALIDE", 608);
define("ERR_NO_DEVISE", 609);
define("ERR_CPT_MONODEVISE", 610);
define("ERR_DEVISE_CPT", 611);
define("ERR_DEVISE_CPT_INT", 612);
define("ERR_DEV_DIFF_CPT_PROV", 613);
define("ERR_TAUX_CHANGE_DIFF_MULTIAGENCE", 614);
define("ERR_DEVISE_REF_DIFF_MA", 615);
define("ERR_FLAG_PRELEV_COMM_DIFF_MA", 616);

// Erreurs licence
define("ERR_LIC_FIC", 701);
define("ERR_LIC_AGC", 702);
define("ERR_LIC_EXP", 703);
define("ERR_LIC_CLI", 704);

// Erreurs paramétrage avant batch
define("ERR_PARAM_CPT_INT", 711);
define("ERR_PARAM_CPT_ASS", 712);
define("ERR_PARAM_OPE", 713);
define("ERR_PARAM_AGC", 714);

//Erreurs paramétrage avant consolidation
define("ERR_NOM_BASE", 750);
define("ERR_AUCUN_FICHIER_DUMP", 751);


// Erreurs système 951 -> 999
define("ERR_PSQL_DUMP", 951);
define("ERR_GZIP", 952);
define("ERR_PATH", 953);

/*
 Erreur générique.Cette erreur est définie dans le but de la suppression de certains appels à signalErreur.
 Il faudrait définir d'autres objets erreurs plus explicites
*/
define("ERR_GENERIQUE", 1000);


// Match entre code d'erreur et description
// *** Création d'un tableau $error de type $error[code] = description ***
$error = array();

// 1 -> 100 : Procédures stockées bas-niveau (débit crédit de comptes)
$error[ERR_CPTE_SOLDE_NON_NUL] = _("Le solde du compte n'est pas nul au moment de sa cloture");
$error[ERR_CPTE_INEXISTANT] = _("Le compte n'existe pas");
$error[ERR_CPTE_BLOQUE] = _("Le compte est bloqué");
$error[ERR_CPTE_BLOQUE_DEPOT] = _("Le compte est bloqué pour dépôt");
$error[ERR_CPTE_BLOQUE_RETRAIT] = _("Le compte est bloqué pour retrait");
$error[ERR_CPTE_FERME] = _("Le compte est fermé");
//$error[ERR_CPTE_ORD_PERM] = _("Le compte est lié à un ordre permanent actif. Veuillez supprimer l'ordre pour continuer le blocage.");
$error[ERR_CPTE_ATT_FERM] = _("Le compte est en attente de fermeture");
$error[ERR_DUPLICATE_CHEQUE] = _("Le chèque existe déjà dans la base de données");
$error[ERR_CPTE_DEB_POS] = _("Le solde du compte naturellement débiteur va devenir créditeur : ");
$error[ERR_CPTE_CRED_NEG] = _("Le solde du compte naturellement créditeur va devenir débiteur : ");
$error[ERR_CPTE_GUI_POS] = _("Le montant de l'encaisse est insuffisant pour cette opération");  //"Le solde du compte guichet va devenir créditeur";
$error[ERR_CPTE_CHQ_POS] = _("Le solde du compte chèque va devenir créditeur");
$error[ERR_CPTE_CPT_POS] = _("Le solde du compte comptable débiteur va devenir créditeur");
$error[ERR_CPTE_CLI_NEG] = _("Le solde du compte client est insuffisant pour cette opération"); // va devenir débiteur";
$error[ERR_CPTE_CPT_NEG] = _("Le solde du compte comptable créditeur va devenir débiteur");
$error[ERR_CPTE_CLI_POS] = _("Le solde du compte client débiteur est insuffisant pour cette opération"); //va devenir créditeur";
$error[ERR_CPTE_CLI_BASE_NEG] = _("Le solde du compte de base est insuffisant pour cette opération");  //ou va devenir débiteur";
$error[ERR_CPTE_OUVERT] = _("Le compte est ouvert");
$error[ERR_CPTE_DORMANT] = _("Le compte est dormant");
$error[ERR_CPTE_PART_SOC] = _("Le compte est un compte de part sociale");
$error[ERR_CPTE_GARANTIE] = _("Le compte est un compte de garantie");

$error[ERR_DB_SQL] = _("La requête SQL a généré une erreur");

// 101  : PS Perception de frais d'adhésion
$error[ERR_PFH_DEJA_PAYE] = _("Le client a déjà payé les frais d'adhésion");

// 111  : PS Souscription des parts sociales
//$error[SPS_ETAT_EAV] = _("L'état du client est \"en cours de validation\", il doit donc d'abord payer les frais d'adhésion avant de pouvoir souscrire des parts sociales");

//// 141 : PS Souscription des parts sociales par lot via fichier
$error[ERR_ID_CLIENT_NON_EXIST] = _("Le numéro de client  ne correspond à aucun client valide");
$error[ERR_MNT_PS] = _("Le montant  des part sociales doit être multiple de la valeur nominale de la part sociale");
$error[ERR_NBRE_MAX_PS]=_("Vous ne pouvez pas depasser  le nombre  maximum de part sociale pour un client");
$error[ERR_NBRE_MAX_PS_LIBERER]=_("Vous ne pouvez pas libérer un nombre de PS depassant le nombre souscrite par le client");
$error[ERR_NBRE_MAX_PS_DESTINATAIRE]=_("Le nombre max de PS sociale est déjå atteint pour le client destinataire. Cette demande ne peut pas etre mise en place.");
$error[ERR_MAX_PS_SOUSCRIT_AGC]=_("Le max de souscription est deja atteint pour cette agence");




// 151 -> 159 : PS défection clients
$error[ERR_DEF_CPT_EXIST] = _("Des comptes subsistent pour ce client");
$error[ERR_DEF_SLD_NON_NUL] = _("Le solde du compte de base n'est pas nul");
$error[ERR_DEF_AUTRE_CLIENT_EPNANTIE] = _("Crédit dont le compte de garantie appartient à un autre client");
$error[ERR_DEF_CLIENT_EAV] = _("Client non actif En Attente de Validation");
$error[ERR_DEPOT_UNIQUE] = _("Ce compte est à dépôt unique");
$error[ERR_RETRAIT_UNIQUE] = _("Ce compte est à retrait unique");
$error[ERR_MNT_MAX_DEPASSE] = _("Le solde va dépasser le montant maximum pour ce compte");
$error[ERR_MNT_MIN_DEPASSE] = _("Le solde va passer en dessous du montant minimum pour ce compte");
$error[ERR_CHAMP_NON_SAISI] = _("Vous devez saisir une valeur");
$error[ERR_SOLDE_INSUFFISANT] = _("Le solde est insuffisant pour cette opération");
$error[ERR_MANDAT_INSUFFISANT] = _("La limitation du mandat est insuffisante pour cette opération");
$error[ERR_DUREE_MIN_RETRAIT] = _("La durée minimum entre deux retraits n'est pas atteinte");
$error[ERR_CLIENT_INEXISTANT] = _("Client inexistant dans la base");
$error[ERR_CLOTURE_NON_AUTORISEE] = _("La clôture de ce compte n'est pas autorisée");
$error[ERR_CHEQUE_EN_INSTANCE] = _("Un ou plusieurs chèques sont en instance d'encaissement ou de rejet");

// 160 -> 169 : PS defection client décédé sans ayant-droit
$error[ERR_CLIENT_NON_EAED] = _("Ce client n'est pas en attente d'enregistrement de décès");
$error[ERR_CLIENT_EXIST_AD] = _("Un ayant-droit existe pour ce client");

// 170
$error[ERR_CLIENT_DEBITEUR] = _("Ce client est débiteur : opération impossible");

// 180-189 : Chèquiers
$error[ERR_CMD_CHEQUIER_ENCOURS] = _("Une commande de chéquier est déjà en cours pour ce compte");
$error[ERR_NO_CMD_CHEQUIER] = _("Aucune commande de chéquier n'a été enregistrée pour ce compte");
$error[ERR_NO_CHEQUIER_IMPR] = _("Aucun chéquier n'a été imprimé pour ce compte");
$error[ERR_NO_CHEQUE] = _("Aucun chèque ne correspondant pour ce compte");
$error[ERR_CHEQUE_USE] = _("Ce chèque est déjà utilisé");
$error[ERR_CHEQUIER_INACTIF] = _("Le chéquier n'est pas actif");
$error[ERR_CHEQUIER_SERIE_EXIST] = _("Le numéro de série du chequier existe dèjà <br>");
$error[ERR_CHEQUE_OPPOSITION] = _("Le chèque est mise en opposition");
$error[ERR_CMD_CHEQUIER_MAX_CARNETS] = _("Le nombre de carnets à importer n'est pas égal au nombre demandé pour ce compte");

// 201-210 : PS cloture compte de crédit
$error[ERR_CRE_NO_DOSS] = _("Aucun dossier de crédit en cours pour ce client");
$error[ERR_CRE_ASS_DIFF_SOLDE] = _("Le montant de l'assurance est différent montant du crédit à apurer");
$error[ERR_GAR_ETAT_INCORRECT] = _("L'état de la garantie est incorrect");

// 211-220 : PS de remboursement de crédit
$error[ERR_CRE_MNT_TROP_ELEVE] = _("Le montant du remboursement est trop élevé par rapport au solde du crédit");
$error[ERR_CRE_NO_DOSS] = _("Aucune échéance non-remboursée pour ce client");

// 221-230 : Abattement des pénalités
$error[ERR_CRE_PEN_TROP_ELEVE] = _("Le montant des pénalités est trop élevé");

// 231-240 : Déboursement de crédit
$error[ERR_CRE_MNT_DEB_TROP_ELEVE] = _("Le montant déboursé est trop élevé par rapport au montant octroyé");
$error[ERR_CRE_DEST_DEB_INCONNU] = _("La destination des fonds de déboursement est inconnue");

// 300-310 : PS de manipulation de l'historique et du débit du compte de base
$error[ERR_HIS_NO_DEB] = _("Aucune transaction en attente pour cette ligne de l'historique");
$error[ERR_SOURCE_FRAIS_RETRAIT] = _("La source pour l\'encaissement des frais de retrait n\'a pas été spécifiée");
$error[ERR_FRAIS_DEBIT_NON_PERCU] = _("Le solde du compte de base est insuffisant pour la perception des frais : ceux-ci sont mis en attente");

// 321-330 Gestion des localisations
$error[ERR_LOC_EXIST_CHILD] = _("La localisation possède des fils");
$error[ERR_LOC_EXIST_CLIENT] = _("La localisation est référencée par 1 ou plusieurs clients");

$error[ERR_DAT_NON_PROLONGEABLE] = _("Le DAT n\'est pas prolongeable");
$error[ERR_DAT_NBRE_PROLONGATIONS_MAX_ATTEINT] = _("Le nombre de prolongations maximum a été atteint");

// 350 -> 369 : Menu comptabilité
$error[ERR_EMAIL_EXIST] = _("L'email existe déjà");
$error[ERR_CPT_EXIST] = _("Ce compte existe déjà");
$error[ERR_NO_ASSOCIATION] = _("Aucun compte n'a été associé à cette opération");
$error[ERR_CPT_RESULTAT_EXIST] = _("Un compte de résultat existe déjà dans la DB");
$error[ERR_CPTE_RESULT_NON_DEF] = _("Le compte de résultat n'est pas défini");
$error[ERR_CPT_ECRITURE_EXIST] = _("Il y a des écritures en attente pour le compte principal");
$error[EXIST_ECR_ATT] = _("Une ou plusieurs écritures en attente antérieures à la date de clôture existent");
$error[ERR_DATE_NON_VALIDE] = _("La date donnée n'est pas valide");
$error[ERR_SOLDES_DIFFERENTS] = _("Le solde calculé n'est pas égal au solde réel du compte ");
//$error[ERR_CPTE_CENTRALISE] = _("Le compte est centralisateur ");
$error[ERR_SOLDE_MAL_REPARTI] = _("Le solde du compte principal est mal réparti aux soldes des sous-comptes ");
$error[ERR_DEJA_PRINC_JOURNAL] = _("Le compte ou un de ses sous-comptes est déja compte principal d'un autre journal ");
$error[ERR_DEJA_CONTREPARTIE] = _("Le compte ou un de ses sous-comptes est déja compte de contrepartie d'un autre journal ");
$error[ERR_JOU_EXISTE] = _("Le journal existe déjà ");
$error[ERR_JOU_NON_SUPPRIMABLE] = _("On ne peut pas supprimer le journal ");
$error[ERR_DOSSIER_NOT_EXIST] ="Le Dossier n'existe pas ";
$error[ERR_ECR_NON_VALIDE] = _("Les écritures comptables ne peuvent pas être validées ");
$error[ERR_CPTE_ETAT_CRE_NON_PARAMETRE] = _("Les comptes comptables liés aux états de crédit n'ont pas été correctement paramétrés pour le produit ");
$error[ERR_PAS_CPTE_LIAISON] = _("Le compte de liaison n'est pas défini");
$error[ERR_CPTE_NON_PARAM] = _("Le compte comptable associé n'est pas paramétré : ");
$error[ERR_ECR_ATTENTE_VALID] = _("Des écritures comptables sont en attente de validation");
$error[ERR_CPT_CENTRALISE] = _("L'opération ne peut mouvementer un compte centralisateur");
$error[ERR_MODIF_ETAT_CRE] = _("La modification d'un état de crédit ne s'est pas bien déroulée.");
$error[ERR_CPTE_CRE_ATT_DEB_NON_PARAMETRE] = _("Le compte comptable pour l'attente de déboursement n'est pas paramétré pour ce produit ");

// 401 -> 450 : Paramétrage
$error[ERR_EXIST_REFERENCE] = _("Certaines données font référence à la donnée à supprimer");
$error[ERR_TROP_RECORDS] = _("Incohérence dans la base de données: une requête a renvoyé trop d'enregistrements. Veuillez contacter l'administrateur. ");
$error[ERR_TIMEOUT_INVALID] = sprintf(_("Elle est plus grande que celle spécifiée de manière globale pour le système (%d secondes, la valeur de <em>session.gc_maxlifetime</em> dans <strong>php.ini</strong>).  Ce temps d'inactivité ne sera donc probablement pas pris en compte."), get_cfg_var("session.gc_maxlifetime"));
$error[ERR_NO_RECORDS] = _("Incohérence dans la base de données: une requête a renvoyé aucun enregistrement. Veuillez contacter l'administrateur. ");

// 451 -> 500 : Module guichet
$error[ERR_GUICHET_OUVERT] = _("Ce guichet est ouvert");

// 501 - 510 : DAT
$error[ERR_NON_CAT] = _("Ce compte n'est pas un compte à terme");

// 511 -> 520 : Dépot par lot via fichier
$error[ERR_FICHIER_DONNEES] = _("Le fichier de données n'a pas été correctement envoyé");
$error[ERR_NBR_COLONNES] = _("Mauvais nombre de colonnes");
$error[ERR_NUM_COMPLET_CPTE] = _("Numéro de compte invalide");
$error[ERR_ID_CLIENT] = _("Numéro de client invalide");
$error[ERR_DEVISE_CPTE] = _("Devise du compte invalide");
$error[ERR_MONTANT] = _("Montant invalide");
$error[ERR_NUM_COMPLET_CPTE_NOT_EXIST] = _("Numéro de compte n'existe pas'");
$error[ERR_NBR] = _("Nombre invalide");
$error[ERR_MNT_NON_EQUIV] = _("le montant 1 exprimé en devise 1 n'est pas équivalent au montant 2 exprimé en devise 2");

//750->760: paramètrage de rapport
$error[ERR_NON_ID_PERE]=_("L'identifiant du père n'existe pas");
$error[ERR_NUM_COMPART_NON_VALIDE]=_("La valeur du compartiment n'est pas valide");

// 601 -> 650 : Multidevise
$error[ERR_DEVISE_NOT_EXIST] = _("La devise n'existe pas");
$error[ERR_DEV_DIFF_CPT_CENTR] = _("La devise du compte ne correspond pas à celle du compte centralisateur");
$error[ERR_MULTIDEV_ECR_EXIST] = _("Impossible de passer en mode multidevise car au moins une écriture a déjà été passée");
$error[ERR_POS_CHANGE_NOT_EXIST] = _("La paramétrage des comptes de Position de Change dans la table Agence est manquant");
$error[ERR_DEVISE_CPT_DIFF] = _("Les comptes du correspondant n'ont pas la même devise");
$error[ERR_CPTE_ABSENT] = _("Un des comptes du correspondant n'est pas renseigné");
$error[ERR_SWIFT_NON_VALIDE] = _("Un des éléments du message SWIFT n'est pas valide");
$error[ERR_NO_DEVISE] = _("Aucune devise n'a été définie");
$error[ERR_CPT_MONODEVISE] = _("Le compte associé à cette opération doit être multidevise");
$error[ERR_DEVISE_CPT] = _("Tentative de mouvement un compte dans une devise différente de celui-ci.");
$error[ERR_DEVISE_CPT_INT] = _("La devise du compte interne ne correspond pas à celle de l'opération");
$error[ERR_DEV_DIFF_CPT_PROV] = _("La devise du compte ne correspond pas à celle du compte de provision");
$error[ERR_TAUX_CHANGE_DIFF_MULTIAGENCE] = _("Le taux de change des devises concernés sont différents dans les deux agences");
$error[ERR_DEVISE_REF_DIFF_MA] = _("La devise de référence n'est pas la même dans les agences");
$error[ERR_FLAG_PRELEV_COMM_DIFF_MA] = _("Le paramétrage 'Appliquer la commission dans l'agence locale en mode multi-agences?' ne sont pas identiques dans les deux agences . Revoir paramétrage des deux agences.");

// 701 -> 710 : Licence
$error[ERR_LIC_FIC] = _("La licence de cette agence n'existe pas");
$error[ERR_LIC_AGC] = _("La licence de cette agence n'est pas valide");
$error[ERR_LIC_EXP] = _("La licence de cette agence a expiré");
$error[ERR_LIC_CLI] = _("Le licence de cette agence ne permet pas de gérer autant de clients actifs");

// 711 -> 720 : Parametrage
$error[ERR_PARAM_CPT_INT] = _("paramétrer le compte comptable des interêts du produit d'épargne ");
$error[ERR_PARAM_CPT_ASS] = _("faire le paramétrage des comptes comptables associés aux états de crédit du produit ");
$error[ERR_PARAM_OPE] = _("paramétrer l'opération ");
$error[ERR_PARAM_AGC] = _("parametrer le champ base de calcul du taux d'interêt pour l'epargne dans la table agence");

//750 -> 770 : Consolidation
$error[ERR_NOM_BASE] = _("Problème de nommage d'une base: vérifier si le nommage est respecté ou si la version correspond à celle enregistrée dans la table de schema");
$error[ERR_AUCUN_FICHIER_DUMP] = _("Aucun fichier dump a éte trouvé dans le repertoire images_consolidation.");


// 951 -> 999 : Système
$error[ERR_PSQL_DUMP] = _("Erreur lors de la génération du dump PostgreSQL ");
$error[ERR_GZIP] = _("Erreur lors de la compression de l'archive ");
$error[ERR_PATH] = _("Erreur lors de la vérification des répertoires");

// 1000
$error[ERR_GENERIQUE] = _("L'opération ne s'est pas correctement terminée.");

class ErrorObj {
  /**
   * Choix parmi les constantes d'erreur
   * @see NO_ERR
   */
  var $errCode = NULL;
  /**
   * Paramètre optionel, variable selon le type d'erreur.
   */
  var $param = "";
  /**
   * Gestion éventuelle d'un handler d'erreur.
   */
  var $handler = NULL;

  function ErrorObj ($code, $param="", $handler=NULL) {
    $this->errCode = $code;
    $this->param = $param;
    $this->handler = $handler;
  }
}