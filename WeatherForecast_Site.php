<?php 
        function getCordinates($street, $city, $state){
            $address = $street . ", " . $city . ", " . $state;
            $address = urlencode($address);
            $url = "https://maps.googleapis.com/maps/api/geocode/xml?address={$address}&key=AIzaSyBFx4FaEYddgVz09pOD-hhxkmyt-gHXWqA";
            $resp_json = file_get_contents($url);
            $xml_res = simplexml_load_string($resp_json);
            $json_resp = json_encode($xml_res);
            $resp = json_decode($json_resp, true);
            if($xml_res->status=="OK"){
                $lati = $xml_res->result->geometry->location->lat;
                $longi = $xml_res->result->geometry->location->lng;
                if($lati && $longi){    
                    $data_arr = array();                              
                    array_push(
                        $data_arr, 
                            $lati, 
                            $longi
                        );                   
                    return $data_arr;                   
                }else{
                    return false;
                }
            }
            else {
                echo "<script type=\"text/javascript\">displayError(\"No results for input address.\");</script>";
                return false;
            }
        }

        function getWeatherCardData($latitude, $longitude, $city){
            $url = "https://api.forecast.io/forecast/d6fca095fe103602268dc22e673baa11/$latitude,$longitude?exclude=minutely,hourly,alerts,flags";
            $resp_json = file_get_contents($url);
            $jsondata = json_decode($resp_json, true);

            // Weather Card data
            $timezone =  isset($jsondata['timezone']) ? $jsondata['timezone'] : "";
            $temp = isset($jsondata['currently']['temperature']) ? $jsondata['currently']['temperature'] : "";
            $summary = isset($jsondata['currently']['summary']) ? $jsondata['currently']['summary'] : "";
            $humidity = isset($jsondata['currently']['humidity']) ? $jsondata['currently']['humidity'] : "";
            $pressure = isset($jsondata['currently']['pressure']) ? $jsondata['currently']['pressure'] : "";
            $windSpd = isset($jsondata['currently']['windSpeed']) ? $jsondata['currently']['windSpeed'] : "";
            $visibility = isset($jsondata['currently']['visibility']) ? $jsondata['currently']['visibility'] : "";
            $cloudCover =isset($jsondata['currently']['cloudCover']) ? $jsondata['currently']['cloudCover'] : "";
            $ozone = isset($jsondata['currently']['ozone']) ? $jsondata['currently']['ozone'] : "";
           
            displayWeatherCard($city, $timezone, (int)$temp, $summary, $humidity, $pressure,  $windSpd, $visibility, $cloudCover, $ozone);
            getWeatherTableData($jsondata, $latitude, $longitude, $timezone);
        }
        
        function getWeatherTableData($jsondata, $lat, $long, $timezone){
            $length = count($jsondata['daily']['data']);
            $data_arr = array("date" => array(), "status" => array(), "summary" => array(), "tempHigh" => array(), "tempLow" => array(), "windSpd" => array());
            for($i = 0; $i < $length; $i++)
            {
                array_push($data_arr["date"], $jsondata['daily']['data'][$i]['time']);
                array_push($data_arr['status'], $jsondata['daily']['data'][$i]['icon']);
                array_push($data_arr['summary'], $jsondata['daily']['data'][$i]['summary']);
                array_push($data_arr['tempHigh'], $jsondata['daily']['data'][$i]['temperatureHigh']);
                array_push($data_arr['tempLow'], $jsondata['daily']['data'][$i]['temperatureLow']);
                array_push($data_arr['windSpd'], $jsondata['daily']['data'][$i]['windSpeed']);
            }
            
            displayWeatherTable($data_arr, $length, $lat, $long, $timezone);
        }
        
        function displayWeatherTable($data_arr, $length, $lat, $long, $timezone){
            $arr_icons = array("clear-day"=>"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-12-512.png", 
                "clear-night" => "https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-12-512.png", 
                "rain" => "https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-04-512.png",
                "snow" => "https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-19-512.png",
                "sleet" => "https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-07-512.png",
                "wind" => "https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-27-512.png",
                "fog" => "https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-28-512.png",
                "cloudy" => "https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-01-512.png",
                "partly-cloudy-day" => "https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-02-512.png",
                "partly-cloudy-night" => "https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-02-512.png"
            );
            echo "<table class=\"table_Weather\" id=\"weatherTable\">";
            echo "<tr>";
            echo "<th><span class=\"entries\">Date</span></th>";
            echo "<th><span class=\"entries\">Status</span></th>";
            echo "<th><span class=\"entries\">Summary</span></th>";
            echo "<th><span class=\"entries\">TemperatureHigh</span></th>";
            echo "<th><span class=\"entries\">TemperatureLow</span></th>";
            echo "<th><span class=\"entries\">Wind Speed</span></th>";
            echo "</tr>";

            for($i = 0; $i < $length; $i++)
            {
                echo "<tr>";
                $time = $data_arr["date"][$i];
                $date = date("Y-m-d", $time);
                $status_url = $arr_icons[$data_arr["status"][$i]];
                $summary = $data_arr["summary"][$i];
                $tempHigh = $data_arr["tempHigh"][$i];
                $tempLow = $data_arr["tempLow"][$i];
                $windSpd = $data_arr["windSpd"][$i];
                echo "<td><span class=\"entries\">$date</span></td>";
                echo "<td><span class=\"entries\"><img src = \"$status_url\" height=\"40px\" width = \"40px\"></span></td>";
                echo "<td><span class=\"entries\"><form action=\"\" method=\"post\"><button class=\"btn\" type=\"submit\" name=\"summ\" value=$lat,$long,$time,'$timezone'>$summary</button></form></span></td>";
                echo "<td><span class=\"entries\">$tempHigh</span></td>";
                echo "<td><span class=\"entries\">$tempLow</span></td>";
                echo "<td><span class=\"entries\">$windSpd</span></td>";
                echo "</tr>";
            }

            echo "</table>";
        }

        function displayDetailedReport($latitude, $longitude, $time, $timezone){
            $url_detailReport = "https://api.darksky.net/forecast/d6fca095fe103602268dc22e673baa11/$latitude,$longitude,$time?exclude=minutely";
            $resp_json = file_get_contents($url_detailReport);
            $jsondata = json_decode($resp_json, true);
            
            $summary =  $jsondata['currently']['summary'];
            $temperature = $jsondata['currently']['temperature'];
            $icon = $jsondata['currently']['icon'];
            $prIntensity = $jsondata['currently']['precipIntensity'];
            $prProb = $jsondata['currently']['precipProbability'];
            $wndSpd = $jsondata['currently']['windSpeed'];
            $humidity = $jsondata['currently']['humidity'];
            $visibility = $jsondata['currently']['visibility'];
            $sunRise = $jsondata['daily']['data'][0]['sunriseTime'];
            $sunSet = $jsondata['daily']['data'][0]['sunsetTime'];

            displayDetailedWeatherReport($summary, (int)$temperature, $icon, $prIntensity, $prProb, $wndSpd, $humidity, $visibility, $sunRise, $sunSet, $timezone);
            
            $hourlyData = array();
            $length = count($jsondata['hourly']['data']);
            for($i = 0; $i < $length; $i++)
            {
                array_push($hourlyData, array($jsondata['hourly']['data'][$i]['time'], (int)$jsondata['hourly']['data'][$i]['temperature']));
            }
            echo "<br><br>";
            echo "<div id=\"detail_cont\" style=\"align-items:center;jutify-content:center;text-align:center;margin-top:20px\">";
            echo "<scan class=\"detail_header\" style=\"margin-left:0px\">Day's Hourly Weather</scan><br><br>";
            echo "<button class=\"btn\" onClick=\"draw(".json_encode($hourlyData).");\"><img id=\"arrow_chart\" src=\"https://cdn4.iconfinder.com/data/icons/geosm-e-commerce/18/point-down-512.png\" height=\"100px\" width=\"100px\"></button>";
            echo "</div>";
            echo "<div id =\"chart_div\" style=\"margin-left:16%;margin-bottom:5%\"></div>";
        }
        
        function displayDetailedWeatherReport($summary, $temperature, $icon, $prIntensity, $prProb, $wndSpd, $humidity, $visibility, $sunRise, $sunSet, $timezone){
            $arr_icons = array("clear-day"=>"https://cdn3.iconfinder.com/data/icons/weather-344/142/sun-512.png", 
                "clear-night" => "https://cdn3.iconfinder.com/data/icons/weather-344/142/sun-512.png", 
                "rain" => "https://cdn3.iconfinder.com/data/icons/weather-344/142/rain-512.png",
                "snow" => "https://cdn3.iconfinder.com/data/icons/weather-344/142/snow-512.png",
                "sleet" => "https://cdn3.iconfinder.com/data/icons/weather-344/142/lightning-512.png",
                "wind" => "https://cdn4.iconfinder.com/data/icons/the-weather-is-nice-today/64/weather_10-512.png",
                "fog" => "https://cdn3.iconfinder.com/data/icons/weather-344/142/cloudy-512.png",
                "cloudy" => "https://cdn3.iconfinder.com/data/icons/weather-344/142/cloud-512.png",
                "partly-cloudy-day" => "https://cdn3.iconfinder.com/data/icons/weather-344/142/sunny-512.png",
                "partly-cloudy-night" => "https://cdn3.iconfinder.com/data/icons/weather-344/142/sunny-512.png"
            );

            $dsply_precipitation = "";
            if($prIntensity <=0.001): $dsply_precipitation = "None";
            elseif($prIntensity <=0.015): $dsply_precipitation = "Very Light";
            elseif($prIntensity <=0.05): $dsply_precipitation = "Light";
            elseif($prIntensity <=0.1): $dsply_precipitation = "Moderate";
            elseif($prIntensity >0.1): $dsply_precipitation = "Heavy";
            endif;

            $chance_of_rain = $prProb * 100;
            $hum = $humidity * 100;


            $timezone = str_replace("'","",$timezone);
            $sunRisedate = new DateTime( gmdate("Y-m-d\TH:i:s\Z", $sunRise), new DateTimeZone( 'UTC' ) );
            $sunSetdate = new DateTime( gmdate("Y-m-d\TH:i:s\Z", $sunSet), new DateTimeZone( 'UTC' ) );
            $sunRisedate->setTimezone( new DateTimeZone($timezone));
            $sunSetdate->setTimezone( new DateTimeZone($timezone));
            
            $riseUnit = $sunRisedate->format('A');
            $riseHour = $sunRisedate->format('g');
            $setUnit = $sunSetdate->format('A');
            $setHour = $sunSetdate->format('g');

            $icon_url = $arr_icons[$icon];
            echo "<div class=\"weather_detail\" id=\"detailed_card\">";
            echo "<scan class=\"detail_header\">Daily Weather Detail</scan><br><br>";
            echo "<div class=\"card\" id=\"weatherCard\"><br>";
            echo "<div style=\"top:0;margin-top:0px;margin-bottom:0px;height:200px;width:480px;\">";
            echo "<div id=\"left-weather\">";
            echo "<scan class=\"city2\" id=\"city2\">$summary</scan><br>";
            echo "<scan class=\"temp2\" id=\"temp2\">$temperature<sup><img src=\"https://cdn3.iconfinder.com/data/icons/virtual-notebook/16/button_shape_oval-512.png\" height=\"13px\", width=\"13px\"></sup><scan class=\"city3\"> F</scan></scan>";
            echo "</div>";
            echo "<div id=\"right-weather\">";
            echo "<img src= $icon_url height=\"190px\" width=\"190px\">";
            echo "</div>";
            echo "</div>";
            echo "<table style=\"margin-left:30%;\">";
            echo "<tr>";
            echo "<td style=\"text-align:right;\"><scan class=\"city4\">Precipitation:</scan></td>";
            echo "<td><scan class=\"city5\">$dsply_precipitation</scan></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style=\"text-align:right;\"><scan class=\"city4\">Chance of Rain:&nbsp;</scan></td>";
            echo "<td><scan class=\"city5\">$chance_of_rain</scan> <scan class=\"city6\">&#x00025</scan></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style=\"text-align:right;\"><scan class=\"city4\">Wind Speed:</scan></td>";
            echo "<td><scan class=\"city5\">$wndSpd </scan><scan class=\"city6\">mph</scan></td>";
            echo "</tr>"; 
            echo "<tr>";
            echo "<td style=\"text-align:right;\"><scan class=\"city4\">Humidity:</scan></td>";
            echo "<td><scan class=\"city5\">$hum </scan><scan class=\"city6\">&#x00025</scan></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style=\"text-align:right;\"><scan class=\"city4\">Visibility:</scan></td>";
            echo "<td><scan class=\"city5\">$visibility </scan><scan class=\"city6\">mi</scan></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style=\"text-align:right;\"><scan class=\"city4\">Sunrise/Sunset:</scan></td>";
            echo "<td><scan class=\"city5\">$riseHour </scan></scan><scan class=\"city6\">$riseUnit  </scan> <scan class=\"city5\">/ $setHour </scan></scan><scan class=\"city6\">$setUnit</scan></td>";
            echo "</tr>";                  
            echo "</table>";
            echo "</div>";
            echo "</div>";
        }

        function displayWeatherCard($city, $timezone, $temp, $summary, $humidity, $pressure, $windSpd, $visibility, $cloudCover, $ozone){
            echo "<div class=\"card\" id=\"weatherCard\">";
            echo "<scan class=\"city\" id=\"city\">$city</scan><br>";
            echo "<scan class=\"timezone\" id=\"timezone\">$timezone</scan><br><br>";
            echo "<scan class=\"temp\" id=\"temp\">$temp<sup><img src=\"https://cdn3.iconfinder.com/data/icons/virtual-notebook/16/button_shape_oval-512.png\" height=\"13px\", width=\"13px\"></sup><scan class=\"city\">F</scan></scan><br>";
            echo "<scan class=\"city\" id=\"clear\">$summary</scan><br>";
            echo "<table>";
            echo "<tr>";
            if(!($humidity == "0" || $humidity == ""))
            {
                echo "<td><img title =\"Humidity\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-16-512.png\" height=\"30px\" width=\"30px\"><br><scan id=\"humidity\" class=\"card_v\">$humidity</scan></td>";
            }
               
            if(!($pressure == "0" || $pressure == ""))
            {
                echo "<td><img title =\"Pressure\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-25-512.png\" height=\"30px\" width=\"30px\"><br><scan id=\"pressure\" class=\"card_v\">$pressure</scan></td>";
            }
                
            if(!($windSpd == "0" || $windSpd == ""))
            {
                echo "<td><img title =\"Wind Speed\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-27-512.png\" height=\"30px\" width=\"30px\"><br><scan id=\"wndSpd\" class=\"card_v\">$windSpd</scan></td>";
            }
               
            if(!($visibility == "0" || $visibility == ""))
            {
                echo "<td><img title =\"Visibility\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-30-512.png\" height=\"30px\" width=\"30px\"><br><scan id=\"visibility\" class=\"card_v\">$visibility</scan></td>";
            }
               
            if(!($cloudCover == "0" || $cloudCover == ""))
            {
                echo "<td><img title =\"Cloud Cover\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-28-512.png\" height=\"30px\" width=\"30px\"><br><scan id=\"cloudCover\" class=\"card_v\">$cloudCover</scan></td>";
            }
                
            if(!($ozone == "0" || $ozone == ""))
            {
                echo "<td><img title =\"Ozone\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-24-512.png\" height=\"30px\" width=\"30px\"><br><scan id=\"Ozone\" class=\"card_v\">$ozone</scan></td>";
            }
                
            echo "</tr>";
            echo "</table>";
            echo "</div>";            
        }
?>
<html>

<head>
    <meta charset="utf-8">
    <style type="text/css">
        h2 {
            font-size: 50px;
            font-weight: 100;
            align-items: center;
            justify-content: center;
            text-align: center;
            text-shadow: 1 px 1 px;
            margin-left: 10%;
            margin-right: 10%;
            width: 80%;
            color: white;
            margin-bottom:10px;
        }

        h5 {
            font-size: 20px;
            font-weight: bold;
            text-shadow: 1 px 1 px;
            margin-left: 40%;
            color: white;
            float:left;
            margin-top:0px;
        }

        .field{
            font-size: 20px;
            font-weight: bold;
            text-shadow: 1 px 1 px;
            color: white;
            margin-bottom:0px;
            width:30px;
        }

        .errBorder{
            border-color: rgb(209, 109, 109);
            outline: 0;
            -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, 0.6);
            box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, 0.6);
        } 

        #left-field { 
            float:left;  
            width:18%; 
            height:60%;
            margin-left:10%;
        } 

        #right-field { 
            float:right;  
            width:70%; 
            height:60%;
        } 

        .CenterForm{
            height:40%;
            width:60%;
            margin-left:20%;
            margin-right:20%;
            margin-top:1%;
            Background-color:rgb(50,171,57);
            border-radius: 15px;
        }

        #left-content { 
            float:left;  
            width:48%; 
            height:70%;
        } 

        #middle-content { 
            float:left;  
            width:5px; 
            height:90%; 
            margin-left:2%;
            background-color:white;
            border-radius: 15px;
        } 

        #right-content{ 
            float:right; 
            width:48%; 
            height:70%; 
        }

        input[type=text], select {
            font-size: 16px;
            width: 55%;
            background: white;
            z-index: 100;
            position:relative;
        }

        .container {
            display: block;
            position: relative;
            padding-left: 25px;
            -moz-user-select: none;
            user-select: none;
            font-size: 20px;
            font-weight: bold;
            margin-left: 30%;
            top:10px;
            color: white;
            float:left;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        
        .container input {
            position: relative;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }
        
        .checkmark {
            position: absolute;
            top:3px;
            left: 0;
            height: 15px;
            width: 15px;
            background-color:white;
        }

        .container:hover input ~ .checkmark {
            background-color: #ccc;
        }
        
        .container input:checked ~ .checkmark {
            background-color: #2196F3;
        }
        
        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .container input:checked ~ .checkmark:after {
            display: block;
        }

        .container .checkmark:after {
            left:5px;
            top: 2px;
            width: 2px;
            height: 7px;
            border: solid white;
            border-width: 0 3px 3px 0;
            -webkit-transform: rotate(45deg);
            -ms-transform: rotate(45deg);
            transform: rotate(45deg);
        }

        #button1{
            width: 70px;
            height: 25px;
            background-color:white;
            border-radius: 8px;
            font-size:14px; 
            font-weight: bold;
        }

        #button2{
            width: 70px;
            height: 25px;
            background-color:white;
            border-radius: 8px;
            font-size:14px;
            font-weight: bold;
        }

        #container2{
            margin-left:28%;
            margin-top:0px;
        }

        .err {
            border-style: groove;
            background-color:rgb(240,240,240);
            height:25px;
            width:25%;
            margin-left:35%;
            text-align: center;
            justify-content: center;
            font-size:18px;
            margin-top:40px;
            font-weight: 600;
        }

        .city{
            font-size: 30px;
            font-weight: bold;
            color: white;
        }

        .card_v{
            font-size: 18px;
            font-weight: bold;
            color: white;
        }

        .timeZone{
            font-size: 18px;
            color: white;
            font-weight: 200;
        }

        .temp{
            font-size: 75px;
            font-weight: bold;
            color: white;
        }

        .card {
            margin-left: 32%;
            padding: 30px 30px;
            width: 350px;
            height: 250px;
            border-radius: 3px;
            background-color: rgba(92, 195, 243);
            box-shadow: 1px 2px 10px rgba(0, 0, 0, .2);
            border-radius: 20px;
        }

        sup{
            vertical-align:baseline;
            line-height: 0; 
            position: relative;
            top: -0.8em
        }

        .table_Weather table, td {
            padding: 15px 22px 5px 0px;
        }

        .table_Weather{
            margin: 0 auto;
            margin-top: 5%;
            width: 1000px;
            background-color: rgba(159, 201, 238);
            box-shadow: 1px 2px 10px rgba(0, 0, 0, .2);          
            border-collapse: collapse;
        }

        .table_Weather td, th{
            border: 2px solid rgb(97, 158, 212);
            text-align: center;
            align-items: center;
            justify-content: center;
            padding: 20px 0px 20px 0px;
        }

        .table_Weather th {
            padding: 10px 0px 10px 0px;
        }

        .entries{
            font-size: 20px;
            font-weight: bold;
            color: white;
        }

        .btn {
            border: none;
            background-color: inherit;
            color:inherit;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
        }
        .detail_header{
            font-size: 35px;
            font-weight: bold;
            text-shadow: 1px 1px;
            color: black;
            margin-left:47%;
        }

        .weather_detail{
        height: 450px;
        width:600px;
        margin-left:15%;
        margin-top:5%;
        margin-bottom:15px;
        }

        .weather_detail .card {
            width: 480px;
            height: 400px;
            background-color: rgba(167, 208, 217);
            border-radius: 20px;
            padding: 0px;
        }

        .city2{
            font-size: 28px;
            font-weight: bold;
            color: white;
        }

        .city3{
            font-size: 40px;
            font-weight: bold;
            color: white;
        }

        .city4{
            font-size: 18px;
            font-weight: bold;
            color: white;
        }

        .city6{
            font-size: 14px;
            font-weight: bold;
            color: white;
        }

        .city5{
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        .temp2{
            font-size: 65px;
            font-weight: bold;
            color: white;
        }

        .weather_detail td {
            padding:0px;
        }

        #left-weather {
            margin-top:40px;
            margin-left:8%;
            float:left;  
            width:40%; 
        } 

        #right-weather { 
            top:0;
            float:right;  
            width:50%;
        } 

        *{
            outline: none;
         } 
    </style>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        function checkForm(form){
            try{
                var isfilled = true;
                var err = "Missing fields:";
                if(document.getElementById("currentLocation").checked)
                {
                    sessionStorage.setItem("isChecked", "on");
                    getCurrentLocation();
                    return isfilled;
                }
                else
                {
                    if(form.street.value == "")
                    { 
                        err += "Street!";
                        document.getElementById("street_id").setAttribute("class", "errBorder");
                        isfilled = false;
                    }
                    else
                    {
                        document.getElementById("street_id").setAttribute("class", "");
                    } 
                    if(form.city.value == "") 
                    { 
                        err += " City!";
                        document.getElementById("city_id").setAttribute("class", "errBorder");
                        isfilled = false;
                    }else{ document.getElementById("city_id").setAttribute("class", "");}

                    if(form.state.value == "") 
                    { 
                        err += " State!";
                        document.getElementById("state_id").setAttribute("class", "errBorder");
                        isfilled = false;
                    }else{ document.getElementById("state_id").setAttribute("class", "");}
                }
                if(!isfilled)
                {
                    document.getElementById("errText").innerHTML = err;
                    document.getElementById("errText").setAttribute("class", "err");
                }
                else{
                    sessionStorage.setItem("isChecked", "off");
                    sessionStorage.setItem("street_value", form.street.value);
                    sessionStorage.setItem("city_value", form.city.value);
                    sessionStorage.setItem("state_value", form.state.value);

                    document.getElementById("errText").innerHTML = "";
                    document.getElementById("errText").setAttribute("class", "");
                }
                return isfilled; 
            }
            catch(err)
            {
                displayError("Error in Validating data!")
                return false;
            }
        }
        
        function getCurrentLocation(){
            try{
                var url = "http://ip-api.com/json/";
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.open("GET", url, false); 
                xmlhttp.send(); 
                jsonDoc = xmlhttp.responseText;
                var obj = JSON.parse(jsonDoc);
                var latitude = obj.lat;
                var longitude = obj.lon;
                var city = obj.city;
                var arr_data = [latitude, longitude, city];
                document.getElementById("curr_cords").value = JSON.stringify(arr_data);
            }
            catch(err)
            {
                displayError("Error in fetching current location.")
            }
        }

        function resetAllFields(){
           if(document.getElementById("currentLocation").checked)
           {
                document.getElementById("street_id").value = "";
                document.getElementById("street_id").disabled = true;
                document.getElementById("city_id").value = "";
                document.getElementById("city_id").disabled = true;
                document.getElementById("state_id").selectedIndex = 0;
                document.getElementById("state_id").disabled = true;
                document.getElementById("street_id").setAttribute("class", "");
                document.getElementById("city_id").setAttribute("class", "");
                document.getElementById("state_id").setAttribute("class", "");
                getCurrentLocation();
           }
           else{
                document.getElementById("street_id").disabled = false;
                document.getElementById("city_id").disabled = false;
                document.getElementById("state_id").disabled = false;
           }
        }

        var hr_data; 
        function draw(hourly_data){
            hr_data = hourly_data;
            google.charts.load('current', {packages: ['corechart', 'line']});
            google.charts.setOnLoadCallback(drawBasic);
        }
       
        function drawBasic() {
            if(document.getElementById("arrow_chart").src == "https://cdn0.iconfinder.com/data/icons/navigation-set-arrows-part-one/32/ExpandLess-512.png")
            {
                document.getElementById("arrow_chart").src = "https://cdn4.iconfinder.com/data/icons/geosm-e-commerce/18/point-down-512.png";
                document.getElementById('chart_div').innerHTML = "";
            } 
            else{
                document.getElementById("arrow_chart").src = "https://cdn0.iconfinder.com/data/icons/navigation-set-arrows-part-one/32/ExpandLess-512.png";
                var data = new google.visualization.DataTable();
                data.addColumn('number', 'X');
                data.addColumn('number', 'T');
                var data_rows = [];
                for(var i = 0; i < hr_data.length; i++)
                {
                var temp_arr = [i, hr_data[i][1]];
                data_rows[i] = temp_arr;   
                }       
                data.addRows(data_rows);
                var options = {
                    hAxis: { title: 'Time'},
                    vAxis: { title: 'Temperature', textPosition: 'none'},
                    'width':900,
                    'height':300,
                };
                var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
                chart.draw(data, options);
            }           
        }

        function ClearAllContents(){
            document.getElementById("street_id").value = "";
            document.getElementById("street_id").disabled = false;
            document.getElementById("city_id").value = "";
            document.getElementById("city_id").disabled = false;
            document.getElementById("state_id").selectedIndex = 0;
            document.getElementById("state_id").disabled = false;
            sessionStorage.clear();
            document.getElementById("errText").setAttribute("class", "");
            document.getElementById("errText").innerHTML = "";
            
            var ele2 = document.getElementById("weatherTable");
            if(ele2)
            {
                ele2.parentNode.removeChild(ele2);
            }
            
            var g = document.getElementById("weatherCard");
            if(g)
            {
                g.parentNode.removeChild(g);
            }

            var ele3 = document.getElementById("chart_div");
            if(ele3)
            {
                ele3.parentNode.removeChild(ele3);
            }

            var ele4 = document.getElementById("detailed_card");
            if(ele4)
            {
                ele4.parentNode.removeChild(ele4);
            }
            var ele5 = document.getElementById("detail_cont");
            if(ele5)
            {
                ele5.parentNode.removeChild(ele5);
            }
        }

        function setAll(){
            if(sessionStorage.getItem("isChecked") == "on")
            {
                document.getElementById("street_id").value = "";
                document.getElementById("street_id").disabled = true;
                document.getElementById("city_id").value = "";
                document.getElementById("city_id").disabled = true;
                document.getElementById("state_id").selectedIndex = 0;
                document.getElementById("state_id").disabled = true;
                document.getElementById("currentLocation").checked = true;
            }
            else{
                document.getElementById("street_id").value = sessionStorage.getItem("street_value");
                document.getElementById("city_id").value = sessionStorage.getItem("city_value");
                if(!sessionStorage.getItem("state_value") == "")
                {
                    document.getElementById("state_id").value = sessionStorage.getItem("state_value");
                }
                else
                {
                    document.getElementById("state_id").selectedIndex = 0;
                }
                document.getElementById("currentLocation").checked = false;
            }
        }
        function displayError(err){
            document.getElementById("errText").innerHTML = err;
            document.getElementById("errText").setAttribute("class", "err");
        }
    </script>
</head>

<body onLoad="setAll()">
    <div class="CenterForm">
        <form method="POST" action="" onsubmit="return checkForm(this);">
                <h2><em>Weather Search</em></h2>
                <div style="height:55%;">
                    <div id="left-content">
                        <div id="left-field">
                            <label for="street" class="field">Street</label><br>
                            <label for="city" class="field">City </label><br><br>
                            <label for="state" class="field">State</label> 
                        </div>
                        <div id="right-field">
                            <input type="text" id="street_id" name="street"><br>
                            <input type="text" id="city_id" name="city"><br><br>
                            <select id="state_id" name="state" onfocus='this.size=15;' onblur='this.size=1;' onchange='this.size=1; this.blur();' style="width:70%;">
                                <option value="" >State</option>
                                <option value="" disabled="disabled">------------------------------------</option>
                                <option value="AL">Alabama</option>
                                <option value="AK">Alaska</option>
                                <option value="AZ">Arizona</option>
                                <option value="AR">Arkansas</option>
                                <option value="CA">California</option>
                                <option value="CO">Colorado</option>
                                <option value="CT">Connecticut</option>
                                <option value="DE">Delaware</option>
                                <option value="DC">District Of Columbia</option>
                                <option value="FL">Florida</option>
                                <option value="GA">Georgia</option>
                                <option value="HI">Hawaii</option>
                                <option value="ID">Idaho</option>
                                <option value="IL">Illinois</option>
                                <option value="IN">Indiana</option>
                                <option value="IA">Iowa</option>
                                <option value="KS">Kansas</option>
                                <option value="KY">Kentucky</option>
                                <option value="LA">Louisiana</option>
                                <option value="ME">Maine</option>
                                <option value="MD">Maryland</option>
                                <option value="MA">Massachusetts</option>
                                <option value="MI">Michigan</option>
                                <option value="MN">Minnesota</option>
                                <option value="MS">Mississippi</option>
                                <option value="MO">Missouri</option>
                                <option value="MT">Montana</option>
                                <option value="NE">Nebraska</option>
                                <option value="NV">Nevada</option>
                                <option value="NH">New Hampshire</option>
                                <option value="NJ">New Jersey</option>
                                <option value="NM">New Mexico</option>
                                <option value="NY">New York</option>
                                <option value="NC">North Carolina</option>
                                <option value="ND">North Dakota</option>
                                <option value="OH">Ohio</option>
                                <option value="OK">Oklahoma</option>
                                <option value="OR">Oregon</option>
                                <option value="PA">Pennsylvania</option>
                                <option value="RI">Rhode Island</option>
                                <option value="SC">South Carolina</option>
                                <option value="SD">South Dakota</option>
                                <option value="TN">Tennessee</option>
                                <option value="TX">Texas</option>
                                <option value="UT">Utah</option>
                                <option value="VT">Vermont</option>
                                <option value="VA">Virginia</option>
                                <option value="WA">Washington</option>
                                <option value="WV">West Virginia</option>
                                <option value="WI">Wisconsin</option>
                                <option value="WY">Wyoming</option>
                            </select>
                        </div>
                    </div>
                    <div id="middle-content">
                    </div>
                    <div id="right-content">
                        <label class="container">Current Location
                            <input type="checkbox" id="currentLocation" name="curr_loc" onChange="resetAllFields()">
                            <span class="checkmark"></span>
                        </label>
                    </div>
                </div>
            
                <div id="container2">
                    <input type="submit" id="button1" name="search" value="search">
                    <input type="reset" id="button2" name="clear" value="clear" onClick="ClearAllContents();">
                    <input type="hidden" id="curr_cords" name="curr_cords" value="">
                </div>
        </form>
    </div>
    <p id="errText"></p>
    <?php
        $street = "";
        $city = "";
        $state = "";
        if (isset($_POST["search"])){
            if(!empty($_POST["street"]) && !empty($_POST["city"]) && !empty($_POST["state"]))
            {
                $street = $_POST['street'];
                $city = $_POST['city'];
                $state = $_POST['state'];

                $cords = getCordinates($street, $city, $state);
                if($cords){
                    $forecast_data  = getWeatherCardData($cords[0], $cords[1], $city);
                }
            }
            elseif(isset($_POST["curr_loc"]))
            {
                if(isset($_POST["curr_cords"]))
                {
                    $arr_data = json_decode($_POST['curr_cords']);
                    if($arr_data){
                        $forecast_data  = getWeatherCardData($arr_data[0], $arr_data[1], $arr_data[2]);
                    }
                }
            }
        }        

        if(isset($_POST['summ'])){
            $arr_v = explode(",",$_POST['summ']);
            displayDetailedReport($arr_v[0], $arr_v[1], $arr_v[2], $arr_v[3]);
        }
    ?>
</body>

</html>