
indexId = "";
chartTimeOut = "";

// Load the Visualization API and the piechart package.
google.load('visualization', '1.0', {'packages': ['corechart']});

// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(getChartData);

//drawVisualization();

function getChartData() {

    $.getJSON("AWSgetChartData.php?indexId=" + indexId,
            function(item) {
                drawChart1(item);
                drawChart2(item);
            });
}



function drawChart1(item) {

    // Create the data table.
    var data1 = new google.visualization.DataTable();
    data1.addColumn('string', 'Sentiment');
    data1.addColumn('number', 'Type');
    data1.addRows([
        ['Positive', item.positive],
        ['Neutral', item.neutral],
        ['Negative', item.negative]
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

google.load("visualization", "1", {packages: ["corechart"]});
google.setOnLoadCallback(drawChart2);

function drawChart2(item) {
    var data2 = google.visualization.arrayToDataTable([
        ['Time', 'Sentiment'],
        ['t1', item.t1],
        ['t2', item.t2],
        ['t3', item.t3],
        ['t4', item.t4],
        ['t5', item.t5]
    ]);

    var options2 = {
        title: 'Sentiment Tracking',
        vAxis: {
            maxValue: 2,
            minValue: -2
        }
    };

    var chart2 = new google.visualization.LineChart(document.getElementById('chart2'));
    chart2.draw(data2, options2);
}


function pollChart() {
    //chartTimeOut = setTimeout('pollChart()', 2000);//It calls itself every xms
    //getChartData();



// Callback that creates and populates a data table,
// instantiates the pie chart, passes in the data and
// draws it.
}

$(document).ready(function() {
    //pollChart();
});

