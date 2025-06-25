# LogVault API - دليل الاستخدام البسيط

## نظرة عامة

LogVault هو نظام مركزي لجمع وإدارة السجلات من مشاريع متعددة. يستخدم Laravel Sanctum للمصادقة ويوفر API بسيط لاستقبال السجلات.

## الإعداد السريع

### 1. الحصول على توكن API

```bash
curl http://192.168.56.1:8080/api/generate-token
```

ستحصل على استجابة مثل:
```json
{
    "token": "1|abc123...",
    "note": "انسخ هذا التوكن واستخدمه في المشروع المرسِل"
}
```

### 2. اختبار الاتصال

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://192.168.56.1:8080/api/health
```

## API Endpoints

### 🔍 فحص صحة النظام
```
GET /api/health
```
لا يحتاج مصادقة

### 🔐 إنشاء توكن (للاختبار)
```
GET /api/generate-token
```
لا يحتاج مصادقة

### 📦 إرسال دفعة من السجلات (الاستخدام الأساسي)
```
POST /api/logs/batch
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
```

**البيانات المطلوبة:**
```json
{
    "logs": [
        {
            "log_id": 1,
            "log_name": "student_activity",
            "description": "تسجيل دخول طالب جديد",
            "subject_type": "App\\Models\\Student",
            "subject_id": 123,
            "event": "login",
            "causer_type": "App\\Models\\User",
            "causer_id": 45,
            "batch_uuid": "batch-unique-id",
            "properties": {
                "ip_address": "192.168.1.100",
                "user_agent": "Mozilla/5.0...",
                "session_id": "session_123"
            },
            "source_system": "mini-school",
            "created_at": "2025-06-25T13:45:30.000Z"
        }
    ]
}
```

**الاستجابة:**
```json
{
    "success": true,
    "message": "تم حفظ السجلات بنجاح",
    "saved_count": 3,
    "saved_ids": [1, 2, 3]
}
```

### 📝 إرسال سجل واحد (للاختبار)
```
POST /api/logs
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
```

### 📊 عرض السجلات
```
GET /api/logs?source_system=mini-school&event=login&start_date=2025-06-25
Authorization: Bearer YOUR_TOKEN
```

### 📈 إحصائيات السجلات
```
GET /api/logs/statistics
Authorization: Bearer YOUR_TOKEN
```

## أمثلة عملية

### PHP (مثل mini-school)

```php
<?php

function sendLogsBatchToVault($logs): bool
{
    try {
        $payload = [];

        foreach ($logs as $log) {
            $payload[] = [
                'log_id' => $log->id,
                'log_name' => $log->log_name,
                'description' => $log->description,
                'subject_type' => $log->subject_type,
                'subject_id' => $log->subject_id,
                'event' => $log->event,
                'batch_uuid' => $log->batch_uuid,
                'causer_type' => $log->causer_type,
                'causer_id' => $log->causer_id,
                'properties' => $log->properties,
                'source_system' => 'mini-school',
                'created_at' => $log->created_at->toISOString(),
            ];
        }

        $response = Http::timeout(30)
            ->withToken(env('LOG_API_TOKEN'))
            ->post(env('LOG_API_URL') . '/api/logs/batch', [
                'logs' => $payload
            ]);

        return $response->successful();

    } catch (Exception $e) {
        Log::error("فشل إرسال السجلات إلى LogVault: " . $e->getMessage());
        return false;
    }
}

// الاستخدام
$logs = ActivityLog::where('sent_to_vault', false)->limit(100)->get();
if (sendLogsBatchToVault($logs)) {
    $logs->update(['sent_to_vault' => true]);
}
```

### JavaScript/Node.js

```javascript
const axios = require('axios');

async function sendLogsToVault(logs) {
    try {
        const payload = {
            logs: logs.map(log => ({
                log_id: log.id,
                log_name: log.log_name,
                description: log.description,
                subject_type: log.subject_type,
                subject_id: log.subject_id,
                event: log.event,
                causer_type: log.causer_type,
                causer_id: log.causer_id,
                batch_uuid: log.batch_uuid,
                properties: log.properties,
                source_system: 'my-app',
                created_at: log.created_at
            }))
        };

        const response = await axios.post(
            'http://192.168.56.1:8080/api/logs/batch',
            payload,
            {
                headers: {
                    'Authorization': `Bearer ${process.env.LOG_API_TOKEN}`,
                    'Content-Type': 'application/json'
                },
                timeout: 30000
            }
        );

        console.log('✅ تم إرسال السجلات بنجاح:', response.data);
        return true;

    } catch (error) {
        console.error('❌ فشل إرسال السجلات:', error.message);
        return false;
    }
}
```

### Python

```python
import requests
import json
from datetime import datetime

def send_logs_to_vault(logs):
    try:
        payload = {
            "logs": [
                {
                    "log_id": log["id"],
                    "log_name": log.get("log_name"),
                    "description": log["description"],
                    "subject_type": log.get("subject_type"),
                    "subject_id": log.get("subject_id"),
                    "event": log.get("event"),
                    "causer_type": log.get("causer_type"),
                    "causer_id": log.get("causer_id"),
                    "batch_uuid": log.get("batch_uuid"),
                    "properties": log.get("properties", {}),
                    "source_system": "python-app",
                    "created_at": log["created_at"]
                }
                for log in logs
            ]
        }

        response = requests.post(
            'http://192.168.56.1:8080/api/logs/batch',
            json=payload,
            headers={
                'Authorization': f'Bearer {os.getenv("LOG_API_TOKEN")}',
                'Content-Type': 'application/json'
            },
            timeout=30
        )

        if response.status_code == 201:
            print(f"✅ تم إرسال {len(logs)} سجلات بنجاح")
            return True
        else:
            print(f"❌ فشل الإرسال: {response.status_code}")
            return False

    except Exception as e:
        print(f"❌ خطأ: {str(e)}")
        return False
```

## هيكل البيانات

### الحقول المطلوبة:
- `log_id`: رقم السجل في النظام المرسِل
- `description`: وصف الحدث
- `source_system`: اسم النظام المرسِل
- `created_at`: تاريخ إنشاء السجل (ISO 8601)

### الحقول الاختيارية:
- `log_name`: اسم نوع السجل
- `subject_type`: نوع الكائن المتأثر
- `subject_id`: معرف الكائن المتأثر
- `event`: نوع الحدث (created, updated, deleted, etc.)
- `causer_type`: نوع من تسبب في الحدث
- `causer_id`: معرف من تسبب في الحدث
- `batch_uuid`: معرف المجموعة (لربط السجلات المرتبطة)
- `properties`: بيانات إضافية (JSON)

## فلترة السجلات

يمكن فلترة السجلات باستخدام:
- `source_system`: النظام المرسِل
- `event`: نوع الحدث
- `start_date` & `end_date`: نطاق التاريخ
- `batch_uuid`: معرف المجموعة
- `per_page`: عدد السجلات لكل صفحة (افتراضي: 50، أقصى: 100)

## أكواد الاستجابة

- `200`: نجح الطلب
- `201`: تم إنشاء السجلات بنجاح
- `401`: غير مخول (توكن غير صحيح)
- `422`: بيانات غير صحيحة
- `500`: خطأ خادم

## اختبار سريع

استخدم الملف `test_batch_api.php` للاختبار:

```bash
# 1. احصل على توكن
curl http://192.168.56.1:8080/api/generate-token

# 2. عدل التوكن في الملف
# 3. شغل الاختبار
php test_batch_api.php
```

## الدعم والمساعدة

- الواجهة الإدارية: `http://192.168.56.1:8080/admin`
- فحص صحة API: `http://192.168.56.1:8080/api/health`
- إنشاء توكن: `http://192.168.56.1:8080/api/generate-token`
