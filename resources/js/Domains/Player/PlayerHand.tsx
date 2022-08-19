import React from 'react';
import JetFormSection from "@/Jetstream/FormSection";
import JetInput from "@/Jetstream/Input";
import JetLabel from "@/Jetstream/Label";
import JetButton from "@/Jetstream/Button";
import classNames from "classnames";
import {useForm} from "@inertiajs/inertia-react";
import useRoute from "@/Hooks/useRoute";
import PlayerCard from "@/Domains/Player/PlayerCard";

interface Card {
    suit: string;
    value: number;
    str: string;
}

interface Props {
    gameId: string;
    cards: Card[];
    shouldShowPlayButton: boolean;
}

export default function PlayerHand({
    gameId,
    cards,
    shouldShowPlayButton,
}: Props) {
    const playerHandList = cards.map((card) =>
        <PlayerCard key={card.suit + '.' + card.value}
            gameId={gameId}
            card={card}
            shouldShowPlayButton={shouldShowPlayButton}
        />
    );

    return (
        <ul>{playerHandList}</ul>
    );
}
