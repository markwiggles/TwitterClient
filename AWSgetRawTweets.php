<?php

//For testing purposes
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);

/*
  Gets the tweets from the MSrawTweets table of DynamoDB
 */

require 'AWSSDKforPHP/aws.phar';
require_once('lib/TwitterSentimentAnalysis.php');

use Aws\DynamoDb\DynamoDbClient;

//Configure  Datumbox API Key. 
define('DATUMBOX_API_KEY', 'f170aec75a2c1270b7ad451ddd07db79');

//Setup client connection to DynamoDB
$client = DynamoDbClient::factory(array(
            'key' => 'AKIAIK7RCPMWTZVQWJIA',
            'secret' => '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',
            'region' => 'us-west-2'
        ));

//Get the start value which have been sent by the ajax method
if (isset($_GET['start'])) {
    $start = time();
    //$start = mysql_real_escape_string($_GET['start']);
} else {
    $start = time();
}

//Get the trackwords which have been sent by the ajax method
if (isset($_GET['trackWords'])) {
    //needs to go to array
    //$trackWords = $_GET['trackWords'];
    
    $trackWords = array('life'); //testing
     
} else {
    //test array
    $trackWords = array('life'); //testing
}

//Function call to begin retrieving, filtering & storing tweets
getRawTweets($client, $start);


/*
 * Function to get the tweets from the AWS database, 
 * passing the start value from the browser 
 * (if there is one) else defaults to the last 
 * start value - ie the last tweet in the database
 */
function getRawTweets($client, $start_value) {
    //Name of the DynamoDB table which stores
    //raw tweets from the twitter stream
    $tableName = 'MSrawTweets';
    
    //Connect to DynamoDB and retrieve last 100 rows
    //from MSrawTweets table
    $result = $client->query(array(
        'TableName' => $tableName,
        'Limit' => 100,
        'KeyConditions' => array(
            'indexId' => array(
                'AttributeValueList' => array(
                    array('S' => 'tweets')
                ),
                'ComparisonOperator' => 'EQ'
            ),

            'rangeId' => array(
                'AttributeValueList' => array(
                    array('N' => $start_value),
                    
                ),
                'ComparisonOperator' => 'LE'
            )
        ),
        'ScanIndexForward' => false
    ));
    
    //Store the entire result set from the 'Query'
    $resultObj = $result['Items'];
    
    //Function call to filter the tweets
    filterTweets($client, $resultObj);
}//end of getRawTweets

/*
 * Function to filter the tweets from the AWS database, 
 * based on the 100 results returned from getRawTweets()
 */
function filterTweets($client, array $resultObj) {
    //An array to store the filtered tweets based
    //on the given trackword(s)
    $filteredArray = array();
    
    global $trackWords;
    
    //Loop through each of the tweets returned from
    //getRawTweets() 
    foreach($resultObj as $item) {
        //Get range (timestamp as a number) and 
        //created at (formatted range)
        $rangeId = $item['rangeId']['N'];
        $created_at = date("D M j G:i:s" , $item['rangeId']['N']);
        
        //Get all the tweet related content
        //This includes text, followers count etc
        $jsonTweetDecode = (array) json_decode($item['rawTweet']['S']);

        //Apply filter to user given trackword(s)
        foreach($trackWords as $trackWord) {
            //Check if tweet text contains the trackword(s)
            //The check is performed by querying with the trackword
            //with and without white spaces
            if((stripos($jsonTweetDecode['text'], $trackWord) != false) || (stripos($jsonTweetDecode['text'], str_replace(' ', '', $trackWord)) != false)) {
                //Get user details
                $jsonTweetUserDecode = (array) $jsonTweetDecode['user'];
                //Get tweet location details
                $jsonTweetLocationDecode = (array) $jsonTweetDecode['place'];
                
                //Check if location available or not
                if($jsonTweetLocationDecode == null) {
                    $tweetLocation = "n/a";
                } else {
                    $tweetLocation = $jsonTweetLocationDecode['full_name'];
                }
                
                //Get the sentiment using Datumbox API
                $TwitterSentimentAnalysis = new TwitterSentimentAnalysis(DATUMBOX_API_KEY);
                $tweetSentiment = addslashes($TwitterSentimentAnalysis->sentimentAnalysis($jsonTweetDecode['text']));
                
                //Stored the tweet content in an array
                $tweetArray = array("text"=>$jsonTweetDecode['text'],
                    "indexId"=>'tweets', 
                    "rangeId"=>$rangeId, 
                    "profile_image_url"=>$jsonTweetUserDecode['profile_image_url'], 
                    "sentiment"=>$tweetSentiment, 
                    "created_at"=>$created_at, 
                    "twitter_id"=>$jsonTweetDecode['id_str'], 
                    "screen_name"=>$jsonTweetUserDecode['screen_name'], 
                    "followers_count"=>$jsonTweetUserDecode['followers_count'], 
                    "location"=>$tweetLocation);
                //Store arrays of filtered tweets 
                array_push($filteredArray, $tweetArray);    
            }
        }
        //Perform JSON encoding of the array to pass
        //to jQuery method
        $jsonArray = json_encode($filteredArray); 
    }
    
    //Insert filtered tweets in the tweets table
    //of DynamoDB
    insertTweets($client, $filteredArray);
    
    //Send JSON encoded array to jQuery method
    print $jsonArray;
}//end of filterTweets()

/*
 * Function to insert the filtered tweets into 
 * tweets table of DynamoDB
 */
function insertTweets($client, array $filteredArray) {
    //Retrieve tweet content to store in columns of
    //tweets table
    foreach($filteredArray as $tweet) {
        //Clean the inputs before storing
        $twitterId = addslashes($tweet['twitter_id']);
        $text = addslashes($tweet['text']);
        $screen_name = addslashes($tweet['screen_name']);
        $profile_image_url = addslashes($tweet['profile_image_url']);
        $followers_count = addslashes($tweet['followers_count']);
        $sentiment = addslashes($tweet['sentiment']);
        $location = addslashes($tweet['location']);
                
        //indexId and the created_at time
        $indexId = $tweet['indexId'];
        $rangeId = $tweet['rangeId'];
        $created_at = $tweet['created_at'];

        //Name of the DynamoDB table which stored
        //filtered tweet content
        $tableName = 'tweets';

        //We store the new post in the database, to be able to read it later
        //Insert into AWS dynamoDb
        $insertResult = $client->putItem(array(
            'TableName' => $tableName,
            'Item' => array(
                'indexId' => array('S' => $indexId),
                'rangeId' => array('N' => $rangeId),
                'twitter_id' => array('N' => $twitterId),
                'created_at' => array('S' => $created_at ),
                'text' => array('S' => $text),
                'screen_name' => array('S' => $screen_name),
                'profile_image_url' => array('S' => $profile_image_url),
                'followers_count' => array('N' => $followers_count),
                'sentiment' => array('S' => $sentiment),
                'location' => array('S' => $location)
                ),
        ));
    }
}//end of insertTweets()

?>

