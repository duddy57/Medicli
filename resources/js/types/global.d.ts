import type { Auth } from '@/types/auth';
import type { Clinica } from '@/types/clinicas';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            currentClinica: Clinica | null;
            clinicas: Clinica[];
            [key: string]: unknown;
        };
    }
}
