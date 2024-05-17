<div class="container mt-lg-5">
    <div class="bg-light rounded bordered p-5">
        <form method="post" class="forms">
            <div class="form-group">
                <label for="field_name" class="for">Field Name</label>
                <input type="text" class="form-control" id="field_name" name="field_name" required>
            </div>
            <div class="form-group mt-3">
                <label for="field_type" class="for">Field Type</label>
                <select name="field_type" id="field_type" required class="form-control">
                    <option>Select Field Type</option>
                    <?php if(!empty($content['fields'])): ?>
                     <?php foreach($content['fields'] as $field): ?>
                        <option value="<?= $field['field_type'] ?? null; ?>"><?= $field['label'] ?? null; ?></option>
                     <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group mt-3">
                <label for="field_description" class="for">Field Description</label>
                <textarea name="field_description" id="field_description" cols="8" rows="8"
                          class="form-control"></textarea>
            </div>
            <div class="form-group mt-3">
                <label for="field_size" class="for">Field Size</label>
                <input type="number" class="form-control" id="field_size" name="field_size">
            </div>
            <div class="form-group mt-3">
                <label for="field_required" class="for">Field Required</label>
                <input type="checkbox" name="field_required" id="field_required" value="Yes">
            </div>
            <div class="form-group mt-3">
                <label for="field_default" class="for">Field Default Value</label>
                <input type="text" class="form-control" name="field_default_value" id="field_default">
            </div>
            <div class="form-group mt-3">
                <input type="submit" class="btn btn-secondary" name="field_creation" value="Submit Field">
            </div>
        </form>
    </div>
</div>
