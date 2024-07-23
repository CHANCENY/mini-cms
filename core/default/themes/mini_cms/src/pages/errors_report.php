<div class="mt-lg-5 mb-lg-5">
    <div class="bg-light p-5">
        <div class="mb-5 mt-5">
            <a href="/reporting/errors/delete" class="btn btn-danger">Clear</a>
        </div>
        <table class="table datatable">
            <thead>
            <tr>
                <th>Message</th>
                <th>Type</th>
            </tr>
            </thead>
            <tbody>
            <?php if(!empty($content['errors'])): foreach($content['errors'] as $error):  ?>
                <tr>
                    <td><a href="/reporting/errors/<?= $error->report_on ?>"><?= $error->message ?></a></td>
                    <td><?= $error->type ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>