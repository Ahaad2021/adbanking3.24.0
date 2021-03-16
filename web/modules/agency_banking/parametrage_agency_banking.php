<?php

/**
 * Gestion des logins
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
require_once "lib/html/HTML_menu_gen.php";

if ($global_nom_ecran == "Mpa-1") {
  $MyMenu = new HTML_menu_gen(_("Paramétrage Agency Banking"));
  $MyMenu->addItem(_("Paramétrage des commissions"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pag-1", 754, "$http_prefix/images/param_tables.gif");
  $MyMenu->addItem(_("Paramétrage du compte de commission de l'institution"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Cci-1", 755, "$http_prefix/images/virement_netbank.gif");
  $MyMenu->addItem(_("Paramétrage approvisionnement/transfert compte de flotte"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Atb-1", 761, "$http_prefix/images/virement_netbank.gif");
  $MyMenu->addItem(_("Paramétrage impôt sur commission"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pic-1", 785, "$http_prefix/images/virement_netbank.gif");
  $MyMenu->addItem(_("Retour Menu Agency Banking"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-16", 0, "$http_prefix/images/back.gif");
  $MyMenu->buildHTML();
  echo $MyMenu->HTMLCode;
}

else if ($global_nom_ecran == "Atb-1") {

  $MyPage = new HTML_GEN2();
  $MyPage->setTitle(_("Parametrage des approvisionnements/transferts compte de flotte"));
  $data_param_appro = get_param_appro_trans();
  if($data_param_appro['autorisation_appro'] == 'f')$data_param_appro['autorisation_appro']= FALSE;

  if($data_param_appro['autorisation_transfert'] == 'f')$data_param_appro['autorisation_transfert']= FALSE;

  $MyPage->addField("is_appro_direct", _("Demande d'autorisation  pour approvisionnement compte de flotte?"), TYPC_BOL,$data_param_appro['autorisation_appro']);
  $MyPage->addField("is_transf_direct", _("Demande d'autorisation pour transfert compte de flotte vers compte courant?"), TYPC_BOL,$data_param_appro['autorisation_transfert']);

  //Bouton Afficher tous les utilisateurs

  $MyPage->addFormButton(1, 1, "butVal", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butVal", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butVal", BUTP_AXS, 761);
  $MyPage->setFormButtonProperties("butVal", BUTP_PROCHAIN_ECRAN, "Atb-2");

  //Bouton retour
  $MyPage->addFormButton(1, 2, "butRet", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butRet", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butRet", BUTP_PROCHAIN_ECRAN, "Mpa-1");
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}

else if ($global_nom_ecran == "Atb-2") {
  global $dbHandler, $global_id_agence;

  if (isset($is_appro_direct)) {
    $is_appro_direct = 't';
  } else {
    $is_appro_direct = 'f';
  }

  if (isset($is_transf_direct)) {
    $is_transf_direct = 't';
  } else {
    $is_transf_direct = 'f';
  }

  $count_param = checkIfParamApproTransExist();
  if ($count_param['count'] == 0) {
    $array_insert = array(
      "autorisation_appro" => $is_appro_direct,
      "autorisation_transfert" => $is_transf_direct,
      "date_creation" => date('d-m-y'),
      "id_ag" => $global_id_agence
    );
    $db = $dbHandler->openConnection();
    $result = executeQuery($db, buildInsertQuery("ag_param_appro_transfert", $array_insert));
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    } else {
      $dbHandler->closeConnection(true);
      $html_msg = new HTML_message("Parametrage approvisionnement/transfert terminé");

      $html_msg->setMessage(sprintf(" <br /> Vos parametrages ont été enregistré.<br /> "));

      $html_msg->addButton("BUTTON_OK", 'Mpa-1');

      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
    }
  } else {
    $array_modif = array(
      "autorisation_appro" => $is_appro_direct,
      "autorisation_transfert" => $is_transf_direct,
      "date_modif" => date('d-m-y')
    );
    $db = $dbHandler->openConnection();
    $result = executeQuery($db, buildUpdateQuery("ag_param_appro_transfert", $array_modif));
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    } else {
      $dbHandler->closeConnection(true);
      $html_msg = new HTML_message("Parametrage approvisionnement/transfert terminé");

      $html_msg->setMessage(sprintf(" <br /> Vos parametrages ont été modifié.<br /> "));

      $html_msg->addButton("BUTTON_OK", 'Mpa-1');

      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
    }
  }
}

?>