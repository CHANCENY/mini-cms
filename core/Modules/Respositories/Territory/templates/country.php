<?php if(!empty($countries)): ?>
    <div class="form-group mt-3 country-field mb-3">
        <label for="country">Country</label>
        <select name="<?= $main_field .'___' ?>country" id="country" class="form-control country field-field-address-field">
            <?php foreach($countries as $country): ?>
                <option
                    <?php echo $country['iso2'] === ($default_country ?? null) ? 'selected' : null; ?>
                    value="<?= $country['iso2'] ?? null; ?>">
                    <?= $country['name'] ?? null; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="wrapper-address-field">
        <?= $fields ?? null; ?>
    </div>
 <div>
<?php endif; ?>
