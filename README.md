# LogVault - نظام إدارة السجلات المركزي

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap">
  <img src="https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
</p>

## 📋 نظرة عامة

LogVault هو نظام مركزي مبني بـ Laravel لجمع وإدارة السجلات من مشاريع متعددة. يوفر واجهة API آمنة لاستقبال السجلات ولوحة تحكم إدارية لعرض وتحليل البيانات.

## ✨ المميزات الرئيسية

### 🔐 الأمان
- **توثيق بالتوكن**: استخدام Laravel Sanctum لتوثيق API
- **IP Whitelist**: فلترة الوصول حسب عناوين IP المسموحة
- **أذونات متدرجة**: صلاحيات مختلفة للقراءة والكتابة والإدارة
- **انتهاء صلاحية التوكنات**: إدارة دورة حياة التوكنات

### 📊 إدارة المشاريع
- دعم مشاريع متعددة في نظام واحد
- إعدادات منفصلة لكل مشروع
- API URLs مخصصة لجلب البيانات من المصدر
- حالة نشاط/إيقاف لكل مشروع

### 📈 التحليلات والتقارير
- إحصائيات شاملة للأنشطة
- رسوم بيانية تفاعلية
- تصدير البيانات (CSV, JSON)
- تحليل الأنشطة حسب الوقت والمستخدم

### 🖥️ واجهة المستخدم
- لوحة تحكم عربية جميلة وحديثة
- تصميم متجاوب يعمل على جميع الأجهزة
- بحث وفلترة متقدمة
- إشعارات فورية

## 🚀 التثبيت والإعداد

### 1. متطلبات النظام
```bash
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL/PostgreSQL/SQLite
```

### 2. تثبيت المشروع
```bash
# نسخ المشروع
git clone <repository-url> log-vault
cd log-vault

# تثبيت dependencies
composer install
npm install

# إنشاء ملف البيئة
cp .env.example .env

# توليد مفتاح التطبيق
php artisan key:generate

# إعداد قاعدة البيانات في .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=log_vault
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. إعداد قاعدة البيانات
```bash
# تشغيل migrations
php artisan migrate

# إنشاء بيانات تجريبية (اختياري)
php artisan db:seed --class=DemoDataSeeder
```

### 4. تشغيل النظام
```bash
# تشغيل الخادم المحلي
php artisan serve

# في terminal آخر - تشغيل Vite للـ assets
npm run dev
```

النظام سيعمل على: `http://localhost:8000`

## 🔑 بيانات الدخول الافتراضية

بعد تشغيل DemoDataSeeder:
- **البريد الإلكتروني**: `admin@logvault.com`
- **كلمة المرور**: `password`

## 📡 استخدام API

### التوثيق
جميع طلبات API تتطلب توثيق باستخدام التوكن:

```http
Authorization: Bearer YOUR_API_TOKEN
```

### أمثلة على استخدام API

#### 1. إرسال سجل جديد
```http
POST /api/v1/logs
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN

{
    "user_type": "App\\Models\\User",
    "user_id": 123,
    "event": "created",
    "auditable_type": "App\\Models\\Student",
    "auditable_id": 456,
    "old_values": null,
    "new_values": {
        "name": "أحمد محمد",
        "email": "ahmed@example.com"
    },
    "url": "/admin/students/456",
    "ip_address": "192.168.1.100",
    "user_agent": "Mozilla/5.0...",
    "source_system": "mini-school"
}
```

#### 2. جلب السجلات
```http
GET /api/v1/logs?page=1&per_page=50&event=created&start_date=2024-01-01
Authorization: Bearer YOUR_TOKEN
```

#### 3. إحصائيات السجلات
```http
GET /api/v1/logs/statistics?start_date=2024-01-01&end_date=2024-12-31
Authorization: Bearer YOUR_TOKEN
```

### استخدام Webhooks

يمكن استخدام Webhooks لإرسال السجلات تلقائياً:

```http
POST /api/webhooks/logs/mini-school
Content-Type: application/json
X-API-Token: YOUR_TOKEN

{
    "event": "user_login",
    "user_id": 123,
    "ip_address": "192.168.1.100"
}
```

## 🔧 التكامل مع المشاريع الأخرى

### مثال: Laravel Project Integration

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Spatie\Activitylog\Models\Activity;

class SendLogsToLogVault extends Command
{
    protected $signature = 'logs:send-to-vault';
    protected $description = 'Send activity logs to LogVault system';

    public function handle()
    {
        $logs = Activity::where('created_at', '>=', now()->subDays(1))
            ->whereNull('properties->sent_to_vault')
            ->get();

        foreach ($logs as $log) {
            $response = Http::withToken(env('LOG_VAULT_TOKEN'))
                ->post(env('LOG_VAULT_URL') . '/api/v1/logs', [
                    'user_type' => $log->causer_type,
                    'user_id' => $log->causer_id,
                    'event' => $log->event,
                    'auditable_type' => $log->subject_type,
                    'auditable_id' => $log->subject_id,
                    'old_values' => $log->properties['old'] ?? null,
                    'new_values' => $log->properties['attributes'] ?? null,
                    'url' => $log->properties['url'] ?? null,
                    'ip_address' => $log->properties['ip'] ?? null,
                    'user_agent' => $log->properties['user_agent'] ?? null,
                    'source_system' => 'my-project',
                    'log_id' => $log->id,
                    'created_at' => $log->created_at->toISOString(),
                ]);

            if ($response->successful()) {
                $log->update([
                    'properties' => array_merge(
                        $log->properties->toArray(),
                        ['sent_to_vault' => now()->toISOString()]
                    )
                ]);
            }
        }
    }
}
```

### إعداد متغيرات البيئة في مشروعك

```env
LOG_VAULT_URL=http://logvault.local
LOG_VAULT_TOKEN=your_api_token_here
```

## 🛠️ إدارة التوكنات

### إنشاء توكن جديد عبر Tinker
```php
php artisan tinker

$project = \App\Models\Project::find(1);
$tokenData = \App\Models\ApiToken::generateToken(
    $project->id,
    'My Application Token',
    ['read', 'write'], // الصلاحيات
    now()->addYear() // تاريخ الانتهاء
);

echo "Token: " . $tokenData['plain_token'];
```

### أنواع الصلاحيات
- `read`: قراءة السجلات والإحصائيات
- `write`: إضافة سجلات جديدة
- `admin`: إدارة كاملة (حذف، تعديل)

## 🔒 إعداد IP Whitelist

### إضافة IP جديد
```php
\App\Models\IpWhitelist::addIp(
    $projectId, 
    '192.168.1.0/24', 
    'شبكة المكتب الرئيسي'
);
```

### أنماط IP المدعومة
- IP محدد: `192.168.1.100`
- CIDR Range: `192.168.1.0/24`
- Wildcard: `192.168.1.*`

## 📊 استخدام لوحة التحكم

### الداشبورد الرئيسي
- عرض إحصائيات شاملة
- رسوم بيانية للأنشطة
- أكثر المشاريع نشاطاً
- آخر السجلات

### إدارة المشاريع
- إضافة/تعديل/حذف المشاريع
- إدارة التوكنات لكل مشروع
- إعداد IP Whitelist
- مراقبة حالة الاتصال

### البحث في السجلات
- فلترة حسب المشروع
- فلترة حسب نوع الحدث
- فلترة حسب التاريخ
- فلترة حسب المستخدم
- تصدير النتائج

## ⚙️ إعدادات متقدمة

### تخصيص middleware
```php
// في bootstrap/app.php
$middleware->alias([
    'custom.auth' => \App\Http\Middleware\CustomApiAuth::class,
]);
```

### إعداد CORS للـ API
```php
// في config/cors.php
'paths' => ['api/*'],
'allowed_methods' => ['GET', 'POST'],
'allowed_origins' => ['https://your-domain.com'],
```

### تحسين الأداء
```bash
# تحسين autoloader
composer dump-autoload --optimize

# كاشة الإعدادات
php artisan config:cache

# كاشة الـ routes
php artisan route:cache

# كاشة الـ views
php artisan view:cache
```

## 🧪 الاختبار

### تشغيل الاختبارات
```bash
# جميع الاختبارات
php artisan test

# اختبارات API فقط
php artisan test --filter ApiTest

# مع تغطية الكود
php artisan test --coverage
```

### اختبار API باستخدام cURL
```bash
# إرسال سجل جديد
curl -X POST http://localhost:8000/api/v1/logs \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "event": "test",
    "user_id": 1,
    "source_system": "test"
  }'

# جلب السجلات
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/v1/logs
```

## 🚀 النشر في الإنتاج

### 1. إعداد البيئة
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://logvault.yourdomain.com

# قاعدة البيانات
DB_CONNECTION=mysql
DB_HOST=your_db_host
DB_DATABASE=log_vault_prod

# كاش
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 2. تحسينات الإنتاج
```bash
# تحديث dependencies
composer install --optimize-autoloader --no-dev

# كاش جميع الإعدادات
php artisan optimize

# إنشاء symbolic link للتخزين
php artisan storage:link

# تشغيل migrations
php artisan migrate --force
```

### 3. إعداد خادم الويب

#### Nginx
```nginx
server {
    listen 80;
    server_name logvault.yourdomain.com;
    root /var/www/logvault/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 4. إعداد Queue Worker
```bash
# إضافة إلى crontab
* * * * * cd /var/www/logvault && php artisan schedule:run >> /dev/null 2>&1

# إعداد Supervisor
[program:logvault-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/logvault/artisan queue:work redis --sleep=3 --tries=3
directory=/var/www/logvault
autostart=true
autorestart=true
user=www-data
numprocs=2
```

## 🔧 استكشاف الأخطاء

### مشاكل شائعة

#### 1. خطأ في التوثيق
```
HTTP 401: Unauthorized - Invalid API token
```
**الحل**: تأكد من صحة التوكن وأنه لم ينتهِ

#### 2. IP غير مسموح
```
HTTP 403: Access denied - Your IP address is not whitelisted
```
**الحل**: أضف IP إلى القائمة البيضاء

#### 3. نفاد الذاكرة
**الحل**: زيادة memory_limit في php.ini أو تحسين الاستعلامات

### سجلات النظام
```bash
# مراقبة السجلات
tail -f storage/logs/laravel.log

# مراقبة أخطاء الخادم
tail -f /var/log/nginx/error.log
```

## 🤝 المساهمة

نرحب بمساهماتكم! يرجى:

1. Fork المشروع
2. إنشاء branch للميزة الجديدة
3. Commit التغييرات
4. Push إلى Branch
5. إنشاء Pull Request

### معايير الكود
- اتباع PSR-12 coding standards
- كتابة اختبارات للميزات الجديدة
- توثيق التغييرات

## 📝 الترخيص

هذا المشروع مرخص تحت رخصة MIT - راجع ملف [LICENSE](LICENSE) للتفاصيل.

## 🆘 الدعم

- **البريد الإلكتروني**: support@logvault.com
- **التوثيق**: [docs.logvault.com](https://docs.logvault.com)
- **Issues**: [GitHub Issues](https://github.com/your-repo/issues)

## 🔄 التحديثات

### الإصدار الحالي: 1.0.0

#### المميزات الجديدة:
- ✅ نظام التوثيق بالتوكن
- ✅ IP Whitelist
- ✅ واجهة إدارية عربية
- ✅ تحليلات وإحصائيات
- ✅ تصدير البيانات
- ✅ Webhook endpoints

#### القادم في الإصدارات المستقبلية:
- 🔲 إشعارات فورية
- 🔲 تحليلات متقدمة بـ AI
- 🔲 Mobile App
- 🔲 Multi-tenancy
- 🔲 Advanced alerting system

---

<p align="center">
  صُنع بـ ❤️ للمجتمع العربي المطور
</p>
