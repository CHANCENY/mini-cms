<div class="container mt-lg-5">
    <div class="bg-light p-5 rounded">
            <div class="col d-block">
                <h3>Toast Message</h3>
            </div>
            <div class="col d-block">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Sockets List</button>
                        <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">Add Socket Configuration</button>
                        <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact" aria-selected="false">Messages List</button>

                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                        <?php if(!empty($content['sockets'])): ?>
                         <table class="table table-stripped table-responsive">
                             <thead>
                             <tr>
                                 <th>Title</th>
                                 <th>Key</th>
                                 <th>Host</th>
                                 <th>Port</th>
                                 <th>Action</th>
                             </tr>
                             </thead>
                             <tbody>
                               <?php foreach($content['sockets'] as $socket): ?>
                                <tr>
                                    <td><?= $socket['title'] ?></td>
                                    <td><?= $socket['key'] ?></td>
                                    <td><?= $socket['host'] ?></td>
                                    <td><?= $socket['port'] ?></td>
                                    <td><a href="/toast/configuration/<?= $socket['key'] ?>/test" target="_blank">Test Connection</a></td>
                                </tr>
                               <?php endforeach; ?>
                             </tbody>
                         </table>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                        <div class="d-block">
                            <form method="post" class="form">
                                <div class="form-group">
                                    <label for="host">Host</label>
                                    <input type="text" name="host" required class="form-control" id="host">
                                </div>
                                <div class="form-group mt-4">
                                    <label for="port">Port</label>
                                    <input type="text" name="port" required class="form-control" id="port">
                                </div>
                                <details class="form-group mt-4">
                                    <summary>Others</summary>
                                    <div class="form-group">
                                        <label for="title">Title</label>
                                        <input type="text" name="title" required class="form-control" id="title">
                                    </div>
                                    <div class="form-group mt-4  mb-4">
                                        <label for="key">Key</label>
                                        <input type="text" name="key" required class="form-control" id="key">
                                        <span>key will be used to identify this configuration</span>
                                    </div>
                                </details>
                                <div class="form-group mt-4">
                                    <input type="submit" required class="btn btn-outline-primary" id="submit" name="save" value="Save">
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">...</div>
                </div>
            </div>
    </div>
</div>