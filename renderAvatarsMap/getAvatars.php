<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that uses Corrade to get the avatar positions and    ##
## then aggregates that data to a JSON object that will be read by AJAX. ##
##                                                                       ##
## The output JSON object that will be read via AJAX is of the form:     ##
## {                                                                     ##
##    header: ["x", "y", "z"],                                           ##
##    positions: [[x1, y1, z1], [x2, y2, z2], ...],                      ##
##    names: ["First Resident", "Second Resident", ...],                 ##
##    colors: [[0.5, 1, 0.75], [0.3, 0.55, 0.34], ...]                   ##
## }                                                                     ##
###########################################################################

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

# The configuration file for this script containing the settings.
require_once("config.php");
require_once("functions.php");

###########################################################################
##                               INTERNALS                               ##
###########################################################################

####
# I. Get the avatar positions.
$params = array(
    'command' => 'getavatarpositions',
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
    print 0;
    return;
}

curl_setopt($curl, CURLOPT_URL, $URL);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $postvars);
$result = curl_exec($curl);
curl_close($curl);

$success = urldecode(
    wasKeyValueGet(
        "success", 
        $result
    )
);

// Unable to get avatar positions?
if($success == 'False') return -1;

$data = wasCSVToArray(
    urldecode(
        wasKeyValueGet(
            "data", 
            $result
        )
    )
);

####
# II. Construct and echo the JSON object.
echo json_encode(
    array(
        'header' => array('x', 'y', 'z'),
        'positions' => array_values(
            array_map(
                function($e) {
                    $e = preg_replace('/</', '', $e);
                    $e = preg_replace('/>/', '', $e);
                    $e = explode(',', $e);
                    return array($e[0], $e[1], $e[2]);
                },
                wasArrayStride(
                    array_slice($data, 2), 
                    3
                )
            )
        ),
        'names' => array_values(
            wasArrayStride(
                $data, 
                3
            )
        ),
        'colors' => array_values(
            array_map(
                function($e) {
                    $e = hex2RGB(wasColorHash($e));
                    return array(
                        round (
                            mapValueToRange($e['red'], 0, 255, 0, 1), 2
                        ), 
                        round(
                            mapValueToRange($e['green'], 0, 255, 0, 1), 2
                        ), 
                        round(
                            mapValueToRange($e['blue'], 0, 255, 0, 1), 2
                        )
                    );
                },
                wasArrayStride(
                    $data, 
                    3
                )
            )
        )
    ),
    JSON_NUMERIC_CHECK
);

return 0;

?>

