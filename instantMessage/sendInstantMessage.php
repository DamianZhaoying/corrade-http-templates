<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2016 - License: GNU GPLv3      ##
###########################################################################
## This is a script that sends a message to an agent from Corrade and it ##
## also stores the sent message to a conversation file.                  ##
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

# Check that we have all the necessary variables.
if(!isset($_POST['message']) || 
    empty($_POST['message']) ||
    !isset($_POST['name']) ||
    empty($_POST['name']) ||
    !isset($_POST['firstname']) ||
    empty($_POST['firstname']) || 
    !isset($_POST['lastname']) ||
    empty($_POST['lastname'])) return;

####
# I. Build the POST array to send to Corrade.
$params = array(
    'command' => 'tell',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'entity' => 'avatar',
    'firstname' => $_POST['firstname'],
    'lastname' => $_POST['lastname'],
    'message' => $_POST['name'].' says '.$_POST['message']
);

####
# II. Escape the data to be sent to Corrade.
array_walk($params,
    function(&$value, $key) {
        $value = rawurlencode($key)."=".rawurlencode($value);
    }
);
$postvars = implode('&', $params);

####
# III. Use curl to send the message.
if (!($curl = curl_init())) {
    print 0;
    return;
}
curl_setopt($curl, CURLOPT_URL, $URL);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $postvars);
curl_setopt($curl, CURLOPT_ENCODING, true);
$result = curl_exec($curl);
curl_close($curl);

####
# IV. Grab the status of the command.
$status = urldecode(
    wasKeyValueGet(
        "success", 
        $result
    )
);

####
# IV. Check the status of the command.
switch($status) {
    case "True": # The message was sent successfully so store it within a conversation file.
        ####
        # V. Get the path to the configured chat directory.
        $chatPath = realpath($CHAT_DIRECTORY);

        ####
        # VI. Get the user path.
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
        # VII. Check that the file will be placed within the chat directory.
        $pathPart = pathinfo($userPath);
        if(realpath($pathPart['dirname']) != $chatPath)
            die;
        
        ####
        # VIII. Store the message.
        storeAvatarConversation(
            $_POST['name'], 
            '', 
            $_POST['message'], 
            $userPath,
            $CHAT_LINES
        );
        break;
    default: # Otherwise, return the Corrade error message.
        echo 'Corrade failed to deliver the message with the error message: '.urldecode(
            wasKeyValueGet(
                "error", 
                $result
            )
        );
        break;
}

?>