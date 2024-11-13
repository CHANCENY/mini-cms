<?php if(!empty($content['setting'])): ?>
    <?php if($content['setting']['file_display_type'] === 'image_format'): ?>

        <div class="field-group">
            <?php if($content['setting']['label_visible']): ?>
                <div class="label field-label-<?= $content['setting']['label_name']; ?>">
                    <p class="label"><strong><?php echo $content['setting']['label']; ?>:</strong></p>
                </div>
            <?php endif; ?>
            <?php foreach ($content['value'] as $key=>$value): ?>
                <div class="field-value">
                    <div class="field-field-value field-value-<?= $content['setting']['label_name']; ?>">
                        <img width="<?php echo $value['width'] ?? 0; ?>" height="<?php echo $value['height'] ?? 0; ?>" src="/<?php echo $value['uri'] ?? null; ?>" alt="<?php echo $value['name'] ?? null; ?>" class="field--<?= $content['setting']['label_name']; ?> img-fluid img-thumbnail m-2" id="field--<?= $content['setting']['label_name']; ?>">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($content['setting']['file_display_type'] === 'file_format'): ?>
        <div class="field-group">
            <?php if($content['setting']['label_visible']): ?>
                <div class="label field-label-<?= $content['setting']['label_name']; ?>">
                    <p class="label"><strong><?php echo $content['setting']['label']; ?>:</strong></p>
                </div>
            <?php endif; ?>
            <?php foreach ($content['value'] as $key=>$value): ?>
                <div class="field-value">
                    <div class="field-field-value field-value-<?= $content['setting']['label_name']; ?>">
                        <a href="/<?php echo $value['uri'] ?? null; ?>" title="<?php echo $value['name'] ?? null; ?>" class="field--<?= $content['setting']['label_name']; ?>" id="field--<?= $content['setting']['label_name']; ?>"><?php echo $value['name'] ?? null; ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
