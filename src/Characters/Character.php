<?php

namespace Altino\Characters;

use Altino\Spells\AttackSpell;
use Altino\Spells\DefendSpell;
use Altino\Spells\HealSpell;
use Altino\Types\Element;
use Altino\Items\Item;
use Altino\Languages\Translatable;

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

    public function userTurn(): void
    {
        $this->recoverMana();
        $this->attackSpell->triggerTurn();
        $this->defendSpell->triggerTurn();
        $this->healSpell->triggerTurn();
    }

    private function triggerAction(Character $target){
        if($this->canHealForThisTurn && $this->health*100/$this->maxHealth <= 40){
            echoTranslation("game.character.want_to_heal",$this->getColorCode(),$this->name);
            if($this->healSpell->getCooldownCountDown() == 0){
                if($this->healSpell->getManaCost() <= $this->mana){
                    $this->healSpell->cast($this);
                    $this->removeXpForNextLevel(10);
                    return;
                } else {
                    echoTranslation("game.character.not_enough_mana_to_heal",$this->getColorCode(),$this->name);
                }
            } else {
                echoTranslation("game.character.no_cooldown_to_heal",$this->getColorCode(),$this->name);
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

    public function attack(Character $target): void
    {
        echoTranslation("game.character.attack",$this->getColorCode(),$this->name,$target->getColorCode(),$target->name);
        if(chance($target->dodgeChance)){
            echoTranslation("game.character.dodge",$target->getColorCode(),$target->name,$this->getColorCode(),$this->name);
            $target->removeXpForNextLevel(10);
            return;
        }
        $futureDamages = $this->physicalDamages + $this->item->getAdditionalPhysicalDamages();
        if(chance($this->criticalChance)){
            $futureDamages*=2;
            echoTranslation("game.character.critical_hit",$this->criticalChance*100);
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
        $armor = Translatable::getTranslation("game.character.armor");
        echoTranslation("game.character.took_damage",$this->getColorCode(),$this->name,$realDamages,$this->health,$precedentHealth,$realDamages,$damages,$multiplier,$this->armor,$armor,$this->getColorCode(),$this->name,$damages*$multiplier,$this->armor,$armor,$this->getColorCode(),$this->name,$realDamages);
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
        $magicRes = Translatable::getTranslation("game.character.magic_resistance");
        echoTranslation("game.character.took_damage",$this->getColorCode(),$this->name,$realDamages,$this->health,$precedentHealth,$realDamages,$damages,$multiplier,$this->magicResistance,$magicRes,$this->getColorCode(),$this->name,$damages*$multiplier,$this->magicResistance,$magicRes,$this->getColorCode(),$this->name,$realDamages);
        $this->checkDeath();
    }

    private function checkDeath(): void
    {
        if($this->health == 0){
            echoTranslation("game.character.died",$this->getColorCode(),$this->name);
            $this->isDead = true;
            $this->removeXpForNextLevel(20);
        }
    }



    private function levelUp() {
        if($this->level == 20){
            echoTranslation("game.character.max_level",$this->getColorCode(),$this->name,$this->level);
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
        echoTranslation("game.character.level_up",$this->getColorCode(),$this->name,$this->level);
    }

    public function reset() {
        $this->health = $this->maxHealth;
        $this->mana = $this->maxMana;
        $this->isDead = false;
        $this->attackSpell->resetCooldown();
        $this->defendSpell->resetCooldown();
        $this->healSpell->resetCooldown();
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

    public function toString(): string
    {
        return translate("game.character.info",
            $this->getColorCode(),$this->name,$this->level,$this->getColorCode(),
            $this->element->name,$this->health,$this->maxHealth,$this->mana,$this->maxMana,$this->item->getName(),
            $this->getInitiative(),$this->xpForNextLevel,$this->armor,$this->magicResistance,
            $this->criticalChance*100,$this->dodgeChance*100,$this->physicalDamages,
            $this->magicalDamages,$this->healSpell->getCooldownCountDown(),
            $this->defendSpell->getCooldownCountDown(),$this->attackSpell->getCooldownCountDown());
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

    public function getAttackSpell(): AttackSpell
    {
        return $this->attackSpell;
    }

    public function getDefendSpell(): DefendSpell
    {
        return $this->defendSpell;
    }

    public function getHealSpell(): HealSpell
    {
        return $this->healSpell;
    }

    private function calculateXpForNextLevel() : int
    {
        return $this->level * 100;
    }


    public function getLevel(): int
    {
        return $this->level;
    }

}