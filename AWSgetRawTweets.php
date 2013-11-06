<?php

//for testing purposes
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);

/*
  gets the tweets from the AWS database
 */

require 'AWSSDKforPHP/aws.phar';
require_once('lib/TwitterSentimentAnalysis.php');

use Aws\DynamoDb\DynamoDbClient;

// Configure  Datumbox API Key. 
define('DATUMBOX_API_KEY', '78006b9cafa5dbd4f0b57f6ae0c897d7');

$client = DynamoDbClient::factory(array(
            'key' => 'AKIAIK7RCPMWTZVQWJIA',
            'secret' => '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',
            'region' => 'us-west-2'
        ));

//get the start value and trackwords which have been sent by the ajax method
if (isset($_GET['start'])) {
    $start = time();
    //$start = mysql_real_escape_string($_GET['start']);
} else {
    $start = time();
}
if (isset($_GET['trackwords'])) {
    
    $trackWords = $_GET['trackwords'];
     
} else {
    //test array
    $trackWords = array('life', 'perfect man'); //testing
}



getRawTweetsFromAWS($client, $start);


/*
 * function to get the tweets from the AWS database, passing the start value from the browser (if there is one)
 * else defaults to the last start value - ie the last tweet in the database
 */

function getRawTweetsFromAWS($client, $start_value) {

    $tableName = 'MSrawTweets';

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
    
    //print "rangeID startValue: " .$start_value."<br><br>";
       
    //var_dump($result);
    $jsonObj = $result['Items'];
    
    //filter required tweets
    filterTweets($client, $jsonObj);
}

/*
 * function to filter the tweets from the AWS database, passing the start value from the browser (if there is one)
 * else defaults to the last start value - ie the last tweet in the database
 */

function filterTweets($client, array $jsonObj) {
    
    $array = array();
    
    global $trackWords;
    
    foreach($jsonObj as $item) {
        //get tweet
        $jsonTweetDecode = (array) json_decode($item['rawTweet']['S']);
        
        //get range and created at
        $rangeId = $item['rangeId']['N'];
        $created_at = date("D M j G:i:s" , $item['rangeId']['N']);

        //apply filter to user given trackword(s)
        foreach($trackWords as $trackWord) {
            //check if tweet text contains trackword(s)
            if((stripos($jsonTweetDecode['text'], $trackWord) != false) || (stripos($jsonTweetDecode['text'], str_replace(' ', '', $trackWord)) != false)) {
                //get user details
                $jsonTweetUserDecode = (array) $jsonTweetDecode['user'];
                //get tweet place details
                $jsonTweetLocationDecode = (array) $jsonTweetDecode['place'];
                
                //get the location
                if($jsonTweetLocationDecode == null) {
                    $tweetLocation = "n/a";
                } else {
                    $tweetLocation = $jsonTweetLocationDecode[full_name];
                }
                
                //get the sentiment 
                $TwitterSentimentAnalysis = new TwitterSentimentAnalysis(DATUMBOX_API_KEY);
                $tweetSentiment = addslashes($TwitterSentimentAnalysis->sentimentAnalysis($jsonTweetDecode['text']));
                
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
                array_push($array, $tweetArray);    
            }
        }
        $jsonArray = json_encode($array); 
    }
    print $jsonArray;
    
    //insert filtered tweets in the tweets table
    insertTweets($client, $array);
}

function insertTweets($client, array $array) {
    
    foreach($array as $tweet) {
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

        $tableName = 'tweets';

        
        //We store the new post in the database, to be able to read it later
        //insert into AWS dynamoDb
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
}

function contains( $string, array $search, $caseInsensitive=false ){
    $exp = '/'.implode('|',array_map('preg_quote',$search)).($caseInsensitive?'/i':'/');
    return preg_match($exp, $string)?true:false;
}

?>

