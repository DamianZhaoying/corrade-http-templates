<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that moves a source directory to a target directory  ##
## by UUID in Corrade's inventory.                                       ##
###########################################################################

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

require_once('config.php');
require_once('functions.php');

###########################################################################
##                               INTERNALS                               ##
###########################################################################

if(!isset($_POST['source']) || !isset($_POST['target'])) return;

$source = $_POST["source"];
$target = $_POST["target"];

###########################################################################
# jstree uses the hash sign (#) as the root, so normalize it             ##
###########################################################################
if($source == "#")
    $source = '/';

if($target == "#")
    $target = '/';

###########################################################################
# Moving root to root is funny.                                          ##
###########################################################################
if($source == '/' && $target == '/')
    return;

###########################################################################
# This template uses UUIDs to refer to inventory items and folders such  ##
# that the source and the target path-parts must be valid v4 UUIDs.      ##
###########################################################################
$isSanePath = TRUE;
array_walk(
    array_pop(
        explode('/',
            $source
        )
    ), function($part) use(&$isSanePath) {
        if(!preg_match("^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$", $part))
            $isSanePath = FALSE;
    }
);

if($isSanePath == FALSE)
    return;

$isSanePath = TRUE;
array_walk(
    array_pop(
        explode('/',
            $target
        )
    ), function($part) use(&$isSanePath) {
        if(!preg_match("^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$", $part))
            $isSanePath = FALSE;
    }
);

if($isSanePath == FALSE)
    return;

###########################################################################
##                      CHECK FOR SYSTEM FOLDERS                         ##
###########################################################################
####
# I. Check that the source item is not a system folder.
# This is to prevent accidental moves of the system folders.
if($source != '/') {
    $params = array(
        'command' => 'getinventorydata',
        'group' => $GROUP,
        'password' => $PASSWORD,
        'item' => array_pop(
            explode('/', $source)
        ),
        'data' => wasArrayToCSV(
            array(
                'AssetType',
                'PreferredType'
            )
        )
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
        return;
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

    switch($data['PreferredType']) {
        case 'RootFolder':
        case 'TrashFolder':
        case 'SnapshotFolder':
        case 'LostAndFoundFolder':
        case 'FavoriteFolder':
        case 'LinkFolder':
        case 'CurrentOutfitFolder':
        case 'OutfitFolder':
        case 'MyOutfitsFolder':
            return;
        break;
    }
}

####
# II. Check that the target item is not a system folder.
# This is to prevent accidental moves of the system folders.
if($target != '/') {
    $params = array(
        'command' => 'getinventorydata',
        'group' => $GROUP,
        'password' => $PASSWORD,
        'item' => array_pop(
            explode('/', $target)
        ),
        'data' => wasArrayToCSV(
            array(
                'AssetType',
                'PreferredType'
            )
        )
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
        return;
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

    switch($data['PreferredType']) {
        case 'RootFolder':
        case 'TrashFolder':
        case 'SnapshotFolder':
        case 'LostAndFoundFolder':
        case 'FavoriteFolder':
        case 'LinkFolder':
        case 'CurrentOutfitFolder':
        case 'OutfitFolder':
        case 'MyOutfitsFolder':
            return;
        break;
    }
}

###########################################################################
##                                MOVE ITEM                              ##
###########################################################################

####
# III. Send the command to Corrade to move the source into the target.
$params = array(
    'command' => 'inventory',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'action' => 'mv',
    'source' => $source,
    'target' => $target
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
# IV. Grab the status of the command.
$success = urldecode(
    wasKeyValueGet(
        "success", 
        $result
    )
);

if($success == 'False') {
    echo 'Unable to move item: '.urldecode(
        wasKeyValueGet(
            "error", 
            $result
        )
    );
    return;
}

echo "success";

?>

