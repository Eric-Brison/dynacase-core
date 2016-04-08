<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Created by JetBrains PhpStorm.
 * User: eric
 * Date: 14/01/13
 * Time: 08:48
 * To change this template use File | Settings | File Templates.
 */
class deprecatedHookManager
{
    public $deprecatedHooks = array(
        "postModify" => array(
            "newName" => "postStore",
            "call" => '',
            "declare" => ''
        ) ,
        "specRefresh" => array(
            "newName" => "preRefresh",
            "call" => '',
            "declare" => ''
        ) ,
        "postCopy" => array(
            "newName" => "postDuplicate",
            "declare" => '&$copyfrom',
            "call" => '$copyfrom'
        ) ,
        "preCopy" => array(
            "newName" => "preDuplicate",
            "call" => '$copyfrom',
            "declare" => '&$copyfrom'
        ) ,
        "postRevive" => array(
            "newName" => "postUndelete",
            "call" => '',
            "declare" => ''
        ) ,
        "preRevive" => array(
            "newName" => "preUndelete",
            "call" => '',
            "declare" => ''
        ) ,
        "getSpecTitle" => array(
            "newName" => "getCustomTitle",
            "call" => '',
            "declare" => ''
        ) ,
        "postInsertDoc" => array(
            "newName" => "postInsertDocument",
            "call" => '$docid, $multiple',
            "declare" => '$docid, $multiple = false'
        ) ,
        "preInsertDoc" => array(
            "newName" => "preInsertDocument",
            "call" => '$docid, $multiple',
            "declare" => '$docid, $multiple = false'
        ) ,
        "postUnlinkDoc" => array(
            "newName" => "postRemoveDocument",
            "call" => '$docid, $multiple',
            "declare" => '$docid, $multiple = false'
        ) ,
        "preUnlinkDoc" => array(
            "newName" => "preRemoveDocument",
            "call" => '$docid, $multiple',
            "declare" => '$docid, $multiple = false'
        ) ,
        "postMInsertDoc" => array(
            "newName" => "postInsertMultipleDocuments",
            "call" => '$tdocid',
            "declare" => '$tdocid'
        )
    );
    private $content = '';
    private $methods = array();
    
    public function inspectContent($phpContent)
    {
        $this->methods = array();
        $this->content = $phpContent;
    }
    
    private function extractMethods()
    {
        if (count($this->methods) > 0) return;
        $tokens = token_get_all($this->content);
        $funcHunt = false;
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if ($token[0] === T_FUNCTION) {
                    $funcHunt = true;
                    continue;
                }
                if ($funcHunt && $token[0] === T_STRING) {
                    $this->methods[] = strtolower($token[1]);
                    $funcHunt = false;
                }
            } else {
                if ($token === ';' || $token === '{') {
                    
                    $funcHunt = false;
                }
            }
        }
        $this->testDoubleDeclaration();
    }
    
    private function testDoubleDeclaration()
    {
        foreach ($this->deprecatedHooks as $dName => $info) {
            $nTestingName = strtolower($info["newName"]);
            $dTestingName = strtolower($dName);
            if (in_array($nTestingName, $this->methods) && in_array($dTestingName, $this->methods)) {
                throw new \Dcp\Exception("MTHD0003", $dName, $info["newName"]);
            }
        }
    }
    protected function getDeprecatedHookList()
    {
        $dh = array_keys($this->deprecatedHooks);
        foreach ($dh as $k => $v) {
            $dh[$k] = strtolower($v);
        }
        return $dh;
    }
    
    protected function getNewHookList()
    {
        $dh = array_values($this->deprecatedHooks);
        foreach ($dh as $k => $v) {
            $dh[$k] = strtolower($v["newName"]);
        }
        return $dh;
    }
    
    public function getDeprecatedHooks()
    {
        $this->extractMethods();
        return array_intersect($this->getDeprecatedHookList() , $this->methods);
    }
    
    public function getNewHooks()
    {
        $this->extractMethods();
        return array_intersect($this->getNewHookList() , $this->methods);
    }
    
    protected function getNewHookName($deprecatedName)
    {
        foreach ($this->deprecatedHooks as $dName => $nName) {
            if (strtolower($dName) == strtolower($deprecatedName)) return $nName["newName"];
        }
        return '';
    }
    
    protected function getArgCallHook($deprecatedName)
    {
        foreach ($this->deprecatedHooks as $dName => $nName) {
            if (strtolower($dName) == strtolower($deprecatedName)) return $nName["call"];
        }
        return '';
    }
    
    protected function getArgDeclareHook($deprecatedName)
    {
        foreach ($this->deprecatedHooks as $dName => $nName) {
            if (strtolower($dName) == strtolower($deprecatedName)) return $nName["declare"];
        }
        return '';
    }
    protected function getDeprecatedHookName($newName)
    {
        foreach ($this->deprecatedHooks as $dName => $nName) {
            if (strtolower($nName["newName"]) == strtolower($newName)) return $dName;
        }
        return '';
    }
    
    protected function getOriginalName($name)
    {
        foreach ($this->deprecatedHooks as $dName => $nName) {
            if (strtolower($nName["newName"]) == strtolower($name)) return $nName["newName"];
            if (strtolower($dName) == strtolower($name)) return $dName;
        }
        return $name;
    }
    public function generateCompatibleMethods()
    {
        $alias = '';
        $dh = $this->getDeprecatedHooks();
        foreach ($dh as $dHook) {
            $nHook = $this->getNewHookName($dHook);
            $alias.= "\n/**\n*generated alias : new method name\n";
            $alias.= sprintf("*@deprecated declare %s instead\n", $nHook);
            $alias.= sprintf("*/\n");
            $alias.= sprintf('public function %s(%s) {deprecatedFunction("hook %s");return self::%s(%s);}', $this->getOriginalName($nHook) , $this->getArgDeclareHook($dHook) , $this->getOriginalName($dHook) , $this->getOriginalName($dHook) , $this->getArgCallHook($dHook));
            $alias.= "\n";
        }
        
        $nh = $this->getNewHooks();
        foreach ($nh as $nHook) {
            $dHook = $this->getDeprecatedHookName($nHook);
            $alias.= "\n/**\n*generated alias : old compatibility\n";
            $alias.= sprintf("*@deprecated alias for %s\n", $nHook);
            $alias.= sprintf("*/\n");
            $alias.= sprintf('public function %s(%s) {deprecatedFunction("hook %s");return self::%s(%s);}', $dHook, $this->getArgDeclareHook($dHook) , $this->getOriginalName($dHook) , $this->getOriginalName($nHook) , $this->getArgCallHook($dHook));
            $alias.= "\n";
        }
        return $alias;
    }
}
