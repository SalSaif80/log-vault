<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpWhitelist extends Model
{
    use HasFactory;

    protected $table = 'ip_whitelist';

    protected $fillable = [
        'project_id',
        'ip_address',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * العلاقة مع المشروع
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * التحقق من إذا كان IP مسموح أم لا
     */
    public static function isIpAllowed(string $ip, int $projectId = null): bool
    {
        $query = static::where('status', 'active');

        if ($projectId) {
            $query->where(function ($q) use ($projectId) {
                $q->where('project_id', $projectId)
                  ->orWhereNull('project_id');
            });
        }

        $allowedIps = $query->get();

        foreach ($allowedIps as $allowedIp) {
            if (static::matchesPattern($ip, $allowedIp->ip_address)) {
                return true;
            }
        }

        return false;
    }

    /**
     * التحقق من مطابقة IP للنمط
     */
    protected static function matchesPattern(string $ip, string $pattern): bool
    {
        // إذا كان النمط نفس IP مباشرة
        if ($ip === $pattern) {
            return true;
        }

        // إذا كان النمط يحتوي على wildcard (*)
        if (strpos($pattern, '*') !== false) {
            $regexPattern = str_replace('.', '\.', $pattern);
            $regexPattern = str_replace('*', '.*', $regexPattern);
            return preg_match('/^' . $regexPattern . '$/', $ip);
        }

        // إذا كان النمط CIDR
        if (strpos($pattern, '/') !== false) {
            return static::ipInCidr($ip, $pattern);
        }

        return false;
    }

    /**
     * التحقق من CIDR range
     */
    protected static function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = explode('/', $cidr);

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return static::ipv4InCidr($ip, $subnet, $mask);
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return static::ipv6InCidr($ip, $subnet, $mask);
        }

        return false;
    }

    /**
     * التحقق من IPv4 CIDR
     */
    protected static function ipv4InCidr(string $ip, string $subnet, int $mask): bool
    {
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = -1 << (32 - $mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }

    /**
     * التحقق من IPv6 CIDR
     */
    protected static function ipv6InCidr(string $ip, string $subnet, int $mask): bool
    {
        $ipBin = inet_pton($ip);
        $subnetBin = inet_pton($subnet);

        if ($ipBin === false || $subnetBin === false) {
            return false;
        }

        $bytesToCheck = intval($mask / 8);
        $bitsToCheck = $mask % 8;

        // فحص البايتات الكاملة
        for ($i = 0; $i < $bytesToCheck; $i++) {
            if ($ipBin[$i] !== $subnetBin[$i]) {
                return false;
            }
        }

        // فحص البتات المتبقية
        if ($bitsToCheck > 0 && $bytesToCheck < 16) {
            $mask = 0xFF << (8 - $bitsToCheck);
            return (ord($ipBin[$bytesToCheck]) & $mask) === (ord($subnetBin[$bytesToCheck]) & $mask);
        }

        return true;
    }

    /**
     * إضافة IP جديد
     */
    public static function addIp(int $projectId, string $ipAddress, string $description = null): self
    {
        return static::create([
            'project_id' => $projectId,
            'ip_address' => $ipAddress,
            'description' => $description,
            'status' => 'active',
        ]);
    }

    /**
     * تغيير حالة IP
     */
    public function toggleStatus(): bool
    {
        $this->status = $this->status === 'active' ? 'inactive' : 'active';
        return $this->save();
    }

    /**
     * الحصول على نوع النمط
     */
    public function getPatternTypeAttribute(): string
    {
        if (strpos($this->ip_address, '*') !== false) {
            return 'wildcard';
        } elseif (strpos($this->ip_address, '/') !== false) {
            return 'cidr';
        } else {
            return 'direct';
        }
    }

    /**
     * فلترة IP حسب الحالة
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * فلترة IP حسب المشروع
     */
    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}
