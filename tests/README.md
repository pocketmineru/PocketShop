# PocketShop Tests

## Структура

```
tests/
├── phpunit.xml           # Конфигурация PHPUnit
├── bootstrap.php         # Загрузка зависимостей
└── tests/
    ├── ShopConfigTest.php    # Тесты конфигурации
    └── NavigationTest.php     # Тесты навигации
```

## Запуск тестов

### Установка PHPUnit (если не установлен)

```bash
cd plugins/PocketShop
composer require --dev phpunit/phpunit:^10
```

### Запуск всех тестов

```bash
./vendor/bin/phpunit
```

### Запуск конкретного теста

```bash
./vendor/bin/phpunit tests/tests/ShopConfigTest.php
./vendor/bin/phpunit tests/tests/NavigationTest.php
```

## Тесты

### ShopConfigTest

| Тест | Описание |
|------|----------|
| `testShopConfigExists` | Проверяет существование shop.yml |
| `testMessagesConfigExists` | Проверяет существование messages.yml |
| `testShopConfigIsValidYaml` | Проверяет валидность YAML |
| `testMessagesConfigIsValidYaml` | Проверяет валидность YAML |
| `testAllNineCategoriesExist` | Проверяет наличие 9 категорий |
| `testEachCategoryHasItems` | Проверяет что категории не пустые |
| `testEachItemHasRequiredFields` | Проверяет обязательные поля товаров |
| `testMessagesHavePrefix` | Проверяет наличие префикса |
| `testEssentialMessagesExist` | Проверяет ключевые сообщения |

### NavigationTest

| Тест | Описание |
|------|----------|
| `testPaginationCalculation` | Тесты расчёта страниц |
| `testPageBoundsClamping` | Тесты границ страниц |
| `testSlotCalculationForChest` | Слоты для сундука (27) |
| `testSlotCalculationForHopper` | Слоты для хоппера (45) |
| `testCategorySizes` | Тесты размеров категорий |
| `testNavigationVisibility` | Видимость кнопок prev/next |
| `testItemIndexCalculation` | Расчёт индексов товаров |
| `testEmptyItemsHandling` | Обработка пустых списков |
| `testOverflowItemsHandling` | Обработка переполнения |

## Ожидаемые результаты

```
PHPUnit

OK (18 tests, 50+ assertions)
```

## Примечание

Эти тесты проверяют логику без запуска сервера. Для полного тестирования запустите плагин на сервере и выполните:
- Откройте магазин `/shop`
- Проверьте все 9 категорий
- Протестируйте навигацию по страницам
- Проведите покупку