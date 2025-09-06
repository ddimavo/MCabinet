<?php

namespace MCabinet\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class SkinPreviewController extends AbstractController
{
    public function actionIndex(ParameterBag $params)
    {
        $filename = $this->filter('filename', 'str');
        $type = $this->filter('type', 'str', 'full');
        $size = $this->filter('size', 'uint', 150);

        if (!$filename) {
            return $this->returnDefaultPreview($type, $size);
        }

        $previewGenerator = $this->service('MCabinet:SkinPreviewGenerator');
        $cacheFilename = $previewGenerator->savePreviewToCache($filename, $type, $size);

        if (!$cacheFilename) {
            return $this->returnDefaultPreview($type, $size);
        }

        $cachePath = $this->getCachePath($cacheFilename);

        header('Content-Type: image/png');
        header('Content-Length: ' . filesize($cachePath));
        header('Cache-Control: public, max-age=604800');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 604800) . ' GMT');
        
        readfile($cachePath);
        exit;
    }

    protected function returnDefaultPreview($type, $size)
    {
        $defaultSkin = \XF::getRootDirectory() . '/styles/default/mc-skins/steve.png';
        
        $previewGenerator = $this->service('MCabinet:SkinPreviewGenerator');
        
        if ($type === 'head') {
            $tempFile = $previewGenerator->generateHeadPreview($defaultSkin, $size);
        } else {
            $tempFile = $previewGenerator->generateFrontPreview($defaultSkin, $size);
        }

        if ($tempFile) {
            header('Content-Type: image/png');
            readfile($tempFile);
            unlink($tempFile);
        } else {
            $image = imagecreatetruecolor($size, $type === 'head' ? $size : $size);
            $gray = imagecolorallocate($image, 200, 200, 200);
            imagefill($image, 0, 0, $gray);
            imagepng($image);
            imagedestroy($image);
        }
        exit;
    }

    protected function getCachePath($filename)
    {
        $cacheDir = \XF::getRootDirectory() . '/internal-data/mc-skins-cache';
        return $cacheDir . '/' . $filename;
    }
}