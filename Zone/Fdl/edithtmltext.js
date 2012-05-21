CKEDITOR_BASEPATH = 'ckeditor/';

window.htmlText = {};

window.htmlText.defaultOption = function (config) {
    var property;
    for (property in config) {
        if (config.hasOwnProperty(property)) {
            this[property] = config[property];
        }
    }
};

window.htmlText.defaultOption.prototype = {
    language:'[CORE_LANG]'.substring(0, 2),
    customConfig:'',
    resize_enabled:false,
    fullPage:false,
    font_names:'serif;sans-serif;cursive;fantasy;monospace',
    extraPlugins:'quicksave',
    toolbar_Default:[
        { name:'document', items:[ 'quicksave', 'NewPage', 'DocProps', 'Print'] },
        { name:'clipboard', items:[ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
        { name:'editing', items:[ 'Find', 'Replace', '-', 'SelectAll' ] },
        { name:'basicstyles', items:[ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
        { name:'paragraph', items:[ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv',
            '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl' ] },
        { name:'links', items:[ 'Link', 'Unlink', 'Anchor' ] },
        { name:'insert', items:[ 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'Iframe' ] },
        { name:'styles', items:[ 'Styles', 'Format', 'Font', 'FontSize' ] },
        { name:'colors', items:[ 'TextColor', 'BGColor' ] },
        { name:'tools', items:[ 'Maximize', 'ShowBlocks', '-', 'About' ] }
    ],
    toolbar_Simple:[
        { name:'document', items:[ 'quicksave'] },
        { name:'clipboard', items:[ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
        { name:'basicstyles', items:[ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat' ] },
        { name:'paragraph', items:[ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv',
            '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl' ] },
        { name:'links', items:[ 'Link', 'Unlink', 'Anchor' ] },
        { name:'insert', items:[ 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'Iframe' ] },
        { name:'styles', items:[ 'Styles', 'Format', 'Font', 'FontSize' ] },
        { name:'colors', items:[ 'TextColor', 'BGColor' ] },
        { name:'tools', items:[ '-', 'About' ] }
    ],
    filebrowserImageBrowseUrl:'../../../?sole=Y&app=FDL&action=CKIMAGE',
    filebrowserImageUploadUrl:'../../../?sole=Y&app=FDL&action=CKUPLOAD',
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
