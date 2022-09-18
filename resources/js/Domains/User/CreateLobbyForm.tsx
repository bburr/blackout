import JetFormSection from "@/Jetstream/FormSection";
import JetButton from "@/Jetstream/Button";
import classNames from "classnames";
import React from "react";
import useRoute from "@/Hooks/useRoute";
import {useForm} from "@inertiajs/inertia-react";

export default function CreateLobbyForm() {
    const route = useRoute();
    const createLobbyForm = useForm();

    function createLobby() {
        createLobbyForm.post(route('create-lobby'));
    }

    return (
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
    );
};
