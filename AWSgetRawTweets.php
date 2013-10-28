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

//get the start value which has been sent by the ajax method
if (isset($_GET['start'])) {
    $start = time();
    //$start = mysql_real_escape_string($_GET['start']);
} else {
    $start = time();
}


getRawTweetsFromAWS($client, $start);


/*
 * function to get the tweets from the AWS database, passing the start value from the browser (if there is one)
 * else defaults to the last start value - ir the last tweet in the database
 */

function getRawTweetsFromAWS($client, $start_value) {

    $tableName = 'MSrawTweets';

    $result = $client->query(array(
        'TableName' => $tableName,
        'Limit' => 3,
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
    
    $array = array();
    $keywords = array('perfect', 'emosi');
    for($i=0; $i<3; $i++) {
        $jsonRangeDecode = json_decode($result['Items'][$i]['rangeId']['N']);
        $jsonTweetDecode = (array) json_decode($result['Items'][$i]['rawTweet']['S']);
        $jsonTweetUserDecode = (array) $jsonTweetDecode['user'];

        if (contains($jsonTweetDecode['text'], $keywords, $caseInsensitive=false ) == true) {
            $tweetArray = array("id"=>$jsonTweetDecode['id_str'], "text"=>$jsonTweetDecode['text'], "range_id"=>$jsonRangeDecode, "screen_name"=>$jsonTweetUserDecode['screen_name'], "profile_image_url"=>$jsonTweetUserDecode['profile_image_url'], "followers_count"=>$jsonTweetUserDecode['followers_count']);
            //$tweetNo = array($count => $tweetArray);
            //print_r($tweetNo);
            //$jsonArray = json_encode($tweetNo);
            array_push($array, $tweetArray);    
        }
        $jsonArray = json_encode($array); 
    }
    print_r($jsonArray);

//    foreach($array as $tweet) {
//        //Clean the inputs before storing
//        $twitterId = addslashes($tweet['id']);
//        $text = addslashes($tweet['text']);
//        $screen_name = addslashes($tweet['screen_name']);
//        $profile_image_url = addslashes($tweet['profile_image_url']);
//        $followers_count = addslashes($tweet['followers_count']);
//
//        //idexId and the created_at time
//        $indexId = 'tweets';
//        $rangeId = $tweet['range_id'];
//        $created_at = date("D M j G:i:s" , $rangeId);
//
//        $tableName = 'tweets';
//
//        //get the sentiment 
//        $TwitterSentimentAnalysis = new TwitterSentimentAnalysis(DATUMBOX_API_KEY);
//        $sentiment = addslashes($TwitterSentimentAnalysis->sentimentAnalysis($text));
//        
//        //We store the new post in the database, to be able to read it later
//        //insert into AWS dynamoDb
//        $insertResult = $client->putItem(array(
//            'TableName' => $tableName,
//            'Item' => array(
//                'indexId' => array('S' => $indexId),
//                'rangeId' => array('N' => $rangeId),
//                'twitter_id' => array('N' => $twitterId),
//                'created_at' => array('S' => $created_at ),
//                'text' => array('S' => $text),
//                'screen_name' => array('S' => $screen_name),
//                'profile_image_url' => array('S' => $profile_image_url),
//                'followers_count' => array('N' => $followers_count),
//                'sentiment' => array('S' => $sentiment)
//                ),
//        ));
//    }
}

function contains( $string, array $search, $caseInsensitive=false ){
    $exp = '/'.implode('|',array_map('preg_quote',$search)).($caseInsensitive?'/i':'/');
    return preg_match($exp, $string)?true:false;
}

?>

