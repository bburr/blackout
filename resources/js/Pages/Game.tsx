import React, {PropsWithChildren} from 'react';
import AppLayout from "@/Layouts/AppLayout";
import {EchoClient} from "@/websocket-client";
import JetSectionTitle from "@/Jetstream/SectionTitle";
import {InertiaLink, Link} from "@inertiajs/inertia-react";
import JetFormSection from "@/Jetstream/FormSection";
import JetButton from "@/Jetstream/Button";
import classNames from "classnames";
import JetInput from "@/Jetstream/Input";
import JetLabel from "@/Jetstream/Label";
import MakeBetForm from "@/Domains/Game/MakeBetForm";
import PlayerHand from "@/Domains/Player/PlayerHand";
import {Inertia} from "@inertiajs/inertia";

interface Card {
    suit: string;
    value: number;
    str: string;
}

interface Player {
    index: number;
    name: string;
}

interface RoundConfig {
    roundNumber: number;
    numTricks: number;
}

interface Trick {
    leadingCard: Card;
    plays: Card[];
}

interface Round {
    config: RoundConfig;
    trumpCard: Card;
    bets: number[];
    currentTrick: Trick;
    tricksWon: number[];
}

interface Props {
    gameId: string;
    playerIndex: number;
    dealerIndex: number;
    nextPlayerIndexToBet: number;
    nextPlayerIndexToPlay: number;
    players: Player[];
    currentRound: Round;
    playerHand: Card[];
    scoreTotals: number[];
}

export default function Game({
    gameId,
    playerIndex,
    dealerIndex,
    nextPlayerIndexToBet,
    nextPlayerIndexToPlay,
    players,
    currentRound,
    playerHand,
    scoreTotals,
}: PropsWithChildren<Props>) {
    function reload() {
        Inertia.reload({preserveState: false});
    }

    EchoClient.getInstance()
        .channel(`game.${gameId}`)
        .listen('BetWasMade', reload)
        .listen('CardWasPlayed', reload);

    let playerList = [];
    let scoreList = [];

    for (const player of players) {
        playerList.push(
            <li key={player.index + '-round'}>
                {player.name} | Bet: {currentRound.bets[player.index]} | Tricks: {currentRound.tricksWon[player.index]} | Played: {currentRound.currentTrick.plays[player.index]?.str}
            </li>
        );

        scoreList.push(
            <li key={player.index + '-round'}>
                {player.name} | Current Score: {scoreTotals[player.index]}
            </li>
        );
    }

    return (
        <AppLayout title={'Playing a Game'}>
            <ul>
                <li>Round Number: {currentRound.config.roundNumber}</li>
                <li>Number of Tricks: {currentRound.config.numTricks}</li>
                <li>Player Index: {playerIndex}</li>
                <li>Dealer Index: {dealerIndex}</li>
                <li>Betting Player: {nextPlayerIndexToBet}</li>
                <li>Next Player: {nextPlayerIndexToPlay}</li>
            </ul>
            {currentRound.trumpCard !== null &&
                <div>
                    Trump card: {currentRound.trumpCard.str}
                </div>
            }
            <div>
                <JetSectionTitle title={'Players'} description={''} />
                <ul>{playerList}</ul>
            </div>
            <div>
                <JetSectionTitle title={'Scores'} description={''} />
                <ul>{scoreList}</ul>
            </div>
            <div>
                <JetSectionTitle title={'Your Hand'} description={''} />
                <PlayerHand
                    gameId={gameId}
                    cards={playerHand}
                    shouldShowPlayButton={playerIndex === nextPlayerIndexToPlay && nextPlayerIndexToBet === -1}
                />
            </div>
            {playerIndex === nextPlayerIndexToBet &&
                <MakeBetForm gameId={gameId}></MakeBetForm>
            }
        </AppLayout>
    )
}
