<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2016 - License: GNU GPLv3      ##
###########################################################################
## This is a script that retrieves the past conversations with avatars   ##
## from the chat store directory as an JSON encoded object.              ##
###########################################################################

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

require_once('config.php');

###########################################################################
##                               INTERNALS                               ##
###########################################################################

####
# I. Initialize an array to hold the data.
$data = array();

####
# I. Walk through the chat directory and create the array.
array_walk(
    array_diff(
        scandir(
            $CHAT_DIRECTORY
        ),
        array(
            '.',
            '..'
            )
    ), function($value) use(&$data) {
        $fileName = explode('.', $value);
        $fullName = explode(' ', $fileName[0]);
        if(count($fullName) != 2) return;
        array_push(
            $data, 
            array(
                "firstname" => $fullName[0],
                "lastname" => $fullName[1]
            )
        );
    }
);

####
# III. Dump the JSON data.
echo json_encode($data);

?>
