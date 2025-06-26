<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\IpWhitelist;

class IpWhitelistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🛡️ إضافة عناوين IP التجريبية...\n";

        $projects = Project::all();

        foreach ($projects as $project) {
            // IP مباشر للإنتاج
            IpWhitelist::create([
                'project_id' => $project->id,
                'ip_address' => '192.168.1.100',
                'description' => 'سيرفر الإنتاج الرئيسي',
                'status' => 'active'
            ]);

            // شبكة محلية بـ Wildcard
            IpWhitelist::create([
                'project_id' => $project->id,
                'ip_address' => '10.0.0.*',
                'description' => 'شبكة المكتب الداخلية',
                'status' => 'active'
            ]);

            // شبكة CIDR للـ VPN
            IpWhitelist::create([
                'project_id' => $project->id,
                'ip_address' => '172.16.0.0/16',
                'description' => 'شبكة VPN الشركة',
                'status' => 'active'
            ]);

            // IP خارجي محدد
            IpWhitelist::create([
                'project_id' => $project->id,
                'ip_address' => '203.0.113.50',
                'description' => 'سيرفر خارجي للاختبار',
                'status' => 'inactive'
            ]);

            // localhost للتطوير
            IpWhitelist::create([
                'project_id' => $project->id,
                'ip_address' => '127.0.0.1',
                'description' => 'بيئة التطوير المحلية',
                'status' => 'active'
            ]);

            // شبكة محلية أخرى
            IpWhitelist::create([
                'project_id' => $project->id,
                'ip_address' => '192.168.100.0/24',
                'description' => 'شبكة فرع ثانوي',
                'status' => 'active'
            ]);

            echo "✅ تم إضافة عناوين IP للمشروع: {$project->name}\n";
        }

        echo "🎉 تم إنجاز إضافة جميع عناوين IP التجريبية!\n";
    }
}
