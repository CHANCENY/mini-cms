<div class="container mt-lg-5">
    <div class="p-5 bg-light rounded">
        <h2>How Mini CMS Works</h2>
        <p>
            Mini CMS offers a flexible and customizable content management system that enables you to easily extend and personalize your website's functionality. First, it allows you to create custom modules using PHP code. These modules enable you to modify or extend the default behavior of the CMS, providing a tailored experience for your needs. Additionally, Mini CMS supports theme creation, giving you the ability to design and customize the look of your site using CSS and JavaScript, ensuring that your website matches your brand’s identity.
        </p>
        <p>
            Another key feature is the ability to manage user roles. You can create and manage different types of users, each with specific permissions and access levels, enhancing the security and control over who can access or modify content. Finally, Mini CMS lets you define routes, enabling you to create custom URL structures and map them to specific functionality, ensuring that your site’s navigation is intuitive and easy to manage.
        </p>
        <hr>
        <h2>How to Create a Custom Module in Mini CMS</h2>
        <p>To create a custom module in Mini CMS, follow these steps:</p>
        <ol>
            <li>
                <strong>Create a Module Directory:</strong>
                <p>
                    First, navigate to the modules/custom directory of your Mini CMS installation. Inside this folder, create a new directory for your module. For example, you could name it my_module.
                </p>
            </li>
            <li>
                <strong>Create the my_module.info.yml File:</strong>
                <p>
                    Inside your new module directory, create a file named my_module.info.yml. This file is essential for defining the basic information about your module. The contents of the file should include:
                    <code>
                        name: your_module_name
                        type: module
                        version: 1
                    </code>
                </p>
            </li>
            <li>
                <strong>Enable the Module:</strong>
                <p>After creating the module, log into your Mini CMS dashboard. Navigate to the Settings section and look for the Modules settings. From there, enable your newly created module and save the changes.</p>
            </li>
            <li>
                <strong>Add Additional Files for Functionality:</strong>
                <p>You can further customize your module by adding additional files such as:</p>
                <ul>
                    <li>
                        <pre>your_module.module:</pre> This file is used to implement hooks that Mini CMS uses. You can define custom functionality by hooking into the CMS events.
                    </li>
                    <li>
                        <pre>your_module.routing.yml:</pre> Use this file to define custom routes for your module. It allows you to map specific URLs to functionality within your module.
                    </li>
                    <li>
                        <pre>your_module.menus.yml:</pre> If you want to add custom menu items to the Dashboard, you can define them in this file.
                    </li>
                    <li>
                        <pre>your_module.services.api.yml:</pre>
                        This file is used to define services for your module. For example, if you want to create a service for handling transactions, you can define it as:
                        <br>example: transaction.handler: Mini\Cms\Modules\Transaction
                        Once the service is defined, you can use it in your PHP code like so:
                        <br> Services::create('transaction.handler');
                        <br>Note you need to navigate to settings > General Configurations then click Rebuild Services
                    </li>

                </ul>
            </li>
            <li>
                <strong>Create a src Directory:</strong>
                <p>Within your module, you can create a src directory. This is where you’ll place your PHP classes for handling routes, models, plugins, or any other custom code that your module needs. For example, you could create classes for handling specific routes or database queries.</p>
            </li>
            <li>
                <strong>Organize Your Code:</strong>
                <p>Inside the src directory, you can organize your code further based on its functionality. For example, if you’re creating a class for route handling, you might create a Routes directory and place your route-related classes there.</p>
            </li>
        </ol>
        <p>By following these steps, you can create a fully functional and customizable module for Mini CMS that extends its features and fits your project’s specific needs.</p>
        <hr>
        <h2>How to Create a Theme in Mini CMS</h2>
        <p>To create a custom theme for your Mini CMS site, follow these steps:</p>
        <p><strong>Create a Theme Folder:</strong>
            First, navigate to the themes directory of your Mini CMS installation. Inside this folder, create a new directory for your theme. For example, name it front_theme.</p>

        <p><strong>Create the front_theme.info.yml File:</strong>
            Inside the front_theme directory, create a file called front_theme.info.yml. This file is where you define the basic information about your theme. The content of the file should look like this:
        </p>
        <p>
        <pre>
                title: 'Title Here'
                name: 'front_theme'
                description: 'Description Here'
                source_directory: 'theme://front_theme/src'
            </pre>
        </p>
        <p>
        <ul>
            <li>
                <pre>title:</pre> The name of your theme, which can be displayed in the dashboard.
            </li>
            <li>
                <pre>name:</pre> A unique identifier for your theme.
            </li>
            <li>
                <pre>description:</pre> A short description of your theme.
            </li>
            <li>
                <pre>source_directory:</pre> Specifies where the theme’s source files are located, in this case, the src folder inside the theme folder.
            </li>
        </ul>
        </p>
        <p><strong>Create the src Directory:</strong>
            Inside the front_theme folder, create a src directory. This is where you'll store your theme’s assets and configuration files, such as CSS, JavaScript, and library files.
        </p>

        <p><strong>Create the __theme_libraries.yml File:</strong>
            Inside the src folder, create a file named __theme_libraries.yml. This file defines the libraries (CSS, JS) used by your theme. The structure should include keys for global, head, and footer to define which assets should be loaded in those sections.
        </p>
        <p>Here’s an example of what your __theme_libraries.yml file might look like:</p>
        <p>
        <pre>

  global:
  head:
    - "/themes/global/das/css/style.css"  # Local CSS file for the theme
  footer:
    - "/themes/global/js/main.js"  # Local JavaScript file

  # External libraries can be added like this:
  head:
    - "https://code.jquery.com/jquery-3.4.1.min.js<script src=></script>|ext-lib"  # External JS 
    Note need to be in script tag src and link tag href for css

            </pre>
        <ul>
            <li>head: This key defines the CSS and JS files to be loaded in the section of your theme.</li>
            <li>footer: This key defines the files to be loaded in the section, usually JavaScript files.</li>
        </ul>
        The
        <pre>"|ext-lib"</pre> part is used for external libraries, indicating that the file is not stored locally but is loaded from an external source, like a CDN.
        </p>

        <p><strong>Enable Your Theme:</strong>
            Once you’ve created your theme and the necessary files, you need to enable it within the Mini CMS dashboard:
        </p>
        <ul>
            <li>Navigate to Settings > Extension Configurations in the dashboard.</li>
            <li>Scroll down until you find the list of available themes.</li>
            <li>Click Enable next to your theme to activate it.</li>
        </ul>
        <p>Alternatively, you can also enable the theme directly using the form found in Settings > Extension Configurations.</p>
    </div>
</div>