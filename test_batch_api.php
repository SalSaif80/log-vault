<?php

/**
 * اختبار LogVault API - استقبال دفعة من السجلات
 *
 * هذا الملف يحاكي إرسال السجلات من mini-school إلى LogVault
 */

// بيانات تجريبية تحاكي ما يرسله mini-school
$logs = [
    [
        'log_id' => 1,
        'log_name' => 'student_activity',
        'description' => 'تسجيل دخول طالب جديد',
        'subject_type' => 'App\\Models\\Student',
        'subject_id' => 123,
        'event' => 'login',
        'causer_type' => 'App\\Models\\User',
        'causer_id' => 45,
        'batch_uuid' => 'batch-' . uniqid(),
        'properties' => [
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'session_id' => 'session_' . uniqid()
        ],
        'source_system' => 'mini-school',
        'created_at' => date('c'), // ISO 8601 format
    ],
    [
        'log_id' => 2,
        'log_name' => 'grade_update',
        'description' => 'تحديث درجة طالب',
        'subject_type' => 'App\\Models\\Grade',
        'subject_id' => 456,
        'event' => 'updated',
        'causer_type' => 'App\\Models\\Teacher',
        'causer_id' => 78,
        'batch_uuid' => 'batch-' . uniqid(),
        'properties' => [
            'old_grade' => 85,
            'new_grade' => 90,
            'subject' => 'رياضيات'
        ],
        'source_system' => 'mini-school',
        'created_at' => date('c'),
    ],
    [
        'log_id' => 3,
        'log_name' => 'attendance_record',
        'description' => 'تسجيل حضور طالب',
        'subject_type' => 'App\\Models\\Attendance',
        'subject_id' => 789,
        'event' => 'created',
        'causer_type' => 'App\\Models\\Teacher',
        'causer_id' => 90,
        'batch_uuid' => 'batch-' . uniqid(),
        'properties' => [
            'status' => 'present',
            'class' => 'الصف الثالث أ',
            'date' => date('Y-m-d')
        ],
        'source_system' => 'mini-school',
        'created_at' => date('c'),
    ]
];

// إعداد البيانات للإرسال
$payload = [
    'logs' => $logs
];

// إعداد cURL
$url = 'http://192.168.56.1:8080/api/logs/batch';
$token = 'YOUR_TOKEN_HERE'; // يجب استبدالها بالتوكن الحقيقي

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token,
    ],
    CURLOPT_TIMEOUT => 30,
]);

echo "🚀 إرسال دفعة من " . count($logs) . " سجلات إلى LogVault...\n";
echo "📍 URL: $url\n";
echo "📦 البيانات: " . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

if ($error) {
    echo "❌ خطأ في الاتصال: $error\n";
    exit(1);
}

echo "📊 HTTP Status: $httpCode\n";
echo "📄 الاستجابة: \n";

if ($response) {
    $responseData = json_decode($response, true);
    if ($responseData) {
        echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

        if ($httpCode === 201 && isset($responseData['success']) && $responseData['success']) {
            echo "\n✅ تم إرسال السجلات بنجاح!\n";
            echo "📈 عدد السجلات المحفوظة: " . $responseData['saved_count'] . "\n";
        } else {
            echo "\n❌ فشل في إرسال السجلات\n";
        }
    } else {
        echo $response . "\n";
    }
} else {
    echo "لا توجد استجابة\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "💡 لاختبار الـ API:\n";
echo "1. احصل على توكن من: http://192.168.56.1:8080/api/generate-token\n";
echo "2. استبدل YOUR_TOKEN_HERE بالتوكن الحقيقي\n";
echo "3. شغل هذا الملف: php test_batch_api.php\n";
