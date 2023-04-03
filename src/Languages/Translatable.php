<?php

namespace Altino\Languages;

$lang = unserialize(file_get_contents('config.ini'))['language'];
$data = json_decode(file_get_contents('lang/'.$lang.'.json'), true);

function addTranslations($lang,$translations, $parent_key = '') {
    foreach ($translations as $key => $value) {
        $current_key = $parent_key ? $parent_key . '.' . $key : $key;
        if (is_array($value)) {
            addTranslations($lang,$value, $current_key);
        } else {
            Translatable::addOrCreate($current_key, $lang, $value);
        }
    }
}

addTranslations($lang,$data);

class Translatable {

    private static array $instances = [];

    private array $map;

    public function __construct(private $key, private $translation, $lang) {
        self::$instances[$key] = $this;
        $this->map[$lang] = $translation;
    }

    public static function addOrCreate($key,$lang,$translation) {
        if(!isset(self::$instances[$key])){
            new Translatable($key, $lang,$translation);
        }
        return self::$instances[$key]->map[$lang] = $translation;
    }


    public static function getTranslation(string $key) :string {
        return self::$instances[$key]->map[unserialize(file_get_contents('config.ini'))['language']];
    }

}


