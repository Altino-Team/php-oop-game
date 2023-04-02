<?php

namespace Altino\Spells;

use Altino\Characters\Character;

class DefendSpell extends Spell {

    private bool $isDefending = false;
    private int $defenseAmount;

    public function __construct(int $defenseAmount, int $manaCost, int $cooldown) {
        $this->defenseAmount = $defenseAmount;
        parent::__construct(
            "Defend",
            "Defend yourself for one turn by adding bonus defense amount.",
            $manaCost,
            $cooldown
        );
    }

    public function cast(Character $target): void
    {
        parent::cast($target);
        $this->owner->setArmor($this->owner->getArmor() + $this->defenseAmount);
        $this->owner->setMagicResistance($this->owner->getMagicResistance() + $this->defenseAmount);
        $this->isDefending = true;
        $this->triggerCooldown();
        echo $this->owner->getName() . " se défend pour un tour (Bonus : +$this->defenseAmount en armure et en résistance magique).".PHP_EOL;
    }

    private function endSpell(){
        $this->owner->setArmor($this->owner->getArmor() - $this->defenseAmount);
        $this->owner->setMagicResistance($this->owner->getMagicResistance() - $this->defenseAmount);
        echo $this->owner->getName() . " n'est plus sous l'effet de son sort de défense.".PHP_EOL;
    }

    public function triggerTurn(): void
    {
        parent::triggerTurn();
        if($this->isDefending){
            $this->endSpell();
            $this->isDefending = false;
        }
    }
}