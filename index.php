<?php

require_once('./autoload.php');
require_once('./functions.php');

use Altino\Types\{Element, DamageType};
use Altino\Characters\Character;
use Altino\Items\Item;
use \Altino\Spells\{AttackSpell, DefendSpell, HealSpell};


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

if (!file_exists('config.ini')) {
    $config = array('language' => 'en');
    file_put_contents('config.ini', serialize($config));
}
$config = unserialize(file_get_contents('config.ini'));
if(in_array("--lang", $argv)){
    $config['language'] = $argv[array_search("--lang", $argv) + 1];
    file_put_contents('config.ini', serialize($config));
}

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

$mapInput = [];
foreach ($characters as $character) {
    $mapInput[$character->getName()] = $character;
}

if(in_array("--play", $argv)){
    userInGame($characters, $mapInput);
} elseif (in_array("--auto", $argv)) {
    autoPlayedGame($characters[0], $characters[1], $characters[2], $mapInput);
} else {
    normalGame($characters[0], $characters[1], $characters[2], $mapInput);
}


function autoPlayedGame($brand, $nilah, $ivern, $mapInput)
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
            $characters = playTurn($characters);
        }

        $winner = array_pop($characters);

        $wins[$winner->getName()]++;

        echoTranslation("game.display_winner",$winner->getColorCode(),$winner->getName());

        $gameCount++;
        progressBar($gameCount, GAME_NUMBER);
    }

    endWithStatistics($mapInput, $wins);
}

function playTurn(array $characters): array
{
    $attacker = array_pop($characters);
    echoTranslation("game.normal_turn", $attacker->getColorCode(),$attacker->getName(),$attacker->getLevel());

    $key = array_rand($characters);
    $target = $characters[$key];

    $attacker->turn($target);

    if ($target->isDead()) {
        unset($characters[$key]);
    }

    array_unshift($characters, $attacker);

    debugCharactersArray($characters);
    return $characters;
}


function userInGame($characters,$mapInput){

    while (true) {
        echoTranslation("game.user_choice.choose_character");
        $characterNames = [];
        foreach ($mapInput as $name => $character) {
            $characterNames[] = $name;
            echoTranslation("game.user_choice.character_presentation", $character->getColorCode(), $name);
        }

        $input = trim(stream_get_line(STDIN, 1024, PHP_EOL));

        if (in_array($input, $characterNames)) {
            clearScreen();
            $user = $mapInput[$input];
            echoTranslation("game.user_choice.chosen_character", $user->getColorCode(),$input);
            break;
        } else {
            echoTranslation("game.user_choice.invalid_character");
        }
    }



    while (count($characters) > 1) {

        $attacker = array_pop($characters);


        if($attacker->getName() != $user->getName()){
            echoTranslation("game.normal_turn", $attacker->getColorCode(),$attacker->getName(),$attacker->getLevel());

            $key = array_rand($characters);
            $target = $characters[$key];
            $attacker->turn($target);
        } else {
            $attacker->userTurn();
            echoTranslation("game.user_turn", $attacker->getColorCode(),$attacker->getName(),$attacker->getLevel(),$attacker->getHealth(),$attacker->getMana());

            $key = array_rand($characters);
            $target = $characters[$key];


            echoTranslation("game.player_target", $target->getColorCode(),$target->getName(),$target->getLevel(),$target->getHealth(),$target->getArmor(),$target->getMagicResistance());
            echo PHP_EOL;
            echoTranslation("game.actions.available_actions");
            echoWithColor("&9(1) => ");
            echoTranslation("game.actions.baseAttack", $attacker->getPhysicalDamages(), $attacker->getCriticalChance()*100);
            echoWithColor("&9(2) => ");
            displaySpell($attacker->getAttackSpell());
            echoWithColor("&9(3) => ");
            displaySpell($attacker->getDefendSpell());
            echoWithColor("&9(4) => ");
            displaySpell($attacker->getHealSpell());

            $validAction = false;
            while (!$validAction){
                echoTranslation("game.actions.which_action");
                $input = (int)trim(stream_get_line(STDIN, 1024, PHP_EOL));

                switch ($input) {
                    case 1:
                        $validAction = true;
                        $attacker->attack($target);
                        break;
                    case 2:
                        if($validAction = validateSpellAction($attacker->getAttackSpell(), $attacker)){
                            $attacker->getAttackSpell()->cast($target);
                        }
                        break;
                    case 3:
                        if($validAction = validateSpellAction($attacker->getDefendSpell(), $attacker)){
                            $attacker->getDefendSpell()->cast($target);
                        }
                        break;
                    case 4:
                        if($validAction = validateSpellAction($attacker->getHealSpell(), $attacker)){
                            $attacker->getHealSpell()->cast($target);
                        }
                        break;
                    default:
                        echoTranslation("game.error.invalid_action");
                        break;
                }
            }

        }


        if ($target->isDead()) {
            unset($characters[$key]);
        }

        array_unshift($characters, $attacker);
        debugCharactersArray($characters);
        echo PHP_EOL;
        echoTranslation("game.user_choice.continue");

        stream_get_line(STDIN, 1024, PHP_EOL);
        clearScreen();
    }

    $winner = array_pop($characters);

    echoTranslation("game.display_winner",$winner->getColorCode(),$winner->getName());
    echo PHP_EOL;
    echoTranslation("game.end");
    exit();
}

function normalGame($brand,$nilah, $ivern, $mapInput){
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
            $characters = playTurn($characters);
            if(count($characters) != 1){
                echoTranslation("game.user_choice.continue");
                stream_get_line(STDIN, 1024, PHP_EOL);
                clearScreen();
            }
        }

        $winner = array_pop($characters);

        $wins[$winner->getName()]++;

        echoTranslation("game.display_winner",$winner->getColorCode(),$winner->getName());

        $gameCount++;
        echo PHP_EOL;
        progressBar($gameCount, GAME_NUMBER);
    }

    endWithStatistics($mapInput, $wins);
}




