<style>
    #people,
    #user-roles {
        .mt-lg-5 {
            margin: 0 !important;
            padding: 0 !important;
        }

        .p-5 {
            padding-left: 0 !important;
            padding-top: 0 !important;
        }
    }
</style>
<div class="container mt-lg-5">
    <div class="p-5 bg-light rounded">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="people-tab" data-bs-toggle="tab" data-bs-target="#people" type="button" role="tab" aria-controls="people" aria-selected="true">People Settings</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="user-roles-tab" data-bs-toggle="tab" data-bs-target="#user-roles" type="button" role="tab" aria-controls="user-roles" aria-selected="false">Roles Settings</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Contact</button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="people" role="tabpanel" aria-labelledby="people-tab">
                <div class="d-block">
                    <?= $content['people'] ?? null; ?>
                </div>
            </div>
            <div class="tab-pane fade" id="user-roles" role="tabpanel" aria-labelledby="user-roles-tab">
                <?= $content['roles'] ?? null; ?>
            </div>
            <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">...</div>
        </div>
    </div>
</div>