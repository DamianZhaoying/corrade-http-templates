<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2016 - License: GNU GPLv3      ##
###########################################################################
## A small script that will delete a conversation from the configured    ##
## conversation directory.                                               ##
###########################################################################

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

require_once('config.php');
require_once('functions.php');

###########################################################################
##                               INTERNALS                               ##
###########################################################################

# CRSF.
session_start();
if (empty($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
    http_response_code(403);
    die('Forbidden.');
}

# Bail if "firstname" or "lastname" are blank.
if(!isset($_POST['firstname']) ||
    !isset($_POST['lastname'])) return;

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

####
# IV. Remove the conversation.
unlink($userPath);

?>