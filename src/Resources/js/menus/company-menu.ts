import { Video } from 'lucide-react';

declare global {
    function route(name: string): string;
}

export const jitsiCompanyMenu = (t: (key: string) => string) => [
    {
        title: t('Jitsi Meetings'),
        icon: Video,
        permission: 'manage-jitsi-meetings',
        href: route('jitsi.jitsi-meetings.index'),
        order: 950        
    },
];