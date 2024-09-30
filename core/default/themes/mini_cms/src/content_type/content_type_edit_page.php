<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Content type settings</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Fields settings</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Contact</button>
    </li>
</ul>
<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
        <div class="p-5 bg-light rounded w-75">
            <form action="" class="form mt-lg-4" method="post">
                <div class="form-group">
                    <label for="content-label">Name</label>
                    <input value="<?= isset($content['content_type']) ? $content['content_type']?->getLabel() : null; ?>" type="text" required name="content_label" id="content-label" class="form-control mt-3">
                </div>
                <div class="form-group mt-3">
                    <label for="content-description">Description</label>
                    <textarea name="content_description" id="content-description" class="form-control"><?= isset($content['content_type']) ? $content['content_type']?->getDescription() : null; ?></textarea>
                </div>
                <div class="form-group mt-5">
                    <input type="submit" name="save-content-type" value="Save" class="btn btn-secondary">
                </div>
            </form>
        </div>
    </div>
    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
        <div class="p-5 bg-light rounded w-75">
            <div class="d-block">
                <a class="btn btn-outline-primary mb-5" href="/admin/content-type/<?= isset($content['content_type']) ? $content['content_type']?->getTypeName() : null; ?>/field/new">Add Field</a>
            </div>
            <table class="table table-stripped">
                <thead>
                  <tr>
                      <th>Label</th>
                      <th>Name</th>
                      <th>Type</th>
                      <th>Operations</th>
                  </tr>
                </thead>
                <?php if(!empty($content['fields'])): ?>
                  <?php foreach ($content['fields'] as $field): ?>
                    <?php if($field instanceof \Mini\Cms\Modules\Content\Field\FieldType): ?>
                        <tr>
                            <td><?= $field->getLabel() ?></td>
                            <td><?= $field->getName() ?></td>
                            <td><?= $field->getType() ?></td>
                            <td>
                                <a href="/admin/content-type/<?= $content['content_type']?->getTypeName() ?>/field/<?= $field->getName() ?>/edit">Edit</a>
                                <a href="/admin/content-type/<?= $content['content_type']?->getTypeName() ?>/field/<?= $field->getName() ?>/delete">Delete</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                  <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">...</div>
</div>