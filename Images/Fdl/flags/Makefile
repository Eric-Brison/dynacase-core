# ============================================
# $Id: Makefile,v 1.3 2004/02/24 08:39:16 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = FDL/Images/flags

export appname

pages_png = $(patsubst %.png,$(pubdir)/$(appname)/%.png,$(wildcard *.png))

$(pubdir)/$(appname)/%.png: %.png $(pubdir)/$(appname)
	cp $< $@

$(pubdir)/$(appname) : 
	mkdir -p $@

publish: $(pubdir)/$(appname) $(pages_png)

