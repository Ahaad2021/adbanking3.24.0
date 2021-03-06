<?php

// Custom errors
define("ERR_CPTE_AUTRE_AGC", 1100);
define("ERR_CPTE_SRC_INEXISTANT", 1101);
define("ERR_SOLDE_SRC_INSUFFISANT", 1102);
define("ERR_NUM_COMPLET_CPTE_DEST_NOT_EXIST", 1103);
define("ERR_NUM_COMPLET_CPTE_DEST", 1104);
define("ERR_CPTE_DEST_INEXISTANT", 1105);
define("ERR_NUM_CPTE_SRC_DEST", 1106);
define("ERR_MISE_A_JOUR_MNT_LIMIT", 11113);
define("ERR_MONTANT_DEPASSE_LIMIT_RETRAIT_JOURNALIER", 11114);
define("ERR_MONTANT_DEPASSE_LIMIT_DEPOT_JOURNALIER", 11115);
define("ERR_CLIENT_ABONNEE_NO_EXIST", 1107);
define("ERR_CLIENT_AGE_NON_AUTORISE", 1108);
define("ERR_CLIENT_EN_RETARD", 1109);
define("ERR_CLIENT_MNT_SUPERIEUR", 1110);
define("ERR_NO_ENTRY_AD_BROUILLARD", 1111);
define("ERR_ENTRY_EXIST_AD_BROUILLARD", 11112);
define("ERR_DEMANDE_NON_AUTH", 1116);



$error[ERR_CPTE_AUTRE_AGC] = "Un transfert entre comptes ne peut être effectué que dans une même agence"; // 1100
$error[ERR_CPTE_SRC_INEXISTANT] = "Le compte source n'existe pas"; // 1101
$error[ERR_SOLDE_SRC_INSUFFISANT] = _("Le solde compte source est insuffisant"); // 1102
$error[ERR_NUM_COMPLET_CPTE_DEST_NOT_EXIST] = _("Le compte cible n'existe pas"); // 1103
$error[ERR_NUM_COMPLET_CPTE_DEST] = _("Numéro de compte cible invalide"); // 1104
$error[ERR_CPTE_DEST_INEXISTANT] = "Le compte cible n'existe pas"; // 1105
$error[ERR_NUM_CPTE_SRC_DEST] = "Opération impossible sur le même compte"; // 1106
$error[ERR_MISE_A_JOUR_MNT_LIMIT] = "Erreur lors de la mise à jour du montant limite de retrait journalier Ewallet"; // 11113
$error[ERR_MONTANT_DEPASSE_LIMIT_RETRAIT_JOURNALIER] = "Le montant du retrait dépasse la limite journalier"; //11114
$error[ERR_MONTANT_DEPASSE_LIMIT_DEPOT_JOURNALIER] = "Le montant du dépôt dépasse la limite journalier"; //11115
$error[ERR_CLIENT_ABONNEE_NO_EXIST] = "Client non abonné"; // 1107
$error[ERR_CLIENT_AGE_NON_AUTORISE] = "Age du client inférieur à 21 ans"; // 1108
$error[ERR_CLIENT_EN_RETARD] = "Client en retard sur paiement credit en cours"; // 1109
$error[ERR_CLIENT_MNT_SUPERIEUR] = "Montant supérieur au montant autorisé"; // 1110
$error[ERR_NO_ENTRY_AD_BROUILLARD] = "Pas de demande présent dans le brouillard"; // 1111
$error[ERR_ENTRY_EXIST_AD_BROUILLARD] = "Pas de demande présent dans le brouillard"; // 1112
$error[ERR_DEMANDE_NON_AUTH] = "Demande Retrait du Groupe Solidaire non autorisée"; // 1116

    function getCustomLoginInfo()
    {
        global $dbHandler, $global_id_agence;
        $db = $dbHandler->openConnection();

        // Recherche agence et login
        $retour['login'] = 'api';

        //Recherche info agence
        $retour["id_ag"] = getNumAgence();

        $dataAgence = getAgenceDatas($retour["id_ag"]);

        $retour['libel_ag'] = $dataAgence['libel_ag'];
        $retour['statut_ag'] = $dataAgence['statut'];
        $retour['institution'] = $dataAgence['libel_institution'];
        $retour['type_structure'] = $dataAgence['type_structure'];
        $retour['exercice'] = $dataAgence['exercice'];
        $retour['langue_systeme_dft'] = $dataAgence['langue_systeme_dft'];

        // Recherche infos devise de référence
        $sql = "SELECT code_devise, precision FROM devise WHERE id_ag = " . $retour["id_ag"] . " and code_devise = (SELECT code_devise_reference FROM ad_agc WHERE id_ag =" . $retour["id_ag"] . ")";
        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        } elseif ($result->numrows() <> 1) {
            //   echo "<FONT COLOR=red> ATTENTION, un devise de référence doit être paramétrée</FONT>";
        }
        $row = $result->fetchrow();
        $retour['monnaie'] = $row[0];
        $retour['monnaie_prec'] = $row[1];

        // Sommes-nous en mode unidevise ou multidevise
        $sql = "SELECT count(*) FROM devise WHERE id_ag =" . $retour["id_ag"];

        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $row = $result->fetchrow();

        if ($row[0] > 1) {
            $retour['multidevise'] = 1;
        } else {
            $retour['multidevise'] = 0;
        }

        $dbHandler->closeConnection(true);

        return $retour;
    }
