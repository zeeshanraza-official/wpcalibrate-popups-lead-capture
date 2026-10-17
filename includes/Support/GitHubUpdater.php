<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Support;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles automated WordPress dashboard updates directly from GitHub releases.
 * Fully compatible with native WordPress update APIs and Git Updater.
 */
final class GitHubUpdater
{
    private const REPO_OWNER = 'zeeshanraza-official';
    private const REPO_NAME  = 'wpcalibrate-popups-lead-capture';
    private const TRANSIENT_KEY = 'wpcal_popups_github_release_data';
    private const CACHE_HOURS   = 12;

    public static function init(): void
    {
        add_filter('pre_set_site_transient_update_plugins', [self::class, 'checkUpdate']);
        add_filter('plugins_api', [self::class, 'pluginInformation'], 20, 3);
        add_filter('upgrader_post_install', [self::class, 'postInstall'], 10, 3);
    }

    /**
     * Injects available GitHub update into WordPress core plugin update transient.
     *
     * @param object|false $transient Transient data.
     * @return object|false
     */
    public static function checkUpdate(mixed $transient): mixed
    {
        if (empty($transient) || !is_object($transient)) {
            return $transient;
        }

        $release = self::fetchLatestRelease();
        if (!$release || empty($release['version'])) {
            return $transient;
        }

        $basename = defined('WPCAL_POPUPS_BASENAME') ? \WPCAL_POPUPS_BASENAME : 'wpcalibrate-popups-lead-capture/wpcalibrate-popups-lead-capture.php';
        $currentVersion = defined('WPCAL_POPUPS_VERSION') ? \WPCAL_POPUPS_VERSION : '1.0.0';
        $pluginUrl = defined('WPCAL_POPUPS_URL') ? \WPCAL_POPUPS_URL : '';

        if (version_compare($currentVersion, $release['version'], '<')) {
            $item = (object) [
                'id'            => 'wpcalibrate-popups-lead-capture',
                'slug'          => 'wpcalibrate-popups-lead-capture',
                'plugin'        => $basename,
                'new_version'   => $release['version'],
                'url'           => 'https://github.com/' . self::REPO_OWNER . '/' . self::REPO_NAME,
                'package'       => $release['download_url'],
                'icons'         => [
                    'default' => $pluginUrl . 'branding/icon-dark.png',
                ],
                'banners'       => [],
                'tested'        => '7.1.3',
                'requires_php'  => '8.2',
                'compatibility' => new \stdClass(),
            ];

            $transient->response[$basename] = $item;
            unset($transient->no_update[$basename]);
        } else {
            $item = (object) [
                'id'            => 'wpcalibrate-popups-lead-capture',
                'slug'          => 'wpcalibrate-popups-lead-capture',
                'plugin'        => $basename,
                'new_version'   => $currentVersion,
                'url'           => 'https://github.com/' . self::REPO_OWNER . '/' . self::REPO_NAME,
                'package'       => '',
            ];
            $transient->no_update[$basename] = $item;
        }

        return $transient;
    }

    /**
     * Provides metadata for the WordPress core "View version details" modal.
     *
     * @param mixed  $result Existing result.
     * @param string $action API action being called.
     * @param object $args   Arguments.
     * @return mixed
     */
    public static function pluginInformation(mixed $result, string $action, object $args): mixed
    {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (empty($args->slug) || $args->slug !== 'wpcalibrate-popups-lead-capture') {
            return $result;
        }

        $release = self::fetchLatestRelease();
        $version = $release['version'] ?? WPCAL_POPUPS_VERSION;
        $downloadUrl = $release['download_url'] ?? '';

        $info = new \stdClass();
        $info->name           = 'WPCalibrate Popups & Lead Capture';
        $info->slug           = 'wpcalibrate-popups-lead-capture';
        $info->version        = $version;
        $info->author         = '<a href="https://wpcalibrate.com">WPCalibrate</a>';
        $info->homepage       = 'https://github.com/' . self::REPO_OWNER . '/' . self::REPO_NAME;
        $info->requires       = '6.5';
        $info->tested         = '7.1.3';
        $info->requires_php   = '8.2';
        $info->download_link  = $downloadUrl;
        $info->trunk          = $downloadUrl;
        $info->last_updated   = $release['published_at'] ?? date('Y-m-d');
        $info->sections       = [
            'description'  => '<p>A lightweight, high-performance WordPress popup builder, lead capture system, targeting engine, visitor frequency controller, and analytics manager.</p>',
            'installation' => '<ol><li>Upload the ZIP file through WordPress Admin (Plugins → Add New → Upload Plugin).</li><li>Activate the plugin.</li><li>Navigate to WPCalibrate → Popups & Lead Capture to begin crafting campaigns.</li></ol>',
            'changelog'    => !empty($release['body']) ? nl2br(esc_html($release['body'])) : '<p>Check repository releases for full changelog.</p>',
        ];

        return $info;
    }

    /**
     * Ensures the unzipped directory name strictly matches the plugin slug after an in-dashboard update.
     *
     * @param bool  $response   Installation response.
     * @param array $hook_extra Extra hook information.
     * @param array $result     Installation result data.
     * @return array
     */
    public static function postInstall(bool $response, array $hook_extra, array $result): array
    {
        global $wp_filesystem;

        if (empty($hook_extra['plugin']) || $hook_extra['plugin'] !== WPCAL_POPUPS_BASENAME) {
            return $result;
        }

        $pluginFolder = WP_PLUGIN_DIR . '/wpcalibrate-popups-lead-capture';
        if (isset($result['destination']) && $result['destination'] !== $pluginFolder) {
            $wp_filesystem->move($result['destination'], $pluginFolder);
            $result['destination'] = $pluginFolder;
        }

        return $result;
    }

    /**
     * Queries GitHub Releases API with transient caching and timeout safeguards.
     *
     * @return array{version: string, download_url: string, published_at: string, body: string}|null
     */
    private static function fetchLatestRelease(): ?array
    {
        $cached = get_transient(self::TRANSIENT_KEY);
        if (is_array($cached) && !empty($cached['version'])) {
            return $cached;
        }

        $url = 'https://api.github.com/repos/' . self::REPO_OWNER . '/' . self::REPO_NAME . '/releases/latest';

        $response = wp_remote_get($url, [
            'timeout'    => 10,
            'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url(),
            'headers'    => [
                'Accept' => 'application/vnd.github.v3+json',
            ],
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($body) || empty($body['tag_name'])) {
            return null;
        }

        $version = ltrim((string) $body['tag_name'], 'vV');
        $downloadUrl = '';

        // Prioritize attached ZIP release asset
        if (!empty($body['assets']) && is_array($body['assets'])) {
            foreach ($body['assets'] as $asset) {
                if (!empty($asset['browser_download_url']) && str_ends_with((string) $asset['name'], '.zip')) {
                    $downloadUrl = (string) $asset['browser_download_url'];
                    break;
                }
            }
        }

        // Fallback to source zipball
        if (empty($downloadUrl) && !empty($body['zipball_url'])) {
            $downloadUrl = (string) $body['zipball_url'];
        }

        $data = [
            'version'      => $version,
            'download_url' => $downloadUrl,
            'published_at' => (string) ($body['published_at'] ?? date('Y-m-d')),
            'body'         => (string) ($body['body'] ?? ''),
        ];

        $cacheDuration = defined('HOUR_IN_SECONDS') ? (self::CACHE_HOURS * \HOUR_IN_SECONDS) : (self::CACHE_HOURS * 3600);
        set_transient(self::TRANSIENT_KEY, $data, $cacheDuration);

        return $data;
    }
}
