import { Head, useForm } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { useState } from 'react';

export default function Register() {
    const [step, setStep] = useState(1);
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        clinica_option: 'create',
        clinica_name: '',
        clinica_code: '',
        contact_email: '',
        contact_phone: '',
        address: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(store.url(), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <>
            <Head title="Register" />
            <form onSubmit={submit} className="flex flex-col gap-6">
                <div className="grid gap-6">
                    {step === 1 && (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="name"
                                    name="name"
                                    value={data.name}
                                    onChange={(e) =>
                                        setData('name', e.target.value)
                                    }
                                    placeholder="Full name"
                                />
                                <InputError
                                    message={errors.name}
                                    className="mt-2"
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Email address</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    tabIndex={2}
                                    autoComplete="email"
                                    name="email"
                                    value={data.email}
                                    onChange={(e) =>
                                        setData('email', e.target.value)
                                    }
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">Password</Label>
                                <PasswordInput
                                    id="password"
                                    required
                                    tabIndex={3}
                                    autoComplete="new-password"
                                    name="password"
                                    value={data.password}
                                    onChange={(e) =>
                                        setData('password', e.target.value)
                                    }
                                    placeholder="Password"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    Confirm password
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    required
                                    tabIndex={4}
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    value={data.password_confirmation}
                                    onChange={(e) =>
                                        setData(
                                            'password_confirmation',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="Confirm password"
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <div className="grid gap-4 rounded-lg border bg-muted/20 p-4">
                                <div className="flex gap-4">
                                    <div className="flex items-center space-x-2">
                                        <input
                                            type="radio"
                                            id="create_option"
                                            name="clinica_option"
                                            value="create"
                                            checked={
                                                data.clinica_option === 'create'
                                            }
                                            onChange={() =>
                                                setData(
                                                    'clinica_option',
                                                    'create',
                                                )
                                            }
                                            className="h-4 w-4 border-gray-300 text-primary focus:ring-primary"
                                        />
                                        <Label htmlFor="create_option">
                                            Criar Clínica Agora
                                        </Label>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <input
                                            type="radio"
                                            id="join_option"
                                            name="clinica_option"
                                            value="join"
                                            checked={
                                                data.clinica_option === 'join'
                                            }
                                            onChange={() =>
                                                setData(
                                                    'clinica_option',
                                                    'join',
                                                )
                                            }
                                            className="h-4 w-4 border-gray-300 text-primary focus:ring-primary"
                                        />
                                        <Label htmlFor="join_option">
                                            Prosseguir com Código
                                        </Label>
                                    </div>
                                </div>

                                {data.clinica_option === 'join' && (
                                    <div className="grid gap-2">
                                        <Label htmlFor="clinica_code">
                                            Código da Clínica (UUID)
                                        </Label>
                                        <Input
                                            id="clinica_code"
                                            type="text"
                                            name="clinica_code"
                                            value={data.clinica_code}
                                            onChange={(e) =>
                                                setData(
                                                    'clinica_code',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="Ex: UUID da clínica"
                                        />
                                        <InputError
                                            message={errors.clinica_code}
                                        />
                                    </div>
                                )}
                            </div>

                            {data.clinica_option === 'create' ? (
                                <Button
                                    type="button"
                                    onClick={() => setStep(2)}
                                    className="mt-2 w-full"
                                >
                                    Próximo Passo
                                </Button>
                            ) : (
                                <Button
                                    type="submit"
                                    className="mt-2 w-full"
                                    disabled={processing}
                                >
                                    {processing && <Spinner />}
                                    Create account
                                </Button>
                            )}
                        </>
                    )}

                    {step === 2 && data.clinica_option === 'create' && (
                        <>
                            <div className="grid gap-4 rounded-lg border bg-muted/10 p-4">
                                <h3 className="text-center text-lg font-medium">
                                    Configuração da Clínica
                                </h3>
                                <div className="grid gap-2">
                                    <Label htmlFor="clinica_name">
                                        Nome da Clínica
                                    </Label>
                                    <Input
                                        id="clinica_name"
                                        type="text"
                                        name="clinica_name"
                                        value={data.clinica_name}
                                        onChange={(e) =>
                                            setData(
                                                'clinica_name',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Ex: Minha Clínica"
                                        autoFocus
                                        required
                                    />
                                    <InputError message={errors.clinica_name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="contact_email">
                                        Email de Contato
                                    </Label>
                                    <Input
                                        id="contact_email"
                                        type="email"
                                        name="contact_email"
                                        value={data.contact_email}
                                        onChange={(e) =>
                                            setData(
                                                'contact_email',
                                                e.target.value,
                                            )
                                        }
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
                                        type="text"
                                        name="contact_phone"
                                        value={data.contact_phone}
                                        onChange={(e) =>
                                            setData(
                                                'contact_phone',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="(11) 99999-9999"
                                    />
                                    <InputError
                                        message={errors.contact_phone}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="address">
                                        Endereço Completo
                                    </Label>
                                    <Input
                                        id="address"
                                        type="text"
                                        name="address"
                                        value={data.address}
                                        onChange={(e) =>
                                            setData('address', e.target.value)
                                        }
                                        placeholder="Rua Exemplo, 123 - Centro"
                                    />
                                    <InputError message={errors.address} />
                                </div>
                            </div>

                            <div className="mt-2 flex gap-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setStep(1)}
                                    className="w-full"
                                >
                                    Voltar
                                </Button>
                                <Button
                                    type="submit"
                                    className="w-full"
                                    disabled={processing}
                                >
                                    {processing && <Spinner />}
                                    Finalizar Cadastro
                                </Button>
                            </div>
                        </>
                    )}
                </div>

                <div className="text-center text-sm text-muted-foreground">
                    Already have an account?{' '}
                    <TextLink href={login()} tabIndex={6}>
                        Log in
                    </TextLink>
                </div>
            </form>
        </>
    );
}

Register.layout = {
    title: 'Create an account',
    description: 'Enter your details below to create your account',
};
