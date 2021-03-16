<?php
require_once 'lib/misc/VariablesGlobales.php';

//En-tête doc
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
echo "<html><head><title>$ProjectName</title></head>";

//Définit la taille et la position de la fenêtre
echo "<script type=\"text/javascript\">\n";
echo "if (navigator.appName.charAt(0) != 'N'){\n"; //Si ce n'est pas Netscape (donc si c'est IE), on resize.
echo "window.resizeTo(screen.availWidth, screen.availHeight);\n";
echo "window.moveTo(0,0);";
echo "}\n";
echo "</script>\n";

//Division haut/bas
echo '<frameset rows="80,*" border=0 frameborder="no">';

//Définition frame du haut
echo '<frame name="status_frame" noresize scrolling="no" src="../login/top_login.php">';

//Division gauche/droite dans le bas
echo '<frameset cols="250,*" border=0 frameborder="no">';

//Définition frame gauche
echo '<frame name="menu_frame" noresize scrolling="no" src="../login/left_login.php">';

//Division haut/bas dans le frame de droite
echo '<frameset rows="280,*" border=0 frameborder="no">';

//Définition frame droit haut (principal)
echo '<frame name="main_frame" noresize src="../login/main_login.php?m_agc='.$_REQUEST['m_agc'].'">';

//Définition frame droit bas
echo '<frame name="main_frame_bottom" noresize src="../login/logos.php">';

//Fin division haut/bas à droite
echo "</frameset>";

//Fin division gauche/droite
echo "</frameset>";

//Fin division haut/bas
echo "</frameset>";

//Fin doc
echo "<noframes><body><p><i>"._("ADbanking nécessite un navigateur supportant les frames.")."</i></p></body></noframes></html>";
?>