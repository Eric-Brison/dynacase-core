
// See thread http://www.extjs.com/forum/showthread.php?t=69465

Ext.loadedLibrary = {};
Ext.loader = function(config) {
    Ext.apply(this, config);
    var scope = this.scope, callback = (typeof this.callback == 'function' ? this.callback : function() { });
    this.callback = (typeof scope == "undefined" || null == scope ? callback : callback.createDelegate(scope));

    if (this.js && this.js.constructor != Array) {
        this.js = [this.js];
    }
    this.registerLibrary(this.js);
    this.load(this.callback);
};
Ext.loader.prototype = {
    registerLibrary: function(jCollection) {
        if (Ext.isArray(jCollection)) {
            Ext.each(jCollection, function(src) {
                if (!this.isUrlExit(src)) {
                    this.unregisterLoadedUrl(src);
                }
            }, this);
        }

    },
    isUrlExit: function(url) {
        if (Ext.loadedLibrary[url] == undefined) {
            return false;
        }
        return true;
    },
    until: function(o) {
        if (o.test() === true) {
            o.callback();
        }
        else {
            window.setTimeout(function() {
                this.until(o);
            } .createDelegate(this), o.delay || 50);
        }
    },
    isUrlLoaded: function(url) {
        return Ext.loadedLibrary[url] === true;
    },
    unregisterLoadedUrl: function(url) {
        Ext.loadedLibrary[url] = false;
    },
    registerLoadedUrl: function(url) {
        Ext.loadedLibrary[url] = true;
    },
    load: function(cb) {
        var scriptsToLoad = this.js.length;
        var jCollection = this.js;
        if (0 === scriptsToLoad) {
            return cb();
        }

        Ext.each(jCollection, function(js) {
            if (this.isUrlLoaded(js)) {
                scriptsToLoad--;
                return false;
            } else {
                this.getJavaScript({
                    url: js,
                    success: function() {
                        scriptsToLoad--;
                        this.registerLoadedUrl(js);
                    } .createDelegate(this),
                    error: function(msg) {
                        scriptsToLoad--;
                        this.unregisterLoadedUrl(js);
                        throw "Communication";
                        //if (typeof this.data.error == "function") this.data.error(js, msg);
                    } .createDelegate(this)
                });
            }


        }, this);
        this.until({
            test: function() {
                return scriptsToLoad === 0;
            },
            delay: 50,
            callback: function() {
                cb();
            }
        });

    },
    getJavaScript: function(data) {
        var me = this;
        return Ext.Ajax.request({
            method: "GET",
            url: data.url,
            success: function(response) {
                me.addScript(response.responseText);
                data.success();
            },
            failure: function(xml, status, e) {
                if (xml && xml.responseText)
                    data.error(xml.responseText);
                else
                    data.error(url + '\n' + e.message);
            }
        });

    },
    getHtmlHead: function() {
        return document.getElementsByTagName("head")[0] || document.documentElement;
    },
    addScript: function(data) {
        var script = document.createElement("script");
        script.type = "text/javascript";
        if (Ext.isIE) {
            script.text = data;
        }
        else {
            script.appendChild(document.createTextNode(data));
        }

        var head = this.getHtmlHead();
        head.appendChild(script);
    }
};
Ext.ensure = function(config) {
    new Ext.loader(config);
};