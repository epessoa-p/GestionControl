<?php

namespace App\Services;

use App\Models\Role;

class RoleProvisioner
{
    /** Roles base que recibe una empresa nueva. */
    public const DEFAULT_SLUGS = ['admin'];

    /**
     * Clona una plantilla de rol (company_id NULL) hacia una empresa, copiando
     * sus permisos. Idempotente por (company_id, slug): si ya existe el rol de
     * la empresa, lo devuelve sin duplicar.
     */
    public static function cloneTemplateForCompany(string $slug, int $companyId): ?Role
    {
        $existing = Role::where('company_id', $companyId)->where('slug', $slug)->first();
        if ($existing) {
            return $existing;
        }

        // Fuente: plantilla global (NULL) o, como respaldo, cualquier rol con ese
        // slug de otra empresa (auto-reparable si la plantilla se perdió).
        $template = Role::whereNull('company_id')->where('slug', $slug)->first()
            ?? Role::where('slug', $slug)->where('company_id', '!=', $companyId)->first();

        if (!$template) {
            return null;
        }

        $role = Role::create([
            'company_id'  => $companyId,
            'name'        => $template->name,
            'slug'        => $template->slug,
            'description' => $template->description,
        ]);

        $role->permissions()->sync($template->permissions()->pluck('permissions.id')->all());

        return $role;
    }

    /**
     * Garantiza que exista una PLANTILLA global (company_id NULL) por cada slug
     * de rol presente en el sistema. Si falta, la reconstruye copiando desde un
     * rol existente con ese slug (con sus permisos). Idempotente.
     */
    public static function ensureTemplates(): void
    {
        $slugs = Role::query()->select('slug')->distinct()->pluck('slug');

        foreach ($slugs as $slug) {
            $hasTemplate = Role::whereNull('company_id')->where('slug', $slug)->exists();
            if ($hasTemplate) {
                continue;
            }

            $source = Role::where('slug', $slug)->first();
            if (!$source) {
                continue;
            }

            $template = Role::create([
                'company_id'  => null,
                'name'        => $source->name,
                'slug'        => $source->slug,
                'description' => $source->description,
            ]);
            $template->permissions()->sync($source->permissions()->pluck('permissions.id')->all());
        }
    }

    /**
     * Siembra los roles base de una empresa recién creada.
     */
    public static function seedDefaultsForCompany(int $companyId): void
    {
        foreach (self::DEFAULT_SLUGS as $slug) {
            self::cloneTemplateForCompany($slug, $companyId);
        }
    }
}
