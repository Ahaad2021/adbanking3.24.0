<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Dépôt sur un compte d'épargne
 * @author Hassan Diallo
 * @author Olivier Luickx
 * @since 04/02/2002
 * @package Epargne
 **/

require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/credit_lcr.php';
require_once 'modules/epargne/recu.php';
require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/dbProcedures/tireur_benef.php';
require_once 'lib/misc/divers.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/misc/VariablesSession.php';
require_once 'modules/rapports/xml_devise.php';
require_once 'lib/dbProcedures/billetage.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/agency_banking.php';

if ($global_nom_ecran == "Vta-1") {
  global $global_nom_login;
  $MyPage = new HTML_GEN2(_("Critères de recherche"));

  $login_agent = isLoginAgent();
  $type_trans = array(1 => 'Dépôt via agent' , 2=> 'Retrait via agent');
  $MyPage->addField("type_trans", _("Type transaction"), TYPC_LSB);
  $MyPage->setFieldProperties("type_trans", FIELDP_HAS_CHOICE_AUCUN, false);
  $MyPage->setFieldProperties("type_trans", FIELDP_HAS_CHOICE_TOUS, true);
  $MyPage->setFieldProperties("type_trans", FIELDP_ADD_CHOICES, $type_trans);

  if (!isProfilAgent($global_nom_login)){
    $MyPage->addField("login", _("Login"), TYPC_LSB);
    $MyPage->setFieldProperties("login", FIELDP_HAS_CHOICE_AUCUN, false);
    $MyPage->setFieldProperties("login", FIELDP_HAS_CHOICE_TOUS, true);
    $MyPage->setFieldProperties("login", FIELDP_ADD_CHOICES, $login_agent);
  }

  //Champs client
  $MyPage->addField("num_client", _("Numéro client"), TYPC_INT);
  $MyPage->addLink("num_client", "rechercher", _("Rechercher"), "#");
  $MyPage->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('../modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client', '"._("Recherche")."');return false;"));

  //Champs date début
  $MyPage->addField("date_min", _("Date min"), TYPC_DTE);
  $MyPage->setFieldProperties("date_min", FIELDP_DEFAULT, date("01/01/Y"));

  //Champs date fin
  $MyPage->addField("date_max", _("Date max"), TYPC_DTE);
  $MyPage->setFieldProperties("date_max", FIELDP_DEFAULT, date("d/m/Y"));

  //Boutons
  $MyPage->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $MyPage->addFormButton(1,2,"annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Vta-2");
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-16");


  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
else if ($global_nom_ecran == "Vta-2"){
  global $global_nom_login;

  if (isset($type_trans)){
    if ($type_trans == 1){
      $fonct_trans = '763,762,32';
    }
    else {
      $fonct_trans = '764';
    }
  }
  else if (empty($type_trans)){
    $fonct_trans = NULL;
  }
  if ($num_client =="") $num_client = NULL;
  if ($date_min == "") $date_min = NULL;
  if ($date_max == "") $date_max = NULL;
  $SESSION_VARS['criteres'] = array();
  if (isProfilAgent($global_nom_login)){
    $login_agent = $global_nom_login;
  }else {
    if (isset($login)) {
      $login_agent = $login;
    } else if (empty($login)) {
      $login_agent = null;
    }
  }
  $SESSION_VARS['criteres']['type_trans'] = (empty($type_trans)?'Tous':(($type_trans==1)?'Dépôt via agent':'Retrait via agent'));
  $SESSION_VARS['criteres']['function'] = (empty($type_trans)?null:(($type_trans==1)?$fonct_trans:764));
  $SESSION_VARS['criteres']['login'] = (empty($login_agent)?'Tous':$login_agent);
  $SESSION_VARS['criteres']['login_rech'] = (empty($login_agent)?null:$login_agent);
  $SESSION_VARS['criteres']['num_client'] = $num_client;
  $SESSION_VARS['criteres']['date_min'] = $date_min;
  $SESSION_VARS['criteres']['date_max'] = $date_max;

  $nombre = count_recherche_transactions_depot_retrait_agent($login_agent,$fonct_trans,$num_client, $date_min, $date_max);
  $agent_dataset = recherche_transactions_agent($login_agent,$fonct_trans,$num_client, $date_min, $date_max);

  if ($nombre > 100) {
    $MyPage = new HTML_erreur(_("Trop de correspondances"));
    $MyPage->setMessage(sprintf(_("La recherche a renvoyé %s résultats; veuillez affiner vos critères de recherche ou imprimer."),$nombre));
    $nextScreen = "Gen-16";
    $printScreen = "Vta-3";
//            $SESSION_VARS['login'] = $login_agent;
//            $SESSION_VARS['date_min'] = $date_min;
//            $SESSION_VARS['date_max'] = $date_max;
    $MyPage->addButton(BUTTON_OK, $nextScreen);
    $MyPage->addCustomButton("print", _("Imprimer"), $printScreen, TYPB_SUBMIT);
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;

  } else {
    $html = "<h1 align=\"center\">"._("Résultat recherche")."</h1><br><br>\n";
    $html .= "<FORM name=\"ADForm\" action=\"$PHP_SELF\" method=\"post\" onsubmit=\"return ADFormValid;\">\n";
    $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

    //Ligne titre
    $html .= "<TR bgcolor=$colb_tableau>";

    $html .= "<TD><b>n°</b></TD><TD align=\"center\"><b>"._("Date")."</b></TD><TD align=\"center\"><b>"._("Heure")."</b></TD><TD align=\"center\"><b>"._("Fonction")."</b></TD><TD align=\"center\"><b>"._("Opération")."</b></TD><TD align=\"center\"><b>"._("Login")."</b></TD><TD align=\"center\"><b>"._("N° client")."</b></TD><TD align=\"center\"><b>"._("Login initiateur")."</b></TD></TR>\n";

    $SESSION_VARS['id_his'] = array();
    reset($agent_dataset);
    while (list(,$value) = each($agent_dataset)) { //Pour chaque résultat
      //On alterne la couleur de fond
      if ($a) $color = $colb_tableau;
      else $color = $colb_tableau_altern;
      $a = !$a;
      $html .= "<TR bgcolor=$color>\n";

      //n°
      // FIXME/TF Aaaargh quelle horreur !
      if (($value['trans_fin']) || ($adsys["adsys_fonction_systeme"][$value['type_fonction']]==_('Ajustement du solde d\'un compte')) || ($value["id_his_ext"] != ''))
        $html .= "<TD><A href=# onclick=\"OpenBrwXY('$http_prefix/lib/html/detail_transaction.php?m_agc=".$_REQUEST['m_agc']."&id_transaction=".$value['id_his']."&operation=".$value['type_operation']."','', 800, 600);\">".$value['id_his']."</A></TD>";
      else $html .= "<TD>".$value['id_his']."</TD>";

      //Date
      $html .= "<TD>".pg2phpDate($value['date'])."</TD>";

      //Heure
      $html .= "<TD>".pg2phpHeure($value['date'])."</TD>";

      //Fonction
      $html .= "<TD>".adb_gettext($adsys["adsys_fonction_systeme"][$value['type_fonction']]);
      $html .= "</TD>\n";

      //Operation
      if ($value['type_operation'] != null){
        $libelOperation= getLibelOperationTransaction($value['type_operation']);
        $html .= "<TD align=\"center\">".$libelOperation."</TD>\n";
      }else{
        $html .= "<TD align=\"center\"></TD>\n";
      }


      //Login
      $html .= "<TD>".$value['login']."</TD>\n";

      //N° client
      if($value['type_fonction']==92 || $value['type_fonction']==93)
      {
        if (trim($value['infos'])!='') {
          $html .= "<TD align=\"center\">".trim($value['infos'])."</TD>\n";
        } else {
          $html .= "<TD></TD>\n";
        }
      }
      else
      {
        if ($value['id_client'] > 0) {
          $html .= "<TD align=\"center\">".sprintf("%06d", $value['id_client'])."</TD>\n";
        } else {
          $html .= "<TD></TD>\n";
        }
      }
      if ($value['info_ecriture'] != null){
        $html .= "<TD align=\"center\">".$value['info_ecriture']."</TD>\n";
      }else{
        $html .= "<TD align=\"center\"></TD>\n";
      }

      $html .= "</TR>\n";

      array_push($SESSION_VARS['id_his'], $value['id_his']);
    }

    $html .= "<TR bgcolor=$colb_tableau><TD colspan=8 align=\"center\">\n";

    //Boutons
    $html .= "<TABLE align=\"center\"><TR>";

    if ($global_nom_ecran == "Vta-2") {
      $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Précédent")."\" onclick=\"ADFormValid = true; assign('Vta-1');\"></TD>";
      $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Imprimer détails")."\" onclick=\"ADFormValid=true; assign('Vta-3');\"></TD>";
    }
    $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Retour menu")."\" onclick=\"ADFormValid=true; assign('Gen-16');\"></TD>";

    $html .= "</TR></TABLE>\n";

    $html .= "</TD></TR></TABLE>\n";
    $html .= "<INPUT TYPE=\"hidden\" NAME=\"prochain_ecran\"><INPUT type=\"hidden\" id=\"m_agc\" name=\"m_agc\"></FORM>\n";

    echo $html;
  }
}
else if ($global_nom_ecran == "Vta-3") {
  $login = $SESSION_VARS['criteres']['login'];
  $login_rech = $SESSION_VARS['criteres']['login_rech'];
  $type_trans = $SESSION_VARS['criteres']['type_trans'];
  $function = $SESSION_VARS['criteres']['function'];
  $num_client = $SESSION_VARS['criteres']['num_client'];
  $date_min = $SESSION_VARS['criteres']['date_min'];
  $date_max = $SESSION_VARS['criteres']['date_max'];
  $criteres = array (
    _("Login") => $login,
    _("Fonction") => $type_trans,
    _("Numéro client") => $num_client,
    _("Date min") => date($date_min),
    _("Date max") => date($date_max)
  );
  // Infos sur les transactions
  $DATAS = recherche_transactions_details_trans_agent($login_rech, $function, $num_client, $date_min, $date_max);
  $xml = xml_detail_transactions_visualisation_agent($DATAS, $criteres); //Génération du code XML
  $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'details_transactions_agent.xslt'); //Génération du XSL-FO et du PDF

  //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
  echo get_show_pdf_html("Gen-16", $fichier_pdf);

}