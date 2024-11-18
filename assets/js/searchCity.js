document.addEventListener('DOMContentLoaded', function() {
    const inputCountry = document.getElementById('search-country');
    const searchInput = document.getElementById('search-city');
    const dropdown = document.getElementById('dropdown-city');

    searchInput.addEventListener('input', function() {
        const query = searchInput.value;
        const countryValue = inputCountry.value;

        if (query.length > 0) {
            fetch(`/job_platform/utils/search_location.php?country=${encodeURIComponent(countryValue)}&city=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    dropdown.innerHTML = '';
                    dropdown.style.display = 'block';

                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'dropdown-item';
                        div.textContent = item.name;
                        div.onclick = () => {
                            searchInput.value = item.name;
                            dropdown.style.display = 'none';
                        };
                        dropdown.appendChild(div);
                    });
                });
        } else {
            dropdown.style.display = 'none';
        }
    });

    document.addEventListener('click', function(event) {
        if (!dropdown.contains(event.target) && event.target !== searchInput) {
            dropdown.style.display = 'none';
        }
    });
});
