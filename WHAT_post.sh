#!/bin/sh


. /var/post-install/share/pi-functions.sh

#------------------------------
#post installation
#------------------------------
if [ "$1" = 1 ] ; then

  log "Create anakeen database"
  sulog postgres "createdb anakeen"
  
  log "Create anakeen database user"
  sulog postgres "createuser -d -a anakeen" 
 
  log "Register DB anakeen for automatic dump"
  echo "anakeen" >> /etc/ankpsql-tools/base-list



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

  #test if user and database creation are good
  sulog postgres "echo '\q' | psql anakeen anakeen"

  # Init the database with all App
  pushd /home/httpd/what >/dev/null
  echo '<?
  include_once("Class.Application.php");
  $app=new Application();
  $Null = "";
  $app->Set("CORE",$Null);
  $app->Set("USERS",$Null);
  $app->Set("APPMNG",$Null);
  $app->Set("AUTHENT",$Null);
  $app->Set("ACCESS",$Null);
  ?>' | /usr/bin/php -q 2>&1 >>/dev/null
  popd >/dev/null

  # Restart Apache if running
  run=`/etc/rc.d/init.d/httpd status |grep pid |wc -l`
  if [ $run = 1 ] ; then
     /etc/rc.d/init.d/httpd restart
  fi
fi


#------------------------------
#post uninstallation
#------------------------------
if [ "$1" = 0 ] ; then

  set -e
  # drop anakeen database and user
  log "The anakeen database will be dropped, we save a dump in /tmp/anakeen.dump"
  sulog  postgres  "pg_dump -d anakeen >/tmp/anakeen.dump"
  sulog  postgres  "dropuser anakeen" 
  sulog  postgres  "dropdb anakeen"

  log "Unregister DB anakeen for automatic dump"
  mv /etc/ankpsql-tools/base-list /etc/ankpsql-tools/base-list.old
  cat /etc/ankpsql-tools/base-list.old | grep -v "^anakeen$" > /etc/ankpsql-tools/base-list
  rm -f /etc/ankpsql-tools/base-list.old
fi

exit 0
