<?php
require_once('DatumboxAPI.php');

class TwitterSentimentAnalysis {
    
    protected $datumbox_api_key; //Datumbox API Key. 
    
    public function __construct($datumbox_api_key){
        $this->datumbox_api_key=$datumbox_api_key;
    }
    
    //Evaluate sentiment associated with tweet
    public function sentimentAnalysis($twitterText) {     
        return $this->findSentiment($twitterText);
    }
    
    //Find sentiment associated with the tweet
    protected function findSentiment($tweet) {
        $DatumboxAPI = new DatumboxAPI($this->datumbox_api_key); //initialize the DatumboxAPI client
        
        $sentiment=$DatumboxAPI->TwitterSentimentAnalysis($tweet); //call Datumbox service to get the sentiment

        unset($tweet);
        unset($DatumboxAPI);
        
        return $sentiment;
    }
}