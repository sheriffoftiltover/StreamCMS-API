<?php

use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Config;
use Destiny\Common\User\UserRole;
use Destiny\Common\Utils\Country;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;

?>
<!DOCTYPE html>
<html>
<head>
    <title><?= Tpl::title($model->title) ?></title>
    <meta charset="utf-8">
    <?php include Tpl::file('seg/commontop.php') ?>
</head>
<body id="admin" class="thin">

<?php include Tpl::file('seg/top.php') ?>

<section class="container">
    <ol class="breadcrumb" style="margin-bottom:0;">
        <li><a href="/admin">Users</a></li>
        <li><a href="/admin/chat">Chat</a></li>
        <li><a href="/admin/subscribers">Subscribers</a></li>
        <li><a href="/admin/bans">Bans</a></li>
    </ol>
</section>

<?php if (!empty($model->success)): ?>
    <section class="container">
        <div class="alert alert-info" style="margin-bottom:0;">
            <strong>Success!</strong>
            <?= Tpl::out($model->success) ?>
        </div>
    </section>
<?php endif; ?>

<section class="container collapsible">
    <h3>
        <span class="glyphicon glyphicon-chevron-right expander"></span> Details
        <small>(<?= Tpl::out($model->user['username']) ?>)</small>
    </h3>
    <div class="content content-dark clearfix">

        <form action="/admin/user/<?= Tpl::out($model->user['userId']) ?>/edit" method="post">
            <input type="hidden" name="id" value="<?= Tpl::out($model->user['userId']) ?>"/>

            <div class="ds-block">
                <div class="form-group">
                    <label class="control-label" for="inputUsername">Username / Nickname</label>
                    <div class="controls">
                        <input type="text" class="form-control" name="username" id="inputUsername"
                               value="<?= Tpl::out($model->user['username']) ?>" placeholder="Username">
                        <span class="help-block">A-z 0-9 and underscores. Must contain at least 3 and at most 20 characters</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label" for="inputEmail">Email</label>
                    <div class="controls">
                        <input type="text" class="form-control" name="email" id="inputEmail"
                               value="<?= Tpl::out($model->user['email']) ?>" placeholder="Email">
                        <span class="help-block">Be it valid or not, it will be safe with us.</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Country:</label>
                    <select name="country" class="form-control">
                        <option value="">Select your country</option>
                        <? $countries = Country::getCountries(); ?>
                        <option value="">&nbsp;</option>
                        <option value="US" <? if ($model->user['country'] == 'US'): ?>
                            selected="selected" <? endif; ?>>United States
                        </option>
                        <option value="GB" <? if ($model->user['country'] == 'GB'): ?>
                            selected="selected" <? endif; ?>>United Kingdom
                        </option>
                        <option value="">&nbsp;</option>
                        <? foreach ($countries as $country): ?>
                            <option value="<?= $country['alpha-2'] ?>"
                                    <? if ($model->user['country'] != 'US' && $model->user['country'] != 'GB' && $model->user['country'] == $country['alpha-2']): ?>selected="selected" <? endif; ?>><?= Tpl::out($country['name']) ?></option>
                        <? endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Features:</label>
                    <?php foreach ($model->features as $featureName => $f): ?>
                        <?php if (strcasecmp($featureName, 'subscriber') === 0 || strcasecmp($featureName, 'flair1') === 0 || strcasecmp($featureName, 'flair3') === 0 || strcasecmp($featureName, 'flair8') === 0)
                            continue; // remove subscription flairs?>
                        <label class="checkbox">
                            <input type="checkbox" name="features[]"
                                   value="<?= $f['featureName'] ?>" <?= (in_array($featureName, $model->user['features'])) ? 'checked="checked"' : '' ?>>
                            <?= $f['featureLabel'] ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="form-group">
                    <label>Website Roles:</label>
                    <label class="checkbox">
                        <input type="checkbox" name="roles[]"
                               value="<?= UserRole::ADMIN ?>" <?= (in_array(UserRole::ADMIN, $model->user['roles'])) ? 'checked="checked"' : '' ?>>
                        Administrator
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="/admin" class="btn">Cancel</a>
            </div>

        </form>
    </div>
</section>

<section class="container collapsible">
    <h3><span class="glyphicon glyphicon-chevron-right expander"></span> Address</h3>
    <div class="content content-dark clearfix">

        <?php if (!empty($model->address)): ?>
            <div class="vcard ds-block">
                <div class="fn"><?= Tpl::out($model->address['fullName']) ?></div>
                <br/>
                <div class="adr">
                    <div class="street-address">
                        <?= Tpl::out($model->address['line1']) ?>, <?= Tpl::out($model->address['line2']) ?>
                    </div>
                    <div>
                        <span class="city"><?= Tpl::out($model->address['city']) ?></span>,
                        <span class="region"><?= Tpl::out($model->address['region']) ?></span>,
                        <span class="postal-code"><?= Tpl::out($model->address['zip']) ?></span>
                        <?php
                        $country = Country::getCountryByCode($model->address['country']);
                        if (!empty($country)):
                            ?>
                            <br/>
                            <abbr class="country"><?= Tpl::out($country['name']) ?>
                                <small>(<?= Tpl::out($country['alpha-2']) ?>)</small></abbr>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="ds-block">
                <p>No address available</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="container collapsible">
    <h3><span class="glyphicon glyphicon-chevron-right expander"></span> Subscription</h3>
    <div class="content content-dark clearfix">
        <div class="ds-block">
            <a href="/admin/user/<?= Tpl::out($model->user['userId']) ?>/subscription/add" class="btn btn-primary">New
                subscription</a>
        </div>
        <?php if (!empty($model->subscriptions)): ?>
            <table class="grid">
                <thead>
                <tr>
                    <td>Subscription Type</td>
                    <td>Status</td>
                    <td>Created</td>
                    <td>Ending</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($model->subscriptions as $subinfo): ?>
                    <tr>
                        <td>
                            <a href="/admin/user/<?= Tpl::out($model->user['userId']) ?>/subscription/<?= Tpl::out($subinfo['subscriptionId']) ?>/edit"><?= Tpl::out($subinfo['tierLabel']) ?></a>
                            <?php if ($subinfo['recurring'] == '1'): ?>
                                <span class="subtle">(Recurring)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (strcasecmp($subinfo['status'], SubscriptionStatus::ACTIVE) === 0): ?>
                                <span class="badge badge-success"><?= Tpl::out($subinfo['status']) ?></span>
                            <?php else: ?>
                                <span class="subtle"><?= Tpl::out($subinfo['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= Tpl::moment(Date::getDateTime($subinfo['createdDate']), Date::STRING_FORMAT_YEAR) ?></td>
                        <td><?= Tpl::moment(Date::getDateTime($subinfo['endDate']), Date::STRING_FORMAT_YEAR) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="ds-block">
                <p>No active subscriptions</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="container collapsible">
    <h3><span class="glyphicon glyphicon-chevron-right expander"></span> Smurfs</h3>
    <div class="content content-dark clearfix">
        <div class="ds-block">
            Smurfs are alternative accounts of the user based on the fact that the
            user is using the same IP address on every one of them.<br/>
            The algorithm is the following:<br/>
            We know the last 3 IP addresses of the user and we go and search for any
            other user who has at least one in common.<br/>
            This is <b>not</b> a sure thing.
        </div>
        <?php if (!empty($model->smurfs)): ?>
            <table class="grid">
                <thead>
                <tr>
                    <td>Username</td>
                    <td>Email</td>
                    <td>Created</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($model->smurfs as $user): ?>
                    <tr>
                        <td><a href="/admin/user/<?= $user['userId'] ?>/edit"><?= Tpl::out($user['username']) ?></a>
                        </td>
                        <td><?= Tpl::out($user['email']) ?></td>
                        <td><?= Tpl::moment(Date::getDateTime($user['createdDate']), Date::STRING_FORMAT_YEAR) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="ds-block">
                <p>No smurfs found</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="container collapsible">
    <h3><span class="glyphicon glyphicon-chevron-right expander"></span> IPs</h3>
    <div class="content content-dark clearfix">
        <div class="ds-block">
            <p>The last seen 3 IP addresses of the user (as seen by the chat)</p>
        </div>
        <?php if (!empty($model->user['ips'])): ?>
            <table class="grid">
                <thead>
                <tr>
                    <td>IP</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($model->user['ips'] as $ip): ?>
                    <tr>
                        <td><?= Tpl::out($ip) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="ds-block">
                <p>No IPs found</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="container collapsible">
    <h3><span class="glyphicon glyphicon-chevron-right expander"></span> Ban / Mute</h3>
    <div class="content content-dark clearfix">

        <?php if (empty($model->ban)): ?>

            <div class="ds-block">
                <p>No active bans found</p>
            </div>

            <div class="form-actions">
                <a href="/admin/user/<?= $model->user['userId'] ?>/ban" class="btn btn-danger">Ban user</a>
            </div>

        <?php else: ?>
            <div class="ds-block">
                <p>
                    <?php if (!empty($model->ban['ipaddress'])): ?>
                        Ip: <a target="_blank"
                               href="http://freegeoip.net/json/<?= $model->ban['ipaddress'] ?>"><?= $model->ban['ipaddress'] ?></a>
                    <?php else: ?>
                        Ip: Not set
                    <?php endif; ?>
                </p>
                <p>
                    <?= Tpl::moment(Date::getDateTime($model->ban['starttimestamp']), Date::STRING_FORMAT) ?>
                    <?php if (!empty($model->ban['endtimestamp'])): ?>
                        - <?= Tpl::moment(Date::getDateTime($model->ban['endtimestamp']), Date::STRING_FORMAT) ?>
                    <?php endif; ?>
                </p>
                <blockquote>
                    <p style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= Tpl::out($model->ban['reason']) ?></p>
                    <small class="subtle"><?= Tpl::out((!empty($model->ban['username'])) ? $model->ban['username'] : 'System') ?></small>
                </blockquote>
            </div>

            <?php if (!empty($model->banContext)): ?>
                <div id="ban-context" class="ds-block">
                    <ul class="unstyled" style="height:383px;">
                        <?php foreach ($model->banContext as $line): ?>
                            <li style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <small class="subtle"><?= Tpl::moment(Date::getDateTime($line['timestamp']), Date::STRING_FORMAT, 'h:mm:ss') ?></small>
                                <span>&lt;<?= Tpl::out($line['username']) ?>&gt; <?= Tpl::out($line['data']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <a href="/admin/user/<?= $model->user['userId'] ?>/ban/<?= $model->ban['id'] ?>/edit"
                   class="btn btn-primary">Edit ban</a>
                <a onclick="return confirm('Are you sure?');"
                   href="/admin/user/<?= $model->user['userId'] ?>/ban/remove" class="btn btn-danger">Remove ban</a>
            </div>

        <?php endif; ?>
    </div>
</section>

<?php if (!empty($model->authSessions)): ?>
    <section class="container collapsible">
        <h3><span class="glyphicon glyphicon-chevron-right expander"></span> Authentication</h3>
        <div class="content content-dark clearfix">
            <table class="grid">
                <thead>
                <tr>
                    <td>Provider</td>
                    <td style="width:100%;">Detail</td>
                    <td>Created</td>
                    <td>Modified</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($model->authSessions as $auth): ?>
                    <tr>
                        <td><?= $auth['authProvider'] ?></td>
                        <td><?= (!empty($auth['authDetail'])) ? Tpl::out($auth['authDetail']) : Tpl::out($auth['authId']) ?></td>
                        <td><?= Tpl::moment(Date::getDateTime($auth['createdDate']), Date::STRING_FORMAT_YEAR) ?></td>
                        <td><?= Tpl::moment(Date::getDateTime($auth['modifiedDate']), Date::STRING_FORMAT_YEAR) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<br/>

<?php include Tpl::file('seg/commonbottom.php') ?>

<script src="<?= Config::cdnv() ?>/web/js/admin.js"></script>

</body>
</html>