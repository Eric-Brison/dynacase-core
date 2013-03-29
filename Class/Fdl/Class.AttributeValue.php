<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp;

class AttributeValue
{
    public static function getTypedValue(\Doc & $doc, \NormalAttribute & $oAttr)
    {
        
        if ($oAttr->isMultiple()) {
            return self::getMultipleValues($doc, $oAttr);
        }
        if ($oAttr->type == "array") {
            return self::getArrayValues($doc, $oAttr);
        }
        $rawValue = $doc->getRawValue($oAttr->id, null);
        return self::castValue($oAttr->type, $rawValue);
    }
    private static function castValue($type, $rawValue)
    {
        if ($rawValue === null || $rawValue === '') return null;
        switch ($type) {
            case 'int':
                $typedValue = intval($rawValue);
                break;

            case 'money':
            case 'double':
                $typedValue = doubleval($rawValue);
                break;

            case 'timestamp':
            case 'date':
                $typedValue = stringDateToIso($rawValue, false, true);
                if (strlen($rawValue) == 16) {
                    $typedValue.= ':00';
                }
                break;

            case 'time':
                $typedValue = $rawValue;
                if (strlen($rawValue) == 5) {
                    $typedValue.= ':00';
                }
                break;

            default: // text, htmltext, longtext, enum, file, image,thesaurus,docid,account
                $typedValue = $rawValue;
        }
        return $typedValue;
    }
    private static function getMultipleValues(\Doc & $doc, \NormalAttribute & $oAttr)
    {
        if ($oAttr->inArray() && $oAttr->getOption("multiple") == "yes") {
            return self::getMultiple2Values($doc, $oAttr);
        }
        $rawValues = $doc->getMultipleRawValues($oAttr->id);
        $type = $oAttr->type;
        $typedValues = array();
        foreach ($rawValues as $rawValue) {
            $typedValues[] = self::castValue($type, $rawValue);
        }
        switch ($type) {
            case 'longtext':
                foreach ($typedValues as & $v) {
                    $v = str_replace('<BR>', "\n", $v);
                }
                break;
        }
        return $typedValues;
    }
    private static function getMultiple2Values(\Doc & $doc, \NormalAttribute & $oAttr)
    {
        
        $rawValues = $doc->getMultipleRawValues($oAttr->id);
        $type = $oAttr->type;
        $typedValues = array();
        foreach ($rawValues as $rawValue) {
            $finalValues = ($rawValue !== '') ? explode('<BR>', $rawValue) : array();
            $finalTypedValues = array();
            foreach ($finalValues as $finalValue) {
                $finalTypedValues[] = self::castValue($type, $finalValue);
            }
            $typedValues[] = $finalTypedValues;
        }
        
        return $typedValues;
    }
    
    private static function getArrayValues(\Doc & $doc, \NormalAttribute & $oAttr)
    {
        
        if ($oAttr->type == "array") {
            $ta = $doc->attributes->getArrayElements($oAttr->id);
            $ti = $tv = array();
            $ix = 0;
            // transpose
            foreach ($ta as $k => $v) {
                $tv[$k] = self::getMultipleValues($doc, $doc->getAttribute($k));
                $ix = max($ix, count($tv[$k]));
            }
            for ($i = 0; $i < $ix; $i++) {
                $ti[$i] = array();
            }
            foreach ($ta as $k => $v) {
                for ($i = 0; $i < $ix; $i++) {
                    $ti[$i]+= array(
                        $k => isset($tv[$k][$i]) ? $tv[$k][$i] : null
                    );
                }
            }
            return $ti;
        }
        throw new Exception('DOC0115', $oAttr->id, $doc->title, $doc->fromname);
    }
    public static function setTypedValue(\Doc & $doc, \NormalAttribute & $oAttr, $value)
    {
    }
}
