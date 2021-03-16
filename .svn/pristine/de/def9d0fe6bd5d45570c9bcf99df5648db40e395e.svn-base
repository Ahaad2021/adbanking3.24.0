<?php

/**
 * HTML_erreur
 * @copyright ADbanking
 * @author ADbanking
 * @since unknown
 * @package Ifutilisateur
 */

require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/Erreur.php';


//Styles de boutons
define("BUTTON_OK", "BUTTON_OK");
define("BUTTON_CANCEL", "BUTTON_CANCEL");

class erreurButton {
  var $shortName = "";
  var $longName = "";
  var $prochainEcran = "";
  var $jsProp = array();
}

/**
 * HTML_erreur
 *
 * Classe permettant de générer le code nécessaire à l'affichage d'une page erreur
 */
class HTML_erreur {
  //Attributs publics
  var $HTML_code; //Code HTML généré

  //Attributs privés
  var $titre = ""; //String contenant le titre de la page
  var $message = ""; //String contenant le message à afficher
  var $buttons = array(); //Boutons définit
  var $state = 1; //Etat 1 : on définit le contenu; lorsque buildHTML => Etat 2 : plus aucune méthode n'est acceptée
  var $Indent = 0; //
  var $UsedShortNames = array();

  //Méthodes publiques
  function HTML_erreur($titre = "") {
    $this->titre = $titre;
  }

  function setTitle($value) { //Permet de définir le titre
    if ($this->state != 1) signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'état courant ne permet pas l'appel de cette méthode"
    $this->titre = $value;
  }

  function setMessage($value) { //Permet de définir le message
    if ($this->state != 1) signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'état courant ne permet pas l'appel de cette méthode"
    $this->message = $value;
  }

  function addButton($style, $prochain_ecran, $jsProp=NULL) { // Permet d'ajouter un bouton prédéfini
    if ($this->state != 1) signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'état courant ne permet pas l'appel de cette méthode"
    switch ($style) {
    case BUTTON_OK:
      $btn_ok= new erreurButton();
      $btn_ok->shortName = "button_ok";
      $btn_ok->longName = _("OK");
      $btn_ok->prochainEcran = $prochain_ecran;
      if (is_array($jsProp))
        $btn_ok->jsProp = $jsProp;
      array_push($this->UsedShortNames,"button_ok");
      array_push($this->buttons, $btn_ok);
      break;

    case BUTTON_CANCEL:
      $btn_cancel= new erreurButton();
      $btn_cancel->shortName = "button_cancel";
      $btn_cancel->longName = _("Annuler");
      $btn_cancel->prochainEcran = $prochain_ecran;
      if (is_array($jsProp))
        $btn_cancel->jsProp = $jsProp;
      array_push($this->UsedShortNames,"button_cancel");
      array_push($this->buttons, $btn_cancel);
      break;

    default:
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Type de styles inconnu"
      break;
    }

  }


  function addCustomButton($shortName, $longName, $prochain_ecran, $jsProp=NULL) { // Permet d'ajouter un bouton personnalisé
    if ($this->state != 1)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'état courant ne permet pas l'appel de cette méthode"

    reset($this->UsedShortNames); //Met le pointeur en début de tableau
    while (list($id,$value) = each($this->UsedShortNames))
      if ($value == $shortName)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le bouton '$hortName' existe déjà"

    $btn = new erreurButton();
    $btn->shortName = $shortName;
    $btn->longName = $longName;
    $btn->prochainEcran = $prochain_ecran;
    if (is_array($jsProp))
      $btn->jsProp = $jsProp;

    array_push($this->UsedShortNames, $shortName);
    array_push($this->buttons, $btn);
  }

  function buildHTML() { //Génère le code HTML
    global $colb_tableau; // Couleur background du tableau
    global $colt_error;   // Couleur du texte d'erreur
    global $PHP_SELF;

    if ($this->state != 1) signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'état courant ne permet pas l'appel de cette méthode"

    $this->HTML_code = "\n";// le corps du formulaire
    $this->HTML_code.="<H1 align=\"center\">".$this->titre."<H1>\n";
    $this->HTML_code.="<BR><BR>\n";
    $this->HTML_code.="<FORM name=\"ADForm\" action=\"$PHP_SELF\" method=\"post\">\n";
    $this->HTML_code.=$this->getIndent()."<TABLE width=\"80%\" cellpadding=\"8\" align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\">\n";
    $this->incIndent();
    $this->HTML_code.=$this->getIndent()."<TR bgcolor=$colb_tableau>\n";
    $this->incIndent();
    $span = sizeof($this->UsedShortNames);
    $this->HTML_code.=$this->getIndent()."<TD align=\"left\" colspan=\"".$span."\">\n";
    $this->incIndent();
    $this->HTML_code.=$this->getIndent()."<P><FONT color=\"".$colt_error."\">".$this->message."</FONT>\n";
    $this->HTML_code.=$this->getIndent()."</P>\n";
    $this->decIndent();
    $this->HTML_code.=$this->getIndent()."</TD>\n";
    $this->decIndent();
    $this->HTML_code.=$this->getIndent()."</TR>\n";
    $this->HTML_code.=$this->getIndent()."<TR bgcolor=$colb_tableau>\n";
    $this->incIndent();
    $this->HTML_code.=$this->getIndent()."<TD align=\"left\" colspan=\"".$span."\">\n";
    $this->incIndent();
    $this->HTML_code.=$this->getIndent()."&nbsp;\n";
    $this->decIndent();
    $this->HTML_code.=$this->getIndent()."</TD>\n";
    $this->decIndent();
    $this->HTML_code.=$this->getIndent()."</TR>\n";
    $this->HTML_code.=$this->getIndent()."<TR bgcolor=$colb_tableau>\n";
    $this->incIndent();
    reset($this->buttons);
    while (list($key,$objet) = each($this->buttons)) {
      $this->HTML_code.=$this->getIndent()."<TD align=\"center\">\n";
      $this->incIndent();
      $this->HTML_code.= $this->getIndent()."<INPUT type=\"submit\" name=\"".$objet->shortName."\" value=\"".$objet->longName."\" onclick=\"assign('".$objet->prochainEcran."')\"";
      if (is_array($objet->jsProp)) {
        while (list($event,$JScode) = each($objet->jsProp)) {
          $this->HTML_code .= " $event=\"$JScode\"";
        }
      }
      $this->HTML_code .= ">\n";
      $this->decIndent();
      $this->HTML_code.=$this->getIndent()."</TD>\n";
    }

    $this->decIndent();
    $this->HTML_code.=$this->getIndent()."</TR>\n";
    $this->decIndent();
    $this->HTML_code.=$this->getIndent()."</TABLE>\n";
    $this->incIndent();
    $this->HTML_code.=$this->getIndent()."<INPUT type=\"hidden\" name=\"prochain_ecran\">\n";
    $this->incIndent();
    $this->HTML_code.=$this->getIndent()."<INPUT type=\"hidden\" id=\"m_agc\" name=\"m_agc\">\n";
    $this->HTML_code.="</FORM>\n";
    $this->state = 2;
  }

  function getHTML() { // Renvoie le code HTML généré
    if ($this->state == 2)
      return $this->HTML_code;
    else
      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("L'état courant de HTML_erreur ne permet pas d'exécuter getHTML()"));
  }

  function show() { // Imprime le code HTML de l'objet
    if ($this->state == 1)
      // Le code HTML n'est pas encore généré
      $this->buildHTML();
    if ($this->state == 2)
      echo $this->getHTML();
    else
      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("L'état courant de l'objet HTML_erreur ne permet pas d'exécuter show()"));
  }

  //Méthodes privées

  function incIndent() { //Ajoute une indentation
    ++$this->Indent;
  }

  function decIndent() { //Retire une indetation
    --$this->Indent;
  }

  function getIndent() { //Indente le code HTML
    $retour="";
    for ($i=1; $i<=$this->Indent; ++$i) $retour.="\t";
    return $retour;
  }

}

?>