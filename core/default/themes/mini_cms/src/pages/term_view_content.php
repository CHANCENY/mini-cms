<div class="container mt-lg-5">
    <?php if(!empty($content['nodes'])): ?>
      <?php foreach ($content['nodes'] as $node): ?>
        <?php if($node instanceof \Mini\Cms\Entities\Node): ?>
                <div class="bg-light m-3 p-3">
                    <p>
                        <a href="/structure/content/node/<?php echo $node->id(); ?>" rel="nofollow" title="<?php echo $node->getTitle(); ?>"><?php echo $node->getTitle(); ?></a>
                    </p>
                </div>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php endif; ?>
</div>
