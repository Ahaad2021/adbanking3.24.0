<?php

/**
 * Gestion des parametrages des demandes approvisionnement et transferts de flotte
 * @package Parametrage
 */

require_once 'lib/dbProcedures/utilisateurs.php';
require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/agency_banking.php';
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/misc/divers.php';

// Atg-1 : Saisie du type d'approvisionnement a faire
if ($global_nom_ecran == "Atg-1"){
  global $global_multidevise;

  $MyPage = new HTML_GEN2(_("Type de demande d'approvisionnement"));


  $MyPage->addField("type_appro",_("Source de fond"),TYPC_LSB);
  $MyPage->setFieldProperties("type_appro", FIELDP_HAS_CHOICE_AUCUN, true);
  $MyPage->setFieldProperties("type_appro", FIELDP_IS_REQUIRED, true);
  $array_type_appro = array(
    1 => $adsys['type_appro_agent'][1],
    2 => $adsys['type_appro_agent'][2]
  );
  $MyPage->setFieldProperties("type_appro",FIELDP_ADD_CHOICES, $array_type_appro);


  //Boutons
  $MyPage->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $MyPage->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Atg-2');
  $MyPage->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-16');
  $MyPage->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}

// Atg-2 : Saisie des donnees pour la demande d'approvisionnement
else if ($global_nom_ecran == "Atg-2"){
  $MyPage = new HTML_GEN2(_("Saisie demande d'approvisionnement"));
  $data_login = getDatasLogin();

  $data_util = getDataUtilisateur($data_login['id_utilisateur']);
  if ($data_util['is_agent_ag'] == 'f'){
    $erreur = new HTML_erreur(_("Login non autorisé"));
    $erreur->setMessage(_("Vous n'etes pas un utilisateur Agency Banking"));
    $erreur->addButton(BUTTON_OK,"Gen-16");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    $ok = false;
    die();
  }

  $MyPage->addField("type_appro", _("Type de transaction"), TYPC_TXT);
  $MyPage->setFieldProperties("type_appro", FIELDP_DEFAULT, adb_gettext($adsys['type_appro_agent'][$type_appro]));
  $MyPage->setFieldProperties("type_appro", FIELDP_IS_LABEL, true);


  $cpte_source = getDataCpteEpargne($data_login['cpte_base_agent']);

  $MyPage->addField("num_cpte_complet", _("Compte source"), TYPC_TXT);
  $MyPage->setFieldProperties("num_cpte_complet", FIELDP_DEFAULT, $cpte_source['num_complet_cpte']);
  $MyPage->setFieldProperties("num_cpte_complet", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("num_cpte_complet", FIELDP_IS_LABEL, true);

  $MyPage->addField("mnt_appro", _("Montant à approvisionner"), TYPC_INT);
  $MyPage->setFieldProperties("mnt_appro", FIELDP_DEFAULT, 0);
  $MyPage->setFieldProperties("mnt_appro", FIELDP_IS_REQUIRED, true);

  if ($type_appro == 2) {
    $MyPage->addField("nom_banque", _("Nom Banque"), TYPC_TXT);
    $MyPage->setFieldProperties("nom_banque", FIELDP_IS_REQUIRED, true);

    $MyPage->addField("date_piece", _("Date pièce"), TYPC_DTE);
    $MyPage->setFieldProperties("date_piece", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("date_piece", FIELDP_DEFAULT, date("d/m/Y"));

    $MyPage->addField("num_versement", _("Référence versement bancaire (Numéro)"), TYPC_TXT);
    $MyPage->setFieldProperties("num_versement", FIELDP_IS_REQUIRED, true);
  }

  $MyPage->addField("communication", _("Communication"), TYPC_TXT);

  $MyPage->addField("remarque", _("Remarque"), TYPC_ARE);

  $MyPage->addHiddenType("cpte_base",$data_login['cpte_base_agent']);
  $MyPage->addHiddenType("num_cpte_comptable",$cpte_source['num_cpte_comptable']);
  $MyPage->addHiddenType("type_approvisionnement_transfert",$type_appro);
  $MyPage->addHiddenType("cpte_flotte",$data_login['cpte_flotte_agent']);
  $MyPage->addHiddenType("devise",$type_appro);

  //Boutons

  $MyPage->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $MyPage->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $param_proces = get_param_appro_trans();
  if ($param_proces['autorisation_appro'] == 't'){
    $MyPage->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Atg-3');
  }else{
    $MyPage->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Atg-4');
  }
  $MyPage->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-16');
  $MyPage->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();

}

// Atg-3 : ajout des demandes d'approvisionnement
else if ($global_nom_ecran == "Atg-3"){
  global $global_id_agence;$global_nom_login;$global_monnaie_courante;

  $data_appro_transfert = array(
    "type_transaction" => $type_approvisionnement_transfert,
    "num_cpte_base" => $cpte_base,
    "num_cpte_flotte" => $cpte_flotte,
    "etat_appro" => 1,
    "login_agent" => $global_nom_login,
    "montant" => $mnt_appro,
    "devise" => $global_monnaie_courante,
    "nom_banque" => $nom_banque,
    "ref_versement" => $num_versement,
    "date_creation" => date('d-m-y'),
    "id_ag" =>$global_id_agence
  );

  $db = $dbHandler->openConnection();
  $result = executeQuery($db, buildInsertQuery("ag_approvisionnement_transfert", $data_appro_transfert));
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }else {
    $dbHandler->closeConnection(true);
    $html_msg = new HTML_message("Demande d'approvisionnement de compte de flotte");

    $html_msg->setMessage(sprintf(" <br /> Votre demande a été enregistré.<br /> "));

    $html_msg->addButton("BUTTON_OK", 'Gen-16');

    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
}

else if ($global_nom_ecran == "Atg-4"){
  $login_agent = getDatasLogin();
  $InfoCpte = getAccountDatas($login_agent['cpte_base_agent']);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
  if (isset($mnt_appro)){
    $mnt = $mnt_appro;
  }
  if (isset ($mnt_trans)){
    $mnt = $mnt_trans;

  }

  if ($type_approvisionnement_transfert != 3) {
    $result = approvisionnementAgent($InfoCpte, $login_agent['cpte_base_agent'], $mnt, $login_agent, $InfoProduit);
  } else {
    $result = transfertAgent($InfoCpte, $login_agent['cpte_base_agent'], $mnt, $login_agent, $InfoProduit);
  }


  if ($result->errCode == NO_ERR) {

    $data_insert = array(
      "type_transaction" => $type_approvisionnement_transfert,
      "num_cpte_base" => $login_agent['cpte_base_agent'],
      "num_cpte_flotte" => $login_agent['cpte_flotte_agent'],
      "etat_appro" => 3,
      "login_agent" => $global_nom_login,
      "montant" => $mnt,
      "devise" => $global_monnaie_courante,
      "nom_banque" => $nom_banque,
      "ref_versement" => $num_versement,
      "date_creation" => date('d-m-y'),
      "id_ag" =>$global_id_agence,
      "motif" => "transfert sans processus",
      "id_his" => $result->param['id']
    );
    $db = $dbHandler->openConnection();
    $result = executeQuery($db, buildInsertQuery("ag_approvisionnement_transfert", $data_insert));
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
    $dbHandler->closeConnection(true);
    $html_msg = new HTML_message(_("Confirmation de l'approvisionnement/transfert agent"));
    if ($type_approvisionnement_transfert != 3){
      $message = "
             <table><tr><td>" . _("Le montant approvisionné") . " : </td>
             <td>" . afficheMontant($mnt, true) . "</td>
             </tr>";
    }else{
      $message = "
             <table><tr><td>" . _("Le montant transféré") . " : </td>
             <td>" . afficheMontant($mnt, true) . "</td>
             </tr>";
    }

    $message .= "
              </table>";
    $html_msg->setMessage($message);

    $html_msg->addButton("BUTTON_OK", 'Gen-16');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
}


// Vda-1 : Liste des demande d'approvisionnement
else if ($global_nom_ecran == "Vda-1"){


  // Affichage de la liste des mouvements
  $table = new HTML_TABLE_table(6, TABLE_STYLE_ALTERN);
  $table->set_property("title", "Liste de demandes approvisionnements/transferts");
  $table->add_cell(new TABLE_cell("N°"));
  $table->add_cell(new TABLE_cell("Type de transaction"));
  $table->add_cell(new TABLE_cell("Login demandeur"));
  $table->add_cell(new TABLE_cell("Montant"));
  $table->add_cell(new TABLE_cell("Date demande"));
  $table->add_cell(new TABLE_cell("Action"));

  $type_trans = "1,2";
  $liste_dem_appro_trans = getListeApprovisionnementTransfert(null,1);

  foreach ($liste_dem_appro_trans as $id => $liste) {

    $id_demande = trim($liste["id"]);
    $type_trans = adb_gettext($adsys['type_appro_agent'][$liste["type_transaction"]]);
    $login = $liste["login_agent"];
    $montant = afficheMontant($liste['montant'])." ".$liste['devise'];
    $date_dem= pg2phpDate($liste["date_creation"]);

    $prochain_ecran = "Vda-2";


    $table->add_cell(new TABLE_cell($id_demande));
    $table->add_cell(new TABLE_cell($type_trans));
    $table->add_cell(new TABLE_cell($login));
    $table->add_cell(new TABLE_cell($montant));
    $table->add_cell(new TABLE_cell($date_dem));
    $table->add_cell(new TABLE_cell("<a href=" . $PHP_SELF . "?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=" . $prochain_ecran . "&id_dem=" . $id_demande . ">Valider/Refuser la demande</a>"));
    $table->set_row_property("height", "35px");
  }
  echo $table->gen_HTML();
}

// Vda-2 : Verification des donnees de la demande d'approvisionnement
else if ($global_nom_ecran == "Vda-2"){

  $MyPage = new HTML_GEN2(_("Verification de la demande de l'agent"));
  $SESSION_VARS['id_dem'] = $_GET['id_dem'];
  $id = $_GET['id_dem'];
  $data_appro = getApprovisionnementTransfertAgent($id);

  $MyPage->addField("src_fond", _("Source de fonds"), TYPC_TXT);
  $MyPage->setFieldProperties("src_fond", FIELDP_DEFAULT, adb_gettext($adsys['type_appro_agent'][$data_appro['type_transaction']]));
  $MyPage->setFieldProperties("src_fond", FIELDP_IS_LABEL, true);

  $MyPage->addField("agent_dem", _("Agent demandeur"), TYPC_TXT);
  $MyPage->setFieldProperties("agent_dem", FIELDP_DEFAULT,$data_appro['login_agent']);
  $MyPage->setFieldProperties("agent_dem", FIELDP_IS_LABEL, true);

  $MyPage->addField("etat_dem", _("Etat demande"), TYPC_TXT);
  $MyPage->setFieldProperties("etat_dem", FIELDP_DEFAULT,adb_gettext($adsys['etat_appro_trans'][$data_appro['etat_appro']]));
  $MyPage->setFieldProperties("etat_dem", FIELDP_IS_LABEL, true);

  $MyPage->addField("date_dem", _("Date demande"), TYPC_TXT);
  $MyPage->setFieldProperties("date_dem", FIELDP_DEFAULT,pg2phpDate($data_appro['date_creation']));
  $MyPage->setFieldProperties("date_dem", FIELDP_IS_LABEL, true);

  $MyPage->addField("mnt_dem", _("Montant demande"), TYPC_TXT);
  $MyPage->setFieldProperties("mnt_dem", FIELDP_DEFAULT,afficheMontant($data_appro['montant'])." ".$data_appro['devise']);
  $MyPage->setFieldProperties("mnt_dem", FIELDP_IS_LABEL, true);

  if ($data_appro['type_transaction'] == 2){
    $MyPage->addField("nom_banque", _("Nom de la banque"), TYPC_TXT);
    $MyPage->setFieldProperties("nom_banque", FIELDP_DEFAULT,$data_appro['nom_banque']);
    $MyPage->setFieldProperties("nom_banque", FIELDP_IS_LABEL, true);

    $MyPage->addField("ref_banque", _("Référence versement bancaire"), TYPC_TXT);
    $MyPage->setFieldProperties("ref_banque", FIELDP_DEFAULT,$data_appro['ref_versement']);
    $MyPage->setFieldProperties("ref_banque", FIELDP_IS_LABEL, true);
  }

  $MyPage->addField("motif", _("Motif"), TYPC_ARE);

  //Boutons
  $MyPage->addFormButton(1, 1, "ok", _("Valider verification"), TYPB_SUBMIT);
  $MyPage->addFormButton(1, 2, "rejet", _("Rejeter verification"), TYPB_SUBMIT);
  $MyPage->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Vda-3');
  $MyPage->setFormButtonProperties("rejet", BUTP_PROCHAIN_ECRAN, 'Vda-3');
  $MyPage->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick" =>
    "if (!confirm('" . _("ATTENTION") . "\\n " . _("Vous allez  Valider la demande de cet agent! \\nEtes-vous sur de vouloir confirmer votre choix ? ") . "')) return false;
        "));
  $MyPage->setFormButtonProperties("rejet", BUTP_JS_EVENT, array("onclick" =>
    "if (!confirm('" . _("ATTENTION") . "\\n " . _("Vous allez  Rejeter la demande de cet agent! \\nEtes-vous sur de vouloir confirmer votre choix ? ") . "')) return false;
        "));
  $MyPage->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Vda-1');
  $MyPage->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();

}

// Vda-3 : Confirmation de la verification des donnees
else if ($global_nom_ecran == "Vda-3"){
  global $global_id_agence;$global_nom_login;

  if (isset($ok)) {
    $etat_dem = 2;
  }else if (isset($rejet)){
    $etat_dem = 4;
  }

  $data_modif = array(
    "etat_appro" => $etat_dem,
    "motif" => $motif,
    "date_modif" => date('d-m-y'),
    "login_util" => $global_nom_login
  );
  $data_condi = array(
    "id" => $SESSION_VARS['id_dem']
  );
  $db = $dbHandler->openConnection();
  $result = executeQuery($db, buildUpdateQuery("ag_approvisionnement_transfert", $data_modif,$data_condi));
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }else {
    $dbHandler->closeConnection(true);
    $html_msg = new HTML_message("Verification de la demande d'approvisionnement/transferts");

    $html_msg->setMessage(sprintf(" <br /> Votre demande a été traité.<br /> "));

    $html_msg->addButton("BUTTON_OK", 'Gen-16');

    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }

}

// Vdp-1 : Liste des demandes d'appro Verifiés
else if ($global_nom_ecran == "Vdp-1"){

  // Affichage de la liste des mouvements
  $table = new HTML_TABLE_table(6, TABLE_STYLE_ALTERN);
  $table->set_property("title", "Liste de demandes approvisionnements/transferts verifiées");
  $table->add_cell(new TABLE_cell("N°"));
  $table->add_cell(new TABLE_cell("Type de transaction"));
  $table->add_cell(new TABLE_cell("Login demandeur"));
  $table->add_cell(new TABLE_cell("Montant"));
  $table->add_cell(new TABLE_cell("Date demande"));
  $table->add_cell(new TABLE_cell("Action"));

  $liste_dem_appro_trans = getListeApprovisionnementTransfert(null,2);

  foreach ($liste_dem_appro_trans as $id => $liste) {

    $id_demande = trim($liste["id"]);
    $type_trans = adb_gettext($adsys['type_appro_agent'][$liste["type_transaction"]]);
    $login = $liste["login_agent"];
    $montant = afficheMontant($liste['montant'])." ".$liste['devise'];
    $date_dem= pg2phpDate($liste["date_creation"]);

    $prochain_ecran = "Vdp-2";


    $table->add_cell(new TABLE_cell($id_demande));
    $table->add_cell(new TABLE_cell($type_trans));
    $table->add_cell(new TABLE_cell($login));
    $table->add_cell(new TABLE_cell($montant));
    $table->add_cell(new TABLE_cell($date_dem));
    $table->add_cell(new TABLE_cell("<a href=" . $PHP_SELF . "?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=" . $prochain_ecran . "&id_dem=" . $id_demande . ">Valider/Refuser la demande</a>"));
    $table->set_row_property("height", "35px");
  }
  echo $table->gen_HTML();
}

// Vdp-2 : Verification des donnees de la demande d'approvisionnement
else if ($global_nom_ecran == "Vdp-2"){

  $MyPage = new HTML_GEN2(_("Verification de la demande de l'agent"));
  $SESSION_VARS['id_dem'] = $_GET['id_dem'];
  $id = $_GET['id_dem'];
  $data_appro = getApprovisionnementTransfertAgent($id);

  $MyPage->addField("src_fond", _("Source de fonds"), TYPC_TXT);
  $MyPage->setFieldProperties("src_fond", FIELDP_DEFAULT, adb_gettext($adsys['type_appro_agent'][$data_appro['type_transaction']]));
  $MyPage->setFieldProperties("src_fond", FIELDP_IS_LABEL, true);

  $MyPage->addField("agent_dem", _("Agent demandeur"), TYPC_TXT);
  $MyPage->setFieldProperties("agent_dem", FIELDP_DEFAULT,$data_appro['login_agent']);
  $MyPage->setFieldProperties("agent_dem", FIELDP_IS_LABEL, true);

  $MyPage->addField("etat_dem", _("Etat demande"), TYPC_TXT);
  $MyPage->setFieldProperties("etat_dem", FIELDP_DEFAULT,adb_gettext($adsys['etat_appro_trans'][$data_appro['etat_appro']]));
  $MyPage->setFieldProperties("etat_dem", FIELDP_IS_LABEL, true);

  $MyPage->addField("date_dem", _("Date demande"), TYPC_TXT);
  $MyPage->setFieldProperties("date_dem", FIELDP_DEFAULT,pg2phpDate($data_appro['date_creation']));
  $MyPage->setFieldProperties("date_dem", FIELDP_IS_LABEL, true);

  $MyPage->addField("mnt_dem", _("Montant demande"), TYPC_TXT);
  $MyPage->setFieldProperties("mnt_dem", FIELDP_DEFAULT,afficheMontant($data_appro['montant'])." ".$data_appro['devise']);
  $MyPage->setFieldProperties("mnt_dem", FIELDP_IS_LABEL, true);

  if ($data_appro['type_transaction'] == 2){
    $MyPage->addField("nom_banque", _("Nom de la banque"), TYPC_TXT);
    $MyPage->setFieldProperties("nom_banque", FIELDP_DEFAULT,$data_appro['nom_banque']);
    $MyPage->setFieldProperties("nom_banque", FIELDP_IS_LABEL, true);

    $MyPage->addField("ref_banque", _("Référence versement bancaire"), TYPC_TXT);
    $MyPage->setFieldProperties("ref_banque", FIELDP_DEFAULT,$data_appro['ref_versement']);
    $MyPage->setFieldProperties("ref_banque", FIELDP_IS_LABEL, true);
  }

  $MyPage->addField("motif", _("Motif"), TYPC_ARE);

  //Boutons
  $MyPage->addFormButton(1, 1, "ok", _("Approuver la demande"), TYPB_SUBMIT);
  $MyPage->addFormButton(1, 2, "rejet", _("Rejeter la demande"), TYPB_SUBMIT);
  $MyPage->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Vdp-3');
  $MyPage->setFormButtonProperties("rejet", BUTP_PROCHAIN_ECRAN, 'Vdp-3');
  $MyPage->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick" =>
    "if (!confirm('" . _("ATTENTION") . "\\n " . _("Vous allez  Approuver la demande de cet agent! \\nEtes-vous sur de vouloir confirmer votre choix ? ") . "')) return false;
        "));
  $MyPage->setFormButtonProperties("rejet", BUTP_JS_EVENT, array("onclick" =>
    "if (!confirm('" . _("ATTENTION") . "\\n " . _("Vous allez  Rejeter la demande de cet agent! \\nEtes-vous sur de vouloir confirmer votre choix ? ") . "')) return false;
        "));
  $MyPage->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-16');
  $MyPage->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();

}

// Vdp-3 : Confirmation de l'approvisionnement de l'agent
else if ($global_nom_ecran == "Vdp-3"){
  global $dbHandler, $global_id_agence,$global_nom_ecran_prec;

  if ($global_nom_ecran_prec == "Vdp-2") {

    $data_appro = getApprovisionnementTransfertAgent($SESSION_VARS['id_dem']);
    $montant_appr = $data_appro['montant'];
    $data_agent = getDatasLogin($data_appro['login_agent']);
    $InfoCpte = getAccountDatas($data_agent['cpte_base_agent']);
    $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);


    if (isset($ok)){

      if ($data_appro['type_transaction'] != 3) {
        $result = approvisionnementAgent($InfoCpte, $data_appro['num_cpte_base'], $montant_appr, $data_agent, $InfoProduit);
      } else {
        $result = transfertAgent($InfoCpte, $data_appro['num_cpte_base'], $montant_appr, $data_agent, $InfoProduit);
      }
      if ($result->errCode == NO_ERR) {

        $data_approval = array(
          "etat_appro" => 3,
          "motif" => $motif,
          "id_his" => $result->param['id']
        );
        $data_condi = array(
          "id" => $SESSION_VARS['id_dem']
        );
        $db = $dbHandler->openConnection();
        $result = executeQuery($db, buildUpdateQuery("ag_approvisionnement_transfert", $data_approval, $data_condi));
        if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
        }
        $dbHandler->closeConnection(true);
        if ($data_appro['type_transaction'] != 3) {
          $html_msg = new HTML_message(_("Confirmation du l'approvisionnement"));
          $message = "
             <table><tr><td>" . _("Le montant approvisionné") . " : </td>
             <td>" . afficheMontant($montant_appr, true) . "</td>
             </tr>";
        }else{
          $html_msg = new HTML_message(_("Confirmation du transfert"));
          $message = "
             <table><tr><td>" . _("Le montant transféré") . " : </td>
             <td>" . afficheMontant($montant_appr, true) . "</td>
             </tr>";
        }
        $message .= "
              </table>";
        $html_msg->setMessage($message);



        $html_msg->addButton("BUTTON_OK", 'Gen-16');
        $html_msg->buildHTML();
        echo $html_msg->HTML_code;
      }
      else {
        $html_err = new HTML_erreur("Echec lors de la demande autorisation approvisionnement/delestage.");

        $err_msg = $error[$myErr->errCode];

        $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Gen-16');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }

    }
    else if (isset($rejet)){

      $data_approval = array(
        "etat_appro" => 4,
        "motif" => $motif,
        "id_his" => $result->param['id']
      );
      $data_condi = array(
        "id" => $SESSION_VARS['id_dem']
      );
      $db = $dbHandler->openConnection();
      $result = executeQuery($db, buildUpdateQuery("ag_approvisionnement_transfert", $data_approval, $data_condi));
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
      }
      $dbHandler->closeConnection(true);
      if ($data_appro['type_transaction'] != 3) {
        $html_msg = new HTML_message(_("Rejet de l'approvisionnement"));
        $message = "
             <table><tr><td>" . _("La demande d'approvisionnement a été rejeté") . " : </td>
             <td>" . afficheMontant($montant_appr, true) . "</td>
             </tr>";
      }else{
        $html_msg = new HTML_message(_("Rejet du transfert"));
        $message = "
             <table><tr><td>" . _("La demande du transfert a été rejeté") . " : </td>
             <td>" . afficheMontant($montant_appr, true) . "</td>
             </tr>";
      }
      $message .= "
              </table>";
      $html_msg->setMessage($message);

      $html_msg->addButton("BUTTON_OK", 'Gen-16');
      $html_msg->buildHTML();
      echo $html_msg->HTML_code;

    }
  }
}

// Tcf-1 : Saisie des donnees pour le transfert de compte de flotte vers compte courant
else if ($global_nom_ecran == "Tcf-1"){
  $MyPage = new HTML_GEN2(_("Saisie demande de transfert"));
  $data_login = getDatasLogin();

  $data_util = getDataUtilisateur($data_login['id_utilisateur']);
  if ($data_util['is_agent_ag'] == 'f'){
    $erreur = new HTML_erreur(_("Login non autorisé"));
    $erreur->setMessage(_("Vous n'etes pas un utilisateur Agency Banking"));
    $erreur->addButton(BUTTON_OK,"Gen-16");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    $ok = false;
    die();
  }

  $MyPage->addField("type_trans", _("Type de transaction"), TYPC_TXT);
  $MyPage->setFieldProperties("type_trans", FIELDP_DEFAULT, adb_gettext($adsys['type_appro_agent'][3]));
  $MyPage->setFieldProperties("type_trans", FIELDP_IS_LABEL, true);

  $where = array('num_cpte_comptable' => $data_login['cpte_flotte_agent']);
  $nomCompte = getNomsComptesComptables($where);

  $MyPage->addField("cpte_flotte", _("Compte de flotte"), TYPC_TXT);
  $MyPage->setFieldProperties("cpte_flotte", FIELDP_DEFAULT, $nomCompte[$data_login['cpte_flotte_agent']]);
  $MyPage->setFieldProperties("cpte_flotte", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("cpte_flotte", FIELDP_IS_LABEL, true);

  $MyPage->addField("mnt_trans", _("Montant à transferer"), TYPC_INT);
  $MyPage->setFieldProperties("mnt_trans", FIELDP_DEFAULT, 0);
  $MyPage->setFieldProperties("mnt_trans", FIELDP_IS_REQUIRED, true);

  $MyPage->addField("communication", _("Communication"), TYPC_TXT);

  $MyPage->addField("remarque", _("Remarque"), TYPC_ARE);

  $MyPage->addHiddenType("cpte_base",$data_login['cpte_base_agent']);
  $MyPage->addHiddenType("cpte_flotte_agent",$data_login['cpte_flotte_agent']);
   $MyPage->addHiddenType("type_approvisionnement_transfert",3);

  //Boutons
  $MyPage->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $MyPage->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $param_proces = get_param_appro_trans();
  if ($param_proces['autorisation_transfert'] == 't'){
    $MyPage->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Tcf-2');
  }else{
    $MyPage->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Atg-4');
  }

  $MyPage->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-16');
  $MyPage->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();

}

else if ($global_nom_ecran == "Tcf-2"){
  global $global_id_agence;$global_nom_login;$global_monnaie_courante;

  $data_transfert = array(
    "type_transaction" => 3,
    "num_cpte_base" => $cpte_base,
    "num_cpte_flotte" => $cpte_flotte_agent,
    "etat_appro" => 1,
    "login_agent" => $global_nom_login,
    "montant" => $mnt_trans,
    "devise" => $global_monnaie_courante,
    "date_creation" => date('d-m-y'),
    "id_ag" =>$global_id_agence
  );

  $db = $dbHandler->openConnection();
  $result = executeQuery($db, buildInsertQuery("ag_approvisionnement_transfert", $data_transfert));
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }else {
    $dbHandler->closeConnection(true);
    $html_msg = new HTML_message("Demande de transfert de compte de flotte vers compte courant");

    $html_msg->setMessage(sprintf(" <br /> Votre demande a été enregistré.<br /> "));

    $html_msg->addButton("BUTTON_OK", 'Gen-16');

    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
}
else
  signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'écran '$global_nom_ecran' n'a pas été trouvé"
?>