<?php
  $inbox = $content['inbox'] ?? [];
?>
<style>
    .modal-dialog {
        max-width: 47% !important;
    }
</style>
<div class="container mt-lg-5">
    <div class="d-none" id="inbox-imap">
        <?= json_encode($inbox,JSON_PRETTY_PRINT); ?>
    </div>
    <div class="p-5 bg-light">
        <div class="d-block imap-panel">
            <div class="container">
                <div class="row">
                    <!-- BEGIN INBOX -->
                    <div class="col-md-12">
                        <div class="grid email">
                            <div class="grid-body">
                                <div class="row">
                                    <!-- BEGIN INBOX MENU -->
                                    <div class="col-md-3">
                                        <h2 class="grid-title"><i class="fa fa-inbox"></i> Inbox</h2>
                                        <a class="btn btn-block btn-primary" href="#composer"><i class="fa fa-pencil"></i>&nbsp;&nbsp;NEW MESSAGE</a>
                                        <hr>
                                    </div>
                                    <!-- END INBOX MENU -->

                                    <!-- BEGIN INBOX CONTENT -->
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <label style="margin-right: 8px;" class="">
                                                    <div class="icheckbox_square-blue" style="position: relative;"><input type="checkbox" id="check-all" class="icheck" style="position: absolute; top: -20%; left: -20%; display: block; width: 140%; height: 140%; margin: 0px; padding: 0px; border: 0px; opacity: 0; background: rgb(255, 255, 255);"><ins class="iCheck-helper" style="position: absolute; top: -20%; left: -20%; display: block; width: 140%; height: 140%; margin: 0px; padding: 0px; border: 0px; opacity: 0; background: rgb(255, 255, 255);"></ins></div>
                                                </label>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                        Action <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu" role="menu">
                                                        <li><a href="#">Mark as read</a></li>
                                                        <li><a href="#">Mark as unread</a></li>
                                                        <li><a href="#">Mark as important</a></li>
                                                        <li class="divider"></li>
                                                        <li><a href="#">Report spam</a></li>
                                                        <li><a href="#">Delete</a></li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div class="col-md-6 search-form">
                                                <form action="#" class="text-right">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control input-sm" placeholder="Search">
                                                        <span class="input-group-btn">
                                            <button type="submit" name="search" class="btn_ btn-primary btn-sm search"><i class="fa fa-search"></i></button></span>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="padding"></div>

                                        <div class="table-responsive">
                                            <table class="table">
                                                <tbody>
                                                <?php if(!empty($inbox)): foreach ($inbox as $in): ?>
                                                <?php if($in['seen']): ?>
                                                <tr class="read">
                                                    <td class="action"><input type="checkbox" value="<?= $in['msgno'] ?? null ?>" /></td>
                                                    <td class="name"><?= $in['to'] ?? null; ?></td>
                                                    <td class="subject"><a href="#<?= $in['msgno'] ?? null; ?>"><?= $in['subject'] ?? null; ?></a></td>
                                                    <td class="time"><?= date('d F, Y H:iA', $in['updated']) ?></td>
                                                </tr>
                                                <?php endif; ?>

                                                <?php ?>
                                                <?php endforeach; endif; ?>
                                                </tbody></table>
                                        </div>
                                    </div>
                                    <!-- END INBOX CONTENT -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END INBOX -->
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="container" id="composer" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="p-5 bg-light rounded">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Composer Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" class="form" method="post">
                    <div class="form-group">
                        <label for="to-email">To Email</label>
                        <input type="email" name="to_email" id="to-email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="to-name">To Name</label>
                        <input type="text" name="to_name" id="to-name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" name="subject" id="subject" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="message">Message (body)</label>
                        <textarea name="message" id="message" class="form-control tinymce-editor"></textarea>
                    </div>
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-outline-primary" name="send-mail">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
