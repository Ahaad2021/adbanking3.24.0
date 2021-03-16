<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Affichage des informations sur un mandataire
 * @author Antoine Guyette
 * @package Externe
 **/

require_once('lib/html/HTML_GEN2.php');
require_once('lib/misc/VariablesGlobales.php');
require("lib/html/HtmlHeader.php");


echo "
<script type=\"text/javascript\">
opener.onfocus= react;
function react()
{
window.focus();
}
</script>";

if ($ecran == NULL) {
  // Génération du titre
  $myForm = new HTML_GEN2(_("Informations sur le mandataire"));

  if ($id_cpte != NULL) {
    $MANDATS = getMandats($id_cpte);

    if ($MANDATS != NULL) {
      foreach ($MANDATS as $key=>$value) {
        if ($value['type_pouv_sign'] != 2) {
          unset($MANDATS[$key]);
        } else {
          $MANDATS[$key] = getInfosMandat($key);
        }
      }
    }
  } else {
    $MANDATS[$id_mandat] = getInfosMandat($id_mandat);
  }

  if ($MANDATS != NULL) {
    foreach ($MANDATS as $key=>$value) {
      $myForm->addField("denomination_$key", _("Dénomination"), TYPC_TXT);
      $myForm->setFieldProperties("denomination_$key", FIELDP_DEFAULT, str_replace("&apos;", "'", $value['denomination']));
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
      // gestion d'images
      $IMAGES = imageLocationPersExt($value['id_pers_ext']);
		  if (is_file($IMAGES['photo_chemin_local']))
		    $SESSION_VARS['gpe']['photo'] = $IMAGES['photo_chemin_web'];
		  else
		    $SESSION_VARS['gpe']['photo'] ="/adbanking/images/travaux.gif";
		  if (is_file($IMAGES['signature_chemin_local']))
		    $SESSION_VARS['gpe']['signature'] = $IMAGES['signature_chemin_web'];
		  else
		    $SESSION_VARS['gpe']['signature'] = "/adbanking/images/travaux.gif";

      $myForm->addField("photo_$key", _("Photo"), TYPC_IMG);
      $myForm->setFieldProperties("photo_$key", FIELDP_IMAGE_URL, $SESSION_VARS['gpe']['photo']);

      $myForm->addField("signature_$key", _("Signature"), TYPC_IMG);
      $myForm->setFieldProperties("signature_$key", FIELDP_IMAGE_URL,  $SESSION_VARS['gpe']['signature'] );


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
} else signalErreur(__FILE__,__LINE__,__FUNCTION__);

require("lib/html/HtmlFooter.php");
?>