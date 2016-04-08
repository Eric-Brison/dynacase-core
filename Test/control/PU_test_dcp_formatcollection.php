<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;
/**
 * @author Anakeen
 * @package Dcp\Pu
 */

require_once 'PU_testcase_dcp_commonfamily.php';

class TestFormatCollection extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FMTCOL
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_formatcollection.ods",
            "PU_data_dcp_documentsformat1.xml",
            "PU_data_dcp_documentsformat2.xml",
            "PU_data_dcp_documentsformat3.xml",
            "PU_data_dcp_documentsformat4.xml",
            "PU_data_dcp_documentsformat5.xml",
            "PU_data_dcp_formatcollectionprofil.ods",
        );
    }
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $d1 = new_doc(self::$dbaccess, "TST_FMTCOL1");
        $d1->state = "E1";
        $d1->modify();
        $d1 = new_doc(self::$dbaccess, "TST_FMTCOL2");
        $d1->state = "E2";
        $d1->modify();
        $d1 = new_doc(self::$dbaccess, "TST_FMTCOL3");
        $d1->state = "E3";
        $d1->modify();
    }
    
    protected $famName = 'TST_FMTCOL';
    /**
     * @dataProvider dataRenderProfilRelationFormatCollection
     */
    public function testRenderProfilRelationFormatCollection($login, $docName, $attrName, $expectRender, $expectContainRender = array())
    {
        $this->sudo($login);
        
        $this->testRenderFormatCollection($docName, $attrName, $expectRender, $expectContainRender);
        $this->exitSudo();
    }
    /**
     * @dataProvider dataRenderFormatCollection
     */
    public function testRenderFormatCollection($docName, $attrName, $expectRender, $expectContainRender = array())
    {
        $this->requiresCoreParamEquals('CORE_LANG', 'fr_FR');
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $dl = $s->search()->getDocumentList();
        $fc = new \FormatCollection();
        $fc->useCollection($dl);
        $fc->relationNoAccessText = 'no grant';
        $fc->addProperty($fc::propName)->addProperty($fc::propUrl);
        
        $f = new_doc(self::$dbaccess, $this->famName);
        $la = $f->getNormalAttributes();
        foreach ($la as $aid => $oa) {
            if ($oa->type != "array") $fc->addAttribute($aid);
        }
        
        $r = $fc->render();
        //print_r2($fc->getDebug());
        $this->assertEquals($s->count() , count($r) , "render must have same entry count has collection");
        //print_r(($r));
        //print_r2(json_encode($r));
        $fValue = $this->getRenderValue($r, $docName, $attrName);
        if (is_array($expectRender)) {
            foreach ($expectRender as $expAttr => $expVal) {
                if (is_array($expVal)) {
                    $testValue = array();
                    $this->assertTrue(is_array($fValue) , sprintf("result %s not an array for %s", print_r($fValue, true) , $expAttr));
                    foreach ($fValue as $k => $v) {
                        if (is_array($v)) {
                            $testValue[$k] = array();
                            foreach ($v as $vv) {
                                $testValue[$k][] = $vv->$expAttr;
                            }
                        } else {
                            $testValue[$k] = $v->$expAttr;
                        }
                    }
                } else {
                    $testValue = ($fValue === null) ? null : $fValue->$expAttr;
                }
                $this->assertEquals($expVal, $testValue, sprintf("values is : %s %s ", print_r($testValue, true) , json_encode($fValue)));
            }
        } else {
            $this->assertEquals($expectRender, $fValue, sprintf("values is : %s", sprintf(json_encode($fValue))));
        }
        foreach ($expectContainRender as $expAttr => $expVal) {
            $this->assertTrue(preg_match("/$expVal/", $fValue->$expAttr) > 0, sprintf("not match for $expVal. values is : %s", json_encode($fValue)));
        }
    }
    /**
     * @dataProvider dataUnknowRenderFormatCollection
     */
    public function testUnknowRenderFormatCollection($docName, $attrName, $nc)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $dl = $s->search()->getDocumentList();
        $fc = new \FormatCollection();
        $fc->useCollection($dl);
        $fc->addProperty($fc::propName);
        $fc->addAttribute(('tst_x'));
        $fc->setNc($nc);
        
        $r = $fc->render();
        $this->assertEquals($s->count() , count($r) , "render must have same entry count has collection");
        /**
         * @var \UnknowAttributeValue $fValue
         */
        $fValue = $this->getRenderValue($r, $docName, $attrName);
        $this->assertEquals($nc, $fValue->value);
    }
    /**
     * @dataProvider dataStateRenderFormatCollection
     */
    public function testStateRenderFormatCollection($docName, $expectState, $expectColor, $expectActivity, $expectDisplayValue)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $dl = $s->search()->getDocumentList();
        $fc = new \FormatCollection();
        $fc->useCollection($dl);
        $fc->addProperty($fc::propName)->addProperty($fc::propState);
        
        $r = $fc->render();
        $this->assertEquals($s->count() , count($r) , "render must have same entry count has collection");
        /**
         * @var \StatePropertyValue $fstate
         */
        $fstate = $this->getRenderProp($r, $docName, $fc::propState);
        $this->assertEquals($expectState, $fstate->reference, "incorrect state reference");
        $this->assertEquals($expectColor, $fstate->color, "incorrect state color ");
        $this->assertEquals($expectActivity, $fstate->activity, "incorrect state activity");
        $this->assertEquals($expectDisplayValue, $fstate->displayValue, sprintf("incorrect state display value : %s", print_r($fstate, true)));
    }
    /**
     * @dataProvider dataDatePropertyRenderFormatCollection
     */
    public function testDatePropertyRenderFormatCollection($docName, $propertyName, $format, $expectedFormat)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $dl = $s->search()->getDocumentList();
        $fc = new \FormatCollection();
        $fc->useCollection($dl);
        $fc->addProperty($fc::propName)->addProperty($propertyName);
        $fc->setDateStyle($format);
        
        $r = $fc->render();
        $this->assertEquals($s->count() , count($r) , "render must have same entry count has collection");
        
        $propertyValue = $this->getRenderProp($r, $docName, $propertyName);
        $this->assertRegExp($expectedFormat, $propertyValue, sprintf("incorrect property (%s) display value : %s", $propertyName, print_r($propertyValue, true)));
    }
    /**
     * @dataProvider dataPropertyRenderFormatCollection
     */
    public function testPropertyRenderFormatCollection($docName, $propertyName, $expectedValue)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $dl = $s->search()->getDocumentList();
        $fc = new \FormatCollection();
        $fc->setPropDateStyle(\DateAttributeValue::isoWTStyle);
        $fc->useCollection($dl);
        $fc->addProperty($fc::propName)->addProperty($propertyName);
        
        $r = $fc->render();
        $this->assertEquals($s->count() , count($r) , "render must have same entry count has collection");
        
        $propertyValue = $this->getRenderProp($r, $docName, $propertyName);
        if (is_array($expectedValue)) {
            foreach ($expectedValue as $infoKey => $expectInfo) {
                
                if ($expectInfo[0] === "/") {
                    $this->assertRegExp($expectInfo, (string)$propertyValue[$infoKey], sprintf("incorrect property (%s) display value : %s", $propertyName, print_r($propertyValue, true)));
                } else {
                    $this->assertEquals($expectInfo, $propertyValue[$infoKey], sprintf("incorrect property (%s) display value : %s", $propertyName, print_r($propertyValue, true)));
                }
            }
        } elseif ($expectedValue[0] === "/") {
            $this->assertRegExp($expectedValue, $propertyValue, sprintf("incorrect property (%s) display value : %s", $propertyName, print_r($propertyValue, true)));
        } else {
            $this->assertEquals($expectedValue, $propertyValue, sprintf("incorrect property (%s) display value : %s", $propertyName, print_r($propertyValue, true)));
        }
    }
    /**
     * @dataProvider dataRenderAttributeHookFormatCollection
     */
    public function testRenderAttributeHookFormatCollection($docName, $attrName, $hook, $expectRender)
    {
        $this->requiresCoreParamEquals('CORE_LANG', 'fr_FR');
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $dl = $s->search()->getDocumentList();
        $fc = new \FormatCollection();
        $fc->useCollection($dl);
        $fc->setAttributeRenderHook($hook);
        $fc->relationNoAccessText = 'no grant';
        $fc->addProperty($fc::propName)->addProperty($fc::propUrl);
        
        $f = new_doc(self::$dbaccess, $this->famName);
        $la = $f->getNormalAttributes();
        foreach ($la as $aid => $oa) {
            if ($oa->type != "array") $fc->addAttribute($aid);
        }
        
        $r = $fc->render();
        //print_r2($fc->getDebug());
        $this->assertEquals($s->count() , count($r) , "render must have same entry count has collection");
        //print_r(($r));
        //print_r2(json_encode($r));
        $fValue = $this->getRenderValue($r, $docName, $attrName);
        if (is_array($expectRender)) {
            foreach ($expectRender as $expAttr => $expVal) {
                if (is_array($expVal)) {
                    $testValue = array();
                    $this->assertTrue(is_array($fValue) , sprintf("result %s not an array for %s", print_r($fValue, true) , $expAttr));
                    foreach ($fValue as $k => $v) {
                        if (is_array($v)) {
                            $testValue[$k] = array();
                            foreach ($v as $vv) {
                                $testValue[$k][] = $vv->$expAttr;
                            }
                        } else {
                            $testValue[$k] = $v->$expAttr;
                        }
                    }
                } else {
                    $testValue = ($fValue === null) ? null : $fValue->$expAttr;
                }
                $this->assertEquals($expVal, $testValue, sprintf("values is : %s %s ", print_r($testValue, true) , json_encode($fValue)));
            }
        } else {
            $this->assertEquals($expectRender, $fValue, sprintf("values is : %s", sprintf(json_encode($fValue))));
        }
    }
    /**
     * @dataProvider dataPropertyHookRenderFormatCollection
     */
    public function testPropertyHookRenderFormatCollection($docName, $propertyName, $hook, $expectedValue)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $dl = $s->search()->getDocumentList();
        
        $fc = new \FormatCollection();
        $fc->useCollection($dl);
        $fc->setPropertyRenderHook($hook);
        $fc->addProperty($fc::propName)->addProperty($propertyName);
        
        $r = $fc->render();
        $this->assertEquals($s->count() , count($r) , "render must have same entry count has collection");
        
        $propertyValue = $this->getRenderProp($r, $docName, $propertyName);
        $this->assertEquals($expectedValue, $propertyValue, sprintf("incorrect property (%s)  value : %s", $propertyName, print_r($propertyValue, true)));
    }
    /**
     * @dataProvider dataDocumentHookRenderFormatCollection
     */
    public function testDocumentHookRenderFormatCollection($docName, $hook, array $expectedProps)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $dl = $s->search()->getDocumentList();
        
        $fc = new \FormatCollection();
        $fc->useCollection($dl);
        $fc->setDocumentRenderHook($hook);
        $fc->addProperty($fc::propName)->addProperty("revision");
        
        $r = $fc->render();
        $this->assertEquals($s->count() , count($r) , "render must have same entry count has collection");
        
        foreach ($expectedProps as $propKey => $propValue) {
            $propertyValue = $this->getRenderProp($r, $docName, $propKey);
            $this->assertEquals($propValue, $propertyValue, sprintf("incorrect property (%s)  value : %s", $propKey, print_r($propertyValue, true)));
        }
    }
    private function getRenderValue(array $r, $docName, $attrName)
    {
        foreach ($r as $format) {
            if ($format["properties"]["name"] == $docName) {
                return $format["attributes"][$attrName];
            }
        }
        return null;
    }
    
    private function getRenderProp(array $r, $docName, $attrName)
    {
        foreach ($r as $format) {
            if ($format["properties"]["name"] == $docName) {
                return $format["properties"][$attrName];
            }
        }
        return null;
    }
    public function dataPropertyRenderFormatCollection()
    {
        return array(
            array(
                "TST_FMTCOL1",
                "revision",
                0
            ) ,
            array(
                "TST_FMTCOL1",
                "name",
                "TST_FMTCOL1"
            ) ,
            array(
                "TST_FMTCOL1",
                "revdate",
                '/^(\d\d\d\d)-(\d\d)-(\d\d) (\d\d):(\d\d):(\d\d)$/'
            ) ,
            array(
                "TST_FMTCOL1",
                "cdate",
                '/^(\d\d\d\d)-(\d\d)-(\d\d) (\d\d):(\d\d):(\d\d)$/'
            ) ,
            array(
                "TST_FMTCOL1",
                \formatCollection::propFamily,
                array(
                    "name" => $this->famName,
                    "title" => "Test Format",
                    "id" => '/^[0-9]+$/',
                    "icon" => "/resizeimg.php/"
                )
            ) ,
            array(
                "TST_FMTCOL1",
                \formatCollection::propRevisionData,
                array(
                    "id" => '/^[0-9]+$/',
                    "number" => 0
                )
            ) ,
            array(
                "TST_FMTCOL1",
                \formatCollection::propSecurity,
                array(
                    "readOnly" => false
                )
            ) ,
            array(
                "TST_FMTCOL1",
                \formatCollection::propType,
                "document"
            ) ,
            array(
                "TST_FMTCOL1",
                \formatCollection::propUsage,
                "normal"
            ) ,
            array(
                "TST_FMTCOL1",
                \formatCollection::propNote,
                array(
                    "id" => 0
                )
            ) ,
            array(
                "TST_FMTCOL1",
                \formatCollection::propViewController,
                array(
                    "id" => 0
                )
            ) ,
            array(
                "TST_FMTCOL1",
                \formatCollection::propAffected,
                array(
                    "id" => 0
                )
            ) ,
            array(
                "TST_FMTCOL1",
                \formatCollection::propWorkflow,
                array(
                    "title" => "Cycle format",
                    "id" => '/^[0-9]+$/',
                    "icon" => "/resizeimg.php/"
                )
            ) ,
            array(
                "TST_FMTCOL1",
                \formatCollection::propCreationDate,
                '/^(\d\d\d\d)-(\d\d)-(\d\d) (\d\d):(\d\d):(\d\d)$/'
            ) ,
            array(
                "TST_FMTCOL1",
                \formatCollection::propLastModificationDate,
                '/^(\d\d\d\d)-(\d\d)-(\d\d) (\d\d):(\d\d):(\d\d)$/'
            ) ,
            array(
                "TST_FMTCOL1",
                \formatCollection::propLastAccessDate,
                '/^(\d\d\d\d)-(\d\d)-(\d\d) (\d\d):(\d\d):(\d\d)$/'
            )
        );
    }
    public function dataDocumentHookRenderFormatCollection()
    {
        return array(
            array(
                "TST_FMTCOL1",
                function ($info, $doc)
                {
                    /**
                     * @var \Doc $doc
                     */
                    $info["properties"]["revision"].= ' (bis)';
                    $info["properties"]["hello"] = 'world - ' . $doc->name;
                    return $info;
                }
                ,
                array(
                    "revision" => '0 (bis)',
                    "hello" => "world - TST_FMTCOL1"
                )
            )
        );
    }
    public function dataPropertyHookRenderFormatCollection()
    {
        return array(
            array(
                "TST_FMTCOL1",
                "revision",
                function ($info, $propId)
                {
                    if ($propId === "revision") {
                        $info.= " (bis)";
                    }
                    return $info;
                }
                ,
                '0 (bis)'
            ) ,
            array(
                "TST_FMTCOL1",
                "doctype",
                function ($info, $propId)
                {
                    if ($propId === "doctype") {
                        if ($info === "F") {
                            $info = "document";
                        }
                    }
                    return $info;
                }
                ,
                "document"
            ) ,
        );
    }
    public function dataUnknowRenderFormatCollection()
    {
        
        return array(
            array(
                "TST_FMTCOL1",
                "tst_x",
                "act"
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_x",
                ""
            )
        );
    }
    
    public function dataRenderAttributeHookFormatCollection()
    {
        
        return array(
            array(
                "TST_FMTCOL1",
                "tst_title",
                function ($info)
                {
                    if ($info) {
                        if (!is_array($info)) {
                            $info->value.= " (bis)";
                            $info->displayValue.= " (ter)";
                        }
                    }
                    return $info;
                }
                ,
                array(
                    "value" => "Test 1 (bis)",
                    "displayValue" => "Test 1 (ter)"
                )
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_doubles",
                function ($info, $oa)
                {
                    if ($info) {
                        
                        if ($oa->id === "tst_doubles") {
                            foreach ($info as & $oneInfo) {
                                $oneInfo->value+= 10;
                            }
                        }
                    }
                    return $info;
                }
                ,
                array(
                    "value" => array(
                        56.67 + 10,
                        88.0 + 10,
                        3.1415926535 + 10
                    ) ,
                    "displayValue" => array(
                        "56,67",
                        "88",
                        "3,1415926535"
                    )
                )
            )
        );
    }
    public function dataDatePropertyRenderFormatCollection()
    {
        return array(
            array(
                "TST_FMTCOL1",
                "adate",
                \DateAttributeValue::frenchStyle,
                '/^(\d\d)\/(\d\d)\/(\d\d\d\d)\s?(\d\d)?:?(\d\d)?:?(\d\d)?/'
            ) ,
            array(
                "TST_FMTCOL1",
                "adate",
                \DateAttributeValue::isoWTStyle,
                '/^(\d\d\d\d)-(\d\d)-(\d\d)\s?(\d\d)?:?(\d\d)?:?(\d\d)?/'
            ) ,
            array(
                "TST_FMTCOL1",
                "adate",
                \DateAttributeValue::isoStyle,
                '/^(\d\d\d\d)-(\d\d)-(\d\d)T?(\d\d)?:?(\d\d)?:?(\d\d)?/'
            ) ,
            array(
                "TST_FMTCOL2",
                "cdate",
                \DateAttributeValue::frenchStyle,
                '/^(\d\d)\/(\d\d)\/(\d\d\d\d)\s?(\d\d)?:?(\d\d)?:?(\d\d)?/'
            ) ,
            array(
                "TST_FMTCOL2",
                "cdate",
                \DateAttributeValue::isoWTStyle,
                '/^(\d\d\d\d)-(\d\d)-(\d\d)\s?(\d\d)?:?(\d\d)?:?(\d\d)?/'
            ) ,
            array(
                "TST_FMTCOL2",
                "cdate",
                \DateAttributeValue::isoStyle,
                '/^(\d\d\d\d)-(\d\d)-(\d\d)T?(\d\d)?:?(\d\d)?:?(\d\d)?/'
            ) ,
            array(
                "TST_FMTCOL3",
                "revdate",
                \DateAttributeValue::frenchStyle,
                '/^(\d\d)\/(\d\d)\/(\d\d\d\d)\s?(\d\d)?:?(\d\d)?:?(\d\d)?/'
            ) ,
            array(
                "TST_FMTCOL3",
                "revdate",
                \DateAttributeValue::isoWTStyle,
                '/^(\d\d\d\d)-(\d\d)-(\d\d)\s?(\d\d)?:?(\d\d)?:?(\d\d)?/'
            ) ,
            array(
                "TST_FMTCOL3",
                "revdate",
                \DateAttributeValue::isoStyle,
                '/^(\d\d\d\d)-(\d\d)-(\d\d)T?(\d\d)?:?(\d\d)?:?(\d\d)?/'
            )
        );
    }
    
    public function dataStateRenderFormatCollection()
    {
        
        return array(
            array(
                "TST_FMTCOL1",
                "E1",
                "#7DFF63",
                "Activity E1",
                "Activity E1"
            ) ,
            array(
                "TST_FMTCOL2",
                "E2",
                "#8CFFDD",
                "Activity E2",
                "Activity E2"
            ) ,
            
            array(
                "TST_FMTCOL3",
                "E3",
                "#BC8FFF",
                "",
                "E3"
            )
        );
    }
    
    public function dataRenderFormatCollection()
    {
        
        return array(
            array(
                "TST_FMTCOL1",
                "tst_title",
                array(
                    "value" => "Test 1",
                    "displayValue" => "Test 1"
                )
            ) ,
            array(
                "TST_FMTCOL1",
                "tst_enum",
                array(
                    "value" => "1",
                    "displayValue" => "Un"
                )
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_double",
                array(
                    "value" => "23.4567",
                    "displayValue" => "23,46"
                ) ,
            ) ,
            array(
                "TST_FMTCOL3",
                "tst_int",
                null
            ) ,
            array(
                "TST_FMTCOL3",
                "tst_double",
                null
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_rellatest",
                array(
                    "displayValue" => "Test 1",
                    "familyRelation" => "TST_FMTCOL"
                ) ,
                array(
                    "value" => "^[0-9]+$",
                    "url" => "latest=Y"
                )
            ) ,
            
            array(
                "TST_FMTCOL4",
                "tst_rellatest",
                array(
                    "displayValue" => "Test 3",
                    "familyRelation" => "TST_FMTCOL"
                ) ,
                array(
                    "value" => "^[0-9]+$",
                    "url" => "latest=Y"
                )
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_title",
                array(
                    "value" => "Test 2",
                    "displayValue" => "Test 2"
                )
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_color",
                array(
                    "value" => "#52D7FF",
                    "displayValue" => "#52D7FF"
                )
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_enum",
                array(
                    "value" => "2",
                    "displayValue" => "Deux"
                )
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_time",
                array(
                    "value" => "12:20:00"
                )
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_text",
                array(
                    "value" => "Cassoulet",
                    "displayValue" => "before Cassoulet"
                )
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_int",
                array(
                    "value" => "101",
                    "displayValue" => "101"
                )
            ) ,
            array(
                "TST_FMTCOL1",
                "tst_int",
                array(
                    "value" => "0",
                    "displayValue" => "0"
                )
            ) ,
            array(
                "TST_FMTCOL1",
                "tst_double",
                array(
                    "value" => "0",
                    "displayValue" => "0,00"
                )
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_ts",
                array(
                    "value" => "2012-06-13 11:27:00"
                )
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_date",
                array(
                    "value" => "2012-06-13"
                )
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_enums",
                array(
                    "value" => array(
                        1,
                        2,
                        3
                    ) ,
                    "displayValue" => array(
                        "Un",
                        "Deux",
                        "Trois"
                    )
                )
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_doubles",
                array(
                    "value" => array(
                        56.67,
                        88.0,
                        3.1415926535
                    ) ,
                    "displayValue" => array(
                        "56,67",
                        "88",
                        "3,1415926535"
                    )
                )
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_ints",
                array(
                    "value" => array(
                        0,
                        12,
                        null
                    ) ,
                    "displayValue" => array(
                        "0",
                        "12",
                        ""
                    )
                )
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_relmuls",
                array(
                    "displayValue" => array(
                        array(
                            "Test 1"
                        ) ,
                        array(
                            "Test 1"
                        ) ,
                        array(
                            "Test 1"
                        )
                    ) ,
                )
            ) ,
            
            array(
                "TST_FMTCOL3",
                "tst_relmuls",
                array(
                    "displayValue" => array(
                        array(
                            "Test 1"
                        ) ,
                        array(
                            "Test 1",
                            "Test 2"
                        ) ,
                        array(
                            "Test 2",
                            "Test 1"
                        ) ,
                        array()
                    ) ,
                )
            ) ,
            array(
                "TST_FMTCOL2",
                "tst_file",
                array(
                    "size" => 5,
                    "mime" => "text/plain",
                    "fileName" => "Test.txt"
                )
            ) ,
            array(
                "TST_FMTCOL5",
                "tst_rellatests",
                array(
                    "url" => array(
                        ""
                    ) ,
                    "icon" => array(
                        ""
                    ) ,
                    "value" => array(
                        ""
                    ) ,
                    "displayValue" => array(
                        null
                    )
                )
            )
        );
    }
    
    public function dataRenderProfilRelationFormatCollection()
    {
        return array(
            array(
                "tstLoginFmtU1",
                "TST_FMTCOL1",
                "tst_title",
                array(
                    "value" => "Test 1",
                    "displayValue" => "Test 1"
                )
            ) ,
            array(
                "tstLoginFmtU1",
                "TST_FMTCOL2",
                "tst_rellatest",
                array(
                    "displayValue" => "Test 1",
                    "familyRelation" => "TST_FMTCOL"
                ) ,
                array(
                    "value" => "^[0-9]+$",
                    "url" => "latest=Y"
                )
            ) ,
            
            array(
                "tstLoginFmtU1",
                "TST_FMTCOL4",
                "tst_rellatest",
                array(
                    "displayValue" => "Test 3",
                    "familyRelation" => "TST_FMTCOL"
                ) ,
                array(
                    "value" => "^[0-9]+$",
                    "url" => "latest=Y"
                )
            ) ,
            
            array(
                "tstLoginFmtU2",
                "TST_FMTCOL4",
                "tst_rellatest",
                array(
                    "displayValue" => "no grant",
                    "familyRelation" => "TST_FMTCOL"
                ) ,
                array(
                    "value" => "^[0-9]+$",
                    "url" => ""
                )
            ) ,
            
            array(
                "tstLoginFmtU1",
                "TST_FMTCOL2",
                "tst_relmuls",
                array(
                    "displayValue" => array(
                        array(
                            "Test 1"
                        ) ,
                        array(
                            "Test 1"
                        ) ,
                        array(
                            "Test 1"
                        )
                    ) ,
                )
            ) ,
            
            array(
                "tstLoginFmtU2",
                "TST_FMTCOL4",
                "tst_relmuls",
                array(
                    "displayValue" => array(
                        array(
                            "Test 1"
                        ) ,
                        array(
                            "Test 1",
                            "Test 2",
                            "no grant"
                        ) ,
                        array(
                            "Test 2",
                            "Test 1"
                        ) ,
                        array()
                    ) ,
                )
            ) ,
            
            array(
                "tstLoginFmtU1",
                "TST_FMTCOL3",
                "tst_relmuls",
                array(
                    "displayValue" => array(
                        array(
                            "Test 1"
                        ) ,
                        array(
                            "Test 1",
                            "Test 2"
                        ) ,
                        array(
                            "Test 2",
                            "Test 1"
                        ) ,
                        array()
                    ) ,
                )
            )
        );
    }
}
?>
