<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [43] Impression des extraits de compte.
 * Ces fonctions appellent les écrans suivants :
 * - Ext-1 : Choix du compte
 * - Ext-2 : Impression
 *
 * @author Antoine Guyette
 * @since 2.6
 * @package Clients
 **/
require_once('lib/dbProcedures/extraits.php');
require_once('lib/dbProcedures/tireur_benef.php');
require_once('lib/misc/csv.php');
require_once('modules/rapports/csv_epargne.php');
require_once('modules/rapports/xml_epargne.php');
require_once('modules/rapports/xslt.php');

/*{{{ Ext-1 : Choix du compte */
if ($global_nom_ecran == 'Ext-1') {
  global $global_id_client;

  // JavaScript
  $JS  = "if (document.ADForm.HTML_GEN_BOL_dernier_extrait.checked == true)";
  $JS .= "{";
  $JS .= "  document.ADForm.HTML_GEN_date_date_debut.disabled = true;";
  $JS .= "  document.ADForm.HTML_GEN_date_date_fin.disabled = true;";
  $JS .= "  document.ADForm.num_debut.disabled = true;";
  $JS .= "  document.ADForm.num_fin.disabled = true;";
  $JS .= "}";
  $JS .= "else";
  $JS .= "{";
  $JS .= "  document.ADForm.HTML_GEN_date_date_debut.disabled = false;";
  $JS .= "  document.ADForm.HTML_GEN_date_date_fin.disabled = false;";
  $JS .= "  document.ADForm.num_debut.disabled = false;";
  $JS .= "  document.ADForm.num_fin.disabled = false;";
  $JS .= "}";

  $html = new HTML_GEN2(_("Sélection compte"));

  // FIXME : Quels sont les comptes à sortir ?
  $ListeComptesEpargne = getAccountStatement($global_id_client);
  $choix = array();
  if (isset($ListeComptesEpargne)) {
    foreach($ListeComptesEpargne as $key=>$value) {
      $choix[$key] = $value["num_complet_cpte"]." ".$value["intitule_compte"]; // Index par id_cpte pour la listbox
    }
  }

  $html->addField("num_cpte", _("Numéro de compte"), TYPC_LSB);
  $html->setFieldProperties("num_cpte", FIELDP_ADD_CHOICES, $choix);
  $html->setFieldProperties("num_cpte", FIELDP_IS_REQUIRED, true);

  $html->addField("dernier_extrait", _("Depuis la dernière impression ?"), TYPC_BOL);
  $html->setFieldProperties("dernier_extrait", FIELDP_JS_EVENT, array("onchange()"=>$JS));

  $html->addField("date_debut", _("Date de début :"), TYPC_DTE);
  $html->setFieldProperties("date_debut", FIELDP_IS_REQUIRED, false);
  $html->setFieldProperties("date_debut", FIELDP_DEFAULT, date("d/m/Y", mktime(0,0,0,1,1,date("Y"))));

  $html->addField("date_fin", _("Date de fin :"), TYPC_DTE);
  $html->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, false);
  $html->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));

  $html->addField("num_debut", _("Numéro de début :"), TYPC_TXT);
  $html->setFieldProperties("num_debut", FIELDP_IS_REQUIRED, false);

  $html->addField("num_fin", _("Numéro de fin :"), TYPC_TXT);
  $html->setFieldProperties("num_fin", FIELDP_IS_REQUIRED, false);

  // Boutons
  $html->addFormButton(1, 1, "valider", _("Rapport PDF"), TYPB_SUBMIT);
  $html->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ext-2");
  $html->addFormButton(1, 2, "csv", _("Export CSV"), TYPB_SUBMIT);
  $html->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Ext-3");
  $html->addFormButton(1, 3, "csv_netbank", _("Export CSV Netbank"), TYPB_SUBMIT);
  $html->setFormButtonProperties("csv_netbank", BUTP_PROCHAIN_ECRAN, "Ext-4");
  $html->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-4");
  $html->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $html->buildHTML();
  echo $html->getHTML();
}
/*}}}*/

/*{{{ Ext-2 ou Ext-3 : Impression */
else if ($global_nom_ecran == 'Ext-2' || $global_nom_ecran == 'Ext-3' || $global_nom_ecran == 'Ext-4') {
  global $global_id_client;

  $DATA["id_client"] = $global_id_client;
  $DATA["nom_client"] = getClientName($global_id_client);

  // Impression depuis le dernier extrait imprimé
  if (isset($dernier_extrait)) {
    $dernier_extrait = true;
    $DATA["eft_id_dern_extrait"] = getNumDernierExtrait($num_cpte);
  } else {
    $dernier_extrait = false;
  }

  // Infos sur les mouvements de ce compte à cette période (infos stockées dans ad_extrait_cpte)
  $InfoMvts = getExtraitCompte($num_cpte, $date_debut, $date_fin, $dernier_extrait, $num_debut, $num_fin);
  $DATA["InfoMvts"] = $InfoMvts;

  // Infos sur le compte du client
  $InfoCpte = getAccountDatas($num_cpte);

  $DATA["num_complet_cpte"] = $InfoCpte["num_complet_cpte"];
  $DATA["devise"] = $InfoCpte["devise"];
  $DATA["intitule_compte"] = $InfoCpte["intitule_compte"];
  $DATA["date_debut"] = $date_debut;
  $DATA["date_fin"] = $date_fin;
  $DATA["num_debut"] = $num_debut;
  $DATA["num_fin"] = $num_fin;

  // Pour que le rapport soit dans la même langue
  basculer_langue_rpt();

  $liste_criteres = array();

  if ($dernier_extrait == true) {
    if ($DATA["eft_id_dern_extrait"] != NULL) {
      $liste_criteres = array_merge($liste_criteres, array(_("Depuis le dernier extrait imprimé")=>_("N°").sprintf("%03d", $DATA["eft_id_dern_extrait"])));
    } else {
      $liste_criteres = array_merge($liste_criteres, array(_("Depuis le dernier extrait imprimé")=>_("Aucun")));
    }
  } else {
    if ($date_debut != '') {
      $liste_criteres = array_merge($liste_criteres, array(_("Date de début")=>localiser_date_rpt($DATA["date_debut"])));
    }
    if ($date_fin != '') {
      $liste_criteres = array_merge($liste_criteres, array(_("Date de fin")=>localiser_date_rpt($DATA["date_fin"])));
    }
    if ($num_debut != '') {
      $liste_criteres = array_merge($liste_criteres, array(_("Numéro de début")=>localiser_date_rpt($DATA["num_debut"])));
    }
    if ($num_fin != '') {
      $liste_criteres = array_merge($liste_criteres, array(_("Numéro de fin")=>localiser_date_rpt($DATA["num_fin"])));
    }
  }

  reset_langue();
  if ($global_nom_ecran == 'Ext-2') {
    // Génération du code XML
    $xml = xml_extrait_compte($DATA, $liste_criteres);

    // Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'extrait_compte.xslt');

    // Message de confirmation + affichage du rapport dans une nouvelle fenêtre
    echo get_show_pdf_html("Gen-4", $fichier_pdf);
  } else if ($global_nom_ecran == 'Ext-3') {
    //ajout csv
    $xml = xml_extrait_compte($DATA, $liste_criteres, true);

    $csv_file = xml_2_csv($xml, 'extrait_compte.xslt');

    //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
    echo getShowCSVHTML("Gen-4", $csv_file);
    //fin ajout csv
  }
  else if ($global_nom_ecran == 'Ext-4') 
  {
		//ajout csv pour netbank
		$xml = xml_extrait_cpte_netbank($InfoMvts, $liste_criteres, true);
		$csv_file = xml_2_csv($xml, 'extrait_cpte_netbank.xslt');
	
		//Message de confirmation + affichage du rapport dans une nouvelle fenêtre
		echo getShowCSVHTML("Gen-4", $csv_file);
		//fin ajout csv
  }
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>