<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that uses Corrade's "getregiondata" command in order ##
## to retrieve statistics on the current simulator.                      ##
###########################################################################

if(!isset($_POST["folder"])) return;

$inventoryFolder = $_POST["folder"];

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

require_once('config.php');
require_once('functions.php');

###########################################################################
##                               INTERNALS                               ##
###########################################################################

####
# I. First call will always return "My Inventory" and "Library" for root.
if($inventoryFolder == 'init') {

  echo <<< EOL
[
      { "id" : "/My Inventory", "parent" : "#", "text" : "My Inventory", "data" : { "type" : "folder" }, "children" : true, "opened" : false },
      { "id" : "/Library", "parent" : "#", "text" : "Library", "data" : { "type" : "folder" }, "children" : true, "opened" : false }
]
EOL;

  return;
}

####
# II. Send the "inventory" command to list the folder contents.
$params = array(
    'command' => 'inventory',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'action' => 'ls',
    'path' => $inventoryFolder
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

# Set the options, send the request and then display the outcome
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

####
# III. Return if the command did not succeed or the data is empty.
$success = urldecode(
    wasKeyValueGet(
        "success", 
        $result
    )
);

if($success == "False")
    return;

$data = urldecode(
    wasKeyValueGet(
        "data",
        $result
    )
);

if(!trim($data))
    return;

####
# IV. Walk through the CSV list of the items in the directory and build a
# jstree-compatible array to be passed back to the javascript.
$contents = array();
array_walk(
    array_chunk(
        str_getcsv($data),
            10
    ),
    function($item) use(&$contents, $inventoryFolder) {
        $data = array_combine(
            wasArrayStride(
                $item,
                2
            ),
            wasArrayStride(
                array_slice(
                    $item,
                    1
                ),
            2
            )
        );
        array_push($contents,
            array(
                "id" => $inventoryFolder == '/' ? '/'.$data['item'] : $inventoryFolder.'/'.$data['item'],
                "parent" => $inventoryFolder,
                "data" => array(
                    'type' => strtolower($data['type']),
                    'time' => $data['time']
                ),
                "text" => $data['name'],
                "children" => strcasecmp($data['type'], 'folder') == 0,
                "icon" => 'images/icons/'.strtolower($data['type']).'.png',
                "opened" => "false"
            )
        );
    }
);

####
# V. Dump the array to JSON to be processed by jstree.
echo json_encode($contents);

?>

