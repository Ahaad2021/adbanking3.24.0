<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/* [44] Situation globale d'un client
 * Cette opération comprends les écrans :
 * - Rap-1 : Personnalisation situation globale d'un client
 * - Rap-2 et Rap-3 : Impression fiche client
 * @since 06/05/2007
 * @package Clients
 **/

require_once('lib/dbProcedures/client.php');
require_once('lib/misc/tableSys.php');
require_once 'lib/dbProcedures/rapports.php';
require_once 'modules/rapports/xml_clients.php';
require_once 'modules/rapports/xslt.php';
require_once 'lib/misc/csv.php';

global $global_id_client;

/*{{{ Rap-1 : Personnalisation rapport pour la situation globale */
if ($global_nom_ecran == "Rap-1") {
  $MyPage = new HTML_GEN2(_("Personnalisation du rapport"));

  //Choix impression part sociale
  $MyPage->addField("ps", _("Imprimer situation parts sociales ?"), TYPC_BOL);
  $MyPage->setFieldProperties("ps", FIELDP_DEFAULT, true);
  //Choix impression épargne
  $MyPage->addField("epargne", _("Imprimer situation comptes d'épargne ?"), TYPC_BOL);
  $MyPage->setFieldProperties("epargne", FIELDP_DEFAULT, true);

  //Choix impression ordre permanent
  $MyPage->addField("ord", _("Imprimer situation ordre permanent ?"), TYPC_BOL);
  $MyPage->setFieldProperties("ord", FIELDP_DEFAULT, true);

  //Choix impression garanties
  $MyPage->addField("gar", _("Imprimer situation garanties ?"), TYPC_BOL);
  $MyPage->setFieldProperties("gar", FIELDP_DEFAULT, true);

  //Choix impression crédit
  $MyPage->addField("credit", _("Imprimer situation crédit en cours ?"), TYPC_BOL);
  $MyPage->setFieldProperties("credit", FIELDP_DEFAULT, true);

  //Choix impression infos des membres si groupe solidaire
  $MyPage->addField("membre_gs", _("Imprimer situation des membres si groupe solidaire ?"), TYPC_BOL);
  $MyPage->setFieldProperties("membre_gs", FIELDP_DEFAULT, true);

  //Boutons
  $MyPage->addFormButton(1,1,"valider", _("Rapport PDF"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rap-2");
  $MyPage->addFormButton(1,2,"csv", _("Export CSV"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Rap-3");
  $MyPage->addFormButton(1,3,"annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-4");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
} /*}}}*/

/*{{{Rap-2 : Impression fiche client */
else if ($global_nom_ecran == "Rap-2" || $global_nom_ecran == "Rap-3") {
  //Récupère le numéro & le nom du client concerné
  $details = getClientDatas($global_id_client);
  $id_client = $details['id_client'];
  $statut_juridique = $details['statut_juridique'];
  $clientName = getClientName($id_client);
  $nbre_ps = getNbrePartSoc($id_client);

  $DATA["num_client"]=$id_client;
  $DATA["nom_client"]=$clientName;
  $DATA['num_stat_jur'] = $details['statut_juridique'];
  $DATA['statut_juridique'] = adb_gettext($adsys["adsys_stat_jur"][$details['statut_juridique']]);
  if ($details['statut_juridique'] == 1) {
    $DATA['pp_date_naissance'] = pg2phpDate($details['pp_date_naissance']);
    $DATA['pp_lieu_naissance'] = $details['pp_lieu_naissance'];
  }
  $DATA['qualite'] = adb_gettext($adsys['adsys_qualite_client'][$details['qualite']]);
  $DATA["etat_client"] = adb_gettext($adsys["adsys_etat_client"][$details['etat']]);
  $DATA["date_adhesion"] = pg2phpDate($details['date_adh']);
  $DATA["nbre_ps"] = $nbre_ps->param[0]['nbre_parts']; debug($DATA["nbre_ps"],'nbre ps');
  if ($details["gestionnaire"] != "")
    $DATA["gestionnaire"] = $details['gestionnaire']." (".getNomUtilisateur($details['gestionnaire']).")";

  if ($ps) { //Afficher situationn des parts sociales
    array($DATA['PS']);
    $DATA['PS'] = getSituationPartSocialeClient($id_client);
  }
  if ($epargne) { //Afficher situationn des épargnes
    array($DATA['EPARGNE']);
    $DATA['EPARGNE'] = getSituationEpargneClient($id_client);
  }
  if ($ord) {  //Afficher situation des ordres permanents
    array($DATA['ORD']);
    $DATA['ORD'] = getOrdresPermParClientInfo($id_client);
  }
  if ($gar) { //Afficher situationn des garanties
    array($DATA['GAR']);
    $DATA['GAR'] = getDossierCreditsGarantis($id_client);
  }
  if ($credit) { //Afficher situation des crédits
    array($DATA['CREDIT']);
    $DATA['CREDIT'] = getSituationCredits($id_client);
  }

  // Si Groupe solidaire, Imprimer la situation des membres si demandée
  if ($statut_juridique == 4 and $membre_gs) {
    // Récupération des membres du groupe
    $result = getListeMembresGrpSol($id_client);
    if (is_array($result->param))
      foreach($result->param as $key=>$id_cli) {
      if ($epargne) {
        $data_ep = getSituationEpargneClient($id_cli);
        if (is_array($data_ep))
          $DATA['EPARGNE'] = array_merge($DATA['EPARGNE'], $data_ep);
      }
      if ($ord) {
        $data_ord = getOrdresPermParClientInfo($id_cli);
        if (is_array($data_ord))
          $DATA['ORD'] = array_merge($DATA['ORD'], $data_ord);
      }

      if ($gar) {
        $data_gar = getDossierCreditsGarantis($id_cli);
        if (is_array($data_gar))
          $DATA['GAR'] = array_merge($DATA['GAR'], $data_gar);
      }

      if ($credit) {
        $data_cre = getSituationCredits($id_cli);
        if (is_array($data_cre))
          $DATA['CREDIT'] = array_merge($DATA['CREDIT'], $data_cre);
      }
    }
  }

  //Génération du xml puis de l'export CSV
  $xml = xml_sit_globale_clients($DATA);

  if ($global_nom_ecran == "Rap-2") {
    //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'situation_client.xslt');

    //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
    echo get_show_pdf_html("Gen-4", $fichier_pdf);
  }
  elseif ($global_nom_ecran == "Rap-3") {
    //Génération du fichier CSV

    $csv_file = xml_2_csv($xml, 'situation_client.xslt');
    //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
    echo getShowCSVHTML("Gen-4", $csv_file);
  }

  ajout_historique(350,$id_client, NULL, $global_nom_login, date("r"), NULL);

}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>