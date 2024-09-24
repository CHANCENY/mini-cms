## Click Tracker with XMLHttpRequest and Dynamic Content Replacement

This JavaScript code tracks clicks on links, buttons, and form submissions, prevents their default behavior, sends silent `XMLHttpRequest` requests to the server, and updates parts of the page based on the server's response.

### Features
- **Track Clicks on Links, Buttons, and Forms**: The script listens for clicks on `<a>`, `<button>`, and `<form>` elements.
- **Prevent Default Behavior**: Actions like page navigation or form submission are blocked, allowing custom behavior.
- **Support for `GET` and `POST` Methods**: It automatically determines the HTTP method (`GET` or `POST`) based on the clicked element.
- **Dynamic Content Replacement**: Allows replacing either the entire page content or a specific part of the page (based on the `data-replace` attribute).

---

## How the Code Works

### 1. Event Listener Setup
The script adds a click event listener on the `document` that captures all clicks on:
- **Links** (`<a>` tags)
- **Buttons** (`<button>` tags)
- **Forms** (`<form>` submissions)

### 2. Determine the Request Method
The script determines the HTTP method (`GET` or `POST`) based on the element type:
- **Links**: Assumed to be `GET` requests. The `href` attribute defines the URL.
- **Buttons**:
  - If inside a form, the form's `action` URL and `method` are used.
  - If not inside a form, the script checks for `data-url` and `data-method` attributes (defaults to `GET` and the current URL if not provided).
- **Forms**: Uses the form's `method` (`GET` or `POST`). If missing, defaults to `GET`.

### 3. Send Silent XMLHttpRequest
The code silently sends an `XMLHttpRequest`:
- It opens the request using the determined `url` and `method`.
- If it's a `POST` request, form data is serialized and sent.
- If it's a `GET` request, it sends without data.

### 4. Dynamic Content Replacement
When the server response is received:
- If the clicked element (link, button, or form) contains a `data-replace` attribute, the response content will replace the `innerHTML` of the tag with the specified `id` (based on the value of `data-replace`).
- If `data-replace` is not present, the entire page content (`document.body.innerHTML`) is replaced with the server response.

---

## Usage

### Example for a Link
```html
<a href="/load-new-content" data-replace="content-area">Load Content</a>
