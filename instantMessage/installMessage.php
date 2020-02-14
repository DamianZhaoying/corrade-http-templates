<?php
 
###########################################################################
##  Copyright (C) Wizardry and Steamworks 2016 - License: GNU GPLv3      ##
###########################################################################
## This is a script that binds to Corrade's "message" IM notification.   ##
###########################################################################

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

require_once('config.php');
require_once('functions.php');

###########################################################################
##                               INTERNALS                               ##
###########################################################################

####
# I. Build the POST array to send to Corrade.
$params = array(
    'command' => 'notify',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'type' => 'message',
    'action' => 'set', # Set will discard other URLs
    'URL' => $STORE
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
    case "True":
        echo 'Instant message notification installed!';
        break;
    default:
        echo 'Corrade returned the error: '.urldecode(
            wasKeyValueGet(
                "error", 
                $result
            )
        );
        break;
}
 
?>