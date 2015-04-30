<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Created by PhpStorm.
 * User: eric
 * Date: 26/03/15
 * Time: 16:22
 */

namespace Dcp\Utils;

class htmlclean
{
    /**
     * Delete dangerous attributes and elements to prevent xss attacks
     * @param string $data html fragment
     * @return string
     */
    public static function xssClean($data)
    {
        // Fix &entity\n;
        $data = str_replace(array(
            '&amp;',
            '&lt;',
            '&gt;'
        ) , array(
            '&amp;amp;',
            '&amp;lt;',
            '&amp;gt;'
        ) , $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[a-z]*\s*=\s*["][^"]*["]+#iu', '$1', $data);
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[a-z]*\s*=\s*[\'][^\']*[\']+#iu', '$1', $data);
        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
        
        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|script)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);
        // we are done...
        return $data;
    }
    
    public static function cleanStyle($data)
    {
        // Remove span tags and keep content
        $data = preg_replace('/<\/?span[^>]*>/is', "", $data);
        // Remove font tags and keep content
        $data = preg_replace('/<\/?font[^>]*>/is', "", $data);
        // Remove style attributes
        $data = preg_replace('/<([^>]*) style\s*=\s*"[^"]*"/is', "<\\1", $data);
        $data = preg_replace('/<([^>]*) style\s*=\s*\'[^\']*\'/is', "<\\1", $data);
        // Delete class attributes
        $data = preg_replace('/<([^>]*) class\s*=\s*"[^"]*"/is', "<\\1", $data);
        $data = preg_replace('/<([^>]*) class\s*=\s*\'[^\']*\'/is', "<\\1", $data);
        // Delete style tags
        $data = preg_replace('/<\s*style[^>]*>[^<]*<\/style>/iu', "", $data);
        /*
        do {
            // Remove really unwanted tags
            $old_data = $data;
           // $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);
        */
        return $data;
    }
}
