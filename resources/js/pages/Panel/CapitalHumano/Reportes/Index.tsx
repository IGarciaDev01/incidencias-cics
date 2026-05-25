import ReportesDashboard from '@/components/reportes-dashboard';
import { dashboard } from '@/routes/panel';
import { index, exportar } from '@/routes/panel/capital_humano/reportes';

type Props = React.ComponentProps<typeof ReportesDashboard>;

export default function Index(props: Omit<Props, 'indexUrl' | 'exportUrl'>) {
    return (
        <ReportesDashboard
            {...props}
            indexUrl={index.url()}
            exportUrl={(query) => exportar.url({ query })}
        />
    );
}

Index.layout = {
    breadcrumbs: [
        { title: 'Panel Principal', href: dashboard.url() },
        { title: 'Reportes', href: index.url() },
    ],
};
