// --- GOOGLE MAPS AUTOCOMPLETE ---
function initGoogleAutocomplete() {
    const cityInput = document.getElementById('city-input');
    const locationInput = document.getElementById('location-input');
    const editCityInput = document.getElementById('edit-city-input');
    const editLocationInput = document.getElementById('edit-location-input');

    function setupAutocomplete(inputElement, typesArray) {
        if (!inputElement) return;
        const autocomplete = new google.maps.places.Autocomplete(inputElement, {
            types: typesArray,
            componentRestrictions: { country: "ro" }
        });
        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (place && place.name) inputElement.value = place.name;
        });
    }

    setupAutocomplete(cityInput, ['(cities)']);
    setupAutocomplete(locationInput, ['establishment', 'geocode']);
    setupAutocomplete(editCityInput, ['(cities)']);
    setupAutocomplete(editLocationInput, ['establishment', 'geocode']);
}