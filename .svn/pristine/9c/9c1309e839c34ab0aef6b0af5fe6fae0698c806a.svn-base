#!/bin/bash
############################################################
# Variables utiles
############################################################

source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh
unset DBPASS
echo
echo -e "--------------------------------------------------------------------------"
echo -e "Mise à jour  mot de passe base de données depuis la licence"
echo -e "--------------------------------------------------------------------------"

    unset DBPASS

    output=`php  /usr/share/adbanking/web/lib/misc/get_encrypted_password.php`

    DBPASS=$output

echo -e "Mot de passe : \033[1m ${DBPASS} \033[0m"
sed -i "s/\(DB_pass = \).*\$/\1${DBPASS}/" /usr/share/adbanking/web/adbanking.ini
sed -i "s/\(DB_pass =\).*\$/\1${DBPASS}/" /usr/share/adbanking/web/adbanking.ini
sed -i "s/\(DB_pass = \).*\$/\1${DBPASS}/" /usr/share/adbanking/web/jasper_config/*.ini
sed -i "s/\(DB_pass =\).*\$/\1${DBPASS}/" /usr/share/adbanking/web/jasper_config/*.ini
sed -i "s/\(DB_pass =\).*\$/\1${DBPASS}/" /usr/share/adbanking/web/multiagence/properties/adbanking_pass.ini
echo -e "Les fichiers suivant ont été mis à jour pour le mot de passe:"
echo -e "=> /usr/share/adbanking/web/adbanking.ini"
echo -e "=> /usr/share/adbanking/web/jasper_config/adbanking<id_agence>.ini"
echo -e "=> /usr/share/adbanking/web/multiagence/properties/adbanking_pass.ini"
echo -e "--------------------------------------------------------------------------"


