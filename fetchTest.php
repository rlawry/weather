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
        $result1 = mysqli_query($conn, $sql1) or die("ERROR IN SELECTING FIRST". mysqli_error($conn));
        $result2 = mysqli_query($conn, $sql2) or die("ERROR IN SELECTING SECOND". mysqli_error($conn));

        $weatherArray1 = array();
        $weatherArray2 = array();
        
        while($row = mysqli_fetch_assoc($result1))
        {
            $weatherArray1[]=$row;
        }
        while($row = mysqli_fetch_assoc($result2))
        {
            $weatherArray2[]=$row;
        }

        print_r($weatherArray1);
        echo "<br>";
        $pressure1 = $weatherArray1[0]['baromrelin'];
        $pressure2 = $weatherArray2[0]['baromrelin'];
        if($pressure1<40){$pressure1 = $pressure1 * 33.8639; }
        if($pressure2<40){$pressure2 = $pressure2 * 33.8639; }
        echo $pressure1." pressure 1, ".$pressure2." pressure 2 <br>";
        $pressureChange = round($pressure1 - $pressure2, 2, PHP_ROUND_HALF_UP);
        echo $pressureChange;

   
?>