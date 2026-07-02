import { Video } from 'lucide-react';

export interface SettingMenuItem {
  order: number;
  title: string;
  href: string;
  icon: any;
  permission: string;
  component: string;
}

export const getJitsiMeetingCompanySettings = (t: (key: string) => string): SettingMenuItem[] => [
  {
    order: 650,
    title: t('Jitsi Meet Settings'),
    href: '#jitsi-settings',
    icon: Video,
    permission: 'manage-jitsi-settings',
    component: 'jitsi-settings'
  }
];