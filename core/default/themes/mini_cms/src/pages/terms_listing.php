<div class="container-fluid mt-lg-5">
    <div class="bordered bg-light p-3">
        <div class="d-block">
            <a class="text-decoration-none btn btn-info" href="/structure/vocabularies/<?php echo $content['vid'] ?? null; ?>/term/new">Add Term</a>
        </div>
        <div class="d-block mt-2">
            <form method="get" class="w-25 d-inline-flex pt-3">
                <input placeholder="Term" type="text" name="title" class="form-control">
                <input type="submit" name="search" class="btn ms-4 btn-secondary" value="Search">
            </form>
        </div>
        <div class="clearfix"></div>
        <div class="d-block mt-5">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <?php if(!empty($content['terms'])): ?>
                        <?php foreach ($content['terms'] as $term): ?>
                            <?php if($term instanceof \Mini\Cms\Entities\Term): ?>
                                <tr>
                                    <td><a class="text-decoration-none" href="/structure/vocabularies/<?= $term->getId(); ?>/content-form"><?= $term->getTerm(); ?></a></td>
                                    <td>
                                        <div class="action-button">
                                            <a title="edit vocabulary" aria-label="edit vocabulary" class="text-decoration-none" href="/structure/vocabularies/<?= $content['vid'] ?>/term/<?= $term->getId() ?>/edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                            <a title="delete vocabulary" aria-label="delete vocabulary" class="text-decoration-none mx-2 ms-2" href="/structure/vocabularies/<?= $content['vid']; ?>/term/<?= $term->getId() ?>/delete"><i class="fa-solid fa-trash"></i></a>
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
