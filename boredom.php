<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="chrome=1">
    <title>Bored Today</title>

    <link rel="stylesheet" href="boredtoday.css">
    <script src="https://maps.googleapis.com/maps/api/js"></script>
    <script src="boredtoday.js" type="text/javascript"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  
  </head>
  <body>
    <h1>Bored Today? Here's Somewhere For You To Go</h1>

<?php
/**
 * Yelp API v2.0 code sample.
 *
 * This program demonstrates the capability of the Yelp API version 2.0
 * by using the Search API to query for businesses by a search term and location,
 * and the Business API to query additional information about the top result
 * from the search query.
 * 
 * Please refer to http://www.yelp.com/developers/documentation for the API documentation.
 * 
 * This program requires a PHP OAuth2 library, which is included in this branch and can be
 * found here:
 *      http://oauth.googlecode.com/svn/code/php/
 * 
 * Sample usage of the program:
 * `php sample.php --term="bars" --location="San Francisco, CA"`
 */
// Enter the path that the oauth library is in relation to the php file
require_once('OAuth.php');
// Set your OAuth credentials here  
// These credentials can be obtained from the 'Manage API Access' page in the
// developers documentation (http://www.yelp.com/developers)
$CONSUMER_KEY = '4TrZPvNHDTQrgfPF3yQAXQ';
$CONSUMER_SECRET = 'gEcc0LEUsneDbhZQGNIWeldbqqU';
$TOKEN = 'Qq0sE3x5OP3Pe-znqxSlXX4gbJi8dPQ9';
$TOKEN_SECRET = 'etRWM4Tjqbwh7b8bv-Z_OSeKrQQ';
$API_HOST = 'api.yelp.com';
$DEFAULT_TERM = 'attraction';
$country=file_get_contents('http://api.hostip.info/get_html.php?ip=');
$DEFAULT_LOCATION = substr($country, strpos($country, "City")+6, strpos($country, "City")-strpos($country, "IP")-2);
$SEARCH_LIMIT = 20;
$SEARCH_PATH = '/v2/search/';
$BUSINESS_PATH = '/v2/business/';
$url = "";
$img = "";
/** 
 * Makes a request to the Yelp API and returns the response
 * 
 * @param    $host    The domain host of the API 
 * @param    $path    The path of the APi after the domain
 * @return   The JSON response from the request      
 */
function request($host, $path) {
    $unsigned_url = "http://" . $host . $path;
    // Token object built using the OAuth library
    $token = new OAuthToken($GLOBALS['TOKEN'], $GLOBALS['TOKEN_SECRET']);
    // Consumer object built using the OAuth library
    $consumer = new OAuthConsumer($GLOBALS['CONSUMER_KEY'], $GLOBALS['CONSUMER_SECRET']);
    // Yelp uses HMAC SHA1 encoding
    $signature_method = new OAuthSignatureMethod_HMAC_SHA1();
    $oauthrequest = OAuthRequest::from_consumer_and_token(
        $consumer, 
        $token, 
        'GET', 
        $unsigned_url
    );
    
    // Sign the request
    $oauthrequest->sign_request($signature_method, $consumer, $token);
    
    // Get the signed URL
    $signed_url = $oauthrequest->to_url();
    
    // Send Yelp API Call
    $ch = curl_init($signed_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    
    return $data;
}
/**
 * Query the Search API by a search term and location 
 * 
 * @param    $term        The search term passed to the API 
 * @param    $location    The search location passed to the API 
 * @return   The JSON response from the request 
 */
function search($term, $location) {
    $url_params = array();
    
    $url_params['term'] = $term ?: $GLOBALS['DEFAULT_TERM'];
    $url_params['location'] = $location?: $GLOBALS['DEFAULT_LOCATION'];
    $url_params['limit'] = $GLOBALS['SEARCH_LIMIT'];
    $search_path = $GLOBALS['SEARCH_PATH'] . "?" . http_build_query($url_params);
    
    return request($GLOBALS['API_HOST'], $search_path);
}
/**
 * Query the Business API by business_id
 * 
 * @param    $business_id    The ID of the business to query
 * @return   The JSON response from the request 
 */
function get_business_name($business_id) {
    $business_path = $GLOBALS['BUSINESS_PATH'] . $business_id;
    
    $request = request($GLOBALS['API_HOST'], $business_path);
    return $request;
}
/**
 * Queries the API by the input values from the user 
 * 
 * @param    $term        The search term to query
 * @param    $location    The location of the business to query
 */
function query_api($term, $location) {     
    $response = json_decode(search($term, $location));
    $ids = array();
    for($i =0; $i<$GLOBALS['SEARCH_LIMIT']; $i++) {
        $id =$response->businesses[$i]->id;
        array_push($ids, $id); 
        
    }
    $num =mt_rand(0, count($ids)-1);
    print($num);
    $responsee = get_business_name($ids[$num]);
    $json = json_decode($responsee);
    $name = $json->name;
    $rating = $json->rating;
    $location = $json->location;
    $img = $json->image_url;
    $location = json_decode(json_encode($location, true));
    $add = $location->display_address;

    foreach ($add as $ad) {
        if(is_null($address)) {
            $address = $ad;
        }else {
        $address = $address."'%2C%'".$ad;
        }
    }
    $address = str_replace(" ", '%20', $address);
    
    $url = "https://www.google.com/maps/embed/v1/place?q=".$address."&key=AIzaSyDunxkbh0Nr7LiIhQ7aDdxGH-EZWDzLaS8";
    ?>

<iframe width="600" height="450" frameborder="0" style="border:0" src=<?=$url?>></iframe>

        <div id="business">
            <h2><?= $name ?></h2>
            <img src=<?= $img ?>>
        </div>
<?php
}
/**
 * User input is handled here 
 */
$longopts  = array(
    "term::",
    "location::",
);
    
$options = getopt("", $longopts);
$term = $options['term'] ?: '';
$location = $options['location'] ?: '';
query_api($term, $location);
?>
  </body>
</html>
