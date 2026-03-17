# 🤖 Qonun Bot

Telegram guruhlarini **bot va nakrutka**lardan himoya qiluvchi PHP bot.  
Yangi a'zo qo'shilganda inson ekanligini tasdiqlashini talab qiladi, aks holda 1 soat ichida guruhdan chiqariladi.

---

## ⚙️ Qanday ishlaydi?

1. Yangi odam guruhga qo'shiladi
2. Bot uning **yozish huquqini bloklaydi**
3. "✅ Men insonman" tugmali xabar yuboradi
4. Foydalanuvchi tugmani bosadi → **huquqlar qaytariladi**
5. **1 soat** ichida bosmasa → guruhdan **chiqariladi**

---

## 📁 Fayl tuzilmasi

```
├── bot.php          # Asosiy webhook handler
├── config.php       # Token va sozlamalar
├── db.php           # SQLite baza (pending foydalanuvchilar)
├── cron.php         # 1 soat o'tganlarni chiqarish (cron job)
└── set_webhook.php  # Webhook o'rnatish
```

---

## 🚀 O'rnatish

### 1. Token sozlash
`config.php` faylida tokenni o'zgartiring:
```php
define('BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE');
```

### 2. Webhook o'rnatish
`set_webhook.php` da domenni kiriting:
```php
$webhookUrl = 'https://sizning-domen.com/bot.php';
```
Keyin brauzerda ochib yuboring:
```
https://sizning-domen.com/set_webhook.php
```

### 3. Cron job qo'shish
Har 5 daqiqada muddati o'tganlarni tekshirish uchun:
```bash
*/5 * * * * php /path/to/cron.php
```

---

## 🔐 Bot huquqlari

Botni guruhga **admin** qilib qo'shing va quyidagi huquqlarni bering:

| Huquq | Kerakmi? |
|-------|----------|
| Foydalanuvchilarni chiqarish | ✅ Ha |
| Xabarlarni o'chirish | ✅ Ha |
| Foydalanuvchilarni cheklash | ✅ Ha |

---

## 🌐 Ko'p guruh qo'llab-quvvatlash

Bot **cheksiz guruhlarda** bir vaqtda ishlaydi.  
Har bir guruh va foydalanuvchi alohida `(user_id, chat_id)` juftligi bilan bazada saqlanadi.

---

## 🛠 Texnologiyalar

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat&logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-003B57?style=flat&logo=sqlite&logoColor=white)
![Telegram](https://img.shields.io/badge/Telegram_Bot_API-2CA5E0?style=flat&logo=telegram&logoColor=white)

---

## 📄 Litsenziya

MIT License © 2026 [Iftix0r](https://github.com/Iftix0r)
