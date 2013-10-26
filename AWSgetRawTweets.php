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

use Aws\DynamoDb\DynamoDbClient;

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
        'Limit' => 2,
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
    
    print "rangeID startValue: " .$start_value."<br><br>";
       
    //var_dump($result);
    
    //send the json file out
    $jsonObj= $result['Items'];
    
    //print_r ($jsonObj);
    
   //convert array to json
   $jsonArray = json_encode($jsonObj);
   
   print_r ($jsonArray);


}



?>

