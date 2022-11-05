<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Decimal\Decimal;


require '../../StreamCMSInit.php';

class Product
{
    public function __construct(protected Decimal $basePrice, protected Decimal $profitMargin, protected string $name)
    {

    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): Decimal
    {
        return $this->basePrice->mul($this->profitMargin->add(new Decimal('1.0')));
    }

    public function getProfitMargin(): Decimal
    {
        return $this->profitMargin;
    }

    public function getProfit(): Decimal
    {
        return $this->basePrice->mul($this->profitMargin);
    }

    public function getBasePrice(): Decimal
    {
        return $this->basePrice;
    }

    public function toArray(): array
    {

    }
}

class Billable
{
    public function __construct(
        protected Product $product,
        protected CarbonImmutable|null $lastDueDate = null,
        protected CarbonImmutable|null $createdAt = null,
    )
    {
        $this->createdAt ??= CarbonImmutable::now();
        $this->lastDueDate ??= CarbonImmutable::now();
    }

    public function getPrice(): Decimal
    {
        return $this->product->getPrice();
    }

    public function getProfitMargin(): Decimal
    {
        return $this->product->getProfitMargin();
    }

    public function getBasePrice(): Decimal
    {
        return $this->product->getBasePrice();
    }

    public function getProfit(): Decimal
    {
        return $this->product->getProfit();
    }

    public function getLifetimeProfit(): Decimal
    {
        return (new Decimal(CarbonImmutable::today()->diffInMonths($this->createdAt)))->mul($this->getProfit());
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getCreatedAt(): CarbonImmutable
    {
        return $this->createdAt;
    }
}

class Account
{
    /** @var Billable[] */
    protected array $billables = [];

    public function addBillable(Billable $billable): void
    {
        $this->billables[] = $billable;
    }

    public function getLifetimeProfit(): Decimal
    {
        $totalProfit = new Decimal('0.00');
        foreach ($this->billables as $billable) {
            $totalProfit = $totalProfit->add($billable->getLifetimeProfit());
        }
        return $totalProfit;
    }

    public function getMonthlyProfit(): Decimal
    {
        $totalProfit = new Decimal('0.00');
        foreach ($this->billables as $billable) {
            $totalProfit = $totalProfit->add($billable->getProfit());
        }
        return $totalProfit;
    }

    public function getMRR(): Decimal
    {
        $mrr = new Decimal('0.00');
        foreach ($this->billables as $billable) {
            $mrr = $mrr->add($billable->getPrice());
        }
        return $mrr;
    }

    public function printProfit(): void
    {
        $monthlyProfit = $this->getMonthlyProfit();
        $monthlyRecurringRevenue = $this->getMRR();
        $lifetimeProfit = $this->getLifetimeProfit();
        echo '==============================================' . PHP_EOL .
            "Monthly Profit:    {$monthlyProfit->toString()}" . PHP_EOL .
            "Lifetime Profit:   {$lifetimeProfit->toString()}" . PHP_EOL .
            "MRR:               {$monthlyRecurringRevenue->toString()}" . PHP_EOL .
            '==============================================' . PHP_EOL;
    }

    public function printBillables(): void
    {
        foreach ($this->billables as $billable) {
            $product = $billable->getProduct();
            echo '==============================================' . PHP_EOL .
                "Name:  {$product->getName()}" . PHP_EOL .
                "Price: {$product->getBasePrice()->toString()}" . PHP_EOL .
            '==============================================' . PHP_EOL;
        }
    }
}

$products = [
    new Product(new Decimal('10.00'), new Decimal('0.4'), 'Cloud Level 1'),
    new Product(new Decimal('20.00'), new Decimal('0.3'), 'Cloud Level 2'),
    new Product(new Decimal('30.00'), new Decimal('0.2'), 'Cloud Level 3'),
    new Product(new Decimal('40.00'), new Decimal('0.1'), 'Cloud Level 4'),
];


$account = new Account();

$createdAt = null;
for ($i = 1; $i <= 4; $i++) {
    $product = $products[array_rand($products)];
    $account->addBillable(new Billable(product: $product, lastDueDate: null, createdAt: $createdAt));
}

$account->printBillables();
$account->printProfit();

//$authCode = 'c3868qt44uarwp0oqdydvihrva1356';
//$grantType = 'authorization_code';
//
//$twitchController = new TwitchController();
//$twitchController->setTwitchAuth($authCode, $grantType);
//dump($twitchController->getTwitchUser());

//dump(Site::findOneBy(['host' => 'streamcms.dev']));
//
//exit;
//
//$code = 'm9ehftxd35osiqjvbr4hr33u8xx4a1';
//$scope = 'user_read';
//
//$twitchController = new TwitchController();
//$twitchController->setTwitchAuth($code, 'authorization_code');
//$account = (new TwitchAccountProvider($twitchController->getTwitchUser()))->getAccount();
//
//$site = Site::findOneBy(['host' => 'streamcms.dev']);
//$token = (new TwitchController($account, $site))->getRefreshToken();
//dump($token);

//$account = Account::getOneBy(['email' => 'sheriffoftiltover@hotmail.com']);
//dump('Wtf ' . IdentityRefreshToken::create($account));