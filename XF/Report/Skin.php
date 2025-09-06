<?php

namespace ddimavo/MCabinet\XF\Report;

use XF\Entity\Report;
use XF\Mvc\Entity\Entity;
use XF\Report\AbstractHandler;

class Skin extends AbstractHandler
{
    public function getContentTitle(Report $report)
    {
        return 'Скин: ' . $report->Content->skin_name;
    }

    public function getContentMessage(Report $report)
    {
        return $report->Content->skin_name;
    }

    public function getContentUrl(Report $report)
    {
        return $this->app->router('public')->buildLink('canonical:ddimavo/MCabinet/gallery');
    }

    public function getEntityWith()
    {
        return ['User'];
    }
}