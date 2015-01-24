<?php

class EncounterController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
    }
	
	public function generateAction()
	{
		$party = new Party($_POST['characterLevels']);
		$terrain = (isset($_POST['terrain']) ? $_POST['terrain'] : 'all');
		$difficulty = (isset($_POST['difficulty']) ? $_POST['difficulty'] : 0);
		
		$encounter = new Encounter($party, $terrain, $difficulty);
		$encounter->generate();
		$this->view->encounter = $encounter;
	}

}