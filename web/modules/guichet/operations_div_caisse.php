<?php
require_once 'lib/dbProcedures/guichet.php';
require_once 'lib/dbProcedures/devise.php';
require_once 'modules/rapports/xml_guichet.php';

if ($global_nom_ecran == "Pdc-1") {
  $MyPage = new HTML_GEN2(_("Saisie opération diverse de caisse/compte"));

  $SESSION_VARS['infos_recu'] = array(); // informations du reçu qui sera généré
  $listeODC=array(); // Liste des opérations diverses de caisse/compte

	if (profil_has_guichet($global_id_profil)) {
 		$ODC=getODC();
 	} else {
 		$ODC=getODC('categorie_ope = 3');
 	}

  if (isset($ODC)) {
    $js1 = "function chercherNumCpte()
         {";
    $js2 = "function effacerNumCpte()
         {";
    foreach($ODC as $key=>$value) {
      $listeODC[$key] = $value['libel_ope'];
      // Opération de catégorie 3
      if ($value['categorie_ope'] == 3) {
        $js1 .= "if (document.ADForm.HTML_GEN_LSB_odc.value == ".$key.")
              {
                OpenBrw('../modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=1&cpt_dest=num_cpte&id_cpt_dest=id_cpte');
              }";
      }
      // Opération de catégorie 2
      else {
        $js2 .= "if (document.ADForm.HTML_GEN_LSB_odc.value == ".$key.")
              {
                document.ADForm.num_cpte.value = '';
                document.ADForm.id_cpte.value = '';
              }";
      }
    }
    $js1 .= "};";
    $js2 .= "};";
    $js3 = "document.ADForm.num_cpte.disabled = true;";
  }
    //#610 Ordre dans l'affichage des operations
    $js4 = " function sortOperation()
            {
              sortSelect(document.ADForm.HTML_GEN_LSB_odc);
            }
            document.onload = sortOperation(); \n
           ";

  // si un type d'operation a été choisi.
  if (isset($SESSION_VARS['type_operation'])) {
    $session_operation=$SESSION_VARS['type_operation'];
    $js4 .= " document.ADForm.HTML_GEN_LSB_odc.value = $session_operation;\n ";
  }


  // Ajout du javascript
  $MyPage->addJS(JSP_FORM,"JS1",$js1);
  $MyPage->addJS(JSP_FORM,"JS2",$js2);
  $MyPage->addJS(JSP_FORM,"JS3",$js3);
  $MyPage->addJS(JSP_FORM, "JS4", $js4);

  // Type de l'opération
  $MyPage->addField("odc", _("Opérations diverses de caisse/compte"), TYPC_LSB);
  //$MyPage->setFieldProperties("odc", FIELDP_ADD_CHOICES, $listeODC);
  $MyPage->setFieldProperties("odc", FIELDP_ADD_CHOICES_TRAD, $listeODC);
  $MyPage->setFieldProperties("odc", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("odc", FIELDP_JS_EVENT, array("onchange" => "effacerNumCpte();"));
  if (isset($SESSION_VARS['type_operation'])) {
    $MyPage->setFieldProperties("odc", FIELDP_DEFAULT, $SESSION_VARS['type_operation']);
  }

  // Montant de l'opération
  $MyPage->addField("montant", _("Montant"), TYPC_TXT);
  $MyPage->setFieldProperties("montant", FIELDP_IS_REQUIRED, true);
  //$MyPage->setFieldProperties("montant", FIELDP_DEVISE, '');
  if (isset($SESSION_VARS['montant'])) {
    $MyPage->setFieldProperties("montant", FIELDP_DEFAULT, preg_replace('/\s+/', '',$SESSION_VARS['montant']));
  }

  // Devise de l'opération
  $MyPage->addTable("ad_cpt", OPER_INCLUDE, array('devise'));
  if (isset($SESSION_VARS['devise'])) {
    $MyPage->setFieldProperties("devise", FIELDP_DEFAULT, $SESSION_VARS['devise']);
  }

  // Identifiant du compte de l'opération
  if (isset($SESSION_VARS['id_cpte'])) {
    $MyPage->addHiddenType("id_cpte", $SESSION_VARS['id_cpte']);
  } else {
    $MyPage->addHiddenType("id_cpte");
  }

  // Numéro de compte de l'opération
  $MyPage->addField("num_cpte", _("Numéro de compte"), TYPC_TXT);
  if (isset($SESSION_VARS['num_cpte'])) {
    $MyPage->setFieldProperties("num_cpte", FIELDP_DEFAULT, $SESSION_VARS['num_cpte']);
  }

  // Lien de recherche d'un compte
  $MyPage->addLink("num_cpte", "chercher_cpte", _("Chercher"), "#");
  $MyPage->setLinkProperties("chercher_cpte", LINKP_JS_EVENT, array("onclick" => "chercherNumCpte();"));

  // Bouton valider
  $MyPage->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Pdc-2");

  // Bouton annuler
  $MyPage->addFormButton(1,2,"annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-6");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
  
  if(isset($SESSION_VARS['montant'])){
      unset($SESSION_VARS['montant']);
  }
} else if ($global_nom_ecran == "Pdc-2") { // Confirmation saisie
  global $global_id_agence;

  $MyPage = new HTML_GEN2(_("Confirmation saisie"));

  // Récupération des infos saisies
  $SESSION_VARS["type_operation"] = $odc;
  $SESSION_VARS["montant"] = preg_replace('/\s+/', '', $montant);
  $SESSION_VARS["devise"] = $devise;
  
    //affichage des infos sur les taxes
  $taxesOperation = getTaxesOperation($odc);
  $details_taxesOperation = $taxesOperation->param;
  
  // Calculate actual amount by removing TVA
  if (sizeof($details_taxesOperation) > 0){
      $montant = (preg_replace('/\s+/', '', $montant) / (1 + $details_taxesOperation[1]["taux"]));
  }
  else{
      $montant = preg_replace('/\s+/', '', $montant);
  }
  
  $SESSION_VARS["montant_ht"] = $montant;

  // Récupération des informations de l'opération
  $categorie_ope 	= getOperations($odc)->param['categorie_ope']; // Récupération de la catégorie de l'opération
  $operation 		= getOperations($odc)->param['libel']; // suppression des clés du tableau (type opération)

  $SESSION_VARS["categorie_operation"] = $categorie_ope;

  // Vérification de la catégorie de l'opération
  if ($categorie_ope == 3) {
    // Vérification de l'existence du numéro de compte
    if ($id_cpte == '') {
      $SESSION_VARS["num_cpte"] = '';
      $SESSION_VARS["id_cpte"] = '';
      $MyPage = new HTML_erreur(_("Erreur de compte"));
      $MyPage->setMessage("Erreur : vous devez choisir un compte pour cette opération diverse");
      $MyPage->addButton(BUTTON_OK, "Pdc-1");
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
      die();
    }

    $ACC = getAccountDatas($id_cpte);

    $SESSION_VARS["id_cpte"] = $id_cpte;
    $SESSION_VARS["num_cpte"] = $ACC['num_complet_cpte'];

    // Vérification de la devise du compte
    if ($ACC['devise'] != $devise) {
      $MyPage = new HTML_erreur(_("Erreur de devise"));
      $MyPage->setMessage("Erreur : la devise choisie ne correspond pas à la devise du compte choisi");
      $MyPage->addButton(BUTTON_OK, "Pdc-1");
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
      die();
    }
  } else {
    $SESSION_VARS["id_cpte"] = '';
    $SESSION_VARS["num_cpte"] = '';
  }

  // Détail de opération
  $myErr = getDetailsOperation($odc); // récupération de l'objet erreur
  if ($myErr->errCode != NO_ERR) { // si aucun compte n'est paramétré au débit et au crédit de l'opération
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $detail_ope = $myErr->param; // récupération du tableau des données

  // Récupérations des information des opérations
  if (isset($detail_ope)) {
    // traitement au débit de l'opération
    if ($detail_ope['debit']["categorie"] == 0) {
      $num = $detail_ope['debit']["compte"];
      $param = array();
      $param["num_cpte_comptable"] = $num;
      $compte = getComptesComptables($param);
      $contrepartie = $compte[$num]["num_cpte_comptable"]." ".$compte[$num]["libel_cpte_comptable"];
      if ($detail_ope['debit']["sens"] == SENS_DEBIT) {
        $cpte_debit = $contrepartie;
        $dev_debit = $compte[$num]['devise'];
        if ($dev_debit == '') {
          $dev_debit = $devise;
        }
        $mnt_debit = $montant;
      }
      $sens = $detail_ope['debit']["sens"];
    }
    elseif($detail_ope['debit']["categorie"] == 4) { // Mouvement de caisse
      $cpte_debit = "Guichet";
      $dev_debit = $devise;
      $mnt_debit = $montant;
    }
    elseif($detail_ope['debit']["categorie"] == 2) { // Mouvement de compte
      // Vérification de l'existence du numéro de compte
      if (getSoldeDisponible($id_cpte) < $montant) {
        $MyPage = new HTML_erreur(_("Erreur de solde"));
        $MyPage->setMessage("Erreur : le montant disponible sur ce compte est trop faible pour cette opération diverse");
        $MyPage->addButton(BUTTON_OK, "Pdc-1");
        $MyPage->buildHTML();
        echo $MyPage->HTML_code;
        die();
      }

      $cpte_debit = $ACC['num_complet_cpte']." ".$ACC['intitule_compte'];
      $dev_debit = $devise;
      $mnt_debit = $montant;
    }
    // traitement au crédit de l'opération
    if ($detail_ope['credit']["categorie"] == 0) {
      {
        $num = $detail_ope['credit']["compte"];
        $param = array();
        $param["num_cpte_comptable"] = $num;
        $compte = getComptesComptables($param);
        $contrepartie = $compte[$num]["num_cpte_comptable"]." ".$compte[$num]["libel_cpte_comptable"];
        $cpte_credit = $contrepartie;
        $dev_credit = $compte[$num]['devise'];
        if ($dev_credit == '') {
          $dev_credit = $devise;
        }
        $mnt_credit = $montant;
        $sens = $detail_ope['credit']["sens"];
      }
    }
    elseif($detail_ope['credit']["categorie"] == 4) { // Mouvement de caisse
      $cpte_credit = "Guichet";
      $dev_credit = $devise;
      $mnt_credit = $montant;
    }
    elseif($detail_ope['credit']["categorie"] == 2) { // Mouvement de compte
      $cpte_credit = $ACC['num_complet_cpte']." ".$ACC['intitule_compte'];
      $dev_credit = $devise;
      $mnt_credit = $montant;
    }
  } // fin isset detail op

  if ($sens == SENS_DEBIT) { // sens de l'opératiob diverse
    $mnt_debit = preg_replace('/\s+/', '', calculeCV($dev_credit, $dev_debit, $mnt_debit));
  } else {
    $mnt_credit = preg_replace('/\s+/', '', calculeCV($dev_debit, $dev_credit, $mnt_credit));
  }

  // Ordre de contrepartie
  $ODC = getODC();
  
  $libel_ope = new Trad($ODC[$odc]["libel_ope"]);
  $libel_ope_trad = $libel_ope->traduction();
  
  $MyPage->addField("odc",_("Opération diverse de caisse"), TYPC_TXT);
  $MyPage->setFieldProperties("odc", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("odc", FIELDP_DEFAULT, $libel_ope_trad);
  $SESSION_VARS['infos_recu']['libelle_op'] = $libel_ope_trad;

  // Compte au débit
  $MyPage->addField("cpte_debit",_("Compte au débit"), TYPC_TXT);
  $MyPage->setFieldProperties("cpte_debit", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("cpte_debit", FIELDP_DEFAULT, $cpte_debit);
  $SESSION_VARS['infos_recu']['compte_debit'] = $cpte_debit;

  // Montant débité
  $MyPage->addField("mnt_debit", _("Montant débité"), TYPC_MNT);
  $MyPage->setFieldProperties("mnt_debit", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("mnt_debit", FIELDP_DEFAULT, $mnt_debit);
  $MyPage->setFieldProperties("mnt_debit", FIELDP_DEVISE, $dev_debit);
  $SESSION_VARS['infos_recu']['montant_debit'] = $mnt_debit;
  $SESSION_VARS['infos_recu']['devise_debit'] = $dev_debit;

  // Compte au crédit
  $MyPage->addField("cpte_credit",_("Compte au crédit"), TYPC_TXT);
  $MyPage->setFieldProperties("cpte_credit", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("cpte_credit", FIELDP_DEFAULT, $cpte_credit);
  $SESSION_VARS['infos_recu']['compte_credit'] = $cpte_credit;

  // Montant crédité
  $MyPage->addField("mnt_credit", _("Montant crédité"), TYPC_MNT);
  $MyPage->setFieldProperties("mnt_credit", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("mnt_credit", FIELDP_DEFAULT, $mnt_credit);
  $MyPage->setFieldProperties("mnt_credit", FIELDP_DEVISE, $dev_credit);
  $SESSION_VARS['infos_recu']['montant_credit'] = $mnt_credit;
  $SESSION_VARS['infos_recu']['devise_credit'] = $dev_credit;

	
	$mnt_tax_credit = $mnt_tax_debit = 0;
	$SESSION_VARS['infos_recu']['montant_tax_credit'] = 0;
	$SESSION_VARS['infos_recu']['montant_tax_debit'] = 0;
	if (sizeof($details_taxesOperation) > 0){
		$mnt_tax_debit = $mnt_debit * $details_taxesOperation[1]["taux"];
		$MyPage->addField("mnt_tva_debit", _("Montant tva débité"), TYPC_MNT);
 		$MyPage->setFieldProperties("mnt_tva_debit", FIELDP_IS_LABEL, true);
 		$MyPage->setFieldProperties("mnt_tva_debit", FIELDP_DEFAULT, $mnt_tax_debit);
 		$MyPage->setFieldProperties("mnt_tva_debit", FIELDP_DEVISE, $dev_debit);
 		$SESSION_VARS['infos_recu']['montant_tax_debit'] = $mnt_tax_debit;

		$mnt_tax_credit = $mnt_credit * $details_taxesOperation[1]["taux"];
		$MyPage->addField("mnt_tva_credit", _("Montant tva crédité"), TYPC_MNT);
  	$MyPage->setFieldProperties("mnt_tva_credit", FIELDP_IS_LABEL, true);
  	$MyPage->setFieldProperties("mnt_tva_credit", FIELDP_DEFAULT, $mnt_tax_credit);
  	$MyPage->setFieldProperties("mnt_tva_credit", FIELDP_DEVISE, $dev_credit);
  	$SESSION_VARS['infos_recu']['montant_tax_credit'] = $mnt_tax_credit;
	}
	$SESSION_VARS['infos_recu']['montant_ttc_credit'] = $mnt_credit + $mnt_tax_credit;
	$SESSION_VARS['infos_recu']['montant_ttc_debit'] = $mnt_debit + $mnt_tax_debit;

  // Confirmation montant saisi avec billetage et contrôle de l'égalité des montants
  $MyPage->addField("mnt_ht", _("Montant hors taxe"), TYPC_MNT);
  $MyPage->setFieldProperties("mnt_ht", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("mnt_ht", FIELDP_IS_READONLY, true);
  $MyPage->addField("mnt_ttc", _("Montant toutes taxes"), TYPC_MNT);
  $MyPage->setFieldProperties("mnt_ttc", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("mnt_ttc", FIELDP_IS_READONLY, true);
  $MyPage->addField("bil_mnt", _("Confirmation montant"), TYPC_MNT);
  $MyPage->setFieldProperties("bil_mnt", FIELDP_IS_REQUIRED, true);
  if ($sens == SENS_DEBIT) {
    $MyPage->setFieldProperties("mnt_ht", FIELDP_DEVISE, $dev_credit);
    $MyPage->setFieldProperties("mnt_ht", FIELDP_DEFAULT, $mnt_credit);
    $MyPage->setFieldProperties("mnt_ttc", FIELDP_DEVISE, $dev_credit);
    $MyPage->setFieldProperties("mnt_ttc", FIELDP_DEFAULT, $mnt_credit + $mnt_tax_credit);

    $MyPage->setFieldProperties("bil_mnt", FIELDP_DEVISE, $dev_credit);
    if ($categorie_ope == 3) {
      $MyPage->setFieldProperties("bil_mnt", FIELDP_DEFAULT, $mnt_credit + $mnt_tax_credit);
      $MyPage->setFieldProperties("bil_mnt", FIELDP_IS_READONLY, true);
    } else
      $MyPage->setFieldProperties("bil_mnt", FIELDP_HAS_BILLET, true);

    $MyPage->addJS(JSP_BEGIN_CHECK, "checkMoney", "if (recupMontant(document.ADForm.mnt_ttc.value) != recupMontant(document.ADForm.bil_mnt.value)) {msg += '- Les sommes ne correspondent pas \\n';ADFormValid = false;}");
 	} else {
    $MyPage->setFieldProperties("mnt_ht", FIELDP_DEVISE, $dev_debit);
    $MyPage->setFieldProperties("mnt_ht", FIELDP_DEFAULT, $mnt_debit);
    $MyPage->setFieldProperties("mnt_ttc", FIELDP_DEVISE, $dev_debit);
    $MyPage->setFieldProperties("mnt_ttc", FIELDP_DEFAULT, $mnt_debit + $mnt_tax_debit);
    $MyPage->setFieldProperties("bil_mnt", FIELDP_DEVISE, $dev_debit);
    $MyPage->addJS(JSP_BEGIN_CHECK, "checkMoney", "if (recupMontant(document.ADForm.mnt_ttc.value) != recupMontant(document.ADForm.bil_mnt.value)) {msg += '- Les sommes ne correspondent pas \\n';ADFormValid = false;}");

    if ($categorie_ope == 3) {
      $MyPage->setFieldProperties("bil_mnt", FIELDP_DEFAULT, $mnt_debit + $mnt_tax_debit);
      $MyPage->setFieldProperties("bil_mnt", FIELDP_IS_READONLY, true);
    } else
      $MyPage->setFieldProperties("bil_mnt", FIELDP_HAS_BILLET, true);
  }

  // Pièce justificative
  $MyPage->addTable("ad_his_ext", OPER_INCLUDE, array("type_piece", "num_piece", "date_piece", "communication", "remarque"));
  $MyPage->setFieldProperties("type_piece", FIELDP_INCLUDE_CHOICES, array(9, 10, 11, 12, 13));

  // Ordre des champs de la pièce justificative
  $MyPage->setOrder("bil_mnt", array("type_piece", "num_piece", "date_piece", "communication", "remarque"));
  $SESSION_VARS['id_cpte'] = $id_cpte;

  // Boutons
  $MyPage->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Pdc-3");
  $MyPage->addFormButton(1,2,"precedent", _("Précédent"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, "Pdc-1");
  $MyPage->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);
  $MyPage->addFormButton(1,3,"annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-6");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();

} else if ($global_nom_ecran == "Pdc-3") { // passage de l'opération diverse dans la base
  global $adsys, $global_id_agence;
  $format_A5 = false;
  $type_operation = $SESSION_VARS["type_operation"];
  $montant = str_replace(' ','',strval($SESSION_VARS["montant_ht"])); // recupMontant($mnt_ht)
  $devise = $SESSION_VARS["devise"];
  $id_cpte = $SESSION_VARS['id_cpte'];

  $piece_just = array('type_piece' => $type_piece, 'num_piece' => $num_piece, 'date_piece' => $date_piece, 'remarque' => $remarque, "communication" => $communication);
  
  if(isset($SESSION_VARS["montant_ht"])){
      unset($SESSION_VARS["montant_ht"]);
  }

  $result = passageODC($type_operation, $montant, $devise, $id_cpte, $piece_just);

  if ($result->errCode == NO_ERR) {
		if ($SESSION_VARS["categorie_operation"] == 2) {
		$SESSION_VARS['infos_recu']['ref_doc'] = 'REC-DIV';
		} elseif ($SESSION_VARS["categorie_operation"] == 3) {
			$SESSION_VARS['infos_recu']['ref_doc'] = 'GUI-DIV';
		}
		$liste_type_piece = getListeTypePieceComptables();
    $SESSION_VARS['infos_recu']['date_op'] = date("d/m/Y");
    $SESSION_VARS['infos_recu']['type_piece'] = $liste_type_piece[$type_piece];
    $SESSION_VARS['infos_recu']['numero_piece'] = $num_piece;
    $SESSION_VARS['infos_recu']['date_piece'] = $date_piece;
    $SESSION_VARS['infos_recu']['communication'] = $communication;
    $SESSION_VARS['infos_recu']['remarque'] = $remarque;
    $xml = xml_recu_operation_diverse_caisse($SESSION_VARS['infos_recu'],$result->param);
    $AGC = getAgenceDatas($global_id_agence);
	  if($AGC['imprimante_matricielle'] == 't'){
	  	$format_A5 = true;
	  }
    //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
    if($format_A5){
    	$fichier_pdf = xml_2_xslfo_2_pdf($xml, 'operation_diverse_caisseA5.xslt');
    } else {
    	$fichier_pdf = xml_2_xslfo_2_pdf($xml, 'operation_diverse_caisse.xslt');
    }

    $msg = _("L'opération a été réalisée avec succès ");
    $msg .= "</UL><br /><br />"._("Numéro de transaction")." : <code><B>".sprintf("%09d", $result->param)."</B></code>";
    echo get_show_pdf_html("Gen-6", $fichier_pdf, $msg);

  } else {
    $MyPage = new HTML_erreur(_("Echec"));
    $msg = "Erreur : ".$error[$result->errCode].$result->param." Echec de l'opération";
    $MyPage->setMessage($msg);
    $MyPage->addButton(BUTTON_OK, "Gen-6");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
} else
  signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
?>
