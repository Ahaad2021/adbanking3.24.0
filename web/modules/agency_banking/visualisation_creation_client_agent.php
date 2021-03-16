<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Dépôt sur un compte d'épargne
 * @author Ahaad M
 /* @since 17/02/2020
 * @package Agency
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

if ($global_nom_ecran == "Cva-1") {
  global $global_nom_login;
  $MyPage = new HTML_GEN2(_("Critères de recherche"));

  $util_agent = getAllUtiAgent();
  if (!isProfilAgent($global_nom_login)){
    $MyPage->addField("util", _("Login"), TYPC_LSB);
    $MyPage->setFieldProperties("util", FIELDP_HAS_CHOICE_AUCUN, false);
    $MyPage->setFieldProperties("util", FIELDP_HAS_CHOICE_TOUS, true);
    $MyPage->setFieldProperties("util", FIELDP_ADD_CHOICES, $util_agent);
  }

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
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Cva-2");
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-16");


  $MyPage->buildHTML();
  echo $MyPage->getHTML();

}
else if ($global_nom_ecran == "Cva-2") {
  global $global_nom_login,$global_nom_utilisateur,$global_id_utilisateur;

  if ($date_min == "") $date_min = NULL;
  if ($date_max == "") $date_max = NULL;
  $SESSION_VARS['criteres'] = array();
  if (isProfilAgent($global_nom_login)){
    $nom_util = $global_nom_utilisateur;
    $id_util = $global_id_utilisateur;
      $SESSION_VARS['login'] =$id_util ;
  }else {
    if (isset($util)) {
      $id_util = $util;
      $SESSION_VARS['login'] =$util ;
      $data_util = getDataUtilisateur($id_util);
        $nom_util = $data_util['nom']." ".$data_util['prenom'];
    } else if (empty($util)) {
      $id_util = null;
      $SESSION_VARS['login'] = null;
    }
  }

  $SESSION_VARS['criteres']['login'] = (empty($id_util)?'Tous':$id_util);
  $SESSION_VARS['criteres']['login_rech'] = (empty($nom_util)?'Tous':$nom_util);
  $SESSION_VARS['criteres']['num_client'] = $num_client;
  $SESSION_VARS['criteres']['date_min'] = $date_min;
  $SESSION_VARS['criteres']['date_max'] = $date_max;

//  $nombre = count_recherche_transactions_depot_retrait_agent($login_agent,$fonct_trans,$num_client, $date_min, $date_max);
  $agent_dataset = recherche_creation_client_agent($id_util, $date_min, $date_max);

  if (sizeof($agent_dataset) > 100) {
    $MyPage = new HTML_erreur(_("Trop de correspondances"));
    $MyPage->setMessage(sprintf(_("La recherche a renvoyé %s résultats; veuillez affiner vos critères de recherche ou imprimer."),$nombre));
    $nextScreen = "Gen-16";
    $printScreen = "Vta-2";
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

    $html .= "<TD><b>"._("Id Transaction")."</b></TD><TD><b>"._("N° client")."</b></TD><TD align=\"center\"><b>"._("Nom client")."</b></TD><TD align=\"center\"><b>"._("Statut juridique")."</b></TD><TD align=\"center\"><b>"._("Etat")."</b></TD><TD align=\"center\"><b>"._("Login createur")."</b></TD><TD align=\"center\"><b>"._("Date creation")."</b></TD></TR>\n";

    reset($agent_dataset);
    while (list(,$value) = each($agent_dataset)) { //Pour chaque résultat
      //On alterne la couleur de fond
      if ($a) $color = $colb_tableau;
      else $color = $colb_tableau_altern;
      $a = !$a;
      $html .= "<TR bgcolor=$color>\n";

      //ID transaction
      $id_trans = getIdHisCreationClient($value['id_client']);
      $html .= "<TD>".$id_trans;
      $html .= "</TD>\n";

      //No client
      $html .= "<TD>".$value['id_client'];
      $html .= "</TD>\n";

      switch ($value['statut_juridique']) {
        case 1 : //PP
          $nom = $value['pp_prenom'] . " " . $value['pp_nom'];
          break;
        case 2 : //PM
          $nom = $value['pm_raison_sociale'];
          break;
        case 3 : //GI
          $nom = $value['gi_nom'];
        case 4 : //GS
          $nom = $value['gi_nom'];
          break;
        default : //Autre
          break;
      }

      //Nom client
      $html .= "<TD>".$nom;
      $html .= "</TD>\n";

      //statut juridique
      $html .= "<TD>".adb_gettext($adsys["adsys_stat_jur"][$value['statut_juridique']]);
      $html .= "</TD>\n";


      //etat client
      $html .= "<TD>".adb_gettext($adsys["adsys_etat_client"][$value['etat']]);
      $html .= "</TD>\n";

      $nom_uti = get_utilisateur_nom($value['utilis_crea']);
      //Utilisateur createur
      $html .= "<TD>".$nom_uti;
      $html .= "</TD>\n";

      //Date creation
      $html .= "<TD>".pg2phpDate($value['date_crea'])."</TD>";
    }

    $html .= "<TR bgcolor=$colb_tableau><TD colspan=7 align=\"center\">\n";

    //Boutons
    $html .= "<TABLE align=\"center\"><TR>";

    if ($global_nom_ecran == "Cva-2") {
      $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Précédent")."\" onclick=\"ADFormValid = true; assign('Cva-1');\"></TD>";
      $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Imprimer détails")."\" onclick=\"ADFormValid=true; assign('Cva-3');\"></TD>";
    }
    $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Retour menu")."\" onclick=\"ADFormValid=true; assign('Gen-16');\"></TD>";

    $html .= "</TR></TABLE>\n";

    $html .= "</TD></TR></TABLE>\n";
    $html .= "<INPUT TYPE=\"hidden\" NAME=\"prochain_ecran\"><INPUT type=\"hidden\" id=\"m_agc\" name=\"m_agc\"></FORM>\n";

    echo $html;
  }
}
else if ($global_nom_ecran == "Cva-3") {
  global $global_nom_login;

  $dataset = array();
  $critere = array(
      'Login agent' => $SESSION_VARS['criteres']['login_rech'],
      'Date minimum' => empty($SESSION_VARS['criteres']['date_min'])?' - ':$SESSION_VARS['criteres']['date_min'],
      'Date maximum' => empty($SESSION_VARS['criteres']['date_max'])?' - ':$SESSION_VARS['criteres']['date_max']
  );


  $agent_dataset = recherche_creation_client_agent($SESSION_VARS['login'], $SESSION_VARS['criteres']['date_min'], $SESSION_VARS['criteres']['date_max']);

  foreach ($agent_dataset as $value){
      $id_trans = getIdHisCreationClient($value['id_client']);
      $dataset[$id_trans]['id_client'] =  $value['id_client'];
      switch ($value['statut_juridique']) {
          case 1 : //PP
              $nom = $value['pp_prenom'] . " " . $value['pp_nom'];
              break;
          case 2 : //PM
              $nom = $value['pm_raison_sociale'];
              break;
          case 3 : //GI
              $nom = $value['gi_nom'];
          case 4 : //GS
              $nom = $value['gi_nom'];
              break;
          default : //Autre
              $nom = '';
      }
      $dataset[$id_trans]['nom_client'] = $nom;
      $dataset[$id_trans]['statut_juridique'] = adb_gettext($adsys["adsys_stat_jur"][$value['statut_juridique']]);
      $dataset[$id_trans]['etat'] = adb_gettext($adsys["adsys_etat_client"][$value['etat']]);
      $dataset[$id_trans]['nom_agent'] = get_utilisateur_nom($value['utilis_crea']);
      $dataset[$id_trans]['date_creation'] = pg2phpDate($value['date_crea']);
  }

  $xml_creation_cli = xml_creation_cli_agent($dataset, $critere);
  $fichier_pdf = xml_2_xslfo_2_pdf($xml_creation_cli, 'visualisation_creation_client_agent.xslt');
  echo get_show_pdf_html("Gen-16", $fichier_pdf);
}
