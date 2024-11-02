<div class="container mt-lg-5">
    <div class="p-5 border rounded">
        <form method="post" action="/caching/settings">
            <div class="form-group">
                <label for="is_global">Global Caching</label>
                <input type="checkbox" name="is_global" id="is_global" class="form-check-input" <?= !empty($content['cache_global']) ? 'checked': null;  ?>>
            </div>
            <div class="form-group mt-5">
                <label for="cached">Cache Keys</label>
                <select class="form-control" name="cache_key[]" id="cached" multiple>
                    <option value="">Select Cache Key</option>
                    <option value="all">All</option>
                    <?php if(!empty($content['cached'])): foreach ($content['cached'] as $k=>$v): ?>
                       <option value="<?= $k ?>"><?= $k ?></option>
                    <?php endforeach; endif; ?>
                </select>
                <span>select cache key to delete, select All to clear all cache or nothing not to clean up</span>
            </div>
            <div class="form-group mt-5">
                <input class="btn btn-outline-primary" type="submit" value="Save Changes">
            </div>
        </form>
    </div>
</div>
