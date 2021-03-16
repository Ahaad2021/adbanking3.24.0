<?php

/* vim: set expandtab softtabstop=2 shiftwidth=2: */
/**
 * Génère le code XML pour les rapports multi_agences
 * @package Rapports
 */

require_once 'ad_ma/app/models/Divers.php';
require_once 'ad_ma/app/models/AuditVisualisation.php';
require_once 'lib/misc/xml_lib.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/rapports.php';
require_once 'modules/rapports/xslt.php';


function xml_situation_compensation($DATAS, $criteres, $rapport_pdf = false)
{
  global $global_id_agence, $global_monnaie;
  $agenceDatas = getAgenceDatas($global_id_agence);
  $nom_agence_local = $agenceDatas['libel_ag'];


  $document = create_xml_doc("situation_compensation", "situation_compensation.dtd");
  $code_rapport = 'RMA-SCP';

  //Element root
  $root = $document->root();
  //En-tête généraliste
  $header_nom_agence = " " . $nom_agence_local;
  $ref = gen_header($root, $code_rapport, $header_nom_agence);

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  $criteres_header = $criteres;
  unset($criteres_header['criteres_recherche']);
  gen_criteres_recherche($header_contextuel, $criteres_header);

  //Corps du rapport
  $compensations_par_agence = $root->new_child("compensations_par_agence", "");

  // le corps du xml
  $devise_count = 0;
  $devise_devise_count = 0; //en devise international
  $devise_ref_count = 0; //en devise local
  $summary_devise[] = array();
  $summary_devise_reference[] = array();
  $msg_summary[] = array();

  foreach($DATAS as $devise => $DATA)
  {
    foreach($DATA as $id_agence_externe => $details)
    {
      $agc_local_solde_deb = $details['situation_local_data']['solde_deb'];
      $agc_distant_solde_deb = $details['situation_distant_data']['solde_deb'];

      $agc_local_solde_fin = $details['situation_local_data']['solde_fin'];
      $agc_distant_solde_fin = $details['situation_distant_data']['solde_fin'];

      $agc_local_mvmts_deb = $details['situation_local_data']['mvmts_deb'];
      $agc_local_mvmts_cred = $details['situation_local_data']['mvmts_cred'];
      $agc_distant_mvmts_deb = $details['situation_distant_data']['mvmts_deb'];
      $agc_distant_mvmts_cred = $details['situation_distant_data']['mvmts_cred'];
      // ajout des donnees pour comm_od
      $agc_local_comm_od = $details['situation_local_data']['solde_comm_od'];
      $agc_distant_comm_od = $details['situation_distant_data']['solde_comm_od'];

      // la synthese
      if(abs($agc_local_solde_fin) == abs($agc_distant_solde_fin) && (!empty($agc_local_solde_fin)))
      {
        if($agc_local_solde_fin > $agc_distant_solde_fin) {
          $agence_debitrice = $details['situation_local_data']['agence_distant'];
          $agence_creditrice =  $details['situation_local_data']['agence_local'];
          $montant_du = abs($details['situation_local_data']['solde_fin']);
        }
        else {
          $agence_debitrice = $details['situation_distant_data']['agence_local'];
          $agence_creditrice = $details['situation_distant_data']['agence_distant'];
          $montant_du = abs($details['situation_distant_data']['solde_fin']);
        }

        // Multi devise
        $msg_summary[$devise_count]['agc'] = $agence_debitrice;
        $montant_du_dev = "";
        if ($devise != $global_monnaie) {
          $msg_summary[$devise_count]['mnt'] = calculeCV($devise, $global_monnaie, $montant_du);
          $montant_du_dev = ' ( '.Divers::afficheMontant($msg_summary[$devise_count]['mnt'], $global_monnaie, false, 2).' )';

          if($montant_du>0){ //liste détail agences et montants en devise(EUR, DOLLAR etc) pour message summary
            $summary_devise[$devise_devise_count]["devise"] = $devise;
            $summary_devise[$devise_devise_count]["agence_local"] = $details['situation_local_data']['agence_local'];
            $summary_devise[$devise_devise_count]["agence_distant"] = $details['situation_local_data']['agence_distant'];
            $summary_devise[$devise_devise_count]["agence_debit"] = $agence_debitrice;
            $summary_devise[$devise_devise_count]["agence_credit"] = $agence_creditrice;
            $summary_devise[$devise_devise_count]["montant"] = $msg_summary[$devise_count]['mnt'];
          }
          $devise_devise_count++;

        } else {
          $msg_summary[$devise_count]['mnt'] = $montant_du;

          if($montant_du>0){ //liste détail agences et montants en devise de reference pour message summary
            $summary_devise_reference[$devise_ref_count]["devise"] = $devise;
            $summary_devise_reference[$devise_ref_count]["agence_local"] = $details['situation_local_data']['agence_local'];
            $summary_devise_reference[$devise_ref_count]["agence_distant"] = $details['situation_local_data']['agence_distant'];
            $summary_devise_reference[$devise_ref_count]["agence_debit"] = $agence_debitrice;
            $summary_devise_reference[$devise_ref_count]["agence_credit"] = $agence_creditrice;
            $summary_devise_reference[$devise_ref_count]["montant"] = $msg_summary[$devise_count]['mnt'];
          }
          $devise_ref_count++;

        }

        $montant_du = Divers::afficheMontant($montant_du, $devise, false, 2).$montant_du_dev;

        $msg_synthese = sprintf("%s doit au %s, le montant de %s", $agence_debitrice, $agence_creditrice, $montant_du);
      }
      else if(!empty($agc_local_solde_fin) && !empty($agc_distant_solde_fin)) {
        if($rapport_pdf) {
          $msg_synthese = _("Attention, la valeur absolue des deux soldes n'est pas égale, vérifiez les écritures sur les comptes de liaison !");
        }
        else {
          $msg_synthese = _("Attention la valeur absolue des deux soldes n'est pas égale vérifiez les écritures sur les comptes de liaison !");
        }
      }
      else {
        $msg_synthese = _("Il n'y a pas de compensation à faire.");
      }

      // Situation local
      // xml nodes local :
      $situation_agence = $compensations_par_agence->new_child("situation_agence", "");

      $total_depot = $details['situation_local_data']['total_depot'];
      $total_retrait = $details['situation_local_data']['total_retrait'];

      if($rapport_pdf) {
        !empty($total_depot) ? $total_depot = Divers::afficheMontant($total_depot, $devise, false, 2) : $total_depot = 0;
        !empty($total_retrait) ? $total_retrait = Divers::afficheMontant($total_retrait, $devise, false, 2) : $total_retrait = 0;
        !empty($agc_local_solde_deb) ? $agc_local_solde_deb = Divers::afficheMontant($agc_local_solde_deb, $devise, false, 2) : $agc_local_solde_deb = 0;
        !empty($agc_local_solde_fin) ? $agc_local_solde_fin = Divers::afficheMontant($agc_local_solde_fin, $devise, false, 2) : $agc_local_solde_fin = 0;
        !empty($agc_local_mvmts_deb) ? $agc_local_mvmts_deb = Divers::afficheMontant($agc_local_mvmts_deb, $devise, false, 2) : $agc_local_mvmts_deb = 0;
        !empty($agc_local_mvmts_cred) ? $agc_local_mvmts_cred = Divers::afficheMontant($agc_local_mvmts_cred, $devise, false, 2) : $agc_local_mvmts_cred = 0;
        !empty($agc_local_comm_od) ? $agc_local_comm_od = Divers::afficheMontant($agc_local_comm_od, $devise, false, 2) : $agc_local_comm_od = 0;
      }

      $situation_local = $situation_agence->new_child("situation_local", "");
      $donnees_agc_loc = $situation_local->new_child("donnees_agence", "");
      $donnees_agc_loc_nom_agc_loc = $donnees_agc_loc->new_child("agence_local", $details['situation_local_data']['agence_local']);
      $donnees_agc_loc_code_devise_loc = $donnees_agc_loc->new_child("code_devise_local", $devise);
      $donnees_agc_loc_nom_agc_dist = $donnees_agc_loc->new_child("agence_distant", $details['situation_local_data']['agence_distant']);
      $donnees_agc_loc_code_devise_dist = $donnees_agc_loc->new_child("code_devise_distant", $devise);
      $donnees_agc_loc_total_depot = $donnees_agc_loc->new_child("total_depot", $total_depot);
      $donnees_agc_loc_total_retrait = $donnees_agc_loc->new_child("total_retrait",$total_retrait);
      $donnees_agc_loc_cpte_liaison = $donnees_agc_loc->new_child("cpte_liaison", $details['situation_local_data']['cpte_liaison']);
      $donnees_agc_loc_solde_deb = $donnees_agc_loc->new_child("solde_deb", $agc_local_solde_deb);
      $donnees_agc_loc_solde_fin = $donnees_agc_loc->new_child("solde_fin", $agc_local_solde_fin);
      $donnees_agc_loc_mvmts_deb = $donnees_agc_loc->new_child("mvmts_deb", $agc_local_mvmts_deb);
      if ($devise == $global_monnaie) {
        $donnees_agc_loc_comm_od_deplace = $donnees_agc_loc->new_child("comm_od_deplace", $agc_local_comm_od);
      } else{
        $donnees_agc_loc_comm_od_deplace = $donnees_agc_loc->new_child("comm_od_deplace", 0);
      }



      // Situation distant
      $situation_distant = $situation_agence->new_child("situation_distant", "");
      $donnees_agc_dist = $situation_distant->new_child("donnees_agence", "");

      $total_depot = $details['situation_distant_data']['total_depot'];
      $total_retrait = $details['situation_distant_data']['total_retrait'];

      if($rapport_pdf) {
        !empty($total_depot) ? $total_depot = Divers::afficheMontant($total_depot, $devise, false, 2) : $total_depot = 0;
        !empty($total_retrait) ? $total_retrait = Divers::afficheMontant($total_retrait, $devise, false, 2) : $total_retrait = 0;

        !empty($agc_distant_solde_deb) ? $agc_distant_solde_deb = Divers::afficheMontant($agc_distant_solde_deb, $devise, false, 2) : $agc_distant_solde_deb = 0;
        !empty($agc_distant_solde_fin) ? $agc_distant_solde_fin = Divers::afficheMontant($agc_distant_solde_fin, $devise, false, 2) : $agc_distant_solde_fin = 0;
        !empty($agc_distant_mvmts_deb) ? $agc_distant_mvmts_deb = Divers::afficheMontant($agc_distant_mvmts_deb, $devise, false, 2) : $agc_distant_mvmts_deb = 0;
        !empty($agc_distant_mvmts_cred) ? $agc_distant_mvmts_cred = Divers::afficheMontant($agc_distant_mvmts_cred, $devise, false, 2) : $agc_distant_mvmts_cred = 0;
        !empty($agc_distant_comm_od) ? $agc_distant_comm_od = Divers::afficheMontant($agc_distant_comm_od, $devise, false, 2) : $agc_distant_comm_od = 0;

      }

      $donnees_agc_dist_nom_agc_loc = $donnees_agc_dist->new_child("agence_local", $details['situation_distant_data']['agence_local']);
      $donnees_agc_dist_code_devise_loc = $donnees_agc_dist->new_child("code_devise_local", $devise);
      $donnees_agc_dist_nom_agc_dist = $donnees_agc_dist->new_child("agence_distant", $details['situation_distant_data']['agence_distant']);
      $donnees_agc_dist_code_devise_dist = $donnees_agc_dist->new_child("code_devise_distant", $devise);
      $donnees_agc_dist_total_depot = $donnees_agc_dist->new_child("total_depot", $total_depot);
      $donnees_agc_dist_total_retrait = $donnees_agc_dist->new_child("total_retrait", $total_retrait);
      $donnees_agc_dist_total_retrait = $donnees_agc_dist->new_child("cpte_liaison", $details['situation_distant_data']['cpte_liaison']);
      $ddonnees_agc_dist_solde_deb = $donnees_agc_dist->new_child("solde_deb", $agc_distant_solde_deb);
      $ddonnees_agc_dist_solde_fin = $donnees_agc_dist->new_child("solde_fin", $agc_distant_solde_fin);
      $donnees_agc_dist_mvmts_deb = $donnees_agc_dist->new_child("mvmts_deb", $agc_distant_mvmts_deb);
      $donnees_agc_dist_mvmts_cred = $donnees_agc_dist->new_child("mvmts_cred", $agc_distant_mvmts_cred);
      if ($devise== $global_monnaie) {
        $donnees_agc_dist_comm_od_deplace = $donnees_agc_dist->new_child("comm_od_deplace", $agc_distant_comm_od);
      } else {
        $donnees_agc_dist_comm_od_deplace = $donnees_agc_dist->new_child("comm_od_deplace", 0);
      }

      // synthese
      $synthese = $situation_agence->new_child("synthese", $msg_synthese);
    }
    $devise_count++;
  }

  // Multi devise
  $total_diff = 0;
  if ($devise_count > 1) { //resumé montant total dont l'agence doit au different agences en devise de reference
    if (sizeof($summary_devise) > 0) {
      for ($i = 0; $i < sizeof($summary_devise); $i++) {
        for ($j = 0; $j < sizeof($summary_devise_reference); $j++) {
          if ($summary_devise[$i]['agence_distant'] == $summary_devise_reference[$j]['agence_distant']){
            $total_diff = $summary_devise[$i]['montant'] - $summary_devise_reference[$j]['montant'];
            if ($summary_devise[$i]['agence_debit'] == $summary_devise_reference[$j]['agence_debit']){
              $total_diff = $summary_devise[$i]['montant'] + $summary_devise_reference[$j]['montant'];
            }
            $total_diff = Divers::afficheMontant(abs($total_diff), $global_monnaie, false, 2);
            $info = sprintf("%s doit au %s, le montant de %s", $summary_devise[$i]['agence_debit'], $summary_devise[$i]['agence_credit'], $total_diff);
            if (sizeof($summary_devise[$i]) == 0 && sizeof($summary_devise_reference[$j]) == 0){
              $info = "Il n'y a pas de compensation à faire";
            }
            $summary = $compensations_par_agence->new_child("summary", "");
            $summary_info = $summary->new_child("summary_info", ($info));
          }
          else if ($summary_devise[$i]['agence_distant'] != $summary_devise_reference[$j]['agence_distant']) {
            if (sizeof($summary_devise[$i]) == 0 || sizeof($summary_devise) == 1){
              $total_diff = $summary_devise_reference[$j]['montant'];
              $total_diff = Divers::afficheMontant(abs($total_diff), $global_monnaie, false, 2);
              $info = sprintf("%s doit au %s, le montant de %s", $summary_devise_reference[$j]['agence_debit'], $summary_devise_reference[$j]['agence_credit'], $total_diff);
              if (sizeof($summary_devise_reference[$j]) != 0){
                $summary = $compensations_par_agence->new_child("summary", "");
                $summary_info = $summary->new_child("summary_info", ($info));
              }
            }
            if (sizeof($summary_devise_reference[$j]) == 0 || sizeof($summary_devise_reference) == 1){
              $total_diff = $summary_devise[$i]['montant'];
              $total_diff = Divers::afficheMontant(abs($total_diff), $global_monnaie, false, 2);
              $info = sprintf("%s doit au %s, le montant de %s", $summary_devise[$i]['agence_debit'], $summary_devise[$i]['agence_credit'], $total_diff);
              if (sizeof($summary_devise[$i]) != 0){
                $summary = $compensations_par_agence->new_child("summary", "");
                $summary_info = $summary->new_child("summary_info", ($info));
              }
            }
          }
          else{
            //do nothing
          }
        }
      }
    }
  }
  // Multi devise
  /*if ($devise_count > 1) {print_rn($msg_summary);

    $total_diff = ($msg_summary[0]['mnt'] - $msg_summary[1]['mnt']);

    if ($total_diff > 0) {
      $agc1 = $msg_summary[0]['agc'];
      $agc2 = $msg_summary[1]['agc'];
    } else {
      $agc1 = $msg_summary[1]['agc'];
      $agc2 = $msg_summary[0]['agc'];
    }

    $total_diff = Divers::afficheMontant(abs($total_diff), $global_monnaie, false, 2);

    $msg_summary = sprintf("%s doit au %s, le montant de %s", $agc1, $agc2, $total_diff);

    $summary = $compensations_par_agence->new_child("summary", ($msg_summary));
  }*/

  $output = $document->dump_mem(true);

  return($output);
}

function xml_situation_compensation_siege($DATAS, $criteres, $rapport_pdf = false)
{
  global $global_id_agence, $global_monnaie;
  /*global $mnt_sep_mil,$mnt_sep_mil_csv;
  $mnt_sep_mil = ' ';
  $mnt_sep_mil_csv = ' ';
  $mnt_sep_mil = ',';
  $mnt_sep_mil_csv = ',';*/
  $agenceDatas = getAgenceDatas($global_id_agence);
  $nom_agence_local = $agenceDatas['libel_ag'];

  $document = create_xml_doc("situation_compensation_siege", "situation_compensation_siege.dtd");
  $code_rapport = 'RMA-SCP';

  //Element root
  $root = $document->root();
  //En-tête généraliste
  $header_nom_agence = " " . $nom_agence_local;
  $ref = gen_header($root, $code_rapport, $header_nom_agence);

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  $criteres_header = $criteres;
  unset($criteres_header['criteres_recherche']);
  gen_criteres_recherche($header_contextuel, $criteres_header);

  //Corps du rapport
  $agc_local_solde_deb =0;
  $agc_local_mvmts_deb =0;
  $agc_local_mvmts_cred =0;
  $solde_operations_distantes =0;
  $solde_operations_locales =0;
  $solde_compensation_globale =0;
  $solde_fin =0;

  foreach($DATAS as $devise => $DATA)
  {
    foreach($DATA as $id_agence_externe => $details)
    {
      $agc_local_solde_deb = arrondiMonnaiePrecision($details['situation_local_data']['solde_deb'],$devise);

      $solde_operations_locales += arrondiMonnaiePrecision($details['situation_local_data']['total_depot'],$devise) - arrondiMonnaiePrecision($details['situation_local_data']['total_retrait'],$devise);
      $solde_operations_distantes += arrondiMonnaiePrecision($details['situation_distant_data']['total_depot'],$devise) - arrondiMonnaiePrecision($details['situation_distant_data']['total_retrait'],$devise);

      $agc_local_mvmts_deb = arrondiMonnaiePrecision($details['situation_local_data']['mvmts_deb'],$devise);
      $agc_local_mvmts_cred = arrondiMonnaiePrecision($details['situation_local_data']['mvmts_cred'],$devise);

      //multi-devise
      if ($devise != $global_monnaie) {
        $solde_operations_locales += arrondiMonnaiePrecision(calculeCV($devise, $global_monnaie, $details['situation_local_data']['total_depot']),$devise) - arrondiMonnaiePrecision(calculeCV($devise, $global_monnaie, $details['situation_local_data']['total_retrait']),$devise);
        $solde_operations_distantes += arrondiMonnaiePrecision(calculeCV($devise, $global_monnaie, $details['situation_distant_data']['total_depot']),$devise) - arrondiMonnaiePrecision(calculeCV($devise, $global_monnaie, $details['situation_distant_data']['total_retrait']),$devise);

        $agc_local_mvmts_deb = arrondiMonnaiePrecision(calculeCV($devise, $global_monnaie, $details['situation_local_data']['mvmts_deb']),$devise);
        $agc_local_mvmts_cred = arrondiMonnaiePrecision(calculeCV($devise, $global_monnaie, $details['situation_local_data']['mvmts_cred']),$devise);
      }
      $solde_compensation_globale = $solde_operations_distantes - $solde_operations_locales;
    }
  }

  $devise_global = $global_monnaie;
  $solde_fin = $agc_local_solde_deb + $solde_compensation_globale + $agc_local_mvmts_deb - $agc_local_mvmts_cred;

  // le corps du xml

  $devise_global = $root->new_child("devise", $devise_global);

  if($rapport_pdf) {
    !empty($agc_local_solde_deb) ? $agc_local_solde_deb=Divers::afficheMontant($agc_local_solde_deb, $global_monnaie, false, 0) : $agc_local_solde_deb = '0 '.$global_monnaie;
  }

  $agc_local_solde_deb = $root->new_child("solde_deb", $agc_local_solde_deb);

  $situation_agence = $root->new_child("situation_agence", "");

  foreach($DATAS as $devise => $DATA)
  {
    foreach($DATA as $id_agence_externe => $details)
    {
      $compensations_par_agence = $situation_agence->new_child("compensations_par_agence", "");
      $nom_agence = $compensations_par_agence->new_child("nom_agence", $nom_agence_local);
      $title = sprintf("Compensation avec Agence %s (%s)", $details['situation_local_data']['agence_distant'], $devise);
      $title = $compensations_par_agence->new_child("title", $title);
      $situation_local = $compensations_par_agence->new_child("situation_local", "");
      $situation_distant = $compensations_par_agence->new_child("situation_distant", "");

      //recuperation donnees situation local et distant
      $total_depot_local = arrondiMonnaiePrecision($details['situation_local_data']['total_depot'],$devise);
      $total_retrait_local = arrondiMonnaiePrecision($details['situation_local_data']['total_retrait'],$devise);
      $total_depot_distant = arrondiMonnaiePrecision($details['situation_distant_data']['total_depot'],$devise);
      $total_retrait_distant = arrondiMonnaiePrecision($details['situation_distant_data']['total_retrait'],$devise);
      $total_commission_retait_distant = arrondiMonnaiePrecision($details['situation_distant_data']['solde_comm_od_retrait'],$devise);
      $total_commission_depot_distant = arrondiMonnaiePrecision($details['situation_distant_data']['solde_comm_od_depot'],$devise);
      $total_commission_retait_local = arrondiMonnaiePrecision($details['situation_local_data']['solde_comm_od_retrait'],$devise);
      $total_commission_depot_local = arrondiMonnaiePrecision($details['situation_local_data']['solde_comm_od_depot'],$devise);
      $solde_operations_local = $details['situation_local_data']['total_depot'] - $details['situation_local_data']['total_retrait'];
      $solde_operations_distant = $details['situation_distant_data']['total_depot'] - $details['situation_distant_data']['total_retrait'];
      $solde_compensation_local = $solde_operations_distant - $solde_operations_local;
      $montant_doit = abs($solde_compensation_local);
      $agence_perdant = $details['situation_local_data']['agence_distant'];
      $agence_gagnant = $details['situation_local_data']['agence_local'];

      if ($solde_compensation_local<0){
        if($rapport_pdf) {
          !empty($solde_compensation_local) ? $solde_compensation_local = '( ' . Divers::afficheMontant(abs($solde_compensation_local), $devise, false, 0) . ' )' : $solde_compensation_local = '0 ' . $devise;
        }
        $agence_perdant = $details['situation_local_data']['agence_local'];
        $agence_gagnant = $details['situation_local_data']['agence_distant'];
      }
      else{
        if($rapport_pdf) {
          !empty($solde_compensation_local) ? $solde_compensation_local=Divers::afficheMontant($solde_compensation_local, $devise, false, 0) : $solde_compensation_local = '0 '.$devise;
        }
      }

      //multi-devise
      $montant_doit_dev = '';
      if ($devise != $global_monnaie) {
        $montant_doit_dev = calculeCV($devise, $global_monnaie, $montant_doit);
        if($rapport_pdf) {
          !empty($montant_doit_dev) ? $montant_doit_dev = '( ' . Divers::afficheMontant(abs($montant_doit_dev), $global_monnaie, false, 0) . ' )' : $montant_doit_dev = '0 ' . $devise;
        }
      }

      //population des donnees pour situation distant
      $nom_agence_distant = $situation_distant->new_child("nom_agence_distant", $details['situation_local_data']['agence_distant']);
      if($rapport_pdf) {
        !empty($total_depot_distant) ? $total_depot_distant = Divers::afficheMontant($total_depot_distant, $devise, false, 0) : $total_depot_distant = '0 ' . $devise;
      }
      $total_depot_distant = $situation_distant->new_child("total_depot", $total_depot_distant);
      if($rapport_pdf) {
        !empty($total_retrait_distant) ? $total_retrait_distant = Divers::afficheMontant($total_retrait_distant, $devise, false, 0) : $total_retrait_distant = '0 ' . $devise;
      }
      $total_retrait_distant = $situation_distant->new_child("total_retrait", $total_retrait_distant);

      if($rapport_pdf) {
        !empty($total_commission_depot_local) ? $total_commission_depot_local = Divers::afficheMontant($total_commission_depot_local, $devise, false, 0) : $total_commission_depot_local = '0 ' . $devise;
      }
      $comm_od_depot_local = $situation_local->new_child("comm_od_depot_local", $total_commission_depot_local);
      if($rapport_pdf) {
        !empty($total_commission_retait_local) ? $total_commission_retait_local = Divers::afficheMontant($total_commission_retait_local, $devise, false, 0) : $total_commission_retait_local = '0 ' . $devise;
      }
      $comm_od_retrait_local = $situation_local->new_child("comm_od_retrait_local", $total_commission_retait_local);

      $cpte_liaison_distant = $situation_distant->new_child("cpte_liaison", $details['situation_distant_data']['cpte_liaison']);
      if ($solde_operations_distant < 0){
        if ($rapport_pdf){
          !empty($solde_operations_distant) ? $solde_operations_distant = '( '.Divers::afficheMontant(abs($solde_operations_distant), $devise, false, 0).' )' : $solde_operations_distant = '0 ' . $devise;
        }
      }
      else{
        if ($rapport_pdf){
          !empty($solde_operations_distant) ? $solde_operations_distant = Divers::afficheMontant($solde_operations_distant, $devise, false, 0) : $solde_operations_distant = '0 ' . $devise;
        }
      }
      $solde_operation_distant = $situation_distant->new_child("solde_operation_distant", $solde_operations_distant);

      //population des donnees pour situation local
      $nom_agence_distant = $situation_local->new_child("nom_agence_distant", $details['situation_local_data']['agence_distant']);
      if($rapport_pdf) {
        !empty($total_depot_local) ? $total_depot_local = Divers::afficheMontant($total_depot_local, $devise, false, 0) : $total_depot_local = '0 ' . $devise;
      }
      $total_depot_local = $situation_local->new_child("total_depot", $total_depot_local);
      if($rapport_pdf) {
        !empty($total_retrait_local) ? $total_retrait_local = Divers::afficheMontant($total_retrait_local, $devise, false, 0) : $total_retrait_local = '0 ' . $devise;
      }
      $total_retrait_local = $situation_local->new_child("total_retrait", $total_retrait_local);

     if($rapport_pdf) {
        !empty($total_commission_depot_distant) ? $total_commission_depot_distant = Divers::afficheMontant($total_commission_depot_distant, $devise, false, 0) : $total_commission_depot_distant = '0 ' . $devise;
      }
      $comm_od_depot_distant = $situation_distant->new_child("comm_od_depot_distant", $total_commission_depot_distant);
      if($rapport_pdf) {
        !empty($total_commission_retait_distant) ? $total_commission_retait_distant = Divers::afficheMontant($total_commission_retait_distant, $devise, false, 0) : $total_commission_retait_distant = '0 ' . $devise;
     }
      $comm_od_retrait_distant = $situation_distant->new_child("comm_od_retrait_distant", $total_commission_retait_distant);

      $cpte_liaison_local = $details['situation_local_data']['cpte_liaison'].' ';
      $cpte_liaison_local = $situation_local->new_child("cpte_liaison", $cpte_liaison_local);
      if ($solde_operations_local < 0) {
        if ($rapport_pdf) {
          !empty($solde_operations_local) ? $solde_operations_local = '( '.Divers::afficheMontant(abs($solde_operations_local), $devise, false, 0).' )' : $solde_operations_local = '0 ' . $devise;
        }
      }
      else{
          if ($rapport_pdf) {
            !empty($solde_operations_local) ? $solde_operations_local = Divers::afficheMontant($solde_operations_local, $devise, false, 0) : $solde_operations_local = '0 ' . $devise;
          }
      }
      $solde_operations_local = $situation_local->new_child("solde_operation_local", $solde_operations_local);
      $solde_compensation_local = $situation_local->new_child("solde_compensation_local", $solde_compensation_local);

      //message synthese
      $msg_synthese = '';
      $msg_synthese = sprintf("Il n'y a pas de compensation a faire!");
      if ($montant_doit > 0){
        $montant_doit=Divers::afficheMontant($montant_doit, $devise, false, 0).$montant_doit_dev;
        $msg_synthese = sprintf("Agence %s doit à Agence %s, le montant de %s", $agence_perdant, $agence_gagnant, $montant_doit);
      }
      $msg_synthese = $compensations_par_agence->new_child("synthese", $msg_synthese);

    }
  }

  if ($solde_compensation_globale < 0) {
    if ($rapport_pdf) {
      !empty($solde_compensation_globale) ? $solde_compensation_globale = '( '.Divers::afficheMontant(abs($solde_compensation_globale), $devise, false, 0).' )' : $solde_compensation_globale = '0 ' . $devise;
    }
  }
  else{
    if ($rapport_pdf) {
      !empty($solde_compensation_globale) ? $solde_compensation_globale = Divers::afficheMontant($solde_compensation_globale, $devise, false, 0) : $solde_compensation_globale = '0 ' . $devise;
    }
  }
  $solde_compensation_globale = $root->new_child("solde_compensation_global", $solde_compensation_globale);

  if($rapport_pdf) {
    !empty($agc_local_mvmts_deb) ? $agc_local_mvmts_deb = Divers::afficheMontant($agc_local_mvmts_deb, $global_monnaie, false, 0) : $agc_local_mvmts_deb = '0 ' . $devise;
  }
  $agc_local_mvmts_deb = $root->new_child("mvmts_deb", $agc_local_mvmts_deb);

  if($rapport_pdf) {
    !empty($agc_local_mvmts_cred) ? $agc_local_mvmts_cred = Divers::afficheMontant($agc_local_mvmts_cred, $global_monnaie, false, 0) : $agc_local_mvmts_cred = '0 ' . $devise;
  }
  $agc_local_mvmts_cred = $root->new_child("mvmts_cred", $agc_local_mvmts_cred);

  if($rapport_pdf) {
    !empty($solde_fin) ? $solde_fin = Divers::afficheMontant($solde_fin, $global_monnaie, false, 0) : $solde_fin = '0 ' . $devise;
  }
  $solde_fin = $root->new_child("solde_fin", $solde_fin);

  /*$mnt_sep_mil = ' ';
  $mnt_sep_mil_csv = ' ';*/

  $output = $document->dump_mem(true);
  return($output);
}

?>