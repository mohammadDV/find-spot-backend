<div class="interactive-map-container">
    <div id="map-{{ $lat }}-{{ $long }}" style="height: 400px; width: 100%; border-radius: 8px; border: 2px solid #e5e7eb;"></div>

    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Coordinates</label>
                <div class="text-sm text-gray-600">
                    <strong>Latitude:</strong> {{ $lat }}<br>
                    <strong>Longitude:</strong> {{ $long }}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Actions</label>
                <button type="button"
                        onclick="copyCoordinates('{{ $lat }}', '{{ $long }}')"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                    Copy Coordinates
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize map with a small delay to ensure DOM is ready
function initializeMap() {
    // Simple coordinate display and interaction
    const mapContainer = document.getElementById('map-{{ $lat }}-{{ $long }}');

    // Check if container exists
    if (!mapContainer) {
        console.error('Map container not found');
        return;
    }

    // Check if already initialized
    if (mapContainer.dataset.initialized === 'true') {
        return;
    }

    // Mark as initialized
    mapContainer.dataset.initialized = 'true';

    // Validate coordinates
    const latStr = '{{ $lat }}';
    const longStr = '{{ $long }}';

    // Check if coordinates are empty strings or null
    if (!latStr || !longStr || latStr.trim() === '' || longStr.trim() === '') {
        mapContainer.innerHTML = `
            <div style="background: #f3f4f6; height: 100%; display: flex; align-items: center; justify-content: center; color: #6b7280; border: 2px dashed #d1d5db;">
                <div style="text-align: center;">
                    <div style="font-size: 18px; margin-bottom: 10px;">📍 No Coordinates</div>
                    <div style="font-size: 14px;">Please enter latitude and longitude values above</div>
                </div>
            </div>
        `;
        return;
    }

    const lat = parseFloat(latStr);
    const long = parseFloat(longStr);

    if (isNaN(lat) || isNaN(long)) {
        mapContainer.innerHTML = `
            <div style="background: #f3f4f6; height: 100%; display: flex; align-items: center; justify-content: center; color: #6b7280; border: 2px dashed #d1d5db;">
                <div style="text-align: center;">
                    <div style="font-size: 18px; margin-bottom: 10px;">⚠️ Invalid Coordinates</div>
                    <div style="font-size: 14px;">Please enter valid latitude and longitude values</div>
                </div>
            </div>
        `;
        return;
    }

    // Check coordinate ranges
    if (lat < -90 || lat > 90 || long < -180 || long > 180) {
        mapContainer.innerHTML = `
            <div style="background: #f3f4f6; height: 100%; display: flex; align-items: center; justify-content: center; color: #6b7280; border: 2px dashed #d1d5db;">
                <div style="text-align: center;">
                    <div style="font-size: 18px; margin-bottom: 10px;">⚠️ Out of Range</div>
                    <div style="font-size: 14px;">Latitude: -90 to 90, Longitude: -180 to 180</div>
                </div>
            </div>
        `;
        return;
    }

    // Create a simple map-like display
    mapContainer.innerHTML = `
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100%; display: flex; align-items: center; justify-content: center; color: white; position: relative;">
            <div style="text-align: center;">
                <div style="font-size: 24px; font-weight: bold; margin-bottom: 10px;">📍 Business Location</div>
                <div style="font-size: 16px; margin-bottom: 5px;">Latitude: {{ $lat }}</div>
                <div style="font-size: 16px; margin-bottom: 5px;">Longitude: {{ $long }}</div>
                <div style="font-size: 14px; opacity: 0.8; margin-top: 15px;">Click to update coordinates</div>
            </div>
            <div style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.2); padding: 8px; border-radius: 4px; font-size: 12px;">
                Zoom: {{ $zoom ?? 15 }}
            </div>
        </div>
    `;

        // Enable clicking to update coordinates
    mapContainer.addEventListener('click', function(e) {
        try {
            // Simulate coordinate update (in real implementation, you'd use a proper map API)
            const newLat = (parseFloat('{{ $lat }}') + (Math.random() - 0.5) * 0.01).toFixed(6);
            const newLng = (parseFloat('{{ $long }}') + (Math.random() - 0.5) * 0.01).toFixed(6);

            // Update the form fields
            const latInput = document.querySelector('input[name="lat"]');
            const longInput = document.querySelector('input[name="long"]');

            if (latInput && longInput) {
                latInput.value = newLat;
                latInput.dispatchEvent(new Event('input', { bubbles: true }));
                longInput.value = newLng;
                longInput.dispatchEvent(new Event('input', { bubbles: true }));

                // Trigger Filament's live update
                if (typeof window.Alpine !== 'undefined') {
                    latInput.dispatchEvent(new Event('change', { bubbles: true }));
                    longInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

                    // Update the display
        updateMapDisplay(newLat, newLng);
    } catch (error) {
        console.error('Error updating coordinates:', error);
    }
});

    // Add cleanup function
    mapContainer.addEventListener('filament:unmount', function() {
        mapContainer.dataset.initialized = 'false';
    });
}

// Try to initialize immediately, then with delay
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initializeMap, 100);
    });
} else {
    setTimeout(initializeMap, 100);
}

function updateMapDisplay(lat, lng) {
        mapContainer.innerHTML = `
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100%; display: flex; align-items: center; justify-content: center; color: white; position: relative;">
                <div style="text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; margin-bottom: 10px;">📍 Business Location</div>
                    <div style="font-size: 16px; margin-bottom: 5px;">Latitude: ${lat}</div>
                    <div style="font-size: 16px; margin-bottom: 5px;">Longitude: ${lng}</div>
                    <div style="font-size: 14px; opacity: 0.8; margin-top: 15px;">Click to update coordinates</div>
                </div>
                <div style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.2); padding: 8px; border-radius: 4px; font-size: 12px;">
                    Zoom: {{ $zoom ?? 15 }}
                </div>
            </div>
        `;
    }
});

// Function to copy coordinates to clipboard
function copyCoordinates(lat, long) {
    const text = `${lat}, ${long}`;

    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            showCopySuccess();
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
            fallbackCopyTextToClipboard(text);
        });
    } else {
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        document.execCommand('copy');
        showCopySuccess();
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
        alert('Failed to copy coordinates');
    }

    document.body.removeChild(textArea);
}

function showCopySuccess() {
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Copied!';
    button.classList.remove('bg-blue-500', 'hover:bg-blue-700');
    button.classList.add('bg-green-500');

    setTimeout(function() {
        button.textContent = originalText;
        button.classList.remove('bg-green-500');
        button.classList.add('bg-blue-500', 'hover:bg-blue-700');
    }, 2000);
}
</script>

<style>
.interactive-map-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.interactive-map-container button {
    transition: all 0.2s ease-in-out;
}

.interactive-map-container button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
</style>
