<div class="container-fluid mt-lg-5">
    <div class="bordered bg-light p-3">
        <div class="d-block">
            <a class="text-decoration-none btn btn-info" href="/structure/content-type/field/<?= $content['entity']?->getEntityTypeName(); ?>/new">Add Field</a>
        </div>
        <div class="d-block mt-2">
            <form method="get" class="w-25 d-inline-flex pt-3">
                <input placeholder="Field name" type="text" name="title" class="form-control">
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
                          <th>Machine Name</th>
                          <th>Description</th>
                          <th>Content Type</th>
                          <th>Type</th>
                          <th>Actions</th>
                      </tr>
                    </thead>
                    <?php if(!empty($content['fields'])): ?>
                    <?php foreach ($content['fields'] as $field): ?>
                      <?php if($field instanceof \Mini\Cms\Fields\FieldInterface): ?>
                            <tr>
                                <td><a class="text-decoration-none" href="/structure/content-type/field/<?= $field->getName(); ?>/view"><?= $field->getLabel() ?></a></td>
                                <td><?= $field->getName() ?></td>
                                <td><?= $field->getDescription() ?></td>
                                <td><a class="text-decoration-none" href="/structure/content-type/<?= $content['entity']?->getEntityTypeName(); ?>/view"><?= $content['entity']?->getEntityLabel() ?></a></td>
                                <td><?= $field->getType() ?></td>
                                <td>
                                    <div class="action-button">
                                        <a title="edit content field" aria-label="edit content field" class="text-decoration-none" href="/structure/content-type/field/<?= $field->getName(); ?>/edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <a title="delete content field" aria-label="delete content field" class="text-decoration-none mx-2 ms-2" href="/structure/content-type/field/<?= $field->getName(); ?>/delete"><i class="fa-solid fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                      <?php endif; ?>
                    <?php endforeach; ?>
                    <?php else: ?>
                     <tr>
                         <td></td>
                         <td>No Content fields found</td>
                         <td></td>
                         <td></td>
                         <td></td>
                         <td></td>
                        
                     </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>
