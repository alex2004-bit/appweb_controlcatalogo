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
    UserCircle,
    FileText,
    ShoppingCart,
    CreditCard,
    BarChart3,
    Settings,
    Building2,
    Share2,
    SlidersHorizontal,
    ChevronDown,
    AppWindow,
    Key,
    Globe,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavUser from '@/components/NavUser.vue';
import TeamSwitcher from '@/components/TeamSwitcher.vue';
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
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes';
import { index as categoriasIndex } from '@/routes/admin/catalogo/categorias';
import { index as galeriaIndex } from '@/routes/admin/catalogo/galeria';
import { index as productosIndex } from '@/routes/admin/catalogo/productos';
import { index as serviciosIndex } from '@/routes/admin/catalogo/servicios';
import { index as clientesIndex } from '@/routes/admin/clientes';
import { index as consultasIndex } from '@/routes/admin/consultas';
import { index as cotizacionesIndex } from '@/routes/admin/cotizaciones';
import { index as pedidosIndex } from '@/routes/admin/pedidos';
import { index as empresaIndex } from '@/routes/admin/configuracion/empresa';
import { index as generalesIndex } from '@/routes/admin/configuracion/generales';
import { index as socialIndex } from '@/routes/admin/configuracion/social';
import { index as pagosIndex } from '@/routes/admin/finanzas/pagos';
import { index as reportesIndex } from '@/routes/admin/finanzas/reportes';
import { index as permisosIndex } from '@/routes/admin/seguridad/permisos';
import { index as rolesIndex } from '@/routes/admin/seguridad/roles';
import { index as usersIndex } from '@/routes/admin/seguridad/users';
import type { NavItem } from '@/types';

const page = usePage();
const { isCurrentUrl } = useCurrentUrl();

const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const dashboardUrl = computed(() =>
    teamSlug.value ? dashboard(teamSlug.value).url : '/',
);

const navGroups = computed(() => [
    {
        title: 'Administración',
        icon: LayoutGrid,
        gradient: 'from-cyan-500 to-blue-600',
        items: [
            {
                title: 'Dashboard',
                href: dashboardUrl.value,
                icon: LayoutGrid,
                description: 'Panel de control',
            },
        ],
    },
    {
        title: 'Seguridad',
        icon: Shield,
        gradient: 'from-purple-500 to-pink-600',
        items: [
            {
                title: 'Usuarios',
                href: teamSlug.value ? usersIndex(teamSlug.value).url : '#',
                icon: Users,
                description: 'Gestión de usuarios',
            },
            {
                title: 'Roles',
                href: teamSlug.value ? rolesIndex(teamSlug.value).url : '#',
                icon: ShieldCheck,
                description: 'Roles del sistema',
            },
            {
                title: 'Permisos',
                href: teamSlug.value ? permisosIndex(teamSlug.value).url : '#',
                icon: Key,
                description: 'Permisos y accesos',
            },
        ],
    },
    {
        title: 'Catálogo',
        icon: FolderTree,
        gradient: 'from-blue-500 to-indigo-600',
        items: [
            {
                title: 'Categorías',
                href: teamSlug.value ? categoriasIndex(teamSlug.value).url : '#',
                icon: FolderTree,
                description: 'Organizar por categorías',
            },
            {
                title: 'Productos',
                href: teamSlug.value ? productosIndex(teamSlug.value).url : '#',
                icon: Package,
                description: 'Productos del catálogo',
            },
            {
                title: 'Servicios',
                href: teamSlug.value ? serviciosIndex(teamSlug.value).url : '#',
                icon: Wrench,
                description: 'Servicios disponibles',
            },
            {
                title: 'Galería',
                href: teamSlug.value ? galeriaIndex(teamSlug.value).url : '#',
                icon: Images,
                description: 'Multimedia',
            },
        ],
    },
    {
        title: 'Clientes',
        icon: UserCircle,
        gradient: 'from-emerald-500 to-teal-600',
        items: [
            {
                title: 'Clientes',
                href: teamSlug.value ? clientesIndex(teamSlug.value).url : '#',
                icon: UserCircle,
                description: 'Base de clientes',
            },
            {
                title: 'Consultas',
                href: teamSlug.value ? consultasIndex(teamSlug.value).url : '#',
                icon: MessageSquare,
                description: 'Mensajes recibidos',
            },
            {
                title: 'Cotizaciones',
                href: teamSlug.value ? cotizacionesIndex(teamSlug.value).url : '#',
                icon: FileText,
                description: 'Presupuestos',
            },
        ],
    },
    {
        title: 'Pedidos',
        icon: ShoppingCart,
        gradient: 'from-cyan-500 to-teal-600',
        items: [
            {
                title: 'Pedidos',
                href: teamSlug.value ? pedidosIndex(teamSlug.value).url : '#',
                icon: ShoppingCart,
                description: 'Órdenes de clientes',
            },
        ],
    },
    {
        title: 'Finanzas',
        icon: CreditCard,
        gradient: 'from-rose-500 to-red-600',
        items: [
            {
                title: 'Pagos',
                href: teamSlug.value ? pagosIndex(teamSlug.value).url : '#',
                icon: CreditCard,
                description: 'Registro de pagos',
            },
            {
                title: 'Reportes',
                href: teamSlug.value ? reportesIndex(teamSlug.value).url : '#',
                icon: BarChart3,
                description: 'Estadísticas',
            },
        ],
    },
    {
        title: 'Configuración',
        icon: Settings,
        gradient: 'from-amber-500 to-orange-600',
        items: [
            {
                title: 'Empresa',
                href: teamSlug.value ? empresaIndex(teamSlug.value).url : '#',
                icon: Building2,
                description: 'Datos de la empresa',
            },
            {
                title: 'Redes Sociales',
                href: teamSlug.value ? socialIndex(teamSlug.value).url : '#',
                icon: Share2,
                description: 'Enlaces sociales',
            },
            {
                title: 'Datos Generales',
                href: teamSlug.value ? generalesIndex(teamSlug.value).url : '#',
                icon: SlidersHorizontal,
                description: 'Config. del sistema',
            },
        ],
    },
]);

const openGroups = ref<string[]>(['Administración']);

const footerNavItems: NavItem[] = [
    {
        title: 'Sistema de Catálogo',
        href: '#',
        icon: AppWindow,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <SidebarMenu>
                <SidebarMenuItem>
                    <TeamSwitcher />
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup
                v-for="group in navGroups"
                :key="group.title"
                class="px-2 py-0"
            >
                <Collapsible
                    :default-open="openGroups.includes(group.title)"
                    class="group/collapsible"
                >
                    <SidebarGroupLabel
                        as-child
                        class="group/label text-xs font-semibold tracking-wider text-sidebar-foreground/50 hover:text-sidebar-foreground hover:bg-sidebar-accent w-full"
                    >
                        <CollapsibleTrigger>
                            <span class="flex h-5 w-5 items-center justify-center rounded-md" :class="'bg-gradient-to-br ' + group.gradient">
                                <component :is="group.icon" class="h-3 w-3 text-white" />
                            </span>
                            <span class="ml-2">{{ group.title }}</span>
                            <ChevronDown class="ml-auto h-3.5 w-3.5 shrink-0 transition-transform duration-300 group-data-[state=open]/collapsible:rotate-180 text-sidebar-foreground/40" />
                        </CollapsibleTrigger>
                    </SidebarGroupLabel>
                    <CollapsibleContent>
                        <SidebarMenu>
                            <SidebarMenuItem
                                v-for="item in group.items"
                                :key="item.title"
                            >
                                <SidebarMenuButton
                                    as-child
                                    :is-active="isCurrentUrl(item.href)"
                                    :tooltip="item.title"
                                >
                                    <Link :href="item.href">
                                        <component :is="item.icon" />
                                        <div class="flex flex-col">
                                            <span>{{ item.title }}</span>
                                            <span v-if="item.description" class="text-[10px] text-sidebar-foreground/40 leading-tight">{{ item.description }}</span>
                                        </div>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </CollapsibleContent>
                </Collapsible>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <SidebarGroup>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <SidebarMenuItem>
                            <SidebarMenuButton
                                as-child
                                tooltip="Ver sitio público"
                                class="text-zinc-500 hover:text-cyan-400"
                            >
                                <Link href="/">
                                    <Globe class="h-4 w-4" />
                                    <span>Ver sitio público</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
