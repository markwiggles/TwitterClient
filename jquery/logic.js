last = '';
timeOut = "";
trackwords= "";

$(document).ready (function() {  
    //set up the click function for the getTweets button
    $('#submitTrackWords').click(function() {       
         trackwords = $('#trackWords').val();//get words
        $('#trackWords').val(""); //empty textbox
    }); 
});


function getTweets(id) {
    
    $.getJSON("AWSgetRawTweets.php?start=" + id  + "&trackwords=" + trackwords,
            function(data) {
                               
                $.each(data, function(count, item) {
                    addNew(item);
                    last = item.rangeId;
                });
            });
}

function addNew(item) {
    if ($('#tweets div.tweet').length > 9) { //If we have more than nine tweets
        $('#tweets div.tweet:first').toggle(500);//remove it from the screen
        $('#tweets div.tweet:first').removeClass('tweet');//and it's class
        $("#tweets div:hidden").remove(); //sweeps the already hidden elements
    }
    renderTweet(item);
}

function renderTweet(item) {
    var importanceColor = getImportanceColor(item.followers_count);
    var sentimentColor = getSentimentColor(item.sentiment);
    var imageLink = "http://twitter.com/" + item.screen_name;
    var createdLink = "http://twitter.com/" + item.screen_name + "/status/" + item.rangeId;

    $("#tweets")
    .append($("<div>").addClass("tweet").attr("id", item.indexId)
    .append($("<img>").attr("src", item.profile_image_url).addClass("image"))
    .append($("<a>").attr("href", imageLink).append(item.screen_name).attr("style", "color:" + importanceColor))
    .append($("<p>").append(item.text).addClass("tweetText"))
    .append($("<p>").append("<br>created ").append(item.created_at).addClass("created"))
    .append($("<p>").addClass("sentiment").append("Sentiment Analysis: ").append(item.sentiment).attr("style", "color:" + sentimentColor))
    );
}

function getImportanceColor(number) {
    rgb = 255 - Math.floor(16 * (Math.log(number + 1) + 1)); //should return about 0 for 0 followers and 255 for 4million (Ashton Kutchner? Obama?)
    return 'rgb(' + rgb + ',0,0)';
}

function getSentimentColor(text) {
    if (text === "positive") {
        color = "green";
    } else if (text === "negative") {
        color = "red";
    } else if (text === "neutral") {
        color = "grey";
    } else {
        color = "black";
    }
    return color;
}

function poll() {
    timeOut = setTimeout('poll()', 300);//It calls itself every 200ms
    getTweets(last);
}

$(document).ready(function() {
    poll();
});
