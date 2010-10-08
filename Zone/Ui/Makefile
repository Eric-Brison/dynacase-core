
pages_js = $(patsubst %.js,$(pubdir)/lib/ui/%.js,$(wildcard *.js))
pages_css = $(patsubst %.css,$(pubdir)/lib/ui/%.css,$(wildcard *.css))
pages_php = $(patsubst %.php,$(pubdir)/lib/ui/%.php,$(wildcard *.php))
pages_gif = $(patsubst %.gif,$(pubdir)/lib/ui/%.gif,$(wildcard *.gif))
pages_png = $(patsubst %.png,$(pubdir)/lib/ui/%.png,$(wildcard *.png))


$(pubdir)/lib:
	mkdir -p $@
$(pubdir)/lib/ui: $(pubdir)/lib
	mkdir -p $@
$(pubdir)/lib/ui/icons: 
	make -C icons publish
$(pubdir)/lib/ui/themes: 
	make -C themes publish

$(pubdir)/lib/ui/%.js: %.js $(pubdir)/lib/ui
	cp $< $@
$(pubdir)/lib/ui/%.css: %.css $(pubdir)/lib/ui
	cp $< $@
$(pubdir)/lib/ui/%.php: %.php $(pubdir)/lib/ui
	cp $< $@
$(pubdir)/lib/ui/%.gif: %.gif $(pubdir)/lib/ui
	cp $< $@
$(pubdir)/lib/ui/%.png: %.png $(pubdir)/lib/ui
	cp $< $@

$(pubdir)/lib/ui/freedom-extui-debug.js: ux-javascriptload.js ux-fieldlabeler.js ux-griddragdroproworder.js ux-rowexpander.js ux-toolbartabpanel.js ux-taskbar.js ux-broadcast.js ux-rowactions.js ux-roweditor.js ux-searchfield.js ux-fileuploadfield.js ux-colorfield.js ux-datetime.js ux-xcheckbox.js ux-fieldpanel.js ux-pagingstore.js ux-columnfilter.js ux-columnlock.js uxvismode.js multidom.js mif.js mifmsg.js uxmedia.js override.js fdl-interface-actions.js widget-attribute.js widget-menu-manager.js widget-document.js widget-document-multiview.js widget-multi-document.js widget-window.js default-view-document.js widget-collection.js widget-collection-grid.js widget-collection-tree.js widget-collection-icon.js widget-collection-abstract.js widget-history.js widget-services.js widget-family-tree.js widget-selector.js widget-search.js widget-interface.js widget-requester.js widget-mailer.js widget-family-provider.js widget-family-xplorer.js widget-admin.js $(pubdir)/lib/ui
	cat ux-javascriptload.js ux-fieldlabeler.js ux-griddragdroproworder.js ux-rowexpander.js ux-toolbartabpanel.js ux-taskbar.js ux-broadcast.js ux-rowactions.js ux-roweditor.js ux-searchfield.js ux-fileuploadfield.js ux-colorfield.js ux-datetime.js ux-xcheckbox.js ux-fieldpanel.js ux-pagingstore.js ux-columnfilter.js ux-columnlock.js uxvismode.js multidom.js mif.js mifmsg.js uxmedia.js override.js fdl-interface-actions.js widget-attribute.js widget-menu-manager.js widget-document.js widget-document-multiview.js widget-multi-document.js widget-window.js default-view-document.js widget-collection.js widget-collection-grid.js widget-collection-tree.js widget-collection-icon.js widget-collection-abstract.js widget-history.js widget-services.js widget-family-tree.js widget-selector.js widget-search.js widget-interface.js widget-requester.js widget-mailer.js widget-family-provider.js widget-family-xplorer.js widget-admin.js > $@


$(pubdir)/lib/ui/freedom-extui.js: $(pubdir)/lib/ui/freedom-extui-debug.js
	cp $(pubdir)/lib/ui/freedom-extui-debug.js $@
	if [ -x $(utildir)/jsSquish/bin/js_compactor ]; then \
	  cd $(utildir)/jsSquish/lib; \
	  /bin/rm -f $@; \
	  ../bin/js_compactor --force --src $(pubdir)/lib/ui/freedom-extui-debug.js --dest $@; \
	fi

$(pubdir)/lib/ui/freedom-extui.css: ux-taskbar.css ux-rowactions.css ux-roweditor.css ux-columnfilter.css ux-columnlock.css ux-fileuploadfield.css ux-colorfield.css widget.css widget-services.css widget-notes.css 
	cat ux-taskbar.css ux-rowactions.css ux-roweditor.css ux-columnfilter.css ux-columnlock.css ux-fileuploadfield.css ux-colorfield.css widget.css widget-services.css widget-notes.css > $@


publish: $(pubdir)/lib/ui $(pages_js) $(pages_css) $(pages_php) $(pages_gif) $(pages_png) $(pubdir)/lib/ui/freedom-extui-debug.js $(pubdir)/lib/ui/freedom-extui.js $(pubdir)/lib/ui/freedom-extui.css $(pubdir)/lib/ui/icons $(pubdir)/lib/ui/themes
