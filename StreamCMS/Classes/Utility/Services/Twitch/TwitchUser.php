<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Services\Twitch;

class TwitchUser
{
    public function __construct(
        private string $id,
        private string $login,
        private string $displayName,
        private string $type,
        private string $broadcasterType,
        private string $description,
        private string $profileImageUrl,
        private string $offlineImageUrl,
        private int $viewCount,
        private string $email,
        private string $createdAt,
    )
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getBroadcasterType(): string
    {
        return $this->broadcasterType;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getProfileImageUrl(): string
    {
        return $this->profileImageUrl;
    }

    public function getOfflineImageUrl(): string
    {
        return $this->offlineImageUrl;
    }

    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}
