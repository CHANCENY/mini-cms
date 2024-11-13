<div class="container-fluid mt-lg-5">
    <div class="bordered bg-light p-3">
        <div class="d-block">
            <a class="text-decoration-none btn btn-info" href="/structure/vocabularies/new">Add Vocabulary</a>
        </div>
        <div class="d-block mt-2">
            <form method="get" class="w-25 d-inline-flex pt-3">
                <input placeholder="Vocabulary" type="text" name="title" class="form-control">
                <input type="submit" name="search" class="btn ms-4 btn-secondary" value="Search">
            </form>
        </div>
        <div class="clearfix"></div>
        <div class="d-block mt-5">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Label</th>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <?php if(!empty($content['vocabularies'])): ?>
                        <?php foreach ($content['vocabularies'] as $vocabulary): ?>
                            <?php if($vocabulary instanceof \Mini\Cms\Vocabulary): ?>
                                <tr>
                                    <td><a class="text-decoration-none" href="/structure/vocabularies/<?= $vocabulary->getVocabulary(); ?>/content-form"><?= $vocabulary->getLabelName(); ?></a></td>
                                    <td><?= $vocabulary->getVocabulary(); ?></td>
                                    <td>
                                        <div class="action-button">
                                            <a title="edit vocabulary" aria-label="edit vocabulary" class="text-decoration-none" href="/vocabularies/<?= $vocabulary->getVocabulary(); ?>/term/list"><i class="fa-solid fa-list"></i></a>
                                            <a title="edit vocabulary" aria-label="edit vocabulary" class="text-decoration-none" href="/structure/vocabularies/<?= $vocabulary->getVocabulary(); ?>/edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                            <a title="delete vocabulary" aria-label="delete vocabulary" class="text-decoration-none mx-2 ms-2" href="/structure/vocabularies/<?= $vocabulary->getVocabulary(); ?>/delete"><i class="fa-solid fa-trash"></i></a>
                                            <a title="add term" aria-label="add term" class="text-decoration-none mx-2 ms-2" href="/structure/vocabularies/<?= $vocabulary->getVocabulary(); ?>/term/new"><i class="fa-solid fa-plus"></i></a>
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