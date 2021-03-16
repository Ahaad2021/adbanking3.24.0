<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 */

/**
 * Batch : traitements pour NetBank
 * @package Systeme
 **/

require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'ad_ma/batch/batch_declarations_ma.php';

/**
 * Fonction qui fait la validation sémantique et l'extraction de données nécessaires swift pour les messages domestiques
 * @author Papa
 * @since 2.0
 * @param int $id_message l'identifiant du message Swift
 * @return array £DATA tableau contenant les données nessaires à l'ordre de paiement
 */
function validationSwiftOd() {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $global_id_agence=getNumAgence();
  affiche(_("Validation des messages SWIFT (paiement domestique) ..."));
  incLevel();

  // Recherche des messages SWIFT en attente
  $sql = "SELECT * FROM swift_op_domestiques WHERE statut = 0";
  $result = $db->query($sql);
  if (DB::isError($result))
    erreur("validationSwiftOd()", $result2->getMessage());

  // Validation du contenu des messages en attente
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $valid="";
    $id_message = $row["id_message"]; // id du message à traiter
    $num_sequence = $row["num_sequence"];

    // Validation numéro session
    if ( $row["num_session"] != 1)
      $valid = " "._("La session doit être égale à 1");

    // Validation SGN
    if ( $row["staut_paiement"] != "SGN")
      $valid =  " "._("Le statut doit être égal à SGN");

    // Validation du numéro de la séquence
    if ($num_sequence > 0) { // alors le message doit avoir de précédent
      $precedent = $num_sequence -1;
      $nom_fichier = trim($row["nom_fichier"]);
      $sql = "SELECT * FROM swift_op_etrangers WHERE trim(nom_fichier)='$nom_fichier' AND num_sequence=$precedent AND statut=0";
      $result_seq = $db->query($sql);
      if (DB::isError($result_seq))
        erreur("validationSwiftOd()", $result2->getMessage());

      if ($result_seq->numrows()==0)
        $valid =  " ".sprintf(_("La séquence est incorrecte au niveau du message %s pour le fichier %s"),$id_message, $nom_fichier);
    }

    // Validation du code SWIFT de la banque émettrice
    $infosagence = get_info_agence($global_id_agence);
    $val1 = trim($row["code_swift_em"]);
    $val2 = trim($infosagence["code_institution"]);
    settype($val1,"string");
    settype($val2,"string");
    if ( $val1 != $val2 )
      $valid = ". "._("Le code SWIFT de la banque émettrice doit être égal à celui de l institution");

    // vérification de l'existence du compte source
    $num_cpte = trim($row["num_cpte_do"]); //numéro complet du compte
    $sql = "SELECT * FROM ad_cpt WHERE replace(num_complet_cpte,'-','')='$num_cpte'";
    //$sql = "SELECT * FROM ad_cpt WHERE num_complet_cpte = '$num_cpte'";
    $result2 = $db->query($sql);
    if (DB::isError($result2))
      erreur("validationSwiftOd()", $result2->getMessage());

    if ($result2->numrows()==0)
      $valid =  " "._("Le compte source est inexistant");
    else {
      $row2 = $result2->fetchrow(DB_FETCHMODE_ASSOC);
      $id_titulaire = $row2["id_titulaire"];
      $id_cpte = $row2["id_cpte"]; // numéro du compte

      if ($id_titulaire != NULL) {
        // vérification de la correspondance des noms, prénoms, adresses
        $CLI = getClientDatas($id_titulaire);

        if (!isset($CLI))
          $valid =  " "._("Le client est inexistant");
        else {
          if ( $CLI["statut_juridique"] == 1) {
            // vérif nom
            $val1 = trim($CLI["pp_nom"]);
            $val2 = trim($row["nom_do"]);
            settype($val1,"string");
            settype($val2,"string");
            if ( $val1 != $val2 )
              $valid =  " "._("Le non du  donnateur ne correspond pas");

            // vérif pays
            //recherche du code de pays
            $id_pays = trim($CLI["pp_pays_naiss"]);
            $sql = "SELECT code_pays FROM adsys_pays WHERE id_pays=$id_pays";
            $res = $db->query($sql);
            if (DB::isError($res))
              erreur("validationSwiftOd()", $result2->getMessage());

            $row_pays = $res->fetchrow(DB_FETCHMODE_ASSOC);
            $code_pays = $row_pays["code_pays"];
            // Conversion des types
            $val1 = trim($code_pays);
            $val2 = trim($row["pays_do"]);
            settype($val1,"string");
            settype($val2,"string");
            if ( $val1 != $val2 )
              $valid =  " "._("Le pays du donnateur ne correspond pas");

          } else if ( $CLI["statut_juridique"] == 2) {
            // Vérif raison sociale
            $val1 = trim($CLI["pm_raison_sociale"]);
            $val2 = trim($row["nom_do"]);
            settype($val1,"string");
            settype($val2,"string");
            if ( $val1 != $val2 )
              $valid =  " "._("La raison sociale du donnateur ne correspond pas");
          } else if ( $CLI["statut_juridique"] == 3 || $CLI["statut_juridique"] == 4) {
            // Vérif non gie
            $val1 = trim($CLI["gi_nom"]);
            $val2 = trim($row["nom_do"]);
            settype($val1,"string");
            settype($val2,"string");
            if ( $val1 != $val2 )
              $valid =  " "._("Le nom du donnateur ne correspond pas");
          }

          // Vérif adresse
          $val1 = trim($CLI["adresse"]);
          $val2 = trim($row["adresse_do_1"]);
          settype($val1,"string");
          settype($val2,"string");
          if ( $val1 != $val2 )
            $valid =  " "._("Adresse du donnateur ne correspond pas");

          // Vérif code postal
          $val1 = trim($CLI["code_postal"]);
          $val2 = trim($row["code_postal"]);
          settype($val1,"string");
          settype($val2,"string");
          if ( $val1 != $val2 )
            $valid =  " "._("Le code postal du donnateur ne correspond pas");
        }
      }
    }

    // Faire la même chose pour le compte de destination

    // Vérification de l'existance du compte bénéficiare
    $num_cpte_ben = trim($row["num_cpte_ben"]);
    $sql = "SELECT * FROM tireur_benef WHERE replace(num_cpte,'-','')='$num_cpte_ben'";
    $result3 = $db->query($sql);
    if (DB::isError($result3))
      erreur("validationSwiftOd()", $result2->getMessage());

    if ($result3->numrows() == 0) { // le compte bénéficiaire n'existe pas
      // il faut l'inserer dans la table des tireurs bénéficiaires
      $sql = "INSERT INTO tireur_benef (denomination,id_ag,tireur,beneficiaire,adresse,code_postal,ville,num_tel,num_cpte,iban_cpte)";
      $sql .= " VALUES ('".$row["nom_ben"]."',$global_id_agence,'f','t'";
      $sql .= ",'".$row["adrsse_ben"]."'";
      $sql .= ",'".$row["code_postal_ben"]."'";
      $sql .= ",'".$row["ville_ben"]."'";
      $sql .= ",''";
      $sql .= ",'".$row["num_cpte_ben"]."'";
      //$sql .= ",'".$rows["pays_ben"]."'";
      $sql .= ",'')";
      $result4 = $db->query($sql);
      if (DB::isError($result4))
        erreur("validationSwiftOd()", $result2->getMessage());

    } else { // le compte bénéficiaire existe
      // Récupération des infos
      $row3 = $result3->fetchrow(DB_FETCHMODE_ASSOC);
      $DATA[$id_message]["num_cpte_ben"] = $row3["id"]; // id du compte du bénéficiaire

      // Vérification de la correspondance des noms
      if ( settype($row3["denomination"],"string") != settype($row["nom_ben"],"string") )
        $valid = ". "._("Le nom du bénéficiaire ne correspond pas");

      // Vérification de la correspondance des adresses
      if ( settype($row3["adresse"],"string") != settype($row["adresse_ben_1"],"string") )
        $valid = ". "._("Adresse du bénéficiaire ne correspond pas");

      // Vérification de la correspondance du code postal
      if ( settype($row3["code_postal"],"string") != settype($row["code_postal_ben"],"string") )
        $valid = ". "._("Le code postal du bénéficiaire ne correspond pas");

      // Vérification de la correspondance de la ville
      if ( $row3["ville"] != $row["ville_ben"] )
        $valid = ". "._("La ville du bénéficiaire ne correspond pas");

    }

    // Si le message n'est pas valide, on bascule son statut à 2 et on rapporte le message d'erreur.
    if ($valid != "") {
      $id_message = $row["id_message"];
      $sql = "UPDATE swift_op_domestiques SET statut = 2, message_erreur ='$valid' WHERE id_ag=$global_id_agence AND id_message=$id_message";
      $result3 = $db->query($sql);
      if (DB::isError($result3))
        erreur("validationSwift()", $result3->getMessage());

    } else {
      $DATA[$id_message]["num_cpte_do"] = $id_cpte;
      $DATA[$id_message]["montant"] = $row["montant"];
      $DATA[$id_message]["devise"] = $row["devise"];
      $DATA[$id_message]["communnication"] = $row["comm_1"];

    }
  }

  $dbHandler->closeConnection(true);

  affiche(_("Validation des messages Swift (paiement domestique) terminée"));
  decLevel();

  return $DATA;

}

/**
 * Fonction qui fait la validation sémantique et l'extraction de données nécessaires swift pour les messages étrangers
 * @author Papa
 * @since 2.0
 * @param int $id_message l'identifiant du message Swift
 * @return array £DATA tableau contenant les données nessaires à l'ordre de paiement
 */
function ValidationSwiftOe() {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $global_id_agence=getNumAgence();
  affiche(_("Validation des messages SWIFT (paiement international) ..."));
  incLevel();

  // Recherche des messages SWIFT en attente dans la table des op étrangers
  $sql = "SELECT * FROM swift_op_etrangers WHERE statut = 0";
  $result = $db->query($sql);
  if (DB::isError($result))
    erreur("validationSwiftOe()", $result2->getMessage());

  // Validation du contenu du message SWIFT en attente
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $valid="";
    $id_message = $row["id_message"];
    $num_sequence = $row["num_sequence"];

    // Validation numéro session
    if ( $row["num_session"] != 1)
      $valid = " "._("La session doit être égale à 1");

    // Validation SGN
    if ( $row["staut_paiement"] != "SGN")
      $valid =  " "._("Le statut doit être égal à SGN");

    // Validation du numéro de la séquence
    if ($num_sequence > 0) { // alors le message doit avoir de précédent
      $precedent = $num_sequence -1;
      $nom_fichier = trim($row["nom_fichier"]);
      $sql = "SELECT * FROM swift_op_etrangers WHERE trim(nom_fichier)='$nom_fichier' AND num_sequence=$precedent AND statut=0";
      $result_seq = $db->query($sql);
      if (DB::isError($result_seq))
        erreur("validationSwiftOe()", $result2->getMessage());

      if ($result_seq->numrows()==0)
        $valid =  " ".sprintf(_("La séquence est incorrecte au niveau du message %s pour le fichier %s"),$id_message,$nom_fichier);
    }

    // Validation du code SWIFT de la banque émettrice
    $infosagence = get_info_agence($global_id_agence);

    // conversion des types en string
    $val1 = trim($row["code_swift_em"]);
    $val2 = trim($infosagence["code_institution"]);
    settype($val1,"string");
    settype($val2,"string");

    if ($val1 != $val2)
      $valid = ". "._("Le code SWIFT de la banque émettrice doit être égal à celui de l institution");

    // vérification de l'existence du compte source
    $num_cpte = trim($row["num_cpte_do"]); // numéro complet du compte soustrait des '-'

    $sql = "SELECT * FROM ad_cpt WHERE replace(num_complet_cpte,'-','')='$num_cpte'";
    //$sql = "SELECT * FROM ad_cpt WHERE num_complet_cpte='$num_cpte'";
    $result2 = $db->query($sql);
    if (DB::isError($result2))
      erreur("validationSwiftOe()", $result2->getMessage());

    if ($result2->numrows()==0)
      $valid =  " "._("Le compte source est inexistant");
    else {
      $row2 = $result2->fetchrow(DB_FETCHMODE_ASSOC);
      $id_cpte = $row2["id_cpte"]; // numéro du compte
      $id_titulaire = $row2["id_titulaire"];

      if ($id_titulaire != NULL) {
        // vérification de la correspondance des noms, prénoms, adresses
        $CLI = getClientDatas($id_titulaire);

        if (!isset($CLI))
          $valid =  " "._("Le client est inexistant");
        else {
          if ( $CLI["statut_juridique"] == 1) {
            // vérif nom
            $val1 = trim($CLI["pp_nom"]);
            $val2 = trim($row["nom_do"]);
            settype($val1,"string");
            settype($val2,"string");
            if ( $val1 != $val2 )
              $valid = " "._("Le non du donnateur ne correspond pas");

            // vérif pays
            //recherche du code de pays
            $id_pays = trim($CLI["pp_pays_naiss"]);
            $sql = "SELECT code_pays FROM adsys_pays WHERE id_pays=$id_pays";
            $res = $db->query($sql);
            if (DB::isError($res))
              erreur("validationSwiftOe()", $result2->getMessage());

            $row_pays = $res->fetchrow(DB_FETCHMODE_ASSOC);
            $code_pays = $row_pays["code_pays"];
            // Conversion des types
            $val1 = trim($code_pays);
            $val2 = trim($row["pays_do"]);
            settype($val1,"string");
            settype($val2,"string");

            if ( $val1 != $val2 )
              $valid =  " "._("Le pays du donnateur ne correspond pas");

          } else if ( $CLI["statut_juridique"] == 2) {
            // Vérif raison sociale
            $val1 = trim($CLI["pm_raison_sociale"]);
            $val2 = trim($row["nom_do"]);
            settype($val1,"string");
            settype($val2,"string");
            if ( $val1 != $val2 )
              $valid =  " "._("La raison sociale du donnateur ne correspond pas");
          } else if ( $CLI["statut_juridique"] == 3 || $CLI["statut_juridique"] == 4) {
            // Vérif non gie
            $val1 = trim($CLI["gi_nom"]);
            $val2 = trim($row["nom_do"]);
            settype($val1,"string");
            settype($val2,"string");
            if ( $val1 != $val2 )
              $valid =  " "._("Le nom du donnateur ne correspond pas");
          }

          // Vérif adresse
          $val1 = trim($CLI["adresse"]);
          $val2 = trim($row["adresse_do_1"]);
          settype($val1,"string");
          settype($val2,"string");
          if ( $val1 != $val2 )
            $valid =  " "._("Adresse du donnateur ne correspond pas");

          // Vérif code postal
          $val1 = trim($CLI["code_postal"]);
          $val2 = trim($row["code_postal_do"]);
          settype($val1,"string");
          settype($val2,"string");
          if ( $val1 != $val2 )
            $valid =  " "._("Le code postal du donnateur ne correspond pas");
        }
      }
    }

    // Vérif code swift de la banque de destintion
    if ( ($row["code_swift_re"] == NULL ) or ($row["code_swift_re"]=='')) {
      // le code n'est pas précisé, on regarde si une banque matche grâce à quelles données ( pour le moment nom_banque)
      $nom_bq_ben = trim($row["nom_bq_ben"]);
      $ville_bq_ben = $row["ville_bq_ben"];
      $pays_bq_ben = $row["pays_bq_ben"];
      $code_postal_bq_ben = $row["code_postal_bq_ben"];

      if ($row["nom_bq_ben"] != NULL ) {
        $sql = "SELECT * FROM adsys_banque WHERE nom_banque='$nom_bq_ben'";
        $res = $db->query($sql);
        if (DB::isError($res))
          erreur("validationSwift()", $result2->getMessage());

        if ($res->numrows()==0) {
          // Aucune banque correspond alors on fait quoi ?
        } else {
          // une banque correspond alors on fait quoi ?
        }
      }
    } else { // le code swift de la banque de destination est donné
      // Vérif si ce code existe bien dans la liste des codes
      $code_swift = trim($row["code_swift_re"]);
      $sql = "SELECT * FROM adsys_banque WHERE code_swift='$code_swift'";
      $res = $db->query($sql);
      if (DB::isError($res))
        erreur("validationSwiftOe()", $result2->getMessage());

      if ($res->numrows()==0) { // le code n'est pas dans la liste, alors le créer
        $sql = "INSERT INTO adsys_banque (id_ag,code_swift) VALUES($global_id_agence,'$code_swift')";
        $res = $db->query($sql);
        if (DB::isError($res))
          erreur("validationSwift()", $result2->getMessage());
      }

    }

    // Vérification de l'existance du compte bénéficiare
    if ( $row["num_cpte_ben"] != NULL) {
      $num_cpte_ben = trim($row["num_cpte_ben"]);

      $sql = "SELECT * FROM tireur_benef WHERE replace(num_cpte,'-','')='$num_cpte_ben'";
      $result3 = $db->query($sql);
      if (DB::isError($result3))
        erreur("validationSwiftOe()", $result2->getMessage());

      if ($result3->numrows() == 0) { // le compte du tireur bénéficiaire n'existe pas
        // il faut l'inserer dans la table des tireurs bénéficiaires
        $sql = "INSERT INTO tireur_benef (denomination,id_ag,tireur,beneficiaire,adresse,code_postal,ville,num_tel,num_cpte,iban_cpte)";
        $sql .= " VALUES ('".$row["nom_ben"]."',$global_id_agence,'f','t'";
        $sql .= ",'".$row["adrsse_ben"]."'";
        $sql .= ",'".$row["code_postal_ben"]."'";
        $sql .= ",'".$row["ville_ben"]."'";
        $sql .= ",''";
        $sql .= ",'".trim($row["num_cpte_ben"])."'";
        //$sql .= ",'".$rows["pays_ben"]."'";
        $sql .= ",'')";
        $result4 = $db->query($sql);
        if (DB::isError($result4))
          erreur("validationSwift()", $result2->getMessage());

      } else { // le compte du tireur bénéficiaire existe
        // Récupération des infos
        $row3 = $result3->fetchrow(DB_FETCHMODE_ASSOC);
        $DATA[$id_message]["num_cpte_ben"] = $row3["id"]; // id du compte du bénéficiaire

        // Vérification de la correspondance des noms
        $val1 = trim($row3["denomination"]);
        $val2 = trim($row["nom_ben"]);
        settype($val1,"string");
        settype($val2,"string");
        if ( $val1 != $val2 )
          $valid = ". "._("Le nom du bénéficiaire ne correspond pas");

        // Vérification de la correspondance des adresses
        $val1 = trim($row3["adresse"]);
        $val2 = trim($row["adresse_ben_1"]);
        settype($val1,"string");
        settype($val2,"string");
        if ( $val1 != $val2 )
          $valid = ". "._("Adresse du bénéficiaire ne correspond pas");

        // Vérification de la correspondance du code postal
        $val1 = trim($row3["code_postal"]);
        $val2 = trim($row["code_postal_ben"]);
        settype($val1,"string");
        settype($val2,"string");
        if ( $val1 != $val2 )
          $valid = ". "._("Le code postal du bénéficiaire ne correspond pas");

        // Vérification de la correspondance de la ville
        $val1 = trim($row3["ville"]);
        $val2 = trim($row["ville_ben"]);
        settype($val1,"string");
        settype($val2,"string");
        if ( $val1 != $val2 )
          $valid = ". "._("La ville du bénéficiaire ne correspond pas");

      }
    } else {
      // le compte num_cpte_ben n'est pas renseigné ds la table swift_od_etrangers
    }

    // Si le message n'est pas valide, on bascule son statut à 2 et on rapporte le message d'erreur.
    if ($valid != "") {
      //$id_message = $row["id_message"];
      $sql = "UPDATE swift_op_etrangers SET statut=2,message_erreur='$valid' WHERE id_ag=$global_id_agence AND id_message=$id_message;";
      $result2 = $db->query($sql);
      if (DB::isError($result2))
        erreur("validationSwiftOe()", $result2->getMessage());

      $dbHandler->closeConnection(true);

    } else {
      $DATA[$id_message]["num_cpte_do"] = $id_cpte;
      $DATA[$id_message]["montant"] = $row["montant"];
      $DATA[$id_message]["devise"] = $row["devise"];
      $DATA[$id_message]["communnication"] = $row["comm_1"];

    }
  }

  $dbHandler->closeConnection(true);

  affiche(_("Validation des messages Swift (paiement international) terminée"));
  decLevel();

  return $DATA;
}

function traite_net_bank() {
  affiche(_("Démarre le traitement des messages SWIFT ..."));
  incLevel();

  // Validation des opérations domestiques
  $DATA = validationSwiftOd();

  // Validation des opérations étrangères
  $DATA = validationSwiftOe();

  decLevel();
  affiche(_("Traitement des messages SWIFT terminé !"));
}

?>