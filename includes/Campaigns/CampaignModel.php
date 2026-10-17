<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Campaigns;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Campaign data model representing a popup campaign configuration.
 */
final class CampaignModel
{
    private int $id = 0;
    private string $title = '';
    private string $content = '';
    private string $status = 'draft'; // Post status (publish, draft, etc.)
    private bool $enabled = true;      // Logical switch for active/inactive

    private array $trigger = [
        'type'             => 'time_delay',
        'time_delay_ms'    => 3000,
        'scroll_percentage'=> 50,
        'click_selector'   => '',
        'inactivity_seconds' => 15,
        'page_views_count' => 3,
    ];

    private array $targeting = [
        'mode'       => 'all', // 'all', 'specific', 'exclude'
        'page_ids'   => [],
        'post_types' => ['page', 'post'],
        'auth_state' => 'all', // 'all', 'logged_in', 'logged_out'
        'devices'    => ['desktop', 'tablet', 'mobile'],
        'groups'     => [],    // For advanced/Pro conditions
    ];

    private array $frequency = [
        'rule'                   => 'every_visit', // 'every_visit', 'once_per_session', 'once_per_days'
        'days'                   => 7,
        'suppress_on_conversion' => true,
        'max_impressions'        => 0,
    ];

    private array $appearance = [
        'width'                   => '580px',
        'max_width'               => '92%',
        'padding'                 => '32px',
        'border_radius'           => '12px',
        'background_color'        => '#ffffff',
        'text_color'              => '#1e293b',
        'text_align'              => 'left',
        'overlay_color'           => 'rgba(15, 23, 42, 0.65)',
        'close_button_position'   => 'inside_top_right',
        'close_button_color'      => '#64748b',
        'close_button_aria_label' => 'Close popup',
        'vertical_align'          => 'center',
        'entrance_animation'      => 'zoom_in',
        'exit_animation'          => 'fade_out',
        'close_on_overlay_click'  => true,
        'lock_scroll'             => true,
    ];

    private array $leadSettings = [
        'close_on_submit'        => true,
        'close_delay_ms'         => 2500,
        'custom_success_message' => 'Thank you! Your submission has been received.',
        'redirect_url'           => '',
        'notification_email'     => '',
    ];

    private array $scheduling = [
        'enabled'    => false,
        'start_date' => '',
        'end_date'   => '',
    ];

    private array $proSettings = [];

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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = sanitize_text_field($title);
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = in_array($status, ['publish', 'draft', 'pending', 'private'], true) ? $status : 'draft';
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getTrigger(): array
    {
        return $this->trigger;
    }

    public function setTrigger(array $trigger): self
    {
        $this->trigger = array_merge($this->trigger, $trigger);
        return $this;
    }

    public function getTargeting(): array
    {
        return $this->targeting;
    }

    public function setTargeting(array $targeting): self
    {
        $this->targeting = array_merge($this->targeting, $targeting);
        return $this;
    }

    public function getFrequency(): array
    {
        return $this->frequency;
    }

    public function setFrequency(array $frequency): self
    {
        $this->frequency = array_merge($this->frequency, $frequency);
        return $this;
    }

    public function getAppearance(): array
    {
        return $this->appearance;
    }

    public function setAppearance(array $appearance): self
    {
        $this->appearance = array_merge($this->appearance, $appearance);
        return $this;
    }

    public function getLeadSettings(): array
    {
        return $this->leadSettings;
    }

    public function setLeadSettings(array $leadSettings): self
    {
        $this->leadSettings = array_merge($this->leadSettings, $leadSettings);
        return $this;
    }

    public function getScheduling(): array
    {
        return $this->scheduling;
    }

    public function setScheduling(array $scheduling): self
    {
        $this->scheduling = array_merge($this->scheduling, $scheduling);
        return $this;
    }

    public function getProSettings(): array
    {
        return $this->proSettings;
    }

    public function setProSettings(array $proSettings): self
    {
        $this->proSettings = $proSettings;
        return $this;
    }

    /**
     * Convert campaign settings into an array for meta persistence.
     */
    public function toConfigArray(): array
    {
        return [
            'enabled'       => $this->enabled,
            'trigger'       => $this->trigger,
            'targeting'     => $this->targeting,
            'frequency'     => $this->frequency,
            'appearance'    => $this->appearance,
            'lead_settings' => $this->leadSettings,
            'scheduling'    => $this->scheduling,
            'pro_settings'  => $this->proSettings,
        ];
    }

    /**
     * Hydrate campaign model from stored config array.
     */
    public function loadFromConfigArray(array $config): self
    {
        if (isset($config['enabled'])) {
            $this->enabled = (bool) $config['enabled'];
        }
        if (isset($config['trigger']) && is_array($config['trigger'])) {
            $this->setTrigger($config['trigger']);
        }
        if (isset($config['targeting']) && is_array($config['targeting'])) {
            $this->setTargeting($config['targeting']);
        }
        if (isset($config['frequency']) && is_array($config['frequency'])) {
            $this->setFrequency($config['frequency']);
        }
        if (isset($config['appearance']) && is_array($config['appearance'])) {
            $this->setAppearance($config['appearance']);
        }
        if (isset($config['lead_settings']) && is_array($config['lead_settings'])) {
            $this->setLeadSettings($config['lead_settings']);
        }
        if (isset($config['scheduling']) && is_array($config['scheduling'])) {
            $this->setScheduling($config['scheduling']);
        }
        if (isset($config['pro_settings']) && is_array($config['pro_settings'])) {
            $this->setProSettings($config['pro_settings']);
        }

        return $this;
    }
}
