CKEDITOR.plugins.add('docattr', {
    requires:[ 'richcombo' ],

    init:function (editor) {
        var config = editor.config;

        editor.ui.addRichCombo('docattr',
            {
                label:editor.lang.docattr.label,
                title:editor.lang.docattr.title,
                multiSelect:false,

                panel:{
                    css:[ CKEDITOR.getUrl(editor.skinPath + 'editor.css') ].concat(config.contentsCss)
                },

                init:function () {
                    var i, length;

                    if (window.FCK_DocAttr && window.FCK_DocAttr.split(';').length) {
                        this.startGroup(editor.lang.docattr.group);
                        var aSizes = window.FCK_DocAttr.split(';');
                        for (i = 0, length =  aSizes.length; i < length; i++) {
                            var aSizeParts = aSizes[i].split('|');
                            if (aSizeParts[0]) this.add(aSizeParts[0], aSizeParts[1] + '<pre> ' + aSizeParts[0].replace('<', '&lt;').replace('>', '&gt;') + '</pre>', aSizeParts[1]);
                        }
                    }else {
                        this.startGroup(editor.lang.docattr.nogroup);
                    }
                },
                onClick:function (value) {
                    editor.focus();
                    editor.fire('saveSnapshot');
                    editor.insertHtml(value);
                    editor.fire('saveSnapshot');
                }
            });
    }
});
;

CKEDITOR.plugins.setLang('docattr', 'en',
    {
        docattr:{
            title:'Key',
            label:'Key',
            group:"Template Keys",
            nogroup : "No keys : you can reload the document to try to add new key"
        }
    }
);
CKEDITOR.plugins.setLang('docattr', 'fr',
    {
        docattr:{
            title:'Clefs',
            label:"Clefs",
            group:"Clef de template",
            nogroup : "Pas de clefs : essayez de recharger le document"
        }
    }
);