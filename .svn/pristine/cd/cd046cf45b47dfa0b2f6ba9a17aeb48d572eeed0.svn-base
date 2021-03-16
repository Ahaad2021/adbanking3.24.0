<?php

/**
 * HTML_GEN2 Classe de génération automatique des formulares
 * REM : La classe HTML_GEN n'est plus utilisée
 * @author TK - TF
 * @since janvier 2002
 * @package Ifutilisateur
 */

require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/VariablesSession.php';
require_once 'lib/misc/tableSys.php';
require_once 'lib/dbProcedures/interface.php';
require_once 'lib/html/calendrier.php';
require_once 'lib/html/html_table_gen.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/misc/access.php';
require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/multilingue/traductions.php';

// Constantes utilisées pour les types de champs
define("TYPC_TXT", "txt"); //Type texte, tout permis
define("TYPC_ARE", "are"); //Type text area
define("TYPC_INT", "int"); //Type entier
define("TYPC_INN", "inn"); //Type entier négatif
define("TYPC_DTE", "dte"); //Type date antérieure ou égale à la date du jour, format : jj/mm/aaaa
define("TYPC_DTG", "dtg"); //Type date, format : jj/mm/aaaa
define("TYPC_DTF", "dtf"); //Type date postérieure ou égale à la date du jour
define("TYPC_TEL", "tel"); //Type tel, sont acceptés : [0-9()+. ]+
define("TYPC_EMA", "ema"); //Type email, sont acceptés : string@string.string
define("TYPC_BOL", "bol"); //Type booléen, checkbox
define("TYPC_MNT", "mnt"); //Type montant, son acceptés : (-)?[0-9]+(\.[0-9]+)?
define("TYPC_DVR", "dev"); //Type devise: montant avec une listbox pour choisir la devise
define("TYPC_LSB", "lsb"); //Type listbox
define("TYPC_PWD", "pwd"); //Type mot de passe
define("TYPC_FLT", "flt"); //Type float
define("TYPC_PRC", "prc"); //Type pourcentage
define("TYPC_CNT", "cnt"); //Type container
define("TYPC_TTR", "ttr"); //Type texte traduit
define("TYPC_LTR", "ltr"); //Type listbox traduit
define("TYPC_TBL", "tbl"); //Type table HTML_TABLE_GEN
define("TYPC_IMG", "img"); //Type Image (Fichier Image dans le File system)
define("TYPC_FILE", "file"); //Type file (Fichier  dans le File system) 
// Constantes utilisées pour les types de boutons
define("TYPB_SUBMIT", "TYPB_SUBMIT");
define("TYPB_RESET", "TYPB_RESET");
define("TYPB_BUTTON", "TYPB_BUTTON");

// Constantes utilisées pour l'opérateur de l'insertion d'une table
define("OPER_INCLUDE", "OPER_INCLUDE");
define("OPER_EXCLUDE", "OPER_EXCLUDE");
define("OPER_NONE", "OPER_NONE");

// Constantes utilisées pour définir les propriétés du formulaire
define("FORMP_JS_EVENT", "FORMP_JS_EVENT"); //Value : array(evnt=>value)

// Constantes utilisées pour définir les propriétés d'un champ
define("FIELDP_JS_EVENT", "FIELDP_JS_EVENT"); //Value : array(evnt=>value)
define("FIELDP_SHORT_NAME", "FIELDP_SHORT_NAME"); //Value : string
define("FIELDP_LONG_NAME", "FIELDP_LONG_NAME"); //Value : string
define("FIELDP_EXCLUDE_CHOICES", "FIELDP_EXCLUDE_CHOICES"); //Value : array(id)
define("FIELDP_INCLUDE_CHOICES", "FIELDP_INCLUDE_CHOICES"); //Value : array(id)
define("FIELDP_ADD_CHOICES", "FIELDP_ADD_CHOICES"); //Value : array(id=>libellé)
define("FIELDP_ADD_CHOICES_TRAD", "FIELDP_ADD_CHOICES_TRAD"); //Value : array(id=>libellé->traduit)
define("FIELDP_ORDER_CHOICES", "FIELDP_ORDER_CHOICES"); //Value: array(id)
define("FIELDP_HAS_CHOICE_AUCUN", "FIELDP_HAS_CHOICE_AUCUN"); //Value : booléen
define("FIELDP_HAS_CHOICE_TOUS", "FIELDP_HAS_CHOICE_TOUS"); //Value : booléen
define("FIELDP_IS_LABEL", "FIELDP_IS_LABEL"); //Value : booléen
define("FIELDP_IS_READONLY", "FIELDP_IS_READONLY"); //Value : booléen
define("FIELDP_DEFAULT", "FIELDP_DEFAULT"); //Value : type simple (string, bool, ...)
define("FIELDP_FILL_ZERO", "FIELDP_FILL_ZERO"); //Value : entier
define("FIELDP_IS_REQUIRED", "FIELDP_IS_REQUIRED"); //Value : booléen
define("FIELDP_TYPE", "FIELDP_TYPE"); //Value : TYPC_xxx
define("FIELDP_HAS_CALEND", "FIELDP_HAS_CALEND"); //Value: booléen
define("FIELDP_CHECK", "FIELDP_CHECK"); //Value: booléen
define("FIELDP_HAS_BILLET", "FIELDP_HAS_BILLET"); //Value: booléen
define("FIELDP_NUMB_ROWS", "FIELDP_NUMB_ROWS"); //Value: naturel
define("FIELDP_WIDTH", "FIELDP_WIDTH"); //Value: naturel
define("FIELDP_SENS_BIL", "FIELDP_SENS_BIL"); //Value: constante définie SENS_BIL_???
define("FIELDP_CAN_MODIFY", "FIELDP_CAN_MODIFY"); //Value : booléen
define("FIELDP_DEVISE", "FIELDP_DEVISE"); //Value : code ISO d'une devise

// Constantes utilisées pour définir les propriétés d'un lien
define("LINKP_JS_EVENT", "LINKP_JS_EVENT"); //Value : array(evnt=>value)
define("LINKP_AXS", "LINKP_AXS"); //Value : entier
define("LINKP_KEY", "LINKP_KEY"); //Value : define
define("LINKP_PROCHAIN_ECRAN", "LINKP_PROCHAIN_ECRAN"); //Value : string

// Constantes utilisées pour définir les propriétés d'un boutton
define("BUTP_JS_EVENT", "BUTP_JS_EVENT"); //Value : array(evnt=>value)
define("BUTP_AXS", "BUTP_AXS"); //Value : entier
define("BUTP_KEY", "BUTP_KEY"); //Value : define
define("BUTP_CHECK_FORM", "BUTP_CHECK_FORM"); //Value : booléen
define("BUTP_PROCHAIN_ECRAN", "BUTP_PROCHAIN_ECRAN"); //Value : string

// Constantes utilisées pour définir les propriétés d'une cellule
define("CELP_SPAN", "CELP_SPAN"); //Value : entier

// Constantes utilisées pour définir les propriétés d'un HTMLExtraCode
define("HTMP_IN_TABLE", "HTMP_IN_TABLE"); //Value : booléen

//Touches reconnues pour les boutons; le nom court du bouton est renommé automatiquement vers la valeur associée à la constante
define("KEYB_ENTER", "enterButton");

//Touches reconnues pour les liens
define("KEYL_0", "link0Value");
define("KEYL_1", "link1Value");
define("KEYL_2", "link2Value");
define("KEYL_3", "link3Value");
define("KEYL_4", "link4Value");
define("KEYL_5", "link5Value");
define("KEYL_6", "link6Value");
define("KEYL_7", "link7Value");
define("KEYL_8", "link8Value");
define("KEYL_9", "link9Value");

// Constantes de position pour un block javascript
define("JSP_FORM", "JSP_FORM"); //En dehors de la fonction checkForm()
define("JSP_BEGIN_CHECK", "JSP_BEGIN_CHECK"); //Au début de la fonction checkForm()
define("JSP_END_CHECK", "JSP_END_CHECK"); //En fin de fonction checkForm()

// Constantes pour le sens du billetage
define ("SENS_BIL_IN", "in");          // Billets remis par le client
define ("SENS_BIL_OUT", "out");        // Billets remis au client
define ("SENS_BIL_CC_IN", "in_cc");    // Billets remis par la caisse centrale
define ("SENS_BIL_CC_OUT", "out_cc");  // Billets remis à la caise centrale
define ("SENS_BIL_CAISSE_SEULE", "caisse_seule");  // Billetage pour la saisie de l'encaisse

// Constantes utilisées par setOrder
define("ORDER_FIRST", NULL);  // On met les champs ordonnés au début de la liste
define("ORDER_LAST", -1);     // On met les champs ordonnés à la fin de la liste

class JS { //Block javascript
  var $ShortName = "";
  var $Code = "";
  var $Position = NULL;
}

class HiddenType { //Un champs invisible
  var $ShortName = "";
  var $Value = "";
}

class HTMLExtraCode { //Code HTML supplémentaire
  var $ShortName = "";
  var $Contenu = "";
  var $InTable = false;
}

class Cell { //Cellule du tableau des boutons/links
  var $Span = 0; //Convention : vaut 0 si appartient à un span et qu'il n'est pas la 1ère cellule du span
  var $ButtonOrLink = NULL;

  function Cell($buttonOrLink) {
    $this->Span = 1;
    $this->ButtonOrLink = $buttonOrLink;
  }
}

class Choice { //Choix d'un ListBox
  var $ShortName = ""; //Correspond à l'identificateur du choix
  var $LongName = "";

  function Choice($shortName, $longName) {
    $this->ShortName = $shortName;
    $this->LongName = $longName;
  }
}

class Button { //Bouton, soit associé à un champs, soit au formulaire (en bas de page)
  var $ShortName = "";
  var $LongName = "";
  var $Type = NULL;   // Types prévus : TYPB_*
  var $JSEvents = array();   // (Event => JSCode)
  var $CheckForm = false;   // Indique si le formulaire doit être vérifié lors du click bouton
  var $Key = ""; // Touche à laquelle est associée le bouton (cf. define)
  var $ProchainEcran = ""; //Nom du prochain écran
  var $Visible = true; //Le bouton est-il visible ?
}

class Link { //Lien, soit associé à un champs, soit au formulaire (en bas de page)
  var $ShortName = "";
  var $LongName = "";
  var $Href = "";
  var $JSEvents = array(); // (Event => JSCode)
  var $Key = ""; // Touche à laquelle est associée le lien
  var $ProchainEcran = ""; //Nom du prochain écran
  var $Visible = true; //Le lien est-il visible ?
}

class Field {
  var $Table = "";        // Nom court de la table à laquelle appartient le champs.
  var $IdRefField = NULL; // Si le champs est lié à un champs d'une table, ID de ce champs dans d_tablelsite
  var $ShortName = "";
  var $LongName = "";
  var $Type = NULL;   // Type est un type tel que défini ci-dessus (TYPC_???)
  var $DefaultValue = "";
  var $Label = false;   // Booléen indiquant si le champs est un label (non-modifiable) ou non (param DISABLED en HTML)
  var $ReadOnly = false;  // Boolean indiquant si le champ est en lecture seule (param READONLY en HTML)
  var $FieldModify = false;   // Booléen indiquant si la valeus par défaut du champ est modifiable ou non.
  var $Required = false;
  var $ListBox = array();  // Si type = listbox, ensemble des valeurs possibles (array(class Choice, ...))
  var $ListBoxIncExList = array(); // Si type = Listbox, liste des valeurs à inclure
  var $ListBoxIncEx = 0; // Si type = Listbox, prend la valeur 1 si on a fait un include, 2 si on a fait un exclude et 0 si on n'a rien fait.
  var $LinksAndButtons = array();   //Tableau mixte des liens/bouttons attachés à ce champs. (rang => Link/Button)
  var $FillZero = 0;      //SI TYPE=int : Nombre de chiffres du champs
  var $HasCalend = true;  //SI TYPE=dte : Insérer un calendrier ?
  var $NestedFields = array();  //SI TYPE=container : (position => Field)
  var $linkedField = NULL; //SI TYPE=MNT, lien vers un champ DVR; si TPYE=DVR, lien vers un champ MNT
  var $JSEvents = array();    // (Event => JSCode)
  var $Check = true; // La validité du champs doit-il être vérifié par checkForm() ?
  var $HasBillet = false; //SI TYPE=mnt : Insérer un billetage ?
  var $SensBillet = SENS_BIL_IN; //SI HasBillet = true : Indique le sens de la transaction tel que défini ci-dessus (SENS_BIL_??)
  var $HasAucun = true; //SI TYPE=lsb : y a-t-il un choix [Aucun]
  var $HasTous = false; //SI TYPE=lsb : y a-t-il un choix [Tous] NOTE:On ne peut pas avoir en même temps [Tous] et [Aucun]
  var $NumbRows = 3; //SI TYPE=are : nombre de lignes
  var $Width = 0; //Largeur du champs
  var $Devise = NULL; // SI TYPE=mnt ou dev: Code de la devise du montant
  var $reste = false; //Si champ de type DVR, vrai s'il y a un champ "reste"

  function lenDefaultValue($nb_espaces = 5) { // Renvoie la longueur du texte "Default Value", particulièrement utile pour les champs TTR
    if (!is_trad($this->DefaultValue))
      return max(16,$nb_espaces+strlen($this->DefaultValue),$this->Width);
    if ($this->Type	== TYPC_TTR)
      return max(16,$nb_espaces+$this->DefaultValue->get_traduction_max_len(),$this->Width);
    return max(16,$nb_espaces+strlen($this->DefaultValue->traduction()),$this->Width);
  }
}

class HTML_GEN2 {
  //--------------------Attributs publics--------------------
  var $HTMLTitle = "";
  var $HTMLFormHead = "";
  var $HTMLFormBody = "";
  var $HTMLFormButtons = "";
  var $HTMLFormFooter = "";

  //--------------------Attributs privés--------------------
  var $Title = "";

  var $FieldsAndHTMLExtraCode = array();  // Ensemble des champs à afficher (position => Field) : position à partir de 1
  var $HiddenTypes = array(); //Champs invisibles
  var $LinksAndButtons = array(); // (PosY => (PosX => Cell ) )
  var $JS = array(); //Code javascript
  var $JSEvents = array(); //(Event=>JSCode)
  var $State = 1;        /* Etats :
			    1 => Etat acceptation de champs : Toutes les opérations sont permises sauf getHTML();
				 si buildHTML() , passage état 2
			    2 => Seul getHTML() accepté
			 */

  var $ShortNames = array(); //Liste de tous les noms courts existants
  var $LinkAssignedKeys = array(); //Liste des touches déjà assignées à un lien (array[KEYL_*] = href associé)
  var $Indent = 0; //Niveau d'indentation du code HTML courant

  //--------------------Fonctions publiques--------------------

  function HTML_GEN2($title = "") {
    $this->Title = $title;
    return true;
  }

  function getIndent() {
    $retour = "";
    for ($i=0; $i<$this->Indent; ++$i) $retour .= "\t";
    return $retour;
  }

  function addIndent() {
    ++$this->Indent;
  }

  function delIndent() {
    --$this->Indent;
    if ($this->Ident < 0) signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Indentation négative !"
  }

  function setTitle($value) {
    $this->Title = $value;
  }

  //Fonctions publiques TF

  function addTable($nomTable, $operateur, $champs) {/* Paramètres entrants :
	- nom (court) de la table à insérer
	- opérateur qui va être appliqué aux champs suivants (cf. define)
	- champs qui vont être traités par l'opérateur

	Traitement :
	Insère les champs de la table en fin d'array en consultant d_tableliste
     */
    $rowset = getFieldsFromTable ($nomTable); // Récupère tous les champs de la table
    while ( $row = $rowset->fetchRow(DB_FETCHMODE_ASSOC) ) {
      switch ($operateur) {
      case OPER_INCLUDE:
        if (in_array($row["nchmpc"], $champs))
          $this->insertField($row, $nomTable);
        break;
      case OPER_EXCLUDE:
        if (!in_array($row["nchmpc"], $champs))
          $this->insertField($row, $nomTable);
        break;
      case OPER_NONE:
        $this->insertField($row, $nomTable);
        break;
      default:
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'opérateur $operateur n'est pas reconnu"
      }
    }
    return true;
  }

  /**
   * Crée un champ ListBox une liste trié dont les choix sont les enrégistrements de la table netrée en paramètre
   *
   * @param $shortName Nom court de la table entrée en paramètre
   * @param $longName Nom long de la table entrée en paramètre
   * @param $nomTable Nom de la table dans la BD
   * @param String $sortFlag type de trie;
   * $sortFlag=NULL  : trie alphabétique sur les libellés;
   * $sortFlag=sortString : trie alphabétique sur les libellés;
   * $sortFlag=sortNumeric : trie numérique sur l'index de la table;
   */
  function addTableRefField($shortName, $longName, $nomTable, $sortFlag=NULL) {
    $this->addField($shortName, $longName, TYPC_LSB);
    $choices = makeListFromTable($nomTable);
    if ($sortFlag != "sortNumeric")
      asort($choices);

    $this->setFieldProperties($shortName, FIELDP_ADD_CHOICES, $choices);
    return true;
  }

  function addField($shortName, $longName, $type, $defaultValue=NULL) {
    /* Paramètres entrants :
       - nom court du champs
       - nom long du champs
       - type du champs (cf. define)

       Traitement :
       Insère le champs en fin d'array Fields
    */

    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "buildHTML a déjà été appelé"
    // Vérifier qu'aucun objet ne porte le même nom court.
    if (in_array($shortName, $this->ShortNames)) {
      debug(sprintf(_("L'objet %s existe déjà !"),$shortName));
      signalErreur(__FILE__,__LINE__,__FUNCTION__); //
    }
    $f = new Field;
    $f->ShortName = $shortName;
    if (is_trad($longName))
      $longName = $longName->traduction();
    $f->LongName = $longName;
    // Vérifier que le type est valide
    if (!in_array($type, array(TYPC_TXT, TYPC_ARE, TYPC_FLT, TYPC_INT,TYPC_INN, TYPC_DTE, TYPC_DTG, TYPC_DTF, TYPC_TEL, TYPC_EMA, TYPC_BOL, TYPC_MNT, TYPC_CNT, TYPC_LSB, TYPC_PWD, TYPC_PRC, TYPC_DVR, TYPC_TTR,TYPC_TBL, TYPC_IMG,TYPC_FILE)))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le type '$type' n'existe pas pour le champs $shortName!"
    $f->Type = $type;

    // Si le champ est un montant, la devise par défaut est la devise de référence
    if (($type == TYPC_MNT) || ($type == TYPC_DVR)) {
      global $global_monnaie_courante;
      $f->Devise = $global_monnaie_courante;
    }
    // Insertion de ce champs dans la liste des champs de la classe
    array_push($this->FieldsAndHTMLExtraCode, $f);
    // Renseignement de la liste des noms
    array_push($this->ShortNames, $f->ShortName);

    if ($defaultValue != NULL)
      $this->setFieldProperties($shortName, FIELDP_DEFAULT, $defaultValue);

    return true;
  }

  function addHTMLExtraCode ($shortName, $HTML) {
    /* Paramètres entrants :
       - nom court du morceau d'ExtraHTML
       - code HTML à insérer
    */
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("buildHTML a déjà été appelé"));
    // Vérifier qu'aucun objet ne porte le même nom court.
    if (in_array($shortName, $this->ShortNames))
      signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'objet %s existe déjà !"), $shortName));
    // Construction du HTMLExtraCode
    $h = new HTMLExtraCode;
    $h->ShortName = $shortName;
    $h->Contenu = $HTML;
    array_push($this->FieldsAndHTMLExtraCode, $h);
    // Ajout dans la liste des noms courts;
    array_push($this->ShortNames, $shortName);
    return true;
  }

  function &addHTMLTable($shortName, $nbColonnes, $style = '') {
    // Paramètres entrants:
    // - nom court associé à la table
    // - Nombre de colonnes de la table
    // - Style de la table
    // Paramètre sortant : référence vers la table créée.

    // On encapsule la table dans un objet HTML_EXTRACODE
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("buildHTML a déjà été appelé"));
    // Vérifier qu'aucun objet ne porte le même nom court.
    if (in_array($shortName, $this->ShortNames))
      signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'objet %s existe déjà !"), $shortName));
    // Construction du HTMLExtraCode
    $h = new HTMLExtraCode;
    $h->ShortName = $shortName;
    $h->Contenu = new HTML_TABLE_table($nbColonnes, $style);
    array_push($this->FieldsAndHTMLExtraCode, $h);
    // Ajout dans la liste des noms courts;
    array_push($this->ShortNames, $shortName);

    return $h->Contenu;
  }

  function addLink($fieldShortName, $shortName, $longName, $href) {
    /* Paramètres entrants :
       - nom court du champs auquel il sera associé
       - nom court du lien
       - nom long du lien
       - référence du lien
    */
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("buildHTML a déjà été appelé"));
    $f = &$this->getField($fieldShortName, $this->FieldsAndHTMLExtraCode); // Tente de récupérer le champs concerné.
    if ($f == NULL)
      signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Le champ %s n'existe pas"), $fieldShortName));
    // Vérifier qu'aucun objet ne porte le mlême nom court.
    if (in_array($shortName, $this->ShortNames))
      signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'objet %s existe déjà !"), $shortName));
    // Construction du lien
    $l = new Link;
    $l->ShortName = $shortName;
    $l->LongName = $longName;
    $l->Href = $href;
    // Insertion du lien
    array_push($f->LinksAndButtons, $l);
    // Ajouter dans la lsite des noms
    array_push($this->ShortNames, $shortName);
    return true;
  }

  function addHiddenType($shortName, $value = "")
  // Ajoute un champs hidden dans le formulaire
  {
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("buildHTML a déjà été appelé"));
    // Vérifier que le shortName n'a pas été utilisé
    if (in_array($shortName, $this->ShortNames))
      signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'objet %s existe déjà !"), $shortName));
    $h = new HiddenType;
    $h->ShortName = $shortName;
    $h->Value = $value;
    array_push($this->HiddenTypes, $h);
    array_push($this->ShortNames, $shortName);
  }

  function addButton($fieldShortName, $shortName, $longName, $type) {
    /* Paramètres entrants :
       - nom court du champs auquel il sera associé
       - nom court du boutton
       - nom long du boutton
       - type du boutton (cf. define)
    */
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("buildHTML a déjà été appelé"));
    $f = & $this->getField($fieldShortName, $this->FieldsAndHTMLExtraCode); // Tente de récupérer le champs concerné.
    if ($f == NULL)
      signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Le champ %s n'existe pas"), $fieldShortName));
    // Vérifier qu'aucun objet ne porte le même nom court.
    if (in_array($shortName, $this->ShortNames))
      signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'objet %s existe déjà !"), $shortName));
    // Construction du bouton
    $b = new Button;
    $b->ShortName = $shortName;
    $b->LongName = $longName;
    if (!in_array($type, array(TYPB_SUBMIT, TYPB_RESET, TYPB_BUTTON)))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le type $type n'existe pas"
    $b->Type = $type;
    if ($type == TYPB_SUBMIT)
      $b->CheckForm = true;
    // Insertion du bouton
    array_push($f->LinksAndButtons, $b);
    // Ajouter dans la liste des noms.
    array_push($this->ShortNames, $shortName);
    return true;
  }

  function addFormButton($posY, $posX, $shortName, $longName, $type) {
    /* Paramètres entrants :
     - nom court du boutton
       - nom long du boutton
       - type du boutton
       - position Y du boutton
       - position X du boutton
    */
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "buildHTML a déjà été appelé"
    // Vérifier qu'aucun objet ne porte le même nom court.
    if (in_array($shortName, $this->ShortNames))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'objet $shortName existe déjà !"
    // Vérifie qu'un bouton n'est pas déjà présent à l'endroit demandé
    if (($this->LinksAndButtons[$posY][$posX]->ButtonOrLink != NULL))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Un bouton existe déjà à la position $posY, $posX"
    // Vérifie qu'on ne se trouve pas déjà dans unz zone de span
    if ($this->isInSpan ($posY, $posX))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La cellule se trouve dans un zone de span"
    // Construction du bouton
    $b = new Button;
    $b->ShortName = $shortName;
    $b->LongName = $longName;
    if (!in_array($type, array(TYPB_SUBMIT, TYPB_RESET, TYPB_BUTTON)))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le type $type n'existe pas"
    $b->Type = $type;
    if ($type == TYPB_SUBMIT)
      $b->CheckForm = true;
    // Création de la cellule
    $myCell = new Cell($b);
    // Insertion de la cellule
    $this->LinksAndButtons[$posY][$posX] = $myCell;
    // Ajouter dans la liste des noms.
    array_push($this->ShortNames, $shortName);
    return true;
  }

  function addFormLink($posY, $posX, $shortName, $longName, $href) {
    /* Paramètres entrants :
       - nom court du lien
       - nom long du lien
       - référence du lien
       - position Y du lien
       - position X du lien
    */
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "buildHTML a déjà été appelé"
    // Vérifier qu'aucun objet ne porte le même nom court.
    if (in_array($shortName, $this->ShortNames))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'objet $shortName existe déjà !"
    // Vérifie qu'un bouton n'est pas déjà présent à l'endroit demandé
    if (($this->LinksAndButtons[$posY][$posX]->ButtonOrLink != NULL))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Un bouton existe déjà à la position $posY, $posX"
    // Vérifie qu'on ne se trouve pas déjà dans unz zone de span
    if ($this->isInSpan ($posY, $posX))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La cellule se trouve dans un zone de span"
    // Construction du lien
    $l = new Link;
    $l->ShortName = $shortName;
    $l->LongName = $longName;
    $l->Href = $href;
    // Création de la cellule
    $myCell = new Cell($l);
    // Insertion de la cellule
    $this->LinksAndButtons[$posY][$posX]= $myCell;
    // Ajouter dans la liste des noms.
    array_push($this->ShortNames, $shortName);
    return true;
  }

  function addJS($position, $shortName, $JSCode) {
    /* Paramètre entrant :
       - Nom court identifiant le bout de code
       - Position (choix parmi constantes)
       - Code JavaScript; si destiné à être intégré à checkForm, il faut mettre à jour les variables msg et
         ADFormValid.
    */
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "buildHTML a déjà été appelé"
    // Vérifier qu'aucun objet ne porte le même nom court.
    if (in_array($shortName, $this->ShortNames))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'objet $shortName existe déjà !"
    //Vérifier que la position soit connue
    if (! in_array($position, array(JSP_FORM, JSP_BEGIN_CHECK, JSP_END_CHECK)))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Position JS '$position' inconnue !"
    //Insère réellement l'objet
    $myJS = new JS();
    $myJS->ShortName = $shortName;
    $myJS->Position = $position;
    $myJS->Code = $JSCode;
    array_push($this->JS, $myJS);
    array_push($this->ShortNames, $shortName);
    return true;
  }

  function setFormProperties($property, $value) {
    /* Paramètre entrant
       - propriété que l'on veut modifier (cf. define)
       - valeur de la propriété
    */
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "buildHTML a déjà été appelé"
    switch ($property) {
    case FORMP_JS_EVENT:
      while (list($event, $code) = each($value)) {
        $event = strtolower($event);
        $this->JSEvents[$event] .= $code;
      }
      break;
    default:
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La propriété $property n'existe pas"
    }
    return true;
  }

  /** Règle une propriété d'un champ
   * @param str $shortName Le nom du champ
   * @param str $property La propriété à modifier
   * @param str $value La nouvelle valeur de la propriété
   */
  function setFieldProperties($shortName, $property, $value) {
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("buildHTML a déjà été appelé"));

    if ($shortName == "*") {
      foreach ($this->ShortNames AS $poubelle => $sn)
      $this->setFieldProperties($sn,$property,$value);
      return;
    }
    if (is_array($shortName)) {
      foreach ($shortName AS $poubelle => $sn)
      $this->setFieldProperties($sn,$property,$value);
      return;
    }

    $f = &$this->getField($shortName, $this->FieldsAndHTMLExtraCode); // Tente de récupérer le champs concerné.
    if ($f == NULL)
      signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Le champ %s n'existe pas"), $shortName));
    switch ($property) {
    case FIELDP_JS_EVENT:
      reset ($value);
      while (list($event, $code) = each($value)) {
        $event = strtolower($event);
        $f->JSEvents[$event] .= $code;
      }
      break;
    case FIELDP_SHORT_NAME:
      // Vérifier qu'aucun objet ne porte le même nom court.
      if (in_array($value, $this->ShortNames))
        signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Renommage de l'objet %s, le nouveau nom %s existe déjà !"), $shortName, $value));
      // Retire le nom de la liste des noms courts
      $this->removeFromShortNameList($shortName);
      // Changer la propriété
      $f->ShortName = $value;
      // Ajouter le nouveau nom court à la liste des noms courts
      array_push($this->ShortNames, $value);
      break;
    case FIELDP_LONG_NAME:
      $f->LongName = $value;
      break;
    case FIELDP_EXCLUDE_CHOICES:
      // On vérifie que c'est bien une ListBox
      if ($f->Type != TYPC_LSB)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs $shortName n'est pas une listBox"

      // On vérifie si on n'a pas déjà fait un include pour ce champs
      if ($f->ListBoxIncEx == 1)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Un IncludeChoices a déjà été effectué sur le champs $shortName"

      // Si le champs est lié à une table, on n'a pas encore généré la liste des choix, on va donc utiliser la structure ListBoxIncExList à la place
      if ($f->Table != '') {
        // Comme on fait un exclude_choices, on enregistre les valeurs dans ListBoxIncExList
        $f->ListBoxIncEx = 2;
        foreach ($value as $excludeName)
        array_push($f->ListBoxIncExList, $excludeName);
      } else
        // Sinon, on supprime les objets Choice de la liste des choix
      {
        foreach($f->ListBox AS $key => $myChoice)
        if (in_array($myChoice->ShortName, $value))
          unset($f->ListBox[$key]);
      }
      break;
    case FIELDP_INCLUDE_CHOICES:
      // On vérifie que c'est bien une ListBox
      if ($f->Type != TYPC_LSB)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs $shortName n'est pas une listBox"

      // On vérifie si on n'a pas déjà fait un exclude pour ce champs
      if ($f->ListBoxIncEx == 2)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Un ExcludeChoices a déjà été effectué sur le champs $shortName"

      // Si le champs est lié à une table, on n'a pas encore généré la liste des choix, on va donc utiliser la structure ListBoxIncExList à la place
      if ($f->Table != '') {
        // Comme on fait un include_choices, on en registre les valeurs dans ListBoxIncExList
        $f->ListBoxIncEx = 1;
        foreach ($value AS $includeName)
        array_push($f->ListBoxIncExList, $includeName);
      } else {
        // Sinon, on supprime les objets Choice de la lsite des choix
        foreach($f->ListBox AS $key => $myChoice) {
          if (!in_array($myChoice->ShortName, $value)) unset($f->ListBox[$key]);
        }
      }
      break;
    case FIELDP_ADD_CHOICES:
      if ($f->Type != TYPC_LSB)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs $shortName n'est pas une listBox"
      foreach ($value as $ident => $libel) {
        // Construction du Choix
        $c = new Choice($ident, $libel);
        // Insertion dans la lsite des choix
        array_push($f->ListBox, $c);
      }
     
      break;
    case FIELDP_ORDER_CHOICES:
      // On vérifie que c'est bien une ListBox
      if ($f->Type != TYPC_LSB)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs $shortName n'est pas une listBox"
      reset($value);
      while (list($index, $name) = each($value)) {
        $pos = $this->getChoicePosition ($name, $f->ListBox);
        // Swap des deux éléments
        $tmp = $f->ListBox[$pos];
        $f->ListBox[$pos] = $f->ListBox[$index];
        $f->ListBox[$index] = $tmp;
      }
      break;
    case FIELDP_HAS_CHOICE_AUCUN:
      // On vérifie que c'est bien une ListBox
      if ($f->Type != TYPC_LSB)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs $shortName n'est pas une listBox"
      $f->HasAucun = $value;
      break;
    case FIELDP_HAS_CHOICE_TOUS:
      // On vérifie que c'est bien une ListBox
      if ($f->Type != TYPC_LSB)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs $shortName n'est pas une listBox"
      $f->HasTous = $value;
      break;
    case FIELDP_IS_LABEL:
      $f->Label = $value;
      if ($f->Type == TYPC_DTE || $f->Type == TYPC_DTG || $f->Type == TYPC_DTF) {
        // Spprimer le calendrier
        $f->HasCalend = false;
      }
      $f->Width = $f->lenDefaultValue();
      $f->Check = !$value;
      break;

    case FIELDP_IS_READONLY:
      $f->ReadOnly = $value;
      break;

    case FIELDP_CAN_MODIFY:
      $f->FieldModify = $value;
      break;

    case FIELDP_DEFAULT:
      if ($f->Type == TYPC_DTE  || $f->Type == TYPC_DTG || $f->Type == TYPC_DTF) {
        // si c'est une date, effectuer la conversion
        if (!isPHPDate($value))
          $value = pg2phpDate($value);
        $value = localiser_date($value);
      }
      if ($f->Type == TYPC_TTR)
        if (!is_trad($value)&&($value!=NULL)){
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

      $f->DefaultValue = $value;
      $f->Width = $f->lenDefaultValue();
      break;
    case FIELDP_ADD_CHOICES_TRAD:
      if ($f->Type == TYPC_LSB){
      	  $f->ListBox = Array();
	      foreach ($value as $ident => $libel) {
	        $libel = new Trad($libel);
	         // Construction du Choix
	        $c = new Choice($ident, $libel->traduction());
	        // Insertion dans la lsite des choix
	        array_push($f->ListBox, $c);
	      }
      }
      break;
    case FIELDP_FILL_ZERO:
      if ($f->Type != TYPC_INT)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs $shortName n'est pas un entier"
      $f->FillZero = $value;
      break;
    case FIELDP_IS_REQUIRED:
      $f->Required = $value;
      break;
    case FIELDP_TYPE:
      if (!in_array($value, array(TYPC_TXT, TYPC_ARE, TYPC_FLT, TYPC_INT,TYPC_INN, TYPC_DTE, TYPC_DTG, TYPC_DTF, TYPC_TEL, TYPC_EMA, TYPC_BOL, TYPC_MNT, TYPC_CNT, TYPC_LSB, TYPC_PWD, TYPC_PRC, TYPC_TTR, TYPC_TBL)))
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le type $value n'existe pas"
      $f->Type = $value;
      break;
    case FIELDP_HAS_CALEND:
      // Vérifier que le type est bien une date
      if ($f->Type != TYPC_DTE && $f->Type != TYPC_DTG && $f->Type != TYPC_DTF)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs $shortName n'est pas uen date"
      $f->HasCalend = $value;
      break;
    case FIELDP_CHECK:
      $f->Check = $value;
      break;
    case FIELDP_HAS_BILLET:
      if ($f->Type != TYPC_MNT)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs $shortName n'est pas un montant"
      // Billettage
      global $global_billet_req;
      if ($global_billet_req) {
        $f->HasBillet = $value;
        if ($value == true)
          $f->ReadOnly = true;
      }
      break;
    case FIELDP_NUM_ROWS:
      if ($f->Type != TYPC_ARE)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs $shortName doit être de type Textarea"
      $f->NumRows = $value;
      break;
    case FIELDP_WIDTH:
      $f->Width = $value;
      break;
    case FIELDP_SENS_BIL:
      // Vérifie que le type est bien montant et qu'il y a un billetage
      if ($f->Type != TYPC_MNT || $f->HasBillet == false)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs $shortName doit être de type Montant avec le billetage activé"
      // Vérifier que la valeur fournie est une constante valide
      if (!in_array($value, array(SENS_BIL_IN, SENS_BIL_OUT, SENS_BIL_CC_IN, SENS_BIL_CC_OUT, SENS_BIL_CAISSE_SEULE)))
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La constante $value n'est pas un sens de billet correct"
      // On peut assigner
      $f->SensBillet = $value;
      break;
    case FIELDP_DEVISE:
      if (($f->Type != TYPC_MNT) && ($f->Type != TYPC_DVR))
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs $shortName doit être de type Montant ou Devise pour recevoir une devise"
      $f->Devise = $value;
      break;
    case FIELDP_IMAGE_URL:
      if ($f->Type != TYPC_IMG)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs $shortName doit être de type Image pour recevoir une URL"
      $f->URL = $value;
      break;
    default:
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La propriété $property n'existe pas"
    }
  }

  function setHTMLExtraCodeProperties ($shortName, $property, $value) {
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "buildHTML a déjà été appelé"
    $h = &$this->getHTMLExtraCode($shortName, $this->FieldsAndHTMLExtraCode); // Tente de récupérer le champs concerné.
    if ($h == NULL) {
      debug(sprintf(_("Le champs %s n'existe pas"),$shortName));
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    switch ($property) {
    case HTMP_IN_TABLE:
      $h->InTable = $value;
      break;
    default:
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La propriété $property n'existe pas"
    }
  }

  function setHTMLTableProperties ($shortName, $property, $value) {
    $this->setHTMLExtraCodeProperties ($shortName, $property, $value);
  }

  function setLinkProperties($shortName, $property, $value) {
    /* Paramètre entrant
       - propriété que l'on veut modifier (cf. define)
       - valeur de la propriété
    */
    // Vérifie que le lien existe bel et bien
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "buildHTML a déjà été appelé"
    $l = &$this->getLinkOrButton($shortName, $this->FieldsAndHTMLExtraCode);
    if ($l == NULL)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le lien $shortName n'existe pas"
    if (get_class($l) != 'Link')
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'objet $shortName n'est pas un lien"
    switch ($property) {
    case LINKP_JS_EVENT:
      reset($value);
      while (list($event, $code) = each($value)) {
        $event = strtolower($event);
        $l->JSEvents[$event] .= $code;
      }
      break;
    case LINKP_AXS:
      global $global_profil_axs;
      // Vérifie que l'utilisateur a bien accès à la fonction associée à ce lien.
      // Si ce n'est pas le cas, on ôte le lien.
      if (! check_access($value)) {
        $l->Visible = false;
      }
      break;
    case LINKP_KEY:
      // Vérifier que l'argument est coorect
      if (!in_array($value, array(KEYL_1, KEYL_2, KEYL_3, KEYL_4, KEYL_5, KEYL_6, KEYL_7, KEYL_8, KEYL_9, KEYL_0)))
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La touche $value n'est pas définie"
      // Vérifier que la touche n'est pas déjà assignée par quelqu'un d'autre
      if (in_array($value, $this->LinkAssignedKeys))
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La touche $vaue a déjà été assignée"
      array_push($this->LinkAssignedKeys, $value);
      $l->Key = $value;
      break;
    case LINKP_PROCHAIN_ECRAN:
      $l->ProchainEcran = $value;
      break;
    default:
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La propiété $property n'existe pas"
    }
  }

  function setButtonProperties($shortName, $property, $value) {
    /* Paramètre entrant
       - propriété que l'on veut modifier (cf. define)
       - valeur de la propriété
    */
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "buildHTML a déjà été appelé"
    // Vérifie que le bouton existe bel et bien
    $b = &$this->getLinkOrButton($shortName, $this->FieldsAndHTMLExtraCode);
    if ($b == NULL)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le bouton $shortName n'existe pas"
    if (get_class($b) != 'Button')
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'objet $shortName n'est pas un bouton"
    switch ($property) {
    case BUTP_JS_EVENT:
      reset($value);
      while (list($event, $code) = each($value)) {
        $event = strtolower($event);
        $b->JSEvents[$event] .= $code;
      }
      break;
    case BUTP_AXS:
      global $global_profil_axs;
      // Vérifie que l'utilisateur a bien accès à la fonction associée à ce lien.
      // Si ce n'est pas le cas, on ôte le lien.
      if (! check_access($value)) {
        $b->Visible = false;
      }
      break;
    case BUTP_KEY:
      if (!in_array($value, array(KEYB_ENTER)))
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La touche $value n'est pas définie"
      if (in_array("KEYB_ENTER", $this->LinkAssignedKeys))
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La touche <ENTER> a déjà été assignée à un autre bouton"
      $b->Key = $value;
      /*$this->removeFromShortNameList($b->ShortName);
      $b->ShortName = "enterButton";
      array_push($this->ShortNames, $b->ShortName);*/
      break;
    case BUTP_CHECK_FORM:
      $b->CheckForm = $value;
      break;
    case BUTP_PROCHAIN_ECRAN:
      $b->ProchainEcran = $value;
      break;
    default:
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La propiété $property n'existe pas"
    }
  }

  function setFormButtonProperties($shortName, $property, $value) {
    /* Paramètre entrant
       - propriété que l'on veut modifier (cf. define)
       - valeur de la propriété
    */
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("buildHTML a déjà été appelé"));

    // Vérifie que le bouton existe bel et bien
    $b = &$this->getFormLinkOrButton($shortName);
    if ($b == NULL)
      signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Le bouton %s n'existe pas"), $shortName));
    if (get_class($b) != 'Button')
      signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'objet %s n'est pas un bouton"), $shortName));
    switch ($property) {
    case BUTP_JS_EVENT:
      reset($value);
      while (list($event, $code) = each($value)) {
        $event = strtolower($event);
        $b->JSEvents[$event] .= $code;
      }
      break;
    case BUTP_AXS:
      global $global_profil_axs;
      // Vérifie que l'utilisateur a bien accès à la fonction associée à ce lien.
      // Si ce n'est pas le cas, on ôte le lien.
      if (! check_access($value)) {
        $b->Visible = false;
      }
      break;
    case BUTP_KEY:
      if (!in_array($value, array(KEYB_ENTER)))
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La touche $value n'est pas définie"
      $b->Key = $value;
      break;
    case BUTP_CHECK_FORM:
      $b->CheckForm = $value;
      break;
    case BUTP_PROCHAIN_ECRAN:
      $b->ProchainEcran = $value;
      break;
    default:
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La propiété $property n'existe pas"
    }
  }

  function setFormLinkProperties($shortName, $property, $value) {
    /* Paramètre entrant
       - propriété que l'on veut modifier (cf. define)
       - valeur de la propriété
    */
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "buildHTML a déjà été appelé"
    $l = &$this->getFormLinkOrButton($shortName);
    if ($l == NULL)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le bouton $shortName n'existe pas"
    if (get_class($l) != 'Link')
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'objet $shortName n'est pas un lien"
    switch ($property) {
    case LINKP_JS_EVENT:
      reset($value);
      while (list($event, $code) = each($value)) {
        $event = strtolower($event);
        $l->JSEvents[$event] .= $code;
      }
      break;
    case LINKP_AXS:
      global $global_profil_axs;
      // Vérifie que l'utilisateur a bien accès à la fonction associée à ce lien.
      // Si ce n'est pas le cas, on ôte le lien.
      if (! check_access($value)) {
        if (!$this->removeFormCell($value))
          signalErreur(__FILE__,__LINE__,__FUNCTION__); // "!! Incohérences dans removeLinkOrButton !!"
        // Oter le nom court de la liste.
        $this->removeFromShortNameList($shortName);
      }
      break;
    case LINKP_KEY:
      // Vérifier que l'argument est coorect
      if (!in_array($value, array(KEYL_1, KEYL_2, KEYL_3, KEYL_4, KEYL_5, KEYL_6, KEYL_7, KEYL_8, KEYL_9, KEYL_0)))
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La touche $value n'est pas définie"
      // Vérifier que la touche n'est pas déjà assignée par quelqu'un d'autre
      if (in_array($value, $this->LinkAssignedKeys))
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La touche $vaue a déjà été assignée"
      array_push($this->LinkAssignedKeys, $value);
      $l->Key = $value;
      break;
    case LINKP_PROCHAIN_ECRAN:
      $l->ProchainEcran = $value;
      break;
    default:
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La propiété $property n'existe pas"
    }
  }

  function setFormCellProperties($posY, $posX, $property, $value) {
    /* Paramètre entrant
       - propriété que l'on veut modifier (cf. define)
       - valeur de la propriété
    */
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "buildHTML a déjà été appelé"
    // Vérifie que la cellule existe
    $myCell = $this->LinksAndButtons[$posY][$posX];
    if ($myCell == NULL)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); //  "La cellule ($posY, $posX) n'existe pas"
    switch ($property) {
    case CELP_SPAN:
      // Vérifier qu'il n'y a pas d'overlap avec d'autres spam
      if ($this->isInSpan ($posY, $posX))
        signalErreur(__FILE__,__LINE__,__FUNCTION__); //  "Il y a overlap sur un précédent span"
      // Vérifier que les cellules suivant le span sont bien vides
      $count = 1;
      while ($count < $value) {
        if ($this->LinksAndButtons[$posY][$posX+$count] != NULL)
          signalErreur(__FILE__,__LINE__,__FUNCTION__); //  "Il y a des objets dans la zone de span"
        $count++;
      }
      $this->LinksAndButtons[$posY][$posX]->Span = $value;
      break;
    default:
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La propiété $property n'existe pas"
    }
  }

  function setOrder($shortName, $order) {
    /* Paramètres entrants
       - nom du champ à partir duquel vont être triés les champs; NULL si début
       - array(shortName) indiquant l'ordre
    */
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("buildHTML a déjà été appelé"));
    if ($shortName == ORDER_FIRST)
      $index = 0;
    else if ($shortName == ORDER_LAST) {
      $index = sizeof($this->FieldsAndHTMLExtraCode) - sizeof($order);
    } else
      $index = $this->getFieldPosition($shortName) + 1;
    if ($index == -1)
      signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Le champ %s n'existe pas"), $shortName));
    reset($order);
    while (list(,$sName) = each($order)) {
      $pos = &$this->getFieldPosition ($sName);
      if ($pos == -1)
        signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Le champ %s n'existe pas"), $sName));

      $field = $this->getField($sName, $this->FieldsAndHTMLExtraCode);

      // Swap des deux éléments
      $tmp = $this->FieldsAndHTMLExtraCode[$pos];
      $this->FieldsAndHTMLExtraCode[$pos] = $this->FieldsAndHTMLExtraCode[$index];
      $this->FieldsAndHTMLExtraCode[$index] = $tmp;
      $index++;

      if (($field->Type == TYPC_MNT) && ($field->linkedField != NULL))
        //On colle le champ DVR juste après le champ MNT associé
        $index = $this->setOrder($sName,array($field->linkedField->ShortName));

      if (($field->Type == TYPC_DVR) && ($field->reste))
        // On colle le champ "reste" généré par HTML_GEN2 juste derrière le champ DVR
        $index = $this->setOrder($sName,array("HTML_GEN_dvr_${sName}_reste"));
    }

    return $index;
  }

  function makeNested($containerShortName, $shortName) {
    /* Paramètres entrants
       - nom du champs qui va contenir le champs
       - nom du champs à insérer
    */
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "buildHTML a déjà été appelé"
    // Vérifie si les deux champs existent et se trouvent bien au premier niveau
    $posCField = &$this->getFieldPosition($containerShortName);
    if ($posCField == -1)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs '$containerShortName' n'existe pas ou ne se trouve pas au niveau 1 ('$shortName')"
    if ($this->FieldsAndHTMLExtraCode[$posCField]->Type != TYPC_CNT)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le type du champs '$containerShortName' doit être TYPC_CNT"
    $posNField = &$this->getFieldPosition($shortName);
    if ($posNField == -1)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs '$shortName' n'existe pas ou ne se trouve pas au niveau 1"

    // Insertion du champs
    array_push($this->FieldsAndHTMLExtraCode[$posCField]->NestedFields, $this->FieldsAndHTMLExtraCode[$posNField]);
    unset($this->FieldsAndHTMLExtraCode[$posNField]);

    return true;
  }

  function linkFieldsChange($shortName1, $shortName2, $achat_vente, $type_change, $reste=false, $transac_multi_agence = false)
  /* Paramètres entrants
     - nom d'un champ de type TYPC_MNT
     - nom d'un champ de type TYPC_DVR
     Lie les valeurs des deux champs et permet d'appeler le popup pour le change
  */
  {
    // ETAPE 1 : Préconditions
    ///////////////////////////
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "buildHTML a déjà été appelé"

    $f_mnt = &$this->getField($shortName1, $this->FieldsAndHTMLExtraCode); // Tente de récupérer le champs concerné.
    $f_dvr = &$this->getField($shortName2, $this->FieldsAndHTMLExtraCode);
    if (($f_mnt == NULL) || ($f_dvr == NULL))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champ n'existe pas"

    // f1= champ de type MNT, f2= champ de type DVR
    if ($f_mnt->Type == TYPC_DVR) {
      $swap=$f_mnt;
      $f_mnt=$f_dvr;
      $f_dvr=$swap;
    }

    if (($f_mnt->Type != TYPC_MNT) || ($f_dvr->Type != TYPC_DVR))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il faut lier 1 champ montant avec 1 champ devise"

    if (($achat_vente != 'achat') && ($achat_vente != 'vente'))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le 3e paramètre doit être 'achat' ou 'vente'"

    if (!in_array($type_change, array(1,2)))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le 4e paramètre doit être 1 ou 2"

    // ETAPE 2: Traitement proprement dit
    //////////////////////////////////////

    // Association des deux champs
    $f_mnt->linkedField = $f_dvr;
    $f_dvr->linkedField = $f_mnt;
    $f_dvr->Devise	    = $f_mnt->Devise;

    $f_dvr->reste	    = $reste;

    $f_dvr_short = $f_dvr->ShortName;
    $f_mnt_short = $f_mnt->ShortName;
    $f_mnt_long  = $f_mnt->LongName;
    $f_mnt_devise= $f_mnt->Devise;

    $this->setFieldProperties($f_dvr_short,FIELDP_IS_LABEL,true);
    // On devra tout de meme vérifier ce champ
    $this->setFieldProperties($f_dvr_short,FIELDP_CHECK,true);

    // On ajoute au champ 'devise variable',le lien Changer et de son javascript
    // On ajoute aussi le javascript pour griser le champ dvr et assigner la valeur entrée en mnt au champ dvr
    $js_valeur_dvr = "document.ADForm.$f_dvr_short.value";	// Montant saisi dans le champ DVR
    $js_valeur_mnt = "document.ADForm.$f_mnt_short.value";	// Montant saisi dans le champ MNT

    $js_onchange_mnt = "$js_valeur_dvr=$js_valeur_mnt;"; //Sera écrasé par une autre valeur si on est en multidevise
    $js_onchange_dvr = "$js_valeur_mnt=$js_valeur_dvr;";

    global $global_multidevise;
    if ($global_multidevise) {	//Multidevise
      //1. Ajout de champs hidden pour le change (fonction JavaScript open_change) et du champ MNT pour le reste
      //   en devise de référence
      $prefix = "HTML_GEN_dvr_$f_dvr_short";
      $js_comm_nette	= $prefix."_comm_nette";
      $js_taux	= $prefix."_taux";
      $js_dest_reste	= $prefix."_dest_reste";

      $this->addHiddenType($js_comm_nette	,"");
      $this->addHiddenType($js_taux		,"");
      $this->addHiddenType($js_dest_reste	,"");

      if ($reste) {
        $js_reste_hidden= $prefix."_reste_hidden";
        $this->addHiddenType($js_reste_hidden	,"");
        $js_reste = $prefix."_reste";
        $this->addField($js_reste,"$f_mnt_long "._("(reste)"),TYPC_MNT);
        $this->setFieldProperties($js_reste,FIELDP_IS_LABEL,true);
        global $global_monnaie;
        // Le reste est toujours en devise de référence
        $this->setFieldProperties($js_reste,FIELDP_DEVISE,$global_monnaie);
        $this->setOrder($f_dvr_short, array($js_reste));
      } else
        $js_reste = "";

      //2. Javascript relatif au lien "Changer"

      $nomFnJS = $prefix."_popup()";
      $this->addLink($f_dvr_short, $prefix."_change", _("Changer"), "#");
      $this->setLinkProperties($prefix."_change", LINKP_JS_EVENT, array("onclick" => "$nomFnJS;"));

      $js_devise_dvr = "document.ADForm.$prefix.value";	//Devise du champ DVR

      if(! $transac_multi_agence) {
          $js = "function $nomFnJS { if (($js_devise_dvr == '$f_mnt_devise') || (($js_valeur_dvr == '') && ($js_valeur_mnt == ''))) return false; else open_change($js_valeur_mnt,'$f_mnt_devise',$js_valeur_dvr,$js_devise_dvr,'$f_mnt_short','$f_dvr_short','$js_comm_nette','$js_taux','$js_reste','$js_dest_reste','$achat_vente',$type_change);};\n";          
      } else {
          $js = "function $nomFnJS { if (($js_devise_dvr == '$f_mnt_devise') || (($js_valeur_dvr == '') && ($js_valeur_mnt == ''))) return false; else open_change_multi_agences($js_valeur_mnt,'$f_mnt_devise',$js_valeur_dvr,$js_devise_dvr,'$f_mnt_short','$f_dvr_short','$js_comm_nette','$js_taux','$js_reste','$js_dest_reste','$achat_vente',$type_change);};\n";           
      }     
      
      $this->addJS(JSP_FORM,$nomFnJS,$js);

      //3. Javascript relatif à la recopie des valeurs du champ MNT vers DVR
      $js_onchange_mnt = "if ($js_devise_dvr == '$f_mnt_devise') $js_valeur_dvr=$js_valeur_mnt;";
      $js_onchange_dvr = "if ($js_devise_dvr == '$f_mnt_devise') $js_valeur_mnt=$js_valeur_dvr;";

      //4. JavaScript relatif à la remise à zéro des variables au focus
      $js = "document.ADForm.$js_comm_nette.value='';document.ADForm.$js_taux.value='';document.ADForm.$js_dest_reste.value='';";
      if ($reste)
        $js.= "document.ADForm.$js_reste.value='';";
      $this->setFieldProperties($f_mnt_short,FIELDP_JS_EVENT, array("onfocus" => "$js_valeur_dvr='';$js"));
      $this->setFieldProperties($f_dvr_short,FIELDP_JS_EVENT, array("onfocus" => "$js_valeur_mnt='';$js"));
    }

    $this->setFieldProperties($f_mnt_short,FIELDP_JS_EVENT, array("onchange" => $js_onchange_mnt));
    $this->setFieldProperties($f_dvr_short,FIELDP_JS_EVENT, array("onchange" => $js_onchange_dvr));
  }


//Fonctions publiques
  function buildHTML() {
    if ($this->State == 1) {
      //On commence par générer le titre
      if ($this->Title != "") $this->genTitle();
      //Ensuite on génère l'en-tête du formulaire
      $this->genFormHead();
      //Génère le corps
      if (sizeof($this->FieldsAndHTMLExtraCode) > 0) $this->genFormFields($this->FieldsAndHTMLExtraCode);
      if (sizeof($this->LinksAndButtons) > 0) $this->genFormLinksAndButtons();
      if (sizeof($this->HiddenTypes) > 0) $this->genHiddenTypes();
      //Génère le footer
      $this->genFormFooter();

      //Change l'état de l'objet
      $this->State = 2;
    } else signalErreur(__FILE__,__LINE__,__FUNCTION__, _("L'état courant ne permet pas d'exécuter buildHTML()"));
  }

  function getHTML() { // Renvoie l'entièreté du code généré (concatène les 5 parties)
    if ($this->State == 2)
      return $this->HTMLTitle . $this->HTMLFormHead . $this->HTMLFormBody . $this->HTMLFormButtons . $this->HTMLFormFooter;
    else
      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("L'état courant de l'objet HTML_GEN2 ne permet pas d'exécuter getHTML()"));
  }

  function show() { // Imprime le code HTML généré
    if ($this->State == 1)
      // Le code HTML n'est pas encore généré
      $this->buildHTML();
    if ($this->State == 2)
      echo $this->getHTML();
    else
      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("L'état courant de l'objet HTML_GEN2 ne permet pas d'exécuter show()"));
  }

  //--------------------Fonctions privées--------------------
  //Partie TF
  function insertField ($row, $nomTable)
  /* Cette fonction ajoute un champ dans $this->Fields et renseigne la liste $this->ShortNames
     IN : $row est un tableau associatif corespondant à une entrée de d_tableliste
          $nomTable est le nom court de la table à laquelle appartient ce champs.
  */
  {
    // Vérifier qu'un champs portant le même nom n'est pas déjà été inséré dans Fields
    if (in_array($row["nchmpc"], $this->ShortNames))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs ".$row["nchmpc"]." existe déjà !"
    // Création et initialisation de l'objet Fields
    $f = new Field;
    $f->ShortName = $row["nchmpc"];
    $f->LongName = new Trad($row["nchmpl"]);
    $f->LongName = $f->LongName->traduction();
    $f->IdRefField = $row["ident"];
    $f->Table = $nomTable;
    /* Traitement spécial pour les entiers :
    Dans la base : on stocke 'inX' avec X =
    't' --> Entier normal
    nbr --> Remplir le champs avec des '0' pour obtenir nbr chiffres.
    */
    if (substr($row["type"], 0, 3) == 'inn') {
      $nbrZero = $row["type"][2];
      if ($nbrZero != 't')
        $f->FillZero = $nbrZero;
      $f->Type = TYPC_INN;
    } elseif(substr($row["type"], 0, 2) == 'in') { 
	 	      $nbrZero = $row["type"][2]; 
	 	      if ($nbrZero != 't') 
	 	        $f->FillZero = $nbrZero; 
	 	      $f->Type = TYPC_INT; 
    } elseif(($row["type"] == TYPC_TXT)&&($row["traduit"] == 't')){
    	 $f->Type = TYPC_TTR;
    } else
     $f->Type = $row["type"];
    if ($row["isreq"] == 't')
      $f->Required = true;
    else
      $f->Required = false;
    if (isset($row["ref_field"])) {
      $f->Type = TYPC_LSB;
    }

    // Affectation de la devise si montant
    global $global_monnaie_courante;
    if ($f->Type == TYPC_MNT)
      $f->Devise = $global_monnaie_courante;

    // Insertion de ce champs dans la liste des champs de la classe
    array_push($this->FieldsAndHTMLExtraCode, $f);
    // Renseignement de la liste des noms
    array_push($this->ShortNames, $f->ShortName);

    return true;
  }


  function &getField($shortName, &$Fields) {
    reset($Fields);
    while (list($key, $value) = each($Fields)) {
      if (get_class($value) == "Field") { //Si c'est un champs (et non HTMLEtxraCode)
        if ($value->ShortName == $shortName)
          return $Fields[$key];
        else if ($value->Type == TYPC_CNT) { //Si container
          if (($retour = &$this->getField($shortName, $Fields[$key]->NestedFields)) != NULL) return $retour;
        }
      }
    }
    return NULL;
  }

  function &getHTMLExtraCode($shortName, &$Fields) {
    reset($Fields);
    while (list($key, $value) = each($Fields)) {
      if ($this->isHTMLExtraCode($value)) { //Si c'est un champs (et non HTMLEtxraCode)
        if ($value->ShortName == $shortName)
          return $Fields[$key];
        else if ($value->Type == TYPC_CNT) { //Si container
          if (($retour = &$this->getHTMLExtraCode($shortName, $Fields[$key]->NestedFields)) != NULL) return $retour;
        }
      }
    }
    return NULL;
  }

  function removeFromShortNameList ($shortName)
  /*Supprime un shortName de la liste des noms courts.
    IN : shortName à supprimer
  */
  {
    $this->ShortNames = array_diff($this->ShortNames, array($shortName));
    return true;
  }

  function getFieldPosition ($shortName)
  /* Cette fonction renvoie la position d'un champs dans le tableau $Fields
     On reste au premier niveau
     IN : Le nom cout du champs recherché
     OUT: La position (-1 si le champs ne se trouve pas dans Fields)
  */
  {
    reset($this->FieldsAndHTMLExtraCode);
    while (list($pos, $f) = each($this->FieldsAndHTMLExtraCode))
      if ($f->ShortName == $shortName)
        return $pos;
    return -1;
  }

  function getChoicePosition ($choiceShortName, $choices)
  /* Cette fonction renvoie la position d'un choix dans le tableau $ListBox
     IN : Le nom court du choix
          Le tableau des choix ($ListBox)
     OUT: La position (-1 si le nom ne se trouve pas dans $ListBox)
  */
  {
    reset($choices);
    while (list($pos, $c) = each($choices))
      if ($c->ShortName == $choiceShortName)
        return $pos;
    return -1;
  }

  function isInSpan($posY, $posX)
  /* Fonction qui indique si l'on se trouve dans un zone de span définie auparavant */
  {
    // Vérifier que si l'on se trouve dans un span, on est bien au début de ce span
    if ($this->LinksAndButtons[$posY][$posX]->Span == 0) {
      $idx = $posX;
      $count = 0;
      $OK = false;
      while ($idx != 1 && !($OK)) {
        "Examining cell line $posY col $posX  .. ";
        $spanValue = $this->LinksAndButtons[$posY][$idx]->Span;
        if ($spanValue != 0)
          if ($spanValue > $count)
            // On est dans une zone de span
            return true;
          else
            // On arrive au span précédent qui ne couvre pas la position convoitée
            $OK = true;
        else {
          // On continue la vérification
          $idx--;
          $count++;
        }
      }
    }
    return false;
  }

  function &getLinkOrButton($shortName, & $fields)
  /* Cette fonction prend le nom court d'un objet (un lien ou un bouton) et renvoie une référence vers cet objet s'il existe ou NULL s'il n'a pas pu le trouver
     IN : Le nom court de l'objet
     OUT: Réf vers l'objet ou NULL
  */
  {
    reset($fields);
    while (list($key, $f) = each($fields)) { //Pour chaque champs
      if ($this->isField($fields[$key])) { //Si c'est un champs
        reset($fields[$key]->LinksAndButtons);
        while (list($pos, $obj) = each($fields[$key]->LinksAndButtons)) //Pour chaque Lien/Bouton
          if ($obj->ShortName == $shortName) return $fields[$key]->LinksAndButtons[$pos];

        if ($fields[$key]->Type == TYPC_CNT) { //Si c'est un container
          $res = &$this->getLinkOrButton($shortName, $fields[$key]->NestedFields);
          if ($res != NULL) return $res;
        }
      }
    }
    return NULL;
  }

  function removeLinkOrButton ($shortName, $fields)
  /* Cete fonction retire le le lien ou le bouton dont le nom est $shortnam de la structure $fields */
  {
    reset($fields);
    while (list($key, $f) = each ($fields)) {  //Pour chaque champs
      reset($fields[$key]->LinksAndButtons);
      if ($this->isField($fields[$key])) { //Si c'est un champs
        while (list($pos, $obj) = each($fields[$key]->LinksAndButtons)) //Pour chaque lien/bouton
          if ($obj->ShortName == $shortName) { //Si ça correspond on remove
            $fields[$key]->LinksAndButtons[$pos] = NULL;
            return true;
          }
        if  ($fields[$key]->Type == TYPC_CNT) { //Si container
          $res = &$this->removeLinkOrButton($shortName, $fields[$key]->NestedFields);
          if ($res) return true;
        }
      }
    }
    return false;
  }

  function &getFormLinkOrButton($shortName)
  /* Cette fonction renvoie une référence vers l'objet (lien ou bouton) attaché au formulaire dont le nom court est fourni en paramètre
     IN : le nom court de l'objet recherché
     OUT : une réf vers l'objet ou NULL si on ne le trouve pas.
  */
  {
    reset ($this->LinksAndButtons);
    while (list($posY, $vect) = each($this->LinksAndButtons)) {
      reset($this->LinksAndButtons[$posY]);
      while (list($posX, $obj) = each($this->LinksAndButtons[$posY])) {
        if ($obj->ButtonOrLink->ShortName == $shortName) return $this->LinksAndButtons[$posY][$posX]->ButtonOrLink;
      }
    }
    return NULL;
  }

  function removeFormCell ($shortName)
  /* Cete fonction retire la cellule qui contient le lien ou le bouton dont le nom est $shorName du formulaire  */
  {
    reset ($this->LinksAndButtons);
    while (list($posY, $vect) = each($this->LinksAndButtons)) {
      reset($vect);
      while (list($posX, $obj) = each($this->LinksAndButtons[$posY])) {
        if ($obj->ShortName == $shortName) {
          $this->LinksAndButtons[$posX] = NULL;
          return true;
        }
      }
    }
    return false;
  }

  function isField ($obj) {
    return (get_class($obj) == 'Field');
  }

  function isHTMLExtraCode ($obj) {
    return ((get_class($obj) == 'HTMLExtraCode') || (get_class($obj) == 'HTML_TABLE_table'));
  }


  //Partie TK
  function genTitle() {
    //Génère le code HTMLTitle
    $this->HTMLTitle = "\n<H1 align=\"center\">".$this->Title."</H1>\n";
    $this->HTMLTitle .= "<br><br>\n";
  }

  function genFormHead() {
    global $PHP_SELF;

    //Génère le code HTMLFormHead
    $this->HTMLFormHead = $this->getIndent()."<FORM name=\"ADForm\" action=\"$PHP_SELF\" method=\"post\" enctype=\"multipart/form-data\"";

    //Evènements javascript
    /*
      On ajoute 3 tâches au moment du submit :
      - Vérifier que le formulaire n'ait pas déjà été envoyé (en cas de couble-click sur le bouton)
      - Vérifier que le formulaire ait été validé par le checkForm()
      - Valider que javascript est actif
    */
    $this->JSEvents["onsubmit"] .= "if ((! isSubmit) && (ADFormValid)){isSubmit=true; document.ADForm.java_enabled.value = '1'; return true;} else {return false;}";
    reset($this->JSEvents);
    while (list($key,$value) = each($this->JSEvents)) {
      $this->HTMLFormHead .= " $key=\"$value\"";
    }

    $this->HTMLFormHead .= ">\n";
    $this->addIndent();
  }

  function genFormFooter() {
    // Génère le code HTMLFormFooter
    // Crée un champ hidden pour le prochain écran
    $this->HTMLFormFooter = $this->getIndent()."<INPUT type=\"hidden\" name=\"prochain_ecran\">\n"; //Champs pour prochain_ecran
    $this->HTMLFormFooter .= $this->getIndent()."<INPUT type=\"hidden\" id=\"m_agc\" name=\"m_agc\">\n"; //Champs pour m_agc
    $this->HTMLFormFooter .= $this->getIndent()."<INPUT type=\"hidden\" name=\"java_enabled\" value=\"0\">\n"; //Champs javascript activé ?
    $this->delIndent();
    $this->HTMLFormFooter .= $this->getIndent()."</FORM>\n";

    //Javascript
    $this->genJS();
  }

  function genJS() {
    //Crée les 3 strings de javascript
    $formJS = "isSubmit=false;\n";
    $beginJS = "";
    $endJS = "";
    reset($this->JS);
    while (list(,$value) = each($this->JS)) {
      switch ($value->Position) {
      case JSP_FORM :
        $formJS .= $value->Code;
        break;
      case JSP_BEGIN_CHECK :
        $beginJS .= $value->Code;
        break;
      case JSP_END_CHECK :
        $endJS .= $value->Code;
        break;
      }
    }
    //Header
    $this->HTMLFormFooter .= $this->getIndent()."<SCRIPT type=\"text/javascript\">\n";
    $this->addIndent();

    $this->HTMLFormFooter .= $this->getIndent().$formJS;
    $this->genCheckFormJS($beginJS, $endJS);

    //Footer
    $this->delIndent();
    $this->HTMLFormFooter .= $this->getIndent()."</SCRIPT>\n";

  }

  function genCheckFormJS($beginJS, $endJS) {
    //Gestion des touches associées aux boutons
    reset($this->LinksAndButtons);
    $js = "enterButtonExist = false;";
    while (list($key, $posX) = each($this->LinksAndButtons)) { //Parcours tous les FormButtons
      while (list($key, $Cell) = each($posX)) {
        if ($Cell->ButtonOrLink) {
          $value = $Cell->ButtonOrLink;
          if (get_class($value) == "Button") {
            if ($value->Key == KEYB_ENTER) $js = "enterButtonExist = true; enterButton = document.ADForm.".$value->ShortName.";";
          }
        }
      }
    }
    $this->HTMLFormFooter .= $this->getIndent().$js;

    //Gestion des touches associées aux links
    for ($i=0; $i<10; ++$i) {//Gestion des touches 0 à 9
      if (isset($this->LinkAssignedKeys[constant("KEYL_".$i)]))  //Si la touche $i est associée à un lien
        $this->HTMLFormFooter .= $this->getIndent()."link".$i."Value='".$this->LinkAssignedKeys[constant("KEYL_".$i)]."';\n";
    }

    //Début fonction
    $this->HTMLFormFooter .= $this->getIndent()."function checkForm(){\n";
    $this->addIndent();

    $this->HTMLFormFooter .= $this->getIndent()."document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';ADFormValid = true; msg = '';\n";
    $this->HTMLFormFooter .= $beginJS;
    $this->genFieldCheck($this->FieldsAndHTMLExtraCode);
    $this->HTMLFormFooter .= $this->getIndent()."if (msg != '') alert(msg);\n";
    $this->HTMLFormFooter .= $endJS;

    //Fin de la fonction
    $this->delIndent();
    $this->HTMLFormFooter .= $this->getIndent()."}\n";
  }


  function genFieldCheck($Fields) {
    //Vérification du format et si les champs obligatoires sont bien renseignés
    reset($Fields);
    while (list(, $field) = each($Fields)) {
      if ($this->isHTMLExtraCode($field)) {
        //Aucune vérification pour le code html
      } else if (get_class($field) == "Field") {

        if ($field->Check) {

          switch ($field->Type) {
          case TYPC_TXT :
          case TYPC_ARE :
            if ($field->Required) {
              $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName.".value == '')";
              $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné"),$this->escape_quote($field->LongName))."\\n'; ADFormValid=false;}\n";
            }
            break;
          case TYPC_INT :
            $this->HTMLFormFooter .= $this->getIndent()."if (! isIntPos(document.ADForm.".$field->ShortName.".value))";
            $this->HTMLFormFooter .= "{msg += '- ".sprintf(_("Le format du champ \'%s\' est incorrect : il doit être un nombre naturel"),$this->escape_quote($field->LongName))."\\n'; ADFormValid = false;}\n";
            if ($field->Required) {
              $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName.".value == '')";
              $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné"),$this->escape_quote($field->LongName))."\\n'; ADFormValid=false;}\n";
            }
            break;
          case TYPC_INN:
             $this->HTMLFormFooter .= $this->getIndent()."if (! isIntPos(Math.abs(document.ADForm.".$field->ShortName.".value)))";
             $this->HTMLFormFooter .= "{msg += '- ".sprintf(_("Le format du champ \'%s\' est incorrect : il doit être un nombre relatif"),$this->escape_quote($field->LongName))."\\n'; ADFormValid = false;}\n";
             if ($field->Required) {
              $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName.".value == '')";
              $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné"),$this->escape_quote($field->LongName))."\\n'; ADFormValid=false;}\n";
            }
            break;
          case TYPC_DTE :
            $this->HTMLFormFooter .= $this->getIndent()."if (! isDate(document.ADForm.".$field->ShortName.".value))";
            $this->HTMLFormFooter .= "{msg +=  convert_js_date(document.ADForm.".$field->ShortName.".value)+'- ".sprintf(_("Le format du champ \'%s\' est incorrect"),$this->escape_quote($field->LongName))."\\n'; ADFormValid = false;}\n";
            // Ajouté par TF, vérifie que la date est bien antérieure à la date courante

            $this->HTMLFormFooter .= $this->getIndent()."if (isBefore('".localiser_date(date("d/m/Y"))."', document.ADForm.".$field->ShortName.".value))";
            $this->HTMLFormFooter .= "{msg += '- ".sprintf(_("La date précisée dans le champ \'%s\' doit être égale ou antérieure à la date du jour"),$this->escape_quote($field->LongName))."\\n'; ADFormValid = false;}\n";
            // Fin ajout
            if ($field->Required) {
              $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName.".value == '')";
              $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné"),$this->escape_quote($field->LongName) )."\\n'; ADFormValid=false;}\n";
            }
            break;                                   
          case TYPC_DTG :
            $this->HTMLFormFooter .= $this->getIndent()."if (! isDate(document.ADForm.".$field->ShortName.".value))";
            $this->HTMLFormFooter .= "{msg += '- ".sprintf(_("Le format du champ \'%s\' est incorrect"),$this->escape_quote($field->LongName))."\\n'; ADFormValid = false;}\n";
            if ($field->Required) {
              $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName.".value == '')";
              $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné"),$this->escape_quote($field->LongName) )."\\n'; ADFormValid=false;}\n";
            }
            break;
          case TYPC_DTF :
            $this->HTMLFormFooter .= $this->getIndent()."if (! isDate(document.ADForm.".$field->ShortName.".value))";
            $this->HTMLFormFooter .= "{msg += '- ".sprintf(_("Le format du champ \'%s\' est incorrect"),$this->escape_quote($field->LongName))."\\n'; ADFormValid = false;}\n";
            
            $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName.".value != '')";{
            $this->HTMLFormFooter .= $this->getIndent()."if (! isBefore('".localiser_date(date("d/m/Y"))."', document.ADForm.".$field->ShortName.".value))";
            $this->HTMLFormFooter .= "{msg += '- ".sprintf(_("La date précisée dans le champ \'%s\' doit être égale ou postérieure à la date du jour"),$this->escape_quote($field->LongName))."\\n'; ADFormValid = false;}\n";            
            }
            break;  
          case TYPC_TEL :
            $this->HTMLFormFooter .= $this->getIndent()."if (! isPhone(document.ADForm.".$field->ShortName.".value))";
            $this->HTMLFormFooter .= "{msg += '- ".sprintf(_("Le format du champ \'%s\' est incorrect"),$this->escape_quote($field->LongName))."\\n'; ADFormValid = false;}\n";
            if ($field->Required) {
              $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName.".value == '')";
              $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné"),$this->escape_quote($field->LongName))."\\n'; ADFormValid=false;}\n";
            }
            break;
          case TYPC_EMA :
            $this->HTMLFormFooter .= $this->getIndent()."if (! isEmail(document.ADForm.".$field->ShortName.".value))";
            $this->HTMLFormFooter .= "{msg += '- ".sprintf(_("Le format du champ \'%s\' est incorrect"),$this->escape_quote($field->LongName))."\\n'; ADFormValid = false;}\n";
            if ($field->Required) {
              $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName.".value == '')";
              $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné"),$this->escape_quote($field->LongName))."\\n'; ADFormValid=false;}\n";
            }
            break;
          case TYPC_BOL :
            break;
          case TYPC_MNT :
            $this->HTMLFormFooter .= $this->getIndent()."if (! isMontant(document.ADForm.".$field->ShortName.".value))";
            $this->HTMLFormFooter .= "{msg += '- ".sprintf(_("Le format du champ \'%s\' est incorrect"),$this->escape_quote($field->LongName))."\\n'; ADFormValid = false;}\n";
            if ($field->Required) {
              $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName.".value == '')";
              $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné"),$this->escape_quote($field->LongName))."\\n'; ADFormValid=false;}\n";
            }
            break;
          case TYPC_DVR :
            $this->HTMLFormFooter .= $this->getIndent()."if (! isMontant(document.ADForm.".$field->ShortName.".value))";
            $this->HTMLFormFooter .= "{msg += '- ".sprintf(_("Le format du champ \'%s\' est incorrect"),$this->escape_quote($field->LongName))."\\n'; ADFormValid = false;}\n";
            if ($field->Required) {
              $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName.".value == '')";
              $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné"),$this->escape_quote($field->LongName))."\\n'; ADFormValid=false;}\n";
              if ($field->reste == true) {
                // Dans ce cas, la variable doit etre postée via un champ hidden car le champ est disabled
                $this->HTMLFormFooter .= $this->getIndent()."document.ADForm.HTML_GEN_dvr_".$field->ShortName."_reste_hidden.value = document.ADForm.HTML_GEN_dvr_".$field->ShortName."_reste.value;\n";
              }
            }
            break;
          case TYPC_LSB :
            if ($field->Required) {
              $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName.".value == 0)";
              $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné"),$this->escape_quote($field->LongName))."\\n'; ADFormValid=false;}\n";
            }
            break;
          case TYPC_PWD :
            if ($field->Required) {
              $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName.".value == '')";
              $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné"),$this->escape_quote($field->LongName))."\\n'; ADFormValid=false;}\n";
            }
            break;
          case TYPC_FLT :
            $this->HTMLFormFooter .= $this->getIndent()."if (! isFloat(document.ADForm.".$field->ShortName.".value))";
            $this->HTMLFormFooter .= "{msg += '- ".sprintf(_("Le format du champ \'%s\' est incorrect"),$this->escape_quote($field->LongName))."\\n'; ADFormValid = false;}\n";
            if ($field->Required) {
              $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName.".value == '')";
              $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné"),$this->escape_quote($field->LongName))."\\n'; ADFormValid=false;}\n";
            }
            break;
          case TYPC_PRC :
            $this->HTMLFormFooter .= $this->getIndent()."if (! isFloat(document.ADForm.".$field->ShortName.".value))";
            $this->HTMLFormFooter .= "{msg += '- ".sprintf(_("Le format du champ \'%s\' est incorrect"),$this->escape_quote($field->LongName))."\\n'; ADFormValid = false;}\n";
            if ($field->Required) {
              $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName.".value == '')";
              $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné"),$this->escape_quote($field->LongName))."\\n'; ADFormValid=false;}\n";
            }
            break;
          case TYPC_CNT :
            $this->genFieldCheck($field->NestedFields);
            break;
          case TYPC_TTR :
            $lang_syst_par_dft = get_langue_systeme_par_defaut();
            if ($field->Required) {
              // Cas UN: le champ est requis: la traduction dans la langue système par dft doit être remplie
              if (count(get_langues_installees())>1) {      //Cas où plusieurs langues sont installées
                $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName."_$lang_syst_par_dft.value == '')";
                $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné (en %s au moins)"),$this->escape_quote($field->LongName),get_langue_nom($lang_syst_par_dft))."\\n'; ADFormValid=false;}\n";
              } else {      //Cas où une langue seulement est installée
                $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName."_$lang_syst_par_dft.value == '')";
                $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné"),$this->escape_quote($field->LongName))."\\n'; ADFormValid=false;}\n";
              };
            } else {      //CAS DEUX: le champ n'est pas requis: si un champ est rempli, la traduction doit être donnée au
              //          moins dans la langue système par défaut
              //          Donc, si le champ en langue syst par dft est vide, les autres doivent l'être également
              $this->HTMLFormFooter .= $this->getIndent()."if ((document.ADForm.".$field->ShortName."_$lang_syst_par_dft.value == '') && (";
              foreach (get_langues_installees() as $code => $langue)
              $this->HTMLFormFooter .= "document.ADForm.".$field->ShortName."_$code.value != '' || ";
              $this->HTMLFormFooter .= "false))";
              $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné (en %s au moins)"),$this->escape_quote($field->LongName),get_langue_nom($lang_syst_par_dft))."\\n'; ADFormValid=false;}\n";
            };
            break;
          case TYPC_IMG :
            if ($field->Required) {
              $this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.HTML_GEN_IMG_".$field->ShortName.".value == '')";
              $this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit avoir une image associée"),$this->escape_quote($field->LongName))."\\n'; ADFormValid=false;}\n";
            }
            break;
            case TYPC_FILE : 
				if ($field->Required) { 
					$this->HTMLFormFooter .= $this->getIndent()."if (document.ADForm.".$field->ShortName.".value == '')"; 
					$this->HTMLFormFooter .= "{msg+='- ".sprintf(_("Le champ \'%s\' doit être renseigné"),$this->escape_quote($field->LongName))."\\n'; ADFormValid=false;}\n"; 
 				} 
 			break; 
          default :
            debug(sprintf(_("Type de champ inconnu '%s' pour le champ"),$field->Type)." : ");
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
          }
        }
      } else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Type de classe inconnu '".get_class($field)."'"
    }
  }
  function genFormFields($FieldsAndHTML) {
    /*
      Paramètre entrant : array(pos=>Field)
      Cette fonction génère les champs et réalise un appel récursif lorsqu'un champ est de type "container"
     */

    $this->HTMLFormBody .= $this->getIndent()."<TABLE align=\"center\" valign=\"middle\">\n";
    $this->addIndent();

    foreach ($FieldsAndHTML AS $value) {
      if ($this->isHTMLExtraCode($value)) {
        $this->genHTMLExtraCode($value);
      } else if (get_class($value) == "Field") {
        $this->genField($value);
      } else {
        debug(sprintf(_("Type d'entrée inconnu pour %s"),$value->ShortName)." : '".get_class($value)."'");
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }
    }
    $this->delIndent();
    $this->HTMLFormBody .= $this->getIndent()."</TABLE>\n";
  }

  function genHTMLExtraCode($HTMLExtraCode) {
    global $colb_tableau;

    if ($HTMLExtraCode->InTable == false) {
      $this->delIndent();
      $this->HTMLFormBody .= $this->getIndent()."</TABLE>\n";
    } else {
      $this->HTMLFormBody .= $this->getIndent()."<TR bgcolor=$colb_tableau><TD colspan=3>\n";
      $this->addIndent();
    }

    if (is_object($HTMLExtraCode->Contenu) && get_class($HTMLExtraCode->Contenu) == "HTML_TABLE_table")
      $this->HTMLFormBody .= $this->getIndent().$HTMLExtraCode->Contenu->gen_HTML();
    else
      $this->HTMLFormBody .= $this->getIndent().$HTMLExtraCode->Contenu;

    if ($HTMLExtraCode->InTable == false) {
      $this->HTMLFormBody .= $this->getIndent()."<TABLE align=\"center\" valign=\"middle\">\n";
      $this->addIndent();
    } else {
      $this->delIndent();
      $this->HTMLFormBody .= $this->getIndent()."</TD></TR>\n";
    }
  }

  function genFieldRow1($Field) {
    global $HTML_champ_oblig;

    $this->HTMLFormBody .= $this->getIndent()."<TD align=\"left\">\n";
    $this->addIndent();

    $this->HTMLFormBody .= $this->getIndent()."<p align=\"left\">".$Field->LongName;
    if ($Field->Required) $this->HTMLFormBody .= $HTML_champ_oblig;
    $this->HTMLFormBody .= "</p>\n";

    $this->delIndent();
    $this->HTMLFormBody .= $this->getIndent()."</TD>\n";
  }

  function genFieldRow2($Field) {
    $this->HTMLFormBody .= $this->getIndent()."<TD align=\"left\">\n";
    $this->addIndent();
    switch ($Field->Type) {
    case TYPC_TXT : //Texte
      $this->genFieldTxt($Field);
      break;
    case TYPC_TTR : //Texte traduit
      $this->genFieldTtr($Field);
      break;
    case TYPC_ARE : //Texte
      $this->genFieldAre($Field);
      break;
    case TYPC_INT : //Entier
      $this->genFieldInt($Field);
      break;
    case  TYPC_INN://Entier négatif
      $this->genFieldInt($Field);
      break;
    case TYPC_DTE : //Date
      $this->genFieldTxt($Field);
      break;
    case TYPC_DTG : //Date générale
      $this->genFieldTxt($Field);
      break;
    case TYPC_DTF : //Date générale
      $this->genFieldTxt($Field);
      break;
    case TYPC_TEL : //Telephonne
      $this->genFieldTxt($Field);
      break;
    case TYPC_EMA : //Email
      $this->genFieldTxt($Field);
      break;
    case TYPC_BOL : //Booléen
      $this->genFieldBox($Field);
      break;
    case TYPC_MNT : //Montant
      $this->genFieldMnt($Field);
      break;
    case TYPC_DVR : // Devise (listbox des différentes devises)
      $this->genFieldDvr($Field);
      break;
    case TYPC_LSB : //Listbox
      $this->genFieldListBox($Field);
      break;
    case TYPC_PWD : //Password
      $this->genFieldPwd($Field);
      break;
    case TYPC_FLT : //Float
      $this->genFieldFlt($Field);
      break;
    case TYPC_PRC : //Pourcentage
      $this->genFieldPrc($Field);
      break;
    case TYPC_CNT : //Container
      $this->genFormFields($Field->NestedFields);
      break;
    case TYPC_IMG: // Image
      $this->genFieldImage($Field);
      break;
    case TYPC_FILE: // Image 
 		$this->genFieldFile($Field); 
 		break; 
    default : //Autre
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Type de champs inconnu'".$Field->Type."' ('".$Field->ShortName."')" break;
    }

    $this->delIndent();
    $this->HTMLFormBody .= $this->getIndent()."</TD>\n";
  }

  function genFieldBox($Field) {
    $Field->ShortName = "HTML_GEN_BOL_".$Field->ShortName; # Utile pour transformer la valeur "on" en true (voir mainframe.php)
    $this->HTMLFormBody .= $this->getIndent()."<INPUT type=\"checkbox\"";
    $this->HTMLFormBody .= " name=\"".$Field->ShortName."\"";
    if ($Field->DefaultValue) $this->HTMLFormBody .= " checked";
    if ($Field->Label) $this->HTMLFormBody .= " disabled=\"true\"";
    //Javascript
    reset($Field->JSEvents);
    while (list($key, $value) = each($Field->JSEvents)) {
      $this->HTMLFormBody .= " $key=\"$value\"";
    }
    $this->HTMLFormBody .= ">\n";
  }

  function genFieldTxt($Field) {
    if (is_trad($Field->DefaultValue))
      $default_val = $Field->DefaultValue->traduction();
    else
      $default_val = $Field->DefaultValue;

    if (($Field->Type == TYPC_DTE) || ($Field->Type == TYPC_DTG) || ($Field->Type == TYPC_DTF))
      $Field->ShortName = "HTML_GEN_date_".$Field->ShortName; # Utile pour la localisation des dates (voir mainframe.php)

    $this->HTMLFormBody .= $this->getIndent()."<INPUT type=\"text\"";
    $this->HTMLFormBody .= " name=\"".$Field->ShortName."\"";
    $this->HTMLFormBody .= " value=\"".string_make_htmlcompatible($default_val)."\"";
    if ($Field->Label) $this->HTMLFormBody .= " class=\"labelClass\" disabled=\"true\"";
    if ($Field->Width != 0) $this->HTMLFormBody .= " size=\"".$Field->Width."\"";

    //Javascript
    foreach ($Field->JSEvents AS $key => $value)
    $this->HTMLFormBody .= " $key=\"$value\"";
    $this->HTMLFormBody .= ">\n";
  }

  function genFieldTtr($Field) {
    global $HTML_champ_oblig;
    if (($Field->DefaultValue != '') && (!is_trad($Field->DefaultValue)))
      signalErreur(__FILE__,__LINE__,__FUNCTION__);

    $langue_systeme_par_defaut = get_langue_systeme_par_defaut();

    $id_str="";
    if (is_trad($Field->DefaultValue))
      $id_str = $Field->DefaultValue->get_id_str();

    // On enregistre le strid dans la page HTML
    $this->HTMLFormBody .= $this->getIndent()."<INPUT type=\"hidden\" name=\"HTML_GEN_ttr_strid_".$Field->ShortName."\" value=\"$id_str\">\n";
    $Field->ShortName = "HTML_GEN_ttr_".$Field->ShortName;
    foreach (get_langues_installees() as $code => $langue) {	// Pour chaque langue installée
      if (is_trad($Field->DefaultValue))
        $default_val = $Field->DefaultValue->traduction($code, true);
      else
        $default_val = "";
      $this->HTMLFormBody .= $this->getIndent()."<INPUT type=\"text\"";
      $this->HTMLFormBody .= " name=\"".$Field->ShortName."_${code}\"";
      $this->HTMLFormBody .= ' value="'.string_make_htmlcompatible($default_val).'"';
      if ($Field->Label) $this->HTMLFormBody .= " class=\"labelClass\" disabled=\"true\"";
      if ($Field->Width != 0) $this->HTMLFormBody .= " size=\"".$Field->Width."\"";

      if ($code == $langue_systeme_par_defaut)
        //Javascript
        foreach($Field->JSEvents AS $key => $value)
        $this->HTMLFormBody .= " $key=\"$value\"";
      $this->HTMLFormBody .= "> ";

      if (count(get_langues_installees())>1) {	// On n'affiche pas le nom de la langue si adbanking est installé en 1 langue
        $this->HTMLFormBody .= sprintf(_("(en %s)"),string_make_htmlcompatible($langue->traduction()));
        if ($code == $langue_systeme_par_defaut)
          if ($Field->Required)
            $this->HTMLFormBody .= $HTML_champ_oblig;
      };
      $this->HTMLFormBody .= "<BR>\n";
    }
  }

  function genFieldAre($Field) {
    $this->HTMLFormBody .= $this->getIndent()."<TEXTAREA";
    $this->HTMLFormBody .= " name=\"".$Field->ShortName."\"";
    $this->HTMLFormBody .= " rows=\"".$Field->NumbRows."\"";
    if ($Field->Label) $this->HTMLFormBody .= " class=\"labelClass\" disabled=\"true\"";
    if ($Field->Width != 0) $this->HTMLFormBody .= " size=\"".$Field->Width."\"";
    //Javascript
    reset($Field->JSEvents);
    while (list($key, $value) = each($Field->JSEvents)) {
      $this->HTMLFormBody .= " $key=\"$value\"";
    }
    $this->HTMLFormBody .= ">";
    $this->HTMLFormBody .= string_make_htmlcompatible($Field->DefaultValue)."</TEXTAREA>\n";
  }
  function genFieldInt($Field) {
    $this->HTMLFormBody .= $this->getIndent()."<INPUT type=\"text\"";
    $this->HTMLFormBody .= " name=\"".$Field->ShortName."\"";

    if ($Field->DefaultValue == "") $this->HTMLFormBody .= " value=\"\"";
    else $this->HTMLFormBody .= " value=\"".sprintf("%0".$Field->FillZero."s", $Field->DefaultValue)."\"";
    if ($Field->Label) $this->HTMLFormBody .= " class=\"labelClass\" disabled=\"true\"";
    reset($Field->JSEvents);
    while (list($key, $value) = each($Field->JSEvents)) {
      $this->HTMLFormBody .= " $key=\"$value\"";
    }
    
    $this->HTMLFormBody .= ">\n";
  }

  function genFieldPwd($Field) {
    $this->HTMLFormBody .= $this->getIndent()."<INPUT type=\"password\"";
    $this->HTMLFormBody .= " name=\"".$Field->ShortName."\"";
    $this->HTMLFormBody .= " value=\"".string_make_htmlcompatible($Field->DefaultValue)."\"";
    if ($Field->Label) $this->HTMLFormBody .= " class=\"labelClass\" disabled=\"true\"";
    //Javascript
    reset($Field->JSEvents);
    while (list($key, $value) = each($Field->JSEvents)) {
      $this->HTMLFormBody .= " $key=\"$value\"";
    }
    $this->HTMLFormBody .= ">\n";
  }


  function genFieldMnt($Field) {
    setMonnaieCourante($Field->Devise);
    // Génère le code pour un champ de type montant ou de type devise
    global $global_billets;
    $this->HTMLFormBody .= $this->getIndent()."<INPUT type=\"text\"";
    $this->HTMLFormBody .= " name=\"".$Field->ShortName."\"";
    $this->HTMLFormBody .= " value=\"".afficheMontant($Field->DefaultValue, false)."\"";
    if ($Field->Label) {
      $this->HTMLFormBody .= " class=\"labelClass\" disabled";
    }
    if ($Field->ReadOnly) {
      $this->HTMLFormBody .= " readonly";
    }
    //Javascript
    $Field->JSEvents['onchange'] =
      "document.ADForm.".$Field->ShortName.".value = formateMontant(document.ADForm.".$Field->ShortName.".value);".
      $Field->JSEvents['onchange']; //On concatène au début car on formatte d'abord la valeur du champs!

    foreach($Field->JSEvents AS $key => $value)
    $this->HTMLFormBody .= " $key=\"$value\"";

    // Affichage de la devise
    $this->HTMLFormBody.= "> ".$Field->Devise."\n";

    //Billetage
    if ($Field->HasBillet) {
      $valeurs = recupeBillet($Field->Devise);
      while (list($key, $value) = each( $valeurs)) {
        $this->HTMLFormBody .= $this->getIndent() . "<INPUT type=\"hidden\" name=\"".$Field->ShortName."_billet_$key\" value=\"0\">";
        $this->HTMLFormBody .= $this->getIndent() . "<INPUT type=\"hidden\" name=\"".$Field->ShortName."_billet_rendu_$key\" value=\"0\">";
      }
    }
  }

  function genFieldDvr($Field) {
    // Génère le code pour un champ de type montant ou de type devise
    global $global_billets;
    //Fin de la préparation du code javascript. Début du traitement proprement dit
    $this->HTMLFormBody .= $this->getIndent()."<INPUT type=\"text\"";
    $this->HTMLFormBody .= " name=\"".$Field->ShortName."\"";
    $this->HTMLFormBody .= " value=\"".afficheMontant($Field->DefaultValue, false)."\"";
    $disabled = "";
    if ($Field->Label) {
      $this->HTMLFormBody .= " class=\"labelClass\" disabled";
    };
    //Javascript
    $Field->JSEvents['onchange'] =
      "document.ADForm.".$Field->ShortName.".value = formateMontant(document.ADForm.".$Field->ShortName.".value);".
      $Field->JSEvents['onchange']; //On concatène au début car on formatte d'abord la valeur du champs!

    reset($Field->JSEvents);
    while (list($key, $value) = each($Field->JSEvents)) {
      $this->HTMLFormBody .= " $key=\"$value\"";
    }

    $devise = "> \n";
    // Affichage de la listbox des devises
    global $global_multidevise;
    if (! $global_multidevise) {
      $devise.=$Field->Devise."\n";
    } else {
      $js="";
      if ($Field->linkedField != NULL) {      //Affichage d'une listbox qui contient toutes les monnaies
        $js_valeur_mnt = "document.ADForm.".$Field->linkedField->ShortName.".value";    // Montant champ MNT
        $js_valeur_dvr = "document.ADForm.".$Field->ShortName.".value";                 // Montant champ DVR
        $js_disabled_dvr= "document.ADForm.".$Field->ShortName.".disabled";             // Disabled JS champ DVR
        $js_devise_mnt = "'".$Field->linkedField->Devise."'";                           // Devise du champ MNT
        $js_devise_dvr = "document.ADForm.HTML_GEN_dvr_".$Field->ShortName.".value";    //Devise de DVR

        $js = " onchange=\"";
        $js.= "if ($js_devise_dvr == $js_devise_mnt) ".'{';
        $js.= "$js_valeur_dvr=$js_valeur_mnt; $js_disabled_dvr=true;";
        $js.= '} else {';
        $js.= "$js_valeur_dvr='';$js_disabled_dvr=false;";
        $js.= '};"';
      }

      $devise.= $this->getIndent()."<SELECT name=\"HTML_GEN_dvr_".$Field->ShortName."\" $js>\n";
      $this->addIndent();
      foreach (get_table_devises() AS $code => $poubelle) {
        $devise .= $this->getIndent()."<OPTION value=\"$code\"";
        if ($Field->Devise == $code)
          $devise .= " selected=\"selected\"";
        $devise .= ">$code</OPTION>\n";
      }
      $this->delIndent();
      $devise.=$this->getIndent()."</SELECT>\n";
    }
    $this->HTMLFormBody .= $devise;
  }

  function genFieldFlt($Field) {
    global $global_monnaie;

    $this->HTMLFormBody .= $this->getIndent()."<INPUT type=\"text\"";
    $this->HTMLFormBody .= " name=\"".$Field->ShortName."\"";
    $this->HTMLFormBody .= " value=\"".$Field->DefaultValue."\"";
    if ($Field->Label) $this->HTMLFormBody .= " class=\"labelClass\" disabled";
    //Javascript
    reset($Field->JSEvents);
    while (list($key, $value) = each($Field->JSEvents)) {
      $this->HTMLFormBody .= " $key=\"$value\"";
    }
    $this->HTMLFormBody .= ">\n";
  }


  function genFieldImage($Field) {
    global $http_prefix;
    // Préparation de la valeur du champ canmodif qui sera passé par la fonction open_image_manager
    if ($Field->Label == false)
      $canmodif = 1;
    else
      $canmodif = 0;

    // Génération du chemin d'accès vers l'image
    if (isset($Field->URL)) {
      $localfilepath = $Field->URL;
      $url = $Field->URL;
    } else {
      if ($Field->Table == 'ad_cli')
        $PATHS = imageLocationClient($Field->DefaultValue);
      else
        if ($Field->Table == 'ad_pers_ext')
          $PATHS = imageLocationPersExt($Field->DefaultValue);

      if ($Field->ShortName == 'photo') {
        if (is_file($PATHS["photo_chemin_local"])) {
          $url = $PATHS["photo_chemin_web"];
          $localfilepath = $PATHS["photo_chemin_local"];
        } else {
          $url = "/adbanking/images/travaux.gif";
          $localfilepath = "";
        }
      } else
        if ($Field->ShortName == 'signature') {
          if (is_file($PATHS["signature_chemin_local"])) {
            $url = $PATHS["signature_chemin_web"];
            $localfilepath = $PATHS["signature_chemin_local"];
          } else {
            $url = "/adbanking/images/travaux.gif";
            $localfilepath = "";
          }
        }
    }

    $html = $this->getIndent()."<A href=\"#\" onclick=\"open_image_manager('".$Field->ShortName."','".$Field->LongName."','".$url."',".$canmodif.");\"><IMG name=\"".$Field->ShortName."\" WIDTH=\"60\" HEIGHT=\"60\" src=\"".$url."\"";
    while (list($key, $value) = each($Field->JSEvents)) {
      $html .= " $key=\"$value\"";
    }
    $html .= "></IMG></A>\n";

    // Ajout du champ hidden qui contiendra la nouvelle URL si l'image a été modifié
    $html .= $this->getIndent()."<INPUT type=\"hidden\" name=\"HTML_GEN_IMG_".$Field->ShortName."\" value=\"".$localfilepath."\"/>\n";

    $this->HTMLFormBody .= $html;
  }
  function genFieldFile($Field) { 
  	$this->HTMLFormBody .= $this->getIndent()."<INPUT type=\"file\""; 
 	$this->HTMLFormBody .= " name=\"".$Field->ShortName."\""; 
 	$this->HTMLFormBody .= " value=\"".$Field->DefaultValue."\""; 
 	if ($Field->Label) $this->HTMLFormBody .= " class=\"labelClass\" disabled"; 
 	//Javascript 
 	reset($Field->JSEvents); 
 	while (list($key, $value) = each($Field->JSEvents)) { 
 		$this->HTMLFormBody .= " $key=\"$value\""; 
 	}
 	$this->HTMLFormBody .= ">\n"; 
  }

  function genFieldPrc($Field) {
    $this->HTMLFormBody .= $this->getIndent()."<INPUT type=\"text\"";
    $this->HTMLFormBody .= " name=\"".$Field->ShortName."\"";
    $this->HTMLFormBody .= " value=\"".$Field->DefaultValue."\"";
    if ($Field->Label) $this->HTMLFormBody .= " class=\"labelClass\" disabled";
    //Javascript
    reset($Field->JSEvents);
    while (list($key, $value) = each($Field->JSEvents)) {
      $this->HTMLFormBody .= " $key=\"$value\"";
    }
    $this->HTMLFormBody .= ">%\n";
  }

  function genFieldListBox($Field) {
    // Si le champs est un label, il faut plutôt générer un champs text normal
    if ($Field->Label && $Field->DefaultValue != '') {	//On peut avoir des champs label sans valeur par défaut car celle-ci sera définie plus tard par javascript
      $this->HTMLFormBody .= $this->getIndent()."<INPUT type=\"text\"";
      $this->HTMLFormBody .= " name=\"".$Field->ShortName."\"";

      if ($Field->Table != '') { // Si ce champs est lié à une table
        $rowset = getFieldsFromTable ($Field->Table); // Récupère tous les champs de la table
        while ( $row = $rowset->fetchRow(DB_FETCHMODE_ASSOC) )
          if ($row["nchmpc"] == $Field->ShortName) {
            $defStrArr = getReferencedFields($row["ref_field"], $Field->DefaultValue);
            $defStr = array_pop($defStrArr);
            $this->HTMLFormBody .= " value=\"".string_make_htmlcompatible($defStr)."\"";
            $Field->DefaultValue = $defStr;
          }
      } else {	// Ce champs n'est pas attaché à une table
        $pos = $this->getChoicePosition($Field->DefaultValue, $Field->ListBox);
        if ($pos == -1)
          $defStr = "";
        else {
          $myChoice = $Field->ListBox[$pos];
          $defStr = $myChoice->LongName;
        }
        $Field->DefaultValue = $defStr;
        $this->HTMLFormBody .= ' value="'.string_make_htmlcompatible($defStr).'"';
      }

      $this->HTMLFormBody .= " class=\"labelClass\" disabled=\"true\"";
      $Field->Width = $Field->lenDefaultValue(0);

      if ($Field->Width != 0)
        $this->HTMLFormBody .= " size=\"".$Field->Width."\"";

      //Javascript
      foreach ($Field->JSEvents AS $key => $value)
      $this->HTMLFormBody .= " $key=\"$value\"";
      $this->HTMLFormBody .= ">\n";
    } else {	// Le champs n'est pas un label avec valeur par défaut
      $Field->ShortName = "HTML_GEN_LSB_".$Field->ShortName; # Utile pour transformer la valeur 0 ([Aucun] en NULL (voir mainframe.php)

      if ($Field->Table != '') {	// Ce champs est attaché à une table
      $rowset = getFieldsFromTable ($Field->Table); // Récupère tous les champs de la table
        while ( $row = $rowset->fetchRow(DB_FETCHMODE_ASSOC) )
          if ($row["ident"] == $Field->IdRefField) {
            $choices = getReferencedFields($row["ref_field"]);
            foreach ($choices AS $key => $value) {
              if ($Field->ListBoxIncEx == 1 && in_array($key, $Field->ListBoxIncExList))
                // INCLUDE_CHOICES et le champ est dans la liste à inclure
                array_push($Field->ListBox, new Choice($key, $value));
              else
                if ($Field->ListBoxIncEx == 2
                    && (!in_array($key, $Field->ListBoxIncExList)))
                  // EXCLUDE_CHOICES et champ pas dans la liste des exclude
                  array_push($Field->ListBox, new Choice($key, $value));
                else
                  if ($Field->ListBoxIncEx == 0)
                    array_push($Field->ListBox, new Choice($key, $value));
            }
          }
      }

      $this->HTMLFormBody .= $this->getIndent()."<SELECT";
      $this->HTMLFormBody .= " name=\"".$Field->ShortName."\"";
      if ($Field->Label)
      $this->HTMLFormBody .= " class=\"labelClass\" disabled=true ";
      //Javascript
      foreach ($Field->JSEvents AS $key => $value)
      $this->HTMLFormBody .= " $key=\"$value\"";
      $this->HTMLFormBody .= ">\n";
      $this->addIndent();

      //Choix [Aucun]
      if ($Field->HasAucun) {
        if ($Field->DefaultValue == "")
            $select = " selected";
          else
            $select = "";
          $this->HTMLFormBody .= $this->getIndent()."<OPTION value=\"0\"$select>["._("Aucun")."]</OPTION>\n";
        }

      //Choix [Tous]
      if ($Field->HasTous) {
      if ($Field->DefaultValue == "") $select = " selected";
        else $select = "";
        $this->HTMLFormBody .= $this->getIndent()."<OPTION value=\"0\"$select>["._("Tous")."]</OPTION>\n";
      }

      //Autres choix
      foreach ($Field->ListBox AS $key => $value) {
        if ($value->ShortName == $Field->DefaultValue)
          $select = " selected";
        else
          $select = "";
        $this->HTMLFormBody .= $this->getIndent()."<OPTION value=\"".$value->ShortName."\"$select>".string_make_htmlcompatible($value->LongName)."</OPTION>\n";
      }

      $this->delIndent();
      $this->HTMLFormBody .= $this->getIndent()."</SELECT>\n";
    }
  }

  function genFieldRow3($Field) {
    global $colb_tableau;
    global $calend_annee_passe;
    global $calend_annee_futur;

    $this->HTMLFormBody .= $this->getIndent()."<TD align=\"left\">\n";
    $this->addIndent();

    $this->HTMLFormBody .= $this->getIndent()."<TABLE align=\"center\" valign=\"middle\">\n";
    $this->addIndent();
    $this->HTMLFormBody .= $this->getIndent()."<TR bgcolor=$colb_tableau>\n";
    $this->addIndent();

    //Lien vers calendrier
    if ((($Field->Type == TYPC_DTE) || ($Field->Type == TYPC_DTG) || ($Field->Type == TYPC_DTF)) && ($Field->HasCalend)) {
      $formfield = "document.ADForm.".$Field->ShortName.".value";
      $this->HTMLFormBody .= $this->getIndent()."<TD align=\"center\">";
      $this->HTMLFormBody .= "<A href=\"#\" onClick=\" if (! isDate($formfield)) $formfield='';open_calendrier(getMonth($formfield), getYear($formfield), $calend_annee_passe, $calend_annee_futur, '".$Field->ShortName."');return false;\">"._("Calendrier")."</A>";
      $this->HTMLFormBody .= "</TD>\n";
    }

    //champ modifier
    if (($Field->Type == TYPC_MNT) &&($Field->FieldModify)) {
      if (check_access(299)) {
        $formfield = "document.ADForm.".$Field->ShortName.".disabled";
        $value=false;
        $this->HTMLFormBody .= $this->getIndent()."<TD align=\"center\">";
        $this->HTMLFormBody .= "<A href=\"#\" onClick=\" $formfield='$value';return false;\">"._("Modifier")."</A>";
        $this->HTMLFormBody .= "</TD>\n";
      }
    }

      
    //Lien vers billetage
    if (($Field->Type == TYPC_MNT) && ($Field->HasBillet)) {
      $formfield = $Field->ShortName;
      $direction = $Field->SensBillet;
      $devise= $Field->Devise;
      $this->HTMLFormBody .= $this->getIndent()."<TD align=\"center\">";
      $this->HTMLFormBody .="<A href=\"#\" onClick=\"open_billetage('$formfield', '$direction','$devise');return false;\">"._("Billetage")."</A>";
      $this->HTMLFormBody .= "</TD>\n";
    }

    //Autres liens & boutons
    reset($Field->LinksAndButtons);
    while (list($key, $value) = each($Field->LinksAndButtons)) {
      $this->HTMLFormBody .= $this->getIndent()."<TD align=\"center\">";

      if (get_class($value) == "Button") {
        if ($value->Visible) $this->genButton($value);
      } else if (get_class($value) == "Link") {
        if ($value->Visible) $this->genLink($value);
      } else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Type de bouton/lien inconnu
      $this->HTMLFormBody .= "</TD>\n";
    }

    $this->delIndent();
    $this->HTMLFormBody .= $this->getIndent()."</TR>\n";
    $this->delIndent();
    $this->HTMLFormBody .= $this->getIndent()."</TABLE>\n";

    $this->delIndent();
    $this->HTMLFormBody .= $this->getIndent()."</TD>\n";
  }

  function genButton($Button) {
    switch ($Button->Type) {
    case TYPB_SUBMIT :
      $type = "submit";
      break;
    case TYPB_RESET :
      $type = "reset";
      break;
    case TYPB_BUTTON :
      $type = "button";
      break;
    default :
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // Type de bouton inconnu
    }

    if ($Button->Key == KEYB_ENTER) $plus = " [enter]";
    else $plus = "";
    $this->HTMLFormBody .= "<INPUT type=\"$type\" name=\"".$Button->ShortName."\" value=\"".string_make_htmlcompatible($Button->LongName.$plus)."\"";

    //Javascript
    if ($Button->ProchainEcran != "") $Button->JSEvents['onclick'] .= "assign('".$Button->ProchainEcran."');";
    if ($Button->CheckForm) $Button->JSEvents['onclick'] .= "checkForm();";
    else  $Button->JSEvents['onclick'] = "ADFormValid=true;" . $Button->JSEvents['onclick']; //On met d'abord à true puis le reste du JS
    foreach ($Button->JSEvents AS $key => $value)
    $this->HTMLFormBody .= " $key=\"$value\"";

    $this->HTMLFormBody .= ">";
  }


  function genLink($Link) {
    $lien = $Link->Href;
    if ($Link->ProchainEcran != "") { //Si prochain_ecran à définir
      if (! strrchr($lien, "?")) //S'il n'y a pas encore de variable définie dans le lien
        $lien .= "?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=".$Link->ProchainEcran;
      else
        $lien .= "&m_agc=".$_REQUEST['m_agc']."&prochain_ecran=".$Link->ProchainEcran;
    }

    $this->HTMLFormBody .= "<A href=\"$lien\" name=\"".$Link->ShortName."\"";

    //Javascript
    reset($Link->JSEvents);
    while (list($key, $value) = each($Link->JSEvents)) {
      $this->HTMLFormBody .= " $key=\"$value\"";
    }

    $this->HTMLFormBody .= ">".$Link->LongName."</A>";
  }


  function genFormLinksAndButtons() {
    global $colb_tableau;

    //Détermine la largeur max du tableau
    $maxX = 0;
    reset($this->LinksAndButtons);
    while (list($Ypos, $line) = each($this->LinksAndButtons)) {
      if (sizeof($line) > 0) {
        end($line); //Place le pointeur sur le dernier élément de l'array
        if (! list($Xpos, $cell) = each($line)) signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Structure incorrecte"
        $currentMaxX = $Xpos;
        if ($cell->Span > 1) $currentMaxX += $cell->Span-1;
      } else $currentMaxX = 0;

      if ($currentMaxX > $maxX) $maxX = $currentMaxX;
    }

    //Génère le tableau
    $this->HTMLFormBody .= $this->getIndent()."<TABLE align=\"center\" valign=\"middle\">\n";
    $this->addIndent();

    for ($Ypos = 1; ($Ypos<=sizeof($this->LinksAndButtons)); ++$Ypos) { //Pour chaque ligne
      $line = $this->LinksAndButtons[$Ypos];

      $this->HTMLFormBody .= $this->getIndent()."<TR bgcolor=$colb_tableau>\n";
      $this->addIndent();

      for ($x=1; ($x <= $maxX); ++$x) { //Pour chaque cellule
        if (!isset($line[$x])) $this->HTMLFormBody .= $this->getIndent()."<TD></TD>\n";
        else if ($line[$x]->Span > 0) {
          $this->HTMLFormBody .= $this->getIndent()."<TD align=\"center\" colspan=".$line[$x]->Span.">";

          if (get_class($line[$x]->ButtonOrLink) == "Button")
            if ($line[$x]->ButtonOrLink->Visible) $this->genButton($line[$x]->ButtonOrLink);
            else if (get_class($line[$x]->ButtonOrLink) == "Link")
              if ($line[$x]->ButtonOrLink->Visible) $this->genLink($line[$x]->ButtonOrLink);
              else signalErreur(__FILE__,__LINE__,__FUNCTION__); // Type de bouton/lien inconnu

          $this->HTMLFormBody .= "</TD>\n";
        }
      }
      $this->delIndent();
      $this->HTMLFormBody .= $this->getIndent()."</TR>\n";
    }
    $this->delIndent();
    $this->HTMLFormBody .= $this->getIndent()."</TABLE>\n";
  }

  function genHiddenTypes() {
    reset($this->HiddenTypes);
    while (list($key,$value) = each($this->HiddenTypes)) {
      $this->HTMLFormBody .= $this->getIndent()."<INPUT type=\"hidden\" name=\"".$value->ShortName."\" value=\"".$value->Value."\">\n";
    }
  }

  function genField(&$Field) {
    global $colb_tableau;

    $this->HTMLFormBody .= $this->getIndent()."<TR bgcolor=$colb_tableau>\n";
    $this->addIndent();

    //Colonne 1
    $this->genFieldRow1($Field);

    //Colonne 2
    $this->genFieldRow2($Field);

    //Colonne 3
    $this->genFieldRow3($Field);

    $this->delIndent();
    $this->HTMLFormBody .= $this->getIndent()."</TR>\n";
  }

  function escape_quote($str) {
    return str_replace("'", "\\'", $str);
  }

  function add_js_enable_disable($shortName) {
    // Préconditions
    if ($this->State == 2)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "buildHTML a déjà été appelé"
    $f = &$this->getField($shortName, $this->FieldsAndHTMLExtraCode); // Tente de récupérer le champs concerné.
    if ($f == NULL)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs $shortName n'existe pas"
    if ($f->Type != TYPC_DVR)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "add_js_enable_disable ne peut être appellé que pour des champs de type TYPC_DVR"

    // Initialisation des variables
    $dvr_disabled 	= "document.ADForm.$shortName.disabled";
    $dvr_value	= "document.ADForm.$shortName.value";
    $dvr_dev_disable= "document.ADForm.HTML_GEN_dvr_$shortName.disabled";
    $dvr_dev_value	= "document.ADForm.HTML_GEN_dvr_$shortName.value";
    if ($f->linkedField != NULL) {
      $mnt_disabled	= "document.ADForm.".$f->linkedField->ShortName.".disabled";
      $mnt_value	= "document.ADForm.".$f->linkedField->ShortName.".value";
    };

    // Génération de la fonction "disable"
    $js_disable =	"function HTML_GEN_fn_disable_$shortName() {\n";
    $js_disable.=	"\t$dvr_disabled	= true;\n";
    $js_disable.=   "\t$dvr_value		= '';\n";
    $js_disable.=	"\t$dvr_dev_disable	= true;\n";
    $js_disable.=	"\t$dvr_dev_value 	= '".$f->Devise."';\n";
    if ($f->linkedField != NULL) {	//On fait un disable du champ MNT associé
      $js_disable .= "\t$mnt_disabled	= true;\n";
      $js_disable .= "\t$mnt_value	= '';\n";
    };
    $js_disable.=	"};\n";
    $this->addJS(JSP_FORM, "HTML_GEN_fn_disable_$shortName", $js_disable);

    //Génération de la fonction "enable"
    $js_enable =   "function HTML_GEN_fn_enable_$shortName() {\n";
    $js_enable .= "\t$dvr_dev_disable = false;\n";
    if ($f->linkedField == NULL)
      $js_enable .= "\t$dvr_disabled = false;\n";
    else {
      $js_enable .= "\t$mnt_disabled = false;\n";
      $js_enable .= "\tif ($dvr_dev_value != '".$f->Devise."')\n\t\t$dvr_disabled = false;\n";
    }
    $js_enable .= "};\n";
    $this->addJS(JSP_FORM, "HTML_GEN_fn_enable_$shortName", $js_enable);
  }
}
?>
