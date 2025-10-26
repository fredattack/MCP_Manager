import type { SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { AlertCircle, CheckCircle, Info } from 'lucide-react';
import { useEffect } from 'react';
import { toast } from 'sonner';

export function FlashMessages() {
    const { flash } = usePage().props as SharedData;

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success, {
                icon: <CheckCircle className="h-4 w-4" />,
            });
        }

        if (flash?.error) {
            toast.error(flash.error, {
                icon: <AlertCircle className="h-4 w-4" />,
            });
        }

        if (flash?.warning) {
            toast.warning(flash.warning, {
                icon: <AlertCircle className="h-4 w-4" />,
            });
        }

        if (flash?.info) {
            toast.info(flash.info, {
                icon: <Info className="h-4 w-4" />,
            });
        }
    }, [flash]);

    return null;
}
