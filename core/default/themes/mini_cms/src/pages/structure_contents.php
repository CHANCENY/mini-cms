<div class="container-fluid mt-lg-5">
    <div class="bordered bg-light p-3">
        <div class="d-block">
            <a class="text-decoration-none btn" href="/structure/content-type/new">Add Contents</a>
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
                <table class="table database">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Bundle</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>Created</th>
                        <th>Author</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <?php if(!empty($content['nodes'])): ?>
                        <?php foreach ($content['nodes'] as $node): ?>
                            <?php if($node instanceof \Mini\Cms\Entities\Node): ?>
                                <tr>
                                    <td><a class="text-decoration-none" href="/structure/content/node/<?= $node->id(); ?>"><?= $node->getTitle() ?></a></td>
                                    <td><?= $node->type(); ?></td>
                                    <td><?= $node->published() ? 'Yes' : 'NO'; ?></td>
                                    <td><?= $node->updatedOn('d-m-Y'); ?></td>
                                    <td><?= $node->createdOn('d-m-Y'); ?></td>
                                    <td><?= $node->author()?->getName(); ?></td>
                                    <td>
                                        <div class="action-button">
                                            <a title="edit content type" aria-label="edit content type" class="text-decoration-none" href="/structure/content/node/<?= $node->id(); ?>/edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                            <a title="delete content type" aria-label="delete content type" class="text-decoration-none mx-2 ms-2" href="/structure/content/node/<?= $node->id(); ?>/delete"><i class="fa-solid fa-trash"></i></a>
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