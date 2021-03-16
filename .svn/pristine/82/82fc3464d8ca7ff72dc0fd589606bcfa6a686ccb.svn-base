<?php

// error_reporting(E_ALL);
// ini_set("display_errors", "on");

/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Affichage des informations sur un mandataire
 * @author Antoine Guyette
 * @package Externe
 * */

require_once('lib/html/HTML_GEN2.php');
require_once 'lib/misc/VariablesSession.php';
require_once('lib/misc/VariablesGlobales.php');

require("lib/html/HtmlHeader.php");

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

echo "
<script type=\"text/javascript\">
opener.onfocus= react;
function react()
{
window.focus();
}
</script>";

if ($ecran == NULL) {
    global $global_remote_id_agence;

    // Begin remote transaction
    $pdo_conn->beginTransaction();

    // Génération du titre
    $myForm = new HTML_GEN2(_("Informations sur le mandataire"));

    // Init class
    $EpargneObj = new Epargne($pdo_conn, $global_remote_id_agence);

    if ($id_cpte != NULL) {
        $MANDATS = $EpargneObj->getMandats($id_cpte);
        // $MANDATS = getMandats($id_cpte);

        if ($MANDATS != NULL) {
            foreach ($MANDATS as $key => $value) {
                if ($value['type_pouv_sign'] != 2) {
                    unset($MANDATS[$key]);
                } else {
                    $MANDATS[$key] = $EpargneObj->getInfosMandat($key);
                }
            }
        }
    } else {
        $MANDATS[$id_mandat] = $EpargneObj->getInfosMandat($id_mandat);
    }

    // Destroy object
    unset($EpargneObj);

    if ($MANDATS != NULL) {
        foreach ($MANDATS as $key => $value) {
            $myForm->addField("denomination_$key", _("Dénomination"), TYPC_TXT);
            $myForm->setFieldProperties("denomination_$key", FIELDP_DEFAULT, $value['denomination']);
            $myForm->setFieldProperties("denomination_$key", FIELDP_IS_LABEL, true);

            $myForm->addField("type_piece_id_$key", _("Type de la pièce d'identité"), TYPC_TXT);
            $myForm->setFieldProperties("type_piece_id_$key", FIELDP_DEFAULT, $value['libel_type_piece_id']);
            $myForm->setFieldProperties("type_piece_id_$key", FIELDP_IS_LABEL, true);

            $myForm->addField("num_piece_id_$key", _("Numéro de la pièce d'identité"), TYPC_TXT);
            $myForm->setFieldProperties("num_piece_id_$key", FIELDP_DEFAULT, $value['num_piece_id']);
            $myForm->setFieldProperties("num_piece_id_$key", FIELDP_IS_LABEL, true);

            $myForm->addField("lieu_piece_id_$key", _("Lieu de délivrance de la pièce d'identité"), TYPC_TXT);
            $myForm->setFieldProperties("lieu_piece_id_$key", FIELDP_DEFAULT, $value['lieu_piece_id']);
            $myForm->setFieldProperties("lieu_piece_id_$key", FIELDP_IS_LABEL, true);

            $myForm->addField("date_piece_id_$key", _("Date de délivrance de la pièce d'identité"), TYPC_DTE);
            $myForm->setFieldProperties("date_piece_id_$key", FIELDP_DEFAULT, $value['date_piece_id']);
            $myForm->setFieldProperties("date_piece_id_$key", FIELDP_IS_LABEL, true);

            $myForm->addField("date_exp_piece_id_$key", _("Date d'expiration de la pièce d'identité"), TYPC_DTE);
            $myForm->setFieldProperties("date_exp_piece_id_$key", FIELDP_DEFAULT, $value['date_exp_piece_id']);
            $myForm->setFieldProperties("date_exp_piece_id_$key", FIELDP_IS_LABEL, true);

            /*
            // gestion d'images
            $IMAGES = imageLocationPersExt($value['id_pers_ext']);
            if (is_file($IMAGES['photo_chemin_local']))
                $SESSION_VARS['gpe']['photo'] = $IMAGES['photo_chemin_web'];
            else
                $SESSION_VARS['gpe']['photo'] = "/adbanking/images/travaux.gif";
            if (is_file($IMAGES['signature_chemin_local']))
                $SESSION_VARS['gpe']['signature'] = $IMAGES['signature_chemin_web'];
            else
                $SESSION_VARS['gpe']['signature'] = "/adbanking/images/travaux.gif";

            $myForm->addField("photo_$key", _("Photo"), TYPC_IMG);
            $myForm->setFieldProperties("photo_$key", FIELDP_IMAGE_URL, $SESSION_VARS['gpe']['photo']);

            $myForm->addField("signature_$key", _("Signature"), TYPC_IMG);
            $myForm->setFieldProperties("signature_$key", FIELDP_IMAGE_URL, $SESSION_VARS['gpe']['signature']);
            */
            $ClientObj = new Client($pdo_conn, $global_remote_id_agence);

            //Recupere le photo et signature pour client en operation dans agence externe
            $IMGS = $ClientObj->getImagesClient($value['id_client']);

            if ($IMGS['photo']!=null || $IMGS['photo']!=''){
                $SESSION_VARS['gpe']['photo'] = $IMGS['photo'];
            }
            else{
                $IMGS_PER_EXT = $ClientObj->getImagesPersExt($value['id_pers_ext']);
                if ($IMGS_PER_EXT['photo']!=null || $IMGS_PER_EXT['photo']!=''){
                    $SESSION_VARS['gpe']['photo'] = $IMGS_PER_EXT['photo'];
                }
                else{
                    $SESSION_VARS['gpe']['photo'] = "/adbanking/images/travaux.gif";
                }
            }

            if ($IMGS['signature']!=null || $IMGS['signature']!=''){
                $SESSION_VARS['gpe']['signature'] = $IMGS['signature'];
            }
            else{
                $IMGS_PER_EXT = $ClientObj->getImagesPersExt($value['id_pers_ext']);
                if ($IMGS_PER_EXT['signature']!=null || $IMGS_PER_EXT['signature']!=''){
                    $SESSION_VARS['gpe']['signature'] = $IMGS_PER_EXT['signature'];
                }
                else{
                    $SESSION_VARS['gpe']['signature'] = "/adbanking/images/travaux.gif";
                }
            }

            $myForm->addField("photo_$key", _("Photo"), TYPC_IMG);
            $myForm->setFieldProperties("photo_$key", FIELDP_IMAGE_URL, $SESSION_VARS['gpe']['photo']);

            $myForm->addField("signature_$key", _("Signature"), TYPC_IMG);
            $myForm->setFieldProperties("signature_$key", FIELDP_IMAGE_URL, $SESSION_VARS['gpe']['signature']);

            $myForm->addHTMLExtraCode("html_$key", "<br />");
        }
    }

    // Boutons
    $myForm->addFormButton(1, 1, "annuler", _("Annuler"), TYPB_BUTTON);
    $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT, array("onclick" => "window.close();"));
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    // Génération du code HTML
    $myForm->buildHTML();
    echo $myForm->getHTML();

    // Commit transaction
    $pdo_conn->commit();
}
else
    signalErreur(__FILE__, __LINE__, __FUNCTION__);

require("lib/html/HtmlFooter.php");

// Fermer la connexion BDD
unset($pdo_conn);
?>