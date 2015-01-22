<?php

class EncounterController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
    }
	
	public function generateAction()
	{
		include("class_Encounter.php");
		include("class_Party.php");

		$party = new Party($_POST['characterLevels']);
		$encounter = new Encounter($party);
		$encounter->generateEncounterForDifficulty(Encounter::MEDIUM);
		$this->view->encounter = $encounter;
	}

}