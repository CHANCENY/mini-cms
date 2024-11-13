<section class="container w-75 mt-lg-5 p-lg-5">
    <div class="col-md-9 border rounded p-5 bg-light">
        <div class="d-block mt-lg-5 mb-lg-5">
            <h2>Site Configuration</h2>
        </div>
        <div class="d-block">
            <form class="form" method="POST" enctype="multipart/form-data">
                <div class="row mb-lg-4 border rounded p-2">
                    <h6>Site Information:</h6>
                    <div class="form-group col">
                        <label for="domain">Site Name:</label>
                        <input title="site name" id="domain" type="text" name="site_name" class="form-control" required>
                    </div>
                    <div class="form-group col">
                        <label for="site_logo">Site Logo:</label>
                        <input title="site logo" id="site_logo" type="file" name="site_logo" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-lg-4 border rounded p-2">
                    <h6>Site Contact Information:</h6>
                    <div class="form-group col">
                        <label for="site_email">Email:</label>
                        <input title="site email" id="site_email" type="email" name="site_email" class="form-control">
                    </div>
                    <div class="form-group col">
                        <label for="site_phone">Phone:</label>
                        <input title="site email" id="site_phone" type="text" name="site_phone" class="form-control">
                    </div>
                </div>
                <div class="row mb-lg-4 border rounded p-2">
                    <h6>Social SMTP Server Integration:</h6>
                    <div class="form-group col">
                        <label for="smtp_server">Smtp Server:</label>
                        <input title="site smtp server" id="smtp_server" type="text" name="smtp_server" class="form-control">
                    </div>
                    <div class="form-group col">
                        <label for="smtp_username">Smtp username:</label>
                        <input aria-label="site smtp username" title="site smtp username" id="smtp_username" type="text" name="smtp_username" class="form-control">
                    </div>
                    <div class="form-group col">
                        <label for="smtp_password">Smtp Password:</label>
                        <input aria-label="site smtp password" title="site smtp password" id="smtp_password" type="password" name="smtp_password" class="form-control">
                    </div>
                </div>
                <div class="row mb-lg-5 mt-lg4">
                    <input type="submit" name="op" class="btn btn-secondary" value="Save Site Configuration">
                </div>
            </form>
        </div>
    </div>
</section>