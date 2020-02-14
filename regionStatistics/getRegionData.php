<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2016 - License: GNU GPLv3      ##
###########################################################################
## This is a script that uses Corrade's "getregiondata" command in order ##
## to retrieve statistics on the current simulator.                      ##
###########################################################################

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

require_once('config.php');
require_once('functions.php');

###########################################################################
##                               INTERNALS                               ##
###########################################################################

if(!isset($_POST["query"])) return;

####
# I. Get the query from the javascript.
$query = $_POST["query"];

# This constructs the command as an array of key-value pairs.
$params = array(
    'command' => 'getregiondata',
    'group' => $GROUP,
    'data' => $query,
    'password' => $PASSWORD
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

####
# II. Set the options, send the request and then display the outcome.
if (!($curl = curl_init())) return;

curl_setopt($curl, CURLOPT_URL, $URL);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $postvars);
curl_setopt($curl, CURLOPT_ENCODING, true);
$result = str_getcsv(
    urldecode(
        wasKeyValueGet(
            "data", 
            curl_exec($curl)
        )
    )
);
curl_close($curl);

####
# III. Dump JSON for AJAX.
echo json_encode(
    array_combine(
        wasArrayStride(
            $result,
            2
        ),
        wasArrayStride(
            array_slice(
                $result, 
                1
            ), 
            2
        )
    )
);

?>

