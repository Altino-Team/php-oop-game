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
            "Attack",
            "Attack someone with some mysterious power.",
            $manaCost,
            $cooldown
        );
    }

    public function cast(Character $target): void
    {
        parent::cast($target);
        echo "{$this->owner->getName()} fait son sort d'attaque à {$target->getName()} !" . PHP_EOL;
        if($this->damageType == DamageType::PHYSICAL){
            $target->takePhysicalDamages($this->owner,$this->damageAmount);
        } else {
            $target->takeMagicalDamages($this->owner,$this->damageAmount+$this->owner->getItem()->getAdditionalMagicalDamages());
        }
    }




}