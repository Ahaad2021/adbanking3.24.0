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
require("lib/html/HtmlHeader.php");
require_once ('lib/dbProcedures/parametrage.php');


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
  // Génération du titre
  $myForm = new HTML_GEN2(_("Recherche personne extérieure"));

  // Variables de session
  if ($SESSION_VARS['gpe']['denom'] == NULL) {
    $SESSION_VARS['gpe']['denom'] = $denom;
  }
  if ($SESSION_VARS['gpe']['pers_ext'] == NULL) {
    $SESSION_VARS['gpe']['pers_ext'] = $pers_ext;
  }

  // Affichage des champs de recherche
  $include = array("denomination", "date_naiss", "lieu_naiss", "ville", "pays");
  $myForm->addTable("ad_pers_ext", OPER_INCLUDE, $include);
  $myForm->setOrder(NULL, $include);
  foreach ($include as $key=>$value) {
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
}

//--------------------------------------------------------------------------------------
//--------- Gpe-2 : Résultat de la recherche -------------------------------------------
//--------------------------------------------------------------------------------------
else if ($ecran == 'Gpe-2') {
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

  $result = nombrePersonneExt($where);
  if ($result->errCode != NO_ERR) {
    $myForm->setTitle(_("Erreur lors de la recherche : ").$result->param[0]);
  } else {
    $nombre = $result->param[0];
  }
  if ($nombre > 300) {
    $myForm->setTitle("$nombre "._("résultats correspondent à vos critères. Veuillez affiner votre recherche."));
  } elseif ($nombre == 0) {
    $myForm->setTitle(_("Aucun résultat pour vos critères."));
  } else {
    $myForm->setTitle(_("Résultats de la recherche")." ($nombre)");

    $table =& $myForm->addHTMLTable('tablepersext', 4, TABLE_STYLE_ALTERN);
    $table->add_cell(new TABLE_cell(_("Dénomination"), 1, 1));
    $table->add_cell(new TABLE_cell(_("Date de naissance"), 1, 1));
    $table->add_cell(new TABLE_cell(_("Lieu de naissance"), 1, 1));
    $table->add_cell(new TABLE_cell(_("Modification"), 1, 1));

    $donnees = getPersonneExt($where);

    foreach($donnees as $key=>$value) {
      $table->add_cell(new TABLE_cell_link($value['denomination'], "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gpe-3&denomination=".$value['denomination']."&id_pers_ext=".$value['id_pers_ext']));
      $table->add_cell(new TABLE_cell(pg2phpdate($value['date_naiss']), 1, 1));
      $table->add_cell(new TABLE_cell($value['lieu_naiss'], 1, 1));

      if ($value['id_client'] != NULL) {
        $table->add_cell(new TABLE_cell("", 1, 1));
      } else {
        $table->add_cell(new TABLE_cell_link(_("Modifier"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gpe-6&id_pers_ext=".$value['id_pers_ext']));
      }
    }
  }

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
}

//--------------------------------------------------------------------------------------
//--------- Gpe-3 : Envoi du résultat à l'écran appelant -------------------------------
//--------------------------------------------------------------------------------------
else if ($ecran == 'Gpe-3') {
  $denom = $SESSION_VARS['gpe']['denom'];
  $pers_ext = $SESSION_VARS['gpe']['pers_ext'];

  if ($denomination == NULL) {
    $denomination = $SESSION_VARS['gpe']['denomination'];
  }

  if ($id_pers_ext == NULL) {
    $id_pers_ext = $SESSION_VARS['gpe']['id_pers_ext'];
  }
  $denomination = str_replace('&apos;', "\'", $denomination);
  echo "
  <script type=\"text/javascript\">
  opener.document.ADForm.$denom.value = '$denomination';
  opener.document.ADForm.$pers_ext.value = $id_pers_ext;
  window.close();
  </script>";
}
//--------------------------------------------------------------------------------------
//--------- Gpe-4 : Ajout d'une nouvelle personne extérieure ---------------------------
//--------------------------------------------------------------------------------------
else if ($ecran == 'Gpe-4') {
  // Génération du titre
  $myForm = new HTML_GEN2(_("Ajout d'une personne extérieure"));

  // Affichage des champs
  
  $include = array("denomination", "adresse", "code_postal", "ville", "pays", "num_tel", "date_naiss", "lieu_naiss", "type_piece_id", "num_piece_id", "lieu_piece_id", "date_piece_id", "date_exp_piece_id");

  $url = "/adbanking/images/travaux.gif";
  $myForm->addField("photo",_("Photographie"),TYPC_IMG);
  $myForm->setFieldProperties("photo", FIELDP_IMAGE_URL, $url);
  $myForm->addField("signature",_("Spécimen de signature"),TYPC_IMG);
  $myForm->setFieldProperties("signature", FIELDP_IMAGE_URL, $url);

  $myForm->addTable("ad_pers_ext", OPER_INCLUDE, $include);
  $myForm->setOrder(NULL, $include);
  $myForm->setFieldProperties("denomination", FIELDP_IS_REQUIRED, true);
  //champs obligatoire ou pas a gerer ici
  if ($global_nom_ecran =="Rcp-2"){
  $myForm->setFieldProperties("type_piece_id", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("num_piece_id", FIELDP_IS_REQUIRED, true);
  }

  //Ajout d'un hidden Field pour la validation des piece d'identité
  $myForm->addHiddenType("char_length_hidden");

  //liste des pièces d'identité et leurs nombre de caractères
  $listPieceIdentLen=getListPieceIdentLength();

  $myForm->setFieldProperties("type_piece_id", FIELDP_JS_EVENT, array("onchange"=>"getCharLength()"));

  //Fonction JavaScript pour trouver le type de pièce d'identité choisie et le nombre de caractères correspondant
  $js1 = "function lookup( name , arr)
          {
             for(var i = 0, len = arr.length; i < len; i++)
                {
                  if( arr[ i ].key == name )
                    {
                        return arr[ i ].value;
                    }
                }
                return false;
            };\n ";

  $js1 .= "function getCharLength(){ \n var myArray = [\n";

  //fonction qui construit un tableau en javascript contenant les pièces d'identité et leurs nombre de caractères respectifs.
  foreach($listPieceIdentLen as $key=>$value)
    {
        $js1 .= "{ key: $key, value: $value },";
    }

  $js1 .= "];\n";

  $js1 .= " document.ADForm.char_length_hidden.value='';\n";
  $js1 .= " if( lookup(document.ADForm.HTML_GEN_LSB_type_piece_id.value, myArray ) != false ) { \n";
  $js1 .= "document.ADForm.char_length_hidden.value = lookup(document.ADForm.HTML_GEN_LSB_type_piece_id.value, myArray );\n}";
  $js1 .= "}\n";

  $myForm->addJS(JSP_FORM, "js", $js1);

    //Validation du nombre de caractères des pièces d'identité
  $js2 = "";
  $js2 .= "if (document.ADForm.char_length_hidden.value != 0 && (document.ADForm.char_length_hidden.value != '' && document.ADForm.num_piece_id.value.length != document.ADForm.char_length_hidden.value))
                {
                    msg += '"._("- Le no. de la pièce d\'identité ne correspond pas à ")."';
                    msg += document.ADForm.char_length_hidden.value
                    msg += '"._(" caractères ")."\\n';
                    ADFormValid = false;
                    }";

  $myForm->addJS(JSP_BEGIN_CHECK, "js2",$js2);

  //Boutons
  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_BUTTON);
  $myForm->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value = 'Gpe-5';"));
  $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT, array("onclick" => "window.close();"));
  $myForm->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->buildHTML();
  echo $myForm->getHTML();
}

//--------------------------------------------------------------------------------------
//--------- Gpe-5 : Confirmation de l'ajout d'une personne extérieure ------------------
//--------------------------------------------------------------------------------------
else if ($ecran == 'Gpe-5') {
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
  $photo = $HTML_GEN_IMG_photo;
  $signature = $HTML_GEN_IMG_signature;

  $mod = str_replace("'", "&apos;", $denomination);
  $DATA = array('denomination' => $mod, 'adresse' => $adresse, 'code_postal' => $code_postal, 'ville' => $ville, 'pays' => $pays, 'num_tel' => $num_tel, 'date_naiss' => $date_naiss, 'lieu_naiss' => $lieu_naiss, 'type_piece_id' => $type_piece_id, 'num_piece_id' => $num_piece_id, 'lieu_piece_id' => $lieu_piece_id, 'date_piece_id' => $date_piece_id, 'date_exp_piece_id' => $date_exp_piece_id, 'photo' => $photo, 'signature' => $signature);

  $result = ajouterPersonneExt($DATA);

  if ($result->errCode == NO_ERR) {
    $SESSION_VARS['gpe']['denomination'] = $mod;
    $SESSION_VARS['gpe']['id_pers_ext'] = $result->param['id_pers_ext'];
    $myForm = new HTML_message(_("Confirmation de l'ajout d'une personne extérieure"));
    $msg = _("L'ajout de la personne extérieure s'est déroulée avec succès ".$mod);
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
  // Variables globales
  $SESSION_VARS['gpe']['id_pers_ext'] = $id_pers_ext;
  $IMAGES = imageLocationPersExt($SESSION_VARS['gpe']['id_pers_ext']);
  if (is_file($IMAGES['photo_chemin_local']))
    $SESSION_VARS['gpe']['photo'] = $IMAGES['photo_chemin_web'];
  else
    $SESSION_VARS['gpe']['photo'] ="/adbanking/images/travaux.gif";
  if (is_file($IMAGES['signature_chemin_local']))
    $SESSION_VARS['gpe']['signature'] = $IMAGES['signature_chemin_web'];
  else
    $SESSION_VARS['gpe']['signature'] = "/adbanking/images/travaux.gif";

  // Génération du titre
  $myForm = new HTML_GEN2(_("Modification d'une personne extérieure"));

  // Affichage des champs

  $myForm->addField("photo",_("Photographie"),TYPC_IMG);
  $myForm->setFieldProperties('photo', FIELDP_IMAGE_URL, $SESSION_VARS['gpe']['photo']);
  $myForm->addField("signature",_("Spécimen de signature"),TYPC_IMG);
  $myForm->setFieldProperties('signature', FIELDP_IMAGE_URL, $SESSION_VARS['gpe']['signature']);

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
}

//--------------------------------------------------------------------------------------
//--------- Gpe-7 : Confirmation de la modification d'une personne extérieure ----------
//--------------------------------------------------------------------------------------
else if ($ecran == 'Gpe-7') {
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
  $photo = $HTML_GEN_IMG_photo;
  $signature = $HTML_GEN_IMG_signature;

  $DATA = array('denomination' => $denomination, 'adresse' => $adresse, 'code_postal' => $code_postal, 'ville' => $ville,
                'pays' => $pays, 'num_tel' => $num_tel, 'date_naiss' => $date_naiss, 'lieu_naiss' => $lieu_naiss, 'type_piece_id' => $type_piece_id, 'num_piece_id' => $num_piece_id, 'lieu_piece_id' => $lieu_piece_id, 'date_piece_id' => $date_piece_id, 'date_exp_piece_id' => $date_exp_piece_id, 'photo' => $photo, 'signature' => $signature);

  $result = modifierPersonneExt($id_pers_ext, $DATA);

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
else signalErreur(__FILE__,__LINE__,__FUNCTION__);

require("lib/html/HtmlFooter.php");
?>