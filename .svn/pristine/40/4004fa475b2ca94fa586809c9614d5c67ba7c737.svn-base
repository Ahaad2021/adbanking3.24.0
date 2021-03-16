#!/bin/bash
# vim: set expandtab softtabstop=2 shiftwidth=2:
# Arguments :
# $1 base de données à charger, tous les formats doivent être reconnus, gzippé ou non.
#
# Utilisation :
# load_bd.sh backup.sql.gz
#
# Il faut que les utilitaires postgresql soit dans votre $PATH pour que ce script fonctionne.
############################################################
# Variables utiles
############################################################
let ${DBUSER:=adbanking}
let ${DBNAME:=adbanking}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh
tmp_file="/tmp/load_bd_adbanking.in"
tmp_sql="/tmp/load_bd_adbanking.sql"

echo -e "\033[1mChargement d'une base de données sauvegardée\033[0m : $1"
if [[ $1 == "" ]]; then
  echo -e "\033[1mErreur\033[0m : il faut donner le fichier dump de la base en argument."
  exit
fi
if [[ ! -f $1 ]]; then
  echo -e "\033[1mErreur\033[0m : le fichier dump donné est inexistant."
  exit
fi
PGOPTIONS='-c client_min_messages=warning'
export PGOPTIONS
dropdb -U $DBUSER $DBNAME
if [[ $? > 0 ]]; then
  echo
  echo -e "\033[1m========= Problème d'accès à la BD\033[0m"
  echo -e "Il faut peut-être faire redémarrer postgresql : \033[1m/etc/init.d/postgresql restart\033[0m"
  echo -e "Ou créer manuellement la base de données \033[1madbanking\033[0m si elle n'existe pas."
  exit
fi
createdb -U $DBUSER $DBNAME
gzip -t $1 2>/dev/null
if [[ $? > 0 ]]; then
  # L'archive n'est pas compressée
  cp -f $1 $tmp_file
else
  # L'archive est compressée
  gzip -dc $1 > $tmp_file
fi

# On essaye à présent de faire la restauration
pg_restore $tmp_file > $tmp_sql 2>/dev/null
if [[ $? > 0 ]]; then
  # On a une erreur, le fichier n'est donc pas une archive créée par pg_dump
  # C'est peut-être un simple fichier SQL
  # Attention, l'option --mime est plus compatible que -i (sous OSX, c'est -I !)
  type=`file -b --mime $tmp_file`
  if [[ "$type" == "text/x-c; charset=utf-8" || "$type" == "text/plain; charset=utf-8" ]]; then
    # On a un fichier texte qui contient des commandes SQL (reconnu comme C par file), c'est probablement bon
    archive=$tmp_file
  else
    # On n'est pas sûr du fichier, on ne fait rien
    archive="x";
  fi
else
  archive=$tmp_sql
fi
if [[ $archive != "x" ]]; then
  # On ignore les messages d'erreurs sur triggers.so et trig_suppr_trad car ils ne sont plus utilisés, voir #1056.
  (execute_psql 'file' $archive) 3>&1 1>&2 2>&3 | grep -v triggers.so | grep -v trig_suppr_trad
  echo "La restauration est terminée !"
else
  echo -e "\033[1mL'archive n'a pas été reconnue !\033[0m"
  exit 1
fi

# On supprime les fichiers temporaires
rm -rf $tmp_file
rm -rf $tmp_sql

# Après avoir rechargé un dump d'une BD, il est bon de faire un ANALYZE pour avoir les meilleures performances possibles
echo "Optimisons à présent la BD."
execute_psql 'cmd' "VACUUM FULL ANALYZE"

