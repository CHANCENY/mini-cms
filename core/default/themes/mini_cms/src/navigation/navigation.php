<style>
    .admin-nav nav,
    .nav-item {
        display: flex;
    }

    .admin-nav nav {
        background-image: linear-gradient(
                rgb(48, 48, 48) 13%,
                rgb(30, 30, 30) 40%,
                #0c0d11 86%
        );
        color: rgba(255, 255, 255, 0.6);
        text-shadow: 0 -2px 0 black;
        cursor: pointer;
        box-shadow: 1px 2px 4px rgb(20, 20, 20), 0 4px 12px rgb(10, 10, 10);
    }

    .admin-nav .nav-item {
        font-size: 0.8999rem;
        line-height: 1rem;
        align-items: center;
        min-width: 120px;
        justify-content: space-between;
        transition: all 80ms ease;

        &.active {
            color: $primary;
            text-shadow: 0 0 3px hsla(260, 100%, 70%, 0.7);
        }

        &:not(.active):hover {
            color: rgba(255, 255, 255, 0.87);
        }

        &:hover > .icon .subicon {
            height: 32px;
            width: 32px;
            border-radius: 32px;
            top: -16px;
            right: -16px;
            border-color: white;
        }

        &:not(:first-of-type) {
            border-left: 1px solid rgb(60, 60, 60);
        }
        &:not(:last-of-type) {
            border-right: 0.1rem solid black;
        }

        a {
            color: inherit;
            text-decoration: none;
            padding: 1ch;
        }

        .icon {
            padding: 1ch;
            position: relative;

            .subicon {
                text-shadow: none;
                transition: all 40ms ease;
                position: absolute;
                top: -3px;
                right: -3px;
                background: red;
                color: white;
                box-shadow: 0 0 4px rgba(41, 41, 41, 0.405);
                width: 18px;
                height: 18px;
                border-radius: 14px;
                font-size: 0.7em;
                font-weight: 700;
                display: inline-grid;
                place-items: center;
                border: 2px solid mix(white, red);
            }
        }

        .icon > svg {
            max-width: 16px;
        }
    }

    .admin-nav {
        overflow: hidden;
        background-color: #333;
    }


    .admin-nav.sticky {
        position: fixed;
        top: 0;
        width: 100%;
    }

    .admin-nav.sticky + .content {
        padding-top: 60px;
    }

</style>
<?php if(!empty($content)): ?>
<?php $menus = $content; ?>
<?php if($menus instanceof \Mini\Cms\Theme\Menus): ?>
<div id="admin-nav" class="admin-nav">
    <nav class="menu" id="nav">
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
	<span class="nav-item <?= $active_class; ?>">
		<span class="icon">
			<i data-feather="<?= $title; ?>"></i>
		</span>
		<a href="<?= $menu->getLink(); ?>"
           id="<?= $id; ?>"
           target="<?= $target; ?>"
           title="<?= $title; ?>"
           class="<?= $classes; ?>">
            <?= $menu->getLabel(); ?>
        </a>
	</span>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
</div>
    <?php endif; ?>
<?php endif; ?>
<script>
    window.onscroll = function() {myFunction()};

    const navbar = document.getElementById("admin-nav");
    const sticky = navbar.offsetTop;

    function myFunction() {
        if (window.pageYOffset > sticky) {
            navbar.classList.add("sticky")
        } else {
            navbar.classList.remove("sticky");
        }
    }
</script>
