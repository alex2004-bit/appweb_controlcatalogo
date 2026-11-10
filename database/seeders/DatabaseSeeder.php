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
            CatalogBrandsSeeder::class,
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
            'videovigilancia' => [
                'nombre' => 'Videovigilancia',
                'descripcion' => 'Cámaras y grabadores para sistemas de videovigilancia.',
                'parent_id' => null,
                'activo' => true,
            ],
            'accesorios' => [
                'nombre' => 'Accesorios',
                'descripcion' => 'Accesorios para sistemas de seguridad.',
                'parent_id' => null,
                'activo' => true,
            ],
            'alarmas' => [
                'nombre' => 'Alarmas',
                'descripcion' => 'Sistemas y dispositivos de alarma.',
                'parent_id' => null,
                'activo' => true,
            ],
            'control-de-acceso' => [
                'nombre' => 'Control de Acceso',
                'descripcion' => 'Equipos de control de acceso.',
                'parent_id' => null,
                'activo' => true,
            ],
        ];

        foreach ($categorias as $slug => $data) {
            Categoria::updateOrCreate(['slug' => $slug], $data);
        }

        $categoryIds = Categoria::query()
            ->whereIn('slug', array_keys($categorias))
            ->pluck('id', 'slug')
            ->all();

        $subcategorias = [
            'camaras-ip' => ['nombre' => 'Cámaras IP', 'parent_id' => 'videovigilancia', 'descripcion' => 'Cámaras de red IP'],
            'camaras-analogicas' => ['nombre' => 'Cámaras Analógicas', 'parent_id' => 'videovigilancia', 'descripcion' => 'Cámaras analógicas HD'],
            'camaras-termicas' => ['nombre' => 'Cámaras Térmicas', 'parent_id' => 'videovigilancia', 'descripcion' => 'Cámaras con visión térmica'],
            'otros-grabadores' => ['nombre' => 'Otros grabadores', 'parent_id' => 'videovigilancia', 'descripcion' => 'Grabadores sin una subcategoría más específica'],
            'nvr-ip' => ['nombre' => 'NVR IP', 'parent_id' => 'videovigilancia', 'descripcion' => 'Grabadores de red'],
            'dvr-hd' => ['nombre' => 'DVR HD', 'parent_id' => 'videovigilancia', 'descripcion' => 'Grabadores digitales HD'],
            'sensores' => ['nombre' => 'Sensores', 'parent_id' => 'alarmas', 'descripcion' => 'Sensores de movimiento y apertura'],
            'cerraduras-electricas' => ['nombre' => 'Cerraduras Eléctricas', 'parent_id' => 'control-de-acceso', 'descripcion' => 'Cerraduras y chapas eléctricas'],
        ];

        foreach ($subcategorias as $slug => $data) {
            $parentSlug = $data['parent_id'];
            $data['parent_id'] = $categoryIds[$parentSlug] ?? null;
            $data['activo'] = true;

            Categoria::updateOrCreate(['slug' => $slug], $data);
        }

        $categoryIds = Categoria::query()
            ->whereIn('slug', [...array_keys($categorias), ...array_keys($subcategorias)])
            ->pluck('id', 'slug')
            ->all();

        $productos = [
            ['categoria_slug' => 'camaras-ip', 'nombre' => 'Cámara IP 2MP', 'descripcion' => 'Cámara IP 2MP Full HD con visión nocturna', 'sku' => 'CAM-IP-001', 'precio' => 149.99, 'especificaciones' => [['nombre' => 'Resolución', 'valor' => '2MP (1920x1080)'], ['nombre' => 'Visión Nocturna', 'valor' => '30m'], ['nombre' => 'IR', 'valor' => 'Si']], 'activo' => true, 'imagen' => 'productos/prod_01_0_1.png'],
            ['categoria_slug' => 'camaras-ip', 'nombre' => 'Cámara IP 5MP', 'descripcion' => 'Cámara IP 5MP con audio bidireccional', 'sku' => 'CAM-IP-002', 'precio' => 249.99, 'especificaciones' => [['nombre' => 'Resolución', 'valor' => '5MP (2592x1944)'], ['nombre' => 'Audio', 'valor' => 'Bidireccional'], ['nombre' => 'WDR', 'valor' => 'DWDR']], 'activo' => true, 'imagen' => 'productos/prod_01_1_1.png'],
            ['categoria_slug' => 'camaras-ip', 'nombre' => 'Cámara IP PTZ 4MP', 'descripcion' => 'Cámara PTZ 4MP con zoom óptico 25x', 'sku' => 'CAM-PTZ-001', 'precio' => 599.99, 'especificaciones' => [['nombre' => 'Resolución', 'valor' => '4MP'], ['nombre' => 'Zoom', 'valor' => '25x óptico'], ['nombre' => 'IP', 'valor' => 'IP66']], 'activo' => true, 'imagen' => 'productos/prod_01_0_2.png'],
            ['categoria_slug' => 'camaras-analogicas', 'nombre' => 'Cámara HD-TVI 2MP', 'descripcion' => 'Cámara analógica HD-TVI 2MP', 'sku' => 'CAM-TVI-001', 'precio' => 89.99, 'especificaciones' => [['nombre' => 'Tecnología', 'valor' => 'HD-TVI'], ['nombre' => 'Resolución', 'valor' => '2MP'], ['nombre' => 'Alcance IR', 'valor' => '20m']], 'activo' => true, 'imagen' => 'productos/prod_01_0_3.png'],
            ['categoria_slug' => 'nvr-ip', 'nombre' => 'NVR 8 Canales', 'descripcion' => 'Grabador de red NVR 8 canales con PoE', 'sku' => 'NVR-8CH-001', 'precio' => 399.99, 'especificaciones' => [['nombre' => 'Canales', 'valor' => '8'], ['nombre' => 'PoE', 'valor' => 'Si, 8 puertos'], ['nombre' => 'Almacenamiento', 'valor' => 'Hasta 10TB']], 'activo' => true, 'imagen' => 'productos/prod_01_0_4.png'],
            ['categoria_slug' => 'dvr-hd', 'nombre' => 'DVR 16 Canales', 'descripcion' => 'Grabador digital DVR 16 canales 1080p', 'sku' => 'DVR-16CH-001', 'precio' => 299.99, 'especificaciones' => [['nombre' => 'Canales', 'valor' => '16'], ['nombre' => 'Resolución', 'valor' => '1080p'], ['nombre' => 'Almacenamiento', 'valor' => 'Hasta 8TB']], 'activo' => true, 'imagen' => 'productos/prod_01_1_2.png'],
            ['categoria_slug' => 'sensores', 'nombre' => 'Sensor de Movimiento', 'descripcion' => 'Sensor de movimiento infrarrojo para interiores', 'sku' => 'SENS-MOV-001', 'precio' => 19.99, 'especificaciones' => [['nombre' => 'Tipo', 'valor' => 'Infrarrojo pasivo'], ['nombre' => 'Alcance', 'valor' => '12m'], ['nombre' => 'Ángulo', 'valor' => '90°']], 'activo' => true, 'imagen' => 'productos/prod_01_1_3.png'],
            ['categoria_slug' => 'cerraduras-electricas', 'nombre' => 'Cerradura Eléctrica', 'descripcion' => 'Cerradura eléctrica para puerta de vidrio', 'sku' => 'CERR-ELEC-001', 'precio' => 79.99, 'especificaciones' => [['nombre' => 'Tipo', 'valor' => 'Electromagnética'], ['nombre' => 'Fuerza', 'valor' => '600lbs'], ['nombre' => 'Voltaje', 'valor' => '12VDC']], 'activo' => true, 'imagen' => 'galeria/ZBmiDlV9OJaZeZhZjGSB75VnSabblWWIIYT7L4gk.png'],
        ];

        $servicios = [
            ['categoria_slug' => 'videovigilancia', 'nombre' => 'Instalación de Cámaras', 'descripcion' => 'Instalación profesional de sistemas de videovigilancia', 'precio' => 250.00, 'duracion_estimada' => '4 horas', 'caracteristicas' => ['Instalación de hasta 4 cámaras', 'Cableado estructurado', 'Configuración de DVR/NVR', 'Pruebas de funcionamiento'], 'activo' => true, 'imagen' => 'https://images.unsplash.com/photo-1557324232-b8917d3c3dcb?q=80&w=800&auto=format&fit=crop'],
            ['categoria_slug' => 'alarmas', 'nombre' => 'Instalación de Alarma', 'descripcion' => 'Instalación de sistema de alarma para hogar o negocio', 'precio' => 180.00, 'duracion_estimada' => '3 horas', 'caracteristicas' => ['Panel de control', 'Sensores de puerta/ventana', 'Sensor de movimiento', 'Sirena interior'], 'activo' => true, 'imagen' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?q=80&w=800&auto=format&fit=crop'],
            ['categoria_slug' => 'control-de-acceso', 'nombre' => 'Configuración Control de Acceso', 'descripcion' => 'Configuración e instalación de sistemas de control de acceso', 'precio' => 200.00, 'duracion_estimada' => '3 horas', 'caracteristicas' => ['Lector biométrico o de tarjeta', 'Cerradura eléctrica', 'Fuente de poder', 'Software de gestión'], 'activo' => true, 'imagen' => 'https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?q=80&w=800&auto=format&fit=crop'],
            ['categoria_slug' => 'videovigilancia', 'nombre' => 'Mantenimiento de Equipos', 'descripcion' => 'Mantenimiento preventivo y correctivo de equipos de seguridad', 'precio' => 120.00, 'duracion_estimada' => '2 horas', 'caracteristicas' => ['Limpieza de equipos', 'Revisión de conexiones', 'Actualización de firmware', 'Diagnóstico general'], 'activo' => true, 'imagen' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?q=80&w=800&auto=format&fit=crop'],
        ];

        foreach ($productos as $prod) {
            $prod['categoria_id'] = $categoryIds[$prod['categoria_slug']];
            unset($prod['categoria_slug']);

            Producto::firstOrCreate(
                ['slug' => str($prod['nombre'])->slug()],
                $prod
            );
        }

        foreach ($servicios as $serv) {
            $serv['categoria_id'] = $categoryIds[$serv['categoria_slug']];
            unset($serv['categoria_slug']);

            Servicio::firstOrCreate(
                ['slug' => str($serv['nombre'])->slug()],
                $serv
            );
        }
    }
}
