<?php

use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
?>
<section class="container">

    <div class="content-dark clearfix">
        <div class="ds-block">

            <form id="nameForm" method="post" action="/profile/usernamechange">
                <?php if(boolval($this->user['allowNameChange'])): ?>
                    <div id="nameChangeBackDrop"></div>
                    <input name="username" id="nameChange" autofocus autocomplete="off" type="text" class="form-control" placeholder="<?=Tpl::out($this->user['username'])?>" value="" />
                <?php else: ?>
                    <h3><?= Tpl::out($this->user['username']) ?></h3>
                <?php endif; ?>
                <?php if(boolval($this->user['allowNameChange'])): ?>
                    <div id="nameChangeAlert" class="alert alert-danger" role="alert">
                        <p class="mb-0"><i class="fas fa-fw fa-arrow-circle-up"></i>&nbsp;Please confirm your username!</p>
                    </div>
                <?php endif; ?>
            </form>

            <div>
                <p>
                    <span>Joined on <?=Tpl::moment(Date::getDateTime($this->user['createdDate']), 'jS F, Y H:i a', 'Do MMMM, YYYY h:mm a')?></span><br />
                    Check out your old <a href="/profile/subscriptions" title="Your Subscriptions">Subscriptions</a>, <a href="/profile/gifts" title="Your Gifts">Gifts</a> and <a href="/profile/donations" title="Your Donations">Donations</a>.
                    <?php if(!$this->user['allowChatting']): ?>
                    <br /><span>Chat <strong><?= $this->user['allowChatting'] ? 'Enabled' : 'Disabled'?></strong></span>
                    <?php endif; ?>
                </p>
                <hr />
            </div>
            <div style="width: 100%; clear: both;">
                <a href="/logout" class="btn btn-primary">Sign Out</a>
                <button class="btn btn-danger float-right" data-toggle="modal" data-target="#deleteAccountModal">Delete Account</button>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="deleteAccountModal" tabindex="-1" role="dialog" aria-labelledby="deleteAccountModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" id="deleteAccountForm" action="/profile/delete">
                <div class="modal-header">
                    <h4 class="modal-title" id="deleteAccountModalTitle">Delete confirmation</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p style="color: black;">Do you really want to delete your account? <br /> This cannot be undone, so be sure!</p>
                    <div style="margin-bottom: 1em;"><div class="g-recaptcha" data-sitekey="<?= Config::$a['g-recaptcha']['key'] ?>"></div></div>
                    <p>Note: your username will <strong>NOT</strong> be made available after deletion.<br /> You will be logged on completion.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>