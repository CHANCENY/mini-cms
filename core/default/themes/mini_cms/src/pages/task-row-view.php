<?php
$task = $content['task'] ?? [];
?>
<input value="<?= $task['tid'] ?? null; ?>" onclick="enableRemoveAll(this)" class="form-check-input m-0 task-checkbox" type="checkbox">
<div class="w-100 ms-3">
    <div class="d-flex w-100 align-items-center justify-content-between">
        <span><?= $time_now < (int) $task['time_stamp'] ? ($task['task'] ?? null) : "<del>". ($task['task'] ?? null)."</del>"; ?></span>
        <button onclick="removeTask(this)" class="btn btn-sm"><i class="fa fa-times"></i></button>
    </div>
</div>
