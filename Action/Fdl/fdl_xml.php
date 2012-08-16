<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * XML Utilities for freedom
 *
 * @author Anakeen
 * @version $Id: fdl_xml.php,v 1.3 2005/09/22 16:34:44 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
//============================== XML =======================

/**
 * construct document from xml
 * @param string $dbaccess database specification
 * @param string $xml the xml structure
 * @param string $famid identificator of family (need correspond to xml)
 * @param bool $encoded true is xml is encoded
 * @return Doc the document (view like a temporary document);
 */
function fromxml($dbaccess, $xml, $famid, $encoded = false)
{
    if ($encoded) $xml = base64_decode(($xml));
    $xml2 = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"yes\" ?>";
    $xml2.= str_replace("<BR>", "<BR/>", $xml);
    $idoc = createDoc($dbaccess, $famid, false); ///new doc
    $idoc->doctype = 'T';
    
    _fromxml($xml2, $idoc);
    $idoc->Add();
    return $idoc;
}

function _fromxml($xml, &$idoc)
{
    global $action;
    $fp = $xml;
    global $value; //used to stock value of one attribut (is string type)
    global $tabvalues; //used to stock document attribute values
    global $is_array;
    global $i;
    global $depth_index;
    global $title; //
    global $attr_idoc; // used for idoc attribute (idoc and idoclist)
    global $list; // used for list attribute (textlist and idoclist)
    global $tempidoc; //is need to acces to $idoc in startElement()
    $tempidoc = $idoc;
    $list = array();
    $depth_index = 0; //used for knowing the curent xml level. 0 at the begining
    $i = 0;
    $attr_idoc = false;
    $value = "";
    $tabvalues = array();
    $is_array = false;
    
    $xml_parser = xml_parser_create("ISO-8859-1");
    // use case-folding so we are sure to find the tag in $map_array
    xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
    xml_set_element_handler($xml_parser, "startElementXml", "endElementXml");
    xml_set_character_data_handler($xml_parser, "characterDataXml");
    
    if (!xml_parse($xml_parser, $fp)) {
        die(sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($xml_parser)) , xml_get_current_line_number($xml_parser)));
    }
    xml_parser_free($xml_parser);
    
    while ($attribut = each($tabvalues)) {
        //printf($attribut[0]);
        //printf(" : ");
        //printf(sizeof($list[$attribut[0]]));
        //printf("\n");
        if (sizeof($list[$attribut[0]]) != 0) { //
            //printf("tableau_sup_a_zero");
            $value = "";
            $ii = 0;
            while ($x = each($list[$attribut[0]])) {
                $ii++;
                if (($ii) != 1) {
                    $value.= "\n";
                }
                $value.= $x[1];
            }
            //printf($value);
            $idoc->SetValue($attribut[0], $value);
        } else {
            $idoc->SetValue($attribut[0], $attribut[1]);
        }
    }
    $idoc->title = $title;
}
//these two next functions are used for idoc attributes
function recreate_balise_ouvrante($name, $attrs)
{
    //printf("ici ");
    $balise = "<$name ";
    while (list($att, $valeur) = each($attrs)) {
        $balise.= " $att=\"$valeur\"";
    }
    $balise.= ">";
    //printf("balise_ouvrante");
    return $balise;
}

function recreate_balise_fermante($name)
{
    return "</$name>";
}

function startElementXml($parser, $name, $attrs)
{
    //this function is called when parser find a start element in the xml.
    global $depth_index;
    $depth_index++;
    global $action;
    global $title;
    global $attr_idoc;
    global $is_array;
    global $value;
    global $tempidoc;
    
    if ($depth_index == 1) {
        $title = $attrs["TITLE"];
    }
    
    if ($attr_idoc) {
        $value.= recreate_balise_ouvrante($name, $attrs); //to recover xml of idoc or listidoc attribute.
        
    }
    
    if ($depth_index == 3) {
        
        $attribute = $tempidoc->GetAttribute($name);
        
        $is_array = false;
        // $attr_idoc=false;
        $is_array = $attribute->inArray();
        if ($attribute->type == "idoc") {
            $attr_idoc = true;
        }
        if ($attribute->repeat) {
            $is_array = true;
        }
    }
}

function endElement($parser, $name)
{
    //this function is called when parser find a end element in the xml.
    global $value;
    global $tabvalues;
    global $i;
    global $list;
    global $depth_index;
    global $attr_idoc;
    global $is_array;
    
    $value = trim($value);
    
    if ($depth_index == 3) {
        if (!$is_array) { //case of single attribut
            if ($attr_idoc) {
                $tabvalues[$name] = base64_encode($value); // in case of idoc attribute, value(a xml) of the attribute is coded
                
            } else {
                $tabvalues[$name] = $value;
            }
        } else {
            $tabvalues[$name] = "something"; //the value is not important but $tabvalues[$name] must exist
            //case of list attribut
            if ($attr_idoc) {
                $list[$name][$i] = base64_encode($value);
            } else {
                $list[$name][$i] = $value;
            }
            // printf("icic");
            $i++;
        }
        $value = "";
        if ($attr_idoc) {
            $attr_idoc = false;
        }
    }
    
    if ($attr_idoc) { //to recover xml of idoc attribute
        $value.= recreate_balise_fermante($name);
        //printf("la   ");printf($value);
        
    }
    
    $depth_index--;
}

function characterDataXml($parser, $data)
{
    global $value;
    if (chop($data) != "") {
        $value.= $data;
    }
}

function recup_argument_from_xml($xml, $nom_arg)
{
    
    $title = stristr($xml, "$nom_arg=");
    $title = strstr($title, "\"");
    $title = substr($title, 1);
    $fin = strpos($title, "\"");
    $title = substr($title, 0, $fin);
    //printf($title);
    return $title;
}
?>
