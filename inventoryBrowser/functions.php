<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
function atomized_put_contents($file, $data) {
    $fp = fopen($file, "w+");
    if (flock($fp, LOCK_EX)) {
        fwrite($fp, $data);
        fflush($fp);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
}
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

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
function wasKeyValueGet($key, $data) {
    return array_reduce(
        explode(
            "&", 
            $data
        ),
        function($o, $p) use($key) {
            return array_reduce(
                    explode(
                        "=",
                        $p
                    ),
                    function($q, $r) use($key) {
                        if($q === $key) return $r;
                    },
                    $key
            ). $o;
        }
    );
}

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
function wasArrayStride($a, $s) {
    return array_filter($a, 
        function($e, $i) use($s) {
            return $i % $s == 0;
        },
        ARRAY_FILTER_USE_BOTH
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



