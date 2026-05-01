<?php

declare(strict_types=1);

namespace pocketmine_ru\PocketShop\libs\invmenu\session;

use pocketmine_ru\PocketShop\libs\invmenu\InvMenu;
use pocketmine_ru\PocketShop\libs\invmenu\type\graphic\InvMenuGraphic;

final class InvMenuInfo{

	public function __construct(
		readonly public InvMenu $menu,
		readonly public InvMenuGraphic $graphic,
		readonly public ?string $graphic_name
	){}
}