<?php

namespace ddimavo/MCabinet\XF\Listener;

class UserEntity
{
    public static function extendUserEntity($class, array &$extend)
    {
        $extend[] = 'ddimavo/MCabinet\XF\Entity\User';
    }
}