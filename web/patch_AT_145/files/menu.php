<?Php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Fichier de gestion des menus principaux
 *
 * Les écrans suivants sont définis :
 * - Gen-3 : Menu principal
 * - Gen-4 : Menu documents (sous menu clientèle)
 * - Gen-6 : Menu guichet
 * - Gen-7 : Menu système
 * - Gen-8 : Menu clientèle
 * - Gen-9 : Menu gestion clientèle
 * - Gen-10 : Menu épargne
 * - Gen-11 : Menu crédit
 * - Gen-12 : Menu paramétrage
 * - Gen-13 : Menu rapports
 * - Gen-14 : Menu comptabilité
 * - Gen-15 : Menu Budget
 * - Gen-16 : Menu Agency Banking
 * - Gen-17 : Menu Mobile Lending
 *
 * Gen-5 existe aussi et est lié à la sélection d'un client, il n'est pas géré dans ce fichier.
 *
 * @package Ifutilisateur
 **/

require_once "lib/html/HTML_menu_gen.php";
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/multilingue/traductions.php'; // La classe doit être déclarée avant l'ouverture de la session
require_once 'lib/misc/divers.php';

/* Vérifié l'accès */
checkADBankingAccess();

/**
 * Cette fonction met à NULL toutes les variables globales concernant la sélection d'un client
 * Doit etre appelée à chaque fois qu'on entre dans un menu de niveau 1
 */
function resetVariablesGlobalesClient() { /*{{{ */
  global $global_client;
  global $global_id_client;
  global $global_client_debiteur;
  global $global_id_client_formate;
  global $global_alerte_DAT;
  global $global_credit_niveau_retard;
  global $global_suspension_pen;
  global $global_cli_epar_obli;
  global $global_photo_client;
  global $global_signature_client;

  $global_client = "";
  $global_id_client = "";
  $global_client_debiteur = "";
  $global_id_client_formate = "";
  $global_alerte_DAT = "";
  $global_suspension_pen = false;
  $global_cli_epar_obli = "";
  $global_credit_niveau_retard = array();
  $global_signature_client = NULL;
  $global_photo_client = NULL;
} /*}}} */

/* Ici on définit la SEULE variable de session "utlisateur" permettant de faire passer des données d'un écran à un autre
  Cette variable est réinitialisé à chaque fois que l'on passe par un menu puisque dans ce cas on est sorti d'une suite logique d'écrans */
if (session_is_registered("SESSION_VARS"))
  session_unregister("SESSION_VARS");
unset($SESSION_VARS);
$SESSION_VARS = array();
session_register("SESSION_VARS");

// On réinitialise également la devise courante
setMonnaieCourante($global_monnaie);

/*{{{ Gen-3 : Menu principal */
If ($global_nom_ecran == "Gen-3") {

  //initialise les variables ... si jamais on vient d'un écran où le client était sélectionné
  resetVariablesGlobalesClient();
    $ad_cli_hist_tablen = getTableliste('ad_cli_hist');
    $ad_cli_tablen = getTableliste('ad_cli');

    updateTableliste(array('tablen' => $ad_cli_tablen), array('tablen' => $ad_cli_hist_tablen));

  $MyMenu = new HTML_menu_gen(_("Menu principal"));
  $MyMenu->addItem(_("Création client"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Acl-1", 30, "$http_prefix/images/ajout_client.gif");
  if(isMobileLending()){
    $MyMenu->addItem(_("Mobile Lending"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-17", 900, "$http_prefix/images/telephone3.gif");
  }
  $MyMenu->addItem(_("Menu Guichet"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-6", 151, "$http_prefix/images/menu_guichet.gif");
  $MyMenu->addItem(_("Menu Système"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-7", 201, "$http_prefix/images/menu_systeme.gif");
  $MyMenu->addItem(_("Menu Paramétrage"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-12", 251, "$http_prefix/images/menu_param.gif");
  $MyMenu->addItem(_("Menu Rapports"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-13", 301, "$http_prefix/images/rapport.gif");
  $MyMenu->addItem(_("Menu Comptabilité"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-14", 401, "$http_prefix/images/menu_compta.gif");
  $MyMenu->addItem(_("Menu Budget"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-15", 700, "$http_prefix/images/gestion_plan_comptable.gif");
  $MyMenu->addItem(_("Menu Agency Banking"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-16", 751, "$http_prefix/images/operation_agence_siege.gif");
  $MyMenu->addItem(_("Quitter ADbanking"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Out-1", 0, "$http_prefix/images/quitter.gif");
  $MyMenu->buildHTML();
  echo $MyMenu->HTML_header_code;

  //Partie sélection client
  if (check_access(3)) {
    $myForm = new HTML_GEN2();
    $myForm->addField("num_client", _("N° de client"), TYPC_INT);
    $myForm->addLink("num_client", "rechercher", _("Rechercher"), "#");
    $myForm->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client', '"._("Recherche")."');return false;"));
    $myForm->setFieldProperties("num_client", FIELDP_JS_EVENT, array("onkeypress" => "return true;"));
    $myForm->addButton("num_client", "ok", _("OK"), TYPB_SUBMIT);
      $myForm->setButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Gen-8');
      $myForm->setButtonProperties("ok", BUTP_AXS, 3);

      $myForm->setFieldProperties("num_client", FIELDP_IS_REQUIRED, true);

    // Petite astuces pour permettre à l'utilisateur d'utiliser la touche 'Entrée' depuis le champs
    $ControlNumClient =" function control(){\n";
    $ControlNumClient .=" var chaine;\n";
    $ControlNumClient .=" chaine=document.ADForm.num_client.value;\n";
    $ControlNumClient .=" var tab=chaine.split('-');\n";
    $ControlNumClient .=" if( tab.length >1 )\n";
    $ControlNumClient .=" document.ADForm.num_client.value=tab[1];\n";
    $ControlNumClient .=" else \n";
    $ControlNumClient .=" document.ADForm.num_client.value=tab[0];\n";
    $ControlNumClient .=" } \n";
    $JS = "hasChecked = false;";
    $myForm->addJS(JSP_FORM, "JS", $JS);
    $myForm->addJS(JSP_FORM, "JS_NUM", $ControlNumClient);
    $myForm->addJS(JSP_BEGIN_CHECK, "changeChecked", "hasChecked = true;");
    $myForm->setButtonProperties("ok", BUTP_JS_EVENT, array("onClick" => "control();"));
    $myForm->setFormProperties(FORMP_JS_EVENT, array("onsubmit" => "if (hasChecked == false) {assign('Gen-8');checkForm();} hasChecked = false;"));

    $myForm->buildHTML();
    echo $myForm->HTMLFormHead;
    echo $myForm->HTMLFormBody;
    echo $myForm->HTMLFormButtons;
    echo $myForm->HTMLFormFooter;
    echo "<br><br>";
  }

  echo $MyMenu->HTML_body_code;
}
/*}}}*/

/*{{{ Gen-4 : Menu documents (sous menu clientèle) */
else if ($global_nom_ecran == "Gen-4")

{
  $MyMenu = new HTML_menu_gen(_("Menu documents"));
  $MyMenu->addItem(_("Commande chèquier"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Chq-1", 41, "$http_prefix/images/commandecheq.gif","1");
  $MyMenu->addItem(_("Retrait chèquier"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rch-1", 42, "$http_prefix/images/retraitcheq.gif","2");
  $MyMenu->addItem(_("Mise en opposition chèque"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Och-1", 45, "$http_prefix/images/oppositioncheq.gif","3"); 
  $MyMenu->addItem(_("Extraits de compte"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ext-1", 43, "$http_prefix/images/extraits_compte.gif","");
  $MyMenu->addItem(_("Situation globale client"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rap-1", 44, "$http_prefix/images/rapport_client.gif","5");
  if(isAtmModule()) {
      $MyMenu->addItem(_("Commande carte ATM"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Caa-1", 46, "$http_prefix/images/rapport_client.gif", "6");
      $MyMenu->addItem(_("Retrait carte ATM"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Rct-1", 47, "$http_prefix/images/rapport_client.gif", "7");
      $MyMenu->addItem(_("Gestion des cartes suspendues/désactivées"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Gcs-1", 48, "$http_prefix/images/rapport_client.gif", "8");
  }
  $MyMenu->addItem(_("Retour menu clientèle"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-8", 0, "$http_prefix/images/back.gif","0");
  $MyMenu->buildHTML();
  echo $MyMenu->HTMLCode;
}



/*}}}*/

/*{{{ Gen-6 : Menu guichet */
else if ($global_nom_ecran == "Gen-6") {

  //initialise les variables ... si jamais on vient d'un écran où le client était sélectionné
  resetVariablesGlobalesClient();

  $MyMenu = new HTML_menu_gen(_("Menu de gestion des guichets"));

  $MyMenu->addItem(_("Approvisionnement"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Agu-1", 155, "$http_prefix/images/approvisionnement.gif","1");
  $MyMenu->addItem(_("Délestage"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dgu-1", 156, "$http_prefix/images/delestage.gif","2");

  $MyMenu->addItem(_("Ajustement encaisse"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Jgu-1", 170, "$http_prefix/images/ajuste_encaisse.gif","3");
  $MyMenu->addItem(_("Passage opération diverse de caisse/compte"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pdc-1",189,"$http_prefix/images/menu_credit.gif","4");
  $MyMenu->addItem(_("Rapport sur les opérations diverses"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rod-1",195,"$http_prefix/images/rapport_credit.gif","11");
  $MyMenu->addItem(_("Retrait par lot"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rgu-1", 154, "$http_prefix/images/retrait.gif");
  $MyMenu->addItem(_("Dépôt par lot"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Sgu-1", 158, "$http_prefix/images/saisie_lot.gif","5");
  $MyMenu->addItem(_("Traitement par lot Dépôt / Quotité via fichier"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dlf-1", 159, "$http_prefix/images/saisie_lot_fich.gif","6");
  $MyMenu->addItem(_("Autorisation de retrait"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Adr-1", 157, "$http_prefix/images/retrait.gif","-1");
  $MyMenu->addItem(_("Autorisation de transfert"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Adt-1", 152, "$http_prefix/images/retrait.gif","-1");
  $MyMenu->addItem(_("Autorisation de retrait en déplacé"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Atd-1", 198, "$http_prefix/images/retrait.gif","-1");
  $MyMenu->addItem(_("Autorisation approvisionnement / délestage"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Aad-1", 804, "$http_prefix/images/retrait.gif","-1");
  $MyMenu->addItem(_("Effectuer approvisionnement / délestage"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ead-1", 177, "$http_prefix/images/retrait.gif","-1");
  $MyMenu->addItem(_("Traitement des attentes"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Att-1", 188, "$http_prefix/images/traitement_chq_gui.gif","7");
  $MyMenu->addItem(_("Change Cash"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Cca-1", 186, "$http_prefix/images/change_cash_guichet.gif","8");
  $MyMenu->addItem(_("Paiement Net Bank"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Swi-1", 187, "$http_prefix/images/virement_netbank.gif","9");
  $MyMenu->addItem(_("Souscription des parts sociales par lot via fichier"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Psf-1", 190, "$http_prefix/images/souscription.gif","10");
  $MyMenu->addItem(_("Perception des frais d'adhesion par lot"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Faf-1", 153, "$http_prefix/images/saisie_lot_fich.gif","-1");
  $MyMenu->addItem(_("Recharge Ferlo par Versement"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rfv-1", 160, "$http_prefix/images/retrait.gif","13");
  $MyMenu->addItem(_("Ajout des chéquiers imprimés"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ich-1", 191, "$http_prefix/images/traitement_chq_gui.gif","14");
if (isFusion()) {
    $MyMenu->addItem(_("Liste des chéquiers à imprimer"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Ich-5", 192, "$http_prefix/images/traitement_chq_gui.gif", "15");
}else{
    $MyMenu->addItem(_("Liste des chéquiers à imprimer"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Ich-3", 192, "$http_prefix/images/traitement_chq_gui.gif", "15");
}
  $MyMenu->addItem(_("Gestion des chèques certifiés"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gcc-1", 162, "$http_prefix/images/menu_systeme.gif","-1");
  $MyMenu->addItem(_("Traitement des chèques reçus en compensation"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Tcc-1", 163, "$http_prefix/images/traitement_chq.gif","-1");
  $MyMenu->addItem(_("Visualisation des transactions"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Vgu-1", 180, "$http_prefix/images/visualisation_transactions.gif","-1");
  $MyMenu->addItem(_("Visualisation des transactions pour tous les logins"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Vtg-1", 181, "$http_prefix/images/visualisation_toutes_trans.gif","-1");
  $MyMenu->addItem(_("PNSEB-FENACOBU"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pns-1",171,"$http_prefix/images/menu_credit.gif","-1");

  $MyMenu->addItem(_("Opération en déplacé"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ope-11", 193, "$http_prefix/images/menu_compta.gif","-1");
  
  $MyMenu->addItem(_("Visualisation des opérations en déplacé"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ama-1", 194, "$http_prefix/images/visualisation_toutes_trans.gif","-1");
  $MyMenu->addItem(_("Remboursement de crédit par lot"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rpl-1", 806, "$http_prefix/images/remboursement.gif","-1");
  if(isAtmModule()) {
      $MyMenu->addItem(_("Gestion des cartes ATM"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Gca-1", 807, "$http_prefix/images/remboursement.gif", "-1");
  }

  $MyMenu->addItem(_("Retour menu principal"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-3", 0, "$http_prefix/images/back.gif","0");

  $MyMenu->buildHTML();
  echo $MyMenu->HTMLCode;
}
/*}}}*/

/*{{{ Gen-7 : Menu système */
else if ($global_nom_ecran == "Gen-7") {

  //initialise les variables ... si jamais on vient d'un écran où le client était sélectionné
  resetVariablesGlobalesClient();

  $MyMenu = new HTML_menu_gen(_("Menu Système"));
  $MyMenu->addItem(_("Ouverture agence"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Oag-1", 205, "$http_prefix/images/ouverture_agence.gif","1");
  $MyMenu->addItem(_("Fermeture agence"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Fag-1", 206, "$http_prefix/images/fermeture_agence.gif","2");
  $MyMenu->addItem(_("Modification autre mot de passe"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Map-1", 215, "$http_prefix/images/mot_passe.gif","3");
  //  $MyMenu->addItem(_("Traitements de fin de journée (en construction)"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Fjr-1", 212, "$http_prefix/images/batch.gif","4");
  // FIXME: Pas encore implémenté
  $MyMenu->addItem(_("Sauvegarde de données"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dat-1", 210, "$http_prefix/images/sauve_data.gif","5");

  //  $MyMenu->addItem(_("Restauration de données (en construction)"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=xxx-x", 0, "$http_prefix/images/restaure_data.gif","6"); //Restaure_data.gif
  // FIXME : Pas encore implémenté
  $MyMenu->addItem(_("Déconnexion autre code utilisateur"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Flo-1", 230, "$http_prefix/images/force_logout.gif","6");
  $MyMenu->addItem(_("Ajustement du solde d'un compte d'épargne"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Acp-1", 235, "$http_prefix/images/ajustement_solde.gif","7");
  $MyMenu->addItem(_("Gestion de la licence"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gli-1", 240, "$http_prefix/images/gestion_licence.gif","8");
  $MyMenu->addItem(_("Consolidation de données"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Cdb-1", 211, "$http_prefix/images/sauve_data.gif","9");
  $MyMenu->addItem(_("Traitements de nuit Multi-Agence"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Tnm-1", 213, "$http_prefix/images/batch.gif","-1");
  $MyMenu->addItem(_("Traitement compensation au siège"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Tcs-1", 214, "$http_prefix/images/gestion_ecritures_comptables.gif","-1");
  $MyMenu->addItem(_("Rapport Etat de la Compensation des Opérations en déplacé"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rec-1", 242, "$http_prefix/images/rapport.gif","-1");
  $MyMenu->addItem(_("Information système"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ifs-1", 241, "$http_prefix/images/visualisation_toutes_trans.gif","-1");
  $MyMenu->addItem(_("Retour menu principal"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-3", 0, "$http_prefix/images/back.gif","0");
  $MyMenu->buildHTML();
  echo $MyMenu->HTMLCode;
}
/*}}}*/

/*{{{ Gen-8 : Menu clientèle */
else if ($global_nom_ecran == "Gen-8") {
  require_once 'lib/misc/tableSys.php';
  require_once 'lib/dbProcedures/client.php';
  $ok = true;
  global $double_affiliation, $global_id_client;

  global $global_cpt_base_ouvert, $global_depot_bloque, $global_retrait_bloque;

  $no_client = "";

  if(!empty($global_id_client)) {
    $no_client = (int)$global_id_client;
  }
  elseif(!empty($num_client)) {
    $no_client = (int)$num_client;
  }

  if(!empty($no_client) && is_int($no_client) && getClientDatas($no_client)!=NULL)
  {
    // Vérifie si le compte de base du client est ouvert
    $CB = getAccountDatas(getBaseAccountID($no_client));

    // etat des comptes epargnes
    switch ($CB["etat_cpte"])
    {
      // Ouvert
      case (1) :
        $global_cpt_base_ouvert = true;
        $global_depot_bloque = false;
        $global_retrait_bloque = false;
        break;

      // Depots bloqués
      case(6) :
        $global_cpt_base_ouvert = false;
        $global_depot_bloque = true;
        $global_retrait_bloque = false;
        break;

      // Retraits bloqués
      case(7) :
        $global_cpt_base_ouvert = false;
        $global_depot_bloque = false;
        $global_retrait_bloque = true;
        break;

      default :
        $global_cpt_base_ouvert = false;
        $global_depot_bloque = true;
        $global_retrait_bloque = true;
        break;
    }
  }

  if ($global_client == "") {
    //Récupère le numéro & le nom du client concerné
    $details = NULL;

    $num_client = (int)$num_client;
    if (is_int($num_client)) {
      $details = getClientDatas($num_client);
    }

    if ($details == NULL) { //Si le client n'existe pas
      $erreur = new HTML_erreur(_("Client inexistant"));
      $erreur->setMessage(_("Le numéro de client entré ne correspond à aucun client valide"));
      $erreur->addButton(BUTTON_OK,"Gen-3");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      $ok = false;
    } else { //Si le client existe
      $global_id_client = $details['id_client'];

      //Contrôle sur la double affiliation
      if($double_affiliation == false){
        if($details['pp_type_piece_id'] != "" && $details['pp_nm_piece_id'] != ""){
          $numero_client = getNumPieceIdClient($details['pp_type_piece_id'], $details['pp_nm_piece_id']);
          $msg = "";
          if(count($numero_client) > 1){
            $msg = _("Veuillez contacter l'administrateur !")." <br /> "._("Le type et le numéro de la pièce d'identité sont déjà utilisés par d'autres clients")." : ";
            for($i=0;$i<count($numero_client);$i++){
              $les_clients = $numero_client[$i]["id_client"];
              $msg .= " $les_clients , ";
            }
            if ($msg != "") {
                $ok = false;
                $colb_tableau = '#e0e0ff';
                $MyPage = new HTML_erreur(_("Double affiliation")." ");
                $MyPage->setMessage($msg);
                $MyPage->addButton(BUTTON_OK, "Gen-8");
                $MyPage->buildHTML();
                echo $MyPage->HTML_code;
              }
          }
        }
      }
      $id_agc = getNumAgence();
      $AGD = getAgenceDatas($id_agc);
      $type_num_cpte = $AGD['type_numerotation_compte'];

      if ($type_num_cpte == 1) {
        $global_id_client_formate = sprintf("%06d", $details['id_client']);
      } else if ($type_num_cpte == 2) {
        $global_id_client_formate = sprintf("%05d", $details['id_client']);
      } else if ($type_num_cpte == 3) {
        $global_id_client_formate = sprintf("%07d", $details['id_client']);
      } else if ($type_num_cpte == 4) {
        $global_id_client_formate = makeNumClient($details['id_client']);
      }


      $global_etat_client = getEtatClient($global_id_client);
      $global_langue_rapport = $details['langue_correspondance'];

      if (isClientDebiteur($global_id_client))
        $global_client_debiteur = TRUE;
      else
        $global_client_debiteur = FALSE;

      // récupération de l'état le plus en retard des dossiers du client ( ou des membres si c'est groupe solidaire)
      $etats_credit = end(getEtatCredit($global_id_client));
      $global_credit_niveau_retard[$etats_credit['cre_etat']] = array();
      array_push($global_credit_niveau_retard[$etats_credit['cre_etat']], $etats_credit['id_doss']);
      $etat_plus_avance = $etats_credit['cre_etat'];

      // Si le client est un groupe solidaire, vérifier l'état des dossiers des membres du groupe
      if ($details['statut_juridique'] == 4 ) {
        $result = getListeMembresGrpSol($global_id_client);
        if (is_array($result->param))
          foreach($result->param as $key=>$id_membre) {
          $etats_credit_membre = end(getEtatCredit($id_membre));
          if ($etats_credit_membre['cre_etat'] > $etat_plus_avance and $etats_credit_membre['gs_cat'] ==2) {
            unset($global_credit_niveau_retard[$etat_plus_avance]); // on garde l'état le plus avancé
            $global_credit_niveau_retard[$etats_credit_membre['cre_etat']] = array();
            array_push($global_credit_niveau_retard[$etats_credit_membre['cre_etat']],$etats_credit_membre['id_doss']);
            $etat_plus_avance = $etats_credit_membre['cre_etat'];
          }
          elseif($etats_credit_membre['cre_etat'] == $etat_plus_avance and $etats_credit_membre['gs_cat'] ==2)
          array_push($global_credit_niveau_retard[$etats_credit_membre['cre_etat']],$etats_credit_membre['id_doss']);
        }
      }

      if (alerteEcheanceDAT($global_id_client)) $global_alerte_DAT = TRUE;

      // Vérification de la suspension des pénalités
      $whereCl=" AND (etat = 5 OR etat = 7 OR etat = 14 OR etat = 15)";
      $dossiers = getIdDossier($global_id_client,$whereCl);

      foreach($dossiers as $id_doss=>$value) {
        $DOSS = getDossierCrdtInfo($id_doss);
        if ($DOSS["suspension_pen"] == 't') {
          $global_suspension_pen = true;
        }
      }

      $global_client = getClientName($global_id_client);

      // Récupération de la photo et de la signature du client
      $IMGS = getImagesClient($global_id_client);
      $global_photo_client = $IMGS["photo"];
      $global_signature_client = $IMGS["signature"];

      ajout_historique(3,$global_id_client, NULL, $global_nom_login, date("r"), NULL);
      if (!isAlreadyAccessed($global_id_client) && $ok == true && check_access(10)) {
        $ok = false;
        $myMsg = new HTML_message();
        $myMsg->setMessage(_("Ceci est une première consultation pour ce client.")."<br />"._("Veuillez vérifier les informations administratives et financières.")."<br />"._("Cliquez sur OK pour accéder à l'écran de modification du client"));
        $myMsg->addButton(BUTTON_OK, 'Mcl-1');
        $myMsg->buildHTML();
        echo $myMsg->HTML_code;
        markClientAccessed($global_id_client);
      }
    }
  }

  if ($ok) {
    $MyMenu = new HTML_menu_gen(_("Menu clientèle"));
    $MyMenu->addItem(_("Menu Gestion du client"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-9", 5, "$http_prefix/images/menu_gestion_client.gif","1");
    $MyMenu->addItem(_("Menu Epargne"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-10", 51, "$http_prefix/images/menu_epargne.gif","2");
    $MyMenu->addItem(_("Menu Credit"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-11", 101, "$http_prefix/images/menu_credit.gif","3");
    $MyMenu->addItem(_("Menu Documents"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-4", 40, "$http_prefix/images/menu_documents.gif","4");

    $MyMenu->addItem(_("Retour menu principal"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-3", 0, "$http_prefix/images/back.gif","0");
    $MyMenu->buildHTML();
    echo $MyMenu->HTMLCode;
    echo "<br><br><br>";
    echo "<TABLE align=\"center\" border=0 width=\"100%\">";
    if (check_access(85)) echo "<TR><TD align=\"center\"><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rex-1\">"._("Retrait express")."</A></TD>";
    if (check_access(81)) echo "<TD align=\"center\"><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Aut-1\">"._("Recharge Ferlo")."</A></TD>";
    if (check_access(25)) echo "<TD align=\"center\"><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ccl-2&orig=menu\">"._("Infos financières")."</A></TD>";
    if (check_access(86)) echo "<TD align=\"center\"><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dex-1\">"._("Dépôt express")."</A></TD></TR>";
    echo "</TABLE>";
  }
}
/*}}}*/

/*{{{ Gen-9 : Menu gestion clientèle */
else if ($global_nom_ecran == "Gen-9") {
  $MyMenu = new HTML_menu_gen(_("Menu gestion du client"));
  $MyMenu->addItem(_("Consultation du client"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ccl-1", 25, "$http_prefix/images/consultation_client.gif","1");
  $MyMenu->addItem(_("Modification du client"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mcl-1", 10, "$http_prefix/images/modif_client.gif","2");
  $MyMenu->addItem(_("Gestion des relations"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rel-1", 11, "$http_prefix/images/gestion_relations.gif","3");
  $MyMenu->addItem(_("Perception des frais d'adhésion"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pfh-1", 31, "$http_prefix/images/perception_frais.gif","4");

  //check si client a ps liberer
  $nbre_part_lib = getNbrePartSocLib($global_id_client);
  $nbrePSlib = $nbre_part_lib->param[0]['nbre_parts_lib'];
  $etat=getEtatClient($global_id_client);

  if ($etat != 1) {
   $MyMenu->addItem(_("Gestion des parts sociales"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mgp-1", 27, "$http_prefix/images/souscription.gif","5");
  }

  $MyMenu->addItem(_("Simulation de défection"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Sdc-1", 17, "$http_prefix/images/simulation_defection.gif","6");
  $MyMenu->addItem(_("Défection du client"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dcl-1", 15, "$http_prefix/images/defection_client.gif","7");
  $MyMenu->addItem(_("Faire jouer l'assurance"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ass-1", 19, "$http_prefix/images/assurances.gif","8");
  $MyMenu->addItem(_("Finalisation défection du client"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Fcl-1", 16, "$http_prefix/images/final_defection_client.gif","9");
  $MyMenu->addItem(_("Gestion des abonnements"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Abn-1", 12, "$http_prefix/images/modif_client.gif","10");
  if (isAtmModule()) {
      $MyMenu->addItem(_("Gestion des abonnements ATM"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Abt-1", 13, "$http_prefix/images/modif_client.gif", "11");
  }
  $MyMenu->addItem(_("Retour menu clientèle"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-8", 0, "$http_prefix/images/back.gif","0");
  $MyMenu->buildHTML();
  echo $MyMenu->HTMLCode;
}
/*}}}*/

/*{{{ Gen-10 : Menu épargne */
else if ($global_nom_ecran == "Gen-10") {
  $MyMenu = new HTML_menu_gen(_("Menu épargne"));
  $MyMenu->addItem(_("Consultation d'un compte"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Vcp-1", 80, "$http_prefix/images/consult_cpt.gif","1");
  $MyMenu->addItem(_("Ouverture d'un compte"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ocp-1", 53, "$http_prefix/images/ouvert_cpt.gif","2");
  $MyMenu->addItem(_("Transfert entre comptes"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Tcp-1", 76, "$http_prefix/images/transfert_cpt.gif","3");
  $MyMenu->addItem(_("Paiement transfert autorisé"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pdt-1", 100, "$http_prefix/images/transfert_cpt.gif","-1");
  $MyMenu->addItem(_("Retrait"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rcp-1", 70, "$http_prefix/images/retrait.gif","4");
  $MyMenu->addItem(_("Paiement retrait autorisé"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pdr-1", 74, "$http_prefix/images/retrait.gif","-1");
  $MyMenu->addItem(_("Dépôt"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dcp-1", 75, "$http_prefix/images/depot.gif","5");
  $MyMenu->addItem(_("Gestion des annulations retrait et dépôt"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gae-1", 60, "$http_prefix/images/gestion_operations_caisse.gif","-1");
  $MyMenu->addItem(_("Traitement chèques"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Tch-1", 77, "$http_prefix/images/traitement_chq.gif","6");
  $MyMenu->addItem(_("Prolongation de compte à terme"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pda-1", 78, "$http_prefix/images/prolongation_dat.gif","7");
  $MyMenu->addItem(_("Ordres Permanents"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ope-1",79,"$http_prefix/images/transfert_cpt.gif","8");

  $MyMenu->addItem(_("Simulation arrêté de compte"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Scp-1", 55, "$http_prefix/images/simu_cpt.gif","9");
  $MyMenu->addItem(_("Rupture anticipée d'un compte"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ccp-1", 54, "$http_prefix/images/cloture_cpt.gif","0");
  $MyMenu->addItem(_("Modification de compte"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mdc-1", 88, "$http_prefix/images/autorisation_decouvert.gif","-1");
  $MyMenu->addItem(_("Simulation échéancier"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Spe-1", 68, "$http_prefix/images/simul_echeancier.gif","6");
  $MyMenu->addItem(_("Bloquer / débloquer un compte"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Bdc-1", 89, "$http_prefix/images/bloquer_compte.gif","-1");
  $MyMenu->addItem(_("Gestion des mandats"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Man-1", 90, "$http_prefix/images/gestion_mandats.gif","-1");
  $MyMenu->addItem(_("Activez les comptes dormants / Inactifs"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ced-1", 91, "$http_prefix/images/bloquer_compte.gif","-1");
  $MyMenu->addItem(_("Débloquer les montants"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dlm-1", 52, "$http_prefix/images/bloquer_compte.gif","-1");
  $MyMenu->addItem(_("Retour menu clientèle"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-8", 0, "$http_prefix/images/back.gif","0");
  $MyMenu->buildHTML();

  echo $MyMenu->HTMLCode;
}
/*}}}*/

/*{{{ Gen-11 : Menu crédit */
else if ($global_nom_ecran == "Gen-11") {
  if ($global_nom_ecran_prec == "Mlt-1"){
    $global_id_client = $_GET['num_client'];
    $global_id_client_formate = $_GET['global_id_client_formate'];
    $global_client = getClientName($global_id_client);;
  }
  $global_cli_epar_obli = "" ;
  $MyMenu = new HTML_menu_gen(_("Menu de Crédits"));
  $MyMenu->addItem(_("Mise en place d'un dossier de crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ado-1", 105, "$http_prefix/images/nouveau_dossier.gif","1");
  
  $MyMenu->addItem(_("Gestion ligne de crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Lcr-1", 102, "$http_prefix/images/menu_credit.gif","2"); // new

  if (hasCreditAttReechMor($global_id_client)) {
    //$MyMenu->addItem(_("Approbation du rééchelonnement / moratoire"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Apd-1", 110, "$http_prefix/images/approb_dossier.gif","2");
    $MyMenu->addItem(_("Déboursement des fonds"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dbd-1", 125, "$http_prefix/images/deboursement.gif","3");
    $MyMenu->addItem(_("Remboursement crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rcr-1", 147, "$http_prefix/images/remboursement.gif","4");
    $MyMenu->addItem(_("Réalisation garantie"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rga-1", 148, "$http_prefix/images/realisation_gar.gif","5");
    //$MyMenu->addItem(_("Rééchelonnement / Moratoire"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rdo-1", 145, "$http_prefix/images/reech_morat.gif","6");
    $MyMenu->addItem(_("Suspension / ajustement des pénalités"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pen-1", 131, "$http_prefix/images/ajustement_penalites.gif","7");
    $MyMenu->addItem(_("Consultation du dossier de crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Cdo-1", 140, "$http_prefix/images/consult_dossier.gif","8");
    $MyMenu->addItem(_("Modification d'un dossier de crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mdd-1", 130, "$http_prefix/images/modif_dossier.gif","9");
    $MyMenu->addItem(_("Correction d'un dossier de crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Cdd-1", 129, "$http_prefix/images/correct_dossier.gif","13");
    //$MyMenu->addItem(_("Annulation du rééchelonnement / moratoire"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=And-1",120, "$http_prefix/images/annul_dossier.gif","10");
    //$MyMenu->addItem(_("Rejet du rééchelonnement / moratoire"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rfd-1", 115, "$http_prefix/images/refus_dossier.gif","11");
    $MyMenu->addItem(_("Annulation déboursement progressif"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Adp-1",126, "$http_prefix/images/annul_dossier.gif","14");

  } else {
    $MyMenu->addItem(_("Approbation du dossier de crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Apd-1", 110, "$http_prefix/images/approb_dossier.gif","2");
    $MyMenu->addItem(_("Déboursement des fonds"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dbd-1", 125, "$http_prefix/images/deboursement.gif","3");
    $MyMenu->addItem(_("Remboursement crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rcr-1", 147, "$http_prefix/images/remboursement.gif","4");
    $MyMenu->addItem(_("Réalisation garantie"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rga-1", 148, "$http_prefix/images/realisation_gar.gif","5");
    //$MyMenu->addItem(_("Rééchelonnement / Moratoire"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rdo-1", 145, "$http_prefix/images/reech_morat.gif","6");
    $MyMenu->addItem(_("Suspension / ajustement des pénalités"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pen-1", 131, "$http_prefix/images/ajustement_penalites.gif","7");
    $MyMenu->addItem(_("Consultation du dossier de crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Cdo-1", 140, "$http_prefix/images/consult_dossier.gif","8");
    $MyMenu->addItem(_("Modification d'un dossier de crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mdd-1", 130, "$http_prefix/images/modif_dossier.gif","9");
    $MyMenu->addItem(_("Correction d'un dossier de crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Cdd-1", 129, "$http_prefix/images/correct_dossier.gif","13");
    $MyMenu->addItem(_("Annulation du dossier de crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=And-1",120, "$http_prefix/images/annul_dossier.gif","10");
    $WhereC= " and etat = 1";
    $id_dossier = getIdDossier($global_id_client,$WhereC);
    if ($id_dossier != null)
    {
      $MyMenu->addItem(_("Rejet du dossier de crédit"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Rfd-1", 115, "$http_prefix/images/refus_dossier.gif", "11");
    }
    $MyMenu->addItem(_("Annulation déboursement progressif"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Adp-1",126, "$http_prefix/images/annul_dossier.gif","14");

  }
  
    $MyMenu->addItem(_("Modification de l'échéancier de crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mec-1", 136, "$http_prefix/images/reech_morat.gif","");
    $MyMenu->addItem(_("Abattement des intérêts et pénalités"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Abi-1", 132, "$http_prefix/images/abattement_interets.gif","");
    $MyMenu->addItem(_("Traitement pour remboursement anticipé"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Tre-1", 133, "$http_prefix/images/abattement_interets.gif","");
    $MyMenu->addItem(_("Simulation échéancier"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Sdo-1", 135, "$http_prefix/images/simul_echeancier.gif","12");
    $MyMenu->addItem(_("Retour clientèle"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-8", 0, "$http_prefix/images/back.gif","0");

  $MyMenu->buildHTML();
  echo $MyMenu->HTMLCode;
}
/*}}}*/

/*{{{ Gen-12 : Menu paramétrage */
else if ($global_nom_ecran == "Gen-12") {

  //initialise les variables ... si jamais on vient d'un écran où le client était sélectionné
  resetVariablesGlobalesClient();

  $MyMenu = new HTML_menu_gen(_("Menu de Paramétrage"));
  $MyMenu->addItem(_("Gestion des profils"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gpf-1", 255, "$http_prefix/images/gestion_profils.gif","1");
  $MyMenu->addItem(_("Changer mot de passe"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mdp-1", 260, "$http_prefix/images/mot_passe_utilisateur.gif","2");
  $MyMenu->addItem(_("Gestion des utilisateurs"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gus-1", 265, "$http_prefix/images/gestion_utilisateurs.gif","3");
  $MyMenu->addItem(_("Gestion des codes utilisateurs"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Glo-1", 287, "$http_prefix/images/gestion_logins.gif","4");
  $MyMenu->addItem(_("Gestion des tables de paramétrage"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gta-1", 292, "$http_prefix/images/param_tables.gif","5");
  $MyMenu->addItem(_("Visualisation jours ouvrables"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Vjf-1", 296, "$http_prefix/images/visu_ferie.gif","6");
  $MyMenu->addItem(_("Gestion des devises"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dev-1", 274, "$http_prefix/images/gestion_devises.gif","7");
  $MyMenu->addItem(_("Gestion de Jasper report"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gjr-1",300, "$http_prefix/images/jasper.jpg","8");
  $MyMenu->addItem(_("Gestion des champs extras"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Cex-1", 281, "$http_prefix/images/field.gif","9");
  $MyMenu->addItem(_("Gestion des modules spécifiques"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gfp-1", 252, "$http_prefix/images/param_tables.gif","10");
  $MyMenu->addItem(_("Retour menu principal"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-3", 0, "$http_prefix/images/back.gif","0");
  $MyMenu->buildHTML();
  echo $MyMenu->HTMLCode;
}
/*}}}*/

/*{{{ Gen-13 : Menu rapport */
else if ($global_nom_ecran == "Gen-13") {

  //initialise les variables ... si jamais on vient d'un écran où le client était sélectionné
  resetVariablesGlobalesClient();

  // Pour l'instant, il n'est possible d'afficher les rapports que dans la langue de l'interface de l'utilisateur connecté
  $global_langue_rapport = $global_langue_utilisateur;

  $MyMenu = new HTML_menu_gen(_("Menu Rapports"));
  $MyMenu->addItem(_("Rapports client"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Cra-1", 310, "$http_prefix/images/rapport_client.gif","1");
  $MyMenu->addItem(_("Rapports épargne"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Era-1", 330, "$http_prefix/images/rapport_epargne.gif","2");
  $MyMenu->addItem(_("Rapports crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Kra-1", 350, "$http_prefix/images/rapport_credit.gif","3");
  $MyMenu->addItem(_("Rapports agence"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ara-1", 370, "$http_prefix/images/rapport_agence.gif","4");

  $MyMenu->addItem(_("Rapports chéquiers"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rcq-1", 340, "$http_prefix/images/traitement_chq.gif","5");

  $MyMenu->addItem(_("Rapports externe"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rae-1", 380, "$http_prefix/images/jasper.jpg","6");

  if(isset($global_id_agence) && isMultiAgence()) {
    $MyMenu->addItem(_("Rapports multi-agences"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rma-1", 320, "$http_prefix/images/virement_netbank.gif","7");
  }

  $MyMenu->addItem(_("Simulation échéancier"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Sra-1", 390, "$http_prefix/images/simul_echeancier.gif","8");
  $MyMenu->addItem(_("Visualisation du dernier rapport imprimé"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dra-1", 399, "$http_prefix/images/visu_dernier_rapport.gif","9");
  $MyMenu->addItem(_("Visualisation des rapports imprimé"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Vdr-1", 398, "$http_prefix/images/rapport.gif","10");
  $MyMenu->addItem(_("Retour menu principal"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-3", 0, "$http_prefix/images/back.gif","0");
  $MyMenu->buildHTML();
  echo $MyMenu->HTMLCode;
}
/*}}}*/

/*{{{ Gen-14 : Menu comptabilité */
else if ($global_nom_ecran == "Gen-14") {

  //initialise les variables ... si jamais on vient d'un écran où le client était sélectionné
  resetVariablesGlobalesClient();

  $MyMenu = new HTML_menu_gen(_("Menu Comptabilité"));
  $MyMenu->addItem(_("Gestion du plan comptable"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ppc-1", 410, "$http_prefix/images/gestion_plan_comptable.gif","1");
  $MyMenu->addItem(_("Gestion des operations"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gop-1", 420, "$http_prefix/images/gestion_operations_comptables.gif","2");
  $MyMenu->addItem(_("Gestion des operations diverses de caisse/compte"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Odc-1", 472, "$http_prefix/images/gestion_operations_caisse.gif","3");
  $MyMenu->addItem(_("Passage des opérations siège/agence"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Osa-1", 473, "$http_prefix/images/operation_agence_siege.gif","4");
  $MyMenu->addItem(_("Annulation des opérations réciproques"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ano-1", 474, "$http_prefix/images/annulation_operations_reciproques.gif","5");
  $MyMenu->addItem(_("Passage d'écritures libres"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ecr-1", 470, "$http_prefix/images/gestion_ecritures_comptables.gif","6");
  $MyMenu->addItem(_("Validation écritures libres"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ecr-1", 471, "$http_prefix/images/approb_dossier.gif","7");
  $MyMenu->addItem(_("Gestion des exercices"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gex-1", 440,"$http_prefix/images/gestion_exercice_comptable.gif","8");
  $MyMenu->addItem(_("Gestion des journaux"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Jou-1", 450,"$http_prefix/images/gestion_journaux_comptable.gif","9");
  $MyMenu->addItem(_("Rapports comptabilité"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Tra-1", 430, "$http_prefix/images/rapports_compta.gif","10");
  $MyMenu->addItem(_("Traitement des transactions Ferlo"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Trs-1", 431, "$http_prefix/images/transaction_ferlo.gif","11");
  $MyMenu->addItem(_("Provision crédits"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pcs-1", 432, "$http_prefix/images/provision_credit.gif","12");
  $MyMenu->addItem(_("Déclarations de tva"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Tva-1", 476, "$http_prefix/images/tva.gif","13");
  $infos_ag = getAgenceDatas($global_id_agence);
  if ($infos_ag['passage_perte_automatique'] == "f")
    $MyMenu->addItem(_("Radiation crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rad-1", 475, "$http_prefix/images/annul_dossier.gif","14");
  $MyMenu->addItem(_("Gestion des écritures libres"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gel-1", 478, "$http_prefix/images/gestion_ecritures_comptables.gif","15");
  $MyMenu->addItem(_("Retour menu principal"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-3", 0, "$http_prefix/images/back.gif","0");
  $MyMenu->buildHTML();
  echo $MyMenu->HTMLCode;
}


/*}}}*/

/*{{{ Gen-15 : Menu principal : Budget */
else if ($global_nom_ecran == "Gen-15") {

  //initialise les variables ... si jamais on vient d'un écran où le client était sélectionné
  resetVariablesGlobalesClient();

  $MyMenu = new HTML_menu_gen(_("Gestion du Budget"));
  $MyMenu->addItem(_("Gestion des tables de correspondances"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gtc-1", 701, "$http_prefix/images/param_tables.gif");
  $MyMenu->addItem(_("Mise en Place du Budget Annuel"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Meb-1", 705, "$http_prefix/images/nouveau_dossier.gif");
  $MyMenu->addItem(_("Raffiner le Budget"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rlb-1", 706, "$http_prefix/images/correct_dossier.gif");
  $MyMenu->addItem(_("Reviser le Budget"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rdb-1", 707, "$http_prefix/images/ajustement_solde.gif");
  $MyMenu->addItem(_("Valider le Budget"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Vlb-1", 708, "$http_prefix/images/approb_dossier.gif");
  $MyMenu->addItem(_("Mise en Place Nouvelle Ligne Budgetaire"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mnl-1", 716, "$http_prefix/images/nouveau_dossier.gif");
  $MyMenu->addItem(_("Validation Nouvelle Ligne Budgetaire"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Vnl-1", 717, "$http_prefix/images/approb_dossier.gif");
  $MyMenu->addItem(_("Visualisation du Budget"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Vdb-1", 712, "$http_prefix/images/visualisation_transactions.gif");
  $MyMenu->addItem(_("Visualisation des Comptes Comptables bloqués"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dcc-1", 713, "$http_prefix/images/visualisation_transactions.gif");
  $MyMenu->addItem(_("Rapports Budget"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rpb-1", 715, "$http_prefix/images/rapport.gif");
  $MyMenu->addItem(_("Retour Menu Principal"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-3", 0, "$http_prefix/images/back.gif");
  $MyMenu->buildHTML();
  echo $MyMenu->HTML_header_code;

  echo $MyMenu->HTML_body_code;
}
/*}}}*/

/*{{{ Gen-16 : Menu principal : Agency Banking */
else if ($global_nom_ecran == "Gen-16") {

  //initialise les variables ... si jamais on vient d'un écran où le client était sélectionné
  resetVariablesGlobalesClient();

  $MyMenu = new HTML_menu_gen(_("Gestion Agency Banking"));
  //$MyMenu->addItem(_("Gestion des tables de correspondances"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gtc-1", 701, "$http_prefix/images/param_tables.gif");
$MyMenu->addItem(_("Création client par agent"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Cpa-1", 762, "$http_prefix/images/ajout_client.gif");
$MyMenu->addItem(_("Consultation d'un compte via agent"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ccv-1", 789, "$http_prefix/images/consult_cpt.gif");
$MyMenu->addItem(_("Dépôt via agent"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dva-1", 763, "$http_prefix/images/depot.gif");
$MyMenu->addItem(_("Retrait via agent"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rva-1", 764, "$http_prefix/images/retrait.gif");
$MyMenu->addItem(_("Opération en déplacé via agent"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=God-1", 773, "$http_prefix/images/retrait.gif");
$MyMenu->addItem(_("Gestion annulation dépôts/retraits via agent"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gdr-1", 765, "$http_prefix/images/gestion_operations_caisse.gif");
$MyMenu->addItem(_("Gestion des profils agents"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gpa-1", 776, "$http_prefix/images/gestion_profils.gif");
$MyMenu->addItem(_("Gestion des utilisateurs agents"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Aga-1", 752, "$http_prefix/images/gestion_utilisateurs.gif");
$MyMenu->addItem(_("Gestion des codes utilisateurs agents"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gla-1", 753, "$http_prefix/images/gestion_logins.gif");
$MyMenu->addItem(_("Paramétrage Agency Banking"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mpa-1", 760, "$http_prefix/images/param_tables.gif");
$MyMenu->addItem(_("Demande approvisionnement agent"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Atg-1", 756, "$http_prefix/images/approvisionnement.gif");
$MyMenu->addItem(_("Demande de transfert compte de flotte vers compte courant"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Tcf-1", 759, "$http_prefix/images/delestage.gif");
$MyMenu->addItem(_("Vérification des demandes approvisionnements/transferts agents"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Vda-1", 757, "$http_prefix/images/visu_dernier_rapport.gif");
$MyMenu->addItem(_("Approbation des demandes approvisionnements/transferts agents"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Vdp-1", 758, "$http_prefix/images/gestion_logins.gif");
$MyMenu->addItem(_("Visualisations"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Vis-1", 770, "$http_prefix/images/visualisation_transactions.gif");
$MyMenu->addItem(_("Approbation création client par agents"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Acc-1", 782, "$http_prefix/images/visu_dernier_rapport.gif");
$MyMenu->addItem(_("Effectuer création client par agents"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Eca-1", 783, "$http_prefix/images/gestion_logins.gif");
$MyMenu->addItem(_("Rapports Agency Banking"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rab-1", 787, "$http_prefix/images/rapport.gif");

$MyMenu->addItem(_("Retour Menu Principal"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-3", 0, "$http_prefix/images/back.gif");
$MyMenu->buildHTML();
  echo $MyMenu->HTML_header_code;

  echo $MyMenu->HTML_body_code;
}

/*{{{ Gen-17 : Menu principal : Mobile Lending */
else if ($global_nom_ecran == "Gen-17") {

  //initialise les variables ... si jamais on vient d'un écran où le client était sélectionné
  resetVariablesGlobalesClient();

  $MyMenu = new HTML_menu_gen(_("Gestion Mobile Lending"));
  $MyMenu->addItem(_("Nouveaux clients Mobile Lending"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mle-1", 901, "$http_prefix/images/ajout_client.gif");
  $MyMenu->addItem(_("Dossiers Mobile Lending en attente"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mlt-1", 902, "$http_prefix/images/consult_dossier.gif");
  $MyMenu->addItem(_("Rapport Mobile Lending"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mlr-1", 906, "$http_prefix/images/rapport_client.gif");
  $MyMenu->addItem(_("Retour Menu Principal"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-3", 0, "$http_prefix/images/back.gif");
  $MyMenu->buildHTML();
  echo $MyMenu->HTML_header_code;

  echo $MyMenu->HTML_body_code;
}

/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>
