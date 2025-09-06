<?php

namespace ddimavo/MCabinet\XF\Repository;

use XF\Mvc\Entity\Repository;

class SkinRepository extends Repository
{
    public function findSkinsForUser($userId)
    {
        return $this->finder('ddimavo/MCabinet:UserSkin')
            ->where('user_id', $userId)
            ->order('upload_date', 'DESC');
    }

    public function getSkinByUuid($uuid)
    {
        return $this->finder('ddimavo/MCabinet:UserSkin')
            ->where('uuid', $uuid)
            ->where('is_active', 1)
            ->fetchOne();
    }

    public function getSkinById($id)
    {
        return $this->finder('ddimavo/MCabinet:UserSkin')
            ->where('skin_id', $id)
            ->fetchOne();
    }

    public function getCatalogSkins($page = 1, $perPage = 24)
    {
        return $this->finder('ddimavo/MCabinet:UserSkin')
            ->where('is_in_catalog', 1)
            ->where('catalog_approved', 1)
            ->order('upload_date', 'DESC')
            ->limitByPage($page, $perPage)
            ->fetch();
    }

    public function getCatalogSkinsCount()
    {
        return $this->finder('ddimavo/MCabinet:UserSkin')
            ->where('is_in_catalog', 1)
            ->where('catalog_approved', 1)
            ->total();
    }

    public function getUserSkinHistory($userId, $page = 1, $perPage = 24)
    {
        return $this->finder('ddimavo/MCabinet:UserSkin')
            ->where('user_id', $userId)
            ->where('is_active', 0)
            ->order('upload_date', 'DESC')
            ->limitByPage($page, $perPage)
            ->fetch();
    }

    public function getUserSkinHistoryCount($userId)
    {
        return $this->finder('ddimavo/MCabinet:UserSkin')
            ->where('user_id', $userId)
            ->where('is_active', 0)
            ->total();
    }

    public function getActiveSkin($userId)
    {
        return $this->finder('ddimavo/MCabinet:UserSkin')
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->fetchOne();
    }

    public function deactivateOtherSkins($userId, $currentSkinId)
    {
        return $this->db()->update(
            'xf_mc_user_skins',
            ['is_active' => 0],
            'user_id = ? AND skin_id != ? AND is_active = 1',
            [$userId, $currentSkinId]
        );
    }

    public function getSkinPreviewUrl($skin, $type = 'full', $size = 150)
    {
        if (!$skin->skin_texture) {
            return $this->getDefaultPreviewUrl($type, $size);
        }

        try {
            $previewGenerator = \XF::service('ddimavo/MCabinet:SkinPreviewGenerator');
            $cacheFilename = $previewGenerator->savePreviewToCache($skin->skin_texture, $type, $size);
            
            if ($cacheFilename) {
                return $this->app->options()->boardUrl . '/internal-data/mc-skins-cache/' . $cacheFilename;
            }
        } catch (\Exception $e) {
            \XF::logError('Preview generation failed: ' . $e->getMessage());
        }

        return $this->getDefaultPreviewUrl($type, $size);
    }

    protected function getDefaultPreviewUrl($type, $size)
    {
        return $this->app->options()->boardUrl . '/styles/default/mc-skins/steve_' . $type . '.png';
    }

    public function canUseSkin($skin, $user = null)
    {
        if ($user === null) {
            $user = \XF::visitor();
        }
        
        if ($skin->is_in_catalog && !$skin->catalog_approved) {
            return false;
        }
        
        if ($skin->is_in_catalog && $skin->catalog_approved) {
            return true;
        }
        
        return $skin->user_id == $user->user_id;
    }
}