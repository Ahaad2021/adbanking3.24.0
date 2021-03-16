#!/bin/bash
# Ce script est appelé par le cronjob de l'utilisateur apache
############################################################
# Variables utiles
############################################################
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh
let ${DBUSER:=adbanking}
let ${DBNAME:=$DBUSER}
let ${ADB_BACKUP_DIR:="/var/lib/adbanking/backup"}

# Exécution du batch d'ADbanking
/usr/bin/php -d include_path=".:/usr/share/pear:${ADB_INSTALL_DIR}/web" ${ADB_INSTALL_DIR}/web/batch/batch.php > ${ADB_BACKUP_DIR}/images_tmp/batch_result.html 2> /dev/null
if [ `grep ROLLBACK ${ADB_BACKUP_DIR}/images_tmp/batch_result.html |  wc -l` = 0 ]
then
	echo "Le batch s'est déroulé correctement."
else
	echo "Le batch a échoué."
fi
echo "Pour voir le résultat du batch, veuillez vous connecter à l'adresse http://`hostname`/adbanking/images_tmp/batch_result.html"


# Exécution du script php de consolidation des agences
/usr/bin/php -d include_path=".:/usr/share/pear:${ADB_INSTALL_DIR}/web" ${ADB_INSTALL_DIR}/web/modules/systeme/script_consolidation_db.php > ${ADB_BACKUP_DIR}/images_tmp/consolidation_result.html 2> /dev/null
if [ `grep ROLLBACK ${ADB_BACKUP_DIR}/images_tmp/consolidation_result.html | wc -l` = 0 ]
then
        echo "La consolidation a été effectuée avec succès."
else
        echo "La consolidation a échouée."
fi
echo "Pour voir le résultat, veuillez vous connecter à l'adresse http://`hostname`/adbanking/images_tmp/consolidation_result.html"
~

