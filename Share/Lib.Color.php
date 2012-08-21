<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Color utilities
 *
 * @author Anakeen
 * @version $Id: Lib.Color.php,v 1.1 2007/02/21 11:04:26 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
// JScript source code
//Red : 0..255
//Green : 0..255
//Blue : 0..255
//Hue : 0,0..360,
//Lum : 0,0..1,0
//Sat : 0,0..1,0

/**
 * return hue, saturation, luminace
 */
function srgb2hsl($rgb)
{
    if ($rgb[0] == '#') {
        $r = hexdec(substr($rgb, 1, 2));
        $g = hexdec(substr($rgb, 3, 2));
        $b = hexdec(substr($rgb, 5, 2));
        return RGB2HSL($r, $g, $b);
    }
    return array(
        0,
        0,
        0
    );
}
//Retourne un tableau de 3 valeurs : H,S,L
function RGB2HSL($r, $g, $b)
{
    $red = round($r);
    $green = round($g);
    $blue = round($b);
    $minval = min($red, min($green, $blue));
    $maxval = max($red, max($green, $blue));
    $mdiff = $maxval - $minval + 0.0;
    $msum = $maxval + $minval + 0.0;
    $luminance = $msum / 510.0;
    
    $hue = 0.0;
    if ($maxval == $minval) {
        $saturation = 0.0;
    } else {
        $rnorm = ($maxval - $red) / $mdiff;
        $gnorm = ($maxval - $green) / $mdiff;
        $bnorm = ($maxval - $blue) / $mdiff;
        $saturation = ($luminance <= 0.5) ? ($mdiff / $msum) : ($mdiff / (510.0 - $msum));
        if ($red == $maxval) $hue = 60.0 * (6.0 + $bnorm - $gnorm);
        if ($green == $maxval) $hue = 60.0 * (2.0 + $rnorm - $bnorm);
        if ($blue == $maxval) $hue = 60.0 * (4.0 + $gnorm - $rnorm);
        if ($hue > 360.0) $hue-= 360.0;
    }
    // return array (round ($hue * 255.0 / 360.0), round ($saturation * 255.0), round ($luminance * 255.0));
    return array(
        $hue,
        $saturation,
        $luminance
    );
}

function Magic($rm1, $rm2, $rh)
{
    $retval = $rm1;
    if ($rh > 360.0) $rh-= 360.0;
    if ($rh < 0.0) $rh+= 360.0;
    if ($rh < 60.0) $retval = $rm1 + ($rm2 - $rm1) * $rh / 60.0;
    else if ($rh < 180.0) $retval = $rm2;
    else if ($rh < 240.0) $retval = $rm1 + ($rm2 - $rm1) * (240.0 - $rh) / 60.0;
    return round($retval * 255);
}
/**
 * Retourne une chaine: #RRGGBB
 * @param float $h hue
 * @param float $s saturation
 * @param float $l luminance
 * @return string
 */

function HSL2RGB($h, $s, $l)
{
    $hue = $h;
    $saturation = $s;
    $luminance = $l;
    if ($saturation == 0.0) {
        $red = $green = $blue = round($luminance * 255.0);
    } else {
        if ($luminance <= 0.5) $rm2 = $luminance + $luminance * $saturation;
        else $rm2 = $luminance + $saturation - $luminance * $saturation;
        $rm1 = 2.0 * $luminance - $rm2;
        $red = Magic($rm1, $rm2, $hue + 120.0);
        $green = Magic($rm1, $rm2, $hue);
        $blue = Magic($rm1, $rm2, $hue - 120.0);
    }
    return sprintf("#%02X%02X%02X", $red, $green, $blue);
    //return new Array ($red, $green, $blue);
    
}
