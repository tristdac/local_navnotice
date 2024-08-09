document.addEventListener('DOMContentLoaded', function() {
    function hexToRgb(hex) {
        let r = 0, g = 0, b = 0;
        // 3 digits
        if (hex.length === 4) {
            r = parseInt(hex[1] + hex[1], 16);
            g = parseInt(hex[2] + hex[2], 16);
            b = parseInt(hex[3] + hex[3], 16);
        // 6 digits
        } else if (hex.length === 7) {
            r = parseInt(hex[1] + hex[2], 16);
            g = parseInt(hex[3] + hex[4], 16);
            b = parseInt(hex[5] + hex[6], 16);
        }
        return [r, g, b];
    }

    function luminance(r, g, b) {
        [r, g, b] = [r, g, b].map(function (v) {
            v = v / 255;
            return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
        });
        return 0.2126 * r + 0.7152 * g + 0.0722 * b;
    }

    function contrastRatio(hex1, hex2) {
        const [r1, g1, b1] = hexToRgb(hex1);
        const [r2, g2, b2] = hexToRgb(hex2);
        const L1 = luminance(r1, g1, b1);
        const L2 = luminance(r2, g2, b2);
        return L1 > L2 ? (L1 + 0.05) / (L2 + 0.05) : (L2 + 0.05) / (L1 + 0.05);
    }

    function checkColorContrast() {
        const colorPicker = document.getElementById('id_navcolor');
        const selectedColor = colorPicker.value;
        const textColor = '#1d2125'; // Black

        console.log('Selected color:', selectedColor); // Debugging
        console.log('Contrast ratio:', contrastRatio(selectedColor, textColor)); // Debugging

        if (selectedColor && contrastRatio(selectedColor, textColor) < 4.5) {
            colorPicker.style.borderColor = 'red'; // Optional: highlight invalid input
            colorPicker.setCustomValidity('The selected color does not provide sufficient contrast with black text. Please choose a different color.');
        } else {
            colorPicker.style.borderColor = ''; // Reset border color
            colorPicker.setCustomValidity('');
        }
    }

    // Bind to the input event for live checking
    const colorPicker = document.getElementById('id_navcolor');
    if (colorPicker) {
        colorPicker.addEventListener('input', checkColorContrast);
    } else {
        console.log('Color picker not found'); // Debugging
    }

    // Bind to the form submission event to validate one last time
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (colorPicker && colorPicker.validationMessage) {
                console.log('Form submission prevented due to validation error'); // Debugging
                event.preventDefault(); // Prevent form submission if invalid
            }
        });
    }
});
