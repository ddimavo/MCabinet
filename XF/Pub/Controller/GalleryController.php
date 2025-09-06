<?php

namespace MCabinet\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

class GalleryController extends AbstractController
{
    public function actionIndex(ParameterBag $params)
    {
        $page = $this->filterPage($params->page);
        $perPage = 24;
        
        $view = $this->view('MCabinet:Gallery\Index', 'mcabinet_gallery', [
            'page' => $page,
            'perPage' => $perPage,
            'totalSkins' => $this->getSkinRepo()->getCatalogSkinsCount(),
            'catalogSkins' => $this->getSkinRepo()->getCatalogSkins($page, $perPage),
            'mySkins' => $this->getSkinRepo()->findSkinsForUser(\XF::visitor()->user_id)->fetch(),
            'canUploadToCatalog' => \XF::visitor()->hasPermission('mcabinet', 'uploadToCatalog')
        ]);
        
        return $view;
    }
    
    public function actionAddToCatalog(ParameterBag $params)
    {
        $skin = $this->assertSkinExists($params->skin_id);
        
        if (!$skin->canEdit()) {
            return $this->noPermission();
        }
        
        if (!$this->canUploadToCatalog()) {
            return $this->noPermission();
        }
        
        if ($this->isPost()) {
            $skin->is_in_catalog = true;
            $skin->catalog_approved = \XF::visitor()->hasPermission('mcabinet', 'moderateCatalog') ? 1 : 0;
            $skin->save();
            
            return $this->redirect($this->buildLink('mcabinet/gallery'), 'Skin added to catalog successfully.');
        }
        
        return $this->view('MCabinet:Gallery\AddToCatalog', 'mcabinet_gallery_add');
    }

    public function actionApplySkin(ParameterBag $params)
    {
        $this->assertPostOnly();
        
        $skin = $this->assertSkinExists($params->skin_id);
        
        if (!$skin->canUse()) {
            return $this->noPermission();
        }
        
        // Добавляем текущий активный скин в историю
        $activeSkin = $this->getSkinRepo()->getActiveSkin(\XF::visitor()->user_id);
        if ($activeSkin && $activeSkin->skin_id != $skin->skin_id) {
            $activeSkin->is_active = 0;
            $activeSkin->save();
        }
        
        // Устанавливаем новый скин
        $skin->is_active = 1;
        $skin->save();
        
        $this->getSkinRepo()->deactivateOtherSkins(
            \XF::visitor()->user_id, 
            $skin->skin_id
        );
        
        if ($skin->uuid) {
            $user = \XF::visitor();
            $user->mc_uuid = $skin->uuid;
            $user->save();
        }
        
        return $this->message('Скин успешно установлен!');
    }
    
    public function actionReport(ParameterBag $params)
    {
        $skin = $this->assertSkinExists($params->skin_id);
        
        if ($this->isPost()) {
            $reason = $this->filter('reason', 'str');
            $comments = $this->filter('comments', 'str');
            
            $this->createReport($skin, $reason, $comments);
            
            return $this->message('Жалоба отправлена администрации. Спасибо!');
        }
        
        return $this->view('MCabinet:Gallery\Report', 'mcabinet_gallery_report', [
            'skin' => $skin
        ]);
    }
    
    protected function canUploadToCatalog()
    {
        return \XF::visitor()->hasPermission('mcabinet', 'uploadToCatalog');
    }
    
    protected function getSkinRepo()
    {
        return $this->repository('MCabinet:SkinRepository');
    }
    
    protected function assertSkinExists($id)
    {
        $skin = $this->getSkinRepo()->getSkinById($id);
        if (!$skin) {
            throw $this->exception($this->notFound('Requested skin not found.'));
        }
        return $skin;
    }

    private function createReport($skin, $reason, $comments)
    {
        /** @var \XF\Repository\Report $reportRepo */
        $reportRepo = $this->repository('XF:Report');
        
        $report = $reportRepo->getReportHandler('mc_skin');
        
        if (!$report) {
            return false;
        }
        
        $contentUrl = $this->buildLink('canonical:mcabinet/gallery');
        
        return $reportRepo->insertReport(
            'mc_skin', 
            $skin->skin_id, 
            $reason, 
            $comments, 
            $contentUrl
        );
    }
}