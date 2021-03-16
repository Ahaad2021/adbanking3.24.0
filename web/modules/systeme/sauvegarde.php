<?php

/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */
// TF - 06/09/2002
// Création d'une image de la base de données sur le disque pour éventuelle restauration future

require_once('batch/backup_db.php');

/*{{{ Dat-1 : Sélection type de sauvegarde */
if ($global_nom_ecran == "Dat-1") {
  $type = array (
            "c" => _("Sauvegarde complète"),
            "d" => _("Sauvegarde données pour consolidation")
          );
  $MyPage = new HTML_GEN2(_("Sélection type de sauvegarde"));
  $MyPage->addField("type", _("Type de sauvegarde"), TYPC_LSB);
  $MyPage->setFieldProperties("type", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("type", FIELDP_ADD_CHOICES, $type);
  //Boutons
  $MyPage->addFormButton(1, 1, "valider", _("Sélectionner"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Dat-2");
  $MyPage->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-7");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $prochEc = array (
               "c" => 2,
               "d" => 4
             );
  //JS pour bouton
  foreach ($prochEc as $code => $ecran)
  $js .= "if (document.ADForm.HTML_GEN_LSB_type.value == '$code')
         assign('Dat-$ecran');";
  $MyPage->addJS(JSP_BEGIN_CHECK, "js1", $js);
  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
} /*}}}*/
else
  /*{{{ Dat-2 : Récupération des anciennes images */
  if ($global_nom_ecran == 'Dat-2') {
    $images = array ();
    $dir = opendir("$lib_path/backup/images");
    if ($dir == false) {
      $html_err = new HTML_erreur(_("Echec de la sauvegarde.")." ");
      $html_err->setMessage(_("Le répertoire de sauvegarde n'existe pas."));
      $html_err->addButton("BUTTON_OK", 'Gen-7');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      exit ();
    }

    $myForm = new HTML_GEN2(_("Sauvegarde des données de l'agence"));

    // Tableau des anciennes images
    $xtHTML = "<h3>Espace disque disponible sur le serveur</h3>";
    $espace_total = disk_total_space("$lib_path/backup/");
    $espace_libre = disk_free_space("$lib_path/backup/");
    // espace disque exprimé en giga octets
    $espace_libre_affiche = round($espace_libre / 1024 / 1024 / 1024, 2);
    $pourcentage_libre = round($espace_libre / $espace_total * 100, 1);
    $xtHTML .= "<p>".sprintf(_("Il y a <b> %sGo disponibles</b> sur le disque du serveur, soit %s de l'espace total (1&nbsp;Go = 1024 Mo)."),$espace_libre_affiche . "&nbsp;","<b>" . $pourcentage_libre . "%</b>")."</p>";
    if ($pourcentage_libre < 10) {
      $xtHTML .= "<p style='color: red; font-weight: bold;'>"._("Attention, le disque est presque plein, il faut peut-être déplacer certaines images ou certains backups vers un CD.");
    }
    $xtHTML .= "<h3>"._("Images déjà créées")."</h3>";
    $xtHTML .= "<br><table align=\"center\" cellpadding=\"5\" width=\"95%\">";
    $xtHTML .= "\n<tr align=\"center\" bgcolor=\"$colb_tableau\"><td><b>"._("Nom image")."</b></td><td><b>"._("Date de création")."</b></td><td><b>"._("Heure")."</b></td><td><b>"._("Taille")."</b></td></tr>";
    $color = $colb_tableau;
    // Tableau qui contient les noms des images déjà créées
    while ($file = readdir($dir)) {
      // Vérifier que c'est bien un fichier .sql.gz
      if (substr($file, -7) == '.sql.gz') {
        // Récupération des infos sur le fichier
        $nom_image = substr($file, 0, strlen($file) - 7);
        $date = strftime("%A %d %B %Y", filemtime("$lib_path/backup/images/$file"));
        $heure = date("H:i", filemtime("$lib_path/backup/images/$file"));
        $taille = round(filesize("$lib_path/backup/images/$file") / 1024 / 1024, 1) . " Mo";

        // Génération du code HTML pour ce fichier
        $color = ($color == $colb_tableau_altern ? $colb_tableau : $colb_tableau_altern);
        $xtHTML .= "\n<tr bgcolor=\"$color\"><td>" . $nom_image . "</td><td align='center'>" . $date . "</td><td align='center'>" . $heure . "</td><td align='right'>" . $taille . "</td></tr>";
      }
    }
    $xtHTML .= "</table><br /><br />";
    $myForm->addHTMLExtraCode("xtHTML", $xtHTML);
    $myForm->addField("nom_fichier", _("Nom de l'image"), TYPC_TXT);
    $myForm->setFieldProperties("nom_fichier", FIELDP_IS_REQUIRED, true);

    $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Dat-3");
    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-7");
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    $myForm->buildHTML();
    echo $myForm->getHTML();
  }
/*}}}*/

/*{{{ Dat-3 : Confirmation */
  else
    if ($global_nom_ecran == 'Dat-3') {
      $fichier = "$lib_path/backup/images/" . $nom_fichier . ".sql.gz";
      if (file_exists($fichier)) {
        $MyPage = new HTML_erreur(_("Erreur"));
        $MyPage->setMessage(_("L'image n'a pas pu être créée car une image portant le même nom existe déjà.")."<br />"._("Veuillez choisir un autre nom"));
        $MyPage->addButton(BUTTON_OK, "Dat-1");
        $MyPage->buildHTML();
        echo $MyPage->HTML_code;
      } else {
        $result = make_gzip($fichier);

        // Vérification de l'espace disque disponible
        $espace_total = disk_total_space("$lib_path/backup/");
        $espace_libre = disk_free_space("$lib_path/backup/");
        $pourcentage_libre = round($espace_libre / $espace_total * 100, 1);
        if ($pourcentage_libre < 10) {
          $msg = "<span style='color: red;'>" . sprintf(_("Attention, le disque du serveur est presque plein (seulement %s%% disponible"),$pourcentage_libre)."<br />"._("Il faut peut-être déplacer certains backups vers un CD."). "</span><br /><br />";
        } else {
          $msg = "";
        }
        if ($result->errCode == NO_ERR) {
          $myMsg = new HTML_message(_("Confirmation de la création de l'image"));
          $myMsg->setMessage($msg . sprintf(_("Une image des données de l'agence a bien été enregistrée sous le nom %s"), '<b>'.$nom_fichier.'</b>'));
          $myMsg->addButton(BUTTON_OK, "Gen-7");
          $myMsg->buildHTML();
          echo $myMsg->HTML_code;
        } else {
          $MyPage = new HTML_erreur(_("Erreur"));
          $MyPage->setMessage($msg . _("L'image n'a pas pu être créée")." ". $result->param . $result->handler);
          $MyPage->addButton(BUTTON_OK, "Gen-7");
          $MyPage->buildHTML();
          echo $MyPage->HTML_code;
        }
      }
    }
/*}}}*/

/*{{{ Dat-4 : Création image consolidation */
    else
      if ($global_nom_ecran == 'Dat-4') {
        global $DB_dsn;
        global $global_id_agence, $DB_user, $DB_name, $DB_pass;
        $db = $dbHandler->openConnection();
        $sql ="SELECT version from adsys_version_schema;";
        $result = $db->query($sql);
        if (DB::isError($result)) {
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
        $row = $result->fetchrow();
        $version_db = $row[0];
        $dbHandler->closeConnection(true);
        debug($version_db,"verion bd");
        $dirname = "$lib_path/backup/images/images_consolidation/";
        $fichier = "$lib_path/backup/images/images_consolidation/db-conso-agence" . $global_id_agence . "-v" .$version_db .".sql";
        $fic = "$lib_path/backup/images/images_consolidation/db-conso-agence" . $global_id_agence . "-v" .$version_db .".sql.gz";
        // FIXME: le répertoire devrait être créé lors de l'installation du RPM
        if (!is_dir($dirname)) {
          $cmde = "mkdir -p $dirname";
          shell_exec($cmde);
        }
        if (file_exists($fic)) {
          $cmd = "rm -rf $fic";
          debug(shell_exec($cmd));
        }
        //suppression du fichier $fichier s'il existe 
        if (file_exists($fichier)) { 
	          $cmd_ef = "rm -rf $fichier"; 
	          debug(shell_exec($cmd_ef)); 
        }
        //droits aux autres pour extraire le fichier
        $cmd1 = "chmod o+rwx $dirname";
        shell_exec($cmd1);
        // On optimise et on nettoye, ce qui permet aussi de tester si on a accès à la BD
        $retour = passthru("PGPASSWORD=$DB_pass psql -U $DB_user -d $DB_name -qc 'VACUUM ANALYZE'", $code_psql);
        /*
         * FIXME
         *
         * En attendant de trouver comment récupérer la liste des tables dans l'ordre de crétaion
        */
         //on recupère la liste des tables à sauvegarder(contenant id_ag) dans le tableau nom_table
         $db = $dbHandler->openConnection();
         $sql ="SELECT nom_table from adsys_table_conso;";
         $result = $db->query($sql);
         if (DB::isError($result)) {
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
         }
         $nom_table = array ();
         while ($row = $result->fetchrow()) {
          array_push($nom_table, $row[0]);
         }
         $dbHandler->closeConnection(true);        

        // Liste des tables à sauvegarder
//       $nom_table = array (
//														0 => 'adsys_pays',
//														1 => 'ad_cli',
//														2 => 'ad_grp_sol',
//														3 => 'adsys_type_piece_identite',
//														4 => 'ad_pers_ext',
//														5 => 'ad_rel',
//														6 => 'ad_exercices_compta',
//														7 => 'ad_clotures_periode',
//														8 => 'ad_classes_compta',
//														9 => 'ad_cpt_ope',
//														10 => 'ad_cpt_ope_cptes',
//														11 => 'ad_cpt_comptable',
//														12 => 'ad_cpt_soldes',
//														13 => 'ad_journaux',
//														14 => 'ad_journaux_cptie',
//														15 => 'ad_journaux_liaison',
//														16 => 'devise',
//														17 => 'adsys_produit_epargne',
//														18 => 'adsys_banque',
//														19 => 'tireur_benef',
//														20 => 'ad_his_ext',
//														21 => 'ad_his',
//														22 => 'ad_cpt',
//														23 => 'ad_extrait_cpte',
//														24 => 'ad_mandat',
//														25 => 'adsys_produit_credit',
//														26 => 'adsys_etat_credits',
//														27 => 'ad_dcr',
//														28 => 'ad_dcr_grp_sol',
//														29 => 'ad_etr',
//														30 => 'ad_sre',
//														31 => 'ad_gui',
//														32 => 'ad_fer',
//														33 => 'ad_agc',
//														34 => 'adsys_rejet_pret',
//														35 => 'adsys_localisation',
//														36 => 'adsys_sect_activite',
//														37 => 'adsys_langue',
//														38 => 'adsys_types_billets',
//														39 => 'ad_rapports',
//														40 => 'adsys_objets_credits',
//														41 => 'ad_brouillard',
//														42 => 'adsys_correspondant',
//														43 => 'ad_ecriture',
//														44 => 'ad_mouvement',
//														45 => 'adsys_etat_credit_cptes',
//														46 => 'attentes',
//														47 => 'adsys_types_biens',
//														48 => 'ad_biens',
//														49 => 'ad_gar',
//														50 => 'ad_frais_attente',
//														51 => 'adsys_asso_produitcredit_statjuri',
//														52 => 'ad_ord_perm',
//														53 => 'ad_libelle',
//														54 => 'ad_mouvement_consolide',
//														55 => 'swift_op_domestiques',
//														56 => 'swift_op_etrangers'
//        										);

        debug($nom_table,_("les tables"));
        //créer le fichier de sauvergarde des tables 
        $res = touch($fichier);

        //Dump des données des tables contenant id_ag
        if ($code_psql == 0) {
          for ($i = 0; $i < count($nom_table); $i++) {
            passthru("PGPASSWORD=$DB_pass /usr/bin/pg_dump -U $DB_user --data-only --table=$nom_table[$i] $DB_name >> $fichier", $code_gz);
          }
        }
        $retour = passthru("gzip $fichier", $code_gz);
        // Vérification de l'espace disque disponible
        $espace_total = disk_total_space("$lib_path/backup/");
        $espace_libre = disk_free_space("$lib_path/backup/");
        $pourcentage_libre = round($espace_libre / $espace_total * 100, 1);
        if ($pourcentage_libre < 10) {
          $msg = "<span style='color: red;'>" . sprintf(_("Attention, le disque du serveur est presque plein (seulement %s%% disponible)."), $pourcentage_libre)."<br />"._("Il faut peut-être déplacer certains backups vers un CD.").  "</span><br /><br />";
        } else {
          $msg = "";
        }
        if ($code_psql == 0 && $code_gz == 0) {
          $myMsg = new HTML_message(_("Confirmation de la création de l'image"));
          $myMsg->setMessage($msg . sprintf(_("Une image des données des tables pour consolidation a bien été enregistrée sous le nom %s"), "<b>db-conso-agence" . $global_id_agence . ".v" .$version_db .".sql.gz</b>"));
          $myMsg->addButton(BUTTON_OK, "Gen-7");
          $myMsg->buildHTML();
          echo $myMsg->HTML_code;
        } else {
          $MyPage = new HTML_erreur(_("Erreur"));
          $MyPage->setMessage($msg . sprintf(_("L'image n'a pas pu être créée pour une raison inconnue. Veuillez contacter le support technique (msg %s - %s)"), $code_psql, $code_gz));
          $MyPage->addButton(BUTTON_OK, "Gen-7");
          $MyPage->buildHTML();
          echo $MyPage->HTML_code;
        }
      }
/*}}}*/

      else
        signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
?>