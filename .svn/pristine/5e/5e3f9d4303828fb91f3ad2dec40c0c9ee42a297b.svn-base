<?php


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
if ($ecran == '' || $ecran == 'Gpe-10') {
    // Génération du titre
    $myForm = new HTML_GEN2(_("Selection d'un mandataire pour le groupe"));

    // Variables de session
    if ($SESSION_VARS['gpe']['denom'] == NULL) {
        $SESSION_VARS['gpe']['denom'] = $denom;
    }
    if ($SESSION_VARS['gpe']['pers_ext'] == NULL) {
        $SESSION_VARS['gpe']['pers_ext'] = $pers_ext;
    }

    if ($SESSION_VARS['gpe']['num_tel'] == NULL) {
        $SESSION_VARS['gpe']['num_tel'] = $num_tel;
    }

//    // Affichage des champs de recherche
//    $include = array("denomination", "date_naiss", "lieu_naiss", "ville", "pays");
//    $myForm->addTable("ad_pers_ext", OPER_INCLUDE, $include);
//    $myForm->setOrder(NULL, $include);
//    foreach ($include as $key=>$value) {
//        $myForm->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
//    }

    //liste des personnes non  mandataires
    $id_compte = $id_cpte;
    $info_mandataires = getlisteGSNonMandataire($id_compte);

    $myForm->addField("mandataire", _("Nom mandataire"), TYPC_LSB);
    foreach ($info_mandataires as $key=>$value) {
        $myForm->setFieldProperties("mandataire", FIELDP_ADD_CHOICES, array($key => $value));
    }
    $myForm->setFieldProperties("mandataire", FIELDP_IS_REQUIRED, true);




    // Boutons
    $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_BUTTON);
    $myForm->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value = 'Gpe-11';"));
    $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT, array("onclick" => "window.close();"));
    $myForm->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    $myForm->buildHTML();
    echo $myForm->getHTML();
}

else if ($ecran == 'Gpe-11') {


    $id_client_mandataire = $HTML_GEN_LSB_mandataire;
    $data_cli = getClientDatas($id_client_mandataire);
    $mod = str_replace("'", "&apos;", $data_cli['pp_nom']." ".$data_cli['pp_prenom']);
    $num_tel = $data_cli['num_tel'];
    $DATA = array('denomination' => $mod,  'id_client' => $id_client_mandataire);

    $result = ajouterPersonneExt($DATA);

    if ($result->errCode == NO_ERR) {
        $SESSION_VARS['gpe']['denomination'] = $mod;
        $SESSION_VARS['gpe']['id_pers_ext'] = $result->param['id_pers_ext'];
        $SESSION_VARS['gpe']['num_tel'] = $num_tel;
        $myForm = new HTML_message(_("Confirmation de l'ajout d'une personne extérieure"));
        $msg = _("L'ajout de la personne extérieure s'est déroulée avec succès ".$mod);
        $myForm->setMessage($msg);
        $myForm->addButton(BUTTON_OK, "Gpe-12");
        $myForm->buildHTML();
        echo $myForm->HTML_code;
    }
}
else if ($ecran == 'Gpe-12') {
    $denom = $SESSION_VARS['gpe']['denom'];
    $pers_ext = $SESSION_VARS['gpe']['pers_ext'];
    $num_tel = $SESSION_VARS['gpe']['num_tel'];

    if ($denomination == NULL) {
        $denomination = $SESSION_VARS['gpe']['denomination'];
    }

    if ($id_pers_ext == NULL) {
        $id_pers_ext = $SESSION_VARS['gpe']['id_pers_ext'];
    }

    if ($num_tel_client == NULL) {
        $numero = $SESSION_VARS['gpe']['num_tel'];
    }
    $denomination = str_replace('&apos;', "\'", $denomination);
    echo "
  <script type=\"text/javascript\">
  opener.document.ADForm.$denom.value = '$denomination';
  opener.document.ADForm.$pers_ext.value = $id_pers_ext;
  opener.document.ADForm.num_tel.value = '$numero';
  window.close();
  </script>";
}