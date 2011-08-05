
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/*
 * Ext.ux.InlineToolbarTabPanel
 * Extension to create tabpanel with an inline toolbar
 * Ext JS forum links :
 * http://www.extjs.com/forum/showthread.php?t=25577
 * http://www.extjs.com/forum/showthread.php?t=65354
*/

Ext.namespace('Ext.ux.InlineToolbarTabPanel');

Ext.ux.InlineToolbarTabPanel = Ext.extend(Ext.TabPanel, {
  inlineToolbar: null
  ,toolbar: null
  ,headerToolbar: false
  ,tabToolbars: false
  ,shadowTabs: false
  
  ,headerBorder: false
  
  // private
  ,onRender : function(ct, position){
    Ext.TabPanel.superclass.onRender.call(this, ct, position);

    if(this.plain){
      var pos = this.tabPosition == 'top' ? 'header' : 'footer';
      this[pos].addClass('x-tab-panel-'+pos+'-plain');
    }

    var st = this[this.stripTarget];

    this.stripWrap = st.createChild({cls:'x-tab-strip-wrap', cn:{
      tag:'ul', cls:'x-tab-strip x-tab-strip-'+this.tabPosition
    }});

    var beforeEl = (this.tabPosition=='bottom' ? this.stripWrap : null);
    st.createChild({cls:'x-tab-strip-spacer'}, beforeEl);
    this.strip = new Ext.Element(this.stripWrap.dom.firstChild);

    // create an empty span with class x-tab-strip-text to force the height of the header element when there's no tabs.
    this.edge = this.strip.createChild({tag:'li', cls:'x-tab-edge', cn: [{tag: 'span', cls: 'x-tab-strip-text', cn: '&#160;'}]});
    this.strip.createChild({cls:'x-clear'});

    this.body.addClass('x-tab-panel-body-'+this.tabPosition);

    if(!this.itemTpl){
      var tt;
      if (this.tabToolbars) {
        tt = new Ext.Template(
          '<li class="{cls}" id="{id}"><a class="x-tab-strip-close" onclick="return false;"></a>',
          '<a class="x-tab-right" href="#" onclick="return false;"><em class="x-tab-left">',
	  '<table cellpadding="1" cellspacing="0" class="x-tab-strip-inner"><tr><td>',
	  '<span class="x-tab-strip-text {iconCls}">{text}</span></td><td>&nbsp;</td>',
	  '<td style="padding-top:1px;"><div id="{tabTB}" style="position:relative;"></div></td></tr></table></em></a></li>'
        );
      } else {
        tt = new Ext.Template(
	  '<li class="{cls}" id="{id}"><a class="x-tab-strip-close"></a>',
          '<a class="x-tab-right" href="#"><em class="x-tab-left">',
          '<span class="x-tab-strip-inner"><span class="x-tab-strip-text {iconCls}">{text}</span></span>',
          '</em></a></li>'
        );
      }
      tt.disableFormats = true;
      tt.compile();
      Ext.TabPanel.prototype.itemTpl = tt;
    }

    this.items.each(this.initTab, this);
  }

  ,afterRender: function() {
    Ext.ux.InlineToolbarTabPanel.superclass.afterRender.call(this);
    if (!Ext.isEmpty(this.toolbar)) {
      this.setToolbar(this.toolbar);
    }
  }

  ,onResize: function() {
    Ext.ux.InlineToolbarTabPanel.superclass.onResize.apply(this, arguments);
    if (Ext.isEmpty(this.inlineToolbar)) return;

    var tbEl = this.inlineToolbar.getEl();
    var tbWidth = tbEl.dom.offsetWidth;
    var w = this.header.dom.offsetWidth - tbWidth - (this.headerToolbar?0:10);
    var h = this.header.getHeight();
    var defaultHeight = 27;

    this.header.setHeight(h < defaultHeight? defaultHeight:h);
    this.tbHeader.setHeight(this.header.dom.offsetHeight);
    this.stripWrap.setHeight(this.header.dom.offsetHeight);
    this.tbWrap.setHeight(this.stripWrap.dom.offsetHeight);
    this.strip.setHeight(this.stripWrap.dom.offsetHeight - 4);
    this.tbContainer.setHeight(this.strip.dom.offsetHeight);

//    this.header.setWidth((Ext.isIE6)?(w-2):(Ext.isIE7 || Ext.isGecko3)?w-4:w);
    this.header.setWidth((Ext.isIE || Ext.isGecko3)?w-4:w);    //(Ext.isIE6)?(w-2);

    this.stripWrap.setWidth(w);
    this.tbHeader.setWidth(tbWidth + (this.headerToolbar?4:10));
    this.inlineToolbar.setWidth(tbWidth);
    this.inlineToolbar.setHeight(this.tbContainer.dom.offsetHeight-1);

    this.tbHeader.alignTo(this.header, 'tr', (Ext.isGecko && !Ext.isGecko3)?[-1,-1]:[0,0]);

    this.delegateUpdates();
  }

  ,getToolbar: function() {
    return this.inlineToolbar;
  }

  ,setToolbar: function(obj) {
    var cls = 'x-tab-panel-header';
    var tbStyle = {style: 'border-width:0px;' +
      (this.headerToolbar? 'padding:0px;background:transparent none;': '')};

    if (this.headerToolbar)
      cls += (this.border? '':
        ' x-tab-panel-noborder x-tab-panel-header-noborder');
    else
      cls += ' x-tab-strip-wrap x-tab-strip-top';

    this.tbHeader = this.header.insertSibling({
      id:"tbHeader",
      style: 'position:absolute;' + (Ext.isIE? 'width:0px;' : '')
    });

    this.tbWrap = this.tbHeader.createChild({
      id:'tbWrap'
      ,style: this.headerBorder ? 'border-left:0px none;border-left-width:0px;':'border:0px none;'
      ,cls:cls
    });

    this.tbContainer = this.tbWrap.createChild({
      id:'tbContainer',
      style: 'border-left:1px solid #8DB2E3;border-top:0px none;'
      , tag: this.headerToolbar? 'ul': 'div'
      , cls: this.headerToolbar? 'x-tab-strip-top': 'x-tab-right x-tab-panel-header'
    });

    this.headerBorder ? this.header.setStyle('border-right', '0px none'):this.header.setStyle('border', '0px none');

    Ext.apply(this.toolbar, tbStyle);
    this.inlineToolbar = new Ext.Toolbar(this.toolbar);

    if (!this.headerToolbar) {
      this.inlineToolbar.removeClass('x-toolbar');
      this.inlineToolbar.addClass('x-tab-strip-inner');
    }
    
    this.inlineToolbar.render(this.tbContainer);

    if (this.toolbar != obj) {
      this.onResize(this.getSize().width);
      this.toolbar = obj;
    }
  }

  ,createScrollers: function(){
    var h = this.strip.dom.offsetHeight;

    // left
    var sl = this.header.insertFirst({
      cls:'x-tab-scroller-left'
    });
    sl.setHeight(h);
    sl.addClassOnOver('x-tab-scroller-left-over');
    this.leftRepeater = new Ext.util.ClickRepeater(sl, {
      interval : this.scrollRepeatInterval,
      handler: this.onScrollLeft,
      scope: this
    });
    this.scrollLeft = sl;

    // right
    var sr = this.header.insertFirst({
        cls:'x-tab-scroller-right'
    });
    sr.setHeight(h);
    sr.addClassOnOver('x-tab-scroller-right-over');
    this.rightRepeater = new Ext.util.ClickRepeater(sr, {
      interval : this.scrollRepeatInterval,
      handler: this.onScrollRight,
      scope: this
    });
    this.scrollRight = sr;
  }
});

Ext.reg('toolbartabpanel', Ext.ux.InlineToolbarTabPanel);