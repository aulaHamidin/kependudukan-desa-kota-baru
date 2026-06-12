<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\JenisSurat;
use Illuminate\Database\Seeder;

/**
 * JenisSuratSeeder - Hybrid Template System
 *
 * Menggunakan pendekatan hybrid:
 * - template_category: memilih file Blade mana (keterangan, pengantar, internal)
 * - template_sections: JSON konfigurasi untuk fields, intro, body, signature
 *
 * Template Categories Aktif:
 * - keterangan: Surat keterangan umum (SKD, SKTM, SKBB, SKU, SKBK)
 * - pengantar: Surat pengantar ke instansi (SKLHR, SKMT, SKPD, SKN)
 * - internal: Surat internal desa (SBALASAN)
 *
 * Total Aktif: 10 jenis surat (mencakup ~90% kebutuhan warga desa)
 */
class JenisSuratSeeder extends Seeder
{
    public function run(): void
    {
        $jenisSurat = [
            // =========================================================
            // KELOMPOK 1 — KEPENDUDUKAN
            // =========================================================
            [
                'kode'              => 'SKD',
                'nama'              => 'Surat Keterangan Domisili',
                'deskripsi'         => 'Surat keterangan tempat tinggal untuk penduduk tetap maupun sementara',
                'template_category' => 'keterangan',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'bin_binti', 'tempat_tanggal_lahir', 'nik', 'no_kk', 'pekerjaan', 'alamat', 'alamat_domisili'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'          => 'Nama tersebut adalah benar warga desa dan berdomisili di alamat tersebut.',
                    'required_fields' => ['nama_lengkap', 'nik'],
                    'show_purpose'  => false,
                    'closing'       => 'Demikian Surat Keterangan Domisili ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.',
                    'suppress_default_closing' => true,
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKD',
                'masa_berlaku_hari' => 180,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk pengurusan NPWP, pembukaan rekening bank, dll',
            ],
            [
                'kode'              => 'SKPD',
                'nama'              => 'Surat Keterangan Pindah/Datang',
                'deskripsi'         => 'Surat keterangan untuk mutasi penduduk antar desa/kelurahan',
                'template_category' => 'pengantar',
                'template_sections' => [
                    'data_fields'      => ['nama_lengkap', 'nik', 'no_kk', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'alamat'],
                    'additional_fields' => ['alamat_tujuan', 'alasan_pindah'],
                    'intro'            => 'Yang bertanda tangan di bawah ini, Kepala Desa, memberikan keterangan pindah kepada:',
                    'body'             => 'Bermaksud untuk pindah/datang ke alamat tujuan sebagaimana tersebut di atas.',
                    'target_instansi'  => 'Dinas Kependudukan dan Pencatatan Sipil',
                    'required_fields'  => ['nama_lengkap', 'nik', 'alamat_tujuan', 'alasan_pindah'],
                    'signature'        => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKPD',
                'masa_berlaku_hari' => 30,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk proses mutasi kependudukan ke Disdukcapil',
            ],
            [
                'kode'              => 'SKLHR',
                'nama'              => 'Surat Keterangan Kelahiran',
                'deskripsi'         => 'Surat pengantar desa untuk pengurusan akta kelahiran di Disdukcapil',
                'template_category' => 'pengantar',
                'template_sections' => [
                    'data_fields'      => ['nama_bayi', 'jenis_kelamin_bayi', 'tempat_lahir_bayi', 'tanggal_lahir_bayi', 'anak_ke'],
                    'additional_fields' => ['nama_ibu', 'nik_ibu', 'nama_ayah', 'nik_ayah'],
                    'intro'            => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa telah lahir seorang anak:',
                    'body'             => 'Demikian surat keterangan kelahiran ini dibuat berdasarkan keterangan dari orang tua dan saksi-saksi.',
                    'target_instansi'  => 'Dinas Kependudukan dan Pencatatan Sipil',
                    'required_fields'  => ['nama_bayi', 'jenis_kelamin_bayi', 'tempat_lahir_bayi', 'tanggal_lahir_bayi', 'nama_ibu', 'nik_ibu', 'nama_ayah', 'nik_ayah'],
                    'signature'        => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKLHR',
                'masa_berlaku_hari' => null,
                'is_active'         => true,
                'keterangan'        => 'Syarat pengurusan akta kelahiran di Disdukcapil',
            ],
            [
                'kode'              => 'SKMT',
                'nama'              => 'Surat Keterangan Kematian',
                'deskripsi'         => 'Surat pengantar desa untuk pengurusan akta kematian di Disdukcapil',
                'template_category' => 'pengantar',
                'template_sections' => [
                    'data_fields'      => ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'alamat'],
                    'additional_fields' => ['tanggal_meninggal', 'tempat_meninggal', 'sebab_kematian'],
                    'intro'            => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'             => 'Telah meninggal dunia sebagaimana keterangan di atas.',
                    'target_instansi'  => 'Dinas Kependudukan dan Pencatatan Sipil',
                    'required_fields'  => ['nama_lengkap', 'nik', 'tanggal_meninggal', 'tempat_meninggal', 'sebab_kematian'],
                    'signature'        => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKMT',
                'masa_berlaku_hari' => null,
                'is_active'         => true,
                'keterangan'        => 'Syarat pengurusan akta kematian di Disdukcapil',
            ],
            [
                'kode'              => 'SKBK',
                'nama'              => 'Surat Keterangan Belum Kawin',
                'deskripsi'         => 'Surat keterangan bahwa pemohon belum pernah menikah',
                'template_category' => 'keterangan',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama', 'pekerjaan', 'alamat', 'status_kawin'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'          => 'Berdasarkan data kependudukan dan pengakuan yang bersangkutan, hingga surat ini dibuat yang bersangkutan belum pernah melangsungkan pernikahan.',
                    'required_fields' => ['nama_lengkap', 'nik'],
                    'show_purpose'  => true,
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKBK',
                'masa_berlaku_hari' => 90,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk persyaratan pernikahan di KUA atau catatan sipil',
            ],
            [
                'kode'              => 'SKN',
                'nama'              => 'Surat Keterangan Nikah',
                'deskripsi'         => 'Surat pengantar desa untuk pendaftaran pernikahan di KUA',
                'template_category' => 'pengantar',
                'template_sections' => [
                    'data_fields'      => ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama', 'pekerjaan', 'alamat', 'status_kawin'],
                    'additional_fields' => ['nama_calon_pasangan', 'nik_calon_pasangan', 'alamat_calon_pasangan'],
                    'intro'            => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'             => 'Berdasarkan data kependudukan dan keterangan yang bersangkutan, pemohon tersebut bermaksud melangsungkan pernikahan dengan calon pasangan sebagaimana keterangan di bawah ini.',
                    'target_instansi'  => 'Kantor Urusan Agama (KUA)',
                    'required_fields'  => ['nama_lengkap', 'nik', 'nama_calon_pasangan', 'nik_calon_pasangan', 'alamat_calon_pasangan'],
                    'closing'          => 'Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.',
                    'signature'        => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKN',
                'masa_berlaku_hari' => 30,
                'is_active'         => true,
                'keterangan'        => 'Syarat pendaftaran nikah di KUA bagi pemohon yang beragama Islam',
            ],

            // DINONAKTIFKAN — jarang digunakan
            /*
            [
                'kode'              => 'SKCR',
                'nama'              => 'Surat Keterangan Cerai',
                'deskripsi'         => 'Surat keterangan bahwa pemohon telah bercerai secara resmi',
                'template_category' => 'pernyataan',
                'template_sections' => [
                    'data_fields'        => ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'alamat'],
                    'related_data_intro' => 'Berdasarkan dokumen resmi, yang bersangkutan telah bercerai dengan:',
                    'related_fields'     => ['nama_mantan_pasangan', 'tanggal_cerai', 'nomor_akta_cerai'],
                    'intro'              => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'               => 'Berdasarkan dokumen resmi yang ditunjukkan, yang bersangkutan telah bercerai secara sah menurut hukum.',
                    'show_purpose'       => true,
                    'signature'          => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKCR',
                'masa_berlaku_hari' => 90,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk pengurusan dokumen kependudukan pasca cerai',
            ],
            [
                'kode'              => 'SKJD',
                'nama'              => 'Surat Keterangan Janda/Duda',
                'deskripsi'         => 'Surat keterangan status janda atau duda karena pasangan meninggal',
                'template_category' => 'pernyataan',
                'template_sections' => [
                    'data_fields'        => ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'alamat'],
                    'related_data_intro' => 'Adalah benar berstatus janda/duda sejak meninggalnya:',
                    'related_fields'     => ['nama_pasangan', 'tanggal_meninggal'],
                    'intro'              => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'               => 'Sejak meninggalnya pasangan tersebut sampai dengan surat ini dibuat, yang bersangkutan tidak/belum menikah lagi.',
                    'show_purpose'       => true,
                    'signature'          => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKJD',
                'masa_berlaku_hari' => 90,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk berbagai keperluan administrasi dan hak waris',
            ],
            [
                'kode'              => 'SKPEND',
                'nama'              => 'Surat Keterangan Penduduk Sementara',
                'deskripsi'         => 'Surat keterangan bagi penduduk yang tinggal sementara di desa',
                'template_category' => 'keterangan',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'alamat_asal', 'alamat_sementara'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'          => 'Adalah benar tinggal sementara di wilayah Desa kami pada alamat tersebut di atas.',
                    'show_purpose'  => true,
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKPEND',
                'masa_berlaku_hari' => 90,
                'is_active'         => true,
                'keterangan'        => 'Untuk pendatang yang belum pindah KTP namun tinggal di wilayah desa',
            ],
            */

            // =========================================================
            // KELOMPOK 2 — EKONOMI & SOSIAL
            // =========================================================
            [
                'kode'              => 'SKTM',
                'nama'              => 'Surat Keterangan Tidak Mampu',
                'deskripsi'         => 'Surat keterangan kondisi ekonomi untuk pengurusan KIS, KIP, atau bantuan sosial',
                'template_category' => 'keterangan',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'bin_binti', 'tempat_tanggal_lahir', 'nik', 'no_kk', 'kewarganegaraan', 'agama', 'jenis_kelamin', 'pekerjaan', 'alamat', 'alamat_domisili'],
                    'additional_fields' => ['nama_anak', 'bin_binti_anak', 'tempat_lahir_anak', 'tanggal_lahir_anak', 'nik_anak', 'no_kk_anak', 'kewarganegaraan_anak', 'agama_anak', 'jenis_kelamin_anak', 'pekerjaan_anak', 'alamat_anak', 'alamat_domisili_anak', 'keperluan_program'],
                    'intro'         => 'Kepala Desa menerangkan bahwa:',
                    'body'          => 'Keluarga tersebut tergolong keluarga tidak mampu/kurang mampu berdasarkan data dan keadaan yang sebenarnya.',
                    'required_fields' => ['nama_lengkap', 'nik', 'nama_anak', 'bin_binti_anak', 'tempat_lahir_anak', 'tanggal_lahir_anak', 'nik_anak', 'no_kk_anak', 'kewarganegaraan_anak', 'agama_anak', 'jenis_kelamin_anak', 'pekerjaan_anak', 'alamat_anak', 'alamat_domisili_anak'],
                    'show_purpose'  => false,
                    'closing'       => 'Demikian Surat Keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.',
                    'suppress_default_closing' => true,
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKTM',
                'masa_berlaku_hari' => 90,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk pengurusan bantuan sosial, KIS, KIP, beasiswa',
            ],
            [
                'kode'              => 'SKU',
                'nama'              => 'Surat Keterangan Usaha',
                'deskripsi'         => 'Surat keterangan kepemilikan usaha untuk pengajuan pinjaman atau izin',
                'template_category' => 'keterangan',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'tempat_tanggal_lahir', 'nik', 'no_kk', 'kewarganegaraan', 'agama', 'status_perkawinan', 'pekerjaan', 'alamat', 'alamat_domisili', 'nama_usaha', 'jenis_usaha', 'alamat_usaha', 'ukuran_tempat_usaha', 'jumlah_tenaga_pembantu', 'catatan_usaha'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'          => 'Adalah benar memiliki dan menjalankan usaha sebagaimana tersebut di atas di wilayah Desa kami.',
                    'required_fields' => ['nama_lengkap', 'nik', 'nama_usaha', 'alamat_usaha', 'ukuran_tempat_usaha', 'jumlah_tenaga_pembantu'],
                    'show_purpose'  => false,
                    'closing'       => 'Demikian Surat Keterangan ini dibuat dengan sebenarnya dan diberikan kepada yang bersangkutan sebagai pegangan dan untuk dipergunakan sebagaimana mestinya.',
                    'suppress_default_closing' => true,
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKU',
                'masa_berlaku_hari' => 365,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk pengajuan kredit usaha, izin usaha mikro',
            ],

            // DINONAKTIFKAN — jarang digunakan
            /*
            [
                'kode'              => 'SKHBS',
                'nama'              => 'Surat Keterangan Penghasilan',
                'deskripsi'         => 'Surat keterangan penghasilan warga untuk keperluan administrasi',
                'template_category' => 'keterangan',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'nik', 'pekerjaan', 'alamat', 'penghasilan_bulanan'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'          => 'Berdasarkan keterangan yang bersangkutan dan pengamatan, mempunyai penghasilan sebagaimana tersebut di atas.',
                    'show_purpose'  => true,
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKHBS',
                'masa_berlaku_hari' => 90,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk beasiswa, kredit, atau permohonan keringanan biaya',
            ],
            [
                'kode'              => 'SKTNI',
                'nama'              => 'Surat Keterangan Tanggungan',
                'deskripsi'         => 'Surat keterangan jumlah tanggungan dalam keluarga',
                'template_category' => 'keterangan',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'nik', 'pekerjaan', 'alamat', 'jumlah_tanggungan'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'          => 'Berdasarkan data Kartu Keluarga, yang bersangkutan mempunyai tanggungan sebagaimana tersebut di atas.',
                    'show_purpose'  => true,
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKTNI',
                'masa_berlaku_hari' => 90,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk keperluan tunjangan, pajak, atau beasiswa',
            ],
            [
                'kode'              => 'SKWRST',
                'nama'              => 'Surat Keterangan Ahli Waris',
                'deskripsi'         => 'Surat keterangan susunan ahli waris dari almarhum/almarhumah',
                'template_category' => 'pernyataan',
                'template_sections' => [
                    'data_fields'        => ['nama_almarhum', 'nik_almarhum', 'tanggal_meninggal', 'alamat_terakhir'],
                    'related_data_intro' => 'Telah meninggal dunia dan meninggalkan ahli waris yang sah.',
                    'intro'              => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'               => 'Para ahli waris tersebut di atas adalah satu-satunya ahli waris yang sah dari almarhum/almarhumah.',
                    'show_purpose'       => true,
                    'signature'          => 'dual',
                ],
                'prefix_nomor'      => 'SKWRST',
                'masa_berlaku_hari' => null,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk pengurusan warisan, tanah, rekening, atau asuransi',
            ],
            */

            // =========================================================
            // KELOMPOK 3 — HUKUM & KETERTIBAN
            // =========================================================

            // DINONAKTIFKAN — jarang digunakan
            /*
            [
                'kode'              => 'SKBD',
                'nama'              => 'Surat Keterangan Bersih Diri',
                'deskripsi'         => 'Surat keterangan bahwa pemohon dan keluarga tidak terlibat organisasi terlarang atau tindak kriminal',
                'template_category' => 'keterangan',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama', 'pekerjaan', 'alamat'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan dengan sebenarnya bahwa:',
                    'body'          => 'Berdasarkan catatan yang ada dan sepanjang pengetahuan kami, nama tersebut di atas beserta keluarganya tidak pernah terlibat dalam organisasi terlarang (G.30.S/PKI) maupun organisasi radikal lainnya, serta tidak pernah tersangkut tindak pidana atau pelanggaran hukum lainnya.',
                    'show_purpose'  => true,
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKBD',
                'masa_berlaku_hari' => 90,
                'is_active'         => true,
                'keterangan'        => 'Sering digunakan sebagai syarat pendaftaran TNI, POLRI, atau instansi pemerintahan tertentu untuk memastikan track record pemohon.',
            ],
            */

            [
                'kode'              => 'SKBB',
                'nama'              => 'Surat Keterangan Berkelakuan Baik',
                'deskripsi'         => 'Surat pengantar untuk pengurusan SKCK di kepolisian',
                'template_category' => 'keterangan',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama', 'pekerjaan', 'alamat'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'          => 'Sepanjang pengetahuan kami, yang bersangkutan berkelakuan baik dan tidak pernah terlibat dalam tindak kejahatan atau pelanggaran hukum.',
                    'required_fields' => ['nama_lengkap', 'nik'],
                    'show_purpose'  => true,
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKBB',
                'masa_berlaku_hari' => 90,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk pengurusan SKCK, melamar pekerjaan, atau sekolah',
            ],

            // DINONAKTIFKAN — jarang digunakan
            /*
            [
                'kode'              => 'SKKH',
                'nama'              => 'Surat Keterangan Kehilangan',
                'deskripsi'         => 'Surat pengantar untuk laporan kehilangan dokumen ke polisi',
                'template_category' => 'keterangan',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'nik', 'alamat', 'jenis_dokumen_hilang', 'nomor_dokumen', 'tempat_kehilangan', 'waktu_kehilangan'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'          => 'Telah kehilangan dokumen sebagaimana tersebut di atas dan bermaksud untuk melaporkan ke pihak kepolisian.',
                    'purpose_label' => 'Surat ini dibuat sebagai pengantar untuk laporan kehilangan di',
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKKH',
                'masa_berlaku_hari' => 7,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk melapor kehilangan KTP, KK, atau dokumen penting',
            ],
            [
                'kode'              => 'SKPBL',
                'nama'              => 'Surat Keterangan Pembebasan Lahan',
                'deskripsi'         => 'Surat keterangan tidak sengketa atas tanah/lahan di wilayah desa',
                'template_category' => 'keterangan',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'nik', 'alamat', 'lokasi_tanah', 'luas_tanah', 'batas_utara', 'batas_selatan', 'batas_timur', 'batas_barat'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'          => 'Sepanjang pengetahuan kami, tanah/lahan tersebut di atas tidak dalam sengketa dan bebas dari permasalahan hukum.',
                    'show_purpose'  => true,
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKPBL',
                'masa_berlaku_hari' => 90,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk proses jual-beli, sertifikasi, atau hibah tanah',
            ],
            [
                'kode'              => 'SKGNT',
                'nama'              => 'Surat Keterangan Ganti Nama',
                'deskripsi'         => 'Surat keterangan permohonan ganti nama warga',
                'template_category' => 'keterangan',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'nama_baru', 'alasan_ganti_nama'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'          => 'Bermaksud untuk mengganti nama sebagaimana tersebut di atas dengan alasan yang telah dikemukakan.',
                    'purpose_label' => 'Surat ini dibuat sebagai syarat pengurusan ganti nama di',
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKGNT',
                'masa_berlaku_hari' => 30,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan sebagai syarat pengurusan ganti nama ke Pengadilan Negeri',
            ],
            */

            // =========================================================
            // KELOMPOK 4 — PERIZINAN & KEGIATAN
            // =========================================================

            [
                'kode'              => 'SIK',
                'nama'              => 'Surat Izin Keramaian',
                'deskripsi'         => 'Surat izin untuk mengadakan acara/keramaian di wilayah desa',
                'template_category' => 'izin',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'nik', 'alamat'],
                    'detail_fields' => ['jenis_kegiatan', 'tanggal_mulai', 'tanggal_selesai', 'waktu', 'tempat', 'jumlah_undangan'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, memberikan izin keramaian kepada:',
                    'body'          => 'Untuk mengadakan kegiatan sebagaimana tersebut di bawah ini:',
                    'required_fields' => ['nama_lengkap', 'nik', 'jenis_kegiatan', 'tanggal_mulai', 'tanggal_selesai', 'waktu', 'tempat'],
                    'terms'         => [
                        'Wajib menjaga ketertiban dan keamanan selama kegiatan berlangsung.',
                        'Tidak mengganggu ketertiban umum dan masyarakat sekitar.',
                        'Mematuhi peraturan perundang-undangan yang berlaku.',
                        'Izin ini dapat dicabut apabila terjadi pelanggaran.',
                    ],
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SIK',
                'masa_berlaku_hari' => null,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk acara pernikahan, khitanan, atau kegiatan yang mengundang banyak orang',
            ],

            // DINONAKTIFKAN — jarang digunakan
            /*
            [
                'kode'              => 'SIBNG',
                'nama'              => 'Surat Izin Mendirikan Bangunan Desa',
                'deskripsi'         => 'Surat rekomendasi desa untuk pengurusan IMB ke kecamatan',
                'template_category' => 'izin',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'nik', 'alamat'],
                    'detail_fields' => ['jenis_bangunan', 'luas_bangunan', 'lokasi_bangunan'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, memberikan rekomendasi izin mendirikan bangunan kepada:',
                    'body'          => 'Untuk mendirikan bangunan sebagaimana tersebut di bawah ini:',
                    'terms'         => [
                        'Bangunan tidak mengganggu fasilitas umum dan kepentingan masyarakat.',
                        'Memenuhi persyaratan jarak sempadan sesuai peraturan yang berlaku.',
                        'Wajib mengurus izin lebih lanjut ke instansi berwenang.',
                    ],
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SIBNG',
                'masa_berlaku_hari' => 30,
                'is_active'         => true,
                'keterangan'        => 'Sebagai syarat pengajuan IMB ke instansi berwenang',
            ],
            [
                'kode'              => 'SREKOM',
                'nama'              => 'Surat Rekomendasi Desa',
                'deskripsi'         => 'Surat rekomendasi umum dari kepala desa untuk berbagai keperluan warga',
                'template_category' => 'rekomendasi',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'alamat'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, memberikan rekomendasi kepada:',
                    'body'          => 'Adalah benar warga Desa kami yang kami kenal baik dan memiliki perilaku yang baik selama tinggal di wilayah Desa kami.',
                    'show_purpose'  => true,
                    'closing'       => 'Kami merekomendasikan yang bersangkutan untuk mendapatkan pertimbangan sebagaimana mestinya.',
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SREKOM',
                'masa_berlaku_hari' => 30,
                'is_active'         => true,
                'keterangan'        => 'Untuk keperluan yang membutuhkan rekomendasi resmi dari desa',
            ],
            [
                'kode'              => 'SKTNH',
                'nama'              => 'Surat Keterangan Tanah/Hibah',
                'deskripsi'         => 'Surat keterangan pemilikan atau hibah tanah dari desa',
                'template_category' => 'keterangan',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'nik', 'alamat', 'lokasi_tanah', 'luas_tanah', 'asal_usul_tanah', 'batas_utara', 'batas_selatan', 'batas_timur', 'batas_barat'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'          => 'Adalah benar memiliki/menguasai tanah sebagaimana tersebut di atas berdasarkan bukti kepemilikan yang sah.',
                    'show_purpose'  => true,
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKTNH',
                'masa_berlaku_hari' => null,
                'is_active'         => true,
                'keterangan'        => 'Sebagai alas hak sebelum pengurusan sertifikat ke BPN',
            ],
            */

            // =========================================================
            // KELOMPOK 5 — PENDIDIKAN & KEPEMUDAAN
            // =========================================================

            // DINONAKTIFKAN — jarang digunakan
            /*
            [
                'kode'              => 'SKBS',
                'nama'              => 'Surat Keterangan Beasiswa',
                'deskripsi'         => 'Surat keterangan dari desa sebagai syarat pengajuan beasiswa',
                'template_category' => 'rekomendasi',
                'template_sections' => [
                    'data_fields'     => ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'nama_sekolah', 'kelas'],
                    'activity_intro'  => 'Berkeinginan untuk mengajukan beasiswa dengan keterangan:',
                    'activity_fields' => ['nama_instansi', 'jenis_beasiswa'],
                    'intro'           => 'Yang bertanda tangan di bawah ini, Kepala Desa, memberikan rekomendasi kepada:',
                    'body'            => 'Adalah benar warga Desa kami yang berasal dari keluarga kurang mampu dan berprestasi dalam bidang akademik.',
                    'closing'         => 'Kami merekomendasikan agar yang bersangkutan dapat dipertimbangkan untuk menerima beasiswa tersebut.',
                    'signature'       => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKBS',
                'masa_berlaku_hari' => 90,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk pengajuan beasiswa pemerintah atau swasta',
            ],
            [
                'kode'              => 'SKAKTF',
                'nama'              => 'Surat Keterangan Aktif Organisasi',
                'deskripsi'         => 'Surat keterangan keaktifan warga dalam organisasi kemasyarakatan di desa',
                'template_category' => 'rekomendasi',
                'template_sections' => [
                    'data_fields'     => ['nama_lengkap', 'nik', 'alamat'],
                    'activity_intro'  => 'Aktif dalam organisasi kemasyarakatan di desa:',
                    'activity_fields' => ['nama_organisasi', 'jabatan_organisasi', 'periode_aktif'],
                    'intro'           => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'            => 'Adalah benar aktif dalam kegiatan organisasi kemasyarakatan di wilayah Desa kami.',
                    'show_purpose'    => true,
                    'signature'       => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKAKTF',
                'masa_berlaku_hari' => 90,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk keperluan administrasi organisasi atau beasiswa',
            ],
            [
                'kode'              => 'SKPNLTN',
                'nama'              => 'Surat Keterangan Penelitian',
                'deskripsi'         => 'Surat izin dan keterangan bagi mahasiswa atau peneliti yang melakukan penelitian di wilayah desa',
                'template_category' => 'rekomendasi',
                'template_sections' => [
                    'data_fields'     => ['nama_lengkap', 'nik', 'alamat', 'nama_instansi', 'nim_nip'],
                    'activity_intro'  => 'Bermaksud untuk melakukan penelitian dengan keterangan:',
                    'activity_fields' => ['judul_penelitian', 'periode_penelitian', 'lokasi_penelitian'],
                    'intro'           => 'Yang bertanda tangan di bawah ini, Kepala Desa, memberikan izin penelitian kepada:',
                    'body'            => 'Untuk melakukan penelitian di wilayah Desa kami dengan ketentuan tidak mengganggu ketertiban umum dan masyarakat.',
                    'closing'         => 'Kami berharap hasil penelitian dapat memberikan manfaat bagi pengembangan Desa kami.',
                    'signature'       => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKPNLTN',
                'masa_berlaku_hari' => 60,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk KKN, skripsi, tesis, atau penelitian lainnya',
            ],
            [
                'kode'              => 'SKMAGANG',
                'nama'              => 'Surat Keterangan Magang/PKL',
                'deskripsi'         => 'Surat keterangan penerimaan atau selesai magang di kantor desa',
                'template_category' => 'rekomendasi',
                'template_sections' => [
                    'data_fields'     => ['nama_lengkap', 'nik', 'alamat', 'nama_instansi', 'nim_nip'],
                    'activity_intro'  => 'Telah melaksanakan magang/PKL di Kantor Desa dengan keterangan:',
                    'activity_fields' => ['periode_magang', 'bagian_penempatan'],
                    'intro'           => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'            => 'Telah melaksanakan magang/Praktik Kerja Lapangan (PKL) di Kantor Desa kami dengan baik.',
                    'closing'         => 'Selama melaksanakan magang, yang bersangkutan menunjukkan dedikasi dan kinerja yang baik.',
                    'signature'       => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKMAGANG',
                'masa_berlaku_hari' => null,
                'is_active'         => true,
                'keterangan'        => 'Diberikan kepada peserta magang/PKL yang telah selesai',
            ],
            */

            // =========================================================
            // KELOMPOK 6 — KESEHATAN & SOSIAL
            // =========================================================

            // DINONAKTIFKAN — jarang digunakan
            /*
            [
                'kode'              => 'SKBPJS',
                'nama'              => 'Surat Pengantar BPJS Kesehatan',
                'deskripsi'         => 'Surat pengantar desa untuk pendaftaran atau perubahan data BPJS Kesehatan',
                'template_category' => 'pengantar',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'nik', 'no_kk', 'alamat'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, memberikan pengantar kepada:',
                    'body'          => 'Bermaksud untuk mendaftar/mengubah data kepesertaan BPJS Kesehatan.',
                    'purpose_label' => 'Surat ini ditujukan kepada Kantor BPJS Kesehatan untuk keperluan',
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKBPJS',
                'masa_berlaku_hari' => 30,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk pengurusan BPJS bagi warga tidak mampu (PBI)',
            ],
            [
                'kode'              => 'SKLANSIA',
                'nama'              => 'Surat Keterangan Lanjut Usia',
                'deskripsi'         => 'Surat keterangan warga berusia lanjut untuk mendapatkan hak atau bantuan',
                'template_category' => 'keterangan',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'usia'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'          => 'Adalah benar warga Desa kami yang telah berusia lanjut (lansia) dan berhak mendapatkan program bantuan/fasilitas lansia.',
                    'show_purpose'  => true,
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKLANSIA',
                'masa_berlaku_hari' => 180,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk mendapatkan program bantuan lansia dari pemerintah',
            ],
            [
                'kode'              => 'SKDISAB',
                'nama'              => 'Surat Keterangan Penyandang Disabilitas',
                'deskripsi'         => 'Surat keterangan bagi warga penyandang disabilitas',
                'template_category' => 'keterangan',
                'template_sections' => [
                    'data_fields'   => ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'jenis_disabilitas'],
                    'intro'         => 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:',
                    'body'          => 'Adalah benar warga Desa kami yang merupakan penyandang disabilitas dan berhak mendapatkan program bantuan/fasilitas disabilitas.',
                    'show_purpose'  => true,
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'SKDISAB',
                'masa_berlaku_hari' => 365,
                'is_active'         => true,
                'keterangan'        => 'Diperlukan untuk mengakses program bantuan dan fasilitas disabilitas',
            ],
            */

            // =========================================================
            // KELOMPOK 7 — SURAT RESMI DESA (INTERNAL)
            // =========================================================

            // DINONAKTIFKAN — jarang digunakan
            /*
            [
                'kode'              => 'SUNDGN',
                'nama'              => 'Surat Undangan Resmi Desa',
                'deskripsi'         => 'Surat undangan resmi yang dikeluarkan oleh pemerintah desa',
                'template_category' => 'internal',
                'template_sections' => [
                    'salam_pembuka'  => 'Dengan hormat,',
                    'body'           => 'Bersama ini kami mengundang Bapak/Ibu/Saudara untuk menghadiri acara sebagaimana tersebut di bawah ini.',
                    'salam_penutup'  => 'Atas perhatian dan kehadirannya, kami ucapkan terima kasih.',
                    'signature'      => 'kepala_desa',
                ],
                'prefix_nomor'      => 'UNDGN',
                'masa_berlaku_hari' => null,
                'is_active'         => true,
                'keterangan'        => 'Untuk kegiatan musyawarah desa, rapat RT/RW, atau acara resmi desa',
            ],
            [
                'kode'              => 'SKTGS',
                'nama'              => 'Surat Tugas',
                'deskripsi'         => 'Surat tugas bagi perangkat desa yang menjalankan tugas kedinasan',
                'template_category' => 'internal',
                'template_sections' => [
                    'salam_pembuka' => 'Dalam rangka pelaksanaan tugas kedinasan, dengan ini:',
                    'body'          => 'Kepala Desa memberikan tugas kepada pegawai/perangkat desa tersebut di atas.',
                    'salam_penutup' => 'Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.',
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'TUGAS',
                'masa_berlaku_hari' => null,
                'is_active'         => true,
                'keterangan'        => 'Diberikan kepada perangkat desa yang ditugaskan keluar kantor',
            ],
            [
                'kode'              => 'SMOU',
                'nama'              => 'Surat Perjanjian/MoU',
                'deskripsi'         => 'Surat perjanjian kerja sama antara desa dengan pihak lain',
                'template_category' => 'internal',
                'template_sections' => [
                    'salam_pembuka' => 'Pada hari ini, telah disepakati perjanjian kerja sama antara:',
                    'body'          => 'Kedua belah pihak sepakat untuk menjalin kerja sama sesuai dengan ketentuan yang telah disepakati.',
                    'salam_penutup' => 'Perjanjian ini dibuat dengan itikad baik untuk dilaksanakan oleh kedua belah pihak.',
                    'signature'     => 'dual',
                ],
                'prefix_nomor'      => 'MOU',
                'masa_berlaku_hari' => null,
                'is_active'         => true,
                'keterangan'        => 'Untuk kerja sama desa dengan lembaga, perusahaan, atau desa lain',
            ],
            */

            [
                'kode'              => 'SBALASAN',
                'nama'              => 'Surat Balasan Desa',
                'deskripsi'         => 'Surat balasan resmi atas surat masuk yang diterima desa',
                'template_category' => 'internal',
                'template_sections' => [
                    'salam_pembuka' => 'Dengan hormat,',
                    'detail_fields'  => ['nomor_surat_masuk', 'tanggal_surat_masuk', 'isi_balasan'],
                    'body'          => 'Menindaklanjuti surat yang kami terima, dengan ini kami sampaikan hal-hal sebagai berikut.',
                    'salam_penutup' => 'Demikian surat balasan ini kami sampaikan, atas perhatiannya kami ucapkan terima kasih.',
                    'required_fields' => ['kepada', 'alamat_tujuan', 'perihal', 'keterangan_tambahan'],
                    'signature'     => 'kepala_desa',
                ],
                'prefix_nomor'      => 'BALASAN',
                'masa_berlaku_hari' => null,
                'is_active'         => true,
                'keterangan'        => 'Untuk membalas surat dari instansi lain, warga, atau lembaga',
            ],
        ];

        foreach ($jenisSurat as $data) {
            $data['format_nomor'] ??= '145 / {sequence3} / {kode_surat} / {year}';

            JenisSurat::updateOrCreate(
                ['kode' => $data['kode']],
                $data
            );
        }
    }
}
