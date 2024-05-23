<div class="container mt-lg-5">
    <div class="bg-light rounded bordered p-5">
        <?php if (!empty($content['field']) && $content['field'] instanceof \Mini\Cms\Fields\FieldInterface): ?>
            <form method="post" class="forms">
                <div class="form-group">
                    <label for="field_name" class="for">Field Name</label>
                    <input type="text" class="form-control" id="field_name" name="field_name" required value="<?= $content['field']?->getLabel() ?>">
                </div>
                <div class="form-group mt-3">
                    <label for="field_type" class="for">Field Type</label>
                    <select name="field_display" id="field_type" required class="form-control">
                        <option>Select Field Type</option>
                        <?php if(!empty($content['displays'])): ?>
                            <?php foreach($content['displays'] as $field): ?>
                                <?php $default = $content['field']?->getDisplayType(); ?>
                                <?php if($field['name'] === $default['name'] ?? null): ?>
                                    <option selected value="<?= $field['name'] ?? null; ?>"><?= $field['label'] ?? null; ?></option>
                                <?php else: ?>
                                    <option value="<?= $field['name'] ?? null; ?>"><?= $field['label'] ?? null; ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group mt-3">
                    <label for="field_description" class="for">Field Description</label>
                    <textarea name="field_description" id="field_description" cols="8" rows="8"
                              class="form-control"><?= $content['field']?->getDescription(); ?></textarea>
                </div>
                <div class="form-group mt-3">
                    <label for="field_label_visible" class="for">Label Visible</label>
                    <input type="checkbox" <?php echo  $content['field']?->isLabelVisible() ? 'checked' : null; ?> class="form-check" name="field_label_visible" id="field_label_visible">
                </div>
                <div class="form-group mt-3">
                    <input type="submit" class="btn btn-secondary" name="field_creation" value="Update Field">
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
