<?php

declare(strict_types=1);

namespace pocketmine_ru\PocketShop\libs\invmenu\type\graphic\network;

use pocketmine_ru\PocketShop\libs\invmenu\session\InvMenuInfo;
use pocketmine_ru\PocketShop\libs\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

final class WindowTypeInvMenuGraphicNetworkTranslator implements InvMenuGraphicNetworkTranslator{

	public function __construct(
		readonly private int $window_type
	){}

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void{
		$packet->windowType = $this->window_type;
	}
}