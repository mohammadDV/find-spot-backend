<div class="simple-map-container">
    <div class="map-display" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 300px; border-radius: 8px; border: 2px solid #e5e7eb; display: flex; align-items: center; justify-content: center; color: white; position: relative;">
        <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: bold; margin-bottom: 8px;">📍 Business Location</div>
            <div style="font-size: 14px; margin-bottom: 4px;">Latitude: {{ $lat ?? 'N/A' }}</div>
            <div style="font-size: 14px; margin-bottom: 4px;">Longitude: {{ $long ?? 'N/A' }}</div>
            <div style="font-size: 12px; opacity: 0.8; margin-top: 12px;">Click to update coordinates</div>
        </div>
        <div style="position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.2); padding: 6px; border-radius: 4px; font-size: 11px;">
            Zoom: {{ $zoom ?? 15 }}
        </div>
    </div>

    <div class="mt-3 p-3 bg-gray-50 rounded-lg">
        <div class="text-center">
            <button type="button"
                    onclick="copyCoordinates('{{ $lat ?? '' }}', '{{ $long ?? '' }}')"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                Copy Coordinates
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapDisplay = document.querySelector('.map-display');

    if (mapDisplay) {
        mapDisplay.addEventListener('click', function() {
            // Simple coordinate update simulation
            const currentLat = parseFloat('{{ $lat ?? 0 }}');
            const currentLong = parseFloat('{{ $long ?? 0 }}');

            if (currentLat && currentLong) {
                const newLat = (currentLat + (Math.random() - 0.5) * 0.01).toFixed(6);
                const newLong = (currentLong + (Math.random() - 0.5) * 0.01).toFixed(6);

                // Update form fields
                const latInput = document.querySelector('input[name="lat"]');
                const longInput = document.querySelector('input[name="long"]');

                if (latInput && longInput) {
                    latInput.value = newLat;
                    longInput.dispatchEvent(new Event('input', { bubbles: true }));
                    longInput.value = newLong;
                    longInput.dispatchEvent(new Event('input', { bubbles: true }));
                }

                // Update display
                updateMapDisplay(newLat, newLong);
            }
        });
    }
});

function updateMapDisplay(lat, lng) {
    const mapDisplay = document.querySelector('.map-display');
    if (mapDisplay) {
        mapDisplay.innerHTML = `
            <div style="text-align: center;">
                <div style="font-size: 20px; font-weight: bold; margin-bottom: 8px;">📍 Business Location</div>
                <div style="font-size: 14px; margin-bottom: 4px;">Latitude: ${lat}</div>
                <div style="font-size: 14px; margin-bottom: 4px;">Longitude: ${lng}</div>
                <div style="font-size: 12px; opacity: 0.8; margin-top: 12px;">Click to update coordinates</div>
            </div>
            <div style="position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.2); padding: 6px; border-radius: 4px; font-size: 11px;">
                Zoom: {{ $zoom ?? 15 }}
            </div>
        `;
    }
}

function copyCoordinates(lat, long) {
    if (!lat || !long) {
        alert('No coordinates to copy');
        return;
    }

    const text = `${lat}, ${long}`;

    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            showCopySuccess();
        }).catch(function(err) {
            fallbackCopyTextToClipboard(text);
        });
    } else {
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        document.execCommand('copy');
        showCopySuccess();
    } catch (err) {
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
.simple-map-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.simple-map-container button {
    transition: all 0.2s ease-in-out;
}

.simple-map-container button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
</style>
