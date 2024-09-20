<div class="container mt-lg-5 p-5">
    <div class="bordered rounded bg-light col-md-7 p-5">
        <form method="post">
            <div class="form-group">
                <label for="label" class="for">Max Age</label>
                <input value="<?= getConfigValue('caching_setting.max_age') ?>" type="number" name="ma-age" id="label" class="form-control">
                <span>Time in hours for max-age header value</span>
            </div>
            <div class="form-group mt-3">
                <label for="caching" class="for">Mini Cache Server</label>
                <input <?= getConfigValue('caching_setting.enabled') ? 'checked' : null; ?> type="checkbox" class="form-check" name="cache-setting" id="caching">
            </div>
            <div class="form-group mt-3">
                <input type="submit" name="caching-saver" value="Save Configuration" class="btn btn-dark">
            </div>
        </form>
    </div>
</div>