<?php

/**
 * Changement du mot de passe
 * @package Parametrage
 */

require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/html/HTML_GEN2.php';

if($global_nom_ecran == "Mdp-0"){//

	$MyPage = new HTML_erreur(_("Mot de passe expiré"));
  $MyPage->setMessage("<center>"._("Votre mot de passe est expiré, vous devez le changer maintenant")."</center>");
  $MyPage->addButton(BUTTON_OK,"Mdp-1");
  $MyPage->buildHTML();
  echo $MyPage->HTML_code;


}
else
if ($global_nom_ecran == "Mdp-1") { //Changement mot de passe
  $MyPage = new HTML_GEN2(_("Modification mot de passe"));
  //Champs
  $MyPage->addField("mdp_actuel", _("Mot de passe actuel"), TYPC_PWD);
  $MyPage->setFieldProperties("mdp_actuel", FIELDP_IS_REQUIRED, true);
  $MyPage->addField("mdp_new1", _("Nouveau mot de passe"), TYPC_PWD);
  $MyPage->setFieldProperties("mdp_new1", FIELDP_IS_REQUIRED, true);
  $MyPage->addField("mdp_new2", _("Confirmation nouveau mot de passe"), TYPC_PWD);
  $MyPage->setFieldProperties("mdp_new2", FIELDP_IS_REQUIRED, true);
  //Boutons
  $MyPage->addFormButton(1,1, "butok", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butok", BUTP_PROCHAIN_ECRAN, "Mdp-2");
  $MyPage->addFormButton(1,2, "butanul", _("Annuler"), TYPB_SUBMIT);
  // déterminer le Prochain ecran,
  if($global_modif_pwd_login){ //mot de passe expiré
  	$MyPage->setFormButtonProperties("butanul", BUTP_PROCHAIN_ECRAN, "Out-3");
  }else{
  	$MyPage->setFormButtonProperties("butanul", BUTP_PROCHAIN_ECRAN, "Gen-12");
  }
  $MyPage->setFormButtonProperties("butanul", BUTP_CHECK_FORM, false);
  //Javascript
  // verification du mot de passe
  $js="if (document.ADForm.mdp_new1.value != document.ADForm.mdp_new2.value)" .
  		" {msg+= '-"._("Les nouveaux mots de passe doivent être identiques !")."\\n'; ADFormValid = false;}";
  //verification de la longueur du mot mot de passe
    //recuperation nbre de caractère min autorisé
  $AG=getAgenceDatas($global_id_agence);
  $nbre_car_min=$AG["nbre_car_min_pwd"];
  if($nbre_car_min>0){

  	$js.="if (document.ADForm.mdp_new1.value.length <$nbre_car_min)" .
  		" {msg+= '-".sprintf(_("la longueur minimale du mot de passe doit être de '%s' caractères !"),$nbre_car_min)."\\n'; ADFormValid = false;}";

  }
  //verifier si l'utilisateur a retapé le même mot de passe

  	$js.="if (document.ADForm.mdp_new1.value == document.ADForm.mdp_actuel.value)" .
  		" {msg+= '-"._("Vous devriez inscrire un mot de passe différent du mot de passe actuel !")."\\n'; ADFormValid = false;}";

   $MyPage->addJS(JSP_BEGIN_CHECK, "js1",$js);
  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
} else if ($global_nom_ecran == "Mdp-2") { //Changement mot de passe, confirmation

  if (check_pass($global_nom_login, $mdp_actuel)) {

    if ($mdp_new1 == $mdp_new2) { //Si tout OK
      update_pass($global_nom_login, $mdp_new1); //Enregistre le nouveau mot de passe

      //HTML
      $MyPage = new HTML_message(_('Confirmation modification mot de passe'));
      $MyPage->setMessage(_("Votre mot de passe a été modifié avec succès !"));
      if($global_modif_pwd_login){
      	$MyPage->addButton(BUTTON_OK, "Gen-3");
      	$global_modif_pwd_login=false;
      	require_once 'extra_gen/extra_frame.php?m_agc='.$_REQUEST['m_agc'];
      }else{
      	$MyPage->addButton(BUTTON_OK, "Gen-12");
      }
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
    } else { //Si les 2 nouveaux mots de passes ne sont pas équivalents
      $MyPage = new HTML_erreur(_('Echec lors de la  modification du mot de passe'));
      $MyPage->setMessage(_("Les nouveaux mots de passe ne sont pas équivalents."));
      if ($global_modif_pwd_login){
			  $MyPage->addButton(BUTTON_OK,"Mdp-1");
			}else{
				$MyPage->addButton(BUTTON_OK, "Gen-12");
			}
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
      exit();
    }
  } else { //Si le mot de passe actuel n'est pas correct
    //HTML
    $MyPage = new HTML_erreur(_('Erreur modification mot de passe'));
    $MyPage->setMessage(_("Une erreur s'est produite lors de la modification du mot de passe : le mot de passe actuel est incorrect !"));
    if($global_modif_pwd_login){
				$MyPage->addButton(BUTTON_OK, "Mdp-1");
		}else{
				$MyPage->addButton(BUTTON_OK, "Gen-12");
		}
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
} else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Nom d'écran invalide : '$global_nom_ecran'"
?>