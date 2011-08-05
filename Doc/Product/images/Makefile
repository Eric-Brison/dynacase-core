# ============================================
# $Id: Makefile,v 1.1 2002/08/19 12:15:34 eric Exp $
# ============================================



pages_gif = $(patsubst %.gif,$(pubdoc)/images/%.gif,$(wildcard *.gif))
pages_png = $(patsubst %.png,$(pubdoc)/images/%.png,$(wildcard *.png))
pages_jpg = $(patsubst %.jpg,$(pubdoc)/images/%.jpg,$(wildcard *.jpg))
images = $(pages_gif) $(pages_png) $(pages_jpg)

$(pubdoc)/images:
	mkdir -p $@

$(pubdoc)/images/%.png : %.png $(pubdoc)/images $(pubdoc)/images
	cp $< $@
$(pubdoc)/images/%.gif : %.gif $(pubdoc)/images $(pubdoc)/images
	cp $< $@
$(pubdoc)/images/%.jpg : %.jpg $(pubdoc)/images $(pubdoc)/images
	cp $< $@


# don't use default rule for images
override pages =
publish : $(images)

include $(utildir)/PubRule


