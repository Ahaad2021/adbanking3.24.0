<?php

/**
 * Classe de gestion des strings traduits
 * Attention cette classe doit être chargée avant le démarrage de la session ???
 * @author Olivier Luyckx
 * @since janvier 2005
 * @package Multilingue
 */

require_once 'lib/dbProcedures/multilingue.php';

class Trad {
  /*
  	// Partie PUBLIQUE
  	function Trad();				// Constructeur
  	function traduction(); 				// Renvoie la traduction dans la langue de l'interface
  	function traduction_rpt();				// Renvoie la traduction dans la langue du rapport
  	function get_traductions();			// Renvoie un vecteur contenant toutes les traductions

  	function set_traduction($langue,$traduction);	// Met à jour une traduction dans l'objet (mais pas dans la DB)
  	function set_id_str($id_str);
  	function save();				// Enregistre les traductions dans la DB
  */
  // Partie PRIVEE (ne pas accéder à ceci en dehors de la classe !)
  var $private_id_str;
  var $private_traductions;			// Array associatif: code langue - traduction

  /*
  	function private_get_traduction($langue,$renvoie_null);
  */

  /////////////////////
  // Partie PUBLIQUE //
  /////////////////////

  function Trad($id_str='Not set')
  // Constructeur
  {
    if (isset($id_str) && is_numeric($id_str)) {
      $this->private_id_str = $id_str;
      $this->private_traductions = db_get_traductions($id_str);
    }

  } // Fin constructeur Trad

  function traduction($langue=NULL, $renvoie_null = false)
  // Renvoie la traduction dans la langue spécifiée (on passe le code langue)
  // Renvoie la traduction dans la langue de l'interface utilisateur si aucune langue n'est spécifiée
  {
    global $global_langue_utilisateur;
    if ($langue==NULL)
      $langue=$global_langue_utilisateur;
    return $this->private_get_traduction($langue, $renvoie_null);
  }

  function traduction_rpt()
  // Renvoie la traduction dans la langue du rapport
  {
    global $global_langue_rapport;
    return $this->private_get_traduction($global_langue_rapport);
  }


  function get_traductions()
  // Renvoie un vecteur contenant toutes les traductions
  {
    return $this->private_traductions;
  }

  function get_traduction_max_len()
  // Renvoie la longueur de la plus grande traduction
  {
    $longueurs = array_map("strlen",$this->private_traductions);	// chaque valeur=longueur de la traduction
    $longueurs = array_values($longueurs);
    rsort($longueurs,SORT_NUMERIC);					// Tri des longueurs par ordre décroissant
    return $longueurs[0];
  }

  function traduction_lang_syst()
  // Renvoie la traduction dans la langue système par défaut
  {
    return $this->private_traductions[get_langue_systeme_par_defaut()];
  }

  function set_traduction($langue, $traduction)
  // Met à jour une traduction dans l'objet (mais pas dans la DB)
  {
    $this->private_traductions[$langue] = $traduction;
  }

  function set_id_str($id_str) {
    if (isset($this->private_id_str))
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $this->private_id_str = $id_str;
  }

  function get_id_str() {
    //if (!isset($this->private_id_str))
    //  signalErreur(__FILE__,__LINE__,__FUNCTION__);
    return $this->private_id_str;
  }

  function save($trad=NULL)
  // Enregistre les traductions dans la DB
  {
    if (isset($this->private_id_str) && is_numeric($this->private_id_str))
      // String déjà présent dans la DB -> mise à jour des traductions
      db_modifier_traductions($this);
    else
      // String pas encore présent dans la DB -> création d'un nouveau string dans la DB
      $this->private_id_str = db_creer_traductions($this); // Cet appel met à jour la prorpiété id_str
    return $this->get_id_str();
  }
	
  function remove()
  // Supprime les traductions dans la DB
  {
    if (isset($this->private_id_str) && is_numeric($this->private_id_str))
      // String déjà présent dans la DB -> mise à jour des traductions
      db_supprimer_traductions($this);
    return $this->get_id_str();
  }
  
  ///////////////////
  // Partie PRIVEE //
  ///////////////////
  function private_get_traduction($langue, $renvoie_null = false)
  // SI renvoie_null est faux :
  // 	Renvoie la traduction (si elle existe) dans la langue spécifiée
  // 	Si elle n'existe pas, fournit la traduction dans la langue système par défaut
  // SI renvoie_null est vrai :
  // 	Renvoie la traduction (si elle existe) dans la langue spécifiée
  // 	Si elle n'existe pas, renvoie le string vide
  {
    $langue_systeme_par_defaut = get_langue_systeme_par_defaut();
    if (isset($this->private_traductions[$langue]))
      return $this->private_traductions[$langue];
    else
      if ($renvoie_null == true)
        return '';
      else
        return $this->private_traductions[$langue_systeme_par_defaut];
  }

} // Fin class Trad


// Renvoie vrai si l'argument est un objet trad
function is_trad($objet) {
  return (is_object($objet) && get_class($objet) == 'Trad');
};
?>