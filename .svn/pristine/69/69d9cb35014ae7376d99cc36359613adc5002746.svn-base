<?php
/*

Retrait d'un compte d'épargne client

Description :
  Ce module crée 3 écrans :
  * Rcp-1 : Choix d'un compte pour le retrait
  * Rcp-2 : Retrait du compte avec mouvements des comptes


  HD - 06/02/2002
*/
require_once 'lib/dbProcedures/epargne.php';
require_once 'modules/epargne/recu.php';
require_once 'lib/dbProcedures/billetage.php';
if ($global_nom_ecran == "Rex-1") {
global $global_nom_login, $global_id_profil;

//-------------------------------- ECRAN Rex-1 : choix du compte ------------------------------------------



  //afficher la liste des comptes du client puis le montant à retirer et ne pas oublier les frais d'opérations éventuels

  $html = new HTML_GEN2();

  $id_cpte = getBaseAccountID($global_id_client);
  $InfoCpteBase = getAccountDatas(getBaseAccountID ($global_id_client));
  $InfoProduitBase = getProdEpargne($InfoCpteBase["id_prod"]);

  $choix[$id_cpte] = $InfoCpteBase["num_complet_cpte"]." ".$InfoCpteBase["intitule_compte"];

  $html->addField("NumCpte", _("Numéro de compte"), TYPC_LSB);
  $html->setFieldProperties("NumCpte", FIELDP_ADD_CHOICES, $choix);

  $html->addField("mnt",_("Montant à retirer"),TYPC_MNT);

  //retrait express
  $html->setTitle(_("Retrait express")); //Si retrait express
  $html->setFieldProperties("NumCpte", FIELDP_DEFAULT, $id_cpte); //Compte de base par défaut
  $html->setFieldProperties("NumCpte", FIELDP_IS_LABEL, true);
  $access_solde = get_profil_acces_solde($global_id_profil, $InfoCpteBase["id_prod"]);
  $access_solde_vip = get_profil_acces_solde_vip($global_id_profil, $global_id_client);
  if(manage_display_solde_access($access_solde, $access_solde_vip)){// contrôle sur l'accès au solde
	  $html->addField("solde_reel",_("Solde réel"),TYPC_MNT);
	  $html->setFieldProperties("solde_reel", FIELDP_IS_LABEL, true);
	  $html->setFieldProperties("solde_reel",FIELDP_DEFAULT, $InfoCpteBase["solde"]);
	  $html->addField("solde",_("Solde disponible"),TYPC_MNT);
	  $html->setFieldProperties("solde", FIELDP_IS_LABEL, true);
	  $html->setFieldProperties("solde",FIELDP_DEFAULT, getSoldeDisponible($id_cpte));
	  $html->setOrder(NULL, array("NumCpte", "solde_reel","solde","mnt"));
  }else{
  	$html->setOrder(NULL, array("NumCpte", "mnt"));
  }
    $code_abo = 'epargne';
  $html->addTable("adsys_produit_epargne", OPER_INCLUDE,array("frais_retrait_cpt"));
    if ($InfoProduitBase["frais_retrait_spec"] == 't'){
        $type_de_frais = 'EPG_RET_ESPECES';
        $retrait_frais = getFraisRetrait($code_abo,$type_de_frais);
        $html->setFieldProperties("frais_retrait_cpt", FIELDP_IS_LABEL, true);
        $html->setFieldProperties("frais_retrait_cpt", FIELDP_DEFAULT, $retrait_frais['valeur']);
        $html->setFieldProperties("frais_retrait_cpt", FIELDP_CAN_MODIFY, true);
        $SESSION_VARS["frais_retrait_cpt"]= $retrait_frais['valeur'];
    }else {
        $html->setFieldProperties("frais_retrait_cpt", FIELDP_IS_LABEL, true);
        $html->setFieldProperties("frais_retrait_cpt", FIELDP_DEFAULT, $InfoProduitBase["frais_retrait_cpt"]);
        $html->setFieldProperties("frais_retrait_cpt", FIELDP_CAN_MODIFY, true);
        $SESSION_VARS["frais_retrait_cpt"]= $InfoProduitBase["frais_retrait_cpt"];
    }



  //aam temp
  $ChkJSexpress = "\n\tif (recupMontant(document.ADForm.mnt.value) > recupMontant(document.ADForm.solde.value)- recupMontant(document.ADForm.frais_retrait_cpt.value))";
  $ChkJSexpress .= "{msg += '- "._("Le montant du retrait augmenté des frais de retrait est supérieur au solde disponible")."\\n'; ADFormValid=false;};\n";

  $html->addJS(JSP_BEGIN_CHECK, "myJS",$ChkJSexpress);

  //Boutons
  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Rex-2');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-8');

  $html->buildHTML();
  echo $html->getHTML();
}

else if ($global_nom_ecran == "Rex-2") {
// ----------------------------------------- Ecran Rex-2 : confirmation saisie montant --------------------------------------

  global $global_id_client;

  unset($SESSION_VARS['id_dem']);
  if (isset($_GET['id_dem']) && $_GET['id_dem'] > 0) {

    $SESSION_VARS['id_dem'] = $_GET['id_dem'];

    $infoRetraitAttente = getRetraitAttenteExpressAutorise($SESSION_VARS['id_dem'], $global_id_client);

    $mnt = recupMontant($infoRetraitAttente['montant_retrait']);
    $frais_retrait_cpt = recupMontant($infoRetraitAttente['frais_retrait_cpt']);
    $mandat = trim($infoRetraitAttente['mandat']);
  }

  $SESSION_VARS['id_mandat'] = $mandat;
  $SESSION_VARS["NumCpte"] = getBaseAccountID($global_id_client); //Si retrait express

  // récupérer le infos sur le produit associé au compte sélectionné
  $InfoCpte = getAccountDatas($SESSION_VARS["NumCpte"]);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

  $SESSION_VARS["mnt"] = recupMontant($mnt);
  if (isset($frais_retrait_cpt))
    $SESSION_VARS["mnt_frais_retrait"] = recupMontant($frais_retrait_cpt);
  else
    $SESSION_VARS["mnt_frais_retrait"] = $SESSION_VARS["frais_retrait_cpt"];

  $html = new HTML_GEN2(_("Confirmation du montant à retirer"));

  $html->addField("mnt",_("Montant à retirer"),TYPC_MNT);
  $html->setFieldProperties("mnt", FIELDP_DEFAULT, $SESSION_VARS["mnt"]);
  $html->setFieldProperties("mnt", FIELDP_IS_LABEL, true);

  //Crontôler si le montant à retirer ne dépasse pas le montant plafond de retrait autorisé s'il y a lieu
	global $global_nom_login, $global_id_agence, $colb_tableau;
	$info_login = get_login_full_info($global_nom_login);
	$info_agence = getAgenceDatas($global_id_agence);
	$msg = "";
	if (!isset($SESSION_VARS['id_dem']) && $info_agence['plafond_retrait_guichet'] == 't'){
	  if($info_login['depasse_plafond_retrait'] == 'f' && $SESSION_VARS["mnt"] > $info_agence['montant_plafond_retrait']){
	 		//$msg = "<center>"._("Le montant demandé dépasse le montant plafond de retrait autorisé. Ce login n'est pas habilité à le faire.");
	 		//$msg .= " "._("Veuillez contacter votre administrateur.")."</center>";

        // Affichage de la confirmation
        $html_msg = new HTML_message("Demande autorisation de retrait");

        $html_msg->setMessage("<center><span style='color: #FF0000;'><br />Le montant demandé dépasse le montant plafond de retrait autorisé.</span><br /><br />Montant demandé = <span style='color: #FF0000;font-weight: bold;'>".afficheMontant($SESSION_VARS["mnt"], true)."</span><br/>Montant plafond de retrait autorisé = ".afficheMontant($info_agence['montant_plafond_retrait'], true)."<br /><br />Veuillez choisir une option ci-dessous ?<br /><br/></center><input type=\"hidden\" name=\"montant_retrait\" value=\"".recupMontant($mnt)."\" /><input type=\"hidden\" name=\"frais_retrait_cpt\" value=\"".recupMontant($frais_retrait_cpt)."\" /><input type=\"hidden\" name=\"type_retrait\" value=\"2\" /><input type=\"hidden\" name=\"choix_retrait\" value=\"1\" />");

        $html_msg->addCustomButton("btn_demande_autorisation_retrait", "Demande d’autorisation", 'Rex-4');
        $html_msg->addCustomButton("btn_annuler", "Annuler", 'Gen-8');

        $html_msg->buildHTML();

        echo $html_msg->HTML_code;
        die();
      }
	}
	/*if ($msg != "") {
		 $html = new HTML_erreur(_("Retrait impossible")." ");
		 $html->setMessage($msg);
		 $html->addButton(BUTTON_OK, "Rex-1");
		 $html->buildHTML();
		 echo $html->HTML_code;
		 exit();
	}*/

  $html->addField("mnt_reel",_("Confirmation du montant"),TYPC_MNT);
  $html->setFieldProperties("mnt_reel", FIELDP_IS_REQUIRED, true);

  global $global_billet_req;
  if ($global_billet_req) {
    $html->setFieldProperties("mnt_reel", FIELDP_HAS_BILLET, true);
    $html->setFieldProperties("mnt_reel", FIELDP_SENS_BIL, SENS_BIL_OUT);
  }

  $ChkJS = "\t\tif (recupMontant(document.ADForm.mnt_reel.value) != recupMontant(document.ADForm.mnt.value))";
  $ChkJS .= "{\nmsg += '- "._("Le montant saisi ne correspond pas au montant à retirer")."\\n'; ADFormValid=false;};\n";
  $html->addJS(JSP_BEGIN_CHECK, "JS5",$ChkJS);

  if ($SESSION_VARS["mnt_frais_retrait"] > 0) {
    $html->addField("frais_retrait", _("Frais de retrait pris sur le compte"), TYPC_MNT);
    $html->setFieldProperties("frais_retrait", FIELDP_DEFAULT, $SESSION_VARS["mnt_frais_retrait"]);
    $html->setFieldProperties("frais_retrait", FIELDP_IS_LABEL, true);
  }

  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "retour", _("Retour"), TYPB_SUBMIT);
  $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  $SESSION_VARS['envoi'] = 0;
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Rex-3');
  $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Rex-1');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-8');

  $html->buildHTML();
  echo $html->getHTML();

} else if ($global_nom_ecran == "Rex-3") {
	global $global_id_client, $global_id_agence, $report_backup_path;
// capturer des types de billets de la bd et nombre de billets saisie par l'utilisateur
	$valeurBilletArr = array();
	$InfoCpte = getAccountDatas($SESSION_VARS["NumCpte"]);
	$dev = ($InfoCpte["devise"]);
	$listTypesBilletArr = buildBilletsVect($dev);
	$total_billetArr = array();

  if (!isset($SESSION_VARS['ecran_prec'])) { // ticket 805 ajout if statement
    //insert nombre billet into array
    for($x = 0; $x < 20; $x++) {
      if(isset($_POST['mnt_reel_billet_'.$x]) && trim($_POST['mnt_reel_billet_'.$x])!='') {
        $valeurBilletArr[] = trim($_POST['mnt_reel_billet_'.$x]);
      }
      else{
        if(isset($listTypesBilletArr[$x]['libel']) && trim($listTypesBilletArr[$x]['libel'])!='') {
          $valeurBilletArr[] = 'XXXX';
        }
      }
    }
    $SESSION_VARS['valeurBilletArr'] = $valeurBilletArr; // ticket 805
      // calcul total pour chaque billets
    for($x = 0; $x < 20; $x ++) {

      if ($valeurBilletArr [$x] == 'XXXX') {
        $total_billetArr [] = 'XXXX';
      } else {
        if (isset ( $listTypesBilletArr [$x] ['libel'] ) && trim ( $listTypesBilletArr [$x] ['libel'] ) != '' && isset ( $valeurBilletArr [$x] ['libel'] ) && trim ( $valeurBilletArr [$x] ['libel'] ) != '') {
          $total_billetArr [] = ( int ) ($valeurBilletArr [$x]) * ( int ) ($listTypesBilletArr [$x] ['libel']);
        }
      }
    }
    $SESSION_VARS['total_billetArr'] = $total_billetArr; // ticket 805
	
    //controle d'envoie du formulaire
    $SESSION_VARS['envoi']++;
    if( $SESSION_VARS['envoi'] != 1 ) {
      $html_err = new HTML_erreur(_("Confirmation"));
        $html_err->setMessage(_("Donnée dèjà envoyée"));
        $html_err->addButton("BUTTON_OK", 'Gen-8');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
        exit();
    }
  }

  if (isset($SESSION_VARS['ecran_prec']) && $SESSION_VARS['ecran_prec'] == 'Rex-3'){ //ticket 805 information billetage gerer par sessions
    if (isset($SESSION_VARS['valeurBilletArr']) && $SESSION_VARS['valeurBilletArr'] != null){
      $valeurBilletArr = $SESSION_VARS['valeurBilletArr'];
    }
    if (isset($SESSION_VARS['total_billetArr']) && $SESSION_VARS['total_billetArr'] != null){
      $total_billetArr = $SESSION_VARS['total_billetArr'];
    }
  }


  //--------------------------- ECRAN Rcp-3 : mouvement des comptes --------------------------------------

    $isbilletage = getParamAffichageBilletage();
  //mouvement des comptes avec gestion des frais d'opérations sur compte s'il y lieu

  //récupérer le infos sur le produit associé au compte sélectionné
  $InfoCpte = getAccountDatas($SESSION_VARS["NumCpte"]);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
  if (!isset($SESSION_VARS["mnt"])){ //ajout frais de non respect de la duree minimum entre 2 retraits
    $SESSION_VARS["mnt"] = recupMontant($mnt_reel);
  }
  if ( check_access(299) )
    $InfoProduit["frais_retrait_cpt"] = $SESSION_VARS["mnt_frais_retrait"];

  $erreur = retrait_cpte($global_id_guichet,$SESSION_VARS["NumCpte"], $InfoProduit, $InfoCpte, $SESSION_VARS["mnt"], 1, $SESSION_VARS['id_mandat'],null,null,null,$SESSION_VARS['ERR_DUREE_MIN_RETRAIT']);

  if ($erreur->errCode == NO_ERR) {

    // Mettre à jour le statut d'une demande de retrait à Payé
    if (isset($SESSION_VARS['id_dem'])) {
      $erreur2 = updateRetraitAttenteEtat($SESSION_VARS['id_dem'], 3, "Demande autorisation retrait : Payé", $erreur->param['id']);

      if ($erreur2->errCode == NO_ERR) {
        // Commit
        $dbHandler->closeConnection(true);
        unset($SESSION_VARS['id_dem']);
      }
    }

    $infos = get_compte_epargne_info($SESSION_VARS['NumCpte']);
    //print_recu_retrait($global_id_client, $global_client, $InfoProduit, $infos, $SESSION_VARS['mnt'], $erreur->param['id']);

    print_recu_retrait($global_id_client, $global_client, $InfoProduit, $infos, $SESSION_VARS['mnt'], $erreur->param['id'], 'REC-REE',$SESSION_VARS['id_mandat'], $SESSION_VARS["remarque"], $SESSION_VARS["communication"], $SESSION_VARS['id_pers_ext'],NULL,$SESSION_VARS['denomination_conj'], $listTypesBilletArr, $valeurBilletArr, $global_langue_rapport, $total_billetArr, true, $isbilletage,$SESSION_VARS['ERR_DUREE_MIN_RETRAIT']);
    create_report_tag("epargne",array($report_backup_path => array("num_client" => $global_id_client,"num_transactions" => $erreur->param["id"], "type_recu" => "retrait", "id_ag" => $global_id_agence, "montant" => $SESSION_VARS["mnt"])));

    $html_msg =new HTML_message(_("Confirmation de retrait sur un compte"));
    $fraisDureeMinEntreRetrait = 0; // ticket 805
    if(isset($SESSION_VARS['ERR_DUREE_MIN_RETRAIT']) && $SESSION_VARS['ERR_DUREE_MIN_RETRAIT'] == 't' && $InfoProduit['frais_duree_min2retrait'] > 0){ // ticket 805 : ajout frais de non respect de la duree minimum entre 2 retraits
      $fraisDureeMinEntreRetrait = $InfoProduit['frais_duree_min2retrait'];
    }
    $msg = _("Le compte a été débité de")." : ".afficheMontant($SESSION_VARS["mnt"]+$InfoProduit["frais_retrait_cpt"]+$fraisDureeMinEntreRetrait)." $global_monnaie<br />"._("Recu imprimé").".";
    $msg .= "<br /><br />"._("N° de transaction")." : <B><code>".sprintf("%09d", $erreur->param['id'])."</code></B>";
    $html_msg->setMessage($msg);
    $html_msg->addButton("BUTTON_OK", 'Gen-8'); //Si express
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  } else {
    //	$dbHandler->closeConnection(false);
    if ($erreur->errCode == ERR_DUREE_MIN_RETRAIT){ // Ticket 805 : ajout frais de non respect de la duree minimum entre 2 retraits
      $SESSION_VARS['ecran_prec'] = 'Rex-3';
      $SESSION_VARS['ERR_DUREE_MIN_RETRAIT'] = 't';
      $html_err = new HTML_erreur(_("Retrait sur un compte.")." ");
      $html_err->setMessage(_("ATTENTION")." : ".$error[$erreur->errCode]."<br />"._("Paramètre Numero Compte Client : ")." : ".$erreur->param." <br /> Mais si vous voulez continuer le retrait, sachez que les frais de non respect de la durée minimum entre deux retraits seront prelevés sur le compte du client; alors veuillez cliquer sur le bouton 'OK' pour continuer sinon le bouton 'annuler'!");
      $html_err->addButton("BUTTON_CANCEL", 'Rex-1');
      $html_err->addButton("BUTTON_OK", 'Rex-3');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
    else{
      unset($SESSION_VARS['ERR_DUREE_MIN_RETRAIT']);
      unset($SESSION_VARS['ecran_prec']);
      $html_err = new HTML_erreur(_("Echec du retrait sur un compte.")." ");
      $html_err->setMessage(_("Erreur")." : ".$error[$erreur->errCode]."<br />"._("Paramètre")." : ".$erreur->param);
      $html_err->addButton("BUTTON_OK", 'Rex-1'); //Si express
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  }

} else if ($global_nom_ecran == "Rex-4") {

  global $global_nom_login, $global_id_client;

  $id_cpte = $SESSION_VARS['NumCpte'];

  /*require_once ('lib/misc/debug.php');
  print_rn($_POST);die;*/

  $erreur = insertRetraitAttente($global_id_client, $id_cpte, $type_retrait, $choix_retrait, $montant_retrait,$devise, $mnt_devise, $mnt_reste,$taux_devise,$taux_commission,$dest_reste, $global_nom_login, $communication, $remarque, $id_pers_ext, $mandat, $num_chq, $date_chq, $id_ben, $beneficiaire, $nom_ben, $denomination, $frais_retrait_cpt,$num_piece,$lieu_delivrance);

  if ($erreur->errCode == NO_ERR) {

    // Affichage de la confirmation
    $html_msg = new HTML_message("Confirmation demande autorisation retrait");

    $html_msg->setMessage("La demande d'autorisation de retrait a été envoyée.");

    $html_msg->addButton("BUTTON_OK", 'Gen-8');

    $html_msg->buildHTML();
    echo $html_msg->HTML_code;

    $data_client = getClientDatas($global_id_client);
    switch ($data_client['statut_juridique']){
      case 1 :
        $nom = $data_client["pp_nom"] . " " . $data_client["pp_prenom"];
        break;
      case 2 :
        $nom = $data_client["pm_raison_sociale"];
        break;
      case 3 :
        $nom = $data_client["gi_nom"];
        break;
      case 4:
        $nom = $data_client["gi_nom"];
        break;
    }

    $now = date("Y-m-d");
    $id_dmde_retrait = getDataRetraitAttente($global_id_client,$id_cpte,1);
    $id_dem = $id_dmde_retrait['max_id'];
    //AT-151 : affichage num compte complet au lieu de id_cpte dans le recu
    $cpte_info=get_compte_epargne_info($id_cpte);

    print_recu_demande_autorisation($global_id_client,$nom,$cpte_info['num_complet_cpte'],$montant_retrait, $now , $global_nom_login, $id_dem );

  } else {
    $html_err = new HTML_erreur("Echec lors de la demande autorisation retrait.");

    $err_msg = $error[$erreur->errCode];

    $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

    $html_err->addButton("BUTTON_OK", 'Gen-8');

    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }

} else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'écran '$global_nom_ecran' n'a pas été trouvé"
?>