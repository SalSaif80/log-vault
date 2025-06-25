<?php

/*
 * مثال بسيط لاختبار API نظام LogVault المبسط
 *
 * قم بتغيير TOKEN بالتوكن الخاص بك من المشروع
 */

$baseUrl = 'http://127.0.0.1:8080/api';
$token = 'YOUR_SANCTUM_TOKEN_HERE'; // ضع التوكن الخاص بك هنا

// بيانات سجل تجريبي
$logData = [
    'project_id' => 1, // معرف المشروع (اختياري)
    'event' => 'test_event',
    'user_type' => 'User',
    'user_id' => 123,
    'auditable_type' => 'App\Models\User',
    'auditable_id' => 123,
    'old_values' => [
        'status' => 'inactive'
    ],
    'new_values' => [
        'status' => 'active',
        'last_login' => date('Y-m-d H:i:s')
    ],
    'url' => 'https://example.com/test',
    'source_system' => 'Test Script',
    'external_log_id' => 'test_' . time()
];

// Headers للطلب
$headers = [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
];

// إرسال السجل
echo "🚀 إرسال سجل تجريبي...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/logs');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($logData));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📊 Response Code: " . $httpCode . "\n";
echo "📝 Response Body:\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// قراءة السجلات
echo "📖 قراءة السجلات...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/logs?per_page=5');
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📊 Response Code: " . $httpCode . "\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "📋 عدد السجلات: " . count($data['data']) . "\n";
    echo "📄 الصفحة: " . $data['pagination']['current_page'] . " من " . $data['pagination']['last_page'] . "\n";
    echo "📈 إجمالي السجلات: " . $data['pagination']['total'] . "\n\n";

    echo "📃 آخر 3 سجلات:\n";
    foreach (array_slice($data['data'], 0, 3) as $log) {
        echo "  • {$log['event']} - {$log['occurred_at']} - {$log['source_system']}\n";
    }
} else {
    echo "❌ خطأ في قراءة السجلات:\n";
    echo $response . "\n";
}

// إحصائيات
echo "\n📊 إحصائيات...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/logs/statistics');
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $stats = json_decode($response, true);
    echo "📊 إجمالي السجلات: " . $stats['data']['total_logs'] . "\n";
    echo "📈 أنواع الأحداث:\n";
    foreach ($stats['data']['events_count'] as $event => $count) {
        echo "  • {$event}: {$count}\n";
    }
} else {
    echo "❌ خطأ في قراءة الإحصائيات:\n";
    echo $response . "\n";
}

echo "\n✅ انتهاء الاختبار!\n";
echo "\n💡 تذكر:\n";
echo "1. غيّر TOKEN في أول الملف\n";
echo "2. تأكد من تشغيل الخادم على http://127.0.0.1:8080\n";
echo "3. أنشئ مشروع جديد من Admin Panel إذا لم يكن موجوداً\n";
echo "4. يمكنك عرض السجلات من: http://127.0.0.1:8080/admin/logs\n";

?>
