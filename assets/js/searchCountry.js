document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-country');
    const dropdown = document.getElementById('dropdown-country');

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
