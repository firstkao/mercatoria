<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

#[Signature('admin:create {email : Admin login email} {--name= : Display name}')]
#[Description('Create an admin account, or reset the password of an existing one')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = Str::lower(trim($this->argument('email')));
        $name = $this->option('name') ?: Str::before($email, '@');
        $password = (string) $this->secret('Password (tidak terlihat saat diketik)');
        $confirmation = (string) $this->secret('Ulangi password');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password, 'password_confirmation' => $confirmation],
            ['email' => ['required', 'email'], 'password' => ['required', 'confirmed', Password::min(10)]],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $admin = Admin::updateOrCreate(['email' => $email], ['name' => $name, 'password' => $password]);

        $this->info($admin->wasRecentlyCreated ? "Admin {$email} dibuat." : "Password admin {$email} diperbarui.");

        return self::SUCCESS;
    }
}