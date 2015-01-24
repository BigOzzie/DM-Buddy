<?php
class Encounter {
	//Encounter difficulties
	const EASY = 1;
	const MEDIUM = 2;
	const HARD = 3;
	const DEADLY = 4;
	
	function __construct($party, $terrain, $difficulty) {
		$this->party = $party;
		$this->terrain = $terrain;
		
		if ($difficulty<1 || $difficulty>4) {
			$this->difficulty = rand(1,4);
		} else {
			$this->difficulty = $difficulty;
		}
		
		$this->monsterList = array();
		$this->encounterSize = 1;
		$this->experienceMultiplier = 0;
		$this->experienceRange = array(-1,-1);
		$this->numberOfCreaturesRange = array(-1,-1);
	}
	
	public function generate() {
		$validCRs = array();
		while(empty($validCRs)) {
			if($this->encounterSize == 1) {
				$this->rollEncounterSize();
			} else {
				$this->setEncounterSize($this->encounterSize-1);
			}
			$this->setExperienceRanges();
			$this->setNumberOfCreaturesRanges();
			
			$minimumCreatures = $this->numberOfCreaturesRange[0];
			$maximumCreatures = $this->numberOfCreaturesRange[1];
			$expByCR = $this->experienceByCR();
			$maxMultFactor = $minimumCreatures * $this->experienceMultiplier;
			$minMultFactor = $maximumCreatures * $this->experienceMultiplier;
			$maxExpAllowed = $this->experienceRange[1];
			$minExpAllowed = $this->experienceRange[0];
			$maxExpPerCreatureAllowed = $maxExpAllowed/$maxMultFactor;
			$minExpPerCreatureAllowed = $minExpAllowed/$minMultFactor;
			$this->maxExpPerCreatureAllowed = $maxExpPerCreatureAllowed;
			$this->minExpPerCreatureAllowed = $minExpPerCreatureAllowed;
			$validCRs = array();
			foreach($expByCR as $CR => $expValue) {
				$numCR = floatval($CR);
				if($expValue <= $maxExpPerCreatureAllowed && $expValue >= $minExpPerCreatureAllowed) {
					$validCRs[] = $numCR;
				}
				
			}
			$this->validCRs = $validCRs;
		}
		$this->chosenCR = $validCRs[rand(0,count($validCRs)-1)];
		$this->generatePossibleMonsterPool();
		$expPerCreature = $expByCR[(string)$this->chosenCR];
		$maxNumberOfCreatures = max(min(floor($maxExpAllowed / ($expPerCreature*$this->experienceMultiplier)), $this->numberOfCreaturesRange[1]),$this->numberOfCreaturesRange[0]);
		$minNumberOfCreatures = min(max(ceil($this->experienceRange[0]/ ($expPerCreature*$this->experienceMultiplier)), $this->numberOfCreaturesRange[0]),$this->numberOfCreaturesRange[1]);
		$this->maxNum = $maxNumberOfCreatures;
		$this->minNum = $minNumberOfCreatures;
		$numberOfCreatures = rand($minNumberOfCreatures, $maxNumberOfCreatures);
		$this->monsterList = array();
		if(count($this->possibleMonsterPool)>0) {
			$chosenMonster = $this->possibleMonsterPool[rand(0,count($this->possibleMonsterPool)-1)];
			$this->monsterList[] = $numberOfCreatures .'x '.$chosenMonster['name'].' - '. $expPerCreature .' XP';
		} else {
			$this->monsterList[] = $numberOfCreatures .'x CR '. $this->chosenCR .' Creature - '. $expPerCreature .' XP';
		}
		if ($numberOfCreatures > 1) {
			$this->monsterList[0] .= ' each';
		}
	}
	
	private function generatePossibleMonsterPool() {
		$this->possibleMonsterPool = Monsters::getByTerrainAndCr($this->terrain, $this->chosenCR)->toArray();
	}
	
	private function setNumberOfCreaturesRanges() {
		$encSize = $this->encounterSize;
		if($encSize < 3) {
			$this->numberOfCreaturesRange = array($encSize, $encSize);
		} else {
			$this->numberOfCreaturesRange[0] = 3 + 4 * ($encSize - 3);
			if ($encSize==6) {
				$this->numberOfCreaturesRange[1] = 30;
			} else {
				$this->numberOfCreaturesRange[1] = $this->numberOfCreaturesRange[0] + 3;
			}
		}
	}
	
	private function setExperienceRanges() {
		$this->experienceRange[0] = $this->getPartyThreshold($this->difficulty);
		if($this->difficulty < Encounter::DEADLY) {
			$this->experienceRange[1] = $this->getPartyThreshold($this->difficulty+1)-1;
		} else {
			$this->experienceRange[1] = $this->experienceRange[0]*1.5;
		}
	}
	
	private function rollEncounterSize() {
		$encounterSize = rand(1,6);
		$this->setEncounterSize($encounterSize);
	}
	
	private function setEncounterSize($encounterSize) {
		$this->encounterSize = $encounterSize;
		$this->setExperienceMultiplier();
	}
	
	private function setExperienceMultiplier() {
		$experienceMultipliers = $this->experienceMultipliers();
		$encSize = $this->encounterSize;
		if($this->party->getPartySize() < 3) {
			$encSize++;
		} else if($this->party->getPartySize() > 5) {
			$encSize--;
		}
		$this->experienceMultiplier = $experienceMultipliers[$encSize];
	}
	
	private function getPartyThreshold($threshold) {
		$total = 0;
		foreach($this->party->getPartyLevels() as $characterLevel) {
			$total += $this->getCharacterThreshold($threshold, $characterLevel);
		}
		return $total;
	}
	
	private function getCharacterThreshold($threshold, $characterLevel) {
		$characterThresholds = $this->thresholdTable()[$threshold];
		return $characterThresholds[$characterLevel];
	}
	
	private static function experienceMultipliers() {
		return array(
			0.5,1,1.5,2,2.5,3,4,4
		);
	}
	
	private static function thresholdTable() {
		return array(
			Encounter::EASY=>array(0,25,50,75,125,250,300,350,450,550,600,800,1000,1100,1250,1400,1600,2000,2100,2400,2800),
			Encounter::MEDIUM=>array(0,50,100,150,250,500,600,750,900,1100,1200,1600,2000,2200,2500,2800,3200,3900,4200,4900,5700),
			Encounter::HARD=>array(0,75,150,225,375,750,900,1100,1400,1600,1900,2400,3000,3400,3800,4300,4800,5900,6300,7300,8500),
			Encounter::DEADLY=>array(0,100,200,400,500,1100,1400,1700,2100,2400,2800,3600,4500,5100,5700,6400,7200,8800,9500,10900,12700)
		);
	}
	
	private static function experienceByCR() {
		return array(
			'0.125' => 25,
			'0.25' => 50,
			'0.5' => 100,
			'1' => 200,
			'2' => 450,
			'3' => 700,
			'4' => 1100,
			'5' => 1800,
			'6' => 2300,
			'7' => 2900,
			'8' => 3900,
			'9' => 5000,
			'10' => 5900,
			'11' => 7200,
			'12' => 8400,
			'13' => 10000,
			'14' => 11500,
			'15' => 13000,
			'16' => 15000,
			'17' => 18000,
			'18' => 20000,
			'19' => 22000,
			'20' => 25000,
			'21' => 33000,
			'22' => 41000,
			'23' => 50000,
			'24' => 62000,
			'30' => 155000
		);
	}
}