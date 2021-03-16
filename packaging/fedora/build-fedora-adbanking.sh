#!/bin/bash
# Name: build-fedora-adbanking.sh
# Description: Script d'aide à la d'une distribution Fedora adaptée à ADbanking

if [ $USER != 'root' ];
then
  echo "Erreur : il faut être root pour exécuter cette commande"
  exit 1
fi

REVISOR=`which revisor`
if [ ! -e $REVISOR ];
then
  echo "Erreur : il faut que l'utilitaire revisor soit installé correctement pour utiliser ce script"
  exit 1
fi

$REVISOR --cli --kickstart=adbanking-ks.cfg --config=revisor.conf --model=f7-i386 --kickstart-default --kickstart-include --yes
