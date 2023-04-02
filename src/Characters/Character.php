<?php

namespace Altino\Characters;

use Altino\Spells\AttackSpell;
use Altino\Spells\DefendSpell;
use Altino\Spells\HealSpell;
use Altino\Types\Element;
use Altino\Items\Item;

class Character
{
    private int $maxHealth;
    private int $maxMana;
    private bool $isDead = false;
    private int $xpForNextLevel;
    private int $level;

    private bool $canHealForThisTurn = true;

    public function __construct(
        private string $name,
        private Element $element,
        private int $health,
        private int $mana,
        private Item $item,
        private int $armor,
        private int $magicResistance,
        private float $criticalChance,
        private float $dodgeChance,
        private int $initiative,
        private int $physicalDamages,
        private int $magicalDamages,
        private AttackSpell $attackSpell,
        private DefendSpell $defendSpell,
        private HealSpell $healSpell
    ){
        $this->maxHealth = $health+$item->getAdditionalHealth();
        $this->maxMana = $mana;
        $this->health = $this->maxHealth;
        $this->attackSpell->setOwner($this);
        $this->defendSpell->setOwner($this);
        $this->healSpell->setOwner($this);
        $this->level = 1;
        $this->xpForNextLevel = $this->calculateXpForNextLevel();
    }

    public function turn(Character $target): void
    {
        $this->recoverMana();
        $this->attackSpell->triggerTurn();
        $this->defendSpell->triggerTurn();
        $this->healSpell->triggerTurn();

        $this->triggerAction($target);
        $this->canHealForThisTurn = true;
    }

    private function triggerAction(Character $target){
        if($this->canHealForThisTurn && $this->health*100/$this->maxHealth <= 40){
            echo "$this->name a moins de 40% de ses points de vie, il aimerait donc se soigner !" . PHP_EOL;
            if($this->healSpell->getCooldownCountDown() == 0){
                if($this->healSpell->getManaCost() <= $this->mana){
                    $this->healSpell->cast($this);
                    $this->removeXpForNextLevel(10);
                    return;
                } else {
                    echo "$this->name n'a malheureusement pas assez de mana pour se soigner !" . PHP_EOL;
                }
            } else {
                echo "$this->name n'a malheureusement pas son sort de heal de disponible !" . PHP_EOL;
            }
            $this->canHealForThisTurn = false;
        }
        switch (rand() % 3){
            case 0:
                $this->attack($target);
                break;
            case 1:
                if($this->attackSpell->getCooldownCountDown() == 0 && $this->attackSpell->getManaCost() <= $this->mana){
                    $this->attackSpell->cast($target);
                } else {
                    $this->triggerAction($target);
                }
                break;
            case 2:
                if($this->defendSpell->getCooldownCountDown() == 0 && $this->defendSpell->getManaCost() <= $this->mana){
                    $this->defendSpell->cast($this);
                } else {
                    $this->triggerAction($target);
                }
                break;
            default:
                break;
        }
    }

    private function attack(Character $target): void
    {
        echo "$this->name fait une attaque de base à $target->name !" . PHP_EOL;
        if(chance($target->dodgeChance)){
            echo "$target->name esquive l'attaque de $this->name" . PHP_EOL;
            $this->removeXpForNextLevel(10);
            return;
        }
        $futureDamages = $this->physicalDamages + $this->item->getAdditionalPhysicalDamages();
        if(chance($this->criticalChance)){
            $futureDamages*=2;
            echo "Coup critique ! (".($this->criticalChance*100)."% de chance)" . PHP_EOL;
            $this->removeXpForNextLevel(20);
        }
        $target->takePhysicalDamages($this,$futureDamages);
    }

    public function takePhysicalDamages(Character $sender, int $damages): void
    {
        $precedentHealth = $this->health;
        $multiplier = calculateMultiplierByType($sender,$this);
        $realDamages = ($damages*$multiplier) - $this->armor;
        if($realDamages < 0){
            $realDamages = 0;
        }
        $this->setHealth($this->health - $realDamages);
        echo "$this->name a perdu ".($realDamages)." points de vie ! Il est maintenant à $this->health HP ($precedentHealth HP - $realDamages Dégâts)".PHP_EOL."  DEGATS = $damages(Dégats) * $multiplier(Multiplieur de Type) - $this->armor (Armure de $this->name)" . PHP_EOL . "         = ".$damages * $multiplier."(Dégâts avec Type) - $this->armor (Armure de $this->name)" . PHP_EOL . "         = $realDamages" . PHP_EOL;
        $this->checkDeath();
    }

    public function takeMagicalDamages(Character $sender, int $damages): void
    {
        $precedentHealth = $this->health;
        $multiplier = calculateMultiplierByType($sender,$this);
        $realDamages = ($damages*$multiplier) - $this->magicResistance;
        if($realDamages < 0){
            $realDamages = 0;
        }
        $this->setHealth($this->health - $realDamages);
        echo "$this->name a perdu ".($realDamages)." points de vie ! Il est maintenant à $this->health HP ($precedentHealth HP - $realDamages Dégâts)".PHP_EOL."  DEGATS = $damages(Dégats) * $multiplier(Multiplieur de Type) - $this->magicResistance (Résistance Magique de $this->name)" . PHP_EOL . "         = ".$damages * $multiplier."(Dégâts avec Type) - $this->magicResistance (Résistance Magique de $this->name)" . PHP_EOL . "         = $realDamages" . PHP_EOL;
        $this->checkDeath();
    }

    private function checkDeath(): void
    {
        if($this->health == 0){
            echo "$this->name est mort" . PHP_EOL;
            $this->isDead = true;
            $this->removeXpForNextLevel(20);
        }
    }

    public function getMagicalDamages(): int
    {
        return $this->magicalDamages;
    }

    public function getPhysicalDamages(): int
    {
        return $this->physicalDamages;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getElement(): Element
    {
        return $this->element;
    }

    public function getHealth(): int
    {
        return $this->health;
    }

    public function setHealth(int $health): void
    {
        if($this->maxHealth < $health){
            $this->health = $this->maxHealth;
        } else if($health < 0) {
            $this->health = 0;
        } else {
            $this->health = $health;
        }
    }

    public function isDead() : bool {
        return $this->isDead;
    }

    public function getMana(): int
    {
        return $this->mana;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function getArmor(): int
    {
        return $this->armor;
    }

    public function getMagicResistance(): int
    {
        return $this->magicResistance;
    }

    public function getCriticalChance(): float
    {
        return $this->criticalChance;
    }

    public function getDodgeChance(): float
    {
        return $this->dodgeChance;
    }

    public function getInitiative(): int
    {
        return $this->initiative+$this->item->getAdditionalInitiative();
    }

    public function setArmor(int $armor): void
    {
        $this->armor = $armor;
    }

    public function setMagicResistance(int $magicResistance): void
    {
        $this->magicResistance = $magicResistance;
    }

    private function recoverMana()
    {
        $this->setMana($this->mana + 10);
    }


    public function toString(): string
    {
        return "{$this->getColorCode()}{$this->name}&f:".PHP_EOL
            ."&f  - &eElement : {$this->getColorCode()}{$this->element->name}".PHP_EOL
            ."&f  - &eVie : &a{$this->health}".PHP_EOL
            ."&f  - &eMana : &b{$this->mana}".PHP_EOL
            ."&f  - &eObjet : &d{$this->item->getName()}".PHP_EOL
            ."&f  - &eArmure : &f{$this->armor}".PHP_EOL
            ."&f  - &eRésistance magique : &f{$this->magicResistance}".PHP_EOL
            ."&f  - &eChance de coup critique : &f{$this->criticalChance}".PHP_EOL
            ."&f  - &eChance d'esquive : &f{$this->dodgeChance}".PHP_EOL
            ."&f  - &eInitiative : &f{$this->initiative}".PHP_EOL
            ."&f  - &eDégâts &cphysiques&f/&dmagiques : &c{$this->physicalDamages}&f/&d{$this->magicalDamages}".PHP_EOL
            ."&f  - &eCooldown Sort d'attaque : &f{$this->attackSpell->getCooldownCountDown()}".PHP_EOL
            ."&f  - &eCooldown Sort de défense : &f{$this->defendSpell->getCooldownCountDown()}".PHP_EOL
            ."&f  - &eCooldown Sort de soin : &f{$this->healSpell->getCooldownCountDown()}";
    }

    public function setMana(int $mana){
        if ($mana > $this->maxMana){
            $this->mana = $this->maxMana;
        } else if ($mana < 0){
            $this->mana = 0;
        } else {
            $this->mana = $mana;
        }
    }

    private function calculateXpForNextLevel() : int
    {
        return $this->level * 100;
    }

    private function removeXpForNextLevel(int $xp)
    {
        $this->xpForNextLevel -= $xp;
        if($this->xpForNextLevel <= 0){
            $this->levelUp();
        }
    }

    public function getColorCode() : string
    {
        return match ($this->element->name) {
            "FIRE" => "&c",
            "WATER" => "&b",
            "GRASS" => "&a",
            default => "&f",
        };
    }

    private function levelUp() {
        if($this->level == 20){
            echo "$this->name est au niveau maximum (Niveau $this->level) !". PHP_EOL;
            return;
        }
        $this->level++;
        $this->xpForNextLevel = $this->calculateXpForNextLevel();
        $this->maxHealth += 10;
        $this->maxMana += 10;
        $this->physicalDamages += 5;
        $this->magicalDamages += 5;
        $this->armor += 5;
        $this->magicResistance += 5;
        $this->initiative += 10;
        echo "$this->name passe au niveau $this->level !". PHP_EOL;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function reset() {
        $this->health = $this->maxHealth;
        $this->mana = $this->maxMana;
        $this->isDead = false;
        $this->attackSpell->resetCooldown();
        $this->defendSpell->resetCooldown();
        $this->healSpell->resetCooldown();
    }

}