<?php

namespace ddimavo/MCabinet\XF\Service;

use XF\Service\AbstractService;
use XF\Util\File;

class SkinPreviewGenerator extends AbstractService
{
    public function generateFrontPreview($skinPath, $size = 150)
    {
        if (!file_exists($skinPath) || !is_readable($skinPath)) {
            return false;
        }

        require_once \XF::getAddOnDirectory() . '/ddimavo/MCabinet/SkinViewer2D.php';

        try {
            $preview = \SkinViewer2D::createPreview($skinPath, false, 'front', $size);
            
            if (!$preview) {
                return false;
            }

            $tempFile = File::getTempFile();
            imagepng($preview, $tempFile);
            imagedestroy($preview);

            return $tempFile;

        } catch (\Exception $e) {
            \XF::logError('Skin preview generation error: ' . $e->getMessage());
            return false;
        }
    }

    public function generateHeadPreview($skinPath, $size = 100)
    {
        if (!file_exists($skinPath) || !is_readable($skinPath)) {
            return false;
        }

        require_once \XF::getAddOnDirectory() . '/ddimavo/MCabinet/SkinViewer2D.php';

        try {
            $preview = \SkinViewer2D::createHead($skinPath, $size);
            
            if (!$preview) {
                return false;
            }

            $tempFile = File::getTempFile();
            imagepng($preview, $tempFile);
            imagedestroy($preview);

            return $tempFile;

        } catch (\Exception $e) {
            \XF::logError('Head preview generation error: ' . $e->getMessage());
            return false;
        }
    }

    public function savePreviewToCache($skinFilename, $previewType = 'full', $size = 150)
    {
        $skinPath = $this->getSkinFilePath($skinFilename);
        $cacheFilename = $this->getCacheFilename($skinFilename, $previewType, $size);
        $cachePath = $this->getCachePath($cacheFilename);

        if (file_exists($cachePath) && filemtime($cachePath) > filemtime($skinPath)) {
            return $cacheFilename;
        }

        if ($previewType === 'head') {
            $tempFile = $this->generateHeadPreview($skinPath, $size);
        } else {
            $tempFile = $this->generateFrontPreview($skinPath, $size);
        }

        if (!$tempFile) {
            return false;
        }

        File::copyFile($tempFile, $cachePath);
        unlink($tempFile);

        return $cacheFilename;
    }

    protected function getSkinFilePath($filename)
    {
        $basePath = $this->app->options()->ddimavo/MCabinetUploadPath;
        return sprintf('%s/%s', rtrim($basePath, '/'), $filename);
    }

    protected function getCachePath($filename)
    {
        $cacheDir = \XF::getRootDirectory() . '/internal-data/mc-skins-cache';
        if (!file_exists($cacheDir)) {
            File::createDirectory($cacheDir, false);
        }
        return $cacheDir . '/' . $filename;
    }

    protected function getCacheFilename($skinFilename, $previewType, $size)
    {
        $hash = md5($skinFilename . $previewType . $size);
        return "preview_{$previewType}_{$size}_{$hash}.png";
    }
}