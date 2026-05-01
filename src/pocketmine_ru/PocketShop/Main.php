<?php

declare(strict_types=1);

namespace pocketmine_ru\PocketShop;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\player\Player;
use onebone\economyapi\EconomyAPI;
use pocketmine_ru\PocketShop\command\ShopCommand;
use pocketmine_ru\PocketShop\menu\ShopMenu;
use pocketmine_ru\PocketShop\libs\invmenu\InvMenuHandler;

class Main extends PluginBase {

    private static self $instance;
    private Config $shopConfig;
    private Config $messagesConfig;
    private ?EconomyAPI $economy = null;
    private ShopMenu $shopMenu;

    public function onLoad(): void {
        self::$instance = $this;
    }

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->saveResource("shop.yml", false);
        $this->saveResource("messages.yml", false);

        $this->shopConfig = new Config($this->getDataFolder() . "shop.yml", Config::YAML);
        $this->messagesConfig = new Config($this->getDataFolder() . "messages.yml", Config::YAML);

        $this->initEconomy();
        $this->initInvMenu();

        $this->shopMenu = new ShopMenu($this);

        $this->getServer()->getCommandMap()->register("pocketshop", new ShopCommand($this));
    }

    private function initEconomy(): void {
        $economyPlugin = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        if ($economyPlugin instanceof EconomyAPI) {
            $this->economy = $economyPlugin;
        }
    }

    private function initInvMenu(): void {
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }
    }

    public function onDisable(): void {
    }

    public static function getInstance(): self {
        return self::$instance;
    }

    public function getShopConfig(): Config {
        return $this->shopConfig;
    }

    public function getMessagesConfig(): Config {
        return $this->messagesConfig;
    }

    public function getEconomy(): ?EconomyAPI {
        return $this->economy;
    }

    public function getShopMenu(): ShopMenu {
        return $this->shopMenu;
    }

    public function getMessage(string $key, array $replacements = []): string {
        $message = $this->messagesConfig->getNested($key, "§cСообщение не найдено: $key");

        $prefix = $this->messagesConfig->getNested("prefix", "§8§l[§6§lPocketShop§8§l]§r§f ");
        if (!str_starts_with($key, "help.") && !str_starts_with($key, "admin.")) {
            $message = $prefix . $message;
        }

        foreach ($replacements as $search => $replace) {
            $message = str_replace($search, (string) $replace, $message);
        }

        return $message;
    }

    public function getPlayerMoney(Player $player): float {
        return $this->economy?->myMoney($player) ?? 0.0;
    }

    public function reduceMoney(Player $player, float $amount): bool {
        if ($this->economy === null) return false;
        return $this->economy->reduceMoney($player, $amount, true) === EconomyAPI::RET_SUCCESS;
    }

    public function openMainShop(Player $player): void {
        $this->shopMenu->openMainMenu($player);
    }
}