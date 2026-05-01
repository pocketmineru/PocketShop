<?php

declare(strict_types=1);

namespace pocketmine_ru\PocketShop\libs\invmenu\type\graphic\network;

use InvalidArgumentException;
use pocketmine_ru\PocketShop\libs\invmenu\session\InvMenuInfo;
use pocketmine_ru\PocketShop\libs\invmenu\session\PlayerSession;
use pocketmine_ru\PocketShop\libs\invmenu\type\graphic\PositionedInvMenuGraphic;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;

final class BlockInvMenuGraphicNetworkTranslator implements InvMenuGraphicNetworkTranslator{

	public static function instance() : self{
		static $instance = null;
		return $instance ??= new self();
	}

	private function __construct(){
	}

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void{
		$graphic = $current->graphic;
		$graphic instanceof PositionedInvMenuGraphic || throw new InvalidArgumentException("Expected " . PositionedInvMenuGraphic::class . ", got " . $graphic::class);
		$pos = $graphic->getPosition();
		$packet->blockPosition = BlockPosition::fromVector3($pos);
	}
}