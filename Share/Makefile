# ============================================
# $Id: Makefile,v 1.2 2003/03/06 18:03:54 eric Exp $
# ============================================




include $(utildir)/PubRule

publish: $(pubdir)/log.sh $(pubdir)/WHAT/Lib.Prefix.php.in

$(pubdir)/log.sh: log.sh
	cp $< $@
$(pubdir)/WHAT/Lib.Prefix.php.in: Lib.Prefix.php.in
	cp $< $@

DISTFILES += log.sh Lib.Prefix.php.in
