#!/bin/bash
############################################################
# Variables utiles
############################################################

# On convertit le text foruni par l'utilisateur
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh
unset plain_text
echo
echo -e "Veuillez entrer le texte à encrypter : "

unset plain_text
read  plain_text

encrypted_text=`php /usr/share/adbanking/web/lib/misc/encryptMessageQueue.php $plain_text`

echo -e "Voici le text en version encrypté :"
echo $encrypted_text