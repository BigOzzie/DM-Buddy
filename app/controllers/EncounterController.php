<?php

class EncounterController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
		$this->view->validTerrains = Monsters::validTerrainsWithAllOption();
		$this->view->validDifficulties = Encounter::validDifficulties();
    }
	
	public function generateAction()
	{
		$params = $this->request->getPost();
		$party = new Party($params['characterLevels']);
		$terrain = ($params['terrain'] != '') ? $params['terrain'] : 'all';
		$difficulty = (isset($params['difficulty']) ? $params['difficulty'] : 0);
		
		$encounter = new Encounter($party, $terrain, $difficulty);
		if($encounter->generate() === FALSE) {
			die("Generate failed!");
		}
		$this->view->encounter = $encounter;
	}

}