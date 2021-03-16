#!/bin/bash
# Ce script permet de decrypter un fichier de demande de licence
############################################################
# Variables utiles
############################################################
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh
let ${DBUSER:=adbanking}
let ${DBNAME:=$DBUSER}

/usr/bin/php -d include_path=".:/usr/share/pear:${ADB_INSTALL_DIR}/web" ${ADB_INSTALL_DIR}/bin/decrypte.php /tmp/licence_request.bin /tmp/licence_request_decrypte.bin public
