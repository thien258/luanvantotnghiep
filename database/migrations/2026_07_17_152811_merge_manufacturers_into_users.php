<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Gộp bảng manufacturers vào users:
 *
 * 1. Với NSX có user_id → giữ nguyên, chỉ cập nhật FK trong các bảng phụ
 * 2. Với NSX KHÔNG có user_id (id=1, tên "a") → tạo user mới
 * 3. Đổi FK manufacturer_id trong supplier_offers, purchase_orders,
 *    manufacturers_product → trỏ vào users.id
 * 4. Xóa bảng manufacturers
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Bước 1: Với mỗi manufacturer không có user_id → tạo user mới ──
        $orphans = DB::table('manufacturers')->whereNull('user_id')->get();
        foreach ($orphans as $mfr) {
            $userId = DB::table('users')->insertGetId([
                'name'              => $mfr->name,
                'email'             => 'nsx_' . $mfr->id . '_' . time() . '@auto.local',
                'phone'             => $mfr->phone ?? '',
                'address'           => $mfr->address ?? '',
                'password'          => Hash::make('password123'),
                'role'              => 'manufacturer',
                'is_active'         => 1,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            // Gán user_id vào manufacturer để bước sau dùng
            DB::table('manufacturers')->where('id', $mfr->id)->update(['user_id' => $userId]);
        }

        // ── Bước 2: Build map manufacturer_id → user_id ──────────────────
        $manufacturers = DB::table('manufacturers')->get();
        $map = []; // manufacturer_id => user_id
        foreach ($manufacturers as $mfr) {
            if ($mfr->user_id) {
                $map[$mfr->id] = $mfr->user_id;
            }
        }

        // ── Bước 3: Đổi FK trong supplier_offers ─────────────────────────
        // Xóa FK cũ trước, thêm FK mới sau
        Schema::table('supplier_offers', function (Blueprint $table) {
            $table->dropForeign(['manufacturer_id']);
        });

        foreach ($map as $mfrId => $userId) {
            DB::table('supplier_offers')
                ->where('manufacturer_id', $mfrId)
                ->update(['manufacturer_id' => $userId]);
        }

        Schema::table('supplier_offers', function (Blueprint $table) {
            $table->foreign('manufacturer_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });

        // ── Bước 4: Đổi FK trong purchase_orders ─────────────────────────
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['manufacturer_id']);
        });

        foreach ($map as $mfrId => $userId) {
            DB::table('purchase_orders')
                ->where('manufacturer_id', $mfrId)
                ->update(['manufacturer_id' => $userId]);
        }

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreign('manufacturer_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });

        // ── Bước 5: Đổi FK trong manufacturers_product ───────────────────
        Schema::table('manufacturers_product', function (Blueprint $table) {
            $table->dropForeign(['manufacturer_id']);
        });

        foreach ($map as $mfrId => $userId) {
            DB::table('manufacturers_product')
                ->where('manufacturer_id', $mfrId)
                ->update(['manufacturer_id' => $userId]);
        }

        Schema::table('manufacturers_product', function (Blueprint $table) {
            $table->foreign('manufacturer_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });

        // ── Bước 6: Xóa bảng manufacturers ───────────────────────────────
        // Xóa FK user_id trong manufacturers trước để tránh constraint
        Schema::table('manufacturers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('manufacturers');
    }

    public function down(): void
    {
        // Rollback: tạo lại bảng manufacturers (không restore data cũ)
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Xóa FK mới trong các bảng phụ và tạo lại FK cũ
        Schema::table('supplier_offers', function (Blueprint $table) {
            $table->dropForeign(['manufacturer_id']);
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['manufacturer_id']);
        });
        Schema::table('manufacturers_product', function (Blueprint $table) {
            $table->dropForeign(['manufacturer_id']);
        });

        // NOTE: Không thể restore FK về manufacturers vì bảng không còn data
        // Cần restore thủ công nếu rollback
    }
};
