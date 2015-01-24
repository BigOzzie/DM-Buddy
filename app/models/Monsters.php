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
}