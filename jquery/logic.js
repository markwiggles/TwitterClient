last = '';
first = '';
timeOut = "";
chartTimeOut = "";
trackWords = "";
positive = 0;
negative = 0;
neutral = 0;


/* set up the textbox to get the trackWords */
$(document).ready(function() {
    //set up the click function for the getTweets button
    $('#submitTrackWords').click(function() {
        trackWords = $('#trackWords').val();//get words
        $('#trackWords').val(""); //empty textbox
        $('#tweets').empty(); //empty tweets
        $('#tracking').html("Tracking: " + trackWords);
    });
});
//
//

function getTweets(id) {
    $.getJSON("AWSgetTweets.php?start=" + id + "&trackWords=" + trackWords,
            function(data) {

                processChartData(data);

                $.each(data, function(count, item) {
                    addNew(item);
                    last = item.rangeId.N;
                });
            });
}

function addNew(item) {
    if ($('#tweets div.tweet').length > 9) { //If we have more than nine tweets
        $('#tweets div.tweet:first').toggle(200);//remove it from the screen
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
    timeOut = setTimeout('poll()', 400);//It calls itself every xms
    getTweets(last);
}

$(document).ready(function() {
    poll();
});


/*************** CHART FUNCTIONS**********************************************************/

chartTimeOut = "";


// Load the Visualization API and the piechart package.
google.load('visualization', '1.0', {'packages': ['corechart']});

// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(getChartData);

//drawVisualization();

function getChartData() {
    drawChart1();
}



function drawChart1() {

    // Create the data table.
    var data1 = new google.visualization.DataTable();
    data1.addColumn('string', 'Sentiment');
    data1.addColumn('number', 'Type');
    data1.addRows([
        ['Positive', positive],
        ['Neutral', neutral],
        ['Negative', negative]
    ]);

    // Set chart options
    var options1 = {'title': 'Trending Sentiment',
        width: 400,
        height: 300,
    };

    // Instantiate and draw our chart, passing in some options.
    var chart1 = new google.visualization.PieChart(document.getElementById('chart1'));
    chart1.draw(data1, options1);
}



function pollChart() {
    chartTimeOut = setTimeout('pollChart()', 2000);//It calls itself every xms
    getChartData();



// Callback that creates and populates a data table,
// instantiates the pie chart, passes in the data and
// draws it.
}

$(document).ready(function() {
    pollChart();
});

function processChartData(data) {

    positive = 0;
    negative = 0;
    neutral = 0;

    //iterate through the data array and calculate pie chart data
    $.each(data, function(count, item) {
        if (item.sentiment.S === 'positive') {
            positive++;
        } else if (item.sentiment.S === 'negative') {
            negative++;
        } else {
            neutral++;
        }
    });

    if (positive === 0 && negative === 0 && neutral === 0) {
        neutral = 1;
    }
}