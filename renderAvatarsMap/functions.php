<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
function wasKeyValueGet($key, $data) {
    return array_reduce(
        explode(
            "&", 
            $data
        ),
        function($o, $p) {
            $x = explode("=", $p);
            return array_shift($x) != $o ? $o : array_shift($x);
        },
        $key
    );
}

///////////////////////////////////////////////////////////////////////////
//  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      //
///////////////////////////////////////////////////////////////////////////
function wasCSVToArray($csv) {
    $l = array();
    $s = array();
    $m = "";
    for ($i = 0; $i < strlen($csv); ++$i) {
        switch ($csv{$i}) {
            case ',':
                if (sizeof($s) == 0 || !current($s) == '"') {
                    array_push($l, $m);
                    $m = "";
                    break;
                }
                $m .= $csv{$i};
                continue;
            case '"':
                if ($i + 1 < strlen($csv) && $csv{$i} == $csv{$i + 1}) {
                    $m .= $csv{$i};
                    ++$i;
                    break;
                }
                if (sizeof($s) == 0|| !current($s) == $csv[$i]) {
                    array_push($s, $csv{$i});
                    continue;
                }
                array_pop($s);
                break;
            default:
                $m .= $csv{$i};
                break;
        }
    }
    array_push($l, $m);
    return $l;
}

///////////////////////////////////////////////////////////////////////////
//  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      //
///////////////////////////////////////////////////////////////////////////
function wasArrayStride($a, $s) {
    return array_filter($a, 
        function($e, $i) use($s) {
            return $i % $s == 0;
        },
        ARRAY_FILTER_USE_BOTH
    );
}

///////////////////////////////////////////////////////////////////////////
//  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      //
///////////////////////////////////////////////////////////////////////////
function wasColorHash($a) {
  $hash = sha1($a);
  $size = strlen($hash);

  return substr($hash, 0, 2).
         substr($hash, $size/2, 2).
         substr($hash, $size-2, 2);
}

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
function mapValueToRange($value, $xMin, $xMax, $yMin, $yMax) {
    return $yMin + (
        (
            $yMax - $yMin
        ) 
        *
        (
            $value - $xMin
        )
        /
        (
            $xMax - $xMin
        )
    );
}

function hex2RGB($hexStr, $returnAsString = false, $seperator = ',') {
    $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr);
    $rgbArray = array();
    switch(strlen($hexStr)) {
        case 6:
            $colorVal = hexdec($hexStr);
            $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
            $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
            $rgbArray['blue'] = 0xFF & $colorVal;
            break;
        case 3:
            $rgbArray['red'] = hexdec(
                str_repeat(substr($hexStr, 0, 1), 2));
            $rgbArray['green'] = hexdec(
                str_repeat(substr($hexStr, 1, 1), 2));
            $rgbArray['blue'] = hexdec(
                str_repeat(substr($hexStr, 2, 1), 2));
            break;
        default:
            return false;
    }
    return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray;
}
