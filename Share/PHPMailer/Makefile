appname = lib/PHPMailer/
language = lib/PHPMailer/language
export appname

# include $(utildir)/PubRule

$(pubdir)/$(appname):
	mkdir -p "$@"
	cp -p class.*.php "$@/"
	cp -p LICENSE "$@/"

$(pubdir)/$(appname)/language: $(pubdir)/$(appname)
	mkdir -p "$@"
	tar -C language -cf - . | tar -C "$@" -xf -

publish: $(pubdir)/$(appname) $(pubdir)/$(appname)/language
