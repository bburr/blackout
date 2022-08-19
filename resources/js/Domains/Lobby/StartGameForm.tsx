import JetButton from "@/Jetstream/Button";
import classNames from "classnames";
import JetFormSection from "@/Jetstream/FormSection";
import React from "react";
import useRoute from "@/Hooks/useRoute";
import {useForm} from "@inertiajs/inertia-react";

export default function () {
    const route = useRoute();
    const form = useForm();

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
        </JetFormSection>
    )
}
