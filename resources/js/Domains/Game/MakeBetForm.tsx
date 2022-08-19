import React from 'react';
import JetFormSection from "@/Jetstream/FormSection";
import JetInput from "@/Jetstream/Input";
import JetLabel from "@/Jetstream/Label";
import JetButton from "@/Jetstream/Button";
import classNames from "classnames";
import {useForm} from "@inertiajs/inertia-react";
import useRoute from "@/Hooks/useRoute";

interface Props {
    gameId: string;
}

export default function MakeBetForm({
    gameId,
}: Props) {
    const route = useRoute();
    const form = useForm({
        bet: '',
    });

    form.transform((data) => ({
        bet: parseInt(data.bet),
        gameId: gameId,
    }));

    function makeBet() {
        form.post(route('perform-bet'));
    }

    return (
        <JetFormSection
            title={'Make Bet'}
            description={''}
            onSubmit={makeBet}
            renderActions={() => (
                <>
                    <JetButton
                        className={classNames({'opacity-25': form.processing})}
                        disabled={form.processing}
                    >
                        Make Bet
                    </JetButton>
                </>
            )}
        >
            <JetLabel htmlFor={'bet'}>Make your bet</JetLabel>
            <JetInput
                id={'bet'}
                type={'number'}
                value={form.data.bet}
                onChange={e => form.setData('bet', e.currentTarget.value)}
            />
        </JetFormSection>
    );
}
