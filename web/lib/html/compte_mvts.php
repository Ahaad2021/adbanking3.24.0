<?php
/**
 * Created by PhpStorm.
 * User: Roshan
 * Date: 2/15/2018
 * Time: 9:35 AM
 */

/**
 * Compte Epargne : 100 derniers mouvements version Rapport PDF
 * @package Epargne
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/html/HtmlHeader.php';
require_once 'lib/dbProcedures/epargne.php';
require_once('lib/dbProcedures/compte.php');
require_once 'lib/misc/divers.php';
require_once "modules/rapports/csv_epargne.php";
require_once "modules/rapports/xml_epargne.php";
require_once 'modules/rapports/xslt.php';

if ($is_ord_per == 0 || $is_ord_per == 1){ // Compte Epargne - 100 derniers mouvements ou Ordre permanents Compte - 100 derniers mouvements
  global $global_id_client, $global_monnaie_courante_prec;

  $Myform = new HTML_GEN2(_("Generation Rapport Liste des 100 derniers mouvements"));

  // Informations Compte Epargne
  $InfoCpte = getAccountDatas($id_cpte);
  $solde_courant = $InfoCpte["solde"];

  // Les informations des 100 derniers mouvements
  $InfoMvts = getMvtsCpteClientParNumero($global_id_client, $id_cpte, 100);
  if (isset($InfoMvts)) {
    $date_jour = date("d");
    $date_mois = date("m");
    $date_annee = date("Y");
    $date_ancien = $date_annee."/".$date_mois."/".$date_jour;
    foreach ($InfoMvts as $key => $mvt) {
      //écriture des lignes de mvts dans le tableau
      $fonction = $mvt["type_fonction"];
      $libel_fonction = adb_gettext($adsys["adsys_fonction_systeme"][$fonction]);
      $id_his_ancien = $mvt["id_his"];
      if ($date_ancien != $mvt["date"]) {
        //dans ce cas, il y a rupture sur une nouvelle date
        $InfoMvts[$key]["nbre_jours_inactivite"] = nbreDiffJours(pg2phpDate($date_ancien),pg2phpDate($mvt["date"]));
        $date_ancien = $mvt["date"];
        $tmp_dte = pg2phpDatebis($mvt["date"]);
        $date = $tmp_dte[1]."/".$tmp_dte[0]."/".$tmp_dte[2]." ".$tmp_dte[3].":".$tmp_dte[4];
      } else {
        $date = "";
        $InfoMvts[$key]["nbre_jours_inactivite"] = 0;
      }
      $tradLibel_operation = new Trad($mvt['libel_ecriture']);

      // Multi agence fix
      $libel_operation = $tradLibel_operation->traduction();
      if($libel_operation=='Dépôt en déplacé' || $libel_operation=='Retrait en déplacé'){
        $libel_operation = 'Opération en déplacé';
      }

      //pour l'operation des transfert, les numéro des comptes des transactions sont affichés dans le libellé opération.
      if ($fonction=='76' && $mvt["type_operation"]=='120'){

        if(isset($mvt["info_ecriture"])){
          $numcpts = explode('|', $mvt["info_ecriture"]);

          if(count($numcpts)==2){

            $libel_operation .= ":<br/>";
            $libel_operation .= "Compte source: ".$numcpts[0];
            $libel_operation .= "<br/>Compte destination: ".$numcpts[1];
          }
        }
      }

      // Vérifier liste opération à modifier.
      if(in_array($mvt['type_operation'], $adsys["adsys_operation_cheque_infos"]) ){
        $libel_operation = getChequeno($mvt['id_his'],$libel_operation,$mvt["info_ecriture"]);
      }
      // On a les infos dans l'ordre chrono inverse
      $solde_apres = $solde_courant;
      if ($mvt["sens"] == 'd')
        $solde_courant += $mvt['montant'];
      else
        $solde_courant -= $mvt['montant'];
      $solde_avant = $solde_courant;
      $InfoMvts[$key]['solde'] = $solde_apres;
    }
    // Arrondi à cause des imprécisions des calculs float
    $solde_courant = round($solde_courant, $global_monnaie_courante_prec);
  }

  // On récupère les infos déjà en notre possession
  $DATA["client"] = $global_id_client;
  $DATA["nom_client"] = getClientName($global_id_client);
  $DATA["num_cpte"] = $InfoCpte["num_complet_cpte"];
  $tmp_dte = pg2phpDatebis($InfoCpte["date_ouvert"]);
  $DATA["date_ouverture"] = $tmp_dte[1] . "/" . $tmp_dte[0] . "/" . $tmp_dte[2];
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
  $DATA["produit"] = $InfoProduit["libel"];
  $DATA["id_produit"] = $InfoCpte["id_prod"];
  $DATA["solde_min"] = $InfoCpte["solde_calcul_interets"];
  $DATA["taux_int"] = $InfoProduit["tx_interet"];
  $DATA["date_debut"] = null; // $SESSION_VARS['DateDeb'];
  $DATA["date_fin"] = date('d/m/Y'); // $SESSION_VARS['DateFin'];
  $DATA["NbHisto"] = 100;
  $DATA["solde"] = $InfoCpte['solde'];
  $DATA["mnt_bloq"] = $InfoCpte["mnt_bloq"] + $InfoCpte["mnt_bloq_cre"];
  $DATA["mnt_min"] = $InfoCpte["mnt_min_cpte"];
  $DATA["solde_disp"] = $InfoProduit["retrait_unique"] == 't' ? _("Compte à retrait unique") : getSoldeDisponible($InfoCpte["id_cpte"]);
  $DATA["InfoMvts"] = $InfoMvts;
  $DATA["devise"] = $InfoProduit["devise"];

  //pour que le rapport soit dans la même langue
  basculer_langue_rpt();
  $list_criteres = array ();
  $list_criteres = array_merge($list_criteres, array ( _("ID client") => makeNumClient($DATA["client"])));
  $list_criteres = array_merge($list_criteres, array ( _("Nom") => $DATA["nom_client"]));
  $list_criteres = array_merge($list_criteres, array ( _("Compte") => $DATA["num_cpte"]));
  $list_criteres = array_merge($list_criteres, array ( _("Nombre de mouvements") => $DATA["NbHisto"]));
  reset_langue();

  //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
  $xml = xml_epargne($DATA, $list_criteres);
  $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'epargne.xslt');

  $xtHTML = "<center><input type='button' name='ok' value='Fermer' onClick='ADFormValid=true;self.close();'></center>";
  $Myform->addHTMLExtraCode("xtHTML", $xtHTML);
  $Myform->buildHTML();

  //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
  echo get_show_pdf_html(null, $fichier_pdf);

  echo $Myform->getHTML();
}

