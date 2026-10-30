<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($subject ?? 'Mercatoria'); ?></title>
</head>
<body style="margin:0;padding:0;background-color:#F2EFE8;font-family:Georgia,'Times New Roman',serif;color:#141B22;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F2EFE8;">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="width:100%;max-width:560px;background-color:#FFFFFF;border:1px solid #D8D2C4;">
                    <tr>
                        <td align="center" style="padding:24px 28px 16px;border-bottom:1px solid #D8D2C4;">
                            <img src="<?php echo e(asset('logo.png')); ?>" alt="Mercatoria" width="180" style="display:block;height:auto;max-width:180px;">
                            <p style="margin:10px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:#141B22;opacity:0.55;">
                                Merch &amp; Collectibles Store
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($greeting)): ?>
                                <p style="margin:0 0 16px;font-family:Arial,Helvetica,sans-serif;font-size:18px;font-weight:600;color:#141B22;">
                                    <?php echo e($greeting); ?>

                                </p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $introLines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <p style="margin:0 0 12px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:#141B22;">
                                    <?php echo e($line); ?>

                                </p>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($actionText)): ?>
                                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:20px 0;">
                                    <tr>
                                        <td style="background-color:#000000;border-radius:6px;">
                                            <a href="<?php echo e($actionUrl); ?>"
                                               style="display:inline-block;padding:12px 28px;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:600;letter-spacing:1px;color:#FFFFFF;text-decoration:none;">
                                                <?php echo e($actionText); ?>

                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $outroLines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <p style="margin:0 0 12px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:#141B22;">
                                    <?php echo e($line); ?>

                                </p>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($actionText)): ?>
                                <p style="margin:16px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:1.5;color:#141B22;opacity:0.6;">
                                    Jika tombol di atas tidak bisa diklik, salin dan tempel tautan berikut ke browser kamu:<br>
                                    <span style="word-break:break-all;"><?php echo e($actionUrl); ?></span>
                                </p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px;border-top:1px solid #D8D2C4;">
                            <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:1.6;color:#141B22;opacity:0.55;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($salutation)): ?>
                                    <?php echo e($salutation); ?>

                                <?php else: ?>
                                    Salam,<br>Mercatoria
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </p>
                            <p style="margin:10px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#141B22;opacity:0.45;">
                                Email ini dikirim otomatis oleh sistem Mercatoria.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
<?php /**PATH C:\Users\BayuD\OneDrive\Documents\GitHub\v2\mercatoria\resources\views\vendor\notifications/email.blade.php ENDPATH**/ ?>