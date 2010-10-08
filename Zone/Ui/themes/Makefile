

pages_themes = $(patsubst %.theme,$(pubdir)/lib/ui/themes/%,$(wildcard *.theme))


$(pubdir)/lib/ui/themes: 
	mkdir -p $@

$(pubdir)/lib/ui/themes/%: %.theme $(pubdir)/lib/ui/themes
	cp -r $< $@

publish: $(pages_themes)
