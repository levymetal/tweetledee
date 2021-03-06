<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   homejson_pp.php -- Home timeline results formatted as pretty printed JSON
 *   Version: 0.3.7
 * Copyright 2013 Christopher Simpkins
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default home timeline JSON (count = 25 & includes replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/homejson_pp.php
==> User's Home Timeline Pretty Printed JSON parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/homejson_pp.php?c=100
    - 'xrp' - exclude replies (1=true, default = false)
            e.g. http://<yourdomain>/tweetledee/homejson_pp.php?xrp=1
    - Example of all of the available parameters:
            e.g. http://<yourdomain>/tweetledee/homejson_pp.php?c=100&xrp=1
--------------------------------------------------------------------------------------------------*/

/*******************************************************************
*  Debugging Flag
********************************************************************/
$TLD_DEBUG = 0;
if ($TLD_DEBUG == 1){
    ini_set('display_errors', 'On');
    error_reporting(E_ALL | E_STRICT);
}

/*******************************************************************
*  Includes
********************************************************************/
// Matt Harris' Twitter OAuth library
require 'tldlib/tmhOAuth.php';
require 'tldlib/tmhUtilities.php';

// include user keys
require 'tldlib/keys/tweetledee_keys.php';

// include Geoff Smith's utility functions
require 'tldlib/tldUtilities.php';

// include Christian Varga's twitter cache
require 'tldlib/tldCache.php';

/*******************************************************************
*  Defaults
********************************************************************/
$count = 25;  //default tweet number = 25
$exclude_replies = false;  //default to include replies
$cache_interval = 300; // default cache interval = 300 seconds (5 minutes)

/*******************************************************************
*   Parameters
*    - can pass via URL to web server
*    - or as a short or long switch at the command line
********************************************************************/
// Command line parameter definitions //
if (defined('STDIN')) {
    // check whether arguments were passed, if not there is no need to attempt to check the array
    if (isset($argv)){
        $shortopts = "c:";
        $longopts = array(
            "xrp",
        );
        $params = getopt($shortopts, $longopts);
        if (isset($params['c'])){
            if ($params['c'] > 0 && $params['c'] <= 200)
                $count = $params['c'];  //assign to the count variable
        }
        if (isset($params['xrp'])){
            $exclude_replies = true;
        }
        if (isset($params['xrp'])){
            $exclude_replies = true;
        }
        if (isset($params['cache_interval'])){
            $cache_interval = $params['cache_interval'];
        }
    }

} //end if
// Web server URL parameter definitions //
else{
    // c = tweet count ( possible range 1 - 200 tweets, else default = 25)
    if (isset($_GET["c"])){
        $getcount = $_GET["c"];
        if ($getcount > 0 && $getcount <= 200){
            $count = $getcount;
        }
    }

    // xrp = exclude replies from the timeline (possible values: 1=true, else false)
    if (isset($_GET["xrp"])){
        if ($_GET["xrp"] == 1){
            $exclude_replies = true;
        }
    }

    // cache_interval = the amount of time to keep the cached file
    if (isset($_GET["cache_interval"])){
        $cache_interval = $_GET["cache_interval"];
    }
} //end else

/*******************************************************************
*  OAuth
********************************************************************/

$tldCache = new tldCache(array(
            'consumer_key'        => $my_consumer_key,
            'consumer_secret'     => $my_consumer_secret,
            'user_token'          => $my_access_token,
            'user_secret'         => $my_access_token_secret,
            'curl_ssl_verifypeer' => false
        ), $cache_interval);

// request the user information
$data = $tldCache->auth_request();

// Parse information from response
$twitterName = $data['screen_name'];
$fullName = $data['name'];
$twitterAvatarUrl = $data['profile_image_url'];
$feedTitle = ' Twitter home timeline for ' . $twitterName;
$screen_name = $data['screen_name'];


/*******************************************************************
*  Request
********************************************************************/
$homeTimelineObj = $tldCache->user_request(array(
            'url' => '1.1/statuses/home_timeline',
            'params' => array(
                'include_entities' => true,
                'count' => $count,
                'exclude_replies' => $exclude_replies,
            )
        ));

header('Content-Type: application/json');
echo json_encode($homeTimelineObj, JSON_PRETTY_PRINT);
