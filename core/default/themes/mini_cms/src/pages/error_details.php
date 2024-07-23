<?php $error = $content['error']; ?>
<div class="mt-lg-5 mb-lg-5">
    <div class="bg-light p-5">
        <table class="table datatable">
            <thead>
            <tr>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Message</td>
                    <td><?= $error?->message ?></td>
                </tr>
                <tr>
                    <td>Type</td>
                    <td><?= $error?->type ?></td>
                </tr>
                <tr>
                    <td>Line</td>
                    <td><?= $error?->line ?></td>
                </tr>
                <tr>
                    <td>Code</td>
                    <td><?= $error?->code ?></td>
                </tr>
                <tr>
                    <td>File</td>
                    <td><?= $error?->file ?></td>
                </tr>
                <tr>
                    <td>Trace</td>
                    <td><?= $error?->trace ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>