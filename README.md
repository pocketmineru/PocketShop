# PocketShop

Магазин предметов для PocketMine-MP 5.39+

## Возможности

- 8 категорий товаров (блоки, инструменты, броня, оружие, красители, еда, зелья)
- Выбор количества (1-64)
- DoubleChest меню (54 слота)
- Красивая рамка из цветного стекла
- Пропорциональный пересчёт цены
- Удобная настройка через YAML

## Требования

- PocketMine-MP 5.39+
- PHP 8.1+

## Установка

1. Скачай последнюю версию из releases
2. Распакуй в папку `plugins`
3. Перезапусти сервер

## Конфигурация

Товары настраиваются в `plugins/PocketShop/shop.yml`:

```yaml
blocks:
  - {name: "Камень", item: "stone", price: 10, amount: 64}
  - {name: "Блок травы", item: "grass_block", price: 15, amount: 32}

potions:
  - {name: "Зелье лечения", item: "potion_healing", price: 100, amount: 1}
  - {name: "Зелье скорости", item: "potion_speed", price: 150, amount: 1}
```

## Формат товаров

| Параметр | Описание |
|----------|----------|
| `name` | Название товара |
| `item` | ID предмета (`potion_speed`, `diamond_sword` и т.д.) |
| `price` | Цена за базовое количество |
| `amount` | Базовое количество |
| `color` | Цвет брони (RED, BLUE, GREEN...) |
| `enchantments` | Зачарования (массив с name и level) |

### Пример с зачарованиями

```yaml
swords:
  - name: "Зачарованный алмазный меч"
    item: "diamond_sword"
    price: 5000
    amount: 1
    enchantments:
      - {name: "sharpness", level: 5}
      - {name: "unbreaking", level: 3}
```

## Доступные зелья

- `potion_healing` — зелье лечения
- `potion_speed` — зелье скорости
- `potion_slowness` — зелье замедления
- `potion_strength` — зелье силы
- `potion_regeneration` — зелье регенерации
- `potion_fire_resistance` — зелье огнестойкости
- `potion_invisibility` — зелье невидимости
- `potion_night_vision` — зелье ночного зрения
- `potion_jump` — зелье прыгучести
- `potion_weakness` — зелье слабости
- `potion_water_breathing` — зелье подводного дыхания
- `potion_slow_falling` — зелье медленного падения

## Команды

| Команда | Описание |
|---------|----------|
| `/shop` | Открыть магазин |

## Права

| Право | Описание |
|-------|----------|
| `pocketshop.open` | Открытие магазина |

## Структура проекта

```
PocketShop/
├── src/pocketmine_ru/PocketShop/
│   ├── menu/
│   │   └── ShopMenu.php       # Меню магазина
│   ├── libs/
│   │   └── invmenu/           # Библиотека InvMenu
│   ├── command/
│   │   └── ShopCommand.php    # Команда /shop
│   └── Main.php               # Главный класс
├── resources/
│   └── shop.yml              # Конфиг товаров
├── shop.yml                   # Runtime конфиг
├── plugin.yml                 # Описание плагина
└── README.md
```

## Заказать плагины
💎 Наша Студия — https://vk.me/pocketmine

## Лицензия

MIT
