<?php

function chance($percentage): bool
{
    return rand() % 100 < $percentage;
}

function calculateMultiplierByType(\Characters\Character $attacker, \Characters\Character $defender): float
{

    return $multiplier;
}
