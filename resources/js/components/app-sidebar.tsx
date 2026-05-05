import { Link, usePage } from '@inertiajs/react';
import {
    FileTextIcon,
    BarChart2,
    FolderClock,
    LayoutGrid,
    Shield,
    Users,
    UserSearch,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes/panel';
import { index as chEmpleados }       from '@/routes/panel/capital_humano/empleados';
import { index as chIncidencias }     from '@/routes/panel/capital_humano/incidencias';
import { index as jefeEmpleados }     from '@/routes/panel/jefe_inmediato/empleados';
import { index as jefeIncidencias }   from '@/routes/panel/jefe_inmediato/incidencias';
import { index as adminAreas }        from '@/routes/panel/subdireccion/admin/areas';
import { index as adminLogs }         from '@/routes/panel/subdireccion/admin/logs';
import { index as adminUsuarios }     from '@/routes/panel/subdireccion/admin/usuarios';
import { index as subdirEmpleados }   from '@/routes/panel/subdireccion/empleados';
import { index as subdirIncidencias } from '@/routes/panel/subdireccion/incidencias';
import { index as subdirReportes }    from '@/routes/panel/subdireccion/reportes';
import type { NavItem } from '@/types';

const subdirNavItems: NavItem[] = [
    { title: 'Panel Principal',   href: dashboard.url(),         icon: LayoutGrid },
    { title: 'Incidencias', href: subdirIncidencias.url(), icon: FileTextIcon },
    { title: 'Reportes',    href: subdirReportes.url(),    icon: BarChart2 },
    { title: 'Empleados',   href: subdirEmpleados.url(),   icon: UserSearch },
    { title: 'Usuarios',    href: adminUsuarios.url(),     icon: Users },
    { title: 'Áreas',       href: adminAreas.url(),       icon: Shield },
    { title: 'Logs',        href: adminLogs.url(),         icon: FolderClock },
];

const jefeNavItems: NavItem[] = [
    { title: 'Dashboard',   href: dashboard.url(),       icon: LayoutGrid },
    { title: 'Incidencias', href: jefeIncidencias.url(), icon: FileTextIcon },
    { title: 'Empleados',   href: jefeEmpleados.url(),   icon: UserSearch },
];

const capitalHumanoNavItems: NavItem[] = [
    { title: 'Dashboard',   href: dashboard.url(),     icon: LayoutGrid },
    { title: 'Incidencias', href: chIncidencias.url(), icon: FileTextIcon },
    { title: 'Empleados',   href: chEmpleados.url(),   icon: UserSearch },
];

const NAV_BY_ROL: Record<string, NavItem[]> = {
    jefe_inmediato: jefeNavItems,
    capital_humano: capitalHumanoNavItems,
    subdirector:    subdirNavItems,
};

export function AppSidebar() {
    const { auth } = usePage().props as { auth: { user?: { rol?: string } } };
    const rol = auth.user?.rol ?? '';
    const navItems = NAV_BY_ROL[rol] ?? [];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard.url()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={navItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
