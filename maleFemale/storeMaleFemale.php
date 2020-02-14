<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
## This is a script that listens for Corrade's "terse" notification and  ##
## then uses Corrade's "getavatardata" command in order to determine the ##
## shape gender and to store that data as a CSV of UUIDs by gender.      ##
###########################################################################

# Check if this is the terse notification, otherwise bail.
if(!isset($_POST['type']) || $_POST['type'] != 'avatars') return;
# Check if this is an avatar terse notification, otherwise bail.
if(!isset($_POST['entity']) || $_POST['entity'] != 'Avatar') return;

###########################################################################
##                            CONFIGURATION                              ##
###########################################################################

# Set this to the name of the group.
$GROUP = 'My Group';
# Set this to the group password.
$PASSWORD = 'mypassword';
# Set this to Corrade's HTTP Server URL.
$URL = 'http://corrade.local.site:8080';
# Visitors file.
$VISITOR_FILE = 'visitors.log';

###########################################################################
##                               INTERNALS                               ##
###########################################################################

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
function wasKeyValueGet($key, $data) {
    return array_reduce(
        explode(
            "&", 
            $data
        ),
        function($o, $p) {
            $x = explode("=", $p);
            return array_shift($x) != $o ? $o : array_shift($x);
        },
        $key
    );
}

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
function atomized_put_contents($file, $data) {
    $fp = fopen($file, "w+");
    if (flock($fp, LOCK_EX)) {
        fwrite($fp, $data);
        fflush($fp);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
}

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      ##
###########################################################################
function atomized_get_contents($file) {
    $fp = fopen($file, "r+");
    $ct = '';
    if (flock($fp, LOCK_SH)) {
        if (filesize($file)) {
            $ct = fread($fp, filesize($file));
        }
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    return $ct;
}

///////////////////////////////////////////////////////////////////////////
//  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      //
///////////////////////////////////////////////////////////////////////////
function wasCSVToArray($csv) {
    $l = array();
    $s = array();
    $m = "";
    for ($i = 0; $i < strlen($csv); ++$i) {
        switch ($csv{$i}) {
            case ',':
                if (sizeof($s) == 0 || !current($s) == '"') {
                    array_push($l, $m);
                    $m = "";
                    break;
                }
                $m .= $csv{$i};
                continue;
            case '"':
                if ($i + 1 < strlen($csv) && $csv{$i} == $csv{$i + 1}) {
                    $m .= $csv{$i};
                    ++$i;
                    break;
                }
                if (sizeof($s) == 0|| !current($s) == $csv[$i]) {
                    array_push($s, $csv{$i});
                    continue;
                }
                array_pop($s);
                break;
            default:
                $m .= $csv{$i};
                break;
        }
    }
    array_push($l, $m);
    return $l;
}

///////////////////////////////////////////////////////////////////////////
//  Copyright (C) Wizardry and Steamworks 2015 - License: GNU GPLv3      //
///////////////////////////////////////////////////////////////////////////
function wasArrayToCSV($a) {
    return implode(
        ',',
        array_map(
            function($o) {
                $o = str_replace('"', '""', $o);
                switch(
                    (strpos($o, ' ') !== FALSE) ||
                    (strpos($o, '"') !== FALSE) ||
                    (strpos($o, ',') !== FALSE) ||
                    (strpos($o, '\r') !== FALSE) ||
                    (strpos($o, '\n') !== FALSE)
                )
                {
                    case TRUE:
                        return '"' . $o . '"';
                    default:
                        return $o;
                }
            },
            $a
        )
    );
}

$visitors = array();
if(file_exists($VISITOR_FILE)) {
    $visitors = explode(
        PHP_EOL, 
        atomized_get_contents(
            $VISITOR_FILE
        )
    );
    array_walk(
        $visitors, 
        function($e, $k) {
            if(wasCSVToArray($e)[0] == $_POST['id']) die;
        }
    );
}

# This constructs the command as an array of key-value pairs.
$params = array(
    'command' => 'getavatardata',
    'group' => $GROUP,
    'password' => $PASSWORD,
    'agent' => $_POST['id'],
    'data' => 'VisualParameters'
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
$return = curl_exec($curl);
curl_close($curl);

$success = urldecode(
    wasKeyValueGet(
        "success", 
        $return
    )
);

if($success == 'False') {
    # DEBUG: This will be triggered if getting the avatar data fails.
    #print $uuid." ".urldecode(
    #    wasKeyValueGet(
    #        'error', 
    #        $return
    #    )
    #)."\n";
    die;
}

$visual = wasCSVToArray(
    urldecode(
        wasKeyValueGet(
            "data", 
            $return
        )
    )
);

$new = array();
array_push($new, $_POST['id']);
switch($visual[32]) {
    case 0:
        # DEBUG
        #print $uuid.' is female '."\n";
        array_push($new, 'female');
        break;
    default:
        # DEBUG
        #print $uuid.' is male '."\n";
        array_push($new, 'male');
        break;
}

array_push($visitors, wasArrayToCSV($new));

atomized_put_contents(
    $VISITOR_FILE, 
    implode(
        PHP_EOL, 
        $visitors
    )
);


?>