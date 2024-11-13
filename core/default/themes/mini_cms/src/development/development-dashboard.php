<?php
$database = $content['database'];
?>
<div class="container mt-lg-5">
    <div class="rounded bg-light p-5">
        <div class="row">
            <div class="d-block">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="configuration-tab" data-bs-toggle="tab" data-bs-target="#configuration" type="button" role="tab" aria-controls="configuration" aria-selected="true">Configurations</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="database-tab" data-bs-toggle="tab" data-bs-target="#database" type="button" role="tab" aria-controls="database" aria-selected="false">Database</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Contact</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="configuration" role="tabpanel" aria-labelledby="configuration-tab">
                        <div class="d-block mt-5">
                            <a href="/caching/clear" class="btn btn-outline-primary">Clear Caching Files</a>
                            <a href="/caching/rebuild/etagsregister" class="btn btn-outline-primary">Rebuild E-Tags</a>
                            <a href="/caching/rebuild/servicesregister" class="btn btn-outline-primary">Rebuild Services</a>
                            <a href="/caching/rebuild/routesregister" class="btn btn-outline-primary">Rebuild Routes</a>
                            <a href="/caching/rebuild/menus" class="btn btn-outline-primary">Rebuild Menus</a>
                        </div>
                        <hr>
                        <div class="d-block mt-5">
                            <h3>Configurations</h3>
                            <div class="d-block mt-2">
                                <div class="p-2 text-bg-light">
                                    <p>Maintenance mode: <?= get_config_value('maintain_mode.is_active') === true ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?></p>
                                </div>
                                <div class="p-2 text-bg-light">
                                    <p>Error Saver Mode: <?= get_config_value('error_saver') === true ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?></p>
                                </div>
                                <div class="p-2 text-bg-light">
                                    <p>Auto File Upload: <?= get_config_value('file_auto_uploader.is_active') === true ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="database" role="tabpanel" aria-labelledby="database-tab">
                        <div class="d-block mt-4">
                            <h3>Database Information</h3>
                            <div class="d-block w-50">
                                <form method="post" class="form">
                                    <div class="form-group">
                                        <label for="dbname">Database Name</label>
                                        <input type="text" name="db_name" id="dbname" class="form-control" value="<?= $database->getDatabaseName() ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="dbpassword">Database Password</label>
                                        <input type="password" name="db_password" id="dbpassword" class="form-control" value="<?= $database->getDatabasePassword() ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="db_user">Database User</label>
                                        <input type="text" name="db_user" id="db_user" class="form-control" value="<?= $database->getDatabaseUser() ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="db_host">Database Host</label>
                                        <input type="text" name="db_host" id="db_host" class="form-control" value="<?= $database->getDatabaseHost() ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="db_type">Database Type</label>
                                        <select name="db_type" id="db_type" class="form-control">
                                            <option value="mysql" <?= $database->getDatabaseType() === 'mysql' ? 'selected' : null; ?>>MySQL</option>
                                            <option value="sqlite" <?= $database->getDatabaseType() === 'sqlite' ? 'selected' : null; ?>>SQLITE</option>
                                        </select>
                                    </div>
                                    <div class="form-group mt-4">
                                        <button class="btn btn-outline-secondary" type="submit">Save Database</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">...</div>
                </div>
            </div>
        </div>
    </div>
</div>