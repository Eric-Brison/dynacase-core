
include $(utildir)/PubRule

pages_js = $(patsubst %.js,$(libdir)/examples/%.js,$(wildcard *.js))
pages_css = $(patsubst %.css,$(libdir)/examples/%.css,$(wildcard *.css))
pages_html = $(patsubst %.html,$(libdir)/examples/%.html,$(wildcard *.html))

$(libdir)/examples:
	mkdir $@

$(libdir)/examples/%.js: %.js $(libdir)/examples
	cp $< $@

$(libdir)/examples/%.css: %.css $(libdir)/examples
	cp $< $@

$(libdir)/examples/%.html: %.html $(libdir)/examples
	cp $< $@

publish: $(libdir)/examples $(pages_js) $(pages_css) $(pages_html) 