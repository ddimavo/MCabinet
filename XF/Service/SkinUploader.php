<?php

namespace MCabinet\XF\Service;

use XF\Service\AbstractService;
use XF\Util\File;
use XF\Http\Upload;

class SkinUploader extends AbstractService
{
    protected $file;
    protected $type;
    protected $userId;
    protected $isHdAllowed = false;

    const TYPE_SKIN = 'skin';
    const TYPE_CAPE = 'cape';

    const MAX_STANDARD_SIZE = 64;
    const MAX_HD_SIZE = 1024;

    public function __construct(\XF\App $app, Upload $file, $type, $userId)
    {
        parent::__construct($app);
        $this->file = $file;
        $this->type = $type;
        $this->userId = $userId;
        $this->isHdAllowed = $this->hasHdPermission();
    }

    public function upload()
    {
        if (!$this->validateFile()) {
            throw new \XF\PrintableException('Invalid file format or size.');
        }

        list($width, $height) = getimagesize($this->file->getTempFile());
        $size = "{$width}x{$height}";

        if ($this->isHdSize($width, $height) && !$this->isHdAllowed) {
            throw new \XF\PrintableException('You do not have permission to upload HD textures. Please use standard size 64x64.');
        }

        $fileName = $this->generateFileName();
        $filePath = $this->getFilePath($fileName);

        $this->file->moveFile($filePath);

        return [
            'file_name' => $fileName,
            'is_hd' => $this->isHdSize($width, $height),
            'size' => $size
        ];
    }

    protected function validateFile()
    {
        if ($this->file->getExtension() !== 'png') {
            return false;
        }

        if ($this->file->getFileSize() > ($this->isHdAllowed ? 2048 * 1024 : 512 * 1024)) {
            return false;
        }

        return true;
    }

    protected function isHdSize($width, $height)
    {
        $maxStandard = self::MAX_STANDARD_SIZE;
        return $width > $maxStandard || $height > $maxStandard;
    }

    protected function hasHdPermission()
    {
        $visitor = \XF::visitor();
        return $visitor->hasPermission('mcabinet', 'uploadHd');
    }

    protected function generateFileName()
    {
        return sprintf('%s_%s_%d.png', 
            $this->type, 
            uniqid(), 
            $this->userId
        );
    }

    protected function getFilePath($fileName)
    {
        $basePath = $this->app->options()->mcabinetUploadPath;
        return sprintf('%s/%s', rtrim($basePath, '/'), $fileName);
    }
}