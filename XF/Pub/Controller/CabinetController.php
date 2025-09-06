<?php

namespace MCabinet\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

class CabinetController extends AbstractController
{
    public function actionIndex(ParameterBag $params)
    {
        if (!\XF::visitor()->user_id) {
            return $this->redirect($this->buildLink('login'));
        }

        $skinRepo = $this->getSkinRepo();
        $skins = $skinRepo->findSkinsForUser(\XF::visitor()->user_id)->fetch();

        $viewParams = [
            'skins' => $skins,
            'recentSkins' => $skinRepo->getRecentSkins(\XF::visitor()->user_id, 5),
            'totalRecent' => $skinRepo->getUserSkinHistoryCount(\XF::visitor()->user_id)
        ];

        return $this->view('MCabinet:Cabinet\Index', 'mcabinet_page', $viewParams);
    }

    protected function getSkinRepo()
    {
        return $this->repository('MCabinet:SkinRepository');
    }
}