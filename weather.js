async function getAndDisplayWeather(city) {
    let data;

    // Attempt to retrieve data from localStorage
    const storedData = localStorage.getItem(city);
    if (storedData) {
        data = JSON.parse(storedData);
    } else if (navigator.onLine) { // If data is not in localStorage, and online, fetch from server
        const response = await fetch(`connection.php?q=${city}`);
        data = await response.json();
        // Save data to localStorage
        localStorage.setItem(city, JSON.stringify(data));
    } else {
        // Offline and no data in localStorage
        console.error("No data available for this city.");
        return; // Exit function if no data available
    }

    // Update HTML elements with weather data
    document.querySelector("#condition").innerHTML = `<p>${data[0].weather_description}</p>`;
    document.querySelector(".main_weather").innerHTML = `<strong>${data[0].main_weather}</strong>`;
    document.querySelector(".location span").textContent = data[0].city;
    document.querySelector(".temperature .temp").textContent = `${Math.round(data[0].temperature)}°C`;
    document.querySelector(".Pressure-box .content .pressure-index").textContent = `${data[0].pressure}hPa`;
    document.querySelector(".Humidity-box .content .humidity-index").textContent = `${data[0].humidity}%`;
    document.querySelector(".Wind-speed-box .content .windspeed-index").textContent = `${data[0].wind}m/s Direction: ${data[0].wind_direction}°`;
    document.querySelector(".Visibility-box .content .visibility-index").textContent = `${data[0].visibility} meters`;
    document.querySelector(".feeling .feel span").textContent = `${Math.round(data[0].feels_like)}°C`;
    document.querySelector(".max_temperature .maxtemp span").textContent = `${Math.round(data[0].temp_max)}°C`;
    document.querySelector(".min_temperature .mintemp span").textContent = `${Math.round(data[0].temp_min)}°C`;

    const currentDate = new Date().toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
    document.querySelector(".date .todays").textContent = currentDate;

    const currentTime = new Date().toLocaleString("en-US", {
        weekday: "long",
        hour: "numeric",
        minute: "numeric",
        hour12: true,
    });
    document.querySelector(".week #wtime").textContent = currentTime;

    // Updating the weather icon
    const weatherIcon = `http://openweathermap.org/img/wn/${data[0].weather_icon}@2x.png`;
    document.getElementById("image").innerHTML = `<img src="${weatherIcon}" alt="Weather Icon">`;
}

document.querySelector("#buttonClick").addEventListener("click", async (e) => {
    e.preventDefault();
    let input = document.querySelector("#inid").value;
    await getAndDisplayWeather(input); // Wait for getAndDisplayWeather to complete before proceeding
});

// Call getAndDisplayWeather with default city on page load
getAndDisplayWeather("North Lincolnshire");
