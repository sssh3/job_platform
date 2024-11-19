document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-country');
    const dropdown = document.getElementById('dropdown-country');
    let dropdownSelected = false;

    searchInput.addEventListener('input', function() {
        const query = searchInput.value;

        if (query.length > 0) {
            fetch(`/job_platform/utils/search_location.php?country=${encodeURIComponent(query)}`)
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
    
});
