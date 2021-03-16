<?php

//--------- Gpe-1 : Recherche d'une personne extérieure --------------------------------
//--------- Gpe-2 : Résultat de la recherche -------------------------------------------
//--------- Gpe-3 : Envoi du résultat à l'écran appelant -------------------------------
//--------- Gpe-4 : Ajout d'une nouvelle personne extérieure ---------------------------
//--------- Gpe-5 : Confirmation de l'ajout d'une personne extérieure ------------------
//--------- Gpe-6 : Modification d'une personne extérieure -----------------------------
//--------- Gpe-7 : Confirmation de la modification d'une personne extérieure ----------

require_once('lib/html/HTML_GEN2.php');
require_once('lib/html/FILL_HTML_GEN2.php');
require_once('lib/html/HTML_message.php');
require_once('lib/misc/VariablesGlobales.php');

// Multi agence includes
require_once 'ad_ma/app/controllers/misc/VariablesSessionRemote.php';
require_once 'ad_ma/app/models/AgenceRemote.php';
require_once 'ad_ma/app/models/Agence.php';
require_once 'ad_ma/app/models/Client.php';
require_once 'ad_ma/app/models/Compta.php';
require_once 'ad_ma/app/models/Compte.php';
require_once 'ad_ma/app/models/Devise.php';
require_once 'ad_ma/app/models/Divers.php';
require_once 'ad_ma/app/models/Epargne.php';
require_once 'ad_ma/app/models/Guichet.php';
require_once 'ad_ma/app/models/Historique.php';
require_once 'ad_ma/app/models/Parametrage.php';
require_once 'ad_ma/app/models/TireurBenef.php';

require("lib/html/HtmlHeader.php");

echo "
<script type=\"text/javascript\">
opener.onfocus= react;
function react()
{
window.focus();
}
</script>";

$ecran = $prochain_ecran;

//--------------------------------------------------------------------------------------
//--------- Gpe-1 : Recherche d'une personne extérieure --------------------------------
//--------------------------------------------------------------------------------------
if ($ecran == '' || $ecran == 'Gpe-1') {
    global $global_remote_id_agence;
    
    // Begin remote transaction
    $pdo_conn->beginTransaction();

    // Génération du titre
    $myForm = new HTML_GEN2(_("Recherche personne extérieure"));

    // Variables de session
    if ($SESSION_VARS['gpe']['denom'] == NULL) {
        $SESSION_VARS['gpe']['denom'] = $denom;
    }
    if ($SESSION_VARS['gpe']['pers_ext'] == NULL) {
        $SESSION_VARS['gpe']['pers_ext'] = $pers_ext;
    }
    
    // Recupere la liste des pays
    $ListePays = Divers::getListPays($pdo_conn, $global_remote_id_agence);
    
    $choix_pays = array();
    if (is_array($ListePays) && count($ListePays) > 0) {
        foreach ($ListePays as $key => $value) {
            $choix_pays[$key] = trim($value["libel_pays"]);
        }
    }
    $myForm->addField("pays", _("Pays"), TYPC_LSB);
    $myForm->setFieldProperties("pays", FIELDP_ADD_CHOICES, $choix_pays);
    $myForm->setFieldProperties("pays", FIELDP_IS_REQUIRED, false);

    // Affichage des champs de recherche
    $include = array("denomination", "date_naiss", "lieu_naiss", "ville");
    $include_order = array("denomination", "date_naiss", "lieu_naiss", "ville" , "pays");
    $myForm->addTable("ad_pers_ext", OPER_INCLUDE, $include);
    $myForm->setOrder(NULL, $include_order);
    foreach ($include as $key => $value) {
        $myForm->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
    }

    // Boutons
    $myForm->addFormButton(1, 1, "rechercher", _("Rechercher"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "ajouter", _("Ajouter"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_BUTTON);
    $myForm->setFormButtonProperties("rechercher", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value = 'Gpe-2';"));
    $myForm->setFormButtonProperties("ajouter", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value = 'Gpe-4';"));
    $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT, array("onclick" => "window.close();"));
    $myForm->setFormButtonProperties("rechercher", BUTP_CHECK_FORM, false);
    $myForm->setFormButtonProperties("ajouter", BUTP_CHECK_FORM, false);
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    $myForm->buildHTML();
    echo $myForm->getHTML();
    
    // Commit transaction
    $pdo_conn->commit();
}

//--------------------------------------------------------------------------------------
//--------- Gpe-2 : Résultat de la recherche -------------------------------------------
//--------------------------------------------------------------------------------------
else if ($ecran == 'Gpe-2') {
    global $global_remote_id_agence;
    
    // Begin remote transaction
    $pdo_conn->beginTransaction();

    require_once('lib/misc/divers.php');

    $date_naiss = $HTML_GEN_date_date_naiss;

    if ($HTML_GEN_LSB_pays == 0) {
        $pays = '';
    } else {
        $pays = $HTML_GEN_LSB_pays;
    }

    // Requête et affichage des données
    if ($denomination) {
        $where["denomination"] = $denomination;
    }
    if ($date_naiss) {
        $where["date_naiss"] = $date_naiss;
    }
    if ($lieu_naiss) {
        $where["lieu_naiss"] = $lieu_naiss;
    }
    if ($ville) {
        $where["ville"] = $ville;
    }
    if ($pays) {
        $where["pays"] = $pays;
    }

    $myForm = new HTML_GEN2();
    
    // Init class
    $ClientObj = new Client($pdo_conn, $global_remote_id_agence);

    $nombre = $ClientObj->nombrePersonneExt($where);

    if ($nombre > 300) {
        $myForm->setTitle("$nombre " . _("résultats correspondent à vos critères. Veuillez affiner votre recherche."));
    } elseif ($nombre == 0) {
        $myForm->setTitle(_("Aucun résultat pour vos critères."));
    } else {
        $myForm->setTitle(_("Résultats de la recherche") . " ($nombre)");

        $table = & $myForm->addHTMLTable('tablepersext', 3, TABLE_STYLE_ALTERN); //4
        $table->add_cell(new TABLE_cell(_("Dénomination"), 1, 1));
        $table->add_cell(new TABLE_cell(_("Date de naissance"), 1, 1));
        $table->add_cell(new TABLE_cell(_("Lieu de naissance"), 1, 1));
        //$table->add_cell(new TABLE_cell(_("Modification"), 1, 1));

        $donnees = $ClientObj->getPersonneExt($where);

        foreach ($donnees as $key => $value) {
            $table->add_cell(new TABLE_cell_link($value['denomination'], "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gpe-3&denomination=" . $value['denomination'] . "&id_pers_ext=" . $value['id_pers_ext']));
            $table->add_cell(new TABLE_cell(pg2phpdate($value['date_naiss']), 1, 1));
            $table->add_cell(new TABLE_cell($value['lieu_naiss'], 1, 1));

            if ($value['id_client'] != NULL) {
                //$table->add_cell(new TABLE_cell("", 1, 1));
            } else {
                //$table->add_cell(new TABLE_cell_link(_("Modifier"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gpe-6&id_pers_ext=" . $value['id_pers_ext'])); // Hide modify
            }
        }
    }
    
    // Destroy object
    unset($ClientObj);

    // Boutons
    $myForm->addFormButton(1, 1, "ajouter", _("Ajouter"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "precedent", _("Précédent"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_BUTTON);
    $myForm->setFormButtonProperties("ajouter", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value = 'Gpe-4';"));
    $myForm->setFormButtonProperties("precedent", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value = 'Gpe-1';"));
    $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT, array("onclick" => "window.close();"));
    $myForm->setFormButtonProperties("ajouter", BUTP_CHECK_FORM, false);
    $myForm->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    $myForm->buildHTML();
    echo $myForm->getHTML();
    
    // Commit transaction
    $pdo_conn->commit();
}

//--------------------------------------------------------------------------------------
//--------- Gpe-3 : Envoi du résultat à l'écran appelant -------------------------------
//--------------------------------------------------------------------------------------
else if ($ecran == 'Gpe-3') {
    
    // Begin remote transaction
    $pdo_conn->beginTransaction();
    
    $denom = $SESSION_VARS['gpe']['denom'];
    $pers_ext = $SESSION_VARS['gpe']['pers_ext'];

    if ($denomination == NULL) {
        $denomination = $SESSION_VARS['gpe']['denomination'];
    }

    if ($id_pers_ext == NULL) {
        $id_pers_ext = $SESSION_VARS['gpe']['id_pers_ext'];
    }

    echo "
  <script type=\"text/javascript\">
  opener.document.ADForm.$denom.value = '$denomination';
  opener.document.ADForm.$pers_ext.value = '$id_pers_ext';
  window.close();
  </script>";
    
    // Commit transaction
    $pdo_conn->commit();
}
//--------------------------------------------------------------------------------------
//--------- Gpe-4 : Ajout d'une nouvelle personne extérieure ---------------------------
//--------------------------------------------------------------------------------------
else if ($ecran == 'Gpe-4') {
    global $global_remote_id_agence, $global_langue_utilisateur;
    
    // Begin remote transaction
    $pdo_conn->beginTransaction();

    // Génération du titre
    $myForm = new HTML_GEN2(_("Ajout d'une personne extérieure"));

    // Affichage des champs
    $include = array("denomination", "adresse", "code_postal", "ville", "num_tel", "date_naiss", "lieu_naiss" , "num_piece_id", "lieu_piece_id", "date_piece_id", "date_exp_piece_id");

    $include_order = array("denomination", "adresse", "code_postal", "ville", "pays", "num_tel", "date_naiss", "lieu_naiss", "type_piece_id", "num_piece_id", "lieu_piece_id", "date_piece_id", "date_exp_piece_id");
    
    /*
    $url = "/adbanking/images/travaux.gif";
    $myForm->addField("photo", _("Photographie"), TYPC_IMG);
    $myForm->setFieldProperties("photo", FIELDP_IMAGE_URL, $url);
    $myForm->addField("signature", _("Spécimen de signature"), TYPC_IMG);
    $myForm->setFieldProperties("signature", FIELDP_IMAGE_URL, $url);
    */
    
    // Récupère la liste des pays
    $ListePays = Divers::getListPays($pdo_conn, $global_remote_id_agence);
    
    $choix_pays = array();
    if (is_array($ListePays) && count($ListePays) > 0) {
        foreach ($ListePays as $key => $value) {
            $choix_pays[$key] = trim($value["libel_pays"]);
        }
    }
    $myForm->addField("pays", _("Pays"), TYPC_LSB);
    $myForm->setFieldProperties("pays", FIELDP_ADD_CHOICES, $choix_pays);
    $myForm->setFieldProperties("pays", FIELDP_IS_REQUIRED, false);
    
    // Récupère la liste des types pièce identité
    $ListeTypePieceIdentite = Divers::getListTypePieceIdentite($pdo_conn, $global_remote_id_agence, $global_langue_utilisateur);
    
    $choix_TypePieceIdentite = array();
    if (is_array($ListeTypePieceIdentite) && count($ListeTypePieceIdentite) > 0) {
        foreach ($ListeTypePieceIdentite as $key => $value) {
            $choix_TypePieceIdentite[$key] = trim($value["traduction"]);
        }
    }
    $myForm->addField("type_piece_id", _("Type de pièce d'identité"), TYPC_LSB);
    $myForm->setFieldProperties("type_piece_id", FIELDP_ADD_CHOICES, $choix_TypePieceIdentite);
    $myForm->setFieldProperties("type_piece_id", FIELDP_IS_REQUIRED, false);
    
    $myForm->addTable("ad_pers_ext", OPER_INCLUDE, $include);
    $myForm->setOrder(NULL, $include_order);
    $myForm->setFieldProperties("denomination", FIELDP_IS_REQUIRED, true);

    //Boutons
    $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_BUTTON);
    $myForm->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value = 'Gpe-5';"));
    $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT, array("onclick" => "window.close();"));
    $myForm->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    $myForm->buildHTML();
    echo $myForm->getHTML();
    
    // Commit transaction
    $pdo_conn->commit();
}

//--------------------------------------------------------------------------------------
//--------- Gpe-5 : Confirmation de l'ajout d'une personne extérieure ------------------
//--------------------------------------------------------------------------------------
else if ($ecran == 'Gpe-5') {
    global $global_remote_id_agence;
    
    // Begin remote transaction
    $pdo_conn->beginTransaction();

    if ($HTML_GEN_LSB_pays == 0) {
        $pays = '';
    } else {
        $pays = $HTML_GEN_LSB_pays;
    }

    if ($HTML_GEN_LSB_type_piece_id == 0) {
        $type_piece_id = '';
    } else {
        $type_piece_id = $HTML_GEN_LSB_type_piece_id;
    }

    $date_naiss = $HTML_GEN_date_date_naiss;
    $date_piece_id = $HTML_GEN_date_date_piece_id;
    $date_exp_piece_id = $HTML_GEN_date_date_exp_piece_id;
    //$photo = $HTML_GEN_IMG_photo;
    //$signature = $HTML_GEN_IMG_signature;

    $DATA = array('denomination' => $denomination, 'adresse' => $adresse, 'code_postal' => $code_postal, 'ville' => $ville, 'pays' => $pays, 'num_tel' => $num_tel, 'date_naiss' => $date_naiss, 'lieu_naiss' => $lieu_naiss, 'type_piece_id' => $type_piece_id, 'num_piece_id' => $num_piece_id, 'lieu_piece_id' => $lieu_piece_id, 'date_piece_id' => $date_piece_id, 'date_exp_piece_id' => $date_exp_piece_id); // , 'photo' => $photo, 'signature' => $signature

    // $result = ajouterPersonneExt($DATA);
    
    try{
        // Init class
        $ClientObj = new Client($pdo_conn, $global_remote_id_agence);

        $result = $ClientObj->ajouterPersonneExt($DATA);
        
        // Destroy object
        unset($ClientObj);
        
        $pdo_conn->commit(); // Commit
    }
    catch (PDOException $e) {
        $pdo_conn->rollBack(); // Roll back

        signalErreur(__FILE__, __LINE__, __FUNCTION__, $e->getMessage());	
    }

    if ($result->errCode == NO_ERR) {
        $SESSION_VARS['gpe']['denomination'] = $denomination;
        $SESSION_VARS['gpe']['id_pers_ext'] = $result->param['id_pers_ext'];
        $myForm = new HTML_message(_("Confirmation de l'ajout d'une personne extérieure"));
        $msg = _("L'ajout de la personne extérieure s'est déroulée avec succès ");
        $myForm->setMessage($msg);
        $myForm->addButton(BUTTON_OK, "Gpe-3");
        $myForm->buildHTML();
        echo $myForm->HTML_code;
    }
}

//--------------------------------------------------------------------------------------
//--------- Gpe-6 : Modification d'une personne extérieure -----------------------------
//--------------------------------------------------------------------------------------
else if ($ecran == 'Gpe-6') {
    
    // Begin remote transaction
    $pdo_conn->beginTransaction();
    
    // Variables globales
    $SESSION_VARS['gpe']['id_pers_ext'] = $id_pers_ext;
    /*
      $IMAGES = imageLocationPersExt($SESSION_VARS['gpe']['id_pers_ext']);
      if (is_file($IMAGES['photo_chemin_local']))
      $SESSION_VARS['gpe']['photo'] = $IMAGES['photo_chemin_web'];
      else
      $SESSION_VARS['gpe']['photo'] = "/adbanking/images/travaux.gif";
      if (is_file($IMAGES['signature_chemin_local']))
      $SESSION_VARS['gpe']['signature'] = $IMAGES['signature_chemin_web'];
      else
      $SESSION_VARS['gpe']['signature'] = "/adbanking/images/travaux.gif";
     */

    // Génération du titre
    $myForm = new HTML_GEN2(_("Modification d'une personne extérieure"));

    // Affichage des champs
    /* DISABLE IMAGE
      $myForm->addField("photo", _("Photographie"), TYPC_IMG);
      $myForm->setFieldProperties('photo', FIELDP_IMAGE_URL, $SESSION_VARS['gpe']['photo']);
      $myForm->addField("signature", _("Spécimen de signature"), TYPC_IMG);
      $myForm->setFieldProperties('signature', FIELDP_IMAGE_URL, $SESSION_VARS['gpe']['signature']);
     */

    $include = array("denomination", "adresse", "code_postal", "ville", "pays", "num_tel", "date_naiss", "lieu_naiss", "type_piece_id", "num_piece_id", "lieu_piece_id", "date_piece_id", "date_exp_piece_id");
    $myForm->addTable("ad_pers_ext", OPER_INCLUDE, $include);
    $myForm->setOrder(NULL, $include);
    $myForm->setFieldProperties("denomination", FIELDP_IS_REQUIRED, true);

    $myFill = new FILL_HTML_GEN2();
    $myFill->addFillClause('pers_ext_clause', 'ad_pers_ext');
    $myFill->addCondition('pers_ext_clause', 'id_pers_ext', $id_pers_ext);
    $myFill->addManyFillFields('pers_ext_clause', OPER_INCLUDE, $include);
    $myFill->fill($myForm);

    //Boutons
    $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_BUTTON);
    $myForm->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value = 'Gpe-7';"));
    $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT, array("onclick" => "window.close();"));
    $myForm->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    $myForm->buildHTML();
    echo $myForm->getHTML();
    
    // Commit transaction
    $pdo_conn->commit();
}

//--------------------------------------------------------------------------------------
//--------- Gpe-7 : Confirmation de la modification d'une personne extérieure ----------
//--------------------------------------------------------------------------------------
else if ($ecran == 'Gpe-7') {
    global $global_remote_id_agence;
    
    // Begin remote transaction
    $pdo_conn->beginTransaction();

    $id_pers_ext = $SESSION_VARS['gpe']['id_pers_ext'];

    if ($HTML_GEN_LSB_pays == 0) {
        $pays = '';
    } else {
        $pays = $HTML_GEN_LSB_pays;
    }

    if ($HTML_GEN_LSB_type_piece_id == 0) {
        $type_piece_id = '';
    } else {
        $type_piece_id = $HTML_GEN_LSB_type_piece_id;
    }

    $date_naiss = $HTML_GEN_date_date_naiss;
    $date_piece_id = $HTML_GEN_date_date_piece_id;
    $date_exp_piece_id = $HTML_GEN_date_date_exp_piece_id;
    //$photo = $HTML_GEN_IMG_photo;
    //$signature = $HTML_GEN_IMG_signature;

    $DATA = array('denomination' => $denomination, 'adresse' => $adresse, 'code_postal' => $code_postal, 'ville' => $ville,
        'pays' => $pays, 'num_tel' => $num_tel, 'date_naiss' => $date_naiss, 'lieu_naiss' => $lieu_naiss, 'type_piece_id' => $type_piece_id, 'num_piece_id' => $num_piece_id, 'lieu_piece_id' => $lieu_piece_id, 'date_piece_id' => $date_piece_id, 'date_exp_piece_id' => $date_exp_piece_id); // , 'photo' => $photo, 'signature' => $signature    
  
    try{
        // Init class
        $ClientObj = new Client($pdo_conn, $global_remote_id_agence);

        $result = $ClientObj->modifierPersonneExt($id_pers_ext, $DATA);
        
        // Destroy object
        unset($ClientObj);
        
        $pdo_conn->commit(); // Commit
    }
    catch (PDOException $e) {
        $pdo_conn->rollBack(); // Roll back

        signalErreur(__FILE__, __LINE__, __FUNCTION__, $e->getMessage());
    }

    if ($result->errCode == NO_ERR) {
        $SESSION_VARS['gpe']['denomination'] = $denomination;
        $SESSION_VARS['gpe']['id_pers_ext'] = $id_pers_ext;
        $myForm = new HTML_message(_("Confirmation de la modification d'une personne extérieure"));
        $msg = _("La modification de la personne extérieure s'est déroulée avec succès ");
        $myForm->setMessage($msg);
        $myForm->addButton(BUTTON_OK, "Gpe-3");
        $myForm->buildHTML();
        echo $myForm->HTML_code;
    }
}

//--------------------------------------------------------------------------------------
//--------- Erreur ---------------------------------------------------------------------
//--------------------------------------------------------------------------------------
else
    signalErreur(__FILE__, __LINE__, __FUNCTION__);

require("lib/html/HtmlFooter.php");

// Fermer la connexion BDD
unset($pdo_conn);

?>