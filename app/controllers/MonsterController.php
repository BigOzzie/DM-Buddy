<?php

class MonsterController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
		$this->view->validTerrains = Monsters::validTerrains();
		$this->view->validCRs = Monsters::validCRs();
    }
	
	public function addAction()
	{
		$params = $this->request->getPost();
		$monsters = Monsters::findByName($params['name'])->toArray();
		if(!empty($monsters)) {
			echo 0;
			die();
		}
		
		$monster = new Monsters();
		
		$validCRs = Monsters::validCRs();
		$params['cr'] = $validCRs[$params['cr']];
		
		if(isset($params['terrains'])) {
			$params['terrains'] = json_encode($params['terrains']);
		}
				
		$success = $monster->save($params, array('name','plural','cr','terrains','sourceBook','page'));
		
		if ($success) {
            echo $monster->id;
        } else {
            echo 0;
        }

        $this->view->disable();
	}

}