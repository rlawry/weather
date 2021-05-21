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
    console.log("temp "+ my_temp);
    console.log("dew " + my_dewpt);
    obj1.text = my_temp;
    obj2.text = my_dewpt;

}

function draw_dp(obj, trend, pressureTrend) {
    var dp = pressureTrend;
    console.log(dp + "TREND");
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
            trend.sourceRect = new createjs.Rectangle(20, 0, 20, 20);
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
