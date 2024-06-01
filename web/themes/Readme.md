# How to Create a Theme in Mini CMS

Creating a theme in Mini CMS is crucial as it determines the appearance of the site. Mini CMS is designed to support multiple themes that can be switched and used at any time.

## Instructions

1. **Include the Created Theme in `configurations.json` File:**
    - Locate the `configurations.json` file in the `configs` folder.
    - Add the theme object under the key `"theme"` with the following keys and values:
        - **title:** This is the theme title.
        - **description:** This is the theme description.
        - **source_directory:** This is the directory where Mini CMS will start looking for view files.
        - **active:** This indicates whether the theme is the default. Set to `true` or `false`.
        - **name:** This is the name of the theme. **Note:** The name must be unique.

2. **Create the Theme:**
    - Create the theme in the `web/themes` directory.

## Theme Structure

The theme structure is as follows:

1. **Parent Directory inside Themes (e.g., `classy`):**
    - This directory is the theme directory and must contain a `src` directory.
    - The `src` directory must contain a file named `__theme_libraries.json`.
    - Inside the `src` directory, you can have any folder or view files.

   **Note:** The `source_directory` value will be `"themes/classy/src"`.

### `__theme_libraries.json`

This file is located at `themes/classy/src/__theme_libraries.json`.

#### Structure of `__theme_libraries.json`:

The file is divided into the following sections:

- **head:** Contains a list of asset file paths to be included in the `<head></head>` section of the page.
- **footer:** Contains a list of asset file paths to be included at the bottom of the page.

#### Assets Directives:

Asset paths can include directives appended at the end. Examples:

- `<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>|ext-lib`
- `/themes/global/js/quill/quill.min.js`

**Note:** If the asset file is part of your project, you do not need to append a directive.

- **ext-lib:** Indicates that Mini CMS should attach assets as defined in this file.

By following these instructions, you can successfully create and manage themes in Mini CMS, ensuring your site looks exactly how you want it to.
