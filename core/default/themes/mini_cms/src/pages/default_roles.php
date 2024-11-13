<div class="mt-lg-5 mb-lg-5">
    <div class="bg-light p-5">
        <div class="mb-5 mt-5">
            <a href="/user/role/add" class="btn btn-primary">Add Role</a>
        </div>
        <table class="table datatable">
            <thead>
            <tr>
                <th>Label</th>
                <th>Name</th>
                <th>Permission</th>
            </tr>
            </thead>
            <tbody>
            <?php if(!empty($content['roles'])): foreach($content['roles'] as $role): ?>
                <?php if($role instanceof \Mini\Cms\Modules\Access\Role): ?>
                <tr>
                    <td><?= $role->getLabel() ?></td>
                    <td><?= $role->getName() ?></td>
                    <td><?= implode(', ',$role->getPermissions()) ?></td>
                </tr>
                <?php endif; ?>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
