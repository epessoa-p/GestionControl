<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Role;
use App\Services\RoleProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateRolesPerCompany extends Command
{
    protected $signature = 'roles:make-per-company';

    protected $description = 'Clona los roles globales en roles por empresa y repunta company_user (idempotente).';

    public function handle(): int
    {
        if (!$this->columnExists()) {
            $this->error("La columna roles.company_id no existe. Ejecuta primero el SQL: database/*_roles_add_company_id.sql");
            return self::FAILURE;
        }

        // Reconstruir las plantillas globales faltantes (fuente para clonar).
        RoleProvisioner::ensureTemplates();
        $this->info('Plantillas globales verificadas.');

        // Incluye empresas eliminadas (soft-delete) por si se restauran luego.
        $companies = Company::withTrashed()->get();
        $this->info("Procesando {$companies->count()} empresa(s) (incluye eliminadas)...");

        foreach ($companies as $company) {
            // 1. Sembrar los roles base de la empresa (admin).
            RoleProvisioner::seedDefaultsForCompany($company->id);

            // 2. Repuntar cada asignación que apunte a una plantilla (company_id NULL).
            $assignments = DB::table('company_user')
                ->where('company_id', $company->id)
                ->whereNotNull('role_id')
                ->get();

            foreach ($assignments as $assignment) {
                $role = Role::find($assignment->role_id);
                if (!$role) {
                    continue;
                }

                // Ya apunta a un rol de la empresa correcta → nada que hacer.
                if ($role->company_id === $company->id) {
                    continue;
                }

                // Apunta a una plantilla (u otra empresa): clonar por slug a esta empresa.
                $clone = RoleProvisioner::cloneTemplateForCompany($role->slug, $company->id)
                    ?? Role::where('company_id', $company->id)->where('slug', $role->slug)->first();

                if ($clone) {
                    DB::table('company_user')->where('id', $assignment->id)->update(['role_id' => $clone->id]);
                    $this->line("  · {$company->name}: '{$role->slug}' → rol #{$clone->id}");
                }
            }
        }

        $this->info('Listo. Roles migrados por empresa.');
        return self::SUCCESS;
    }

    private function columnExists(): bool
    {
        $db = DB::getDatabaseName();
        return DB::table('information_schema.columns')
            ->where('table_schema', $db)
            ->where('table_name', 'roles')
            ->where('column_name', 'company_id')
            ->exists();
    }
}
