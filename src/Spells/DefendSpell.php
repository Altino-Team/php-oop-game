<?php

namespace Altino\Spells;

use Altino\Characters\Character;

class DefendSpell extends Spell {

    private bool $isDefending = false;
    private int $defenseAmount;

    public function __construct(int $defenseAmount, int $manaCost, int $cooldown) {
        $this->defenseAmount = $defenseAmount;
        parent::__construct(
            "game.spells.defend.name",
            "game.spells.defend.description",
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
        echoTranslation("game.spells.defend.cast", $this->owner->getColorCode(),$this->owner->getName(), $this->defenseAmount);
    }

    private function endSpell(){
        $this->owner->setArmor($this->owner->getArmor() - $this->defenseAmount);
        $this->owner->setMagicResistance($this->owner->getMagicResistance() - $this->defenseAmount);
        echoTranslation("game.spells.defend.end", $this->owner->getColorCode(),$this->owner->getName());
    }

    public function triggerTurn(): void
    {
        parent::triggerTurn();
        if($this->isDefending){
            $this->endSpell();
            $this->isDefending = false;
        }
    }

    public function getDescription(): string
    {
        return translate($this->description, $this->defenseAmount);
    }
}