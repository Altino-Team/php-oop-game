<?php

use Altino\Characters\Character;
use Altino\Spells\{Spell, AttackSpell, DefendSpell, HealSpell};
use Altino\Items\Item;
use Altino\Types\{Element, DamageType};
use Altino\Languages\Translatable;

function defineGameNumber($argv){
    if(in_array("--turn", $argv)){
        $turn = $argv[array_search("--turn", $argv) + 1];
        $turn = (int)$turn;
        if($turn > 0){
            if(in_array("--play", $argv)){
                define("GAME_NUMBER", 1);
            } else {
                define("GAME_NUMBER", $turn);
            }
        } else {
            define("GAME_NUMBER", 1);
        }
    } else {
        define("GAME_NUMBER", 1);
    }
}

function loadLanguages($argv){
    if (!file_exists('config.ini')) {
        $config = array('language' => 'en');
        file_put_contents('config.ini', serialize($config));
    }
    $config = unserialize(file_get_contents('config.ini'));
    if(in_array("--lang", $argv)){
        $config['language'] = $argv[array_search("--lang", $argv) + 1];
        file_put_contents('config.ini', serialize($config));
    }
}

function createCharacters() : array {
    $brand = new Character("Brand", Element::FIRE, 3000, 250,
        new Item("Baton du Vide", 10, 200, 10, 10, 10),
        100, 90, 0.20, 0.20, 100,100,280,
        new AttackSpell(700,DamageType::MAGICAL,40,1),new DefendSpell(150,50,4),new HealSpell(800,40,3));

    $nilah = new Character("Nilah", Element::WATER, 2200, 140,
        new Item("Arc-Bouclier", 150, 0, 0, 10, 10),
        90, 150, 0.4, 0.10, 120,180,30,
        new AttackSpell(450,DamageType::PHYSICAL,40,2),new DefendSpell(100,20,4),new HealSpell(500,30,4));

    $ivern = new Character("Ivern", Element::GRASS, 5000, 120,
        new Item("Warmog", 0, 0, 30, 1000, 0),
        100, 150, 0.05, 0.05, 140,150,180,
        new AttackSpell(400,DamageType::MAGICAL,30,2),new DefendSpell(200,70,5),new HealSpell(500,50,3));

    return [$brand, $nilah, $ivern];
}

function chance(float $percentage): bool
{
    return rand() % 100 < $percentage*100;
}

function calculateMultiplierByType(Character $attacker, Character $defender): float
{
    switch ($attacker->getElement()){
        case Element::FIRE:
            if($defender->getElement() == Element::WATER){
                $multiplier = 0.5;
            } elseif ($defender->getElement() == Element::GRASS){
                $multiplier = 2;
            } else {
                $multiplier = 1;
            }
            break;
        case Element::WATER:
            if($defender->getElement() == Element::GRASS){
                $multiplier = 0.5;
            } elseif ($defender->getElement() == Element::FIRE){
                $multiplier = 2;
            } else {
                $multiplier = 1;
            }
            break;
        case Element::GRASS:
            if($defender->getElement() == Element::FIRE){
                $multiplier = 0.5;
            } elseif ($defender->getElement() == Element::WATER){
                $multiplier = 2;
            } else {
                $multiplier = 1;
            }
            break;
        default:
            $multiplier = 1;
    }
    return $multiplier;
}

function progressBar($done, $total)
{
    $perc = floor(($done / $total) * 100);
    $half = round($perc/2);
    $left = 50 - $half;
    $write = sprintf("\033[0G\033[2K[%'={$half}s>%-{$left}s] - $perc%% - $done/$total", "", "");
    fwrite(STDOUT, $write);
}

function debugCharactersArray($characters)
{
    $test = $characters;
    orderByInitiative($test);
    echo PHP_EOL;
    foreach ($test as $character) {
        echoWithColor($character->toString().PHP_EOL.PHP_EOL);
    }
}

function orderByInitiative(&$characters)
{
    usort($characters, function ($a, $b) {
        return $a->getInitiative() <=> $b->getInitiative();
    });
}

function clearScreen()
{
    pclose(popen('cls','w'));
}

/**
 * Hello @Minecraft
 */
function echoWithColor(string $text)
{
    $colorMap = [
        "&0" => "\033[30m",
        "&9" => "\033[34m",
        "&a" => "\033[32m",
        "&b" => "\033[36m",
        "&c" => "\033[31m",
        "&d" => "\033[35m",
        "&e" => "\033[33m",
        "&f" => "\033[37m",
    ];

    foreach ($colorMap as $colorCode => $color) {
        $text = str_replace($colorCode, $color, $text);
    }

    echo $text."\033[0m";
}

function echoTranslation(string $key, ...$parameters){
    echoWithColor(translate($key,...$parameters));
}

function translate(string $key, ...$parameters) : string {
    $translation = Translatable::getTranslation($key);
    $translation = str_replace("%n", PHP_EOL, $translation);
    return sprintf($translation,...$parameters);
}

function displaySpell(Spell $spell){
    if($spell->getManaCost() > $spell->getOwner()->getMana()){
        $manaTranslation = translate("game.actions.not_enough_mana",$spell->getManaCost());
    } else {
        $manaTranslation = translate("game.actions.mana",$spell->getManaCost());
    }
    if($spell->getCooldownCountDown() > 0){
        $cooldownTranslation = translate("game.actions.cooldown", $spell->getCooldownCountDown());
    } else {
        $cooldownTranslation = Translatable::getTranslation("game.actions.available");
    }
    echoTranslation("game.actions.main_text",$spell->getName(),$manaTranslation,$cooldownTranslation,$spell->getDescription());
}

function validateSpellAction(Spell $spell, Character $sender) : bool {
    if($spell->getManaCost() > $sender->getMana()){
        echoTranslation("game.error.not_enough_mana");
        return false;
    }
    if($spell->getCooldownCountDown() > 0){
        echoTranslation("game.error.in_cooldown");
        return false;
    }
    return true;
}

function endWithStatistics($mapInput, $wins){
    echo PHP_EOL.PHP_EOL;
    echoTranslation("game.statistics.title");
    $statsArray = array_map(fn (int $charWins) => ($charWins / GAME_NUMBER * 100)."%", $wins);
    foreach ($statsArray as $name => $stats){
        echoTranslation("game.statistics.character", $mapInput[$name]->getColorCode(), $name, $stats);
    }
    echoTranslation("game.end");
    exit();
}


