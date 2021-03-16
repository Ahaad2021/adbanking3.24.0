#!/bin/bash
############################################################
# Variables utiles
############################################################
let ${dataname:=$(awk -F "=" '/DB_name/ {print $2}' /usr/share/adbanking/web/multiagence/properties/adbanking.ini)}
let ${datapass:=$(awk -F "=" '/DB_pass/ {print $2}' /usr/share/adbanking/web/multiagence/properties/adbanking_pass.ini)}
let ${DBUSER:=adbanking}
let ${DBNAME:=$dataname}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh

TEST=`return_psql "SELECT count(*) FROM pg_database WHERE datname = lower('$DBNAME') "`
#echo -e $TEST
IS_auto=`return_psql "SELECT traite_compensation_automatique FROM ad_agc "`
#echo -e $IS_auto

#Pour cree le fichier txt, si en cas le present Cron n'est pas encore terminé et que le prochaine Cron s'est lancé alors dans ce cas le Cron ne pourra pas se continuer, il faut attendre que le Cron en cours soit terminer
pathJobEncours="/usr/share/adbanking/web/ad_compensation_siege/app/job_encours.txt"


    if [[ "$TEST" >0 ]]
    then

		if [[ "$IS_auto" = 't' ]]
		then
			#Verification Cron en cours
			if [ ! -e $pathJobEncours ]
			then
				#Creation fichier txt pour signaler que ce Cron est actuellement en cours
				touch "$pathJobEncours"
				chmod 777 $pathJobEncours

				# Execution du main script
				echo -e
				echo -e "Debut Execution du Cron..."
				#let ${DATE:=$(date +%d-%m-%Y-%H-%M)}
				DATE_DEBUT=`date`
				echo -e "Execution du cron au $DATE_DEBUT pour l'alimentation compensation automatique du base siege '$DBNAME'!!"
				#Creation du multiagence csv et Execution du Job et Continuation traitement compensation apres JOB
				php /usr/share/adbanking/web/ad_compensation_siege/app/script_compensation.php $DBNAME $datapass
				#echo -e "Creation multiagence csv terminé!!"
				DATE_FIN=`date`
				#echo -e "Execution JOB Talend terminé!!"
				echo -e "Traitement Compensation au siege automatique terminé!!"

				#Retire le fichier txt pour signaler que le Cron est terminé
				rm "$pathJobEncours"

				###############################################################################
				#echo -e "---- DB VACUUM in progress......... ----"
				# vacuum de la base
				#execute_psql 'cmd' "VACUUM FULL ANALYZE"
				#echo -e "----- DB VACUUM Finished -----"
				echo -e "Execution du Cron terminé au $DATE_FIN!!"
			else
				start_t=$(stat -c %Y $pathJobEncours)
				curr=$(date +%s)
				age=$((curr - start_t))
				echo "Le Job Compensation Siege et le traitement compensation sont en cours depuis $age s --\n"
				if [ $age -gt 14400 ]; then
					echo "Job Compensation Siege en cours depuis 4 heures temps. pkill java... --\n"
					pid=`ps -eo pid,user,args | awk '/alim_siege\.alim_siege_0_3\.ALIM_SIEGE/ {print$1}'`
					kill -9 $pid
					rm "$pathJobEncours"
				else
					echo -e
					echo -e "Le present Cron ne peut se continuer car il y a un Cron déjà en cours..."
					echo -e "Il faut attendre que le Cron precedent soit terminer!!"
				fi
			fi
		else
			echo -e "Le parametrage de la l'agence n'est pas activé pour le traitement automatique via cron!!"
		fi

    else
		echo -e "La base siege '$DBNAME' n'exist pas. Veuillez verifier le fichier /usr/share/adbanking/web/multiagence/properties/adbanking.ini!!"
		unset DBNAME
    fi






