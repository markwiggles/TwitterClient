last = '';
timeOut = "";

function getTweets(id) {
    $.getJSON("AWSgetTweets.php?start=" + id,
            function(data) {
                $.each(data, function(count, item) {
                    addNew(item);
                    last = item.rangeId.N;
                    //console.log(item.rangeId.N);
                });
            });
}

function addNew(item) {
    if ($('#tweets div.tweet').length > 9) { //If we have more than nine tweets
        $('#tweets div.tweet:first').toggle(500);//remove it form the screen
        $('#tweets div.tweet:first').removeClass('tweet');//and it's class
        $("#tweets div:hidden").remove(); //sweeps the already hidden elements
    }
    renderTweet(item);
}

function renderTweet(item) {
    var importanceColor = getImportanceColor(item.followers_count.N);
    var sentimentColor = getSentimentColor(item.sentiment.S);
    var imageLink = "http://twitter.com/" + item.screen_name.S;
    var createdLink = "http://twitter.com/" + item.screen_name.S + "/status/" + item.rangeId.N;

    $("#tweets")
    .append($("<div>").addClass("tweet").attr("id", item.indexId.N)
    .append($("<img>").attr("src", item.profile_image_url.S).addClass("image"))
    .append($("<a>").attr("href", imageLink).append(item.screen_name.S).attr("style", "color:" + importanceColor))
    .append($("<p>").append(item.text.S).addClass("tweetText"))
    .append($("<p>").append("<br>created ").append(item.created_at.S).addClass("created"))
    .append($("<p>").addClass("sentiment").append("Sentiment Analysis: ").append(item.sentiment.S).attr("style", "color:" + sentimentColor))
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
