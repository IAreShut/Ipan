/**
 * Supervisor Profile Scripts
 * - Tag input for Programme Codes
 * - Avatar preview
 * - Word count for About textarea
 * - Location search via Nominatim / OpenStreetMap
 * - Browser Geolocation API
 */

// ======== Tag Input (Generic) ========
function handleTagInput(event, type) {
    if (event.key === 'Enter') {
        event.preventDefault();
        var input = event.target;
        var value = input.value.trim().toUpperCase();
        
        if (!value) return;

        var containerId = type + '_tags_container';
        var inputName = type + '[]';

        // Prevent duplicate
        var existing = document.querySelectorAll('#' + containerId + ' input[name="' + inputName + '"]');
        for (var i = 0; i < existing.length; i++) {
            if (existing[i].value.toUpperCase() === value) {
                input.value = '';
                return;
            }
        }

        addTag(value, type, containerId, inputName, input);
        input.value = '';
    }
}

function addTag(value, type, containerId, inputName, inputElement) {
    var container = document.getElementById(containerId);
    
    var tag = document.createElement('span');
    tag.className = 'tag-item';
    tag.innerHTML = value 
        + '<input type="hidden" name="' + inputName + '" value="' + value + '">'
        + '<button type="button" class="tag-remove" onclick="removeTag(this)">&times;</button>';
    
    container.insertBefore(tag, inputElement);
}

function removeTag(btn) {
    var tag = btn.parentElement;
    tag.style.transform = 'scale(0.8)';
    tag.style.opacity = '0';
    setTimeout(function() { tag.remove(); }, 150);
}

// Click wrapper to focus input
document.addEventListener('DOMContentLoaded', function() {    
    // Init word count
    var textArea = document.getElementById('about_text');
    if (textArea) {
        updateWordCount(textArea);
    }
});

// ======== Avatar Preview ========
function previewAvatar(event) {
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById('avatar_preview');
        output.src = reader.result;
    };
    if(event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}

// ======== Word Count ========
function updateWordCount(textarea) {
    var text = textarea.value.trim();
    var words = text === '' ? 0 : text.split(/\s+/).length;
    var display = document.getElementById('word_count');
    
    display.innerText = words + '/120 words';
    
    if (words > 120) {
        display.style.color = 'red';
    } else {
        display.style.color = '#64748b';
    }
}

// ======== Location Search (Nominatim / OpenStreetMap) ========
var searchTimeout = null;

function searchLocation(query) {
    clearTimeout(searchTimeout);
    var dropdown = document.getElementById('location_dropdown');

    if (query.length < 3) {
        dropdown.style.display = 'block';
        dropdown.innerHTML = '<div class="location-dropdown-empty">Type at least 3 characters...</div>';
        return;
    }

    dropdown.style.display = 'block';
    dropdown.innerHTML = '<div class="location-dropdown-empty"><i class="fas fa-spinner fa-spin me-2"></i>Searching...</div>';

    searchTimeout = setTimeout(function() {
        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query) + '&limit=6&addressdetails=1', {
            headers: { 'Accept-Language': 'en' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            renderLocationResults(data);
        })
        .catch(function() {
            dropdown.innerHTML = '<div class="location-dropdown-empty">Failed to fetch results. Try again.</div>';
        });
    }, 400);
}

function renderLocationResults(results) {
    var dropdown = document.getElementById('location_dropdown');
    if (!results || results.length === 0) {
        dropdown.innerHTML = '<div class="location-dropdown-empty">No locations found.</div>';
        return;
    }
    var html = '';
    results.forEach(function(place) {
        var name = place.display_name;
        var icon = getPlaceIcon(place.type);
        html += '<div class="location-dropdown-item" onclick="selectLocation(\'' + name.replace(/'/g, "\\'") + '\')">'
              + '<i class="fas ' + icon + ' me-2" style="color: #6366f1;"></i>'
              + '<span>' + name + '</span>'
              + '</div>';
    });
    dropdown.innerHTML = html;
}

function getPlaceIcon(type) {
    var map = {
        'city': 'fa-city', 'town': 'fa-city', 'village': 'fa-home',
        'suburb': 'fa-map-marker-alt', 'state': 'fa-flag',
        'country': 'fa-globe-asia', 'residential': 'fa-home',
        'administrative': 'fa-landmark'
    };
    return map[type] || 'fa-map-pin';
}

function selectLocation(name) {
    document.getElementById('location_search').value = name;
    document.getElementById('location_value').value = name;
    document.getElementById('location_dropdown').style.display = 'none';
}

function showDropdown() {
    var dropdown = document.getElementById('location_dropdown');
    dropdown.style.display = 'block';
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    var wrapper = document.getElementById('location_wrapper');
    if (wrapper && !wrapper.contains(e.target) && e.target.id !== 'geolocate_btn') {
        document.getElementById('location_dropdown').style.display = 'none';
    }
});

// ======== Browser Geolocation API ========
function detectMyLocation() {
    var icon = document.getElementById('geo_icon');
    icon.className = 'fas fa-spinner fa-spin';

    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        icon.className = 'fas fa-crosshairs';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(position) {
            var lat = position.coords.latitude;
            var lon = position.coords.longitude;

            fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lon + '&addressdetails=1', {
                headers: { 'Accept-Language': 'en' }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                var address = data.display_name || (lat + ', ' + lon);
                selectLocation(address);
                icon.className = 'fas fa-crosshairs';
            })
            .catch(function() {
                selectLocation(lat + ', ' + lon);
                icon.className = 'fas fa-crosshairs';
            });
        },
        function(err) {
            alert('Unable to retrieve your location. Please allow location access.');
            icon.className = 'fas fa-crosshairs';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}
