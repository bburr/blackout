import React from 'react';
import JoinLobbyForm from "@/Domains/User/JoinLobbyForm";
import CreateLobbyForm from "@/Domains/User/CreateLobbyForm";

export default function ExistingUserWelcome() {
    return (
        <div>
            <CreateLobbyForm></CreateLobbyForm>
            <JoinLobbyForm></JoinLobbyForm>
        </div>
    );
}
