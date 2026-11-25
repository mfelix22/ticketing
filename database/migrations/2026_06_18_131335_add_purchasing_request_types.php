<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert Jasa (Service) request type
        $jasaId = DB::table('request_types')->insertGetId([
            'name'                      => 'Jasa',
            'description'               => 'Service procurement requests for Purchasing department',
            'requires_external_handling' => false,
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);

        // Fields for Jasa
        DB::table('request_type_fields')->insert([
            [
                'request_type_id'  => $jasaId,
                'field_name'       => 'Nama Jasa',
                'field_key'        => 'nama_jasa',
                'field_type'       => 'text',
                'is_required'      => true,
                'field_options'    => null,
                'placeholder'      => 'Masukkan nama jasa yang dibutuhkan',
                'validation_rules' => null,
                'order'            => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'request_type_id'  => $jasaId,
                'field_name'       => 'Keterangan',
                'field_key'        => 'keterangan',
                'field_type'       => 'textarea',
                'is_required'      => true,
                'field_options'    => null,
                'placeholder'      => 'Jelaskan detail kebutuhan jasa',
                'validation_rules' => null,
                'order'            => 2,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'request_type_id'  => $jasaId,
                'field_name'       => 'Catatan Tambahan',
                'field_key'        => 'catatan_tambahan',
                'field_type'       => 'textarea',
                'is_required'      => false,
                'field_options'    => null,
                'placeholder'      => 'Informasi tambahan (opsional)',
                'validation_rules' => null,
                'order'            => 3,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ]);

        // Insert Barang (Goods) request type
        $barangId = DB::table('request_types')->insertGetId([
            'name'                      => 'Barang',
            'description'               => 'Goods procurement requests for Purchasing department',
            'requires_external_handling' => false,
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);

        // Fields for Barang
        DB::table('request_type_fields')->insert([
            [
                'request_type_id'  => $barangId,
                'field_name'       => 'Nama Barang',
                'field_key'        => 'nama_barang',
                'field_type'       => 'text',
                'is_required'      => true,
                'field_options'    => null,
                'placeholder'      => 'Masukkan nama barang yang dibutuhkan',
                'validation_rules' => null,
                'order'            => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'request_type_id'  => $barangId,
                'field_name'       => 'Jumlah',
                'field_key'        => 'jumlah',
                'field_type'       => 'number',
                'is_required'      => true,
                'field_options'    => null,
                'placeholder'      => '1',
                'validation_rules' => null,
                'order'            => 2,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'request_type_id'  => $barangId,
                'field_name'       => 'Spesifikasi',
                'field_key'        => 'spesifikasi',
                'field_type'       => 'textarea',
                'is_required'      => false,
                'field_options'    => null,
                'placeholder'      => 'Jelaskan spesifikasi barang',
                'validation_rules' => null,
                'order'            => 3,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'request_type_id'  => $barangId,
                'field_name'       => 'Tujuan Penggunaan',
                'field_key'        => 'tujuan_penggunaan',
                'field_type'       => 'textarea',
                'is_required'      => true,
                'field_options'    => null,
                'placeholder'      => 'Jelaskan tujuan penggunaan barang ini',
                'validation_rules' => null,
                'order'            => 4,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'request_type_id'  => $barangId,
                'field_name'       => 'Catatan Tambahan',
                'field_key'        => 'catatan_tambahan',
                'field_type'       => 'textarea',
                'is_required'      => false,
                'field_options'    => null,
                'placeholder'      => 'Informasi tambahan (opsional)',
                'validation_rules' => null,
                'order'            => 5,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('request_types')->whereIn('name', ['Jasa', 'Barang'])->delete();
    }
};
