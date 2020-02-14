<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that can be used to download a texture using Corrade ##
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
# II. Download the image as a PNG file.
$params = array(
    'command' => 'download',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'item' => $data['AssetUUID'],
    'type' => 'Texture',
    'format' => 'Png'
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
    echo 'Unable to download texture: '.urldecode(
        wasKeyValueGet(
            "error", 
            $result
        )
    );
    die;
}

####
# III. Convert the image data to a PNG of size 512x512
$im = imagescale(
    imagecreatefromstring(
        base64_decode(
            rawurldecode(
                wasKeyValueGet(
                    "data", 
                    $result
                )
            )
        )
    ), 
    512, 
    512
);

####
# IV. Output the Base64 encoded image for AJAX.
ob_start();
imagepng($im);
$png = ob_get_contents();
imagedestroy($im);
ob_end_clean();

echo base64_encode($png);

?>

