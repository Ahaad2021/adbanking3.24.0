<?php

require_once 'lib/misc/VariablesGlobales.php';
//require_once 'lib/dompdf/autoload.inc.php';
//use Dompdf\Dompdf;
//use Dompdf\Options;

function xml_2_xslfo($xml, $xslt_file) {
  global $xml_output;
  global $doc_prefix;
  global $xslfo_output;
  global $java_home;
  global $fop_path;
  global $global_langue_rapport, $global_langue_utilisateur;
  // On enregistre le XML généré par ADBanking dans un fichier
  $file_handle = fopen("$xml_output.".session_id(),"w");
  fwrite($file_handle, $xml);
  fclose($file_handle);

  // On utilise XALAN pour transformer le fichier xml en xslfo
  $output_file = "$xslfo_output.".session_id();
  $cmd = "export JAVA_HOME=\"$java_home\";";
  $cmd .= "${fop_path}/xalan.sh -IN $xml_output.".session_id()." ";
  $cmd .= "-XSL ${doc_prefix}/rapports/xslt/${global_langue_utilisateur}/${xslt_file} ";
  $cmd .= "-OUT $output_file 2>&1";
  $retour = array();
  $error = 0;
  $retour1 = exec($cmd, $retour, $error);
  if ($error > 0) {
    debug($cmd, _("Erreur à la génération du fichier FOP, la commande était"));
    debug($retour, _("Le message de retour de la commande xalan.sh est"));
  }

  return $output_file;
}

/**
 * Convertit un fichier au format XSL-FO en un fichier PDF au moyen de FOP
 * @param text $input_file Nom du fichier XSL-FO
 * @param bool $change Indique s'il s'agit d'un reçu de change. Dans ce cas, ne pas supprimer le rapport précédent mais ajouter les caractères -change à la fin de celui-ci
 * REM : Ceci est nécessaire pour éviter que le reçu de change n'écrase le rapport précédemment généré avant que l'utilisateur n'ait eu le temps d'ouvrir ce dernier
 * @return text Le nom du fichier pdf créé
 */

function xslfo_2_pdf($input_file, $change=false, $name="") {
  global $fop_path;
  global $pdf_output;
  global $java_home;
  global $java_memory;

  $output_file = $pdf_output.".".session_id().$name;

  // On ajoute -change au nom du fichier généré
  if ($change == true)
    $output_file .= "-change";

  // On augmente la mémoire allouée à la JVM pour que FOP puisse correctement lire le fichier .fo et générer le PDF
  $cmd = "export JAVA_HOME=\"$java_home\";export FOP_OPTS=\"-Xmx$java_memory\"; rm -f $output_file;${fop_path}/fop.sh $input_file $output_file";
  $retour = array();
  $error = 0;
  exec($cmd, $retour, $error);
  debug($input_file , "input file");
  debug($output_file , "output file");
  debug($error , _("erreur commande"));
  if ($error > 0) {
    debug($cmd, _("Problème dans la génération du PDF, la commande était"));
  }
  debug($retour, _("Le message de retour de la commande fop.sh (.fo -> .pdf) est"));

  return $output_file;
}

/**
 * Génère un fichier PDF à partir d'un fichier XML et d'une feuille de style XSLT
 * @param str $xml Nom du fichier XML
 * @param str $xslt Nom du fichier XSLT
 * @param bool $change Cfr fonction xslfo_2_pdf
 * @return str Nom du fichier PDF ainsi généré
 */
function xml_2_xslfo_2_pdf($xml, $xslt, $change=false, $name="",$template_name=null,$format='A4') {
  global $report_backup_path;
  // XML -> FO
  $fichier_xslfo = xml_2_xslfo($xml, $xslt);

  // FO -> PDF
    if ($template_name == null){
        $fichier_pdf = xslfo_2_pdf($fichier_xslfo, $change, $name);
        backup_pdf_file($fichier_xslfo, $name);
    }/*else{
        $fichier_pdf = xml_2_domPdf($xml, $template_name, $change, $name, $format);
        $report_backup_path = $fichier_pdf;
    }*/
    
  return $fichier_pdf;
}


/**
 * Affiche une fenêtre popup qui envoie le fichier PDF spécifié.
 * @param str $ecran_retour L'écran de retour après l'affichage du popup,
 * si NULL, alors aucun message de confirmation n'est affiché, seul un echo du javascript est fait
 * @param str $fichier_pdf Le fichier à envoyer
 * @param str $msg Message à afficher lors de la confirmation
 * @return str Code HTML à afficher avant l'écran de retour
 */
function get_show_pdf_html($ecran_retour, $fichier_pdf=NULL, $msg=NULL, $title="", $count="", $screenX="200", $screenY="75") {
  global $SERVER_NAME;
  global $pdf_output;

  //Récupération du PDF
  if ($fichier_pdf != NULL)
    $filename = $fichier_pdf;
  else
    $filename = "$pdf_output.".session_id();


  if (file_exists($filename)) {
    $url = "$SERVER_NAME/rapports/http/rapport_http.php?m_agc=".$_REQUEST['m_agc']."&filename=$filename";
    if ($title!="") {
      $filename=$title;
    }

    $js = "<script type=\"text/javascript\">var child_window$count=OpenBrwCustom('$url','$filename','$screenX','$screenY');child_window$count.document.title='$title';</script>";
    if ($ecran_retour != NULL) {
      echo $js;
      if ($msg == NULL) {
        $MyPage = new HTML_message(_("Génération rapport"));
        $MyPage->setMessage(_("Le rapport a été généré avec succès !"));
      } else {
        $MyPage = new HTML_message(_("Confirmation"));
        $MyPage->setMessage($msg);
      }
      $MyPage->addButton(BUTTON_OK, $ecran_retour);
      $MyPage->buildHTML();
      return $MyPage->HTML_code;
    } else {
      return $js;
    }
  } else {
    if ($ecran_retour != NULL) {
      $erreur = new HTML_erreur(_("Echec lors de la génération du rapport"));
      if ($msg == NULL) {
        $erreur->setMessage(_("Aucun rapport n'a été trouvé."));
      } else {
        $erreur->setMessage(_("L'opération s'est déroulée correctement, cependant aucun rapport n'a pu être trouvé."));
      }
      $erreur->addButton(BUTTON_OK, $ecran_retour);
      $erreur->buildHTML();
      return $erreur->HTML_code;
    } else {
      return false;
    }
  }
}


/**
 * Copie un fichier PDF qui vient d'être généré
 * Cette fonction est utilisée par le batch pour la rapport de batch
 * @param str $fichier_pdf Le fichier PDF à copier
 * @param str $destination L'endroit où il faut copier le fichier
 * @return bool true si tout se passe bien
 */
function get_pdf_html($fichier_pdf=NULL, $destination) {
  global $pdf_output;

  //Récupération du PDF
  if ($fichier_pdf != NULL)
    $filename = $fichier_pdf;
  else
    $filename = "$pdf_output.".session_id();

  if (file_exists($filename)) {
    // Sauvegarde du fichier
    $cmd = "cp $filename $destination";
    shell_exec($cmd);
  }
}

/**
 * Convertit un fichier au format XML en un fichier CSV au moyen de XALAN
 * @param string $xml Nom du fichier xml
 * @param string $xslt_file Nom du fichier xslt
 * @return string Le nom du fichier csv créé
 * @author Djibril NIANG
 * @since 2.7
 */
function xml_2_csv($xml, $xslt_file) {
    global $xml_output;
    global $doc_prefix;
    global $csv_output;
    global $java_home;
    global $fop_path;
    global $global_langue_rapport;

    // On enregistre le XML généré par ADBanking dans un fichier
    $rand = rand();
    $file_handle = fopen("$xml_output.".$rand,"w");
    fwrite($file_handle, $xml);
    fclose($file_handle);
    // On utilise XALAN pour transformer le fichier xml en csv
    $csv_file = $csv_output.".".$rand;
    $cmd = "export JAVA_HOME=\"$java_home\";";
    $cmd .= "${fop_path}/xalan.sh -IN $xml_output.".$rand." ";
    $cmd .= "-XSL ${doc_prefix}/rapports/csv/${xslt_file} ";
    $cmd .= "-OUT $csv_file 2>&1";

    $retour = array();
    $error = 0;
    $retour1 = exec($cmd, $retour, $error);
    if ($error > 0) {
        debug($cmd, _("Erreur à la génération du fichier CSV, la commande était"));
        debug($retour, _("Et le message de retour de la commande est"));
    }

    return $csv_file;
}

/** FONCTION XML ML**/
function xml_2_csv_ml($xml, $xslt_file) {
    global $xml_output;
    global $doc_prefix;
    global $csv_output;
    global $java_home;
    global $fop_path;
    global $global_langue_rapport;

    // On enregistre le XML généré par ADBanking dans un fichier
    $rand = rand();
    $file_handle = fopen("$xml_output.".$rand,"w");
    fwrite($file_handle, $xml);
    fclose($file_handle);
    // On utilise XALAN pour transformer le fichier xml en csv
    $csv_file = $csv_output.".".$rand;
    $cmd = "export JAVA_HOME=\"$java_home\";";
    $cmd .= "${fop_path}/xalan.sh -IN $xml_output.".$rand." ";
    $cmd .= "-XSL ${doc_prefix}/rapports/csv/${xslt_file} ";
    $cmd .= "-OUT $csv_file 2>&1";

    $retour = array();
    $error = 0;
    $retour1 = exec($cmd, $retour, $error);
    if ($error > 0) {
        debug($cmd, _("Erreur à la génération du fichier CSV, la commande était"));
        debug($retour, _("Et le message de retour de la commande est"));
    }

    return $csv_file;
}
function backup_pdf_file($file_fo_path, $file_output_name = null){
    global $fop_path;
    global $java_home;
    global $java_memory;
    global $report_backup_path;

    $output_file_dir = "/tmp/".(empty($file_output_name)?"export".rand()."pdf":$file_output_name.".pdf");
    $cmd = "export JAVA_HOME=\"$java_home\";export FOP_OPTS=\"-Xmx$java_memory\"; rm -f $output_file_dir;${fop_path}/fop.sh $file_fo_path $output_file_dir";
    $retour = array();
    $error = 0;
    exec($cmd, $retour, $error);


    $report_backup_path = $output_file_dir;
}
function xml_2_domPdf($xml,$filename,$change=false, $name="", $format){
  global $fop_path;
  global $pdf_output;
  global $SERVER_NAME;
  global $xml_output;
  global $java_home;


  $output_file = $pdf_output.".".$format."-".session_id().'.pdf';
  $xml_output_file = $xml_output.".".session_id();
  
  $paper_margin = "0.2cm 0.2cm 0.2cm 0.2cm";

  if($format == 'A4'){
    $x_size = 595.28;
    $y_size = 841.89;
    $x_inch = "8.3in";
    $y_inch = "11.7in";
  }else if($format == 'A5'){
    $x_size = 419.53;
    $y_size = 595.28;
    $x_inch = "5.8in";
    $y_inch = "8.3in";
  }else if($format == 'A6'){
    $x_size = 297.64;
    $y_size = 419.53;
    $x_inch = "4.1in";
    $y_inch = "5.8in";
  }else if($format == 'CT'){
    $x_size = 226.77;
    $y_size =  467.72;
    $x_inch = "3.1in";
    $y_inch = "6.5in";
    $paper_margin = "0cm 0.3cm 0.2cm 0cm";
  }else if($format == 'JALI'){
    $x_size = 283.47;
    $y_size = 430.87;
    $x_inch = "4in";
    $y_inch = "6in";
    $paper_margin = "1.3cm 0.3cm 0.2cm 0cm"; //30
  }else if($format == 'TWG'){
    $x_size = 481.89;
    $y_size = 226.77;
    $x_inch = "6.7in";
    $y_inch = "3.1in";
    $paper_margin = "0.6cm 0.2cm 0cm 0.05cm";
  }else if($format == 'DTB'){
    $x_size = 504.57;
    $y_size = 453.54;
    $x_inch = "7in";
    $y_inch = "6.3in";
    $paper_margin = "3.8cm 0.9cm 0cm 0.05cm";
  }else{
    $x_size = 595.28;
    $y_size = 841.89;
    $x_inch = "8.3in";
    $y_inch = "11.7in";
  }
  
  $xml = new SimpleXMLElement(file_get_contents($xml_output_file));

  ob_start();
  include "templates/$filename";
  $html = ob_get_clean();
  

  $options = new Options();
  $options->setIsRemoteEnabled(true);

  $dompdf = new DOMPDF();
  $dompdf->loadHtml($html);
  $customPaper = array(0,0,$x_size,$y_size);
  debug($customPaper, _("custom size"));
  $dompdf->set_paper($customPaper);
  // $dompdf->set_paper("A4", "portrait");
  $dompdf->render();

  $output = $dompdf->output();

  file_put_contents($output_file, $output);

  return $output_file;
}
?>