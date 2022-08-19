import JetButton from "@/Jetstream/Button";
import classNames from "classnames";
import JetFormSection from "@/Jetstream/FormSection";
import React from "react";
import useRoute from "@/Hooks/useRoute";
import {useForm} from "@inertiajs/inertia-react";
import JetLabel from "@/Jetstream/Label";
import JetInput from "@/Jetstream/Input";

export default function () {
    const route = useRoute();
    const form = useForm({
        ending_num_tricks: 1,
        max_num_tricks: 5,
        points_for_correct_bet: 10,
        starting_num_tricks: 1,
    });

    function startGame() {
        form.post(route('start-game'));
    }

    return (
        <JetFormSection
            title={'Start Game'}
            description={''}
            onSubmit={startGame}
            renderActions={() => (
                <>
                    <JetButton
                        className={classNames({'opacity-25': form.processing})}
                        disabled={form.processing}
                    >
                        Start Game
                    </JetButton>
                </>
            )}
        >

            <JetLabel htmlFor={'startingNumTricks'}>Starting Number of Tricks</JetLabel>
            <JetInput
                id={'startingNumTricks'}
                type={'number'}
                value={form.data.starting_num_tricks}
                onChange={e => form.setData('starting_num_tricks', e.currentTarget.value)}
            />

            <JetLabel htmlFor={'maxNumTricks'}>Maximum Number of Tricks</JetLabel>
            <JetInput
                id={'maxNumTricks'}
                type={'number'}
                value={form.data.max_num_tricks}
                onChange={e => form.setData('max_num_tricks', e.currentTarget.value)}
            />

            <JetLabel htmlFor={'endingNumTricks'}>Ending Number of Tricks</JetLabel>
            <JetInput
                id={'endingNumTricks'}
                type={'number'}
                value={form.data.ending_num_tricks}
                onChange={e => form.setData('ending_num_tricks', e.currentTarget.value)}
            />

            <JetLabel htmlFor={'pointsForCorrectBet'}>Points for Correct Bet</JetLabel>
            <JetInput
                id={'pointsForCorrectBet'}
                type={'number'}
                value={form.data.points_for_correct_bet}
                onChange={e => form.setData('points_for_correct_bet', e.currentTarget.value)}
            />

        </JetFormSection>
    )
}
