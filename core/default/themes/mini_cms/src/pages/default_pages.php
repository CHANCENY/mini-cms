<div class="mt-lg-5 mb-lg-5">
    <div class="bg-light p-5">
        <table class="table datatable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Page Title</th>
                <th>Accessible</th>
                <th>Methods</th>
                <th>URI</th>
                <th>Roles</th>
            </tr>
            </thead>
            <tbody>
            <?php if(!empty($content['pages'])): foreach($content['pages'] as $page): ?>
                <?php if($page instanceof \Mini\Cms\Routing\Route): ?>
                <tr>
                    <td><?= $page->getRouteId()  ?></td>
                    <td><?= $page->getRouteTitle()  ?></td>
                    <td><?= $page->isAccessible() ? 'Yes' : 'No'  ?></td>
                    <td><?= implode(',',$page->getAllowedMethods())  ?></td>
                    <td><a href="<?= $page->getUrl() ?>"><?= $page->getUrl() ?></a></td>
                    <td><?= implode(',',$page->getRoles())  ?></td>
                </tr>
                <?php endif; ?>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Page Creation</h5>
            </div>
            <div class="modal-body">
                <form action="" class="form" method="post">
                    <div class="form-group mt-4">
                        <label for="name">Page Title</label>
                        <input type="text" class="form-control" id="name" name="title">
                        <span>enter page title</span>
                    </div>
                    <div class="form-group mt-4">
                        <label for="methods">Access Methods</label>
                        <select multiple name="methods[]" id="methods" class="form-control">
                            <option value="GET" selected>GET</option>
                            <option value="POST">POST</option>
                            <option value="PUT">PUT</option>
                            <option value="PATCH">PATCH</option>
                            <option value="DELETE">DELETE</option>
                            <option value="HEAD">HEAD</option>
                            <option value="OPTIONS">OPTIONS</option>
                            <option value="CONNECT">CONNECT</option>
                            <option value="TRACE">TRACE</option>
                        </select>
                        <span>enter page title</span>
                    </div>
                    <div class="form-group mt-4">
                        <label for="uri">Page URI</label>
                        <input type="text" class="form-control" id="uri" name="uri">
                        <span>enter page uri</span>
                    </div>
                    <div class="form-group mt-4">
                        <label for="roles">Who will access this page</label>
                        <select multiple name="roles[]" id="roles" class="form-control">
                            <?php if ($content['roles']): foreach ($content['roles'] as $role): ?>
                            <option value="<?= $role->getName() ?>"><?= $role->getLabel() ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="form-group mt-4">
                        <label for="accessible">Page Accessible</label>
                        <input type="checkbox" class="form-check" id="accessible" name="accessible">
                    </div>
                    <div class="form-group mt-4">
                        <label for="name">Page is form route</label>
                        <input type="checkbox" class="form-check" id="name" name="is_form">
                    </div>
                    <div class="form-group mt-4">
                        <label for="description">Page Description</label>
                        <textarea name="description" id="description" class="form-control"></textarea>
                    </div>
                    <div class="form-group mt-5">
                        <input type="submit" value="Submit" class="btn btn-outline-primary">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

