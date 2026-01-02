<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // مهم: لو كنت عامل config:cache لازم تعمل clear قبل التشغيل
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ✅ جلب التعريفات من config/permissions.php
        $modules = config('permissions', []); // شكلها: ['cars'=>['create','edit',...], ...]

        // إنشاء جميع الصلاحيات من الإعدادات
        collect($modules)->each(function ($abilities, $module) {
            foreach ($abilities as $ability) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$ability}",
                    'guard_name' => 'web',
                ]);
            }
        });

        // إنشاء أدوار افتراضية
        $admin  = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // الإدمن يأخذ الكل
        $admin->syncPermissions(Permission::all());

        // إنشاء/تعيين أدمن افتراضي
        $user = User::firstOrCreate(
            ['email' => 'admin@dev.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('123456789'),
                'email_verified_at' => now(),
            ]
        );
        if (!$user->hasRole('admin')) {
            $user->assignRole($admin);
        }

        // إعادة تفريغ كاش الصلاحيات
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}