<?php

declare(strict_types=1);

namespace pocketmine_ru\PocketShop\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine_ru\PocketShop\Main;

class ShopCommand extends Command {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("shop", "Открыть магазин", "/shop", ["магазин", "купить", "store"]);
        $this->setPermission("pocketshop.use");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage($this->plugin->getMessage("player_only"));
            return true;
        }

        $this->plugin->openMainShop($sender);
        return true;
    }
}