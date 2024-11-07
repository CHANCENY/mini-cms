<?php
/**
 *  Navigation variables
 *  - content:
 *    This variable has a menus object. Mini\Cms\Theme\Menus
 *  - current_route:
 *    This variable has a current route object. Mini\Cms\Controller\Route
 *  - current_user:
 *    This variable has a current user object Mini\Cms\Modules\CurrentUser\CurrentUser
 *  - site:
 *    This variable has site object Mini\Cms\Modules\Site\Site
 */
$site_name = null;
$site_logo = null;
$site_description = null;
if(!empty($site) && $site instanceof \Mini\Cms\Modules\Site\Site) {
    $site_name = $site->getBrandingAssets('Name');
    $site_logo = $site->getBrandingAssets('Logo');
    if(!empty($site_logo['fid'])) {
        $file = \Mini\Cms\Modules\FileSystem\File::load($site_logo['fid']);
        $site_logo = $file?->getFilePath();
    }
    $site_description = $site->getBrandingAssets('Slogan');
}
?>

<?php if(!empty($content)): ?>
    <?php $menus = $content; ?>
    <?php if($menus instanceof \Mini\Cms\Theme\Menus): ?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <img src="<?= $site_logo; ?>" alt="" width="30" height="24" class="d-inline-block align-text-top">
            <?= $site_name; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($menus->getMenus() as $menu_key=>$menu): ?>
                    <?php  if($menu instanceof \Mini\Cms\Theme\Menu): ?>
                        <?php
                        $attributes = $menu->getAttributes();
                        $title = $attributes['title'] ?? null;
                        $id = $attributes['id'] ?? null;
                        $classes = $attributes['class'] ?? null;
                        $target = $attributes['target'] ?? null;
                        $options = $menu->getOptions();
                        $active_class = !empty($options['active']) ? 'active' : null;
                        ?>
                <li class="nav-item">
                    <a href="<?= $menu->getLink(); ?>"
                       id="<?= $id; ?>"
                       target="<?= $target; ?>"
                       title="<?= $title; ?>"
                       class="<?= $classes; ?> nav-link">
                        <?= $menu->getLabel(); ?>
                    </a>
                </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>
    <?php endif; ?>
<?php endif; ?>