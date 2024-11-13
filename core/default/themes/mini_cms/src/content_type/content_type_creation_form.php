
<div class="container mt-lg-5">
    <!-- MultiStep Form -->
    <div class="p-5 bg-light rounded w-50">
        <?php if(!isset($content['content_type'])): ?>
            <h2>Content Type Creation</h2>
        <?php else: ?>
            <h2>Update Type Creation</h2>
        <?php endif; ?>
        <form action="" class="form mt-lg-4" method="post">
            <div class="form-group">
                <label for="content-label">Name</label>
                <input value="<?= isset($content['content_type']) ? $content['content_type']?->getLabel() : null; ?>" type="text" required name="content_label" id="content-label" class="form-control mt-3">
            </div>
            <div class="form-group mt-3 d-none">
                <label for="content-name">Machine Name</label>
                <input value="<?= isset($content['content_type']) ? $content['content_type']?->getTypeName() : null; ?>" type="text" required name="content_name" id="content-name" class="form-control">
            </div>
            <div class="form-group mt-3">
                <label for="content-description">Description</label>
                <textarea name="content_description" id="content-description" class="form-control"><?= isset($content['content_type']) ? $content['content_type']?->getDescription() : null; ?></textarea>
            </div>
            <div class="form-group mt-5">
                <input type="submit" name="save-content-type" value="Save" class="btn btn-secondary">
            </div>
        </form>
    </div>
</div>
<script>
  document.getElementById('content-label').addEventListener('input', (e)=>{
      const contentName = document.getElementById('content-name').parentElement;
      const data = e.target.value;
      if(data.length && contentName.classList.contains('d-none')) {
          contentName.classList.remove('d-none');
          contentName.querySelector('#content-name').value = data.replace(/[^a-zA-Z0-9]/g, '_').toLowerCase()
      }
      else if(data.length && !contentName.classList.contains('d-none')) {
          contentName.querySelector('#content-name').value = data.replace(/[^a-zA-Z0-9]/g, '_').toLowerCase()
      }
      else if(data.length === 0) {
          contentName.classList.add('d-none')
      }
  })
</script>