<?php
/**
 * src/pocketmine/block/Air.php
 *
 * @package default
 */


/*
 *
 *  _                       _           _ __  __ _
 * (_)                     (_)         | |  \/  (_)
 *  _ _ __ ___   __ _  __ _ _  ___ __ _| | \  / |_ _ __   ___
 * | | '_ ` _ \ / _` |/ _` | |/ __/ _` | | |\/| | | '_ \ / _ \
 * | | | | | | | (_| | (_| | | (_| (_| | | |  | | | | | |  __/
 * |_|_| |_| |_|\__,_|\__, |_|\___\__,_|_|_|  |_|_|_| |_|\___|
 *                     __/ |
 *                    |___/
 *
 * This program is a third party build by ImagicalMine.
 *
 * PocketMine is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author ImagicalMine Team
 * @link http://forums.imagicalcorp.ml/
 *
 *
*/

namespace pocketmine\block;

use pocketmine\item\Item;


/**
 * Air block
 */
class Air extends Transparent{

	protected $id = self::AIR;
	protected $meta = 0;

	/**
	 *
	 */
	public function __construct() {

	}


	/**
	 *
	 * @return unknown
	 */
	public function getName() {
		return "Air";
	}


	/**
	 *
	 * @return unknown
	 */
	public function canPassThrough() {
		return true;
	}


	/**
	 *
	 * @param Item    $item
	 * @return unknown
	 */
	public function isBreakable(Item $item) {
		return false;
	}


	/**
	 *
	 * @return unknown
	 */
	public function canBeFlowedInto() {
		return true;
	}


	/**
	 *
	 * @return unknown
	 */
	public function canBeReplaced() {
		return true;
	}


	/**
	 *
	 * @return unknown
	 */
	public function canBePlaced() {
		return false;
	}


	/**
	 *
	 * @return unknown
	 */
	public function isSolid() {
		return false;
	}


	/**
	 *
	 * @return unknown
	 */
	public function getBoundingBox() {
		return null;
	}


	/**
	 *
	 * @return unknown
	 */
	public function getHardness() {
		return -1;
	}


	/**
	 *
	 * @return unknown
	 */
	public function getResistance() {
		return 0;
	}


}
