import React, {PropsWithChildren} from 'react';
import AppLayout from "@/Layouts/AppLayout";
import {EchoClient} from "@/websocket-client";
import StartGameForm from "@/Domains/Lobby/StartGameForm";
import {Inertia} from "@inertiajs/inertia";
import route from "ziggy-js";

interface LobbyUser {
    id: string;
    name: string;
}

interface Props {
    lobbyId: string;
    users: LobbyUser[];
    isOwner: boolean;
    inviteCode: string;
}

export default class Lobby extends React.Component {
    constructor(props: PropsWithChildren<Props>) {
        super(props);

        this.state = {
            users: props.users,
        };
    }

    componentDidMount() {
        let component = this;
        function handleUserJoinedLobby(e) {
            component.setState((state, props) => {
                state.users.push({
                    id: e.userId,
                    name: e.userName,
                });

                return {
                    users: state.users,
                };
            });
        }

        function handleGameStarted(e) {
            if (component.props.isOwner) {
                return;
            }

            Inertia.get(route('game', {game: e.gameId}));
        }

        // todo private/presence
        EchoClient.getInstance().channel(`lobby.${this.props.lobbyId}`)
            .listen('UserJoinedLobby', handleUserJoinedLobby)
            .listen('GameStarted', handleGameStarted)
        ;
    }

    componentWillUnmount() {
        EchoClient.getInstance().leave(`lobby.${this.props.lobbyId}`);
    }

    render() {
        const userList = this.state.users.map((user) =>
            <li key={user.id}>{user.name}</li>
        );

        return (
            <AppLayout title={'Lobby'}>
                <div>
                    Lobby {this.props.lobbyId}
                </div>
                <div>
                    <ul>{userList}</ul>
                </div>
                {this.props.isOwner &&
                    <div>
                        <div>
                            Invite Code: {this.props.inviteCode}
                        </div>
                        <div>
                            <StartGameForm></StartGameForm>
                        </div>
                    </div>
                }
            </AppLayout>
        )
    }
}
