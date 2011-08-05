<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Workflow Object Definition
 *
 * @author Anakeen 2009
 * @version $Id: $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package API
 */
/**
 */
include_once ("DATA/Class.Document.php");
/**
 * Workflow Class
 *
 */
Class Fdl_Workflow extends Fdl_Document
{
    /**
     * return properties, values and attributes definition
     */
    function getDocument($onlyvalues = false, $completeprop = true)
    {
        $out = parent::getDocument($onlyvalues, $completeprop);
        $out["workflow"] = $this->getWorflowInformations();
        
        return $out;
    }
    
    function getWorflowInformations()
    {
        if ($this->doc->doctype != 'W') return null;
        $states = $this->doc->getStates();
        foreach ($states as $v) {
            $out["states"][$v] = array(
                "key" => $v,
                "activity" => $this->doc->getActivity($v) ,
                "label" => _($v)
            );
        }
        
        foreach ($this->doc->cycle as $v) {
            $out["transitions"][] = array(
                "start" => $v["e1"],
                "finish" => $v["e2"],
                "transitionType" => $v["t"]
            );
        }
        foreach ($this->doc->transitions as $k => $v) {
            $out["transitionTypes"][$k] = array(
                "key" => $k,
                "label" => _($k) ,
                "ask" => $v["ask"],
                "preMethod" => $v["m1"],
                "postMethod" => $v["m2"],
                "noComment" => $v["nr"] ? true : false
            );
        }
        
        return $out;
    }
    
    function addTransition($start, $finish, $transitionType)
    {
        return $this->modifyTransition("add", $start, $finish, $transitionType);
    }
    
    function removeTransition($start, $finish)
    {
        return $this->modifyTransition("remove", $start, $finish);
    }
    
    private function modifyTransition($addorrem, $start, $finish, $transitionType = '')
    {
        if ($this->doc) {
            if ($start && $finish && ($addorrem == "remove" || $transitionType)) {
                if ($this->doc->doctype != 'W') return null;
                $err = $this->doc->lock(true);
                if ($err) {
                    $this->setError($err);
                    return null;
                }
                
                $cycles = $this->doc->cycle;
                foreach ($cycles as $k => $v) {
                    if ($v["e1"] == $start && $v["e2"] == $finish) unset($cycles[$k]);
                }
                if ($addorrem == "add") {
                    $cycles[] = array(
                        "e1" => $start,
                        "e2" => $finish,
                        "t" => $transitionType
                    );
                }
                $fam = $this->doc->getFamDoc();
                $classname = $fam->classname;
                $file = sprintf("%s/FDL/Class.%s.php", DEFAULT_PUBDIR, $classname);
                if (file_exists($file)) {
                    if (!is_writable($file)) {
                        $this->doc->unlock(true);
                        $this->setError(sprintf(_("workflow file %s is not writable") , $file));
                        return null;
                    }
                    $na = "array(";
                    foreach ($cycles as $k => $v) {
                        $na.= sprintf('array("e1"=>"%s", "e2"=>"%s", "t"=>"%s"),' . "\n", $v["e1"], $v["e2"], $v["t"]);
                    }
                    $na = substr($na, 0, -2) . ")"; //delete end comma and cr
                    $content = file_get_contents($file);
                    $nc = preg_replace('/\$cycle\s*=([^;]*);/i', "\$cycle=$na;", $content);
                    
                    file_put_contents($file, $nc);
                    $this->doc->cycle = $cycles;
                    //print "<pre>".htmlentities($nc)."</pre>";
                    
                }
                
                $err = $this->doc->unlock(true);
            } else {
                $this->setError(_("modifyTransition: missing parameter"));
                return null;
            }
        }
    }
    
    function addTraduction($lang, $key, $text)
    {
        if ($key && $text && $lang) {
            $mo = sprintf('msgid ""
msgstr ""
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"

msgid "%s"
msgstr "%s"', $key, str_replace('"', '\"', $text));
            
            $mofile = sprintf("%s/locale/%s/LC_MESSAGES/w%s.po", DEFAULT_PUBDIR, $lang, $this->doc->fromid);
            file_put_contents($mofile, $mo);
            
            if (file_exists(sprintf("%s/locale/%s/LC_MESSAGES/0w.mo", DEFAULT_PUBDIR, $lang))) {
                
                $cmd = sprintf('cd %s/locale/%s/LC_MESSAGES;msgunfmt 0w.mo > _w.po && msgcat --use-first  %s _w.po > __w.po && msgfmt __w.po -o 0w.mo && %s/whattext', DEFAULT_PUBDIR, $lang, $mofile, DEFAULT_PUBDIR);
            } else {
                $cmd = sprintf('cd %s/locale/%s/LC_MESSAGES;msgfmt %s  -o 0w.mo && %s/whattext', DEFAULT_PUBDIR, $lang, $mofile, DEFAULT_PUBDIR);
            }
            //print $cmd;
            $log = exec($cmd, $out, $ret);
            //    print "<hr>$log : $ret";
            return ($ret == 0);
        }
    }
    
    function addState($key, $label = '', $activity = '')
    {
        // need control not exists
        $this->alterState("add", $key, $label, $activity);
    }
    
    function modifyState($key, $label = '', $activity = '')
    {
        // need control already exists
        $this->alterState("modify", $key, $label, $activity);
    }
    function removeState($key)
    {
        // need control already exists
        $this->alterState("remove", $key);
    }
    
    private function alterState($fn, $key, $label = '', $activity = '')
    {
        if ($this->doc) {
            if ($key) {
                if ($this->doc->doctype != 'W') return null;
                $err = $this->doc->lock(true);
                if ($err) {
                    $this->setError($err);
                    return null;
                }
                
                $states = $this->doc->getStates();
                if ($fn == "remove") {
                    unset($states[$key]);
                    $cycles = $this->doc->cycle; // delete also transition which use these transition type
                    foreach ($cycles as $k => $v) {
                        if (($v["e1"] == $key) || ($v["e2"] == $key)) unset($cycles[$k]);
                    }
                    $ncy = "array(";
                    foreach ($cycles as $k => $v) {
                        $ncy.= sprintf('array("e1"=>"%s", "e2"=>"%s", "t"=>"%s"),' . "\n", $v["e1"], $v["e2"], $v["t"]);
                    }
                    $ncy = substr($ncy, 0, -2) . ")"; //delete end comma and cr
                    $this->doc->cycle = $cycles;
                } else {
                    $states[$key] = $key;
                }
                $fam = $this->doc->getFamDoc();
                $classname = $fam->classname;
                $file = sprintf("%s/FDL/Class.%s.php", DEFAULT_PUBDIR, $classname);
                if (file_exists($file)) {
                    if (!is_writable($file)) {
                        $this->doc->unlock(true);
                        $this->setError(sprintf(_("workflow file %s is not writable") , $file));
                        return null;
                    }
                    $na = "array(";;
                    foreach ($states as $k => $v) {
                        $na.= sprintf('"%s"=>"%s",' . "\n", $k, $v);
                    }
                    $na = substr($na, 0, -2) . ")"; //delete end comma and cr
                    $content = file_get_contents($file);
                    $nc = preg_replace('/public \$states\s*=([^;]*);/i', "public \$states=$na;", $content, 1, $count);
                    if ($count == 0) {
                        $nc = preg_replace("/(extends WDoc {\s*)/i", "extends WDoc {\n\tpublic \$states=$na;\n", $content, 1, $count);
                    }
                    
                    if ($ncy) $nc = preg_replace('/\$cycle\s*=([^;]*);/i', "\$cycle=$ncy;", $nc);
                    if ($label) {
                        $this->addTraduction('fr', $key, $label);
                    }
                    
                    if ($activity) {
                        $na = "array(";
                        $na.= sprintf('"%s"=>"%s",' . "\n", $key, $activity);
                        foreach ($states as $k => $v) {
                            $actid = $this->doc->attrPrefix . "_ACTIVITYLABEL" . $k;
                            $actv = $this->getValue($actid);
                            if ($k != $key) {
                                if ($actv) {
                                    $na.= sprintf('"%s"=>"%s",' . "\n", $k, $actv);
                                }
                            }
                        }
                        $na = substr($na, 0, -2) . ")"; //delete end comma and cr
                        $nc = preg_replace('/public \$stateactivity\s*=([^;]*);/i', "public \$stateactivity=$na;", $nc, 1, $count);
                        if ($count == 0) {
                            $nc = preg_replace("/(extends WDoc {\s*)/i", "extends WDoc {\n\tpublic \$stateactivity=$na;\n", $nc, 1, $count);
                        }
                        $this->doc->stateactivity[$key] = $activity;
                        $this->doc->postModify();
                    }
                    
                    file_put_contents($file, $nc);
                    $this->doc->states = $states;
                    $wsh = getWshCmd();
                    $cmd = $wsh . " --api=fdl_adoc --docid=" . intval($this->doc->fromid);
                    $log = exec($cmd, $out, $ret);
                    if ($err) $this->setError($err);
                    //	  print "<pre>".htmlentities($nc)."</pre>";
                    
                }
                
                $err = $this->doc->unlock(true);
            } else {
                $this->setError(_("addState: missing parameter"));
                return null;
            }
        }
    }
    
    function addTransitiontype($key, $label = '', $ask = null, $preMethod = null, $postmethod = null, $noComment = false, $autoNext = null)
    {
        return $this->alterTransitiontype("add", $key, $label, $ask, $preMethod, $postmethod, $noComment, $autoNext);
    }
    
    function modifyTransitiontype($key, $label = '', $ask = null, $preMethod = null, $postmethod = null, $noComment = false, $autoNext = null)
    {
        return $this->alterTransitiontype("modify", $key, $label, $ask, $preMethod, $postmethod, $noComment, $autoNext);
    }
    function removeTransitiontype($key)
    {
        return $this->alterTransitiontype("remove", $key);
    }
    
    private function alterTransitiontype($addorrem, $key, $label = '', $ask = null, $preMethod = null, $postmethod = null, $noComment = null, $autoNext = null)
    {
        if ($this->doc) {
            if (true) {
                if ($this->doc->doctype != 'W') return null;
                $err = $this->doc->lock(true);
                if ($err) {
                    $this->setError($err);
                    return null;
                }
                
                $transitions = $this->doc->transitions;
                if ($addorrem == "remove") {
                    unset($transitions[$key]);
                    $cycles = $this->doc->cycle; // delete also transition which use these transition type
                    foreach ($cycles as $k => $v) {
                        if ($v["t"] == $key) unset($cycles[$k]);
                    }
                    $ncy = "array(";
                    foreach ($cycles as $k => $v) {
                        $ncy.= sprintf('array("e1"=>"%s", "e2"=>"%s", "t"=>"%s"),' . "\n", $v["e1"], $v["e2"], $v["t"]);
                    }
                    $ncy = substr($ncy, 0, -2) . ")"; //delete end comma and cr
                    $this->doc->cycle = $cycles;
                } else {
                    if ($addorrem == "modify") {
                        $tkey = $transitions[$key]; // conserve last values is not new set
                        if ($ask === null) $ask = $tkey["ask"];
                        if ($preMethod === null) $preMethod = $tkey["m1"];
                        if ($postMethod === null) $postMethod = $tkey["m2"];
                        if ($noComment === null) $noComment = $tkey["nr"];
                    }
                    $transitions[$key] = array(
                        "ask" => $ask,
                        "m1" => $preMethod,
                        "m2" => $postmethod,
                        "nr" => $noComment
                    );
                }
                if ($label) {
                    $this->addTraduction('fr', $key, $label);
                }
                $fam = $this->doc->getFamDoc();
                $classname = $fam->classname;
                $file = sprintf("%s/FDL/Class.%s.php", DEFAULT_PUBDIR, $classname);
                if (file_exists($file)) {
                    if (!is_writable($file)) {
                        $this->doc->unlock(true);
                        $this->setError(sprintf(_("workflow file %s is not writable") , $file));
                        return null;
                    }
                    $na = "array(";
                    foreach ($transitions as $k => $v) {
                        if (is_array($v["ask"])) {
                            $sax = $v["ask"];
                            $v["ask"] = 'array(';
                            foreach ($sax as $ax) {
                                $v["ask"].= "'" . $ax . "',";
                            }
                            $v["ask"] = substr($v["ask"], 0, -1) . ")"; //delete end comma
                            
                        } else $v["ask"] = "null";
                        $na.= sprintf('"%s"=>array("m1"=>"%s", "m2"=>"%s", "ask"=>%s, "nr"=>%s),' . "\n", $k, $v["m1"], $v["m2"], $v["ask"], $v["nr"] ? "true" : "false");
                    }
                    $na = substr($na, 0, -2) . ")"; //delete end comma and cr
                    $content = file_get_contents($file);
                    $nc = preg_replace('/\$transitions\s*=([^;]*);/i', "\$transitions=$na;", $content);
                    if ($ncy) $nc = preg_replace('/\$cycle\s*=([^;]*);/i', "\$cycle=$ncy;", $nc);
                    
                    file_put_contents($file, $nc);
                    $this->doc->transitions = $transitions;
                    $wsh = getWshCmd();
                    $cmd = $wsh . " --api=fdl_adoc --docid=" . intval($this->doc->fromid);
                    $log = exec($cmd, $out, $ret);
                    if ($err) $this->setError($err);
                    //print "<pre>".htmlentities($nc)."</pre>";
                    
                }
                
                $err = $this->doc->unlock(true);
            } else {
                $this->setError(_("alterTransitiontype: missing parameter"));
                return null;
            }
        }
    }
}
?>