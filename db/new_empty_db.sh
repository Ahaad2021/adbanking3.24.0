#!/bin/bash
# vim: set expandtab softtabstop=2 shiftwidth=2:

############################################################
# Variables utiles
############################################################
let ${DBUSER:=adbanking}
let ${DBNAME:=$DBUSER}
let ${PSQLUSER:=postgres}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh

# Vérification de l'utilisateur
#------------------------------
if [ $USER != 'root' ];
then
    echo "Erreur : il faut être root pour exécuter cette commande"
    exit 1
fi

# Création d'une db vide initiale
#---------------------------------
echo -e "\033[1m==========\033[0m Suppression de la base de données existante"
CMD="dropdb $DBNAME"
su -m $PSQLUSER -c "$CMD" 2>/dev/null
echo -e ""
if [[ $? > 0 ]]; then
  echo
  echo -e "\033[1m========= Problème d'accès à la BD\033[0m"
  echo -e "Il faut peut-être faire redémarrer postgresql : \033[1m/etc/init.d/postgresql restart\033[0m"
  echo -e "Ou créer manuellement la base de données \033[1madbanking\033[0m si elle n'existe pas."
  exit
fi
CMD="dropuser $DBUSER"
su -m $PSQLUSER -c "$CMD" 2>/dev/null
CMD="droplang plpgsql $DBNAME"
su -m $PSQLUSER -c "$CMD" 2>/dev/null

echo -e "\033[1m==========\033[0m Création d'un utilisateur et d'une base de données vide"
CMD="createuser -sdR $DBUSER"
su -m $PSQLUSER -c "$CMD"
CMD="createdb -U $DBUSER --encoding=UNICODE $DBNAME"
su -m $PSQLUSER -c "$CMD"
if [[ $? > 0 ]];
then
    echo "Erreur : il faut que la méthode 'trust' soit utilisée pour les connexions locales dans pg_hba.conf pour exécuter cette commande"
    exit 1
fi
CMD="createlang plpgsql $DBNAME"
su -m $PSQLUSER -c "$CMD"
CMD="echo \"ALTER ROLE $DBUSER SET datestyle = 'iso, dmy'\" | psql $DBUSER 2>/dev/null"
su -m $PSQLUSER -c "$CMD"

# Message de confirmation
#------------------------
echo -e "\033[1m========== La base de donnée ADbanking vide est créée\033[0m"
echo -e "Vous pouvez maintenant charger une BD sauvegardée grâce à \033[1mload_bd.sh\033[0m"
echo -e "Ou continuer la procédure de création d'une BD complète ADbanking"
