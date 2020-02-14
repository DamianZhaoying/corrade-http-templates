<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that stores group messages to a local file that can  ##
## then be read-in by other scripts and displayed. The script will only  ##
## write to a file "chat.log" in the current directory and the name of   ##
## the chat log ("chat.log") is hard-coded because it depends on this    ##
## script's HTML counter-part that has the name hard-coded too.          ##
###########################################################################

# Send the response.
http_response_code(200);

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

require_once('config.php');
require_once('functions.php');

###########################################################################
##                               INTERNALS                               ##
###########################################################################

# Check if this is the membership notification.
if(!isset($_POST['type']) || $_POST['type'] != "membership") return;
# Check that the notification is for the configured group.
if(!isset($_POST['group']) || $_POST['group'] != $GROUP) return;
# Bail if "firstname", "lastname" or the "message" variables are blank.
if($_POST['firstname'] == '' 
    || $_POST['lastname'] == '' 
    || (
        $_POST['action'] != 'joined' && 
        $_POST['action'] != 'parted'
    )) return;

####
# I. Initialize a new blank array.
$data = array();

####
# II. If the file exists, trim it to the number of chat lines.
switch(file_exists("membership.log")) {
    case TRUE:
        # Open the membership.log and trim the lines.
        $data = explode(
            PHP_EOL, 
            atomized_get_contents(
                    "membership.log"
            )
        );
        while(sizeof($data) > $MEMBERSHIP_LINES)
            array_shift($data);
        break;
}

####
# II. Add the line at the end.
array_push(
    $data, 
    sprintf(
        "[%s:%s] %s %s : %s",
        date("H"),
        date("i"),
        $_POST['firstname'], 
        $_POST['lastname'], 
        $_POST['action']." the group."
    )
);

####
# II. Now dump the array to the file.
atomized_put_contents(
    "membership.log", 
    implode(
        PHP_EOL, 
        $data
    )
);

?>
