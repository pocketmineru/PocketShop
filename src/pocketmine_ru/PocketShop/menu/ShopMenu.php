<?php

declare(strict_types=1);

namespace pocketmine_ru\PocketShop\menu;

use pocketmine\player\Player;
use pocketmine\item\StringToItemParser;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\Armor;
use pocketmine\item\Potion;
use pocketmine\item\PotionType;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\color\Color;
use pocketmine\block\VanillaBlocks;
use pocketmine_ru\PocketShop\Main;
use pocketmine_ru\PocketShop\libs\invmenu\InvMenu;
use pocketmine_ru\PocketShop\libs\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine_ru\PocketShop\libs\invmenu\type\InvMenuTypeIds;
use pocketmine_ru\PocketShop\libs\invmenu\inventory\InvMenuInventory;

class ShopMenu {

    private const CATEGORIES = [
        "blocks" => ["name" => "§r§eБлоки"],
        "items" => ["name" => "§r§bПредметы"],
        "tools" => ["name" => "§r§6Инструменты"],
        "armor" => ["name" => "§r§cБроня"],
        "swords" => ["name" => "§r§dОружие"],
        "dyes" => ["name" => "§r§6Красители"],
        "food" => ["name" => "§r§aЕда"],
        "potions" => ["name" => "§r§5Зелья"],
    ];

    private const ITEMS_PER_PAGE = 54;

    private Main $plugin;
    private array $currentCategory = [];
    private array $currentPage = [];
    private array $currentItems = [];
    private array $buyQuantity = [];

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function getPlugin(): Main {
        return $this->plugin;
    }

    public function openMainMenu(Player $player): void {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName("§8§l⬥ §6Магазин §fPocketShop §8⬥");

        $inventory = $menu->getInventory();

        $glassColors = [
            "white_stained_glass_pane", "orange_stained_glass_pane", "magenta_stained_glass_pane",
            "light_blue_stained_glass_pane", "yellow_stained_glass_pane", "lime_stained_glass_pane",
            "pink_stained_glass_pane", "gray_stained_glass_pane", "silver_stained_glass_pane"
        ];

        $borderSlots = array_merge(
            range(0, 8),
            range(45, 53),
            [9, 18, 27, 36],
            [17, 26, 35, 44]
        );
        foreach ($borderSlots as $index => $slot) {
            $colorName = $glassColors[$index % count($glassColors)];
            $glass = $this->parseItem($colorName);
            $glass->setCustomName("§r");
            $inventory->setItem($slot, $glass);
        }

        $money = $this->plugin->getPlayerMoney($player);
        $balanceItem = $this->parseItem("gold_ingot");
        $balanceItem->setCustomName("§r§e§lБаланс: §a" . number_format($money) . " §fмонет");
        $balanceItem->setLore(["§r§7Ваш текущий баланс"]);
        $inventory->setItem(49, $balanceItem);

        $centerSlots = [10, 11, 12, 13, 14, 15, 16, 19, 20, 21, 22, 23, 24, 25, 28, 29, 30, 31, 32, 33, 34, 37, 38, 39, 40, 41, 42, 43];
        $categories = array_keys(self::CATEGORIES);

        foreach ($centerSlots as $i => $slot) {
            if (!isset($categories[$i])) break;
            $catKey = $categories[$i];
            $cat = self::CATEGORIES[$catKey];
            $categoryItem = $this->createCategoryItem($catKey, $cat);
            $inventory->setItem($slot, $categoryItem);
        }

        $menu->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction) use ($player, $categories): void {
            $slot = $transaction->getAction()->getSlot();

            if ($slot === 49) {
                return;
            }

            $centerSlots = [10, 11, 12, 13, 14, 15, 16, 19, 20, 21, 22, 23, 24, 25, 28, 29, 30, 31, 32, 33, 34, 37, 38, 39, 40, 41, 42, 43];
            $index = array_search($slot, $centerSlots);
            if ($index !== false && isset($categories[$index])) {
                $this->openCategory($player, $categories[$index]);
            }
        }));

        $menu->send($player);
    }

    public function openCategory(Player $player, string $category): void {
        $this->currentCategory[$player->getName()] = $category;
        $this->currentPage[$player->getName()] = 0;

        $cat = self::CATEGORIES[$category] ?? ["name" => "§fМагазин", "size" => 27];
        $items = $this->getItems($category);

        if (empty($items)) {
            $player->sendMessage($this->plugin->getMessage("category_empty"));
            $this->openMainMenu($player);
            return;
        }

        $this->currentItems[$player->getName()] = $items;
        $this->showCategoryPage($player, $category, 0);
    }

    private function showCategoryPage(Player $player, string $category, int $page): void {
        $cat = self::CATEGORIES[$category] ?? ["name" => "§fМагазин", "size" => 27];
        $allItems = $this->currentItems[$player->getName()] ?? [];
        $totalItems = count($allItems);
        $totalPages = max(1, (int) ceil($totalItems / 28));
        $page = max(0, min($page, $totalPages - 1));

        $this->currentPage[$player->getName()] = $page;

        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName($cat["name"] . " §8| §fPocketShop");

        $inventory = $menu->getInventory();

        $borderItem = $this->parseItem("black_stained_glass_pane");
        $borderItem->setCustomName("§r");

        $borderSlots = array_merge(
            range(0, 8),
            range(45, 53),
            [9, 18, 27, 36],
            [17, 26, 35, 44]
        );
        foreach ($borderSlots as $slot) {
            $inventory->setItem($slot, $borderItem);
        }

        $navItem = $this->parseItem("arrow");
        $navItem->setCustomName("§c§l◀ Назад в меню");
        $inventory->setItem(49, $navItem);

        $prevSlot = 48;
        $nextSlot = 50;

        if ($page > 0) {
            $prevItem = $this->parseItem("arrow");
            $prevItem->setCustomName("§e§l◀ Пред. страница");
            $inventory->setItem($prevSlot, $prevItem);
        }

        if ($page < $totalPages - 1) {
            $nextItem = $this->parseItem("arrow");
            $nextItem->setCustomName("§e§lСлед. страница ►");
            $inventory->setItem($nextSlot, $nextItem);
        }

        $infoItem = $this->parseItem("paper");
        $infoItem->setCustomName("§e§lСтраница " . ($page + 1) . "§a/§e" . $totalPages);
        $infoItem->setLore(["§7Всего товаров: §e$totalItems"]);
        $inventory->setItem(53, $infoItem);

        $startIndex = $page * 28;
        $pageItems = array_slice($allItems, $startIndex, 28);

        $centerSlots = [10, 11, 12, 13, 14, 15, 16, 19, 20, 21, 22, 23, 24, 25, 28, 29, 30, 31, 32, 33, 34, 37, 38, 39, 40, 41, 42, 43];
        foreach ($centerSlots as $index => $slot) {
            if (isset($pageItems[$index])) {
                $item = $this->createShopItem($pageItems[$index]);
                $inventory->setItem($slot, $item);
            }
        }

        $centerSlots = [10, 11, 12, 13, 14, 15, 16, 19, 20, 21, 22, 23, 24, 25, 28, 29, 30, 31, 32, 33, 34, 37, 38, 39, 40, 41, 42, 43];

        $menu->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction) use ($player, $category, $page, $totalPages, $allItems, $centerSlots): void {
            $slot = $transaction->getAction()->getSlot();

            if ($slot === 49) {
                $this->openMainMenu($player);
                return;
            }

            if ($slot === 48 && $page > 0) {
                $this->showCategoryPage($player, $category, $page - 1);
                return;
            }

            if ($slot === 50 && $page < $totalPages - 1) {
                $this->showCategoryPage($player, $category, $page + 1);
                return;
            }

            $centerIndex = array_search($slot, $centerSlots);
            if ($centerIndex !== false) {
                $itemIndex = $page * 28 + $centerIndex;
                if (isset($allItems[$itemIndex])) {
                    $this->openBuyConfirm($player, $allItems[$itemIndex]);
                }
            }
        }));

        $menu->send($player);
    }

public function openBuyConfirm(Player $player, array $itemData): void {
        $itemName = $itemData["name"] ?? "Неизвестно";
        $basePrice = (int) ($itemData["price"] ?? 0);
        $baseAmount = (int) ($itemData["amount"] ?? 1);
        $money = $this->plugin->getPlayerMoney($player);

        $playerName = $player->getName();
        $this->buyQuantity[$playerName] = $baseAmount;
        $quantity = $baseAmount;

        $this->renderBuyMenu($player, $itemData, $quantity);
    }

    private function renderBuyMenu(Player $player, array $itemData, int $quantity): void {
        $itemName = $itemData["name"] ?? "Неизвестно";
        $basePrice = (int) ($itemData["price"] ?? 0);
        $baseAmount = (int) ($itemData["amount"] ?? 1);
        $money = $this->plugin->getPlayerMoney($player);
        $totalPrice = (int) ceil($basePrice * ($quantity / $baseAmount));
        $canAfford = $money >= $totalPrice;

        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName("§a§lПокупка: §f" . $itemName);

        $inventory = $menu->getInventory();

        $borderSlots = array_merge(
            range(0, 8),
            range(45, 53),
            [9, 18, 27, 36],
            [17, 26, 35, 44]
        );
        foreach ($borderSlots as $slot) {
            $border = $this->parseItem("black_stained_glass_pane");
            $border->setCustomName("§r");
            $inventory->setItem($slot, $border);
        }

        $item = $this->parseItem($itemData["item"] ?? "diamond");
        $item->setCustomName("§r§f" . $itemName);
        $item->setLore(["§r§7Покупка"]);
        $inventory->setItem(22, $item);

        $minus16 = $this->parseItem("redstone");
        $minus16->setCustomName("§c§l-16");
        $minus16->setLore(["§r§7Убрать 16 шт"]);
        $inventory->setItem(19, $minus16);

        $minus1 = $this->parseItem("redstone");
        $minus1->setCustomName("§c§l-1");
        $minus1->setLore(["§r§7Убрать 1 шт"]);
        $inventory->setItem(20, $minus1);

        $qtyDisplay = $this->parseItem("paper");
        $qtyDisplay->setCustomName("§e§lКоличество: §f" . $quantity);
        $qtyDisplay->setLore(["§r§7Базовое: " . $baseAmount . " шт"]);
        $inventory->setItem(21, $qtyDisplay);

        $plus1 = $this->parseItem("emerald");
        $plus1->setCustomName("§a§l+1");
        $plus1->setLore(["§r§7Добавить 1 шт"]);
        $inventory->setItem(23, $plus1);

        $plus16 = $this->parseItem("emerald");
        $plus16->setCustomName("§a§l+16");
        $plus16->setLore(["§r§7Добавить 16 шт"]);
        $inventory->setItem(24, $plus16);

        $priceDisplay = $this->parseItem("gold_ingot");
        $priceDisplay->setCustomName("§e§lИтого: §6" . number_format($totalPrice));
        $priceDisplay->setLore(["§r§7Цена за шт: §f" . number_format($basePrice)]);
        $inventory->setItem(25, $priceDisplay);

        $balanceDisplay = $this->parseItem("gold_ingot");
        $balanceDisplay->setCustomName("§e§lБаланс: §a" . number_format($money));
        $balanceDisplay->setLore(["§r§7Доступно"]);
        $inventory->setItem(49, $balanceDisplay);

        $confirmSlot = 40;
        if ($canAfford) {
            $confirm = $this->parseItem("emerald_block");
            $confirm->setCustomName("§a§l✓ Купить");
            $confirm->setLore(["§r§7Нажмите для покупки"]);
        } else {
            $confirm = $this->parseItem("redstone_block");
            $confirm->setCustomName("§c§l✗ Недостаточно");
            $confirm->setLore(["§r§7Не хватает: §c" . number_format($totalPrice - $money)]);
        }
        $inventory->setItem($confirmSlot, $confirm);

        $cancel = $this->parseItem("barrier");
        $cancel->setCustomName("§c§l✗ Назад");
        $cancel->setLore(["§r§7Вернуться в категорию"]);
        $inventory->setItem(53, $cancel);

        $menu->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction) use ($player, $itemData, $quantity, $baseAmount, $basePrice, $canAfford): void {
            $slot = $transaction->getAction()->getSlot();
            $playerName = $player->getName();
            $currentQty = $this->buyQuantity[$playerName] ?? $baseAmount;

            switch ($slot) {
                case 19:
                    $this->buyQuantity[$playerName] = max($baseAmount, $currentQty - 16);
                    break;
                case 20:
                    $this->buyQuantity[$playerName] = max($baseAmount, $currentQty - 1);
                    break;
                case 23:
                    $this->buyQuantity[$playerName] = min(64, $currentQty + 1);
                    break;
                case 24:
                    $this->buyQuantity[$playerName] = min(64, $currentQty + 16);
                    break;
                case 53:
                    $category = $this->currentCategory[$playerName] ?? "blocks";
                    $this->openCategory($player, $category);
                    return;
                case 40:
                    if ($canAfford) {
                        $this->buyQuantity[$playerName] = $currentQty;
                        $this->buyItem($player, $itemData, $currentQty);
                    }
                    return;
                default:
                    return;
            }

            $this->renderBuyMenu($player, $itemData, $this->buyQuantity[$playerName]);
        }));

        $menu->send($player);
    }

    public function buyItem(Player $player, array $itemData, int $quantity): void {
        $itemName = $itemData["name"] ?? "Неизвестно";
        $basePrice = (int) ($itemData["price"] ?? 0);
        $baseAmount = (int) ($itemData["amount"] ?? 1);
        $itemId = $itemData["item"] ?? "diamond";

        $totalPrice = (int) ceil($basePrice * ($quantity / $baseAmount));
        $money = $this->plugin->getPlayerMoney($player);

        if ($money < $totalPrice || !$this->plugin->reduceMoney($player, (float) $totalPrice)) {
            $player->sendMessage($this->plugin->getMessage("no_money", [
                "{price}" => number_format($totalPrice),
                "{balance}" => number_format($money)
            ]));
            $this->openMainMenu($player);
            return;
        }

        $item = $this->parseItem($itemId, 1);
        if ($item === null || $item->isNull()) {
            return;
        }

        if (isset($itemData["color"]) && $item instanceof Armor) {
            $color = $this->getColorFromName($itemData["color"]);
            if ($color !== null) {
                $item->setCustomColor($color);
            }
        }

        if (isset($itemData["enchantments"]) && is_array($itemData["enchantments"])) {
            foreach ($itemData["enchantments"] as $enchantData) {
                $enchantment = $this->getEnchantment($enchantData);
                if ($enchantment !== null) {
                    $level = (int) ($enchantData["level"] ?? 1);
                    $item->addEnchantment(new EnchantmentInstance($enchantment, $level));
                }
            }
        }

        $player->getInventory()->addItem($item->setCount($quantity));

        $player->sendMessage($this->plugin->getMessage("success_buy", [
            "{item}" => $itemName,
            "{amount}" => $quantity,
            "{price}" => number_format($totalPrice)
        ]));

        unset($this->buyQuantity[$player->getName()]);
        $player->removeCurrentWindow();
    }

    private function createCategoryItem(string $categoryKey, array $category): \pocketmine\item\Item {
        $itemId = match ($categoryKey) {
            "blocks" => "cobblestone",
            "items" => "diamond",
            "tools" => "diamond_pickaxe",
            "armor" => "diamond_chestplate",
            "swords" => "diamond_sword",
            "dyes" => "lapis_lazuli",
            "food" => "golden_apple",
            "potions" => "potion",
            default => "diamond",
        };
        $item = $this->parseItem($itemId);
        $item->setCustomName("§r" . $category["name"]);
        $item->setLore(["§r§7Нажмите для просмотра"]);
        return $item;
    }

    private function createShopItem(array $itemData): \pocketmine\item\Item {
        $itemId = $itemData["item"] ?? "diamond";
        $item = $this->parseItem($itemId);

        $name = $itemData["name"] ?? "Неизвестно";
        $price = number_format((int) ($itemData["price"] ?? 0));
        $amount = (int) ($itemData["amount"] ?? 1);

        $item->setCustomName("§r" . $name);
        $item->setLore([
            "§r§7Цена: §e$price монет",
            "§r§7Количество: §e$amount",
            "",
            "§r§aНажмите для покупки"
        ]);

        return $item;
    }

    private function getItems(string $section): array {
        return $this->plugin->getShopConfig()->get($section, []);
    }

    private function getColorFromName(string $colorName): ?Color {
        return match ($colorName) {
            "RED" => new Color(255, 50, 50),
            "BLUE" => new Color(50, 50, 255),
            "GREEN" => new Color(50, 255, 50),
            "YELLOW" => new Color(255, 255, 50),
            "BLACK" => new Color(50, 50, 50),
            "WHITE" => new Color(255, 255, 255),
            "ORANGE" => new Color(255, 165, 50),
            "PURPLE" => new Color(180, 50, 255),
            "PINK" => new Color(255, 182, 203),
            "CYAN" => new Color(50, 255, 255),
            "LIME" => new Color(182, 255, 50),
            default => null,
        };
    }

    private function parseItem(string $itemId, int $amount = 1): \pocketmine\item\Item {
        $itemId = str_replace("minecraft:", "", $itemId);

        $potionType = null;
        $isSplash = false;

        if (str_starts_with($itemId, "potion_")) {
            $potionType = $this->getPotionType(substr($itemId, 7));
        } elseif (str_starts_with($itemId, "splash_potion") || str_starts_with($itemId, "splash_potion_")) {
            $isSplash = true;
            $suffix = str_starts_with($itemId, "splash_potion_") ? substr($itemId, 14) : substr($itemId, 13);
            $potionType = $this->getPotionType($suffix);
        } elseif (str_ends_with($itemId, "_potion")) {
            $potionType = $this->getPotionType(substr($itemId, 0, -7));
        } elseif (str_starts_with($itemId, "splash_") && str_contains($itemId, "_potion")) {
            $isSplash = true;
            $potionType = $this->getPotionType(substr($itemId, 6, -7));
        }

        if ($potionType !== null) {
            $parser = StringToItemParser::getInstance();
            $baseItem = $isSplash ? $parser->parse("splash_potion") : $parser->parse("potion");
            if ($baseItem !== null && method_exists($baseItem, 'setType')) {
                $baseItem->setType($potionType);
                $baseItem->setCount($amount);
                return $baseItem;
            }
        }

        try {
            $parser = LegacyStringToItemParser::getInstance();
            $item = $parser->parse($itemId);
            if ($item !== null) {
                return $item->setCount($amount);
            }
        } catch (\Exception $e) {
        }

        $parser = StringToItemParser::getInstance();
        $item = $parser->parse($itemId);
        if ($item !== null) {
            return $item->setCount($amount);
        }

        return VanillaBlocks::AIR()->asItem();
    }

    private function getPotionType(string $type): ?PotionType {
        return match (strtolower($type)) {
            "speed", "swiftness" => PotionType::SWIFTNESS(),
            "slow", "slowness" => PotionType::SLOWNESS(),
            "strength", "power" => PotionType::STRENGTH(),
            "instant_health", "heal", "healing" => PotionType::HEALING(),
            "instant_damage", "harm", "harming" => PotionType::HARMING(),
            "jump", "jump_boost", "leap" => PotionType::LEAPING(),
            "regen", "regeneration" => PotionType::REGENERATION(),
            "fire_resistance", "fireresistance" => PotionType::FIRE_RESISTANCE(),
            "water_breathing", "waterbreathing" => PotionType::WATER_BREATHING(),
            "invisibility", "invisible" => PotionType::INVISIBILITY(),
            "night_vision", "nightvision" => PotionType::NIGHT_VISION(),
            "weakness", "weak" => PotionType::WEAKNESS(),
            "luck", "lucky" => null,
            "slow_falling", "slowfalling" => PotionType::SLOW_FALLING(),
            "turtle_master", "turtle" => PotionType::TURTLE_MASTER(),
            "poison", "poisonous" => PotionType::POISON(),
            "wither" => PotionType::WITHER(),
            "long_speed", "long_swiftness" => PotionType::LONG_SWIFTNESS(),
            "long_slow", "long_slowness" => PotionType::LONG_SLOWNESS(),
            "long_strength" => PotionType::LONG_STRENGTH(),
            "long_healing" => PotionType::LONG_HEALING(),
            "long_jump", "long_leap", "long_jump_boost" => PotionType::LONG_LEAPING(),
            "long_regen", "long_regeneration" => PotionType::LONG_REGENERATION(),
            "long_fire_resistance", "long_fireresistance" => PotionType::LONG_FIRE_RESISTANCE(),
            "long_water_breathing", "long_waterbreathing" => PotionType::LONG_WATER_BREATHING(),
            "long_invisibility", "long_invisible" => PotionType::LONG_INVISIBILITY(),
            "long_night_vision", "long_nightvision" => PotionType::LONG_NIGHT_VISION(),
            "long_weakness", "long_weak" => PotionType::LONG_WEAKNESS(),
            "long_slow_falling", "long_slowfalling" => PotionType::LONG_SLOW_FALLING(),
            "strong_speed", "strong_swiftness" => PotionType::STRONG_SWIFTNESS(),
            "strong_slow", "strong_slowness" => PotionType::STRONG_SLOWNESS(),
            "strong_strength" => PotionType::STRONG_STRENGTH(),
            "strong_healing" => PotionType::STRONG_HEALING(),
            "strong_harming" => PotionType::STRONG_HARMING(),
            "strong_jump", "strong_leap", "strong_jump_boost" => PotionType::STRONG_LEAPING(),
            "strong_regen", "strong_regeneration" => PotionType::STRONG_REGENERATION(),
            "strong_poison" => PotionType::STRONG_POISON(),
            "strong_turtle_master", "strong_turtle" => PotionType::STRONG_TURTLE_MASTER(),
            default => null,
        };
    }

    private function getEnchantment(array $enchantData): ?\pocketmine\item\enchantment\Enchantment {
        $name = $enchantData["name"] ?? "";
        return match (strtolower($name)) {
            "protection" => VanillaEnchantments::PROTECTION(),
            "sharpness" => VanillaEnchantments::SHARPNESS(),
            "efficiency" => VanillaEnchantments::EFFICIENCY(),
            "unbreaking" => VanillaEnchantments::UNBREAKING(),
            "power" => VanillaEnchantments::POWER(),
            "flame" => VanillaEnchantments::FLAME(),
            "infinity" => VanillaEnchantments::INFINITY(),
            "respiration" => VanillaEnchantments::RESPIRATION(),
            "feather_falling" => VanillaEnchantments::FEATHER_FALLING(),
            "thorns" => VanillaEnchantments::THORNS(),
            default => null,
        };
    }
}