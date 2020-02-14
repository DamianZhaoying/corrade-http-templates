<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that sends a group message using Corrade.            ##
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

# If there is no message set or no name set or if the message or the name
# are empty then do not proceed any further.
if(!isset($_POST['message']) ||
    empty($_POST['message']) || 
    !isset($_POST['name']) || 
    empty($_POST['name'])) return;

####
# I. Build the POST array to send to Corrade.
$params = array(
    'command' => 'tell',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'entity' => 'group',
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
# V. Check the status of the command.
switch($status) {
    case "True": # Be silent if the message has been sent successfully.
        # echo 'Message sent successfully!';
        break;
    default: # If an error occured, then return the error message.
        echo 'Corrade failed to send the group message and reported the error: '.urldecode(
            wasKeyValueGet(
                "error", 
                $result
            )
        );
        break;
}

?>
