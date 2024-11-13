
<div class="container mt-lg-5">
    <!-- MultiStep Form -->
    <div class="p-5 bg-light rounded w-50">
       <h2>Field Creation</h2>
        <form action="" class="form mt-lg-4" method="post">
            <div class="form-group">
                <label for="field-label">Field Label</label>
                <input type="text" required name="field_label" id="field-label" class="form-control mt-3">
            </div>
            <div class="form-group mt-3 d-none">
                <label for="field-name">Machine Name</label>
                <input type="text" required name="field_name" id="field-name" class="form-control">
            </div>
            <div class="form-group mt-3">
                <label for="field-type">Field Type</label>
                <select name="field_type" id="field-type" class="form-control">
                    <option value="">select</option>
                    <?php if(!empty($content['fields_types'])): foreach ($content['fields_types'] as $fields_type): ?>
                        <option value="<?= $fields_type ?>"><?= $fields_type ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="form-group mt-3">
                <details>
                    <summary>Field Storage Settings</summary>
                    <div class="form-group mt-3">
                        <label for="field_multiple_allowed">Allow multiple</label>
                        <input type="checkbox" name="field_multiple_allowed" id="field_multiple_allowed" class="form-check">
                    </div>
                    <div class="form-group mt-3 d-none">
                        <label for="field_multiple_count">Allowed Limit</label>
                        <input type="number" name="field_multiple_count" id="field_multiple_count" class="form-control">
                    </div>
                    <div class="form-group mt-3">
                        <label for="field_size">Max Size</label>
                        <input type="number" name="field_size" id="field_size" class="form-control">
                    </div>
                    <div class="form-group mt-3">
                        <label for="field_empty_allowed">Empty Allowed</label>
                        <input type="checkbox" name="field_empty_allowed" id="field_empty_allowed" class="form-check">
                    </div>
                    <div class="form-group mt-3 d-none">
                        <label for="field_default_value">Default Value</label>
                        <input type="text" name="field_default_value" id="field_default_value" class="form-control">
                    </div>
                </details>
            </div>
            <div class="form-group mt-5">
                <input type="submit" name="save-content-type-field" value="Save" class="btn btn-secondary">
            </div>
        </form>
    </div>
</div>
<script>
    document.getElementById('field-label').addEventListener('input', (e)=>{
        const contentName = document.getElementById('field-name').parentElement;
        const data = e.target.value;
        if(data.length && contentName.classList.contains('d-none')) {
            contentName.classList.remove('d-none');
            contentName.querySelector('#field-name').value = data.replace(/[^a-zA-Z0-9]/g, '_').toLowerCase()
        }
        else if(data.length && !contentName.classList.contains('d-none')) {
            contentName.querySelector('#field-name').value = data.replace(/[^a-zA-Z0-9]/g, '_').toLowerCase()
        }
        else if(data.length === 0) {
            contentName.classList.add('d-none')
        }
    });

    document.getElementById('field_multiple_allowed').addEventListener('change',(e)=>{
        const count_limit = document.getElementById('field_multiple_count').parentElement;
        if(e.target.checked && count_limit.classList.contains('d-none')) {
            count_limit.classList.remove('d-none');
        }
        else {
            count_limit.classList.add('d-none')
        }
    })

    document.getElementById('field_empty_allowed').addEventListener('change',(e)=>{
        const default_value = document.getElementById('field_default_value').parentElement;
        if(e.target.checked && default_value.classList.contains('d-none')) {
            default_value.classList.remove('d-none');
        }
        else {
            default_value.classList.add('d-none')
        }
    })
</script>