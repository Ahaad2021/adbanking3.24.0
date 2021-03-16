<?php

/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [432] Calcul provision crédits en souffrance
 * Ces fonctions appellent les écrans suivants :
 * - Pcs-1 : Gestion de provision des crédit
 * - Pcs-2 : Provisionner Les Dossiers de crédits
 * - Pcs-3 : Confirmation calcul provision
 * - Pcs-4 : critère de Selection  des dossiers de crédits à modifier
 * - Pcs-5 :Modification des provisions des crédits en souffrances
 * - Pcs-6 : Modification des provisions des crédits en souffrances
 * - Pcs-7 : Confirmation Modification des provisions des crédits en souffrances
 * - Pcs-8 : Liste des dossiers crédits à provisionner
  * @package compta
 * @since 10/02/09
 **/

require_once 'lib/dbProcedures/compta.php';
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/html/HTML_erreur.php';

global $global_multidevise, $global_niveau_max;
$gestion = array("prov_provisionner" => _("Provisionner Les Dossiers de crédits"));
 if (check_access(433)) $gestion[ "prov_modifier"]= _("Modifier Le dossier de crédit");

/*{{{ Pcs-1 :Gestion de provision des crédit */
if ($global_nom_ecran == "Pcs-1") {

	 $MyPage = new HTML_GEN2(_("Gestion des provisions des crédits en souffrances"));
	 $MyPage->addField("gestion", _("Action"), TYPC_LSB);

  $MyPage->setFieldProperties("gestion", FIELDP_ADD_CHOICES, $gestion);
  $MyPage->setFieldProperties("gestion", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("gestion", FIELDP_HAS_CHOICE_AUCUN, true);

  $MyPage->addButton("gestion", "valider", _("Valider"), TYPB_SUBMIT);
  $MyPage->setButtonProperties("valider", BUTP_JS_EVENT, array("onclick"=>"setProchainEcran();"));

  //Bouton formulaire
  $MyPage->addFormButton(1,1, "butret", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Gen-14");

  //Javascript
  $js  = "function setProchainEcran(){\n";
  $js .= "if (document.ADForm.HTML_GEN_LSB_gestion.value == 'prov_provisionner') {assign('Pcs-2');}\n";
  $js .= "else if (document.ADForm.HTML_GEN_LSB_gestion.value == 'prov_modifier') {assign('Pcs-4');}\n";
  $js .= "else {assign('Pcs-1');}\n";
  $js .= "}\n";
  $MyPage->addJS(JSP_FORM, "js1", $js);

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();

}
/*}}}*/

/*{{{ Pcs-2 :Provisionner Les Dossiers de crédits */
if ($global_nom_ecran == "Pcs-2") {

  $allowed_dates = getAllowedDatesForBackdateProvision();
  $allowed_date_deb = $allowed_dates['allowed_date_deb'];
  $allowed_date_fin = $allowed_dates['allowed_date_fin'];
  $last_date_prov = $allowed_dates['last_date_prov'];

  $today = date("d/m/Y");
  $hier = hier($today);

  $default_date_prov = $hier;

  if($today == $last_date_prov) {
    $default_date_prov = $today;
  }

  if($last_date_prov == '') {
    $default_date_prov = $today;
  }

  $MyPage = new HTML_GEN2(_("Provisionner Les Dossiers de crédits"));

  $MyPage->addField("date_prov",_("Date provision"), TYPC_DTE);
  $MyPage->setFieldProperties("date_prov", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("date_prov", FIELDP_DEFAULT, $default_date_prov);

  $MyPage->addField("date_dernier_prov",_("Date dernier provision"), TYPC_DTE);
  $MyPage->setFieldProperties("date_dernier_prov", FIELDP_IS_REQUIRED, false);
  $MyPage->setFieldProperties("date_dernier_prov", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("date_dernier_prov", FIELDP_DEFAULT, $last_date_prov);

  $MyPage->addField("type", _("Type de produits"), TYPC_LSB);
  $choix = array (
    "1" => _("Produit découvert"),
    "2" => _("Produit non découvert"),
    "3" => _("Produit reéchélonné")
  );
  $MyPage->setFieldProperties("type", FIELDP_ADD_CHOICES, $choix);
  $MyPage->setFieldProperties("type", FIELDP_HAS_CHOICE_AUCUN, false);
  $MyPage->setFieldProperties("type", FIELDP_HAS_CHOICE_TOUS, true);

  $JS = "";
  $JS .= "\t\tif(isBefore(document.ADForm.HTML_GEN_date_date_prov.value, '$allowed_date_deb')) { \n";
  $JS .= "\t\talert('"._("La date valeur doit être postérieure ou égale au $allowed_date_deb")."'); ADFormValid=false;\n";
  $JS .= "\t\t}";

  $JS .= "\t\tif(isAfter(document.ADForm.HTML_GEN_date_date_prov.value, '$allowed_date_fin')) { \n";
  $JS .= "\t\talert('"._("La date valeur doit être antérieure ou égale au $allowed_date_fin")."'); ADFormValid=false;\n";
  $JS .= "\t\t}";

  $MyPage->addJS(JSP_BEGIN_CHECK,"check_date",$JS);

  $MyPage->addFormButton(1,1, "valider", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Pcs-3");
  $MyPage->addFormButton(1,2, "modifier", _("Modifier Les crédits"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("modifier", BUTP_PROCHAIN_ECRAN, "Pcs-8");
  $MyPage->addFormButton(1,3, "annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-14");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Pcs-3 : Confirmation calcul provision*/
else
  if ($global_nom_ecran == 'Pcs-3') {

  	if ($global_nom_ecran_prec== 'Pcs-8') {

  		$Data = array();

    	foreach($SESSION_VARS["dossiers_prov"] as $id_doss=>$val_doss) {
    		if($ {'prov_is_calcul'.$id_doss} =='on' OR $ {'prov_is_calcul'.$id_doss} == true ) {
                $Data[$id_doss]['id_doss']=$SESSION_VARS["dossiers_prov"][$id_doss]['id_doss'];
    			$Data[$id_doss]["id_prod"]=$SESSION_VARS["dossiers_prov"][$id_doss]["id_prod"];
    			$Data[$id_doss]['prov_mnt_new']=recupMontant( ${'prov_mnt'.$id_doss});
    			$Data[$id_doss]['prov_mnt']=$SESSION_VARS["dossiers_prov"][$id_doss]['prov_mnt'];           
    			$Data[$id_doss]['cre_etat']=$SESSION_VARS["dossiers_prov"][$id_doss]['id_etat_credit'];
    			$Data[$id_doss]['devise']=$SESSION_VARS["dossiers_prov"][$id_doss]['devise'];
    			$Data[$id_doss]['id_client']=$SESSION_VARS["dossiers_prov"][$id_doss]['id_client'];
          $Data[$id_doss]['taux_prov']=$SESSION_VARS["dossiers_prov"][$id_doss]['taux'];

                $dotation = true;
                if (recupMontant( ${'prov_mnt'.$id_doss}) < 0 ){
                $dotation = false;
                }
                $Data[$id_doss]['dotation']=$dotation;

    			//$Data[$id_doss]=$SESSION_VARS["dossiers_prov"][$id_doss]
    			//$Data[$id_doss]=$SESSION_VARS["dossiers_prov"][$id_doss]
    			//debug($ {'prov_is_calcul'.$id_doss});
    		}
    	}

    	unset ($SESSION_VARS["dossiers_prov"]);
        if(!empty($Data)){
          $myErr = provisionCredit($Data, $SESSION_VARS['date_prov']);
        }
        elseif ($etat_credits == NULL) {
          $erreur = new HTML_erreur(_("Dossiers inexistants"));
          $erreur->setMessage(_("Il n'y a aucun dossier à provisionner"));
          $erreur->addButton("BUTTON_OK","Gen-14");
          $erreur->buildHTML();
          echo $erreur->HTML_code;
          exit();
        }

    } else {
        if (!empty($_POST["type"])){
          $type_produit=$_POST["type"];
        }
        else {
          $type_produit = null;
        }
        $etat_credits = getDossiersProvisionData($type_produit,null, null, null, $date_prov, null, true);

        if ($etat_credits == NULL) {
          $erreur = new HTML_erreur(_("Dossiers inexistants"));
          $erreur->setMessage(_("Il n'y a aucun dossier à provisionner"));
          $erreur->addButton("BUTTON_OK","Gen-14");
          $erreur->buildHTML();
          echo $erreur->HTML_code;
          exit();
        }
        else {
          $myErr = provisionCredit(null, $date_prov,$type_produit );
        }
    }

  	if ($myErr->errCode != NO_ERR) {
  		$html_err = new HTML_erreur(_("Echec lors du calcul des provisions des crédits en souffrances. "));
	    $html_err->setMessage("Erreur : ".$error[$myErr->errCode].$myErr->param);
	    $html_err->addButton("BUTTON_OK", 'Pcs-1');
	    $html_err->buildHTML();
	    echo $html_err->HTML_code;
	    exit();
  	} else {
	  	$myMsg = new HTML_message(_("Calcul provisions en souffrances terminé"));
		  $msg = _("Le calcul des provisions des crédits en souffrance  s'est terminé avec succès");
		  $msg .="<BR><BR>". _(sprintf("Provision                : %d dossiers de crédits traités ",$myErr->param["nbre_prov"]));
		  $msg .="<BR>". _(sprintf("Reprise sur la provision : %d dossiers crédits traités",$myErr->param["nbre_prov_reprise"]));
		  $myMsg->setMessage($msg);

		  $myMsg->addButton(BUTTON_OK, 'Gen-3');
		  $myMsg->buildHTML();
		  echo $myMsg->HTML_code;
  	}
  }
/*}}}*/
/*{{{ Pcs-4 : critère de Selection  des dossiers de crédits à modifier */
else
  if ($global_nom_ecran == 'Pcs-4') {

  	 $myForm = new HTML_GEN2(_("Choix des dossiers de crédit à modifier"));
  	//Champs 'Critère de selection'
  	$myForm->addField("critere", _("Critère de selection"), TYPC_LSB);
  	/*$choix = array (  "client" => _("Client"),
                       "liste_credition" => _("Liste des dossiers de crédits ")
                     );*/

    $choix = array (  "client" => _("Client"));

  $myForm->setFieldProperties("critere", FIELDP_ADD_CHOICES, $choix);
  $myForm->setFieldProperties("critere", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("critere", FIELDP_DEFAULT, $choix['client']);

  $js_fct = "function set_disabled(num_client,etat_credit){";
  $js_fct .= "  document.ADForm.num_client.disabled = num_client; ";
  $js_fct .= "  document.ADForm.HTML_GEN_LSB_etat_credit.disabled = etat_credit; ";
  $js_fct .= "}";

  $js_fct .= "function check_choix() {";
  $js_fct .= " if (document.ADForm.HTML_GEN_LSB_critere.value == 'client') {";
  $js_fct .= "   document.ADForm.HTML_GEN_LSB_etat_credit.value=0;";
  $js_fct .= "   set_disabled(false,true); ";
  $js_fct .= "   document.ADForm.num_client.required = true;";
  $js_fct .= " }";
  $js_fct .= " else { " ;
  $js_fct .= "   document.ADForm.num_client.value='';";
  $js_fct .= "   set_disabled(true,false);" ;
  $js_fct .= " } ";
  $js_fct .= "} ";
  /* Contrôle des champs à renseigner selon le critere selectionné  */
  $JS_valide ="";
  $JS_valide .="\n\tif(document.ADForm.HTML_GEN_LSB_critere.value == 'client' && document.ADForm.num_client.value =='')";
  $JS_valide .="\n\t{msg+='"._("Le numéro du client doit être renseigné")."'; ADFormValid = false;}";

  $JS_valide .= "\n\tif( document.ADForm.HTML_GEN_LSB_critere.value=='liste_credition' &&  document.ADForm.HTML_GEN_LSB_etat_credit.value == 0) ";
  $JS_valide .="\n\t{msg+='"._("Etat de crédit  doit être choisi ")."'; ADFormValid = false;}";
  //$Myform->addJS(JSP_BEGIN_CHECK, "js_fct", $js_fct);
  $myForm->addJS(JSP_FORM, "JS", $js_fct);
  $myForm->addJS(JSP_BEGIN_CHECK , "valJS", $JS_valide);

  $js_chercheClient = " if (document.ADForm.HTML_GEN_LSB_critere.value == 'client')
              {
                OpenBrw('$SERVER_NAME/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client', '"._("Recherche")."');return false;
              } else {document.ADForm.num_client.value=''};";

  $myForm->setFieldProperties("critere", FIELDP_JS_EVENT, array ("onchange" => "check_choix()"));

  $myForm->addField("num_client", _("N° de client"), TYPC_INT);
  $myForm->setFieldProperties("num_client", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("num_client", FIELDP_IS_LABEL, true);
  $myForm->addLink("num_client", "rechercher", _("Rechercher"), "#");
  $myForm->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => $js_chercheClient));

  $myForm->addField("etat_credit", _("Etat crédit"), TYPC_LSB);
  $myForm->setFieldProperties("etat_credit", FIELDP_ADD_CHOICES, getEtatCreditprovision());
  //$myForm->setFieldProperties("etat_credit", FIELDP_IS_REQUIRED, true);

  //Boutons
  $myForm->addFormButton(1,1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Pcs-5");
  $myForm->addFormButton(1,2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Pcs-1");
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $myForm->buildHTML();
  echo $myForm->getHTML();

  }
/*}}}*/
/*{{{ Pcs-5 :Modification des provisions des crédits en souffrances*/
else
  if ($global_nom_ecran == 'Pcs-5') {

    if ($critere == 'client') {
      // Récupération des infos du client
      $num_client = ${'num_client'};
      if (is_null($num_client)) {
        $myForm = new HTML_erreur(_("Erreur de client"));
        $myForm->setMessage(_("Erreur") . " : " . _("vous devez choisir un client pour cette option"));
        $myForm->addButton(BUTTON_OK, "Pcs-4");
        $myForm->buildHTML();
        echo $myForm->HTML_code;
        die();
      }

      $SESSION_VARS['infos_client'] = getClientDatas($num_client);
      if ($SESSION_VARS['infos_client'] == NULL) {
        //Si le client n'existe pas
        $erreur = new HTML_erreur(_("Client inexistant"));
        $erreur->setMessage(_("Le numéro de client entré ne correspond à aucun client valide"));
        $erreur->addButton(BUTTON_OK, "Gen-3");
        $erreur->buildHTML();
        echo $erreur->HTML_code;
        exit();

      }
      unset($SESSION_VARS['infos_doss']);
      //en fonction du choix du numéro de dossier, afficher les infos avec le onChange javascript
      $codejs = "\n\nfunction getInfoDossier() {";

      $dossiers = array(); // tableau contenant les infos sur dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
      $liste = array(); // Liste des dossiers à afficher
      $i = 1;

      // Récupération des dossiers individuels dans ad_dcr en attente de décision ou en attente de Rééch/Moratoire
      $whereCl = " AND ( (etat=7) OR (etat=5) OR (etat=13) OR (etat=14) OR (etat=15) )";
      $dossiers_reels = getIdDossier($num_client, $whereCl);
      if (is_array($dossiers_reels))
        foreach ($dossiers_reels as $id_doss => $value)
          if ($value['gs_cat'] != 2) { // les dossiers pris en groupe
            $date = pg2phpDate($value["date_dem"]); //Fonction renvoie  des dates au format jj/mm/aaaa
            $liste[$i] = "n° $id_doss du $date"; //Construit la liste en affichant N° dossier + date
            $dossiers[$i] = $value;

            $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
            $codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $value["libelle"] . "\";";
            $codejs .= "}\n";
            $i++;
          }

      // SI GS, récupérer les dossiers des membres dans le cas de dossiers multiples
      if ($SESSION_VARS['infos_client']['statut_juridique'] == 4) {
        // Récupération des dossiers fictifs du groupe avec dossiers multiples : cas 2
        $whereCl = " WHERE id_membre= $num_client and gs_cat=2";
        $dossiers_fictifs = getCreditFictif($whereCl);

        // Pour chaque dossier fictif du GS, récupération des dossiers réels des membres du GS
        $dossiers_membre = getDossiersMultiplesGS($num_client);

        foreach ($dossiers_fictifs as $id => $value) {
          // Récupération, pour chaque dossier fictif, des dossiers réels associés : CS avec dossiers multiples
          $infos = '';
          foreach ($dossiers_membre as $id_doss => $val)
            if (($val['id_dcr_grp_sol'] == $id) AND ($val['etat'] == 9 OR $val['etat'] == 5)) {
              //  $date_dem = $date = pg2phpDate($val['date_dem']);
              $infos .= "n° $id_doss "; // on affiche les numéros de dossiers réels sur une ligne
            }
          if ($infos != '') { // Si au moins on 1 dossier
            $infos .= "du $date_dem";
            $liste[$i] = $infos;
            $dossiers[$i] = $value; // on garde les infos du dossier fictif

            $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
            $codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $val["libelle"] . "\";";
            $codejs .= "}\n";
            $i++;
          }
        }
      }

      $SESSION_VARS['dossiers'] = $dossiers;
      $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value =='0') {";
      $codejs .= "\n\t\tdocument.ADForm.id_prod.value='';";
      $codejs .= "\n\t}\n";
      $codejs .= "}\ngetInfoDossier();";

      $Myform = new HTML_GEN2(_("Sélection d'un dossier de crédit"));
      $Myform->addField("id_doss", _("Dossier de crédit"), TYPC_LSB);
      $Myform->addField("id_prod", _("Type produit de crédit"), TYPC_TXT);

      $Myform->setFieldProperties("id_prod", FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("id_prod", FIELDP_IS_REQUIRED, false);
      $Myform->setFieldProperties("id_prod", FIELDP_WIDTH, 30);

      $Myform->setFieldProperties("id_doss", FIELDP_ADD_CHOICES, $liste);
      $Myform->setFieldProperties("id_doss", FIELDP_JS_EVENT, array("onChange" => "getInfoDossier();"));
      $Myform->addJS(JSP_FORM, "JS3", $codejs);

      // Javascript : vérifie qu'un dossier est sélectionné
      $JS_1 = "";
      $JS_1 .= "\t\tif(document.ADForm.HTML_GEN_LSB_id_doss.options[document.ADForm.HTML_GEN_LSB_id_doss.selectedIndex].value==0){ msg+=' - " . _("Aucun dossier sélectionné") . " .\\n';ADFormValid=false;}\n";
      $Myform->addJS(JSP_BEGIN_CHECK, "testdos", $JS_1);

      // bornes des dates
      $allowed_dates = getAllowedDatesForBackdateProvision();
      $allowed_date_deb = $allowed_dates['allowed_date_deb'];
      $allowed_date_fin = $allowed_dates['allowed_date_fin'];
      $last_date_prov = $allowed_dates['last_date_prov'];

      $today = date("d/m/Y");
      $hier = hier($today);
      $default_date_prov = $hier;

      if($today == $last_date_prov) {
        $default_date_prov = $today;
      }

      if($last_date_prov == '') {
        $default_date_prov = $today;
      }

      $Myform->addField("date_prov",_("Date provision"), TYPC_DTE);
      $Myform->setFieldProperties("date_prov", FIELDP_IS_REQUIRED, true);
      $Myform->setFieldProperties("date_prov", FIELDP_DEFAULT, $default_date_prov);

      $Myform->addField("date_dernier_prov",_("Date dernier provision"), TYPC_DTE);
      $Myform->setFieldProperties("date_dernier_prov", FIELDP_IS_REQUIRED, false);
      $Myform->setFieldProperties("date_dernier_prov", FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("date_dernier_prov", FIELDP_DEFAULT, $last_date_prov);

      // Control JS
      $JS = "";
      $JS .= "\t\tif(isBefore(document.ADForm.HTML_GEN_date_date_prov.value, '$allowed_date_deb')) { \n";
      $JS .= "\t\talert('"._("La date valeur doit être postérieure ou égale au $allowed_date_deb")."'); ADFormValid=false;\n";
      $JS .= "\t\t}";

      $JS .= "\t\tif(isAfter(document.ADForm.HTML_GEN_date_date_prov.value, '$allowed_date_fin')) { \n";
      $JS .= "\t\talert('"._("La date valeur doit être antérieure ou égale au $allowed_date_fin")."'); ADFormValid=false;\n";
      $JS .= "\t\t}";

      $Myform->addJS(JSP_BEGIN_CHECK,"check_date",$JS);


      // Ordre d'affichage des champs
      $order = array("id_doss", "id_prod", "date_prov", "date_dernier_prov");

      // les boutons ajoutés
      $Myform->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
      $Myform->addFormButton(1, 2, "annuler", _("Retour Menu"), TYPB_SUBMIT);

      // Propriétés des boutons
      $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Pcs-1");
      $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Pcs-6");
      $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

      $Myform->setOrder(NULL, $order);
      $Myform->buildHTML();
      echo $Myform->getHTML();

    }

/*    else {

      // liste 
      $etat_credits = getListCreditsSouffrancesProv(null,null,$etat_credit);

      $SESSION_VARS["infos_doss"] = $etat_credits;

      if ($etat_credits == NULL) {
        $erreur = new HTML_erreur(_("Dossiers inexistants"));
        $erreur->setMessage(_("Il n'y a aucun dossier à provisionner"));
        $erreur->addButton("BUTTON_OK", "Gen-14");
        $erreur->buildHTML();
        echo $erreur->HTML_code;
        $ok = false;
      } else {
        $EtatCredits = getTousEtatCredit();
        $myForm = new HTML_GEN2(_("Modification de la provision des crédits en souffrances"));
        $myTable =& $myForm->addHTMLTable("dossiers_prov", 9, TABLE_STYLE_ALTERN);
        $myTable->add_cell(new TABLE_cell(_("Id dossier"), 1, 1));
        $myTable->add_cell(new TABLE_cell(_("Id client"), 1, 1));
        //$myTable->add_cell(new TABLE_cell(_("ID gpe"), 1, 1));
        $myTable->add_cell(new TABLE_cell(_("Capital Restant Dû "), 1, 1));
        $myTable->add_cell(new TABLE_cell(_("Garantie Total"), 1, 1));
        $myTable->add_cell(new TABLE_cell(_("taux"), 1, 1));

        //#549 : add new coluums
        $myTable->add_cell(new TABLE_cell(_("Provisions exigées"), 1, 1));
        $myTable->add_cell(new TABLE_cell(_("Provisions antérieures"), 1, 1));

        $myTable->add_cell(new TABLE_cell(_("Montant provisionné"), 1, 1));
        $myTable->add_cell(new TABLE_cell(_("provisionné ?"), 1, 1));

        while (list(, $credit) = each($etat_credits)) {
          $id_doss = $credit['id_doss'];
          $myTable->add_cell(new TABLE_cell($credit['id_doss'], 1, 1));
          $myTable->add_cell(new TABLE_cell($credit['id_client'], 1, 1));
          //$myTable->add_cell(new TABLE_cell($credit['nom'], 1, 1));
          $myTable->add_cell(new TABLE_cell(afficheMontant(getSoldeCapital($id_doss)), 1, 1));
          $myTable->add_cell(new TABLE_cell(afficheMontant(getSoldeGarNumeraires($id_doss)), 1, 1));
          $myTable->add_cell(new TABLE_cell($EtatCredits[$credit["cre_etat"]]["taux"], 1, 1));

          // //Provisions exigées
          $provisions_required = afficheMontant( $credit['provisions_required']);
          $myTable->add_cell(new TABLE_cell($provisions_required, 1, 1));

          //Provisions anterieures
          $previous_provisions = afficheMontant( $credit['previous_provisions']);
          $myTable->add_cell(new TABLE_cell($previous_provisions, 1, 1));

          // additional provisions / mnt provisions
          $additional_provisions = afficheMontant( $credit['additional_provisions']);

          $prov_mnt_new = $additional_provisions;
          $prov_is_calcul = "";

          if ($credit['prov_is_calcul'] == 't') {
            $SESSION_VARS["infos_doss"][$id_doss]['prov_is_calcul'] = true;
            $prov_is_calcul = "checked";
          } else {
            $SESSION_VARS["infos_doss"][$id_doss]['prov_is_calcul'] = false;
          }

          $myTable->add_cell(new TABLE_cell("<input type = 'text' align ='right' name = 'prov_mnt$id_doss' value =$prov_mnt_new  />", 1, 1));
          $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'prov_is_calcul$id_doss' checked='true'  />", 1, 1));

        }
        //Boutons
        $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Pcs-7");
        $myForm->addFormButton(1, 2, "precedent", _("Précédent"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, "Pcs-4");
        $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-14");
        $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
        $myForm->addFormButton(1, 4, "coche_tout", _("Cocher tout"), TYPB_BUTTON);
        $MyjsCk = 'var checker = "false";
				function check(formulaire_rad_cre) {
					if (checker == "false") {
  						for (i = 0; i < formulaire_rad_cre.length; i++) {
  							formulaire_rad_cre[i].checked = true;}
  							checker = "true";
  						 }
					else {
  						for (i = 0; i < formulaire_rad_cre.length; i++) {
  							formulaire_rad_cre[i].checked = false;}
  							checker = "false";
  						 }
				}';
        $myForm->addJS(JSP_FORM, "js_check", $MyjsCk);
        $myForm->setFormButtonProperties("coche_tout", BUTP_JS_EVENT, array("onclick" => "check(document.ADForm);"));
        $myForm->buildHTML();
        echo $myForm->getHTML();
      }
    }*/



  }
/*}}}*/
/*{{{ Pcs-6 : Modification des provisions des crédits en souffrances - choix modification client */
  else
    if ($global_nom_ecran == 'Pcs-6') {

      global $adsys;

      $date_prov = $SESSION_VARS['date_prov'] = $_POST['date_prov'];
      $dates_allowed = getAllowedDatesForBackdateProvision($date_prov);
      $SESSION_VARS['id_exo'] = $dates_allowed['id_exo'];

      if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2) { // dossier individuel
        // Les informations sur le dossier
        $id_doss = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_doss'];
        $id_prod = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_prod'];;
        $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
        $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
        $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'];
        // Infos dossiers fictifs dans le cas de GS avec dossier unique
        if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 1) {
          $whereCond = " WHERE id_dcr_grp_sol = $id_doss";
          $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] = getCreditFictif($whereCond);
        }
      }
      elseif ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 2) { // GS avec dossiers multiples
        // id du dossier fictif : id du dossier du groupe
        $id_doss_fic = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'];

        // dossiers réels des membre du GS
        $dossiers_membre = getDossiersMultiplesGS($global_id_client);
        foreach ($dossiers_membre as $id_doss => $val) {
          if ($val['id_dcr_grp_sol'] == $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'] and ($val['etat'] == 1 or $val['etat'] == 2 or $val['etat'] == 5)) {
            $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
            $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
            $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'];
            $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
          }
        }
      }

      /* Récupération des garanties déjà mobilisées pour ce dossier */
      foreach ($SESSION_VARS['infos_doss'] as $id_doss => $infos_doss) {
        $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'] = array();
        $liste_gar = getListeGaranties($id_doss);

        foreach ($liste_gar as $key => $value) {
          $num = count($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR']) + 1; // indice du tableau
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['id_gar'] = $value['id_gar'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['type'] = $value['type_gar'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['valeur'] = recupMontant($value['montant_vente']);
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['devise_vente'] = $value['devise_vente'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['etat'] = $value['etat_gar'];

          /* Si c'est une garantie numéraire récupérer le numéro complet du compte de prélèvement */
          if ($value['type_gar'] == 1) /* Garantie numéraire */
            $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['descr_ou_compte'] = $value['gar_num_id_cpte_prelev'];
          elseif ($value['type_gar'] == 2 and isset($value['gar_mat_id_bien'])) { /* garantie matérielle */
            $id_bien = $value['gar_mat_id_bien'];
            $infos_bien = getInfosBien($id_bien);
            $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['descr_ou_compte'] = $infos_bien['description'];
            $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['type_bien'] = $infos_bien['type_bien'];
            $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['piece_just'] = $infos_bien['piece_just'];
            $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['num_client'] = $infos_bien['id_client'];
            $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['remarq'] = $infos_bien['remarque'];
            $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['gar_mat_id_bien'] = $id_bien;
          }
        } /* Fin foreach garantie */
      } /* Fin foreach infos dossiers */

      // Les informations sur le produit de crédit
      $Produit = getProdInfo(" where id =" . $id_prod, $id_doss);
      $SESSION_VARS['infos_prod'] = $Produit[0];

      // Récupérations des utilisateurs. C'est à dire les agents gestionnaires
      $SESSION_VARS['utilisateurs'] = array();
      $utilisateurs = getUtilisateurs();
      foreach ($utilisateurs as $id_uti => $val_uti)
        $SESSION_VARS['utilisateurs'][$id_uti] = $val_uti['nom'] . " " . $val_uti['prenom'];
      //Tri par ordre alphabétique des utilisateurs
      natcasesort($SESSION_VARS['utilisateurs']);
      // Objet demande de crédit
      $SESSION_VARS['obj_dem'] = getObjetsCredit();
      // } //fin si on vient de Apd-1

      // Gestion de la devise
      setMonnaieCourante($SESSION_VARS['infos_prod']['devise']);
      $id_prod = $SESSION_VARS['infos_prod']['id'];
      //recuperation de la precision de la devise du produit de credit
      $devise_prod = $SESSION_VARS['infos_prod']['devise'];
      $DEV = getInfoDevise($devise_prod);// recuperation d'info sur la devise'
      $precision_devise = pow(10, $DEV["precision"]);

      // Création du formulaire
      $Myform = new HTML_GEN2(_("Modification dossier de crédit à la date $date_prov"));

      $js_check = ""; // Javascript de validation de la saisie
      $js_copie_mnt_dem = ""; // sauvegare de mnt_dem si le champ est désactivé
      $gar_mob = false; // déterinantion du prochain écran

      foreach ($SESSION_VARS['infos_doss'] as $id_doss => $val_doss)
      {
        $nom_cli = getClientName($val_doss['id_client']);
        $Myform->addHTMLExtraCode("espace" . $id_doss, "<br /><b><p align=\"center\"><b> " . sprintf(_("Modification du dossier N° %s de %s"), $id_doss, $nom_cli) . "</b></p>");

        $infos_doss_hist = getDossiersProvisionData(null,null, null, null, ($SESSION_VARS['date_prov']), $id_doss);

        $Myform->addField("id_doss" . $id_doss, _("Numéro de dossier"), TYPC_TXT);
        $Myform->setFieldProperties("id_doss" . $id_doss, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("id_doss" . $id_doss, FIELDP_DEFAULT, $val_doss['id_doss']);
        $Myform->addField("id_prod" . $id_doss, _("Produit de crédit"), TYPC_LSB);
        $Myform->setFieldProperties("id_prod" . $id_doss, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("id_prod" . $id_doss, FIELDP_ADD_CHOICES, array("$id_prod" => $SESSION_VARS['infos_prod']['libel']));
        $Myform->setFieldProperties("id_prod" . $id_doss, FIELDP_DEFAULT, $id_prod);
        // Ajout de liens
        $Myform->addLink("id_prod" . $id_doss, "produit" . $id_doss, _("Détail produit"), "#");
        $Myform->setLinkProperties("produit" . $id_doss, LINKP_JS_EVENT, array("onClick" => "open_produit(" . $id_prod . "," . $id_doss . ");"));
        $Myform->addField("periodicite" . $id_doss, _("Périodicité"), TYPC_INT);
        $Myform->setFieldProperties("periodicite" . $id_doss, FIELDP_DEFAULT, adb_gettext($adsys["adsys_type_periodicite"][$SESSION_VARS['infos_prod']['periodicite']]));
        $Myform->setFieldProperties("periodicite" . $id_doss, FIELDP_IS_LABEL, true);
        $Myform->addField("obj_dem" . $id_doss, _("Objet de la demande"), TYPC_LSB);
        $Myform->setFieldProperties("obj_dem" . $id_doss, FIELDP_ADD_CHOICES, $SESSION_VARS['obj_dem']);
        $Myform->setFieldProperties("obj_dem" . $id_doss, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("obj_dem" . $id_doss, FIELDP_DEFAULT, $val_doss['obj_dem']);

        $val_doss['detail_obj_dem'] = $val_doss['detail_obj_dem'];
        $Myform->addField("detail_obj_dem" . $id_doss, _("Détail objet demande"), TYPC_TXT);
        $Myform->setFieldProperties("detail_obj_dem" . $id_doss, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("detail_obj_dem" . $id_doss, FIELDP_DEFAULT, $val_doss['detail_obj_dem']);

        $Myform->addField("id_agent_gest" . $id_doss, _("Agent gestionnaire"), TYPC_LSB);
        $Myform->setFieldProperties("id_agent_gest" . $id_doss, FIELDP_ADD_CHOICES, $SESSION_VARS['utilisateurs']);
        $Myform->setFieldProperties("id_agent_gest" . $id_doss, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("id_agent_gest" . $id_doss, FIELDP_DEFAULT, $val_doss['id_agent_gest']);
        $Myform->addField("etat" . $id_doss, _("Etat du dossier"), TYPC_TXT);
        $Myform->setFieldProperties("etat" . $id_doss, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("etat" . $id_doss, FIELDP_DEFAULT, adb_gettext($adsys["adsys_etat_dossier_credit"][$val_doss['etat']]));
        $Myform->addField("date_etat" . $id_doss, _("Date état du dossier"), TYPC_DTE);
        $Myform->setFieldProperties("date_etat" . $id_doss, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("date_etat" . $id_doss, FIELDP_DEFAULT, $val_doss['date_etat']);

        $Myform->addField("cre_mnt_octr" . $id_doss, _("Montant octroyé"), TYPC_MNT);
        $Myform->setFieldProperties("cre_mnt_octr" . $id_doss, FIELDP_IS_REQUIRED, true);
        $Myform->setFieldProperties("cre_mnt_octr" . $id_doss, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("cre_mnt_octr" . $id_doss, FIELDP_DEFAULT, $val_doss['cre_mnt_octr']);
        $Myform->setFieldProperties("cre_mnt_octr" . $id_doss, FIELDP_JS_EVENT, array("OnFocus" => "reset($id_doss);"));
        $Myform->setFieldProperties("cre_mnt_octr" . $id_doss, FIELDP_JS_EVENT, array("OnChange" => "init($id_doss);"));

        if ($val_doss['gs_cat'] == 1)
          $Myform->setFieldProperties("cre_mnt_octr" . $id_doss, FIELDP_IS_LABEL, true);

        $Myform->addField("cre_date_debloc" . $id_doss, _("Date de déblocage"), TYPC_DTE);
        $Myform->setFieldProperties("cre_date_debloc" . $id_doss, FIELDP_DEFAULT, $val_doss['cre_date_debloc']);
        $Myform->setFieldProperties("cre_date_debloc" . $id_doss, FIELDP_IS_LABEL, true);
        $Myform->addField("cre_etat" . $id_doss, _("Etat crédit"), TYPC_INT);
        $Myform->setFieldProperties("cre_etat" . $id_doss, FIELDP_DEFAULT, $infos_doss_hist[$id_doss]['id_etat_credit']);
        $Myform->setFieldProperties("cre_etat" . $id_doss, FIELDP_IS_LABEL, true);

        //type de durée : en mois ou en semaine
        $type_duree = $SESSION_VARS['infos_prod']["type_duree_credit"];
        $libelle_duree = mb_strtolower(adb_gettext($adsys["adsys_type_duree_credit"][$type_duree])); // libellé type durée en minuscules
        $Myform->addField("duree_mois" . $id_doss, sprintf(_("Durée en %s"), $libelle_duree), TYPC_INT);
        $Myform->setFieldProperties("duree_mois" . $id_doss, FIELDP_IS_REQUIRED, true);
        $Myform->setFieldProperties("duree_mois" . $id_doss, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("duree_mois" . $id_doss, FIELDP_DEFAULT, $val_doss['duree_mois']);


        if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
          $Myform->addField("gar_num_encours" . $id_doss, _("Garantie numéraire encours"), TYPC_MNT);
          $Myform->setFieldProperties("gar_num_encours" . $id_doss, FIELDP_IS_LABEL, true);
          $Myform->setFieldProperties("gar_num_encours" . $id_doss, FIELDP_DEFAULT, $val_doss['gar_num_encours']);
        }
        $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $val_doss['cre_mnt_octr'];
        $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob'] = 0; // garanties numéraires totales mobilisées
        $SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob'] = 0; // garanties matérilles totales mobilisées

        if (is_array($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR']))
          foreach ($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'] as $key => $value) {
            if ($value['type'] == 1)
              $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob'] += recupMontant($value['valeur']);
            elseif ($value['type'] == 2)
              $SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob'] += recupMontant($value['valeur']);
          }
        if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
          $Myform->addField("gar_num_mob" . $id_doss, _("Garantie numéraire mobilisée"), TYPC_MNT);
          $Myform->setFieldProperties("gar_num_mob" . $id_doss, FIELDP_IS_LABEL, true);
          $Myform->setFieldProperties("gar_num_mob" . $id_doss, FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob']);
          $Myform->addField("gar_mat_mob" . $id_doss, _("Garantie matérielle mobilisée"), TYPC_MNT);
          $Myform->setFieldProperties("gar_mat_mob" . $id_doss, FIELDP_IS_LABEL, true);
          $Myform->setFieldProperties("gar_mat_mob" . $id_doss, FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob']);
        }

        /*$Myform->addField("gar_num_mob".$id_doss, _("Garantie numéraire mobilisée"), TYPC_MNT);
        $Myform->setFieldProperties("gar_num_mob".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("gar_num_mob".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob']);
    */
        $Myform->addField("prov_date" . $id_doss, _("Date provisions"), TYPC_DTE);
        $Myform->setFieldProperties("prov_date" . $id_doss, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("prov_date" . $id_doss, FIELDP_DEFAULT, $date_prov);

        $Myform->addField("prev_prov_date" . $id_doss, _("Date provisions antérieure"), TYPC_DTE);
        $Myform->setFieldProperties("prev_prov_date" . $id_doss, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("prev_prov_date" . $id_doss, FIELDP_DEFAULT, $val_doss['prov_date']);

        //#549 : add new coluums
        $Myform->addField("provisions_required" . $id_doss, _("Provisions exigées"), TYPC_MNT);
        $Myform->addField("previous_provisions" . $id_doss, _("Provisions antérieures"), TYPC_MNT);
        $Myform->setFieldProperties("previous_provisions" . $id_doss, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("provisions_required" . $id_doss, FIELDP_IS_LABEL, true);

        // Calcul provision
        //$prov_data = calculprovision($id_doss);
        $prov_data = calculprovision($id_doss, $infos_doss_hist[$id_doss]['id_etat_credit'], $infos_doss_hist[$id_doss]['taux_prov'], $date_prov, $infos_doss_hist[$id_doss]['capital_restant_du'], $infos_doss_hist[$id_doss]['solde_gar'], $infos_doss_hist[$id_doss]['prov_mnt']);

        $Myform -> setFieldProperties("provisions_required" . $id_doss, FIELDP_DEFAULT , $prov_data['provisions_required']);
        $Myform -> setFieldProperties("previous_provisions" . $id_doss, FIELDP_DEFAULT , $prov_data['previous_provisions']);

        $Myform->addField("prov_mnt" . $id_doss, _("Montant provisionné"), TYPC_MNT);
        //$Myform->setFieldProperties("prov_mnt" . $id_doss, FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['prov_mnt']);
        $Myform->setFieldProperties("prov_mnt" . $id_doss, FIELDP_DEFAULT, $prov_data['additional_provisions']);

       /* $Myform->addField("prov_is_calcul" . $id_doss, _("Provisionné ?"), TYPC_BOL);
        if ($SESSION_VARS['infos_doss'][$id_doss]['prov_is_calcul'] == 'f' OR $SESSION_VARS['infos_doss'][$id_doss]['prov_is_calcul'] == NULL) {
          $SESSION_VARS['infos_doss'][$id_doss]['prov_is_calcul'] = false;
        } elseif ($SESSION_VARS['infos_doss'][$id_doss]['prov_is_calcul'] == 't') {
          $SESSION_VARS['infos_doss'][$id_doss]['prov_is_calcul'] = true;
        }
        $Myform->setFieldProperties("prov_is_calcul" . $id_doss, FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['prov_is_calcul']);*/

        $Myform->addHiddenType("prov_is_calcul" . $id_doss, true);

        // Affichage des dossiers fictifs dans le cas d'un GS avec dossier réel unique
        if ($SESSION_VARS['infos_doss'][$id_doss]['gs_cat'] == 1) {
          $js_mnt_dem = "function calculeMontant() {"; // function de calcule du demandé
          $js_mnt_dem .= "var tot_mnt_dem = 0;\n";

          foreach ($SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] as $id_fic => $val_fic) {
            $val_fic['detail_obj_dem'] = $val_fic['detail_obj_dem'];
            $Myform->addHTMLExtraCode("espace_fic" . $id_fic, "<BR>");
            $Myform->addField("membre" . $id_fic, _("Membre"), TYPC_TXT);
            $Myform->setFieldProperties("membre" . $id_fic, FIELDP_IS_REQUIRED, true);
            $Myform->setFieldProperties("membre" . $id_fic, FIELDP_IS_LABEL, true);
            $Myform->setFieldProperties("membre" . $id_fic, FIELDP_DEFAULT, $val_fic['id_membre'] . " " . getClientName($val_fic['id_membre']));
            $Myform->addField("obj_dem_fic" . $id_fic, _("Objet demande"), TYPC_LSB);
            $Myform->setFieldProperties("obj_dem_fic" . $id_fic, FIELDP_ADD_CHOICES, $SESSION_VARS['obj_dem']);
            $Myform->setFieldProperties("obj_dem_fic" . $id_fic, FIELDP_IS_REQUIRED, true);
            $Myform->setFieldProperties("obj_dem_fic" . $id_fic, FIELDP_DEFAULT, $val_fic['obj_dem']);
            $Myform->addField("detail_obj_dem_fic" . $id_fic, _("Détail demande"), TYPC_TXT);
            $Myform->setFieldProperties("detail_obj_dem_fic" . $id_fic, FIELDP_IS_REQUIRED, true);
            $Myform->setFieldProperties("detail_obj_dem_fic" . $id_fic, FIELDP_DEFAULT, $val_fic['detail_obj_dem']);
            $Myform->addField("mnt_dem_fic" . $id_fic, _("Montant demandé"), TYPC_MNT);
            $Myform->setFieldProperties("mnt_dem_fic" . $id_fic, FIELDP_IS_REQUIRED, true);
            $Myform->setFieldProperties("mnt_dem_fic" . $id_fic, FIELDP_DEFAULT, $val_fic['mnt_dem'], true);
            $Myform->setFieldProperties("mnt_dem_fic" . $id_fic, FIELDP_JS_EVENT, array("OnChange" => "calculeMontant();"));

            if ($val_doss['etat'] != 1) {
              $Myform->setFieldProperties("obj_dem_fic" . $id_fic, FIELDP_IS_LABEL, true);
              $Myform->setFieldProperties("detail_obj_dem_fic" . $id_fic, FIELDP_IS_LABEL, true);
              $Myform->setFieldProperties("mnt_dem_fic" . $id_fic, FIELDP_IS_LABEL, true);
            }

            $js_mnt_dem .= "tot_mnt_dem = tot_mnt_dem + recupMontant(document.ADForm.mnt_dem_fic" . $id_fic . ".value);\n";
          }
          $js_mnt_dem .= "document.ADForm.mnt_dem" . $id_doss . ".value = formateMontant(tot_mnt_dem);\n";
          if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
            $js_mnt_dem .= "\n\tdocument.ADForm.gar_num" . $id_doss . ".value =formateMontant(Math.round(" . $SESSION_VARS['infos_prod']['prc_gar_num'] . "*parseFloat(tot_mnt_dem))*" . $precision_devise . ")/" . $precision_devise . ";";
          }
          if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
            $js_mnt_dem .= "\n\tdocument.ADForm.gar_mat" . $id_doss . ".value = formateMontant(Math.round(" . $SESSION_VARS['infos_prod']['prc_gar_mat'] . "*parseFloat(tot_mnt_dem))*" . $precision_devise . ")/" . $precision_devise . ";";
          }
          if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
            $js_mnt_dem .= "\n\tdocument.ADForm.gar_tot" . $id_doss . ".value = formateMontant(Math.round(" . $SESSION_VARS['infos_prod']['prc_gar_tot'] . "*parseFloat(tot_mnt_dem))*" . $precision_devise . ")/" . $precision_devise . ";";
          }
          if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
            $js_mnt_dem .= "\n\tdocument.ADForm.gar_num_encours" . $id_doss . ".value = formateMontant(Math.round(" . $SESSION_VARS['infos_prod']['prc_gar_encours'] . "*parseFloat(tot_mnt_dem))*" . $precision_devise . ")/" . $precision_devise . ";";
          }

          $js_mnt_dem .= "}\n";
          $Myform->addJS(JSP_FORM, "js_mnt_dem", $js_mnt_dem);
        }


        // Contrôle Javascript
        // Vérifier que le montant totat mobilisé est supérieur ou égal au montant attendu
        if ($SESSION_VARS['infos_doss'][$id_doss]['gar_tot'] > 0) {
          $gar_num_mob = "document.ADForm.gar_num_mob" . $id_doss;
          $gar_mat_mob = "document.ADForm.gar_mat_mob" . $id_doss;
          $gar_tot = "document.ADForm.gar_tot" . $id_doss;
          if ($SESSION_VARS['infos_doss'][$id_doss]['gar_num'] > 0) {
            $gar_num = "document.ADForm.gar_num" . $id_doss;
            // Vérifer que les garanties numéraires mobilisées sont supérieues aux garanties numéraires attendues
            $js_check .= "
      if (recupMontant($gar_num.value) > recupMontant($gar_num_mob.value))
                   {
                       msg += '- " . sprintf(_("Les garanties numéraires mobilisées par le dossier %s sont insuffisantes"), $id_doss) . "\\n';
                       ADFormValid = false;
                   }";
          }
          if ($SESSION_VARS['infos_doss'][$id_doss]['gar_mat'] > 0) {
            $gar_mat = "document.ADForm.gar_mat" . $id_doss;
            // Vérifer que les garanties matérielles mobilisées sont supérieues aux garanties matérielles attendues
            $js_check .= "
      if (recupMontant($gar_mat.value) > recupMontant($gar_mat_mob.value))
                   {
                       msg += '- " . sprintf(_("Les garanties matérielles mobilisées par le dossier %s sont insuffisantes"), $id_doss) . "\\n';
                       ADFormValid = false;
                   }";
          }
          $js_check .= "
      gar_tot_mob = recupMontant($gar_num_mob.value)+recupMontant($gar_mat_mob.value);
      if (recupMontant($gar_tot.value) > gar_tot_mob)
                   {
                       msg += '- " . sprintf(_("Le montant total des garanties numéraires et matérielles mobilisées par le dossier %s est insuffisant"), $id_doss) . "\\n';
                       ADFormValid = false;
                   }";
        }

        // Vérification de la durée en mois
        $js_check .= "\tif(parseInt(" . $SESSION_VARS['infos_prod']['duree_max_mois'] . ")>0){\n";
        $js_check .= "\t\tif((parseInt(document.ADForm.duree_mois" . $id_doss . ".value) < parseInt(" . $SESSION_VARS['infos_prod']['duree_min_mois'] . ")) || (parseInt(document.ADForm.duree_mois" . $id_doss . ".value) > parseInt(" . $SESSION_VARS['infos_prod']['duree_max_mois'] . "))) { msg+=' - La durée du crédit doit être comprise entre " . $SESSION_VARS['infos_prod']['duree_min_mois'] . " et " . $SESSION_VARS['infos_prod']['duree_max_mois'] . " comme définie dans le produit.\\n';ADFormValid=false;}\n";
        $js_check .= "\t}else\n";
        $js_check .= "\t\tif(parseInt(document.ADForm.duree_mois" . $id_doss . ".value) < parseInt(" . $SESSION_VARS['infos_prod']['duree_min_mois'] . ")) { msg+=' - " . sprintf(_("La durée du crédit doit être au moins égale à %s"), $SESSION_VARS['infos_prod']['duree_min_mois']) . ". " . _("comme définie dans le produit") . ".\\n';ADFormValid=false;}\n";

        $js_check2 = "\n if(recupMontant(document.ADForm.prov_mnt" . $id_doss . ".value) == 0) { alert(' - La dotation ou reprise sur ce dossier de crédit n\'est pas possible ');ADFormValid=false;}\n";
      }
      $Myform->addJS(JSP_BEGIN_CHECK, "js_check2", $js_check2);
      $Myform->addJS(JSP_BEGIN_CHECK, "test", $js_check);

      //Les boutons ajoutés
      $Myform->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
      $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Pcs-7");
      $Myform->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);


      // Propriétés des boutons
      $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-14");
      //$Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
      $Myform->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);


      $Myform->buildHTML();
      echo $Myform->getHTML();


    }
/*}}}*/
/*{{{ Pcs-7 : Confirmation Modification des provisions des crédits en souffrances*/
    else
      if ($global_nom_ecran == 'Pcs-7') {
        $Data = array();

        foreach ($SESSION_VARS['infos_doss'] as $id_doss => $val_doss) {
          $prov_mnt = recupMontant(${'prov_mnt' . $id_doss});
          if (${'prov_is_calcul' . $id_doss} == 'on' OR ${'prov_is_calcul' . $id_doss} == true) $prov_is_calcul = true;
          else $prov_is_calcul = false;

          if ($prov_is_calcul) {
            $Data[$id_doss]['id_doss'] = $val_doss['id_doss'];
            $Data[$id_doss]["id_prod"] = $val_doss["id_prod"];
            $Data[$id_doss]['prov_mnt_new'] = recupMontant(${'prov_mnt' . $id_doss});
            $Data[$id_doss]['prov_mnt'] = $val_doss['prov_mnt'];
            $Data[$id_doss]['cre_etat'] = $val_doss['cre_etat'];
            $Data[$id_doss]['devise'] = $val_doss['devise'];
            $Data[$id_doss]['id_client'] = $val_doss['id_client'];
            $Data[$id_doss]['prov_is_calcul'] = $prov_is_calcul;

            $dotation = true;
            if (recupMontant( ${'prov_mnt'.$id_doss}) < 0 ){
              $dotation = false;
            }
            $Data[$id_doss]['dotation']=$dotation;
          }
        }

        if (count($Data) > 0) {
          $myErr = modifierProvCreditsSouffrances($Data, $SESSION_VARS['date_prov']);

          if ($myErr->errCode != NO_ERR) {
            $html_err = new HTML_erreur(_("Echec lors de la modification des provisions des crédits en souffrances. "));
            $html_err->setMessage("Erreur : " . $error[$myErr->errCode] . $myErr->param);
            $html_err->addButton("BUTTON_OK", 'Pcs-1');
            $html_err->buildHTML();
            echo $html_err->HTML_code;
            exit();
          } else {
            $myMsg = new HTML_message(_("Modification provisions en souffrances terminée"));
            $msg = _("La modification des provisions des crédits en souffrance  s'est terminé avec succès");
            $msg .= "<BR><BR>" . _(sprintf("Provision                : %d dossiers de crédits traités ", $myErr->param["nbre_prov"]));
            $msg .= "<BR>" . _(sprintf("Reprise sur la provision : %d dossiers crédits traités", $myErr->param["nbre_prov_reprise"]));
            $myMsg->setMessage($msg);

            $myMsg->addButton(BUTTON_OK, 'Gen-3');
            $myMsg->buildHTML();
            echo $myMsg->HTML_code;
          }
        } elseif ($etat_credits == NULL) {
          $erreur = new HTML_erreur(_("Dossiers inexistants"));
          $erreur->setMessage(_("Il n'y a aucun dossier à Modifier"));
          $erreur->addButton("BUTTON_OK", "Gen-14");
          $erreur->buildHTML();
          echo $erreur->HTML_code;
          exit();
        }

      }
/*}}}*/
/*{{{ Pcs-8 : Liste des dossiers crédits à provisionner */
else
  if ($global_nom_ecran == 'Pcs-8') {
    global $adsys;

    $date_prov = $SESSION_VARS['date_prov'] = $_POST['date_prov'];
    $dates_allowed = getAllowedDatesForBackdateProvision($date_prov);
    $SESSION_VARS['id_exo'] = $dates_allowed['id_exo'];
    $type_produit =$_POST["type"];

    // liste
    $etat_credits = getDossiersProvisionData($type_produit,null, null, null, ($SESSION_VARS['date_prov']), null, true);

    $SESSION_VARS["dossiers_prov"] = $etat_credits;

    if ($etat_credits == NULL) {
      $erreur = new HTML_erreur(_("Dossiers inexistants"));
      $erreur->setMessage(_("Il n'y a aucun dossier à provisionner"));
      $erreur->addButton("BUTTON_OK","Gen-14");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      $ok = false;
    } else {
      $myForm = new HTML_GEN2(_(" Provisions des crédits au $date_prov"));
      $myTable =& $myForm->addHTMLTable("dossiers_prov", 10, TABLE_STYLE_ALTERN);
      $myTable->add_cell(new TABLE_cell(_("Id dossier"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Id client"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Etat crédit"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Capital Restant Dû "), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Garantie Total"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("taux"), 1, 1));

      //#549 : add new coluums
      $myTable->add_cell(new TABLE_cell(_("Provisions exigées"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Provisions antérieures"), 1, 1));

      $myTable->add_cell(new TABLE_cell(_("Montant provisionné"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("provisionné ?"), 1, 1));

      while (list(,$credit) = each($etat_credits)) {
        $id_doss = $credit['id_doss'];

        $libel_etat_credit = ($credit['etat'] == 12)?$adsys["adsys_etat_dossier_credit"][12]:$credit['libel_etat_credit'];
        $myTable->add_cell(new TABLE_cell($credit['id_doss'], 1, 1));
        $myTable->add_cell(new TABLE_cell($credit['id_client'], 1, 1));
        $myTable->add_cell(new TABLE_cell(_($libel_etat_credit), 1, 1));
        $myTable->add_cell(new TABLE_cell(afficheMontant($credit['capital_restant_du']), 1, 1));
        $myTable->add_cell(new TABLE_cell(afficheMontant($credit['mnt_gar_mob']), 1, 1));
        $myTable->add_cell(new TABLE_cell($credit['taux'], 1, 1));

        //Provisions exigées
        $provisions_required = afficheMontant($credit['provisions_required']);
        $myTable->add_cell(new TABLE_cell($provisions_required, 1, 1));

        //Provisions anterieures
        $previous_provisions = afficheMontant($credit['previous_provisions']);
        $myTable->add_cell(new TABLE_cell($previous_provisions, 1, 1));

        // additional provisions / mnt provisions
        $additional_provisions = afficheMontant($credit['additional_provisions']);

        $myTable->add_cell(new TABLE_cell("<input type = 'text' align ='right' name = 'prov_mnt$id_doss' value=$additional_provisions  />", 1, 1));
        $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'prov_is_calcul$id_doss'  checked='true' />", 1, 1));
      }
      //Boutons
      $myForm->addFormButton(1,1, "valider", _("Valider"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Pcs-3");
      $myForm->addFormButton(1,2, "precedent", _("Précédent"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, "Pcs-2");
      $myForm->addFormButton(1,3, "annuler", _("Annuler"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-14");
      $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
      $myForm->addFormButton(1,4, "coche_tout", _("Cocher / Décocher tous"), TYPB_BUTTON);
      $MyjsCk = 'var checker = "false";
				function check(formulaire_rad_cre) {
					if (checker == "false") {
  						for (i = 0; i < formulaire_rad_cre.length; i++) {
  							formulaire_rad_cre[i].checked = true;}
  							checker = "true";
  						 }
					else {
  						for (i = 0; i < formulaire_rad_cre.length; i++) {
  							formulaire_rad_cre[i].checked = false;}
  							checker = "false";
  						 }
				}';
      $myForm->addJS(JSP_FORM, "js_check", $MyjsCk);
      $myForm->setFormButtonProperties("coche_tout", BUTP_JS_EVENT, array("onclick" => "check(document.ADForm);"));
      $myForm->buildHTML();
      echo $myForm->getHTML();
    }

  }
?>
