<?php

/* Ecran de Liberation PS
    TF - 14/01/2002 
    - 2015_361_ ex- souscription ps          */

require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/compte.php');
require_once('lib/misc/tableSys.php');
require_once('lib/dbProcedures/historique.php');
require_once('lib/dbProcedures/agence.php');
require_once('lib/html/HTML_GEN2.php');
require_once('lib/html/FILL_HTML_GEN2.php');
require_once('modules/epargne/recu.php');


if ($global_nom_ecran == "Lps-1") {
  global $global_id_client;
  global $global_id_agence;
  global $global_nom_utilisateur;
  
	$AGC = getAgenceDatas($global_id_agence);
	$id_cpt = getBaseAccountID($global_id_client);
	$CPT = getAccountDatas($id_cpt);
	//Etat du client
	$etat = $global_etat_client;
  if ($etat == 1) {
    $erreur = new HTML_erreur(_("Libération de parts sociales non-autorisée"));
    $erreur->setMessage(_("Ce client doit d'abord acquitter les frais d'adhésion avant de pouvoir souscrire des parts sociales."));
    $erreur->addButton(BUTTON_OK,"Gen-9");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
  } else {
    $nbre_part = getNbrePartSoc($global_id_client);
    $nbrePS = $nbre_part->param[0]['nbre_parts'];
    $soldePartSoc = getSoldePartSoc($global_id_client);
    
    $nbre_part_lib = getNbrePartSocLib($global_id_client);
    $nbrePSlib = $nbre_part_lib->param[0]['nbre_parts_lib'];

    $soldePartSocRestant = $soldePartSoc->param[0]['solde_part_soc_restant'];

      //getSoldeDisponible($id_cpt)
    //$solde = $CPT['solde'];
      $solde = getSoldeDisponible($id_cpt);
    if ($AGC["tranche_part_sociale"] == "t") {
      $Title = _("Libération part sociale ");
      $myForm = new HTML_GEN2($Title);
      //solde cpte de base
	    $myForm->addField("solde", _("Solde compte de base"), TYPC_MNT);
	    $myForm->setFieldProperties("solde", FIELDP_DEFAULT, $solde);
	    $myForm->setFieldProperties("solde", FIELDP_IS_LABEL, true);
	    //Valeur nominale PS
	    $myForm->addField("valeur_nominale_ps", _("Valeur nominale part sociale"), TYPC_MNT);
	    $myForm->setFieldProperties("valeur_nominale_ps", FIELDP_DEFAULT,$AGC["val_nominale_part_sociale"]);
	    $myForm->setFieldProperties("valeur_nominale_ps", FIELDP_IS_LABEL, true);
      //Nbre de PS souscrites
      $myForm->addField("ps_souscrites", _("Nombre de parts souscrites"), TYPC_INT);
      $myForm->setFieldProperties("ps_souscrites", FIELDP_DEFAULT,$nbrePS);
      $myForm->setFieldProperties("ps_souscrites", FIELDP_IS_LABEL, true);
      
      //Nbre de PS libérer
      $myForm->addField("ps_liberer", _("Nombre de parts libérées"), TYPC_INT);
      $myForm->setFieldProperties("ps_liberer", FIELDP_DEFAULT, $nbrePSlib);
      $myForm->setFieldProperties("ps_liberer", FIELDP_IS_LABEL, true);
      
      //Montant déjà payé
      $myForm->addField("somme_paye", _("Montant déjà versé"), TYPC_MNT);
      $myForm->setFieldProperties("somme_paye", FIELDP_IS_LABEL, true);
      $myForm->setFieldProperties("somme_paye", FIELDP_DEFAULT,$soldePartSoc->param[0]['solde']);
      
      //Restant à payer
      $myForm->addField("solde_restant", _("Reste à payer"), TYPC_MNT);
      $myForm->setFieldProperties("solde_restant", FIELDP_IS_LABEL, true);
      $myForm->setFieldProperties("solde_restant", FIELDP_DEFAULT,$soldePartSocRestant);
      //Nouvelles PS
      $myForm->addField("new_nbr_parts", _("Nombre de parts à libérer"), TYPC_INT);
      $myForm->setFieldProperties ( "new_nbr_parts", FIELDP_IS_LABEL, true );
      $myForm->addHiddenType ( "hid_new_nbr_parts", "" );
      
      $SoldeMAx = ( $nbrePS * $AGC["val_nominale_part_sociale"])-($soldePartSoc->param[0]['solde']) ;
      
      $myForm->addHiddenType ( "hid_solde_max", $SoldeMAx );
      
      
      $codejs3 = "
				function setValues()
	{
      		
      var soldePS = recupMontant(document.ADForm.somme_paye.value) ;
      var solde_max = recupMontant(document.ADForm.hid_solde_max.value) ;		
      var versement = recupMontant(document.ADForm.versement.value);		
	  var mnt_versement = recupMontant(document.ADForm.confirmation_versement.value);
	  var valeur_nominale_ps = recupMontant(document.ADForm.valeur_nominale_ps.value);
      var ps_actuellement_lib = parseInt(document.ADForm.ps_liberer.value);
      		
      var ps_souscrites =parseInt(document.ADForm.ps_souscrites.value);
      var solde_restant = recupMontant(document.ADForm.solde_restant.value);
	  var nbre_ps_a_liberer = parseInt(Math.floor((mnt_versement - solde_restant)/ valeur_nominale_ps));
	  var new_nbre_ps_liberer = (ps_actuellement_lib + nbre_ps_a_liberer) ;
	  var nbre_part_liberable = (ps_souscrites	- ps_actuellement_lib) ;
  	




      		if((mnt_versement==0)&&(versement==0)){
      		alert('Les montants de versement ne sont pas correctes. '  );
      		document.ADForm.new_nbr_parts.value = '';
      		document.ADForm.hid_new_nbr_parts.value = '';
      		document.ADForm.confirmation_versement.value = '';
      		document.ADForm.versement.value = '';
      		}


      		if((mnt_versement!= versement)||(versement!= mnt_versement)){
      		alert('Les montants ne correspondent pas. '  );
  
      		document.ADForm.new_nbr_parts.value = '';
      		document.ADForm.hid_new_nbr_parts.value = '';
      		document.ADForm.confirmation_versement.value = '';
      		document.ADForm.versement.value = '';
      		}
      		
      	
		if  ( (nbre_ps_a_liberer > nbre_part_liberable) ){
			alert('Nombre max de part sociale libérable est ' + nbre_part_liberable );
      		
      		document.ADForm.new_nbr_parts.value = '';
      		document.ADForm.hid_new_nbr_parts.value = '';
      		document.ADForm.confirmation_versement.value = '';
      		document.ADForm.versement.value = '';
			}
      	else if(nbre_ps_a_liberer < nbre_part_liberable){
      		document.ADForm.new_nbr_parts.value = 0;
      		document.ADForm.hid_new_nbr_parts.value = 0;
      		
      		}
      		
      	else if((mnt_versement!= versement)||(versement!= mnt_versement)){
      		alert('Les montants ne correspondent pas. '  );
  
      		document.ADForm.new_nbr_parts.value = '';
      		document.ADForm.hid_new_nbr_parts.value = '';
      		document.ADForm.confirmation_versement.value = '';
      		document.ADForm.versement.value = '';
      		}
       
		else {
		    document.ADForm.new_nbr_parts.value = nbre_ps_a_liberer;
      		document.ADForm.hid_new_nbr_parts.value = nbre_ps_a_liberer;
		}


      	if(	ps_actuellement_lib == 0){
      		if (soldePS < valeur_nominale_ps){
      		var update_liber = parseInt(Math.floor((mnt_versement + soldePS)/ valeur_nominale_ps));
      		document.ADForm.new_nbr_parts.value = update_liber;
      		document.ADForm.hid_new_nbr_parts.value = update_liber;
      		}
      		}
      		else if(ps_actuellement_lib >0){
      		if ( soldePS >= valeur_nominale_ps ){
            var update_liber = parseInt(Math.floor((mnt_versement + (soldePS-(ps_actuellement_lib * valeur_nominale_ps)))/ valeur_nominale_ps));
      		document.ADForm.new_nbr_parts.value = update_liber;
      		document.ADForm.hid_new_nbr_parts.value = update_liber;
           }
      		
      		}
      		
      	if((mnt_versement > solde_max )&&(versement > solde_max )){
      		alert('Le solde de versement ne peut pas depasser le solde max de part sociales ' + solde_max );
      		
      		document.ADForm.new_nbr_parts.value = '';
      		document.ADForm.hid_new_nbr_parts.value = '';
      		document.ADForm.confirmation_versement.value = '';
      		document.ADForm.versement.value = '';
      		}		
      		
	}";
 
      if( $nbre_part_max > 0) {
      	$ExtraJS .= "\n\t  if( ".$nbre_part_max * $AGC["val_nominale_part_sociale"]." < (parseFloat(document.ADForm.new_nbr_parts.value) +  parseFloat(document.ADForm.ps_liberer.value) )) ";
      	$ExtraJS .= "\n\t { ADFormValid = false; msg+='".sprintf(_(" %s !\\n nombre max de parts sociales : %s"),$error[ERR_NBRE_MAX_PS],$nbre_part_max)."';\n\t}";
      }
 
      $myForm->addJS ( JSP_FORM, "JS3", $codejs3 );

      //versement et confirmation
	    $myForm->addField("versement", _("Montant versement"), TYPC_MNT);
	    $myForm->setFieldProperties("versement", FIELDP_IS_REQUIRED, true);
	    $myForm->addField("confirmation_versement", _("Confirmation du montant"), TYPC_MNT);
		$myForm->setFieldProperties("confirmation_versement", FIELDP_IS_REQUIRED, true);
		$myForm->setFieldProperties ( "confirmation_versement", FIELDP_JS_EVENT, array (
				"onChange" => "setValues();"));
		  
		  $myForm->addJS(JSP_BEGIN_CHECK, "js1", "if (recupMontant(document.ADForm.versement.value) > recupMontant(document.ADForm.solde.value)) { msg += '- "._("Le solde du compte de base est insuffisant.")."\\n'; ADFormValid = false;}");
		  $myForm->addJS(JSP_BEGIN_CHECK, "js2", "if (recupMontant(document.ADForm.versement.value) != recupMontant(document.ADForm.confirmation_versement.value)) { msg += '- "._("Les montants ne correspondent pas.")."\\n'; ADFormValid = false;}");
  
    } else {
    	$Title = _("Libération part sociale ")." ";
      $myForm = new HTML_GEN2($Title);

      //solde cpte de base
	    $myForm->addField("solde", _("Solde compte de base"), TYPC_MNT);
	    $myForm->setFieldProperties("solde", FIELDP_DEFAULT, $solde);
	    $myForm->setFieldProperties("solde", FIELDP_IS_LABEL, true);
	    //Valeur nominale PS
	    $myForm->addField("valeur_nominale_ps", _("Valeur nominale part sociale"), TYPC_MNT);
	    $myForm->setFieldProperties("valeur_nominale_ps", FIELDP_DEFAULT,$AGC["val_nominale_part_sociale"]);
	    $myForm->setFieldProperties("valeur_nominale_ps", FIELDP_IS_LABEL, true);
	    //Nbre de PS souscrites
      $myForm->addField("ps_souscrites", _("Nombre de parts souscrites"), TYPC_INT);
      $myForm->setFieldProperties("ps_souscrites", FIELDP_DEFAULT,$nbrePS);
      $myForm->setFieldProperties("ps_souscrites", FIELDP_IS_LABEL, true);
      
      //Nbre de PS libérer
      $myForm->addField("ps_liberer", _("Nombre de parts libérées"), TYPC_INT);
      $myForm->setFieldProperties("ps_liberer", FIELDP_DEFAULT,$nbrePSlib);
      $myForm->setFieldProperties("ps_liberer", FIELDP_IS_LABEL, true);
      
	  //Nouvelles PS
      $myForm->addField("new_nbr_parts", _("Nombre de parts à libérer"), TYPC_INT);
      $myForm->setFieldProperties("new_nbr_parts", FIELDP_IS_REQUIRED, true);

    }

    //Contrôle sur le solde disponible du client.

      if ($AGC["tranche_part_sociale"] == "f") {
          $ExtraJS .= "\n\t if (" . getSoldeDisponible($id_cpt) . " < (document.ADForm.new_nbr_parts.value) * " . $AGC["val_nominale_part_sociale"] . ") \n\t {ADFormValid = false; msg += ' -" . sprintf(_(" Le Solde du compte de base est insuffisant")) . " !\\n'; \n\t}";
      }
      else{//true
          $ExtraJS .= "\n\t  if (" . getSoldeDisponible($id_cpt) . " < (parseFloat(document.ADForm.versement.value))  ) \n\t {ADFormValid = false; msg += ' -" . sprintf(_(" Le Solde du compte de base est insuffisant")) . " !\\n'; \n\t}";

     }

    // verifier le nbre de part sociale max souscripte autorisé pour un client
 	  $nbre_part_param = getNbrePartSoc($global_id_client);
	  $nbre_part = $nbre_part_param->param[0]['nbre_parts'];
	  $nbre_part_max = $AGC['nbre_part_social_max_cli'];

	  if( $nbrePS > 0) {
	  	$ExtraJS .= "\n\t  if( ".$nbrePS." < (parseFloat(document.ADForm.new_nbr_parts.value) +  parseFloat(document.ADForm.ps_liberer.value) )) ";
	    $ExtraJS .= "\n\t { ADFormValid = false; msg+='".sprintf(_(" %s !\\n nombre max de PS souscrites : %s"),$error[ERR_NBRE_MAX_PS_LIBERER],$nbrePS)."';\n\t}";
	  }
    $myForm->addJS(JSP_BEGIN_CHECK, "extrajs", $ExtraJS);
    //Boutons
    $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Lps-2');
    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Mgp-1');
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    $myForm->buildHTML();
    echo $myForm->getHTML();
    $SESSION_VARS["id_cpte_base"] = $id_cpt;
  }
}
else if ($global_nom_ecran == "Lps-2") {
	$AGC = getAgenceDatas($global_id_agence);
	$SESSION_VARS["tranche_part_sociale"] = $AGC["tranche_part_sociale"];
	$SESSION_VARS["val_nominale_part_sociale"] = $AGC["val_nominale_part_sociale"];
	$id_cpt = getBaseAccountID($global_id_client);
	$CPT = getAccountDatas($id_cpt);
	//$solde = $CPT['solde'];
    $solde = getSoldeDisponible($id_cpt);
	$SESSION_VARS["solde"] = $solde;
	//get nombre part sociale liberer
	$nbre_part_lib = getNbrePartSocLib($global_id_client);
	$nbrePSlib = $nbre_part_lib->param[0]['nbre_parts_lib'];
	
	
  if (!isset($hid_new_nbr_parts) || $hid_new_nbr_parts == "") {
    $hid_new_nbr_parts = 0;
  }
  $SESSION_VARS["new_nbr_parts"] = $hid_new_nbr_parts;

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

      $Title = _("Libération parts sociales ");
      $myForm = new HTML_GEN2($Title);
      
      //solde cpte de base
	    $myForm->addField("solde", _("Solde compte de base"), TYPC_MNT);
	    $myForm->setFieldProperties("solde", FIELDP_DEFAULT, $solde);
	    $myForm->setFieldProperties("solde", FIELDP_IS_LABEL, true);
	    
	    //Valeur nominale PS
	    $myForm->addField("valeur_nominale_ps", _("Valeur nominale part sociale"), TYPC_MNT);
	    $myForm->setFieldProperties("valeur_nominale_ps", FIELDP_DEFAULT,$SESSION_VARS["val_nominale_part_sociale"]);
	    $myForm->setFieldProperties("valeur_nominale_ps", FIELDP_IS_LABEL, true);
      
      //Nbre de PS souscrites
      $myForm->addField("ps_souscrites", _("Nombre de parts souscrites"), TYPC_INT);
      $myForm->setFieldProperties("ps_souscrites", FIELDP_DEFAULT,$nbrePS);
      $myForm->setFieldProperties("ps_souscrites", FIELDP_IS_LABEL, true);

      //Nbre de PS libérer
      $myForm->addField("ps_liberer", _("Nombre de parts libérer"), TYPC_INT);
      $myForm->setFieldProperties("ps_liberer", FIELDP_DEFAULT,$nbrePSlib);
      $myForm->setFieldProperties("ps_liberer", FIELDP_IS_LABEL, true);
      
      //Montant déjà payé
      $myForm->addField("somme_paye", _("Montant déjà versé"), TYPC_MNT);
      $myForm->setFieldProperties("somme_paye", FIELDP_IS_LABEL, true);
      $myForm->setFieldProperties("somme_paye", FIELDP_DEFAULT,$SESSION_VARS["somme_paye"]);
      
      //Restant à payer
      $myForm->addField("solde_restant", _("Reste à payer"), TYPC_MNT);
      $myForm->setFieldProperties("solde_restant", FIELDP_IS_LABEL, true);
      $myForm->setFieldProperties("solde_restant", FIELDP_DEFAULT,$soldePartSocRestant);
      
      //Nouvelles PS
      $myForm->addField("new_nbr_parts", _("Nombre de parts à libérer"), TYPC_INT);
	    $myForm->setFieldProperties("new_nbr_parts", FIELDP_DEFAULT, $hid_new_nbr_parts);
	    $myForm->setFieldProperties("new_nbr_parts", FIELDP_IS_LABEL, true);
      
      //versement et confirmation
	    $myForm->addField("versement", _("Montant versement"), TYPC_MNT);
	    $myForm->setFieldProperties("versement", FIELDP_IS_LABEL, true);
	    $myForm->setFieldProperties("versement", FIELDP_DEFAULT, $versement);
	}else{
    	$Title = _("Libération parts sociales")." ";
      $myForm = new HTML_GEN2($Title);
      //solde cpte de base
	    $myForm->addField("solde", _("Solde compte de base"), TYPC_MNT);
	    $myForm->setFieldProperties("solde", FIELDP_DEFAULT, $solde);
	    $myForm->setFieldProperties("solde", FIELDP_IS_LABEL, true);
	  //Valeur nominale PS
	    $myForm->addField("valeur_nominale_ps", _("Valeur nominale part sociale"), TYPC_MNT);
	    $myForm->setFieldProperties("valeur_nominale_ps", FIELDP_DEFAULT,$SESSION_VARS["val_nominale_part_sociale"]);
	    $myForm->setFieldProperties("valeur_nominale_ps", FIELDP_IS_LABEL, true);
	  //Nbre de PS souscrites
      $myForm->addField("ps_souscrites", _("Nombre de parts souscrites"), TYPC_INT);
      $myForm->setFieldProperties("ps_souscrites", FIELDP_DEFAULT,$nbrePS);
      $myForm->setFieldProperties("ps_souscrites", FIELDP_IS_LABEL, true);
      //Nbre de PS libérer
      $myForm->addField("ps_liberer", _("Nombre de parts libérer"), TYPC_INT);
      $myForm->setFieldProperties("ps_liberer", FIELDP_DEFAULT,$nbrePSlib);
      $myForm->setFieldProperties("ps_liberer", FIELDP_IS_LABEL, true);
	  //Nouvelles PS
      $myForm->addField("new_nbr_parts", _("Nombre de parts à libérer"), TYPC_INT);
	    $myForm->setFieldProperties("new_nbr_parts", FIELDP_DEFAULT, $new_nbr_parts);
	    $myForm->setFieldProperties("new_nbr_parts", FIELDP_IS_LABEL, true);
	    
	    $myForm->addHiddenType("hid_new_nbr_parts", $new_nbr_parts );
	}
	 //Boutons
    $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Lps-3');
    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Mgp-1');
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    $myForm->buildHTML();
    echo $myForm->getHTML();
}
else if ($global_nom_ecran == "Lps-3") {//Confirmation Liberation
	$new_part_tranche_f = $hid_new_nbr_parts;
	
  $new_nbr_parts = $SESSION_VARS["new_nbr_parts"];
		// Souscription
	if ($SESSION_VARS ["tranche_part_sociale"] == "t") {
		
		$versement = $SESSION_VARS ["versement"];
		// get to update part liberer
		$nbre_part_lib = getNbrePartSocLib ( $global_id_client );
		$nbrePSlib = $nbre_part_lib->param [0] ['nbre_parts_lib'];
		$nbrefinalPSlib = $nbrePSlib + $new_nbr_parts;
		
		// Recupération du solde restant des ps
		$nbrePS = $SESSION_VARS ['ps_souscrites'];
		$nbre_total_ps = $nbrePS + $new_nbr_parts;
		
		// Mise à jour du solde restant et control
		$capital_souscrit = $nbrePS * $SESSION_VARS ["val_nominale_part_sociale"];
		$new_capital_liberer = ($SESSION_VARS ["somme_paye"] + $versement);
		$soldeRestantAPayer = $capital_souscrit - $new_capital_liberer;
		
		$err = liberationPartsSocialesInt ( $global_id_client, $new_nbr_parts, $global_id_utilisateur, $versement );

	//MAJ solde restant
  if ($err->errCode == NO_ERR) {
	  $err_update = updateSodeRestantPartSoc($global_id_client,  $soldeRestantAPayer);
	  if ($err_update->errCode != NO_ERR) {
	    $html_err = new HTML_erreur(_("Echec de la mise à jour du solde restant."));
	    $html_err->setMessage(_("Erreur")." : ".$error[$err_update->errCode]."<br/>"._("Paramètre")." : ".$err_update->param);
	    $html_err->addButton("BUTTON_OK", 'Gen-3');
	    $html_err->buildHTML();
	    echo $html_err->HTML_code;
	  }
	  else{//historique ps
	  	$id_his = $err ->param;
	  	$err_h =historique_mouvementPs( $global_id_client, $id_his,28);
	  	if ($err_h->errCode != NO_ERR){
	  		return $err_h;	
	  	}
	  }
	}
  } else {	
  	
  	 $valeur_liberation = $new_part_tranche_f * $SESSION_VARS["val_nominale_part_sociale"];
  
  	// get to update part liberer
  	$nbre_part_lib = getNbrePartSocLib ( $global_id_client );
  	$nbrePSlib = $nbre_part_lib->param [0] ['nbre_parts_lib'];
  	$nbrefinalPSlib = $nbrePSlib + $new_part_tranche_f;
  	
  	// Recupération du solde restant des ps
  	$nbrePS = $SESSION_VARS ['ps_souscrites'];
  	$nbre_total_ps = $nbrePS + $new_part_tranche_f;
  	
  	// Mise à jour du solde restant et control
  	$capital_souscrit = $nbrePS * $SESSION_VARS ["val_nominale_part_sociale"];//souscription
  	$new_capital_liberer = ($SESSION_VARS ["somme_paye"] + $valeur_liberation);
  	$soldeRestantAPayer = $capital_souscrit - $new_capital_liberer;
  	
  	$err =  liberationPartsSocialesInt($global_id_client, $new_part_tranche_f, $global_id_utilisateur);
  	
  //MAJ solde restant
  if ($err->errCode == NO_ERR) {
	  $err_update = updateSodeRestantPartSoc($global_id_client,  $soldeRestantAPayer);
	  if ($err_update->errCode != NO_ERR) {
	    $html_err = new HTML_erreur(_("Echec de la mise à jour du solde restant."));
	    $html_err->setMessage(_("Erreur")." : ".$error[$err_update->errCode]."<br/>"._("Paramètre")." : ".$err_update->param);
	    $html_err->addButton("BUTTON_OK", 'Gen-3');
	    $html_err->buildHTML();
	    echo $html_err->HTML_code;
	  }
	  else{//historique ps
	  	$id_his = $err ->param;
	  	$err_h =historique_mouvementPs( $global_id_client, $id_his,28);
	  	if ($err_h->errCode != NO_ERR){
	  		return $err_h;	
	  	}
	  }
	}	
  }

  if ($err->errCode == ERR_SPS_ETAT_EAV) {
    $myMsg = new HTML_message(_("Erreur"));
    $myMsg->setMessage(_("Ce client ne peut pas libérer de parts sociales car il n'a pas acquitté les frais d'adhésion"));
    $myMsg->addButton(BUTTON_OK, 'Mgp-1');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  } else if ($err->errCode == NO_ERR) {
    if ($SESSION_VARS["tranche_part_sociale"] == "f") {
    	$recu = 2;
      print_recu_sps($global_id_client, $new_part_tranche_f, $err->param, $new_part_tranche_f * $SESSION_VARS["val_nominale_part_sociale"], $recu);
    } else {
    	$recu = 2;
      print_recu_sps($global_id_client, $new_nbr_parts, $err->param, $versement, $recu);
    }
    $html_msg = new HTML_message(_("Confirmation de la Libération PS"));
    if ($SESSION_VARS["tranche_part_sociale"] == "t") {
    	$message =_("Montant de la libération PS : ").afficheMontant($versement, true);
    } else {
    	$message =_("Montant de la libération PS: ").afficheMontant($new_part_tranche_f * $SESSION_VARS["val_nominale_part_sociale"], true);
    }
    $message .= "<br/><br/>"._("N° de transaction")." : <b><code>".sprintf("%09d", $err->param)."</code></b>";
    $html_msg->setMessage($message);
    $html_msg->addButton("BUTTON_OK", 'Mgp-1');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  } else {
  	$param=$err->param;
    $html_err = new HTML_erreur(_("Libération de parts sociales."));
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