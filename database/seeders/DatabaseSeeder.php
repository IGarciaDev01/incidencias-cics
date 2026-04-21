<?php

namespace Database\Seeders;

use App\Enums\RolUsuario;
use App\Models\Area;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Admin ─────────────────────────────────────────────────────────
        User::create([
            'nombre'   => 'Administrador',
            'email'    => 'admin@incidencias.test',
            'password' => Hash::make('password'),
            'rol'      => RolUsuario::Admin,
            'activo'   => true,
        ]);

        // ── 2. Subdirección Académica ─────────────────────────────────────────
        User::create([
            'nombre'   => 'María González',
            'email'    => 'subdireccion@incidencias.test',
            'password' => Hash::make('password'),
            'rol'      => RolUsuario::SubdireccionAcademica,
            'activo'   => true,
        ]);

        // ── 3. Capital Humano ─────────────────────────────────────────────────
        User::create([
            'nombre'   => 'Roberto Méndez',
            'email'    => 'capitalhumano@incidencias.test',
            'password' => Hash::make('password'),
            'rol'      => RolUsuario::CapitalHumano,
            'activo'   => true,
        ]);

        // ── 4. Áreas (10) ─────────────────────────────────────────────────────
        $areas = [
            ['nombre' => 'Departamento de Odontología',                           'slug' => 'odontologia'],
            ['nombre' => 'Departamento de Optometría',                            'slug' => 'optometria'],
            ['nombre' => 'Departamento de Psicología',                            'slug' => 'psicologia'],
            ['nombre' => 'Sección de Posgrado e Investigación',                   'slug' => 'posgrado'],
            ['nombre' => 'Gestión Escolar',                                       'slug' => 'gestion-escolar'],
            ['nombre' => 'Becas y Apoyos Económicos',                             'slug' => 'becas'],
            ['nombre' => 'Servicio Social y Titulación',                          'slug' => 'servicio-social'],
            ['nombre' => 'Capital Humano',                                        'slug' => 'capital-humano'],
            ['nombre' => 'Unidad de Informática',                                 'slug' => 'informatica'],
            ['nombre' => 'Biblioteca y Actividades Culturales',                   'slug' => 'biblioteca'],
        ];

        $areasCreadas = [];
        foreach ($areas as $area) {
            $areasCreadas[] = Area::create(array_merge($area, ['activa' => true]));
        }

        // ── 5. Jefes Inmediatos (uno por área) ────────────────────────────────
        $jefes = [
            ['nombre' => 'Dr. Carlos Ramírez',    'email' => 'jefe.odontologia@incidencias.test',    'area' => 'odontologia'],
            ['nombre' => 'Dra. Laura Mendoza',    'email' => 'jefe.optometria@incidencias.test',     'area' => 'optometria'],
            ['nombre' => 'Mtro. Jorge Castillo',  'email' => 'jefe.psicologia@incidencias.test',     'area' => 'psicologia'],
            ['nombre' => 'Dra. Patricia Herrera', 'email' => 'jefe.posgrado@incidencias.test',       'area' => 'posgrado'],
            ['nombre' => 'Lic. Ana Flores',       'email' => 'jefe.gestion@incidencias.test',        'area' => 'gestion-escolar'],
            ['nombre' => 'Lic. Mario Torres',     'email' => 'jefe.becas@incidencias.test',          'area' => 'becas'],
            ['nombre' => 'Lic. Sandra López',     'email' => 'jefe.servicio@incidencias.test',       'area' => 'servicio-social'],
            ['nombre' => 'Lic. Luis Gutiérrez',   'email' => 'jefe.capitalhumano@incidencias.test',  'area' => 'capital-humano'],
            ['nombre' => 'Ing. Pedro Sánchez',    'email' => 'jefe.informatica@incidencias.test',    'area' => 'informatica'],
            ['nombre' => 'Lic. Claudia Reyes',    'email' => 'jefe.biblioteca@incidencias.test',     'area' => 'biblioteca'],
        ];

        $areasIndexadas = collect($areasCreadas)->keyBy('slug');

        foreach ($jefes as $jefe) {
            $area = $areasIndexadas->get($jefe['area']);
            User::create([
                'nombre'   => $jefe['nombre'],
                'email'    => $jefe['email'],
                'password' => Hash::make('password'),
                'rol'      => RolUsuario::JefeInmediato,
                'area_id'  => $area?->id,
                'activo'   => true,
            ]);
        }
    }
}
