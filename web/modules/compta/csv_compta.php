<?php

/**
 * Fonctions de création des fichiers CSV utilisés pour exporter des données comptables vers Excel
 *
 * @package Compta
 **/

require_once 'lib/dbProcedures/rapports.php';
require_once 'lib/dbProcedures/epargne.php';

/**
  * Construit une chaîne au format CSV pour l'export du journal comptable.
  *
  * @param array $CRIT Les critères d'extraction
  * @param array $DATA Les données à exporter
  * @return string La chaîne pour l'export
  */
function csv_extrait_journal_comptable($CRIT, $DATA) {
  global $adsys;

  $str = "";

  // Indication des critères d'extraction utilisés
  if (isset($CRIT["date_deb"]))
    $str .=_("Date début").";".$CRIT["date_deb"]."\n";
  if (isset($CRIT["date_fin"]))
    $str .=_("Date fin").";".$CRIT["date_fin"]."\n";
  if ($CRIT["type_operation"] > 0)
    $str .=_("Operation").";".adb_gettext($adsys["adsys_type_operation"][$CRIT["type_operation"]])."\n";
  if ($CRIT["compte"] > 0)
    $str .=_("Compte").";".$CRIT["compte"]."\n";

  // Première colonne contenant l'en-tête
  $str .= _("Date").";"._("Heure").";"._("N° transaction").";"._("Opération").";"._("Compte").";"._("Intitulé compte").";"._("Débit").";"._("Crédit").";"._("Client").";"._("Devise")."\n";
  $tempId_ecri = "";
  // Remplissage avec les données extraites
  while (list(,$ligne) = each($DATA)) {
    $str .= pg2phpDate($ligne["date_s"]).";".pg2phpHeure($ligne["date_s"]).";";
     $str.=$ligne["id_his"]." ; ";
      if($tempId_ecri!=$ligne["id_ecriture"]){ // operation differente
        if(intval($ligne["libel_ecriture"])){
            $libel_ecriture_trad = new Trad($ligne["libel_ecriture"]);
            $libel_ecriture = htmlspecialchars($libel_ecriture_trad->traduction(), ENT_QUOTES, "UTF-8");
        }else {
            $libel_ecriture=$ligne["libel_ecriture"];
        }
        $libel_ecriture = str_replace(';',',',$libel_ecriture);
        // Vérifier liste opération à modifier.
        if(in_array($ligne['type_operation'], $adsys["adsys_operation_cheque_infos"]) )
        {
          $libel_ecriture = getChequeno($ligne['id_his'],$libel_ecriture,$ligne['info_ecriture']);
        }

        $str.=$libel_ecriture." ; ";

      }else {
      	$str.=" ;";
      }
      $str.=$ligne["compte"].";".getLibelleValable($ligne["compte"],'ad_cpt_comptable',$ligne["date_s"])." ;";

      if ($ligne["sens"] == 'd')
				$str .= ($ligne["montant"]*1).";;";
      else
				$str .= ";".($ligne["montant"]*1).";";
      $str .= $ligne["id_client"].";";
      $str .= $ligne["devise"]."\n";

      $tempId_ecri=$ligne["id_ecriture"];
  }
  // Renvoi du string
  return $str;
}

/**
 * Fonction de création d'un rapport csv pour le bilan
 * @author Mamadou Mbaye
 * @param  array $DATA
 * @param  $date date du bilan
 * @return $str string pour le rapport csv
**/
function csv_bilan($DATA,$date) {
  global $adsys;

  $str = "";
  $str1 = "\n";

  $str .=_("Date")." : $date\n";

  // Première colonne contenant l'en-tête
  $str.= _("Actif")."\n"._("Compte[[compte bancaire]]").";"._("Libellé").";"._("Solde[[solde bancaire]]").",".("Amortissements[[amortissement d'un emprunt]]").";".("Net[[montant net]]")."\n";
  $str1.= " "._("Passif")."\n"._("Compte[[compte bancaire]]").";"._("Libellé").";"._("Solde[[solde bancaire]]")."\n";

  // Remplissage avec les données extraites
  while (list(,$ligne) = each($DATA)) {
    $str.="\"".$ligne["compte_actif"]."\";";
    $str.=$ligne["libel_actif"].";";
    $str.=$ligne["solde_actif"].";";
    $str.=$ligne["amort_actif"].";";
    $str.=$ligne["net_actif"].";\n";
    $str1 .= "\"".$ligne["compte_passif"]."\";";
    $str1.=$ligne["libel_passif"].";";
    $str1.=$ligne["solde_passif"].";\n";

  }
  $str.=$str1;

  // Renvoi du string
  return $str;
}

/**
 * Fonction de création d'un rapport csv pour la balance
 * @author Ares
 * @param  array $DATA
 * @param  $titre titre du bilan
 * @return $str string pour le rapport csv
**/


function csv_balance($DATA,$titre) {
  debug($DATA,"DATA");
  $str="\t\t\t\t"._("Balance[[balance des paiements]]")." ".$titre."\n\n";
  $str.=_("Libelé").";"._("Numéro").";"._("Solde début déb[[Solde début débit]]").";"._("Solde début créd[[Solde début crédit]]").";"._("Mouvements déb[[Mouvements débit]]").";"._("Mouvements créd[[Mouvements crédit]]").";"._("Total période déb[[Total période débit]]").";"._("Total période créd[[Total période crédit]]").";"._("Solde fin déb[[Solde fin débit]]").";"._("Solde fin créd[[Solde fin crédit]]").";"._("Variation")."\n";
  while (list($key, $values) = each($DATA)) {
    $str.="\t\t".sprintf(_("Balance en %s"),$key)."\n";
    $total_solde_periode_credit=0;
    $total_solde_periode_debit=0;
    $total_solde_mvt_credit=0;
    $total_solde_mvt_debit=0;
    $total_solde_debut_credit=0;
    $total_solde_debut_debit=0;
    $total_solde_fin_credit=0;
    $total_solde_fin_debit=0;
    while (list($numCompte, $info) = each($values)) {
      $str.=$info["libel"].";";
      $str.= $numCompte.";";
      if($info["solde_debut"]>0){
      	$str.= ";";//debit
        $str.= round($info["solde_debut"], 2).";";
        $total_solde_debut_credit+=$info["solde_debut"];
      }else{
      	$str.= round(abs($info["solde_debut"]), 2).";";
        $str.= ";";//credit
        $total_solde_debut_debit+=abs($info["solde_debut"]);
      }
      $total_solde_mvt_debit+=$info["total_debits"];
      $str.= round($info["total_debits"], 2).";";

      $total_solde_mvt_credit+=$info["total_credits"];
      $str.= round($info["total_credits"], 2).";";

      $solde_periode=recupMontant($info["total_credits"])-recupMontant($info["total_debits"]);
      if($solde_periode<0){
      	$str.= round(abs($solde_periode), 2).";";
        $str.= " ;";
        $total_solde_periode_debit+=abs($solde_periode);
      }else{
      	$str.= ";";//debit
        $str.= round(abs($solde_periode), 2)." ;";//credit
        $total_solde_periode_credit+=abs($solde_periode);
      }
      if($info["solde_fin"]>0){
      	$str.= ";";//debit
        $str.= round($info["solde_fin"], 2).";";
        $total_solde_fin_credit+=$info["solde_fin"];
      }else{
      	$str.= round(abs($info["solde_fin"]), 2).";";
        $str.= ";";//credit
        $total_solde_fin_debit+=abs($info["solde_fin"]);
      }
      $str.= round($info["variation"],2).";";
      $str.="\n";
    }//fin parcours comptes
    //mettre les totaux
    $str.=" ;";
    $str.= _("TOTAL")." ;";
    $str.= $total_solde_debut_debit.";";//debit
    $str.= $total_solde_debut_credit.";";
    $str.= $total_solde_mvt_debit.";";
    $str.= $total_solde_mvt_credit.";";
    $str.= $total_solde_periode_debit.";";
    $str.= $total_solde_periode_credit." ;";
    $str.= $total_solde_fin_debit.";";//debit
    $str.= $total_solde_fin_credit.";";
    $str.= ";";
    $str.="\n";

  }//fin parcours devise

  // Renvoi du string
  return $str;
}

?>