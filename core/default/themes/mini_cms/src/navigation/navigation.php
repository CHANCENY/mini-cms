<style>
    .admin-nav nav,
    .nav-item {
        display: flex;
    }

    .admin-nav nav {
        background-image: linear-gradient(rgb(48, 48, 48) 13%,
                rgb(30, 30, 30) 40%,
                #0c0d11 86%);
        color: rgba(255, 255, 255, 0.6);
        text-shadow: 0 -2px 0 black;
        cursor: pointer;
        box-shadow: 1px 2px 4px rgb(20, 20, 20), 0 4px 12px rgb(10, 10, 10);
    }

    .admin-nav .nav-item {
        font-size: 0.8999rem;
        line-height: 1rem;
        align-items: center;
        min-width: 120px;
        justify-content: space-between;
        transition: all 80ms ease;

        &.active {
            color: $primary;
            text-shadow: 0 0 3px hsla(260, 100%, 70%, 0.7);
        }

        &:not(.active):hover {
            color: rgba(255, 255, 255, 0.87);
        }

        &:hover>.icon .subicon {
            height: 32px;
            width: 32px;
            border-radius: 32px;
            top: -16px;
            right: -16px;
            border-color: white;
        }

        &:not(:first-of-type) {
            border-left: 1px solid rgb(60, 60, 60);
        }

        &:not(:last-of-type) {
            border-right: 0.1rem solid black;
        }

        a {
            color: inherit;
            text-decoration: none;
            padding: 1ch;
        }

        .icon {
            padding: 1ch;
            position: relative;

            .subicon {
                text-shadow: none;
                transition: all 40ms ease;
                position: absolute;
                top: -3px;
                right: -3px;
                background: red;
                color: white;
                box-shadow: 0 0 4px rgba(41, 41, 41, 0.405);
                width: 18px;
                height: 18px;
                border-radius: 14px;
                font-size: 0.7em;
                font-weight: 700;
                display: inline-grid;
                place-items: center;
                border: 2px solid mix(white, red);
            }
        }

        .icon>svg {
            max-width: 16px;
        }
    }

    .admin-nav {
        overflow: hidden;
        background-color: #333;
    }


    .admin-nav.sticky {
        position: fixed;
        top: 0;
        width: 100%;
    }

    .admin-nav.sticky+.content {
        padding-top: 60px;
    }

    .user-profile {
        padding: 15px;
    }

    .user-profile .card {
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, .1), 0 1px 2px 0 rgba(0, 0, 0, .06);
    }

    .user-profile .card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        word-wrap: break-word;
        background-color: #fff;
        background-clip: border-box;
        border: 0 solid rgba(0, 0, 0, .125);
        border-radius: .25rem;
    }

    .user-profile .card-body {
        flex: 1 1 auto;
        min-height: 1px;
        padding: 1rem;
    }

    .user-profile .gutters-sm {
        margin-right: -8px;
        margin-left: -8px;
    }

    .user-profile .gutters-sm>.col,
    .gutters-sm>[class*=col-] {
        padding-right: 8px;
        padding-left: 8px;
    }

    .user-profile .mb-3,
    .my-3 {
        margin-bottom: 1rem !important;
    }

    .user-profile .bg-gray-300 {
        background-color: #e2e8f0;
    }

    .user-profile .h-100 {
        height: 100% !important;
    }

    .user-profile .shadow-none {
        box-shadow: none !important;
    }

    @import url('https://fonts.googleapis.com/css2?family=Barlow&display=swap');

    body {
        font-family: 'Barlow', sans-serif;
    }

    a:hover {
        text-decoration: none;
    }

    .border-left {
        border-left: 2px solid var(--primary) !important;
    }


    .sidebar {
        top: 0;
        left: 0;
        z-index: 100;
        overflow-y: auto;
    }

    .overlay {
        background-color: rgb(0 0 0 / 45%);
        z-index: 99;
    }

    /* sidebar for small screens */
    @media screen and (max-width: 767px) {

        .sidebar {
            max-width: 18rem;
            transform: translateX(-100%);
            transition: transform 0.4s ease-out;
        }

        .sidebar.active {
            transform: translateX(0);
        }

    }

    /* Style the entire details element */
    details {
        border: 1px solid #ccc;
        padding: 10px;
        background-color: #f9f9f9;
        border-radius: 4px;
    }

    /* Style the summary element (the clickable part) */
    summary {
        font-weight: bold;
        cursor: pointer;
        color: black;
    }

    /* Change the summary hover effect */
    summary:hover {
        color: #007BFF;
    }

    /* Style the content inside the details when opened */
    details[open] {
        background-color: #f0f0f0;
    }

    /* Style the content inside the details element */
    details p {
        margin: 10px 0 0;
        font-size: 14px;
        color: #333;
    }
</style>
<?php if (!empty($content)): ?>
    <?php $menus = $content; ?>
    <?php if ($menus instanceof \Mini\Cms\Theme\Menus): ?>
        <div id="admin-nav" class="admin-nav">
            <nav class="menu" id="nav">
                <?php foreach ($menus->getMenus() as $menu_key => $menu): ?>
                    <?php if ($menu instanceof \Mini\Cms\Theme\Menu): ?>
                        <?php
                        $attributes = $menu->getAttributes();
                        $title = $attributes['title'] ?? null;
                        $id = $attributes['id'] ?? null;
                        $classes = $attributes['class'] ?? null;
                        $target = $attributes['target'] ?? null;
                        $options = $menu->getOptions();
                        $active_class = !empty($options['active']) ? 'active' : null;
                        ?>
                        <span class="nav-item <?= $active_class; ?>">
                            <span class="icon">
                                <i data-feather="<?= $title; ?>"></i>
                            </span>
                            <a href="<?= $menu->getLink(); ?>"
                                id="<?= $id; ?>"
                                target="<?= $target; ?>"
                                title="<?= $title; ?>"
                                class="<?= $classes; ?>">
                                <?= $menu->getLabel(); ?>
                            </a>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
    window.onscroll = function() {
        myFunction()
    };

    const navbar = document.getElementById("admin-nav");
    const sticky = navbar.offsetTop;

    function myFunction() {
        if (window.pageYOffset > sticky) {
            navbar.classList.add("sticky")
        } else {
            navbar.classList.remove("sticky");
        }
    }
</script>

<div class="container-xxl position-relative bg-white d-flex p-0">
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Content Start -->
    <div class="content">
        <!-- Navbar Start -->
        <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
            <form class="d-none d-md-flex ms-4">
                <input class="form-control border-0" type="search" placeholder="Search">
            </form>
            <div class="navbar-nav align-items-center ms-auto">
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-envelope me-lg-2"></i>
                        <span class="d-none d-lg-inline-flex">Message</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                        <a href="#" class="dropdown-item">
                            <div class="d-flex align-items-center">
                                <img class="rounded-circle" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                                <div class="ms-2">
                                    <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                                    <small>15 minutes ago</small>
                                </div>
                            </div>
                        </a>
                        <hr class="dropdown-divider">
                        <a href="#" class="dropdown-item">
                            <div class="d-flex align-items-center">
                                <img class="rounded-circle" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                                <div class="ms-2">
                                    <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                                    <small>15 minutes ago</small>
                                </div>
                            </div>
                        </a>
                        <hr class="dropdown-divider">
                        <a href="#" class="dropdown-item">
                            <div class="d-flex align-items-center">
                                <img class="rounded-circle" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                                <div class="ms-2">
                                    <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                                    <small>15 minutes ago</small>
                                </div>
                            </div>
                        </a>
                        <hr class="dropdown-divider">
                        <a href="#" class="dropdown-item text-center">See all message</a>
                    </div>
                </div>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-bell me-lg-2"></i>
                        <span class="d-none d-lg-inline-flex">Notificatin</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                        <a href="#" class="dropdown-item">
                            <h6 class="fw-normal mb-0">Profile updated</h6>
                            <small>15 minutes ago</small>
                        </a>
                        <hr class="dropdown-divider">
                        <a href="#" class="dropdown-item">
                            <h6 class="fw-normal mb-0">New user added</h6>
                            <small>15 minutes ago</small>
                        </a>
                        <hr class="dropdown-divider">
                        <a href="#" class="dropdown-item">
                            <h6 class="fw-normal mb-0">Password changed</h6>
                            <small>15 minutes ago</small>
                        </a>
                        <hr class="dropdown-divider">
                        <a href="#" class="dropdown-item text-center">See all notifications</a>
                    </div>
                </div>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <img class="rounded-circle me-lg-2" src="<?= $current_user?->getImage() ?>" alt="" style="width: 40px; height: 40px;">
                        <span class="d-none d-lg-inline-flex"><?= $current_user?->getAccountName() ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                        <a href="/user" class="dropdown-item">My Profile</a>
                        <a href="/user/<?= $current_user?->id() ?>" class="dropdown-item">Settings</a>
                        <a href="/user/logout" class="dropdown-item">Log Out</a>
                    </div>
                </div>
            </div>
        </nav>
        <!-- Navbar End -->

        <div class="container mt-lg-5">
            <div class="status-container-admin w-75" id="status-container-admin">
                <?php

                /**@var $theme \Mini\Cms\Theme\Theme **/
                $theme = get_global('theme_loaded');
                if ($theme->getThemeName() === 'default_admin') {
                    try {
                        $status_in_stack = \Mini\Cms\Mini::messenger()->getMessages();
                        foreach ($status_in_stack as $message) {
                            echo $message;
                        }
                    } catch (Exception $e) {
                    }
                }
                ?>
            </div>
        </div>