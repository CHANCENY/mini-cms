<div class="container mt-lg-5">
    <div class="bg-light rounded bordered p-5 col-md-7">
        <form method="post" action="/user/reset-password/<?= $content['token'] ?? null; ?>">
            <div class="form-group">
                <label for="password">New Password:</label>
                <input type="password" name="password" class="form-control" id="password">
            </div>
            <div class="form-group mt-3">
                <label for="confirm">Confirm New Password:</label>
                <input type="password" name="confirm_password" class="form-control" id="confirm">
            </div>
            <div class="form-group mt-3">
                <input type="submit" name="password_change" class="btn btn-secondary" value="Change Password">
            </div>
        </form>
    </div>
</div>
