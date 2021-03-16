<?php

//error_reporting(E_ALL);
//ini_set("display_errors", "on");

/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Gestion des tireurs bénéficiaires
 * Cette opération comprends les écrans :
 * - Tib-1 : écran de recherche d'un tireur bénéficiaire
 * - Tib-2 : résultat de la recherche
 * - Tib-3 : encodage nouveau tireur / bénéficiaire
 * - Tib-4 : confirmation de l'encodage
 * - Tib-5 : Affichage d'un tireur bénéficiaire
 * - Tib-6 : Modification d'un tireur bénéficiaire
 * - Tib-7 : confirmation de l'encodage
 * - Tib-8 : envoi du résultat à l'écran appelant
 * - Tib-9 : mettre les informations du bénéficiaire dans une variable de session
 * @package Guichet
 */
require_once('lib/html/HTML_GEN2.php');
require_once('lib/dbProcedures/tireur_benef.php');
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
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once('lib/html/HTML_message.php');

echo "
<script type=\"text/javascript\">
opener.onfocus= react;
function react()
{
window.focus();
}
</script>";

$ecran = $prochain_ecran;

/* {{{ Tib-1 : Ecran de recherche d'un tireur bénéficiaire */
if ($ecran == '' || $ecran == 'Tib-1') {
    global $global_remote_id_agence;
    
    // Begin remote transaction
    $pdo_conn->beginTransaction();

    //Mise à blanc des critères de recherche
    $SESSION_VARS['tib']['denomination'] = '';
    $SESSION_VARS['tib']['ville'] = '';
    $SESSION_VARS['tib']['pays'] = '';
    $SESSION_VARS['tib']['id_banque'] = '';

    //S'il y a des variables "GET", cela signifie que l'on arrive dans l'écran de recherche pour la première fois. On initialise les variables de Session.
    if (isset($type) || isset($field_name) || isset($field_id)) {
        unset($SESSION_VARS['type_recherche']);
        unset($SESSION_VARS['field_name']);
        unset($SESSION_VARS['field_id']);
    }

    //Sauvegarde des informations "GETées"
    if (isset($type))
        $SESSION_VARS['type_recherche'] = $type;
    if (isset($field_name))
        $SESSION_VARS['field_name'] = $field_name; //Nom du champs dans lequel va être inscrit la dénomination du tireur/bénéficiaire sélectionné
    if (isset($field_id))
        $SESSION_VARS['field_id'] = $field_id; //Nom du champs dans lequel va être l'ID du tireur/bénéficiaire


        
//Génération du titre
    switch ($SESSION_VARS['type_recherche']) {
        case 'b' :
            $charTitle = _("Recherche bénéficiaire");
            break;
        case 't' :
            $charTitle = _("Recherche tireur");
            break;
        default :
            $charTitle = _("Recherche bénéficiaire / tireur");
    }
    $myForm = new HTML_GEN2($charTitle);
    
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

    //Affichage des champs de recherche
    $include = array("denomination", "ville", "id_banque");
    $include_order = array("denomination", "ville", "pays", "id_banque");

    $myForm->addTable("tireur_benef", OPER_INCLUDE, $include);
    $myForm->setOrder(NULL, $include_order);
    foreach ($include as $key => $value) {
        $myForm->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
    }

    //Boutons
    $myForm->addFormButton(1, 1, "rechercher", _("Rechercher"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_BUTTON);
    if ($SESSION_VARS['type_recherche'] != 'a') {
        $myForm->addFormButton(1, 3, "ajouter", _("Ajouter"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("ajouter", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value = 'Tib-3';"));
        $myForm->setFormButtonProperties("ajouter", BUTP_CHECK_FORM, false);
    }
    $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT, array("onclick" => "window.close();"));
    $myForm->setFormButtonProperties("rechercher", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value = 'Tib-2';"));
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    $myForm->setFormButtonProperties("rechercher", BUTP_CHECK_FORM, false);
    $myForm->buildHTML();
    echo $myForm->getHTML();
    
    // Commit transaction
    $pdo_conn->commit();
}
/* }}} */

/* {{{ Tib-2 : Résultat de la recherche */
else if ($ecran == 'Tib-2') {
    global $global_remote_id_agence;
    
    // Begin remote transaction
    $pdo_conn->beginTransaction();
    
    require_once('lib/misc/divers.php');
    
    if (isset($denomination) && $denomination != '')
        $SESSION_VARS['tib']['denomination'] = $denomination;
    if (isset($ville) && $ville != '')
        $SESSION_VARS['tib']['ville'] = $ville;
    if (isset($pays) && $pays != '')
        $SESSION_VARS['tib']['pays'] = $pays;
    if (isset($id_banque) && $id_banque != '')
        $SESSION_VARS['tib']['id_banque'] = $id_banque;

    //Requete et affichage des données
    $Where = array();
    $Where["denomination"] = $SESSION_VARS['tib']['denomination'];
    $Where["ville"] = $SESSION_VARS['tib']['ville'];
    $Where["pays"] = $SESSION_VARS['tib']['pays'];
    $Where["id_banque"] = $SESSION_VARS['tib']['id_banque'];
    
    // Init class
    $TireurBenefObj = new TireurBenef($pdo_conn, $global_remote_id_agence);

    $myForm = new HTML_GEN2();
    $nombre = $TireurBenefObj->countTireurBenef($Where, $SESSION_VARS['type_recherche']);

    if ($nombre > 300)
        $myForm->setTitle("$nombre " . _("résultats correspondent à vos critères, Veuillez affiner votre recherche"));
    else if ($nombre == 0)
        $myForm->setTitle(_("Aucun résultat pour vos critères"));
    else {
        $myForm->setTitle(_("Résultats de la recherche") . " ($nombre)");

        $table = & $myForm->addHTMLTable('tablepersext', 3, TABLE_STYLE_ALTERN);
        $table->add_cell(new TABLE_cell(_("Dénomination"), 1, 1));
        $table->add_cell(new TABLE_cell(_("Ville"), 1, 1));
        $table->add_cell(new TABLE_cell(_("Pays"), 1, 1));

        $donnees = $TireurBenefObj->getMatchedTireurBenef($Where, $SESSION_VARS['type_recherche']);

        foreach ($donnees as $key => $value) {
            $prochain_ecran = 'Tib-5'; //Tib-5 : écran d'affichage
            switch ($SESSION_VARS['type_recherche']) {
                case 'b' : //si on sélectionne un bénéficiaire,on vérifie si les données obligatoires (compte) sont présentes.
                    if ($value['num_cpte'] == '' && $value['iban_cpte'] == '') {
                        $prochain_ecran = 'Tib-6'; //Tib-6 : écran de modification
                    }
                    break;
                case 't' : //si on sélectionne un tireur,on vérifie que la dénomination est bien alimentée.
                    if ($value['denomination'] == '') {
                        $prochain_ecran = 'Tib-6'; //Tib-6 : écran de modification
                    }
                    break;
            }

            $table->add_cell(new TABLE_cell_link($value['denomination'], "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=$prochain_ecran&id=" . $value['id']));
            $table->add_cell(new TABLE_cell($value['ville'], 1, 1));
            $table->add_cell(new TABLE_cell($value['libel_pays'], 1, 1));
        }
    }
    
    // Destroy object
    unset($TireurBenefObj);

    //Boutons
    $myForm->addHiddenType("ecran");
    $myForm->addFormButton(1, 1, "rechercher", _("Nouvelle recherche"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_BUTTON);
    if ($SESSION_VARS['type_recherche'] != 'a') { //'a' n'autorise que l'affichage
        $myForm->addFormButton(1, 3, "ajouter", _("Ajouter"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("ajouter", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value = 'Tib-3';"));
        $myForm->setFormButtonProperties("ajouter", BUTP_CHECK_FORM, false);
    }
    $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT, array("onclick" => "window.close();"));
    $myForm->setFormButtonProperties("rechercher", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value = 'Tib-1';"));
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    $myForm->setFormButtonProperties("rechercher", BUTP_CHECK_FORM, false);
    $myForm->buildHTML();
    echo $myForm->getHTML();
    
    // Commit transaction
    $pdo_conn->commit();
}
/* }}} */

/* {{{ Tib-3 : Encodage nouveau tireur / bénéficiaire */ 
else if ($ecran == 'Tib-3') {
    global $global_remote_id_agence, $global_langue_utilisateur;
    
    // Begin remote transaction
    $pdo_conn->beginTransaction();

    if (!isset($SESSION_VARS['type_recherche']))
        $SESSION_VARS['type_recherche'] = $type;
    if (isset($field_name))
        $SESSION_VARS['field_name'] = $field_name;
    if (isset($field_id))
        $SESSION_VARS['field_id'] = $field_id;
    switch ($SESSION_VARS['type_recherche']) {
        case "b":
        case "r":
            $title = _("Introduction d'un nouveau bénéficiaire");
            break;
        case "t":
            $title = _("Introduction d'un nouveau tireur");
            break;
        default:
            $title = _("Introduction d'un nouveau tireur/bénéficiaire");
    }
    $myForm = new HTML_GEN2($title);

    //Affichage des champs d'encodage
    $exclude = array("id", "id_banque", 'type_piece', 'num_piece', 'lieu_delivrance');

    //Ordre d'affichage
    $ordre = array("denomination");

    // champs obligatoire 
    $pays_is_req = true;

    if ($SESSION_VARS['type_recherche'] == 'b') { // bénéficiaire est mis à true.
        array_push($exclude, "tireur", "beneficiaire", "pays", "type_piece");
        array_push($ordre, "banque", "num_cpte", "iban_cpte");
        $myForm->addHiddenType("beneficiaire", 1);
    } else if ($SESSION_VARS['type_recherche'] == 't') { //si on encode un tireur, tireur est mis à true.
        array_push($exclude, "tireur", "beneficiaire", "pays", "type_piece");
        array_push($ordre, "banque", "num_cpte", "iban_cpte");
        $myForm->addHiddenType("tireur", 1);
    } else if ($SESSION_VARS['type_recherche'] == 'm') {
        array_push($exclude, "pays", "type_piece");
        array_push($ordre, "tireur", "beneficiaire", "banque", "num_cpte", "iban_cpte");
    }
    if ($SESSION_VARS['type_recherche'] == 'r') {
        $params_type_piece = array('type_piece', 'num_piece', 'lieu_delivrance');
        array_push($ordre, 'type_piece', 'num_piece', 'lieu_delivrance');
        $pays_is_req = false;
        $exclude = array("id", "id_banque", "tireur", "beneficiaire", "banque", "num_cpte", "iban_cpte", "pays", "type_piece");
    } else {
        array_push($exclude, "pays", "type_piece");
        
        // Init class
        $ParametrageObj = new Parametrage($pdo_conn, $global_remote_id_agence);
        
        //Champs banque
        $libels = $ParametrageObj->getLibelBanque();
        $myForm->addField("banque", _("Banques"), TYPC_LSB);
        $myForm->setFieldProperties("banque", FIELDP_ADD_CHOICES, $libels);
        
        // Destroy object
        unset($ParametrageObj);
    }

    $myForm->addTable("tireur_benef", OPER_EXCLUDE, $exclude);

    // Récupère la liste des types pièce identité
    $ListeTypePieceIdentite = Divers::getListTypePieceIdentite($pdo_conn, $global_remote_id_agence, $global_langue_utilisateur);

    $choix_TypePieceIdentite = array();
    if (is_array($ListeTypePieceIdentite) && count($ListeTypePieceIdentite) > 0) {
        foreach ($ListeTypePieceIdentite as $key => $value) {
            $choix_TypePieceIdentite[$key] = trim($value["traduction"]);
        }
    }
    $myForm->addField("type_piece", _("Type de pièce d'identité"), TYPC_LSB);
    $myForm->setFieldProperties("type_piece", FIELDP_ADD_CHOICES, $choix_TypePieceIdentite);
    $myForm->setFieldProperties("type_piece", FIELDP_IS_REQUIRED, true);
        
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
    $myForm->setFieldProperties("pays", FIELDP_IS_REQUIRED, $pays_is_req);
    foreach ($params_type_piece as $champs) {
        $myForm->setFieldProperties($champs, FIELDP_IS_REQUIRED, true);
    }

    array_push($ordre, "adresse", "code_postal", "ville", "pays", "num_tel");
    $myForm->setOrder(NULL, $ordre);

    $JS_check =
            "if (document.ADForm.num_cpte.value == '' && document.ADForm.iban_cpte.value == '')
  {
    msg += '- " . _("Les champs \"N° de compte\" et/ou \"N° IBAN\" doivent être renseignés") . "\\n';
    ADFormValid = false;
  }";

    if ($SESSION_VARS['type_recherche'] == 'b') {
        $myForm->addJS(JSP_BEGIN_CHECK, "test", $JS_check);
    }

    //Boutons
    $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_BUTTON);
    $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT, array("onclick" => "window.close();"));
    if ($SESSION_VARS['type_recherche'] != 'r') {
        $myForm->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value = 'Tib-4';"));
    } else {
        $myForm->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value = 'Tib-8';"));
    }
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    $myForm->buildHTML();
    echo $myForm->getHTML();
    
    // Commit transaction
    $pdo_conn->commit();
}
/* }}} */

/* {{{ Tib-4 : Confirmation de l'encodage */ 
else if ($ecran == 'Tib-4') {
    global $global_remote_id_agence;
    
    // Begin remote transaction
    $pdo_conn->beginTransaction();

    //Insertion du nouveau Tireur/Bénéficiaire
    $DATA['denomination'] = $denomination;
    $DATA['adresse'] = $adresse;
    $DATA['code_postal'] = $code_postal;
    $DATA['ville'] = $ville;
    $DATA['num_tel'] = $num_tel;
    $DATA['num_cpte'] = $num_cpte;
    $DATA['iban_cpte'] = $iban_cpte;
    if ($HTML_GEN_LSB_pays != 0)
        $DATA['pays'] = $HTML_GEN_LSB_pays;
    if ($HTML_GEN_LSB_banque != 0)
        $DATA['id_banque'] = $HTML_GEN_LSB_banque;
    if ($tireur)
        $DATA['tireur'] = 't';
    else
        $DATA['tireur'] = 'f';
    if ($beneficiaire)
        $DATA['beneficiaire'] = 't';
    else
        $DATA['beneficiaire'] = 'f';

    
    try{
        // Init class
        $TireurBenefObj = new TireurBenef($pdo_conn, $global_remote_id_agence);

        $id = $TireurBenefObj->insereTireurBenef($DATA);
        
        // Destroy object
        unset($TireurBenefObj);

        $pdo_conn->commit(); // Commit
    }
    catch (PDOException $e) {
        $pdo_conn->rollBack(); // Roll back

        signalErreur(__FILE__, __LINE__, __FUNCTION__, $e->getMessage());		
    }

    $SESSION_VARS['tib']['nomTireur'] = $DATA['denomination'];
    $SESSION_VARS['tib']['id'] = $id;
    $myForm = new HTML_message(_("Confirmation de l'enregistrement d'un tireur/bénéficiaire"));
    $msg = _("L'enregistrement du tireur/bénéficiaire s'est déroulé avec succès") . " ";
    $myForm->setMessage($msg);
    $myForm->addButton(BUTTON_OK, "Tib-8");
    $myForm->buildHTML();
    echo $myForm->HTML_code;
}
/* }}} */

/* {{{ Tib-5 : Affichage d'un tireur bénéficiaire */
else if ($ecran == 'Tib-5') 
{
    global $global_remote_id_agence;
    
    // Begin remote transaction
    $pdo_conn->beginTransaction();

    $myForm = new HTML_GEN2(_("Tireur/Bénéficiaire sélectionné"));
    
    // Get tireur benef datas
    $TireurBenefObj = new TireurBenef($pdo_conn, $global_remote_id_agence);
    $benef = $TireurBenefObj->getTireurBenefDatas($id);
    unset($TireurBenefObj);
    
    $pays = '';  $banque = '';
    
    if(!empty($benef["pays"])) {
        $pays = Divers::getLibellePays($pdo_conn, $global_remote_id_agence, $benef["pays"]);
    }    
    
    if(!empty($benef["id_banque"])) {
        $banque = Divers::getLibelleBanque($pdo_conn, $global_remote_id_agence, $benef["id_banque"]);
    }  
    
    $include = array("denomination", "tireur", "beneficiaire", "adresse", "code_postal", "ville", "pays", "num_tel", "num_cpte", "id_banque", "iban_cpte");
     
    $fields = array();
    
    $fields[] = array(
            "name" => "denomination", 
            "type" => TYPC_TXT, 
            "label" => "Denomination", 
            "src" => $benef["denomination"] , 
            "required" => true);
    
    $fields[] = array(
            "name" => "tireur",
            "type" => TYPC_BOL,
            "label" => "Tireur",
            "src" => $benef["tireur"] ,
            "required" => true);
    
    $fields[] = array(
            "name" => "beneficiaire",
            "type" => TYPC_BOL,
            "label" => "Bénéficiaire",
            "src" => $benef["beneficiaire"] ,
            "required" => true);
    
    $fields[] = array(
            "name" => "id_banque",
            "type" => TYPC_TXT,
            "label" => "Banque",
            "src" => $banque,
            "required" => false);
    
    $fields[] = array(
            "name" => "adresse",
            "type" => TYPC_TXT,
            "label" => "Adresse",
            "src" => $benef["adresse"] ,
            "required" => false);
    
    $fields[] = array(
            "name" => "code_postal",
            "type" => TYPC_TXT,
            "label" => "Code Postal",
            "src" => $benef["code_postal"] ,
            "required" => false);
    
    $fields[] = array(
            "name" => "ville",
            "type" => TYPC_TXT,
            "label" => "Ville",
            "src" => $benef["ville"] ,
            "required" => false);
    
    $fields[] = array(
            "name" => "pays",
            "type" => TYPC_TXT,
            "label" => "Pays",
            "src" => $pays ,
            "required" => false);
    
    $fields[] = array(
            "name" => "num_tel",
            "type" => TYPC_TXT,
            "label" => "N° de téléphone",
            "src" => $benef["num_tel"] ,
            "required" => false);
    
    $fields[] = array(
            "name" => "num_cpte",
            "type" => TYPC_TXT,
            "label" => "N° de compte",
            "src" => $benef["num_cpte"] ,
            "required" => false);
    
    $fields[] = array(
            "name" => "iban_cpte",
            "type" => TYPC_TXT,
            "label" => "N° IBAN",
            "src" => $benef["iban_cpte"] ,
            "required" => false);
        

    // Add the fields  
    foreach ($fields as $field) 
    {      
        $myForm->addField($field["name"], _($field["label"]), $field["type"], $field["src"]);       
        $myForm->setFieldProperties($field["name"], FIELDP_IS_LABEL, true);
        $myForm->setFieldProperties($field["name"], FIELDP_IS_REQUIRED, $field["required"]);    
    }   
    
    $ordre = array("denomination", "tireur", "beneficiaire", "id_banque", "num_cpte", "iban_cpte", "adresse", "code_postal", "ville", "pays", "num_tel");    
    $myForm->setOrder(NULL, $ordre);  
    
    //Boutons
    $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_BUTTON);
    
    // Pas de modification de tireur/benef en remote :
    /*
    if ($SESSION_VARS['type_recherche'] != 'a') { //on ne permet pas la modification en cas d'affichage
        $myForm->addFormButton(1, 3, "modifier", _("Modifier"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("modifier", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value = 'Tib-6'; document.ADForm.id.value=$id;"));
        $myForm->setFormButtonProperties("modifier", BUTP_CHECK_FORM, false);
    }
    */
    
    $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT, array("onclick" => "window.close();"));
    $myForm->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => "validateSearch();"));
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    //code Javascript qui alimente les champs de la page appelante
    $field_name = $SESSION_VARS['field_name'];
    $field_id = $SESSION_VARS['field_id'];
    $nomBenef = $benef['denomination'];
    $JScode1 = "
              function validateSearch()
            {
              window.opener.document.ADForm.$field_name.value = \"$nomBenef\";
              window.opener.document.ADForm.$field_id.value = \"$id\";
              window.close();
            }
              function remiseABlanc()
            {
              document.ADForm.denomination.value='';
              document.ADForm.ville.value='';
              document.ADForm.pays.value='';
              document.ADForm.id_banque.value='';
            }
              ";
    $myForm->addJS(JSP_FORM, "JS1", $JScode1);

    $myForm->buildHTML();
    echo $myForm->getHTML();
    
    // Commit transaction
    $pdo_conn->commit();
}

/* }}} */

/* {{{ Tib-6 : Modification d'un tireur bénéficiaire */
else if ($ecran == 'Tib-6') {
    global $global_remote_id_agence;
    
    // Begin remote transaction
    $pdo_conn->beginTransaction();

    $myForm = new HTML_GEN2(_("Tireur/Bénéficiaire sélectionné"));
    $myForm->addHiddenType("id", $id);

    //Affichage des champs d'encodage
    $exclude = array("id");
    $myForm->addTable("tireur_benef", OPER_EXCLUDE, $exclude);

    $fill = new FILL_HTML_GEN2();
    $fill->addFillClause("infoBenef", "tireur_benef");
    $fill->addCondition("infoBenef", "id", $id);
    $fill->addManyFillFields("infoBenef", OPER_EXCLUDE, $exclude);
    $fill->fill($myForm);

    if ($SESSION_VARS['type_recherche'] == 'b') { //si on modifie un bénéficiaire, certaines données sont obligatoires et d'autres inchangeables.
        $myForm->setFieldProperties("beneficiaire", FIELDP_DEFAULT, true);
        $myForm->setFieldProperties("beneficiaire", FIELDP_IS_LABEL, true);
    }

    if ($SESSION_VARS['type_recherche'] == 't') { //si on modifie un tireur, certaines données sont obligatoires et d'autres inchangeables.
        $myForm->setFieldProperties("denomination", FIELDP_IS_LABEL, true);
//      $myForm->setFieldProperties("id_banque", FIELDP_IS_REQUIRED, true);
        $myForm->setFieldProperties("tireur", FIELDP_DEFAULT, true);
        $myForm->setFieldProperties("tireur", FIELDP_IS_LABEL, true);
    }

    if ($SESSION_VARS['type_recherche'] == 'm') { //si on modifie un tireur, certaines données sont obligatoires et d'autres inchangeables.
        $myForm->setFieldProperties("denomination", FIELDP_IS_LABEL, true);
        $myForm->setFieldProperties("id_banque", FIELDP_IS_REQUIRED, true);
    }
    $myForm->setFieldProperties("pays", FIELDP_IS_REQUIRED, true);
    
    // Init class
    $TireurBenefObj = new TireurBenef($pdo_conn, $global_remote_id_agence);

    $benef = $TireurBenefObj->getTireurBenefDatas($id);
    
    // Destroy object
    unset($TireurBenefObj);

    $ordre = array("denomination", "tireur", "beneficiaire", "id_banque", "num_cpte", "iban_cpte", "adresse", "code_postal", "ville", "pays", "num_tel");
    $myForm->setOrder(NULL, $ordre);

    $JS_check =
            "if (document.ADForm.num_cpte.value == '' && document.ADForm.iban_cpte.value == '')
  {
    msg += '- " . _("Les champs \"N° de compte\" et/ou \"N° IBAN\" doivent être renseignés") . "\\n';
    ADFormValid = false;
  }";

    if ($SESSION_VARS['type_recherche'] == 'b') {
        $myForm->addJS(JSP_BEGIN_CHECK, "test", $JS_check);
    }

    //Boutons
    $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_BUTTON);
    $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT, array("onclick" => "window.close();"));
    $myForm->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value= 'Tib-7';"));
    //On remet a blanc les champs, sinon ceux-ci servent de critères de recherche dans l'écran Tib-2.
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    //code Javascript qui alimente les champs de la page appelante
    $field_name = $SESSION_VARS['field_name'];
    $field_id = $SESSION_VARS['field_id'];
    $nomBenef = $benef['denomination'];
    $JScode1 = "
              function remiseABlanc()
            {
              document.ADForm.denominationvalue='';
              document.ADForm.ville.value='';
              document.ADForm.pays.value='';
            }
              ";
    $myForm->addJS(JSP_FORM, "JS1", $JScode1);

    $myForm->buildHTML();
    echo $myForm->getHTML();
    
    // Commit transaction
    $pdo_conn->commit();
}
/* }}} */

/* {{{ Tib-7 : Confirmation de l'encodage */
else if ($ecran == 'Tib-7') {
    global $global_remote_id_agence;
    
    // Begin remote transaction
    $pdo_conn->beginTransaction();

    //Mise à jour Tireur/Bénéficiaire
    if ($tireur || $SESSION_VARS['type_recherche'] == 't')
        $DATA['tireur'] = 't';
    else
        $DATA['tireur'] = 'f';
    if ($beneficiaire || $SESSION_VARS['type_recherche'] == 'b')
        $DATA['beneficiaire'] = 't';
    else
        $DATA['beneficiaire'] = 'f';
    if ($SESSION_VARS['type_recherche'] == 'b')
        $DATA['beneficiaire'] = 't';
    if (isset($denomination))
        $DATA['denomination'] = $denomination;
    if (isset($adresse))
        $DATA['adresse'] = $adresse;
    if (isset($code_postal))
        $DATA['code_postal'] = $code_postal;
    if (isset($ville))
        $DATA['ville'] = $ville;
    if (isset($pays))
        $DATA['pays'] = $pays;
    if (isset($num_tel))
        $DATA['num_tel'] = $num_tel;
    if (isset($num_cpte))
        $DATA['num_cpte'] = $num_cpte;
    if (isset($iban_cpte))
        $DATA['iban_cpte'] = $iban_cpte;
    if (isset($banque))
        $DATA['id_banque'] = $banque;
    foreach ($DATA as $key => $value) {
        if ($DATA[$key] == '')
            unset($DATA[$key]);
    }
     
    try{
        // Init class
        $TireurBenefObj = new TireurBenef($pdo_conn, $global_remote_id_agence);
        
        $result = $TireurBenefObj->updateTireurBenef($id, $DATA);
        
        // Destroy object
        unset($TireurBenefObj);

        $pdo_conn->commit(); // Commit
    }
    catch (PDOException $e) {
        $pdo_conn->rollBack(); // Roll back

        signalErreur(__FILE__, __LINE__, __FUNCTION__, $e->getMessage());		
    }

    if ($result->errCode == NO_ERR) {
        $benef = getTireurBenefDatas($id);
        $SESSION_VARS['tib']['nomTireur'] = $benef['denomination'];
        $SESSION_VARS['tib']['id'] = $id;
        $myForm = new HTML_message(_("Confirmation de la modification d'un tireur/bénéficiaire"));
        $msg = _("La modification du tireur/bénéficiaire s'est déroulé avec succès") . " ";
        $myForm->setMessage($msg);
        $myForm->addButton(BUTTON_OK, "Tib-8");
        $myForm->buildHTML();
        echo $myForm->HTML_code;
    }
}
/* }}} */

/* {{{ Tib-8 : Envoi du résultat à l'écran appelant */
else if ($ecran == 'Tib-8') {
    
    // Begin remote transaction
    $pdo_conn->beginTransaction();
    
    if ($SESSION_VARS['type_recherche'] != 'r') {
        $field_name = $SESSION_VARS['field_name'];
        $field_id = $SESSION_VARS['field_id'];
        $nomTireur = $SESSION_VARS['tib']['nomTireur'];
        $id = $SESSION_VARS['tib']['id'];
    } else {
        $DATA['beneficiaire'] = 't';
        $DATA['tireur'] = 'f';
        if (isset($denomination))
            $DATA['denomination'] = $denomination;
        if (isset($adresse))
            $DATA['adresse'] = $adresse;
        if (isset($code_postal))
            $DATA['code_postal'] = $code_postal;
        if (isset($ville))
            $DATA['ville'] = $ville;
        if (isset($pays))
            $DATA['pays'] = $pays;
        if (isset($num_tel))
            $DATA['num_tel'] = $num_tel;
        if (isset($type_piece))
            $DATA['type_piece'] = $type_piece;
        if (isset($num_piece))
            $DATA['num_piece'] = $num_piece;
        if (isset($lieu_delivrance))
            $DATA['lieu_delivrance'] = $lieu_delivrance;
        foreach ($DATA as $key => $value) {
            if ($DATA[$key] == '')
                unset($DATA[$key]);
        }
        $field_name = $SESSION_VARS['field_name'];
        $field_id = $SESSION_VARS['field_id'];
        $nomTireur = $DATA['denomination'];
        $id = 0;
        $SESSION_VARS['tib'] = $DATA;
    }

    echo "
  <script type=\"text/javascript\">
  opener.document.ADForm.$field_name.value = '$nomTireur';
  opener.document.ADForm.$field_id.value = '$id';
  window.close();
  </script>";
    
    // Commit transaction
    $pdo_conn->commit();
}
/* }}} */
/* {{{  Tib-9 : mettre les informations du bénéficiaire dans une variable de session */
else if ($ecran == 'Tib-9') {
    
    // Begin remote transaction
    $pdo_conn->beginTransaction();
    
    if ($SESSION_VARS['type_recherche'] == 'r') {
        $DATA['beneficiaire'] = 't';
        $DATA['tireur'] = 'f';
        if (isset($denomination))
            $DATA['denomination'] = $denomination;
        if (isset($adresse))
            $DATA['adresse'] = $adresse;
        if (isset($code_postal))
            $DATA['code_postal'] = $code_postal;
        if (isset($ville))
            $DATA['ville'] = $ville;
        if (isset($pays))
            $DATA['pays'] = $pays;
        if (isset($num_tel))
            $DATA['num_tel'] = $num_tel;
        if (isset($type_piece))
            $DATA['type_piece'] = $type_piece;
        if (isset($num_piece))
            $DATA['num_piece'] = $num_piece;
        if (isset($lieu_delivrance))
            $DATA['lieu_delivrance'] = $lieu_delivrance;
        foreach ($DATA as $key => $value) {
            if ($DATA[$key] == '')
                unset($DATA[$key]);
        }
    }
    
    // Commit transaction
    $pdo_conn->commit();
}
/* }}} */
else
    signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

require("lib/html/HtmlFooter.php");

// Fermer la connexion BDD
unset($pdo_conn);

?>