<section class="container w-75 mt-lg-5 p-lg-5">
    <div class="col-md-9 border rounded p-5 bg-light">
        <div class="d-block mt-lg-5 mb-lg-5">
            <h2>Database Configuration</h2>
        </div>
        <div class="d-block">
            <form class="form" method="POST" enctype="multipart/form-data">
                <div class="row mb-lg-4 border rounded p-2">
                    <h6>Site Access Information:</h6>
                    <div class="form-group col">
                        <label for="domain">Domain Name:</label>
                        <input title="site domain (https://example.com)" id="domain" type="url" name="domain_name" class="form-control" required>
                    </div>
                    <div class="form-group col">
                        <label for="purpose">Site Purpose:</label>
                        <input title="site purpose" id="purpose" type="text" name="purpose" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-lg-4 border rounded p-2">
                    <h6>Site Information:</h6>
                    <div class="form-group col">
                        <label for="site_name">Site Name:</label>
                        <input title="site name" id="site_name" type="text" name="site_name" class="form-control" required>
                    </div>
                    <div class="form-group col">
                        <label for="site_slogan">Site Slogan:</label>
                        <input title="site slogan" id="site_slogan" type="text" name="site_slogan" class="form-control" required>
                    </div>
                    <div class="form-group col">
                        <label for="site_logo">Site Logo:</label>
                        <input title="site logo" id="site_logo" type="file" name="site_logo" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-lg-4 border rounded p-2">
                    <h6>Site Legal Information:</h6>
                    <div class="form-group col">
                        <label for="site_privacy">Site Privacy (pdf,text file):</label>
                        <input title="site privacy legal document" id="site_privacy" type="file" name="site_privacy" class="form-control">
                    </div>
                    <div class="form-group col">
                        <label for="site_terms">Site Terms (pdf,text file):</label>
                        <input title="site terms of usage" id="site_terms" type="file" name="site_terms" class="form-control">
                    </div>
                </div>
                <div class="row mb-lg-4 border rounded p-2">
                    <h6>Site Contact Information:</h6>
                    <div class="form-group col">
                        <label for="site_email">Email:</label>
                        <input title="site email" id="site_email" type="email" name="site_email" class="form-control">
                    </div>
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
                <div class="row mb-lg-4 border rounded p-2">
                    <h6>Social Media Integration:</h6>
                    <div class="form-group col">
                        <label for="Facebook">Facebook:</label>
                        <input title="site Facebook account" id="Facebook" type="text" name="site_facebook" class="form-control">
                    </div>
                    <div class="form-group col">
                        <label for="Instagram">Instagram:</label>
                        <input title="site Instagram account" id="Instagram" type="text" name="site_instagram" class="form-control">
                    </div>
                    <div class="form-group col">
                        <label for="twitter">Twitter:</label>
                        <input aria-label="site Twitter" title="site Twitter" id="Twitter" type="text" name="site_twitter" class="form-control">
                    </div>
                    <div class="form-group col">
                        <label for="LinkedIn">LinkedIn:</label>
                        <input aria-label="site LinkedIn" title="site LinkedIn" id="LinkedIn" type="text" name="LinkedIn" class="form-control">
                    </div>
                </div>
                <div class="row mb-lg-5 mt-lg4">
                    <input type="submit" name="op" class="btn btn-secondary" value="Save Site Configuration">
                </div>
            </form>
        </div>
    </div>
</section>