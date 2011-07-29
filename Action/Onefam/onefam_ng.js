/**
 * 
 */

function create_url(type, hroot, removeHref, target) {
    for ( var i = 0; i < type.length; i++) {
        var relhref = type[i].getAttribute("hrefDoc");
        var relid = type[i].getAttribute("documentId");
        type[i].setAttribute('onclick', 'vselect(this);window.parent.MultiDocument.newDoc(\''
                + relid + '\',"' + hroot + relhref + '");return false;');
        // WORKS ON IE ... type[i].onclick =
        // function(){window.parent.parent.MultiDocument.newDoc(relid, hroot +
        // relhref);return false;};
        if (removeHref == true) {
            type[i].removeAttribute('href');
        }
        if (target == true) {
            type[i].removeAttribute('target');
        }
    }
}

function multidoc(documentList) {
    var d = new Date();
    var relhref;
    var relid;
    var i=0;
    
    document.getElementById('finfo').style.display = "block";
    redisplaywsdiv();
    var hroot = window.location.href.substr(0, window.location.href.indexOf('?'));

    // DOCUMENTS LINK
    var relations = $(documentList).find('div[view="multidoc"]');
    if (relations.length==0) relations = $(documentList).find('tr[view="multidoc"]');
    create_url(relations, hroot, false, false);

    // EDIT IMAGE LINKS
    var editions = $(documentList).find('.aicon');
    create_url(editions, hroot, false, false);

    editions.click(function(event) {
        event.stopPropagation();
    });

    // CLASS LINKS UNDER DOCUMENT NAME
    editions = $(documentList).find('.relation');
    for (  i = 0; i < editions.length; i++) {
         relhref = editions[i].getAttribute("href");
         relid = editions[i].getAttribute("documentId");
        editions[i].setAttribute('onclick',
                'window.parent.MultiDocument.newDoc(\'' + relid + '\',"'
                        + hroot + relhref + '");return false;');
        // //WORKS ON IE ... editions[i].onclick =
        // function(){window.parent.parent.MultiDocument.newDoc(relid, hroot +
        // relhref);return false;};
        editions[i].removeAttribute('href');
        editions[i].removeAttribute('target');
    }

    editions.click(function(event) {
        event.stopPropagation();
    });

    // MENU LINK CREATION
    var menus = $(documentList).find('#newmenu > a');
    for (  i = 0; i < menus.length; i++) {
         relhref = menus[i].getAttribute("hrefDoc");
         relid = "idtmpcreate" + i + d.getTime();
        // WORKS ON IE ... menus[i].onclick =
        // function(){window.parent.parent.MultiDocument.newDoc(relid, hroot +
        // relhref);return false;};
        menus[i].setAttribute('onclick',
                'window.parent.MultiDocument.newDoc(\'' + relid + '\',"'
                        + hroot + relhref + '");return false;');
        menus[i].removeAttribute('href');
    }

    // MENU LINK TOOLS
    var helpmenus = $(documentList).find('#helpmenu > a[hrefDoc]');
    for (  i = 0; i < helpmenus.length; i++) {
         relhref = helpmenus[i].getAttribute("hrefDoc");
         relid = "idtmpmenu" + i + d.getTime();
        // WORKS ON IE ... helpmenus[i].onclick =
        // function(){window.parent.parent.MultiDocument.newDoc(relid, hroot +
        // relhref);return false;};
        helpmenus[i].setAttribute('onclick',
                'window.parent.MultiDocument.newDoc(\'' + relid + '\',"'
                        + hroot + relhref + '");return false;');
        helpmenus[i].removeAttribute('href');
    }
}
