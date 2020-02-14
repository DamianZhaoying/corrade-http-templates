<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that can be used to retrieve information on an       ##
## inventory item by calling the "getinventorydata" Corrade command.     ##
###########################################################################

if(!isset($_POST['uuid']) || !isset($_POST['data'])) return;

$uuid = $_POST['uuid'];
$data = $_POST['data'];

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

require_once('config.php');
require_once('functions.php');

###########################################################################
##                               INTERNALS                               ##
###########################################################################

####
# I. Resolve the inventory UUID to an asset UUID.
$params = array(
    'command' => 'getinventorydata',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'item' => $uuid,
    'data' => wasArrayToCSV($data)
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
$result = curl_exec($curl);
curl_close($curl);

$success = urldecode(
    wasKeyValueGet(
        "success", 
        $result
    )
);

if($success == 'False') {
    echo 'Unable to get inventory UUID: '.urldecode(
        wasKeyValueGet(
            "error", 
            $result
        )
    );
    die;
}

####
# II. Get the returned data and build an associative array.
$data = str_getcsv(
    urldecode(
        wasKeyValueGet(
            "data", 
            $result
        )
    )
);

$data = array_combine(
    wasArrayStride(
        $data,
        2
    ),
    wasArrayStride(
        array_slice(
            $data,
            1
        ),
    2
    )
);

####
# III. Dump the JSON for AJAX.
echo json_encode(
    array(
        $data
    )
);

?>

