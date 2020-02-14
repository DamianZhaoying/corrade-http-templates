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
