<div class="container mt-lg-5">
    <div class="bg-light rounded bordered">
        <form method="post" class="forms">
            <div class="form-group">
                <label for="type" class="for">Extension Type</label>
                <select name="type" id="type" class="form-control">
                    <option value="module">Module</option>
                    <option value="theme">Theme</option>
                </select>
            </div>
            <div class="form-group mt-3">
                <label for="zip_file" class="for">Zip File</label>
                <input type="file" class="form-control" name="zip_file" id="zip_file">
            </div>
            <div class="form-group mt-3">
                <input type="submit" value="Submit" name="extension" class="btn btn-secondary">
            </div>
        </form>
    </div>
</div>
