<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Fichier contenant tous les traitements relatifs au module multi-devise
 * @version 2
 * @package System
 */

require_once 'lib/dbProcedures/interface.php';
require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/misc/Erreur.php';
require_once('Numbers/Words.php');
/**
 * renvoie un tableau contenant toutes les devises avec les informations suivantes : Code Iso de la devise, libellé, taux, taux achat, taux vente.
 * @author Bernard DE BOIS
 * @return array
 */
function get_table_devises() {
  global $dbHandler, $global_id_agence;


  $db = $dbHandler->openConnection();
  $retour = array();

  $sql = "SELECT code_devise, libel_devise, taux_indicatif, taux_achat_trf, taux_achat_cash, taux_vente_trf, taux_vente_cash FROM devise ";// ORDER BY taux_indicatif";
  $sql .=	" where id_ag =  $global_id_agence ORDER BY taux_indicatif ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  while ($row = $result->fetchrow()) {
    $retour[$row[0]]['libel']=$row[1];
    $retour[$row[0]]['taux']=$row[2];
    $retour[$row[0]]['achatTrf']=$row[3];
    $retour[$row[0]]['achatCash']=$row[4];
    $retour[$row[0]]['venteTrf']=$row[5];
    $retour[$row[0]]['venteCash']=$row[6];
  }

  $db = $dbHandler->closeConnection(true);
  return $retour;
}
/**
 * Fonction de création du fichier autorisation
 * @param array $DATA tableau de données contenant les informations de la recharge
 * @param string chemin du fichier
 * @return void
 */
function creationFichier($DATA,$Fnm){
  $inF = fopen($Fnm,"w");
  //Parcourir le tableau de données
  reset($DATA);
  foreach ($DATA as $ligneFichier) {
    //Ecrire la valeur courante sur une ligne et aller à la ligne suivante
    fputs($inF,$ligneFichier."\n");
  }
  //Enfin fermer le fichier
  fclose($inF);
  return;
}
/**
 * Retourne tous les champs de la table devise pour une devise donnée ($dev)
 * @author Bernard DE BOIS
 * @param char(3) code de la devise
 * @return array
 */
function getInfoDevise($dev) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $retour = NULL;

  $sql = "SELECT * FROM devise WHERE code_devise = '$dev' and id_ag=$global_id_agence ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $retour = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);
  return $retour;
}
/**
 * Retourne tous le champ libelle de la table devise pour une devise donnée ($dev)
 * @param char(3) code de la devise
 * @return array
 */

function getLibelDevise($dev) {
	global $dbHandler,$global_id_agence;

	$db = $dbHandler->openConnection();
	$retour = NULL;

	$sql = "SELECT libel_devise FROM devise WHERE code_devise = '$dev' and id_ag=$global_id_agence ";
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}
	$retour = $result->fetchrow(DB_FETCHMODE_ASSOC);
	$dbHandler->closeConnection(true);
	return $retour;
}

/**
 * Retourne le montant en lettre pour une montant donnée ().
 * Cette fonction utilise la fonctionalite PEAR(Numbers_Words)
 * @param $montant
 * @return string
 */
function getMontantEnLettre($montant,$global_langue_rapport,$dev) {
	$mntEnLettre = Numbers_Words::toWords($montant,$global_langue_rapport);
	if (PEAR::isError($mntEnLettre)) {
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	} else {
		
		$libelDevise = getLibelDevise($dev);
		
		$mntAvecDevise=$mntEnLettre." ".strtolower($libelDevise['libel_devise']);
		$mntAvecDevise=ucfirst($mntAvecDevise);
		return $mntAvecDevise;
		
	}
	
}


/**
 * Insère une nouvelle devise dans la table des devises.
 *S'il s'agit de la devise de référence, on met aussi à jour la table ad_agc avec les données de la devise.
 * @author Bernard DE BOIS
 * @param array Array contenant toutes les infos de la nouvelle devise : code, libellé, taux, taux vente, taux achat, ...
 * @return Objet ErrorObj
 */
function insertDevise($data) {
  global $dbHandler;
  global $global_id_agence;
  global $global_multidevise, $global_monnaie;
  global $global_nom_login;

  $db = $dbHandler->openConnection();
  $retour = array();
  if (isset($data['taux_indicatif'])) { // Il s'agit d'une nouvelle devise autre que la devise de référence.

    // On tente de passer du mode unidevise en mode multidevise
    // Annuler toutes les affectations en devise pour les comptes de la classe  1 => 5
    if ($global_multidevise == false) {
      // Ceci n'est possible que si aucune écriture n'a été passée sur un compte
      $sql = "SELECT count(*) FROM ad_ecriture where id_ag=$global_id_agence ";
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }
      $row = $result->fetchrow();
      if ($row[0] > 0) { // On a déjà passé au moins une écriture
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_MULTIDEV_ECR_EXIST);
      }

      // On récupère les comptes de pos de ch, cv pos de ch, et variation taux déb/créd pour les renseigner dans la table agence
      $UPDATE = array();
      $UPDATE["cpte_position_change"] = $data["cpte_position_change"];
      $UPDATE["cpte_contreval_position_change"] = $data["cpte_contreval_position_change"];
      $UPDATE["cpte_variation_taux_deb"] = $data["cpte_variation_taux_deb"];
      $UPDATE["cpte_variation_taux_cred"] = $data["cpte_variation_taux_cred"];
      $WHERE = array("id_ag" => $global_id_agence);
      $sql = buildUpdateQuery("ad_agc", $UPDATE, $WHERE);
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }

      // On supprime de $data les champs qui ne doivent pas etre insérés dans la table devise
      unset($data["cpte_position_change"]);
      unset($data["cpte_contreval_position_change"]);
      unset($data["cpte_variation_taux_deb"]);
      unset($data["cpte_variation_taux_cred"]);

      // Passage en mode multidevise
      $global_multidevise = true;
      $sql = "UPDATE ad_cpt_comptable SET devise = NULL WHERE id_ag=$global_id_agence AND compart_cpte BETWEEN 1 AND 2";
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }
    }

    // Récupère les infos sur l'agence
    $AG = getAgenceDatas($global_id_agence);

    // Insertion de la devise proprement dite
    $data["id_ag"]= $global_id_agence;
    $sql = buildInsertQuery("devise", $data);
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }


    // Création du compte de Pos de Ch pour cette devise
    $cpt_pos_ch = $AG['cpte_position_change'];
    $num_cpte = "$cpt_pos_ch.".$data["code_devise"];
    $libel = _("Position de change ").$data["code_devise"];
    $devise = $data["code_devise"];
    $CPT = array("num_cpte_comptable" => $num_cpte, "libel_cpte_comptable" => $libel, "solde" => 0, "devise" => $devise);
    $CPTS = array ($num_cpte => $CPT);

    $myErr = ajoutSousCompteComptable($cpt_pos_ch, $CPTS);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      global $error;
      return $myErr;
    }

    // Création du compte de C/V Pos de Ch pour cette devise
    $cpt_cv_pos_ch = $AG['cpte_contreval_position_change'];
    $num_cpte = "$cpt_cv_pos_ch.".$data["code_devise"];
    $libel = _("C/V position de change ").$data["code_devise"];
    $devise = $AG["code_devise_reference"];
    $CPT = array("num_cpte_comptable" => $num_cpte, "libel_cpte_comptable" => $libel, "solde" => 0, "devise" => $devise);
    $CPTS = array ($num_cpte => $CPT);

    $myErr = ajoutSousCompteComptable($cpt_cv_pos_ch, $CPTS);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

    // Création du compte d'attente variation Taux Débiteur
    $cpt_vr_tx_deb = $AG['cpte_variation_taux_deb'];
    $num_cpte = "$cpt_vr_tx_deb.".$data["code_devise"];
    $libel = _("Variation taux débiteur ").$data["code_devise"];
    $devise = $AG["code_devise_reference"];
    $CPT = array("num_cpte_comptable" => $num_cpte, "libel_cpte_comptable" => $libel, "solde" => 0, "devise" => $devise);
    $CPTS = array ($num_cpte => $CPT);

    $myErr = ajoutSousCompteComptable($cpt_vr_tx_deb, $CPTS);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

    // Création du compte d'attente variation Taux Créditeur
    $cpt_vr_tx_cred = $AG['cpte_variation_taux_cred'];
    $num_cpte = "$cpt_vr_tx_cred.".$data["code_devise"];
    $libel = _("Variation taux créditeur ").$data["code_devise"];
    $devise = $AG["code_devise_reference"];
    $CPT = array("num_cpte_comptable" => $num_cpte, "libel_cpte_comptable" => $libel, "solde" => 0, "devise" => $devise);
    $CPTS = array ($num_cpte => $CPT);

    $myErr = ajoutSousCompteComptable($cpt_vr_tx_cred, $CPTS);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

  } else { // Il s'agit de la devise de référence définie pour la première fois

    // Insertion de la devise dans la table
    if ($data["precision"] == "") $precision = 1;
    else $precision = $data["precision"];
    $sql = "INSERT INTO devise (code_devise,id_ag, libel_devise, precision, taux_indicatif, taux_achat_cash, taux_achat_trf, taux_vente_cash, taux_vente_trf, cpte_produit_commission, cpte_produit_taux) VALUES ('".$data['code_devise']."',$global_id_agence,'".$data['libel_devise']."', ".$precision.",1,1,1,1,1,NULL,NULL)";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    // MAJ ad_agc
    $sql = "UPDATE ad_agc SET code_devise_reference ='".$data['code_devise']."' WHERE id_ag = $global_id_agence";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    // MAJ toutes les tables de comptes et de produits
    $WHERE = array("id_ag" => $global_id_agence);
    $sql = buildUpdateQuery("ad_cpt_comptable", array("devise" => $data["code_devise"]),$WHERE);
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $sql = buildUpdateQuery("ad_cpt", array("devise" => $data["code_devise"]),$WHERE);
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $sql = buildUpdateQuery("adsys_produit_epargne", array("devise" => $data["code_devise"]),$WHERE);
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $sql = buildUpdateQuery("adsys_produit_credit", array("devise" => $data["code_devise"]),$WHERE);
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    // On initialise les variables globales
    $global_monnaie = $data["code_devise"];

  }
  $myErr = ajout_historique(275,NULL,NULL,$global_nom_login,date("r"),NULL);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  $db = $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Met à jour les données d'une devise déterminée dans la table des devises.
 * Passe les écritures comptables nécessaires si le taux indicatif varie et qu'il existe une position de change dans cette devise.
 * @author Bernard DE BOIS
 * @param char(3) $devise Code ISO de la devise
 * @param array Array contenant tous les champs associés à la devise.
 * @return Message d'erreur s'il y a lieu.
 */
function updateDevise($code_devise, $data) {
  global $dbHandler;
  global $global_id_agence;
  global $global_nom_login;
  global $error;
  $comptable=array();
  $AG = getAgenceDatas($global_id_agence);
  $dev_ref = $AG["code_devise_reference"];

  $db = $dbHandler->openConnection();
  $retour = array();
  $cptDevise=getCptesLies($code_devise);
  $position=getComptesComptables(array("num_cpte_comptable" => $cptDevise['position']));
  $cvPositionInit = getComptesComptables(array("num_cpte_comptable" => $cptDevise['cvPosition']));
  $soldePosition = $position[$cptDevise['position']]['solde'];
  $soldeCV = $cvPositionInit[$cptDevise['cvPosition']]['solde'];
  $WHERE = array("id_ag" => $global_id_agence);
  $sql = buildUpdateQuery("devise", $data, array("code_devise" => $code_devise),$WHERE);
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  // calcul de la nouvelle contrevaleur en fonction du nouveau taux
  $nouveauCvPosition=calculeCV($code_devise, $dev_ref, $soldePosition);
  if (($nouveauCvPosition + $soldeCV)==0) {
    $myErr = ajout_historique(276,NULL,NULL,$global_nom_login,date("r"),NULL);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
    $db = $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);
  }
  if (($nouveauCvPosition + $soldeCV) > 0) {
    $gainSurTaux=$nouveauCvPosition+$soldeCV;
    $array_cptes["cpta"]["debit"] = $cptDevise['cvPosition'];
    $array_cptes["int"]["debit"] = NULL;
    $array_cptes["cpta"]["credit"] = $cptDevise['credit'];
    $array_cptes["int"]["credit"] = NULL;
    $myErr = passageEcrituresComptablesAuto(453, $gainSurTaux, $comptable, $array_cptes, $dev_ref);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  } else {
    $perteSurTaux=-($soldeCV+$nouveauCvPosition);
    $array_cptes["cpta"]["debit"] = $cptDevise['debit'];
    $array_cptes["int"]["debit"] = NULL;
    $array_cptes["cpta"]["credit"] = $cptDevise['cvPosition'];
    $array_cptes["int"]["credit"] = NULL;
    $myErr = passageEcrituresComptablesAuto(454, $perteSurTaux, $comptable, $array_cptes, $dev_ref);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }
  $myErr = ajout_historique(276,NULL,NULL,$global_nom_login,date("r"),$comptable);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $db = $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Renvoie tous les numéros de comptes liés à une devise, tels que paramétrés dans ad_agc
 * <ul>
 *  <li>compte de position de change</li>
 *  <li>compte de la contrevaleur de la position de change</li>
 *  <li>compte d'attente de variation de taux au crédit</li>
 *  <li>compte d'attente de variation de taux au débit</li>
 * </ul>
 * @author Thomas FASTENAKEL
 * @param char(3) $devise Code ISO de la devise
 * @return array les quatres numéros de compte
 */
function getCptesLies($devise) {
  global $global_id_agence;
  $comptes=array();
  $AG = getAgenceDatas($global_id_agence);
  $cpt_pos_ch = $AG["cpte_position_change"];
  $cpt_cv_pos_ch = $AG["cpte_contreval_position_change"];
  $cpt_credit = $AG["cpte_variation_taux_cred"];
  $cpt_debit = $AG["cpte_variation_taux_deb"];
  $comptes['position']=$cpt_pos_ch.".".$devise;
  $comptes['cvPosition']=$cpt_cv_pos_ch.".".$devise;
  $comptes['debit']=$cpt_debit.".".$devise;
  $comptes['credit']=$cpt_credit.".".$devise;
  return $comptes;
}

/**
 * Renvoie la valeur du taux dez change devise1 => devise2 en fonction du contexte
 * @author Thomas FASTENAKEL
 * @param char(3) $devise1 Code ISO de la devise 1
 * @param char(3) $devise2 Code ISO de la devise 2
 * @param bool $commercial Indique si les taux commerciaux doivent tre utilisés
 * @param defined(1,2)  $type Le type est 1 pour un change cash, 2 pour un change par transfert (utile uniquement si $commercial = true)
 * @return double Taux de change ou NULL si une des devises n'existe pas
 */
function getTauxChange($devise1, $devise2, $commercial, $type=NULL) {
  // Recherche infos devise 1
  $DEV1 = getInfoDevise($devise1);
  if (!is_array($DEV1)) { // La devise 1 n'existe pas
    return NULL;
  }

  // Recherche infos devise 2
  $DEV2 = getInfoDevise($devise2);
  if (!is_array($DEV2)) { // La devise 2 n'existe pas
    return NULL;
  }

  if (!$commercial) { // C'est le taux indicatif qui dpoit etre utilisé
    $field_taux1 = "taux_indicatif";
    $field_taux2 = "taux_indicatif";
  } else { // On prend le taux achat de $devise1 et le taux vente de $devise2 pour maximiser le bénéfice
    if ($type == 1) { // CASH
      $field_taux1 = "taux_achat_cash";
      $field_taux2 = "taux_vente_cash";
    } else if ($type == 2) { // TRANSFERT
      $field_taux1 = "taux_achat_trf";
      $field_taux2 = "taux_vente_trf";
    }
  }

  // Calcul du taux réel
  $taux_change = round($DEV2[$field_taux2] / $DEV1[$field_taux1], 12);
  return $taux_change;
}

/**
 * Calcule le C/V en $devise2 de $motnant exprimé dans $devise1
 * @author Thomas FASTENAKEL
 * @param double $montant Montant à changer
 * @param char(3) $devise1 Code ISO de la devise de départ
 * @param char(3) $devise2 Code ISO de la devise de destination
 * @return double Montant de la C/V
 */
function calculeCV($devise1, $devise2, $montant) {

  if ($devise1 == $devise2)
    return $montant;

  $taux = getTauxChange($devise1, $devise2, false);
  $cv_montant = $montant * $taux;
  $DEV = getInfoDevise($devise2);
  $cv_montant = round($cv_montant, $DEV["precision"]);
  return $cv_montant;
}

/**
 * Renvoie true si le montant 1 exprimé en devise 1 est équivalent au montant 2 exprimé en devise 2
 * On considère que deux montants sont équivalents si la C/V de montant 1 dans la devise 2 est égal à montant 2 +/- le maximum entre la plus petite unité de devise 1 exprimée en devise 2 et la plus petite unité en devise2
 * @param float $mnt1
 * @param char(3) $devise1
 * @param float $mnt2
 * @param char(3) $devise2
 * @return bool
 * @author Thomas Fastenakel
 */
function estEquivalent($mnt1, $devise1, $mnt2, $devise2) {
  $DEV1 = getInfoDevise($devise1);
  $DEV2 = getInfoDevise($devise2);
  $cv_mnt1 = calculeCV($devise1, $devise2, $mnt1);
  $unite_min_dev1 = pow(10, -$DEV1["precision"]);
  $cv_unite_min_dev1 = calculeCV($devise1, $devise2, $unite_min_dev1);
  $tolerance = max($cv_unite_min_dev1, pow(10, -$DEV2["precision"]));
  $borne_inf = round($mnt2 - $tolerance, $DEV2["precision"]);
  $borne_sup = round($mnt2 + $tolerance, $DEV2["precision"]);
  if ($borne_inf <= $cv_mnt1 && $cv_mnt1 <= $borne_sup) {
    return true;
  } else {
    return false;
  }
}

/**
 * Renvoie le montant qui sera prélevé au titre de commission lorsqu'une opérationd e change a lieu
 * @author Thomas FASTENAKEL
 * @param double $montant Montant à changer
 * @param char(3) $devise1 Code ISO de la devise de départ
 * @param char(3) $devise2 Code ISO de la devise de destination
 * @return double Montant de la commission
 */
function calculeCommissionChange($montant, $devise1, $devise2) {
  global $global_id_agence;
  $AG = getAgenceDatas($global_id_agence);
  $dev_ref = $AG["code_devise_reference"];

  // On vérifie d'abord si une commission doit bien etre prélevée
  if ($AG["comm_dev_ref"] == 'f') { // Si la commission ne doit pas etre prélevée sur le change de la devise de référence
    if (($devise1 == $dev_ref) || ($devise2 == $dev_ref))
      return 0;
  }

  // Recherhe de la C/V du minimum à payer dans la devise de la commission
  $contreval_comm_min = calculeCV($dev_ref, $devise1, $AG["mnt_min_comm_change"]);

  $prc_com = $AG['prc_comm_change'];
  //Constante de la commission de change
  if ($AG["constante_comm_change"]=="" || $AG["constante_comm_change"]==NULL)
    $contante_commission=0;
  else
    $contante_commission=$AG["constante_comm_change"];
  //Valeur maximale en tenant compte de la constante de la commission de change
  $mnt_commission = max($contreval_comm_min, $montant * $prc_com+$contante_commission);
  // Arrondi (supérieur) à la plus petite unité monétaire
  $DEV = getInfoDevise($devise1);
  $mnt_commission = round($mnt_commission, $DEV["precision"]);

  return $mnt_commission;
}

/**
 * Renvoie le montant qui sera prélevé au titre de taxe lors d'une opération de change.<BR>Ce montant est proportionnel à la commission
 * @author Thomas FASTENAKEL
 * @param double $montant Commission prélevée
 * @param char(3) $devise1 Code ISO de la devise de départ
 * @param char(3) $devise2 Code ISO de la devise de destination
 * @return double Montant de la taxe
 */
function calculeTaxeChange($montant, $devise1, $devise2) {

  global $global_id_agence;
  $AG = getAgenceDatas($global_id_agence);
  $dev_ref = $AG["code_devise_reference"];

  // On vérifie d'abord si une taxe doit bien etre prélevée
  if ($AG["tax_dev_ref"] == 'f')
    // Si la taxe ne doit pas etre prélevée sur le change de la devise de référence
  {
    if (($devise1 == $dev_ref) || ($devise2 == $dev_ref))
      return 0;
  }

  $prc_tax = $AG['prc_tax_change'];

  // Recherhe de la C/V du minimum à payer dans la devise de la taxe
  // $taux_dev_ref = getTauxChange($dev_ref, $devise1, false);
  // $contreval_tax_min = $AG["mnt_min_tax_change"] * $taux_dev_ref;

  // $mnt_tax = max($contreval_tax_min, $montant * $prc_tax);
  $mnt_tax = $montant * $prc_tax;

  // Arrondi (supérieur) à la plus petite unité monétaire
  $DEV = getInfoDevise($devise1);
  $mnt_tax = round($mnt_tax, $DEV["precision"]);

  return $mnt_tax;
}

/**
 * Sépare la taxe et la commission à partir de la commission nette
 * @param float $comm_nette Montant de la commission nette
 * @param char(3) $devise1 Code ISO de la devise de départ
 * @param char(3) $devise2 Code ISO de la devise de destination
 * @return Array Tableau ("commission" => Commission, "Taxe" => Taxe)
 */
function splitCommissionNette($comm_nette, $devise1, $devise2) {
  global $global_id_agence;
  $AG = getAgenceDatas($global_id_agence);
  $dev_ref = $AG["code_devise_reference"];

  $DEV1 = getInfoDevise($devise1);

  // On vérifie d'abord si une taxe doit bien etre prélevée
  if ($AG["tax_dev_ref"] == 'f')
    // Si la taxe ne doit pas etre prélevée sur le change de la devise de référence
  {
    if (($devise1 == $dev_ref) || ($devise2 == $dev_ref))
      return array("commission" => $comm_nette, "taxe" => 0);
  }

  $prc_tax = $AG['prc_tax_change'];

  $commission = round($comm_nette / (1+$prc_tax), $DEV1["precision"]);
  $taxe = round($prc_tax * $commission, $DEV1["precision"]);

  // Au cas où le montant ne tomberait pas tout à fait juste suite aux arrondis, ajouter ce qu'il faut du coté de la commission
  $diff = $comm_nette - ($commission + $taxe);
  if ($diff != 0)
    $commission += $diff;

  return array("commission" => $commission, "taxe" => $taxe);
}

/**
 * Renvoie le montant réalisé comme bénéfice (ou comme perte) en jouant sur le taux lors d'une opération de change
 * @author Thomas FASTENAKEL
 * @param double $montant Montant à changer
 * @param char(3) $devise1 Code ISO de la devise de départ
 * @param char(3) $devise2 Code ISO de la devise de destination
 * @param double $taux Taux de change utilisé pour l'opération
 * @param defined(1,2) $renvoi_devise 1 si résultat en devise1 et 2 si résultat en devise2
 * @return double Montant du bénéfice sur taux
 */
function calculeBeneficeTaux($montant, $devise1, $devise2, $taux, $renvoi_devise=1) {
  // Recherche du taux indicatif de conversion
  $taux_ind = getTauxChange($devise1, $devise2, false);

  if ($renvoi_devise == 1) {
    // Calcul du bénéfice
    $benef_taux = $montant * (($taux_ind - $taux) / $taux_ind);

    // Arrondi à la plus petite unité monétaire
    $DEV = getInfoDevise($devise1);
    debug("BNEF TAUX AVANT $benef_taux");
    $benef_taux = round($benef_taux, $DEV["precision"]);
  } else if ($renvoi_devise == 2) {
    // Calcul du bénéfice
    $benef_taux = $montant * ($taux_ind - $taux);

    // Arrondi à la plus petite unité monétaire
    $DEV = getInfoDevise($devise2);
    $benef_taux = round($benef_taux, $DEV["precision"]);
  }
  return $benef_taux;
}


/**
 * Calcule les informations suivantes sur une opération de change
 * <UL>
 *  <LI> Montant qui sera crédité au client </LI>
 *  <LI> Montant de la commission </LI>
 *  <LI> Montant de la taxe </LI>
 *  <LI> Montant du bénéfice pris en jouant sur le taux de change </LI>
 *  <LI> Montant qui sera réellement changé </LI>
 *  <LI> C/V en devise2 du montant qui sera réellement changé arrondi à la plus petit unité monétaire </LI>
 *  <LI> Différence entre la C/V réelle et la C/V arrondie </LI>
 *  <LI> Différence en devise de référence entre la C/V réelle et la C/V arrondie </LI>
 * </UL>
 * @author Thomas FASTENAKEL
 * @param double $montant Montant à changer
 * @param char(3) $devise1 Code ISO de la devise de départ
 * @param char(3) $devise2 Code ISO de la devise de destination
 * @param float $commissionnette Montant de la commission nette (= commission + taxe). Si non précisée, on la calcule suivant les règles définies dans ad_agc
 * @param double $taux Taux de change
 * @return array Tableau contenant les informations sus-mentionnées
 */
function getChangeInfos($montant, $devise1, $devise2, $commissionnette=NULL, $taux=NULL) {
  global $global_id_agence;
  global $global_monnaie_prec;
  $AG = getAgenceDatas($global_id_agence);
  $dev_ref = $AG["code_devise_reference"];
  $DEV2 = getInfoDevise($devise2);
  $DEV1 = getInfoDevise($devise1);
  // Calcul de la commission
  if (!isset($commissionnette)) {
    $commission = calculeCommissionChange($montant, $devise1, $devise2);
    // Calcul de la taxe
    $taxe = calculeTaxeChange($commission, $devise1, $devise2);
  } else {
    // Récupère la taxe et la commission en fonction du montant
    $SPLIT = splitCommissionNette($commissionnette, $devise1, $devise2);
    $commission = $SPLIT["commission"];
    $taxe = $SPLIT["taxe"];
  }
  $montant -= $commission;
  $montant -= $taxe;

  $montant_net_change = $montant;

  if (!isset($taux)) { // Si taux non précisé
    $taux = getTauxChange($devise1, $devise2, true, 1); // Par défaut : change CASH
  }

  $benef_taux = calculeBeneficeTaux($montant, $devise1, $devise2, $taux);
  $montant -= $benef_taux;

  // Retour des valeurs calculées
  $retour = array();
  $retour['commission'] = $commission;
  $retour['taxe'] = $taxe;
  $retour['benef_taux'] = $benef_taux;
  $retour['montant_reel_change'] = $montant;
  $retour['cv_montant_reel_change'] = round($montant_net_change * $taux, $DEV2["precision"]);
  // Recherche de la C/V arrondie au billet disponible dans la devise 2 inférieur le plus proche
  $retour["cv_billet"] = arrondiMonnaie($retour["cv_montant_reel_change"], -1, $devise2);
  $retour["diff_dev_2"] = $retour["cv_montant_reel_change"] - $retour["cv_billet"];
  $retour["diff_dev_ref"] = arrondiMonnaie(calculeCV($devise2, $dev_ref, $retour["diff_dev_2"]), 1, $dev_ref);
  // arrondi par rapport à la precision de la devise
  $retour["cv_billet"] =round($retour["cv_billet"],$DEV1["precision"]);
  $retour["diff_dev_ref"]=round($retour["diff_dev_ref"],$global_monnaie_prec);

  return $retour;
}

/**
 * Fonction effectuant une opération de change sans la perception de commissions.
 *
 * Elle n'est normalement jamais appelée directement.
 * @author Thomas FASTENAKEL
 * @param char(3) $devise_achat Code ISO de la devise achetée
 * @param char(3) $devise_vente Code ISO de la devise de la devise vendue
 * @param double $montant Montant à changer exprimé dans la devise d'achat
 * @param int $type_oper Le type d'opération ayant entrainé le change
 * @param array $subst Le tableau de substitution tel que passé à passageEcrituresComptablesAuto
 * @param &array $comptable Liste de mouvements comptables précédemment enregistrés
 * @param bool $mnt_debit Flag : true si $montant exprime le montant à débiter, false si $montant exprime le montant à créditer
 * @param float $cv Contre valeur (= Le montant au crédit), si elle n'est pas donnée on fait un appel à calculeCV {@see calculeCV}.
 * @return ErrorObj Avec en paramètre un array des montants au débit et au crédit si pas d'erreur, sinon le code de l'erreur.
 */
function effectueChangePrivate ($devise_achat, $devise_vente, $montant, $type_oper, $subst, &$comptable, $mnt_debit=true, $cv=NULL, $info_ecriture=NULL, $infos_sup=NULL, $date_comptable = NULL) {


  global $dbHandler;
  $db = $dbHandler->openConnection();
	 // Vérifie que les devises sont renseignées
  if ($devise_achat == '' || $devise_vente == '') {
  	$dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Devises non renseignées"));
  }
  if ($devise_achat == $devise_vente) {
    // Pas d'opération de change à réaliser
    $result = passageEcrituresComptablesAuto($type_oper, $montant, $comptable, $subst, $devise_achat, $date_comptable, $info_ecriture, $infos_sup);
    $montant_debit = $montant;
    $montant_credit = $montant;
  } else {
    if ($mnt_debit == true) {
      // $montant représente un montant à débiter en $devise_achat
      $montant_debit = $montant;
      if ($cv == NULL)
        $montant_credit = calculeCV($devise_achat, $devise_vente, $montant);
      else
        $montant_credit = $cv;
    } else {
      // $montant représente un montant à créditer en $devise_vente
      $montant_credit = $montant;
      if ($cv == NULL)
        $montant_debit = calculeCV($devise_vente, $devise_achat, $montant);
      else
        $montant_debit = $cv;
    }

    // On récupère la devise de référence
    global $global_monnaie;
    $dev_ref = $global_monnaie;

    // Passage des écritures relatives à la devise d'achat
    $cptes = $subst;
    if ($devise_achat != $dev_ref) {
      $cpt_devise=getCptesLies($devise_achat);
      $cptes["cpta"]["credit"] = $cpt_devise['position'];
    } else {
      $cpt_devise=getCptesLies($devise_vente);
      $cptes["cpta"]["credit"] = $cpt_devise['cvPosition'];
    }

    $cptes["int"]["credit"] = NULL;
    $result = passageEcrituresComptablesAuto($type_oper, $montant_debit, $comptable, $cptes, $devise_achat,$date_comptable,$info_ecriture,$infos_sup);
    if ($result->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $result;
    }

    // Passage des écritures relatives à la devise de vente
    $cptes = $subst;
    if ($devise_vente != $dev_ref) {
      $cpt_devise=getCptesLies($devise_vente);
      $cptes["cpta"]["debit"] = $cpt_devise['position'];
    } else {
      $cpt_devise=getCptesLies($devise_achat);
      $cptes["cpta"]["debit"] = $cpt_devise['cvPosition'];
    }
    $cptes["int"]["debit"] = NULL;
    $result = passageEcrituresComptablesAuto($type_oper, $montant_credit, $comptable, $cptes, $devise_vente,$date_comptable,$info_ecriture,$infos_sup);
    if ($result->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $result;
    }

    // Passage des écritures relatives à la devise de référence (intermédiaire)
    if (($devise_achat != $dev_ref) && ($devise_vente != $dev_ref)) {
      // Recherche de la CV en devise de référence
      $cv_montant_dev_ref = calculeCV($devise_achat, $dev_ref, $montant_debit);
      $cptes = $subst;
      $cpt_devise=getCptesLies($devise_achat);
      $cptes["cpta"]["debit"] = $cpt_devise['cvPosition'];
      $cptes["int"]["debit"] = NULL;
      $cpt_devise=getCptesLies($devise_vente);
      $cptes["cpta"]["credit"] = $cpt_devise['cvPosition'];
      $cptes["int"]["credit"] = NULL;
      $result = passageEcrituresComptablesAuto($type_oper, $cv_montant_dev_ref, $comptable, $cptes, $dev_ref,$date_comptable,$info_ecriture,$infos_sup);
    }
  }

  if ($result->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $result;
  }

  // Préparation des valeurs de retour
  $param_result = array("montant_debit" => $montant_debit, "montant_credit" => $montant_credit);
  $result = new ErrorObj(NO_ERR, $param_result);
  $dbHandler->closeConnection(true);
  return $result;
}

/**
 * Effectue une opération de change de haut niveau
 * Càd qu'elle perçoit d'abord les divers frais et commissions avant d'effectuer le change lui-mme
 * @author Thomas FASTENAKEL
 * @param char(3) $devise_achat Code ISO de la devise achetée
 * @param char(3) $devise2 Code ISO de la devise de la devise vendue
 * @param double $montant Montant à changer exprimé dans la devise d'achat
 * @param double $cv_montant Montant que l'on est censé obtenir après l'opération de change. Ce paramètre est nécessaire pour vérifier que l'utilisateur a utilisé correctement le logiciel.
 * @param int $type_oper Le type d'opération ayant entrainé le change
 * @param array $subst Le tableau de substitution tel que passé à passageEcrituresComptablesAuto
 * @param &array $comptable Liste de mouvements comptables précédemment enregistrés
 * @param defined(1,2,3) $destination_reste Indique que faire du reste de l'opération de change (1 = versement au guichet, 2 = versement sur compte de base, 3 = intégration dans produits)
 * @param double $commission Montant prélevé au titre de commission (optionnel)
 * @param double $taxe Montant prélevé au titre de taxe (optionnel)
 * @param float $taux : Taux appliqué afind e réaliser un bénéfice supplémentaire sur le taux
 * @param boolean $is_guichet : true si le change se fait au guichet
 * @param int info_ecriture : information de l'ecriture comtable à passer : (dans le cas de l'epargne le id_cpte du compte source)
 * @return array Array comptable à passer à ajout_historique
 */
function change($devise_achat, $devise_vente, $montant, $cv_montant, $type_oper, $subst, &$comptable, $destination_reste, $commissionnette=NULL, $taux=NULL,$is_guichet=NULL,$infos_sup=NULL) {
  global $global_monnaie;
  global $dbHandler;
  
  $db = $dbHandler->openConnection();
  $dev_ref = $global_monnaie;

  $DEV_A = getInfoDevise($devise_achat);
  $DEV_V = getInfoDevise($devise_vente);

  // Récupère la commission de change si non précisé
  if (!isset($commissionnette)) {
    $commission = calculeCommissionChange($montant, $devise_achat, $devise_vente);
    $taxe = calculeTaxeChange($commission, $devise_achat, $devise_vente);
  } else {
    $SPLIT = splitCommissionNette($commissionnette, $devise_achat, $devise_vente);
    $commission = $SPLIT["commission"];
    $taxe = $SPLIT["taxe"];
  }

  // Récupère le bénéfice sur le taux si le taux n'est pas précisé
  if (!isset($taux)) {
    $taux = getTauxChange($devise_achat, $devise_vente, true, 1);
  }
  $benef_taux = calculeBeneficeTaux(($montant - $commission - $taxe), $devise_achat, $devise_vente, $taux);

  // Prélèvement de la commission
  if ($commission > 0) {
    // Construction de l'array de substitution :
    // Compte au D = compte source du change
    $array_cptes = array();
    $array_cptes["int"]["debit"] = $subst["int"]["debit"];
    $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];

    if ($devise_achat == $dev_ref) { // Pas de mouvement de la position de change
      // Compte au C = compte de produit de commission de la devise de vente
      $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_commission"];

      $myErr = passageEcrituresComptablesAuto(450, $commission, $comptable, $array_cptes, $dev_ref,NULL,$info_ecriture);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    } else { // Mouvement de la position de change
      // Compte au C = compte de produit de commission de la devise d'achat
      $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_commission"];

      $myErr = effectueChangePrivate($devise_achat, $dev_ref, $commission, 450, $array_cptes, $comptable,TRUE,NULL,$info_ecriture);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }
  }

  // Prélèvement de la taxe
  if ($taxe > 0) {
    // Construction de l'array de substitution
    $array_cptes = array();
    $array_cptes["int"]["debit"] = $subst["int"]["debit"];
    $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];

    if ($devise_achat == $dev_ref) { // Pas de mouvement de la position de change
      $myErr = passageEcrituresComptablesAuto(451, $taxe, $comptable, $array_cptes, $dev_ref);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    } else { // Mouvement de la position de change
      $myErr = effectueChangePrivate($devise_achat, $dev_ref, $taxe, 451, $array_cptes, $comptable);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }
  }

  // Prélèvement du bénéfice par jeu sur le taux
  if ($benef_taux > 0) {
    // Construction de l'array de substitution
    $array_cptes = array();
    $array_cptes["int"]["debit"] = $subst["int"]["debit"];
    $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];

    if ($devise_achat == $dev_ref) { // Pas de mouvement de la position de change
      // Compte au C = compte de produit de commission de la devise de vente
      $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_taux"];

      $myErr = passageEcrituresComptablesAuto(452, $benef_taux, $comptable, $array_cptes, $dev_ref);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    } else { // Mouvement de la position de change
      // Compte au C = compte de produit de commission de la devise d'achat
      $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_taux"];
    
      $myErr = effectueChangePrivate($devise_achat, $dev_ref, $benef_taux, 452, $array_cptes, $comptable);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }
  } else if ($benef_taux < 0) { // Cas d'une vente de devise à perte
    // On va plutot exprimer $benef_taux dans la devise vendue
    $benef_taux_dev_vente = calculeBeneficeTaux(($montant - $commission - $taxe), $devise_achat, $devise_vente, $taux, 2);

    // Sera utilisé ultérieurement
    $perte_change = abs($benef_taux_dev_vente);

    // Construction de l'array de substitution
    $array_cptes = array();
    $array_cptes["int"]["credit"] = $subst["int"]["credit"];
    $array_cptes["cpta"]["credit"] = $subst["cpta"]["credit"];

    if ($devise_vente == $dev_ref) { // Pas de mouvement de la position de change
      // Compte au D = compte de perte de change de la devise
      $array_cptes["cpta"]["debit"] = $DEV_V["cpte_perte_taux"];
      $myErr = passageEcrituresComptablesAuto(458, abs($benef_taux_dev_vente), $comptable, $array_cptes, $dev_ref);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    } else { // Mouvement de la position de change
      // Compte au D = compte de perte de change
      $array_cptes["cpta"]["debit"] = $DEV_V["cpte_perte_taux"];
      $myErr = effectueChangePrivate($dev_ref, $devise_vente, abs($benef_taux_dev_vente), 458, $array_cptes, $comptable, false);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }
    // La perte ayant déjà été comptabilisée, la suite se déroule comme si on changeait au taux indicatif
    $benef_taux = 0;
    $taux = getTauxChange($devise_achat, $devise_vente, false);
    $cv_montant = calculeCV($devise_achat, $devise_vente, $montant - $commission - $taxe);
  }

  // Opération de change proprement dite

  // Le montant brut = le montant à changer uen fois les commissions et taxes retirées
  $mnt_brut = $montant - $commission - $taxe;
  // Le montant réel à changer = le montant réel qui est changé (par rapport au taux indicatif)
  $mnt_change = $mnt_brut - $benef_taux;
  // La C/V calculée = la C/V du montant réellement changée
  $cv_calculee = calculeCV($devise_achat, $devise_vente, $mnt_change);
  // On peut aussi calculer la C/V en diminuant le risque d'erreur sur base du montant brut
  $cv_calculee_rapport_mnt_brut = round(($mnt_brut * $taux), $DEV_V["precision"]);

  // Vérifie que ce montant est bien conforme aux attentes
  // Si il s'agit d'une opération au guichet, la C/V passée est une C/V arrondie inférieure au plus petit billet. Il faut donc y ajouter le reste en devise vente avant de faire le test d'équivalence
  $cpt_credit = $subst["cpta"]["credit"];
  if (isCompteGuichet($cpt_credit) || $is_guichet) {
    $reste_dev_vente = ($cv_calculee_rapport_mnt_brut + $perte_change) - (arrondiMonnaie($cv_calculee_rapport_mnt_brut + $perte_change, -1, $devise_vente));
    if (round($reste_dev_vente, $DEV_V["precision"] > 0)) { // Il y a un reliquiat à traiter
      // On cherche la C/V en devise de référence de ce reste
      $reste_dev_ref = calculeCV($devise_vente, $dev_ref, $reste_dev_vente);
      // On ne fait le change que si le montant peut tre remis au client
      $reste_dev_ref_arrondi_billet = arrondiMonnaie($reste_dev_ref, -1, $dev_ref);
    } else {
      $reste_dev_ref_arrondi_billet = 0;
    }
  } else {
    $reste_dev_ref_arrondi_billet = 0;
  }

  if (!estEquivalent($mnt_change, $devise_achat, $cv_montant + $reste_dev_vente, $devise_vente)) {
    // Quelque chose n'est pas clair ...
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_MNT_NON_EQUIV, _("pas d'équivalence'"));

  } else { // A partir de maintenant c'est $cv_montant qui fera foi
    $cv_mnt_change = $cv_montant;
  }

  debug($reste_dev_ref_arrondi_billet, "reste_dev_ref_arrondi_billet");

  //  *********** Gestion des arrondis *********
  if ($reste_dev_ref_arrondi_billet > 0) {
    $array_cptes = array();
    $array_cptes["int"]["debit"] = $subst["int"]["debit"];
    $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];

    // Recherche du montant à changer dans la devise de départ
    $cv_reste_dev_achat = calculeCV($devise_vente, $devise_achat, $reste_dev_vente);
    switch ($destination_reste) {
    case 1:              // Versement au guichet
      global $global_id_guichet;
      $id_gui = $global_id_guichet;  // FIXME Je sais que je ne devrais pas ...
      $array_cptes["cpta"]["credit"] = getCompteCptaGui($id_gui);
      $type_oper_rest = 455;
      break;
    case 2:              // Versement sur compte de base
      global $global_id_client; // FIXME Je sais que je ne devrais pas ...
      $id_cpt_base = getBaseAccountID($global_id_client);
      $array_cptes["cpta"]["credit"] = getCompteCptaProdEp($id_cpt_base);
      if ($array_cptes["cpta"]["credit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
      }
      $array_cptes["int"]["credit"] = $id_cpt_base;
      $type_oper_rest = 456;
      break;
    case 3:
      $type_oper_rest = 457;
      break;
    default:
      $dbHandler->closeConnetion(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "le paramètre 'destination_change' est incorrect : $destination_change"
    }
    $myErr = effectueChangePrivate($devise_achat, $dev_ref, $cv_reste_dev_achat, $type_oper_rest, $array_cptes, $comptable);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
    $mnt_change -= $cv_reste_dev_achat;
  }

  $myErr = effectueChangePrivate($devise_achat, $devise_vente, $mnt_change, $type_oper, $subst, $comptable, true, $cv_montant, NULL, $infos_sup);

  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Effectue une opération de change de haut niveau sur l'agence local lor d'un depot en deplacé
 * Càd qu'elle perçoit d'abord les divers frais et commissions avant d'effectuer le change lui-mme
 *
 * @author BD
 * @param char(3) $devise_achat
 *            Code ISO de la devise achetée
 * @param char(3) $devise2
 *            Code ISO de la devise de la devise vendue
 * @param double $montant
 *            Montant à changer exprimé dans la devise d'achat
 * @param double $cv_montant
 *            Montant que l'on est censé obtenir après l'opération de change. Ce paramètre est nécessaire pour vérifier que l'utilisateur a utilisé correctement le logiciel.
 * @param int $type_oper
 *            Le type d'opération ayant entrainé le change
 * @param array $subst
 *            Le tableau de substitution tel que passé à passageEcrituresComptablesAuto
 * @param &array $comptable
 *            Liste de mouvements comptables précédemment enregistrés
 * @param defined(1,2,3) $destination_reste
 *            Indique que faire du reste de l'opération de change (1 = versement au guichet, 2 = versement sur compte de base, 3 = intégration dans produits)
 * @param double $commission
 *            Montant prélevé au titre de commission (optionnel)
 * @param double $taxe
 *            Montant prélevé au titre de taxe (optionnel)
 * @param float $taux
 *            : Taux appliqué afind e réaliser un bénéfice supplémentaire sur le taux
 * @param boolean $is_guichet
 *            : true si le change se fait au guichet
 * @param
 *            int info_ecriture : information de l'ecriture comtable à passer : (dans le cas de l'epargne le id_cpte du compte source)
 * @return array Array comptable à passer à ajout_historique
 */
function changeDepotLocalAvecCommissions($devise_achat, $devise_vente, $montant, $cv_montant, $type_oper, $subst, &$comptable, $destination_reste, $commissionnette = NULL, $taux = NULL, $is_guichet = NULL, $infos_sup = NULL)
{
    global $global_monnaie;
    global $dbHandler;
    
    $db = $dbHandler->openConnection();
    
    $dev_ref = $global_monnaie;
    
    $DEV_A = getInfoDevise($devise_achat);
    $DEV_V = getInfoDevise($devise_vente);
    
    // Récupère la commission de change si non précisé
    if (! isset($commissionnette)) {
        $commission = calculeCommissionChange($montant, $devise_achat, $devise_vente);
        $taxe = calculeTaxeChange($commission, $devise_achat, $devise_vente);
    } else {
        $SPLIT = splitCommissionNette($commissionnette, $devise_achat, $devise_vente);
        $commission = $SPLIT["commission"];
        $taxe = $SPLIT["taxe"];
    }
    
    // Récupère le bénéfice sur le taux si le taux n'est pas précisé
    if (! isset($taux)) {
        $taux = getTauxChange($devise_achat, $devise_vente, true, 1);
    }
    $benef_taux = calculeBeneficeTaux(($montant - $commission - $taxe), $devise_achat, $devise_vente, $taux);
    
    // Prélèvement de la commission
    if ($commission > 0) {
        // Construction de l'array de substitution :
        // Compte au D = compte source du change
        $array_cptes = array();
        $array_cptes["int"]["debit"] = $subst["int"]["debit"];
        $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
        
        if ($devise_achat == $dev_ref) { // Pas de mouvement de la position de change
            // Compte au C = compte de produit de commission de la devise de vente
            $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_commission"];
            
            $myErr = passageEcrituresComptablesAuto(450, $commission, $comptable, $array_cptes, $dev_ref, NULL, $info_ecriture);
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        } else { // Mouvement de la position de change
                 // Compte au C = compte de produit de commission de la devise d'achat
            $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_commission"];
            
            $myErr = effectueChangePrivate($devise_achat, $dev_ref, $commission, 450, $array_cptes, $comptable, TRUE, NULL, $info_ecriture);
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }
    }
    
    // Prélèvement de la taxe
/*     if ($taxe > 0) {
        // Construction de l'array de substitution
        $array_cptes = array();
        $array_cptes["int"]["debit"] = $subst["int"]["debit"];
        $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
        
        if ($devise_achat == $dev_ref) { // Pas de mouvement de la position de change
            $myErr = passageEcrituresComptablesAuto(451, $taxe, $comptable, $array_cptes, $dev_ref);
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        } else { // Mouvement de la position de change
            $myErr = effectueChangePrivate($devise_achat, $dev_ref, $taxe, 451, $array_cptes, $comptable);
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }
    }
     */
    // Prélèvement du bénéfice par jeu sur le taux
    /* if ($benef_taux > 0) {
        // Construction de l'array de substitution
        $array_cptes = array();
        $array_cptes["int"]["debit"] = $subst["int"]["debit"];
        $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
        
        if ($devise_achat == $dev_ref) { // Pas de mouvement de la position de change
                                         // Compte au C = compte de produit de commission de la devise de vente
            $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_taux"];
            
            $myErr = passageEcrituresComptablesAuto(452, $benef_taux, $comptable, $array_cptes, $dev_ref);
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        } else { // Mouvement de la position de change
                 // Compte au C = compte de produit de commission de la devise d'achat
            $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_taux"];
            
            $myErr = effectueChangePrivate($devise_achat, $dev_ref, $benef_taux, 452, $array_cptes, $comptable);
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }
    } else 
        if ($benef_taux < 0) { // Cas d'une vente de devise à perte
                                      // On va plutot exprimer $benef_taux dans la devise vendue
            $benef_taux_dev_vente = calculeBeneficeTaux(($montant - $commission - $taxe), $devise_achat, $devise_vente, $taux, 2);
            
            // Sera utilisé ultérieurement
            $perte_change = abs($benef_taux_dev_vente);
            
            // Construction de l'array de substitution
            $array_cptes = array();
            $array_cptes["int"]["credit"] = $subst["int"]["credit"];
            $array_cptes["cpta"]["credit"] = $subst["cpta"]["credit"];
            
            if ($devise_vente == $dev_ref) { // Pas de mouvement de la position de change
                                             // Compte au D = compte de perte de change de la devise
                $array_cptes["cpta"]["debit"] = $DEV_V["cpte_perte_taux"];
                $myErr = passageEcrituresComptablesAuto(458, abs($benef_taux_dev_vente), $comptable, $array_cptes, $dev_ref);
                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            } else { // Mouvement de la position de change
                     // Compte au D = compte de perte de change
                $array_cptes["cpta"]["debit"] = $DEV_V["cpte_perte_taux"];
                $myErr = effectueChangePrivate($dev_ref, $devise_vente, abs($benef_taux_dev_vente), 458, $array_cptes, $comptable, false);
                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }
            // La perte ayant déjà été comptabilisée, la suite se déroule comme si on changeait au taux indicatif
            $benef_taux = 0;
            $taux = getTauxChange($devise_achat, $devise_vente, false);
            $cv_montant = calculeCV($devise_achat, $devise_vente, $montant - $commission - $taxe);
        } */
    
    
    
    // Opération de change proprement dite
    
    // Le montant brut = le montant à changer uen fois les commissions et taxes retirées
    $mnt_brut = $montant - $commission - $taxe;
    // Le montant réel à changer = le montant réel qui est changé (par rapport au taux indicatif)
    $mnt_change = $mnt_brut - $benef_taux;
    // La C/V calculée = la C/V du montant réellement changée
    $cv_calculee = calculeCV($devise_achat, $devise_vente, $mnt_change);
    // On peut aussi calculer la C/V en diminuant le risque d'erreur sur base du montant brut
    $cv_calculee_rapport_mnt_brut = round(($mnt_brut * $taux), $DEV_V["precision"]);
    
    // Vérifie que ce montant est bien conforme aux attentes
    // Si il s'agit d'une opération au guichet, la C/V passée est une C/V arrondie inférieure au plus petit billet. Il faut donc y ajouter le reste en devise vente avant de faire le test d'équivalence
    $cpt_credit = $subst["cpta"]["credit"];
    if (isCompteGuichet($cpt_credit) || $is_guichet) {
        $reste_dev_vente = ($cv_calculee_rapport_mnt_brut + $perte_change) - (arrondiMonnaie($cv_calculee_rapport_mnt_brut + $perte_change, - 1, $devise_vente));
        if (round($reste_dev_vente, $DEV_V["precision"] > 0)) { // Il y a un reliquiat à traiter
                                                                // On cherche la C/V en devise de référence de ce reste
            $reste_dev_ref = calculeCV($devise_vente, $dev_ref, $reste_dev_vente);
            // On ne fait le change que si le montant peut tre remis au client
            $reste_dev_ref_arrondi_billet = arrondiMonnaie($reste_dev_ref, - 1, $dev_ref);
        } else {
            $reste_dev_ref_arrondi_billet = 0;
        }
    } else {
        $reste_dev_ref_arrondi_billet = 0;
    }
    
    if (! estEquivalent($mnt_change, $devise_achat, $cv_montant + $reste_dev_vente, $devise_vente)) {
        // Quelque chose n'est pas clair ...
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_MNT_NON_EQUIV, _("pas d'équivalence'"));
    } else { // A partir de maintenant c'est $cv_montant qui fera foi
        $cv_mnt_change = $cv_montant;
    }
    
    debug($reste_dev_ref_arrondi_billet, "reste_dev_ref_arrondi_billet");
    
    // *********** Gestion des arrondis *********
    if ($reste_dev_ref_arrondi_billet > 0) {
        $array_cptes = array();
        $array_cptes["int"]["debit"] = $subst["int"]["debit"];
        $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
        
        // Recherche du montant à changer dans la devise de départ
        $cv_reste_dev_achat = calculeCV($devise_vente, $devise_achat, $reste_dev_vente);
        switch ($destination_reste) {
            case 1: // Versement au guichet
                global $global_id_guichet;
                $id_gui = $global_id_guichet; // FIXME Je sais que je ne devrais pas ...
                $array_cptes["cpta"]["credit"] = getCompteCptaGui($id_gui);
                $type_oper_rest = 455;
                break;
            case 2: // Versement sur compte de base
                global $global_id_client; // FIXME Je sais que je ne devrais pas ...
                $id_cpt_base = getBaseAccountID($global_id_client);
                $array_cptes["cpta"]["credit"] = getCompteCptaProdEp($id_cpt_base);
                if ($array_cptes["cpta"]["credit"] == NULL) {
                    $dbHandler->closeConnection(false);
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                }
                $array_cptes["int"]["credit"] = $id_cpt_base;
                $type_oper_rest = 456;
                break;
            case 3:
                $type_oper_rest = 457;
                break;
            default:
                $dbHandler->closeConnetion(false);
                signalErreur(__FILE__, __LINE__, __FUNCTION__); // "le paramètre 'destination_change' est incorrect : $destination_change"
        }
        $myErr = effectueChangePrivate($devise_achat, $dev_ref, $cv_reste_dev_achat, $type_oper_rest, $array_cptes, $comptable);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }
        $mnt_change -= $cv_reste_dev_achat;
    }
    
    $myErr = effectueChangePrivate($devise_achat, $devise_achat, $mnt_change, $type_oper, $subst, $comptable, true, $cv_montant, NULL, $infos_sup);
        
    if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
    }
    
    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);
}

/**
 * Effectue une opération de change de haut niveau sur l'agence local lor d'un retrait en deplacé
 * Càd qu'elle perçoit d'abord les divers frais et commissions avant d'effectuer le change lui-mme
 *
 * @author BD
 * @param char(3) $devise_achat
 *            Code ISO de la devise achetée
 * @param char(3) $devise2
 *            Code ISO de la devise de la devise vendue
 * @param double $montant
 *            Montant à changer exprimé dans la devise d'achat
 * @param double $cv_montant
 *            Montant que l'on est censé obtenir après l'opération de change. Ce paramètre est nécessaire pour vérifier que l'utilisateur a utilisé correctement le logiciel.
 * @param int $type_oper
 *            Le type d'opération ayant entrainé le change
 * @param array $subst
 *            Le tableau de substitution tel que passé à passageEcrituresComptablesAuto
 * @param &array $comptable
 *            Liste de mouvements comptables précédemment enregistrés
 * @param defined(1,2,3) $destination_reste
 *            Indique que faire du reste de l'opération de change (1 = versement au guichet, 2 = versement sur compte de base, 3 = intégration dans produits)
 * @param double $commission
 *            Montant prélevé au titre de commission (optionnel)
 * @param double $taxe
 *            Montant prélevé au titre de taxe (optionnel)
 * @param float $taux
 *            : Taux appliqué afind e réaliser un bénéfice supplémentaire sur le taux
 * @param boolean $is_guichet
 *            : true si le change se fait au guichet
 * @param
 *            int info_ecriture : information de l'ecriture comtable à passer : (dans le cas de l'epargne le id_cpte du compte source)
 * @return array Array comptable à passer à ajout_historique
 */
function changeRetraitLocalAvecCommissions($devise_achat, $devise_vente, $montant, $cv_montant, $type_oper, $subst, &$comptable, $destination_reste, $commissionnette = NULL, $taux = NULL, $is_guichet = NULL, $infos_sup = NULL)
{
    global $global_monnaie;
    global $dbHandler;

    $db = $dbHandler->openConnection();
    
    $dev_ref = $global_monnaie;
    
    $DEV_A = getInfoDevise($devise_achat);
    $DEV_V = getInfoDevise($devise_vente);
   
    // Init :
    $commission = 0;
    $taxe = 0;
    $benef_taux = 0;
    
    // Récupère la commission de change si non précisé
    if (! isset($commissionnette)) {
        $commission = calculeCommissionChange($montant, $devise_achat, $devise_vente);
        $taxe = calculeTaxeChange($commission, $devise_achat, $devise_vente);
    } else {
        $SPLIT = splitCommissionNette($commissionnette, $devise_achat, $devise_vente);
        $commission = $SPLIT["commission"];
        $taxe = $SPLIT["taxe"];
    }
        
    // Récupère le bénéfice sur le taux si le taux n'est pas précisé
    if (! isset($taux)) {
        $taux = getTauxChange($devise_achat, $devise_vente, true, 1);
    }
    $benef_taux = calculeBeneficeTaux(($montant - $commission - $taxe), $devise_achat, $devise_vente, $taux);
    
    // Prélèvement de la commission
    if ($commission > 0) {
        // Construction de l'array de substitution :
        // Compte au D = compte source du change
        $array_cptes = array();
        //$array_cptes["int"]["debit"] = $subst["int"]["debit"];
        $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
        
        // Pas de mouvement de la position de change
        if ($devise_achat == $dev_ref) { // retrait
            // Compte au C = compte de produit de commission de la devise de vente
            $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_commission"];
            
            $myErr = passageEcrituresComptablesAuto(450, $commission, $comptable, $array_cptes, $dev_ref, NULL, $info_ecriture);

            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        } else { // Mouvement de la position de change
            
            // @todo : DELETE ??            
            // Compte au C = compte de produit de commission de la devise d'achat
           /*  $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_commission"];
            $myErr = effectueChangePrivate($devise_achat, $dev_ref, $commission, 450, $array_cptes, $comptable, TRUE, NULL, $info_ecriture);
                                   
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            } */
        }
    }
    
    // Prélèvement de la taxe
    /*
     * if ($taxe > 0) { // Construction de l'array de substitution $array_cptes = array(); $array_cptes["int"]["debit"] = $subst["int"]["debit"]; $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"]; if ($devise_achat == $dev_ref) { // Pas de mouvement de la position de change $myErr = passageEcrituresComptablesAuto(451, $taxe, $comptable, $array_cptes, $dev_ref); if ($myErr->errCode != NO_ERR) { $dbHandler->closeConnection(false); return $myErr; } } else { // Mouvement de la position de change $myErr = effectueChangePrivate($devise_achat, $dev_ref, $taxe, 451, $array_cptes, $comptable); if ($myErr->errCode != NO_ERR) { $dbHandler->closeConnection(false); return $myErr; } } }
     */
    // Prélèvement du bénéfice par jeu sur le taux
    /*
     * if ($benef_taux > 0) { // Construction de l'array de substitution $array_cptes = array(); $array_cptes["int"]["debit"] = $subst["int"]["debit"]; $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"]; if ($devise_achat == $dev_ref) { // Pas de mouvement de la position de change // Compte au C = compte de produit de commission de la devise de vente $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_taux"]; $myErr = passageEcrituresComptablesAuto(452, $benef_taux, $comptable, $array_cptes, $dev_ref); if ($myErr->errCode != NO_ERR) { $dbHandler->closeConnection(false); return $myErr; } } else { // Mouvement de la position de change // Compte au C = compte de produit de commission de la devise d'achat $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_taux"]; $myErr = effectueChangePrivate($devise_achat, $dev_ref, $benef_taux, 452, $array_cptes, $comptable); if ($myErr->errCode != NO_ERR) { $dbHandler->closeConnection(false); return $myErr; } } } else if ($benef_taux < 0) { // Cas d'une vente de devise à perte // On va plutot exprimer $benef_taux dans la devise vendue $benef_taux_dev_vente = calculeBeneficeTaux(($montant - $commission - $taxe), $devise_achat, $devise_vente, $taux, 2); // Sera utilisé ultérieurement $perte_change = abs($benef_taux_dev_vente); // Construction de l'array de substitution $array_cptes = array(); $array_cptes["int"]["credit"] = $subst["int"]["credit"]; $array_cptes["cpta"]["credit"] = $subst["cpta"]["credit"]; if ($devise_vente == $dev_ref) { // Pas de mouvement de la position de change // Compte au D = compte de perte de change de la devise $array_cptes["cpta"]["debit"] = $DEV_V["cpte_perte_taux"]; $myErr = passageEcrituresComptablesAuto(458, abs($benef_taux_dev_vente), $comptable, $array_cptes, $dev_ref); if ($myErr->errCode != NO_ERR) { $dbHandler->closeConnection(false); return $myErr; } } else { // Mouvement de la position de change // Compte au D = compte de perte de change $array_cptes["cpta"]["debit"] = $DEV_V["cpte_perte_taux"]; $myErr = effectueChangePrivate($dev_ref, $devise_vente, abs($benef_taux_dev_vente), 458, $array_cptes, $comptable, false); if ($myErr->errCode != NO_ERR) { $dbHandler->closeConnection(false); return $myErr; } } // La perte ayant déjà été comptabilisée, la suite se déroule comme si on changeait au taux indicatif $benef_taux = 0; $taux = getTauxChange($devise_achat, $devise_vente, false); $cv_montant = calculeCV($devise_achat, $devise_vente, $montant - $commission - $taxe); }
     */
   
    
    // Opération de change proprement dite :
    
    // Le montant brut = le montant à changer uen fois les commissions et taxes retirées
    $mnt_brut = $montant - $commission - $taxe;
    // Le montant réel à changer = le montant réel qui est changé (par rapport au taux indicatif)
    $mnt_change = $mnt_brut - $benef_taux;    
    // La C/V calculée = la C/V du montant réellement changée
    $cv_calculee = calculeCV($devise_achat, $devise_vente, $mnt_change);
       
    // On peut aussi calculer la C/V en diminuant le risque d'erreur sur base du montant brut
    $cv_calculee_rapport_mnt_brut = round(($mnt_brut * $taux), $DEV_V["precision"]);
      
    // Vérifie que ce montant est bien conforme aux attentes
    // Si il s'agit d'une opération au guichet, la C/V passée est une C/V arrondie inférieure au plus petit billet. Il faut donc y ajouter le reste en devise vente avant de faire le test d'équivalence
    $cpt_credit = $subst["cpta"]["credit"];
    
    if (isCompteGuichet($cpt_credit) || $is_guichet) {
        $reste_dev_vente = ($cv_calculee_rapport_mnt_brut + $perte_change) - (arrondiMonnaie($cv_calculee_rapport_mnt_brut + $perte_change, - 1, $devise_vente));
        if (round($reste_dev_vente, $DEV_V["precision"] > 0)) { // Il y a un reliquiat à traiter
                                                                // On cherche la C/V en devise de référence de ce reste
            $reste_dev_ref = calculeCV($devise_vente, $dev_ref, $reste_dev_vente);
            // On ne fait le change que si le montant peut tre remis au client
            $reste_dev_ref_arrondi_billet = arrondiMonnaie($reste_dev_ref, - 1, $dev_ref);
        } else {
            $reste_dev_ref_arrondi_billet = 0;
        }
    } else {
        $reste_dev_ref_arrondi_billet = 0;
    }  
    
    $isEquivalent = estEquivalent($mnt_change, $devise_achat, $cv_montant + $reste_dev_vente, $devise_vente);
   
    if (! $isEquivalent) {
        // Quelque chose n'est pas clair ...
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_MNT_NON_EQUIV, _("pas d'équivalence'"));
    } else { // A partir de maintenant c'est $cv_montant qui fera foi
        $cv_mnt_change = $cv_montant;
    }
    
    debug($reste_dev_ref_arrondi_billet, "reste_dev_ref_arrondi_billet");
    
    // *********** Gestion des arrondis *********
    if ($reste_dev_ref_arrondi_billet > 0) {
        $array_cptes = array();
        $array_cptes["int"]["debit"] = $subst["int"]["debit"];
        $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
        
        // Recherche du montant à changer dans la devise de départ
        $cv_reste_dev_achat = calculeCV($devise_vente, $devise_achat, $reste_dev_vente);
        switch ($destination_reste) {
            case 1: // Versement au guichet
                global $global_id_guichet;
                $id_gui = $global_id_guichet; // FIXME Je sais que je ne devrais pas ...
                $array_cptes["cpta"]["credit"] = getCompteCptaGui($id_gui);
                $type_oper_rest = 455;
                break;
            case 2: // Versement sur compte de base
                global $global_id_client; // FIXME Je sais que je ne devrais pas ...
                $id_cpt_base = getBaseAccountID($global_id_client);
                $array_cptes["cpta"]["credit"] = getCompteCptaProdEp($id_cpt_base);
                if ($array_cptes["cpta"]["credit"] == NULL) {
                    $dbHandler->closeConnection(false);
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                }
                $array_cptes["int"]["credit"] = $id_cpt_base;
                $type_oper_rest = 456;
                break;
            case 3:
                $type_oper_rest = 457;
                break;
            default:
                $dbHandler->closeConnetion(false);
                signalErreur(__FILE__, __LINE__, __FUNCTION__); // "le paramètre 'destination_change' est incorrect : $destination_change"
        }
        $myErr = effectueChangePrivate($devise_achat, $dev_ref, $cv_reste_dev_achat, $type_oper_rest, $array_cptes, $comptable);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }
        $mnt_change -= $cv_reste_dev_achat;
    }   
    
    $result = passageEcrituresComptablesAuto($type_oper, $cv_montant, $comptable, $subst, $devise_vente, NULL, NULL,$infos_sup);
    if ($result->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $result;
    }
        
    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);
}

/**
 * Vérifie si le compte $num_cpte peut être mouvementé dans la devise $devise
 * Impossibile si le compte possède déjà une devise différente de la devise $devise
 * Si le compte n'a pas de devise assignée,
 * création d'un sous-compte dans la devise désirée si ce dernier est inexistant
 * @author Thomas FASTENAKEL
 * @param text $num_cpte Numéro du compte
 * @param char(3) $devise Code ISO de la devise du mouvement
 * @return text Numéro du compte à mouvementer ou NULL si mouvement impossible
 */
function checkCptDeviseOK($num_cpte, $devise) {
  global $global_multidevise, $error;
  global $global_id_agence;
  if ($global_multidevise) {
    // Recherche des infos sur le compte
    $ACC = getComptesComptables(array("num_cpte_comptable" => $num_cpte));
    //debug($ACC,"acc");
    if (sizeof($ACC) != 1) {
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $ACC = $ACC[$num_cpte];

    // Si le compte a une devise associée, alors vérifier que c'est la même que celle de l'opération
    if (isset($ACC["devise"])) {
      if ($ACC["devise"] == $devise)
        return $num_cpte;
      else {
        return NULL;
      }
    } else {
      // Chercher si le compte possède un sous-compte dans la devise renseignée
      $ACC2 = getComptesComptables(array("cpte_centralise" => $num_cpte, "devise" => $devise));
      if (count($ACC2) == 1) {
        $ACC  = array_pop($ACC2);
        return $ACC["num_cpte_comptable"];
      } else if (count($ACC2) == 0) {
        // Création du sous-compte dans la devise de l'écriture
        $sscomptes = array();
        $sscompte = array();
        $sscompte["num_cpte_comptable"] = $num_cpte.".$devise";
        $sscompte["libel_cpte_comptable"] = $ACC["libel_cpte_comptable"]."-$devise";
        $sscompte["solde"] = 0;
        $sscompte["devise"] = $devise;
        $sscomptes[$num_cpte.".$devise"] = $sscompte;

        $myErr = ajoutSousCompteComptable($num_cpte, $sscomptes);
        if ($myErr->errCode != NO_ERR) {
          debug(sprintf(_("Problème lors de la création du sous-compte %s"),$num_cpte.$devise)." : ".$error[$myErr->errCode]);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
        } else
          return $num_cpte.".".$devise;
      } else
        signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Au moins deux sous-comptes du compte %s existent dans la devise %s"),$num_cpte,$devise));
      return $num_cpte;
    }
  } else
    return $num_cpte;
}

/**
 * Renvoie les valeurs associées à la position de change :
 * <ul>
 *  <li>Position nette</li>
 *  <li>Contre-valeur de la position nette</li>
 *  <li>Variation des taux</li>
 * </ul>
 * @author Bernard DE BOIS
 * @param char(3) $devise Code ISO de la devise
 * @return array contenant les trois montants ci-dessus.
 */
function getInfoPosition($devise) {
  global $global_id_agence;
  $montant=array();
  $comptes=getCptesLies($devise);
  $cptPosition=getComptesComptables(array("num_cpte_comptable" => $comptes['position']));
  $cptCv=getComptesComptables(array("num_cpte_comptable" => $comptes['cvPosition']));
  $cptDebit=getComptesComptables(array("num_cpte_comptable" => $comptes['debit']));
  $cptCredit=getComptesComptables(array("num_cpte_comptable" => $comptes['credit']));
  $montant['position']=$cptPosition[$comptes['position']]['solde'];
  $montant['cv']=-$cptCv[$comptes['cvPosition']]['solde'];
  $montant['varTx']=$cptDebit[$comptes['debit']]['solde']+$cptCredit[$comptes['credit']]['solde'];
  return $montant;
}


/**
 * Affiche un taux et/ou une valeur de manière synthétique.
 * S'il s'agit d'un nombre > 1, il affichera le nombre avec le séparateur de millier, de décimale et autant de chiffres derrière la virgule que $precision_taux.
 * S'il s'agit d'un nombre < 1, il n'affichera que certain nombre de chiffres significatifs après le dernier zéro (selon la valeur de $precision_taux).
 * 0,00001534748975165 sera transformé en 0,0000153 (si $precision_taux = 3).
 * <li>Contre-valeur de la position nette</li>
 * <li>Variation des taux</li>
 * @author Bernard DE BOIS
 * @param float $valeur : taux à afficher
 * @param int $nombre : nombre de chiffres significatifs à afficher (si celui-ci n'est pas renseigné, il prend $precision_taux)
 * @return string le taux mis en forme.
 */
function affTx($valeur,$nombre=NULL) {
  global $precision_taux;
  global $mnt_sep_mil;
  global $mnt_sep_dec;
  if ($nombre==NULL) {
    $nombre=$precision_taux;
  }
  if ($valeur>=1) {
    $nValeur=number_format($valeur,$nombre,$mnt_sep_dec,$mnt_sep_mil);
  } else {
    $i=0;
    $tValeur=$valeur;
    while ($tValeur<1 && $i<12) {
      $i++;
      $tValeur=$tValeur*10;
    }
    $nValeur=number_format($valeur,($i+$nombre-1),$mnt_sep_dec,$mnt_sep_mil);
  }
//la boucle suivante supprime les zéros après la virgule (et la virgule elle-même, s'il n'y a pas de décimale)
  while (((strrpos($nValeur,'0')==(strlen($nValeur)-1)) && (strpos($nValeur,','))) || ((strpos($nValeur,',')==(strlen($nValeur)-1)) && (strlen($nValeur)>1))) {
    $nValeur=substr($nValeur,0,strlen($nValeur)-1);
  }

  return $nValeur;
}

/**
 * Calcule le montant à débiter d'un compte à partir du montant final exigé par le client.
 * Quatre valeurs sont retournées : <ul>
 * <li> - le montant à débiter (en devise de départ) </li>
 * <li> - la commission prélevée (en devise de départ) </li>
 * <li> - la taxe prélevée (en devise de départ) </li>
 * <li> - le bénéfice dégagé sur l'opération (en devise de départ) </li>
 * </ul>
 * @author Bernard DE BOIS
 * @param float $mntFin : montant final à recevoir.
 * @param char(3) $dev1 : devise du montant de départ.
 * @param char(3) $dev2 : devise du montant final.
 * @param float $taux : taux auquel doit être fait la conversion
 * @return array : les quatre montants décrits ci-dessus.
 */
function getChangeFinal($mntFin,$dev1,$dev2,$commission_nette=NULL,$taux=NULL) {
  global $global_id_agence;
  $ag = getAgenceDatas($global_id_agence);
  $infoDev1=getInfoDevise($dev1);
  $retour=array();


  if (!isset($taux)) { // Si taux non précisé
    $taux = getTauxChange($dev1, $dev2, true, 1); // Par défaut : change CASH
  }

  $dev_ref = $ag['code_devise_reference'];
  $prc_tax = $ag['prc_tax_change'];
  $prc_com = $ag['prc_comm_change'];
  $taux_dev_ref = getTauxChange($dev_ref,$dev1,false);
  // Commission et Taxe minimum exprimées dans la devise du compte à débiter.
  $cv_com = $ag['mnt_min_comm_change'] * $taux_dev_ref;
  $cv_tax = $cv_com * $prc_tax;
  // Si le change se fait avec la devise de référence, on met à zéro les frais de commission et de taxe.
  if (($dev1 == $dev_ref) || ($dev2 == $dev_ref)) {
    if ($ag['tax_dev_ref'] == 'f') {
      $prc_tax = 0;
      $cv_tax = 0;
    }
    if ($ag['comm_dev_ref'] == 'f') {
      $prc_com = 0;
      $cv_com = 0;
    }
  }

  $debit_com = 0;

  //On calcule le montant limite de la commission (celui au dessus duquel, on prend un pourcentage et en dessous duquel on prend la commission minimum). Ce montant est celui débité du compte.
  if ($prc_com != 0) $debit_com = $cv_com / $prc_com;

  $credit_com = getChangeInfos($debit_com,$dev1,$dev2,$commission_nette,$taux);
  // On calcule les montants minimum auxquels on enlève la commission et la taxe.
  $credit_com = $credit_com['montant_reel_change'];
  // On exprime le montant final en monnaie du compte à débiter.
  $taux_ind = getTauxChange($dev1, $dev2, false);
  $cvMntFin=round($mntFin/$taux_ind,$infoDev1['precision']);
  if ($cvMntFin < $credit_com) {
    $prc_tax = 0;
    $prc_com = 0;
  } else {
    $cv_com=0;
    $cv_tax=0;
  }

  // Calcul de la commission
  if (isset($commission_nette)) {
    // Récupère la taxe et la commission en fonction du montant
    $SPLIT = splitCommissionNette($commission_nette, $dev1, $dev2);
    $cv_com = $SPLIT["commission"];
    $cv_tax = $SPLIT["taxe"];
    $prc_com = 0;
    $prc_tax = 0;
  }
  $montant_avant_benefice = $cvMntFin / ( 1 - ( ($taux_ind-$taux) / $taux_ind));
  $montant_debite=($montant_avant_benefice + $cv_com + $cv_tax)/(1-($prc_com + $prc_tax * $prc_com));
  $commission=round($montant_debite * $prc_com + $cv_com,$infoDev1['precision']);
  $taxe      =round( $montant_debite * $prc_com * $prc_tax + $cv_tax,$infoDev1['precision']);
  $retour['montant_debite'] = round ($montant_debite,$infoDev1['precision']);
  $retour['commission'] = $commission;
  $retour['taxe'] = $taxe;
  $retour['montant_reel_change']=$cvMntFin;
  $retour['benef_taux'] = calculeBeneficeTaux($montant_avant_benefice,$dev1,$dev2,$taux);

  // Avant de renvoyer les valeurs, vérifier que le change ne produira pas un reste de change car dans ce cas, nous ne garantissons pas que la C/V reflétera la réalité
  $CHBACK = getChangeInfos($retour["montant_debite"], $dev1, $dev2);
  if ($CHBACK["diff_dev_ref"] > 0) // On a un reste
    $retour["alert"] = true;
  else
    $retour["alert"] = false;

  return $retour;
}
/**
 * Renvoie la valeur du plus petit billet  de la devise :
 * @author Ares
 * @param char(3) $devise Code ISO de la devise
 * @return double le petit billet de la devise.
 */
function getPetitBillet($devise) {
  global $global_id_agence;
  global $dbHandler;
  $sql = "SELECT MIN(valeur) FROM adsys_types_billets WHERE id_ag=$global_id_agence and devise = '$devise'";
  $db = $dbHandler->openConnection();
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $tmprow = $result->fetchrow();
  $dbHandler->closeConnection(true);
  if($tmprow[0]==0 || $tmprow[0]==NULL){
  	$tmprow[0]=1;
  }
  return $tmprow[0];
}

/**
 * 
 * Retourne la precision d'un devise
 * 
 * @param String $devise
 * @return integer $precision
 */
function getPrecisionDevise($devise)
{
    global $error;
    
    if(!empty ($devise)) {
        $infos_devise = getInfoDevise($devise);
        return $infos_devise['precision'];
    }
    else {
        signalErreur(__FILE__,__LINE__,__FUNCTION__, _("La devise n'est pas renseigné"));        
    }
}
?>