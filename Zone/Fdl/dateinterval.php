<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Display input to select date interval
 *
 * @author Anakeen
 * @version $Id: dateinterval.php,v 1.1 2004/02/05 15:41:40 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
function dateinterval(&$action)
{
    
    $actualyear = strftime("%Y", time());
    $fmonth = GetHttpVars("fmonth", strftime("%m", time())); // from year
    $fyear = GetHttpVars("fyear", $actualyear);
    $tmonth = GetHttpVars("tmonth"); // to year
    $tyear = GetHttpVars("tyear", $actualyear);
    
    for ($i = 1; $i < 13; $i++) {
        $monthname[$i]["monthname"] = strftime("%B", $i * 3600 * 24 * 28);
        $monthname[$i]["monthvalue"] = $i;
        if ($i == $fmonth) $monthname[$i]["fselectmonth"] = "selected";
        else $monthname[$i]["fselectmonth"] = "";
        if ($i == $tmonth) $monthname[$i]["tselectmonth"] = "selected";
        else $monthname[$i]["tselectmonth"] = "";
    }
    
    for ($i = 2002; $i <= $actualyear; $i++) {
        $year[$i]["yearvalue"] = $i;
        if ($i == $fyear) $year[$i]["fselectyear"] = "selected";
        else $year[$i]["fselectyear"] = "";
        if ($i == $fyear) $year[$i]["tselectyear"] = "selected";
        else $year[$i]["tselectyear"] = "";
    }
    $action->lay->SetBlockData("SELECTMONTHFROM", $monthname);
    $action->lay->SetBlockData("SELECTMONTHTO", $monthname);
    $action->lay->SetBlockData("SELECTYEARFROM", $year);
    $action->lay->SetBlockData("SELECTYEARTO", $year);
    $action->lay->Set("today", strftime("%d %B %Y", time()));
    
    $fdate = mktime(0, 0, 0, $fmonth, 1, $fyear);
    
    if ($tmonth == 0) $tdate = time(); // today
    else $tdate = mktime(0, 0, -1, $tmonth + 1, 1, $tyear); // last day of the month
    $action->lay->Set("period", sprintf(_("period from %s to %s") , strftime("%d %B %Y", $fdate) , strftime("%d %B %Y", $tdate)));
    
    if ($tdate < $fdate) $action->ExitError(sprintf(_("the end date (%s) is before the begin date (%s)") , strftime("%d %B %Y %T", $tdate) , strftime("%d %B %Y %T", $fdate)));
    
    return array(
        "fromdate" => $fdate,
        "todate" => $tdate
    );
}
?>