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



    if [[ "$TEST" >0 ]]
    then

		# Execution du main script
		echo -e
		echo -e "Debut Execution du Cron..."
		#let ${DATE:=$(date +%d-%m-%Y-%H-%M)}
		START_DATE=`date`
		echo -e "[$START_DATE]Execution du cron mensuel de mise à jour des données et scores des clients abonnés"
		php /usr/share/adbanking/web/services/mobile_lending/algo_scoring_client_abonnee.php $DBNAME $datapass >> /usr/share/adbanking/web/services/mobile_lending/cron/phplog.log

		echo -e "Traitement cron mensuel terminé!!"

        ###############################################################################
        echo -e "---- DB VACUUM in progress......... ----"
        # vacuum de la base
        execute_psql 'cmd' "VACUUM FULL ANALYZE"
        echo -e "----- DB VACUUM Finished -----"
        echo -e "Execution du Cron terminé!!"
    else
        unset DBNAME
		echo -e "La base '$DBNAME' n'existe pas. Veuillez verifier le fichier /usr/share/adbanking/web/adbanking.ini!!"
    fi


