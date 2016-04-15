<?php
/**
 * src/pocketmine/event/TextContainer.php
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

namespace pocketmine\event;

class TextContainer {

	/** @var string $text */
	protected $text;

	/**
	 *
	 * @param unknown $text
	 */
	public function __construct($text) {
		$this->text = $text;
	}


	/**
	 *
	 * @param unknown $text
	 */
	public function setText($text) {
		$this->text = $text;
	}


	/**
	 *
	 * @return string
	 */
	public function gettext() {
		return $this->text;
	}


	/**
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->getText();
	}


}
