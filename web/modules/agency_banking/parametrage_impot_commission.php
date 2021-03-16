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

if ($global_nom_ecran == "Pic-1"){
  $data_impot = getInfoImpot();

  $MyPage = new HTML_GEN2();
  $MyPage->setTitle(_("Saisie de l'impôt sur commission"));
  $html_js .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
  $MyPage->addHTMLExtraCode("html_js",$html_js);


  $MyPage->addField("prc_impot", _("Montant de l'impôt"), TYPC_PRC);
  $MyPage->setFieldProperties("prc_impot", FIELDP_DEFAULT,$data_impot['prc_import'] );

  if ($global_multidevise)
    $include = getNomsComptesComptables(array("devise" => NULL,
      "compart_cpte" => 2    // Passif
    ));
  else
    $include = getNomsComptesComptables(array("compart_cpte" => 2));   // Comptes Passif

  $MyPage->addField("cpte_impot",_("Compte créditeur de l'impôt"), TYPC_LSB,$data_impot['cpte_impot']);
  $MyPage->setFieldProperties("cpte_impot", FIELDP_ADD_CHOICES, $include);
  $MyPage->setFieldProperties("cpte_impot", FIELDP_HAS_CHOICE_AUCUN, true);

  //$MyPage->setFieldProperties("cpte_impot", FIELDP_INCLUDE_CHOICES, array_keys($include));

  $MyPage->addField("appl_impot_agent", _("Prélever l'impôt sur la commission de l'agent"), TYPC_BOL,$data_impot['appl_impot_agent']);
  $MyPage->addField("appl_impot_inst", _("Prélever l'impôt sur la commission de l'institution"), TYPC_BOL,$data_impot['appl_impot_institution']);

  //Boutons
  $MyPage->addFormButton(1, 1, "butok", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butok", BUTP_PROCHAIN_ECRAN, "Pic-2");
  $MyPage->addFormButton(1, 2, "butno", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butno", BUTP_PROCHAIN_ECRAN, "Mpa-1");
  $MyPage->setFormButtonProperties("butno", BUTP_CHECK_FORM, false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
else if ($global_nom_ecran == "Pic-2"){
  $data_impot = getInfoImpot();

  if (isset($appl_impot_agent) && $appl_impot_agent == 1) {
    $appl_impot_agent = 't';
  }
  if (isset($appl_impot_inst) && $appl_impot_inst == 1) {
    $appl_impot_inst = 't';
  }
  $data = array(
    "prc_import" => $prc_impot,
    "cpte_impot" => $cpte_impot,
    "appl_impot_agent" => $appl_impot_agent,
    "appl_impot_institution" => $appl_impot_inst,
    "date_creation" => date('d-m-y'),
    "login" => $global_nom_login,
    "id_ag" => $global_id_agence
  );

  updateOperationCompta(629, 'd', $data['cpte_impot']);
  
  $db = $dbHandler->openConnection();
  if (sizeof($data_impot)> 0){
    $result = executeQuery($db, buildUpdateQuery("ag_param_impot", $data));
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,$result1->getMessage());
    }else{
      $connection = true;
    }$dbHandler->closeConnection(true);

    $html_msg = new HTML_message("Modification des informations sur l'impôt");

    $html_msg->setMessage(sprintf(" <br /> Les données de l'impôt ont été modifié! <br /> "));

    $html_msg->addButton("BUTTON_OK", 'Mpa-1');

    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }else {
    $result = executeQuery($db, buildInsertQuery("ag_param_impot", $data));
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    } else {
      $dbHandler->closeConnection(true);
      $html_msg = new HTML_message("Enregistrement des informations sur l'impôt sur commission ");

      $html_msg->setMessage(sprintf(" <br /> Vos informations ont été enregistré.<br /> "));

      $html_msg->addButton("BUTTON_OK", 'Mpa-1');

      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
    }
  }

}



?>

