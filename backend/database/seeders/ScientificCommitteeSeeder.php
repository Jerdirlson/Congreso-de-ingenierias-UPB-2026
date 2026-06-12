<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ScientificCommitteeSeeder extends Seeder
{
    /**
     * Crea o actualiza los miembros del Comité Científico.
     * - Si el usuario ya existe: solo añade el rol 'revisor' (sin tocar su rol actual).
     * - Si no existe: crea la cuenta con rol 'participante' + 'revisor', email verificado.
     * - Contraseña temporal para cuentas nuevas: Congreso2026!
     */
    public function run(): void
    {
        $members = [
            // ── Internacionales ──────────────────────────────────────────────
            ['name' => 'Jackeline Martínez Gómez',          'email' => 'j.martinez@umwelt-campus.de',      'institution' => 'Universidad de Ciencias Aplicadas de Trier',       'country' => 'Alemania',       'city' => 'Trier'],
            ['name' => 'Roberto Alonso González Lezcano',   'email' => 'rgonzalezcano@ceu.es',             'institution' => 'Universidad CEU San Pablo',                        'country' => 'España',         'city' => 'Madrid'],
            ['name' => 'Jairo Viola',                        'email' => 'jviola@ucmerced.edu',              'institution' => 'UC Merced',                                        'country' => 'EE.UU',          'city' => 'Merced'],
            ['name' => 'Paalo Andrea Moreno Yañez',          'email' => 'morp2616@usherbrooke.ca',          'institution' => 'Université de Sherbrooke',                         'country' => 'Canadá',         'city' => 'Sherbrooke'],
            ['name' => 'Natalia Andrea Cano Londoño',        'email' => 'n.a.canolondono@utwente.nl',       'institution' => 'University of Twente',                             'country' => 'Países Bajos',   'city' => 'Enschede'],
            ['name' => 'Mirza Cequea',                       'email' => 'mirza.cequea@ucn.cl',              'institution' => 'Universidad Católica del Norte',                   'country' => 'Chile',          'city' => 'Antofagasta'],
            ['name' => 'Jorge Arenas Bermudez',              'email' => 'jparenas@uach.cl',                 'institution' => 'Universidad Austral de Chile',                     'country' => 'Chile',          'city' => 'Valdivia'],
            ['name' => 'Juan Felipe Miranda',                'email' => 'jfmiranda@ucsp.edu.pe',            'institution' => 'Universidad San Pablo',                            'country' => 'Perú',           'city' => 'Arequipa'],
            ['name' => 'Carlos Renzo Rivera',                'email' => 'crrivera@ucsp.edu.pe',             'institution' => 'Universidad Católica San Pablo',                   'country' => 'Perú',           'city' => 'Arequipa'],
            ['name' => 'Fredy Huamán',                       'email' => 'fhuaman@ucsp.edu.pe',              'institution' => 'Universidad Católica San Pablo de Arequipa',       'country' => 'Perú',           'city' => 'Arequipa'],
            ['name' => 'Jorge Guillermo Díaz Rodríguez',     'email' => 'jorgegdiaz@tec.mx',               'institution' => 'Campus Guadalajara - Tecnológico de Monterrey',    'country' => 'México',         'city' => 'Guadalajara'],
            // Jorge Cerna Cortéz — sin correo, omitido
            ['name' => 'Dora Luz González Bañales',          'email' => 'doraglez@itdurango.edu.mx',        'institution' => 'Instituto Tecnológico de Durango',                 'country' => 'México',         'city' => 'Durango'],

            // ── Colombia (externos a UPB) ─────────────────────────────────────
            ['name' => 'Yanyn Aurora Rincón Quintero',       'email' => 'yanynrincon@gmail.com',            'institution' => 'Universidad del Atlántico',                        'country' => 'Colombia',       'city' => 'Barranquilla'],
            ['name' => 'Marianela Luzardo',                  'email' => 'manelubri@gmail.com',              'institution' => 'Asesora Experta en Estadística',                   'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Jorge Luis Chacón Velasco',          'email' => 'jchacon@uis.edu.co',               'institution' => 'Universidad Industrial de Santander',              'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Yesid Javier Rueda Ordoñez',         'email' => 'yjruedao@uis.edu.co',              'institution' => 'Universidad Industrial de Santander',              'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'César Augusto Sierra Avila',         'email' => 'casierraa@unal.edu.co',            'institution' => 'Universidad Nacional de Colombia',                 'country' => 'Colombia',       'city' => 'Bogotá'],
            ['name' => 'Gustavo Emilio Ramirez Caballero',   'email' => 'gusramca@uis.edu.co',              'institution' => 'Universidad Industrial de Santander',              'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Miguel David Rojas López',           'email' => 'mdrojas@unal.edu.co',              'institution' => 'Universidad Nacional de Colombia',                 'country' => 'Colombia',       'city' => 'Bogotá'],
            ['name' => 'Carlos Eduardo García Sánchez',      'email' => 'cgarcia@cdtdegas.com',             'institution' => 'CDT del Gas',                                      'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Neila Mantilla Barbosa',             'email' => 'nmantill3@cuc.edu.co',             'institution' => 'Universidad de la Costa',                          'country' => 'Colombia',       'city' => 'Barranquilla'],
            ['name' => 'Sebastian Alberto Pelaez',           'email' => 'spelaez@poligran.edu.co',          'institution' => 'Politécnico Grancolombiano',                       'country' => 'Colombia',       'city' => 'Bogotá'],
            // Elena Losik — sin correo, omitida
            ['name' => 'Johan Leandro Téllez Garzón',        'email' => 'jtellez@correo.uts.edu.co',        'institution' => 'Unidades Tecnológicas de Santander',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Marcela Villa Marulanda',            'email' => 'm.villa@galapagoagro.co',           'institution' => 'Galápago Agroconsultores',                         'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Hernan Gonzalez Acuña',              'email' => 'hgonzalez3@unab.edu.co',           'institution' => 'Universidad Autónoma de Bucaramanga',              'country' => 'Colombia',       'city' => 'Bucaramanga'],

            // ── UPB Bucaramanga ───────────────────────────────────────────────
            ['name' => 'Mónica Rocío Ordóñez Rodríguez',     'email' => 'monica.ordonez@upb.edu.co',        'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Omar Pinzon Ardila',                  'email' => 'omar.pinzon@upb.edu.co',           'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Luis Angel Silva',                   'email' => 'luis.angel@upb.edu.co',            'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Alexandra Cerón Vivas',              'email' => 'alexandra.ceron@upb.edu.co',       'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Claudia Sofia Quintero Duque',       'email' => 'claudia.quintero@upb.edu.co',      'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Olga Lucía Duarte Bolivar',          'email' => 'olga.duarte@upb.edu.co',           'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Alba Soraya Aguilar Jiménez',        'email' => 'alba.aguilar@upb.edu.co',          'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Elsa Beatriz Gutiérrez Navas',       'email' => 'elsa.gutierrez@upb.edu.co',        'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Maryory Patricia Villamizar Leon',   'email' => 'maryory.villamizar@upb.edu.co',    'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Ludym Jaimes Carrillo',              'email' => 'ludym.jaimes@upb.edu.co',          'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Marco Villamizar',                   'email' => 'marco.villamizar@upb.edu.co',      'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Jairo Núñez Rodriguez',              'email' => 'jairo.nunez@upb.edu.co',           'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Juan Carlos Mantilla Saavedra',      'email' => 'juan.mantilla@upb.edu.co',         'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Gabriel Fernando García Sánchez',    'email' => 'gabriel.garcia@upb.edu.co',        'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Margareth Viecco Márquez',           'email' => 'margareth.viecco@upb.edu.co',      'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Emil Hernandez Arroyo',              'email' => 'emil.hernandez@upb.edu.co',        'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Diego Alejandro Guzmán Arias',       'email' => 'diego.guzman@upb.edu.co',          'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Sergio Andrés Gómez',                'email' => 'sergio.gomezs@upb.edu.co',         'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Juan Carlos Forero Sarmiento',       'email' => 'juan.forero@upb.edu.co',           'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Norma Cristina Solarte Vanegas',     'email' => 'norma.solarte@upb.edu.co',         'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Claudia Patricia Retamoso Llamas',   'email' => 'claudia.retamoso@upb.edu.co',      'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Edwin Córdoba',                      'email' => 'edwin.cordoba@upb.edu.co',         'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Alfonso Santos Jaimes',              'email' => 'alfonso.santos@upb.edu.co',        'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Rolando Guzman Lopez',               'email' => 'rolando.guzman@upb.edu.co',        'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Gloria Patricia Fernández Morales',  'email' => 'patricia.fernandez@upb.edu.co',    'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Lenin Javier Serrano Gil',           'email' => 'lenin.serrano@upb.edu.co',         'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Diego Méndez Chavez',                'email' => 'diego-mendez@javeriana.edu.co',    'institution' => 'Universidad Pontificia Javeriana',                 'country' => 'Colombia',       'city' => 'Bogotá'],
            ['name' => 'Claudia Paulina González Cuervo',    'email' => 'claudia.gonzalez@upb.edu.co',      'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Johanna Marcela Suárez Pedraza',     'email' => 'jomasupe@hotmail.com',             'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Javier Enrique Lizarazo Rueda',      'email' => 'javier.lizarazo@upb.edu.co',       'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Sandra Pilar Reyes Hernandez',       'email' => 'sandra.reyes@upb.edu.co',          'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Claudia Leonor Rueda Guzman',        'email' => 'claudia.rueda@upb.edu.co',         'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Juan Carlos Villamizar',             'email' => 'juan.villamizar@upb.edu.co',       'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Orlando Federico González Casallas', 'email' => 'orlando.gonzalez@upb.edu.co',      'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Juan Manuel Arguello',               'email' => 'juan.arguello@upb.edu.co',         'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
            ['name' => 'Jacqueline Santamaría Valbuena',     'email' => 'jacque.santamaria@upb.edu.co',     'institution' => 'Universidad Pontificia Bolivariana',               'country' => 'Colombia',       'city' => 'Bucaramanga'],
        ];

        $emails   = array_map(fn ($m) => strtolower($m['email']), $members);
        $created  = 0;
        $updated  = 0;
        $pwReset  = 0;

        // Corregir contraseñas de cuentas del comité creadas por el seeder anterior
        // (identificadas por phone = 'N/A', que solo usa este seeder)
        User::whereIn('email', $emails)->where('phone', 'N/A')->each(function (User $u) use (&$pwReset) {
            $u->password = 'Congreso2026!';
            $u->save();
            $pwReset++;
        });

        foreach ($members as $data) {
            $email = strtolower($data['email']);

            $existing = User::where('email', $email)->first();

            if ($existing) {
                // Ya existe: solo añadir rol revisor si no lo tiene
                if (! $existing->hasRole('revisor')) {
                    $existing->assignRole('revisor');
                }
                $updated++;
            } else {
                // No existe: crear cuenta nueva con participante + revisor
                $user = User::create([
                    'name'            => $data['name'],
                    'email'           => $email,
                    'password'        => 'Congreso2026!',
                    'institution'     => $data['institution'],
                    'country'         => $data['country'],
                    'city'            => $data['city'],
                    'phone'           => 'N/A',
                    'document_type'   => 'cedula',
                    'document_number' => 'N/A',
                    'email_verified_at' => now(),
                ]);

                $user->assignRole('participante');
                $user->assignRole('revisor');
                $created++;
            }
        }

        if ($pwReset > 0) {
            $this->command->info("Contraseñas corregidas: {$pwReset} cuentas del comité.");
        }
        $this->command->info("Comité Científico: {$created} cuentas nuevas, {$updated} existentes con rol revisor añadido.");
    }
}
