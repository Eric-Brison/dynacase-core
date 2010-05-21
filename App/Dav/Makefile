
# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname

include $(utildir)/PubRule

DISTFILES = Makefile  $(appname).app $(appname)_init.php.in

$(pubdir)/$(appname)/DAV_conf.apache: DAV_conf.apache $(pubdir)/$(appname)
	cp $< $@

$(pubdir)/freedav:
	mkdir -p $@

$(pubdir)/freedav/.htaccess: freedav.htaccess $(pubdir)/freedav
	cp $< $@

$(pubdir)/freedav/index.php: $(pubdir)/freedav
	ln -s ../DAV/dav.php $@

$(pubdir)/webdav:
	mkdir -p $@

$(pubdir)/webdav/.htaccess: webdav.htaccess $(pubdir)/webdav
	cp $< $@

$(pubdir)/webdav/index.php: $(pubdir)/webdav
	ln -s ../DAV/dav.php $@

publish: $(pubdir)/$(appname)/DAV_conf.apache $(pubdir)/freedav/.htaccess $(pubdir)/freedav/index.php $(pubdir)/webdav/.htaccess $(pubdir)/webdav/index.php