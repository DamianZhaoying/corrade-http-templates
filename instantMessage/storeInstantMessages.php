<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2016 - License: GNU GPLv3      ##
###########################################################################
## This is a script that stores instant messages to a local file inside  ##
## a sub-directory from the current path.                                ##
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
if(!isset($_POST['type']) || $_POST['type'] != "message") return;
# Check that we have all the required variables.
if(!isset($_POST['firstname']) ||
    empty($_POST['firstname']) ||
    !isset($_POST['lastname']) ||
    empty($_POST['lastname']) ||
    !isset($_POST['message']) ||
    empty($_POST['message'])) return;
    
####
# I. Get the path to the configured chat directory.
$chatPath = realpath($CHAT_DIRECTORY);

####
# II. Get the user path.
$userPath = join(
    DIRECTORY_SEPARATOR, 
    array(
        $CHAT_DIRECTORY,
        ucfirst(
            strtolower(
                $_POST['firstname']
            )
        ) .' '.
        ucfirst(
            strtolower(
                $_POST['lastname']
            )
        ).'.log'
    )
);

####
# III. Check that the file will be placed within the chat directory.
$pathPart = pathinfo($userPath);
if(realpath($pathPart['dirname']) != $chatPath)
    die;

storeAvatarConversation(
    $_POST['firstname'], 
    $_POST['lastname'], 
    $_POST['message'],
    $userPath,
    $CHAT_LINES
);

?>
