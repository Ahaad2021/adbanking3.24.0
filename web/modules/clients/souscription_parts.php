<?php

/* Ecran de souscription de parts sociales
    TF - 14/01/2002 
    MAJ 02/2015_361          */

require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/compte.php');
require_once('lib/misc/tableSys.php');
require_once('lib/dbProcedures/historique.php');
require_once('lib/dbProcedures/agence.php');
require_once('lib/html/HTML_GEN2.php');
require_once('lib/html/FILL_HTML_GEN2.php');
require_once('modules/epargne/recu.php');


if ($global_nom_ecran == "Sps-1") {
  global $global_id_client;
  global $global_id_agence;
  global $global_nom_utilisateur;

	$AGC = getAgenceDatas($global_id_agence);
	$id_cpt = getBaseAccountID($global_id_client);
	$CPT = getAccountDatas($id_cpt);
	//Etat du client
	$etat=getEtatClient($global_id_client);
	
  if ($etat == 1) {
    $erreur = new HTML_erreur(_("Souscription de parts sociales non-autorisée"));
    $erreur->setMessage(_("Ce client doit d'abord acquitter les frais d'adhésion avant de pouvoir souscrire des parts sociales."));
    $erreur->addButton(BUTTON_OK,"Gen-9");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
  } else {
    $nbre_part = getNbrePartSoc($global_id_client);
    $nbrePS = $nbre_part->param[0]['nbre_parts'];
    $soldePartSoc = getSoldePartSoc($global_id_client);
    $soldePartSocRestant = $soldePartSoc->param[0]['solde_part_soc_restant'];
    $nbre_part_lib = getNbrePartSocLib($global_id_client);
    $nbrePSlib = $nbre_part_lib->param[0]['nbre_parts_lib'];
    
    //control_souscription au niveau  d'agence
    $souscription_ouvert = checkSouscription();
  
    $solde = $CPT['solde'];
    if ($AGC["tranche_part_sociale"] == "t") {
      $Title = _("Souscription parts sociales ");
      $myForm = new HTML_GEN2($Title);
      
	    //Valeur nominale PS
	  $myForm->addField("valeur_nominale_ps", _("Valeur nominale part sociale"), TYPC_MNT);
	  $myForm->setFieldProperties("valeur_nominale_ps", FIELDP_DEFAULT,$AGC["val_nominale_part_sociale"]);
	  $myForm->setFieldProperties("valeur_nominale_ps", FIELDP_IS_LABEL, true);
      //Nbre de PS souscrites
      $myForm->addField("ps_souscrites", _("Nombre de parts souscrites"), TYPC_INT);
      $myForm->setFieldProperties("ps_souscrites", FIELDP_DEFAULT,$nbrePS);
      $myForm->setFieldProperties("ps_souscrites", FIELDP_IS_LABEL, true);
      
      //Nbre de PS liberer
      $myForm->addField("ps_lib", _("Nombre de parts libérées"), TYPC_INT);
      $myForm->setFieldProperties("ps_lib", FIELDP_DEFAULT,$nbrePSlib);
      $myForm->setFieldProperties("ps_lib", FIELDP_IS_LABEL, true);
      
      //Nouvelles PS
      $myForm->addField("new_nbr_parts", _("Nombre de parts à souscrire"), TYPC_INT);
      $myForm->setFieldProperties("new_nbr_parts", FIELDP_IS_REQUIRED, true);
      $myForm->setFieldProperties ("new_nbr_parts", FIELDP_JS_EVENT, array (
      		"onChange" => "checkzero();"
      ) );
      
	
	$codeJS = "function checkzero(){ ";
	$codeJS .= "if(document.ADForm.new_nbr_parts.value == '0'){";
	$codeJS .= "alert('Le nombre de parts saisie est invalide!');";
	$codeJS .= "document.ADForm.new_nbr_parts.value=\"\"";
	$codeJS .= "}";
	$codeJS .= "}";
	
      $myForm->addJS ( JSP_FORM, "js", $codeJS );
    } else {
    	$Title = _("Souscription parts sociales")." ";
      $myForm = new HTML_GEN2($Title);

      $nbre_part_lib = getNbrePartSocLib($global_id_client);
      $nbrePSlib = $nbre_part_lib->param[0]['nbre_parts_lib'];
      
	    //Valeur nominale PS
	    $myForm->addField("valeur_nominale_ps", _("Valeur nominale part sociale"), TYPC_MNT);
	    $myForm->setFieldProperties("valeur_nominale_ps", FIELDP_DEFAULT,$AGC["val_nominale_part_sociale"]);
	    $myForm->setFieldProperties("valeur_nominale_ps", FIELDP_IS_LABEL, true);
	    //Nbre de PS souscrites
      $myForm->addField("ps_souscrites", _("Nombre de parts souscrites"), TYPC_INT);
      $myForm->setFieldProperties("ps_souscrites", FIELDP_DEFAULT,$nbrePS);
      $myForm->setFieldProperties("ps_souscrites", FIELDP_IS_LABEL, true);
      
      //Nbre de PS liberer
      $myForm->addField("ps_lib", _("Nombre de parts libérées"), TYPC_INT);
      $myForm->setFieldProperties("ps_lib", FIELDP_DEFAULT,$nbrePSlib);
      $myForm->setFieldProperties("ps_lib", FIELDP_IS_LABEL, true);
      
	    //Nouvelles PS
      $myForm->addField("new_nbr_parts", _("Nombre de parts à souscrire"), TYPC_INT);
      $myForm->setFieldProperties("new_nbr_parts", FIELDP_IS_REQUIRED, true);
      $myForm->setFieldProperties ("new_nbr_parts", FIELDP_JS_EVENT, array (
      		"onChange" => "checkzero();"
      ) );
      
	
	$codeJS = "function checkzero(){ ";
	$codeJS .= "if(document.ADForm.new_nbr_parts.value == '0'){";
	$codeJS .= "alert('Le nombre de parts saisie est invalide!');";
	$codeJS .= "document.ADForm.new_nbr_parts.value=\"\"";
	$codeJS .= "}";
	$codeJS .= "}";
	
      $myForm->addJS ( JSP_FORM, "js", $codeJS );
       
    }
    
    // verifier le nbre de part sociale max souscripte autorisé pour un client
 	  $nbre_part_param = getNbrePartSoc($global_id_client);
	  $nbre_part = $nbre_part_param->param[0]['nbre_parts'];
	  $nbre_part_max = $AGC['nbre_part_social_max_cli'];
	  $souscrites_agence = $AGC['nbre_part_sociale'];
	  
	  //Les controle JS pour souscription autorisé
	  //blocage de souscription
	if (($souscription_ouvert == x)){ //false
	  	$souscription_ouvert = 0;
	  	$ExtraJS .= "\n\t  if(parseFloat(document.ADForm.new_nbr_parts.value) >  " . $souscription_ouvert ." )" ;
	  	$ExtraJS .= "\n\t { ADFormValid = false; msg+='" . sprintf ( _ ( " %s !\\n Souscription non-autorisé  %s" ), $error [ERR_MAX_PS_SOUSCRIT_AGC],null) . "';\n\t}";  		
	  }
	  //souscription limité
	  if (($souscription_ouvert > 0) ){
			$ExtraJS .= "\n\t  if(parseFloat(document.ADForm.new_nbr_parts.value) >  " . $souscription_ouvert ." )" ;
			$ExtraJS .= "\n\t { ADFormValid = false; msg+='" . sprintf ( _ ( " %s !\\n PS Restant à souscrire dans l\'agence : %s" ), $error [ERR_MAX_PS_SOUSCRIT_AGC], $souscription_ouvert ) . "';\n\t}";	
		} 
	// souscription illimité  && control nombre max par client
	 if ($nbre_part_max > 0) {
			$ExtraJS .= "\n\t if( " . $nbre_part_max . " < (parseFloat(document.ADForm.new_nbr_parts.value) +  parseFloat(document.ADForm.ps_souscrites.value) )) ";
			$ExtraJS .= "\n\t { ADFormValid = false; msg+='" . sprintf ( _ ( "\\n\\n %s !\\n Nombre max de parts sociales : %s" ), $error [ERR_NBRE_MAX_PS], $nbre_part_max ) . "';\n\t}";
		}
  
   $myForm->addJS(JSP_BEGIN_CHECK, "extrajs", $ExtraJS);

    //Boutons
    $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Sps-2');
    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Mgp-1');
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    $myForm->buildHTML();
    echo $myForm->getHTML();
    $SESSION_VARS["id_cpte_base"] = $id_cpt;
  }
}
else if ($global_nom_ecran == "Sps-2") {
	$AGC = getAgenceDatas($global_id_agence);
	$SESSION_VARS["tranche_part_sociale"] = $AGC["tranche_part_sociale"];
	$SESSION_VARS["val_nominale_part_sociale"] = $AGC["val_nominale_part_sociale"];
	$id_cpt = getBaseAccountID($global_id_client);
	$CPT = getAccountDatas($id_cpt);
	$solde = $CPT['solde'];
	$SESSION_VARS["solde"] = $solde;
  if (!isset($new_nbr_parts) || $new_nbr_parts == "") {
    $new_nbr_parts = 0;
  }
  $SESSION_VARS["new_nbr_parts"] = $new_nbr_parts;

  $nbre_part = getNbrePartSoc($global_id_client);
  $nbrePS = $nbre_part->param[0]['nbre_parts'];
  $SESSION_VARS["ps_souscrites"] = $nbrePS;
  $soldePartSoc = getSoldePartSoc($global_id_client);

  $soldePartSocRestant = $soldePartSoc->param[0]['solde_part_soc_restant'];
  $SESSION_VARS["solde_restant"] = $soldePartSocRestant;
  $SESSION_VARS["somme_paye"] = $soldePartSoc->param[0]['solde'];
  $versement = recupMontant($versement);
  $SESSION_VARS["versement"] = $versement;
	if ($SESSION_VARS["tranche_part_sociale"] == "t") {

      $Title = _("Confirmation de la souscription ");
      $myForm = new HTML_GEN2($Title);
     
	  //Valeur nominale PS
	  $myForm->addField("valeur_nominale_ps", _("Valeur nominale part sociale"), TYPC_MNT);
	  $myForm->setFieldProperties("valeur_nominale_ps", FIELDP_DEFAULT,$SESSION_VARS["val_nominale_part_sociale"]);
	  $myForm->setFieldProperties("valeur_nominale_ps", FIELDP_IS_LABEL, true);
      //Nbre de PS souscrites
      $myForm->addField("ps_souscrites", _("Nombre de parts souscrites"), TYPC_INT);
      $myForm->setFieldProperties("ps_souscrites", FIELDP_DEFAULT,$nbrePS);
      $myForm->setFieldProperties("ps_souscrites", FIELDP_IS_LABEL, true);
      //Nouvelles PS
      $myForm->addField("new_nbr_parts", _("Nombre de parts à souscrire"), TYPC_INT);
	  $myForm->setFieldProperties("new_nbr_parts", FIELDP_DEFAULT, $new_nbr_parts);
	  $myForm->setFieldProperties("new_nbr_parts", FIELDP_IS_LABEL, true);
      
	}else{
    	$Title = _("Souscription parts sociales")." ";
      $myForm = new HTML_GEN2($Title);

	    //Valeur nominale PS
	  $myForm->addField("valeur_nominale_ps", _("Valeur nominale part sociale"), TYPC_MNT);
	  $myForm->setFieldProperties("valeur_nominale_ps", FIELDP_DEFAULT,$SESSION_VARS["val_nominale_part_sociale"]);
	  $myForm->setFieldProperties("valeur_nominale_ps", FIELDP_IS_LABEL, true);
	    //Nbre de PS souscrites
      $myForm->addField("ps_souscrites", _("Nombre de parts souscrites"), TYPC_INT);
      $myForm->setFieldProperties("ps_souscrites", FIELDP_DEFAULT,$nbrePS);
      $myForm->setFieldProperties("ps_souscrites", FIELDP_IS_LABEL, true);
	    //Nouvelles PS
      $myForm->addField("new_nbr_parts", _("Nombre de parts à souscrire"), TYPC_INT);
	  $myForm->setFieldProperties("new_nbr_parts", FIELDP_DEFAULT, $new_nbr_parts);
	  $myForm->setFieldProperties("new_nbr_parts", FIELDP_IS_LABEL, true);
	}
	 //Boutons
    $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Sps-3');
    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Mgp-1');
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    $myForm->buildHTML();
    echo $myForm->getHTML();
}
else if ($global_nom_ecran == "Sps-3") {//Confirmation souscription part sociale
	
  $new_nbr_parts = $SESSION_VARS["new_nbr_parts"];
  
  //Souscription
  if($SESSION_VARS["tranche_part_sociale"] == "t"){
  	  $new_solde = $new_nbr_parts * $SESSION_VARS["val_nominale_part_sociale"];
  	  $versement = $SESSION_VARS["versement"];
	  //Recupération du solde restant des ps
	  $soldePartSocRestant = $SESSION_VARS['solde_restant'] + $new_solde;
	  $nbrePS = $SESSION_VARS['ps_souscrites'];
	  $nbre_total_ps = $nbrePS + $new_nbr_parts;

  	$err =  souscriptionPartsSocialesInt($global_id_client, $new_nbr_parts, $global_id_utilisateur);
  	if ($err->errCode == NO_ERR) {
  		$err_update = updateSodeRestantPartSoc($global_id_client,  $soldePartSocRestant);
  		if ($err_update->errCode != NO_ERR) {
  			$html_err = new HTML_erreur(_("Echec de la mise à jour du solde restant."));
  			$html_err->setMessage(_("Erreur")." : ".$error[$err_update->errCode]."<br/>"._("Paramètre")." : ".$err_update->param);
  			$html_err->addButton("BUTTON_OK", 'Gen-3');
  			$html_err->buildHTML();
  			echo $html_err->HTML_code;
  		}
  		else{//historique ps
  			$id_his = $err->param;
  			$err_h =historique_mouvementPs($global_id_client, $id_his,20);
  			if ($err_h->errCode != NO_ERR){
  				return $err_h;
  			}
  		}
  	}
  	
  }else {// tranche ps ==false
  	
  	$nbre_part = getNbrePartSoc($global_id_client);
  	$nbrePS = $nbre_part->param[0]['nbre_parts'];
  	$SESSION_VARS["ps_souscrites"] = $nbrePS;
  	$soldePartSoc = getSoldePartSoc($global_id_client);
  	
  	$soldePartSocRestant = $soldePartSoc->param[0]['solde_part_soc_restant'];
  	$SESSION_VARS["solde_restant"] = $soldePartSocRestant;
  	
  	//calcule new solde restant
  	$new_valeur_souscription = $new_nbr_parts * $SESSION_VARS["val_nominale_part_sociale"];
  	$new_solde_restant = $SESSION_VARS["solde_restant"] + $new_valeur_souscription ;

  	$err =  souscriptionPartsSocialesInt($global_id_client, $new_nbr_parts, $global_id_utilisateur);
  	//historisation pour tranche
  	if ($err->errCode == NO_ERR) {
  	$err_update = updateSodeRestantPartSoc($global_id_client, $new_solde_restant);
  		if ($err_update->errCode != NO_ERR) {
  			$html_err = new HTML_erreur(_("Echec de la mise à jour du solde restant."));
  			$html_err->setMessage(_("Erreur")." : ".$error[$err_update->errCode]."<br/>"._("Paramètre")." : ".$err_update->param);
  			$html_err->addButton("BUTTON_OK", 'Gen-3');
  			$html_err->buildHTML();
  			echo $html_err->HTML_code;
  		}
  		else{//historique ps
  			$id_his = $err->param;
  			$err_h =historique_mouvementPs($global_id_client, $id_his,20);
  			if ($err_h->errCode != NO_ERR){
  				return $err_h;
  			}
  		}
  	}
  } 

  if ($err->errCode == ERR_SPS_ETAT_EAV) {
    $myMsg = new HTML_message(_("Erreur"));
    $myMsg->setMessage(_("Ce client ne peut souscrire de parts sociales car il n'a pas acquitté les frais d'adhésion"));
    $myMsg->addButton(BUTTON_OK, 'Gen-9');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  } else if ($err->errCode == NO_ERR) {
    if ($SESSION_VARS["tranche_part_sociale"] == "f") {
    	$recu = 1;
      print_recu_sps($global_id_client, $new_nbr_parts, $err->param, $soldePartSocRestant, $recu );
    } else {
    	$recu = 1;
      print_recu_sps($global_id_client, $new_nbr_parts, $err->param, $versement, $recu);
    }
    $html_msg = new HTML_message(_("Confirmation de la souscription"));
    if ($SESSION_VARS["tranche_part_sociale"] == "t") {
    	$message =_("Nombre de PS souscrites : ").$new_nbr_parts;
    } else {
    	$message =_("Nombre de PS souscrites : ").$new_nbr_parts;
    }
    $message .= "<br/><br/>"._("N° de transaction")." : <b><code>".sprintf("%09d", $err->param)."</code></b>";
    $html_msg->setMessage($message);
    $html_msg->addButton("BUTTON_OK", 'Mgp-1');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  } else {
  	$param=$err->param;
    $html_err = new HTML_erreur(_("Souscription de parts sociales."));
    $msg=_("Echec")." : ".$error[$err->errCode]."<br/>";
    if ($param != NULL) {
    	if(is_array($param)){
    		foreach($param as $key => $val){
    			$msg .= "<br /> ".$key." : ".$param["$key"]."";
    		}
    	} else {
    		$msg .= $param;
    	}
    }

    $html_err->setMessage( $msg);
    $html_err->addButton("BUTTON_OK", 'Mgp-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
}
else
  signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran non trouvé : " . $global_nom_ecran
?>