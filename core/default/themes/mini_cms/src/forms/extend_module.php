
<div class="container mt-lg-5">
    <div class="bg-light rounded bordered p-3">
        <div class="d-block mb-4 mt-4">
            <a class="text-decoration-none btn btn-info" href="/extension/install">New Module</a>
        </div>
        <form method="post" class="forms">
            <table class="table table-stripped">
                <thead>
                <tr>
                    <th></th>
                    <th>Module Name</th>
                    <th>Version</th>
                </tr>
                </thead>
                <tbody>
                <?php if(!empty($content['modules'])): ?>
                 <?php foreach ($content['modules'] as $module): ?>
                    <?php if($module instanceof \Mini\Cms\Modules\Extensions\ModuleHandler\ModuleHandler): ?>
                        <tr>
                            <td>
                                <label for="">Enabled</label>
                                <input type="radio" <?= $module->getStatus() ? 'checked' : null; ?> name="action_<?= $module->id(); ?>" value="on" placeholder="Enabled">

                                <label class="ms-4">Disabled</label>
                                <input type="radio" <?= !$module->getStatus() ? 'checked' : null; ?> name="action_<?= $module->id(); ?>" value="off" placeholder="Disabled">
                            </td>
                            <td><?= $module->getName(); ?></td>
                            <td><?= $module->getVersion(); ?></td>
                        </tr>
                    <?php endif; ?>
                 <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
            <?php if(!empty($content['modules'])): ?>
             <input type="submit" name="save_extension" value="Save" class="btn btn-primary">
            <?php else: ?>
             <p>No module found!</p>
            <?php endif; ?>
        </form>
    </div>
</div>
