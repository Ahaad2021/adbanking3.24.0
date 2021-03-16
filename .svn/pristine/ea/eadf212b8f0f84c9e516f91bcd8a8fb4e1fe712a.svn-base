#!/bin/bash
############################################################
# Variables utiles
############################################################
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh
let ${DBUSER:=adbanking}
let ${DBNAME:=$DBUSER}

echo -e "Reset des mots de passe à \033[1mpublic\033[0m"
execute_psql "cmd" "UPDATE ad_log set pwd = md5('public');"

