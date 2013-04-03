<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp;

class AttributeValue
{
    /**
     * return typed value for an document's attribute
     * @param \Doc $doc
     * @param \NormalAttribute $oAttr
     * @throws AttributeValue\Exception
     * @internal param $
     * @return array|float|int|null|string
     */
    public static function getTypedValue(\Doc & $doc, \NormalAttribute & $oAttr)
    {
        
        if (!isset($doc->attributes->attr[$oAttr->id])) {
            throw new \Dcp\AttributeValue\Exception('VALUE0101', $oAttr->id, $doc->fromname, $doc->getTitle());
        }
        if ($oAttr->isMultiple()) {
            return self::getMultipleValues($doc, $oAttr);
        }
        if ($oAttr->type == "array") {
            return self::getArrayValues($doc, $oAttr);
        }
        $rawValue = $doc->getRawValue($oAttr->id, null);
        return self::castValue($oAttr->type, $rawValue);
    }
    
    private static function getMultipleValues(\Doc & $doc, \NormalAttribute & $oAttr)
    {
        if ($oAttr->isMultipleInArray()) {
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
    /**
     * cast raw value to type value
     * @param string $type like text, int, double
     * @param string $rawValue raw database value
     * @return float|int|null|string
     */
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
        throw new \Dcp\AttributeValue\Exception('VALUE0100', $oAttr->id, $doc->title, $doc->fromname);
    }
    /**
     * Set a new value to an attribute document
     * @param \Doc $doc
     * @param \NormalAttribute $oAttr
     * @param mixed $value
     * @see Doc::setAttributeValue()
     * @throws AttributeValue\Exception in case of incompatible value
     */
    public static function setTypedValue(\Doc & $doc, \NormalAttribute & $oAttr, $value)
    {
        if (!isset($doc->attributes->attr[$oAttr->id])) {
            throw new \Dcp\AttributeValue\Exception('VALUE0004', $oAttr->id, $doc->fromname, $doc->getTitle());
        }
        $err = '';
        if ($value === null) {
            $err = $doc->clearValue($oAttr->id);
        } else if ($oAttr->isMultiple()) {
            
            if (!is_array($value)) {
                throw new \Dcp\AttributeValue\Exception('VALUE0002', print_r($value, true) , $oAttr->id, $doc->fromname, $doc->getTitle());
            }
            if ($value === array()) {
                $err = $doc->clearValue($oAttr->id);
            } else {
                if ($oAttr->isMultipleInArray()) {
                    $rawValues = array();
                    foreach ($value as $k => $rowValues) {
                        if (is_array($rowValues)) {
                            $rawValues[$k] = implode('<BR>', $rowValues);
                        } else {
                            if ($rowValues === null) {
                                $rawValues[$k] = '';
                            } else {
                                throw new \Dcp\AttributeValue\Exception('VALUE0003', print_r($value, true) , $oAttr->id, $doc->fromname, $doc->getTitle());
                            }
                        }
                    }
                    $err = $doc->setValue($oAttr->id, $rawValues);
                } else {
                    $err = $doc->setValue($oAttr->id, $value);
                }
            }
        } elseif ($oAttr->type == "array") {
            // TODO
            
        } else {
            $err = $doc->setValue($oAttr->id, $value);
        }
        if ($err) {
            throw new \Dcp\AttributeValue\Exception('VALUE0001', $oAttr->id, $doc->fromname, $doc->getTitle() , $err);
        }
    }
}
