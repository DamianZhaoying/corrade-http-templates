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

# If no avatars have been sent, then bail.
if(!isset($_POST['avatars'])) return;

####
# I. Build the list of invites to send to Corrade.
$invites = array();
foreach(
    array_filter(explode("\n", $_POST['avatars']), 'trim') as $avatar) {
    $nameUUID = preg_split("/[\s]+/", $avatar);
    switch(count($nameUUID)) {
        case 2:
        case 1:
            array_push($invites, $avatar);
        break;
    }
}

# Do not bother with an empty list of invites
if(count($invites) == 0) {
    echo implode(PHP_EOL, $invites);
    return -1;
}

####
# II. Build the command by name.
$params = array(
    'command' => 'batchinvite',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'avatars' => wasArrayToCSV($invites)
);

####
# III. Escape the data to be sent to Corrade.
array_walk($params,
    function(&$value, $key) {
        $value = rawurlencode($key)."=".rawurlencode($value);
    }
);
$postvars = implode('&', $params);

####
# IV. Use curl to send the message.
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
# V. Grab the status of the command.
$status = urldecode(
    wasKeyValueGet(
        "success", 
        $result
    )
);


####
# VI. Check the status of the command.
switch($status) {
    case "False":
        return -1;
}

####
# V. Return any avatars or UUIDs for which invites could not be sent.
echo implode(
    PHP_EOL, 
    wasCSVToArray(
        urldecode(
            wasKeyValueGet(
                "data", 
                $result
            )
        )
    )
);

return 0;

?>
