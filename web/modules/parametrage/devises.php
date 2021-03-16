<?php
require_once 'lib/dbProcedures/devise.php';
require_once 'modules/rapports/xml_devise.php';
//Gestion des devises
$tDevise=get_table_devises();
$dataAgence = getAgenceDatas($global_id_agence);
$codeJS ="function verifDeviseDoublon()
         {
         ";
$codeJS .= "   var arrayDevise = new Array(";
foreach($tDevise as $key=>$value) {
  $codeJS .="\"$key\",";
}
$codeJS .="\"\");
          var ligneDevise=arrayDevise.toString();
          var message=\"\";
          document.ADForm.code_devise.value=document.ADForm.code_devise.value.toUpperCase();
          if (document.ADForm.code_devise.value.length!=3)
          {
          message='"._("Le code devise doit avoir exactement 3 caractères")."';
          }
          if (ligneDevise.search(document.ADForm.code_devise.value)!=-1)
          {
          message='"._("Cette devise a déjà été encodée")."';
          }
          if (message!=\"\")
          {
          alert(message);
          document.ADForm.code_devise.value=\"\";
          }
          }

          function decimale()
          {
          //nbre détermine le nombre de chiffres après la virgule
          var nbre=1000000000000;
          if (!isNaN(document.ADForm.taux_indicatif.value)) document.ADForm.taux_indicatif.value=Math.round(document.ADForm.taux_indicatif.value*nbre)/nbre;
          if (!isNaN(document.ADForm.unSurTx.value)) document.ADForm.unSurTx.value=Math.round(document.ADForm.unSurTx.value*nbre)/nbre;
          if (!isNaN(document.ADForm.taux_achat_cash.value)) document.ADForm.taux_achat_cash.value=Math.round(document.ADForm.taux_achat_cash.value*nbre)/nbre;
          if (!isNaN(document.ADForm.unSurAcCash.value)) document.ADForm.unSurAcCash.value=Math.round(document.ADForm.unSurAcCash.value*nbre)/nbre;
          if (!isNaN(document.ADForm.taux_vente_cash.value)) document.ADForm.taux_vente_cash.value=Math.round(document.ADForm.taux_vente_cash.value*nbre)/nbre;
          if (!isNaN(document.ADForm.unSurVeCash.value)) document.ADForm.unSurVeCash.value=Math.round(document.ADForm.unSurVeCash.value*nbre)/nbre;
          if (!isNaN(document.ADForm.taux_achat_trf.value)) document.ADForm.taux_achat_trf.value=Math.round(document.ADForm.taux_achat_trf.value*nbre)/nbre;
          if (!isNaN(document.ADForm.unSurAcTrf.value)) document.ADForm.unSurAcTrf.value=Math.round(document.ADForm.unSurAcTrf.value*nbre)/nbre;
          if (!isNaN(document.ADForm.taux_vente_trf.value)) document.ADForm.taux_vente_trf.value=Math.round(document.ADForm.taux_vente_trf.value*nbre)/nbre;
          if (!isNaN(document.ADForm.unSurVeTrf.value)) document.ADForm.unSurVeTrf.value=Math.round(document.ADForm.unSurVeTrf.value*nbre)/nbre;
          }

          function modifChange()
          {
          if (!isNaN(document.ADForm.taux_indicatif.value) && (document.ADForm.taux_indicatif.value!=0))
          {
          document.ADForm.unSurTx.value = (1 / document.ADForm.taux_indicatif.value);
          document.ADForm.pourcent.value=0;
          calculTauxSecondaires();
          }
          else
          {
          document.ADForm.taux_indicatif.value=\"\";
          document.ADForm.unSurTx.value=\"\";
          }
          decimale();
          }

          function modifUnSurChange()
          {
          if (!isNaN(document.ADForm.unSurTx.value) && (document.ADForm.unSurTx.value!=0))
          {
          document.ADForm.taux_indicatif.value = (1 / document.ADForm.unSurTx.value);
          document.ADForm.pourcent.value=0;
          calculTauxSecondaires();
          }
          else
          {
          document.ADForm.unSurTx.value=\"\";
          modifChange();
          }
          decimale();
          }

          function calculTauxSecondaires()
          {
          document.ADForm.taux_achat_cash.value=document.ADForm.taux_indicatif.value * (100 + Number(document.ADForm.pourcent.value)) / 100;
          document.ADForm.taux_vente_cash.value=document.ADForm.taux_indicatif.value * (100 - document.ADForm.pourcent.value) / 100;
          document.ADForm.unSurAcCash.value = 1 / document.ADForm.taux_achat_cash.value;
          document.ADForm.unSurVeCash.value = 1 / document.ADForm.taux_vente_cash.value;
          document.ADForm.taux_achat_trf.value=document.ADForm.taux_indicatif.value * (100 + Number(document.ADForm.pourcent.value)) / 100;
          document.ADForm.taux_vente_trf.value=document.ADForm.taux_indicatif.value * (100 - document.ADForm.pourcent.value) / 100;
          document.ADForm.unSurAcTrf.value = 1 / document.ADForm.taux_achat_trf.value;
          document.ADForm.unSurVeTrf.value = 1 / document.ADForm.taux_vente_trf.value;
          }

          function modifPourcent()
          {
          if (!isNaN(document.ADForm.pourcent.value))
          {
          if (document.ADForm.pourcent.value < 0) document.ADForm.pourcent.value = 0;
          }
          else
          {
          document.ADForm.pourcent.value=0;
          }
          if (!isNaN(document.ADForm.taux_indicatif.value)) calculTauxSecondaires();
          decimale();
          }

          function modifAchatCash()
          {
          document.ADForm.pourcent.value=\"\";
          if (isNaN(document.ADForm.taux_achat_cash.value) || document.ADForm.taux_achat_cash.value==0)
          {
          document.ADForm.taux_achat_cash.value=\"\";
          document.ADForm.unSurAcCash.value=\"\";
          }
          else
          {
          document.ADForm.unSurAcCash.value= 1 / document.ADForm.taux_achat_cash.value;
          }
          decimale();
          }

          function modifUnSurAchatCash()
          {
          document.ADForm.pourcent.value=\"\";
          if (isNaN(document.ADForm.unSurAcCash.value) || document.ADForm.unSurAcCash.value==0)
          {
          document.ADForm.taux_achat_cash.value=\"\";
          document.ADForm.unSurAcCash.value=\"\";
          }
          else
          {
          document.ADForm.taux_achat_cash.value= 1 / document.ADForm.unSurAcCash.value;
          }
          decimale();
          }
          function modifVenteCash()
          {
          document.ADForm.pourcent.value=\"\";
          if (isNaN(document.ADForm.taux_vente_cash.value) || document.ADForm.taux_vente_cash.value==0)
          {
          document.ADForm.taux_vente_cash.value=\"\";
          document.ADForm.unSurVeCash.value=\"\";
          }
          else
          {
          document.ADForm.unSurVeCash.value= 1 / document.ADForm.taux_vente_cash.value;
          }
          decimale();
          }

          function modifUnSurVenteCash()
          {
          document.ADForm.pourcent.value=\"\";
          if (isNaN(document.ADForm.unSurVeCash.value) || document.ADForm.unSurVeCash.value==0)
          {
          document.ADForm.taux_vente_cash.value=\"\";
          document.ADForm.unSurVeCash.value=\"\";
          }
          else
          {
          document.ADForm.taux_vente_cash.value= 1 / document.ADForm.unSurVeCash.value;
          }
          decimale();
          }

          function modifAchatTrf()
          {
          document.ADForm.pourcent.value=\"\";
          if (isNaN(document.ADForm.taux_achat_trf.value) || document.ADForm.taux_achat_trf.value==0)
          {
          document.ADForm.taux_achat_trf.value=\"\";
          document.ADForm.unSurAcTrf.value=\"\";
          }
          else
          {
          document.ADForm.unSurAcTrf.value= 1 / document.ADForm.taux_achat_trf.value;
          }
          decimale();
          }

          function modifUnSurAchatTrf()
          {
          document.ADForm.pourcent.value=\"\";
          if (isNaN(document.ADForm.unSurAcTrf.value) || document.ADForm.unSurAcTrf.value==0)
          {
          document.ADForm.taux_achat_trf.value=\"\";
          document.ADForm.unSurAcTrf.value=\"\";
          }
          else
          {
          document.ADForm.taux_achat_trf.value= 1 / document.ADForm.unSurAcTrf.value;
          }
          decimale();
          }
          function modifVenteTrf()
          {
          document.ADForm.pourcent.value=\"\";
          if (isNaN(document.ADForm.taux_vente_trf.value) || document.ADForm.taux_vente_trf.value==0)
          {
          document.ADForm.taux_vente_trf.value=\"\";
          document.ADForm.unSurVeTrf.value=\"\";
          }
          else
          {
          document.ADForm.unSurVeTrf.value= 1 / document.ADForm.taux_vente_trf.value;
          }
          decimale();
          }

          function modifUnSurVenteTrf()
          {
          document.ADForm.pourcent.value=\"\";
          if (isNaN(document.ADForm.unSurVeTrf.value) || document.ADForm.unSurVeTrf.value==0)
          {
          document.ADForm.taux_vente_trf.value=\"\";
          document.ADForm.unSurVeTrf.value=\"\";
          }
          else
          {
          document.ADForm.taux_vente_trf.value= 1 / document.ADForm.unSurVeTrf.value;
          }
          decimale();
          }

          ";
// Affichage de la liste des devises et de leurs taux par rapport à la monnaie de référence ou création de la devise de référence
if ($global_nom_ecran == "Dev-1") {
  /*   if (($dataAgence["cpte_position_change"] == NULL) || ($dataAgence["cpte_contreval_position_change"] == NULL) || ($dataAgence["cpte_variation_taux_deb"] == NULL) || ($dataAgence["cpte_variation_taux_cred"] == NULL))
     {
        $html_msg = new HTML_message (_("Paramétres incomplets dans l'agence"));
        $message="<b> "._("Veuillez paramétrer correctement les données de l'agence avant d'introduire une nouvelle devise")."</b><br>";
        $message.="<br>"._("Les comptes suivants sont manquants")." : ";
        if ($dataAgence['cpte_position_change'] == NULL) $message.="<br> - "._("Compte de position de change");
        if ($dataAgence['cpte_contreval_position_change'] == NULL) $message.="<br> - "._("Compte de contrevaleur de la position de change");
        if ($dataAgence['cpte_variation_taux_deb'] == NULL) $message.="<br> - "._("Compte de variation de taux débiteur");
        if ($dataAgence['cpte_variation_taux_cred'] == NULL) $message.="<br> - "._("Compte de variation de taux créditeur");
        $html_msg->setMessage ($message);
        $html_msg->addButton ("BUTTON_OK", "Gen-12");
        $html_msg->buildHTML();
        echo $html_msg->HTML_code;
        } */

  if ($dataAgence['code_devise_reference'] != NULL) { // La devise de référence a déjà été crée
    $MyPage = new HTML_GEN2(_("Gestion des devises"));
    if (isset($_POST['affTaux'])) {
      if ($_POST['affTaux'] == '1/TAUX') {
        $affNormal=false;
        $MyPage->addHiddenType('affTaux','TAUX');
      } else {
        $affNormal=true;
        $MyPage->addHiddenType('affTaux','1/TAUX');
      }
    } else {
      $affNormal=true;
      $MyPage->addHiddenType('affTaux','1/TAUX');
    }

    $modifAutorisee=check_access(276);
    if ($modifAutorisee)
      $table =& $MyPage->addHTMLTable('tabledevises', 8 /*nbre colonnes*/, TABLE_STYLE_ALTERN);
    else
      $table =& $MyPage->addHTMLTable('tabledevises', 7 /*nbre colonnes*/, TABLE_STYLE_ALTERN);

    // Création de la ligne de titres
    $table->add_cell(new TABLE_cell(_("Code"),	/*colspan*/1,	/*rowspan*/2	));
    $table->add_cell(new TABLE_cell(_("Libellé"),	/*colspan*/1,	/*rowspan*/2	));
    if ($affNormal) {
      $table->add_cell(new TABLE_cell(_("Taux indicatif"),	/*colspan*/1,	/*rowspan*/2	));
      $table->add_cell(new TABLE_cell(_("Taux achat"),	/*colspan*/2));
      $table->add_cell(new TABLE_cell(_("Taux vente"),	/*colspan*/2));
    } else {
      $table->add_cell(new TABLE_cell(_("1 / Taux indicatif"),/*colspan*/1,	/*rowspan*/2	));
      $table->add_cell(new TABLE_cell(_("1 / Taux achat"),	/*colspan*/2));
      $table->add_cell(new TABLE_cell(_("1 / Taux vente"),	/*colspan*/2));
    }
    if ($modifAutorisee) $table->add_cell(new TABLE_cell(""));
    $table->set_row_property("class","tablealternheader"); // Cas particulier car le header fait 2 lignes

    $table->add_cell(new TABLE_cell(_("Cash")));
    $table->add_cell(new TABLE_cell(_("Transfert")));
    $table->add_cell(new TABLE_cell(_("Cash")));
    $table->add_cell(new TABLE_cell(_("Transfert")));
    if ($modifAutorisee) $table->add_cell(new TABLE_cell(""));
    $table->set_row_property("class","tablealternheader"); // Cas particulier car le header fait 2 lignes
    $table->set_child_property("class",array("tablealternligneimpaire","tablealternlignepaire"));// Cas particulier car le header fait 2 lignes

    // Ligne de titres créée, création du reste du tableau
    foreach ($tDevise as $key=>$value) {
      if ($affNormal) {
        $taux		= affTx($value['taux']);
        $tauxAchatCash	= affTx($value['achatCash']);
        $tauxVenteCash	= affTx($value['venteCash']);
        $tauxAchatTrf	= affTx($value['achatTrf']);
        $tauxVenteTrf	= affTx($value['venteTrf']);
      } else {
        $taux		= affTx(1/$value['taux']);
        $tauxAchatCash	= affTx(1/$value['achatCash']);
        $tauxVenteCash	= affTx(1/$value['venteCash']);
        $tauxAchatTrf	= affTx(1/$value['achatTrf']);
        $tauxVenteTrf	= affTx(1/$value['venteTrf']);
      }

      $table->add_cell(new TABLE_cell($key)); 		// Code de la devise
      $table->add_cell(new TABLE_cell($value['libel']));	// Libellé de la devise
      $table->add_cell(new TABLE_cell($taux));
      $table->add_cell(new TABLE_cell($tauxAchatCash));
      $table->add_cell(new TABLE_cell($tauxAchatTrf));
      $table->add_cell(new TABLE_cell($tauxVenteCash));
      $table->add_cell(new TABLE_cell($tauxVenteTrf));

      if ($modifAutorisee)
        $table->add_cell(new TABLE_cell_link(_("Mod"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dev-3&id_dev=$key"));

      if ($key==$dataAgence['code_devise_reference'])
        $table->set_row_childs_property("color","red");
    }

    $MyPage->addHTMLExtraCode("tdev",$codeHtml);
    if ($affNormal) {
      $MyPage->addFormButton(1,1,'butAff', _('Afficher 1/taux'), TYPB_SUBMIT);
    } else {
      $MyPage->addFormButton(1,1,'butAff', _('Afficher Taux'), TYPB_SUBMIT);
    }
    $MyPage->setFormButtonProperties('butAff',BUTP_PROCHAIN_ECRAN,'Dev-1');
    $MyPage->addFormButton(1,2,'butAj',_('Ajouter une devise'), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties('butAj',BUTP_PROCHAIN_ECRAN,'Dev-2');
    $MyPage->setFormButtonProperties('butAj',BUTP_AXS,'275');
    $MyPage->addFormButton(1,3,'butPos', _('Position de change'), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties('butPos',BUTP_PROCHAIN_ECRAN,'Dev-4');
    $MyPage->setFormButtonProperties('butPos',BUTP_AXS,'277');
    $MyPage->addFormButton(2,2,'butRet',_('Retour Menu'), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties('butRet',BUTP_PROCHAIN_ECRAN,'Gen-12');
    $MyPage->buildHTML();
    echo $MyPage->getHTML();

  } else { // Il faut d'abord créer la devise de référence
    $MyPage = new HTML_GEN2(_("Gestion des Devises"));
    $codeHtml="<h2 align=center>"._("Veuillez introduire les données relatives à la devise de référence")."<br/><br/></h2>";
    $MyPage->addHTMLExtraCode("tdev",$codeHtml);
    $MyPage->addTable("devise", OPER_INCLUDE, array("code_devise","libel_devise", "precision"));
    $MyPage->addFormButton(1,1,'butOk',_('Valider'),TYPB_SUBMIT);
    $MyPage->setFormButtonProperties ('butOk',BUTP_PROCHAIN_ECRAN,'Dev-5');
    $MyPage->addFormButton(1,2,'butKo',_('Annuler'),TYPB_SUBMIT);
    $MyPage->setFormButtonProperties ('butKo',BUTP_PROCHAIN_ECRAN,'Gen-12');
    $MyPage->setFormButtonProperties ('butKo',BUTP_CHECK_FORM,false);
    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  }
}
// Ajout d'une nouvelle devise autre que la devise de référence
elseif ($global_nom_ecran == "Dev-2") {

  $MyPage=new HTML_GEN2(_("Ajout d'une devise"));
  $xtra1 = "<b>"._("Informations générales sur la devise")."</b>";
  $MyPage->addHTMLExtraCode("infosgen", $xtra1);
  $MyPage->setHTMLExtraCodeProperties("infosgen",HTMP_IN_TABLE, true);

  $MyPage->addTable("devise", OPER_NONE, array(""));
  $MyPage->addField("unSurTx",_("1 / Taux de change"),TYPC_FLT);
  $MyPage->addField("unSurAcCash",_("1 / Taux achat Cash"),TYPC_FLT);
  $MyPage->addField("unSurVeCash",_("1 / Taux vente Cash"),TYPC_FLT);
  $MyPage->addField("unSurAcTrf",_("1 / Taux achat Transfert"),TYPC_FLT);
  $MyPage->addField("unSurVeTrf",_("1 / Taux vente Transfert"),TYPC_FLT);
  $MyPage->addField("pourcent",_("Pourcentage d'écart (%)"),TYPC_FLT);
//$MyPage->addLink("pourcent","calcul",_("Calculer"),"#");
  $MyPage->setOrder(NULL,array("infosgen","code_devise","libel_devise","precision","taux_indicatif","unSurTx","pourcent","taux_achat_cash","unSurAcCash","taux_vente_cash","unSurVeCash","taux_achat_trf","unSurAcTrf","taux_vente_trf","unSurVeTrf","cpte_produit_commission","cpte_produit_taux", "cpte_perte_taux"));
  $MyPage->setFieldProperties("code_devise", FIELDP_JS_EVENT, array("onChange"=>"verifDeviseDoublon();"));
  $MyPage->setFieldProperties("code_devise",FIELDP_WIDTH,3);
  $MyPage->setFieldProperties("taux_indicatif", FIELDP_JS_EVENT, array("onChange"=>"modifChange();"));
  $MyPage->setFieldProperties("unSurTx", FIELDP_JS_EVENT, array("onChange"=>"modifUnSurChange();"));
  $MyPage->setFieldProperties("pourcent", FIELDP_JS_EVENT, array("onChange"=>"modifPourcent();"));
  $MyPage->setFieldProperties("taux_achat_cash", FIELDP_JS_EVENT, array("onChange"=>"modifAchatCash();"));
  $MyPage->setFieldProperties("unSurAcCash", FIELDP_JS_EVENT, array("onChange"=>"modifUnSurAchatCash();"));
  $MyPage->setFieldProperties("taux_vente_cash", FIELDP_JS_EVENT, array("onChange"=>"modifVenteCash();"));
  $MyPage->setFieldProperties("unSurVeCash", FIELDP_JS_EVENT, array("onChange"=>"modifUnSurVenteCash();"));
  $MyPage->setFieldProperties("taux_achat_trf", FIELDP_JS_EVENT, array("onChange"=>"modifAchatTrf();"));
  $MyPage->setFieldProperties("unSurAcTrf", FIELDP_JS_EVENT, array("onChange"=>"modifUnSurAchatTrf();"));
  $MyPage->setFieldProperties("taux_vente_trf", FIELDP_JS_EVENT, array("onChange"=>"modifVenteTrf();"));
  $MyPage->setFieldProperties("unSurVeTrf", FIELDP_JS_EVENT, array("onChange"=>"modifUnSurVenteTrf();"));

  // Filtrage des comptes de produits sur commission et de produit sur jeu sur taux
  $include_prod = getNomsComptesComptables(	array("compart_cpte"  => 4));
  $include_char = getNomsComptesComptables(	array("compart_cpte"  => 3));
  $MyPage->setFieldProperties("cpte_produit_commission", FIELDP_INCLUDE_CHOICES, array_keys($include_prod));
  $MyPage->setFieldProperties("cpte_produit_taux", FIELDP_INCLUDE_CHOICES, array_keys($include_prod));
  $MyPage->setFieldProperties("cpte_perte_taux", FIELDP_INCLUDE_CHOICES, array_keys($include_char));

  if ($global_multidevise == false) { // On ajoute la seconde devise, on oblige l'utilisateur à définir les comptes Pos de Ch, C/V Pos de Ch, Variation Taux déb et variation taux créd
    $xtra2 = "<b>"._("Définition des comptes comptables de position de change (passage en mode multidevise)")."</b>";
    $MyPage->addHTMLExtraCode("infosposch", $xtra2);
    $MyPage->setHTMLExtraCodeProperties("infosposch",HTMP_IN_TABLE, true);
    $MyPage->addTable("ad_agc", OPER_INCLUDE, array("cpte_position_change", "cpte_contreval_position_change", "cpte_variation_taux_deb", "cpte_variation_taux_cred"));
    // Position de change : Compte passif mixte
    $include = getNomsComptesComptables(array("compart_cpte"  => 2, "sens_cpte" => 3));
    $MyPage->setFieldProperties("cpte_position_change",FIELDP_INCLUDE_CHOICES, array_keys($include));
    // CV Position de change : Compte d'actif mixte
    $include = getNomsComptesComptables(array("compart_cpte"  => 1, "sens_cpte" => 3));
    $MyPage->setFieldProperties("cpte_contreval_position_change",FIELDP_INCLUDE_CHOICES, array_keys($include));
    // Compte de variation taux débiteur : Compte d'actif mixte
    $include = getNomsComptesComptables(array("compart_cpte"  => 1, "sens_cpte" => 3));
    $MyPage->setFieldProperties("cpte_variation_taux_deb",FIELDP_INCLUDE_CHOICES, array_keys($include));
    // Compte de variation taux créditeur : Compte passif mixte
    $include = getNomsComptesComptables(array("compart_cpte"  => 2, "sens_cpte" => 3));
    $MyPage->setFieldProperties("cpte_variation_taux_cred",FIELDP_INCLUDE_CHOICES, array_keys($include));
  }

  $MyPage->addJS(JSP_FORM, "JS1", $codeJS);
  $MyPage->addFormButton(1,1,'butOk',_('Valider'),TYPB_SUBMIT);
  $MyPage->setFormButtonProperties ('butOk',BUTP_PROCHAIN_ECRAN,'Dev-5');
  $MyPage->addFormButton(1,2,'butKo',_('Annuler'),TYPB_SUBMIT);
  $MyPage->setFormButtonProperties ('butKo',BUTP_PROCHAIN_ECRAN,'Dev-1');
  $MyPage->setFormButtonProperties ('butKo',BUTP_CHECK_FORM,false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
//Modification d'une devise.
elseif ($global_nom_ecran == "Dev-3") {
  $SESSION_VARS["code_devise"] = $id_dev;
  $infoDevise=getInfoDevise($_GET['id_dev']);
  $MyPage=new HTML_GEN2(_("Modification d'une devise"));
  if ($id_dev == $dataAgence['code_devise_reference']) { // On modifie lma devise de référence
    $MyPage->addTable("devise", OPER_INCLUDE, array("code_devise", "libel_devise", "precision", "cpte_produit_commission", "cpte_produit_taux", "cpte_perte_taux"));
    $MyPage->setOrder(NULL,array("code_devise","libel_devise","precision"));
    $MyPage->setFieldProperties("code_devise",FIELDP_DEFAULT,$infoDevise['code_devise']);
    $MyPage->setFieldProperties("libel_devise",FIELDP_DEFAULT,$infoDevise['libel_devise']);
    $MyPage->setFieldProperties("precision",FIELDP_DEFAULT,$infoDevise['precision']);
    $MyPage->setFieldProperties("cpte_produit_commission",FIELDP_DEFAULT,$infoDevise['cpte_produit_commission']);
    $MyPage->setFieldProperties("cpte_produit_taux",FIELDP_DEFAULT,$infoDevise['cpte_produit_taux']);
    $MyPage->setFieldProperties("cpte_perte_taux",FIELDP_DEFAULT,$infoDevise['cpte_perte_taux']);
  } else { // On modifie une autre devise que la devise de référence
    $MyPage->addTable("devise", OPER_NONE, array(""));
    $MyPage->addField("unSurTx",_("1 / Taux de change"),TYPC_FLT);
    $MyPage->addField("unSurAcCash",_("1 / Taux achat Cash"),TYPC_FLT);
    $MyPage->addField("unSurVeCash",_("1 / Taux vente Cash"),TYPC_FLT);
    $MyPage->addField("unSurAcTrf",_("1 / Taux achat Transfert"),TYPC_FLT);
    $MyPage->addField("unSurVeTrf",_("1 / Taux vente Transfert"),TYPC_FLT);
    $MyPage->addField("pourcent",_("Pourcentage d'écart"),TYPC_FLT);
    $MyPage->setOrder(NULL,array("code_devise","libel_devise","precision","taux_indicatif","unSurTx","pourcent","taux_achat_cash","unSurAcCash","taux_vente_cash","unSurVeCash","taux_achat_trf", "unSurAcTrf","taux_vente_trf", "unSurVeTrf","cpte_produit_commission","cpte_produit_taux","cpte_perte_taux"));
    foreach ($infoDevise as $key=>$value) {
      if ($key!='id_ag')
        $MyPage->setFieldProperties($key,FIELDP_DEFAULT,$value);
    }
    $MyPage->setFieldProperties("code_devise",FIELDP_WIDTH,3);
    $MyPage->setFieldProperties("taux_indicatif", FIELDP_JS_EVENT, array("onChange"=>"modifChange();"));
    $MyPage->setFieldProperties("unSurTx", FIELDP_JS_EVENT, array("onChange"=>"modifUnSurChange();"));
    $MyPage->setFieldProperties("pourcent", FIELDP_JS_EVENT, array("onChange"=>"modifPourcent();"));
    $MyPage->setFieldProperties("taux_achat_cash", FIELDP_JS_EVENT, array("onChange"=>"modifAchatCash();"));
    $MyPage->setFieldProperties("unSurAcCash", FIELDP_JS_EVENT, array("onChange"=>"modifUnSurAchatCash();"));
    $MyPage->setFieldProperties("taux_vente_cash", FIELDP_JS_EVENT, array("onChange"=>"modifVenteCash();"));
    $MyPage->setFieldProperties("unSurVeCash", FIELDP_JS_EVENT, array("onChange"=>"modifUnSurVenteCash();"));
    $MyPage->setFieldProperties("taux_achat_trf", FIELDP_JS_EVENT, array("onChange"=>"modifAchatTrf();"));
    $MyPage->setFieldProperties("unSurAcTrf", FIELDP_JS_EVENT, array("onChange"=>"modifUnSurAchatTrf();"));
    $MyPage->setFieldProperties("taux_vente_trf", FIELDP_JS_EVENT, array("onChange"=>"modifVenteTrf();"));
    $MyPage->setFieldProperties("unSurVeTrf", FIELDP_JS_EVENT, array("onChange"=>"modifUnSurVenteTrf();"));

  }

  // Restriction du choix des comptes de produits comm et marge taux au compartiment des produits
  $include_prod = getNomsComptesComptables(array("compart_cpte"  => 4));
  $include_char = getNomsComptesComptables(array("compart_cpte"  => 3));
  $MyPage->setFieldProperties("cpte_produit_commission", FIELDP_INCLUDE_CHOICES, array_keys($include_prod));
  $MyPage->setFieldProperties("cpte_produit_taux", FIELDP_INCLUDE_CHOICES, array_keys($include_prod));
  $MyPage->setFieldProperties("cpte_perte_taux", FIELDP_INCLUDE_CHOICES, array_keys($include_char));

  $MyPage->setFieldProperties("code_devise",FIELDP_IS_LABEL,true);
  $MyPage->addJS(JSP_FORM, "JS1", $codeJS);
  $MyPage->addFormButton(1,1,'butOk',_('Valider'),TYPB_SUBMIT);
  $MyPage->setFormButtonProperties ('butOk',BUTP_PROCHAIN_ECRAN,'Dev-6');
  $MyPage->addFormButton(1,2,'butKo',_('Annuler'),TYPB_SUBMIT);
  $MyPage->setFormButtonProperties ('butKo',BUTP_PROCHAIN_ECRAN,'Dev-1');
  $MyPage->setFormButtonProperties ('butKo',BUTP_CHECK_FORM,false);
  $MyPage->addHiddenType('id_devise',$_GET['id_dev']);
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
// Affichage des positions de change de toutes les devises
elseif ($global_nom_ecran == "Dev-4") {
  $MyPage = new HTML_GEN2(_("Position de change"));
  $boutonTaux=_('Afficher 1/taux');
  $boutonImp=_('Imprimer');
  if (isset($_POST['butAff'])) {
    if ($_POST['butAff'] == $boutonTaux) {
      $affNormal=false;
    } else {
      $affNormal=true;
    }
  } else {
    $affNormal=true;
  }
  $color=$colb_tableau;
  $codeHtml="<table border=\"1\" align=\"center\" cellpadding=\"5\" width=\"95%\">";
  if ($affNormal) {
    $codeHtml.="<tr bgcolor=\"$color\"><th>"._("Code")."</th><th>"._("Libellé")."</th><th>"._("Pos. Nette")."</th><th>"._("Taux jour")."</th><th>"._("C/V en ").$dataAgence['code_devise_reference']."</th><th>"._("Var. Tx")."</th><th>"._("Taux moyen")."</th></tr>";
  } else {
    $codeHtml.="<tr bgcolor=\"$color\"><th>"._("Code")."</th><th>"._("Libellé")."</th><th>"._("Pos. Nette")."</th><th>"._("1 / Taux jour")."</th><th>"._("C/V en ").$dataAgence['code_devise_reference']."</th><th>"._("Var. Tx")."</th><th>"._("1 / Taux moyen")."</th></tr>";
  }
  unset($tDevise[$dataAgence['code_devise_reference']]);
  reset($tDevise);
  $DATA=array();

  foreach ($tDevise as $key => $value) {
    $infoPosition=getInfoPosition($key);
    $color=($color==$colb_tableau?$colb_tableau_altern:$colb_tableau);
    $codeHtml.="<tr bgcolor=\"$color\"><td>$key</td>";
    $codeHtml.="<td>".$value['libel']."</td>";
    if (($infoPosition['cv']-$infoPosition['varTx'])!=0) {
      $tauxMoyen=$infoPosition['position']/($infoPosition['cv']-$infoPosition['varTx']);
      if (!$affNormal) {
        $value['taux']=1/$value['taux'];
        $tauxMoyen=1/$tauxMoyen;
      }
      setMonnaieCourante($key);
      $codeHtml.="<td>".afficheMontant($infoPosition['position'])."</td>";
      $codeHtml.="<td>".affTx($value['taux'])."</td>";
      setMonnaieCourante($dataAgence['code_devise_reference']);
      $codeHtml.="<td>".afficheMontant($infoPosition['cv'])."</td>";
      $codeHtml.="<td>".afficheMontant($infoPosition['varTx'])."</td>";
      $codeHtml.="<td>".affTx($tauxMoyen)."</td>";
    } else {
      if (!$affNormal) {
        $value['taux']=1/$value['taux'];
      }
      $codeHtml.="<td>0</td>";
      $codeHtml.="<td>".affTx($value['taux'])."</td>";
      $codeHtml.="<td>0</td>";
      $codeHtml.="<td>0</td>";
      $codeHtml.="<td>0</td>";
    }
    $codeHtml.="</tr>";

    $DATA[$key]["libel"]=$value['libel'];
    $DATA[$key]["pos_net"]=$infoPosition['position'];
    $DATA[$key]["taux_jour"]= $value['taux'];
    $DATA[$key]["cv"]=$infoPosition['cv'];
    $DATA[$key]["var_taux"]=$infoPosition['varTx'];
    $DATA[$key]["taux_moyen"]=$tauxMoyen;
  }
  $codeHtml.="</table><br /><br/>";
  $SESSION_VARS["devise"]=$DATA ;
  $MyPage->addHTMLExtraCode("tdev",$codeHtml);
  if ($affNormal) {
    $MyPage->addFormButton(1,1,'butAff', $boutonTaux, TYPB_SUBMIT);
  } else {
    $MyPage->addFormButton(1,1,'butAff', _('Afficher Taux'), TYPB_SUBMIT);
  }
  $MyPage->addFormButton(1,2,'butRet',_('Retour'), TYPB_SUBMIT);
  $MyPage->addFormButton(2,1,'butImp',$boutonImp, TYPB_SUBMIT);
  $MyPage->setFormButtonProperties('butRet',BUTP_PROCHAIN_ECRAN,'Dev-1');
  $MyPage->setFormButtonProperties('butAff',BUTP_PROCHAIN_ECRAN,'Dev-4');
  $MyPage->setFormButtonProperties('butImp',BUTP_PROCHAIN_ECRAN,'Dev-7');
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
//Confirmation de la sauvegarde de la nouvelle devise
elseif ($global_nom_ecran == "Dev-5") {

  // Extraction des champs qui seront insérés dans la DB
  $DATAS = $_POST;
  unset($DATAS["unSurTx"]);
  unset($DATAS["pourcent"]);
  unset($DATAS["unSurAcCash"]);
  unset($DATAS["unSurVeCash"]);
  unset($DATAS["unSurAcTrf"]);
  unset($DATAS["unSurVeTrf"]);
  unset($DATAS["butOk"]);
  unset($DATAS["prochain_ecran"]);
  unset($DATAS["java_enabled"]);
  unset($DATAS["m_agc"]);
  
  $myErr = insertDevise($DATAS);
  if ($myErr->errCode == NO_ERR) {
    $html_msg = new HTML_message (_("Confirmation de l'ajout d'une devise"));
    $html_msg->setMessage (sprintf(_("La devise %s a bien été ajoutée"), $code_devise));
    $html_msg->addButton ("BUTTON_OK", "Dev-1");
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  } else {
    $html_err = new HTML_erreur(_("Echec de l'ajout d'une devise."));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode]);
    $html_err->addButton("BUTTON_OK", 'Dev-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
}
//Confirmation de la sauvegarde des modifications d'une ancienne devise
elseif ($global_nom_ecran == "Dev-6") {

  // Extraction des champs qui seront insérés dans la DB
  $DATAS = $_POST;
  unset($DATAS["unSurTx"]);
  unset($DATAS["pourcent"]);
  unset($DATAS["unSurAcCash"]);
  unset($DATAS["unSurVeCash"]);
  unset($DATAS["unSurAcTrf"]);
  unset($DATAS["unSurVeTrf"]);
  unset($DATAS["butOk"]);
  unset($DATAS["prochain_ecran"]);
  unset($DATAS["java_enabled"]);
  unset($DATAS["id_devise"]);
  unset($DATAS["m_agc"]);

  $erreur = updateDevise($SESSION_VARS["code_devise"], $DATAS);
  if ($erreur->errCode != NO_ERR) {
    global $error;
    $html_err = new HTML_erreur(_("Echec de la mise à jour de la devise.")." ");
    $html_err->setMessage(_("Erreur")." : ".$error[$erreur->errCode]."<br />"._("Paramètre")." : ".$erreur->param);
    $html_err->addButton("BUTTON_OK", 'Dev-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $html_msg = new HTML_message (_("Confirmation de la modification d'une devise"));
    $html_msg->setMessage (sprintf(_("La devise %s a bien été modifiée"),$_SESSION['devise']));
    $html_msg->addButton ("BUTTON_OK", "Dev-1");
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
}
elseif ($global_nom_ecran == "Dev-7") {
  $xml = xml_position_change($SESSION_VARS["devise"]);

  //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
  $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'position_de_change.xslt');

  //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
  echo get_show_pdf_html("Dev-4", $fichier_pdf);
}
?>