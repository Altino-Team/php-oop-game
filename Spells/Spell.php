<?php

abstract class Spell {


    public function __construct(
        private string $name,
        private string $description,
        private int $manaCost,
        private int $damage,
        private int $cooldown
    ) {

    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getManaCost(): int
    {
        return $this->manaCost;
    }

    public function getDamage(): int
    {
        return $this->damage;
    }

    public function getCooldown(): int
    {
        return $this->cooldown;
    }
}