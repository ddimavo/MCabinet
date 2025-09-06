<?php

namespace MCabinet\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

class SkinHistoryController extends AbstractController
{
    public function actionIndex(ParameterBag $params)
    {
        $userId = $this->filter('user_id', 'uint');
        $page = $this->filterPage($params->page);
        $perPage = 24;
        
        if ($userId && $userId != \XF::visitor()->user_id) {
            if (!\XF::visitor()->hasPermission('mcabinet', 'viewSkinHistory')) {
                return $this->noPermission();
            }
        }
        
        $targetUserId = $userId ?: \XF::visitor()->user_id;
        $targetUser = $this->em()->find('XF:User', $targetUserId);
        
        if (!$targetUser) {
            return $this->error('Пользователь не найден');
        }
        
        $skinRepo = $this->getSkinRepo();
        $skins = $skinRepo->getUserSkinHistory($targetUserId, $page, $perPage);
        $totalSkins = $skinRepo->getUserSkinHistoryCount($targetUserId);
        $activeSkin = $skinRepo->getActiveSkin($targetUserId);
        
        $viewParams = [
            'skins' => $skins,
            'activeSkin' => $activeSkin,
            'targetUser' => $targetUser,
            'page' => $page,
            'perPage' => $perPage,
            'totalSkins' => $totalSkins,
            'isOwnHistory' => $targetUserId == \XF::visitor()->user_id,
            'canViewOthers' => \XF::visitor()->hasPermission('mcabinet', 'viewSkinHistory')
        ];
        
        return $this->view('MCabinet:SkinHistory\Index', 'mcabinet_history', $viewParams);
    }
    
    public function actionApplyFromHistory(ParameterBag $params)
    {
        $this->assertPostOnly();
        
        $skin = $this->assertSkinExists($params->skin_id);
        
        if ($skin->user_id != \XF::visitor()->user_id) {
            return $this->noPermission();
        }
        
        // Добавляем текущий активный скин в историю
        $activeSkin = $this->getSkinRepo()->getActiveSkin(\XF::visitor()->user_id);
        if ($activeSkin && $activeSkin->skin_id != $skin->skin_id) {
            $activeSkin->is_active = 0;
            $activeSkin->save();
        }
        
        $skin->is_active = 1;
        $skin->save();
        
        $this->getSkinRepo()->deactivateOtherSkins(
            \XF::visitor()->user_id, 
            $skin->skin_id
        );
        
        return $this->message('Скин успешно восстановлен!');
    }
    
    protected function getSkinRepo()
    {
        return $this->repository('MCabinet:SkinRepository');
    }
    
    protected function assertSkinExists($id)
    {
        $skin = $this->getSkinRepo()->getSkinById($id);
        if (!$skin) {
            throw $this->exception($this->notFound('Скин не найден'));
        }
        return $skin;
    }
}