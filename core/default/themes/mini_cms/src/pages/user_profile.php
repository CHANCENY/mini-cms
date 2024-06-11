<?php if(!empty($content['user']) && $content['user'] instanceof \Mini\Cms\Entities\User): ?>
    <div class="container">
        <div class="user-profile">
            <div class="row gutters-sm">
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column align-items-center text-center">
                                <img src="<?= $content['user']->getImage() ?>" class="rounded-circle" width="150">
                                <div class="mt-3">
                                    <h4><?= $content['user']->getFirstname() .' '. $content['user']->getLastname(); ?></h4>
                                    <p class="text-secondary mb-1"><?= $content['user']->getRole() ?></p>
                                    <p class="text-muted font-size-sm">Joined <?= date('d F, Y',$content['user']->getCreated()) ?></p>
                                    <?php if($content['actions_button']): ?>
                                        <a href="/user/<?= $content['user']->getUid() ?>/delete" class="btn btn-danger">Delete profile</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Full Name</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <?= $content['user']->getFirstname() .' '. $content['user']->getLastname(); ?>
                                </div>
                            </div>
                            <?php if($content['actions_button']): ?>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Email</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <?= $content['user']->getEmail(); ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Phone</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <?= $content['user']->get('phone') ?? null; ?>
                                </div>
                            </div>
                            <hr>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Address</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <?= $content['user']->get('address') ?? null ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-12">
                                        <a class="btn btn-info " target="__blank" href="/user/<?= $content['user']->getUid() ?>/edit">Edit</a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
