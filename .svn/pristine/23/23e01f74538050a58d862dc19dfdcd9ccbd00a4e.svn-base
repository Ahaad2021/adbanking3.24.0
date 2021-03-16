<?php
/**
 * @package Multilingue
 */
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/dbProcedures/agence.php';

/**
 * Consulte d_tableliste et renvoie si le champ $nom_champ de la table $nom_table est oui ou non un champ traduit
 * Un champ traduit est un champ qui est un entier, foreing key vers un identifieur de string (table ad_traductions)
 * @author Olivier LUYCKX
 * @since 1.0.8m
 * @param string $nom_table Le nom de la table dans lequel se trouve le champ dont on veut savoir s'il est traduit
 * @param string $nom_champ Le nom du champ dont on veut savoir s'il est traduit
 * @return boolean Les erreurs possibles sont <UL>
 *   <LI> true: le champ est un traduit</LI>
 *   <LI> false: le champ n'est pas un champ traduit</LI> </UL>
 */

function is_champ_traduit($nom_table, $nom_champ) {
  // On vérifie dans d_tableliste si le champ $nom_champ de la table $nom_table est un champ traduit
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $traduit = false;

  $sql = "SELECT traduit FROM d_tableliste AS d, tableliste AS t ";
  $sql .= "WHERE ((d.tablen=t.ident) AND (d.nchmpc='$nom_champ') AND (t.nomc='$nom_table'));";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  };
  if ($result->numrows() == 1) {
    $row = $result->fetchrow();
    $traduit = (($row[0]) == 't');
  }

  $db = $dbHandler->closeConnection(true);
  return $traduit;
};

/**
 * Renvoie le code langue de la langue système par défaut
 * @author Olivier LUYCKX
 * @since 1.0.8m
 * @return string Le code langue de la langue système par défaut (par ex: 'fr_BE', 'en_GB')
 */
function get_langue_systeme_par_defaut()
// Renvoie le code langue de la langue système par défaut
{

  global $global_langue_systeme_dft;
  global $global_id_agence;

  // Optimisation
  if (isset($global_langue_systeme_dft))
    return $global_langue_systeme_dft;

  global $dbHandler;
  $db = $dbHandler->openConnection();
  $global_id_agence = getNumAgence();
  $sql = "SELECT langue_systeme_dft FROM ad_agc where id_ag=$global_id_agence;";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  };
  if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // Il n'y a pas une et une seule agence
  };

  $row = $result->fetchrow();
  $db = $dbHandler->closeConnection(true);

  return $row[0];
};

/**
 * Renvoie les langues dans lesquelles le logiciel est installé
 * @author Olivier LUYCKX
 * @since 1.0.8m
 * @return vecteur associatif qui contient les renseignements sur les langues installées
 *   <LI> key: le code langue (ex: 'fr_BE', 'en_GB') </LI>
 *   <LI> valeur: un vecteur associatif qui contient les traductions du nom de la langue qui correspond au code langue\
                  renseigné dans la key</LI> </UL>
 */
function get_langues_installees()
//Renvoie un vecteur qui contient les langues installées,indexé selon les codes langues
{
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $retour = array();

  $sql = "SELECT code,langue FROM adsys_langues_systeme;";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  };

  while ($row = $result->fetchrow())
    $retour[$row[0]] = new Trad($row[1]);

  $dbHandler->closeConnection(true);

  return $retour;
};

/**
 * Consulte la table ad_traductions et renvoie toutes les traductions associées à un identifieur de string donné
 * Elle ne devrait être appellée que depuis l'objet Trad
 * @author Olivier LUYCKX
 * @since 1.0.8m
 * @param $id_str Renseigne l'identifieur du string dont on veut récupérer les traductions.
 *
 * @return vecteur associatif <UL>
 *   <LI> key: code langue (ex: 'fr_BE', 'en_GB')</LI>
 *   <LI> value: La traduction du string dans la langue correspondant à la clé</LI>
 *   <LI> Une entrée est d'un format différent: la clé est "strid" et la valeur, l'id du string traduit</LI></UL>
 */
function db_get_traductions($id_str) {
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $retour = array();

  $sql = "SELECT langue,traduction FROM ad_traductions WHERE id_str=$id_str;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  };

  while ($row = $result->fetchrow())
    $retour[$row[0]] = $row[1];

  $dbHandler->closeConnection(true);
  return $retour;
};

/**
 * Renvoie le libellé de la langue correspondant au code langue fourni, dans la langue de l'interface utilisateur
 * @author Olivier LUYCKX
 * @since 1.0.8m
 * @param string $code_langue Le code langue (ex: 'fr_BE', 'en_GB')
 * @return string Le libellé de la langue $code_langue, traduit dans la langue de l'interface utilisateur (ex: si $code_langue est 'fr_BE', la fonction renvoir 'Français' ou 'French')
 */
function get_langue_nom($code_langue) {
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT langue FROM adsys_langues_systeme WHERE code='$code_langue';";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // Langue '%s' non trouvée dans la base de données
  }

  $retour = $result->fetchrow();
  $retour = new Trad($retour[0]);
  $retour = $retour->traduction();

  $db = $dbHandler->closeConnection(true);
  return $retour;
}

/**
 * Cette fonction modifie toutes les traductions (dans la table ad_traductions)
 * Elle ne devrait être appellée que depuis l'objet Trad
 * @author Olivier LUYCKX
 * @since 1.0.8m
 * @param object Trad
 */
function db_modifier_traductions($traductions) {
  if (!is_trad($traductions))
    signalErreur(__FILE__,__LINE__,__FUNCTION__);

  global $dbHandler;
  $db = $dbHandler->openConnection();

  //ETAPE1: On vérifie d'abord que le $id_string passé existe bien
  $sql = "SELECT id_str FROM ad_str WHERE id_str=".$traductions->get_id_str();

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  };

  if ($result->numrows() != 1) {	// Si l'id_str ne correspond pas à un id_string valide
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // Aucun enregistrement trouvé
  };

  //ETAPE 2: traitement pour chaque langue dans le vecteur $traductions
  foreach ($traductions->get_traductions() as $langue => $traduction) {	//Mise à jour de la traduction dans chaque langue
    $traduction = string_make_pgcompatible($traduction);
    if ($traduction == "") {//Si aucune traduction a été entrée pour cette langue
      if ($langue == get_langue_systeme_par_defaut())
        //Erreur: string non traduit dans la langue syst par défaut
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // String non traduit dans la langue système par défaut
      //On supprime la traduction
      $sql = "DELETE FROM ad_traductions WHERE (id_str=".$traductions->get_id_str().")
             AND (langue='$langue');";
    } else {
      //On cherche s'il existe déjà une traduction pour cette langue
      $sql =  "SELECT * FROM ad_traductions ";
      $sql .= "WHERE ((id_str=".$traductions->get_id_str().") AND (langue='$langue'));";

      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      };

      if ($result->numRows() == 1)
        //Si une traduction a été entrée pour cette langue
      {
        $sql = "UPDATE ad_traductions SET traduction='$traduction' ";
        $sql .= "WHERE ((id_str=".$traductions->get_id_str().") AND (langue='$langue'));";
      } else {	// cas de l'insertion d'une nouvelle traduction
        $sql = "INSERT INTO ad_traductions (id_str, langue, traduction) ";
        $sql .= "VALUES (".$traductions->get_id_str().", '$langue', '$traduction');";
      };
    };
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    };
  };
  $db = $dbHandler->closeConnection(true);
};

/**
 * Cette fonction crée un nouvel identifieur de string, et insère les traductions passées en paramètre dans la table ad_traductions)
 * Elle ne devrait être appellée que depuis l'objet Trad
 * @author Olivier LUYCKX
 * @since 1.0.8m
 * @param object Trad
 */

function db_creer_traductions(&$traductions) {
  if (!is_trad($traductions))
    signalErreur(__FILE__,__LINE__,__FUNCTION__);

  // Etape 1: création d'une traduction pour le string dans la langue système par défaut
  global $dbHandler, $global_langue_systeme_dft;
  $db = $dbHandler->openConnection();
  $traduction = string_make_pgcompatible($traductions->traduction($global_langue_systeme_dft));

  $sql = "SELECT makeTraductionLangSyst('".$traduction."');";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  };

  $row = $result->fetchrow();
  $dbHandler->closeConnection(true);

  $traductions->set_id_str($row[0]);

  // Etape 2: maintenant qu'un identifieur de string a été créé, on peut appeller save (sans faire une boucle infinie)
  $traductions->save();
  return $row[0];
};
/**
 * Cette fonction supprime les traductions passées en paramètre dans la table ad_traductions)
 * Elle ne devrait être appellée que depuis l'objet Trad
 * @author Ibou ndiaye
 * @since 1.0.8m
 * @param object Trad
 */

function db_supprimer_traductions(&$traductions) {
  if (!is_trad($traductions))
    signalErreur(__FILE__,__LINE__,__FUNCTION__);

  global $dbHandler;
  $db = $dbHandler->openConnection();

  //ETAPE1: On vérifie d'abord que le $id_string passé existe bien
  $sql = "DELETE FROM ad_str WHERE id_str=".$traductions->get_id_str();

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  };
  $db = $dbHandler->closeConnection(true);
 };
?>