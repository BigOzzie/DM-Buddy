<?php
class Encounter {
	//Encounter difficulties
	const EASY = 1;
	const MEDIUM = 2;
	const HARD = 3;
	const DEADLY = 4;
	
	/**
	* Constructor
	* @param Party  $party
	* @param String $terrain
	* @param int    $difficulty
	* @returns VOID
	*/
	function __construct($party, $terrain, $difficulty) {
		$this->party = $party;
		$this->terrain = $terrain;
		
		if ($difficulty<1 || $difficulty>4) {
			$this->difficulty = rand(1,4);
		} else {
			$this->difficulty = $difficulty;
		}
		
		$this->encounterMakeup = array();
		$this->monsterList = array();
		$this->encounterSize = 0;
		$this->possibleEncounterSizes = array(1,2,3,4,5,6);
		$this->experienceMultiplier = 0;
		$this->experienceRangeLowerBound = -1;
		$this->experienceRangeUpperBound = -1;
		$this->numberOfCreaturesRangeLowerBound = -1;
		$this->numberOfCreaturesRangeUpperBound = -1;
	}
	
	public static function validDifficulties() {
		return array(
			self::EASY=>'Easy',
			self::MEDIUM=>'Medium',
			self::HARD=>'Hard',
			self::DEADLY=>'Deadly'
		);
	}
	
	public function output() {
		$returnVar = array();
		$expValues = $this->experienceByCR();
		if(!empty($this->encounterMakeup)) {
			$expTotal = 0;
			foreach($this->encounterMakeup as $encounterPart) {
				$message = $encounterPart['quantity'] . 'x';
				if($encounterPart['name'] == '') {
					$message .= ' CR '.$encounterPart['cr'].' Monster';
					if ($encounterPart['quantity'] > 1) $message .= 's';
				} else {
					$message .= ' '.$encounterPart['name'].' (CR '.$encounterPart['cr'].')';
				}
				$message .= ' - '.$expValues[$encounterPart['cr']].' XP';
				if ($encounterPart['quantity'] > 1) $message .= ' each';
				$returnVar[] = $message;
				$expTotal += $encounterPart['quantity'] * $expValues[$encounterPart['cr']];
			}
			$returnVar[] = '';
			$returnVar[] = 'ENCOUNTER EXPERIENCE VALUE: '.number_format($expTotal*$this->experienceMultiplier,2);
			$returnVar[] = 'EXPERIENCE AWARDED (TOTAL): '.number_format($expTotal,0);
			$returnVar[] = 'EXPERIENCE AWARDED (PER CHARACTER): '.number_format($expTotal/$this->party->getPartySize(),0);
		}
		return $returnVar;
	}
	
	public function generate() {
		while(empty($this->encounterMakeup)) {
			while(empty($this->possibleBaseCRs)) {
				$this->generateEncounterSize();
				if(empty($this->possibleEncounterSizes)) {
					return FALSE;
				}
				$this->setExperienceRanges();
				$this->setNumberOfCreaturesRanges();
				$this->findPossibleBaseCRs();
			}
			$this->generateEncounterMakeup();
			$this->possibleBaseCRs = array();
		}
		$this->generateSpecificMonsters();
		return TRUE;
	}
	
	private function generateEncounterMakeup() {
		$expValues = $this->experienceByCR();
		if($this->encounterSize == 1) {
			$chosenCR = $this->possibleBaseCRs[array_rand($this->possibleBaseCRs)];
			$this->encounterMakeup = array(
				array('cr'=>$chosenCR, 'quantity'=>1)
			);
		} else {
			$attempts = 0;
			while(empty($this->encounterMakeup) && $attempts < 50) {
				$expBudgetMin = $this->experienceRangeLowerBound;
				$expBudgetMax = $this->experienceRangeUpperBound;
				$possibleCRs = $this->possibleBaseCRs;
				$totalNumberOfCreatures = 0;
				while($expBudgetMin > 0 && count($possibleCRs) > 0) {
					//$chosenCR = array_splice($possibleCRs,array_rand($possibleCRs),1)[0];
					$chosenCrIndex = array_rand($possibleCRs);
					$chosenCR = $possibleCRs[$chosenCrIndex];
					$expOfChosen = $expValues[$chosenCR] * $this->experienceMultiplier;
					if($expOfChosen > $expBudgetMax) {
						array_splice($possibleCRs, $chosenCrIndex, 1);
					} else {
						$maxNumberOfChosen = floor($expBudgetMax/$expOfChosen);
						$numberOfChosen = rand(1, $maxNumberOfChosen);
						$this->encounterMakeup[] = array('cr'=>$chosenCR, 'quantity' => $numberOfChosen);
						
						$expBudgetMin -= $expOfChosen * $numberOfChosen;
						$expBudgetMax -= $expOfChosen * $numberOfChosen;
						$totalNumberOfCreatures += $numberOfChosen;
					}
				}
				if($expBudgetMax < 0 || $totalNumberOfCreatures < $this->numberOfCreaturesRangeLowerBound || $totalNumberOfCreatures > $this->numberOfCreaturesRangeUpperBound) {
					$this->encounterMakeup = array();
					$attempts++;
				}
			}
		}
	}
	
	private function findPossibleBaseCRs() {
		$expValues = $this->experienceByCR();
		$this->possibleBaseCRs = array();
		foreach($expValues as $cr => $exp) {
			$crExperienceValue = $exp * $this->experienceMultiplier;
			if(($this->encounterSize!=1 || $crExperienceValue >= $this->experienceRangeLowerBound) && $crExperienceValue <= $this->experienceRangeUpperBound) {
				$this->possibleBaseCRs[] = $cr;
			}
		}
	}
	
	private function generateEncounterSize() {
		if($this->encounterSize > 0) {
			unset($this->possibleEncounterSizes[array_search($this->encounterSize, $this->possibleEncounterSizes)]);
		}
		if(!empty($this->possibleEncounterSizes)) {
			$this->rollEncounterSize();
		} else {
			$this->setEncounterSize(0);
		}
	}
	
	private function generateSpecificMonsters() {
		foreach ($this->encounterMakeup as $i => $encounterPart) {
			$possibleMonsters = $this->generatePossibleMonsterPool($encounterPart['cr']);
			if(empty($possibleMonsters)) {
				$this->encounterMakeup[$i]['name'] = '';
			} else {
				$chosenMonster = $possibleMonsters[array_rand($possibleMonsters)];
				$this->encounterMakeup[$i]['name'] = ($encounterPart['quantity'] > 1 ? $chosenMonster['plural'] : $chosenMonster['name']);
			}
		}
	}
	
	private function generatePossibleMonsterPool($cr) {
		return Monsters::getByTerrainAndCr($this->terrain, $cr)->toArray();
	}
	
	private function setNumberOfCreaturesRanges() {
		$encSize = $this->encounterSize;
		if($encSize < 3) {
			$this->numberOfCreaturesRangeLowerBound = $this->numberOfCreaturesRangeUpperBound = $encSize;
		} else {
			$this->numberOfCreaturesRangeLowerBound = 3 + 4 * ($encSize - 3);
			if ($encSize==6) {
				$this->numberOfCreaturesRangeUpperBound = 30;
			} else {
				$this->numberOfCreaturesRangeUpperBound = $this->numberOfCreaturesRangeLowerBound + 3;
			}
		}
	}
	
	private function setExperienceRanges() {
		$this->experienceRangeLowerBound = $this->getPartyThreshold($this->difficulty);
		if($this->difficulty < Encounter::DEADLY) {
			$this->experienceRangeUpperBound = $this->getPartyThreshold($this->difficulty+1)-1;
		} else {
			$this->experienceRangeUpperBound = $this->experienceRangeLowerBound*1.5;
		}
	}
	
	private function rollEncounterSize() {
		$encounterSize = $this->possibleEncounterSizes[array_rand($this->possibleEncounterSizes)];
		$this->setEncounterSize($encounterSize);
	}
	
	private function setEncounterSize($encounterSize) {
		$this->encounterSize = $encounterSize;
		$this->generateExperienceMultiplier();
	}
	
	private function generateExperienceMultiplier() {
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
			'0' => 10,
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