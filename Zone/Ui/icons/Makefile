

pages_png = $(patsubst %.png,$(pubdir)/lib/ui/icon/%.png,$(wildcard *.png))


$(pubdir)/lib/ui/icon: 
	mkdir -p $@

$(pubdir)/lib/ui/icon/%.png: %.png $(pubdir)/lib/ui/icon
	cp $< $@

publish: $(pages_png)
