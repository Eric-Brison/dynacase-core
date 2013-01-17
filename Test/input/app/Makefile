appList = $(pubdir)/$(appname)/app/TST_ACCESS $(pubdir)/$(appname)/app/TST_PARAMETER_MANAGER

$(pubdir)/$(appname)/app: $(pubdir)/$(appname)
	mkdir -p $@

$(pubdir)/$(appname)/app/TST_ACCESS: TST_ACCESS $(pubdir)/$(appname)/app
	cp -pR $< $@

$(pubdir)/$(appname)/app/TST_PARAMETER_MANAGER: TST_PARAMETER_MANAGER $(pubdir)/$(appname)/app
	cp -pR $< $@

publish: $(appList)
