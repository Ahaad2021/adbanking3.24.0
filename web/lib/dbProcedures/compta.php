<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2: */

/**
 * Procédures / accès DB pour les fonctions du menu comptabilité
 *
 * @author Thomas Fastenakel
 * @since 1.0 26/08/2003
 * @package Compta
 */

require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/misc/divers.php';

/**
 * Fonction renvoyant les informations sur les comptes comptables définis dans le plan comptable
 * @since 1.0
 * @param array $fields_values Array permettant de construire une clause WHERE pour le SELECT.
 * Si argument est NULL, on renvoie tous les comptes. L'array a la forme (fieldname=>value recherchée).
 * @return array On renvoie un tableau de la forme (numéro compte => infos compte)
 */
function getComptesComptables($fields_values=NULL, $niveau=NULL,$date_modif=NULL) {
  global $dbHandler,$global_id_agence;

	//vérifier qu'on reçoit bien un array
  if (($fields_values != NULL) && (! is_array($fields_values)))
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Mauvais argument dans l'appel de la fonction"
  $db = $dbHandler->openConnection();
  if($date_modif == NULL){
  	 $sql = "SELECT * FROM ad_cpt_comptable WHERE id_ag = $global_id_agence AND is_actif = 't' AND ";
  }else{
  	 $date_mod= php2pg($date_modif);
  	 $sql = "SELECT * FROM ad_cpt_comptable WHERE id_ag = $global_id_agence AND ((is_actif = 't') OR (is_actif = 'f' AND date_modif > '$date_mod')) AND ";
  }
  if (isset($fields_values)) {

    foreach ($fields_values as $key => $value)
    if (($value == '') or ($value == NULL))
      $sql .= "$key IS NULL AND ";
    else
      $sql .= "$key = '$value' AND ";

  }
  $sql = substr($sql, 0, -4);
  $sql .= "ORDER BY id_ag, num_cpte_comptable ASC";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $cptes = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    if (getNiveauCompte($row["num_cpte_comptable"]) <= $niveau && $niveau != NULL) {
      $cptes[$row["num_cpte_comptable"]] = $row;
    }
  elseif($niveau == NULL) {
    $cptes[$row["num_cpte_comptable"]] = $row;
  }


  $dbHandler->closeConnection(true);

  return $cptes;
}

/**
 * //K
 * Fonction renvoyant les informations sur les comptes comptables définis dans le plan comptable
 * @since 
 * @param array $fields_values Array permettant de construire une clause WHERE pour le SELECT.
 * Si argument est NULL, on renvoie tous les comptes. L'array a la forme (fieldname=>value recherchée).
 * @return array On renvoie un tableau de la forme (numéro compte => num_cpte_comptable,libel_cpte_comptable)
 */
function getNumLibelComptables($fields_values=NULL, $niveau=NULL,$date_modif=NULL) {
	global $dbHandler,$global_id_agence;

	//vérifier qu'on reçoit bien un array
	if (($fields_values != NULL) && (! is_array($fields_values)))
		signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Mauvais argument dans l'appel de la fonction"
	$db = $dbHandler->openConnection();
	if($date_modif == NULL){
		$sql = "SELECT num_cpte_comptable,libel_cpte_comptable FROM ad_cpt_comptable WHERE id_ag = $global_id_agence AND is_actif = 't' AND ";
	}else{
		$date_mod= php2pg($date_modif);
		$sql = "SELECT num_cpte_comptable,libel_cpte_comptable FROM ad_cpt_comptable WHERE id_ag = $global_id_agence AND ((is_actif = 't') OR (is_actif = 'f' AND date_modif > '$date_mod')) AND ";
	}
	if (isset($fields_values)) {

		foreach ($fields_values as $key => $value)
			if (($value == '') or ($value == NULL))
				$sql .= "$key IS NULL AND ";
			else
				$sql .= "$key = '$value' AND ";

	}
	$sql = substr($sql, 0, -4);
	$sql .= "ORDER BY id_ag, num_cpte_comptable ASC";
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}

	$cptes = array();
	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
		if (getNiveauCompte($row["num_cpte_comptable"]) <= $niveau && $niveau != NULL) {
			$cptes[$row["num_cpte_comptable"]] = $row;
		}
	elseif($niveau == NULL) {
		$cptes[$row["num_cpte_comptable"]] = $row;
	}


	$dbHandler->closeConnection(true);

	return $cptes;
}

/**
 * Fonction renvoyant les informations sur les comptes credits dans la table adsys_etats_credits_cptes
 * @since 1.0
 * @param array $fields_values Array permettant de construire une clause WHERE pour le SELECT.
 * Si argument est NULL, on renvoie tous les comptes. L'array a la forme (fieldname=>value recherchée).
 * @return array On renvoie un tableau de la forme (numéro compte => infos compte)
 */
function getComptesAssocieAuxCredits() {
	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();

	$sql ="SELECT num_cpte_comptable FROM adsys_etat_credit_cptes ";
	$sql .="WHERE id_ag = $global_id_agence ";
	$sql .="group BY id_ag, num_cpte_comptable ";
	$sql .="ORDER BY id_ag, num_cpte_comptable ASC ;";
	

	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}

	$credits = array();
	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){

		$credits[$row["num_cpte_comptable"]] = $row;
	}

	$dbHandler->closeConnection(true);
	return $credits;

}

/**
 * Mettre à jour l'état du compte à false cad on ne l'affiche parés la date de modification
 * @param string $num_cpte_comptable le numéro de compte comptable à supprimer
 * @return un void
 */
function updateEtatCpt($num_cpte_comptable){
	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();

    $sql = "UPDATE ad_cpt_comptable set is_actif='f', date_modif='".date("Y-m-d")."' WHERE id_ag = $global_id_agence AND num_cpte_comptable LIKE '$num_cpte_comptable';";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
   }
   $dbHandler->closeConnection(true);
}
function getNomsComptesComptables($fields_values) {
  $retour = array();
  $comptes = getComptesComptables($fields_values);
  foreach ($comptes AS $key=>$compte)
  $retour[$compte["num_cpte_comptable"]] = $compte["num_cpte_comptable"]." ".$compte["libel_cpte_comptable"];
  return $retour;
}
function getNomsComptesComptablesMA($fields_values) {
  $retour = array();
  //$comptes = getComptesComptables($fields_values,NULL,"01/01/1970");
  $comptes = getComptesComptables($fields_values);
  foreach ($comptes AS $key=>$compte)
  $retour[$compte["num_cpte_comptable"]] = $compte["num_cpte_comptable"]." ".$compte["libel_cpte_comptable"];
  return $retour;
}

/**
 * Fonction renvoyant l'ensemble des classes comptables définies dans le plan comptable.
 *
 * @return array index => array("numero_classe" => Numéro de la classe
 *                              "libel_classe" => Libellé de la classe)
 */
function getClassesComptables() {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_classes_compta WHERE id_ag=$global_id_agence ORDER BY numero_classe";

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($CC, $row);

  return $CC;
}
/**
 * Liste des classes comptables
 */
function getListeClasseComptable() {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_classes_compta WHERE id_ag=$global_id_agence ORDER BY numero_classe";

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $CC[$row['id_classe']]= $row['libel_classe']." ".$row['numero_classe'];

  return $CC;
}
/**
 * Fonction qui ajoute un nouveau compte comptable <B>principal</B> dans le plan comptable
 *
 * @author Papa Ndiaye
 * @since 1.0.8
 * @param text $num Numéro de compte
 * @param text $libel Libellé du compte
 * @param char $sens Sens naturel du compte
 * @param char $classe Classe comptable
 * @param int $compartiment Actif, Passif, Charges ou Produits
 * @param text $cpte_centralise Indique si le compte est un sous-compte d'un compte centralisateur
 * @param float $solde Solde d'ouverture du compte
 * @param char(3) Devise du compte
 * @param text $cpte_provision Compte de provision
 * @return ErrorObj Objet erreur
 */
function ajoutCompteComptable($num, $libel,$sens,$classe, $compartiment,$cpte_centralise,$solde, $devise=NULL, $cpte_provision=NULL) {
  global $dbHandler, $global_nom_login,$global_id_agence;
  $db = $dbHandler->openConnection();

  // Si la devise n'est pas précisée, c'est la devise de référence
  global $global_multidevise;
  if (!$global_multidevise) {
    global $global_monnaie;
    $devise = $global_monnaie;
  }

  // Vérifie que le compte n'existe pas dans la DB
  $sql = "SELECT * FROM ad_cpt_comptable WHERE num_cpte_comptable = '$num' and id_ag=$global_id_agence";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() > 0) {
    $dbHandler->closeConnection(true);
    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
    return new ErrorObj(ERR_CPT_EXIST, $num);
  }

  // Insertion du signe pour le solde
  if ($compartiment == 1 || $compartiment == 3) // Compte d'actif ou de charges
    $solde = -$solde;

  // Construction de la requête d'insertion
  $DATA = array();
  $DATA["num_cpte_comptable"] = $num;
  $DATA["libel_cpte_comptable"] = $libel;
  $DATA["sens_cpte"] = $sens;
  $DATA["classe_compta"] = $classe;
  $DATA["compart_cpte"] = $compartiment;
  $now = date("Y-m-d");
  $DATA["date_ouvert"] = $now;
  $DATA["etat_cpte"] = 1;
  $DATA["solde"] = 0;
  $DATA["cpte_princ_jou"] = 'f';
  $DATA["cpte_centralise"] = NULL;
  $DATA["niveau"] = 1;
  $DATA["devise"] = $devise;
  $DATA["cpte_provision"] = $cpte_provision;
  $DATA["id_ag"] = $global_id_agence;

  $sql = buildInsertQuery("ad_cpt_comptable",$DATA);

  // Insertion dans la DB
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $erreur=ajout_historique(410, NULL, _("Ajout compte principal"), $global_nom_login, date("r"), NULL);
  if ($erreur->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $erreur;
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

function updateCompteComptable($num,$Fields) {
  // Cette fonction met à jour les informations sur un compte comptable
  // IN: $num = Le numéro du compte à modfiier
  //     $libel = Le nouveau libellé
  // OUT: Objet ErrorObj

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  //on donne la possibilité de modifier le sens d'un comptable : 
/*  if ($Fields['sens_cpte'] != NULL && getMouvementsComptables(array('compte' => $num), 1) != NULL) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }*/

  if ($Fields['cpte_provision'] != NULL ) {
  	$infoscpteprov=getComptesComptables(array("num_cpte_comptable"=>$Fields["cpte_provision"]));
    if($infoscpteprov[$Fields["cpte_provision"] ]["devise"] != $Fields["devise"] ) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_DEV_DIFF_CPT_PROV, $Fields["devise"]);
        }
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  // Construction de la requête de mise à jour
  // $Fields = array("libel_cpte_comptable" => $libel);
  $Where = array("num_cpte_comptable" => $num,'id_ag'=>$global_id_agence);
  $sql = buildUpdateQuery("ad_cpt_comptable", $Fields, $Where);

  // Mise à jour de la DB
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

function ajoutSousCompteComptable($compte_centralisateur,$liste_sous_comptes, $solde_reparti=NULL) {
  /*
     Fonction qui ajoute des sous-comptes à un compte comptable

     IN: - $compte_centralisateur = le numéro du compte auquel on veut ajouter des sous-comptes
         - $liste_sous_comptes = tableau contenant la liste des sous-comptes au format
           array (n° cpte => array(n° cpte, libel, solde de départ, devise))

     OUT : Objet ErrorObj
  */
  global $dbHandler, $global_nom_login,$global_id_agence;

  $db = $dbHandler->openConnection();
  $global_id_agence=getNumAgence();
  //Recupèration des infos du compte centralisateur
  $param["num_cpte_comptable"]=$compte_centralisateur;
  $infocptecentralise = getComptesComptables($param);

  // Verifier s'il n y a pas, pour le compte centralisateur, des ecritures en attente dans le brouillard
  $ecriture_attente = isEcritureAttente($compte_centralisateur);
  if ($ecriture_attente == true) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPT_ECRITURE_EXIST, $compte_centralisateur);
  }

  // Récupère le nombre de sous-comptes du compte centralisateur
  $nbre_souscompte = getNbreSousComptesComptables($compte_centralisateur) ;

  // Vérifie si c'est la première création de sous-comptes pour le compte centralisateur
  if ($nbre_souscompte == 0 ) {
    // première création, Vérifier alors que solde du compte centralisateur est complétement réparti entre les sous-comptes

    $soldesc=0; // la somme des soldes des sous-comptes
    if (isset($liste_sous_comptes))
      foreach($liste_sous_comptes as $key=>$value)
      $soldesc = $soldesc + abs($value["solde"]);
    if ($solde_reparti == NULL) {
      if ($infocptecentralise[$compte_centralisateur]['compart_cpte'] == 3 OR $infocptecentralise[$compte_centralisateur]['compart_cpte'] == 4) {
        $solde_reparti = calculeSoldeCpteGestion($compte_centralisateur);
      } else {
        $solde_reparti = $infocptecentralise[$compte_centralisateur]['solde'];
      }
    }

    //comparaison entre la sommme des soldes et le solde du compte centralisateur
    if ( abs($solde_reparti) != $soldesc) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_SOLDE_MAL_REPARTI, $compte_centralisateur);
    }
  }
  // Ajout des sous comptes
  if (isset($liste_sous_comptes)) // parcours de la liste des sous-comptes
    foreach($liste_sous_comptes as $key=>$value)
    if ($key!='') {
      // Vérifier que le sous-compte n'existe pas dans la DB
      $sql = "SELECT * FROM ad_cpt_comptable WHERE id_ag=$global_id_agence and num_cpte_comptable='$key';";
      // FIXME : Utiliser getComptesComptables ?
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }
      
   //if compte exist deja on modifier le procedure
   /*
     if ($result->numRows() > 0) {
       $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPT_EXIST, $key);
    }*/


      // Héritage automatique de la devise du compte centralisateur
      if (!isset($value["devise"]) && isset($infocptecentralise[$compte_centralisateur]["devise"]))
        $value["devise"] = $infocptecentralise[$compte_centralisateur]["devise"];

      // Vérfieir si la devise du sous-compte n'est pas différente de la devise du compte centralisateur
      if ($infocptecentralise[$compte_centralisateur]["devise"] != NULL && $infocptecentralise[$compte_centralisateur]["devise"] != $value["devise"]) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_DEV_DIFF_CPT_CENTR, $value["devise"]);
      }
       // Construction de la requête d'insertion de sous-compte
      $DATA = array();
     
	 // Vérifier si la devise du sous-compte n'est pas différente de la devise du compte de provision
      if ( $value['cpte_provision'] != "[Aucun]" &&  $value["cpte_provision"] != NULL) {
      	$infoscpteprov=getComptesComptables(array("num_cpte_comptable"=>$value["cpte_provision"]));
        if($infoscpteprov[$value["cpte_provision"] ]["devise"] != $value["devise"] ) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_DEV_DIFF_CPT_PROV, $value["devise"]);
        }
        $DATA["cpte_provision"] =$value["cpte_provision"];
      } else {
      	$DATA["cpte_provision"] =NULL;
      }

      $DATA["num_cpte_comptable"] = $value["num_cpte_comptable"];
      $DATA["libel_cpte_comptable"] = $value["libel_cpte_comptable"];
      if ($value["compart_cpte"]!='') // si le compartiment n'edst pas renseigné alors il l'hérite du compte père
        $DATA["compart_cpte"] = $value["compart_cpte"];
      else
        $DATA["compart_cpte"] = $infocptecentralise[$compte_centralisateur]["compart_cpte"];

      if ($value["sens_cpte"]!='') // si le sens n'est pas renseigné alors il l'hérite du compte père
        $DATA["sens_cpte"] = $value["sens_cpte"];
      else
        $DATA["sens_cpte"] = $infocptecentralise[$compte_centralisateur]["sens_cpte"];

      $DATA["classe_compta"] = $infocptecentralise[$compte_centralisateur]["classe_compta"];
      //$DATA["cpte_centralise"] = $compte_centralisateur;

      if ($infocptecentralise[$compte_centralisateur]['cpte_princ_jou']=='t')
        $DATA["cpte_princ_jou"] = 't';
      else
        $DATA["cpte_princ_jou"] = 'f';

      $DATA["solde"] = 0;

      $now = date("Y-m-d");
      $DATA["date_ouvert"] = $now;
      $DATA["etat_cpte"] = 1;
      $DATA["id_ag"] = $global_id_agence;
      $DATA["devise"] = $value["devise"];
      
      
      
      // pour cas ou  le sous compte exist deja  on va faire un update
      $DATA["is_actif"] = TRUE;
      $Where = array("num_cpte_comptable" => $key,'id_ag'=> $global_id_agence,'is_actif'=>'FALSE');
      
      if ($result->numRows() > 0){	
      	$sql = buildUpdateQuery("ad_cpt_comptable", $DATA, $Where);

      	}
      	// else insert normal
      else{
    	
      $sql = buildInsertQuery("ad_cpt_comptable",$DATA);
   
      }
      // Insertion dans la DB 
       $result = $db->query($sql);
        if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }

      //Recherche des contrepartie pour le compte centralisateur
      $cpt_cptie=getInfosJournalCptie(NULL,$compte_centralisateur);
        if(is_array($cpt_cptie)){
      	foreach($cpt_cptie as $key1=>$DATA){
      		foreach($liste_sous_comptes as $key2=>$value2){
      		 //ajout dans le journal des contreparties
      		 // ajoutjournalCptie verifie si il y a une entre dans la table ad_cpt_comptable avec le nuvo num_cpte_comptable
      		 $myErr=ajoutJournalCptie($DATA["id_jou"], $value2["num_cpte_comptable"]);
		     if ($myErr->errCode != NO_ERR) {
      			$html_err = new HTML_erreur(_("Echec création journal. "));
      			$html_err->setMessage(_("Erreur")." : ".$myErr->param);
      			$html_err->addButton("BUTTON_OK", 'Jou-6');
      			$html_err->buildHTML();
      			echo $html_err->HTML_code;
    		 }
  			}
      	}
      }
      // Insertion dans la DB
     /* $result = $db->query($sql);
     /* if (DB::isError($result)) {
      	$dbHandler->closeConnection(false);
      	signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }*/

      if ( abs($solde_reparti) != 0 && ($value['solde'] != 0)) {
        // Passage des écritures comptables
        $comptable = array();
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        if ($solde_reparti < 0 ) {
          //crédit du compte centralisateur par le débit d'un sous-compte
          $cptes_substitue["cpta"]["debit"] = $key;
          $cptes_substitue["cpta"]["credit"] = $compte_centralisateur;
        } else {
          //débit d'un sous compte par le credit du compte centralisateur
          $cptes_substitue["cpta"]["debit"] = $compte_centralisateur;
          $cptes_substitue["cpta"]["credit"] = $key;
        }
        $myErr = passageEcrituresComptablesAuto(1003, abs($value["solde"]), $comptable, $cptes_substitue, $value["devise"]);
        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }
        $erreur=ajout_historique(410, NULL, _("Virement solde compte principal"), $global_nom_login, date("r"), $comptable);
        if ($erreur->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $erreur;
        }
      }
    }

  // Mise à jour du champs compte centralisateur des sous-compte
  if (isset($liste_sous_comptes)) // parcours de la liste des sous-comptes
    foreach($liste_sous_comptes as $key=>$value)
    if ($key!='') {
    	$niveau = getNiveauCompte($compte_centralisateur) + 1;
      $sql = "UPDATE ad_cpt_comptable set cpte_centralise='$compte_centralisateur', niveau = $niveau WHERE id_ag=$global_id_agence AND num_cpte_comptable = '$key'";
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }
    }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

function getNbreSousComptesComptables($num_cpte,$a_isActif=NULL) {
  /*

   Fonction renvoyant le nombre de sous comptes d'un compte principal définis dans le plan comptable
   IN : numero du compte

   OUT: nombre de sous compte   */

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT count(num_cpte_comptable) FROM ad_cpt_comptable where id_ag=$global_id_agence and  num_cpte_comptable like '$num_cpte.%' ";
  if($a_isActif != NULL){
  	$sql .=" AND is_actif='".$a_isActif."' ";

  }

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $row = $result->fetchrow();

  return $row[0];
}
/**
  * Teste si compte :
  * <li>
  *     <ul> N'est pas centralisateur pour le monodevise</ul>
  *     <ul> Est centralisateur sans devise pour le mutidevise</ul>
  * </li>
  *
  * @param string $num_cpte Numero de compte comptable
  * @return boolean True si compte passé est feuille en monodevise, ou centralisateur sans devise si multidevise, False sinon.
  * @author Saourou Mbodj
  * @since 2.8
  */
function isNotCentralisateurSansDevise($num_cpte) {
  global $global_multidevise,$global_id_agence;

  // Teste d'abord si c'est un compte comptable
  $sql = "SELECT COUNT(*) FROM ad_cpt_comptable where id_ag=$global_id_agence and num_cpte_comptable = '$num_cpte' ";
  $result = executeDirectQuery($sql, true);
  if ($result->errCode != NO_ERR || $result->param[0] < 1)
    return false;

  // Si c'est un compte comptable, il faut vérifier pour la multidevise et la monodevise
  if (!$global_multidevise)
    // Monodevise
    return !isCentralisateur($num_cpte);
  else {
    // MultiDevise
    if (!isCentralisateur($num_cpte))
      // Si c'est un compte comptable feuille
      return true;
    else {
      // Si c'est un compte centralisateur, il faut qu'il soit sans devise
      $sql = "SELECT COUNT(*) FROM ad_cpt_comptable where num_cpte_comptable = '$num_cpte' AND id_ag = $global_id_agence AND devise IS NULL ";
      $result = executeDirectQuery($sql, true);
      if ($result->errCode != NO_ERR || $result->param[0] < 1)
        return false;
      else
        return true;
    }
  }
}
/**
 * @description: Calcul le niveau d'un compte
 * @param text Numéro d'un Compte comptables
 * @return int le niveau du compte comptable
 */
function getNiveauCompte($compte) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  //On commence par récupérer le numéro de lot
  $sql = "SELECT getNiveau('$compte',$global_id_agence) ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
  }
  $row = $result->fetchrow();
  $niveau = $row[0];
  $dbHandler->closeConnection(true);
  return $niveau;

}
/**
 * @description: Cette fonction permet de récupèrer le niveau maximum des comptes comptables
 * @return int le niveau maximum des comptes comptables
 */
function getNiveauMaxComptesComptables($list_agence) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $niveau_max=0;
  foreach($list_agence as $key_id_ag =>$value) {
    //Parcours des agences
    setGlobalIdAgence($key_id_ag);
    $sql = "SELECT num_cpte_comptable FROM ad_cpt_comptable WHERE id_ag= $global_id_agence";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $cptes = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      if (!isCentralisateur($row["num_cpte_comptable"])) {
        if (getNiveauCompte($row["num_cpte_comptable"])>=$niveau_max) {
          $niveau_max=getNiveauCompte($row["num_cpte_comptable"]);
        }
      }

    }
  }//Fin parcours des agences
  $dbHandler->closeConnection(true);
  //Suppression des points(.) entre les chiffres
  $niveau_max= ($niveau_max+1)/2;
  return $niveau_max;
}
/**
 * Verifie si le compte est un compte de crédits
 * @param string $num_cpte Le numéro du compte comptable.
 * @return boolean True si compte est un compte de crédit, False sinon.
 */
function isComptesCredits($num_cpte) {
  global $global_id_agence;
  $sql = "SELECT COUNT(*) FROM adsys_etat_credit_cptes where num_cpte_comptable ='$num_cpte'  ";
  $result = executeDirectQuery($sql, true);
  if ($result->errCode != NO_ERR) {
    return false;
  } else {
    return ($result->param[0] > 0);
  }
}

/**
 * Verifie si le compte est un compte associé au produit d'épargne
 * @param string $num_cpte Le numéro du compte comptable.
 * @return boolean True si compte est associé à un produit d'épargne, False sinon.
 */
function isComptesEpargne($num_cpte) {
  global $global_id_agence;
  $sql = "SELECT COUNT(*) FROM adsys_produit_epargne where cpte_cpta_prod_ep ='$num_cpte'  ";
  $result = executeDirectQuery($sql, true);
  if ($result->errCode != NO_ERR) {
    return false;
  } else {
    return ($result->param[0] > 0);
  }
}

/**
 * Verifie si le compte est un compte de garanties
 * @param string $num_cpte Le numéro du compte comptable.
 * @return boolean True si compte est compte de garanties, False sinon.
 */
function isComptesGaranties($num_cpte) {
  global $global_id_agence;
  $sql = "SELECT COUNT(*) FROM adsys_produit_credit where cpte_cpta_prod_cr_gar ='$num_cpte'  ";
  $result = executeDirectQuery($sql, true);
  if ($result->errCode != NO_ERR) {
    return false;
  } else {
    return ($result->param[0] > 0);
  }
}

/**
 * Verifie si le compte est centralisateur, c'est à dire s'il a des sous-comptes.
 *
 * @param string $num_cpte Le numéro du compte comptable.
 * @return boolean True si compte possède des sous comptes, False sinon.
 */
function isCentralisateur($num_cpte) {
  global $global_id_agence;
  $sql = "SELECT COUNT(*) FROM ad_cpt_comptable where cpte_centralise ='$num_cpte'  ";
  $result = executeDirectQuery($sql, true);
  if ($result->errCode != NO_ERR) {
    return false;
  } else {
    return ($result->param[0] > 0);
  }
}

/**
 * Fonction vérifiant si un compte comptable est compte de provision d'un autre compte comptable
 * @author Papa
 * @since 1.0
 * @param text $num_compte le numéro du compte comptable.
 * @return boolean On renvoie true si le compte est compte de provision sinon false
 */
function isCompteDeProvision($num_compte) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_cpt_comptable where id_ag=$global_id_agence and cpte_provision ='$num_compte'";
  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numrows()==0)
    return false;
  else
    return true;

}

/**
 * Fonction vérifiant si un compte comptable est compte principal,
 * Un compte est dit compte principal s'il ne possède pas de compte centralisateur
 * En d'autre terme, tout compte fils d'une classe comptable est compte principal
 * @author Papa
 * @since 1.0
 * @param text $num_compte le numéro du compte comptable.
 * @return boolean On renvoie true si le compte est compte de principal sinon false
 */
function isComptePrincipal($num_compte) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_cpt_comptable where id_ag=$global_id_agence and  num_cpte_comptable ='$num_compte' AND cpte_centralise is null ";
  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numrows()==0)
    return false;
  else
    return true;

}

/**
 *  Verifie si on peut modifier une devise ou non (une devise pour un compte peut etre modifier que  si elle est NULL  et que ce compte n'a pas de sous comptes)
 * @author Mamadou Mbaye
 * @param int $num_cpte le numéro du compte
 * @return true si la devise peut etre modifier et false si non
 */
function canModifyDevise($num_cpte) {
  global $global_id_agence;
  // verifier que le compte n'a pas de sous compte
  if (isCentralisateur($num_cpte))
    return false;

  // verifier que le compte n'a pas de sous comptedevise qui lui est associé
  $tmp["num_cpte_comptable"]=$num_cpte;
  $value= getComptesComptables($tmp);
  if ($value[$num_cpte]["devise"] != NULL)
    return false;
  return true;
}

function isEcritureAttente($num_cpte) {
  //Verifie s'il y des ecritures en attente sur le compte

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT count(compte) FROM ad_brouillard where id_ag=$global_id_agence and compte ='$num_cpte' ";
  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $row = $result->fetchrow();

  if ($row[0] > 0)
    return true;
  else
    return false;
}

/**
 * Renvoie la catégorie d'un compte (adsys_categorie_compte) en fonction d'un compte
 * <B> NB. Fonction doit etre mise à jour du fait de l'apparition des nouvelles catégoriels </B>
 * @param int $num_cpte Numéro du compte
 * @author Papa Ndiaye (?)
 * @return int Catégorie du compte
 * @info DEPRECATED since 2.5
 */
function getCategorie($num_cpte) {
  //Renvoi la categorie du compte

  global $dbHandler;
  global $global_id_agence;

  $db = $dbHandler->openConnection();

  //Verifie si la categorie est compte d'epargne
  $sql = "SELECT * FROM adsys_produit_epargne where id_ag=$global_id_agence and cpte_cpta_prod_ep ='$num_cpte' ";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() != 0)

    return 1;

  // Verifie si la categorie est compte de credit
  $sql = "SELECT * FROM adsys_produit_credit where id_ag=$global_id_agence and cpte_cpta_prod_cr ='$num_cpte' or cpte_cpta_prod_cr_sf ='$num_cpte'";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() != 0)

    return 2;

  //Verifie si la categorie est compte du coffre-fort
  $sql = "SELECT * FROM ad_agc where id_ag=$global_id_agence and cpte_cpta_coffre ='$num_cpte' ";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() != 0)

    return 3;

  //Verifie si la categorie est compte guichet
  $sql = "SELECT * FROM ad_gui where id_ag=$global_id_agence and cpte_cpta_gui ='$num_cpte' ";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() != 0)

    return 4;


  //Verifie si la categorie est compte de banque
  $sql = "SELECT * FROM adsys_banques where id_ag=$global_id_agence and cpte_cpta_bqe ='$num_cpte' ";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() != 0)

    return 5;

  //Verifie si la categorie est compte d'interêts sur les credits
  $sql = "SELECT * FROM adsys_produit_credit where id_ag=$global_id_agence and cpte_cpta_prod_cr_int ='$num_cpte' ";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() != 0)

    return 6;

  //Verifie si la categorie est compte de penalites sur les credits
  $sql = "SELECT * FROM adsys_produit_credit where id_ag=$global_id_agence and cpte_cpta_prod_cr_pen ='$num_cpte' ";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() != 0)

    return 7;

  //Verifie si la categorie est compte garantie
  $sql = "SELECT * FROM adsys_produit_credit where id_ag=$global_id_agence and cpte_cpta_prod_cr_gar ='$num_cpte' ";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() != 0)

    return 8;


  //Verifie si la categorie est compte de parts sociales
  $id_prod_ps = getPSProductID ($global_id_agence);
  $sql = "SELECT * FROM adsys_produit_epargne where id_ag=$global_id_agence and cpte_cpta_prod_ep = '$num_cpte' AND id = $id_prod_ps";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() != 0)

    return 9;

  //Verifie si la categorie est compte d'interêts sur epargne
  $sql = "SELECT * FROM adsys_produit_epargne where id_ag=$global_id_agence and cpte_cpta_prod_ep_int ='$num_cpte' ";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() != 0)

    return 10;

  return 0;

  $dbHandler->closeConnection(true);

}


/* ************************** Gestion des Operations ***********************************/

function getOperations($id_oper=0) {
  // Fonction renvoyant toutes les associations définies selon les opérations ou les informations concernant une opération particulière
  // IN : $id_oper = 0 ==> Renvoie toutes les opérations
  //               > 0 ==> Renvoie l'opération id_oper
  // OUT: Objet ErrorObj avec en param :
  //      Si $id_oper = 0 : array($key => array("type_operation", "libel", "cptes" = array ("sens" = array("categorie, "compte")))
  //                  > 0 : array("libel") = libellé de l'opération

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_cpt_ope ";
  if ($id_oper == 0)
    $sql .= "WHERE id_ag = $global_id_agence ORDER BY type_operation";
  else
    $sql .= "WHERE type_operation = $id_oper and id_ag = $global_id_agence ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($id_oper > 0) {
    if ($result->numRows() == 0) {
      // Il n'y a pas d'association pour cette opération
      $dbHandler->closeConnection(false);
      return new ErrorOBj(ERR_NO_ASSOCIATION, "L'opération $id_oper n'existe pas");
    } else {
      $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
      $dbHandler->closeConnection(true);
      return new ErrorObj(NO_ERR, array("libel" => $row["libel_ope"], "type_operation" => $row["type_operation"], "categorie_ope" => $row["categorie_ope"]));
    }
  } else {
    $OP= array();
    while ($rows = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      $sql = "SELECT * FROM ad_cpt_ope_cptes WHERE id_ag = $global_id_agence and type_operation = ".$rows["type_operation"];
      $result2 = $db->query($sql);
      if (DB::isError($result2)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }
      while ($row_cptes = $result2->fetchrow(DB_FETCHMODE_ASSOC)) {
        $rows["cptes"][$row_cptes["sens"]] = $row_cptes;
      }

      array_push($OP,$rows);
    }
    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR, $OP);
  }
}



/**
 * Fonction renvoyant les informations au débit et au crédit d'une ou des opérations ADbanking
 * @author
 * @since
 * @param int $type_oper le numéro de l'opération
 * @return Objet ErrorObj avec param contenant le tableau les infos au débit au crédit de l'opération ADbanking
 * le tableau des infos est de la forme array(debit => array(compte, sens, categorie
                                             credit => array(compte, sens, categorie)
*/
function getDetailsOperation($type_oper) {
  global $dbHandler, $global_id_agence, $global_langue_systeme_dft;
  $db = $dbHandler->openConnection();

  // récupération du détail de l'opération
  $sql = "SELECT * FROM ad_cpt_ope_cptes WHERE id_ag=$global_id_agence and type_operation = $type_oper ORDER BY sens DESC;";
  $result = $db->query($sql);
  $dbHandler->closeConnection(true);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() == 0) // Il n'y a pas d'association pour cette opération
    return new ErrorOBj(ERR_NO_ASSOCIATION);

  $OP = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    if ($row["sens"] == SENS_DEBIT) // informations au débit de l'opération
      $OP["debit"] = array("compte"=>$row["num_cpte"], "sens"=>$row["sens"], "categorie"=>$row["categorie_cpte"]);
    elseif($row["sens"] == SENS_CREDIT)  // informations au crédit de l'opération
    $OP["credit"] = array("compte"=>$row["num_cpte"],"sens"=>$row["sens"],"categorie"=>$row["categorie_cpte"]);
  }

  return new ErrorObj(NO_ERR, $OP);
}

function getOperationsCompte($compte) {
  /*
    Fonction qui renvoie les opérations dans lesquelles est uilisé le compte comptable.
    On renvoie un array qui contiendra la liste des numéros d'opération
      Cet array est paramètre de 'objet Erreur

    FIXME : vérifier qu'on a passé un n° d'opération
  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT a.type_operation, libel_ope FROM ad_cpt_ope_cptes a, ad_cpt_ope b ";
  $sql .= " WHERE a.id_ag=b.id_ag and a.id_ag=$global_id_agence and num_cpte = '$compte' and a.type_operation = b.type_operation ";
  $sql .= " ORDER BY a.type_operation;";

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() == 0) {
    return NULL;
  }
  $OP = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($OP, $row);

  return $OP;

}
function updateOperation($id, $debit, $credit)
// Cette fonction met à jour les informations sur une opération
// IN: $id = L'ID de l'opération à modifier
//     $id_ag : le numéro de l'agence
//   $debit : Le numéro di compte qui sera débité
//     $credit : Le numéro di compte qui sera crédité
// OUT: Objet ErrorObj
{
  global $dbHandler,$global_id_agence;
  $global_id_agence=getNumAgence();
  $db = $dbHandler->openConnection();

  // Construction de la resquête de mise à jour
  // $Fields = array("num_cpte_debit" => $debit, "num_cpte_credit" => $credit);
  /*$Fields = array("jou_ope" => $journal);
  $Where = array("type_operation" => $id);
  $sql = buildUpdateQuery("ad_cpt_ope", $Fields, $Where);

  // Mise à jour de la DB
  $result = $db->query($sql);
  if (DB::isError($result))
    {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  */
  //Mise à jour du compte au débit, si aucun compte n'était pas défini dans les opérations , on doit faire un e insertion dans la table ad_cpt_ope_cptes

  $sql = "select *  from ad_cpt_ope_cptes where  sens = 'd' and type_operation = ".$id." and id_ag = ".$global_id_agence ;

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numrows() == 0) {
    // Construction de la requête d'insertion
    $DATA = array();
    $DATA["type_operation"] = $id;
    $DATA["id_ag"] = $global_id_agence;
    $DATA["num_cpte"] = $debit;
    $DATA["sens"] = 'd';
    $DATA["categorie_cpte"] = 0;
    $DATA["id_ag"] = $global_id_agence;
    $sql = buildInsertQuery("ad_cpt_ope_cptes",$DATA);

    // Insertion dans la DB
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  } else {
    if (isset($debit))
      $sql = "update ad_cpt_ope_cptes set num_cpte = '$debit'  where id_ag = ".$global_id_agence." AND type_operation = ".$id." and sens = 'd'";
    else
      $sql = "update ad_cpt_ope_cptes set num_cpte = NULL  where id_ag = ".$global_id_agence." AND type_operation = ".$id." and sens = 'd'";

    // Mise à jour de la DB
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  }

  //Mise à jour du compte au credit, si aucun  compte n'était pas défini dans les opérations , on doit faire une insertion dans la table ad_cpt_ope_cptes

  $sql = "select * from ad_cpt_ope_cptes where sens = 'c' and type_operation = ".$id." and id_ag = ".$global_id_agence;

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numrows() == 0) {
    // Construction de la requête d'insertion
    $DATA = array();
    $DATA["type_operation"] = $id;
    $DATA['id_ag'] = $global_id_agence;
    $DATA["num_cpte"] = $credit;
    $DATA["sens"] = 'c';
    $DATA["categorie_cpte"] = 0;
    $DATA["id_ag"] = $global_id_agence;
    $sql = buildInsertQuery("ad_cpt_ope_cptes",$DATA);

    // Insertion dans la DB
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  } else {
    if (isset($credit))
      $sql = "update ad_cpt_ope_cptes set num_cpte = '$credit'  where id_ag = ".$global_id_agence." AND type_operation = ".$id." and sens = 'c'";
    else
      $sql = "update ad_cpt_ope_cptes set num_cpte = NULL where id_ag = ".$global_id_agence." AND type_operation = ".$id." and sens = 'c'";
    // Mise à jour de la DB
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

function ajoutOperation($id, $debit, $credit) {
  // Cette fonction ajoute les informations sur une opération
  // IN: $id = L'ID de l'opération à ajouter
  //     $debit : Le numéro di compte qui sera débité
  //     $credit : Le numéro di compte qui sera crédité
  // OUT: Objet ErrorObj

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  // Construction de la requête d'insertion
  $DATA = array();
  $DATA["type_operation"] = $id;
  $DATA["num_cpte_debit"] = $debit;
  $DATA["num_cpte_credit"] = $credit;
  $DATA["id_ag"] = $global_id_agence;
  $sql = buildInsertQuery("ad_cpt_ope",$DATA);

  // Insertion dans la DB
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Vérification du paramétrage de l'opération financière $operation
 *
 * @param integer $operation Numéro de l'opération
 * @return ErrorObj Objet Erreur
 */
function isConfigurerOperation($operation) {
  $result = getDetailsOperation($operation);
  if ($result->errCode == NO_ERR) {
    // On vérifie les comptes comptables de l'opération
    $detail_operation = $result->param;

    if ($detail_operation['debit']['categorie'] == 0 AND $detail_operation['debit']['compte'] == NULL)
      //opération pas configurée !
      return new ErrorObj(ERR_PARAM_OPE, $operation);

    if ($detail_operation['credit']['categorie'] == 0 AND $detail_operation['credit']['compte'] == NULL)
      //opération pas configurée !
      return new ErrorObj(ERR_PARAM_OPE, $operation);

    return new ErrorObj(NO_ERR);
  }
}

/* ************************** Gestion des Taxes ***********************************/
/**
 * Liste des taxes avec leurs informations
 * @author Ibou NDIAYE
 * @since 3.2
 * @return array Liste des taxes qu'on peut appliquer dans adbanking
 */
function getTaxesInfos() {

  global $dbHandler,$global_id_agence, $global_langue_systeme_dft;
  $db = $dbHandler->openConnection();

	$sql = "SELECT id, traduction(libel, '$global_langue_systeme_dft') as libel, type_taxe, taux, cpte_tax_col, cpte_tax_ded FROM adsys_taxes WHERE id_ag=$global_id_agence order by id ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  $list_tax = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $list_tax[$row['id']]= $row;
  return $list_tax;
}

/**
 * Fonction renvoyant les informations des taxes appliquées à une opération d'ADbanking
 * @author Ibou NDIAYE
 * @since 3.2
 * @param int $type_oper le numéro de l'opération
 * @return Objet ErrorObj avec param contenant le tableau les infos des taxes
 * le tableau des infos est de la forme array(type_taxe => array(type_taxe, id_taxe, libel_taxe, taux, cpte_tax_col, cpte_tax_ded)
*/
function getTaxesOperation($type_oper=NULL) {
  global $dbHandler, $global_id_agence, $global_langue_systeme_dft;
  $db = $dbHandler->openConnection();

	   // récupération des taxes appliquées à l'opération
  $sql = "SELECT a.type_taxe, a.id_taxe, a.type_oper, traduction(b.libel,  '$global_langue_systeme_dft') as libel_taxe, b.taux, b.cpte_tax_col, b.cpte_tax_ded from ad_oper_taxe a , adsys_taxes b where a.id_ag = b.id_ag and b.id_ag = $global_id_agence and a.id_taxe = b.id ";
  if ($type_oper != NULL)
  $sql .= "and a.type_oper = $type_oper ";
  $result = $db->query($sql);
  $dbHandler->closeConnection(true);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $taxes = array();
  if ($result->numRows() == 0) {
		return new ErrorObj(NO_ERR, $taxes);
  }
  else{
	  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
			$taxes[$row["type_taxe"]] = array("type_taxe"=>$row["type_taxe"], "id_taxe"=>$row["id_taxe"], "libel_taxe"=>$row["libel_taxe"],
				"taux"=>$row["taux"], "cpte_tax_col"=>$row["cpte_tax_col"], "cpte_tax_ded"=>$row["cpte_tax_ded"]);
	  }
  }
  return new ErrorObj(NO_ERR, $taxes);
}
/**
 * Lie une taxe à une opération
 * @author Ibou NDIAYE
 * @since 3.2
 * @param $id_oper, id de l'opération
 * @param $id_taxe, id de la taxe
 * @return ErrorObj Objet Erreur
 */
function insertTaxeOperation($id_oper, $id_taxe){
	global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

	$sql = "SELECT id, type_taxe FROM adsys_taxes WHERE id = $id_taxe and id_ag=$global_id_agence order by id ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
	$row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $type_taxe = $row["type_taxe"];

  // supprimer la taxe liée de même type
  $sql = "DELETE FROM ad_oper_taxe WHERE type_taxe = $type_taxe and type_oper = $id_oper and id_ag=$global_id_agence  ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }


	$sql = "INSERT INTO ad_oper_taxe(type_oper, type_taxe, id_taxe, id_ag) VALUES($id_oper, $type_taxe, $id_taxe, $global_id_agence)";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);

	return new ErrorObj(NO_ERR);
}
/**
 * Supprime l'association entre une taxe et une opération
 * @author Ibou NDIAYE
 * @since 3.2
 * @param $id_oper, id de l'opération
 * @param $id_taxe, id de la taxe
 * @return ErrorObj Objet Erreur
 */
function deleteTaxeOperation($id_oper, $id_taxe=NULL){
	global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

	$sql = "DELETE FROM ad_oper_taxe WHERE type_oper = $id_oper and id_ag=$global_id_agence ";
	if ($id_taxe != NULL)
  $sql .= " and id_taxe = $id_taxe ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

	return new ErrorObj(NO_ERR);
}

/**
 * Récupère la dernière déclaration de tva dans ad_declare_tva
 * @author Ibou NDIAYE
 * @since 3.2
 * @return ErrorObj Objet Erreur
 */
function getLastDecTva(){
	global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

	$sql = "SELECT * FROM ad_declare_tva  WHERE id_ag = $global_id_agence and id = (SELECT MAX(id) FROM ad_declare_tva WHERE id_ag=$global_id_agence) ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
	$dectva = array();
	if ($result->numRows() == 0) {
		return new ErrorObj(NO_ERR, $dectva);
  }
  else{
	    $dectva = $result->fetchrow(DB_FETCHMODE_ASSOC);
  }
  return new ErrorObj(NO_ERR, $dectva);
}

/**
 * Récupère la liste de déclaration de tva dans ad_declare_tva de l'exercice courant
 * @author Ibou NDIAYE
 * @since 3.2
 * @param int $id_exo, id de l'exercice
 * @return ErrorObj Objet Erreur
 */
function getDecTva($id_exo){
	global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

	$sql = "SELECT * FROM ad_declare_tva  WHERE id_ag = $global_id_agence and id_exo = $id_exo ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
	$dectva = array();
	if ($result->numRows() == 0) {
		return new ErrorObj(NO_ERR, $dectva);
  }
  else{
  	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
	  $dectva [$row["id"]]= $row;
  	}
  }
  return new ErrorObj(NO_ERR, $dectva);
}

/**
 * Paie ou perçois la taxe appliquée à l'opération
 * @author Ibou NDIAYE
 * @since 3.2
 * @param integer $type_operation, id de l'opération
 * @param float $montant, montant de l'opération
 * @param char $sens, sens débit ou crédit
 * @param string $devise, devise montant
 * @param array $cptes_substitue,tableau des comptes à mouvementer
 * @param array $comptable, tableau des mouvements comptables
 * @return ErrorObj Objet Erreur
 */
function reglementTaxe($type_operation, $montant, $sens, $devise, $cptes_substitue, &$comptable){
	global $dbHandler, $global_id_agence, $global_monnaie, $global_id_exo, $global_nom_login;
  $db = $dbHandler->openConnection();
	$taxesOperation = getTaxesOperation($type_operation);
	$details_taxesOperation = $taxesOperation->param;
	if (sizeof($details_taxesOperation) > 0){
	  $subst_tva = array();
		$subst_tva["cpta"] = array();
    $subst_tva["int"] = array();
  	if ($sens == SENS_DEBIT) {
    	$devise_debit_tax = $global_monnaie;
		  $devise_credit_tax = $devise;
		  $mnt_debit = false;
		  $type_oper_tax = 473;//paiement tva récupérable
		  $subst_tva["cpta"]["debit"] = $details_taxesOperation[1]["cpte_tax_ded"];
			if ($subst_tva["cpta"]["debit"] == NULL){
				$dbHandler->closeConnection(false);
	      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte associé à la taxe récupérable: ").$details_taxesOperation[1]["libel_taxe"]);
			}
		  $subst_tva["cpta"]["credit"] = $cptes_substitue["cpta"]["credit"];
		  $subst_tva["int"]["credit"] = $cptes_substitue["int"]["credit"];

		  } else {
		    $devise_debit_tax = $devise;
		    $devise_credit_tax = $global_monnaie;
		    $mnt_debit = true;
		    $type_oper_tax = 474;//perception tva collectée
		    $subst_tva["cpta"]["debit"] = $cptes_substitue["cpta"]["debit"];
		    $subst_tva["int"]["debit"] = $cptes_substitue["int"]["debit"];
		    $subst_tva["cpta"]["credit"] = $details_taxesOperation[1]["cpte_tax_col"];
				if ($subst_tva["cpta"]["credit"] == NULL){
					$dbHandler->closeConnection(false);
	        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte associé à la taxe collectée: ").$details_taxesOperation[1]["libel_taxe"]);
			}
		}

		$mnt_tax = $montant * $details_taxesOperation[1]["taux"];
		$myErr = effectueChangePrivate($devise_debit_tax, $devise_credit_tax, $mnt_tax, $type_oper_tax, $subst_tva, $comptable, $mnt_debit);
	  if ($myErr->errCode != NO_ERR) {
	    $dbHandler->closeConnection(false);
	    return $myErr;
	  }

	}
	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR, $myErr->param);
}
/**
 * Effectue la déclaration de tva
 * @author Ibou NDIAYE
 * @since 3.2
 * @param $date_deb, date début période
 * @param $date_fin, date fin période
 * @return ErrorObj Objet Erreur
 */
function declareTva($date_deb, $date_fin){
	global $dbHandler, $global_id_agence, $global_monnaie, $global_id_exo, $global_nom_login;
  $db = $dbHandler->openConnection();

	//récupère les diffèrentes opérations de paiement et de perception de tva passées dans la base
  $data_tva = getMouvementsTva($date_deb, $date_fin);
  $detail_tva = $data_tva["detail_tva"];
  $mnt_tva = $data_tva["mnt_tva"];

  //mouvementer le compte de tva à décaisser si $mnt_tva positif, ou le compte de tva à reporter si $mnt_tva négatif
  $data_ag = getAgenceDatas($global_id_agence);
  if($mnt_tva >= 0){
  	$cpte_tva = $data_ag['cpte_tva_dec'];
  }else{
  	$cpte_tva = $data_ag['cpte_tva_rep'];
  }
	if ($cpte_tva == NULL) {
		return new ErrorObj(ERR_CPTE_NON_PARAM,sprintf( _("Un des comptes tva de l'agence n'est pas bien paramétré")));
		$dbHandler->closeConnection(false);
	}

	//parcours le tableau obtenu et passe les écritures nécessaires pour la déclaration de tva
	$comptable = array();
	if (sizeof($detail_tva) > 0) {
	foreach ($detail_tva as $key => $value){
		$cptes_substitue = array();
		if($value['type_operation'] == 474){
			$cptes_substitue["cpta"]["debit"] = $value['compte'];
			$cptes_substitue["cpta"]["credit"] = $cpte_tva;
			$mnt_debit = true;
		}else{
			$cptes_substitue["cpta"]["debit"] = $cpte_tva;
			$cptes_substitue["cpta"]["credit"] = $value['compte'];
			$mnt_debit = false;
		}
		if ($value["devise"] != $global_monnaie) {
			$result = effectueChangePrivate($value["devise"], $global_monnaie, $value['montant'], 475, $cptes_substitue, $comptable, $mnt_debit);
		}else
    	$result = passageEcrituresComptablesAuto(475, $value['montant'], $comptable, $cptes_substitue, $global_monnaie);
    if ($result->errCode != NO_ERR) {
    	$dbHandler->closeConnection(false);
      return $result;
    }
	}

  // Fonction 476 : déclaration de tva
  $myErr = ajout_historique(476, NULL, NULL, $global_nom_login, date("r"), $comptable, NULL, NULL);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
   }
  if($mnt_tva >= 0){
  	$sql = "INSERT INTO ad_declare_tva (date_deb, date_fin, mnt_tva_dec, sens, id_exo, id_ag) VALUES(date('$date_deb'), date('$date_fin'), $mnt_tva, 'out', $global_id_exo, $global_id_agence) ";
  }else{
  	$sql = "INSERT INTO ad_declare_tva (date_deb, date_fin, mnt_cred_tva, sens, id_exo, id_ag) VALUES(date('$date_deb'), date('$date_fin'), $mnt_tva, 'in', $global_id_exo, $global_id_agence) ";
  }

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
	}
	$dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $data_tva);
}



/**
 * Récupère tous les mouvements pour la déclaration de tva
 * @author Ibou NDIAYE
 * @since 3.2
 * @param $date_deb, date début période
 * @param $date_fin, date fin période
 * @return ErrorObj Objet Erreur
 */
function getMouvementsTva($date_deb, $date_fin){
	global $dbHandler, $global_id_agence, $global_monnaie, $global_id_exo, $global_nom_login;
  $db = $dbHandler->openConnection();

	//récupère les diffèrentes opérations de paiement et de perception de tva passées dans la base
	$sql = "SELECT h.type_fonction, h.id_his, a.id_ecriture, a.libel_ecriture, a.type_operation, a.date_comptable, a.ref_ecriture, b.compte, b.date_valeur, b.montant, b.devise, b.sens, c.libel_cpte_comptable FROM ad_his h, ad_ecriture a, ad_mouvement b, ad_cpt_comptable c ";
	$sql .= "  WHERE h.id_ag = a.id_ag and a.id_ag = b.id_ag and b.id_ag = c.id_ag and c.id_ag = $global_id_agence  and h.id_his = a.id_his and a.id_ecriture = b.id_ecriture and b.compte = c.num_cpte_comptable";
	$sql .= " and ((a.type_operation = 474 and b.sens = 'c') or (a.type_operation = 473 and b.sens = 'd')) and b.devise = '$global_monnaie' ";
	$sql .= " and date(a.date_comptable) >= date('$date_deb') and date(a.date_comptable) <= date('$date_fin') ";
	$sql .= " order by a.type_operation ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $ligne_tva = array();
  $mnt_tva = 0; // le montant tva à décaisser ou à reporter

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
  	$ligne_tva[$row['id_ecriture']] = $row;
  	if($row['devise'] != $global_monnaie){
			$mnt_devise_ref = calculeCV($row['devise'], $global_monnaie, $row['montant']);
		}else{
			$mnt_devise_ref = $row['montant'];
		}
		$ligne_tva[$row['id_ecriture']]['mnt_devise_ref'] =  $mnt_devise_ref;
		if($row['type_operation'] == 474){
			$mnt_tva = $mnt_tva + $mnt_devise_ref;
		}else{
			$mnt_tva = $mnt_tva - $mnt_devise_ref;
		}
  }
	$data_tva = array();
	$data_tva["detail_tva"] = $ligne_tva;
	$data_tva["mnt_tva"] = $mnt_tva;
  $dbHandler->closeConnection(true);
  return $data_tva;

}


/* ************************** Gestion des Exercices ****************************************/
function getExercicesComptables($id_exo=NULL) {
  /*

   Fonction renvoyant l'ensemble des exercices comptables
   IN : <néant>
   OUT: array ( index => array(infos exercice))

  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_exercices_compta where id_ag=$global_id_agence ";
  if ($id_exo)
    $sql .= " AND id_exo_compta=$id_exo ";
  $sql .= "ORDER BY id_exo_compta";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $exos = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($exos, $row);

  $dbHandler->closeConnection(true);
  return $exos;
}

function getInfosJournal($id_jou = NULL) {
  /*
    Renvoie les infos des journaux
  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql="SELECT *  FROM ad_journaux where id_ag=$global_id_agence ";

  if ($id_jou != NULL)
    $sql .= "AND id_jou=$id_jou";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $jnl=array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $jnl[$row["id_jou"]]=$row;
  }

  $dbHandler->closeConnection(true);

  return $jnl;
}

function getInfosEcritures() {
  /*
    Renvoie toutes les éccrgitures du brouillard
  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql="select *  from ad_brouillard where id_ag=$global_id_agence";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $ecr=array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($ecr,$row);
  }

  $dbHandler->closeConnection(true);
  return $ecr;
}

function getLibelEcritures($login='', $date='') {
  /*
    Renvoie tous les libellés des operations dans le brouilllard
  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  if ('' !== $login || ('' !== $date && null !== $date)) {
      $sql = "SELECT DISTINCT b.libel_ecriture,b.type_operation,b.id_his,b.id,b.id_jou,b.id_exo,b.date_comptable FROM ad_log l INNER JOIN ad_his h ON l.login=h.login INNER JOIN ad_brouillard b ON h.id_his=b.id_his WHERE b.id_ag=$global_id_agence";
      
      if ('' !== $login) {
          $sql .= " AND l.login='$login' ";
      }

      if ('' !== $date && null !== $date) {          
          $sql .= " AND b.date_comptable='$date' ";
      }
  }
  else {
    $sql="select DISTINCT libel_ecriture,type_operation,id_his,id,id_jou,id_exo,date_comptable  from ad_brouillard where id_ag=$global_id_agence";
  }
  
  $sql .= " ORDER BY date_comptable ASC";
  
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $libels=array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($libels,$row);
  }

  $dbHandler->closeConnection(true);
  return $libels;
}

function getEcritureLibreUtilisateurs() {
  /*
    Renvoie tous les utilisateurs des operations dans le brouilllard
  */
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT DISTINCT h.login, (u.nom || ' ' || u.prenom) AS fullname FROM ad_uti u INNER JOIN ad_log l ON u.id_utilis=l.id_utilisateur INNER JOIN ad_his h ON l.login=h.login INNER JOIN ad_brouillard b ON h.id_his=b.id_his WHERE b.id_ag=$global_id_agence ORDER BY fullname ASC";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $users = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($users, $row);
  }

  $dbHandler->closeConnection(true);

  return $users;
}

function getEcritureLibreDates() {
  /*
    Renvoie tous les utilisateurs des operations dans le brouilllard
  */
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT DISTINCT date_comptable FROM ad_brouillard WHERE id_ag=$global_id_agence ORDER BY date_comptable DESC";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dates = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($dates, $row);
  }

  $dbHandler->closeConnection(true);

  return $dates;
}

/**
 * Renvoie le nom et prénom d'un utilisateur
 */
function getUtilisateurFullName($login) {

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT (u.nom || ' ' || u.prenom) AS fullname FROM ad_uti u INNER JOIN ad_log l ON u.id_utilis=l.id_utilisateur WHERE l.login='$login' AND u.id_ag=$global_id_agence LIMIT 1";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0)
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
  $tmprow = $result->fetchrow();
  if ($result->numRows() > 1) return '';
  return $tmprow[0];

}

function getUtilisateurFullNameByIdHis($id_his) {

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT (u.nom || ' ' || u.prenom) AS fullname FROM ad_uti u INNER JOIN ad_log l ON u.id_utilis=l.id_utilisateur INNER JOIN ad_his h ON l.login=h.login WHERE h.id_his=$id_his AND h.id_ag=$global_id_agence ORDER BY fullname ASC LIMIT 1";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0)
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
  $tmprow = $result->fetchrow();
  if ($result->numRows() > 1) return '';
  return $tmprow[0];

}

function ajoutJournal($DATA) {
	// ajoute un journal dans la base
	global $dbHandler,$global_id_agence, $global_nom_login, $global_id_client;
	$db = $dbHandler->openConnection();

	// vérifie qu'il n'existe pas de journal portant le même libellé
	if (is_champ_traduit('ad_journaux','libel_jou')) {
		$libel_jou_trad = new Trad();
		$libel_jou_trad = $DATA["libel_jou"];
		$libel_jou_trad->save();
		$libel_jou = $libel_jou_trad->get_id_str();
	}else{
		$libel_jou = htmlspecialchars($DATA["libel_jou"], ENT_QUOTES, "UTF-8");
	}
	$DATA["libel_jou"] = $libel_jou;
	$sql = "SELECT * FROM  ad_journaux WHERE id_ag=$global_id_agence and libel_jou='$libel_jou'";
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}

	if ($result->numrows()!=0) {
		$dbHandler->closeConnection(false);
		return new ErrorOBj(ERR_JOU_EXISTE);
	}

	// vérifie si le compte principal ou un de ses sous-comptes n'est pas déjà compte principal d'un autre journal
	if ($DATA["num_cpte_princ"]) {
		$princ=$DATA["num_cpte_princ"];
		$sql="SELECT * FROM  ad_cpt_comptable WHERE id_ag=$global_id_agence and (num_cpte_comptable='$princ' OR num_cpte_comptable like '$princ.%') AND cpte_princ_jou='t'";
		$result = $db->query($sql);
		if (DB::isError($result)) {
			$dbHandler->closeConnection(false);
			signalErreur(__FILE__,__LINE__,__FUNCTION__);
		}

		if ($result->numrows()!=0) {
			$dbHandler->closeConnection(false);
			return new ErrorOBj(ERR_DEJA_PRINC_JOURNAL);
		}

	}

	// vérifie si le compte ou un de ses sous-comptes n'est pas déjà un compte de contrepartie d'un journal
	if ($DATA["num_cpte_princ"]) {
		$princ=$DATA["num_cpte_princ"];
		$sql="SELECT * FROM  ad_journaux_cptie WHERE id_ag=$global_id_agence and num_cpte_comptable='$princ' OR num_cpte_comptable like '$princ.%'";
		$result = $db->query($sql);
		if (DB::isError($result)) {
			$dbHandler->closeConnection(false);
			signalErreur(__FILE__,__LINE__,__FUNCTION__);
		}
		if ($result->numrows()!=0) {
			$dbHandler->closeConnection(false);
			return new ErrorOBj(ERR_DEJA_CONTREPARTIE);
		}
	}
	$DATA["id_ag"] = $global_id_agence;
	$sql = buildInsertQuery("ad_journaux", $DATA);
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}

	// les sous-comptes du compte principal deviennent aussi des comptes principaux de ce journal
	if ($DATA["num_cpte_princ"]) {
		$princ=$DATA["num_cpte_princ"];
		$sql="update ad_cpt_comptable set cpte_princ_jou='t' where id_ag=$global_id_agence AND num_cpte_comptable='$princ' OR num_cpte_comptable like '$princ.%'";
		$result = $db->query($sql);
		if (DB::isError($result)) {
			$dbHandler->closeConnection(false);
			signalErreur(__FILE__,__LINE__,__FUNCTION__);
		}
	}

	// Enregistrement - Ajout d'un journal
	ajout_historique(454, $global_id_client, 'Creation d\'un journal comptable', $global_nom_login, date("r"), NULL);
	
	
	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR);

}

function modifJournal($DATA) {
	// Cette fonction modifie un journal dans la base
	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
	if (is_champ_traduit('ad_journaux','libel_jou')) {
		$libel_jou_trad = new Trad();
		$libel_jou_trad = $DATA["libel_jou"];
		$libel_jou_trad->save();
		$libel_jou = $libel_jou_trad->get_id_str();
	}else{
		$libel_jou = htmlspecialchars($DATA["libel_jou"], ENT_QUOTES, "UTF-8");
	}
	$DATA["libel_jou"] = $libel_jou;
	$sql = "UPDATE ad_journaux SET ";
	$sql .= "libel_jou ='".$DATA["libel_jou"]."'";
	if ($DATA["num_cpte_princ"]=='')
	$sql .= ",num_cpte_princ =NULL";
	else
	$sql .= ",num_cpte_princ ='".$DATA["num_cpte_princ"]."'";
	$sql .= ",etat_jou =".$DATA["etat_jou"];
	$sql .= " WHERE id_ag=$global_id_agence AND id_jou=".$DATA["id_jou"];

	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}

	$dbHandler->closeConnection(true);
	return true;
}

function supJournal($id_journal) {
  // Cette fonction supprime un journal

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  // Infos du journal à suppprimer
  $jou=getInfosJournal($id_journal);

  // on ne supprime ni le journal principal  ni le journal des od
  if ( ($jou[$id_journal]["id_jou"]==1) || ($jou[$id_journal]["id_jou"]==2) ) {
    $dbHandler->closeConnection(false);
    return new ErrorOBj(ERR_JOU_NON_SUPPRIMABLE,". "._("Il ne peut être supprimé"));
  }

  // s'il existe des écritures validées dans ce journal
  $sql="select * from ad_ecriture where id_ag=$global_id_agence and id_jou=$id_journal";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numrows()!=0) {
    $dbHandler->closeConnection(false);
    return new ErrorOBj(ERR_JOU_NON_SUPPRIMABLE,". "._("Le journal contient des écritures validées."));
  }

  // s'il existe des écritures dans le brouillard pour ce journal
  $sql="select * from ad_brouillard where id_ag=$global_id_agence and id_jou=$id_journal";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numrows()!=0) {
    $dbHandler->closeConnection(false);
    return new ErrorOBj(ERR_JOU_NON_SUPPRIMABLE,". "._("Des écritures dans le brouillard lui sont associées."));
  }

  // Suppression de la contrepartie du journal FIXME trigger
  $sql="DELETE FROM ad_journaux_cptie where id_ag=$global_id_agence and id_jou=$id_journal";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  // Suppression des comptes de liaison du journal FIXME trigger
  $sql="DELETE FROM ad_journaux_liaison where id_ag=$global_id_agence and id_jou1=$id_journal OR id_jou2=$id_journal";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }


  // Mise à jour des comptes princiaux
  $princ=$jou[$id_journal]["num_cpte_princ"];
  if ($princ) {
    $sql="update ad_cpt_comptable set cpte_princ_jou='f' where id_ag=$global_id_agence AND num_cpte_comptable='$princ' OR num_cpte_comptable like'$princ.%'";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  }

  // Suppression du journal
  $sql="DELETE FROM ad_journaux  where id_ag=$global_id_agence and id_jou=$id_journal";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

function getJournauxLiaison($fields_values=NULL) {
  /**
   *Fonction renvoyant l'ensemble des comptes de liaison et leurs journaux associés
   * @author Papa NDIAYE
   * @since 1.0.8
   * @param array $fields_values, on construit la clause WHERE ainsi : ... WHERE field = value ...
   * @return array ( index => infos)
   */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  //vérifier qu'on reçoit bien un array
  if (($fields_values != NULL) && (! is_array($fields_values)))
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Mauvais argument dans l'appel de la fonction"

  // construction de la requête
  $sql = "SELECT * FROM ad_journaux_liaison where id_ag=$global_id_agence";
  if (isset($fields_values)) {
  	$sql .= " AND ";
    foreach ($fields_values as $key => $value)
    if ( $key == 'id_jou1' || $key == 'id_jou2')
      $sql .= "(id_jou1=$value OR id_jou2=$value ) AND "; // Soit il est à la première position soit il est la 2ème
    else
      $sql .= "$key = '$value' AND ";
    $sql = substr($sql, 0, -4);
  }
  $sql .= " ORDER BY id_jou1 ASC";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  // Liste des comptes de liaison
  $info = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($info,$row);

  $dbHandler->closeConnection(true);
  return $info;

}

function ajoutJournauxLiaison($DATA) {
  /**
   * Fonction ajoute un compte de liaison entre deux journaux
   * @author Papa NDIAYE
   * @since 1.0.8
   * @param array $DATA Array contenant les infos à inserer (les deux journaux et le compte comptable)
   * @return ObjetError renvoie un ojet erreur
   */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // Vérifie s'il n'existe pas de compte de liaison entre les deux journaux
  $sql = "SELECT * FROM ad_journaux_liaison ";
  $sql .= "WHERE id_ag=$global_id_agence and (id_jou1=".$DATA["id_jou1"]." AND id_jou2=".$DATA["id_jou2"]." ) ";
  $sql .= "OR (id_jou1=".$DATA["id_jou2"]." AND id_jou2=".$DATA["id_jou1"].")";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numrows() != 0) {
    $dbHandler->closeConnection(false);
    return new ErrorOBj(ERR_CPT_EXIST);
  }

  // ajout du journal
  $sql = "INSERT INTO ad_journaux_liaison ";
  $sql .= "(id_jou1,id_ag, id_jou2, num_cpte_comptable) ";
  $sql .= "VALUES(".$DATA["id_jou1"].",$global_id_agence,".$DATA["id_jou2"].",'".$DATA["num_cpte_comptable"]."')";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

function modifJournauxLiaison($DATA) {
  /**
   * Fonction modifie le compte de liaison entre deux journaux
   * @author Papa NDIAYE
   * @since 1.0.8
   * @param array $DATA Array contenant les id des deux journaux et le compte de liaison
   * @return Boolean renvoie true si la modification s'est bien passée sinon false
   */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // modif du compte
  $sql = "UPDATE ad_journaux_liaison ";
  $sql .= "SET num_cpte_comptable='".$DATA["num_cpte_comptable"]."' ";
  $sql .= "WHERE (id_ag=$global_id_agence) AND (id_jou1=".$DATA["id_jou1"]." OR id_jou2=".$DATA["id_jou1"]." ) ";
  $sql .= "AND (id_jou1=".$DATA["id_jou2"]." OR id_jou2=".$DATA["id_jou2"].")";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return true;

}

function supJournauxLiaison($DATA) {
  /**
   * Fonction supprime le compte de liaison entre deux journaux
   * @author Papa NDIAYE
   * @since 1.0.8
   * @param array $DATA Array contenant les id des deux journaux et le compte de liaison
   * @return Boolean renvoie true si la suppression s'est bien passée sinon false
   */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // suppression compte de liaison
  $sql = "DELETE FROM ad_journaux_liaison ";
  $sql .= "WHERE id_ag=$global_id_agence and (id_jou1=".$DATA["id_jou1"]." AND id_jou2=".$DATA["id_jou2"]." ) ";
  $sql .= "OR (id_jou1=".$DATA["id_jou2"]." AND id_jou2=".$DATA["id_jou1"].")";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return true;

}

function isCpteCtrePtie($id_jou,$id_compte) {
  // verifie si compte est  de la contre partie du  journal

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql="select * from ad_journaux_cptie where id_ag=$global_id_agence and id_jou=$id_jou ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  while ($row=$result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $lg_compte=strlen($row["num_cpte_comptable"]);
    $racine=substr($id_compte,0,$lg_compte);
    if ($racine==$row["num_cpte_comptable"])
      return true;
  }

  $dbHandler->closeConnection(true);
  return false;
}

function supJournalCptie($id_jou,$id_compte) {
  // supprime des comptes de contrepartie d'un journal

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // le compte et ses sous-comptes qui sont de la contrepartie
  $cptie=getInfosJournalCptie($id_jou,$id_compte);
  if (isset($cptie))
    foreach($cptie as $row) {
    $id=$row["id_jou"];
    $num=$row["num_cpte_comptable"];

    $sql="delete from ad_journaux_cptie where id_ag=$global_id_agence and id_jou=$id and num_cpte_comptable='$num'";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  }

  $dbHandler->closeConnection(true);
  return true;
}

function getInfosJournalCptie($id_jou=NULL,$num_cpte=NULL) {
  // renvoie les donnes de la table ad_journaux_cptie
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql="SELECT *  FROM ad_journaux_cptie where id_ag=$global_id_agence ";
  if ($id_jou != NULL) {
    $sql .= "AND id_jou=$id_jou";
    if ($num_cpte != NULL)
      $sql .= " and (num_cpte_comptable='$num_cpte' OR num_cpte_comptable like '$num_cpte.%')";
  } else
    if ($num_cpte != NULL)
      $sql .= "AND num_cpte_comptable='$num_cpte' OR num_cpte_comptable like '$num_cpte.%'";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $cptie = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($cptie,$row);

  $dbHandler->closeConnection(true);
  return $cptie;
}


function ajoutJournalCptie($id_jou,$compte) {
  // Ajout le compte $compte et ses sous-comptes dans la contrepartie du journal dont l'id est donné

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // si le compte ou les sous-comptes sont de la contrepartie, les supprimer d'abord
  $sup=supJournalCptie($id_jou,$compte);

  // Récupération de tous les comptes dérivés de ce compte
  $sous_comptes=getSousComptes($compte, true);

  // Ajout du compte dans la contrepartie du journal
  $sql="INSERT INTO ad_journaux_cptie Values($id_jou,'$compte',$global_id_agence)";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  // Ajout des sous-comptes dans la contrepartie du journal
  if (isset($sous_comptes))
    foreach($sous_comptes as $key=>$value) {
    // récupère informations du sous-compte
    $param["num_cpte_comptable"]=$key;
    $cpte=getComptesComptables($param);

    // vérifie si le sous-compte n'est pas compte principal d'un journal
    if ($cpte[$key]["cpte_princ_jou"]=='t') {
      $dbHandler->closeConnection(false);
      return new ErrorOBj(ERR_DEJA_PRINC_JOURNAL,$key);
    }

    // ajout du sous-compte dans la contrepartie
    $sql="INSERT INTO ad_journaux_cptie Values($id_jou,'$key',$global_id_agence)";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

/**
 * Renvoie tous les comptes de contrepartie du journal comptable possédant une devise
 * @author Papa Ndiaye
 * @since 1.0.8
 * @param int $id_jou identifiant de journal
 * @return array Liste des comptes de contrepartie
 */
function getComptesContrepartie($id_jou) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql  = "SELECT cpte.num_cpte_comptable, cpte.libel_cpte_comptable FROM ad_cpt_comptable cpte ,ad_journaux_cptie jou ";
  $sql .= "WHERE jou.id_ag=$global_id_agence and cpte.id_ag=$global_id_agence and jou.id_jou = $id_jou AND jou.num_cpte_comptable = cpte.num_cpte_comptable AND cpte.devise IS NOT NULL";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $liste_comptes=array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $liste_comptes[$row["num_cpte_comptable"]]=$row;

  $dbHandler->closeConnection(true);
  return $liste_comptes;

}

function getComptesPrinc($id_jou) {
  /**
   * Fonction qui renvoie le compte principal d'un journal auxiliaire et ses sous-comptes
   * @author Papa Ndiaye
   * @since 1.0.8
   * @param int $id_jou identifiant de journal
   * @return array Liste comptes comptables
   */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // Récupération du compte principal du journal
  $jou=getInfosJournal($id_jou);
  $compte_princ=$jou[$id_jou]["num_cpte_princ"];

  // Récupération des sous-comptes du compte principal du journal
  if ($compte_princ!='')
    $sous_comptes=getSousComptes($compte_princ, true);

  $sql  = "SELECT cpte.num_cpte_comptable, cpte.libel_cpte_comptable FROM ad_cpt_comptable cpte ,ad_journaux_cptie jou ";
  $sql .= "WHERE cpte.id_ag=$global_id_agence and jou.id_ag=$global_id_agence and jou.id_jou = $id_jou AND jou.num_cpte_comptable = cpte.num_cpte_comptable";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $liste_comptes=array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $liste_comptes[$row["num_cpte_comptable"]]=$row;

  $dbHandler->closeConnection(true);
  return $liste_comptes;

}


function getJournalCpte($num_cpte) {
  //renvoie les informations sur le Journal associé au compte comptable

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $infos = array();

  // Regarder si ce compte a un journal associé
  $sql="SELECT *  FROM ad_cpt_comptable where id_ag=$global_id_agence and num_cpte_comptable = '$num_cpte' and cpte_princ_jou = 't'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numrows()==0) { // Si pas de journal associé. Rem :pourquoi ne pas faire appel à getComptesComptes et vérifier que c'un compte principal
    //$dbHandler->closeConnection(true);
    $non_jou = true;
    //return NULL;
  }

  $sql="SELECT *  FROM ad_journaux  where id_ag=$global_id_agence and num_cpte_princ = '$num_cpte' ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    // $dbHandler->closeConnection(true);
    $non_jou = true; // $non_jou nous indique que c'est le journal 1 qui sera utilisé par défaut
    // return NULL;
  }

  if ($non_jou == false) { // Sinon pas la peine, on sait déjà qu'il n'y a pas de journal associé
    // Si on a de la chance, ce compte est directement associé à un journal
    $sql="SELECT *  FROM ad_journaux  where id_ag=$global_id_agence and num_cpte_princ = '$num_cpte' ";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    if ($result->numrows()==1) {
      $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
      $infos = $row;
      //$dbHandler->closeConnection(true);
      //return($row);
    } else {
      // On regarde si le compte centralisateur n'est pas compte principal d'un journal
      // FIXME : INUTILE : On peut déjà faire l'appel récursif !
      /*
      $sql="SELECT *  FROM ad_journaux  where num_cpte_princ = (SELECT cpte_centralise  FROM ad_cpt_comptable where num_cpte_comptable = '$num_cpte') ";
      $result = $db->query($sql);
      if (DB::isError($result))
        {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

      if($result->numrows()==1)
        {
          $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
          $infos = $row;
          //  $dbHandler->closeConnection(true);
          // return($row);
        }
      else
      {*/
      $sql ="SELECT cpte_centralise  FROM ad_cpt_comptable where id_ag=$global_id_agence and num_cpte_comptable = '$num_cpte'";
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }

      if ($result->numrows()==1) {
        $row = $result->fetchrow();
        $info_jou = getJournalCpte($row[0]); // Appel récursif avec le compte centralisateur
        $dbHandler->closeConnection(true);
        return $info_jou;
      } else {
        // On est arrivés à la racine du plan comptable, il y a donc une inconsistance dans la base de données
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Inconsistance dans la DB : le compte $num_cpte est censé tre compte principal et pourant ..."
      }

    }
  }
  $dbHandler->closeConnection(true);
  if ($non_jou == true) {
    $jou_princ = getInfosJournal(1);
    $infos = $jou_princ[1];
    return($infos);
  } else
    return($infos);

}

function passageEcrituresBrouillard($DATA) {
  global $dbHandler, $global_nom_login, $global_id_agence;

  $db = $dbHandler->openConnection();
  //historiser avant d'inserer dans ad_brouillard
 	$MyError = ajout_historique(470, NULL,_("Passage écritures Brouillard"),$global_nom_login,date("r"),NULL,NULL);
 	if ($MyError->errCode != NO_ERR)
 	   return $MyError;
 	$idhis = $MyError->param;

  if ($MyError->errCode != NO_ERR)
    return $MyError;

  $idhis = $MyError->param;

  //  passage des ecritures
  if (isset($DATA))
  foreach ($DATA as $key => $value) {
  	$sql = "INSERT INTO ad_brouillard ";
  	$sql .= "(id_his,id_ag, id, devise, compte, cpte_interne_cli, sens, montant, date_comptable, libel_ecriture, type_operation, ";
  	$sql .= "id_jou, id_exo, id_taxe, sens_taxe) ";
  	//$sql .= "VALUES('".$value['id_his']."', '".$value['id']."','".$value['compte']."', ";
  	$sql .= "VALUES('".$idhis."',$global_id_agence, '".$value['id']."','".$value['devise']."','".$value['compte']."', ";

  	if ($value['cpte_interne_cli'] == '') $sql .= "NULL,";
  	else $sql .= "'".$value['cpte_interne_cli']."',";

  	if ($value['sens']=='' or $value['sens']==NULL) $sql .= "NULL,";
  	else $sql .= "'".$value['sens']."', ";

  	if ($value['montant']=='' or $value['montant']==NULL) $sql .= "0,";
  	else $sql .= $value['montant'].", ";

  	if ($value['date_comptable']=='') 	$sql .= "NULL,";
  	else $sql .="'".$value['date_comptable']."',";
  	if (is_champ_traduit('ad_brouillard','libel_ecriture')) {

            if(is_trad(unserialize($value["libel_ecriture"]))){

                $libel_ecriture_trad = unserialize($value["libel_ecriture"]);
  		$libel_ecriture = $libel_ecriture_trad->save();
            }else{
                $libel_ecriture_trad = new Trad();
                $libel_ecriture_trad->set_traduction(get_langue_systeme_par_defaut(), ($value["libel_ecriture"]));

                $libel_ecriture = $libel_ecriture_trad->save();
            }
  	}else{
            $libel_ecriture = htmlspecialchars($value["libel_ecriture"], ENT_QUOTES, "UTF-8");
  	}
  	$sql .= "'".$libel_ecriture."',";
        if ($value['type_operation'] == '') $sql .= "NULL,";
  	else $sql .= "'".$value['type_operation']."',";

  	if ($value['id_jou'] == '') $sql .= "NULL,";
  	else $sql .= "'".$value['id_jou']."',";

  	if ($value['id_exo'] == '') $sql .= "NULL,";
  	else $sql .= $value["id_exo"].",";

  	if ($value['id_taxe'] == '') $sql .= "NULL,";
  	else $sql .= $value['id_taxe'].",";

  	if ($value['sens_taxe'] == '') $sql .= "NULL)";
  	else $sql .= "'".$value["sens_taxe"]."')";

        // $sql .= ",'".$value["validation"]."')";

  	$result = $db->query($sql);
  	if (DB::isError($result)) {
  		$dbHandler->closeConnection(false);
  		signalErreur(__FILE__,__LINE__,__FUNCTION__);
  	}
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

function modifEcrituresBrouillard($DATA) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  //  modification des ecritures
  $idop = $idhis = -1;

  foreach ($DATA as $key => $value) {
  	if ($value['id_mouvement']!='') { // cette ligne existe dans le brouillard
  		// le compte est renseigné, donc on veut faire une modification de la ligne existante
  		if ($value['compte']!='' && $value['compte']!= 0) {
  			$sql = "UPDATE ad_brouillard ";
  			$sql .= "set compte='".$value['compte']."'";
  			$sql .= ",sens='".$value['sens']."'";
  			$sql .= ",montant=".$value['montant'];
  			$sql .= ",devise='".$value['devise']."'";
  			$sql .= ",sens_taxe='".$value['sens_taxe']."'";
  			if($value['id_taxe']=='')
  			$sql .= ",id_taxe=NULL";
  			else
  			$sql .= ",id_taxe='".$value['id_taxe']."'";
  			if ($value['date_comptable']=='')
  			$sql .= ",date_comptable=NULL";
  			else
  			$sql .= ",date_comptable='".$value['date_comptable']."'";
  			if (is_champ_traduit('ad_brouillard','libel_ecriture')) {
  				$libel_ecriture_trad = new Trad();
  				$libel_ecriture_trad = $value["libel_ecriture"];
  				$libel_ecriture_trad->save();
  				$libel_ecriture = $libel_ecriture_trad->get_id_str();
  			}else{
  				$libel_ecriture = htmlspecialchars($value["libel_ecriture"], ENT_QUOTES, "UTF-8");
  			}
  			$sql .= ",libel_ecriture='".$libel_ecriture."'";
  			$sql .= ",type_operation='".$value['type_operation']."'";
  			if ($value['cpte_interne_cli'] == '')
  			$sql .= ",cpte_interne_cli=NULL";
  			else
  			$sql .= ",cpte_interne_cli=".$value['cpte_interne_cli'];
  			$sql .= " where id_ag=$global_id_agence AND id_mouvement =".$value['id_mouvement'];

  			if ($value['id']!=$idop) {
  				$idop=$value['id'];
  				$idhis=$value['id_his'];
  			}
  		} else  // le compte n'est renseigné, donc on veut supprimer la ligne dans le brouillard
  		$sql = "delete from ad_brouillard  where id_mouvement =".$value['id_mouvement'];
  	} else { // le id_mouvement n'existe pas, donc on veut ajouter une ligne
  		$sql = "INSERT INTO ad_brouillard ";
  		$sql .= "(id_his,id_ag, id, compte, devise, cpte_interne_cli, sens, montant, date_comptable, libel_ecriture, type_operation, ";
  		$sql .= "id_jou, id_exo, id_taxe, sens_taxe) ";
  		$sql .= "VALUES('".$idhis."',$global_id_agence, '".$idop."','".$value['compte']."', '".$value['devise']."', ";
  		//$sql .= "VALUES('".$idhis."', '".$value['id']."','".$value['compte']."', ";
  		if ($value['cpte_interne_cli'] == '')
  		$sql .= "NULL,";
  		else
  		$sql .= "'".$value['cpte_interne_cli']."',";
  		$sql .= "'".$value['sens']."', ".$value['montant'].",";
  		if ($value['date_comptable']=='')
  		$sql .= "NULL,";
  		else
  		$sql .= "'".$value['date_comptable']."',";
  		if (is_champ_traduit('ad_brouillard','libel_ecriture')) {
  				$libel_ecriture_trad = new Trad();
  				$libel_ecriture_trad = $value["libel_ecriture"];
  				$libel_ecriture_trad->save();
  				$libel_ecriture = $libel_ecriture_trad->get_id_str();
  		}else{
  			$libel_ecriture = htmlspecialchars($value["libel_ecriture"], ENT_QUOTES, "UTF-8");
  		}
  		$sql .= "'".$libel_ecriture."',";
  		$sql .= "'".$value["type_operation"]."',";

  		if ($value['id_jou'] == '')
  		$sql .= "NULL,";
  		else
  		$sql .= $value['id_jou'].",";
  		if ($value['id_exo'] == '')
  		$sql .= "NULL,";
  		else
  		$sql .= $value["id_exo"].",";
  		if ($value['id_taxe'] == '')
  		$sql .= "NULL,";
  		else
  		$sql .= $value['id_taxe'].",";
  		if ($value['sens_taxe'] == '')
  		$sql .= "NULL)";
  		else
  		$sql .= "'".$value["sens_taxe"]."')";

  	}

  	$result = $db->query($sql);
  	if (DB::isError($result)) {
  		$dbHandler->closeConnection(false);
  		signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
  	}
  }

  $dbHandler->closeConnection(true);
  return true;

}

function supEcritureBrouillard($id_his) {
  /*
    Cette Fonnction supprime une écriture dans le brouillard (peut être n débit et n crédit)
      IN: le id_his de l'écriture
  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "DELETE FROM  ad_brouillard WHERE id_ag=$global_id_agence and id_his =".$id_his;

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
  }

  $dbHandler->closeConnection(true);
  return true;
}

function supMouvementBrouillard($id_mouvement) {
  /*
    Cette Fonnction supprime une ligne (movement dans le brouillard)
      IN: le id du mouvement à supprimer
  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "DELETE FROM  ad_brouillard where id_ag=$global_id_agence and id_mouvement =".$id_mouvement;

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
  }

  $dbHandler->closeConnection(true);
  return true;
}

/**
 * Fonction passant des écritures comptables: écritures libres ou d'annulation de mouvements réciproques
 * cas d'écritures libres : si les écritures étaient dans le brouillard alors les supprimer
 * cas d'écriture d'annulation : marquer les mouvements à contrepasser comme consolidés
 * @author Papa Ndiaye
 * @since 2.0
 * @param array $DATA Array contenat les écritures à passer
 * @param Array $PIECE Tableau contenant les infos sur la pièce justificative
 * @return ObjError On renvoie un objet erreur
 */
function validationEcrituresComptables($DATA, $PIECE, $num_fonction,$login_initiateur=null) {
  global $dbHandler, $global_nom_login, $global_id_guichet, $global_id_agence, $global_monnaie, $global_monnaie_courante_prec, $global_langue_utilisateur;
  $db = $dbHandler->openConnection();

  $i=0;
  $id_his=0; // utiliser pour savoir si les écritures à valider viennent du brouillad. Car il faut les supprimer dans le cas échéant

  $totaldeb = 0;
  $totalcred = 0;

  foreach ($DATA as $key => $value) {
    // Vérification de la validité des écritures

    $cc = $value['compte'];
    $dateope = php2pg($value["date_comptable"]);
    $dateope=getPhpDateTimestamp($dateope);

    // Vérifie que le compte peut être mouvementé dans la devise
    $compte_dev = checkCptDeviseOK($value["compte"], $value["devise"]);
    if ($compte_dev == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_ECR_NON_VALIDE,".".sprintf(_("Vous essayez de mouvementer le compte %s dans la devise %s"),$value["compte"],$value["devise"]));
    }
    $value['compte'] = $compte_dev;

    // si un compte comptable est associé à un produit d'épargne il faut donner un compte interne de client
    if (isCompteEpargne($cc) && ($value['cpte_interne_cli'] == '') ) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_ECR_NON_VALIDE,".".sprintf(_("Vous essayez de mouvementer le compte d'épargne %s sans lui assocer un compte client"),$cc));
    }
  // si un compte comptable est associé à un produit de crédit il faut donner un compte interne de client
    if (isCompteCredit($cc) && ($value['cpte_interne_cli'] == '') ) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_ECR_NON_VALIDE,".".sprintf(_("Vous essayez de mouvementer le compte %s associé à un crédit sans lui assocer un compte client"),$cc));
    }
  // si un compte comptable est associé à une garantie il faut donner un compte interne de client
    if (isCompteGarantie($cc) && ($value['cpte_interne_cli'] == '') ) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_ECR_NON_VALIDE,".".sprintf(_("Vous essayez de mouvementer le compte de garantie %s sans lui assocer un compte client"),$cc));
    }
    //D'aprés DIOUF, il doit être possible de mouvementer le compte à une date antérieure : voir #1721.il faut avoir le droit de le faire 
    //on ne peut mouvementer les comptes liés à un produit d'épargne, à un produit de crédit ou à un guichet qu'à la date d'aujourd'hui
   if ( (isCompteEpargne($cc))|| (isCompteCredit($cc))|| (isCompteGuichet($cc))|| (isCompteGarantie($cc))){
    	if ( ( date("y/m/d",$dateope) != date("y/m/d") ) && (!check_access(479)) ) { 
         $dbHandler->closeConnection(false);
         return new ErrorObj(ERR_ECR_NON_VALIDE,".".sprintf(_("Le compte %s associé à un compte interne ne peut être mouvementé qu'au jourd'hui"),$cc));
      }
    }

    // Seul le guichetier peut mouvementer le compte comptable qui est associé à son guichet
    if (isCompteGuichet($cc)) {
      if (isset($global_id_guichet)) {
        // Récupération du compte comptable associé au guichet connecté
        $critere = array();
        $critere['num_cpte_comptable'] = getCompteCptaGui($global_id_guichet);
        $cpte_cpta_gui = $critere['num_cpte_comptable'];
        // Arrondi du montant opération au guichet
        $cpte_gui = getComptesComptables($critere);
        $value['montant'] = arrondiMonnaie( $value['montant'], 0, $cpte_gui['devise'] );
      } else {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_ECR_NON_VALIDE,".".sprintf(_("Le compte %s ne peut être mouvementé que par le guichetier qui lui est associé"),$cc));
      }

      // Vérifie si le compte à mouvementer est bien le compte associé au guichet connecté ou un de ses sous-comptes
      if ( $cc != $cpte_cpta_gui ) {
        $cptes_cent = getComptesCentralisateurs($cc);
        if (!in_array($cpte_cpta_gui,$cptes_cent)) {
          $dbHandler->closeConnection(false);
          return new ErrorObj(ERR_ECR_NON_VALIDE,".".sprintf(_("Le compte %s ne peut être mouvementé que par le guichetier qui lui est associé"),$cc));
        }
      }
    }

    // La date comptable doit se situer dans la période de l'exercice
    $dateValide = false;
    $exo = getExercicesComptables($value["id_exo"]);
    if (isset($exo)) {
      $datedeb=getPhpDateTimestamp($exo[0]['date_deb_exo']);
      $datefin=getPhpDateTimestamp($exo[0]['date_fin_exo']);

      if ( ($exo[0]['etat_exo']!=3) // si lexercice n'est pas fermé
           && ( date("y/m/d",$dateope) >= date("y/m/d",$datedeb)) // si la date de l'opération est supérieure à la date début éxo
           && ( date("y/m/d",$dateope) <= date("y/m/d",$datefin)) ) // si la date de l'opération est inférieure à la date fin éxo
        $dateValide = true;

      // la date de l'opération n'est pas valide
      if (!$dateValide) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_DATE_NON_VALIDE,". "._("La date n'est pas dans la période de l'exercice."));

      }

    }

    // pour chaque mouvement, vérifie qu'il n'était dans le brouillard
    if (!$id_his) // tant qu'on n'a pas trouvé de id_his rechercher.
      $id_his=$value['id_his']; // si au moins un mouvement vient du brouillard alors récupérer son id_his.

    $comptable[$i]["id"] = $value['id'];
    $comptable[$i]["compte"] = $value['compte'];

    if ($value['cpte_interne_cli'] == '') {
      $comptable[$i]["cpte_interne_cli"] = NULL;
    } else {
      $comptable[$i]["cpte_interne_cli"] = $value['cpte_interne_cli'];
    }

    if ($value["sens"]=='d') {
      $comptable[$i]["sens"] = SENS_DEBIT;
      $totaldeb += $value["montant"];
    } else {
      $comptable[$i]["sens"] = SENS_CREDIT;
      $totalcred += $value["montant"];
    }

    $comptable[$i]["montant"] = $value["montant"];
    $comptable[$i]["date_comptable"] = $value["date_comptable"];
    $comptable[$i]["date_valeur"] = $value["date_comptable"];
    if (is_champ_traduit('ad_brouillard','libel_ecriture')) {
        // Verify if var is an instance of class Trad
        
        if(is_trad(unserialize($value["libel_ecriture"]))){

            $libel_ecriture_trad = unserialize($value["libel_ecriture"]);
            $libel_ecriture = $libel_ecriture_trad->save();
        }else{
            $libel_ecriture_trad = new Trad();
            $libel_ecriture_trad->set_traduction(get_langue_systeme_par_defaut(), ($value["libel_ecriture"]));

            $libel_ecriture = $libel_ecriture_trad->save();
        }
    }else{
    	$libel_ecriture = htmlspecialchars($value["libel_ecriture"], ENT_QUOTES, "UTF-8");
    }
    $comptable[$i]["libel"] = $libel_ecriture;
    $comptable[$i]["type_operation"] = $value["type_operation"];
    $comptable[$i]["info_ecriture"] = $login_initiateur;
    $comptable[$i]["jou"] = $value["id_jou"];
    $comptable[$i]["exo"] = $value["id_exo"];
    $comptable[$i]["validation"] = 't';
    $comptable[$i]["devise"] = $value["devise"];
    $comptable[$i]["id_ag"] = $value["id_ag"];
    $i++;

    // cas d'annulation, marquer les mouvements à contrepasser comme consolidés
    if ($num_fonction == 474) {
      if ($value["mouvement_consolide"] != NULL) {
        $DATA_CONS = array();
        $DATA_CONS['consolide'] = 't';
        $sql = buildUpdateQuery("ad_mouvement", $DATA_CONS, array("id_mouvement"=>$value["mouvement_consolide"],
                                "id_ag"=>$value["id_ag"]));
        $result = executeQuery($db, $sql);
        if ($result->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $result;
        }
      }
    }
  }

  // le total débit doit être égal au total crédit
  if (round($totaldeb - $totalcred, $global_monnaie_courante_prec) != 0) {
     $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_ECR_NON_VALIDE,". "._("Le total du débit n'est pas égal au total du crédit"));
  }

  // récupère la date comptable
  $date_compta = $comptable[0]["date_comptable"];
  $date_compta=php2pg($date_compta);
  $date_compta=getPhpDateTimestamp($date_compta);

  // vérifier si la date comptable n'est pas postérieure à la date du jour
  if ( date("y/m/d",$date_compta) > date("y/m/d") ) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_DATE_NON_VALIDE,". "._("Elle est postérieure à la date du jour"));
  }

  // dernière clôture
  $sql = "SELECT * FROM ad_clotures_periode WHERE id_ag=$global_id_agence and date_clot_per=(SELECT MAX(date_clot_per) FROM ad_clotures_periode)";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  //s'il existe une clôture périodique,vérifier que la date comptable est postérieure à la date de la dernière clôture périodique
  if ($result->numrows()==1) {
    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $date_dern_clot=getPhpDateTimestamp($row['date_clot_per']);
    if ( date("y/m/d",$date_compta) <= date("y/m/d",$date_dern_clot) ) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_DATE_NON_VALIDE,". "._("Elle est antérieure à la dernière clôture périodique"));
    }
  }

  // écritures
  if ($num_fonction == 470)
    $libel_fonction = _('Validation écritures manuelles');
  elseif($num_fonction == 474)
  $libel_fonction = _('Annulation mouvements réciproques');

  $erreur = ajout_historique($num_fonction, '', $libel_fonction, $global_nom_login, date("r"), $comptable, $PIECE);
  if ($erreur->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $erreur;
  }
  $num_trans = $erreur->param;


  // suppression de l'écriture dans le brouillard
  if ($id_his) // donc les mouvements étaient dans le broullard, il faut les supprimer
    supEcritureBrouillard($id_his);

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $num_trans);

}

/**
 * Fonction  marquant les mouvements reciproque entre le siège et les agences comme annulé (lors de l'édition des etats consolidés)
 * @author Ares
 * @since 3.0
 * @param array $DATA Array contenat les mouvements à annuler
 * @return ObjError On renvoie un objet erreur
 */
 function annulationEcrituresComptables($DATA) {
  global $dbHandler, $global_nom_login, $global_id_guichet, $global_id_agence, $global_monnaie, $global_monnaie_courante_prec;
  $db = $dbHandler->openConnection();
  $num_fonction=474;//
  foreach ($DATA as $key => $value) {
    // Vérification de la validité des écritures
    // cas d'annulation, marquer les mouvements à contrepasser comme consolidés
    if ($num_fonction == 474) {
      if ($value["mouvement_consolide"] != NULL) {
        $DATA_CONS = array();
        $DATA_CONS['consolide'] = 't';
        $sql = buildUpdateQuery("ad_mouvement", $DATA_CONS, array("id_mouvement"=>$value["mouvement_consolide"],
                                "id_ag"=>$value["id_ag"]));
        $result = executeQuery($db, $sql);
        if ($result->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $result;
        }
      }
    }
  }
  $libel_fonction = _('Annulation mouvements réciproques');

  $erreur = ajout_historique($num_fonction, '', $libel_fonction, $global_nom_login, date("r"), NULL, NULL);
  if ($erreur->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $erreur;
  }
  $num_trans = $erreur->param;
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $num_trans);

}

/**
 * Renvoie les comptes qui sont actifs
 */
function getComptesActifs() {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  if($global_id_agence == 0){
  	$sql = "SELECT num_cpte_comptable, libel_cpte_comptable FROM ad_cpt_comptable WHERE is_actif = 't' AND id_ag=$global_id_agence and cpte_princ_jou = 'f' AND devise IS NOT NULL AND num_cpte_comptable NOT IN (SELECT distinct(a.num_cpte_comptable) FROM ad_cpt_comptable a, adsys_etat_credit_cptes b WHERE a.id_ag=$global_id_agence and b.id_ag=$global_id_agence and a.num_cpte_comptable = b.num_cpte_comptable) AND num_cpte_comptable NOT IN (SELECT num_cpte_comptable from ad_cpt_comptable a WHERE EXISTS (SELECT * from ad_cpt_comptable b WHERE b.id_ag=$global_id_agence and a.num_cpte_comptable = b.cpte_centralise)) ORDER BY num_cpte_comptable ;";
  } else {
  	$sql = "SELECT num_cpte_comptable, libel_cpte_comptable FROM ad_cpt_comptable WHERE is_actif = 't' 
            AND id_ag=1 and cpte_princ_jou = 'f' AND devise IS NOT NULL ORDER BY num_cpte_comptable ";
  }


  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
  }

  // création de la liste des comptes
//  $ListeCptes=array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $ListeCptes[$row["num_cpte_comptable"]] = $row["num_cpte_comptable"]. " " .$row["libel_cpte_comptable"]; // FIXME envoie toutes les infos

  $dbHandler->closeConnection(true);
  return $ListeCptes;

}

/**
 * Renvoie les comptes qui peuvent être utilisés pour les écritures dans le brouillard
 * Ces comptes sont ceux qui ont une devise associés, qui ne sont pas comptes principaux d'un journal, et qui ne sont pas comptes miroir d'un crédit
 */
function getComptesBrouillard() {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  if($global_id_agence == 0){
  	$sql = "SELECT num_cpte_comptable, libel_cpte_comptable FROM ad_cpt_comptable WHERE is_actif = 't' AND etat_cpte = 1 AND id_ag=$global_id_agence and cpte_princ_jou = 'f' AND devise IS NOT NULL AND num_cpte_comptable NOT IN (SELECT distinct(a.num_cpte_comptable) FROM ad_cpt_comptable a, adsys_etat_credit_cptes b WHERE a.id_ag=$global_id_agence and b.id_ag=$global_id_agence and a.num_cpte_comptable = b.num_cpte_comptable) AND num_cpte_comptable NOT IN (SELECT num_cpte_comptable from ad_cpt_comptable a WHERE EXISTS (SELECT * from ad_cpt_comptable b WHERE b.id_ag=$global_id_agence and a.num_cpte_comptable = b.cpte_centralise)) ORDER BY num_cpte_comptable ;";
  } else {
  	$sql = "SELECT num_cpte_comptable, libel_cpte_comptable FROM ad_cpt_comptable WHERE is_actif = 't' AND etat_cpte = 1 AND id_ag=$global_id_agence and cpte_princ_jou = 'f' AND devise IS NOT NULL AND num_cpte_comptable NOT IN (SELECT distinct(a.num_cpte_comptable) FROM ad_cpt_comptable a, adsys_etat_credit_cptes b WHERE a.id_ag=$global_id_agence and b.id_ag=$global_id_agence and a.num_cpte_comptable = b.num_cpte_comptable OR a.num_cpte_comptable LIKE b.num_cpte_comptable || '.%') AND num_cpte_comptable NOT IN (SELECT num_cpte_comptable from ad_cpt_comptable a WHERE EXISTS (SELECT * from ad_cpt_comptable b WHERE b.id_ag=$global_id_agence and a.num_cpte_comptable = b.cpte_centralise)) ORDER BY num_cpte_comptable ;";
  }
  

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
  }

  // création de la liste des comptes
  $ListeCptes=array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $ListeCptes[$row["num_cpte_comptable"]] = $row["libel_cpte_comptable"]; // FIXME envoie toutes les infos

  $dbHandler->closeConnection(true);
  return $ListeCptes;

}


/**
 * Fonction vérifiant si un compte comptable est associé à un produit d'épargne
 * @author Papa
 * @since 2.0
 * @param text $compte : numéro d'un compte comptable.
 * @return boolean true si le compte est associé à un produit de d'épargne sinon false
 */
function isCompteEpargne($compte) {
  /*
    Un compte comptable est associé à un produit d'épargne :
      - s'il est directement liè à un produit d'épargne
      - si au moins un de ses comptes centralisateurs est directement lié à un produit d'épargne
  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // Récupération des comptes centralisateurs du compte comptable
  $centralisateurs = array();
  $centralisateurs = getComptesCentralisateurs($compte);

  // On fusionne le compte lui-même et la liste des comptes centralisateurs
  $liste = array();
  $liste = array_merge($centralisateurs,(array)$compte);

  // Parcours de la liste des comptes
  foreach($liste as $key=>$value) {
    $sql = "SELECT * FROM adsys_produit_epargne WHERE id_ag=$global_id_agence and cpte_cpta_prod_ep = '$value'";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    }

    if ($result->numRows() >= 1) {
      $dbHandler->closeConnection(true);
      return true;
    }
  }

  // ni le compte comptable lui-même ni un de ses comptes centralisateurs n'est associé à un produit d'épargne
  $dbHandler->closeConnection(true);
  return false;

}

/**
 * Fonction vérifiant si un compte comptable est associé à un produit de crédit
 * @author Papa
 * @since 2.0
 * @param text $compte : numéro d'un compte comptable.
 * @return boolean true si le compte est associé à un produit de crédit sinon false
 */
function isCompteCredit($compte) {
  /*
    Un compte comptable est associé à un produit de crédit :
      - s'il est directement liè à un produit de crédit
      - si au moins un de ses comptes centralisateurs est directement lié à un produit de crédit
  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // Récupération des comptes centralisateurs du compte comptable
  $centralisateurs = array();
  $centralisateurs = getComptesCentralisateurs($compte);

  // On fusionne le compte lui-même et la liste des comptes centralisateurs
  $liste = array();
  $liste = array_merge($centralisateurs,(array)$compte);

  // Parcours de la liste des comptes
  foreach($liste as $key=>$value) {
    $sql = "SELECT * FROM adsys_etat_credit_cptes WHERE id_ag=$global_id_agence and num_cpte_comptable = '$value'";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    }

    if ($result->numRows() == 1) {
      $dbHandler->closeConnection(true);
      return true;
    }
  }

  // ni le compte comptable lui-même ni un de ses comptes centralisateurs n'est associé à un produit de crédit
  $dbHandler->closeConnection(true);
  return false;

}

/**
 * Fonction vérifiant si un compte comptable est associé à une garantie
 * @author Ibou
 * @since 3.4
 * @param text $compte : numéro d'un compte comptable.
 * @return boolean true si le compte est associé à un produit de d'épargne sinon false
 */
function isCompteGarantie($compte) {
  /*
    Un compte comptable est associé à un produit d'épargne :
      - s'il est directement liè à un produit d'épargne
      - si au moins un de ses comptes centralisateurs est directement lié à un produit d'épargne
  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // Récupération des comptes centralisateurs du compte comptable
  $centralisateurs = array();
  $centralisateurs = getComptesCentralisateurs($compte);

  // On fusionne le compte lui-même et la liste des comptes centralisateurs
  $liste = array();
  $liste = array_merge($centralisateurs,(array)$compte);

  // Parcours de la liste des comptes
  foreach($liste as $key=>$value) {
    $sql = "SELECT * FROM adsys_produit_credit WHERE id_ag=$global_id_agence and cpte_cpta_prod_cr_gar = '$value'";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    }

    if ($result->numRows() >= 1) {
      $dbHandler->closeConnection(true);
      return true;
    }
  }

  // ni le compte comptable lui-même ni un de ses comptes centralisateurs n'est associé à un produit d'épargne
  $dbHandler->closeConnection(true);
  return false;

}

/**
 * Fonction vérifiant si un compte comptable est associé à un guichet
 * Un compte comptable est associé à un guichet :
 *    - s'il est directement liè à guichet
 *    - si au moins un de ses comptes centralisateurs est directement lié à un guichet
 * @author Papa
 * @since 2.0
 * @param text $compte : numéro d'un compte comptable.
 * @return boolean true si le compte est associé à un guichet sinon false
 */
function isCompteGuichet($compte) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // Récupération des comptes centralisateurs du compte comptable
  $centralisateurs = array();
  $centralisateurs = getComptesCentralisateurs($compte);

  // On fusionne le compte lui-même et la liste des comptes centralisateurs
  $liste = array();
  $liste = array_merge($centralisateurs,(array)$compte);

  // Parcours de la liste des comptes
  foreach($liste as $key=>$value) {
    $sql = "SELECT * FROM ad_gui WHERE id_ag=$global_id_agence and cpte_cpta_gui = '$value'";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    }

    if ($result->numRows() == 1) { // le compte ou un des centralisateurs est associé à un guichet
      $dbHandler->closeConnection(true);
      return true;
    }
  }

  // ni le compte comptable lui-même ni un de ses comptes centralisateurs n'est associé à un guichet
  $dbHandler->closeConnection(true);
  return false;

}

function verifClotureExo() {
  //  Verifie si un exercice peut etre cloture a  ce jour
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // Vérifie s'il existe un exercice ouvert ou en cours de fermeture dont sa date de fin est passée
  $date=date("d/m/y");
  $sql  = "SELECT * FROM ad_exercices_compta WHERE id_ag=$global_id_agence and id_exo_compta=";
  $sql .= "(SELECT MIN(id_exo_compta) FROM ad_exercices_compta WHERE id_ag=$global_id_agence and (etat_exo=1 OR etat_exo=2) AND date(date_fin_exo) < date('$date')) ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
  }

  if ($result->numrows()==0)
    return NULL;
  else if ($result->numrows()==1)
    $exo_compta = $result->fetchrow(DB_FETCHMODE_ASSOC);

  $dbHandler->closeConnection(true);
  return $exo_compta["id_exo_compta"];

}

/**********
 * Fonction qui réalise la clôture d'un exercice
 * si l'état de l'exo est à 'Ouvert' alors on ne modifie que cet état en le passant à l'état 'En cours de clôture'
 * si l'état de l'exo est 'En cours de clôture' alors
 *  - on reporter le solde des comptes de charge et de produit vers le compte de résultat (excédent / perte )
 *  - on modifie l'état en le passant à l'état 'clôturé'
 * @author Papa
 * @since 2.0
 * @param int $id_exo L'ID de l'exercice à clôturer
 * @return ErrorObj Les erreurs possibles sont <UL>
 *   <LI> ERR_ECR_ATTENTE_VALID </LI>
 *   <LI> ERR_CPTE_RESULT_NON_DEF </LI>
 *   <LI> Celles renvoyées par {@link #ajout_historique cloturePeriodique} </LI> </UL>
 */
function clotureExercice($id_exo) {
  global $dbHandler, $global_mouvements, $db, $global_id_agence, $global_nom_login, $global_monnaie;
  global $date_total, $date_jour, $date_mois, $date_annee;

  global $comptable_his;
  $db = $dbHandler->openConnection();

  // Vérifie qu'il n'existe pas d'écritures non validéés pour l'exercice
  $sql = "select * from  ad_brouillard where id_ag=$global_id_agence and id_exo=$id_exo";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numrows()!=0) { // il existe des écritures en attente de validation
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_ECR_ATTENTE_VALID);
  }

  // Récupération info exercice
  $exo = array();
  $exo = getExercicesComptables($id_exo);
  $etat_exo = $exo[0]["etat_exo"];
  $date_deb_exo = $exo[0]["date_deb_exo"];
  $date_fin_exo = $exo[0]["date_fin_exo"];

  if ($etat_exo==1) { //  l'exercice est à l'état "ouvert"
    // l'exercice passe de l'état "Ouvert" à l'état "En cours de clôture"
    $etat_exo++;
    $sql = "UPDATE ad_exercices_compta SET etat_exo = $etat_exo  WHERE id_ag = $global_id_agence AND id_exo_compta = $id_exo;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    }
  } else if ($etat_exo==2) { //  l'exercice était en cours de clôture
    // La date de clôture est la date de fin de l'exercice
    $DFE = pg2phpDateBis($date_fin_exo); // conversion date aaaa/mm/jj => jj/mm/aaaa
    $date_cloture = date("d/m/Y", mktime(0,0,0,$DFE[0], $DFE[1], $DFE[2]));

    /* Récupération du compte de résultat paramétré dans agence */
    $AD_AGC = getAgenceDatas($global_id_agence);
    if (!isset($AD_AGC["num_cpte_resultat"]) or $AD_AGC["num_cpte_resultat"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_RESULT_NON_DEF);  // le compte de résultat n'est pas défini
    }
    $num_cpte_resultat = $AD_AGC["num_cpte_resultat"];

    $i = 0;
    $comptable = array();

    /* Récupération de tous les comptes de charges */
    $param["compart_cpte"] = 3;
    $cptes_charge = getComptesComptables($param);

    /* Récupération de tous les comptes de produit */
    $param["compart_cpte"] = 4;
    $cptes_produit = getComptesComptables($param);

    /* Récupération de tous les comptes de gestion */
    $cptes_gestion = array_merge($cptes_charge, $cptes_produit);

    /* Virement des comptes de gestion */
    if (is_array($cptes_gestion))
      foreach($cptes_gestion as $key=>$value) {
      $num_cpte = $value["num_cpte_comptable"];
      $devise_cpte = $value["devise"];

      $solde = calculSoldeNonRecursif($num_cpte, $date_cloture);
      if ($solde != 0) {
        // Mouvement du compte de gestion
        $comptable[$i]["id"] = 1;
        $comptable[$i]["compte"] = $num_cpte;;
        $comptable[$i]["cpte_interne_cli"] = NULL;

        if ($solde > 0) // le solde est créditeur
          $comptable[$i]["sens"] = SENS_DEBIT; // on le débite et on crédite le compte de résultat
        else // le solde est débiteur
          $comptable[$i]["sens"] = SENS_CREDIT; // on le crédite et on débite le compte de résultat
              // traduction du champs 
        $libel_ope_trad = new Trad();
        global $global_langue_utilisateur;
  		$libel_ope_trad->set_traduction($global_langue_utilisateur,  _("Virement des comptes de gestion"));
  		$libel_ope_trad->save();
        $comptable[$i]["montant"] = abs($solde);
        $comptable[$i]["date_comptable"] = $date_cloture;
        $comptable[$i]["date_valeur"] = $date_cloture;
        $comptable[$i]["libel"] = $libel_ope_trad->get_id_str();
        $comptable[$i]["jou"] = 2; // Journal des op diverses
        $comptable[$i]["exo"] = $id_exo;
        $comptable[$i]["validation"] = 't';
        $comptable[$i]["devise"] = $devise_cpte;
        $i++;

        // Mouvement du compte de résultat
        $comptable[$i]["id"] = 1;
        $comptable[$i]["compte"] = $num_cpte_resultat;;
        $comptable[$i]["cpte_interne_cli"] = NULL;

        if ($solde > 0)
          $comptable[$i]["sens"] = SENS_CREDIT; // le solde du compte est créditeur, on crédite le compte de résultat
        else
          $comptable[$i]["sens"] = SENS_DEBIT; // le solde du compte est débiteur, on débite le compte de résultat

        $comptable[$i]["montant"] = abs($solde);
        $comptable[$i]["date_comptable"] = $date_cloture;
        $comptable[$i]["date_valeur"] = $date_cloture;
        $comptable[$i]["libel"] = $libel_ope_trad->get_id_str();
        $comptable[$i]["jou"] = 2; // Journal des op diverses
        $comptable[$i]["exo"] = $id_exo;
        $comptable[$i]["validation"] = 't';
        $comptable[$i]["devise"] = $devise_cpte;
        $i++;
      }
    } // fin virement des comptes de gestion

    $erreur=ajout_historique(442, '', _("Clôture exercice: virement des comptes de gestion "), $global_nom_login, date("r"), $comptable);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }

    // On fait une clôture périodie pour mémoriser les soldes à la date de fin de l'exercice
    $myError = cloturePeriodique($date_cloture, true);

    if ($myError->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myError;
    }

    // L'exercice passe  de l'état "En cours de clôture à l'état  "Clôturé"
    $etat_exo++;
    $sql = " UPDATE ad_exercices_compta SET etat_exo=$etat_exo  WHERE id_ag=$global_id_agence AND id_exo_compta=$id_exo";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    }
  } else {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // état non pris en compte
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

function hasCompteInterne($compte_comptable) {
  /*
     Vérifie si un compte comptable est lié à un compte interne : produit d'épargne, produit de crédit ou guichet
      IN : numéro du compte coptable
      OUT: true si le compte est lié à un compte interne
           False si non
  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // vérifie s'il n'y a pas d'association avec un produit d'épargne
  if (isCompteEpargne($compte_comptable)) {
  	$dbHandler->closeConnection(true);
    return true;
  }

  // vérifie s'il n'y a pas d'association avec un produit de crédit
  if (isCompteCredit($compte_comptable)) {
    $dbHandler->closeConnection(true);
    return true;
  }
// vérifie s'il n'y a pas d'association avec une garantie
  if (isCompteGarantie($compte_comptable)) {
    $dbHandler->closeConnection(true);
    return true;
  }
  $dbHandler->closeConnection(true);
  return false;
}

function cloturePeriodique($date_cloture, $cloture_exo = NULL) {
  /*

    cloturePeriodique effectue une clôture périodique

    IN : $date_cloture-> (j/m/a) date à la quelle on veut effectue une clôture périodique
         $cloture_exo -> indique si la fonction est appelée par clotureExercice

    OUT :

    TRAITEMENTS:
        - vérifier si la date donnée est postérieure à la date de la dernière clôture périodique et antérieure à la date du jour
        - sélection de l'exercice ouvert ou en cours de clôture le plus ancien
        - vérifier si la date donnée est postérieure à la date de début et antérieure à la date de fin de l'exercice
        - vérifier que la date n'est pas la date de fin de l'exercice si elle n'est pas appelée par clôtureExercice
        - vérifie qu'il n'y ait pas d'écritures en attente pour cet exercice
        - vérifie que le solde à mémoriser, pour chaque compte, est égal au solde dans ad_cpt_comptable
        - créer une entrée dans ad_clotures_periode
        - créer des entrées dans ad_cpt_soldes pour mémoriser les soldes des comptes

  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $datedonnee=php2pg($date_cloture);
  $datedonnee=getPhpDateTimestamp($datedonnee);

  // vérifier si date donnée est antérieure à la date du jour
  if ( date("y/m/d",$datedonnee) >= date("y/m/d") ) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_DATE_NON_VALIDE,". "._("Elle est postérieure à la date du jour"));
  }

  // dernière clôture
  $sql = "SELECT * FROM ad_clotures_periode WHERE id_ag=$global_id_agence and date_clot_per=(SELECT MAX(date_clot_per) FROM ad_clotures_periode where id_ag=$global_id_agence)";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  //s'il existe une clôture périodique,vérifier que date donnée est postérieure à la date de la dernière clôture périodique
  if ($result->numrows()==1) {
    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $date_dern_clot=getPhpDateTimestamp($row['date_clot_per']);
    if ( date("y/m/d",$datedonnee) <= date("y/m/d",$date_dern_clot) ) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_DATE_NON_VALIDE,". "._("Elle est antérieure à la dernière clôture périodique"));
    }

  }

  // sélection de l'exercice ouvert ou en cours de clôture le plus ancien
  $sql ="SELECT * FROM ad_exercices_compta WHERE id_ag=$global_id_agence and id_exo_compta=(SELECT MIN(id_exo_compta) FROM ad_exercices_compta WHERE id_ag=$global_id_agence and etat_exo!=3)";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numrows()==1) {
    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $exo=$row["id_exo_compta"];

    $date_debut_exo=getPhpDateTimestamp($row['date_deb_exo']);
    $date_fin_exo=getPhpDateTimestamp($row['date_fin_exo']); //moins un

    // vérifier si la date donnée est postérieure à la date de début et antérieure à la date de fin-1 de l'exercice
    if ( date("y/m/d",$datedonnee) < date("y/m/d",$date_debut_exo) ) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_DATE_NON_VALIDE,". "._("Elle est antérieure à la date de début de l'exercice"));
    }

    //if( date("y/m/d",$datedonnee) >= date("y/m/d",$date_fin_exo) )
    // on a enlevé le = pour la cloture à la date de fin lors d'une clôture d'exercice

    if ( date("y/m/d",$datedonnee) > date("y/m/d",$date_fin_exo) ) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_DATE_NON_VALIDE,". "._("Elle est postérieure à la date de fin de l'exercice auquel appartiendrait la clôture"));
    }

    // Vérifie que seule clotureExercice() peut faire une clôture période au dernier jour de l'exercice
    if ($cloture_exo == NULL AND (date("y/m/d",$datedonnee) == date("y/m/d",$date_fin_exo))) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_DATE_NON_VALIDE,". "._("Vous ne pouvez pas faire une clôture périodique au dernier jour de l'exercice"));
    }


    // vérifie qu'il n'y ait pas d'écritures en attente, pour cet exercice, qui sont antérieure à la date donnée
    $sql ="SELECT * FROM ad_brouillard WHERE id_ag=$global_id_agence and id_exo=$exo AND date_comptable <= '$date_cloture'";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    if ($result->numrows()!=0) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(EXIST_ECR_ATT);
    }

    //vérifie si les soldes calculés sont égaux aux soldes réels des comptes comptables
    $cptes=getComptesComptables();
    if (isset($cptes))
      foreach($cptes as $key=>$value) {
      // calcule le solde à la clôture
      $solde_cloture=calculSolde($value["num_cpte_comptable"],$date_cloture, false);

      $lendemain =pg2phpDateBis( php2pg($date_cloture));
      $lendemain = date("d/m/Y", mktime(0,0,0,$lendemain[0], $lendemain[1]+1, $lendemain[2]));

      //calcule le solde réel
      $solde_reel = calculSoldeNonRecursif($value["num_cpte_comptable"],$date_cloture);

      $difference = $solde_reel - $solde_cloture;

      $InfoDevise = getInfoDevise($value['devise']);
      if (round($difference, $InfoDevise['precision']) != 0) {
        // si le solde réel (dans ad_cpt_comptable) d'un compte n'est pas égal au solde calculé
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_SOLDES_DIFFERENTS,$key);
      }

      $DATA_SOLDES[$key]["num_cpte_comptable_solde"]=$value["num_cpte_comptable"];
      $DATA_SOLDES[$key]["solde_cloture"]=$solde_cloture;

    }

    // Création de la clôture périodique
    $DATA["date_clot_per"]=$date_cloture;
    $DATA["id_exo"]=$exo;

    ajoutCloturePeriodique($DATA);

    // récup id de la clôture créée
    $sql ="SELECT currval('ad_clot_per_seq') as id_clot ";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $id_clot=$row["id_clot"];

    //créer des entrées dans ad_cpt_soldes pour mémoriser les soldes des comptes
    if (isset($DATA_SOLDES))
      foreach ($DATA_SOLDES as $k=>$v) {
      $DATA["num_cpte_comptable_solde"]=$v["num_cpte_comptable_solde"];
      $DATA["solde_cloture"]=$v["solde_cloture"];
      $DATA["id_cloture"]=$id_clot;

      ajoutSoldeCloture($DATA);
    }

    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);
  }
}

/**
 * Reconstitue le solde du compte $compte au soir la date $date_solde
 * NB Ceci implqiue que les mouvements comptabilisés au jour de $date_solde sont bien inclus dans le calcul du solde
 *   TRAITEMENTS:
 *    - Récupère la clôture périodique la plus proche dans le passé
 *    - Pour les comptes de gestion ( 6 ,7 ):
 *     - si cette dernière cloture et $date_solde sont dans le même exercice alors
            solde = solde dernière clôture + les mouvements au crédit - mouvements au débit
 *     - si cette dernière cloture et $date_solde ne sont pas dans le même exercice alors
 *          solde =  mouvements au crédit depuis le début de l'exercice - mouvements au débit depuis le début de l'exercice
 *
 *    - Pour les autres comptes :
 *        solde = solde dernière clôture + les mouvements au crédit - mouvements au débit
 *
 * @param text $compte Numéro du compte
 * @param date $date_solde Date du solde
 * @param bool $cv Calculer la C/V en devise de référence ?
 * @return float $solde
 * @author Papa Ndiaye + Thomas Fastenakel
 * @since 2.0
 */
function calculSolde($compte,$date_solde, $cv=false) {
  global $dbHandler;
  global $global_multidevise, $global_monnaie, $global_id_agence;

  $db = $dbHandler->openConnection();

  // Vérifie que le compte existe bien
  $param["num_cpte_comptable"] = $compte;
  $cpte = getComptesComptables($param);
  if (sizeof($compte) == 0)
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // Le compte $compte n'existe pas

  $solde=0;

  // Récupération de l'exercice dans lequel se trouve $date_solde
  $sql = "SELECT * FROM ad_exercices_compta WHERE id_ag = $global_id_agence and date_deb_exo <= '$date_solde' AND date_fin_exo >= '$date_solde'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $exo = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $id_exo_compta = $exo["id_exo_compta"];
  $date_deb_exo  = $exo["date_deb_exo"];

  // Récupération de la clôture périodique la plus proche à cette date dans le passé
  $sql  = "SELECT * FROM ad_clotures_periode WHERE id_ag = $global_id_agence and date_clot_per=";
  $sql .= "(SELECT MAX(date_clot_per) FROM ad_clotures_periode WHERE id_ag = $global_id_agence and date_clot_per < date('$date_solde')) ";
  $result1 = $db->query($sql);
  if (DB::isError($result1)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // $result1->getMessage()
  }

  if ($result1->numrows()==1) {
    $row = $result1->fetchrow(DB_FETCHMODE_ASSOC);
    $num_cloture=$row["id_clot_per"];
    $date_proche=$row["date_clot_per"];
    $id_exo_cloture=$row["id_exo"];

    // solde à cette clôture
    $solde = getSoldeCompteCloture($compte,$num_cloture, $cv);

    // lendemain de la clôture
    $date_proche = pg2phpDateBis($date_proche);
    $date_proche = date("d/m/Y", mktime(0,0,0,$date_proche[0], $date_proche[1]+1, $date_proche[2]));
  }

  /* Mouvements du lendemain de la dernière clôture jusqu'à la date donnée, ou tous les mvts avant la date donnée si pas de clôture */
  $sql="SELECT * FROM ad_mouvement MV, ad_ecriture EC WHERE MV.id_ag=$global_id_agence and EC.id_ag=$global_id_agence and MV.compte='$compte' AND MV.id_ecriture=EC.id_ecriture ";

  if (isset($date_proche)) // il y a une clôture périodique
    $sql .="AND EC.date_comptable BETWEEN '$date_proche' AND '$date_solde'";
  else // on a pas trouvé une clôture périodique
    $sql .="AND EC.date_comptable <= '$date_solde'";

  $result2 = $db->query($sql);
  if (DB::isError($result2)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // $result2->getMessage()
  }

  while ($row = $result2->fetchrow(DB_FETCHMODE_ASSOC)) {
    if ($cv)
      $montant = calculeCV($row['devise'], $global_monnaie, $row['montant']);
    else
      $montant = $row['montant'];

    if ($row['sens'] == SENS_DEBIT)
      $solde = $solde - $montant;
    elseif($row['sens'] == SENS_CREDIT)
    $solde = $solde + $montant;
  }

  $dbHandler->closeConnection(true);
  return $solde;
}


/**
 * Calcule le solde du compte $compte à la date $date_solde sans récursivité
 * Càd que les sous compte s'ils existent ne sont pas inclus dans le solde du compte
 * Cette fonction est nécessaire pour la balance
 * @param text $compte Numéro du compte
 * @param date $date_solde Date du solde
 * @return float $solde
 * @author Papa Ndiaye + Thomas Fastenakel
 * @since 2.0
 */
function calculSoldeNonRecursif($compte,$date_solde,$consolide=false) {

  global $dbHandler;
  global $global_multidevise, $global_monnaie, $global_id_agence;

  $db = $dbHandler->openConnection();

  /*//L'appel à la fonction getComptesComptables pour recupérer seulemnt le solde est trop lourd
  // Vérifie que le compte existe bien
  $param["num_cpte_comptable"] = $compte;
  $cpte = getComptesComptables($param,NULL,$date_solde);
  $solde_courant = $cpte[$compte]["solde"];
  */
  //Recupération du solde du compte
  $solde_courant = getSoldeCpteComptable($compte, $date_solde);

  // Mouvements du lendemain de la date de calcul du solde jusqu'à aujourd'hui
  // Au débit
  $sql="SELECT sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE a.id_ag=b.id_ag and b.id_ag=$global_id_agence and a.id_ecriture = b.id_ecriture AND compte = '$compte' AND date_comptable BETWEEN (date('$date_solde') + interval '1 day') AND date(now()) AND sens = 'd'";
  $result2 = $db->query($sql);
  if (DB::isError($result2)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, $result2->getMessage());
  }
  $row = $result2->fetchrow();
  $total_debit = $row[0];

  // Au crédit
  $sql="SELECT sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE a.id_ag=b.id_ag and b.id_ag=$global_id_agence and a.id_ecriture = b.id_ecriture AND compte = '$compte' AND date_comptable BETWEEN (date('$date_solde') + interval '1 day') AND date(now()) AND sens = 'c'";
  $result2 = $db->query($sql);
  if (DB::isError($result2)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, $result2->getMessage());
  }
  $row = $result2->fetchrow();
  $total_credit = $row[0];
  //
  $soldeReciproque=0;
  if($consolide){
  	$b_ok=existeMouvementsReciproques($compte,$date_solde,NULL);
  	if($b_ok){
  		 $soldeReciproque=calculSoldereciproque($compte,$date_solde);
  	}
  }

  $dbHandler->closeConnection(true);
  return recupMontant($solde_courant - $total_credit + $total_debit-$soldeReciproque);
}

/**
 * Calcule le solde du compte $compte à la date $date_solde avec récursivité
 * Càd que les sous compte s'ils existent  sont  inclus dans le solde du compte
 * Cette fonction est nécessaire pour la balance
 * @param text $compte Numéro du compte
 * @param date $date_solde Date du solde
 * @param text $condSousComptes condition de selection des sous comptes
 * @return float $solde
 * @author Papa Ndiaye + Thomas Fastenakel
 * @since 2.0
 */
function calculSoldeRecursif($compte,$date_solde,$consolide,$condSousComptes) {

  global $dbHandler;
  global $global_multidevise, $global_monnaie, $global_id_agence;

  $db = $dbHandler->openConnection();
      $solde= calculSoldeNonRecursif($compte,$date_solde,$consolide);

   /* Si c'est un compte centralisateur */
  if (isCentralisateur($compte)) {
  	$sous_comptes = array();
    $sous_comptes = getSousComptes($compte,false,$condSousComptes);

   /* Ajouter dans son solde les soldes de ses sous-comptes directs */
    while (list($key,$value)=each($sous_comptes)) {
    	$solde = $solde + calculSoldeRecursif($key, $date_solde,$consolide,$condSousComptes);

    }

  }

  $dbHandler->closeConnection(true);
  return recupMontant($solde);
}


/**
 * Calcule le solde du compte $compte de tous les mvts reciproques SIEGE/AGENCE à la date $date_solde
 * Càd que les sous compte s'ils existent ne sont pas inclus dans le solde du compte
 * Cette fonction est nécessaire pour avoir les etats consolidés
 * @param text $compte Numéro du compte
 * @param date $date_solde Date du solde
 * @return float $solde
 * @author ares
 * @since 3.0
 */
function calculSoldereciproque($compte,$date_solde) {

  global $dbHandler;
  global $global_multidevise, $global_monnaie, $global_id_agence;

  $db = $dbHandler->openConnection();

  // Au débit
  $sql="SELECT sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE a.id_ag=$global_id_agence and b.id_ag=$global_id_agence and a.id_ecriture = b.id_ecriture AND compte = '$compte' AND date_comptable <=date('$date_solde')  AND sens = 'd' AND consolide='t'";
  $result2 = $db->query($sql);
  if (DB::isError($result2)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, $result2->getMessage());
  }
  $row = $result2->fetchrow();
  $total_debit = floatval($row[0]);

  // Au crédit
  $sql="SELECT sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE a.id_ag=$global_id_agence and b.id_ag=$global_id_agence and a.id_ecriture = b.id_ecriture AND compte = '$compte' AND date_comptable <=date('$date_solde')  AND sens = 'c' AND consolide='t'";
  $result2 = $db->query($sql);
  if (DB::isError($result2)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, $result2->getMessage());
  }
  $row = $result2->fetchrow();
  $total_credit = floatval($row[0]);

  $dbHandler->closeConnection(true);

  return recupMontant( $total_credit - $total_debit);
}
/**
 * Calcule la somme des mouvements d'un compte sur une periode'
 * @param text $compte Numéro du compte
 * @param date $date_deb Date de début de la période
 * @param date $date_fin Date de fin de la période
 * @param text $sens sens du mouvement: d=debit ou c=credit
 * @return float $somme
 * @author ares
 * @since 3.0
 */
function calculeSommeMvtCpte($compte,$date_deb,$date_fin,$sens,$consolide) {
	global $dbHandler;
  global $global_multidevise, $global_monnaie, $global_id_agence;
  $db = $dbHandler->openConnection();
  $conditon="";
  if($consolide){ //si on veut les états consolidés
  	$conditon=" AND consolide is not  true  ";
    }
  // Ajout de la somme des mouvements au débit sur la période
  $sql = "SELECT SUM(montant)  AS total_debits FROM ad_ecriture,ad_mouvement WHERE ";
  $sql .= "ad_ecriture.id_ag = ad_mouvement.id_ag AND ad_mouvement.id_ag = $global_id_agence AND ad_ecriture.id_ecriture=ad_mouvement.id_ecriture AND compte = '$compte' ";
  $sql .= "AND sens = '$sens' AND date(date_comptable) >= '$date_deb' AND date(date_comptable) <= '$date_fin'".$conditon;
  $result2 = $db->query($sql);
  if (DB :: isError($result2)) {
  	$dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $infos = $result2->fetchrow();
  $dbHandler->closeConnection(true);
  return $infos[0];
}

/**
 * Calcule la somme des mouvements d'un compte sur une periode de manière recursive'
 * @param text $compte Numéro du compte
 * @param date $date_deb Date de début de la période
 * @param date $date_fin Date de fin de la période
 * @param text $sens sens du mouvement: d=debit ou c=credit
 * @return float $somme
 * @author ares
 * @since 3.0
 */
function calculeSommeMvtCpteRecursif($compte,$date_deb,$date_fin,$sens,$consolide,$condSousComptes) {
	global $dbHandler;
  global $global_multidevise, $global_monnaie, $global_id_agence;

  $solde=calculeSommeMvtCpte($compte,$date_deb,$date_fin,$sens,$consolide);
    /* Si c'est un compte centralisateur */
  if (isCentralisateur($compte)) {
  	$sous_comptes = array();
    $sous_comptes = getSousComptes($compte,false,$condSousComptes);
    /* Ajouter dans son solde les soldes de ses sous-comptes directs */
    while (list($key,$value)=each($sous_comptes)) {
    	$solde = $solde + calculeSommeMvtCpteRecursif($key,$date_deb,$date_fin,$sens,$consolide,$condSousComptes);
    }
  }
return $solde;

}

/**
 * Calcule récursif du solde d'un compte à la date $date_solde dans la partie du bilan correspondant au compartiment $partie_bilan
 * @param text $compte Numéro du compte
 * @param date $date_solde Date du solde
 * @param int $compart indique dans quelle partie du bilan on veut obtenir le solde
 * @return float $solde le solde du compte : à l'ACTIF ($partie_bilan=1) ou au PASSIF ($partie_bilan=2)
 * @author Papa Ndiaye
 * @since 2.0
 */
function calculeSoldeBilan($compte, $date_solde, $partie_bilan,$cv=false,$consolide) {
  global $dbHandler;
  global $global_monnaie, $global_id_agence;
  $db = $dbHandler->openConnection();

  /* Listes des comptes comptables */
  $Where["num_cpte_comptable"]=$compte;
  $infos_cpte = getComptesComptables($Where);

  $solde = 0;
  $total_credit =0;
  $total_debit=0 ;

  $solde_reel = calculSoldeNonRecursif($compte, $date_solde,$consolide);
  if ($cv)
    $solde_reel = calculeCV($infos_cpte[$compte]['devise'], $global_monnaie, $solde_reel);

  /* Si c'est un compte de la partie du bilan à créer : ACTIF ou bien PASSIF */
  if ($infos_cpte[$compte]['compart_cpte'] == $partie_bilan)
    $solde = $solde_reel;
  elseif($infos_cpte[$compte]['compart_cpte'] == 5) { /* Compte Actif-Passif */
    if ($partie_bilan==1 and $solde_reel <0)
      $solde = $solde_reel;

    if ($partie_bilan==2 and $solde_reel > 0)
      $solde = $solde_reel;
  }

  /* Si c'est un compte centralisateur */
  if (isCentralisateur($compte)) {
    $sous_comptes = array();
    $sous_comptes = getSousComptes($compte, false);

    /* Ajouter dans son solde les soldes de ses sous-comptes directs */
    while (list($key,$value)=each($sous_comptes)) {
           $solde = $solde + calculeSoldeBilan($key, $date_solde, $partie_bilan, $cv,$consolide);
    }

  }// fin else compte feuille

  $dbHandler->closeConnection(true);
  return $solde;

}

/**
 * Calcule récursif du solde d'un compte à une période pour le rapport compte de résultat
 * @param text $compte Numéro du compte
 * @param date $date_deb Date de début de la période
 * @param date $date_fin Date de fin de la période
 * @param boolean $cv booléen Indique s'il faut calculer le solde dans la devise de référence
 * @return float $solde le solde du compte à la période
 * @author Papa Ndiaye
 * @since 2.0
 */
function calculeSoldeCompteResultat($compte, $date_deb, $date_fin, $date_fin_exo, $cv=false,$consolide) {
  global $dbHandler;
  global $global_monnaie, $global_id_agence;
  $db = $dbHandler->openConnection();

  /* Listes des comptes comptables */
  $Where["num_cpte_comptable"]=$compte;
  $infos_cpte = getComptesComptables($Where);


  $solde = 0;

  /* Solde du compte au début de la période */
  $solde_deb = calculSoldeNonRecursif($compte, $date_deb,$consolide);

  /* Solde du compte à la fin de la période */
  $solde_fin = calculSoldeNonRecursif($compte, $date_fin,$consolide);

  /* Si la date de fin coincide avec la date de fin de l'exo, enlever les éventuels mouvements de virement */
  if ($date_fin == $date_fin_exo) {
  	//si etat consolidé
  	if($consolide){
  		$condition=" AND consolide is not  true  ";
  	}
    /* Annulation des débits */
    $sql  ="SELECT sum(c.montant) FROM ad_his a, ad_ecriture b, ad_mouvement c WHERE a.id_ag=$global_id_agence and b.id_ag=$global_id_agence and c.id_ag=$global_id_agence";
    $sql .="AND a.type_fonction=442 AND b.date_comptable='$date_fin_exo' AND c.compte='$compte' AND sens='d'";
    $sql .="AND a.id_his=b.id_his AND b.id_ecriture=c.id_ecriture";
    $sql.=$condition;
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $row= $result->fetchrow();
    if ($row[0] > 0)
      $solde_fin += $row[0];

    /* Annulations des crédits */
    $sql  ="SELECT sum(c.montant) FROM ad_his a, ad_ecriture b, ad_mouvement c WHERE a.id_ag=$global_id_agence and b.id_ag=$global_id_agence and c.id_ag=$global_id_agence";
    $sql .="AND a.type_fonction=442 AND b.date_comptable='$date_fin_exo' AND c.compte='$compte' AND sens='c'";
    $sql .="AND a.id_his=b.id_his AND b.id_ecriture=c.id_ecriture ";
    $sql.=$condition;
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $row= $result->fetchrow();
    if ($row[0] > 0)
      $solde_fin -= $row[0];
  }

  if ($cv) {
    $solde_deb = calculeCV($infos_cpte[$compte]['devise'], $global_monnaie, $solde_deb);
    $solde_fin = calculeCV($infos_cpte[$compte]['devise'], $global_monnaie, $solde_fin);
  }

  $solde = $solde_fin - $solde_deb;

  /* Si c'est un compte centralisateur */
  if (isCentralisateur($compte)) {
    $sous_comptes = array();
    $sous_comptes = getSousComptes($compte,false);

    /* Ajouter dans son solde les soldes de ses sous-comptes directs */
    while (list($key,$value)=each($sous_comptes)) {
      $solde = $solde + calculeSoldeCompteResultat($key, $date_deb, $date_fin, $date_fin_exo, $cv,$consolide);
    }

  }// fin else compte feuille

  $dbHandler->closeConnection(true);
  return $solde;

}


/**********
 * Fonction qui calcule pour un compte le solde des mouvements de l'exerciece en cours
 * utile pour la répartition des soldes des comptes de gestions centralisateurs lors de la création de sous-comptes
 * @author Papa
 * @since 2.2
 * @param txt $compte Le numéro du compte comptable
 * @return int Le solde des mouvements du compte dans l'exercice en cours
 */
function calculeSoldeCpteGestion($compte) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $solde=0;

  /* Exercice en cours */
  $AG = getAgenceDatas($global_id_agence );
  $id_exo_encours = $AG["exercice"];

  $infos_exo_encours = getExercicesComptables($id_exo_encours);

  /* Mouvements au débit dans l'exercie en cours */
  $sql="SELECT sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE b.id_ag=$global_id_agence and a.id_ag=$global_id_agence and a.id_ecriture = b.id_ecriture AND compte = '$compte' AND date_comptable BETWEEN date('".$infos_exo_encours[0]['date_deb_exo']."') AND date('".$infos_exo_encours[0]['date_fin_exo']."') AND sens = 'd' ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $total_debit = $row[0];

  /* Mouvements au crédit dans l'exercie en cours */
  $sql="SELECT sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE a.id_ag=$global_id_agence and b.id_ag=$global_id_agence and a.id_ecriture = b.id_ecriture AND compte = '$compte' AND date_comptable BETWEEN date('".$infos_exo_encours[0]['date_deb_exo']."') AND date('".$infos_exo_encours[0]['date_fin_exo']."') AND sens = 'c'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $total_credit = $row[0];

  $solde = $total_credit - $total_debit;

  $dbHandler->closeConnection(true);
  return $solde;
}


/**
 * Renvoie le solde du compte $compte à la cloture périodique $id_cloture
 * Si $cv = true, le solde est calculé dans la devise de référence
 * @param text $compte Numéro du compte
 * @param int $id_cloture Numéro de la cloture périodique
 * @param bool $cv True si on veut avoir la C/V du solde en devise de référence
 * @author Papa Ndiaye + Thomas Fastenakel
 * @return float Solde à la clôture
 */
function getSoldeCompteCloture($compte,$id_cloture, $cv=false) {
  global $dbHandler;
  global $global_multidevise, $global_monnaie, $global_id_agence;
  $db = $dbHandler->openConnection();
  $solde=0;

  $CPT = getComptesComptables(array("num_cpte_comptable" => $compte));

  $sql = "SELECT solde_cloture ";
  $sql.= "  FROM ad_cpt_soldes" ;
  $sql.= " WHERE id_ag=$global_id_agence and num_cpte_comptable_solde ='$compte' and id_cloture=$id_cloture";

  $result2 = $db->query($sql);
  if (DB::isError($result2)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $row = $result2->fetchrow(DB_FETCHMODE_ASSOC);
  if (!isset($row['solde_cloture']) or $row['solde_cloture'] == NULL)
    $solde= 0;
  else {
    $solde= $row['solde_cloture'];
    if ($cv)
      $solde = calculeCV($CPT[$compte]["devise"], $global_monnaie, $solde);
  }

  $dbHandler->closeConnection(true);
  return $solde;

}
/**
 * Fonction nombre de cloture periodique  de l' exercice
 * @return Objet ObjError
 *                      errCode: attribut code de l'erreur
 *                      param[0]: nombre de cloture periodique
 *
 */

function getNbreCloturesPeriodiques() {

  global $dbHandler, $global_id_agence,$global_id_exo;

  $sql = "SELECT count(id_clot_per) FROM ad_clotures_periode where id_ag=$global_id_agence and id_exo=$global_id_exo ";
  $resultat = executeDirectQuery($sql,true);
  return $resultat;
}


/**
 * parametres num_compte
 * Fonction verifie si solde d'un compte est null
 * @return  true if  solde  is null
 *          false if solde  has value           
 *
 */
function checksoldeNull($numcpt){
	
	global $dbHandler, $global_id_agence;
	
	$db = $dbHandler->openConnection();

	$sql = "Select * from ad_cpt_comptable Where num_cpte_comptable ='$numcpt' AND solde = 0.000000 ;";
   
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}
	$dbHandler->closeConnection(true);
	if($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
		return true;
	}else{
		return false;
	}
	
	
}

/**
 * Vérifie s'il y'a des écritures dans le dernier nimporte quel periode d'exercice
 * @param string $compte le numéro de compte
 * @return bool true s'il y'a des écritures dans nimporte quel period d'exercice  false sinon
 */
function checkMouvementEcritures($compte){

		global $dbHandler, $global_id_agence;
	
		$db = $dbHandler->openConnection();

		$sql = "SELECT * FROM ad_mouvement m, ad_ecriture e";
		$sql .= " WHERE m.compte = '$compte' AND m.id_ag = $global_id_agence AND e.id_ag = m.id_ag ";
		$sql .= " AND e.id_ecriture = m.id_ecriture  ;";
	
		$result = $db->query($sql);
		if (DB::isError($result)) {
			$dbHandler->closeConnection(false);
			signalErreur(__FILE__,__LINE__,__FUNCTION__);
		}
		$dbHandler->closeConnection(true);
		if($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
			return true;
		}else{
			return false;
		}
	}


function getCloturesPeriodiques($fields_values=NULL) {
  /*
   Fonction renvoyant les clôtures périodiques
   IN : si pas de paramères en entrée alors renvoyer toutes les clôtures périodiques
        si array fields_values, on construit la clause WHERE ainsi : ... WHERE field = value ...

   OUT: array ( index => infos clôture périodique )
  */

  global $dbHandler, $global_id_agence;

  //vérifier qu'on reçoit bien un array
  if (($fields_values != NULL) && (! is_array($fields_values)))
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Mauvais argument dans l'appel de la fonction"

  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_clotures_periode where id_ag=$global_id_agence and ";

  if (isset($fields_values)) {

    foreach ($fields_values as $key => $value)
    $sql .= "$key = '$value' AND ";

  }
  $sql = substr($sql, 0, -4);
  $sql .= "ORDER BY id_ag, id_clot_per ASC";
  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $clotures = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $clotures[$row["id_ag"]][$row["id_clot_per"]] = $row;

  return $clotures;
}

function getDetailClotPer($fields_values=NULL) {
  /*
   Fonction renvoyant le détail des  clôtures périodiques
   IN : si pas de paramères en entrée alors renvoyer toutes les clôtures périodiques
        si array fields_values, on construit la clause WHERE ainsi : ... WHERE field = value ...

   OUT: array ( index => infos clôture périodique )
  */

  global $dbHandler, $global_id_agence;

  //vérifier qu'on reçoit bien un array
  if (($fields_values != NULL) && (! is_array($fields_values)))
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Mauvais argument dans l'appel de la fonction"

  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_cpt_soldes where id_ag=$global_id_agence and ";

  if (isset($fields_values)) {
    foreach ($fields_values as $key => $value)
    $sql .= "$key = '$value' AND ";
  }
  $sql = substr($sql, 0, -4);
  $sql .= "ORDER BY id_cloture ASC";
  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $clotures = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($clotures,$row);

  return $clotures;
}


function ajoutCloturePeriodique($DATA) {
  /*
    ajoutCloturePeriodique crée une nouvelle clôture période
   IN : array contenant la date de la clôture et l'id de l'exercice correspondant

   TRAITEMENTS: ajouter une entrée dans la table ad_clotures_periodique

   */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql ="INSERT INTO ad_clotures_periode (date_clot_per,id_ag,id_exo) VALUES('".$DATA["date_clot_per"]."',$global_id_agence,".$DATA["id_exo"].")";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return true;
}

function ajoutSoldeCloture($DATA) {
  /*
    ajoutSoldeCloture mémorise le solde d'un compte comptable lors d'une cloture périodique
   IN : array contenant: - le numéro du compte comptable
                         - l'id de la clôture période
                         - le sode à mémoriser

   TRAITEMENTS: ajoute une entrée dans la table ad_cpt_soldes

   */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

   $sql ="INSERT INTO ad_cpt_soldes VALUES('".$DATA["num_cpte_comptable_solde"]."',".$DATA["id_cloture"].",".$DATA["solde_cloture"].", $global_id_agence)";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return true;
}

/**
 * Renvoie la liste des sous-comptes d'un compte comptable
 * @param text $compte Numéro du compte comptable
 * @param bool $recusrif true si on désire ontenir tous les sous comptes récursivement
 * @param text $whereSousCpte condition de selections des sous comptes
 * @return Array List edes sous comptes
 */
function getSousComptes($compte, $recursif=true,$whereSousCpte) { //$condSousComptes
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $liste_sous_comptes=array();

  $sql ="SELECT * FROM ad_cpt_comptable WHERE cpte_centralise ='".$compte."' AND id_ag = ".$global_id_agence;
  $sql.=$whereSousCpte;
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    // ajoute le compte dans la liste
    $liste_sous_comptes[$row['num_cpte_comptable']] = $row;

    // ajouter les sous-comptes du sous-compte par récursivité si récursif
    if ($recursif)
      $liste_sous_comptes = array_merge($liste_sous_comptes,getSousComptes($row['num_cpte_comptable'], true,$whereSousCpte));
  }

  $dbHandler->closeConnection(true);
  return $liste_sous_comptes;
}
/**
 * Vérifie s'il y'a des écritures dans le dernier exercice
 * @param string $compte le numéro de compte
 * @return bool true s'il y'a des écritures dans l'exercice encours false sinon
 */
function isEcritureDerniereExercice($compte){

 global $dbHandler, $global_id_agence,$global_id_exo;

  $db = $dbHandler->openConnection();

  $liste_sous_comptes=array();
  $sql = "SELECT * FROM ad_mouvement m, ad_ecriture e";
  $sql .= " WHERE m.compte = '$compte' AND m.id_ag = $global_id_agence AND e.id_ag = m.id_ag ";
  $sql .= " AND e.id_ecriture = m.id_ecriture AND e.id_exo = $global_id_exo ;";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  if($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
  	 return true;
  }else{
  	 return false;
  }
}

/**
 * Fonction permettant de savoir si des écritures ont été passées sur un compte depuis la dernière cloture périodique
 * @author Djibril NIANG
 * @since 3.0
 * @param text $compte : numéro d'un compte comptable.
 * @return BOOLEAN true s'il y'a des écritures dans l'exercice encours false sinon
 */
function isEcritureDerniereCloturePeriodique($compte){

 global $dbHandler, $global_id_agence,$global_id_exo;

  $db = $dbHandler->openConnection();

  $liste_sous_comptes=array();
  $sql = "SELECT * FROM ad_mouvement m, ad_ecriture e";
  $sql .= " WHERE m.compte = '$compte' AND m.id_ag = $global_id_agence AND m.id_ag = e.id_ag ";
  $sql .= " AND e.id_ecriture = m.id_ecriture AND e.id_ecriture = m.id_ecriture ";
  $sql .=	" AND e.date_comptable >  (select  max(date_clot_per) FROM ad_clotures_periode c ";
  $sql .=	" WHERE c.id_exo = $global_id_exo AND  c.id_ag = $global_id_agence);";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  if($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
  	 return true;
  }else{
  	 return false;
  }
}

/**
 * Fonction renvoyant la liste des comptes centralisateurs d'un compte comptable
 * @author Papa
 * @since 2.0
 * @param text $compte : numéro d'un compte comptable.
 * @return array liste de comptes comptables
 */
function getComptesCentralisateurs($compte) {
  /*
   renvoie la liste des comptes dont est dérivée un un compte comptable
   IN:  compte comptable
   OUT : liste des comptes centralisateurs de ce compte
  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $centralisateurs=array();

  // récupère le numéro du compte centralisateur
  $param["num_cpte_comptable"]=$compte;
  $cpte=getComptesComptables($param);

  //si compte possède un compte centralisateur
  if ( $cpte[$compte]["cpte_centralise"]!='') {
    // ajoute le numéro du compte centralisateur dans la liste
    array_push($centralisateurs , $cpte[$compte]["cpte_centralise"]);

    // ajouter, par récursivité, les comptes centralisateurs du compte centralisateur
    $centralisateurs=array_merge($centralisateurs,getComptesCentralisateurs($cpte[$compte]["cpte_centralise"]));
  }

  // Tri du tableau
  if ( isset($centralisateurs) )
    sort($centralisateurs);

  $dbHandler->closeConnection(true);
  return $centralisateurs;

}


function isCompteCentCptePrinc($compte) {
  /*
     Vérifie s'il existe un compte principal dérivé du compte comptable donné

  */

  global $global_id_agence;
  // récupération de tous les comptes comptables
  $cptes= getComptesComptables();

  // récupération des comptes dérivés de ce compte
  $sous_comptes=array();
  $sous_comptes=getSousComptes($compte, true);

  // vérifier si au moins un de ces comptes est compte principal d'un journal
  if (isset($sous_comptes))
    foreach($sous_comptes as $key=>$value)
    if ($cptes[$value["num_cpte_comptable"]]["cpte_princ_jou"]=='t')
      return true;

  return false;

}

/**
 * Fonction renvoyant les mouvements sur les comptes comptables définis dans le plan comptable
 * @author Mouhamadou
 * @since 1.0
 * @param array $fields_values Array permettant de construire une clause WHERE pour le SELECT.
 * @param integer $limit Limite sur le nombre de lignes à retourner (0 = pas de limite)
 * Si argument NULL, on renvoie tous les comptes. L'array a la forme (fieldname=>value recherchée)
 * @return array On renvoie un tableau associatif avec les infos ad_mouvement
 */
function getMouvementsComptables($fields_values=NULL, $limit=0) {

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  //vérifier qu'on reçoit bien un array
  if (($fields_values != NULL) && (! is_array($fields_values)))
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Mauvais argument dans l'appel de la fonction"

  $sql = "SELECT * FROM ad_mouvement where id_ag=$global_id_agence and ";

  if (isset($fields_values)) {
    foreach ($fields_values as $key => $value)
    $sql .= "$key = '$value' AND ";

  }
  $sql= substr($sql, 0, -4);
  $sql .= "ORDER BY id_ecriture ASC";

  // Limite sur le nombre de records retournés
  if ($limit > 0) {
    $sql .= " LIMIT $limit";
  }

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $mouves = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    //array_push($mouves, $row);
    $mouves[$row["id_mouvement"]]=$row;

  $dbHandler->closeConnection(true);
  return $mouves;
}

/**
 * Fonction renvoyant les infos des écritures  sur les comptes comptables définis
 * @author Mouhamadou
 * @since 1.0
 * @param array $fields_values Array permettant de construire une clause WHERE pour le SELECT.
 * Si argument NULL, on renvoie tous les comptes. L'array a la forme (fieldname=>value recherchée)
 * @return array On renvoie un tableau associatif avec les infos ad_ecriture
 */
function getEcrituresComptables($fields_values=NULL) {

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  //vérifier qu'on reçoit bien un array
  if (($fields_values != NULL) && (! is_array($fields_values)))
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Mauvais argument dans l'appel de la fonction"

  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_ecriture where id_ag=$global_id_agence and ";

  if (isset($fields_values)) {
    foreach ($fields_values as $key => $value)
    $sql .= "$key = '$value' AND ";

  }
  $sql = substr($sql, 0, -4);
  $sql .= "ORDER BY id_ecriture ASC";

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $mouves = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    //    array_push($mouves, $row);
    $mouves[$row["id_ecriture"]]=$row;
  return $mouves;
}

/**
 * Fonction pour la suppression du compte et de l'etat lié  à un produit de crédit
 * @author Mamadou Mbaye
 * @param int $id_etat_credit ID de l'etat du crédit
 * @param int $id_prod_credit ID du produit de crédit
 * @return 1
 */
function supprime_compte_etat_credit($id_etat_credit,$id_prod_credit) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $retour=array();

  $sql ="DELETE FROM adsys_etat_credit_cptes WHERE id_ag=$global_id_agence and id_etat_credit =$id_etat_credit  AND id_prod_cre=$id_prod_credit  ;";
  $result = $db->query($sql);

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  return 1;
}

/**
 * Fonction qui recupére pour un produit de credit donné, tous les états de credit et leurs comptes associés
 * @author ares voukissi
 * @param array $id_etat_credit  ID de l'état du crédit
 * @return array $retour
 */
function getAllCompteEtatCredit($id_produit_credit) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $retour=array();

  $sql ="SELECT *  FROM adsys_etat_credit_cptes WHERE id_ag=$global_id_agence and id_prod_cre ='".$id_produit_credit."';";

  $result = $db->query($sql);

  if (DB::isError($result)) {
  	$dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
  	foreach ($row as $name=>$values ) {
  		$retour[$row["id_etat_credit"]]["$name"]=$row["$name"];
  	}

  }
  $dbHandler->closeConnection(true);
  return $retour;
}
/**
 * Fonction pour la recupération des tous les comptes et de l'etat lié  à un produit de crédit
 * @author Mamadou Mbaye
 * @param array $id_etat_credit  ID de l'état du crédit
 * @return array $retour contenant le numero de compte et id du produit de crédit
 */
function recup_compte_etat_credit($id_produit_credit) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $retour=array();

  $sql ="SELECT *  FROM adsys_etat_credit_cptes WHERE id_ag=$global_id_agence and id_prod_cre ='".$id_produit_credit."';";

  $result = $db->query($sql);
  if (DB::isError($result)) {
  	$dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $retour[$row["id_etat_credit"]]=$row["num_cpte_comptable"];
 $dbHandler->closeConnection(true);
  return $retour;
}

/**
 * Fonction pour l'alimentation de la table adsys_etat_credit_cptes
 * @author Mamadou Mbaye
 * @param array $cptes_etats tableau des comptes comptables à alimenter, indexé par les états de crédit
 * @param array $id_produit_credit  ID du produit de crédit
 * @return 1
 */
function compte_etat_credit($cptes_etats, $id_produit_credit,$maj) {
  global $dbHandler, $error, $global_nom_login, $global_id_agence;
  $msg_erreur = "";
  $nouvel_etat = array ();
  $nouvel_etat["id_prod_cre"] = $id_produit_credit;
  $produits = getProdInfo("WHERE id = $id_produit_credit");
  $produit = $produits[0];
  foreach ($cptes_etats as $etat => $cptes) {
    // On met toutes les opérations SQL relatives à un état de crédit ensemble
    $db = $dbHandler->openConnection();
	$existDefEtat = true;
    // Recherche de la définition actuelle de l'état de crédit
    $sql = "SELECT * FROM adsys_etat_credit_cptes WHERE id_ag = $global_id_agence AND id_prod_cre = $id_produit_credit AND id_etat_credit = $etat;";
    $result = executeQuery($db, $sql, FALSE);
    if ($result->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      $msg_erreur .= sprintf(_("Problème à la mise à jour de l'état %s : %s.  "), $etat, $error[$result->errCode]);
      continue;
    }elseif(sizeof($result->param[0]) == 0){ 
      $existDefEtat = false;
    }
    $etat_credit = $result->param[0];

    $sql = "SELECT SUM(solde_cap) as somme FROM ad_dcr, ad_etr
            WHERE ad_dcr.id_ag =ad_etr.id_ag
            AND ad_dcr.id_doss = ad_etr.id_doss AND remb = 'f'
            AND id_prod = $id_produit_credit AND cre_etat = $etat
            AND ad_dcr.id_ag = $global_id_agence AND NOT etat = 12
            ";

    $result = executeQuery($db, $sql, FALSE);
    if ($result->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      $msg_erreur .= sprintf(_("Problème à la mise à jour de l'état %s : %s.  "), $etat, $error[$result->errCode]);
      continue;
    }
    $montant_total = $result->param[0]["somme"];
    if ($montant_total != NULL && $montant_total != 0 && isset($cptes["num_cpte_comptable"])) {
      // Le montant à mouvementer n'est pas null, on passe les écritures comptables nécessaires
      $cptes_substitue["cpta"] = array ();
      $comptable = array ();
      $cptes_substitue["cpta"]["credit"] = $etat_credit["num_cpte_comptable"];
      $cptes_substitue["cpta"]["debit"] = $cptes["num_cpte_comptable"];
      // Opération 275 Transfert d'écritures entre comptes comptables d'état de crédit
      $result = passageEcrituresComptablesAuto(275, $montant_total, $comptable, $cptes_substitue, $produit['devise']);
      if ($result->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        $msg_erreur .= sprintf(_("Problème à la mise à jour de l'état %s : %s (%s).  "), $etat, $error[$result->errCode], $result->param);
        continue;
      }
      // Fonction 294 : modification d'une table de paramétrage
      $myErr = ajout_historique(294, NULL, $id_produit_credit, $global_nom_login, date("r"), $comptable, NULL, NULL);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        $msg_erreur .= sprintf(_("Problème à la mise à jour de l'état %s : %s.  "), $etat, $error[$result->errCode]);
        continue;
      }
    }

    // Création de la défintion du nouvel état
    if(($maj)&&($existDefEtat)){
    	$sql=buildUpdateQuery("adsys_etat_credit_cptes",$cptes,array("id_etat_credit"=>$etat,"id_prod_cre"=>$id_produit_credit,"id_ag"=>$global_id_agence));
    } else {
    	$nouvel_etat["num_cpte_comptable"] = $cptes["num_cpte_comptable"];
    	$nouvel_etat["id_etat_credit"] = $etat;
    	$nouvel_etat["cpte_provision_credit"] = $cptes["cpte_provision_credit"];
    	$nouvel_etat["cpte_provision_debit"] = $cptes["cpte_provision_debit"];
    	$nouvel_etat["cpte_reprise_prov"] = $cptes["cpte_reprise_prov"];
    	$nouvel_etat["id_ag"] = $global_id_agence;
    	$sql = buildInsertQuery("adsys_etat_credit_cptes", $nouvel_etat);
    }

    $result = executeQuery($db, $sql, FALSE);
    if ($result->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      $msg_erreur .= sprintf(_("Problème à la mise à jour de l'état %s : %s.  "), $etat, $error[$result->errCode]);
    } else {
      $dbHandler->closeConnection(true);
    }
  }
  if ($msg_erreur == "")
    return new ErrorObj(NO_ERR);
  else
    return new ErrorObj(ERR_MODIF_ETAT_CRE, $msg_erreur);
}

/**
 * Fonction renvoyant toutes les opérations diverses de caisse
 * @author Papa
 * @since 1.0.8
 * @return array => array(type_operation, libel_ope, categorie_ope )
 */
function getODC($a_selectCondition='categorie_ope = 2 or categorie_ope = 3') {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_cpt_ope where id_ag=$global_id_agence and $a_selectCondition";
  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $ODC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $ODC[$row["type_operation"]]=$row;
  return $ODC;

}

/**
 * Fonction ajout une opération diverse de caisse/compte
 * @author Papa
 * @since 1.0.8
 * @param $libelleODC le libellé de l'opération
 * @param $sensCpte sens du compte
 * @param $typeMouv type de mouvement (caisse ou compte)
 * @param $numCpteContrepartie le compte de contrepartie
 * @return Objet Error
 */
function creationODC($libelleODC,$sensCpte,$typeMouv,$numCpteContrepartie) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  //récupération du numéro de l'opération
  $sql = "SELECT nextval('ad_cpt_ope_seq'::text)";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $type_ope = $row[0];

  if ($typeMouv == 2) { // Ajout d'un compte de categorie caisse
    $catCpte = 4;
  } else if ($typeMouv == 3) {
    $catCpte = 2;
  } else { // pas possible
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  // Ajout du type opération diverse de caisse/compte
  if (is_champ_traduit('ad_cpt_ope','libel_ope')) {
    $libelleODC->save();
    $id_str_libel = $libelleODC->get_id_str();
    $sql = "INSERT INTO ad_cpt_ope(type_operation,id_ag,libel_ope,categorie_ope) VALUES($type_ope,$global_id_agence, $id_str_libel, $typeMouv)";
  }else{
  	$libelleODC = htmlspecialchars($libelleODC, ENT_QUOTES, "UTF-8");
  	$sql = "INSERT INTO ad_cpt_ope(type_operation,id_ag,libel_ope,categorie_ope) VALUES($type_ope,$global_id_agence, '$libelleODC', $typeMouv)";
  }
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $sql = "INSERT INTO ad_cpt_ope_cptes(type_operation,id_ag,num_cpte,sens,categorie_cpte) VALUES($type_ope,$global_id_agence,NULL,'$sensCpte',$catCpte)";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  // Sens du compte de contrepartie
  if ($sensCpte==SENS_DEBIT)
    $senscontrepartie=SENS_CREDIT;
  else
    $senscontrepartie=SENS_DEBIT;

  // Ajout du compte de contrepartie
  $sql = "INSERT INTO ad_cpt_ope_cptes(type_operation,id_ag,num_cpte,sens,categorie_cpte) VALUES($type_ope,$global_id_agence,'$numCpteContrepartie','$senscontrepartie',0)";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

/**
 * Fonction modificaton opération diverse de caisse
 * @author Papa
 * @since 1.0.8
 * @param $libelleODC le libellé de l'opération
 * @param $sensCpte sens du compte
 * @param $typeMouv type de mouvement (caisse ou compte)
 * @param $numCpteContrepartie le compte de crontrepartie
 * @return Objet Error
 */
function modificationODC($type_oper,$libelleODC,$sensCpte,$typeMouv,$numCpteContrepartie) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  if ($typeMouv == 2) { // Ajout d'un compte de categorie caisse
    $catCpte = 4;
  } else if ($typeMouv == 3) {
    $catCpte = 2;
  } else { // pas possible
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  // modification du type opération diverse de caisse et de catégorie opération (=type de mouvement) pour compte/caisse
  $sql_libel = '';
  if (is_champ_traduit('ad_cpt_ope','libel_ope')) {
    $libelleODC->save();
  }else{
  	$libelleODC = htmlspecialchars($libelleODC, ENT_QUOTES, "UTF-8");
  	$sql_libel = "libel_ope='$libelleODC', ";
  }
  $sql = "UPDATE ad_cpt_ope SET ".$sql_libel." categorie_ope='$typeMouv' WHERE id_ag=$global_id_agence AND type_operation=$type_oper";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  // modification du sens de la caisse
  $sql = "UPDATE ad_cpt_ope_cptes SET sens='$sensCpte', categorie_cpte=$catCpte WHERE id_ag = $global_id_agence AND type_operation=$type_oper AND categorie_cpte<>0";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  // Sens du compte de contrepartie
  if ($sensCpte==SENS_DEBIT)
    $senscontrepartie=SENS_CREDIT;
  else
    $senscontrepartie=SENS_DEBIT;

  // modificationt du compte de contrepartie
  $sql = "UPDATE ad_cpt_ope_cptes SET num_cpte='$numCpteContrepartie',sens='$senscontrepartie' WHERE id_ag=$global_id_agence AND type_operation=$type_oper AND categorie_cpte=0";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

/**
 * Fonction suppression opération diverse de caisse
 * @author Papa
 * @since 1.0.8
 * @param $type_oper le numéro de l'opération
 * @return Objet Error
 */
function suppressionODC($type_oper) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  // suppression du schema
  $sql = "DELETE FROM ad_cpt_ope_cptes WHERE id_ag = ".$global_id_agence." AND type_operation = ".$type_oper;

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  // suppression de l'opération
  $sql = "DELETE FROM ad_cpt_ope WHERE id_ag = ".$global_id_agence." AND type_operation = ".$type_oper;

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}


/**
 * Fonction renvoyant tous les libellés des écritures libres 
 * @author Ibou Ndiaye
 * @since 3.2.1
 * @return array => array(type_operation, libel_ope, categorie_ope )
 */
function getLEL($a_selectCondition='categorie_ope = 4') {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_cpt_ope where id_ag=$global_id_agence and $a_selectCondition";
  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $GEL = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $GEL[$row["type_operation"]]=$row;
  return $GEL;

}
/**
 * Fonction ajout un libellé d'une écriture libre
 * @author Ibou Ndiaye
 * @since 3.2.1
 * @param $libelleEL le libellé de l'écriture libre
 * @return Objet Error
 */
function creationLEL($libelleLEL) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  //récupération du numéro de l'opération
  $sql = "SELECT nextval('ad_cpt_ope_seq'::text)";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $type_ope = $row[0];
  $categorie_ope = 4;

  if (is_champ_traduit('ad_cpt_ope','libel_ope')) {
  	$libel_ope_trad = new Trad();
  	$libel_ope_trad = $libelleLEL;
  	$libel_ope_trad->save();
    $libel_ope = $libel_ope_trad->get_id_str();
  }else{
  	$libel_ope = htmlspecialchars($libelleLEL, ENT_QUOTES, "UTF-8");
  }
  // Ajout du libellé d'écriture libre
  $sql = "INSERT INTO ad_cpt_ope(type_operation,id_ag,libel_ope,categorie_ope) VALUES($type_ope,$global_id_agence, '$libel_ope', $categorie_ope)";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}
/**
 * Fonction modificaton opération diverse de caisse
 * @author Ibou Ndiaye
 * @since 3.2.1
 * @param $type_oper type de l'opération
 * @param $libelleLEL le libellé de l'opération
 * @return Objet Error
 */
function modificationLEL($type_oper,$libelleLEL) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  if (is_champ_traduit('ad_cpt_ope','libel_ope')) {
  	$libel_ope_trad = new Trad();
  	$libel_ope_trad = $libelleLEL;
  	$libel_ope_trad->save();
    $libel_ope = $libel_ope_trad->get_id_str();
  }else{
  	$libel_ope = htmlspecialchars($libelleLEL, ENT_QUOTES, "UTF-8");
  }
  // modification du libellé de l'écriture libre
  $sql = "UPDATE ad_cpt_ope SET libel_ope='$libel_ope'  WHERE id_ag=$global_id_agence AND type_operation=$type_oper";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

/**
 * Fonction suppression libellé écriture libre
 * @author Ibou Ndiaye
 * @since 3.2.1
 * @param $type_oper le numéro de l'opération
 * @return Objet Error
 */
function suppressionLEL($type_oper) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  
  // suppression du libellé
  $sql = "DELETE FROM ad_cpt_ope WHERE id_ag = ".$global_id_agence." AND type_operation = ".$type_oper;

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

/**
 * Fonction qui passe une écriture entre le siège et une agence
 * @author Papa
 * @since 2.9
 * @param $num_op int : le numéro de l'opération
 * @param $mnt_op real : le montant de l'opération
 * @param $agence int : le numéro de l'agence concernée dans l'opération avec le siège
 * @return Object Error
 */
function passageOperationSiegeAgence($num_op, $date_op, $mnt_op, $devise, $agence) {
  global $dbHandler, $global_nom_login, $global_id_agence;
  $db = $dbHandler->openConnection();

  $comptable = array();
  $cptes_substitue["cpta"] = array();

	$myErr = passageEcrituresComptablesAuto($num_op, $mnt_op, $comptable, $cptes_substitue, $devise, $date_op);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $myErr = ajout_historique(473, $agence, $num_op, $global_nom_login, date("r"), $comptable, NULL, NULL);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}
/**
 *Fonction qui permet de supprimer un compte comptable
 *@since 3.1 - 26 janv. 09
 *@param text $a_cpteSupprimer compte a supprimer
 *@param array $a_cpteVentiller donnée des comptes permettant de ventiller le solde du compte à supprimer
 */
function supprimerCompte($a_cpteSupprimer,$a_cpteVentiller,$a_soldeVentiller) {
	global $global_id_agence;
	global $global_nom_login;
	global $dbHandler;
	$db = $dbHandler->openConnection();
	$comptable = array();
	//Recupèration des infos du compte centralisateur
  $param["num_cpte_comptable"]=$a_cpteSupprimer;
  $infocptecentralise = getComptesComptables($param);
  $solde_total=0;//la somme des soldes des comptes sur lesquels on va ventiller le solde à supprimer
	foreach ($a_cpteVentiller as $key=>$value){
		// Vérifier que les comptes sur lequel on va ventiller le solde existe dans la DB
	  $sql = "SELECT * FROM ad_cpt_comptable WHERE id_ag=$global_id_agence and num_cpte_comptable='$key';";
	  $param["num_cpte_comptable"]=$key;
	  $cptetransfere= getComptesComptables($param);
	  if ($cptetransfere == NULL) {
	  	$dbHandler->closeConnection(false);

	    return new ErrorObj(ERR_CPT_EXIST, $key);
	  }

	   // verifier si le compte de destination à la même devise que les compte à supprimer
	   if ($infocptecentralise[$a_cpteSupprimer]["devise"] != NULL && $infocptecentralise[$a_cpteSupprimer]["devise"] != $cptetransfere[$key]["devise"]) {
	        $dbHandler->closeConnection(false);
	        return new ErrorObj(ERR_DEV_DIFF_CPT_CENTR, $cptetransfere[$key]["devise"]);
	   }
		if ( abs($value["solde"]) != 0 ) {
			// Passage des écritures comptables

			$cptes_substitue = array();
			$cptes_substitue["cpta"] = array();

			if ($a_soldeVentiller < 0 ) {
			//crédit du compte à supprimer par le débit d'un compte de ventillation
			$cptes_substitue["cpta"]["debit"] = $key;
			$cptes_substitue["cpta"]["credit"] = $a_cpteSupprimer;
			} else {
			//débit d'un compte de ventillation par le credit du compte à supprimer
			$cptes_substitue["cpta"]["debit"] = $a_cpteSupprimer;
			$cptes_substitue["cpta"]["credit"] = $key;

			}

			$myErr = passageEcrituresComptablesAuto(1005, abs($value["solde"]), $comptable, $cptes_substitue, $value["devise"],NULL,$a_cpteSupprimer);
			if ($myErr->errCode != NO_ERR) {
			$dbHandler->closeConnection(false);
			return $myErr;
			}

		}
		$solde_total+=$value["solde"];

	}
	//comparaison entre la sommme des soldes et le solde du compte à supprimer
	if( $a_soldeVentiller > 0 && $solde_total !=$a_soldeVentiller ) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_SOLDES_DIFFERENTS ,$a_cpteSupprimer );
	}
	 $myErr=updateEtatCpt($a_cpteSupprimer);
	 if ($myErr->errCode != NO_ERR) {
				$dbHandler->closeConnection(false);
			  return $myErr;
	 }
	 $erreur=ajout_historique(477, NULL, _("Suppression compte"), $global_nom_login, date("r"), $comptable);
	 if ($erreur->errCode != NO_ERR) {
				$dbHandler->closeConnection(false);
			  return $erreur;
	 }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

/**
 * Fonction permettant de faire la provision et la reprise sur la provision, des crédits en souffrances
 */
function provisionCredit($tabcredit = NULL, $date_compta_prov = NULL, $type_produit = null)
{
	global $dbHandler, $global_id_agence, $global_nom_login, $global_monnaie;

    $db = $dbHandler->openConnection();
	$comptable=array();
    set_time_limit(0);

  //provision et reprise sur provision selon les cas
	if (is_null($tabcredit)){
      if (!empty($type_produit)){
        $etat_credits = getDossiersProvisionData($type_produit, null, null, null, $date_compta_prov, null, true);
      }
    else {
      $etat_credits = getDossiersProvisionData(null, null, null, null, $date_compta_prov, null, true);
    }

      $Data = array();

      if(count($etat_credits)>0)
        {
        foreach($etat_credits as $id_doss=>$val_doss) {
          $Data[$id_doss]['id_doss']=$etat_credits[$id_doss]['id_doss'];
          $Data[$id_doss]["id_prod"]=$etat_credits[$id_doss]["id_prod"];
          $Data[$id_doss]['prov_mnt_new']=recupMontant($etat_credits[$id_doss]['additional_provisions']);
          $Data[$id_doss]['prov_mnt']=$etat_credits[$id_doss]['prov_mnt'];
          $Data[$id_doss]['cre_etat']=$etat_credits[$id_doss]['id_etat_credit'];
          $Data[$id_doss]['devise']=$etat_credits[$id_doss]['devise'];
          $Data[$id_doss]['id_client']=$etat_credits[$id_doss]['id_client'];
          $Data[$id_doss]['taux_prov']=$etat_credits[$id_doss]['taux_prov'];

          $dotation = true;
          if (recupMontant( $etat_credits[$id_doss]['additional_provisions'] ) < 0 ){
            $dotation = false;
          }
          $Data[$id_doss]['dotation']=$dotation;
        }

        $myErr2 = provisionTabCreditsSouffrances($Data, $comptable, $date_compta_prov);
      }

	}
	elseif (count($tabcredit)>0) {
      $myErr2 = provisionTabCreditsSouffrances($tabcredit, $comptable, $date_compta_prov);
	}
	if ($myErr2->errCode != NO_ERR ) {
		$dbHandler->closeConnection(false);
		return $myErr2;
	}

    $list_id_provisionne = $myErr2->param['list_id_provisionne'];

    $myErr = ajout_historique(432,NULL, NULL, $global_nom_login,  date("r"), $comptable);

    $id_his = $myErr->param;

	if ($myErr->errCode != NO_ERR ) {
		$dbHandler->closeConnection(false);
		return $myErr;
	}

	$myErr2->param["nbre_prov_reprise"];

    // Update id_his of provision
    if(count($list_id_provisionne) > 0) {
      foreach($list_id_provisionne as $id_provision) {
        $DATA['id_his'] = $id_his;
        insertOrUpdateProvision($DATA, $id_provision);
      }
    }

    $dbHandler->CloseConnection(true);
	return new ErrorObj(NO_ERR,$myErr2->param);
}

/**
 * Retourne le montant a provisionner pour un dossier de credit en perte :
 * Montant provisionné =
 * [Encours restant dû - Dépôts de garanties (garanties constituées au début + garanties constituée au fur et à mesure) ]
 * taux de provision pour la classe – provisions_antérieures
 *
 * @param $id_doss
 * @param null $id_cre_etat
 * @param null $taux_prov
 * @param null $date_export
 * @return int $mnt_provision
 */
function calculprovision($id_doss, $id_cre_etat=null, $taux_prov=null, $date_export=null, $capital_restant_du=null, $solde_gar=null, $previous_provisions=null)
{
  global $dbHandler;
  $db = $dbHandler->openConnection();

  // les infos du dossier
  $dossier_infos = getDossierCrdtInfo($id_doss);

  if(empty($date_export))
    $date_export = php2pg(date("d/m/Y"));

  if(empty($previous_provisions)) {
    $previous_provisions = floatval($dossier_infos['prov_mnt']);
  }

  if($previous_provisions < 0) {
    $previous_provisions = 0;
  }

  // recupere la classe de retard du credit
  if(empty($id_cre_etat)) {
    $id_cre_etat = $dossier_infos['cre_etat'];
  }

  //recuperer tous les etat des crédits
  $EtatCredits = getTousEtatCredit();
  // Le taux de provision
  if(empty($taux_prov)) {
    $taux_prov =  $EtatCredits[$id_cre_etat]["taux"];
  }

  /* Récupération du capital dû */
  if ($dossier_infos['is_ligne_credit'] == 't') {
    $capital_restant_du = getCapitalRestantDuLcr($dossier_infos['id_doss'], $date_export);
  } else if(empty($capital_restant_du)) {
    $capital_restant_du = getSoldeCapital($id_doss, $date_export);
  }

  /* Si c'est une garantie numéraire mobilisée : diminuer le capital restant dû de la garantie nantie */
  /* Récupération des infos sur les garantie du crédit */
  if(empty($solde_gar))
    $solde_gar = getSoldeGarNumeraires($id_doss);

  /*
   * Montant provisionné =
   * {[Encours restant dû - Dépôts de garanties (garanties constituées au début + garanties constituée au fur et à mesure) ]
   *  x taux de provision pour la classe} – provisions_antérieures.
   */
  $provisions_required = (($capital_restant_du - $solde_gar) * $taux_prov) > 0 ? (($capital_restant_du - $solde_gar) * $taux_prov) : 0;
  $provisions_required = floatval($provisions_required);

  $provisions_required = arrondiMonnaiePrecision($provisions_required);
  $previous_provisions = arrondiMonnaiePrecision($previous_provisions);

  $additional_provisions = floatval($provisions_required - $previous_provisions);

  $prov_data['id_doss'] = $id_doss;
  $prov_data['taux'] = $taux_prov;
  $prov_data['provisions_required'] = $provisions_required;
  $prov_data['previous_provisions'] = $previous_provisions;
  $prov_data['additional_provisions'] = arrondiMonnaiePrecision($additional_provisions);
  $prov_data['gar_numeraires'] = $solde_gar;

  $dbHandler->closeConnection(true);
  return $prov_data;
}


/**
 * Fonction permettant de faire la reprise sur provision des crédits qui étaient en souffrances ou qui sont en pertes
 */
function repriseProvisionCredit (&$comptable, $date_compta_prov = null) {
	global $dbHandler,$global_id_agence,$global_monnaie;
	global $global_nom_login;

	$db = $dbHandler->openConnection();
	//recupérer les crédits qui ont été provisionnés , qu'ils soient soldés, en pertes, deboursés ou réechelonés et n'appartenant plus a un etat de risque
    $sql = "SELECT d.id_doss, d.id_client, d.etat, d.prov_mnt, d.cre_etat, d.id_prod, d.devise FROM get_ad_dcr_ext_credit
(null, null, null, null, $global_id_agence) d WHERE (d.etat=6 OR d.etat=9 OR ((d.etat=5 OR d.etat=7 OR d.etat=14 OR d.etat=15) AND d.cre_etat IN (SELECT id FROM adsys_etat_credits WHERE provisionne=false) ) ) AND d.prov_is_calcul=true AND d.prov_mnt >0 ";

	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->CloseConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL : ").$sql);
	}
	$nbre_prov_reprise=0;

	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
		$solde_provisionne = $row["prov_mnt"];
		$solde_prov_new = 0;

		$fields['prov_mnt'] = $solde_prov_new; // set le montant prov a zero pour une reprise

		if ( $row['etat']==9 || $row['etat'] == 6 ){ // radié ou soldé, on supprime le flag calculé
			$fields['prov_is_calcul']='false';
		}

        $infosProvision = getlastProvision($row['id_doss']);

        if($infosProvision[$row['id_doss']]['montant'] > 0 ) { //reprise sur provisiion
               // sauvegarde l'ancienne reprise
               //insereAncienneRepriseProvision($row['id_doss']);
        }

		// reprise de la provision
		$err_solde_prov = passeEcritRepriseProvision($row['id_doss'],$solde_provisionne,$row["prov_mnt"], $row['id_prod'],$row['cre_etat'],$row['devise'],$comptable, $date_compta_prov);

        if ($err_solde_prov->errCode != NO_ERR ) {
			$dbHandler->closeConnection(false);
			return $err_solde_prov;
		}

		//mise a jour du dossier de crédit
		$fields['prov_date']=date("d/m/Y H:i:s.u"); // date du jour
		updateCredit($row["id_doss"], $fields);

        $nbre_prov_reprise++;

	}//fin while

	$dbHandler->CloseConnection(true);
	return new ErrorObj(NO_ERR,array("nbre_prov_reprise"=>$nbre_prov_reprise));
}

// Sauvegarde l'ancienne reprise dans la table ad_provision
function insereAncienneRepriseProvision($id_doss) {
	/*
    global $dbHandler, $global_id_agence;

	$db = $dbHandler->openConnection();

	// recuperer la derniere ecriture d'un reprise sur provision
	$sql = "SELECT * FROM ad_ecriture ";
	$sql .= " WHERE id_ag=$global_id_agence AND type_operation=272 AND info_ecriture LIKE '$id_doss' ";
	$sql .= " ORDER BY id_ecriture DESC LIMIT 1;";

	$result = $db->query($sql);
	if (DB::isError($result)) {
            $dbHandler->CloseConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requete SQL :")." ".$sql);
	}

        $row = $result->fetchrow(DB_FETCHMODE_ASSOC);

        if(is_array($row) && count($row)>0) {

            //recuperer tous les etat des credits
            $EtatCredits = getTousEtatCredit();

            $dataProv = array();
            $dataProv['id_doss'] = $id_doss;
            $dataProv['montant'] = recupMntEcriture($row['id_ecriture']);
            $cre_etat = calculEtatCredit($id_doss, $row['date_comptable']);
            $dataProv['taux'] = $EtatCredits[$cre_etat]["taux"];
            $dataProv['id_cred_etat'] = $cre_etat;
            $dataProv['date_prov'] = date("d/m/Y"); // date du jour
            $dataProv['reprise_prov'] = 't';

            insertionProvision($dataProv);
        }

        $dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR);
	*/
}

// Recupere le montant d'une ecriture
function recupMntEcriture($id_ecriture) {
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "SELECT montant FROM ad_mouvement WHERE id_ag=$global_id_agence AND sens='c' AND id_ecriture=$id_ecriture ORDER BY id_mouvement DESC LIMIT 1";
    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $tmprow = $result->fetchrow();

    return $tmprow[0];
}

// Calcul l'etat d'un credit a une date 
function calculEtatCredit($id_doss, $date_etat) {
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "SELECT CalculEtatCredit($id_doss, '$date_etat', $global_id_agence);";
    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $tmprow = $result->fetchrow();

    return $tmprow[0];
}

/**
 *
 * Fonction permettant de faire la provision des crédits en souffrances.
 *
 */
function provisionCreditsSouffrances_old(&$comptable, $date_comptable = NULL)
{
  global $dbHandler, $global_id_agence, $global_monnaie;
  global $global_nom_login;

  $db = $dbHandler->openConnection();
  // recuperer tous les credit appartenant a l'etat de souffrances
  $sql = "SELECT ad_dcr.* ,adsys_produit_credit.devise FROM ad_dcr,adsys_produit_credit  ";
  $sql .= " WHERE   id_prod = id  AND ad_dcr.id_ag=adsys_produit_credit.id_ag AND ad_dcr.id_ag=$global_id_agence  ";
  $sql .= " AND (etat=5 OR etat=7 OR etat=14 OR etat=15) AND cre_etat IN (select id from adsys_etat_credits where provisionne=true and nbre_jours != -1) ";
  $sql .= "  AND prov_is_calcul=true  order by id_doss";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->CloseConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL :") . " " . $sql);
  }

  //recuperer tous les etat des crédits
  $EtatCredits = getTousEtatCredit();
  $nbre_prov_reprise = 0;
  $nbre_prov = 0;

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

    // calcul du mnt de la provision
    $mnt_provision = calculprovision($row['id_doss'], $row["cre_etat"], $EtatCredits[$row["cre_etat"]]["taux"]);

    $cptes = getAllCompteEtatCredit($row["id_prod"]);

    $err_solde_prov = NULL;
    $id_doss = $row['id_doss'];

    // FixMe si montant calculé de la provision est negatif ( il ya soufisament de la garantie pour couvrir le risque, montant de la garantie doit etre à zero')
    if ($mnt_provision < 0) $mnt_provision = 0;

    //calcul de la difference entre la nouvelle et ancienne provision
    $diff_prov = $mnt_provision - $row["prov_mnt"];//nouvelle -ancienne provision
    $diff_prov = round($diff_prov, EPSILON_PRECISION);

    if ($diff_prov != 0)
    {
      $infosProvision = getlastProvision($row['id_doss']);

      if ($infosProvision[$id_doss]['montant'] > 0) { //reprise sur provisiion
        // sauvegarde l'ancienne reprise
        //insereAncienneRepriseProvision($id_doss);

        // reprise de la provision
        $err_solde_prov = passeEcritRepriseProvision($row['id_doss'], $infosProvision[$id_doss]['montant'], $row["prov_mnt"], $row['id_prod'], $row['cre_etat'], $row['devise'], $comptable, $date_comptable);
        if ($err_solde_prov->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $err_solde_prov;
        }
        $nbre_prov_reprise++;
      }
      // (provisionné)
      $err_solde_prov = passeEcritProvision($row['id_doss'], $mnt_provision, $row["prov_mnt"], $row['id_prod'], $row['cre_etat'], $row['devise'], $comptable, $date_comptable);

      if ($err_solde_prov->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $err_solde_prov;
      }
      $nbre_prov++;
      if (isset ($err_solde_prov->param['solde_prov_new'])) {
        //mise a jour du dossier de crédit
        $Fields['prov_date'] = date("d/m/Y H:i:s.u"); // date du jour
        $Fields["prov_mnt"] = $mnt_provision;
        $dataProv['id_doss'] = $row['id_doss'];
        $dataProv['montant'] = $Fields["prov_mnt"];
        $dataProv['taux'] = $EtatCredits[$row["cre_etat"]]["taux"];
        $dataProv['id_cred_etat'] = $row['cre_etat'];
        $dataProv['date_prov'] = $Fields['prov_date'];
        insertOrUpdateProvision($dataProv);
        updateCredit($row["id_doss"], $Fields);
      }
    }


  }//fin while

  $dbHandler->CloseConnection(true);
  return new ErrorObj(NO_ERR, array("nbre_prov" => $nbre_prov, "nbre_prov_reprise" => $nbre_prov_reprise));
}

/**
 * Fonction qui permet de faire la provision des crédits en souffrances /Modification des provisions
 * param array $tabcredit liste des crédits à provisionner ou modifier la provision
 *              $tabcredit[$id_doss]['id_doss']       id dossier de crédit
 * $tabcredit[$id_doss]["id_prod"]       id produit de crédit
 * $tabcredit[$id_doss]['prov_mnt_new']   nouvelle valeur de la provision de crédit
 * $tabcredit[$id_doss]['prov_mnt']       ancienne valeur de la provision
 * $tabcredit[$id_doss]['cre_etat']       ID etat de crédit
 * $tabcredit[$id_doss]['devise']         devise du produit de crédit
 * $tabcredit[$id_doss]['prov_is_calcul'] flag ,vrai si on doit provisionner le dossier de crédit
 * */

function  provisionTabCreditsSouffrances($tabcredit, &$comptable, $date_compta_prov=null)
{
  global $dbHandler, $global_id_agence, $global_monnaie, $appli, $date_total;

  $nbre_prov_reprise = 0;
  $nbre_prov = 0;
  $list_id_provisionne = array();

  //recuperer tous les etat des crédits
  $EtatCredits = getTousEtatCredit();
  $etat_non_prov = getEtatCreditNonProv();

  foreach ($tabcredit as $id_doss => $row)
  {
    // garde le montant saisi de la provision
    $mnt_provision = $row['prov_mnt_new'];
    $err_solde_prov = NULL;
    $err_solde_repr = NULL;
    $Fields = array();
    $is_prov = 1;
    $prov_mnt_anc_etat = arrondiMonnaiePrecision($row["prov_mnt"]);
    $mnt_provision = arrondiMonnaiePrecision($mnt_provision);

    // si c'est une dotation:
    if ($row["dotation"]==true){

      /**
       * Provision courant :
       * Debit : Compte de dotation [le champ compte au debit] du cre_etat du dossier au mmt de la provision
       * Credit : Compte provision [le champ compte au credit] du cre_etat du dossier au mmt de la provision
       */

      $infosLastProvision = getlastProvision($id_doss);
      $id_last_etat_prov =$infosLastProvision[$id_doss]["id_cred_etat"];

        //Si il y a eu déclassement du crédit avec des provision antèrieurs
        if(($prov_mnt_anc_etat > 0) && ($id_last_etat_prov !=  $row['cre_etat']) && ($row['cre_etat'] != 7) ){


          $prov_mnt_nov_etat = arrondiMonnaiePrecision($mnt_provision);

          /* - prends le montant de la provision antèrieure de l'ancien etat du credit
             - provision partielle avec le montant montant de la provision antèrieure
             - Faire une dotation avec le reste du montant exigé.
          */

          $err_solde_prov_dcl = passeEcritProvisionDeclassementCred($row['id_doss'], $prov_mnt_anc_etat, $id_last_etat_prov, $row['id_prod'], $row['cre_etat'], $row['devise'], $comptable, $date_compta_prov);

          if ($err_solde_prov_dcl->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $err_solde_prov_dcl;
          }

          $err_solde_prov = passeEcritProvision($row['id_doss'], $prov_mnt_nov_etat, $row["prov_mnt"], $row['id_prod'], $row['cre_etat'], $row['devise'], $comptable, $date_compta_prov);

          $dataProv['montant_dotation'] = $prov_mnt_nov_etat;
          $dataProv['montant_repris'] = NULL;

          if ($err_solde_prov->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $err_solde_prov;
          }
          $nbre_prov++;

        }
        else {

          $err_solde_prov = passeEcritProvision($row['id_doss'], $mnt_provision, $row["prov_mnt"], $row['id_prod'], $row['cre_etat'], $row['devise'], $comptable, $date_compta_prov);

          $dataProv['montant_dotation'] = $mnt_provision;
          $dataProv['montant_repris'] = NULL;

          if ($err_solde_prov->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $err_solde_prov;
          }
          $nbre_prov++;
        }


    }
    //si c'est un reprise:
    else
    {

       /**
         * Reprise du precedent provision :
         * Debit : Compte provision [le champ compte au credit] du cre_etat du dossier au mmt de la provision
         * Credit : compte reprise du cre_etat du dossier au mmt de la provision
         */

        // prendre la valeur absolue du montant de la reprise:
	  $mnt_provision_abs = abs($mnt_provision);

      $infosLastProvision = getlastProvision($id_doss);
      $id_last_etat_prov = $infosLastProvision[$id_doss]["id_cred_etat"];

      $prov_mnt_nov_etat = arrondiMonnaiePrecision($mnt_provision_abs);

      $mnt_rep_diff = $prov_mnt_anc_etat - $prov_mnt_nov_etat;

        //Si il y a eu réclassement du crédit avec des provision antèrieurs sur l'ancien etat
        if( !in_array($row['cre_etat'],$etat_non_prov) && ($prov_mnt_anc_etat > 0) && ($id_last_etat_prov !=  $row['cre_etat'])){

            $dataProv['montant_dotation'] = NULL;
            $dataProv['montant_repris'] =  $prov_mnt_nov_etat;

          /* - prends le montant de la provision antèrieure de l'ancien etat du credit
            - provision partielle avec le montant montant de la provision antèrieure
            - Faire une reprise avec le reste du montant exigé.
          */

          $err_solde_recl = passeEcritProvisionDeclassementCred($row['id_doss'], $mnt_rep_diff, $id_last_etat_prov, $row['id_prod'], $row['cre_etat'], $row['devise'], $comptable, $date_compta_prov);

          if ($err_solde_recl->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $err_solde_recl;
          }

          $err_solde_repr = passeEcritRepriseProvision($row['id_doss'], $prov_mnt_nov_etat, $row["prov_mnt"], $row['id_prod'], $row['cre_etat'], $row['devise'], $comptable, $date_compta_prov);

          if ($err_solde_repr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $err_solde_repr;
          }
          $nbre_prov_reprise++;


        }
        else{ // Pas de declassement : Reprise totale

          $err_solde_repr = passeEcritRepriseProvision($row['id_doss'], $prov_mnt_nov_etat, $row["prov_mnt"], $row['id_prod'], $row['cre_etat'], $row['devise'], $comptable, $date_compta_prov);

          if ($err_solde_repr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $err_solde_repr;
          }
          $nbre_prov_reprise++;

        if (($mnt_provision + arrondiMonnaiePrecision($row["prov_mnt"])) == 0){

          $is_prov = 0;
        }

        $dataProv['montant_dotation'] = NULL;
        $dataProv['montant_repris'] = $prov_mnt_nov_etat;
        }
      }

      $date_compta_enregistre = $date_compta_prov;

      if(empty($date_compta_prov) && $appli = 'batch') {
        $date_compta_enregistre = getValideDateComptaProvForBatch(null);
      }
      elseif(empty($date_compta_prov) && $appli = 'main') {
        $today =   date("d/m/Y H:i:s.u");
        $date_compta_enregistre = hier($today); //hier
      }

      // nouveau montant lors de la provision
      if (isset($err_solde_prov->param['solde_prov_new'])) {
        //mise a jour du dossier de crédit
        $Fields['prov_date'] = $date_compta_enregistre; // date du jour
        $Fields["prov_mnt"] = $err_solde_prov->param['solde_prov_new'] + $prov_mnt_anc_etat; // stock le montant du provision / dotation courant

      }

    // nouveau montant lors de la reprise
      if (isset($err_solde_repr->param['solde_prov_new'])) {
        //mise a jour du dossier de crédit
        $Fields['prov_date'] = $date_compta_enregistre;
        $Fields["prov_mnt"] = $mnt_provision  + $prov_mnt_anc_etat ; // stock le montant de la reprise / reprise courant

      }
      if (isset($row['prov_is_calcul'])) {
        $Fields['prov_is_calcul'] = $is_prov;
      }

      if (count($Fields) > 0) { //mise a jour du dossier de crédit
        $dataProv['id_doss'] = $row['id_doss'];
        $dataProv['montant'] = $Fields["prov_mnt"];//$row["prov_mnt"];
        //$dataProv['taux'] = $EtatCredits[$row["cre_etat"]]["taux"];
        $dataProv['taux'] = $row['taux_prov'];
        $dataProv['id_cred_etat'] = $row['cre_etat'];
        $dataProv['date_prov'] = $Fields['prov_date'];

        $row["dotation"]?$dataProv['is_repris']=0:$dataProv['is_repris']=1;

        // Inserer la nouvelle provision
        insertOrUpdateProvision($dataProv);
        $infosLastProvision = getlastProvision($row["id_doss"]);
        $id_provision = $infosLastProvision[$row['id_doss']]['id_provision'];
        $list_id_provisionne[] = $id_provision;

        // m-a-j du dossier avec les nouveaux montants des provisions
        updateCredit($row["id_doss"], $Fields);
      }

  }//fin foreach

  return new ErrorObj(NO_ERR, array("nbre_prov" => $nbre_prov, "nbre_prov_reprise" => $nbre_prov_reprise, "list_id_provisionne" => $list_id_provisionne));
}

/**
 * Fonction qui permet de passer les écritures comptables lors de la provision d'un dossier de crédit
 * param integer  $id_doss              ID dossier de crédit
 * param double   $mnt_provision        Nouveau montant calculer pour la provision
 * param double   $mnt_provision_ancien ancien Montant de la provision
 * param integer  $id_prod              ID produit de crédit
 * param integer  $cre_etat             ID état de crédit
 * param integer  $devise               devise
 * param array    $comptable            tableau des ecritures comptables
 * return double ErrorObj
 *                       array("solde_prov_new"=>$solde_prov_new) nouveau montant de la provision
 */

function passeEcritProvision($id_doss,$mnt_provision,$mnt_provision_ancien, $id_prod,$cre_etat,$devise, &$comptable, $date_compta_prov=null)
{
    global $dbHandler, $appli;

	global $global_monnaie;
	//type opération
	$type_oper=271;
	// declassement (provisionné)
	
	$cptes=getAllCompteEtatCredit($id_prod);
	$Fields['prov_mnt']=$mnt_provision;
	$solde_provisionne = $mnt_provision;
	$solde_prov_new=$mnt_provision;

	if($mnt_provision != 0 )
    {
		//compte au debit pr la provision -> c'est le compte de dotation !
		$cptes_substitue["cpta"]["debit"] =$cptes[$cre_etat]["cpte_provision_debit"];
		if ($cptes_substitue["cpta"]["debit"] == NULL) {
			$where=" WHERE id=".$id_prod." ";
			$produit=getProdInfo($where, $id_doss);
			//recuperer tous les etat des crédits
			$EtatCredits=getTousEtatCredit();
			return new ErrorObj(ERR_CPTE_NON_PARAM,sprintf( _("compte provision au débit associé à l'état %s du  produit de crédit %s"),$EtatCredits[$cre_etat]['libel'],$produit[0]['libel'])." ");
		}
		//compte au credit pr la provision -> c'est le compte de provision !
		$cptes_substitue["cpta"]["credit"] =$cptes[$cre_etat]["cpte_provision_credit"];
		if ($cptes_substitue["cpta"]["credit"] == NULL) {
			$where=" WHERE id=".$id_prod." ";
			$produit=getProdInfo($where, $id_doss);
			//recuperer tous les etat des crédits
			$EtatCredits=getTousEtatCredit();
	
			return new ErrorObj(ERR_CPTE_NON_PARAM,sprintf( _("compte provision  au crédit associé à l'état %s du  produit de crédit %s - %s"),$EtatCredits[$cre_etat]['libel'],$produit[0]['libel'])." ");
		}

      $infos_sup = array();
        if($appli == 'main') {
          $allowed_dates = getAllowedDatesForBackdateProvision($date_compta_prov);
          $infos_sup['date_debut'] = $allowed_dates['allowed_date_deb'];
          $infos_sup['date_fin'] = $allowed_dates['allowed_date_fin'];
          $infos_sup['id_exo'] = $allowed_dates['id_exo'];
        }
      
        // Le montant de l'ecriture est le nouveau montant saisi pour la provision
        $err = effectueChangePrivate($global_monnaie, $devise, $solde_provisionne, $type_oper, $cptes_substitue, $comptable, false,NULL,$id_doss, $infos_sup, $date_compta_prov);

      
        if ($err->errCode != NO_ERR ) {
			return $err;
		}
	}

	return new ErrorObj(NO_ERR,array("solde_prov_new"=>$solde_prov_new));
}

/**
 * Fonction qui permet de passer les écritures comptables lors de la reprise de la provision d'un dossier de crédit
 * param integer  $id_doss              ID dossier de crédit
 * param double   $mnt_provision        Nouveau montant calculer pour la provision
 * param double   $mnt_provision_ancien ancien Montant de la provision
 * param integer  $id_prod              ID produit de crédit
 * param integer  $cre_etat             ID état de crédit
 * param integer  $devise               devise
 * param array    $comptable            tableau des ecritures comptables
 * return double ErrorObj
 *                       array("solde_prov_new"=>$solde_prov_new) nouveau montant de la provision
 */

function passeEcritRepriseProvision($id_doss,$mnt_provision,$mnt_provision_ancien, $id_prod, $cre_etat, $devise, &$comptable, $date_compta_prov=null)
{
    global $appli;
	global $global_monnaie;
	$type_oper=272;
	// reprise de la provision
	$solde_provisionne=$mnt_provision;
	$solde_prov_new=0;
	$cptes=getAllCompteEtatCredit($id_prod);
	//$last_ecritureProvision =getlasEcritureProvision($id_doss);
	$infosLastProvision = getlastProvision($id_doss);

	$id_last_etat_provisionne = $infosLastProvision[$id_doss]['id_cred_etat'];
	//compte au debit pr la reprise sur provision
	//$cptes_substitue["cpta"]["debit"] = $cptes[$cre_etat]["cpte_provision_credit"];

    // //compte au debit pr la provision -> c'est le compte de provision !
	$cptes_substitue["cpta"]["debit"] = $cptes[$id_last_etat_provisionne]["cpte_provision_credit"];//$last_ecritureProvision['c']['compte'];

	if ($cptes_substitue["cpta"]["debit"] == NULL) {
		$where=" WHERE id=".$id_prod." ";
		$produit=getProdInfo($where, $id_doss);
		//recuperer tous les etat des crédits
		$EtatCredits=getTousEtatCredit();
		return new ErrorObj(ERR_CPTE_NON_PARAM,sprintf( _("compte sur provision au crédit associé à l'état %s du  produit de crédit %s"),$EtatCredits[$infosLastProvision[$id_doss]['id_cred_etat']]['libel'],$produit[0]['libel'])." ");
	}
	//compte au crédit pr la reprise sur provison
	
	$cptes_substitue["cpta"]["credit"] = $cptes[$id_last_etat_provisionne]["cpte_reprise_prov"];
	if ($cptes_substitue["cpta"]["credit"] == NULL) {
		$where=" WHERE id=".$id_prod." ";
		$produit=getProdInfo($where, $id_doss);
		//recuperer tous les etat des crédits
		$EtatCredits=getTousEtatCredit();
		return new ErrorObj(ERR_CPTE_NON_PARAM,sprintf( _("compte reprise sur provision associé à l'état %s du  produit de crédit %s"),$EtatCredits[$id_last_etat_provisionne]['libel'],$produit[0]['libel'])." ");
	}


    $infos_sup = null;

    // Si on n'est pas dans le batch
    if($appli == 'main') {
      $cre_etat = intval($cre_etat);
      $converted_cre_etat = (string)$cre_etat; //AT-68 : pour comparaison cre etat proprement dit

      $etatARadier = getIDEtatARadier();
      $etatPerte = getIDEtatPerte();
      $etats = array($etatPerte);

      if(!is_null($etatARadier)) {
        $etats[] = $etatARadier->param;
      }

      if(!empty($cre_etat) && !in_array($converted_cre_etat, $etats, true)) {
        $infos_sup = array();
        $allowed_dates = getAllowedDatesForBackdateProvision($date_compta_prov);
        $infos_sup['date_debut'] = $allowed_dates['allowed_date_deb'];
        $infos_sup['date_fin'] = $allowed_dates['allowed_date_fin'];
        $infos_sup['id_exo'] = $allowed_dates['id_exo'];
      }
    }

  // Le montant de l'ecriture est le nouveau montant saisi pour la provision
  $err = effectueChangePrivate($devise, $global_monnaie, $solde_provisionne, $type_oper, $cptes_substitue, $comptable, true,NULL,$id_doss, $infos_sup, $date_compta_prov);
  // $err = effectueChangePrivate( $devise,$global_monnaie, $solde_provisionne, $type_oper, $cptes_substitue, $comptable, true,NULL,$id_doss);

  if ($err->errCode != NO_ERR ) {
      return $err;
  }

  return new ErrorObj(NO_ERR,array("solde_prov_new"=>$solde_provisionne));
}


/**
 * Fonction permettant de renvoyer la liste des credits en souffrances à provisionner
 * @param null $limit
 * @param null $offset
 * @param null $cre_etat
 * @param null $date_rapport
 * @param null $id_doss
 * @param bool|false $recup_credit_solde
 * @return mixed
 */
function getDossiersProvisionData($type_produit = null,$limit = null, $offset = null, $cre_etat = null, $date_rapport = null, $id_doss = null, $recup_credit_solde = false)
{
  global $dbHandler, $global_id_agence, $appli;
  $db = $dbHandler->openConnection();

  // Init
  $list_credit = array();

  // Recuperer tous les credits en souffrance a la date rapport
  if(is_null($date_rapport)) {
    if($appli = 'batch'){
      $date_rapport = getValideDateComptaProvForBatch(null);
    }
    elseif($appli = 'main') {
      $today = date("d/m/Y");
      $date_rapport = hier($today);
    }
  }

  $idEtatPerte = getIDEtatPerte();

  // Recupere tous les dossiers qui ne sont pas en perte a la date rapport
  $sql = "SELECT p.id_doss, p.id_client, p.id_prod, p.cre_mnt_octr, p.mnt_cred_paye,
          (p.cre_mnt_octr - p.mnt_cred_paye) AS capital_restant_du,
          p.id_etat_credit, p.libel_etat_credit, p.mnt_gar_mob,
          CASE
          WHEN p.cre_nbre_reech > 0 THEN e.taux_prov_reechelonne
          WHEN p.is_credit_decouvert = 'f' THEN e.taux
		      WHEN p.is_credit_decouvert = 't' THEN e.taux_prov_decouvert
		      END AS taux_prov,
          p.prov_mnt, p.is_ligne_credit, p.devise, p.id_ag ";
          if ($id_doss != null){
            $sql .="FROM getportfeuilleviewDoss('$date_rapport',$id_doss, $global_id_agence) p ";
          }else{
            $sql .="FROM getportfeuilleview('$date_rapport', $global_id_agence) p ";
          }
          $sql .="INNER JOIN adsys_produit_credit c on c.id = p.id_prod
          INNER JOIN adsys_etat_credits e on p.id_etat_credit = e.id
          WHERE p.id_etat_credit != $idEtatPerte
          AND p.id_ag = $global_id_agence " ;

  if ($type_produit == 1){
    $sql .= " AND c.is_produit_decouvert = 'true' AND p.cre_nbre_reech = 0";
  }elseif ($type_produit == 2){
    $sql .= " AND c.is_produit_decouvert = 'false' AND p.cre_nbre_reech = 0";
  }elseif ($type_produit == 3){
    $sql .=" AND p.cre_nbre_reech > 0";
  }
  if(empty($id_doss)) {
    $sql .= " ORDER by id_etat_credit, id_doss;";
  }
  else {
    $sql .= " AND id_doss = $id_doss;";
  }
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->CloseConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL :") . " " . $sql);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $prov_data = calculprovision($row['id_doss'], $row['id_etat_credit'], $row['taux_prov'], $date_rapport, $row['capital_restant_du'], $row['mnt_gar_mob'], $row['prov_mnt']);

    // si le additional provisions < 0 => reprise
    // si le additional provisions > 0 => dotation
    if(is_array($prov_data) && !empty($prov_data) && round($prov_data['additional_provisions']) != 0) {
      $list_credit[$row['id_doss']] = $row;
      $list_credit[$row['id_doss']]['is_dossier_solde'] = false;
      $list_credit[$row['id_doss']]['taux'] = $prov_data['taux'];
      $list_credit[$row['id_doss']]['provisions_required'] = $prov_data['provisions_required'];
      $list_credit[$row['id_doss']]['previous_provisions'] = $prov_data['previous_provisions'];
      $list_credit[$row['id_doss']]['additional_provisions'] = $prov_data['additional_provisions'];
      $list_credit[$row['id_doss']]['gar_numeraires'] = $prov_data['gar_numeraires'];
    }
  }

  if($recup_credit_solde) {
    // Recuperer les dossiers de credit soldes qui ont des dotations
    $sql = "SELECT d.id_doss, d.id_client, d.id_prod, d.cre_mnt_octr, d.cre_etat AS id_etat_credit,
            d.prov_mnt, d.prov_mnt as previous_provisions, d.is_ligne_credit, d.id_ag,
            pr.devise, e.libel as libel_etat_credit, e.taux, d.etat,
            CASE
            WHEN d.cre_nbre_reech >0 THEN e.taux_prov_reechelonne
            WHEN pr.is_produit_decouvert = 'f' THEN e.taux
            WHEN pr.is_produit_decouvert = 't'  THEN e.taux_prov_decouvert
            END AS taux_prov, p.id_cred_etat, p.montant_dotation
            FROM ad_dcr d
            INNER JOIN adsys_produit_credit pr ON d.id_prod = pr.id
            INNER JOIN adsys_etat_credits e ON d.cre_etat = e.id
            INNER JOIN ad_provision p ON d.id_doss = p.id_doss
            WHERE d.etat IN (6, 12)
            AND d.prov_mnt > 0
            AND p.is_repris = 'f'
            AND d.id_ag=pr.id_ag AND pr.id_ag=e.id_ag AND e.id_ag=p.id_ag AND d.id_ag=$global_id_agence";
    if ($type_produit == 1){
      $sql .= " AND pr.is_produit_decouvert = 'true' AND d.cre_nbre_reech = 0;";
    }elseif ($type_produit == 2){
      $sql .= " AND pr.is_produit_decouvert = 'false' AND d.cre_nbre_reech = 0;";
    }
    elseif ($type_produit == 3){
      $sql .=" AND d.cre_nbre_reech > 0";
    }

    $result = $db->query($sql);

    if (DB::isError($result)) {
      $dbHandler->CloseConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL :") . " " . $sql);
    }

    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      // reset previous values
      unset($list_credit[$row['id_doss']]);
      $list_credit[$row['id_doss']] = $row;

      $list_credit[$row['id_doss']]['mnt_cred_paye'] = '';
      $list_credit[$row['id_doss']]['capital_restant_du'] = 0;
      $list_credit[$row['id_doss']]['mnt_gar_mob'] = 0;
      $list_credit[$row['id_doss']]['solde_gar'] = 0;

      // valeurs par defaut des champs provisions
      $list_credit[$row['id_doss']]['taux'] = '';
      $list_credit[$row['id_doss']]['provisions_required'] = 0;
      $list_credit[$row['id_doss']]['previous_provisions'] = $row['previous_provisions'];
      $list_credit[$row['id_doss']]['additional_provisions'] = -1 * $row['previous_provisions'];
      $list_credit[$row['id_doss']]['gar_numeraires'] = 0;

      // Libelle de la class credit a titre d'information
      $list_credit[$row['id_doss']]['libel_etat_credit'] = _("Credit soldé");
      $list_credit[$row['id_doss']]['etat'] = $row['etat'];
      $list_credit[$row['id_doss']]['is_dossier_solde'] = true;
    }
  }

  $dbHandler->CloseConnection(true);
  return $list_credit;
}


/**
 * Fonction permettant de renvoyer la liste des credits en souffrances à provisionner
 * */
function getListCreditsSouffrancesProv($limit = null, $offset = null,$cre_etat = null)
{
  global $dbHandler, $global_id_agence, $global_monnaie;
  global $global_nom_login;

  $db = $dbHandler->openConnection();
  // recuperer tous les credit appartenant a l'etat de souffrances
  $sql = "SELECT d.* FROM get_ad_dcr_ext_credit(null, null, null, null, $global_id_agence) d WHERE d.etat in(5,6,7,13,14,15) ";
  if (!is_null($cre_etat)){
    $sql .= " AND d.cre_etat IN (select id from adsys_etat_credits where id=$cre_etat /*and provisionne=true */and  nbre_jours != -1) ";
  }
  else{
    $sql .= " AND d.cre_etat IN (select id from adsys_etat_credits where /* provisionne=true and */ nbre_jours != -1) ";
  }
  $sql .= " ORDER BY d.id_doss";

  if (!is_null($limit)) $sql .= " LIMIT  $limit";
  if (!is_null($offset)) $sql .= " OFFSET $offset ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->CloseConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL :") . " " . $sql);
  }

  $list_credit = array();

  //recuperer tous les etat des crédits
  $EtatCredits = getTousEtatCredit();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $prov_data = calculprovision($row['id_doss']);
    // si le additional provisions < 0 => reprise
    // si le additional provisions > 0 => dotation

    if(is_array($prov_data) && !empty($prov_data) && round($prov_data['additional_provisions']) != 0) {
      $list_credit[$row['id_doss']] = $row;
      $list_credit[$row['id_doss']]['taux'] = $prov_data['taux'];
      $list_credit[$row['id_doss']]['provisions_required'] = $prov_data['provisions_required'];
      $list_credit[$row['id_doss']]['previous_provisions'] = $prov_data['previous_provisions'];
      $list_credit[$row['id_doss']]['additional_provisions'] = $prov_data['additional_provisions'];
      $list_credit[$row['id_doss']]['gar_numeraires'] = $prov_data['gar_numeraires'];

    }
  }

  $dbHandler->CloseConnection(true);
  return $list_credit;
}


/**
 * Fonction permettant de renvoyer la liste des credits sains ou en perte pour lesquels on doit faire une reprise sur provision
 * */
function getListCreditsRepriseProv ( $limit=null,$offset=null){
	global $dbHandler,$global_id_agence,$global_monnaie;
	global $global_nom_login;

	$db = $dbHandler->openConnection();
	//recupérer les crédits qui ont été provionnés , qu'ils soient soldés,en pertes, deboursés ou réechelones et n'appartenant plus a un etat de risque
    $sql = "SELECT d.* FROM get_ad_dcr_ext_credit(null, null, null, null, $global_id_agence) d WHERE (d.etat=6 OR d.etat=9 OR ((d.etat=5 OR d.etat=7 OR d.etat=14 OR d.etat=15) AND d.cre_etat IN (SELECT id FROM adsys_etat_credits WHERE provisionne=false) ) ) AND d.prov_is_calcul=true AND d.prov_mnt >0 ORDER BY d.id_doss";
	if (!is_null($limit)) $sql.=" LIMIT  $limit";
	if (!is_null($offset)) $sql.=" OFFSET $offset ";

	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->CloseConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL :")." ".$sql);
	}
	$list_credit=array();
	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
		$list_credit[$row['id_doss']]=$row;
	}//fin while
	$dbHandler->CloseConnection(true);
	return 	$list_credit;
}

/**
 *
 * Modifier les provisions des dossier de credit
 *
 * @param $tabcredit
 * @return ErrorObj
 */
function modifierProvCreditsSouffrances ($tabcredit, $date_compta_prov=null) {

	global $dbHandler,$global_id_agence,$global_monnaie;
	global $global_nom_login;

    $comptable = array();
	$db = $dbHandler->openConnection();

    $reponse = provisionTabCreditsSouffrances($tabcredit, $comptable, $date_compta_prov);
    $myErr = ajout_historique(433, NULL, NULL, $global_nom_login, date("r"),$comptable);

    if ($myErr->errCode != NO_ERR ) {
		$dbHandler->closeConnection(false);
		return $myErr;
	}

    $list_id_provisionne = $reponse->param['list_id_provisionne'];
    $id_his = $myErr->param;

    // Update id_his of provision
    if(count($list_id_provisionne) > 0) {
      foreach($list_id_provisionne as $id_provision) {
        $DATA['id_his'] = $id_his;
        insertOrUpdateProvision($DATA, $id_provision);
      }
    }

	$dbHandler->CloseConnection(true);
	return $reponse;
}



/**
 * Crée une nouvelle entrée dans ad_provion avec les données des provisions
 * @param Array $DATA Toutes les données de la provision
 * @return ErrorObj Objet Erreur
 */
function insertOrUpdateProvision($DATA, $id = null) {

	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
	$DATA['id_ag']= $global_id_agence;

    // Insert
    if(is_null($id)) {
      $sql = buildInsertQuery ("ad_provision", $DATA);
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
      }
    }
    else { // Update
      $WHERE['id_provision'] = $id;
      $sql = buildUpdateQuery('ad_provision', $DATA, $WHERE);
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
      }
    }

	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR);
}

// @todo: delete ?
function insertionProvision($DATA, $id = null) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $DATA['id_ag']= $global_id_agence;

  $sql = buildInsertQuery ("ad_provision", $DATA);
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}


function getlasEcritureProvision($id_doss) {
	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
	$sql = "select c.compte, c.sens,type_operation ";
	$sql .= " from ad_his a, ad_ecriture b, ad_mouvement c ";
	$sql .= " where  a.id_ag=b.id_ag and a.id_ag=c.id_ag and ";
	$sql .= " b.id_ag=c.id_ag and a.id_ag = $global_id_agence AND   cast(info_ecriture as INTEGER) = $id_doss and ";
	$sql .= " a.id_his= b.id_his and b.id_ecriture= c.id_ecriture and (type_fonction = 432 OR type_fonction = 433 ) and";
	$sql .= " a.date = ( select max(date) from ad_his where ad_his.date = a.date and (type_fonction = 432 OR type_fonction = 433 ) and  id_ag = $global_id_agence) order by c.id_mouvement,sens";
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->CloseConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL :")." ".$sql);
	}
	$list_ecriture=array();
	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
		$list_ecriture[$row['sens']]=$row;
	}//fin while
	$dbHandler->CloseConnection(true);
	return 	$list_ecriture;
}

function getlastProvision($id_doss) {
	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
	$sql = "SELECT * ";
	$sql .= " FROM ad_provision ";
	$sql .= " WHERE  id_ag = $global_id_agence AND  id_doss = $id_doss  ";
	$sql .= " AND date_prov = ( SELECT max(date_prov) FROM ad_provision WHERE id_doss = $id_doss ) ";
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->CloseConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL :")." ".$sql);
	}
	$provision=array();
	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
		$provision[$row['id_doss']]=$row;
	}//fin while
	$dbHandler->CloseConnection(true);
	return 	$provision;
}


function provisionCreditsRadie($id_doss, &$comptable, $soldecap,$id_prod,$cre_etat,$devise, $date_compta_prov=null)
{
  global $dbHandler, $error;

  /*
   *  vérifier que le crédit en question a été provisionné 100%
   * Dans ce cas, faire une dotation ou une reprise.
   * Puis faire une reprise en totalité sur le montant constitué pour ce dossier.
  */

  $infos_doss_hist = getDossiersProvisionData(null,null, null, null, $date_compta_prov, $id_doss);
  $prov_cre = calculprovision($id_doss, $infos_doss_hist[$id_doss]['id_etat_credit'], $infos_doss_hist[$id_doss]['taux_prov'], $date_compta_prov, $infos_doss_hist[$id_doss]['capital_restant_du'], $infos_doss_hist[$id_doss]['solde_gar'], $infos_doss_hist[$id_doss]['prov_mnt']);

  $mnt_provision = $prov_cre['additional_provisions'];

  $Data = array();

  $Data[$id_doss]['id_doss'] = $id_doss;
  $Data[$id_doss]["id_prod"] = $id_prod;
  $Data[$id_doss]['prov_mnt_new'] = $mnt_provision;
  $Data[$id_doss]['prov_mnt'] = $prov_cre["previous_provisions"];
  $Data[$id_doss]['cre_etat'] = $cre_etat;
  $Data[$id_doss]['devise'] = $devise;

  //si le montant à provisioner != 0 faire une dotation ou une reprise.
  if ($mnt_provision != 0 )
  {
    $dotation = true;
    if (recupMontant($mnt_provision) < 0) {
      $dotation = false;
    }
    $Data[$id_doss]['dotation'] = $dotation;

    $myErr = provisionCredit($Data, $date_compta_prov);

    if ($myErr->errCode != NO_ERR) {
      $html_err = new HTML_erreur(_("Echec lors du calcul des provisions des crédits en perte. "));
      $html_err->setMessage("Erreur : " . $error[$myErr->errCode] . $myErr->param);
      $html_err->addButton("BUTTON_OK", 'Gen-14');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      exit();
    }
  }

// REPRISE TOTALE
  $dotation = false;

  $infos_doss_hist = getDossiersProvisionData(null,null, null, null, $date_compta_prov, $id_doss);
  $rep_cre = calculprovision($id_doss, $infos_doss_hist[$id_doss]['id_etat_credit'], $infos_doss_hist[$id_doss]['taux_prov'], $date_compta_prov, $infos_doss_hist[$id_doss]['capital_restant_du'], $infos_doss_hist[$id_doss]['solde_gar'], $infos_doss_hist[$id_doss]['prov_mnt']);
  $mnt_reprise_abs =  $rep_cre["previous_provisions"];
  $mnt_reprise = ($mnt_reprise_abs * -1);

  $Data[$id_doss]['prov_mnt_new'] = $mnt_reprise;
  $Data[$id_doss]['prov_mnt'] = $mnt_reprise_abs;
  $Data[$id_doss]['dotation'] = $dotation;

  $myErr2 = provisionCredit($Data, $date_compta_prov);

  if ($myErr2->errCode != NO_ERR) {
      $html_err = new HTML_erreur(_("Echec lors du calcul des provisions des crédits en souffrances. "));
      $html_err->setMessage("Erreur : " . $error[$myErr2->errCode] . $myErr2->param);
      $html_err->addButton("BUTTON_OK", 'Gen-14');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      exit();
    }

  return new ErrorObj(NO_ERR, $myErr2->param);
}

/**
 * Recupere les dates debut et date de fin pour les provisions a date anterieure :
 * 1. Date minimum dois être postérieure ou égal a la date de dernier provision si dans le même exercice comptable ouvert
 *     ou égal a la date de début exercice si la date dernière provision se trouve dans une exercice clôturée.
 *
 * 2. Date max dois être égal ou antérieure a la date d'hier
 * @author : bd
 * @return array
 */
function getAllowedDatesForBackdateProvision($date_valeur = NULL)
{
  global $global_id_agence, $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT MAX(date_prov) as max_date_prov from ad_provision;";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->CloseConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL :")." ".$sql);
  }

  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);

  $last_date_prov = $row['max_date_prov'];
  $last_date_prov = pg2phpDate($last_date_prov);

  $today = (date("d/m/Y"));
  $hier = hier($today);

  $sql = "SELECT * FROM ad_exercices_compta WHERE id_ag=$global_id_agence AND etat_exo in (1,2) ORDER BY id_exo_compta asc;";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->CloseConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL :")." ".$sql);
  }

  $allowed_dates = array();
  $allowed_date_deb_exo = NULL;
  $id_exo = "";

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $date_debut_exo = $row['date_deb_exo'];
    $date_debut_exo = pg2phpDate($date_debut_exo);

    $date_fin_exo = $row['date_fin_exo'];
    $date_fin_exo = pg2phpDate($date_fin_exo);

    if( is_null($allowed_date_deb_exo) || ( ! is_null($allowed_date_deb_exo) && isBefore($date_debut_exo, $allowed_date_deb_exo, true)) ) {
      $allowed_date_deb_exo = $date_debut_exo;
    }

    if(!empty($date_valeur)) {
      if( (isAfter($date_valeur, $date_debut_exo, true)) && (isBefore($date_valeur, $date_fin_exo, true))) {
        $id_exo =  $row['id_exo_compta'];
      }
    }
  }

  $allowed_date_fin = $hier;

  if(isAfter($allowed_date_deb_exo, $last_date_prov)) {
    $allowed_date_deb =  $allowed_date_deb_exo;
  }
  else {
    $allowed_date_deb = $last_date_prov;
  }

  if(isAfter($allowed_date_deb, $hier)) {
    $allowed_date_deb = $hier;
  }

  if(empty($last_date_prov)) {
    $allowed_date_deb = $today;
    $allowed_date_fin = $today;
  }

  if( !empty($last_date_prov) && ($last_date_prov == $today)) {
    $allowed_date_deb = $today;
    $allowed_date_fin = $today;
  }

  $allowed_dates['allowed_date_deb'] = $allowed_date_deb;
  $allowed_dates['allowed_date_fin'] = $allowed_date_fin;
  $allowed_dates['last_date_prov'] = $last_date_prov;
  $allowed_dates['id_exo'] = $id_exo;

  $dbHandler->CloseConnection(true);
  return $allowed_dates;
}

function passeEcritProvisionDeclassementCred($id_doss,$mnt_provision,$ancien_etat, $id_prod,$cre_etat,$devise, &$comptable, $date_compta_prov=null)
{
  global $appli;
  global $dbHandler;

  global $global_monnaie;
  //type opération
  $type_oper=271;
  // declassement (provisionné)

  $cptes=getAllCompteEtatCredit($id_prod);
  $Fields['prov_mnt']=$mnt_provision;
  $solde_provisionne = $mnt_provision;
  $solde_prov_new=$mnt_provision;

  if($mnt_provision != 0 )
  {
    //compte au debit pr la provision -> c'est le compte de dotation !
    $cptes_substitue["cpta"]["debit"] = $cptes[$ancien_etat]["cpte_provision_credit"];
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $where=" WHERE id=".$id_prod." ";
      $produit=getProdInfo($where, $id_doss);
      //recuperer tous les etat des crédits
      $EtatCredits=getTousEtatCredit();
      return new ErrorObj(ERR_CPTE_NON_PARAM,sprintf( _("compte provision au débit associé à l'état %s du  produit de crédit %s"),$EtatCredits[$cre_etat]['libel'],$produit[0]['libel'])." ");
    }


    //compte au credit pr la provision -> c'est le compte de provision !
    $cptes_substitue["cpta"]["credit"] =$cptes[$cre_etat]["cpte_provision_credit"];
    if ($cptes_substitue["cpta"]["credit"] == NULL) {
      $where=" WHERE id=".$id_prod." ";
      $produit=getProdInfo($where, $id_doss);
      //recuperer tous les etat des crédits
      $EtatCredits=getTousEtatCredit();

      return new ErrorObj(ERR_CPTE_NON_PARAM,sprintf( _("compte provision  au crédit associé à l'état %s du  produit de crédit %s - %s"),$EtatCredits[$cre_etat]['libel'],$produit[0]['libel'])." ",$id_doss);
    }

    // Si on n'est pas dans le batch
    if($appli = 'main') {
      $infos_sup = array();
      $allowed_dates = getAllowedDatesForBackdateProvision($date_compta_prov);
      $infos_sup['date_debut'] = $allowed_dates['allowed_date_deb'];
      $infos_sup['date_fin'] = $allowed_dates['allowed_date_fin'];
      $infos_sup['id_exo'] = $allowed_dates['id_exo'];
    }

    // Le montant de l'ecriture est le nouveau montant saisi pour la provision
    $err = effectueChangePrivate($global_monnaie, $devise, $solde_provisionne, $type_oper, $cptes_substitue, $comptable, false,NULL,$id_doss, $infos_sup, $date_compta_prov);
    
    if ($err->errCode != NO_ERR ) {
      return $err;
    }
  }

  return new ErrorObj(NO_ERR,array("solde_prov_new"=>$solde_prov_new));
}

/*
   *Fonction qui retourne les id des dossier non provisioné
   *B&D
   * */
function getEtatCreditNonProv(){

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $retour = array();
  $sql = "SELECT id from adsys_etat_credits where provisionne = false and nbre_jours > 0 and id_ag = $global_id_agence ";


  $result=$db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);


    if ($result->numRows() == 0) return NULL;
  while ($rows = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $retour[$rows["id"]]=$rows;

  foreach($retour as $k => $v ){
    $resultat[]=$k;
  }
  return $resultat;

}

/**
 * Type opération frais forfaitaire du service SMS
 * Check if type opération 188 has a num_cpte on the credit side (sens 'c')
 */
function checkTypeOperationFraisSMSsensCredit($typeOperation)
{
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT num_cpte FROM ad_cpt_ope_cptes WHERE type_operation = $typeOperation AND id_ag = $global_id_agence AND sens = 'c'";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL :")." ".$sql);
  }

  $rows = $result->fetchrow(DB_FETCHMODE_ASSOC);

  if(!isset($rows['num_cpte'])){
    $dbHandler->closeConnection(false);
    return new ErrorOBj(ERR_NO_ASSOCIATION, "L'opération $typeOperation n'a pas de compte associer paramétrer");
  }
  else {
    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);
  }
}

function isEcritureAttenteMTN($compte, $cpte_interne_cli,$id_transaction) {
  //Verifie s'il y des ecritures en attente sur le compte

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT count(compte) FROM ad_brouillard where id_ag=$global_id_agence and compte = '$compte' and cpte_interne_cli = $cpte_interne_cli and infos_brouillard ='$id_transaction'";
  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $row = $result->fetchrow();

  if ($row[0] > 0)
    return true;
  else
    return false;
}



function getDataBrouillard($num_compte, $type_ope,$num_transaction)
{
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT id_his FROM ad_brouillard WHERE compte = '$num_compte' and type_operation = $type_ope AND id_ag = $global_id_agence AND sens = 'c' AND infos_brouillard = '$num_transaction'";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL :") . " " . $sql);
  }

  $rows = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);
  return $rows["id_his"];

}

function passageEcrituresBrouillardEwallet($DATA) {
  global $dbHandler, $global_nom_login, $global_id_agence;

  $db = $dbHandler->openConnection();
  //historiser avant d'inserer dans ad_brouillard
  $MyError = ajout_historique(470, NULL,_("Passage écritures Brouillard"),$global_nom_login,date("r"),NULL,NULL);
  if ($MyError->errCode != NO_ERR)
    return $MyError;
  $idhis = $MyError->param;

  if ($MyError->errCode != NO_ERR)
    return $MyError;

  $idhis = $MyError->param;

  //  passage des ecritures
  if (isset($DATA))

    foreach ($DATA as $key => $value) {
      $sql = "INSERT INTO ad_brouillard ";
      $sql .= "(id_his,id_ag, id, devise, compte, cpte_interne_cli, sens, montant, date_comptable, libel_ecriture, type_operation, ";
      $sql .= "id_jou, id_exo, id_taxe, sens_taxe,infos_brouillard) ";
      //$sql .= "VALUES('".$value['id_his']."', '".$value['id']."','".$value['compte']."', ";
      $sql .= "VALUES('".$idhis."',$global_id_agence, '".$value['id']."','".$value['devise']."','".$value['compte']."', ";

      if ($value['cpte_interne_cli'] == '') $sql .= "NULL,";
      else $sql .= "'".$value['cpte_interne_cli']."',";

      if ($value['sens']=='' or $value['sens']==NULL) $sql .= "NULL,";
      else $sql .= "'".$value['sens']."', ";

      if ($value['montant']=='' or $value['montant']==NULL) $sql .= "0,";
      else $sql .= $value['montant'].", ";

      if ($value['date_comptable']=='') 	$sql .= "NULL,";
      else $sql .="'".$value['date_comptable']."',";

      if (is_champ_traduit('ad_brouillard','libel_ecriture')) {

        if(is_trad(unserialize($value["libel_ecriture"]))){

          $libel_ecriture_trad = unserialize($value["libel_ecriture"]);
          $libel_ecriture = $libel_ecriture_trad->save();
        }else{
          $libel_ecriture_trad = new Trad();
          $libel_ecriture_trad->set_traduction(get_langue_systeme_par_defaut(), ($value["libel_ecriture"]));

          $libel_ecriture = $libel_ecriture_trad->save();
        }
      }else{
        $libel_ecriture = htmlspecialchars($value["libel_ecriture"], ENT_QUOTES, "UTF-8");
      }
      $sql .= "'".$libel_ecriture."',";
      if ($value['type_operation'] == '') $sql .= "NULL,";
      else $sql .= "'".$value['type_operation']."',";

      if ($value['id_jou'] == '') $sql .= "NULL,";
      else $sql .= "'".$value['id_jou']."',";

      if ($value['id_exo'] == '') $sql .= "NULL,";
      else $sql .= $value["id_exo"].",";

      if ($value['id_taxe'] == '') $sql .= "NULL,";
      else $sql .= $value['id_taxe'].",";

      if ($value['sens_taxe'] == '') $sql .= "NULL,";
      else $sql .= "'".$value["sens_taxe"].",";

      if (isset($value['infos_brouillard'])) {
        if ($value['infos_brouillard'] == '') $sql .= "NULL)";
        else $sql .= "'" . $value['infos_brouillard'] . "')";
      }else{
        $sql .= "NULL)";
      }
      // $sql .= ",'".$value["validation"]."')";

      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }
    }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR,$idhis);

}

?>
