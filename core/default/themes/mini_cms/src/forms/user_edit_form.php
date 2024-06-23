<?php if (!empty($content['user']) && $content['user'] instanceof \Mini\Cms\Entities\User): ?>
    <div class="container mt-lg-5">
        <div class="bordered rounded bg-light col-md-10 p-5">
            <form class="form" enctype="multipart/form-data" method="post">
                <div class="p-2 m-2 bordered rounded">
                    <div class="row">
                        <h3>User Edit</h3>
                    </div>
                    <div class="row mt-lg-2">
                        <div class="form-group col">
                            <label for="firstname">Firstname:</label>
                            <input type="text" name="firstname" id="firstname" value="<?= $content['user']->getFirstname(); ?>" class="form-control">
                        </div>
                        <div class="form-group col">
                            <label for="lastname">Lastname:</label>
                            <input type="text" name="lastname" value="<?= $content['user']->getLastname(); ?>" id="lastname" class="form-control">
                        </div>
                    </div>
                    <div class="row mt-lg-2">
                        <div class="form-group col">
                            <label for="email">EmailAddress:</label>
                            <input type="text" name="email" value="<?= $content['user']->getEmail(); ?>" id="email" class="form-control">
                        </div>
                        <div class="form-group col">
                            <label for="username">Username:</label>
                            <input type="text" name="username" id="username" value="<?= $content['user']->getName(); ?>" class="form-control">
                        </div>
                    </div>
                    <div class="row mt-lg-2">
                        <div class="form-group col">
                            <label for="image">Profile Image:</label>
                            <input type="file" name="image" id="image" class="form-control" multiple>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mt-lg-5">
                            <input type="submit" name="user" value="Submit" class="btn btn-secondary">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

