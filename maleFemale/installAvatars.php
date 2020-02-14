<?php
 
###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that binds to Corrade's "group" chat notification.   ##
###########################################################################

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

# Set this to the name of the group.
$GROUP = 'My Group';
# Set this to the group password.
$PASSWORD = 'mypassword';
# Set this to Corrade's HTTP Server URL.
$URL = 'http://corrade.local.site:8080';
# Set this to the location of the process PHP script.
$READER = 'http://web.server/path/to/maleFemale/storeMaleFemale.php';
 
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
 
# This constructs the command as an array of key-value pairs.
$params = array(
    'command' => 'notify',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'type' => 'avatars',
    'action' => 'set',
    'URL' => $READER
);
 
# We now escape each key and value: this is very important, because the 
# data we send to Corrade may contain the '&' or '=' characters (we don't 
# in this example though but this will not hurt anyone).
array_walk($params,
 function(&$value, $key) {
     $value = rawurlencode($key)."=".rawurlencode($value);
 }
);
$postvars = implode('&', $params);
 
# Set the options, send the request and then display the outcome
if (!($curl = curl_init())) {
    print 0;
    return;
}
 
curl_setopt($curl, CURLOPT_URL, $URL);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $postvars);
$result = curl_exec($curl);
$status = urldecode(
    wasKeyValueGet(
        "success", 
        $result
    )
);
$error = urldecode(
    wasKeyValueGet(
        "error", 
        $result
    )
);
curl_close($curl);

switch($status) {
    case "True":
        echo 'Avatars notification installed!';
        break;
    default:
        echo 'The following error was encountered while attempting to install the avatars notification: '.$error;
        break;
}
 
?>