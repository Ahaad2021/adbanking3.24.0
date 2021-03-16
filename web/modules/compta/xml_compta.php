<?php


/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

// Code permettant de générer les fichiers XML utilisés pour les rapports de comptabilité
// TF - 03/06/2003

require_once 'lib/misc/xml_lib.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/rapports.php';

/**
 * Génération du code XML pour les rapports balance comptable d'une période
 * @author last Modif: Stefano A.
 * @since 1.0.8
 * @param array $DATA Liste des mmouvements de la période
 * @param array $titre Tire du rapport
 * * @param int $id_agence idenfiant de l'agence' utilise, si on est au siege, pr mettre l'en-tête de l'agence corresp
 * @return array Liste opérations comptables
 */


function xml_balance_comptable($DATA, $titre,$id_agence=NULL) {
  reset($DATA);
  //Création racine
  $document = create_xml_doc("balance_comptable", "balance_comptable.dtd");

  //Element root
  $root = $document->root();

  //En-tête généraliste
  if($id_agence==NULL){
  	gen_header($root, 'CPT-BAL',$titre);
  }else{ //multiagence, mettre le nom de l'agence selectionné'
  	setGlobalIdAgence($id_agence);
  	gen_header($root, 'CPT-BAL',  $titre);
  	resetGlobalIdAgence();
  }

  //Body
  while (list ($key, $values) = each($DATA)) {
    setMonnaieCourante($key);
    $attName = " "._("Balance dans la devise")." " . $key;
    $comptable = $root->new_child("comptable", "");
    $comptable->set_attribute("type", $attName);
    $total_solde_periode_credit=0;
    $total_solde_periode_debit=0;
    $total_solde_mvt_credit=0;
    $total_solde_mvt_debit=0;
    $total_solde_debut_credit=0;
    $total_solde_debut_debit=0;
    $total_solde_fin_credit=0;
    $total_solde_fin_debit=0;
    while (list ($numCompte, $info) = each($values)) {
      $compte = $comptable->new_child("compte", "");

      $solde_periode=0;
      $compte->set_attribute("total", 0);
      $compte->new_child("num", $numCompte);
      $compte->new_child("libel", htmlspecialchars($info["libel"], ENT_QUOTES, "UTF-8"));
      if($info["solde_debut"]>0){
      	$compte->new_child("solde_debut_deb", afficheMontant(NULL, false));
        $compte->new_child("solde_debut_cre", afficheMontant($info["solde_debut"], false));
        $total_solde_debut_credit+=$info["solde_debut"];
      }else{
      	$compte->new_child("solde_debut_deb", afficheMontant(abs($info["solde_debut"]), false));
        $compte->new_child("solde_debut_cre", afficheMontant(NULL, false));
        $total_solde_debut_debit+=abs($info["solde_debut"]);
      }
      $total_solde_mvt_debit+=$info["total_debits"];
      $compte->new_child("total_debits", afficheMontant($info["total_debits"], false));
      $total_solde_mvt_credit+=$info["total_credits"];
      $compte->new_child("total_credits", afficheMontant($info["total_credits"], false));
      $solde_periode=recupMontant($info["total_credits"])-recupMontant($info["total_debits"]);
      if($solde_periode<0){
      $compte->new_child("calcul_debit", afficheMontant(abs($solde_periode), false));
       $compte->new_child("calcul_credit", afficheMontant(0, false));
       $total_solde_periode_debit+=abs($solde_periode);
      }else{
      $compte->new_child("calcul_debit", afficheMontant(0, false));
      $compte->new_child("calcul_credit", afficheMontant(abs($solde_periode), false));
      $total_solde_periode_credit+=abs($solde_periode);
      }
      if($info["solde_fin"]>0){
      	$compte->new_child("solde_fin_deb", afficheMontant(NULL, false));
        $compte->new_child("solde_fin_cre", afficheMontant($info["solde_fin"], false));
         $total_solde_fin_credit+=$info["solde_fin"];
      }else{
      	$compte->new_child("solde_fin_deb", afficheMontant(abs($info["solde_fin"]), false));
        $compte->new_child("solde_fin_cre", afficheMontant(NULL, false));
        $total_solde_fin_debit+=abs($info["solde_fin"]);
      }

      if ($info["variation"] > 0) {
        $compte->new_child("variation", "+" . affichePourcentage($info["variation"], 2));
      } else
        if ($info["variation"] < 0) {
          $compte->new_child("variation", affichePourcentage($info["variation"], 2));
        }
    }//fin parcour num compte
    $compte = $comptable->new_child("compte", "");
    $compte->new_child("num", _("TOTAL"));
    $compte->set_attribute("total", 1);
    $compte->new_child("libel", htmlspecialchars("TOTAL", ENT_QUOTES, "UTF-8"));
    $compte->new_child("solde_debut_deb", afficheMontant($total_solde_debut_debit, false));
    $compte->new_child("solde_debut_cre", afficheMontant($total_solde_debut_credit, false));
    $compte->new_child("total_debits", afficheMontant($total_solde_mvt_debit, false));
    $compte->new_child("total_credits", afficheMontant($total_solde_mvt_credit, false));
    $compte->new_child("calcul_debit", afficheMontant($total_solde_periode_debit, false));
    $compte->new_child("calcul_credit", afficheMontant($total_solde_periode_credit, false));
    $compte->new_child("solde_fin_deb", afficheMontant($total_solde_fin_debit, false));
    $compte->new_child("solde_fin_cre", afficheMontant($total_solde_fin_credit, false));

  }
  $agences=$root->new_child("agences", "");
  if(isSiege()){
    //Liste des agences consolidées
    $list_agence= getListAgenceConsolide();
    foreach($list_agence as $id_ag =>$data_agence) {
      $enreg_agence=$agences->new_child("enreg_agence", "");
      $enreg_agence->new_child("is_siege", 'true');
      $enreg_agence->new_child("id_ag", $data_agence['id']);
      $enreg_agence->new_child("libel_ag", $data_agence['libel']);
      $enreg_agence->new_child("date_max", $data_agence['date_dernier_mouv']);

    }
  }else{
  	 $enreg_agence=$agences->new_child("enreg_agence", "");
     $enreg_agence->new_child("is_siege", 'false');
  }
  return $document->dump_mem(true);
}

/**
 * Génération du code XML pour le rapport de operations diverses
 * T437
 * @author Kheshan.A.G
 */

function xml_rapport_oper_div( $date_debut ,$date_fin ,$type_operation, $login , $export_csv = false) {
	global $global_multidevise;
	global $global_monnaie;
	global $global_id_agence;
	
	//Création racine
	$document = create_xml_doc("rapport_op_div", "rapport_op_div.dtd");
	
   //les données pour le rapport
	$DATAS = donnnes_oper_div( $date_debut ,$date_fin ,$type_operation ,$login   );
	if ($DATAS == NULL)
		return NULL;

	//date du rapport: date du jour
	$export_date = date("d/m/Y");
	
	$root = $document->root();
	//En-tête généraliste
	gen_header($root, 'GUI-OPE-DIV');
	//En-tête contextuel
	
	$header_contextuel = $root->new_child("header_contextuel", "");
	if (($login == "0") || ($login == "") || ($login == null)) {
		$criteres = array (
				_("Login") => _("Tous"),
				_("Date") => date($export_date),//le date du rapport =date du jour
				_("Date début ") => date($date_debut),
				_("Date fin ") => date($date_fin)
				
		);
	} else {
		$criteres = array (
				_("Login") => $login,
				_("Date du rapport") => date($export_date),//le date du rapport =date du jour
				_("Date début ") => date($date_debut),
				_("Date fin ") => date($date_fin)
		);
	}
	// filtre par type operation
	if (($type_operation == "0") || ($type_operation == "") || ($type_operation == null)) {
		$criteres = array_merge ( $criteres, array (
				_ ( "Type d'opération" ) => _ ( "Tous" )
		) );
	} else {
		$criteres = array_merge ( $criteres, array (
				_ ( "Type d'opération" ) => getLibelOperation ( $type_operation) 
		) );
	}
	gen_criteres_recherche($header_contextuel, $criteres);
	
	$total = $root->new_child("total", afficheMontant(($DATAS["total"]/2) , true ));
	foreach ($DATAS as $key => $value){
		if(is_array($DATAS[$key])){
		$ligne = $root->new_child("ligne", "");
		$details = $ligne->new_child("details", "");
		$details->new_child("num_transaction",  $value["num_transaction"]);
		$details->new_child("login",  $value["login"]);
		$details->new_child("date", pg2phpDAte( $value["date"]));
		$details->new_child("libel_ecriture",  getLibellEcriture($value["libel_ecriture"]));
		$details->new_child("num_client",  $value["num_client"]);
		$details->new_child("montant", afficheMontant($value["montant"], true) );
		}
	}

	return $document->dump_mem(true);

}


/**
 * Génération du code XML pour les rapports sur les mouvements comptable d'une période
 * @param array $DATA Liste des mmouvements de la période
 * @param array $liste_criteres Liste des critères de sélection
 * @return array Liste opérations comptables
 * @since 1.0.8
 */
function xml_journal_cpt($DATA, $list_criteres) {
  global $adsys;
  global $global_multidevise;

  $document = create_xml_doc("journal_comptable", "journal_comptable.dtd");

  //Définition de la racine
  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'CPT-JOU');

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $list_criteres);

  $total_debit = 0;
  $total_credit = 0;

  //Body
  if (is_array($DATA["lignes_journal"])) {
    $tmp_oper = 0;
    $tmp_date = "";
    $tmp_ecr = "";
    foreach ($DATA["lignes_journal"] as $value) {
      setMonnaieCourante($value["devise"]);
      $ligne = $root->new_child("ligne", "");

      // La date comptable
      if (($tmp_date != pg2phpDAte($value["date_comptable"])) || ($tmp_oper != $value["id_his"])) { // Nouvelle date
        $ligne->new_child("date_comptable", pg2phpDAte($value["date_comptable"]));
      } else { // On travaille toujours pour la meme date
        $ligne->new_child("date_comptable", "");
      }

      // Données liées à la fonction
      if ($tmp_oper != $value["id_his"]) { // Nouvelle fonction
        $fonction = adb_gettext($adsys["adsys_fonction_systeme"][$value["type_fonction"]]);
        $ligne->new_child("fonction", $fonction);
        $ligne->new_child("num_piece", $value["id_his"]);
      } else { // On travaille toujours dans la mme fonction
        $ligne->new_child("fonction", "");
        $ligne->new_child("num_piece", "");
      }

      // Infos liées à l'écriture
      if (($tmp_ecr != $value["ref_ecriture"]) || ($value["ref_ecriture"] == NULL)) { // Nouvelle écriture ou reprise d'une BD de la version 1
        $ligne->new_child("ref_ecriture", $value["ref_ecriture"]);
         $libel_ecriture = new Trad($value["libel_ecriture"]);
          $libel_ecriture = $libel_ecriture->traduction();

        if(in_array($value['type_operation'], $adsys["adsys_operation_cheque_infos"]) ){
          $libel_ecriture = getChequeno($value["id_his"],$libel_ecriture,$value['info_ecriture']);
        }

        $ligne->new_child("operation", htmlspecialchars($libel_ecriture, ENT_QUOTES, "UTF-8"));
      } else { // On travaille toujours pour la meme écriture
        $ligne->new_child("ref_ecriture", "");
        $ligne->new_child("operation", "");
      }

      // Le numéro du compte comptable
      $ligne->new_child("compte", $value["compte"]);

      // Le libellé du compte comptable
      $ligne->new_child("libel_cpte", htmlspecialchars($value["libel_cpte_comptable"], ENT_QUOTES, "UTF-8"));

      // Le montant de débit ou de crédit
      if ($value["sens"] == "d") {
        $ligne->new_child("montant_debit", afficheMontant($value["montant"], true));
        $total_debit += $value["montant"];
      } else
        if ($value["sens"] == "c") {
          $ligne->new_child("montant_credit", afficheMontant($value["montant"], true));
          $total_credit += $value["montant"];
        }

      $tmp_oper = $value["id_his"];
      $tmp_date = pg2phpDate($value["date_comptable"]);
      $tmp_ecr = $value["ref_ecriture"];
    }// fin parcour mvt
    //mettre les totaux si on est en mono devise
    if( !$global_multidevise ){
    	$total_ligne=$root->new_child("totaux_ligne", "");
    	$total_ligne->new_child("total_debit", afficheMontant($total_debit, true));
    	$total_ligne->new_child("total_credit", afficheMontant($total_credit, true));
    }

  }

  return $document->dump_mem(true);
}

/**
 * Génération du code XML du rapport des mouvements reflexifs entre le siège et les agences
 * <li>
 *     <ul> mouvements passés par la fonction 473 (Gestion opérations siège/agence) sur les comptes reflets</ul>
 *     <ul> compte au débit de l'opération 600: Dépôt au siège</ul>
 *     <ul> compte au crédit de l'opération 601: Dépôt agence</ul>
 *     <ul> compte au crédit de l'opération 602: Emprunt auprès du siège</ul>
 *     <ul> compte au débit de l'opération 603: Prêts aux agences</ul>
 *     <ul> compte au débit de l'opération 604: Titres de participations</ul>
 *     <ul> compte au crédit de l'opération 605: Parts sociales agence</ul>
 *     <ul> compte au débit de l'opération 606: Participation aux charges du réseau</ul>
 *     <ul> compte au crédit de l'opération 607: Refacturatiion des charges du réseau</ul>
 * </li>
 * @author Papa
 * @since 2.9
 * @param array $DATA Liste des mouvements de la période
 * @param array $liste_criteres Liste des critères de sélection
 * @return array Liste opérations comptables
 */
function xml_mouvements_reciproques($DATA, $list_criteres,$id_agence=NULL) {
  global $adsys;

  $document = create_xml_doc("journal_annulations", "journal_annulations.dtd");

  //définition de la racine
  $root = $document->root();

  //En-tête généraliste
  if($id_agence==NULL){
  	gen_header($root, 'CPT-JAN');
  }else{ //multiagence, mettre le nom de l'agence selectionné'
  	setGlobalIdAgence($id_agence);
  	gen_header($root, 'CPT-JAN');
  	resetGlobalIdAgence();
  }

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $list_criteres);

  // totaux par devise
  $SESSION_VARS['total_devise'] = array ();

  //body
  if (is_array($DATA["lignes_journal"])) {
    $tmp_oper = 0;
    $tmp_date = "";
    $tmp_ecr = "";
    foreach ($DATA["lignes_journal"] as $value) {
      setMonnaieCourante($value["devise"]);
      $ligne = $root->new_child("ligne", "");
      //On selectionne une agence
      setGlobalIdAgence($value["id_ag"]);
      // La date comptable
      if (($tmp_date != pg2phpDAte($value["date_comptable"])) || ($tmp_oper != $value["id_his"])) // Nouvelle date
        $ligne->new_child("date_comptable", pg2phpDAte($value["date_comptable"]));
      else // On travaille toujours pour la meme date
        $ligne->new_child("date_comptable", "");

      // Données liées à la fonction
      if ($tmp_oper != $value["id_his"]) { // Nouvelle fonction
        $fonction = adb_gettext($adsys["adsys_fonction_systeme"][$value["type_fonction"]]);
        $ligne->new_child("fonction", $fonction);
        $ligne->new_child("num_piece", $value["id_his"]);
      } else { // On travaille toujours dans la mme fonction
        $ligne->new_child("fonction", "");
        $ligne->new_child("num_piece", "");
      }

      // Infos liées à l'écriture
      if (($tmp_ecr != $value["ref_ecriture"]) || ($value["ref_ecriture"] == NULL)) { // Nouvelle écriture ou reprise d'une BD de la version 1
        $ligne->new_child("ref_ecriture", $value["ref_ecriture"]);
        $libel_ecriture = new Trad($value['libel_ecriture']);
        $ligne->new_child("operation", htmlspecialchars($libel_ecriture->traduction(), ENT_QUOTES, "UTF-8"));
      } else { // On travaille toujours pour la meme écriture
        $ligne->new_child("ref_ecriture", "");
        $ligne->new_child("operation", "");
      }

      // Le numéro du compte comptable
      $ligne->new_child("compte", $value["compte"]);

      // Le libellé du compte comptable
      $ligne->new_child("libel_cpte", htmlspecialchars(getLibelleValable($value["num_cpte_comptable"], 'ad_cpt_comptable', $value["date_comptable"]), ENT_QUOTES, "UTF-8"));

      // Le montant de débit ou de crédit
      if ($value["sens"] == "d") {
        $ligne->new_child("montant_debit", afficheMontant($value["montant"], true));
        $SESSION_VARS['total_devise'][$value["devise"]]['debit'] += $value["montant"];
      } else
        if ($value["sens"] == "c") {
          $ligne->new_child("montant_credit", afficheMontant($value["montant"], true));
          $SESSION_VARS['total_devise'][$value["devise"]]['credit'] += $value["montant"];
        }

      $tmp_oper = $value["id_his"];
      $tmp_date = pg2phpDate($value["date_comptable"]);
      $tmp_ecr = $value["ref_ecriture"];
      resetGlobalIdAgence();
    }

    // lignes des totaux
    foreach ($SESSION_VARS['total_devise'] as $key => $value) {
      $ligne = $root->new_child("ligne_totaux", "");
      $ligne->new_child("totaux", "Total $key :");
      if ($value['debit'] != NULL)
        $ligne->new_child("total_debit", afficheMontant($value['debit']));
      else
        $ligne->new_child("total_debit", afficheMontant(0));

      if ($value['credit'] != NULL)
        $ligne->new_child("total_credit", afficheMontant($value['credit']));
      else
        $ligne->new_child("total_credit", afficheMontant(0));
    }

  }

  return $document->dump_mem(true);
}

/*
 * Fonction qui génère le code XML pour le rapport Grand Livre Comptable
 */
function xml_grandlivre($DATA, $titre, $condense) {

  global $global_id_agence;
	$document = create_xml_doc("grandlivre", "grandlivre.dtd");

  //Définition de la racine
  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'CPT-GLI', " : " . $titre);

  //body
  if (is_array($DATA)) {
  	//récupérer les comptes comptables avant la boucle
  	$infos = getComptesComptables();
    foreach ($DATA as $key => $value) {
      $compte = $root->new_child("compte", "");
      $intitule = $key . " " . $infos[$key]["libel_cpte_comptable"];
      setMonnaieCourante($infos[$key]["devise"]);
      $libel_cpte = $compte->new_child("libel_cpte", htmlspecialchars($intitule, ENT_QUOTES, "UTF-8"));
      $libel_cpte = $compte->new_child("solde_fin_periode",afficheMontant(abs($value['solde_fin']),true));

      if (is_array($value)) {
        foreach ($value as $key1 => $value1) {
          if (count($value1) > 1) {
            setMonnaieCourante($value1["devise"]);
            $ligne = $compte->new_child("ligne", "");
             if(!$condense){
			      	$ligne->set_attribute("condense", 1);
			      }
			      else{
			      	$ligne->set_attribute("condense", 2);
			      }
            $ligne->set_attribute("numero", $key1);
            $ligne->new_child("piece", $value1["piece"]);
            $ligne->new_child("date", pg2phpDAte($value1["date"]));
            $libel_ecriture = new Trad($value1["libel"]);
            $ligne->new_child("libel", htmlspecialchars($libel_ecriture->traduction(), ENT_QUOTES, "UTF-8"));
            $ligne->new_child("histo", $value1["id_his"]);
            $ligne->set_attribute("niveau", 0);
            if($value1["id_client"] > 0) {
              $libel_client =  $value1["id_client"];
            }
            else {
              $libel_client = "n/a";
            }
            $ligne->new_child("client",$libel_client);


            if( $key1 == 0 ) {
            	 $ligne->set_attribute("niveau", 1);//mettre la premiere ligne en gras
            }
            if (isset ($value1["debit"])) {
              $ligne->new_child("debit", afficheMontant($value1["debit"], true));
              $ligne->new_child("credit", "");
            } else {
              $ligne->new_child("debit", "");
              $ligne->new_child("credit", afficheMontant($value1["credit"], true));
            }
          }
        }
      }

      // Ligne pour le solde débiteur ou créditeur
      $ligne = $compte->new_child("ligne", "");
			if(!$condense){
			   $ligne->set_attribute("condense", 1);
			  }
			  else{
			   $ligne->set_attribute("condense", 2);
			 }
      $ligne->set_attribute("niveau", 1);
      $ligne->new_child("histo", "");
      $ligne->new_child("client", "");
      $ligne->new_child("date", "");
      $ligne->new_child("libel", "");

      $solde = abs($value["total_debit"] - $value["total_credit"]);
      if ($value["total_debit"] > $value["total_credit"]) {
        $ligne->new_child("piece", _("Solde débiteur"));
        $ligne->new_child("debit", "");
        $ligne->new_child("credit", afficheMontant($solde, true));
      } else
        if ($value["total_debit"] < $value["total_credit"]) {
          $ligne->new_child("piece", _("Solde créditeur"));
          $ligne->new_child("debit", afficheMontant($solde, true));
          $ligne->new_child("credit", "");
        } else {
          $ligne->new_child("piece", _("Solde")." ");
          $ligne->new_child("debit", afficheMontant(0, true));
          $ligne->new_child("credit", afficheMontant(0, true));
        }

      // Ligne pour les totaux du compte
      $ligne = $compte->new_child("ligne", "");
      if(!$condense){
			   $ligne->set_attribute("condense", 1);
			  }
			 else{
			 	$ligne->set_attribute("condense", 2);
			}
      $ligne->set_attribute("niveau", 2);
      $ligne->new_child("histo", "");
      $ligne->new_child("client", "");
      $ligne->new_child("date", "");
      $ligne->new_child("libel", "");
      $ligne->new_child("piece", _("Totaux"));

      if ($value["total_debit"] > $value["total_credit"]) {
        $ligne->new_child("debit", afficheMontant($value["total_debit"], true));
        $ligne->new_child("credit", afficheMontant(($value["total_credit"] + $solde), true));
      } else {
        $ligne->new_child("debit", afficheMontant(($value["total_debit"] + $solde), true));
        $ligne->new_child("credit", afficheMontant($value["total_credit"], true));
      }
    }
  }
  return $document->dump_mem(true);

}

function xml_compte_de_resultat($DATA, $titre,$id_agence=NULL) {

  global $global_monnaie;

  reset($DATA);

  // Création racine
  $document = create_xml_doc("compte_de_resultat", "compte_de_resultat.dtd");

  //Element root
  $root = $document->root();

  //En-tête généraliste

  if($id_agence==NULL){
  	gen_header($root, 'CPT-RES',  $titre);
  }else{ //multiagence, mettre le nom de l'agence selectionné'
  	setGlobalIdAgence($id_agence);
  	gen_header($root, 'CPT-RES',  $titre);
  	resetGlobalIdAgence();
  }
  // Définition d'une ligne du compte de résultat
  while (list ($numCompte, $infos) = each($DATA)) {
    $compte = $root->new_child("compte", "");
    if ($infos["compte_charge"] == 'TOTAL') {
      $compte->set_attribute("total", 1);
    } else {
      $compte->set_attribute("total", 0);
    }

    // Niveau des comptes de charge et de produit de la ligne
    if ($infos["compte_charge"] == '') {
      $nivchge = 0;
    } else {
      $nivchge = substr_count($infos["compte_charge"], ".") + 1;
    }

    if ($infos["compte_produit"] == '') {
      $nivprod = 0;
    } else {
      $nivprod = substr_count($infos["compte_produit"], ".") + 1;
    }

    // Définition des propriétés niveau des comptes
    $compte->set_attribute("nivchge", $nivchge);
    $compte->set_attribute("nivprod", $nivprod);

    $compte->new_child("compte_charge", $infos["compte_charge"]);
    $compte->new_child("libel_charge", htmlspecialchars($infos["libel_charge"], ENT_QUOTES, "UTF-8"));
    $compte->new_child("solde_charge", afficheMontant($infos["solde_charge"], false));
    $compte->new_child("compte_produit", $infos["compte_produit"]);
    $compte->new_child("libel_produit", htmlspecialchars($infos["libel_produit"], ENT_QUOTES, "UTF-8"));
    $compte->new_child("solde_produit", afficheMontant($infos["solde_produit"], false));
  }
  $agences=$root->new_child("agences", "");
  if(isSiege()){
   //Liste des agences consolidées
   $list_agence= getListAgenceConsolide();
   foreach($list_agence as $id_ag =>$data_agence) {
     $enreg_agence=$agences->new_child("enreg_agence", "");
     $enreg_agence->new_child("is_siege", "true");
     $enreg_agence->new_child("id_ag", $data_agence['id']);
     $enreg_agence->new_child("libel_ag", $data_agence['libel']);
     $enreg_agence->new_child("date_max", $data_agence['date_dernier_mouv']);
   }
  }else{
  	 $enreg_agence=$agences->new_child("enreg_agence", "");
     $enreg_agence->new_child("is_siege", "false");
  }

  return $document->dump_mem(true);
}

function xml_bilan($DATA, $titre,$id_agence=NULL) {

  reset($DATA);

  //Création racine
  $document = create_xml_doc("bilan", "bilan.dtd");

  //Element root
  $root = $document->root();

  //En-tête généraliste

  //En-tête généraliste
  if($id_agence==NULL){
  	gen_header($root, 'CPT-BIL',  $titre);
  }else{ //multiagence, mettre le nom de l'agence selectionné'
  	setGlobalIdAgence($id_agence);
  	gen_header($root, 'CPT-BIL',  $titre);
  	resetGlobalIdAgence();
  }


  //Définition d'une ligne du compte de résultat
  while (list ($cle, $infos) = each($DATA)) {
    $compte = $root->new_child("compte", "");
    if ($infos["compte_actif"] == 'TOTAL') {
      $compte->set_attribute("total", 1);
    } else {
      $compte->set_attribute("total", 0);
    }

    // Niveau des comptes
    if ($infos["compte_actif"] == '') {
      $nivact = 0;
    } else {
      $nivact = substr_count($infos["compte_actif"], ".") + 1;
    }

    if ($infos["compte_passif"] == '') {
      $nivpass = 0;
    } else {
      $nivpass = substr_count($infos["compte_passif"], ".") + 1;
    }

    // Définition des propriétés niveau des comptes
    $compte->set_attribute("nivact", $nivact);
    $compte->set_attribute("nivpass", $nivpass);

    $compte->new_child("compte_actif", $infos["compte_actif"]);
    $compte->new_child("libel_actif", htmlspecialchars($infos["libel_actif"], ENT_QUOTES, "UTF-8"));
    $compte->new_child("solde_actif", afficheMontant($infos["solde_actif"], false));
    $compte->new_child("amort_actif", afficheMontant($infos["amort_actif"], false));
    $compte->new_child("net_actif", afficheMontant($infos["net_actif"], false));
    $compte->new_child("compte_passif", $infos["compte_passif"]);
    $compte->new_child("libel_passif", htmlspecialchars($infos["libel_passif"], ENT_QUOTES, "UTF-8"));
    $compte->new_child("solde_passif", afficheMontant($infos["solde_passif"], false));
  }
  $agences=$root->new_child("agences", "");
  if(isSiege()){
   //Liste des agences consolidées
   $list_agence= getListAgenceConsolide();
   foreach($list_agence as $id_ag =>$data_agence) {
     $enreg_agence=$agences->new_child("enreg_agence", "");
     $enreg_agence->new_child("is_siege", "true");
     $enreg_agence->new_child("id_ag", $data_agence['id']);
     $enreg_agence->new_child("libel_ag", $data_agence['libel']);
     $enreg_agence->new_child("date_max", $data_agence['date_dernier_mouv']);
   }
  }else{
   	 $enreg_agence=$agences->new_child("enreg_agence", "");
     $enreg_agence->new_child("is_siege", "false");
   }
  return $document->dump_mem(true);
}
/**
 *
 *
 */
function xml_bilan_BNR($DATA,$type_etat, $titre,$id_agence=NULL,$csv=false) {

  //reset($DATA);

  //Création racine
  $document = create_xml_doc("bilan_bnr", "bilan_bnr.dtd");

  //Element root
  $root = $document->root();

  //code rapport
  	$code_rapport='CPT-BSH';


  //En-tête généraliste
  if($id_agence==NULL){
  	gen_header($root, $code_rapport,  $titre);
  }else{ //multiagence, mettre le nom de l'agence selectionné'
  	setGlobalIdAgence($id_agence);
  	gen_header($root, $code_rapport,  $titre);
  	resetGlobalIdAgence();
  }
  //enlever les resultats des exos ds $DATA
  $tab_res_exo=$DATA['resultats_exo'];
  unset($DATA['resultats_exo']);
  foreach($DATA as $cle =>$compart){
  	$elt_compart=$root->new_child("compartiment", "");
  	$elt_compart->set_attribute("numero",$cle);
  	if($cle==1) {//actif (A)
  		$coef_debiteur=-1;
  	} elseif( $cle == 2 ) {//passif (B)
  		$coef_debiteur=1;
  	}
    foreach ( $compart as $cle1 => $infos ){
    	if( $infos['niveau']== 0 ){
    		if(!isset( $elt_entete[$infos['compartiment']] ) ){
    		  $elt_entete[$infos['compartiment']]=$elt_compart->new_child("entete");
    		  $index=1;
    	  }else {
    		  $index += 1;//$elt_entete[$infos['compartiment']]->childNodes();
    	  }
    	  if($index == 2 ) $tab_total=$infos;//tab contenant les totaux de chaque compartiment
    	  $elt_entete[$infos['compartiment']]->new_child('entete_'.$index,htmlspecialchars($infos["libel"], ENT_QUOTES, "UTF-8"));

    	} else {
    		$solde=$infos["solde"] * $coef_debiteur;
    		$net=$infos["net"] * $coef_debiteur;

	    	$elt_poste=$elt_compart->new_child("poste");
	  		$elt_poste->new_child("code", $infos["code"]);
			  $elt_poste->new_child("libel", htmlspecialchars($infos["libel"], ENT_QUOTES, "UTF-8"));
			  $elt_poste->new_child("solde", afficheMontant($solde, false,$csv));
			  $elt_poste->new_child("amort_actif", afficheMontant($infos["amortissement"], false,$csv));
			  $elt_poste->new_child("net_actif", afficheMontant($net, false,$csv));
			  $elt_poste->new_child("niveau", $infos["niveau"]);
      }
    }
    //mettre le resulat des exercices ouverts
    if( $cle == 2 ){
    	foreach($tab_res_exo as $cleexo=>$exo){
      	$elt_poste=$elt_compart->new_child("poste");
			 	$elt_poste->new_child("code","");
				$elt_poste->new_child("libel", htmlspecialchars($exo["libel"], ENT_QUOTES, "UTF-8"));
				$elt_poste->new_child("solde", afficheMontant($exo['solde'], false,$csv));
				$elt_poste->new_child("amort_actif", afficheMontant("", false,$csv));
				$elt_poste->new_child("net_actif", afficheMontant($exo['solde'], false,$csv));
				$elt_poste->new_child("niveau", $tab_total["niveau"]+1);
      	$total_exo += $exo['solde'];
      }
    }
    //Total
   	$solde= $coef_debiteur * $tab_total["solde"] + $total_exo;
    $net= $coef_debiteur * $tab_total["net"] + $total_exo;

    $elt_poste=$elt_compart->new_child("poste");
		$elt_poste->new_child("code","");
		$elt_poste->new_child("libel"," TOTAL " . htmlspecialchars($tab_total["libel"], ENT_QUOTES, "UTF-8"));
		$elt_poste->new_child("solde", afficheMontant($solde, false,$csv));
		$elt_poste->new_child("amort_actif", afficheMontant($tab_total["amortissement"], false,$csv));
		$elt_poste->new_child("net_actif", afficheMontant($net, false,$csv));
		$elt_poste->new_child("niveau", $tab_total["niveau"]+1);
  }


  $agences=$root->new_child("agences", "");
  if(isSiege()){
   //Liste des agences consolidées
   $list_agence= getListAgenceConsolide();
   foreach($list_agence as $id_ag =>$data_agence) {
     $enreg_agence=$agences->new_child("enreg_agence", "");
     $enreg_agence->new_child("is_siege", "true");
     $enreg_agence->new_child("id_ag", $data_agence['id']);
     $enreg_agence->new_child("libel_ag", $data_agence['libel']);
     $enreg_agence->new_child("date_max", $data_agence['date_dernier_mouv']);
   }
  }else{
   	 $enreg_agence=$agences->new_child("enreg_agence", "");
     $enreg_agence->new_child("is_siege", "false");
   }
  return $document->dump_mem(true);
}
/**
 *
 *
 */

function xml_compte_resultat_BNR($DATA,$type_etat, $titre,$id_agence=NULL,$csv=false) {

  //reset($DATA);

  //Création racine
  $document = create_xml_doc("compte_resultat", "compte_de_resultat_BNR.dtd");

  //Element root
  $root = $document->root();

  //code rapport
 	$code_rapport='CPT-IST';
 	$compart_charge=3;
 	$compart_produit=4;

  //En-tête généraliste
  if($id_agence==NULL){
  	gen_header($root, $code_rapport,  $titre);
  }else{ //multiagence, mettre le nom de l'agence selectionné'
  	setGlobalIdAgence($id_agence);
  	gen_header($root, $code_rapport,  $titre);
  	resetGlobalIdAgence();
  }

  foreach($DATA as $cle =>$compart){
  	$elt_compart[$cle]=$root->new_child("compartiment", "");
  	$elt_compart[$cle]->set_attribute("numero",$cle);
    foreach ($compart as $cle1 => $infos ){
    	if($infos['niveau']==0){
    		if(!isset( $elt_entete[$infos['compartiment']] ) ){
    		  $elt_entete[$infos['compartiment']]=$elt_compart[$cle]->new_child("entete");
    		  $index=1;
    	  }else {
    		  $index+=1;//$elt_entete[$infos['compartiment']]->childNodes();
    	  }
    	  if($index==2) $tab_total[$cle]=$infos;//tab contenant les totaux du compartiment
    	  $elt_entete[$infos['compartiment']]->new_child('entete_'.$index,htmlspecialchars($infos["libel"], ENT_QUOTES, "UTF-8"));

    	} else {
	    	$elt_poste=$elt_compart[$cle]->new_child("poste");
	  		$elt_poste->new_child("code", $infos["code"]);
			  $elt_poste->new_child("libel", htmlspecialchars($infos["libel"], ENT_QUOTES, "UTF-8"));
			  $elt_poste->new_child("solde", afficheMontant($infos["solde"], false,$csv));
			  $elt_poste->new_child("niveau", $infos["niveau"]);
      }
    }
  }
  //Calcul du deficit-Excedent et mettre les totaux ds chaque compartiment
  $excent_deficit=$tab_total[$compart_produit]['solde']-$tab_total[$compart_charge]['solde'];
  if($excent_deficit<0) {
  	$excent_deficit_charge=0;
  	$excent_deficit_produit=(-1)*$excent_deficit;//deficit positif
  	$total_charge=$tab_total[$compart_charge]["solde"];
  	$total_produit=$tab_total[$compart_produit]["solde"]+(-1)*$excent_deficit;//pour equilibré le resultat
  } elseif( $excent_deficit >0){
  	$excent_deficit_charge=$excent_deficit;
  	$excent_deficit_produit=0;
  	$total_charge=$tab_total[$compart_charge]["solde"]+$excent_deficit;
  	$total_produit=$tab_total[$compart_produit]["solde"];
  }
  //charge
  $elt_poste=$elt_compart[$compart_charge]->new_child("poste");
  $libel="(59) "._("Gains net sur la periode (Profit)[[Net earnings for the period (Profit)]]"); //$libel=_("(59) Net earnings for the period (Profit)");
	$elt_poste->new_child("code","");
	$elt_poste->new_child("libel",$libel);
	$elt_poste->new_child("solde", afficheMontant($excent_deficit_charge, false,$csv));
	$elt_poste->new_child("niveau", $tab_total[$compart_charge]["niveau"]+1);

	$elt_poste=$elt_compart[$compart_charge]->new_child("poste");
	$elt_poste->new_child("code","");
	$elt_poste->new_child("libel"," TOTAL " . htmlspecialchars($tab_total[$compart_charge]["libel"], ENT_QUOTES, "UTF-8"));
	$elt_poste->new_child("solde", afficheMontant($total_charge, false,$csv));
	$elt_poste->new_child("niveau", $tab_total[$compart_charge]["niveau"]+1);
	//produit
	$elt_poste=$elt_compart[$compart_produit]->new_child("poste");
	$libel="(59) "._("Gains net sur la periode (Perte)[[Net earnings for the period (Loss)]]"); // $libel=_("(59) Net earnings for the period (Loss)");
	$elt_poste->new_child("code","");
	$elt_poste->new_child("libel", htmlspecialchars($libel, ENT_QUOTES, "UTF-8"));
	$elt_poste->new_child("solde", afficheMontant($excent_deficit_produit, false,$csv));
	$elt_poste->new_child("niveau", $tab_total[$compart_produit]["niveau"]+1);

	$elt_poste=$elt_compart[$compart_produit]->new_child("poste");
	$elt_poste->new_child("code","");
	$elt_poste->new_child("libel"," TOTAL " . htmlspecialchars($tab_total[$compart_produit]["libel"], ENT_QUOTES, "UTF-8"));
	$elt_poste->new_child("solde", afficheMontant($total_produit, false,$csv));
	$elt_poste->new_child("niveau", $tab_total[$compart_produit]["niveau"]+1);



  $agences=$root->new_child("agences", "");
  if(isSiege()){
   //Liste des agences consolidées
   $list_agence= getListAgenceConsolide();
   foreach($list_agence as $id_ag =>$data_agence) {
     $enreg_agence=$agences->new_child("enreg_agence", "");
     $enreg_agence->new_child("is_siege", "true");
     $enreg_agence->new_child("id_ag", $data_agence['id']);
     $enreg_agence->new_child("libel_ag", $data_agence['libel']);
     $enreg_agence->new_child("date_max", $data_agence['date_dernier_mouv']);
   }
  }else{
   	 $enreg_agence=$agences->new_child("enreg_agence", "");
     $enreg_agence->new_child("is_siege", "false");
   }
  return $document->dump_mem(true);
}



/**
 * Fonction de création du doccument XML pour Simulation intermédiaire
 * @author Mamadou Mbaye
 * @param  array $DATA
 * @return string code xml
**/
function xml_situation_intermediaire($DATA) {

  global $global_monnaie;

  reset($DATA);

  // Création racine
  $document = create_xml_doc("simulation_intermediaire", "simulation_intermediaire.dtd");

  //Element root
  $root = $document->root();

  gen_header($root, 'CPT-SIT', " ($global_monnaie)");

  // Définition d'une ligne du compte de résultat
  while (list ($cle, $infos) = each($DATA)) {
    if ($infos["tot"] != 0) {
      $compte = $root->new_child("compte", "");
      $compte->new_child("numero", $cle);
      $compte->new_child("libel", htmlspecialchars($infos["libel"], ENT_QUOTES, "UTF-8"));
      $compte->new_child("mn", afficheMontant(abs($infos["mn"]), false));
      $compte->new_child("me", afficheMontant(abs($infos["me"]), false));
      $compte->new_child("tot", afficheMontant(abs($infos["tot"]), false));
    }
  }

  return $document->dump_mem(true);
}

function xml_cloture_periodique($cloture, $DATA, $titre) {

  global $global_monnaie;

  reset($DATA);

  // Création racine
  $document = create_xml_doc("cloture_periodique", "cloture_periodique.dtd");

  //Element root
  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'DET-CLO', " : " . $titre);

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  $header_contextuel->new_child("id_cloture", $cloture["id_clot_per"]);
  $header_contextuel->new_child("date_cloture", pg2phpDate($cloture["date_clot_per"]));
  $header_contextuel->new_child("id_exo", $cloture["id_exo"]);

  $tot_debit = 0;
  $tot_credit = 0;

  setMonnaieCourante($global_monnaie);
  // Body
  while (list ($key, $info) = each($DATA)) {
    $compte = $root->new_child("compte", "");
    $compte->set_attribute("total", 0);
    $compte->new_child("num", $info["num_cpte_comptable_solde"]);

    // Recherche Libellé du compte comptable
    $temp = array ();
    $temp["num_cpte_comptable"] = $info["num_cpte_comptable_solde"];
    $cpte = getComptesComptables($temp);
    $devise = $cpte[$info["num_cpte_comptable_solde"]]["devise"];

    $compte->new_child("libel", $cpte[$info["num_cpte_comptable_solde"]]["libel_cpte_comptable"]);

    // Solde du compte à la clôture
    if ($info["solde_cloture"] < 0) { // alors le solde est débiteur
      $solde = calculeCV($devise, $global_monnaie, $info["solde_cloture"]);
      $compte->new_child("solde_debit", afficheMontant(abs($solde), true));
      $compte->new_child("solde_credit", "");
      $tot_debit += abs($solde);
    } else
      if ($info["solde_cloture"] > 0) { // alors le solde est créditeur
        $solde = calculeCV($devise, $global_monnaie, $info["solde_cloture"]);
        $compte->new_child("solde_credit", afficheMontant(abs($solde), true));
        $compte->new_child("solde_debit", "");
        $tot_credit += abs($solde);
      }
  }

  // Totaux
  $compte = $root->new_child("compte", "");
  $compte->set_attribute("total", 1);
  $compte->new_child("num", "TOTAL");
  $compte->new_child("libel", "");
  $compte->new_child("solde_debit", afficheMontant($tot_debit, false));
  $compte->new_child("solde_credit", afficheMontant($tot_credit, false));

  return $document->dump_mem(true);
}

function xml_schemas($DATA) {

  reset($DATA);

  // Création racine
  $document = create_xml_doc("schemas_comptables", "schemas_comptables.dtd");

  // Element root
  $root = $document->root();

  // En-tête généraliste
  gen_header($root, 'CPT-GOP', "");
  // Body
  $schema_compta = $root->new_child("schema_compta", "");
  foreach ($DATA as $key => $value) {
    $detail_schema = $schema_compta->new_child("detail_schema", "");
    $detail_schema->new_child("type_ope", $value["type_operation"]);
    $libel_ecriture = new Trad($value["libel_ope"]);
    $detail_schema->new_child("libel_ope", htmlspecialchars($libel_ecriture->traduction(), ENT_QUOTES, "UTF-8"));
    $detail_schema->new_child("cpte_debit", $value["cpte_debit"]);
    $detail_schema->new_child("cpte_credit", $value["cpte_credit"]);
  }

  return $document->dump_mem(true);
}

function xml_ratio_liquidite_BNR($DATA,$titre,$a_list_criteres,$csv=false) {

  reset($DATA);

  // Création racine
  $document = create_xml_doc("ratio_liq", "ratio_liquidite.dtd");

  // Element root
  $root = $document->root();

  // En-tête généraliste
  gen_header($root, 'CPT-LIR',$titre);
  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_research_criteria($header_contextuel, $a_list_criteres);
  // Body
  $elt_compart=$root->new_child("compartiment", "");
  $elt_compart->set_attribute("numero",1);
  $index=0;
  $tabsom_compart=array();
  foreach($DATA as $cle =>$compart){
  	$tabsom_compart[$cle]=0;
  	$elt_entete=$elt_compart->new_child("entete");
  	if($cle==1) {//actif (A)
  		$coef_debiteur=-1;
  		$libel=_("TOTAL LIQUID ASSETS  (A) ");
  	} elseif( $cle == 2 ) {//passif (B)
  		$coef_debiteur=1;
  		$libel=_(" TOTAL LIQUID LIABILITIES (B) ");
  	}
  	foreach ($compart as $cle1 => $infos ){
    	if($infos['niveau']==0){
    		$index++;
    		$elt_entete->new_child('entete_'.$index,htmlspecialchars($infos["libel"], ENT_QUOTES, "UTF-8"));
    	} else {
    		$solde=$infos["solde"] * $coef_debiteur;
	    	$elt_poste=$elt_compart->new_child("poste");
	  		$elt_poste->new_child("code", $infos["code"]);
			  $elt_poste->new_child("libel", htmlspecialchars($infos["libel"], ENT_QUOTES, "UTF-8"));
			  $elt_poste->new_child("solde", afficheMontant($solde,false, $csv));
			  $elt_poste->new_child("total", 0);
			  //calcul somme du groupe
			  $tabsom_compart[$cle] += $solde;
      }
    }
    //Total
    $elt_poste=$elt_compart->new_child("poste");
		$elt_poste->new_child("code","");
		$elt_poste->new_child("libel", htmlspecialchars($libel ,ENT_QUOTES, "UTF-8"));
		$elt_poste->new_child("solde", afficheMontant(($tabsom_compart[$cle]),false, $csv));
		$elt_poste->new_child("total", 1);

  }
  //calcul ratio de liquidité
  // Ratio liquidite(%)=(A/B)x100
  if($tabsom_compart[2]!= 0 ){
  	$ratio=( $tabsom_compart[1] / $tabsom_compart[2] ) * 100;
  }else{
  	$ratio=0;
  }

  $ratio=round($ratio,2);
  $elt_poste=$elt_compart->new_child("poste");
	$elt_poste->new_child("code","");
	$libel=" "._("LIQUIDITY RATIO = A / B %  (*)")." ";
	$elt_poste->new_child("libel", htmlspecialchars($libel ,ENT_QUOTES, "UTF-8"));
	$elt_poste->new_child("solde", $ratio."%");
	$elt_poste->new_child("total", 1);

  $agences=$root->new_child("agences", "");
  if(isSiege()){
   //Liste des agences consolidées
   $list_agence= getListAgenceConsolide();
   foreach($list_agence as $id_ag =>$data_agence) {
     $enreg_agence=$agences->new_child("enreg_agence", "");
     $enreg_agence->new_child("is_siege", "true");
     $enreg_agence->new_child("id_ag", $data_agence['id']);
     $enreg_agence->new_child("libel_ag", $data_agence['libel']);
     $enreg_agence->new_child("date_max", $data_agence['date_dernier_mouv']);
   }
  }else{
   	 $enreg_agence=$agences->new_child("enreg_agence", "");
     $enreg_agence->new_child("is_siege", "false");
   }
  return $document->dump_mem(true);
}


/**
 * Génération du code XML pour les rapports sur les mouvements comptables d'une période
 * @param array $DATA Liste des mmouvements concernant la gestion de la tva
 * @param array $liste_criteres Liste des critères de sélection
 * @return array Liste opérations comptables
 * @since 1.0.8
 */
function xml_declaration_tva($DATA, $list_criteres, $export_csv = false) {
  global $adsys;
  global $global_multidevise;

  $document = create_xml_doc("declaration_tva", "declaration_tva.dtd");

  //Définition de la racine
  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'CPT-TVA');

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $list_criteres);

  $infos_synthetiques = $header_contextuel->new_child("infos_synthetiques", "");
  if($DATA["mnt_tva"] >= 0)
		$infos_synthetiques->new_child("libel", mb_substr(_('Montant tva à décaisser '), 0, 50, "UTF-8"));
	else
		$infos_synthetiques->new_child("libel", mb_substr(_('Montant tva à reporter '), 0, 50, "UTF-8"));
	$infos_synthetiques->new_child("valeur",  afficheMontant(abs($DATA["mnt_tva"]), false, $export_csv));

  $total_debit = 0;
  $total_credit = 0;

  //Body
  if (is_array($DATA["detail_tva"])) {
    foreach ($DATA["detail_tva"] as $value) {
      setMonnaieCourante($value["devise"]);
      $ligne = $root->new_child("ligne", "");

      // La date comptable
        $ligne->new_child("date_comptable", pg2phpDAte($value["date_comptable"]));

        $fonction = adb_gettext($adsys["adsys_fonction_systeme"][$value["type_fonction"]]);
        $ligne->new_child("fonction", $fonction);
        $ligne->new_child("num_piece", $value["id_his"]);

      // Infos liées à l'écriture
        $ligne->new_child("ref_ecriture", $value["ref_ecriture"]);
         $libel_ecriture = new Trad($value["libel_ecriture"]);
        $ligne->new_child("operation", htmlspecialchars($libel_ecriture->traduction(), ENT_QUOTES, "UTF-8"));

      // Le numéro du compte comptable
      $ligne->new_child("compte", $value["compte"]);

      // Le libellé du compte comptable
      $ligne->new_child("libel_cpte", htmlspecialchars($value["libel_cpte_comptable"], ENT_QUOTES, "UTF-8"));

      // Le montant de débit ou de crédit
      if ($value["sens"] == "d") {
        $ligne->new_child("montant_debit", afficheMontant($value["montant"], true));
        $total_debit += $value["montant"];
      } else
        if ($value["sens"] == "c") {
          $ligne->new_child("montant_credit", afficheMontant($value["montant"], true));
          $total_credit += $value["montant"];
        }

    }// fin parcour mvt
    //mettre les totaux si on est en mono devise

    	$total_ligne=$root->new_child("totaux_ligne", "");
    	$total_ligne->new_child("total_debit", afficheMontant($total_debit, true));
    	$total_ligne->new_child("total_credit", afficheMontant($total_credit, true));

  }

  return $document->dump_mem(true);
}
?>