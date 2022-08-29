import React from 'react';
import CreateUserForm from "@/Domains/User/CreateUserForm";
import AppLayout from "@/Layouts/AppLayout";
import useTypedPage from "@/Hooks/useTypedPage";
import ExistingUserWelcome from "@/Domains/User/ExistingUserWelcome";

export default function Home() {
    const page = useTypedPage();

    return (
        <AppLayout title={'Home'}>
            <div>
                {page.props.user === null
                    ? <CreateUserForm />
                    : <ExistingUserWelcome />
                }
            </div>
        </AppLayout>
    )
}
