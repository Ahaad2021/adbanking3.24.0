<?php

$return_data = array(
    'success' => false,
    'datas' => array(),
);

/*
  $return_data = array(
  'success' => true,
  'datas' => array(
  'identifiant_client' => $_POST['identifiant_client'],
  'id_agence_source' => $_POST['id_agence_source'],
  'id_compte_source' => $_POST['id_compte_source'],
  'id_agence_cible' => $_POST['id_agence_cible'],
  'num_compte_cible' => rawurldecode($_POST['num_compte_cible']),
  'montant' => $_POST['montant'],
  'libelle' => rawurldecode($_POST['libelle']),
  ),
  );

  echo json_encode($return_data, 1 | 4 | 2 | 8);
  exit;
  */

//error_reporting(E_ALL);
//ini_set("display_errors", "on");

// Permet d'afficher les dates/heures en langue française
//setlocale(LC_ALL, "fr_BE");

// Get posted data
$identifiant_client = trim($_REQUEST['identifiant_client']);
$id_agence_source = trim($_REQUEST['id_agence_source']);
$id_compte_source = trim($_REQUEST['id_compte_source']);
$id_agence_cible = trim($_REQUEST['id_agence_cible']);
$num_compte_cible = trim($_REQUEST['num_compte_cible']);
$montant = trim($_REQUEST['montant']);
$libelle = trim($_REQUEST['libelle']);

// Load ini data for agence
$_REQUEST['m_agc'] = $id_agence_source;

// On charge les variables globales
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/VariablesSession.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/tarification.php';
require_once 'lib/misc/access.php';
require_once 'services/misc_api.php';

// Récupère les infos de l'agence distante choisie
require_once 'ad_ma/app/models/AgenceRemote.php';
if ($id_agence_source != $id_agence_cible) {
    $global_remote_agence_obj = AgenceRemote::getRemoteAgenceInfo($id_agence_cible);
}

// Multi agence includes
require_once 'ad_ma/app/controllers/misc/VariablesSessionRemote.php';
require_once 'ad_ma/app/models/Agence.php';
require_once 'ad_ma/app/models/Audit.php';
require_once 'ad_ma/app/models/Client.php';
require_once 'ad_ma/app/models/Compta.php';
require_once 'ad_ma/app/models/Compte.php';
require_once 'ad_ma/app/models/Credit.php';
require_once 'ad_ma/app/models/Devise.php';
require_once 'ad_ma/app/models/Divers.php';
require_once 'ad_ma/app/models/Epargne.php';
require_once 'ad_ma/app/models/Guichet.php';
require_once 'ad_ma/app/models/Historique.php';
require_once 'ad_ma/app/models/Parametrage.php';
require_once 'ad_ma/app/models/TireurBenef.php';

require_once 'ad_ma/app/controllers/epargne/Retrait.php';
require_once 'ad_ma/app/controllers/epargne/Depot.php';

$valeurs = getCustomLoginInfo();

$global_agence = $valeurs['libel_ag'];
$global_id_agence = $valeurs['id_ag'];
$global_remote_id_agence = $id_agence_cible;
$global_nom_login = $valeurs['login'];
$global_monnaie = $valeurs['monnaie'];
$global_monnaie_prec = $valeurs['monnaie_prec'];
$global_monnaie_courante_prec = $valeurs['monnaie_prec'];
$global_monnaie_courante = $valeurs['monnaie'];
$global_remote_monnaie = $valeurs['monnaie'];
$global_remote_monnaie_courante = $valeurs['monnaie'];
$global_multidevise = $valeurs['multidevise'];
$global_last_axs = time();
$global_institution = $valeurs['institution'];
$global_type_structure = $valeurs['type_structure'];
$global_id_exo = $valeurs['exercice'];
$global_remote_id_exo = $valeurs['exercice'];
$global_langue_systeme_dft = $valeurs['langue_systeme_dft'];
$global_langue_utilisateur = 'fr_BE'; //$valeurs['langue'];
$global_id_guichet = 1; // To create

require_once 'lib/dbProcedures/main_func.php';
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/client.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/misc/divers.php';
include_once 'lib/misc/debug.php';

$appli = "main"; // On est dans l'application (et pas dans le batch)

// Strip client id from identifiant and set global_id_client
$global_id_client = intval(substr($identifiant_client, -8));

// Fonctions systèmes ~ Transfert compte API
$opt_transfert_api = 77;

$MyErr = null;
$isCpteSrcValid = false;
$isCpteDestValid = false;
$out = 0;

// Check agence source not set
if ($id_agence_source == null || $id_agence_source == '') {
    $MyErr = new ErrorObj('ERR_CPTE_AUTRE_AGC');
    $out = 1;
}
elseif ($montant <= 0) {
    $MyErr = new ErrorObj('ERR_MONTANT');
    $out = 1;
}
else
{
    // Check cpte source if exist for client source
    $cpte_src_arr = get_comptes_epargne($global_id_client);
    if (is_array($cpte_src_arr) && count($cpte_src_arr) > 0) {
        if ($cpte_src_arr[$id_compte_source]) {
            if ($cpte_src_arr[$id_compte_source]['id_titulaire'] != $global_id_client) {
                $MyErr = new ErrorObj('ERR_CPTE_SRC_INEXISTANT');
                $out = 2;
            } else {
                if ($cpte_src_arr[$id_compte_source]["soldeDispo"] < $montant) {
                    $MyErr = new ErrorObj('ERR_SOLDE_SRC_INSUFFISANT');
                    $out = 3;
                } else {
                    $cpteSrc = getAccountDatas($id_compte_source);
                    $prodSrc = getProdEpargne($cpteSrc['id_prod']);
                    $out = 4;
                    $isCpteSrcValid = true;
                }
            }
        } else {
            $MyErr = new ErrorObj('ERR_CPTE_SRC_INEXISTANT');
            $out = 5;
        }
    } else {
        $MyErr = new ErrorObj('ERR_CPTE_SRC_INEXISTANT');
        $out = 6;
    }

    if ($id_agence_source != $id_agence_cible) { // Multi-devise
        
        if(isMultiAgence()) {
            global $global_monnaie_courante, $global_remote_monnaie_courante, $global_id_guichet, $global_id_agence, $global_remote_id_agence, $global_remote_monnaie, $global_nom_login;
            global $dbHandler, $global_remote_id_client, $global_remote_client;

            // Store local monnaie courante
            //$global_monnaie_courante_tmp = $global_monnaie_courante;
            //$global_monnaie_courante = $global_remote_monnaie_courante;

            // Begin remote transaction
            $pdo_conn->beginTransaction();

            // Init class
            $EpargneObj = new Epargne($pdo_conn, $global_remote_id_agence);

            $id_cpte_cible = $EpargneObj->getIdCompte($num_compte_cible);

            if ($id_cpte_cible == NULL) {
                // Rollback
                $pdo_conn->rollBack(); // Roll back remote transaction

                $MyErr = new ErrorObj('ERR_NUM_COMPLET_CPTE_DEST_NOT_EXIST');
                $out = 12;
            } else {
                $CompteObj = new Compte($pdo_conn, $global_remote_id_agence);

                // Récupérer le infos sur le produit associé au compte sélectionné
                $InfoCpte = $CompteObj->getAccountDatas($id_cpte_cible);
                $InfoProduit = $EpargneObj->getProdEpargne($InfoCpte["id_prod"]);
                
                // Init class
                $ComptaObj = new Compta($pdo_conn, $global_remote_id_agence);

                $global_remote_id_exo = $ComptaObj->getCurrentExercicesComptables();

                // Destroy object
                unset($ComptaObj);

                // Client cible
                $global_remote_id_client = $InfoCpte["id_titulaire"];

                $devise_src = $cpteSrc["devise"];
                $devise_cible = $InfoCpte["devise"];

                $CHANGE = NULL;
                if ($devise_src != $devise_cible) { // Multi-devise
                    // Rollback
                    $pdo_conn->rollBack(); // Roll back remote transaction

                    /*
                    $CHANGE = array(
                        'cv' => '',
                        'devise' => '',
                        'comm_nette' => '',
                        'taux' => '',
                        'dest_reste' => '',
                        'reste' => ''
                    );
                    */
                    $MyErr = new ErrorObj('ERR_DEVISE_CPT_DIFF');
                    $out = 13;

                } else {
                    $type_transfert = 3;

                    $MyErr = new ErrorObj(NO_ERR);
                }
                // Destroy object
                unset($CompteObj);
            }
            // Destroy object
            unset($EpargneObj);
        } else {
            $MyErr = new ErrorObj('ERR_PAS_MULTI_AGENCE');
            $out = 19;
        }
    } else {
        if ($isCpteSrcValid) {
            // Check cpte cible format and if exist
            //if (isNumComplet($num_compte_cible))
            {
                $id_cpte_cible = get_id_compte($num_compte_cible);
                if ($id_cpte_cible == NULL) {
                    $MyErr = new ErrorObj('ERR_NUM_COMPLET_CPTE_DEST_NOT_EXIST');
                    $out = 7;
                } else {
                    $cpteCible = getAccountDatas($id_cpte_cible);

                    if ($cpteCible != NULL) {

                        // Check compte epargne service_financier = true
                        $cpte_dest_arr = get_comptes_epargne($cpteCible['id_titulaire']);

                        if ($cpte_dest_arr[$cpteCible["id_cpte"]]) {
                            if ($cpteCible['id_titulaire'] == $global_id_client) {
                                $type_transfert = 1; // Entre comptes d'un même client de la banque
                            } else {
                                $type_transfert = 2; // Le compte d'un autre client de la banque
                            }
                            $isCpteDestValid = true;

                            $MyErr = new ErrorObj(NO_ERR);
                        } else {
                            $MyErr = new ErrorObj('ERR_CPTE_DEST_INEXISTANT');
                            $out = 9;
                        }
                    } else {
                        $MyErr = new ErrorObj('ERR_CPTE_DEST_INEXISTANT');
                        $out = 8;
                    }
                }
            }
            /*
            else {
                $MyErr = new ErrorObj('ERR_NUM_COMPLET_CPTE_DEST');
                $out = 10;
            }
            */
            if ($isCpteDestValid) {
                // Si Transfert Même client
                if ($type_transfert == 1 && $id_compte_source == $id_cpte_cible) {
                    $MyErr = new ErrorObj('ERR_NUM_CPTE_SRC_DEST');
                    $out = 11;
                }
            }
        }
    }
}

/**
 * Transfère un montant d'un compte vers un autre à l'intérieur de l'institution
 * @author Bernard de Bois
 * @param int $id_cpte_source Compte source
 * @param int $id_cptedestination Compte destination
 * @param float $montant Montant à tansférer
 * @param float $montant_frais_transfert Montant des frais de transfert
 * @param array $CHANGE Tableau avec toutes les infos en cas de change
 * @param array $data_virement Données concernant la pièce justificative si transfert extérieur
 * @param array $DATA_SWIFT Pour transfert NetBank ??
 * @param int $cpte_preleve identifiant du compte de prélèvement des frais de transfert
 * @param array $a_his_compta Historique des mouvements comptables si mouvements précédents (c-à-d batch)
 * @return ErorObj Objet Erreur
 */
function transfertCpteClientAPI($id_cpte_source, $id_cpte_destination, $montant, $id_mandat, $montant_frais_transfert=NULL, $CHANGE=NULL, $data_virement=NULL, $DATA_SWIFT=NULL, $cpte_preleve=NULL, $a_his_compta=NULL, $test_delai = false,$data_cheque_benef = NULL, $type_fonction = 76) {
  global $dbHandler;
  global $global_id_client, $global_nom_login, $global_id_agence, $global_monnaie;
  $comptable = array();
   // On veut pouvoir commit ou rollback toute la procédure
  $db = $dbHandler->openConnection();

  // Infos compte source
  $InfoCpteSource = getAccountDatas($id_cpte_source);
  $InfoProduitSource = getProdEpargne($InfoCpteSource["id_prod"]);
  if (isset($montant_frais_transfert))
    $InfoCpteSource['frais_transfert'] =  $montant_frais_transfert;

  // Infos compte destination
  $InfoCpteDestination = getAccountDatas($id_cpte_destination);
  $InfoProduitDestination = getProdEpargne($InfoCpteDestination["id_prod"]);

  // D'abord vérifier qu'on peut retirer du compte source
  $erreur = CheckRetraitEwallet($InfoCpteSource, $InfoProduitSource, $montant, 1, $id_mandat, $test_delai);
  if ($erreur->errCode != NO_ERR)  {
    $dbHandler->closeConnection(false);
    return $erreur;
  }

  // Ensuite vérifier qu'on peut déposer sur le compte destination
  $erreur = CheckDepotEwallet($InfoCpteDestination, $montant);
  if ($erreur->errCode != NO_ERR)  {
    $dbHandler->closeConnection(false);
    return $erreur;
  }

  // Si le compte de destination paie les frais, vérifier que le retrait est autorisé dans ce compte
  if ($cpte_preleve == $id_cpte_destination) {
    $erreur = CheckRetraitEwallet($InfoCpteDestination, $InfoProduitDestination, $montant_frais_transfert, NULL, NULL, $test_delai);
    if ($erreur->errCode != NO_ERR)  {
      $dbHandler->closeConnection(false);
      return $erreur;
    }
  }

  // Passage de l'écriture comptable du transfert
  // Passage des écritures comptables : débit client / crédit client
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $cptes_substitue["int"] = array();

  //débit d'un client par le crédit d'un autre client
  $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_source);
  if ($cptes_substitue["cpta"]["debit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  $cptes_substitue["int"]["debit"] = $id_cpte_source;
  $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte_destination);
  if ($cptes_substitue["cpta"]["credit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  $cptes_substitue["int"]["credit"] = $id_cpte_destination;

  if ($InfoCpteSource['devise'] != $InfoCpteDestination['devise']) {
  	if (empty($CHANGE)) {
  		// On prend les valeurs du moment
      $CHANGE['cv'] = calculeCV($InfoCpteSource['devise'], $InfoCpteDestination['devise'], $montant);
      $CHANGE['dest_reste'] = 2; // reste sur compte de base
      $CHANGE['comm_nette'] = NULL; // calculé automatiquement
      $CHANGE['taux'] = NULL; // calculé automatiquement
    }
    $myErr = change($InfoCpteSource['devise'], $InfoCpteDestination['devise'], $montant, $CHANGE['cv'], 119, $cptes_substitue, $comptable, $CHANGE['dest_reste'], $CHANGE['comm_nette'], $CHANGE['taux'],$id_cpte_source);
  } else {
    $myErr = passageEcrituresComptablesAuto(119, $montant, $comptable, $cptes_substitue, $InfoCpteSource['devise'],NULL,$id_cpte_source);
  }

  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  // Prélèvement des frais de transfert : débit du compte de prélèvement
  if ($montant_frais_transfert > 0) {
    $type_oper = 152;
    // Passage des écritures comptables : débit client
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();

    // si le compte de destination paie les frais
    if ($cpte_preleve == $id_cpte_destination) {
      $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_destination);
      $cptes_substitue["int"]["debit"] = $id_cpte_destination;
      $devise_frais = $InfoCpteDestination['devise'];
    } else { // le compte source du transfert paie les frais
      $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_source);
      $cptes_substitue["int"]["debit"] = $id_cpte_source;
      $devise_frais = $InfoCpteSource['devise'];
    }

    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    // si la devise des frais n'est pas la même que la devise de référence, faire le change
    if ($devise_frais != $global_monnaie)
      $myErr = effectueChangePrivate($devise_frais,$global_monnaie, $montant_frais_transfert, $type_oper, $cptes_substitue, $comptable);
    else
      $myErr = passageEcrituresComptablesAuto($type_oper, $montant_frais_transfert, $comptable, $cptes_substitue);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  $myErr = preleveFraisDecouvert($id_cpte_source, $comptable);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  if ($id_mandat != NULL) {
    $MANDAT = getInfosMandat($id_mandat);
    $data_virement['id_pers_ext'] = $MANDAT['id_pers_ext'];
  }

  if ($data_virement != NULL) {
    $data_his_ext = creationHistoriqueExterieur($data_virement);
  } else {
    $data_his_ext = NULL;
  }
  if(!is_null($data_cheque_benef)) {
    $id = insere_tireur_benef($data_cheque_benef);
 	$data_his_ext['id_tireur_benef']=$id;
 	$data_ch['id_cheque']=$data_virement['num_piece'];
	$data_ch['date_paiement']=$data_virement['date_piece'];
	$data_ch['etat_cheque']=1;
	$data_ch['id_benef'] =$id; 
	$rep=insertCheque($data_ch,$id_cpte_source);
	if ($rep->errCode != NO_ERR ) {
		$dbHandler->closeConnection(false);
		return $rep;
	}
  }

  /* Mise à jour des données de traitement de l'ordre de virement */
  if ($DATA_SWIFT != NULL) {
    if ( $DATA_SWIFT["type"] == 1)
      updateSwiftDomestique($DATA_SWIFT["id"], $DATA_SWIFT["statut"], $DATA_SWIFT["$mess_err"],$DATA_SWIFT["cpte_don"],$DATA_SWIFT["cpte_ben"]);
    else if ( $DATA_SWIFT["type"] == 2)
      updateSwiftEtranger($DATA_SWIFT["id"], $DATA_SWIFT["statut"], $DATA_SWIFT["$mess_err"],$DATA_SWIFT["cpte_don"],$DATA_SWIFT["cpte_ben"]);
  }

  if (is_array($a_his_compta )) {
  	// On a déjà un historique comptable, on y ajoute les mouvements du transfert
    $a_his_compta = array_merge($a_his_compta, $comptable);
  } else {
  	// Si on n'a pas passé d'historique comptable, c'est qu'on n'est pas appelé par le batch, il faut donc ajouter à l'historique
    $myErr = ajout_historique($type_fonction, $InfoCpteSource["id_titulaire"],'', $global_nom_login, date("r"), $comptable, $data_his_ext);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  $dbHandler->closeConnection(true);
  return $myErr;
}

// Output error
if ($MyErr->errCode === NO_ERR) {
    $dbHandler->closeConnection(true);

    //echo json_encode($return_data, 1 | 4 | 2 | 8);
    //exit;
    //$cpteSrc = getAccountDatas($id_cpte_source);
    //var_dump($cpte_arr);
    //$cpteDest = getAccountDatas($id_cpte_cible);
    //print_rn($cpteCible);
    //exit;

    // Récupération du montant réel
    $mnt_reel = recupMontant($montant);

    // Création du tableau contenant les données de la pièce justificative
    $data_virement = array();
    $data_virement['communication'] = NULL;
    $data_virement['remarque'] = 'Transfert API';
    $data_virement['sens'] = '---'; //il s'agit d'un transfert interne (aucun mouvement de ou vers l'ext.)

    // Transfert entre comptes d'un même client
    if ($type_transfert == 1) {
        // Montant des frais de transfert
        if ($cpteSrc["frais_transfert"] > 0) {
            $frais_transfert = $cpteSrc["frais_transfert"];
        } else {
            $frais_transfert = 0;
        }

        // Compte de prélèvement des frais
        $cpte_preleve = $id_compte_source;

        // Process transfer
        $erreur = transfertCpteClientAPI($id_compte_source, $id_cpte_cible, $mnt_reel, NULL, $frais_transfert, NULL, $data_virement, NULL, $cpte_preleve, NULL, false, NULL, $opt_transfert_api);

        if ($erreur->errCode === NO_ERR) {
            
            // Prélève frais transfert de compte à compte
            $err = preleveFraisAbonnement('SMS_TRC', $global_id_client, 182, $mnt_reel);
            
            if ($err->errCode !== NO_ERR) {
                $dbHandler->closeConnection(false);
                $return_data = array(
                    'success' => false,
                    'datas' => array(
                        'msg' => $err->param,
                    ),
                );
            } else {
                $dbHandler->closeConnection(true);
                $return_data = array(
                    'success' => true,
                    'datas' => array(
                        'msg' => "N° de transaction : " . sprintf("%09d", $erreur->param),
                    ),
                );
            }
        } else {

            $dbHandler->closeConnection(false);

            $return_data = array(
                'success' => false,
                'datas' => array(
                    'msg' => sprintf("Echec de transfert sur un compte : %s", $erreur->errCode),
                ),
            );
        }
    } // Fin si transfert pour le même client
    elseif ($type_transfert == 2) { // Transfert sur le compte d'un autre client de la banque

        if ($cpteSrc["frais_transfert"] > 0) {
            $frais_transfert = $cpteSrc["frais_transfert"];
        } else {
            $frais_transfert = 0;
        }

        // Compte de prélèvement des frais
        $cpte_preleve = $id_compte_source;
        $data_benef = NULL;
        $his = NULL;

        // Process transfer
        $erreur = transfertCpteClientAPI($id_compte_source, $id_cpte_cible, $mnt_reel, NULL, $frais_transfert, NULL, $data_virement, NULL, $cpte_preleve, $his, NULL, $data_benef, $opt_transfert_api);

        if ($erreur->errCode === NO_ERR) {
            
            // Prélève frais transfert de compte à compte
            $err = preleveFraisAbonnement('SMS_TRC', $global_id_client, 182, $mnt_reel);
            
            if ($err->errCode !== NO_ERR) {

                $dbHandler->closeConnection(false);

                $return_data = array(
                    'success' => false,
                    'datas' => array(
                        'msg' => $err->param,
                    ),
                );
            } else {

                $dbHandler->closeConnection(true);

                $return_data = array(
                    'success' => true,
                    'datas' => array(
                        'msg' => "N° de transaction : " . sprintf("%09d", $erreur->param),
                    ),
                );
            }
        } else {

            $dbHandler->closeConnection(false);

            $return_data = array(
                'success' => false,
                'datas' => array(
                    'msg' => sprintf("Echec de transfert sur un compte : %s", $erreur->errCode),
                ),
            );
        }
    } // Fin Transfert sur le compte d'un autre client de la banque
    elseif ($type_transfert == 3) { // Transfert sur deux différents comptes clients dans deux différentes agences
        $rollBackRemote = true;

        // Init class
        $AuditObj = new Audit();

        try {
            $type_transaction = 11; // transfert
            $type_choix = 11;
            $type_choix_libel = 'Transfert sur deux différents comptes dans deux agences différentes';

            // Sauvegarder la transaction en cours
            $AuditObj->insertTransacData($global_nom_login, $global_id_agence, $global_remote_id_agence, $global_remote_id_client, $id_cpte_cible, $type_transaction, $type_choix, $type_choix_libel, recupMontant($montant), '');

            $erreur_local = Depot::depoCpteLocalMultidevises($global_id_agence, $global_id_guichet, $id_compte_source, recupMontant($montant), $prodSrc, $cpteSrc, NULL, $type_transaction, $CHANGE);

            // Prélève frais transfert de compte à compte multi-agence
            $err = preleveFraisAbonnement('SMS_TRC', $global_id_client, 182, recupMontant($montant));

            if ($err->errCode !== NO_ERR) {
                $erreur_local = $err;
            }

            if ($erreur_local->errCode === NO_ERR) {

                //mouvement des comptes avec gestion des frais d'opérations sur compte s'il y lieu
                $erreur_remote = Depot::depotCpteRemoteMultidevises($pdo_conn, $global_remote_id_agence, $global_id_guichet, $id_cpte_cible, recupMontant($montant), $InfoProduit, $InfoCpte, NULL, $type_transaction, $CHANGE);

                //die;

                if ($erreur_remote->errCode === NO_ERR) {

                    // Commit local transaction
                    if ($dbHandler->closeConnection(true)) {
                         $rollBackRemote = false;                    

                        if(isset($erreur_local->param['id_his']) && $erreur_local->param['id_his']>0) {
                            // Sauvegarder l'ID historique en local
                            $AuditObj->updateLocalHisId($erreur_local->param['id_his']);
                        }

                        if(isset($erreur_local->param['id_ecriture']) && $erreur_local->param['id_ecriture']>0) {
                            // Sauvegarder l'ID ecriture en local
                            $AuditObj->updateLocalEcritureId($erreur_local->param['id_ecriture']);
                        }

                        // Commit remote transaction
                        if ($pdo_conn->commit()) {

                            if(isset($erreur_remote->param['id_his']) && $erreur_remote->param['id_his']>0)
                            {
                                // Sauvegarder l'ID historique en déplacé
                                $AuditObj->updateRemoteHisId($erreur_remote->param['id_his']);
                            }

                            if(isset($erreur_remote->param['id_ecriture']) && $erreur_remote->param['id_ecriture']>0)
                            {
                                // Sauvegarder l'ID ecriture en déplacé
                                $AuditObj->updateRemoteEcritureId($erreur_remote->param['id_ecriture']);
                            }

                            // Valider la transaction en cours
                            $AuditObj->updateTransacFlag('t');

                            // All success
                            $MyErr = new ErrorObj(NO_ERR, $erreur_local->param['id_his']);
                        }
                        else {
                            // Rollback
                            $pdo_conn->rollBack(); // Roll back remote transaction
                            $MyErr = $erreur_remote; // Erreur ecriture en distant
                            $out = 18;
                        }
                    } else {
                        $dbHandler->closeConnection(false);
                        $MyErr = $erreur_local; // Erreur ecriture en local
                        $out = 17;
                    }
                } else {
                    $pdo_conn->rollBack(); // Roll back remote transaction
                    $MyErr = $erreur_remote; // Erreur ecriture en distant
                    $out = 16;
                }
            } else {
                $dbHandler->closeConnection(false);
                $MyErr = $erreur_local; // Erreur ecriture en local
                $out = 15;
            }

        } catch (PDOException $e) {

            // Sauvegarder le message d'erreur
            $AuditObj->saveErrorMessage($e->getMessage());

            // Sauvegarder le log SQL
            $AuditObj->saveSQLLog($pdo_conn->getError());

            if ($rollBackRemote) {
                $pdo_conn->rollBack(); // Roll back remote transaction
            }

            $MyErr = new ErrorObj('ERR_TRANSFERT_CPTE', $e->getMessage());
            $out = 14;
        }

        if ($MyErr->errCode === NO_ERR) {
            
            $return_data = array(
                'success' => true,
                'datas' => array(
                    'msg' => "N° de transaction : " . sprintf("%09d", $MyErr->param)
                ),
            );
        } else {
            $return_data = array(
                'success' => false,
                'datas' => array(
                    'msg' => $MyErr->param
                ),
            );
        }
        
        // Destroy object
        unset($AuditObj);
    } // Fin Transfert sur deux différents comptes clients dans deux différentes agences
} else {

    $dbHandler->closeConnection(false);

    $return_data = array(
        'success' => false,
        'datas' => array(
            'msg' => $MyErr->errCode,
        ),
    );
}

echo json_encode($return_data, 1 | 4 | 2 | 8);
exit;
