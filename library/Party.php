<?php
class Party {
	function __construct($partyLevels) {
		$this->characters = $partyLevels;
	}
	
	public function getPartyLevels() {
		return $this->characters;
	}
	
	public function getPartySize() {
		return count($this->characters);
	}
	
	public function getLowestLevel() {
		$lowest = 21;
		foreach($this->characters as $level) {
			if($level < $lowest) $lowest = $level;
		}
		return $lowest;
	}
}