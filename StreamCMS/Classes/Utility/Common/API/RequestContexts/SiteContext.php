<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Common\API\RequestContexts;

use StreamCMS\Site\Models\Site;

/**
 * Class SiteContext
 * @package StreamCMS\Utility\Common\API\RequestContexts
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

    public function getSite(): ?string
    {
        return $this->site;
    }

    public function setSite(?string $domain): void
    {
        $this->domain = $domain;
        if ($domain !== null) {
            $this->site = Site::findOneBy(['domain' => $domain]);
        }
    }
}
