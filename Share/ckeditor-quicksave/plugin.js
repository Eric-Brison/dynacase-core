CKEDITOR.plugins.add('quicksave', {
    init:function (editor) {

        editor.addCommand('quicksave',
            {
                exec:function (editor) {
                    window.quicksave();
                }
            });
        editor.ui.addButton('quicksave',
            {
                label:editor.lang.quicksave.toolbar,
                command:'quicksave',
                icon:this.path + 'Images/floppy.png'
            });
    }
});

CKEDITOR.plugins.setLang('quicksave', 'en',
    {
        title : 'Quick save',
        toolbar:'Quick save'
    }
);
CKEDITOR.plugins.setLang('quicksave', 'fr',
    {
        title : 'Sauvegarde rapide',
        toolbar:'Sauvegarde rapide'
    }
);