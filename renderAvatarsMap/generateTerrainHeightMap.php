<?php
 
###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that uses Corrade's "getterrainheight" command in    ##
## order to genereate a red-channel height-map of the terrain without    ##
## the need to be an estate / region owner. Essentially this script can  ##
## be used to give an overview of the terrain height for any region.     ##
###########################################################################

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

# The configuration file for this script containing the settings.
require_once("config.php");
require_once('functions.php');

###########################################################################
##                               INTERNALS                               ##
###########################################################################

####
# I. Get the terrain height.
$params = array(
    'command' => 'getterrainheight',
    'group' => $GROUP,
    'entity' => 'region',
    'password' => $PASSWORD
);
 
# We now escape each key and value: this is very important, because the 
# data we send to Corrade may contain the '&' or '=' characters (we don't 
# in this example though but this will not hurt anyone).
array_walk($params,
 function(&$value, $key) {
     $value = urlencode($key)."=".urlencode($value);
 }
);
$postvars = implode('&', $params);
 
# Set the options, send the request and then display the outcome
if (!($curl = curl_init())) {
    echo "Could not initialise CURL".PHP_EOL;
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

// Unable to get the region heights?
if($success == 'False') return -1;

$map = str_getcsv(
    urldecode(
        wasKeyValueGet(
            "data", 
            $return
        )
    )
);

####
# II. If we did not have exactly 256 x 256 array elements, then abort. 
if(count($map) != 65536) return -1;

// Find the maximal height of the terrain.
$max = max($map);

####
# III. Create a new image by encoding the elevations to the red channel.
$im = imagecreatetruecolor(256, 256);
foreach(range(0, 255) as $x) {
    foreach(range(0, 255) as $y) {
        $red = mapValueToRange(
            $map[256 * $x + $y], 
            0,
            $max,
            0,
            256
        );
        imagesetpixel(
            $im,
            $x,
            256-$y,
            imagecolorallocate(
                $im,
                $red,
                0,
                0
            )
        );
    }
}



####
# IV. Output the image as Base64 encoded data.
ob_start();
imagepng($im);
$png = ob_get_contents();
imagedestroy($im);
ob_end_clean();

echo base64_encode($png);

?>