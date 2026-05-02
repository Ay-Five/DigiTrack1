async function getLocationData() {
    const latitudeEl = document.getElementById("latitude");
    const longitudeEl = document.getElementById("longitude");
    const mapsLinkEl = document.getElementById("maps_link");
    const cityEl = document.getElementById("city");
    const regionEl = document.getElementById("region");
    const countryEl = document.getElementById("country");
    const ipEl = document.getElementById("ip");

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;

                if (latitudeEl) latitudeEl.value = lat;
                if (longitudeEl) longitudeEl.value = lon;
                if (mapsLinkEl) {
                    mapsLinkEl.value = `https://www.google.com/maps?q=${lat},${lon}`;
                }
            },
            function (error) {
                console.log("Geolocation failed:", error.message);
            }
        );
    }

    try {
        const response = await fetch("https://ipapi.co/json/");
        const data = await response.json();

        if (cityEl) cityEl.value = data.city || "";
        if (regionEl) regionEl.value = data.region || "";
        if (countryEl) countryEl.value = data.country_name || "";
        if (ipEl) ipEl.value = data.ip || "";
    } catch (error) {
        console.log("Location API failed", error);
    }
}

window.addEventListener("DOMContentLoaded", getLocationData);