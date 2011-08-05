<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Set of usefull Date and Time functions
 *
 * @author Anakeen 2000
 * @version $Id: Lib.Date.php,v 1.2 2003/08/18 15:46:42 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------------------
// Date
// ---------------------------------------------------------------------------
// Anakeen 2000 - yannick.lebriquer@anakeen.com
// ---------------------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------------------
//  $Id: Lib.Date.php,v 1.2 2003/08/18 15:46:42 eric Exp $
$LIB_DATE_PHP = '$Id: Lib.Date.php,v 1.2 2003/08/18 15:46:42 eric Exp $';

function DayRound(&$timestamp)
{
    
    return mktime(0, 0, 0, strftime("%m", $timestamp) , strftime("%d", $timestamp) , strftime("%Y", $timestamp));
}

function IncDay($timestamp, $inc)
{
    return mktime(0, 0, 0, strftime("%m", $timestamp) , strftime("%d", $timestamp) + $inc, strftime("%Y", $timestamp));
}
// Return the next requested day
function GoNextWeekDay($now, $daynumber)
{
    $inc = ($daynumber - strftime("%w", $now)) % 7;
    return mktime(0, 0, 0, strftime("%m", $now) , strftime("%d", $now) + $inc, strftime("%Y", $now));
}
// Return the week first day as an epoch time
function FirstWeekDay($now)
{
    $inc = (7 - strftime("%w", $now));
    return mktime(0, 0, 0, strftime("%m", $now) , strftime("%d", $now) - $inc, strftime("%Y", $now));
}
/*
function UpdateFields() {
  $this->year      = strftime("%Y",$this->timestamp);
  $this->monthnum  = strftime("%m",$this->timestamp);
  $this->lmonthstr = strftime("%B",$this->timestamp);
  $this->smonthstr = strftime("%b",$this->timestamp);
  $this->daynum    = strftime("%d",$this->timestamp);
  $this->ldaystr   = strftime("%A",$this->timestamp);
  $this->sdaystr   = strftime("%a",$this->timestamp);
  $this->hour      = strftime("%H",$this->timestamp);
  $this->minute    = strftime("%M",$this->timestamp);
  $this->second    = strftime("%S",$this->timestamp);
}
*/
// Return day count for a month
function daycount($m, $y)
{
    return date("t", mktime(0, 0, 0, $m, 1, $y));
}
// Return the next day
function nextday($d, $m, $y, &$nd, &$nm, &$ny)
{
    $nd = ($d == daycount($m, $y) ? 1 : $d + 1);
    if ($nd == 1) nextmonth($m, $y, $nm, $ny);
    else {
        $nm = $m;
        $ny = $y;
    }
}
// Return the previous day
function prevday($d, $m, $y, &$pd, &$pm, &$py)
{
    $pd = ($d == 1 ? daycount(($m - 1) , $y) : $d - 1);
    if ($pd == daycount(($m - 1) , $y)) prevmonth($m, $y, $pm, $py);
    else {
        $pm = $m;
        $py = $y;
    }
}
// Return the next month
function nextmonth($m, $y, &$nm, &$ny)
{
    $nm = ($m == 12 ? 1 : $m + 1);
    $ny = ($nm == 1 ? $y + 1 : $y);
}
// Return the previous month
function prevmonth($m, $y, &$pm, &$py)
{
    $pm = ($m == 1 ? 12 : $m - 1);
    $py = ($pm == 12 ? $y - 1 : $y);
}
// Return the week number (weeks start on Monday)
function WeekNumber($epoch)
{
    return strftime("%W", $epoch);
}
// Return the week first day as an epoch time
function GetWeekFirstDay($now, $epoch)
{
    
    return strftime("%W", $epoch);
}
?>
