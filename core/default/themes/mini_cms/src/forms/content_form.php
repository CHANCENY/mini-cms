<div class="container mt-lg-5">
    <div class="bg-light rounded bordered p-5 m-auto">
        <form class="forms" method="post">
            <?php if(!empty($content['entity']) && $content['entity'] instanceof \Mini\Cms\Entity): ?>
             <div class="mb-5">
                 <h2><?= $content['entity']->getEntityLabel(); ?></h2>
             </div>
            <?php endif; ?>
            <?php if(!empty($content['fields'])): ?>
            <?= $content['fields']; ?>
            <?php endif; ?>
            <div class="form-group mt-3">
                <input type="submit" name="content_submission" value="Save" class="btn btn-secondary">
            </div>
        </form>
    </div>
</div>
