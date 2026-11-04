<?php

namespace App\Helpers;

class LogoHelper
{
    /**
     * Get logo URL
     */
    public static function getLogoUrl($variant = 'default'): string
    {
        $logos = [
            'default' => 'img/logo.png',
            'white' => 'img/logo-white.png',
            'small' => 'img/logo-small.png',
            'header' => 'img/logo-header.png',
            'favicon' => 'img/favicon.ico',
        ];

        $path = $logos[$variant] ?? $logos['default'];

        if (file_exists(public_path($path))) {
            return asset($path);
        }

        // Fallback to default if variant doesn't exist
        if ($variant !== 'default' && file_exists(public_path($logos['default']))) {
            return asset($logos['default']);
        }

        return '';
    }

    /**
     * Get logo as base64 (for PDFs)
     */
    public static function getLogoBase64($variant = 'default'): string
    {
        $path = public_path(self::getLogoPath($variant));

        if (!file_exists($path)) {
            return '';
        }

        $type = pathinfo($path, PATHINFO_EXTENSION);
        return 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path));
    }

    /**
     * Get logo file path
     */
    public static function getLogoPath($variant = 'default'): string
    {
        $logos = [
            'default' => 'img/logo.png',
            'white' => 'img/logo-white.png',
            'small' => 'img/logo-small.png',
            'header' => 'img/logo-header.png',
            'favicon' => 'img/favicon.ico',
        ];

        return $logos[$variant] ?? $logos['default'];
    }
}
