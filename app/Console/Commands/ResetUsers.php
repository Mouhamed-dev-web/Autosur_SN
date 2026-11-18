<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class ResetUsers extends Command
{
    protected $signature = 'autosur:reset-users';

    protected $description = 'DÉVELOPPEMENT : supprime tous les utilisateurs et données liées, puis crée un administrateur';

    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('Interdit en production.');

            return self::FAILURE;
        }

        if (! $this->confirm('Supprimer TOUS les utilisateurs et leurs données ?')) {
            return self::SUCCESS;
        }

        $name = $this->ask('Nom de l\'administrateur');
        $email = $this->ask('E-mail');
        $password = $this->secret('Mot de passe (8 caractères minimum)');

        if (strlen((string) $password) < 8 || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('E-mail ou mot de passe invalide. Rien n\'a été supprimé.');

            return self::FAILURE;
        }

        Schema::disableForeignKeyConstraints();
        foreach ([
            'ratings', 'sos_request_declines', 'sos_requests', 'transactions', 'insurance_subscriptions',
            'insurance_quotes', 'technical_visits', 'reminders', 'documents', 'vehicles', 'partners',
            'otp_codes', 'contact_messages', 'audit_logs', 'notifications', 'sessions',
            'model_has_roles', 'model_has_permissions', 'users',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        Schema::enableForeignKeyConstraints();

        // Fichiers des utilisateurs supprimés
        Storage::disk('local')->deleteDirectory('documents');
        Storage::disk('public')->deleteDirectory('vehicle-photos');
        Storage::disk('public')->deleteDirectory('profile-photos');

        Role::findOrCreate('admin', 'web');

        $user = new User(['name' => $name, 'email' => $email, 'password' => $password]);
        $user->save();
        $user->assignRole('admin');

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info("Terminé. Administrateur créé : {$email}");
        $this->line('Connexion : /admin/login (e-mail + mot de passe).');

        return self::SUCCESS;
    }
}