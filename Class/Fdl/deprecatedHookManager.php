<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
        "postModify" => "postStore",
        "specRefresh" => "preRefresh",
        "postCopy" => "postDuplicate",
        "preCopy" => "preDuplicate",
        "postRevive" => "postUndelete",
        "preRevive" => "preUndelete"
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
            $dh[$k] = strtolower($v);
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
            if (strtolower($dName) == strtolower($deprecatedName)) return $nName;
        }
        return '';
    }
    
    protected function getDeprecatedHookName($newName)
    {
        foreach ($this->deprecatedHooks as $dName => $nName) {
            if (strtolower($nName) == strtolower($newName)) return $dName;
        }
        return '';
    }
    public function generateCompatibleMethods()
    {
        $alias = '';
        $dh = $this->getDeprecatedHooks();
        foreach ($dh as $dHook) {
            $nHook = $this->getNewHookName($dHook);
            $alias.= "\n/**\n*generated alias : new method name\n*/";
            $alias.= sprintf("*@deprecated declare %s instead\n", $nHook);
            $alias.= sprintf('public function %s() {self::%s();}', $nHook, $dHook);
            $alias.= "\n";
        }
        
        $nh = $this->getNewHooks();
        foreach ($nh as $nHook) {
            $alias.= "\n/**\n*generated alias : old compatibility\n";
            $alias.= sprintf("*@deprecated alias for %s\n", $nHook);
            $alias.= sprintf("*/\n");
            $alias.= sprintf('public function %s() {self::%s();}', $this->getDeprecatedHookName($nHook) , $nHook);
            $alias.= "\n";
        }
        return $alias;
    }
}
