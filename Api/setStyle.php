<?php
/*
 * @author Anakeen
 * @package FDL
*/

$usage = new ApiUsage();
$usage->setDefinitionText("apply given style - if no style is set then update current style");
$styFilePath = $usage->addOptionalParameter("style", "path to style file");
if (!$styFilePath) {
    /**
     * @var Action $action
     */
    $defautStyle = $action->getParam("STYLE");
    $styFilePath = sprintf("STYLE/%s/%s.sty", $defautStyle, $defautStyle);
}
$verbose = ('yes' === $usage->addOptionalParameter('verbose', 'verbose', array(
    'yes',
    'no'
) , 'no'));
$usage->verify();

chdir(DEFAULT_PUBDIR);

class styleManager
{
    
    const CUSTOM_RULES_DIR_NAME = "rules.d";
    const DEFAULT_CSS_PARSER_DEPLOY_CLASS = '\Dcp\Style\dcpCssConcatParser';
    const DEFAULT_CSS_PARSER_RUNTIME_CLASS = null;
    const GLOBAL_RULES_DIR_NAME = "global-rules.d";
    
    protected $verbose = false;
    protected $logIndent = 0;
    protected $styleConfig = array();
    /** @var Action $action */
    protected $action = null;
    
    protected function log($msg)
    {
        if ($this->verbose) {
            print str_repeat("\t", $this->logIndent) . " -- " . $msg . PHP_EOL;
        }
    }
    
    public function __construct(Action $action)
    {
        $this->action = $action;
    }
    
    public function loadStyle($styFilePath)
    {
        
        $styleDefinition = $this->loadStyleDefinition($styFilePath);
        $this->computeStyleColors($styleDefinition);
    }
    
    protected function loadStyleDefinition($styFilePath)
    {
        $styFilePath = DEFAULT_PUBDIR . DIRECTORY_SEPARATOR . $styFilePath;
        $this->log("load style definition from $styFilePath");
        if (!is_readable($styFilePath)) {
            if (!file_exists($styFilePath)) {
                $msg = "sty file [$styFilePath] does not exists";
            } else {
                $msg = "sty file [$styFilePath] is not readable";
            }
            throw new \Dcp\Style\Exception("STY0001", $msg);
        }
        /** @noinspection PhpIncludeInspection */
        require $styFilePath;
        
        $styleDefinition = array(
            "sty_desc" => empty($sty_desc) ? array() : $sty_desc,
            "sty_const" => empty($sty_const) ? array() : $sty_const,
            "sty_colors" => empty($sty_colors) ? array() : $sty_colors,
            "sty_local" => empty($sty_local) ? array() : $sty_local,
            "sty_rules" => empty($sty_rules) ? array() : $sty_rules
        );
        
        if (!isset($styleDefinition['sty_desc']) || !isset($styleDefinition['sty_desc']['name'])) {
            throw new \Dcp\Style\Exception("STY0002", "Style definition does not contains style name");
        }
        // init with parent style
        if (!empty($sty_inherit)) {
            $this->log("using parent style file: $sty_inherit");
            $this->logIndent+= 1;
            $parentStyleDefinition = $this->loadStyleDefinition($sty_inherit);
            $styleDefinition = array_replace_recursive($parentStyleDefinition, $styleDefinition);
            $this->logIndent-= 1;
        }
        //load rules (rules.d)
        $customRulesDirPath = dirname($styFilePath) . DIRECTORY_SEPARATOR . self::CUSTOM_RULES_DIR_NAME;
        if (is_dir($customRulesDirPath) && is_readable($customRulesDirPath)) {
            $customRules = & $styleDefinition['sty_rules'];
            $customRulesFiles = scandir($customRulesDirPath);
            if (false !== $customRulesFiles) {
                foreach ($customRulesFiles as $customRulesFile) {
                    if ($customRulesFile == '.' || $customRulesFile == '..') continue;
                    $customRules = array_replace_recursive($customRules, $this->loadCustomRulesFromFile($customRulesDirPath . DIRECTORY_SEPARATOR . $customRulesFile));
                }
            }
        }
        //load global rules (rules.d)
        $globalRulesDirPath = dirname(dirname($styFilePath)) . DIRECTORY_SEPARATOR . self::GLOBAL_RULES_DIR_NAME;
        if (is_dir($globalRulesDirPath) && is_readable($globalRulesDirPath)) {
            $globalRules = & $styleDefinition['sty_rules'];
            $globalRulesFiles = scandir($globalRulesDirPath);
            if (false !== $globalRulesFiles) {
                foreach ($globalRulesFiles as $globalRulesFile) {
                    if ($globalRulesFile == '.' || $globalRulesFile == '..') continue;
                    $globalRules = array_replace_recursive($globalRules, $this->loadCustomRulesFromFile($globalRulesDirPath . DIRECTORY_SEPARATOR . $globalRulesFile));
                }
            }
        }
        
        return $styleDefinition;
    }
    
    protected function loadCustomRulesFromFile($customRulesFilePath)
    {
        
        $this->log("load custom rules from $customRulesFilePath");
        if (!is_readable($customRulesFilePath)) {
            if (!file_exists($customRulesFilePath)) {
                $msg = "$customRulesFilePath does not exists";
            } else {
                $msg = "$customRulesFilePath is not readable";
            }
            throw new \Dcp\ApiUsage\Exception("FILE0011", $msg);
        }
        /** @noinspection PhpIncludeInspection */
        require $customRulesFilePath;
        return empty($sty_rules) ? array() : $sty_rules;
    }
    
    protected function computeStyleColors($styleDefinition)
    {
        $styleConfig = $styleDefinition;
        // compute colors
        
        /** @noinspection PhpIncludeInspection */
        require_once "Lib.Color.php";
        
        $computedColors = array();
        
        if (empty($styleConfig['sty_colors'])) {
            throw new \Dcp\Style\Exception("STY0002", "Style definition does not contains sty_colors");
        }
        
        $styleBaseColors = $styleConfig['sty_colors'];
        
        $darkStyle = false;
        $whiteHsl = array();
        if (isset($sty_const["COLOR_WHITE"])) {
            $whiteHsl = srgb2hsl($sty_const["COLOR_WHITE"]);
        }
        if (!empty($whiteHsl)) {
            $darkStyle = ($whiteHsl[2] < 0.5);
        }
        foreach ($styleBaseColors as $colorKey => $baseColor) {
            $baseColorHsl = srgb2hsl($baseColor);
            $baseHue = $baseColorHsl[0];
            $baseSaturation = $baseColorHsl[1];
            $baseLuminance = $baseColorHsl[2];
            if ($darkStyle) {
                $luminanceStep = (0 - $baseLuminance) / 10;
            } else {
                $luminanceStep = (1 - $baseLuminance) / 10;
            }
            $computedColors[$colorKey] = array();
            $currentStepLuminance = $baseColorHsl[2];
            for ($i = 0; $i < 10; $i++) {
                $currentStepColor = HSL2RGB($baseHue, $baseSaturation, $currentStepLuminance);
                $computedColors[$colorKey][$i] = $currentStepColor;
                $currentStepLuminance+= $luminanceStep;
            }
        }
        
        $styleConfig['computed_colors'] = $computedColors;
        
        $this->styleConfig = $styleConfig;
    }
    
    public function applyStyle()
    {
        $styleConfig = $this->styleConfig;
        
        if (empty($styleConfig['sty_desc']) || empty($styleConfig['sty_desc']['name'])) {
            throw new \Dcp\Style\Exception("STY0002", "Style definition does not contains name");
        }
        
        $param = new Param();
        
        $styleName = $styleConfig['sty_desc']['name'];
        $style = new Style('', $styleName);
        $style->description = empty($styleConfig['sty_desc']['description']) ? '' : $styleConfig['sty_desc']['description'];
        if (isset($styleConfig['sty_desc']['parsable'])) {
            print "\n[WARNING] use of parsable property on style is deprecated\n\n";
            $style->parsable = (('Y' === $styleConfig['sty_desc']['parsable']) ? 'Y' : 'N');
        }
        
        if (!$style->isAffected()) {
            $style->name = $styleName;
            $err = $style->Add();
            if ($err) {
                throw new \Dcp\Style\Exception("STY0003", "error when registering style");
            }
        }
        $err = $style->modify();
        if ($err) {
            throw new \Dcp\Style\Exception("STY0003", "error when modifying style");
        }
        // delete previous style parameters
        $this->log("delete previous style parameters");
        $query = new QueryDb("", "Param");
        $query->AddQuery(sprintf("type ~ '^%s'", Param::PARAM_STYLE)); //all of them, regardless of the style they come from
        $oldParamList = $query->Query();
        if (!empty($oldParamList)) {
            foreach ($oldParamList as $oldParam) {
                /** @var $oldParam Param */
                $oldParam->delete();
            }
        }
        
        $paramType = Param::PARAM_STYLE . $styleName;
        // register color params ($styleConfig['sty_computed_colors'])
        $this->log("register color params");
        $this->logIndent+= 1;
        foreach ($styleConfig['computed_colors'] as $colorClass => $colorList) {
            foreach ($colorList as $colorIndex => $color) {
                $paramName = "COLOR_{$colorClass}{$colorIndex}";
                // if value is a reference to another parameter
                $dynamicColorValue = ApplicationParameterManager::getScopedParameterValue($color);
                if (!empty($dynamicColorValue)) {
                    $this->log("dynamic value " . var_export($dynamicColorValue, true) . " set for $paramName ($color)");
                    $color = $dynamicColorValue;
                } else {
                    $this->log("static value " . var_export($color, true) . " set for $paramName ($color)");
                }
                $param->Set($paramName, $color, $paramType, 1);
                $this->action->parent->SetVolatileParam($paramName, $color); //add parameter in session cache
                
            }
        }
        $this->logIndent-= 1;
        // register other params ($styleConfig['sty_const'])
        $this->log("register other params");
        $this->logIndent+= 1;
        foreach ($styleConfig['sty_const'] as $paramName => $paramValue) {
            // if value is a reference to another parameter
            $dynamicParamValue = ApplicationParameterManager::getScopedParameterValue($paramValue);
            if (!empty($dynamicParamValue)) {
                $this->log("dynamic value " . var_export($dynamicParamValue, true) . " set for $paramName ($paramValue)");
                $paramValue = $dynamicParamValue;
            } else {
                $this->log("static value " . var_export($paramValue, true) . " set for $paramName ($paramValue)");
            }
            $param->Set($paramName, $paramValue, $paramType, 1);
            $this->action->parent->SetVolatileParam($paramName, $paramValue); //add parameter in session cache
            
        }
        $this->logIndent-= 1;
        // volatile register parsing params ($styleConfig['sty_local'])
        $this->log("declare volatile params");
        $this->logIndent+= 1;
        foreach ($styleConfig['sty_local'] as $paramName => $paramValue) {
            // if value is a reference to another parameter
            $dynamicParamValue = ApplicationParameterManager::getScopedParameterValue($paramValue);
            if (!empty($dynamicParamValue)) {
                $this->log("dynamic value " . var_export($dynamicParamValue, true) . " used for $paramName ($paramValue)");
                $paramValue = $dynamicParamValue;
            } else {
                $this->log("static value " . var_export($paramValue, true) . " used for $paramName ($paramValue)");
            }
            $this->action->parent->SetVolatileParam($paramName, $paramValue); //add parameter in session cache
            
        }
        $this->logIndent-= 1;
        // apply sty_rules
        $this->deployStyleFiles($styleConfig['sty_rules']);
        
        $style->setRules($styleConfig['sty_rules']);
        $err = $style->modify();
        if ($err) {
            throw new \Dcp\Style\Exception("STY0003", "error when modifying style");
        }
        ApplicationParameterManager::setCommonParameterValue("CORE", "STYLE", $styleName);
    }
    
    protected function deployStyleFiles(Array $rules)
    {
        $this->log("deploy style files");
        $this->logIndent+= 1;
        
        $filesDefinition = array();
        $cssRules = empty($rules['css']) ? array() : $rules['css'];
        $filesDefinition['css'] = $this->deployStyleCssFiles($cssRules, 'css');
        
        $this->logIndent-= 1;
        
        return $filesDefinition;
    }
    
    protected function deployStyleCssFiles(Array $cssRules, $targetDirName)
    {
        $this->log("deploy css files");
        $filesDefinition = array();
        
        $pubDir = DEFAULT_PUBDIR;
        $targetDir = $pubDir . DIRECTORY_SEPARATOR . $targetDirName;
        // clean previous files
        $this->deleteDirectory($targetDir);
        mkdir($targetDir);
        // deploy new css files
        $this->logIndent+= 1;
        foreach ($cssRules as $targetFile => $rule) {
            $this->log("processing rules for $targetFile");
            // check src file
            if (empty($rule['src'])) {
                throw new \Dcp\Style\Exception("STY0002", "rule for $targetFile does not contains src");
            }
            
            $src = $rule['src'];
            $destFile = $targetDirName . DIRECTORY_SEPARATOR . $targetFile;
            
            $deployParserClass = self::DEFAULT_CSS_PARSER_DEPLOY_CLASS;
            $deployParserOptions = array();
            if (!empty($rule['deploy_parser'])) {
                if (!empty($rule['deploy_parser']['className'])) {
                    $deployParserClass = $rule['deploy_parser']['className'];
                }
                if (!empty($rule['deploy_parser']['options'])) {
                    $deployParserOptions = $rule['deploy_parser']['options'];
                }
            }
            $this->log("parsing $targetFile using $deployParserClass");
            $classInterfaces = class_implements($deployParserClass, true);
            if (!isset($classInterfaces['Dcp\Style\ICssParser'])) {
                throw new \Dcp\Style\Exception("STY0006", "class $deployParserClass does not implements \\Dcp\\Style\\ICssParser");
            }
            $this->log(print_r($src, true));
            /** @var $parser \Dcp\Style\ICssParser */
            $parser = new $deployParserClass($src, $deployParserOptions, $this->styleConfig);
            $parser->gen($destFile);
        }
        $this->logIndent-= 1;
        
        return $filesDefinition;
    }
    
    protected function deleteDirectory($dir)
    {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir) || is_link($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!$this->deleteDirectory($dir . "/" . $item)) {
                chmod($dir . "/" . $item, 0777);
                if (!$this->deleteDirectory($dir . "/" . $item)) return false;
            };
        }
        
        return rmdir($dir);
    }
    
    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;
    }
}
/** @global $action Action */
$sm = new styleManager($action);
$sm->setVerbose($verbose);
$sm->loadStyle($styFilePath);
$sm->applyStyle();

