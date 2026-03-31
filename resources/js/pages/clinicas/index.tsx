import { Head, Link } from '@inertiajs/react';
import { Eye, Pencil, Plus } from 'lucide-react';
import CreateClinicaModal from '@/components/create-clinica-modal';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { edit, index } from '@/routes/clinicas';
import type { Clinica } from '@/types';

type Props = {
    clinicas: Clinica[];
};

export default function ClinicasIndex({ clinicas }: Props) {
    return (
        <>
            <Head title="Clinicas" />

            <h1 className="sr-only">Clinicas</h1>

            <div className="flex flex-col space-y-6">
                <div className="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Clinicas"
                        description="Manage your clinicas and clinica memberships"
                    />

                    <CreateClinicaModal>
                        <Button data-test="clinicas-new-clinica-button">
                            <Plus /> New clinica
                        </Button>
                    </CreateClinicaModal>
                </div>

                <div className="space-y-3">
                    {clinicas.map((clinica) => (
                        <div
                            key={clinica.id}
                            data-test="clinica-row"
                            className="flex items-center justify-between rounded-lg border p-4"
                        >
                            <div className="flex items-center gap-4">
                                <div>
                                    <div className="flex items-center gap-2">
                                        <span className="font-medium">
                                            {clinica.name}
                                        </span>
                                        {clinica.isPersonal ? (
                                            <Badge variant="secondary">
                                                Personal
                                            </Badge>
                                        ) : null}
                                    </div>
                                    <span className="text-sm text-muted-foreground">
                                        {clinica.roleLabel}
                                    </span>
                                </div>
                            </div>

                            <TooltipProvider>
                                <div className="flex items-center gap-2">
                                    {clinica.role === 'member' ? (
                                        <Tooltip>
                                            <TooltipTrigger asChild>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    data-test="clinica-view-button"
                                                    asChild
                                                >
                                                    <Link
                                                        href={edit(clinica.slug)}
                                                    >
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>View clinica</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    ) : (
                                        <Tooltip>
                                            <TooltipTrigger asChild>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    data-test="clinica-edit-button"
                                                    asChild
                                                >
                                                    <Link
                                                        href={edit(clinica.slug)}
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>Edit clinica</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    )}
                                </div>
                            </TooltipProvider>
                        </div>
                    ))}

                    {clinicas.length === 0 ? (
                        <p className="py-8 text-center text-muted-foreground">
                            You don't belong to any clinicas yet.
                        </p>
                    ) : null}
                </div>
            </div>
        </>
    );
}

ClinicasIndex.layout = {
    breadcrumbs: [
        {
            title: 'Clinicas',
            href: index(),
        },
    ],
};
