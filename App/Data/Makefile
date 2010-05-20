
# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule

$(pubdir)/$(appname):
	mkdir -p $@
$(pubdir)/$(appname)/.htaccess: htaccess
	cp $< $@

DISTFILES = Makefile  $(appname).app $(appname)_init.php.in

publish: $(pubdir)/$(appname)/.htaccess