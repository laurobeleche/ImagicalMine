<?php
/**
 * src/pocketmine/item/GoldHelmet.php
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

namespace pocketmine\item;


class GoldHelmet extends Armor{

	/**
	 *
	 * @param unknown $meta  (optional)
	 * @param unknown $count (optional)
	 */
	public function __construct($meta = 0, $count = 1) {
		parent::__construct(self::GOLD_HELMET, $meta, $count, "Gold Helmet");
	}


	/**
	 *
	 * @return unknown
	 */
	public function isHelmet() {
		return self::TIER_GOLD;
	}


	/**
	 *
	 * @return unknown
	 */
	public function getProtection() {
		return parent::getProtection() + 2;
	}


	/**
	 *
	 * @return unknown
	 */
	public function getMaxDurability() {
		return 78;
	}


}
