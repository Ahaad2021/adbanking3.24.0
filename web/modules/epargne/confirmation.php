<?php
require_once 'lib/dbProcedures/epargne.php';
require_once 'modules/epargne/recu.php';
require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/dbProcedures/tireur_benef.php';
require_once 'lib/misc/divers.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/misc/VariablesSession.php';
require_once 'modules/rapports/xml_devise.php';
debug($_REQUEST);
global  $global_nom_ecran;
 if ($global_nom_ecran == "Dcp-5") {
  setMonnaieCourante($SESSION_VARS["set_monnaie_courante"]);

  //mouvement des comptes avec gestion des frais d'opérations sur compte s'il y lieu
  //$NumCpte et $mnt ont été postés de l'écran précédent; $mnt est le montant net à verser non compris les frais d'opération
  //Vérification si le client n'est pas "débiteur"
  // recupére les information sur le compte
  $InfoCpte = getAccountDatas($SESSION_VARS["NumCpte"]);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
  
  // remplacer les frais de dépot par la valeur saisie s'il y'a possibilité de modification de frais
  if (isset($SESSION_VARS['frais_depot_cpt']))
    $InfoProduit["frais_depot_cpt"] = $SESSION_VARS["frais_depot_cpt"];

  if ($SESSION_VARS['mnt_cv']['cv']!='')
    $CHANGE = $SESSION_VARS['mnt_cv'];
  else
    $CHANGE = NULL;

  $data['id_pers_ext'] = $SESSION_VARS['id_pers_ext'];

  if ($SESSION_VARS["type_depot"] == 1) { // dépôt au guichet
    $data['sens'] = 'in ';
    $data['communication'] = $SESSION_VARS['communication'];
    $data['remarque'] = $SESSION_VARS['remarque'];

    $type_depot=NULL;
    $erreur = depot_cpte($global_id_guichet, $SESSION_VARS["NumCpte"], $SESSION_VARS["mnt"],$InfoProduit, $InfoCpte, $data, $type_depot, $CHANGE); //mnt = montant net à déposer

    if ($erreur->errCode == NO_ERR) {

      //prélèvement des frais en attente si solde_disponible > montant_frais
      $prelevement_frais = false;
      $num_compte = $SESSION_VARS["NumCpte"]; debug($num_compte,"num cpte");
      $mnt_frais_attente = 0;
      //Y a t-il des frais en attente sur le compte ?
      if(hasFraisAttenteCompte($num_compte)){
      	$result = getFraisAttenteCompte($num_compte);
      	$liste_frais_attente = $result->param;
      	//Pour chaque frais en attente
      	foreach($liste_frais_attente as $key=>$frais_attente) {
      		//Recupération du solde disponible sur le compte
      		$solde_disponible = getSoldeDisponible($num_compte);
      		$montant_frais = $frais_attente['montant'];
      		$type_frais = $frais_attente['type_frais'];
      		$date_frais = $frais_attente['date_frais'];
      		$comptable = array();//pour passage ecritures
      		//vois si le solde disponible est suffisant pour prélever les frais
	      	if($solde_disponible >= $montant_frais){
	      		$erreurs = paieFraisAttente($num_compte, $type_frais, $montant_frais, $comptable);
		        if ($erreurs->errCode != NO_ERR){
		        	return $erreurs;
		        }
		        //Suppression dans la table des frais en attente
		        $sql = "DELETE FROM ad_frais_attente WHERE id_cpte = $num_compte AND date(date_frais) = date('$date_frais') AND type_frais = $type_frais;";
		        $result = executeDirectQuery($sql);
		        if ($result->errCode != NO_ERR){
		        	return new ErrorObj($result->errCode);
		        }
		        $prelevement_frais = true;
		        //memoriser montant des frais prélevés
	      		$mnt_frais_attente += $montant_frais;
	      		//Historiser le prelevement
		      	$myErr = ajout_historique(75, $InfoCpte["id_titulaire"],'', $global_nom_login, date("r"), $comptable);
					  if ($myErr->errCode != NO_ERR) {
					    $dbHandler->closeConnection(false);
					    return $myErr;
					  }
	      	}
      	}
      }

      $id_his = $erreur->param['id'];
      $infos = get_compte_epargne_info($SESSION_VARS['NumCpte']);

      print_recu_depot($global_id_client, $global_client, $SESSION_VARS['mnt'], $InfoProduit, $infos, $id_his, $data['id_pers_ext'],$SESSION_VARS["remarque"],$SESSION_VARS["communication"], $mnt_frais_attente, $SESSION_VARS['id_mandat']);

      $html_msg =new HTML_message(_("Confirmation de dépôt sur un compte"));
      setMonnaieCourante($InfoCpte['devise']);
      $message =_("Montant déposé sur le compte : ").afficheMontant($SESSION_VARS['mnt'], true);
      if (isset($CHANGE)) {
        // Impression du bordereau de change
        $cpteSource=getAccountDatas($SESSION_VARS['NumCpte']);


        $cpteGuichet=getCompteCptaGui($global_id_guichet);
        $cpteDevise=$cpteGuichet.".".$SESSION_VARS['mnt_cv']['devise'];

        $SESSION_VARS["mnt_cv"]["source_achat"]=$cpteSource["num_complet_cpte"];//." ".$cpteSource["intitule_compte"];
        $SESSION_VARS["mnt_cv"]["dest_vente"]= $global_guichet;
        printRecuChange($id_his, $SESSION_VARS["mnt_cv"]["cv"],$SESSION_VARS["mnt_cv"]["devise"],$SESSION_VARS["mnt_cv"]["source_achat"],$SESSION_VARS["mnt"],$global_monnaie_courante,$SESSION_VARS["mnt_cv"]["comm_nette"],$SESSION_VARS["mnt_cv"]["taux"],$SESSION_VARS["mnt_cv"]["reste"],$SESSION_VARS["mnt_cv"]["dest_vente"]);

        setMonnaieCourante($CHANGE['devise']);
        $message .="<br>"._("Montant déposé au guichet : ").afficheMontant($CHANGE['cv'], true);
      }
      if ($SESSION_VARS['frais_depot_cpt']>0) {
        setMonnaieCourante($InfoCpte['devise']);
        $message .="<br>"._("Frais de dépôt : ").afficheMontant($SESSION_VARS['frais_depot_cpt'], true);
      }

      if ($erreur->param["mnt"] > 0) {
        $message .= "<br>"._("Des frais impayés ont été débités de votre compte de base pour un montant de")." :<br>";
        $message .= afficheMontant($erreur->param["mnt"], true);
      }
      if ($prelevement_frais) {
        $message .= "<br>"._("Des frais en attente ont été débités de votre compte de base pour un montant de")." :<br>";
        $message .= afficheMontant($mnt_frais_attente, true);
      }
      $message .= "<br /><br />"._("N° de transaction")." : <B><code>".sprintf("%09d", $erreur->param['id'])."</code></B>";
      $html_msg->setMessage($message);
      $html_msg->addButton("BUTTON_OK", 'Gen-10');
      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
    } else {
      $html_err = new HTML_erreur(_("Echec du dépôt sur un compte. "));
      $html_err->setMessage("Erreur : ".$error[$erreur->errCode]);
      $html_err->addButton("BUTTON_OK", 'Dcp-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    };
  }
  //Cheque ou travelers cheque
  else if ($SESSION_VARS["type_depot"] == 2 || $SESSION_VARS['type_depot']==5) { // Dépt par chèque ou TCH
    $InfoCpte = getAccountDatas($SESSION_VARS["NumCpte"]);
    if ($SESSION_VARS["type_depot"] == 2)
      $InfoTireur = getTireurBenefDatas($SESSION_VARS['id_ben']);
    $data['id_correspondant']	= $SESSION_VARS['id_correspondant'];
    $data['id_ext_benef']	= null;
    $data['id_cpt_benef']	= $SESSION_VARS['NumCpte'];
    $data['id_ext_ordre']	= $SESSION_VARS['id_ben'];
    $data['id_cpt_ordre']	= null;
    $data['sens']		= 'in ';
    $data['type_piece']		= $SESSION_VARS['type_depot'];
    $data['num_piece']		= $SESSION_VARS['num_chq'];
    $data['date_piece']		= $SESSION_VARS['date_chq'];
    if ($SESSION_VARS["type_depot"] == 5)
      $data['date_piece'] = date("d/m/Y");
    $data['date']		= date("d/m/Y");
    $data['etat']		= 1;                           //état = en attente
    if ($SESSION_VARS["type_depot"] == 2)
      $data['id_banque'] = $InfoTireur['id_banque'];
    $data['communication']	= $SESSION_VARS['communication'];
    $data['remarque']		= $SESSION_VARS['remarque'];

    if (isset($CHANGE)) {
      $data['montant']		= $CHANGE['cv']; // Montant du chèque / déposé au guichet
      $data['devise']           = $CHANGE['devise'];
    } else {
      $data['montant']		= $SESSION_VARS['mnt'];
      $data['devise']           = $InfoCpte["devise"];
    }

    if ($_POST['trait']==2) {
      $creditDirectSaufBonneFin = true;
      $commissionSurCreditDirect = recupMontant($_POST['comm_credit']);
    } else {
      $creditDirectSaufBonneFin = false;
      $commissionSurCreditDirect = null;
    }

    $erreur = receptionCheque($data, $InfoCpte, $InfoProduit, $SESSION_VARS["mnt"], $creditDirectSaufBonneFin, $commissionSurCreditDirect, $CHANGE);
    if ($erreur->errCode == NO_ERR) {

      $html_msg =new HTML_message(_("Confirmation de dépôt d'un chèque sur un compte"));
      $message = "";
      if (isset($SESSION_VARS['mnt_cv']['cv'])) {
        setMonnaieCourante($SESSION_VARS['mnt_cv']['devise']);
        $message .= _("Montant du chèque : ").afficheMontant($SESSION_VARS['mnt_cv']['cv'], true)."<br/>";
      }
      setMonnaieCourante($InfoCpte['devise']);
      $message .= _("Montant à déposer sur le compte : ").afficheMontant($SESSION_VARS["mnt"], true);
      if ($creditDirectSaufBonneFin) {
        $message .= "<BR/>"._("Frais de Crédit direct sauf bonne fin : ").afficheMontant($commissionSurCreditDirect, true)."</br>";
      }
      if ($SESSION_VARS['frais_depot_cpt']>0) {
        setMonnaieCourante($InfoCpte['devise']);
        $message .= "<BR/>"._("Frais de dépôt : ").afficheMontant($SESSION_VARS['frais_depot_cpt'], true)."</br>";
      }
      $message .= "<BR><BR>N° de transaction : <B><code>".sprintf("%09d", $erreur->param)."</code></B>";
      $html_msg->setMessage($message);
      $html_msg->addButton("BUTTON_OK", 'Gen-10');
      $html_msg->buildHTML();
      echo $html_msg->HTML_code;

      // Impression du bordereau de change
      if (isset($CHANGE) && $creditDirectSaufBonneFin) {
        printRecuChange($erreur->param, $SESSION_VARS["mnt_cv"]['cv'], $SESSION_VARS["mnt_cv"]['devise'], "Traveler's Cheque", $SESSION_VARS["mnt"], $InfoCpte["devise"], $CHANGE["comm_nette"],$CHANGE["taux"],$CHANGE["reste"],_("Compte ").$InfoCpte["num_complet_cpte"], $CHANGE["dest_reste"],true);
      }

    } else if ($erreur->errCode == ERR_CPT_CENTRALISE) {
      $html_err = new HTML_erreur(_("Echec du dépôt d'un chèque sur un compte."));
      $html_err->setMessage(_("Erreur : Les comptes comptables des correspondants bancaires ne peuvent être des comptes centralisateurs. Merci de reconfigurer le correspondant bancaire utilisé lors de cette opération."));
      $html_err->addButton("BUTTON_OK", 'Dcp-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    } else {
      $html_err = new HTML_erreur(_("Echec du dépôt d'un chèque sur un compte."));
      $html_err->setMessage(_("Erreur")." : ".$error[$erreur->errCode]."\n".$erreur->param);
      $html_err->addButton("BUTTON_OK", 'Dcp-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  }
  //virement
  else if ($SESSION_VARS["type_depot"] == 3) {
    $InfoCpte = getAccountDatas($SESSION_VARS["NumCpte"]);
    $InfoTireur = getTireurBenefDatas($SESSION_VARS['id_ben']);
    $data['id_correspondant']	= $SESSION_VARS['id_correspondant'];
    $data['id_ext_benef']	= null;
    $data['id_cpt_benef']	= $SESSION_VARS['NumCpte'];
    $data['id_ext_ordre']	= $SESSION_VARS['id_ben'];
    $data['id_cpt_ordre']	= null;
    $data['sens']		= 'in ';
    $data['type_piece']		= $SESSION_VARS['type_depot'];
    $data['num_piece']		= $SESSION_VARS['num_chq'];
    $data['date_piece']		= $SESSION_VARS['date_chq'];
    $data['date']		= date("d/m/Y");
    $data['montant']		= $SESSION_VARS['mnt'];
    $data['devise']		= $InfoCpte['devise'];
    $data['id_banque']		= $InfoTireur['id_banque'];
    $data['remarque']		= $SESSION_VARS['remarque'];
    $data['communication']	= $SESSION_VARS['communication'];
    $erreur = receptionVirement($data, $InfoCpte, $InfoProduit, $CHANGE);

    if ($erreur->errCode == NO_ERR) {
      // A vérifier mais je pense que dans ce cas pas besoin d'imprimer un reçu.
      // La pièce justificative est l'OP lui-meme
      //  print_recu_depot_cheque($global_id_client, $global_client, $SESSION_VARS['NumCpte'], $SESSION_VARS['mnt_chq'], $erreur->param['id'], $DATA["num"], $DATA["id_bqe"], $DATA["date"]);


      $html_msg =new HTML_message(_("Confirmation de dépôt d'un virement sur un compte"));
      setMonnaieCourante($InfoCpte['devise']);
      $message = "<br>"._("Montant à déposer sur le compte : ").afficheMontant($SESSION_VARS["mnt"], true);
      if (isset($SESSION_VARS['mnt_cv']['cv'])) {
        setMonnaieCourante($SESSION_VARS['mnt_cv']['devise']);
        $message .= "<br>"._("Montant du virement : ").afficheMontant($SESSION_VARS['mnt_cv']['cv'], true);
      }
      if ($SESSION_VARS['frais_depot_cpt']>0) {
        setMonnaieCourante($InfoCpte['devise']);
        $message .="<br />"._("Frais de dépôt")." : ".afficheMontant($SESSION_VARS['frais_depot_cpt'], true);
      }
      $message .= "<br /><br />"._("N° de transaction")." : <B><code>".sprintf("%09d", $erreur->param["id"])."</code></B>";
      $html_msg->setMessage($message);
      $html_msg->addButton("BUTTON_OK", 'Gen-10');
      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
    } else {
      $html_err = new HTML_erreur(_("Echec du dépôt d'un chèque sur un compte.")." ");
      $html_err->setMessage("Erreur : ".$error[$erreur->errCode]);
      $html_err->addButton("BUTTON_OK", 'Dcp-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  }


  // On vérifie si le client n'est plus débiteur
  if (!isClientDebiteur($global_id_client))

    $global_client_debiteur = false;

}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
