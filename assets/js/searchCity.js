document.addEventListener('DOMContentLoaded', function() {
    const inputCountry = document.getElementById('search-country');
    const inputProvince = document.getElementById('search-province');
    const searchInput = document.getElementById('search-city');
    const dropdown = document.getElementById('dropdown-city');
    let dropdownSelected = false;

    searchInput.addEventListener('input', function() {
        const query = searchInput.value;
        const countryValue = inputCountry.value;
        const provinceValue = inputProvince.value;

        if (query.length > 0 && countryValue !== '' && provinceValue !== '') {
            fetch(`/job_platform/utils/search_location.php?country=${encodeURIComponent(countryValue)}&province=${encodeURIComponent(provinceValue)}&city=${encodeURIComponent(query)}`)
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
                            dropdownSelected = true;
                        };
                        dropdown.appendChild(div);
                    });
                });
        } else {
            dropdown.style.display = 'none';
        }
    });

    document.addEventListener('click', function(event) {
        // Check if the click is outside of the dropdown and the input field
        if (!dropdown.contains(event.target) && event.target !== searchInput) {
            dropdown.style.display = 'none';
            if (!dropdownSelected && searchInput.value !== '') {
                searchInput.value = ''; // Clear input only if no item was selected
            }
        }
    });

    searchInput.addEventListener('focus', function() {
        dropdownSelected = false;
    });
    
});