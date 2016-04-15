<?php
/**
 * src/pocketmine/utils/BlockIterator.php
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

namespace pocketmine\utils;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

/**
 * This class performs ray tracing and iterates along blocks on a line
 */
class BlockIterator implements \Iterator{

	/** @var Level */
	private $level;
	private $maxDistance;

	private static $gridSize = 16777216; //1 << 24

	private $end = false;

	/** @var \SplFixedArray<Block>[3] */
	private $blockQueue;
	private $currentBlock = 0;
	/** @var Block */
	private $currentBlockObject = null;
	private $currentDistance = 0;
	private $maxDistanceInt = 0;

	private $secondError;
	private $thirdError;

	private $secondStep;
	private $thirdStep;

	private $mainFace;
	private $secondFace;
	private $thirdFace;

	/**
	 *
	 * @param Level   $level
	 * @param Vector3 $start
	 * @param Vector3 $direction
	 * @param unknown $yOffset     (optional)
	 * @param unknown $maxDistance (optional)
	 */
	public function __construct(Level $level, Vector3 $start, Vector3 $direction, $yOffset = 0, $maxDistance = 0) {
		$this->level = $level;
		$this->maxDistance = (int) $maxDistance;
		$this->blockQueue = new \SplFixedArray(3);

		$startClone = new Vector3($start->x, $start->y, $start->z);
		$startClone->y += $yOffset;

		$this->currentDistance = 0;

		$mainDirection = 0;
		$secondDirection = 0;
		$thirdDirection = 0;

		$mainPosition = 0;
		$secondPosition = 0;
		$thirdPosition = 0;

		$pos = new Vector3($startClone->x, $startClone->y, $startClone->z);
		$startBlock = $this->level->getBlock(new Vector3(floor($pos->x), floor($pos->y), floor($pos->z)));

		if ($this->getXLength($direction) > $mainDirection) {
			$this->mainFace = $this->getXFace($direction);
			$mainDirection = $this->getXLength($direction);
			$mainPosition = $this->getXPosition($direction, $startClone, $startBlock);

			$this->secondFace = $this->getYFace($direction);
			$secondDirection = $this->getYLength($direction);
			$secondPosition = $this->getYPosition($direction, $startClone, $startBlock);

			$this->thirdFace = $this->getZFace($direction);
			$thirdDirection = $this->getZLength($direction);
			$thirdPosition = $this->getZPosition($direction, $startClone, $startBlock);
		}
		if ($this->getYLength($direction) > $mainDirection) {
			$this->mainFace = $this->getYFace($direction);
			$mainDirection = $this->getYLength($direction);
			$mainPosition = $this->getYPosition($direction, $startClone, $startBlock);

			$this->secondFace = $this->getZFace($direction);
			$secondDirection = $this->getZLength($direction);
			$secondPosition = $this->getZPosition($direction, $startClone, $startBlock);

			$this->thirdFace = $this->getXFace($direction);
			$thirdDirection = $this->getXLength($direction);
			$thirdPosition = $this->getXPosition($direction, $startClone, $startBlock);
		}
		if ($this->getZLength($direction) > $mainDirection) {
			$this->mainFace = $this->getZFace($direction);
			$mainDirection = $this->getZLength($direction);
			$mainPosition = $this->getZPosition($direction, $startClone, $startBlock);

			$this->secondFace = $this->getXFace($direction);
			$secondDirection = $this->getXLength($direction);
			$secondPosition = $this->getXPosition($direction, $startClone, $startBlock);

			$this->thirdFace = $this->getYFace($direction);
			$thirdDirection = $this->getYLength($direction);
			$thirdPosition = $this->getYPosition($direction, $startClone, $startBlock);
		}

		$d = $mainPosition / $mainDirection;
		$secondd = $secondPosition - $secondDirection * $d;
		$thirdd = $thirdPosition - $thirdDirection * $d;

		$this->secondError = floor($secondd * self::$gridSize);
		$this->secondStep = round($secondDirection / $mainDirection * self::$gridSize);
		$this->thirdError = floor($thirdd * self::$gridSize);
		$this->thirdStep = round($thirdDirection / $mainDirection * self::$gridSize);

		if ($this->secondError + $this->secondStep <= 0) {
			$this->secondError = -$this->secondStep + 1;
		}

		if ($this->thirdError + $this->thirdStep <= 0) {
			$this->thirdError = -$this->thirdStep + 1;
		}

		$lastBlock = $startBlock->getSide(Vector3::getOppositeSide($this->mainFace));

		if ($this->secondError < 0) {
			$this->secondError += self::$gridSize;
			$lastBlock = $lastBlock->getSide(Vector3::getOppositeSide($this->secondFace));
		}

		if ($this->thirdError < 0) {
			$this->thirdError += self::$gridSize;
			$lastBlock = $lastBlock->getSide(Vector3::getOppositeSide($this->thirdFace));
		}

		$this->secondError -= self::$gridSize;
		$this->thirdError -= self::$gridSize;

		$this->blockQueue[0] = $lastBlock;

		$this->currentBlock = -1;

		$this->scan();

		$startBlockFound = false;

		for ($cnt = $this->currentBlock; $cnt >= 0; --$cnt) {
			if ($this->blockEquals($this->blockQueue[$cnt], $startBlock)) {
				$this->currentBlock = $cnt;
				$startBlockFound = true;
				break;
			}
		}

		if (!$startBlockFound) {
			throw new \InvalidStateException("Start block missed in BlockIterator");
		}

		$this->maxDistanceInt = round($maxDistance / (sqrt($mainDirection ** 2 + $secondDirection ** 2 + $thirdDirection ** 2) / $mainDirection));
	}


	/**
	 *
	 * @param Block   $a
	 * @param Block   $b
	 * @return unknown
	 */
	private function blockEquals(Block $a, Block $b) {
		return $a->x === $b->x and $a->y === $b->y and $a->z === $b->z;
	}


	/**
	 *
	 * @param Vector3 $direction
	 * @return unknown
	 */
	private function getXFace(Vector3 $direction) {
		return (($direction->x) > 0) ? Vector3::SIDE_EAST : Vector3::SIDE_WEST;
	}


	/**
	 *
	 * @param Vector3 $direction
	 * @return unknown
	 */
	private function getYFace(Vector3 $direction) {
		return (($direction->y) > 0) ? Vector3::SIDE_UP : Vector3::SIDE_DOWN;
	}


	/**
	 *
	 * @param Vector3 $direction
	 * @return unknown
	 */
	private function getZFace(Vector3 $direction) {
		return (($direction->z) > 0) ? Vector3::SIDE_SOUTH : Vector3::SIDE_NORTH;
	}


	/**
	 *
	 * @param Vector3 $direction
	 * @return unknown
	 */
	private function getXLength(Vector3 $direction) {
		return abs($direction->x);
	}


	/**
	 *
	 * @param Vector3 $direction
	 * @return unknown
	 */
	private function getYLength(Vector3 $direction) {
		return abs($direction->y);
	}


	/**
	 *
	 * @param Vector3 $direction
	 * @return unknown
	 */
	private function getZLength(Vector3 $direction) {
		return abs($direction->z);
	}


	/**
	 *
	 * @param unknown $direction
	 * @param unknown $position
	 * @param unknown $blockPosition
	 * @return unknown
	 */
	private function getPosition($direction, $position, $blockPosition) {
		return $direction > 0 ? ($position - $blockPosition) : ($blockPosition + 1 - $position);
	}


	/**
	 *
	 * @param Vector3 $direction
	 * @param Vector3 $position
	 * @param Block   $block
	 * @return unknown
	 */
	private function getXPosition(Vector3 $direction, Vector3 $position, Block $block) {
		return $this->getPosition($direction->x, $position->x, $block->x);
	}


	/**
	 *
	 * @param Vector3 $direction
	 * @param Vector3 $position
	 * @param Block   $block
	 * @return unknown
	 */
	private function getYPosition(Vector3 $direction, Vector3 $position, Block $block) {
		return $this->getPosition($direction->y, $position->y, $block->y);
	}


	/**
	 *
	 * @param Vector3 $direction
	 * @param Vector3 $position
	 * @param Block   $block
	 * @return unknown
	 */
	private function getZPosition(Vector3 $direction, Vector3 $position, Block $block) {
		return $this->getPosition($direction->z, $position->z, $block->z);
	}


	/**
	 *
	 */
	public function next() {
		$this->scan();

		if ($this->currentBlock <= -1) {
			throw new \OutOfBoundsException;
		}else {
			$this->currentBlockObject = $this->blockQueue[$this->currentBlock--];
		}
	}


	/**
	 *
	 * @throws \OutOfBoundsException
	 * @return Block
	 */
	public function current() {
		if ($this->currentBlockObject === null) {
			throw new \OutOfBoundsException;
		}
		return $this->currentBlockObject;
	}


	/**
	 *
	 */
	public function rewind() {
		throw new \InvalidStateException("BlockIterator doesn't support rewind()");
	}


	/**
	 *
	 * @return unknown
	 */
	public function key() {
		return $this->currentBlock - 1;
	}


	/**
	 *
	 * @return unknown
	 */
	public function valid() {
		$this->scan();
		return $this->currentBlock !== -1;
	}


	/**
	 *
	 */
	private function scan() {
		if ($this->currentBlock >= 0) {
			return;
		}

		if ($this->maxDistance !== 0 and $this->currentDistance > $this->maxDistanceInt) {
			$this->end = true;
			return;
		}

		if ($this->end) {
			return;
		}

		++$this->currentDistance;

		$this->secondError += $this->secondStep;
		$this->thirdError += $this->thirdStep;

		if ($this->secondError > 0 and $this->thirdError > 0) {
			$this->blockQueue[2] = $this->blockQueue[0]->getSide($this->mainFace);

			if (($this->secondStep * $this->thirdError) < ($this->thirdStep * $this->secondError)) {
				$this->blockQueue[1] = $this->blockQueue[2]->getSide($this->secondFace);
				$this->blockQueue[0] = $this->blockQueue[1]->getSide($this->thirdFace);
			}else {
				$this->blockQueue[1] = $this->blockQueue[2]->getSide($this->thirdFace);
				$this->blockQueue[0] = $this->blockQueue[1]->getSide($this->secondFace);
			}

			$this->thirdError -= self::$gridSize;
			$this->secondError -= self::$gridSize;
			$this->currentBlock = 2;
		}elseif ($this->secondError > 0) {
			$this->blockQueue[1] = $this->blockQueue[0]->getSide($this->mainFace);
			$this->blockQueue[0] = $this->blockQueue[1]->getSide($this->secondFace);
			$this->secondError -= self::$gridSize;
			$this->currentBlock = 1;
		}elseif ($this->thirdError > 0) {
			$this->blockQueue[1] = $this->blockQueue[0]->getSide($this->mainFace);
			$this->blockQueue[0] = $this->blockQueue[1]->getSide($this->thirdFace);
			$this->thirdError -= self::$gridSize;
			$this->currentBlock = 1;
		}else {
			$this->blockQueue[0] = $this->blockQueue[0]->getSide($this->mainFace);
			$this->currentBlock = 0;
		}
	}


}
