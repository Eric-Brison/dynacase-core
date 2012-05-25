CKEDITOR_BASEPATH = 'ckeditor/';

window.htmlText = {};

window.htmlText.toolbars = {
    toolbar_Full:[
        { name:'document', items:[ 'Source', '-', 'quicksave', 'NewPage', 'DocProps', 'Preview', 'Print', '-', 'Templates' ] },
        { name:'clipboard', items:[ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
        { name:'editing', items:[ 'Find', 'Replace', '-', 'SelectAll', '-' ] },
        { name:'forms', items:[ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton',
            'HiddenField' ] },
        '/',
        { name:'basicstyles', items:[ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
        { name:'paragraph', items:[ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv',
            '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl' ] },
        { name:'links', items:[ 'Link', 'Unlink' ] },
        { name:'insert', items:[ 'Image', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe' ] },
        '/',
        { name:'styles', items:[ 'Styles', 'Format', 'Font', 'FontSize' ] },
        { name:'colors', items:[ 'TextColor', 'BGColor' ] },
        { name:'tools', items:[ 'Maximize', 'ShowBlocks', '-', 'About' ] }
    ],
    toolbar_Default:[
        { name:'document', items:[ 'quicksave', 'Source'] },
        { name:'clipboard', items:[ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
        { name:'editing', items:[ 'Find', 'Replace', '-', 'SelectAll' ] },
        { name:'basicstyles', items:[ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
        { name:'paragraph', items:[ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv',
            '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl' ] },
        { name:'links', items:[ 'Link', 'Unlink' ] },
        { name:'insert', items:[ 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'Iframe' ] },
        { name:'styles', items:[ 'Styles', 'Format', 'Font', 'FontSize' ] },
        { name:'colors', items:[ 'TextColor', 'BGColor' ] },
        { name:'tools', items:[ 'Maximize', 'ShowBlocks', '-', 'About' ] }
    ],
    toolbar_Simple:[
        { name:'document', items:[ 'quicksave'] },
        { name:'basicstyles', items:[ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat' ] },
        { name:'paragraph', items:[ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-',
            '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
        { name:'links', items:[ 'Link', 'Unlink' ] },
        { name:'insert', items:[ 'Image', 'Table', 'SpecialChar' ] },
        { name:'styles', items:[ 'Format', 'FontSize' ] },
        { name:'colors', items:[ 'TextColor', 'BGColor' ] },
        { name:'tools', items:[ 'Maximize', 'Source', '-', 'About' ] }
    ],
    toolbar_Basic:[
        { name:'links', items:['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', '-', 'quicksave', 'About'] }
    ]
};

window.htmlText.defaultOption = function (config) {
    var element, property, i, length, modedoclink = false;

    if (config.doclink && (config.doclink.famId || config.doclink.URL)) {
        this.extraPlugins = this.extraPlugins ? this.extraPlugins + ",doclink" : 'doclink';
        if (!config.doclink.URL) {
            config.doclink.URL = "?app=FDL&action=HTMLEDITSELECTDOC&fam=" + config.doclink.famId;
            if (config.doclink.docrev) {
                config.doclink.URL += "&docrev=" + config.doclink.docrev;
            }
            if (config.doclink.filter) {
                config.doclink.URL += "&filter=" + config.doclink.filter;
            }
        } else {
            config.doclink.URL = config.doclink.URL;
        }
        modedoclink = true;
    }

    if (config.addPlugins && config.addPlugins.length > 0) {
        this.extraPlugins = this.extraPlugins ? this.extraPlugins + "," + config.addPlugins.join(",") : config.addPlugins.join(",");
        for (property in window.htmlText.toolbars) {
            this[property] = [].concat(window.htmlText.toolbars[property], [
                {name:'extension', items:config.addPlugins}
            ]);
            if (modedoclink) {
                for (element in this[property]) {
                    if (this[property][element].name == "links") {
                        this[property][element].items.push("doclink");
                    }
                }
            }
        }
    } else {
        for (property in window.htmlText.toolbars) {
            this[property] = window.htmlText.toolbars[property];
            if (modedoclink) {
                for (element in this[property]) {
                    if (this[property][element].name == "links") {
                        this[property][element].items.push("doclink");
                    }
                }
            }
        }
    }

    for (property in config) {
        if (config.hasOwnProperty(property)) {
            this[property] = config[property];
        }
    }
};

window.htmlText.defaultOption.prototype = {
    language:'[CORE_LANG]'.substring(0, 2),
    toolbar:'Simple',
    height:'150px',
    customConfig:'',
    resize_enabled:false,
    fullPage:false,
    font_names:'serif;sans-serif;cursive;fantasy;monospace',
    removePlugins:'elementspath',
    extraPlugins:'quicksave',
    filebrowserImageBrowseUrl:'../../?sole=Y&app=FDL&action=CKIMAGE',
    filebrowserImageUploadUrl:'../../?sole=Y&app=FDL&action=CKUPLOAD',
    blockedKeystrokes:[
        CKEDITOR.CTRL + 66 /*B*/,
        CKEDITOR.CTRL + 73 /*I*/,
        CKEDITOR.CTRL + 85 /*U*/,
        CKEDITOR.CTRL + 83 /*S*/
    ],
    keystrokes:[
        [ CKEDITOR.CTRL + 83 /*S*/, 'quicksave'],
        [ CKEDITOR.CTRL + 90 /*Z*/, 'undo' ],
        [ CKEDITOR.CTRL + 89 /*Y*/, 'redo' ],
        [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 90 /*Z*/, 'redo' ],
        [ CKEDITOR.CTRL + 66 /*B*/, 'bold' ],
        [ CKEDITOR.CTRL + 73 /*I*/, 'italic' ],
        [ CKEDITOR.CTRL + 85 /*U*/, 'underline' ]
    ]
};

window.htmlText.initEditor = function initEditor(htmlId, config) {
    config = config || {};
    CKEDITOR.replace(htmlId, new window.htmlText.defaultOption(config));
};

window.htmlText.deleteContent = function deleteContent(htmlTextId) {
    if (CKEDITOR.instances[htmlTextId] && CKEDITOR.instances[htmlTextId].setData) {
        CKEDITOR.instances[htmlTextId].setData("");
    } else {
        throw "unable to delete content : " + htmlTextId + " doesn't exist";
    }
};

window.htmlText.synchronizeWithTextArea = function synchronizeWithTextArea(htmlTextId) {
    var currentInstanceName;
    if (htmlTextId) {
        if (CKEDITOR.instances && CKEDITOR.instances[htmlTextId] && CKEDITOR.instances[htmlTextId].updateElement) CKEDITOR.instances[htmlTextId].updateElement();
    } else {
        for (currentInstanceName in CKEDITOR.instances) {
            if (CKEDITOR.instances.hasOwnProperty(currentInstanceName)) {
                if (CKEDITOR.instances[currentInstanceName].updateElement) CKEDITOR.instances[currentInstanceName].updateElement();
            }
        }
    }
};

window.htmlText.setValue = function setValue(htmlTextId, value) {
    if (CKEDITOR.instances && CKEDITOR.instances[htmlTextId] && CKEDITOR.instances[htmlTextId].setData) {
        CKEDITOR.instances[htmlTextId].setData(value);
    } else {
        throw new Exception("unable to set content : " + htmlTextId + " doesn't exist");
    }
}

window.htmlText.getValue = function getValue(htmlTextId) {
    if (CKEDITOR.instances && CKEDITOR.instances[htmlTextId] && CKEDITOR.instances[htmlTextId].getData) {
        return CKEDITOR.instances[htmlTextId].getData();
    } else {
        throw "unable to get content : " + htmlTextId + " doesn't exist";
    }
}

window.htmlText.deactivateEditor = function deactivateEditor(htmlTextId, noUpdate) {
    var currentConfig = CKEDITOR.instances[htmlTextId].config;
    CKEDITOR.instances[htmlTextId].destroy(noUpdate);
    return currentConfig;
}
