<?php

/**
 * Gestion des parametrages des commissions institution
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

if ($global_nom_ecran == "Cci-1") {
  global $global_multidevise;

  $MyPage = new HTML_GEN2(_("Saisie des commissions pour l'institution"));
  $html_js .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
  $MyPage->addHTMLExtraCode("html_js",$html_js);

  $data_comm_inst_exist = getCommissionInstitution();
  if (!isset($charge_comm_agent_depot)){
    $charge_comm_agent_depot = $data_comm_inst_exist['choix_depot_comm'];
  }
  if (!isset($cpte_comm_inst_depot)){
    $cpte_comm_inst_depot = $data_comm_inst_exist['cpte_compta_comm_depot'];
  }
  if (!isset($charge_comm_agent_retrait)){
    $charge_comm_agent_retrait = $data_comm_inst_exist['choix_retrait_comm'];
  }
  if(!isset($cpte_comm_inst_retrait)){
    $cpte_comm_inst_retrait = $data_comm_inst_exist['cpte_compta_comm_retrait'];
  }

  $MyPage->addField("charge_comm_agent_depot",_("Prise en charge de la commission de l'agent sur depot"),TYPC_LSB,$charge_comm_agent_depot);
  $MyPage->setFieldProperties("charge_comm_agent_depot", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("charge_comm_agent_depot", FIELDP_HAS_CHOICE_AUCUN, true);
  $MyPage->setFieldProperties("charge_comm_agent_depot",FIELDP_ADD_CHOICES, $adsys['comm_agent_par_inst']);
  //$MyPage->setFieldProperties("charge_comm_agent_depot", FIELDP_JS_EVENT, array("onChange"=>"assign('Cci-1'); this.form.submit();"));


  if (isset($charge_comm_agent_depot)){
    if ($charge_comm_agent_depot == 1) {
      $classe_compta = 6;
    }else {
      $classe_compta = 7;
    }
    $cpte = getCompteComptableAgency(null,$classe_compta);
    $array_depot = array();
    foreach($cpte as $key=>$value) {
      $array_depot[$key] = $value['num_cpte_comptable']." ".$value['libel_cpte_comptable'];
    }
  }

  $MyPage->addField("cpte_comm_inst_depot",_("Compte de commission associé à l'institution"), TYPC_LSB,$cpte_comm_inst_depot);
  $MyPage->setFieldProperties("cpte_comm_inst_depot", FIELDP_ADD_CHOICES, $array_depot);
  $MyPage->setFieldProperties("cpte_comm_inst_depot", FIELDP_HAS_CHOICE_AUCUN, true);
  //$MyPage->setFieldProperties("cpte_comm_inst_depot", FIELDP_IS_REQUIRED, true);




  $MyPage->addField("charge_comm_agent_retrait",_("Prise en charge de la commission de l'agent sur retrait"),TYPC_LSB,$charge_comm_agent_retrait);
  $MyPage->setFieldProperties("charge_comm_agent_retrait", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("charge_comm_agent_retrait", FIELDP_HAS_CHOICE_AUCUN, true);
  $MyPage->setFieldProperties("charge_comm_agent_retrait",FIELDP_ADD_CHOICES, $adsys['comm_agent_par_inst']);
  //$MyPage->setFieldProperties("charge_comm_agent_retrait", FIELDP_JS_EVENT, array("onChange"=>"assign('Cci-1'); this.form.submit();"));

  if (isset($charge_comm_agent_retrait)){
    if ($charge_comm_agent_retrait == 1) {
      $classe_compta = 6;
    }else {
      $classe_compta = 7;
    }
    $cpte = getCompteComptableAgency(null,$classe_compta);
    $array_retrait = array();
    foreach($cpte as $key=>$value) {
      $array_retrait[$key] = $value['num_cpte_comptable']." ".$value['libel_cpte_comptable'];
    }
  }

  $MyPage->addField("cpte_comm_inst_retrait",_("Compte de commission associé à l'institution"), TYPC_LSB,$cpte_comm_inst_retrait);
  $MyPage->setFieldProperties("cpte_comm_inst_retrait", FIELDP_ADD_CHOICES, $array_retrait);
  $MyPage->setFieldProperties("cpte_comm_inst_retrait", FIELDP_HAS_CHOICE_AUCUN, true);
  //$MyPage->setFieldProperties("cpte_comm_inst_retrait", FIELDP_IS_REQUIRED, true);

  //Boutons
  $MyPage->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $MyPage->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Cci-2');
  $MyPage->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-16');
  $MyPage->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

  $js = "
    var default_selection_depot = $('[name=HTML_GEN_LSB_charge_comm_agent_depot]').find('option:selected');
    var default_selection_retrait = $('[name=HTML_GEN_LSB_charge_comm_agent_retrait]').find('option:selected');
    
    $('[name=HTML_GEN_LSB_charge_comm_agent_depot]').change(function(){
        var node = $(this).find('option:selected');
        $(default_selection_depot).removeAttr('selected');
        if($(node).text() == '[Aucun]'){
            $('[name=HTML_GEN_LSB_cpte_comm_inst_depot] option').each(function(index, item){
                $(item).removeAttr('selected');
            });
            $(node).attr('selected', 'selected');
            $('[name=HTML_GEN_LSB_cpte_comm_inst_depot]').attr('selected', 'selected');
        }else{
            $('[name=HTML_GEN_LSB_charge_comm_agent_depot] option').each(function(index, item){
                $(item).removeAttr('selected');
            });
            $(node).attr('selected', 'selected');    
            assign('Cci-1'); this.form.submit();
        }
    });
    
    $('[name=HTML_GEN_LSB_charge_comm_agent_retrait]').change(function(){
        var node = $(this).find('option:selected');
        $(default_selection_retrait).removeAttr('selected');
        if($(node).text() == '[Aucun]'){
            $(node).attr('selected', 'selected');
            $('[name=HTML_GEN_LSB_cpte_comm_inst_retrait] option').each(function(index, item){
                $(item).removeAttr('selected');
            });
            $('[name=HTML_GEN_LSB_cpte_comm_inst_retrait]').attr('selected', 'selected');
        }else{
            $('[name=HTML_GEN_LSB_charge_comm_agent_retrait] option').each(function(index, item){
                $(item).removeAttr('selected');
            });
            $(node).attr('selected', 'selected');  
            assign('Cci-1'); this.form.submit();  
        }
    });
  ";

  //HTML
  $MyPage->addJS(JSP_FORM, "JS1", $js);
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}

else if ($global_nom_ecran == "Cci-2") {
  global $dbHandler, $global_id_agence,$global_nom_login;
  $data_commission_inst = getCommissionInstitution();
  if ($data_commission_inst == null){
    $data_insert = array(
      "choix_retrait_comm" => $charge_comm_agent_retrait,
      "cpte_compta_comm_retrait" => $cpte_comm_inst_retrait,
      "choix_depot_comm" => $charge_comm_agent_depot,
      "cpte_compta_comm_depot" => $cpte_comm_inst_depot,
      "date_creation" => date('d-m-y'),
      "login" => $global_nom_login,
      "id_ag" => $global_id_agence
    );
    $db = $dbHandler->openConnection();
    $result = executeQuery($db, buildInsertQuery("ag_param_commission_institution", $data_insert));
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
    }else{
      $connection = true;
    }$dbHandler->closeConnection(true);

    $html_msg = new HTML_message("Ajout des commissions pour l'institution");

    $html_msg->setMessage(sprintf(" <br /> Les données des commissions ont été ajouté! <br /> "));

    $html_msg->addButton("BUTTON_OK", 'Gen-16');

    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
  else{
    $data_update = array(
      "choix_retrait_comm" => $charge_comm_agent_retrait,
      "cpte_compta_comm_retrait" => $cpte_comm_inst_retrait,
      "choix_depot_comm" => $charge_comm_agent_depot,
      "cpte_compta_comm_depot" => $cpte_comm_inst_depot,
      "date_modif" => date('d-m-y'),
      "login" => $global_nom_login,
    );
    $db1 = $dbHandler->openConnection();
    $result1 = executeQuery($db1, buildUpdateQuery("ag_param_commission_institution", $data_update));
    if (DB::isError($result1)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,$result1->getMessage());
    }else{
      $connection = true;
    }$dbHandler->closeConnection(true);

    $html_msg = new HTML_message("Modification des commissions pour l'institution");

    $html_msg->setMessage(sprintf(" <br /> Les données des commissions ont été modifié! <br /> "));

    $html_msg->addButton("BUTTON_OK", 'Gen-16');

    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }

}

/*}}}*/
else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
?>