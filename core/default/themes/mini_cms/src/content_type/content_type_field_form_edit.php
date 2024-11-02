
<div class="container mt-lg-5">
    <!-- MultiStep Form -->
    <div class="p-5 bg-light rounded w-50">
        <h2>Field Update</h2>
        <form action="" class="form mt-lg-4" method="post">
            <div class="form-group">
                <label for="field-label">Field Label</label>
                <input value="<?= $content['field']->getLabel() ?>" type="text" required name="field_label" id="field-label" class="form-control mt-3">
            </div>
            <div class="form-group mt-5">
                <input type="submit" name="save-content-type-field" value="Save" class="btn btn-secondary">
            </div>
        </form>
    </div>
</div>
