<?php

namespace Altino\Spells;

use Altino\Characters\Character;
use Altino\Types\DamageType;

class AttackSpell extends Spell {

    private int $damageAmount;
    private DamageType $damageType;

    public function __construct(
        int $damageAmount,
        DamageType $damageType,
        int $manaCost,
        int $cooldown
    ) {
        $this->damageAmount = $damageAmount;
        $this->damageType = $damageType;
        parent::__construct(
            "game.spells.attack.name",
            "game.spells.attack.description",
            $manaCost,
            $cooldown
        );
    }

    public function cast(Character $target): void
    {
        parent::cast($target);
        echoTranslation("game.spells.attack.cast", $this->owner->getColorCode(),$this->owner->getName(), $target->getColorCode(),$target->getName());
        if($this->damageType == DamageType::PHYSICAL){
            $target->takePhysicalDamages($this->owner,$this->damageAmount);
        } else {
            $target->takeMagicalDamages($this->owner,$this->damageAmount+$this->owner->getItem()->getAdditionalMagicalDamages());
        }
    }

    public function getDescription(): string
    {
        return translate($this->description, $this->damageAmount);
    }


}