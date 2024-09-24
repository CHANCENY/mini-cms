// Helper function to send the XMLHttpRequest and manage history state
function sendRequest(url, method, data = null, replaceTarget = null, pushToHistory = true) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true); // Open the request with the specified method and URL

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // Replace content based on data-replace
                if (replaceTarget) {
                    const targetElement = document.getElementById(replaceTarget);
                    if (targetElement) {
                        targetElement.innerHTML = xhr.responseText;
                    } else {
                        console.warn(`Element with id "${replaceTarget}" not found.`);
                    }
                } else {
                    // Replace entire page if no replace target is specified
                    document.body.innerHTML = xhr.responseText;
                }

                // Add new state to browser history
                if (pushToHistory) {
                    history.pushState({ url: url, replaceTarget: replaceTarget }, '', url);
                }
            } else {
                console.error('Request failed with status:', xhr.status);
            }
        }
    };

    // Send the request
    if (method === 'POST' && data) {
        xhr.send(data); // Send form data in case of POST
    } else {
        xhr.send(); // Send GET request
    }
}

// Handle popstate event (when user clicks back/forward button)
window.addEventListener('popstate', function(event) {
    if (event.state && event.state.url) {
        sendRequest(event.state.url, 'GET', null, event.state.replaceTarget, false);
    }
});

// Add event listeners to <a> tags
document.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default navigation

        const url = link.getAttribute('href');
        const replaceTarget = link.getAttribute('data-replace');
        sendRequest(url, 'GET', null, replaceTarget); // Send a GET request
    });
});

// Add event listeners to <form> elements
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        const method = form.getAttribute('method') || 'GET';
        const action = form.getAttribute('action') || window.location.href;
        const replaceTarget = form.getAttribute('data-replace');
        const data = new FormData(form); // Collect form data for POST

        sendRequest(action, method.toUpperCase(), method.toUpperCase() === 'POST' ? data : null, replaceTarget);
    });
});

// Add event listeners to <button> elements
document.querySelectorAll('button').forEach(button => {
    button.addEventListener('click', function(event) {
        const form = button.closest('form');
        if (!form) {
            event.preventDefault(); // Only prevent default if not in a form

            const url = button.getAttribute('data-url') || window.location.href;
            const method = button.getAttribute('data-method') || 'GET';
            const replaceTarget = button.getAttribute('data-replace');

            sendRequest(url, method.toUpperCase(), null, replaceTarget); // Send request for buttons outside of forms
        }
    });
});
