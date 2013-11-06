<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <title>TwitterSentiments</title>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <script src="jquery/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>

        <script src="jquery/charts.js"></script>
        <script src="jquery/logic.js"></script>


        <link rel="stylesheet" type="text/css" href="css/mainStylesheet.css"/>
        <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="screen"/>

    </head>
    <body>
        <div id="wrapper">
            <div class="container">


                <div class="row-fluid" id="header">
                    <h1>TwitterSentiments</h1>
                    <div class="span3"></div>
                </div><!--end header-->

                <div class="row-fluid span11 offset1" id="input">

                    <form id="trackInput" class="span9">
                        <div class="input-prepend">
                            
                            <span><i class="icon-search"></i></span>
                            <input class="input-medium search-query span7" type="text" id="trackWords" placeholder="enter tracking words with commas separating" value="" size="500" />
                            <input class="btn" id="submitTrackWords" value="Get Tweets" name="getTweets" />
                          
                        </div>
                    </form>

                </div><!--end input-->

                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-default" onclick="clearTimeout(timeOut);
                            console.log('pause');">Pause</button>
                    <button type="button" class="btn btn-default"  onclick="poll();
                            console.log('run');">Run</button>
                </div>

                <div class="row-fluid">
                    <div id="chart1" class="span6"></div>
                    <div id="chart2" class="span6"></div>
                </div>


                <div id="tweets">  </div>
            </div><!--end container-->
        </div><!--end wrapper-->

    </body>
</html>
</body>
</html>
