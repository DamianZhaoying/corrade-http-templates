<?php
 
###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that uses Corrade's "getterrainheight" command in    ##
## order to retrieve the maximal terrain height of a region.             ##
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

####
# II. Get the array of terrain heights and output the maximum value.
echo max(
    str_getcsv(
        urldecode(
            wasKeyValueGet(
                "data", 
                $return
            )
        )
    )
);

return 0;

?>