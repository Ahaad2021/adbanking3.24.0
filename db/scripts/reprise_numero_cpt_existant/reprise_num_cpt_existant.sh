#!/bin/bash


############################################################
# Variables utiles
############################################################
let ${DBUSER:=adbanking}
let ${DBNAME:=$DBUSER}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh 

execute_psql 0 script.sql

echo -e "ADBanking script reprise numero compte existant"

echo
echo -e "\033[1mFiles have been copied successfully !\033[0m"
