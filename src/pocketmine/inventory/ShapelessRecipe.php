<?php
/**
 * src/pocketmine/inventory/ShapelessRecipe.php
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

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\Server;
use pocketmine\utils\UUID;

class ShapelessRecipe implements Recipe{
	/** @var Item */
	private $output;

	private $id = null;

	/** @var Item[] */
	private $ingredients = [];

	/**
	 *
	 * @param Item    $result
	 */
	public function __construct(Item $result) {
		$this->output = clone $result;
	}


	/**
	 *
	 * @return unknown
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 *
	 * @param UUID    $id
	 */
	public function setId(UUID $id) {
		if ($this->id !== null) {
			throw new \InvalidStateException("Id is already set");
		}

		$this->id = $id;
	}


	/**
	 *
	 * @return unknown
	 */
	public function getResult() {
		return clone $this->output;
	}


	/**
	 *
	 * @throws \InvalidArgumentException
	 * @returns ShapelessRecipe
	 * @param Item    $item
	 */
	public function addIngredient(Item $item) {
		if (count($this->ingredients) >= 9) {
			throw new \InvalidArgumentException("Shapeless recipes cannot have more than 9 ingredients");
		}

		$it = clone $item;
		$it->setCount(1);

		while ($item->getCount() > 0) {
			$this->ingredients[] = clone $it;
			$item->setCount($item->getCount() - 1);
		}

		return $this;
	}


	/**
	 *
	 * @param Item    $item
	 * @return $this
	 */
	public function removeIngredient(Item $item) {
		foreach ($this->ingredients as $index => $ingredient) {
			if ($item->getCount() <= 0) {
				break;
			}
			if ($ingredient->equals($item, $item->getDamage() === null ? false : true, $item->getCompoundTag() === null ? false : true)) {
				unset($this->ingredients[$index]);
				$item->setCount($item->getCount() - 1);
			}
		}

		return $this;
	}


	/**
	 *
	 * @return Item[]
	 */
	public function getIngredientList() {
		$ingredients = [];
		foreach ($this->ingredients as $ingredient) {
			$ingredients[] = clone $ingredient;
		}

		return $ingredients;
	}


	/**
	 *
	 * @return int
	 */
	public function getIngredientCount() {
		$count = 0;
		foreach ($this->ingredients as $ingredient) {
			$count += $ingredient->getCount();
		}

		return $count;
	}


	/**
	 *
	 */
	public function registerToCraftingManager() {
		Server::getInstance()->getCraftingManager()->registerShapelessRecipe($this);
	}


}
