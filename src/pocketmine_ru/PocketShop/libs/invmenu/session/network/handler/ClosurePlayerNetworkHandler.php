<?php

declare(strict_types=1);

namespace pocketmine_ru\PocketShop\libs\invmenu\session\network\handler;

use Closure;
use pocketmine_ru\PocketShop\libs\invmenu\session\network\NetworkStackLatencyEntry;

final class ClosurePlayerNetworkHandler implements PlayerNetworkHandler{

	/**
	 * @param Closure(Closure, int) : NetworkStackLatencyEntry $creator
	 */
	public function __construct(
		readonly private Closure $creator
	){}

	public function createNetworkStackLatencyEntry(Closure $then, int $protocolId) : NetworkStackLatencyEntry{
		return ($this->creator)($then, $protocolId);
	}
}