<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Core\vidExtractor;

class Exception extends \Dcp\Exception
{
}
/**
 * Class vidExtractor
 *
 * Extract VIDs from documents or families (suitable for updating and maintaining the "docvaultindex" table)
 *
 * @package Dcp\Core
 */
class vidExtractor
{
    /**
     * Get list of distinct VIDs (files vault identifier) from a "raw" document (i.e. a row from a SQL query resultset)
     *
     * @param array $raw Raw document
     * @param array $fileAttrIdList List of attribute's name of type 'file' (if null, the list will be dynamically created from $raw['id'])
     * @return array
     * @throws Exception
     */
    public static function getVidsFromRawDoc($raw, $fileAttrIdList = null)
    {
        if (!is_array($raw)) {
            throw new Exception('VIDEXTRACTOR0001', gettype($raw));
        }
        if ($fileAttrIdList === null) {
            if (!isset($raw['id'])) {
                throw new Exception('VIDEXTRACTOR0002');
            }
            $fileAttrIdList = array();
            $doc = new_Doc('', $raw['id']);
            if (!is_object($doc)) {
                throw new Exception('VIDEXTRACTOR0003', $raw['id']);
            }
            $fileAttrList = $doc->getFileAttributes();
            foreach ($fileAttrList as $attrId => $attr) {
                $fileAttrIdList[] = $attrId;
            }
        }
        $vidList = array();
        /* icon */
        if (!empty($raw['icon']) && ($vid = self::parseVid($raw['icon'])) !== false) {
            $vidList[] = $vid;
        }
        /* file attributes */
        foreach ($fileAttrIdList as $attr) {
            if (!isset($raw[$attr])) {
                continue;
            }
            $values = $raw[$attr];
            $values = preg_split('/\n/', str_replace('<BR>', "\n", $values));
            foreach ($values as $value) {
                if (($vid = self::parseVid($value)) !== false) {
                    $vidList[] = $vid;
                }
            }
        }
        return $vidList;
    }
    /**
     * Extract VIDs from a family object
     *
     * @param \DocFam $docfam
     * @return array
     */
    public static function getVidsFromDocFam(\DocFam & $docfam)
    {
        $values = array();
        /*
         * Track files from docfam.param and docfam.defval
        */
        foreach (array(
            $docfam->getOwnParams() ,
            $docfam->getOwnDefValues()
        ) as $list) {
            foreach ($list as $aid => $value) {
                if (($oattr = $docfam->getAttribute($aid)) === false) {
                    $docfam->log->warning(\ErrorCode::getError('VIDEXTRACTOR0004', $aid, $docfam->name));
                    continue;
                }
                if ($oattr->type !== 'file' && $oattr->type !== 'image') {
                    continue;
                }
                if ($oattr->inArray()) {
                    $values = array_merge($values, $docfam->rawValueToArray($value));
                } else {
                    $values[] = $value;
                }
            }
        }
        /*
         * Track files from icon
        */
        if (isset($docfam->icon)) {
            $values[] = $docfam->icon;
        }
        /*
         * Extract vids from values
        */
        $vids = array();
        foreach ($values as $value) {
            if (($vid = self::parseVid($value)) !== false) {
                $vids[$vid] = $vid;
            }
        }
        return $vids;
    }
    /**
     * Extract VIDs from a document object
     *
     * @param \Doc $doc
     * @return array
     */
    public static function getVidsFromDoc(\Doc & $doc)
    {
        $vids = array();
        $fa = $doc->GetFileAttributes();
        /* Track files from attributes */
        foreach ($fa as $aid => $oattr) {
            if ($oattr->inArray()) {
                $ta = $doc->getMultipleRawValues($aid);
            } else {
                $ta = array(
                    $doc->getRawValue($aid)
                );
            }
            foreach ($ta as $k => $v) {
                if (($vid = self::parseVid($v)) !== false) {
                    $vids[$vid] = $vid;
                }
            }
        }
        /* Track file from icon */
        if (isset($doc->icon)) {
            if (($vid = self::parseVid($doc->icon)) !== false) {
                $vids[$vid] = $vid;
            }
        }
        return $vids;
    }
    /**
     * Parse and extract VID from string
     *
     * @param $vid
     * @return bool
     */
    public static function parseVid($vid)
    {
        if (!preg_match(PREGEXPFILE, $vid, $m)) {
            return false;
        }
        if (!isset($m[2])) {
            return false;
        }
        if ($m[2] === '') {
            return false;
        }
        return $m[2];
    }
}
