#!/bin/bash
############################################################
# Variables utiles
############################################################
let ${dataname:=$(awk -F "=" '/DB_name/ {print $2}' /usr/share/adbanking/web/adbanking.ini)}
let ${datapass:=$(awk -F "=" '/DB_pass/ {print $2}' /usr/share/adbanking/web/adbanking.ini)}
let ${DBUSER:=adbanking}
let ${DBNAME:=$dataname}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh

TEST=`return_psql "SELECT count(*) FROM pg_database WHERE datname = lower('$DBNAME') "`
echo -e $TEST


    if [[ "$TEST" >0 ]]
    then

		# Execution du main script
		echo -e
		echo -e "Debut Execution du Cron..."
		let ${DATE:=$(date +%d-%m-%Y-%H-%M)}
		echo -e "Execution du cron à $DATE pour l'alimentation des transactions Angrais Chimiques depuis les base filles jusqu'au base siege '$DBNAME'!!"
		php /usr/share/adbanking/web/ad_acu/app/script_globalisation.php $DBNAME $datapass >> /usr/share/adbanking/web/ad_acu/app/phplog.log

		echo -e "Traitement Alimentation au siege automatique terminé!!"

        ###############################################################################
        echo -e "---- DB VACUUM in progress......... ----"
        # vacuum de la base
        execute_psql 'cmd' "VACUUM FULL ANALYZE"
        echo -e "----- DB VACUUM Finished -----"
        echo -e "Execution du Cron terminé!!"
    else
        unset DBNAME
		echo -e "La base siege '$DBNAME' n'exist pas. Veuillez verifier le fichier /usr/share/adbanking/web/adbanking.ini!!"
    fi






