#!/bin/bash
############################################################
# Variables utiles
############################################################
#Par defaut le nom de la base a prendre en compte c'est adbanking
let ${DBDEFAULT:=adbanking}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh

#initialisation des variables
unset PASS
unset OPTION
unset DB_USER
DB_USER=""
USERPASS=""

echo
#echo -e "\033[1m=======================================================================================================\033[0m"
echo -e "\033[1m Gestion Mot de passe/Utilisateurs de la base de données \033[0m"
echo -e "\033[1m=======================================================================================================\033[0m"
echo -e "Veuillez choisir une parmi les deux options ci-dessous :"
echo -e "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"
echo -e "1. Automatisme Création MDP pour les utilisateurs existant de la base de données"
echo -e "2. Création d'un nouvel utilisateur base de données avec droit d'acess lecture seulement"
echo -e "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"
read -p ">> Votre choix (1 / 2): " OPTION

unset PASS
#Recuperation mot de passe version encrypté depuis la licence
output_pass=`php  /usr/share/adbanking/web/lib/misc/get_encrypted_password.php $PASS`
PASS=$output_pass
#Mot de passe version en claire
output_pass=`php  /usr/share/adbanking/web/lib/misc/get_decrypted_password.php $PASS`
PASS=$output_pass

#Gestion option 1 et 2
if [[ "$OPTION" == "1" ]]
then
    echo -e "-------------------------------------------------------------------------------------------------------"
    echo -e "[1] Automatisme Création/Mise à jour mot de passe pour les utilisateurs 'postgres' et 'adbanking'"
    echo -e " de la base de données depuis la licence"
    echo -e "-------------------------------------------------------------------------------------------------------"

    #Mot de passe pour l'utilisateur postgres
    #TEST1=`PGPASSWORD=$PASS psql -U postgres -c "ALTER USER postgres WITH ENCRYPTED PASSWORD '$DBPASS';"`
    TESTPOSTGRES=`return_psql "SELECT count(*) FROM pg_roles WHERE rolname='postgres' "`
    if [[ "$TESTPOSTGRES" -gt 0 ]]
    then
        TEST1=`return_psql "ALTER USER postgres WITH ENCRYPTED PASSWORD '$DBPASS'"`
        echo -e "=> Le Mot de passe pour l'utiisateur 'postgres' a été créé/mise à jour!"
    else
        echo -e "\033[1mError !\033[0m => L'utilisateur 'postgres' n'existe pas. Veuillez le créer manuellement puis re-lancer ce script!"
    fi

    #Mot de passe pour l'utilisateur adbanking
    #TEST2=`PGPASSWORD=$PASS psql -U adbanking -c "ALTER USER adbanking WITH ENCRYPTED PASSWORD '$DBPASS';"`
    TESTADBANKING=`return_psql "SELECT count(*) FROM pg_roles WHERE rolname='adbanking' "`
    if [[ "$TESTADBANKING" -gt 0 ]]
    then
        TEST2=`return_psql "ALTER USER adbanking WITH ENCRYPTED PASSWORD '$DBPASS'"`
        echo -e "=> Le Mot de passe pour l'utiisateur 'adbanking' a été créé/mise à jour!"
    else
        echo -e "\033[1mError !\033[0m => L'utilisateur 'adbanking' n'existe pas. Veuillez le créer manuellement puis re-lancer ce script!"
    fi

    echo -e "\033[1m=======================================================================================================\033[0m"
elif [[ "$OPTION" == "2" ]]
then
    echo -e "-------------------------------------------------------------------------------------------------------"
    echo -e "[2] Création d'un nouvel utilisateur base de données avec droit d'acess lecture seulement"
    echo -e "-------------------------------------------------------------------------------------------------------"

    while [[ "$DB_USER" == "" ]]; do
        unset DB
        DB=""
        MSG_ERROR=""
        unset DB_USER
        unset USERPASS
        unset lenpwd

        read -p ">> Veuillez entrer le nom du nouvel utilisateur: " DB_USER
        TESTUSER=`return_psql "SELECT count(*) FROM pg_user WHERE usename='$DB_USER' "`
        if [[ "$DB_USER" =~ [^a-zA-Z0-9] ]]; then
          MSG_ERROR="Le nom d'utilisateur ne doit pas contenir des caractères speciales!"
          TESTUSER=-1
        fi
        if [[ "$TESTUSER" -gt 0 ]]
        then
            MSG_ERROR="Le nom d'utilisateur '$DB_USER' a déjà été utilisé. Veuillez procéder avec un autre nom!"
        fi
        #Si l'utilisateur n'existe pas, il faut le créer
        if [[ "$TESTUSER" == 0 ]]
        then
            #Gestion mot de passe
            while [[ "$USERPASS" == "" ]]; do
                echo -n ">> Veuillez entrer le mot de passe de l'utilisateur '$DB_USER': "
                read -s USERPASS
                echo -e
                unset lenpwd
                lenpwd=${#USERPASS}
                #Gestion ';' dans le mot de passe
                if [[ ! "$USERPASS" == "" && "$USERPASS" == *";"* ]]
                then
                    echo
                    echo -e "\033[1mError !\033[0m => Le mot de passe ne doit pas contenir le caractère ';'"
                    USERPASS=""
                fi
                #Gestion des caractères speciales
                if [[ ! "$USERPASS" == "" && "$USERPASS" == *['!@#\$%^\&*()_+']* ]]
                then
                    echo
                    echo -e "\033[1mError !\033[0m => Le mot de passe ne doit pas contenir des caractères speciales!"
                    USERPASS=""
                fi
                #Gestion nombres de caractères dans le mot de passe
                if [[ $lenpwd -lt 8 ]]
                then
                    echo
                    echo -e "\033[1mError !\033[0m => Le mot de passe doit contenir au minimum 8 caractères!"
                    echo -e "Le nombre de caractères renseigné est de $lenpwd qui est inférieure de 8!"
                    USERPASS=""
                fi
            done

            #Creation d'un group role avec comme nom 'read_only'
            TESTROLE=`return_psql "SELECT count(*) FROM pg_roles WHERE rolname='read_only' "`
            #Si le role n'existe pas, il faut le créer
            if [[ "$TESTROLE" == 0 ]]
            then
                CREATEROLE=`return_psql "CREATE ROLE read_only"`
            fi

            echo -e ">> Veuillez entrer le nom de la base de données pour donner les droits à l'utilisateur '$DB_USER'"
            read -p "(Par défaut c'est 'adbanking' si vous n'avez rien entré): " DB
            if [[ "$DB" == "" ]]
            then
                let ${DBNAME:=$DBDEFAULT}
            else
                TESTDB=`return_psql "SELECT count(*) FROM pg_database WHERE datname = lower('$DB') "`
                #Si la base de données n'existe pas
                while [[ "$TESTDB" == 0 ]]; do
                    echo
                    echo -e "\033[1mError !\033[0m => La base de données specifiée n'existe pas"
                    echo -e "Veuillez entrer correctement le nom d'une base de données existante!"
                    unset DB
                    echo
                    echo -e ">> Veuillez re-entrer le nom de la base de données"
                    read -p "(Par défaut c'est 'adbanking' si vous n'avez rien entré): " DB
                    if [[ "$DB" == "" ]]
                    then
                        let ${DB:=$DBDEFAULT}
                    fi
                    TESTDB=`return_psql "SELECT count(*) FROM pg_database WHERE datname = lower('$DB') "`
                done
                let ${DBNAME:=$DB}
            fi

            #Donner les droits d'acces lecture seulement au role read_only par rapport à la base de données
            TESTROLE=`return_psql "SELECT count(*) FROM pg_roles WHERE rolname='read_only' "`
            #Si le role a été créé ou existe déjà
            if [[ "$TESTROLE" == 1 ]]
            then
                GRANTUSAGE=`return_psql "GRANT USAGE ON SCHEMA public TO read_only;"`
                GRANTSELECTONLY=`return_psql "GRANT SELECT ON ALL TABLES IN SCHEMA public TO read_only;"`
                GRANTFUTURE=`return_psql "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO read_only;"`
            fi

            #Création utilisateur
            CREATEUSER=`return_psql "CREATE USER $DB_USER WITH ENCRYPTED PASSWORD '$USERPASS';"`
            GRANTUSER=`return_psql "GRANT read_only TO $DB_USER;"`
            echo -e "-------------------------------------------------------------------------------------------------------"
            echo -e "=> L'utilisateur '$DB_USER' avec son MDP a été créé!"
            echo -e "=> Les droits d'acces lecture seulement a été donné à l'utilisateur '$DB_USER' sur la base '$DBNAME'!"
            echo -e "=> A decider par ADFinance : Les details de ce nouvel utilisateur seront à mettre à disposition des "
            echo -e "   IT Officers concernés des IMFs!"
        else
            echo
            echo -e "\033[1mError !\033[0m => $MSG_ERROR"
            DB_USER=""
            echo -e
        fi
    done
    echo -e "\033[1m=======================================================================================================\033[0m"
else
    echo -e "\033[1mError !\033[0m => L'option '$OPTION' saisie n'est pas la bonne. Veuillez saisir l'option convenable (1 / 2)!"
    echo -e "\033[1m=======================================================================================================\033[0m"
    unset OPTION
    DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
    source ${DIR}/gestion_mdp_utilisateur.sh
fi


