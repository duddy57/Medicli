import { Form } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store } from '@/routes/clinicas';

export default function CreateClinicaModal({ children }: PropsWithChildren) {
    const [open, setOpen] = useState(false);

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{children}</DialogTrigger>
            <DialogContent>
                <Form
                    key={String(open)}
                    {...store.form()}
                    className="space-y-6"
                    onSuccess={() => setOpen(false)}
                >
                    {({ errors, processing }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>Create a new clinica</DialogTitle>
                                <DialogDescription>
                                    Create a new clinica to collaborate with
                                    others.
                                </DialogDescription>
                            </DialogHeader>

                            <div className="grid gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Clinica name</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        data-test="create-clinica-name"
                                        placeholder="My clinica"
                                        required
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="contact_email">
                                        Email de Contato
                                    </Label>
                                    <Input
                                        id="contact_email"
                                        name="contact_email"
                                        type="email"
                                        placeholder="contato@clinica.com"
                                    />
                                    <InputError
                                        message={errors.contact_email}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="contact_phone">
                                        Telefone
                                    </Label>
                                    <Input
                                        id="contact_phone"
                                        name="contact_phone"
                                        placeholder="(11) 99999-9999"
                                    />
                                    <InputError
                                        message={errors.contact_phone}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="address">Endereço</Label>
                                    <Input
                                        id="address"
                                        name="address"
                                        placeholder="Rua Exemplo, 123"
                                    />
                                    <InputError message={errors.address} />
                                </div>
                            </div>

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button variant="secondary">Cancel</Button>
                                </DialogClose>

                                <Button
                                    type="submit"
                                    data-test="create-clinica-submit"
                                    disabled={processing}
                                >
                                    Create clinica
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
