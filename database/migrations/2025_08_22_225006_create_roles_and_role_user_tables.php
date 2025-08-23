    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            // Tabel untuk menyimpan daftar semua peran
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique(); // Contoh: admin, dosen, mahasiswa
                $table->string('display_name'); // Contoh: Administrator, Dosen, Mahasiswa
                $table->timestamps();
            });

            // Tabel pivot untuk menghubungkan users dan roles (Many-to-Many)
            Schema::create('role_user', function (Blueprint $table) {
                $table->primary(['user_id', 'role_id']); // Composite primary key
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('role_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('role_user');
            Schema::dropIfExists('roles');
        }
    };
    