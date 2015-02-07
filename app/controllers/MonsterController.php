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
	
	public function importMonstersByCrAction() {
		die();
		$file = fopen('monsters.csv','r');
		if($file === FALSE) {
			echo "File open failure";
			die();
		}
		$monsters = fgetcsv($file);
		$cr = 0;
		for($i=0; $i<count($monsters);$i++) {
			$name = $monsters[$i];
			$name = trim(ucwords($name));
			if(!empty($name)) {
				$monster = new Monsters();
				$params = array('name'=>$name,'cr'=>$cr,'sourceBook'=>'MM','plural'=>$name.'s');
				$monster->save($params, array('name','plural','cr','sourceBook'));
			}
		}
		echo 1;
		die();
	}
	
	public function importMonstersAction() {
		die();
		$file = fopen('monstersTerrains.csv','r');
		if($file === FALSE) {
			echo "File open failure";
			die();
		}
		$notFound = array();
		while(!feof($file)) {
			$monsters = fgetcsv($file);
			$terrain = $monsters[0];
			for($i=1; $i<count($monsters);$i++) {
				$name = $monsters[$i];
				$name = trim(ucwords($name));
				if(!empty($name)) {
					$monster = Monsters::findFirst('name = "'.$name.'"');
					if($monster===FALSE) {
						$notFound[] = $name;
					} else {
						if($monster->terrains === null) {
							$currentTerrains = array();
						} else {
							$currentTerrains = json_decode($monster->terrains);
						}
						if(!in_array($terrain, $currentTerrains)) {
							$currentTerrains[] = $terrain;
							$monster->terrains = json_encode($currentTerrains);
							$monster->save();
						}
					}
				}
			}
		}
		if(!empty($notFound)) {
			echo "The following monsters were not found:";
			foreach($notFound as $mon) {
				echo "<br>".$mon;
			}
		} else {
			echo 1;
		}
		die();
	}

}