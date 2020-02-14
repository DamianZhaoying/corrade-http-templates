<?php
 
###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that binds to Corrade's "group" chat notification.   ##
###########################################################################

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

require_once('config.php');

###########################################################################
##                               INTERNALS                               ##
###########################################################################
 
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
 
####
# I. Build the POST array to send to Corrade.
$params = array(
    'command' => 'notify',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'type' => 'group',
    'action' => 'add', # Set will discard other URLs
    'tag' => $NOTIFICATION_TAG,
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
    case "True":
        echo 'Group chat notification installed!'.PHP_EOL;
        break;
    default:
        echo 'Corrade returned the error: '.urldecode(
            wasKeyValueGet(
                "error", 
                $result
            )
        ).PHP_EOL;
        break;
}
 
?>