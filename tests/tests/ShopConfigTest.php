<?php

declare(strict_types=1);

namespace pocketmine_ru\PocketShop\tests;

use PHPUnit\Framework\TestCase;

final class ShopConfigTest extends TestCase
{
    private string $pluginPath;
    private string $shopConfigPath;
    private string $messagesConfigPath;

    protected function setUp(): void
    {
        $this->pluginPath = dirname(__DIR__, 2);
        $this->shopConfigPath = $this->pluginPath . '/resources/shop.yml';
        $this->messagesConfigPath = $this->pluginPath . '/resources/messages.yml';
    }

    public function testShopConfigExists(): void
    {
        $this->assertFileExists($this->shopConfigPath, 'shop.yml должен существовать');
    }

    public function testMessagesConfigExists(): void
    {
        $this->assertFileExists($this->messagesConfigPath, 'messages.yml должен существовать');
    }

    public function testShopConfigIsValidYaml(): void
    {
        $content = file_get_contents($this->shopConfigPath);
        $data = yaml_parse($content);

        $this->assertNotFalse($data, 'shop.yml должен быть валидным YAML');
        $this->assertIsArray($data);
    }

    public function testMessagesConfigIsValidYaml(): void
    {
        $content = file_get_contents($this->messagesConfigPath);
        $data = yaml_parse($content);

        $this->assertNotFalse($data, 'messages.yml должен быть валидным YAML');
        $this->assertIsArray($data);
    }

    public function testAllNineCategoriesExist(): void
    {
        $content = file_get_contents($this->shopConfigPath);
        $data = yaml_parse($content);

        $expectedCategories = [
            'blocks', 'items', 'tools', 'armor',
            'swords', 'dyes', 'food', 'potions', 'spawn_eggs'
        ];

        foreach ($expectedCategories as $category) {
            $this->assertArrayHasKey($category, $data, "Категория '$category' должна существовать");
            $this->assertIsArray($data[$category], "Категория '$category' должна быть массивом");
        }
    }

    public function testEachCategoryHasItems(): void
    {
        $content = file_get_contents($this->shopConfigPath);
        $data = yaml_parse($content);

        foreach ($data as $category => $items) {
            $this->assertNotEmpty($items, "Категория '$category' не должна быть пустой");
        }
    }

    public function testEachItemHasRequiredFields(): void
    {
        $content = file_get_contents($this->shopConfigPath);
        $data = yaml_parse($content);

        $requiredFields = ['name', 'item', 'price', 'amount'];

        foreach ($data as $category => $items) {
            foreach ($items as $index => $item) {
                foreach ($requiredFields as $field) {
                    $this->assertArrayHasKey($field, $item,
                        "Товар [$category][$index] должен иметь поле '$field'");
                }

                $this->assertIsString($item['name'], "name должен быть строкой");
                $this->assertIsString($item['item'], "item должен быть строкой");
                $this->assertIsInt($item['price'], "price должен быть числом");
                $this->assertIsInt($item['amount'], "amount должен быть числом");

                $this->assertGreaterThan(0, $item['price'], "price должен быть > 0");
                $this->assertGreaterThan(0, $item['amount'], "amount должен быть > 0");
            }
        }
    }

    public function testMessagesHavePrefix(): void
    {
        $content = file_get_contents($this->messagesConfigPath);
        $data = yaml_parse($content);

        $this->assertArrayHasKey('prefix', $data, 'messages.yml должен иметь prefix');
        $this->assertNotEmpty($data['prefix'], 'prefix не должен быть пустым');
    }

    public function testEssentialMessagesExist(): void
    {
        $content = file_get_contents($this->messagesConfigPath);
        $data = yaml_parse($content);

        $essentialKeys = [
            'player_only',
            'no_money',
            'success_buy',
            'category_empty'
        ];

        foreach ($essentialKeys as $key) {
            $this->assertArrayHasKey($key, $data, "Сообщение '$key' должно существовать");
        }
    }
}