<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script uses Corrade to fetch the top scripts on a region    ##
## and then passes that data back as a JSON object to be used with the   ##
## DataTables JQuery extension.                                          ##
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
# I. Get the top scripts.
$params = array(
    'command' => 'getregiontop',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'type' => 'scripts'
);
array_walk($params,
    function(&$value, $key) {
        $value = rawurlencode($key)."=".rawurlencode($value);
    }
);
$postvars = implode('&', $params);
if (!($curl = curl_init())) {
    print 0;
    return;
}
curl_setopt($curl, CURLOPT_URL, $URL);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $postvars);
curl_setopt($curl, CURLOPT_ENCODING, true);
$return = curl_exec($curl);
curl_close($curl);

####
# II. Check for success.
$success = urldecode(
    wasKeyValueGet(
        "success", 
        $return
    )
);
if($success == 'False') {
    echo 'Unable to query the top scripts.';
    die;
}

####
# III. Dump JSON for DataTables.
echo json_encode(
    array(
        "data" => 
        array_chunk(
            wasCSVToArray(
                urldecode(
                    wasKeyValueGet(
                        "data", 
                        $return
                    )
                )
            ), 
            5 # We split the CSV in 5: score, task name, UUID, Owner and Position
        )
    )
);

?>


