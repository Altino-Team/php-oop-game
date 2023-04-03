<?php

require_once('./autoload.php');
require_once('./functions.php');

defineGameNumber($argv);

loadLanguages($argv);

$characters = createCharacters();

$mapInput = [];
foreach ($characters as $character) {
    $mapInput[$character->getName()] = $character;
}

if (in_array("--play", $argv)) {
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

        echoTranslation("game.display_winner", $winner->getColorCode(), $winner->getName());

        $gameCount++;
        progressBar($gameCount, GAME_NUMBER);
    }

    endWithStatistics($mapInput, $wins);
}

function playTurn(array $characters): array
{
    $attacker = array_pop($characters);
    echoTranslation("game.normal_turn", $attacker->getColorCode(), $attacker->getName(), $attacker->getLevel());

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


function userInGame($characters, $mapInput)
{

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
            echoTranslation("game.user_choice.chosen_character", $user->getColorCode(), $input);
            break;
        } else {
            echoTranslation("game.user_choice.invalid_character");
        }
    }

    while (count($characters) > 1) {
        $attacker = array_pop($characters);

        if ($attacker->getName() != $user->getName()) {
            echoTranslation("game.normal_turn", $attacker->getColorCode(), $attacker->getName(), $attacker->getLevel());

            $key = array_rand($characters);
            $target = $characters[$key];
            $attacker->turn($target);
        } else {
            $attacker->userTurn();
            echoTranslation("game.user_turn", $attacker->getColorCode(), $attacker->getName(), $attacker->getLevel(), $attacker->getHealth(), $attacker->getMana());

            $key = array_rand($characters);
            $target = $characters[$key];

            echoTranslation("game.player_target", $target->getColorCode(), $target->getName(), $target->getLevel(), $target->getHealth(), $target->getArmor(), $target->getMagicResistance());
            echo PHP_EOL;
            echoTranslation("game.actions.available_actions");
            echoWithColor("&9(1) => ");
            echoTranslation("game.actions.baseAttack", $attacker->getPhysicalDamages(), $attacker->getCriticalChance() * 100);
            echoWithColor("&9(2) => ");
            displaySpell($attacker->getAttackSpell());
            echoWithColor("&9(3) => ");
            displaySpell($attacker->getDefendSpell());
            echoWithColor("&9(4) => ");
            displaySpell($attacker->getHealSpell());

            $validAction = false;
            while (!$validAction) {
                echoTranslation("game.actions.which_action");
                $input = (int)trim(stream_get_line(STDIN, 1024, PHP_EOL));

                switch ($input) {
                    case 1:
                        $validAction = true;
                        $attacker->attack($target);
                        break;
                    case 2:
                        if ($validAction = validateSpellAction($attacker->getAttackSpell(), $attacker)) {
                            $attacker->getAttackSpell()->cast($target);
                        }
                        break;
                    case 3:
                        if ($validAction = validateSpellAction($attacker->getDefendSpell(), $attacker)) {
                            $attacker->getDefendSpell()->cast($target);
                        }
                        break;
                    case 4:
                        if ($validAction = validateSpellAction($attacker->getHealSpell(), $attacker)) {
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

    echoTranslation("game.display_winner", $winner->getColorCode(), $winner->getName());
    echo PHP_EOL;
    echoTranslation("game.end");
    exit();
}

function normalGame($brand, $nilah, $ivern, $mapInput)
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
            if (count($characters) != 1) {
                echoTranslation("game.user_choice.continue");
                stream_get_line(STDIN, 1024, PHP_EOL);
                clearScreen();
            }
        }

        $winner = array_pop($characters);

        $wins[$winner->getName()]++;

        echoTranslation("game.display_winner", $winner->getColorCode(), $winner->getName());

        $gameCount++;
        echo PHP_EOL;
        progressBar($gameCount, GAME_NUMBER);
    }

    endWithStatistics($mapInput, $wins);
}




