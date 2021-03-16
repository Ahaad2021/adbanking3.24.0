<?php
/**
 * Created by PhpStorm.
 * User: Roshan
 * Date: 2/15/2018
 * Time: 5:01 PM
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/html/HtmlHeader.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/credit.php';
require_once('lib/dbProcedures/compte.php');
require_once 'lib/misc/divers.php';
require_once "modules/rapports/csv_epargne.php";
require_once "modules/rapports/xml_epargne.php";
require_once 'modules/rapports/xslt.php';
require_once 'modules/rapports/xml_echeancier.php';
require_once 'lib/misc/csv.php';

if (isset($id_doss) && $id_doss != ""){
  $dossier = getDossierCrdtInfo($id_doss);
  $infos_doss = array();
  $infos_doss[$id_doss] = $dossier;
  if ($dossier['gs_cat'] != 2){
    $infos_doss[$id_doss]['last_etat'] = $dossier['etat'];
  }
  if ($dossier['gs_cat'] == 2){
    $dossiers_membre = getDossiersMultiplesGS($global_id_client);
    foreach($dossiers_membre as $id_doss=>$val) {
      if (($val['is_ligne_credit'] != 't') AND $val['id_dcr_grp_sol'] == $id_doss) {
        $infos_doss[$id_doss] = $val; // infos d'un dossier reel d'un membre
        $infos_doss[$id_doss]['last_etat'] = $dossier['etat'];
      }
    }
  }

  // Récupération des garanties déjà mobilisées
  foreach($infos_doss as $id_doss=>$info_doss) {
    $infos_doss[$id_doss]['DATA_GAR'] = array();
    $liste_gar = getListeGaranties($id_doss);
    foreach($liste_gar as $key=>$value ) {
      $num = count($infos_doss[$id_doss]['DATA_GAR']) + 1;
      $infos_doss[$id_doss]['DATA_GAR'][$num]['id_gar'] = $value['id_gar'];
      $infos_doss[$id_doss]['DATA_GAR'][$num]['type'] = $value['type_gar'];
      $infos_doss[$id_doss]['DATA_GAR'][$num]['valeur'] = recupMontant($value['montant_vente']);
      $infos_doss[$id_doss]['DATA_GAR'][$num]['devise_vente'] = $value['devise_vente'];
      $infos_doss[$id_doss]['DATA_GAR'][$num]['etat'] = $value['etat_gar'];

      // Si c'est une garantie numéraire récupérer le numéro complet du compte de prélèvement
      if ($value['type_gar'] == 1) // Garantie numéraire
        $infos_doss[$id_doss]['DATA_GAR'][$num]['descr_ou_compte'] = $value['gar_num_id_cpte_prelev'];
      elseif($value['type_gar'] == 2 and isset($value['gar_mat_id_bien'])) { // garantie matérielle
        $id_bien = $value['gar_mat_id_bien'];
        $infos_bien = getInfosBien($id_bien);
        $infos_doss[$id_doss]['DATA_GAR'][$num]['descr_ou_compte'] = $infos_bien['description'];
        $infos_doss[$id_doss]['DATA_GAR'][$num]['type_bien'] = $infos_bien['type_bien'];
        $infos_doss[$id_doss]['DATA_GAR'][$num]['piece_just'] = $infos_bien['piece_just'];
        $infos_doss[$id_doss]['DATA_GAR'][$num]['num_client'] = $infos_bien['id_client'];
        $infos_doss[$id_doss]['DATA_GAR'][$num]['remarq'] = $infos_bien['remarque'];
        $infos_doss[$id_doss]['DATA_GAR'][$num]['gar_mat_id_bien'] = $id_bien;
      }
    } // Fin foreach garantie
    // recupération des valeurs des champs supplémentaire
    // champsextras
    if ( !isset($infos_doss[$id_doss]['champsExtrasValues'])) {
      $infos_doss[$id_doss]['champsExtrasValues'] = getChampsExtrasDCRValues($id_doss);
    }
  } // Fin foreach infos dossiers

  foreach($infos_doss as $id_doss=>$val_doss) {

    if ($val_doss["etat"] == 10) { // Le crédit est actuellement en cours de reprise

      $Myform = new HTML_GEN2(_("Rapport Suivi de Crédit"));
      $msg = sprintf(_("Le dossier de crédit %s est actuellement en cours de reprise et ne peut être visualisé"),$id_doss);
      $Myform->addHTMLExtraCode("espace".$id_doss,"<b>$msg</b><BR>");
      $xtHTML = "<center><input type='button' name='ok' value='Fermer' onClick='ADFormValid=true;self.close();'></center>";
      $Myform->addHTMLExtraCode("xtHTML", $xtHTML);
      $Myform->buildHTML();
      exit();

    } else {

      // Fonds déboursés ou soldé ou en perte
      if (($val_doss["etat"] == 5) || ($val_doss["etat"] == 6) || ($val_doss["etat"] == 7) || ($val_doss["etat"] == 9)) {
        if ($val_doss["etat"] != 9) { // dossier pas en perte
          if ($val_doss["cre_nbre_reech"] > 0) {
            $reech_moratoire = getLastRechMorHistorique (145,$val_doss['id_client']);
            $infos_doss[$id_doss]['montant'] = $reech_moratoire["infos"];
          }
        }
      }

      $infos_doss[$id_doss]['gar_num_mob'] = 0; // garanties numéraires totales mobilisées
      $infos_doss[$id_doss]['gar_mat_mob'] = 0; // garanties matérilles totales mobilisées
      if (is_array($infos_doss[$id_doss]['DATA_GAR'])) {
        foreach($infos_doss[$id_doss]['DATA_GAR'] as $key=>$value ) {
          if ($value['type'] == 1)
            $infos_doss[$id_doss]['gar_num_mob'] += recupMontant($value['valeur']);
          elseif($value['type'] == 2)
            $infos_doss[$id_doss]['gar_mat_mob'] += recupMontant($value['valeur']);
        }
      }
    }
  }
  $xml = xml_echeancier($infos_doss);
  $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'echeancier.xslt');
  $Myform = new HTML_GEN2(_("Generation Rapport Suivi de Crédit"));
  $xtHTML = "<center><input type='button' name='ok' value='Fermer' onClick='ADFormValid=true;self.close();'></center>";
  $Myform->addHTMLExtraCode("xtHTML", $xtHTML);
  $Myform->buildHTML();
  echo get_show_pdf_html(null, $fichier_pdf);
  echo $Myform->getHTML();
}