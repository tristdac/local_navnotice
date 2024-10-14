document.addEventListener('DOMContentLoaded', function () {

    function hexToRgb(hex) {
        let r = 0, g = 0, b = 0;
        hex = hex.replace(/^#/, '');  // Ensure hex is processed correctly
        // 3 digits
        if (hex.length === 3) {
            r = parseInt(hex[0] + hex[0], 16);
            g = parseInt(hex[1] + hex[1], 16);
            b = parseInt(hex[2] + hex[2], 16);
        // 6 digits
        } else if (hex.length === 6) {
            r = parseInt(hex.substring(0, 2), 16);
            g = parseInt(hex.substring(2, 4), 16);
            b = parseInt(hex.substring(4, 6), 16);
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
        return (Math.max(L1, L2) + 0.05) / (Math.min(L1, L2) + 0.05);
    }

    function checkColorContrast() {
        const colorPicker = document.getElementById('id_backgroundcolor'); // Updated to correct ID
        const textColorPicker = document.getElementById('id_textcolor');
        const selectedColor = colorPicker.value;
        const selectedTextColor = textColorPicker.value;

        if (selectedColor && contrastRatio(selectedColor, selectedTextColor) < 4.5) {
            colorPicker.style.borderColor = 'red'; // Optional: highlight invalid input
            colorPicker.setCustomValidity('The selected color does not provide sufficient contrast. Please choose a different color.');
        } else {
            colorPicker.style.borderColor = ''; // Reset border color
            colorPicker.setCustomValidity('');
        }
    }

    const colorPicker = document.getElementById('id_backgroundcolor'); // Corrected ID
    const textColorPicker = document.getElementById('id_textcolor');

    if (colorPicker && textColorPicker) {
        colorPicker.addEventListener('input', checkColorContrast);
        textColorPicker.addEventListener('input', checkColorContrast);
    } else {
        console.log('One or both pickers are null');
    }

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (colorPicker.validationMessage) {
                console.log('Form submission prevented due to validation error');
                event.preventDefault();
            }
        });
    } else {
        console.log('Form is null');
    }
});
