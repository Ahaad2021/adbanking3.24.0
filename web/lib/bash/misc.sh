#!/bin/bash
# vim: set expandtab softtabstop=2 shiftwidth=2:

# Déclaration de variables utiles pour toutes les fonctions
# Utilisateur sous lequel tourne Postgres
let ${PSQLUSER:=postgres}
#Recupere le mot de passe de l'utilisateur base de donnees
Encrypted_Pass=`php  /usr/share/adbanking/web/lib/misc/get_encrypted_password.php`
Decrypted_Pass=`php  /usr/share/adbanking/web/lib/misc/get_decrypted_password.php $Encrypted_Pass`
DBPASS=$Decrypted_Pass
# Option supplémentaire pour PSQL si on n'est pas l'utilisateur root ni celui de la BD (adbanking)
if [[ $USER != 'root' && $USER != $DBUSER ]]; then
  let ${PSQLOPT:=-U $DBUSER}
else
  let ${PSQLOPT:=""}
fi

# Fonctions utiles dans les scripts bash liés à ADbanking

ask_file()
{
  # Demande le nom d'un fichier et vérifie s'il existe
  # $1 : Le prompt pour demander le nom du fichier à l'utilisateur
  echo -e "Entrez le nom (et chemin) du fichier $1 :"
  read FILE_NAME
  while [[ "${FILE_NAME}" != "" ]] ; do
    # Tester l'existence du fichier
    if [[ -f ${FILE_NAME} ]] ; then
      return 0
    else
      echo -e "\033[1mFichier $FILE_NAME non existant.\033[0m"
      echo -e "Entrez le nom (et chemin) du fichier $1 :"
      read FILE_NAME
    fi
  done
}

execute_psql()
{
  # Exécute une requête SQL en tant que l'utilisateur $DBUSER et sur BD $DBNAME
  # (ce qui correspond à une installation traditionnelle d'ADbanking)
  # Le premier paramètre ($1) indique la nature du second (voir ci-dessous)
  #   - $1 == 'cmd' : $2 est une chaîne avec une requête SQL
  #   - $1 != 'cmd' : $2 est le nom d'un fichier avec les requêtes SQL
  # $3 contient une chaîne avec des options supplémentaires à passer à psql
  #    (ce paramètre est par exemple utile pour travailler sur une BD différente que celle portant le nom de l'utilisateur courant)
  # Les messages de type 'NOTICE' sont supprimés de l'output
  PGOPTIONS='-c client_min_messages=warning'
  export PGOPTIONS
  PSQLOPT="$PSQLOPT $3"
  if [[ $1 == 'cmd' ]] ; then
    # $2 contient une chaîne avec la requête SQL
    CMD="PGPASSWORD=$DBPASS psql $DBNAME $PSQLOPT -c \"$2\" | awk 'BEGIN{ORS = \"\";}{print \".\";fflush()}END{print \"\n\"}' ;"
  else
    # $2 contient le nom d'un fichier avec les requêtes SQL
    CMD="PGPASSWORD=$DBPASS psql $DBNAME $PSQLOPT -f $2 | awk 'BEGIN{ORS = \"\";}{print \".\";fflush()}END{print \"\n\"} ' ;"
  fi
  if [ $USER == 'root' ]; then
    su -m $PSQLUSER -c "$CMD"
  else
    eval "$CMD"
  fi
}

return_psql()
{
  # Exécute une requête SQL en tant que l'utilisateur $DBUSER et sur la BD $DBNAME et retourne la valeur de la requête
  # $1 est une chaîne avec une requête SQL
  # $2 est une chaîne avec des options supplémentaires à passer à psql
  # $3 contient une chaîne avec des options supplémentaires à passer à psql
  # Aucune vérification n'est faite sur l'output généré par psql
  PGOPTIONS='-c client_min_messages=warning'
  export PGOPTIONS
  PSQLOPT="$PSQLOPT $3"
  CMD="PGPASSWORD=$DBPASS psql $DBNAME $PSQLOPT $2 -Atc \"$1\""
  if [ $USER == 'root' ]; then
    su -m $PSQLUSER -c "$CMD"
  else
    eval "$CMD"
  fi
}

get_nom_langue()
{   # Prend en parametre un code langue et renvoie dans la variable nom_langue le nom de la langue en francais
  if [ ! -e $ADB_INSTALL_DIR/web/locale/$1/nom_langue ] ; then
    dialog --colors --clear --title "Erreur" --msgbox "ERREUR pour la langue \Zb\Z3$1\Z0\ZB, le fichier\n$ADB_INSTALL_DIR/web/locale/$1/nom_langue\nn'existe pas !" 0 0
    terminer
  fi

  nom_langue="`cat $ADB_INSTALL_DIR/web/locale/$1/nom_langue`"
}

