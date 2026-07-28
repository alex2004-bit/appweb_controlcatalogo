<?php

namespace Database\Seeders;

use App\Actions\Teams\CreateTeam;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
        ]);

        $createTeam = app(CreateTeam::class);

        $users = [
            [
                'name' => 'Administrador',
                'email' => 'admin@seguridadcatalogo.com',
                'role' => 'Super Admin',
            ],
            [
                'name' => 'Admin General',
                'email' => 'admin@sistema.com',
                'role' => 'Admin',
            ],
            [
                'name' => 'Vendedor Demo',
                'email' => 'vendedor@sistema.com',
                'role' => 'Vendedor',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt('password'),
                ]
            );

            if (! $user->hasRole($userData['role'])) {
                $user->assignRole($userData['role']);
            }

            if (! $user->current_team_id) {
                $createTeam->handle($user, $userData['name'], isPersonal: true);
            }
        }

        $this->callDemoData();
    }

    private function callDemoData(): void
    {
        $categorias = [
            ['nombre' => 'Cámaras', 'descripcion' => 'Cámaras de seguridad y videovigilancia', 'activo' => true],
            ['nombre' => 'DVR / NVR', 'descripcion' => 'Grabadores digitales y de red', 'activo' => true],
            ['nombre' => 'Accesorios', 'descripcion' => 'Accesorios para sistemas de seguridad', 'activo' => true],
            ['nombre' => 'Sistemas de Alarma', 'descripcion' => 'Sistemas de alarma y detección', 'activo' => true],
            ['nombre' => 'Control de Acceso', 'descripcion' => 'Equipos de control de acceso', 'activo' => true],
        ];

        $subcategorias = [
            ['nombre' => 'Cámaras IP', 'parent_id' => 1, 'descripcion' => 'Cámaras de red IP', 'activo' => true],
            ['nombre' => 'Cámaras Analógicas', 'parent_id' => 1, 'descripcion' => 'Cámaras analógicas HD', 'activo' => true],
            ['nombre' => 'Cámaras Térmicas', 'parent_id' => 1, 'descripcion' => 'Cámaras con visión térmica', 'activo' => true],
            ['nombre' => 'NVR IP', 'parent_id' => 2, 'descripcion' => 'Grabadores de red', 'activo' => true],
            ['nombre' => 'DVR HD', 'parent_id' => 2, 'descripcion' => 'Grabadores digitales HD', 'activo' => true],
            ['nombre' => 'Sensores', 'parent_id' => 4, 'descripcion' => 'Sensores de movimiento y apertura', 'activo' => true],
            ['nombre' => 'Cerraduras Eléctricas', 'parent_id' => 5, 'descripcion' => 'Cerraduras y chapas eléctricas', 'activo' => true],
        ];

        $productos = [
            ['categoria_id' => 6, 'nombre' => 'Cámara IP 2MP', 'descripcion' => 'Cámara IP 2MP Full HD con visión nocturna', 'sku' => 'CAM-IP-001', 'precio' => 149.99, 'especificaciones' => [['nombre' => 'Resolución', 'valor' => '2MP (1920x1080)'], ['nombre' => 'Visión Nocturna', 'valor' => '30m'], ['nombre' => 'IR', 'valor' => 'Si']], 'activo' => true],
            ['categoria_id' => 6, 'nombre' => 'Cámara IP 5MP', 'descripcion' => 'Cámara IP 5MP con audio bidireccional', 'sku' => 'CAM-IP-002', 'precio' => 249.99, 'especificaciones' => [['nombre' => 'Resolución', 'valor' => '5MP (2592x1944)'], ['nombre' => 'Audio', 'valor' => 'Bidireccional'], ['nombre' => 'WDR', 'valor' => 'DWDR']], 'activo' => true],
            ['categoria_id' => 6, 'nombre' => 'Cámara IP PTZ 4MP', 'descripcion' => 'Cámara PTZ 4MP con zoom óptico 25x', 'sku' => 'CAM-PTZ-001', 'precio' => 599.99, 'especificaciones' => [['nombre' => 'Resolución', 'valor' => '4MP'], ['nombre' => 'Zoom', 'valor' => '25x óptico'], ['nombre' => 'IP', 'valor' => 'IP66']], 'activo' => true],
            ['categoria_id' => 7, 'nombre' => 'Cámara HD-TVI 2MP', 'descripcion' => 'Cámara analógica HD-TVI 2MP', 'sku' => 'CAM-TVI-001', 'precio' => 89.99, 'especificaciones' => [['nombre' => 'Tecnología', 'valor' => 'HD-TVI'], ['nombre' => 'Resolución', 'valor' => '2MP'], ['nombre' => 'Alcance IR', 'valor' => '20m']], 'activo' => true],
            ['categoria_id' => 9, 'nombre' => 'NVR 8 Canales', 'descripcion' => 'Grabador de red NVR 8 canales con PoE', 'sku' => 'NVR-8CH-001', 'precio' => 399.99, 'especificaciones' => [['nombre' => 'Canales', 'valor' => '8'], ['nombre' => 'PoE', 'valor' => 'Si, 8 puertos'], ['nombre' => 'Almacenamiento', 'valor' => 'Hasta 10TB']], 'activo' => true],
            ['categoria_id' => 10, 'nombre' => 'DVR 16 Canales', 'descripcion' => 'Grabador digital DVR 16 canales 1080p', 'sku' => 'DVR-16CH-001', 'precio' => 299.99, 'especificaciones' => [['nombre' => 'Canales', 'valor' => '16'], ['nombre' => 'Resolución', 'valor' => '1080p'], ['nombre' => 'Almacenamiento', 'valor' => 'Hasta 8TB']], 'activo' => true],
            ['categoria_id' => 11, 'nombre' => 'Sensor de Movimiento', 'descripcion' => 'Sensor de movimiento infrarrojo para interiores', 'sku' => 'SENS-MOV-001', 'precio' => 19.99, 'especificaciones' => [['nombre' => 'Tipo', 'valor' => 'Infrarrojo pasivo'], ['nombre' => 'Alcance', 'valor' => '12m'], ['nombre' => 'Ángulo', 'valor' => '90°']], 'activo' => true],
            ['categoria_id' => 12, 'nombre' => 'Cerradura Eléctrica', 'descripcion' => 'Cerradura eléctrica para puerta de vidrio', 'sku' => 'CERR-ELEC-001', 'precio' => 79.99, 'especificaciones' => [['nombre' => 'Tipo', 'valor' => 'Electromagnética'], ['nombre' => 'Fuerza', 'valor' => '600lbs'], ['nombre' => 'Voltaje', 'valor' => '12VDC']], 'activo' => true],
        ];

        $servicios = [
            ['categoria_id' => 1, 'nombre' => 'Instalación de Cámaras', 'descripcion' => 'Instalación profesional de sistemas de videovigilancia', 'precio' => 250.00, 'duracion_estimada' => '4 horas', 'caracteristicas' => ['Instalación de hasta 4 cámaras', 'Cableado estructurado', 'Configuración de DVR/NVR', 'Pruebas de funcionamiento'], 'activo' => true],
            ['categoria_id' => 4, 'nombre' => 'Instalación de Alarma', 'descripcion' => 'Instalación de sistema de alarma para hogar o negocio', 'precio' => 180.00, 'duracion_estimada' => '3 horas', 'caracteristicas' => ['Panel de control', 'Sensores de puerta/ventana', 'Sensor de movimiento', 'Sirena interior'], 'activo' => true],
            ['categoria_id' => 5, 'nombre' => 'Configuración Control de Acceso', 'descripcion' => 'Configuración e instalación de sistemas de control de acceso', 'precio' => 200.00, 'duracion_estimada' => '3 horas', 'caracteristicas' => ['Lector biométrico o de tarjeta', 'Cerradura eléctrica', 'Fuente de poder', 'Software de gestión'], 'activo' => true],
            ['categoria_id' => 2, 'nombre' => 'Mantenimiento de Equipos', 'descripcion' => 'Mantenimiento preventivo y correctivo de equipos de seguridad', 'precio' => 120.00, 'duracion_estimada' => '2 horas', 'caracteristicas' => ['Limpieza de equipos', 'Revisión de conexiones', 'Actualización de firmware', 'Diagnóstico general'], 'activo' => true],
        ];

        foreach ($categorias as $cat) {
            Categoria::firstOrCreate(
                ['slug' => str($cat['nombre'])->slug()],
                $cat
            );
        }

        foreach ($subcategorias as $sub) {
            Categoria::firstOrCreate(
                ['slug' => str($sub['nombre'])->slug()],
                $sub
            );
        }

        foreach ($productos as $prod) {
            Producto::firstOrCreate(
                ['slug' => str($prod['nombre'])->slug()],
                $prod
            );
        }

        foreach ($servicios as $serv) {
            Servicio::firstOrCreate(
                ['slug' => str($serv['nombre'])->slug()],
                $serv
            );
        }
    }
}
