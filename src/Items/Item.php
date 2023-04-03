<?php

namespace Altino\Items;

class Item
{
    protected string $name;
    protected int $additionalPhysicalDamage = 0;
    protected int $additionalMagicalDamage = 0;
    protected int $additionalInitiative = 0;
    protected int $additionalHealth = 0;

    public function __construct(string $name, int $additionalPhysicalDamage, int $additionalMagicalDamage, int $additionalInitiative, int $additionalHealth)
    {
        $this->name = $name;
        $this->additionalPhysicalDamage = $additionalPhysicalDamage;
        $this->additionalMagicalDamage = $additionalMagicalDamage;
        $this->additionalInitiative = $additionalInitiative;
        $this->additionalHealth = $additionalHealth;
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

    public function getAdditionalHealth(): int
    {
        return $this->additionalHealth;
    }

    public function getName(): string
    {
        return $this->name;
    }
}