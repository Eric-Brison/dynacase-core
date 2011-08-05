#!/bin/bash 
# $Id: freedom-cleanattr.sh,v 1.1 2008/05/29 05:54:07 marc Exp $
if [ "$pgservice_core" == "" ]; then
    #load environement variable for freedom
  . /etc/freedom.conf
   wchoose -b
fi

if [ "$#" -lt 1 ] ; then
        echo "select name,title from docfam;" | PGSERVICE=$pgservice_freedom psql -E
        echo "Indiquez en paramètre le(s) famille(s) pour laquelle les attributs seront nettoyés (cf liste ci-dessus)"
   exit
fi

while [ $# -ge 1 ]; do
    idFamille=$1
    restitle=`echo "select title from docfam where name='"$idFamille"'" | PGSERVICE=$pgservice_freedom psql -E -A -x | awk -F"|" '{ print $2 }'`
    resid=`echo "select id from docfam where name='"$idFamille"'" | PGSERVICE=$pgservice_freedom psql -E -A -x | awk -F"|" '{ print $2}'`

    if [ "$resid" = "" ]; then
	echo "**E** Pas de famille $idFamille"
	exit
    else
	echo "> Famille $restitle ($idFamille/$resid)"

	echo "delete from docattr where docid=$resid" | PGSERVICE=$pgservice_freedom psql -e
    fi
    shift   
done
exit
