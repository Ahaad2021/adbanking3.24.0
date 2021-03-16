<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Affichage des informations sur une personne externe
 * @author Nielsh
 * @package Externe
 **/

require_once('lib/misc/VariablesGlobales.php');
require("lib/html/HtmlHeader.php");
require_once('lib/html/HTML_GEN2.php');
require_once('lib/html/html_table_gen.php');
require_once('lib/html/FILL_HTML_GEN2.php');

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
  $myForm = new HTML_GEN2(_("Informations sur la relation"));

  $include = array('typ_rel');
  $myForm->addTable("ad_rel", OPER_INCLUDE, $include);

  $myFill = new FILL_HTML_GEN2();
  $myFill->addFillClause('rel_clause', 'ad_rel');
  $myFill->addCondition('rel_clause', 'id_rel', $id_rel);
  $myFill->addManyFillFields('rel_clause', OPER_INCLUDE, $include);
  $myFill->fill($myForm);
  $myForm->setFieldProperties('typ_rel', FIELDP_IS_LABEL, true);

  $include = array('denomination', 'date_naiss', 'lieu_naiss', 'adresse', 'code_postal', 'ville', 'pays', 'num_tel', 'type_piece_id', 'num_piece_id', 'lieu_piece_id');
  $myForm->addTable('ad_pers_ext', OPER_INCLUDE, $include);
  $myForm->setOrder(NULL, $include);
  $myForm->setFieldProperties("denomination", FIELDP_IS_REQUIRED, true);

  $myFill2 = new FILL_HTML_GEN2();
  $myFill2->addFillClause('pers_ext_clause', 'ad_pers_ext');
  $myFill2->addCondition('pers_ext_clause', 'id_pers_ext', $id_pers_ext);
  $myFill2->addManyFillFields('pers_ext_clause', OPER_INCLUDE, $include);
  $myFill2->fill($myForm);

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

  $myForm->addField("photo",_("Photographie"),TYPC_IMG);
  $myForm->setFieldProperties('photo', FIELDP_IMAGE_URL, $SESSION_VARS['gpe']['photo']);
  $myForm->setFieldProperties('photo', FIELDP_IS_LABEL, true);
  $myForm->addField("signature",_("Spécimen de signature"),TYPC_IMG);
  $myForm->setFieldProperties('signature', FIELDP_IMAGE_URL, $SESSION_VARS['gpe']['signature']);
  $myForm->setFieldProperties('signature', FIELDP_IS_LABEL, true);

  // Boutons
  $myForm->addFormButton(1, 1, "annuler", _("Annuler"), TYPB_BUTTON);
  $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT, array("onclick" => "window.close();"));
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $JS_1 = "document.getElementsByName('photo')[0].parentNode.onclick = '#';";
  $JS_1 .= "document.getElementsByName('signature')[0].parentNode.onclick = '#';";
  $myForm->addJS(JSP_FORM,"testdos",$JS_1);

  // Génération du code HTML
  $myForm->buildHTML();
  echo $myForm->getHTML();
} else signalErreur(__FILE__,__LINE__,__FUNCTION__);

require("lib/html/HtmlFooter.php");
?>