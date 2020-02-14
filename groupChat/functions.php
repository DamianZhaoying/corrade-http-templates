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
        function($o, $p) {
            $x = explode("=", $p);
            return array_shift($x) != $o ? $o : array_shift($x);
        },
        $key
    );
}

