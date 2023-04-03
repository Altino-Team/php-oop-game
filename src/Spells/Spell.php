<?php

namespace Altino\Spells;

use Altino\Characters\Character;

abstract class Spell {

    protected int $cooldownCountDown = 0;
    protected Character $owner;


    public function __construct(
        protected string $name,
        protected string $description,
        protected int $manaCost,
        protected int $cooldown
    ) {}

    public function cast(Character $target): void {
        $this->triggerCooldown();
        $this->owner->setMana($this->owner->getMana() - $this->manaCost);
    }

    public function triggerTurn(): void {
        if($this->cooldownCountDown > 0){
            $this->cooldownCountDown--;
        }
    }

    protected function triggerCooldown(){
        $this->cooldownCountDown = $this->cooldown;
    }

    public function setOwner(Character $owner): void
    {
        $this->owner = $owner;
    }
    public function getName(): string
    {
        return translate($this->name);
    }

    public function getDescription(): string
    {
        return translate($this->description);
    }

    public function resetCooldown(): void
    {
        $this->cooldownCountDown = 0;
    }

    public function getManaCost(): int
    {
        return $this->manaCost;
    }

    public function getCooldownCountDown(): int
    {
        return $this->cooldownCountDown;
    }

    public function getOwner()
    {
        return $this->owner;
    }
}