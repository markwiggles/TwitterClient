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

//get the start value and trackwords which have been sent by the ajax method
if (isset($_GET['start'])) {

    $start = (int) $_GET['start'];
    $comparision = 'GT';
} else {
    $start = time();
    $comparision = 'LE';
}

$_GET['trackWords'] = "the"; //for testing

if (isset($_GET['trackWords'])) {

    //trackwords to array
    $words = str_replace("%", " ", $_GET['trackWords']);
    $trackWords = explode(',', $words);
} else {
    //test array
    $trackWords = array('the'); //testing
}


getTweetsFromAWS($client, $start, $comparision);


/*
 * function to get the tweets from the AWS database, 
 * passing the start value from the browser (if there is one)
 * else defaults to the last start value - ie the last tweet in the database
 */

function getTweetsFromAWS($client, $start_value, $comparision) {

    $tableName = 'MStweets';

    $result = $client->query(array(
        'TableName' => $tableName,
        'Limit' => 10,
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
                'ComparisonOperator' => $comparision
            )
        ),
        'ScanIndexForward' => false
    ));
    filterRawTweets($result);
}

/*
 * function to filter the tweets from the DynamoDB AWS database
 * using the result from the range query and filtering on trackWords (global variable)
 */
function filterRawTweets($result) {
    
    $tweets = $result['Items']; //the tweets 
    global $trackWords; //the trackWords to find
    $answers = array(); //the answers to pass on
    
    for ($i = 0; $i < $result['Count']; $i++) { //going through the tweets
        $countFind = 0;
        foreach ($trackWords as $trackWord) { //check for trackWords
            if (stripos($tweets[$i]['text']['S'], $trackWord)) {
                $countFind++;
            }  
        }        
        if($countFind > 0) {
            array_push($answers,$tweets[$i]);//place answer in array
        }       
    }//end for loop 
    $answers = array_reverse($answers);
    
    print json_encode($answers); //send off the answers
}


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

