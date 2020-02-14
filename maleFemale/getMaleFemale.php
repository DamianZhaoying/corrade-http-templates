<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that reads a visitors log and generates a json       ##
## object from the number of males and females that Corrade detected.    ##
###########################################################################

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

# Visitors file.
$VISITOR_FILE = "visitors.log";

###########################################################################
##                               INTERNALS                               ##
###########################################################################

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
function atomized_get_contents($file) {
    $fp = fopen($file, "r+");
    $ct = '';
    if (flock($fp, LOCK_SH)) {
        if (filesize($file)) {
            $ct = fread($fp, filesize($file));
        }
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    return $ct;
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

$f = 0;
$m = 0;

array_walk(
    explode(
        PHP_EOL, 
        atomized_get_contents(
            $VISITOR_FILE
        )
    ),
    function($e, $k) use(&$f, &$m) {
        switch(wasCSVToArray($e)[1]) {
            case 'female':
                ++$f;
                break;
            default:
                ++$m;
                break;
        }
    }
);

header('content-type: application/json; charset=utf-8');
echo json_encode(
    array(
        "Male" => $m,
        "Female" => $f
    )
);

?>