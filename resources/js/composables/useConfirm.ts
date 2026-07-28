import Swal from 'sweetalert2';

const swal = Swal.mixin({
    customClass: {
        container: '!font-sans',
        popup: '!bg-card !rounded-2xl !border !border-border/50 !shadow-2xl !backdrop-blur-xl',
        title: '!text-foreground !text-lg !font-semibold !px-4 !pt-4',
        htmlContainer: '!text-muted-foreground !text-sm !px-4',
        confirmButton: '!bg-gradient-to-r !from-cyan-500 !to-blue-600 !text-white !rounded-xl !px-5 !py-2.5 !text-sm !font-medium !shadow-lg !shadow-cyan-500/25 !border-0 !outline-none !transition-all !duration-200 hover:!shadow-xl active:!scale-95',
        cancelButton: '!bg-card !text-foreground !rounded-xl !px-5 !py-2.5 !text-sm !font-medium !border !border-border/50 !outline-none !transition-all !duration-200 hover:!bg-accent/50',
        denyButton: '!bg-gradient-to-r !from-red-500 !to-rose-600 !text-white !rounded-xl !px-5 !py-2.5 !text-sm !font-medium !shadow-lg !shadow-red-500/25 !border-0 !outline-none',
        actions: '!gap-2 !px-4 !pb-4',
        footer: '!border-t !border-border/40 !text-muted-foreground !text-xs',
    },
    buttonsStyling: false,
    reverseButtons: true,
});

export function useConfirm() {
    async function confirm(config: {
        title?: string;
        text?: string;
        icon?: 'warning' | 'question' | 'error' | 'info' | 'success';
        confirmText?: string;
        cancelText?: string;
        reverse?: boolean;
        danger?: boolean;
    } = {}) {
        const result = await swal.fire({
            title: config.title ?? '¿Estás seguro?',
            text: config.text ?? 'Esta acción no se puede deshacer.',
            icon: config.icon ?? 'warning',
            showCancelButton: true,
            confirmButtonText: config.confirmText ?? 'Confirmar',
            cancelButtonText: config.cancelText ?? 'Cancelar',
            reverseButtons: config.reverse ?? true,
            iconColor: config.danger ? '#ef4444' : '#06b6d4',
            confirmButtonColor: config.danger ? '#ef4444' : undefined,
            background: 'var(--card)',
            color: 'var(--foreground)',
        });
        return result.isConfirmed;
    }

    async function success(title: string, text?: string) {
        await swal.fire({
            title,
            text,
            icon: 'success',
            iconColor: '#10b981',
            showConfirmButton: true,
            confirmButtonText: 'Continuar',
            background: 'var(--card)',
            color: 'var(--foreground)',
            timer: 3000,
            timerProgressBar: true,
        });
    }

    async function error(title: string, text?: string) {
        await swal.fire({
            title,
            text,
            icon: 'error',
            iconColor: '#ef4444',
            showConfirmButton: true,
            confirmButtonText: 'Entendido',
            background: 'var(--card)',
            color: 'var(--foreground)',
        });
    }

    return { confirm, success, error, swal };
}
