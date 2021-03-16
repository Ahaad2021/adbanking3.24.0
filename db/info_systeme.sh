#!/bin/bash
############################################################
# Variables utiles
############################################################
#let ${DBUSER:=adbanking}
#let ${DBNAME:=$DBUSER}
#source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh 

echo -e "Mise a jour infos systeme"

# Relecture des fonctions et triggers

version_os=`cat /etc/*-release`
version_php=`php -v`
version_appache=`httpd -v`


return_psql "SELECT insert_adsys_infos_systeme ('$DIR','$version_os','$version_php','$version_appache');"

echo

	