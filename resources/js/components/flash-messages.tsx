import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { AlertCircle, CheckCircle, Info, X } from 'lucide-react';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { toast } from 'sonner';
import type { SharedData } from '@/types';

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