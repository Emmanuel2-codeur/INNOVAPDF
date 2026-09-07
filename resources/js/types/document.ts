export type DocumentType = 'cv' | 'cover_letter' | 'invoice' | 'quote' | 'certificate' | 'attestation';

export interface ProfileData {
    fullName: string;
    title: string;
    email: string;
    phone: string;
    location: string;
    photoUrl?: string;
    summary?: string;
}

export interface ExperienceItem {
    id: string;
    company: string;
    position: string;
    startDate: string;
    endDate?: string;
    description: string;
}

export interface InvoiceItem {
    id: string;
    description: string;
    quantity: number;
    unitPrice: number;
    total: number;
}

export interface StyleConfig {
    primaryColor: string;
    secondaryColor?: string;
    fontFamily: string;
    fontSize: 'sm' | 'md' | 'lg';
    margins: number;
}

export interface DocumentSchema {
    id?: number;
    type: DocumentType;
    title: string;
    template: string;
    profile?: ProfileData;
    experiences?: ExperienceItem[];
    invoiceItems?: InvoiceItem[];
    style: StyleConfig;
}