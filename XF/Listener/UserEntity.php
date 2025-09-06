<?php

namespace MCabinet\XF\Listener;

class UserEntity
{
    public static function extendUserEntity($class, array &$extend)
    {
        $extend[] = 'MCabinet\XF\Entity\User';
    }
}