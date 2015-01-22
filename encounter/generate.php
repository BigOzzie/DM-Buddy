<?php
include("class_Encounter.php");
include("class_Party.php");

$party = new Party($_POST['characterLevels']);
$encounter = new Encounter($party);
$encounter->generateEncounterForDifficulty(Encounter::MEDIUM);
var_dump($encounter);
