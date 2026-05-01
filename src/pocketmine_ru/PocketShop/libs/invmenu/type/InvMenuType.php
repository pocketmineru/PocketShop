<?php

declare(strict_types=1);

namespace pocketmine_ru\PocketShop\libs\invmenu\type;

use pocketmine_ru\PocketShop\libs\invmenu\InvMenu;
use pocketmine_ru\PocketShop\libs\invmenu\type\graphic\InvMenuGraphic;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

interface InvMenuType{

	public function createGraphic(InvMenu $menu, Player $player) : ?InvMenuGraphic;

	public function createInventory() : Inventory;
}