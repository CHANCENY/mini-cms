<div class="container mt-lg-5">
    <div class="bg-light p-5 bordered rounded">
        <div class="node-title">
            <h1><?php echo $content['node']?->getTitle(); ?></h1>
        </div>
        <?php  echo $content['markup_line'] ?? null;?>
    </div>
</div>
