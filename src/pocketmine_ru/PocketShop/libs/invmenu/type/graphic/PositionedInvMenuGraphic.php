<?php

declare(strict_types=1);

namespace pocketmine_ru\PocketShop\libs\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}