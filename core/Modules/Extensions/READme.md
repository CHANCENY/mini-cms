## HOOKS

Hooks in this mini system function as follows:

___

1. All hook functions start with the module name followed by one underscore.
2. The module name should always be in lowercase and without any spaces.
3. All modules return `void`, and all the parameters are passed by reference.

___

## List of Hooks in the Mini System

1. **_wrapper_register_alter**: This hook is used to register stream wrappers which you want to use. By default, mini has:
    - `public://`
    - `private://`
    - `module://`
    - `theme://`

   `_wrapper_register_alter` has no parameters.

2. **_theme_alter**: This hook is used to work with the theme which has just been loaded. This hook has one parameter (Theme class object).

3. **_menus_alter**: This hook is used to work with the created Menus object which will be used to build navigation. This hook has one parameter (Menus class object).

4. **_footer_alter**: This hook is used to override the footer. This hook has one parameter (Footer class object).

5. **_meta_data_initialize_alter**: This hook is triggered as soon as the Metatag object is created. This hook has one parameter (Metatag class object).

6. **_request_params_alter**: This hook is called only if there are GET request parameters. This hook has one parameter, an array `$_GET`.

7. **_loaded_route_alter**: This hook is for overriding the loaded controller. The hook takes one parameter (Mini\Cms\Routing\Route class object).

8. **_response_alter**: This hook is called just before the response is sent. The hook takes one parameter (Response class object).

9. **_not_found_alter**: This hook is called when a 404 error is encountered, i.e., when the URI has no page.
10. **_services_register**: This hook is for register services the hook tak array of services registered.
11. **_node_prepare_insert**: This hook is triggered just before node creation starts. hook expect (Node parameter as reference)
12. **_node_prepare_delete**: This hook is triggered just before node start deletion. hook expect (Node parameter as reference)
13. **_node_prepare_update**: This hook is triggered just before node update starts. hook expect (Node parameter as reference)
14. **_user_prepare_insert**: This hook is triggered just before user creation starts. hook expect (Node parameter as reference)
15. **_user_post_insert**: This is hook triggered just after user is created. hook expect (User parameter as reference)
16. **_user_prepare_update**: This is hook triggered just before user update. hook expect (User parameter as reference)
17. **_user_post_update**: This is hook that get triggered just after user updated. hook expect (User parameter as reference)
18. **_user_prepare_delete**: This is hook that get triggered just before user deletion. hook expect (User parameter as reference)
19. **_user_post_delete**: This is hook that get triggered just after user deletion. hook expect (User parameter as reference)
20. **_node_post_start**: This is hook that get triggered just after node creation. hook expect (Node parameter as reference)
21. **_node_post_update**: This is hook that get triggered just after node update. hook expect (Node parameter as reference)
22. **_node_post_delete**: This is hook that get triggered just after node deletion. hook expect (Node parameter as reference)