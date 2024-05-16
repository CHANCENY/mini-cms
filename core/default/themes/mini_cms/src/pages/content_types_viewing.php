<div class="container mt-lg-5">
    <div class="bg-light rounded bordered p-5">
        <div>
            <?php $entity = $content['entity'] ?? null; ?>
            <?php if($entity instanceof \Mini\Cms\Entity): ?>
                <div class="bg-white p-3 bordered rounded">
                    <p><strong>Content Type Label:</strong><br><?= $entity->getEntityLabel() ?></p>
                </div>
                <div class="bg-white p-3 bordered rounded mt-2 mb-2">
                    <p><strong>Content Name:</strong><br><?= $entity->getEntityTypeName() ?></p>
                </div>
                <div class="bg-white p-3 bordered rounded">
                    <p><strong>Content Description:</strong><br><?= $entity->getEntityTypeDescription() ?></p>
                </div>
                <div class="bg-white p-3 bordered rounded">
                    <a class="text-decoration-none" href="/structure/content-type/<?= $entity->getEntityTypeName() ?>/edit">Edit</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>