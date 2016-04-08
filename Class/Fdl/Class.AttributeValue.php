<?php
/*
 * @author Anakeen
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
                $isoDate = stringDateToIso($rawValue, false, true);
                if (strlen($rawValue) == 16) {
                    $isoDate.= ':00';
                }
                $typedValue = new \DateTime($isoDate);
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
    private static function typed2string($type, $typedValue)
    {
        if ($typedValue === null || $typedValue === '') return null;
        
        if (is_array($typedValue)) {
            $arrayString = array();
            foreach ($typedValue as $k => $aSingleValue) {
                $arrayString[$k] = self::singleTyped2string($type, $aSingleValue);
            }
            return $arrayString;
        } else {
            return self::singleTyped2string($type, $typedValue);
        }
    }
    private static function singleTyped2string($type, $typedValue)
    {
        if ($typedValue === null || $typedValue === '') {
            return null;
        }
        
        switch ($type) {
            case 'int':
                if (!is_string($typedValue) && !is_int($typedValue)) {
                    throw new \Dcp\AttributeValue\Exception('VALUE0200', print_r($typedValue, true) , gettype($typedValue));
                }
                break;

            case 'money':
            case 'double':
                if (!is_string($typedValue) && !is_int($typedValue) && !is_double($typedValue)) {
                    throw new \Dcp\AttributeValue\Exception('VALUE0201', print_r($typedValue, true) , gettype($typedValue));
                }
                break;

            case 'timestamp':
                if (is_a($typedValue, "DateTime")) {
                    /**
                     * @var \DateTime $typedValue
                     */
                    $typedValue = $typedValue->format('Y-m-d\TH:i:s');
                }
                
                break;

            case 'date':
                if (is_a($typedValue, "DateTime")) {
                    /**
                     * @var \DateTime $typedValue
                     */
                    $typedValue = $typedValue->format('Y-m-d');
                }
                
                break;

            default: // text, htmltext, longtext, enum, file, image,thesaurus,docid,account
                ;
        }
        if (!is_scalar($typedValue)) {
            throw new \Dcp\AttributeValue\Exception('VALUE0202', print_r($typedValue, true) , gettype($typedValue));
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
    
    private static function setTypedArrayValue(\Doc & $doc, \NormalAttribute & $oAttr, array $value)
    {
        $doc->clearArrayValues($oAttr->id);
        foreach ($value as $row) {
            if (!is_array($row)) {
                throw new \Dcp\AttributeValue\Exception('VALUE0009', $oAttr->id, $doc->fromname, $doc->getTitle() , print_r($row, true));
            }
            foreach ($row as $columnName => & $columnValue) {
                $cAttr = $doc->getAttribute($columnName);
                if ($cAttr) {
                    $columnValue = self::typed2string($cAttr->type, $columnValue);
                }
            }
            unset($columnValue);
            $err = $doc->addArrayRow($oAttr->id, $row);
            if ($err) {
                throw new \Dcp\AttributeValue\Exception('VALUE0007', $oAttr->id, $doc->fromname, $doc->getTitle() , $err);
            }
        }
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
        $kindex = - 1;
        $err = '';
        if ($value === null) {
            if ($oAttr->type == "array") {
                self::setTypedArrayValue($doc, $oAttr, array());
            } else {
                $err = $doc->clearValue($oAttr->id);
            }
        } else if ($oAttr->isMultiple()) {
            
            if (!is_array($value)) {
                $e = new \Dcp\AttributeValue\Exception('VALUE0002', print_r($value, true) , $oAttr->id, $doc->fromname, $doc->getTitle());
                $e->attributeId = $oAttr->id;
                throw $e;
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
                                $e = new \Dcp\AttributeValue\Exception('VALUE0003', print_r($value, true) , $oAttr->id, $doc->fromname, $doc->getTitle());
                                $e->attributeId = $oAttr->id;
                                throw $e;
                            }
                        }
                    }
                    $err = $doc->setValue($oAttr->id, self::typed2string($oAttr->type, $rawValues) , -1, $kindex);
                } else {
                    $err = $doc->setValue($oAttr->id, self::typed2string($oAttr->type, $value) , -1, $kindex);
                }
            }
        } elseif ($oAttr->type == "array") {
            if (!is_array($value)) {
                $e = new \Dcp\AttributeValue\Exception('VALUE0008', $oAttr->id, $doc->fromname, $doc->getTitle() , print_r($value, true));
                $e->attributeId = $oAttr->id;
                throw $e;
            }
            self::setTypedArrayValue($doc, $oAttr, $value);
        } else {
            if (is_array($value)) {
                $e = new \Dcp\AttributeValue\Exception('VALUE0006', $oAttr->id, $doc->fromname, $doc->getTitle() , print_r($value, true));
                $e->attributeId = $oAttr->id;
                throw $e;
            }
            try {
                $err = $doc->setValue($oAttr->id, self::typed2string($oAttr->type, $value) , -1, $kindex);
            }
            catch(\Dcp\AttributeValue\Exception $e) {
                $e = new \Dcp\AttributeValue\Exception('VALUE0005', $oAttr->id, $doc->fromname, $doc->getTitle() , $e->getMessage());
                $e->attributeId = $oAttr->id;
                throw $e;
            }
        }
        if ($err) {
            $e = new \Dcp\AttributeValue\Exception('VALUE0001', $oAttr->id, $doc->fromname, $doc->getTitle() , $err);
            $e->originalError = $err;
            $e->attributeId = $oAttr->id;
            $e->index = $kindex;
            throw $e;
        }
    }
}
