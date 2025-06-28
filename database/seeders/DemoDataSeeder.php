<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\ApiToken;

use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    // /**
    //  * Run the database seeds.
    //  */
    // public function run(): void
    // {
    //     echo "🚀 بدء إضافة البيانات التجريبية...\n";

    //     // إنشاء مستخدم admin
    //     $admin = User::firstOrCreate(
    //         ['email' => 'admin@logvault.com'],
    //         [
    //             'name' => 'مدير النظام',
    //             'password' => bcrypt('password'),
    //             'email_verified_at' => now(),
    //         ]
    //     );

    //     echo "✅ تم إنشاء المستخدم الإداري: {$admin->email}\n";

    //     // إنشاء المشاريع
    //     $projects = [
    //         [
    //             'name' => 'نظام إدارة المدرسة',
    //             'description' => 'نظام شامل لإدارة المدارس والطلاب',
    //             'status' => 'active',
    //         ],
    //         [
    //             'name' => 'متجر إلكتروني',
    //             'description' => 'منصة تجارة إلكترونية متكاملة',
    //             'status' => 'active',
    //         ],
    //         [
    //             'name' => 'نظام المحاسبة',
    //             'description' => 'نظام محاسبة للشركات الصغيرة',
    //             'status' => 'inactive',
    //         ],
    //     ];

    //     foreach ($projects as $projectData) {
    //         $project = Project::firstOrCreate(
    //             ['name' => $projectData['name']],
    //             $projectData
    //         );
    //         echo "📁 تم إنشاء المشروع: {$project->name}\n";
    //     }

    //     // إنشاء توكنات Sanctum للمشاريع
    //     $projectsList = Project::all();
    //     foreach ($projectsList as $project) {
    //         $tokenName = "API Token - {$project->name}";

    //         // التحقق من وجود توكن مسبقاً
    //         $existingToken = $admin->tokens()->where('name', $tokenName)->first();
    //         if (!$existingToken) {
    //             $token = $admin->createToken($tokenName, ['project:' . $project->id]);
    //             echo "🔑 تم إنشاء توكن للمشروع: {$project->name}\n";
    //             echo "   Token: " . substr($token->plainTextToken, 0, 20) . "...\n";
    //         }
    //     }

    //     // إنشاء سجلات تجريبية
    //     $events = [
    //         'user_login', 'user_logout', 'user_register', 'product_created',
    //         'product_updated', 'order_placed', 'payment_processed', 'email_sent',
    //         'file_uploaded', 'data_exported', 'user_deleted', 'backup_created'
    //     ];

    //     $userTypes = ['User', 'Admin', 'Manager', 'Customer', 'Employee'];
    //     $auditableTypes = [
    //         'App\\Models\\User', 'App\\Models\\Product', 'App\\Models\\Order',
    //         'App\\Models\\Payment', 'App\\Models\\Category', 'App\\Models\\Report'
    //     ];

    //     $sourceSystems = ['Web App', 'Mobile App', 'Admin Panel', 'API Client', 'Background Job'];

    //     echo "📊 إنشاء السجلات التجريبية...\n";

    //     for ($i = 0; $i < 1000; $i++) {
    //         $occurredAt = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

    //         Log::create([
    //             'project_id' => $projectsList->random()->id,
    //             'user_type' => $userTypes[array_rand($userTypes)],
    //             'user_id' => rand(1, 100),
    //             'event' => $events[array_rand($events)],
    //             'auditable_type' => $auditableTypes[array_rand($auditableTypes)],
    //             'auditable_id' => rand(1, 500),
    //             'old_values' => $this->generateRandomData(),
    //             'new_values' => $this->generateRandomData(),
    //             'url' => 'https://example.com/' . strtolower(str_replace('_', '-', $events[array_rand($events)])),
    //             'ip_address' => $this->generateRandomIP(),
    //             'user_agent' => $this->generateRandomUserAgent(),
    //             'source_system' => $sourceSystems[array_rand($sourceSystems)],
    //             'external_log_id' => 'ext_' . rand(1000, 9999) . '_' . $i,
    //             'occurred_at' => $occurredAt,
    //             'created_at' => $occurredAt,
    //             'updated_at' => $occurredAt,
    //         ]);

    //         if (($i + 1) % 100 === 0) {
    //             echo "   📝 تم إنشاء " . ($i + 1) . " سجل\n";
    //         }
    //     }

    //     echo "\n🎉 انتهاء إضافة البيانات التجريبية!\n";
    //     echo "📊 الإحصائيات:\n";
    //     echo "   👥 المستخدمين: " . User::count() . "\n";
    //     echo "   📁 المشاريع: " . Project::count() . "\n";
    //     echo "   🔑 التوكنات: " . $admin->tokens()->count() . "\n";
    //     echo "   📋 السجلات: " . Log::count() . "\n";
    //     echo "\n🔐 بيانات الدخول:\n";
    //     echo "   Email: admin@logvault.com\n";
    //     echo "   Password: password\n";
    // }

    // /**
    //  * توليد بيانات عشوائية للسجلات
    //  */
    // private function generateRandomData(): array
    // {
    //     $data = [];
    //     $fields = ['status', 'name', 'email', 'price', 'quantity', 'category', 'description'];

    //     for ($i = 0; $i < rand(1, 3); $i++) {
    //         $field = $fields[array_rand($fields)];
    //         $data[$field] = $this->generateRandomValue($field);
    //     }

    //     return $data;
    // }

    // /**
    //  * توليد قيمة عشوائية حسب نوع الحقل
    //  */
    // private function generateRandomValue(string $field): mixed
    // {
    //     return match($field) {
    //         'status' => ['active', 'inactive', 'pending', 'completed'][array_rand(['active', 'inactive', 'pending', 'completed'])],
    //         'name' => 'Item ' . rand(1, 100),
    //         'email' => 'user' . rand(1, 100) . '@example.com',
    //         'price' => rand(10, 1000) . '.00',
    //         'quantity' => rand(1, 50),
    //         'category' => ['Electronics', 'Books', 'Clothing', 'Home'][array_rand(['Electronics', 'Books', 'Clothing', 'Home'])],
    //         'description' => 'Sample description for item ' . rand(1, 100),
    //         default => 'random_value_' . rand(1, 100)
    //     };
    // }

    // /**
    //  * توليد عنوان IP عشوائي
    //  */
    // private function generateRandomIP(): string
    // {
    //     return rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255);
    // }

    // /**
    //  * توليد User Agent عشوائي
    //  */
    // private function generateRandomUserAgent(): string
    // {
    //     $userAgents = [
    //         'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    //         'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
    //         'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
    //         'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X)',
    //         'Mozilla/5.0 (Android 11; Mobile; rv:91.0) Gecko/91.0'
    //     ];

    //     return $userAgents[array_rand($userAgents)];
    // }
}
