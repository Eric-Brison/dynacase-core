<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Mail template document
 *
 * @author Anakeen
 * @version $Id: Method.Timer.php,v 1.8 2009/01/16 12:53:10 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _TIMER extends Doc
{
    /*
     * @end-method-ignore
    */
    private $lineActions;
    /**
     * attach timer to a document
     * @param _TIMER &$timer the timer document
     * @param Doc &$origin the document which comes from the attachement
     * @param date $tododate special date to trigger the actions
     * @return string error - empty if no error -
     */
    function attachDocument(&$doc, &$origin, $tododate = null)
    {
        include_once ("FDL/Class.DocTimer.php");
        
        $dt = new DocTimer($this->dbaccess);
        $dt->timerid = $this->id;
        $dt->docid = $doc->initid;
        $dt->title = $doc->title;
        $dt->attachdate = $doc->getTimeDate(); // now
        $dt->level = 0;
        if ($origin) $dt->originid = $origin->id;
        $dt->fromid = $doc->fromid;
        
        $dates = $this->getTValue("tm_delay");
        $hours = $this->getTValue("tm_hdelay");
        
        if (((count($dates) == 0) || $dates[0] + $hours[0] == 0) && ($tododate == null)) {
            $err = sprintf(_("no delay specified in timer %s [%d]") , $this->title, $this->id);
        } else {
            
            $acts = $this->getPrevisions($dt->attachdate, false, 0, 1);
            if (count($acts) == 1) {
                $act = current($acts);
                $dt->actions = serialize($act["actions"]);
                
                $day = doubleval($dates[0]);
                $hour = doubleval($hours[0]);
                if ($tododate) $dt->tododate = $tododate;
                else $dt->tododate = $this->getTimeDate(24 * $day + $hour);
                $err = $dt->Add();
            } else $err = sprintf(_("no level 0 specified in timer %s [%d]") , $this->title, $this->id);
        }
        return $err;
    }
    /**
     * unattach timer to a document
     * @param _TIMER &$timer the timer document
     * @param Doc &$origin the document which comes from the attachement
     * @return string error - empty if no error -
     */
    function unattachAllDocument(&$doc, &$origin = null, &$c = 0)
    {
        include_once ("FDL/Class.DocTimer.php");
        
        $dt = new DocTimer($this->dbaccess);
        if ($origin) $err = $dt->unattachFromOrigin($doc->initid, $origin->initid, $c);
        else $err = $dt->unattachAll($doc->initid, $c);
        
        return $err;
    }
    /**
     * unattach timer to a document
     * @param _TIMER &$timer the timer document
     * @param Doc &$origin the document which comes from the attachement
     * @return string error - empty if no error -
     */
    function unattachDocument(&$doc)
    {
        include_once ("FDL/Class.DocTimer.php");
        
        $dt = new DocTimer($this->dbaccess);
        $err = $dt->unattachDocument($doc->initid, $this->id);
        
        return $err;
    }
    /**
     * get prevision for an activate timer
     * @param date $adate attach date
     * @param date $tododate todo date may be false if not an already attached timer
     * @param int $level from level
     * @param int $maxOccur slice level (since level+maxOccur)
     * @return array array of prevision
     */
    function getPrevisions($adate, $tododate = false, $level = 0, $maxOccur = 10)
    {
        $this->linearizeActions();
        
        $jdnow = StringDateToJD($this->getTimeDate());
        $jdattach = StringDateToJD($adate);
        $spentDelay = $jdnow - $jdattach;
        
        $first = true;
        $tprev = array();
        $jdstart = $jdattach; //$jdnow-$spentDelay;
        //compute jdstart first
        $max = min(($level + $maxOccur) , count($this->lineActions));
        for ($clevel = 0; $clevel < $level; $clevel++) {
            $prev[$clevel] = $this->lineActions[$clevel];
            if ($first && $tododate) { // add delta when timer is modify after attachement
                /*$jdtodo=StringDateToJD($tododate);
                $execdate=$jdstart+$prev[$clevel]["delay"];
                $delta=$jdtodo - $execdate;
                $first=false;
                $jdstart += $delta;*/
            }
            $ldelay = $prev[$clevel]["delay"];
            $jdstart+= $ldelay;
        }
        for ($clevel = $level; $clevel < $max; $clevel++) {
            $tprev[$clevel] = $this->lineActions[$clevel];
            if ($first && $tododate) { // add delta when timer is modify after attachement
                $jdtodo = StringDateToJD($tododate);
                $execdate = $jdstart + $tprev[$clevel]["delay"];
                $delta = $jdtodo - $execdate;
                $first = false;
                $jdstart+= $delta;
            }
            $ldelay = $tprev[$clevel]["delay"];
            //  print "$clevel)jdstart:$jdstart".jd2cal($jdstart)."[$ldelay] --".jd2cal($jdstart+$ldelay)."--\n";
            $tprev[$clevel]["execdate"] = jd2cal($jdstart + $ldelay);
            $tprev[$clevel]["execdelay"] = ($jdstart + $ldelay) - $jdnow;
            $jdstart+= $ldelay;
        }
        return ($tprev);
    }
    
    private function linearizeActions()
    {
        $this->lineActions = array();
        $tactions = $this->getAvalues("tm_t_config");
        $level = 0;
        foreach ($tactions as $k => $v) {
            $repeat = intval($v["tm_iteration"]);
            if ($repeat <= 0) $repeat = 1;
            
            for ($i = 0; $i < $repeat; $i++) {
                $this->lineActions[$level] = array(
                    "level" => $level,
                    "delay" => $v["tm_delay"] + ($v["tm_hdelay"] / 24) ,
                    "actions" => array(
                        "state" => $v["tm_state"],
                        "tmail" => $v["tm_tmail"],
                        "method" => $v["tm_method"]
                    )
                );
                $level++;
            }
        }
        ksort($this->lineActions);
    }
    /**
     * execute a level for a document
     * @param int $level level af action to execute
     * @param int $docid document to apply action
     * @return string error - empty if no error -
     */
    function executeLevel($level, $docid, &$msg = null, &$nextlevel = true)
    {
        $msg = '';
        $nextlevel = true;
        $doc = new_doc($this->dbaccess, $docid, true);
        if (!$doc->isAlive()) return sprintf(_("cannot execute : document %s is not found") , $docid);
        $acts = $this->getPrevisions($this->getTimeDate() , false, $level, 1);
        
        $gerr = "";
        $tmsg = array();
        if (count($acts) > 0) {
            foreach ($acts as $k => $v) {
                foreach ($v["actions"] as $ka => $va) {
                    if ($va) {
                        $err = "";
                        switch ($ka) {
                            case "tmail":
                                $tva = $this->_val2array(str_replace('<BR>', "\n", $va));
                                foreach ($tva as $idmail) {
                                    $tm = new_doc($this->dbaccess, $idmail);
                                    if ($tm->isAlive()) {
                                        $msg = sprintf(_("send mail with template %s [%d]") , $tm->title, $tm->id);
                                        $doc->addComment(sprintf(_("execute timer %s (level %d) : %s") , $this->title, $this->level, $msg));
                                        $err = $tm->sendDocument($doc);
                                        $tmsg[] = $msg;
                                    }
                                }
                                break;

                            case "state":
                                $msg = sprintf(_("change state to %s") , _($va));
                                $doc->addComment(sprintf(_("execute timer %s (level %d) : %s") , $this->title, $this->level, $msg));
                                $err = $doc->setState($va);
                                $tmsg[] = $msg;
                                break;

                            case "method":
                                $msg = sprintf(_("apply method %s") , $va);
                                $doc->addComment(sprintf(_("execute timer %s (level %d) : %s") , $this->title, $this->level, $msg));
                                $err = $doc->applyMethod($va);
                                $tmsg[] = $msg;
                                break;
                        }
                        
                        if ($err) {
                            $gerr.= "$err\n";
                            $doc->addComment(sprintf(_("execute timer %s (level %d) : %s") , $this->title, $this->level, $err) , HISTO_ERROR);
                        }
                    }
                }
            }
        } else {
            $nextlevel = false; // this is the end level
            
        }
        
        $msg = implode(".\n", $tmsg);
        return $gerr;
    }
    /**
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
     */
}
/*
 * @end-method-ignore
*/
?>