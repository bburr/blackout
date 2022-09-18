import {useForm} from "@inertiajs/inertia-react";
import useRoute from "@/Hooks/useRoute";
import JetButton from "@/Jetstream/Button";
import classNames from "classnames";
import JetLabel from "@/Jetstream/Label";
import JetInput from "@/Jetstream/Input";
import JetFormSection from "@/Jetstream/FormSection";
import React from "react";


export default function JoinLobbyForm() {
    const route = useRoute();
    const joinLobbyForm = useForm({
        invite_code: '',
    });

    function joinLobby() {
        joinLobbyForm.post(route('join-lobby'));
    }

    return (
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
    );
}
