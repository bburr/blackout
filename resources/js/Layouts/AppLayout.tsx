import { Inertia } from '@inertiajs/inertia';
import { InertiaLink, Head } from '@inertiajs/inertia-react';
import classNames from 'classnames';
import React, { PropsWithChildren, useState } from 'react';
import useRoute from '@/Hooks/useRoute';
import useTypedPage from '@/Hooks/useTypedPage';
import JetApplicationMark from '@/Jetstream/ApplicationMark';
import JetBanner from '@/Jetstream/Banner';
import JetDropdown from '@/Jetstream/Dropdown';
import JetDropdownLink from '@/Jetstream/DropdownLink';
import JetNavLink from '@/Jetstream/NavLink';
import JetResponsiveNavLink from '@/Jetstream/ResponsiveNavLink';

interface Props {
  title: string;
  renderHeader?(): JSX.Element;
}

export default function AppLayout({
    title,
    renderHeader,
    children,
}: PropsWithChildren<Props>) {
    const page = useTypedPage();
    const route = useRoute();
    const [showingNavigationDropdown, setShowingNavigationDropdown] =
    useState(false);

    return (
        <div>
            <Head title={title} />

            <JetBanner />

            <div className="min-h-screen bg-gray-100">
                <nav className="bg-white border-b border-gray-100">
                    {/* <!-- Primary Navigation Menu --> */}
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between h-16">
                            <div className="flex">
                                {/* <!-- Logo --> */}
                                <div className="flex-shrink-0 flex items-center">
                                    <InertiaLink href={route('home')}>
                                        <JetApplicationMark className="block h-9 w-auto" />
                                    </InertiaLink>
                                </div>

                                {/* <!-- Navigation Links --> */}
                                <div className="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                    {/* using <a> instead of <JetNavLink> as the component does not handle external links well */}
                                    <a className="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition"
                                        href="https://github.com/bburr/blackout"
                                        target="_blank"
                                    >
                                        Github / Rules
                                    </a>
                                </div>
                            </div>


                            {/* <!-- Hamburger --> */}
                            <div className="-mr-2 flex items-center sm:hidden">
                                <button
                                    onClick={() =>
                                        setShowingNavigationDropdown(!showingNavigationDropdown)
                                    }
                                    className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition"
                                >
                                    <svg
                                        className="h-6 w-6"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            className={classNames({
                                                hidden: showingNavigationDropdown,
                                                'inline-flex': !showingNavigationDropdown,
                                            })}
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M4 6h16M4 12h16M4 18h16"
                                        />
                                        <path
                                            className={classNames({
                                                hidden: !showingNavigationDropdown,
                                                'inline-flex': showingNavigationDropdown,
                                            })}
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* <!-- Responsive Navigation Menu --> */}
                    <div
                        className={classNames('sm:hidden', {
                            block: showingNavigationDropdown,
                            hidden: !showingNavigationDropdown,
                        })}
                    >
                        <div className="pt-2 pb-3 space-y-1">
                            <a className="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition"
                               href="https://github.com/bburr/blackout"
                               target="_blank"
                            >
                                Github / Rules
                            </a>
                        </div>

                    </div>
                </nav>

                {/* <!-- Page Heading --> */}
                {renderHeader ? (
                    <header className="bg-white shadow">
                        <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {renderHeader()}
                        </div>
                    </header>
                ) : null}

                {/* <!-- Page Content --> */}
                <main>{children}</main>
            </div>
        </div>
    );
}
