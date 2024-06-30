<?php
$serverName = "localhost";
$userName= "root";
$password = "";


$conn = mysqli_connect($serverName, $userName, $password);
if($conn){
    // echo "Connection Successful <br>";
}
else{
    // echo "Failed to connect".mysqli_connect_error();
}


#Creating a database for the weather
$createDatabase = "CREATE DATABASE IF NOT EXISTS prototype3";
if (mysqli_query($conn, $createDatabase)) {
    // echo "Database Created or already Exists <br>"; // Commented out echo
} else {
    // echo "Failed to create database <br>" . mysqli_connect_error(); // Commented out echo
}


// Select the created database
mysqli_select_db($conn, 'prototype3');


// Create the weather table if it doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS weather (
    city VARCHAR(150) NOT NULL,
    main_weather VARCHAR(150) NOT NULL,
    weather_description VARCHAR(150) NOT NULL,
    temperature FLOAT NOT NULL,
    humidity FLOAT NOT NULL,
    wind FLOAT NOT NULL,
    pressure FLOAT NOT NULL,
    weather_icon VARCHAR(20) NOT NULL,
    wind_direction FLOAT NOT NULL,
    visibility FLOAT NOT NULL,
    feels_like FLOAT NOT NULL,
    temp_max FLOAT NOT NULL,
    temp_min FLOAT NOT NULL,
    forecasted_time DATETIME NOT NULL
)";
if (!mysqli_query($conn, $createTable)) {
    die("Failed to create table: " . mysqli_error($conn));
}


// Get the city name from the query string or use default
$cityName = isset($_GET['q']) ? $_GET['q'] : "North Lincolnshire";


// API key for OpenWeatherMap
$apiKey = "b9798d225b1a99f9180aa4f949faa8c0";


// API URL for weather data
$url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($cityName) . "&appid=" . $apiKey . "&units=metric";


// Get weather data from the API
$response = file_get_contents($url);
$data = json_decode($response, true);


// Check if weather data is available
if (isset($data['main']) && !empty($data['main'])) {
    $current_time = time(); // Current UNIX timestamp
    $two_hours_ago = $current_time - (2 * 60 * 60); // Two hours ago in UNIX timestamp


    $forecast_timestamp = $data['dt']; // UNIX timestamp of forecast
    $forecast_time = date('Y-m-d h:i:s A', $forecast_timestamp); // Format forecast time


    // Check if the forecast time is within the last 2 hours
    if ($forecast_timestamp >= $two_hours_ago && $forecast_timestamp <= $current_time) {
        $main_weather = $data['weather'][0]['main'];
        $weather_description = $data['weather'][0]['description'];
        $temperature = $data['main']['temp'];
        $humidity = $data['main']['humidity'];
        $windspeed = isset($data['wind']['speed']) ? $data['wind']['speed'] : 0;
        $pressure = $data['main']['pressure'];
        $weather_icon = $data['weather'][0]['icon'];
        $wind_direction = isset($data['wind']['deg']) ? $data['wind']['deg'] : 0;
        $visibility = isset($data['visibility']) ? $data['visibility'] : 0;
        $feels_like = $data['main']['feels_like'];
        $temp_max = $data['main']['temp_max'];
        $temp_min = $data['main']['temp_min'];


        // Insert weather data into the database
        $insertData = "INSERT INTO weather (city, main_weather, weather_description, temperature, humidity, wind, pressure, weather_icon, wind_direction, visibility, feels_like, temp_max, temp_min, forecasted_time)
            VALUES ('$cityName', '$main_weather', '$weather_description', '$temperature', '$humidity', '$windspeed', '$pressure', '$weather_icon', '$wind_direction', '$visibility', '$feels_like', '$temp_max', '$temp_min', '$forecast_time')";


        if (!mysqli_query($conn, $insertData)) {
            die("Not Inserted: " . mysqli_error($conn));
        }
    }
}


// Fetch and display the newly inserted data for the current city
$selectRecentData = "SELECT * FROM weather WHERE city = '$cityName' AND forecasted_time > '$two_hours_ago'";
$result = mysqli_query($conn, $selectRecentData);


$rows = [];
if (!$result) {
    die("Query Error: " . mysqli_error($conn));
} elseif (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
}


// Output the JSON data
header('Content-Type: application/json');
echo json_encode($rows);


// Close the database connection
mysqli_close($conn);
?>
