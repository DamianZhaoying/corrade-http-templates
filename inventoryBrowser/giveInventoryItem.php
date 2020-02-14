<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that gives an inventory item by UUID to an agent     ##
## specified by firstname and lastname.                                  ##
###########################################################################

if(!isset($_POST['uuid']) || 
    !isset($_POST['firstname']) || 
    !isset($_POST['lastname'])) return;

$uuid = $_POST['uuid'];
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

require_once('config.php');
require_once('functions.php');

###########################################################################
##                               INTERNALS                               ##
###########################################################################


####
# I. Send the command to give the avatar the item.
$params = array(
    'command' => 'give',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'item' => $uuid,
    'entity' => 'avatar',
    'firstname' => $firstname,
    'lastname' => $lastname
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

####
# II. Process the success status and display any error.
$success = urldecode(
    wasKeyValueGet(
        "success", 
        $result
    )
);

if($success == 'False') {
    echo 'Unable to give inventory item: '.urldecode(
        wasKeyValueGet(
            "error", 
            $result
        )
    );
    die;
}

?>

