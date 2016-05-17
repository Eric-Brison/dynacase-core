<?php
/*
 * @author Anakeen
 * @package FDL
 */


namespace Dcp;

class VerifyAttributeAccess
{
    
    protected static $attributeGrants = array();
    /**
     * Verify is attribute has visible access
     * @param \Doc $doc the document to see
     * @param \BasicAttribute $attribute the attribut to see
     * @return bool return true if can be viewed
     */
    public static function isAttributeAccessGranted(\Doc $doc, \BasicAttribute $attribute)
    {
        
        $key = sprintf("%0d-%0d-%0d-%s", $doc->fromid, $doc->cvid, $doc->wid, $doc->state);
        
        if (!isset(self::$attributeGrants[$key])) {
            if (!$doc->mid) {
                $doc->setMask(\Doc::USEMASKCVVIEW);
            }
            self::$attributeGrants[$key] = array();
            $oas = $doc->getNormalAttributes();
            foreach ($oas as $oa) {
                if ($oa->mvisibility === "I") {
                    self::$attributeGrants[$key][$oa->id] = false;
                }
            }
        }
        return (!isset(self::$attributeGrants[$key][$attribute->id]));
    }
    public static function clearCache()
    {
        self::$attributeGrants = array();
    }
}
