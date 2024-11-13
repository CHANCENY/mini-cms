<div class="mt-lg-5 mb-lg-5">
    <div class="bg-light p-5">
        <div class="mb-5 mt-5">
            <a href="/user/register" class="btn btn-primary">Add User</a>
            <a href="/user/roles" class="btn btn-outline-primary">roles</a>
        </div>
        <table class="table datatable">
            <thead>
            <tr>
                <th>FirstName</th>
                <th>LastName</th>
                <th>UserName</th>
                <th>Status</th>
                <th>Created On</th>
                <th>Updated On</th>
                <th>Last Login</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if(!empty($content['users'])): foreach($content['users'] as $user): ?>
                <tr>
                    <td><?= $user['firstname'] ?? null ?></td>
                    <td><?= $user['lastname'] ?? null ?></td>
                    <td><?= $user['name'] ?? null ?></td>
                    <td><?= $user['active'] === '1' ? 'Active' : 'Blocked' ?></td>
                    <td><?= date('d F, Y',$user['created']) ?></td>
                    <td><?= date('d F, Y',$user['updated']) ?></td>
                    <td><?= $user['login'] ? date('d F, Y',$user['login']) : 'None' ?></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                action
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" href="/user/<?= $user['uid'] ?>/edit">Edit</a>
                                <a class="dropdown-item" href="/user/<?= $user['uid'] ?>/delete">Delete</a>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
