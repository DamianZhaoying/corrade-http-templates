<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that uses Corrade's HTTP server to get the UUID of   ##
## the current region map, then downloads the map as a PNG which then    ##
## gets encoded to a Base64 string and echoed from this script.          ##
###########################################################################

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

# The configuration file for this script containing the settings.
require_once('config.php');
require_once('functions.php');

###########################################################################
##                               INTERNALS                               ##
###########################################################################

####
# I. Get the UUID of the map image for the current region
$params = array(
    'command' => 'getgridregiondata',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'data' => 'MapImageID'
);
array_walk($params,
    function(&$value, $key) {
        $value = urlencode($key)."=".urlencode($value);
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
$return = curl_exec($curl);
curl_close($curl);

$success = urldecode(
    wasKeyValueGet(
        "success", 
        $return
    )
);

// Unable to retrieve the map image?
if($success == 'False') return -1;

$mapUUID = wasCSVToArray(
    urldecode(
        wasKeyValueGet(
            "data", 
            $return
        )
    )
)[1];

####
# II. Download the map image as a PNG file.
$params = array(
    'command' => 'download',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'item' => $mapUUID,
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
$return = curl_exec($curl);
curl_close($curl);

$success = urldecode(
    wasKeyValueGet(
        "success", 
        $return
    )
);

// Unable to download the region map texture?
if($success == 'False') return -1;

####
# III. Convert the image data to a PNG of size 512x512
$im = imagescale(
    imagecreatefromstring(
        base64_decode(
            rawurldecode(
                wasKeyValueGet(
                    "data", 
                    $return
                )
            )
        )
    ), 
    512, 
    512
);

####
# VI. Output the Base64 encoded image for AJAX.
ob_start();
imagepng($im);
$png = ob_get_contents();
imagedestroy($im);
ob_end_clean();

echo base64_encode($png);

?>

