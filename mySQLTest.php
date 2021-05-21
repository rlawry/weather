<?php
    $servername = "127.0.0.1";
    $username = "pma";
    $password = "";
    $dbname = "weather";
    $conn = new mysqli($servername, $username, $password, $dbname);

    $api_url = 'https://api.ambientweather.net/v1/devices?applicationKey=698db98a3d16443eabc27e05981707dd1816f9fdce234080b1d322c757fb3bee&apiKey=4e79425cd75740b48c9755b55d59ad830393733a125a44f2b52b85e4e1310f48';
    $api_url2 = 'https://api.darksky.net/forecast/1ab54032d29c1e5563ca79d31c65c951/43.6361,-75.3944';
    $fileContents = file_get_contents($api_url);
    $file2Contents = file_get_contents($api_url2);

    $guts = json_decode($fileContents, true);
    $guts2 = json_decode($file2Contents, true);
    echo $guts2['currently']['cloudCover'];
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

    if(multiKeyExists($guts,'tempf')) {
        foreach ($guts[0]['lastData'] as $row => $val){
            $dateutc = $guts[0]['lastData']['dateutc'];
            $tempf = $guts[0]['lastData']['tempf'];
            $dewPoint = $guts[0]['lastData']['dewPoint'];
            $baromrelin = round($guts[0]['lastData']['baromrelin'] * 33.8639, 1, PHP_ROUND_HALF_UP);
            $humidity = $guts[0]['lastData']['humidity'];
            $windspeedmph = $guts[0]['lastData']['windspeedmph'];
            $winddir = $guts[0]['lastData']['winddir'];
            $gust = $guts[0]['lastData']['windgustmph'];
            $hourlyrainin = $guts[0]['lastData']['hourlyrainin'];  
        }
        $cloudCover = $guts2['currently']['cloudCover'];
        $summary = $guts2['currently']['summary'];
        $visibility = $guts2['currently']['visibility'];
        $icon = $guts2['currently']['icon'];
        $stationState = "Online";
    }
    else {
        $dateutc = $guts[0]['lastData']['dateutc'];
        $windspeedmph = $guts2['currently']['windSpeed'];
        $winddir = $guts2['currently']['windBearing'];
        $cloudCover = $guts2['currently']['cloudCover'];
        $summary = $guts2['currently']['summary'];
        $visibility = $guts2['currently']['visibility'];
        $icon = $guts2['currently']['icon'];
        $gust = $guts2['currently']['windGust'];
        $tempf = $guts2['currently']['temperature'];
        $dewPoint = $guts2['currently']['dewPoint'];
        $baromrelin = round($guts2['currently']['pressure'], 1, PHP_ROUND_HALF_UP);
        $humidity = $guts2['currently']['humidity']*100;
        $stationState = "Offline";
    }
        $sql = "INSERT INTO weatherstation(dateutc, tempf, dewPoint, baromrelin, humidity, windspeedmph, winddir, gust, hourlyrainin, cloudcover, visibility, summary, icon, stationState) VALUES('$dateutc', '$tempf', '$dewPoint', '$baromrelin', '$humidity', '$windspeedmph', '$winddir', '$gust', '$hourlyrainin', '$cloudCover', '$visibility', '$summary', '$icon', '$stationState');";

        if($conn->connect_error){
            die("Connection failed: " . $conn->connect_error);
        }
        if($conn->query($sql) === TRUE){
            echo "Oh YEAH MONKAY";
        } else {
            echo "Whooooooops: " . $conn->error;
        }
    
   
    $conn->close();
?>

<html>
    <div id="test"></div>
</html>