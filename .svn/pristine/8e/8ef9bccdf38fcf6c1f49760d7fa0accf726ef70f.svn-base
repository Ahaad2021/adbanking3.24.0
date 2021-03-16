<?php

/**
 * FILL_HTML_GEN2
 *
 * @package Ifutilisateur
 */

require_once 'lib/misc/Erreur.php';
require_once 'lib/html/HTML_GEN2.php';

/**#@+
 * Types d'opérateur liés au FillFields
 */
/**
 * On inclut uniquement les champs de FilFields.
 */
define("OPER_INCLUDE", "OPER_INCLUDE");
/**
 * On prend tous les champs, sauf ceux de FillFields
 */
define("OPER_EXCLUDE", "OPER_EXCLUDE");
/**
 * On prend tous les champs.
 */
define("OPER_NONE", "OPER_NONE");
/**#@-*/

class Condition {
  var $ShortName;
  var $Value;

  function Condition($s, $v) {
    $this->ShortName = $s;
    $this->Value = $v;
  }
}

class FillField {
  var $ShortName;
  var $FormShortName;

  function FillField($s, $f) {
    $this->ShortName = $s;
    $this->FormShortName = $f;
  }
}

class FillClause {
  var $ShortName = "";
  var $NomTable = "";
  var $Conditions = array();
  var $FillFields = array();
}

class FILL_HTML_GEN2 {
  //ATTRIBUTS PUBLICS

  //ATTRIBUTS PRIVES
  var $FillClauses = array();
  var $UsedShortNames = array();
  var $State = 1; //Tant que 1 : tout permit; lorsque Fill() => State 2 => plus rien accepté

  //METHODES PUBLIQUES

  // constructeur, ne fait rien pour le moment.
  function FILL_HTML_GEN2() {
  }

  function addFillClause($shortName, $nomTable) {
    // Vérifier que le shortName n'existe pas encore
    if (in_array($shortName, $this->UsedShortNames))
      signalErreur(__FILE__,__LINE__,__FUNCTION__); //  "La clause $shortName a déjà été définie"
    array_push($this->UsedShortNames, $shortName);
    // Construction de la caluse
    $fc = new FillClause();
    $fc->ShortName = $shortName;
    $fc->NomTable = $nomTable;
    array_push($this->FillClauses, $fc);
    return true;
  }

  function addCondition($fillClauseShortName, $shortName, $value) {
    // Trouve la fillClause
    $fc = &$this->getFillClause($fillClauseShortName);
    if ($fc == NULL)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); //  "La clause $fillClauseShortName n'existe pas"
    // Vérifier que la contrainte pour ce chmaps n'a pas été définie auparavant
    $c = &$this->getCondition($fc, $shortName);
    if ($c != NULL)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); //  "La condition sur le champs $shortName a déjà été définie"
    // Création de la condition
    $cond = new Condition($shortName, $value);
    array_push($fc->Conditions, $cond);
    return true;
  }

  function addFillField($fillClauseShortName, $shortName, $formShortName) {
    // Trouve la fillClause
    $fc = &$this->getFillClause($fillClauseShortName);
    if ($fc == NULL)
      signalErreur(__FILE__,__LINE__,__FUNCTION__); //  "La clause $fillClauseShortName n'existe pas"
    // Vérifier que le FillField n'a pas déjà été défini
    $ff = &$this->getFillField($fc, $shortName);
    if ($ff != NULL) {
      debug(sprintf(_("Le nom de FillField %s a déjà été utilisé"),$shortName));
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    // Création du FillField
    if ($formShortName == NULL)
      $formShortName = $shortName;
    $ff = new FillField($shortName, $formShortName);
    array_push($fc->FillFields, $ff);
    return true;
  }

  function addManyFillFields($clauseName, $operator, $fields) {
    switch ($operator) {
    case OPER_INCLUDE:
      while (list($key, $value) = each($fields)) {
        $this->addFillField($clauseName, $value, $value);
      }
      break;
    case OPER_EXCLUDE:
      $fc = &$this->getFillClause($clauseName);
      if ($fc == NULL)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); //  "La clause $fillClauseShortName n'existe pas"
      $fieldList = getFieldList($fc->NomTable);
      while (list(,$fn) = each($fieldList)) {
        if (!in_array($fn, $fields)) {
          $this->addFillField($clauseName, $fn, $fn);
        }
      }
      break;
    case OPER_NONE:
      $fc = &$this->getFillClause($clauseName);
      if ($fc == NULL)
        signalErreur(__FILE__,__LINE__,__FUNCTION__); //  "La clause $clauseName n'existe pas"
      // Vérifier que le FillField n'a pas déjà été défini
      $fieldList = getFieldList($fc->NomTable);
      while (list(,$fn) = each($fieldList)) {
        $this->addFillField($clauseName, $fn, $fn);
      }
      break;
    default:
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'opérateur $operator n'existe pas"
    }
  }

  function fill(&$HTML_GENobject) {
    // Cette fonction prend en entrée un objet de type HTML_GEN2
    // Et va remplir les champs concernés dans cet objet.

    // Pour chaque FillClause
    reset($this->FillClauses);
    while (list($key, $fc) = each($this->FillClauses)) {
      // Appelle la PS qui va aller rechercher dans la DB les valeurs par défaut
      $result = getDatas($fc->NomTable, $fc->FillFields, $fc->Conditions);
		
      // Remplir les valeurs dans l'objet HTML_GEN passé en paramètres.
      foreach($result as $key=>$value) {
        $ff = $this->getFillField($fc, $key);
        //if (is_trad($longName))
      	//$longName = $longName->traduction();
      	
        $FT = $HTML_GENobject->getField($ff->FormShortName, $HTML_GENobject->FieldsAndHTMLExtraCode);
        if ($FT == NULL)
          signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs ".$ff->FormShortName." n'existe pas dans le formulaire"
        // Traitement spécifique si le champs est de type booléen
        if ($FT->Type == TYPC_BOL) {
          $value = ($value == "f" ? false : true);
        }
        if ($FT->Type == TYPC_PRC) {
          $value *= 100;
        }
        if ($FT->Type == TYPC_IMG) {
          // on donne à "DefaultValue" l'ID du client
          $value = $fc->Conditions[0]->Value;
        }
        if ($FT->Type == TYPC_TTR) {
        	// on donne à "DefaultValue" l'ID du client
        	$value = new Trad($value);
        }
         
        // Setting default value into HTML_GEN Object field.
        if ($value != '') // PHP confond 0 avec ''. Si le string est vide, l'empécher d'écrire 0
          $HTML_GENobject->setFieldProperties($ff->FormShortName, FIELDP_DEFAULT, $value);
      }
      reset($result);
    }
    return true;
  }

  //METHODES PRIVEES

  function &getFillClause($shortName) {
    reset($this->FillClauses);
    while (list($key, $obj) = each($this->FillClauses)) {
      if ($obj->ShortName == $shortName)
        return $this->FillClauses[$key];
    }
    return NULL;
  }

  function &getCondition($fc, $shortName) {
    reset($fc->Conditions);
    while (list($key, $obj) = each($fc->Conditions))
      if ($obj->ShortName == $shortName)
        return $fc->Conditions[$key];
    return NULL;
  }

  function &getFillField($fc, $shortName) {
    reset($fc->FillFields);
    while (list($key, $obj) = each($fc->FillFields))
      if ($obj->ShortName == $shortName)
        return $fc->FillFields[$key];
    return NULL;
  }

}

?>