#!/bin/bash

source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh

# Parametres pour le message queue
MSQ_HOST=$(awk -F "=" '/MSQ_HOST/ {print $2}' /usr/share/adbanking/web/adbanking.ini)
let ${MSQ_PORT:=$(awk -F "=" '/MSQ_PORT/ {print $2}' /usr/share/adbanking/web/adbanking.ini)}
let ${MSQ_USERNAME:=$(awk -F "=" '/MSQ_USERNAME/ {print $2}' /usr/share/adbanking/web/adbanking.ini)}
let ${MSQ_PASSWORD:=$(awk -F "=" '/MSQ_PASSWORD/ {print $2}' /usr/share/adbanking/web/adbanking.ini)}
let ${MSQ_VHOST:=$(awk -F "=" '/MSQ_VHOST/ {print $2}' /usr/share/adbanking/web/adbanking.ini)}
let ${MSQ_EXCHANGE_NAME:=$(awk -F "=" '/MSQ_EXCHANGE_NAME/ {print $2}' /usr/share/adbanking/web/adbanking.ini)}
let ${MSQ_EXCHANGE_TYPE:=$(awk -F "=" '/MSQ_EXCHANGE_TYPE/ {print $2}' /usr/share/adbanking/web/adbanking.ini)}
let ${MSQ_QUEUE_NAME_MOUVEMENT:=$(awk -F "=" '/MSQ_QUEUE_NAME_MOUVEMENT/ {print $2}' /usr/share/adbanking/web/adbanking.ini)}
MSQ_ROUTING_KEY_MOUVEMENT=$(awk -F "=" '/MSQ_ROUTING_KEY_MOUVEMENT/ {print $2}' /usr/share/adbanking/web/adbanking.ini)
let ${MSG_MOUVEMENT_LIFETIME:=$(awk -F "=" '/MSG_MOUVEMENT_LIFETIME/ {print $2}' /usr/share/adbanking/web/adbanking.ini)}

# Retirer les espaces vides
MSQ_HOST="$(echo -e "${MSQ_HOST}" | tr -d '[:space:]')"
MSQ_PORT="$(echo -e "${MSQ_PORT}" | tr -d '[:space:]')"
MSQ_USERNAME="$(echo -e "${MSQ_USERNAME}" | tr -d '[:space:]')"
MSQ_PASSWORD="$(echo -e "${MSQ_PASSWORD}" | tr -d '[:space:]')"
MSQ_VHOST="$(echo -e "${MSQ_VHOST}" | tr -d '[:space:]')"
MSQ_EXCHANGE_NAME="$(echo -e "${MSQ_EXCHANGE_NAME}" | tr -d '[:space:]')"
MSQ_EXCHANGE_TYPE="$(echo -e "${MSQ_EXCHANGE_TYPE}" | tr -d '[:space:]')"
MSQ_QUEUE_NAME_MOUVEMENT="$(echo -e "${MSQ_QUEUE_NAME_MOUVEMENT}" | tr -d '[:space:]')"
MSQ_ROUTING_KEY_MOUVEMENT="$(echo -e "${MSQ_ROUTING_KEY_MOUVEMENT}" | tr -d '[:space:]')"
MSG_MOUVEMENT_LIFETIME="$(echo -e "${MSG_MOUVEMENT_LIFETIME}" | tr -d '[:space:]')"

# Recuperer les fichier sur le repertoire jasper_config
cd /usr/share/adbanking/web/jasper_config/
for file in *; do
    if [ -f "$file" ]; then
        unset datauser
        unset dataname
        unset datapass
        unset DBUSER
        unset DBNAME
        unset TEST

        let ${datauser:=$(awk -F "=" '/DB_user/ {print $2}' "/usr/share/adbanking/web/jasper_config/$file")}
        let ${dataname:=$(awk -F "=" '/DB_name/ {print $2}' "/usr/share/adbanking/web/jasper_config/$file")}
        let ${datapass:=$(awk -F "=" '/DB_pass/ {print $2}' "/usr/share/adbanking/web/jasper_config/$file")}

        let ${DBUSER:=$datauser}
        let ${DBNAME:=$dataname}

        # Retirer les espaces vides
        DBUSER="$(echo -e "${DBUSER}" | tr -d '[:space:]')"
        DBNAME="$(echo -e "${DBNAME}" | tr -d '[:space:]')"
        datapass="$(echo -e "${datapass}" | tr -d '[:space:]')"

        TEST=`return_psql "SELECT count(*) FROM pg_database WHERE datname = lower('$DBNAME') "`

        if [[ "$TEST" >0 ]]
        then
            # Execution du main script
            echo -e
            DECRYPTED_MSQ_HOST=`php /usr/share/adbanking/web/lib/misc/get_decryption_message_queue.php $MSQ_HOST`

            DECRYPTED_MSQ_USERNAME=`php /usr/share/adbanking/web/lib/misc/get_decryption_message_queue.php $MSQ_USERNAME`

            DECRYPTED_MSQ_PASSWORD=`php /usr/share/adbanking/web/lib/misc/get_decryption_message_queue.php $MSQ_PASSWORD`

            START_DATE=`date`
            echo -e "[$START_DATE] Debut Execution du Cron Mouvement..."
            echo -e "Execution du cron pour la publication des messages sur le broker pour la base '$DBNAME'!!"

            php /usr/share/adbanking/web/lib/php-amqplib/failoverMouvementMSQ.php $DBNAME $datapass $DECRYPTED_MSQ_HOST $MSQ_PORT $DECRYPTED_MSQ_USERNAME $DECRYPTED_MSQ_PASSWORD $MSQ_VHOST $MSQ_EXCHANGE_NAME $MSQ_EXCHANGE_TYPE $MSQ_QUEUE_NAME_MOUVEMENT $MSQ_ROUTING_KEY_MOUVEMENT $MSG_MOUVEMENT_LIFETIME

            END_DATE=`date`
            echo -e "[$END_DATE] FIN Execution du Cron Mouvement!!"

        else
            unset DBNAME
            echo -e "La base '$DBNAME' n'exist pas. Veuillez verifier le fichier /usr/share/adbanking/web/adbanking.ini!!"
        fi

    fi
done