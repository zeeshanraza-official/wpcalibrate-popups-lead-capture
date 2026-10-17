<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Leads;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Lead entity representing a captured visitor lead submission.
 */
final class LeadModel
{
    private int $id = 0;
    private string $uuid = '';
    private int $popupId = 0;
    private string $createdAt = '';
    private string $updatedAt = '';
    private ?string $email = null;
    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?string $phone = null;
    private array $fields = [];
    private array $consent = [];
    private ?string $sourceUrl = null;
    private string $status = 'new';
    private string $notificationStatus = 'pending';
    private string $integrationStatus = 'none';
    private ?string $dedupeKey = null;

    public function __construct(int $id = 0)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getPopupId(): int
    {
        return $this->popupId;
    }

    public function setPopupId(int $popupId): self
    {
        $this->popupId = $popupId;
        return $this;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email !== null ? sanitize_email($email) : null;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName !== null ? sanitize_text_field($firstName) : null;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName !== null ? sanitize_text_field($lastName) : null;
        return $this;
    }

    public function getFullName(): string
    {
        $name = trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
        return $name !== '' ? $name : ($this->email ?? __('Anonymous', 'wpcalibrate-popups-lead-capture'));
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone !== null ? sanitize_text_field($phone) : null;
        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function getConsent(): array
    {
        return $this->consent;
    }

    public function setConsent(array $consent): self
    {
        $this->consent = $consent;
        return $this;
    }

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }

    public function setSourceUrl(?string $sourceUrl): self
    {
        $this->sourceUrl = $sourceUrl !== null ? esc_url_raw($sourceUrl) : null;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = in_array($status, ['new', 'contacted', 'archived'], true) ? $status : 'new';
        return $this;
    }

    public function getNotificationStatus(): string
    {
        return $this->notificationStatus;
    }

    public function setNotificationStatus(string $status): self
    {
        $this->notificationStatus = $status;
        return $this;
    }

    public function getIntegrationStatus(): string
    {
        return $this->integrationStatus;
    }

    public function setIntegrationStatus(string $status): self
    {
        $this->integrationStatus = $status;
        return $this;
    }

    public function getDedupeKey(): ?string
    {
        return $this->dedupeKey;
    }

    public function setDedupeKey(?string $dedupeKey): self
    {
        $this->dedupeKey = $dedupeKey !== null ? sanitize_text_field($dedupeKey) : null;
        return $this;
    }
}
