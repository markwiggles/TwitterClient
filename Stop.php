<?php

/*
 * file to process form data from index.php, 
 * executes the code to stop the long running process
 * which gets tweets from the server
 * 
 * Parameter: $pid - the process identifier
 */

    //for testing purposes
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);


require 'AWSSDKforPHP/aws.phar';

use Aws\DynamoDb\DynamoDbClient;

$client = DynamoDbClient::factory(array(
            'key' => 'AKIAIK7RCPMWTZVQWJIA',
            'secret' => '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',
            'region' => 'us-west-2'
        ));

$pid = $_POST['stop'];
$lastValue = $_POST['last'];

//update the database wit the last start value
updateStartValue($client, $lastValue);

exec("kill $pid");

//send a message to the browser
print "stopped tweets with pid:" . $pid;

/*
 * Func
 */

function updateStartValue ($client, $lastValue) {

    $result = $client->updateItem(array(
    'TableName' => 'start_value',
    'Key' => array(
        'start' => array('S' => 'start')
    ),
    'AttributeUpdates' => array(
        'value' => array(
            'Action' => 'PUT',
            'Value' => array('N' => $lastValue)
        )
    ),
    'ReturnValues' => 'ALL_NEW'
        ));
}

?>
