<div class="container mt-lg-5">
    <div class="bg-light rounded bordered p-5">
        <?php if (!empty($content['field']) && $content['field'] instanceof \Mini\Cms\Fields\FieldInterface): ?>
        <form method="post" class="forms">
            <div class="form-group">
                <label for="field_name" class="for">Field Name</label>
                <input type="text" class="form-control" id="field_name" name="field_name" required value="<?= $content['field']?->getLabel() ?>">
            </div>
            <div class="form-group mt-3">
                <label for="field_description" class="for">Field Description</label>
                <textarea name="field_description" id="field_description" cols="8" rows="8"
                          class="form-control"><?= $content['field']?->getDescription(); ?></textarea>
            </div>
            <div class="form-group mt-3">
                <input type="submit" class="btn btn-secondary" name="field_creation" value="Update Field">
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
