<?php

use Characters\Element;
use Characters\Character;
use Weapons\Weapon;

$brand = new Character("Brand", Element::Fire, 4300, 3000, new Weapon("Baton du Vide", 0, 60, 10, 10, 10), 60, 50, 0, 15, 100);
$nilah = new Character("Nilah", Element::Water, 2600, 1420, new Weapon("Arc-Bouclier", 70, 0, 0, 10, 10), 60, 60, 60, 10, 120);
$ivern = new Character("Ivern", Element::Grass, 5000, 2000, new Weapon("Warmog", 0, 0, 0, 0, 0), 60, 60, 60, 10, 120);