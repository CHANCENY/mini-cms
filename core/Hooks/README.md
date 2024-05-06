### Hooking the request circle.

This is away where are calling specific functions in hooks file
at any given time

INSTRUCTION:
These functions are expected to be in hooks folder.
You can create php file in hooks and add these hooks there
Make sure every hook function start with file name is located in.

This hook gets call when request object is created.
__request_build_up(Request &$request);

This is hook that get call when access is about to processed.
__access_denied_error(Throwable|\Exception $exception);

This is hook that get call when menu items is collected
__menus_collection(Menus &$menus);

This is hook that get called when menu item is about to be processed
__menu_render(Menu &$menu);