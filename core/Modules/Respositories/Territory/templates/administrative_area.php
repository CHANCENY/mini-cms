<div class="form-group state-field">
    <label for="<?= $key ?? null; ?>" class="label"><?= $label ?? null; ?></label>
    <?php if(!empty($content['options']) && is_array($content['options'])): ?>
      <select name="<?= $field_field_main .'___'. $key ?? null; ?>" class="form-control administrative-area" id="<?= $key ?? null; ?>">
          <?php foreach ($content['options'] as $s=>$v): ?>
           <?php if(is_array($v)): ?>
              <?php $v_v = array_keys($v); $vvv = array_values($v); ?>
              <option value="<?= reset($v_v); ?>"><?= reset($vvv); ?></option>
           <?php endif; ?>
          <?php endforeach; ?>
      </select>
    <?php else: ?>
      <input type="text" name="<?= $field_field_main. '___'.$key ?? null; ?>" id="<?= $key ?? null; ?>" class="form-control administrative-area">
    <?php endif; ?>
</div>
