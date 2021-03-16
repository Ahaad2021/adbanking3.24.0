<?php

/* vim: set expandtab softtabstop=2 shiftwidth=2: */
/**
 * Génère le code XML pour les échéanciers
 * @package Rapports
 */

require_once 'lib/misc/xml_lib.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/dbProcedures/rapports.php';
require_once 'lib/misc/divers.php';

/**
 * xml_echeancier_theorique Génère le code XML pour un rapport concernant une simulation d'échéancier
 *
 * @param mixed $DATA Les données de l'échéancier
 * Le tableau est indicé par le id des dossiers
 * Le tableau est de la forme : $id_doss=><UL>
 *   <LI> array : tableau des infos du dossier </LI>
 *   <LI> array DATA_GAR : tableau contenant les garanties mobilisées et les infos des</LI>
 *   <LI> array CRIT : tableau des critères de recherche</LI></UL>
 * @return void
 */
function xml_echeancier_theorique($infos_dossiers) {
  //XML
  $document = create_xml_doc("simulation_echeancier", "simulation_echeancier.dtd");

  //Element root
  $root = $document->root();

  foreach ($infos_dossiers as $id_doss => $DATA) {
    // Une page d'échéancier
    $infos_doss = $root->new_child("infos_doss", "");

    //En-tête généraliste
    if (!isset ($DATA['CRIT']['etat']) || $DATA['CRIT']['etat'] == 2) //gestion de l'entète dans le cas ou il s'agit d'un déboursement
      gen_header($infos_doss, 'SIM-ECH');
    else
      gen_header($infos_doss, 'ECH-REL');

    //En-tête contextuel
    $header_contextuel = $infos_doss->new_child("header_contextuel", "");
    gen_criteres_recherche($header_contextuel, $DATA['CRIT']);

    // Body
    $solde_cap = $DATA["total_cap"];
    $solde_int = $DATA["total_int"];
    $solde_gar = $DATA["total_gar"];
    while (list ($key, $echeance) = each($DATA['echeances'])) {
      $total_du = $echeance["mnt_cap"] + $echeance["mnt_int"] + $echeance["mnt_gar"];
      $solde_cap -= $echeance["mnt_cap"];
      $solde_int -= $echeance["mnt_int"];
      $solde_gar -= $echeance["mnt_gar"];
      $solde_total = $solde_cap + $solde_int + $solde_gar;
      $ech = $infos_doss->new_child("ech", "");
      $ech->new_child("num_ech", $key);
      $ech->new_child("date_ech", $echeance["date_ech"]);
      $ech->new_child("cap_du", afficheMontant($echeance["mnt_cap"], false));
      $ech->new_child("int_du", afficheMontant($echeance["mnt_int"], false));
      $ech->new_child("gar_du", afficheMontant($echeance["mnt_gar"], false));
      $ech->new_child("total_du", afficheMontant($total_du, false));
      $ech->new_child("solde_cap", afficheMontant(abs($solde_cap), false));
      $ech->new_child("solde_int", afficheMontant(abs($solde_int), false));
      $ech->new_child("solde_gar", afficheMontant(abs($solde_gar), false));
      $ech->new_child("solde_total", afficheMontant(abs($solde_total), false));
    }
    $total = $infos_doss->new_child("ech", "");
    $total->new_child("num_ech", "");
    $total->new_child("date_ech", "Total");
    $total->new_child("cap_du", afficheMontant($DATA["total_cap"], false));
    $total->new_child("int_du", afficheMontant($DATA["total_int"], false));
    $total->new_child("gar_du", afficheMontant($DATA["total_gar"], false));
    $total->new_child("total_du", afficheMontant($DATA["total_cap"] + $DATA["total_int"] + $DATA["total_gar"], false));
    $ech->new_child("solde_cap", "");
    $ech->new_child("solde_int", "");
    $ech->new_child("solde_total", "");
  } // fin parcours des dossiers

  return $document->dump_mem(true);
}
/**
 * @param mixed $DATA Les données de l'échéancier
 * @return void
 */
function xml_credit_imprim($infos_dossiers)
{
    //XML
    $document = create_xml_doc("impression_echeancier", "impression_echeancier.dtd");
    global $adsys;
    //Element root
    $root = $document->root();
    $infos_doss = $root->new_child("infos_doss", "");
    gen_header($infos_doss, 'ECH-CRE');


    $diff = $infos_dossiers["param"]["differe_jours"];
    $diff_ech = $infos_dossiers["param"]["differe_ech"];
    if(($infos_dossiers["produit"][0]['prelev_commission'] == 't'  && $infos_dossiers["produit"][0]['prelev_frais_doss']== 2 && $infos_dossiers["produit"][0]['debours'] == "true")||($infos_dossiers["produit"][0]['prelev_commission'] == 't' && $infos_dossiers["produit"][0]['prelev_frais_doss']== 1 && $infos_dossiers["produit"][0]['debours'] != "true")){
        $mnt_octroyer = afficheMontant ($infos_dossiers["param"]["mnt_octr"]-$infos_dossiers["param"]["mnt_des_frais"],true);
    }else{
        $mnt_octroyer = afficheMontant ($infos_dossiers["param"]["mnt_octr"],true);
    }

    $DATA['CRIT']['N° client'] = $infos_dossiers["param"]["id_client"];
    $DATA['CRIT']['Nom client'] = _(getClientName($infos_dossiers["param"]["id_client"]));
    $DATA['CRIT']['Produit'] = $infos_dossiers["produit"][0]["libel"];
    $DATA['CRIT']['Durée'] = $infos_dossiers["param"]["duree"]." ".$adsys["adsys_type_duree_credit"][$infos_dossiers["produit"][0]["type_duree_credit"]];
    $DATA['CRIT']['Différé'] =  str_affichage_diff($diff, $diff_ech);
    $DATA['CRIT']['Périodicité'] = adb_gettext($adsys["adsys_type_periodicite"][$infos_dossiers["produit"][0]["periodicite"]]);
    (($infos_dossiers["produit"][0]["freq_paiement_cap"] == 1)? $frequence_paiement_capital = _("Chaque échéance") :$frequence_paiement_capital = sprintf(_("Toutes les %d échéances"), $infos_dossiers["produit"][0]["freq_paiement_cap"]));
    $DATA['CRIT']['Fréq. remb. capital'] =  $frequence_paiement_capital;
    $DATA['CRIT']['Date de déboursement'] = $infos_dossiers["param"]["date"];
    $DATA['CRIT']['Montant octroyé'] =  $mnt_octroyer;
    $DATA['CRIT']['Montant rééchel'] = afficheMontant ($infos_dossiers["param"]["mnt_reech"],true);
    $DATA['CRIT']['Taux d\'intérêt'] = 100*$infos_dossiers["produit"][0]["tx_interet"]."%";
    $DATA['CRIT']['Garantie totale'] = afficheMontant ($infos_dossiers["param"]["garantie"],true);
    $DATA['CRIT']['Mode de calcul des intérêts'] = adb_gettext($adsys["adsys_mode_calc_int_credit"][$infos_dossiers["produit"][0]["mode_calc_int"]]);

    $header_contextuel = $infos_doss->new_child("header_contextuel", "");
    gen_criteres_recherche($header_contextuel, $DATA['CRIT']);

    $total_cap = 0;
    $total_int = 0;
    $total_gar = 0;

    foreach ($infos_dossiers["echeance"] AS $key=>$echanc) {
        $total_cap = $total_cap + $echanc["mnt_cap"];
        $total_int = $total_int + $echanc["mnt_int"];
        $total_gar = $total_gar + $echanc["mnt_gar"];

        $som=$echanc["mnt_cap"] + $echanc["mnt_int"] + $echanc["mnt_gar"];
        $rest=max(0,$infos_dossiers["param"]["montant"] - $total_cap);

        $ech = $infos_doss->new_child("ech", "");
        $ech->new_child("eid", $echanc["id_ech"]);
        $ech->new_child("date_s", pg2phpDate($echanc["date_ech"]));
        $ech->new_child("montant_capital", afficheMontant($echanc["mnt_cap"]));
        $ech->new_child("montant_interets", afficheMontant($echanc["mnt_int"]));
        $ech->new_child("montant_garantie", afficheMontant($echanc["mnt_gar"]));
        $ech->new_child("total_echeance",afficheMontant($som));
        $ech->new_child("solde_restant", afficheMontant($rest));
    }
    $tot=$total_cap+$total_int+$total_gar;
    $total = $infos_doss->new_child("ech", "");
    $total->new_child("eid", "");
    $total->new_child("date_s", "Total");
    $total->new_child("montant_capital", afficheMontant($total_cap));
    $total->new_child("montant_interets", afficheMontant($total_int));
    $total->new_child("montant_garantie", afficheMontant($total_gar));
    $total->new_child("total_echeance", afficheMontant($tot));
    $total->new_child("solde_restant", "");

    return $document->dump_mem(true);
}
/**
 * xml_echeancier_theorique_DAT Génère le code XML pour un rapport concernant une simulation d'échéancier de DAT
 *
 * @param mixed $DATA Les données de l'échéancier
 * Le tableau est indicé par le id des DATS
 * Le tableau est de la forme : $id=><UL>
 *   <LI> array : tableau des infos de DAT </LI>
 *   <LI> array DATAECH : tableau contenant les infos des echeances</LI>
 *   <LI> array CRIT : tableau des critères de recherche</LI></UL>
 * @return void
 */
function xml_echeancier_theorique_DAT($infos_epargne) {
  //XML
  $document = create_xml_doc("simulation_echeancier_dat", "simulation_echeancier_dat.dtd");

  //Element root
  $root = $document->root();

  foreach ($infos_epargne as $id_epargne => $DATA) {
    // Une page d'échéancier
    $infos_epargne = $root->new_child("infos_epargne", "");

    //En-tête généraliste
    gen_header($infos_epargne, 'SIM-DAT');

    //En-tête contextuel
    $header_contextuel = $infos_epargne->new_child("header_contextuel", "");
    gen_criteres_recherche($header_contextuel, $DATA['CRIT']);

    // Body
    while (list ($key, $echeance) = each($DATA['echeances'])) {
      $ech = $infos_epargne->new_child("ech", "");
      $ech->new_child("num_ech", $key);
      $ech->new_child("date_ech", $echeance["date_ech"]);
      $ech->new_child("solde_cap", afficheMontant($echeance["mnt_cap"], false));
      $ech->new_child("solde_int", afficheMontant($echeance["mnt_int"], false));
      $ech->new_child("solde_total", afficheMontant($echeance["mnt_ech"], false));
    }
    $total = $infos_epargne->new_child("ech", "");
    $total->new_child("num_ech", "");
    $total->new_child("date_ech", "Total");
    $total->new_child("solde_cap", afficheMontant($DATA["total_cap"], false));
    $total->new_child("solde_int", afficheMontant($DATA["total_int"], false));
    $total->new_child("solde_total", afficheMontant($DATA["total_ech"], false));
  } // fin parcours des epargnes

  return $document->dump_mem(true);
}

/**
 * xml_echeancier Génère le code XML pour un rapport concernant le dossier de client sélectionné.
 *
 * @param mixed $id_client
 * @param mixed $id_dossier
 * @access public
 * @return void
 */
function xml_echeancier($info_doss, $export_csv = false) {
  /* Le dossier doit avoir été déboursé; son état doit donc être 5,6,7,8 */
  global $adsys;
  global $global_multidevise,$global_id_agence;

  //XML
  $document = create_xml_doc("echeancier", "echeancier.dtd");
  //Element root
  $root = $document->root();

  foreach ($info_doss as $id_doss => $val_doss) {
    //Recup info produit crédit
    $whereCl = " where id='" . $val_doss["id_prod"] . "'";
    $produit = getProdInfo($whereCl, $id_doss); // Tableau associatif de produit $Produit[0]["champ"]

    //Recup info echeancier théorique
    switch ($val_doss['etat']) {
    case 5 : //Déboursé
    case 6 : //Clôturé
    case 9 : //En perte
      $whereCond = "WHERE (id_doss='$id_doss')";
      $echeancier = getEcheancier($whereCond);
      break;
    case 7 : //Attente rééchelonnement/moratoire
      //FIXME
    case 8 : //Exceptionnelement non-remboursé
      //FIXME
    default : //Autre
      //signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Etat du dossier de crédit inconnu ou non-géré par ce type de rapport!" 
 	    //Amélioration du mesage d'erreur
 	    $msg = "Etat du dossier de crédit inconnu ou non-géré par ce type de rapport!"; 
 	    $colb_tableau = '#e0e0ff'; 
 	    $MyPage = new HTML_erreur(_("Erreur dans la saisie des infos du client ")); 
 	    $MyPage->setMessage($msg); 
 	    $MyPage->addButton(BUTTON_OK, "Cdo-4"); 
 	    $MyPage->buildHTML(); 
 	    echo $MyPage->HTML_code; 
 	    exit();      
      break;
    }

    if ($global_multidevise)
      setMonnaieCourante($val_doss["devise"]);

    //En-tête généraliste
    if ($global_multidevise)
      gen_header($root, 9, " (" . $val_doss["devise"] . ")");
    else
      gen_header($root, 9);

    $infos_doss = $root->new_child("infos_doss", "");
    //En-tête généraliste
    gen_header($infos_doss, 'CRD-ECH');
    //En-tête contextuel
    $header_contextuel = $infos_doss->new_child("header_contextuel", "");
    $header_contextuel->new_child("num_client", makeNumClient($val_doss['id_client']));
    $header_contextuel->new_child("nom_client", getNomClient($val_doss['id_client']));
    $header_contextuel->new_child("num_credit", sprintf("%07d", $id_doss));
    $ET = getTousEtatCredit();
    $header_contextuel->new_child("etat_credit", adb_gettext($adsys["adsys_etat_dossier_credit"][$val_doss['etat']]) . " ("._("crédit")." " . $ET[$val_doss["cre_etat"]]["libel"] . ")");
    $header_contextuel->new_child("date_demande", pg2phpDate($val_doss['date_dem']));
    $header_contextuel->new_child("date_approb", pg2phpDate($val_doss['cre_date_approb']));
    $header_contextuel->new_child("date_debours", pg2phpDate($val_doss['cre_date_debloc']));
    $header_contextuel->new_child("produit", $produit[0]['libel']);
    $header_contextuel->new_child("montant", afficheMontant($val_doss['cre_mnt_octr'], true));
    $header_contextuel->new_child("taux_int", $produit[0]['tx_interet'] * 100);
    $header_contextuel->new_child("delais_grace", $val_doss['delai_grac']);
    if ($global_multidevise)
      $header_contextuel->new_child("devise", $val_doss["devise"]);

    //Pour chaque échéance théorique
    $total = array ();
    $total['tot_cap_du'] = 0;
    $total['tot_int_du'] = 0;
    $total['tot_gar_du'] = 0;
    $total['tot_total_du'] = 0;
    $total['tot_cap'] = 0;
    $total['tot_int'] = 0;
    $total['tot_gar'] = 0;
    $total['tot_pen'] = 0;
    $total['tot_total'] = 0;
    $total['tot_remb_cap'] = 0;
    $total['tot_remb_int'] = 0;
    $total['tot_remb_gar'] = 0;
    $total['tot_remb_pen'] = 0;
    $total['tot_remb_total'] = 0;

    reset($echeancier);
    while (list (, $value_etr) = each($echeancier)) {
      $echeance = $infos_doss->new_child("echeance", "");
      //Echéance théorique
      $ech_theo = $echeance->new_child("ech_theo", "");
      $ech_theo->new_child("date_ech", pg2phpDate($value_etr['date_ech']));
      if ($export_csv) {
        $ech_theo->new_child("cap_du", afficheMontant($value_etr['mnt_cap'], false, $typ_raport = true));
        $ech_theo->new_child("int_du", afficheMontant($value_etr['mnt_int'], false, $typ_raport = true));
        $ech_theo->new_child("gar_du", afficheMontant($value_etr['mnt_gar'], false, $typ_raport = true));
        $ech_theo->new_child("total_du", afficheMontant($value_etr['mnt_cap'] + $value_etr['mnt_int'] + $value_etr['mnt_gar'], false, $typ_raport = true));
        $ech_theo->new_child("solde_cap", afficheMontant($value_etr['solde_cap'], false, $typ_raport = true));
        $ech_theo->new_child("solde_int", afficheMontant($value_etr['solde_int'], false, $typ_raport = true));
        $ech_theo->new_child("solde_gar", afficheMontant($value_etr['solde_gar'], false, $typ_raport = true));
        $ech_theo->new_child("solde_pen", afficheMontant($value_etr['solde_pen'], false, $typ_raport = true));
        $ech_theo->new_child("solde_total", afficheMontant($value_etr['solde_cap'] + $value_etr['solde_int'] + $value_etr['solde_pen'] + $value_etr['solde_gar'], false, $typ_raport = true));
      } else {
        $ech_theo->new_child("cap_du", afficheMontant($value_etr['mnt_cap']));
        $ech_theo->new_child("int_du", afficheMontant($value_etr['mnt_int']));
        $ech_theo->new_child("gar_du", afficheMontant($value_etr['mnt_gar']));
        $ech_theo->new_child("total_du", afficheMontant($value_etr['mnt_cap'] + $value_etr['mnt_int'] + $value_etr['mnt_gar']));
        $ech_theo->new_child("solde_cap", afficheMontant($value_etr['solde_cap']));
        $ech_theo->new_child("solde_int", afficheMontant($value_etr['solde_int']));
        $ech_theo->new_child("solde_gar", afficheMontant($value_etr['solde_gar']));
        $ech_theo->new_child("solde_pen", afficheMontant($value_etr['solde_pen']));
        $ech_theo->new_child("solde_total", afficheMontant($value_etr['solde_cap'] + $value_etr['solde_int'] + $value_etr['solde_pen'] + $value_etr['solde_gar']));
      }
      $total['tot_cap_du'] += $value_etr['mnt_cap'];
      $total['tot_int_du'] += $value_etr['mnt_int'];
      $total['tot_gar_du'] += $value_etr['mnt_gar'];
      $total['tot_total_du'] += ($value_etr['mnt_cap'] + $value_etr['mnt_int'] + $value_etr['mnt_gar']);
      $total['tot_cap'] += $value_etr['solde_cap'];
      $total['tot_int'] += $value_etr['solde_int'];
      $total['tot_gar'] += $value_etr['solde_gar'];
      $total['tot_pen'] += $value_etr['solde_pen'];
      $total['tot_total'] += $value_etr['solde_cap'] + $value_etr['solde_int'] + $value_etr['solde_pen'] + $value_etr['solde_gar'];

      //Suivi associé à l'échéance théorique
      $suivi = getRemboursement(" WHERE (id_doss=$id_doss) AND (id_ech=" . $value_etr['id_ech'] . ") ");
      reset($suivi);
      // Cas particulier du crédit repris manuellement
      if (sizeof($suivi) == 0 && $value_etr["remb"] == 't') {
        // Ajoute un remboursement artificiel avec N/A
        $suivi_remb = $echeance->new_child("suivi_remb", "");
        $suivi_remb->new_child("date_suivi", "N/A");
        $suivi_remb->new_child("mnt_cap", "N/A");
        $suivi_remb->new_child("mnt_int", "N/A");
        $suivi_remb->new_child("mnt_gar", "N/A");
        $suivi_remb->new_child("mnt_pen", "N/A");
        $suivi_remb->new_child("mnt_total", "N/A");
      } else {
        while (list (, $value_sre) = each($suivi)) {
          $suivi_remb = $echeance->new_child("suivi_remb", "");
          $suivi_remb->new_child("date_suivi", pg2phpDate($value_sre['date_remb']));
          if ($export_csv) {
            $suivi_remb->new_child("mnt_cap", afficheMontant($value_sre['mnt_remb_cap'], false, $typ_raport = true));
            $suivi_remb->new_child("mnt_int", afficheMontant($value_sre['mnt_remb_int'], false, $typ_raport = true));
            $suivi_remb->new_child("mnt_gar", afficheMontant($value_sre['mnt_remb_gar'], false, $typ_raport = true));
            $suivi_remb->new_child("mnt_pen", afficheMontant($value_sre['mnt_remb_pen'], false, $typ_raport = true));
            $suivi_remb->new_child("mnt_total", afficheMontant($value_sre['mnt_remb_cap'] + $value_sre['mnt_remb_int'] + $value_sre['mnt_remb_pen'] + $value_sre['mnt_remb_gar'], false, $typ_raport = true));

          } else {
            $suivi_remb->new_child("mnt_cap", afficheMontant($value_sre['mnt_remb_cap']));
            $suivi_remb->new_child("mnt_int", afficheMontant($value_sre['mnt_remb_int']));
            $suivi_remb->new_child("mnt_gar", afficheMontant($value_sre['mnt_remb_gar']));
            $suivi_remb->new_child("mnt_pen", afficheMontant($value_sre['mnt_remb_pen']));
            $suivi_remb->new_child("mnt_total", afficheMontant($value_sre['mnt_remb_cap'] + $value_sre['mnt_remb_int'] + $value_sre['mnt_remb_pen'] + $value_sre['mnt_remb_gar']));
          }
          $total['tot_remb_cap'] += $value_sre['mnt_remb_cap'];
          $total['tot_remb_int'] += $value_sre['mnt_remb_int'];
          $total['tot_remb_gar'] += $value_sre['mnt_remb_gar'];
          $total['tot_remb_pen'] += $value_sre['mnt_remb_pen'];
          $total['tot_remb_total'] += $value_sre['mnt_remb_cap'] + $value_sre['mnt_remb_int'] + $value_sre['mnt_remb_pen'] + $value_sre['mnt_remb_gar'];
        }
      }
    }

    $xml_total = $echeance->new_child("xml_total", "");
    if ($export_csv) {
      $xml_total->new_child("tot_cap_du", afficheMontant($total['tot_cap_du'], false, $typ_raport = true));
      $xml_total->new_child("tot_int_du", afficheMontant($total['tot_int_du'], false, $typ_raport = true));
      $xml_total->new_child("tot_gar_du", afficheMontant($total['tot_gar_du'], false, $typ_raport = true));
      $xml_total->new_child("tot_total_du", afficheMontant($total['tot_total_du'], false, $typ_raport = true));
      $xml_total->new_child("tot_remb_cap", afficheMontant($total['tot_remb_cap'], false, $typ_raport = true));
      $xml_total->new_child("tot_remb_int", afficheMontant($total['tot_remb_int'], false, $typ_raport = true));
      $xml_total->new_child("tot_remb_gar", afficheMontant($total['tot_remb_gar'], false, $typ_raport = true));
      $xml_total->new_child("tot_remb_pen", afficheMontant($total['tot_remb_pen'], false, $typ_raport = true));
      $xml_total->new_child("tot_remb_total", afficheMontant($total['tot_remb_total'], false, $typ_raport = true));
      $xml_total->new_child("tot_cap", afficheMontant($total['tot_cap'], false, $typ_raport = true));
      $xml_total->new_child("tot_int", afficheMontant($total['tot_int'], false, $typ_raport = true));
      $xml_total->new_child("tot_gar", afficheMontant($total['tot_gar'], false, $typ_raport = true));
      $xml_total->new_child("tot_pen", afficheMontant($total['tot_pen'], false, $typ_raport = true));
      $xml_total->new_child("tot_total", afficheMontant($total['tot_total'], false, $typ_raport = true));
    } else {
      $xml_total->new_child("tot_cap_du", afficheMontant($total['tot_cap_du']));
      $xml_total->new_child("tot_int_du", afficheMontant($total['tot_int_du']));
      $xml_total->new_child("tot_gar_du", afficheMontant($total['tot_gar_du']));
      $xml_total->new_child("tot_total_du", afficheMontant($total['tot_total_du']));
      $xml_total->new_child("tot_remb_cap", afficheMontant($total['tot_remb_cap']));
      $xml_total->new_child("tot_remb_int", afficheMontant($total['tot_remb_int']));
      $xml_total->new_child("tot_remb_gar", afficheMontant($total['tot_remb_gar']));
      $xml_total->new_child("tot_remb_pen", afficheMontant($total['tot_remb_pen']));
      $xml_total->new_child("tot_remb_total", afficheMontant($total['tot_remb_total']));
      $xml_total->new_child("tot_cap", afficheMontant($total['tot_cap']));
      $xml_total->new_child("tot_int", afficheMontant($total['tot_int']));
      $xml_total->new_child("tot_gar", afficheMontant($total['tot_gar']));
      $xml_total->new_child("tot_pen", afficheMontant($total['tot_pen']));
      $xml_total->new_child("tot_total", afficheMontant($total['tot_total']));
    }
  }
  return $document->dump_mem(true);

}
?>