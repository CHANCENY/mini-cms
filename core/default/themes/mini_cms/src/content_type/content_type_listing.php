<script>
    function question(e) {
        e.preventDefault();
        if(window.confirm("Are you sure you want to delete this content type? note all fields related to this content type will be deleted together with its data."))
        {
            console.log(e.href)
        }
    }
</script>
<div class="container mt-lg-5">
    <div class="p-5 bg-light rounded">
        <div class="d-block">
            <div class="col-sm-4">
                <a href="/admin/content-type/new" class="btn btn-primary">Add Content Type</a>
            </div>
            <div class="table-responsive mt-lg-5">
                <table class="table table-stripped">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Operations</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $theme = \Mini\Cms\Theme\Theme::override('default_admin'); foreach ($content['content_types'] as $content_type): ?>
                     <tr>
                         <?php if($content_type instanceof Mini\Cms\Modules\Content\Node\NodeType): ?>
                            <td><?= $content_type->getLabel() ?></td>
                            <td><?= $content_type->getTypeName() ?></td>
                            <td><?= $content_type->getDescription() ?></td>
                            <td>
                                <a href="/admin/content-types/<?= $content_type->getTypeName() ?>/edit">Edit</a>
                                <a onclick="question(this)" href="/admin/content-types/<?= $content_type->getTypeName() ?>/delete">Delete</a>
                            </td>
                         <?php endif; ?>
                     </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
