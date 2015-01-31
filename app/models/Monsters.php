<?php

class Monsters extends \Phalcon\Mvc\Model
{
	public static function getByTerrainAndCr($terrain,$cr) {
		if ($terrain == 'all') {
			return self::findByCr($cr);
		}
		return self::find('cr='.$cr.' AND terrains LIKE "%'.$terrain.'%"');
	}
	
	public static function getByTerrain($terrain) {
		return self::find('terrains LIKE "%'.$terrain.'%"');
	}
	
	public static function validTerrains() {
		$valid = array('arctic', 'coastal', 'desert', 'forest', 'grassland', 'hill', 'mountain', 'swamp', 'underdark', 'underwater', 'urban');
		$returnVar = array();
		foreach($valid as $terrain) {
			$returnVar[$terrain] = ucfirst($terrain);
		}
		return $returnVar;
	}
	
	public static function validTerrainsWithAllOption() {
		return array_merge(array('all'=>'All'), self::validTerrains());
	}
	
	public static function validCRs() {
		$output = array(0.125, 0.25, 0.5);
		for($i=1; $i<=24; $i++) {
			$output[] = $i;
		}
		$output[] = 30;
		return $output;
	}
}