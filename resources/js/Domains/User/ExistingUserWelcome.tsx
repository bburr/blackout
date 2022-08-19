import React from 'react';
import JetFormSection from "@/Jetstream/FormSection";
import useRoute from "@/Hooks/useRoute";
import {useForm} from "@inertiajs/inertia-react";
import JetButton from "@/Jetstream/Button";
import classNames from "classnames";
import JetLabel from "@/Jetstream/Label";
import JetInput from "@/Jetstream/Input";


export default function ExistingUserWelcome() {
    const route = useRoute();
    const createLobbyForm = useForm();

    const joinLobbyForm = useForm({
        invite_code: '',
    });

    function createLobby() {
        createLobbyForm.post(route('create-lobby'));
    }

    function joinLobby() {
        joinLobbyForm.post(route('join-lobby'));
    }

    return (
        <div>
            <JetFormSection
                title={'Create a Lobby'}
                description={'Create a lobby and invite other players'}
                onSubmit={createLobby}
                renderActions={() => (
                    <>
                        <JetButton
                            className={classNames({'opacity-25': createLobbyForm.processing})}
                            disabled={createLobbyForm.processing}
                        >
                            Create Lobby
                        </JetButton>
                    </>
                )}
            >
            </JetFormSection>
            <JetFormSection
                title={'Join a Lobby'}
                description={'Join another player\'s lobby'}
                onSubmit={joinLobby}
                renderActions={() => (
                    <>
                        <JetButton
                            className={classNames({'opacity-25': joinLobbyForm.processing})}
                            disabled={joinLobbyForm.processing}
                        >
                            Join Lobby
                        </JetButton>
                    </>
                )}
            >
                <JetLabel htmlFor={'name'}>Enter an invite code</JetLabel>
                <JetInput
                    id={'invite_code'}
                    type={'text'}
                    value={joinLobbyForm.data.invite_code}
                    onChange={e => joinLobbyForm.setData('invite_code', e.currentTarget.value)}
                />
            </JetFormSection>
        </div>
    );
}
