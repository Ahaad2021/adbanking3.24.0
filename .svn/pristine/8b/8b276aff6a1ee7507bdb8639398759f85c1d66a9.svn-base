#!/bin/bash
# Ce script permet de crypter un fichier de licence
############################################################
# Variables utiles
############################################################
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh
let ${DBUSER:=adbanking}
let ${DBNAME:=$DBUSER}

php -d include_path=".:/usr/share/pear:${ADB_INSTALL_DIR}/web" ${ADB_INSTALL_DIR}/bin/crypte.php /tmp/licence_decrypte.bin /tmp/licence.bin public
