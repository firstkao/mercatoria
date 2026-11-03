<?php

namespace App\Services\Image;

use Illuminate\Http\UploadedFile;
use Throwable;

/**
 * Terjemahkan kegagalan upload (kode error PHP / exception proses gambar)
 * menjadi alasan yang bisa ditindaklanjuti user.
 */
class UploadError
{
    public static function describe(UploadedFile $file): string
    {
        $limit = ini_get('upload_max_filesize') ?: '?';

        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "file too large — melebihi batas server upload_max_filesize ({$limit}); aplikasi maks 6 MB. Kompres/perkecil file lalu coba lagi.",
            UPLOAD_ERR_PARTIAL => 'upload terputus — file terkirim sebagian. Coba upload ulang.',
            UPLOAD_ERR_NO_TMP_DIR => 'konfigurasi server — folder temporary upload (upload_tmp_dir) tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'permission denied — server gagal menulis file temporary. Cek izin folder temporary.',
            UPLOAD_ERR_EXTENSION => 'ekstensi PHP — sebuah ekstensi PHP menghalangi upload file ini.',
            UPLOAD_ERR_NO_FILE => 'file tidak diterima server.',
            default => 'kode error PHP '.$file->getError().'.',
        };
    }

    public static function describeProcessing(Throwable $e): string
    {
        $msg = $e->getMessage();

        if (str_contains($msg, 'GD not installed')) {
            return $msg;
        }

        if (preg_match('/permission|denied|is_writable|mkdir|copy|write|open stream/i', $msg)) {
            return "permission denied — gagal menulis file gambar ke storage ({$msg}).";
        }

        return "proses gambar error ({$msg}).";
    }
}
