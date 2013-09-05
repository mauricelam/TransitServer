<?php

require_once('phpfastcache/phpfastcache.php');

class Key {
    private static $keyturn;
    private static $key;

    private static $KEYS = array(
        'fe18d20a15974099a63329fd612d1702', // Maurice's key
        'e6e671f102454587b38fc538027a31db', // Ryan's key
        '930e06ead51f412098ab59679e684e45', // Transit's key
        '5107f159c03746d3ab775d907d6d0d9a', // Caelitus' key
    );
    
    public static function get() {
        if (!self::$key) {
            self::$keyturn = phpFastCache('files')->get('apikeyturn');
            if (self::$keyturn === null) self::$keyturn = rand(0, count(self::$KEYS));
            self::$key = self::$KEYS[self::$keyturn];
        }
        return self::$key;
    }
    
    public static function next() {
        $turn = ($this->keyturn + 1) % count(self::$KEYS);
        phpFastCache('files')->set('apikeyturn', $turn);
        self::$key = null;
    }
}

?>