<div class="container-fluid mt-lg-5">
    <div class="bordered bg-light p-3">
        <div class="d-block">
            <a class="text-decoration-none btn btn-info" href="/structure/content-type/new">Add Content type</a>
        </div>
        <div class="d-block mt-2">
            <form method="get" class="w-25 d-inline-flex pt-3">
                <input placeholder="Content type name" type="text" name="title" class="form-control">
                <input type="submit" name="search" class="btn ms-4 btn-secondary" value="Search">
            </form>
        </div>
        <div class="clearfix"></div>
        <div class="d-block mt-5">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                      <tr>
                          <th>Title</th>
                          <th>Description</th>
                          <th>Action</th>
                      </tr>
                    </thead>
                    <?php if(!empty($content['entities'])): ?>
                    <?php foreach ($content['entities'] as $entity): ?>
                      <?php if($entity instanceof \Mini\Cms\Entity): ?>
                            <tr>
                                <td><a class="text-decoration-none" href="/structure/content-type/<?= $entity->getEntityTypeName(); ?>/content-form"><?= $entity->getEntityLabel() ?></a></td>
                                <td><?= $entity->getEntityTypeDescription() ?></td>
                                <td>
                                    <div class="action-button">
                                        <a title="edit content type" aria-label="edit content type" class="text-decoration-none" href="/structure/content-type/<?= $entity->getEntityTypeName(); ?>/edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <a title="delete content type" aria-label="delete content type" class="text-decoration-none mx-2 ms-2" href="/structure/content-type/<?= $entity->getEntityTypeName(); ?>/delete"><i class="fa-solid fa-trash"></i></a>
                                        <a title="manager content fields" aria-label="manager content fields" class="text-decoration-none" href="/structure/content-type/<?= $entity->getEntityTypeName(); ?>/fields"><i class="fa-solid fa-list"></i></a>
                                    </div>
                                </td>
                            </tr>
                      <?php endif; ?>
                    <?php endforeach; ?>
                    <?php else: ?>
                     <tr>
                         <td></td>
                         <td>No Content type found</td>
                         <td></td>
                     </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>
