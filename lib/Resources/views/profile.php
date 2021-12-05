<?
namespace Destiny;

use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Config;
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
    <?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="profile">

<?php include Tpl::file('seg/top.php') ?>
<?php include Tpl::file('seg/headerband.php') ?>

<section class="container">
    <ol class="breadcrumb" style="margin-bottom:0;">
        <li class="active" title="Your personal details">Profile</li>
        <li><a href="/profile/authentication" title="Your login methods">Authentication</a></li>
    </ol>
</section>

<?php if (!empty($model->error)): ?>
    <section class="container">
        <div class="alert alert-error" style="margin:0;">
            <strong>Error!</strong>
            <?= Tpl::out($model->error) ?>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($model->success)): ?>
    <section class="container">
        <div class="alert alert-info" style="margin:0;">
            <strong>Success!</strong>
            <?= Tpl::out($model->success) ?>
        </div>
    </section>
<?php endif; ?>

<section class="container collapsible">
    <h3><span class="glyphicon glyphicon-chevron-right expander"></span> Subscription</h3>

    <?php if (!empty($model->subscription) && !empty($model->subscriptionType)): ?>
        <div class="content">

            <div class="content-dark clearfix">

                <div class="ds-block">
                    <div class="subscription" style="width: auto;">

                        <h3><?= $model->subscriptionType['tierLabel'] ?></h3>
                        <p>
                            <span class="sub-amount">$<?= $model->subscriptionType['amount'] ?></span>
                            (<?= $model->subscriptionType['billingFrequency'] ?> <?= strtolower($model->subscriptionType['billingPeriod']) ?>
                            )
                            <?php if ($model->subscription['recurring'] == 1): ?>
                                <span class="label label-success">Recurring</span>
                            <?php endif; ?>
                        </p>

                        <?php if ($model->subscription['recurring'] == 0): ?>
                            <dl>
                                <dt>Remaining time</dt>
                                <dd><?= Date::getRemainingTime(Date::getDateTime($model->subscription['endDate'])) ?></dd>
                            </dl>
                        <?php endif; ?>

                        <?php if (strcasecmp($model->paymentProfile['state'], 'ActiveProfile') === 0): ?>
                            <dl>
                                <dt>Time remaining until renewal</dt>
                                <dd><?= Date::getRemainingTime(Date::getDateTime($model->subscription['endDate'])) ?></dd>
                            </dl>
                            <dl>
                                <?php
                                $billingNextDate = Date::getDateTime($model->paymentProfile['billingNextDate']);
                                $billingStartDate = Date::getDateTime($model->paymentProfile['billingStartDate']);
                                ?>
                                <dt>Next billing date</dt>
                                <?php if ($billingNextDate > $billingStartDate): ?>
                                    <dd><?= Tpl::moment($billingNextDate, Date::STRING_FORMAT_YEAR) ?></dd>
                                <?php else: ?>
                                    <dd><?= Tpl::moment($billingStartDate, Date::STRING_FORMAT_YEAR) ?></dd>
                                <?php endif; ?>
                            </dl>
                        <?php endif; ?>

                        <?php if (strcasecmp($model->subscription['status'], SubscriptionStatus::PENDING) === 0): ?>
                            <dl>
                                <dt>This subscription is currently</dt>
                                <dd>
                                    <span class="label label-warning"><?= Tpl::out(strtoupper($model->subscription['status'])) ?></span>
                                </dd>
                            </dl>
                        <?php endif; ?>

                        <?php if (!empty($model->subscription['gifterUsername'])): ?>
                            <p>
                                <span class="glyphicon glyphicon-gift"></span> This subscription was gifted by <span
                                        class="label label-success"><?= Tpl::out($model->subscription['gifterUsername']) ?></span>
                            </p>
                        <?php endif; ?>

                    </div>
                </div>

                <div class="form-actions block-foot" style="margin-top:0;">
                    <a class="btn btn-lg btn-primary" href="/subscribe">Update</a>
                    <a class="btn btn-link" href="/subscription/cancel">Cancel subscription</a>
                </div>

            </div>

        </div>

    <?php else: ?>
        <div class="content content-dark clearfix">
            <div class="ds-block">Not subscribed? <a title="Subscribe" href="/subscribe">Try it out</a></div>
        </div>
    <?php endif; ?>

</section>

<?php if (!empty($model->gifts)): ?>
    <section class="container collapsible">
        <h3><span class="glyphicon glyphicon-chevron-right expander"></span> Gifts</h3>
        <div class="content">

            <?php foreach ($model->gifts as $gift): ?>
                <div class="content-dark clearfix" style="margin: 15px 0;">
                    <div class="ds-block">
                        <div>

                            <?php if ($gift['recurring'] == 1): ?>
                                <a class="btn btn-danger pull-right cancel-gift"
                                   href="/subscription/<?= $gift['subscriptionId'] ?>/cancel">Cancel</a>
                            <?php endif; ?>

                            <h3><?= Tpl::out($gift['type']['tierLabel']) ?> <small>Gifted to <span
                                            class="label label-primary"><?= $gift['gifterUsername'] ?></span></small>
                            </h3>
                            <p>
                                <span class="sub-amount">$<?= $gift['type']['amount'] ?></span>
                                <span>(<?= $gift['type']['billingFrequency'] ?> <?= strtolower($gift['type']['billingPeriod']) ?><?php if ($gift['recurring'] == 1): ?> recurring<?php endif; ?>)</span>
                                <small>started
                                    on <?= Tpl::moment(Date::getDateTime($gift['createdDate']), Date::FORMAT) ?></small>
                            </p>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </section>
<?php endif; ?>

<section class="container collapsible">
    <h3><span class="glyphicon glyphicon-chevron-right expander"></span> Account</h3>

    <div class="content content-dark clearfix">
        <form id="profileSaveForm" action="/profile/update" method="post" role="form">

            <div class="ds-block">
                <?php if ($model->user['nameChangedCount'] < Config::$a['profile']['nameChangeLimit']): ?>
                    <div class="form-group">
                        <label>Username:
                            <br><small>(You
                                have <?= Tpl::n(Config::$a['profile']['nameChangeLimit'] - $model->user['nameChangedCount']) ?>
                                name changes left)</small>
                        </label>
                        <input class="form-control" type="text" name="username"
                               value="<?= Tpl::out($model->user['username']) ?>" placeholder="Username"/>
                        <span class="help-block">A-z 0-9 and underscores. Must contain at least 3 and at most 20 characters</span>
                    </div>
                <?php endif; ?>

                <?php if ($model->user['nameChangedCount'] >= Config::$a['profile']['nameChangeLimit']): ?>
                    <div class="form-group">
                        <label>Username:
                            <br><small>(You have no more name changes available)</small>
                        </label>
                        <input class="form-control" type="text" disabled="disabled" name="username"
                               value="<?= Tpl::out($model->user['username']) ?>" placeholder="Username"/>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Email:
                        <br><small>Be it valid or not, it will be safe with us.</small>
                    </label>
                    <input class="form-control" type="text" name="email" value="<?= Tpl::out($model->user['email']) ?>"
                           placeholder="Email"/>
                </div>

                <div class="form-group">
                    <label>Nationality:
                        <br><small>The country you indentify with</small>
                    </label>
                    <select class="form-control" name="country">
                        <option value="">Select your country</option>
                        <? $countries = Country::getCountries(); ?>
                        <option value="">&nbsp;</option>
                        <option value="US" <? if ($model->user['country'] == 'US'):?>
                            selected="selected" <? endif; ?>>United States
                        </option>
                        <option value="GB" <? if ($model->user['country'] == 'GB'):?>
                            selected="selected" <? endif; ?>>United Kingdom
                        </option>
                        <option value="">&nbsp;</option>
                        <? foreach ($countries as $country):?>
                            <option value="<?= $country['alpha-2'] ?>"
                                    <? if ($model->user['country'] != 'US' && $model->user['country'] != 'GB' && $model->user['country'] == $country['alpha-2']): ?>selected="selected" <? endif;
                            ?>><?= Tpl::out($country['name']) ?></option>
                        <? endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Accept Gifts:
                        <br><small>Whether or not you would like the ability to receive gifts (subscriptions) from other
                            people.</small>
                    </label>
                    <select class="form-control" name="allowGifting">
                        <option value="1"<?php if ($model->user['allowGifting'] == 1): ?> selected="selected"<? endif; ?>>
                            Yes, I accept gifts
                        </option>
                        <option value="0"<?php if ($model->user['allowGifting'] == 0): ?> selected="selected"<? endif; ?>>
                            No, I do not accept gifts
                        </option>
                    </select>
                </div>

            </div>

            <div class="form-actions block-foot">
                <button class="btn btn-lg btn-primary" type="submit">Save details</button>
            </div>

        </form>
    </div>
</section>

<section class="container collapsible">
    <h3><span class="glyphicon glyphicon-chevron-right expander"></span> Address <small>(optional)</small></h3>

    <div class="content content-dark clearfix">
        <form id="addressSaveForm" action="/profile/address/update" method="post">

            <div class="ds-block">
                <p><span class="glyphicon glyphicon-info-sign"></span> All fields are required</p>
            </div>

            <div class="ds-block">

                <div class="form-group">
                    <label>Full Name:
                        <br><small>The name of the person for this address</small>
                    </label>
                    <input class="form-control" type="text" name="fullName"
                           value="<?= Tpl::out($model->address['fullName']) ?>" placeholder="Full Name"/>
                </div>
                <div class="form-group">
                    <label>Address Line 1:
                        <br><small>Street address, P.O box, company name, c/o</small>
                    </label>
                    <input class="form-control" type="text" name="line1"
                           value="<?= Tpl::out($model->address['line1']) ?>" placeholder="Address Line 1"/>
                </div>
                <div class="form-group">
                    <label>Address Line 2:
                        <br><small>Apartment, Suite, Building, Unit, Floor etc.</small>
                    </label>
                    <input class="form-control" type="text" name="line2"
                           value="<?= Tpl::out($model->address['line2']) ?>" placeholder="Address Line 2"/>
                </div>

                <div class="form-group">
                    <label>City:</label>
                    <input class="form-control" type="text" name="city" value="<?= Tpl::out($model->address['city']) ?>"
                           placeholder="City"/>
                </div>
                <div class="form-group">
                    <label>State/Province/Region:</label>
                    <input class="form-control" type="text" name="region"
                           value="<?= Tpl::out($model->address['region']) ?>" placeholder="Region"/>
                </div>
                <div class="form-group">
                    <label>ZIP/Postal Code:</label>
                    <input class="form-control" type="text" name="zip" value="<?= Tpl::out($model->address['zip']) ?>"
                           placeholder="Zip/Postal Code"/>
                </div>
                <div class="form-group">
                    <label>Country:</label>
                    <select class="form-control" name="country">
                        <option value="">Select your country</option>
                        <? $countries = Country::getCountries(); ?>
                        <option value="">&nbsp;</option>
                        <option value="US" <? if ($model->address['country'] == 'US'):?>
                            selected="selected" <? endif; ?>>United States
                        </option>
                        <option value="GB" <? if ($model->address['country'] == 'GB'):?>
                            selected="selected" <? endif; ?>>United Kingdom
                        </option>
                        <option value="">&nbsp;</option>
                        <? foreach ($countries as $country):?>
                            <option value="<?= $country['alpha-2'] ?>"
                                    <? if ($model->address['country'] != 'US' && $model->address['country'] != 'GB' && $model->address['country'] == $country['alpha-2']): ?>selected="selected" <? endif;
                            ?>><?= Tpl::out($country['name']) ?></option>
                        <? endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-actions block-foot">
                <button class="btn btn-lg btn-primary" type="submit">Save address</button>
            </div>

        </form>
    </div>
</section>

<br/>

<?php include Tpl::file('seg/foot.php') ?>
<?php include Tpl::file('seg/commonbottom.php') ?>

</body>
</html>