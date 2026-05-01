<?php

declare(strict_types=1);

namespace pocketmine_ru\PocketShop\libs\invmenu\session\network\handler;

use Closure;
use pocketmine_ru\PocketShop\libs\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then, int $protocolId) : NetworkStackLatencyEntry;
}