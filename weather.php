<?php

	$api_url = 'https://api.ambientweather.net/v1/devices?applicationKey=698db98a3d16443eabc27e05981707dd1816f9fdce234080b1d322c757fb3bee&apiKey=4e79425cd75740b48c9755b55d59ad830393733a125a44f2b52b85e4e1310f48';
    $api_url2 = 'https://api.darksky.net/forecast/1ab54032d29c1e5563ca79d31c65c951/43.6361,-75.3944';
    
    $fileContents = file_get_contents($api_url);
    
    $forecast = json_decode($fileContents, true);
    
    $forecastA = json_decode(file_get_contents($api_url2));  
    
    function multiKeyExists(array $arr, $key) {
        
        // is in base array?
        if (array_key_exists($key, $arr)) {
            return true;
        }
    
        // check arrays contained in this array
        foreach ($arr as $element) {
            if (is_array($element)) {
                if (multiKeyExists($element, $key)) {
                    return true;
                }
            }  
        }
    
        return false;
    }

    if(multiKeyExists($forecast,'tempf')) {
        foreach ($forecast as $row)
        {
            $temp = round($row['lastData']['tempf'], 1, PHP_ROUND_HALF_UP);
            $dew = round($row['lastData']['dewPoint'], 1, PHP_ROUND_HALF_UP);
            //$windBearing = $row['lastData']['winddir'];
            $windSpeed = $row['lastData']['windspeedmph'];
            $press = round($row['lastData']['baromrelin'] * 33.8639, 1, PHP_ROUND_HALF_UP);
            $hum = $row['lastData']['humidity']/100;
        }
        $windBearing = $forecastA->currently->windBearing;
        //$windSpeed = $forecastA->currently->windSpeed;
        $cloudCover = $forecastA->currently->cloudCover;
        $summary = $forecastA->currently->summary;
        $visibility = $forecastA->currently->visibility;
        $icon = $forecastA->currently->icon;
        $gust = $forecastA->currently->windGust;
        
        $stationState = "Weather Station Online";
    }
    else {
        $windSpeed = $forecastA->currently->windSpeed;
        $windBearing = $forecastA->currently->windBearing;
        $cloudCover = $forecastA->currently->cloudCover;
        $summary = $forecastA->currently->summary;
        $visibility = $forecastA->currently->visibility;
        $icon = $forecastA->currently->icon;
        $gust = $forecastA->currently->windGust;
        $temp = $forecastA->currently->temperature;
        $dew = $forecastA->currently->dewPoint;
        $press = round($forecastA->currently->pressure, 1, PHP_ROUND_HALF_UP);
        $hum = $forecastA->currently->humidity;

        $stationState = "South Lewis Weather Station Offline - Using Darksky API Interpolated Data";
    }
   
?>

<html>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, shrink-to-fit=no, initial-scale=1">
<title>South Lewis Weather</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<script type="text/javascript" src="createjs.min.js"></script>
<script type="text/javascript" src="stationmodel_draw_fcns.js"></script>
<script type="text/javascript" src="jquery-3.4.1.min.js"></script>

<style>
    * {
        -webkit-tap-highlight-color: rgba(0,0,0,0);
    }

    a:link {
        color: red;
    }

    /* visited link */
    a:visited {
        color: green;
    }

    /* mouse over link */
    a:hover {
        color: hotpink;
    }

    /* selected link */
    a:active {
        color: blue;
    }
    
    ::selection {
        background: none;
    }

    .button-panel {
        height: 270px;
        width: 200px;
        position: absolute;
        top: 0%;
        right: 20px;
        background: rgba(0,0,0,0.1);
        z-index: 9;
    }

    .button-panel-header {
        text-align: center; 
        cursor: move;
        z-index: 10;
    }

    button {
        border: none;
        background: rgba(0,0,0,1);
        color: #ffffff !important;
        position: relative;
        text-align: center;
        left: 20px;
        width: 150px;
        font-size: 12px;
        font-weight: 100;
        padding: 10px 20px;
        margin: 5px;
        text-transform: uppercase;
        border-radius: 6px;
        box-shadow: inset 0 0 7px white;
        display: inline-block;
        transition: all 0.3s ease-in 0.1s;
    }

    button:hover{
        color: #404040 !important;
        font-weight: 700 !important;
        left: -5px;
        width: 200px;
        letter-spacing: 3px;
        background: white;
        -webkit-box-shadow: 0px 5px 40px -10px rgba(255,255,255,0.7);
        -moz-box-shadow: 0px 5px 40px -10px rgba(255,255,255,0.7);
        transition: all 0.3s ease 0s;
    }

    button:focus{
        outline: none;
        box-shadow: inset 0 0px 5px 0 rgba(255, 255, 255, 0.7);
    }

    canvas {
        padding-left: 0;
        padding-right: 0;
        width: 500px;
        margin-left: auto;
        margin-right: auto;
        display: block;   
    }

    .horizontal {
        background-color: transparent;
        border: none;
        color: red;
        padding: 5px 5px;
        text-align: center;
        font-size: 16px;
        margin: 2px 2px;
        opacity: 1;
        transition: 0.3s;
        -webkit-text-stroke: 1px black;
        background-image: linear-gradient(to right, rgba(0,0,0,0), rgba(0,0,0,1), rgba(0,0,0,0));
    }

    .horizontal:hover {
        color: white; 
        background-color: #ff5555;
    }

    .dataField {
        -webkit-transition: color 0.5s, font-size 0.5s;
        transition: color 0.5s, font-size 0.3s;
        font-size: 16px;
        color: #FFFFFF;
    }

    .dataField:hover {
        font-size: 30px;
        color: #FFFFFF;
    }
    
    .grad{
        background-image: linear-gradient(to right, rgba(0,0,0,0), rgba(0,0,0,1), rgba(0,0,0,0));
    }

    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 11; /* Sit on top */
        padding-top: 50px; /* Location of the box */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgba(0,0,0,0.4); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        outline: none;
        border: none;
    }

    /* Modal Content */
    .modal-content {
        background-color: rgba(255,255,255,0.4);
        margin: auto;
        padding: 5px;
        outline: none;
        border: none;
        width: 200px;
    }

    /* The Close Button */
    .close {
        color: #aaaaaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }

    .dataField.changed {
        color: aqua;
    }
    
</style>

<script>
    var tempF = <?php echo $temp ?>;
    var rh = 100 * <?php echo $hum ?>;
    var MBpressure = <?php echo $press ?>;
    var dewPOYNT = <?php echo $dew ?>;
    var wetbulb;
    var Ctemp = (tempF - 32) * 5 / 9;
    var Cdew = (dewPOYNT - 32) * 5 / 9;
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

    var canvas, stage;
    var station, wind_barb, sky, temp, dewpt, pres, pres_box, background;
    var dpres, dpres_box, temp_box, dewpt_box, p_trend, p_trend_box;
    var vis, vis_box, vis_whole, vis_num, vis_denom, vis_line, weather;
    var weather_image = new Image();
    var windSpeed = <?php echo $windSpeed ?>*0.868976;
    var windDir = <?php echo $windBearing ?>;
    var cloudCoverage = <?php echo $cloudCover ?>;
    cloudCoverage = Math.round(cloudCoverage*8);
    
    var visibility = <?php echo $visibility ?>;
    var visibilityWhole = Math.trunc(visibility);
    var visibilityRemainder = visibility - visibilityWhole;
    var visIndexReal = Math.round(Math.log(visibilityRemainder)/Math.log(2)*(-1));
    var whatWeather = "<?php echo $summary?>";
    var clickState = true;


    function convertWet(){			
        Es = parseFloat(esubs(Ctemp));		     
        E = parseFloat(invertedRH(Es,rh));
        dewpoint = Dewpoint(E);	
        var E2 = E;
	    Twguess = 0;
	    incr = 10;
	    previoussign = 1;
	    Edifference = 1;
			
	    wetbulb = calcwetbulb(Edifference,Twguess,Ctemp,MBpressure,E2,previoussign,incr);
	    wetbulb = roundOff(convertCtoF(wetbulb));
        wetbulb = wetbulb.toFixed(1);
        Cwet = ((wetbulb - 32) * 5 / 9).toFixed(1);        
        return wetbulb;				 			
    }

    function makeAbsoluteHumidity(){
        PWS = Ay*Math.pow(10,Em*Ctemp/(Ctemp+Tn));
        absoluteHum = PWS*rh*Ce/(273.15+Ctemp);
        document.getElementById("AbsHum").innerHTML = "Absolute Humidity&nbsp;&nbsp;"+absoluteHum.toFixed(1)+" grams/meter&sup3;";        
    }
    
    function convertFtoC(Fahr){
        var Cels;
        Cels = .55556 * (Fahr - 32);
        return Cels;
    }

    function convertCtoF(Cels){
        var Fahr;
        Fahr = 1.8 * Cels + 32;
        return Fahr;
    }

    function esubs(Ctemp){
        var Es;
        Es = 6.112 * Math.exp(17.67 * Ctemp / (Ctemp + 243.5));
        return Es;
    }

    function invertedRH(Es,rh){
        var E;
        E = Es * (rh/100);
        return E;
    }

    function Dewpoint(E){
        var Dewpont;
        Dewpont = (243.5 * Math.log(E/6.112))/(17.67 - Math.log(E/6.112));
        return Dewpont;
    }

    function calcwetbulb(Edifference,Twguess,Ctemp,MBpressure,E2,previoussign,incr){
        var cursign, previoussign, incr;
        console.log(Ctemp + "cTemp " + MBpressure + " mBpressure");
        outerloop:
        while (Math.abs(Edifference) > 0.005) 
        {
            Ewguess = 6.112 * Math.exp((17.67 * Twguess) / (Twguess + 243.5));
            Eguess = Ewguess - MBpressure * (Ctemp - Twguess) * 0.00066 * (1 + (0.00115 * Twguess));
            Edifference = E2 - Eguess;

            if (Edifference == 0)
            {
                incr = 0;
            } else {
                if (Edifference < 0)
                {
                    cursign = -1;
                    if (cursign != previoussign)
                    {
                        previoussign = cursign;
                        incr = incr/10;
                    } else {
                        incr = incr;
                    }
                } else {
                    cursign = 1;
                    if (cursign != previoussign)
                    {
                        previoussign = cursign;
                        incr = incr/10;
                    } else {
                        incr = incr;
                    }
                }
            }
            Twguess = Twguess + incr * previoussign;
        }
        wetbulb = Twguess;
        console.log(wetbulb + " wetbulb");
        return wetbulb;
    }			

    function roundOff(value){
        value = Math.round(100*value)/100;
        return value;
    }

    function roundOff(value){
        value = Math.round(100*value)/100;
        return value;
    }
    
    function drawStationModel(){
        
        canvas = document.getElementById("canvas");
        
        stage = new createjs.Stage(canvas);
        stage.autoClear = true;

        station = new createjs.Shape();
        wind_barb = new createjs.Shape();
        sky = new createjs.Shape();
        pres = new createjs.Text("000", "bold 22px Arial");
        pres_box = new createjs.Shape();
        dpres = new createjs.Text("0000", "bold 22px Arial");
        dpres_box = new createjs.Shape();
        temp = new createjs.Text("0000", "bold 22px Arial");
        temp_box = new createjs.Shape();
        dewpt = new createjs.Text("0000", "bold 22px Arial");
        dewpt_box = new createjs.Shape();
        weather = new createjs.Bitmap("weather_symbols.gif");
        p_trend = new createjs.Bitmap("p_trends.gif");

        vis = new createjs.Container();
        vis_box = new createjs.Shape();
        vis_whole = new createjs.Text("00", "bold 24px Arial");
        vis_num = new createjs.Text("0", "bold 20px Arial");
        vis_denom = new createjs.Text("00", "bold 20px Arial");
        vis_line = new createjs.Shape();
        vis.addChild(vis_num);
        vis.addChild(vis_denom);
        vis.addChild(vis_line);
        vis.addChild(vis_whole);
    
        selectedWeather = 0;
        if(whatWeather=="snow"){selectedWeather = 10;}
        else if(whatWeather=="fog"){selectedWeather = 21;}
        else if(whatWeather=="rain"){selectedWeather = 4;}
        else if(whatWeather=="sleet"){selectedWeather = 14;}
        else {selectedWeather = 0;}
        
        station.graphics.ss(3).s("#000").f("rgba(255,255,255,1)").dc(0, 0, 25);
        station.x = canvas.width >> 1;
        station.y = canvas.height >> 1;

        backGround = new createjs.Shape();
        backGround.x = 0;
        backGround.y = 0;
        backGround.graphics.beginRadialGradientFill(["white","white","transparent"], [0, 0.7, 1], canvas.width/2, canvas.height/2, 0, canvas.width/2, canvas.height/2, 230).drawRect(0,0, canvas.width,canvas.height);
        backGround.graphics.endFill();
        stage.addChild(backGround);

        draw_wind(wind_barb);
        wind_barb.x = canvas.width >> 1;
        wind_barb.y = canvas.height >> 1;

        draw_weather(weather, selectedWeather);
        weather.x = station.x - 70;
        weather.y = station.y - 16;

        draw_sky(sky, cloudCoverage);
        sky.x = canvas.width >> 1;
        sky.y = canvas.height >> 1;

        draw_P(pres);
        pres.x = station.x + 25;
        pres.y = station.y - 50;
        pres_box.graphics.f("rgba(255,255,255,0.2)").dr(pres.x, pres.y, 40, 22);

        draw_dp(dpres, p_trend)
        dpres.x = station.x + 38;
        dpres.y = station.y - 10;
        dpres_box.graphics.f("rgba(255,255,255,0.2)").dr(dpres.x - 2, dpres.y, 40, 22);
        p_trend.x = station.x + 75;
        p_trend.y = station.y - 10;

        draw_T_Td(temp, dewpt);
        temp.x = station.x - 65;
        temp.y = station.y - 50;
        temp_box.graphics.f("rgba(255,255,255,0.2)").dr(temp.x, temp.y, 45, 22);
        dewpt.x = station.x - 65;
        dewpt.y = station.y + 35;
        dewpt_box.graphics.f("rgba(255,255,255,0.2)").dr(dewpt.x, dewpt.y, 45, 22);

        vis_whole.name = "vis_whole";
        vis_whole.textAlign = "end";
        vis_num.name = "vis_num";
        vis_num.textAlign = "center";
        vis_denom.name = "vis_denom";
        vis_denom.textAlign = "center";
        vis_line.name = "vis_line";
        vis_num.x = vis_whole.x + 14;
        vis_num.y = vis_whole.y - 10;
        vis_denom.x = vis_whole.x + 15;
        vis_denom.y = vis_whole.y + 10;
        vis_line.graphics.ss(2).s("#000").mt(5, 9).lt(25, 9);
        vis.x = station.x - 95;
        vis.y = station.y - 11;
    
        //draw_vis(vis, visIndexReal);
       
        draw_vis(vis);    
       
        vis_box.graphics.f("rgba(255,255,255,1)").dr(vis.x - 13, vis.y, 45, 22);

        stage.addChild(wind_barb);
        stage.addChild(vis_box);
        stage.addChild(vis);
        stage.addChild(weather);
        stage.addChild(pres_box);
        stage.addChild(pres);
        stage.addChild(dpres_box);
        stage.addChild(dpres);
        stage.addChild(p_trend);
        stage.addChild(temp_box);
        stage.addChild(temp);
        stage.addChild(dewpt_box);
        stage.addChild(dewpt);
        stage.addChild(station);
        stage.addChild(sky);

        stage.update();
    
        makeAbsoluteHumidity();
        cloudBase();
        drawPressureModal();
        drawTempModal();
        drawHumidityModal();
        drawDewModal();
        drawCloudModal();
    }

    function checkInput(obj, what_chars) {
        var invalidChars;
        if (what_chars == "#") {
            invalidChars = /[^0-9]/gi;
        } else if (what_chars == "#.") {
            invalidChars = /[^0-9\\.]/gi;
        } else if (what_chars == "#-") {
            invalidChars = /[^0-9\-]/gi;
        } else if (what_chars == "#-.") {
            invalidChars = /[^0-9\-\\.]/gi;
        }

        if (invalidChars.test(obj.value)) {
            obj.value = obj.value.replace(invalidChars, "");
        } else {
            if (obj.id == "temperature" || obj.id == "dewpoint") {
                draw_T_Td(temp, dewpt);
            } else if (obj.id == "pressure" || obj.id == "pres_3hr") {
                draw_P(pres);
                draw_dp(dpres, p_trend);
            } else if (obj.id == "w_speed" || obj.id == "w_dir") {
                draw_wind(wind_barb);
            } else if (obj.id == "visibility") {
                draw_vis(vis, document.getElementById("visibility_frac").selectedIndex);
            }
            stage.update();
        }
    }

    function cloudBase(){
        var cloudBaseAltitude = (Ctemp - Cdew) / 2.5 * 1000;
        document.getElementById("cloudBase").innerHTML = "Cloud Base Altitude " + cloudBaseAltitude.toFixed(0) + " feet";
    }
  
    function drawPressureModal(){
        var canvas = document.getElementById("pressureScale");
        var modalCtx = canvas.getContext("2d");
        modalCtx.scale(0.9,0.9);
        var img = document.getElementById("pressureImg");
        var pressurePixel = (-863/76)*(MBpressure-1041-(5928/863));
        modalCtx.drawImage(img, 10, 10);
        modalCtx.lineWidth = 2;
        modalCtx.strokeStyle = "red";                                             //draw the line.
        modalCtx.beginPath();
        modalCtx.moveTo(10,pressurePixel);
        modalCtx.lineTo(166,pressurePixel);           //78 = 1041.0
        modalCtx.stroke();
        modalCtx.fillStyle = 'rgba(255,255,255,0.8)';
        modalCtx.fillRect(10, pressurePixel+20, 156, 25);
        modalCtx.fillStyle = "black";
        modalCtx.font = 'bold 18px Arial';
        modalCtx.fillText("Current Pressure", 15, pressurePixel + 38);                  //941 = 965.0       863   76
        modalCtx.closePath();
    }

    function drawTempModal(){
        var canvas = document.getElementById("temperatureScale");
        var modalCtx = canvas.getContext("2d");
        var img = document.getElementById("temperatureImg");
        var tempPixel = (-577/261)*(tempF-212-(39933/577));
        var dewPixel = (-577/261)*(dewPOYNT-212-(39933/577));
        modalCtx.drawImage(img, 10, 10);
        modalCtx.lineWidth = 2;
        modalCtx.strokeStyle = "red";                                             //draw the line.
        modalCtx.beginPath();
        modalCtx.moveTo(10,tempPixel);
        modalCtx.lineTo(297,tempPixel);                                                     //153 = 100
        modalCtx.stroke();
        modalCtx.closePath();
        modalCtx.strokeStyle = "blue";
        modalCtx.beginPath();
        modalCtx.moveTo(10,dewPixel);
        modalCtx.lineTo(297,dewPixel);                                                     //153 = 100
        modalCtx.stroke();
        modalCtx.closePath();
        modalCtx.fillStyle = 'rgba(255,100,100,1)';
        modalCtx.fillRect(205, tempPixel-26, 91, 25);
        modalCtx.fillStyle = "white";                                                                  //730 = -40
        modalCtx.font = 'bold 11px Arial';
        modalCtx.fillText("TEMPERATURE", 210, tempPixel - 10);

        modalCtx.fillStyle = 'rgba(100,100,255,1)';
        modalCtx.fillRect(205, dewPixel+1, 75, 25);
        modalCtx.fillStyle = "white";   
        modalCtx.fillText("DEWPOINT", 210, dewPixel + 17);   
        modalCtx.closePath();
    }

    function drawHumidityModal(){
        var canvas = document.getElementById("humidityTable");
        var modalCtx = canvas.getContext("2d");
        var img = document.getElementById("humidityImg");
        var rectWidth = 43;
        var rectHeight = 21;
        var flooredCtemp = Math.round(Ctemp/2)*2;
        var drybulb = (496/50)*(flooredCtemp-30+(30200/496));
        var actualDrybulb = (496/50)*(Ctemp-30+(30200/496))+rectHeight/2;
       
        console.log(wetbulb);
        var difference = Ctemp - convertFtoC(wetbulb);
        var roundedDifference = Math.round(difference);
        //console.log(difference);
        roundedDifference = (648/15)*(roundedDifference+(1560/648));
        difference = (648/15)*(difference+(1560/648))+rectWidth/2;
        //console.log(difference);
        modalCtx.drawImage(img, 10, 10);
        modalCtx.lineWidth = 2;
        modalCtx.strokeStyle = "red";                                             //draw the line.
        modalCtx.beginPath();
        modalCtx.rect(19,drybulb,776,rectHeight);           
        modalCtx.stroke();
        modalCtx.closePath();
        modalCtx.strokeStyle = "blue";
        modalCtx.beginPath();
        modalCtx.rect(roundedDifference,108,rectWidth,519);           
        modalCtx.stroke();
        modalCtx.closePath();
        modalCtx.beginPath();
        modalCtx.fillStyle = "rgba(100,100,100,0.8)";
        modalCtx.arc(difference, actualDrybulb, 5, 0, 2 * Math.PI);
        modalCtx.fill();
        modalCtx.stroke();
        modalCtx.fillStyle = 'rgba(255,255,255,0.9)';
        modalCtx.fillRect(580, 210, 120, 70);
        modalCtx.fillStyle = "black";                                                                  //730 = -40
        modalCtx.font = 'bold 40px Arial';
        modalCtx.fillText(rh.toFixed(0) + "%", 610, 260);

    }

    function drawDewModal(){
        var canvas = document.getElementById("dewTable");
        var modalCtx = canvas.getContext("2d");
        var img = document.getElementById("dewImg");
        var rectWidth = 43;
        var rectHeight = 21;
        modalCtx.scale(0.88,0.88);
        var flooredCtemp = Math.round(Ctemp/2)*2;
        var drybulb = (496/50)*(flooredCtemp-30+(30200/496));
        var actualDrybulb = (496/50)*(Ctemp-30+(30200/496))+rectHeight/2;
        //console.log(wetbulb);
        var difference = Ctemp - convertFtoC(wetbulb);
        var roundedDifference = Math.round(difference);
        //console.log(difference);
        roundedDifference = (648/15)*(roundedDifference+(1560/648));
        difference = (648/15)*(difference+(1560/648))+rectWidth/2;
        //console.log(difference);
        modalCtx.drawImage(img, 10, 10);
        modalCtx.scale(1.13636363636,1.13636363636);
        modalCtx.lineWidth = 2;
        modalCtx.strokeStyle = "red";                                             
        modalCtx.beginPath();
        modalCtx.rect(19-3,drybulb+2,776,rectHeight)                                  //modalCtx.rect(19,drybulb,776,rectHeight); 
        modalCtx.stroke();
        modalCtx.closePath();
        modalCtx.strokeStyle = "blue";                                              
        modalCtx.beginPath();
        modalCtx.rect(roundedDifference-5,108+2,rectWidth,519);                       //modalCtx.rect(roundedDifference,108,rectWidth,519);             
        modalCtx.stroke();
        modalCtx.closePath();
        modalCtx.beginPath();
        modalCtx.fillStyle = "rgba(100,100,100,0.8)";
        modalCtx.arc(difference-5-3, actualDrybulb+4, 5, 0, 2 * Math.PI);
        modalCtx.fill();
        modalCtx.stroke();
        modalCtx.fillStyle = 'rgba(255,255,255,0.9)';
        modalCtx.fillRect(580, 210, 150, 70);
        modalCtx.fillStyle = "black";                                                                  //730 = -40
        modalCtx.font = 'bold 40px Arial';
        modalCtx.fillText(Cdew.toFixed(1) + "°C", 610, 260);
    }

    function drawCloudModal(){
        var canvas = document.getElementById("cloudIMG");
        var modalCtx = canvas.getContext("2d");
        var img = document.getElementById("cloudBaseIMG");
        modalCtx.drawImage(img,10,10);
        modalCtx.scale(1,1);
    }
            
</script>

<body class="container bg-dark text-white">
    <div class="container horizontal grad">
        <h2 align="center">South Lewis High School</h2>
    </div>
    <div class="container-fluid" style="position: relative;">
        <div class="container-fluid rounded ml-2 py-2" style="background-color: #444444;"><h4 align="left">Current Weather</h4>   
        <input type="checkbox" id="showHideData" name="showHide" value="show" onclick="toggle('#dataSpot', this)"> Show Data   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
            <div id="dataSpot" style="display: none; background-color: #444444;">
                <div class="row pt-2">
                    <div class="col dataField" id="temp">Temperature&nbsp;<?php echo $temp; ?>&degF</div>
                    <div class="col dataField" id="press">Pressure&nbsp;<?php echo $press; ?>&nbsp;mb</div>
                </div>
                <div class="row pt-2">
                    <div class="col dataField" id="wetBulb"><script>document.getElementById("wetBulb").innerHTML = "Wetbulb&nbsp;"+convertWet()+"˚F";</script></div>
                    <div class="col dataField" id="cloud">Cloud Cover&nbsp;<?php echo 100 * $cloudCover; ?>%</div>
                </div>
                <div class="row pt-2">
                    <div class="col dataField" id="dpt">Dewpoint&nbsp;<?php echo $dew; ?>&degF</div>
                    <div class="col dataField" id="rh">Humidity&nbsp;<?php echo 100 * $hum; ?>%</div>
                </div>
                <div class="row pt-2">
                    <div class="col dataField" id="windData"><script>document.getElementById("windData").innerHTML = "Wind Speed&nbsp;"+windSpeed.toFixed(1)+" kts from "+windDir+"&deg;";</script></div>
                    <div class="col dataField"><?php echo $summary ?></div>
                </div>
                <div class="row pt-2">
                    <div class="col dataField" id="vis">Visibility&nbsp;<?php echo $visibility; ?> miles</div>
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
        <div class="button-panel" id="button-panel" style="position: absolute; top: 0%; right:0%;">
            <div class="button-panel-header" id="button-panelheader" onclick="toggleGuts('#button-panelGuts',this)">Reference Table Pages</div>
            <div class="button-panel-guts" id="button-panelGuts">
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
            <div class="col-sm-8" id="status"><?php echo $stationState ?></div>
            <script>
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
                }

                var windUnit = "kts";
                var tempUnit = "fahr";
                var dewUnit = "fahr";
                var wetUnit = "fahr";
                var pressUnit = "mb";

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


                dragElement(document.getElementById("button-panel"));

                function dragElement(elmnt) {
                    var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
                    if (document.getElementById(elmnt.id + "header")) {
                        /* if present, the header is where you move the DIV from:*/
                        document.getElementById(elmnt.id + "header").onmousedown = dragMouseDown;
                    } else {
                        /* otherwise, move the DIV from anywhere inside the DIV:*/
                        elmnt.onmousedown = dragMouseDown;
                    }

                    function dragMouseDown(e) {
                        e = e || window.event;
                        e.preventDefault();
                        // get the mouse cursor position at startup:
                        pos3 = e.clientX;
                        pos4 = e.clientY;
                        document.onmouseup = closeDragElement;
                        // call a function whenever the cursor moves:
                        document.onmousemove = elementDrag;
                    }

                    function elementDrag(e) {
                        e = e || window.event;
                        e.preventDefault();
                        // calculate the new cursor position:
                        pos1 = pos3 - e.clientX;
                        pos2 = pos4 - e.clientY;
                        pos3 = e.clientX;
                        pos4 = e.clientY;
                        // set the element's new position:
                        elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
                        elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
                    }

                    function closeDragElement() {
                        /* stop moving when mouse button is released:*/
                        document.onmouseup = null;
                        document.onmousemove = null;
                    }
                }
            </script>
    </div>
    <div class="row">
        <div class="col-sm-1"></div>
        <div class="col-sm-8">
            <a href="http://www.nysmesonet.org/weather/meteogram#network=nysm&stid=gfld">Glenfield Meteogram</a>
        </div>
    </div>
    <script>drawStationModel();</script>
</html>