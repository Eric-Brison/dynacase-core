<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: Class.Lang.php,v 1.2 2003/08/18 15:46:42 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Class.Lang.php,v 1.2 2003/08/18 15:46:42 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Class/Layout/Class.Lang.php,v $
// ---------------------------------------------------------------
// $Log: Class.Lang.php,v $
// Revision 1.2  2003/08/18 15:46:42  eric
// phpdoc
//
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.9  2001/10/17 09:08:49  eric
// mise en place de i18n via gettext
//
// Revision 1.8  2001/02/26 15:05:31  yannick
// Optimization : Add a buffer
//
// Revision 1.7  2000/11/02 19:04:51  marc
// Simplification, affichage des code si pas de message.
//
// Revision 1.6  2000/10/23 14:23:55  marc
// Another release
//
// Revision 1.5  2000/10/19 16:34:45  yannick
// Pour Marc
//
// Revision 1.4  2000/10/19 10:31:04  marc
// Langues: suppression ancien catalogue lors du reload de l'app
//
// Revision 1.3  2000/10/19 10:15:13  marc
// Finalisation de l'internationalisation
//
// Revision 1.2  2000/10/18 20:15:17  marc
// Internationalisation
//
// Revision 1.1  2000/10/18 19:55:08  marc
// Internationalisation
//
//
// ---------------------------------------------------------------
include_once ('Class.DbObj.php');
include_once ('Class.QueryDb.php');
include_once ('Class.Log.php');

class Lang extends DbObj
{
    
    var $fmttxt = "NO TEXT DEFINED";
    var $fields = array(
        "idapp",
        "lang",
        "code",
        "fmt"
    );
    var $id_fields = array(
        "idapp",
        "lang",
        "code"
    );
    var $dbtable = "lang";
    
    var $sqlcreate = '
create table lang (idapp int not null,
lang  varchar(10) not null,
code  varchar(60) not null,
fmt   varchar(200) not null );
create index lang_idx1 on lang(idapp, lang, code);
';
    
    var $buffer = array();
    
    function Exist($idapp, $code, $lang)
    {
        $query = new QueryDb($this->dbaccess, "Lang");
        $query->basic_elem->sup_where = array(
            "idapp={$idapp}",
            "lang='{$lang}'",
            "code='{$code}'"
        );
        $query->Query();
        
        if ($query->nb == 0) return FALSE;
        return TRUE;
    }
    
    function SetEnv($id, $lang, $deflang)
    {
        $this->idapp = $id;
        $this->lang = $lang;
        $this->deflang = $lang;
        $query = new QueryDb($this->dbaccess, "Lang");
        $query->basic_elem->sup_where = array(
            "idapp={$id}",
            "lang='{$deflang}'"
        );
        $lista = $query->Query();
        $query->basic_elem->sup_where = array(
            "idapp={$id}",
            "lang='{$lang}'"
        );
        $listb = $query->Query();
        $list = array_merge($lista, $listb);
        reset($list);
        while (list($k, $v) = each($list)) {
            $this->buffer[$v->code] = $v->fmt;
        }
    }
    
    function Store($idapp, $code, $lang, $fmt)
    {
        $this->idapp = $idapp;
        $this->lang = $lang;
        $this->code = $code;
        $this->fmt = $fmt;
        if ($this->exist($idapp, $code, $lang)) {
            $this->Modify();
        } else {
            $this->Add();
        }
    }
    
    function Get($idapp, $lang, $code, $args = NULL)
    {
        
        $query = new QueryDb($this->dbaccess, "Lang");
        $query->basic_elem->sup_where = array(
            "idapp={$idapp}",
            "lang='{$lang}'",
            "code='{$code}'"
        );
        $list = $query->Query();
        
        if ($query->nb <= 0) {
            $this->fmttxt = "**{$code}**";
            return FALSE;
        }
        
        $uf = $list[0]->fmt;
        if ($args == NULL) {
            $this->fmttxt = $uf;
        } else {
            $nfmt = preg_replace("/%([0-9]+)%/", "{\$args[\\1]}", $uf);
            eval("\$out = \"$nfmt\";");
            $this->fmttxt = $out;
        }
        return TRUE;
    }
    function GetText($code, $args = NULL)
    {
        if (!isset($this->buffer[$code])) return FALSE;
        $uf = $this->buffer[$code];
        if ($args == NULL) {
            $this->fmttxt = $uf;
        } else {
            $nfmt = preg_replace("/%([0-9]+)%/", "{\$args[\\1]}", $uf);
            eval("\$out = \"$nfmt\";");
            $this->fmttxt = $out;
        }
        return TRUE;
    }
    
    function deletecatalog($idapp)
    {
        $query = new QueryDb($this->dbaccess, "Lang");
        $query->basic_elem->sup_where = array(
            "idapp={$idapp}"
        );
        $list = $query->Query();
        if ($query->nb > 0) {
            while (list($k, $v) = each($list)) $v->delete();
        }
    }
} // End Class
