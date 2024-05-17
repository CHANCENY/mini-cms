<div class="container mt-lg-5">
    <div class="bg-light rounded bordered p-5">
        <div>
            <?php $entity = $content['field'] ?? null; ?>
            <?php if($entity instanceof \Mini\Cms\Fields\FieldInterface): ?>
                <div class="bg-white p-3 bordered rounded">
                    <p><strong>Field Label:</strong><br><?= $entity->getLabel() ?></p>
                </div>
                <div class="bg-white p-3 bordered rounded mt-2 mb-2">
                    <p><strong>Field Name:</strong><br><?= $entity->getName() ?></p>
                </div>
                <div class="bg-white p-3 bordered rounded">
                    <p><strong>Content Description:</strong><br><?= $entity->getDescription() ?></p>
                </div>
                <div class="bg-white p-3 bordered rounded">
                    <a class="text-decoration-none" href="/structure/content-type/field/<?= $entity->getName() ?>/edit">Edit</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>