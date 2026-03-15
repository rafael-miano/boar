<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('profile_picture')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'boar-raiser', 'customer']);
            $table->string('phone_number', 25)->nullable();
            $table->string('address', 500)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->string('theme')->nullable()->default('default');
            $table->string('theme_color')->nullable();
        });

        $now = now();
        $password = bcrypt('password');

        // 1 admin account
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@a.com',
            'email_verified_at' => $now,
            'password' => $password,
            'role' => 'admin',
            'phone_number' => '09171234567',
            'address' => 'Santa Cruz (Poblacion), Calape, Bohol, Philippines',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Fixed boar raiser and customer accounts
        DB::table('users')->insert([
            'name' => 'Boar Raiser',
            'email' => 'boar-raiser@a.com',
            'email_verified_at' => $now,
            'password' => $password,
            'role' => 'boar-raiser',
            'phone_number' => '09181234567',
            'address' => 'Desamparados (Poblacion), Calape, Bohol, Philippines',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('users')->insert([
            'name' => 'Customer',
            'email' => 'customer@a.com',
            'email_verified_at' => $now,
            'password' => $password,
            'role' => 'customer',
            'phone_number' => '09191234567',
            'address' => 'Lo-oc, Calape, Bohol, Philippines',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('users')->insert([
            'name' => 'Customer 2',
            'email' => 'customer2@a.com',
            'email_verified_at' => null,
            'password' => $password,
            'role' => 'customer',
            'phone_number' => '09201234567',
            'address' => 'Mantatao, Calape, Bohol, Philippines',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Filipino first names and surnames for generated accounts
        $firstNames = [
            'Juan', 'Maria', 'Jose', 'Ana', 'Pedro', 'Rosa', 'Miguel', 'Teresa', 'Antonio', 'Carmen',
            'Francisco', 'Josefa', 'Manuel', 'Filomena', 'Ramon', 'Gregoria', 'Pablo', 'Vicenta', 'Santiago', 'Elena',
            'Andres', 'Catalina', 'Felipe', 'Margarita', 'Rafael', 'Lucia', 'Domingo', 'Marcela', 'Emilio', 'Consuelo',
            'Alejandro', 'Dolores', 'Fernando', 'Patricia', 'Ricardo', 'Gloria', 'Eduardo', 'Lourdes', 'Roberto', 'Amelia',
            'Carlos', 'Fe', 'Enrique', 'Luz', 'Alberto', 'Mercedes', 'Jorge', 'Rosario', 'Daniel', 'Corazon',
        ];
        $lastNames = [
            'Santos', 'Reyes', 'Cruz', 'Garcia', 'Ramos', 'Mendoza', 'Torres', 'Gonzales', 'Dela Cruz', 'Lopez',
            'Villanueva', 'Castillo', 'Fernandez', 'Rivera', 'Bautista', 'Gutierrez', 'Mercado', 'Castro', 'Aquino', 'Ocampo',
            'Romero', 'Santiago', 'Diaz', 'Morales', 'Flores', 'Ramos', 'Pascual', 'Salvador', 'Estrada', 'Molina',
            'Ignacio', 'Perez', 'Navarro', 'Vargas', 'Medina', 'Cabrera', 'Santiago', 'Domingo', 'Marquez', 'Cortez',
            'Valdez', 'Solis', 'Acosta', 'Miranda', 'Ortega', 'Serrano', 'Villanueva', 'Espinoza', 'Herrera', 'Maldonado',
        ];

        // 50 boar raiser accounts (Filipino names, @gmail.com, Calape Bohol)
        $barangays = [
            'Abucayan Norte', 'Abucayan Sur', 'Banlasan', 'Bentig', 'Binogawan', 'Bonbon',
            'Cabayugan', 'Cabudburan', 'Calunasan', 'Camias', 'Canguha', 'Catmonan',
            'Desamparados (Poblacion)', 'Kahayag', 'Kinabag-an', 'Labuon', 'Lawis', 'Liboron',
            'Lo-oc', 'Lomboy', 'Lucob', 'Madangog', 'Magtongtong', 'Mandaug', 'Mantatao',
            'Sampoangon', 'San Isidro', 'Santa Cruz (Poblacion)', 'Sohoton', 'Talisay',
            'Tinibgan', 'Tultugan', 'Ulbujan',
        ];
        for ($i = 1; $i <= 50; $i++) {
            $firstName = $firstNames[($i - 1) % count($firstNames)];
            $lastName = $lastNames[(($i - 1) * 7) % count($lastNames)];
            $name = $firstName . ' ' . $lastName;
            $email = strtolower(str_replace(' ', '', $firstName)) . '.' . strtolower(str_replace(' ', '', $lastName)) . $i . '@gmail.com';
            DB::table('users')->insert([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => $now,
                'password' => $password,
                'role' => 'boar-raiser',
                'phone_number' => '09' . str_pad((string) ($i + 170000000), 9, '0'),
                'address' => $barangays[$i % count($barangays)] . ', Calape, Bohol, Philippines',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 50 customer accounts (Filipino names, @gmail.com, Calape Bohol)
        for ($i = 1; $i <= 50; $i++) {
            $firstName = $firstNames[(($i - 1) + 25) % count($firstNames)];
            $lastName = $lastNames[(($i - 1) * 11) % count($lastNames)];
            $name = $firstName . ' ' . $lastName;
            $email = strtolower(str_replace(' ', '', $firstName)) . '.' . strtolower(str_replace(' ', '', $lastName)) . $i . '@gmail.com';
            DB::table('users')->insert([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => $now,
                'password' => $password,
                'role' => 'customer',
                'phone_number' => '09' . str_pad((string) ($i + 190000000), 9, '0'),
                'address' => $barangays[($i + 5) % count($barangays)] . ', Calape, Bohol, Philippines',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
