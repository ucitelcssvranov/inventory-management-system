<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class QrCodeService
{
    /**
     * Generates QR code for an asset
     */
    public function generateQrCode(Asset $asset): ?string
    {
        if (!$this->isAutoGenerationEnabled()) {
            return null;
        }

        try {
            $url = $this->buildAssetUrl($asset);
            $qrCodeContent = $this->generateQrCodeImage($url);
            $filename = $this->saveQrCode($asset, $qrCodeContent);
            
            Log::info("QR kód vygenerovaný pre asset {$asset->id}: {$filename}");
            return $filename;
            
        } catch (\Exception $e) {
            Log::error("Chyba pri generovaní QR kódu pre asset {$asset->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generates QR codes for multiple assets
     */
    public function generateQrCodes(array $assetIds): array
    {
        $results = [];
        $batchSize = $this->getBatchSize();
        
        // Process in batches to avoid memory issues
        $chunks = array_chunk($assetIds, $batchSize);
        
        foreach ($chunks as $chunk) {
            $assets = Asset::whereIn('id', $chunk)->get();
            
            foreach ($assets as $asset) {
                $filename = $this->generateQrCode($asset);
                $results[$asset->id] = [
                    'success' => $filename !== null,
                    'filename' => $filename,
                    'asset' => $asset
                ];
            }
        }
        
        return $results;
    }

    /**
     * Generate QR code image content using external API
     */
    protected function generateQrCodeImage(string $content): string
    {
        $size = $this->getQrCodeSize();
        $errorCorrection = $this->getErrorCorrectionLevel();
        $margin = $this->getMargin();
        $format = $this->getFormat();

        // QR Server API supports only PNG and GIF, not SVG
        $apiFormat = ($format === 'svg') ? 'png' : $format;
        
        // Use QR Server API as fallback (free service)
        $url = "https://api.qrserver.com/v1/create-qr-code/";
        
        try {
            $response = Http::timeout(10)->get($url, [
                'size' => "{$size}x{$size}",
                'data' => $content,
                'format' => $apiFormat,
                'ecc' => strtoupper($errorCorrection),
                'margin' => $margin,
                'color' => str_replace('#', '', $this->getForegroundColor()),
                'bgcolor' => str_replace('#', '', $this->getBackgroundColor())
            ]);

            if ($response->successful()) {
                // If we requested SVG but got PNG, convert to SVG
                if ($format === 'svg' && $apiFormat === 'png') {
                    return $this->convertPngToSvg($response->body(), $content);
                }
                return $response->body();
            }
            
            throw new \Exception('QR API request failed: ' . $response->status());
            
        } catch (\Exception $e) {
            // Fallback to simple SVG QR code if API fails
            return $this->generateSimpleSvgQrCode($content);
        }
    }

    /**
     * Convert PNG to SVG by embedding it
     */
    protected function convertPngToSvg(string $pngData, string $content): string
    {
        $size = $this->getQrCodeSize();
        $base64 = base64_encode($pngData);
        $text = htmlspecialchars($content);
        
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<svg width=\"{$size}\" height=\"{$size}\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\">
    <image x=\"0\" y=\"0\" width=\"{$size}\" height=\"{$size}\" xlink:href=\"data:image/png;base64,{$base64}\"/>
</svg>";
    }

    /**
     * Generate simple SVG QR code as fallback
     */
    protected function generateSimpleSvgQrCode(string $content): string
    {
        // Very basic SVG QR code placeholder
        $size = $this->getQrCodeSize();
        $text = htmlspecialchars($content);
        
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<svg width=\"{$size}\" height=\"{$size}\" xmlns=\"http://www.w3.org/2000/svg\">
    <rect width=\"{$size}\" height=\"{$size}\" fill=\"white\" stroke=\"black\" stroke-width=\"2\"/>
    <text x=\"50%\" y=\"50%\" text-anchor=\"middle\" dominant-baseline=\"middle\" font-family=\"Arial\" font-size=\"12\">
        QR: {$text}
    </text>
</svg>";
    }

    /**
     * Save QR code to storage
     */
    protected function saveQrCode(Asset $asset, string $content): string
    {
        $storagePath = $this->getStoragePath();
        $format = $this->getFormat();
        $filename = "qr-{$asset->inventory_number}.{$format}";
        $fullPath = "{$storagePath}/{$filename}";

        // Ensure directory exists
        Storage::disk('public')->makeDirectory($storagePath);
        
        // Save the QR code
        Storage::disk('public')->put($fullPath, $content);
        
        return $filename;
    }

    /**
     * Build asset URL for QR code
     */
    protected function buildAssetUrl(Asset $asset): string
    {
        $baseUrl = $this->getBaseUrl();
        return "{$baseUrl}/assets/{$asset->id}/scan";
    }

    /**
     * Get QR code for asset (returns existing or generates new)
     */
    public function getQrCodeForAsset(Asset $asset): ?string
    {
        $storagePath = $this->getStoragePath();
        $format = $this->getFormat();
        $filename = "qr-{$asset->inventory_number}.{$format}";
        $fullPath = "{$storagePath}/{$filename}";

        // Check if QR code already exists
        if (Storage::disk('public')->exists($fullPath)) {
            return $filename;
        }

        // Generate new QR code
        return $this->generateQrCode($asset);
    }

    /**
     * Delete QR code for asset
     */
    public function deleteQrCode(Asset $asset): bool
    {
        $storagePath = $this->getStoragePath();
        $format = $this->getFormat();
        $filename = "qr-{$asset->inventory_number}.{$format}";
        $fullPath = "{$storagePath}/{$filename}";

        if (Storage::disk('public')->exists($fullPath)) {
            return Storage::disk('public')->delete($fullPath);
        }

        return true;
    }

    /**
     * Get QR code URL for frontend
     */
    public function getQrCodeUrl(Asset $asset): ?string
    {
        $filename = $this->getQrCodeForAsset($asset);
        
        if (!$filename) {
            return null;
        }

        $storagePath = $this->getStoragePath();
        return Storage::url("{$storagePath}/{$filename}");
    }

    /**
     * Generate print view for QR codes
     */
    public function generatePrintView(array $assetIds): array
    {
        $assets = Asset::whereIn('id', $assetIds)->get();
        $qrCodes = [];

        foreach ($assets as $asset) {
            $qrCodeUrl = $this->getQrCodeUrl($asset);
            
            $qrCodes[] = [
                'asset' => $asset,
                'qr_code_url' => $qrCodeUrl,
                'inventory_number' => $asset->inventory_number,
                'name' => $asset->name,
                'show_text' => $this->shouldIncludeText()
            ];
        }

        return $qrCodes;
    }

    // Configuration getters

    protected function isAutoGenerationEnabled(): bool
    {
        return SystemSetting::get('qr_code_auto_generate', true);
    }

    protected function getQrCodeSize(): int
    {
        return (int) SystemSetting::get('qr_code_size', 200);
    }

    protected function getFormat(): string
    {
        return SystemSetting::get('qr_code_format', 'png');
    }

    protected function getErrorCorrectionLevel(): string
    {
        return SystemSetting::get('qr_code_error_correction', 'M');
    }

    protected function getForegroundColor(): string
    {
        return SystemSetting::get('qr_code_foreground_color', '#000000');
    }

    protected function getBackgroundColor(): string
    {
        return SystemSetting::get('qr_code_background_color', '#FFFFFF');
    }

    protected function getMargin(): int
    {
        return (int) SystemSetting::get('qr_code_margin', 4);
    }

    protected function getBaseUrl(): string
    {
        return rtrim(SystemSetting::get('qr_code_base_url', config('app.url')), '/');
    }

    protected function getBatchSize(): int
    {
        return (int) SystemSetting::get('qr_code_batch_size', 50);
    }

    protected function getStoragePath(): string
    {
        return SystemSetting::get('qr_code_storage_path', 'qr-codes');
    }

    protected function shouldIncludeText(): bool
    {
        return SystemSetting::get('qr_code_include_text', true);
    }

    protected function shouldAutoPrint(): bool
    {
        return SystemSetting::get('qr_code_auto_print', false);
    }

    protected function getPrintTemplate(): string
    {
        return SystemSetting::get('qr_code_print_template', 'standard');
    }

    /**
     * Convert hex color to RGB array
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) == 6) {
            return [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2))
            ];
        }
        
        // Default to black if invalid
        return [0, 0, 0];
    }

    /**
     * Get QR code statistics
     */
    public function getStatistics(): array
    {
        $storagePath = $this->getStoragePath();
        $allFiles = Storage::disk('public')->files($storagePath);
        $qrFiles = array_filter($allFiles, function($file) {
            return preg_match('/qr-.*\.(png|jpg|svg)$/', basename($file));
        });

        $totalAssets = Asset::count();
        $qrCodeCount = count($qrFiles);

        return [
            'total_assets' => $totalAssets,
            'qr_codes_generated' => $qrCodeCount,
            'coverage_percentage' => $totalAssets > 0 ? round(($qrCodeCount / $totalAssets) * 100, 2) : 0,
            'storage_path' => $storagePath,
            'auto_generation_enabled' => $this->isAutoGenerationEnabled()
        ];
    }
}