import React from 'react';
import JetFormSection from "@/Jetstream/FormSection";


export default function CreateTeamForm() {
    function createUser() {

    }

    return (
        <JetFormSection title={'Welcome!'} description={'Enter your first name to get started'} onSubmit={createUser}>

        </JetFormSection>
    );
}
