<?php

namespace ddimavo/MCabinet\XF\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class UserSkin extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_mc_user_skins';
        $structure->shortName = 'ddimavo/MCabinet:UserSkin';
        $structure->primaryKey = 'skin_id';
        $structure->columns = [
            'skin_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'user_id' => ['type' => self::UINT, 'required' => true],
            'uuid' => ['type' => self::STR, 'maxLength' => 36, 'default' => ''],
            'skin_name' => ['type' => self::STR, 'maxLength' => 255, 'required' => true],
            'skin_texture' => ['type' => self::STR, 'maxLength' => 255, 'default' => ''],
            'cape_texture' => ['type' => self::STR, 'maxLength' => 255, 'default' => ''],
            'is_hd_skin' => ['type' => self::BOOL, 'default' => false],
            'is_hd_cape' => ['type' => self::BOOL, 'default' => false],
            'skin_size' => ['type' => self::STR, 'maxLength' => 20, 'default' => '64x64'],
            'cape_size' => ['type' => self::STR, 'maxLength' => 20, 'default' => '64x32'],
            'is_active' => ['type' => self::BOOL, 'default' => false],
            'is_public' => ['type' => self::BOOL, 'default' => false],
            'is_in_catalog' => ['type' => self::BOOL, 'default' => false],
            'catalog_approved' => ['type' => self::BOOL, 'default' => false],
            'view_count' => ['type' => self::UINT, 'default' => 0],
            'like_count' => ['type' => self::UINT, 'default' => 0],
            'download_count' => ['type' => self::UINT, 'default' => 0],
            'upload_date' => ['type' => self::UINT, 'default' => \XF::$time]
        ];
        $structure->relations = [
            'User' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => 'user_id',
                'primary' => true
            ]
        ];
        return $structure;
    }

    public function canUse($user = null)
    {
        if ($user === null) {
            $user = \XF::visitor();
        }
        
        if ($this->is_in_catalog && !$this->catalog_approved) {
            return false;
        }
        
        if ($this->is_in_catalog && $this->catalog_approved) {
            return true;
        }
        
        return $this->user_id == $user->user_id;
    }

    public function getPreviewUrl()
    {
        $repo = \XF::repository('ddimavo/MCabinet:SkinRepository');
        return $repo->getSkinPreviewUrl($this);
    }
}