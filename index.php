<?php

require_once('./autoload.php');
require_once('./functions.php');

use Altino\Types\{Element, DamageType};
use Altino\Characters\Character as Character;
use Altino\Items\Item as Item;
use \Altino\Spells\{AttackSpell, DefendSpell, HealSpell};


$brand = new Character("Brand", Element::FIRE, 3600, 250,
    new Item("Baton du Vide", 10, 200, 10, 10, 10),
    100, 90, 0.20, 0.20, 100,100,280,
    new AttackSpell(700,DamageType::MAGICAL,40,1),new DefendSpell(150,50,4),new HealSpell(800,40,2));
$nilah = new Character("Nilah", Element::WATER, 2200, 140,
    new Item("Arc-Bouclier", 150, 0, 0, 10, 10),
    90, 150, 0.60, 0.10, 120,180,30,
    new AttackSpell(450,DamageType::PHYSICAL,40,2),new DefendSpell(120,20,4),new HealSpell(500,30,4));
$ivern = new Character("Ivern", Element::GRASS, 5000, 120,
    new Item("Warmog", 0, 0, 30, 800, 0),
    100, 150, 0.05, 0.05, 140,150,200,
    new AttackSpell(400,DamageType::MAGICAL,30,2),new DefendSpell(200,70,5),new HealSpell(500,50,3));

$characters = [$brand, $nilah, $ivern];

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

if(in_array("--play", $argv)){
    userInGame($characters);
} elseif (in_array("--auto", $argv)) {
    autoPlayedGame($characters[0], $characters[1], $characters[2]);
} else {
    normalGame($characters[0], $characters[1], $characters[2]);
}


function autoPlayedGame($brand, $nilah, $ivern)
{
    clearScreen();
    $wins = [
        "Brand" => 0,
        "Nilah" => 0,
        "Ivern" => 0,
    ];

    $gameCount = 0;

    while ($gameCount < GAME_NUMBER) {
        $characters = [$brand, $nilah, $ivern];

        foreach ($characters as $character) {
            $character->reset();
        }
        orderByInitiative($characters);

        while (count($characters) > 1) {


            $attacker = array_pop($characters);
            echoWithColor("&eC'est au tour de {$attacker->getColorCode()}{$attacker->getName()}&e &d(&eNiveau &b{$attacker->getLevel()}&d)".PHP_EOL);

            $key = array_rand($characters);
            $target = $characters[$key];

            $attacker->turn($target);

            if ($target->isDead()) {
                unset($characters[$key]);
            }

            array_unshift($characters, $attacker);

            debugCharactersArray($characters);
        }

        $winner = array_pop($characters);

        $wins[$winner->getName()]++;

        echoWithColor("&d====================".PHP_EOL);
        echoWithColor("&d| {$winner->getColorCode()}{$winner->getName()} &ea gagné !  &d|".PHP_EOL);
        echoWithColor("&d====================".PHP_EOL);
        echo PHP_EOL;

        $gameCount++;
        progressBar($gameCount, GAME_NUMBER);
    }

    echo PHP_EOL;
    var_dump(array_map(fn (int $charWins) => ($charWins / GAME_NUMBER * 100)."%", $wins));
}



function userInGame($characters){

    $mapInput = [];
    foreach ($characters as $character) {
        $mapInput[$character->getName()] = $character;
    }

    while (true) {
        echoWithColor("&eChoisissez un personnage : ".PHP_EOL);
        $characterNames = [];
        foreach ($mapInput as $name => $character) {
            $characterNames[] = $name;
        }
        foreach ($characterNames as $characterName) {
            echoWithColor("&e- &a{$characterName}".PHP_EOL);
        }

        $input = trim(stream_get_line(STDIN, 1024, PHP_EOL));

        if (in_array($input, $characterNames)) {
            echoWithColor("&bVous avez choisi &a$input.\n");
            break;
        } else {
            echoWithColor("&cPersonnage invalide.".PHP_EOL);
        }
    }

    $user = $mapInput[$input];

    while (count($characters) > 1) {

        $attacker = array_pop($characters);
        echo PHP_EOL.PHP_EOL."C'est au tour de {$attacker->getName()} (Niveau {$attacker->getLevel()})".PHP_EOL;

        $key = array_rand($characters);
        $target = $characters[$key];

        if($attacker->getName() != $user->getName()){
            $attacker->turn($target);
        } else {
            echo "C'est à vous de jouer !";
            stream_get_line(STDIN, 1024, PHP_EOL);
        }


        if ($target->isDead()) {
            unset($characters[$key]);
        }


        array_unshift($characters, $attacker);

        echoWithColor(PHP_EOL.PHP_EOL."&cAppuyez sur &bEntrée &cpour continuer...");

        stream_get_line(STDIN, 1024, PHP_EOL);
        clearScreen();

        //debugCharactersArray($characters);
    }

    $winner = array_pop($characters);

    echo "&d==================".PHP_EOL;
    echo "&a{$winner->getName()} &ea gagné !".PHP_EOL;
    echo "&d==================".PHP_EOL;
    echo PHP_EOL;
}

function normalGame($brand,$nilah, $ivern){
    clearScreen();
    $wins = [
        "Brand" => 0,
        "Nilah" => 0,
        "Ivern" => 0,
    ];

    $gameCount = 0;

    while ($gameCount < GAME_NUMBER) {

        $characters = [$brand, $nilah, $ivern];
        foreach ($characters as $character) {
            $character->reset();
        }
        orderByInitiative($characters);

        while (count($characters) > 1) {


            $attacker = array_pop($characters);
            echoWithColor("&eC'est au tour de {$attacker->getColorCode()}{$attacker->getName()}&e &d(&eNiveau &b{$attacker->getLevel()}&d)".PHP_EOL);

            $key = array_rand($characters);
            $target = $characters[$key];

            $attacker->turn($target);

            if ($target->isDead()) {
                unset($characters[$key]);
            }


            array_unshift($characters, $attacker);

            debugCharactersArray($characters);
            if(count($characters) != 1){
                echoWithColor(PHP_EOL.PHP_EOL."&cAppuyez sur &bEntrée &cpour continuer...");
                stream_get_line(STDIN, 1024, PHP_EOL);
                clearScreen();
            }

        }

        $winner = array_pop($characters);

        $wins[$winner->getName()]++;

        echoWithColor("&d====================".PHP_EOL);
        echoWithColor("&d| {$winner->getColorCode()}{$winner->getName()} &ea gagné !  &d|".PHP_EOL);
        echoWithColor("&d====================".PHP_EOL);
        echo PHP_EOL;

        $gameCount++;
        progressBar($gameCount, GAME_NUMBER);

    }

    echo PHP_EOL;
    var_dump(array_map(fn (int $charWins) => ($charWins / GAME_NUMBER * 100)."%", $wins));
}



