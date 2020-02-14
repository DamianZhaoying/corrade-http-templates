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

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

require_once('config.php');
require_once('functions.php');

###########################################################################
##                               INTERNALS                               ##
###########################################################################

# Check if this is the group chat notification.
if(!isset($_POST['type']) || $_POST['type'] != "group") return;
# Check that the notification is for the configured group.
if(!isset($_POST['group']) || $_POST['group'] != $GROUP) return;
# Bail if "firstname", "lastname" or the "message" variables are blank.
if(!isset($_POST['firstname']) || 
    empty($_POST['firstname']) ||
    !isset($_POST['lastname']) ||
    empty($_POST['lastname']) ||
    !isset($_POST['message']) ||
    empty($_POST['message'])) return;

####
# I. Initialize a new blank array.
$data = array();

####
# II. If the file exists, trim it to the number of chat lines.
switch(file_exists("chat.log")) {
    case TRUE:
        # Open the chat log and trim the lines to the configured line size.
        $data = explode(
            PHP_EOL, 
            atomized_get_contents(
                    "chat.log"
            )
        );
        while(sizeof($data) > $CHAT_LINES)
            array_shift($data);
        break;
}

####
# III. Add the line at the end including the date.
array_push(
    $data, 
    sprintf(
        "[%s:%s] %s %s : %s",
        gmdate("H"),
        gmdate("i"),
        $_POST['firstname'], 
        $_POST['lastname'], 
        $_POST['message']
    )
);

####
# IV. Now dump the array to the file.
atomized_put_contents(
    "chat.log", 
    implode(
        PHP_EOL, 
        $data
    )
);

?>
