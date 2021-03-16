<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [440] Gestion des exercices
 * Cette fonction appelle les écrans suivants :
 * - Gex-1 : Menu principal de gestion des exercices
 * - Gex-2 : Recherche d'un exercice cloturable
 * - Gex-3 : Passage exercice 'En cours de clôture' ou 'Clôturé'
 * - Gex-4 : Menu principal clôture périodique
 * - Gex-5 : Menu confirmation clôture périodique
 * - Gex-6 : Infos sur la clôture
 *
 * @package Compta
 **/

require_once "lib/html/HTML_menu_gen.php";
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/misc/divers.php';
require_once 'modules/compta/xml_compta.php';
require_once 'modules/rapports/xslt.php';

/*{{{ Gex-1 : Menu principal gestion des exercices  */
if ($global_nom_ecran == "Gex-1") {
  global $global_id_agence;
  $MyPage = new HTML_GEN2(_("Gestion des exercices"));

  //Affichage de tous les exercices comptables
  $xtHTML1 .= "<br><TABLE align=\"center\" cellpadding=\"5\" width=\"95%\">";
  $xtHTML1 .= "\n<tr align=\"center\" bgcolor=\"$colb_tableau\">";
  $xtHTML1 .= "<td><b>"._("N°")."</b></td>";
  $xtHTML1 .= "<td><b>"._("DATE DEBUT")."</b></td>";
  $xtHTML1 .= "<td><b>"._("DATE FIN")."</b></td>";
  $xtHTML1 .= "<td width=\"20%\"><b>"._("ETAT")."</b></td>";
  $xtHTML1 .= "</tr>";
  $color = $colb_tableau;

  // liste des exercices
  $exos = getExercicesComptables();
  while (list(,$exo) = each($exos)) {
    $date_deb=pg2phpDate($exo["date_deb_exo"]);
    $date_fin=pg2phpDate($exo["date_fin_exo"]);
    $color = ($color == $colb_tableau_altern ? $colb_tableau : $colb_tableau_altern);
    $xtHTML1 .= "\n<tr align=\"center\"  bgcolor=\"$color\">";
    $xtHTML1 .= "<td>".$exo['id_exo_compta']."</td>";
    $xtHTML1 .= "<td>".$date_deb."</td>";
    $xtHTML1 .= "<td>".$date_fin."</td>";
    $xtHTML1 .= "<td>".adb_gettext($adsys["adsys_etat_exo_compta"][$exo['etat_exo']])."</td>";
    $xtHTML1 .= "</tr>";
  }

  $xtHTML1 .= "</TABLE>";
  $MyPage->addHTMLExtraCode("xtHTML1", $xtHTML1);

  //Bouton Cloture périodiquement
  $MyPage->addFormButton(1, 1, "cloturePeriod", _("Clôture périodique"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("cloturePeriod", BUTP_AXS, 444);
  $MyPage->setFormButtonProperties("cloturePeriod", BUTP_PROCHAIN_ECRAN, "Gex-4");
  $MyPage->setFormButtonProperties("cloturePeriod", BUTP_CHECK_FORM, false);

  //Bouton Cloture exercice
  $cl=verifClotureExo($global_id_agence); // Vérifie s'il existe un exercice qui peut être clôturer
  if ($cl) {
    $MyPage->addFormButton(1, 2, "clotureExo", _("Clôture exercice "), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("clotureExo", BUTP_AXS, 442);
    $MyPage->setFormButtonProperties("clotureExo", BUTP_PROCHAIN_ECRAN, "Gex-2");
    $MyPage->setFormButtonProperties("clotureExo", BUTP_CHECK_FORM, false);
  }

  //Bouton retour
  $MyPage->addFormButton(1, 3, "ret", _("Retour menu "), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("ret", BUTP_PROCHAIN_ECRAN, "Gen-14");
  $MyPage->setFormButtonProperties("ret", BUTP_CHECK_FORM, false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();

}
/*}}}*/

/*{{{ Gex-2 : Recherche d'un exercice cloturable */
else if ($global_nom_ecran == "Gex-2") {
  $exo=verifClotureExo($global_id_agence);

  if ($exo==NULL) {
    $html_err = new HTML_erreur(_("Clôture exercice"));
    $html_err->setMessage(_("Il n'existe pas un exercice qui peut être clôturé à cette date"));
    $html_err->addButton("BUTTON_OK", "Gex-1");
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $infos=getExercicesComptables($exo);
    $SESSION_VARS["id_exo_compta"]=$infos[0]["id_exo_compta"];
    $SESSION_VARS["etat_exo"]=$infos[0]["etat_exo"];

    $myPage = new HTML_GEN2(_("Confirmation de la clôture"));

    // Code HTML pour un tableau présentant l'exercice à clôturer
    $xtHTML  = "<H3 align=\"center\">"._("Exercice à clôtuter")."</H3> <BR>";
    $xtHTML .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border ";
    $xtHTML .= "cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
    // Ligne titre
    $xtHTML .= "<TR bgcolor=$colb_tableau>";
    $xtHTML .= "<TD><b>"._("N° EXERCICE")."</b></TD>";
    $xtHTML .= "<TD align=\"center\"><b>"._("DATE DEBUT")."</b></TD>";
    $xtHTML .= "<TD align=\"center\"><b>"._("DATE FIN")."</b></TD>";
    $xtHTML .= "<TD align=\"center\"><b>"._("ETAT")."</b></TD>";
    $xtHTML .= "</TR>\n";
    // Exercice
    $color = $colb_tableau_altern;
    $xtHTML .= "<TR bgcolor=$color>\n";
    $xtHTML .= "<TD>".$infos[0]["id_exo_compta"]."</TD>";
    $xtHTML .= "<TD>".pg2phpDate($infos[0]["date_deb_exo"])."</TD>";
    $xtHTML .= "<TD>".pg2phpDate($infos[0]["date_fin_exo"])."</TD>";
    $xtHTML .= "<TD>".adb_gettext($adsys["adsys_etat_exo_compta"][$infos[0]["etat_exo"]])."</TD>";
    $xtHTML .= "</TR>";
    $xtHTML .= "</TABLE><br/><br/>";
    $myPage->addHTMLExtraCode("exercice", $xtHTML);

    // Code HTML pour message d'attention
    $xtHTML = "<table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\">
              <tr><td align=\"center\"><font color=\"".$colt_error."\">
              <b>"._("Attention !")."</b></font></td></tr>
              <tr><td align=\"center\"><font color=\"".$colt_error."\">
              "._("La clôture d'un exercice est <b>irréversible</b>, êtes vous sûr de vouloir clôturer cet exercice ?")."</font></td></tr>
              <tr><td>&nbsp;</td></tr></table>";
    $myPage->addHTMLExtraCode("attention", $xtHTML);
    $myPage->addFormButton(1, 1, "oui", _("Oui"), TYPB_SUBMIT);
    $myPage->setFormButtonProperties("oui", BUTP_PROCHAIN_ECRAN, "Gex-3");
    $myPage->addFormButton(1, 2, "non", _("Non"), TYPB_SUBMIT);
    $myPage->setFormButtonProperties("non", BUTP_PROCHAIN_ECRAN, "Gex-1");

    $myPage->buildHTML();
    echo $myPage->getHTML();
  }
}
/*}}}*/

/*{{{ Gex-3 : Passage exercice 'En cours de clôture' ou 'Clôturé' */
else if ($global_nom_ecran == "Gex-3") {
  /* Récupération de la balance Avant ou Après inventaire (état exo 1 => Avant inventaire, 2 => Après Inventaire) */
  $exo = array();
  $exo = getExercicesComptables($SESSION_VARS["id_exo_compta"]);
  $etat_exo = $exo[0]["etat_exo"];
  $date_deb_exo = $exo[0]["date_deb_exo"];
  $date_fin_exo = $exo[0]["date_fin_exo"];

  $DDE = pg2phpDateBis($date_deb_exo);
  $DDE = date("d/m/Y", mktime(0,0,0,$DDE[0], $DDE[1], $DDE[2]));
  $DFE = pg2phpDateBis($date_fin_exo);
  $an = $DFE[2];
  $DFE = date("d/m/Y", mktime(0,0,0,$DFE[0], $DFE[1], $DFE[2]));
  $liste_ag = getAllIdNomAgence();
  $DATA = getBalanceComptable($DDE,$DFE, NULL, $liste_ag);
  /* Clôture de l'exercie ou Passage en cours de clôture */
  $erreur = clotureExercice($SESSION_VARS["id_exo_compta"]);

  if ($erreur->errCode == NO_ERR) {
    if ($SESSION_VARS["etat_exo"] == 1) {
      /* Exo était ouvert. Son état passe en "En cours de clôture" et on génère la balance avant inventaire */
      $titre = " ".sprintf(_("avant inventaire du %s au %s"),$DDE,$DFE);
      $destination = "$lib_path/rapports/balanceAvInv$an.pdf";
    } else if ($SESSION_VARS["etat_exo"]==2) {
      /* Exo était 'En cours de clôture'. On vide les comptes de gestion, on fait une on passe à l'état "Clôturé" et on génère
               la balance après inventaire */
      $titre = " ".sprintf(_("après inventaire du %s au %s"),$DDE,$DFE);
      $destination = "$lib_path/backup/batch/rapports/balanceApInv$an.pdf";
    } else {
      $html_err = new HTML_erreur(_("Clôture exercice"));
      $html_err->setMessage(_("Echec : Cet état d'exercice n'est reconnu"));
      $html_err->addButton("BUTTON_OK", 'Gex-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      exit();
    }

    $xml = xml_balance_comptable($DATA, $titre);
    
    /* Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP) */
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'balance_comptable.xslt');

    /* Message de confirmation + affichage du rapport dans une nouvelle fenêtre */
    echo get_show_pdf_html("Gex-1", $fichier_pdf);

    /* Backup du rapport balance Avant ou après Inventaire */
    global $lib_path;

    $dir = opendir("$lib_path/backup/batch/rapports");
    if ($dir == true)
      get_pdf_html($fichier_pdf, $destination);

  } else if ($erreur->errCode == ERR_CPTE_RESULT_NON_DEF) {
    $html_err = new HTML_erreur(_("Clôture exercice"));
    $html_err->setMessage(_("Echec : le compte de résultat n'est pas paramétré"));
    $html_err->addButton("BUTTON_OK", 'Gex-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $html_err = new HTML_erreur(_("Clôture exercice"));
    $html_err->setMessage(_("Echec")." : ".$error[$erreur->errCode].$erreur->param);
    $html_err->addButton("BUTTON_OK", 'Gex-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
}
/*}}}*/

/*{{{ Gex-4 : Menu principal clôture périodique */
else if ($global_nom_ecran == "Gex-4") {
  $MyPage = new HTML_GEN2(_("Clôture périodique"));

  // Liste des clôtures périodiques
  $clotures = getCloturesPeriodiques();
  $clotures = $clotures["id_ag"];
  $xtHTML = "<br><TABLE align=\"center\" cellpadding=\"5\" width=\"95%\">";
  $xtHTML .= "\n<tr align=\"center\" bgcolor=\"$colb_tableau\"><td><b>"._("N°")."</b></td><td><b>"._("DATE")."</b></td><td><b>"._("EXERCICE")."</b></td><td><b></b></td></tr>";
  $color = $colb_tableau;
  foreach($clotures as $key=>$value) {
    $date_clot=pg2phpDate($value['date_clot_per']);
    // Génération du HTML en conséquence
    $color = ($color == $colb_tableau_altern ? $colb_tableau : $colb_tableau_altern);
    $xtHTML .= "\n<tr align=\"center\" bgcolor=\"$color\">";
    $xtHTML .= "<td width=\"10%\">".$value['id_clot_per']."</td>";
    $xtHTML .= "<td>".$date_clot;
    $xtHTML .= "<td>".$value['id_exo'];
    //$xtHTML .= "<td><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Odc-4&id_oper=".$value['type_operation']."\">Détail</A></td>";
    $xtHTML .= "<td><A href=# onclick=\"OpenBrwXY('$http_prefix/modules/compta/detail_cloture_per.php?m_agc=".$_REQUEST['m_agc']."&id_clot=".$value['id_clot_per']."','', 800, 600);\">"._("Détail")."</A>";
    $xtHTML .= "&nbsp&nbsp<A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gex-6&id_clot=".$value['id_clot_per']."\">"._("Imprimer")."</A></TD>";

    $xtHTML .= "</tr>";

  }

  $xtHTML .= "</TABLE>";
  $xtHTML .= "<br>";
  $MyPage->addHTMLExtraCode("xtHTML", $xtHTML);

  // La date de clôture
  $MyPage->addField("date_clot",_("Date clôture"), TYPC_DTE);
  $MyPage->setFieldProperties("date_clot", FIELDP_IS_REQUIRED, true);
  //$MyPage->setFieldProperties("date_ope", FIELDP_DEFAULT, date("d/m/Y"));

  //Bouton Valider
  $MyPage->addFormButton(1, 1, "cloturer", _("Clôturer"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("cloturer", BUTP_PROCHAIN_ECRAN, "Gex-5");
  $MyPage->setFormButtonProperties("cloturer", BUTP_CHECK_FORM, false);

  //Bouton annuler
  $MyPage->addFormButton(1, 2, "annuler", _("Annuler "), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gex-1");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();

}
/*}}}*/

/*{{{ Gex-5 : Menu confirmation clôture périodique */
else if ($global_nom_ecran == "Gex-5") {

  $myErr = cloturePeriodique($date_clot);

  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Clôture périodique"));
    $html_err->setMessage(_("Echec")." : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gex-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    //HTML
    $MyPage = new HTML_GEN2(_("Clôture périodique"));
    $MyPage = new HTML_message(_("Confirmation clôture périodique"));
    $MyPage->setMessage(_("La clôture périodique a été effectuée !"));
    $MyPage->addButton(BUTTON_OK, "Gex-1");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
}
/*}}}*/

/*{{{ Gex-6 : Infos sur la clôtutre */
else if ($global_nom_ecran == "Gex-6") {
  $temp = array();
  $temp["id_clot_per"] = $id_clot;
  $cloture = getCloturesPeriodiques($temp);
  $cloture = $cloture["id_ag"];

  $clot= array();
  $clot["id_clot_per"] = $cloture[$id_clot]["id_clot_per"] ;
  $clot["date_clot_per"] = $cloture[$id_clot]["date_clot_per"] ;
  $clot["id_exo"] = $cloture[$id_clot]["id_exo"] ;

  // Soldes des comptes comptables à cette clôture
  $param= array();
  $param["id_cloture"] = $id_clot;
  $DATA = getDetailClotPer($param);

  // Ajout du titre
  $titre = "";

  $xml = xml_cloture_periodique($clot, $DATA, $titre);

  //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
  $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'cloture_periodique.xslt');

  //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
  echo get_show_pdf_html("Gex-4", $fichier_pdf);
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>