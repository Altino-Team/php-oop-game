<?php

namespace Characters;

use Weapons\Item;

class Character
{
    public function __construct(
        private string $name,
        private Element $element,
        private int $health,
        private int $mana,
        private Item $item,
        private int $armor,
        private int $magicResistance,
        private float $criticalChance,
        private float $dodgeChance,
        private int $initiative
    ){}

    public function getName(): string
    {
        return $this->name;
    }

    public function getElement(): Element
    {
        return $this->element;
    }

    public function getHealth(): int
    {
        return $this->health;
    }

    public function getMana(): int
    {
        return $this->mana;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function getArmor(): int
    {
        return $this->armor;
    }

    public function getMagicResistance(): int
    {
        return $this->magicResistance;
    }

    public function getCriticalChance(): float
    {
        return $this->criticalChance;
    }

    public function getDodgeChance(): float
    {
        return $this->dodgeChance;
    }

    public function getInitiative(): int
    {
        return $this->initiative;
    }

}