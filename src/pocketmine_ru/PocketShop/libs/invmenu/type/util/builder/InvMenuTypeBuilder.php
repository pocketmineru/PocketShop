<?php

declare(strict_types=1);

namespace pocketmine_ru\PocketShop\libs\invmenu\type\util\builder;

use pocketmine_ru\PocketShop\libs\invmenu\type\InvMenuType;

interface InvMenuTypeBuilder{

	public function build() : InvMenuType;
}