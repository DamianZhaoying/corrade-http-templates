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

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
function wasArrayToCSV($a) {
    return implode(
        ',',
        array_map(
            function($o) {
                $o = str_replace('"', '""', $o);
                switch(
                    (strpos($o, ' ') !== FALSE) ||
                    (strpos($o, '"') !== FALSE) ||
                    (strpos($o, ',') !== FALSE) ||
                    (strpos($o, '\r') !== FALSE) ||
                    (strpos($o, '\n') !== FALSE)
                )
                {
                    case TRUE:
                        return '"' . $o . '"';
                    default:
                        return $o;
                }
            },
            $a
        )
    );
}

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
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