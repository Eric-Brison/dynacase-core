<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Timer document
 */
namespace Dcp\Core;
class Timer extends \Dcp\Family\Document
{
    
    private $lineActions;
    /**
     * attach timer to a document
     * @param \Doc &$doc the document where timer will be attached
     * @param \Doc &$origin the document which comes from the attachement
     * @param string $referenceDate reference date to trigger the actions
     * @return string error - empty if no error -
     */
    function attachDocument(&$doc, $origin, $referenceDate = null)
    {
        include_once ("FDL/Class.DocTimer.php");
        
        $dt = new \DocTimer($this->dbaccess);
        $dt->timerid = $this->id;
        $dt->docid = $doc->initid;
        $dt->title = $doc->title;
        $dt->attachdate = $doc->getTimeDate(); // now
        if ($referenceDate === null) $referenceDate = $dt->attachdate;
        $dt->level = 0;
        if ($origin) $dt->originid = $origin->id;
        $dt->fromid = $doc->fromid;
        
        $dates = $this->getMultipleRawValues("tm_delay");
        $hours = $this->getMultipleRawValues("tm_hdelay");
        
        if ((count($dates) == 0)) {
            $err = sprintf(_("no processes specified in timer %s [%d]") , $this->title, $this->id);
        } else {
            
            $acts = $this->getPrevisions($referenceDate, false, 0, 1);
            if (count($acts) == 1) {
                $act = current($acts);
                $dt->actions = serialize($act["actions"]);
                
                if ($referenceDate === '') {
                    $dt->tododate = 'infinity';
                } else {
                    
                    $jdRef = StringDateToJD($referenceDate);
                    $jdRef+= doubleval($this->getRawValue("tm_refdaydelta"));
                    $jdRef+= doubleval($this->getRawValue("tm_refhourdelta")) / 24;
                    $deltaReferenceDate = jd2cal($jdRef);
                    $dt->referencedate = $deltaReferenceDate;
                    
                    $day = doubleval($dates[0]);
                    $hour = doubleval($hours[0]);
                    $jdTodo = $jdRef;
                    $jdTodo+= $day + ($hour / 24);
                    $dt->tododate = jd2cal($jdTodo);
                }
                $err = $dt->Add();
            } else $err = sprintf(_("no level 0 specified in timer %s [%d]") , $this->title, $this->id);
        }
        return $err;
    }
    /**
     * unattach timer to a document
     * @param \Dcp\Family\Timer &$timer the timer document
     * @param \Doc &$origin the document which comes from the attachement
     * @return string error - empty if no error -
     */
    function unattachAllDocument(&$doc, &$origin = null, &$c = 0)
    {
        include_once ("FDL/Class.DocTimer.php");
        
        $dt = new \DocTimer($this->dbaccess);
        if ($origin) $err = $dt->unattachFromOrigin($doc->initid, $origin->initid, $c);
        else $err = $dt->unattachAll($doc->initid, $c);
        
        return $err;
    }
    /**
     * unattach timer to a document
     * @param \Dcp\Family\Timer &$timer the timer document
     * @param \Doc &$origin the document which comes from the attachement
     * @return string error - empty if no error -
     */
    function unattachDocument(&$doc)
    {
        include_once ("FDL/Class.DocTimer.php");
        
        $dt = new \DocTimer($this->dbaccess);
        $err = $dt->unattachDocument($doc->initid, $this->id);
        
        return $err;
    }
    /**
     * get prevision for an activate timer
     * @param string $adate attach date
     * @param string $tododate todo date may be false if not an already attached timer
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
        //compute jdstart firstMD
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
            if ($tododate === "infinity") {
                $tprev[$clevel]["execdate"] = _("Timer infinity date");
                $tprev[$clevel]["execdelay"] = "";
            } else {
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
        }
        return ($tprev);
    }
    
    private function linearizeActions()
    {
        $this->lineActions = array();
        $tactions = $this->getArrayRawValues("tm_t_config");
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
                                $tva = $this->rawValueToArray(str_replace('<BR>', "\n", $va));
                                foreach ($tva as $idmail) {
                                    /**
                                     * @var \Dcp\Family\MAILTEMPLATE $tm
                                     */
                                    $tm = new_doc($this->dbaccess, $idmail);
                                    if ($tm->isAlive()) {
                                        $msg = sprintf(_("send mail with template %s [%d]") , $tm->title, $tm->id);
                                        $doc->addHistoryEntry(sprintf(_("execute timer %s (level %d) : %s") , $this->title, $level, $msg));
                                        $err = $tm->sendDocument($doc);
                                        $tmsg[] = $msg;
                                    }
                                }
                                break;

                            case "state":
                                $msg = sprintf(_("change state to %s") , _($va));
                                $doc->addHistoryEntry(sprintf(_("execute timer %s (level %d) : %s") , $this->title, $level, $msg));
                                $err = $doc->setState($va);
                                $tmsg[] = $msg;
                                break;

                            case "method":
                                $msg = sprintf(_("apply method %s") , $va);
                                $doc->addHistoryEntry(sprintf(_("execute timer %s (level %d) : %s") , $this->title, $level, $msg));
                                $err = $doc->applyMethod($va);
                                $tmsg[] = $msg;
                                break;
                        }
                        
                        if ($err) {
                            $gerr.= "$err\n";
                            $doc->addHistoryEntry(sprintf(_("execute timer %s (level %d) : %s") , $this->title, $level, $err) , HISTO_ERROR);
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
}
