import React from 'react';
import JetFormSection from "@/Jetstream/FormSection";
import JetInput from "@/Jetstream/Input";
import JetLabel from "@/Jetstream/Label";
import JetButton from "@/Jetstream/Button";
import classNames from "classnames";
import {useForm} from "@inertiajs/inertia-react";
import useRoute from "@/Hooks/useRoute";

interface Card {
    suit: string;
    value: number;
    str: string;
}

interface Props {
    gameId: string;
    card: Card;
    shouldShowPlayButton: boolean;
}

export default function PlayerCard({
    gameId,
    card,
    shouldShowPlayButton,
}: Props) {
    const route = useRoute();
    const form = useForm({
        gameId: gameId,
        cardSuit: card.suit,
        cardValue: card.value,
    });

    function playCard() {
        form.post(route('play-card'));
    }

    return (
        <li>
            {card.str}
            {shouldShowPlayButton &&
                <JetButton onClick={playCard}>Play</JetButton>
            }
        </li>
    );
}
