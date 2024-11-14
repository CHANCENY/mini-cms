<div class="container-fluid">
    <div class="row">
        <!-- note: in the layout margin auto is the key as sidebar is fixed -->
        <div class="col-md-9 col-lg-10 ml-md-auto px-0">
            <!-- main content -->
            <main class="container-fluid">
                <section class="row">
                    <div class="col-md-6 col-lg-4">
                        <!-- card -->
                        <article class="p-4 rounded shadow-sm border-left
               mb-4">
                            <a href="/system/pages" class="d-flex align-items-center">
                                <span class="bi bi-box h5"></span>
                                <h5 class="ml-2">Pages</h5>
                            </a>
                        </article>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <article class="p-4 rounded shadow-sm border-left mb-4">
                            <a href="/users" class="d-flex align-items-center">
                                <span class="bi bi-person h5"></span>
                                <h5 class="ml-2">People</h5>
                            </a>
                        </article>
                    </div>
                </section>

                <section class="row">
                    <div class="col-md-6 col-lg-4">
                        <!-- card -->
                        <article class="p-4 rounded shadow-sm border-left
               mb-4">
                            <a href="/reporting/errors" class="d-flex align-items-center">
                                <span class="bi bi-box h5"></span>
                                <h5 class="ml-2">Errors</h5>
                            </a>
                        </article>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <article class="p-4 rounded shadow-sm border-left mb-4">
                            <a href="/development" class="d-flex align-items-center">
                                <span class="bi bi-person h5"></span>
                                <h5 class="ml-2">Development</h5>
                            </a>
                        </article>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <article class="p-4 rounded shadow-sm border-left mb-4">
                            <a href="/admin/content-types" class="d-flex align-items-center">
                                <span class="bi bi-person-check h5"></span>
                                <h5 class="ml-2">Content Types</h5>
                            </a>
                        </article>
                    </div>
                </section>

                <section class="row">
                    <div class="col-md-6 col-lg-4">
                        <!-- card -->
                        <article class="p-4 rounded shadow-sm border-left
               mb-4">
                            <a href="/user/register" class="d-flex align-items-center">
                                <span class="bi bi-box h5"></span>
                                <h5 class="ml-2">Register</h5>
                            </a>
                        </article>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <article class="p-4 rounded shadow-sm border-left mb-4">
                            <a href="/user/logout" class="d-flex align-items-center">
                                <span class="bi bi-person h5"></span>
                                <h5 class="ml-2">Logout</h5>
                            </a>
                        </article>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <article class="p-4 rounded shadow-sm border-left mb-4">
                            <a href="/user/roles" class="d-flex align-items-center">
                                <span class="bi bi-person-check h5"></span>
                                <h5 class="ml-2">Roles</h5>
                            </a>
                        </article>
                    </div>
                </section>

                <div class="jumbotron jumbotron-fluid rounded bg-white border-0 shadow-sm border-left px-4">
                    <div class="container">
                        <h3 class="mb-2 text-primary">Profile</h3>
                        <?php echo \Mini\Cms\Theme\Theme::build('user_profile.php',['user' => \Mini\Cms\Entities\User::load($current_user->id()), 'actions_button'=>true]) ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>