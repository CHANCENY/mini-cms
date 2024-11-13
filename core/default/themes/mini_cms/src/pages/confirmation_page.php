<div class="container mt-lg-5">
    <div class="bg-light bordered rounded p-5">
        <div class="d-block mb-4">
            <h4><?= $content['title'] ?? null; ?></h4>
        </div>
        <div class="d-inline">
            <a class="btn btn-primary mx-3" href="?action=1">Continue</a>
            <a class="btn btn-danger" href="?action=0">Cancel</a>
        </div>
    </div>
</div>