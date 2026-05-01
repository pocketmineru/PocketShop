<?php

declare(strict_types=1);

namespace pocketmine_ru\PocketShop\libs\invmenu\type\graphic\network;

use pocketmine_ru\PocketShop\libs\invmenu\session\InvMenuInfo;
use pocketmine_ru\PocketShop\libs\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

final class MultiInvMenuGraphicNetworkTranslator implements InvMenuGraphicNetworkTranslator{

	/**
	 * @param InvMenuGraphicNetworkTranslator[] $translators
	 */
	public function __construct(
		readonly private array $translators
	){}

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void{
		foreach($this->translators as $translator){
			$translator->translate($session, $current, $packet);
		}
	}
}