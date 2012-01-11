appList = $(pubdir)/$(appname)/app/TST_ACCESS

$(pubdir)/$(appname)/app: $(pubdir)/$(appname)
	mkdir -p $@

$(pubdir)/$(appname)/app/TST_ACCESS: TST_ACCESS $(pubdir)/$(appname)/app
	cp -pR $< $@

publish: $(appList)
