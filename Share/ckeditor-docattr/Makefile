
appname = lib/ckeditor/3/plugins/docattr
plug4 = lib/ckeditor/4/plugins
export appname

include $(utildir)/PubRule

DISTFILES +=  $(SUBDIR) $(wildcard *.js) 
$(pubdir)/$(appname):
	mkdir -p $@

$(pubdir)/$(appname)/plugin.js : plugin.js $(pubdir)/$(appname)
	(cd $(pubdir)/$(appname);ln -sf  Layout/$< $<)

$(pubdir)/$(plug4):
	mkdir -p $@

$(pubdir)/$(plug4)/docattr : $(pubdir)/$(plug4)
	(cd $(pubdir)/$(plug4) ; ln -sf  ../../../ckeditor/3/plugins/docattr .)


publish:$(pubdir)/$(appname)/plugin.js $(pubdir)/$(plug4)/docattr
