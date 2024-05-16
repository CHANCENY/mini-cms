<div class="container mt-lg-5">
    <div class="bg-light rounded bordered p-5">
        <form method="post" class="form">
            <?php $entity = $content['entity'] ?? null; if ($entity instanceof \Mini\Cms\Entity): ?>
                <div class="form-group">
                    <label for="content_label">Content Type Label</label>
                    <input type="text" value="<?= $entity->getEntityLabel(); ?>" name="content_label" class="form-control" id="content_label">
                </div>
                <div class="form-group mt-3">
                    <label for="content_name">Content Type Name</label>
                    <input readonly disabled value="<?= $entity->getEntityTypeName(); ?>" type="text" name="content_name" class="form-control" id="content_name">
                </div>
                <div class="form-group mt-3">
                    <label for="content_description">Content Type Description</label>
                    <textarea cols="8" rows="8" name="content_description" class="form-control" id="content_description"><?= $entity->getEntityTypeDescription(); ?></textarea>
                </div>
                <div class="form-group mt-3">
                    <input type="submit" name="content_update" class="btn btn-secondary" value="Submit and Update">
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>
