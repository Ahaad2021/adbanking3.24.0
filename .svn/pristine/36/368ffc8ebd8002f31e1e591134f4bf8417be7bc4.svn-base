<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2: */
/**
 * Génère le code XML pour les rapports agence
 * @package Rapports
 */
require_once 'lib/misc/xml_lib.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/dbProcedures/rapports.php';

function xml_agence($DATA, $export_csv = false) {

  global $global_id_agence;
  global $global_multidevise;
  global $adsys;

  // Document
  $document = create_xml_doc("stat_agence", "stat_agence.dtd");
  // Element root
  $root = $document->root();
  // En-tête généraliste
  echo"post = ".$_POST["agence"];
  if (isSiege() && $_POST["agence"] == "")
    gen_header($root, 'AGC-STR');
  else
    gen_header($root, 'AGC-STA');



  $debut_periode = date("d/m/Y", mktime(0, 0, 0, 1, 1, date("Y")));

  // Si le rapport est demandé pour plus d'une agence on affiche pas les Infos globales
  if ($DATA['a_infosGlobales']) {
    // Infos globales
    $infos = $DATA['a_infosGlobales'];
    $infos_globales = $root->new_child("infos_globales", "");
    $infos_globales->new_child("debut_periode", $debut_periode);
    $infos_globales->new_child("fin_periode", date("d/m/Y"));
    $infos_globales->new_child("responsable", $infos['responsable']);
    $infos_globales->new_child("type_structure", adb_gettext($adsys["adsys_typ_struct"][$infos['type_structure']]));
    $infos_globales->new_child("date_agrement", pg2phpDate($infos['date_agrement']));
    $infos_globales->new_child("num_agrement", $infos['num_agrement']);
  }

  $msg = _("Indicateur non diponible");

  // Ratios prudentiels
  if ($DATA['ratios_prudentiels']) {
    $source = $DATA['ratios_prudentiels'];
    $ratios_prudentiels = $root->new_child("ratio_prud", "");
    if ($source['total_epargne'] > 0) {
      $ratios_prudentiels->new_child("limit_prets_dirig", affichePourcentage($source['limitation_prets_dirigeants'], 2, false));
      $ratios_prudentiels->new_child("limit_risk_membre", affichePourcentage($source['limitation_risque_membre'], 2, false));
      $ratios_prudentiels->new_child("tx_transform", affichePourcentage($source['taux_transformation'], 2, false));
    } else {
      $ratios_prudentiels->new_child("limit_prets_dirig", $msg);
      $ratios_prudentiels->new_child("limit_risk_membre", $msg);
      $ratios_prudentiels->new_child("tx_transform", $msg);
    }
  }

  // Qualité du portefeuille
  if ($DATA['qualite_portefeuille']) {
    $source = $DATA['qualite_portefeuille'];
    $qualite_portefeuille = $root->new_child("indic_perf", "");
    if ($source['encours_brut'] > 0) {
      $qualite_portefeuille->new_child("risk_1_ech", affichePourcentage($source['risque_1_ech'], 2, false));
      $qualite_portefeuille->new_child("risk_2_ech", affichePourcentage($source['risque_2_ech'], 2, false));
      $qualite_portefeuille->new_child("risk_3_ech", affichePourcentage($source['risque_3_ech'], 2, false));
      $qualite_portefeuille->new_child("risk_30j_ech", affichePourcentage($source['risque_30_jours'], 2, false));
      $qualite_portefeuille->new_child("tx_provisions", affichePourcentage($source['taux_provisions'], 2, false));
      $qualite_portefeuille->new_child("tx_reech", affichePourcentage($source['taux_reech'], 2, false));
      $qualite_portefeuille->new_child("tx_perte", affichePourcentage($source['taux_perte'], 2, false));
    } else {
      $qualite_portefeuille->new_child("risk_1_ech", $msg);
      $qualite_portefeuille->new_child("risk_2_ech", $msg);
      $qualite_portefeuille->new_child("risk_3_ech", $msg);
      $qualite_portefeuille->new_child("risk_30j_ech", $msg);
      $qualite_portefeuille->new_child("tx_provisions", $msg);
      $qualite_portefeuille->new_child("tx_reech", $msg);
      $qualite_portefeuille->new_child("tx_perte", $msg);
    }
  }

  // Indices de couverture
  if ($DATA['indices_couverture']) {
    $source = $DATA['indices_couverture'];
    $indices_couverture = $root->new_child("couverture", "");
    $indices_couverture->new_child("nbr_credits", $source['nombre_credits']);
    $indices_couverture->new_child("portefeuille", afficheMontant($source['encours_brut'], false, $export_csv));
    $indices_couverture->new_child("nbre_cpt_epargne", $source['nombre_epargne']);
    $indices_couverture->new_child("total_epargne", afficheMontant($source['total_epargne'], false, $export_csv));
    $indices_couverture->new_child("tx_renouvellement_credits", affichePourcentage($source['taux_renouvellement_credits'], 2, false));
    $indices_couverture->new_child("first_credit_moyen", afficheMontant($source['first_credit_moyen'], false, $export_csv));
    $indices_couverture->new_child("first_credit_median", afficheMontant($source['first_credit_median'], false, $export_csv));
    $indices_couverture->new_child("credit_moyen", afficheMontant($source['credit_moyen'], false, $export_csv));
    $indices_couverture->new_child("credit_median", afficheMontant($source['credit_median'], false, $export_csv));
    $indices_couverture->new_child("epargne_moyen_cpte", afficheMontant($source['epargne_moyen_cpte'], false, $export_csv));
    $indices_couverture->new_child("epargne_median_cpte", afficheMontant($source['epargne_median_cpte'], false, $export_csv));
    $indices_couverture->new_child("epargne_moyen_client", afficheMontant($source['epargne_moyen_client'], false, $export_csv));
    $indices_couverture->new_child("epargne_median_client", afficheMontant($source['epargne_median_client'], false, $export_csv));
  }

  // Indices de productivité
  if ($DATA['indices_productivite']) {
    $source = $DATA['indices_productivite'];
    $indices_productivite = $root->new_child("indic_productivite", "");
    if ($source['encours_net'] > 0) {
      $indices_productivite->new_child("rendement_portefeuille", affichePourcentage($source['rendement_portefeuille'], 2, false));
      $indices_productivite->new_child("rendement_theorique", affichePourcentage($source['rendement_theorique'], 2, false));
    } else {
      $indices_productivite->new_child("rendement_portefeuille", $msg);
      $indices_productivite->new_child("rendement_theorique", $msg);
    }
    if ($source['remb_attendus'] > 0) {
      $indices_productivite->new_child("ecart_rendement", affichePourcentage($source['ecart_rendement'], 2, false));
    } else {
      $indices_productivite->new_child("ecart_rendement", $msg);
    }
  }

  // Indices d'impact
  if ($DATA['indices_impact']) {
    $indices_impact = $root->new_child("indic_impact", "");

    $source = $DATA['indices_impact']['general'];
    $indices_impact->new_child("nbr_moyen_gi", $source["nombre_moyen_gi"]);
    $indices_impact->new_child("nbr_moyen_gs", $source["nombre_moyen_gs"]);
    $indices_impact->new_child("total_membres_empr_gi",$source['total_membre_empr_gi']);
    $indices_impact->new_child("total_membres_empr_gs",$source['total_membre_empr_gs']);
    $indices_impact->new_child("total_hommes_gs", $source['total_homme_gs']);
    $indices_impact->new_child("total_femmes_gs", $source['total_femme_gs']);
    $source = $DATA['indices_impact']['clients'];

    $clients = $indices_impact->new_child("clients", "");
    $nbre_individus = $clients->new_child("nbre_individus", "");
    $nbre_individus->new_child("nbre_total", $source['total']);
    $nbre_individus->new_child("nbre_pp", $source['pp']);
    $nbre_individus->new_child("prc_homme", affichePourcentage($source['pourcentage_homme'], 2, false));
    $nbre_individus->new_child("prc_femme", affichePourcentage($source['pourcentage_femme'], 2, false));
    $nbre_individus->new_child("nbre_pm", $source['pm']);
    $nbre_individus->new_child("nbre_gi", $source['gi']);
    $nbre_individus->new_child("nbre_gs", $source['gs']);


    $source = $DATA['indices_impact']['epargnants'];
    $epargnants = $indices_impact->new_child("epargnants", "");
    $nbre_individus = $epargnants->new_child("nbre_individus", "");
    $nbre_individus->new_child("nbre_total", $source['total']);
    $nbre_individus->new_child("nbre_pp", $source['pp']);
    $nbre_individus->new_child("prc_homme", affichePourcentage($source['pourcentage_homme'], 2, false));
    $nbre_individus->new_child("prc_femme", affichePourcentage($source['pourcentage_femme'], 2, false));
    $nbre_individus->new_child("nbre_pm", $source['pm']);
    $nbre_individus->new_child("nbre_gi", $source['gi']);
    $nbre_individus->new_child("nbre_gs", $source['gs']);

    $source = $DATA['indices_impact']['emprunteurs'];
    $emprunteurs = $indices_impact->new_child("emprunteurs", "");
    $nbre_individus = $emprunteurs->new_child("nbre_individus", "");
    $nbre_individus->new_child("nbre_total", $source['total']);
    $nbre_individus->new_child("nbre_pp", $source['pp']);
    $nbre_individus->new_child("prc_homme", affichePourcentage($source['pourcentage_homme'], 2, false));
    $nbre_individus->new_child("prc_femme", affichePourcentage($source['pourcentage_femme'], 2, false));
    $nbre_individus->new_child("nbre_pm", $source['pm']);
    $nbre_individus->new_child("nbre_gi", $source['gi']);
    $nbre_individus->new_child("nbre_gs", $source['gs']);
  }

  //Liste des agences consolidées
  if (isSiege() && $_POST["agence"] == NULL) {
   $list_agence=getListAgenceConsolide();
   foreach($list_agence as $id_ag =>$data_agence) {
     $enreg_agence=$root->new_child("enreg_agence","");
     $enreg_agence->new_child("is_siege", "true");
     $enreg_agence->new_child("id_ag", $data_agence['id']);
     $enreg_agence->new_child("libel_ag", $data_agence['libel']);
     $enreg_agence->new_child("date_max", $data_agence['date_dernier_mouv']);
   }
  }else{
  	 $enreg_agence=$root->new_child("enreg_agence","");
  	 $enreg_agence->new_child("is_siege", "false");
  }

  return $document->dump_mem(true);
}

/**
 * Fonction utilisée par le rapport Statistiques et indicateur d'agence
 * Renvoie un tableau contenant les statistiques d'une agence ou du réseau
 * @author Djibril NIANG
 * @since 2.9
 * @param array $liste_ag  Liste des agences à imprimer les données
 * @param Boolean $prudentiel pour avoir les ratios prudentiels
 * @param $qualite_port pour avoir la qulité du portefeuille
 * @param Boolean $couverture
 * @param Boolean $productivite pour la productivité de l'agence ou du réseau
 * @param Boolean $impact pour les statistiques sur les clients
 * @return Array $data tableau contenant les statistiques d'une agence ou du réseau
*/
function getStatistiqueAgence($list_agence, $prudentiel, $qualite_port, $couverture, $productivite, $impact) {

  global $global_id_agence;

  //inialisation du tableau de retour
  $data['prudentiel'] = 0;
  $data['qualite_port'] = 0;
  $data['couverture'] = 0;
  $data['productivite'] = 0;
  $data['impact'] = 0;
  $data['total_epargne'] = 0;
  $data['credit']['dirigeants'] = 0;
  $data['credit']['max'] = 0;
  $data['credit']['brut'] = 0;
  $data['credit']['risque_ech_1'] = 0;
  $data['credit']['risque_ech_2'] = 0;
  $data['credit']['risque_ech_3'] = 0;
  $data['credit']['risque_ech_30j'] = 0;
  $data['credit']['reech'] = 0;
  $data['credit']['perte'] = 0;
  $data['credit']['net'] = 0;
  $data['credit']['id_cli'] = 0;
  $data['credit']['tx_renouvellement_credits'] = 0;
  $data['credit']['first_credit_moyen'] = 0;
  $data['credit']['first_credit_median'] = 0;
  $data['credit']['credit_moyen'] = 0;
  $data['credit']['credit_median'] = 0;
  $data['credit']['epargne_moyen_cpte'] = 0;
  $data['credit']['epargne_median_cpte'] = 0;
  $data['credit']['epargne_median_client'] = 0;
  $data['credit']['epargne_moyen_client'] = 0;
  $data['credit']['rendement_portefeuille'] = 0;
  $data['credit']['rendement_theorique'] = 0;
  $data['credit']['ecart_rendement'] = 0;
  $data['credit']['nbr_moyen_gi'] = 0;
  $data['credit']['nbr_moyen_gs'] = 0;
  $data['nbre_cpt_epargne'] = 0;
  $data['individus']['gi_nbre_membre'] = 0;
  $data['individus']['clients']['homme'] = 0;
  $data['individus']['clients']['femme'] = 0;
  $data['individus']['clients']['gi'] = 0;
  $data['individus']['clients']['gs'] = 0;
  $data['individus']['clients']['pm'] = 0;
  $data['individus']['gi_nbre_membre'] = 0;
  $data['individus']['gs_nbre_membre'] = 0;
  $data['individus']['gs_nbre_hommes'] = 0;
  $data['individus']['gs_nbre_femmes'] = 0;
  $data['individus']['epargnants']['homme'] = 0;
  $data['individus']['epargnants']['femme'] = 0;
  $data['individus']['epargnants']['gi'] = 0;
  $data['individus']['epargnants']['gs'] = 0;
  $data['individus']['epargnants']['pm'] = 0;
  $data['individus']['emprunteurs']['homme'] = 0;
  $data['individus']['emprunteurs']['femme'] = 0;
  $data['individus']['emprunteurs']['gi'] = 0;
  $data['individus']['emprunteurs']['gs'] = 0;
  $data['individus']['emprunteurs']['pm'] = 0;

  //Parcours des agences
  foreach($list_agence as $key_id_ag =>$value) {
    // Parcours des agences
    setGlobalIdAgence($key_id_ag);
    $total_epargne = 0;
    $total_epargne += get_total_epargne($key_id_ag);
    $credit = array();
    $credit = get_stat_credit();
    $individus = array();
    $individus = get_stat_individus($credit['id_cli'], $key_id_ag);
    $nbre_cpt_epargne = 0;
    $nbre_cpt_epargne += getNombreCptEpargne($key_id_ag);

    //les parametres
    $data['prudentiel'] = $prudentiel;
    $data['qualite_port'] = $qualite_port;
    $data['couverture'] = $couverture;
    $data['productivite'] = $productivite;
    $data['impact'] = $impact;

    //épargne
    $data['nbre_cpt_epargne'] += $nbre_cpt_epargne;
    $data['total_epargne'] += $total_epargne;

    //credits
    $data['credit']['dirigeants'] += $credit['dirigeants'];
    $data['credit']['max'] += $credit['max'];
    $data['credit']['brut'] = $credit['brut'];
    $data['credit']['risque_ech_1'] += $credit['risque_ech_1'];
    $data['credit']['risque_ech_2'] += $credit['risque_ech_2'];
    $data['credit']['risque_ech_3'] += $credit['risque_ech_3'];
    $data['credit']['risque_ech_30j'] += $credit['risque_ech_30j'];
    $data['credit']['reech'] += $credit['reech'];
    $data['credit']['perte'] += $credit['perte'];
    $data['credit']['net'] += $credit['net'];
    $data['credit']['tx_renouvellement_credits'] += $credit['tx_renouvellement_credits'];
    $data['credit']['first_credit_moyen'] += $credit['first_credit_moyen'];
    $data['credit']['first_credit_median'] += $credit['first_credit_median'];
    $data['credit']['credit_moyen'] += $credit['credit_moyen'];
    $data['credit']['credit_median'] += $credit['credit_median'];
    $data['credit']['epargne_moyen_cpte'] += $credit['epargne_moyen_cpte'];
    $data['credit']['epargne_median_cpte'] += $credit['epargne_median_cpte'];
    $data['credit']['epargne_median_client'] += $credit['epargne_median_client'];
    $data['credit']['epargne_moyen_client'] += $credit['epargne_moyen_client'];
    $data['credit']['rendement_portefeuille'] += $credit['rendement_portefeuille'];
    $data['credit']['rendement_theorique'] += $credit['rendement_theorique'];
    $data['credit']['ecart_rendement'] += $credit['ecart_rendement'];
    $data['credit']['nbr_moyen_gi'] += $credit['nbr_moyen_gi'];
    $data['credit']['nbr_moyen_gs'] += $credit['nbr_moyen_gs'];

    //individus
    $data['individus']['gi_nbre_membre'] += $individus['gi_nbre_membre'];
    $data['individus']['clients']['homme'] += $individus['clients']['homme'];
    $data['individus']['clients']['femme'] += $individus['clients']['femme'];
    $data['individus']['clients']['gi'] += $individus['clients']['gi'];
    $data['individus']['clients']['gs'] += $individus['clients']['gs'];
    $data['individus']['clients']['pm'] += $individus['clients']['pm'];
    //individus epargants
    $data['individus']['epargnants']['homme'] += $individus['epargnants']['homme'];
    $data['individus']['epargnants']['femme'] += $individus['epargnants']['femme'];
    $data['individus']['epargnants']['gi'] += $individus['epargnants']['gi'];
    $data['individus']['epargnants']['gs'] += $individus['epargnants']['gs'];
    $data['individus']['epargnants']['pm'] += $individus['epargnants']['pm'];
    //emprunteurs
    $data['individus']['emprunteurs']['homme'] += $individus['emprunteurs']['homme'];
    $data['individus']['emprunteurs']['femme'] += $individus['emprunteurs']['femme'];
    $data['individus']['emprunteurs']['gi'] += $individus['emprunteurs']['gi'];
    $data['individus']['emprunteurs']['gs'] += $individus['emprunteurs']['gs'];
    $data['individus']['emprunteurs']['pm'] += $individus['emprunteurs']['pm'];
    //totaux
    $data['individus']['gi_nbre_membre'] += $individus['gi_nbre_membre'];
    $data['individus']['gs_nbre_membre'] += $individus['gs_nbre_membre'];
    $data['individus']['gs_nbre_hommes'] += $individus['gs_nbre_hommes'];
    $data['individus']['gs_nbre_femmes'] += $individus['gs_nbre_femmes'];

    resetGlobalIdAgence();
  }  // fin parcours des agences

  return $data;
}

function xml_activite_agence($data, $debut_periode, $fin_periode, $Export_csv = false) {
  global $global_id_agence;
  global $global_id_client;
  global $global_multidevise;
  global $adsys;
  global $dbHandler;

  $document = create_xml_doc("activite_agence", "activite_agence.dtd");
  //Element root
  $root = $document->root();
  //En-tête généraliste
  gen_header($root, 'AGC-ACT');
  $debut_periode = date("d/m/Y", mktime(0, 0, 0, 1, 1, date("Y")));
  // Infos globales
  $infos_globales = $root->new_child("infos_globales", "");
  $infos = getAgenceDatas($global_id_agence);
  $infos_globales->new_child("debut_periode", $debut_periode);
  $infos_globales->new_child("fin_periode", $fin_periode);
  $clients = $root->new_child("clients", "");
  $agences = $root->new_child("agences", "");
  $clients->new_child("date_deb", $debut_periode);
  $clients->new_child("nbre_hom_deb", $data['client_deb']['homme']);
  $clients->new_child("nbre_fem_deb", $data['client_deb']['femme']);
  $clients->new_child("nbre_pm_deb", $data['client_deb']['pm']);
  $tot_deb = $data['client_deb']['homme'] + $data['client_deb']['femme'] + $data['client_deb']['pm'];
  $clients->new_child("total_client_deb", $tot_deb);
  $clients->new_child("date_fin", $fin_periode);
  $clients->new_child("nbre_hom_fin", $data['client_fin']['homme']);
  $clients->new_child("nbre_fem_fin", $data['client_fin']['femme']);
  $clients->new_child("nbre_pm_fin", $data['client_fin']['pm']);
  $tot_fin = $data['client_fin']['homme'] + $data['client_fin']['femme'] + $data['client_fin']['pm'];
  $clients->new_child("total_client_fin", $tot_fin);
  $diff_hom = $data['client_fin']['homme'] - $data['client_deb']['homme'];
  $diff_fem = $data['client_fin']['femme'] - $data['client_deb']['femme'];
  $diff_pm = $data['client_fin']['pm'] - $data['client_deb']['pm'];
  $diff_tot = $tot_fin - $tot_deb;
  $clients->new_child("diff_fem", $diff_fem);
  $clients->new_child("diff_hom", $diff_hom);
  $clients->new_child("diff_pm", $diff_pm);
  $clients->new_child("diff_tot", $diff_tot);
  $div_hom = max(1, $data['client_deb']['homme']); //Pour éviter la division par zéro
  $div_fem = max(1, $data['client_deb']['femme']); //Pour éviter la division par zéro
  $div_pm = max(1, $data['client_deb']['pm']); //Pour éviter la division par zéro
  $div_tot = max(1, $tot_deb); //Pour éviter la division par zéro
  $clients->new_child("prc_hom", affichePourcentage($diff_hom / $div_hom));
  $clients->new_child("prc_fem", affichePourcentage($diff_fem / $div_fem));
  $clients->new_child("prc_pm", affichePourcentage($diff_pm / $div_pm));
  $clients->new_child("prc_tot", affichePourcentage($diff_tot / $div_tot));
  //Nombre et montant des crédits octroyés
  $credits = $root->new_child("credits", "");
  $credits->new_child("cred_jan", $data['credit']['janvier']['nbre_credit']);
  $credits->new_child("mnt_jan", afficheMontant($data['credit']['janvier']['montant_tot']));
  $credits->new_child("cred_fev", $data['credit']['fevrier']['nbre_credit']);
  $credits->new_child("mnt_fev", afficheMontant($data['credit']['fevrier']['montant_tot']));
  $credits->new_child("cred_mars", $data['credit']['mars']['nbre_credit']);
  $credits->new_child("mnt_mars",afficheMontant( $data['credit']['mars']['montant_tot']));
  $credits->new_child("cred_av", $data['credit']['avril']['nbre_credit']);
  $credits->new_child("mnt_av", afficheMontant($data['credit']['avril']['montant_tot']));
  $credits->new_child("cred_mai", $data['credit']['mai']['nbre_credit']);
  $credits->new_child("mnt_mai", afficheMontant($data['credit']['mai']['montant_tot']));
  $credits->new_child("cred_juin", $data['credit']['juin']['nbre_credit']);
  $credits->new_child("mnt_juin", afficheMontant($data['credit']['juin']['montant_tot']));
  $credits->new_child("cred_jui", $data['credit']['juillet']['nbre_credit']);
  $credits->new_child("mnt_jui",afficheMontant( $data['credit']['juillet']['montant_tot']));
  $credits->new_child("cred_aout", $data['credit']['aout']['nbre_credit']);
  $credits->new_child("mnt_aout", afficheMontant($data['credit']['aout']['montant_tot']));
  $credits->new_child("cred_sept", $data['credit']['septembre']['nbre_credit']);
  $credits->new_child("mnt_sept",afficheMontant( $data['credit']['septembre']['montant_tot']));
  $credits->new_child("cred_oc", $data['credit']['octobre']['nbre_credit']);
  $credits->new_child("mnt_oc",afficheMontant( $data['credit']['octobre']['montant_tot']));
  $credits->new_child("cred_nov", $data['credit']['novembre']['nbre_credit']);
  $credits->new_child("mnt_nov",afficheMontant( $data['credit']['novembre']['montant_tot']));
  $credits->new_child("cred_dec", $data['credit']['decembre']['nbre_credit']);
  $credits->new_child("mnt_dec",afficheMontant( $data['credit']['decembre']['montant_tot']));

  $cred_tot = $data['credit']['janvier']['nbre_credit'] + $data['credit']['fevrier']['nbre_credit'] + $data['credit']['mars']['nbre_credit'] + $data['credit']['avril']['nbre_credit'] + $data['credit']['mai']['nbre_credit'] + $data['credit']['juin']['nbre_credit'] + $data['credit']['juillet']['nbre_credit'] + $data['credit']['aout']['nbre_credit'] + $data['credit']['septembre']['nbre_credit'] + $data['credit']['octobre']['nbre_credit'] + $data['credit']['novembre']['nbre_credit'] + $data['credit']['decembre']['nbre_credit'];

  $mnt_tot = $data['credit']['janvier']['montant_tot'] + $data['credit']['fevrier']['montant_tot'] + $data['credit']['mars']['montant_tot'] + $data['credit']['avril']['montant_tot'] + $data['credit']['mai']['montant_tot'] + $data['credit']['juin']['montant_tot'] + $data['credit']['juillet']['montant_tot'] + $data['credit']['aout']['montant_tot'] + $data['credit']['septembre']['montant_tot'] + $data['credit']['octobre']['montant_tot'] + $data['credit']['novembre']['montant_tot'] + $data['credit']['decembre']['montant_tot'];

  $credits->new_child("mnt_tot", afficheMontant($mnt_tot));
  $credits->new_child("cred_tot", $cred_tot);
  //Epargnes collectées
  $epargnes = $root->new_child("epargnes", "");
  $epargnes->new_child("epargne_jan",afficheMontant( $data['epargne']['janvier']));
  $epargnes->new_child("epargne_fev",afficheMontant( $data['epargne']['fevrier']));
  $epargnes->new_child("epargne_mars",afficheMontant( $data['epargne']['mars']));
  $epargnes->new_child("epargne_av", afficheMontant($data['epargne']['avril']));
  $epargnes->new_child("epargne_mai",afficheMontant( $data['epargne']['mai']));
  $epargnes->new_child("epargne_juin",afficheMontant( $data['epargne']['juin']));
  $epargnes->new_child("epargne_jui", afficheMontant($data['epargne']['juillet']));
  $epargnes->new_child("epargne_aout", afficheMontant($data['epargne']['aout']));
  $epargnes->new_child("epargne_sept", afficheMontant($data['epargne']['septembre']));
  $epargnes->new_child("epargne_oc", afficheMontant($data['epargne']['octobre']));
  $epargnes->new_child("epargne_nov", afficheMontant($data['epargne']['novembre']));
  $epargnes->new_child("epargne_dec",afficheMontant( $data['epargne']['decembre']));
  //Encours epargne
  $epargnes->new_child("encour_jan",afficheMontant( $data['encours']['janvier']));
  $epargnes->new_child("encour_fev", afficheMontant($data['encours']['fevrier']));
  $epargnes->new_child("encour_mars",afficheMontant( $data['encours']['mars']));
  $epargnes->new_child("encour_av",afficheMontant( $data['encours']['avril']));
  $epargnes->new_child("encour_mai", afficheMontant($data['encours']['mai']));
  $epargnes->new_child("encour_juin", afficheMontant($data['encours']['juin']));
  $epargnes->new_child("encour_jui",afficheMontant( $data['encours']['juillet']));
  $epargnes->new_child("encour_aout", afficheMontant($data['encours']['aout']));
  $epargnes->new_child("encour_sept", afficheMontant($data['encours']['septembre']));
  $epargnes->new_child("encour_oc", afficheMontant($data['encours']['octobre']));
  $epargnes->new_child("encour_nov", afficheMontant($data['encours']['novembre']));
  $epargnes->new_child("encour_dec", afficheMontant($data['encours']['decembre']));
  //Liste des agences consolidées
  if (isSiege() && $_POST["agence"] == NULL) {
   $list_agence=getListAgenceConsolide();
   foreach($list_agence as $id_ag =>$data_agence) {
     $enreg_agence=$root->new_child("enreg_agence","");
     $enreg_agence->new_child("is_siege", "true");
     $enreg_agence->new_child("id_ag", $data_agence['id']);
     $enreg_agence->new_child("libel_ag", $data_agence['libel']);
     $enreg_agence->new_child("date_max", $data_agence['date_dernier_mouv']);
   }
  }else{
  	 $enreg_agence=$root->new_child("enreg_agence","");
  	 $enreg_agence->new_child("is_siege", "false");
  }
  //total encours épargne
  $encour_tot = $data['encours']['janvier'] + $data['encours']['fevrier']+$data['encours']['mars']+$data['encours']['avril']+$data['encours']['mai']+$data['encours']['juin']+$data['encours']['juillet']+$data['encours']['aout']+$data['encours']['septembre']+$data['encours']['octobre']+$data['encours']['novembre']+$data['encours']['decembre'];
  $epargnes->new_child("encours_tot",afficheMontant( $encour_tot));
  //total épargne collectée
  $epargne_tot = $data['epargne']['janvier'] + $data['epargne']['fevrier']+$data['epargne']['mars']+$data['epargne']['avril']+$data['epargne']['mai']+$data['epargne']['juin']+$data['epargne']['juillet']+$data['epargne']['aout']+$data['epargne']['septembre']+$data['epargne']['octobre']+$data['epargne']['novembre']+$data['epargne']['decembre'];
  $epargnes->new_child("epargne_tot",afficheMontant( $epargne_tot));
  return $document->dump_mem(true);
}

function get_rapports_journaliers($liste_agence, $date_deb, $date_fin, $select_criter) {
	global $global_id_agence;
  global $global_multidevise;
	$datas = array();
	$data_journalier = array();
	$date = $date_deb;
	while ($date <= $date_fin){
		$data_journalier["jour"] = $date;
		$data_journalier["data"] = get_data_journalier($liste_agence, $date, $select_criter);
		array_push($datas, $data_journalier);
		$date = demain($date);
	}
	return $datas;
}

function get_data_journalier($liste_agence, $date, $select_criter) {
  global $adsys;
  global $global_id_agence;
  global $global_multidevise;
  $val = array();
  $val['tot_new_adh'] = 0;
  $val['tot_defections'] = 0;
  $val['tot_ouv_cptes'] = 0;
  $val['tot_clot_cptes'] = 0;
  $val['tot_DAT_prolong'] = 0;
  $val['tot_DAT_non_prolong'] = 0;
  $val['tot_dem_credit'] = 0;
  $val['tot_credits_app'] = 0;
  $val['tot_credits_rejetes'] = 0;
  $val['tot_credits_annules'] = 0;
  $val['tot_credits_debourse'] =0;
  $val['tot_credits_repris'] = 0;
  $val['tot_part_soc_repris'] = 0;
  $val['tot_ajust_solde_cptes_cli'] = 0;
  $val['total_solde'] = 0;
//$list_agence=getAllIdNomAgence();
  foreach($liste_agence as $key_id_ag =>$value) {
    setGlobalIdAgence($key_id_ag);
    if ($key_id_ag != 0) {
    if ($select_criter["data_cli"]){//Inclure les données des clients
      // Nouvelles adhésions du jour
      $adhArr = getNewAdhesions($key_id_ag, $date);
      $val['tot_new_adh'] += sizeof($adhArr);
      $i=0;
      while (list ($key, $value) = each($adhArr)) {
        $val['adh']['id_agence'][$key_id_ag][$i] = $value['id_ag'];
        $val['adh']['id_client'][$key_id_ag][$i] = $value['id_client'];
        if ($value['statut_juridique'] == 1)
          $val['adh']['nom'][$key_id_ag][$i] = $value['pp_nom'];
        else if ($value['statut_juridique'] == 2)
          $val['adh']['nom'][$key_id_ag][$i] = $value['pm_raison_sociale'];
        else if ($value['statut_juridique'] == 3 || $value['statut_juridique'] == 4)
          $val['adh']['nom'][$key_id_ag][$i] = $value['gi_nom'];
        $val['adh']['statut_juridique'][$key_id_ag][$i] = $value['statut_juridique'];
        $val['adh']['sect_act'][$key_id_ag][$i] = $value['sect_act'];
        $val['adh']['gestionnaire'][$key_id_ag][$i] = $value['gestionnaire'];
        $i++;
      }
      // Nouvelles défections
      $defArr = getNewDefections($key_id_ag, $date);
      $val['tot_defections'] += sizeof($defArr);
      $j=0;
      while (list ($key, $value) = each($defArr)) {
        $val['def']['id_agence'][$key_id_ag][$j] = $value['id_ag'];
        $val['def']['id_client'][$key_id_ag][$j] = $value['id_client'];
        if ($value['statut_juridique'] == 1)
          $val['def']['nom'][$key_id_ag][$j] = $value['pp_nom'];
        else if ($value['statut_juridique'] == 2)
          $val['def']['nom'][$key_id_ag][$j] = $value['pm_raison_sociale'];
        else if ($value['statut_juridique'] == 3 || $value['statut_juridique'] == 4)
          $val['def']['nom'][$key_id_ag][$j] = $value['gi_nom'];
        $val['def']['statut_juridique'][$key_id_ag][$j] = $value['statut_juridique'];
        $val['def']['sect_act'][$key_id_ag][$j] = $value['sect_act'];
        $val['def']['gestionnaire'][$key_id_ag][$j] = $value['gestionnaire'];
        $val['def']['date_adh'][$key_id_ag][$j] = $value['date_adh'];
        $val['def']['etat'][$key_id_ag][$j] = $value['etat'];
        $j++;
      }
    }
    if ($select_criter["data_cpt"]){//Inclure les données des comptes
      // Nouvelles ouvertures de comptes
      $ouvArr = getNewOuvertures($key_id_ag, $date);
      //$val['ouv_cptes'] = sizeof($ouvArr);
      $val['tot_ouv_cptes'] += sizeof($ouvArr);
      $i = 0;
      while (list ($key, $value) = each($ouvArr)) {
        if ($global_multidevise)
          setMonnaieCourante($value["devise"]);
        $val['ouv']['id_agence'][$key_id_ag][$i] = $value['id_ag'];
        $val['ouv']['num_cpte'][$key_id_ag][$i] =  $value['num_complet_cpte'];
        $val['ouv']['id_client'][$key_id_ag][$i] = $value['id_titulaire'];
        $val['ouv']['nom_client'][$key_id_ag][$i] = getNomClient($value['id_titulaire']);
        $val['ouv']['produit_epargne'][$key_id_ag][$i] = $value['id_prod'];
        $val['ouv']['solde'][$key_id_ag][$i] = $value['solde'];
        $i++;
      }

      // Nouvelles clôtures de comptes
      $clotArr = getNewClotures($key_id_ag, $date);
      $val['tot_clot_cptes'] += sizeof($clotArr);
      $i = 0;
      while (list ($key, $value) = each($clotArr)) {
        if ($global_multidevise)
          setMonnaieCourante($value["devise"]);
        $val['clot']['id_agence'][$key_id_ag][$i] = $value['id_ag'];
        $val['clot']['num_cpte'][$key_id_ag][$i] = $value['num_complet_cpte'];
        $val['clot']['id_client'][$key_id_ag][$i] = $value['id_titulaire'];
        $val['clot']['nom_client'][$key_id_ag][$i] = getNomClient($value['id_titulaire']);
        $val['clot']['produit_epargne'][$key_id_ag][$i] = $value['id_prod'];
        $val['clot']['date_ouvert'][$key_id_ag][$i] = $value['date_ouvert'];
        $val['clot']['raison_clot'][$key_id_ag][$i] = $value['raison_clot'];
        $val['clot']['solde_clot'][$key_id_ag][$i] = $value['solde_clot'];
        $i++;
      }

      // DAT dont le propriétaire a décidé de le prolonger durant la journée
      $DATArr = getNewDATDecisionPrise($key_id_ag, $date, true);
      $val['tot_DAT_prolong'] += sizeof($DATArr);
      $i = 0;
      while (list ($key, $value) = each($DATArr)) {
        if ($global_multidevise)
          setMonnaieCourante($value["devise"]);
        $val['prolong']['id_agence'][$key_id_ag][$i] =  $value['id_ag'];
        $val['prolong']['num_cpte'][$key_id_ag][$i] =  $value['num_complet_cpte'];
        $val['prolong']['id_client'][$key_id_ag][$i] = $value['id_titulaire'];
        $val['prolong']['nom_client'][$key_id_ag][$i] = getNomClient($value['id_titulaire']);
        $val['prolong']['produit_epargne'][$key_id_ag][$i] = $value['id_prod'];
        $val['prolong']['solde'][$key_id_ag][$i] = $value['solde'];
        $val['prolong']['terme_initial'][$key_id_ag][$i] = $value['dat_date_fin'];
        // Calcul des intérêts prévus
        $PROD = getProdEpargne($value["id_prod"]);
        $val['prolong']['interets'][$key_id_ag][$i] = $value["solde"] * $PROD["tx_interet"];
        // Calcul de prochain terme
        $dateArr = pg2phpDateBis($value['dat_date_fin']);
        $PROD = getProdEpargne($value['id_prod']);
        $val['prolong']['nouv_mois'][$key_id_ag][$i] = sprintf("%02d", ($dateArr[0] + $PROD['terme']) % 12);
        $val['prolong']['nouv_annee'][$key_id_ag][$i] = $dateArr[2] + floor(($dateArr[0] + $PROD['terme']) / 12);
        $val['prolong']['nouv_jour'][$key_id_ag][$i] = sprintf("%02d", $dateArr[1]);
        $val['prolong']['prochain_terme'][$key_id_ag][$i] = $val['prolong']['nouv_jour'][$key_id_ag][$i] . "/" . $val['prolong']['nouv_mois'][$key_id_ag][$i] . "/" . $val['prolong']['nouv_annee'][$key_id_ag][$i];
        $i++;
      }


      // DAT dont le propriétaire a décidé de ne pas le prolonger durant la journée
      $DATArr = getNewDATDecisionPrise($key_id_ag, $date, false);
      $val['tot_DAT_non_prolong'] += sizeof($DATArr);
      $i = 0;
      while (list ($key, $value) = each($DATArr)) {
        if ($global_multidevise)
          setMonnaieCourante($value["devise"]);
        $val['non_prolong']['id_agence'][$key_id_ag][$i] =  $value['id_ag'];
        $val['non_prolong']['num_cpte'][$key_id_ag][$i] =  $value['num_complet_cpte'];
        $val['non_prolong']['id_client'][$key_id_ag][$i] = $value['id_titulaire'];
        $val['non_prolong']['nom_client'][$key_id_ag][$i] = getNomClient($value['id_titulaire']);
        $val['non_prolong']['produit_epargne'][$key_id_ag][$i] = $value['id_prod'];
        $val['non_prolong']['solde'][$key_id_ag][$i] = $value['solde'];
        $val['non_prolong']['terme_initial'][$key_id_ag][$i] = $value['dat_date_fin'];
        // Calcul des intérêts prévus
        $PROD = getProdEpargne($value["id_prod"]);
        $val['non_prolong']['interets'][$key_id_ag][$i] = $value["solde"] * $PROD["tx_interet"];
        // Calcul du nombre de jours restant avant le terme
        $dateArr = pg2phpDateBis($value['dat_date_fin']);
        $termeTstp = mktime(0, 0, 0, $dateArr[0], $dateArr[1], $dateArr[2]);
        $val['non_prolong']['nbre_jours'][$key_id_ag][$i] = ceil(($termeTstp -time()) / (3600 * 24));
        $i++;
      }
        // Parts sociales reprises dans la journée
      $psArr = getNewPartsReprises($date);
      $val['tot_part_soc_repris'] += sizeof($psArr);
      $i = 0;
      while (list (, $value) = each($psArr)) {
        $DATA_CLI[$key_id_ag] = getClientDatas($value);
        $val['ps']['id_agence'][$key_id_ag][$i] = $DATA_CLI[$key_id_ag]['id_ag'];
        $val['ps']['id_client'][$key_id_ag][$i] = $DATA_CLI[$key_id_ag]['id_client'];
        $val['ps']['nom_client'][$key_id_ag][$i] = getNomClient($DATA_CLI[$key_id_ag]['id_client']);
        $val['ps']['sect_act'][$key_id_ag][$i] = $DATA_CLI[$key_id_ag]['sect_act'];
        $val['ps']['nbre_parts'][$key_id_ag][$i] = $DATA_CLI['nbre_parts'];
        $val['ps']['gestionnaire'][$key_id_ag][$i] = $DATA_CLI[$key_id_ag]['gestionnaire'];
        $i++;
      }

      // Comptes dont le solde a été corrigé durant la journée
      $DATAS = getNewAjustementsSoldes($date);
      $val['tot_ajust_solde_cptes_cli'] += sizeof($DATAS);
      $i = 0;
      while (list ($key, $value) = each($DATAS)) {
        $val['cpt']['id_agence'][$key_id_ag][$i] = $value['id_ag'];
        $val['cpt']['login'][$key_id_ag][$i] = $value['login'];
        $val['cpt']['heure'][$key_id_ag][$i] = $value['heure'];
        $val['cpt']['id_client'][$key_id_ag][$i] = $value['id_client'];
        $val['cpt']['nom_client'][$key_id_ag][$i] = getNomClient($value['id_client']);
        $val['cpt']['num_cpte'][$key_id_ag][$i] = $value['num_cpte'];
        $val['cpt']['anc_solde'][$key_id_ag][$i] = $value['anc_solde'];
        $val['cpt']['nouv_solde'][$key_id_ag][$i] = $value['nouv_solde'];
        $i++;
      }
    }
    if ($select_criter["data_cred"]){//Inclure les données des crédits
      // Dossiers de crédit mis en place dans la journée
      $DCRArr = getNewDCR($key_id_ag, $date);
      //$val['dem_credit'] = sizeof($DCRArr);
      $val['tot_dem_credit'] += sizeof($DCRArr);
      $i = 0;
      while (list ($key, $value) = each($DCRArr)) {
        $val['dem']['id_agence'][$key_id_ag][$i] = $value['id_ag'];
        $val['dem']['id_doss'][$key_id_ag][$i] = $value['id_doss'];
        $val['dem']['id_client'][$key_id_ag][$i] = $value['id_client'];
        $val['dem']['nom_client'][$key_id_ag][$i] = getNomClient($value['id_client']);
        $val['dem']['produit_credit'][$key_id_ag][$i] = $value['id_prod'];
        $val['dem']['montant_demande'][$key_id_ag][$i] = $value['mnt_dem'];
        $val['dem']['duree'][$key_id_ag][$i] = $value['duree_mois'];
        $val['dem']['objet_dem'][$key_id_ag][$i] = $value['obj_dem'];
        $val['dem']['gestionnaire'][$key_id_ag][$i] = $value['id_agent_gest'];
        $i++;
      }

      // Dossiers de crédit approuvés dans la journée
      $DCRArr = getNewApprobDCR($key_id_ag, $date);
      $val['tot_credits_app'] += sizeof($DCRArr);
      $i = 0;
      while (list ($key, $value) = each($DCRArr)) {
        $val['app']['id_agence'][$key_id_ag][$i] = $value['id_ag'];
        $val['app']['id_doss'][$key_id_ag][$i] = $value['id_doss'];
        $val['app']['id_client'][$key_id_ag][$i] = $value['id_client'];
        $val['app']['nom_client'][$key_id_ag][$i] = getNomClient($value['id_client']);
        $val['app']['produit_credit'][$key_id_ag][$i] = $value['id_prod'];
        $val['app']['montant_demande'][$key_id_ag][$i] = $value['mnt_dem'];
        $val['app']['montant_octroye'][$key_id_ag][$i] = $value['cre_mnt_octr'];
        $val['app']['duree'][$key_id_ag][$i] = $value['duree_mois'];
        $val['app']['objet_dem'][$key_id_ag][$i] = $value['obj_dem'];
        $val['app']['gestionnaire'][$key_id_ag][$i] = $value['id_agent_gest'];
        $i++;
      }

      // Dossiers de crédit rejetés dans la journée
      $DCRArr = getNewRejetDCR($key_id_ag, $date);
      $val['tot_credits_rejetes'] += sizeof($DCRArr);
      $i = 0;
      while (list ($key, $value) = each($DCRArr)) {
        $val['rejet']['id_agence'][$key_id_ag][$i] = $value['id_ag'];
        $val['rejet']['id_doss'][$key_id_ag][$i] = $value['id_doss'];
        $val['rejet']['id_client'][$key_id_ag][$i] = $value['id_client'];
        $val['rejet']['nom_client'][$key_id_ag][$i] = getNomClient($value['id_client']);
        $val['rejet']['produit_credit'][$key_id_ag][$i] = $value['id_prod'];
        $val['rejet']['montant_demande'][$key_id_ag][$i] = $value['mnt_dem'];
        $val['rejet']['duree'][$key_id_ag][$i] = $value['duree_mois'];
        $val['rejet']['objet_dem'][$key_id_ag][$i] = $value['obj_dem'];
        $val['rejet']['gestionnaire'][$key_id_ag][$i] = $value['id_agent_gest'];
        $i++;
      }

      // Dossiers de crédit annulés dans la journée
      $DCRArr = getNewAnnuleDCR($key_id_ag, $date);
      $val['tot_credits_annules'] += sizeof($DCRArr);
      $i = 0;
      while (list ($key, $value) = each($DCRArr)) {
        $val['annule']['id_agence'][$key_id_ag][$i] = $value['id_ag'];
        $val['annule']['id_doss'][$key_id_ag][$i] = $value['id_doss'];
        $val['annule']['id_client'][$key_id_ag][$i] = $value['id_client'];
        $val['annule']['nom_client'][$key_id_ag][$i] = getNomClient($value['id_client']);
        $val['annule']['produit_credit'][$key_id_ag][$i] = $value['id_prod'];
        $val['annule']['montant_demande'][$key_id_ag][$i] = $value['mnt_dem'];
        $val['annule']['duree'][$key_id_ag][$i] = $value['duree_mois'];
        $val['annule']['objet_dem'][$key_id_ag][$i] = $value['obj_dem'];
        $val['annule']['gestionnaire'][$key_id_ag][$i] = $value['id_agent_gest'];
        $i++;
      }


      // Dossiers de crédit déboursés dans la journée
      $DCRArr = getNewDebourseDCR($key_id_ag, $date);
      $val['tot_credits_debourse'] += sizeof($DCRArr);
      $i = 0;
      while (list ($key, $value) = each($DCRArr)) {
        $val['deb']['id_agence'][$key_id_ag][$i] = $value['id_ag'];
        $val['deb']['id_doss'][$key_id_ag][$i] = $value['id_doss'];
        $val['deb']['id_client'][$key_id_ag][$i] = $value['id_client'];
        $val['deb']['nom_client'][$key_id_ag][$i] = getNomClient($value['id_client']);
        $val['deb']['produit_credit'][$key_id_ag][$i] = $value['id_prod'];
        $val['deb']['montant_demande'][$key_id_ag][$i] = $value['mnt_dem'];
        $val['deb']['montant_octroye'][$key_id_ag][$i] = $value['cre_mnt_octr'];
        $val['deb']['duree'][$key_id_ag][$i] = $value['duree_mois'];
        $val['deb']['objet_dem'][$key_id_ag][$i] = $value['obj_dem'];
        $val['deb']['gestionnaire'][$key_id_ag][$i] = $value['id_agent_gest'];
        $i++;
      }

      // Dossiers de crédits repris dans la journée
      $crdArr = getNewCreditsRepris($key_id_ag, $date);
      $val['tot_credits_repris'] += sizeof($crdArr);
      $i = 0;
      while (list (, $value) = each($crdArr)) {
        $val['repris']['id_agence'][$key_id_ag][$i] = $key_id_ag;
        $val['repris']['id_client'][$key_id_ag][$i] = $value['id_client'];
        $val['repris']['nom_client'][$key_id_ag][$i] = getNomClient($value['id_client']);
        $val['repris']['id_doss'][$key_id_ag][$i] = $value['id_doss'];
        $val['repris']['cre_etat'][$key_id_ag][$i] = getLibel("adsys_etat_credits", $value['cre_etat']);
        $val['repris']['libel_prod'][$key_id_ag][$i] = getLibel("adsys_produit_credit", $value['id_prod']);
        $val['repris']['montant_octroye'][$key_id_ag][$i] = $value['cre_mnt_octr'];
        $tot_remb[$key_id_ag] = getTotremb($value['id_doss']);
        $tot_restant[$key_id_ag] = getTotrestant($value['id_doss']);
        $val['repris']['cap_remb'][$key_id_ag][$i] = $tot_remb[$key_id_ag]['cap_remb'];
        $val['repris']['int_remb'][$key_id_ag][$i] = $tot_remb[$key_id_ag]['int_remb'];
        $val['repris']['pen_remb'][$key_id_ag][$i] = $tot_remb[$key_id_ag]['pen_remb'];
        $val['repris']['cap_restant'][$key_id_ag][$i] = $tot_restant[$key_id_ag]['cap_rest'];
        $val['repris']['int_restant'][$key_id_ag][$i] = $tot_restant[$key_id_ag]['int_rest'];
        $val['repris']['pen_restant'][$key_id_ag][$i] = $tot_restant[$key_id_ag]['pen_rest'];
        $i++;
      }
    }

    if ($select_criter["data_caiss"]){//Inclure les données des caisses
      // Approvisionnement du jour
      $newApp = getNewApprGui($key_id_ag, $date);
      $val['tot_nbr_caiss_app'] += sizeof($newApp);
      $i = 0;
      while (list ($key_gui, $value) = each($newApp)) {
      	while (list ($key_dev, $val_app) = each($value)) {
	        $val['app_caiss']['id_gui'][$key_id_ag][$i] = $val_app['id_gui'];
	        $val['app_caiss']['libel_gui'][$key_id_ag][$i] = $val_app['libel_gui'];
	        $val['app_caiss']['montant'][$key_id_ag][$i] = $val_app['montant'];
	        $val['app_caiss']['devise'][$key_id_ag][$i] = $val_app['devise'];
	        $i++;
      	}
      }
     // Delestages du jour
      $newDelest = getNewDelestageGui($key_id_ag, $date);
      $val['tot_nbr_caiss_delest'] += sizeof($newDelest);
      $i = 0;
      while (list ($key_gui, $value) = each($newDelest)) {
      	while (list ($key_dev, $val_delest) = each($value)) {
	        $val['delest_caiss']['id_gui'][$key_id_ag][$i] = $val_delest['id_gui'];
	        $val['delest_caiss']['libel_gui'][$key_id_ag][$i] = $val_delest['libel_gui'];
	        $val['delest_caiss']['montant'][$key_id_ag][$i] = $val_delest['montant'];
	        $val['delest_caiss']['devise'][$key_id_ag][$i] = $val_delest['devise'];
	        $i++;
      	}
      }
      // Situation du coffre-fort du jour
      $newSitCof = getSituationCoffr($key_id_ag, $date);
      $val['tot_nbr_dev_coffr'] += sizeof($newSitCof);
      $i = 0;
      while (list ($key_cpt, $value) = each($newSitCof)) {

	        $val['sit_coffr']['solde'][$key_id_ag][$i] = $value['solde'];
	        $val['sit_coffr']['montant_deb'][$key_id_ag][$i] = $value['montant_deb'];
	        $val['sit_coffr']['montant_cred'][$key_id_ag][$i] = $value['montant_cred'];
	        $val['sit_coffr']['devise'][$key_id_ag][$i] = $value['devise'];
	        $i++;
      }

       // Situation des dépenses du jour
      $newSitDep = getNewDep($key_id_ag, $date);
      $val['tot_nbr_dep'] += sizeof($newSitDep);
      $i = 0;
      while (list ($key_cpt, $value) = each($newSitDep)) {

	        $val['sit_dep']['compte'][$key_id_ag][$i] = $value['compte'];
	        $val['sit_dep']['montant'][$key_id_ag][$i] = $value['montant'];
	        $val['sit_dep']['devise'][$key_id_ag][$i] = $value['devise'];
	        $val['sit_dep']['libel_ecriture'][$key_id_ag][$i] = $value['libel_ecriture'];
	        $i++;
      }
    }

    }
  }
  // Ajout du nombre d'agence selectionné dans tableau contenant les statistiques de l'agence ou du réseau
  $val['a_nombreAgence'] = count($liste_agence);
  if ($val['a_nombreAgence'] > 1) {
    resetGlobalIdAgence();
  }

  return $val;
}

/**
 * Fonction qui génère le code XML du rapport journalier
 * @param array $val Tableau contenant les valeurs à placer dans le rapport
 * @param date $date La date pour laquelle le rapport doit être généré
 * @param bool $export_csv Flag à vrai si le rapport doit être sorti au format CSV
 * @return str Chaîne contenant le code XML du rapport
 */
function xml_journalier($list_agence, $data, $date_deb, $date_fin, $select_criter, $export_csv = false) {
  global $adsys;
  global $global_id_agence;
  global $global_multidevise;
  //$list_agence=getAllIdNomAgence();
  // Création racine
  $document = create_xml_doc("journaux", "journalier.dtd");
  //Element root
  $root = $document->root();
  //En-tête généraliste
  gen_header($root, 'AGC-JOU', " du : " . $date_deb." au : " . $date_fin);
  foreach($data as $key_data =>$data_journalier){
  $val = array();
  $date = $data_journalier["jour"];
  $val = $data_journalier["data"];

	$journal = $root->new_child("journalier", "");
	$journal->set_attribute("jour", $date);
  foreach($list_agence as $key_id_ag =>$value) {
    if ($key_id_ag != 0) {
      setGlobalIdAgence($key_id_ag);
      if ($select_criter["data_cli"]){//Inclure les données des clients
	      //Adhésions du jour
	      $adhesions = $journal->new_child("adhesions", "");
	      $adhesions->set_attribute("nombre", $val['tot_new_adh']);
	      for ($i = 0; $i < sizeof($val['adh']['id_client'][$key_id_ag]); $i++) {
	        $detail_adhesion = $adhesions->new_child("detail_adhesion", "");
	        $detail_adhesion->new_child("id_agence", $val['adh']['id_agence'][$key_id_ag][$i]);
	        $detail_adhesion->new_child("id_client", makeNumClient($val['adh']['id_client'][$key_id_ag][$i]));
	        $detail_adhesion->new_child("nom_client", getNomClient($val['adh']['id_client'][$key_id_ag][$i]));
	        $detail_adhesion->new_child("stat_jur", adb_gettext($adsys["adsys_stat_jur"][$val['adh']['statut_juridique'][$key_id_ag][$i]]));
	        $detail_adhesion->new_child("sect_act", getLibel("adsys_sect_activite", $val['adh']['sect_act'][$key_id_ag][$i]));
	        if ($val['adh']['gestionnaire'][$key_id_ag][$i] != '')
	          $detail_adhesion->new_child("gestionnaire", $val['adh']['gestionnaire'][$key_id_ag][$i] . " (" . getNomUtilisateur($val['adh']['gestionnaire'][$key_id_ag][$i]) . ")");
	        else
	          $detail_adhesion->new_child("gestionnaire", " ");
	      }

	      //Défections du jour
	      $defections = $journal->new_child("defections", "");
	      $defections->set_attribute("nombre", $val['tot_defections']);
	      for ($i = 0; $i < sizeof($val['def']['id_client'][$key_id_ag]); $i++) {
	        $detail_defection = $defections->new_child("detail_defection", "");
	        $detail_defection->new_child("id_agence", $val['def']['id_agence'][$key_id_ag][$i]);
	        $detail_defection->new_child("id_client", makeNumClient($val['def']['id_client'][$key_id_ag][$i]));
	        $detail_defection->new_child("nom_client", getNomClient($val['def']['id_client'][$key_id_ag][$i]));
	        $detail_defection->new_child("stat_jur", adb_gettext($adsys["adsys_stat_jur"][$val['def']['statut_juridique'][$key_id_ag][$i]]));
	        $detail_defection->new_child("sect_act", getLibel("adsys_sect_activite", $val['def']['sect_act'][$key_id_ag][$i]));
	        if ($val['def']['gestionnaire'][$key_id_ag][$i] != '')
	          $detail_defection->new_child("gestionnaire", $val['def']['gestionnaire'][$key_id_ag][$i] . " (" . getNomUtilisateur($val['def']['gestionnaire'][$key_id_ag][$i]) . ")");
	        else
	          $detail_defection->new_child("gestionnaire", " ");
	        $detail_defection->new_child("date_adh", pg2phpDate($val['def']['date_adh'][$key_id_ag][$i]));
	        $detail_defection->new_child("raison_defection", adb_gettext($adsys["adsys_etat_client"][$val['def']['etat'][$key_id_ag][$i]]));
	      }
      }
			if ($select_criter["data_cpt"]){//Inclure les données des comptes
	      // Nouvelles ouvertures de comptes
	      $ouvertures = $journal->new_child("ouvertures", "");
	      $ouvertures->set_attribute("nombre", $val['tot_ouv_cptes']);
	      for ($i = 0; $i < sizeof($val['ouv']['id_client'][$key_id_ag]); $i++) {
	        $detail_ouverture = $ouvertures->new_child("detail_ouverture", "");
	        $detail_ouverture->new_child("id_agence", $val['ouv']['id_agence'][$key_id_ag][$i]);
	        $detail_ouverture->new_child("num_cpte", $val['ouv']['num_cpte'][$key_id_ag][$i]);
	        $detail_ouverture->new_child("id_client", makeNumClient($val['ouv']['id_client'][$key_id_ag][$i]));
	        $detail_ouverture->new_child("nom_client", $val['ouv']['nom_client'][$key_id_ag][$i]);
	        $detail_ouverture->new_child("produit_epargne", getLibel("adsys_produit_epargne", $val['ouv']['produit_epargne'][$key_id_ag][$i]));
	        $detail_ouverture->new_child("solde", afficheMontant($val['ouv']['solde'][$key_id_ag][$i], false, $export_csv));
	      }
	      // Nouvelles clotures de comptes
	      $clotures = $journal->new_child("clotures", "");
	      $clotures->set_attribute("nombre", $val['tot_clot_cptes']);
	      for ($i = 0; $i < sizeof($val['clot']['id_client'][$key_id_ag]); $i++) {
	        $detail_cloture = $clotures->new_child("detail_cloture", "");
	        $detail_cloture->new_child("id_agence", $val['clot']['id_agence'][$key_id_ag][$i]);
	        $detail_cloture->new_child("num_cpte", $val['clot']['num_cpte'][$key_id_ag][$i]);
	        $detail_cloture->new_child("id_client", makeNumClient($val['clot']['id_client'][$key_id_ag][$i]));
	        $detail_cloture->new_child("nom_client", $val['clot']['nom_client'][$key_id_ag][$i]);//getNomClient($val['clot']['id_client'][$key_id_ag][$i]));
	        $detail_cloture->new_child("produit_epargne", getLibel("adsys_produit_epargne", $val['clot']['produit_epargne'][$key_id_ag][$i]));
	        $detail_cloture->new_child("date_ouverture", pg2phpDate($val['clot']['date_ouvert'][$key_id_ag][$i]));
	        $detail_cloture->new_child("raison_cloture", adb_gettext($adsys["adsys_raison_cloture"][$val['clot']['raison_clot'][$key_id_ag][$i]]));
	        $detail_cloture->new_child("solde", afficheMontant($val['clot']['solde_clot'][$key_id_ag][$i], false, $export_csv));
	      }

	      // DAT dont le propriétaire a décidé de le prolonger durant la journée
	      $dat_prolonges = $journal->new_child("dat_prolonges", "");
	      $dat_prolonges->set_attribute("nombre", $val['tot_DAT_prolong']);
	      for ($i = 0; $i < sizeof($val['prolong']['id_client'][$key_id_ag]); $i++) {
	        $detail_dat_prolonge = $dat_prolonges->new_child("detail_dat_prolonge", "");
	        $detail_dat_prolonge->new_child("id_agence", $val['prolong']['id_agence'][$key_id_ag][$i]);
	        $detail_dat_prolonge->new_child("num_cpte", $val['prolong']['num_cpte'][$key_id_ag][$i]);
	        $detail_dat_prolonge->new_child("id_client", makeNumClient($val['prolong']['id_client'][$key_id_ag][$i]));
	        $detail_dat_prolonge->new_child("nom_client", $val['prolong']['nom_client'][$key_id_ag][$i]);
	        $detail_dat_prolonge->new_child("produit_epargne", getLibel("adsys_produit_epargne", $val['prolong']['produit_epargne'][$key_id_ag][$i]));
	        $detail_dat_prolonge->new_child("solde", afficheMontant($val['prolong']['solde'][$key_id_ag][$i], false, $export_csv));
	        $detail_dat_prolonge->new_child("terme_initial", pg2phpDate($val['prolong']['terme_initial'][$key_id_ag][$i]));
	        // calcul des intêrets
	        $detail_dat_prolonge->new_child("interets", afficheMontant($val['prolong']['interets'][$key_id_ag][$i], false, $export_csv));
	        // Calcul de prochain terme
	        $detail_dat_prolonge->new_child("prochain_terme", $val['prolong']['prochain_terme'][$key_id_ag][$i]);
	      }

	      // DAT dont le propriétaire a décidé de ne pas le prolonger durant la journée
	      $dat_non_prolonges = $journal->new_child("dat_non_prolonges", "");
	      $dat_non_prolonges->set_attribute("nombre", $val['tot_DAT_non_prolong']);
	      for ($i = 0; $i < sizeof($val['non_prolong']['id_client'][$key_id_ag]); $i++) {
	        $detail_dat_non_prolonge = $dat_non_prolonges->new_child("detail_dat_non_prolonge", "");
	        $detail_dat_non_prolonge->new_child("id_agence", $val['non_prolong']['id_agence'][$key_id_ag][$i]);
	        $detail_dat_non_prolonge->new_child("num_cpte", $val['non_prolong']['num_cpte'][$key_id_ag][$i]);
	        $detail_dat_non_prolonge->new_child("id_client", makeNumClient($val['non_prolong']['id_client'][$key_id_ag][$i]));
	        $detail_dat_non_prolonge->new_child("nom_client", $val['non_prolong']['nom_client'][$key_id_ag][$i]);
	        $detail_dat_non_prolonge->new_child("produit_epargne", getLibel("adsys_produit_epargne", $val['non_prolong']['produit_epargne'][$key_id_ag][$i]));
	        $detail_dat_non_prolonge->new_child("solde", afficheMontant($val['non_prolong']['solde'][$key_id_ag][$i], false, $export_csv));
	        $detail_dat_non_prolonge->new_child("terme", pg2phpDate($val['non_prolong']['terme_initial'][$key_id_ag][$i]));
	        $detail_dat_non_prolonge->new_child("interets", afficheMontant($val['non_prolong']['interets'][$key_id_ag][$i], true));
	        $detail_dat_non_prolonge->new_child("nbre_jours", $val['non_prolong']['nbre_jours'][$key_id_ag][$i]);
	      }

	         // Parts sociales reprises dans la journée
	      $partsreprises = $journal->new_child("ps_repris", "");
	      $partsreprises->set_attribute("nombre", $val['tot_part_soc_repris']);
	      for ($i = 0; $i < sizeof($val['ps']['id_client'][$key_id_ag]); $i++) {
	        $detail_ps_repris = $partsreprises->new_child("detail_ps_repris", "");
	        $detail_ps_repris->new_child("id_agence", $val['ps']['id_agence'][$key_id_ag][$i]);
	        $detail_ps_repris->new_child("id_client", makeNumClient($val['ps']['id_client'][$key_id_ag][$i]));
	        $detail_ps_repris->new_child("nom_client", $val['ps']['nom_client'][$key_id_ag][$i]);
	        $detail_ps_repris->new_child("sect_act", getLibel($val['ps']['sect_act'][$key_id_ag][$i]));
	        $detail_ps_repris->new_child("nbre_parts", $val['ps']['nbre_parts'][$key_id_ag][$i]);
	        if ($val['gestionnaire'][$key_id_ag][$i] != '')
	          $detail_ps_repris->new_child("gestionnaire", $val['ps']['gestionnaire'][$key_id_ag][$i] . " (" . getNomUtilisateur($val['ps']['gestionnaire'][$key_id_ag][$i]) . ")");
	        else
	          $detail_ps_repris->new_child("gestionnaire", "");
	      }
	      // Comptes dont le solde a été corrigé durant la journée
	      $comptes_ajustes = $journal->new_child("comptes_ajustes", "");
	      $comptes_ajustes->set_attribute("nombre", $val['tot_ajust_solde_cptes_cli']);
	      for ($i = 0; $i < sizeof($val['cpt']['id_client'][$key_id_ag]); $i++) {
	        $detail_compte_ajuste = $comptes_ajustes->new_child("detail_compte_ajuste", "");
	        $detail_compte_ajuste->new_child("id_agence", $val['cpt']['id_agence'][$key_id_ag][$i]);
	        $detail_compte_ajuste->new_child("login", $val['cpt']['login'][$key_id_ag][$i]);
	        $detail_compte_ajuste->new_child("heure", $val['cpt']['heure'][$key_id_ag][$i]);
	        $detail_compte_ajuste->new_child("id_client", makeNumClient($val['cpt']['id_client'][$key_id_ag][$i]));
	        $detail_compte_ajuste->new_child("nom_client", $val['cpt']['nom_client'][$key_id_ag][$i]);
	        $detail_compte_ajuste->new_child("num_cpte", $val['cpt']['num_cpte'][$key_id_ag][$i]);
	        $detail_compte_ajuste->new_child("anc_solde", $val['cpt']['anc_solde'][$key_id_ag][$i]);
	        $detail_compte_ajuste->new_child("nouv_solde", $val['cpt']['nouv_solde'][$key_id_ag][$i]);
	      }
			}
			if ($select_criter["data_cred"]){//Inclure les données des crédits
	      // Dossiers de crédit mis en place dans la journée
	      $dossiers_credit = $journal->new_child("dossiers_credit", "");
	      $dossiers_credit->set_attribute("nombre", $val['tot_dem_credit']);
	      $mnt_dem_tot = 0;
	      for ($i = 0; $i < sizeof($val['dem']['id_client'][$key_id_ag]); $i++) {
	        $detail_dossier_credit = $dossiers_credit->new_child("detail_dossier_credit_sans_mnt_octr", "");
	        $detail_dossier_credit->new_child("id_agence", $val['dem']['id_agence'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("id_doss", $val['dem']['id_doss'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("id_client", makeNumClient($val['dem']['id_client'][$key_id_ag][$i]));
	        $detail_dossier_credit->new_child("nom_client", $val['dem']['nom_client'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("produit_credit", getLibel("adsys_produit_credit", $val['dem']['produit_credit'][$key_id_ag][$i]));
	        $detail_dossier_credit->new_child("montant_demande", afficheMontant($val['dem']['montant_demande'][$key_id_ag][$i], false, $export_csv));
	        $mnt_dem_tot += $val['dem']['montant_demande'][$key_id_ag][$i];
	        $detail_dossier_credit->new_child("duree", $val['dem']['duree'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("objet_dem", getLibel("adsys_objets_credits", $val['dem']['objet_dem'][$key_id_ag][$i]));
	        if ($val['id_agent_gest'] != "")
	          $detail_dossier_credit->new_child("gestionnaire", $val['dem']['gestionnaire'][$key_id_ag][$i] .			" (" . getNomUtilisateur($val['dem']['id_agent_gest'][$key_id_ag][$i]) . ")");
	        else
	          $detail_dossier_credit->new_child("gestionnaire", "");
	      }
	      //Totaux des dossiers de credits mis en place
	      if(sizeof($val['dem']['id_client'][$key_id_ag]) > 0){
	        $detail_dossier_credit = $dossiers_credit->new_child("detail_dossier_credit_sans_mnt_octr", "");
	       	$detail_dossier_credit->new_child("id_agence", "TOTAL");
	        $detail_dossier_credit->new_child("id_doss", "");
	        $detail_dossier_credit->new_child("id_client", "");
	        $detail_dossier_credit->new_child("nom_client", "");
	        $detail_dossier_credit->new_child("produit_credit", "");
	        $detail_dossier_credit->new_child("montant_demande", afficheMontant($mnt_dem_tot, false, $export_csv));
	        $detail_dossier_credit->new_child("duree", "");
	        $detail_dossier_credit->new_child("objet_dem", "");
	        $detail_dossier_credit->new_child("gestionnaire", "");

	      }

	      // Dossiers de crédit approuvés dans la journée
	      $dossiers_credit = $journal->new_child("dcr_approuves", "");
	      $dossiers_credit->set_attribute("nombre", $val['tot_credits_app']);
	      $mnt_dem_tot = 0;
	      $mnt_app_tot = 0;
	      for ($i = 0; $i < sizeof($val['app']['id_client'][$key_id_ag]); $i++) {
	        $detail_dossier_credit = $dossiers_credit->new_child("detail_dossier_credit_avec_mnt_octr", "");
	        $detail_dossier_credit->new_child("id_agence", $val['app']['id_agence'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("id_doss", $val['app']['id_doss'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("id_client", makeNumClient($val['app']['id_client'][$key_id_ag][$i]));
	        $detail_dossier_credit->new_child("nom_client", $val['app']['nom_client'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("produit_credit", getLibel("adsys_produit_credit", $val['app']['produit_credit'][$key_id_ag][$i]));
	        $detail_dossier_credit->new_child("montant_demande", afficheMontant($val['app']['montant_demande'][$key_id_ag][$i], false, $export_csv));
	        $detail_dossier_credit->new_child("montant_octroye", afficheMontant($val['app']['montant_octroye'][$key_id_ag][$i], false, $export_csv));
	        $mnt_dem_tot += $val['app']['montant_demande'][$key_id_ag][$i];
	        $mnt_app_tot += $val['app']['montant_octroye'][$key_id_ag][$i];
	        $detail_dossier_credit->new_child("duree", $val['app']['duree'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("objet_dem", getLibel("adsys_objets_credits", $val['app']['objet_dem'][$key_id_ag][$i]));
	        if ($val['id_agent_gest'] != "")
	          $detail_dossier_credit->new_child("gestionnaire", $val['app']['gestionnaire'][$key_id_ag][$i] .			" (" . getNomUtilisateur($val['app']['gestionnaire'][$key_id_ag][$i]) . ")");
	        else
	          $detail_dossier_credit->new_child("gestionnaire", "");
	      }
				 //Totaux des dossiers de credits approuvés
	      if(sizeof($val['app']['id_client'][$key_id_ag]) > 0){
	        $detail_dossier_credit = $dossiers_credit->new_child("detail_dossier_credit_avec_mnt_octr", "");
	       	$detail_dossier_credit->new_child("id_agence", "TOTAL");
	        $detail_dossier_credit->new_child("id_doss", "");
	        $detail_dossier_credit->new_child("id_client", "");
	        $detail_dossier_credit->new_child("nom_client", "");
	        $detail_dossier_credit->new_child("produit_credit", "");
	        $detail_dossier_credit->new_child("montant_demande", afficheMontant($mnt_dem_tot, false, $export_csv));
	        $detail_dossier_credit->new_child("montant_octroye", afficheMontant($mnt_app_tot, false, $export_csv));
	        $detail_dossier_credit->new_child("duree", "");
	        $detail_dossier_credit->new_child("objet_dem", "");
	        $detail_dossier_credit->new_child("gestionnaire", "");
	      }

	      // Dossiers de crédit rejetés dans la journée
	      $dossiers_credit = $journal->new_child("dcr_rejetes", "");
	      $dossiers_credit->set_attribute("nombre", $val['tot_credits_rejetes']);
	      for ($i = 0; $i < sizeof($val['rejet']['id_client'][$key_id_ag]); $i++) {
	        $detail_dossier_credit = $dossiers_credit->new_child("detail_dossier_credit_rejete", "");
	        $detail_dossier_credit->new_child("id_agence", $val['rejet']['id_agence'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("id_doss", $val['rejet']['id_doss'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("id_client", makeNumClient($val['rejet']['id_client'][$key_id_ag][$i]));
	        $detail_dossier_credit->new_child("nom_client", $val['rejet']['nom_client'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("produit_credit", getLibel("adsys_produit_credit", $val['rejet']['produit_credit'][$key_id_ag][$i]));
	        $detail_dossier_credit->new_child("montant_demande", afficheMontant($val['rejet']['montant_demande'][$key_id_ag][$i], false, $export_csv));
	        $detail_dossier_credit->new_child("duree", $val['rejet']['duree'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("objet_dem", getLibel("adsys_objets_credits", $val['rejet']['objet_dem'][$key_id_ag][$i]));
	        if ($val['id_agent_gest'] != "")
	          $detail_dossier_credit->new_child("gestionnaire", $val['rejet']['gestionnaire'][$key_id_ag][$i] .			" (" . getNomUtilisateur($val['rejet']['gestionnaire'][$key_id_ag][$i]) . ")");
	        else
	          $detail_dossier_credit->new_child("gestionnaire", "");
	      }

	      // Dossiers de crédit annulés dans la journée
	      $dossiers_credit = $journal->new_child("dcr_annules", "");
	      $dossiers_credit->set_attribute("nombre", $val['tot_credits_annules']);
	      for ($i = 0; $i < sizeof($val['annule']['id_client'][$key_id_ag]); $i++) {
	        $detail_dossier_credit = $dossiers_credit->new_child("detail_dossier_credit_rejete", "");
	        $detail_dossier_credit->new_child("id_agence", $val['annule']['id_agence'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("id_doss", $val['annule']['id_doss'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("id_client", makeNumClient($val['annule']['id_client'][$key_id_ag][$i]));
	        $detail_dossier_credit->new_child("nom_client", $val['annule']['nom_client'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("produit_credit", getLibel("adsys_produit_credit", $val['annule']['produit_credit'][$key_id_ag][$i]));
	        $detail_dossier_credit->new_child("montant_demande", afficheMontant($val['annule']['montant_demande'][$key_id_ag][$i], false, $export_csv));
	        $detail_dossier_credit->new_child("duree", $val['annule']['duree'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("objet_dem", getLibel("adsys_objets_credits", $val['annule']['objet_dem'][$key_id_ag][$i]));
	        if ($val['id_agent_gest'] != "")
	          $detail_dossier_credit->new_child("gestionnaire", $val['annule']['gestionnaire'][$key_id_ag][$i] .			" (" . getNomUtilisateur($val['annule']['gestionnaire'][$key_id_ag][$i]) . ")");
	        else
	          $detail_dossier_credit->new_child("gestionnaire", "");
	      }
	      // Dossiers de crédit déboursés dans la journée
	      $dossiers_credit = $journal->new_child("dcr_debourses", "");
	      $dossiers_credit->set_attribute("nombre", $val['tot_credits_debourse']);
	      for ($i = 0; $i < sizeof($val['deb']['id_client'][$key_id_ag]); $i++) {
	        $detail_dossier_credit = $dossiers_credit->new_child("detail_dossier_credit_avec_mnt_octr", "");
	        $detail_dossier_credit->new_child("id_agence", $val['deb']['id_agence'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("id_doss", $val['deb']['id_doss'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("id_client", makeNumClient($val['deb']['id_client'][$key_id_ag][$i]));
	        $detail_dossier_credit->new_child("nom_client", $val['deb']['nom_client'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("produit_credit", getLibel("adsys_produit_credit", $val['deb']['produit_credit'][$key_id_ag][$i]));
	        $detail_dossier_credit->new_child("montant_demande", afficheMontant($val['deb']['montant_demande'][$key_id_ag][$i], false, $export_csv));
	        $detail_dossier_credit->new_child("montant_octroye", afficheMontant($val['deb']['montant_octroye'][$key_id_ag][$i], false, $export_csv));
	        $detail_dossier_credit->new_child("duree", $val['deb']['duree'][$key_id_ag][$i]);
	        $detail_dossier_credit->new_child("objet_dem", getLibel("adsys_objets_credits", $val['deb']['objet_dem'][$key_id_ag][$i]));
	        if ($val['id_agent_gest'] != "")
	          $detail_dossier_credit->new_child("gestionnaire", $val['deb']['gestionnaire'][$key_id_ag][$i] .			" (" . getNomUtilisateur($val['id_agent_gest'][$key_id_ag][$i]) . ")");
	        else
	          $detail_dossier_credit->new_child("gestionnaire", "");
	      }

	      // Dossiers de crédits repris dans la journée
	      $creditsrepris = $journal->new_child("dcr_repris", "");
	      $creditsrepris->set_attribute("nombre", $val['tot_credits_repris']);
	      for ($i = 0; $i < sizeof($val['repris']['id_client'][$key_id_ag]); $i++) {
	        $detail_crd_repris = $creditsrepris->new_child("detail_crd_repris", "");
	        $detail_crd_repris->new_child("id_agence", $val['repris']['id_agence'][$key_id_ag][$i]);
	        $detail_crd_repris->new_child("id_client", sprintf("%06d", $val['repris']['id_client'][$key_id_ag][$i]));
	        $detail_crd_repris->new_child("nom_client", $val['repris']['nom_client'][$key_id_ag][$i]);
	        $detail_crd_repris->new_child("id_doss", sprintf("%06d", $val['repris']['id_doss'][$key_id_ag][$i]));
	        $detail_crd_repris->new_child("cre_etat", getLibel("adsys_etat_credits", $val['repris']['cre_etat'][$key_id_ag][$i]));
	        $detail_crd_repris->new_child("libel_prod", getLibel("adsys_produit_credit", $val['repris']['libel_prod'][$key_id_ag][$i]));
	        $detail_crd_repris->new_child("montant_octroye", afficheMontant($val['repris']['montant_octroye'][$key_id_ag][$i], false, $export_csv));
	        $detail_crd_repris->new_child("cap_remb", afficheMontant($val['repris']['cap_remb'][$key_id_ag][$i], false, $export_csv));
	        $detail_crd_repris->new_child("int_remb", afficheMontant($val['repris']['int_remb'][$key_id_ag][$i], false, $export_csv));
	        $detail_crd_repris->new_child("pen_remb", afficheMontant($val['repris']['pen_remb'][$key_id_ag][$i], false, $export_csv));
	        $detail_crd_repris->new_child("cap_restant", afficheMontant($val['repris']['cap_rest'][$key_id_ag][$i], false, $export_csv));
	        $detail_crd_repris->new_child("int_restant", afficheMontant($val['repris']['int_rest'][$key_id_ag][$i], false, $export_csv));
	        $detail_crd_repris->new_child("pen_restant", afficheMontant($val['repris']['pen_rest'][$key_id_ag][$i], false, $export_csv));
	      }
			}
			if ($select_criter["data_caiss"]){//Inclure les données des caisses
	      //Approvisionnement du jour
	      $app_caisses = $journal->new_child("app_caisses", "");
	      $app_caisses->set_attribute("nombre", $val['tot_nbr_caiss_app']);
	      for ($i = 0; $i < sizeof($val['app_caiss']['id_gui'][$key_id_ag]); $i++) {
	        $detail_app_caisses = $app_caisses->new_child("detail_app_caisses", "");
	        $detail_app_caisses->new_child("id_gui", $val['app_caiss']['id_gui'][$key_id_ag][$i]);
	        $detail_app_caisses->new_child("libel_gui", $val['app_caiss']['libel_gui'][$key_id_ag][$i]);
	        $detail_app_caisses->new_child("montant",  afficheMontant($val['app_caiss']['montant'][$key_id_ag][$i], false, $export_csv));
	        $detail_app_caisses->new_child("devise", $val['app_caiss']['devise'][$key_id_ag][$i]);
	      }

	      //Delestages du jour
	      $delest_caisses = $journal->new_child("delest_caisses", "");
	      $delest_caisses->set_attribute("nombre", $val['tot_nbr_caiss_delest']);
	      for ($i = 0; $i < sizeof($val['delest_caiss']['id_gui'][$key_id_ag]); $i++) {
	        $detail_delest_caisses = $delest_caisses->new_child("detail_delest_caisses", "");
	        $detail_delest_caisses->new_child("id_gui", $val['delest_caiss']['id_gui'][$key_id_ag][$i]);
	        $detail_delest_caisses->new_child("libel_gui", $val['delest_caiss']['libel_gui'][$key_id_ag][$i]);
	        $detail_delest_caisses->new_child("montant",  afficheMontant($val['delest_caiss']['montant'][$key_id_ag][$i], false, $export_csv));
	        $detail_delest_caisses->new_child("devise", $val['delest_caiss']['devise'][$key_id_ag][$i]);
	      }

	       //Situation du coffre-fort du jour
	      $situation_coffre = $journal->new_child("situation_coffre", "");
	      $situation_coffre->set_attribute("nombre", $val['tot_nbr_dev_coffr']);
	      for ($i = 0; $i < sizeof($val['sit_coffr']['solde'][$key_id_ag]); $i++) {
	        $detail_situation_coffre = $situation_coffre->new_child("detail_situation_coffre", "");
	        $detail_situation_coffre->new_child("solde", afficheMontant($val['sit_coffr']['solde'][$key_id_ag][$i], false, $export_csv));
	        $detail_situation_coffre->new_child("montant_deb", afficheMontant($val['sit_coffr']['montant_deb'][$key_id_ag][$i], false, $export_csv));
	        $detail_situation_coffre->new_child("montant_cred", afficheMontant($val['sit_coffr']['montant_cred'][$key_id_ag][$i], false, $export_csv));
	        $detail_situation_coffre->new_child("devise", $val['sit_coffr']['devise'][$key_id_ag][$i]);
	      }

	      //Situation des dépenses du jour
	      $situation_dep = $journal->new_child("situation_dep", "");
	      $situation_dep->set_attribute("nombre", $val['tot_nbr_dep']);
	      for ($i = 0; $i < sizeof($val['sit_dep']['compte'][$key_id_ag]); $i++) {
	        $detail_situation_dep = $situation_dep->new_child("detail_situation_dep", "");
	        $detail_situation_dep->new_child("libel_ecriture", $val['sit_dep']['libel_ecriture'][$key_id_ag][$i]);
	        $detail_situation_dep->new_child("montant", afficheMontant($val['sit_dep']['montant'][$key_id_ag][$i], false, $export_csv));
	        $detail_situation_dep->new_child("devise", $val['sit_dep']['devise'][$key_id_ag][$i]);
	      }
      }
    }
  }
	}
   //Liste des agences consolidées
  if (isSiege()) {
   $list_agence=getListAgenceConsolide();
   foreach($list_agence as $id_ag =>$data_agence) {
     $enreg_agence=$root->new_child("enreg_agence","");
     $enreg_agence->new_child("is_siege", "true");
     $enreg_agence->new_child("id_ag", $data_agence['id']);
     $enreg_agence->new_child("libel_ag", $data_agence['libel']);
     $enreg_agence->new_child("date_max", $data_agence['date_dernier_mouv']);
   }
  }else{
  	 $enreg_agence=$root->new_child("enreg_agence","");
  	 $enreg_agence->new_child("is_siege", "false");
  }

  return $document->dump_mem(true);
}

function get_activite_agence($list_agence, $debut_periode, $fin_periode, $annee) {
  global $global_id_agence;
  global $global_id_client;
  global $global_multidevise;
  global $adsys;
  global $dbHandler;
  //Inialisation du tableau de retour
  $data['epargne']['janvier'] = 0;
  $data['epargne']['fevrier'] = 0;
  $data['epargne']['mars'] = 0;
  $data['epargne']['avril'] = 0;
  $data['epargne']['mai'] = 0;
  $data['epargne']['juin'] = 0;
  $data['epargne']['juillet'] = 0;
  $data['epargne']['aout'] = 0;
  $data['epargne']['septembre'] = 0;
  $data['epargne']['octobre'] = 0;
  $data['epargne']['novembre'] = 0;
  $data['epargne']['decembre'] = 0;
  $data['encours']['janvier'] = 0;
  $data['encours']['fevrier'] = 0;
  $data['encours']['mars'] = 0;
  $data['encours']['avril'] = 0;
  $data['encours']['mai'] = 0;
  $data['encours']['juin'] = 0;
  $data['encours']['juillet'] = 0;
  $data['encours']['aout'] = 0;
  $data['encours']['septembre'] = 0;
  $data['encours']['octobre'] = 0;
  $data['encours']['novembre'] = 0;
  $data['encours']['decembre'] = 0;
  //Montant crédit octroyé
  $data['credit']['janvier']['montant_tot'] = 0;
  $data['credit']['fevrier']['montant_tot'] = 0;
  $data['credit']['mars']['montant_tot'] = 0;
  $data['credit']['avril']['montant_tot'] = 0;
  $data['credit']['mai']['montant_tot'] = 0;
  $data['credit']['juin']['montant_tot'] = 0;
  $data['credit']['juillet']['montant_tot'] = 0;
  $data['credit']['aout']['montant_tot'] = 0;
  $data['credit']['septembre']['montant_tot'] = 0;
  $data['credit']['octobre']['montant_tot']= 0;
  $data['credit']['novembre']['montant_tot'] = 0;
  $data['credit']['decembre']['montant_tot'] = 0;
  $data['credit']['janvier']['nbre_credit'] = 0;
  $data['credit']['fevrier']['nbre_credit'] = 0;
  $data['credit']['mars']['nbre_credit'] = 0;
  $data['credit']['avril']['nbre_credit'] = 0;
  $data['credit']['mai']['nbre_credit'] = 0;
  $data['credit']['juin']['nbre_credit'] = 0;
  $data['credit']['juillet']['nbre_credit'] = 0;
  $data['credit']['aout']['nbre_credit'] = 0;
  $data['credit']['septembre']['nbre_credit'] = 0;
  $data['credit']['octobre']['nbre_credit']= 0;
  $data['credit']['novembre']['nbre_credit'] = 0;
  $data['credit']['decembre']['nbre_credit'] = 0;
  //Répartion clients
  $data['client_deb']['homme'] = 0;
  $data['client_deb']['femme'] = 0;
  $data['client_fin']['homme'] = 0;
  $data['client_fin']['femme'] = 0;
  $data['client_deb']['pm'] = 0;
  $data['client_fin']['pm'] = 0;

  //Parcours des agences
  foreach($list_agence as $key_id_ag =>$value) { // Parcours des agences
    setGlobalIdAgence($key_id_ag); //pour travailler avec l'agence en question
    if (Bissextile($annee)) {
      $fevrier = getNbreCreditsMois(date("01/02/$annee"), date("29/02/$annee"));
      $data['credit']['fevrier']['nbre_credit'] += $fevrier['nbre_credit'];
      $data['credit']['fevrier']['montant_tot'] += $fevrier['montant_tot'];
      $data['epargne']['fevrier'] += getEpargneCollectee(date("01/02/$annee"), date("29/02/$annee"));
      $data['encours']['fevrier'] += getEncoursEpargne(date("29/02/$annee"));
    } else {
      $data['epargne']['fevrier'] += getEpargneCollectee(date("01/02/$annee"), date("28/02/$annee"));
      $data['encours']['fevrier'] += getEncoursEpargne(date("28/02/$annee"));
      $fevrier = getNbreCreditsMois(date("01/02/$annee"),date("28/02/$annee"));
      $data['credit']['fevrier']['nbre_credit'] += $fevrier['nbre_credit'];
      $data['credit']['fevrier']['montant_tot'] += $fevrier['montant_tot'];
    }
    $data['epargne']['janvier'] += getEpargneCollectee(date("01/01/$annee"), date("31/01/$annee"));
    $data['epargne']['mars'] += getEpargneCollectee(date("01/03/$annee"), date("31/03/$annee"));
    $data['epargne']['avril'] += getEpargneCollectee(date("01/04/$annee"), date("30/04/$annee"));
    $data['epargne']['mai'] += getEpargneCollectee(date("01/05/$annee"), date("31/05/$annee"));
    $data['epargne']['juin'] = getEpargneCollectee(date("01/06/$annee"), date("30/06/$annee"));
    $data['epargne']['juillet'] += getEpargneCollectee(date("01/07/$annee"), date("31/07/$annee"));
    $data['epargne']['aout'] += getEpargneCollectee(date("01/08/$annee"), date("31/08/$annee"));
    $data['epargne']['septembre'] += getEpargneCollectee(date("01/09/$annee"), date("30/09/$annee"));
    $data['epargne']['octobre'] += getEpargneCollectee(date("01/10/$annee"), date("31/10/$annee"));
    $data['epargne']['novembre'] += getEpargneCollectee(date("01/11/$annee"), date("30/11/$annee"));
    $data['epargne']['decembre'] += getEpargneCollectee(date("01/12/$annee"), date("31/12/$annee"));
    //encours epargne
    $data['encours']['janvier'] += getEncoursEpargne(date("31/01/$annee"));
    $data['encours']['mars'] += getEncoursEpargne(date("31/03/$annee"));
    $data['encours']['avril'] += getEncoursEpargne(date("30/04/$annee"));
    $data['encours']['mai'] += getEncoursEpargne(date("31/05/$annee"));
    $data['encours']['juin'] += getEncoursEpargne(date("30/06/$annee"));
    $data['encours']['juillet'] += getEncoursEpargne(date("31/07/$annee"));
    $data['encours']['aout'] += getEncoursEpargne(date("31/08/$annee"));
    $data['encours']['septembre'] += getEncoursEpargne(date("30/09/$annee"));
    $data['encours']['octobre'] += getEncoursEpargne(date("31/10/$annee"));
    $data['encours']['novembre'] += getEncoursEpargne(date("30/11/$annee"));
    $data['encours']['decembre'] += getEncoursEpargne(date("31/12/$annee"));
    //credits
    $janvier = getNbreCreditsMois(date("01/01/$annee"), date("31/01/$annee"));
    $data['credit']['janvier']['nbre_credit'] += $janvier['nbre_credit'];
    $mars = getNbreCreditsMois(date("01/03/$annee"), date("31/03/$annee"));
    $data['credit']['mars']['nbre_credit'] += $mars['nbre_credit'];
    $avril = getNbreCreditsMois(date("01/04/$annee"), date("30/04/$annee"));
    $data['credit']['avril']['nbre_credit'] += $avril['nbre_credit'];
    $mai = getNbreCreditsMois(date("01/05/$annee"), date("31/05/$annee"));
    $data['credit']['mai']['nbre_credit'] += $mai['nbre_credit'];
    $juin = getNbreCreditsMois(date("01/06/$annee"), date("30/06/$annee"));
    $data['credit']['juin']['nbre_credit'] += $juin['nbre_credit'];
    $juillet = getNbreCreditsMois(date("01/07/$annee"), date("31/07/$annee"));
    $data['credit']['juillet']['nbre_credit'] += $juillet['nbre_credit'];
    $aout = getNbreCreditsMois(date("01/08/$annee"), date("31/08/$annee"));
    $data['credit']['aout']['nbre_credit'] += $aout['nbre_credit'];
    $septembre = getNbreCreditsMois(date("01/09/$annee"), date("30/09/$annee"));
    $data['credit']['septembre']['nbre_credit'] += $septembre['nbre_credit'];
    $octobre = getNbreCreditsMois(date("01/10/$annee"), date("31/10/$annee"));
    $data['credit']['octobre']['nbre_credit'] += $octobre['nbre_credit'];
    $novembre = getNbreCreditsMois(date("01/11/$annee"), date("30/11/$annee"));
    $data['credit']['novembre']['nbre_credit'] += $novembre['nbre_credit'];
    $decembre = getNbreCreditsMois(date("01/12/$annee"), date("31/12/$annee"));
    $data['credit']['decembre']['nbre_credit'] += $decembre['nbre_credit'];
    $data['credit']['janvier']['montant_tot'] += $janvier['montant_tot'];
    $data['credit']['mars']['montant_tot'] += $mars['montant_tot'];
    $data['credit']['avril']['montant_tot'] += $avril['montant_tot'];
    $data['credit']['mai']['montant_tot'] += $mai['montant_tot'];
    $data['credit']['juin']['montant_tot'] += $juin['montant_tot'];
    $data['credit']['juillet']['montant_tot'] += $juillet['montant_tot'];
    $data['credit']['aout']['montant_tot'] += $aout['montant_tot'];
    $data['credit']['septembre']['montant_tot'] += $septembre['montant_tot'];
    $data['credit']['octobre']['montant_tot'] += $octobre['montant_tot'];
    $data['credit']['novembre']['montant_tot'] += $novembre['montant_tot'];
    $data['credit']['decembre']['montant_tot'] += $decembre['montant_tot'];
    //clients
    $client_deb = array();
    $client_deb = get_Clients_Actifs($debut_periode);
    $client_fin = array();
    $client_fin = get_Clients_Actifs($fin_periode);
    $data['client_deb']['homme'] += $client_deb['clients']['homme'];
    $data['client_deb']['femme'] += $client_deb['clients']['femme'];
    $data['client_deb']['pm'] += $client_deb['clients']['pm'];
    $data['client_fin']['homme'] += $client_fin['clients']['homme'];
    $data['client_fin']['femme'] += $client_fin['clients']['femme'];
    $data['client_fin']['pm'] += $client_fin['clients']['pm'];

    resetGlobalIdAgence();
  }//fin parcours agences

    return $data;
}

/**
 * Génère le code XML du rapport de batch
 * @param date $date Date d'exécution du batch
 * @param array $ordres_traites Données concernant les ordres permanents traités
 * @return str Le code XML du rapport
 */
function xml_batch($date, $liste_cli_arch, $liste_comptes_arretes, $liste_DAT, $liste_CAT, $liste_es, $liste_rembourse_auto, $liste_credits_declasses, $liste_frais_tenue, $liste_int_deb, $transaction_ferlo, $ordres_traites,$soldeComptaSoldeInterneCredit,$soldeCreditSoldeInterneCredit) {
  global $adsys;
  global $global_id_agence;
  global $global_multidevise;
  // Création racine
  $document = create_xml_doc("batch", "batch.dtd");
  //Element root
  $root = $document->root();
  //En-tête généraliste
  gen_header($root, 'AGC-BAT', " : " . $date);
  /*  Archivage clients */
  $archivage = $root->new_child("archivage", "");
  $archivage->set_attribute("nombre", sizeof($liste_cli_arch));
  while (list (, $value) = each($liste_cli_arch))
    if ($value != '') {
      $DATA_CLI = getClientDatas($value);
      $detail_archivage = $archivage->new_child("detail_archivage", "");
      $detail_archivage->new_child("id_client", makeNumClient($DATA_CLI['id_client']));
      $detail_archivage->new_child("nom_client", getNomClient($value));
      $detail_archivage->new_child("date_adh", $DATA_CLI['date_adh']);
    }
  /* Comptes arrêtés */
  $comptes_arretes = $root->new_child("comptes_arretes", "");
  $comptes_arretes->set_attribute("nombre", sizeof($liste_comptes_arretes));
  while (list ($key, $value) = each($liste_comptes_arretes))
    if ($value['cpt_vers_int'] != '') {
      $DATA_CPT = getAccountDatas($value['cpt_vers_int']);
    if ($global_multidevise)
        setMonnaieCourante($DATA_CPT["devise"]);
      $detail_comptes_arretes = $comptes_arretes->new_child("detail_comptes_arretes", "");
      $detail_comptes_arretes->new_child("id_cpte", $value['num_complet_cpte']);
      $detail_comptes_arretes->new_child("nom_client", getNomClient($value['id_titulaire']));
      $detail_comptes_arretes->new_child("solde", afficheMontant($value['solde'], true));
      $detail_comptes_arretes->new_child("montant_interets", afficheMontant($value['interets'], true));
      $detail_comptes_arretes->new_child("compte_ben", $DATA_CPT['num_complet_cpte']);
    }
  /*  CAT et DAT arrivés à échéance */
  $nombre = sizeof($liste_DAT) + sizeof($liste_CAT) + sizeof($liste_es);
  $cat_dat_echeance = $root->new_child("cat_dat_echeance", "");
  $cat_dat_echeance->set_attribute("nombre", $nombre);
  /* liste des DAT */
  if (is_array($liste_DAT))
    while (list ($key, $value) = each($liste_DAT)) {
      $DATA_CPT = getAccountDatas($key);
      if ($global_multidevise)
        setMonnaieCourante($DATA_CPT["devise"]);
      $detail_cat_dat_echenace = $cat_dat_echeance->new_child("detail_cat_dat_echeance", "");
      $detail_cat_dat_echenace->new_child("id_cpte", $DATA_CPT['num_complet_cpte']);
      $detail_cat_dat_echenace->new_child("nom_client", getNomClient($DATA_CPT['id_titulaire']));
      $detail_cat_dat_echenace->new_child("solde", afficheMontant($value['solde_cloture'], true));
      $detail_cat_dat_echenace->new_child("action", $value['action']);
      $detail_cat_dat_echenace->new_child("date_ouv", $DATA_CPT['date_ouvert']);
      $detail_cat_dat_echenace->new_child("destination", $value['destination']);
    }
  /*  liste des CAT */
  if (is_array($liste_CAT))
    while (list ($key, $value) = each($liste_CAT)) {
      $DATA_CPT = getAccountDatas($key);
      if ($global_multidevise)
        setMonnaieCourante($DATA_CPT["devise"]);
      $detail_cat_dat_echenace = $cat_dat_echeance->new_child("detail_cat_dat_echeance", "");
      $detail_cat_dat_echenace->new_child("id_cpte", $DATA_CPT['num_complet_cpte']);
      $detail_cat_dat_echenace->new_child("nom_client", getNomClient($DATA_CPT['id_titulaire']));
      $detail_cat_dat_echenace->new_child("solde", afficheMontant($value['solde_cloture'], true));
      $detail_cat_dat_echenace->new_child("action", $value['action']);
      $detail_cat_dat_echenace->new_child("date_ouv", $DATA_CPT['date_ouvert']);
      $detail_cat_dat_echenace->new_child("destination", $value['destination']);
    }
   /*  liste des CAT */
  if (is_array($liste_es))
    while (list ($key, $value) = each($liste_es)) {
      $DATA_CPT = getAccountDatas($key);
      if ($global_multidevise)
        setMonnaieCourante($DATA_CPT["devise"]);
      $detail_cat_dat_echenace = $cat_dat_echeance->new_child("detail_cat_dat_echeance", "");
      $detail_cat_dat_echenace->new_child("id_cpte", $DATA_CPT['num_complet_cpte']);
      $detail_cat_dat_echenace->new_child("nom_client", getNomClient($DATA_CPT['id_titulaire']));
      $detail_cat_dat_echenace->new_child("solde", afficheMontant($value['solde_cloture'], true));
      $detail_cat_dat_echenace->new_child("action", $value['action']);
      $detail_cat_dat_echenace->new_child("date_ouv", $DATA_CPT['date_ouvert']);
      $detail_cat_dat_echenace->new_child("destination", $value['destination']);
    }
  /* Prélèvement frais de tenue de compte */
  $frais_tenue_cpte = $root->new_child("frais_tenue_cpte", "");
  $frais_tenue_cpte->set_attribute("nombre", sizeof($liste_frais_tenue));
  $i = 1;
  while (list ($key, $value) = each($liste_frais_tenue)) {
    if ($global_multidevise)
      setMonnaieCourante($value['devise_frais']);
    /* Diviser le rapport en deux colonnes */
    $j = $i % 2;
    if ($j != 0) {
      $detail_frais_tenue_cpte = $frais_tenue_cpte->new_child("detail_frais_tenue_cpte", "");
      $detail_frais_tenue_cpte->new_child("num_cpte1", $value['num_cpte_frais']);
      $detail_frais_tenue_cpte->new_child("num_client1", makeNumClient($value['id_titulaire_frais']));
      $detail_frais_tenue_cpte->new_child("solde1", afficheMontant($value['solde_initial_frais'], true));
      $detail_frais_tenue_cpte->new_child("frais1", afficheMontant($value['interet_frais'], true));
    } else {
      $detail_frais_tenue_cpte->new_child("num_cpte2", $value['num_cpte_frais']);
      $detail_frais_tenue_cpte->new_child("num_client2", makeNumClient($value['id_titulaire_frais']));
      $detail_frais_tenue_cpte->new_child("solde2", afficheMontant($value['solde_initial_frais'], true));
      $detail_frais_tenue_cpte->new_child("frais2", afficheMontant($value['interet_frais'], true));
    }
    $i++;
  }
  /* Prélèvement intérêts débiteurs */
  $interets_debiteurs = $root->new_child("interets_debiteurs", "");
  $interets_debiteurs->set_attribute("nombre", sizeof($liste_int_deb));
  $i = 1;
  while (list ($key, $value) = each($liste_int_deb)) {
    if ($global_multidevise)
      setMonnaieCourante($value['devise_deb']);
    /* Diviser le rapport en deux colonnes */
    $j = $i % 2;
    if ($j != 0) {
      $detail_interets_debiteurs = $interets_debiteurs->new_child("detail_interets_debiteurs", "");
      $detail_interets_debiteurs->new_child("num_cpte1", $value['num_cpte_deb']);
      $detail_interets_debiteurs->new_child("id_client1", makeNumClient($value['id_titulaire_deb']));
      $detail_interets_debiteurs->new_child("solde1", afficheMontant($value['solde_initial_deb'], true));
      $detail_interets_debiteurs->new_child("frais1", afficheMontant($value['interet_deb'], true));
    } else {
      $detail_interets_debiteurs->new_child("num_cpte2", $value['num_cpte_deb']);
      $detail_interets_debiteurs->new_child("id_client2", makeNumClient($value['id_titulaire_deb']));
      $detail_interets_debiteurs->new_child("solde2", afficheMontant($value['solde_initial_deb'], true));
      $detail_interets_debiteurs->new_child("frais2", afficheMontant($value['interet_deb'], true));
    }
    $i++;
  }

  /* Ordres permanents */
  $ordres_permanents = $root->new_child("ordres_permanents", "");
  $ordres_permanents->set_attribute("nombre", sizeof($ordres_traites));
  $i = 1;
  foreach ($ordres_traites as $ordre) {
    $detail_ordres_permanents = $ordres_permanents->new_child("detail_ordres_permanents", "");
    $num_cpte_src = getnumcptecomplet($ordre['num_cpte_src']); 
 	  $detail_ordres_permanents->new_child("num_cpte_src", $num_cpte_src); 
 	  $num_cpte_dest = getnumcptecomplet($ordre['num_cpte_dest']); 
 	  $detail_ordres_permanents->new_child("num_cpte_dest", $num_cpte_dest);
    $detail_ordres_permanents->new_child("montant", afficheMontant($ordre['montant'], true));
    $detail_ordres_permanents->new_child("frais", afficheMontant($ordre['frais'], true));
    $detail_ordres_permanents->new_child("periodicite", $ordre['periodicite']);
    $detail_ordres_permanents->new_child("intervale", $ordre['intervale']);
    $detail_ordres_permanents->new_child("statut", $ordre['statut']);
    $i++;
  }

  /* Rembouresement automatique d'échéance */
  $rembourse_auto = $root->new_child("rembourse_auto", "");
  $rembourse_auto->set_attribute("nombre", sizeof($liste_rembourse_auto));
  if (is_array($liste_rembourse_auto))
    while (list ($key, $value) = each($liste_rembourse_auto))
      if ($value['cre_id_cpte'] != '') {
        /* Infos du compte associé au crédit */
        $DATA_CPT = getAccountDatas($value['cre_id_cpte']);
        if ($global_multidevise)
          setMonnaieCourante($DATA_CPT["devise"]);
        $detail_rembourse_auto = $rembourse_auto->new_child("detail_rembourse_auto", "");
        $detail_rembourse_auto->new_child("id_doss", $value['id_doss']);
        $detail_rembourse_auto->new_child("id_ech", $value['id_ech']);
        $detail_rembourse_auto->new_child("id_client", makeNumClient($value['id_client']));
        $detail_rembourse_auto->new_child("nom_client", getNomClient($value['id_client']));
        $detail_rembourse_auto->new_child("compte", $DATA_CPT['num_complet_cpte']);
        $detail_rembourse_auto->new_child("cap", afficheMontant($value['solde_cap'], true));
        $detail_rembourse_auto->new_child("int", afficheMontant($value['solde_int'], true));
        $detail_rembourse_auto->new_child("pen", afficheMontant($value['solde_pen'], true));
        $detail_rembourse_auto->new_child("tot", afficheMontant($value['solde_cap'] + $value['solde_int'] + $value['solde_pen'], true));
      }
  /*  Déclassement de crédit */
  $declasse_credit = $root->new_child("declasse_credit", "");
  $declasse_credit->set_attribute("nombre", sizeof($liste_credits_declasses));
  while (list ($key, $value) = each($liste_credits_declasses)) {
    $DATA_DCR = getDossierCrdtInfo($value['id_doss']);
    if ($global_multidevise)
      setMonnaieCourante($DATA_DCR["devise"]);
    $detail_declasse_credit = $declasse_credit->new_child("detail_declasse_credit", "");
    $detail_declasse_credit->new_child("id_doss", $DATA_DCR['id_doss']);
    $detail_declasse_credit->new_child("id_client", makeNumClient($DATA_DCR['id_client']));
    $detail_declasse_credit->new_child("nom_client", getNomClient($DATA_DCR['id_client']));
    $detail_declasse_credit->new_child("solde", afficheMontant($value['solde'], true));
    $detail_declasse_credit->new_child("ancien", $value['etat_courant']);
    $detail_declasse_credit->new_child("nouveau", $value['etat_nouveau']);
  }
  $transactions = $root->new_child("transaction_ferlo", "");
  $transactions->set_attribute("nombre", sizeof($transaction_ferlo));
  while (list ($key, $value) = each($transaction_ferlo)) {
    $detail_transaction = $transactions->new_child("detail_transaction_ferlo", "");
    if ($value['typeTransaction'] == '01')
      $detail_transaction->new_child("type", _('Retrait'));
    elseif ($value['typeTransaction'] == '02') $detail_transaction->new_child("type", _('Dépôt'));
    elseif ($value['typeTransaction'] == '03') $detail_transaction->new_child("type", _('Payement'));
    $detail_transaction->new_child("compte", $value['compteTransaction']);
    $detail_transaction->new_child("montant", $value["montant"]);
  }
 // $soldeComptaSoldeInterneCredit,$soldeCreditSoldeInterneCredit
  $elt_coherence_compta_cpte_interne_credit = $root->new_child("coherence_compta_cpte_interne_credit", "");
  $elt_coherence_compta_cpte_interne_credit->set_attribute("nombre", sizeof($soldeComptaSoldeInterneCredit));
  while (list ($key, $value) = each($soldeComptaSoldeInterneCredit)) {
  	$detail_coherence_compta_cpte_interne_credit = $elt_coherence_compta_cpte_interne_credit->new_child("detail_coherence_compta_cpte_interne_credit", "");
    $detail_coherence_compta_cpte_interne_credit->new_child("id_doss", $value['id_doss']);
    $detail_coherence_compta_cpte_interne_credit->new_child("compte",$value['num_cpte']);
    $detail_coherence_compta_cpte_interne_credit->new_child("solde_cpt", $value['solde_cpte_interne']);
    $detail_coherence_compta_cpte_interne_credit->new_child("solde_cap_compta", $value['solde_cap']);
    $detail_coherence_compta_cpte_interne_credit->new_child("solde_diff", $value["solde_cap"] - $value['solde_cpte_interne']);
  }
  $elt_coherence_cap_restant_cpte_interne_credit = $root->new_child("coherence_cap_restant_cpte_interne_credit", "");
  $elt_coherence_cap_restant_cpte_interne_credit->set_attribute("nombre", sizeof($soldeCreditSoldeInterneCredit));
  while (list ($key, $value) = each($soldeCreditSoldeInterneCredit)) {
  	$detail_coherence_cap_restant_cpte_interne_credit = $elt_coherence_cap_restant_cpte_interne_credit->new_child("detail_coherence_cap_restant_cpte_interne_credit", "");
    $detail_coherence_cap_restant_cpte_interne_credit->new_child("id_doss", $value['id_doss']);
    $detail_coherence_cap_restant_cpte_interne_credit->new_child("compte",$value['num_cpte']);
    $detail_coherence_cap_restant_cpte_interne_credit->new_child("solde_cpt", $value['solde_cpte_interne']);
    $detail_coherence_cap_restant_cpte_interne_credit->new_child("solde_cap_compta", $value['solde_cap']);
    $detail_coherence_cap_restant_cpte_interne_credit->new_child("solde_diff", $value["solde_cap"] - $value['solde_cpte_interne']);
  }
  return $document->dump_mem(true);
}

function gen_xml_previsions($data_previsions, &$xml_node, $export_csv = false) {
  $previsions = $xml_node->new_child("previsions", "");
  $previsions->new_child("j", afficheMontant($data_previsions["j"], false, $export_csv));
  $previsions->new_child("s1", afficheMontant($data_previsions["s1"], false, $export_csv));
  $previsions->new_child("s2", afficheMontant($data_previsions["s2"], false, $export_csv));
  $previsions->new_child("s3", afficheMontant($data_previsions["s3"], false, $export_csv));
  $previsions->new_child("m1", afficheMontant($data_previsions["m1"], false, $export_csv));
  $previsions->new_child("m2", afficheMontant($data_previsions["m2"], false, $export_csv));
  $previsions->new_child("m3", afficheMontant($data_previsions["m3"], false, $export_csv));
  $previsions->new_child("m6", afficheMontant($data_previsions["m6"], false, $export_csv));
  $previsions->new_child("m9", afficheMontant($data_previsions["m9"], false, $export_csv));
  $previsions->new_child("m12", afficheMontant($data_previsions["m12"], false, $export_csv));

}

function get_data_prevision($list_agence, $devise = NULL) {
  global $adsys;
  global $global_id_agence;
  global $global_multidevise;
  $val = array();
  $val['cap_attendu']['j'] = 0;
  $val['cap_attendu']['s1'] = 0;
  $val['cap_attendu']['s2'] = 0;
  $val['cap_attendu']['s3'] = 0;
  $val['cap_attendu']['m1'] = 0;
  $val['cap_attendu']['m2'] = 0;
  $val['cap_attendu']['m3'] = 0;
  $val['cap_attendu']['m6'] = 0;
  $val['cap_attendu']['m9'] = 0;
  $val['cap_attendu']['m12'] = 0;
  $val['int_attendu']['j'] = 0;
  $val['int_attendu']['s1'] = 0;
  $val['int_attendu']['s2'] = 0;
  $val['int_attendu']['s3'] = 0;
  $val['int_attendu']['m1'] = 0;
  $val['int_attendu']['m2'] = 0;
  $val['int_attendu']['m3'] = 0;
  $val['int_attendu']['m6'] = 0;
  $val['int_attendu']['m9'] = 0;
  $val['int_attendu']['m12'] = 0;
  $val['ep_nantie']['j'] = 0;
  $val['ep_nantie']['s1'] = 0;
  $val['ep_nantie']['s2'] = 0;
  $val['ep_nantie']['s3'] = 0;
  $val['ep_nantie']['m1'] = 0;
  $val['ep_nantie']['m2'] = 0;
  $val['ep_nantie']['m3'] = 0;
  $val['ep_nantie']['m6'] = 0;
  $val['ep_nantie']['m9'] = 0;
  $val['ep_nantie']['m12'] = 0;
  $val['ep_terme']['j'] = 0;
  $val['ep_terme']['s1'] = 0;
  $val['ep_terme']['s2'] = 0;
  $val['ep_terme']['s3'] = 0;
  $val['ep_terme']['m1'] = 0;
  $val['ep_terme']['m2'] = 0;
  $val['ep_terme']['m3'] = 0;
  $val['ep_terme']['m6'] = 0;
  $val['ep_terme']['m9'] = 0;
  $val['ep_terme']['m12'] = 0;
  $val['ep_libre']['j'] = 0;
  $val['ep_libre']['s1'] = 0;
  $val['ep_libre']['s2'] = 0;
  $val['ep_libre']['s3'] = 0;
  $val['ep_libre']['m1'] = 0;
  $val['ep_libre']['m2'] = 0;
  $val['ep_libre']['m3'] = 0;
  $val['ep_libre']['m6'] = 0;
  $val['ep_libre']['m9'] = 0;
  $val['ep_libre']['m12'] = 0;

  //Récupère les données pour le rapport
  $data_dates = get_dates();
  setMonnaieCourante($devise);

  foreach($list_agence as $key_id_ag =>$value) {
    setGlobalIdAgence($key_id_ag);
    $data_credit = get_prevision_credit($devise);
    $data_epargne = get_prevision_epargne($devise);
    if ($key_id_ag != 0) {
      $val['cap_attendu']['j'] += $data_credit['cap_attendu']['j'];
      $val['cap_attendu']['s1'] += $data_credit['cap_attendu']['s1'];
      $val['cap_attendu']['s2'] += $data_credit['cap_attendu']['s2'];
      $val['cap_attendu']['s3'] += $data_credit['cap_attendu']['s3'];
      $val['cap_attendu']['m1'] += $data_credit['cap_attendu']['m1'];
      $val['cap_attendu']['m2'] += $data_credit['cap_attendu']['m2'];
      $val['cap_attendu']['m3'] += $data_credit['cap_attendu']['m3'];
      $val['cap_attendu']['m6'] += $data_credit['cap_attendu']['m6'];
      $val['cap_attendu']['m9'] += $data_credit['cap_attendu']['m9'];
      $val['cap_attendu']['m12'] += $data_credit['cap_attendu']['m12'];

      $val['int_attendu']['j'] += $data_credit['int_attendu']['j'];
      $val['int_attendu']['s1'] += $data_credit['int_attendu']['s1'];
      $val['int_attendu']['s2'] += $data_credit['int_attendu']['s2'];
      $val['int_attendu']['s3'] += $data_credit['int_attendu']['s3'];
      $val['int_attendu']['m1'] += $data_credit['int_attendu']['m1'];
      $val['int_attendu']['m2'] += $data_credit['int_attendu']['m2'];
      $val['int_attendu']['m3'] += $data_credit['int_attendu']['m3'];
      $val['int_attendu']['m6'] += $data_credit['int_attendu']['m6'];
      $val['int_attendu']['m9'] += $data_credit['int_attendu']['m9'];
      $val['int_attendu']['m12'] += $data_credit['int_attendu']['m12'];


      $val['ep_nantie']['j'] += $data_epargne['ep_nantie']['j'];
      $val['ep_nantie']['s1'] += $data_epargne['ep_nantie']['s1'];
      $val['ep_nantie']['s2'] += $data_epargne['ep_nantie']['s2'];
      $val['ep_nantie']['s3'] += $data_epargne['ep_nantie']['s3'];
      $val['ep_nantie']['m1'] += $data_epargne['ep_nantie']['m1'];
      $val['ep_nantie']['m2'] += $data_epargne['ep_nantie']['m2'];
      $val['ep_nantie']['m3'] += $data_epargne['ep_nantie']['m3'];
      $val['ep_nantie']['m6'] += $data_epargne['ep_nantie']['m6'];
      $val['ep_nantie']['m9'] += $data_epargne['ep_nantie']['m9'];
      $val['ep_nantie']['m12'] += $data_epargne['ep_nantie']['m12'];

      $val['ep_terme']['j'] += $data_epargne['ep_terme']['j'];
      $val['ep_terme']['s1'] += $data_epargne['ep_terme']['s1'];
      $val['ep_terme']['s2'] += $data_epargne['ep_terme']['s2'];
      $val['ep_terme']['s3'] += $data_epargne['ep_terme']['s3'];
      $val['ep_terme']['m1'] += $data_epargne['ep_terme']['m1'];
      $val['ep_terme']['m2'] += $data_epargne['ep_terme']['m2'];
      $val['ep_terme']['m3'] += $data_epargne['ep_terme']['m3'];
      $val['ep_terme']['m6'] += $data_epargne['ep_terme']['m6'];
      $val['ep_terme']['m9'] += $data_epargne['ep_terme']['m9'];
      $val['ep_terme']['m12'] += $data_epargne['ep_terme']['m12'];

      $val['ep_libre']['j'] += $data_epargne['ep_libre']['j'];
      $val['ep_libre']['s1'] += $data_epargne['ep_libre']['s1'];
      $val['ep_libre']['s2'] += $data_epargne['ep_libre']['s2'];
      $val['ep_libre']['s3'] += $data_epargne['ep_libre']['s3'];
      $val['ep_libre']['m1'] += $data_epargne['ep_libre']['m1'];
      $val['ep_libre']['m2'] += $data_epargne['ep_libre']['m2'];
      $val['ep_libre']['m3'] += $data_epargne['ep_libre']['m3'];
      $val['ep_libre']['m6'] += $data_epargne['ep_libre']['m6'];
      $val['ep_libre']['m9'] += $data_epargne['ep_libre']['m9'];
      $val['ep_libre']['m12'] += $data_epargne['ep_libre']['m12'];

      $val["j"] = $data_dates['j'];
      $val["s1"] = $data_dates['s1'];
      $val["s2"] = $data_dates['s2'];
      $val["s3"] = $data_dates['s3'];
      $val["m1"] = $data_dates['m1'];
      $val["m2"] = $data_dates['m2'];
      $val["m3"] = $data_dates['m3'];
      $val["m6"] = $data_dates['m6'];
      $val["m9"] = $data_dates['m9'];
      $val["m12"] = $data_dates['m12'];
    }
  }

  // Ajout du nombre d'agence selectionné dans tableau contenant les statistiques de l'agence ou du réseau
  $val['a_nombreAgence'] = count($list_agence);
  if ($val['a_nombreAgence'] > 1) {
    resetGlobalIdAgence();
  }
  return $val;
}

function xml_prevision_liquidite($val, $devise = NULL, $export_csv = false) {
  $document = create_xml_doc("prevision_liquidite", "prevision_liquidite.dtd");
  //Element root
  $root = $document->root();
  //En-tête généraliste
  gen_header($root, 'AGC-LIQ', " ($devise)");
  //Corps
  $body = $root->new_child("body", "");

  //Dates
  $dates = $body->new_child("dates", "");
  $previsions = $dates->new_child("previsions", "");
  $previsions->new_child("j", $val["j"]);
  $previsions->new_child("s1", $val["s1"]);
  $previsions->new_child("s2", $val["s2"]);
  $previsions->new_child("s3", $val["s3"]);
  $previsions->new_child("m1", $val["m1"]);
  $previsions->new_child("m2", $val["m2"]);
  $previsions->new_child("m3", $val["m3"]);
  $previsions->new_child("m6", $val["m6"]);
  $previsions->new_child("m9", $val["m9"]);
  $previsions->new_child("m12", $val["m12"]);
  //Crédit
  $credit = $body->new_child("credit", "");
  $cap_attendu = $credit->new_child("cap_attendu", "");
  gen_xml_previsions($val['cap_attendu'], $cap_attendu, $export_csv);
  $int_attendu = $credit->new_child("int_attendu", "");
  gen_xml_previsions($val['int_attendu'], $int_attendu, $export_csv);
  //Epargne
  $epargne = $body->new_child("epargne", "");
  $ep_nantie = $epargne->new_child("ep_nantie", "");
  gen_xml_previsions($val['ep_nantie'], $ep_nantie,  $export_csv);
  $ep_terme = $epargne->new_child("ep_terme", "");
  gen_xml_previsions($val['ep_terme'], $ep_terme,  $export_csv);
  $ep_libre = $epargne->new_child("ep_libre", "");
  gen_xml_previsions($val['ep_libre'], $ep_libre,  $export_csv);

  //Liste des agences consolidées
  if (isSiege()) {
   $list_agence=getListAgenceConsolide();
   foreach($list_agence as $id_ag =>$data_agence) {
     $enreg_agence=$root->new_child("enreg_agence","");
     $enreg_agence->new_child("is_siege", "true");
     $enreg_agence->new_child("id_ag", $data_agence['id']);
     $enreg_agence->new_child("libel_ag", $data_agence['libel']);
     $enreg_agence->new_child("date_max", $data_agence['date_dernier_mouv']);
   }
  }else{
  	 $enreg_agence=$root->new_child("enreg_agence","");
  	 $enreg_agence->new_child("is_siege", "false");
  }

  return $document->dump_mem(true);
}

function xml_brouillard_caisse($GDATA, $date, $export_csv = false) {
  global $global_multidevise, $global_monnaie, $adsys;
  reset($GDATA);
  $document = create_xml_doc("brouillard_caisse", "brouillard_caisse.dtd");
  $root = $document->root();
  gen_header($root, 'AGC-BRO', " : $date");
  foreach ($GDATA as $guichet => $DATAS) {
    foreach ($DATAS as $devise => $DATA) {
      $brouillard_devise = $root->new_child("brouillard_devise", "");
      $brouillard_devise->set_attribute("devise", $devise);
      $brouillard_devise->set_attribute("guichet", $guichet);
      setMonnaieCourante($devise);
      $infos_globales = $brouillard_devise->new_child("infos_globales", "");
      $infos_globales->new_child("libel_gui", $DATA["libel_gui"]);
      $infos_globales->new_child("nom_uti", $DATA["utilisateur"]);
      $infos_globales->new_child("encaisse_deb", afficheMontant($DATA["encaisse_debut"], false, $export_csv));
      //$infos_globales->new_child("encaisse_fin", afficheMontant($DATA["encaisse_fin"], true));
      $infos_globales->new_child("encaisse_fin", afficheMontant($DATA["encaisse_fin"], false, $export_csv));
      // Ajout des infos globales
      $resume_transactions = $infos_globales->new_child("resume_transactions", "");
      
      while (list ($key, $infos) = each($DATA["global"])) {
        $ligne_resume_transactions = $resume_transactions->new_child("ligne_resume_transactions", "");
        $libel_operation_trad = new Trad($infos["libel_operation"]);
        $libel_operation = $libel_operation_trad->traduction();
        $ligne_resume_transactions->new_child("libel_operation", $libel_operation);
        $ligne_resume_transactions->new_child("nombre", $infos["nombre"]);
        $ligne_resume_transactions->new_child("montant_debit", $infos["montant_debit"]);
        $ligne_resume_transactions->new_child("montant_credit", $infos["montant_credit"]);
        $ligne_resume_transactions->set_attribute("total", "0");
      }
      // Ajout de la ligne pour les totaux
      $infos = $DATA["total"];
      $ligne_resume_transactions = $resume_transactions->new_child("ligne_resume_transactions", "");
      $ligne_resume_transactions->new_child("libel_operation", "TOTAL");
      $ligne_resume_transactions->new_child("nombre", $infos["nombre"]);
      $ligne_resume_transactions->new_child("montant_debit", $infos["montant_debit"]);
      $ligne_resume_transactions->new_child("montant_credit", $infos["montant_credit"]);
      $ligne_resume_transactions->set_attribute("total", "1");
      // Ajout des infos détaillées
      if (is_array($DATA["details"])) {
        $detail = $brouillard_devise->new_child("detail", "");
        while (list ($key, $infos) = each($DATA["details"])) {
          $libel_operation_trad = new Trad($infos["libel_operation"]);
      	  $libel_operation = $libel_operation_trad->traduction();
          $id_client = makeNumClient($infos["id_client"]);
          $nom_client = mb_substr($infos["client"], 0, 22, "UTF-8");

          // Multi agence fix
          if($libel_operation=="Retrait en déplacé" || $libel_operation=="Dépôt en déplacé")
          {
              $c_id_his = (int)$infos["id_his"];
              
              $his_data = getHistoriqueDatas(array('id_his' => $c_id_his));

              if(is_array($his_data) && count($his_data)==1)
              {
                  $id_client = "";
                  $nom_client = trim($his_data[$c_id_his]["infos"]);
              }
              else
              {
                  $id_client = "";
                  $nom_client = "Client extérieur";
              }
          }

          if(in_array($infos['type_operation'], $adsys["adsys_operation_cheque_infos"]) ){
            $libel_operation = getChequeno($infos["id_his"],$libel_operation,$infos['info_ecriture']);
          }

          $ligne_detail = $detail->new_child("ligne_detail", "");
          $ligne_detail->new_child("num_trans", $infos["id_his"]);
          $ligne_detail->new_child("num_piece", $infos["num_piece"]);
          $ligne_detail->new_child("heure", $infos["heure"]);
          $ligne_detail->new_child("libel_operation", mb_substr($libel_operation, 0, 50, "UTF-8"));
          $ligne_detail->new_child("id_client", $id_client);
          $ligne_detail->new_child("nom_client", $nom_client);
          $ligne_detail->new_child("montant_debit", $infos["montant_debit"]);
          $ligne_detail->new_child("montant_credit", $infos["montant_credit"]);
          $ligne_detail->new_child("encaisse", $infos["encaisse"]);
        }
      }
    }
  }
  return $document->dump_mem(true);
}

function xml_ecritures_libres($GDATA, $date_debut, $date_fin, $export_csv = false){
	global $global_multidevise, $global_monnaie;
	reset($GDATA);
	$document = create_xml_doc("ecritures_libres", "ecritures_libres.dtd");
	$root = $document->root();
	gen_header($root, 'AGC-LIB', " : "._("Du")." $date_debut "._("Au")." $date_fin");
	foreach ($GDATA as $devise => $DATA) {
		$ecritures_devise = $root->new_child("ecritures_devise", "");
		$ecritures_devise->set_attribute("devise", $devise);

		setMonnaieCourante($devise);

		$infos_globales = $ecritures_devise->new_child("infos_globales", "");
		$infos_globales->new_child("nom_uti", $DATA["utilisateur"]);
		$infos_globales->new_child("login", $DATA["login"]);
		if(!isset($DATA["sans_guichet"])) {
			$infos_globales->new_child("encaisse_deb", afficheMontant($DATA["encaisse_debut"], false, $export_csv));
			$infos_globales->new_child("encaisse_fin", afficheMontant($DATA["encaisse_fin"], false, $export_csv));
		}
		else
		$infos_globales->new_child("sans_gui", $DATA["sans_guichet"], false, $export_csv);


		// Ajout des infos globales
		$resume_transactions = $infos_globales->new_child("resume_transactions", "");
		while (list ($key, $infos) = each($DATA["global"])) {
			$libel_operation_trad = new Trad($infos["libel_operation"]);
			$libel_operation = $libel_operation_trad->traduction();
			$ligne_resume_transactions = $resume_transactions->new_child("ligne_resume_transactions", "");
			$ligne_resume_transactions->new_child("libel_operation", $libel_operation);
			$ligne_resume_transactions->new_child("nombre", $infos["nombre"]);
			$ligne_resume_transactions->new_child("montant", $infos["montant_debit"]);
			$ligne_resume_transactions->set_attribute("total", "0");
		}

		// Ajout de la ligne pour les totaux
		$infos = $DATA["total"];
		$ligne_resume_transactions = $resume_transactions->new_child("ligne_resume_transactions", "");
		$ligne_resume_transactions->new_child("libel_operation", $libel_operation);
		$ligne_resume_transactions->new_child("nombre", $infos["nombre"]);
		$ligne_resume_transactions->new_child("montant", $infos["montant_debit"]);
		$ligne_resume_transactions->set_attribute("total", "1");

		// Ajout des infos détaillées
		if (is_array($DATA["details"])) {
			$detail = $ecritures_devise->new_child("detail", "");
			while (list ($key, $infos) = each($DATA["details"])) {
				$libel_operation_trad = new Trad($infos["libel_operation"]);
				$libel_operation = $libel_operation_trad->traduction();
				$ligne_detail = $detail->new_child("ligne_detail", "");
				$ligne_detail->new_child("num_trans", $infos["id_his"]);
				$ligne_detail->new_child("client", $infos["client"]);
				$ligne_detail->new_child("heure", $infos["date"]."-".$infos["heure"]);
				$ligne_detail->new_child("libel_operation", mb_substr($libel_operation, 0, 22, "UTF-8"));
				$ligne_detail->new_child("compte_debit", $infos["compte_debit"]);
				$ligne_detail->new_child("compte_credit", $infos["compte_credit"]);
				$ligne_detail->new_child("montant_debit", $infos["montant_debit"]);
				$ligne_detail->new_child("montant_credit", $infos["montant_credit"]);
			}
		}
	}

	return $document->dump_mem(true);
}

function xml_ajustements_caisse($DATA, $liste_criteres, $export_csv = false) {
  basculer_langue_rpt();
  global $adsys;
  $document = create_xml_doc("ajustements_caisse", "ajustements_caisse.dtd");
  // Définition de la racine
  $root = $document->root();
  // En-tête généraliste
  gen_header($root, 'AGC-ADC', "");
  // En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $liste_criteres);
  // Body
  foreach ($DATA as $value) {
    setMonnaieCourante($value['devise']);
    $ajustement = $root->new_child("ajustement", "");
    $ajustement->new_child("utilisateur", $value['utilisateur']);
    $ajustement->new_child("date_ajustement", substr($value['date_ajustement'],0,10));
    $ajustement->new_child("manquant", afficheMontant($value['manquant'], false, $export_csv));
    $ajustement->new_child("excedent", afficheMontant($value['excedent'], false, $export_csv));
    $ajustement->new_child("total", afficheMontant($value['total'], false, $export_csv));
  }
  reset_langue();
  return $document->dump_mem(true);
}

/**
 *
 * Fonction qui génère le code XML pour le rapport Appel de fonds
 *
 * @param array $a_data : Liste de données renvoyée par {@see getHisDdeCrd}
 * @param array $a_listeCriteres : Liste des critères sélectionnée
 * @param boolean $a_exportCsv : Valeur spécifiant si le XML est généré pour un PDF ou un CSV
 * @return Object $document
 *
 */
function xmlAppelFonds($a_data, $a_listeCriteres, $a_exportCsv = false) {
  global $adsys;
  global $global_multidevise;
  $document = create_xml_doc("appel_fonds", "appel_fonds.dtd");

  //définition de la racine
  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'AGC-APF');

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $a_listeCriteres);

  //body
	$nb_total_credit = $nb_credit_ordinaire = $nb_credit_solidaire = 0;
	$mnt_total_credit = $mnt_credit_ordinaire = $mnt_credit_solidaire = 0;
  if (is_array($a_data)) {
  	$gestionnaire_courant = "";
  	$group_prec="";
    foreach ($a_data as $value) {
    	if ($gestionnaire_courant != $value['id_agent_gest']) {
        if ( $_POST["gest"] == NULL ) {
          $gestionnaire = $root->new_child("gestionnaire", "");
          $gestionnaire->new_child("agent_gest", getLibel("ad_uti", $value['id_agent_gest']));
        } else {
          $gestionnaire = $root->new_child("gestionnaire", "");
          $gestionnaire->new_child("agent_gest", "Gestionnaire: " . getLibel("ad_uti", $value['id_agent_gest']));
        }
        $gestionnaire_courant = $value['id_agent_gest'];
    	}
      if ($global_multidevise) {
        setMonnaieCourante($value["devise"]);
      }

      if($value["groupe_solidaire"] == "OUI") {
	    	if (!isset($gestionnaire)) {
  	  		$gestionnaire = $root->new_child("gestionnaire", "");
  	  		$gestionnaire->new_child("agent_gest", "Sans gestionnaire");
    		}
      	$ligneCredit = $gestionnaire->new_child("ligneCredit", "");

       	$infosCreditSolidiaire = $ligneCredit->new_child("infosCreditSolidiaire", "");
      	$infosCreditSolidiaire->new_child("num_client", $value["id_client"]);
      	$nomClient = mb_substr(getClientName($value["id_client"]), 0, 16, "UTF-8");
				$infosCreditSolidiaire->new_child("nom_client", $nomClient);
      	$infosCreditSolidiaire->new_child("no_dossier", $value["id_doss"]);
      	$infosCreditSolidiaire->new_child("prd_credit", $value["id_prod"]);
      	$infosCreditSolidiaire->new_child("date_dde", pg2phpDate($value["date_dem"]));
      	$infosCreditSolidiaire->new_child("devise", $value["devise"]);
      	$infosCreditSolidiaire->new_child("montant_dde", afficheMontant($value["mnt_dem"], false, $a_exportCsv));
      	$infosCreditSolidiaire->new_child("obj_dde", mb_substr($value["libel_obj"], 0, 16, "UTF-8"));
      	$infosCreditSolidiaire->new_child("detail_obj_dde", mb_substr($value["detail_obj_dem"], 0, 16, "UTF-8"));
      	$infosCreditSolidiaire->new_child("duree", $value["duree_mois"]);
      	$infosCreditSolidiaire->new_child("etat", mb_substr(adb_gettext($adsys["adsys_etat_dossier_credit"][$value["etat"]]), 0, 10, "UTF-8"));

      	$nb_total_credit++;
      	$nb_credit_solidaire++;
      	$mnt_total_credit += $value["mnt_dem"];
      	$mnt_credit_solidaire += $value["mnt_dem"];

    	} else {
    		if (!(isset($value["membre_gs"]))) {
    			$ligneCredit = $gestionnaire->new_child("ligneCredit", "");
    			$nb_total_credit++;
	      	$nb_credit_ordinaire++;
	      	$mnt_total_credit += $value["mnt_dem"];
	      	$mnt_credit_ordinaire += $value["mnt_dem"];
    		}

    		$detailCredit = $ligneCredit->new_child("detailCredit", "");

    		$detailCredit->new_child("num_client", $value["id_client"]);
      	$nomClient = mb_substr(getClientName($value["id_client"]), 0, 11, "UTF-8");
				$detailCredit->new_child("nom_client", $nomClient);
      	$detailCredit->new_child("no_dossier", $value["id_doss"]);
      	$detailCredit->new_child("prd_credit", $value["id_prod"]);
      	$detailCredit->new_child("date_dde", pg2phpDate($value["date_dem"]));
      	$detailCredit->new_child("devise", $value["devise"]);
      	$detailCredit->new_child("montant_dde", afficheMontant($value["mnt_dem"], false, $a_exportCsv));
      	$detailCredit->new_child("obj_dde", mb_substr($value["libel_obj"], 0, 10, "UTF-8"));
      	$detailCredit->new_child("detail_obj_dde", mb_substr($value["detail_obj_dem"], 0, 10, "UTF-8"));
      	$detailCredit->new_child("duree", $value["duree_mois"]);
      	$detailCredit->new_child("agent_gest", mb_substr($value["nom"] . " " . $value["prenom"], 0, 10, "UTF-8"));
      	$detailCredit->new_child("etat", mb_substr(adb_gettext($adsys["adsys_etat_dossier_credit"][$value["etat"]]), 0, 10, "UTF-8"));
      	$detailCredit->new_child("membre_gs", $value["membre_gs"]);
    	}
  	}
	  $recapitulatif = $root->new_child("recapitulatif", "");
  	$recapitulatif->new_child("nb_total_credit", $nb_total_credit);
  	$recapitulatif->new_child("nb_credit_ordinaire", $nb_credit_ordinaire);
  	$recapitulatif->new_child("nb_credit_solidaire", $nb_credit_solidaire);
  	$recapitulatif->new_child("mnt_total_credit", afficheMontant($mnt_total_credit, false, $a_exportCsv));
  	$recapitulatif->new_child("mnt_credit_ordinaire", afficheMontant($mnt_credit_ordinaire, false, $a_exportCsv));
  	$recapitulatif->new_child("mnt_credit_solidaire", afficheMontant($mnt_credit_solidaire, false, $a_exportCsv));
	}
  return $document->dump_mem(true);
}

function xml_resultat_trimestriel($data,$annee, $Export_csv = false,$id_agc=NULL) {
  global $global_id_agence;
  global $global_id_client;
  global $global_multidevise;
  global $adsys;
  global $dbHandler;

  $document = create_xml_doc("resultat_trimestriel", "resultat_trimestriel.dtd");
  //Element root
  $root = $document->root();
  //En-tête généraliste
  if($id_agc==NULL){
  	gen_header($root, 'AGC-TRT');
  }else{ //multiagence, mettre le nom de l'agence selectionné'
  	setGlobalIdAgence($id_agc);
  	gen_header($root, 'AGC-TRT');
  	resetGlobalIdAgence();
  }

  $debut_periode = date("d/m/Y", mktime(0, 0, 0, 1, 1, date("Y")));
  $fin_periode="31/12/".$annee;
  // Infos globales
  $infos_globales = $root->new_child("infos_globales", "");
  $infos = getAgenceDatas($global_id_agence);
  $infos_globales->new_child("debut_periode", $debut_periode);
  $infos_globales->new_child("fin_periode", $fin_periode);



  $usagers = $root->new_child("usagers", "");
  $epargnants=$root->new_child("epargnants", "");
  $encoursepargnes=$root->new_child("encoursepargnes", "");
  $credits_accordes=$root->new_child("credits_accordes", "");
  $montant_credits_accordes=$root->new_child("montant_credits_accordes", "");
  $credit_en_cours=$root->new_child("credit_en_cours", "");
  $charge_exploitation=$root->new_child("charge_exploitation", "");
  $produit_exploitation=$root->new_child("produit_exploitation", "");
  $tabcptescharges=array();
   $tabcptesprouits=array();
  foreach ($data as $trim =>$valeur ){


  	$usagers->new_child("nbre_homme_t".$trim, $valeur['usagers']['clients']['homme']);
  	$usagers->new_child("nbre_femme_t".$trim,$valeur['usagers']['clients']['femme']);
  	$usagers->new_child("nbre_g_homme_t".$trim,$valeur['usagers']['clients']['g_homme']);
  	$usagers->new_child("nbre_g_femme_t".$trim, $valeur['usagers']['clients']['g_femme']);
  	$usagers->new_child("nbre_g_mixte_t".$trim,$valeur['usagers']['clients']['g_mixte']);
  	$usagers->new_child("TOTAL_t".$trim,$valeur['usagers']['clients']['TOTAL']);
  	$epargnants->new_child("nbre_homme_t".$trim, $valeur["epargnants"]['homme']['nbre']);
  	$epargnants->new_child("nbre_femme_t".$trim,$valeur["epargnants"]['femme']['nbre']);
  	$epargnants->new_child("nbre_g_homme_t".$trim, $valeur["epargnants"]['g_homme']['nbre']);
  	$epargnants->new_child("nbre_g_femme_t".$trim,$valeur["epargnants"]['g_femme']['nbre']);
  	$epargnants->new_child("nbre_g_mixte_t".$trim, $valeur["epargnants"]['g_mixte']['nbre']);
  	$epargnants->new_child("TOTAL_t".$trim, $valeur["epargnants"]['TOTAL']['nbre']);
  	$encoursepargnes->new_child("montant_homme_t".$trim, afficheMontant($valeur["encourepargants"]['homme']['montant'], $Export_csv));
  	$encoursepargnes->new_child("montant_femme_t".$trim, afficheMontant($valeur["encourepargants"]['femme']['montant'], $Export_csv));
  	$encoursepargnes->new_child("montant_g_homme_t".$trim, afficheMontant($valeur["encourepargants"]['g_homme']['montant'], $Export_csv));
  	$encoursepargnes->new_child("montant_g_femme_t".$trim, afficheMontant($valeur["encourepargants"]['g_femme']['montant'], $Export_csv));
  	$encoursepargnes->new_child("montant_g_mixte_t".$trim, afficheMontant($valeur["encourepargants"]['g_mixte']['montant'], $Export_csv));
  	$encoursepargnes->new_child("TOTAL_t".$trim, afficheMontant($valeur["encourepargants"]['TOTAL']['montant'], $Export_csv));




  	$credits_accordes->new_child("nbre_homme_t".$trim, $valeur["credits_accordes"]['homme']['nbre']);
  	$credits_accordes->new_child("nbre_femme_t".$trim, $valeur["credits_accordes"]['femme']['nbre']);
  	$credits_accordes->new_child("nbre_g_homme_t".$trim, $valeur["credits_accordes"]['g_homme']['nbre']);
  	$credits_accordes->new_child("nbre_g_femme_t".$trim, $valeur["credits_accordes"]['g_femme']['nbre']);
  	$credits_accordes->new_child("nbre_g_mixte_t".$trim, $valeur["credits_accordes"]['g_mixte']['nbre']);
  	$credits_accordes->new_child("TOTAL_t".$trim, $valeur["credits_accordes"]['TOTAL']['nbre']);

  	$montant_credits_accordes->new_child("montant_homme_t".$trim, afficheMontant($valeur["credits_accordes"]['homme']['montant'], $Export_csv));
  	$montant_credits_accordes->new_child("montant_femme_t".$trim, afficheMontant($valeur["credits_accordes"]['femme']['montant'], $Export_csv));
  	$montant_credits_accordes->new_child("montant_g_homme_t".$trim, afficheMontant($valeur["credits_accordes"]['g_homme']['montant'], $Export_csv));
  	$montant_credits_accordes->new_child("montant_g_femme_t".$trim, afficheMontant($valeur["credits_accordes"]['g_femme']['montant'], $Export_csv));
  	$montant_credits_accordes->new_child("montant_g_mixte_t".$trim, afficheMontant($valeur["credits_accordes"]['g_mixte']['montant'], $Export_csv));
  	$montant_credits_accordes->new_child("TOTAL_t".$trim,  afficheMontant($valeur["credits_accordes"]['TOTAL']['montant'], $Export_csv));


  	foreach($valeur['exploitation']->param as $numCompte=>$infos){

  			if($infos["compte_charge"] == 'TOTAL' OR $infos["compte_charge"] == 'Résultat de la période'){
  				if($infos["compte_charge"] == 'TOTAL'  && $infos['libel_charge']== 'TOTAL CHARGE'){
  					if(!isset($tabcptescharges['TOTAL_CHARGE'])){
  						$tabcptescharges['TOTAL_CHARGE'] =  $charge_exploitation->new_child("compteCharge", "");
  						$tabcptescharges['TOTAL_CHARGE']->new_child("compte_charge", $infos["compte_charge"]);
	        	  $tabcptescharges['TOTAL_CHARGE']->new_child("libel_charge", htmlspecialchars($infos["libel_charge"], ENT_QUOTES, "UTF-8"));
  					}
  					$tabcptescharges['TOTAL_CHARGE']->set_attribute("total", 1);
  					$tabcptescharges['TOTAL_CHARGE']->new_child("solde_charge_t".$trim , afficheMontant($infos["solde_charge"], false));

	        	if(!isset($tabcptesproduits['TOTAL_PRODUIT'])){
	        		$tabcptesproduits['TOTAL_PRODUIT'] =  $produit_exploitation->new_child("compteProduit", "");
	        		$tabcptesproduits['TOTAL_PRODUIT']->new_child("compte_produit", 'TOTAL');
		      	  $tabcptesproduits['TOTAL_PRODUIT']->new_child("libel_produit", htmlspecialchars($infos["libel_produit"], ENT_QUOTES, "UTF-8"));

	        	}
	        	 $tabcptesproduits['TOTAL_PRODUIT']->set_attribute("total", 1);
	        	 $tabcptesproduits['TOTAL_PRODUIT']->new_child("solde_produit_t".$trim , afficheMontant($infos["solde_produit"], false));
  				}elseif( $infos["compte_charge"] == 'Résultat de la période'){
  					if(!isset($tabcptescharges['RESULTAT'])){
  						$tabcptescharges['RESULTAT'] =  $charge_exploitation->new_child("compteCharge", "");
  						$tabcptescharges['RESULTAT']->new_child("compte_charge", $infos["compte_charge"]);
	        	  $tabcptescharges['RESULTAT']->new_child("libel_charge", htmlspecialchars($infos["libel_charge"], ENT_QUOTES, "UTF-8"));

  					}
  					$tabcptescharges['RESULTAT']->set_attribute("total", 1);
  					$tabcptescharges['RESULTAT']->new_child("solde_charge_t".$trim , afficheMontant($infos["solde_charge"], false));

  				}

  			}else{
  				if(  $infos["compte_charge"]!="" OR $infos["compte_charge"]!=NULL)  {
  					if(!isset($tabcptescharges[$infos["compte_charge"]])){
  						$tabcptescharges[$infos["compte_charge"]] =  $charge_exploitation->new_child("compteCharge", "");
  						$tabcptescharges[$infos["compte_charge"]]->new_child("compte_charge", $infos["compte_charge"]);
	        		$tabcptescharges[$infos["compte_charge"]]->new_child("libel_charge", htmlspecialchars($infos["libel_charge"], ENT_QUOTES, "UTF-8"));
  					}
  					$nivchge = substr_count($infos["compte_charge"], ".") + 1;
  					$tabcptescharges[$infos["compte_charge"]]->set_attribute("total", 0);
  					// Définition des propriétés niveau des comptes
    			  $tabcptescharges[$infos["compte_charge"]]->set_attribute("nivchge", $nivchge);

	     		  $tabcptescharges[$infos["compte_charge"]]->new_child("solde_charge_t".$trim , afficheMontant($infos["solde_charge"], false));


  				}
  				if($infos["compte_produit"]!=NULL OR $infos["compte_produit"]!="") {
  					if( !isset($tabcptesproduits[$infos["compte_charge"] ] ) ){
		  				$tabcptesproduits[$infos["compte_charge"]] =  $produit_exploitation->new_child("compteProduit", "");
		  				$tabcptesproduits[$infos["compte_charge"]]->new_child("compte_produit", $infos["compte_produit"]);
				      $tabcptesproduits[$infos["compte_charge"]]->new_child("libel_produit", htmlspecialchars($infos["libel_produit"], ENT_QUOTES, "UTF-8"));
		  			}
		  			$nivprod = substr_count($infos["compte_produit"], ".") + 1;
			  		$tabcptesproduits[$infos["compte_charge"]]->set_attribute("total", 0);
				    // Définition des propriétés niveau des comptes
				    $tabcptesproduits[$infos["compte_charge"]]->set_attribute("nivprod", $nivprod);
				    $tabcptesproduits[$infos["compte_charge"]]->new_child("solde_produit_t".$trim, afficheMontant($infos["solde_produit"], false));
	  			}

  			}


    }//FIN parcours comptes


    }//fin parcour trimestre

  //Liste des agences consolidées
  if (isSiege() && $_POST["agence"] == NULL) {
   $list_agence=getListAgenceConsolide();
   foreach($list_agence as $id_ag =>$data_agence) {
     $enreg_agence=$root->new_child("enreg_agence","");
     $enreg_agence->new_child("is_siege", "true");
     $enreg_agence->new_child("id_ag", $data_agence['id']);
     $enreg_agence->new_child("libel_ag", $data_agence['libel']);
     $enreg_agence->new_child("date_max", $data_agence['date_dernier_mouv']);
   }
  }else{
  	 $enreg_agence=$root->new_child("enreg_agence","");
  	 $enreg_agence->new_child("is_siege", "false");
  }

  return $document->dump_mem(true);
}

/**
 * Genere le xml pour le rapport equilibre inventaire/comptabilite
 * 
 * @param array $DATAS
 * @param date $date_rapport
 * @param string $compte_comptable
 * @param string $Export_csv
 * @param string $id_agc
 * @return string
 */
function xml_equilibre_inventaire_compta($export_date, $compte_comptable) 
{
	global $global_id_agence;	
	global $global_multidevise;
	global $adsys;
	global $dbHandler;
	
	$document = create_xml_doc("equilibre_inventaire_comptabilite", "equilibre_inventaire_comptabilite.dtd");
	
	// Recuperation des donnees du rapport
	$DATAS = get_rapport_equilibre_compta_data($export_date, $compte_comptable);
		
	//Element root
	$root = $document->root();
	
	//En-tête généraliste
	if($id_agc==NULL) {
		gen_header($root, 'AGC-EIC');
	} else { //multiagence, mettre le nom de l'agence selectionné'
		setGlobalIdAgence($id_agc);
		gen_header($root, 'AGC-EIC');
		resetGlobalIdAgence();
	}
	
	//En-tête contextuel
	$header_contextuel = $root->new_child("header_contextuel", "");
		
	// le critere 'cpte_cpta_ecart'
	if(empty($cpte_cpta_ecart)) $cpte_cpta_ecart = _("Tous");	
	
	// Affichage critere de recherches
	$criteres = array (			
			_("Date") => date($export_date),
			_("Compte comptable") => _($cpte_cpta_ecart)
	);
	
	gen_criteres_recherche($header_contextuel, $criteres);

 	/* Node des ecarts */
    $ecarts = $root->new_child("ecarts", "");
	
	// xml donnees ecarts
	foreach($DATAS as $row) {
		$ecart = $ecarts->new_child("ecart", "");		
		$ecart->new_child("date_ecart", $row['date_ecart']);
		$ecart->new_child("numero_compte_comptable", $row['numero_compte_comptable']);
		$ecart->new_child("libel_cpte_comptable", $row['libel_cpte_comptable']);
		$ecart->new_child("devise", $row['devise']);
		$ecart->new_child("solde_cpte_int", afficheMontant($row['solde_cpte_int'], false, $export_csv));
		$ecart->new_child("solde_cpte_comptable", afficheMontant($row['solde_cpte_comptable'], false, $export_csv));
		$ecart->new_child("ecart", afficheMontant($row['ecart'], false, $export_csv));
		$ecart->new_child("login", $row['login']);
		$ecart->new_child("id_his", $row['id_his']);
		$ecart->new_child("id_doss", $row['id_doss']);
		$ecart->new_child("cre_etat", $row['cre_etat']);
		$ecart->new_child("solde_credit", afficheMontant($row['solde_credit'], false, $export_csv));
		$ecart->new_child("solde_cpt", afficheMontant($row['solde_cpt'], false, $export_csv));
		$ecart->new_child("ecart_credits", afficheMontant($row['ecart_credits'], false, $export_csv));	
	}	
	
	$output = $document->dump_mem(true);
	return $output;
}


function xml_statistique_operationelle($info_ad, $info_ep,$info_cr, $DATA_EMP,$date_deb,$date_fin)
{
  global $global_id_agence;
  global $global_multidevise;
  global $adsys;
  global $dbHandler;

  $document = create_xml_doc("statistique_operationelle", "statistique_operationelle.dtd");

  // Recuperation des donnees du rapport
  if ($info_ad == true){
    $DATAS = get_rapport_statistique_operationnelle_data($info_ad, $info_ep,$info_cr, $DATA_EMP,$date_deb,$date_fin);
  }

  //Element root
  $root = $document->root();
  //En-tête généraliste

    setGlobalIdAgence($global_id_agence);
    gen_header($root, 'AGC-STO');
    resetGlobalIdAgence();

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");

  // Affichage critere de recherches
  $criteres = array (
    _("Date début") => date($date_deb),
    _("Date fin") => date($date_fin)
  );

  gen_criteres_recherche($header_contextuel, $criteres);


  //total des adhesions
  $total_ad_cible = 0;
  $total_ad_nombre= 0;
  $total_ad_actif = 0;
  $total_prc_nombre = 0;
  $total_prc_actif = 0;

  //total des credits
  $tot_nbre_octroi = 0;
  $tot_mnt_octroi= 0;
  $tot_nbre_remb = 0;
  $tot_mnt_remb = 0;
  $tot_nbre_encours= 0;
  $tot_mnt_encours = 0;

  //total des epargnes
  $tot_nbre_depot = 0;
  $tot_mnt_depot= 0;
  $tot_nbre_retait = 0;
  $tot_mnt_retrait = 0;
  $tot_nbre_encours_ep= 0;
  $tot_mnt_encours_ep = 0;

  // xml donnees statistique
  $info_rapport = $root->new_child("infos_rapport", "");
  foreach($DATAS as $row) {
    if(isset($row['adhesion']) && sizeof($row['adhesion']) > 0){
      $adhesion = $info_rapport->new_child("adhesion", "");
      foreach($row["adhesion"] as $row_ad) {
        $data_adhesion = $adhesion->new_child("data_ad", "");
        $data_adhesion->new_child("employeur", $row_ad['employeur']);
        $data_adhesion->new_child("nbre_cible", $row_ad['cible']);
        $data_adhesion->new_child("nombre", $row_ad['nombre']);
        $data_adhesion->new_child("actif", $row_ad['actif']);
        $data_adhesion->new_child("prc_nbre", $row_ad['prc_nbre']);
        $data_adhesion->new_child("prc_actif", $row_ad['prc_actif']);
        $total_ad_cible += $row_ad['cible'];
        $total_ad_nombre += $row_ad['nombre'];
        $total_ad_actif += $row_ad['actif'];
      }
      $total_prc_nombre = round($total_ad_nombre / $total_ad_cible *100,2);
      $total_prc_actif = round($total_ad_actif / $total_ad_nombre *100,2);
      $adhesion_total = $adhesion->new_child("total_adhesion", "");
      $adhesion_total->new_child("tot_cible",$total_ad_cible);
      $adhesion_total->new_child("tot_nbre", $total_ad_nombre);
      $adhesion_total->new_child("tot_actif", $total_ad_actif);
      $adhesion_total->new_child("tot_prc_nbre", $total_prc_nombre);
      $adhesion_total->new_child("total_prc_actif", $total_prc_actif);
    }
    if(isset($row['credit']) && sizeof($row['credit']) > 0){
      if(isset($row['credit']) && sizeof($row['credit']) > 0){
        $credit = $info_rapport->new_child("credit", "");
        foreach($row["credit"] as $row_cr) {
          $data_credit = $credit->new_child("data_cr", "");
          $data_credit->new_child("employeur_credit", $row_cr['employeur']);
          $data_credit->new_child("nbre_octroi", $row_cr['nbre_octroi']);
          $data_credit->new_child("mnt_octroi", afficheMontant($row_cr['mnt_octroi']));
          $data_credit->new_child("nbre_remb", $row_cr['nbre_remb']);
          $data_credit->new_child("mnt_remb", afficheMontant($row_cr['mnt_remb']));
          $data_credit->new_child("nbre_encours", $row_cr['nbre_encours']);
          $data_credit->new_child("mnt_encours", afficheMontant($row_cr['mnt_encours']));
          $tot_nbre_octroi += $row_cr['nbre_octroi'];
          $tot_mnt_octroi += $row_cr['mnt_octroi'];
          $tot_nbre_remb += $row_cr['nbre_remb'];
          $tot_mnt_remb += $row_cr['mnt_remb'];
          $tot_nbre_encours += $row_cr['nbre_encours'];
          $tot_mnt_encours += $row_cr['mnt_encours'];

        }
        $credit_total = $credit->new_child("total_credit", "");
        $credit_total->new_child("tot_nbre_octroi",afficheMontant($tot_nbre_octroi));
        $credit_total->new_child("tot_mnt_octroi", afficheMontant($tot_mnt_octroi));
        $credit_total->new_child("tot_nbre_remb", afficheMontant($tot_nbre_remb));
        $credit_total->new_child("tot_mnt_remb", afficheMontant($tot_mnt_remb));
        $credit_total->new_child("tot_nbre_encours", afficheMontant($tot_nbre_encours));
        $credit_total->new_child("tot_mnt_encours", afficheMontant($tot_mnt_encours));
      }
    }
    if(isset($row['epargne']) && sizeof($row['epargne']) > 0){
      if(isset($row['epargne']) && sizeof($row['epargne']) > 0){
        $epargne = $info_rapport->new_child("epargne", "");
        foreach($row["epargne"] as $row_ep) {
          $data_epargne = $epargne->new_child("data_ep", "");
          $data_epargne->new_child("employeur_epargne", $row_ep['employeur']);
          $data_epargne->new_child("nbre_depot", $row_ep['nbre_depot']);
          $data_epargne->new_child("mnt_depot", afficheMontant($row_ep['mnt_depot']));
          $data_epargne->new_child("nbre_retrait", $row_ep['nbre_retrait']);
          $data_epargne->new_child("mnt_retrait", afficheMontant($row_ep['mnt_retrait']));
          $data_epargne->new_child("nbre_encours_epargne", $row_ep['nbre_encours']);
          $data_epargne->new_child("mnt_encours_epargne", afficheMontant($row_ep['mnt_encours']));
          $tot_nbre_depot += $row_ep['nbre_depot'];
          $tot_mnt_depot += $row_ep['mnt_depot'];
          $tot_nbre_retait += $row_ep['nbre_retrait'];
          $tot_mnt_retrait += $row_ep['mnt_retrait'];
          $tot_nbre_encours_ep += $row_ep['nbre_encours'];
          $tot_mnt_encours_ep += $row_ep['mnt_encours'];

        }
        $epargne_total = $epargne->new_child("total_epargne", "");
        $epargne_total->new_child("tot_nbre_depot",afficheMontant($tot_nbre_depot));
        $epargne_total->new_child("tot_mnt_depot", afficheMontant($tot_mnt_depot));
        $epargne_total->new_child("tot_nbre_retrait", afficheMontant($tot_nbre_retait));
        $epargne_total->new_child("tot_mnt_retrait", afficheMontant($tot_mnt_retrait));
        $epargne_total->new_child("tot_nbre_encours_epargne", afficheMontant($tot_nbre_encours_ep));
        $epargne_total->new_child("tot_mnt_encours_epargne", afficheMontant($tot_mnt_encours_ep));
      }
    }
  }

  $output = $document->dump_mem(true);
  return $output;
}
  ?>