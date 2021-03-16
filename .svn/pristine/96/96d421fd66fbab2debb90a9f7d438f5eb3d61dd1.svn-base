
<?php
/**
 * Created by PhpStorm.
 * User: Ahaad
 * Date: 10/3/2017
 * Time: 10:30 AM
 */
require_once ('lib/misc/tableSys.php');
require_once ('lib/dbProcedures/budget.php');
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/html/FILL_HTML_GEN2.php';

/*{{{ Vdb-1 : Choix du type budget */
if ($global_nom_ecran == "Dcc-1") {
  if(isset($SESSION_VARS['id_block'])){
    unset($SESSION_VARS['id_block']);
  }
  if(isset($SESSION_VARS['ligne_budgetaire'])){
    unset($SESSION_VARS['ligne_budgetaire']);
  }

  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Type de budget"));

  $adsys_type_budget = $adsys["adsys_type_budget"];

  $myForm->addField("type_budget", _("Type de budget"), TYPC_LSB);
  $myForm->setFieldProperties('type_budget', FIELDP_ADD_CHOICES, $adsys_type_budget);
  $myForm->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_TOUS, false);
  $myForm->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("type_budget", FIELDP_IS_REQUIRED, true);


  $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Dcc-2');
  $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
  $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $myForm->buildHTML();
  echo $myForm->getHTML();

}
/*{{{ Vdb-2 : Visualisation Données */
else if ($global_nom_ecran == "Dcc-2") {
  $myForm = new HTML_GEN2();
  if (isset($SESSION_VARS['type_budget']) && $SESSION_VARS['type_budget'] != null){
    $SESSION_VARS['type_budget'];
  }else{
    $SESSION_VARS['type_budget']= $type_budget;
  }
  $myForm->setTitle(_("Table de deblocage : ".adb_gettext($adsys['adsys_type_budget'][$SESSION_VARS['type_budget']])));


  $myTable =& $myForm->addHTMLTable("tableau_deblock", 4, TABLE_STYLE_ALTERN);
  $myTable->add_cell(new TABLE_cell(_("LIGNE BUDGETAIRE"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("DESCRIPTION"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("COMPTES COMPTABLES"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("ACTIONS"), 1, 1));

  $compteBlock = getCompteBlock($type_budget);

  while (list(,$cpte) = each($compteBlock)) {
    $myTable->add_cell(new TABLE_cell($cpte["ligne_budgetaire"], 1, 1));
    $myTable->add_cell(new TABLE_cell($cpte["description"], 1, 1));
    $myTable->add_cell(new TABLE_cell($cpte["cpte_comptable"], 1, 1));
    $myTable->add_cell(new TABLE_cell_link("Debloquer", "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Dcc-3&id_block=" . $cpte['id_bloc']), 1, 1);

  }

  $myForm->addFormButton(1, 1, "retour", _("Retour Menu"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Gen-15');


  $myForm->buildHTML();
  echo $myForm->getHTML();
}

/*{{{ Dcc-3 : Deblocage compte*/
else if ($global_nom_ecran == "Dcc-3") {

  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Deblocage ligne budgetaire"));

  if (isset($_GET['id_block'])){
    $SESSION_VARS["id_block"] = $_GET["id_block"];
  }

  $data_debloc =getLigneBudgetaireBlock($_GET['id_block']);

  $myForm->addField("ligne_budgetaire", _("Ligne budgetaire"), TYPC_TXT);
  $myForm->setFieldProperties("ligne_budgetaire",FIELDP_DEFAULT , $data_debloc["ligne_budgetaire"]);
  $myForm->setFieldProperties("ligne_budgetaire", FIELDP_IS_LABEL, true);

  $myForm->addField("description", _("Description"), TYPC_TXT);
  $myForm->setFieldProperties("description",FIELDP_DEFAULT , $data_debloc["description"]);
  $myForm->setFieldProperties("description", FIELDP_IS_LABEL, true);

  $myForm->addField("cpte_comptable", _("Comptes comptables"), TYPC_TXT);
  $myForm->setFieldProperties("cpte_comptable",FIELDP_DEFAULT , $data_debloc["cpte_comptable"]);
  $myForm->setFieldProperties("cpte_comptable", FIELDP_IS_LABEL, true);

  $myForm->addField("mnt_trim1", _("Montant Trimestre 1"), TYPC_MNT);
  $myForm->setFieldProperties("mnt_trim1",FIELDP_DEFAULT , $data_debloc["mnt_trim1"]);
  $myForm->setFieldProperties("mnt_trim1", FIELDP_IS_LABEL, true);

  $myForm->addField("mnt_trim2", _("Montant Trimestre 2"), TYPC_MNT);
  $myForm->setFieldProperties("mnt_trim2",FIELDP_DEFAULT , $data_debloc["mnt_trim2"]);
  $myForm->setFieldProperties("mnt_trim2", FIELDP_IS_LABEL, true);

  $myForm->addField("mnt_trim3", _("Montant Trimestre 3"), TYPC_MNT);
  $myForm->setFieldProperties("mnt_trim3",FIELDP_DEFAULT , $data_debloc["mnt_trim3"]);
  $myForm->setFieldProperties("mnt_trim3", FIELDP_IS_LABEL, true);

  $myForm->addField("mnt_trim4", _("Montant Trimestre 4"), TYPC_MNT);
  $myForm->setFieldProperties("mnt_trim4",FIELDP_DEFAULT , $data_debloc["mnt_trim4"]);
  $myForm->setFieldProperties("mnt_trim4", FIELDP_IS_LABEL, true);

  $myForm->addField("budget_annuel", _("Budget Annuel"), TYPC_MNT);
  $myForm->setFieldProperties("budget_annuel",FIELDP_DEFAULT , $data_debloc["total_annuel"]);
  $myForm->setFieldProperties("budget_annuel", FIELDP_IS_LABEL, true);

  $SESSION_VARS['ligne_budgetaire'] = $data_debloc["ligne_budgetaire"];

  $myForm->addFormButton(1, 1, "deblock", _("Debloquer la ligne"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("deblock", BUTP_PROCHAIN_ECRAN, 'Dcc-4');
  $myForm->setFormButtonProperties("deblock", BUTP_JS_EVENT, array("onclick" =>
    "
        if (!confirm('" . _("ATTENTION") . "\\n " . _("Cette operation permet le deblocage de cette ligne budgetaire. \\nPar conséquent, tous les comptes comptables associés seront debloqués \\n Lors du prochain il n y aura pas de blocage si la revision n est pas faite.\\nEtes-vous sur de vouloir continuer ? ") . "')) return false;
        "));
  $myForm->addFormButton(1, 2, "retour", _("Retour Menu"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Gen-15');

  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*{{{ Dcc-3 : Validation deblocage*/
else if ($global_nom_ecran == "Dcc-4") {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $CpteComptableBlock = getCompteComptableBlock($SESSION_VARS["id_block"]);
  foreach ($CpteComptableBlock as $key => $value) {

    $data_update_cpte=array();
    $data_update_cpte['etat_cpte']=1;
    $data_update_cpte['is_actif']= 't';

    $where_update_cpte["num_cpte_comptable"] = $value['cpte_comptable'];
    $where_update_cpte["id_ag"] = $global_id_agence;
    $update_cpte_comptable = buildUpdateQuery('ad_cpt_comptable',$data_update_cpte,$where_update_cpte);
    $result_cpte_comptable = $db->query($update_cpte_comptable);
    if (DB::isError($result_cpte_comptable)) {
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
      $dbHandler->closeConnection(false);
    }
  }

  $data_disable_bloc_budget =array();
  $data_disable_bloc_budget['etat_bloque'] = 'f';

  $where_disable_bloc_budget['id_ligne'] = $SESSION_VARS['ligne_budgetaire'];
  $update_disable_bloc_budget = buildUpdateQuery('ad_ligne_budgetaire',$data_disable_bloc_budget,$where_disable_bloc_budget);
  $result_disable_bloc_budget = $db->query($update_disable_bloc_budget);
  if (DB::isError($result_disable_bloc_budget)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }


  $data_blocage_set_false =array();
  $data_blocage_set_false['cpte_bloquer'] = 'f';

  $where_blocage_set_false['id_bloc'] = $SESSION_VARS['id_block'];
  $update_blocage_set_false = buildUpdateQuery('ad_budget_cpt_bloquer',$data_blocage_set_false,$where_blocage_set_false);
  $result_blocage_set_false = $db->query($update_blocage_set_false);
  if (DB::isError($result_blocage_set_false)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  $dbHandler->closeConnection(true);
  $myMsg = new HTML_message(_("Confirmation de deblocage de la ligne budgetaire"));
  $msg = _("Votre deblocage a ete fait avec succès");
  $myMsg->setMessage($msg);

  $myMsg->addButton(BUTTON_OK, 'Dcc-1');
  $myMsg->buildHTML();
  echo $myMsg->HTML_code;
}
?>