<?php

namespace ddimavo/MCabinet\XF\Entity;

class User extends XFCP_User
{
    public static function getStructure(\XF\Mvc\Entity\Structure $structure)
    {
        $structure = parent::getStructure($structure);
        $structure->columns['mc_uuid'] = ['type' => \XF\Mvc\Entity\Entity::STR, 'maxLength' => 36, 'default' => ''];
        $structure->columns['mc_username'] = ['type' => \XF\Mvc\Entity\Entity::STR, 'maxLength' => 255, 'default' => ''];
        return $structure;
    }
}