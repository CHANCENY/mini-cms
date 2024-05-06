<?php if(!empty($content)): ?>
    <?php $menus = $content; ?>
    <?php if($menus instanceof \Mini\Cms\Theme\Menus): ?>
        <ul>
            <?php foreach ($menus->getMenus() as $menu_key=>$menu): ?>
                <?php  if($menu instanceof \Mini\Cms\Theme\Menu): ?>
                <?php
                  $attributes = $menu->getAttributes();
                  $title = $attributes['title'] ?? null;
                  $id = $attributes['id'] ?? null;
                  $classes = $attributes['class'] ?? null;
                  $target = $attributes['target'] ?? null;
                  $options = $menu->getOptions();
                  $active_class = !empty($options['active']) ? 'active-menu' : null;
                ?>
                    <a href="<?= $menu->getLink(); ?>" target="<?= $target?>" id="<?= $id ?>" title="<?= $title; ?>" class="<?= $menu_key.' '.$classes. ' '.$active_class; ?>"><?= $menu->getLabel(); ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>
