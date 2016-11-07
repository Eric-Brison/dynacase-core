appList = $(pubdir)/$(appname)/app/TST_ACCESS \
	$(pubdir)/$(appname)/app/TST_PARAMETER_MANAGER \
	$(pubdir)/$(appname)/app/INHERIT_ACL_A \
	$(pubdir)/$(appname)/app/INHERIT_ACL_B \
	$(pubdir)/$(appname)/app/TST_OPENACCESS

$(pubdir)/$(appname)/app: $(pubdir)/$(appname)
	mkdir -p $@

$(pubdir)/$(appname)/app/TST_ACCESS: TST_ACCESS $(pubdir)/$(appname)/app
	cp -pR $< $@

$(pubdir)/$(appname)/app/TST_PARAMETER_MANAGER: TST_PARAMETER_MANAGER $(pubdir)/$(appname)/app
	cp -pR $< $@

$(pubdir)/$(appname)/app/INHERIT_ACL_A: INHERIT_ACL_A $(pubdir)/$(appname)/app
	cp -pR $< $@

$(pubdir)/$(appname)/app/INHERIT_ACL_B: INHERIT_ACL_B $(pubdir)/$(appname)/app
	cp -pR $< $@

$(pubdir)/$(appname)/app/TST_OPENACCESS: TST_OPENACCESS $(pubdir)/$(appname)/app
	cp -pR $< $@

publish: $(appList)
