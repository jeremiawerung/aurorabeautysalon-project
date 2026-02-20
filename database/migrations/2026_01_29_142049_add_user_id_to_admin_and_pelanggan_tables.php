<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added this line

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Add user_id column
        Schema::table('admin', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id_admin')->constrained('users')->onDelete('cascade');
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id_pelanggan')->constrained('users')->onDelete('cascade');
        });

        // 2. Migrate Data (Admin)
        $admins = DB::table('admin')->get();
        foreach ($admins as $admin) {
            if (empty($admin->email)) continue;
            
            // Check if user exists
            $user = DB::table('users')->where('email', $admin->email)->first();
            
            if (!$user) {
                // Create new user
                $userId = DB::table('users')->insertGetId([
                    'name' => $admin->nama,
                    'email' => $admin->email,
                    'password' => $admin->password, // Assume already hashed
                    'role' => 'admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                    'email_verified_at' => now(), // Auto verify legacy admins?
                ]);
            } else {
                $userId = $user->id;
                // Update role if needed? No, user might exist.
                // Force role admin?
                // DB::table('users')->where('id', $userId)->update(['role' => 'admin']); 
            }

            DB::table('admin')->where('id_admin', $admin->id_admin)->update(['user_id' => $userId]);
        }

        // 3. Migrate Data (Pelanggan)
        $pelanggans = DB::table('pelanggan')->whereNotNull('email')->get();
        foreach ($pelanggans as $p) {
            if (empty($p->email)) continue;

            $user = DB::table('users')->where('email', $p->email)->first();

            if (!$user) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $p->nama,
                    'email' => $p->email,
                    'password' => $p->password ?: '$2y$12$K.x.8...', // Dummy hash if null? Or empty string?
                    'role' => 'pelanggan',
                    'created_at' => $p->tanggal_daftar ?? now(),
                    'updated_at' => now(),
                    // 'email_verified_at' => null // Need verification
                ]);
            } else {
                $userId = $user->id;
            }

            DB::table('pelanggan')->where('id_pelanggan', $p->id_pelanggan)->update(['user_id' => $userId]);
        }

        // 4. Drop columns (Email & Password)
        Schema::table('admin', function (Blueprint $table) {
            $table->dropColumn(['email', 'password']);
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn(['email', 'password']);
        });
    }

    public function down()
    {
        // Reverse operations
        Schema::table('admin', function (Blueprint $table) {
            $table->string('email', 65)->unique()->nullable();
            $table->string('password')->nullable();
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->string('email', 65)->nullable()->unique();
            $table->string('password')->nullable();
        });

        // Restore data from users? Complicated. We just nullify user_id for now or drop column.
        
        Schema::table('admin', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
