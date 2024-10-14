document.addEventListener('DOMContentLoaded', function() {
    // Check if the navStyles object is defined
    if (typeof navStyles !== 'undefined') {
        // Select all navigation items with a data-key that starts with 'navnotice-id-'
        const navItems = document.querySelectorAll('.primary-navigation [data-key^="navnotice-id-"]');

        navItems.forEach(function(navItem) {
            // Extract the ID from the data-key attribute
            const key = navItem.getAttribute('data-key');
            const id = key.split('-').pop(); // Get the numeric ID

            // Get the corresponding styles from the navStyles object
            const styles = navStyles[id];

            // If a background color is found, apply it
            if (styles && styles.backgroundColor) {
                navItem.style.backgroundColor = styles.backgroundColor;
            }

            // If a text color is found, apply it
            if (styles && styles.textColor) {
                const link = navItem.querySelector('a.nav-link');
                if (link) {
                    link.style.color = styles.textColor;
                }
            }
        });
    }
});
