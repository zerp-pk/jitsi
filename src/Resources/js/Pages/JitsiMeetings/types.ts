import { PaginatedData, ModalState, AuthContext } from '@/types/common';

export interface User {
    id: number;
    name: string;
}

export interface JitsiMeeting {
    id: number;
    title: string;
    description?: string;
    room_name?: string;
    start_url?: string;
    join_url?: string;
    start_time: any;
    duration: number;
    status: string;
    participants?: string[];
    host_id?: number;
    host?: User;
    created_at: string;
}

export interface CreateJitsiMeetingFormData {
    title: string;
    description: string;
    start_time: any;
    duration: string;
    status: string;
    participants: string[];
    host_id: string;
    sync_to_google_calendar: boolean;
}

export interface EditJitsiMeetingFormData {
    title: string;
    description: string;
    start_time: any;
    duration: string;
    status: string;
    participants: string[];
    host_id: string;
}

export interface JitsiMeetingFilters {
    title: string;
    description: string;
    status: string;
    date_range: string;
}

export type PaginatedJitsiMeetings = PaginatedData<JitsiMeeting>;
export type JitsiMeetingModalState = ModalState<JitsiMeeting>;

export interface JitsiMeetingsIndexProps {
    jitsimeetings: PaginatedJitsiMeetings;
    auth: AuthContext;
    users: any[];
    [key: string]: unknown;
}

export interface CreateJitsiMeetingProps {
    onSuccess: () => void;
}

export interface EditJitsiMeetingProps {
    jitsimeeting: JitsiMeeting;
    onSuccess: () => void;
}

export interface JitsiMeetingShowProps {
    jitsimeeting: JitsiMeeting;
    [key: string]: unknown;
}
