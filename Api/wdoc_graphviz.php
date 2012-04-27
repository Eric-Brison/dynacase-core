<?php
/*
 * Generate worflow graph
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

/**
 * @global string $id Http var : document id to affect
 * @global string $type Http var : type of graph
 * @global string $orient Http var :orientation TB (TopBottom)  or LR (LeftRight)
 * @global string $size Http var : global size of graph
 */
// refreah for a classname
// use this only if you have changed title attributes
include_once ("FDL/Lib.Attr.php");
include_once ("FDL/Class.DocFam.php");

/**
 * generate doc text for workflow to be used by graphviz
 * @class DotWorkflow
 */
class DotWorkflow
{
    /**
     * @var array
     */
    private $lines;
    /**
     * @var WDoc
     */
    private $wdoc;
    private $ratio = 'auto';
    private $orient = 'LR';
    private $size;
    private $type = 'simple';
    private $statefontsize;
    private $conditionfontsize;
    private $labelfontsize;
    private $fontsize;
    private $memoTr = array();
    private $clusters = array();
    private $clusterProps = array();
    public $style = array(
        'autonext-color' => '#006400', // darkgreen
        'arrow-label-font-color' => '#555555', // dark grey
        'arrow-color' => '#00008b', // darkblue
        'ask-color' => '#00008b', // darkblue
        'condition-color0' => '#6df8ab', // green
        'condition-color1' => '#ffff00', // yellow
        'action-color2' => '#ffa500', // orange
        'action-color3' => '#74c0ec', // light blue
        'mail-color' => '#a264d2', // light violet
        'timer-color' => '#64a2d2', // light blue
        'start-color' => '#00ff00', // green
        'end-color' => '#ff0000', // red

    );

    /**
     * Generate dot text
     * @return string dot text
     */
    public function generate()
    {
        if (!$this->wdoc) {
            throw new Exception('need workflow');
        }
        $ft = $this->wdoc->firstState;

        switch ($this->type) {
            case 'cluster':
            case 'complet':

                $ft = "D";
                $this->setActivity();
                $this->setStartPoint();
                $this->setEndPoint();
                $this->setTransitionLines();
                if ($this->type == 'cluster') $this->drawCluster();
                break;

            case 'simple':
                $this->setStates();
                $this->setTransitionLines();
                $this->lines[] = sprintf('%s [shape=doublecircle]', $this->wdoc->firstState);
                break;

            case 'activity':
                $this->setStates();
                $this->setActivity();
                $this->setTransitionLines();
                $this->lines[] = sprintf('%s [shape=doublecircle]', $this->wdoc->firstState);
                break;
        }
        //if ($this->ratio=="auto") $this->size='';
        $dot = "digraph \"" . $this->wdoc->getTitle() . "\" {
        ratio=\"{$this->ratio}\";
	    rankdir={$this->orient};
        {$this->size}
        bgcolor=\"transparent\";
        {rank=1; \"$ft\";}
        splines=true; fontsize={$this->conditionfontsize}; fontname=sans;
	node [shape = circle, style=filled, fixedsize=true,fontsize={$this->fontsize},fontname=sans];
	edge [shape = circle, style=filled, fixedsize=true,fontsize={$this->conditionfontsize},fontname=sans];\n";

        $dot .= implode($this->lines, "\n");
        $dot .= "\n}";
        return $dot;
    }

    private function setTransitionLines()
    {
        foreach ($this->wdoc->cycle as $k => $v) {
            switch ($this->type) {
                case 'cluster':
                case 'complet':
                    $this->setCompleteTransitionLine($k, $v);
                    break;

                case 'simple':
                    $this->setSimpleTransitionLine($k, $v);
                    break;

                case 'activity':
                    $this->setActivityTransitionLine($k, $v);
                    break;
            }
        }
    }

    /**
     * search attach point to activity
     * the node which are branch to activity
     * @param $startPoint
     * @param $end
     */
    private function  setAttachStart($startPoint, $end, $limitIndex=-1)
    {
        $transitionLink=array();
        foreach ($this->wdoc->cycle as $k => $v) {
            if ($v["e2"] == $startPoint) {
                $t = $this->wdoc->transitions[$v["t"]];
                if ($t["m3"]) $start = "m3" . $k;
                else {
                    $tmids = $this->wdoc->getTransitionTimers($v["t"]);
                    if ($tmids) $start = "tm" . $k;
                    else {
                        $tmid = $this->wdoc->getStateTimers($v["e2"]);

                        if ($tmid) $start = "tmf" . $k;
                        else {
                            $tmid = $this->wdoc->getTransitionMailTemplates($v["t"]);


                            if ($tmid) $start = "mt" . $k;
                            else {
                                $tmid = $this->wdoc->getStateMailTemplate($v["e2"]);

                                if ($tmid) $start = "mtf" . $k;
                                else {

                                    if ($t["m2"]) $start = "m2" . $k;
                                    else $start = $v["e2"] . $k;

                                }

                            }
                        }

                    }

                }
                if ($start&&(($limitIndex==-1) || (!$transitionLink[$v["t"].$v["e2"]]))) {
                    $transitionLink[$v["t"].$v["e2"]]=true;
                if (!$this->memoTr[$start][$end]) {
                    $this->lines[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s", labelfontname=sans, label="%s"];',
                        $start, $end, $this->style['arrow-label-font-color'], $this->style['arrow-color'], "");

                    $this->memoTr[$start][$end] = true;
                }
                }
            }
        }
    }

    /**
     * add node for state (one per transition)
     * @param $e1
     * @param $index
     */
    private function setTransitionState($e1, $index)
    {
        $color = $this->wdoc->getColor($e1);
        $saction = $this->getActivity($e1);
        $tt = sprintf('label="%s"', $this->_n($e1));
        $tt .= ',shape = circle, style=filled, fixedsize=true,width=1.0,   fontname=sans';
        if ($saction) $tt .= ', tooltip="' . $this->_n($e1) . '"';


        if ($color) $tt .= ',fillcolor="' . $color . '"';

        $this->lines[] = '"' . $e1 . $index . '" [' . $tt . '];';

        $this->clusters[$index][] = $e1 . $index;
        $this->clusterProps[$index] = sprintf('color="%s";fillcolor="blue:yellow";label="%s";', $color, $index);


    }

    private function existsTransition($e1,$e2,$t) {
        foreach ($this->wdoc->cycle as $k => $v) {
            if (($v["e1"]==$e1) && ($v["e2"]==$e2) && ($v["t"]==$t)) return true;
        }
        return false;
    }
    private function linkSameTransition($tr, $index)
    {
        foreach ($this->wdoc->cycle as $k => $v) {
            if (($k < $index) && ($v["e2"] == $tr["e2"]) && ($v["t"] == $tr["t"])) {
                $e2 = $v["e2"] . $k;
                $t = $this->wdoc->transitions[$tr["t"]];
                if ($t["m0"]) $e2 = "m0" . $k;
                elseif ($t["m0"]) $e2 = "m1" . $k;
                $e1 = $this->getActivity($tr["e1"]);
                if ($this->existsTransition($tr["e2"],$tr["e1"],$tr["t"])) continue;

                $this->lines[] = sprintf('"%s" -> "%s" [labelfontsize=6,color="%s" ,labelfontname=sans, label="%s"];', $e1, $e2, $this->style['arrow-color'],
$this->_n($tr["t"]));
                return true;
            }
        }
        return false;
    }

    /**
     * analyze a transition e1 - e2
     * @param $index
     * @param array $tr
     */
    public function setCompleteTransitionLine($index, $tr)
    {
        $this->lines[] = sprintf('# complete %d %s %s->%s', $index, $tr["t"], $tr["e1"], $tr["e2"]);
        $e1 = $tr["e1"];
        $e2 = $tr["e2"];
        $t = $this->wdoc->transitions[$tr["t"]];
        $m0 = $t["m0"];
        $m1 = $t["m1"];
        $m2 = $t["m2"];
        $m3 = $t["m3"];
        $ask = $t["ask"];
        $act = $this->getActivity($e1);


        if ($this->linkSameTransition($tr, $index)) {

               $this->setAttachStart($e1, $act);
            return;
        }

        $this->setTransitionState($e2, $index);
        $this->setM0M3($t, $index);

        $tmain = '';
        if (isset($this->wdoc->autonext[$tr["e1"]]) && ($this->wdoc->autonext[$e1] == $e2)) {
            $tmain = sprintf('color="%s",style="setlinewidth(3)",arrowsize=1.0', $this->style['autonext-color']);
        }
        $startedPoint = false;
        if ($act) {
            $this->setAttachStart($e1, $act);
            $startedPoint = true;

            $e1 = $act;
        }


        if ($ask) {
            $mi = "ask" . $index;
            if (!$startedPoint) {
                $this->setAttachStart($e1, $mi);
                $startedPoint = true;
            }
            $this->lines[] = '#ASK';
            $this->lines[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s",  labelfontname=sans, label="%s"];', $e1, $mi, $this->style['arrow-label-font-color'], $this->style['arrow-color'], _($tr["t"]));
            $e1 = $mi;
        }
        if ($m0) {
            $mi = "m0" . $index;
            if (!$startedPoint) {
                $this->setAttachStart($e1, $mi);
                $startedPoint = true;
            }
            $this->lines[] = '#M0';
            $this->lines[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s",  labelfontname=sans, label="%s"];', $e1, $mi, $this->style['arrow-label-font-color'], $this->style['arrow-color'], _($tr["t"]));
            $e1 = $mi;
        }

        if ($m1) {
            $mi = "m1" . $index;
            if (!$startedPoint) {
                $this->setAttachStart($e1, $mi);
                $startedPoint = true;
            }
            $this->lines[] = '#M1';
            $this->lines[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s",  labelfontname=sans, label="%s"];', $e1, $mi, $this->style['arrow-label-font-color'], $this->style['arrow-color'], _($tr["t"]));
            $e1 = $mi;
        }
        $e2p = $e2 . $index;
        if (!$this->memoTr[$e1][$e2p]) {

            if (!$startedPoint) {
                $this->setAttachStart($e1, $e2);
                $startedPoint = true;
            }
            $this->lines[] = sprintf('"%s" -> "%s" [labelfontsize=6,color="%s" %s,labelfontname=sans, label="%s"];',
                $e1,
                $e2p,
                $this->style['arrow-color'], $tmain, _($tr["t"]));
            $this->memoTr[$e1][$e2p] = true;
        }
        $e2 = $e2p;
        if ($m2) {
            $mi = "m2" . $index;
            $this->lines[] = '#M2';
            $this->lines[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s",  labelfontname=sans, label="%s"];', $e2, $mi, $this->style['arrow-label-font-color'], $this->style['arrow-color'], _($tr["t"]));
            $e2 = $mi;
        }
        $e2 = $this->setTransitionMail($e2, $tr, $index);
        $e2 = $this->setStateMail($e2, $tr, $index);
        $e2 = $this->setTransitionTimer($e2, $tr, $index);
        $e2 = $this->setStateTimer($e2, $tr, $index);
        if ($m3) {
            $mi = "m3" . $index;
            $this->lines[] = '#M3';
            $this->lines[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s",  labelfontname=sans, label="%s"];', $e2, $mi, $this->style['arrow-label-font-color'], $this->style['arrow-color'], _($tr["t"]));
            $e2 = $mi;
        }

    }

    /**
     * declare end state and add red node to see it
     */
    private function setEndPoint()
    {

        $end = $start = array();
        foreach ($this->wdoc->cycle as $k => $t) {
            $start[] = $t["e1"];
            $end[] = $t["e2"];
        }
        $end = array_unique($end);
        $start = array_unique($start);
        $endState = array_diff(($end), ($start));
        //print_r2($endState);
        $see = array();
        $ends = array();
        foreach ($endState as $e) {
            foreach ($this->wdoc->cycle as $k => $t) {
                if ($t["e2"] == $e && (!$see[$e]) && (!$see[$t["t"] . $t["e2"]])) {
                    $end = 'E' . $e . $k;
                    $this->lines[] = '"' . $end . '" [shape = square,style=filled, width=0.3,label="", fixedsize=true,fontname=sans,color="' . $this->style['end-color'] . '"];';
                    $see[$e] = true;
                    $see[$t["t"] . $t["e2"]] = true;

                    $ends[] = $end;
                    $this->setAttachStart($t["e2"], $end, $k);
                }
            }
        }
        if (count($ends) > 0) {
            // $this->lines[] = sprintf('{rank=max; %s}', implode(',',$ends));
        }
    }

    /**
     * draw cluster around transition
     */
    private function drawCluster()
    {
        foreach ($this->clusters as $kc => $aCluster) {
            $sCluster = sprintf('subgraph cluster_%d {
        		style="rounded"; %s label="%s"',
                $kc, $this->clusterProps[$kc], $kc);
            $sCluster .= '"' . implode('";"', $aCluster) . '"';
            $sCluster .= '}';
            $this->lines[] = $sCluster;
        }
    }

    /**
     * define starting of workflow
     */
    private function setStartPoint()
    {

        $aid = strtolower($this->wdoc->attrPrefix . "_TMID" . $this->wdoc->firstState);
        $tm = $this->wdoc->getTValue($aid);
        $aid = strtolower($this->wdoc->attrPrefix . "_MTID" . $this->wdoc->firstState);
        $mt = $this->wdoc->getTValue($aid);
        $e1 = "D";

        $this->lines[] = '"' . $e1 . '" [shape = point,style=filled, width=0.3, fixedsize=true,fontname=sans,color="' . $this->style['start-color'] . '"];';
        ;

        $e2 = 'D' . $this->wdoc->firstState;
        $this->lines[] = sprintf('"%s" [label="%s",shape = doublecircle, style=filled, width=1.0, fixedsize=true,fontname=sans,fillcolor="%s"];', $e2, $this->_n($this->wdoc->firstState), $this->wdoc->getColor($this->wdoc->firstState));
        $this->lines[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s", labelfontname=sans];', $e1, $e2, $this->style['arrow-label-font-color'], $this->style['arrow-color']);
        $e1 = $e2;

        if (count($tm) > 0) {
            $e2 = 'tmfirst';

            $tmlabel = str_replace(array(
                "\n",' ',
                '<BR>'
            ), array("\\n,","\\n",",\\n"), $this->wdoc->getHtmlValue($this->wdoc->getAttribute($this->wdoc->attrPrefix . "_TMID" . $this->wdoc->firstState), $this->wdoc->_array2val($tm), '_self', false));
            $timgt = ' image="' . DEFAULT_PUBDIR . '/Images/timer.png"';
            $this->lines[] = '"' . str_replace(" ", "\\n", $e2) . '" [ label="' . $tmlabel . '",fixedsize=false,style=bold,shape=octagon,color="' . $this->style['timer-color'] . '", fontsize=' . $this->conditionfontsize . $timgt . ' ];';
            $this->lines[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s", labelfontname=sans];', $e1, $e2, $this->style['arrow-label-font-color'], $this->style['arrow-color']);
            $e1 = $e2;
        }
        if (count($mt) > 0) {
            $e2 = 'mtfirst';
            $tmlabel = str_replace(array(
                "\n",' ',
                '<BR>'
            ), array("\\n,","\\n",",\\n"), $this->wdoc->getHtmlValue($this->wdoc->getAttribute($this->wdoc->attrPrefix . "_MTID" . $this->wdoc->firstState), $this->wdoc->_array2val($mt), '_self', false));
            $timgt = ' image="' . DEFAULT_PUBDIR . '/Images/tmail.png"';
            $this->lines[] = '"' . $e2 . '" [ label="' . $tmlabel . '",fixedsize=false,style=bold,shape=house,color="' . $this->style['mail-color'] . '", fontsize=' . $this->conditionfontsize . $timgt . ' ];';
            $this->lines[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s", labelfontname=sans];', $e1, $e2, $this->style['arrow-label-font-color'], $this->style['arrow-color']);
            $e1 = $e2;
        }

        if ($e1 != 'D') {
            //attach to first state
            $e2 = $this->getActivity($this->wdoc->firstState);
            $this->lines[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s", labelfontname=sans];', $e1, $e2, $this->style['arrow-label-font-color'], $this->style['arrow-color']);
        }
    }

    /**
     * define mail node
     * @param string $e2 state
     * @param array $t transition
     * @param int $index index
     * @return string new node id
     */
    private function setTransitionMail($e2, $t, $index)
    {
        $ttrans = array();
        $tm = $this->wdoc->getStateMailTemplate($t["t"]);
        if ($tm) $ttrans[] = $tm;
        $mtrans = $this->wdoc->getTransitionMailTemplates( $t["t"]);

        if (count($mtrans) > 0) {
            $ex = 'mt' . $index;
            $tmlabel = "mail";

            $tmlabel = str_replace(array(
                "\n",' ',
                '<BR>',
            ), array("\\n,","\\n",",\\n"), $this->wdoc->getHtmlValue($this->wdoc->getAttribute($this->wdoc->attrPrefix . "_TRANS_MTID" . $t["t"]), $this->wdoc->_array2val($mtrans), '_self', false));
            $timgt = ' image="' . DEFAULT_PUBDIR . '/Images/tmail.png"';

            $this->lines[] = '"' . $ex . '" [ label="' . $tmlabel . '",fixedsize=false, tooltip="mail",style=bold,shape=house,color="' . $this->style['mail-color'] . '"' . $timgt . ' ];';
            $this->lines[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false,color="%s",labelfontname=sans];', $e2, $ex, $this->style['arrow-label-font-color'], $this->style['arrow-color']);
            $this->clusters[$index][] = $ex;
            $e2 = $ex;
        }

        return $e2;
    }

    /**
     * define mail node
     * @param string $e2 state
     * @param array $t transition
     * @param int $index index
     * @return string new node id
     */
    private function setStateMail($e2, $t, $index)
    {
        $mt = $this->wdoc->getStateMailTemplate($t["e2"]);
        if (count($mt) > 0) {
            $ex = 'mtf' . $index;

            $tmlabel = str_replace(array(
                "\n",' ',
                '<BR>'
            ), array("\\n,","\\n",",\\n"), $this->wdoc->getHtmlValue($this->wdoc->getAttribute($this->wdoc->attrPrefix . "_MTID" . $t["e2"]), $this->wdoc->_array2val($mt), '_self', false));
            $timgt = ' image="' . DEFAULT_PUBDIR . '/Images/tmail.png"';
            $this->lines[] = '"' . $ex . '" [ label="' . $tmlabel . '",fixedsize=false,tooltip="mail",style=bold,shape=house,color="' . $this->style['mail-color'] . '"' . $timgt . ' ];';
            $this->lines[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s", labelfontname=sans];', $e2, $ex, $this->style['arrow-label-font-color'], $this->style['arrow-color']);

            $this->clusters[$index][] = $ex;
            $e2 = $ex;
        }

        return $e2;
    }

    /**
     * define timer node
     * @param string $e2 state
     * @param array $t transition
     * @param int $index index
     * @return string new node id
     */
    private function setStateTimer($e2, $t, $index)
    {
        $aid = strtolower($this->wdoc->attrPrefix . "_TMID" . $t["e2"]);
        $mt = $this->wdoc->getTValue($aid);
        if (count($mt) > 0) {
            $ex = 'tmf' . $index;

            $tmlabel = str_replace(array(
                "\n",' ',
                '<BR>'
            ), array("\\n,","\\n",",\\n"), $this->wdoc->getHtmlValue($this->wdoc->getAttribute($this->wdoc->attrPrefix . "_MTID" . $t["e2"]), $this->wdoc->_array2val($mt), '_self', false));
            $timgt = ' image="' . DEFAULT_PUBDIR . '/Images/timer.png"';
            $this->lines[] = '"' . $ex . '" [ label="' . $tmlabel . '",fixedsize=false,tooltip="timer",style=bold,shape=octagon,color="' . $this->style['mail-color'] . '"' . $timgt . ' ];';
            $this->lines[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s", labelfontname=sans];', $e2, $ex, $this->style['arrow-label-font-color'], $this->style['arrow-color']);
            $this->clusters[$index][] = $ex;
            $e2 = $ex;
        }

        return $e2;
    }

    /**
     * define timer node
     * @param string $e2 state
     * @param array $t transition
     * @param int $index index
     * @return string new node id
     */
    private function setTransitionTimer($e2, $t, $index)
    {
        $ttrans = array();
        $tm = $this->wdoc->getValue($this->wdoc->attrPrefix . "_TRANS_TMID" . $t["t"]);
        if ($tm) $ttrans[] = $tm;
        $ttrans = array_merge($ttrans, $this->wdoc->getTValue($this->wdoc->attrPrefix . "_TRANS_PA_TMID" . $t["t"]));

        if (count($ttrans) > 0) {
            $ex = 'tm' . $index;
            $tmlabel = "tumer";
            $tmlabel = str_replace(array(
                "\n",' ',
                '<BR>'
            ), array("\\n,","\\n",",\\n"), $this->wdoc->getHtmlValue($this->wdoc->getAttribute($this->wdoc->attrPrefix . "_TRANS_MTID" . $t["t"]), $this->wdoc->_array2val($ttrans), '_self', false));
            $timgt = ' image="' . DEFAULT_PUBDIR . '/Images/timer.png"';
            $this->lines[] = '"' . $ex . '" [ label="' . $tmlabel . '",fixedsize=false,style=bold,tooltip="timer",shape=octagon,color="' . $this->style['timer-color'] . '"' . $timgt . ' ];';
            $this->lines[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s",labelfontname=sans];', $e2, $ex, $this->style['arrow-label-font-color'], $this->style['arrow-color']);
            $this->clusters[$index][] = $ex;
            $e2 = $ex;
        }

        return $e2;
    }

    private function setSimpleTransitionLine($index, $tr)
    {
        $this->lines[] = sprintf('# simple %d %s %s->%s', $index, $tr["t"], $tr["e1"], $tr["e2"]);
        $e1 = $tr["e1"];
        $e2 = $tr["e2"];

        $tmain = '';
        if (isset($this->wdoc->autonext[$tr["e1"]]) && ($this->wdoc->autonext[$tr["e1"]] == $tr["e2"])) {
            $tmain = sprintf('color="%s",style="setlinewidth(3)",arrowsize=1.0', $this->style['autonext-color']);
        }
        $this->lines[] = sprintf('"%s" -> "%s" [labelfontsize=6,color="%s" %s,labelfontname=sans, label="%s"];',
            $e1,
            $e2,
            $this->style['arrow-color'], $tmain, _($tr["t"]));
    }

    private function getActivity($e)
    {
        $act = $this->wdoc->getActivity($e);
        if (!$act) {
            return sprintf(_("activity for %s"), _($e));
        }
        return $act;
    }

    private function setActivityTransitionLine($index, $tr)
    {
        $this->lines[] = sprintf('# activity %d %s %s->%s', $index, $tr["t"], $tr["e1"], $tr["e2"]);

        $e1 = $tr["e1"];
        $e2 = $tr["e2"];
        $act = $this->getActivity($e1);
        $tmain = '';
        if (isset($this->wdoc->autonext[$tr["e1"]]) && ($this->wdoc->autonext[$e1] == $e2)) {
            $tmain = sprintf('color="%s",style="setlinewidth(3)",arrowsize=1.0', $this->style['autonext-color']);
        }
        if ($act) {
            if (!$this->memoTr[$e1][$act]) {
                $this->lines[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s", labelfontname=sans, label="%s"];',
                    $e1, $act, $this->style['arrow-label-font-color'], $this->style['arrow-color'], "");

                $this->memoTr[$e1][$act] = true;
            }
            $e1 = $act;
        }

        if (!$this->memoTr[$e1][$e2]) {
            $this->lines[] = sprintf('"%s" -> "%s" [labelfontsize=6,color="%s" %s,labelfontname=sans, label="%s"];',
                $e1,
                $e2,
                $this->style['arrow-color'], $tmain, _($tr["t"]));
            $this->memoTr[$e1][$e2] = true;
        }
    }

    private function setStates()
    {
        $states = $this->wdoc->getStates();
        foreach ($states as $k => $v) {
            $color = $this->wdoc->getColor($v);
            $saction = $this->getActivity($v);
            $tt = sprintf('label="%s"', $this->_n($v));
            $tt .= ',shape = circle, style=filled, fixedsize=true,width=1.0,   fontname=sans';
            if ($saction) $tt .= ', tooltip="' . $saction . '"';


            if ($color) $tt .= ',fillcolor="' . $color . '"';

            $this->lines[] = '"' . $v . '" [' . $tt . '];';

        }
    }

    private function setActivity()
    {
        $states = $this->wdoc->getStates();
        foreach ($states as $k => $v) {
            $color = $this->wdoc->getColor($v);
            $sact = $this->getActivity($v);
            if (!$sact) {
                //$sact = "activity $v";
            }
            if ($this->wdoc->getActivity($v) || (!$this->isEndState($v))) {
                $tt = 'shape = box, style=filled, fixedsize=false,width=1.0,   fontname=sans';
                if ($sact) $tt .= sprintf(',label="%s"', $this->_n($sact));

                if ($color) $tt .= ',fillcolor="' . $color . '"';


                $this->lines[] = '"' . $sact . '" [' . $tt . '];';
            }
        }
    }

    private function isEndState($e)
    {
        foreach ($this->wdoc->cycle as $t) {
            if ($t["e1"] == $e) return false;
        }
        return true;
    }

    private function setM0M3($tr, $k)
    {

        $tt=sprintf('fixedsize=false,fontsize="%s"',$this->conditionfontsize);
        if ($tr["m0"]) {
            $mi = "m0" . $k;
            $this->lines[] = sprintf('"%s" [%s,label="%s",  shape=Mdiamond,tooltip="m0",color="%s"];',
                $mi,$tt, $this->_n($tr["m0"]), $this->style['condition-color0']);

            $this->clusters[$k][] = $mi;
        }
        if ($tr["m1"]) {
            $mi = "m1" . $k;
            $this->lines[] = sprintf('"%s" [%s,label="%s", tooltip="m1",shape=diamond,color="%s"];',
                $mi,$tt, $this->_n($tr["m1"]), $this->style['condition-color1']);
            $this->clusters[$k][] = $mi;
        }
        if ($tr["m2"]) {
            $mi = "m2" . $k;
            $this->lines[] = sprintf('"%s" [%s,label="%s",tooltip="m2",shape=box,color="%s"];',
               $mi,  $tt,$this->_n($tr["m2"]), $this->style['action-color2']);
            $this->clusters[$k][] = $mi;
        }
        if ($tr["m3"]) {
            $mi = "m3" . $k;
            $this->lines[] = sprintf('"%s" [%s,label="%s",tooltip="m3",shape=box,color="%s"];',
               $mi,  $tt,$this->_n($tr["m3"]), $this->style['action-color3']);
            $this->clusters[$k][] = $mi;
        }
        if ($tr["ask"] && count($tr["ask"]) > 0) {
            $mi = "ask" . $k;
            $askLabel = array();
            foreach ($tr["ask"] as $aAsk) {
                $oa = $this->wdoc->getAttribute($aAsk);
                if ($oa) $askLabel [] = $oa->getLabel();
            }
            $this->lines[] = sprintf('"%s" [%s,label="%s", style="rounded",tooltip="ask",shape=egg,color="%s", image="%s"];',
                $mi, $tt,implode('\\n', $askLabel), $this->style['ask-color'], DEFAULT_PUBDIR . '/Images/wask.png');
            $this->clusters[$k][] = $mi;
        }


    }

    public function _n($s)
    {
        if ($s) return str_replace(" ", "\\n", _($s));
        return '';
    }

    public function setWorkflow(WDoc & $doc)
    {
        $this->wdoc = $doc;
    }

    public function setRatio($ratio)
    {
        $this->ratio = $ratio;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setOrient($orient)
    {
        $this->orient = $orient;
    }

    public function setSize($isize)
    {
        $this->fontsize = 13;
        if ($isize == "auto") $this->size = "";
        else {
            if ($isize == "A4") {
                $this->fontsize=20;
                $this->size = "size=\"7.6,11!\";"; // A4 whith 1.5cm margin

            } else {
                if (preg_match("/([0-9\.]+),([0-9\.]+)/", $isize, $reg)) {
                    $this->fontsize = intval(min($reg[1], $reg[2]) / 1);
                     $this->fontsize = 12;
                    $this->size = sprintf("size=\"%.2f,%.2f!\";", floatval($reg[1]) / 2.55, floatval($reg[2]) / 2.55);
                } else {
                    $isize = sprintf("%.2f", floatval($isize) / 2.55);
                    $this->size = "size=\"$isize,$isize!\";";
                }
            }
        }

        $this->statefontsize = $this->fontsize;
        $this->conditionfontsize = intval($this->fontsize * 10 / 13);
        $this->labelfontsize = intval($this->fontsize * 11 / 13);
    }
}


/**
 * @var Application $appl
 */
$dbaccess = $appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
}

$usage = new ApiUsage();
$usage->setText("Create graph image for workflow");
$docid = $usage->addNeeded("docid", "workflow identificator");
$orient = $usage->addOption("orient", "orientation", array(
    "LR",
    "TB",
    "BT",
    "RL"
), "LR");

$ratio = $usage->addOption("ratio", "ratio", array(
    "auto",
    "fill",
    "compress",
    "expand"
), "auto");
$isize = $usage->addOption("size", "image size", array(), "10");

$type = $usage->addOption("type", "type of output", array(
    "complet",
    "activity",
    "simple",
    "cluster"
), "complet");
$usage->verify();
/**
 * @var WDoc $doc
 */
$doc = new_doc($dbaccess, $docid);


$dw = new DotWorkflow();
$dw->setOrient($orient);
$dw->setRatio($ratio);
$dw->setSize($isize);
$dw->setType($type);
$dw->setWorkflow($doc);
print $dw->generate();

?>