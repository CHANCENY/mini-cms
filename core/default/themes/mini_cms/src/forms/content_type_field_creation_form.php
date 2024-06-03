<div class="container mt-lg-5">
    <div class="bg-light rounded bordered p-5">
        <form method="post" class="forms">
            <div class="form-group">
                <label for="field_name" class="for">Field Name</label>
                <input type="text" class="form-control" id="field_name" name="field_name" required>
            </div>
            <div class="form-group mt-3">
                <label for="field_type" class="for">Field Type</label>
                <select name="field_type" id="field_type" required class="form-control" onchange="displaySetting(this)">
                    <option>Select Field Type</option>
                    <?php if(!empty($content['fields'])): ?>
                     <?php foreach($content['fields'] as $field): ?>
                        <option value="<?= $field['field_type'] ?? null; ?>"><?= $field['label'] ?? null; ?></option>
                     <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group mt-3">
                <label for="field_display" class="for">Field Display</label>
                <select name="field_display" id="field_display" required class="form-control">
                    <option>Select Field Display</option>
                </select>
            </div>
            <div class="form-group mt-3">
                <label for="field_description" class="for">Field Description</label>
                <textarea required name="field_description" id="field_description" cols="8" rows="8"
                          class="form-control"></textarea>
            </div>
            <div class="form-group mt-3">
                <label for="field_size" class="for">Field Size</label>
                <input type="text" class="form-control" id="field_size" name="field_size">
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
                <label for="field_label_visible" class="for">Label Visible</label>
                <input type="checkbox" checked class="form-check" name="field_label_visible" id="field_label_visible">
            </div>
            <div class="form-group mt-3">
                <input type="submit" class="btn btn-secondary" name="field_creation" value="Submit Field">
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">

    function displaySetting(event) {
       const value = event.value;
       const xhr = new XMLHttpRequest();
       const settField = document.getElementById('field_display');
       xhr.open('GET', '?type='+value+'&action=display');
       xhr.onload = function () {
           if(this.status === 200) {
               try {
                   const settings = JSON.parse(this.responseText);
                   if(settField) {
                       settField.innerHTML = '';
                   }
                   Array.from(settings).forEach((item)=>{
                       const option = document.createElement('option');
                       option.value = item.name;
                       option.textContent = item.label;
                       if(settField) {
                           settField.appendChild(option);
                       }
                   })
               }catch (e) {

               }
           }
       }
       xhr.send();
       field_reference_settings(event);
    }

    function field_reference_settings(event) {

      const parentEl = event.parentElement.parentElement;
        if(event.value === 'reference') {

            const selectRef = document.createElement('select');
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '/filters/autocomplete?action=ref_types', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function () {
                if(this.status === 200) {
                    try {
                        const types = JSON.parse(this.responseText);
                        Array.from(types).forEach((item)=>{
                            const option = document.createElement('option');
                            option.value = item.type+'|'+item.name;
                            option.textContent = item.type +' '+item.label;
                            selectRef.appendChild(option);
                        });
                    }catch (e) {

                    }
                }
            }
            xhr.send();

            selectRef.name =  'reference_setting';
            selectRef.id = 'reference_setting';
            selectRef.className = 'form-control';
            const label = document.createElement('label');
            label.for = 'reference_setting';
            label.textContent = "Reference Settings";

            const div = document.createElement('div');
            div.className = 'form-group mt-3';
            div.appendChild(label);
            div.appendChild(selectRef);

            parentEl.insertBefore(div,event.parentElement.nextSibling);
        }
        else {
            const ref = document.getElementById('reference_setting');
            if(ref) {
                ref.parentElement.remove();
            }
        }
    }
</script>