<div class="container mt-lg-5 mb-lg-5">
    <div class="bg-light rounded p-5 m-auto">
        <h1>Real-Time MySQL Connection Monitor</h1>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Host</th>
                    <th>DB</th>
                    <th>Command</th>
                    <th>Time</th>
                    <th>State</th>
                    <th>Info</th>
                </tr>
                </thead>
                <tbody id="connections-table">
                <!-- Connection rows will be inserted here -->
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    function fetchConnections() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', '/admin/database-processes', true);
        xhr.setRequestHeader('Content-Type', 'application/json');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    const data = JSON.parse(xhr.responseText);
                    const tableBody = document.getElementById('connections-table');
                    tableBody.innerHTML = '';

                    data.forEach(connection => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                                <td>${connection.Id}</td>
                                <td>${connection.User}</td>
                                <td>${connection.Host}</td>
                                <td>${connection.db}</td>
                                <td>${connection.Command}</td>
                                <td>${connection.Time}</td>
                                <td>${connection.State}</td>
                                <td>${connection.Info}</td>
                            `;
                        tableBody.appendChild(row);
                    });
                } else {
                    console.error('Error fetching connections:', xhr.statusText);
                }
            }
        };

        xhr.send();
    }

    // Fetch connections every 5 seconds
    setInterval(fetchConnections, 5000);

    // Initial fetch
    fetchConnections();
</script>