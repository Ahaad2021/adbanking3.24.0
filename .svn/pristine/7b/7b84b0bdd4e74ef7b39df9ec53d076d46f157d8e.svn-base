<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Ce fichier est appelé lorsqu'il y a eu une perte de session
 *
 * Définit le contenu du frame principal, l'écran à afficher est stocké dans la variable : $prochain_ecran
 * @package Ifutilisateur
 */

require("lib/html/HtmlHeader.php");
echo "<br><h1 align=\"center\">"._("Perte de la connexion")."</h1><br /><br />";
echo "<p>"._("La connexion au serveur a été perdue probablement pour une des raisons suivantes")." :</p>";
echo "<ul>";
echo "<li>"._("Vous vous êtes connecté sur une autre machine avec ce même login")."</li>";
echo "<li>"._("Un autre utilisateur s'est connecté sur cette machine")."</li>";
echo "<li>"._("Vous avez tenté de vous connecter sans vous identifier")."</li>";
echo "</ul>";
echo "<p>"._("Vous allez être redirigé vers la phase d'identification afin de créer une nouvelle connexion.")."</p>";
echo "<form name=\"ADForm\"><p align=\"center\"><input type=\"button\" value=\""._("OK")."\" onclick=\"redirect();\" /></p></form>";

echo "<script type=\"text/javascript\">\n";
echo "function redirect(){";
echo "window.parent.location = \"$SERVER_NAME/login/login.php\";\n";
echo "}";
echo "</script>\n";

require("lib/html/HtmlFooter.php");

?>