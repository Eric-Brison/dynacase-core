appname = lib/Dcp/Mail
export appname

# include $(utildir)/PubRule

$(pubdir)/$(appname):
	mkdir -p "$@"
	cp -p *.php "$@/"

publish: $(pubdir)/$(appname)
