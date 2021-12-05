<?
namespace Destiny;

use Destiny\Common\Config;
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
<body id="ordercomplete">

<?php include Tpl::file('seg/top.php') ?>
<?php include Tpl::file('seg/headerband.php') ?>

<section class="container">

    <h1 class="title">
        <span>Complete</span> <small>successful</small>
    </h1>

    <div class="content content-dark clearfix">

        <div class="ui-step-legend-wrap clearfix">
            <div class="ui-step-legend clearfix">
                <ul>
                    <li style="width: 25%;"><a>Select a subscription</a></li>
                    <li style="width: 25%;"><a>Confirmation</a></li>
                    <li style="width: 25%;"><a>Pay subscription</a></li>
                    <li style="width: 25%;" class="active"><a>Complete</a><i class="arrow"></i></li>
                </ul>
            </div>
        </div>

        <form action="/" method="GET">

            <div class="ds-block">
                <p>Your order was successful, The order reference is <span
                            class="label label-default">#<?= $model->order['orderId'] ?></span>
                    <br/>Please email <a
                            href="mailto:<?= Config::$a['paypal']['support_email'] ?>"><?= Config::$a['paypal']['support_email'] ?></a>
                    for any queries or issues.
                    <br/><br/>Thank you for your support!</p>
            </div>

            <div class="subscription-tier ds-block">

                <div class="subscription" style="width: auto;">
                    <h3><?= $model->subscriptionType['tierLabel'] ?></h3>
                    <p><span class="sub-amount">$<?= $model->subscriptionType['amount'] ?></span>
                        (<?= $model->subscriptionType['billingFrequency'] ?> <?= strtolower($model->subscriptionType['billingPeriod']) ?>
                        )</p>

                    <?php if ($model->subscription['recurring'] == 1): ?>
                        <p>Subscription is automatically renewed</p>
                    <?php endif; ?>

                    <?php if (!empty($model->giftee)): ?>
                        <p><span class="glyphicon glyphicon-gift"></span> You have gifted this to <span
                                    class="label label-danger"><?= Tpl::out($model->giftee['username']) ?></span></p>
                    <?php endif; ?>

                </div>

            </div>

            <div class="form-actions">
                <img class="pull-right" title="Powered by Paypal"
                     src="<?= Config::cdn() ?>/web/img/Paypal.logosml.png"/>
                <a href="/profile" class="btn btn-primary">Back to profile</a>
            </div>

        </form>

    </div>
</section>

<?php include Tpl::file('seg/foot.php') ?>
<?php include Tpl::file('seg/commonbottom.php') ?>

</body>
</html>