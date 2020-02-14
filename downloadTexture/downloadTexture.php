<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that can be used to download a texture using Corrade ##
## and the "download" Corrde command.                                    ##
###########################################################################

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

require_once('config.php');
require_once('functions.php');

###########################################################################
##                               INTERNALS                               ##
###########################################################################

if(!isset($_POST['uuid'])) return;
$uuid = $_POST['uuid'];

####
# I. Download the map image as a PNG file.
$params = array(
    'command' => 'download',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'item' => $uuid,
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

if($success == 'False') {
    echo 'Unable to download texture: '.urldecode(
        wasKeyValueGet(
            "success", 
            $return
        )
    );
    die;
}

####
# II. Convert the image data to a PNG of size 512x512
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
# III. Output the Base64 encoded image for AJAX.
ob_start();
imagepng($im);
$png = ob_get_contents();
imagedestroy($im);
ob_end_clean();

echo base64_encode($png);

?>

