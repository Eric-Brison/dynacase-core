#!/bin/sh -norc 


. /var/post-install/share/pi-functions.sh

#------------------------------
#post installation
#------------------------------
if [ "$1" = 1 ] ; then

  run=`pidof postmaster`
  if [ "x$run" = "x" ] ; then
    log "postgresql is not running ..Init is delayed"
    exit 1
  fi

  log "Create anakeen database"
  sulog postgres "createdb --encoding LATIN1 anakeen"
  
  log "Create anakeen database user"
  sulog postgres "createuser -d -a anakeen" 
 
  log "Add plpgsql language"
  sulog postgres "createlang --dbname=anakeen --pglib=/usr/lib/pgsql plpgsql"

  log "Add plpgsql functions"
  sulog postgres "psql freedom anakeen -f /home/httpd/what/WHAT/getprivilege.sql"

  log "Register DB anakeen for automatic dump"
  ll=0
  if [ -f /etc/ankpsql-tools/base-list ] ; then
    ll=`cat /etc/ankpsql-tools/base-list | grep "^anakeen$" | wc -l`
  fi
  if [ $ll -eq 0 ]; then 
    echo "Register DB anakeen for automatic dump"
    echo "anakeen" >> /etc/ankpsql-tools/base-list
  fi



# Add What log facility in syslog
  echo "local6.*				/var/log/what.log" >> /etc/syslog.conf
  /etc/rc.d/init.d/syslog restart
  echo "/var/log/what.log {"               >  /etc/logrotate.d/what
  echo "   missingok"                      >> /etc/logrotate.d/what
  echo "   postrotate"                     >> /etc/logrotate.d/what
  echo "   /usr/bin/killall -HUP syslogd"  >> /etc/logrotate.d/what
  echo "   endscript"                      >> /etc/logrotate.d/what
  echo "}"                                 >> /etc/logrotate.d/what



  # now all functions must return 0
  set -e

  #add permission functionnalities
  sulog postgres "psql anakeen anakeen -f /home/httpd/what/WHAT/getprivilege.sql"

  # Init the database with all App
  log "Initiate Database elements"
  /home/httpd/what/wsh.php --api=appadmin  --appname=CORE
  /home/httpd/what/wsh.php --api=appadmin  --appname=USERS
  /home/httpd/what/wsh.php --api=appadmin  --appname=APPMNG
  /home/httpd/what/wsh.php --api=appadmin  --appname=AUTHENT
  /home/httpd/what/wsh.php --api=appadmin  --appname=ACCESS

  /home/httpd/what/wsh.php  --api=import_style --name=RED
  /home/httpd/what/wsh.php  --api=import_style --name=GREEN
fi


#------------------------------
#post uninstallation
#------------------------------
if [ "$1" = 0 ] ; then

  set -e
  # drop anakeen database and user
  log "The anakeen database will be dropped, we save a dump in /tmp/anakeen$$.dump"
  sulog  postgres  "pg_dump -d anakeen >/tmp/anakeen$$.dump"
  sulog  postgres  "dropuser anakeen" 
  sulog  postgres  "dropdb anakeen"

  log "Unregister DB anakeen for automatic dump"
  mv /etc/ankpsql-tools/base-list /etc/ankpsql-tools/base-list.old
  cat /etc/ankpsql-tools/base-list.old | grep -v "^anakeen$" > /etc/ankpsql-tools/base-list
  rm -f /etc/ankpsql-tools/base-list.old
fi

exit 0
