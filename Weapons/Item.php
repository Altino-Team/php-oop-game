<?php

namespace Weapons;
class Item
{
    protected string $name;
    protected int $additionalPhysicalDamage = 0;
    protected int $additionalMagicalDamage = 0;
    protected int $additionalInitiative = 0;

    public function __construct(string $name, int $additionalPhysicalDamage, int $additionalMagicalDamage, int $additionalInitiative)
    {
        $this->name = $name;
        $this->additionalPhysicalDamage = $additionalPhysicalDamage;
        $this->additionalMagicalDamage = $additionalMagicalDamage;
        $this->additionalInitiative = $additionalInitiative;
    }

    public function getAdditionalPhysicalDamages(): int
    {
        return $this->additionalPhysicalDamage;
    }

    public function getAdditionalInitiative(): int
    {
        return $this->additionalInitiative;
    }

    public function getAdditionalMagicalDamages(): int
    {
        return $this->additionalMagicalDamage;
    }
}