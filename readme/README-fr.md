
<center><b>This README is available in English and in French<b>

<a href="README.md"><img src="https://upload.wikimedia.org/wikipedia/commons/8/83/Flag_of_the_United_Kingdom_%283-5%29.svg" alt="Drapeau anglais" width="60"/></a>
<a href="readme/README-fr.md"><img src="https://upload.wikimedia.org/wikipedia/commons/c/c3/Flag_of_France.svg" alt="Drapeau français" width="60"></a>
</center>

# Turn-Based Combat Game
Game made for the PHP OOP course at ESGI Paris.

    Contributors:
    - Noam DE MASURE
    - Thibaut LULINSKI
    - Alexandre COMET
## Description
This is a turn-based game where characters fight each other with spells, taking into account their stats, type, and level.
## Requirements
- PHP 8.1

## How to Play
To let the characters play 1 game and visualize the game in turn-based view, run the following command:
```bash
php index.php
```

You can also make them play without the game stopping at each turn, to get directly to the final score. To do so, use the **--auto** option:
```bash
php index.php --auto # Play automatically
```

You can modify the number of turns by specifying the **--turn** parameter:
```bash
php index.php --turn 10 # Play 10 turns
```

You can choose to play your own character in the game, which will then necessarily take place in turn-based mode, on 1 game. Use the --play option:
```bash
php index.php --play # Play by choosing your character
```

We can then imagine the following command:
```bash
php index.php --auto --turn 100 # Play automatically 100 turns (useful for getting statistics)
```

## Languages

This project has a translation system. Currently, it is available in French and English.
Feel free to add your language if you wish by adding the corresponding **JSON** file for your language in the project's **lang** folder.

By default, the game is in **French**, but you can change the language with the **--lang** option:
```bash
php index.php --lang en # Play in english
```