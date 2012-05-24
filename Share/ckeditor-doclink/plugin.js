CKEDITOR.plugins.add('doclink',
    {

        init:function (editor) {
            var linkURL = editor.config.doclink.URL || "?app=FDL&action=HTMLEDITSELECTDOC&fam=IUSER";
            var iconPath = this.path + 'Images/link.png';
            editor.addCommand('doclinkDialog', new CKEDITOR.dialogCommand('doclinkDialog'));
            editor.ui.addButton('doclink',
                {
                    label:editor.lang.doclink.label,
                    command:'doclinkDialog',
                    icon:iconPath
                });
            if (editor.contextMenu) {
                editor.addMenuGroup('myGroup');
                editor.addMenuItem('doclinkItem',
                    {
                        label:editor.lang.doclink.menuLabel,
                        icon:iconPath,
                        command:'doclinkDialog',
                        group:'myGroup'
                    });
                editor.contextMenu.addListener(function (element) {
                    if (element) {
                        element = element.getAscendant('a', true);
                    }
                    if (element && !element.isReadOnly() && element.data('initid')) {
                        return { doclinkItem:CKEDITOR.TRISTATE_OFF };
                    }
                    return null;
                });
            }
            CKEDITOR.dialog.add('doclinkDialog', function (editor) {
                return {
                    title:editor.lang.doclink.label,
                    minWidth:400,
                    minHeight:200,
                    resizable:false,

                    contents:[
                        {
                            id:'tab1',
                            label:'Iframe link',
                            elements:[
                                {
                                    id:'iframe',
                                    type:'html',
                                    html:'<iframe style="width:400px; height:200px;" src="' + linkURL + '"></iframe>'
                                }
                            ]
                        }
                    ],
                    onLoad:function () {
                        var element = editor.getSelection().getStartElement();
                    },
                    onShow:function () {
                        var element = editor.getSelection().getStartElement();
                        var currentDcpDocument = {};
                        this.linkWindow = this.getElement().getElementsByTag("iframe").getItem(0).$.contentWindow;

                        if (element) {
                            element = element.getAscendant('a', true);
                        }
                        if (element && !element.isReadOnly() && element.data('initid')) {
                            if (this.linkWindow) {
                                currentDcpDocument = {
                                    initid : element.data('initid'),
                                    title : element.getText(),
                                    docrev : element.data('docrev')
                                }
                                this.linkWindow.currentDcpDocument = currentDcpDocument;
                                if (this.linkWindow.updateIHM){
                                    this.linkWindow.updateIHM();
                                }
                            }
                            this.insertMode = false;
                        }
                        else {
                            element = editor.document.createElement('a');
                            element.setAttribute("href", "#");
                            this.insertMode = true;
                        }
                        this.link = element;
                    },
                    onOk:function () {
                        this.linkWindow = this.getElement().getElementsByTag("iframe").getItem(0).$.contentWindow;
                        if (this.linkWindow.currentDcpDocument.title && this.linkWindow.currentDcpDocument.initid) {
                            this.link.data("initid", this.linkWindow.currentDcpDocument.initid);
                            this.link.data("docrev", this.linkWindow.currentDcpDocument.docrev || "latest");
                            this.link.setText(this.linkWindow.currentDcpDocument.title);
                            if (this.insertMode) {
                                editor.insertElement(this.link);
                            }
                        }
                    }
                };
            });
        }
    });

CKEDITOR.plugins.setLang('doclink', 'en',
    {
        doclink:{
            label:'Link to documents',
            menuLabel:'Edit link'
        }
    }
);
CKEDITOR.plugins.setLang('doclink', 'fr',
    {
        doclink:{
            label:"Lien vers des documents",
            menuLabel:'Éditer le lien'
        }
    }
);