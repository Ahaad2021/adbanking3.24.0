<?php
/**
 * Génération de l'écran de bilettage
 *
 * NB : Role semblable à billetage.php à la seule différence qu'on suppose ici qu'on vient d'un fichier avec plusieurs liens billetage. On renonce donc à se souvenir des valeus précédemment entrées.
 * Cet écran doit recevoir en POST :
 * - $shortName : Le nom du champ du formulaire à partir duquel on vient
 * - $direction :
 *   - in si l'argent entre
 *   - out si l'argent sort
 *   - in_cc si l'argent entre dans la caisse centrale
 *   - out_cc si l'argent sort de la caisse centrale 
 *   - caisse_seule si il s'agit d'un comptage de billet d'une caisse
 * $devise    : La devise dans laquelle le billetage est exprimé
 * @package Guichet
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/misc/VariablesSession.php';
require_once 'lib/html/HtmlHeader.php';
require_once 'lib/dbProcedures/billetage.php';
require_once 'lib/dbProcedures/parametrage.php';

setMonnaieCourante($devise);

$valeurs = recupeBillet($devise);
$myForm = new HTML_GEN2(_("Billettage"));

// Selon que l'argent entre ou sort, les libellés des deux colonnes seront différents

if ($direction == "in_cc") {
  $libel1 = _("Reçu de la caisse centrale");
  $libel2 = _("Rendu à la caisse centrale");
} else if ($direction == "out_cc") {
  $libel1 = _("Remis à la caisse centrale");
  $libel2 = _("Rendu par la caisse centrale");
} else
  signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La valeur de 'Direction' n'est pas reconnue ($direction)"


// Construction du tableau de billettage
$xtHTML  = "<TABLE align=\"center\" width=\"40%\">";
$xtHTML .= "\n\t<tr bgcolor=\"$colb_tableau\"><td></td><td><b>$libel1</b></td><td><b>$libel2</b></td></tr>";

$disable = 'disabled';  // Petite astuce pour disabler la cellule 'rendu' du billet le plus élevé
while (list($key,$value) = each($valeurs)) {
  $xtHTML .= "\n\t<tr bgcolor=\"$colb_tableau\"><td>".afficheMontant($value)."</td><td><INPUT type=\"text\" name=\"bil_$key\" value=\"\" size=\"5\"></td><td><INPUT type=\"text\" name=\"rend_bil_$key\" value=\"\" size=\"5\" $disable></td></tr>";
  $disable = '';
}

$xtHTML .= "\n</TABLE>";

$myForm->addHTMLExtraCode("tableau", $xtHTML);
$myForm->addFormButton(1, 1, "ok", _("OK"), TYPB_SUBMIT);
$myForm->addFormButton(1, 2, "ann", _("Annuler"), TYPB_BUTTON);
$myForm->setFormButtonProperties("ann", BUTP_JS_EVENT, array("onclick" => "window.close();"));
$validateCode = "somme = 0;";

$valeurs = recupeBillet($devise);
while (list($key, $value) = each($valeurs)) {
  $validateCode .= "somme += document.ADForm.bil_".$key.".value * ".$value."; ";
  $validateCode .= "somme -= document.ADForm.rend_bil_".$key.".value * ".$value."; ";
}
$validateCode .= "if (somme < 0) {alert('"._("Le montant est négatif")."');return false;} checkForm(); if (ADFormValid == false) return false; opener.document.ADForm.".$shortName.".focus();opener.document.ADForm.".$shortName.".value = formateMontant(somme);opener.document.ADForm.".$shortName.".blur();window.close();";
$myForm->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick" => $validateCode));
// Initialisation des champs en fonction des champs hidden de l'appelant
$checkJS = "";  // Script de vérification du formulaire
$initJS = "if (opener.document.ADForm.$shortName.value != '') {";  // Script d'initialisation des valeurs
$valeurs = recupeBillet($devise);
while (list($key, $value) = each($valeurs)) {
  $checkJS .= "if (!isIntPos(document.ADForm.bil_$key.value) || !isIntPos(document.ADForm.rend_bil_$key.value)) {msg += '- ".sprintf(_("Le nombre de billet de %s doit être un entier positif"),$value)."\\n';ADFormValid=false;}\n";
}
$initJS .= "}";
$myForm->addJS(JSP_FORM, "initJS", $initJS);
$myForm->addJS(JSP_BEGIN_CHECK, "checkJS", $checkJS);


$myForm->buildHTML();

echo $myForm->getHTML();


?>