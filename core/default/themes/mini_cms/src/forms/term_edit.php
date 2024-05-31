<div class="container mt-lg-5">
    <div class="bg-light bordered rounded p-5 w-50">
        <form method="post" class="forms">
            <div class="form-group mt-3">
                <h3>Term Edit:</h3>
            </div>
            <div class="form-group mt-lg-5">
                <label for="term_name">Term Name:</label>
                <input type="text" class="form-control" id="term_name" value="<?= $content['term_name'] ?? null; ?>" name="term_name">
            </div>
            <div class="form-group mt-5">
                <input type="submit" name="term_creation" value="Save Term" class="btn btn-secondary">
            </div>
        </form>
    </div>
</div>
