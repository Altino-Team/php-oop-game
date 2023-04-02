<?php

namespace Altino\Spells;

use Altino\Characters\Character;

class HealSpell extends Spell {

    private int $healAmount;

    public function __construct(int $healAmount, int $manaCost, int $cooldown) {
        $this->healAmount = $healAmount;
        parent::__construct(
            "game.spells.heal.name",
            "game.spells.heal.description",
            $manaCost,
            $cooldown
        );
    }

    public function cast(Character $target): void
    {
        parent::cast($target);
        $this->owner->setHealth($this->owner->getHealth() + $this->healAmount);
        $this->triggerCooldown();
        echoTranslation("game.spells.heal.cast", $this->owner->getColorCode(),$this->owner->getName(), $this->healAmount, $this->owner->getHealth());
    }

    public function getDescription(): string
    {
        return translate($this->description, $this->healAmount);
    }


}