<?php
        $databaseName = "weather";
        $tablename = "weatherstation";
        $servername = "127.0.0.1";
        $username = "pma";
        $password = "";
        $dbname = "weather";
        $conn = new mysqli($servername, $username, $password) or die("Error connecting ". mysqli_error($conn));
        mysqli_select_db($conn, $dbname) or die("yeah that doesn't work");
        $sql1 = "SELECT * FROM weatherstation ORDER BY dateutc DESC LIMIT 0,1";
        $sql2 = "SELECT * FROM weatherstation ORDER BY dateutc DESC LIMIT 36,1";
        $sql3 = "SELECT dateutc, baromrelin, tempf, dewPoint, windspeedmph FROM weatherstation ORDER BY dateutc DESC LIMIT 0,864";

        $result1 = mysqli_query($conn, $sql1) or die("ERROR IN SELECTING FIRST". mysqli_error($conn));
        $result2 = mysqli_query($conn, $sql2) or die("ERROR IN SELECTING SECOND". mysqli_error($conn));
        $result3 = mysqli_query($conn, $sql3) or die("ERROR IN SELECTING THIRD". mysqli_error($conn));

        $weatherArray1 = array();
        $weatherArray2 = array();
        $weatherArray3 = array();
        
        while($row = mysqli_fetch_assoc($result1))
        {
            $weatherArray1[]=$row;
        }
        while($row = mysqli_fetch_assoc($result2))
        {
            $weatherArray2[]=$row;
        }
        while($row = mysqli_fetch_assoc($result3))
        {
            $weatherArray3[]=$row;
        }

        file_put_contents("./data/data.json",json_encode($weatherArray3));

        $pressure1 = $weatherArray1[0]['baromrelin'];
        $pressure2 = $weatherArray2[0]['baromrelin'];
        if($pressure1<40){$pressure1 = $pressure1 * 33.8639; }
        if($pressure2<40){$pressure2 = $pressure2 * 33.8639; }
        $pressureChange = round($pressure1 - $pressure2, 2, PHP_ROUND_HALF_UP);

        $timeInterval = $weatherArray1[0]['dateutc']-$weatherArray2[0]['dateutc'];

        $windSpeed = $weatherArray1[0]['windspeedmph'];
        $windBearing = $weatherArray1[0]['winddir'];
        $cloudCover = $weatherArray1[0]['cloudcover'];
        $summary = $weatherArray1[0]['summary'];
        $visibility = $weatherArray1[0]['visibility'];
        $icon = $weatherArray1[0]['icon'];
        $gust = $weatherArray1[0]['gust'];
        $temp = $weatherArray1[0]['tempf'];
        $dew = $weatherArray1[0]['dewPoint'];
        $press = $pressure1;
        $hum = $weatherArray1[0]['humidity'];
        $date = $weatherArray1[0]['dateutc'];

        $stationState = $weatherArray1[0]['stationState'];
?>

<html>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, shrink-to-fit=no, initial-scale=1">
<title>South Lewis Weather</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link rel="stylesheet" href="weather.css">
<script type="text/javascript" src="createjs.min.js"></script>
<script type="text/javascript" src="../node_modules/chart.js/dist/chart.js"></script>
<script type="text/javascript" src="stationfunctions.js"></script>
<script type="text/javascript" src="jquery-3.4.1.min.js"></script>
<script type="text/javascript" src="../node_modules/dayjs/dayjs.min.js"></script>

<script>
    var tempF = <?php echo $temp ?>;
    var rh = <?php echo $hum ?>;
    var MBpressure = <?php echo $press ?>;
    var dewPOYNT = <?php echo $dew ?>;
    var timeInterval = <?php echo $timeInterval ?>;
    var stationState = "<?php echo $stationState ?>";
    var currentDate = <?php echo $date ?>;

    var Ctemp = (tempF - 32) * 5 / 9;
    var Cdew = (dewPOYNT - 32) * 5 / 9;
    var wetbulb = convertWet();
    var Cwet;
    var Es, E, dewpoint;
    var absoluteHum;
    var Ay = 6.116441;
    var Em = 7.591386;
    var Tn = 240.7263;
    var Ce = 2.16679;
    var PWS;
    var presentWeather = ["Light Rain","Moderate Rain","Heavy Rain","Rain Showers","Thunderstorm","Thunderstorm with Hail","Light Drizzle","Moderate Drizzle","Heavy Drizzle","Light Snow","Moderate Snow","Heavy Snow","Snow Showers","Sleet","Freezing Rain","Freezing Drizzle","Blowing Snow","Blowing Dust","Smoke","Haze","Fog"];
    var selectedWeather = 0;
    var cloudCover = <?php echo $cloudCover ?>;

    var canvas, stage;
    var station, wind_barb, sky, temp, dewpt, pres, pres_box, background;
    var dpres, dpres_box, temp_box, dewpt_box, p_trend, p_trend_box;
    var vis, vis_box, vis_whole, vis_num, vis_denom, vis_line, weather;
    var weather_image = new Image();
    var windSpeed = <?php echo $windSpeed ?>*0.868976;
    var windDir = <?php echo $windBearing ?>;
    var cloudCoverage = Math.round(cloudCover*8);
    
    var visibility = <?php echo $visibility ?>;
    var visibilityWhole = Math.trunc(visibility);
    var visibilityRemainder = visibility - visibilityWhole;
    var visIndexReal = Math.round(Math.log(visibilityRemainder)/Math.log(2)*(-1));
    var whatWeather = "<?php echo $summary?>";
    var clickState = false;
    var pressureChange = <?php echo $pressureChange ?>;

    var dataArray = <?php echo json_encode($weatherArray3) ?>;

    var windUnit = "kts";
    var tempUnit = "fahr";
    var dewUnit = "fahr";
    var wetUnit = "fahr";
    var pressUnit = "mb";

    function toggle(className, obj){
        var $input = $(obj);
        if ($input.prop('checked')) $(className).slideDown();
        else $(className).slideUp();
    }

    function toggleGuts(className, obj){
        
        if (!clickState){
            $(className).slideDown();
            clickState = true;
        }
        else{
            $(className).slideUp();
            clickState = false;
        }
        $(className).toggleClass("open");
    }

    $(document).ready(function(){
        $("#windData").click(function(){
            if (windUnit == "kts"){
                $("#windData").html("Wind Speed "+(windSpeed/0.868976).toFixed(1)+" mph from "+windDir+"&#176;");
                $(this).toggleClass("changed");
                windUnit = "mph";}
            else{
                $("#windData").html("Wind Speed "+windSpeed.toFixed(1)+" kts from "+windDir+"&#176;");
                $(this).toggleClass("changed");
                windUnit = "kts";}
        });

        $("#temp").click(function(){
            if (tempUnit == "fahr"){
                $("#temp").html("Temperature&nbsp;"+Ctemp.toFixed(1)+"&#176;C");
                $(this).toggleClass("changed");
                tempUnit = "cels";}
            else{
                $("#temp").html("Temperature&nbsp;"+tempF.toFixed(1)+"&#176;F");
                $(this).toggleClass("changed");
                tempUnit = "fahr";}
        });
        
        $("#dpt").click(function(){
            if (dewUnit == "fahr"){
                $("#dpt").html("Dewpoint&nbsp;"+Cdew.toFixed(1)+"&#176;C");
                $(this).toggleClass("changed");
                dewUnit = "cels";}
            else{
                $("#dpt").html("Dewpoint&nbsp;"+dewPOYNT.toFixed(1)+"&#176;F");
                $(this).toggleClass("changed");
                dewUnit = "fahr";}
        });

        $("#wetBulb").click(function(){
            if (wetUnit == "fahr"){
                $("#wetBulb").html("Wetbulb&nbsp;"+Cwet+"&#176;C");
                $(this).toggleClass("changed");
                wetUnit = "cels";}
            else{
                $("#wetBulb").html("Wetbulb&nbsp;"+wetbulb+"&#176;F");
                $(this).toggleClass("changed");
                wetUnit = "fahr";}
        });

        $("#press").click(function(){
            if (pressUnit == "mb"){
                $("#press").html("Pressure&nbsp;"+(MBpressure*0.02953).toFixed(2)+"in Hg");
                $(this).toggleClass("changed");
                pressUnit = "hg";}
            else{
                $("#press").html("Pressure&nbsp;"+MBpressure.toFixed(1)+"mb");
                $(this).toggleClass("changed");
                pressUnit = "mb";}
        });

        $("#pressureBtn").click(function(){
            $("#pressureModal").toggle();        
        });
        $(".closePressure").click(function(){
            $("#pressureModal").toggle();
        });

        $("#tempBtn").click(function(){
            $("#tempModal").toggle();        
        });
        $(".closeTemp").click(function(){
            $("#tempModal").toggle();
        });

        $("#humidityBtn").click(function(){
            $("#humidityModal").toggle();        
        });
        $(".closeHumidity").click(function(){
            $("#humidityModal").toggle();
        });

        $("#dewBtn").click(function(){
            $("#dewModal").toggle();        
        });
        $(".closeDew").click(function(){
            $("#dewModal").toggle();
        });

        $("#cloudBtn").click(function(){
            $("#cloudModal").toggle();        
        });
        $(".closeCloud").click(function(){
            $("#cloudModal").toggle();
        });
    });

</script>

<body class="container bg-dark text-white">
    <div class="container horizontal grad">
        <h2 align="center">South Lewis High School</h2>
    </div>
    <div class="container-fluid" style="position: relative;">
        <div class="container-fluid rounded ml-2 py-2" style="background-color: #444444;">
            <div class="title">
                <span class="words">Current Weather</span>
                <span class="date" id="currentTime"></span><br>
            </div>
            <div class="data-panel-action">
                <input type="checkbox" id="showHideData" name="showHide" value="show" onclick="toggle('#dataSpot', this)"> Show Data
            </div>
            <div id="dataSpot" style="display: none; background-color: #444444;">
                <div class="row pt-2">
                    <div class="col dataField" id="temp"></div>
                    <div class="col dataField" id="press"></div>
                </div>
                <div class="row pt-2">
                    <div class="col dataField" id="wetBulb"></div>
                    <div class="col dataField" id="cloud"></div>
                </div>
                <div class="row pt-2">
                    <div class="col dataField" id="dpt"></div>
                    <div class="col dataField" id="rh"></div>
                </div>
                <div class="row pt-2">
                    <div class="col dataField" id="windData"></div>
                    <div class="col dataField" id="summary"></div>
                </div>
                <div class="row pt-2">
                    <div class="col dataField" id="vis"></div>
                    <div class="col dataField" id="AbsHum"></div>
                </div>
                <div class="row py-2">
                    <div class="col dataField" id="cloudBase">Cloud Base Altitude&nbsp; feet</div>
                    <div class="col dataField"></div>
                </div>
            </div>
        </div>

        <div class="modal" id="pressureModal">
            <div class="modal-content" style="width: 166px;">
                <span><span class="closePressure close">&times;</span> Pressure Scale</span>
                <canvas id="pressureScale" height="870" width="500"></canvas>
            </div>
        </div>  
        <div class="modal" id="tempModal">
            <div class="modal-content" style="width: 316px;">
                <span><span class="closeTemp close">&times;</span> Temperature Scale</span>
                <canvas id="temperatureScale" height="785" width="500"></canvas>
            </div>  
        </div>
        <div class="modal" id="humidityModal">
            <div class="modal-content" style="width: 814px;">
                <span><span class="closeHumidity close">&times;</span> Humidity Table</span>
                <canvas id="humidityTable" height="643" width="800"></canvas>
            </div>  
        </div>
        <div class="modal" id="dewModal">
            <div class="modal-content" style="width: 814px;">
                <span><span class="closeDew close">&times;</span> Dewpoint Table</span>
                <canvas id="dewTable" height="643" width="800"></canvas>
            </div>  
        </div>
        <div class="modal" id="cloudModal">
            <div class="modal-content" style="width: 814px;">
                <span><span class="closeCloud close">&times;</span> Cloud Base</span>
                <canvas id="cloudIMG" height="643" width="800"></canvas>
            </div>  
        </div>
        <img id="pressureImg" src="pressure.png" style="display:none;"/>
        <img id="temperatureImg" src="temperature.png" style="display:none;"/>
        <img id="humidityImg" src="humidity.png" style="display:none;"/>
        <img id="dewImg" src="dewpoint.png" style="display:none;"/>
        <img id="cloudBaseIMG" src="Sky.png" style="display:none;"/>
    </div>
    <div class="container-fluid" style="position: relative;">
        <div class="button-panel" id="button-panel">
            <div class="button-panel-header" id="button-panelheader" onclick="toggleGuts('#button-panelGuts','button-panelGuts')">Reference Table Pages</div>
            <div class="button-panel guts" id="button-panelGuts">
                <div><button id="pressureBtn">Pressure</button></div>
                <div><button id="tempBtn">Temperature</button></div>
                <div><button id="humidityBtn">Humidity Calc</button></div>
                <div><button id="dewBtn">Dewpoint Calc</button></div>
                <div><button id="cloudBtn">Cloud Base</button></div>
            </div>
        </div>
        <canvas id="canvas" height="500" width="500">Canvas is not supported by your browser.</canvas>
    </div>
   
    <div class="row">
            <div class="col-sm-1"></div>
            <div class="col-sm-8" id="status">Station State</div>
            <script>
                
            </script>
    </div>
    <div class="row">
        <div class="col-sm-1"></div>
        <div class="col-sm-8">
            <a href="http://www.nysmesonet.org/weather/meteogram#network=nysm&stid=gfld">Glenfield Meteogram</a>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-1"></div>
        <div class="col-sm-8" id="timeInterval"></div>
    </div>
    <script>
        drawStationModel();
       
    </script>
    <canvas class="graph" id="pressureChart" width="1600px" height="600px"></canvas>
    <canvas class="graph" id="tempsChart" width="1600px" height="600px"></canvas>
    <canvas class="graph" id="windChart" width="1600px" height="600px"></canvas>
    <script>drawCharts();</script>
</html>