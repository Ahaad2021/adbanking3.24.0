<?php

/**
 * HTML_message
 * Classe permettant de générer le code nécessaire à l'affichage d'une page message
 * @package Ifutilisateur
 */

require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/Erreur.php';

//Styles de boutons
define("BUTTON_OK", "BUTTON_OK");
define("BUTTON_CANCEL", "BUTTON_CANCEL");
define("BUTTON_OUI", "BUTTON_OUI");
define("BUTTON_NON", "BUTTON_NON");

class button_msg {
  var $shortName = "";
  var $longName = "";
  var $prochainEcran = "";
}

class HTML_message {
  //Attributs publics
  var $HTML_code ; //Code HTML généré

  //Attributs privés
  var $titre = ""; //String contenant le titre de la page
  var $message = ""; //String contenant le message à afficher
  var $buttons = array(); //Boutons définit
  var $state = 1; //Etat 1 : on définit le contenu; lorsque buildHTML => Etat 2 : plus aucune méthode n'est acceptée
  var $indent = 0;
  var $shortNameUsed = array(); //Liste des noms court déjà utilisés

  //Méthodes publiques
  function HTML_message($titre = "") { //Constructeur
    $this->titre = $titre;
  }

  function setTitle($value) { //Permet de définir le titre

    if ($this->state != 1) signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Etat courant ne permet pas appel de cette méthode"
    $this->titre = $value;
  }

  function setMessage($value) { //Permet de définir le message

    if ($this->state != 1) signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Etat courant ne permet pas appel de cette méthode"

    $this->message = $value;

  }

  function addButton($style, $prochain_ecran) { //Permet d'ajouter un bouton prédéfini

    if ($this->state != 1) signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Etat courant ne permet pas appel de cette méthode"

    if ($style == BUTTON_OK) {
      if (in_array("BOK", $this->shortNameUsed))
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le Bouton OK existe déjà"
      $b = new button_msg();
      $b->shortName = "BOK";
      $b->longName = _("OK");
      array_push($this->shortNameUsed, "BOK");
    }
    elseif ($style == BUTTON_CANCEL) {
      if (in_array("BCN", $this->shortNameUsed))
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le Bouton CANCEL existe déjà"
      $b = new button_msg;
      $b->shortName = "BCN";
      $b->longName = _("Annuler");
      array_push($this->shortNameUsed, "BCN");
    }
    elseif ($style == BUTTON_OUI) {
      if (in_array("BOUI", $this->shortNameUsed))
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le Bouton OUI existe déjà"
      $b = new button_msg;
      $b->shortName = "BOUI";
      $b->longName = _("Oui");
      array_push($this->shortNameUsed, "BOUI");
    }
    elseif ($style == BUTTON_NON) {
      if (in_array("BNON", $this->shortNameUsed))
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le Bouton NON existe déjà"
      $b = new button_msg;
      $b->shortName = "BNON";
      $b->longName = _("Non");
      array_push($this->shortNameUsed, "BNON");
    }
    else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Création d'un style de bouton prédéfini inconnu"

    $b->prochainEcran = $prochain_ecran;
    array_push($this->buttons, $b);

  }

  function addCustomButton($shortName, $longName, $prochain_ecran) { //Permet d'ajouter un bouton personnalisé
    if ($this->state != 1)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Etat courant ne permet pas appel de cette méthode"

    if (in_array($shortName, $this->shortNameUsed))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le Bouton ".$shortName."  existe déjà"

    $b = new button_msg();
    $b->shortName = $shortName;
    $b->longName = $longName;
    $b->prochainEcran = $prochain_ecran;
    array_push($this->shortNameUsed, $shortName);
    array_push($this->buttons, $b);

  }

  function buildHTML() { //Génère le code HTML

    global $colb_tableau;
    global $PHP_SELF;

    if ($this->state != 1)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'état courant ne permet pas d'exécuter buildHTML()"
    //On commence par générer le titre
    if ($this->titre != "") $this->HTML_code = "\n<H1 align=\"center\">" . $this->titre ."</H1>\n" . "<br><br>\n";

    //Génération de l'entête de formulaire
    $this->incIndent();

    $this->HTML_code .= $this->getIndent() . "<FORM NAME=\"ADForm\" ACTION=\"$PHP_SELF\" METHOD=\"POST\" onsubmit=\"if (! isSubmit){ isSubmit=true; return true;} else {return false;}\">\n";

    //on génère un tableau à 2 lignes, la première contenant le message et la seconde les boutons
    $this->incIndent();
    $this->HTML_code .=  $this->getIndent() . "<TABLE ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">\n";
    $this->incIndent();
    $this->HTML_code .=  $this->getIndent() . "<TR BGCOLOR=$colb_tableau>\n";
    $this->incIndent();
    $this->HTML_code .=  $this->getIndent() . "<TD ALIGN=\"CENTER\" COLSPAN=" .
                         (string)(sizeof($this->buttons)) . ">\n";
    $this->incIndent();
    $this->HTML_code .=  $this->getIndent() . "<P ALIGN =\"CENTER\">" . $this->message;
    $this->HTML_code .= "</P>\n";
    $this->decIndent();
    $this->HTML_code .=  $this->getIndent() . "</TD>\n";
    $this->decIndent();
    $this->HTML_code .=  $this->getIndent() . "</TR>\n";

    //lignes vides
    $this->HTML_code .=  $this->getIndent() . "<TR BGCOLOR=$colb_tableau>\n";
    $this->incIndent();
    $this->HTML_code .=  $this->getIndent() . "<TD ALIGN=\"CENTER\" COLSPAN=" .
                         (string)(sizeof($this->buttons)) . " > &nbsp;\n";
    $this->HTML_code .=  $this->getIndent() . "</TD>\n";
    $this->decIndent();
    $this->HTML_code .=  $this->getIndent() . "</TR>\n";

    $this->HTML_code .=  $this->getIndent() . "<TR BGCOLOR=$colb_tableau>\n";
    $this->incIndent();
    $this->HTML_code .=  $this->getIndent() . "<TD ALIGN=\"CENTER\" COLSPAN=" .
                         (string)(sizeof($this->buttons)) . " > &nbsp;\n";
    $this->HTML_code .=  $this->getIndent() . "</TD>\n";
    $this->decIndent();
    $this->HTML_code .=  $this->getIndent() . "</TR>\n";

    $this->HTML_code .=  $this->getIndent() . "<TR BGCOLOR=$colb_tableau>\n";
    $this->incIndent();
    $this->HTML_code .=  $this->getIndent() . "<TD ALIGN=\"CENTER\" COLSPAN=" .
                         (string)(sizeof($this->buttons)) . " > &nbsp;\n";
    $this->HTML_code .=  $this->getIndent() . "</TD>\n";
    $this->decIndent();
    $this->HTML_code .=  $this->getIndent() . "</TR>\n";

    //ligne des boutons
    $this->HTML_code .=  $this->getIndent() . "<TR BGCOLOR=$colb_tableau>\n";

    $this->incIndent();
    reset($this->buttons);
    //pour chaque bouton dans la liste. Les custom button sont submit par défaut
    while (list(,$btn) = each($this->buttons)) {
      $this->HTML_code .=  $this->getIndent() . "<TD ALIGN=\"CENTER\" COLSPAN=\"1\">\n";
      $this->incIndent();
      if ($btn->shortName == "BOK") $this->HTML_code .=  $this->getIndent() .
            "<INPUT TYPE=SUBMIT NAME=\"".$btn->shortName ."\" VALUE=\"".$btn->longName."\"";
      else if ($btn->shortName == "BCN") $this->HTML_code .=  $this->getIndent() .
            "<INPUT TYPE=RESET NAME=\"".$btn->shortName ."\" VALUE=\"".$btn->longName."\"";
      else $this->HTML_code .=  $this->getIndent() .
                                  "<INPUT TYPE=SUBMIT NAME=\"".$btn->shortName ."\" VALUE=\"".$btn->longName."\"";
      if ($btn->prochainEcran != "") $this->HTML_code .= " ONCLICK=\"assign('".$btn->prochainEcran."');\">\n";
      else $this->HTML_code .= ">\n";
      $this->decIndent();
      $this->HTML_code .=  $this->getIndent() . "</TD>\n";
    }

    $this->decIndent();

    $this->HTML_code .=  $this->getIndent() . "</TR>\n";

    $this->decIndent();

    $this->HTML_code .=  $this->getIndent() . "</TABLE>\n";

    $this->decIndent();

    //fin du FORM
    $this->HTML_code .= $this->getIndent()."<INPUT type=\"hidden\" name=\"prochain_ecran\">\n";
    $this->HTML_code .= $this->getIndent()."<INPUT type=\"hidden\" id=\"m_agc\" name=\"m_agc\">\n";
    $this->HTML_code .=  $this->getIndent() . "</FORM>\n";
    $this->HTML_code .= "<SCRIPT type=\"text/javascript\">\n";
    $this->HTML_code .= "isSubmit = false;\n";
    $this->HTML_code .= "</SCRIPT>\n";

    //on change l'état de l'objet
    $this->state = 2;

  }

  function getHTML() { // Renvoie le code HTML généré
    if ($this->state == 2)
      return $this->HTML_code;
    else
      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("L'état courant de HTML_message ne permet pas d'exécuter getHTML()"));
  }

  function show() { // Imprime le code HTML généré
    if ($this->state == 1)
      // Le code HTML n'est pas encore généré
      $this->buildHTML();
    if ($this->state == 2)
      echo $this->getHTML();
    else
      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("L'état courant de l'objet HTML_message ne permet pas d'exécuter show()"));
  }

  //Méthodes privées
  function incIndent() {//incrémente l'indentation
    ++$this->indent;
  }

  function decIndent() {//décrémente l'indentation
    --$this->indent;
  }

  function getIndent() {//renvoie le nombre de TAB pour l'indentation

    for ($retour = "", $i = 1; ($i <= $this->indent); $retour .= "\t", ++$i);
    return $retour;
  }
}

?>