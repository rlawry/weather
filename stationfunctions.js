function loadVariables(){
    document.getElementById("temp").innerHTML = "Temperature " + tempF + "&degF";
    document.getElementById("press").innerHTML = "Pressure " + MBpressure + " mb";
    document.getElementById("wetBulb").innerHTML = "Wetbulb " + convertWet() + "&degF";
    document.getElementById("cloud").innerHTML = "Cloud Cover " + cloudCover*100 +"%";
    document.getElementById("dpt").innerHTML = "Dewpoint " +dewPOYNT+ "&degF";
    document.getElementById("rh").innerHTML = "Humidity " + rh + "%";
    document.getElementById("windData").innerHTML = "Wind Speed "+windSpeed.toFixed(1)+" kts from " +windDir+"&deg;";
    document.getElementById("summary").innerHTML = whatWeather;
    document.getElementById("vis").innerHTML = "Visibility " +visibility+" miles";
    document.getElementById("status").innerHTML = stationState;
    document.getElementById("currentTime").innerHTML = dayjs(currentDate).format('dd hh:mm a');
}

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

    draw_dp(dpres, p_trend, pressureChange);
    dpres.x = station.x + 38;
    dpres.y = station.y - 10;
    dpres_box.graphics.f("rgba(255,255,255,0.2)").dr(dpres.x - 2, dpres.y, 40, 22);
    p_trend.x = station.x + 75;
    p_trend.y = station.y - 10;
    document.getElementById("timeInterval").innerHTML = "Pressure Trend over "+ (timeInterval * 2.77778e-7).toFixed(2) + " hours";

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
    loadVariables();
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
   
    var difference = Ctemp - convertFtoC(wetbulb);
    var roundedDifference = Math.round(difference);

    roundedDifference = (648/15)*(roundedDifference+(1560/648));
    difference = (648/15)*(difference+(1560/648))+rectWidth/2;

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

    var difference = Ctemp - convertFtoC(wetbulb);

    var roundedDifference = Math.round(difference);

    roundedDifference = (648/15)*(roundedDifference+(1560/648));
    difference = (648/15)*(difference+(1560/648))+rectWidth/2;

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

function drawCharts(){
    var pressureCtx = document.getElementById('pressureChart').getContext('2d');
    var tempsCtx = document.getElementById('tempsChart').getContext('2d');
    var windCtx = document.getElementById('windChart').getContext('2d');
    var pressureArray = [];
    var tempArray = [];
    var dewArray = [];
    var windArray = [];
    var dataLabels = [];
    var dateIndex;
    for(var x = dataArray.length-1, y = 0; x>0; x--, y++){
        pressureArray[y] = Number(parseFloat(dataArray[x]['baromrelin']));
        tempArray[y] = Number(parseFloat(dataArray[x]['tempf']));
        dewArray[y] = Number(parseFloat(dataArray[x]['dewPoint']));
        windArray[y] = Number(parseFloat(dataArray[x]['windspeedmph']));
        dateIndex = dataArray[x]['dateutc'];
        dateIndex = Number(dateIndex);
        dataLabels[y] = dayjs(dateIndex).format('dd hh:mm a');
    }
    var pressureMin = Math.min.apply(null, pressureArray),
        pressureMax = Math.max.apply(null, pressureArray);
    var tempMin = Math.min.apply(null, dewArray),
        tempMax = Math.max.apply(null, tempArray);

    var smoothWindArray = smooth(windArray);

    var windMin = Math.min.apply(null, smoothWindArray),
        windMax = Math.max.apply(null, smoothWindArray);
    var pressureChart = new Chart(pressureCtx, {
        type: 'line',
        data: {
            labels: dataLabels,
            datasets: [{
                label: 'Relative Barometric Pressure (mb)',
                data: pressureArray,
                backgroundColor: [
                    'rgba(255, 255, 200, 1)',
                ],
                borderColor: [
                    'rgba(255, 255, 200, 1)',
                ],
                borderWidth: 1,
                fontColor: 'white',
                color: 'white'
            }
            ]
        },
        options: {
            scales: {
                y: {
                    min: pressureMin,
                    max: pressureMax,
                    beginAtZero: false,
                    ticks:{
                        fontColor: 'white',
                        color: 'white'
                    }
                },
                x: {
                    ticks:{
                        fontColor: 'white',
                        color: 'white'
                    }
                }
            },
            elements: {
                point:{
                    radius: 0.5
                }
            },
            plugins:{
                legend:{
                        labels:{
                            font:{
                                size:24,
                            },
                            color: 'white'
                        }
                }
            }
        }
    });
    var tempsChart = new Chart(tempsCtx, {
        type: 'line',
        data: {
            labels: dataLabels,
            datasets: [{
                label: 'Temperature (°F)',
                data: tempArray,
                backgroundColor: [
                    'rgba(255, 100, 100, 1)',
                ],
                borderColor: [
                    'rgba(255, 100, 100, 1)',
                ],
                borderWidth: 1,
                fontColor: 'white',
                color: 'white'
            },
            {
                label: 'Dewpoint (°F)',
                data: dewArray,
                backgroundColor: [
                    'rgba(100, 255, 100, 1)',
                ],
                borderColor: [
                    'rgba(100, 255, 100, 1)',
                ],
                borderWidth: 1,
                fontColor: 'white',
                color: 'white'
            }
            ]
        },
        options: {
            scales: {
                y: {
                    min: tempMin,
                    max: tempMax,
                    beginAtZero: false,
                    ticks:{
                        fontColor: 'white',
                        color: 'white'
                    }
                },
                x: {
                    ticks:{
                        fontColor: 'white',
                        color: 'white'
                    }
                }
            },
            elements: {
                point:{
                    radius: 0.5
                }
            },
            plugins:{
                legend:{
                        labels:{
                            font:{
                                size:24,
                            },
                            color: 'white'
                        }
                }
            }
        }
    });
    var windChart = new Chart(windCtx, {
        type: 'line',
        data: {
            labels: dataLabels,
            datasets: [{
                label: 'Wind Speed (kts)',
                data: smoothWindArray,
                backgroundColor: [
                    'rgba(100, 100, 255, 1)',
                ],
                borderColor: [
                    'rgba(100, 100, 255, 1)',
                ],
                borderWidth: 1,
                fontColor: 'white',
                color: 'white'
            }
            ]
        },
        options: {
            scales: {
                y: {
                    min: windMin,
                    max: windMax,
                    beginAtZero: false,
                    ticks:{
                        fontColor: 'white',
                        color: 'white'
                    }
                },
                x: {
                    ticks:{
                        fontColor: 'white',
                        color: 'white'
                    }
                }
            },
            elements: {
                point:{
                    radius: 0.5
                }
            },
            plugins:{
                legend:{
                        labels:{
                            font:{
                                size:24,
                            },
                            color: 'white'
                        }
                }
            }
        }
    });
}

function smooth(array){
    var smoothFactor = 0.42;
    var smoothResult = [array[0]];
    for(var i=1; array.length > i; i++){
        smoothResult[i] = smoothFactor*array[i] + (1-smoothFactor)*smoothResult[i-1];  //	α * x\t + (1 - α) * s\t-1
    }
    return smoothResult;

}

function draw_weather(obj, state) {
    obj.sourceRect = new createjs.Rectangle(state * 33, 0, 33, 33);
}

function draw_sky(obj, sky_state) {
    obj.graphics.clear();
    if (sky_state == 1) {
        obj.graphics.setStrokeStyle(8).beginStroke("#000").beginFill("rgba(0,0,0,0)").moveTo(0, 24).lineTo(0, -24);
    } else if (sky_state == 7) {
        obj.graphics.setStrokeStyle(0).beginStroke("#000").beginFill("rgba(0,0,0,1)").drawCircle(0, 0, 24).setStrokeStyle(8).beginStroke("#fff").beginFill("rgba(0,0,0,0)").moveTo(0, 24).lineTo(0, -24);
    } else if (sky_state == 9) {
        obj.graphics.setStrokeStyle(4).beginStroke("#000").beginFill("rgba(0,0,0,0)").moveTo(25 * Math.cos(0.785398), 25 * Math.sin(0.785398)).lt(-25 * Math.cos(0.785398), -25 * Math.sin(0.785398)).mt(-25 * Math.cos(0.785398), 25 * Math.sin(0.785398)).lt(25 * Math.cos(0.785398), -25 * Math.sin(0.785398));
    } else if (sky_state == 0) {
        // do nothing
    } else {
        obj.graphics.ss(0).s("#000").f("rgba(0,0,0,1)").mt(0, 0).lt(0, -25).a(0, 0, 25, -1 * Math.PI / 2, Math.PI / 2 * (Math.floor(sky_state / 2) - 1));
        if (sky_state == 3) {
            obj.graphics.ss(3).s("#000").f("rgba(0,0,0,0)").mt(1, 0).lt(1, 25);
        } else if (sky_state == 5) {
            obj.graphics.ss(3).s("#000").f("rgba(0,0,0,0)").mt(0, 0).lt(-25, 0);
        }
    }
}

function draw_T_Td(obj1, obj2) {
    var my_temp = Math.round(tempF);
    var my_dewpt = Math.round(dewPOYNT);
    obj1.text = my_temp;
    obj2.text = my_dewpt;

}

function draw_dp(obj, trend, pressureTrend) {
    var dp = pressureTrend;
    if (dp == "") {
        obj.text = "";
        trend.sourceRect = new createjs.Rectangle(0, 0, 20, 20);       
    } else if (Math.abs(dp) > 9.9) {
        obj.text = "???";
        trend.sourceRect = new createjs.Rectangle(0, 0, 20, 20);
    } else {
        
        if (dp > 0) {
            obj.text = "+" + Math.round(dp * 10);
            trend.sourceRect = new createjs.Rectangle(40, 0, 20, 20);
        } else if (dp < 0) {
            obj.text = Math.round(dp * 10);
            trend.sourceRect = new createjs.Rectangle(100, 0, 20, 20);
        } else {
            obj.text = "0";       
            trend.sourceRect = new createjs.Rectangle(40, 0, 20, 20);
        }

    }
}

function draw_P(obj) {
    var P_uncoded = MBpressure;
    if (P_uncoded != "") {
        if (P_uncoded <= 1050 && P_uncoded >= 901) {
            if (P_uncoded >= 1000) {
                P_coded = Math.round((P_uncoded - 1000) * 10);
                while (P_coded.toString().length < 3) {
                    P_coded = "0" + P_coded.toString();
                }
                obj.text = P_coded;
            } else {
                obj.text = Math.round((P_uncoded - 900) * 10);
            }
        } else {
            obj.text = "???";
        }

    } else {
        obj.text = "";
    }
}

function draw_vis(obj) {
    if(visibility<10&&visibility>0){
        obj.getChildByName("vis_whole").text = parseInt(visibilityWhole);
    }
    else if(visibility>=10){
        obj.getChildByName("vis_whole").text = "∞";
    } else {
        obj.getChildByName("vis_whole").text = "";
    }
    
    if (visIndexReal > 0 && visIndexReal<5) {
        obj.getChildByName("vis_num").text = "1";
        obj.getChildByName("vis_denom").text = Math.pow(2, visIndexReal);
        obj.getChildByName("vis_line").graphics.ss(2).s("#000").mt(5, 9).lt(25, 9);
        obj.getChildByName("vis_whole").x = 0;
        if (visibility == 0) {
            obj.getChildByName("vis_whole").text = "";
        }
    } else {
        obj.getChildByName("vis_num").text = "";
        obj.getChildByName("vis_denom").text = "";
        obj.getChildByName("vis_line").graphics.ss(0).s("#fff").mt(5, 9).lt(25, 9);
        obj.getChildByName("vis_whole").x = 20;
    }
}

function draw_wind(obj) {
    var wind_speed = parseFloat(windSpeed);
    var wind_direction = parseInt(windDir);
    var flag_width = 20;
    var flag_length = 40;
    var flag_separation = 18;
    var stickLength = 130;
    var offset = 15;
    if (wind_speed == "") {
        wind_speed = 0;
    }

    obj.graphics.clear();
    if (wind_speed <= 0.5) {
        obj.graphics.ss(2).s("#000").f("rgba(0,0,0,0)").dc(0, 0, 33);
    } else if (wind_speed > 0.5 && wind_speed < 2.5){
        obj.graphics.ss(3).s("#000").f("rgba(0,0,0,0)").mt(0, 0).lt(0, -stickLength);
        obj.rotation = wind_direction;
    } else if (wind_speed >= 2.5 && wind_speed < 7.5){
        marker = -(stickLength - 1);
        obj.graphics.ss(3).s("#000").f("rgba(0,0,0,0)").mt(0, 0).lt(0, -stickLength);
        obj.graphics.ss(2).s("#000").f("rgba(0,0,0,0)").mt(0, marker + flag_length / 2.121).lt(flag_length / 2.121, marker-offset+45/2);
        obj.rotation = wind_direction;
    } else {
        obj.graphics.ss(3).s("#000").f("rgba(0,0,0,0)").mt(0, 0).lt(0, -stickLength);
        // round up/down
        wind_speed += 2.5;
        marker = -(stickLength - 1);

        while (wind_speed >= 50) {
            obj.graphics.ss(1).s("#000").f("rgba(0,0,0,1)").mt(0, marker).lt(flag_length, marker + flag_width / 2).lt(0, marker + flag_width - offset);
            marker += flag_width + 5;
            wind_speed -= 50;
        }

        while (wind_speed >= 10) {
            obj.graphics.ss(2).s("#000").f("rgba(0,0,0,0)").mt(0, marker).lt(flag_length, marker-offset);
            marker += flag_separation;
            wind_speed -= 10;
        }

        if (wind_speed >= 5) {
            if (marker == -149) {
                obj.graphics.ss(2).s("#000").f("rgba(0,0,0,0)").mt(0, marker + flag_length / 2.121).lt(flag_length / 2.121, marker-offset/2);
            } else {
                obj.graphics.ss(2).s("#000").f("rgba(0,0,0,0)").mt(0, marker).lt(flag_length / 2, marker-offset/2);
            }
        }
        obj.rotation = wind_direction;
    }

}
