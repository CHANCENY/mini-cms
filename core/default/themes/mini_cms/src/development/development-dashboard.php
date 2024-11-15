<?php
$database = $content['database'];
$site = $content['site'];
?>
<div class="container mt-lg-5">
    <div class="rounded bg-light p-5">
        <div class="row">
            <div class="d-block">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="configuration-tab" data-bs-toggle="tab" data-bs-target="#configuration" type="button" role="tab" aria-controls="configuration" aria-selected="true">General Configurations</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="database-tab" data-bs-toggle="tab" data-bs-target="#database" type="button" role="tab" aria-controls="database" aria-selected="false">Database Configurations</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="site-tab" data-bs-toggle="tab" data-bs-target="#site" type="button" role="tab" aria-controls="site" aria-selected="false">Site Configurations</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="extension-tab" data-bs-toggle="tab" data-bs-target="#extension" type="button" role="tab" aria-controls="extension" aria-selected="false">Extension Configurations</button>
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
                            <a href="/caching/rebuild/themes" class="btn btn-outline-primary">Rebuild Themes</a>
                        </div>
                        <hr>
                        <div class="d-block mt-5">
                            <h3>Configurations</h3>
                            <div class="d-block mt-2">
                                <table class="table table-striped table-responsive">
                                    <thead>
                                      <tr>
                                          <th>Setting Name</th>
                                          <th>Setting Status</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <tr>
                                          <td>Maintenance mode</td>
                                          <td><?= get_config_value('maintain_mode.is_active') === true ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?></td>
                                      </tr>
                                      <tr>
                                          <td>Error Saver Mode</td>
                                          <td><?= get_config_value('error_saver') === true ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?></td>
                                      </tr>
                                      <tr>
                                          <td>Auto File Upload</td>
                                          <td><?= get_config_value('file_auto_uploader.is_active') === true ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?></td>
                                      </tr>
                                    </tbody>
                                </table>
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
                                        <button class="btn btn-outline-secondary" name="database" type="submit">Save Database</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="site" role="tabpanel" aria-labelledby="site-tab">
                        <div class="d-block mt-5">
                            <div class="row">
                                <div class="col">
                                    <h3>Basic Information</h3>
                                    <div class="form">
                                        <form action="" method="post" class="form">
                                            <div class="form-group">
                                                <label for="Name">Site Name</label>
                                                <input type="text" name="Name" id="Name" class="form-control" value="<?= $site->getBrandingAssets('Name') ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="Email">Site Email</label>
                                                <input type="email" name="Email" id="Email" class="form-control" value="<?= $site->getBrandingAssets('Email') ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="Phone">Site Phone</label>
                                                <input type="tel" name="Phone" id="Phone" class="form-control" value="<?= $site->getBrandingAssets('Phone') ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="Logo">Site Logo</label>
                                                <input type="file" name="Logo" id="Logo" class="form-control">
                                                <?php
                                                  $fid = $site->getBrandingAssets('Logo');
                                                  $file = \Mini\Cms\Modules\FileSystem\File::load(reset($fid));
                                                ?>
                                                <?= $file->getRenderHtmlFileField('Logo') ?>
                                            </div>
                                            <div class="form-group mt-5">
                                                <button type="submit" name="site" class="btn btn-outline-secondary">Save Site Configuration</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col">
                                    <h3>Site Email Configuration</h3>
                                    <div class="form">
                                        <form action="" method="post" class="form">
                                            <div class="form-group">
                                                <label for="smtp_server">Smtp Server</label>
                                                <input type="text" name="smtp_server" id="smtp_server" class="form-control" value="<?= $site->getSmtpInformation('smtp_server') ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="smtp_username">Smtp Username</label>
                                                <input type="text" name="smtp_username" id="smtp_username" class="form-control" value="<?= $site->getSmtpInformation('smtp_username') ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="smtp_password">Smtp Password</label>
                                                <input type="password" name="smtp_password" id="smtp_password" class="form-control" value="<?= $site->getSmtpInformation('smtp_password') ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="smtp_port">Smtp Port</label>
                                                <input type="number" name="smtp_port" id="smtp_port" class="form-control" value="<?= $site->getSmtpInformation('smtp_port') ?>">
                                            </div>
                                            <div class="form-group mt-5">
                                                <button type="submit" name="smtp" class="btn btn-outline-secondary">Save Site Configuration</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="extension" role="tabpanel" aria-labelledby="extension-tab">
                        <style>
                            .extension-settings {
                                h3{
                                    margin-left: 27px;
                                }
                                .mt-lg-5{
                                    margin-top: 0 !important;
                                }
                                .mb-lg-5{
                                    margin-bottom: 0 !important;
                                }
                                .p-5 {
                                    padding: 20px !important;
                                    padding-top: 0 !important;
                                }

                            }
                        </style>
                        <div class="d-block mt-4 extension-settings">
                            <h3>Modules</h3>
                            <?= $content['extend'] ?>
                            <h3>User Roles</h3>
                            <?= $content['role_controller'] ?>
                            <h3>Themes</h3>
                            <?= $content['theme_controller'] ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>