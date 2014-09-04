CKEDITOR.plugins.add('docattr', {
    requires:[ 'richcombo' ],

    init:function (editor) {
        var config = editor.config;
        var skinPath=editor.skinPath;

        if (! skinPath) {
            if (CKEDITOR.skin) {
                skinPath=CKEDITOR.skin.path();
            }
        }



        editor.ui.addRichCombo('docattr',
            {
                label: editor.lang.docattr.label,
                title:editor.lang.docattr.title,
                multiSelect:false,


                panel:{
                    css:[ CKEDITOR.getUrl(skinPath + 'editor.css') ].concat(config.contentsCss)
                },

                init:function () {
                    var property;

                    if (window.CK_DocAttr) {
                        this.startGroup(editor.lang.docattr.group);
                        for (property in window.CK_DocAttr) {
                            this.add(property, window.CK_DocAttr[property].alabel, window.CK_DocAttr[property].alabel);
                        }
                    }else {
                        this.startGroup(editor.lang.docattr.nogroup);
                    }
                },
                onClick:function (value) {
                    editor.focus();
                    editor.fire('saveSnapshot');
                    editor.insertHtml(window.CK_DocAttr[value].aid);
                    editor.fire('saveSnapshot');
                }
            });
    }
});

CKEDITOR.plugins.setLang('docattr', 'en',
    {
        title:'Key',
        label:'Key',
        group:"Template Keys",
        nogroup : "No keys : you can reload the document to try to add new key",
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
        label:"Clefs",
        title:'Clefs',
        group:"Clef de template",
        nogroup : "Pas de clefs : essayez de recharger le document",
        docattr:{
            title:'Clefs',
            label:"Clefs",
            group:"Clef de template",
            nogroup : "Pas de clefs : essayez de recharger le document"
        }
    }
);