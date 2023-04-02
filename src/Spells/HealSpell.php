<?php

namespace Altino\Spells;

use Altino\Characters\Character;

class HealSpell extends Spell {

    private int $healAmount;

    public function __construct(int $healAmount, int $manaCost, int $cooldown) {
        $this->healAmount = $healAmount;
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
        $this->owner->setHealth($this->owner->getHealth() + $this->healAmount);
        $this->triggerCooldown();
        echo $this->owner->getName() . " se soigne de $this->healAmount HP. Il est maintenant à ".$this->owner->getHealth()." HP.".PHP_EOL;
    }


}