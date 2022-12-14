import React from 'react';
import JetFormSection from "@/Jetstream/FormSection";
import JetInput from "@/Jetstream/Input";
import JetLabel from "@/Jetstream/Label";
import JetButton from "@/Jetstream/Button";
import classNames from "classnames";
import {useForm} from "@inertiajs/inertia-react";
import useRoute from "@/Hooks/useRoute";


export default function CreateTeamForm() {
    const route = useRoute();
    const form = useForm({
        name: '',
    });

    function createUser() {
        form.post(route('create-user'));
    }

    return (
        <JetFormSection
            title={'Welcome!'}
            description={'Enter your first name to get started'}
            onSubmit={createUser}
            renderActions={() => (
                <>
                    <JetButton
                        className={classNames({'opacity-25': form.processing})}
                        disabled={form.processing}
                    >
                        Start playing!
                    </JetButton>
                </>
            )}
        >
            <JetLabel htmlFor={'name'}>Enter your name</JetLabel>
            <JetInput
                id={'name'}
                type={'text'}
                value={form.data.name}
                onChange={e => form.setData('name', e.currentTarget.value)}
            />
        </JetFormSection>
    );
}
