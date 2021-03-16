<?php

// Gestion des Net bank

require_once 'lib/dbProcedures/net_bank.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'modules/rapports/xml_devise.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
//require_once 'batch/checkNewNetBankMessage.php';


if ($global_nom_ecran == "Swi-1") {
  unset($SESSION_VARS["swift"]["type"]);
  unset($SESSION_VARS["swift"]["num_cpte_do"]);
  unset($SESSION_VARS["swift"]["num_cpte_ben"]);
  unset($SESSION_VARS["id_ben"]);

  $MyPage = new HTML_GEN2(_("Choix critères d'affichage des messages"));

  /* Type de message : Domestiques ou étrangers */
  $type_mess = array("1"=>_("Domestiques"), "2"=>_("Etrangers"));
  $MyPage->addField("type",_("Type de message"), TYPC_LSB);
  $MyPage->setFieldProperties("type", FIELDP_ADD_CHOICES, $type_mess);
  $MyPage->setFieldProperties("type", FIELDP_HAS_CHOICE_AUCUN, false);
  $MyPage->setFieldProperties("type", FIELDP_IS_REQUIRED, true);

  /* Liste des statuts de message SWIFT */
  $liste = $adsys["adsys_statut_message_swif"];
  $liste[-1] ="Tous";
  $MyPage->addField("statut",_("Statut message"), TYPC_LSB);
  $MyPage->setFieldProperties("statut", FIELDP_ADD_CHOICES, $liste);
  $MyPage->setFieldProperties("statut", FIELDP_HAS_CHOICE_AUCUN, false);

  /* Date de début de réception des messages SWIFT */
  $MyPage->addField("DateDeb", _("Date de début")." :", TYPC_DTE);
  $MyPage->setFieldProperties("DateDeb", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("DateDeb", FIELDP_DEFAULT, date("d/m/Y", mktime(0,0,0,1,1,date("Y"))));

  /* Date de fin de réception des messages SWIFT */
  $MyPage->addField("DateFin", _("Date de fin")." :", TYPC_DTE);
  $MyPage->setFieldProperties("DateFin", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("DateFin", FIELDP_DEFAULT, date("d/m/Y"));

  /* Boutons */
  $MyPage->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Swi-2");
  $MyPage->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-6");
  $MyPage->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
  $MyPage->buildHTML();
  echo $MyPage->getHTML();

}
if ($global_nom_ecran == "Swi-2") {
  $SESSION_VARS["swift"]["type"] = $type ;

  /* Récupération des messages correspondant aux critères de sélection */
  $param = array();
  if ($statut != -1)
    $param["statut"] =  $statut;
  $param["DateDeb"] = $DateDeb;
  $param["DateFin"] = $DateFin;
  if ($type == 1) // Messages domestiques
    $messages = getMessagesSwiftDomestiques($param);
  else if ($type == 2) //Messages étrangers
    $messages = getMessagesSwiftEtrangers($param);

  /* Si des messages répondent aux critères */
  if (!empty($messages)) {
    /* Création d'un tableau contenant les messages trouvés  */
    $MyPage = new HTML_GEN2(_("Liste des messages")." : ".adb_gettext($adsys["adsys_statut_message_swif"][$statut]));

    $table =& $MyPage->addHTMLTable('tabledevises', 8 /*nbre colonnes*/, TABLE_STYLE_ALTERN);

    /* En-tête du tableau */
    $table->add_cell(new TABLE_cell(_("ID"),1, 1 ));
    $table->add_cell(new TABLE_cell(_("Type"),1, 1 ));
    $table->add_cell(new TABLE_cell(_("Nom donneur d'ordre"), 1, 1 ));
    $table->add_cell(new TABLE_cell(_("Compte donneur d'ordre"), 1, 1 ));
    $table->add_cell(new TABLE_cell(_("Nom bénéficiaire"), 1, 1 ));
    $table->add_cell(new TABLE_cell(_("Montant"), 1, 1 ));
    $table->add_cell(new TABLE_cell(_("Devise"), 1, 1 ));
    $table->add_cell(new TABLE_cell(_("Statut actuel"), 1, 1));

    /* Contenu du tableau */
    foreach($messages as $key=>$value) {
      $table->add_cell(new TABLE_cell_link($key,"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Swi-3&id=$key"));

      if ($type == 1)
        $table->add_cell(new TABLE_cell("Domestique"));
      else if ($type == 2)
        $table->add_cell(new TABLE_cell("Etranger"));

      $table->add_cell(new TABLE_cell($value['nom_do']));	// Libellé de la devise
      $table->add_cell(new TABLE_cell($value['num_cpte_do']));
      $table->add_cell(new TABLE_cell($value['nom_ben']));
      $table->add_cell(new TABLE_cell(afficheMontant($value['montant'],false)));
      $table->add_cell(new TABLE_cell($value['devise']));
      $table->add_cell(new TABLE_cell($value['statut']));
    }

    $MyPage->addHTMLExtraCode("tdev",$codeHtml);

    /* Boutons */
    $MyPage->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Swi-1");
    $MyPage->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Swi-1");
    $MyPage->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
    $MyPage->buildHTML();
    echo $MyPage->getHTML();

  } else { /* Aucun message ne correspond aux critères */
    $html_err = new HTML_erreur(_("Echec de la sélection "));
    $html_err->setMessage(_("Aucun message ne correspond aux critères de sélection"));
    $html_err->addButton("BUTTON_OK", 'Swi-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }

}
if ($global_nom_ecran == "Swi-3") {
  $MyPage = new HTML_GEN2(_("Détail du message"));

  unset( $SESSION_VARS["id_ben"]);

  /* Récupération des informations du message */
  $param = array();
  $param["id_message"] =  $id;
  if ($SESSION_VARS["swift"]["type"] == 1)
    $message = getMessagesSwiftDomestiques($param);
  else if ($SESSION_VARS["swift"]["type"] == 2)
    $message = getMessagesSwiftEtrangers($param);

  /* Information non modifiable */
  $SESSION_VARS["swift"]["id_message"] = $id;
  $SESSION_VARS["swift"]["devise"] = $message[$id]["devise"];
  $SESSION_VARS["swift"]["montant"] = $message[$id]["montant"];
  $SESSION_VARS["swift"]["communication"] = $message[$id]["comm_1"]."\n".$message[$id]["comm_2"];

  /* Informatiion modifiable */
  $MyPage->addField("id",_("Identifiant message"), TYPC_TXT);
  $MyPage->setFieldProperties("id", FIELDP_DEFAULT,$message[$id]["id_message"]);
  $MyPage->setFieldProperties("id", FIELDP_IS_LABEL, true);
  $SESSION_VARS["id_message"]= $id;

  /* Le type de message*/
  $MyPage->addField("type",_("Type"), TYPC_TXT);
  if ($SESSION_VARS["swift"]["type"] == 1)
    $MyPage->setFieldProperties("type", FIELDP_DEFAULT, _("Domestique"));
  else if ($SESSION_VARS["swift"]["type"] == 2)
    $MyPage->setFieldProperties("type", FIELDP_DEFAULT, _("Etranger"));
  $MyPage->setFieldProperties("type", FIELDP_IS_LABEL, true);

  /* Mesage d'erreur */
  $MyPage->addField("mess_err",_("Message d'erreur"), TYPC_TXT);
  $MyPage->setFieldProperties("mess_err", FIELDP_DEFAULT, $message[$id]["message_erreur"]);

  /* Compte du donneur d'ordre */
  $MyPage->addField("cpte_don",_("Compte donneur d'ordre"), TYPC_TXT);
  $MyPage->setFieldProperties("cpte_don", FIELDP_DEFAULT, $message[$id]["num_cpte_do"]);
  $MyPage->addLink("cpte_don", "rechercher", _("Rechercher"), "#");
  $str = "if (document.ADForm.cpte_don.disabled == false) OpenBrw('../modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=1&cpt_dest=cpte_don&id_cpt_dest=id_cpte_don', '"._("Recherche")."');return false;";
  $MyPage->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => $str));
  $MyPage->addHiddenType("id_cpte_don", "");

  /* Compte du bénéficiaire client */
  $MyPage->addField("cpte_ben",_("Compte bénéficiaire client"), TYPC_TXT);
  $MyPage->addLink("cpte_ben", "rechercher1", _("Rechercher"), "#");
  $str1 = "if (document.ADForm.cpte_ben.disabled == false) OpenBrw('../modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=1&cpt_dest=cpte_ben&id_cpt_dest=id_cpte_ben', '"._("Recherche")."');return false;";
  $MyPage->setLinkProperties("rechercher1", LINKP_JS_EVENT, array("onclick" => $str1));
  $MyPage->addHiddenType("id_cpte_ben", "");

  /* Nom (Compte) du bénéficiaire non client */
  $MyPage->addField("nom_ben",_("Nom bénéficiaire non client"), TYPC_TXT);
  //$MyPage->setFieldProperties("nom_ben", FIELDP_IS_REQUIRED, true);
  $MyPage->addLink("nom_ben", "rechercher2", _("Rechercher"), "#");
  $MyPage->setLinkProperties("rechercher2", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/externe/rech_benef.php?m_agc=".$_REQUEST['m_agc']."&field_name=nom_ben&field_id=id_ben&type=b', '"._("Recherche")."');return false;"));

  /* Vérifie si le compte du bénéficiaire est un compte client */
  $res = isBenefClient(trim($message[$id]["num_cpte_ben"]));

  if ($res) // si le bénéficiaire est un client
    $MyPage->setFieldProperties("cpte_ben", FIELDP_DEFAULT, $message[$id]["num_cpte_ben"]);
  else { // le bénéficiaire n'est pas un client
    /* Vérifie si c'est un  tireur bénéficiaire  */
    $benef = isBenefTireur(trim($message[$id]["num_cpte_ben"]));
    if ($benef != NULL) {
      $MyPage->setFieldProperties("nom_ben", FIELDP_DEFAULT, $benef["denomination"]);
      $SESSION_VARS["id_ben"] =  $benef["id"];
    }
  }

  $MyPage->addHiddenType("id_ben", $SESSION_VARS["id_ben"]);

  $listeCorrespondant=getLibelCorrespondant();
  $MyPage->addField("id_cor", _("Correspondant bancaire"), TYPC_LSB);
  $MyPage->setFieldProperties("id_cor", FIELDP_ADD_CHOICES , $listeCorrespondant);
  //$MyPage->setFieldProperties("id_cor", FIELDP_IS_REQUIRED, true);

  $MyPage->addField("montant",_("Montant"), TYPC_TXT);
  $MyPage->setFieldProperties("montant", FIELDP_DEFAULT, afficheMontant($message[$id]["montant"],true));
  $MyPage->setFieldProperties("montant", FIELDP_IS_LABEL, true);

  $MyPage->addField("devise",_("Devise"), TYPC_TXT);
  $MyPage->setFieldProperties("devise", FIELDP_DEFAULT, $message[$id]["devise"]);
  $MyPage->setFieldProperties("devise", FIELDP_IS_LABEL, true);

  /* Cette information n'est pas stockée ds la table swift, donc n'a pas de valeur par défaut et doit être saisie */
  $MyPage->addField("remarque",_("Remarque"), TYPC_TXT);

  $liste =  $adsys["adsys_statut_message_swif"];
  $MyPage->addField("statut",_("Statut"), TYPC_LSB);
  $MyPage->setFieldProperties("statut", FIELDP_ADD_CHOICES, $liste);
  $MyPage->setFieldProperties("statut", FIELDP_DEFAULT, $message[$id]["statut"]);
  $MyPage->setFieldProperties("statut", FIELDP_HAS_CHOICE_AUCUN, false);

  /* On ne peut pas modifier le messages si le statut est 3 (accepté) ou 4(refusé) */
  if ($message[$id]["statut"] == 3 or $message[$id]["statut"] == 4) {
    $MyPage->setFieldProperties("mess_err", FIELDP_IS_LABEL, true);
    $MyPage->setFieldProperties("cpte_don", FIELDP_IS_LABEL, true);
    $MyPage->setFieldProperties("cpte_ben", FIELDP_IS_LABEL, true);
    $MyPage->setFieldProperties("nom_ben", FIELDP_IS_LABEL, true);
    $MyPage->setFieldProperties("id_cor", FIELDP_IS_LABEL, true);
    $MyPage->setFieldProperties("remarque", FIELDP_IS_LABEL, true);
    $MyPage->setFieldProperties("statut", FIELDP_IS_LABEL, true);

    /* Boutons */
    $MyPage->addFormButton(1,1,"retour", _("Retour"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Swi-1");
  } else {
    /* Boutons */
    $MyPage->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Swi-4");
    $MyPage->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Swi-1");
    $MyPage->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
  }

  $MyPage->buildHTML();
  echo $MyPage->getHTML();

} else if ($global_nom_ecran == "Swi-4") { /* Ecran de confirmation de la saisie */
  $MyPage = new HTML_GEN2(_("Confirmation de la saise"));

  /* Récupération de la saisie   */
  $SESSION_VARS["swift"]["message_erreur"] = $mess_err;
  $SESSION_VARS["swift"]["num_cpte_do"] = $cpte_don;
  $SESSION_VARS["swift"]["statut"] = $statut;
  $SESSION_VARS["swift"]["id_correspondant"]= $id_cor;
  $SESSION_VARS["swift"]["remarque"]= $remarque;

  $MyPage->addField("id",_("Identifiant message"), TYPC_TXT);
  $MyPage->setFieldProperties("id", FIELDP_DEFAULT,$SESSION_VARS["swift"]["id_message"]);
  $MyPage->setFieldProperties("id", FIELDP_IS_LABEL, true);

  $MyPage->addField("type",_("Type"), TYPC_TXT);
  if ($SESSION_VARS["swift"]["type"] == 1)
    $MyPage->setFieldProperties("type", FIELDP_DEFAULT,_("Domestique"));
  else if ($SESSION_VARS["swift"]["type"] == 2)
    $MyPage->setFieldProperties("type", FIELDP_DEFAULT,_("Etranger"));

  $MyPage->setFieldProperties("type", FIELDP_IS_LABEL, true);

  $MyPage->addField("mess_err",_("Message d'erreur"), TYPC_TXT);
  $MyPage->setFieldProperties("mess_err", FIELDP_DEFAULT, $mess_err);
  $MyPage->setFieldProperties("mess_err", FIELDP_IS_LABEL, true);

  $MyPage->addField("cpte_don",_("Compte donneur d'ordre"), TYPC_TXT);
  $MyPage->setFieldProperties("cpte_don", FIELDP_DEFAULT, $cpte_don);
  $MyPage->setFieldProperties("cpte_don", FIELDP_IS_LABEL, true);

  /* Récupération du id du tireur bénéficiaire */
  if (isset($id_ben) and $id_ben != '')
    $SESSION_VARS["id_ben"] =  $id_ben;

  /* Vérifie d'abord si le bénéficiaire est un client  */
  $cpte_ben = trim(str_replace('-','',$cpte_ben));
  $res = isBenefClient(trim($cpte_ben));

  if ($res) { // si c'est un bénéficiaire client
    $MyPage->addField("cpte_ben",_("Compte bénéficiaire client"), TYPC_TXT);
    $MyPage->setFieldProperties("cpte_ben", FIELDP_DEFAULT, $cpte_ben);
    $MyPage->setFieldProperties("cpte_ben", FIELDP_IS_LABEL, true);
    $SESSION_VARS["swift"]["num_cpte_ben"] = $cpte_ben;
  } else // si ce n'est pas un bénéficiaire client
    if ( isset($SESSION_VARS["id_ben"]) and $SESSION_VARS["id_ben"]!='') { // si c'est un tireur bénéficiaire
      $DATA = getTireurBenefDatas($SESSION_VARS["id_ben"]);
      $cpte_ben = trim($DATA["num_cpte"]);

      $MyPage->addField("nom_ben",_("Nom bénéficiaire non client"), TYPC_TXT);
      $MyPage->setFieldProperties("nom_ben", FIELDP_DEFAULT, $nom_ben);
      $MyPage->setFieldProperties("nom_ben", FIELDP_IS_LABEL, true);
      $SESSION_VARS["swift"]["num_cpte_ben"] = $cpte_ben;

    }

  $MyPage->addField("montant",_("Montant"), TYPC_TXT);
  $MyPage->setFieldProperties("montant", FIELDP_DEFAULT, afficheMontant($SESSION_VARS["swift"]["montant"],true));
  $MyPage->setFieldProperties("montant", FIELDP_IS_LABEL, true);

  $MyPage->addField("devise",_("Devise"), TYPC_TXT);
  $MyPage->setFieldProperties("devise", FIELDP_DEFAULT, $SESSION_VARS["swift"]["devise"]);
  $MyPage->setFieldProperties("devise", FIELDP_IS_LABEL, true);

  $MyPage->addField("corres",_("Correspondant bancaire"), TYPC_TXT);
  $MyPage->setFieldProperties("corres", FIELDP_DEFAULT, "");
  $MyPage->setFieldProperties("corres", FIELDP_IS_LABEL, true);

  $stat = adb_gettext($adsys["adsys_statut_message_swif"][$statut]);
  $MyPage->addField("statut",_("Statut"), TYPC_TXT);
  $MyPage->setFieldProperties("statut", FIELDP_DEFAULT, $stat);
  $MyPage->setFieldProperties("statut", FIELDP_IS_LABEL, true);

  /* Boutons */
  $MyPage->addFormButton(1,1,"modifier", _("Modifier"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("modifier", BUTP_PROCHAIN_ECRAN, "Swi-5");

  if ($statut==1) { // on peut passer au paiement
    $MyPage->addFormButton(1,2,"payer", _("Payer"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("payer", BUTP_PROCHAIN_ECRAN, "Swi-6");
  }

  $MyPage->addFormButton(1,3,"annul", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Swi-1");
  $MyPage->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
} else if ($global_nom_ecran == "Swi-5") {
  $id = $SESSION_VARS["swift"]["id_message"];
  $cpte_don = str_replace('-','', $SESSION_VARS["swift"]["num_cpte_do"]);
  $cpte_ben = $SESSION_VARS["swift"]["num_cpte_ben"];
  $mess_err = $SESSION_VARS["swift"]["message_erreur"];
  $statut = $SESSION_VARS["swift"]["statut"];

  if ($SESSION_VARS["swift"]["type"] == 1)
    $myErr = updateSwiftDomestique($id, $statut, $mess_err,$cpte_don,$cpte_ben);
  else
    $myErr = updateSwiftEtranger($id, $statut, $mess_err,$cpte_don,$cpte_ben);

  if ($myErr->errCode == NO_ERR) {
    //HTML
    $MyPage = new HTML_message(_("Confirmation mise à jour message"));
    $MyPage->setMessage(_("Le message a été mis à jour avec succès !"));
    $MyPage->addButton(BUTTON_OK, "Swi-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else {
    $html_err = new HTML_erreur(_("Echec modification"));
    $html_err->setMessage("Erreur : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Swi-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
} else if ($global_nom_ecran == "Swi-6") { /* Traitement de l'ordre de paiement */
  $id = $SESSION_VARS["swift"]["id_message"];
  $cpte_don = str_replace('-','', $SESSION_VARS["swift"]["num_cpte_do"]);
  $cpte_ben = $SESSION_VARS["swift"]["num_cpte_ben"];
  $mess_err = $SESSION_VARS["swift"]["message_erreur"];

  /* Changer le statut à executer   */
  $statut = 3;

  /* Enregistrer les données avec lesquelles on effectue la transaction */
  $DATA_SWIFT["id"] = $id;
  $DATA_SWIFT["type"] = $SESSION_VARS["swift"]["type"];
  $DATA_SWIFT["statut"] = $statut;
  $DATA_SWIFT["$mess_err"] = $mess_err ;
  $DATA_SWIFT["cpte_don"] = $cpte_don;
  $DATA_SWIFT["cpte_ben"] = $cpte_ben;

  /* Vérifie si le compte du donneur est un compte client */
  //$cpte_don = str_replace('-','', $SESSION_VARS["swift"]["num_cpte_do"]);
  $donneur_interne = isBenefClient(trim($cpte_don));

  /* Vérifie si le compte du donneur est un compte client */
  //$cpte_ben = str_replace('-','',$SESSION_VARS["swift"]["num_cpte_ben"]);
  $benef_interne = isBenefClient(trim($cpte_ben));

  if (($donneur_interne != NULL) and (is_array($donneur_interne)) ) { // si le donneur est client interne,
    $id_cpte_don = $donneur_interne["id_cpte"];
    $devise_cpte_don = $donneur_interne["devise"];

    if ( ($benef_interne != NULL) and (is_array($benef_interne)) ) { // si le bénéficiaire est client interne,
      $id_cpte_ben = $benef_interne["id_cpte"];
      $devise_cpte_ben = $benef_interne["devise"];

      $data_virement=array();
      $data_virement['communication']    = $SESSION_VARS["swift"]["communication"];
      $data_virement['id_tireur_benef']  = NULL;
      $data_virement['type_piece']       = 3;
      $data_virement['remarque']         = $SESSION_VARS["swift"]["remarque"];
      $data_virement['sens']             = '---';//il s'agit d'un transfert interne (aucun mouvement de ou vers l'ext.)
      $data_virement['num_piece']        = $id;
      $data_virement['date_piece']       = date("d/m/Y");

      /* if faut d'abord convertir de swift à donneur  */
      // Recherche du taux de change scriptural
      $taux_change = getTauxChange($devise_cpte_don, $devise_cpte_ben, true, 2);
      $change = getChangeInfos($SESSION_VARS["swift"]["montant"], $devise_cpte_don, $devise_cpte_ben, NULL, $taux_change);
      $erreur = transfertCpteClient($id_cpte_don, $id_cpte_ben, $SESSION_VARS["swift"]["montant"], NULL, NULL, $change, $data_virement, $DATA_SWIFT);
      if ($erreur->errCode == NO_ERR) {
        //HTML
        $MyPage = new HTML_message(_("Confirmation traiment"));
        $MyPage->setMessage(_("Le message a été traité avec succès !"));
        $MyPage->addButton(BUTTON_OK, "Swi-1");

        $MyPage->buildHTML();
        echo $MyPage->HTML_code;
      } else {
        $html_err = new HTML_erreur(_("Echec du traitement.")." ");
        $html_err->setMessage(_("Erreur")." : ".$error[$erreur->errCode]);
        $html_err->addButton("BUTTON_OK", 'Swi-1');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }
    } else { // Si le bénéficiaire n'est pas un client interne
      // vérifier que c'est un tireur bénéficiaire
      $benef = isBenefTireur(trim($SESSION_VARS["swift"]["num_cpte_ben"]));
      if ($benef != NULL ) {
        //Transfert par chèque ou par virement
        $infosCorrespondant = getInfosCorrespondant($SESSION_VARS["swift"]["id_correspondant"]);
        $data=array();
        $data['id_correspondant'] = $SESSION_VARS["swift"]["id_correspondant"];
        $data['id_ext_benef']     = $benef["id"];
        $data['id_cpt_ordre']     = $donneur_interne["id_cpte"];
        $data['sens']             = 'out';
        $data['type_piece']       = 3;
        $data['num_piece']        = $id;
        $data['date_piece']       = date("d/m/Y");
        $data['date']             = date("d/m/Y");
        $data['montant']          = $SESSION_VARS["swift"]["montant"];
        $data['devise']           = $SESSION_VARS["swift"]["devise"];
        $data['etat']             = 1;
        $data['communication']    = $SESSION_VARS["swift"]["communication"];
        $data['remarque']         = $SESSION_VARS["swift"]["remarque"];
        $data['id_banque']        = $infosCorrespondant['id_banque'];

        $benef = getTireurBenefDatas($benef["id"]);

        $cptSource=getAccountDatas($donneur_interne["id_cpte"]);
        $prodSource=getProdEpargne($cptSource['id_prod']);

        // Pourquoi ces affectations ? les deux appels de fonction précédents ne renvoient-ils pas ces infos ?
        //$cptSource['frais_transfert']=NULL;
        //$prodSource['frais_transfert']=NULL;

        $erreur = retrait_cpte(null, $donneur_interne["id_cpte"], $prodSource, $cptSource, $SESSION_VARS["swift"]["montant"], 3, NULL, $data, NULL);

        if ($erreur->errCode == NO_ERR) {
          $compteSource=getAccountDatas($donneur_interne["id_cpte"]);
          setMonnaieCourante($compteSource['devise']);
          if (check_access(299) && isset($SESSION_VARS["frais_transfert"]))
            $compteSource["frais_transfert"] = $SESSION_VARS["frais_transfert"];
          $mntPreleve=afficheMontant($SESSION_VARS["swift"]["montant"] + $compteSource["frais_transfert"],TRUE);
          $mntFraisTransfert=afficheMontant($compteSource["frais_transfert"],TRUE);
          if (isset($SESSION_VARS['change']['cv'])) {
            setMonnaieCourante($SESSION_VARS['change']['devise']);
            $mnt_reel=afficheMontant($SESSION_VARS['change']['cv'], true);
          } else {
            $mnt_reel=afficheMontant($SESSION_VARS["swift"]["montant"],TRUE);
          }
          $html_msg =new HTML_message(_("Confirmation de transfert sur un compte"));
          $messageFinal=_("Montant prélevé sur le compte source")." ". $mntPreleve ."<br />";
          $messageFinal.=_("Montant déposé sur le compte destination")." ". $mnt_reel ."<br />";
          $messageFinal.=_("Frais de transfert prélevés sur le compte source")." ". $mntFraisTransfert;
          $html_msg->setMessage($messageFinal);
          $html_msg->addButton("BUTTON_OK", 'Gen-10');
          $html_msg->buildHTML();
          echo $html_msg->HTML_code;
        } else {
          $html_err = new HTML_erreur(_("Echec de transfert sur un compte.")." ");
          $html_err->setMessage("Erreur : ".$error[$erreur->errCode]);
          $html_err->addButton("BUTTON_OK", 'Swi-1');
          $html_err->buildHTML();
          echo $html_err->HTML_code;
        }

      } else {
        $html_err = new HTML_erreur(_("Echec du traitement.")." ");
        $html_err->setMessage(_("Le bénéficiaire n'est ni un client ni un tireur bénéficiaire"));
        $html_err->addButton("BUTTON_OK", 'Swi-1');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }
    }
  } else {
    $html_err = new HTML_erreur(_("Echec du traitement.")." ");
    $html_err->setMessage(_("Le donneur n'est pas un client"));
    $html_err->addButton("BUTTON_OK", 'Swi-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }

}
?>