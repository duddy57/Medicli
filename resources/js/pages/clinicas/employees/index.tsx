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
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface Employee {
    id: number;
    user_id: number;
    role: string;
    clinica_id: number;
    user: {
        id: number;
        name: string;
    };
}

interface UserOption {
    id: number;
    name: string;
}

interface Clinica {
    id: number;
    name: string;
    slug: string;
}

interface Props {
    clinica: Clinica;
    employees: Employee[];
    users: UserOption[];
}

const roleLabels: Record<string, string> = {
    owner: 'Owner',
    admin: 'Admin',
    doctor: 'Doctor',
    member: 'Member',
};

export default function EmployeesIndex({ clinica, employees, users }: Props) {
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [editingEmployee, setEditingEmployee] = useState<Employee | null>(
        null,
    );
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
        user_id: '',
        role: '',
    });

    const closeModals = () => {
        setIsCreateModalOpen(false);
        setEditingEmployee(null);
        reset();
        clearErrors();
    };

    const openCreateModal = () => {
        reset();
        clearErrors();
        setIsCreateModalOpen(true);
    };

    const openEditModal = (employee: Employee) => {
        setEditingEmployee(employee);
        setData({
            user_id: employee.user_id.toString(),
            role: employee.role,
        });
        clearErrors();
    };

    const handleCreate = (e: React.FormEvent) => {
        e.preventDefault();

        post(`/${clinica.slug}/employees`, {
            onSuccess: () => closeModals(),
        });
    };

    const handleUpdate = (e: React.FormEvent) => {
        e.preventDefault();

        if (!editingEmployee) return;

        patch(`/${clinica.slug}/employees/${editingEmployee.id}`, {
            onSuccess: () => closeModals(),
        });
    };

    const handleDelete = (id: number) => {
        if (confirm('Tem certeza que deseja excluir este funcionário?')) {
            router.delete(`/${clinica.slug}/employees/${id}`);
        }
    };

    return (
        <>
            <Head title="Funcionários" />

            <div className="flex flex-col space-y-6 p-8">
                {flashMessage && (
                    <div className="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
                        {flashMessage}
                    </div>
                )}

                <div className="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Funcionários"
                        description={`Gerencie os funcionários da clínica ${clinica.name}`}
                    />

                    <Button onClick={openCreateModal}>
                        <Plus className="mr-2 h-4 w-4" />
                        Novo Funcionário
                    </Button>
                </div>

                <div className="rounded-lg border bg-card shadow-sm">
                    {employees.length > 0 ? (
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nome</TableHead>
                                    <TableHead>Cargo</TableHead>
                                    <TableHead className="w-[100px] text-right">
                                        Ações
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {employees.map((employee) => (
                                    <TableRow key={employee.id}>
                                        <TableCell className="font-medium">
                                            {employee.user.name}
                                        </TableCell>
                                        <TableCell className="text-muted-foreground">
                                            {roleLabels[employee.role] ||
                                                employee.role}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-1">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="h-8 w-8"
                                                    onClick={() =>
                                                        openEditModal(employee)
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
                                                        handleDelete(
                                                            employee.id,
                                                        )
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
                                Nenhum funcionário cadastrado
                            </h3>
                            <p className="mt-1 max-w-sm text-sm text-muted-foreground">
                                Comece adicionando o primeiro funcionário para
                                esta clínica.
                            </p>
                            <Button
                                variant="outline"
                                className="mt-4"
                                onClick={openCreateModal}
                            >
                                <Plus className="mr-2 h-4 w-4" />
                                Adicionar Funcionário
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
                        <DialogTitle>Novo Funcionário</DialogTitle>
                        <DialogDescription>
                            Adicione um novo funcionário para esta clínica.
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleCreate} className="grid gap-4 py-2">
                        <div className="grid gap-2">
                            <Label htmlFor="user_id">Usuário</Label>
                            <Select
                                value={data.user_id}
                                onValueChange={(value) =>
                                    setData('user_id', value)
                                }
                            >
                                <SelectTrigger id="user_id">
                                    <SelectValue placeholder="Selecione um usuário" />
                                </SelectTrigger>
                                <SelectContent>
                                    {users.map((user) => (
                                        <SelectItem
                                            key={user.id}
                                            value={user.id.toString()}
                                        >
                                            {user.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.user_id} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="role">Cargo</Label>
                            <Select
                                value={data.role}
                                onValueChange={(value) =>
                                    setData('role', value)
                                }
                            >
                                <SelectTrigger id="role">
                                    <SelectValue placeholder="Selecione um cargo" />
                                </SelectTrigger>
                                <SelectContent>
                                    {Object.entries(roleLabels).map(
                                        ([value, label]) => (
                                            <SelectItem
                                                key={value}
                                                value={value}
                                            >
                                                {label}
                                            </SelectItem>
                                        ),
                                    )}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.role} />
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
                                    ? 'Criando...'
                                    : 'Criar Funcionário'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={!!editingEmployee}
                onOpenChange={(open) => {
                    if (!open) closeModals();
                }}
            >
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Editar Funcionário</DialogTitle>
                        <DialogDescription>
                            Atualize o cargo do funcionário.
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleUpdate} className="grid gap-4 py-2">
                        <div className="grid gap-2">
                            <Label htmlFor="edit-user">Usuário</Label>
                            <div className="rounded-md bg-muted px-3 py-2 text-sm text-muted-foreground">
                                {editingEmployee?.user.name}
                            </div>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="edit-role">Cargo</Label>
                            <Select
                                value={data.role}
                                onValueChange={(value) =>
                                    setData('role', value)
                                }
                            >
                                <SelectTrigger id="edit-role">
                                    <SelectValue placeholder="Selecione um cargo" />
                                </SelectTrigger>
                                <SelectContent>
                                    {Object.entries(roleLabels).map(
                                        ([value, label]) => (
                                            <SelectItem
                                                key={value}
                                                value={value}
                                            >
                                                {label}
                                            </SelectItem>
                                        ),
                                    )}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.role} />
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

EmployeesIndex.layout = (props: Props) => {
    const clinica = props.clinica;

    if (!clinica) return {};

    return {
        breadcrumbs: [
            { title: 'Clínicas', href: '#' },
            { title: clinica.name, href: '#' },
            { title: 'Funcionários', href: `/${clinica.slug}/employees` },
        ],
    };
};
