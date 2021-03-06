<?php
/**
 * src/pocketmine/block/TrappedChest.php
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
use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\NBT;
use pocketmine\Player;
use pocketmine\tile\TrappedChest as TileChest;
use pocketmine\tile\Tile;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;

class TrappedChest extends Transparent{

	protected $id = self::TRAPPED_CHEST;

	/**
	 *
	 * @param unknown $meta (optional)
	 */
	public function __construct($meta = 0) {
		$this->meta = $meta;
	}


	/**
	 *
	 * @return unknown
	 */
	public function canBeActivated() {
		return true;
	}


	/**
	 *
	 * @return unknown
	 */
	public function getHardness() {
		return 2.5;
	}


	/**
	 *
	 * @return unknown
	 */
	public function getName() {
		return "Trapped Chest";
	}


	/**
	 *
	 * @return unknown
	 */
	public function getToolType() {
		return Tool::TYPE_AXE;
	}


	/**
	 *
	 * @return unknown
	 */
	protected function recalculateBoundingBox() {
		return new AxisAlignedBB(
			$this->x + 0.0625,
			$this->y,
			$this->z + 0.0625,
			$this->x + 0.9375,
			$this->y + 0.9475,
			$this->z + 0.9375
		);
	}


	/**
	 *
	 * @param Item    $item
	 * @param Block   $block
	 * @param Block   $target
	 * @param unknown $face
	 * @param unknown $fx
	 * @param unknown $fy
	 * @param unknown $fz
	 * @param Player  $player (optional)
	 * @return unknown
	 */
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null) {
		$faces = [
			0 => 4,
			1 => 2,
			2 => 5,
			3 => 3,
		];

		$chest = null;
		$this->meta = $faces[$player instanceof Player ? $player->getDirection() : 0];

		for ($side = 2; $side <= 5; ++$side) {
			if (($this->meta === 4 or $this->meta === 5) and ($side === 4 or $side === 5)) {
				continue;
			}elseif (($this->meta === 3 or $this->meta === 2) and ($side === 2 or $side === 3)) {
				continue;
			}
			$c = $this->getSide($side);
			if ($c instanceof Chest and $c->getDamage() === $this->meta) {
				$tile = $this->getLevel()->getTile($c);
				if ($tile instanceof TileChest and !$tile->isPaired()) {
					$chest = $tile;
					break;
				}
			}
		}

		$this->getLevel()->setBlock($block, $this, true, true);
		$nbt = new CompoundTag("", [
				new ListTag("Items", []),
				new StringTag("id", Tile::TRAPPED_CHEST),
				new IntTag("x", $this->x),
				new IntTag("y", $this->y),
				new IntTag("z", $this->z)
			]);
		$nbt->Items->setTagType(NBT::TAG_Compound);

		if ($item->hasCustomName()) {
			$nbt->CustomName = new StringTag("CustomName", $item->getCustomName());
		}

		if ($item->hasCustomBlockData()) {
			foreach ($item->getCustomBlockData() as $key => $v) {
				$nbt->{$key} = $v;
			}
		}

		$tile = Tile::createTile("Trapped Chest", $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);

		if ($chest instanceof TileChest and $tile instanceof TileChest) {
			$chest->pairWith($tile);
			$tile->pairWith($chest);
		}

		return true;
	}


	/**
	 *
	 * @param Item    $item
	 * @return unknown
	 */
	public function onBreak(Item $item) {
		$t = $this->getLevel()->getTile($this);
		if ($t instanceof TileChest) {
			$t->unpair();
		}
		$this->getLevel()->setBlock($this, new Air(), true, true);

		return true;
	}


	/**
	 *
	 * @param Item    $item
	 * @param Player  $player (optional)
	 * @return unknown
	 */
	public function onActivate(Item $item, Player $player = null) {
		if ($player instanceof Player) {
			$top = $this->getSide(1);
			if ($top->isTransparent() !== true) {
				return true;
			}

			$t = $this->getLevel()->getTile($this);
			$chest = null;
			if ($t instanceof TileChest) {
				$chest = $t;
			}else {
				$nbt = new CompoundTag("", [
						new ListTag("Items", []),
						new StringTag("id", Tile::TRAPPED_CHEST),
						new IntTag("x", $this->x),
						new IntTag("y", $this->y),
						new IntTag("z", $this->z)
					]);
				$nbt->Items->setTagType(NBT::TAG_Compound);
				$chest = Tile::createTile("Trapped Chest", $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);
			}

			if (isset($chest->namedtag->Lock) and $chest->namedtag->Lock instanceof StringTag) {
				if ($chest->namedtag->Lock->getValue() !== $item->getCustomName()) {
					return true;
				}
			}

			if ($player->isCreative()) {
				return true;
			}

			if ($chest !== null) {
				$player->addWindow($chest->getInventory());
			}
		}

		return true;
	}


	/**
	 *
	 * @param Item    $item
	 * @return unknown
	 */
	public function getDrops(Item $item) {
		return [
			[$this->id, 0, 1],
		];
	}


}
