<?php

/* vim: set expandtab softtabstop=2 shiftwidth=2: */
/**
 * Génère le code XML pour les rapports multi_agences
 * @package Rapports
 */

require_once 'ad_ma/app/models/Divers.php';
require_once 'ad_ma/app/models/AgenceRemote.php';
require_once 'ad_ma/app/models/AuditVisualisation.php';
require_once 'lib/misc/xml_lib.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/rapports.php';
require_once 'modules/rapports/xslt.php';


/**
 * Genere le xml pour le rapport états des chequiers imprimés
 *
 * @param $DATAS
 * @param $criteres
 * @return string
 */
function xml_chequiers_en_opposition($DATAS, $criteres)
{
  global $adsys;

  $document = create_xml_doc("chequiers_en_opposition", "chequiers_opposition.dtd");
  $code_rapport = 'RCQ-CMO';

  $count_chequiers = count($DATAS);
  if (is_null($count_chequiers)) $count_chequiers = "0";

  //Element root
  $root = $document->root();

  //En-tête généraliste
  $ref = gen_header($root, $code_rapport);

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  $criteres_header = $criteres;
  gen_criteres_recherche($header_contextuel, $criteres_header);

  // Infos synthetiques
  $infos_synthetique = $root->new_child("infos_synthetique", "");
  $nb_chequiers_en_opposition = $infos_synthetique->new_child("nb_chequiers_en_opposition", $DATAS['nb_chequiers_en_opposition']);
  $nb_cheques_en_opposition = $infos_synthetique->new_child("nb_cheques_en_opposition", $DATAS['nb_cheques_en_opposition']);

//  $syntheses_par_etat = $DATAS['syntheses_par_etat'];
//
//  // Recap de tous les etats
//  foreach($adsys['adsys_etat_chequier'] as $id_etat=>$tablesys_etat) {
//    $ligne_synthese = $infos_synthetique->new_child("ligne_synthese", "");
//    $etat_cheq = $ligne_synthese->new_child("etat_cheq", $tablesys_etat);
//    $nb = $syntheses_par_etat[$id_etat]['nb_chequier'];
//    if(empty($nb)) $nb = "0";
//    $nb_chequiers = $ligne_synthese->new_child("nb_chequiers", $nb);
//  }

  //Corps du rapport - donnees detaillés
  $chequiers_opposition_data = $root->new_child("chequiers_opposition_data", "");
  if($count_chequiers > 0){
    foreach ($DATAS['chequiers_opposition_data'] as $ligne){
      $ligne_chequier = $chequiers_opposition_data->new_child("ligne_chequier", "");
      $num_client = $ligne_chequier->new_child("num_client", $ligne['id_client']);
      $num_cpte = $ligne_chequier->new_child("num_cpte", $ligne['num_complet_cpte']);
      $nom_client = $ligne_chequier->new_child("nom_client", $ligne['nom_client']);
      $date_opposition = $ligne_chequier->new_child("date_opposition", $ligne['date_statut']);
      $id_chequier = $ligne_chequier->new_child("id_chequier", $ligne['id_chequier']);
      $num_deb_cheq = $ligne_chequier->new_child("num_deb_cheq", $ligne['num_first_cheque']);
      $num_fin_cheq = $ligne_chequier->new_child("num_fin_cheq", $ligne['num_last_cheque']);
      $etat_description = $ligne_chequier->new_child("description", $ligne['description']);
      $etat_chequier = $ligne_chequier->new_child("etat_chequier", $ligne['etat_chequier']);
    }
  }

  $cheques_opposition_data = $root->new_child("cheques_opposition_data", "");
  if($count_chequiers > 0){
    foreach ($DATAS['cheques_opposition_data'] as $ligne){
      $ligne_cheque = $cheques_opposition_data->new_child("ligne_cheque", "");
      $num_client_ch = $ligne_cheque->new_child("num_client_ch", $ligne['id_client']);
      $num_cpte_ch = $ligne_cheque->new_child("num_cpte_ch", $ligne['num_complet_cpte']);
      $nom_client_ch = $ligne_cheque->new_child("nom_client_ch", $ligne['nom_client']);
      $date_opposition_ch = $ligne_cheque->new_child("date_opposition_ch", $ligne['date_opposition']);
      $id_cheque_ch = $ligne_cheque->new_child("id_cheque_ch", $ligne['id_cheque']);
      $libel_etat_cheque_ch = $ligne_cheque->new_child("libel_etat_cheque_ch", $ligne['libel_etat_cheque_ch']);
      $description_ch = $ligne_cheque->new_child("description_ch", $ligne['description']);
    }
  }

  $output = $document->dump_mem(true);
  return($output);
}


/**
 * Genere le xml pour le rapport états des chequiers imprimés
 *
 * @param $DATAS
 * @param $criteres
 * @return string
 */
function xml_etat_chequiers_imprime($DATAS, $criteres)
{
  global $adsys;

  $document = create_xml_doc("etat_chequiers_imprime", "etat_chequiers_imprime.dtd");
  $code_rapport = 'RCQ-ECI';

  $count_chequiers = count($DATAS);
  if (is_null($count_chequiers)) $count_chequiers = "0";

  //Element root
  $root = $document->root();

  //En-tête généraliste
  $ref = gen_header($root, $code_rapport);

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  $criteres_header = $criteres;
  gen_criteres_recherche($header_contextuel, $criteres_header);

  // Infos synthetiques
  $infos_synthetique = $root->new_child("infos_synthetique", "");
  $syntheses_par_etat = $DATAS['syntheses_par_etat'];

  // Recap de tous les etats
  foreach($adsys['adsys_etat_chequier'] as $id_etat=>$tablesys_etat) {
    if($id_etat==0 || $id_etat==1) {
      $ligne_synthese = $infos_synthetique->new_child("ligne_synthese", "");
      $etat_cheq = $ligne_synthese->new_child("etat_cheq", $tablesys_etat);
    }
    $nb = $syntheses_par_etat[$id_etat]['nb_chequier'];
    if(empty($nb)) $nb = "0";
    $nb_chequiers = $ligne_synthese->new_child("nb_chequiers", $nb);
  }

  //Corps du rapport - donnees detaillés
  $etat_chequiers_data = $root->new_child("etat_chequiers_data", "");
  if($count_chequiers > 0){
    foreach ($DATAS['etat_chequiers_data'] as $ligne){
      $ligne_chequier = $etat_chequiers_data->new_child("ligne_chequier", "");
      $ordre = $ligne_chequier->new_child("ordre", $ligne['row_counter']);
      $num_client = $ligne_chequier->new_child("num_client", $ligne['id_client']);
      $num_cpte = $ligne_chequier->new_child("num_cpte", $ligne['num_complet_cpte']);
      $nom_client = $ligne_chequier->new_child("nom_client", $ligne['nom_client']);
      $date_livraison = $ligne_chequier->new_child("date_livraison", $ligne['date_livraison']);
      $id_chequier = $ligne_chequier->new_child("id_chequier", $ligne['id_chequier']);
      $num_deb_cheq = $ligne_chequier->new_child("num_deb_cheq", $ligne['num_first_cheque']);
      $num_fin_cheq = $ligne_chequier->new_child("num_fin_cheq", $ligne['num_last_cheque']);
      $nb_cheq = $ligne_chequier->new_child("nb_cheq", $ligne['nb_cheque']);
      $etat_chequier = $ligne_chequier->new_child("etat_chequier", $ligne['etat_chequier']);
    }
  }

  $output = $document->dump_mem(true);
  return($output);
}

/**
 *
 * Genere le xml pour les rapports liste des commandes chéquiers OU liste des chéquiers envoyés a l'impression
 *
 * @param $DATAS
 * @param $criteres
 * @param bool|true $isRapportCheqCmd : Si true c'est le rapport liste des commandes chequiers, sinon c'est le rapport
 *   chequiers envoyee a l'impression
 * @return string
 */
function xml_liste_chequiers_commande_envoye_impression($DATAS, $criteres, $isRapportCheqCmd = true)
{
  global $adsys, $global_monnaie;

  $document = create_xml_doc("chequiers_commande_envoye_impression", "chequiers_commande_envoye_impression.dtd");

  if($isRapportCheqCmd) {
    $code_rapport = 'RCQ-CCM';
  }
  else {
    $code_rapport = 'RCQ-CEI';
  }

  $count_chequiers = count($DATAS);
  if (is_null($count_chequiers)) $count_chequiers = "0";

  //Element root
  $root = $document->root();

  //En-tête généraliste
  $ref = gen_header($root, $code_rapport);

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  $criteres_header = $criteres;
  gen_criteres_recherche($header_contextuel, $criteres_header);

  // Infos synthetiques
  $infos_synthetique = $root->new_child("infos_synthetique", "");
  $syntheses_par_etat_cmd = $DATAS['syntheses_par_etat_cmd'];

  $type_rapport = ($isRapportCheqCmd ? 1 : 2);
  // Recap de tous les etats

  //foreach($adsys['adsys_etat_commande_chequier'] as $id_etat => $tablesys_etat) {
    if($type_rapport == 1) {
      $ligne_synthese = $infos_synthetique->new_child("ligne_synthese", "");
      $etat_cmd_cheq = $ligne_synthese->new_child("etat_cmd_cheq", $adsys['adsys_etat_commande_chequier'][1]);
      $nb = $syntheses_par_etat_cmd[1]['nb_chequier'];
      if (empty($nb)) $nb = "0";
      $nb_chequiers = $ligne_synthese->new_child("nb_chequiers", $nb);
    }
    if($type_rapport == 2) {
      $ligne_synthese = $infos_synthetique->new_child("ligne_synthese", "");
      $etat_cmd_cheq = $ligne_synthese->new_child("etat_cmd_cheq", $adsys['adsys_etat_commande_chequier'][2]);
      $nb = $syntheses_par_etat_cmd[2]['nb_chequier'];
      if (empty($nb)) $nb = "0";
      $nb_chequiers = $ligne_synthese->new_child("nb_chequiers", $nb);
    }
  //}


  //Corps du rapport - donnees detaillés

  $cmd_chequiers_data = $root->new_child("cmd_chequiers_data", "");

  // 1 : rapport liste des commandes chequiers; 2 : Liste des chequiers envoyés à l'impression

  $type_rapport = $cmd_chequiers_data->new_child("type_rapport", $type_rapport);
  $total_mnt = 0;
  $count_cheq_data = count($DATAS['cmd_chequiers_data']);
  if($count_cheq_data > 0){
    foreach ($DATAS['cmd_chequiers_data'] as $ligne){
      $ligne_cmd_chequier = $cmd_chequiers_data->new_child("ligne_cmd_chequier", "");
      $ordre = $ligne_cmd_chequier->new_child("ordre", $ligne['row_counter']);
      $num_client = $ligne_cmd_chequier->new_child("num_client", $ligne['id_client']);
      $num_cpte = $ligne_cmd_chequier->new_child("num_cpte", $ligne['num_complet_cpte']);
      $nom_client = $ligne_cmd_chequier->new_child("nom_client", $ligne['nom_client']);
      $date_commande = $ligne_cmd_chequier->new_child("date_commande", $ligne['date_cmde']);

      if($isRapportCheqCmd) {
        $frais = afficheMontant(recupMontant($ligne['frais']), $global_monnaie, true);
        !empty($frais) ? $frais = $frais : $frais = 0;
        $total_mnt += $frais;
        $frais = $ligne_cmd_chequier->new_child("frais", $frais);

      }
      else {
        $date_envoi_impr = $ligne_cmd_chequier->new_child("date_envoi_impr", $ligne['date_envoi_impr']);
      }
    }
      $total_frais = $ligne_cmd_chequier->new_child("total_frais", afficheMontant($total_mnt, $global_monnaie, true));

  }
  //$tot_frais = $total_frais_info->new_child("tot_frais", afficheMontant($total_mnt, $global_monnaie, true));
 // $infos_tot_frais = $root->new_child("infos_tot_frais", "");
  //$montant_total = $infos_tot_frais->new_child("montant_total", afficheMontant($total_mnt, $global_monnaie, true));



  $output = $document->dump_mem(true);
  return($output);
}


?>