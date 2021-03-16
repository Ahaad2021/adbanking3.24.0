<?php


/* vim: set expandtab softtabstop=2 shiftwidth=2: */
/**
 * Génère le code XML pour les rapports agence
 * @package Rapports
 */

require_once 'lib/misc/xml_lib.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/rapports.php';
require_once 'lib/dbProcedures/engrais_chimiques.php';
require_once 'modules/rapports/xslt.php';
require_once 'lib/dbProcedures/client.php';
require_once 'lib/dbProcedures/engrais_chimiques.php';
require_once 'lib/misc/tableSys.php';
require_once 'ad_ma/app/models/AgenceRemote.php';
require_once 'ad_ma/app/models/Divers.php';
require_once 'ad_ma/app/controllers/misc/class.db.oo.php';


function xml_situation_paiement($criteres,$DATA, $choix_period, $export_csv = false){
  global $global_multidevise;
  global $global_monnaie, $global_id_agence;

  $document = create_xml_doc("engraischimiques_situation_paiement", "engraischimiques_situation_paiement.dtd");

  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'PNS-SIT');

  $header_contextuel = $root->new_child("header_contextuel", "");
  $agence_data = getAgenceDatas($global_id_agence);

   if (is_array($DATA)){
     $prod = $root->new_child("list_paiement", "");
     foreach ($DATA as $key => $value){
       foreach ($value["agence"] as $key1 => $value1) {
         //$localisation_rapport = getLocRapportSituation($key);
         foreach ($value1 as $key3 => $value3){
           $details_bureau = $prod->new_child("details_bureau", "");
           $province = $details_bureau->new_child("province", $value3["nom_province"], false, $export_csv);
           $commune = $details_bureau->new_child("commune", $value3["nom_commune"], false, $export_csv);
           $bureau = $details_bureau->new_child("bureau", $value3["agence"], false, $export_csv);
           if ($choix_period == 1) {
             $nbre_agri = countBenefAvanceRapportSituation($key, $value3["nom_province"], $value3["nom_commune"],$criteres);
           } else {
             $nbre_agri = countBenefSoldeRapportSituation($key, $value3["nom_province"], $value3["nom_commune"],$criteres);
           }
           $agriculteur = $details_bureau->new_child("agriculteur", $nbre_agri["nbre_agri"], false, $export_csv);
           foreach ($value3["produit"] as $key2 => $value2) {
             $details_produit = $details_bureau->new_child("detail_produit", "");
             $id_prod = $details_produit->new_child("id_produit", $value2["id"], false, $export_csv);
             $libel = $details_produit->new_child("libel_produit", $value2["libel"], false, $export_csv);
             $qtite = $details_produit->new_child("qty_produit", $value2["qtite"], false, $export_csv);
           }

           $total = $details_bureau->new_child("total", $value3["total"], false, $export_csv);
        }
       }

       $colonne = $prod->new_child("nbre_colonne", null);
       foreach ($value["item_produit"] as $list_prod) {
         $nb_colonne = $colonne->new_child("colonne", $list_prod,false, $export_csv);
       }
     }

     IF($export_csv== true){
       $list_criteres = array ();
       $list_criteres[_("Année agricole")] = $criteres['Annee agricole'];
       $list_criteres[_("Saison culturale")] = $criteres['Saison culturale'];
       $list_criteres[_("Choix periode")] = $criteres['Choix periode'];
       $list_criteres[_("Date debut")] = $criteres['Date debut'];
       $list_criteres[_("Date fin")] = $criteres['Date fin'];
       $list_criteres[_("Nombre agriculteur")] = $criteres['Nombre agriculteurs'];
       $list_criteres[_("Total montant encaisse")] = afficheMontant($criteres['Total montant encaissee'],false,$export_csv);
     }else{
       $list_criteres = array ();
       $list_criteres[_("Année agricole")] = $criteres['Annee agricole'];
       $list_criteres[_("Saison culturale")] = $criteres['Saison culturale'];
       $list_criteres[_("Choix periode")] = $criteres['Choix periode'];
       $list_criteres[_("Date debut")] = $criteres['Date debut'];
       $list_criteres[_("Date fin")] = $criteres['Date fin'];
       $list_criteres[_("Nombre agriculteur")] = $criteres['Nombre agriculteurs'];
       $list_criteres[_("Total montant encaisse")] = afficheMontant($criteres['Total montant encaissee'],false);
     }

     gen_criteres_recherche($header_contextuel, $list_criteres);

   }

  return $document->dump_mem(true);

}

function xml_liste_benef_autorisation_plafond($id_annee,$id_saison,$criteres,$DATAS = null, $export_csv = false)
{
  global $global_multidevise;
  global $global_monnaie, $global_id_agence;

  $document = create_xml_doc("engraischimiques_liste_benef_plafond", "engraischimiques_liste_benef_plafond.dtd");

  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'PNS-PLA');

  $header_contextuel = $root->new_child("header_contextuel", "");
  $libel_annee = getListeAnneeAgricolePNSEB("id_annee =".$id_annee);
  if ($id_saison!=null) {
    $whereSaisonIs = "id_saison = " . $id_saison . " AND id_annee = " . $id_annee;
    $libelSaison = getListeSaisonPNSEB($whereSaisonIs);
  }
  $list_criteres = array ();
  $list_criteres[_("Année agricole")] = $libel_annee[$id_annee];
  if ($id_saison==null){
    $list_criteres[_("Saison culturale")] = 'Tous';
  }else {
    $list_criteres[_("Saison culturale")] = $libelSaison[$id_saison];
  }

  gen_criteres_recherche($header_contextuel, $list_criteres);
  $sous_total_engrais =0;
  $sous_total_amendement =0;
  $sous_total_montant =0;
  $saison = $root->new_child("saison", "");
  foreach ($DATAS as $id_saison) {
    foreach ($id_saison as $id_detail_commande) {
      if($id_detail_commande['id_benef'] != null) {
        $details_commande = $saison->new_child("commande", "");
        $id_benef = $details_commande->new_child("id_benef", $id_detail_commande['id_benef']);
        $nom_prenom = $details_commande->new_child("nom_prenom", $id_detail_commande['nom_prenom']);
        $id_commande = $details_commande->new_child("id_commande", $id_detail_commande['id_commande']);
        $nbre_engrais = $details_commande->new_child("nbre_engrais", $id_detail_commande['nbre_engrais']);
        $total_engrais = $details_commande->new_child("total_engrais", afficheMontant($id_detail_commande['total_engrais'],false,$export_csv));
        $nbre_amendement = $details_commande->new_child("nbre_amendement", $id_detail_commande['nbre_amendement']);
        $total_amendement = $details_commande->new_child("total_amendement", afficheMontant($id_detail_commande['total_amendement'],false,$export_csv));
        $total_depassement = $details_commande->new_child("total_depassement", afficheMontant($id_detail_commande['total_depassement'],false,$export_csv));
      }
    }
    $sous_total_engrais += $id_saison['sous_total_engrais'];
    $sous_total_amendement += $id_saison['sous_total_amendement'];
    $sous_total_montant += $id_saison['sous_total_montant'];

    /*$sous_total= $saison->new_child("total_montant", "");
    $sous_total_engrais= $sous_total->new_child("sous_total_engrais",afficheMontant($id_saison['sous_total_engrais'],true,$export_csv));
    $sous_total_amendement= $sous_total->new_child("sous_total_amendement",afficheMontant($id_saison['sous_total_amendement'],true,$export_csv));
    $sous_total_montant= $sous_total->new_child("sous_total_montant",afficheMontant($id_saison['sous_total_montant'],true,$export_csv));*/
  }
  $sous_total= $saison->new_child("total_montant", "");
  $sous_total_engrais= $sous_total->new_child("sous_total_engrais",afficheMontant($sous_total_engrais,true,$export_csv));
  $sous_total_amendement= $sous_total->new_child("sous_total_amendement",afficheMontant($sous_total_amendement,true,$export_csv));
  $sous_total_montant= $sous_total->new_child("sous_total_montant",afficheMontant($sous_total_montant,true,$export_csv));

  return $document->dump_mem(true);

}

/**
 *
 * Renvoie le xml pour la generation du Rapport Engrais Chimiques : Liste des bénéficiaires ayant payés dans une période donnée
 *
 * @param number $annee
 * @param number $saison
 * @param number $periode
 * @param date $date debut
 * @param date $date fin
 * @return NULL|array
 */

function xml_list_beneficiaire_payant ($annee, $saison, $periode=0, $date_debut, $date_fin, $export_csv=false){

  global $global_multidevise;
  global $global_monnaie;
  global $adsys;

  // Création racine
  global $global_id_agence;
  $document = create_xml_doc("engraisChimiques_listbenefpayant", "engraisChimiques_listbenefpayant.dtd");

  if($saison ==null){
    $saison = 0;
    $DATA_SAISON = array();
    $DATA_SAISON = get_rapport_list_beneficiaire_payant_data($annee, $saison, $periode, $date_debut, $date_fin);
  }else{
    $DATA_SAISON = get_rapport_list_beneficiaire_payant_data($annee, $saison, $periode, $date_debut, $date_fin);
  }
  if ($DATA_SAISON == NULL)
    return NULL;

  $produit_actif = getProduitCommander($annee);


  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'PNS-LBP');

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");

  //Recuperation libel Annee Agricole, Saison Culturale et Periode
  $whereAnneeIs = "id_annee = ".$annee;
  $libelAnnee = getListeAnneeAgricolePNSEB($whereAnneeIs);
  $whereSaisonIs = "id_saison = ".$saison." AND id_annee = ".$annee;
  if ($saison != null){
    $libelSaison = getListeSaisonPNSEB($whereSaisonIs);
    $libelSaison = $libelSaison[$saison];
  }
  else{
    $libelSaison = _("Tous");
  }if ($periode != null){
    $libelPeriode = $adsys["adsys_choix_periode"][$periode];
  }
  else{
    $libelPeriode = _("Tous");
  }

  // Affichage critere de recherches
  $criteres = array (
    _("Annee Agricole") => _($libelAnnee[$annee]),
    _("Saison Culturale") => _($libelSaison),
    _("Periode") => _($libelPeriode),
    _("Date Debut") => date($date_debut),
    _("Date Fin") => date($date_fin),
    _("Devise") => _($global_monnaie)
  );

  if (($date_debut == null) && ($date_fin == null)){
    unset($criteres[3]);
    unset($criteres[4]);
  }
  gen_criteres_recherche($header_contextuel, $criteres);
  $count_colonne= 0;


  $list_beneficiaires = $root->new_child("list_beneficiaires", "");

  foreach($DATA_SAISON as $id_saison => $value_saison){
    foreach($value_saison["province"] as $detail_province => $value_province){
      $province = $list_beneficiaires->new_child("province", "");
      $nom_province = $province->new_child("nom_province", $value_province["nom_province"]);
      foreach($value_province["commune"] as $detail_commune => $value_commune) {
        $commune = $province->new_child("commune", "");
        $nom_commune = $commune->new_child("nom_commune", $value_commune["nom_commune"]);
        //$zone = $commune->new_child("zone", "");
        $coopec = $commune->new_child("coopec", "");
        foreach ($value_commune["coopec"] as $detail_coopec => $value_coopec) {
          $nom_coopec = $coopec->new_child("nom_coopec", $value_coopec["nom_coopec"]);
          $zone = $coopec->new_child("zone", "");
        foreach ($value_coopec["zone"] as $detail_zone => $value_zone) {
          $nom_zone = $zone->new_child("nom_zone", $value_zone["nom_zone"]);
          $colline = $zone->new_child("colline", "");
          foreach ($value_zone["colline"] as $detail_colline => $value_colline) {
            $nom_colline = $colline->new_child("nom_colline", $value_colline["nom_colline"]);
            foreach ($value_colline["benef"] as $benef_detail) {
              $benef = $colline->new_child("details_benef", null);
              $nom_zone1 = $benef->new_child("nom_zone1", $value_zone["nom_zone"]);
              $nom_colline1 = $benef->new_child("nom_colline1", $value_colline["nom_colline"]);
              $nom_benef = $benef->new_child("nom_benef", $benef_detail["nom_prenom"]);
              $id_card = $benef->new_child("id_card", $benef_detail["id_card"]);
              //$montant_avance = $benef->new_child("montant_avance", afficheMontant($benef_detail["montant_avance"], false, $export_csv));
              //$montant_solde = $benef->new_child("montant_solde", afficheMontant($benef_detail["montant_solde"], false, $export_csv));
              $count = 0;
              $mnt_avance = 0;
              $mnt_solde = 0;
              $mnt_tot =0;
              foreach ($benef_detail["produit"] as $prod) {
                $detail_produit = $benef->new_child("detail_produit", null);
                $qty_produit = $detail_produit->new_child("qty_produit", $prod['quantite']);
                $count++;
                if ($periode == 1){
                  $mnt_avance +=  $prod['total'];
                }else if ($periode == 2){
                  $mnt_solde +=  $prod['total'];
                }else if ($periode == 0){
                  $mnt_tot +=  $prod['total'];
                }

              }
              if ($periode == 1){
                $montant = $benef->new_child("montant", afficheMontant($mnt_avance, false, $export_csv));
              }else if ($periode == 2){
                $montant = $benef->new_child("montant", afficheMontant($mnt_solde, false, $export_csv));
              }else if ($periode == 0){
                $montant = $benef->new_child("montant", afficheMontant($mnt_tot , false, $export_csv));
              }
            }
          }
        }
      }
      }
    }
    $colonne = $list_beneficiaires->new_child("nbre_colonne", null);
    foreach ($value_saison["item_produit"] as $list_prod) {
      $nb_colonne = $colonne->new_child("colonne", $list_prod);
    }
  }
  return $document->dump_mem(true);

}

function xml_repartition_qtite_zone( $id_annee=null,$id_saison=null,$date_debut, $date_fin,$export_csv=false){

  global $global_multidevise;
  global $global_monnaie, $global_id_agence;

  $document = create_xml_doc("engraischimiques_repartition_qtite_zone", "engraischimiques_repartition_qtite_zone.dtd");

  $root = $document->root();
  // Affichage critere de recherches
  $whereAnneeIs = "id_annee = ".$id_annee;
  $libelAnnee = getListeAnneeAgricolePNSEB($whereAnneeIs);
  $whereSaisonIs = "id_saison = ".$id_saison." AND id_annee = ".$id_annee;
  $libelSaison = getListeSaisonPNSEB($whereSaisonIs);


  $criteres = array (
    _("Annee Agricole") => _($libelAnnee[$id_annee]),
    _("Saison Culturale") => _($libelSaison[$id_saison])
  );

  $data = getRepartitionQtiteZone($id_annee,$id_saison,$date_debut,$date_fin );

  //En-tête généraliste
  gen_header($root, 'PNS-RQZ');
  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $criteres);


  $list_quantite = $root->new_child("list_quantite", "");
  foreach($data as $id_saison => $value_saison){
    foreach($value_saison["province"] as $detail_province => $value_province){
      $province = $list_quantite->new_child("province", "");
      $nom_province = $province->new_child("nom_province", $value_province["nom_province"]);
      foreach($value_province["commune"] as $detail_commune => $value_commune) {
        $commune = $province->new_child("commune", "");
        $nom_commune = $commune->new_child("nom_commune", $value_commune["nom_commune"]);
        //$zone = $commune->new_child("zone", "");
        $coopec = $commune->new_child("coopec", "");
        foreach ($value_commune["coopec"] as $detail_coopec => $value_coopec) {
          $nom_coopec = $coopec->new_child("nom_coopec", $value_coopec["nom_coopec"]);
          foreach ($value_coopec["zone"] as $detail_zone => $value_zone) {
            //$nbre_agri = calculBeneficiaireCommande($id_saison,$value_zone['nom_zone'] );
            $zone = $coopec->new_child("zone", "");
            $mnt =0;
            $nom_zone = $zone->new_child("nom_zone", $value_zone["nom_zone"]);
            $data_zone = countBenefRapportRepartitionZone($id_saison,$value_zone["nom_zone"]);
            $agriculteur = $zone->new_child("agriculteur", $data_zone['nbre_agri']);
            foreach($value_zone['id_prod'] as $detail_prod => $value_prod){
              $produit = $zone->new_child("detail_produit", null);
              $qty_produit = $produit->new_child("qty_produit", $value_prod['qtite']);
              $mnt +=  $value_prod['montant'];
            }
            $montant_zone = $zone->new_child("montant", $mnt,$export_csv);
          }
        }
      }
    }
    $colonne = $list_quantite->new_child("nbre_colonne", null);
    foreach ($value_saison["item_produit"] as $list_prod) {
      $nb_colonne = $colonne->new_child("colonne", $list_prod);
    }
  }
  return $document->dump_mem(true);
}


function xml_detail_transactions_ec($DATAS, $criteres) {
  global $adsys;
  $document = create_xml_doc("detail_transaction_engraischimiques", "detail_transaction_engraischimiques.dtd");

  //Element root
  $root = $document->root();
  //En-tête généraliste
  $ref = gen_header($root, 'GUI-TRA-EC');
  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $criteres);

  //Corps
  $transactions = $root->new_child("transactions", "");
  $his = array();
  while (list ($cle, $details) = each($DATAS)) {
    if(!isset($his[$details['id_his']])){
      $his[$details['id_his']] = $transactions->new_child("his_data", "");
    }
    $num_trans = $his[$details['id_his']]->new_child("num_trans", $details['id_his']);
    $date = $his[$details['id_his']]->new_child("date", $details['date']);
    $fonction = $his[$details['id_his']]->new_child("fonction", adb_gettext($adsys["adsys_fonction_systeme"][$details['type_fonction']]));
    $login = $his[$details['id_his']]->new_child("login", $details['login']);
    $val_benef = explode("-", $details['info_ecriture']);
    $num_beneficiaire = $his[$details['id_his']]->new_child("num_beneficiaire", $val_benef[1]);
    if(!isset($ecriture[$details['id_ecriture']])){
      $ecriture[$details['id_ecriture']] = $his[$details['id_his']]->new_child("ligne_ecritures", "");
    }
    $num_ecriture = $ecriture[$details['id_ecriture']]->new_child("num_ecriture", $details['ref_ecriture']);
    $libel_ecriture = new Trad($details['libel_ecriture']);
    $libel_ecriture = $ecriture[$details['id_ecriture']]->new_child("libel_ecriture", $libel_ecriture->traduction());
    $mvmts[$details['id_mouvement']] = $ecriture[$details['id_ecriture']]->new_child("ligne_mouvements", "");
    $mvmts[$details['id_mouvement']]->new_child("compte", $details['compte']);
    $mvmts[$details['id_mouvement']]->new_child("compte_client", $details['cpte_interne_cli']);
    if($details['sens'] == 'd'){
      $mvmts[$details['id_mouvement']]->new_child("montant_debit", afficheMontant($details['montant']));
    }else{
      $mvmts[$details['id_mouvement']]->new_child("montant_credit", afficheMontant($details['montant']));
    }
  }
  return($document->dump_mem(true));

}

/**
 *
 * Renvoie le xml pour la generation du Rapport Engrais Chimiques : Liste des bénéficiaires ayant payés dans une période donnée Globale
 *
 * @param $export_csv
 * @return NULL|array
 */

function xml_list_beneficiaire_payant_globale ($annee, $saison, $periode, $date_debut, $date_fin, $export_csv=false){

  global $global_multidevise;
  global $global_monnaie;
  global $adsys;

  // Création racine
  global $global_id_agence;
  $document = create_xml_doc("engraisChimiques_listbenefpayant", "engraisChimiques_listbenefpayant.dtd");


  $DATA_SAISON = get_rapport_list_beneficiaire_payant_data_globale($saison, $periode);

  if ($DATA_SAISON == NULL)
    return NULL;


  //$produit_actif = getProduitCommander($annee);


  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'PNS-LPG');

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");

  //Recuperation libel Annee Agricole, Saison Culturale et Periode
  $whereCond = "id_annee = ".$annee;//= "etat = 1";
  $libelAnnee = getListeAnneeAgricolePNSEB($whereCond);
  $whereSaisonIs = "id_saison = ".$saison." AND id_annee = ".$annee;
  $libelSaison = getListeSaisonPNSEB($whereSaisonIs);
  $libelSaison = $libelSaison[$saison];
  if ($periode != null){
    $libelPeriode = $adsys["adsys_choix_periode"][$periode];
  }

  // Affichage critere de recherches
  $criteres = array (
    _("Annee Agricole") => _($libelAnnee[$annee]),
    _("Saison Culturale") => _($libelSaison),
    _("Periode") => _($libelPeriode),
    _("Date Debut") => date($date_debut),
    _("Date Fin") => date($date_fin),
    _("Devise") => _($global_monnaie)
  );

  if (($date_debut == null) && ($date_fin == null)){
    unset($criteres[3]);
    unset($criteres[4]);
  }
  gen_criteres_recherche($header_contextuel, $criteres);
  $count_colonne= 0;


  $list_beneficiaires = $root->new_child("list_beneficiaires", "");

  foreach($DATA_SAISON as $id_saison => $value_saison){
    foreach($value_saison["province"] as $detail_province => $value_province){
      $province = $list_beneficiaires->new_child("province", "");
      $nom_province = $province->new_child("nom_province", $value_province["nom_province"]);
      foreach($value_province["commune"] as $detail_commune => $value_commune) {
        $commune = $province->new_child("commune", "");
        $nom_commune = $commune->new_child("nom_commune", $value_commune["nom_commune"]);
        //$zone = $commune->new_child("zone", "");
        $coopec = $commune->new_child("coopec", "");
        foreach ($value_commune["coopec"] as $detail_coopec => $value_coopec) {
          $nom_coopec = $coopec->new_child("nom_coopec", $value_coopec["nom_coopec"]);
          $zone = $coopec->new_child("zone", "");
          foreach ($value_coopec["zone"] as $detail_zone => $value_zone) {
            $nom_zone = $zone->new_child("nom_zone", $value_zone["nom_zone"]);
            $colline = $zone->new_child("colline", "");
            foreach ($value_zone["colline"] as $detail_colline => $value_colline) {
              $nom_colline = $colline->new_child("nom_colline", $value_colline["nom_colline"]);
              foreach ($value_colline["benef"] as $benef_detail) {
                $benef = $colline->new_child("details_benef", null);
                $nom_zone1 = $benef->new_child("nom_zone1", $value_zone["nom_zone"]);
                $nom_colline1 = $benef->new_child("nom_colline1", $value_colline["nom_colline"]);
                $nom_benef = $benef->new_child("nom_benef", $benef_detail["nom_prenom"]);
                $id_card = $benef->new_child("id_card", $benef_detail["id_card"]);
                //$montant_avance = $benef->new_child("montant_avance", afficheMontant($benef_detail["montant_avance"], false, $export_csv));
                //$montant_solde = $benef->new_child("montant_solde", afficheMontant($benef_detail["montant_solde"], false, $export_csv));
                $count = 0;
                $mnt_avance = 0;
                $mnt_solde = 0;
                $mnt_tot =0;
                foreach ($benef_detail["produit"] as $prod) {
                  $detail_produit = $benef->new_child("detail_produit", null);
                  $qty_produit = $detail_produit->new_child("qty_produit", $prod['quantite']);
                  $count++;
                  if ($periode == 1){
                    $mnt_avance +=  $prod['total'];
                  }else if ($periode == 2){
                    $mnt_solde +=  $prod['total'];
                  }else if ($periode == 0){
                    $mnt_tot +=  $prod['total'];
                  }

                }
                if ($periode == 1){
                  $montant = $benef->new_child("montant", afficheMontant($mnt_avance, false, $export_csv));
                }else if ($periode == 2){
                  $montant = $benef->new_child("montant", afficheMontant($mnt_solde, false, $export_csv));
                }else if ($periode == 0){
                  $montant = $benef->new_child("montant", afficheMontant($mnt_tot , false, $export_csv));
                }
              }
            }
          }
        }
      }
    }
    $colonne = $list_beneficiaires->new_child("nbre_colonne", null);
    foreach ($value_saison["item_produit"] as $list_prod) {
      $nb_colonne = $colonne->new_child("colonne", $list_prod);
    }
  }
  return $document->dump_mem(true);

}

function xml_repartition_qtite_zone_global( $id_annee=null,$id_saison=null,$date_debut, $date_fin,$export_csv=false){

  global $global_multidevise;
  global $global_monnaie, $global_id_agence;

  $document = create_xml_doc("engraischimiques_repartition_qtite_zone", "engraischimiques_repartition_qtite_zone.dtd");

  $root = $document->root();
  // Affichage critere de recherches
  $whereAnneeIs = "id_annee = ".$id_annee;
  $libelAnnee = getListeAnneeAgricolePNSEB($whereAnneeIs);
  $whereSaisonIs = "id_saison = ".$id_saison." AND id_annee = ".$id_annee;
  $libelSaison = getListeSaisonPNSEB($whereSaisonIs);


  $criteres = array (
    _("Annee Agricole") => _($libelAnnee[$id_annee]),
    _("Saison Culturale") => _($libelSaison[$id_saison])
  );

  $data = getRepartitionQtiteZoneGlobal($id_annee,$id_saison,$date_debut,$date_fin );

  //En-tête généraliste
  gen_header($root, 'PNS-RZG');
  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $criteres);

  $list_quantite = $root->new_child("list_quantite", "");
    foreach ($data as $id_saison => $value_saison) {
      foreach ($value_saison as $id_ag => $value_ag) {
        foreach ($value_ag["province"] as $detail_province => $value_province) {
          $province = $list_quantite->new_child("province", "");
          $nom_province = $province->new_child("nom_province", $value_province["nom_province"]);
          foreach ($value_province["commune"] as $detail_commune => $value_commune) {
            $commune = $province->new_child("commune", "");
            $nom_commune = $commune->new_child("nom_commune", $value_commune["nom_commune"]);
            //$zone = $commune->new_child("zone", "");
            $coopec = $commune->new_child("coopec", "");
            foreach ($value_commune["coopec"] as $detail_coopec => $value_coopec) {
              $nom_coopec = $coopec->new_child("nom_coopec", $value_coopec["nom_coopec"]);
              foreach ($value_coopec["zone"] as $detail_zone => $value_zone) {
                //$nbre_agri = calculBeneficiaireCommande($id_saison,$value_zone['nom_zone'] );
                $zone = $coopec->new_child("zone", "");
                $mnt = 0;
                $nom_zone = $zone->new_child("nom_zone", $value_zone["nom_zone"]);
                $agenceCo = AgenceRemote::getRemoteAgenceConnection($id_ag);
                if($agenceCo != null) {
                  $agenceCo->beginTransaction();

                  try {

                    // Init class
                    $DiversObj = new Divers($agenceCo, $id_ag);
                    $date_jour = date("d");
                    $date_mois = date("m");
                    $date_annee = date("Y");
                    $date_total = $date_jour."/".$date_mois."/".$date_annee;
                    $nbre_agri = $DiversObj->countBenefRapportRepartitionZone($id_saison,$value_zone["nom_zone"]);
                    unset($DiversObj);

                    $agenceCo->commit();

                  } catch (PDOException $e) {
                    //$pdo_conn->rollBack(); // Roll back remote transaction
                  }
                }
                $data_zone = countBenefRapportRepartitionZone($id_saison, $value_zone["nom_zone"]);
                $agriculteur = $zone->new_child("agriculteur", $nbre_agri['nbre_agri']);
                foreach ($value_zone['id_prod'] as $detail_prod => $value_prod) {
                  $produit = $zone->new_child("detail_produit", null);
                  $qty_produit = $produit->new_child("qty_produit", $value_prod['qtite']);
                  $mnt += $value_prod['montant'];
                }
                $montant_zone = $zone->new_child("montant", $mnt, $export_csv);
              }
            }
          }
        }
      }
      $colonne = $list_quantite->new_child("nbre_colonne", null);
      foreach ($value_ag["item_produit"] as $list_prod) {
        $nb_colonne = $colonne->new_child("colonne", $list_prod);
      }
  }
  return $document->dump_mem(true);
}

function xml_situation_paiement_global($DATA, $choix_period,$id_saison,$id_annne, $export_csv = false){
  global $global_multidevise;
  global $global_monnaie, $global_id_agence, $adsys;

  $document = create_xml_doc("engraischimiques_situation_paiement", "engraischimiques_situation_paiement.dtd");

  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'PNS-SPG');

  $header_contextuel = $root->new_child("header_contextuel", "");
  $agence_data = getAgenceDatas($global_id_agence);

  if (is_array($DATA)){
    $prod = $root->new_child("list_paiement", "");
    foreach ($DATA as $key => $value) {
      foreach ($value as $key_agence => $value_agence){
        foreach ($value_agence["agence"] as $key1 => $value1) {
          foreach ($value1 as $key3 => $value3){
            $details_bureau = $prod->new_child("details_bureau", "");
            $localisation_rapport = getLocRapportSituation($key);
            $province = $details_bureau->new_child("province", $value3["nom_province"], false, $export_csv);
            $commune = $details_bureau->new_child("commune", $value3["nom_commune"], false, $export_csv);
            $bureau = $details_bureau->new_child("bureau", $value3["agence"], false, $export_csv);

            $agenceCo = AgenceRemote::getRemoteAgenceConnection($key_agence);
            if($agenceCo != null) {
              $agenceCo->beginTransaction();

              try {

                // Init class
                $DiversObj = new Divers($agenceCo, $key_agence);
                $date_jour = date("d");
                $date_mois = date("m");
                $date_annee = date("Y");
                $date_total = $date_jour."/".$date_mois."/".$date_annee;
                $details_period = getPeriodeEC ($date_total,$id_saison);
                $nbre_agri = $DiversObj->countBenefAvanceRapportSituation($id_saison,$value3["nom_province"],$value3["nom_commune"],$choix_period,$details_period['date_debut'],$details_period['date_fin']);
                unset($DiversObj);

                $agenceCo->commit();

              } catch (PDOException $e) {
                //$pdo_conn->rollBack(); // Roll back remote transaction
              }
            }
            $agriculteur = $details_bureau->new_child("agriculteur", $nbre_agri["nbre_agri"], false, $export_csv);
            foreach ($value3["produit"] as $key2 => $value2) {
              $details_produit = $details_bureau->new_child("detail_produit", "");
              $id_prod = $details_produit->new_child("id_produit", $value2["id"], false, $export_csv);
              $libel = $details_produit->new_child("libel_produit", $value2["libel"], false, $export_csv);
              $qtite = $details_produit->new_child("qty_produit", $value2["qtite"], false, $export_csv);
            }
            $total = $details_bureau->new_child("total", $value3["total"], false, $export_csv);
          }
        }
    }

      $colonne = $prod->new_child("nbre_colonne", null);
      foreach ($value["item_produit"] as $list_prod) {
        $nb_colonne = $colonne->new_child("colonne", $list_prod,false, $export_csv);
      }
    }  //Recuperation libel Annee Agricole, Saison Culturale et Periode
    $whereCond = "id_annee = ".$id_annne;//= "etat = 1";
    $libelAnnee = getListeAnneeAgricolePNSEB($whereCond);
    $whereSaisonIs = "id_saison = ".$id_saison." AND id_annee = ".$id_annne;
    $libelSaison = getListeSaisonPNSEB($whereSaisonIs);
    $libelSaison = $libelSaison[$id_saison];
    if ($choix_period != null){
      $libelPeriode = gettext($adsys["adsys_choix_periode"][$choix_period]);
    }

    // Affichage critere de recherches
    $criteres = array (
      _("Annee Agricole") => _($libelAnnee[$id_annne]),
      _("Saison Culturale") => _($libelSaison)
    );


    gen_criteres_recherche($header_contextuel, $criteres);

  }
  return $document->dump_mem(true);

}


function xml_situation_paiement_historique($DATA, $choix_period,$id_saison,$id_annne, $date_debut=null,$date_fin=null,$export_csv = false){
  global $global_multidevise;
  global $global_monnaie, $global_id_agence, $adsys;

  $document = create_xml_doc("engraischimiques_situation_paiement", "engraischimiques_situation_paiement.dtd");

  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'PNS-SPG');

  $header_contextuel = $root->new_child("header_contextuel", "");
  $agence_data = getAgenceDatas($global_id_agence);

  if (is_array($DATA)){
    $prod = $root->new_child("list_paiement", "");
    foreach ($DATA as $key => $value) {
      foreach ($value as $key_agence => $value_agence){
        foreach ($value_agence["agence"] as $key1 => $value1) {
          foreach ($value1 as $key3 => $value3) {
            $details_bureau = $prod->new_child("details_bureau", "");
            $localisation_rapport = getLocRapportSituation($key);
            $province = $details_bureau->new_child("province", $value3["nom_province"], false, $export_csv);
            $commune = $details_bureau->new_child("commune", $value3["nom_commune"], false, $export_csv);
            $bureau = $details_bureau->new_child("bureau", $value3["agence"], false, $export_csv);

            $agenceCo = AgenceRemote::getRemoteAgenceConnection($key_agence);
            if ($agenceCo != null) {
              $agenceCo->beginTransaction();

              try {

                // Init class
                $DiversObj = new Divers($agenceCo, $key_agence);
                $date_jour = date("d");
                $date_mois = date("m");
                $date_annee = date("Y");
                $date_total = $date_jour . "/" . $date_mois . "/" . $date_annee;
                $nbre_agri = $DiversObj->countBenefAvanceRapportSituation($id_saison, $value3["nom_province"], $value3["nom_commune"], $choix_period, $date_debut, $date_fin);
                unset($DiversObj);

                $agenceCo->commit();

              } catch (PDOException $e) {
                //$pdo_conn->rollBack(); // Roll back remote transaction
              }
            }
            $agriculteur = $details_bureau->new_child("agriculteur", $nbre_agri["nbre_agri"], false, $export_csv);
            foreach ($value3["produit"] as $key2 => $value2) {
              $details_produit = $details_bureau->new_child("detail_produit", "");
              $id_prod = $details_produit->new_child("id_produit", $value2["id"], false, $export_csv);
              $libel = $details_produit->new_child("libel_produit", $value2["libel"], false, $export_csv);
              $qtite = $details_produit->new_child("qty_produit", $value2["qtite"], false, $export_csv);
            }

            $total = $details_bureau->new_child("total", $value3["total"], false, $export_csv);
          }
        }
      }

      $colonne = $prod->new_child("nbre_colonne", null);
      foreach ($value["item_produit"] as $list_prod) {
        $nb_colonne = $colonne->new_child("colonne", $list_prod,false, $export_csv);
      }
    }  //Recuperation libel Annee Agricole, Saison Culturale et Periode
    $whereCond = "id_annee = ".$id_annne;//= "etat = 1";
    $libelAnnee = getListeAnneeAgricolePNSEB($whereCond);
    $whereSaisonIs = "id_saison = ".$id_saison." AND id_annee = ".$id_annne;
    $libelSaison = getListeSaisonPNSEB($whereSaisonIs);
    $libelSaison = $libelSaison[$id_saison];
    if ($choix_period != null){
      $libelPeriode = gettext($adsys["adsys_choix_periode"][$choix_period]);
    }

    // Affichage critere de recherches
    $criteres = array (
      _("Annee Agricole") => _($libelAnnee[$id_annne]),
      _("Saison Culturale") => _($libelSaison)
    );


    gen_criteres_recherche($header_contextuel, $criteres);

  }
  return $document->dump_mem(true);

}

function xml_repartition_qtite_zone_hist( $id_annee=null,$id_saison=null,$date_debut, $date_fin,$export_csv=false){

  global $global_multidevise;
  global $global_monnaie, $global_id_agence;

  $document = create_xml_doc("engraischimiques_repartition_qtite_zone", "engraischimiques_repartition_qtite_zone.dtd");

  $root = $document->root();
  // Affichage critere de recherches
  $whereAnneeIs = "id_annee = ".$id_annee;
  $libelAnnee = getListeAnneeAgricolePNSEB($whereAnneeIs);
  $whereSaisonIs = "id_saison = ".$id_saison." AND id_annee = ".$id_annee;
  $libelSaison = getListeSaisonPNSEB($whereSaisonIs);


  $criteres = array (
    _("Annee Agricole") => _($libelAnnee[$id_annee]),
    _("Saison Culturale") => _($libelSaison[$id_saison])
  );

  $data = getRepartitionQtiteZoneHist($id_annee,$id_saison,$date_debut,$date_fin );

  //En-tête généraliste
  gen_header($root, 'PNS-RZG');
  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $criteres);

  $list_quantite = $root->new_child("list_quantite", "");
  foreach ($data as $id_saison => $value_saison) {
    foreach ($value_saison as $id_ag => $value_ag) {
      foreach ($value_ag["province"] as $detail_province => $value_province) {
        $province = $list_quantite->new_child("province", "");
        $nom_province = $province->new_child("nom_province", $value_province["nom_province"]);
        foreach ($value_province["commune"] as $detail_commune => $value_commune) {
          $commune = $province->new_child("commune", "");
          $nom_commune = $commune->new_child("nom_commune", $value_commune["nom_commune"]);
          //$zone = $commune->new_child("zone", "");
          $coopec = $commune->new_child("coopec", "");
          foreach ($value_commune["coopec"] as $detail_coopec => $value_coopec) {
            $nom_coopec = $coopec->new_child("nom_coopec", $value_coopec["nom_coopec"]);
            foreach ($value_coopec["zone"] as $detail_zone => $value_zone) {
              //$nbre_agri = calculBeneficiaireCommande($id_saison,$value_zone['nom_zone'] );
              $zone = $coopec->new_child("zone", "");
              $mnt = 0;
              $nom_zone = $zone->new_child("nom_zone", $value_zone["nom_zone"]);
              $agenceCo = AgenceRemote::getRemoteAgenceConnection($id_ag);
              if($agenceCo != null) {
                $agenceCo->beginTransaction();

                try {

                  // Init class
                  $DiversObj = new Divers($agenceCo, $id_ag);
                  $date_jour = date("d");
                  $date_mois = date("m");
                  $date_annee = date("Y");
                  $date_total = $date_jour."/".$date_mois."/".$date_annee;
                  $nbre_agri = $DiversObj->countBenefRapportRepartitionZone($id_saison,$value_zone["nom_zone"]);
                  unset($DiversObj);

                  $agenceCo->commit();

                } catch (PDOException $e) {
                  //$pdo_conn->rollBack(); // Roll back remote transaction
                }
              }
              $data_zone = countBenefRapportRepartitionZone($id_saison, $value_zone["nom_zone"]);
              $agriculteur = $zone->new_child("agriculteur", $nbre_agri['nbre_agri']);
              foreach ($value_zone['id_prod'] as $detail_prod => $value_prod) {
                $produit = $zone->new_child("detail_produit", null);
                $qty_produit = $produit->new_child("qty_produit", $value_prod['qtite']);
                $mnt += $value_prod['montant'];
              }
              $montant_zone = $zone->new_child("montant", $mnt, $export_csv);
            }
          }
        }
      }
    }
    $colonne = $list_quantite->new_child("nbre_colonne", null);
    foreach ($value_ag["item_produit"] as $list_prod) {
      $nb_colonne = $colonne->new_child("colonne", $list_prod);
    }
  }
  return $document->dump_mem(true);
}

/**
 *
 * Renvoie le xml pour la generation du Rapport Engrais Chimiques : Liste des bénéficiaires ayant payés dans une période donnée Globale
 *
 * @param $export_csv
 * @return NULL|array
 */

function xml_list_beneficiaire_payant_globale_his ($annee, $saison, $periode, $date_debut, $date_fin, $export_csv=false){

  global $global_multidevise;
  global $global_monnaie;
  global $adsys;

  // Création racine
  global $global_id_agence;
  $document = create_xml_doc("engraisChimiques_listbenefpayant", "engraisChimiques_listbenefpayant.dtd");


  $DATA_SAISON = get_rapport_list_beneficiaire_payant_data_globale_his($saison, $periode);

  if ($DATA_SAISON == NULL)
    return NULL;


  //$produit_actif = getProduitCommander($annee);


  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'PNS-LPG');

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");

  //Recuperation libel Annee Agricole, Saison Culturale et Periode
  $whereCond = "id_annee = ".$annee;//= "etat = 1";
  $libelAnnee = getListeAnneeAgricolePNSEB($whereCond);
  $whereSaisonIs = "id_saison = ".$saison." AND id_annee = ".$annee;
  $libelSaison = getListeSaisonPNSEB($whereSaisonIs);
  $libelSaison = $libelSaison[$saison];
  if ($periode != null){
    $libelPeriode = $adsys["adsys_choix_periode"][$periode];
  }

  // Affichage critere de recherches
  $criteres = array (
    _("Annee Agricole") => _($libelAnnee[$annee]),
    _("Saison Culturale") => _($libelSaison),
    _("Periode") => _($libelPeriode),
    _("Date Debut") => date($date_debut),
    _("Date Fin") => date($date_fin),
    _("Devise") => _($global_monnaie)
  );

  if (($date_debut == null) && ($date_fin == null)){
    unset($criteres[3]);
    unset($criteres[4]);
  }
  gen_criteres_recherche($header_contextuel, $criteres);
  $count_colonne= 0;


  $list_beneficiaires = $root->new_child("list_beneficiaires", "");

  foreach($DATA_SAISON as $id_saison => $value_saison){
    foreach($value_saison["province"] as $detail_province => $value_province){
      $province = $list_beneficiaires->new_child("province", "");
      $nom_province = $province->new_child("nom_province", $value_province["nom_province"]);
      foreach($value_province["commune"] as $detail_commune => $value_commune) {
        $commune = $province->new_child("commune", "");
        $nom_commune = $commune->new_child("nom_commune", $value_commune["nom_commune"]);
        //$zone = $commune->new_child("zone", "");
        $coopec = $commune->new_child("coopec", "");
        foreach ($value_commune["coopec"] as $detail_coopec => $value_coopec) {
          $nom_coopec = $coopec->new_child("nom_coopec", $value_coopec["nom_coopec"]);
          $zone = $coopec->new_child("zone", "");
          foreach ($value_coopec["zone"] as $detail_zone => $value_zone) {
            $nom_zone = $zone->new_child("nom_zone", $value_zone["nom_zone"]);
            $colline = $zone->new_child("colline", "");
            foreach ($value_zone["colline"] as $detail_colline => $value_colline) {
              $nom_colline = $colline->new_child("nom_colline", $value_colline["nom_colline"]);
              foreach ($value_colline["benef"] as $benef_detail) {
                $benef = $colline->new_child("details_benef", null);
                $nom_zone1 = $benef->new_child("nom_zone1", $value_zone["nom_zone"]);
                $nom_colline1 = $benef->new_child("nom_colline1", $value_colline["nom_colline"]);
                $nom_benef = $benef->new_child("nom_benef", $benef_detail["nom_prenom"]);
                $id_card = $benef->new_child("id_card", $benef_detail["id_card"]);
                //$montant_avance = $benef->new_child("montant_avance", afficheMontant($benef_detail["montant_avance"], false, $export_csv));
                //$montant_solde = $benef->new_child("montant_solde", afficheMontant($benef_detail["montant_solde"], false, $export_csv));
                $count = 0;
                $mnt_avance = 0;
                $mnt_solde = 0;
                $mnt_tot =0;
                foreach ($benef_detail["produit"] as $prod) {
                  $detail_produit = $benef->new_child("detail_produit", null);
                  $qty_produit = $detail_produit->new_child("qty_produit", $prod['quantite']);
                  $count++;
                  if ($periode == 1){
                    $mnt_avance +=  $prod['total'];
                  }else if ($periode == 2){
                    $mnt_solde +=  $prod['total'];
                  }else if ($periode == 0){
                    $mnt_tot +=  $prod['total'];
                  }

                }
                if ($periode == 1){
                  $montant = $benef->new_child("montant", afficheMontant($mnt_avance, false, $export_csv));
                }else if ($periode == 2){
                  $montant = $benef->new_child("montant", afficheMontant($mnt_solde, false, $export_csv));
                }else if ($periode == 0){
                  $montant = $benef->new_child("montant", afficheMontant($mnt_tot , false, $export_csv));
                }
              }
            }
          }
        }
      }
    }
    $colonne = $list_beneficiaires->new_child("nbre_colonne", null);
    foreach ($value_saison["item_produit"] as $list_prod) {
      $nb_colonne = $colonne->new_child("colonne", $list_prod);
    }
  }
  return $document->dump_mem(true);

}

?>