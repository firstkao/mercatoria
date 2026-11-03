<?php

use App\Services\Image\UploadError;
use Illuminate\Http\UploadedFile;

test('describe memetakan kode error upload ke alasan yang jelas', function (int $code, string $needle) {
    $file = new UploadedFile('', 'foto.jpg', 'image/jpeg', $code, true);

    expect(UploadError::describe($file))->toContain($needle);
})->with([
    'file too large' => [UPLOAD_ERR_INI_SIZE, 'file too large'],
    'file too large (form)' => [UPLOAD_ERR_FORM_SIZE, 'file too large'],
    'upload terputus' => [UPLOAD_ERR_PARTIAL, 'upload terputus'],
    'tmp dir' => [UPLOAD_ERR_NO_TMP_DIR, 'upload_tmp_dir'],
    'permission denied' => [UPLOAD_ERR_CANT_WRITE, 'permission denied'],
    'ekstensi PHP' => [UPLOAD_ERR_EXTENSION, 'ekstensi PHP'],
]);

test('describeProcessing memetakan exception proses gambar', function () {
    expect(UploadError::describeProcessing(new RuntimeException('GD not installed - ekstensi')))
        ->toContain('GD not installed');

    expect(UploadError::describeProcessing(new RuntimeException('mkdir(): Permission denied')))
        ->toContain('permission denied');

    expect(UploadError::describeProcessing(new RuntimeException('codec rusak')))
        ->toContain('proses gambar error');
});
