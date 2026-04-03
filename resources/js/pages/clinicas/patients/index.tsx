import { Head, router, usePage, useForm } from '@inertiajs/react';
import { Pencil, Plus, Trash2, Users } from 'lucide-react';
import { useEffect, useState } from 'react';

import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface Patient {
    id: number;
    name: string;
    email: string;
    phone?: string | null;
    address?: string | null;
    age?: number | null;
    gender?: string | null;
    clinica_id: number;
}

interface Clinica {
    id: number;
    name: string;
    slug: string;
}

interface Props {
    clinica: Clinica;
    patients: Patient[];
}

export default function PatientsIndex({ clinica, patients }: Props) {
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [editingPatient, setEditingPatient] = useState<Patient | null>(null);
    const [flashMessage, setFlashMessage] = useState<string | null>(null);

    const { flash } = usePage().props as { flash?: { success?: string } };

    useEffect(() => {
        if (flash?.success) {
            setFlashMessage(flash.success);
            const timer = setTimeout(() => setFlashMessage(null), 4000);
            return () => clearTimeout(timer);
        }
    }, [flash]);

    const {
        data,
        setData,
        post,
        patch,
        processing,
        errors,
        reset,
        clearErrors,
    } = useForm({
        name: '',
        email: '',
        phone: '',
        address: '',
        age: '',
        gender: '',
    });

    const closeModals = () => {
        setIsCreateModalOpen(false);
        setEditingPatient(null);
        reset();
        clearErrors();
    };

    const openCreateModal = () => {
        reset();
        clearErrors();
        setIsCreateModalOpen(true);
    };

    const openEditModal = (patient: Patient) => {
        setEditingPatient(patient);
        setData({
            name: patient.name ?? '',
            email: patient.email ?? '',
            phone: patient.phone ?? '',
            address: patient.address ?? '',
            age: patient.age ? String(patient.age) : '',
            gender: patient.gender ?? '',
        });
        clearErrors();
    };

    const handleCreate = (e: React.FormEvent) => {
        e.preventDefault();

        post(`/${clinica.slug}/patients`, {
            onSuccess: () => closeModals(),
        });
    };

    const handleUpdate = (e: React.FormEvent) => {
        e.preventDefault();

        if (!editingPatient) return;

        patch(`/${clinica.slug}/patients/${editingPatient.id}`, {
            onSuccess: () => closeModals(),
        });
    };

    const handleDelete = (id: number) => {
        if (confirm('Tem certeza que deseja excluir este paciente?')) {
            router.delete(`/${clinica.slug}/patients/${id}`);
        }
    };

    return (
        <>
            <Head title="Pacientes" />

            <div className="flex flex-col space-y-6 p-8">
                {flashMessage && (
                    <div className="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
                        {flashMessage}
                    </div>
                )}

                <div className="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Pacientes"
                        description={`Gerencie os pacientes da clínica ${clinica.name}`}
                    />

                    <Button onClick={openCreateModal}>
                        <Plus className="mr-2 h-4 w-4" />
                        Novo Paciente
                    </Button>
                </div>

                <div className="rounded-lg border bg-card shadow-sm">
                    {patients.length > 0 ? (
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nome</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Telefone</TableHead>
                                    <TableHead>Idade</TableHead>
                                    <TableHead>Gênero</TableHead>
                                    <TableHead className="w-[100px] text-right">
                                        Ações
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {patients.map((patient) => (
                                    <TableRow key={patient.id}>
                                        <TableCell className="font-medium">
                                            {patient.name}
                                        </TableCell>
                                        <TableCell className="text-muted-foreground">
                                            {patient.email}
                                        </TableCell>
                                        <TableCell className="text-muted-foreground">
                                            {patient.phone || '—'}
                                        </TableCell>
                                        <TableCell className="text-muted-foreground">
                                            {patient.age ?? '—'}
                                        </TableCell>
                                        <TableCell className="text-muted-foreground">
                                            {patient.gender || '—'}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-1">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="h-8 w-8"
                                                    onClick={() =>
                                                        openEditModal(patient)
                                                    }
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                    <span className="sr-only">
                                                        Editar
                                                    </span>
                                                </Button>

                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="h-8 w-8 text-destructive hover:bg-destructive/10 hover:text-destructive"
                                                    onClick={() =>
                                                        handleDelete(patient.id)
                                                    }
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                    <span className="sr-only">
                                                        Excluir
                                                    </span>
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <div className="flex flex-col items-center justify-center py-16 text-center">
                            <Users className="mb-4 h-12 w-12 text-muted-foreground/40" />
                            <h3 className="text-lg font-medium text-foreground">
                                Nenhum paciente cadastrado
                            </h3>
                            <p className="mt-1 max-w-sm text-sm text-muted-foreground">
                                Comece adicionando o primeiro paciente para esta
                                clínica.
                            </p>
                            <Button
                                variant="outline"
                                className="mt-4"
                                onClick={openCreateModal}
                            >
                                <Plus className="mr-2 h-4 w-4" />
                                Adicionar Paciente
                            </Button>
                        </div>
                    )}
                </div>
            </div>

            <Dialog
                open={isCreateModalOpen}
                onOpenChange={(open) => {
                    if (!open) closeModals();
                }}
            >
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Novo Paciente</DialogTitle>
                        <DialogDescription>
                            Adicione um novo paciente para esta clínica.
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleCreate} className="grid gap-4 py-2">
                        <div className="grid gap-2">
                            <Label htmlFor="name">Nome</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                                placeholder="Nome do paciente"
                                autoFocus
                                required
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(e) =>
                                    setData('email', e.target.value)
                                }
                                placeholder="email@exemplo.com"
                                required
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="phone">Telefone</Label>
                            <Input
                                id="phone"
                                value={data.phone}
                                onChange={(e) =>
                                    setData('phone', e.target.value)
                                }
                                placeholder="(11) 99999-9999"
                            />
                            <InputError message={errors.phone} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="address">Endereço</Label>
                            <Input
                                id="address"
                                value={data.address}
                                onChange={(e) =>
                                    setData('address', e.target.value)
                                }
                                placeholder="Rua Exemplo, 123"
                            />
                            <InputError message={errors.address} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="age">Idade</Label>
                            <Input
                                id="age"
                                type="number"
                                value={data.age}
                                onChange={(e) =>
                                    setData('age', e.target.value)
                                }
                                placeholder="30"
                            />
                            <InputError message={errors.age} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="gender">Gênero</Label>
                            <Input
                                id="gender"
                                value={data.gender}
                                onChange={(e) =>
                                    setData('gender', e.target.value)
                                }
                                placeholder="Masculino / Feminino / Outro"
                            />
                            <InputError message={errors.gender} />
                        </div>

                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={closeModals}
                            >
                                Cancelar
                            </Button>

                            <Button type="submit" disabled={processing}>
                                {processing ? 'Criando...' : 'Criar Paciente'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={!!editingPatient}
                onOpenChange={(open) => {
                    if (!open) closeModals();
                }}
            >
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Editar Paciente</DialogTitle>
                        <DialogDescription>
                            Atualize os dados do paciente.
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleUpdate} className="grid gap-4 py-2">
                        <div className="grid gap-2">
                            <Label htmlFor="edit-name">Nome</Label>
                            <Input
                                id="edit-name"
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                                required
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="edit-email">Email</Label>
                            <Input
                                id="edit-email"
                                type="email"
                                value={data.email}
                                onChange={(e) =>
                                    setData('email', e.target.value)
                                }
                                required
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="edit-phone">Telefone</Label>
                            <Input
                                id="edit-phone"
                                value={data.phone}
                                onChange={(e) =>
                                    setData('phone', e.target.value)
                                }
                            />
                            <InputError message={errors.phone} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="edit-address">Endereço</Label>
                            <Input
                                id="edit-address"
                                value={data.address}
                                onChange={(e) =>
                                    setData('address', e.target.value)
                                }
                            />
                            <InputError message={errors.address} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="edit-age">Idade</Label>
                            <Input
                                id="edit-age"
                                type="number"
                                value={data.age}
                                onChange={(e) =>
                                    setData('age', e.target.value)
                                }
                            />
                            <InputError message={errors.age} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="edit-gender">Gênero</Label>
                            <Input
                                id="edit-gender"
                                value={data.gender}
                                onChange={(e) =>
                                    setData('gender', e.target.value)
                                }
                            />
                            <InputError message={errors.gender} />
                        </div>

                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={closeModals}
                            >
                                Cancelar
                            </Button>

                            <Button type="submit" disabled={processing}>
                                {processing
                                    ? 'Salvando...'
                                    : 'Salvar Alterações'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}

PatientsIndex.layout = (props: Props) => {
    const clinica = props.clinica;

    if (!clinica) return {};

    return {
        breadcrumbs: [
            { title: 'Clínicas', href: '#' },
            { title: clinica.name, href: '#' },
            { title: 'Pacientes', href: `/${clinica.slug}/patients` },
        ],
    };
};