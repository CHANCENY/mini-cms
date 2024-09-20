<script>
    function change_text(e) {
        const string_data = e.value;
        console.log(string_data)
        if(string_data){
            e.value = string_data.replace(' ', '_').toLowerCase();
        }
    }
</script>
<div class="container mt-lg-5 p-5">
    <div class="bordered rounded bg-light col-md-7 p-5">
        <form method="post">
            <div class="form-group">
                <label for="label" class="for">Label</label>
                <input type="text" name="label" id="label" class="form-control">
            </div>
            <div class="form-group mt-3">
                <label for="name" class="for">Name</label>
                <input type="text" oninput="change_text(this)" name="name" class="form-control" id="name">
            </div>
            <div class="form-group mt-3">
                <label for="permission" class="for">Permission</label>
                <select multiple name="permissions[]" class="form-control" id="permission">
                    <option value="authenticated_access">Authenticated Access</option>
                    <option value="administrator_access">Administrator Access</option>
                    <option value="anonymous_access">Anonymous Access</option>
                </select>
            </div>
            <div class="form-group mt-3">
                <input type="submit" name="new-role" value="New Role" class="btn btn-dark">
            </div>
        </form>
    </div>
</div>

