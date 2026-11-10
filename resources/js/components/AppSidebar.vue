<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Users,
    Shield,
    ShieldCheck,
    FolderTree,
    Package,
    Wrench,
    Images,
    MessageSquare,
    FileText,
    ShoppingCart,
    CreditCard,
    BarChart3,
    Building2,
    Share2,
    SlidersHorizontal,
    ChevronDown,
    Globe,
    Tags,
    Ticket,
    Truck,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarSeparator,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes';
import { index as comprasIndex } from '@/routes/admin/abastecimiento/compras';
import { index as proveedoresIndex } from '@/routes/admin/abastecimiento/proveedores';
import { index as categoriasIndex } from '@/routes/admin/catalogo/categorias';
import { index as galeriaIndex } from '@/routes/admin/catalogo/galeria';
import { index as marcasIndex } from '@/routes/admin/catalogo/marcas';
import { index as productosIndex } from '@/routes/admin/catalogo/productos';
import { index as serviciosIndex } from '@/routes/admin/catalogo/servicios';
import { index as clientesIndex } from '@/routes/admin/clientes';
import { index as empresaIndex } from '@/routes/admin/configuracion/empresa';
import { index as generalesIndex } from '@/routes/admin/configuracion/generales';
import { index as socialIndex } from '@/routes/admin/configuracion/social';
import { index as consultasIndex } from '@/routes/admin/consultas';
import { index as cotizacionesIndex } from '@/routes/admin/cotizaciones';
import { index as pagosIndex } from '@/routes/admin/finanzas/pagos';
import { index as reportesIndex } from '@/routes/admin/finanzas/reportes';
import { index as pedidosIndex } from '@/routes/admin/pedidos';
import { index as permisosIndex } from '@/routes/admin/seguridad/permisos';
import { index as rolesIndex } from '@/routes/admin/seguridad/roles';
import { index as usersIndex } from '@/routes/admin/seguridad/users';

const page = usePage();
const { isCurrentUrl } = useCurrentUrl();

const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const dashboardUrl = computed(() =>
    teamSlug.value ? dashboard(teamSlug.value).url : '/',
);

type MenuItem = {
    title: string;
    href: string;
    icon: any;
    badge?: string;
};

type MenuSection = {
    title: string;
    items: MenuItem[];
};

const menuSections = computed<MenuSection[]>(() => [
    {
        title: 'Administración',
        items: [
            { title: 'Dashboard', href: dashboardUrl.value, icon: LayoutGrid },
        ],
    },
    {
        title: 'Catálogo',
        items: [
            { title: 'Productos', href: teamSlug.value ? productosIndex(teamSlug.value).url : '#', icon: Package },
            { title: 'Categorías', href: teamSlug.value ? categoriasIndex(teamSlug.value).url : '#', icon: FolderTree },
            { title: 'Marcas', href: teamSlug.value ? marcasIndex(teamSlug.value).url : '#', icon: Tags },
            { title: 'Servicios', href: teamSlug.value ? serviciosIndex(teamSlug.value).url : '#', icon: Wrench },
            { title: 'Galería', href: teamSlug.value ? galeriaIndex(teamSlug.value).url : '#', icon: Images },
        ],
    },
    {
        title: 'Clientes',
        items: [
            { title: 'Clientes', href: teamSlug.value ? clientesIndex(teamSlug.value).url : '#', icon: Users },
            { title: 'Consultas', href: teamSlug.value ? consultasIndex(teamSlug.value).url : '#', icon: MessageSquare },
            { title: 'Cotizaciones', href: teamSlug.value ? cotizacionesIndex(teamSlug.value).url : '#', icon: FileText },
        ],
    },
    {
        title: 'Ventas',
        items: [
            { title: 'Pedidos', href: teamSlug.value ? pedidosIndex(teamSlug.value).url : '#', icon: ShoppingCart },
            { title: 'Pagos', href: teamSlug.value ? pagosIndex(teamSlug.value).url : '#', icon: CreditCard },
            { title: 'Cupones', href: '#', icon: Ticket },
        ],
    },
    {
        title: 'Abastecimiento',
        items: [
            { title: 'Proveedores', href: teamSlug.value ? proveedoresIndex(teamSlug.value).url : '#', icon: Building2 },
            { title: 'Compras', href: teamSlug.value ? comprasIndex(teamSlug.value).url : '#', icon: Truck },
        ],
    },
    {
        title: 'Reportes',
        items: [
            { title: 'Reportes', href: teamSlug.value ? reportesIndex(teamSlug.value).url : '#', icon: BarChart3 },
        ],
    },
    {
        title: 'Seguridad',
        items: [
            { title: 'Usuarios', href: teamSlug.value ? usersIndex(teamSlug.value).url : '#', icon: Users },
            { title: 'Roles', href: teamSlug.value ? rolesIndex(teamSlug.value).url : '#', icon: ShieldCheck },
            { title: 'Permisos', href: teamSlug.value ? permisosIndex(teamSlug.value).url : '#', icon: Shield },
        ],
    },
    {
        title: 'Configuración',
        items: [
            { title: 'Empresa', href: teamSlug.value ? empresaIndex(teamSlug.value).url : '#', icon: Building2 },
            { title: 'Redes Sociales', href: teamSlug.value ? socialIndex(teamSlug.value).url : '#', icon: Share2 },
            { title: 'Datos Generales', href: teamSlug.value ? generalesIndex(teamSlug.value).url : '#', icon: SlidersHorizontal },
        ],
    },
]);

const openSections = ref<string[]>(['Administración', 'Catálogo']);

function toggleSection(title: string) {
    const idx = openSections.value.indexOf(title);

    if (idx >= 0) {
        openSections.value.splice(idx, 1);
    } else {
        openSections.value.push(title);
    }
}
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader class="border-b border-sidebar-border/50 px-4 py-4">
            <div class="flex items-center gap-3 group-data-[collapsible=icon]:justify-center">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 shadow-md shadow-blue-500/30">
                    <AppLogoIcon class="size-5 fill-current text-white" />
                </div>
                <div class="grid group-data-[collapsible=icon]:hidden">
                    <span class="text-sm font-bold text-white leading-tight">DOMOTECH</span>
                    <span class="text-xs text-blue-200/90 leading-tight">Panel Administrativo</span>
                </div>
            </div>
        </SidebarHeader>

        <SidebarContent class="px-3 py-3">
            <div v-for="section in menuSections" :key="section.title" class="mb-1">
                <Collapsible
                    :default-open="openSections.includes(section.title)"
                    class="group/collapsible"
                >
                    <CollapsibleTrigger
                        class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-xs font-semibold uppercase tracking-wider text-blue-200/90 hover:text-white transition-colors"
                        @click="toggleSection(section.title)"
                    >
                        <ChevronDown class="h-3 w-3 transition-transform duration-200 group-data-[state=open]/collapsible:rotate-0 -rotate-90" />
                        {{ section.title }}
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <SidebarMenu class="gap-0.5">
                            <SidebarMenuItem v-for="item in section.items" :key="item.title">
                                <SidebarMenuButton
                                    as-child
                                    :is-active="isCurrentUrl(item.href)"
                                    :tooltip="item.title"
                                >
                                    <Link :href="item.href">
                                        <component :is="item.icon" class="size-4" />
                                        <span>{{ item.title }}</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </CollapsibleContent>
                </Collapsible>
            </div>

            <SidebarSeparator class="my-3 bg-sidebar-border/40" />

            <div class="px-2">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-blue-200/70 group-data-[collapsible=icon]:hidden">Catálogo Público</p>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton as-child tooltip="Ver tienda">
                            <Link href="/">
                                <Globe class="size-4" />
                                <span>Ver tienda</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </div>
        </SidebarContent>

        <SidebarFooter class="border-t border-sidebar-border/40 p-4">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
