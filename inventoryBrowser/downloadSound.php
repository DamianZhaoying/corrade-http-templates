<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that can be used to download a sound using Corrade   ##
## and the "download" Corrde command.                                    ##
###########################################################################

if(!isset($_POST['uuid'])) return;

$uuid = $_POST['uuid'];

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
    'data' => 'AssetUUID'
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

if(!trim($data['AssetUUID'])) {
    echo 'Could not retrieve asset UUID';
    die;
}

####
# II. Download the sound as an MP3 file for HTML5 compatiblity.
$params = array(
    'command' => 'download',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'item' => $data['AssetUUID'],
    'format' => 'mp3',
    'type' => 'Sound'
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
    echo 'Unable to download sound: '.urldecode(
        wasKeyValueGet(
            "error", 
            $result
        )
    );
    die;
}

####
# III. Output the Base64 encoded data.
echo urldecode(
    wasKeyValueGet(
        "data", 
        $result
    )
);

?>

