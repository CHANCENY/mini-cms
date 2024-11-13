<?php if(!empty($content['setting']) && !empty($content['value'])): ?>
    <div class="field-group">
        <?php if($content['setting']['label_visible']): ?>
            <div class="label field-label-<?= $content['setting']['label_name']; ?>">
                <p class="label"><strong><?php echo $content['setting']['label']; ?>:</strong></p>
            </div>
        <?php endif; ?>
        <div class="field-value">
            <div class="field-field-value field-value-<?= $content['setting']['label_name']; ?>">
                <p class="field--<?= $content['setting']['label_name']; ?>" id="field--<?= $content['setting']['label_name']; ?>">
                    <?php echo $content['value']; ?>
                </p>
            </div>
        </div>
    </div>

<?php endif; ?>