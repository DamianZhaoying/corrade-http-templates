<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that uses Corrade's:                                 ##
##     * getregiondata                                                   ##
##     * getavatarpositions                                              ##
##     * download                                                        ##
## commands in order to display the positions of avatars on the region.  ##
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
# I. Get the UUID of the map image for the current region
$params = array(
    'command' => 'getgridregiondata',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'data' => 'MapImageID'
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

$success = urldecode(
    wasKeyValueGet(
        "success", 
        $return
    )
);

if($success == 'False') {
    echo 'Unable to get the region map image.';
    die;
}

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
curl_setopt($curl, CURLOPT_ENCODING, true);
$return = curl_exec($curl);
curl_close($curl);

if($success == 'False') {
    echo 'Unable to download the region map texture.';
    die;
}


####
# III. Convert the image data to a PNG of size 512x512
$im = imagescale(
    imagecreatefromstring(
        base64_decode(
            urldecode(
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
# IV. Get the avatar positions on the map.
$params = array(
    'command' => 'getavatarpositions',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'entity' => 'region'
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

$success = urldecode(
    wasKeyValueGet(
        "success", 
        $return
    )
);

if($success == 'False') {
    echo 'Unable to get the avatars positions.';
    die;
}


####
# V. Display the coordinates on the map.
array_walk(
    array_chunk(
        wasCSVToArray(
            urldecode(
                wasKeyValueGet(
                    "data", 
                    $return
                )
            )
        ),
        3
    ),
    function(&$value, $key) use(&$im) {
        if(count($value) != 3) return;
        $components = wasLSLVectorToArray($value[2]);
        if(count($components) != 3) return;
        $x = mapValueToRange($components[0], 0, 255, 0, 512);
        $y = 512 - mapValueToRange($components[1], 0, 255, 0, 512);
        imagefilledellipse(
            $im, 
            $x,
            $y, 
            8, 
            8, 
            imagecolorallocate(
                $im, 
                0, 
                0, 
                0
            )   
        );
        imagefilledellipse(
            $im, 
            $x,
            $y, 
            6, 
            6, 
            imagecolorallocate(
                $im, 
                0, 
                255, 
                0
            )
        );
    }
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

