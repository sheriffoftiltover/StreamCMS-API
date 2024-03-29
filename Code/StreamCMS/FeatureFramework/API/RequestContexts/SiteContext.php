<?php

declare(strict_types=1);

namespace StreamCMS\Core\API\RequestContexts;

use Logging\LogUtil;
use StreamCMS\Site\Models\Site;

/**
 * Class SiteContext
 * @package StreamCMS\Core\API\RequestContexts
 * This holds the site context so we can know in our request what site this is in reference to.
 */
class SiteContext
{
    protected Site|null $site;
    protected string|null $domain;

    public function __construct()
    {
        $this->site = null;
        $this->domain = null;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?string $domain): void
    {
        $this->domain = $domain;
        if ($domain !== null) {
            LogUtil::debug("Domain: {$domain}");
            $this->site = Site::findOneBy(['host' => $domain]);
            LogUtil::debug("SiteContext::\$site: {$this->site?->getHost()}");
        }
    }
}
