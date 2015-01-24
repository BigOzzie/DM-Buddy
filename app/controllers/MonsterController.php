<?php

class MonsterController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
    }
	
	public function addAction()
	{
		$monster = new Monsters();
		$params = $this->request->getPost();
		
		if(isset($params['terrains'])) {
			$params['terrains'] = str_replace(' ','',$params['terrains']);
			$params['terrains'] = explode(',',$params['terrains']);
			$params['terrains'] = json_encode($params['terrains']);
		}
		
		$success = $monster->save($params, array('name','cr','terrains','sourceBook','page'));
		
		if ($success) {
            echo $monster->id;
        } else {
            echo 0;
        }

        $this->view->disable();
	}

}