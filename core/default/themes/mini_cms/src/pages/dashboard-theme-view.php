<div class="container mt-lg-5">
    <div class="p-5 bg-light">
        <div class="d-block">
            <a class="btn btn-outline-primary" href="/extend/theme/system/new" data-toggle="modal" data-target="#upload-theme">Upload New Theme</a>
            <a class="btn btn-outline-primary" href="/extend/theme/system/create" data-toggle="modal" data-target="#new-theme">Create New Theme</a>
        </div>
        <div class="d-block mt-5">
            <table class="table-responsive table table-striped">
                <thead>
                    <tr>
                        <th>Theme Title</th>
                        <th>Theme Name</th>
                        <th>Theme Version</th>
                        <th>Theme Description</th>
                        <th>Operation</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($content['themes'])): foreach ($content['themes'] as $theme): ?>
               
                <tr>
                    <td><?= $theme['title'] ?? null ?></td>
                    <td><?= $theme['name'] ?? null ?></td>
                    <td><?= $theme['version'] ?? null ?></td>
                    <td><?= $theme['description'] ?? null ?></td>
                    <?php $status = \Mini\Cms\Modules\Themes\ThemeExtension::isThemeActive($theme['name']); ?>
                    <td><a href="/extend/theme/system/<?= $theme['name'] ?>/status/<?= $status ? 0 : 1  ?>/update">
                            <?= $status ? 'Disable' : 'Enable' ?>
                        </a>
                    </td>
                </tr>
                
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="upload-theme" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Upload Theme Zip</h4>
                </div>
                <div class="modal-body">
                    <form method="post" enctype="multipart/form-data" action="/extend/theme/system" class="form">
                        <div class="form-group">
                            <label for="theme_zip">Theme Zip File</label>
                            <input type="file" name="theme_zip" id="theme_zip" class="form-control">
                        </div>
                        <div class="form-group mt-4">
                            <button class="btn btn-outline-primary" type="submit" name="theme_upload">Submit</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="new-theme" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">New Theme</h4>
                </div>
                <div class="modal-body">
                    <form method="post" action="/extend/theme/system" class="form">
                        <div class="form-group">
                            <label for="title">Theme Title</label>
                            <input type="text" required name="title" id="title" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="version">Theme Version</label>
                            <input type="text" required name="version" id="version" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="description">Theme Description</label>
                            <input type="text" aria-multiline="true" required name="description" id="description" class="form-control">
                        </div>
                        <div class="form-group mt-4">
                            <button type="submit" name="new_theme" class="btn btn-outline-primary">Submit</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
</div>