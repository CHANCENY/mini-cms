# How Mini CMS Works

Mini CMS offers a flexible and customizable content management system that enables you to easily extend and personalize your website's functionality. First, it allows you to create custom modules using PHP code. These modules enable you to modify or extend the default behavior of the CMS, providing a tailored experience for your needs. Additionally, Mini CMS supports theme creation, giving you the ability to design and customize the look of your site using CSS and JavaScript, ensuring that your website matches your brand’s identity.

Another key feature is the ability to manage user roles. You can create and manage different types of users, each with specific permissions and access levels, enhancing the security and control over who can access or modify content. Finally, Mini CMS lets you define routes, enabling you to create custom URL structures and map them to specific functionality, ensuring that your site’s navigation is intuitive and easy to manage.

---

## How to Create a Custom Module in Mini CMS

To create a custom module in Mini CMS, follow these steps:

1. **Create a Module Directory:**
   First, navigate to the `modules/custom` directory of your Mini CMS installation. Inside this folder, create a new directory for your module. For example, you could name it `my_module`.

2. **Create the `my_module.info.yml` File:**
   Inside your new module directory, create a file named `my_module.info.yml`. This file is essential for defining the basic information about your module. The contents of the file should include:

    ```yaml
    name: your_module_name
    type: module
    version: 1
    ```

3. **Enable the Module:**
   After creating the module, log into your Mini CMS dashboard. Navigate to the Settings section and look for the Modules settings. From there, enable your newly created module and save the changes.

4. **Add Additional Files for Functionality:**
   You can further customize your module by adding additional files such as:
    - `your_module.module`: This file is used to implement hooks that Mini CMS uses. You can define custom functionality by hooking into the CMS events.
    - `your_module.routing.yml`: Use this file to define custom routes for your module. It allows you to map specific URLs to functionality within your module.
    - `your_module.menus.yml`: If you want to add custom menu items to the Dashboard, you can define them in this file.
    - `your_module.services.api.yml`: This file is used to define services for your module. For example, if you want to create a service for handling transactions, you can define it as:

    ```yaml
    example: transaction.handler: Mini\Cms\Modules\Transaction
    ```

   Once the service is defined, you can use it in your PHP code like so:
    ```php
    Services::create('transaction.handler');
    ```
   Note: You need to navigate to `Settings > General Configurations`, then click **Rebuild Services**.

5. **Create a `src` Directory:**
   Within your module, you can create a `src` directory. This is where you’ll place your PHP classes for handling routes, models, plugins, or any other custom code that your module needs. For example, you could create classes for handling specific routes or database queries.

6. **Organize Your Code:**
   Inside the `src` directory, you can organize your code further based on its functionality. For example, if you’re creating a class for route handling, you might create a `Routes` directory and place your route-related classes there.

By following these steps, you can create a fully functional and customizable module for Mini CMS that extends its features and fits your project’s specific needs.

---

## How to Create a Theme in Mini CMS

To create a custom theme for your Mini CMS site, follow these steps:

1. **Create a Theme Folder:**
   First, navigate to the `themes` directory of your Mini CMS installation. Inside this folder, create a new directory for your theme. For example, name it `front_theme`.

2. **Create the `front_theme.info.yml` File:**
   Inside the `front_theme` directory, create a file called `front_theme.info.yml`. This file is where you define the basic information about your theme. The content of the file should look like this:

    ```yaml
    title: 'Title Here'
    name: 'front_theme'
    description: 'Description Here'
    source_directory: 'theme://front_theme/src'
    ```

3. **Create the `src` Directory:**
   Inside the `front_theme` folder, create a `src` directory. This is where you'll store your theme’s assets and configuration files, such as CSS, JavaScript, and library files.

4. **Create the `__theme_libraries.yml` File:**
   Inside the `src` folder, create a file named `__theme_libraries.yml`. This file defines the libraries (CSS, JS) used by your theme. The structure should include keys for `global`, `head`, and `footer` to define which assets should be loaded in those sections.

   Here’s an example of what your `__theme_libraries.yml` file might look like:

    ```yaml
    global:
      head:
        - "/themes/global/das/css/style.css"  # Local CSS file for the theme
      footer:
        - "/themes/global/js/main.js"  # Local JavaScript file

    # External libraries can be added like this:
    head:
      - "https://code.jquery.com/jquery-3.4.1.min.js"  # External JS
    ```

    - `head`: This key defines the CSS and JS files to be loaded in the `<head>` section of your theme.
    - `footer`: This key defines the files to be loaded in the footer section, usually JavaScript files.
    - The `"|ext-lib"` part is used for external libraries, indicating that the file is not stored locally but is loaded from an external source, like a CDN.

5. **Enable Your Theme:**
   Once you’ve created your theme and the necessary files, you need to enable it within the Mini CMS dashboard:

    - Navigate to `Settings > Extension Configurations` in the dashboard.
    - Scroll down until you find the list of available themes.
    - Click **Enable** next to your theme to activate it.

Alternatively, you can also enable the theme directly using the form found in `Settings > Extension Configurations`.

---

# Hooks Explanation

- **_response_headers_alter**: Triggered before sending HTTP response headers, allowing developers to modify or add custom headers dynamically.
- **_inline_head_script_alter**: Allows alteration of inline scripts in the head or footer of a page, enabling dynamic script modifications.
- **_global_definitions_alter**: Provides an opportunity to modify global definitions used across the application.
- **_theme_alter**: Enables modification of the active theme or its properties before rendering begins.
- **_menus_alter**: Allows adjustment of the site's menu definitions, such as adding or modifying menu items.
- **_footer_alter**: Used to alter footer content before it is rendered.
- **_request_params_alter**: Triggered to modify request parameters like `$_GET` data, enabling custom filtering or processing.
- **_loaded_route_alter**: Provides access to alter the current loaded route’s properties or behavior.
- **_route_controller_handler_alter**: Allows modification of the controller handling a specific route.
- **_route_access_alter**: Facilitates altering access rules for a route before it's processed.
- **_post_request_alter**: Invoked after a request is processed, enabling post-processing tasks.
- **_meta_data_initialize_alter**: Allows modification of metadata tags before they are passed to the view or used in the response.
- **_response_alter**: Triggered to modify the entire response object, allowing final adjustments before it’s sent to the user.
- **_not_found_alter**: Enables changes to the handling of paths that result in a "not found" response.
- **_wrapper_register_alter**: Used for altering wrapper registrations in the application.
- **_user_prepare_insert**: Called before inserting user data, allowing validation or transformation.
- **_user_post_insert**: Triggered after user data is inserted, useful for post-insert actions.
- **_user_prepare_update**: Allows modification of user data before it's updated in the database.
- **_user_post_update**: Executed after a user’s data is updated, useful for logging or cache updates.
- **_user_prepare_delete**: Provides a way to validate or alter user data before deletion.
- **_user_post_delete**: Called after user data is deleted, enabling cleanup tasks.
- **_user_roles_list_alter**: Allows modification of the list of user roles.
- **_authentication_method_alter**: Enables altering the authentication methods available in the system.
- **_user_login_validated**: Triggered after user login validation, allowing custom actions during login.
- **_user_logout**: Called during user logout, providing a way to clean up session or perform custom tasks.
- **_image_styles_alter**: Allows adjustment of image styles before they are applied.
- **_file_system_save_as_alter**: Facilitates altering file save operations, such as changing file paths or metadata.
- **_tokens_info**: Provides a way to modify token definitions before they are used in token replacements.
- **_token_replacement**: Enables custom logic for replacing tokens with dynamic values.
- **_attachments_assets**: Allows modification of assets attached to a theme or page, such as CSS or JS files.
- **_view_data_alter**: Triggered before view data is rendered, allowing developers to modify the data.
- **_themes_list_alter**: Facilitates modification of the list of available themes.
- **_navigation_template_alter**: Allows customization of the navigation template file.
- **_footer_template_alter**: Enables altering the render array of the footer template.
- **_meta_pre_render_alter**: Provides access to metadata before it’s rendered.
- **_html_attribute_alter**: Allows developers to modify HTML attributes applied to elements on the page.
Each hook serves as a specific integration point, providing extensive flexibility to developers for customizing and extending the CMS.

